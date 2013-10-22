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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
	$rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.getHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( $args['field'] == 'lists' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryTags');
		return ciniki_core_dbGetModuleHistoryTags($ciniki, 'ciniki.artcatalog', 
			'ciniki_artcatalog_history', $args['business_id'], 
			'ciniki_artcatalog_tags', $args['artcatalog_id'], 'tag_name', 'artcatalog_id', 1);
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', $args['business_id'], 'ciniki_artcatalog', $args['artcatalog_id'], $args['field']);
}
?>
