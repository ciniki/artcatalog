<?php
//
// Description
// ===========
// This function will add a new art catalog piece to the database.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_add($ciniki) {
	error_log('test');
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'type'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No type specified'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'errmsg'=>'No location specified'), 
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'default'=>'0', 'errmsg'=>'No image specified'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No name specified'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'errmsg'=>'No catalog number specified'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No category specified'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No year specified'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'errmsg'=>'No media specified'), 
        'size'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'errmsg'=>'No size specified'), 
        'framed_size'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'errmsg'=>'No framed_size specified'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No price specified'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No location specified'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No description specified'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No awards specified'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No notes specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.add'); 
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
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddChangeLog.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id, name, permalink FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'piece');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'649', 'msg'=>'You already have artwork with this name, please choose another name'));
	}

	//
	// Add the artcatalog to the database
	//
	$strsql = "INSERT INTO ciniki_artcatalog (uuid, business_id, name, permalink, type, flags, image_id, catalog_number, category, year, "
		. "media, size, framed_size, price, location, description, awards, notes, user_id, "
		. "date_added, last_updated) VALUES ("
		. "UUID(), "
		. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['name']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['type']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['flags']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['image_id']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['catalog_number']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['category']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['year']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['media']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['size']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['framed_size']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['price']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['location']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['description']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['awards']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['notes']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "', "
		. "UTC_TIMESTAMP(), UTC_TIMESTAMP())"
		. "";
	$rc = ciniki_core_dbInsert($ciniki, $strsql, 'artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return $rc;
	}
	if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'592', 'msg'=>'Unable to add item'));
	}
	$artcatalog_id = $rc['insert_id'];

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'name',
		'permalink',
		'type',
		'flags',
		'catalog_number',
		'category',
		'year',
		'media',
		'size',
		'framed_size',
		'price',
		'location',
		'description',
		'awards',
		'notes',
		);
	foreach($changelog_fields as $field) {
		$insert_name = $field;
		if( isset($ciniki['request']['args'][$field]) && $ciniki['request']['args'][$field] != '' ) {
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'artcatalog', $args['business_id'], 
				'ciniki_artcatalog', $artcatalog_id, $insert_name, $ciniki['request']['args'][$field]);
		}
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'id'=>$artcatalog_id);
}
?>
