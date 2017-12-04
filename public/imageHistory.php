<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// artcatalog_image_id: The ID of the item in the art catalog to get the history for.
// field:               The field to get the history for. This can be any of the 
//                      elements returned by the ciniki.artcatalog.get method.
//
// Returns
// -------
// <history>
//  <action user_id="2" date="May 12, 2012 10:54 PM" value="photographs" age="2 months" user_display_name="Andrew" />
//  ...
// </history>
//
function ciniki_artcatalog_imageHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'artcatalog_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image ID'), 
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.imageHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', $args['tnid'], 'ciniki_artcatalog_images', $args['artcatalog_image_id'], $args['field']);
}
?>
