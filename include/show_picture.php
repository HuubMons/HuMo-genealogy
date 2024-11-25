<?php
// *** Function to show media by person or by marriage ***
// *** Updated feb 2013, aug 2015, feb 2023. ***
function show_media($event_connect_kind, $event_connect_id)
{
    global $dbh, $db_functions, $tree_id, $user, $dataDb, $uri_path;
    global $sect, $screen_mode; // *** RTF Export ***
    global $data, $page;

    include_once(__DIR__ . "/../admin/include/media_inc.php");
    //$pcat_dirs = get_pcat_dirs();
    global $pcat_dirs;

    $templ_person = array(); // local version
    $process_text = '';
    $media_nr = 0;

    // *** Pictures/ media ***
    //if ($user['group_pictures'] == 'j' and $data["picture_presentation"] != 'hide') {
    if ($user['group_pictures'] == 'j' && isset($data["picture_presentation"]) && $data["picture_presentation"] != 'hide') {
        $tree_pict_path = $dataDb->tree_pict_path;

        // *** Use default folder: media ***
        if (substr($tree_pict_path, 0, 1) === '|') {
            $tree_pict_path = 'media/';
        }

        //TODO check PDF code
        if ($screen_mode == 'PDF') {
            $tree_pict_path = __DIR__ . '/../' . $tree_pict_path;
        }

        // *** Standard connected media by person and family ***
        $picture_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
            AND event_connect_kind='" . safe_text_db($event_connect_kind) . "'
            AND event_connect_id='" . safe_text_db($event_connect_id) . "'
            AND LEFT(event_kind,7)='picture'
            ORDER BY event_kind, event_order");
        while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
            $media_nr++;
            $media_event_id[$media_nr] = $pictureDb->event_id;
            $media_event_event[$media_nr] = $pictureDb->event_event;
            $media_event_date[$media_nr] = $pictureDb->event_date;
            $media_event_place[$media_nr] = $pictureDb->event_place;
            $media_event_text[$media_nr] = $pictureDb->event_text;
            // *** Remove last seperator ***
            if ($media_event_text[$media_nr] && substr(rtrim($media_event_text[$media_nr]), -1) === "|") {
                $media_event_text[$media_nr] = substr($media_event_text[$media_nr], 0, -1);
            }
            //$media_event_source[$media_nr]=$pictureDb->event_source;
        }

        // *** Search for all external connected objects by a person, family or source ***
        if ($event_connect_kind == 'person') {
            $connect_sql = $db_functions->get_connections_connect_id('person', 'pers_object', $event_connect_id);
        } elseif ($event_connect_kind == 'family') {
            $connect_sql = $db_functions->get_connections_connect_id('family', 'fam_object', $event_connect_id);
        } elseif ($event_connect_kind == 'source') {
            $connect_sql = $db_functions->get_connections_connect_id('source', 'source_object', $event_connect_id);
        }

        if ($event_connect_kind == 'person' || $event_connect_kind == 'family' || $event_connect_kind == 'source') {
            foreach ($connect_sql as $connectDb) {
                $picture_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_gedcomnr='" . safe_text_db($connectDb->connect_source_id) . "' AND event_kind='object'
                    ORDER BY event_order");
                while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                    $media_nr++;
                    $media_event_id[$media_nr] = $pictureDb->event_id;
                    $media_event_event[$media_nr] = $pictureDb->event_event;
                    $media_event_date[$media_nr] = $pictureDb->event_date;
                    $media_event_place[$media_nr] = $pictureDb->event_place;
                    $media_event_text[$media_nr] = $pictureDb->event_text;
                    // *** Remove last seperator ***
                    if (substr(rtrim($media_event_text[$media_nr]), -1) === "|") {
                        $media_event_text[$media_nr] = substr($media_event_text[$media_nr], 0, -1);
                    }
                    //$media_event_source[$media_nr]=$pictureDb->event_source;
                }
            }
        }

        // ******************
        // *** Show media ***
        // ******************
        if ($media_nr > 0) {
            if ($screen_mode == "RTF") {
                $process_text .= "\n";
            } else {
                $process_text .= '<br>';
            }
        }

        // ?? $picpath unused
        $picpath = $uri_path;

        for ($i = 1; $i < ($media_nr + 1); $i++) {
            $date_place = date_place($media_event_date[$i], $media_event_place[$i]);
            // *** If possible show a thumb ***

            // *** Don't use entities in a picture ***
            //$event_event = html_entity_decode($pictureDb->event_event, ENT_NOQUOTES, 'ISO-8859-15');
            $event_event = $media_event_event[$i];

            // in case subfolders are made for photobook categories and this was not already set in $picture_path, look there
            // (if the $picture_path is already set with subfolder this anyway gives false and so the $picture_path given will work)
            $temp_path = $tree_pict_path; // use temp path to modify

            // look in category subfolder if exists - lookup code moved to media_inc.php
            if (array_key_exists(substr($event_event, 0, 3), $pcat_dirs)) {
                $temp_path .= substr($event_event, 0, 2) . '/';
            }

            // *** In some cases the picture name must be converted to lower case ***
            if (file_exists($temp_path . strtolower($event_event))) {
                $event_event = strtolower($event_event);
            }
            // *** Show photo using the lightbox effect ***
            if (in_array(strtolower(pathinfo($event_event, PATHINFO_EXTENSION)), array('jpg', 'png', 'gif', 'bmp', 'tif'))) {

                $line_pos = 0;
                if ($media_event_text[$i]) {
                    $line_pos = strpos($media_event_text[$i], "|");
                }
                $title_txt = $media_event_text[$i];
                if ($line_pos > 0) {
                    $title_txt = substr($media_event_text[$i], 0, $line_pos);
                }

                // *** April 2021: using GLightbox ***
                // *** lightbox can't handle brackets etc so encode it. ("urlencode" doesn't work since it changes spaces to +, so we use rawurlencode)
                // *** But: reverse change of / character (if sub folders are used) ***
                $picture = '<a href="' . $temp_path . str_ireplace("%2F", "/", rawurlencode($event_event)) . '" class="glightbox3" data-gallery="gallery' . $event_connect_id . '" data-glightbox="description: .custom-desc' . $media_event_id[$i] . '">';

                // *** Need a class for multiple lines and HTML code in a text ***
                $picture .= '<div class="glightbox-desc custom-desc' . $media_event_id[$i] . '">';
                if ($date_place) {
                    $picture .= $date_place . '<br>';
                }
                $picture .= $title_txt . '</div>';

                $picture .= print_thumbnail($tree_pict_path, $event_event); // in media_inc.php. using default hight 120px
                $picture .= '</a>';

                $thumb_url = thumbnail_exists($temp_path, $event_event); //in media_inc.php: returns url of thumb or empty string
                if (!empty($thumb_url)) {
                    $templ_person["pic_path" . $i] = $thumb_url; //for the time being pdf only with thumbs
                } else {
                    $templ_person["pic_path" . $i] = $temp_path . $event_event; // use original picture instead
                }
                // *** Remove spaces ***
                $templ_person["pic_path" . $i] = trim($templ_person["pic_path" . $i]);
            } else {
                // other media formats not to be displayed with lightbox
                $picture = '<a href="' . $temp_path . $event_event . '" target="_blank">' . print_thumbnail($temp_path, $event_event) . '</a>';
            }


            // *** Show picture date and place ***
            $picture_text = '';
            if ($media_event_date[$i] || $media_event_place[$i]) {
                if ($screen_mode != 'RTF') {
                    $picture_text = $date_place . ' ';
                }
                $templ_person["pic_text" . $i] = $date_place;
            }

            // *** Show text by picture of little space ***
            if (isset($media_event_text[$i]) && $media_event_text[$i]) {
                if ($screen_mode != 'RTF') {
                    //$picture_text.=' '.str_replace("&", "&amp;", $media_event_text[$i]);
                    $picture_text .= ' ' . str_replace("&", "&amp;", process_text($media_event_text[$i]));
                }
                if (isset($templ_person["pic_text" . $i])) {
                    $templ_person["pic_text" . $i] .= ' ' . $media_event_text[$i];
                } else {
                    $templ_person["pic_text" . $i] = $media_event_text[$i];
                }
            }

            if ($screen_mode != 'RTF') {
                // Jan. 2024: Don't connect a source to a picture if source page is shown.
                if ($page != 'source') {
                    // *** Show source by picture ***
                    $source_array = '';
                    if ($event_connect_kind == 'person') {
                        $source_array = show_sources2("person", "pers_event_source", $media_event_id[$i]);
                    } else {
                        $source_array = show_sources2("family", "fam_event_source", $media_event_id[$i]);
                    }
                    if ($source_array) {
                        $picture_text .= $source_array['text'];
                    }
                }

                $process_text .= '<div class="photo">';
                $process_text .= $picture;

                if (!file_exists($temp_path . $event_event) && !file_exists($temp_path . strtolower($event_event))) {
                    $picture_text .= '<br><b>' . __('Missing image') . ':<br>' . $temp_path . $event_event . '</b>';
                }
                // *** Show text by picture ***
                if (isset($picture_text)) {
                    $process_text .= '<div class="phototext">' . $picture_text . '</div>';
                }
                $process_text .= '</div>' . "\n";
            }
        }

        if ($media_nr > 0) {
            $process_text .= '<br clear="All">';
            $templ_person["got_pics"] = 1;
        }
    }
    //return $process_text;
    $result[0] = $process_text;
    $result[1] = $templ_person; // local version with pic data
    return $result;
}
// unused function show_picture deleted