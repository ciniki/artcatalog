<?php
//
// Description
// -----------
// This funciton will return a list of the random added items in the art catalog. 
// These are used on the homepage of the tenant website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get images for.
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
function ciniki_artcatalog_web_sliderImages($ciniki, $settings, $tnid, $list, $limit) {


    if( $list == 'random' ) {
        $strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.category, "
            . "name AS title, permalink, image_id, media, size, framed_size, price, "
            . "IF(status>=50, 'yes', 'no') AS sold, "
            . "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
            . "FROM ciniki_artcatalog "
            . "LEFT JOIN ciniki_images ON ("
                . "ciniki_artcatalog.image_id = ciniki_images.id "
                . "AND ciniki_images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
            . "AND ciniki_artcatalog.image_id > 0 "
            . "";
        if( $limit != '' && $limit > 0 && is_int($limit) ) {
            $strsql .= "ORDER BY RAND() "
                . "LIMIT " . intval($limit) . " ";
        } else {
            $strsql .= "ORDER BY RAND() "
                . "LIMIT 15 ";
        }
    } elseif( $list == 'forsale' ) {
        $strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.category, "
            . "name AS title, permalink, image_id, media, size, framed_size, price, "
            . "IF(status>=50, 'yes', 'no') AS sold, "
            . "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
            . "FROM ciniki_artcatalog "
            . "LEFT JOIN ciniki_images ON ("
                . "ciniki_artcatalog.image_id = ciniki_images.id "
                . "AND ciniki_images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
            . "AND ciniki_artcatalog.image_id > 0 "
            . "AND ciniki_artcatalog.status = 20 "
            . "";
        if( $limit != '' && $limit > 0 && is_int($limit) ) {
            $strsql .= "ORDER BY RAND() "
                . "LIMIT " . intval($limit) . " ";
        } else {
            $strsql .= "ORDER BY RAND() "
                . "LIMIT 15 ";
        }
    } else {
        $strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.category, "
            . "name AS title, permalink, image_id, media, size, framed_size, price, "
            . "IF(status>=50, 'yes', 'no') AS sold, "
            . "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
            . "FROM ciniki_artcatalog "
            . "LEFT JOIN ciniki_images ON ("
                . "ciniki_artcatalog.image_id = ciniki_images.id "
                . "AND ciniki_images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_artcatalog.image_id > 0 "
            . "AND (ciniki_artcatalog.webflags&0x01) = 1 "
            . "ORDER BY ciniki_artcatalog.year DESC, "
                . "ciniki_artcatalog.month DESC, "
                . "ciniki_artcatalog.day DESC, "
                . "ciniki_artcatalog.date_added DESC "
                . "";
        if( $limit != '' && $limit > 0 && is_int($limit) ) {
            $strsql .= "LIMIT " . intval($limit) . " ";
        } else {
            $strsql .= "LIMIT 15";
        }
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
            'category'=>urlencode($row['category']),
            'permalink'=>$row['permalink'], 'image_id'=>$row['image_id'],
            'caption'=>$caption, 'sold'=>$row['sold'], 'last_updated'=>$row['last_updated']));
    }
    
    return array('stat'=>'ok', 'images'=>$images);
}
?>
