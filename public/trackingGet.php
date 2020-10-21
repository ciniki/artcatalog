<?php
//
// Description
// ===========
// This method will return all the information for an item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the item from.
// artcatalog_id:       The ID of the item in the catalog to be retrieved.
// 
// Returns
// -------
//
function ciniki_artcatalog_trackingGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'tracking_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tracking'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.trackingGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    $strsql = "SELECT tracking.id, "
        . "artcatalog.name AS artcatalog_name, "
        . "tracking.name, "
        . "tracking.external_number, "
        . "IFNULL(DATE_FORMAT(tracking.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS start_date, "
        . "IFNULL(DATE_FORMAT(tracking.end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS end_date, "
        . "tracking.notes "
        . "FROM ciniki_artcatalog_tracking AS tracking "
        . "LEFT JOIN ciniki_artcatalog AS artcatalog ON ("
            . "tracking.artcatalog_id = artcatalog.id "
            . "AND artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE tracking.id = '" . ciniki_core_dbQuote($ciniki, $args['tracking_id']) . "' "
        . "AND tracking.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'place');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( !isset($rc['place']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.41', 'msg'=>'Unable to find tracking item'));
    }
    
    return array('stat'=>'ok', 'place'=>$rc['place']);
}
?>
