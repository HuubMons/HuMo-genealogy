<?php
class PhotoalbumModel extends BaseModel
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

    public function get_search_media(): string
    {
        $safeTextDb = new SafeTextDb();

        // *** Photo search ***
        $search_media = '';
        if (isset($_SESSION['save_search_media'])) {
            $search_media = $_SESSION['save_search_media'];
        }
        if (isset($_POST['search_media'])) {
            $search_media = $safeTextDb->safe_text_db($_POST['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        if (isset($_GET['search_media'])) {
            $search_media = $safeTextDb->safe_text_db($_GET['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        return $search_media;
    }

    public function get_categories($selected_language): array
    {
        // *** Check if user has permission to view categories ***
        $hide_photocat_array = explode(";", $this->user['group_hide_photocat']);

        $photoalbum['category'] = [];
        $photoalbum['category_id'] = [];
        $photoalbum['category_enabled'] = [];
        // *** Check if photocat table exists ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if ($temp->rowCount()) {
            // *** Get array of categories ***
            $temp2 = $this->dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none'");
            // the table contains more than the default category (otherwise display regular photoalbum)
            if ($temp2->rowCount() >= 1) {
                $qry = "SELECT photocat_id, photocat_prefix FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
                $result = $this->dbh->query($qry);
                $result_arr = $result->fetchAll();
                foreach ($result_arr as $row) {
                    if (!in_array($row['photocat_id'], $hide_photocat_array)) {
                        $photoalbum['category'][] = $row['photocat_prefix'];
                        $photoalbum['category_id'][$row['photocat_prefix']] = $row['photocat_id'];
                        $photoalbum['category_enabled'][$row['photocat_prefix']] = false;

                        $photoalbum['category_name'][$row['photocat_prefix']] = __('NO NAME');
                        // check if name for this category exists for this language
                        $qry2 = "SELECT * FROM humo_photocat WHERE photocat_prefix ='" . $row['photocat_prefix'] . "' AND photocat_language ='" . $selected_language . "'";
                        $result2 = $this->dbh->query($qry2);
                        if ($result2->rowCount() != 0) {
                            $catnameDb = $result2->fetch(PDO::FETCH_OBJ);
                            $photoalbum['category_name'][$row['photocat_prefix']] = $catnameDb->photocat_name;
                        } else {
                            // check if default name exists for this category
                            $qry3 = "SELECT * FROM humo_photocat WHERE photocat_prefix ='" . $row['photocat_prefix'] . "' AND photocat_language ='default'";
                            $result3 = $this->dbh->query($qry3);
                            if ($result3->rowCount() != 0) {
                                $catnameDb = $result3->fetch(PDO::FETCH_OBJ);
                                $photoalbum['category_name'][$row['photocat_prefix']] = $catnameDb->photocat_name;
                            }
                        }
                    }
                }
            }
        }
        return $photoalbum;
    }

    public function get_chosen_tab($category): string
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

    public function get_media_files($chosen_tab, $search_media, $category): array
    {
        $personPrivacy = new PersonPrivacy();
        $photoalbum['media_files'] = [];

        // *** Create an array of all pics with person_id's. Also check for OBJECT (Family Tree Maker GEDCOM file) ***
        $qry = "SELECT event_event, event_kind, event_connect_kind, event_connect_id, event_gedcomnr FROM humo_events
            WHERE (event_tree_id='" . $this->tree_id . "' AND event_connect_kind='person' AND event_kind='picture' AND event_connect_id NOT LIKE '')
            OR (event_tree_id='" . $this->tree_id . "' AND event_kind='object')
            ORDER BY event_event";
        $picqry = $this->dbh->query($qry);
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
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $picqryDb->event_connect_id . "'
                    AND CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$quicksearch%'";
                $persoon = $this->dbh->query($querie);
                $personDb = $persoon->fetch(PDO::FETCH_OBJ);
                if (!$personDb) {
                    $process_picture = false;
                }
            }

            // *** Check for privacy of connected persons ***
            if ($process_picture) {
                if ($picqryDb->event_connect_id) {
                    // *** Check privacy filter ***
                    $personDb = $this->db_functions->get_person($picqryDb->event_connect_id);
                    $privacy = $personPrivacy->get_privacy($personDb);
                    if ($privacy) {
                        $process_picture = false;
                    }
                    //$personPrivacy = new PersonPrivacy();
                    //$personName = new PersonName();
                    //$privacy = $personPrivacy->get_privacy($personDb);
                    //$name=$personName->get_person_name($personDb, $privacy);
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    //$personLink = new PersonLink();
                    //$url = $personLink->get_person_link($personDb);
                    //$picture_text.='<a href="'.$url.'">'.$name["standard_name"].'</a><br>';
                    //$picture_text2.=$name["standard_name"];
                } else {
                    // *** OBJECTS: Family Tree Maker GEDCOM file ***
                    $connect_qry = $this->dbh->query("SELECT connect_connect_id FROM humo_connections
                        WHERE connect_tree_id='" . $this->tree_id . "' AND connect_sub_kind='pers_object' AND connect_source_id='" . $picqryDb->event_gedcomnr . "'");
                    while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                        $personDb = $this->db_functions->get_person($connectDb->connect_connect_id);
                        $privacy = $personPrivacy->get_privacy($personDb);
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

    public function calculate_pages($photoalbum): array
    {
        $processLinks = new ProcessLinks();

        // *** Calculate pages ***
        $nr_pictures = count($photoalbum['media_files']);

        $albumpath = $processLinks->get_link($this->uri_path, 'photoalbum', $this->tree_id, true);

        $item = 0;
        if (isset($_GET['item'])) {
            $item = $_GET['item'];
        }
        $start = 0;
        if (isset($_GET["start"])) {
            $start = $_GET["start"];
        }

        // At this moment this line is only used to prevent VS code error.
        $photoalbum['show_pictures'] = $photoalbum['show_pictures'] ? $photoalbum['show_pictures'] : 20;

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
                    $data["page_status"][$i] = 'active';
                } else {
                    $data["page_status"][$i] = '';
                }
                $data["page_link"][$i] = $albumpath . "start=" . $start . "&amp;item=" . $calculated;
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

        // Only needed for empty page to prevent fault message.
        if (!isset($data["page_nr"])) {
            $data["page_nr"][] = 1;
        }
        if (!isset($data["page_link"])) {
            $data["page_link"][] = 1;
        }
        if (!isset($data["page_status"])) {
            $data["page_status"][] = 1;
        }

        return $data;
    }
}
