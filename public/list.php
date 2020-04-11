<?php
//
// Description
// ===========
// This method will get the number of items in each category, media type, location and years.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the stats for.
// 
// Returns
// -------
//
function ciniki_artcatalog_list($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'), 
        'section'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Media'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Location'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Year'), 
        'material'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Material'), 
        'list'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'List'), 
        'tracking'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Exhibited'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'End'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
//        // PDF options
        'output'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Output Type'), 
        'layout'=>array('required'=>'no', 'blank'=>'no', 'default'=>'list', 'name'=>'Layout',
            'validlist'=>array('pricelist', 'thumbnails', 'list', 'quad', 'single', 'excel')), 
        'pagetitle'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Title'), 
        'sortby'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sort By'), 
//        'align'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Align'), 
//        'fields'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Fields'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.artcatalog.list'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
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

    // Load db query functions
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');


    $rsp = array('stat'=>'ok');

    //
    // Get the list of types
    //
    $strsql = "SELECT type, "
        . "type AS name, "
        . "COUNT(*) AS count "
        . "FROM ciniki_artcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "GROUP BY type "
        . "";
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'types', 'fname'=>'type',
            'fields'=>array('type', 'name', 'count'), 
            'maps'=>array('name'=>$maps['item']['type'])),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp['types'] = isset($rc['types']) ? $rc['types'] : array();

    //
    // Get the sections
    //
    $rsp['sections'] = array(
        'categories' => array('label' => 'Categories'),
        'media' => array('label' => 'Media'),
        'locations' => array('label' => 'Locations'),
        'years' => array('label' => 'Years'),
        );

    //
    // Get the tag counts
    //
    $strsql = "SELECT tag_type, COUNT(*) AS count "
        . "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
        . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
        . "";
    if( isset($args['type']) && $args['type'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
    }
    $strsql .= "GROUP BY tag_type "
        . "ORDER BY tag_type DESC "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.artcatalog', 'tagtypes');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tagtypes']) ) {
        foreach($rc['tagtypes'] as $typecode => $num_tags) {
            if( $num_tags > 0 ) {
                switch($typecode) {
//                    case 1: 
//                        $rsp['sections']['lists'] = array('label' => 'Lists');
//                        break;
                    case 100: 
                        $rsp['sections']['materials'] = array('label' => 'Materials');
                        break;
                }
            }
        }
    }

    //
    // Check if any exhibited
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_artcatalog, ciniki_artcatalog_tracking "
        . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_artcatalog.id = ciniki_artcatalog_tracking.artcatalog_id "
        . "AND ciniki_artcatalog_tracking.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    if( isset($args['type']) && $args['type'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.artcatalog', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.52', 'msg'=>'Unable to load get the number of exhibited items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 0 ) {
        $rsp['sections']['tracking'] = array('label' => 'Exhibited');
    }

    //
    // Get the list of categories
    //
    if( isset($args['category']) ) {
        $strsql = "SELECT IF(category='', 'Unknown', category) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= ""
            . "GROUP BY category "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'categories', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['categories'] = isset($rc['categories']) ? $rc['categories'] : array();
    }

    //
    // Get the list of media
    //
    if( isset($args['media']) ) {
        $strsql = "SELECT IF(media='', 'Unknown', media) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= ""
            . "GROUP BY media "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'media', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['media'] = isset($rc['media']) ? $rc['media'] : array();
    }

    //
    // Get the list of locations
    //
    if( isset($args['location']) ) {
        $strsql = "SELECT IF(location='', 'Unknown', location) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= ""
            . "GROUP BY location "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'locations', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['locations'] = isset($rc['locations']) ? $rc['locations'] : array();
    }

    //
    // Get the list of Years
    //
    if( isset($args['year']) ) {
        $strsql = "SELECT IF(year='', 'Unknown', year) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= ""
            . "GROUP BY year "
            . "ORDER BY year DESC "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'years', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['years'] = isset($rc['years']) ? $rc['years'] : array();
    }

    //
    // Get the list of Materials
    //
    if( isset($args['material']) ) {
        $strsql = "SELECT IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
            . "AND ciniki_artcatalog_tags.tag_type = 100 "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= "GROUP BY tag_name "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'materials', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['materials'] = isset($rc['materials']) ? $rc['materials'] : array();
    }

    //
    // Get the list of Lists
    //
    if( isset($args['list']) ) {
        $strsql = "SELECT IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS name, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
            . "AND ciniki_artcatalog_tags.tag_type = 1 "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= "GROUP BY tag_name "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'lists', 'fname'=>'name', 'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['lists'] = isset($rc['lists']) ? $rc['lists'] : array();
    }

    //
    // Get the list of exhibited
    //
    if( isset($args['tracking']) ) {
        $dt = new DateTime('now');
        $dt->sub(new DateInterval('P1M'));
        $strsql = "SELECT "
            . "CONCAT_WS('-', tracking.name, tracking.start_date, tracking.end_date) AS id, "
            . "IF(tracking.end_date = '0000-00-00' "
                . "OR tracking.end_date > '" . ciniki_core_dbQuote($ciniki, $dt->format('Y-m-d')) . "', 'active', 'past') AS timeframe, "
            . "IF(tracking.name='', '', tracking.name) AS name, "
            . "IFNULL(DATE_FORMAT(IF(tracking.start_date = '0000-00-00', '', tracking.start_date), '%b %d, %Y'), '') AS start_date, "
            . "IFNULL(DATE_FORMAT(IF(tracking.end_date = '0000-00-00', '', tracking.end_date), '%b %d, %Y'), '') AS end_date, "
            . "COUNT(*) AS count "
            . "FROM ciniki_artcatalog, ciniki_artcatalog_tracking AS tracking "
            . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_artcatalog.id = tracking.artcatalog_id "
            . "AND tracking.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        $strsql .= "GROUP BY timeframe, id "
            . "ORDER BY timeframe, id, name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'timeframes', 'fname'=>'timeframe', 'fields'=>array('timeframe')),
            array('container'=>'tracking', 'fname'=>'id', 'fields'=>array('id', 'name', 'start_date', 'end_date', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['activetracking'] = isset($rc['timeframes']['active']['tracking']) ? array_values($rc['timeframes']['active']['tracking']) : array();
        $rsp['pasttracking'] = isset($rc['timeframes']['past']['tracking']) ? array_values($rc['timeframes']['past']['tracking']) : array();
    }

    //
    // Get the list of images
    //
    if( isset($args['category']) 
        || isset($args['media'])
        || isset($args['location'])
        || isset($args['year'])
        || isset($args['list'])
        || isset($args['material'])
        || isset($args['tracking'])
        ) {

        $strsql = "SELECT ciniki_artcatalog.id, "
            . "image_id, "
            . "ciniki_artcatalog.name, "
            . "ciniki_artcatalog.category AS category_name, "
            . "type, "
            . "status, "
            . "status AS status_text, "
            . "year, "
            . "media, "
            . "catalog_number, "
            . "size, "
            . "framed_size, "
            . "price, "
            . "flags, "
            . "location, "
            . "description, "
            . "ciniki_artcatalog.awards, "
            . "ciniki_artcatalog.notes, "
            . "ciniki_artcatalog.inspiration, "
            . "ciniki_artcatalog.last_updated, "
            . "IF(status>=50, 'yes', 'no') AS sold, "
            . "";
        if( isset($args['sortby']) && $args['sortby'] == 'catalognumber' ) {
            $strsql .= "'' AS sname ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'category' ) {
            $strsql .= "IF(ciniki_artcatalog.category='', 'Unknown', ciniki_artcatalog.category) AS sname ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'media' ) {
            $strsql .= "IF(ciniki_artcatalog.media='', 'Unknown', ciniki_artcatalog.media) AS sname ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'location' ) {
            $strsql .= "IF(ciniki_artcatalog.location='', 'Unknown', ciniki_artcatalog.location) AS sname ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'year' ) {
            $strsql .= "IF(ciniki_artcatalog.year='', 'Unknown', ciniki_artcatalog.year) AS sname ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'tracking' ) {
            $strsql .= "IF(ciniki_artcatalog_tracking.name='', 'Unknown', ciniki_artcatalog_tracking.name) AS sname ";
        } elseif( isset($args['category']) ) {
            $strsql .= "IF(ciniki_artcatalog.category='', 'Unknown', ciniki_artcatalog.category) AS sname ";
        } elseif( isset($args['media']) ) {
            $strsql .= "IF(ciniki_artcatalog.media='', 'Unknown', ciniki_artcatalog.media) AS sname ";
        } elseif( isset($args['location']) ) {
            $strsql .= "IF(ciniki_artcatalog.location='', 'Unknown', ciniki_artcatalog.location) AS sname ";
        } elseif( isset($args['year']) ) {
            $strsql .= "IF(ciniki_artcatalog.year='', 'Unknown', ciniki_artcatalog.year) AS sname ";
        } elseif( isset($args['material']) ) {
            $strsql .= "IF(ciniki_artcatalog_tags.tag_name='', 'Unknown', ciniki_artcatalog_tags.tag_name) AS sname ";
        } elseif( isset($args['list']) ) {
            $strsql .= "IF(ciniki_artcatalog_tags.tag_name='', 'Unknown', ciniki_artcatalog_tags.tag_name) AS sname ";
        } elseif( isset($args['tracking']) ) {
            $strsql .= "IF(ciniki_artcatalog_tracking.name='', 'Unknown', ciniki_artcatalog_tracking.name) AS sname ";
        }

        if( isset($args['material']) ) {
            $strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
                . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
                . "AND ciniki_artcatalog_tags.tag_type = 100 "
                . "";
        } elseif( isset($args['list']) ) {
            $strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
                . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
                . "AND ciniki_artcatalog_tags.tag_type = 1 "
                . "";
        } elseif( isset($args['tracking']) ) {
            $strsql .= "FROM ciniki_artcatalog, ciniki_artcatalog_tracking "
                . "WHERE ciniki_artcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "AND ciniki_artcatalog.id = ciniki_artcatalog_tracking.artcatalog_id "
                . "";
        } else {
            $strsql .= "FROM ciniki_artcatalog "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "";
        }
        //
        // Check if this should just be a sublist with one section of a group
        //
        if( isset($args['category']) ) {
            if( $args['category'] == 'Unknown' ) {
                $strsql .= "AND (category = 'Unknown' OR category = '') ";
            } else {
                $strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' ";
            }
        } elseif( isset($args['media']) ) {
            if( $args['media'] == 'Unknown' ) {
                $strsql .= "AND (media = 'Unknown' OR media = '') ";
            } else {
                $strsql .= "AND media = '" . ciniki_core_dbQuote($ciniki, $args['media']) . "' ";
            }
        } elseif( isset($args['location']) ) {
            if( $args['location'] == 'Unknown' ) {
                $strsql .= "AND (location = 'Unknown' OR location = '') ";
            } else {
                $strsql .= "AND location = '" . ciniki_core_dbQuote($ciniki, $args['location']) . "' ";
            }
        } elseif( isset($args['year']) ) {
            if( $args['year'] == 'Unknown' ) {
                $strsql .= "AND (year = 'Unknown' OR year = '') ";
            } else {
                $strsql .= "AND year = '" . ciniki_core_dbQuote($ciniki, $args['year']) . "' ";
            }
        } elseif( isset($args['material']) ) {
            $strsql .= "AND ciniki_artcatalog_tags.tag_name = '" . ciniki_core_dbQuote($ciniki, $args['material']) . "' ";
        } elseif( isset($args['list']) ) {
            $strsql .= "AND ciniki_artcatalog_tags.tag_name = '" . ciniki_core_dbQuote($ciniki, $args['list']) . "' ";
        } elseif( isset($args['tracking']) ) {
            if( preg_match("/^(.*)-([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])-([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])$/", $args['tracking'], $m) ) {
                $strsql .= "AND ciniki_artcatalog_tracking.name = '" . ciniki_core_dbQuote($ciniki, $m[1]) . "' "
                    . "AND ciniki_artcatalog_tracking.start_date = '" . ciniki_core_dbQuote($ciniki, $m[2]) . "' "
                    . "AND ciniki_artcatalog_tracking.end_date = '" . ciniki_core_dbQuote($ciniki, $m[3]) . "' "
                    . "";
            } else {
                $strsql .= "AND ciniki_artcatalog_tracking.name = '" . ciniki_core_dbQuote($ciniki, $args['tracking']) . "' ";
                if( isset($args['start_date']) ) {
                    if( $args['start_date'] == '0000-00-00' || $args['start_date'] == '' ) {
                        $strsql .= "AND start_date = '0000-00-00' ";
                    } else {
                        $strsql .= "AND start_date = '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' ";
                    }
                }
                if( isset($args['end_date']) ) {
                    if( $args['end_date'] == '0000-00-00' || $args['end_date'] == '' ) {
                        $strsql .= "AND end_date = '0000-00-00' ";
                    } else {
                        $strsql .= "AND end_date = '" . ciniki_core_dbQuote($ciniki, $args['end_date']) . "' ";
                    }
                }
            }
        } 
        if( isset($args['type']) && $args['type'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
        }
        //
        // Check if output is PDF and sorted by catalog number instead of categories
        //
        if( isset($args['sortby']) && $args['sortby'] == 'catalognumber' ) {
            $strsql .= "ORDER BY catalog_number, name ";
        } elseif( isset($args['sortby']) && $args['sortby'] == 'year' ) {
            $strsql .= "ORDER BY sname COLLATE latin1_general_cs DESC, name ";
        } elseif( isset($args['sortby']) 
            && ($args['sortby'] == 'category' || $args['sortby'] == 'media' || $args['sortby'] == 'location' || $args['sortby'] == 'tracking') ) {
            $strsql .= "ORDER BY sname COLLATE latin1_general_cs, name ";
        } elseif( isset($args['section']) && $args['section'] == 'year' ) {
            $strsql .= "ORDER BY sname COLLATE latin1_general_cs DESC, name "
                . "";
        } else {
            $strsql .= "ORDER BY sname COLLATE latin1_general_cs, name "
                . "";
        }
        if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
            $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
        }
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'items', 'fname'=>'id', 'name'=>'item',
                'fields'=>array('id', 'title'=>'name', 'name', 'category'=>'category_name', 
                    'image_id', 'type', 'status', 'status_text', 'year', 'media', 'catalog_number', 
                    'size', 'framed_size', 'price', 'flags', 'location', 
                    'description', 'notes', 'awards', 'inspiration', 'sold', 'last_updated'),
                'maps'=>array('status_text'=>$maps['item']['status']),
                'utctots'=>array('last_updated'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $rsp['items'] = isset($rc['items']) ? $rc['items'] : array();
    }
/*
    //
    // Check if output is to be pdf
    //
    if( isset($args['output']) && $args['output'] == 'pdf' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'templates', $args['layout']);
        $function = 'ciniki_artcatalog_templates_' . $args['layout'];
        $rc = $function($ciniki, $args['tnid'], $items, $args);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        return array('stat'=>'ok');
    }

    //
    // Check if output is to be excel
    //
    if( isset($args['output']) && $args['output'] == 'excel' ) {
        $sections = array(
            'name' => 'Name',
            'items' => $items,
            );
        ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'templates', $args['layout']);
        $function = 'ciniki_artcatalog_templates_' . $args['layout'];
        $rc = $function($ciniki, $args['tnid'], $sections, $args);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        return array('stat'=>'ok');
    }
*/
    //
    // Add thumbnail information into list
    //
    if( isset($rsp['items']) && count($rsp['items']) > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
        foreach($rsp['items'] as $iid => $item) {
            if( isset($item['image_id']) && $item['image_id'] > 0 ) {
                $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], 
                    array('image_id'=>$item['image_id'], 'maxlength'=>75, 'last_updated'=>$item['last_updated'], 'reddot'=>$item['sold']));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $rsp['items'][$iid]['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                $rsp['items'][$iid]['price'] = '$' . number_format($item['price'], 2);
            }
        }
    }

    return $rsp;
}
?>
