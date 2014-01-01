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
function ciniki_artcatalog_templates_list($ciniki, $business_id, $sections, $args) {

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
	$pdf->title = $args['title'];
	if( $args['title'] != '' ) {
		$filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $args['title']));
	}

	// Set PDF basics
	$pdf->SetCreator('Ciniki');
	$pdf->SetAuthor($business_details['name']);
	$pdf->SetTitle($args['title']);
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	// set margins
	$header_height = 25;
	$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// Set font
	$pdf->SetFont('times', 'BI', 10);
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
	$w = array(30, 120, 30);
	foreach($sections as $sid => $section) {
		//
		// Check if we need a page break
		//
		if( $pdf->getY() > ($pdf->getPageHeight() - 55) ) {
			$pdf->AddPage();
		}

		//
		// Add the section title
		//
		if( count($sections) > 1 ) {
			$pdf->SetFont('', '', 16);
			$pdf->SetFont('helvetica', 'B', 16);
			$pdf->Cell(0, 15, $section['section']['name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
			$pdf->Ln(10);
			$pdf->SetFont('', '', 10);
		}
	
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
			if( in_array('price', $fields) && $item['price'] != '' ) {
				if( $img_title != '' ) { $img_title .= ' - '; }
				$img_title .= numfmt_format_currency($intl_currency_fmt, $item['price'], $intl_currency);
			}
			if( in_array('sold', $fields) && $item['sold'] == 'yes' ) {
				if( $img_title != '' ) { $img_title .= ' (SOLD)'; }
				else { $img_title .= " SOLD"; }
			}

			//
			// Add the other details
			//
			$details = '';
			$divider = "\n";
			if( in_array('description', $fields) && $item['description'] != '' ) {
				$divider = ', '; 
			}
			if( in_array('media', $fields) && $item['media'] != '' ) {
				$details .= 'Media: ' . $item['media'];
			}
			if( in_array('size', $fields) && $item['size'] != '' ) {
				if( $details != '' ) { $details .= $divider; }
				$details .= 'Size: ' . $item['size'];
				if( in_array('framed_size', $fields) && $item['framed_size'] != '' ) {
					$details .= ', Framed: ' . $item['framed_size'];
				}
			} elseif( in_array('framed_size', $fields) && $item['framed_size'] != '' ) {
				if( $details != '' ) { $details .= $divider; }
				$details .= 'Framed Size: ' . $item['framed_size'];
			}
			if( in_array('location', $fields) && $item['location'] != '' ) {
				if( $details != '' ) { $details .= $divider; }
				$details .= 'Location: ' . $item['location'];
			}
			
			//
			// Calculate the size of image title, details, description
			$nlines = 0;
			if( $img_title != '' ) {
				$nlines += 1;
			}
			if( $details != '' ) {
				$nlines += $pdf->getNumLines($details, 140);
			}
			if( in_array('description', $fields) && $item['description'] != '' ) {
				$nlines += $pdf->getNumLines($item['description'], 140);
			}

			$item_height = 50;
			if( $nlines > 5 ) {
				$item_height = 15 + ($nlines * 7);
			}
			if( $pdf->getY() > ($pdf->getPageHeight() - $item_height) ) {
				if( count($sections) > 1 ) {
					$pdf->AddPage();
					$pdf->SetFont('', '', 16);
					$pdf->SetFont('helvetica', 'B', 16);
					$pdf->Cell(0, 15, $section['section']['name'] . ' (continued)', 0, false, 'L', 0, '', 0, false, 'M', 'M');
					$pdf->Ln(10);
					$pdf->SetFont('', '', 10);
				} else {
					$pdf->AddPage();
				}
			}

			$item_y = $pdf->getY();
			//
			// Load the image
			//
			if( $item['image_id'] > 0 ) {
				$rc = ciniki_images_loadCacheThumbnail($ciniki, $item['image_id'], 300);
				if( $rc['stat'] == 'ok' ) {
					$image = $rc['image'];
					$img = $pdf->Image('@'.$image, $cur_x, '', 30, 30, 'JPEG', '', '', false, 150, '', false, false, 0);
					$cur_x += 36;
				}
			}

			//
			// Add the image title
			//
			if( $img_title != '' ) {
				$pdf->SetX($cur_x);
				$pdf->SetFont('', 'B', '12');
				$pdf->Cell(140, 8, $img_title, 0, 2, 'L');
			}
		
			if( $details != '' ) {
				$pdf->SetX($cur_x);
				$pdf->SetFont('', '', '10');
				$pdf->MultiCell(140, 8, $details, 0, 'L', false, 2, '', '', true, 0, false, true, 0, 'T');
			}
			
			//
			// Add the description
			//
			if( in_array('description', $fields) && $item['description'] != '' ) {
				$pdf->SetX($cur_x);
				$pdf->SetFont('', '', '10');
				$pdf->MultiCell(140, 8, $item['description'], 0, 'L', false, 2, '', '', true, 0, false, true, 0, 'T');
			}
			
			$diff_y = $pdf->getY() - $item_y;
			if( $diff_y > 0 && $diff_y < 36 ) {
				$pdf->Ln((36-$diff_y) + 5);
			} else {
				$pdf->Ln(10);
			}
		}
	}

	//
	// Close and output the PDF
	//
	$pdf->Output($filename . '.pdf', 'D');

	return array('stat'=>'exit');
}
?>