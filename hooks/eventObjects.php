<?php
//
// Description
// -----------
// This function returns the list of objects available to the event to be linked for additional images.
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_artcatalog_hooks_eventObjects($ciniki, $business_id, $args) {

    $objects = array();

    if( !isset($args['event_id']) ) {
        return array('stat'=>'ok', 'objects'=>$objects);
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the objects
    //
    $strsql = "SELECT DISTINCT CONCAT('ciniki.artcatalog.place:', permalink) AS id, "
        . "IF(start_date!='0000-00-00', CONCAT_WS(' - ', 'Art Catalog', name, DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "')), CONCAT_WS(' - ' , 'Art Catalog', name)) AS name, "
        . "IFNULL(DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS start_date, "
        . "IFNULL(DATE_FORMAT(end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS end_date "
        . "FROM ciniki_artcatalog_tracking "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "ORDER BY start_date DESC, name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'objects', 'fname'=>'name', 'name'=>'object',
            'fields'=>array('id', 'name', 'start_date', 'end_date')),
            ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rc;
}
?>
