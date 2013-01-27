<?php
//
// Description
// -----------
// This function will go through the history of the ciniki.customers module and add missing history elements.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_historyFix($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
	$rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.historyFix', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');

	//
	// Check for items that are missing a add value in history
	//
	$fields = array('uuid', 'name', 'permalink', 'type', 'flags', 'webflags', 'image_id', 'catalog_number', 'category', 'year', 'media', 'size', 'framed_size', 'price', 'location', 'awards', 'notes', 'description', 'inspiration', 'user_id');
	foreach($fields as $field) {
		//
		// Get the list of artcatalog which don't have a history for the field
		//
		$strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.$field AS field_value, "
			. "UNIX_TIMESTAMP(ciniki_artcatalog.date_added) AS date_added, "
			. "UNIX_TIMESTAMP(ciniki_artcatalog.last_updated) AS last_updated "
			. "FROM ciniki_artcatalog "
			. "LEFT JOIN ciniki_artcatalog_history ON (ciniki_artcatalog.id = ciniki_artcatalog_history.table_key "
				. "AND ciniki_artcatalog_history.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND ciniki_artcatalog_history.table_name = 'ciniki_artcatalog' "
				. "AND (ciniki_artcatalog_history.action = 1 OR ciniki_artcatalog_history.action = 2) "
				. "AND ciniki_artcatalog_history.table_field = '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ") "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_artcatalog.$field <> '' "
			. "AND ciniki_artcatalog.$field <> '0000-00-00' "
			. "AND ciniki_artcatalog.$field <> '0000-00-00 00:00:00' "
			. "AND ciniki_artcatalog_history.uuid IS NULL "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'history');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
	
		$elements = $rc['rows'];
		foreach($elements AS $rid => $row) {
			$strsql = "INSERT INTO ciniki_artcatalog_history (uuid, business_id, user_id, session, action, "
				. "table_name, table_key, table_field, new_value, log_date) VALUES ("
				. "UUID(), "
				. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $ciniki['session']['change_log_id']) . "', "
				. "'1', 'ciniki_artcatalog', "
				. "'" . ciniki_core_dbQuote($ciniki, $row['id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $field) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $row['field_value']) . "', "
				. "FROM_UNIXTIME('" . ciniki_core_dbQuote($ciniki, $row['date_added']) . "') "
				. ")";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artcatalog');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}
	}

	//
	// Check for items that are missing a add value in history
	//
	$fields = array('uuid', 'artcatalog_id','tag_type','tag_name');
	foreach($fields as $field) {
		//
		// Get the list of address which don't have a history for the field
		//
		$strsql = "SELECT ciniki_artcatalog_tags.id, ciniki_artcatalog_tags.$field AS field_value, "
			. "UNIX_TIMESTAMP(ciniki_artcatalog_tags.date_added) AS date_added, "
			. "UNIX_TIMESTAMP(ciniki_artcatalog_tags.last_updated) AS last_updated "
			. "FROM ciniki_artcatalog_tags "
			. "LEFT JOIN ciniki_artcatalog ON (ciniki_artcatalog_tags.artcatalog_id = ciniki_artcatalog.id "
				. ") "
			. "LEFT JOIN ciniki_artcatalog_history ON (ciniki_artcatalog_tags.id = ciniki_artcatalog_history.table_key "
				. "AND ciniki_artcatalog_history.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND ciniki_artcatalog_history.table_name = 'ciniki_artcatalog_tags' "
				. "AND (ciniki_artcatalog_history.action = 1 OR ciniki_artcatalog_history.action = 2) "
				. "AND ciniki_artcatalog_history.table_field = '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ") "
			. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_artcatalog_tags.$field <> '' "
			. "AND ciniki_artcatalog_tags.$field <> '0000-00-00' "
			. "AND ciniki_artcatalog_tags.$field <> '0000-00-00 00:00:00' "
			. "AND ciniki_artcatalog_history.uuid IS NULL "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'history');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
	
		$elements = $rc['rows'];
		foreach($elements AS $rid => $row) {
			$strsql = "INSERT INTO ciniki_artcatalog_history (uuid, business_id, user_id, session, action, "
				. "table_name, table_key, table_field, new_value, log_date) VALUES ("
				. "UUID(), "
				. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $ciniki['session']['change_log_id']) . "', "
				. "'1', 'ciniki_artcatalog_tags', "
				. "'" . ciniki_core_dbQuote($ciniki, $row['id']) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $field) . "', "
				. "'" . ciniki_core_dbQuote($ciniki, $row['field_value']) . "', "
				. "FROM_UNIXTIME('" . ciniki_core_dbQuote($ciniki, $row['date_added']) . "') "
				. ")";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artcatalog');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}
	}

	//
	// Check for items missing a UUID
	//
	$strsql = "UPDATE ciniki_artcatalog_history SET uuid = UUID() WHERE uuid = ''";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Remote any entries with blank table_key, they are useless we don't know what they were attached to
	//
	$strsql = "DELETE FROM ciniki_artcatalog_history WHERE table_key = ''";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}


	return array('stat'=>'ok');
}
?>
