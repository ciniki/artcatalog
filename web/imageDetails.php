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
function ciniki_artcatalog_web_imageDetails($ciniki, $settings, $business_id, $permalink) {

	//
	// Load INTL settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Get the details about the item
	//
	$strsql = "SELECT ciniki_artcatalog.id, "
		. "ciniki_artcatalog.name, "
		. "ciniki_artcatalog.permalink, "
		. "ciniki_artcatalog.image_id, "
		. "ciniki_artcatalog.type, "
		. "ciniki_artcatalog.catalog_number, "
		. "ciniki_artcatalog.category, "
		. "ciniki_artcatalog.year, "
		. "ciniki_artcatalog.flags, "
		. "ciniki_artcatalog.webflags, "
		. "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
		. "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
		. "IF((ciniki_artcatalog.webflags&0x01)=0, 'yes', 'no') AS hidden, "
		. "ciniki_artcatalog.media, "
		. "ciniki_artcatalog.size, "
		. "ciniki_artcatalog.framed_size, "
		. "ciniki_artcatalog.price, "
		. "ciniki_artcatalog.location, "
		. "ciniki_artcatalog.description, "
		. "ciniki_artcatalog.inspiration, "
		. "ciniki_artcatalog.awards, "
		. "ciniki_artcatalog.notes, "
		. "ciniki_artcatalog.date_added, ciniki_artcatalog.last_updated, "
		. "ciniki_artcatalog_images.id AS additional_id, "
		. "ciniki_artcatalog_images.image_id AS additional_image_id, "
		. "ciniki_artcatalog_images.permalink AS additional_permalink, "
		. "ciniki_artcatalog_images.name AS additional_name, "
		. "ciniki_artcatalog_images.description AS additional_description, "
		. "ciniki_artcatalog_images.last_updated AS additional_last_updated "
		. "FROM ciniki_artcatalog "
		. "LEFT JOIN ciniki_artcatalog_images ON (ciniki_artcatalog.id = ciniki_artcatalog_images.artcatalog_id "
			. "AND ciniki_artcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (ciniki_artcatalog_images.webflags&0x01) = 1 ) "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_artcatalog.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
		. "ORDER BY ciniki_artcatalog.id, ciniki_artcatalog_images.sequence, ciniki_artcatalog_images.date_added "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'items', 'fname'=>'id', 
			'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id', 'type', 'catalog_number', 
				'category', 'year', 'flags', 'webflags', 'forsale', 'sold', 'hidden', 
				'media', 'size', 'framed_size', 'price',
				'location', 'description', 'inspiration', 'awards', 'notes', 'date_added', 'last_updated')),
		array('container'=>'additionalimages', 'fname'=>'additional_id', 
			'fields'=>array('id'=>'additional_id', 'image_id'=>'additional_image_id',
				'permalink'=>'additional_permalink',
				'title'=>'additional_name', 'description'=>'additional_description',
				'last_updated'=>'additional_last_updated')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['items']) || count($rc['items']) < 1 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'651', 'msg'=>'Unable to find artwork'));
	}
	$image = array_pop($rc['items']);
//	$image = $rc['items'][0]['item'];
//	$image = array('id'=>$rc['piece']['id'],
//		'title'=>$rc['piece']['name'],
//		'category'=>$rc['piece']['category'],
//		'image_id'=>$rc['piece']['image_id'],
//		'details'=>'',
//		'description'=>$rc['piece']['description'],
//		'awards'=>$rc['piece']['awards'],
//		'date_added'=>$rc['piece']['date_added'],
//		'last_updated'=>$rc['piece']['last_updated']);
	$image['details'] = '';
	$comma = '';
	if( $image['media'] != '' && ($image['webflags']&0x1000) > 0 ) {
		$image['details'] .= $image['media'];
		$comma = ', ';
	}
	if( $image['size'] != '' ) {
		$image['details'] .= $comma . $image['size'];
		$comma = ', ';
	}
//	if( $image['framed_size'] != '' && 
	if( ($image['flags']&0x10) > 0 ) {
		if( $image['size'] != '' ) {
			if( $image['framed_size'] != '' ) {
				$image['details'] .= ' (framed: ' . $image['framed_size'] . ')';
			} else {
				$image['details'] .= ' framed';
			}
		} else {
			if( $image['framed_size'] != '' ) {
				$image['details'] .= 'Framed: ' . $image['framed_size'] . '';
			} else {
				$image['details'] .= 'Framed';
			}
		}
		$comma = ', ';
	} elseif( $image['size'] != '' && $image['type'] == 1 && ($image['flags']&0x10) == 0 ) {
		$image['details'] .= ' (unframed)';
		$comma = ', ';
	}
	if( ($image['webflags']&0x0800) > 0 ) {
		if( $image['price'] != '' && $image['price'] != '0' && $image['price'] != '0.00' 
			&& $image['forsale'] == 'yes' ) {
			if( is_numeric($image['price']) ) {
				$image['price'] = numfmt_format_currency($intl_currency_fmt, $image['price'], $intl_currency);
				$image['details'] .= $comma . $image['price'] . ' ' . $intl_currency;
			} else {
				$image['details'] .= $comma . $image['price'];
			}
	//		$image['details'] .= $comma . preg_replace('/^\s*([^$])/', '\$$1', $image['price']);
			$comma = ', ';
		}
		if( isset($image['sold']) && $image['sold'] == 'yes' ) {
			$image['details'] .= " <b> SOLD</b>";
			$comma = ', ';
		}
	}

	$image['category_permalink'] = urlencode($image['category']);

	return array('stat'=>'ok', 'image'=>$image);
}
?>
