<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get events for.
// type:            The list to return, either by category or year.
//
//                  - category
//                  - year
//
// type_name:       The name of the category or year to list.
//
// Returns
// -------
// <images>
//      [title="Slow River" permalink="slow-river" image_id="431" 
//          caption="Based on a photograph taken near Slow River, Ontario, Pastel, size: 8x10" sold="yes"
//          last_updated="1342653769"],
//      [title="Open Field" permalink="open-field" image_id="217" 
//          caption="An open field in Ontario, Oil, size: 8x10" sold="yes"
//          last_updated="1342653769"],
//      ...
// </images>
//
function ciniki_artcatalog_web_albumDetails($ciniki, $settings, $tnid, $args) {
    //
    // Get the gallery information
    //
    $album = array();

    return array('stat'=>'ok', 'album'=>$album);
}
?>
