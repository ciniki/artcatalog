<?php
//
// Description
// ===========
// This function will search for items in the artcatalog that can be included
// an on invoice.
//
// Arguments
// =========
// ciniki:
// 
// Returns
// =======
// <rsp stat="ok" />
//
function ciniki_artcatalog_sapos_itemSearch($ciniki, $business_id, $args) {

    if( $args['start_needle'] == '' ) {
        return array('stat'=>'ok', 'items'=>array());
    }

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
    $strsql = "SELECT ciniki_artcatalog.id, "
        . "ciniki_artcatalog.name, "
        . "ciniki_artcatalog.size, "
        . "ciniki_artcatalog.framed_size, "
        . "ciniki_artcatalog.price, "
        . "ciniki_artcatalog.media "
        . "FROM ciniki_artcatalog "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (ciniki_artcatalog.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_artcatalog.name LIKE ' %" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . ") "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $items = array();
    foreach($rc['rows'] as $item) {
        $details = array(
            'status'=>0,
            'object'=>'ciniki.artcatalog.item',
            'object_id'=>$item['id'],
            'description'=>$item['name'],
            'quantity'=>1,
            'flags'=>0x40,
            'unit_amount'=>0,
            'unit_discount_amount'=>0,
            'unit_discount_percentage'=>0,
            'taxtype_id'=>$taxtype_id, 
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
        $items[] = array('item'=>$details);
    }

    return array('stat'=>'ok', 'items'=>$items);        
}
?>
