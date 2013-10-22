<?php
//
// Description
// ===========
// This method will return all the information for an item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 		The ID of the business to get the item from.
// artcatalog_id:		The ID of the item in the catalog to be retrieved.
// 
// Returns
// -------
// <item id="27" name="South River" permalink="south-river" 
//		image_id="34" type="1" type_text="Painting"
//		flags="1" webflags="0" catalog_number="20120423" 
//		category="Landscape" year="2012"
//		media="Pastel" size="8x10" framed_size="12x14" forsale="yes" 
//		sold="no" website="visible, category highlight"
//		price="210" location="Home" inspiration="" notes="">
//		<description>
//			The description of the item.
//		</description>
//		<awards>
//			The awards the item has won.
//		</awards>
// </item>
//
function ciniki_artcatalog_get($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
		'tracking'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tracking'),
		'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.get'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timezoneOffset');
	$utc_offset = ciniki_users_timezoneOffset($ciniki);

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	$strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.name, permalink, image_id, type, type AS type_text, "
		. "ciniki_artcatalog.flags, "
		. "IF((ciniki_artcatalog.flags&0x01)=0x01, 'yes', 'no') AS forsale, "
		. "IF((ciniki_artcatalog.flags&0x02)=0x02, 'yes', 'no') AS sold, "
		. "CONCAT_WS('', IF((ciniki_artcatalog.webflags&0x01)=0x01, 'hidden', 'visible'), IF((ciniki_artcatalog.webflags&0x10)=0x10, ', category highlight', '')) AS website , "
		. "webflags, catalog_number, category, year, month, day, "
		. "media, size, framed_size, ciniki_artcatalog.price, ciniki_artcatalog.location, "
		. "ciniki_artcatalog.description, inspiration, awards, ciniki_artcatalog.notes, "
		. "ciniki_artcatalog.date_added, ciniki_artcatalog.last_updated, "
		. "ciniki_artcatalog_tags.tag_name AS lists "
//		. "ciniki_artcatalog_customers.customer_id AS customer_id, "
//		. "CONCAT_WS(' ', IFNULL(ciniki_customers.first, 'Unknown'), IFNULL(ciniki_customers.last, 'Customer')) AS customer_name, "
//		. "IF((ciniki_artcatalog_customers.flags&0x01)=0x01, 'yes', 'no') AS paid, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x10, 'yes', 'no') AS trade, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x20, 'yes', 'no') AS donation, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x40, 'yes', 'no') AS gift, "
//		. "ciniki_artcatalog_customers.price AS customer_price, "
//		. "(ciniki_artcatalog_customers.price + ciniki_artcatalog_customers.taxes + ciniki_artcatalog_customers.shipping "
//			. "+ ciniki_artcatalog_customers.return_shipping + ciniki_artcatalog_customers.other_costs) AS customer_sale_total "
		. "FROM ciniki_artcatalog "
		. "LEFT JOIN ciniki_artcatalog_tags ON (ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id AND ciniki_artcatalog_tags.tag_type = 1) ";
//		. "LEFT JOIN ciniki_artcatalog_customers ON (ciniki_artcatalog.id = ciniki_artcatalog_customers.artcatalog_id) "
//		. "LEFT JOIN ciniki_customers ON (ciniki_artcatalog_customers.customer_id = ciniki_customers.id "
//			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
	$strsql .= "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artcatalog.id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "ORDER BY ciniki_artcatalog.id, ciniki_artcatalog_tags.tag_name ";
//		. "ORDER BY ciniki_artcatalog.id, ciniki_customers.last, ciniki_customers.first "

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'items', 'fname'=>'id', 'name'=>'item',
			'fields'=>array('id', 'name', 'permalink', 'image_id', 'type', 'type_text', 
				'flags', 'webflags', 'catalog_number', 'category', 'year', 'month', 'day', 
				'media', 'size', 'framed_size', 'forsale', 'sold', 'website', 'price', 'location', 
				'description', 'inspiration', 'awards', 'notes', 'lists'),
			'dlists'=>array('lists'=>'::'),
			'maps'=>array('type_text'=>array('0'=>'Unknown', 
				'1'=>'Painting', 
				'2'=>'Photograph', 
				'3'=>'Jewelry', 
				'4'=>'Sculpture', 
				'5'=>'Craft',
				)),
			),
//		array('container'=>'sales', 'fname'=>'customer_id', 'name'=>'customer',
//			'fields'=>array('id'=>'customer_id', 'name'=>'customer_name', 'paid', 'trade', 'donation', 'gift', 'price'=>'customer_price', 'total'=>'customer_sale_total')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['items']) ) {
		return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'593', 'msg'=>'Unable to find item'));
	}
	$item = $rc['items'][0]['item'];

	//
	// Get the available tags
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsList');
	$rc = ciniki_core_tagsList($ciniki, 'ciniki.artcatalog', $args['business_id'], 'ciniki_artcatalog', 'id', 'ciniki_artcatalog_tags', 'artcatalog_id', 1);
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'594', 'msg'=>'Unable to get lists', 'err'=>$rc['err']));
	}
	$tags = array();
	if( isset($rc['tags']) ) {
		$tags = $rc['tags'];
	}

	//
	// Get the tracking list if request
	//
	if( isset($args['tracking']) && $args['tracking'] == 'yes' ) {
		$strsql = "SELECT id, name, external_number, "
			. "IFNULL(DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS start_date, "
			. "IFNULL(DATE_FORMAT(end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS end_date "
			. "FROM ciniki_artcatalog_tracking "
			. "WHERE artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY ciniki_artcatalog_tracking.start_date DESC "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
			array('container'=>'tracking', 'fname'=>'id', 'name'=>'place',
				'fields'=>array('id', 'name', 'external_number', 'start_date', 'end_date')),
			));
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		if( isset($rc['tracking']) ) {
			$item['tracking'] = $rc['tracking'];
		}
	}

	//
	// Get the additional images if requested
	//
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
		$strsql = "SELECT ciniki_artcatalog_images.id, "
			. "ciniki_artcatalog_images.image_id, "
			. "ciniki_artcatalog_images.name, "
			. "ciniki_artcatalog_images.sequence, "
			. "ciniki_artcatalog_images.webflags, "
			. "ciniki_artcatalog_images.description "
			. "FROM ciniki_artcatalog_images "
			. "WHERE ciniki_artcatalog_images.artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
			. "AND ciniki_artcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY ciniki_artcatalog_images.sequence, ciniki_artcatalog_images.date_added, ciniki_artcatalog_images.name "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
			array('container'=>'images', 'fname'=>'id', 'name'=>'image',
				'fields'=>array('id', 'image_id', 'name', 'sequence', 'webflags', 'description')),
			));
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		if( isset($rc['images']) ) {
			$item['images'] = $rc['images'];
			foreach($item['images'] as $inum => $img) {
				if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
					$rc = ciniki_images_loadCacheThumbnail($ciniki, $img['image']['image_id'], 75);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$item['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
				}
			}
		}
	}

	return array('stat'=>'ok', 'item'=>$item, 'tags'=>$tags);
}
?>
