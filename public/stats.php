<?php
//
// Description
// ===========
// This method will get the number of items in each category, media type, location and years.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the stats for.
// type:			(optional) Only get the stats for a particular type.
//
//					1 - Painting
//					2 - Photograph
//					3 - Jewelry
//					4 - Sculpture
// 
// Returns
// -------
// <stats>
//		<types>
//			<section type="painting" name="Paintings" count="21" />
//		</types>
//		<categories>
//			<section name="Landscape" count="14" />
//			<section name="Portrait" count="7" />
//		</categories>
//		<media>
//			<section name="Oil" count="12" />
//			<section name="Pastel" count="9" />
//		</media>
//		<locations>
//			<section name="Home" count="12" />
//			<section name="Gallery" count="4" />
//			<section name="Private Collection" count="5" />
//		</locations>
//		<years>
//			<section name="2012" count="15" />
//			<section name="2011" count="6" />
//		</years>
//		<lists>
//			<section name="County Fair" count="15" />
//			<section name="Private Gallery" count="6" />
//		</lists>
// </stats>
//
function ciniki_artcatalog_stats($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'type'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No type specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( isset($args['type']) && $args['type'] != '' ) {
		if( $args['type'] == 'painting' ) {
			$args['type_id'] = 1;
		} elseif( $args['type'] == 'photograph' ) {
			$args['type_id'] = 2;
		} elseif( $args['type'] == 'sculpture' ) {
			$args['type_id'] = 3;
		} elseif( $args['type'] == 'jewelry' ) {
			$args['type_id'] = 4;
		} else {
			$args['type_id'] = 0;
		}
	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.stats'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');

	$rsp = array('stat'=>'ok', 'stats'=>array());
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
	
	//
	// Get type stats
	//
	$strsql = "SELECT type, type AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "GROUP BY type "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'type', 'name'=>'section',
			'fields'=>array('type', 'name', 'count'), 
			'maps'=>array('type'=>array(''=>'unknown', '1'=>'painting', '2'=>'photograph', '3'=>'sculpture', '4'=>'jewelry'),
				'name'=>array(''=>'Unknown', '1'=>'Paintings', '2'=>'Photographs', '3'=>'Sculptures', '4'=>'Jewelry'))),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][0] = array('stat'=>array('name'=>'Categories', 'sections'=>$rc['sections']));
	$rsp['stats']['types'] = $rc['sections'];

	//
	// Get the category stats
	//
	$strsql = "SELECT IF(category='', 'Unknown', category) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "";
	}
	$strsql .= ""
		. "GROUP BY category "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][0] = array('stat'=>array('name'=>'Categories', 'sections'=>$rc['sections']));
	$rsp['stats']['categories'] = $rc['sections'];
	
	//
	// Get the media stats
	//
	$strsql = "SELECT IF(media='', 'Unknown', media) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "";
	}
	$strsql .= ""
		. "AND ciniki_artcatalog.type = 1 "
		. "GROUP BY media "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][1] = array('stat'=>array('name'=>'Media', 'sections'=>$rc['sections']));
	if( isset($rc['sections']) ) {
		$rsp['stats']['media'] = $rc['sections'];
	}

	//
	// Get the location stats
	//
	$strsql = "SELECT IF(location='', 'Unknown', location) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "";
	}
	$strsql .= ""
		. "GROUP BY location "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][2] = array('stat'=>array('name'=>'Locations', 'sections'=>$rc['sections']));
	$rsp['stats']['locations'] = $rc['sections'];

	//
	// Get the year stats
	//
	$strsql = "SELECT IF(year='', 'Unknown', year) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "";
	}
	$strsql .= ""
		. "GROUP BY year "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][3] = array('stat'=>array('name'=>'Years', 'sections'=>$rc['sections']));
	$rsp['stats']['years'] = $rc['sections'];

	//
	// Get the lists stats
	//
	$strsql = "SELECT IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS name, COUNT(*) AS count "
		. "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
		. "AND ciniki_artcatalog_tags.tag_type = 1 "
		. "";
	if( isset($args['type_id']) && $args['type_id'] > 0 ) {
		$strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "";
	}
	$strsql .= "GROUP BY tag_name "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		error_log($strsql);
		return $rc;
	}
	if( isset($rc['sections']) ) {
		$rsp['stats']['lists'] = $rc['sections'];
	}

	//
	// Get the total count
	//
	$strsql = "SELECT 'total', COUNT(*) AS total FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
//	if( isset($args['type']) && $args['type'] != '' ) {
//		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' "
//			. "";
//	}
	$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.artcatalog', 'count');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$rsp['total'] = $rc['count']['total'];


	return $rsp;
}
?>
