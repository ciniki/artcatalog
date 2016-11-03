<?php
//
// Description
// -----------
// This method will backup a businesses artcatalog to the ciniki-backups folder.
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business on the local side to check sync.
//
//
function ciniki_artcatalog_backupModule(&$ciniki, $business) {

    //
    // Check the backup directory exists
    //
    $backup_dir = $business['backup_dir'] . '/ciniki.artcatalog';
    if( !file_exists($backup_dir) ) {
        if( mkdir($backup_dir, 0755, true) === false ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.6', 'msg'=>'Unable to create backup directory'));
        }
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');

    //
    // Load the artcatalog for the business
    //
    $strsql = "SELECT "
        . "ciniki_artcatalog.id, "
        . "ciniki_artcatalog.uuid, "
        . "ciniki_artcatalog.name, "
        . "ciniki_artcatalog.type, "
        . "ciniki_artcatalog.type AS type_name, "
        . "ciniki_artcatalog.flags, "
        . "ciniki_artcatalog.webflags, "
        . "ciniki_artcatalog.image_id, "
        . "ciniki_images.original_filename AS image_original_filename, "
        . "ciniki_artcatalog.catalog_number, "
        . "ciniki_artcatalog.category, "
        . "ciniki_artcatalog.year, "
        . "ciniki_artcatalog.month, "
        . "ciniki_artcatalog.day, "
        . "ciniki_artcatalog.media, "
        . "ciniki_artcatalog.size, "
        . "ciniki_artcatalog.framed_size, "
        . "ciniki_artcatalog.price, "
        . "ciniki_artcatalog.location, "
        . "ciniki_artcatalog.awards, "
        . "ciniki_artcatalog.notes, "
        . "ciniki_artcatalog.description, "
        . "ciniki_artcatalog.inspiration, "
        . "ciniki_artcatalog_tags.tag_name AS lists "
        . "FROM ciniki_artcatalog "
        . "LEFT JOIN ciniki_images ON ("
            . "ciniki_artcatalog.image_id = ciniki_images.id "
            . "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
            . ") "
        . "LEFT JOIN ciniki_artcatalog_tags ON ("
            . "ciniki_artcatalog.id = ciniki_artcatalog_tags.artcatalog_id "
            . "AND ciniki_artcatalog_tags.tag_type = 1 "
            . "AND ciniki_artcatalog_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
            . ") "
        . "WHERE ciniki_artcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
        . "ORDER BY ciniki_artcatalog.type, ciniki_artcatalog.name "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'types', 'fname'=>'type', 'name'=>'type',
            'fields'=>array('type', 'name'=>'type_name'),
            'maps'=>array('type_name'=>array('1'=>'Paintings', '2'=>'Photographs', '3'=>'Jewelry', '4'=>'Sculptures', '5'=>'Fibre Arts', '8'=>'Pottery'))),
        array('container'=>'items', 'fname'=>'id', 'name'=>'item',
            'fields'=>array('id', 'uuid', 'name', 'type', 'flags', 'webflags', 'image_id', 'image_original_filename',
                'catalog_number', 'category', 'year', 'month', 'day', 'media', 'size', 'framed_size',
                'price', 'location', 'awards', 'notes', 'description', 'inspiration', 'lists'),
            'maps'=>array('month'=>array('0'=>'', '1'=>'Jan', '2'=>'Feb', '3'=>'Mar',
                '4'=>'Apr', '5'=>'May', '6'=>'Jun', '7'=>'Jul', '8'=>'Aug', '9'=>'Sep',
                '10'=>'Oct', '11'=>'Nov', '12'=>'Dec')),
            'dlists'=>array('lists'=>',')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        $artcatalog_types = $rc['types'];
    } else {
        // No items
        return array('stat'=>'ok');
    }

    //
    // Load exhibitied
    //
    $strsql = "SELECT id, artcatalog_id, "
        . "name, external_number, "
        . "IFNULL(DATE_FORMAT(start_date, '%b %e, %Y'), '') AS start_date, "
        . "IFNULL(DATE_FORMAT(end_date, '%b %e, %Y'), '') AS end_date, "
        . "notes "
        . "FROM ciniki_artcatalog_tracking "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
        . "ORDER BY artcatalog_id, name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'items', 'fname'=>'artcatalog_id',
            'fields'=>array('artcatalog_id')),
        array('container'=>'tracking', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'external_number',
                'start_date', 'end_date', 'notes')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $tracking_items = $rc['items'];
    } else {
        $tracking_items = array();
    }

    //
    // Load extra images
    //
    $strsql = "SELECT ciniki_artcatalog_images.id, "
        . "ciniki_artcatalog_images.artcatalog_id, "
        . "ciniki_artcatalog_images.name, "
        . "ciniki_artcatalog_images.image_id, "
        . "ciniki_artcatalog_images.sequence, "
        . "ciniki_artcatalog_images.description, "
        . "ciniki_images.original_filename "
        . "FROM ciniki_artcatalog_images "
        . "LEFT JOIN ciniki_images ON (ciniki_artcatalog_images.image_id = ciniki_images.id "
            . "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
            . ") "
        . "WHERE ciniki_artcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business['id']) . "' "
        . "ORDER BY ciniki_artcatalog_images.artcatalog_id, ciniki_artcatalog_images.sequence "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artcatalog', array(
        array('container'=>'items', 'fname'=>'artcatalog_id',
            'fields'=>array('artcatalog_id')),
        array('container'=>'images', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'image_id',
                'sequence', 'description', 'original_filename')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $image_items = $rc['items'];
    } else {
        $image_items = array();
    }


    
    //
    // Create the spreadsheet
    //
    $objPHPExcel = new PHPExcel();
    $title = "Art Catalog";
    $sheet_num = 0;
    $sheet = $objPHPExcel->setActiveSheetIndex($sheet_num);
    foreach($artcatalog_types as $tid => $type) {
        $type = $type['type'];
        if( $sheet_num > 0 ) {
            $objPHPExcel->createSheet(NULL, $sheet_num);
        }
        $sheet = $objPHPExcel->getSheet($sheet_num);
        $sheet_title = $type['name'];
        $sheet->setTitle($sheet_title);

        if( $type['type'] == 1 ) {
            // Painting
            $headers = array('ID', 'Name', 'Category', 'Options', 'Media', 'Size', 'Framed Size', 'Price', 
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'media', 'size', 'framed_size', 'price', 
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        } elseif( $type['type'] == 2 ) {
            // Photograph
            $headers = array('ID', 'Name', 'Category', 'Options', 'Size', 'Price',
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'size', 'price', 
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        } elseif( $type['type'] == 3 ) {
            // Jewelry
            $headers = array('ID', 'Name', 'Category', 'Options', 'Price', 
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'price',
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        } elseif( $type['type'] == 4 ) {
            // Sculpture
            $headers = array('ID', 'Name', 'Category', 'Options', 'Size', 'Media', 'Price', 
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'size', 'media', 'price',
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        } elseif( $type['type'] == 5 ) {
            // Fibre Art
            $headers = array('ID', 'Name', 'Category', 'Options', 'Size', 'Price', 
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'size', 'price',
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        } elseif( $type['type'] == 8 ) {
            // Pottery 
            $headers = array('ID', 'Name', 'Category', 'Options', 'Media', 'Size', 'Price', 
                'Image', 'Number', 'Date', 'Location', 'Description', 'Notes');
            $fields = array('uuid', 'name', 'category', 'options', 'media', 'size', 'price',
                'image_original_filename', 'catalog_number', 'date', 'location', 'description', 'notes');
        }

        $i = 0;
        foreach($headers as $header) {
            $sheet->setCellValueByColumnAndRow($i++, 1, $header, false);
        }
        $sheet->getStyle('A1:' . chr(65+$i) . '1')->getFont()->setBold(true);

        $row = 2;
        foreach($type['items'] as $iid => $item) {
            $item = $item['item'];
            $artcatalog_id = $item['id'];

            //
            // Create the file name
            //
            $item['filename'] = preg_replace("/[^a-zA-Z0-9\ \.\-\_\[\]\(\)\:\;\'\"]/", ' ', $item['name']);
            $artcatalog_types[$tid]['type']['items'][$iid]['item']['filename'] = $item['filename'];
            
            //
            // Create the directory for the item
            //
            $item_backup_dir = $backup_dir . '/' . $type['name'] . '/' . $item['filename'];
            if( !file_exists($item_backup_dir) ) {
                if( mkdir($item_backup_dir, 0755, true) === false ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.7', 'msg'=>'Unable to create backup directory for artcatalog item: ' . $type['name'] . '/' . $item['name']));
                }
            }

            $options = '';
            if( ($item['flags']&0x02) > 0 ) {
                $options = 'Sold';
            } elseif( ($item['flags']&0x01) > 0 ) {
                $options = 'For Sale';
            }
            $artcatalog_types[$tid]['type']['items'][$iid]['item']['options'] = $options;
            $item['options'] = $options;

            $date = '';
            if( $item['month'] != '' && $item['day'] != '' && $item['day'] != '0' && $item['year'] != '' ) { 
                $date = $item['month'] . ' ' . $item['day'] . ', ' . $item['year'];
            } elseif( $item['month'] != '' && $item['year'] != '' ) { 
                $date = $item['month'] . ' ' . $item['year'];
            } elseif( $item['year'] != '' ) {
                $date = $item['year'];
            }
//          $date .= $item['month']; }
//          if( $item['year'] != '' ) { $date .= $item['year']; }
//          if( $item['month'] != '' && $item['month'] != '0' ) { $date .= ($date!=''?'-':'') . $item['month']; }
//          if( $item['day'] != '' && $item['day'] != '0' ) { $date .= ($date!=''?'-':'') . $item['day']; }
            $artcatalog_types[$tid]['type']['items'][$iid]['item']['date'] = $date;
            $item['date'] = $date;

            $i = 0;
            foreach($fields as $field) {
                $sheet->setCellValueByColumnAndRow($i++, $row, $item[$field], false);
            }
            $row++;
        }
        $i = 0;
        foreach($fields as $field) {
            $sheet->getColumnDimension(chr(65+$i))->setAutoSize(true);
            if( $field == 'price' ) {
                $col = chr(65+$i);
                $sheet->getStyle($col . '2:' . $col . $row)->getNumberFormat()->setFormatCode("#,##0.00");
            }
            $i++;
        }

        $sheet_num++;
    }

    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save($backup_dir . '/Catalog.xls');
    $objPHPExcel->disconnectWorksheets();
    unset($objWriter, $objPHPExcel);

    //
    // Save the images
    //
    foreach($artcatalog_types as $type) {
        $type = $type['type'];
        foreach($type['items'] as $item) {
            $item = $item['item'];
            $artcatalog_id = $item['id'];

            //
            // Make sure the directory for the item exists
            //
            $item_backup_dir = $backup_dir . '/' . $type['name'] . '/' . $item['filename'];
            if( !file_exists($item_backup_dir) ) {
                if( mkdir($item_backup_dir, 0755, true) === false ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.8', 'msg'=>'Unable to create backup directory for artcatalog item: ' . $type['name'] . '/' . $item['name']));
                }
            }

            $objPHPExcel = new PHPExcel();
            $title = $item['name'];
            $sheet_num = 0;
            $sheet = $objPHPExcel->setActiveSheetIndex($sheet_num);
            $sheet->setTitle('Details');
            $row = 1;

            //
            // Save the details about the image
            //
            $details = '';
            $details .= "Name: " . $item['name'] . "\n";
            $sheet->setCellValueByColumnAndRow(0, $row, 'Name', false);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1, $row, $item['name'], false);
            $row++;

            if( $item['category'] != '' ) {
                $details .= "Category: " . $item['category'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Category', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['category'], false);
                $row++;
            }

            if( $item['options'] != '' ) {
                $details .= "Options: " . $item['options'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Options', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['options'], false);
                $row++;
            }

            if( $item['media'] != '' ) {
                $details .= "Media: " . $item['media'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Media', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['media'], false);
                $row++;
            }

            if( $item['size'] != '' ) {
                $details .= "Size: " . $item['size'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Size', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['size'], false);
                $row++;
            }

            if( $item['framed_size'] != '' ) {
                $details .= "Framed Size: " . $item['framed_size'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Framed Size', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['framed_size'], false);
                $row++;
            }

            if( $item['price'] != '' ) {
                $details .= "Price: " . $item['price'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Price', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['price'], false);
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode("#,##0.00");
                $row++;
            }

            if( $item['image_original_filename'] != '' ) {
                $details .= "Image: " . $item['image_original_filename'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Image', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['image_original_filename'], false);
                $row++;
            }

            if( $item['catalog_number'] != '' ) {
                $details .= "Catalog Number: " . $item['catalog_number'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Number', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['catalog_number'], false);
                $row++;
            }

            if( $item['date'] != '' ) {
                $details .= "Date: " . $item['date'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Date', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['date'], false);
                $row++;
            }

            if( $item['location'] != '' ) {
                $details .= "Location: " . $item['location'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Location', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['location'], false);
                $row++;
            }

            if( $item['lists'] != '' ) {
                $details .= "Lists: " . $item['lists'] . "\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Lists', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['lists'], false);
                $row++;
            }

            if( $item['description'] != '' ) {
                $details .= "Description:\n" . $item['description'] . "\n\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Description', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['description'], false);
                $row++;
            }
            if( $item['awards'] != '' ) {
                $details .= "Awards:\n" . $item['awards'] . "\n\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Awards', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['awards'], false);
                $row++;
            }
            if( $item['inspiration'] != '' ) {
                $details .= "Inspiration:\n" . $item['inspiration'] . "\n\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Inspiration', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['inspiration'], false);
                $row++;
            }
            if( $item['notes'] != '' ) {
                $details .= "Notes:\n" . $item['notes'] . "\n\n";
                $sheet->setCellValueByColumnAndRow(0, $row, 'Notes', false);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, $item['notes'], false);
                $row++;
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);

            $objPHPExcel->createSheet(NULL, 1);
            $sheet = $objPHPExcel->getSheet(1);
            $sheet->setTitle('Exhibited');

            $sheet->setCellValueByColumnAndRow(0, 1, 'Number', false);
            $sheet->setCellValueByColumnAndRow(1, 1, 'Name', false);
            $sheet->setCellValueByColumnAndRow(2, 1, 'Start', false);
            $sheet->setCellValueByColumnAndRow(3, 1, 'End', false);
            $sheet->setCellValueByColumnAndRow(4, 1, 'Notes', false);
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);

            $row = 2;
            if( isset($tracking_items[$artcatalog_id]['tracking']) ) {
                $details .= "\nExhibited:\n";
                foreach($tracking_items[$artcatalog_id]['tracking'] as $titem) {
                    $details .= $titem['name'];
                    if( $titem['external_number'] != '' ) { $details .= ', ' . $titem['external_number']; }
                    if( $titem['start_date'] != '' && $titem['start_date'] != '0000-00-00' ) {
                        $details .= '(' . $titem['start_date'];
                        if( $titem['end_date'] != '' && $titem['end_date'] != '0000-00-00' ) {
                            $details .= ' - ' . $titem['end_date'];
                        }
                        $details .= ')';
                    }
                    $details .= "\n";
                    if( $titem['notes'] != '' ) {
                        $details .= "    " . $titem['notes'] . "\n";
                    }
                    $sheet->setCellValueByColumnAndRow(0, $row, $titem['external_number'], false);
                    $sheet->setCellValueByColumnAndRow(1, $row, $titem['name'], false);
                    $sheet->setCellValueByColumnAndRow(2, $row, ($titem['start_date']=='0000-00-00'?'':$titem['start_date']), false);
                    $sheet->setCellValueByColumnAndRow(3, $row, ($titem['end_date']=='0000-00-00'?'':$titem['end_date']), false);
                    $sheet->setCellValueByColumnAndRow(4, $row, $titem['notes'], false);
                }
            }

            
            //
            // Save the primary image
            //
            if( $item['image_id'] != '' && $item['image_id'] > 0 ) {
                $rc = ciniki_images_loadImage($ciniki, $business['id'], $item['image_id'], 'original');
                if( $rc['stat'] != 'ok' ) {
                    error_log('BACKUP-ERR[' . $business['name'] . ']: ' . $rc['err']['code'] . ' - ' . $rc['err']['msg']);
                    continue;
                }
                $original = $rc['image'];
                $h = fopen($item_backup_dir . '/' . $item['image_original_filename'], 'w');
                if( $h ) {
                    fwrite($h, $original->getImageBlob());
                    fclose($h);
                } else {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.9', 'msg'=>'Unable to save image'));
                }
            }

            if( isset($image_items[$artcatalog_id]['images']) ) {
                $details .= "\nAdditional Images:\n";
                foreach($image_items[$artcatalog_id]['images'] as $image) {
                    $rc = ciniki_images_loadImage($ciniki, $business['id'], $image['image_id'], 'original');
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $original = $rc['image'];
                    $h = fopen($item_backup_dir . '/' . $image['original_filename'], 'w');
                    $details .= $image['original_filename'] . "\n";
                    if( $h ) {
                        fwrite($h, $original->getImageBlob());
                        fclose($h);
                    } else {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.artcatalog.10', 'msg'=>'Unable to save image'));
                    }
                }
            }

            file_put_contents($item_backup_dir . '/' . $item['filename'] . '.txt', $details);

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet = $objPHPExcel->setActiveSheetIndex(0);
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($item_backup_dir . '/' . $item['filename'] . '.xls');
            $objPHPExcel->disconnectWorksheets();
            unset($objWriter, $objPHPExcel);
        }
    }


    return array('stat'=>'ok');
}
?>
