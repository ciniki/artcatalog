<?php
//
// Description
// ===========
// This function will return all the details for a artcatalog.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_artcatalog_get($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No artcatalog specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.get'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/timezoneOffset.php');
	$utc_offset = ciniki_users_timezoneOffset($ciniki);

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/datetimeFormat.php');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT ciniki_artcatalog.id, ciniki_artcatalog.name, permalink, image_id, type, type AS type_text, "
		. "ciniki_artcatalog.flags, "
		. "IF((ciniki_artcatalog.flags&0x01)=0x01, 'yes', 'no') AS forsale, "
		. "IF((ciniki_artcatalog.flags&0x02)=0x02, 'yes', 'no') AS sold, "
		. "CONCAT_WS('', IF((ciniki_artcatalog.webflags&0x01)=0x01, 'hidden', 'visible'), IF((ciniki_artcatalog.webflags&0x10)=0x10, ', category highlight', '')) AS website , "
		. "webflags, catalog_number, category, year, "
		. "media, size, framed_size, ciniki_artcatalog.price, ciniki_artcatalog.location, "
		. "ciniki_artcatalog.description, inspiration, awards, ciniki_artcatalog.notes, "
		. "ciniki_artcatalog.date_added, ciniki_artcatalog.last_updated "
//		. "ciniki_artcatalog_customers.customer_id AS customer_id, "
//		. "CONCAT_WS(' ', IFNULL(ciniki_customers.first, 'Unknown'), IFNULL(ciniki_customers.last, 'Customer')) AS customer_name, "
//		. "IF((ciniki_artcatalog_customers.flags&0x01)=0x01, 'yes', 'no') AS paid, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x10, 'yes', 'no') AS trade, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x20, 'yes', 'no') AS donation, "
//		. "IF((ciniki_artcatalog_customers.flags&0x10)=0x40, 'yes', 'no') AS gift, "
//		. "ciniki_artcatalog_customers.price AS customer_price, "
//		. "(ciniki_artcatalog_customers.price + ciniki_artcatalog_customers.taxes + ciniki_artcatalog_customers.shipping "
//			. "+ ciniki_artcatalog_customers.return_shipping + ciniki_artcatalog_customers.other_costs) AS customer_sale_total "
		. "FROM ciniki_artcatalog "
//		. "LEFT JOIN ciniki_artcatalog_customers ON (ciniki_artcatalog.id = ciniki_artcatalog_customers.artcatalog_id) "
//		. "LEFT JOIN ciniki_customers ON (ciniki_artcatalog_customers.customer_id = ciniki_customers.id "
//			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artcatalog.id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
//		. "ORDER BY ciniki_artcatalog.id, ciniki_customers.last, ciniki_customers.first "
		. "";
	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'artcatalog', array(
		array('container'=>'pieces', 'fname'=>'id', 'name'=>'piece',
			'fields'=>array('id', 'name', 'permalink', 'image_id', 'type', 'type_text', 'flags', 'webflags', 'catalog_number', 'category', 'year',
				'media', 'size', 'framed_size', 'forsale', 'sold', 'website', 'price', 'location', 'description', 'inspiration', 'awards', 'notes'),
			'maps'=>array('type_text'=>array('0'=>'Unknown', '1'=>'Painting', '2'=>'Photograph', '3'=>'Jewelry', '4'=>'Sculpture', '5'=>'Craft')),
			),
//		array('container'=>'sales', 'fname'=>'customer_id', 'name'=>'customer',
//			'fields'=>array('id'=>'customer_id', 'name'=>'customer_name', 'paid', 'trade', 'donation', 'gift', 'price'=>'customer_price', 'total'=>'customer_sale_total')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['pieces']) ) {
		return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'593', 'msg'=>'Unable to find item'));
	}
	$piece = $rc['pieces'][0]['piece'];

	return array('stat'=>'ok', 'piece'=>$piece);
}
?>
