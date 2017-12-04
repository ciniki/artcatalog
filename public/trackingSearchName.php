<?php
//
// Description
// ===========
// This method will return the existing tracking place names.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to search.
//
// start_needle:    The search string to search the field for.
//
// limit:           (optional) Limit the number of results to be returned. 
//                  If the limit is not specified, the default is 25.
// 
// Returns
// -------
// <items>
//      <item id="2434" name="Black River" image_id="3872" media="Pastel" catalog_number="20120316"
//          size="8x10" framed_size="12x14" price="350" location="Home" />
//      <item id="1854" name="Flowing Stream" image_id="3871" media="Oil" catalog_number="20120219"
//          size="8x10" framed_size="12x14" price="350" location="Home" />
//      ...
// </items>
//
function ciniki_artcatalog_trackingSearchName($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Search Word'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'default'=>'10', 'name'=>'Limit'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.trackingSearchName'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    $strsql = "SELECT id, name "
        . "FROM ciniki_artcatalog_tracking "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . ") "
        . "AND name <> '' "
        . "";
    $strsql .= "ORDER BY name, start_date DESC "
        . "";
    if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'results', 'fname'=>'name', 'name'=>'result', 
            'fields'=>array('name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['results']) || !is_array($rc['results']) ) {
        return array('stat'=>'ok', 'results'=>array());
    }
    return array('stat'=>'ok', 'results'=>$rc['results']);
}
?>
