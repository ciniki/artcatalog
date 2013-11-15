<?php
//
// Description
// ===========
// This function will lookup the details about an object and return the information
// required for a line item on an invoice.
//
// Arguments
// =========
// ciniki:
// 
// Returns
// =======
// <rsp stat="ok" />
//
function ciniki_artcatalog_sapos_itemDetails($ciniki, $business_id, $object_id) {
	if( $object_id == '' || $object_id < 1 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1152', 'msg'=>'Invalid item'));
	}

	//
	// Prepare the query
	//
	$strsql = "SELECT ciniki_artcatalog.name, "
		. "ciniki_artcatalog.size, "
		. "ciniki_artcatalog.framed_size, "
		. "ciniki_artcatalog.price, "
		. "ciniki_artcatalog.media "
		. "FROM ciniki_artcatalog "
		. "WHERE ciniki_artcatalog.id = '" . ciniki_core_dbQuote($ciniki, $object_id) . "' "
		. "AND ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1122', 'msg'=>'Item not found'));
	}
	$item = $rc['item'];

	$details = array(
		'status'=>0,
		'object'=>'ciniki.artcatalog.item',
		'object_id'=>$object_id,
		'description'=>$item['name'],
		'quantity'=>1,
		'unit_amount'=>0,
		'taxes'=>0xffff,		// Apply all taxes
		'notes'=>'',
		);

	if( $item['price'] != '' ) {
		$details['unit_amount'] = $item['price'];
	}
	if( $item['media'] != '' ) {
		$details['description'] .= ', ' . $item['media'];
	}
	if( $item['framed_size'] != '' ) {
		$details['description'] .= ', framed ' . $item['framed_size'];
	} elseif( $item['size'] != '' ) {
		$details['description'] .= ', unframed ' . $item['size'];
	}

	return array('stat'=>'ok', 'details'=>$details);		
}
?>
