<?php
//
// Description
// -----------
// This function returns the index details for an object
//
// Arguments
// ---------
// ciniki:
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_artcatalog_hooks_webIndexObject($ciniki, $business_id, $args) {

    if( !isset($args['object']) || $args['object'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3273', 'msg'=>'No object specified'));
    }

    if( !isset($args['object_id']) || $args['object_id'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3274', 'msg'=>'No object ID specified'));
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
            . "image_id, media, size, framed_size, price, description, inspiration, awards "
            . "FROM ciniki_artcatalog "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2375', 'msg'=>'Object not found'));
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2376', 'msg'=>'Object not found'));
        }

        //
        // Check if item is visible on website
        //
        if( ($rc['item']['webflags']&0x01) == 0 ) {
            return array('stat'=>'ok');
        }
        $object = array(
            'title'=>$rc['item']['name'],
            'subtitle'=>'',
            'meta'=>'',
            'primary_image_id'=>$rc['item']['image_id'],
            'synopsis'=>'',
            'object'=>'ciniki.artcatalog.item',
            'object_id'=>$rc['item']['id'],
            'primary_words'=>$rc['item']['name'] . ' ' . $rc['item']['category'] . ' ' . $rc['item']['size'],
            'secondary_words'=>'',
            'tertiary_words'=>'',
            'weight'=>10000,
            'url'=>'/gallery/category/' . urlencode($rc['item']['category']) . '/' . $rc['item']['permalink']
            );

        if( $rc['item']['media'] != '' && ($rc['item']['webflags']&0x1000) > 0 ) {
            $object['meta'] .= $rc['item']['media'];
            $object['secondary_words'] .= ($object['secondary_words']!=''?' ':'') . $rc['item']['media'];
        }
        if( ($rc['item']['webflags']&0x2000) > 0 ) {
            //
            // Get the list of materials
            //
            $strsql = "SELECT DISTINCT tag_name "
                . "FROM ciniki_artcatalog_tags "
                . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . "AND artcatalog_id = '" . ciniki_core_dbQuote($ciniki, $rc['item']['id']) . "' "
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
        if( $rc['item']['size'] != '' ) {
            $object['meta'] .= ($object['meta']!=''?', ':'') . $rc['item']['size'];
        }
        if( ($rc['item']['flags']&0x10) > 0 ) {
            if( $rc['item']['size'] != '' ) {
                if( $rc['item']['framed_size'] != '' ) {
                    $object['meta'] .= ' (Framed: ' . $rc['item']['framed_size'] . ')';
                } else {
                    $object['meta'] .= ' Framed';
                }
            } else {
                if( $rc['item']['framed_size'] != '' ) {
                    $object['meta'] .= ($object['meta']!=''?', ':'') . 'Framed: ' . $rc['item']['framed_size'] . '';
                } else {
                    $object['meta'] .= ($object['meta']!=''?', ':'') . 'Framed';
                }
            }
        } elseif( $rc['item']['size'] != '' && $rc['item']['type'] == 1 && ($rc['item']['flags']&0x10) == 0 ) {
            $object['meta'] .= ' (unframed)';
        }
        if( ($rc['item']['webflags']&0x0800) > 0 ) {
            if( $rc['item']['status'] == 20 ) {
                if( $rc['item']['price'] != '' && $rc['item']['price'] != '0' && $rc['item']['price'] != '0.00' ) {
                    if( is_numeric($rc['item']['price']) ) {
                        $rc['item']['price'] = numfmt_format_currency($ciniki['business']['settings']['intl-default-currency-fmt'], $rc['item']['price'], $ciniki['business']['settings']['intl-default-currency']);
                        $object['meta'] .= ($object['meta']!=''?', ':'') . $rc['item']['price'] . ' ' . $ciniki['business']['settings']['intl-default-currency'];
                        $object['primary_words'] .= ' ' . sprintf("%d", $rc['item']['price']);
                    } else {
                        $object['meta'] .= ($object['meta']!=''?', ':'') . $rc['item']['price'];
                        $object['primary_words'] .= ' ' . $rc['item']['price'];
                    }
                }
            } else {
                $object['meta'] .= ($object['meta']!=''?', ':'') . $maps['item']['status'][$rc['item']['status']];
            }
        }

        if( isset($rc['item']['description']) && $rc['item']['description'] != '' && ($rc['item']['webflags']&0x0100) > 0 ) {
            $object['synopsis'] = $rc['item']['description'];
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $rc['item']['description'];
        }

        if( isset($rc['item']['inspiration']) && $rc['item']['inspiration'] != '' && ($rc['item']['webflags']&0x0200) > 0 ) {
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $rc['item']['inspiration'];
        }

        if( isset($rc['item']['awards']) && $rc['item']['awards'] != '' && ($rc['item']['webflags']&0x0400) > 0 ) {
            $object['tertiary_words'] .= ($object['tertiary_words']!=''?', ':'') . ' ' . $rc['item']['awards'];
        }

        return array('stat'=>'ok', 'object'=>$object);
    }

	return array('stat'=>'ok');
}
?>
