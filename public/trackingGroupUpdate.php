<?php
//
// Description
// ===========
// This method will update all the tracking entries with new name, start_date and end_date.
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
function ciniki_artcatalog_trackingGroupUpdate($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'org_name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Original Name'), 
        'org_start_date'=>array('required'=>'yes', 'blank'=>'yes', 'type'=>'date', 'name'=>'Original Start Date'), 
        'org_end_date'=>array('required'=>'yes', 'blank'=>'yes', 'type'=>'date', 'name'=>'Original End Date'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'New Name'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'New Start Date'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'New End Date'), 
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
    // Check if anything has changed
    //
    if( (!isset($args['name']) || $args['name'] == $args['org_name'])
        && (!isset($args['start_date']) || $args['start_date'] == $args['org_start_date'])
        && (!isset($args['end_date']) || $args['end_date'] == $args['org_end_date'])
        ) {
        return array('stat'=>'ok');
    }

    //
    // Get the original items
    //
    $strsql = "SELECT id, name, start_date, end_date "
        . "FROM ciniki_artcatalog_tracking "
        . "WHERE name = '" . ciniki_core_dbQuote($ciniki, $args['org_name']) . "' "
        . "AND start_date = '" . ciniki_core_dbQuote($ciniki, $args['org_start_date']) . "' "
        . "AND end_date = '" . ciniki_core_dbQuote($ciniki, $args['org_end_date']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.54', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    $items = isset($rc['rows']) ? $rc['rows'] : array();
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    foreach($items as $item) {
        //
        // Update the tracking entry
        //
        error_log($item['id']);
        error_log(print_r($args, true));
        $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.artcatalog.place', $item['id'], $args, 0x04);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.53', 'msg'=>'Unable to update the tracking'));
        }
    }
    
    return array('stat'=>'ok');
}
?>
