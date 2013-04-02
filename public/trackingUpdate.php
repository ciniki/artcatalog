<?php
//
// Description
// ===========
// This method updates one or more elements of an existing item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to the item is a part of.
//
// artcatalog_id:	(optional) The new ID of the artcatalog item the tracking is attached to.
//
// name:			The name of the place for tracking.
// external_number:	The number assigned to the item by the place.
// start_date:		The date the item was added to the place.
// end_date:		The date the item was removed from the place.
// notes:			Any notes about this showing.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_trackingUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'tracking_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tracking'), 
        'artcatalog_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Item'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'external_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Number'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'End'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.trackingUpdate'); 
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Keep track if anything has been updated
	//
	$updated = 0;

	//
	// Start building the update SQL
	//
	$strsql = "UPDATE ciniki_artcatalog_tracking SET last_updated = UTC_TIMESTAMP()";

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'artcatalog_id',
		'name',
		'external_number',
		'start_date',
		'end_date',
		'notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
				'ciniki_artcatalog_history', $args['business_id'], 
				2, 'ciniki_artcatalog_tracking', $args['tracking_id'], $field, $args[$field]);
			$updated = 1;
		}
	}

	//
	// Only update the record, and last_updated if there is something to update, or lists were updated
	//
	if( $updated > 0 ) {
		$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['tracking_id']) . "' ";
		$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artcatalog');
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
			return $rc;
		}
		if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1010', 'msg'=>'Unable to update art'));
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

		//
		// Add to the sync queue so it will get pushed
		//
		$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.tracking', 
			'args'=>array('id'=>$args['tracking_id']));
	}

	return array('stat'=>'ok');
}
?>
