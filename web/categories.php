<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries, 
// along with the images for each category highlight.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
// <categories>
//      <category name="Portraits" image_id="349" />
//      <category name="Landscape" image_id="418" />
//      ...
// </categories>
//
function ciniki_artcatalog_web_categories($ciniki, $settings, $tnid, $args) {

    //
    // Load the settings for each category
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash'); 
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'tnid', $tnid,
        'ciniki.artcatalog', 'settings', 'category');
    if( $rc['stat'] != 'ok' ) {
        error_log('ERR: Unable to get category details');
    }
    if( isset($rc['settings']) ) {
        $csettings = $rc['settings'];
    } else {
        $csettings = array();
    }

    //
    // Get the list of categories
    //
    $strsql = "SELECT DISTINCT category AS name "
        . "FROM ciniki_artcatalog "
        . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
        . "AND ciniki_artcatalog.image_id > 0 "
        . "";
    if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
    }
    $strsql .= "AND category <> '' "
        . "ORDER BY category "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'categories', 'fname'=>'name', 'name'=>'category',
            'fields'=>array('name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['categories']) ) {
        return array('stat'=>'ok');
    }
    $categories = $rc['categories'];

    //
    // Load highlight images
    //
    foreach($categories as $cnum => $cat) {
        //
        // Look for the highlight image, or the most recently added image
        //
        $strsql = "SELECT ciniki_artcatalog.image_id "
            . "FROM ciniki_artcatalog, ciniki_images "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND category = '" . ciniki_core_dbQuote($ciniki, $cat['name']) . "' "
            . "";
        if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
            $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
        }
        $strsql .= "AND ciniki_artcatalog.image_id = ciniki_images.id "
            . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
            . "ORDER BY (ciniki_artcatalog.webflags&0x10) DESC, "
            . "ciniki_artcatalog.year DESC, "
            . "ciniki_artcatalog.month DESC, "
            . "ciniki_artcatalog.day DESC, "
            . "ciniki_artcatalog.date_added DESC "
            . "LIMIT 1";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'image');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['image']) ) {
            $categories[$cnum]['image_id'] = $rc['image']['image_id'];
        }

        //
        // Setup
        //
        $categories[$cnum]['permalink'] = urlencode($cat['name']);
        $categories[$cnum]['is_details'] = 'yes';
    
        //
        // Setup the synopsis
        //
        if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 
            && isset($csettings['category-synopsis-' . $args['artcatalog_type'] . '-' . $cat['name']]) ) {
            $categories[$cnum]['description'] = $csettings['category-synopsis-' . $args['artcatalog_type'] . '-' . $cat['name']];
        } elseif( isset($csettings['category-synopsis-' . $cat['name']]) ) {
            $categories[$cnum]['description'] = $csettings['category-synopsis-' . $cat['name']];
        } else {
            $categories[$cnum]['description'] = '';
        }
    }

    return array('stat'=>'ok', 'categories'=>$categories);  
}
?>
