<?php
//
// Description
// ===========
// This method will add a new item to the art catalog.  The image for the item
// must be uploaded separately into the ciniki images module.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to add the tracking to.
//
// artcatalog_id:   The ID of the artcatalog item to add the tracking for.
//
// name:            The name of the place for tracking.
// external_number: The number assigned to the item by the place.
// start_date:      The date the item was added to the place.
// end_date:        The date the item was removed from the place.
// notes:           Any notes about this showing.
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_trackingAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Artcatalog Item'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'external_number'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Number'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'date', 'name'=>'Start'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'date', 'name'=>'End'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Notes'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    if( $args['artcatalog_id'] == '0' ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1012', 'msg'=>'No artcatalog item specified'));
    }

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.trackingAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    if( isset($args['name']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name'] . '-' . ($args['start_date']==''?'0000-00-00':$args['start_date']));
    }

    //
    // Update tracking
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.artcatalog.place', $args);
}
?>
