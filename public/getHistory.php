<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an
// art catalog item.  This method is typically used by the UI to display a list of changes
// that have occured on an element through time.  This information can be used to revert
// elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// artcatalog_id:		The ID of the item in the art catalog to get the history for.
// field:				The field to get the history for. This can be any of the 
//						elements returned by the ciniki.artcatalog.get method.
//
// Returns
// -------
// <history>
//	<action user_id="2" date="May 12, 2012 10:54 PM" value="photographs" age="2 months" user_display_name="Andrew" />
//	...
// </history>
//
function ciniki_artcatalog_getHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No item specified'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No field specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
	$rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.getHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbGetModuleHistory.php');
	return ciniki_core_dbGetModuleHistory($ciniki, 'artcatalog', 'ciniki_artcatalog_history', $args['business_id'], 'ciniki_artcatalog', $args['artcatalog_id'], $args['field']);
}
?>
