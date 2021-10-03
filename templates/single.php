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
function ciniki_artcatalog_templates_single($ciniki, $tnid, $sections, $args) {

    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheJPEG');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'tenantDetails');

    //
    // Load tenant details
    //
    $rc = ciniki_tenants_tenantDetails($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['details']) && is_array($rc['details']) ) {   
        $tenant_details = $rc['details'];
    } else {
        $tenant_details = array();
    }

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
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
        // set margins
        public $header_height = 35;
        public $footer_height = 15;
        public $top_margin = 13;
        public $left_margin = 20;
        public $right_margin = 20;
        public $tenant_name = '';
        public $title = '';
        public $pagenumbers = 'no';

        public function Header() {
            $this->SetFont('helvetica', 'B', 20);
            $this->Cell(0, 20, $this->title, 0, false, 'C', 0, '', 0, false, 'M', 'B');
        }

        // Page footer
        public function Footer() {
            if( $this->pagenumbers == 'yes' ) {
                $this->SetY(-15);
                $this->SetFont('helvetica', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
                    0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    $filename = '';
    $pdf->title = $args['pagetitle'];
    if( $args['pagetitle'] != '' ) {
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $args['pagetitle']));
        $filename = preg_replace('/__/', '_', $filename);
    }
    if( in_array('pagenumbers', $fields) ) {
        $pdf->pagenumbers = 'yes';
    }

    // Set PDF basics
    $pdf->SetCreator('Ciniki');
    $pdf->SetAuthor($tenant_details['name']);
    $pdf->SetTitle($args['pagetitle']);
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins($pdf->left_margin, $pdf->header_height, $pdf->right_margin);
    $pdf->SetHeaderMargin($pdf->top_margin);
    $pdf->SetFooterMargin($pdf->footer_height);

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
    $page_num = 1;
    foreach($sections as $sid => $section) {
        //
        // Check if we need a page break
        //
//      if( $pdf->getY() > ($pdf->getPageHeight() - 55) ) {
//          $pdf->AddPage();
//      }

        foreach($section['section']['items'] as $iid => $item) {
            if( $page_num > 1 ) {
                $pdf->AddPage();
            }
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
            if( $item['status'] == 20 ) {
                if( in_array('price', $fields) && $item['price'] != '' ) {
                    $img_title .= ($img_title != ''?' - ':'') . numfmt_format_currency($intl_currency_fmt, $item['price'], $intl_currency);
                } elseif( in_array('status_text', $fields) && $item['status_text'] != '' ) {
                    $img_title .= ($img_title != ''?' - ':'') . $item['status_text'];
                }
            } else {
                if( in_array('status_text', $fields) && $item['status_text'] != '' ) {
                    $img_title .= ($img_title != ''?' - ':'') . $item['status_text'];
                }
            }
//          if( in_array('price', $fields) && $item['price'] != '' ) {
//              if( $img_title != '' ) { $img_title .= ' - '; }
//              $img_title .= numfmt_format_currency($intl_currency_fmt, $item['price'], $intl_currency);
//          }
//          if( in_array('sold_label', $fields) && $item['sold'] == 'yes' ) {
//              if( $img_title != '' ) { $img_title .= ' (SOLD)'; }
//              else { $img_title .= " SOLD"; }
//          }

            //
            // Add the other details
            //
            $details = '';
            $divider = "\n";
            if( (in_array('description', $fields) && $item['description'] != '')
                || (in_array('awards', $fields) && $item['awards'] != '')
                || (isset($item['publications']) && in_array('publications', $fields) && $item['publications'] != '')
                || (in_array('notes', $fields) && $item['notes'] != '')
                || (in_array('inspiration', $fields) && $item['inspiration'] != '')
                ) {
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
            // Calculate how many lines are required at the bottom of the page
            //
            $nlines = 0;
            $blank_lines = 0;
            $details_height = 0;
            if( $img_title != '' ) {
                $details_height += 12;
            }
            if( $details != '' ) {
                $nlines += $pdf->getNumLines($details, 176);
            }
            if( in_array('description', $fields) && $item['description'] != '' ) {
                $nlines += $pdf->getNumLines($item['description'], 176);
                $blank_lines++;
            }
            if( in_array('awards', $fields) && $item['awards'] != '' ) {
                $nlines += $pdf->getNumLines($item['awards'], 176);
                $blank_lines++;
            }
            if( isset($item['publications']) && in_array('publications', $fields) && $item['publications'] != '' ) {
                $nlines += $pdf->getNumLines($item['publications'], 176);
                $blank_lines++;
            }
            if( in_array('notes', $fields) && $item['notes'] != '' ) {
                $nlines += $pdf->getNumLines($item['notes'], 176);
                $blank_lines++;
            }
            if( in_array('inspiration', $fields) && $item['inspiration'] != '' ) {
                $nlines += $pdf->getNumLines($item['inspiration'], 176);
                $blank_lines++;
            }

            $img_box_width = 176;
            $img_box_height = $pdf->getPageHeight() - $pdf->footer_height - $pdf->header_height;
            $details_height += 10 + ($nlines * 6);
            if( $blank_lines > 0 ) {
                $details_height += ($blank_lines-1) * 3;
            }
            $img_box_height -= ($details_height + 10);

            //
            // Load the image
            //
            if( $item['image_id'] > 0 ) {
                $rc = ciniki_images_loadCacheJPEG($ciniki, $tnid, $item['image_id'], 2000, 2000);
                if( $rc['stat'] == 'ok' ) {
                    $image = $rc['image'];
                    $img = $pdf->Image('@'.$image, '', '', $img_box_width, $img_box_height, 'JPEG', '', '', false, 300, '', false, false, 0, 'CM');
                }
            }

            $pdf->SetX(0);
            $pdf->SetY($pdf->getPageHeight() - $pdf->footer_height - $details_height);

            //
            // Add the image title
            //
            if( $img_title != '' ) {
                $pdf->SetFont('', 'B', '16');
                $pdf->Cell(176, 12, $img_title, 0, 1, 'L');
            }
        
            if( $details != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $details, 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }
            
            //
            // Add the description
            //
            if( in_array('description', $fields) && $item['description'] != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $item['description'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }

            //
            // Add the awards
            //
            if( in_array('awards', $fields) && $item['awards'] != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $item['awards'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }

            //
            // Add the publications
            //
            if( isset($item['publications']) && in_array('publications', $fields) && $item['publications'] != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $item['publications'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }

            //
            // Add the notes
            //
            if( in_array('notes', $fields) && $item['notes'] != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $item['notes'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }

            //
            // Add the inspiration
            //
            if( in_array('inspiration', $fields) && $item['inspiration'] != '' ) {
                $pdf->SetFont('', '', '12');
                $pdf->MultiCell(176, 8, $item['inspiration'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
            }
            $page_num++;
        }
    }

    //
    // Close and output the PDF
    //
    $pdf->Output($filename . '.pdf', 'D');

    return array('stat'=>'exit');
}
?>
