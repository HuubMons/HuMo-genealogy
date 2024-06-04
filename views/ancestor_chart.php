<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

if (!isset($hourglass)) {
    //TODO check if this is still needed
    //$data["main_person"] = 'I1'; // *** Default value, normally not used... ***
    //if (isset($_GET["id"])) {
    //    $data["main_person"] = $_GET["id"];
    //}
    //if (isset($_POST["id"])) {
    //    $data["main_person"] = $_POST["id"];
    //}

    // *** Check if person gedcomnumber is valid ***
    $db_functions->check_person($data["main_person"]);

    echo $data["ancestor_header"];
}

// The following is used for ancestor chart, ancestor sheet and ancestor sheet PDF (ASPDF)
// person 01
$personDb = $db_functions->get_person($data["main_person"]);
$gedcomnumber[1] = $personDb->pers_gedcomnumber;
$pers_famc[1] = $personDb->pers_famc;
$sexe[1] = $personDb->pers_sexe;
$parent_array[2] = '';
$parent_array[3] = '';
if ($pers_famc[1]) {
    $parentDb = $db_functions->get_family($pers_famc[1]);
    $parent_array[2] = $parentDb->fam_man;
    $parent_array[3] = $parentDb->fam_woman;
    $marr_date_array[2] = $parentDb->fam_marr_date;
    $marr_place_array[2] = $parentDb->fam_marr_place;
}
// end of person 1

// Loop to find person data
$count_max = 64;
// *** hourglass report ***
if (isset($hourglass) && $hourglass === true) {
    $count_max = pow(2, $data["chosengenanc"]);
}

for ($counter = 2; $counter < $count_max; $counter++) {
    $gedcomnumber[$counter] = '';
    $pers_famc[$counter] = '';
    $sexe[$counter] = '';
    if ($parent_array[$counter]) {
        $personDb = $db_functions->get_person($parent_array[$counter]);
        $gedcomnumber[$counter] = $personDb->pers_gedcomnumber;
        $pers_famc[$counter] = $personDb->pers_famc;
        $sexe[$counter] = $personDb->pers_sexe;
    }

    $Mcounter = $counter * 2;
    $Fcounter = $Mcounter + 1;
    $parent_array[$Mcounter] = '';
    $parent_array[$Fcounter] = '';
    $marr_date_array[$Mcounter] = '';
    $marr_place_array[$Mcounter] = '';
    if ($pers_famc[$counter]) {
        $parentDb = $db_functions->get_family($pers_famc[$counter]);
        $parent_array[$Mcounter] = $parentDb->fam_man;
        $parent_array[$Fcounter] = $parentDb->fam_woman;
        $marr_date_array[$Mcounter] = $parentDb->fam_marr_date;
        $marr_place_array[$Mcounter] = $parentDb->fam_marr_place;
    }
}

// *** Function to show data ***
// box_appearance (large, medium, small, and some other boxes...)
function ancestor_chart_person($id, $box_appearance)
{
    global $dbh, $db_functions, $tree_prefix_quoted, $humo_option, $user;
    global $marr_date_array, $marr_place_array;
    global $gedcomnumber, $language, $dirmark1, $dirmark2;

    $hour_value = ''; // if called from hourglass size of chart is given in box_appearance as "hour45" etc.
    if (strpos($box_appearance, "hour") !== false) {
        $hour_value = substr($box_appearance, 4);
    }

    $text = '';
    $popup = '';

    if ($gedcomnumber[$id]) {
        @$personDb = $db_functions->get_person($gedcomnumber[$id]);
        $person_cls = new person_cls($personDb);
        $pers_privacy = $person_cls->privacy;
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
                    if (isset($marr_date_array[$id]) and ($marr_date_array[$id] != '')) {
                        $marr_date = $marr_date_array[$id];
                    }
                    $marr_place = '';
                    if (isset($marr_place_array[$id]) and ($marr_place_array[$id] != '')) {
                        $marr_place = $marr_place_array[$id];
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
                    if (isset($marr_date_array[$id]) and ($marr_date_array[$id] != '')) {
                        $marr_date = $marr_date_array[$id];
                    }
                    $marr_place = '';
                    if (isset($marr_place_array[$id]) and ($marr_place_array[$id] != '')) {
                        $marr_place = $marr_place_array[$id];
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
        if (isset($marr_date_array[$id]) and ($marr_date_array[$id] != '')) {
            $marr_date = $marr_date_array[$id];
        }
        $marr_place = '';
        if (isset($marr_place_array[$id]) and ($marr_place_array[$id] != '')) {
            $marr_place = $marr_place_array[$id];
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
                    $picture = show_picture($tree_pict_path, $pictureDb->event_event, 80, 70);
                    //$text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'">';
                    $text .= '<img src="' . $picture['path'] . $picture['thumb'] . $picture['picture'] . '" style="float:left; margin:5px;" alt="' . $pictureDb->event_text . '" width="' . $picture['width'] . '">';
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
// *** End of function ancestor_chart_person ***

// Specific code for ancestor chart:
if (!isset($hourglass)) {

    $divlen = 1000;
    // width of the chart. for 6 generations 1000px is right
    // if we ever make the anc chart have optionally more generations, the width and length will have to be generated
    // as in report_descendant
?>

    <script src="include/html2canvas/html2canvas.min.js"></script>

    <!-- following div gets width and length in imaging java function showimg() (at bottom) otherwise double scrollbars won't work -->
    <style type="text/css">
        #doublescroll {
            position: relative;
            /* width: auto; */
            width: 1000px;
            height: 1100px;
            overflow: auto;
            overflow-y: hidden;
        }

        /*
        #doublescroll p {
            margin: 0;
            padding: 1em;
            white-space: nowrap;
        }
        */
    </style>

    <div style="text-align:center;">
        <input type="button" id="imgbutton" value="<?= __('Get image of chart for printing (allow popup!)'); ?>" onClick="showimg();" class="btn btn-sm btn-secondary">
    </div>

    <div id="png">
        <div id="doublescroll">
            <?php
            // *** First column name ***
            $left = 10;
            $sexe_colour = '';
            if ($sexe[1] == 'F') {
                $sexe_colour = ' ancestor_woman';
            }
            if ($sexe[1] == 'M') {
                $sexe_colour = ' ancestor_man';
            }
            ?>
            <!-- No _ character allowed in name of CSS class because of javascript -->
            <div class="ancestorName<?= $sexe_colour; ?>" align="left" style="top: 520px; left: <?= $left; ?>px; height: 80px; width:200px;">
                <?= ancestor_chart_person('1', 'large'); ?>
            </div>

            <?php
            $left = 50;
            $top = 320;
            // *** Second column split ***
            ?>
            <div class="ancestor_split" style="top: <?= $top; ?>px; left: <?= $left; ?>px; height: 199px"></div>
            <div class="ancestor_split" style="top: <?= ($top + 281); ?>px; left: <?= $left; ?>px; height: 199px"></div>
            <?php
            // *** Second column names ***
            for ($i = 1; $i < 3; $i++) {
                $sexe_colour = '';
                if ($sexe[$i + 1] == 'F') {
                    $sexe_colour = ' ancestor_woman';
                }
                if ($sexe[$i + 1] == 'M') {
                    $sexe_colour = ' ancestor_man';
                }
            ?>
                <div class="ancestorName<?= $sexe_colour; ?>" style="top: <?= (($top - 520) + ($i * 480)); ?>px; left: <?= ($left + 8); ?>px; height: 80px; width:200px;">
                    <?= ancestor_chart_person($i + 1, 'large'); ?>
                </div>
            <?php
            }

            $left = 80;
            $top = 199;
            // *** Third column split ***
            ?>
            <div class="ancestor_split" style="top: <?= $top; ?>px; left: <?= ($left + 32); ?>px; height: 80px;"></div>
            <?php
            echo '<div class="ancestor_split" style="top: ' . ($top + 162) . 'px; left: ' . ($left + 32) . 'px; height: 80px;"></div>';
            echo '<div class="ancestor_split" style="top: ' . ($top + 480) . 'px; left: ' . ($left + 32) . 'px; height: 80px;"></div>';
            echo '<div class="ancestor_split" style="top: ' . ($top + 642) . 'px; left: ' . ($left + 32) . 'px; height: 80px;"></div>';
            // *** Third column names ***
            for ($i = 1; $i < 5; $i++) {
                $sexe_colour = '';
                if ($sexe[$i + 3] == 'F') {
                    $sexe_colour = ' ancestor_woman';
                }
                if ($sexe[$i + 3] == 'M') {
                    $sexe_colour = ' ancestor_man';
                }
                echo '<div class="ancestorName' . $sexe_colour . '" style="top: ' . (($top - 279) + ($i * 240)) . 'px; left: ' . ($left + 40) . 'px; height: 80px; width:200px;">';
                echo ancestor_chart_person($i + 3, 'large');
                echo '</div>';
            }

            $left = 300;
            $top = -290;
            // *** Fourth column line ***
            for ($i = 1; $i < 3; $i++) {
                echo '<div class="ancestor_line" style="top: ' . ($top + ($i * 485)) . 'px; left: ' . ($left + 24) . 'px; height: 240px;"></div>';
            }
            // *** Fourth column split ***
            for ($i = 1; $i < 5; $i++) {
                echo '<div class="ancestor_split" style="top: ' . (($top + 185) + ($i * 240)) . 'px; left: ' . ($left + 32) . 'px; height: 120px;"></div>';
            }
            // *** Fourth column names ***
            for ($i = 1; $i < 9; $i++) {
                $sexe_colour = '';
                if ($sexe[$i + 7] == 'F') {
                    $sexe_colour = ' ancestor_woman';
                }
                if ($sexe[$i + 7] == 'M') {
                    $sexe_colour = ' ancestor_man';
                }
                echo '<div class="ancestorName' . $sexe_colour . '" style="top: ' . (($top + 265) + ($i * 120)) . 'px; left: ' . ($left + 40) . 'px; height: 80px; width:200px;">';
                echo ancestor_chart_person($i + 7, 'large');
                echo '</div>';
            }

            $left = 520;
            $top = -110;
            // *** Fifth column line ***
            for ($i = 1; $i < 5; $i++) {
                echo '<div class="ancestor_line" style="top: ' . ($top + ($i * 240)) . 'px; left: ' . ($left + 24) . 'px; height: 120px;"></div>';
            }
            // *** Fifth column split ***
            for ($i = 1; $i < 9; $i++) {
                echo '<div class="ancestor_split" style="top: ' . (($top + 90) + ($i * 120)) . 'px; left: ' . ($left + 32) . 'px; height: 60px;"></div>';
            }
            // *** Fifth column names ***
            for ($i = 1; $i < 17; $i++) {
                $sexe_colour = '';
                if ($sexe[$i + 15] == 'F') {
                    $sexe_colour = ' ancestor_woman';
                }
                if ($sexe[$i + 15] == 'M') {
                    $sexe_colour = ' ancestor_man';
                }
                echo '<div class="ancestorName' . $sexe_colour . '" style="top: ' . (($top + 125) + ($i * 60)) . 'px; left: ' . ($left + 40) . 'px; height: 50px; width:200px;">';
                echo ancestor_chart_person($i + 15, 'medium');
                echo '</div>';
            }

            $left = 740;
            $top = -20;
            // *** Last column line ***
            for ($i = 1; $i < 9; $i++) {
                echo '<div class="ancestor_line" style="top: ' . ($top + ($i * 120)) . 'px; left: ' . ($left + 24) . 'px; height: 60px;"></div>';
            }
            // *** Last column split ***
            for ($i = 1; $i < 17; $i++) {
                echo '<div class="ancestor_split" style="top: ' . (($top + 45) + ($i * 60)) . 'px; left: ' . ($left + 32) . 'px; height: 30px;"></div>';
            }
            // *** Last column names ***
            for ($i = 1; $i < 33; $i++) {
                $sexe_colour = '';
                if ($sexe[$i + 31] == 'F') {
                    $sexe_colour = ' ancestor_woman';
                }
                if ($sexe[$i + 31] == 'M') {
                    $sexe_colour = ' ancestor_man';
                }
                echo '<div class="ancestorName' . $sexe_colour . '" style="top: ' . (($top + 66) + ($i * 30)) . 'px; left: ' . ($left + 40) . 'px; height:16px; width:200px;">';
                echo ancestor_chart_person($i + 31, 'small');
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <?php
    // YB:
    // before creating the image we want to hide unnecessary items such as the help link, the menu box etc
    // we also have to set the width and height of the "png" div (this can't be set before because then the double scrollbars won't work
    // after generating the image, all those items are returned to their  previous state....
    // *** 19-08-2022: script updated by Huub ***
    echo '<script>';
    echo "
    function showimg() {
        /*   document.getElementById('helppopup').style.visibility = 'hidden';
        document.getElementById('menubox').style.visibility = 'hidden'; */
        document.getElementById('imgbutton').style.visibility = 'hidden';
        document.getElementById('png').style.width = '" . $divlen . "px';
        document.getElementById('png').style.height= 'auto';

        // *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
        const el = document.querySelectorAll('.ancestorName');
        el.forEach((elItem) => {
            //elItem.style.setProperty('border-radius', 'none', 'important');
            //elItem.style.setProperty('border-radius', '0px', 'important');
            elItem.style.setProperty('box-shadow', 'none', 'important');
        });

        //html2canvas( [ document.getElementById('png') ], {
        //	onrendered: function( canvas ) {

        html2canvas(document.querySelector('#png')).then(canvas => {
            var img = canvas.toDataURL();
            /*   document.getElementById('helppopup').style.visibility = 'visible';
            document.getElementById('menubox').style.visibility = 'visible'; */
            document.getElementById('imgbutton').style.visibility = 'visible';
            document.getElementById('png').style.width = 'auto';
            document.getElementById('png').style.height= 'auto';
            var newWin = window.open();
            newWin.document.open();
            newWin.document.write('<!DOCTYPE html><head></head><body>" . __('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ') . "<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>" . __('If you have a plotter you can use its software to print the image on one large sheet.') . "<br><br><img src=\"' + img + '\"></body></html>');
            newWin.document.close();
            }
        //}
        );
    }
    ";
    echo '</script>';
    ?>

    <!-- Doublescroll doesn't work anymore. For now disabled.
    <script>
        function DoubleScroll(element) {
            var scrollbar = document.createElement('div');
            scrollbar.appendChild(document.createElement('div'));
            scrollbar.style.overflow = 'auto';
            scrollbar.style.overflowY = 'hidden';
            scrollbar.firstChild.style.width = element.scrollWidth + 'px';
            scrollbar.firstChild.style.paddingTop = '1px';
            scrollbar.firstChild.style.height = '20px';
            scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
            scrollbar.onscroll = function() {
                element.scrollLeft = scrollbar.scrollLeft;
            };
            element.onscroll = function() {
                scrollbar.scrollLeft = element.scrollLeft;
            };
            element.parentNode.insertBefore(scrollbar, element);
        }

        DoubleScroll(document.getElementById('doublescroll'));
    </script>
    -->

<?php
}   // end of ancestor CHART code