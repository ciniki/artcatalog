<?php
//
// Description
// -----------
// This function will return the list of images for an event
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_web_eventImages($ciniki, $settings, $business_id, $args) {

    if( isset($args['object']) && $args['object'] == 'ciniki.artcatalog.place'
        && isset($args['object_id']) && $args['object_id'] != '' ) {
        
        $strsql = "SELECT image_id, "
            . "ciniki_artcatalog.name, "
            . "ciniki_artcatalog.permalink, "
            . "ciniki_artcatalog.description, "
            . "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
            . "FROM ciniki_artcatalog_tracking "
            . "LEFT JOIN ciniki_artcatalog ON ("
                . "ciniki_artcatalog_tracking.artcatalog_id = ciniki_artcatalog.id "
                . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
                . "AND ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . ") "
            . "LEFT JOIN ciniki_images ON ("
                . "ciniki_artcatalog.image_id = ciniki_images.id "
                . "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . ") "
            . "WHERE ciniki_artcatalog_tracking.permalink = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_artcatalog.image_id > 0 "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'images', 'fname'=>'image_id',
                'fields'=>array('image_id', 'title'=>'name', 'permalink', 'description', 'last_updated')),
                ));
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
