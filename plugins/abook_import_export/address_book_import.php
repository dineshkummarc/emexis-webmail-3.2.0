<?php
/**
 * address_book_import.php
 *
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Copyright (c) 2007 Tomas Kuliavas <tokul@users.sourceforge.net>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Import csv files for address book
 * This takes a comma delimited file uploaded from addressbook.php
 * and allows the user to rearrange the field order to better
 * fit the address book. A subset of data is manipulated to save time.
 * @version $Id: address_book_import.php,v 1.26 2007/06/09 08:38:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_import_export
 */

/** SquirrelMail init */
if (file_exists('../../include/init.php')) {
    /* sm 1.5.2+*/

    /* main init script */
    include_once('../../include/init.php');
} else {
    /* sm 1.4.0+ */

    /** @ignore */
    define('SM_PATH', '../../');
    /* main init script */
    include_once(SM_PATH . 'include/validate.php');
}

/* load address book functions */
include_once(SM_PATH . 'functions/addressbook.php');
/* load sqm_baseuri() (sm 1.4.0-1.4.5,1.5.0) function */
include_once(SM_PATH . 'functions/display_messages.php');
/* load own functions */
include_once(SM_PATH . 'plugins/abook_import_export/functions.php');

// Local Variables
$errorstring = '';
$finish = '';
$csvmax = 0;
$key = 0;
$x = 0;
$row = 0;
$cols = 0;
$colspan = 0;
$c = 0;
$error = 0;
$reorg = array();
$selrow = '';

// FIXME: not sure if global declarations are needed
global $color, $squirrelmail_language, $default_charset;

// Make sure that $default_charset is set to correct value
set_my_charset();
   
if (! sqGetGlobalVar('finish',$finish,SQ_POST)) {
    // Stage 1. Process uploaded file

    //displayPageHeader($color, "None");

    // initialize address book. don't display errors. don't init remote backends
    // object is used by form. do it now in order to avoid domain switching.
    $abook = addressbook_init(false, true);
    
    // switch domain
    bindtextdomain('abook_import_export',SM_PATH . 'plugins/abook_import_export/locale');
    textdomain('abook_import_export');

    if (function_exists('bind_textdomain_codeset')) {
        if ($squirrelmail_language == 'ja_JP') {
            bind_textdomain_codeset ('abook_import_export', 'EUC-JP');
        } else {
            bind_textdomain_codeset ('abook_import_export', $default_charset );
        }
    }

    // Check to make sure the user actually put a file in the upload file box.
    $smusercsv = $_FILES['smusercsv'];

    if ($smusercsv['tmp_name'] == '' || $smusercsv['size'] == 0) {
        // Detect PHP 4.2.0+ upload error codes (http://www.php.net/features.file-upload.errors)
        $upload_error = _("Please select a file for uploading.");
        if (isset($smusercsv['error']) && $smusercsv['error']!=0 ) {
            switch($smusercsv['error']) {
            case 1:
                $upload_error = _("The uploaded file exceeds PHP upload_max_filesize limits.");
                break;
            case 2:
                $upload_error = _("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML.");
                break;
            case 3:
                $upload_error = _("The uploaded file was only partially uploaded.");
                break;
            case 4:
                $upload_error = _("No file was uploaded.");
                break;
            case 6:
                $upload_error = _("Missing a temporary directory.");
                break;
            case 7:
                $upload_error = _("Failed to write file to disk.");
                break;
            case 8:
                // File upload stopped by extension. 'security library' is more user friendly.
                $upload_error = _("File upload stopped by security library.");
                break;
            default:
                $upload_error = _("Unknown upload error.");
                break;
            }
        }

        $error_msg = html_tag('p',$upload_error)
            .html_tag('p',sprintf(_("Return to main %sAddress Book%s page."),
                                  '<a href="' . sqm_baseuri() .  'src/addressbook.php">',
                                  '</a>'), 'center');
        aie_error_box($error_msg,_("Upload error"),true);
    } elseif ( $smusercsv['size'] > $aie_csv_maxsize ) {
        // i18n: %s displays 'somenumber B', 'somenumber KB' or 'somenumber MB'.
        $error_msg = sprintf(_("Imported CSV file is too big. Contact your system administrator, if you want to import files, that are bigger than %s."),aie_display_size($aie_csv_maxsize));
        aie_error_box($error_msg,'',true);
    }

    /**
     * Remove old csvdata from session and set initial $csvdata and $csvorder values.
     * $csvdata stores imported data. $csvorder is used by aie_CSVProcess() to detect
     * order of imported fields. variable is accessed through references in order 
     * to avoid globalization of the variable. $error can store fatal processing errors.
     */
    unset($_SESSION['csvdata']);
    $csvdata = array();
    $csvorder = array();
    $error = '';

    // find unused file name in attachment directory.
    $temp_file = md5($smusercsv['tmp_name']);

    // handle misconfigured $attachment_dir
    if (preg_match('/^[a-z]\:\\\\/i',$attachment_dir)) {
        // windows full path x:\something. relative path issues are ignored, because they will
        // require mods in all SquirrelMail scripts.
        if (substr($attachment_dir,-1,1)!='\\') {
            $attachment_dir.= '\\';
        }
    } elseif (substr($attachment_dir,-1,1)!='/') {
        $attachment_dir.= '/';
    }

    while (file_exists($attachment_dir . $temp_file)) {
        // calculate new md5sum until we find place to store data
        $temp_file = md5($temp_file);
    }

    // don't open uploaded file directly. Move it to SM temp directory before using it.
    if (@move_uploaded_file($smusercsv['tmp_name'],$attachment_dir . $temp_file)) {

        $csvfile = fopen($attachment_dir . $temp_file,"r");

        if (!$csvfile) {
            echo '<br><br>'
                .'<table align="center">'
                .'<tr><td>'
                ._("Error, could not open address file.")
                .'</td></tr>'
                .'</table>';
            exit;
        }

        if (! sqgetGlobalVar('field_delimiter',$field_delimiter,SQ_POST) ||
            ! in_array($field_delimiter,array("'",'"',',',';','custom'))) {
            $field_delimiter = ',';
        } elseif ($field_delimiter=='custom') {
            if (! sqgetGlobalVar('custom_field_delimiter',$field_delimiter,SQ_POST)) {
                $field_delimiter = ',';
            }
        }

        if (check_php_version(4,3,0) && 
            sqgetGlobalVar('text_delimiter',$text_delimiter,SQ_POST) &&
            in_array($text_delimiter,array("'",'"',',',';','custom'))) {
            
            if ($text_delimiter=='custom') {
                if (! sqgetGlobalVar('custom_text_delimiter',$text_delimiter,SQ_POST)) {
                    $text_delimiter = '"';
                }
            }

            // compare text and field delimiters
            if ($text_delimiter == $field_delimiter) {
                $error_msg = _("You must use different symbols for text and field delimiters.");                
                //aie_error_box($error_msg,'',true);
            }
        } else {
            if ($field_delimiter=='"') {
                $text_delimiter = "'";
            } else {
                $text_delimiter = '"';
            }
        }

        // use own wrapper to solve differences between 4.3.0+ and older
        while ($csvarray = aie_fgetcsv($csvfile,$smusercsv['size'],$field_delimiter,$text_delimiter)) {
            // Let fgetcsv deal with splitting the line into it's parts. (I.E. it deals with quoted commas right.
            $temp = aie_CSVProcess($csvarray,$text_delimiter,$csvorder);

            if (is_string($temp)) {
                $error = $temp;
                // remove all processed data
                $csvdata = array();
                // remove all line processing errors
                $errorstring = '';
                // stop csv processing
                break;
            } elseif (count($temp) >1) {
                $csvdata[$key] = $temp;
                $key++;
            } elseif (isset($temp[0]) && !empty($temp[0])) {
                // row returned only one element or delimiter was not correct
                $errorstring .= '<li>' . htmlentities($temp[0]) . '</li>';
            }
            
            // After this, the function was just doing some calculations, and returned without a problem.
            if(count($csvarray) > $csvmax) {
                $csvmax = count($csvarray);
            }
        }

        // close file handle
        fclose($csvfile);
        // remove uploaded file
        unlink($attachment_dir . $temp_file);

        /* Compact imported data (csv can store empty fields) */

        // array_fill() is available only in php 4.2+
        // $clean_array = array_fill(0,$csvmax,true)
        $clean_array = array();
        foreach ($csvdata as $idx => $entry) {
            // detect empty columns
            for($i = 0; $i < $csvmax; $i++) {
                if (!empty($entry[$i])) {
                    $clean_array[$i] = false;
                } elseif (!isset($clean_array[$i])) {
                    // see array_fill() comments
                    $clean_array[$i] = true;
                }
            }
        }
        // Unset empty columns
        foreach ($csvdata as $idx => $entry) {
            for($i = 0; $i < $csvmax; $i++) {
                // don't touch first four columns in order to preserve 
                // firstname (1), lastname (2), email (3) order
                if ($i > 3 && $clean_array[$i]) unset($csvdata[$idx][$i]);
            }
        }
        // Rebuild array index
        $new_csvdata = array();
        foreach ($csvdata as $entry) {
            array_push($new_csvdata,array_values($entry));
        }
        $csvdata = $new_csvdata;
        // Get new column counter
        $csvmax = 0;
        foreach ($csvdata as $entry) {
            $count = count($entry);
            if ($csvmax < $count) $csvmax = $count;
        }
        /* End of compacting code */

        // create final import form only when some data is available
        if (count($csvdata) > 0) {
            echo '<form id="formImportConfirm" method="post" action="' . $PHP_SELF . '">';

            echo '<center><table width="95%" frame="void" cellspacing="1">';    // user's data table
        
            // Here I will create the headers that I want.
            echo '<tr bgcolor="' . $color[9] . '" align="center">';
            // Title of column with row numbers
            
            // Title of column with omit checkbox
            
            
            for($x = 0; $x < $csvmax; $x++) { // The Drop down boxes to select what each column is
                echo '<td>';
                aie_create_Select($csvmax,$x);
                echo '</td>';
            }

            echo '<td width="1">' .  _("Omit") . '</td>';
            echo '</tr>';
            
            while ($row < count($csvdata)) {
                if (count($csvdata[$row]) >= 5) {    // This if ensures the minimum number of columns
                    $cols = count($csvdata[$row]);    // so importing can function for all 5 fields
                } else {
                    $cols = 5;
                }

                // unused
                // $colspan = $cols + 1;

                if ($row % 2) {                   // Set up the alternating colored rows
                    echo '<tr bgcolor="' . $color[0] . '">';
                } else {
                    echo '<tr>';
                }
                               
                for($c = 0; $c < $cols; $c++) { // For each column in the current row
                    if (isset($csvdata[$row][$c]) 
                        && $csvdata[$row][$c] != '') {
                        // if not empty, put data in cell.
                        echo '<td NOWRAP>' . ($csvdata[$row][$c]) . '</td>';
                    } else {
                        // if empty, put space in cell keeping colors correct.
                        echo '<td>&nbsp;</td>';
                    }
                }
                 // print row number (start counter from 1 and not from 0)                
                // Print the omit checkbox, to be checked before write
                echo '<td width="1" align="center"><input type="checkbox" name="sel' . $row . '"></td>';

                echo '</tr>';
                $row++;
            }

            echo '</table></center><br />';

            // save uploaded and processed csv data in session
            sqsession_register($csvdata,'csvdata');

            $form=aie_select_backend('write',$bcount);
            if ($bcount>1) {
                echo _("Add to address book: ");
                echo aie_select_backend('write',$bcount);
                echo "<br />\n";
            } else {
                echo $form;
            }
            // display import button only after table is loaded

            echo '<div style="text-align:right;padding-right:10px">
                    <input type="submit" class="finish" name="finish" value="' . _("Confirm") . '" tabindex="4">
                  </div>';
            echo '</form>';
        } else {
            /**
             * $csvdata is empty. User tried to import empty file or $error contains fatal 
             * processing error message.
             */
            if (empty($error)) $error = _("Nothing to import");
            $error .= '<br /><p align="center"><a href="' . sqm_baseuri() . 'src/addressbook.php">' . _("Return to Address Book") . '</a></p>';
            aie_error_box($error);
        }

        if(strlen($errorstring)) {
            echo _("The following rows have errors")
                . ': <br /><ul>' . $errorstring . '</ul>';
        }
    } else {
        // unable to move file to temp directory
        aie_error_box(_("Can't move uploaded file to attachment directory."));
    }
} else {
    // Stage 2. save addresses

    // Since we will print something to the page at this point
    //displayPageHeader($color, 'None');

    /** create address book object without remote backends */
    $abook = addressbook_init(false, true);

    if (!empty($abook->error)) {
        aie_error_box(nl2br(htmlspecialchars($abook->error)),'',true);
    }

    /* set domain, but don't switch it. we need domain for dgettext calls and
     * main code is still running in squirrelmail domain.
     */
    bindtextdomain('abook_import_export',SM_PATH . 'plugins/abook_import_export/locale');
    if (function_exists('bind_textdomain_codeset')) {
        if ($squirrelmail_language == 'ja_JP') {
            bind_textdomain_codeset ('abook_import_export', 'EUC-JP');
        } else {
            bind_textdomain_codeset ('abook_import_export', $default_charset );
        }
    }
    
    /* get csvdata from session */
    if (! sqGetGlobalVar('csvdata',$csvdata,SQ_SESSION) || ! is_array($csvdata)) {
        // $csvdata is not available or is not array.
        $error_msg = html_tag('p',_("Unable to access uploaded data. Contact your system administrator."))
            .html_tag('p',sprintf(_("Return to main %sAddress Book%s page."),
                                  '<a href="' . sqm_baseuri() .  'src/addressbook.php">',
                                  '</a>'), 'center');
        aie_error_box($error_msg,'',true);
    }
    
    while($row < count($csvdata)) {
        if (count($csvdata[$row]) >= 5) {    // This if ensures the minimum number of columns
            $cols = count($csvdata[$row]);    // so importing can function for all 5 fields
        } else {
            $cols = 5;
        }

        $reorg = array('', '', '', '', '');

        for ($c=0; $c < $cols; $c++) {
            // Reorganize the data to fit the header cells that the user chose
            // concatenate fields based on user input into text boxes.
            $column = "COL$c";

            // check if form posts call needed columns
            if(sqGetGlobalVar($column,$colno,SQ_POST)) {
                if($colno != 5)  {
                    if ($colno == 4) {
                        // label field is optional. It might be missing in some wierd csv imports:
                        $reorg[4] .= (isset($csvdata[$row][$c]) ? $csvdata[$row][$c] : '') . ";";
                    } else {
                        $reorg[$colno] = $csvdata[$row][$c];
                        $reorg[$c] = trim($reorg[$c],"\r\n \"");
                    }
                }
            }
        }

        if (isset($reorg[4])) {
            $reorg[4] = trim($reorg[4],";");
        }

        $csvdata[$row] = $reorg;
        unset($reorg); // So that we don't get any weird information from a previous rows

        // If finished, do the import. This uses Pallo's excellent class and object stuff 
        $selrow = 'sel' . $row;
        
        // import row only when Omit option is not set.
        if (! sqGetGlobalVar($selrow,$testvar,SQ_POST)) {
            if (preg_match('[ \\:\\|\\#\\"\\!]i', $csvdata[$row][0])) {
                $csvdata[$row][0] = '';
            }

            // Here we should create the right data to input 
            if (count($csvdata[$row]) < 5) {
                array_pad($csvdata[$row],5,'');
            }

            $addaddr['nickname']  = utf8_encode($csvdata[$row][0]);
            $addaddr['firstname'] = utf8_encode($csvdata[$row][1]);
            $addaddr['lastname']  = utf8_encode($csvdata[$row][2]);
            $addaddr['email']     = utf8_encode($csvdata[$row][3]);
            $addaddr['label']     = utf8_encode($csvdata[$row][4]);

            if (! sqGetGlobalVar('backend',$backend,SQ_POST)) {
                $backend=$abook->localbackend;
            } else {
                // make sure that it is integer
                $backend=(int) $backend;
            }

            if ( ! $abook->add($addaddr,$backend)) {
                // displays row number that can't be imported. SquirrelMail 
                // address book backend error message is displayed after it.
                $errorstring .= sprintf(dgettext('abook_import_export',"Row %d:"),($row+1)) . ' ' . $abook->error . "<br />\n";
                $error++;
            }

            unset($addaddr); // Also so we don't get any weird information from previous rows
        }

        $row++;
    }

    // Now that we've uploaded this information, we dont' need this variable anymore, aka cleanup
    unset($_SESSION['csvdata']);

    textdomain('abook_import_export');

    // Print out that we've completed this operation
    if ($error) {
        echo '<br />'
            . _("There were errors uploading the data, as listed below. Entries not listed here were uploaded.") 
            . '<br /> ' . $errorstring . '<br /> ';
    } else {
        echo '<br /><br /><center><h1><strong>' 
            ._("Upload Completed!")
            .'</strong></h1>'
            .'<p>' . _("Click on the link below to verify your work.") . '</p>'
            .'</center>';
    }
    
    echo '<br /><br /><p align="center"><a href="' . sqm_baseuri() . 'src/addressbook.php">' . _("Addresses") . '</a></p>';

}
?>

</body>
</html>
