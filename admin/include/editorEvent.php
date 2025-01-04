<?php
class EditorEvent
{
    // *** Encode entire array (for picture array searches) ***
    //function utf8ize($d)
    //{
    //	foreach ($d as $key => $value) {
    //		//$d[$key] = utf8_encode($value); // deprecated in PHP 8.2.
    //      $d[$key] = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-2'); 
    //	}
    //	return $d;
    //}

    // *** Show event_kind text ***
    function event_text($event_kind, $event_gedcom = '', $event_event_extra = '')
    {
        if ($event_kind == 'picture') {
            $event_text = __('Picture/ Media');
        } elseif ($event_kind == 'profession') {
            $event_text = __('Profession');
        } elseif ($event_kind == 'event') {
            $event_text = __('Event');
        } elseif ($event_kind == 'ASSO' && $event_gedcom == 'CLERGY') {
            $event_text = __('clergy');
        } elseif ($event_kind == 'ASSO' && $event_gedcom == 'OFFICIATOR') {
            $event_text = __('officiator');
        } elseif ($event_kind == 'ASSO' && $event_gedcom == 'GODP') {
            $event_text = __('godfather');
        } elseif ($event_kind == 'ASSO' && $event_gedcom == 'OTHER') {
            $event_text = $event_event_extra;
            if ($event_text == 'informant') $event_text = __('informant');
            if ($event_text == 'funeral leader') $event_text = __('funeral leader');
        } elseif ($event_kind == 'ASSO') {
            $event_text = __('witness');
        } elseif ($event_kind == 'name') {
            $event_text = __('Name');
        } elseif ($event_kind == 'NPFX') {
            $event_text = __('Prefix');
        } elseif ($event_kind == 'NSFX') {
            $event_text = __('Suffix');
        } elseif ($event_kind == 'nobility') {
            $event_text = __('Title of Nobility');
        } elseif ($event_kind == 'title') {
            $event_text = __('Title');
        } elseif ($event_kind == 'adoption') {
            $event_text = __('Adoption');
        } elseif ($event_kind == 'lordship') {
            $event_text = __('Title of Lordship');
        } elseif ($event_kind == 'URL') {
            $event_text = __('URL/ Internet link');
        } elseif ($event_kind == 'person_colour_mark') {
            $event_text = __('Colour mark by person');
        } elseif ($event_kind == 'source_picture') {
            $event_text = __('Picture/ Media');
        } elseif ($event_kind == 'religion') {
            $event_text = __('Religion');
        } else {
            $event_text = ucfirst($event_kind);
        }
        return $event_text;
    }

    // *** Hide or show lines for editing, using <span> ***
    function hide_show_start($data_listDb, $alternative_text = '')
    {
        // *** Use hideshow to show and hide the editor lines ***
        $text = '';
        $hideshow = '9000' . $data_listDb->event_id;
        $display = ' display:none;';
        $event_event = $data_listDb->event_event;
        //if (!$data_listDb->event_event and !$data_listDb->event_date and !$data_listDb->event_place and !$data_listDb->event_text) {
        if (!$data_listDb->event_event && !$data_listDb->event_connect_id2 && !$data_listDb->event_date && !$data_listDb->event_place && !$data_listDb->event_text) {
            //$event_event=__('EMPTY LINE');
            $display = '';
        }
        if ($alternative_text) {
            $event_event = $alternative_text;
        }

        // *** Also show date and place ***
        //if ($data_listDb->event_date) $event_event.=', '.date_place($data_listDb->event_date,$data_listDb->event_place);
        if ($data_listDb->event_date) {
            $event_event .= ', ' . hideshow_date_place($data_listDb->event_date, $data_listDb->event_place);
        }

        if ($event_event || $data_listDb->event_text || $data_listDb->event_kind == 'picture') {
            echo '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');">' . $event_event;
            if ($data_listDb->event_text) {
                echo ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
            }
            echo '</span>';

            // *** Could be used to connect a picture in a text field (Geneanet doesnt have constant GEDCOM numbers or an own text field) ***
            if ($data_listDb->event_kind == 'picture') {
                echo '&nbsp;<span style="font-size:smaller;">' . __('ID') . ': ' . $data_listDb->event_id . '</span>';
            }

            echo '<br>';
        }

        echo '<span class="humo row' . $hideshow . '" style="margin-left:0px;' . $display . '">';
        return $text;
    }

    // *** Show events ***
    // *** REMARK: queries can be found in editor_inc.php! ***
    // *** REMARK: also used in source editor to add a photo ***
    function show_event($event_connect_kind, $event_connect_id, $event_kind)
    {
        global $dbh, $tree_id, $page, $field_date, $field_place, $field_text, $field_text_medium;
        global $editor_cls, $path_prefix, $tree_pict_path, $humo_option, $field_popup;
        global $db_functions;

        include_once(__DIR__ . "/../include/media_inc.php");
        include_once(__DIR__ . '/../../include/give_media_path.php');
        include_once(__DIR__ . "/../../include/showMedia.php");
        $showMedia = new showMedia();

        $text = '';
        if ($event_kind == 'picture' || $event_kind == 'marriage_picture') {
            $picture_array = array();
            // *** Picture list for selecting pictures ***
            $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $tree_id . "'");
            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
            $tree_pict_path = $dataDb->tree_pict_path;
            if (substr($tree_pict_path, 0, 1) === '|') {
                $tree_pict_path = 'media/';
            }
            $dir = $path_prefix . $tree_pict_path;
            if (file_exists($dir)) {
                $dh  = opendir($dir);
                while (false !== ($filename = readdir($dh))) {
                    if (substr($filename, 0, 6) !== 'thumb_' && $filename !== '.' && $filename !== '..' && !is_dir($dir . $filename)) {
                        $picture_array[] = $filename;
                    }
                }
                closedir($dh);
            }
            @usort($picture_array, 'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11

            $is_cat = false; // flags there are category files (for use later on)
            $picture_array2 = array(); // declare, otherwise if not used gives error
            // if subfolders exist for category files, list those too
            $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
            if ($temp->rowCount()) { // there is a category table
                $catg = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
                if ($catg->rowCount()) {
                    while ($catDb = $catg->fetch(PDO::FETCH_OBJ)) {
                        if (is_dir($dir . substr($catDb->photocat_prefix, 0, 2))) {  // there is a subfolder for this prefix
                            $dh  = opendir($dir . substr($catDb->photocat_prefix, 0, 2));
                            while (false !== ($filename = readdir($dh))) {
                                if (substr($filename, 0, 6) !== 'thumb_' && $filename !== '.' && $filename !== '..') {
                                    $picture_array2[] = $filename;
                                    $is_cat = true;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
            }
            // *** Order pictures by alphabet ***
            @usort($picture_array2, 'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
            $picture_array = array_merge($picture_array, $picture_array2);
            //@sort($picture_array);  
            //@usort($picture_array,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
            $nr_pictures = count($picture_array);
        }

        // *** Change line colour ***
        // TODO: check, probably no longer needed.
        $change_bg_colour = ' class="humo_color3"';

        // *** Show all events EXCEPT for events already processed by person data (profession etc.) ***

        // Don't show Brit Mila and/or Bar Mitzva if user set them to be displayed among person data
        $hebtext = '';
        if ($humo_option['admin_brit'] == "y") {
            $hebtext .= " AND (event_gedcom!='_BRTM'  OR event_gedcom IS NULL) ";
        }
        if ($humo_option['admin_barm'] == "y") {
            $hebtext .= " AND ((event_gedcom!='BARM' AND event_gedcom!='BASM') OR event_gedcom IS NULL) ";
        }

        if ($event_kind == 'person') {
            // *** Filter several events, allready shown in seperate lines in editor ***
            $qry = "SELECT * FROM humo_events
                WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person'
                AND event_connect_id='" . $event_connect_id . "'
                AND event_kind NOT IN ('name','NPFX','NSFX','nobility','title','lordship','birth_declaration','ASSO',
                'death_declaration','profession','religion','picture','witness')
                " . $hebtext . "
                ORDER BY event_kind, event_order";
        } elseif ($event_kind == 'name') {
            $hebclause = "";
            if ($humo_option['admin_hebname'] == 'y') {
                $hebclause = " AND event_gedcom!='_HEBN' ";
            }
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='name' " . $hebclause . "ORDER BY event_order";
        } elseif ($event_kind == 'NPFX') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='NPFX' ORDER BY event_order";
        } elseif ($event_kind == 'NSFX') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='NSFX' ORDER BY event_order";
        } elseif ($event_kind == 'nobility') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='nobility' ORDER BY event_order";
        } elseif ($event_kind == 'title') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='title' ORDER BY event_order";
        } elseif ($event_kind == 'lordship') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='lordship' ORDER BY event_order";
            //} elseif ($event_connect_kind == 'birth_declaration') {
            //    $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='birth_declaration' AND event_connect_id='" . $event_connect_id . "' AND event_kind='witness' ORDER BY event_order";

        } elseif ($event_kind == 'ASSO' && $event_connect_kind == 'CHR') {
            // ADDED oct. 2024
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='CHR' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_order";
        } elseif ($event_connect_kind == 'birth_declaration') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='birth_declaration' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_order";
        } elseif ($event_kind == 'ASSO' && $event_connect_kind == 'BURI') {
            // ADDED oct. 2024
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='BURI' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_order";
        } elseif ($event_connect_kind == 'death_declaration') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='death_declaration' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_order";
        } elseif ($event_kind == 'profession') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='profession' ORDER BY event_order";
        } elseif ($event_kind == 'religion') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='religion' ORDER BY event_order";
        } elseif ($event_kind == 'picture') {
            $search_picture = "";
            $searchpic = "";
            if (isset($_POST['searchpic'])) {
                $search_picture = $_POST['searchpic'];
            }
            if ($search_picture != "") {
                $searchpic = " AND event_event LIKE '%" . $search_picture . "%' ";
            }
            $qry = "SELECT * FROM humo_events
                WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND
                event_kind='picture' " . $searchpic . " ORDER BY event_order";
        } elseif ($event_kind == 'family') {
            $qry = "SELECT * FROM humo_events 
                WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='family' AND event_connect_id='" . $event_connect_id . "'
                AND event_kind!='ASSO'
                AND event_kind!='picture'
                ORDER BY event_kind, event_order";
        } elseif ($event_connect_kind == 'MARR' && $event_kind == 'ASSO') {
            // TODO: remove this query
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='MARR' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_kind, event_order";
        } elseif ($event_connect_kind == 'MARR_REL' && $event_kind == 'ASSO') {
            // TODO: remove this query
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='MARR_REL' AND event_connect_id='" . $event_connect_id . "' AND event_kind='ASSO' ORDER BY event_kind, event_order";
        } elseif ($event_kind == 'marriage_picture') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='family' AND event_connect_id='" . $event_connect_id . "' AND event_kind='picture' ORDER BY event_order";
        } elseif ($event_kind == 'source_picture') {
            $qry = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='source' AND event_connect_id='" . $event_connect_id . "' AND event_kind='picture' ORDER BY event_order";
        }

        $data_list_qry = $dbh->query($qry);

        $show_event_add = false;
        $count = $data_list_qry->rowCount();
        if ($count > 0) {
            $show_event_add = true;
        }


        // *** Show events by person ***
        if ($event_kind == 'person') {
            //$text.='<tr><td style="border-right:0px;"><a name="event_person_link"></a><a href="#event_person_link" onclick="hideShow(51);"><span id="hideshowlink51">'.__('[+]').'</span></a> '.__('Events').'</td>';
            $link = 'event_person_link';
?>
            <tr class="table_header_large" id="event_person_link">
                <td><?= __('Events'); ?></td>
                <td colspan="2">

                    <!-- Add person event -->
                    <div class="row">
                        <div class="col-4">
                            <select size="1" name="event_kind" class="form-select form-select-sm">
                                <option value="event"><?= __('Event'); ?></option>
                                <option value="adoption"><?= __('Adoption'); ?></option>
                                <option value="URL"><?= __('URL/ Internet link'); ?></option>
                                <option value="person_colour_mark"><?= __('Colour mark by person'); ?></option>
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="submit" name="person_event_add" value="<?= __('Add event'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>

                        <div class="col-1">
                            <!-- Help popup -->
                            <?php $rtlmarker = "ltr"; ?>
                            &nbsp;
                            <div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                                <a href="#" style="display:inline" onmouseover="mopen(event,'help_event_person',0,0)" onmouseout="mclosetime()">
                                    <img src="../images/help.png" height="16" width="16">
                                </a>
                                <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_event_person" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                    <?= __('For items like:') . ' ' . __('Event') . ', ' . __('baptized as child') . ', ' . __('depart') . ' ' . __('etc.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>

        <?php
        }

        // *** Show events by family ***
        if ($event_kind == 'family') {
            $link = 'event_family_link';
        ?>
            <tr class="table_header_large" id="event_family_link">
                <td><?= __('Events'); ?></td>
                <td colspan="2">

                    <div class="row">
                        <div class="col-4">
                            <select size="1" name="event_kind" class="form-select form-select-sm">
                                <option value="event"><?= __('Event'); ?></option>
                                <option value="URL"><?= __('URL/ Internet link'); ?></option>
                            </select>
                        </div>

                        <div class="col-3">
                            <input type="submit" name="marriage_event_add" value="<?= __('Add event'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>

                        <div class="col-3">
                            <!-- Help popup -->
                            <?php $rtlmarker = "ltr"; ?>
                            &nbsp;<div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                                <a href="#" style="display:inline" onmouseover="mopen(event,'help_event_family',0,0)" onmouseout="mclosetime()">
                                    <img src="../images/help.png" height="16" width="16">
                                </a>
                                <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_event_family" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                    <?= __('For items like:') . ' ' . __('Event') . ', ' . __('Marriage contract') . ', ' . __('Marriage license') . ', ' . __('etc.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        <?php
        }

        // *** Show name by person ***
        if ($event_kind == 'name') {
            // *** Nickname, alias, adopted name, hebrew name, etc. ***
            // *** Remark: in editor_inc.php a check is done for event_event_name, so this will also be saved if "Save" is clicked ***
            $link = 'name';
        ?>
            <tr class="table_header_large">
                <td></td>
                <td colspan="2">

                    <div class="row">
                        <div class="col-md-4">
                            <select size="1" name="event_gedcom_add" id="event_gedcom_add" class="form-select form-select-sm">
                                <?php event_selection(''); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="event_event_name" id="event_event_name" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="submit" name="event_add_name" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>
                    </div>

                    <!-- Test to add line inside table -->
                    <!--
                    <button type="button" onclick="myFunction()">Test</button>

                    //https://www.w3schools.com/jsref/met_table_insertrow.asp
                    <script>
                    function myFunction() {
                    var table = document.getElementById("table_editor");
                    var row = table.insertRow(8);

                    //APEND!!!!!!
                    //var row = table.insertRow(-1);
                    //var cell = row.insertCell(-1);

                    var cell1 = row.insertCell(0);
                    var cell2 = row.insertCell(1);
                    var cell3 = row.insertCell(2);
                    var cell4 = row.insertCell(3);
                    //row.id = "xyz"; //you can add your id like this

                    var str = document.getElementById("event_gedcom_add");
                    var stra = str.value;

                    var str2 = document.getElementById("event_event_name");
                    var str2a = str2.value;


                    // https://www.w3schools.com/js/tryit.asp?filename=tryjs_ajax_database
                    const xhttp = new XMLHttpRequest();
                    xhttp.onload = function() {
                        cell2.innerHTML = this.responseText;
                    }
                    xhttp.open("GET", "include/editor_ajax.php?event_gedcom_add="+stra+"&event_event_name="+str2a);
                    xhttp.send();

                    cell1.innerHTML = "NEW CELL1";
                    //cell2.innerHTML = "NEW CELL2";

                    var event_gedcom_add = document.getElementById("event_gedcom_add");
                    var value = event_gedcom_add.value;
                    cell3.innerHTML = value;

                    cell4.innerHTML = "NEW CELL4";
                    }
                    </script>
                    -->

                </td>
            </tr>

        <?php
        }

        // *** Show profession by person ***
        if ($event_kind == 'profession') {
            $link = 'profession';
        ?>
            <tr class="table_header_large" id="profession">
                <td style="border-right:0px;">
                    <b><?= __('Profession'); ?></b>
                </td>
                <td colspan="2">
                    <?php
                    // *** Skip for newly added person ***
                    // *** Remark: in editor_inc.php a check is done for event_event_profession, so this will also be saved if "Save" is clicked ***
                    if (!isset($_GET['add_person'])) {
                    ?>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="event_event_profession" value="" size="35" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <input type="submit" name="event_add_profession" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php
        }

        // *** Show religion by person ***
        if ($event_kind == 'religion') {
            $link = 'religion';
        ?>
            <tr class="table_header_large" id="religion">
                <td style="border-right:0px;"><?= __('Religion'); ?></td>
                <td colspan="2">
                    <?php
                    // *** Skip for newly added person ***
                    if (!isset($_GET['add_person'])) {
                        // *** Remark: in editor_inc.php a check is done for event_event_religion, so this will also be saved if "Save" is clicked ***
                    ?>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="event_event_religion" value="" size="35" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <input type="submit" name="event_add_religion" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php
        }

        // *** Show pictures by person, family and (shared) source ***
        if ($event_kind == 'picture' || $event_kind == 'marriage_picture' || $event_kind == 'source_picture') {
            $link = 'picture';
        ?>
            <tr class="table_header_large" id="picture">
                <td style="border-right:0px;">
                    <b><?= __('Picture/ Media'); ?></b>
                </td>
                <td colspan="2">
                    <?php
                    if ($event_kind == 'picture') {
                        echo ' <input type="submit" name="add_picture" value="' . __('Add') . '" class="btn btn-sm btn-outline-primary">';
                    } elseif ($event_kind == 'marriage_picture') {
                        echo ' <input type="submit" name="add_marriage_picture" value="' . __('Add') . '" class="btn btn-sm btn-outline-primary">';
                    } elseif ($event_kind == 'source_picture') {
                        echo ' <input type="submit" name="add_source_picture" value="' . __('Add') . '" class="btn btn-sm btn-outline-primary">';
                    }

                    //TEST
                    /*
                    // *** JUNE 2021: disabled drag and drop to get a clearer editor page ***
                    if ($count>1) { echo "&nbsp;&nbsp;".__('(Drag pictures to change display order)'); }
                    echo '&nbsp;&nbsp;&nbsp;<a href="index.php?page=thumbs">'.__('Pictures/ create thumbnails').'.</a>';
                    echo '<ul id="sortable_pic" class="sortable_pic handle_pic">';
                        echo '<li id="xxxxxxxxxxx" class="mediamove">';
                        echo '<img src="images/drag-icon.gif" style="float:left;vertical-align:top;height:16px;">';
                        echo '&nbsp;Test<br>';
                        echo '<img src="../../humo-gen-afb/mons/thumb_huub_linda_mons.jpg" style="height:80px;">';
                        echo '</li>';

                        echo '<li id="xxxxxxxxxxx" class="mediamove">';
                        echo '<img src="images/drag-icon.gif" style="float:left;vertical-align:top;height:16px;">';
                        echo '&nbsp;Test<br>';
                        echo '<img src="../../humo-gen-afb/mons/thumb_huub_linda_mons.jpg" style="height:80px;">';
                        echo '</li>';
                    echo '</ul>';
                    */

                    /*
                    // *** JUNE 2021: disabled drag and drop to get a clearer editor page ***
                    if ($count>1) { $text.="&nbsp;&nbsp;".__('(Drag pictures to change display order)'); }
                    $text.='&nbsp;&nbsp;&nbsp;<a href="index.php?page=thumbs">'.__('Pictures/ create thumbnails').'.</a>';

                    $text.='<ul id="sortable_pic" class="sortable_pic handle_pic" style="width:auto">';
                    while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
                        $text.='<li style="word-wrap:break-word;hight:auto;" id="'.$data_listDb->event_id.'" class="mediamove">';
                        $text.='<div style="position:relative">';
                        if ($count>1) {
                            $text.='<div style="position:absolute;top:0;left:0">';
                            $show_image= '<img src="images/drag-icon.gif" style="float:left;vertical-align:top;height:16px;">';
                            $text.=$show_image;
                            $text.='</div>';
                        }
                        $text.='<div style="overflow:hidden">';
                        $tree_pict_path2 = $tree_pict_path;  // we change it only if category subfolders exist
                        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
                        if($temp->rowCount()) {  // there is a category table 
                            $catgr = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
                            if($catgr->rowCount()) { 
                                while($catDb = $catgr->fetch(PDO::FETCH_OBJ)) {  
                                    if(substr($data_listDb->event_event,0,3)==$catDb->photocat_prefix AND is_dir($path_prefix.$tree_pict_path2.substr($data_listDb->event_event,0,2)))  {   // there is a subfolder of this prefix
                                        $tree_pict_path2 = $tree_pict_path2.substr($data_listDb->event_event,0,2).'/';  // look in that subfolder
                                    }
                                }
                            }
                        }

                        $thumb_prefix='';
                        if (file_exists($path_prefix.$tree_pict_path2.'thumb_'.$data_listDb->event_event)){ $thumb_prefix='thumb_'; }
                        $extensions_check=substr($path_prefix.$tree_pict_path2.$data_listDb->event_event,-3,3);
                        if($extensions_check=="jpg" OR $extensions_check=="gif" OR $extensions_check=="png" OR $extensions_check=="bmp") {
                            if (file_exists($path_prefix.$tree_pict_path2.$thumb_prefix.$data_listDb->event_event))
                                $show_image= '<img src="'.$path_prefix.$tree_pict_path2.$thumb_prefix.$data_listDb->event_event.'" style="height:80px;">';
                            else
                                $show_image= '<img src="../images/thumb_missing-image.jpg" height="60">';
                            if (!$data_listDb->event_event) $show_image= '&nbsp;<img src="../images/thumb_missing-image.jpg" height="60">';
                            $text.=$show_image;
                        }
                        else {
                            $ext = substr($data_listDb->event_event,-3,3);
                            if($ext=="tif" OR $ext=="iff") { $text.='<span style="font-size:80%">['.__('Format not supported')."]</span>"; }
                            elseif($ext=="pdf") { $text.='<img src="../images/pdf.jpeg" style="width:30px;height:30px;">';}
                            elseif($ext=="doc" OR $ext=="ocx") { $text.='<img src="../images/msdoc.gif" style="width:30px;height:30px;">';}
                            elseif($ext=="avi" OR $ext=="wmv" OR $ext=="mpg" OR $ext=="mp4" OR $ext=="mov") { $text.='<img src="../images/video-file.png" style="width:30px;height:30px;">'; }
                            elseif($ext=="wma" OR $ext=="wav" OR $ext=="mp3" OR $ext=="mid" OR $ext=="ram" OR $ext==".ra" ) { $text.='<img src="../images/audio.gif" style="width:30px;height:30px;">';}

                            $text.='<br><span style="font-size:85%">'.$data_listDb->event_event.'</span>';
                        }
                        // *** No picture selected yet, show dummy picture ***
                        if (!$data_listDb->event_event) $text.='<img src="../images/thumb_missing-image.jpg" height="60">';
                        $text.='</div>';
                        $text.='</div>';
                        $text.='</li>';
                    } 
                    $text.='</ul>';
                    */



                    // MAY 2023: convert OBJECTS to standard images.
                    // DEC 2015: OLD: FOR NOW, ONLY SHOW NUMBER OF PICTURE-OBJECTS.
                    // *** Search for all external connected objects by a person or a family ***
                    if ($event_connect_kind == 'person') {
                        $connect_qry = "SELECT * FROM humo_connections
                            WHERE connect_tree_id='" . $tree_id . "'
                            AND connect_sub_kind='pers_object' AND connect_connect_id='" . $event_connect_id . "'
                            ORDER BY connect_order";
                    } elseif ($event_connect_kind == 'family') {
                        $connect_qry = "SELECT * FROM humo_connections
                            WHERE connect_tree_id='" . $tree_id . "'
                            AND connect_sub_kind='fam_object' AND connect_connect_id='" . $event_connect_id . "'
                            ORDER BY connect_order";
                    }
                    if ($event_connect_kind == 'person' || $event_connect_kind == 'family') {
                        $event_order = 1;

                        $connect_sql = $dbh->query($connect_qry);
                        while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
                            $picture_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                                AND event_gedcomnr='" . $connectDb->connect_source_id . "' AND event_kind='object'
                                ORDER BY event_order");
                            while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                                // *** Check if humo_events was allready updated... => THEN OBJECT IS USED MULTIPLE TIMES ***
                                // *** Maybe this isn't used, but just in case created this insert script ***
                                if ($pictureDb->event_connect_kind || $pictureDb->event_connect_id) {
                                    //	Don't use UPDATE but create a new EVENT!!
                                    $sql = "INSERT INTO humo_events SET
                                        event_tree_id='" . $pictureDb->event_tree_id . "',
                                        event_connect_kind='" . $event_connect_kind . "',
                                        event_connect_id='" . safe_text_db($event_connect_id) . "',
                                        event_kind='picture',
                                        event_event='" . $pictureDb->event_event . "',
                                        event_gedcom='',
                                        event_order='" . $event_order . "'";
                                    $event_order++;
                                    $dbh->query($sql);
                                } else {
                                    // *** Convert OBJECTS to standard images ***
                                    $sql = "UPDATE humo_events SET
                                        event_connect_kind='" . $event_connect_kind . "',
                                        event_connect_id='" . safe_text_db($event_connect_id) . "',
                                        event_kind='picture',
                                        event_gedcom='',
                                        event_order='" . $event_order . "'
                                        WHERE event_id='" . $pictureDb->event_id . "'";
                                    $dbh->query($sql);
                                    $event_order++;
                                    // *** Remove connection ***
                                    $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
                                    $dbh->query($sql);
                                }
                            }
                        }
                    }

                    /*
                    $text.= '
                    <script>
                    $(\'#sortable_pic\').sortable().bind(\'sortupdate\', function() {
                        var mediastring = ""; 
                        var media_arr = document.getElementsByClassName("mediamove"); 
                        for (var z = 0; z < media_arr.length; z++) { 
                            // create the new order after dragging to store in database with ajax
                            mediastring = mediastring + media_arr[z].id + ";"; 
                            // change the order numbers of the pics in the pulldown (that was generated before the drag
                            // so that if one presses on delete before refresh the right pic will be deleted !!
                        }
                        mediastring = mediastring.substring(0, mediastring.length-1); // take off last ;

                        var parnode = document.getElementById(\'pic_main_\' + media_arr[0].id).parentNode; 
                        //var picdomclass = document.getElementsByClassName("pic_row2");
                        //var nextnode = picdomclass[(picdomclass.length)-1].nextSibling;
                        var nextnode = document.getElementById(\'pic_main_\' + media_arr[1].id); 

                        for(var d=media_arr.length-1; d >=0 ; d--) {
                            //parnode.insertBefore(document.getElementById(\'pic_row2_\' + media_arr[d].id),nextnode);
                            //nextnode = document.getElementById(\'pic_row2_\' + media_arr[d].id);

                            //parnode.insertBefore(document.getElementById(\'pic_row1_\' + media_arr[d].id),nextnode);
                            //nextnode = document.getElementById(\'pic_row1_\' + media_arr[d].id);

                            parnode.insertBefore(document.getElementById(\'pic_main_\' + media_arr[d].id),nextnode);
                            nextnode = document.getElementById(\'pic_main_\' + media_arr[d].id);  
                        }

                        $.ajax({ 
                            url: "include/drag.php?drag_kind=media&mediastring=" + mediastring ,
                            success: function(data){
                            } ,
                            error: function (xhr, ajaxOptions, thrownError) {
                                alert(xhr.status);
                                alert(thrownError);
                            }
                        });
                    });
                    </script>';
                    */
                    ?>

                </td>
            </tr>
            <?php
        }

        if (!isset($_GET['add_person'])) {
            $data_list_qry = $dbh->query($qry);
            while ($data_listDb = $data_list_qry->fetch(PDO::FETCH_OBJ)) {
                echo '<input type="hidden" name="event_id[' . $data_listDb->event_id . ']" value="' . $data_listDb->event_id . '">';

                $expand_link = '';
                $internal_link = '#';
                if ($event_kind == 'person') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row51" name="row51"';
                    $expand_link = '';
                    $change_bg_colour = '';
                    $internal_link = '#event_person_link';
                }
                if ($event_kind == 'family') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row52" name="row52"';
                    $expand_link = '';
                    $change_bg_colour = '';
                    $internal_link = '#event_family_link';
                }
                if ($event_kind == 'name') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                    $internal_link = '#';
                }
                if ($event_kind == 'NPFX') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                }
                if ($event_kind == 'NSFX') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                }
                if ($event_kind == 'nobility') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                }
                if ($event_kind == 'title') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                }
                if ($event_kind == 'lordship') {
                    //$change_bg_colour=' class="humo_color"';
                    //$expand_link=' style="display:none;" class="row1" name="row1"';
                    $expand_link = '';
                    $change_bg_colour = '';
                }
                //if ($event_kind == 'birth_decl_witness') {
                if ($event_connect_kind == 'birth_declaration') {
                    //$expand_link=' style="display:none;" class="row2 humo_color" name="row2"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="birth_decl_witness"';
                    }
                    //$change_bg_colour='';
                    $change_bg_colour = ' class="humo_color"';
                }
                //if ($event_kind == 'baptism_witness') {
                if ($event_connect_kind == 'BAPT') {
                    //$expand_link=' style="display:none;" class="row3" name="row3"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="baptism_witness"';
                    }
                    //$change_bg_colour=' class="humo_color"';
                    $change_bg_colour = '';
                }
                //if ($event_kind == 'death_decl_witness') {
                if ($event_kind == 'death_declaration') {
                    //$expand_link=' style="display:none;" class="row4 humo_color" name="row4"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="death_decl_witness"';
                    }
                    //$change_bg_colour='';
                    $change_bg_colour = ' class="humo_color"';
                }
                //if ($event_kind == 'burial_witness') {
                if ($event_connect_kind == 'BURI') {
                    //$expand_link=' style="display:none;" class="row5" name="row5"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="burial_witness"';
                    }
                    //$change_bg_colour=' class="humo_color"';
                    $change_bg_colour = '';
                }
                if ($event_kind == 'profession') {
                    //$expand_link=' style="display:none;" class="row13" name="row13"';
                    $expand_link = '';
                    //$change_bg_colour=' class="humo_color"';
                    $change_bg_colour = '';
                    $internal_link = '#profession';
                }
                if ($event_kind == 'religion') {
                    //$expand_link=' style="display:none;" class="row13" name="row13"';
                    $expand_link = '';
                    //$change_bg_colour=' class="humo_color"';
                    $change_bg_colour = '';
                    $internal_link = '#religion';
                }
                if ($event_kind == 'picture' || $event_kind == 'marriage_picture' || $event_kind == 'source_picture') {
                    //$expand_link=' style="display:none;" id="pic_main_'.$data_listDb->event_id.'" class="pic_main row53 humo_color" name="row53"';
                    $expand_link = '';
                    //$change_bg_colour='';
                    $change_bg_colour = ' class="humo_color"';
                    $internal_link = '#picture';
                }
                //if ($event_kind == 'marriage_witness') {
                if ($event_connect_kind == 'MARR') {
                    //$expand_link=' style="display:none;" class="row8 humo_color" name="row8"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="marriage_witness"';
                    }
                    //$change_bg_colour='';
                    $change_bg_colour = ' class="humo_color"';
                    $internal_link = '#event_family_link';
                }
                //if ($event_kind == 'marriage_witness_rel') {
                if ($event_kind == 'MARR_REL') {
                    //$expand_link=' style="display:none;" class="row10 humo_color" name="row10"';
                    $expand_link = '';
                    if ($data_listDb->event_order == '1') {
                        $expand_link = ' id="marriage_witness_rel"';
                    }
                    //$change_bg_colour='';
                    $change_bg_colour = ' class="humo_color"';
                    $internal_link = '#event_family_link';
                }
            ?>

                <tr <?= $expand_link . $change_bg_colour; ?>>
                    <td>
                        <?php
                        // *** Show name of event and [+] link ***
                        //$text.='&nbsp;&nbsp;&nbsp;<a href="'.$internal_link.'" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a>';
                        //$text.=' #'.$data_listDb->event_order;
                        $newpers = "";
                        if (isset($_GET['add_person'])) {
                            $newpers = "&amp;add_person=1";
                        }
                        ?>
                        <a href="index.php?page=<?= $page . $newpers; ?>&amp;event_connect_kind=<?= $data_listDb->event_connect_kind; ?>&amp;event_kind=<?= $data_listDb->event_kind; ?>&amp;event_drop=<?= $data_listDb->event_order; ?><?= $event_kind == 'source_picture' ? '&amp;source_id=' . $data_listDb->event_connect_id : ''; ?>">
                            <img src="images/button_drop.png" border="0" alt="down">
                        </a>

                        <?php
                        // *** Count number of events ***
                        if ($event_connect_kind == 'person') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'CHR') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='CHR' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'BURI') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='BURI' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'birth_declaration') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='birth_declaration' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'death_declaration') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='death_declaration' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'family') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='family' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'MARR') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='MARR' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'MARR_REL') {
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='MARR_REL' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        } elseif ($event_connect_kind == 'source') {
                            // *** Edit picture by source in seperate source page ***
                            $count_event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='source' AND event_connect_id='" . $event_connect_id . "' AND event_kind='" . $data_listDb->event_kind . "'");
                        }
                        $count = $count_event->rowCount();

                        // *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
                        if ($data_listDb->event_order < $count) {
                        ?>
                            <a href="index.php?page=<?= $page; ?>&amp;event_down=<?= $data_listDb->event_order; ?>&amp;event_connect_kind=<?= $data_listDb->event_connect_kind; ?>&amp;event_kind=<?= $data_listDb->event_kind; ?><?= $event_kind == 'source_picture' ? '&amp;source_id=' . $data_listDb->event_connect_id : ''; ?>&amp;dummy=<?= $data_listDb->event_id . $internal_link; ?>">
                                <img src="images/arrow_down.gif" border="0" alt="down">
                            </a>
                        <?php
                        } else {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }

                        // *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
                        if ($data_listDb->event_order > 1) {
                        ?>
                            <a href="index.php?page=<?= $page; ?>&amp;event_up=<?= $data_listDb->event_order; ?>&amp;event_connect_kind=<?= $data_listDb->event_connect_kind; ?>&amp;event_kind=<?= $data_listDb->event_kind; ?><?= $event_kind == 'source_picture' ? '&amp;source_id=' . $data_listDb->event_connect_id : ''; ?>&amp;dummy=<?= $data_listDb->event_id . $internal_link; ?>">
                                <img src="images/arrow_up.gif" border="0" alt="down">
                            </a>
                        <?php
                        } else {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        ?>
                    </td>

                    <td colspan=" 2">
                        <?php
                        // *** Check number of sources and valid connected sources ***
                        $check_sources_text = ''; // For source editor.
                        if ($event_connect_kind == 'person') {
                            $check_sources_text = check_sources('person', 'pers_event_source', $data_listDb->event_id);
                        } elseif ($event_connect_kind == 'family') {
                            $check_sources_text = check_sources('person', 'fam_event_source', $data_listDb->event_id);
                        }

                        // *** Witness and declaration persons ***
                        if (
                            $data_listDb->event_kind == 'ASSO' || $data_listDb->event_connect_kind == 'birth_declaration' || $data_listDb->event_connect_kind == 'death_declaration'
                        ) {
                            // *** Hide or show editor fields ***
                            if ($data_listDb->event_connect_id2) {
                                //$witness_name = show_person($data_listDb->event_connect_id2, $gedcom_date = false, $show_link = false);
                                $witness_name = show_person($data_listDb->event_connect_id2, false, false);
                            } else {
                                $witness_name = $data_listDb->event_event;
                            }

                            if ($check_sources_text) {
                                $witness_name .= ' ' . $check_sources_text;
                            }

                            $event_text = $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom);
                            $person_item = 'person_witness';
                            if ($event_connect_kind == 'family') {
                                $person_item = 'marriage_witness';
                            }
                            // *** Orange items if no witness name is selected or added in text ***
                            $style = '';
                            if (!$data_listDb->event_event && !$data_listDb->event_connect_id2) {
                                $style = 'style="background-color:#FFAA80"';
                            }
                        ?>

                            <!-- Show name of item -->
                            <?= $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom, $data_listDb->event_event_extra); ?>:

                            <!-- Hide/show line (start <span> to hide edit line) -->
                            <?= $this->hide_show_start($data_listDb, $witness_name); ?>


                            <div class="row mb-1">
                                <!-- <label for="event" class="col-md-3 col-form-label"><?= ucfirst($this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom)); ?></label> -->
                                <div class="col-md-3"></div>
                                <label for="event" class="col-md-9 col-form-label"><?= __('Select GEDCOM number or type name of person:'); ?></label>
                            </div>

                            <div class="row mb-1">
                                <!-- <label for="event" class="col-md-3 col-form-label"><?= ucfirst($this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom)); ?></label> -->
                                <!-- <div class="col-md-3"></div> -->
                                <label for="event" class="col-md-3 col-form-label"><?= __('GEDCOM number (ID)'); ?></label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input <?= $style; ?> type="text" name="event_connect_id2<?= $data_listDb->event_id; ?>" value="<?= $data_listDb->event_connect_id2; ?>" size="17" class="form-control form-control-sm">
                                        &nbsp;<a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=<?= $person_item; ?>&event_row=<?= $data_listDb->event_id; ?>&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                    </div>
                                </div>
                            </div>

                            <!--
                            <div class="row">
                                <div class="col-3"></div>
                                <label for="event" class="col-md-3 col-form-label"><b><?= __('or'); ?>:</b></label>
                            </div>
                            -->

                            <div class="row mb-2">
                                <!-- <label for="event" class="col-md-3 col-form-label"><b><?= __('or'); ?>:</b></label> -->
                                <!-- <div class="col-md-3"></div> -->
                                <label for="event" class="col-md-3 col-form-label"><?= __('Name'); ?></label>
                                <div class="col-md-7">
                                    <!-- <input type="text" <?= $style; ?> name="text_event[<?= $data_listDb->event_id; ?>]" value="<?= htmlspecialchars($data_listDb->event_event); ?>" placeholder="<?= $event_text; ?>" size="44" class="form-control form-control-sm"> -->
                                    <input type="text" <?= $style; ?> name="text_event[<?= $data_listDb->event_id; ?>]" value="<?= htmlspecialchars($data_listDb->event_event); ?>" size="44" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Select ROLE. If own role is added, ROLE will be OTHER *** -->
                            <div class="row mb-1">
                                <!-- <label for="event" class="col-md-3 col-form-label"><?= ucfirst($this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom)); ?></label> -->
                                <div class="col-md-3"></div>
                                <label for="event" class="col-md-9 col-form-label"><?= __('Select role or type other role:'); ?></label>
                            </div>

                            <div class="row mb-2">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Select role'); ?></label>
                                <div class="col-md-7">

                                    <input type="hidden" name="check_event_kind[<?= $data_listDb->event_id; ?>]" value="<?= $data_listDb->event_kind; ?>">

                                    <select size="1" name="event_gedcom[<?= $data_listDb->event_id; ?>]" class="form-select form-select-sm">
                                        <option value="WITN"><?= __('Witness'); ?></option>

                                        <?php if ($data_listDb->event_connect_kind == 'CHR' || $data_listDb->event_connect_kind == 'BURI' || $data_listDb->event_connect_kind == 'MARR_REL') { ?>
                                            <option value="CLERGY" <?= $data_listDb->event_gedcom == 'CLERGY' ? 'selected' : ''; ?>><?= ucfirst(__('clergy')); ?></option>
                                        <?php } ?>

                                        <?php if ($data_listDb->event_connect_kind == 'birth_declaration' || $data_listDb->event_connect_kind == 'death_declaration' || $data_listDb->event_connect_kind == 'MARR') { ?>
                                            <option value="OFFICIATOR" <?= $data_listDb->event_gedcom == 'OFFICIATOR' ? 'selected' : ''; ?>><?= ucfirst(__('officiator')); ?></option>
                                        <?php } ?>

                                        <?php if ($data_listDb->event_connect_kind == 'CHR') { ?>
                                            <option value="GODP" <?= $data_listDb->event_gedcom == 'GODP' ? 'selected' : ''; ?>><?= ucfirst(__('godfather')); ?></option>
                                        <?php } ?>

                                        <option value="OTHER" <?= $data_listDb->event_gedcom == 'OTHER' ? 'selected' : ''; ?>><?= __('Other role'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <!-- <label for="event" class="col-md-3 col-form-label"><b><?= __('or'); ?>:</b></label> -->
                                <!-- <div class="col-md-3"></div> -->
                                <label for="event" class="col-md-3 col-form-label"><?= __('Other role'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    $event_event_extra = '';
                                    if ($data_listDb->event_event_extra) {
                                        $event_event_extra = htmlspecialchars($data_listDb->event_event_extra);
                                    }
                                    ?>
                                    <input type="text" name="event_event_extra[<?= $data_listDb->event_id; ?>]" value="<?= $event_event_extra; ?>" size="44" class="form-control form-control-sm">
                                </div>
                            </div>


                        <?php } elseif ($data_listDb->event_kind == 'picture') { ?>
                            <div>
                                <?php
                                $tree_pict_path3 = $tree_pict_path;  // we change it only if category subfolders exist
                                $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
                                if ($temp->rowCount()) {  // there is a category table 
                                    $catgr = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
                                    if ($catgr->rowCount()) {
                                        while ($catDb = $catgr->fetch(PDO::FETCH_OBJ)) {
                                            if (substr($data_listDb->event_event, 0, 3) == $catDb->photocat_prefix && is_dir($path_prefix . $tree_pict_path3 . substr($data_listDb->event_event, 0, 2))) {   // there is a subfolder of this prefix
                                                $tree_pict_path3 = $tree_pict_path3 . substr($data_listDb->event_event, 0, 2) . '/';  // look in that subfolder
                                            }
                                        }
                                    }
                                }

                                // echo '<a href="../' . give_media_path($path_prefix . $tree_pict_path3, $data_listDb->event_event) . '" target="_blank">' .
                                //     print_thumbnail($path_prefix . $tree_pict_path3, $data_listDb->event_event) . '</a>';
                                echo '<a href="../' . give_media_path($tree_pict_path3, $data_listDb->event_event) . '" target="_blank">' .
                                    $showMedia->print_thumbnail($path_prefix . $tree_pict_path3, $data_listDb->event_event) . '</a>';
                                ?>
                            </div>

                            <?php
                            $picture_link = $data_listDb->event_event;
                            if ($check_sources_text) {
                                $picture_link .= ' ' . $check_sources_text;
                            }
                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $picture_link);

                            // *** Use text box for pictures and pop-up window ***
                            // *** To use place selection pop-up, replaced event_place[x] array by: 'event_place_'.$data_listDb->event_id ***
                            $form = 1;
                            if ($event_connect_kind == 'family') {
                                $form = 2;
                            }
                            if ($event_connect_kind == 'source') {
                                $form = 3;
                            }
                            ?>
                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Picture/ Media'); ?></label>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <input type="text" name="text_event<?= $data_listDb->event_id; ?>" placeholder="<?= __('Picture/ Media'); ?>" value="<?= $data_listDb->event_event; ?>" class="form-control form-control-sm">
                                        <a href="#" onClick='window.open("index.php?page=editor_media_select&amp;form=<?= $form; ?>&amp;event_id=<?= $data_listDb->event_id; ?>","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                    </div>
                                </div>
                            </div>
                        <?php


                        } elseif ($data_listDb->event_kind == 'adoption') {
                            // *** Show names of adoption parents ***
                            $parent_text = '';
                            if ($data_listDb->event_event) {
                                $adoptionDb = $db_functions->get_family($data_listDb->event_event, 'man-woman');
                                $parent_text = '[' . $data_listDb->event_event . '] ';

                                //*** Father ***
                                if (isset($adoptionDb->fam_man) and $adoptionDb->fam_man) {
                                    $parent_text .= show_person($adoptionDb->fam_man, false, false);
                                } else {
                                    $parent_text = __('N.N.');
                                }

                                $parent_text .= ' ' . __('and') . ' ';

                                //*** Mother ***
                                if (isset($adoptionDb->fam_woman) and $adoptionDb->fam_woman) {
                                    $parent_text .= show_person($adoptionDb->fam_woman, false, false);
                                } else {
                                    $parent_text .= __('N.N.');
                                }
                            }

                            if ($check_sources_text) {
                                $parent_text .= ' ' . $check_sources_text;
                            }

                            echo $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom) . ': ';

                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $parent_text);
                        ?>
                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Adoption'); ?></label>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="text_event<?= $data_listDb->event_id; ?>" placeholder="<?= __('GEDCOM number (ID)'); ?>" value="<?= $data_listDb->event_event; ?>" class="form-control form-control-sm">
                                        <a href="#" onClick='window.open("index.php?page=editor_relation_select&amp;adoption_id=<?= $data_listDb->event_id; ?>","","<?= $field_popup; ?>")'><img src=" ../images/search.png" alt="<?= __('Search'); ?>"></a>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }

                        // *** person_colour_mark ***
                        elseif ($data_listDb->event_kind == 'person_colour_mark') {
                            // *** Needed for descendants/ ascendants color ***
                            echo '<input type="hidden" name="event_event_old[' . $data_listDb->event_id . ']" value="' . $data_listDb->event_event . '">';

                            $pers_colour = '';
                            $person_colour_mark = $data_listDb->event_event;
                            if ($person_colour_mark == '1') {
                                $pers_colour = 'style="color:#FF0000;"';
                            }
                            if ($person_colour_mark == '2') {
                                $pers_colour = 'style="color:#00FF00;"';
                            }
                            if ($person_colour_mark == '3') {
                                $pers_colour = 'style="color:#0000FF;"';
                            }
                            if ($person_colour_mark == '4') {
                                $pers_colour = 'style="color:#FF00FF;"';
                            }
                            if ($person_colour_mark == '5') {
                                $pers_colour = 'style="color:#FFFF00;"';
                            }
                            if ($person_colour_mark == '6') {
                                $pers_colour = 'style="color:#00FFFF;"';
                            }
                            if ($person_colour_mark == '7') {
                                $pers_colour = 'style="color:#C0C0C0;"';
                            }
                            if ($person_colour_mark == '8') {
                                $pers_colour = 'style="color:#800000;"';
                            }
                            if ($person_colour_mark == '9') {
                                $pers_colour = 'style="color:#008000;"';
                            }
                            if ($person_colour_mark == '10') {
                                $pers_colour = 'style="color:#000080;"';
                            }
                            if ($person_colour_mark == '11') {
                                $pers_colour = 'style="color:#800080;"';
                            }
                            if ($person_colour_mark == '12') {
                                $pers_colour = 'style="color:#A52A2A;"';
                            }
                            if ($person_colour_mark == '13') {
                                $pers_colour = 'style="color:#008080;"';
                            }
                            if ($person_colour_mark == '14') {
                                $pers_colour = 'style="color:#808080;"';
                            }
                            //$text.=' <span '.$pers_colour.'>'.__('Selected colour').'</span>';
                            $person_colour = ' <span ' . $pers_colour . '>' . __('Selected colour') . '</span>';

                            if ($check_sources_text) {
                                $person_colour .= ' ' . $check_sources_text;
                            }

                            echo $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom) . ': ';

                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $person_colour);
                        ?>

                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Selected colour'); ?></label>
                                <div class="col-md-7">
                                    <select size="1" name="text_event[<?= $data_listDb->event_id; ?>]" class="form-select form-select-sm">
                                        <option value="0"><?= __('Change colour mark by person'); ?></option>
                                        <option value="1" style="color:#FF0000;" <?= $person_colour_mark == '1' ? 'selected' : ''; ?>><?= __('Colour 1'); ?></option>
                                        <option value="2" style="color:#00FF00;" <?= $person_colour_mark == '2' ? 'selected' : ''; ?>><?= __('Colour 2'); ?></option>
                                        <option value="3" style="color:#0000FF;" <?= $person_colour_mark == '3' ? 'selected' : ''; ?>><?= __('Colour 3'); ?></option>
                                        <option value="4" style="color:#FF00FF;" <?= $person_colour_mark == '4' ? 'selected' : ''; ?>><?= __('Colour 4'); ?></option>
                                        <option value="5" style="color:#FFFF00;" <?= $person_colour_mark == '5' ? 'selected' : ''; ?>><?= __('Colour 5'); ?></option>
                                        <option value="6" style="color:#00FFFF;" <?= $person_colour_mark == '6' ? 'selected' : ''; ?>><?= __('Colour 6'); ?></option>
                                        <option value="7" style="color:#C0C0C0;" <?= $person_colour_mark == '7' ? 'selected' : ''; ?>><?= __('Colour 7'); ?></option>
                                        <option value="8" style="color:#800000;" <?= $person_colour_mark == '8' ? 'selected' : ''; ?>><?= __('Colour 8'); ?></option>
                                        <option value="9" style="color:#008000;" <?= $person_colour_mark == '9' ? 'selected' : ''; ?>><?= __('Colour 9'); ?></option>
                                        <option value="10" style="color:#000080;" <?= $person_colour_mark == '10' ? 'selected' : ''; ?>><?= __('Colour 10'); ?></option>
                                        <option value="11" style="color:#800080;" <?= $person_colour_mark == '11' ? 'selected' : ''; ?>><?= __('Colour 11'); ?></option>
                                        <option value="12" style="color:#A52A2A;" <?= $person_colour_mark == '12' ? 'selected' : ''; ?>><?= __('Colour 12'); ?></option>
                                        <option value="13" style="color:#008080;" <?= $person_colour_mark == '13' ? 'selected' : ''; ?>><?= __('Colour 13'); ?></option>
                                        <option value="14" style="color:#808080;" <?= $person_colour_mark == '14' ? 'selected' : ''; ?>><?= __('Colour 14'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Also change'); ?></label>
                                <div class="col-md-3">
                                    <input type="checkbox" name="pers_colour_desc[<?= $data_listDb->event_id; ?>]" class="form-check-input"> <?= __('Descendants'); ?><br>
                                    <input type="checkbox" name="pers_colour_anc[<?= $data_listDb->event_id; ?>]" class="form-check-input"> <?= __('Ancestors'); ?>
                                </div>
                            </div>
                        <?php
                        }

                        // *** profession ***
                        elseif ($data_listDb->event_kind == 'profession') {
                            $profession_link = $data_listDb->event_event;
                            if ($check_sources_text) {
                                $profession_link .= ' ' . $check_sources_text;
                            }
                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $profession_link);
                        ?>
                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Profession'); ?></label>
                                <div class="col-md-7">
                                    <textarea rows="1" name="text_event[<?= $data_listDb->event_id; ?>]" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($data_listDb->event_event); ?></textarea>
                                </div>
                            </div>
                        <?php
                        }

                        // *** religion ***
                        elseif ($data_listDb->event_kind == 'religion') {
                            echo $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom) . ': ';

                            $religion_link = $data_listDb->event_event;
                            if ($check_sources_text) {
                                $religion_link .= ' ' . $check_sources_text;
                            }
                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $religion_link);
                        ?>
                            <div class="row mb-1">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Religion'); ?></label>
                                <div class="col-md-7">
                                    <textarea rows="1" name="text_event[<?= $data_listDb->event_id; ?>]" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($data_listDb->event_event); ?></textarea>
                                </div>
                            </div>
                        <?php
                        }

                        // *** General name of event ***
                        else {
                            // *** Show name of event ***
                            if ($data_listDb->event_gedcom == 'NICK') {
                                echo __('Nickname') . ': ';
                            } elseif ($data_listDb->event_gedcom == '_RUFN') {
                                echo __('German Rufname') . ': ';
                            } elseif (language_name($data_listDb->event_gedcom)) {
                                echo language_name($data_listDb->event_gedcom);
                            } else {
                                echo $this->event_text($data_listDb->event_kind, $data_listDb->event_gedcom) . ': ';
                            }

                            $event_text = $data_listDb->event_event;
                            if (!$event_text) {
                                $event_text = language_event($data_listDb->event_gedcom);
                            }

                            if ($check_sources_text) {
                                $event_text .= ' ' . $check_sources_text;
                            }

                            // *** Hide/show line (start <span> to hide edit line) ***
                            echo $this->hide_show_start($data_listDb, $event_text);

                            // *** Check if event has text ***
                            $style = ''; //if (!$data_listDb->event_event) $style='style="background-color:#FFAA80"';
                        ?>
                            <div class="row mb-2">
                                <label for="event" class="col-md-3 col-form-label"><?= __('Event'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    if ($data_listDb->event_kind == 'name') {
                                        echo '<select size="1" name="event_gedcom[' . $data_listDb->event_id . ']" class="form-select form-select-sm">';
                                        // *** Nickname, alias, adopted name, hebrew name, etc. ***
                                        event_selection($data_listDb->event_gedcom);
                                        echo '</select>';
                                    }

                                    // *** Select type of event ***
                                    if ($data_listDb->event_kind == 'event') {
                                    ?>
                                        <select size="1" name="event_gedcom[<?= $data_listDb->event_id; ?>]" class="form-select form-select-sm">
                                            <?php
                                            if ($event_kind == 'person') {
                                            ?>
                                                <optgroup label="<?= __('Events'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'EVEN');
                                                    echo event_option($data_listDb->event_gedcom, '_NMAR');
                                                    echo event_option($data_listDb->event_gedcom, 'NCHI');
                                                    echo event_option($data_listDb->event_gedcom, 'MILI');
                                                    echo event_option($data_listDb->event_gedcom, 'TXPY');
                                                    echo event_option($data_listDb->event_gedcom, 'CENS');
                                                    echo event_option($data_listDb->event_gedcom, 'RETI');
                                                    echo event_option($data_listDb->event_gedcom, 'CAST');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Baptise'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'BAPM');
                                                    echo event_option($data_listDb->event_gedcom, 'CHRA');
                                                    echo event_option($data_listDb->event_gedcom, 'LEGI');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Adoption'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'ADOP');
                                                    echo event_option($data_listDb->event_gedcom, '_ADPF');
                                                    echo event_option($data_listDb->event_gedcom, '_ADPM');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Settling'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'ARVL');
                                                    echo event_option($data_listDb->event_gedcom, 'DPRT');
                                                    echo event_option($data_listDb->event_gedcom, 'IMMI');
                                                    echo event_option($data_listDb->event_gedcom, 'EMIG');
                                                    echo event_option($data_listDb->event_gedcom, 'NATU');
                                                    echo event_option($data_listDb->event_gedcom, 'NATI');
                                                    echo event_option($data_listDb->event_gedcom, 'PROP');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Characteristics'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, '_HEIG');
                                                    echo event_option($data_listDb->event_gedcom, '_WEIG');
                                                    echo event_option($data_listDb->event_gedcom, '_EYEC');
                                                    echo event_option($data_listDb->event_gedcom, '_HAIR');
                                                    echo event_option($data_listDb->event_gedcom, '_MEDC');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Buried'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, '_FNRL');
                                                    echo event_option($data_listDb->event_gedcom, '_INTE');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Will'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'PROB');
                                                    echo event_option($data_listDb->event_gedcom, 'WILL');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Religious'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'CONF');
                                                    echo event_option($data_listDb->event_gedcom, 'BLES');
                                                    echo event_option($data_listDb->event_gedcom, 'FCOM');
                                                    echo event_option($data_listDb->event_gedcom, 'ORDN');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Education'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'GRAD');
                                                    echo event_option($data_listDb->event_gedcom, 'EDUC');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Social'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'AFN');
                                                    echo event_option($data_listDb->event_gedcom, 'SSN');
                                                    echo event_option($data_listDb->event_gedcom, 'IDNO');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('LDS'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'BAPL');
                                                    echo event_option($data_listDb->event_gedcom, 'CONL');
                                                    echo event_option($data_listDb->event_gedcom, 'ENDL');
                                                    echo event_option($data_listDb->event_gedcom, 'SLGC');
                                                    echo event_option($data_listDb->event_gedcom, 'SLGL');
                                                    ?>
                                                </optgroup>

                                                <optgroup label="<?= __('Jewish'); ?>">
                                                    <?php
                                                    echo event_option($data_listDb->event_gedcom, 'BARM');
                                                    echo event_option($data_listDb->event_gedcom, 'BASM');
                                                    echo event_option($data_listDb->event_gedcom, '_BRTM');
                                                    echo event_option($data_listDb->event_gedcom, '_YART');
                                                    ?>
                                                </optgroup>
                                            <?php
                                            }

                                            if ($event_kind == 'family') {
                                                // *** Marriage events ***
                                                echo event_option($data_listDb->event_gedcom, 'EVEN');
                                                echo event_option($data_listDb->event_gedcom, '_MBON');
                                                echo event_option($data_listDb->event_gedcom, 'MARC');
                                                echo event_option($data_listDb->event_gedcom, 'MARL');
                                                echo event_option($data_listDb->event_gedcom, 'MARS');
                                                echo event_option($data_listDb->event_gedcom, 'DIVF');
                                                echo event_option($data_listDb->event_gedcom, 'ANUL');
                                                echo event_option($data_listDb->event_gedcom, 'ENGA');
                                                echo event_option($data_listDb->event_gedcom, 'SLGS');
                                            }
                                            ?>
                                        </select>
                                    <?php } ?>

                                    <input type="text" <?= $style; ?> name="text_event[<?= $data_listDb->event_id; ?>]" value="<?= $data_listDb->event_event; ?>" size="60" class="form-control form-control-sm">
                                    <?php
                                    if ($data_listDb->event_kind == 'NPFX') {
                                        echo '<span style="font-size: 13px;">' . __('e.g. Lt. Cmndr.') . '</span>';
                                    } elseif ($data_listDb->event_kind == 'NSFX') {
                                        echo '<span style="font-size: 13px;">' . __('e.g. Jr.') . '</span>';
                                    } elseif ($data_listDb->event_kind == 'nobility') {
                                        echo '<span style="font-size: 13px;">' . __('e.g. Jhr., Jkvr.') . '</span>';
                                    } elseif ($data_listDb->event_kind == 'title') {
                                        echo '<span style="font-size: 13px;">' . __('e.g. Prof., Dr.') . '</span>';
                                    } elseif ($data_listDb->event_kind == 'lordship') {
                                        echo '<span style="font-size: 13px;">' . __('e.g. Lord of Amsterdam') . '</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- Date and place by event -->
                        <?php
                        $witness_array = array("ASSO", "witness");
                        if (!in_array($event_kind, $witness_array)) {
                        ?>
                            <div class="row mb-2">
                                <label for="event_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                                <div class="col-md-7">
                                    <?php $editor_cls->date_show($data_listDb->event_date, 'event_date', "[$data_listDb->event_id]"); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- To use place selection pop-up, replaced event_place[x] array by: 'event_place_'.$data_listDb->event_id -->
                        <?php
                        $form = 1;
                        if ($event_connect_kind == 'family') {
                            $form = 2;
                        }
                        if ($event_connect_kind == 'source') {
                            $form = 3;
                        }
                        ?>
                        <?php
                        $witness_array = array("ASSO", "witness");
                        if (!in_array($event_kind, $witness_array)) {
                        ?>
                            <div class="row mb-2">
                                <label for="event_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <input type="text" name="event_place<?= $data_listDb->event_id; ?>" value="<?= $data_listDb->event_place; ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                        <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=event_place&amp;event_id=<?= $data_listDb->event_id; ?>","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php
                        // *** Text by event ***
                        $field_text_selected = $field_text;
                        if ($data_listDb->event_text && preg_match('/\R/', $data_listDb->event_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>

                        <div class="row mb-2">
                            <label for="event_date" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="event_text[<?= $data_listDb->event_id; ?>]" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($data_listDb->event_text); ?></textarea>
                            </div>
                        </div>

                        <?php
                        $witness_array = array("ASSO", "witness");
                        if (!in_array($event_kind, $witness_array)) {
                        ?>
                            <!-- Source by event -->
                            <div class="row mb-2">
                                <label for="source_event" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    if ($event_connect_kind == 'person') {
                                        source_link3('person', 'pers_event_source', $data_listDb->event_id);
                                    } elseif ($event_connect_kind == 'family') {
                                        source_link3('family', 'fam_event_source', $data_listDb->event_id);
                                    }

                                    if ($check_sources_text) {
                                        echo $check_sources_text;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($hideshow) && substr($hideshow, 0, 4) === '9000') {
                            echo '</span>';
                        } ?>
                    </td>

                </tr>

            <?php
            }
        } // *** Don't use this block for newly added person ***


        // *** Directly add a first profession for new person ***
        if (isset($_GET['add_person'])) {
            if ($event_kind == 'profession') {
            ?>
                <tr>
                    <td style="border-right:0px;"><?= __('Profession'); ?></td>
                    <td colspan="2">
                        <div class="row mb-2">
                            <label for="event_profession" class="col-md-3 col-form-label"><?= __('Profession'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="event_profession" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_date_profession" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show("", "event_date_profession", ""); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_place_profession" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="event_place_profession" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_text_profession" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="event_text_profession" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show(""); ?></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } elseif ($event_kind == 'religion') { ?>
                <tr>
                    <td style="border-right:0px;"><?= __('Religion'); ?></td>
                    <td colspan="2">
                        <div class="row mb-2">
                            <label for="event_religion" class="col-md-3 col-form-label"><?= __('Religion'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="event_religion" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_date_religion" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show("", "event_date_religion", ""); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_place_religion" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="event_place_religion" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="event_text_religion" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="event_text_religion" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show(""); ?></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php
            }
        }

        if ($event_kind == 'picture' || $event_kind == 'marriage_picture' || $event_kind == 'source_picture') {
            // get subfolders of media dir 
            $subfolders = glob($path_prefix . $tree_pict_path . '[^.]*', GLOB_ONLYDIR);
            $ignore = array('cms', 'slideshow', 'thumbs');
            // *** Upload image ***
            ?>
            <tr class="table_header_large">
                <td></td>
                <td colspan="2">

                    <div class="row">
                        <div class="col-md-auto">
                            <?= __('Upload new image'); ?>
                        </div>

                        <div class="col-md-auto">
                            <input type="file" name="photo_upload">
                        </div>

                        <div class="col-md-auto">
                            <select size="1" name="select_media_folder" class="form-select form-select-sm">
                                <option value=""><?= __('Main media folder'); ?></option>
                                <?php
                                $pcat_dirs = $showMedia->get_pcat_dirs();
                                foreach ($subfolders as $folder) {
                                    $bfolder = pathinfo($folder, PATHINFO_BASENAME);
                                    if (in_array($bfolder, $ignore)) {
                                        // do nothing
                                    } elseif (array_key_exists($bfolder .  '_', $pcat_dirs)) {
                                ?>
                                        <option value="<?= $bfolder; ?>"><?= __('Category'); ?>: <?= $pcat_dirs[$bfolder . '_']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?= $bfolder; ?>"><?= __('Directory'); ?>: <?= $bfolder; ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <input type="submit" name="<?php
                                                        if ($event_kind == 'picture') {
                                                            echo 'person_add_media';
                                                        } elseif ($event_kind == 'marriage_picture') {
                                                            echo 'relation_add_media';
                                                        } else {
                                                            echo 'source_add_media';
                                                        }
                                                        ?>" title="submit" value="<?= __('Upload'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>
                    </div>
                </td>
            </tr>
        <?php
        }

        // *** Show events if save or arrow links are used ***
        // Deels al vervangen door $_POST...
        /*
        if (isset($_GET['event_person']) OR isset($_GET['event_family']) OR isset($_GET['event_add'])){
        // *** Script voor expand and collapse of items ***

        $link_id='';
        if (isset($_GET['event_person']) AND $_GET['event_person']=='1') $link_id='51';
        if (isset($_GET['event_family']) AND $_GET['event_family']=='1') $link_id='52';
        if (isset($_GET['event_kind'])){
            if ($_GET['event_kind']=='name') $link_id='1';
            if ($_GET['event_kind']=='npfx') $link_id='1';
            if ($_GET['event_kind']=='nsfx') $link_id='1';
            if ($_GET['event_kind']=='nobility') $link_id='1';
            if ($_GET['event_kind']=='title') $link_id='1';
            if ($_GET['event_kind']=='lordship') $link_id='1';
            if ($_GET['event_kind']=='birth_declaration') $link_id='2';
            if ($_GET['event_kind']=='baptism_witness') $link_id='3';
            if ($_GET['event_kind']=='death_declaration') $link_id='4';
            if ($_GET['event_kind']=='burial_witness') $link_id='5';
            if ($_GET['event_kind']=='profession') $link_id='13';
            if ($_GET['event_kind']=='religion') $link_id='14';
            if ($_GET['event_kind']=='picture') $link_id='53';
            if ($_GET['event_kind']=='marriage_witness') $link_id='8';
            if ($_GET['event_kind']=='marriage_witness_rel') $link_id='10';
        }

        if (isset($_GET['event_add'])){
//			if ($_GET['event_add']=='add_name') $link_id='1';
//			if ($_GET['event_add']=='add_npfx') $link_id='1';
//			if ($_GET['event_add']=='add_nsfx') $link_id='1';
//			if ($_GET['event_add']=='add_nobility') $link_id='1';
//			if ($_GET['event_add']=='add_title') $link_id='1';
//			if ($_GET['event_add']=='add_lordship') $link_id='1';
//			if ($_GET['event_add']=='add_birth_declaration') $link_id='2';
//			if ($_GET['event_add']=='add_baptism_witness') $link_id='3';
//			if ($_GET['event_add']=='add_death_declaration') $link_id='4';
//			if ($_GET['event_add']=='add_burial_witness') $link_id='5';
//			if ($_GET['event_add']=='add_profession') $link_id='13';
//			if ($_GET['event_add']=='add_religion') $link_id='14';
            if ($_GET['event_add']=='add_picture') $link_id='53';
            if ($_GET['event_add']=='add_source_picture') $link_id='53';
            if ($_GET['event_add']=='add_marriage_picture') $link_id='53';
//			if ($_GET['event_add']=='add_marriage_witness') $link_id='8';
//			if ($_GET['event_add']=='add_marriage_witness_rel') $link_id='10';
        }

        $text.='
        <script>
        function Show(el_id){
            // *** Hide or show item ***
            var arr = document.getElementsByClassName(\'row\'+el_id);
            for (i=0; i<arr.length; i++){
                arr[i].style.display="";
            }
            // *** Change [+] into [-] ***
            document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
        }
        </script>';

        $text.='<script>
            Show("'.$link_id.'");
        </script>';
    }
*/

        // TODO check return (no longer needed?).
        return $text;
    }   // end function show_event

}   // end class


function event_selection($event_gedcom)
{
    global $humo_option;

    if (!$event_gedcom) {
        ?>
        <optgroup label="<?= __('Nickname'); ?>">
        <?php } ?>
        <option value="NICK" <?= $event_gedcom == 'NICK' ? 'selected' : ''; ?>>NICK <?= __('Nickname'); ?></option>
        <?php if (!$event_gedcom) { ?>
        </optgroup>

        <optgroup label="<?= __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>">
            <option value="NPFX"><?= __('Prefix') . ': ' . __('e.g. Lt. Cmndr.'); ?></option>
            <option value="NSFX" <?= $event_gedcom == 'NSFX' ? 'selected' : ''; ?>><?= __('Suffix'); ?>: <?= __('e.g. Jr.'); ?></option>
            <option value="nobility" <?= $event_gedcom == 'nobility' ? 'selected' : ''; ?>><?= __('Title of Nobility') . ': ' . __('e.g. Jhr., Jkvr.'); ?></option>
            <option value="title" <?= $event_gedcom == 'title' ? 'selected' : ''; ?>><?= __('Title') . ': ' . __('e.g. Prof., Dr.'); ?></option>
            <option value="lordship" <?= $event_gedcom == 'lordship' ? 'selected' : ''; ?>><?= __('Title of Lordship') . ': ' . __('e.g. Lord of Amsterdam'); ?></option>
        </optgroup>

        <optgroup label="<?= __('Name'); ?>">
        <?php } ?>

        <option value="_AKAN" <?= $event_gedcom == '_AKAN' ? 'selected' : ''; ?>><?= '_AKAN ' . __('Also known as'); ?></option>
        <option value="_ALIA" <?= $event_gedcom == '_ALIA' ? 'selected' : ''; ?>><?= '_ALIA ' . __('alias name'); ?></option>
        <option value="_SHON" <?= $event_gedcom == '_SHON' ? 'selected' : ''; ?>>_SHON <?= __('Short name (for reports)'); ?></option>
        <option value="_ADPN" <?= $event_gedcom == '_ADPN' ? 'selected' : ''; ?>>_ADPN <?= __('Adopted name'); ?></option>

        <!--- display here if user didn't set to be displayed in main name section -->
        <?php if ($humo_option['admin_hebname'] != "y") { ?>
            <option value="_HEBN" <?= $event_gedcom == '_HEBN' ? 'selected' : ''; ?>>_HEBN <?= __('Hebrew name'); ?></option>
        <?php } ?>

        <option value="_CENN" <?= $event_gedcom == '_CENN' ? 'selected' : ''; ?>>_CENN <?= __('Census name'); ?></option>
        <option value="_MARN" <?= $event_gedcom == '_MARN' ? 'selected' : ''; ?>>_MARN <?= __('Married name'); ?></option>
        <option value="_GERN" <?= $event_gedcom == '_GERN' ? 'selected' : ''; ?>>_GERN <?= __('Given name'); ?></option>
        <option value="_FARN" <?= $event_gedcom == '_FARN' ? 'selected' : ''; ?>>_FARN <?= __('Farm name'); ?></option>
        <option value="_BIRN" <?= $event_gedcom == '_BIRN' ? 'selected' : ''; ?>>_BIRN <?= __('Birth name'); ?></option>
        <option value="_INDN" <?= $event_gedcom == '_INDN' ? 'selected' : ''; ?>>_INDN <?= __('Indian name'); ?></option>
        <option value="_FKAN" <?= $event_gedcom == '_FKAN' ? 'selected' : ''; ?>>_FKAN <?= __('Formal name'); ?></option>
        <option value="_CURN" <?= $event_gedcom == '_CURN' ? 'selected' : ''; ?>>_CURN <?= __('Current name'); ?></option>
        <option value="_SLDN" <?= $event_gedcom == '_SLDN' ? 'selected' : ''; ?>>_SLDN <?= __('Soldier name'); ?></option>
        <option value="_RELN" <?= $event_gedcom == '_RELN' ? 'selected' : ''; ?>>_RELN <?= __('Religious name'); ?></option>
        <option value="_OTHN" <?= $event_gedcom == '_OTHN' ? 'selected' : ''; ?>>_OTHN <?= __('Other name'); ?></option>
        <option value="_FRKA" <?= $event_gedcom == '_FRKA' ? 'selected' : ''; ?>>_FRKA <?= __('Formerly known as'); ?></option>
        <option value="_RUFN" <?= $event_gedcom == '_RUFN' ? 'selected' : ''; ?>>_RUFN <?= __('German Rufname'); ?></option>

        <?php if (!$event_gedcom) { ?>
        </optgroup>
<?php
        }
    }

    // *** Javascript for "search by file name of picture" feature ***
    // March 2022: no longer in use
    /*
echo '<script>
    function Search_pic(idnum, picnr, picarr){
        var searchval = document.getElementById("inp_text_event" + idnum).value;
        searchval = searchval.toLowerCase();
        var countarr = 0;
        // *** delete existing full list ***
        document.getElementById("text_event" + idnum).options.length=0; 
        for (var countpics=0; countpics<picnr; countpics++){
            var picname = picarr[countpics].toLowerCase();
            if(picname.indexOf(searchval) != -1) {
                document.getElementById("text_event" + idnum).options[countarr]=new Option(picarr[countpics], picarr[countpics], true, false);
                countarr++;
            }
        }
    }
    </script>';
*/

    // *** If profession is added, jump to profession part of screen ***
    if (isset($_POST['event_event_profession']) && $_POST['event_event_profession'] != '') {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#profession";</script>';
    }

    // *** If religion is added, jump to religion part of screen ***
    if (isset($_POST['event_event_religion']) && $_POST['event_event_religion'] != '') {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#religion";</script>';
    }

    // *** If witness is added, jump to witness part of screen ***
    if (isset($_POST['add_birth_declaration'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#birth_decl_witness";</script>';
    }
    if (isset($_POST['add_baptism_witness'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#baptism_witness";</script>';
    }
    if (isset($_POST['add_death_declaration'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#death_decl_witness";</script>';
    }
    if (isset($_POST['add_burial_witness'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#burial_witness";</script>';
    }
    if (isset($_POST['add_marriage_witness'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#marriage_witness";</script>';
    }
    if (isset($_POST['add_marriage_witness_rel'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#marriage_witness_rel";</script>';
    }

    // *** If address is added, jump to witness part of screen ***
    if (isset($_POST['person_add_address'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#addresses";</script>';
    }
    if (isset($_POST['relation_add_address'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#addresses";</script>';
    }

    // *** If media is added, jump to media part of screen ***
    if (isset($_POST['add_picture']) or isset($_POST['add_marriage_picture']) or isset($_POST['add_source_picture'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#picture";</script>';
    }

    // *** If event is added, jump to event part of screen ***
    if (isset($_POST['person_event_add'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#event_person_link";</script>';
    }
    // *** If event is added, jump to event part of screen ***
    if (isset($_POST['marriage_event_add'])) {
        echo '<script>window.location = window.location.origin + window.location.pathname + "#event_family_link";</script>';
    }
