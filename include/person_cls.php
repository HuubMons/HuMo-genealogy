<?php

/**
 * Process person data
 * Class for HuMo-genealogy program
 * $templ_person is used for PDF reports
 */

class person_cls
{
    public $personDb = null;  // Database record
    public $privacy = false;  // Person privacy

    // *** Jul 2023: new constructor ***
    public function __construct($personDb = null)
    {
        $this->personDb = $personDb;    // Database record        
        $this->privacy = $this->set_privacy($personDb); // Set privacy
    }

    // ***************************************************
    // *** Privacy person                              ***
    // ***************************************************
    public function set_privacy($personDb)
    {
        global $user, $dataDb;
        $privacy_person = false;  // *** Standard: show all persons ***
        if ($user['group_privacy'] == 'n') {
            $privacy_person = true;  // *** Standard: filter privacy data of person ***
            // *** $personDb is empty by N.N. person ***
            if ($personDb) {
                // *** HuMo-genealogy, Haza-data and Aldfaer alive/ deceased status ***

                if ($user['group_alive'] == "j") {
                    if ($personDb->pers_alive == 'deceased') {
                        $privacy_person = false;
                    }
                    if ($personDb->pers_alive == 'alive') {
                        $privacy_person = true;
                    }
                }

                // *** Privacy filter: date ***
                if ($user["group_alive_date_act"] == "j") {
                    if ($personDb->pers_birth_date) {
                        if (substr($personDb->pers_birth_date, -2) === "BC") {
                            $privacy_person = false;
                        }  // born before year 0
                        elseif (substr($personDb->pers_birth_date, -2, 1) === " " || substr($personDb->pers_birth_date, -3, 1) === " ") {
                            $privacy_person = false;
                        }  // born between year 0 and 99
                        elseif (substr($personDb->pers_birth_date, -4) < $user["group_alive_date"]) {
                            $privacy_person = false;
                        }  // born from year 100 onwards but before $user["group_alive_date"]
                        else {
                            $privacy_person = true;
                        } // *** overwrite pers_alive status ***
                    }
                    if ($personDb->pers_bapt_date) {
                        if (substr($personDb->pers_bapt_date, -2) === "BC") {
                            $privacy_person = false;
                        }  // baptized before year 0
                        elseif (substr($personDb->pers_bapt_date, -2, 1) === " " || substr($personDb->pers_bapt_date, -3, 1) === " ") {
                            $privacy_person = false;
                        }  // baptized between year 0 and 99
                        elseif (substr($personDb->pers_bapt_date, -4) < $user["group_alive_date"]) {
                            $privacy_person = false;
                        }  // baptized from year 100 onwards but before $user["group_alive_date"]
                        else {
                            $privacy_person = true;
                        } // *** overwrite pers_alive status ***
                    }
                    if ($personDb->pers_cal_date) {
                        if (substr($personDb->pers_cal_date, -2) === "BC") {
                            $privacy_person = false;
                        }  // calculated born before year 0
                        elseif (substr($personDb->pers_cal_date, -2, 1) === " " || substr($personDb->pers_cal_date, -3, 1) === " ") {
                            $privacy_person = false;
                        }  // calculated born between year 0 and 99
                        elseif (substr($personDb->pers_cal_date, -4) < $user["group_alive_date"]) {
                            $privacy_person = false;
                        }  // calculated born from year 100 onwards but before $user["group_alive_date"]
                        else {
                            $privacy_person = true;
                        } // *** overwrite pers_alive status ***
                    }

                    // *** Check if deceased persons should be filtered ***
                    if ($user["group_filter_death"] == 'n') {
                        // *** If person is deceased, filter is off ***
                        if ($personDb->pers_death_date || $personDb->pers_death_place) {
                            $privacy_person = false;
                        }
                        if ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                            $privacy_person = false;
                        }
                        // *** pers_alive for deceased persons without date ***
                        if ($personDb->pers_alive == 'deceased') {
                            $privacy_person = false;
                        }
                    }
                }

                // *** Privacy filter: date ***
                if ($user["group_death_date_act"] == "j") {
                    if ($personDb->pers_death_date) {
                        if (substr($personDb->pers_death_date, -2) === "BC") {
                            $privacy_person = false;
                        } // person died BC
                        elseif (substr($personDb->pers_death_date, -2, 1) === " " || substr($personDb->pers_death_date, -3, 1) === " ") {
                            $privacy_person = false;
                        } // person died between year 0 and 99
                        elseif (substr($personDb->pers_death_date, -4) < $user["group_death_date"]) {
                            $privacy_person = false;
                        } // person died after year 100 until $user["group_death_date"]
                        else {
                            $privacy_person = true;
                        } // *** overwrite pers_alive status ***
                    }
                    if ($personDb->pers_buried_date) {
                        if (substr($personDb->pers_buried_date, -2) === "BC") {
                            $privacy_person = false;
                        } // person buried BC
                        elseif (substr($personDb->pers_buried_date, -2, 1) === " " || substr($personDb->pers_buried_date, -3, 1) === " ") {
                            $privacy_person = false;
                        } // person buried between year 0 and 99
                        elseif (substr($personDb->pers_buried_date, -4) < $user["group_death_date"]) {
                            $privacy_person = false;
                        } // person buried after year 100 until $user["group_death_date"]
                        else {
                            $privacy_person = true;
                        } // *** overwrite pers_alive status ***
                    }
                }


                // *** Filter person's WITHOUT any date's ***
                if ($user["group_filter_date"] == 'j' && ($personDb->pers_birth_date == '' && $personDb->pers_bapt_date == '' && $personDb->pers_death_date == '' && $personDb->pers_buried_date == '' && $personDb->pers_cal_date == '' && $personDb->pers_cal_date == '')) {
                    $privacy_person = false;
                }


                // *** Privacy filter exceptions (added a space for single character check) ***
                if (
                    $user["group_filter_pers_show_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_filter_pers_show"]) > 0
                ) {
                    $privacy_person = false;
                }
                if (
                    $user["group_filter_pers_hide_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_filter_pers_hide"]) > 0
                ) {
                    $privacy_person = true;
                }
            }
        }

        // *** Completely filter a person, if option "completely filter a person" is activated ***
        if ($personDb && ($user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0)) {
            $privacy_person = true;
        }

        // *** Privacy filter for whole family tree ***
        if (isset($dataDb->tree_privacy)) {
            if ($dataDb->tree_privacy == 'filter_persons') {
                $privacy_person = true;
            }
            if ($dataDb->tree_privacy == 'show_persons') {
                $privacy_person = false;
            }
        }

        return $privacy_person;
    }

    /*	*** Get person url ***
    *	16-07-2021: Removed variable: pers_indexnr.
    *	29-02-2020: URL construction in person_cls
    *	*** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
    *	$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
    */
    public function person_url2($pers_tree_id, $pers_famc, $pers_fams, $pers_gedcomnumber = '')
    {
        global $humo_option, $uri_path, $link_cls;

        $pers_family = '';
        if ($pers_famc) {
            $pers_family = $pers_famc;
        }
        if ($pers_fams) {
            $pers_fams = explode(';', $pers_fams);
            $pers_family = $pers_fams[0];
        }

        $vars['pers_family'] = $pers_family;
        $url = $link_cls->get_link($uri_path, 'family', $pers_tree_id, true, $vars);
        $url .= "main_person=" . $pers_gedcomnumber;

        return $url;
    }

    // *** Show nicknames (shown as "Nickname") ***
    function get_nickname($db_functions, $pers_gedcomnumber)
    {
        $nickname = '';
        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'name');
        foreach ($name_qry as $nameDb) {
            if ($nameDb->event_gedcom == 'NICK') {
                if ($nickname) $nickname .= ', ';
                $nickname .= $nameDb->event_event;
                // *** Remark: date, place and source are shown in function: person_data ***
            }
        }
        unset($name_qry);
        return $nickname;
    }

    // *** Aldfaer: nobility (predikaat) by name ***
    function get_nobility($db_functions, $pers_gedcomnumber, $show_name_texts)
    {
        $nobility = '';
        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'nobility');
        foreach ($name_qry as $nameDb) {
            if ($nobility) $nobility .= ' ';
            $nobility .= $nameDb->event_event;

            if ($show_name_texts == true and $nameDb->event_text) {
                if ($nobility) $nobility .= ' ';
                $nobility .= process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $nobility;
    }

    // *** Aldfaer: lordship (heerlijkheid) after name ***
    function get_lordship($db_functions, $pers_gedcomnumber, $show_name_texts)
    {
        $lordship = '';
        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'lordship');
        foreach ($name_qry as $nameDb) {
            if ($lordship) $lordship .= ', ';
            $lordship .= $nameDb->event_event;

            if ($show_name_texts == true and $nameDb->event_text) {
                if ($lordship) $lordship .= ' ';
                $lordship .= process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $lordship;
    }

    // *** Gedcom 5.5 title: NPFX ***
    function get_title_before($db_functions, $pers_gedcomnumber, $show_name_texts)
    {
        $title_before = '';
        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'NPFX');
        foreach ($name_qry as $nameDb) {
            if ($title_before) $title_before .= ' ';
            $title_before .= $nameDb->event_event;

            if ($show_name_texts == true and $nameDb->event_text) {
                if ($title_before) $title_before .= ' ';
                $title_before .= process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $title_before;
    }

    // *** Gedcom 5.5 title: NSFX ***
    function get_title_after($db_functions, $pers_gedcomnumber, $show_name_texts)
    {
        $title_after = '';
        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'NSFX');
        foreach ($name_qry as $nameDb) {
            if ($title_after) $title_after .= ' ';
            $title_after .= $nameDb->event_event;

            if ($show_name_texts == true and $nameDb->event_text) {
                if ($title_after) $title_after .= ' ';
                $title_after .= process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $title_after;
    }

    // *** Aldfaer: title by name ***
    /*
    DUTCH Titles FOR DUTCH Genealogical program ALDFAER!
    Title BEFORE name:
        Prof., Dr., Dr.h.c., Dr.h.c.mult., Ir., Mr., Drs., Lic., Kand., Bacc., Ing., Bc., em., Ds.
    Title BETWEEN pers_firstname and pers_lastname:
        prins, prinses, hertog, hertogin, markies, markiezin, markgraaf, markgravin, graaf,
        gravin, burggraaf, burggravin, baron, barones, ridder
    Title AFTER name:
        All other titles.
    */
    function get_title_aldfaer($db_functions, $pers_gedcomnumber, $show_name_texts)
    {
        $title['before'] = '';
        $title['between'] = '';
        $title['after'] = '';

        $name_qry = $db_functions->get_events_connect('person', $pers_gedcomnumber, 'title');
        foreach ($name_qry as $nameDb) {
            $title_position = 'after';
            if ($nameDb->event_event == 'Prof.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Dr.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Dr.h.c.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Dr.h.c.mult.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Ir.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Mr.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Drs.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Lic.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Kand.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Bacc.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Ing.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Bc.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'em.') {
                $title_position = 'before';
            }
            if ($nameDb->event_event == 'Ds.') {
                $title_position = 'before';
            }

            if ($nameDb->event_event == 'prins') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'prinses') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'hertog') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'hertogin') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'markies') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'markiezin') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'markgraaf') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'markgravin') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'graaf') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'gravin') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'burggraaf') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'burggravin') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'baron') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'barones') {
                $title_position = 'between';
            }
            if ($nameDb->event_event == 'ridder') {
                $title_position = 'between';
            }

            if ($title_position == 'before') {
                if ($title['before']) $title['before'] .= ' ';
                $title['before'] .= $nameDb->event_event;

                if ($show_name_texts == true and $nameDb->event_text) {
                    if ($title['before']) $title['before'] .= ' ';
                    $title['before'] .= process_text($nameDb->event_text);
                }
            }
            if ($title_position == 'between') {
                if ($title['between']) $title['between'] .= ' ';
                $title['between'] .= $nameDb->event_event;

                if ($show_name_texts == true and $nameDb->event_text) {
                    if ($title['between']) $title['between'] .= ' ';
                    $title['between'] .= process_text($nameDb->event_text);
                }
            }
            if ($title_position == 'after') {
                if ($title['after']) $title['after'] .= ' ';
                $title['after'] .= $nameDb->event_event;

                if ($show_name_texts == true and $nameDb->event_text) {
                    if ($title['after']) $title['after'] .= ' ';
                    $title['after'] .= process_text($nameDb->event_text);
                }
            }
        }
        unset($name_qry);
        return $title;
    }



    // *************************************************************
    // *** Show person name standard                             ***
    // *************************************************************
    // *** Remark: it's necessary to use $personDb because of witnesses, parents etc. ***
    //public function person_name($personDb){
    public function person_name($personDb, $show_name_texts = false)
    {
        global $dbh, $db_functions, $user, $language, $screen_mode, $selection;
        global $humo_option;

        if (isset($personDb->pers_gedcomnumber) && $personDb->pers_gedcomnumber) {
            $db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Show nicknames (shown as "Nickname") ***
            $nickname = $this->get_nickname($db_functions, $personDb->pers_gedcomnumber);

            // *** Aldfaer: nobility (predikaat) by name ***
            $nobility = $this->get_nobility($db_functions, $personDb->pers_gedcomnumber, $show_name_texts);

            // *** Aldfaer: lordship (heerlijkheid) after name ***
            $lordship = $this->get_lordship($db_functions, $personDb->pers_gedcomnumber, $show_name_texts);

            // *** Gedcom 5.5 title: NPFX ***
            $title_before = $this->get_title_before($db_functions, $personDb->pers_gedcomnumber, $show_name_texts);

            // *** Gedcom 5.5 title: NSFX ***
            $title_after = $this->get_title_after($db_functions, $personDb->pers_gedcomnumber, $show_name_texts);

            // *** Aldfaer: title by name ***
            $title_between = '';
            $title_array = $this->get_title_aldfaer($db_functions, $personDb->pers_gedcomnumber, $show_name_texts);
            if ($title_array['before']) {
                if ($title_before) {
                    $title_before .= ' ';
                }
                $title_before .= $title_array['before'];
            }
            if ($title_between !== '') {
                $title_between .= $title_array['between'];
            }
            if ($title_array['after']) {
                if ($title_after) {
                    $title_after .= ' ';
                }
                $title_after .= $title_array['after'];
            }

            // ***Still born child ***
            $stillborn = '';
            if (isset($personDb->pers_stillborn) && $personDb->pers_stillborn == "y") {
                if ($personDb->pers_sexe == 'M') {
                    $stillborn = __('stillborn boy');
                } elseif ($personDb->pers_sexe == 'F') {
                    $stillborn = __('stillborn girl');
                } else {
                    $stillborn = __('stillborn child');
                }
            }

            // *** Re-calculate privacy filter for witness names and parents ***
            $privacy = $this->set_privacy($personDb);

            // *** Privacy filter: show only first character of firstname. Like: D. Duck ***
            $pers_firstname = $personDb->pers_firstname;

            if ($pers_firstname != 'N.N.' && $privacy && $user['group_filter_name'] == 'i') {
                $names = explode(' ', $personDb->pers_firstname);
                $pers_firstname = '';
                foreach ($names as $character) {
                    if (substr($character, 0, 1) !== '(' && substr($character, 0, 1) !== '[') {
                        $pers_firstname .= ucfirst(substr($character, 0, 1)) . '.';
                    }
                }
            } else {
                $rufname_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'name');
                foreach ($rufname_qry as $rufnameDb) {
                    if ($rufnameDb->event_gedcom == "_RUFN") {
                        //$pers_firstname = str_ireplace($rufnameDb->event_event,'<u>'.$rufnameDb->event_event.'</u>',$pers_firstname);
                        //$pers_firstname .= '&quot;'.$rufnameDb->event_event.'&quot;';

                        if ($pers_firstname) {
                            $pers_firstname .= ' ';
                        }
                        $pers_firstname .= '<u>' . $rufnameDb->event_event . '</u>'; // *** Show Rufname underlined... ***

                        if ($show_name_texts == true && $rufnameDb->event_text) {
                            if ($pers_firstname) {
                                $pers_firstname .= ' ';
                            }
                            $pers_firstname .= process_text($rufnameDb->event_text);
                        }
                    }
                }
            }

            $privacy_name = '';
            if ($privacy && $user['group_filter_name'] == 'n') {
                $privacy_name = __('Name filtered');
            }

            // *** Completely filter person ***
            if (
                $user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0
            ) {
                $privacy_name = __('Name filtered');
            }
            if ($privacy_name) {
                $name_array["show_name"] = false;
                $name_array["firstname"] = $privacy_name;
                $name_array["name"] = $privacy_name;
                $name_array["short_firstname"] = $privacy_name;
                $name_array["standard_name"] = $privacy_name;
                $name_array["index_name"] = $privacy_name;
                $name_array["index_name_extended"] = $privacy_name;
                $name_array["initials"] = "-.-.";
            } else {
                // *** Hide or show name (privacy) ***
                $name_array["show_name"] = true;

                // *** Firstname only ***
                $name_array["firstname"] = $pers_firstname;

                // *** Firstname, patronym, prefix and lastname ***
                if ($humo_option['name_order'] != "chinese") {
                    $name_array["name"] = $pers_firstname;

                    if ($personDb->pers_patronym) {
                        if ($name_array["name"]) {
                            $name_array["name"] .= ' ';
                        }
                        $name_array["name"] .= $personDb->pers_patronym;
                    }

                    if ($personDb->pers_lastname) {
                        if ($name_array["name"]) {
                            $name_array["name"] .= ' ';
                        }
                        $name_array["name"] .= str_replace("_", " ", $personDb->pers_prefix);
                        $name_array["name"] .= $personDb->pers_lastname;
                    }
                } else {
                    // *** For Chinese no commas or spaces, example: Janssen Jan ***
                    $name_array["name"] = str_replace("_", " ", $personDb->pers_prefix);
                    $name_array["name"] .= $personDb->pers_lastname;

                    if ($pers_firstname) {
                        if ($name_array["name"]) {
                            $name_array["name"] .= ' ';
                        }
                        $name_array["name"] .= $pers_firstname;
                    }
                    if ($personDb->pers_patronym) {
                        if ($name_array["name"]) {
                            $name_array["name"] .= ' ';
                        }
                        $name_array["name"] .= $personDb->pers_patronym;
                    }
                }

                // *** Short firstname, prefix and lastname ***
                if ($humo_option['name_order'] != "chinese") {
                    $name_array["short_firstname"] = substr($personDb->pers_firstname, 0, 1);

                    if ($personDb->pers_lastname) {
                        if ($name_array["short_firstname"]) $name_array["short_firstname"] .= ' ';
                        $name_array["short_firstname"] .= str_replace("_", " ", $personDb->pers_prefix);
                        $name_array["short_firstname"] .= $personDb->pers_lastname;
                    }
                } else {
                    $name_array["short_firstname"] = str_replace("_", " ", $personDb->pers_prefix);
                    $name_array["short_firstname"] .= $personDb->pers_lastname;

                    if ($personDb->pers_firstname) {
                        if ($name_array["short_firstname"]) {
                            $name_array["short_firstname"] .= ' ';
                        }
                        $name_array["short_firstname"] .= substr($personDb->pers_firstname, 0, 1);
                    }
                }

                // *** $name_array["standard_name"] ***
                // *** Example: Predikaat Hubertus [Huub] van Mons, Title, 2nd title ***
                if ($humo_option['name_order'] != "chinese") {
                    $name_array["standard_name"] = $nobility;

                    if ($title_before) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $title_before;
                    }

                    if ($pers_firstname) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $pers_firstname;
                    }

                    if ($personDb->pers_patronym) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $personDb->pers_patronym;
                    }

                    if ($title_between !== '') {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $title_between;
                    }

                    // *** Callname shown as "Huub" ***
                    if ($nickname && (!$privacy || $privacy && $user['group_filter_name'] == 'j')) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= '&quot;' . $nickname . '&quot;';
                    }

                    if ($personDb->pers_lastname) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= str_replace("_", " ", $personDb->pers_prefix);
                        $name_array["standard_name"] .= $personDb->pers_lastname;
                    }
                } else {
                    // *** For Chinese no commas or spaces, example: Janssen Jan ***
                    if ($personDb->pers_lastname) {
                        $name_array["standard_name"] = str_replace("_", " ", $personDb->pers_prefix);
                        $name_array["standard_name"] .= $personDb->pers_lastname;
                    }

                    if ($nobility) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $nobility;
                    }

                    if ($title_before) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $title_before;
                    }

                    if ($pers_firstname) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $pers_firstname;
                    }

                    if ($personDb->pers_patronym) {
                        if ($name_array["standard_name"]) {
                            $name_array["standard_name"] .= ' ';
                        }
                        $name_array["standard_name"] .= $personDb->pers_patronym;
                    }

                    $name_array["standard_name"] .= $title_between;

                    // *** Callname shown as "Huub" ***
                    if ($nickname and (!$privacy or ($privacy and $user['group_filter_name'] == 'j'))) {
                        if ($name_array["standard_name"]) $name_array["standard_name"] .= ' ';
                        $name_array["standard_name"] .= '&quot;' . $nickname . '&quot;';
                    }
                }

                if ($title_after) {
                    if ($name_array["standard_name"]) {
                        $name_array["standard_name"] .= ' ';
                    }
                    $name_array["standard_name"] .= $title_after;
                }

                if ($stillborn) {
                    if ($name_array["standard_name"]) {
                        $name_array["standard_name"] .= ' ';
                    }
                    $name_array["standard_name"] .= $stillborn;
                }

                // $lordship starts using a comma ", lordship".
                if ($lordship) {
                    if ($name_array["standard_name"]) {
                        $name_array["standard_name"] .= ', ';
                    }
                    $name_array["standard_name"] .= $lordship;
                }

                $name_array["standard_name"] = trim($name_array["standard_name"]);

                // *** Name for indexes or search results in lastname order ***
                // *** "index_name_extended" includes patronym and stillborn. ***
                $prefix1 = '';
                $prefix2 = '';
                // *** Option to show "van Mons" of "Mons, van" ***
                if ($user['group_kindindex'] == "j") {
                    $prefix1 = str_replace("_", " ", $personDb->pers_prefix);
                } else {
                    $prefix2 = ' ' . str_replace("_", " ", $personDb->pers_prefix);
                }

                // *** Name for indexes. "index_name" is used in relationship calculator ***
                $name_array["index_name"] = $prefix1;
                if ($personDb->pers_lastname) {
                    $name_array["index_name"] .= $personDb->pers_lastname;
                }
                if ($pers_firstname) {
                    if ($name_array["index_name"]) {
                        if ($humo_option['name_order'] != "chinese") {
                            $name_array["index_name"] .= ', ';
                        } else {
                            // *** For Chinese no commas or spaces, example: Janssen Jan ***
                            $name_array["index_name"] .= ' ';
                        }
                    }
                    $name_array["index_name"] .= $pers_firstname;
                }

                // *** Callname shown as "Huub" ***
                //if ($personDb->pers_callname AND (!$privacy OR ($privacy AND $user['group_filter_name']=='j')) ){
                if ($nickname and (!$privacy or ($privacy and $user['group_filter_name'] == 'j'))) {
                    if ($name_array["index_name"]) $name_array["index_name"] .= ' ';
                    //$name_array["index_name"].= '&quot;'.$personDb->pers_callname.'&quot;';
                    $name_array["index_name"] .= '&quot;' . $nickname . '&quot;';
                }
                $name_array["index_name"] .= $prefix2;

                // *** index_name_extended ***
                $name_array["index_name_extended"] = $prefix1;
                if ($personDb->pers_lastname) {
                    $name_array["index_name_extended"] .= $personDb->pers_lastname;
                }
                if ($pers_firstname) {
                    if ($name_array["index_name_extended"]) {
                        if ($humo_option['name_order'] != "chinese") {
                            $name_array["index_name_extended"] .= ', ';
                        } else {
                            // *** For Chinese no commas or spaces, example: Janssen Jan ***
                            $name_array["index_name_extended"] .= ' ';
                        }
                    }
                    $name_array["index_name_extended"] .= $pers_firstname;
                }

                // *** Callname shown as "Huub" ***
                //if ($personDb->pers_callname AND (!$privacy OR ($privacy AND $user['group_filter_name']=='j')) ){
                if ($nickname and (!$privacy or ($privacy and $user['group_filter_name'] == 'j'))) {
                    if ($name_array["index_name_extended"]) $name_array["index_name_extended"] .= ' ';
                    //$name_array["index_name_extended"].= '&quot;'.$personDb->pers_callname.'&quot;';
                    $name_array["index_name_extended"] .= '&quot;' . $nickname . '&quot;';
                }

                if ($title_after) {
                    if ($name_array["index_name_extended"]) {
                        $name_array["index_name_extended"] .= ' ';
                    }
                    $name_array["index_name_extended"] .= $title_after;
                }

                if ($personDb->pers_patronym) {
                    if ($name_array["index_name_extended"]) {
                        $name_array["index_name_extended"] .= ' ';
                    }
                    $name_array["index_name_extended"] .= $personDb->pers_patronym;
                }

                if ($stillborn) {
                    if ($name_array["index_name_extended"]) {
                        $name_array["index_name_extended"] .= ' ';
                    }
                    $name_array["index_name_extended"] .= $stillborn;
                }

                // *** If a special name is found in the results (event table), show it **
                if (isset($personDb->event_event) and $personDb->event_event and $personDb->event_kind == 'name') {
                    // *** Only shown special name if the name isn't shown as nickname: "Huub" ***
                    if ($personDb->event_event != $nickname)
                        $name_array["index_name_extended"] .= ' (' . $personDb->event_event . ')';
                }

                $name_array["index_name_extended"] .= $prefix2;

                // *** If search is done for profession, show profession **
                if (isset($selection['pers_profession']) and $selection['pers_profession']) {
                    if (isset($personDb->event_event) and $personDb->event_event and $personDb->event_kind == 'profession') {
                        $name_array["index_name_extended"] .= ' (' . $personDb->event_event . ')';
                    }
                }

                // *** If search is done for places, show place **
                if (isset($selection['pers_place']) and $selection['pers_place']) {
                    if (isset($personDb->address_place)) {
                        $name_array["index_name_extended"] .= ' (' . $personDb->address_place . ')';
                    }
                }

                // *** If search is done for places, show place **
                if (isset($selection['zip_code']) and $selection['zip_code']) {
                    if (isset($personDb->address_zip)) {
                        $name_array["index_name_extended"] .= ' (' . $personDb->address_zip . ')';
                    }
                }

                // *** If search is done for places, show place **
                if (isset($selection['witness']) and $selection['witness']) {
                    if (isset($personDb->event_event)) {
                        $name_array["index_name_extended"] .= ' (' . $personDb->event_event . ')';
                    }
                }

                // *** $name["initials"] used in report_descendant ***
                // *** Example: H.M. ***
                $name_array["initials"] = substr($personDb->pers_firstname, 0, 1) . '.' . substr($personDb->pers_lastname, 0, 1) . '.';

                if ($humo_option['name_order'] == "chinese") {
                    // for Chinese no commas or spaces, anyway few characters
                    $name_array["initials"] = $personDb->pers_lastname . ' ' . $personDb->pers_firstname;
                }
            }

            // *** Colour mark by person ***
            $name_array["colour_mark"] = '';
            $person_colour_mark = '';
            $colour = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'person_colour_mark');
            foreach ($colour as $colourDb) {
                if ($colourDb && $screen_mode != "PDF" && $screen_mode != "RTF") {
                    $pers_colour = 'style="border-radius: 40px;';
                    $person_colour_mark = $colourDb->event_event;
                    if ($person_colour_mark == '1') {
                        $pers_colour .= ' background-color:#FF0000;';
                    }
                    if ($person_colour_mark == '2') {
                        $pers_colour .= ' background-color:#00FF00;';
                    }
                    if ($person_colour_mark == '3') {
                        $pers_colour .= ' background-color:#0000FF;';
                    }
                    if ($person_colour_mark == '4') {
                        $pers_colour .= ' background-color:#FF00FF;';
                    }
                    if ($person_colour_mark == '5') {
                        $pers_colour .= ' background-color:#FFFF00;';
                    }
                    if ($person_colour_mark == '6') {
                        $pers_colour .= ' background-color:#00FFFF;';
                    }
                    if ($person_colour_mark == '7') {
                        $pers_colour .= ' background-color:#C0C0C0;';
                    }
                    if ($person_colour_mark == '8') {
                        $pers_colour .= ' background-color:#800000;';
                    }
                    if ($person_colour_mark == '9') {
                        $pers_colour .= ' background-color:#008000;';
                    }
                    if ($person_colour_mark == '10') {
                        $pers_colour .= ' background-color:#000080;';
                    }
                    if ($person_colour_mark == '11') {
                        $pers_colour .= ' background-color:#800080;';
                    }
                    if ($person_colour_mark == '12') {
                        $pers_colour .= ' background-color:#A52A2A;';
                    }
                    if ($person_colour_mark == '13') {
                        $pers_colour .= ' background-color:#008080;';
                    }
                    if ($person_colour_mark == '14') {
                        $pers_colour .= ' background-color:#808080;';
                    }
                    $pers_colour .= '"';
                    $name_array["colour_mark"] .= ' <span ' . $pers_colour . '>&nbsp;&nbsp;&nbsp;</span>';
                }
            }
            unset($colour);
        } else {
            $name_array["show_name"] = true;
            $name_array["firstname"] = __('N.N.');
            $name_array["name"] = '';
            $name_array["short_firstname"] = __('N.N.');
            $name_array["standard_name"] = __('N.N.');
            $name_array["index_name"] = __('N.N.');
            $name_array["index_name_extended"] = __('N.N.');
            $name_array["initials"] = "-.-.";
        }

        return $name_array;
    }


    // *************************************************************
    // *** Show person pop-up menu                               ***
    // *************************************************************
    // *** $extended=true; Show a full persons pop-up including picture and person data ***
    // *** $replacement_text='text'; Replace the pop-up icon by the replacement_text ***
    // *** $extra_pop-up_text=''; To add extra text in the pop-up screen ***
    public function person_popup_menu($personDb, $extended = false, $replacement_text = '', $extra_popup_text = '')
    {
        global $dbh, $db_functions, $bot_visit, $humo_option, $uri_path, $user, $language;
        global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
        global $selected_language, $hourglass, $link_cls, $page;
        $text_start = '';
        $text = '';
        $popover_content = '';
        $privacy = $this->privacy;

        // *** Show pop-up menu ***
        if (!$bot_visit && $screen_mode != "PDF" && $screen_mode != "RTF") {

            // *** Family tree for search in multiple family trees ***
            $db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $start_url = $this->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
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
                $text_start .= ' class="nam" style="z-index:100;font-size:10px; display:block; width:100%; height:100%" ';
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

            $name = $this->person_name($personDb);
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
                    @$check_childrenDb = $db_functions->get_family($check_family[$i]);
                    if ($check_childrenDb->fam_children) {
                        $check_children = true;
                    }
                }
                if ($check_children) {
                    $vars['pers_family'] = $pers_family;
                    $path_tmp = $link_cls->get_link($uri_path, 'family', $personDb->pers_tree_id, true, $vars);
                    $path_tmp .= "main_person=" . $personDb->pers_gedcomnumber . '&amp;descendant_report=1';
                    $text .= '<a href="' . $path_tmp . '"><img src="images/descendant.gif" border="0" alt="' . __('Descendants') . '"> ' . __('Descendants') . '</a>';
                    $popover_content .= '<li><a href="' . $path_tmp . '"><img src="images/descendant.gif" border="0" alt="' . __('Descendants') . '"> ' . __('Descendants') . '</a></li>';
                }
            }

            if ($user['group_gen_protection'] == 'n' && $personDb->pers_famc != '') {
                // == Ancestor report: link & icons by Klaas de Winkel ==
                $vars['id'] = $personDb->pers_gedcomnumber;
                $path_tmp = $link_cls->get_link($uri_path, 'ancestor_report', $personDb->pers_tree_id, false, $vars);
                $text .= '<a href="' . $path_tmp . '"><img src="images/ancestor_report.gif" border="0" alt="' . __('Ancestor report') . '"> ' . __('Ancestors') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '"><img src="images/ancestor_report.gif" border="0" alt="' . __('Ancestor report') . '"> ' . __('Ancestors') . '</a></li>';
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
                    $text .= '<a href="' . $path_tmp . '"><img src="images/timeline.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Timeline') . '</a>';
                    $popover_content .= '<li><a href="' . $path_tmp . '"><img src="images/timeline.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Timeline') . '</a></li>';
                }
            }

            if ($user["group_relcalc"] == 'j') {
                $relpath = $link_cls->get_link($uri_path, 'relations', $personDb->pers_tree_id, true);
                $text .= '<a href="' . $relpath . 'pers_id=' . $personDb->pers_id . '"><img src="images/relcalc.gif" border="0" alt="' . __('Relationship calculator') . '"> ' . __('Relationship calculator') . '</a>';
                $popover_content .= '<li><a href="' . $relpath . 'pers_id=' . $personDb->pers_id . '"><img src="images/relcalc.gif" border="0" alt="' . __('Relationship calculator') . '"> ' . __('Relationship calculator') . '</a></li>';
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
                $text .= '<a href="' . $path_tmp . '"><img src="images/dna.png" border="0" alt="' . __('DNA Charts') . '"> ' . __('DNA Charts') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '"><img src="images/dna.png" border="0" alt="' . __('DNA Charts') . '"> ' . __('DNA Charts') . '</a></li>';
            }

            if ($user['group_gen_protection'] == 'n' && $personDb->pers_famc != '' && $personDb->pers_fams != '' && $check_children) {
                // hourglass only if there is at least one generation of ancestors and of children.
                $vars['pers_family'] = $pers_family;
                $path_tmp = $link_cls->get_link($uri_path, 'hourglass', $personDb->pers_tree_id, true, $vars);
                $path_tmp .= "main_person=" . $personDb->pers_gedcomnumber . '&amp;screen_mode=HOUR';
                $text .= '<a href="' . $path_tmp . '"><img src="images/hourglass.gif" border="0" alt="' . __('Hourglass chart') . '"> ' . __('Hourglass chart') . '</a>';
                $popover_content .= '<li><a href="' . $path_tmp . '"><img src="images/hourglass.gif" border="0" alt="' . __('Hourglass chart') . '"> ' . __('Hourglass chart') . '</a></li>';
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
                    $popover_content .= '<li><a href="' . $path_tmp . '" target="_blank"><img src="images/person_edit.gif" border="0" alt="' . __('Timeline') . '"> ' . __('Editor') . '</a></li>';
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
                        $picture = show_picture($tree_pict_path, $pictureDb->event_event, '', 120);
                        $text .= '<img src="' . $picture['path'] . $picture['thumb'] . $picture['picture'] . '" style="margin-left:10px; margin-top:5px;" alt="' . $pictureDb->event_text . '" height="' . $picture['height'] . '"><br>';
                        //$popover_content .= '<img src="' . $picture['path'] . $picture['thumb'] . $picture['picture'] . '" style="margin-left:10px; margin-top:5px;" alt="' . $pictureDb->event_text . '" height="' . $picture['height'] . '"><br>';
                    }
                }

                // *** Pop-up tekst ***
                if (!$privacy) {
                    if ($personDb->pers_birth_date || $personDb->pers_birth_place) {
                        $text .= __('*') . $dirmark1 . ' ' .
                            date_place($personDb->pers_birth_date, $personDb->pers_birth_place);
                    } elseif ($personDb->pers_bapt_date || $personDb->pers_bapt_place) {
                        $text .= __('~') . $dirmark1 . ' ' .
                            date_place($personDb->pers_bapt_date, $personDb->pers_bapt_place);
                    }

                    if ($personDb->pers_death_date || $personDb->pers_death_place) {
                        $text .= '<br>' . __('&#134;') . $dirmark1 . ' ' .
                            date_place($personDb->pers_death_date, $personDb->pers_death_place);
                    } elseif ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                        $text .= '<br>' . __('[]') . $dirmark1 . ' ' .
                            date_place($personDb->pers_buried_date, $personDb->pers_buried_place);
                    }

                    // *** If needed add extra text in the pop-up box ***
                    if ($extra_popup_text) {
                        $text .= '<br><br>' . $extra_popup_text;
                    }
                } else {
                    $text .= ' ' . __('PRIVACY FILTER');
                }

                $text .= '</td></tr></table>';
            } // *** End of extended pop-up ***

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
        }  // end "if not pdf"

        return $text;
    }



    //*** Show spouse/ partner by child ***
    function get_child_partner($db_functions, $personDb, $person_kind)
    {
        global $bot_visit, $dirmark1;
        $child_marriage = '';
        if (!$bot_visit && $person_kind == 'child' && $personDb->pers_fams) {
            $marriage_array = explode(";", $personDb->pers_fams);
            $nr_marriages = count($marriage_array);
            for ($x = 0; $x <= $nr_marriages - 1; $x++) {
                $fam_partnerDb = $db_functions->get_family($marriage_array[$x]);

                // *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
                if ($personDb->pers_gedcomnumber == $fam_partnerDb->fam_man) {
                    $partner_id = $fam_partnerDb->fam_woman;
                } else {
                    $partner_id = $fam_partnerDb->fam_man;
                }

                //$relation_short=__('&');
                $relation_short = __('relationship with');
                if ($fam_partnerDb->fam_marr_date || $fam_partnerDb->fam_marr_place || $fam_partnerDb->fam_marr_church_date || $fam_partnerDb->fam_marr_church_place || $fam_partnerDb->fam_kind == 'civil') {
                    //$relation_short=__('X');
                    $relation_short = __('married to');
                    if ($nr_marriages > 1) {
                        $relation_short = __('marriage with');
                    }
                }

                // *** Added in jan. 2021 (also see: marriage_cls.php) ***
                if ($fam_partnerDb->fam_kind == 'living together') {
                    $relation_short = __('Living together');
                }
                if ($fam_partnerDb->fam_kind == 'living apart together') {
                    $relation_short = __('Living apart together');
                }
                if ($fam_partnerDb->fam_kind == 'intentionally unmarried mother') {
                    $relation_short = __('Intentionally unmarried mother');
                }
                if ($fam_partnerDb->fam_kind == 'homosexual') {
                    $relation_short = __('Homosexual');
                }
                if ($fam_partnerDb->fam_kind == 'non-marital') {
                    $relation_short = __('Non marital');
                }
                if ($fam_partnerDb->fam_kind == 'extramarital') {
                    $relation_short = __('Extramarital');
                }
                if ($fam_partnerDb->fam_kind == "PRO-GEN") {
                    $relation_short = __('Extramarital');
                }
                if ($fam_partnerDb->fam_kind == 'partners') {
                    $relation_short = __('Partner');
                }
                if ($fam_partnerDb->fam_kind == 'registered') {
                    $relation_short = __('Registered');
                }
                if ($fam_partnerDb->fam_kind == 'unknown') {
                    $relation_short = __('Unknown relation');
                }

                if ($fam_partnerDb->fam_div_date || $fam_partnerDb->fam_div_place) {
                    //$relation_short=__(') (');
                    $relation_short = __('divorced from');
                    if ($nr_marriages > 1) {
                        $relation_short = __('marriage (divorced) with');
                    }
                }

                if ($partner_id != '0' && $partner_id != '') {
                    $partnerDb = $db_functions->get_person($partner_id);
                    $partner_cls = new person_cls;
                    $name = $partner_cls->person_name($partnerDb);

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $this->person_url2($partnerDb->pers_tree_id, $partnerDb->pers_famc, $partnerDb->pers_fams, $partnerDb->pers_gedcomnumber);
                } else {
                    $name["standard_name"] = __('N.N.');

                    // *** Link for N.N. partner, not in database ***
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $this->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                }
                $name["standard_name"] = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';

                $child_marriage .= '<b>';
                if ($nr_marriages > 1) {
                    if ($x == 0) {
                        $child_marriage .= ' ' . __('1st');
                    } elseif ($x == 1) {
                        $child_marriage .= ', ' . __('2nd');
                    } elseif ($x == 2) {
                        $child_marriage .= ', ' . __('3rd');
                    } elseif ($x > 2) {
                        $child_marriage .= ', ' . ($x + 1) . __('th');
                    }
                } else {
                    $child_marriage .= ' ';
                }

                $child_marriage .= ' ' . ucfirst($relation_short) . '</b> ' . $dirmark1 . $name["standard_name"] . $dirmark1;
            }
        }
        return $child_marriage;
    }


    // ************************************************************************
    // *** Show person name and name of parents                             ***
    // *** $person_kind = 'child' generates a link by a child to his family ***
    // ***                                                                  ***
    // *** Oct. 2013: added name of parents after the name of a person.     ***
    // ************************************************************************
    public function name_extended($person_kind, $show_name_texts = false)
    {
        global $dbh, $db_functions, $humo_option, $uri_path, $user, $language;
        global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
        global $selected_language, $bot_visit;
        global $sect; // *** RTF Export ***
        global $templ_name, $familyDb;
        global $pdf;
        global $data;

        $start_name = '';
        $text_name = '';
        $text_name2 = '';
        $text_colour = '';
        $text_parents = '';
        $child_marriage = '';

        $personDb = $this->personDb;
        $privacy = $this->privacy;
        if (!$personDb) {
            // *** Show unknown person N.N. ***
            $templ_name["name_name"] = __('N.N.');
            $text_name = __('N.N.');
        } else {
            $db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Show pop-up menu ***
            $start_name .= $this->person_popup_menu($personDb);

            // *** Check privacy filter ***
            if ($privacy && $user['group_filter_name'] == 'n') {
                //dummy
            } else {
                // *** Show man or woman picture ***
                // *** Ancestor reports uses cells, not sections. Ancestor M/F/? icons are generated in ancestor script ***
                if ($screen_mode == "RTF" and !isset($_POST['ancestor_report'])) {
                    // *** RTF person pictures in JPG, because Word doesn't support GIF pictures... ***
                    if ($personDb->pers_sexe == "M")
                        $sect->addImage('images/man.jpg', null);
                    elseif ($personDb->pers_sexe == "F")
                        $sect->addImage('images/woman.jpg', null);
                    else
                        $sect->addImage('images/unknown.jpg', null);
                    // SOURCE IS MISSING
                } else {
                    $text_name .= $dirmark1;
                    if ($personDb->pers_sexe == "M") {
                        $templ_name["name_sexe"] = $personDb->pers_sexe;
                        $start_name .= '<img src="images/man.gif" alt="man">';
                    } elseif ($personDb->pers_sexe == "F") {
                        $templ_name["name_sexe"] = $personDb->pers_sexe;
                        $start_name .= '<img src="images/woman.gif" alt="woman">';
                    } else {
                        $templ_name["name_sexe"] = '?';
                        $start_name .= '<img src="images/unknown.gif" alt="unknown">';
                    }

                    // *** Source by sexe ***
                    $source_array = '';
                    if ($person_kind != 'outline' and $person_kind != 'outline_pdf')
                        $source_array = show_sources2("person", "pers_sexe_source", $personDb->pers_gedcomnumber);
                    if ($source_array) {
                        $start_name .= $source_array['text'] . ' ';
                        $templ_name["name_sexe_source"] = $source_array['text'];
                    }

                    // *** PDF does this elsewhere ***
                    if ($screen_mode != "PDF") {  //  pdf does this elsewhere
                        if ($humo_option['david_stars'] == "y") {
                            $camps = "Auschwitz|Oświęcim|Sobibor|Bergen-Belsen|Bergen Belsen|Treblinka|Holocaust|Shoah|Midden-Europa|Majdanek|Belzec|Chelmno|Dachau|Buchenwald|Sachsenhausen|Mauthausen|Theresienstadt|Birkenau|Kdo |Kamp Amersfoort|Gross-Rosen|Gross Rosen|Neuengamme|Ravensbrück|Kamp Westerbork|Kamp Vught|Kommando Sosnowice|Ellrich|Schöppenitz|Midden Europa|Lublin|Tröbitz|Kdo Bobrek|Golleschau|Blechhammer|Kdo Gleiwitz|Warschau|Szezdrzyk|Polen|Kamp Bobrek|Monowitz|Dorohucza|Seibersdorf|Babice|Fürstengrube|Janina|Jawischowitz|Katowice|Kaufering|Krenau|Langenstein|Lodz|Ludwigsdorf|Melk|Mühlenberg|Oranienburg|Sakrau|Schwarzheide|Spytkowice|Stutthof|Tschechowitz|Weimar|Wüstegiersdorf|Oberhausen|Minsk|Ghetto Riga|Ghetto Lodz|Flossenbürg|Malapane";
                            if (
                                preg_match("/($camps)/i", $personDb->pers_death_place) !== 0 or
                                preg_match("/($camps)/i", $personDb->pers_buried_place) !== 0 or strpos(strtolower($personDb->pers_death_place), "oorlogsslachtoffer") !== FALSE
                            ) {
                                $start_name .= '<img src="images/star.gif" alt="star">&nbsp;';
                            }
                        }
                        // *** Add own icon by person, using a file name in own code ***
                        if ($personDb->pers_own_code != '' and is_file("images/" . $personDb->pers_own_code . ".gif")) {
                            $start_name .= '<img src="images/' . $personDb->pers_own_code . '.gif" alt="' . $personDb->pers_own_code . '">&nbsp;';
                        }
                    }
                }
            }

            $name = $this->person_name($personDb, $show_name_texts);
            $standard_name = $name["standard_name"] . $dirmark2;

            // *** Show full gedcomnummer as [I5] (because of Heredis GEDCOM file, that shows: 5I) ***
            if ($user['group_gedcomnr'] == 'j') {
                $standard_name .= $dirmark1 . ' [' . $personDb->pers_gedcomnumber . ']';
            }

            // *** No links if gen_protection is enabled ***
            if ($user["group_gen_protection"] == 'j') {
                $person_kind = '';
            }

            //if (($person_kind=='child' OR $person_kind=='outline') AND $personDb->pers_fams){
            // *** 02-08-2021: also add link to partner in family screen ***
            if (($person_kind == 'child' || $person_kind == 'outline' || $person_kind == 'parent2') && $personDb->pers_fams && $screen_mode != "PDF") {
                $templ_name["name_name"] = $standard_name;

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $this->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);

                $standard_name = '<a href="' . $url . '">' . $standard_name;
                // *** Show name with link ***
                $text_name = $text_name . $standard_name . '</a>';
            } else {
                $templ_name["name_name"] = $standard_name;

                $text_name .= $standard_name; // *** Show name without link **
            }


            // *** Check privacy filter ***
            $text_name2 = '';
            if (!$privacy) {
                // *** Text by name ***
                if ($user["group_texts_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_name_text);
                    if ($work_text) {
                        $templ_name["name_text"] = " " . $work_text;
                        $text_name2 .= " " . $work_text;
                    }
                }

                // *** Source by name ***
                $source_array = '';
                if ($person_kind != 'outline' && $person_kind != 'outline_pdf') {
                    $source_array = show_sources2("person", "pers_name_source", $personDb->pers_gedcomnumber);
                }
                if ($source_array) {
                    if ($screen_mode == 'PDF') {
                        $templ_name["name_name_source"] = $source_array['text'];
                    }
                    $text_name2 .= $source_array['text'];
                }
            }

            // *** Add colour marks to person ***
            $text_colour = $name["colour_mark"];

            // *** Show age of parent2 when married (don't show age if it's an relation) ***
            global $relation_check;
            if (!$privacy && $person_kind == 'parent2' && $familyDb->fam_marr_date != '') {
                $process_age = new calculate_year_cls;
                if ($relation_check == true) {
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, $familyDb->fam_marr_date, false, 'relation');
                } else {
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, $familyDb->fam_marr_date, false, 'marriage');
                }
                $templ_name["name_wedd_age"] = $age;
                $text_name2 .= $age;
            }

            // *********************************************************************
            // *** Show: son of/ daughter of/ child of name-father & name-mother ***
            // *********************************************************************
            if (($person_kind == 'parent1' || $person_kind == 'parent2') && $personDb->pers_famc) {
                if ($personDb->pers_sexe == 'M') {
                    $text_parents .= __('son of') . ' ';
                }
                if ($personDb->pers_sexe == 'F') {
                    $text_parents .= __('daughter of') . ' ';
                }
                if ($personDb->pers_sexe == '') {
                    $text_parents .= __('child of') . ' ';
                }
                if ($data["family_expanded"] != 'compact') {
                    $templ_name["name_parents"] = ucfirst($text_parents);
                    $text_parents = ucfirst($text_parents);
                } else {
                    //$templ_name["parent_childof"]=', '.$text_parents;
                    //$temp="parent_childof";
                    $templ_name["name_parents"] = ', ' . $text_parents;
                    $text_parents = ', ' . $text_parents;
                }

                // *** Find parents ID ***
                $parents_familyDb = $db_functions->get_family($personDb->pers_famc);

                // *** Father ***
                if ($parents_familyDb->fam_man) {
                    $fatherDb = $db_functions->get_person($parents_familyDb->fam_man);
                    $name = $this->person_name($fatherDb);
                    //$templ_name["parents"]=$name["standard_name"];
                    $templ_name["name_parents"] .= $name["standard_name"];

                    // *** Seperate father/mother links ***
                    $gedcomnumber = '';
                    if (isset($fatherDb->pers_gedcomnumber)) {
                        $gedcomnumber = $fatherDb->pers_gedcomnumber;
                    }

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $this->person_url2($fatherDb->pers_tree_id, $fatherDb->pers_famc, $fatherDb->pers_fams, $fatherDb->pers_gedcomnumber);

                    // *** Add link ***
                    if ($user['group_gen_protection'] == 'n') {
                        $text = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }
                } else {
                    //$templ_name["parents"]=__('N.N.');
                    // *** Seperate father/mother links ***
                    $templ_name["name_parents"] .= __('N.N.');
                    $text = __('N.N.');
                }

                //$templ_name["parents"].=' '.__('and').' ';
                //$temp="parents";
                //$text=$templ_name["parents"];
                // *** Seperate father/mother links ***
                $templ_name["name_parents"] .= ' ' . __('and') . ' ';
                $text .= ' ' . __('and') . ' ';

                // *** Mother ***
                if ($parents_familyDb->fam_woman) {
                    $motherDb = $db_functions->get_person($parents_familyDb->fam_woman);
                    $name = $this->person_name($motherDb);
                    $templ_name["name_parents"] .= $name["standard_name"];

                    // *** Seperate father/mother links ***
                    $gedcomnumber = '';
                    if (isset($motherDb->pers_gedcomnumber)) {
                        $gedcomnumber = $motherDb->pers_gedcomnumber;
                    }

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $this->person_url2($motherDb->pers_tree_id, $motherDb->pers_famc, $motherDb->pers_fams, $motherDb->pers_gedcomnumber);

                    // *** Add link ***
                    if ($user['group_gen_protection'] == 'n') {
                        $text .= '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }
                } else {
                    $templ_name["name_parents"] .= __('N.N.');
                    $text .= __('N.N.');
                }

                // *** Add link ***
                //if ($user['group_gen_protection']=='n'){ $text='<a href="'.$url.'">'.$text.'</a>'; }

                $templ_name["name_parents"] .= '.';
                $text_parents .= '<span class="parents">' . $text . $dirmark2 . '.</span>';
            }


            // ********************************************************************************************
            // *** Check for adoptive parents (just for sure: made it for multiple adoptive parents...) ***
            // ********************************************************************************************
            if ($person_kind == 'parent1' || $person_kind == 'parent2') {
                $famc_adoptive_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'adoption');
                foreach ($famc_adoptive_qry as $famc_adoptiveDb) {
                    if (!isset($templ_name["name_parents"])) {
                        $templ_name["name_parents"] = '';
                    }
                    $templ_name["name_parents"] .= ' ' . ucfirst(__('adoption parents')) . ': ';
                    $text_parents .= ' ' . ucfirst(__('adoption parents')) . ': ';

                    // *** Just in case: empty $text ***
                    $text = '';
                    // *** Find parents ID ***
                    $parents_familyDb = $db_functions->get_family($famc_adoptiveDb->event_event);

                    //*** Father ***
                    if ($parents_familyDb->fam_man) {
                        $fatherDb = $db_functions->get_person($parents_familyDb->fam_man);
                        $name = $this->person_name($fatherDb);

                        $templ_name["name_parents"] .= $name["standard_name"];
                        $text = $name["standard_name"];

                        $temp = "parents";
                    } else {
                        $templ_name["name_parents"] .= __('N.N.');

                        $text = __('N.N.');

                        $temp = "parents";
                    }

                    $templ_name["name_parents"] .= ' ' . __('and') . ' ';
                    $text .= ' ' . __('and') . ' ';
                    //$templ_name["parents"].=' '.__('and').' ';
                    //$temp="parents";

                    //*** Mother ***
                    if ($parents_familyDb->fam_woman) {
                        $motherDb = $db_functions->get_person($parents_familyDb->fam_woman);
                        $name = $this->person_name($motherDb);
                        $templ_name["name_parents"] .= $name["standard_name"];
                        $text .= $name["standard_name"];
                        //$templ_name["parents"].=$name["standard_name"];
                        //$temp="parents";
                    } else {
                        $templ_name["name_parents"] .= __('N.N.');
                        $text .= __('N.N.');
                    }

                    $url = '';
                    if ($parents_familyDb->fam_man) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $this->person_url2($fatherDb->pers_tree_id, $fatherDb->pers_famc, $fatherDb->pers_fams, $fatherDb->pers_gedcomnumber);
                    } elseif ($parents_familyDb->fam_woman) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $this->person_url2($motherDb->pers_tree_id, $motherDb->pers_famc, $motherDb->pers_fams, $motherDb->pers_gedcomnumber);
                    }

                    // *** Add link ***
                    if ($user['group_gen_protection'] == 'n') {
                        $text = '<a href="' . $url . '">' . $text . '</a>';
                    }

                    //$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
                    $text_parents .= '<span class="parents">' . $text . ' </span>';
                }
            }

            // ***********************************************************************
            // *** Check for adoptive parent ESPECIALLY FOR ALDFAER and MyHeritage ***
            // ***********************************************************************
            if ($person_kind == 'parent1' || $person_kind == 'parent2') {
                $famc_adoptive_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'adoption_by_person');
                foreach ($famc_adoptive_qry as $famc_adoptiveDb) {
                    if (!isset($templ_name["name_parents"])) {
                        $templ_name["name_parents"] = '';
                    }

                    if ($famc_adoptiveDb->event_gedcom == 'steph') {
                        $templ_name["name_parents"] .= ' ' . ucfirst(__('stepparent')) . ': ';
                        $text_parents .= ' ' . ucfirst(__('stepparent')) . ': ';
                    } elseif ($famc_adoptiveDb->event_gedcom == 'legal') {
                        $templ_name["name_parents"] .= ' ' . ucfirst(__('legal parent')) . ': ';
                        $text_parents .= ' ' . ucfirst(__('legal parent')) . ': ';
                    } elseif ($famc_adoptiveDb->event_gedcom == 'foster') {
                        $templ_name["name_parents"] .= ' ' . ucfirst(__('foster parent')) . ': ';
                        $text_parents .= ' ' . ucfirst(__('foster parent')) . ': ';
                    } elseif (substr($famc_adoptiveDb->event_event, 0, 1) === 'F') {
                        // *** MyHeritage ***
                        $templ_name["name_parents"] .= ' ' . ucfirst(__('adoptive parents')) . ': ';
                        $text_parents .= ' ' . ucfirst(__('adoptive parents')) . ': ';
                    } else {
                        $templ_name["name_parents"] .= ' ' . ucfirst(__('adoptive parent')) . ': ';
                        $text_parents .= ' ' . ucfirst(__('adoptive parent')) . ': ';
                    }

                    if (substr($famc_adoptiveDb->event_event, 0, 1) === 'F') {
                        // *** GEDCOM: MyHeritage ***
                        // *** Search for parents ***
                        $family_parents2Db = $db_functions->get_family($famc_adoptiveDb->event_event, 'man-woman');

                        //*** Father ***
                        if ($family_parents2Db->fam_man) {
                            $fatherDb = $db_functions->get_person($family_parents2Db->fam_man);
                            $name = $this->person_name($fatherDb);
                            $templ_name["name_parents"] .= $name["standard_name"];
                            $text = $name["standard_name"];

                            $url = $this->person_url2($fatherDb->pers_tree_id, $fatherDb->pers_famc, $fatherDb->pers_fams, $fatherDb->pers_gedcomnumber);
                            // *** Add link ***
                            if (isset($url) && $user['group_gen_protection'] == 'n')
                                $text = '<a href="' . $url . '">' . $text . '</a>';
                        } else {
                            $text .= __('N.N.');
                        }

                        $templ_name["name_parents"] .= ' ' . __('and');
                        $text .= ' ' . __('and');

                        //*** Mother ***
                        if ($family_parents2Db->fam_woman) {
                            $motherDb = $db_functions->get_person($family_parents2Db->fam_woman);
                            $name = $this->person_name($motherDb);
                            $templ_name["name_parents"] .= ' ' . $name["standard_name"];

                            $url = $this->person_url2($motherDb->pers_tree_id, $motherDb->pers_famc, $motherDb->pers_fams, $motherDb->pers_gedcomnumber);
                            // *** Add link ***
                            if (isset($url) && $user['group_gen_protection'] == 'n')
                                $name["standard_name"] = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';

                            $text .= ' ' . $name["standard_name"];
                        } else {
                            $text .= __('N.N.');
                        }
                    } else {
                        // *** Aldfaer ***
                        $fatherDb = $db_functions->get_person($famc_adoptiveDb->event_event);
                        $name = $this->person_name($fatherDb);
                        $templ_name["name_parents"] .= $name["standard_name"];
                        $text = $name["standard_name"];
                        //$templ_name["parents"]=$name["standard_name"];
                        //$temp="parents";

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        if (isset($fatherDb->pers_tree_id)) {
                            $url = $this->person_url2($fatherDb->pers_tree_id, $fatherDb->pers_famc, $fatherDb->pers_fams, $fatherDb->pers_gedcomnumber);
                        }
                        // *** Add link ***
                        if (isset($url) && $user['group_gen_protection'] == 'n')
                            $text = '<a href="' . $url . '">' . $text . '</a>';
                    }

                    //$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
                    $text_parents .= '<span class="parents">' . $text . ' </span>';
                }
            }

            //*** Show spouse/ partner by child ***
            // Apr. 2024 moved to person_data function.
            // TODO remove $child_marriage variable from this function.
            //$child_marriage = $this->get_child_partner($db_functions, $personDb, $person_kind);
            //if ($child_marriage) $templ_name["name_partner"] = $child_marriage;

        }

        if ($data["family_expanded"] != 'compact') {
            $text_parents = '<div class="margin_person">' . $text_parents . '</div>';
            $child_marriage = '<div class="margin_child">' . $child_marriage . '</div>';
        }


        if ($screen_mode == 'RTF') {
            return '<b>' . $text_name . $dirmark1 . '</b>' . $text_name2 . $text_colour . $text_parents . $child_marriage;
        } elseif ($screen_mode == 'PDF') {
            return $text_name;
        } else {
            return $start_name . '<span class="pers_name">' . $text_name . $dirmark1 . '</span>' . $text_name2 . $text_colour . $text_parents . $child_marriage;
        }

        /*
        if ($screen_mode=='RTF')
            return '<b>'.$text_name.$dirmark1.'</b>'.$text_name2.$text_colour.$text_parents.$child_marriage;
        elseif($screen_mode!="PDF") {
            return '<span class="pers_name">'.$text_name.$dirmark1.'</span>'.$text_name2.$text_colour.$text_parents.$child_marriage;
        }
        // RETURN OF ARRAY GENERATES FAULT MESSAGES.
        else {   // return array with pdf values
            if(isset($templ_name)) { return $templ_name; }
        }
        */
    }



    /*
//TEST
//$process_text.=$this->html_display($templ_person);


//TEST to show HTML:
public function html_display($templ_person){
    global $pdf;
    $text='';
    if (isset($templ_person)){
$own_code=0;
        foreach ($templ_person as $key => $value) {
//			$own_code=0;

            if(strpos($key,"own_code_start")!==false) continue;
            if(!$own_code AND strpos($key,"own_code")!==false) {
                $text='<b>'.$templ_person["own_code_start"].'</b>';
                $own_code=1;
            }

            // for now, only process own_code
            if(strpos($key,"own_code")!==false) {
                if(strpos($key,"text")!==false) {  $text.='<b>'; }
                $text.=$key;
                if(strpos($key,"text")!==false) {  $text.='</b>'; }
            }

        }
    }
    return $text;
}
*/



    // ***************************************************************************************
    // *** Show person                                                                     ***
    // *** $personDb = data person                                                         ***
    // *** $person_kind = parent2 (normally the woman, generates links to all marriages)   ***
    // *** $id = family id for link by woman for multiple marrriages                       ***
    // *** $privacy = privacyfilter                                                        ***
    // ***************************************************************************************
    public function person_data($person_kind, $id)
    {
        global $dbh, $db_functions, $tree_id, $dataDb, $user, $language, $humo_option, $family_id, $uri_path;
        global $swap_parent1_parent2;
        global $childnr, $screen_mode, $dirmark1, $dirmark2;
        global $temp, $templ_person;
        global $sect, $arial12; // *** RTF export ***
        global $pdf;
        global $data;

        $personDb = $this->personDb;
        $privacy = $this->privacy;

        // *** Settings for mobile version, show details in multiple lines ***
        if ($person_kind == "mobile") {
            $data["family_expanded"] = 'expanded1';
        }

        // *** $personDb is empty by N.N. person ***
        if ($personDb) {
            $db_functions->set_tree_id($personDb->pers_tree_id);

            $process_text = '';
            $temp = '';

            //*** PRIVACY PART ***
            $privacy_filter = '';
            if ($privacy) {
                if ($screen_mode == "mobile") {
                    return __('PRIVACY FILTER');
                } elseif ($screen_mode != "PDF") {
                    $privacy_filter = ' ' . __('PRIVACY FILTER');
                } else {
                    return null;
                }  // makes no sense to ask for login in a pdf report.....
            } else {
                // *** Quality (function show_quality can be found in family script) ***
                // Disabled because normally quality belongs to a source.
                //if ($personDb->pers_quality=='0' or $personDb->pers_quality){
                //	$quality_text=show_quality($personDb->pers_quality);
                //	$process_text.= ' <i>'.ucfirst($quality_text).'</i>';
                //}

                // *** Show extra names of BK ***
                if ($personDb->pers_gedcomnumber) {
                    $eventnr = 0;
                    $name_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'name');
                    // *** Can be used to hide $event_gedcom text ***
                    $previous_event_gedcom = '';
                    foreach ($name_qry as $nameDb) {
                        $eventnr++;
                        $text = '';
                        if ($nameDb->event_gedcom == '_AKAN') {
                            $text .= __('Also known as') . ': ';
                        }

                        // *** MyHeritage Family Tree Builder. Only show first "Also known as" text ***
                        if ($nameDb->event_gedcom == '_AKA' && $previous_event_gedcom != '_AKA') {
                            $text .= __('Also known as') . ': ';
                        }

                        // *** December 2021: Nickname is allready shown as "Nickname".
                        //		Nickname is allready shown in function person_name, extra items like date, place, text and source will be shown here ***
                        if ($nameDb->event_gedcom == 'NICK') {
                            // *** To check if there is a source ***
                            $source_array = show_sources2("person", "pers_event_source", $nameDb->event_id);
                            if ($nameDb->event_date || $nameDb->event_place || $nameDb->event_text || $source_array) {
                                $text .= __('Nickname') . ': ';
                            } else {
                                // *** There is no date or text, skip NICK ***
                                $eventnr--;
                                continue;
                            }
                        }
                        // *** Translate names ***
                        $text .= language_name($nameDb->event_gedcom);
                        // *** _RUFN is shown by name ***
                        if ($nameDb->event_gedcom == '_RUFN') {
                            $eventnr--;
                            continue; // *** Skip __RUFN in events ***
                        }

                        $previous_event_gedcom = $nameDb->event_gedcom;
                        if ($eventnr > 1) {
                            $templ_person["bknames" . $eventnr] = ', ' . lcfirst($text);
                            $text = ', ' . lcfirst($text);
                        } else {
                            $templ_person["bknames" . $eventnr] = ucfirst($text);
                            $text = ucfirst($text);
                        }
                        if ($templ_person["bknames" . $eventnr] != '') {
                            $temp = "bk_names" . $eventnr;
                        }
                        $process_text .= $text;

                        $templ_person["bk_event" . $eventnr] = $nameDb->event_event;
                        if ($templ_person["bk_event" . $eventnr] != '') {
                            $temp = "bk_event" . $eventnr;
                        }
                        $process_text .= $nameDb->event_event;

                        if ($nameDb->event_date || $nameDb->event_place) {
                            $templ_person["bk_date" . $eventnr] = ' (' . date_place($nameDb->event_date, $nameDb->event_place) . ')';
                            if ($templ_person["bk_date" . $eventnr] != '') {
                                $temp = "bk_date" . $eventnr;
                            }
                            $process_text .= $templ_person["bk_date" . $eventnr];
                        }

                        if ($nameDb->event_text) {
                            $templ_person["bk_text" . $eventnr] = ' ' . $nameDb->event_text;
                            $temp = "bk_text" . $eventnr;
                            $process_text .= process_text($templ_person["bk_text" . $eventnr]);
                        }

                        $source_array = show_sources2("person", "pers_event_source", $nameDb->event_id);
                        if ($source_array) {
                            if ($screen_mode == 'PDF') {
                                $templ_person["bk_source" . $eventnr] = $source_array['text'];
                                $temp = "bk_source" . $eventnr;
                            } else {
                                $process_text .= $source_array['text'];
                            }
                        }
                    }
                    unset($name_qry);
                }

                // *** Own code ***
                if ($user['group_own_code'] == 'j' and $personDb->pers_own_code) {
                    if ($temp) {
                        $templ_person[$temp] .= ", ";
                    }
                    //$templ_person["own_code"]=ucfirst($personDb->pers_own_code);
                    $templ_person["own_code"] = $personDb->pers_own_code;

                    if (!$temp)
                        $templ_person["own_code_start"] = __('Own code') . ': ';
                    else
                        $templ_person["own_code_start"] = lcfirst(__('Own code')) . ': ';

                    if (!$process_text || $data["family_expanded"] != 'compact') {
                        $text = '<b>' . __('Own code') . ':</b> ';
                    } else {
                        $text = ', <b>' . lcfirst(__('Own code')) . ':</b> ';
                    }
                    if ($process_text && $data["family_expanded"] != 'compact') {
                        $text = '<br>' . $text;
                    }
                    //PDF expanded view
                    //$process_text.='<span class="pers_own_code">'.$text.$templ_person["own_code"].'</span>';
                    $process_text .= '<span class="pers_own_code">' . $text . $personDb->pers_own_code . '</span>';

                    $temp = "own_code";
                }


                // ****************
                // *** BIRTH    ***
                // ****************
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_birth_date || $personDb->pers_birth_place) {
                    $nightfall = "";
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_birth_date_hebnight;
                    }
                    $templ_person["born_dateplacetime"] = date_place($personDb->pers_birth_date, $personDb->pers_birth_place, $nightfall);
                    if ($templ_person["born_dateplacetime"] != '') {
                        $temp = "born_dateplacetime";
                    }
                    $text .= $templ_person["born_dateplacetime"];
                }
                // *** Birth time ***
                if (isset($personDb->pers_birth_time) && $personDb->pers_birth_time) {
                    if ($templ_person["born_dateplacetime"]) {
                        $templ_person["born_dateplacetime"] .= ' ' . __('at') . ' ' . $personDb->pers_birth_time . ' ' . __('hour');
                    } else {
                        $templ_person["born_dateplacetime"] = ' ' . __('at') . ' ' . $personDb->pers_birth_time . ' ' . __('hour');
                    }
                    $temp = "born_dateplacetime";

                    $text .= ' ' . __('at') . ' ' . $personDb->pers_birth_time . ' ' . __('hour');
                }

                if ($user["group_texts_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_birth_text);
                    if ($work_text) {
                        //if($temp) { $templ_person[$temp].=", "; }
                        //$templ_person["born_text"]=" ".strip_tags($work_text);
                        $templ_person["born_text"] = " " . $work_text;
                        $temp = "born_text";
                        $text .= $templ_person["born_text"];
                    }
                }

                // *** Birth source ***
                $source_array = show_sources2("person", "pers_birth_source", $personDb->pers_gedcomnumber);
                if ($source_array) {
                    //test:
                    //$templ_person["born_first"]='bron!!';

                    $templ_person["born_source"] = $source_array['text'];
                    $temp = "born_source";
                    // *** Not necessary to do this in person_cls.php, this is processed in family script.
                    //elseif($screen_mode=='RTF') {
                    //	$templ_person["born_source"]=$source_array['text'];
                    //	$rtf_text=strip_tags($templ_person["born_source"],"<b><i>");
                    //	$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    //}
                    //else{
                    $text .= $dirmark1 . $source_array['text'];
                    //}

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_person["born_add"] = '';
                    $temp = "born_add";
                }

                // *** Birth declaration/ registration ***
                if ($personDb->pers_gedcomnumber) {
                    $text_array = witness($personDb->pers_gedcomnumber, 'birth_declaration');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        $templ_person["born_witn"] = '(' . __('birth declaration') . ': ' . $text_array['text'];
                        $temp = "born_witn";
                        $text .= ' ' . $templ_person["born_witn"];
                        if (isset($text_array['source'])) {
                            $templ_person["born_witn_source"] = $text_array['source'];
                            $temp = "born_witn_source";

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["born_witn_add"] = '';
                            $temp = "born_witn_add";

                            $text .= $text_array['source'];
                        }
                        //$templ_person["born_witn"].=')';
                        $templ_person[$temp] .= ')';
                        $text .= ')';
                    }
                }

                // *** Check for birth items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous)
                        $templ_person["born_start"] = ucfirst(__('born')) . ' ';
                    else {
                        $templ_person["born_start"] = __('born') . ' ';
                        if ($temp_previous) {
                            $templ_person[$temp_previous] .= ', ';
                        }
                    }

                    if (!$process_text or $data["family_expanded"] != 'compact') {
                        $text = '<b>' . ucfirst(__('born')) . '</b> ' . $text;
                    } else {
                        $text = ', <b>' . __('born') . '</b> ' . $text;
                    }
                    if ($process_text && $data["family_expanded"] != 'compact') {
                        $text = '<br>' . $text;
                    }
                    $process_text .= $text;
                }


                // ***************
                // *** BAPTISE ***
                // ***************
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_bapt_date || $personDb->pers_bapt_place) {
                    $templ_person["bapt_dateplacetime"] = date_place($personDb->pers_bapt_date, $personDb->pers_bapt_place);
                    if ($templ_person["bapt_dateplacetime"] != '') {
                        $temp = "bapt_dateplacetime";
                    }
                    $text = $templ_person["bapt_dateplacetime"];
                }
                if ($user["group_texts_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_bapt_text);
                    if ($work_text) {
                        //if($temp) { $templ_person[$temp].=", "; }
                        //$templ_person["bapt_text"]=' '.strip_tags($work_text);
                        $templ_person["bapt_text"] = ' ' . $work_text;
                        $temp = "bapt_text";
                        //$text.=", ".$work_text;
                        $text .= $templ_person["bapt_text"];
                    }
                }

                if ($user['group_religion'] == 'j' && $personDb->pers_religion) {
                    $templ_person["bapt_reli"] = " (" . __('religion') . ': ' . $personDb->pers_religion . ')';
                    $temp = "bapt_reli";
                    $text .= ' <span class="religion">(' . __('religion') . ': ' . $personDb->pers_religion . ')</span>';
                }

                // *** Baptise source ***
                $source_array = show_sources2("person", "pers_bapt_source", $personDb->pers_gedcomnumber);
                //if ($source_array){
                if ($source_array) {
                    //if($screen_mode=='PDF') {
                    $templ_person["bapt_source"] = $source_array['text'];
                    $temp = "bapt_source";
                    //}
                    //else
                    $text .= $source_array['text'];

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_person["bapt_add"] = '';
                    $temp = "bapt_add";
                }

                // *** Show baptise witnesses ***
                if ($personDb->pers_gedcomnumber) {
                    //$temp_text=witness($personDb->pers_gedcomnumber, 'baptism_witness');
                    //if ($temp_text){
                    //	if($temp) { $templ_person[$temp].=" ("; }
                    //	$templ_person["bapt_witn"]=__('baptism witness').': '.$temp_text.')';
                    //	$temp="bapt_witn";
                    //	$text.= ' ('.__('baptism witness').': '.$temp_text.')';
                    //}
                    $text_array = witness($personDb->pers_gedcomnumber, 'baptism_witness');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        $templ_person["bapt_witn"] = '(' . __('baptism witness') . ': ' . $text_array['text'];
                        $temp = "bapt_witn";
                        $text .= ' ' . $templ_person["bapt_witn"];
                        if (isset($text_array['source'])) {
                            $templ_person["bapt_witn_source"] = $text_array['source'];
                            $temp = "bapt_witn_source";

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["bapt_witn_add"] = '';
                            $temp = "bapt_witn_add";

                            $text .= $text_array['source'];
                        }
                        //$templ_person["bapt_witn"].=')';
                        $templ_person[$temp] .= ')';
                        $text .= ')';
                    }
                }

                // *** Geneanet/Geneweb godfather/ doopheffer ***
                if ($personDb->pers_gedcomnumber) {
                    //$temp_text=witness($personDb->pers_gedcomnumber, 'godfather');
                    //if ($temp_text){
                    //	if($temp) { $templ_person[$temp].=" ("; }
                    //	$templ_person["godfather"]=_('godfather').': '.$temp_text.')';
                    //	$temp="godfather";
                    //	$text.= ' ('.__('godfather').': '.$temp_text.')';
                    //}
                    // AUG 2022: NOT TESTED YET!!
                    $text_array = witness($personDb->pers_gedcomnumber, 'godfather');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        $templ_person["godfather"] = '(' . __('godfather') . ': ' . $text_array['text'];
                        $temp = "godfather";
                        $text .= ' ' . $templ_person["godfather"];
                        if (isset($text_array['source'])) {
                            $templ_person["godfather_source"] = $text_array['source'];
                            $temp = "godfather_source";

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["godfather_add"] = '';
                            $temp = "godfather_add";

                            $text .= $text_array['source'];
                        }
                        //$templ_person["godfather"].=')';
                        $templ_person[$temp] .= ')';
                        $text .= ')';
                    }
                }


                // *** check for baptise items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous)
                        $templ_person["bapt_start"] = ucfirst(__('baptised')) . ' ';
                    else {
                        $templ_person["bapt_start"] = __('baptised') . ' ';
                        if ($temp_previous) {
                            $templ_person[$temp_previous] .= ', ';
                        }
                    }

                    if (!$process_text || $data["family_expanded"] != 'compact') {
                        $text = '<b>' . ucfirst(__('baptised')) . '</b> ' . $text;
                    } else {
                        $text = ', <b>' . __('baptised') . '</b> ' . $text;
                    }
                    if ($process_text && $data["family_expanded"] != 'compact') {
                        $text = '<br>' . $text;
                    }
                    $process_text .= $text;
                }

                // *** Show age of living person ***
                if (($personDb->pers_bapt_date || $personDb->pers_birth_date) && !$personDb->pers_death_date && $personDb->pers_alive != 'deceased') {
                    $process_age = new calculate_year_cls;
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, '');
                    $templ_person["age_liv"] = $age;
                    if ($templ_person["age_liv"] != '') {
                        $temp = "age_liv";
                    }
                    $process_text .= $dirmark1 . $age;  // *** comma and space already in $age
                }


                // ******************
                // *** DEATH      ***
                // ******************
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_death_date || $personDb->pers_death_place) {
                    $nightfall = "";
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_death_date_hebnight;
                    }
                    $templ_person["dead_dateplacetime"] = date_place($personDb->pers_death_date, $personDb->pers_death_place, $nightfall);
                    if ($templ_person["dead_dateplacetime"] != '') {
                        $temp = "dead_dateplacetime";
                    }
                    $text = $templ_person["dead_dateplacetime"];
                }
                // *** Death time ***
                if (isset($personDb->pers_death_time) && $personDb->pers_death_time) {
                    $templ_person["dead_time"] = ' ' . __('at') . ' ' . $personDb->pers_death_time . ' ' . __('hour');
                    $temp = "dead_time";
                    $text .= $templ_person["dead_time"];
                }

                if ($user["group_texts_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_death_text);
                    if ($work_text) {
                        $templ_person["dead_text"] = ' ' . $work_text;
                        $temp = "dead_text";
                        $text .= $templ_person["dead_text"];
                    }
                }

                // *** Show age, by Yossi Beck ***
                if (($personDb->pers_bapt_date || $personDb->pers_birth_date) && $personDb->pers_death_date) {
                    $process_age = new calculate_year_cls;
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, $personDb->pers_death_date);
                    $templ_person["dead_age"] = $age;
                    if ($templ_person["dead_age"] != '') {
                        $temp = "dead_age";
                    }
                    $text .= $dirmark1 . $age;  // *** comma and space already in $age
                }

                $pers_death_cause = '';
                if ($personDb->pers_death_cause == 'murdered') {
                    $pers_death_cause = ', ' . __('cause of death') . ': ' . __('murdered');
                }
                if ($personDb->pers_death_cause == 'drowned') {
                    $pers_death_cause = ', ' . __('cause of death') . ': ' . __('drowned');
                }
                if ($personDb->pers_death_cause == 'perished') {
                    $pers_death_cause = ', ' . __('cause of death') . ': ' . __('perished');
                }
                if ($personDb->pers_death_cause == 'killed in action') {
                    $pers_death_cause = ', ' . __('killed in action');
                }
                if ($personDb->pers_death_cause == 'being missed') {
                    $pers_death_cause = ', ' . __('being missed');
                }
                if ($personDb->pers_death_cause == 'committed suicide') {
                    $pers_death_cause = ', ' . __('cause of death') . ': ' . __('committed suicide');
                }
                if ($personDb->pers_death_cause == 'executed') {
                    $pers_death_cause = ', ' . __('cause of death') . ': ' . __('executed');
                }
                if ($personDb->pers_death_cause == 'died young') {
                    $pers_death_cause = ', ' . __('died young');
                }
                if ($personDb->pers_death_cause == 'died unmarried') {
                    $pers_death_cause = ', ' . __('died unmarried');
                }
                if ($personDb->pers_death_cause == 'registration') {
                    $pers_death_cause = ', ' . __('registration');
                } //2 TYPE registration?
                if ($personDb->pers_death_cause == 'declared death') {
                    $pers_death_cause = ', ' . __('declared death');
                }
                if ($pers_death_cause) {
                    $templ_person["dead_cause"] = $pers_death_cause;
                    $temp = "dead_cause";
                    $text .= $pers_death_cause;
                } else {
                    if ($personDb->pers_death_cause) {
                        if ($temp) {
                            $templ_person[$temp] .= ", ";
                        }
                        $templ_person["dead_cause"] = __('cause of death') . ': ' . $personDb->pers_death_cause;
                        $temp = "dead_cause";
                        $text .= ', ' . __('cause of death') . ': ' . $personDb->pers_death_cause;
                    } elseif ($humo_option['death_shoa'] == "y" and $text != '') {
                        $camps = "Auschwitz|Oświęcim|Sobibor|Bergen-Belsen|Bergen Belsen|Treblinka|Holocaust|Shoah|Midden-Europa|Majdanek|Belzec|Chelmno|Dachau|Buchenwald|Sachsenhausen|Mauthausen|Theresienstadt|Birkenau|Kdo |Kamp Amersfoort|Gross-Rosen|Gross Rosen|Neuengamme|Ravensbrück|Kamp Westerbork|Kamp Vught|Kommando Sosnowice|Ellrich|Schöppenitz|Midden Europa|Lublin|Tröbitz|Kdo Bobrek|Golleschau|Blechhammer|Kdo Gleiwitz|Warschau|Szezdrzyk|Polen|Kamp Bobrek|Monowitz|Dorohucza|Seibersdorf|Babice|Fürstengrube|Janina|Jawischowitz|Katowice|Kaufering|Krenau|Langenstein|Lodz|Ludwigsdorf|Melk|Mühlenberg|Oranienburg|Sakrau|Schwarzheide|Spytkowice|Stutthof|Tschechowitz|Weimar|Wüstegiersdorf|Oberhausen|Minsk|Ghetto Riga|Ghetto Lodz|Flossenbürg|Malapane";
                        if (
                            preg_match("/($camps)/i", $personDb->pers_death_place) !== 0 or
                            preg_match("/($camps)/i", $personDb->pers_buried_place) !== 0 or strpos(strtolower($personDb->pers_death_place), "oorlogsslachtoffer") !== FALSE
                        ) {
                            if (!isset($personDb->pers_death_date) or (isset($personDb->pers_death_date) and  substr($personDb->pers_death_date, -4) > 1939 and substr($personDb->pers_death_date, -4) < 1946)) {
                                $text .= ', ' . __('cause of death') . ': ' . __('murdered');
                            }
                        }
                    }
                }

                // *** Death source ***
                $source_array = show_sources2("person", "pers_death_source", $personDb->pers_gedcomnumber);
                if ($source_array) {
                    //if($screen_mode=='PDF') {
                    $templ_person["dead_source"] = $source_array['text'];
                    $temp = "dead_source";
                    //}
                    //else
                    $text .= $source_array['text'];

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_person["dead_add"] = '';
                    $temp = "dead_add";
                }

                // *** Death declaration ***
                if ($personDb->pers_gedcomnumber) {
                    //$temp_text=witness($personDb->pers_gedcomnumber, 'death_declaration');
                    //if ($temp_text){
                    //	if ($temp) { $templ_person[$temp].=" ("; }
                    //	$templ_person["dead_witn"]= __('death declaration').': '.$temp_text.')';
                    //	$temp="dead_witn";
                    //	$text.= ' ('.__('death declaration').': '.$temp_text.')';
                    //}
                    $text_array = witness($personDb->pers_gedcomnumber, 'death_declaration');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        $templ_person["dead_witn"] = '(' . __('death declaration') . ': ' . $text_array['text'];
                        $temp = "dead_witn";
                        $text .= ' ' . $templ_person["dead_witn"];
                        if (isset($text_array['source'])) {
                            $templ_person["dead_witn_source"] = $text_array['source'];
                            $temp = "dead_witn_source";

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["dead_witn_add"] = '';
                            $temp = "dead_witn_add";

                            $text .= $text_array['source'];
                        }
                        //$templ_person["dead_witn"].=')';
                        $templ_person[$temp] .= ')';
                        $text .= ')';
                    }
                }

                // *** Check for death items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous)
                        $templ_person["dead_start"] = ucfirst(__('died')) . ' ';
                    else {
                        $templ_person["dead_start"] = __('died') . ' ';
                        if ($temp_previous) {
                            $templ_person[$temp_previous] .= ', ';
                        }
                    }

                    if (!$process_text || $data["family_expanded"] != 'compact') {
                        $text = '<b>' . ucfirst(__('died')) . '</b> ' . $text;
                    } else {
                        $text = ', <b>' . __('died') . '</b> ' . $text;
                    }
                    if ($process_text && $data["family_expanded"] != 'compact') {
                        $text = '<br>' . $text;
                    }
                    $process_text .= $text;
                }

                // ****************
                // *** BURIED   ***
                // ****************
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                    $nightfall = "";
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_buried_date_hebnight;
                    }
                    $templ_person["buri_dateplacetime"] = date_place($personDb->pers_buried_date, $personDb->pers_buried_place, $nightfall);
                    if ($templ_person["buri_dateplacetime"] != '') {
                        $temp = "buri_dateplacetime";
                    }
                    $text = $templ_person["buri_dateplacetime"];
                }
                if ($user["group_texts_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_buried_text);
                    if ($work_text) {
                        //if($temp) { $templ_person[$temp].=", "; }
                        //$templ_person["buri_text"]=' '.strip_tags($work_text);
                        $templ_person["buri_text"] = ' ' . $work_text;
                        $temp = "buri_text";
                        //$text.=", ".$work_text;
                        $text .= $templ_person["buri_text"];
                    }
                }

                // *** Buried source ***
                $source_array = show_sources2("person", "pers_buried_source", $personDb->pers_gedcomnumber);
                if ($source_array) {
                    //if($screen_mode=='PDF') {
                    $templ_person["buri_source"] = $source_array['text'];
                    $temp = "buri_source";
                    //}
                    //else
                    $text .= $source_array['text'];

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_person["buri_add"] = '';
                    $temp = "buri_add";
                }

                // *** Buried witness ***
                if ($personDb->pers_gedcomnumber) {
                    //$temp_text=witness($personDb->pers_gedcomnumber, 'burial_witness');
                    //if ($temp_text){
                    //	if($temp) { $templ_person[$temp].=' '; }
                    //	$templ_person["buri_witn"]= ' ('.__('burial witness').': '.$temp_text.')';
                    //	$temp="buri_witn";
                    //	$text.= $templ_person["buri_witn"];
                    //}
                    $text_array = witness($personDb->pers_gedcomnumber, 'burial_witness');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        $templ_person["buri_witn"] = '(' . __('burial witness') . ': ' . $text_array['text'];
                        $temp = "buri_witn";
                        $text .= ' ' . $templ_person["buri_witn"];
                        if (isset($text_array['source'])) {
                            $templ_person["buri_witn_source"] = $text_array['source'];
                            $temp = "buri_witn_source";

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["buri_witn_add"] = '';
                            $temp = "buri_witn_add";

                            $text .= $text_array['source'];
                        }
                        //$templ_person["buri_witn"].=')';
                        $templ_person[$temp] .= ')';
                        $text .= ')';
                    }
                }


                // *** Check for burial items, if needed use a new line ***
                if ($text) {
                    if ($personDb->pers_cremation == '1') {
                        $method_of_burial = __('cremation');
                    } elseif ($personDb->pers_cremation == 'R') {
                        $method_of_burial = __('resomated');
                    } elseif ($personDb->pers_cremation == 'S') {
                        $method_of_burial = __('sailor\'s grave');
                    } elseif ($personDb->pers_cremation == 'D') {
                        $method_of_burial = __('donated to science');
                    } else {
                        $method_of_burial = __('buried');
                    }

                    if (!$temp_previous)
                        $templ_person["buri_start"] = ucfirst($method_of_burial) . ' ';
                    else {
                        $templ_person["buri_start"] = $method_of_burial . ' ';
                        if ($temp_previous) {
                            $templ_person[$temp_previous] .= ', ';
                        }
                    }

                    if (!$process_text || $data["family_expanded"] != 'compact') {
                        $text = '<b>' . ucfirst($method_of_burial) . '</b> ' . $text;
                    } else {
                        $text = ', <b>' . $method_of_burial . '</b> ' . $text;
                    }
                    if ($process_text && $data["family_expanded"] != 'compact') {
                        $text = '<br>' . $text;
                    }
                    $process_text .= $text;
                }

                // *** HZ-21 ash dispersion (asverstrooiing) ***
                $name_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'ash dispersion');
                foreach ($name_qry as $nameDb) {
                    $process_text .= ', ' . __('ash dispersion') . ' ';
                    if ($nameDb->event_date) {
                        $process_text .= date_place($nameDb->event_date, '') . ' ';
                    }
                    $process_text .= $nameDb->event_event . ' ';
                    //SOURCE and TEXT.
                    //CHECK PDF EXPORT
                }

                // **************************
                // *** Show professions   ***
                // **************************
                if ($personDb->pers_gedcomnumber) {
                    $temp_previous = $temp;

                    $event_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'profession');
                    $nr_occupations = count($event_qry);
                    $eventnr = 0;
                    foreach ($event_qry as $eventDb) {
                        $eventnr++;
                        if ($eventnr == '1') {
                            if ($nr_occupations == '1') {
                                $occupation = __('occupation');
                            } else {
                                $occupation = __('occupations');
                            }
                            if ($data["family_expanded"] != 'compact') {
                                $process_text .= '<br><span class="profession"><b>' . ucfirst($occupation) . ':</b> ';
                            } else {
                                // punt hoort bij vorige item.
                                if ($process_text) {
                                    $process_text .= '. <span class="profession">';
                                }
                                $process_text .= '<b>' . ucfirst($occupation) . ':</b> ';
                            }

                            if ($temp) {
                                $templ_person[$temp] .= ". ";
                            }
                            $templ_person["prof_start"] = ucfirst($occupation) . ': ';
                        }
                        if ($eventnr > 1) {
                            $process_text .= ', ';

                            if ($data["family_expanded"] == 'expanded2') {
                                $process_text .= '<br>';
                            }
                            if ($temp) {
                                $templ_person[$temp] .= ", ";
                            }
                        }

                        // *** Show profession ***
                        $process_text .= $eventDb->event_event;
                        $templ_person["prof_prof" . $eventnr] = $eventDb->event_event;
                        $temp = "prof_prof" . $eventnr;

                        // *** Profession date and place ***
                        if ($eventDb->event_date || $eventDb->event_place) {
                            $templ_person["prof_date" . $eventnr] = ' (' . date_place($eventDb->event_date, $eventDb->event_place) . ')';
                            $temp = "prof_date" . $eventnr;
                            $process_text .= $templ_person["prof_date" . $eventnr];
                        }

                        if ($eventDb->event_text) {
                            $work_text = process_text($eventDb->event_text);
                            if ($work_text) {
                                //if($temp) { $templ_person[$temp].=", "; }
                                if ($temp) {
                                    $templ_person[$temp] .= " ";
                                }
                                $templ_person["prof_text" . $eventnr] = strip_tags($work_text);
                                $temp = "prof_text" . $eventnr;

                                //$process_text.=", ".$work_text;
                                $process_text .= " " . $work_text;
                            }
                        }

                        // *** Profession source ***
                        $source_array = show_sources2("person", "pers_event_source", $eventDb->event_id);
                        if ($source_array) {
                            //if($screen_mode=='PDF') {
                            $templ_person["prof_source"] = $source_array['text'];
                            $temp = "prof_source";
                            //}
                            //else
                            $process_text .= $source_array['text'];

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["prof_add"] = '';
                            $temp = "prof_add";
                        }
                    }
                    if ($eventnr > 0) {
                        $process_text .= '</span>';
                    }
                }

                // ***********************
                // *** Show religion   ***
                // ***********************
                if ($personDb->pers_gedcomnumber) {
                    $temp_previous = $temp;

                    $event_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'religion');
                    $nr_occupations = count($event_qry);
                    $eventnr = 0;
                    foreach ($event_qry as $eventDb) {
                        $eventnr++;
                        if ($eventnr == '1') {
                            $religion = $nr_occupations == '1' ? __('religion') : __('religions');
                            if ($data["family_expanded"] != 'compact') {
                                $process_text .= '<br><span class="religion"><b>' . ucfirst($religion) . ':</b> ';
                            } else {
                                if ($process_text) {
                                    $process_text .= '. <span class="religion">';
                                }
                                $process_text .= '<b>' . ucfirst($religion) . ':</b> ';
                            }

                            if ($temp) {
                                $templ_person[$temp] .= ". ";
                            }
                            $templ_person["religion_start"] = ucfirst($religion) . ': ';
                        }
                        if ($eventnr > 1) {
                            $process_text .= ', ';
                            if ($temp) {
                                $templ_person[$temp] .= ", ";
                            }
                        }

                        // *** Show religion ***
                        $process_text .= $eventDb->event_event;
                        $templ_person["religion_religion" . $eventnr] = $eventDb->event_event;
                        $temp = "religion_religion" . $eventnr;

                        // *** Religion date and place ***
                        if ($eventDb->event_date || $eventDb->event_place) {
                            $templ_person["religion_date" . $eventnr] = ' (' . date_place($eventDb->event_date, $eventDb->event_place) . ')';
                            $temp = "religion_date" . $eventnr;
                            $process_text .= $templ_person["religion_date" . $eventnr];
                        }

                        if ($eventDb->event_text) {
                            $work_text = process_text($eventDb->event_text);
                            if ($work_text) {
                                //if($temp) { $templ_person[$temp].=", "; }
                                if ($temp) {
                                    $templ_person[$temp] .= " ";
                                }
                                $templ_person["religion_text" . $eventnr] = strip_tags($work_text);
                                $temp = "religion_text" . $eventnr;

                                //$process_text.=", ".$work_text;
                                $process_text .= " " . $work_text;
                            }
                        }

                        // *** Religion source ***
                        $source_array = show_sources2("person", "pers_event_source", $eventDb->event_id);
                        if ($source_array) {
                            //if($screen_mode=='PDF') {
                            $templ_person["religion_source"] = $source_array['text'];
                            $temp = "religion_source";
                            //}
                            //else
                            $process_text .= $source_array['text'];

                            // *** Extra item, so it's possible to add a comma or space ***
                            $templ_person["religion_add"] = '';
                            $temp = "religion_add";
                        }
                    }
                    if ($eventnr > 0) {
                        $process_text .= '</span>';
                    }
                }

                // *********************************
                // *** Show residences/addresses ***
                // *********************************
                if ($personDb->pers_gedcomnumber && $user['group_living_place'] == 'j') {
                    $text = show_addresses('person', 'person_address', $personDb->pers_gedcomnumber);

                    if ($process_text and $text) {
                        if ($data["family_expanded"] != 'compact') {
                            $text = '<br>' . $text;
                        } else {
                            $text = '. ' . $text;
                        }
                    }

                    if ($text) {
                        $process_text .= '<span class="pers_living_place">' . $text . '</span>';
                    }
                }

                // *** Person source ***
                $source_array = show_sources2("person", "person_source", $personDb->pers_gedcomnumber);
                if ($source_array) {
                    if ($screen_mode == 'PDF') {
                        if ($temp) $templ_person[$temp] .= '. ';
                        $templ_person["pers_source"] = $source_array['text'];
                        $temp = "pers_source";
                    } else {
                        $process_text .= $source_array['text'];
                    }
                }

                // *** This person was witness at.... ***
                if ($screen_mode == 'PDF') {
                    $templ_person["witness_by_event"] = "\n" . witness_by_events($personDb->pers_gedcomnumber);
                } else {
                    $process_text .= witness_by_events($personDb->pers_gedcomnumber);
                }
            } //*** END PRIVACY PART ***

            // *** Use a link for multiple marriages by parent2 ***
            // TODO improve extended view.
            if ($person_kind == 'parent2') {
                $marriage_array = explode(";", $personDb->pers_fams);
                if (isset($marriage_array[1])) {

                    // *** Show marriage line ar new line ***
                    $process_text .= "<br>\n";

                    foreach ($marriage_array as $i => $value) {
                        $marriagenr = $i + 1;
                        $parent2_famDb = $db_functions->get_family($marriage_array[$i]);
                        // *** Use a class for marriage ***
                        // Construct for marriage privacy filter is missing? Probably not needed here because no dates are shown.
                        $parent2_marr_cls = new marriage_cls;

                        // *** Show standard marriage text ***
                        if ($screen_mode != "PDF") {
                            $parent2_marr_data = $parent2_marr_cls->marriage_data($parent2_famDb, $marriagenr, 'short');
                        } else {
                            $pdf_marriage = $parent2_marr_cls->marriage_data($parent2_famDb, $marriagenr, 'short');
                        }

                        if ($swap_parent1_parent2 == true) {
                            $parent2Db = $db_functions->get_person($parent2_famDb->fam_woman);
                        } else {
                            $parent2Db = $db_functions->get_person($parent2_famDb->fam_man);
                        }

                        if ($id == $marriage_array[$i]) {
                            //if ($process_text) $process_text .= ',';
                            if ($process_text and $marriagenr > 1) {
                                $process_text .= ',';
                            }
                            if (isset($parent2_marr_data)) {
                                $process_text .= ' ' . $dirmark1 . $parent2_marr_data . ' ';
                            }

                            // *** $parent2Db is empty if it is a N.N. person ***
                            if ($parent2Db) {
                                $name = $this->person_name($parent2Db);
                                $process_text .= $name["standard_name"];
                            } else {
                                $process_text .= __('N.N.');
                            }
                        } else {
                            if ($marriagenr > 1) {
                                $process_text .= ', ';
                            }

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $this->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                            $process_text .= '<a href="' . $url . '">';

                            if (isset($parent2_marr_data)) {
                                $process_text .= $dirmark1 . $parent2_marr_data . ' ';
                            }
                            // *** $parent2Db is empty by N.N. person ***
                            if ($parent2Db) {
                                $name = $this->person_name($parent2Db);
                                $process_text .= $name["standard_name"];
                            } else {
                                $process_text .= __('N.N.');
                            }
                            $process_text .= '</a>';
                        }
                        if ($screen_mode == "PDF") {
                            if ($parent2Db) {
                                if ($temp) {
                                    $templ_person[$temp] .= ", ";
                                }
                                $name = $this->person_name($parent2Db);
                                $templ_person["marr_more" . $marriagenr] = $pdf_marriage["relnr_rel"] . $pdf_marriage["rel_add"] . " " . $name["standard_name"];
                                $temp = "marr_more" . $marriagenr;
                            } else {
                                if ($temp) {
                                    $templ_person[$temp] .= ", ";
                                }
                                $templ_person["marr_more" . $marriagenr] = $pdf_marriage["relnr_rel"] . " " . __('N.N.');
                                $temp = "marr_more" . $marriagenr;
                            }
                        }
                    }
                }
            }

            //if ($privacy){
            if ($screen_mode == "mobile" || $privacy) {
                //
            } else {
                // *** Show media/ pictures ***
                $result = show_media('person', $personDb->pers_gedcomnumber); // *** This function can be found in file: show_picture.php! ***
                $process_text .= $result[0];
                if (isset($templ_person)) {
                    $templ_person = array_merge((array)$templ_person, (array)$result[1]);
                    //$templ_person = array_merge($templ_person, $result[1]);
                } else {
                    $templ_person = $result[1];
                }

                // *** Internet links (URL) ***
                $url_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'URL');
                if (count($url_qry) > 0) {
                    $process_text .= "<br>\n";
                }
                foreach ($url_qry as $urlDb) {
                    //URL/ Internet link
                    $process_text .= '<b>' . __('URL/ Internet link') . '</b> <a href="' . $urlDb->event_event . '" target="_blank">' . $urlDb->event_event . '</a>';
                    if ($urlDb->event_text) {
                        $process_text .= ' ' . process_text($urlDb->event_text);
                    }
                    $process_text .= "<br>\n";
                }

                //******** Text by person **************
                if ($user["group_text_pers"] == 'j') {
                    $work_text = process_text($personDb->pers_text, 'person');

                    if ($work_text) {
                        //$process_text.='<br>'.$work_text."\n";
                        $process_text .= '<br>' . $work_text;
                        // clean html tags
                        $tx = strip_tags($work_text);
                        $templ_person["pers_text"] = "\n" . $tx;
                        $temp = "pers_text";
                    }

                    // *** BK & HuMo-genealogy: Source by person text ***
                    $source_array = show_sources2("person", "pers_text_source", $personDb->pers_gedcomnumber);
                    if ($source_array) {
                        if ($screen_mode == 'PDF') {
                            //$source_array=show_sources2("person","pers_text_source",$personDb->pers_gedcomnumber);
                            $templ_person["pers_text_source"] = $source_array['text'];
                            $temp = "pers_text_source";
                        } else {
                            //$work_text.=$source_array['text'];
                            $process_text .= $source_array['text'];
                        }
                    }
                }

                // *** Show events ***
                if ($user['group_event'] == 'j') {
                    if ($personDb->pers_gedcomnumber) {
                        $event_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'event');
                        $num_rows = count($event_qry);
                        if ($num_rows > 0) {
                            $process_text .= '<span class="event">' . "\n";
                        }
                        $eventnr = 0;
                        foreach ($event_qry as $eventDb) {
                            $eventnr++;
                            $process_text .= "<br>\n";
                            $templ_person["event_start" . $eventnr] = "\n";

                            // *** Check if NCHI is 0 or higher ***
                            $event_gedcom = $eventDb->event_gedcom;
                            //$event_text=$eventDb->event_text;
                            if ($event_gedcom == 'NCHI' and trim($eventDb->event_event) == '') {
                                $event_gedcom = 'NCHI0';
                                //$event_text='';
                            }

                            $process_text .= '<b>' . language_event($event_gedcom) . '</b>';
                            $templ_person["event_ged" . $eventnr] = language_event($event_gedcom);
                            $temp = "event_ged" . $eventnr;

                            if ($eventDb->event_event) {
                                $templ_person["event_event" . $eventnr] = ' ' . $eventDb->event_event;
                                $temp = "event_event" . $eventnr;
                                $process_text .= $templ_person["event_event" . $eventnr];
                            }

                            if ($eventDb->event_date or $eventDb->event_place) {
                                //$templ_person["event_dateplace".$eventnr]=' '.date_place($eventDb->event_date, $eventDb->event_place);
                                $templ_person["event_dateplace" . $eventnr] = ' (' . date_place($eventDb->event_date, $eventDb->event_place) . ')';
                                $temp = "event_dateplace" . $eventnr;
                                $process_text .= $templ_person["event_dateplace" . $eventnr];
                            }

                            if ($eventDb->event_text) {
                                $work_text = process_text($eventDb->event_text);
                                if ($work_text) {
                                    //$process_text.=", ".$work_text;
                                    $process_text .= " " . $work_text;
                                    //if($temp) { $templ_person[$temp].=", "; }
                                    if ($temp) {
                                        $templ_person[$temp] .= " ";
                                    }
                                    $templ_person["event_text" . $eventnr] = $work_text;
                                    $temp = "event_text" . $eventnr;
                                }
                            }

                            $source_array = show_sources2("person", "pers_event_source", $eventDb->event_id);
                            if ($source_array) {
                                if ($screen_mode == 'PDF') {
                                    $templ_person["pers_event_source" . $eventnr] = $source_array['text'];
                                    $temp = "pers_event_source" . $eventnr;
                                } else {
                                    $process_text .= $source_array['text'];
                                }
                            }
                        }
                        if ($num_rows > 0) {
                            $process_text .= "</span>\n";
                        }
                        unset($event_qry);
                    }
                }
            } // End of privacy

            // *** April 2024: added child marriage below child items ***
            //*** Show spouse/ partner by child ***
            $child_marriage = $this->get_child_partner($db_functions, $personDb, $person_kind);
            if ($child_marriage) {
                $templ_person["pers_child_spouse"] = "\n" . $child_marriage;
                $process_text .= "<br>\n" . $child_marriage;
            }

            // *** Return person data ***
            if ($screen_mode == "mobile") {
                if ($process_text) {
                    return $process_text;
                }
            } elseif ($screen_mode != "PDF") {
                if ($process_text) {
                    $div = '<div class="margin_person">';
                    if ($person_kind == 'child') {
                        $div = '<div class="margin_child">';
                    }
                    return $privacy_filter . $div . $process_text . '</div>';
                } else {
                    return $privacy_filter;
                }
            } else {   // return array with pdf values
                if (isset($templ_person)) {
                    foreach ($templ_person as $key => $val) {
                        $templ_person[$key] = strip_tags($val);
                    }
                    return $templ_person;
                }
            }
        } // End of check $personDb

    } // End of function person_data.
} // End of person_cls
