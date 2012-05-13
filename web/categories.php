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
// 	<event id="" name="" />
// </events>
//
function ciniki_artcatalog_web_categories($ciniki, $settings, $business_id) {

	$strsql = "SELECT DISTINCT category AS name "
		. "FROM ciniki_artcatalog "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND (ciniki_artcatalog.webflags&0x01) = 0 "
		. "AND category <> '' "
		. "ORDER BY category "
		. "";
	
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'categories', 'fname'=>'name', 'name'=>'category',
			'fields'=>array('name')),
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
	foreach($categories as $cnum => $cat) {
		//
		// Look for the highlight image, or the most recently added image
		//
		$strsql = "SELECT ciniki_artcatalog.image_id, ciniki_images.image "
			. "FROM ciniki_artcatalog, ciniki_images "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND category = '" . ciniki_core_dbQuote($ciniki, $cat['category']['name']) . "' "
			. "AND ciniki_artcatalog.image_id = ciniki_images.id "
			. "AND (ciniki_artcatalog.webflags&0x01) = 0 "
			. "ORDER BY (ciniki_artcatalog.webflags&0x10) DESC, ciniki_artcatalog.date_added DESC "
			. "LIMIT 1";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['image']) ) {
			$categories[$cnum]['category']['image_id'] = $rc['image']['image_id'];
		}
	}

	return array('stat'=>'ok', 'categories'=>$categories);	
}
?>
