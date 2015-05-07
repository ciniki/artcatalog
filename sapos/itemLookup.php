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
function ciniki_artcatalog_sapos_itemLookup($ciniki, $business_id, $args) {
	if( !isset($args['object']) || $args['object'] == ''
		|| !isset($args['object_id']) || $args['object_id'] == '' 
		) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1970', 'msg'=>'No event specified.'));
	}

	if( isset($args['object']) && $args['object'] == 'ciniki.artcatalog.item'
		&& isset($args['object_id']) && $args['object_id'] != '' ) {
		//
		// Query for the taxes for artcatalog
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'business_id', $business_id,
			'ciniki.artcatalog', 'taxes', 'taxes');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['taxes']) ) {
			$tax_settings = $rc['taxes'];
		} else {
			$tax_settings = array();
		}

		//
		// Set the default taxtype for the item
		//
		$taxtype_id = 0;
		if( isset($tax_settings['taxes-default-taxtype']) ) {
			$taxtype_id = $tax_settings['taxes-default-taxtype'];
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
			. "WHERE ciniki_artcatalog.id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
			. "AND ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['item']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1938', 'msg'=>'Item not found'));
		}
		$item = $rc['item'];

		$details = array(
			'status'=>0,
			'object'=>'ciniki.artcatalog.item',
			'object_id'=>$args['object_id'],
			'description'=>$item['name'],
			'quantity'=>1,
			'flags'=>0,
			'price_id'=>0,
			'code'=>'',
			'unit_amount'=>0,
			'unit_discount_amount'=>0,
			'unit_discount_percentage'=>0,
			'taxtype_id'=>$taxtype_id, 
			'notes'=>'',
			'shipped_quantity'=>0,
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
		return array('stat'=>'ok', 'item'=>$details);
	}

	return array('stat'=>'ok');		
}
?>
