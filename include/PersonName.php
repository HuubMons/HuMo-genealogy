<?php

/**
 * June 2025 Huub Mons: separate class for person name handling.
 * 
 * Show person name standard
 * 
 * Remark: it's necessary to use $personDb because of witnesses, parents etc.
 */

namespace Genealogy\Include;

use Genealogy\Include\ProcessText;

class PersonName
{
    private $db_functions, $pers_gedcomnumber, $show_name_texts, $processText;

    public function __construct()
    {
        $this->processText = new ProcessText();
    }

    public function get_person_name($personDb, bool $privacy, $show_name_texts = false)
    {
        global $db_functions, $user, $screen_mode, $selection, $humo_option;

        $this->db_functions = $db_functions;
        if (isset($personDb->pers_gedcomnumber)) {
            $this->pers_gedcomnumber = $personDb->pers_gedcomnumber;
        }
        $this->show_name_texts = $show_name_texts;

        if (isset($personDb->pers_gedcomnumber) && $personDb->pers_gedcomnumber) {
            $this->db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Show nicknames (shown as "Nickname") ***
            $nickname = $this->get_nickname();

            // *** Aldfaer: nobility (predikaat) by name ***
            $nobility = $this->get_nobility();

            // *** Aldfaer: lordship (heerlijkheid) after name ***
            $lordship = $this->get_lordship();

            // *** Gedcom 5.5 title: NPFX ***
            $title_before = $this->get_title_before();

            // *** Gedcom 5.5 title: NSFX ***
            $title_after = $this->get_title_after();

            // *** Aldfaer: title by name ***
            $title_between = '';
            $title_array = $this->get_title_aldfaer();
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
                            $pers_firstname .= $this->processText->process_text($rufnameDb->event_text);
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
                if ($nickname and (!$privacy or ($privacy and $user['group_filter_name'] == 'j'))) {
                    if ($name_array["index_name"]) $name_array["index_name"] .= ' ';
                    //$name_array["index_name"] .= '&quot;' . $nickname . '&quot;';
                    $name_array["index_name"] .= '"' . $nickname . '"';
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
                if ($nickname and (!$privacy or ($privacy and $user['group_filter_name'] == 'j'))) {
                    if ($name_array["index_name_extended"]) $name_array["index_name_extended"] .= ' ';
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

    // *** Show nicknames (shown as "Nickname") ***
    private function get_nickname(): string
    {
        $nickname = '';
        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'name');
        foreach ($name_qry as $nameDb) {
            if ($nameDb->event_gedcom == 'NICK') {
                if ($nickname) {
                    $nickname .= ', ';
                }
                $nickname .= $nameDb->event_event;
                // *** Remark: date, place and source are shown in function: person_data ***
            }
        }
        unset($name_qry);
        return $nickname;
    }

    // *** Aldfaer: nobility (predikaat) by name ***
    private function get_nobility(): string
    {
        $nobility = '';
        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'nobility');
        foreach ($name_qry as $nameDb) {
            if ($nobility) {
                $nobility .= ' ';
            }
            $nobility .= $nameDb->event_event;

            if ($this->show_name_texts == true and $nameDb->event_text) {
                if ($nobility) {
                    $nobility .= ' ';
                }
                $nobility .= $this->processText->process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $nobility;
    }

    // *** Aldfaer: lordship (heerlijkheid) after name ***
    private function get_lordship(): string
    {
        $lordship = '';
        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'lordship');
        foreach ($name_qry as $nameDb) {
            if ($lordship) {
                $lordship .= ', ';
            }
            $lordship .= $nameDb->event_event;

            if ($this->show_name_texts == true and $nameDb->event_text) {
                if ($lordship) {
                    $lordship .= ' ';
                }
                $lordship .= $this->processText->process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $lordship;
    }

    // *** Gedcom 5.5 title: NPFX ***
    private function get_title_before(): string
    {
        $title_before = '';
        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'NPFX');
        foreach ($name_qry as $nameDb) {
            if ($title_before) {
                $title_before .= ' ';
            }
            $title_before .= $nameDb->event_event;

            if ($this->show_name_texts == true and $nameDb->event_text) {
                if ($title_before) {
                    $title_before .= ' ';
                }
                $title_before .= $this->processText->process_text($nameDb->event_text);
            }
        }
        unset($name_qry);
        return $title_before;
    }

    // *** Gedcom 5.5 title: NSFX ***
    private function get_title_after(): string
    {
        $title_after = '';
        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'NSFX');
        foreach ($name_qry as $nameDb) {
            if ($title_after) {
                $title_after .= ' ';
            }
            $title_after .= $nameDb->event_event;

            if ($this->show_name_texts == true and $nameDb->event_text) {
                if ($title_after) {
                    $title_after .= ' ';
                }
                $title_after .= $this->processText->process_text($nameDb->event_text);
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
    private function get_title_aldfaer(): array
    {
        $title['before'] = '';
        $title['between'] = '';
        $title['after'] = '';

        $name_qry = $this->db_functions->get_events_connect('person', $this->pers_gedcomnumber, 'title');
        foreach ($name_qry as $nameDb) {
            $titles_before = ['Ir.', 'Mr.', 'Drs.', 'Lic.', 'Kand.', 'Bacc.', 'Ing.', 'Bc.', 'em.', 'Ds.'];
            $titles_between = ['prins', 'prinses', 'hertog', 'hertogin', 'markies', 'markiezin', 'markgraaf', 'markgravin', 'graaf', 'gravin', 'burggraaf', 'burggravin', 'baron', 'barones', 'ridder'];

            // Two exceptions at request of users, so it's possible to process multiple titles in one name field.
            // Dr., Dr.h.c., Dr.h.c.mult. And other titles starting with "Dr.".
            // Multiple titles starting with "Prof.".
            if (in_array($nameDb->event_event, $titles_before) || substr($nameDb->event_event, 0, 3) == 'Dr.' || substr($nameDb->event_event, 0, 5) == 'Prof.') {
                if ($title['before']) {
                    $title['before'] .= ' ';
                }
                $title['before'] .= $nameDb->event_event;

                if ($this->show_name_texts == true and $nameDb->event_text) {
                    if ($title['before']) {
                        $title['before'] .= ' ';
                    }
                    $title['before'] .= $this->processText->process_text($nameDb->event_text);
                }
            } elseif (in_array($nameDb->event_event, $titles_between)) {
                if ($title['between']) {
                    $title['between'] .= ' ';
                }
                $title['between'] .= $nameDb->event_event;

                if ($this->show_name_texts == true and $nameDb->event_text) {
                    if ($title['between']) {
                        $title['between'] .= ' ';
                    }
                    $title['between'] .= $this->processText->process_text($nameDb->event_text);
                }
            } else {
                if ($title['after']) {
                    $title['after'] .= ' ';
                }
                $title['after'] .= $nameDb->event_event;

                if ($this->show_name_texts == true and $nameDb->event_text) {
                    if ($title['after']) {
                        $title['after'] .= ' ';
                    }
                    $title['after'] .= $this->processText->process_text($nameDb->event_text);
                }
            }
        }
        unset($name_qry);
        return $title;
    }
}
