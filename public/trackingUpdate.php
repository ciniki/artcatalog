<?php
//
// Description
// ===========
// This method will update the details for a tracking entry.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to the item is a part of.
//
// artcatalog_id:   (optional) The new ID of the artcatalog item the tracking is attached to.
//
// name:            The name of the place for tracking.
// external_number: The number assigned to the item by the place.
// start_date:      The date the item was added to the place.
// end_date:        The date the item was removed from the place.
// notes:           Any notes about this showing.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_trackingUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'tracking_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tracking'), 
        'artcatalog_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Item'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'external_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Number'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'End'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.trackingUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Check if permalink needs updating
    //
    if( isset($args['name']) || isset($args['start_date']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectGet');
        $rc = ciniki_core_objectGet($ciniki, $args['business_id'], 'ciniki.artcatalog.place', $args['tracking_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['object']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2327', 'msg'=>'Unable to find exhibition'));
        }
        if( isset($args['name']) && isset($args['start_date']) ) {
            $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name'] . '-' . ($args['start_date']==''?'0000-00-00':$args['start_date']));
        } elseif( isset($args['name']) ) {
            $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name'] . '-' . $rc['object']['start_date']);
        } elseif( isset($args['start_date']) ) {
            $args['permalink'] = ciniki_core_makePermalink($ciniki, $rc['object']['name'] . '-' . ($args['start_date']==''?'0000-00-00':$args['start_date']));
        }

    }

    //
    // Update tracking
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.artcatalog.place', $args['tracking_id'], $args, 0x07);
}
?>
