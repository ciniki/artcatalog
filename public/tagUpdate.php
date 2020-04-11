<?php
//
// Description
// ===========
// This method will change a group of tag names
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to the item is a part of.
// field:           The field to change (category, media, location, year)
// old_value:   The name of the old value.
// new_value:   The new name for the value.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_tagUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'tag_type'=>array('required'=>'yes', 'blank'=>'yes', 'validlist'=>array(100), 'name'=>'Type'), 
        'old_value'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Old Name'), 
        'new_value'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'New Name'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.tagUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the list of tags
    //
    $strsql = "SELECT id, tag_type, tag_name "
        . "FROM ciniki_artcatalog_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
        . "AND tag_name = '" . ciniki_core_dbQuote($ciniki, $args['old_value']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.57', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    $tags = isset($rc['rows']) ? $rc['rows'] : array();

    //
    // Update the tags
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    foreach($tags as $tag) {
        //
        // Update the tag entry
        //
        $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.artcatalog.tag', $tag['id'], array('tag_name'=>$args['new_value']), 0x04);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.58', 'msg'=>'Unable to update the tag'));
        }
    }
    
    //
    // Commit the database changes
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'artcatalog');

    return array('stat'=>'ok');
}
?>
