<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_sync_objects($ciniki, &$sync, $business_id, $args) {
	
	//
	// NOTES: When pushing a change, grab the history for the current session
	// When increment/partial/full, sync history on it's own
	//

	//
	// Working on version 2 of sync, completely object based
	//
	$objects = array();
	$objects['item'] = array(
		'name'=>'Art Catalog Item',
		'table'=>'ciniki_artcatalog',
		'fields'=>array(
			'name'=>array(),
			'permalink'=>array(),
			'type'=>array(),
			'flags'=>array(),
			'webflags'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'catalog_number'=>array(),
			'category'=>array(),
			'year'=>array(),
			'month'=>array(),
			'day'=>array(),
			'media'=>array(),
			'size'=>array(),
			'framed_size'=>array(),
			'price'=>array(),
			'location'=>array(),
			'awards'=>array(),
			'notes'=>array(),
			'description'=>array(),
			'inspiration'=>array(),
			'user_id'=>array('ref'=>'ciniki.users.user'),
			),
		'history_table'=>'ciniki_artcatalog_history',
		);
	$objects['tag'] = array(
		'name'=>'Art Catalog Tag',
		'table'=>'ciniki_artcatalog_tags',
		'fields'=>array(
			'artcatalog_id'=>array('ref'=>'ciniki.artcatalog.item'),
			'tag_type'=>array(),
			'tag_name'=>array(),
			),
		'history_table'=>'ciniki_artcatalog_history',
		);
	$objects['tracking'] = array(
		'name'=>'Art Catalog Tracking',
		'table'=>'ciniki_artcatalog_tracking',
		'fields'=>array(
			'artcatalog_id'=>array('ref'=>'ciniki.artcatalog.item'),
			'name'=>array(),
			'external_number'=>array(),
			'start_date'=>array(),
			'end_date'=>array(),
			'notes'=>array(),
			),
		'history_table'=>'ciniki_artcatalog_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Art Catalog Settings',
		'table'=>'ciniki_artcatalog_settings',
		'history_table'=>'ciniki_artcatalog_history,
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
