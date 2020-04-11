<?php
//
// Description
// ===========
// This method will remove the items from the tracking group, which deletes the group.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the item from.
// 
// Returns
// -------
//
function ciniki_artcatalog_trackingGroupDelete($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'start_date'=>array('required'=>'yes', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'), 
        'end_date'=>array('required'=>'yes', 'blank'=>'yes', 'type'=>'date', 'name'=>'End Date'), 
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

    //
    // Get the original items
    //
    $strsql = "SELECT id, uuid, name, start_date, end_date "
        . "FROM ciniki_artcatalog_tracking "
        . "WHERE name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
        . "AND start_date = '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "AND end_date = '" . ciniki_core_dbQuote($ciniki, $args['end_date']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.56', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    $items = isset($rc['rows']) ? $rc['rows'] : array();
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    foreach($items as $item) {
        //
        // Update the tracking entry
        //
        $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.artcatalog.place', $item['id'], $item['uuid'], 0x04);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.55', 'msg'=>'Unable to update the tracking'));
        }
    }
    
    return array('stat'=>'ok');
}
?>
