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
function ciniki_artcatalog_listBySection($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'status'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No status specified'), 
        'section'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No section specified'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.notesList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');


	if( !isset($args['section']) || $args['section'] == 'category' ) {
		$strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, price, flags, location, "
			. "IF(ciniki_artcatalog.category='', '', ciniki_artcatalog.category) AS sname "
//		. "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
//		. "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
			. "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY sname, name "
			. "";
	} elseif( $args['section'] == 'media' ) {
		$strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, price, flags, location, "
			. "IF(ciniki_artcatalog.media='', '', ciniki_artcatalog.media) AS sname "
			. "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY sname, name "
			. "";
	} elseif( $args['section'] == 'location' ) {
		$strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, price, flags, location, "
			. "IF(ciniki_artcatalog.location='', '', ciniki_artcatalog.location) AS sname "
			. "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY sname, name "
			. "";
	}
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'sections', 'fname'=>'sname', 'name'=>'section',
			'fields'=>array('sname')),
		array('container'=>'pieces', 'fname'=>'id', 'name'=>'piece',
			'fields'=>array('id', 'name', 'image_id', 'media', 'catalog_number', 'size', 'framed_size', 'price', 'flags', 'location')),
		));
	// error_log($strsql);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sections']) ) {
		return array('stat'=>'ok', 'sections'=>array());
	}
	return array('stat'=>'ok', 'sections'=>$rc['sections']);
}
?>
