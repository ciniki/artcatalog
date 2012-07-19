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
//					3 - Jewelry *future*
//					4 - Sculpture *future*
//					5 - Craft *future*
//					6 - Clothing *future*
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
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artcatalog_update($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'artcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No ID specified'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No type specified'),
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No image specified'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No flags specified'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No webflags specified'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No name specified'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No catalog number specified'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No category specified'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No year specified'), 
        'media'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No media specified'), 
        'size'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No size specified'), 
        'framed_size'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No framed_size specified'), 
        'price'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No price specified'), 
        'location'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No location specified'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No description specified'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No inspiration specified'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No awards specified'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No notes specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( isset($args['name']) ) {
		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'650', 'msg'=>'You already have artwork with this name, please choose another name'));
		}

	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/private/checkAccess.php');
    $rc = ciniki_artcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.artcatalog.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbUpdate.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Start building the update SQL
	//
	$strsql = "UPDATE ciniki_artcatalog SET last_updated = UTC_TIMESTAMP()";

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'name',
		'permalink',
		'image_id',
		'type',
		'flags',
		'webflags',
		'catalog_number',
		'category',
		'year',
		'media',
		'size',
		'framed_size',
		'price',
		'location',
		'description',
		'inspiration',
		'awards',
		'notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'artcatalog', 'ciniki_artcatalog_history', $args['business_id'], 
				2, 'ciniki_artcatalog', $args['artcatalog_id'], $field, $args[$field]);
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['artcatalog_id']) . "' ";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'artcatalog');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'603', 'msg'=>'Unable to update art'));
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'artcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
