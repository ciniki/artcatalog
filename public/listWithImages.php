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
//					- fibreart
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
        'sortby'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sort By'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section Name'),
		'type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
		// PDF options
        'output'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Output Type'), 
        'layout'=>array('required'=>'no', 'blank'=>'no', 'default'=>'list', 'name'=>'Layout',
			'validlist'=>array('pricelist', 'thumbnails', 'list', 'quad', 'single', 'excel')), 
        'pagetitle'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Title'), 
        'fields'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Fields'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.listWithImages'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Load the status maps for the text description of each status
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'maps');
	$rc = ciniki_artcatalog_maps($ciniki);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$maps = $rc['maps'];

   	//
	// Map the types to an ID
	//
	if( isset($args['type']) && $args['type'] != '' ) {
		$args['type_id'] = 0;
		foreach($maps['item']['typecode'] as $type_id => $code) {
			if( $args['type'] == $code ) {
				$args['type_id'] = $type_id;
			}
		}
//		if( $args['type'] == 'painting' ) {
//			$args['type_id'] = 1;
//		} elseif( $args['type'] == 'photograph' ) {
//			$args['type_id'] = 2;
//		} elseif( $args['type'] == 'jewelry' ) {
//			$args['type_id'] = 3;
//		} elseif( $args['type'] == 'sculpture' ) {
//			$args['type_id'] = 4;
//		} elseif( $args['type'] == 'fibreart' ) {
//			$args['type_id'] = 5;
//		} elseif( $args['type'] == 'clothing' ) {
//			$args['type_id'] = 6;
//		} elseif( $args['type'] == 'pottery' ) {
//			$args['type_id'] = 8;
//		} else {
//			$args['type_id'] = 0;
//		}
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


	$strsql = "SELECT ciniki_artcatalog.id, "
		. "image_id, "
		. "ciniki_artcatalog.name, "
		. "ciniki_artcatalog.category AS category_name, "
		. "type, "
		. "status, "
		. "status AS status_text, "
		. "year, "
		. "media, "
		. "catalog_number, "
		. "size, "
		. "framed_size, "
		. "price, "
		. "flags, "
		. "location, "
		. "description, "
		. "ciniki_artcatalog.awards, "
		. "ciniki_artcatalog.notes, "
		. "ciniki_artcatalog.inspiration, "
		. "ciniki_artcatalog.last_updated, "
		. "IF(status>=50, 'yes', 'no') AS sold, "
//		. "IF((flags&0x01)=0x01,'yes','no') AS sold, "
		. "";
	if( isset($args['sortby']) && $args['sortby'] == 'catalognumber' ) {
		$strsql .= "'' AS sname ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'category' ) {
		$strsql .= "IF(ciniki_artcatalog.category='', 'Unknown', ciniki_artcatalog.category) AS sname ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'media' ) {
		$strsql .= "IF(ciniki_artcatalog.media='', 'Unknown', ciniki_artcatalog.media) AS sname ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'location' ) {
		$strsql .= "IF(ciniki_artcatalog.location='', 'Unknown', ciniki_artcatalog.location) AS sname ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'year' ) {
		$strsql .= "IF(ciniki_artcatalog.year='', 'Unknown', ciniki_artcatalog.year) AS sname ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'tracking' ) {
		$strsql .= "IF(ciniki_artcatalog_tracking.name='', 'Unknown', ciniki_artcatalog_tracking.name) AS sname ";
	} elseif( !isset($args['section']) || $args['section'] == 'category' ) {
		$strsql .= "IF(ciniki_artcatalog.category='', 'Unknown', ciniki_artcatalog.category) AS sname ";
	} elseif( $args['section'] == 'media' ) {
		$strsql .= "IF(ciniki_artcatalog.media='', 'Unknown', ciniki_artcatalog.media) AS sname ";
	} elseif( $args['section'] == 'location' ) {
		$strsql .= "IF(ciniki_artcatalog.location='', 'Unknown', ciniki_artcatalog.location) AS sname ";
	} elseif( $args['section'] == 'year' ) {
		$strsql .= "IF(ciniki_artcatalog.year='', 'Unknown', ciniki_artcatalog.year) AS sname ";
	} elseif( $args['section'] == 'list' ) {
		$strsql .= "IF(ciniki_artcatalog_tags.tag_name='', 'Unknown', ciniki_artcatalog_tags.tag_name) AS sname ";
	} elseif( $args['section'] == 'tracking' ) {
		$strsql .= "IF(ciniki_artcatalog_tracking.name='', 'Unknown', ciniki_artcatalog_tracking.name) AS sname ";
	}

	if( isset($args['section']) && $args['section'] == 'list' ) {
		$strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
			. "AND ciniki_artcatalog_tags.tag_type = 1 "
			. "";
	} elseif( (isset($args['section']) && $args['section'] == 'tracking') || (isset($args['sortby']) && $args['sortby'] == 'tracking') ) {
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
	//
	// Check if output is PDF and sorted by catalog number instead of categories
	//
	if( isset($args['sortby']) && $args['sortby'] == 'catalognumber' ) {
		$strsql .= "ORDER BY catalog_number, name ";
	} elseif( isset($args['sortby']) && $args['sortby'] == 'year' ) {
		$strsql .= "ORDER BY sname COLLATE latin1_general_cs DESC, name ";
	} elseif( isset($args['sortby']) 
		&& ($args['sortby'] == 'category' || $args['sortby'] == 'media' || $args['sortby'] == 'location' || $args['sortby'] == 'tracking') ) {
		$strsql .= "ORDER BY sname COLLATE latin1_general_cs, name ";
	} 

	//
	// Organized by year
	//
	elseif( isset($args['section']) && $args['section'] == 'year' ) {
		$strsql .= "ORDER BY sname COLLATE latin1_general_cs DESC, name "
			. "";
	} 
	//
	// Organized by category, media, etc
	//
	else {
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
			'fields'=>array('id', 'title'=>'name', 'name', 'category'=>'category_name', 
				'image_id', 'type', 'status', 'status_text', 'year', 'media', 'catalog_number', 
				'size', 'framed_size', 'price', 'flags', 'location', 
				'description', 'notes', 'awards', 'inspiration', 'sold', 'last_updated'),
			'maps'=>array('status_text'=>$maps['item']['status']),
			'utctots'=>array('last_updated'),
			),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sections']) ) {
		$sections = array();
	} else {
		$sections = $rc['sections'];
	}

	//
	// Check if output is to be pdf
	//
	if( isset($args['output']) && $args['output'] == 'pdf' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'templates', $args['layout']);
		$function = 'ciniki_artcatalog_templates_' . $args['layout'];
		$rc = $function($ciniki, $args['business_id'], $sections, $args);
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		return array('stat'=>'ok');
	}

	//
	// Check if output is to be excel
	//
	if( isset($args['output']) && $args['output'] == 'excel' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'templates', $args['layout']);
		$function = 'ciniki_artcatalog_templates_' . $args['layout'];
		$rc = $function($ciniki, $args['business_id'], $sections, $args);
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		return array('stat'=>'ok');
	}

	//
	// Add thumbnail information into list
	//
	if( count($sections) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
		foreach($sections as $section_num => $section) {
			foreach($section['section']['items'] as $inum => $item) {
				if( isset($item['item']['image_id']) && $item['item']['image_id'] > 0 ) {
					$rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['business_id'], 
						array('image_id'=>$item['item']['image_id'], 'maxlength'=>75, 'last_updated'=>$item['item']['last_updated'], 'reddot'=>$item['item']['sold']));
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$sections[$section_num]['section']['items'][$inum]['item']['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
				}
				$sections[$section_num]['section']['items'][$inum]['item']['price'] = numfmt_format_currency($intl_currency_fmt, $item['item']['price'], $intl_currency);
			}
		}
	}

	return array('stat'=>'ok', 'sections'=>$sections);
}
?>
