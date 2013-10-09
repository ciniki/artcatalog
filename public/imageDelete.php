<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_imageDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'artcatalog_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'),
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.imageDelete', 0); 
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'removeImage');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Get the existing image information
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_artcatalog_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1303', 'msg'=>'Gallery image does not exist'));
	}
	$item = $rc['item'];

	//
	// Delete the reference to the image, and remove the image if no more references
	//
	$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
		'object'=>'ciniki.artcatalog.image',
		'object_id'=>$item['id']));
	if( $rc['stat'] == 'fail' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}

	//
	// Remove the image from the database
	//
	$strsql = "DELETE FROM ciniki_artcatalog_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' ";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}

	ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', 
		$args['business_id'], 1, 'ciniki_artcatalog_images', $args['artcatalog_image_id'], '*', '');

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

	//
	// Add to the sync queue so it will get pushed
	//
	$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.image', 
		'args'=>array('delete_uuid'=>$item['uuid'], 'delete_id'=>$item['id']));

	return array('stat'=>'ok');
}
?>
