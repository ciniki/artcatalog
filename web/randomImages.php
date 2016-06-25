<?php
//
// Description
// -----------
// This funciton will return a list of the random added items in the art catalog. 
// These are used on the homepage of the business website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get images for.
// limit:           The maximum number of images to return.
//
// Returns
// -------
// <images>
//      [title="Slow River" permalink="slow-river" image_id="431" 
//          caption="Based on a photograph taken near Slow River, Ontario, Pastel, size: 8x10" sold="yes"
//          last_updated="1342653769"],
//      [title="Open Field" permalink="open-field" image_id="217" 
//          caption="An open field in Ontario, Oil, size: 8x10" sold="yes"
//          last_updated="1342653769"],
//      ...
// </images>
//
function ciniki_artcatalog_web_randomImages($ciniki, $settings, $business_id, $limit) {

    $strsql = "SELECT ciniki_artcatalog.id, "
        . "name AS title, permalink, image_id, media, size, framed_size, price, "
        . "IF(status>=50, 'yes', 'no') AS sold, "
        . "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
        . "FROM ciniki_artcatalog "
        . "LEFT JOIN ciniki_images ON ("
            . "ciniki_artcatalog.image_id = ciniki_images.id "
            . "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . ") "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
        . "AND ciniki_artcatalog.image_id > 0 "
        . "";
    if( $limit != '' && $limit > 0 && is_int($limit) ) {
        $strsql .= "ORDER BY RAND() "
            . "LIMIT $limit ";
    } else {
        $strsql .= "ORDER BY RAND() "
            . "LIMIT 6 ";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $images = array();
    foreach($rc['rows'] as $rownum => $row) {
        $caption = $row['title'];
        if( $row['media'] != '' ) {
            $caption .= ', ' . $row['media'];
        }
        if( $row['size'] != '' ) {
            $caption .= ', ' . $row['size'];
        }
        if( $row['framed_size'] != '' ) {
            $caption .= ' (framed: ' . $row['framed_size'] . ')';
        }
        if( $row['price'] != '' ) {
            $price = $row['price'];
            if( preg_match('/^\s*[^\$]/', $price) ) {
                $price = '$' . preg_replace('/^\s*/', '\$', $row['price']);
            }
            $caption .= ", " . $price;
        }
        array_push($images, array('id'=>$row['id'], 'title'=>$row['title'], 
            'permalink'=>$row['permalink'], 'image_id'=>$row['image_id'],
            'caption'=>$caption, 'sold'=>$row['sold'], 'last_updated'=>$row['last_updated']));
    }
    
    return array('stat'=>'ok', 'images'=>$images);
}
?>
