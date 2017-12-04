<?php
//
// Description
// ===========
// This method will update a field names in the artcatalog.  This can be used to
// merge fields.
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
function ciniki_artcatalog_fieldUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'field'=>array('required'=>'yes', 'blank'=>'yes', 'validlist'=>array('category','media','location','year'), 'name'=>'Field'), 
        'old_value'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Old value'), 
        'new_value'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'New value'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.fieldUpdate'); 
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
    // Get the settings for category synopsis and descriptions
    //
    if( $args['field'] == 'category' && $args['old_value'] != $args['new_value'] ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash'); 
        $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 
            'tnid', $args['tnid'], 'ciniki.artcatalog', 'settings', 'category');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['settings']) ) {
            $settings = $rc['settings'];
        } else {
            $settings = array();
        }
    
        //
        // When the category name changes, then check through for category details 
        // that need to be updated as well
        //
        $fields = array('synopsis', 'description');
        foreach($fields as $f) {
            if( isset($args['artcatalog_type']) && $args['artcatalog_type'] > 0 ) {
                $old_detail_key = 'category-' . $f . '-' . $args['artcatalog_type'] . '-' . $args['old_value'];
                $new_detail_key = 'category-' . $f . '-' . $args['artcatalog_type'] . '-' . $args['new_value'];
            } else {
                $old_detail_key = 'category-' . $f . '-' . $args['old_value'];
                $new_detail_key = 'category-' . $f . '-' . $args['new_value'];
            }

            if( isset($settings[$old_detail_key]) ) {
                $old_setting = $settings[$old_detail_key];
                //
                // Remove old value, the 
                //
                $strsql = "DELETE FROM ciniki_artcatalog_settings "
                    . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                    . "AND detail_key = '" . ciniki_core_dbQuote($ciniki, $old_detail_key) . "' "
                    . "";
                $rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artcatalog');
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
                    'ciniki_artcatalog_history', $args['tnid'], 
                    3, 'ciniki_artcatalog_settings', $old_detail_key, '*', '');
                $ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.setting',
                    'args'=>array('id'=>$old_detail_key));
            }

            //
            // Create new value, if it doesn't already exist
            //
            if( isset($old_setting) && !isset($settings[$new_detail_key]) ) {
                $strsql = "INSERT INTO ciniki_artcatalog_settings (tnid, detail_key, detail_value, "
                    . "date_added, last_updated) VALUES ("
                    . "' " . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                    . ", '" . ciniki_core_dbQuote($ciniki, $new_detail_key) . "' "
                    . ", '" . ciniki_core_dbQuote($ciniki, $old_setting) . "' "
                    . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
                    . "";
                $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artcatalog');
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 
                    'ciniki_artcatalog_history', $args['tnid'], 
                    1, 'ciniki_artcatalog_settings', $new_detail_key, 'detail_value', $old_setting);
                $ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.setting',
                    'args'=>array('id'=>$new_detail_key));
            }
        }
    }

    //
    // Keep track if anything has been updated
    //
    $updated = 0;

    //
    // Get the list of objects which change, so we can sync them
    //
    $strsql = "SELECT id FROM ciniki_artcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND " . $args['field'] . " = '" . ciniki_core_dbQuote($ciniki, $args['old_value']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'items');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['rows']) || count($rc['rows']) == 0 ) {
        return array('stat'=>'ok');
    }
    $items = $rc['rows'];

    $strsql = "UPDATE ciniki_artcatalog "
        . "SET " . $args['field'] . " = '" . ciniki_core_dbQuote($ciniki, $args['new_value']) . "', "
        . "last_updated = UTC_TIMESTAMP() "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND " . $args['field'] . " = '" . ciniki_core_dbQuote($ciniki, $args['old_value']) . "' "
        . "";
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the change logs
    //
    foreach($items as $inum => $item) {
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artcatalog', 'ciniki_artcatalog_history', $args['tnid'], 
            2, 'ciniki_artcatalog', $item['id'], $args['field'], $args['new_value']);
        $ciniki['syncqueue'][] = array('push'=>'ciniki.artcatalog.item', 
            'args'=>array('id'=>$item['id']));
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
    if( $updated > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
        ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'artcatalog');
    }

    return array('stat'=>'ok');
}
?>
