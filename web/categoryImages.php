<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure.
// business_id:		The ID of the business to get events for.
// type:			The list to return, either by category or year.
//
//					- category
//					- year
//
// type_name:		The name of the category or year to list.
//
// Returns
// -------
// <images>
//		[title="Slow River" permalink="slow-river" image_id="431" 
//			caption="Based on a photograph taken near Slow River, Ontario, Pastel, size: 8x10" sold="yes"
//			last_updated="1342653769"],
//		[title="Open Field" permalink="open-field" image_id="217" 
//			caption="An open field in Ontario, Oil, size: 8x10" sold="yes"
//			last_updated="1342653769"],
//		...
// </images>
//
function ciniki_artcatalog_web_categoryImages($ciniki, $settings, $business_id, $args) {

	$strsql = "SELECT ciniki_artcatalog.id, "
		. "name AS title, permalink, image_id, media, size, framed_size, price, "
		. "IF((flags&0x02)=0x02, 'yes', 'no') AS sold, "
		. "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
		. "FROM ciniki_artcatalog "
		. "LEFT JOIN ciniki_images ON (ciniki_artcatalog.image_id = ciniki_images.id) "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_artcatalog.image_id > 0 "
		. "AND (webflags&0x01) = 0 "
		. "";
	if( isset($args['artcatalog_type']) && $args['artcatalog_type'] != '' ) {
		$strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
	}
	if( isset($args['type']) && $args['type'] == 'category' && isset($args['type_name'])) {
		$strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['type_name']) . "' "
			. "";
	} elseif( isset($args['type']) && $args['type'] == 'year' && isset($args['type_name'])) {
		$strsql .= "AND year = '" . ciniki_core_dbQuote($ciniki, $args['type_name']) . "' "
			. "";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'635', 'msg'=>"Unable to find images."));
	}

	//
	// Put the latest additions first
	//
	$strsql .= "ORDER BY ciniki_artcatalog.year DESC, "
		. "ciniki_artcatalog.month DESC, "
		. "ciniki_artcatalog.day DESC, "
		. "ciniki_artcatalog.date_added DESC ";
//	$strsql .= "ORDER BY ciniki_artcatalog.date_added DESC ";

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
		array_push($images, array('id'=>$row['id'], 'title'=>$row['title'], 'permalink'=>$row['permalink'], 'image_id'=>$row['image_id'],
			'caption'=>$caption, 'sold'=>$row['sold'], 'last_updated'=>$row['last_updated']));
	}
	
	return array('stat'=>'ok', 'images'=>$images);
}
?>
