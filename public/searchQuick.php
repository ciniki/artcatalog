<?php
//
// Description
// ===========
// This method will search the art catalog for pieces that contain the search text
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_artcatalog_searchQuick($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No search specified'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No limit specified'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.searchQuick'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	$strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, price, location "
//		. "IF(ciniki_artcatalog.category='', 'Uncategorized', ciniki_artcatalog.category) AS cname "
//		. "IF(ciniki_artcatalog.status=1, 'open', 'closed') AS status "
		. "FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR name like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR catalog_number like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR media like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR media like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR location like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR location like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. ") "
		. "";
	$strsql .= "ORDER BY name, location "
		. "";
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'pieces', 'fname'=>'id', 'name'=>'piece',
			'fields'=>array('id', 'name', 'image_id', 'media', 'catalog_number', 'size', 'framed_size', 'price', 'location')),
		));
	// error_log($strsql);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['pieces']) ) {
		return array('stat'=>'ok', 'pieces'=>array());
	}
	return array('stat'=>'ok', 'pieces'=>$rc['pieces']);
}
?>
