<?php
//
// Description
// ===========
// This method will return the list of categories used in the artcatalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the list from.
// 
// Returns
// -------
//
function ciniki_artcatalog_categoryDetails($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'category'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Category'),
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.categoryDetails'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the settings for the artcatalog
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash'); 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 
        'tnid', $args['tnid'], 'ciniki.artcatalog', 'settings', 'category');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $settings = $rc['settings'];
    } else {
        $settings = array();
    }
    
    $fields = array('synopsis', 'description');
    $details = array();
    foreach($fields as $f) {
        $details[$f] = '';
        if( isset($args['type']) && $args['type'] > 0
            && isset($settings['category-' . $f . '-' . $args['type'] . '-' . $args['category']]) ) {
            $details[$f] = $settings['category-' . $f . '-' . $args['type'] . '-' . $args['category']];
        } elseif( isset($settings['category-' . $f . '-' . $args['category']]) ) {
            $details[$f] = $settings['category-' . $f . '-' . $args['category']];
        }
    }

    return array('stat'=>'ok', 'details'=>$details);
}
?>
