<?php
//
// Description
// ===========
// This method will add a new item to the art catalog.  The image for the item
// must be uploaded separately into the ciniki images module.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the products to.
//
// artcatalog_id:	The ID of the artcatalog item to add the products for.
//
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_productAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Artcatalog Item'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Permalink'), 
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Flags'), 
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Sequence'), 
        'image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Synopsis'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'default'=>'0', 'name'=>'Price'), 
        'taxtype_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Taxtype'), 
        'inventory'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Inventory'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( $args['artcatalog_id'] == '0' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2409', 'msg'=>'No artcatalog item specified'));
	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.productAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	if( isset($args['name']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
	}

	//
	// Update product
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.artcatalog.product', $args);
}
?>
