<?php
//
// Description
// ===========
// This function will update the web settings controlling the split page gallery.
// The artcatalog is searched for web visible items in different types.
//
// Arguments
// ---------
// ciniki:
// business_id: 		The ID of the business the request is for.
// 
// Returns
// -------
// <rsp stat="ok" />
//
function ciniki_artcatalog_updateWebSettings($ciniki, $business_id) {
	//
	// Get the current settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $business_id,
		'ciniki.web', 'settings', 'page-gallery-artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = $rc['settings'];
	
	//
	// Find the distinct types that are visible online
	//
	$strsql = "SELECT DISTINCT type "
		. "FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND (webflags&0x01) = 0 "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.artcatalog', 'types', 'type');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$types = $rc['types'];

	$maps = array(
		'1'=>'paintings',
		'2'=>'photographs',
		'3'=>'jewelry',
		'4'=>'sculptures',
		'5'=>'crafts',
		'6'=>'clothing',
		);

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	foreach($maps as $type => $name) {
		$field = "page-gallery-artcatalog-$name";
		//
		// Turn on the flag when type exists
		//
		if( isset($types[$type]) && (!isset($settings[$field]) || $settings[$field] != 'yes') ) {
			$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, "
				. "date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", 'yes' "
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = 'yes' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id, 
				2, 'ciniki_web_settings', $field, 'detail_value', 'yes');
			$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
				'args'=>array('id'=>$field));
		}
		//
		// Turn off when type doesn't exist
		//
		elseif( !isset($types[$type]) && (!isset($settings[$field]) || $settings[$field] != 'no') ) {
			$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, "
				. "date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", 'no' "
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = 'no' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id, 
				2, 'ciniki_web_settings', $field, 'detail_value', 'no');
			$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
				'args'=>array('id'=>$field));
		}
	}

	return array('stat'=>'ok');
}
?>
