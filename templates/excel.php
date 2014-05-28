<?php
//
// Description
// -----------
// This function will output a pdf document as a series of thumbnails.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artcatalog_templates_excel($ciniki, $business_id, $sections, $args) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'businessDetails');

	//
	// Load business details
	//
	$rc = ciniki_businesses_businessDetails($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['details']) && is_array($rc['details']) ) {	
		$business_details = $rc['details'];
	} else {
		$business_details = array();
	}

	//
	// Load INTL settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Build the array of fields to include
	//
	if( isset($args['fields']) && $args['fields'] != '' ) {
		$fields = explode(',', $args['fields']);
	}

	$titles = array(
		'catalog_number'=>'Catalog Number',
		'title'=>'Title',
		'category'=>'Category',
		'media'=>'Media',
		'size'=>'Size',
		'framed_size'=>'Framed Size',
		'price'=>'Price',
		'sold_label'=>'Sold',
		'location'=>'Location',
		'description'=>'Description',
		'awards'=>'Awards',
		'notes'=>'Notes',
		'inspiration'=>'Inspiration',
		);

	//
	// Create the excel spreadsheet
	//
	ini_set('memory_limit', '4192M');
	require($ciniki['config']['core']['lib_dir'] . '/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();
	$title = "Art Catalog";
	$sheet_num = 0;
	$sheet = $objPHPExcel->setActiveSheetIndex($sheet_num);
	foreach($sections as $sid => $section) {
		if( $sheet_num > 0 ) {
			$objPHPExcel->createSheet(NULL, $sheet_num);
		}
		$sheet = $objPHPExcel->getSheet($sheet_num);
		$sheet_title = $section['section']['name'];
		$sheet->setTitle($sheet_title);

		//
		// Create the title row
		//
		$i = 0;
		foreach($fields as $field) {
			if( isset($titles[$field]) ) {
				$sheet->setCellValueByColumnAndRow($i++, 1, $titles[$field], false);
			} else {
				$sheet->setCellValueByColumnAndRow($i++, 1, $field, false);
			}
		}
		$sheet->getStyle('A1:' . chr(65+$i) . '1')->getFont()->setBold(true);

		$row = 2;
		foreach($section['section']['items'] as $iid => $item) {
			$item = $item['item'];
			$i = 0;
			foreach($fields as $field) {
				if( isset($item[$field]) ) {
					$sheet->setCellValueByColumnAndRow($i++, $row, $item[$field], false);
				} else {
					$sheet->setCellValueByColumnAndRow($i++, $row, '', false);
				}
			}
			$row++;
		}

		$sheet_num++;
	}

	//
	// Close and output the PDF
	//
	header('Content-Type: application/vnd.ms-excel');
	$filename = preg_replace('/[^a-zA-Z0-9\-]/', '', $args['pagetitle']);
	header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	return array('stat'=>'exit');
}
?>
