<?php
//
// Description
// ===========
// This method will return all the information for an item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the item from.
// artcatalog_id:       The ID of the item in the catalog to be retrieved.
// 
// Returns
// -------
// <item id="27" name="South River" permalink="south-river" 
//      image_id="34" type="1" type_text="Painting"
//      flags="1" webflags="0" catalog_number="20120423" 
//      category="Landscape" year="2012"
//      media="Pastel" size="8x10" framed_size="12x14" forsale="yes" 
//      sold="no" website="visible, category highlight"
//      price="210" location="Home" inspiration="" notes="">
//      <description>
//          The description of the item.
//      </description>
//      <awards>
//          The awards the item has won.
//      </awards>
// </item>
//
function ciniki_artcatalog_get($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
        'tracking'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tracking'),
        'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
        'invoices'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoices'),
        'products'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Products'),
        'tags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tags'),
        // PDF options
        'output'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Output Type'), 
        'layout'=>array('required'=>'no', 'blank'=>'no', 'default'=>'list', 'name'=>'Layout',
            'validlist'=>array('thumbnails', 'list', 'quad', 'single', 'excel')), 
        'pagetitle'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Title'), 
        'fields'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Fields'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.get'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timezoneOffset');
    $utc_offset = ciniki_users_timezoneOffset($ciniki);

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Load artcatalog settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'tnid', $args['tnid'], 'ciniki.artcatalog', 'settings', '');
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

    if( $args['artcatalog_id'] == 0 ) {
        $item = array(
            'id'=>'0',
            'name'=>'',
            'permalink'=>'',
            'image_id'=>'0',
            'type'=>1,
            'type_text'=>'',
            'status'=>'10',
            'status_text'=>'NFS',
            'flags'=>'0',
            'website'=>'',
            'webflags'=>(isset($artcatalog_settings['defaults-webflags']) ? $artcatalog_settings['defaults-webflags'] : 0x0901),
            'catalog_number'=>'',
            'products'=>array(),
            );
        if( ($ciniki['tenant']['modules']['ciniki.artcatalog']['flags']&0x10) > 0 ) {
            $strsql = "SELECT (MAX(catalog_number) + 1) AS next_number "
                . "FROM ciniki_artcatalog "
                . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'catalog');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['catalog']['next_number']) ) {
                $item['catalog_number'] = $rc['catalog']['next_number'];
            } else {
                $item['catalog_number'] = 1;
            }
        }
    } else {
        $strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.name, permalink, image_id, type, type AS type_text, "
            . "ciniki_artcatalog.status, "
            . "ciniki_artcatalog.status AS status_text, "
            . "ciniki_artcatalog.flags, "
            . "IF((ciniki_artcatalog.flags&0x01)=0x01, 'yes', 'no') AS forsale, "
            . "IF((ciniki_artcatalog.flags&0x02)=0x02, 'yes', 'no') AS sold, "
            . "CONCAT_WS('', IF((ciniki_artcatalog.webflags&0x01)=0x01, 'visible', 'hidden'), IF((ciniki_artcatalog.webflags&0x10)=0x10, ', category highlight', '')) AS website , "
            . "webflags, catalog_number, category, year, month, day, "
            . "media, size, framed_size, ciniki_artcatalog.price, ciniki_artcatalog.location, "
            . "ciniki_artcatalog.description, inspiration, awards, publications, ciniki_artcatalog.notes, "
            . "ciniki_artcatalog.date_added, ciniki_artcatalog.last_updated "
    //      . "ciniki_artcatalog_tags.tag_name AS lists "
    //      . "ciniki_artcatalog_customers.customer_id AS customer_id, "
    //      . "CONCAT_WS(' ', IFNULL(ciniki_customers.first, 'Unknown'), IFNULL(ciniki_customers.last, 'Customer')) AS customer_name, "
    //      . "IF((ciniki_artcatalog_customers.flags&0x01)=0x01, 'yes', 'no') AS paid, "
    //      . "IF((ciniki_artcatalog_customers.flags&0x10)=0x10, 'yes', 'no') AS trade, "
    //      . "IF((ciniki_artcatalog_customers.flags&0x10)=0x20, 'yes', 'no') AS donation, "
    //      . "IF((ciniki_artcatalog_customers.flags&0x10)=0x40, 'yes', 'no') AS gift, "
    //      . "ciniki_artcatalog_customers.price AS customer_price, "
    //      . "(ciniki_artcatalog_customers.price + ciniki_artcatalog_customers.taxes + ciniki_artcatalog_customers.shipping "
    //          . "+ ciniki_artcatalog_customers.return_shipping + ciniki_artcatalog_customers.other_costs) AS customer_sale_total "
            . "FROM ciniki_artcatalog "
//          . "LEFT JOIN ciniki_artcatalog_tags ON (ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id AND ciniki_artcatalog_tags.tag_type = 1) ";
    //      . "LEFT JOIN ciniki_artcatalog_customers ON (ciniki_artcatalog.id = ciniki_artcatalog_customers.artcatalog_id) "
    //      . "LEFT JOIN ciniki_customers ON (ciniki_artcatalog_customers.customer_id = ciniki_customers.id "
    //          . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "') "
            . "";
        $strsql .= "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_artcatalog.id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
            . "ORDER BY ciniki_artcatalog.id "; //, ciniki_artcatalog_tags.tag_name ";
    //      . "ORDER BY ciniki_artcatalog.id, ciniki_customers.last, ciniki_customers.first "

        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'items', 'fname'=>'id', 'name'=>'item',
                'fields'=>array('id', 'name', 'permalink', 'image_id', 'type', 'type_text', 'status', 'status_text', 
                    'flags', 'webflags', 'catalog_number', 'category', 'year', 'month', 'day', 
                    'media', 'size', 'framed_size', 'forsale', 'sold', 'website', 'price', 'location', 
                    'description', 'inspiration', 'awards', 'publications', 'notes'),
//              'dlists'=>array('lists'=>'::'),
                'maps'=>array('type_text'=>$maps['item']['type'], 'status_text'=>$maps['item']['status'])),
    //      array('container'=>'sales', 'fname'=>'customer_id', 'name'=>'customer',
    //          'fields'=>array('id'=>'customer_id', 'name'=>'customer_name', 'paid', 'trade', 'donation', 'gift', 'price'=>'customer_price', 'total'=>'customer_sale_total')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['items']) ) {
            return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.artcatalog.21', 'msg'=>'Unable to find item'));
        }
        $item = $rc['items'][0]['item'];

        //
        // Get the tags
        //
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM ciniki_artcatalog_tags "
            . "WHERE artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY tag_type, tag_name "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'tags', 'fname'=>'tag_type', 'name'=>'tags',
                'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tags']['tag_type'] == 1 ) {
                    $item['lists'] = $tags['tags']['lists'];
                } elseif( $tags['tags']['tag_type'] == 100 ) {
                    $item['materials'] = $tags['tags']['lists'];
                }
            }
        }

        //
        // Check if output is PDF, then send to single template
        //
        if( isset($args['output']) && $args['output'] == 'pdf' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'templates', 'single');
            $rc = ciniki_artcatalog_templates_single($ciniki, $args['tnid'], 
                array('sections'=>array('section'=>array('items'=>array('0'=>array('item'=>$item))))), $args);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            return array('stat'=>'ok');
        }

        //
        // Check for price format
        //
        if( $item['price'] != '' && is_numeric($item['price']) ) {
            $item['price'] = numfmt_format_currency($intl_currency_fmt, $item['price'], $intl_currency);
        }

        //
        // Get the tracking list if request
        //
        if( isset($args['tracking']) && $args['tracking'] == 'yes' ) {
            $strsql = "SELECT id, name, external_number, "
                . "IFNULL(DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS start_date, "
                . "IFNULL(DATE_FORMAT(end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS end_date "
                . "FROM ciniki_artcatalog_tracking "
                . "WHERE artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "ORDER BY ciniki_artcatalog_tracking.start_date DESC "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
                array('container'=>'tracking', 'fname'=>'id', 'name'=>'place',
                    'fields'=>array('id', 'name', 'external_number', 'start_date', 'end_date')),
                ));
            if( $rc['stat'] != 'ok' ) { 
                return $rc;
            }
            if( isset($rc['tracking']) ) {
                $item['tracking'] = $rc['tracking'];
            }
        }

        //
        // Get the product list if requested
        //
        if( isset($args['products']) && $args['products'] == 'yes' 
            && ($ciniki['tenant']['modules']['ciniki.artcatalog']['flags']&0x02) > 0
            ) {
            $strsql = "SELECT id, name, inventory, price "
                . "FROM ciniki_artcatalog_products "
                . "WHERE artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "ORDER BY ciniki_artcatalog_products.name "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
                array('container'=>'products', 'fname'=>'id', 'name'=>'product',
                    'fields'=>array('id', 'name', 'inventory', 'price')),
                ));
            if( $rc['stat'] != 'ok' ) { 
                return $rc;
            }
            if( isset($rc['products']) ) {
                $item['oldproducts'] = $rc['products'];
                foreach($item['oldproducts'] as $pid => $product) {
                    $item['oldproducts'][$pid]['product']['price'] = numfmt_format_currency($intl_currency_fmt, $product['product']['price'], $intl_currency);
                }
            }
        }

        //
        // Get the product list if requested
        //
        if( isset($args['products']) && $args['products'] == 'yes' && isset($ciniki['tenant']['modules']['ciniki.merchandise']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'merchandise', 'hooks', 'productList');
            $rc = ciniki_merchandise_hooks_productList($ciniki, $args['tnid'], array('object'=>'ciniki.artcatalog.item', 'object_id'=>$args['artcatalog_id']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $item['products'] = array();
            if( isset($rc['products']) ) {
                $item['products'] = $rc['products'];
            }
        }

        //
        // Get the additional images if requested
        //
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
            $strsql = "SELECT ciniki_artcatalog_images.id, "
                . "ciniki_artcatalog_images.image_id, "
                . "ciniki_artcatalog_images.name, "
                . "ciniki_artcatalog_images.sequence, "
                . "ciniki_artcatalog_images.webflags, "
                . "ciniki_artcatalog_images.description "
                . "FROM ciniki_artcatalog_images "
                . "WHERE ciniki_artcatalog_images.artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
                . "AND ciniki_artcatalog_images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "ORDER BY ciniki_artcatalog_images.sequence, ciniki_artcatalog_images.date_added, ciniki_artcatalog_images.name "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
                array('container'=>'images', 'fname'=>'id', 'name'=>'image',
                    'fields'=>array('id', 'image_id', 'name', 'sequence', 'webflags', 'description')),
                ));
            if( $rc['stat'] != 'ok' ) { 
                return $rc;
            }
            if( isset($rc['images']) ) {
                $item['images'] = $rc['images'];
                foreach($item['images'] as $inum => $img) {
                    if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
                        $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], array('image_id'=>$img['image']['image_id'], 'maxlength'=>75));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $item['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            }
        }

        //
        // Get any invoices for this piece of art
        //
        if( isset($args['invoices']) && $args['invoices'] == 'yes' && isset($modules['ciniki.sapos']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'objectInvoices');
            $rc = ciniki_sapos_objectInvoices($ciniki, $args['tnid'], 'ciniki.artcatalog.item', $args['artcatalog_id']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['invoices']) ) {
                $item['invoices'] = $rc['invoices'];
            }
        }
    }

    $rsp = array('stat'=>'ok', 'item'=>$item);

    //
    // Get the available tags
    //
    if( isset($args['tags']) && $args['tags'] == 'yes' ) {
        $rsp['tags'] = array();

        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsList');
        $rc = ciniki_core_tagsList($ciniki, 'ciniki.artcatalog', $args['tnid'], 
            'ciniki_artcatalog_tags', 100);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.22', 'msg'=>'Unable to get lists', 'err'=>$rc['err']));
        }
        if( isset($rc['tags']) ) {
            $rsp['tags']['materials'] = $rc['tags'];
        }

        $rc = ciniki_core_tagsList($ciniki, 'ciniki.artcatalog', $args['tnid'], 
            'ciniki_artcatalog_tags', 1);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.23', 'msg'=>'Unable to get lists', 'err'=>$rc['err']));
        }
        if( isset($rc['tags']) ) {
            $rsp['tags']['lists'] = $rc['tags'];
        }
    }

    return $rsp;
}
?>
