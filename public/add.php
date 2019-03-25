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
// tnid:     The ID of the tenant to add the item to.  The user must
//                  an owner of the tenant.
//
// type:            The type of the item.  Currently
//                  only two types are supported, Painting and Photographs.
//
//                  1 - Painting
//                  2 - Photograph
//                  3 - Jewelry
//                  4 - Sculpture
//                  5 - Fibre Art
//                  6 - Clothing *future*
//
// status:          (optional) The current status of the item.
//                  
//                  10 - Not for sale (NFS)
//                  20 - For Sale
//                  50 - Sold
//                  60 - Private Collection
//                  70 - Collection of the Artist
//
// flags:           (optional) The bit flags for the item.
//  
//                  0x01 - The item is for sale.
//                  0x02 - The item is sold.  When displayed on the website, a red dot will be added to indicate sold.
//
// webflags:        (optional) The flags for displaying the item on the tenant website.
//
//                  0x01 - Public item, to be displayed on the website
//                  0x10 - Category highlight item
//                  0x20 - Media highlight item *future*
//                  0x40 - Location highlight item *future*
//                  0x80 - Year highlight item *future*
//
// image_id:        (optional) The ID of the image in the images module to be displayed for the item.  This
//                  can be uploaded before or after the item is added to the artcatalog.
//
// name:            The name of the item.  This name must be unique within the tenant, as it's
//                  also used to generate the permalink.  The permalink must be usique because it
//                  is used as in the URL to reference an item.
//
// catalog_number:  (optional) A freeform field to store a catalog number if the user wants.  The
//                  can be any string of characters.
//                  
// category:        (optional) The name of the category the item is a part of.  Only one category can
//                  be assigned to each item.
//
// year:            (optional) The year the item was completed, or in the case of a photograph, when the
//                  photo was taken.
//
// media:           (optional) The type of media the item was created in.  This can be anything that the user
//                  wishes, such as Oil, Pastel, etc for Paintings.  For Photographs, the information will be stored
//                  but nothing done with it.
//
// size:            (optional) The size of the item, typically used for Paintings as the size of the original.  
//                  For photographs this is typically the maximum size which the photo can be printed.  This
//                  information is displayed on the website.
//
// framed_size:     (optional) The framed size of the item, which is only used for paintings and shown on the website.  
//                  It can be stored for a photograph, but will not be shown on the website.
//
// price:           (optional) The price to purchase the item.  This should be a number, and not include $.
//
// location:        (optional) Where the item is currently located.  This can be used to track if paintings are located
//                  at home, or in a gallery.  
//
// description:     (optional) The description of the item, which will be displayed on the website.
//
// inspiration:     (optional) Where the inspiration came from for the item.  This information will not be displayed on the website.
//
// awards:          (optional) Any awards that the item has won.  This information is displayed along with the description
//                  on the website.
//
// notes:           (optional) Any notes the creator has for the item.  This information is private and will not be displayed on the website.
//
// lists:           (optional) The lists the item is a part of.
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artcatalog_add(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'type'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Type'),
        'status'=>array('required'=>'no', 'blank'=>'no', 'default'=>'10', 'name'=>'Status'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Flags'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Web Flags'), 
        'image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Catalog Number'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Category'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Year'), 
        'month'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Month'), 
        'day'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Day'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Media'), 
        'size'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Size'), 
        'framed_size'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Framed Size'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Price'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Location'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Inspiration'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Awards'), 
        'publications'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Publications'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Notes'), 
        'lists'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Lists'),
        'materials'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Materials'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //
    // Strip non-number characater from price, removes $ or NFS, etc.
    //
    if( isset($args['price']) ) {
        $args['price'] = preg_replace('/[^0-9\.]/', '', $args['price']);
    }

    //
    // Reset unknown category back to blank
    //
    if( isset($args['category']) && $args['category'] == 'Unknown' ) {
        $args['category'] = '';
    }
//  $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
    $args['user_id'] = $ciniki['session']['user']['id'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.add'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get a new UUID
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
    $rc = ciniki_core_dbUUID($ciniki, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args['uuid'] = $rc['uuid'];

    //
    // Check the permalink doesn't already exist
    //
    $strsql = "SELECT id, name, permalink FROM ciniki_artcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.13', 'msg'=>'You already have artwork with this name, please choose another name'));
    }

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.artcatalog.item', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
        return $rc;
    }
    $artcatalog_id = $rc['id'];

    //
    // Check if there are any change to the lists the item is a part of
    //
    if( isset($args['lists']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.artcatalog', 'tag', $args['tnid'], 
            'ciniki_artcatalog_tags', 'ciniki_artcatalog_history', 
            'artcatalog_id', $artcatalog_id, 1, $args['lists']);
        if( $rc['stat'] != 'ok' ) {
            array_pop($ciniki['syncqueue']);
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.14', 'msg'=>'Unable to update lists', 'err'=>$rc['err']));
        }
    }

    //
    // Check if there are any change to the materials the item is a part of
    //
    if( isset($args['materials']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.artcatalog', 'tag', $args['tnid'], 
            'ciniki_artcatalog_tags', 'ciniki_artcatalog_history', 
            'artcatalog_id', $artcatalog_id, 100, $args['materials']);
        if( $rc['stat'] != 'ok' ) {
            array_pop($ciniki['syncqueue']);
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.15', 'msg'=>'Unable to update materials', 'err'=>$rc['err']));
        }
    }

    //
    // Update the possible menu items available for artcatalog.  This is for the split gallery
    // between Paintings, Photographs, Jewelry, etc
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'updateWebSettings');
    $rc = ciniki_artcatalog_updateWebSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        array_pop($ciniki['syncqueue']);
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.16', 'msg'=>'Unable to update web settings', 'err'=>$rc['err']));
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

    $ciniki['fbrefreshqueue'][] = array('tnid'=>$args['tnid'], 'url'=>'/gallery/category/' . urlencode($args['category']) . '/' . $args['permalink']);

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.artcatalog.item', 'object_id'=>$artcatalog_id));

    return array('stat'=>'ok', 'id'=>$artcatalog_id);
}
?>
