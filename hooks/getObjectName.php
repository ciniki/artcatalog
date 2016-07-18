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
function ciniki_artcatalog_hooks_getObjectName($ciniki, $business_id, $args) {

    // Set the default to not used
    $used = 'no';
    $count = 0;
    $msg = '';

    if( isset($args['object']) && $args['object'] == 'ciniki.artcatalog.item' 
        && isset($args['object_id']) && $args['object_id'] != '' ) {
        $strsql = "SELECT name "
            . "FROM ciniki_artcatalog "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['item']['name']) ) {
            return array('stat'=>'ok', 'name'=>'Art Catalog - ' . $rc['item']['name']);
        }
    }
    
    return array('stat'=>'noexist', 'err'=>array('pkg'=>'ciniki', 'code'=>'3591', 'msg'=>'Could not find item'));
}
?>
