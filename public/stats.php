<?php
//
// Description
// ===========
// This method will list the art catalog pieces sorted by category.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_artcatalog_stats($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
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
	// Get the category stats
	//
	$strsql = "SELECT IF(category='', 'Unknown', category) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "GROUP BY category "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	// error_log($strsql);
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
		. "GROUP BY media "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][1] = array('stat'=>array('name'=>'Media', 'sections'=>$rc['sections']));
	$rsp['stats']['media'] = $rc['sections'];

	//
	// Get the location stats
	//
	$strsql = "SELECT IF(location='', 'Unknown', location) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "GROUP BY location "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
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
		. "GROUP BY year "
		. "ORDER BY name "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
			'fields'=>array('name', 'count')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//$rsp['stats'][3] = array('stat'=>array('name'=>'Years', 'sections'=>$rc['sections']));
	$rsp['stats']['years'] = $rc['sections'];

	//
	// Get the total count
	//
	$strsql = "SELECT 'total', COUNT(*) AS total FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbCount($ciniki, $strsql, 'artcatalog', 'count');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$rsp['total'] = $rc['count']['total'];


	return $rsp;
}
?>
