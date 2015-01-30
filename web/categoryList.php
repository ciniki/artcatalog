<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries, 
// along with the images for each category highlight.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure.
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
// <categories>
// 		<category name="Portraits" image_id="349" />
// 		<category name="Landscape" image_id="418" />
//		...
// </categories>
//
function ciniki_artcatalog_web_categoryList($ciniki, $settings, $business_id, $args) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');	
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'business_id', $business_id,
		'ciniki.artcatalog', 'settings', 'category');
	if( $rc['stat'] != 'ok' ) {
		error_log('ERR: Unable to get category details');
	}
	if( isset($rc['settings']) ) {
		$csettings = $rc['settings'];
	} else {
		$csettings = array();
	}

	$strsql = "SELECT DISTINCT category AS name "
		. "FROM ciniki_artcatalog "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND (ciniki_artcatalog.webflags&0x01) = 1 "
		. "AND ciniki_artcatalog.image_id > 0 "
		. "";
	if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
		$strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
	}
	$strsql .= "AND category <> '' "
		. "ORDER BY category "
		. "";
	
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'categories', 'fname'=>'name',
			'fields'=>array('name')),
		array('container'=>'list', 'fname'=>'name',
			'fields'=>array('id'=>'name', 'title'=>'name')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['categories']) ) {
		return array('stat'=>'ok');
	}
	$categories = $rc['categories'];

	//
	// Load highlight images
	//
	$count = 0;
	foreach($categories as $cnum => $cat) {
		foreach($cat['list'] as $lnum => $category) {
			//
			// Look for the highlight image, or the most recently added image
			//
			$strsql = "SELECT ciniki_artcatalog.image_id, ciniki_images.image "
				. "FROM ciniki_artcatalog, ciniki_images "
				. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
				. "AND category = '" . ciniki_core_dbQuote($ciniki, $category['title']) . "' "
				. "";
			if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
				$strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_type']) . "' ";
			}
			$strsql .= "AND ciniki_artcatalog.image_id = ciniki_images.id "
				. "AND (ciniki_artcatalog.webflags&0x01) = 1 "
				. "ORDER BY (ciniki_artcatalog.webflags&0x10) DESC, "
				. "ciniki_artcatalog.year DESC, "
				. "ciniki_artcatalog.month DESC, "
				. "ciniki_artcatalog.day DESC, "
				. "ciniki_artcatalog.date_added DESC "
				. "LIMIT 1";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'image');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['image']) ) {
				$categories[$cnum]['list'][$lnum]['image_id'] = $rc['image']['image_id'];
			}
		
			//
			// Setup
			//
			$categories[$cnum]['list'][$lnum]['permalink'] = urlencode($category['title']);
			$categories[$cnum]['list'][$lnum]['is_details'] = 'yes';
		
			//
			// Setup the synopsis
			//
			if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 
				&& isset($csettings['category-synopsis-' . $args['artcatalog_type'] . '-' . $category['title']]) ) {
				$categories[$cnum]['list'][$lnum]['description'] = $csettings['category-synopsis-' . $args['artcatalog_type'] . '-' . $category['title']];
			} elseif( isset($csettings['category-synopsis-' . $category['title']]) ) {
				$categories[$cnum]['list'][$lnum]['description'] = $csettings['category-synopsis-' . $category['title']];
			} else {
				$categories[$cnum]['list'][$lnum]['description'] = '';
			}
		}
	}

	return array('stat'=>'ok', 'categories'=>$categories);	
}
?>
