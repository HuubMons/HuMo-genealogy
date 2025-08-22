<?php

/**
 * AncestorBox class for displaying ancestor information in various formats.
 * box_appearance (large, medium, small, and some other boxes...)
 * Used in ancestor chart, ancestor sheet and hourglass scripts
 */

namespace Genealogy\Include;

class AncestorBox
{
    /*
    private $db_functions, $user, $data, $selectedFamilyTree, $language, $screen_mode;
    function __construct($db_functions, $user, $data, $selectedFamilyTree, $language, $screen_mode)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
        $this->data = $data;
        $this->selectedFamilyTree = $selectedFamilyTree;
        $this->language = $language;
        $this->screen_mode = $screen_mode;
    }
    */

    function ancestorBox($id, $box_appearance, $ancestor_sheet = '')
    {
        global $db_functions, $user, $data, $selectedFamilyTree, $language, $screen_mode;

        $directionMarkers = new \Genealogy\Include\DirectionMarkers($language["dir"], $screen_mode);
        $personName = new \Genealogy\Include\PersonName();
        $personPrivacy = new \Genealogy\Include\PersonPrivacy();
        $personPopup = new \Genealogy\Include\PersonPopup();
        $datePlace = new \Genealogy\Include\DatePlace();

        $hour_value = ''; // if called from hourglass size of chart is given in box_appearance as "hour45" etc.
        if (strpos($box_appearance, "hour") !== false) {
            $hour_value = substr($box_appearance, 4);
        }

        $text = '';

        if ($data["gedcomnumber"][$id]) {
            $personDb = $db_functions->get_person($data["gedcomnumber"][$id]);
            $pers_privacy = $personPrivacy->get_privacy($personDb);
            $name = $personName->get_person_name($personDb, $pers_privacy);
            $name2 = $directionMarkers->dirmark2 . $name["name"] . $name["colour_mark"] . $directionMarkers->dirmark2;

            // *** Replace pop-up icon by a text box ***
            $replacement_text = '<span class="anc_box_name' . $ancestor_sheet . '">' . $name2 . '</span>';

            // >>>>> link to show rest of ancestor chart
            if ($box_appearance == 'small' && isset($personDb->pers_gedcomnumber) && $personDb->pers_famc) {
                $replacement_text .= ' &gt;&gt;&gt;' . $directionMarkers->dirmark1;
            }

            if ($pers_privacy) {
                if ($box_appearance != 'ancestor_sheet_marr') {
                    $replacement_text .= '<br>' . __(' PRIVACY FILTER'); //Tekst privacy weergeven
                } else {
                    $replacement_text = __(' PRIVACY FILTER');
                }
            } else {
                if ($box_appearance != 'small') {
                    //if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
                    if ($personDb->pers_birth_date) {
                        //$replacement_text.='<br>'.__('*').$directionMarkers->dirmark1.' '.$datePlace->date_place($personDb->pers_birth_date,$personDb->pers_birth_place); }
                        $replacement_text .= '<br>' . __('*') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($personDb->pers_birth_date, '');
                    }
                    //elseif ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
                    elseif ($personDb->pers_bapt_date) {
                        //$replacement_text.='<br>'.__('~').$directionMarkers->dirmark1.' '.$datePlace->date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place); }
                        $replacement_text .= '<br>' . __('~') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($personDb->pers_bapt_date, '');
                    }

                    //if ($personDb->pers_death_date OR $personDb->pers_death_place){
                    if ($personDb->pers_death_date) {
                        //$replacement_text.='<br>'.__('&#134;').$directionMarkers->dirmark1.' '.$datePlace->date_place($personDb->pers_death_date,$personDb->pers_death_place); }
                        $replacement_text .= '<br>' . __('&#134;') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($personDb->pers_death_date, '');
                    }
                    //elseif ($personDb->pers_buried_date OR $personDb->pers_buried_place){
                    elseif ($personDb->pers_buried_date) {
                        //$replacement_text.='<br>'.__('[]').$directionMarkers->dirmark1.' '.$datePlace->date_place($personDb->pers_buried_date,$personDb->pers_buried_place); }
                        $replacement_text .= '<br>' . __('[]') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($personDb->pers_buried_date, '');
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
                            //$replacement_text.='<br>'.__('X').$directionMarkers->dirmark1.' '.$datePlace->date_place($marr_date,$marr_place); }
                            $replacement_text .= '<br>' . __('X') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($marr_date, '');
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
                            //$replacement_text=__('X').$directionMarkers->dirmark1.' '.$datePlace->date_place($marr_date,$marr_place); }
                            $replacement_text = __('X') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($marr_date, '');
                        } else $replacement_text = __('X'); // if no details in the row we don't want the row to collapse
                    }
                    if ($box_appearance == 'ancestor_header') {
                        $replacement_text = '';
                        $replacement_text .= strip_tags($name2);
                        $replacement_text .= $directionMarkers->dirmark2;
                    }
                }
            }

            if ($hour_value !== '') {
                // called from hourglass
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
                $extra_popup_text .= '<br>' . __('X') . $directionMarkers->dirmark1 . ' ' . $datePlace->date_place($marr_date, $marr_place);
            }

            // *** Show picture by person ***
            if ($box_appearance != 'small' and $box_appearance != 'medium' and (strpos($box_appearance, "hour") === false or $box_appearance == "hour50")) {
                // *** Show picture ***
                if (!$pers_privacy and $user['group_pictures'] == 'j') {
                    // *** Path can be changed per family tree ***
                    $tree_pict_path = $selectedFamilyTree->tree_pict_path;
                    if (substr($tree_pict_path, 0, 1) == '|') {
                        $tree_pict_path = 'media/';
                    }
                    $picture_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'picture');
                    // *** Only show 1st picture ***
                    if (isset($picture_qry[0])) {
                        $pictureDb = $picture_qry[0];
                        $showMedia = new \Genealogy\Include\ShowMedia();
                        $text .= $showMedia->print_thumbnail($tree_pict_path, $pictureDb->event_event, 80, 70, 'float:left; margin:5px;');
                    }
                }
            }

            if ($box_appearance == 'ancestor_sheet_marr' || $box_appearance == 'ancestor_header') {
                // cause in that case there is no link
                $text .= $replacement_text;
            } else {
                if (!$extra_popup_text) {
                    $extra_popup_text = '&nbsp;';
                }
                $text .= $personPopup->person_popup_menu($personDb, $pers_privacy, true, $replacement_text, $extra_popup_text);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                //$personLink = new PersonLink();
                //$url = $personLink->get_person_link($personDb);
                //$text .= '<a href="'.$url.'"><span clas="nam" style="font-size:10px; color: #000000; text-decoration: none;">'.$replacement_text.'</span></a>';
            }
        }

        return $text . "\n";
    }
}
