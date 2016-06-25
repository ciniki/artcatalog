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
// business_id:         The ID of the business to get the details for.
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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'setting'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Setting'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to business_id as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.settingsHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', 
        $args['business_id'], 'ciniki_artcatalog_settings', $args['setting'], 'detail_value');
}
?>
