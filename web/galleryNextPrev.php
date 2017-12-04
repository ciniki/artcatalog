<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_web_galleryNextPrev($ciniki, $settings, $tnid, $args) {
//$permalink, $img, $type) {

    //
    // Get the list of images for the current gallery
    //
    $strsql = "SELECT id, name, image_id, permalink "
        . "FROM ciniki_artcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND image_id > 0 "
        . "AND (webflags&0x01) = 1 ";
    if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
    }
    if( isset($args['type']) && $args['type'] == 'category' ) {
        $strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['img']['category']) . "' ";
    }
    $strsql .= "ORDER BY ciniki_artcatalog.year DESC, "
        . "ciniki_artcatalog.month DESC, "
        . "ciniki_artcatalog.day DESC, "
        . "ciniki_artcatalog.date_added DESC ";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'next');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['rows']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.50', 'msg'=>'Unable to load image list'));
    }

    $images = $rc['rows'];
    $prev = NULL;
    $next = NULL;
//  foreach($images as $iid => $image) {
    $num_images = count($images);
    if( $num_images > 1 ) {
        for($i=0;$i<$num_images;$i++) {
            if( $images[$i]['id'] == $args['img']['id'] ) {
                if( $i == 0 ) {
                    // First image
                    $next = $images[$i+1];
                    $prev = $images[$num_images-1];
                } elseif( $i == ($num_images-1) ) {
                    // Last image
                    $next = $images[0];
                    $prev = $images[$i-1];
                } elseif( isset($images[$i+1]) ) {
                    $prev = $images[$i-1];
                    $next = $images[$i+1];
                }
                break;
            }
        }
    }

    return array('stat'=>'ok', 'next'=>$next, 'prev'=>$prev);
}
?>
