<?php
//
// Description
// -----------
// This method will update one or more settings for the artcatalog module.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_settingsUpdate(&$ciniki) {
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
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.settingsUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Grab the settings for the business from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_artcatalog_settings', 'business_id', $args['business_id'], 'ciniki.artcatalog', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = $rc['settings'];

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   


	//
	// The list of allowed fields for updating
	//
	$changelog_fields = array(
		'enable-lists',
		'enable-tracking',
		'enable-inspiration',
		'taxes-default-taxtype',
		);

	//
	// Get the list of categories
	//
	$strsql = "SELECT DISTINCT category "
		. "FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'category');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	foreach($rc['rows'] as $row) {
		if( $row['category'] != '' ) {
			$changelog_fields[] = 'category-description-' . $row['category'];
			$changelog_fields[] = 'category-synopsis-' . $row['category'];
			for($i=1;$i<10;$i++) {
				$changelog_fields[] = "category-description-$i-" . $row['category'];
				$changelog_fields[] = "category-synopsis-$i-" . $row['category'];
			}
		}
	}

	//
	// Check each valid setting and see if a new value was passed in the arguments for it.
	// Insert or update the entry in the ciniki_artcatalog_settings table
	//
	foreach($changelog_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) 
			&& (!isset($settings[$field]) || $ciniki['request']['args'][$field] != $settings[$field]) ) {
			$strsql = "INSERT INTO ciniki_artcatalog_settings (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artcatalog');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', $args['business_id'], 
				2, 'ciniki_artcatalog_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
			$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.setting', 
				'args'=>array('id'=>$field));
		}
	}

	//
	// Check for category-descriptions
	//

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artcatalog');

	return array('stat'=>'ok');
}
?>
