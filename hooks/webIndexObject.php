<?php
//
// Description
// -----------
// This function returns the index details for an object
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_artcatalog_hooks_webIndexObject($ciniki, $business_id, $args) {

    if( !isset($args['object']) || $args['object'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.2', 'msg'=>'No object specified'));
    }

    if( !isset($args['object_id']) || $args['object_id'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.3', 'msg'=>'No object ID specified'));
    }

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'maps');
    $rc = ciniki_artcatalog_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    if( $args['object'] == 'ciniki.artcatalog.item' ) {
        $strsql = "SELECT id, name, permalink, type, status, flags, webflags, category, "
            . "image_id, year, media, size, framed_size, price, description, inspiration, awards "
            . "FROM ciniki_artcatalog "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.4', 'msg'=>'Object not found'));
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'noexist', 'err'=>array('code'=>'ciniki.artcatalog.5', 'msg'=>'Object not found'));
        }

        //
        // Check if item is visible on website
        //
        $item = $rc['item'];
        if( ($item['webflags']&0x01) == 0 ) {
            return array('stat'=>'ok');
        }
        $object = array(
            'label'=>$item['category'],
            'title'=>$item['name'],
            'subtitle'=>'',
            'meta'=>'',
            'primary_image_id'=>$item['image_id'],
            'synopsis'=>'',
            'object'=>'ciniki.artcatalog.item',
            'object_id'=>$item['id'],
            'primary_words'=>$item['name'] . ' ' . $item['category'] . ' ' . $item['size'],
            'secondary_words'=>'',
            'tertiary_words'=>'',
            'weight'=>10000,
            'url'=>'/gallery/category/' . urlencode($item['category']) . '/' . $item['permalink']
            );

        if( $item['year'] != '' && ($item['webflags']&0x8000) > 0 ) {
            $object['meta'] .= ($object['meta'] != '' ? ', ' : '') . $item['year'];
            $object['secondary_words'] .= ($object['secondary_words'] != '' ? ' ' : '') . $item['year'];
        }
        if( $item['media'] != '' && ($item['webflags']&0x1000) > 0 ) {
            $object['meta'] .= ($object['meta'] != '' ? ', ' : '') . $item['media'];
            $object['secondary_words'] .= ($object['secondary_words'] != '' ? ' ' : '') . $item['media'];
        }
        if( ($item['webflags']&0x2000) > 0 ) {
            //
            // Get the list of materials
            //
            $strsql = "SELECT DISTINCT tag_name "
                . "FROM ciniki_artcatalog_tags "
                . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['id']) . "' "
                . "AND tag_type = 100 "
                . "ORDER BY tag_name "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
            $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.artcatalog', 'materials', 'tag_name');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['materials']) && count($rc['materials']) > 0 ) {
                $object['meta'] .= implode(', ', $rc['materials']);
                $object['secondary_words'] .= ($object['secondary_words']!=''?' ':'') . implode(' ', $rc['materials']);
            }
        }
        if( $item['size'] != '' ) {
            $object['meta'] .= ($object['meta'] != '' ? ', ' : '') . $item['size'];
        }
        if( ($item['flags']&0x10) > 0 ) {
            if( $item['size'] != '' ) {
                if( $item['framed_size'] != '' ) {
                    $object['meta'] .= ' (Framed: ' . $item['framed_size'] . ')';
                } else {
                    $object['meta'] .= ' Framed';
                }
            } else {
                if( $item['framed_size'] != '' ) {
                    $object['meta'] .= ($object['meta'] != '' ? ', ' : '') . 'Framed: ' . $item['framed_size'] . '';
                } else {
                    $object['meta'] .= ($object['meta'] != '' ? ', ' : '') . 'Framed';
                }
            }
        } elseif( $item['size'] != '' && $item['type'] == 1 && ($item['flags']&0x10) == 0 ) {
            $object['meta'] .= ' (unframed)';
        }
        if( ($item['webflags']&0x0800) > 0 ) {
            if( $item['status'] == 20 ) {
                if( $item['price'] != '' && $item['price'] != '0' && $item['price'] != '0.00' ) {
                    if( is_numeric($item['price']) ) {
                        $item['price'] = numfmt_format_currency($ciniki['business']['settings']['intl-default-currency-fmt'], $item['price'], $ciniki['business']['settings']['intl-default-currency']);
                        $object['meta'] .= ($object['meta']!=''?', ':'') . $item['price'] . ' ' . $ciniki['business']['settings']['intl-default-currency'];
                        $object['primary_words'] .= ' ' . sprintf("%d", $item['price']);
                    } else {
                        $object['meta'] .= ($object['meta']!=''?', ':'') . $item['price'];
                        $object['primary_words'] .= ' ' . $item['price'];
                    }
                }
            } else {
                $object['meta'] .= ($object['meta']!=''?', ':'') . $maps['item']['status'][$item['status']];
            }
        }

        if( isset($item['description']) && $item['description'] != '' && ($item['webflags']&0x0100) > 0 ) {
            $object['synopsis'] = $item['description'];
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $item['description'];
        }

        if( isset($item['inspiration']) && $item['inspiration'] != '' && ($item['webflags']&0x0200) > 0 ) {
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $item['inspiration'];
        }

        if( isset($item['awards']) && $item['awards'] != '' && ($item['webflags']&0x0400) > 0 ) {
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $item['awards'];
        }

        return array('stat'=>'ok', 'object'=>$object);
    }

    return array('stat'=>'ok');
}
?>
