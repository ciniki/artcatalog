<?php
//
// Description
// ===========
// This method will update a category names in the artcatalog.  This can be used to
// merge categories.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to the item is a part of.
// old_category:	The name of the old category.
// new_category:	The new name for the category.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_categoryUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'), 
        'category'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Category'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.categoryUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	$updated = 0;
	$fields = array('synopsis', 'description');
	foreach($fields as $f) {
		if( isset($args[$f]) ) {
			if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
				$detail_key = 'category-' . $f . '-' . $args['artcatalog_type'] . '-' . $args['category'];
			} else {
				$detail_key = 'category-' . $f . '-' . $args['category'];
			}

			//
			// Get the existing category description
			//
			$strsql = "SELECT detail_value "
				. "FROM ciniki_artcatalog_settings "
				. "WHERE ciniki_artcatalog_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND ciniki_artcatalog_settings.detail_key = '" . ciniki_core_dbQuote($ciniki, $detail_key) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'setting');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( !isset($rc['setting']) ) {
				$strsql = "INSERT INTO ciniki_artcatalog_settings (business_id, detail_key, detail_value, "
					. "date_added, last_updated) VALUES ("
					. "' " . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
					. ", '" . ciniki_core_dbQuote($ciniki, $detail_key) . "' "
					. ", '" . ciniki_core_dbQuote($ciniki, $args[$f]) . "' "
					. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
					. "";
				$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artcatalog');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
					'ciniki_artcatalog_history', $args['business_id'], 
					1, 'ciniki_artcatalog_settings', $detail_key, 'detail_value', $args[$f]);
				$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.setting',
					'args'=>array('id'=>$detail_key));
			} else {
				$strsql = "UPDATE ciniki_artcatalog_settings "
					. "SET detail_value = '" . ciniki_core_dbQuote($ciniki, $args[$f]) . "', "
					. "last_updated = UTC_TIMESTAMP() "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
					. "AND detail_key = '" . ciniki_core_dbQuote($ciniki, $detail_key) . "' "
					. "";
				$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artcatalog');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
					'ciniki_artcatalog_history', $args['business_id'], 
					2, 'ciniki_artcatalog_settings', $detail_key, 'detail_value', $args[$f]);
				$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.setting',
					'args'=>array('id'=>$detail_key));
			}
			$updated = 1;
		}
	}

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
	if( $updated > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
		ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artcatalog');
	}

	return array('stat'=>'ok');
}
?>
