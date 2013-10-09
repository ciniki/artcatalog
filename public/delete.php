<?php
//
// Description
// ===========
// This method will remove an item from the art catalog.  All information
// will be removed, so be sure you want it deleted.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 		The ID of the business to remove the item from.
// artcatalog_id:		The ID of the item in the catalog to be removed.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_delete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.delete'); 
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Get the uuid of the artcatalog item to be deleted
	//
	$strsql = "SELECT uuid, image_id FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['artcatalog']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'107', 'msg'=>'Unable to find existing item'));
	}
	$uuid = $rc['artcatalog']['uuid'];
	$image_id = $rc['artcatalog']['image_id'];

	//
	// Remove any tags for the artcatalog item
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsDelete');
	$rc = ciniki_core_tagsDelete($ciniki, 'ciniki.artcatalog', 'tag', $args['business_id'], 
		'ciniki_artcatalog_tags', 'ciniki_artcatalog_history', 'artcatalog_id', $args['artcatalog_id']);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}

	//
	// Remove any tracking items
	//
	$strsql = "SELECT id, uuid FROM ciniki_artcatalog_tracking "
		. "WHERE artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'tracking');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) ) {
		foreach($rc['rows'] as $rid => $row) {
			$strsql = "DELETE FROM ciniki_artcatalog_tracking "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
				. "";
			$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
				return $rc;
			}
			if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1351', 'msg'=>'Unable to delete tracking'));
			}

			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
				'ciniki_artcatalog_history', $args['business_id'], 
				3, 'ciniki_artcatalog_tracking', $row['id'], '*', '');
			$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.tracking',
				'args'=>array('delete_uuid'=>$row['uuid'], 'delete_id'=>$row['id']));
		}
	}


	//
	// Remove any additional images
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_artcatalog_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'image');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
		foreach($rc['rows'] as $rid => $row) {
			//
			// Delete the reference to the image, and remove the image if no more references
			//
			$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
				'object'=>'ciniki.artcatalog.image',
				'object_id'=>$row['id']));
			if( $rc['stat'] == 'fail' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
				return $rc;
			}

			//
			// Remove the image from the database
			//
			$strsql = "DELETE FROM ciniki_artcatalog_images "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND id = '" . ciniki_core_dbQuote($ciniki, $row['id']) . "' ";
			$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
				return $rc;
			}

			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', 
				$args['business_id'], 1, 'ciniki_artcatalog_images', $row['id'], '*', '');
			$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.image',
				'args'=>array('delete_uuid'=>$row['uuid'], 'delete_id'=>$row['id']));
		}
	}

	//
	// Start building the delete SQL
	//
	$strsql = "DELETE FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "";

	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'607', 'msg'=>'Unable to delete art'));
	}

	$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', 
		$args['business_id'], 3, 'ciniki_artcatalog', $args['artcatalog_id'], '*', '');

	//
	// Remove the reference, and remove image if no more references
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
	$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
		'object'=>'ciniki.artcatalog.item', 
		'object_id'=>$args['artcatalog_id']));
	if( $rc['stat'] == 'fail' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artcatalog');

	$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.item', 
		'args'=>array('delete_uuid'=>$uuid, 'delete_id'=>$args['artcatalog_id']));

	return array('stat'=>'ok');
}
?>
