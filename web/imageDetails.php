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
function ciniki_artcatalog_web_imageDetails($ciniki, $settings, $business_id, $permalink) {

	$strsql = "SELECT ciniki_artcatalog.id, name, permalink, image_id, type, "
		. "catalog_number, category, year, flags, webflags, "
		. "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
		. "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
		. "IF((ciniki_artcatalog.webflags&0x01)=1, 'yes', 'no') AS hidden, "
		. "media, size, framed_size, price, location, description, awards, notes, "
		. "date_added, last_updated "
		. "FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'piece');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['piece']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'651', 'msg'=>'Unable to find artwork'));
	}
	$image = array('title'=>$rc['piece']['name'],
		'category'=>$rc['piece']['category'],
		'image_id'=>$rc['piece']['image_id'],
		'details'=>'',
		'description'=>$rc['piece']['description'],
		'awards'=>$rc['piece']['awards'],
		'date_added'=>$rc['piece']['date_added'],
		'last_updated'=>$rc['piece']['last_updated']);
	$comma = '';
	if( $rc['piece']['size'] != '' ) {
		$image['details'] .= $rc['piece']['size'];
		$comma = ', ';
	}
	if( $rc['piece']['framed_size'] != '' ) {
		$image['details'] .= ' (framed: ' . $rc['piece']['framed_size'] . ')';
		$comma = ', ';
	}
	if( $rc['piece']['price'] != '' && $rc['piece']['forsale'] == 'yes' ) {
		$image['details'] .= $comma . preg_replace('/^\s*([^$])/', '\$$1', $rc['piece']['price']);
		$comma = ', ';
	}
	if( isset($rc['piece']['sold']) && $rc['piece']['sold'] == 'yes' ) {
		$image['details'] .= " <b> SOLD</b>";
		$comma = ', ';
	}

	return array('stat'=>'ok', 'image'=>$image);
}
?>
