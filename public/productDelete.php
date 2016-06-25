<?php
//
// Description
// ===========
// This method will remove an item from the art catalog.  All information
// will be removed, so be sure you want it deleted.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to remove the item from.
// product_id:          The ID of the product to be removed.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_productDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.productDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the uuid of the product item to be deleted
    //
    $strsql = "SELECT uuid FROM ciniki_artcatalog_product "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'product');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['product']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2411', 'msg'=>'Unable to find existing product'));
    }
    $uuid = $rc['product']['uuid'];

    //
    // Check if any modules are currently using this subscription
    //
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'checkObjectUsed');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['business_id'], array(
                'object'=>'ciniki.artcatalog.product', 
                'object_id'=>$args['product_id'],
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2412', 'msg'=>'Unable to check if product is still being used', 'err'=>$rc['err']));
            }
            if( $rc['used'] != 'no' ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2413', 'msg'=>"Product is still in use. " . $rc['msg']));
            }
        }
    }

    //
    // Delete product
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    return ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.artcatalog.product', $args['product_id'], $uuid, 0x07);
}
?>
