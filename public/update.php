<?php
//
// Description
// ===========
// This method updates one or more elements of an existing item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to the item is a part of.
// artcatalog_id:	The ID of the item in the art catalog.
//
// type:			(optional) The type of the item.  Currently
//					only two types are supported, Painting and Photographs.
//
//					1 - Painting
//					2 - Photograph
//					3 - Jewelry
//					4 - Sculpture
//					5 - Fibre Art
//					6 - Clothing *future*
//					8 - Pottery 
//
// image_id:		(optional) The ID of the image in the images module to be displayed for the item.  This
//					can be uploaded before or after the item is added to the artcatalog.
//
// flags:			(optional) The bit flags for the item.
//	
//					0x01 - The item is for sale.
//					0x02 - The item is sold.  When displayed on the website, a red dot will be added to indicate sold.
//
// webflags:		(optional) The flags for displaying the item on the business website.
//
//					0x01 - Private item, not to be displayed on the website
//					0x10 - Category highlight item
//					0x20 - Media highlight item *future*
//					0x40 - Location highlight item *future*
//					0x80 - Year highlight item *future*
//
// name:			(optional) The name of the item.  This name must be unique within the business, as it's
//					also used to generate the permalink.  The permalink must be usique because it
//					is used as in the URL to reference an item.
//
// catalog_number:	(optional) A freeform field to store a catalog number if the user wants.  The
//					can be any string of characters.
//					
// category:		(optional) The name of the category the item is a part of.  Only one category can
//					be assigned to each item.
//
// year:			(optional) The year the item was completed, or in the case of a photograph, when the
//					photo was taken.
//
// media:			(optional) The type of media the item was created in.  This can be anything that the user
//					wishes, such as Oil, Pastel, etc for Paintings.  For Photographs, the information will be stored
//					but nothing done with it.
//
// size:			(optional) The size of the item, typically used for Paintings as the size of the original.  
//					For photographs this is typically the maximum size which the photo can be printed.  This
//					information is displayed on the website.
//
// framed_size:		(optional) The framed size of the item, which is only used for paintings and shown on the website.  
//					It can be stored for a photograph, but will not be shown on the website.
//
// price:			(optional) The price to purchase the item.  This can include the dollar '$' sign or not, it will 
//					will automatically be added if missing when displayed on the website.
//
// location:		(optional) Where the item is currently located.  This can be used to track if paintings are located
//					at home, or in a gallery.  
//
// description:		(optional) The description of the item, which will be displayed on the website.
//
// inspiration:		(optional) Where the inspiration came from for the item.  This information will not be displayed on the website.
//
// awards:			(optional) Any awards that the item has won.  This information is displayed along with the description
//					on the website.
//
// notes:			(optional) Any notes the creator has for the item.  This information is private and will not be displayed on the website.
//
// lists:			(optional) The list of tag names for the list tags the item is attached to.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_update(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'),
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Flags'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Web Flags'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Catalog Number'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'year'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Year'), 
        'month'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Month'), 
        'day'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Day'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Media'), 
        'size'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Size'), 
        'framed_size'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Framed Size'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Price'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Location'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Inspiration'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Awards'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
		'lists'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Lists'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	error_log(print_r($args, true));

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'checkAccess');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	if( isset($args['name']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
//		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink "
			. "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'600', 'msg'=>'You already have artwork with this name, please choose another name'));
		}
	}

	//
	// Get the existing information
	//
	$strsql = "SELECT id, name, category, permalink "
		. "FROM ciniki_artcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$item = $rc['item'];

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Check if there are any change to the lists the item is a part of
	//
	if( isset($args['lists']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.artcatalog', 'tag', $args['business_id'], 
			'ciniki_artcatalog_tags', 'ciniki_artcatalog_history', 
			'artcatalog_id', $args['artcatalog_id'], 1, $args['lists']);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'601', 'msg'=>'Unable to update lists', 'err'=>$rc['err']));
		}
		$updated = 1;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.artcatalog.item',
		$args['artcatalog_id'], $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return $rc;
	}

	//
	// Update the possible menu items available for artcatalog.  This is for the split gallery
	// between Paintings, Photographs, Jewelry, etc
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'private', 'updateWebSettings');
	$rc = ciniki_artcatalog_updateWebSettings($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		array_pop($ciniki['syncqueue']);
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1653', 'msg'=>'Unable to update web settings', 'err'=>$rc['err']));
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artcatalog');

	// Refresh facebook cache if image was updated
	if( isset($args['image_id']) ) {
		$ciniki['fbrefreshqueue'][] = array('business_id'=>$args['business_id'], 
			'url'=>'/gallery/category/' . urlencode(isset($args['category'])?$args['category']:$item['category']) . '/' . (isset($args['permalink'])?$args['permalink']:$item['permalink']));
	}

	return array('stat'=>'ok');
}
?>
