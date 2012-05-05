<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
// <events>
// 	<event id="" title="" />
// </events>
//
function ciniki_artcatalog_web_latestImages($ciniki, $settings, $business_id, $limit) {

	$strsql = "SELECT name AS title, image_id, media, size, framed_size, price, "
		. "IF((flags&0x02)=0x02, 'yes', 'no') AS sold, "
		. "IF(ciniki_images.last_updated > ciniki_artcatalog.last_updated, UNIX_TIMESTAMP(ciniki_images.last_updated), UNIX_TIMESTAMP(ciniki_artcatalog.last_updated)) AS last_updated "
		// . "UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
		. "FROM ciniki_artcatalog "
		. "LEFT JOIN ciniki_images ON (ciniki_artcatalog.image_id = ciniki_images.id) "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	if( $limit != '' && $limit > 0 && is_int($limit) ) {
		$strsql .= "ORDER BY ciniki_artcatalog.date_added DESC "
			. "LIMIT $limit ";
	} else {
		$strsql .= "ORDER BY ciniki_artcatalog.date_added DESC "
			. "LIMIT 4 ";
	}

    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', '');
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
		array_push($images, array('title'=>$row['title'], 'image_id'=>$row['image_id'],
			'caption'=>$caption, 'sold'=>$row['sold'], 'last_updated'=>$row['last_updated']));
	}
	
	return array('stat'=>'ok', 'images'=>$images);
}
?>