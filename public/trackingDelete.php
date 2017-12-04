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
// tnid:         The ID of the tenant to remove the item from.
// tracking_id:         The ID of the tracking to be removed.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_trackingDelete(&$ciniki) {
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.trackingDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //  
    // Turn off autocommit
    // 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the uuid of the tracking item to be deleted
    //
    $strsql = "SELECT uuid FROM ciniki_artcatalog_tracking "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['tracking_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'place');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['place']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.40', 'msg'=>'Unable to find existing tracking item'));
    }
    $uuid = $rc['place']['uuid'];

    //
    // Delete tracking
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    return ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.artcatalog.place', $args['tracking_id'], $uuid, 0x07);
}
?>
