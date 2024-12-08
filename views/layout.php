<?php

// *** Set cookies before any output ***

// this if checks if this is special url query for giving the file - it gives the file if user is authorized to get it
if (isset($_GET['page']) && $_GET['page'] == 'serve_file' && isset($_GET['media_dir']) && isset($_GET['media_filename'])) {
    global $dataDb, $tree_id, $dbh, $db_functions;
    if (isset($_GET['media_filename']) && $_GET['media_filename']) {
        $media_filename = $_GET['media_filename'];
    }
    if (isset($_GET['media_dir']) && $_GET['media_dir']) {
        $media_dir = $_GET['media_dir'];
    }
    // we must check if file has category directory prefix from existing prefixes so we must preserve directory and concatenate with original filename (removing thumb only)
    // does photocat_prefix has any dependance to tree_id??
    $photocat_qry = "SELECT * FROM humo_photocat WHERE photocat_prefix!='none'";
    $datasql = $dbh->query($photocat_qry);
    $rowCount = $datasql->rowCount();
    $prefixes = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $photocat_db = $datasql->fetch(PDO::FETCH_OBJ);
        $photocat_prefix = $photocat_db->photocat_prefix;
        if (!in_array($photocat_prefix, $prefixes)) $prefixes[] = $photocat_prefix;
    }

    $matching_prefix = '';

    foreach ($prefixes as $key => $prefix) {
        if (strpos($media_filename, $prefix . DIRECTORY_SEPARATOR) === 0) {
            $prefix_slash = $prefix . DIRECTORY_SEPARATOR;
            // we make the filename without dir origin filename prefix and slash
            $media_filename_with_prefix_dir =  substr($media_filename, strlen($prefix_slash));
            $matching_prefix = $prefix_slash;
        }
    }

    if (isset($media_filename_with_prefix_dir)) {
        $media_filename_for_thumb_check = $media_filename_with_prefix_dir;
    } else {
        $media_filename_for_thumb_check = $media_filename;
    }
    // echo 'MFN<br>';
    // echo $media_filename;
    // echo '<br>';
    // echo 'FTC<br>';
    // echo $media_filename_for_thumb_check;
    // echo '<br>';

    // we are checking if this is thum - if it is we need to check privacy for origin file, not thumb
    // exception will be situation where user puts jpg file with "thumb_" begining in it's name - now this exception is not solved
    if (strpos($media_filename_for_thumb_check, 'thumb_') === 0) {
        // we make the thumbname origin filename
        $original_media_filename = substr($media_filename_for_thumb_check, 6, -4);
        $original_media_filename = $matching_prefix . $original_media_filename;
    } else {
        $original_media_filename = $media_filename;
    }
    // echo 'O<br>';
    // echo $original_media_filename;
    // echo '<br>';

    $qry = "SELECT * FROM humo_events
    WHERE event_tree_id='" . $tree_id . "' AND (event_connect_kind='person' OR event_connect_kind='family') AND event_connect_id NOT LIKE '' AND event_event='" . $original_media_filename . "'";
    $media_qry = $dbh->query($qry);
    $media_qryDb = $media_qry->fetch(PDO::FETCH_OBJ);


    //default var declaration
    $file_allowed = false;

    if ($media_qryDb && $media_qryDb->event_connect_kind === 'person') {
        // echo 'person';
        @$personmnDb = $db_functions->get_person($media_qryDb->event_connect_id);
        $man_cls = new person_cls($personmnDb);
        if (is_object($man_cls->personDb) && !$man_cls->privacy) {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    } elseif ($media_qryDb && $media_qryDb->event_connect_kind === 'family') {
        // echo 'family';
        $qry2 = "SELECT * FROM humo_families WHERE fam_gedcomnumber='" . $media_qryDb->event_connect_id . "'";
        $family_qry = $dbh->query($qry2);
        $family_qryDb2 = $family_qry->fetch(PDO::FETCH_OBJ);

        @$personmnDb2 = $db_functions->get_person($family_qryDb2->fam_man);
        $man_cls2 = new person_cls($personmnDb2);

        @$personmnDb3 = $db_functions->get_person($family_qryDb2->fam_woman);
        $woman_cls = new person_cls($personmnDb3);

        // *** Only use this picture if both man and woman have disabled privacy options ***
        if ($man_cls2->privacy == '' && $woman_cls->privacy == '') {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    } elseif (isset($_SESSION['group_id_admin'])) {
        $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $_SESSION['group_id_admin'] . "'");
        @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
        if ($groepDb->group_admin === 'j') {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    }
    // var_dump($file_allowed);

    //in this if we make exception for favicon.ico, logo.png and logo.jpg which must be served always
    if ($file_allowed || ($media_filename == 'logo.png' || $media_filename == 'logo.jpg' || $media_filename == 'favicon.ico')) {
        // echo 'file allowed';
        // not used as we get this in query string 'media_dir'
        // $tree_pict_path = $dataDb->tree_pict_path;
        // if (substr($tree_pict_path, 0, 1) === '|') {
        //     $tree_pict_path = 'media/';
        // }

        // $picture = $media_dir . '/' . basename($_GET['picture']);
        // $media_dir = realpath($media_dir);
        // echo $media_dir . $media_filename;
        if (file_exists($media_dir . $media_filename)) {
            //we check what content type is file to put header
            $content_type_header = mime_content_type($media_dir . $media_filename);
            header('Content-Type: ' . $content_type_header);
            header('Content-Disposition: inline; filename="' . $media_filename . '"');
            header('Cache-Control: private, max-age=3600');
            header('Pragma:');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (3600))); // 3600s cache
            readfile($media_dir . $media_filename);
        } else {
            echo 'file not exists';
        }
        exit();
    } else {
        echo 'You are non authorized to get this file';
        exit();
    }
}

// *** Number of photo's in photobook ***
if (isset($_POST['show_pictures']) && is_numeric($_POST['show_pictures'])) {
    $show_pictures = $_POST['show_pictures'];
    setcookie("humogenphotos", $show_pictures, time() + 60 * 60 * 24 * 365);
}
if (isset($_GET['show_pictures']) && is_numeric($_GET['show_pictures'])) {
    $show_pictures = $_GET['show_pictures'];
    setcookie("humogenphotos", $show_pictures, time() + 60 * 60 * 24 * 365);
}

// *** Use session if session is available ***
if (isset($_SESSION["save_favorites"]) && $_SESSION["save_favorites"]) {
    $favorites_array = $_SESSION["save_favorites"];
} elseif (isset($_COOKIE['humo_favorite'])) {
    // *** Get favourites from cookie (only if session is empty) ***
    foreach ($_COOKIE['humo_favorite'] as $name => $value) {
        $favorites_array[] = $value;
    }
    // *** Save cookie array in session ***
    $_SESSION["save_favorites"] = $favorites_array;
}

// *** Add new favorite to list of favourites ***
// *** Remark: cookies must be set in header, otherwise they don't work ***
if (isset($_POST['favorite'])) {
    // *** Add favourite to session ***
    $favorites_array[] = $_POST['favorite'];
    $_SESSION["save_favorites"] = $favorites_array;

    // *** Add favourite to cookie ***
    $favorite_array2 = explode("|", $_POST['favorite']);
    // *** Combine tree id and family number as unique array id: 1F4 ***
    $i = $favorite_array2['0'] . $favorite_array2['1'];
    setcookie("humo_favorite[$i]", $_POST['favorite'], time() + 60 * 60 * 24 * 365);
}

// *** Remove favourite from favorite list ***
if (isset($_POST['favorite_remove'])) {
    // *** Remove favourite from session ***
    $process_favorites = false;
    if (isset($_SESSION["save_favorites"])) {
        unset($favorites_array);
        foreach ($_SESSION['save_favorites'] as $key => $value) {
            if ($value != $_POST['favorite_remove']) {
                $favorites_array[] = $value;
                $process_favorites = true;
            }
        }
        //Doesn't work properly: if (isset($favorites_array)){}
        if ($process_favorites) {
            $_SESSION["save_favorites"] = $favorites_array;
        } else {
            // *** Just removed last favorite, so remove session ***
            unset($_SESSION["save_favorites"]);
        }
    }

    // *** Remove cookie ***
    if (isset($_COOKIE['humo_favorite'])) {
        foreach ($_COOKIE['humo_favorite'] as $name => $value) {
            if ($value == $_POST['favorite_remove']) {
                setcookie("humo_favorite[$name]", "", time() - 3600);
            }
        }
    }
}

// TODO this is probably disabled allready.
// *** Cookie for "show descendant chart below fanchart"
// Set default ("0" is OFF, "1" is ON):
/*
$showdesc = "0";
if (isset($_POST['show_desc'])) {
    if ($_POST['show_desc'] == "1") {
        $showdesc = "1";
        $_SESSION['save_show_desc'] = "1";
        setcookie("humogen_showdesc", "1", time() + 60 * 60 * 24 * 365); // set cookie to "1"
    } else {
        $showdesc = "0";
        $_SESSION['save_show_desc'] = "0";
        setcookie("humogen_showdesc", "0", time() + 60 * 60 * 24 * 365); // set cookie to "0"
        // we don't delete the cookie but set it to "O" for the sake of those who want to make the default "ON" ($showdesc="1")
    }
}
*/

// ----------- RTL by Dr Maleki ------------------
$html_text = '';
if ($language["dir"] == "rtl") {   // right to left language
    $html_text = ' dir="rtl"';
}
// TODO check this code
if (isset($screen_mode) && ($screen_mode == "STAR" || $screen_mode == "STARSIZE")) {
    $html_text = '';
}

function getActiveTopMenu(string $page = 'home')
{
    $menu_top = 'home';
    $menu_top_items = [
        'home' => ['index'],
        'information' => ['cms_pages'],
        'tree_menu' => [
            'tree_index',
            'persons',
            'family',
            'family_rtf',
            'descendant',
            'ancestor_report',
            'ancestor_chart',
            'ancestor_sheet',
            'list',
            'list_names',
            'source',
            'sources',
            'places',
            'list_places_families',
            'photoalbum',
            'addresses',
            'address'
        ],
        'tool_menu' => ['anniversary', 'statistics', 'relations', 'maps', 'mailform', 'latest_changes'],
        'user_menu' => ['login', 'register'],
        'setting_menu' => ['user_settings']
    ];

    foreach ($menu_top_items as $menu_top_item => $sub_menu_items) {
        if (in_array($page, $sub_menu_items)) {
            $menu_top = $menu_top_item;
            break;
        }
    }

    return $menu_top;
}
$menu_top = getActiveTopMenu($page);
//if ($menu_top === 'tool_menu') echo 'active';
?>

<!DOCTYPE html>

<html lang="<?= $selected_language; ?>" <?= $html_text; ?>>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <!-- Bootstrap: rescale standard HuMo-genealogy pages for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $index['main_admin']; ?></title>

    <?php if ($humo_option["searchengine"] == "j") { ?>
        <?= $humo_option["robots_option"]; ?>
    <?php } ?>

    <?php if ($base_href) { ?>
        <base href="<?= $base_href; ?>">
    <?php } ?>

    <!-- Bootstrap added in dec. 2023 -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Default CSS settings -->
    <link href="css/gedcom.css" rel="stylesheet" type="text/css">

    <!-- TODO this is only needed for outline report -->
    <link href="css/outline_report.css" rel="stylesheet" type="text/css">

    <!-- TODO check print version -->
    <link href="css/print.css" rel="stylesheet" type="text/css" media="print">

    <?php
    // *** Use your own favicon.ico in media folder ***
    if (file_exists('media/favicon.ico')) {
        include_once(__DIR__ . '/../include/give_media_path.php');
        echo '<link href="' . give_media_path("media/", "favicon.ico") . '" rel="shortcut icon" type="image/x-icon">';
        // echo '<link rel="shortcut icon" href="media/favicon.ico" type="image/x-icon">';
    } else {
        echo '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">';
    }

    /*
    // *****************************************************************
    // Use these lines to show a background picture for EACH FAMILY TREE
    // *****************************************************************
    print '<style type="text/css">';
    $picture= "pictures/".$_SESSION['tree_prefix'].".jpg";
    print " body { background-image: url($picture);}";
    print "</style>";
    */

    // if (lightbox activated or) descendant chart or hourglass chart or google maps is used --> load jquery
    // *** Needed for zoomslider ***
    if (
        strpos($_SERVER['REQUEST_URI'], "maps") !== false || strpos($_SERVER['REQUEST_URI'], "descendant") !== false || strpos($_SERVER['REQUEST_URI'], "HOUR") !== false
    ) {
        echo '<script src="assets/jquery/jquery.min.js"></script> ';
        echo '<link rel="stylesheet" href="assets/jqueryui/jquery-ui.min.css"> ';
        echo '<script src="assets/jqueryui/jquery-ui.min.js"></script>';
    }

    // *** Cookie for theme selection ***
    echo '<script>
        function getCookie(NameOfCookie) {
            if (document.cookie.length > 0) {
                begin = document.cookie.indexOf(NameOfCookie + "=");
                if (begin != -1) {
                    begin += NameOfCookie.length + 1;
                    end = document.cookie.indexOf(";", begin);
                    if (end == -1) {
                        end = document.cookie.length;
                    }
                    return unescape(document.cookie.substring(begin, end));
                }
            }
            return null;
        }
        </script>';

    // *** Style sheet select ***
    include_once(__DIR__ . "/../styles/sss1.php");

    // *** Pop-up menu ***
    // TODO No longer needed for main menu. But still in use for popups at this moment.
    echo '<script src="include/popup_menu/popup_menu.js"></script>';
    echo '<link rel="stylesheet" type="text/css" href="include/popup_menu/popup_menu.css">';

    // TODO replace with bootstrap carousel.
    // *** Always load script, because of "Random photo" at homepage (also used in other pages showing pictures) ***
    // *** Photo lightbox effect using GLightbox ***
    echo '<link rel="stylesheet" href="include/glightbox/css/glightbox.css">';
    echo '<script src="include/glightbox/js/glightbox.min.js"></script>';
    // TODO: could be done here using "defer". But bootstrap will be tried first.
    // *** Remark: there is also a script in footer script, otherwise GLightbox doesn't work ***

    // *** CSS changes for mobile devices ***
    echo '<link rel="stylesheet" media="(max-width: 640px)" href="css/gedcom_mobile.css">';

    // *** Extra items in header added by admin ***
    if ($humo_option["text_header"]) {
        echo "\n" . $humo_option["text_header"];
    }
    ?>
</head>

<body>
    <?php
    // Show menu
    $menu = true;
    // *** Hide menu in descendant chart shown in iframe in fanchart ***
    if (isset($_GET['menu']) && $_GET['menu'] == "1") {
        $menu = false;
    }
    if ($menu) {
        // *** LTR or RTL ***
        $rtlmark = 'ltr';
        if ($language["dir"] == "rtl") {
            $rtlmark = 'rtl';
        }

        // *** Show logo or name of website ***
        $logo = $humo_option["database_name"];
        if (is_file('media/logo.png')) {
            include_once(__DIR__ . '/../include/give_media_path.php');
            $logo = '<img src="' . give_media_path('media/', 'logo.png') . '">';
        } elseif (is_file('media/logo.jpg')) {
            include_once(__DIR__ . '/../include/give_media_path.php');
            $logo = '<img src="' . give_media_path('media/', 'logo.png') . '">';
        }
    ?>

        <div id="top_menu" class="d-print-none">
            <div id="top" class="pt-3 pe-2" style="direction:<?= $rtlmark; ?>">

                <div class="row g-2">
                    <div class="col-md-4">
                        <span id="top_website_name">
                            <!-- *** Show logo or name of website *** -->
                            &nbsp;<a href="<?= $humo_option["homepage"]; ?>"><?= $logo; ?></a>
                        </span>
                        &nbsp;&nbsp;
                    </div>

                    <?php
                    // *** Select family tree ***
                    if (!$bot_visit) {
                        $sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                        $tree_search_result2 = $dbh->query($sql);
                        $num_rows = $tree_search_result2->rowCount();
                        $count = 0;
                        // *** Changed 1 into 0. So pull-down menu is always shown ***
                        //if ($num_rows > 1) {
                        if ($num_rows > 0) {
                            $link = $link_cls->get_link($uri_path, 'tree_index');
                    ?>
                            <div class="col-md-3">
                                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                                    <!-- <?= __('Family tree') . ': '; ?> -->
                                    <select size="1" name="tree_id" onChange="this.form.submit();" class="form-select form-select-sm">
                                        <option value=""><?= __('Select a family tree:'); ?></option>
                                        <?php
                                        while ($tree_searchDb = $tree_search_result2->fetch(PDO::FETCH_OBJ)) {
                                            // *** Check if family tree is shown or hidden for user group ***
                                            $hide_tree_array2 = explode(";", $user['group_hide_trees']);
                                            $hide_tree2 = false;
                                            if (in_array($tree_searchDb->tree_id, $hide_tree_array2)) {
                                                $hide_tree2 = true;
                                            }
                                            if ($hide_tree2 == false) {
                                                $selected = '';
                                                if (isset($_SESSION['tree_prefix'])) {
                                                    if ($tree_searchDb->tree_prefix == $_SESSION['tree_prefix']) {
                                                        $selected = ' selected';
                                                    }
                                                } elseif ($count == 0) {
                                                    $_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
                                                    $selected = ' selected';
                                                }
                                                $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                                                echo '<option value="' . $tree_searchDb->tree_id . '"' . $selected . '>' . @$treetext['name'] . '</option>';
                                                $count++;
                                            }
                                        }
                                        ?>
                                    </select>
                                </form>
                            </div>
                    <?php
                        }
                    }
                    ?>

                    <?php
                    // *** This code is used to restore $dataDb reading. Used for picture etc. ***
                    if (is_string($_SESSION['tree_prefix']) && $_SESSION['tree_prefix']) {
                        $dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);
                    }

                    // *** Show quicksearch field ***
                    if (!$bot_visit) {
                        $menu_path = $link_cls->get_link($uri_path, 'list', $tree_id);

                        $quicksearch = '';
                        if (isset($_POST['quicksearch'])) {
                            $quicksearch = safe_text_show($_POST['quicksearch']);
                            $_SESSION["save_quicksearch"] = $quicksearch;
                        }
                        if (isset($_SESSION["save_quicksearch"])) {
                            $quicksearch = $_SESSION["save_quicksearch"];
                        }
                        if ($humo_option['min_search_chars'] == 1) {
                            $pattern = "";
                            $min_chars = " 1 ";
                        } else {
                            $pattern = 'pattern=".{' . $humo_option['min_search_chars'] . ',}"';
                            $min_chars = " " . $humo_option['min_search_chars'] . " ";
                        }
                    ?>

                        <div class="col-md-2">
                            <form method="post" action="<?= $menu_path; ?>">
                                <input type="hidden" name="index_list" value="quicksearch">
                                <input type="hidden" name="search_database" value="tree_selected">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control form-control-sm" name="quicksearch" placeholder="<?= __('Name'); ?>" value="<?= $quicksearch; ?>" size="10" <?= $pattern; ?> title="<?= __('Minimum:') . $min_chars . __('characters'); ?>">
                                    <button type="submit" class="btn btn-success btn-sm"><?= __('Search'); ?></button>
                                </div>
                            </form>
                        </div>

                        <!-- hidden in mobile version -->
                        <div class="col-md-1 d-none d-md-block">
                            <?php
                            // *** Link for extended search form ***
                            $menu_path = $link_cls->get_link($uri_path, 'list', $tree_id, true);
                            $menu_path .= 'adv_search=1&amp;index_list=search';
                            ?>

                            <!--
                                <a href="<?= $menu_path; ?>"><img src="images/advanced-search.jpg" width="17" alt="<?= __('Advanced search'); ?>"></a>
                                -->

                            <form method="post" action="<?= $menu_path; ?>">
                                <button type="submit" class="btn btn-light btn-sm"><img src="images/advanced-search.jpg" width="17" alt="<?= __('Advanced search'); ?>"></button>
                            </form>
                        </div>
                    <?php
                    }

                    // *** Favourite list for family pages ***
                    if (!$bot_visit) {
                        include_once(__DIR__ . "/../include/person_cls.php");
                        // *** Show favorites in selection list ***
                        $link = $link_cls->get_link($uri_path, 'family', $tree_id);
                    ?>
                        <div class="col-md-2">

                            <form method="POST" action="<?= $link; ?>" style="display : inline;">
                                <!-- <img src="images/favorite_blue.png" alt="<?= __('Favourites'); ?>"> -->
                                <select size=1 name="humo_favorite_id" onChange="this.form.submit();" class="form-select form-select-sm">
                                    <option value=""><?= __('Favourites list:'); ?></option>
                                    <?php
                                    if (isset($_SESSION["save_favorites"])) {
                                        sort($_SESSION['save_favorites']);
                                        foreach ($_SESSION['save_favorites'] as $key => $value) {
                                            if (is_string($value) and $value) {
                                                $favorite_array2 = explode("|", $value);

                                                // *** July 2023: New favorite system: 0=tree/ 1=family/ 2=person GEDCOM number ***
                                                // *** Show only persons in selected family tree ***
                                                if ($tree_id == $favorite_array2['0']) {
                                                    // *** Check if family tree is still the same family tree ***
                                                    // *** Proces man using a class ***
                                                    $test_favorite = $db_functions->get_person($favorite_array2['2']);
                                                    if ($test_favorite) {
                                                        //$name_cls = new person_cls($favorite_array2['3']);
                                                        //$name_cls = new person_cls($favorite_array2['2']);
                                                        $name_cls = new person_cls($test_favorite);
                                                        $name = $name_cls->person_name($test_favorite);
                                                        echo '<option value="' . $favorite_array2['1'] . '|' . $favorite_array2['2'] . '">' . $name['name'] . ' [' . $favorite_array2['2'] . ']</option>';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                </div>

            <?php } ?>

            </div> <!-- End of Top -->

            <?php
            $menu_path_home = $link_cls->get_link($uri_path, 'index', $tree_id);
            // *** Mobile menu ***
            if ($user['group_menu_login'] == 'j') {
                $menu_path_login = $link_cls->get_link($uri_path, 'login');
            }
            // *** Log off ***
            $menu_path_logoff = $link_cls->get_link($uri_path, 'logoff');
            $menu_path_help = $link_cls->get_link($uri_path, 'help');
            $menu_path_register = $link_cls->get_link($uri_path, 'register');
            $menu_path_cms = $link_cls->get_link($uri_path, 'cms_pages');
            $menu_path_cookies = $link_cls->get_link($uri_path, 'cookies');
            $menu_path_persons = $link_cls->get_link($uri_path, 'list', $tree_id, true);
            $menu_path_persons .= 'reset=1';
            if ($humo_option["url_rewrite"] == "j") {
                $menu_path_names = 'list_names/' . $tree_id . '/';
            } else {
                $menu_path_names = 'index.php?page=list_names&amp;tree_id=' . $tree_id;
            }
            // Doesn't work yet. An extra / is added at end of link.
            //$menu_path_names = $link_cls->get_link($uri_path, 'list_names',$tree_id);

            $menu_path_user_settings = $link_cls->get_link($uri_path, 'user_settings');
            $menu_path_admin = 'admin/index.php';
            $menu_path_anniversary = $link_cls->get_link($uri_path, 'anniversary');
            $menu_path_statistics = $link_cls->get_link($uri_path, 'statistics');
            $menu_path_calculator = $link_cls->get_link($uri_path, 'relations');
            $menu_path_map = $link_cls->get_link($uri_path, 'maps');
            $menu_path_contact = $link_cls->get_link($uri_path, 'mailform');
            // *** Latest changes ***
            $menu_path_latest_changes = $link_cls->get_link($uri_path, 'latest_changes', $tree_id);
            $menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index', $tree_id);
            $menu_path_places_persons = $link_cls->get_link($uri_path, 'list', $tree_id, true);
            $menu_path_places_persons .= 'index_list=places&amp;reset=1';
            $menu_path_list_places_families = $link_cls->get_link($uri_path, 'list_places_families', $tree_id, true);
            $menu_path_list_places_families .= 'reset=1';
            $menu_path_photoalbum = $link_cls->get_link($uri_path, 'photoalbum', $tree_id);
            $menu_path_sources = $link_cls->get_link($uri_path, 'sources', $tree_id);
            $menu_path_addresses = $link_cls->get_link($uri_path, 'addresses', $tree_id);
            ?>

        </div> <!-- End of top_menu -->


        <!-- Bootstrap menu using hoover effect -->
        <!-- Example from: https://bootstrap-menu.com/detail-basic-hover.html -->
        <!-- <nav class="mt-5 navbar navbar-expand-lg bg-light border-bottom border-success"> -->
        <!-- <nav class="mt-5 navbar navbar-expand-lg border-bottom border-success genealogy_menu" style="margin: 0 !important;"> -->
        <!-- <nav class="mt-5 navbar navbar-expand-lg border-bottom border-dark-subtle genealogy_menu"> -->
        <!-- <nav class="navbar navbar-expand-lg border-bottom border-dark-subtle genealogy_menu"> -->
        <nav class="navbar navbar-expand-md border-bottom border-dark-subtle genealogy_menu d-print-none">
            <!-- <div class="container-fluid"> -->
            <?php // <a class="navbar-brand" href="#">Brand</a> ;
            ?>
            <button class="navbar-toggler genealogy_toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="main_nav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item <?php if ($menu_top === 'home') echo 'genealogy_active'; ?>">
                        <a class="nav-link <?php if ($menu_top === 'home') echo 'active'; ?>" href="<?= $menu_path_home; ?>"><?= __('Home'); ?></a>
                    </li>

                    <?php
                    // TODO improve code
                    // *** Menu genealogy (for CMS pages) ***
                    if ($user['group_menu_cms'] == 'y') {
                        $cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
                        if ($cms_qry->rowCount() > 0) {
                    ?>
                            <li class="nav-item <?php if ($menu_top == 'information') echo 'genealogy_active'; ?>">
                                <a class="nav-link <?php if ($menu_top == 'information') echo 'active'; ?>" href="<?= $menu_path_cms; ?>"><?= __('Information'); ?></a>
                            </li>
                    <?php
                        }
                    }
                    ?>

                    <?php if (!$bot_visit) { ?>
                        <li class="nav-item dropdown active <?php if ($menu_top == 'tree_menu') echo 'genealogy_active'; ?>">
                            <?php // TODO add active if dropdown item is selected ;
                            ?>

                            <a class="nav-link dropdown-toggle <?php if ($menu_top == 'tree_menu') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>" data-bs-toggle="dropdown">
                                <?= __('Family tree'); ?>
                            </a>

                            <ul class="dropdown-menu genealogy_menu">
                                <li><a class="dropdown-item <?php if ($page == 'tree_index') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>"><?= __('Family tree index'); ?></a></li>

                                <!-- Persons -->
                                <?php if ($user['group_menu_persons'] == "j") { ?>
                                    <li><a class="dropdown-item <?php if ($page == 'persons' || $page == 'family' || $page == 'family_rtf' || $page == 'descendant' || $page == 'ancestor' || $page == 'ancestor_chart' || $page == 'ancestor_sheet' || $page == 'list') echo 'active'; ?>" href="<?= $menu_path_persons; ?>"><?= __('Persons'); ?></a></li>
                                <?php } ?>

                                <!-- Names -->
                                <?php if ($user['group_menu_names'] == "j") {; ?>
                                    <li><a class="dropdown-item <?php if ($page == 'list_names') echo 'active'; ?>" href="<?= $menu_path_names; ?>"><?= __('Names'); ?></a></li>
                                <?php }; ?>

                                <!-- Places -->
                                <?php if ($user['group_menu_places'] == "j") {; ?>
                                    <li><a class="dropdown-item" href="<?= $menu_path_places_persons; ?>"><?= __('Places (by persons)'); ?></a></li>
                                    <li><a class="dropdown-item" href="<?= $menu_path_list_places_families; ?>"><?= __('Places (by families)'); ?></a></li>
                                <?php } ?>

                                <?php if ($user['group_photobook'] == 'j') {; ?>
                                    <li><a class="dropdown-item <?php if ($page == 'photoalbum') echo 'active'; ?>" href="<?= $menu_path_photoalbum; ?>"><?= __('Photobook'); ?></a></li>
                                <?php } ?>

                                <?php
                                if ($user['group_sources'] == 'j' && $tree_prefix_quoted != '' && $tree_prefix_quoted != 'EMPTY') {
                                    // *** Check if there are sources in the database ***
                                    //$source_qry=$dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'AND source_shared='1'");
                                    $source_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
                                    @$sourceDb = $source_qry->rowCount();
                                    if ($sourceDb > 0) {
                                ?>
                                        <li><a class="dropdown-item <?php if ($page == 'sources') echo 'active'; ?>" href="<?= $menu_path_sources; ?>"><?= __('Sources'); ?></a></li>
                                <?php
                                    }
                                }
                                ?>

                                <?php
                                if ($user['group_addresses'] == 'j' && $tree_prefix_quoted != '' && $tree_prefix_quoted != 'EMPTY') {
                                    // *** Check for addresses in the database ***
                                    $address_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'");
                                    @$addressDb = $address_qry->rowCount();
                                    if ($addressDb > 0) {
                                ?>
                                        <li><a class="dropdown-item <?php if ($page == 'addresses') echo 'active'; ?>" href="<?= $menu_path_addresses; ?>"><?= __('Addresses'); ?></a></li>
                                <?php
                                    }
                                }
                                ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <!-- Menu: Tools menu -->
                    <?php
                    if ($bot_visit && $humo_option["searchengine_cms_only"] == 'y') {
                        //
                    } else {
                        // make sure at least one of the submenus is activated, otherwise don't show TOOLS menu
                        //	AND $dbh->query("SELECT * FROM humo_settings WHERE setting_variable ='geo_trees'
                        //		AND setting_value LIKE '%@".$tree_id.";%' ")->rowCount() > 0)
                        if (
                            $user["group_birthday_list"] == 'j' || $user["group_showstatistics"] == 'j' || $user["group_relcalc"] == 'j' || $user["group_googlemaps"] == 'j' || $user["group_contact"] == 'j' && $dataDb->tree_owner && $dataDb->tree_email || $user["group_latestchanges"] == 'j'
                        ) {
                    ?>

                            <li class="nav-item dropdown <?php if ($menu_top == 'tool_menu') echo 'genealogy_active'; ?>">
                                <a class="nav-link dropdown-toggle <?php if ($menu_top == 'tool_menu') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>" data-bs-toggle="dropdown">
                                    <?= __('Tools'); ?>
                                </a>

                                <ul class="dropdown-menu genealogy_menu">
                                    <?php if ($user["group_birthday_list"] == 'j') {; ?>
                                        <li><a class="dropdown-item <?php if ($page == 'anniversary') echo 'active'; ?>" href="<?= $menu_path_anniversary; ?>"><?= __('Anniversary list'); ?></a></li>
                                    <?php } ?>

                                    <?php if ($user["group_showstatistics"] == 'j') {; ?>
                                        <li><a class="dropdown-item <?php if ($page == 'statistics') echo 'active'; ?>" href="<?= $menu_path_statistics; ?>"><?= __('Statistics'); ?></a></li>
                                    <?php } ?>

                                    <?php if ($user["group_relcalc"] == 'j') {; ?>
                                        <li><a class="dropdown-item <?php if ($page == 'relations') echo 'active'; ?>" href="<?= $menu_path_calculator; ?>"><?= __('Relationship calculator'); ?></a></li>
                                    <?php } ?>

                                    <?php if ($user["group_googlemaps"] == 'j') {; ?>
                                        <?php if (!$bot_visit) { ?>
                                            <li><a class="dropdown-item <?php if ($page == 'maps') echo 'active'; ?>" href="<?= $menu_path_map; ?>"><?= __('World map'); ?></a></li>
                                        <?php } ?>
                                    <?php } ?>

                                    <!-- Show link to contact form -->
                                    <?php if ($user["group_contact"] == 'j') {; ?>
                                        <?php if (@$dataDb->tree_owner) { ?>
                                            <li><a class="dropdown-item <?php if ($page == 'mailform') echo 'active'; ?>" href="<?= $menu_path_contact; ?>"><?= __('Contact'); ?></a></li>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ($user["group_latestchanges"] == 'j') {; ?>
                                        <li><a class="dropdown-item <?php if ($page == 'latest_changes') echo 'active'; ?>" href="<?= $menu_path_latest_changes; ?>"><?= __('Latest changes'); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                    <?php } ?>

                    <!-- Only show login/ register if user isn't logged in -->
                    <?php if ($user['group_menu_login'] == 'j' and !$user["user_name"]) { ?>
                        <li class="nav-item dropdown <?php if ($menu_top == 'user_menu') echo 'genealogy_active'; ?>">
                            <a class="nav-link dropdown-toggle <?php if ($menu_top == 'user_menu') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>" data-bs-toggle="dropdown">
                                <?= __('Login'); ?>
                            </a>
                            <ul class="dropdown-menu genealogy_menu">
                                <li><a class="dropdown-item <?php if ($page == 'login') echo 'active'; ?>" href="<?= $menu_path_login; ?>"><?= __('Login'); ?></a></li>

                                <!-- Link to registration form -->
                                <?php if (!$user["user_name"] and $humo_option["visitor_registration"] == 'y') { ?>
                                    <li><a class="dropdown-item <?php if ($page == 'register') echo 'active'; ?>" href="<?= $menu_path_register; ?>"><?= __('Register'); ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <!-- Menu: Control menu -->
                    <?php if (!$bot_visit) { ?>
                        <li class="nav-item dropdown <?php if ($menu_top == 'setting_menu') echo 'genealogy_active'; ?>">
                            <a class="nav-link dropdown-toggle <?php if ($menu_top == 'setting_menu') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>" data-bs-toggle="dropdown">
                                <?= __('Control'); ?>
                            </a>
                            <ul class="dropdown-menu genealogy_menu">
                                <li><a class="dropdown-item <?php if ($page == 'settings') echo 'active'; ?>" href="<?= $menu_path_user_settings; ?>"><?= __('User settings'); ?></a></li>

                                <!-- Admin pages -->
                                <?php if ($user['group_edit_trees'] || $user['group_admin'] == 'j') {; ?>
                                    <li><a class="dropdown-item" href="<?= $menu_path_admin; ?>" target="_blank"><?= __('Admin'); ?></a></li>
                                <?php } ?>

                                <!-- Login - Logoff -->
                                <?php if ($user['group_menu_login'] == 'j' && $user["user_name"]) {; ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= $menu_path_logoff; ?>"><?= __('Logoff'); ?>
                                            <span style="color:#0101DF; font-weight:bold;">[<?= ucfirst($_SESSION["user_name"]); ?>]</span>
                                        </a>
                                    </li>
                                <?php } ?>

                            </ul>
                        </li>
                    <?php } ?>

                    <!-- Select language using country flags -->
                    <?php if (!$bot_visit) { ?>
                        <li class="nav-item dropdown">
                            <?php include_once(__DIR__ . "/partial/select_language.php"); ?>
                            <?php $language_path = $link_cls->get_link($uri_path, 'language', '', true); ?>
                            <?= show_country_flags($selected_language, '', 'language', $language_path); ?>
                        </li>
                    <?php } ?>
                </ul>

                <?php
                /*
                <!-- TEST for theme selection -->
                <?php
                ?>
                <li class="nav-item dropdown" data-bs-theme="light">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="theme-menu" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Toggle theme">
                        Toggle theme
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                                Light
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="true">
                                Dark
                            </button>
                        </li>
                    </ul>
                </li>
                <script>
                    // Toggle light and dark themes
                    function toggleThemeMenu() {
                        let themeMenu = document.querySelector('#theme-menu');
                        if (!themeMenu) return;
                        document.querySelectorAll('[data-bs-theme-value]').forEach(value => {
                            value.addEventListener('click', () => {
                                const theme = value.getAttribute('data-bs-theme-value');
                                document.documentElement.setAttribute('data-bs-theme', theme);
                            });
                        });
                    }
                    toggleThemeMenu();
                </script>
                */
                ?>
            </div>

            <!-- </div> -->
        </nav>

        <?php
        // *** Override margin if slideshow is used ***
        if ($page == 'index' && isset($humo_option["slideshow_show"]) && $humo_option["slideshow_show"] == 'y') {
            echo '<style>
                #rtlcontent {
                    padding-left:0px;
                    padding-right:0px;
                }
                #content {
                    padding-left:0px;
                    padding-right:0px;
                }
                </style>';
        }
        ?>
        <div id="<?= $language["dir"] == "rtl" ? 'rtlcontent' : 'content'; ?>">
            <?php
        }

        // *** Include content ***
        if ($page == 'index') {
            // ***********************************************************************************************
            // ** Main index class ***
            // ***********************************************************************************************

            // *** Replace the main index by an own CMS page ***
            $text = '';
            if (isset($humo_option["main_page_cms_id_" . $selected_language]) && $humo_option["main_page_cms_id_" . $selected_language]) {
                // *** Show CMS page ***
                if (is_numeric($humo_option["main_page_cms_id_" . $selected_language])) {
                    $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='" . $humo_option["main_page_cms_id_" . $selected_language] . "' AND page_status!=''");
                    $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
                    $text = $cms_pagesDb->page_text;
                }
            } elseif (isset($humo_option["main_page_cms_id"]) && $humo_option["main_page_cms_id"]) {
                // *** Show CMS page ***
                if (is_numeric($humo_option["main_page_cms_id"])) {
                    $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='" . $humo_option["main_page_cms_id"] . "' AND page_status!=''");
                    $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
                    $text = $cms_pagesDb->page_text;
                }
            }

            if ($text) {
            ?>
                <!-- Show CMS page -->
                <div class="row m-lg-1 py-3 genealogy_row">
                    <div class="col-sm-12">
                        <?= $text; ?>
                    </div>
                </div>
        <?php
            } else {
                // *** Show default HuMo-genealogy homepage ***
                //$mainindex->show_tree_index();
                include __DIR__ . '/tree_index.php';
            }
        } else {
            require __DIR__ . '/' . $page . '.php';
        }
        ?>

        <br>
        <script src="include/glightbox/glightbox_footer.js"></script>

        <!-- July 2024: Bootstrap popover -->
        <script>
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
        </script>

        <!-- TODO improve code for tab menu in ascendants and descendants -->
        <!-- End of tab menu, if used -->
        <?php if (
            isset($_GET['descendant_report']) && $_GET['descendant_report'] == '1' || $page == 'outline_report' || $page == 'descendant_chart' || $page == 'ancestor_report' || $page == 'ancestor_sheet' || $page == 'ancestor_chart' || $page == 'fanchart'
        ) { ?>
        </div>
    <?php } ?>

    </div> <!-- End of div: Content -->

    <?php if ($menu) { ?>
        <footer class="d-print-none">
            <?php if ($humo_option["text_footer"]) {; ?>
                <?= $humo_option["text_footer"]; ?>
            <?php } ?>

            <!-- Show HuMo-genealogy footer -->
            <?php if (isset($mainindex)) { ?>
                <br>
                <div class="humo_version">
                    <!-- Show owner of family tree -->
                    <?= $mainindex->owner(); ?>

                    <!-- Show HuMo-genealogy link -->
                    <?php printf(__('This website is created using %s, a freeware genealogical  program'), '<a href="https://humo-gen.com">HuMo-genealogy</a>'); ?>.<br>

                    <!-- Show European cookie information -->
                    <?php
                    $url = $humo_option["url_rewrite"] == "j" ? $uri_path . 'cookies' : 'index.php?page=cookies';
                    if (!$bot_visit) {
                        printf(__('European law: %s cookie information'), '<a href="' . $url . '">HuMo-genealogy');
                        echo '</a>';
                    }
                    ?>
                </div>
            <?php } ?>

            <!--  Links in footer -->
            <div id="footer"><br>
                <a href="<?= $menu_path_help; ?>"><?= __('Help'); ?></a>

                <?php if (!$bot_visit) { ?>
                    | <a href="<?= $menu_path_cookies; ?>"><?php echo ucfirst(str_replace('%s ', '', __('%s cookie information'))); ?></a>
                <?php }; ?>
            </div>
        </footer>
    <?php } ?>

</body>

</html>