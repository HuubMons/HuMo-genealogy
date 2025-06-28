<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

$screen_mode = 'ancestor_sheet';

//$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix
// see end of this code 

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);


// *** Function to show data ***
// box_appearance (large, medium, small, and some other boxes...)
function ancestor_chart_person($id, $box_appearance)
{
    global $db_functions, $user, $data, $dirmark1, $dirmark2;

    $person_name = new PersonName();
    $person_privacy = new PersonPrivacy();
    $person_popup = new PersonPopup();
    $date_place = new DatePlace();

    $hour_value = ''; // if called from hourglass size of chart is given in box_appearance as "hour45" etc.
    if (strpos($box_appearance, "hour") !== false) {
        $hour_value = substr($box_appearance, 4);
    }

    $text = '';

    if ($data["gedcomnumber"][$id]) {
        $personDb = $db_functions->get_person($data["gedcomnumber"][$id]);
        $pers_privacy = $person_privacy->get_privacy($personDb);
        $name = $person_name->get_person_name($personDb, $pers_privacy);
        $name2 = $dirmark2 . $name["name"] . $name["colour_mark"] . $dirmark2;

        // *** Replace pop-up icon by a text box ***
        $replacement_text = '<b>' . $name2 . '</b>';

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
                    //$replacement_text.='<br>'.__('*').$dirmark1.' '.$date_place->date_place($personDb->pers_birth_date,$personDb->pers_birth_place); }
                    $replacement_text .= '<br>' . __('*') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_birth_date, '');
                }
                //elseif ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
                elseif ($personDb->pers_bapt_date) {
                    //$replacement_text.='<br>'.__('~').$dirmark1.' '.$date_place->date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place); }
                    $replacement_text .= '<br>' . __('~') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_bapt_date, '');
                }

                //if ($personDb->pers_death_date OR $personDb->pers_death_place){
                if ($personDb->pers_death_date) {
                    //$replacement_text.='<br>'.__('&#134;').$dirmark1.' '.$date_place->date_place($personDb->pers_death_date,$personDb->pers_death_place); }
                    $replacement_text .= '<br>' . __('&#134;') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_death_date, '');
                }
                //elseif ($personDb->pers_buried_date OR $personDb->pers_buried_place){
                elseif ($personDb->pers_buried_date) {
                    //$replacement_text.='<br>'.__('[]').$dirmark1.' '.$date_place->date_place($personDb->pers_buried_date,$personDb->pers_buried_place); }
                    $replacement_text .= '<br>' . __('[]') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_buried_date, '');
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
                        //$replacement_text.='<br>'.__('X').$dirmark1.' '.$date_place->date_place($marr_date,$marr_place); }
                        $replacement_text .= '<br>' . __('X') . $dirmark1 . ' ' . $date_place->date_place($marr_date, '');
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
                        //$replacement_text=__('X').$dirmark1.' '.$date_place->date_place($marr_date,$marr_place); }
                        $replacement_text = __('X') . $dirmark1 . ' ' . $date_place->date_place($marr_date, '');
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
        if (isset($data["marr_date"][$id]) && $data["marr_date"][$id] != '') {
            $marr_date = $data["marr_date"][$id];
        }
        $marr_place = '';
        if (isset($data["marr_place"][$id]) and ($data["marr_place"][$id] != '')) {
            $marr_place = $data["marr_place"][$id];
        }
        if ($marr_date || $marr_place) {
            $extra_popup_text .= '<br>' . __('X') . $dirmark1 . ' ' . $date_place->date_place($marr_date, $marr_place);
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

        if ($box_appearance == 'ancestor_sheet_marr' or $box_appearance == 'ancestor_header') { // cause in that case there is no link
            $text .= $replacement_text;
        } else {
            $text .= $person_popup->person_popup_menu($personDb, $pers_privacy, true, $replacement_text, $extra_popup_text);

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$person_link = new PersonLink();
            //$url=$person_link->get_person_link($personDb);
            //$text .= '<a href="'.$url.'"><span clas="nam" style="font-size:10px; color: #000000; text-decoration: none;">'.$replacement_text.'</span></a>';
        }
    }
    return $text . "\n";
}

// Specific code for ancestor SHEET:
// print names and details for each row in the table
function kwname($start, $end, $increment, $fontclass, $colspan, $type)
{
    global $data;
?>

    <tr>
        <?php
        for ($x = $start; $x < $end; $x += $increment) {
            // *** Added coloured boxes in november 2022 ***
            $sexe_colour = '';
            if ($type != 'ancestor_sheet_marr') {
                if ($data["sexe"][$x] == 'F') {
                    $sexe_colour = ' style="background-image: linear-gradient(to bottom, #FFFFFF 0%, #F5BCA9 100%);"';
                }
                if ($data["sexe"][$x] == 'M') {
                    $sexe_colour = ' style="background-image: linear-gradient(to bottom, #FFFFFF 0%, #81BEF7 100%);"';
                }
            }

        ?>
            <td <?= $colspan > 1 ? 'colspan=' . $colspan : ''; ?><?= $sexe_colour; ?> class="<?= $fontclass; ?>">
                <?php
                $kwpers = ancestor_chart_person($x, $type);
                if ($kwpers != '') {
                    echo $kwpers;
                } else {   // if we don't do this IE7 wil not print borders of cells
                    echo '&nbsp;';
                }
                ?>
            </td>
        <?php } ?>
    </tr>
<?php
}

// check if there is anyone in a generation so no empty and collapsed rows will be shown
function check_gen($start, $end)
{
    global $data;
    $is_gen = 0;
    for ($i = $start; $i < $end; $i++) {
        if (isset($data["gedcomnumber"][$i]) && $data["gedcomnumber"][$i] != '') {
            $is_gen = 1;
        }
    }
    return $is_gen;
}
?>

<?= $data["ancestor_header"]; ?>

<table class="ancestor_sheet">
    <tr>
        <th class="ancestor_head" colspan="8">
            <?= __('Ancestor sheet') . __(' of ') . ancestor_chart_person(1, "ancestor_header"); ?>

            <!-- Show pdf button -->
            <?php if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") { ?>
                <?php
                if ($humo_option["url_rewrite"] == "j") {
                    $link = $uri_path . 'ancestor_sheet_pdf/' . $tree_id . '?main_person=' . $data["main_person"];
                } else {
                    $link = $uri_path . 'index.php?page=ancestor_sheet_pdf&amp;tree_id=' . $tree_id . '&amp;main_person=' . $data["main_person"];
                }
                ?>
                &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;">
                    <input type="hidden" name="screen_mode" value="ASPDF">
                    <input type="submit" class="btn btn-sm btn-info" value="<?= __('PDF'); ?>" name="submit">
                </form>
            <?php } ?>
        </th>
    </tr>

    <?php
    $gen = 0;
    $gen = check_gen(16, 32);
    if ($gen == 1) {
        kwname(16, 32, 2, "kw-small", 1, "medium");
        kwname(16, 32, 2, "kw-small", 1, "ancestor_sheet_marr");
        kwname(17, 33, 2, "kw-small", 1, "medium");
        echo '<tr><td colspan=8 class="ancestor_devider">&nbsp;</td></tr>';
    }
    $gen = 0;
    $gen = check_gen(8, 16);
    if ($gen == 1) {
        kwname(8, 16, 1, "kw-bigger", 1, "medium");
        kwname(8, 16, 2, "kw-small", 2, "ancestor_sheet_marr");
    }
    $gen = 0;
    $gen = check_gen(4, 8);
    if ($gen == 1) {
        kwname(4, 8, 1, "kw-medium", 2, "medium");
        kwname(4, 8, 2, "kw-small", 4, "ancestor_sheet_marr");
    }
    kwname(2, 4, 1, "kw-big", 4, "medium");
    kwname(2, 4, 2, "kw-small", 8, "ancestor_sheet_marr");
    kwname(1, 2, 1, "kw-big", 8, "medium");
    ?>
</table>

<br>
<div class="ancestor_legend">
    <b><?= __('Legend'); ?></b><br>
    <?= __('*') . '  ' . __('born') . ', ' . __('&#134;') . '  ' . __('died') . ', ' . __('X') . '  ' . __('married'); ?><br>
    <?php printf(__('Generated with %s on %s'), 'HuMo-genealogy', date("d M Y - H:i")); ?>
</div>
<br>