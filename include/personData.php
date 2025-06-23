<?php

/**
 * Process person data
 * Class for HuMo-genealogy program
 * $templ_person is used for PDF reports
 */

class PersonData
{
    /**
     * Show person
     * 
     * $personDb = data person
     * $privacy = privacyfilter
     * $person_kind = parent2 (normally the woman, generates links to all marriages)
     * $id = family id for link by woman for multiple marrriages
     */

    private $date_place;

    public function __construct()
    {
        $this->date_place = new DatePlace;
    }

    public function person_data($personDb, $privacy, $person_kind, $id)
    {
        // TODO check globals
        global $db_functions, $user, $humo_option, $swap_parent1_parent2;
        global $screen_mode, $dirmark1, $temp, $templ_person, $data;

        $person_privacy = new PersonPrivacy;
        $person_name = new PersonName;
        $person_link = new PersonLink;

        // *** $personDb is empty by N.N. person ***
        if ($personDb) {
            $db_functions->set_tree_id($personDb->pers_tree_id);

            $process_text = '';
            $temp = '';

            //*** PRIVACY PART ***
            $privacy_filter = '';
            if ($privacy) {
                if ($screen_mode != "PDF") {
                    $privacy_filter = ' ' . __('PRIVACY FILTER');
                } else {
                    // makes no sense to ask for login in a pdf report.....
                    return null;
                }
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
                    $known_as = false; // *** Only show start text once ***
                    foreach ($name_qry as $nameDb) {
                        $eventnr++;
                        $text = '';
                        if ($nameDb->event_gedcom == '_AKAN' && !$known_as) {
                            $text .= __('Also known as') . ': ';
                            $known_as = true;
                        }

                        // *** MyHeritage Family Tree Builder. Only show first "Also known as" text ***
                        if ($nameDb->event_gedcom == '_AKA' && $previous_event_gedcom != '_AKA' && !$known_as) {
                            $text .= __('Also known as') . ': ';
                            $known_as = true;
                        }

                        // *** December 2021: Nickname is allready shown as "Nickname".
                        //      Nickname is allready shown in function person_name, extra items like date, place, text and source will be shown here ***
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
                            $templ_person["bk_date" . $eventnr] = ' (' . $this->date_place->date_place($nameDb->event_date, $nameDb->event_place) . ')';
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
                    $text = '';
                    if ($temp) {
                        if ($data["family_expanded"] != 'compact') {
                            $templ_person[$temp] .= '.';
                            // *** Because of <span> the <br> is added in text ***
                            $text = '.<br>';
                        } else {
                            $templ_person[$temp] .= ', ';
                            $text = ', ';
                        }
                    }

                    if (!$temp || $data["family_expanded"] != 'compact') {
                        $templ_person["own_code_start"] = __('Own code') . ': ';
                        $text .= '<b>' . __('Own code') . ':</b> ';
                    } else {
                        $templ_person["own_code_start"] = lcfirst(__('Own code')) . ': ';
                        $text .= '<b>' . lcfirst(__('Own code')) . ':</b> ';
                    }

                    $templ_person["own_code"] = $personDb->pers_own_code;

                    $process_text .= '<span class="pers_own_code">' . $text . $personDb->pers_own_code . '</span>';

                    $temp = "own_code";
                }


                // ****************
                // *** BIRTH    ***
                // ****************
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_birth_date || $personDb->pers_birth_place) {
                    $nightfall = '';
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_birth_date_hebnight;
                    }
                    $templ_person["born_dateplacetime"] = $this->date_place->date_place($personDb->pers_birth_date, $personDb->pers_birth_place, $nightfall);
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
                    // *** Not necessary to do this in personData.php, this is processed in family script.
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

                // *** Check for birth items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous || $data["family_expanded"] != 'compact') {
                        $templ_person["born_start"] = ucfirst(__('born')) . ' ';
                        $text = '<b>' . ucfirst(__('born')) . '</b> ' . $text;
                    } else {
                        $templ_person["born_start"] = __('born') . ' ';
                        $text = '<b>' . __('born') . '</b> ' . $text;
                    }

                    if ($temp_previous) {
                        if ($data["family_expanded"] != 'compact') {
                            $templ_person[$temp_previous] .= '.';
                            $text = '.<br>' . $text;
                        } else {
                            $templ_person[$temp_previous] .= ', ';
                            $text = ', ' . $text;
                        }
                    }
                    $process_text .= $text;
                }

                /**
                 * BIRTH Declaration/ registration
                 */
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_gedcomnumber) {
                    // *** Sept. 2024: birth declaration and birth declaration witnesses are now seperate events *** 
                    $birth_declaration = '';
                    $birth_decl_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'birth_declaration');
                    foreach ($birth_decl_qry as $birth_decl_qryDb) {
                        // *** Should be only 1 event ***
                        $templ_person["birth_declaration"] = $this->date_place->date_place($birth_decl_qryDb->event_date, $birth_decl_qryDb->event_place);
                        if ($birth_decl_qryDb->event_text) {
                            $templ_person["birth_declaration"] .= ' ' . $birth_decl_qryDb->event_text;
                        }
                        $birth_declaration = $templ_person["birth_declaration"];
                    }

                    // *** Birth declaration source is connected to person ***
                    $birth_decl_source = '';
                    $source_array = show_sources2("person", "birth_decl_source", $personDb->pers_gedcomnumber);
                    if ($source_array) {
                        $templ_person["birth_declaration_source"] = $source_array['text'];
                        $birth_decl_source .= $source_array['text'];
                    }

                    // *** No need to use date, place, text or source with declaration witness. Just show witness ***
                    $birth_decl_witness = witness($personDb->pers_gedcomnumber, 'ASSO', 'birth_declaration');

                    if ($birth_declaration || $birth_decl_source || $birth_decl_witness) {
                        $text .= $birth_declaration;
                        $text .= $birth_decl_source;

                        if (isset($birth_decl_witness['text'])) {
                            //$templ_person["birth_declaration_witness"] = ' (' . __('witness') . ': ' . $birth_decl_witness['text'] . ')';
                            $templ_person["birth_declaration_witness"] = $birth_decl_witness['text'];
                            $temp = "birth_declaration_witness";
                            $text .= $templ_person["birth_declaration_witness"];
                        }

                        // *** No date/ place/ source in use for birth declaration witness. ***
                    }

                    // *** Check for birth declaration items, if needed use a new line ***
                    if ($text) {
                        if (!$temp_previous || $data["family_expanded"] != 'compact') {
                            $templ_person["birth_declaration_start"] = ucfirst(__('birth declaration')) . ' ';
                            $text = '<b>' . ucfirst(__('birth declaration')) . '</b> ' . $text;
                        } else {
                            $templ_person["birth_declaration_start"] = __('birth declaration') . ' ';
                            $text = '<b>' . __('birth declaration') . '</b> ' . $text;
                        }

                        if ($temp_previous) {
                            if ($data["family_expanded"] != 'compact') {
                                $templ_person[$temp_previous] .= '.';
                                $text = '.<br>' . $text;
                            } else {
                                $templ_person[$temp_previous] .= ', ';
                                $text = ', ' . $text;
                            }
                        }

                        $process_text .= $text;
                    }
                }


                /**
                 * BAPTISE/ CHRISTENED
                 */
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_bapt_date || $personDb->pers_bapt_place) {
                    $templ_person["bapt_dateplacetime"] = $this->date_place->date_place($personDb->pers_bapt_date, $personDb->pers_bapt_place);
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
                    $text_array = witness($personDb->pers_gedcomnumber, 'ASSO', 'CHR');

                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        //$templ_person["bapt_witn"] = '(' . __('baptism witness') . ': ' . $text_array['text'];
                        $templ_person["bapt_witn"] = $text_array['text'];
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
                        //$templ_person[$temp] .= ')';
                        //$text .= ')';
                    }
                }

                // *** check for baptise items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous || $data["family_expanded"] != 'compact') {
                        $templ_person["bapt_start"] = ucfirst(__('baptised')) . ' ';
                        $text = '<b>' . ucfirst(__('baptised')) . '</b> ' . $text;
                    } else {
                        $templ_person["bapt_start"] = __('baptised') . ' ';
                        $text = '<b>' . __('baptised') . '</b> ' . $text;
                    }

                    if ($temp_previous) {
                        if ($data["family_expanded"] != 'compact') {
                            $templ_person[$temp_previous] .= '.';
                            $text = '.<br>' . $text;
                        } else {
                            $templ_person[$temp_previous] .= ', ';
                            $text = ', ' . $text;
                        }
                    }

                    $process_text .= $text;
                }

                // *** Show age of living person ***
                if (($personDb->pers_bapt_date || $personDb->pers_birth_date) && !$personDb->pers_death_date && $personDb->pers_alive != 'deceased') {
                    $process_age = new CalculateDates;
                    $age = $process_age->calculate_age($personDb->pers_bapt_date, $personDb->pers_birth_date, '');
                    $templ_person["age_liv"] = $age;
                    if ($templ_person["age_liv"] != '') {
                        $temp = "age_liv";
                    }
                    $process_text .= $dirmark1 . $age;  // *** comma and space already in $age
                }


                /**
                 * DEATH
                 */
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_death_date || $personDb->pers_death_place) {
                    $nightfall = '';
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_death_date_hebnight;
                    }
                    $templ_person["dead_dateplacetime"] = $this->date_place->date_place($personDb->pers_death_date, $personDb->pers_death_place, $nightfall);
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
                    $process_age = new CalculateDates;
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

                // *** Check for death items, if needed use a new line ***
                if ($text) {
                    if (!$temp_previous || $data["family_expanded"] != 'compact') {
                        $templ_person["dead_start"] = ucfirst(__('died')) . ' ';
                        $text = '<b>' . ucfirst(__('died')) . '</b> ' . $text;
                    } else {
                        $templ_person["dead_start"] = __('died') . ' ';
                        $text = '<b>' . __('died') . '</b> ' . $text;
                    }

                    if ($temp_previous) {
                        if ($data["family_expanded"] != 'compact') {
                            $templ_person[$temp_previous] .= '.';
                            $text = '.<br>' . $text;
                        } else {
                            $templ_person[$temp_previous] .= ', ';
                            $text = ', ' . $text;
                        }
                    }

                    $process_text .= $text;
                }

                /**
                 * Death declaration
                 */
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_gedcomnumber) {
                    // *** Sept. 2024: death declaration and death declaration witnesses are now seperate events *** 
                    $death_declaration = '';
                    $death_decl_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'death_declaration');
                    foreach ($death_decl_qry as $death_decl_qryDb) {
                        // *** Should be only 1 event ***
                        $templ_person["death_declaration"] = $this->date_place->date_place($death_decl_qryDb->event_date, $death_decl_qryDb->event_place);
                        if ($death_decl_qryDb->event_text) {
                            $templ_person["death_declaration"] .= ' ' . $death_decl_qryDb->event_text;
                        }
                        $death_declaration = $templ_person["death_declaration"];
                    }

                    // *** death declaration source is connected to person ***
                    $death_decl_source = '';
                    $source_array = show_sources2("person", "death_decl_source", $personDb->pers_gedcomnumber);
                    if ($source_array) {
                        $templ_person["death_declaration_source"] = $source_array['text'];
                        $death_decl_source .= $source_array['text'];
                    }

                    // *** No need to use date, place, text or source with declaration witness. Just show witness ***
                    $death_decl_witness = witness($personDb->pers_gedcomnumber, 'ASSO', 'death_declaration');

                    if ($death_declaration || $death_decl_source || $death_decl_witness) {
                        $text .= $death_declaration;
                        $text .= $death_decl_source;

                        if (isset($death_decl_witness['text'])) {
                            //$text.=$death_decl_witness;
                            //$templ_person["death_declaration_witness"] = ' (' . __('witness') . ': ' . $death_decl_witness['text'] . ')';
                            $templ_person["death_declaration_witness"] = $death_decl_witness['text'];
                            $temp = "death_declaration_witness";
                            $text .= $templ_person["death_declaration_witness"];
                        }

                        // *** No date/ place/ source in use for death declaration witness. ***
                    }

                    // *** Check for death declaration items, if needed use a new line ***
                    if ($text) {
                        if (!$temp_previous || $data["family_expanded"] != 'compact') {
                            $templ_person["death_declaration_start"] = ucfirst(__('death declaration')) . ' ';
                            $text = '<b>' . ucfirst(__('death declaration')) . '</b> ' . $text;
                        } else {
                            $templ_person["death_declaration_start"] = __('death declaration') . ' ';
                            $text = '<b>' . __('death declaration') . '</b> ' . $text;
                        }

                        if ($temp_previous) {
                            if ($data["family_expanded"] != 'compact') {
                                $templ_person[$temp_previous] .= '.';
                                $text = '.<br>' . $text;
                            } else {
                                $templ_person[$temp_previous] .= ', ';
                                $text = ', ' . $text;
                            }
                        }

                        $process_text .= $text;
                    }
                }

                /**
                 * BURIED
                 */
                $text = '';
                $temp_previous = $temp;

                if ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                    $nightfall = '';
                    if ($humo_option['admin_hebnight'] == "y") {
                        $nightfall = $personDb->pers_buried_date_hebnight;
                    }
                    $templ_person["buri_dateplacetime"] = $this->date_place->date_place($personDb->pers_buried_date, $personDb->pers_buried_place, $nightfall);
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
                    $text_array = witness($personDb->pers_gedcomnumber, 'ASSO', 'BURI');
                    if ($text_array) {
                        if ($temp) {
                            $templ_person[$temp] .= ' ';
                        }
                        //$templ_person["buri_witn"] = '(' . __('burial witness') . ': ' . $text_array['text'];
                        $templ_person["buri_witn"] = $text_array['text'];
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
                        //$templ_person[$temp] .= ')';
                        //$text .= ')';
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

                    if (!$temp_previous || $data["family_expanded"] != 'compact') {
                        $templ_person["buri_start"] = ucfirst($method_of_burial) . ' ';
                        $text = '<b>' . ucfirst($method_of_burial) . '</b> ' . $text;
                    } else {
                        $templ_person["buri_start"] = $method_of_burial . ' ';
                        $text = '<b>' . $method_of_burial . '</b> ' . $text;
                    }

                    if ($temp_previous) {
                        if ($data["family_expanded"] != 'compact') {
                            $templ_person[$temp_previous] .= '.';
                            $text = '.<br>' . $text;
                        } else {
                            $templ_person[$temp_previous] .= ', ';
                            $text = ', ' . $text;
                        }
                    }

                    $process_text .= $text;
                }

                // *** HZ-21 ash dispersion (asverstrooiing) ***
                $name_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'ash dispersion');
                foreach ($name_qry as $nameDb) {
                    $process_text .= ', ' . __('ash dispersion') . ' ';
                    if ($nameDb->event_date) {
                        $process_text .= $this->date_place->date_place($nameDb->event_date, '') . ' ';
                    }
                    $process_text .= $nameDb->event_event . ' ';
                    //SOURCE and TEXT.
                    //CHECK PDF EXPORT
                }

                /**
                 * Show professions
                 */
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

                            // *** Period belongs to previous item ***
                            if ($temp) {
                                $process_text .= '. ';
                                $templ_person[$temp] .= ". ";
                            }

                            if ($data["family_expanded"] != 'compact') {
                                $process_text .= '<br>';
                            }
                            $process_text .= '<span class="profession"><b>' . ucfirst($occupation) . ':</b> ';

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
                            $templ_person["prof_date" . $eventnr] = ' (' . $this->date_place->date_place($eventDb->event_date, $eventDb->event_place) . ')';
                            $temp = "prof_date" . $eventnr;
                            $process_text .= $templ_person["prof_date" . $eventnr];
                        }

                        if ($eventDb->event_text) {
                            $work_text = process_text($eventDb->event_text);
                            if ($work_text) {
                                if ($temp) {
                                    $templ_person[$temp] .= " ";
                                }
                                $templ_person["prof_text" . $eventnr] = strip_tags($work_text);
                                $temp = "prof_text" . $eventnr;

                                $process_text .= " " . $work_text;
                            }
                        }

                        // *** Profession source ***
                        $source_array = show_sources2("person", "pers_event_source", $eventDb->event_id);
                        if ($source_array) {
                            $templ_person["prof_source"] = $source_array['text'];
                            $temp = "prof_source";

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

                /**
                 * Show religion
                 */
                if ($personDb->pers_gedcomnumber) {
                    $temp_previous = $temp;

                    $event_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'religion');
                    $nr_occupations = count($event_qry);
                    $eventnr = 0;
                    foreach ($event_qry as $eventDb) {
                        $eventnr++;
                        if ($eventnr == '1') {
                            $religion = $nr_occupations == '1' ? __('religion') : __('religions');

                            if ($temp) {
                                $process_text .= '. ';
                                $templ_person[$temp] .= ". ";
                            }

                            if ($data["family_expanded"] != 'compact') {
                                $process_text .= '<br>';
                            }
                            $process_text .= '<span class="religion"><b>' . ucfirst($religion) . ':</b> ';

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
                            $templ_person["religion_date" . $eventnr] = ' (' . $this->date_place->date_place($eventDb->event_date, $eventDb->event_place) . ')';
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

                /**
                 * Show residences/addresses
                 */
                if ($personDb->pers_gedcomnumber && $user['group_living_place'] == 'j') {
                    $text = show_addresses('person', 'person_address', $personDb->pers_gedcomnumber);

                    if ($process_text and $text) {
                        if ($data["family_expanded"] != 'compact') {
                            $text = '.<br>' . $text;
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
                        //if ($temp) $process_text .= '. '; // disabled for now, otherwise a double period after professions.
                        $process_text .= $source_array['text'];
                    }
                }

                // *** This person was witness at... ***
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
                        $parent2_marr_cls = new MarriageCls;

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
                                $privacy_parent = $person_privacy->get_privacy($parent2Db);
                                $name = $person_name->get_person_name($parent2Db, $privacy_parent);
                                $process_text .= $name["standard_name"];
                            } else {
                                $process_text .= __('N.N.');
                            }
                        } else {
                            if ($marriagenr > 1) {
                                $process_text .= ', ';
                            }

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_link->get_person_link($personDb);
                            $process_text .= '<a href="' . $url . '">';

                            if (isset($parent2_marr_data)) {
                                $process_text .= $dirmark1 . $parent2_marr_data . ' ';
                            }
                            // *** $parent2Db is empty by N.N. person ***
                            if ($parent2Db) {
                                $privacy_parent = $person_privacy->get_privacy($parent2Db);
                                $name = $person_name->get_person_name($parent2Db, $privacy_parent);
                                $process_text .= $name["standard_name"];
                            } else {
                                $process_text .= __('N.N.');
                            }
                            $process_text .= '</a>';
                        }
                        if ($screen_mode == "PDF") {
                            if ($parent2Db) {
                                if ($temp && isset($templ_person[$temp])) {
                                    $templ_person[$temp] .= ", ";
                                }
                                $privacy_parent = $person_privacy->get_privacy($parent2Db);
                                $name = $person_name->get_person_name($parent2Db, $privacy_parent);
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

            if ($privacy) {
                //
            } else {
                // *** Show media/ pictures ***
                $showMedia = new ShowMedia;
                $result = $showMedia->show_media('person', $personDb->pers_gedcomnumber); // *** This function can be found in file: showMedia.php! ***
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
                                //$templ_person["event_dateplace".$eventnr]=' '.$this->date_place->date_place($eventDb->event_date, $eventDb->event_place);
                                $templ_person["event_dateplace" . $eventnr] = ' (' . $this->date_place->date_place($eventDb->event_date, $eventDb->event_place) . ')';
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
            }

            // *** April 2024: added child marriage below child items ***
            //*** Show spouse/ partner by child ***
            $child_marriage = $this->get_child_partner($db_functions, $personDb, $person_kind);
            if ($child_marriage) {
                $templ_person["pers_child_spouse"] = "\n" . $child_marriage;
                $process_text .= "<br>\n" . $child_marriage;
            }

            // *** Return person data ***
            if ($screen_mode != "PDF") {
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
    }

    //*** Show spouse/ partner by child ***
    private function get_child_partner($db_functions, $personDb, $person_kind)
    {
        global $bot_visit, $dirmark1;
        $person_link = new PersonLink;
        $person_privacy = new PersonPrivacy;
        $person_name = new PersonName;

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

                // *** Added in jan. 2021 (also see: marriageCls.php) ***
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
                    $privacy_partner = $person_privacy->get_privacy($partner_id);
                    $name = $person_name->get_person_name($partnerDb, $privacy_partner);

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $person_link->get_person_link($partnerDb);
                } else {
                    $name["standard_name"] = __('N.N.');

                    // *** Link for N.N. partner, not in database ***
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $person_link->get_person_link($personDb);
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
}
