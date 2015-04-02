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
function ciniki_artcatalog_templates_pricelist($ciniki, $business_id, $sections, $args) {

	require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
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
	} else {
		$fields = array();
	}

	//
	// Create a custom class for this document
	//
	class MYPDF extends TCPDF {
		public $business_name = '';
		public $title = '';
		public function Header() {
			$this->SetFont('helvetica', 'B', 20);
			$this->Cell(0, 20, $this->title, 0, false, 'C', 0, '', 0, false, 'M', 'B');
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			$this->Cell(0, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
				0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
	}

	//
	// Start a new document
	//
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$filename = '';
	$pdf->title = $args['pagetitle'];
	if( $args['pagetitle'] != '' ) {
		$filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $args['pagetitle']));
	}

	// Set PDF basics
	$pdf->SetCreator('Ciniki');
	$pdf->SetAuthor($business_details['name']);
	$pdf->SetTitle($args['pagetitle']);
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	// set margins
	$header_height = 25;
	$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// Set font
	$pdf->SetFont('times', 'BI', 12);
	$pdf->SetCellPadding(0);

	// Add a page
	$pdf->AddPage();
	$pdf->SetFillColor(255);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(51);
	$pdf->SetLineWidth(0.15);

	//
	// Add the artcatalog items
	//
	$w = array(140, 40);
	$headings = array('Item', 'Price');
	$section_count = 0;
	foreach($sections as $sid => $section) {
		//
		// Check if we need a page break
		//
		if( $pdf->getY() > ($pdf->getPageHeight() - 55) ) {
			$pdf->AddPage();
		}

		if( $section_count > 0 ) {
			$pdf->Ln();
		}

		//
		// Add the section title
		//
		if( count($sections) > 1 ) {
			$pdf->SetFont('', '', 16);
			$pdf->SetFont('helvetica', 'B', 16);
			$pdf->Cell(0, 15, $section['section']['name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
			$pdf->Ln(5);
			$pdf->SetFont('', '', 12);
		}

		//
		// Add the table headings
		//
		$pdf->SetFont('', 'B', 12);
		$pdf->SetCellPadding(2);
		$pdf->SetFillColor(224);
		$pdf->Cell($w[0], 6, $headings[0], 1, 0, 'C', 1);
		$pdf->Cell($w[1], 6, $headings[1], 1, 0, 'C', 1);
		$pdf->Ln();
		$pdf->SetFillColor(236);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');

		$fill = 0;
		foreach($section['section']['items'] as $iid => $item) {
			$cur_x = 17;
			$item = $item['item'];

			//
			// Determine the title
			//
			$img_title = '';
			if( in_array('catalog_number', $fields) && $item['catalog_number'] != '' ) {
				$img_title = $item['catalog_number'];
			}
			if( isset($item['name']) && $item['name'] != '' ) {
				if( $img_title != '' ) { $img_title .= ' - '; }
				$img_title .= $item['name'];
			}

			//
			// Add the other details
			//
			$divider = ", ";
			if( in_array('media', $fields) && $item['media'] != '' ) {
				$img_title .= $divider . $item['media'];
			}
			if( in_array('size', $fields) && $item['size'] != '' ) {
				$img_title .= $divider . $item['size'];
				if( in_array('framed_size', $fields) && $item['framed_size'] != '' ) {
					$img_title .= ', (' . $item['framed_size'] . ')';
				}
			} elseif( in_array('framed_size', $fields) && $item['framed_size'] != '' ) {
				$img_title .= $divider . $item['framed_size'];
			}
			if( in_array('location', $fields) && $item['location'] != '' ) {
				$img_title .= $divider . $item['location'];
			}
			
			//
			// Calculate the size of image title, details, description
			//
			$nlines = 0;
			$nlines += $pdf->getNumLines($img_title, $w[0]);
			$lh = 6;
			if( $nlines == 2 ) {
				$lh = 3+($nlines*6);
			} elseif( $nlines > 2 ) {
				$lh = 2+($nlines*6);
			}

			$item_height = 25;
			if( $pdf->getY() > ($pdf->getPageHeight() - 22 - $lh) ) {
				if( count($sections) > 1 ) {
					$pdf->AddPage();
					$pdf->SetFont('helvetica', 'B', 16);
					$pdf->Cell(0, 15, $section['section']['name'] . ' (continued)', 0, false, 'L', 0, '', 0, false, 'M', 'M');
					$pdf->Ln(5);
					$pdf->SetFont('', 'B', 12);
					// Add table headings
					$pdf->SetCellPadding(2);
					$pdf->SetFillColor(224);
					$pdf->Cell($w[0], 6, $headings[0], 1, 0, 'C', 1);
					$pdf->Cell($w[1], 6, $headings[1], 1, 0, 'C', 1);
					$pdf->Ln();
					$pdf->SetFillColor(236);
					$pdf->SetTextColor(0);
					$pdf->SetFont('');
					$fill = 0;
				} else {
					$pdf->AddPage();
				}
			}

			//
			// Add the title
			//
			if( $img_title != '' ) {
				$pdf->MultiCell($w[0], $lh, $img_title, 1, 'L', $fill, 
					0, '', '', true, 0, false, true, 0, 'T', false);
			}

			//
			// Add the price
			//
			if( $item['status'] == 20 ) {
//			if( in_array('sold_label', $fields) && $item['sold'] == 'yes' ) {
				$price = numfmt_format_currency($intl_currency_fmt, $item['price'], $intl_currency);
			} else {
				$price = $item['status_text'];
			}
			$pdf->MultiCell($w[1], $lh, $price, 1, 'R', $fill, 
				0, '', '', true, 0, false, true, 0, 'T', false);
		
			$pdf->Ln();
			$fill=!$fill;
		}
		$section_count++;
	}

	//
	// Close and output the PDF
	//
	$pdf->Output($filename . '.pdf', 'D');

	return array('stat'=>'exit');
}
?>
