<?php
//
// Description
// -----------
// This method will turn the artcatalog settings for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get the ATDO settings for.
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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.settingsGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];
    
    //
    // Grab the settings for the business from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_artcatalog_settings', 'business_id', $args['business_id'], 'ciniki.artcatalog', 'settings', '');
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
        $rc = ciniki_taxes_taxTypes($ciniki, $args['business_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        return array('stat'=>'ok', 'settings'=>$settings, 'taxtypes'=>$rc['types']);
    }

    return array('stat'=>'ok', 'settings'=>$settings);
}
?>
