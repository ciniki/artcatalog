<?php
//
// Description
// ===========
// This method will return the list of fields used in the artcatalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get the list from.
// 
// Returns
// -------
//
function ciniki_artcatalog_fieldList($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'field'=>array('required'=>'yes', 'blank'=>'no', 'validlist'=>array('category', 'media', 'location', 'year'), 'name'=>'Field'),
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.fieldList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    $strsql = "SELECT DISTINCT " . $args['field'] . " "
        . "FROM ciniki_artcatalog "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs, " . $args['field'] . " "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'items', 'fname'=>$args['field'], 'name'=>'item',
            'fields'=>array('name'=>$args['field'])),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) ) {
        return array('stat'=>'ok', 'items'=>array());
    }
    return array('stat'=>'ok', 'items'=>$rc['items']);
}
?>
