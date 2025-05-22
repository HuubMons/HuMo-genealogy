<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011 Huub: translated all variables to english by.
 * July 2024 Huub: removed doublescroll and html2canvas. Just use browser to print and scroll.
 */

if (!isset($hourglass)) {
    // *** Check if person gedcomnumber is valid ***
    $db_functions->check_person($data["main_person"]);

    echo $data["ancestor_header"];
}

if (!isset($hourglass)) {
    //Width of the chart. For 6 generations 1000px is right.
    //If we ever make the anc chart have optionally more generations, the width and length will have to be generated as in report_descendant
    //$divlen = 1000;

    $top = 50;

    $column1_left = 10;
    $column1_top = $top + 520;

    $column2_left = 50;
    $column2_top = $top + 320;

    $column3_left = 80;
    $column3_top = $top + 199;

    $column4_left = 300;
    $column4_top = $top - 290;

    $column5_left = 520;
    $column5_top = $top - 110;

    $column6_left = 740;
    $column6_top = $top - 20;
?>

    <div class="container-xl" style="height: 1000px; width:1000px;">
        <!-- First column name -->
        <!-- No _ character allowed in name of CSS class because of javascript -->
        <div class="ancestorName <?= $data["sexe"][1] == 'M' ? 'ancestor_man' : 'ancestor_man'; ?>" align="left" style="top: <?= $column1_top; ?>px; left: <?= $column1_left; ?>px; height: 80px; width:200px;">
            <?= ancestor_chart_person('1', 'large'); ?>
        </div>

        <!-- Second column split -->
        <div class="ancestor_split" style="top: <?= $column2_top; ?>px; left: <?= $column2_left; ?>px; height: 199px"></div>
        <div class="ancestor_split" style="top: <?= ($column2_top + 281); ?>px; left: <?= $column2_left; ?>px; height: 199px"></div>
        <!-- Second column names -->
        <?php for ($i = 1; $i < 3; $i++) { ?>
            <div class="ancestorName <?= $data["sexe"][$i + 1] == 'M' ? 'ancestor_man' : 'ancestor_woman'; ?>" style="top: <?= (($column2_top - 520) + ($i * 480)); ?>px; left: <?= ($column2_left + 8); ?>px; height: 80px; width:200px;">
                <?= ancestor_chart_person($i + 1, 'large'); ?>
            </div>
        <?php } ?>

        <!-- Third column split -->
        <div class="ancestor_split" style="top: <?= $column3_top; ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
        <div class="ancestor_split" style="top: <?= ($column3_top + 162); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
        <div class="ancestor_split" style="top: <?= ($column3_top + 480); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
        <div class="ancestor_split" style="top: <?= ($column3_top + 642); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
        <!-- Third column names -->
        <?php for ($i = 1; $i < 5; $i++) { ?>
            <div class="ancestorName <?= $data["sexe"][$i + 3] == 'M' ? 'ancestor_man' : 'ancestor_woman'; ?>" style="top: <?= (($column3_top - 279) + ($i * 240)); ?>px; left: <?= ($column3_left + 40); ?>px; height: 80px; width:200px;">
                <?= ancestor_chart_person($i + 3, 'large'); ?>
            </div>
        <?php
        }

        // *** Fourth column line ***
        for ($i = 1; $i < 3; $i++) {
            echo '<div class="ancestor_line" style="top: ' . ($column4_top + ($i * 485)) . 'px; left: ' . ($column4_left + 24) . 'px; height: 240px;"></div>';
        }
        // *** Fourth column split ***
        for ($i = 1; $i < 5; $i++) {
            echo '<div class="ancestor_split" style="top: ' . (($column4_top + 185) + ($i * 240)) . 'px; left: ' . ($column4_left + 32) . 'px; height: 120px;"></div>';
        }
        // *** Fourth column names ***
        for ($i = 1; $i < 9; $i++) {
        ?>
            <div class="ancestorName <?= $data["sexe"][$i + 7] == 'M' ? 'ancestor_man' : 'ancestor_woman'; ?>" style="top: <?= (($column4_top + 265) + ($i * 120)); ?>px; left: <?= ($column4_left + 40); ?>px; height: 80px; width:200px;">
                <?= ancestor_chart_person($i + 7, 'large'); ?>
            </div>
        <?php
        }

        // *** Fifth column line ***
        for ($i = 1; $i < 5; $i++) {
            echo '<div class="ancestor_line" style="top: ' . ($column5_top + ($i * 240)) . 'px; left: ' . ($column5_left + 24) . 'px; height: 120px;"></div>';
        }
        // *** Fifth column split ***
        for ($i = 1; $i < 9; $i++) {
            echo '<div class="ancestor_split" style="top: ' . (($column5_top + 90) + ($i * 120)) . 'px; left: ' . ($column5_left + 32) . 'px; height: 60px;"></div>';
        }
        // *** Fifth column names ***
        for ($i = 1; $i < 17; $i++) {
        ?>
            <div class="ancestorName <?= $data["sexe"][$i + 15] == 'M' ? 'ancestor_man' : 'ancestor_woman'; ?>" style="top: <?= (($column5_top + 125) + ($i * 60)); ?>px; left: <?= ($column5_left + 40); ?>px; height: 50px; width:200px;">
                <?= ancestor_chart_person($i + 15, 'medium'); ?>
            </div>
        <?php
        }

        // *** Last column line ***
        for ($i = 1; $i < 9; $i++) {
            echo '<div class="ancestor_line" style="top: ' . ($column6_top + ($i * 120)) . 'px; left: ' . ($column6_left + 24) . 'px; height: 60px;"></div>';
        }
        // *** Last column split ***
        for ($i = 1; $i < 17; $i++) {
            echo '<div class="ancestor_split" style="top: ' . (($column6_top + 45) + ($i * 60)) . 'px; left: ' . ($column6_left + 32) . 'px; height: 30px;"></div>';
        }
        // *** Last column names ***
        for ($i = 1; $i < 33; $i++) {
        ?>
            <div class="ancestorName <?= $data["sexe"][$i + 31] == 'M' ? 'ancestor_man' : 'ancestor_woman'; ?>" style="top: <?= (($column6_top + 66) + ($i * 30)); ?>px; left: <?= ($column6_left + 40); ?>px; height:16px; width:200px;">
                <?= ancestor_chart_person($i + 31, 'small'); ?>
            </div>
        <?php } ?>
    </div>

<?php
}

// *** Function to show data ***
// box_appearance (large, medium, small, and some other boxes...)
function ancestor_chart_person($id, $box_appearance)
{
    global $dbh, $db_functions, $humo_option, $user;
    global $data, $language, $dirmark1, $dirmark2;

    include_once(__DIR__ . "/../admin/include/media_inc.php");

    $hour_value = ''; // if called from hourglass size of chart is given in box_appearance as "hour45" etc.
    if (strpos($box_appearance, "hour") !== false) {
        $hour_value = substr($box_appearance, 4);
    }

    $text = '';
    $popup = '';

    if ($data["gedcomnumber"][$id]) {
        $personDb = $db_functions->get_person($data["gedcomnumber"][$id]);
        $person_cls = new PersonCls($personDb);
        $pers_privacy = $person_cls->get_privacy();
        $name = $person_cls->person_name($personDb);
        $name2 = $name["name"];
        $name2 = $dirmark2 . $name2 . $name["colour_mark"] . $dirmark2;

        // *** Replace pop-up icon by a text box ***
        $replacement_text = '';
        //$replacement_text.='<b>'.$id.'</b>';  // *** Ancestor number: id bold, name not ***
        $replacement_text .= '<span class="anc_box_name">' . $name2 . '</span>';

        // >>>>> link to show rest of ancestor chart
        if ($box_appearance == 'small' && isset($personDb->pers_gedcomnumber) && $personDb->pers_famc) {
            $replacement_text .= ' &gt;&gt;&gt;' . $dirmark1;
        }

        if ($pers_privacy) {
            if ($box_appearance != 'ancestor_sheet_marr') {
                $replacement_text .= '<br>' . __(' PRIVACY FILTER');  //Tekst privacy weergeven
            } else {
                $replacement_text = __(' PRIVACY FILTER');
            }
        } else {
            if ($box_appearance != 'small') {
                //if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
                if ($personDb->pers_birth_date) {
                    //$replacement_text.='<br>'.__('*').$dirmark1.' '.date_place($personDb->pers_birth_date,$personDb->pers_birth_place); }
                    $replacement_text .= '<br>' . __('*') . $dirmark1 . ' ' . date_place($personDb->pers_birth_date, '');
                }
                //elseif ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
                elseif ($personDb->pers_bapt_date) {
                    //$replacement_text.='<br>'.__('~').$dirmark1.' '.date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place); }
                    $replacement_text .= '<br>' . __('~') . $dirmark1 . ' ' . date_place($personDb->pers_bapt_date, '');
                }

                //if ($personDb->pers_death_date OR $personDb->pers_death_place){
                if ($personDb->pers_death_date) {
                    //$replacement_text.='<br>'.__('&#134;').$dirmark1.' '.date_place($personDb->pers_death_date,$personDb->pers_death_place); }
                    $replacement_text .= '<br>' . __('&#134;') . $dirmark1 . ' ' . date_place($personDb->pers_death_date, '');
                }
                //elseif ($personDb->pers_buried_date OR $personDb->pers_buried_place){
                elseif ($personDb->pers_buried_date) {
                    //$replacement_text.='<br>'.__('[]').$dirmark1.' '.date_place($personDb->pers_buried_date,$personDb->pers_buried_place); }
                    $replacement_text .= '<br>' . __('[]') . $dirmark1 . ' ' . date_place($personDb->pers_buried_date, '');
                }

                if ($box_appearance != 'medium') {
                    $marr_date = '';
                    if (isset($data["marr_date"][$id]) and ($data["marr_date"][$id] != '')) {
                        $marr_date = $data["marr_date"][$id];
                    }
                    $marr_place = '';
                    if (isset($data["marr_place"][$id]) and ($data["marr_place"][$id] != '')) {
                        $marr_place = $data["marr_place"][$id];
                    }
                    //if ($marr_date OR $marr_place){
                    if ($marr_date) {
                        //$replacement_text.='<br>'.__('X').$dirmark1.' '.date_place($marr_date,$marr_place); }
                        $replacement_text .= '<br>' . __('X') . $dirmark1 . ' ' . date_place($marr_date, '');
                    }
                }
                if ($box_appearance == 'ancestor_sheet_marr') {
                    $replacement_text = '';
                    $marr_date = '';
                    if (isset($data["marr_date"][$id]) and ($data["marr_date"][$id] != '')) {
                        $marr_date = $data["marr_date"][$id];
                    }
                    $marr_place = '';
                    if (isset($data["marr_place"][$id]) and ($data["marr_place"][$id] != '')) {
                        $marr_place = $data["marr_place"][$id];
                    }
                    //if ($marr_date OR $marr_place){
                    if ($marr_date) {
                        //$replacement_text=__('X').$dirmark1.' '.date_place($marr_date,$marr_place); }
                        $replacement_text = __('X') . $dirmark1 . ' ' . date_place($marr_date, '');
                    } else $replacement_text = __('X'); // if no details in the row we don't want the row to collapse
                }
                if ($box_appearance == 'ancestor_header') {
                    $replacement_text = '';
                    $replacement_text .= strip_tags($name2);
                    $replacement_text .= $dirmark2;
                }
            }
        }

        if ($hour_value !== '') { // called from hourglass
            if ($hour_value === '45') {
                $replacement_text = $name['name'];
            } elseif ($hour_value === '40') {
                $replacement_text = '<span class="wordwrap" style="font-size:75%">' . $name['short_firstname'] . '</span>';
            } elseif ($hour_value > 20 && $hour_value < 40) {
                $replacement_text = $name['initials'];
            } elseif ($hour_value < 25) {
                $replacement_text = "&nbsp;";
            }
            // if full scale (50) then the default of this function will be used: name with details
        }

        $extra_popup_text = '';
        $marr_date = '';
        if (isset($data["marr_date"][$id]) and ($data["marr_date"][$id] != '')) {
            $marr_date = $data["marr_date"][$id];
        }
        $marr_place = '';
        if (isset($data["marr_place"][$id]) and ($data["marr_place"][$id] != '')) {
            $marr_place = $data["marr_place"][$id];
        }
        if ($marr_date || $marr_place) {
            $extra_popup_text .= '<br>' . __('X') . $dirmark1 . ' ' . date_place($marr_date, $marr_place);
        }

        // *** Show picture by person ***
        if ($box_appearance != 'small' and $box_appearance != 'medium' and (strpos($box_appearance, "hour") === false or $box_appearance == "hour50")) {
            // *** Show picture ***
            if (!$pers_privacy and $user['group_pictures'] == 'j') {
                //  *** Path can be changed per family tree ***
                global $dataDb;
                $tree_pict_path = $dataDb->tree_pict_path;
                if (substr($tree_pict_path, 0, 1) == '|') $tree_pict_path = 'media/';
                $picture_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'picture');
                // *** Only show 1st picture ***
                if (isset($picture_qry[0])) {
                    $pictureDb = $picture_qry[0];
                    $showMedia = new ShowMedia();
                    $text .= $showMedia->print_thumbnail($tree_pict_path, $pictureDb->event_event, 80, 70, 'float:left; margin:5px;');
                }
            }
        }

        if ($box_appearance == 'ancestor_sheet_marr' || $box_appearance == 'ancestor_header') { // cause in that case there is no link
            $text .= $replacement_text;
        } else {
            $text .= $person_cls->person_popup_menu($personDb, true, $replacement_text, $extra_popup_text);

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
            //$text .= '<a href="'.$url.'"><span clas="nam" style="font-size:10px; color: #000000; text-decoration: none;">'.$replacement_text.'</span></a>';
        }
    }

    return $text . "\n";
}
