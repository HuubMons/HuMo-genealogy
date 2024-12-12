<?php
class PhotoalbumModel
{
    public function get_show_pictures()
    {
        $show_pictures = 8; // *** Default value ***

        // Remark: setcookie is done in header.
        if (isset($_COOKIE["humogenphotos"]) && is_numeric($_COOKIE["humogenphotos"])) {
            $show_pictures = $_COOKIE["humogenphotos"];
        } elseif (isset($_SESSION['save_show_pictures']) && is_numeric($_SESSION['save_show_pictures'])) {
            $show_pictures = $_SESSION['save_show_pictures'];
        }
        if (isset($_POST['show_pictures']) && is_numeric($_POST['show_pictures'])) {
            $show_pictures = $_POST['show_pictures'];
            $_SESSION['save_show_pictures'] = $show_pictures;
        }
        if (isset($_GET['show_pictures']) && is_numeric($_GET['show_pictures'])) {
            $show_pictures = $_GET['show_pictures'];
            $_SESSION['save_show_pictures'] = $show_pictures;
        }
        return $show_pictures;
    }

    public function get_search_media()
    {
        // *** Photo search ***
        $search_media = '';
        if (isset($_SESSION['save_search_media'])) {
            $search_media = $_SESSION['save_search_media'];
        }
        if (isset($_POST['search_media'])) {
            $search_media = safe_text_db($_POST['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        if (isset($_GET['search_media'])) {
            $search_media = safe_text_db($_GET['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        return $search_media;
    }

    public function get_categories($dbh)
    {
        $photoalbum['category'] = [];
        $photoalbum['category_id'] = [];
        $photoalbum['category_enabled'] = [];
        // *** Check if photocat table exists ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if ($temp->rowCount()) {
            // *** Get array of categories ***
            $temp2 = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none'");
            // the table contains more than the default category (otherwise display regular photoalbum)
            if ($temp2->rowCount() >= 1) {
                $qry = "SELECT photocat_id, photocat_prefix FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
                $result = $dbh->query($qry);
                $result_arr = $result->fetchAll();
                foreach ($result_arr as $row) {
                    $photoalbum['category'][] = $row['photocat_prefix'];
                    $photoalbum['category_id'][$row['photocat_prefix']] = $row['photocat_id'];
                    $photoalbum['category_enabled'][$row['photocat_prefix']] = false;
                }
            }
        }
        return $photoalbum;
    }

    public function get_chosen_tab($category)
    {
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
            if (isset($category) && $_GET['select_category'] != 'none' && in_array($_GET['select_category'], $category)) {
                $chosen_tab = $_GET['select_category'];
                $_SESSION['save_chosen_tab'] = $chosen_tab;
            }
        }
        return $chosen_tab;
    }

    public function get_media_files($dbh, $tree_id, $db_functions, $chosen_tab, $search_media, $category)
    {
        $photoalbum['media_files'] = [];

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
            if ($chosen_tab == 'none' && !isset($category)) {
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
            elseif ($chosen_tab == 'none' && isset($category)) {
                // *** There are categories: filter these items in main page ***
                $process_picture = true;
                // *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
                foreach ($category as $test_category) {
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
            if ($search_media) {
                $quicksearch = str_replace(" ", "%", $search_media);
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
                    $person_cls = new Person_cls;
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
                        $person_cls = new Person_cls;
                        $personDb = $db_functions->get_person($connectDb->connect_connect_id);
                        $privacy = $person_cls->set_privacy($personDb);
                        if ($privacy) {
                            $process_picture = false;
                        }
                    }
                }
            }

            if ($process_picture) {
                if (!isset($photoalbum['media_files']) || !in_array($picname, $photoalbum['media_files'])) { // this pic does not appear in the array yet
                    //$connected_persons[$picname]=$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354"

                    // *** Skip PDF and RTF files ***
                    $check_file = strtolower($picname);
                    if (substr($check_file, -4) !== '.pdf' && substr($check_file, -4) !== '.rtf') {
                        $photoalbum['media_files'][] = $picname;
                    }
                }

                //else { // pic already exists in array with other person_id. Append this one.
                //	$connected_persons[$picname] .= '@@'.$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354@@I653"
                //	//$photoalbum['media_files'][]=$picname;
                //}
            }

            // *** Check if media belongs to category ***
            if (isset($category)) {
                foreach ($category as $test_category) {
                    // *** Check if media belongs to category ***
                    // *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
                    if (strpos($picname, $test_category) !== false) {
                        $photoalbum['show_categories'] = true; // *** There are categories ***
                        $photoalbum['category_enabled'][$test_category] = true; // *** This categorie will be shown ***
                    }

                    // *** Check if directory belongs to category, example: xy/picture.jpg ***
                    if (strpos($picname, substr($test_category, 0, 2) . '/') !== false) {
                        $photoalbum['show_categories'] = true; // *** There are categories ***
                        $photoalbum['category_enabled'][$test_category] = true; // *** This categorie will be shown ***
                    }
                }
            } else {
                // *** There were categories, but they were disabled or removed ***
                //$chosen_tab = 'none';
                //$_SESSION['save_chosen_tab'] = $chosen_tab;
            }
        }
        return $photoalbum;
    }
}
