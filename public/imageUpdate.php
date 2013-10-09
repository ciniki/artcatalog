<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the image belongs to.
// name:				The name of the image.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_imageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Additional Image'), 
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Website Flags'), 
        'sequence'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sequence'), 
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.imageUpdate', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing image details
	//
	$strsql = "SELECT uuid, artcatalog_id, image_id FROM ciniki_artcatalog_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1339', 'msg'=>'Image not found'));
	}
	$item = $rc['item'];

	if( isset($args['name']) ) {
		if( $args['name'] != '' ) { 
			$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
		} else {
			// Use the UUID if image name is not specified
			$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($item['uuid'])));
		}
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink FROM ciniki_artcatalog_images "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['artcatalog_id']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1340', 'msg'=>'You already have an image with this name, please choose another name'));
		}
	} 

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add all the fields to the change log
	//
	$strsql = "UPDATE ciniki_artcatalog_images SET last_updated = UTC_TIMESTAMP()";

	$changelog_fields = array(
		'name',
		'permalink',
		'webflags',
		'sequence',
		'image_id',
		'description',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
				'ciniki_artcatalog_history', $args['business_id'], 
				2, 'ciniki_artcatalog_images', $args['artcatalog_image_id'], $field, $args[$field]);
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' "
		. "";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1341', 'msg'=>'Unable to update image'));	
	}

	//
	// Update image reference
	//
	if( isset($args['image_id']) && $item['image_id'] != $args['image_id']) {
		//
		// Remove the old reference
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
		$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
			'object'=>'ciniki.artcatalog.image', 
			'object_id'=>$args['artcatalog_image_id']));
		if( $rc['stat'] == 'fail' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
			return $rc;
		}

		//
		// Add the new reference
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refAdd');
		$rc = ciniki_images_refAdd($ciniki, $args['business_id'], array(
			'image_id'=>$args['image_id'], 
			'object'=>'ciniki.artcatalog.image', 
			'object_id'=>$args['artcatalog_image_id'],
			'object_field'=>'image_id'));
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
			return $rc;
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artcatalog');

	$ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.image', 
		'args'=>array('id'=>$args['artcatalog_image_id']));

	return array('stat'=>'ok');
}
?>
