<?php
//
// Description
// -----------
// This method will turn the artcatalog settings for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the ATDO settings for.
// 
// Returns
// -------
//
function ciniki_artcatalog_settingsGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.settingsGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];
    
    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_artcatalog_settings', 'tnid', $args['tnid'], 'ciniki.artcatalog', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['settings']) ) {
        return array('stat'=>'ok', 'settings'=>array());
    }
    $settings = $rc['settings'];

    //
    // If ciniki.taxes module is enabled, load the list of tax types
    //
    if( isset($modules['ciniki.taxes']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'taxTypes');
        $rc = ciniki_taxes_taxTypes($ciniki, $args['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        return array('stat'=>'ok', 'settings'=>$settings, 'taxtypes'=>$rc['types']);
    }

    return array('stat'=>'ok', 'settings'=>$settings);
}
?>
