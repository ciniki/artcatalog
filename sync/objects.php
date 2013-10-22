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
function ciniki_artcatalog_sync_objects($ciniki, &$sync, $business_id, $args) {
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'objects');
	return ciniki_artcatalog_objects($ciniki);
}
?>
