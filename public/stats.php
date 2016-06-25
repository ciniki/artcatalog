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
// business_id:     The ID of the business to get the stats for.
// type:            (optional) Only get the stats for a particular type.
//
//                  1 - Painting
//                  2 - Photograph
//                  3 - Jewelry
//                  4 - Sculpture
// 
// Returns
// -------
// <stats sections="13">
//      <types>
//          <section type="painting" name="Paintings" count="21" />
//      </types>
//      <categories>
//          <section name="Landscape" count="14" />
//          <section name="Portrait" count="7" />
//      </categories>
//      <media>
//          <section name="Oil" count="12" />
//          <section name="Pastel" count="9" />
//      </media>
//      <locations>
//          <section name="Home" count="12" />
//          <section name="Gallery" count="4" />
//          <section name="Private Collection" count="5" />
//      </locations>
//      <years>
//          <section name="2012" count="15" />
//          <section name="2011" count="6" />
//      </years>
//      <lists>
//          <section name="County Fair" count="15" />
//          <section name="Private Gallery" count="6" />
//      </lists>
//      <tracking>
//          <section name="Private Collection" count="2" />
//      </tracking>
// </stats>
//
function ciniki_artcatalog_stats($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'), 
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
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.stats'); 
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

    if( isset($args['type']) && $args['type'] != '' ) {
        $args['type_id'] = 0;
        foreach($maps['item']['typecode'] as $type_id => $code) {
            if( $args['type'] == $code ) {
                $args['type_id'] = $type_id;
            }
        }
/*      if( $args['type'] == 'painting' ) {
            $args['type_id'] = 1;
        } elseif( $args['type'] == 'photograph' ) {
            $args['type_id'] = 2;
        } elseif( $args['type'] == 'jewelry' ) {
            $args['type_id'] = 3;
        } elseif( $args['type'] == 'sculpture' ) {
            $args['type_id'] = 4;
        } elseif( $args['type'] == 'fibreart' ) {
            $args['type_id'] = 5;
        } elseif( $args['type'] == 'pottery' ) {
            $args['type_id'] = 8;
        } else {
            $args['type_id'] = 0;
        } */
    }

    // Keep track of the total number of sections
    $num_sections = 0;

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');

    $rsp = array('stat'=>'ok', 'stats'=>array());
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    
    //
    // Get type stats
    //
    $strsql = "SELECT type, type AS name, COUNT(*) AS count FROM ciniki_artcatalog "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "GROUP BY type "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'type', 'name'=>'section',
            'fields'=>array('type', 'name', 'count'), 
            'maps'=>array('type'=>$maps['item']['typecode'],
                'name'=>$maps['item']['type'])),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['types'] = $rc['sections'];
    }

    //
    // Get the category stats
    //
    $strsql = "SELECT IF(category='', 'Unknown', category) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= ""
        . "GROUP BY category "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['categories'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the media stats
    //
    if( !isset($args['type_id']) || $args['type_id'] == 1 ) {
        $strsql = "SELECT IF(media='', 'Unknown', media) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        if( isset($args['type_id']) && $args['type_id'] > 0 ) {
            $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
                . "";
        }
        $strsql .= ""
    //      . "AND ciniki_artcatalog.type = 1 "
            . "GROUP BY media "
            . "ORDER BY name "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
            array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
                'fields'=>array('name', 'count')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sections']) ) {
            $rsp['stats']['media'] = $rc['sections'];
            $num_sections += count($rc['sections']);
        }
        }

        //
        // Get the location stats
        //
        $strsql = "SELECT IF(location='', 'Unknown', location) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= ""
        . "GROUP BY location "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['locations'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the year stats
    //
    $strsql = "SELECT IF(year='', 'Unknown', year) AS name, COUNT(*) AS count FROM ciniki_artcatalog "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= ""
        . "GROUP BY year "
        . "ORDER BY year DESC "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    //$rsp['stats'][3] = array('stat'=>array('name'=>'Years', 'sections'=>$rc['sections']));
    if( isset($rc['sections']) ) {
        $rsp['stats']['years'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the materials stats
    //
    $strsql = "SELECT IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS name, COUNT(*) AS count "
        . "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
        . "AND ciniki_artcatalog_tags.tag_type = 100 "
        . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= "GROUP BY tag_name "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['materials'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the lists stats
    //
    $strsql = "SELECT IF(ciniki_artcatalog_tags.tag_name='', '', ciniki_artcatalog_tags.tag_name) AS name, COUNT(*) AS count "
        . "FROM ciniki_artcatalog, ciniki_artcatalog_tags "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
        . "AND ciniki_artcatalog_tags.tag_type = 1 "
        . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= "GROUP BY tag_name "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['lists'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the tracking stats
    //
    $strsql = "SELECT IF(ciniki_artcatalog_tracking.name='', '', ciniki_artcatalog_tracking.name) AS name, COUNT(*) AS count "
        . "FROM ciniki_artcatalog, ciniki_artcatalog_tracking "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_artcatalog.id = ciniki_artcatalog_tracking.artcatalog_id "
        . "";
    if( isset($args['type_id']) && $args['type_id'] > 0 ) {
        $strsql .= "AND ciniki_artcatalog.type = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
            . "";
    }
    $strsql .= "GROUP BY name "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'sections', 'fname'=>'name', 'name'=>'section',
            'fields'=>array('name', 'count')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $rsp['stats']['tracking'] = $rc['sections'];
        $num_sections += count($rc['sections']);
    }

    //
    // Get the total count
    //
    $strsql = "SELECT 'total', COUNT(*) AS total FROM ciniki_artcatalog "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
//  if( isset($args['type']) && $args['type'] != '' ) {
//      $strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' "
//          . "";
//  }
    $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.artcatalog', 'count');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp['total'] = $rc['count']['total'];
    $rsp['sections'] = $num_sections;


    return $rsp;
}
?>
