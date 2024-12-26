<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$userid = false;
if (is_numeric($_SESSION['user_id_admin'])) {
    $userid = $_SESSION['user_id_admin'];
}

// *** Return deletion confim box in $confirm variabele ***
$confirm = '';
$confirm_relation = '';



// **************************
// *** PROCESS DATA EVENT ***
// **************************

// *** Add new event ***
$new_event = false;
if (!isset($_GET['add_person'])) {
    if (isset($_GET['event_add'])) {
        $new_event = true;
        $event_add = $_GET['event_add'];
    }

    // *** Add Nickname ***
    if (isset($_POST['event_add_name'])) {
        $new_event = true;
        $event_add = 'add_name';
    }
    // *** If "Save" is clicked, also save event names ***
    if (isset($_POST['event_event_name']) && $_POST['event_event_name'] != '') {
        $new_event = true;
        $event_add = 'add_name';
    }

    // *** April 2023: using POST so it's possible to save person data if event is added ***
    if (isset($_POST['add_birth_declaration'])) {
        $new_event = true;
        $event_add = 'add_birth_declaration';
    }
    if (isset($_POST['add_baptism_witness'])) {
        $new_event = true;
        $event_add = 'add_baptism_witness';
    }
    if (isset($_POST['add_death_declaration'])) {
        $new_event = true;
        $event_add = 'add_death_declaration';
    }
    if (isset($_POST['add_burial_witness'])) {
        $new_event = true;
        $event_add = 'add_burial_witness';
    }
    if (isset($_POST['add_marriage_witness'])) {
        $new_event = true;
        $event_add = 'add_marriage_witness';
    }
    if (isset($_POST['add_marriage_witness_rel'])) {
        $new_event = true;
        $event_add = 'add_marriage_witness_rel';
    }

    // *** Add profession ***
    if (isset($_POST['event_add_profession'])) {
        $new_event = true;
        $event_add = 'add_profession';
    }
    // *** If "Save" is clicked, also save event names ***
    if (isset($_POST['event_event_profession']) && $_POST['event_event_profession'] != '') {
        $new_event = true;
        $event_add = 'add_profession';
    }

    // *** Add religion ***
    if (isset($_POST['event_add_religion'])) {
        $new_event = true;
        $event_add = 'add_religion';
    }
    // *** If "Save" is clicked, also save event names ***
    if (isset($_POST['event_event_religion']) && $_POST['event_event_religion'] != '') {
        $new_event = true;
        $event_add = 'add_religion';
    }

    // *** Add picture ***
    if (isset($_POST['add_picture'])) {
        $new_event = true;
        $event_add = 'add_picture';
    }
    if (isset($_POST['add_marriage_picture'])) {
        $new_event = true;
        $event_add = 'add_marriage_picture';
    }
    if (isset($_POST['add_source_picture'])) {
        $new_event = true;
        $event_add = 'add_source_picture';
    }
}
if ($new_event) {
    if (isset($_POST['marriage'])) {
        $marriage = $_POST['marriage'];
    } // *** Needed to check $_POST for multiple relations ***

    if ($event_add == 'add_name') {
        $event_connect_kind = 'person';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'name';

        if ($_POST['event_gedcom_add'] == 'NPFX') {
            $event_kind = 'NPFX';
        }
        if ($_POST['event_gedcom_add'] == 'NSFX') {
            $event_kind = 'NSFX';
        }
        if ($_POST['event_gedcom_add'] == 'nobility') {
            $event_kind = 'nobility';
        }
        if ($_POST['event_gedcom_add'] == 'title') {
            $event_kind = 'title';
        }
        if ($_POST['event_gedcom_add'] == 'lordship') {
            $event_kind = 'lordship';
        }

        $event_event = $_POST['event_event_name'];
        $event_gedcom = $_POST['event_gedcom_add'];
    }

    if ($event_add == 'add_birth_declaration') {
        $event_connect_kind = 'birth_declaration';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = '';
    }
    if ($event_add == 'add_baptism_witness') {
        $event_connect_kind = 'CHR';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = 'WITN';
    }
    if ($event_add == 'add_death_declaration') {
        $event_connect_kind = 'death_declaration';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = '';
    }
    if ($event_add == 'add_burial_witness') {
        $event_connect_kind = 'BURI';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = 'WITN';
    }
    if ($event_add == 'add_marriage_witness') {
        $event_connect_kind = 'MARR';
        $event_connect_id = $marriage;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = 'WITN';
    }
    if ($event_add == 'add_marriage_witness_rel') {
        $event_connect_kind = 'MARR_REL';
        $event_connect_id = $marriage;
        $event_kind = 'ASSO';
        $event_event = '';
        $event_gedcom = 'WITN';
    }

    if ($event_add == 'add_profession') {
        $event_connect_kind = 'person';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'profession';
        $event_gedcom = '';
        $event_event = $_POST['event_event_profession'];
    }

    if ($event_add == 'add_religion') {
        $event_connect_kind = 'person';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'religion';
        $event_gedcom = 'RELI';
        $event_event = $_POST['event_event_religion'];
    }

    // *** Picture by person ***
    if ($event_add == 'add_picture') {
        $event_connect_kind = 'person';
        $event_connect_id = $pers_gedcomnumber;
        $event_kind = 'picture';
        $event_event = '';
        $event_gedcom = '';
    }
    // *** Picture by relation ***
    if ($event_add == 'add_marriage_picture') {
        $event_connect_kind = 'family';
        $event_connect_id = $marriage;
        $event_kind = 'picture';
        $event_event = '';
        $event_gedcom = '';
    }
    // *** Picture by source ***
    if ($event_add == 'add_source_picture') {
        //$event_connect_kind='source'; $event_connect_id=$_GET['source_id']; $event_kind='picture'; $event_event=''; $event_gedcom='';
        $event_connect_kind = 'source';
        $event_connect_id = $_POST['source_gedcomnr'];
        $event_kind = 'picture';
        $event_event = '';
        $event_gedcom = '';
    }

    // *** Add event. If event is new, use: $new_event=true. ***
    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
    add_event(false, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, '', '', '');
}

// *** Add person event ***
if (isset($_POST['person_event_add'])) {
    // *** Add event. If event is new, use: $new_event=true. ***
    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
    add_event(false, 'person', $pers_gedcomnumber, $_POST["event_kind"], '', '', '', '', '');
}

// *** Add marriage event ***
if (isset($_POST['marriage_event_add'])) {
    // *** Add event. If event is new, use: $new_event=true. ***
    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
    //add_event(false,'family',$marriage,$_POST["event_kind"],'','','','','');
    add_event(false, 'family', safe_text_db($_POST['marriage']), $_POST["event_kind"], '', '', '', '', '');
}


// *** Upload images ***
if (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['name']) {
    include_once(__DIR__ . "/../include/media_inc.php");
    include_once(__DIR__ . "/../include/showMedia.php");
    $showMedia = new showMedia();

    // *** get path of pictures folder 
    $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='" . $tree_prefix . "'");
    $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
    $tree_pict_path = $dataDb->tree_pict_path;
    if (substr($tree_pict_path, 0, 1) === '|') {
        $tree_pict_path = 'media/';
    }
    $dir = $path_prefix . $tree_pict_path;

    $safepath = '';
    $selected_subdir = preg_replace("/[\/\\\\]/", '',  $_POST['select_media_folder']); // remove all / and \ 
    if (array_key_exists(substr($_FILES['photo_upload']['name'], 0, 3), $showMedia->pcat_dirs)) { // old suffix style categories
        $dir .= substr($_FILES['photo_upload']['name'], 0, 2) . '/';
    } elseif (
         // new user selected dirs/cats
        !empty($selected_subdir) && is_dir($dir . $selected_subdir)
    ) {
        $dir .= $selected_subdir . '/';
        $safepath = $selected_subdir . '/';
    }
    $picture_original = $dir . $_FILES['photo_upload']['name'];
    if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'], $picture_original)) {
        echo __('Photo upload failed, check folder rights');
    } elseif (check_media_type($dir, $_FILES['photo_upload']['name'])) {
        resize_picture($dir, $_FILES['photo_upload']['name']); // resize only big image files to H=1080px
        create_thumbnail($dir, $_FILES['photo_upload']['name']);
        // *** Add picture to array ***
        $picture_array[] = $_FILES['photo_upload']['name'];

        // *** Re-order pictures by alphabet ***
        @sort($picture_array);
        $nr_pictures = count($picture_array);

        // *** Directly connect new media to person or relation ***
        if (isset($_POST['person_add_media'])) {
            $event_connect_kind = 'person';
            $event_connect_id = $pers_gedcomnumber;
            $event_kind = 'picture';
            $event_event = $safepath . $_FILES['photo_upload']['name'];
            $event_gedcom = '';
        }
        if (isset($_POST['relation_add_media'])) {
            $event_connect_kind = 'family';
            $event_connect_id = $marriage;
            $event_kind = 'picture';
            $event_event = $safepath . $_FILES['photo_upload']['name'];
            $event_gedcom = '';
        }
        if (isset($_POST['source_add_media'])) {
            $event_connect_kind = 'source';
            $event_connect_id = $_POST['source_gedcomnr'];
            $event_kind = 'picture';
            $event_event = $safepath . $_FILES['photo_upload']['name'];
            $event_gedcom = '';
        }
        // *** Add event. If event is new, use: $new_event=true. ***
        // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
        add_event(false, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, '', '', '');
    } else {
        echo '<font color="red">' . __('No valid picture, media or document file') .  '</font>';
    }
}


// *** Change event ***
//also check is_numeric
if (isset($_POST['event_id'])) {
    foreach ($_POST['event_id'] as $key => $value) {
        $event_event = '';
        if (isset($_POST["text_event"][$key])) {
            $event_event = $editor_cls->text_process($_POST["text_event"][$key]);
        }

        // *** Replaced array function, because witness popup javascript doesn't work using an html-form-array ***
        //if (isset($_POST["text_event2" . $key]) and $_POST["text_event2" . $key] != '') {
        //    $event_event = '@' . $_POST["text_event2" . $key] . '@';
        //}
        $event_connect_kind2 = '';
        $event_connect_id2 = '';
        // *** Replaced array function, because witness popup javascript doesn't work using an html-form-array ***
        if (isset($_POST["event_connect_id2" . $key]) && $_POST["event_connect_id2" . $key] != '') {
            $event_connect_kind2 = 'person';
            $event_connect_id2 = $_POST["event_connect_id2" . $key];
        }

        // *** Media selection pop-up option *** 
        if (isset($_POST["text_event" . $key]) && $_POST["text_event" . $key] != '') {
            $event_event = $editor_cls->text_process($_POST["text_event" . $key]);
        }

        // *** Only update if there are changed values! Otherwise all event_change variables will be changed... ***
        $event_id = $_POST["event_id"][$key];
        if (is_numeric($event_id)) {
            // *** Read old values ***
            $event_qry = "SELECT * FROM humo_events WHERE event_id='" . $event_id . "'";
            $event_result = $dbh->query($event_qry);
            $eventDb = $event_result->fetch(PDO::FETCH_OBJ);
            $event_changed = false;

            if ($event_event != $eventDb->event_event) {
                $event_changed = true;
            }

            if ($event_connect_id2 != $eventDb->event_connect_id2) {
                $event_changed = true;
            }

            // *** Compare date case-insensitive (for PHP 8.1 check if variabele is used) ***
            //if (isset($_POST["event_date_prefix"][$key]) OR isset($_POST["event_date"][$key])){
            // Doesn't work properly, date isn't always saved:
            //if ($eventDb->event_date AND ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key])){
            // Doesn't work if date is removed:
            //if ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key]){
            //if (isset($eventDb->event_date)){
            if (isset($_POST["event_date"][$key])) {
                $event_date = '';
                if (isset($eventDb->event_date)) {
                    $event_date = $eventDb->event_date;
                }
                if (strcasecmp($_POST["event_date_prefix"][$key] . $_POST["event_date"][$key], $event_date) != 0) {
                    $event_changed = true;
                }
            }
            if (isset($_POST["event_place" . $key]) && $_POST["event_place" . $key] != $eventDb->event_place) {
                $event_changed = true;
            }
            if (isset($_POST["event_event_extra"][$key]) && $_POST["event_event_extra"][$key] != $eventDb->event_event_extra) {
                $event_changed = true;
            }
            if (isset($_POST["event_gedcom"][$key]) && $_POST["event_gedcom"][$key] != $eventDb->event_gedcom) {
                $event_changed = true;
            }
            if (isset($_POST["event_text"][$key]) && $_POST["event_text"][$key] != $eventDb->event_text) {
                $event_changed = true;
            }

            if ($event_changed) {
                $sql = "UPDATE humo_events SET
                    event_event='" . $event_event . "',
                    event_connect_kind2='" . $event_connect_kind2 . "',
                    event_connect_id2='" . $event_connect_id2 . "',";

                if (isset($_POST["event_date"][$key])) {
                    $sql .= "event_date='" . $editor_cls->date_process("event_date", $key) . "',";
                }

                if (isset($_POST["event_place" . $key])) {
                    $sql .= "event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',";
                }

                if (isset($_POST["event_event_extra"][$key])) {
                    $sql .= "event_event_extra='" . $editor_cls->text_process($_POST["event_event_extra"][$key]) . "',";

                    // *** If witness isn't a connected person (other role), then use OTHER ***
                    if (isset($_POST["check_event_kind"][$key]) && $_POST["check_event_kind"][$key] == 'ASSO' && $_POST["event_event_extra"][$key]) {
                        $_POST["event_gedcom"][$key] = 'OTHER';
                    }
                }

                if (isset($_POST["event_gedcom"][$key])) {
                    $sql .= "event_gedcom='" . $editor_cls->text_process($_POST["event_gedcom"][$key]) . "',";
                }
                if (isset($_POST["event_text"][$key])) {
                    $sql .= "event_text='" . $editor_cls->text_process($_POST["event_text"][$key]) . "',";
                }
                $sql .= "event_changed_user_id='" . $userid . "'";

                $sql .= " WHERE event_id='" . $event_id . "'";

                $dbh->query($sql);
            }
        }

        // *** Also change person colors by descendants of selected person ***
        if (isset($_POST["pers_colour_desc"][$key])) {
            // EXAMPLE: get_descendants($family_id,$main_person,$generation_number,$nr_generations);
            get_descendants($marriage, $pers_gedcomnumber, 0, 20);
            // *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
            // *** $descendant_array[0]= not in use ***
            // *** $descendant_array[1]= main person ***
            for ($i = 2; $i <= $descendant_id; $i++) {
                // *** Check if descendant already has this colour ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' 
                    AND event_connect_id='" . $descendant_array[$i] . "'
                    AND event_kind='person_colour_mark'
                    AND event_event='" . safe_text_db($_POST["event_event_old"][$key]) . "'";
                $event_qry = $dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

                $event_gedcom = '';
                if (isset($_POST["event_gedcom"][$key])) {
                    $event_gedcom = $_POST["event_gedcom"][$key];
                }
                $event_text = '';
                if (isset($_POST["event_text"][$key])) {
                    $event_text = $_POST["event_text"][$key];
                }

                // *** Descendant already has this color, change it ***
                if (isset($eventDb->event_event)) {
                    $sql = "UPDATE humo_events SET
                        event_event='" . $event_event . "',
                        event_date='" . $editor_cls->date_process("event_date", $key) . "',
                        event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',
                        event_changed_user_id='" . $userid . "',
                        event_gedcom='" . $editor_cls->text_process($event_gedcom) . "',
                        event_text='" . $editor_cls->text_process($event_text) . "',
                        event_changed_time='" . $gedcom_time . "'
                        WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                } else {
                    // *** Add person event for descendants ***
                    // *** Add event. If event is new, use: $new_event=true. ***
                    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                    add_event(false, 'person', $descendant_array[$i], 'person_colour_mark', $event_event, $event_gedcom, 'event_date', $_POST["event_place" . $key], $event_text, $key);
                }
            }
        }

        // *** Also change person colors by ancestors of selected person ***
        if (isset($_POST["pers_colour_anc"][$key])) {
            $ancestor_array = get_ancestors($db_functions, $pers_gedcomnumber);
            foreach ($ancestor_array as $key2 => $value) {
                //echo $key2.'-'.$value.', ';
                $selected_ancestor = $value;

                // *** Check if ancestor already has this colour ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person'
                    AND event_connect_id='" . $selected_ancestor . "'
                    AND event_kind='person_colour_mark'
                    AND event_event='" . safe_text_db($_POST["event_event_old"][$key]) . "'";
                $event_qry = $dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

                $event_gedcom = '';
                if (isset($_POST["event_gedcom"][$key])) {
                    $event_gedcom = $_POST["event_gedcom"][$key];
                }
                $event_text = '';
                if (isset($_POST["event_text"][$key])) {
                    $event_text = $_POST["event_text"][$key];
                }

                // *** Ancestor already has this color, change it ***
                if (isset($eventDb->event_event)) {
                    $sql = "UPDATE humo_events SET
                        event_event='" . $event_event . "',
                        event_date='" . $editor_cls->date_process("event_date", $key) . "',
                        event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',
                        event_changed_user_id='" . $userid . "',
                        event_gedcom='" . $editor_cls->text_process($event_gedcom) . "',
                        event_text='" . $editor_cls->text_process($event_text) . "'
                        WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                } else {
                    // *** Add person event for ancestors ***
                    // *** Add event. If event is new, use: $new_event=true. ***
                    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                    add_event(false, 'person', $selected_ancestor, 'person_colour_mark', $event_event, $event_gedcom, 'event_date', $_POST["event_place" . $key], $event_text, $key);
                }
            }
        }
    }
}

// *** Remove event ***
if (isset($_GET['event_drop'])) {
    $confirm .= '<div class="alert alert-danger">';
    $confirm .= '<strong>' . __('Are you sure you want to remove this event?') . '</strong>';
    $confirm .= ' <form method="post" action="index.php';
    if (isset($_GET['source_id'])) {
        $confirm .= '?source_id=' . $_GET['source_id'];
    }
    $confirm .= '" style="display : inline;">';
    $confirm .= '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
    $confirm .= '<input type="hidden" name="event_connect_kind" value="' . $_GET['event_connect_kind'] . '">';
    $confirm .= '<input type="hidden" name="event_kind" value="' . $_GET['event_kind'] . '">';
    $confirm .= '<input type="hidden" name="event_drop" value="' . $_GET['event_drop'] . '">';

    if (isset($_GET['event_kind']) && $_GET['event_kind'] == 'person_colour_mark') {
        $selected = ''; //if ($selected_alive=='alive'){ $selected=' checked'; }
        $confirm .= '<br>' . __('Also remove colour marks of');
        $confirm .= ' <input type="checkbox" name="event_descendants" value="alive"' . $selected . '> ' . __('Descendants');
        $confirm .= ' <input type="checkbox" name="event_ancestors" value="alive"' . $selected . '> ' . __('Ancestors') . '<br>';
    }

    $confirm .= ' <input type="submit" name="event_drop2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
    $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
    $confirm .= '</form>';
    $confirm .= '</div>';
}
if (isset($_POST['event_drop2'])) {
    $event_kind = safe_text_db($_POST['event_kind']);
    $event_order_id = safe_text_db($_POST['event_drop']);

    //if (isset($_POST['event_person'])) {
    if ($_POST['event_connect_kind'] == 'person') {

        // *** Remove NON SHARED source from event (connection in humo_connections table) ***
        $event_sql = "SELECT * FROM humo_events
            WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
            AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
        $event_qry = $dbh->query($event_sql);
        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
        $event_event = $eventDb->event_event;

        // *** Remove sources ***
        remove_sources($tree_id, 'pers_event_source', $eventDb->event_id);

        if (isset($_POST['event_descendants']) || isset($_POST['event_ancestors'])) {
            // *** Get event_event from selected person, needed to remove colour from descendant and/ or ancestors ***
            $event_sql = "SELECT event_event FROM humo_events
                WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
                AND event_kind='person_colour_mark' AND event_order='" . $event_order_id . "'";
            $event_qry = $dbh->query($event_sql);
            $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
            $event_event = $eventDb->event_event;
        }

        $sql = "DELETE FROM humo_events
            WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
            AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
        $dbh->query($sql);

        // *** Change order of events ***
        $event_sql = "SELECT * FROM humo_events
            WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
            AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
        $event_qry = $dbh->query($event_sql);
        while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
            $sql = "UPDATE humo_events SET
                event_order='" . ($eventDb->event_order - 1) . "',
                event_changed_user_id='" . $userid . "'
                WHERE event_id='" . $eventDb->event_id . "'";
            $dbh->query($sql);
        }

        // *** Also remove colour mark from descendants and/ or ancestors ***
        if (isset($_POST['event_descendants'])) {
            // EXAMPLE: get_descendants($family_id,$main_person,$generation_number,$nr_generations);
            get_descendants($marriage, $pers_gedcomnumber, 0, 20);
            // *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
            for ($i = 2; $i <= $descendant_id; $i++) {
                // *** Get event_order from selected person ***
                $event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                    AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                $event_qry = $dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                $event_order = $eventDb->event_order;

                // *** Remove colour from descendant ***
                $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                    AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                $dbh->query($sql);

                // *** Restore order of colour marks ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                    AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
                $event_qry = $dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_events SET
                    event_order='" . ($eventDb->event_order - 1) . "',
                    event_changed_user_id='" . $userid . "'
                    WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                }
            }
        }

        if (isset($_POST['event_ancestors'])) {
            $ancestor_array = get_ancestors($db_functions, $pers_gedcomnumber);
            foreach ($ancestor_array as $key2 => $value) {
                //echo $key2.'-'.$value.', ';
                $selected_ancestor = $value;

                // *** Get event_order from selected person ***
                $event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                    AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                $event_qry = $dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                $event_order = $eventDb->event_order;

                // *** Check if ancestor already has this colour ***
                $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                    AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                $dbh->query($sql);

                // *** Restore order of colour marks ***
                $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                    AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
                $event_qry = $dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_events SET
                    event_order='" . ($eventDb->event_order - 1) . "',
                    event_changed_user_id='" . $userid . "'
                    WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                }
            }
        }
    }

    //if (isset($_POST['event_family'])) {
    elseif ($_POST['event_connect_kind'] == 'family') {
        // *** Remove NON SHARED source from event (connection in humo_connections table) ***
        $event_sql = "SELECT * FROM humo_events
            WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
            AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
        $event_qry = $dbh->query($event_sql);
        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
        $event_event = $eventDb->event_event;

        // *** Remove sources ***
        remove_sources($tree_id, 'fam_event_source', $eventDb->event_id);

        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
            AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
        $dbh->query($sql);

        $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
            AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
        $event_qry = $dbh->query($event_sql);
        while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
            $sql = "UPDATE humo_events SET
                event_order='" . ($eventDb->event_order - 1) . "',
                event_changed_user_id='" . $userid . "'
                WHERE event_id='" . $eventDb->event_id . "'";
            $dbh->query($sql);
        }
    }

    // *** Picture by source: pictures are stored in event table ***
    //if (isset($_POST['event_source'])) {
    elseif ($_POST['event_connect_kind'] == 'source') {
        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='source' AND event_connect_id='" . safe_text_db($_GET['source_id']) . "'
            AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
        $dbh->query($sql);

        $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='source' AND event_connect_id='" . safe_text_db($_GET['source_id']) . "'
            AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
        $event_qry = $dbh->query($event_sql);
        while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
            $sql = "UPDATE humo_events SET
                event_order='" . ($eventDb->event_order - 1) . "',
                event_changed_user_id='" . $userid . "'
                WHERE event_id='" . $eventDb->event_id . "'";
            $dbh->query($sql);
        }
    } else {
        $event_connect_id = '';

        $check_connect_kind = array("birth_declaration", "CHR", "death_declaration", 'BURI');
        if (in_array($_POST['event_connect_kind'], $check_connect_kind)) {
            $event_connect_id = $pers_gedcomnumber;
        }
        if ($_POST['event_connect_kind'] == 'MARR' || $_POST['event_connect_kind'] == 'MARR_REL') {
            $event_connect_id = $marriage;
        }
        if ($event_connect_id) {
            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='" . $_POST['event_connect_kind'] . "' AND event_connect_id='" . safe_text_db($event_connect_id) . "'
                AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
            $dbh->query($sql);

            $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='" . $_POST['event_connect_kind'] . "' AND event_connect_id='" . safe_text_db($event_connect_id) . "'
                AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
            $event_qry = $dbh->query($event_sql);
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_events SET
                    event_order='" . ($eventDb->event_order - 1) . "',
                    event_changed_user_id='" . $userid . "'
                    WHERE event_id='" . $eventDb->event_id . "'";
                $dbh->query($sql);
            }
        }
    }
}

if (isset($_GET['event_down'])) {
    $event_kind = safe_text_db($_GET['event_kind']);
    $event_order = safe_text_db($_GET["event_down"]);
    $event_connect_id = $pers_gedcomnumber;

    $event_connect_kind = safe_text_db($_GET['event_connect_kind']);
    if ($event_connect_kind == 'person') {
        $event_connect_id = $pers_gedcomnumber;
    } elseif ($event_connect_kind == 'person') {
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'source') {
        $event_connect_id = $_GET['source_id'];
    } elseif ($event_connect_kind == 'MARR') {
        $event_connect_kind = 'MARR';
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'MARR_REL') {
        $event_connect_kind = 'MARR_REL';
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'family') {
        $event_connect_id = $marriage;
    }

    $sql = "UPDATE humo_events SET event_order='99' WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order='" . $event_order . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_events SET event_order='" . $event_order . "' WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order='" . ($event_order + 1) . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_events SET event_order='" . ($event_order + 1) . "' WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order=99";
    $dbh->query($sql);
}

if (isset($_GET['event_up'])) {
    $event_kind = safe_text_db($_GET['event_kind']);
    $event_order = safe_text_db($_GET['event_up']);

    /*
    if (isset($_GET['event_person'])) {
        $event_connect_kind = 'person';
        $event_connect_id = $pers_gedcomnumber;
    }
    if (isset($_GET['event_family'])) {
        $event_connect_kind = 'family';
        $event_connect_id = $marriage;
    }

    // *** Move picture by source in seperate source page ***
    if (isset($_GET['event_source'])) {
        $event_connect_kind = 'source';
        $event_connect_id = $_GET['source_id'];
    }
    */
    $event_connect_id = $pers_gedcomnumber;

    $event_connect_kind = safe_text_db($_GET['event_connect_kind']);
    if ($event_connect_kind == 'person') {
        $event_connect_id = $pers_gedcomnumber;
    } elseif ($event_connect_kind == 'person') {
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'source') {
        $event_connect_id = $_GET['source_id'];
    } elseif ($event_connect_kind == 'MARR') {
        $event_connect_kind = 'MARR';
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'MARR_REL') {
        $event_connect_kind = 'MARR_REL';
        $event_connect_id = $marriage;
    } elseif ($event_connect_kind == 'family') {
        $event_connect_id = $marriage;
    }

    // TEST
    /*
    $check_connect_kind = array("birth_declaration", "CHR", "death_declaration", 'BURI');
    if (isset($_GET['event_connect_kind']) && in_array($_GET['event_connect_kind'], $check_connect_kind)) {
        $event_connect_kind = $_GET['event_connect_kind'];
        $event_connect_id = $pers_gedcomnumber;
    }
    if (isset($_GET['event_connect_kind']) && $_GET['event_connect_kind']=='MARR') {
        $event_connect_kind = 'MARR';
        $event_connect_id = $marriage;
    }
    if (isset($_GET['event_connect_kind']) && $_GET['event_connect_kind']=='MARR_REL') {
        $event_connect_kind = 'MARR_REL';
        $event_connect_id = $marriage;
    }
    */


    $sql = "UPDATE humo_events SET event_order='99'
        WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order='" . $event_order . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_events SET
        event_order='" . $event_order . "'
        WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order='" . ($event_order - 1) . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_events SET
        event_order='" . ($event_order - 1) . "'
        WHERE event_tree_id='" . $tree_id . "'
        AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
        AND event_kind='" . $event_kind . "'
        AND event_order=99";
    $dbh->query($sql);
}


// ************************
// *** Save connections ***
// ************************

// *** Add new person-address connection ***
//if (isset($_GET['person_place_address']) AND isset($_GET['address_add'])){
if (isset($_POST['person_add_address'])) {
    $_POST['connect_add'] = 'add_address';
    $_POST['connect_kind'] = 'person';
    $_POST["connect_sub_kind"] = 'person_address';
    $_POST["connect_connect_id"] = $pers_gedcomnumber;
}
// *** Add new family-address connection ***
//if (isset($_GET['family_place_address']) AND isset($_GET['address_add'])){
if (isset($_POST['relation_add_address'])) {
    $_POST['connect_add'] = 'add_address';
    $_POST['connect_kind'] = 'family';
    $_POST["connect_sub_kind"] = 'family_address';
    $marriage = $_POST['marriage']; // *** Needed to check $_POST for multiple relations ***
    $_POST["connect_connect_id"] = $marriage;
}

// *** Added april 2023: Add new source ***
if (isset($_GET['source_add3'])) {
    $_POST['connect_add'] = 'add_source';
    $_POST['connect_kind'] = $_GET['connect_kind'];
    $_POST["connect_sub_kind"] = $_GET["connect_sub_kind"];
    $_POST["connect_connect_id"] = $_GET["connect_connect_id"];
}

/*
// *** Added may 2023: Add new source ***
//http://localhost/humo-genealogy/admin/index.php?page=editor&
//source_add3=1&
//connect_kind=person&
//connect_sub_kind=pers_name_source&
//connect_connect_id=I9892#pers_name_sourceI9892
if (isset($_POST['add_pers_name_source'])){
    $_POST['connect_add']='add_source';
    $_POST['connect_kind']='person';
    $_POST["connect_sub_kind"]='pers_name_source';
    $_POST["connect_connect_id"]=$pers_gedcomnumber;

unset ($_POST['connect_change']);
}
*/

// *** Add new source/ address connection ***
if (isset($_POST['connect_add'])) {
    // *** Generate new order number ***
    $event_sql = "SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . safe_text_db($_POST['connect_kind']) . "'
        AND connect_sub_kind='" . safe_text_db($_POST["connect_sub_kind"]) . "'
        AND connect_connect_id='" . safe_text_db($_POST["connect_connect_id"]) . "'";
    $event_qry = $dbh->query($event_sql);
    $count = $event_qry->rowCount();
    $count++;

    $sql = "INSERT INTO humo_connections SET
        connect_tree_id='" . $tree_id . "',
        connect_order='" . $count . "',
        connect_new_user_id='" . $userid . "',
        connect_kind='" . safe_text_db($_POST['connect_kind']) . "',
        connect_sub_kind='" . safe_text_db($_POST["connect_sub_kind"]) . "',
        connect_connect_id='" . safe_text_db($_POST["connect_connect_id"]) . "'";
    $dbh->query($sql);
} // *** End of update sources ***

// *** Change source/ address connection ***
if (isset($_POST['connect_change'])) {
    foreach ($_POST['connect_change'] as $key => $value) {
        // *** Only update if there are changed values! Otherwise all connect_change variables will be changed... ***
        $connect_changed = true;

        if (isset($_POST['connect_date_old'][$key])) {
            $connect_changed = false;
            // *** Compare date case-insensitive ***
            if (strcasecmp($_POST["connect_date_prefix"][$key] . $_POST["connect_date"][$key], $_POST["connect_date_old"][$key]) != 0) {
                $connect_changed = true;
            }
            if ($_POST["connect_role"][$key] != $_POST["connect_role_old"][$key]) {
                $connect_changed = true;
            }
            if ($_POST['connect_text'][$key] != $_POST["connect_text_old"][$key]) {
                $connect_changed = true;
            }

            // *** Save shared address (even if role or extra text isn't used) ***
            if (isset($_POST['connect_item_id'][$key])) {
                if (isset($_POST["connect_item_id_old"][$key])) {
                    if ($_POST['connect_item_id'][$key] !== $_POST["connect_item_id_old"][$key]) {
                        $connect_changed = true;
                    }
                } else {
                    $connect_changed = true;
                }
            }
        }

        // *** Remark: connect_kind and connect_sub_kind is missing if someone clicks "Add address" twice. ***
        if ($connect_changed) {
            $sql = "UPDATE humo_connections SET";
            if (isset($_POST['connect_kind'][$key])) {
                $sql .= " connect_kind='" . safe_text_db($_POST['connect_kind'][$key]) . "',";
            }
            if (isset($_POST['connect_sub_kind'][$key])) {
                $sql .= " connect_sub_kind='" . safe_text_db($_POST['connect_sub_kind'][$key]) . "',";
            }
            $sql .= " connect_page='" . $editor_cls->text_process($_POST["connect_page"][$key]) . "',
                connect_role='" . $editor_cls->text_process($_POST["connect_role"][$key]) . "',";

            if (isset($_POST['connect_source_id'][$key])) {
                $sql .= "connect_source_id='" . safe_text_db($_POST['connect_source_id'][$key]) . "',";
            }

            if (isset($_POST['connect_date'][$key])) {
                $sql .= "connect_date='" . $editor_cls->date_process("connect_date", $key) . "',";
            }

            if (isset($_POST['connect_place'][$key])) {
                $sql .= "connect_place='" . $editor_cls->text_process($_POST["connect_place"][$key]) . "',";
            }

            // *** Extra text for source ***
            if (isset($_POST['connect_text'][$key])) {
                $sql .= "connect_text='" . safe_text_db($_POST['connect_text'][$key]) . "',";
            }

            if (isset($_POST['connect_quality'][$key])) {
                $sql .= " connect_quality='" . safe_text_db($_POST['connect_quality'][$key]) . "',";
            }

            if (isset($_POST['connect_item_id'][$key]) && $_POST['connect_item_id'][$key]) {
                $sql .= " connect_item_id='" . safe_text_db($_POST['connect_item_id'][$key]) . "',";
            }

            $sql .= " connect_changed_user_id='" . $userid . "'
                WHERE connect_id='" . safe_text_db($_POST["connect_change"][$key]) . "'";
            //echo $sql.'<br>';
            $dbh->query($sql);
        }

        //source_status='".$editor_cls->text_process($_POST['source_status'][$key])."',
        //source_publ='".$editor_cls->text_process($_POST['source_publ'][$key])."',
        //source_auth='".$editor_cls->text_process($_POST['source_auth'][$key])."',
        //source_subj='".$editor_cls->text_process($_POST['source_subj'][$key])."',
        //source_item='".$editor_cls->text_process($_POST['source_item'][$key])."',
        //source_kind='".$editor_cls->text_process($_POST['source_kind'][$key])."',
        //source_repo_caln='".$editor_cls->text_process($_POST['source_repo_caln'][$key])."',
        //source_repo_page='".$editor_cls->text_process($_POST['source_repo_page'][$key])."',
        //source_repo_gedcomnr='".$editor_cls->text_process($_POST['source_repo_gedcomnr'][$key])."',
        if (isset($_POST['source_title'][$key])) {
            //source_date='".safe_text_db($_POST['source_date'][$key])."',
            //$source_shared=''; if (isset($_POST['source_shared'][$key])) $source_shared='1';
            //source_shared='".$source_shared."',

            $sql = "UPDATE humo_sources SET
            source_title='" . $editor_cls->text_process($_POST['source_title'][$key]) . "',
            source_text='" . $editor_cls->text_process($_POST['source_text'][$key], true) . "',
            source_refn='" . $editor_cls->text_process($_POST['source_refn'][$key]) . "',
            source_date='" . $editor_cls->date_process("source_date", $key) . "',
            source_place='" . $editor_cls->text_process($_POST['source_place'][$key]) . "',
            source_changed_user_id='" . $userid . "'
            WHERE source_tree_id='" . $tree_id . "' AND source_id='" . safe_text_db($_POST["source_id"][$key]) . "'";
            $dbh->query($sql);
        }
    }
}

// *** Remove source/ address connection ***
if (isset($_GET['connect_drop'])) {
    // *** Needed for event sources ***
    $connect_kind = '';
    if (isset($_GET['connect_kind'])) {
        $connect_kind = $_GET['connect_kind'];
    }

    $connect_sub_kind = '';
    if (isset($_GET['connect_sub_kind'])) {
        $connect_sub_kind = $_GET['connect_sub_kind'];
    }

    // *** Needed for event sources ***
    $connect_connect_id = '';
    if (isset($_GET['connect_connect_id']) && $_GET['connect_connect_id']) {
        $connect_connect_id = $_GET['connect_connect_id'];
    }
    //if (isset($_POST['connect_connect_id']) AND $_POST['connect_connect_id']) $connect_connect_id=$_POST['connect_connect_id'];

    $event_link = '';
    if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
        $event_link = '&event_person=1';
    }
    if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
        $event_link = '&event_family=1';
    }
    $phpself2 = 'index.php?page=' . $page . '&connect_kind=' . $connect_kind . '&connect_sub_kind=' . $connect_sub_kind . '&connect_connect_id=' . $connect_connect_id;
    $phpself2 .= $event_link;

?>
    <div class="alert alert-danger">
        <form method="post" action="<?= $phpself2; ?>" style="display : inline;">
            <input type="hidden" name="connect_drop" value="<?= $_GET['connect_drop']; ?>">
            <input type="hidden" name="connect_kind" value="<?= $connect_kind; ?>">
            <input type="hidden" name="connect_sub_kind" value="<?= $connect_sub_kind; ?>">
            <input type="hidden" name="connect_connect_id" value="<?= $connect_connect_id; ?>">

            <?php
            if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                echo '<input type="hidden" name="event_person" value="1">';
            }
            if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                echo '<input type="hidden" name="event_family" value="1">';
            }

            // *** Remove address event ***
            if (isset($_GET['person_place_address'])) {
                echo '<input type="hidden" name="person_place_address" value="person_place_address">';
            }

            if (isset($_GET['marriage_nr'])) {
                echo '<input type="hidden" name="marriage_nr" value="' . safe_text_db($_GET['marriage_nr']) . '">';
            }
            ?>

            <strong><?= __('Are you sure you want to remove this event?'); ?></strong>
            <input type="submit" name="connect_drop2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php
}
// *** Delete source or address connection ***
if (isset($_POST['connect_drop2'])) {
    $event_sql = "SELECT * FROM humo_connections
        WHERE connect_id='" . safe_text_db($_POST['connect_drop']) . "'";
    $event_qry = $dbh->query($event_sql);
    $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

    $connect_kind = $eventDb->connect_kind;
    $connect_sub_kind = $eventDb->connect_sub_kind;
    $connect_connect_id = $eventDb->connect_connect_id;
    //echo $connect_kind.' '.$connect_sub_kind.' '.$connect_connect_id.'!!';

    // *** Remove (NON-SHARED) source by all connections ***
    /*
    if ($eventDb->connect_source_id){
        //DOESN'T WORK
        //$sourceDb = $db_functions->get_source($eventDb->connect_source_id);
        $source_sql="SELECT * FROM humo_sources
            WHERE source_gedcomnr='".safe_text_db($eventDb->connect_source_id)."'
            AND source_shared!='1'";
        //echo $source_sql.'<br>';
        $source_qry=$dbh->query($source_sql);
        $sourceDb=$source_qry->fetch(PDO::FETCH_OBJ);
        if ($sourceDb){
            $sql="DELETE FROM humo_sources WHERE source_id='".safe_text_db($sourceDb->source_id)."'";
            $dbh->query($sql);
        }
    }
    */

    // *** Remove NON SHARED addresses ***
    if ($connect_sub_kind == 'person_address' || $connect_sub_kind == 'family_address') {
        $address_sql = "SELECT * FROM humo_addresses
            WHERE address_gedcomnr='" . safe_text_db($eventDb->connect_item_id) . "'
            AND address_shared!='1'";
        $address_qry = $dbh->query($address_sql);
        $addressDb = $address_qry->fetch(PDO::FETCH_OBJ);
        if ($addressDb) {
            $sql = "DELETE FROM humo_addresses WHERE address_id='" . safe_text_db($addressDb->address_id) . "'";
            $dbh->query($sql);
        }

        // *** Remove sources ***
        if ($connect_sub_kind == 'person_address') {
            remove_sources($tree_id, 'pers_address_source', $eventDb->connect_item_id);
        }
        if ($connect_sub_kind == 'family_address') {
            remove_sources($tree_id, 'fam_address_source', $eventDb->connect_item_id);
        }
    }

    $sql = "DELETE FROM humo_connections WHERE connect_id='" . safe_text_db($_POST['connect_drop']) . "'";
    $dbh->query($sql);

    // *** Re-order remaining source connections ***
    $event_order = 1;
    $event_sql = "SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . $connect_kind . "'
        AND connect_sub_kind='" . $connect_sub_kind . "'
        AND connect_connect_id='" . $connect_connect_id . "'
        ORDER BY connect_order";
    $event_qry = $dbh->query($event_sql);
    while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
        $sql = "UPDATE humo_connections
            SET connect_order='" . $event_order . "'
            WHERE connect_id='" . $eventDb->connect_id . "'";
        $dbh->query($sql);
        $event_order++;
    }
}

// TODO check if up and down links can be improved. Maybe only 1 $_GET needed: connect_down or connect_up (including connect_id nr). Get other items from database.
if (isset($_GET['connect_down'])) {
    $sql = "UPDATE humo_connections SET connect_order='99'
        WHERE connect_id='" . safe_text_db($_GET['connect_down']) . "'";
    $dbh->query($sql);

    $event_order = safe_text_db($_GET['connect_order']);
    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "'
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
        AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
        AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
        AND connect_order='" . ($event_order + 1) . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_connections SET connect_order='" . ($event_order + 1) . "'
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
        AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
        AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
        AND connect_order=99";
    $dbh->query($sql);
}

if (isset($_GET['connect_up'])) {
    $sql = "UPDATE humo_connections SET connect_order='99'
    WHERE connect_id='" . safe_text_db($_GET['connect_up']) . "'";
    $dbh->query($sql);

    $event_order = safe_text_db($_GET['connect_order']);
    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "'
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
        AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
        AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
        AND connect_order='" . ($event_order - 1) . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_connections SET connect_order='" . ($event_order - 1) . "'
        WHERE connect_tree_id='" . $tree_id . "'
        AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
        AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
        AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
        AND connect_order=99";
    $dbh->query($sql);
}


// *******************
// *** Save source ***
// *******************

// *** december 2020: new combined source and shared source system ***
if (isset($_GET['source_add2'])) {
    // *** Generate new GEDCOM number ***
    $new_gedcomnumber = 'S' . $db_functions->generate_gedcomnr($tree_id, 'source');

    $sql = "INSERT INTO humo_sources SET
    source_tree_id='" . $tree_id . "',
    source_gedcomnr='" . $new_gedcomnumber . "',
    source_status='',
    source_title='',
    source_date='',
    source_place='',
    source_publ='',
    source_refn='',
    source_auth='',
    source_subj='',
    source_item='',
    source_kind='',
    source_repo_caln='',
    source_repo_page='',
    source_repo_gedcomnr='',
    source_text='',
    source_new_user_id='" . $userid . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_connections SET
        connect_tree_id='" . $tree_id . "',
        connect_source_id='" . $new_gedcomnumber . "',
        connect_changed_user_id='" . $userid . "'
    WHERE connect_id='" . safe_text_db($_GET["connect_id"]) . "'";
    $dbh->query($sql);
}

//source_shared='".$editor_cls->text_process($_POST['source_shared'])."',
//if (isset($_POST['source_change'])){
$save_source_data = false;
if (isset($_POST['source_change'])) {
    $save_source_data = true;
}
// *** Also save source data if media is added ***
if (isset($_POST['source_add_media'])) {
    $save_source_data = true;
}
if ($save_source_data) {
    $sql = "UPDATE humo_sources SET
    source_status='" . $editor_cls->text_process($_POST['source_status']) . "',
    source_title='" . $editor_cls->text_process($_POST['source_title']) . "',
    source_date='" . $editor_cls->date_process('source_date') . "',
    source_place='" . $editor_cls->text_process($_POST['source_place']) . "',
    source_publ='" . $editor_cls->text_process($_POST['source_publ']) . "',
    source_refn='" . $editor_cls->text_process($_POST['source_refn']) . "',
    source_auth='" . $editor_cls->text_process($_POST['source_auth']) . "',
    source_subj='" . $editor_cls->text_process($_POST['source_subj']) . "',
    source_item='" . $editor_cls->text_process($_POST['source_item']) . "',
    source_kind='" . $editor_cls->text_process($_POST['source_kind']) . "',
    source_repo_caln='" . $editor_cls->text_process($_POST['source_repo_caln']) . "',
    source_repo_page='" . $editor_cls->text_process($_POST['source_repo_page']) . "',
    source_repo_gedcomnr='" . $editor_cls->text_process($_POST['source_repo_gedcomnr']) . "',
    source_text='" . $editor_cls->text_process($_POST['source_text'], true) . "',
    source_changed_user_id='" . $userid . "'
    WHERE source_tree_id='" . $tree_id . "' AND source_id='" . safe_text_db($_POST["source_id"]) . "'";
    $dbh->query($sql);
}


// ************************
// *** Save data places ***
// ************************

// *** 25-12-2020: NEW combined addresses and shared addresses ***
if (isset($_GET['address_add2'])) {
    // *** Generate new GEDCOM number ***
    $new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'address');

    $sql = "INSERT INTO humo_addresses SET
    address_tree_id='" . $tree_id . "',
    address_gedcomnr='" . $new_gedcomnumber . "',
    address_address='',
    address_date='',
    address_zip='',
    address_place='',
    address_phone='',
    address_text='',
    address_new_user_id='" . $userid . "'";
    $dbh->query($sql);

    $sql = "UPDATE humo_connections SET
        connect_tree_id='" . $tree_id . "',
        connect_kind='" . safe_text_db($_GET['connect_kind']) . "',
        connect_sub_kind='" . safe_text_db($_GET["connect_sub_kind"]) . "',
        connect_connect_id='" . safe_text_db($_GET["connect_connect_id"]) . "',
        connect_item_id='" . $new_gedcomnumber . "',
        connect_new_user_id='" . $userid . "'
        WHERE connect_id='" . safe_text_db($_GET["connect_id"]) . "'";
    $dbh->query($sql);
}

// *** Change address ***
if (isset($_POST['change_address_id'])) {
    foreach ($_POST['change_address_id'] as $key => $value) {

        // *** Date for address is processed in connection table ***
        //address_date='".$editor_cls->date_process("address_date",$key)."',
        $address_shared = '';
        if (isset($_POST["address_shared_" . $key])) {
            $address_shared = '1';
        }

        // *** Only update if there are changed values! Otherwise all address_change variables will be changed... ***
        $address_changed = false;
        // Or: get old values out of the database. See editor notes (below in this script).
        if ($address_shared != $_POST["address_shared_old"][$key]) {
            $address_changed = true;
        }
        if ($_POST["address_address_" . $key] != $_POST["address_address_old"][$key]) {
            $address_changed = true;
        }
        if ($_POST["address_place_" . $key] != $_POST["address_place_old"][$key]) {
            $address_changed = true;
        }
        if ($_POST["address_text_" . $key] != $_POST["address_text_old"][$key]) {
            $address_changed = true;
        }
        if ($_POST["address_phone_" . $key] != $_POST["address_phone_old"][$key]) {
            $address_changed = true;
        }
        if ($_POST["address_zip_" . $key] != $_POST["address_zip_old"][$key]) {
            $address_changed = true;
        }

        if ($address_changed) {
            $sql = "UPDATE humo_addresses SET
                address_shared='" . $address_shared . "',
                address_address='" . $editor_cls->text_process($_POST["address_address_" . $key]) . "',
                address_place='" . $editor_cls->text_process($_POST["address_place_" . $key]) . "',
                address_text='" . $editor_cls->text_process($_POST["address_text_" . $key]) . "',
                address_phone='" . $editor_cls->text_process($_POST["address_phone_" . $key]) . "',
                address_zip='" . $editor_cls->text_process($_POST["address_zip_" . $key]) . "',
                address_changed_user_id='" . $userid . "'
            WHERE address_id='" . safe_text_db($_POST["change_address_id"][$key]) . "'";
            $dbh->query($sql);
        }
    }
}

// *** Remove all sources from an item ***
function remove_sources($tree_id, $connect_sub_kind, $connect_connect_id)
{
    global $dbh;
    /*
    // *** Remove (NON-SHARED) source by all connections ***
    $connect_source_sql="SELECT * FROM humo_connections LEFT JOIN humo_sources
        ON source_gedcomnr=connect_source_id
        WHERE connect_tree_id='".$tree_id."' AND source_tree_id='".$tree_id."'
        AND connect_sub_kind='".safe_text_db($connect_sub_kind)."'
        AND connect_connect_id='".safe_text_db($connect_connect_id)."'
        AND source_shared!='1'";
    //echo $connect_source_sql.'<br>';
    $connect_source_qry=$dbh->query($connect_source_sql);
    while($connect_sourceDb=$connect_source_qry->fetch(PDO::FETCH_OBJ)){

// TODO: ALWAYS REMOVE A CONNECTION, ONLY REMOVE SOURCE IF IT ISN'T SHARED

        $sql="DELETE FROM humo_sources WHERE source_id='".safe_text_db($connect_sourceDb->source_id)."'";
        $dbh->query($sql);

        $sql="DELETE FROM humo_connections WHERE connect_id='".safe_text_db($connect_sourceDb->connect_id)."'";
        $dbh->query($sql);
    }
    */
}

// *** Add event. $new_event=false/true ***
// *** This function is also copied to editor.php ***
// $event_date='event_date'
function add_event($new_event, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text, $multiple_rows = '')
{
    global $dbh, $tree_id, $editor_cls, $userid;

    // *** Generate new order number ***
    $event_order = 1;
    if (!$new_event) {
        $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='" . $event_connect_kind . "'
            AND event_connect_id='" . $event_connect_id . "'
            AND event_kind='" . $event_kind . "'
            ORDER BY event_order DESC LIMIT 0,1";
        $event_qry = $dbh->query($event_sql);
        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
        $event_order = 0;
        if (isset($eventDb->event_order)) {
            $event_order = $eventDb->event_order;
        }
        $event_order++;
    }

    $sql = "INSERT INTO humo_events SET
        event_tree_id='" . $tree_id . "',
        event_connect_kind='" . $event_connect_kind . "',
        event_connect_id='" . safe_text_db($event_connect_id) . "',
        event_kind='" . $event_kind . "',
        event_event='" . safe_text_db($event_event) . "',
        event_event_extra='',
        event_gedcom='" . safe_text_db($event_gedcom) . "',";
    if ($event_date) {
        $sql .= " event_date='" . $editor_cls->date_process($event_date, $multiple_rows) . "',";
    }
    $sql .= " event_place='" . safe_text_db($event_place) . "',
        event_text='" . safe_text_db($event_text) . "',
        event_order='" . $event_order . "',
        event_new_user_id='" . $userid . "'";
    $dbh->query($sql);
}
