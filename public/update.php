<?php
//
// Description
// ===========
// This function will update an art catalog piece to the database.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_update($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No ID specified'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No type specified'),
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No image specified'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No flags specified'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No webflags specified'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No name specified'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No catalog number specified'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No category specified'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No year specified'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No media specified'), 
        'size'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No size specified'), 
        'framed_size'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No framed_size specified'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No price specified'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No location specified'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No description specified'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No inspiration specified'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No awards specified'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No notes specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( isset($args['name']) ) {
		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'piece');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'650', 'msg'=>'You already have artwork with this name, please choose another name'));
		}

	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbUpdate.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Start building the update SQL
	//
	$strsql = "UPDATE ciniki_artcatalog SET last_updated = UTC_TIMESTAMP()";

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'name',
		'permalink',
		'image_id',
		'type',
		'flags',
		'webflags',
		'catalog_number',
		'category',
		'year',
		'media',
		'size',
		'framed_size',
		'price',
		'location',
		'description',
		'inspiration',
		'awards',
		'notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'artcatalog', 'ciniki_artcatalog_history', $args['business_id'], 
				2, 'ciniki_artcatalog', $args['artcatalog_id'], $field, $args[$field]);
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' ";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'603', 'msg'=>'Unable to update art'));
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
