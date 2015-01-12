<?php
//
// Description
// -----------
// The mappings for int fields to text.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_maps($ciniki) {

	$maps = array();
	$maps['item'] = array(
		'typecode'=>array(
			''=>'unknown',
			'0'=>'unknown',
			'1'=>'painting',
			'2'=>'photograph',
			'3'=>'jewelry',
			'4'=>'sculpture',
			'5'=>'fibreart',
			'6'=>'craft',
			'7'=>'clothing',
			'8'=>'pottery',
			),
		'typepermalinks'=>array(
			'1'=>'paintings',
			'2'=>'photographs',
			'3'=>'jewelry',
			'4'=>'sculptures',
			'5'=>'fibrearts',
			'6'=>'crafts',
			'7'=>'clothing',
			'8'=>'pottery',
			),	
		'type'=>array(
			'1'=>'Painting',
			'2'=>'Photograph',
			'3'=>'Jewelry',
			'4'=>'Sculpture',
			'5'=>'Fibre Art',
			'6'=>'Craft',
			'7'=>'Clothing',
			'8'=>'Pottery',
			),
		);
	
	return array('stat'=>'ok', 'maps'=>$maps);
}
?>
