<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_artcatalog_hooks_uiSettings($ciniki, $business_id, $args) {

    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'settings'=>array(), 'menu_items'=>array(), 'settings_menu_items'=>array());  

    //
    // Get the settings
    //
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_artcatalog_settings', 'business_id', 
        $business_id, 'ciniki.artcatalog', 'settings', '');
    if( $rc['stat'] == 'ok' && isset($rc['settings']) ) {
        $rsp['settings'] = $rc['settings'];
    }

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['business']['modules']['ciniki.artcatalog'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>6700,
            'label'=>'Art Catalog', 
            'edit'=>array('app'=>'ciniki.artcatalog.main'),
            'add'=>array('app'=>'ciniki.artcatalog.main', 'args'=>array('artcatalog_id'=>'0')),
            'search'=>array(
                'method'=>'ciniki.artcatalog.searchQuick',
                'args'=>array(),
                'container'=>'items',
                'cols'=>3,
                'cellClasses'=>array(
                    '0'=>'thumbnail',
                    '1'=>'multiline',
                    '2'=>'multiline',
                    ),
                'cellValues'=>array(
                    '0'=>'if( d.item.image != null && d.item.image != \'\' ) {'
                        . '\'<img width="75px" height="75px" src="\' + d.item.image + \'" />\';'
                        . '} else {'
                        . '\'<img width="75px" height="75px" src="/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg"/>\''
                        . '}',
                    '1'=>'var sold = \'\';'
                        . 'var price = \'<b>Price</b>: \';'
                        . 'var media = \'\';'
                        . 'var size = \'\';'
                        . 'if( d.item.sold == \'yes\' ) { sold = \' <b>SOLD</b>\'; }'
                        . 'if( d.item.price != \'\' ) {'
                            . 'if( d.item.price[0] != \'$\' ) { price += \'$\' + d.item.price; }'
                            . 'else { price += d.item.price; }'
                        . '}'
                        . 'if( d.item.type == 1 ) {'
                            . '\'<span class="maintext">\' + d.item.name + \'</span><span class="subtext"><b>Media</b>: \' + d.item.media + \', <b>Size</b>: \' + d.item.size + \', <b>Framed</b>: \' + d.item.framed_size + \', \' + price + sold + \'</span>\'; '
                        . '} else if( d.item.type == 2 ) {'
                            . '\'<span class="maintext">\' + d.item.name + \'</span><span class="subtext">\' + price + sold + \'</span>\'; '
                        . '} else if( d.item.type == 3 ) {'
                            . '\'<span class="maintext">\' + d.item.name + \'</span><span class="subtext"><b>Size</b>: \' + d.item.size + \', \' + price + sold + \'</span>\'; '
                        . '} else if( d.item.type == 3 ) {'
                            . '\'<span class="maintext">\' + d.item.name + \'</span><span class="subtext">\' + price + sold + \'</span>\'; '
                        . '}',
                    '2'=>'\'<span class="maintext">\' + d.item.catalog_number + \'</span><span class="subtext">\' + d.item.location + \'</span>\';',
                    ),
                'noData'=>'No items found',
                'edit'=>array('method'=>'ciniki.artcatalog.main', 'args'=>array('artcatalog_id'=>'d.item.id;')),
                ),
            );
        $rsp['menu_items'][] = $menu_item;

        $rsp['settings_menu_items'][] = array('priority'=>6700, 'label'=>'Art Catalog', 'edit'=>array('app'=>'ciniki.artcatalog.settings'));
    } 

    return $rsp;
}
?>
