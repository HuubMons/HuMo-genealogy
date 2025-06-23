<?php
class PersonPopup
{

    /**
     * Show person pop-up menu
     * 
     * $extended=true; Show a full persons pop-up including picture and person data
     * $replacement_text='text'; Replace the pop-up icon by the replacement_text
     * $extra_pop-up_text=''; To add extra text in the pop-up screen
     */

    private $date_place;

    public function __construct()
    {
        $this->date_place = new DatePlace;
    }

    public function person_popup_menu($personDb, $privacy, $extended = false, $replacement_text = '', $extra_popup_text = '')
    {
        global $db_functions, $bot_visit, $humo_option, $uri_path, $user;
        global $screen_mode, $dirmark1, $dirmark2, $rtlmarker, $hourglass, $link_cls, $page;

        $person_link = new PersonLink;
        $person_name = new PersonName;
        $date_place = $this->date_place;

        include_once(__DIR__ . "/../admin/include/media_inc.php");

        $text_start = '';
        $text = '';
        $popover_content = '';

        // *** Show pop-up menu ***
        if (!$bot_visit && $screen_mode != "PDF" && $screen_mode != "RTF") {

            // *** Family tree for search in multiple family trees ***
            $db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $start_url = $person_link->get_person_link($personDb);
            $family_url = $start_url;

            // *** Link to own family or parents ***
            $pers_family = '';
            if ($personDb->pers_famc) {
                $pers_family = $personDb->pers_famc;
            }
            if ($personDb->pers_fams) {
                $pers_fams = explode(';', $personDb->pers_fams);
                $pers_family = $pers_fams[0];
            }

            // *** Change start url for a person in a graphical ancestor report ***
            if ($screen_mode == 'ancestor_chart' && $hourglass === false) {
                $vars['id'] = $personDb->pers_gedcomnumber;
                $start_url = $link_cls->get_link($uri_path, 'ancestor_report', $personDb->pers_tree_id, true, $vars);
                $start_url .= 'screen_mode=ancestor_chart';
            }

            $text_start .= '<div class="' . $rtlmarker . 'sddm" style="display:inline;">' . "\n";

            $text_start .= '<a href="' . $start_url . '"';
            if ($extended) {
                if ($page == 'ancestor_sheet') {
                    $text_start .= ' class="nam" style="z-index:100;display:block; width:100%; height:100%" ';
                } else {
                    $text_start .= ' class="nam" style="z-index:100;font-size:10px; display:block; width:100%; height:100%" ';
                }
            }

            $random_nr = rand(); // *** Generate a random number to avoid double numbers ***
            $text_start .= ' onmouseover="mopen(event,\'m1' . $random_nr . $personDb->pers_gedcomnumber . '\',0,0)"';
            $text_start .= ' onmouseout="mclosetime()">';
            if ($replacement_text) {
                $text_start .= $replacement_text;
            } else {
                $text_start .= '<img src="images/reports.gif" border="0" alt="reports">';
            }
            $text_start .= '</a>';

            // *** Added style="z-index:40;" for ancestor and descendant report ***
            $text_start .= '<div style="z-index:500; border:1px solid #999999;" id="m1' . $random_nr . $personDb->pers_gedcomnumber .
                '" class="sddm_fixed" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

            $name = $person_name->get_person_name($personDb, $privacy);
            $text .= $dirmark2 . '<span style="font-size:13px;"><b>' . $name["standard_name"] . $name["colour_mark"] . '</b></span><br>';
            $popover_content .= '<li><span style="font-size:13px;"><b>' . $name["standard_name"] . $name["colour_mark"] . '</b></span></li>';
            if ($extended) {
                $text .= '<table><tr><td style="width:auto; border: solid 0px; border-right:solid 1px #999999;">';
            }

            // *** If child doesn't have own family, directly jump to child in familyscreen using #child_I1234 ***
            $direct_link = '';
            if ($personDb->pers_fams == '') {
                $direct_link = '#person_' . $personDb->pers_gedcomnumber;
            }
            $text .= $dirmark1 . '<a href="' . $family_url . $direct_link . '"><img src="images/family.gif" border="0" alt="' . __('Family group sheet') . '"> ' . __('Family group sheet') . '</a>';
            $popover_content .=  '<li><a href="' . $family_url . $direct_link . '"><img src="images/family.gif" border="0" alt="' . __('Family group sheet') . '"> ' . __('Family group sheet') . '</a></li>';

            if ($user['group_gen_protection'] == 'n' && $personDb->pers_fams != '') {
                // *** Only show a descendant_report icon if there are children ***
                $check_children = false;
                $check_family = explode(";", $personDb->pers_fams);
                foreach ($check_family as $i => $value) {
                    $check_childrenDb = $db_functions->get_family($check_family[$i]);
                    if ($check_childrenDb->fam_children) {
                        $check_children = true;
                    }
                }
                if ($check_children) {
                    $vars['pers_family'] = $pers_family;
                    $path_tmp = $link_cls->get_link($uri_path, 'family', $personDb->pers_tree_id, true, $vars);
                    $path_tmp .= "main_person=" . $personDb->pers_gedcomnumber . '&amp;descendant_report=1';
                    $text .= '<a href="' . $path_tmp . '" rel="nofollow"><img src="images/descendant.gif" border="0" alt="' . __('Descendants') . '"> ' . __('Descendants') . '</a>';
                    $popover_content .= '<li><a href="' . $path_tmp . '" rel="nofollow"><img src="images/descendant.gif" border="0" alt="' . __('Descendants') . '"> ' . __('Descendants') . '</a></li>';
                }
            }

            if ($user['group_gen_protection'] == 'n' && $personDb->pers_famc != '') {
                // == Ancestor report: link & icons by Klaas de Winkel ==
                $vars['id'] = $personDb->pers_gedcomnumber;
                $path_tmp = $link_cls->get_link($uri_path, 'ancestor_report', $personDb->pers_tree_id, false, $vars);
                $text .= '<a href="' . $path_tmp . '" rel="nofollow"><img src="images/ancestor_report.gif" border="0" alt="' . __('Ancestor report') . '"> ' . __('Ancestors') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '" rel="nofollow"><img src="images/ancestor_report.gif" border="0" alt="' . __('Ancestor report') . '"> ' . __('Ancestors') . '</a></li>';
            }

            // check for timeline folder and tml files
            if (!$privacy) {
                $tmldates = 0;
                if (
                    $personDb->pers_birth_date || $personDb->pers_bapt_date || $personDb->pers_death_date || $personDb->pers_buried_date || $personDb->pers_fams
                ) {
                    $tmldates = 1;
                }
                if ($user['group_gen_protection'] == 'n' && $tmldates == 1) {
                    $vars['pers_gedcomnumber'] = $personDb->pers_gedcomnumber;
                    $path_tmp = $link_cls->get_link($uri_path, 'timeline', $personDb->pers_tree_id, false, $vars);
                    $text .= '<a href="' . $path_tmp . '" rel="nofollow"><img src="images/timeline.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Timeline') . '</a>';
                    $popover_content .= '<li><a href="' . $path_tmp . '" rel="nofollow"><img src="images/timeline.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Timeline') . '</a></li>';
                }
            }

            if ($user["group_relcalc"] == 'j') {
                $relpath = $link_cls->get_link($uri_path, 'relations', $personDb->pers_tree_id, true);
                $text .= '<a href="' . $relpath . 'pers_id=' . $personDb->pers_id . '" rel="nofollow"><img src="images/relcalc.gif" border="0" alt="' . __('Relationship calculator') . '"> ' . __('Relationship calculator') . '</a>';
                $popover_content .= '<li><a href="' . $relpath . 'pers_id=' . $personDb->pers_id . '" rel="nofollow"><img src="images/relcalc.gif" border="0" alt="' . __('Relationship calculator') . '"> ' . __('Relationship calculator') . '</a></li>';
            }

            // DNA charts
            if ($user['group_gen_protection'] == 'n' and ($personDb->pers_famc != "" or ($personDb->pers_fams != "" and $check_children))) {
                if ($personDb->pers_sexe == "M") $charttype = "ydna";
                else $charttype = "mtdna";
                if ($humo_option["url_rewrite"] == 'j') {
                    $path_tmp = 'descendant_chart/' . $personDb->pers_tree_id . '/' . $pers_family . '?main_person=' . $personDb->pers_gedcomnumber . '&amp;dnachart=' . $charttype;
                } else {
                    $path_tmp = 'index.php?page=descendant_chart&amp;tree_id=' . $personDb->pers_tree_id . '&amp;id=' . $pers_family . '&amp;main_person=' . $personDb->pers_gedcomnumber . '&amp;dnachart=' . $charttype;
                }
                $text .= '<a href="' . $path_tmp . '" rel="nofollow"><img src="images/dna.png" border="0" alt="' . __('DNA Charts') . '"> ' . __('DNA Charts') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '" rel="nofollow"><img src="images/dna.png" border="0" alt="' . __('DNA Charts') . '"> ' . __('DNA Charts') . '</a></li>';
            }

            if ($user['group_gen_protection'] == 'n' && $personDb->pers_famc != '' && $personDb->pers_fams != '' && $check_children) {
                // hourglass only if there is at least one generation of ancestors and of children.
                $vars['pers_family'] = $pers_family;
                $path_tmp = $link_cls->get_link($uri_path, 'hourglass', $personDb->pers_tree_id, true, $vars);
                $path_tmp .= "main_person=" . $personDb->pers_gedcomnumber . '&amp;screen_mode=HOUR';
                $text .= '<a href="' . $path_tmp . '" rel="nofollow"><img src="images/hourglass.gif" border="0" alt="' . __('Hourglass chart') . '"> ' . __('Hourglass chart') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '" rel="nofollow"><img src="images/hourglass.gif" border="0" alt="' . __('Hourglass chart') . '"> ' . __('Hourglass chart') . '</a></li>';
            }

            // *** Editor link ***
            if ($user['group_edit_trees'] || $user['group_admin'] == 'j') {
                $edit_tree_array = explode(";", $user['group_edit_trees']);
                // *** Administrator can always edit in all family trees ***
                if ($user['group_admin'] == 'j' || in_array($_SESSION['tree_id'], $edit_tree_array)) {
                    $path_tmp = 'admin/index.php?page=editor&amp;menu_tab=person&amp;tree_id=' . $personDb->pers_tree_id . '&amp;person=' . $personDb->pers_gedcomnumber;
                    $text .= '<b>' . __('Admin') . ':</b>';
                    $popover_content .= '<li><b>' . __('Admin') . ':</b></li>';

                    $text .= '<a href="' . $path_tmp . '" target="_blank"><img src="images/person_edit.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Editor') . '</a>';
                    $popover_content .= '<li><a href="' . $path_tmp . '" target="_blank" rel="nofollow"><img src="images/person_edit.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Editor') . '</a></li>';
                }
            }

            // *** Show person picture and person data at right side of the pop-up box ***
            if ($extended) {
                $text .= '</td><td style="width:auto; border: solid 0px; font-size: 10px;" valign="top">';

                // *** Show picture in pop-up box ***
                if (!$privacy && $user['group_pictures'] == 'j') {
                    //  *** Path can be changed per family tree ***
                    global $dataDb;
                    $tree_pict_path = $dataDb->tree_pict_path;
                    if (substr($tree_pict_path, 0, 1) === '|') {
                        $tree_pict_path = 'media/';
                    }
                    $picture_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'picture');
                    // *** Only show 1st picture ***
                    if (isset($picture_qry[0])) {
                        $pictureDb = $picture_qry[0];
                        $showMedia = new ShowMedia;
                        $text .= $showMedia->print_thumbnail($tree_pict_path, $pictureDb->event_event, 0, 120, 'margin-left:10px; margin-top:5px;') . '<br>';

                        //$picture = show_picture($tree_pict_path, $pictureDb->event_event, '', 120);
                        //$text .= '<img src="' . $picture['path'] . $picture['thumb_prefix'] . $picture['picture'] . $picture['thumb_suffix'] . '" style="margin-left:10px; margin-top:5px;" alt="' . $pictureDb->event_text . '" height="' . $picture['height'] . '"><br>';
                        //$popover_content .= '<img src="' . $picture['path'] . $picture['thumb_prefix'] . $picture['picture'] . '" style="margin-left:10px; margin-top:5px;" alt="' . $pictureDb->event_text . '" height="' . $picture['height'] . '"><br>';
                    }
                }

                // *** Pop-up tekst ***
                if (!$privacy) {
                    if ($personDb->pers_birth_date || $personDb->pers_birth_place) {
                        $text .= __('*') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_birth_date, $personDb->pers_birth_place);
                    } elseif ($personDb->pers_bapt_date || $personDb->pers_bapt_place) {
                        $text .= __('~') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_bapt_date, $personDb->pers_bapt_place);
                    }

                    if ($personDb->pers_death_date || $personDb->pers_death_place) {
                        $text .= '<br>' . __('&#134;') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_death_date, $personDb->pers_death_place);
                    } elseif ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                        $text .= '<br>' . __('[]') . $dirmark1 . ' ' . $date_place->date_place($personDb->pers_buried_date, $personDb->pers_buried_place);
                    }

                    // *** If needed add extra text in the pop-up box ***
                    if ($extra_popup_text) {
                        $text .= '<br><br>' . $extra_popup_text;
                    }
                } else {
                    $text .= ' ' . __('PRIVACY FILTER');
                }

                $text .= '</td></tr></table>';
            }

            $text = $text_start . $text;
            $text .= $dirmark1 . '</div>';
            $text .= '</div>' . "\n";

            // *** Use dropdown button in standard family pages ***
            // TODO Check outline report (now disabled). text-indent: -1.5em;
            if ($page != 'descendant_chart' and $page != 'ancestor_chart' and $page != 'hourglass' and $page != 'ancestor_sheet' and $page != 'outline_report') {
                if ($replacement_text) {
                    $popover_text = $replacement_text;
                } else {
                    $popover_text = '<img src="images/reports.gif" border="0" alt="reports">';
                }
                $text = '<div class="dropdown dropend d-inline">';
                //$text .= '<button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-line-height: .5;">' . $popover_text . '</button>';
                $text .= '<button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-line-height: .5;">' . $popover_text . '</button>';
                $text .= '<ul class="dropdown-menu p-2" style="width:260px;">';
                $text .= $popover_content;
                $text .= '</ul>';
                $text .= '</div>';
            }
        }

        return $text;
    }
}
