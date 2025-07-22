<?php

/**
 * Show person name and name of parents
 * 
 * $person_kind = 'child' generates a link by a child to his family
 * Oct. 2013: added name of parents after the name of a person.
 */

namespace Genealogy\Include;

use Genealogy\Include\PersonLink;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonPopup;
use Genealogy\Include\ProcessText;
use Genealogy\Include\ShowSources;
use Genealogy\Include\CalculateDates;

class PersonNameExtended
{
    public function name_extended($personDb, $privacy, $person_kind, $show_name_texts = false)
    {
        // TODO check globals
        global $db_functions, $humo_option, $user, $screen_mode, $dirmark1, $dirmark2;
        global $sect; // *** RTF Export ***
        global $templ_name, $familyDb, $data;

        $personLink = new PersonLink;
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $personPopup = new PersonPopup();
        $processText = new ProcessText();
        $showSources = new ShowSources();

        $start_name = '';
        $text_name = '';
        $text_name2 = '';
        $text_colour = '';
        $text_parents = '';
        $child_marriage = '';

        if (!$personDb) {
            // *** Show unknown person N.N. ***
            $templ_name["name_name"] = __('N.N.');
            $text_name = __('N.N.');
        } else {
            $db_functions->set_tree_id($personDb->pers_tree_id);

            // *** Show pop-up menu ***
            $start_name .= $personPopup->person_popup_menu($personDb, $privacy);

            // *** Check privacy filter ***
            if ($privacy && $user['group_filter_name'] == 'n') {
                //dummy
            } else {
                // *** Show man or woman picture ***
                // *** Ancestor reports uses cells, not sections. Ancestor M/F/? icons are generated in ancestor script ***
                if ($screen_mode == "RTF" and !isset($_POST['ancestor_report'])) {
                    // *** RTF person pictures in JPG, because Word doesn't support GIF pictures... ***
                    if ($personDb->pers_sexe == "M") {
                        $sect->addImage('images/man.jpg', null);
                    } elseif ($personDb->pers_sexe == "F") {
                        $sect->addImage('images/woman.jpg', null);
                    } else {
                        $sect->addImage('images/unknown.jpg', null);
                    }
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
                    if ($person_kind != 'outline' and $person_kind != 'outline_pdf') {
                        $source_array = $showSources->show_sources2("person", "pers_sexe_source", $personDb->pers_gedcomnumber);
                    }
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

            $name = $personName->get_person_name($personDb, $privacy, $show_name_texts);
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
                $url = $personLink->get_person_link($personDb);

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
                    $work_text = $processText->process_text($personDb->pers_name_text);
                    if ($work_text) {
                        $templ_name["name_text"] = " " . $work_text;
                        $text_name2 .= " " . $work_text;
                    }
                }

                // *** Source by name ***
                $source_array = '';
                if ($person_kind != 'outline' && $person_kind != 'outline_pdf') {
                    $source_array = $showSources->show_sources2("person", "pers_name_source", $personDb->pers_gedcomnumber);
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
            // TODO check global.
            global $relation_check;
            if (!$privacy && $person_kind == 'parent2' && $familyDb->fam_marr_date != '') {
                $process_age = new CalculateDates;
                if ($relation_check == true) {
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, $familyDb->fam_marr_date, false, 'relation');
                } else {
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, $familyDb->fam_marr_date, false, 'marriage');
                }
                $templ_name["name_wedd_age"] = $age;
                $text_name2 .= $age;
            }

            /**
             * Show: son of/ daughter of/ child of name-father & name-mother
             */
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
                    $privacy_father = $personPrivacy->get_privacy($fatherDb);
                    $name = $personName->get_person_name($fatherDb, $privacy_father);
                    $templ_name["name_parents"] .= $name["standard_name"];

                    // *** Seperate father/mother links ***
                    $gedcomnumber = '';
                    if (isset($fatherDb->pers_gedcomnumber)) {
                        $gedcomnumber = $fatherDb->pers_gedcomnumber;
                    }

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $personLink->get_person_link($fatherDb);

                    // *** Add link ***
                    if ($user['group_gen_protection'] == 'n') {
                        $text = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }
                } else {
                    // *** Seperate father/mother links ***
                    $templ_name["name_parents"] .= __('N.N.');
                    $text = __('N.N.');
                }

                // *** Seperate father/mother links ***
                $templ_name["name_parents"] .= ' ' . __('and') . ' ';
                $text .= ' ' . __('and') . ' ';

                // *** Mother ***
                if ($parents_familyDb->fam_woman) {
                    $motherDb = $db_functions->get_person($parents_familyDb->fam_woman);
                    $privacy_mother = $personPrivacy->get_privacy($motherDb);
                    $name = $personName->get_person_name($motherDb, $privacy_mother);
                    $templ_name["name_parents"] .= $name["standard_name"];

                    // *** Seperate father/mother links ***
                    $gedcomnumber = '';
                    if (isset($motherDb->pers_gedcomnumber)) {
                        $gedcomnumber = $motherDb->pers_gedcomnumber;
                    }

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $personLink->get_person_link($motherDb);

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


            /**
             * Check for adoptive parents (just for sure: made it for multiple adoptive parents...)
             */
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
                    if (isset($parents_familyDb->fam_man) && $parents_familyDb->fam_man) {
                        $fatherDb = $db_functions->get_person($parents_familyDb->fam_man);
                        $privacy_father = $personPrivacy->get_privacy($fatherDb);
                        $name = $personName->get_person_name($fatherDb, $privacy_father);

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

                    //*** Mother ***
                    if (isset($parents_familyDb->fam_woman) && $parents_familyDb->fam_woman) {
                        $motherDb = $db_functions->get_person($parents_familyDb->fam_woman);
                        $privacy_mother = $personPrivacy->get_privacy($motherDb);
                        $name = $personName->get_person_name($motherDb, $privacy_mother);
                        $templ_name["name_parents"] .= $name["standard_name"];
                        $text .= $name["standard_name"];
                    } else {
                        $templ_name["name_parents"] .= __('N.N.');
                        $text .= __('N.N.');
                    }

                    $url = '';
                    if (isset($parents_familyDb->fam_man) && $parents_familyDb->fam_man) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $personLink->get_person_link($fatherDb);
                    } elseif (isset($parents_familyDb->fam_woman) && $parents_familyDb->fam_woman) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $personLink->get_person_link($motherDb);
                    }

                    // *** Add link ***
                    if ($user['group_gen_protection'] == 'n') {
                        $text = '<a href="' . $url . '">' . $text . '</a>';
                    }

                    $templ_name["name_parents"] .= '.';

                    //$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
                    $text_parents .= '<span class="parents">' . $text . '.</span>';
                }
            }

            /**
             * Check for adoptive parent ESPECIALLY FOR ALDFAER and MyHeritage
             */
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
                            $privacy_father = $personPrivacy->get_privacy($fatherDb);
                            $name = $personName->get_person_name($fatherDb, $privacy_father);
                            $templ_name["name_parents"] .= $name["standard_name"];
                            $text = $name["standard_name"];

                            $url = $personLink->get_person_link($fatherDb);
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
                            $privacy_mother = $personPrivacy->get_privacy($motherDb);
                            $name = $personName->get_person_name($motherDb, $privacy_mother);
                            $templ_name["name_parents"] .= ' ' . $name["standard_name"];

                            $url = $personLink->get_person_link($motherDb);
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
                        $privacy_father = $personPrivacy->get_privacy($fatherDb);
                        $name = $personName->get_person_name($fatherDb, $privacy_father);
                        $templ_name["name_parents"] .= $name["standard_name"];
                        $text = $name["standard_name"];

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        if (isset($fatherDb->pers_tree_id)) {
                            $url = $personLink->get_person_link($fatherDb);
                        }
                        // *** Add link ***
                        if (isset($url) && $user['group_gen_protection'] == 'n')
                            $text = '<a href="' . $url . '">' . $text . '</a>';
                    }

                    $templ_name["name_parents"] .= '.';

                    //$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
                    $text_parents .= '<span class="parents">' . $text . '.</span>';
                }
            }
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
}
