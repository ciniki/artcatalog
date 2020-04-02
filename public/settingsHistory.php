<?php
//
// Description
// -----------
// This function will return the list of changes made to a field in artcatalog settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// setting:             The setting to get the history for.
//
// Returns
// -------
//
function ciniki_artcatalog_settingsHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'setting'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Setting'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.settingsHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( preg_match("/webflags_([0-9]+)/", $args['setting'], $m) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryFlagBit');
        return ciniki_core_dbGetModuleHistoryFlagBit($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history',
        $args['tnid'], 'ciniki_artcatalog_settings', 'defaults-webflags', 'detail_value', $m[1], 'no', 'yes');
    }


    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', 
        $args['tnid'], 'ciniki_artcatalog_settings', $args['setting'], 'detail_value');
}
?>
