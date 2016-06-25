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
function ciniki_artcatalog_templates_thumbnails($ciniki, $business_id, $sections, $args) {

    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
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
    // Build the array of fields to include
    //
    if( isset($args['fields']) && $args['fields'] != '' ) {
        $fields = explode(',', $args['fields']);
    } else {
        $fields = array();
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
    // Create a custom class for this document
    //
    class MYPDF extends TCPDF {
        // set margins
        public $header_height = 35;
        public $footer_height = 10;
        public $top_margin = 10;
        public $left_margin = 21;
        public $right_margin = 18;
        public $business_name = '';
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
    }
    if( in_array('pagenumbers', $fields) ) {
        $pdf->pagenumbers = 'yes';
    }

    // Set PDF basics
    $pdf->SetCreator('Ciniki');
    $pdf->SetAuthor($business_details['name']);
    $pdf->SetTitle($args['pagetitle']);
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    $pdf->SetMargins($pdf->left_margin, $pdf->header_height, $pdf->right_margin);
    $pdf->SetHeaderMargin($pdf->top_margin);

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
    
        $cur_x = $pdf->left_margin;
        foreach($section['section']['items'] as $iid => $item) {
            $item = $item['item'];

            if( count($sections) > 1 && $pdf->getY() > ($pdf->getPageHeight() - 45) ) {
                $pdf->AddPage();
                $pdf->SetFont('', '', 16);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, $section['section']['name'] . ' (continued)', 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(10);
                $pdf->SetFont('', '', 10);
            }

            //
            // Load the image
            //
            if( $item['image_id'] > 0 ) {
                $rc = ciniki_images_hooks_loadThumbnail($ciniki, $business_id, array('image_id'=>$item['image_id'], 'maxlength'=>300));
                if( $rc['stat'] == 'ok' ) {
                    $image = $rc['image'];
                    $img = $pdf->Image('@'.$image, $cur_x, '', 30, 30, 'JPEG', '', '', false, 150, '', false, false, 0);
                    $cur_x += 36;
                }
            }

            if( $cur_x >= 180 ) {
                $pdf->Ln(37);
                $cur_x = $pdf->left_margin;
            }
        }
        if( $cur_x > 20 ) {
            $pdf->Ln(36);
        }
        $pdf->Ln(5);
    }

    //
    // Close and output the PDF
    //
    $pdf->Output($filename . '.pdf', 'D');

    return array('stat'=>'exit');
}
?>
