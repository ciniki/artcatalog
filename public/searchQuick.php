<?php
//
// Description
// ===========
// This method will search the art catalog for items where start_needle matches one
// of the fields: name, catalog_number, media, location or notes.  The search looks
// for fields that start with start_needle, or are preceeded by a space and the start_needle.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to search.
//
// start_needle:	The search string to search the field for.
//
// limit:			(optional) Limit the number of results to be returned. 
//					If the limit is not specified, the default is 25.
// 
// Returns
// -------
// <items>
//		<item id="2434" name="Black River" image_id="3872" media="Pastel" catalog_number="20120316"
//			size="8x10" framed_size="12x14" price="350" location="Home" />
//		<item id="1854" name="Flowing Stream" image_id="3871" media="Oil" catalog_number="20120219"
//			size="8x10" framed_size="12x14" price="350" location="Home" />
//		...
// </items>
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
			. "OR notes like '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR notes like '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. ") "
		. "";
	$strsql .= "ORDER BY name, location "
		. "";
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'items', 'fname'=>'id', 'name'=>'item',
			'fields'=>array('id', 'name', 'image_id', 'media', 'catalog_number', 'size', 'framed_size', 'price', 'location')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['items']) ) {
		return array('stat'=>'ok', 'items'=>array());
	}
	return array('stat'=>'ok', 'items'=>$rc['items']);
}
?>
