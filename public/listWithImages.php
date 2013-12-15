<?php
//
// Description
// ===========
// This method will list the art catalog items sorted by category.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the list from.
// section:			(optional) How the list should be sorted and organized.
//
//					- category
//					- media
//					- location
//					- year
//					- list
//
// name:			(optional) The name of the section to get restrict the list.  This
//					can only be specified if the section is also specified.  If the section
//					is category, then the name will restrict the results to the cateogry of
//					this name.
//
// type:			(optional) Only list items of a specific type. Valid types are:
//
//					- painting
//					- photograph
//					- jewelry
//					- sculpture
//					- craft
//					- clothing
//
// limit:			(optional) Limit the number of results.
// 
// Returns
// -------
// <sections>
//		<section name="Landscape">
//				<item id="23839" name="Swift Rapids" image_id="45" type="1" year="2012" 
//					media="Oil" catalog_number="20120421"
//					size="8x10" framed_size="12x14" price="350" sold="yes" flags="1" location="Home"
//					notes="" />
//				<item id="23853" name="Open Field" image_id="23" type="1" year="2012" 
//					media="Pastel" catalog_number="20120311"
//					size="8x10" framed_size="12x14" price="300" sold="no" flags="1" location="Home"
//					notes="" />
//				...
//		</section>
// </sections>
//
function ciniki_artcatalog_listWithImages($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'section'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section Name specified'),
		'type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'),
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
   
   	//
	// Map the types to an ID
	//
	if( isset($args['type']) && $args['type'] != '' ) {
		if( $args['type'] == 'painting' ) {
			$args['type_id'] = 1;
		} elseif( $args['type'] == 'photograph' ) {
			$args['type_id'] = 2;
		} elseif( $args['type'] == 'jewelry' ) {
			$args['type_id'] = 3;
		} elseif( $args['type'] == 'sculpture' ) {
			$args['type_id'] = 4;
		} elseif( $args['type'] == 'craft' ) {
			$args['type_id'] = 5;
		} elseif( $args['type'] == 'clothing' ) {
			$args['type_id'] = 6;
		} else {
			$args['type_id'] = 0;
		}
	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.listWithImages'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Load INTL settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');


	$strsql = "SELECT ciniki_artcatalog.id, image_id, ciniki_artcatalog.name, "
		. "type, year, media, catalog_number, size, framed_size, ROUND(price, 2) AS price, flags, location, "
		. "ciniki_artcatalog.notes, "
		. "IF((flags&0x02)=0x02,'yes','no') AS sold, "
		. "";
	if( !isset($args['section']) || $args['section'] == 'category' ) {
		$strsql .= "IF(ciniki_artcatalog.category='', '', ciniki_artcatalog.category) AS sname ";
	} elseif( $args['section'] == 'media' ) {
		$strsql .= "IF(ciniki_artcatalog.media='', '', ciniki_artcatalog.media) AS sname ";
	} elseif( $args['section'] == 'location' ) {
		$strsql .= "IF(ciniki_artcatalog.location='', '', ciniki_artcatalog.location) AS sname ";
	} elseif( $args['section'] == 'year' ) {
		$strsql .= "IF(ciniki_artcatalog.year='', '', ciniki_artcatalog.year) AS sname ";
	} elseif( $args['section'] == 'list' ) {
		$strsql .= "IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS sname ";
	} elseif( $args['section'] == 'tracking' ) {
		$strsql .= "IF(ciniki_artcatalog_tracking.name='', '', ciniki_artcatalog_tracking.name) AS sname ";
	}

	if( isset($args['section']) && $args['section'] == 'list' ) {
		$strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
			. "AND ciniki_artcatalog_tags.tag_type = 1 "
			. "";
	} elseif( isset($args['section']) && $args['section'] == 'tracking' ) {
		$strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tracking "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_artcatalog.id = ciniki_artcatalog_tracking.artcatalog_id "
			. "";
	} else {
		$strsql .= "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
	}
	//
	// Check if this should just be a sublist with one section of a group
	//
	if( isset($args['name']) && $args['name'] != '' ) {
		if( $args['name'] == 'Unknown' ) {
			$args['name'] = '';
		}
		if( $args['section'] == 'category' ) {
			$strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' ";
		} elseif( $args['section'] == 'media' ) {
			$strsql .= "AND media = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
				. "AND type = 1 "
				. "";
		} elseif( $args['section'] == 'location' ) {
			$strsql .= "AND location = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' ";
		} elseif( $args['section'] == 'year' ) {
			$strsql .= "AND year = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' ";
		} elseif( $args['section'] == 'list' ) {
			$strsql .= "AND ciniki_artcatalog_tags.tag_name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' ";
		} elseif( $args['section'] == 'tracking' ) {
			$strsql .= "AND ciniki_artcatalog_tracking.name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' ";
		} 
	}
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' ";
	}
	if( isset($args['section']) && $args['section'] == 'year' ) {
		$strsql .= "ORDER BY sname COLLATE latin1_general_cs DESC, name "
			. "";
	} else {
		$strsql .= "ORDER BY sname COLLATE latin1_general_cs, name "
			. "";
	}
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'sname', 'name'=>'section',
			'fields'=>array('name'=>'sname')),
		array('container'=>'items', 'fname'=>'id', 'name'=>'item',
			'fields'=>array('id', 'name', 'image_id', 'type', 'year', 'media', 'catalog_number', 
				'size', 'framed_size', 'price', 'sold', 'flags', 'location', 'notes')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sections']) ) {
		return array('stat'=>'ok', 'sections'=>array());
	}

	//
	// Add thumbnail information into list
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
	$sections = $rc['sections'];
	foreach($sections as $section_num => $section) {
		foreach($section['section']['items'] as $inum => $item) {
			if( isset($item['item']['image_id']) && $item['item']['image_id'] > 0 ) {
				$rc = ciniki_images_loadCacheThumbnail($ciniki, $item['item']['image_id'], 75);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$sections[$section_num]['section']['items'][$inum]['item']['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
			}
			$sections[$section_num]['section']['items'][$inum]['item']['price'] = numfmt_format_currency($intl_currency_fmt, $item['item']['price'], $intl_currency);
		}
	}

	return array('stat'=>'ok', 'sections'=>$sections);
}
?>
