<?php
// *********************************************************
// *** Aug. 2022: Remove old HuMo-genealogy system files ***
// *********************************************************
global $update_dir, $update_files;

function remove_the_folders($remove_folders)
{
    global $update_dir, $update_files;
    //echo '<br><br><br><br><br><br><br>';
    foreach ($remove_folders as $rf) {
        //unset ($update_dir,$update_files);
        //echo $rf . ' folder<br>';
        if (is_dir($rf)) {
            // *** Remove these old HuMo-genealogy files, a__ is just some random text (skip items)... ***
            listFolderFiles2($rf, array('a__', 'a__'), 'update_files');
            //echo $update_dir[0] . ' ' . $update_files[0];
            // *** Count down, because files must be removed first before removing directories ***
            if (is_array($update_files)) {
                for ($i = count($update_files) - 1; $i >= 0; $i--) {
                    if (!is_dir($update_dir[$i] . '/' . $update_files[$i])) {
                        unlink($update_dir[$i] . '/' . $update_files[$i]);
                    } else {
                        rmdir($update_dir[$i] . '/' . $update_files[$i]);
                    }
                    //echo $update_dir[$i] . '/' . $update_files[$i] . '<br>';
                }
            }
            rmdir($rf);
            unset($update_dir, $update_files);
        }
    }
}

function listFolderFiles2($dir, $exclude, $file_array)
{
    global $update_dir, $update_files;
    $ffs = scandir($dir);
    foreach ($ffs as $ff) {
        if (is_array($exclude) and !in_array($ff, $exclude)) {
            if ($ff != '.' && $ff != '..') {
                // *** Skip media files in ../media/, ../media/cms/ etc.
                //if (substr($dir,0,8)=='../media' AND !is_dir($dir.'/'.$ff) AND $ff != 'readme.txt'){
                //	// skip media files
                //}
                //else{
                $update_dir[] = $dir;
                $update_files[] = $ff;
                if (is_dir($dir . '/' . $ff)) listFolderFiles2($dir . '/' . $ff, $exclude, $file_array);
                //}
            }
        }
    }
}

// TODO maybe cleanup in reverse order (most recent first). To prevent all cleanups will be done at once. Just do one part at a time.

if (!isset($humo_option['cleanup_status'])) {
    // *** Remove old files ***
    $remove_file[] = 'gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged';
    $remove_file[] = 'gedcom_files/HuMo-gen test gedcomfile.ged'; // *** File is renamed to HuMo-genealogy ***
    $remove_file[] = '../include/.htaccess'; // *** This file blocks loading of several js scripts ***
    $remove_file[] = '../languages/.htaccess'; // *** This file blocks showing of language flag icons ***
    $remove_file[] = '../styles/Blauw.css';
    $remove_file[] = '../styles/Blue.css';
    $remove_file[] = '../styles/Brown.css';
    $remove_file[] = '../styles/Clear White.css';
    $remove_file[] = '../styles/Donkerbruin.css';
    $remove_file[] = '../styles/Elegant Blue.css';
    $remove_file[] = '../styles/Elegant Corsiva.css';
    $remove_file[] = '../styles/Elegant Green.css';
    $remove_file[] = '../styles/Elegant Mauve.css';
    $remove_file[] = '../styles/Elegant_Blue.css';
    $remove_file[] = '../styles/Elegant_Green.css';
    $remove_file[] = '../styles/Experiment_HTML5.css';
    $remove_file[] = '../styles/Green.css';
    $remove_file[] = '../styles/Groen.css';
    $remove_file[] = '../styles/Heelal.css';
    $remove_file[] = '../styles/Mauve fixed menu.css';
    $remove_file[] = '../styles/Mauve left menu.css';
    $remove_file[] = '../styles/Orange.css';
    $remove_file[] = '../styles/Oranje.css';
    $remove_file[] = '../styles/Paars.css';
    $remove_file[] = '../styles/Purple.css';

    foreach ($remove_file as $rfile) {
        if (file_exists($rfile)) {
            //echo $rfile.'<br>';
            unlink($rfile);
        }
    }

    // *** Remove old folders ***
    $remove_folders[] = '../fanchart';
    $remove_folders[] = '../fpdf16';
    $remove_folders[] = '../humo_mobile';
    $remove_folders[] = '../include/fpdf16';
    $remove_folders[] = '../include/jqueryui/css';
    $remove_folders[] = '../include/jqueryui/development-bundle';
    $remove_folders[] = '../include/jqueryui/js';
    $remove_folders[] = '../include/lightbox';
    $remove_folders[] = '../include/sliderbar';
    $remove_folders[] = '../languages/fa DISABLED';
    $remove_folders[] = '../lightbox';
    $remove_folders[] = '../menu';
    $remove_folders[] = '../popup_menu';
    $remove_folders[] = '../sliderbar';
    $remove_folders[] = '../styles/images_blue';
    $remove_folders[] = '../styles/images_green';
    $remove_folders[] = '../styles/imagesantique';
    $remove_folders[] = '../styles/imagesblauw';
    $remove_folders[] = '../styles/imagesdonkerbruin';
    $remove_folders[] = '../styles/imagesgroen';
    $remove_folders[] = '../styles/imagesheelal';
    $remove_folders[] = '../styles/imagesoranje';
    $remove_folders[] = '../styles/imagesoriginal';
    $remove_folders[] = '../styles/imagespaars';
    $remove_folders[] = '../styles/imagessilverline';
    $remove_folders[] = '../styles/imageswhite';
    $remove_folders[] = '../styles/imagesyossi';
    $remove_folders[] = '../talen';
    $remove_folders[] = 'languages';        // admin/languages
    $remove_folders[] = 'menu';            // admin/languages
    $remove_folders[] = 'statistieken';    // admin/statistieken

    remove_the_folders($remove_folders);

    // *** First cleanup, insert cleanup status into settings ***
    $sql = "INSERT INTO humo_settings SET
        setting_variable='cleanup_status',
        setting_value='1'";
    @$dbh->query($sql);
    $humo_option['cleanup_status'] = '1';
}

// *** Second cleanup of files ***
if (isset($humo_option['cleanup_status']) and $humo_option['cleanup_status'] == '1') {
    unset($remove_folders, $update_dir, $update_files);

    $remove_folders[] = '../include/securimage';
    remove_the_folders($remove_folders);

    // *** Update "update_status" to number 2 ***
    $result = $dbh->query("UPDATE humo_settings SET setting_value='2' WHERE setting_variable='cleanup_status'");
    $humo_option['cleanup_status'] = '2';
}

// *** Third cleanup of files ***
if (isset($humo_option['cleanup_status']) and $humo_option['cleanup_status'] == '2') {
    // *** Remove old files ***
    $remove_file[] = '../info.php';
    $remove_file[] = '../credits.php';
    $remove_file[] = '../README.TXT';
    $remove_file[] = '../lijst.php';
    $remove_file[] = '../lijst_namen.php';
    $remove_file[] = '../gezin.php';

    foreach ($remove_file as $rfile) {
        if (file_exists($rfile)) {
            //echo $rfile.'<br>';
            unlink($rfile);
        }
    }

    // *** Remove old folders ***
    // *** For some reason it doesn't work properly to use multiple dir's in one array ***
    //$remove_folders[] = 'include/ckeditor';
    //$remove_folders[] = 'include/kcfinder';
    //remove_the_folders($remove_folders);
    unset($remove_folders, $update_dir, $update_files);
    $remove_folders[] = 'include/ckeditor';
    remove_the_folders($remove_folders);

    unset($remove_folders, $update_dir, $update_files);
    $remove_folders[] = 'include/kcfinder';
    remove_the_folders($remove_folders);

    // *** Update "update_status" to number 3 ***
    $result = $dbh->query("UPDATE humo_settings SET setting_value='3' WHERE setting_variable='cleanup_status'");
    $humo_option['cleanup_status'] = '3';
}

// *** Fourth cleanup of files ***
if (isset($humo_option['cleanup_status']) and $humo_option['cleanup_status'] == '3') {
    // *** Remove old files ***
    $remove_file[] = '../address.php';
    $remove_file[] = '../addresses.php';
    $remove_file[] = '../admin/include/index_inc.php';
    $remove_file[] = '../admin/include/login.php';
    $remove_file[] = '../admin/include/prefix_editor.php';
    $remove_file[] = '../admin/prefixes.php';
    $remove_file[] = '../birthday_list.php';
    $remove_file[] = '../cms_pages.php';
    $remove_file[] = '../cookies.php';
    $remove_file[] = '../fontsize.js';
    $remove_file[] = '../footer.php';
    $remove_file[] = '../gedcom.css';
    $remove_file[] = '../gedcom_mobile.css';
    $remove_file[] = '../help.php';
    $remove_file[] = '../latest_changes.php';
    $remove_file[] = '../list_names.php';
    $remove_file[] = '../mailform.php';
    $remove_file[] = '../print.css';
    $remove_file[] = '../register.php';
    $remove_file[] = '../sources.php';
    $remove_file[] = '../statistics.php';
    $remove_file[] = '../tree_index.php';
    $remove_file[] = '../user_settings.php';
    $remove_file[] = '../views/addressView.php';
    $remove_file[] = '../views/descendant_chartView.php';
    $remove_file[] = '../views/family_pdfView.php';
    $remove_file[] = '../views/family_rtfView.php';
    foreach ($remove_file as $rfile) {
        if (file_exists($rfile)) {
            //echo $rfile . '<br>';
            unlink($rfile);
        }
    }

    // *** Update "update_status" to number 3 ***
    $result = $dbh->query("UPDATE humo_settings SET setting_value='3' WHERE setting_variable='cleanup_status'");
    $humo_option['cleanup_status'] = '4';
}

// Remark: for testing reset $humo_option['cleanup_status'] option.
