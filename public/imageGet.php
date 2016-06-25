<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the image to.
// artcatalog_image_id: The ID of the image to get.
//
// Returns
// -------
//
function ciniki_artcatalog_imageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'),
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.imageGet', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_artcatalog_images.id, "
        . "ciniki_artcatalog_images.name, "
        . "ciniki_artcatalog_images.permalink, "
        . "ciniki_artcatalog_images.webflags, "
        . "ciniki_artcatalog_images.sequence, "
        . "ciniki_artcatalog_images.image_id, "
        . "ciniki_artcatalog_images.description "
        . "FROM ciniki_artcatalog_images "
        . "WHERE ciniki_artcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_artcatalog_images.id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_image_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'images', 'fname'=>'id', 'name'=>'image',
            'fields'=>array('id', 'name', 'permalink', 'webflags', 'sequence', 'image_id', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['images']) ) {
        return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'1338', 'msg'=>'Unable to find image'));
    }
    $image = $rc['images'][0]['image'];
    
    return array('stat'=>'ok', 'image'=>$image);
}
?>
