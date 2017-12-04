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
function ciniki_artcatalog_categoryList($ciniki) {
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.categoryList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'maps');
    $rc = ciniki_artcatalog_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the settings for the artcatalog
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash'); 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 
        'tnid', $args['tnid'], 'ciniki.web', 'settings', 'page-gallery-artcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $settings = $rc['settings'];
    } else {
        $settings = array();
    }

    if( isset($settings['page-gallery-artcatalog-split']) 
        && $settings['page-gallery-artcatalog-split'] == 'yes' 
        ) {
        $strsql = "SELECT DISTINCT type, type AS name, category "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY type, category COLLATE latin1_general_cs, category "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'types', 'fname'=>'type', 'name'=>'type',
                'fields'=>array('number'=>'type', 'name'),
                'maps'=>array('name'=>$maps['item']['type'])),
//              'maps'=>array('name'=>array('1'=>'Paintings', '2'=>'Photographs', '3'=>'Jewelry', '4'=>'Sculptures', '5'=>'Fibre Arts', '6'=>'Crafts', '8'=>'Pottery'))),
            array('container'=>'categories', 'fname'=>'category', 'name'=>'category',
                'fields'=>array('type', 'name'=>'category')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['types']) ) {
            return array('stat'=>'ok', 'types'=>array());
        }
        return array('stat'=>'ok', 'types'=>$rc['types']);
    } else {
        $strsql = "SELECT DISTINCT '0' AS type, category "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY category COLLATE latin1_general_cs, category "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'categories', 'fname'=>'category', 'name'=>'category',
                'fields'=>array('type', 'name'=>'category')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['categories']) ) {
            return array('stat'=>'ok', 'categories'=>array());
        }
        return array('stat'=>'ok', 'categories'=>$rc['categories']);
    }
}
?>
