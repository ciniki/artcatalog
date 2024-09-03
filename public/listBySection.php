<?php
//
// Description
// ===========
// This method will list the art catalog items sorted by category.  This method will
// not include any images.  If you need the images included, use listWithImages.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the list from.
// section:         (optional) The section to get the images from.  If nothing is specified
//                  the list will be sorted by category.
//
//                  - category - Get the list of images sorted by category. **default**
//                  - media - Get the list of items sorted by media type.
//                  - location - Get the list of items sorted by location.
//
// limit:           (optional) Limit the number of results returned.
// 
// Returns
// -------
// <sections>
//      <section name="Landscapes">
//          <items>
//              <item id="23839" name="Swift Rapids" image_id="45" media="Oil" catalog_number="20120421"
//                  size="8x10" framed_size="12x14" price="350" flags="1" location="Home"
//                  notes="" />
//              <item id="23853" name="Open Field" image_id="23" media="Pastel" catalog_number="20120311"
//                  size="8x10" framed_size="12x14" price="300" flags="1" location="Home"
//                  notes="" />
//              ...
//          </items>
//      </section>
// </sections>
//
function ciniki_artcatalog_listBySection($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'section'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.listBySection'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');


    if( !isset($args['section']) || $args['section'] == 'category' ) {
        $strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, "
            . "ROUND(price, 2) AS price, flags, location, notes, "
            . "IF(ciniki_artcatalog.category='', '', ciniki_artcatalog.category) AS sname "
//      . "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
//      . "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY sname, name "
            . "";
    } elseif( $args['section'] == 'media' ) {
        $strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, price, flags, location, notes, "
            . "IF(ciniki_artcatalog.media='', '', ciniki_artcatalog.media) AS sname "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY sname, name "
            . "";
    } elseif( $args['section'] == 'location' ) {
        $strsql = "SELECT ciniki_artcatalog.id, image_id, name, media, catalog_number, size, framed_size, "
            . "ROUND(price, 2) AS price, flags, location, notes, "
            . "IF(ciniki_artcatalog.location='', '', ciniki_artcatalog.location) AS sname "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY sname, name "
            . "";
    }
    if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'sname', 'name'=>'section',
            'fields'=>array('name'=>'sname')),
        array('container'=>'items', 'fname'=>'id', 'name'=>'item',
            'fields'=>array('id', 'name', 'image_id', 'media', 'catalog_number', 'size', 'framed_size', 'price', 'flags', 'location', 'notes')),
        ));
    // error_log($strsql);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['sections']) ) {
        return array('stat'=>'ok', 'sections'=>array());
    }
    return array('stat'=>'ok', 'sections'=>$rc['sections']);
}
?>
