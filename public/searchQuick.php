<?php
//
// Description
// ===========
// This method will search the art catalog for items where start_needle matches one
// of the fields: name, catalog_number, media, location or notes.  The search looks
// for fields that start with start_needle, or are preceeded by a space and the start_needle.
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
function ciniki_artcatalog_searchQuick($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.searchQuick'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    $strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, "
        . "ROUND(price, 2) AS price, location, type, "
        . "IF(status>=50, 'yes', 'no') AS sold, "
        . "ciniki_artcatalog.last_updated "
//        . "IF((flags&0x02)=0x02,'yes','no') AS sold "
//      . "IF(ciniki_artcatalog.category='', 'Uncategorized', ciniki_artcatalog.category) AS cname "
//      . "IF(ciniki_artcatalog.status=1, 'open', 'closed') AS status "
        . "FROM ciniki_artcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR catalog_number like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR media like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR media like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR location like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR location like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR notes like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR notes like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . ") "
        . "";
    $strsql .= "ORDER BY name, location "
        . "";
    if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'type', 'name', 'image_id', 'media', 'catalog_number', 'size', 'framed_size', 'price', 'location', 'sold', 'last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) ) {
        return array('stat'=>'ok', 'items'=>array());
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
    $rsp = array('stat'=>'ok', 'items'=>$rc['items']);
    foreach($rsp['items'] as $iid => $item) {
        //
        // Load the images
        //
        if( isset($item['image_id']) && $item['image_id'] > 0 ) {
            $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], 
                array('image_id'=>$item['image_id'], 'maxlength'=>75, 'last_updated'=>$item['last_updated'], 'reddot'=>$item['sold']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $rsp['items'][$iid]['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
        }
    }

    return $rsp;
}
?>
