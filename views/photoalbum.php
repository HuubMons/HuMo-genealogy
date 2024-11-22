<?php
// *** Check user privileges ***
if ($user['group_pictures'] != 'j' || $user['group_photobook'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

// *** Get array of categories ***
$show_categories = false; // is set true by following code if necessary

$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
if ($temp->rowCount()) {
    // a humo_photocat table exists
    $temp2 = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none'");
    // the table contains more than the default category (otherwise display regular photoalbum)
    if ($temp2->rowCount() >= 1) {
        $qry = "SELECT photocat_id, photocat_prefix FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
        $result = $dbh->query($qry);
        $result_arr = $result->fetchAll();
        foreach ($result_arr as $row) {
            $category_array[] = $row['photocat_prefix'];
            $category_id_array[$row['photocat_prefix']] = $row['photocat_id'];
            $category_enabled[$row['photocat_prefix']] = false;
        }
    }
}

$chosen_tab = 'none';
if (isset($_SESSION['save_chosen_tab'])) {
    $chosen_tab = $_SESSION['save_chosen_tab'];
}
if (isset($_GET['select_category'])) {
    if ($_GET['select_category'] == 'none') {
        $chosen_tab = $_GET['select_category'];
        $_SESSION['save_chosen_tab'] = $chosen_tab;
    }

    // *** Get selected category ***
    if (isset($category_array) && $_GET['select_category'] != 'none' && in_array($_GET['select_category'], $category_array)) {
        $chosen_tab = $_GET['select_category'];
        $_SESSION['save_chosen_tab'] = $chosen_tab;
    }
}

// *** Create an array of all pics with person_id's. Also check for OBJECT (Family Tree Maker GEDCOM file) ***
$qry = "SELECT event_event, event_kind, event_connect_kind, event_connect_id, event_gedcomnr FROM humo_events
    WHERE (event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_kind='picture' AND event_connect_id NOT LIKE '')
    OR (event_tree_id='" . $tree_id . "' AND event_kind='object')
    ORDER BY event_event";
$picqry = $dbh->query($qry);
while ($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
    $picname = $picqryDb->event_event;

    // *** Show all media, or show only media from selected category ***
    $process_picture = false;
    if ($chosen_tab == 'none' && !isset($category_array)) {
        // *** No categories ***
        $process_picture = true;
    }
    // *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
    elseif ($chosen_tab != 'none' && strpos($picname, $chosen_tab) !== false) {
        // *** One category is selected ***
        $process_picture = true;
    }
    // *** Example of category dir: xy/picture.jpg ***
    elseif (strpos($picname, substr($chosen_tab, 0, 2) . '/') !== false) {
        // *** One category is selected ***
        $process_picture = true;
    }
    // *** If there are categories: filter category photo's from results ***
    elseif ($chosen_tab == 'none' && isset($category_array)) {
        // *** There are categories: filter these items in main page ***
        $process_picture = true;
        // *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
        foreach ($category_array as $test_category) {
            if (strpos($picname, $test_category) !== false) {
                $process_picture = false;
            }
            // *** Example of subdir category: xy/subdir2/akte_mons.gif ***
            if (strpos($picname, substr($test_category, 0, 2) . '/') !== false) {
                $process_picture = false;
            }
        }
    }

    // *** Use search field (search for person) to show pictures ***
    if ($photoalbum['search_media']) {
        $quicksearch = str_replace(" ", "%", $photoalbum['search_media']);
        $querie = "SELECT pers_firstname, pers_prefix, pers_lastname FROM humo_persons
            WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $picqryDb->event_connect_id . "'
            AND CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$quicksearch%'";
        $persoon = $dbh->query($querie);
        $personDb = $persoon->fetch(PDO::FETCH_OBJ);
        if (!$personDb) {
            $process_picture = false;
        }
    }

    // *** Check for privacy of connected persons ***
    if ($process_picture) {
        if ($picqryDb->event_connect_id) {
            // *** Check privacy filter ***
            // TODO: use the person_cls constructor (adding personDb).
            $person_cls = new person_cls;
            $personDb = $db_functions->get_person($picqryDb->event_connect_id);
            // TODO check $privacy. This line isn't needed anymore?
            $privacy = $person_cls->set_privacy($personDb);
            if ($privacy) {
                $process_picture = false;
            }
            //$name=$person_cls->person_name($personDb);
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
            //$picture_text.='<a href="'.$url.'">'.$name["standard_name"].'</a><br>';
            //$picture_text2.=$name["standard_name"];
        } else {
            // *** OBJECTS: Family Tree Maker GEDCOM file ***
            $connect_qry = $dbh->query("SELECT connect_connect_id FROM humo_connections
                WHERE connect_tree_id='" . $tree_id . "' AND connect_sub_kind='pers_object' AND connect_source_id='" . $picqryDb->event_gedcomnr . "'");
            while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                $person_cls = new person_cls;
                $personDb = $db_functions->get_person($connectDb->connect_connect_id);
                $privacy = $person_cls->set_privacy($personDb);
                if ($privacy) {
                    $process_picture = false;
                }
            }
        }
    }

    if ($process_picture) {
        if (!isset($media_files) || !in_array($picname, $media_files)) { // this pic does not appear in the array yet
            //$connected_persons[$picname]=$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354"

            // *** Skip PDF and RTF files ***
            $check_file = strtolower($picname);
            if (substr($check_file, -4) !== '.pdf' && substr($check_file, -4) !== '.rtf') {
                $media_files[] = $picname;
            }
        }

        //else { // pic already exists in array with other person_id. Append this one.
        //	$connected_persons[$picname] .= '@@'.$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354@@I653"
        //	//$media_files[]=$picname;
        //}
    }

    // *** Check if media belongs to category ***
    if (isset($category_array)) {
        foreach ($category_array as $test_category) {
            // *** Check if media belongs to category ***
            // *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
            if (strpos($picname, $test_category) !== false) {
                $show_categories = true; // *** There are categories ***
                $category_enabled[$test_category] = true; // *** This categorie will be shown ***
            }

            // *** Check if directory belongs to category, example: xy/picture.jpg ***
            if (strpos($picname, substr($test_category, 0, 2) . '/') !== false) {
                $show_categories = true; // *** There are categories ***
                $category_enabled[$test_category] = true; // *** This categorie will be shown ***
            }
        }
    } else {
        // *** There were categories, but they were disabled or removed ***
        $chosen_tab = 'none';
        $_SESSION['save_chosen_tab'] = $chosen_tab;
    }
}

// *** Show categories ***
if ($show_categories) {
    $category_enabled['none'] = true; // *** Always show main category ***
    $selected_category = $chosen_tab;
?>
    <ul class="nav nav-tabs mt-1">
        <?php
        foreach ($category_array as $category) {
            if ($category_enabled[$category] == true) {
                // check if name for this category exists for this language
                $qry2 = "SELECT * FROM humo_photocat WHERE photocat_prefix ='" . $category . "' AND photocat_language ='" . $selected_language . "'";
                $result2 = $dbh->query($qry2);
                if ($result2->rowCount() != 0) {
                    $catnameDb = $result2->fetch(PDO::FETCH_OBJ);
                    $menutab_name = $catnameDb->photocat_name;
                } else {
                    // check if default name exists for this category
                    $qry3 = "SELECT * FROM humo_photocat WHERE photocat_prefix ='" . $category . "' AND photocat_language ='default'";
                    $result3 = $dbh->query($qry3);
                    if ($result3->rowCount() != 0) {
                        $catnameDb = $result3->fetch(PDO::FETCH_OBJ);
                        $menutab_name = $catnameDb->photocat_name;
                    } else {
                        // no name at all (neither default nor specific)
                        $menutab_name = __('NO NAME');
                    }
                }
                $path = 'index.php?page=photoalbum?tree_id=' . $tree_id . '&amp;select_category=' . $category;
                if ($humo_option["url_rewrite"] == "j") {
                    $path = 'photoalbum/' . $tree_id . '?select_category=' . $category;
                }
        ?>
                <li class="nav-item me-1">
                    <a class="nav-link genealogy_nav-link <?php if ($selected_category == $category) echo 'active'; ?>" href="<?= $path; ?>"><?= $menutab_name; ?></a>
                </li>
        <?php
            }
        }
        ?>
    </ul>

<?php
}

// *** Show media/ photo's ***
if (isset($media_files)) {
    if ($show_categories == false) {  // *** There are no categories: show regular photo album ***
        show_media_files("none");  // show all
    } else {  // *** Show album with category tabs ***
        // *** Check is photo category tree is shown or hidden for user group ***
        $hide_photocat_array = explode(";", $user['group_hide_photocat']);
        $hide_photocat = false;
        if (in_array($category_id_array[$chosen_tab], $hide_photocat_array)) $hide_photocat = true;
        if ($hide_photocat == false)
            show_media_files($chosen_tab);  // show only pics that match this category
        else {
            echo '<div style="float: left; background-color:white; height:auto; width:98%;padding:5px;"><br>';
            echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***');
            echo '<br><br></div>';
        }
    }
} else {
    // *** Search is used, but there were no results, to prevent empty screen show search bar ***
    $media_files[] = '';
    show_media_files("none");  // show all
}

// *** $pref = category ***
function show_media_files($pref)
{
    global $dataDb, $dbh, $photoalbum, $uri_path, $tree_id, $db_functions,
        $cat_string, $show_categories, $chosen_tab, $media_files, $humo_option, $link_cls;

    $tree_pict_path = $dataDb->tree_pict_path;
    if (substr($tree_pict_path, 0, 1) === '|') {
        $tree_pict_path = 'media/';
    }
    $dir = $tree_pict_path;

    // *** Calculate pages ***
    // Ordering is now done in query...
    //@usort($media_files,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
    $nr_pictures = count($media_files);

    $albumpath = $link_cls->get_link($uri_path, 'photoalbum', $tree_id, true);

    $item = 0;
    if (isset($_GET['item'])) {
        $item = $_GET['item'];
    }
    $start = 0;
    if (isset($_GET["start"])) {
        $start = $_GET["start"];
    }

    // "<="
    $data["previous_link"] = '';
    $data["previous_status"] = '';
    if ($start > 1) {
        $start2 = $start - 20;
        $calculated = ($start - 2) * $photoalbum['show_pictures'];
        $data["previous_link"] = $albumpath . "start=" . $start2 . "&amp;item=" . $calculated;
    }
    if ($start <= 0) {
        $start = 1;
    }
    if ($start == '1') {
        $data["previous_status"] = 'disabled';
    }

    // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
    for ($i = $start; $i <= $start + 19; $i++) {
        $calculated = ($i - 1) * $photoalbum['show_pictures'];
        if ($calculated < $nr_pictures) {
            $data["page_nr"][] = $i;
            if ($item == $calculated) {
                $data["page_link"][$i] = '';
                $data["page_status"][$i] = 'active';
            } else {
                $data["page_link"][$i] = $albumpath . "start=" . $start . "&amp;item=" . $calculated;
                $data["page_status"][$i] = '';
            }
        }
    }

    // "=>"
    $data["next_link"] = '';
    $data["next_status"] = '';
    $calculated = ($i - 1) * $photoalbum['show_pictures'];
    if ($calculated < $nr_pictures) {
        $data["page_nr"][] = $i;
        $data["next_link"] = $albumpath . "start=" . $i . "&amp;item=" . $calculated;
    } else {
        $data["next_status"] = 'disabled';
    }

    $style = '';
    if ($show_categories === true) {
        $style = ' style="float: left; background-color:white; height:auto; width:98%;padding:5px;"';
    }

    //$menu_path_photoalbum = $link_cls->get_link($uri_path, 'photoalbum',$tree_id);
    $path = 'index.php?page=photoalbum?tree_id=' . $tree_id;
    if ($show_categories === true) {
        $path .= '&amp;select_category=' . $pref;
    }

    if ($humo_option["url_rewrite"] == "j") {
        $path = 'photoalbum/' . $tree_id;
        if ($show_categories === true) {
            $path .= '?select_category=' . $pref;
        }
    }
?>

    <div<?= $style; ?>>

        <form method="post" action="<?= $path; ?>" style="display:inline">
            <div class="row mb-2">
                <div class="col-3"></div>

                <div class="col-auto">
                    <label for="show-pictures" class="col-form-label"><?= __('Photo\'s per page'); ?></label>
                </div>
                <div class="col-auto">
                    <select name="show_pictures" id="show_pictures" onChange="window.location=this.value" class="form-select form-select-sm">
                        <?php for ($i = 4; $i <= 60; $i++) { ?>
                            <option value="<?= $albumpath; ?>show_pictures=<?= $i; ?>&amp;start=0&amp;item=0&amp;select_category=<?= $chosen_tab; ?>" <?= $i == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>
                                <?= $i; ?>
                            </option>
                        <?php } ?>

                        <option value="<?= $albumpath; ?>show_pictures=100&amp;start=0&amp;item=0&amp;select_category=<?= $chosen_tab; ?>" <?= 100 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>100</option>
                        <option value="<?= $albumpath; ?>show_pictures=200&amp;start=0&amp;item=0&amp;select_category=<?= $chosen_tab; ?>" <?= 200 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>200</option>
                        <option value="<?= $albumpath; ?>show_pictures=400&amp;start=0&amp;item=0&amp;select_category=<?= $chosen_tab; ?>" <?= 400 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>400</option>
                        <option value="<?= $albumpath; ?>show_pictures=800&amp;start=0&amp;item=0&amp;select_category=<?= $chosen_tab; ?>" <?= 800 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>800</option>
                    </select>
                </div>

                <!-- Search by photo name -->
                <div class="col-auto">
                    <input type="text" name="search_media" value="<?= safe_text_show($photoalbum['search_media']); ?>" size="20" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-success">
                </div>
            </div>
        </form>

        <div style="padding:5px" class="center">
            <?php include __DIR__ . '/partial/pagination.php'; ?>
        </div>

        <?php
        // *** Show photos ***
        for ($picture_nr = $item; $picture_nr < ($item + $photoalbum['show_pictures']); $picture_nr++) {
            if (isset($media_files[$picture_nr]) && $media_files[$picture_nr]) {
                $filename = $media_files[$picture_nr];
                $picture_text = '';    // Text with link to person
                $picture_text2 = '';    // Text without link to person

                $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND LEFT(event_kind,7)='picture' AND LOWER(event_event)='" . safe_text_db(strtolower($filename)) . "'";
                $afbqry = $dbh->query($sql);
                if (!$afbqry->rowCount()) {
                    $picture_text = substr($filename, 0, -4);
                }
                while ($afbDb = $afbqry->fetch(PDO::FETCH_OBJ)) {
                    $person_cls = new person_cls;
                    $personDb = $db_functions->get_person($afbDb->event_connect_id);
                    $name = $person_cls->person_name($personDb);
                    $privacy = $person_cls->set_privacy($personDb);
                    if (!$privacy) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        $picture_text .= '<a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                        $picture_text2 .= $name["standard_name"];
                    }

                    $date_place = date_place($afbDb->event_date, $afbDb->event_place);
                    if ($afbDb->event_text || $date_place) {
                        if ($date_place) {
                            $picture_text .= $date_place . ' ';
                        }
                        $picture_text .= $afbDb->event_text . '<br>';

                        //$picture_text2.=$afbDb->event_text; // Only use event text in lightbox.
                        $picture_text2 .= '<br>';
                        if ($date_place) {
                            $picture_text2 .= $date_place . ' ';
                        }
                        $picture_text2 .= $afbDb->event_text;
                    }
                }

                // *** Show texts from connected objects (where object is saved in seperate table): Family Tree Maker GEDCOM file ***
                $picture_qry = $dbh->query("SELECT * FROM humo_events
                WHERE event_tree_id='" . $tree_id . "' AND event_kind='object' AND LOWER(event_event)='" . strtolower($filename) . "'");
                while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                    $connect_qry = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
                        AND connect_sub_kind='pers_object' AND connect_source_id='" . $pictureDb->event_gedcomnr . "'");
                    while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                        $person_cls = new person_cls;
                        @$personDb = $db_functions->get_person($connectDb->connect_connect_id);
                        $name = $person_cls->person_name($personDb);
                        $privacy = $person_cls->set_privacy($personDb);
                        if (!$privacy) {
                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                            if ($picture_text !== '' && $picture_text !== '0') {
                                $picture_text .= '<br>';
                            }
                            $picture_text .= '<a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                            $picture_text2 .= $name["standard_name"];
                        }

                        $date_place = date_place($pictureDb->event_date, $pictureDb->event_place);
                        if ($pictureDb->event_text || $date_place) {
                            if ($date_place) {
                                $picture_text .= $date_place . ' ';
                            }
                            $picture_text .= $pictureDb->event_text . '<br>';

                            $picture_text2 .= '<br>';
                            if ($date_place) {
                                $picture_text2 .= $date_place . ' ';
                            }
                            $picture_text2 .= $pictureDb->event_text;
                        }
                    }
                }

                $picture2 = show_picture($dir, $filename, 175, 120);
                // *** Check if media exists ***
                if (file_exists($picture2['path'] . $picture2['thumb_prefix'] . $picture2['picture'] . $picture2['thumb_suffix'])) {
                    $picture = '<img src="' . $picture2['path'] . $picture2['thumb_prefix'] . $picture2['picture'] . $picture2['thumb_suffix'] . '" width="' . $picture2['width'] . '" alt="' . $filename . '">';
                } else {
                    $picture = '<img src="images/missing-image.jpg" width="' . $picture2['width'] . '" alt="' . $filename . '">';
                }
                if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), array('jpg', 'png', 'gif', 'bmp', 'tif'))) {
        ?>

                    <div class="photobook">
                        <!-- Show photo using the lightbox: GLightbox effect -->
                        <a href="<?= $dir . $filename; ?>" class="glightbox3" data-gallery="gallery1" data-glightbox="description: .custom-desc<?= $picture_nr; ?>">
                            <!-- Need a class for multiple lines and HTML code in a text -->
                            <div class="glightbox-desc custom-desc<?= $picture_nr; ?>"><?= $picture_text2; ?></div>
                            <?= $picture; ?>
                        </a>
                        <div class="photobooktext"><?= $picture_text; ?></div>
                    </div>
        <?php
                } else {
                    $picture = '<div class="photobook"><a href="' .  $dir . $filename . '" target="_blank">' . $picture . '</a><div class="photobooktext">' . $picture_text . '</div></div>';
                    echo $picture;
                }
            }
        }
        ?>

        </div> <!-- end of white menu page -->
        <br clear="all"><br>

        <?php include __DIR__ . '/partial/pagination.php'; ?>

    <?php
}
