<?php
//
// Description
// -----------
// The objects for the artcatalog.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_objects($ciniki) {
	//
	// Object definitions
	//
	$objects = array();
	$objects['item'] = array(
		'name'=>'Art Catalog Item',
		'sync'=>'yes',
		'table'=>'ciniki_artcatalog',
		'fields'=>array(
			'name'=>array(),
			'permalink'=>array(),
			'type'=>array(),
			'status'=>array(),
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
		'sync'=>'yes',
		'table'=>'ciniki_artcatalog_tags',
		'fields'=>array(
			'artcatalog_id'=>array('ref'=>'ciniki.artcatalog.item'),
			'tag_type'=>array(),
			'tag_name'=>array(),
			),
		'history_table'=>'ciniki_artcatalog_history',
		);
	$objects['image'] = array(
		'name'=>'Image',
		'sync'=>'yes',
		'table'=>'ciniki_artcatalog_images',
		'fields'=>array(
			'artcatalog_id'=>array('ref'=>'ciniki.artcatalog.item'),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'sequence'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'description'=>array(),
			),
		'history_table'=>'ciniki_artcatalog_history',
		);
	$objects['place'] = array(
		'name'=>'tracking',
		'sync'=>'yes',
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
		'history_table'=>'ciniki_artcatalog_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
