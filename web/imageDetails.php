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
function ciniki_artcatalog_web_imageDetails($ciniki, $settings, $business_id, $permalink) {

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    //
    // Load Art Catalog settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'business_id', $business_id, 'ciniki.artcatalog', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $artcatalog_settings = $rc['settings'];

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'maps');
    $rc = ciniki_artcatalog_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the details about the item
    //
    $strsql = "SELECT ciniki_artcatalog.id, "
        . "ciniki_artcatalog.name, "
        . "ciniki_artcatalog.permalink, "
        . "ciniki_artcatalog.image_id, "
        . "ciniki_artcatalog.type, "
        . "ciniki_artcatalog.status, "
        . "ciniki_artcatalog.status AS status_text, "
        . "ciniki_artcatalog.catalog_number, "
        . "ciniki_artcatalog.category, "
        . "ciniki_artcatalog.year, "
        . "ciniki_artcatalog.flags, "
        . "ciniki_artcatalog.webflags, "
//      . "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
//      . "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
        . "IF((ciniki_artcatalog.webflags&0x01)=0, 'yes', 'no') AS hidden, "
        . "ciniki_artcatalog.media, "
        . "ciniki_artcatalog.size, "
        . "ciniki_artcatalog.framed_size, "
        . "ciniki_artcatalog.price, "
        . "ciniki_artcatalog.location, "
        . "ciniki_artcatalog.description, "
        . "ciniki_artcatalog.inspiration, "
        . "ciniki_artcatalog.awards, "
        . "ciniki_artcatalog.publications, "
        . "ciniki_artcatalog.notes, "
        . "ciniki_artcatalog.date_added, ciniki_artcatalog.last_updated, "
        . "ciniki_artcatalog_images.id AS additional_id, "
        . "ciniki_artcatalog_images.image_id AS additional_image_id, "
        . "ciniki_artcatalog_images.permalink AS additional_permalink, "
        . "ciniki_artcatalog_images.name AS additional_name, "
        . "ciniki_artcatalog_images.description AS additional_description, "
        . "ciniki_artcatalog_images.last_updated AS additional_last_updated "
        . "FROM ciniki_artcatalog "
        . "LEFT JOIN ciniki_artcatalog_images ON (ciniki_artcatalog.id = ciniki_artcatalog_images.artcatalog_id "
            . "AND ciniki_artcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_artcatalog_images.image_id > 0 "
            . "AND (ciniki_artcatalog_images.webflags&0x01) = 1 ) "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_artcatalog.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
        . "ORDER BY ciniki_artcatalog.id, ciniki_artcatalog_images.sequence, ciniki_artcatalog_images.date_added "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id', 'type', 'status', 'status_text', 'catalog_number', 
                'category', 'year', 'flags', 'webflags', 'hidden', 
                'media', 'size', 'framed_size', 'price',
                'location', 'description', 'inspiration', 'awards', 'publications', 'notes', 'date_added', 'last_updated'),
            'maps'=>array('status_text'=>$maps['item']['status'])),
        array('container'=>'additionalimages', 'fname'=>'additional_id', 
            'fields'=>array('id'=>'additional_id', 'image_id'=>'additional_image_id',
                'permalink'=>'additional_permalink',
                'title'=>'additional_name', 'description'=>'additional_description',
                'last_updated'=>'additional_last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) || count($rc['items']) < 1 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.51', 'msg'=>'Unable to find artwork'));
    }
    $image = array_pop($rc['items']);
//  $image = $rc['items'][0]['item'];
//  $image = array('id'=>$rc['piece']['id'],
//      'title'=>$rc['piece']['name'],
//      'category'=>$rc['piece']['category'],
//      'image_id'=>$rc['piece']['image_id'],
//      'details'=>'',
//      'description'=>$rc['piece']['description'],
//      'awards'=>$rc['piece']['awards'],
//      'date_added'=>$rc['piece']['date_added'],
//      'last_updated'=>$rc['piece']['last_updated']);
    $image['details'] = '';
    if( $image['media'] != '' && ($image['webflags']&0x1000) > 0 ) {
        $image['details'] .= $image['media'];
    }
    if( ($image['webflags']&0x2000) > 0 ) {
        //
        // Get the list of materials
        //
        $strsql = "SELECT DISTINCT tag_name "
            . "FROM ciniki_artcatalog_tags "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $image['id']) . "' "
            . "AND tag_type = 100 "
            . "ORDER BY tag_name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
        $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.artcatalog', 'materials', 'tag_name');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['materials']) && count($rc['materials']) > 0 ) {
            $image['details'] .= implode(', ', $rc['materials']);
        }
    }
    if( $image['size'] != '' ) {
        $image['details'] .= ($image['details']!=''?', ':'') . $image['size'];
    }
//  if( $image['framed_size'] != '' && 
    if( ($image['flags']&0x10) > 0 ) {
        if( $image['size'] != '' ) {
            if( $image['framed_size'] != '' ) {
                $image['details'] .= ' (framed: ' . $image['framed_size'] . ')';
            } else {
                $image['details'] .= ' framed';
            }
        } else {
            if( $image['framed_size'] != '' ) {
                $image['details'] .= ($image['details']!=''?', ':'') . 'Framed: ' . $image['framed_size'] . '';
            } else {
                $image['details'] .= ($image['details']!=''?', ':'') . 'Framed';
            }
        }
    } elseif( $image['size'] != '' && $image['type'] == 1 && ($image['flags']&0x10) == 0 ) {
        $image['details'] .= ' (unframed)';
    }
    if( ($image['webflags']&0x0800) > 0 ) {
        if( $image['status'] == 20 ) {
            if( $image['price'] != '' && $image['price'] != '0' && $image['price'] != '0.00' ) {
                if( is_numeric($image['price']) ) {
                    $image['price'] = numfmt_format_currency($intl_currency_fmt, $image['price'], $intl_currency);
                    $image['details'] .= ($image['details']!=''?', ':'') . $image['price'] . ' ' . $intl_currency;
                } else {
                    $image['details'] .= ($image['details']!=''?', ':'') . $image['price'];
                }
            }
        } else {
            $image['details'] .= ($image['details']!=''?', ':'') . " <b> " . $image['status_text'] . "</b>";
        }

//      if( isset($image['sold']) && $image['sold'] == 'yes' ) {
//          $image['details'] .= " <b> SOLD</b>";
//      }
    }
    if( isset($artcatalog_settings['forsale-message']) && $artcatalog_settings['forsale-message'] != '' && $image['status'] == 20 ) {
        $image['description'] .= ($image['description'] != '' ? "\n\n" : '') . $artcatalog_settings['forsale-message'] . "";
    }

    $image['category_permalink'] = urlencode($image['category']);

    //
    // Get the list of products for the item
    //
/*    if( ($ciniki['business']['modules']['ciniki.artcatalog']['flags']&0x02) > 0 ) {
        $strsql = "SELECT ciniki_artcatalog_products.id, "
            . "ciniki_artcatalog_products.name AS title, "
            . "ciniki_artcatalog_products.permalink, "
            . "ciniki_artcatalog_products.flags, "
            . "ciniki_artcatalog_products.synopsis AS description, "
            . "'no' AS is_details, "
//          . "IF(ciniki_artcatalog_products.description='','no','yes') AS is_details, "
            . "ciniki_artcatalog_products.price, "
            . "ciniki_artcatalog_products.taxtype_id, "
            . "ciniki_artcatalog_products.inventory, "
            . "ciniki_artcatalog_products.image_id "
            . "FROM ciniki_artcatalog_products "
//          . "LEFT JOIN ciniki_images ON ("
//              . "ciniki_artcatalog_products.image_id = ciniki_images.id "
//              . "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
//              . ") "
            . "WHERE ciniki_artcatalog_products.artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $image['id']) . "' "
            . "AND ciniki_artcatalog_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND (ciniki_artcatalog_products.flags&0x01) > 0 "
            . "ORDER BY sequence, name";
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'products', 'fname'=>'id',
                'fields'=>array('id', 'title', 'permalink', 'image_id', 'description', 'is_details', 'price', 'taxtype_id', 'inventory')),
            ));
        if( $rc['stat'] == 'ok' && isset($rc['products']) ) {
            $image['products'] = $rc['products'];
            //
            // FIXME: Add code to create product information ready to display add to cart
            //
        }
    } */

    //
    // Get the list of products for the item
    //
    if( isset($ciniki['business']['modules']['ciniki.merchandise']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'merchandise', 'web', 'productList');
        $rc = ciniki_merchandise_web_productList($ciniki, $settings, $business_id, array('object'=>'ciniki.artcatalog.item', 'object_id'=>$image['id']));
        if( $rc['stat'] == 'ok' && isset($rc['products']) ) {
            $image['products'] = $rc['products'];
        }
    }

    return array('stat'=>'ok', 'image'=>$image);
}
?>
