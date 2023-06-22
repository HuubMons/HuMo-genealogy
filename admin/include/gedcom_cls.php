<?php
class gedcom_cls
{

    //public function __destruct(){
    //	echo 'DESTRUCTION';
    //}

    // ************************************************************************************************
    // *** Process persons ***
    // ************************************************************************************************
    function process_person($person_array)
    {
        global $dbh, $tree_id, $tree_prefix, $not_processed, $gen_program;
        // *** Data for connection table ***
        global $connect_nr, $connect, $calculated_connect_id;
        global $processed, $level;
        // *** Add GEDCOM file to database ***
        global $largest_pers_ged, $largest_fam_ged, $largest_source_ged, $largest_text_ged, $largest_repo_ged, $largest_address_ged;
        global $add_tree, $reassign;
        // *** Google maps locations ***
        global $geocode_nr, $geocode_plac, $geocode_lati, $geocode_long, $geocode_type, $humo_option;
        // *** Prefix for lastname ***
        global $prefix, $prefix_length;
        // *** Needed for picture function ***
        global $event, $event_nr, $event2, $event2_nr, $calculated_event_id;
        global $nrsource, $source;
        global $address_array, $nraddress2, $address_order;
        global $pers_place_index;

        $line2 = explode("\n", $person_array);

        //TEST LINE
        //echo '<p>'; for ($z=1; $z<=count($line2)-2; $z++){ echo $z.' '.$line2[$z].'<br>'; }

        // Use array for new variables.
        unset($person);  //Reset array

        $person["pers_patronym"] = "";
        $person["pers_prefix"] = "";
        $person["pers_text"] = "";
        $person["pers_own_code"] = "";

        //$pers_callname='';
        $pers_firstname = '';
        $pers_lastname = '';
        $pers_name_text = '';
        $fams = "";
        $pers_famc = "";
        $pers_place_index = "";
        $pers_birth_date = "";
        $pers_birth_time = "";
        $pers_birth_place = "";
        $pers_birth_text = "";
        $pers_stillborn = '';
        $pers_bapt_date = "";
        $pers_bapt_place = "";
        $pers_bapt_text = "";
        $pers_religion = "";
        $pers_death_date = "";
        $pers_death_time = "";
        $pers_death_place = "";
        $pers_death_text = "";
        $pers_buried_date = "";
        $pers_buried_place = "";
        $pers_buried_text = "";
        $pers_cremation = "";
        $pers_death_cause = "";
        $person["pers_death_age"] = '';
        $person["pers_cal_date"] = '';
        $pers_sexe = "";
        $person["pers_quality"] = '';
        $person["pers_unprocessed_tags"] = '';
        $person["new_date"] = "";
        $person["new_time"] = "";
        $person["changed_date"] = "";
        $person["changed_time"] = "";
        $pers_birth_date_hebnight = '';
        $pers_death_date_hebnight = '';
        $pers_buried_date_hebnight = '';
        $pers_heb_flag = '';
        $pers_alive = '';
        if ($gen_program == 'Haza-Data') {
            $pers_alive = 'deceased';
        }
        //if ($gen_program=='HuMo-gen' OR $gen_program=='HuMo-genealogy'){ $pers_alive='deceased'; }

        $event_status = "";

        // *** For event table ***
        $event_nr = 0;
        $event2_nr = 0;

        // *** Save addresses in a seperate table ***
        $nraddress2 = 0;
        $address_order = 0;

        // *** Save sources in a seperate table ***
        $nrsource = 0;

        // *** Location data for Google maps ***
        $geocode_nr = 0;

        // *** For source connect table ***
        $connect_nr = 0;

        // **********************************************************************************************
        // *** Person ***
        // **********************************************************************************************

        // 0 @I1@ INDI
        // *** Process 1st line ***
        $buffer = $line2[0];
        // Old code: it is alowed to read tags like: I_1;
        //$buffer = str_replace("_", "", $buffer); //Aldfaer numbers
        $pers_gedcomnumber = substr($buffer, 3, -6);
        if ($add_tree == true or $reassign == true) {
            $pers_gedcomnumber = $this->reassign_ged($pers_gedcomnumber, 'I');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            print "$pers_gedcomnumber ";
        }

        // *** FOR TEST ONLY: Show endtime (see also show time at the end of person function) ***
        //$person_time=microtime();

        // *** Save level0 ***
        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level['1a'] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        // *** Process other lines ***
        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline

            // TEST: show memory usage
            //if (!isset($memory)){ $memory=memory_get_usage(); }
            //$calc_memory=(memory_get_usage()-$memory);
            //echo '<br>&nbsp;&nbsp;&nbsp;'.memory_get_usage().' '.$calc_memory.'# '.$buffer;
            //$memory=memory_get_usage();

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 5));  // *** rtrim voor CHR_. Update: rtrim not really neccesary anymore? ***
                if (substr($buffer, 0, 6) == '1 CHR ') $level[1] = 'CHR'; // *** Update sep 2015: this is better to process HZ-21 line: 1 CHR Ned. Herv ***

                $level['1a'] = rtrim($buffer);  // *** Needed to test for 1 RESI @. Update: rtrim not really neccesary anymore? ***
                $event_status = '';
                $event_start = '1';
                $level[2] = '';
                $level[3] = '';
                $level[4] = '';
                $famc = '';
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = "";
                $level[4] = "";

                // *** Possible bug in Haza-21 program: 2 @S167@ SOUR. Rebuild to: 2 SOUR @S167@ ***
                if ($gen_program == 'Haza-21' and substr($buffer, -4) == 'SOUR') {
                    $buffer = substr($buffer, 0, 2) . 'SOUR ' . substr($buffer, 2, -5);
                    $level[2] = substr($buffer, 2, 4);
                }
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = "";
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            // *** Save date ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $person["new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $person["new_time"] = substr($buffer, 7);
                }
            }

            // *** Save changed date ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $person["changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $person["changed_time"] = substr($buffer, 7);
                }
            }

            // *** Parents ***
            if ($level[1] == 'FAMC') {
                // 1 FAMC @F1@
                if ($buffer8 == '1 FAMC @') {
                    if ($pers_famc) {
                        // *** Second famc, used for adoptive parents ***
                        $processed = 1;
                        $famc = substr($buffer, 8, -1); // Needed for Aldfaer adoptive parents
                        if ($gen_program != 'ALDFAER') {
                            $pers_famc2 = substr($buffer, 8, -1);
                            if ($add_tree == true or $reassign == true) {
                                $pers_famc2 = $this->reassign_ged($pers_famc2, 'F');
                            }
                            $event_nr++;
                            $calculated_event_id++;
                            $event['connect_kind'][$event_nr] = 'person';
                            $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                            $event['kind'][$event_nr] = 'adoption';
                            $event['event'][$event_nr] = $pers_famc2;
                            $event['event_extra'][$event_nr] = '';
                            $event['gedcom'][$event_nr] = 'FAMC';
                            $event['date'][$event_nr] = '';
                            $event['text'][$event_nr] = '';
                            $event['place'][$event_nr] = '';
                        }
                    } else {
                        // *** Normal parents ***
                        $processed = 1;
                        $famc = substr($buffer, 8, -1); // Needed for Aldfaer adoptive parents
                        $pers_famc = substr($buffer, 8, -1);
                        if ($add_tree == true or $reassign == true) {
                            $pers_famc = $this->reassign_ged($pers_famc, 'F');
                        }
                    }
                }

                // *** Aldfaer adopted/ steph/ legal/ foster childs ***
                //2 PEDI adopted
                //2 PEDI steph
                //2 PEDI legal
                //2 PEDI foster
                //2 PEDI birth     is in use in Aldfaer 8.
                if ($buffer7 == '2 PEDI ' and $buffer != '2 PEDI birth') {
                    // *** Adoption by person ***
                    $processed = 1;
                    $pers_famc2 = $famc;
                    if ($add_tree == true or $reassign == true) {
                        $pers_famc2 = $this->reassign_ged($famc, 'F');
                    }
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'adoption_by_person';
                    // *** BE AWARE: in gedcom.php step 4 further processing is done, famc is converted into a peron number!!! ***
                    $event['event'][$event_nr] = $pers_famc2;
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = substr($buffer, 7); // *** adopted, steph, legal or foster. ***
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';

                    if ($pers_famc == $famc) {
                        $pers_famc = '';
                    }
                }
            }

            // *** Own families ***
            // 1 FAMS @F5@
            // 1 FAMS @F11@
            if ($buffer8 == '1 FAMS @') {
                $processed = 1;
                $tempnr = substr($buffer, 8, -1);
                if ($add_tree == true or $reassign == true) {
                    $tempnr = $this->reassign_ged($tempnr, 'F');
                }
                $fams = $this->merge_texts($fams, ';', $tempnr);
                // In GEDCOM file (FAMS is default, for own family):
                // Aldfaer: first FAMC then FAMS
                // Haza   : first FAMS then FAMC
            }

            // *** Name ***
            // Haza-Data
            // 1 NAME Firstname/Lastname/
            // 2 NOTE Text by name!
            // 3 CONT 2nd line text name
            // 3 CONT 3rd line text name
            // 1 NAME The/Best/
            // 1 NAME Alias//
            // 1 NAME Alias3//

            // Aldfaer
            // 0 @I2@ INDI
            // 1 RIN 1046615289
            // 1 REFN Hu*ub
            // 1 NAME Firstname/Lastname/
            // 2 NICK Alias3

            // Some programs uses an extra space after firstname:
            // 1 NAME Firstname /Lastname/

            // *** ALL BK names. It's possible to have text, source or date by these names ***
            // 1 NAME Hubertus  /Huub/ Andriessen Mons
            // 2 SOUR @S3@
            // 2 _AKAN also known as
            // 2 NICK bijnaam
            // 2 _SHON verkort voor rapporten
            // 2 _ADPN adoptienaam
            // 2 _HEBN hebreeuwse naam
            // 2 _CENN censusnaam
            //  3 DATE 21 FEB 2007
            //  3 SOUR @S3@
            //  3 NOTE text by Name censusnaam
            //  4 CONT 2nd line
            //  4 CONT 3rd line
            // 2 _MARN huwelijksnaam
            //  3 DATE 21 FEB 2007
            // 2 _GERN roepnaam
            // 2 _FARN boerderijnaam
            // 2 _BIRN geboortenaam
            // 2 _INDN indiaanse naam
            // 2 _FKAN officiele naam
            // 2 _CURN huidige naam
            // 2 _SLDN soldatennaam
            // 2 _FRKA voorheen bekend als
            // 2 _RELN kloosternaam
            // 2 _OTHN andere naam
            // 1 TITL Sr.
            // *************************************

            // *** Pro-gen titles by name ***
            // 1 _TITL2 = title between first and last name.
            if ($buffer8 == '1 _TITL2') {
                $level[1] = 'NAME';
                //$level[2]='NPFX';
                $level[2] = 'SPFX';
                //$buffer='2 NPFX'.substr($buffer, 8);
                $buffer = '2 SPFX' . substr($buffer, 8);
                $buffer6 = substr($buffer, 0, 6);
            }
            // 1 _TITL3 = title behind lastname.
            if ($buffer8 == '1 _TITL3') {
                $level[1] = 'NAME';
                $level[2] = 'NSFX';
                $buffer = '2 NSFX' . substr($buffer, 8);
                $buffer6 = substr($buffer, 0, 6);
            }

            if ($level[1] == 'NAME') {
                if ($buffer6 == '1 NAME') {
                    $processed = 1;
                    $name = str_replace("_", " ", $buffer);
                    $name = str_replace("~", " ", $name);

                    // *** Second line "1 NAME" is a callname ***
                    if ($pers_firstname) {
                        //$pers_callname_org=$pers_callname; // *** If "2 TYPE aka" is used, $pers_callname can be restored ***
                        //$pers_aka=substr($name,7); // *** If "2 TYPE aka" is used

                        //if ($pers_callname){
                        //	$pers_callname=$pers_callname.", ".substr($name,7);
                        //} else {
                        //	$pers_callname=substr($name,7);
                        //}
                        //$pers_callname=str_replace("/", " ", $pers_callname);
                        //$pers_callname=str_replace("  ", " ", $pers_callname);
                        //$pers_callname=rtrim($pers_callname);

                        $processed = 1;
                        $pers_aka = substr($name, 7);
                        // *** Remove / if nickname starts with / ***
                        if (substr($pers_aka, 0, 1) == '/') $pers_aka = substr($pers_aka, 1);
                        $pers_aka = str_replace("/", " ", $pers_aka);
                        $pers_aka = str_replace("  ", " ", $pers_aka);
                        $pers_aka = rtrim($pers_aka);

                        $processed = 1;
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'name';
                        $event['event'][$event_nr] = $pers_aka;
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'NICK';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    } else {
                        $position = strpos($name, "/");
                        if ($position !== false) { // there are slashes
                            $pers_firstname = rtrim(substr($name, 7, $position - 7));

                            $pers_lastname = substr($name, $position + 1);
                            $pers_lastname = rtrim($pers_lastname, "/"); // *** Check for last / character, if present remove it ***

                            // *** Three or more (never seen that) name parts: just use this as a last name and remove the / character in these parts ***
                            // BK: 1 NAME Hubertus  /Huub/ Patronym Mons
                            // GEDCOM file from LDS website (3rd item is a TITLE): 1 NAME Richard /de Plaiz/ Lord Plaiz
                            $pers_lastname = str_replace("/", "", $pers_lastname); // *** Remove / character***
                        } else {
                            // *** No slashes in name (probably a bug or just a bad GEDCOM file) ***
                            // 1 NAME Hubertus [Huub] Mons
                            $pers_firstname = rtrim(substr($name, 7));
                        }

                        // *** REMARK: processing of prefixes is done later in script (around line 1800) ***
                    }
                }

                // *** ADDED GIVN and SURN in april 2023. GensDataPro: use GIVN and SURN so / characters in name will be processed ***
                // 1 NAME Willem I/III/van Holland/
                // 2 GIVN Willem I/III
                // 2 SURN van Holland
                if ($buffer6 == '2 GIVN') {
                    $processed = 1;
                    $pers_firstname = substr($buffer, 7);
                }
                if ($buffer6 == '2 SURN') {
                    $processed = 1;
                    $pers_lastname = substr($buffer, 7);

                    // *** REMARK: processing of prefixes is done later in script (around line 1800) ***
                }

                // *** 2 TYPE aka: also know as. ***
                // MyHeritage Family Tree Builder???????????
                // 1 NAME Sijpkje Sipkes /Visser/
                // 1 NAME Sijpkje /Visser/
                // 2 TYPE aka
                // 1 NAME Sijke /Visser/
                // 2 TYPE aka
                // 1 NAME Sipkje /Visser/
                // 2 TYPE aka
                // *** Change "NICK" into "_AKA" ***
                if (strtoupper($buffer) == '2 TYPE AKA') {
                    $processed = 1;
                    //$pers_aka=str_replace("/", " ", $pers_aka);
                    //$pers_aka=str_replace("  ", " ", $pers_aka);
                    //$pers_aka=rtrim($pers_aka);

                    //$processed=1; $event_nr++; $calculated_event_id++;
                    //$event['connect_kind'][$event_nr]='person';
                    //$event['connect_id'][$event_nr]=$pers_gedcomnumber;
                    //$event['kind'][$event_nr]='name';
                    //$event['event'][$event_nr]=$pers_aka;
                    //$event['event_extra'][$event_nr]='';
                    $event['gedcom'][$event_nr] = '_AKA';
                    //$event['date'][$event_nr]='';
                    //$event['text'][$event_nr]='';
                    //$event['place'][$event_nr]='';

                    // *** Empty original pers_call_name ***
                    //$pers_callname=$pers_callname_org;
                }

                // *** GEDCOM 5.5 lastname prefix: 2 SPFX Le ***
                if ($buffer6 == '2 SPFX') {
                    $processed = 1;
                    $person["pers_prefix"] = substr($buffer, 7) . '_';
                }

                // *** Title in GEDCOM 5.5: 2 NPFX Prof. ***
                if ($buffer6 == '2 NPFX') {
                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'NPFX';
                    $event['event'][$event_nr] = substr($buffer, 7);
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'NPFX';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }

                // *** GEDCOM 5.5 name addition: 2 NSFX Jr. ***
                if ($buffer6 == '2 NSFX') {
                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'NSFX';
                    $event['event'][$event_nr] = substr($buffer, 7);
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = $level[1];
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }


                if ($level[2] == 'NOTE') {
                    $pers_name_text = $this->process_texts($pers_name_text, $buffer, '2');
                }

                if ($gen_program == "SukuJutut" and $level[3] == 'NOTE') {
                    $pers_name_text = $this->process_texts($pers_name_text, $buffer, '3');
                }

                // *** Source by pers_name ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_name_source', $pers_gedcomnumber, $buffer, '2');
                }

                $process_event = false;
                if ($buffer7 == '2 _AKAN') {
                    $process_event = true;
                }
                // *** MyHeritage uses _AKA
                if ($buffer6 == '2 _AKA') {
                    $process_event = true;
                    //  *** Replace optional / characters: 2 _AKA Sijpkje /Visser/ ***
                    $buffer = str_replace("/", " ", $buffer);
                    $buffer = str_replace("  ", " ", $buffer);
                    $buffer = rtrim($buffer);
                }

                // *** HuMo-genealogy (roepnaam), BK (als bijnaam) and PG (als roepnaam): 2 NICK name ***
                // *** Users can change nickname for BK in language file! ***
                if ($buffer6 == '2 NICK') {
                    $process_event = true;
                }

                // *** PG: 2 _ALIA ***
                if ($buffer7 == '2 _ALIA') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _SHON') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _ADPN') {
                    $process_event = true;
                }
                //if ($buffer7=='1 _HEBN'){ $process_event=true; }
                if ($buffer7 == '2 _HEBN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _CENN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _MARN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _GERN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _FARN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _BIRN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _INDN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _FKAN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _CURN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _SLDN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _FRKA') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _RELN') {
                    $process_event = true;
                }
                if ($buffer7 == '2 _OTHN') {
                    $process_event = true;
                }

                // *** For German "2 _RUFN" entries. BK uses "2 _RUFNAME" ***
                if ($buffer7 == '2 _RUFN') {
                    $process_event = true;
                }  // 2 _RUFNAME needs the isset($buffer[10] check a few lines below

                if ($process_event) {
                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'name';

                    // *** $buffer[7] = check 8th character.***
                    // *** There maybe is a problem for texts like: "1 tekst" or "A text". ***
                    // *** 2 _AKA Also known as ***
                    if (isset($buffer[6]) and $buffer[6] == ' ') {
                        $event['event'][$event_nr] = substr($buffer, 7);
                    }
                    // *** 2 _ALIA Alias ***
                    elseif (isset($buffer[7]) and $buffer[7] == ' ') {
                        $event['event'][$event_nr] = substr($buffer, 8);
                    }
                    // *** X _MARNM ??  MyHeritage ***
                    elseif (isset($buffer[8]) and $buffer[8] == ' ') {
                        $event['event'][$event_nr] = substr($buffer, 9);
                    }
                    //elseif (isset($buffer[10]) AND $buffer[10]==' ') {$event['event'][$event_nr]=substr($buffer,11);}

                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = trim(substr($buffer, 2, 5));
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }

                // Proces name-source and name-date (BK)
                //  3 DATE 21 FEB 2007
                //  3 SOUR @S3@
                //  3 NOTE text by Naam censusnaam
                //  4 CONT 2nd line
                //  4 CONT 3rd line
                if ($buffer6 == '3 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }

                // *** Source by person event (name) ***
                if ($level[3] == 'SOUR') {
                    $this->process_sources('person', 'pers_event_source', $calculated_event_id, $buffer, '3');
                }

                if ($level[3] == 'NOTE') {
                    // *** GensDataPro uses 3 NOTE, there's no event (because 2 GIVN and 2 SURN are skipped)! ***
                    // 0 @I428@ INDI
                    // 1 NAME Wilhelmina/Brink/
                    // 2 GIVN Wilhelmina
                    // 2 SURN Brink
                    // 3 NOTE Naam kan ook zijn: Brink
                    if (isset($event['text'][$event_nr]))
                        $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, '3');
                    else
                        $pers_name_text = $this->process_texts($pers_name_text, $buffer, '3');
                }
            }

            // *** Quality ***
            // BELONGS TO A 1 xxxx ITEM????
            // Certain/ uncertain person (onzeker persoon) HZ
            if ($gen_program == 'Haza-Data' and $buffer8 == '2 QUAY 0') {
                $processed = 1;
                $pers_firstname = '(?) ' . $pers_firstname;
            }
            //if ($buffer6=='2 QUAY'){ $processed=1; $person["pers_quality"]=$this->process_quality($buffer); }

            // *** Pro-gen: 1 _PATR Jans ***
            if ($buffer7 == '1 _PATR') {
                $processed = 1;
                $person["pers_patronym"] = substr($buffer, 8);
            }

            // *** Own code ***
            if ($buffer6 == '1 REFN') {
                $processed = 1;
                $person["pers_own_code"] = substr($buffer, 7);
            }

            // *** Finnish genealogy program SukuJutut (and some other genealogical programs) ***
            // 1 ALIA Frederik Hektor /McLean/
            if ($buffer6 == '1 ALIA') {
                //$processed=1;
                //$buffer = str_replace("/", "", $buffer);  // *** Remove / from alias: 1 ALIA Frederik Hektor /McLean/ ***
                //if ($pers_callname){
                //	$pers_callname=$pers_callname.", ".substr($buffer, 7);
                //}
                //else {
                //	$pers_callname=substr($buffer,7);
                //}
                //$pers_callname=rtrim($pers_callname);

                $processed = 1;
                $pers_aka = substr($buffer, 7);
                $pers_aka = str_replace("/", "", $pers_aka);  // *** Remove / from alias: 1 ALIA Frederik Hektor /McLean/ ***
                $pers_aka = rtrim($pers_aka);

                $processed = 1;
                $event_nr++;
                $calculated_event_id++;
                $event['connect_kind'][$event_nr] = 'person';
                $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                $event['kind'][$event_nr] = 'name';
                $event['event'][$event_nr] = $pers_aka;
                $event['event_extra'][$event_nr] = '';
                $event['gedcom'][$event_nr] = 'NICK';
                $event['date'][$event_nr] = '';
                $event['text'][$event_nr] = '';
                $event['place'][$event_nr] = '';
            }

            // *** December 2021: now processed as event **
            // *** HuMo-genealogy (roepnaam), BK (als bijnaam) and PG (als roepnaam): 2 NICK name ***
            /*
        if ($buffer6=='2 NICK'){
            $processed=1;
            if ($pers_callname){
                $pers_callname=$pers_callname.", ".substr($buffer,7);
            }
            else {
                $pers_callname=substr($buffer,7);
            }
            $pers_callname=rtrim($pers_callname);
        }
        */

            // *** Text(s) by person ***
            if ($level[1] == 'NOTE') {
                $person["pers_text"] = $this->process_texts($person["pers_text"], $buffer, '1');

                // BK: source by text
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_text_source', $pers_gedcomnumber, $buffer, '2');
                }
            }

            // *** For BK (Interesse) ***
            // 1 ANCI
            // 2 NOTE De moeder trouwde met David Hoofien-de koetsier van haar
            // 3 CONT vader- en werd daarom onterft.Deze was van de fam.
            if ($level[1] == 'ANCI') {
                //if (substr($buffer, 0, 6)=='1 ANCI'){ $processed=1; $person["pers_text"].="<br>".substr($buffer,7); }
                if ($buffer6 == '1 ANCI') {
                    $processed = 1;
                    $person["pers_text"] .= substr($buffer, 7);
                }
                $person["pers_text"] = $this->process_texts($person["pers_text"], $buffer, '2');
            }

            // ******************************************************************************************
            // *** Address(es) ***
            // ******************************************************************************************
            if ($buffer6 == '1 ADDR' or $level[1] == 'RESI') {
                $this->process_addresses('person', 'person_address', $pers_gedcomnumber, $buffer);
            }

            // *** Birth, baptism, death and buried ***
            // PRO-GEN sources
            //1 BIRT
            //2 DATE 23 JUL 1921
            //2 PLAC Borgloon
            //2 SOUR bidprentje
            //1 DEAT
            //2 DATE 25 SEP 1980
            //2 PLAC Hendrieken
            //2 SOUR Jozef Cuyvers, Bevolking Eksel, deel 1, p.3
            //3 REFN 5	? aktenummer wordt niet nog niet gebruikt
            //3 TEXT Vrolingen, ongehuwd.
            //4 CONC Annorim, caelebs, filia Lamberti Gilisen et Anna Maria Raets".

            // Aldfaer sources
            //1 BIRT
            //2 DATE 01 JAN 1970
            //2 PLAC gebplaats pipa
            //2 _ALDFAER_TIME 11:30:00
            //2 SOUR bron aangifte
            //2 NOTE @NB4@
            //1 CHR
            //2 DATE 19 JAN 1970
            //2 PLAC plaats doop pipa
            //2 SOUR bron doop

            // Newer versions of Aldfaer:
            //2 SOUR @S7177@

            // ******************************************************************************************
            // *** Birth ***
            if ($level[1] == 'BIRT') {
                if ($buffer6 == '1 BIRT') {
                    $processed = 1;
                }

                // *** Date ***
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    //FTM and dorotree programs can have multiple birth dates, only first one is saved:
                    if (!$pers_birth_date) {
                        $pers_birth_date = trim(substr($buffer, 7));
                    }
                }
                if ($buffer7 == '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $processed = 1;
                    $pers_birth_date_hebnight = substr($buffer, 8);
                }
                // *** Aldfaer time ***
                // 2 _ALDFAER_TIME 08:00:00
                if (substr($buffer, 0, 15) == '2 _ALDFAER_TIME') {
                    $processed = 1;
                    $pers_birth_time = substr($buffer, 16);
                }
                // *** Pro-gen time NOT TESTED ***
                // 2 TIME 22.40
                if ($buffer6 == '2 TIME') {
                    $processed = 1;
                    $pers_birth_time = substr($buffer, 7);
                }

                // *** Place ***
                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $pers_birth_place = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($pers_birth_place, $buffer);
                }

                // *** Texts ***
                if ($level[2] == 'NOTE') {
                    $pers_birth_text = $this->process_texts($pers_birth_text, $buffer, '2');
                }

                // *** Text for SukuJutut***
                if ($gen_program == "SukuJutut" and $level[3] == 'NOTE') {
                    $pers_birth_text = $this->process_texts($pers_birth_text, $buffer, '3');
                }

                // *** Process sources Pro-gen and (older versions of) Aldfaer etc. ***
                // *** Source by person birth ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_birth_source', $pers_gedcomnumber, $buffer, '2');
                }

                // *** Birth witness Pro-Gen ***
                if ($level[2] == '_WIT') {
                    if (substr($buffer, 2, 5) == '_WITN') {
                        $processed = 1;
                        $buffer = str_replace("/", " ", $buffer);
                        $buffer = str_replace("  ", " ", $buffer);
                        $buffer = trim($buffer);
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'birth_declaration';
                        $event['event'][$event_nr] = substr($buffer, 7);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'WITN';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if ($buffer6 == '3 CONT') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                    if ($buffer6 == '3 CONC') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                }

                // *** Stillborn child ***
                // 1 BIRT
                // 2 TYPE stillborn
                if (substr($buffer, 0, 16) == '2 TYPE stillborn') {
                    $processed = 1;
                    $pers_stillborn = 'y';
                }

                if ($level[2] == 'OBJE') $this->process_picture('person', $pers_gedcomnumber, 'picture_birth', $buffer);
            }

            // *******************************************************************************************
            // *** Baptise ***
            // *******************************************************************************************

            // *** FTW ***
            //1 EVEN
            //2 TYPE Doopgetuigen
            //2 PLAC Petrus Verdeurmen, Judoca De Grauw
            //if gen_program='FTW' then begin
            ////if buf='2 TYPE Doopgetuigen' then buf:='1 CHR';
            ////if (level1='CHR') and (copy(buf,1,6)='2 PLAC') then buf:='2 WITN'+copy(buf,7,length(buf));
            //  if buf='2 TYPE Doopgetuigen' then buf:='1 XXX';
            //  if (level1='XXX') and (copy(buf,1,6)='2 PLAC') then doopgetuigen:=copy(buf,8,length(buf));
            //end;

            // Geneanet/ Geneweb
            // 1 BAPM
            // 2 DATE 23 APR 1658
            // 2 PLAC Venlo
            if ($gen_program == 'GeneWeb' and $buffer == '1 BAPM') {
                $level[1] = 'CHR';
                $buffer = '1 CHR';
                $buffer5 = '1 CHR';
                $buffer6 = '1 CHR';
            }

            //$buffer = str_replace("1 CHR ", "1 CHR", $buffer);  // For Aldfaer etc.
            if ($level[1] == 'CHR') {
                if ($buffer5 == '1 CHR') {
                    $processed = 1;
                }

                // HZ-21
                // 1 CHR Ned. Herv.
                if ($buffer5 == '1 CHR' and substr($buffer, 7)) {
                    $processed = 1;
                    $pers_religion = substr($buffer, 7);
                }

                // *** Date ***
                //if ($buffer6=='2 DATE'){ $processed=1; $pers_bapt_date=substr($buffer, 7); }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    //dorotree programm can have multiple bapt dates, only first one is saved:
                    if (!$pers_bapt_date) {
                        $pers_bapt_date = trim(substr($buffer, 7));
                    }
                }

                // *** Place ***
                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $pers_bapt_place = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($pers_bapt_place, $buffer);
                }

                // *** Texts ***
                if ($level[2] == 'NOTE') {
                    $pers_bapt_text = $this->process_texts($pers_bapt_text, $buffer, '2');
                }

                if ($gen_program == "SukuJutut" and $level[3] == 'NOTE') {
                    $pers_bapt_text = $this->process_texts($pers_bapt_text, $buffer, '3');
                }

                // *** Process sources for Pro-gen and Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_bapt_source', $pers_gedcomnumber, $buffer, '2');
                }

                //Haza-data uses "i.p.v." (instead of).
                //2 WITN Doopgetuige1//
                //3 TYPE locum
                //2 WITN Doopgetuige2//
                if ($level[2] == 'WITN') {
                    if (substr($buffer, 2, 4) == 'WITN') {
                        $processed = 1;
                        $buffer = str_replace("/", " ", $buffer);
                        $buffer = str_replace("  ", " ", $buffer);
                        $buffer = trim($buffer);
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'baptism_witness';
                        $event['event'][$event_nr] = substr($buffer, 7);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'WITN';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if (substr($buffer, 0, 12) == '3 TYPE locum') {
                        $processed = 1;
                        $event['event'][$event_nr] .= " i.p.v. ";
                    }
                }

                // *** Baptise witnesses ***
                // Pro-gen: 2 _WITN Anna van Wely
                if ($level[2] == '_WIT') {
                    if (substr($buffer, 2, 5) == '_WITN') {
                        $processed = 1;
                        $buffer = str_replace("/", " ", $buffer);
                        $buffer = str_replace("  ", " ", $buffer);
                        $buffer = trim($buffer);
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'baptism_witness';
                        $event['event'][$event_nr] = substr($buffer, 7);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'WITN';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if ($buffer6 == '3 CONT') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                    if ($buffer6 == '3 CONC') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                }

                // Doopheffer/ Godfather.
                //  1 BAPM
                //  2 ASSO @I2334@
                //  3 TYPE INDI
                //  3 RELA GODP
                if ($level[2] == 'ASSO') {
                    if (substr($buffer, 0, 6) == '2 ASSO') {
                        $processed = 1;
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'godfather';
                        $event['event'][$event_nr] = substr($buffer, 7);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'GODP';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if (substr($buffer, 0, 11) == '3 TYPE INDI') {
                        $processed = 1; //$event['event'][$event_nr].="godfather";
                    }
                    if (substr($buffer, 0, 11) == '3 RELA GODP') {
                        $processed = 1; //$event['event'][$event_nr].="godfather";
                    }
                }

                // *** Religion (1 RELI = event) ***
                if ($buffer6 == '2 RELI') {
                    $processed = 1;
                    $pers_religion = substr($buffer, 7);
                }

                if ($level[2] == 'OBJE') $this->process_picture('person', $pers_gedcomnumber, 'picture_bapt', $buffer);
            }

            // ******************************************************************************************
            // *** Deceased ***
            if ($level[1] == 'DEAT') {
                if ($buffer6 == '1 DEAT') {
                    $processed = 1;
                }

                // Aldfaer uses DEAT without further data!
                // if ($gen_program=='ALDFAER') { $pers_alive='deceased'; }
                // Legacy death without further date. "1 DEAT Y"
                $pers_alive = 'deceased';

                // *** Date ***
                //if ($buffer6=='2 DATE'){ $processed=1; $pers_death_date=substr($buffer, 7); }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    //dorotree programm can have multiple death dates, only first one is saved:
                    if (!$pers_death_date) {
                        $pers_death_date = trim(substr($buffer, 7));
                    }
                }
                if ($buffer7 == '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $processed = 1;
                    $pers_death_date_hebnight = substr($buffer, 8);
                }
                // *** Aldfaer time ***
                // 2 _ALDFAER_TIME 08:00:00
                if (substr($buffer, 0, 15) == '2 _ALDFAER_TIME') {
                    $processed = 1;
                    $pers_death_time = substr($buffer, 16);
                }
                // *** Pro-gen time ***
                // 2 TIME 22.40
                if ($buffer6 == '2 TIME') {
                    $processed = 1;
                    $pers_death_time = substr($buffer, 7);
                }

                // *** Place ***
                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $pers_death_place = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($pers_death_place, $buffer);
                }

                // *** Texts ***
                if ($level[2] == 'NOTE') {
                    $pers_death_text = $this->process_texts($pers_death_text, $buffer, '2');
                }

                if ($gen_program == "Sukujutut" and $level == 'NOTE') {
                    // *** Texts ***
                    $pers_death_text = $this->process_texts($pers_death_text, $buffer, '3');
                }

                // *** Process source for Pro-gen, Aldfaer, etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_death_source', $pers_gedcomnumber, $buffer, '2');
                }

                // *** Death witness Pro-Gen ***
                if ($level[2] == '_WIT') {
                    if (substr($buffer, 2, 5) == '_WITN') {
                        $processed = 1;
                        $buffer = str_replace("/", " ", $buffer);
                        $buffer = str_replace("  ", " ", $buffer);
                        $buffer = trim($buffer);
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'person';
                        $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                        $event['kind'][$event_nr] = 'death_declaration';
                        $event['event'][$event_nr] = substr($buffer, 7);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'WITN';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if ($buffer6 == '3 CONT') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                    if ($buffer6 == '3 CONC') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in other text!
                    }
                }

                // *** Pers_death_cause ***
                if ($buffer6 == '2 CAUS') {
                    $processed = 1;
                    $pers_death_cause = rtrim(substr($buffer, 7));
                }

                // *** Pers_death_age ***
                if ($buffer5 == '2 AGE') {
                    $processed = 1;
                    $person["pers_death_age"] = substr($buffer, 6);
                }

                // *** Pers_death_cause Haza-data ***
                if ($buffer6 == '2 TYPE') {
                    $processed = 1;
                    $pers_death_cause = rtrim(substr($buffer, 7));
                }
                if ($pers_death_cause == 'died single') {
                    $pers_death_cause = 'died unmarried';
                }

                if ($level[2] == 'OBJE') $this->process_picture('person', $pers_gedcomnumber, 'picture_death', $buffer);
            }

            // ****************************************************************************************
            // *** Burial ***

            //Pro-gen:
            //1 CREM
            //2 DATE 02 MAY 2003
            //2 PLAC Schagen

            if ($buffer6 == '1 CREM') {
                $level[1] = 'BURI';
                $buffer = '2 TYPE cremation';
            }
            if ($level[1] == 'BURI') {
                if ($buffer6 == '1 BURI') {
                    $processed = 1;
                }

                // *** Set pers_alive setting ***
                $pers_alive = 'deceased';

                // *** Date ***
                //if ($buffer6=='2 DATE'){ $processed=1; $pers_buried_date=substr($buffer, 7); }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    //dorotree programm can have multiple burial dates, only first one is saved:
                    if (!$pers_buried_date) {
                        $pers_buried_date = trim(substr($buffer, 7));
                    }
                }
                if ($buffer7 == '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $processed = 1;
                    $pers_buried_date_hebnight = substr($buffer, 8);
                }
                // *** Place ***
                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $pers_buried_place = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($pers_buried_place, $buffer);
                }

                // *** Texts ***
                if ($level[2] == 'NOTE') {
                    $pers_buried_text = $this->process_texts($pers_buried_text, $buffer, '2');
                }

                if ($gen_program == "Sukujutut" and $level[3] == 'NOTE') {
                    // *** Texts ***
                    $pers_buried_text = $this->process_texts($pers_buried_text, $buffer, '3');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_buried_source', $pers_gedcomnumber, $buffer, '2');
                }

                // *** Method of burial ***
                if (substr($buffer, 0, 16) == '2 TYPE cremation') {
                    $processed = 1;
                    $pers_cremation = '1';
                }
                if (substr($buffer, 0, 16) == '2 TYPE resomated') {
                    $processed = 1;
                    $pers_cremation = 'R';
                }
                // 2 TYPE sailor's grave in GEDOM file
                if (substr($buffer, 0, 13) == '2 TYPE sailor') {
                    $processed = 1;
                    $pers_cremation = 'S';
                }
                // 2 TYPE donated to science in GEDCOM file
                if (substr($buffer, 0, 14) == '2 TYPE donated') {
                    $processed = 1;
                    $pers_cremation = 'D';
                }

                if ($level[2] == 'OBJE') $this->process_picture('person', $pers_gedcomnumber, 'picture_buried', $buffer);
            }

            // *******************************************************************************************
            // *** Aldfaer witnesses ***
            // Aldfaer birth declaration (by person who did the declaration)
            //  1 _SORTCHILD
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA birth registration

            // Aldfaer baptise registration (by person who did registration)
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA baptize

            //  Aldfaer
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA death registration

            //  Aldfaer
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA burial

            // Aldfaer witnesses marriage:
            //  1 ASSO @F2612@
            //  2 TYPE FAM
            //  2 RELA civil
            //  3 NOTE INDI I1281

            // Aldfaer witnesses religous marriage:
            //  1 ASSO @F2612@
            //  2 TYPE FAM
            //  2 RELA religious
            //  3 NOTE INDI I1281

            // Geneanet/ geneweb
            //  1 ASSO @F101@
            //  2 TYPE FAM
            //  2 RELA witness

            if ($level[1] == 'ASSO') {
                if ($buffer6 == '1 ASSO') {
                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = substr($buffer, 8, -1);
                    $event['kind'][$event_nr] = 'witness';
                    $event['event'][$event_nr] = '@' . $pers_gedcomnumber . '@';
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'ASSO';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }
                if ($buffer == '2 TYPE INDI') {
                    $processed = 1;
                    if ($add_tree == true or $reassign == true) {
                        $event['connect_id'][$event_nr] = $this->reassign_ged($event['connect_id'][$event_nr], 'I');
                    }
                }
                if ($buffer == '2 TYPE FAM') {
                    $processed = 1;
                    $event['connect_kind'][$event_nr] = 'family';
                    if ($add_tree == true or $reassign == true) {
                        $event['connect_id'][$event_nr] = $this->reassign_ged($event['connect_id'][$event_nr], 'F');
                    }
                }
                if ($buffer == '2 RELA birth registration') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'birth_declaration';
                }
                if ($buffer == '2 RELA baptize') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'baptism_witness';
                }
                if ($buffer == '2 RELA death registration') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'death_declaration';
                }
                if ($buffer == '2 RELA burial') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'burial_witness';
                }
                if ($buffer == '2 RELA civil') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'marriage_witness';
                }
                if ($buffer == '2 RELA witness') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'marriage_witness';
                }
                if ($buffer == '2 RELA religious') {
                    $processed = 1;
                    $event['kind'][$event_nr] = 'marriage_witness_rel';
                }
            }

            // ******************************************************************************************
            // *** Occupation ***
            if ($level[1] == 'OCCU') {
                if ($buffer6 == '1 OCCU') {
                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'profession';
                    $event['event'][$event_nr] = substr($buffer, 7);
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'OCCU';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }

                // *** Occupation, Haza-21 uses empty OCCU events... Isn't strange? ***
                // 1 OCCU lerares
                if ($level[1] == 'OCCU') {
                    if ($buffer6 == '1 OCCU' and substr($buffer, 7)) {
                        $processed = 1;
                        $event['event'][$event_nr] = substr($buffer, 7);
                    }
                    // *** Long occupation ***
                    if ($buffer6 == '2 CONT') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                    }
                    if ($buffer6 == '2 CONC') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                    }
                }

                // *** Text by occupation ***
                if ($level[2] == 'NOTE') {
                    $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, '2'); // BK
                }

                // *** Occupation in iFamily program ***
                // 1 OCCU
                // 2 TYPE baas van de herberg
                if ($buffer6 == '2 TYPE') {
                    $processed = 1;
                    $event['event'][$event_nr] = $event['event'][$event_nr] . substr($buffer, 7);
                }

                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                } // BK
                if ($buffer6 == '2 PLAC') {
                    $processed = 1;
                    $event['place'][$event_nr] = substr($buffer, 7);
                }

                // *** Source by person occupation ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_event_source', $calculated_event_id, $buffer, '2');
                }
            }

            // *** BK, FTM, HuMo-genealogy & Aldfaer: 1 RELI RK ***
            // *** Nov. 2022 religion now saved as event ***
            if ($level[1] == 'RELI') {
                if ($buffer6 == '1 RELI') {
                    //$processed=1; $pers_religion=substr($buffer, 7);

                    $processed = 1;
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'religion';
                    $event['event'][$event_nr] = substr($buffer, 7);
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'RELI';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }

                if ($buffer6 == '2 CONT') {
                    $processed = 1;
                    $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                    $buffer = ""; // to prevent use of this text in other text!
                }
                if ($buffer6 == '2 CONC') {
                    $processed = 1;
                    $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                    $buffer = ""; // to prevent use of this text in other text!
                }

                // *** Text by occupation ***
                if ($level[2] == 'NOTE') {
                    $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, '2');
                }

                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 PLAC') {
                    $processed = 1;
                    $event['place'][$event_nr] = substr($buffer, 7);
                }

                // *** Source by person occupation ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_event_source', $calculated_event_id, $buffer, '2');
                }
            }

            // *** Pictures by person ********************************

            // *** Haza-21 pictures ***
            // 1 OBJE H:\haza21v3\Scannen0001.jpg
            // of:
            // 1 OBJE H:\haza21v3\plaatjes\IM000247.jpg
            // 2 QUAY 3
            // 2 NOTE Ome Rein op verjaardagvisite bij zijn broer Dirk
            // 3 CONC en nog meer tekst...

            // *** Aldfaer pictures tested by: Jeroen Beemster ***
            // *** Picture Aldfaer AND GEDCOM 5.5 ***
            // 1 OBJE
            // 2 FORM jpg
            // 2 FILE C:\Documents and Settings\frans schwartz\Mijn documenten\lammert en tetje.jpg
            // 2 TITL lammert en tetje

            // 2 FILE huub&lin.jpg
            // 2 TITL Picture title

            // *** External object/ image ***
            // 1 OBJE @O3@
            if ($level[1] == 'OBJE') $this->process_picture('person', $pers_gedcomnumber, 'picture', $buffer);

            // *** Haza-data pictures ***
            //1 PHOTO @#Aplaatjes\beert&id.jpg jpg@
            if ($level[1] == 'PHOTO') {
                if ($buffer7 == '1 PHOTO') {
                    $processed = 1;
                    $photo = substr($buffer, 11, -6);
                    $photo = $this->humo_basename($photo);

                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'picture';
                    $event['event'][$event_nr] = $photo;
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'PHOTO';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }
                if ($buffer6 == '2 DSCR' or $buffer6 == '2 NAME') {
                    $processed = 1;
                    $event['text'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }
            }

            // *** Sex: F or M ***
            if (substr($level[1], 0, 3) == 'SEX') { // *** 1 SEX F/ 1 SEX M ***
                if ($buffer5 == '1 SEX') {
                    $processed = 1;
                    $pers_sexe = substr($buffer, 6);
                }
                // *** Source by person sex ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_sexe_source', $pers_gedcomnumber, $buffer, '2');
                }
            }

            // *** Colour mark by a person ***
            // 1 _COLOR 1
            if ($buffer8 == '1 _COLOR') {
                $processed = 1;
                $event_nr++;
                $calculated_event_id++;
                $event['connect_kind'][$event_nr] = 'person';
                $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                $event['kind'][$event_nr] = 'person_colour_mark';
                $event['event'][$event_nr] = substr($buffer, 9);
                $event['event_extra'][$event_nr] = '';
                $event['gedcom'][$event_nr] = '_COLOR';
                $event['date'][$event_nr] = '';
                $event['text'][$event_nr] = '';
                $event['place'][$event_nr] = '';
            }

            // *******************
            // *** Save events ***
            // *******************

            // *** Gramps ***
            // 1 FACT Aaron ben Halevy
            // 2 TYPE Hebrew Name
            if ($buffer6 == '1 FACT') {
                $buffer = '1 EVEN ' . substr($buffer, 7);
                $buffer6 = '1 EVEN';
                $level[1] = 'EVEN';
                $fact = true;
            }

            // *** Aldfaer not married ***
            if ($buffer == '1 _NOPARTNER') {
                $buffer = '1 _NMAR';
                $buffer7 = '1 _NMAR';
                $level[1] = '_NMAR';
            }

            // ALL ITEMS: process text by item, like: if (substr($buffer, 7)) $event_temp=substr($buffer, 7);
            if ($buffer6 == '1 ADOP') { // Adopted
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer7 == '1 _ADPF') { // Adopted by father
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer7 == '1 _ADPM') { // Adopted by mother
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer6 == '1 BAPL') { // LDS baptised
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 BARM') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 BASM') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 BLES') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 CENS') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 CHRA') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 CONF') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 CONL') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 EMIG') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 ENDL') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 FCOM') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer7 == '1 _FNRL') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer6 == '1 GRAD') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 IMMI') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 NATU') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 ORDN') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 PROB') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 RETI') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 SLGC') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 WILL') { // Will
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer7 == '1 _YART') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer7 == '1 _INTE') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer7 == '1 _BRTM') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer7 == '1 _NLIV') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer7 == '1 _NMAR') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 8)) $event_temp = substr($buffer, 8);
            }
            if ($buffer6 == '1 NCHI') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            // BK
            //1 _MILT militaire dienst  Location: Amsterdam
            //2 DATE 3 APR 1996
            //2 NOTE Goedgekeurd.
            //3 CONT 2nd line gebeurtenis bij persoon.
            //3 CONT 3rd line.
            if ($buffer7 == '1 _MILT') {
                $buffer = str_replace("_MILT", "MILI", $buffer);
                $buffer6 = '1 MILI';
                $level[1] = 'MILI';
            }
            if ($buffer6 == '1 MILI') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            //RELI Religion
            if ($buffer6 == '1 EDUC') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 NATI') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 CAST') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            //REFN Ref. nr.  (oown code)
            if ($buffer5 == '1 AFN') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 6)) {
                    $event_temp = substr($buffer, 6);
                }
            }
            if ($buffer5 == '1 SSN') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 6)) {
                    $event_temp = substr($buffer, 6);
                }
            }
            if ($buffer7 == '1 _PRMN') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer6 == '1 IDNO') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer7 == '1 _HEIG') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer7 == '1 _WEIG') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer7 == '1 _EYEC') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer7 == '1 _HAIR') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer6 == '1 DSCR') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer7 == '1 _MEDC') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
            }
            if ($buffer6 == '1 NCHI') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 ANCI') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 DESI') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 PROP') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }

            // *** Other events (no BK?) ***
            if ($buffer6 == '1 ARVL') { // arrived
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 BAPM') { // baptised as child
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 DIVF') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 DPRT') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }

            if ($buffer6 == '1 LEGI') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 SLGL') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }
            if ($buffer6 == '1 TXPY') {
                $processed = 1;
                $event_status = "1";
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }

            // *** Aldfaer, title by name: 1 TITL Ir. ***
            if ($buffer6 == '1 TITL') {
                $processed = 1;
                $event_status = '1';
                if (substr($buffer, 7)) $event_temp = substr($buffer, 7);
            }

            // *** Aldfaer ***
            // 1 EVEN Functie naam
            // 2 TYPE functie
            // 2 NOTE @N26@
            // 2 DATE FROM 1 JAN 1990 TO 1 JAN 2001
            // 2 SOUR @S14@
            //
            // 1 EVEN Onderscheiding naam
            // 2 TYPE onderscheiding
            // 2 NOTE @N28@
            // 2 DATE FROM 1 JAN 1960 TO 12 DEC 1970
            // 2 SOUR @S16@
            //
            // 1 EVEN Predikaat naam
            // 2 TYPE predikaat
            // 2 NOTE @N30@
            // 2 DATE FROM 1 JAN 2001 TO 1 JAN 2004
            // 2 SOUR @S18@

            if ($level[1] == 'EVEN') {
                if ($buffer6 == '1 EVEN') {
                    $processed = 1;
                    // *** Process text after 1 EVEN ***
                    if (substr($buffer, 7)) {
                        $event_temp = substr($buffer, 7);
                        $processed = 1;
                        $event_status = "1";
                    }
                }

                // *** Haza-data ***
                //1 EVEN
                //2 TYPE living
                if (substr($buffer, 0, 13) == '2 TYPE living') {
                    $processed = 1;
                    $pers_alive = 'alive';
                    $event_status = "";
                    $level[1] = "";
                }

                // *** Humo-genealogy ***
                //1 EVEN
                //2 TYPE deceased
                if (substr($buffer, 0, 15) == '2 TYPE deceased') {
                    $processed = 1;
                    $pers_alive = 'deceased';
                    $event_status = "";
                    $level[1] = "";
                }
            }

            if ($event_status) {
                if ($event_start) {
                    $event_start = '';
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'person';
                    $event['connect_id'][$event_nr] = $pers_gedcomnumber;
                    $event['kind'][$event_nr] = 'event';
                    $event['event'][$event_nr] = '';
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = $level[1];
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';

                    // *** Aldfaer, title by name: 1 TITL Ir. ***
                    if ($level[1] == 'TITL') {
                        $event['kind'][$event_nr] = 'title';
                    }

                    // Text by GEDCOM TAG of event:
                    //1 _MILT militaire dienst  Location: Amsterdam
                    if (isset($event_temp)) {
                        //$event['text'][$event_nr]=$this->merge_texts ($event['text'][$event_nr],', ',$event_temp);
                        $event['event'][$event_nr] = $this->merge_texts($event['text'][$event_nr], ', ', $event_temp);
                        $event_temp = '';
                    }

                    $event['place'][$event_nr] = '';
                }

                // *** Save event type ***
                if ($buffer6 == '2 TYPE') {
                    // *** Gramps ***
                    // 1 FACT Aaron ben Halevy
                    // 2 TYPE Hebrew Name
                    if (isset($fact)) {
                        $processed = 1;
                        $event['text'][$event_nr] = substr($buffer, 7);
                    }

                    // *** For Aldfaer ***
                    // 1 EVEN
                    // 2 TYPE birth registration
                    // 2 DATE 21 FEB 1965
                    // 2 SOUR @S9@
                    if ($buffer == '2 TYPE birth registration') {
                        $processed = 1;
                        $event['kind'][$event_nr] = 'birth_declaration';
                    }
                    if ($buffer == '2 TYPE death registration') {
                        $processed = 1;
                        $event['kind'][$event_nr] = 'death_declaration';
                    }

                    // *** Aldfaer nobility (predikaat) by name ***
                    // 1 EVEN Jhr.
                    // 2 TYPE predikaat
                    if ($buffer == '2 TYPE predikaat') {
                        $processed = 1;
                        $event['kind'][$event_nr] = 'nobility';
                    }

                    // *** Aldfaer, lordship (heerlijkheid) after a name: 1 PROP Heerlijkheid ***
                    if ($buffer == '2 TYPE heerlijkheid') {
                        $processed = 1;
                        $event['kind'][$event_nr] = 'lordship';
                    }

                    // *** HZ-21, ash dispersion ***
                    if ($buffer == '2 TYPE ash dispersion') {
                        $processed = 1;
                        $event['kind'][$event_nr] = 'ash dispersion';
                    }

                    // *** Legacy ***
                    if ($buffer == '2 TYPE Property') {
                        $processed = 1;
                        $event['gedcom'][$event_nr] = 'PROP';
                    }

                    // *** Aldfaer if 1 EVEN has no text, but 2 TYPE has text, use that for title of event  ***
                    // The name of the event will be displayed as-is, in the language as entered in the Gedcom
                    // 1 EVEN
                    // 2 TYPE E-mail adres
                    // 2 NOTE @N457@
                    // 1 EVEN
                    // 2 TYPE Telefoon
                    // 2 NOTE @N456@
                    if (substr($buffer, 7) and $level[1] == 'EVEN' and $event['kind'][$event_nr] == 'event' and $event['event'][$event_nr] == '') {
                        $processed = 1;
                        $event['gedcom'][$event_nr] = substr($buffer, 7) . ":";
                    }
                }

                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 PLAC') {
                    $processed = 1;
                    $event['place'][$event_nr] = substr($buffer, 7);
                }

                if ($level[2] == 'NOTE') {
                    $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, '2');
                }

                // *** Ancestry FTM: Normally there is a 2 NOTE > 3 CONC/ 3 CONT structure... ***
                //1 IMMI
                //2 CONC John.
                if ($level[2] == 'CONT') {
                    $processed = 1;
                    $event['text'][$event_nr] .= $this->cont(substr($buffer, 7));
                }
                if ($level[2] == 'CONC') {
                    $processed = 1;
                    $event['text'][$event_nr] .= $this->conc(substr($buffer, 7));
                }

                // *** Aldfaer has source by event: 2 SOUR @S9@
                // *** Source by person event ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_event_source', $calculated_event_id, $buffer, '2');
                }

                // *** Picture by event ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE d:\Mijn documenten\Mijn Stamboom\Media\Afbeeldingen\henk.jpg
                // 3 _SCBK Y
                // 3 _PRIM Y
                // 3 _TYPE PHOTO
                // *** Picture is connected to event_id column (=$calculated_event_id) ***
                if ($level[2] == 'OBJE') {
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_event_' . $calculated_event_id, $buffer);
                }
            }

            // Process here because of: 2 TYPE living
            if ($buffer == '1 EVEN') {
                $processed = 1;
                $event_status = "1";
            }

            //*** Person source ***
            //Haza-data
            //1 SOUR @S1@
            //2 ROLE Persoonskaart
            //2 DATE
            if ($level[1] == 'SOUR') {
                $this->process_sources('person', 'person_source', $pers_gedcomnumber, $buffer, '1');
            }

            // ********************************************************************************************
            // *** Save non processed GEDCOM items ***
            // ********************************************************************************************
            $buffer = trim($buffer);
            // Skip these lines
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }

            // Aldfaer picture info
            if (strtolower($buffer) == '2 form jpg') {
                $processed = 1;
            }

            if ($buffer6 == '1 RIN ') {
                $processed = 1;
            }
            if ($buffer5 == '1 RFN') {
                $processed = 1;
            }

            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }
                if ($person["pers_unprocessed_tags"]) {
                    $person["pers_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $person["pers_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $person["pers_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $person["pers_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $person["pers_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $person["pers_unprocessed_tags"] .= '|' . $buffer;
            }
        }  //end explode

        if ($add_tree == true or $reassign == true) {
            if ($pers_name_text) $pers_name_text = $this->reassign_ged($pers_name_text, 'N');
            if ($person["pers_text"]) $person["pers_text"] = $this->reassign_ged($person["pers_text"], 'N');
            if ($pers_birth_text) $pers_birth_text = $this->reassign_ged($pers_birth_text, 'N');
            if ($pers_bapt_text) $pers_bapt_text = $this->reassign_ged($pers_bapt_text, 'N');
            if ($pers_death_text) $pers_death_text = $this->reassign_ged($pers_death_text, 'N');
            if ($pers_buried_text) $pers_buried_text = $this->reassign_ged($pers_buried_text, 'N');
        }

        // *** Process estimates/ calculated date for privacy filter ***
        if ($pers_birth_date) $person["pers_cal_date"] = $pers_birth_date;
        elseif ($pers_bapt_date) $person["pers_cal_date"] = $pers_bapt_date;

        // for Jewish dates after nightfall
        $heb_qry = '';
        if ($pers_heb_flag == 1) {  // At least one nightfall date is imported. We have to make sure the required tables exist and if not create them
            $column_qry = $dbh->query('SHOW COLUMNS FROM humo_persons');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['pers_birth_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_birth_date;";
                $result = $dbh->query($sql);
            }
            if (!isset($field['pers_death_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_death_date;";
                $result = $dbh->query($sql);
            }
            if (!isset($field['pers_buried_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_buried_date;";
                $result = $dbh->query($sql);
            }
            // we have to add these values to the query below
            $heb_qry .= "pers_birth_date_hebnight='" . $pers_birth_date_hebnight . "',
        pers_death_date_hebnight='" . $pers_death_date_hebnight . "', pers_buried_date_hebnight='" . $pers_buried_date_hebnight . "',";
        }

        // Lastname prefixes, THIS PART SLOWS DOWN READING A BIT!!!
        // Check for accent or space in pers_lastname...
        if (strpos($pers_lastname, "'") or strpos($pers_lastname, " ")) {
            $loop2 = count($prefix);
            for ($i = 0; $i < $loop2; $i++) {
                $check_prefix = substr($pers_lastname, 0, $prefix_length[$i]);
                if (strtolower($check_prefix) == $prefix[$i]) {
                    // *** Show prefixes with a capital letter ***
                    $person["pers_prefix"] = str_replace(" ", "_", $check_prefix);
                    $pers_lastname = substr($pers_lastname, $prefix_length[$i]);
                }
            }
        }

        // *** Save data ***
        //pers_callname='".$this->text_process($pers_callname)."',
        $sql = "INSERT IGNORE INTO humo_persons SET
    pers_gedcomnumber='" . $this->text_process($pers_gedcomnumber) . "',
    pers_tree_id='" . $tree_id . "',
    pers_tree_prefix='" . $tree_prefix . "',
    pers_fams='" . $this->text_process($fams) . "',
    pers_famc='" . $this->text_process($pers_famc) . "',
    pers_firstname='" . $this->text_process($pers_firstname) . "', pers_lastname='" . $this->text_process($pers_lastname) . "',
    pers_name_text='" . $this->text_process($pers_name_text) . "',
    pers_prefix='" . $this->text_process($person["pers_prefix"]) . "',
    pers_patronym='" . $this->text_process($person["pers_patronym"]) . "',
    pers_place_index='" . $this->text_process($pers_place_index) . "',
    pers_text='" . $this->text_process($person["pers_text"]) . "',
    pers_birth_date='" . $this->process_date($this->text_process($pers_birth_date)) . "', pers_birth_time='" . $this->text_process($pers_birth_time) . "',
    pers_birth_place='" . $this->text_process($pers_birth_place) . "',
    pers_birth_text='" . $this->text_process($pers_birth_text) . "',
    pers_stillborn='" . $pers_stillborn . "',
    pers_bapt_date='" . $this->process_date($this->text_process($pers_bapt_date)) . "', pers_bapt_place='" . $this->text_process($pers_bapt_place) . "',
    pers_bapt_text='" . $this->text_process($pers_bapt_text) . "',
    pers_religion='" . $this->text_process($pers_religion) . "',
    pers_death_date='" . $this->process_date($this->text_process($pers_death_date)) . "', pers_death_time='$pers_death_time',
    pers_death_place='" . $this->text_process($pers_death_place) . "',
    pers_death_text='" . $this->text_process($pers_death_text) . "',
    pers_buried_date='" . $this->process_date($this->text_process($pers_buried_date)) . "', pers_buried_place='" . $this->text_process($pers_buried_place) . "',
    pers_buried_text='" . $this->text_process($pers_buried_text) . "',
    pers_cal_date='" . $this->process_date($this->text_process($person["pers_cal_date"])) . "',
    pers_cremation='" . $pers_cremation . "',
    pers_death_cause='" . $this->text_process($pers_death_cause) . "',
    pers_death_age='" . $this->text_process($person["pers_death_age"]) . "',
    pers_sexe='" . $pers_sexe . "',
    pers_own_code='" . $this->text_process($person["pers_own_code"]) . "',
    pers_quality='" . $this->text_process($person["pers_quality"]) . "',
    pers_new_date='" . $this->process_date($this->text_process($person["new_date"])) . "',
    pers_new_time='" . $person["new_time"] . "',
    pers_changed_date='" . $this->process_date($this->text_process($person["changed_date"])) . "',
    pers_changed_time='" . $person["changed_time"] . "'," . $heb_qry . "
    pers_alive='" . $pers_alive . "'";

        if (isset($_POST['debug_mode']) and $_SESSION['debug_person'] < 2) {
            echo '<br>' . $sql . '<br>';
            $_SESSION['debug_person']++; // *** Only process debug line once ***
        }

        // *** Process SQL ***
        $result = $dbh->query($sql);

        $pers_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($person["pers_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_pers_id='" . $pers_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($person["pers_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }

        // *** Empty variable to free memory ***
        unset($person);


        // *** Save sources ***
        if ($nrsource > 0) {
            for ($i = 1; $i <= $nrsource; $i++) {
                $sql = "INSERT IGNORE INTO humo_sources SET
            source_tree_id='" . $tree_id . "',
            source_gedcomnr='" . $this->text_process($source["source_gedcomnr"][$i]) . "',
            source_status='" . $source["source_status"][$i] . "',
            source_title='" . $this->text_process($source["source_title"][$i]) . "',
            source_abbr='" . $this->text_process($source["source_abbr"][$i]) . "',
            source_date='" . $this->process_date($this->text_process($source["source_date"][$i])) . "',
            source_publ='" . $this->text_process($source["source_publ"][$i]) . "',
            source_place='" . $this->text_process($source["source_place"][$i]) . "',
            source_refn='" . $this->text_process($source["source_refn"][$i]) . "',
            source_auth='" . $this->text_process($source["source_auth"][$i]) . "',
            source_subj='" . $this->text_process($source["source_subj"][$i]) . "',
            source_item='" . $this->text_process($source["source_item"][$i]) . "',
            source_kind='" . $this->text_process($source["source_kind"][$i]) . "',
            source_text='" . $this->text_process($source["source_text"][$i]) . "',
            source_repo_name='" . $this->text_process($source["source_repo_name"][$i]) . "',
            source_repo_caln='" . $this->text_process($source["source_repo_caln"][$i]) . "',
            source_repo_page='" . $this->text_process($source["source_repo_page"][$i]) . "',
            source_repo_gedcomnr='" . $this->text_process($source["source_repo_gedcomnr"][$i]) . "',
            source_new_date='" . $this->process_date($source['new_date'][$i]) . "',
            source_new_time='" . $source['new_time'][$i] . "',
            source_changed_date='" . $this->process_date($source['changed_date'][$i]) . "',
            source_changed_time='" . $source['changed_time'][$i] . "'
            ";
                $result = $dbh->query($sql);
            }
            //$source_id=$dbh->lastInsertId();
            unset($source);
        }

        // *** Save unprocessed items ***
        //if ($source["source_unprocessed_tags"]){
        //	$sql="INSERT IGNORE INTO humo_unprocessed_tags SET
        //		tag_source_id='".$source_id."',
        //		tag_tree_id='".$tree_id."',
        //		tag_tag='".$this->text_process($source["source_unprocessed_tags"])."'";
        //	$result=$dbh->query($sql);
        //}


        // *** Save addressses in separate table ***
        if ($nraddress2 > 0) {
            for ($i = 1; $i <= $nraddress2; $i++) {
                //address_connect_kind='person',
                //address_connect_sub_kind='person',
                //address_connect_id='".$this->text_process($pers_gedcomnumber)."',
                //address_order='".$i."',
                $gebeurtsql = "INSERT IGNORE INTO humo_addresses SET
                address_tree_id='" . $tree_id . "',
                address_gedcomnr='" . $this->text_process($address_array["gedcomnr"][$i]) . "',
                address_place='" . $this->text_process($address_array["place"][$i]) . "',
                address_address='" . $this->text_process($address_array["address"][$i]) . "',
                address_zip='" . $this->text_process($address_array["zip"][$i]) . "',
                address_phone='" . $this->text_process($address_array["phone"][$i]) . "',
                address_date='" . $this->process_date($this->text_process($address_array["date"][$i])) . "',
                address_text='" . $this->text_process($address_array["text"][$i]) . "'";
                //echo $gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            unset($address_array);
        }
        // unprocessed items?????


        // *** Store geolocations in humo_locations table ***
        if ($geocode_nr > 0) {
            // *** Check if table exists already if not create it ***
            $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
            if (!$temp->rowCount()) {
                $locationtbl = "CREATE TABLE humo_location (
            location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            location_location VARCHAR(100) CHARACTER SET utf8,
            location_lat DECIMAL(10,6),
            location_lng DECIMAL(10,6),
            location_status TEXT
            ) DEFAULT CHARSET=utf8";
                $dbh->query($locationtbl);
            }
            for ($i = 1; $i <= $geocode_nr; $i++) {
                $loc_qry = $dbh->query("SELECT * FROM humo_location WHERE location_location = '" . $this->text_process($geocode_plac[$i]) . "'");
                if (!$loc_qry->rowCount() and $geocode_type[$geocode_nr] != "") {  // doesn't appear in the table yet and the location belongs to birth, bapt, death or buried event) {  
                    $geosql = "INSERT IGNORE INTO humo_location SET
                location_location='" . $this->text_process($geocode_plac[$i]) . "',
                location_lat='" . $geocode_lati[$i] . "',
                location_lng='" . $geocode_long[$i] . "',
                location_status='" . $tree_prefix . $geocode_type[$i] . "'
                ";
                    $dbh->query($geosql);
                } elseif ($loc_qry->rowCount() and $geocode_type[$geocode_nr] != "") {   // location already exists, check if we need to add something in location_status
                    $loc_qryDb = $loc_qry->fetch(PDO::FETCH_OBJ);
                    if (strpos($loc_qryDb->location_status, $tree_prefix . $geocode_type[$i]) === false) {
                        $dbh->query("UPDATE humo_location SET location_status = CONCAT(location_status,' " . $tree_prefix . $geocode_type[$i] . "') WHERE location_location = '" . $this->text_process($geocode_plac[$i]) . "'");
                    }
                }
            }
            if (strpos($humo_option['geo_trees'], "@" . $tree_id . ";") === false) {
                $dbh->query("UPDATE humo_settings SET setting_value = CONCAT(setting_value,'@" . $tree_id . ";') WHERE setting_variable = 'geo_trees'");
                $humo_option['geo_trees'] .= "@" . $tree_id . ";";
            }
        }

        // *** Save events in seperate table ***
        if ($event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $event['kind']['1'];
            for ($i = 1; $i <= $event_nr; $i++) {
                $event_order++;
                if ($check_event_kind != $event['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $event['kind'][$i];
                }
                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                event_tree_id='" . $tree_id . "',
                event_order='" . $event_order . "',
                event_connect_kind='" . $this->text_process($event['connect_kind'][$i]) . "',
                event_connect_id='" . $this->text_process($event['connect_id'][$i]) . "',
                event_kind='" . $this->text_process($event['kind'][$i]) . "',
                event_event='" . $this->text_process($event['event'][$i]) . "',
                event_event_extra='" . $this->text_process($event['event_extra'][$i]) . "',
                event_gedcom='" . $this->text_process($event['gedcom'][$i]) . "',
                event_date='" . $this->process_date($this->text_process($event['date'][$i])) . "',
                event_text='" . $this->text_process($event['text'][$i]) . "',
                event_place='" . $this->text_process($event['place'][$i]) . "'";
                //echo $gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //echo ' '.memory_get_usage().'@ ';
        }



        // *** Save events CONNECTED TO EVENTS (e.g. picture by event) in seperate table ***
        if ($event2_nr > 0) {
            $event_order = 0;
            $check_event_kind = $event2['kind']['1'];
            for ($i = 1; $i <= $event2_nr; $i++) {
                $calculated_event_id++;
                $event_order++;
                if ($check_event_kind != $event2['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $event2['kind'][$i];
                }
                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                event_tree_id='" . $tree_id . "',
                event_order='" . $event_order . "',
                event_connect_kind='" . $this->text_process($event2['connect_kind'][$i]) . "',
                event_connect_id='" . $this->text_process($event2['connect_id'][$i]) . "',
                event_kind='" . $this->text_process($event2['kind'][$i]) . "',
                event_event='" . $this->text_process($event2['event'][$i]) . "',
                event_event_extra='" . $this->text_process($event2['event_extra'][$i]) . "',
                event_gedcom='" . $this->text_process($event2['gedcom'][$i]) . "',
                event_date='" . $this->process_date($this->text_process($event2['date'][$i])) . "',
                event_text='" . $this->text_process($event2['text'][$i]) . "',
                event_place='" . $this->text_process($event2['place'][$i]) . "'";
                $result = $dbh->query($gebeurtsql);
            }
            //$event2=null;
            unset($event2);
        }

        // *** Add a general source to all persons in this GEDCOM file (source_id is temporary number!) ***
        if ($humo_option["gedcom_read_add_source"] == 'y') {
            // *** Used for general numbering of connections ***
            $connect_nr++;
            $calculated_connect_id++;

            // *** Seperate numbering, because there can be sources by a address ***
            $address_connect_nr = $connect_nr;

            $connect['kind'][$connect_nr] = 'person';
            $connect['sub_kind'][$connect_nr] = 'person_source';
            $connect['connect_id'][$connect_nr] = $pers_gedcomnumber;
            $connect['source_id'][$connect_nr] = 'Stemporary';
            $connect['text'][$connect_nr] = '';
            $connect['item_id'][$connect_nr] = '';
            $connect['quality'][$connect_nr] = '';
            $connect['place'][$connect_nr] = '';
            $connect['page'][$connect_nr] = '';
            $connect['role'][$connect_nr] = '';
            $connect['date'][$connect_nr] = '';
        }

        // *** Save connections in seperate table ***
        if ($connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $connect['kind']['1'] . $connect['sub_kind']['1'] . $connect['connect_id']['1'];
            for ($i = 1; $i <= $connect_nr; $i++) {
                $connect_order++;
                if ($check_connect != $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                if ($connect['sub_kind'][$i] == 'person_address') {
                    if (isset($connect['connect_order'][$i])) $connect_order = $connect['connect_order'][$i];
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id='" . $tree_id . "',
                connect_order='" . $connect_order . "',
                connect_kind='" . $connect['kind'][$i] . "',
                connect_sub_kind='" . $connect['sub_kind'][$i] . "',
                connect_connect_id='" . $this->text_process($connect['connect_id'][$i]) . "',
                connect_source_id='" . $this->text_process($connect['source_id'][$i]) . "',
                connect_quality='" . $connect['quality'][$i] . "',
                connect_item_id='" . $connect['item_id'][$i] . "',
                connect_text='" . $this->text_process($connect['text'][$i]) . "',
                connect_page='" . $this->text_process($connect['page'][$i]) . "',
                connect_role='" . $this->text_process($connect['role'][$i]) . "',
                connect_date='" . $this->process_date($this->text_process($connect['date'][$i])) . "',
                connect_place='" . $this->text_process($connect['place'][$i]) . "'
                ";

                //echo $check_connect.' !! '.$gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($connect);
            //$connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        // *** TEST ONLY: show processed time per person ***
        //global $start_time;
        //echo ':'.(time()-$person_time).' '.(time()-$start_time).'<br>';

        //$process_time=time()-$person_time;
        //echo ':'.$process_time.'<br>';

    } //end person


    // ************************************************************************************************
    // *** Process families ***
    // ************************************************************************************************
    function process_family($family_array, $first_marr, $second_marr)
    {
        global $dbh, $tree_id, $tree_prefix, $gen_program, $not_processed;
        global $processed, $level;
        global $largest_pers_ged, $largest_fam_ged, $largest_source_ged, $largest_text_ged, $largest_repo_ged, $largest_address_ged;
        global $add_tree, $reassign;
        global $geocode_nr, $geocode_plac, $geocode_lati, $geocode_long, $geocode_type, $humo_option;
        global $connect_nr, $connect, $calculated_connect_id;
        // *** Needed for picture function ***
        global $event, $event_nr, $calculated_event_id;
        global $nrsource, $source;
        global $address_array, $nraddress2, $address_order;

        $line = $family_array;
        $line2 = explode("\n", $line);

        //********************************************************************************************
        // *** Family ***
        //********************************************************************************************
        // 0 @F1@ FAM
        // 0 @F1389_1390@ FAM aldfaer

        unset($family);  //Reset de hele array

        // *** For source connect table ***
        $connect_nr = 0;

        $family["fam_religion"] = "";
        $family["fam_kind"] = "";
        $family["fam_text"] = "";
        $family["fam_marr_church_notice_date"] = "";
        $family["fam_marr_church_notice_place"] = "";
        $family["fam_marr_church_notice_text"] = "";
        $family["fam_marr_church_date"] = "";
        $family["fam_marr_church_place"] = "";
        $family["fam_marr_church_text"] = "";
        // *** Living together ***
        $family["fam_relation_date"] = "";
        $family["fam_relation_place"] = "";
        $family["fam_relation_text"] = "";
        $family["fam_relation_end_date"] = "";
        $family["fam_man_age"] = '';
        $family["fam_woman_age"] = '';
        $family["fam_marr_notice_date"] = "";
        $family["fam_marr_notice_place"] = "";
        $family["fam_marr_notice_text"] = "";
        $family["fam_marr_date"] = "";
        $family["fam_marr_place"] = "";
        $family["fam_marr_text"] = "";
        $family["fam_marr_authority"] = "";
        $family["fam_div"] = false;
        $family["fam_div_date"] = "";
        $family["fam_div_place"] = "";
        $family["fam_div_text"] = "";
        $family["fam_div_authority"] = "";
        $family["fam_cal_date"] = "";
        $family["fam_unprocessed_tags"] = "";
        $family["new_date"] = "";
        $family["new_time"] = "";
        $family["changed_date"] = "";
        $family["changed_time"] = "";
        $family["fam_marr_date_hebnight"] = "";
        $family["fam_marr_notice_date_hebnight"] = "";
        $family["fam_marr_church_date_hebnight"] = "";
        $family["fam_marr_church_notice_date_hebnight"] = "";
        $heb_flag = "";
        $fam_children = "";
        $fam_man = 0;
        $fam_woman = 0;

        $event_status = "";
        $event_nr = 0;

        // *** Save addresses in a seperate table ***
        $nraddress2 = 0;
        $address_order = 0;

        // *** Save sources in a seperate table ***
        $nrsource = 0;

        $geocode_nr = 0;

        // *** Process 1st line ***
        $buffer = $line2[0];
        $gedcomnumber = substr($buffer, 3, -5);

        if ($second_marr > 0) {
            $gedcomnumber .= "U1";
        }  // create unique nr. if 1st is F23, then 2nd will be F23U1

        if ($add_tree == true or $reassign == true) {
            $gedcomnumber = $this->reassign_ged($gedcomnumber, 'F');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            print "$gedcomnumber ";
        }

        // *** Save Level0 ***
        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";
        $level['1a'] = '';

        $temp_kind = ''; // marriage kind

        // *** Process other lines ***
        $loop = count($line2) - 2;

        $marr_flag = 0;

        for ($z = 1; $z <= $loop; $z++) {

            if ($second_marr > 0 and $z >= $first_marr and $z < $second_marr) continue; // skip lines that belong to 1st marriage

            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  //newline strippen

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 5));  //rtrim for DIV_/ CHR_
                $level['1a'] = rtrim($buffer);  // *** Needed to test for 1 RESI @. Update: rtrim not really neccesary anymore? ***

                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";

                // *** Same couple: second marriage in BK program (in 1 @FAM part) ***
                $search_marr = rtrim(substr($buffer, 2, 5));
                //if($search_marr=="MARR" OR $search_marr=="MARB" OR $search_marr=="MARL") {
                if ($gen_program == 'BROSKEEP' and ($search_marr == "MARR" or $search_marr == "MARB" or $search_marr == "MARL")) {
                    if ($marr_flag == 1 and $family["fam_div"] == true) {
                        // this is a second MARR in this @FAM after a divorce so second marriage of these people
                        $this->process_family($family_array, $skipfrom, $z); // calls itself with parameters what to skip
                        break;
                    } elseif ($second_marr == 0) {
                        // this is regular first marriage of these people (usually the only one....:-)
                        if ($marr_flag == 0) { // flag only position of first liason before DIV
                            $marr_flag = 1; // flag that a MARR/MARB/MARL has been encountered
                            $skipfrom = $z; // if 2nd MARR will be encountered after a DIV then from this line should be skipped on second run
                        }
                    }
                }
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            // *** Save date ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $family["new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $family["new_time"] = substr($buffer, 7);
                }
            }

            // *** Save change date ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $family["changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $family["changed_time"] = substr($buffer, 7);
                }
            }

            // *** Witnesses ***
            //1 WITN Doeko/Mons/
            //1 WITN Rene/Mansveld/
            if ($buffer6 == '1 WITN') {
                $processed = 1;
                $buffer = str_replace("/", " ", $buffer);
                $buffer = str_replace("  ", " ", $buffer);
                $buffer = trim($buffer);
                $event_nr++;
                $calculated_event_id++;
                $event['connect_kind'][$event_nr] = 'family';
                $event['connect_id'][$event_nr] = $gedcomnumber;
                $event['kind'][$event_nr] = 'marriage_witness';
                $event['event'][$event_nr] = substr($buffer, 7);
                $event['event_extra'][$event_nr] = '';
                $event['gedcom'][$event_nr] = 'WITN';
                $event['date'][$event_nr] = '';
                $event['text'][$event_nr] = '';
                $event['place'][$event_nr] = '';
            }

            // *** Type relation (LAT etc.) ***
            if ($buffer6 == '1 TYPE') {
                $processed = 1;
                $family["fam_kind"] = substr($buffer, 7);
            }

            // *** Gedcomnumber man: 1 HUSB @I14@ ***
            if ($buffer8 == '1 HUSB @') {
                $processed = 1;
                $fam_man = substr($buffer, 8, -1);
                if ($add_tree == true or $reassign == true) {
                    $fam_man = $this->reassign_ged($fam_man, 'I');
                }
                if ($second_marr > 0) {
                    $dbh->query("UPDATE humo_persons SET pers_fams = CONCAT(pers_fams,';','" . $gedcomnumber . "')
                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber = '" . $fam_man . "'");
                }
            }

            // *** Gedcomnumber woman: 1 WIFE @I14@ ***
            if ($buffer8 == '1 WIFE @') {
                $processed = 1;
                $fam_woman = substr($buffer, 8, -1);
                if ($add_tree == true or $reassign == true) {
                    $fam_woman = $this->reassign_ged($fam_woman, 'I');
                }
                if ($second_marr > 0) {
                    $dbh->query("UPDATE humo_persons SET pers_fams = CONCAT(pers_fams,';','" . $gedcomnumber . "')
                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber = '" . $fam_woman . "'");
                }
            }
            // *** Gedcomnumbers children ***
            // 1 CHIL @I13@
            // 1 CHIL @I14@
            if ($second_marr == 0) { // only show children in first marriage of same people
                if ($buffer8 == '1 CHIL @') {
                    $processed = 1;
                    $tempnum = substr($buffer, 8, -1);
                    if ($add_tree == true or $reassign == true) {
                        $tempnum = $this->reassign_ged($tempnum, 'I');
                    }
                    $fam_children = $this->merge_texts($fam_children, ';', $tempnum);
                }


                // *** Adoption by person, used in Legacy and RootsMagic ***
                // 2 _FREL Adopted ===>>> Adopted by father.
                // 2 _MREL Adopted ===>>> Adopted by mother.
                /*
            if ($buffer7=='2 _FREL' OR $buffer7=='2 _MREL'){
                $processed=1;
                $child_array=explode(";",$fam_children); $count_children=count($child_array);

                $event_nr++; $calculated_event_id++;
                $event['connect_kind'][$event_nr]='person';
                $event['connect_id'][$event_nr]=$child_array[$count_children-1];
                $event['kind'][$event_nr]='adoption_by_person';
                $event['event'][$event_nr]=$gedcomnumber;
                $event['event_extra'][$event_nr]='';
                $event['gedcom'][$event_nr]=substr($buffer,8); // *** adopted, steph, legal or foster. ***
                $event['date'][$event_nr]='';
                //$event['source'][$event_nr]='';
                $event['text'][$event_nr]='';
                $event['place'][$event_nr]='';

                // *** Child is adopted child, so remove child from children array *** 
                //array_pop($child_array); // *** Remove last item from array ***
                //$fam_children=implode(";", $child_array);
            }
            */
            }

            // Haza-data
            //1 MARB
            //2 TYPE civil
            //2 DATE 01 JAN 2002
            //2 PLAC Alkmaar
            //2 NOTE tekst ondertr wet
            //3 CONT 2nd line

            // ***************************************************************************************
            // *** Marriage license church ***

            // *** Marriage license Aldfaer ***
            if ($level[1] == 'MARL' and $gen_program == 'ALDFAER') {
                $level[1] = "MARB";
                if ($buffer6 == '1 MARL') {
                    $processed = 1;
                }
            }

            if ($level[1] == 'MARB' and $temp_kind == 'religious') {
                if ($buffer6 == '1 MARB') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_marr_church_notice_date"])   $family["fam_marr_church_notice_date"] = trim(substr($buffer, 7));
                }
                if ($buffer7 == '2 _HNIT') {
                    $heb_flag = 1;
                    $processed = 1;
                    $family["fam_marr_church_notice_date_hebnight"] = substr($buffer, 8);
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_marr_church_notice_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_marr_church_notice_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_marr_church_notice_text"] = $this->process_texts($family["fam_marr_church_notice_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_church_notice_source', $gedcomnumber, $buffer, '2');
                }

                // *** Pictures by marriage church notice ********************************
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($level[2] == 'OBJE') $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_church_notice', $buffer);
            }

            // ******************************************************************************************
            // *** Marriage license ***
            // ******************************************************************************************
            if ($level[1] == 'MARB' and $temp_kind != 'religious') {
                // *** Type marriage / relation (civil or religious) ***
                if ($buffer6 == '2 TYPE') {
                    $processed = 1;
                    $temp_kind = strtolower(substr($buffer, 7));
                }
                if ($buffer6 == '1 MARB') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_marr_notice_date"])    $family["fam_marr_notice_date"] = trim(substr($buffer, 7));
                }
                if ($buffer7 == '2 _HNIT') {
                    $heb_flag = 1;
                    $processed = 1;
                    $family["fam_marr_notice_date_hebnight"] = substr($buffer, 8);
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_marr_notice_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_marr_notice_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_marr_notice_text"] = $this->process_texts($family["fam_marr_notice_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_notice_source', $gedcomnumber, $buffer, '2');
                }

                // *** Pictures by marriage licence ********************************
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($level[2] == 'OBJE') $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_notice', $buffer);

                // *** SAME CODE IS USED FOR AGE BY MARRIAGE_NOTICE AND MARRIAGE! ***
                // *** Man age ***
                // 2 HUSB
                // 3 AGE 42y
                if ($level[2] == 'HUSB') {
                    if ($buffer6 == '2 HUSB') {
                        $processed = 1;
                    }
                    if ($buffer5 == '3 AGE') {
                        $processed = 1;
                        $family["fam_man_age"] = substr($buffer, 6);
                    }
                }
                // *** Woman age ***
                // 2 WIFE
                // 3 AGE 42y 6m
                if ($level[2] == 'WIFE') {
                    if ($buffer6 == '2 WIFE') {
                        $processed = 1;
                    }
                    if ($buffer5 == '3 AGE') {
                        $processed = 1;
                        $family["fam_woman_age"] = substr($buffer, 6);
                    }
                }
            }

            // *******************************************************************************************
            // *** Marriage church ***
            // *******************************************************************************************

            if ($level[1] == 'MARR') {
                // *** Witnesses Pro-gen ***
                //1 MARR
                //2 DATE 21 MAY 1874
                //2 PLAC Winsum
                //2 _WITN Aam Pieter Borgman, oud 48 jaar, herbergier, wonende te
                //3 CONT bla bla
                //3 CONT bla bla
                if ($level[2] == '_WIT') {
                    if ($buffer7 == '2 _WITN') {
                        // new way of saving: not tested yet.
                        $processed = 1;
                        $event_nr++;
                        $calculated_event_id++;
                        $event['connect_kind'][$event_nr] = 'family';
                        $event['connect_id'][$event_nr] = $gedcomnumber;
                        $event['kind'][$event_nr] = 'marriage_witness';
                        $event['event'][$event_nr] = substr($buffer, 8);
                        $event['event_extra'][$event_nr] = '';
                        $event['gedcom'][$event_nr] = 'WITN';
                        $event['date'][$event_nr] = '';
                        $event['text'][$event_nr] = '';
                        $event['place'][$event_nr] = '';
                    }
                    if ($buffer6 == '3 CONT') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->cont(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in text marriage!
                    }
                    if ($buffer6 == '3 CONC') {
                        $processed = 1;
                        $event['event'][$event_nr] .= $this->conc(substr($buffer, 7));
                        $buffer = ""; // to prevent use of this text in text marriage!
                    }
                }

                // *** fam_religion ***
                // Haza-data
                //1 MARR
                //2 TYPE religious
                //2 RELI Hervormd
                if ($buffer6 == '2 RELI') {
                    $processed = 1;
                    $family["fam_religion"] = substr($buffer, 7);
                }

                // *** Haza-data marriage authority ***
                // 1 MARR
                // 2 AGNC alkmaar gemeente wettelijk
                if ($buffer6 == '2 AGNC') {
                    $processed = 1;
                    $family["fam_marr_authority"] = substr($buffer, 7);
                }

                // *** Type marriage / relation (civil or religious) ***
                if ($buffer6 == '2 TYPE') {
                    $processed = 1;
                    $temp_kind = strtolower(substr($buffer, 7));
                    // *** Save marriage type in database, to show proper text if there is no further data.
                    //     Otherwise it will be "relation". ***
                    if ($family["fam_kind"] == '') {
                        $family["fam_kind"] = $temp_kind;
                    }

                    // *** Aldfaer relation ***
                    // 0 @F3027@ FAM
                    // 1 HUSB @I784@
                    // 1 WIFE @I258@
                    // 1 MARR
                    // 2 TYPE partners
                    if ($temp_kind == 'partners') {
                        $family["fam_kind"] = 'partners';
                        $buffer = '1 _LIV';
                        $level[1] = '_LIV';
                    } elseif ($temp_kind == 'registered') {
                        $family["fam_kind"] = 'registered';
                        $buffer = '1 _LIV';
                        $level[1] = '_LIV';
                    } elseif ($temp_kind == 'unknown') {
                        $family["fam_kind"] = 'unknown';
                        $buffer = '1 _LIV';
                        $level[1] = '_LIV';
                    }
                }
            }

            //Pro-gen and GensdataPro, licence marriage church:
            //1 ORDI
            //2 DATE 01 JUN 1749
            //2 PLAC Huizen
            //if (substr($buffer,0,1)=='1'){
            //  $temp_kind="";
            //}
            if ($buffer6 == '1 ORDI') {
                $buffer = "1 MARR";
                $temp_kind = "religious";
                $level[1] = 'MARR';
            }

            if ($level[1] == 'MARR' and $temp_kind == 'religious') {
                if ($buffer6 == '1 MARR') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_marr_church_date"])   $family["fam_marr_church_date"] = trim(substr($buffer, 7));
                }
                if ($buffer7 == '2 _HNIT') {
                    $heb_flag = 1;
                    $processed = 1;
                    $family["fam_marr_church_date_hebnight"] = substr($buffer, 8);
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_marr_church_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_marr_church_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_marr_church_text"] = $this->process_texts($family["fam_marr_church_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_church_source', $gedcomnumber, $buffer, '2');
                }

                // *** Pictures by marriage church ********************************
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($level[2] == 'OBJE') $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_church', $buffer);
            }

            // **********************************************************************************************
            // *** Marriage ***
            // **********************************************************************************************
            if ($level[1] == 'MARR' and $temp_kind != 'religious' and $gen_program != 'SukuJutut') {

                if ($buffer6 == '1 MARR') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_marr_date"])  $family["fam_marr_date"] = trim(substr($buffer, 7));
                }
                if ($buffer7 == '2 _HNIT') {
                    $heb_flag = 1;
                    $processed = 1;
                    $family["fam_marr_date_hebnight"] = substr($buffer, 8);
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_marr_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_marr_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_marr_text"] = $this->process_texts($family["fam_marr_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_source', $gedcomnumber, $buffer, '2');
                }

                // *** Pictures by marriage ********************************
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($level[2] == 'OBJE') $this->process_picture('family', $gedcomnumber, 'picture_fam_marr', $buffer);

                // *** SAME CODE IS USED FOR AGE BY MARRIAGE_NOTICE AND MARRIAGE! ***
                // *** Man age ***
                // 2 HUSB
                // 3 AGE 42y
                if ($level[2] == 'HUSB') {
                    if ($buffer6 == '2 HUSB') {
                        $processed = 1;
                    }
                    if ($buffer5 == '3 AGE') {
                        $processed = 1;
                        $family["fam_man_age"] = substr($buffer, 6);
                    }
                }
                // *** Woman age ***
                // 2 WIFE
                // 3 AGE 42y 6m
                if ($level[2] == 'WIFE') {
                    if ($buffer6 == '2 WIFE') {
                        $processed = 1;
                    }
                    if ($buffer5 == '3 AGE') {
                        $processed = 1;
                        $family["fam_woman_age"] = substr($buffer, 6);
                    }
                }
            }

            // ******************************************************************************************
            // Finnish program SukuJutut uses its own code for type of relation
            if ($level[1] == 'MARR' and $gen_program == 'SukuJutut') {
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $finrelation = 'marr';
                    if (substr($buffer, 7, 9) == 'AVOLIITTO') {
                        $family["fam_kind"] = "living together";
                        $finrelation = 'relation';
                    } elseif (substr($buffer, 7, 8) == 'LIITTO 2') {
                        $family["fam_kind"] = "living together";
                        $finrelation = 'relation';
                    } elseif (substr($buffer, 7, 8) == 'LIITTO 3') {
                        $family["fam_kind"] = "engaged";
                        $finrelation = 'relation';
                    } elseif (substr($buffer, 7, 8) == 'LIITTO 4') {
                        $family["fam_kind"] = "homosexual";
                        $finrelation = 'relation';
                    } elseif (substr($buffer, 7, 8) == 'LIITTO 5') {
                        $family["fam_kind"] = "unknown";
                        $finrelation = 'relation';
                    } elseif (substr($buffer, 7, 8) == 'LIITTO 6') {
                        $family["fam_kind"] = "non-marital"; // originally: father/mother of child (no relation - just conception)
                        $finrelation = 'relation';
                    } else {
                        $family["fam_marr_date"] = substr($buffer, 7);
                    }
                }
                if ($finrelation == 'relation' and isset($family["fam_marr_date"])) {
                    $family["fam_relation_date"] = $family["fam_marr_date"];
                    $family["fam_marr_date"] = '';
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_" . $finrelation . "_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_" . $finrelation . "_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_" . $finrelation . "_text"] = $this->process_texts($person["pers_text"], $buffer, '2');
                }
                if ($level[3] == 'NOTE') {
                    $family["fam_" . $finrelation . "_text"] = $this->process_texts($person["pers_text"], $buffer, '3');
                }

                // ***  Process sources ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_' . $finrelation . '_source', $gedcomnumber, $buffer, '2');
                }
            }

            // ******************************************************************************************
            // *** Living together ***
            // ******************************************************************************************

            // NOT TESTED *** BK living together ***
            if ($buffer7 == '1 _COML') {
                $processed = 1;
                $family["fam_kind"] = "living together";
            }

            // Living together BK
            // 0 @F4664@ FAM
            // 1 HUSB @I12409@
            // 1 WIFE @I12410@
            // 1 CHIL @I1830@
            // 1 _NMR
            // 2 SOUR @S409@
            // 3 PAGE Brief  2 october 2001
            // 1 _MSTAT Partners
            // *** Code _NMR is used TWICE in this file ***
            if ($buffer6 == '1 _NMR') {
                $processed = 1;
                $family["fam_kind"] = "non-marital";
            }

            // Haza-data living together, begin and end date
            // 0 @F9@ FAM
            // 1 TYPE non-marital
            // 1 _STRT
            // 2 DATE 01 JAN 2000
            // 1 _END
            // 2 DATE 02 FEB 2003
            //Haza-data: 1 TYPE non-marital > convert to '1 _LIV'
            if (substr($buffer, 0, 18) == '1 TYPE non-marital') {
                $buffer = '1 _LIV';
                $level[1] = '_LIV';
            }

            // OR (Haza-data):
            // 1 TYPE living together
            // 1 _STRT
            // 2 DATE SEP 2005
            // 1 _END
            // 2 DATE 2006
            if ($level[1] == '_STRT') {
                if ($buffer7 == '1 _STRT') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_relation_date"])  $family["fam_relation_date"] = trim(substr($buffer, 7));
                }
            }
            if ($level[1] == '_END') {
                if ($buffer6 == '1 _END') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_relation_end_date"])  $family["fam_relation_end_date"] = trim(substr($buffer, 7));
                }
            }

            // *** Pro-gen & HuMo-genealogy living together: 1 _LIV ***
            //if ($buffer6=='1 _LIV'){ $processed=1; $family["fam_kind"]="living together"; }
            if ($level[1] == '_LIV') {
                if ($buffer6 == '1 _LIV') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_relation_date"])  $family["fam_relation_date"] = substr($buffer, 7);
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_relation_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_relation_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_relation_text"] = $this->process_texts($family["fam_relation_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_relation_source', $gedcomnumber, $buffer, '2');
                }
            }

            // *****************************************************************************************
            // *** Divorce ***
            // *****************************************************************************************

            // *** Divorce BK ***
            //1 _SEPR
            //2 DATE 1933
            //2 SOUR @S326@
            //2 NOTE He left his family.
            if ($buffer7 == '1 _SEPR') {
                $buffer = str_replace("1 _SEPR", "1 DIV", $buffer);
                $level[1] = 'DIV';
            }

            //if ($level[1]=='DIV'){
            if (substr($level[1], 0, 3) == 'DIV') {
                if (substr($buffer, 0, 5) == '1 DIV') {
                    $processed = 1;
                    $family["fam_div"] = true;
                }

                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    if (!$family["fam_div_date"])  $family["fam_div_date"] = trim(substr($buffer, 7));
                }

                if ($level[2] == 'PLAC') {
                    if ($buffer6 == '2 PLAC') {
                        $processed = 1;
                        $family["fam_div_place"] = $this->process_place(substr($buffer, 7));
                    }
                    $this->process_places($family["fam_div_place"], $buffer);
                }

                if ($level[2] == 'NOTE') {
                    $family["fam_div_text"] = $this->process_texts($family["fam_div_text"], $buffer, '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_div_source', $gedcomnumber, $buffer, '2');
                }

                // *** Haza-data div. authority ***
                // 1 DIV
                // 2 AGNC alkmaar scheiding
                if ($buffer6 == '2 AGNC') {
                    $processed = 1;
                    $family["fam_div_authority"] = substr($buffer, 7);
                }
            }

            // **********************************************************************************************
            // *** Text by family ***
            if ($level[1] == 'NOTE') {
                $family["fam_text"] = $this->process_texts($family["fam_text"], $buffer, '1');

                // *** BK: source by family text ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_text_source', $gedcomnumber, $buffer, '2');
                }
            }

            // *** Pictures by family ********************************
            // 1 OBJE
            // 2 FORM jpg
            // 2 FILE C:\Documents and Settings\Mijn documenten\test.jpg
            // 2 TITL test
            //if ($level[1]=='OBJE') $this->process_picture('','','picture', $buffer);
            if ($level[1] == 'OBJE') $this->process_picture('family', $gedcomnumber, 'picture', $buffer);

            // *********************************************************************
            // *** Events ***
            // *********************************************************************
            //1 MILI
            //2 TYPE militaire dienst
            //2 DATE 01 JAN 1999
            //2 NOTE test

            //1 EVEN
            //2 TYPE gebeurtenis
            //2 DATE 01 JAN 2001
            //2 PLAC Alkmaar
            //2 NOTE gebeurtenis

            if ($buffer7 == '1 _MBON') {
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 MARC') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            // *** Aldfaer: MARL = marriage license! ***
            if ($buffer6 == '1 MARL' and $gen_program != 'ALDFAER') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 MARS') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 DIVF') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 ANUL') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 ENGA') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 SLGS') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 CENS') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            // *** Code _NMR is twice in this file ***
            if ($buffer6 == '1 _NMR') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer7 == '1 _COML') {
                if (substr($buffer, 8)) {
                    $event_temp = substr($buffer, 8);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 NCHI') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer5 == '1 RFN') {
                if (substr($buffer, 6)) {
                    $event_temp = substr($buffer, 6);
                }
                $processed = 1;
                $event_status = "1";
            }
            if ($buffer6 == '1 REFN') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }

            // Other events (no BK?)
            //if ($buffer=='1 EVEN'){$processed=1; $event_status="1";}
            if ($buffer6 == '1 EVEN') {
                if (substr($buffer, 7)) {
                    $event_temp = substr($buffer, 7);
                }
                $processed = 1;
                $event_status = "1";
            }

            if ($buffer == '1 SLGL') {
                $processed = 1;
                $event_status = "1";
            }

            if ($event_status) {
                if ($event_start) {
                    $event_start = '';
                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'family';
                    $event['connect_id'][$event_nr] = $gedcomnumber;
                    $event['kind'][$event_nr] = 'event';
                    $event['event'][$event_nr] = '';
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = $level[1];
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';

                    if (isset($event_temp)) {
                        //$event['text'][$event_nr]=$this->merge_texts ($event['text'][$event_nr],', ',$event_temp);
                        $event['event'][$event_nr] = $this->merge_texts($event['text'][$event_nr], ', ', $event_temp);

                        $event_temp = '';
                    }
                }
                // *** Save type ***
                if ($buffer6 == '2 TYPE') {
                    $processed = 1;
                    $event['event'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 PLAC') {
                    $processed = 1;
                    $event['place'][$event_nr] = substr($buffer, 7);
                }
                //if (copy(buf,1,6)='2 TYPE') AND (gen_program='BROSKEEP') then gebeurttekst[nrgebeurtenis]:=gebeurttekst[nrgebeurtenis]+', '+copy(buf,8,length(buf)); //Voor BK!!!

                if ($level[2] == 'NOTE') {
                    $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, '2');
                }

                // *** Source by family event ***
                if ($level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_event_source', $calculated_event_id, $buffer, '2');
                }
            }

            //*** Family source ***
            // BK:
            //1 SOUR @S2@
            //2 PAGE blad 5
            //2 DATA
            //3 TEXT citaat bron
            //2 QUAY 2
            //2 NOTE informatie citaat bron
            //1 SOUR @S3@
            if ($level[1] == 'SOUR') {
                $this->process_sources('family', 'family_source', $gedcomnumber, $buffer, '1');
            }

            // *** Address/Living place for family BK & HuMo-genealogy ***
            //1 RESI
            //2 ADDR Naam gezin
            //3 CONT Address ridderkerk
            //2 PHON telefoon
            //2 FAX fax
            //2 EMAIL mail
            //2 WWW website
            if ($level[1] == 'RESI') {
                $this->process_addresses('family', 'family_address', $gedcomnumber, $buffer);
            }

            //*******************************************************************************************
            // *** Save non-processed items ***
            // ******************************************************************************************
            // Skip these lines
            if ($buffer == '2 ADDR') {
                $processed = 1;
            }
            //if ($buffer=='1 RESI'){ $processed=1; }
            if ($buffer == '1 REPO') {
                $processed = 1;
            }
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }

            if ($buffer5 == '1 RFN') {
                $processed = 1;
            }

            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($family["fam_unprocessed_tags"]) {
                    $family["fam_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $family["fam_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $family["fam_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $family["fam_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $family["fam_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $family["fam_unprocessed_tags"] .= '|' . $buffer;
            }
        }  //end explode

        // SAVE
        // Pro-gen: special treatment for woman without a man... :-)
        if ($gen_program == 'PRO-GEN') {
            if (!$fam_man) {
                $family["fam_kind"] = "PRO-GEN";
            }
        }

        // Aldfaer: special treatment for end of relation.
        // *** Aldfaer uses DIV if an relation is ended! ***
        // 1 DIV
        // 2 DATE 2 JAN 2011
        // 2 PLAC Brunssum
        // 1 MARR
        // 2 TYPE partners
        // etc.
        if ($gen_program == 'ALDFAER' and $family["fam_kind"] == 'partners') {
            $family["fam_div"] = false;
            $family["fam_relation_end_date"] = $family["fam_div_date"];
            $family["fam_div_date"] = '';
            //$family["fam_relation_end_place"] = $family["fam_div_place"];
            $family["fam_div_place"] = '';
            //$family["fam_relation_text"] = $family["fam_div_text"];
            $family["fam_div_text"] = '';
            // Sources and events...
        }

        if ($add_tree == true or $reassign == true) {
            if ($family["fam_text"]) {
                $family["fam_text"] = $this->reassign_ged($family["fam_text"], 'N');
            }
            if ($family["fam_marr_church_notice_text"]) {
                $family["fam_marr_church_notice_text"] = $this->reassign_ged($family["fam_marr_church_notice_text"], 'N');
            }
            if ($family["fam_marr_church_text"]) {
                $family["fam_marr_church_text"] = $this->reassign_ged($family["fam_marr_church_text"], 'N');
            }
            if ($family["fam_relation_text"]) {
                $family["fam_relation_text"] = $this->reassign_ged($family["fam_relation_text"], 'N');
            }
            if ($family["fam_marr_notice_text"]) {
                $family["fam_marr_notice_text"] = $this->reassign_ged($family["fam_marr_notice_text"], 'N');
            }
            if ($family["fam_marr_text"]) {
                $family["fam_marr_text"] = $this->reassign_ged($family["fam_marr_text"], 'N');
            }
            if ($family["fam_div_text"]) {
                $family["fam_div_text"] = $this->reassign_ged($family["fam_div_text"], 'N');
            }
        }

        // *** Save temporary text "DIVORCE" for a divorce without further data ***
        if ($family["fam_div"]) {
            if (
                !$family["fam_div_date"] and !$family["fam_div_place"] and !$family["fam_div_text"]
                and !$family["fam_div_authority"]
            ) {
                $family["fam_div_text"] = 'DIVORCE';
            }
        }

        // *** Process estimates/ calculated date for privacy filter ***
        if ($family["fam_marr_date"]) $family["fam_cal_date"] = $family["fam_marr_date"];
        elseif ($family["fam_marr_church_date"]) $family["fam_cal_date"] = $family["fam_marr_church_date"];

        // for Jewish dates after nightfall
        $heb_qry = '';
        if ($heb_flag == 1) {  // At least one nightfall date is imported. We have to make sure the required tables exist and if not create them
            $column_qry = $dbh->query('SHOW COLUMNS FROM humo_families');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['fam_marr_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_notice_date;";
                $result = $dbh->query($sql);
            }
            if (!isset($field['fam_marr_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_date;";
                $result = $dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_notice_date;";
                $result = $dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_date;";
                $result = $dbh->query($sql);
            }
            // we have to add these values to the query below
            $heb_qry .= "fam_marr_notice_date_hebnight='" . $family["fam_marr_notice_date_hebnight"] . "',fam_marr_date_hebnight='" . $family["fam_marr_date_hebnight"] . "',fam_marr_church_notice_date_hebnight='" . $family["fam_marr_church_notice_date_hebnight"] . "',fam_marr_church_date_hebnight='" . $family["fam_marr_church_date_hebnight"] . "',";
        }

        $sql = "INSERT IGNORE INTO humo_families SET
    fam_tree_id='" . $tree_id . "',
    fam_gedcomnumber='$gedcomnumber',
    fam_man='$fam_man',
    fam_man_age='" . $family["fam_man_age"] . "',
    fam_woman='$fam_woman',
    fam_woman_age='" . $family["fam_woman_age"] . "',
    fam_children='$fam_children',
    fam_religion='" . $this->text_process($family["fam_religion"]) . "',
    fam_kind='" . $this->text_process($family["fam_kind"]) . "',
    fam_text='" . $this->text_process($family["fam_text"]) . "',
    fam_marr_church_notice_date='" . $this->process_date($this->text_process($family["fam_marr_church_notice_date"])) . "',
    fam_marr_church_notice_place='" . $this->text_process($family["fam_marr_church_notice_place"]) . "',
    fam_marr_church_notice_text='" . $this->text_process($family["fam_marr_church_notice_text"]) . "',
    fam_marr_church_date='" . $this->process_date($this->text_process($family["fam_marr_church_date"])) . "',
    fam_marr_church_place='" . $this->text_process($family["fam_marr_church_place"]) . "',
    fam_marr_church_text='" . $this->text_process($family["fam_marr_church_text"]) . "',
    fam_relation_date='" . $this->process_date($this->text_process($family["fam_relation_date"])) . "',
    fam_relation_place='" . $this->text_process($family["fam_relation_place"]) . "',
    fam_relation_text='" . $this->text_process($family["fam_relation_text"]) . "',
    fam_relation_end_date='" . $this->process_date($this->text_process($family["fam_relation_end_date"])) . "',
    fam_marr_notice_date='" . $this->process_date($this->text_process($family["fam_marr_notice_date"])) . "',
    fam_marr_notice_place='" . $this->text_process($family["fam_marr_notice_place"]) . "',
    fam_marr_notice_text='" . $this->text_process($family["fam_marr_notice_text"]) . "',
    fam_marr_date='" . $this->process_date($this->text_process($family["fam_marr_date"])) . "',
    fam_marr_place='" . $this->text_process($family["fam_marr_place"]) . "',
    fam_marr_text='" . $this->text_process($family["fam_marr_text"]) . "',
    fam_marr_authority='" . $this->text_process($family["fam_marr_authority"]) . "',
    fam_div_date='" . $this->process_date($this->text_process($family["fam_div_date"])) . "',
    fam_div_place='" . $this->text_process($family["fam_div_place"]) . "',
    fam_div_text='" . $this->text_process($family["fam_div_text"]) . "',
    fam_div_authority='" . $this->text_process($family["fam_div_authority"]) . "',
    fam_cal_date='" . $this->process_date($this->text_process($family["fam_cal_date"])) . "',
    fam_new_date='" . $this->process_date($family["new_date"]) . "',
    fam_new_time='" . $family["new_time"] . "',
    fam_changed_date='" . $this->process_date($family["changed_date"]) . "'," . $heb_qry . "
    fam_changed_time='" . $family["changed_time"] . "'";

        $result = $dbh->query($sql);

        $fam_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($family["fam_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_rel_id='" . $fam_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($family["fam_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }

        //echo '!!!!'.$nrsource.'<br>';;
        // *** Save sources ***
        if ($nrsource > 0) {
            for ($i = 1; $i <= $nrsource; $i++) {
                $sql = "INSERT IGNORE INTO humo_sources SET
            source_tree_id='" . $tree_id . "',
            source_gedcomnr='" . $this->text_process($source["source_gedcomnr"][$i]) . "',
            source_status='" . $source["source_status"][$i] . "',
            source_title='" . $this->text_process($source["source_title"][$i]) . "',
            source_abbr='" . $this->text_process($source["source_abbr"][$i]) . "',
            source_date='" . $this->process_date($this->text_process($source["source_date"][$i])) . "',
            source_publ='" . $this->text_process($source["source_publ"][$i]) . "',
            source_place='" . $this->text_process($source["source_place"][$i]) . "',
            source_refn='" . $this->text_process($source["source_refn"][$i]) . "',
            source_auth='" . $this->text_process($source["source_auth"][$i]) . "',
            source_subj='" . $this->text_process($source["source_subj"][$i]) . "',
            source_item='" . $this->text_process($source["source_item"][$i]) . "',
            source_kind='" . $this->text_process($source["source_kind"][$i]) . "',
            source_text='" . $this->text_process($source["source_text"][$i]) . "',
            source_repo_name='" . $this->text_process($source["source_repo_name"][$i]) . "',
            source_repo_caln='" . $this->text_process($source["source_repo_caln"][$i]) . "',
            source_repo_page='" . $this->text_process($source["source_repo_page"][$i]) . "',
            source_repo_gedcomnr='" . $this->text_process($source["source_repo_gedcomnr"][$i]) . "',
            source_new_date='" . $this->process_date($source['new_date'][$i]) . "',
            source_new_time='" . $source['new_time'][$i] . "',
            source_changed_date='" . $this->process_date($source['changed_date'][$i]) . "',
            source_changed_time='" . $source['changed_time'][$i] . "'
            ";
                //echo $sql.' FAM<br>';
                $result = $dbh->query($sql);
            }
            //$source_id=$dbh->lastInsertId();
            unset($source);
        }
        // Unprocessed items???

        // *** Save addressses ***
        if ($nraddress2 > 0) {
            for ($i = 1; $i <= $nraddress2; $i++) {
                //address_order='".$i."',
                //address_connect_kind='family',
                //address_connect_sub_kind='family',
                //address_connect_id='".$this->text_process($gedcomnumber)."',
                $gebeurtsql = "INSERT IGNORE INTO humo_addresses SET
                address_tree_id='" . $tree_id . "',
                address_gedcomnr='" . $this->text_process($address_array["gedcomnr"][$i]) . "',
                address_place='" . $this->text_process($address_array["place"][$i]) . "',
                address_address='" . $this->text_process($address_array["address"][$i]) . "',
                address_zip='" . $this->text_process($address_array["zip"][$i]) . "',
                address_phone='" . $this->text_process($address_array["phone"][$i]) . "',
                address_date='" . $this->process_date($this->text_process($address_array["date"][$i])) . "',
                address_text='" . $this->text_process($address_array["text"][$i]) . "'";
                //echo $gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }
        }
        // Unprocessed items???

        // store geolocations in humo_locations table
        if ($geocode_nr > 0) {
            // Check if table exists already if not create it
            $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
            if (!$temp->rowCount()) {
                $locationtbl = "CREATE TABLE humo_location (
                location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                location_location VARCHAR(120) CHARACTER SET utf8,
                location_lat DECIMAL(10,6),
                location_lng DECIMAL(10,6),
                location_status TEXT
            ) DEFAULT CHARSET=utf8";
                $dbh->query($locationtbl);
            }
            for ($i = 1; $i <= $geocode_nr; $i++) {
                $loc_qry = $dbh->query("SELECT * FROM humo_location WHERE location_location = '" . $this->text_process($geocode_plac[$i]) . "'");

                if (!$loc_qry->rowCount() and $geocode_type[$geocode_nr] != "") {  // doesn't appear in the table yet and the location belongs to birth, bapt, death or buried event
                    $geosql = "INSERT IGNORE INTO humo_location SET
                    location_location='" . $this->text_process($geocode_plac[$i]) . "',
                    location_lat='" . $geocode_lati[$i] . "',
                    location_lng='" . $geocode_long[$i] . "',
                    location_status='" . $tree_prefix . $geocode_type[$i] . "'
                    ";
                    $dbh->query($geosql);
                } elseif ($loc_qry->rowCount() and $geocode_type[$geocode_nr] != "") {  // location already exists, check if we need to add something in location_status
                    $loc_qryDb = $loc_qry->fetch(PDO::FETCH_OBJ);
                    if (strpos($loc_qryDb->location_status, $tree_prefix . $geocode_type[$i]) === false) {
                        $dbh->query("UPDATE humo_location SET location_status = CONCAT(location_status,' " . $tree_prefix . $geocode_type[$i] . "') WHERE location_location = '" . $this->text_process($geocode_plac[$i]) . "'");
                    }
                }
            }
            if (strpos($humo_option['geo_trees'], "@" . $tree_id . ";") === false) {
                $dbh->query("UPDATE humo_settings SET setting_value = CONCAT(setting_value,'@" . $tree_id . ";') WHERE setting_variable = 'geo_trees'");
                $humo_option['geo_trees'] .= "@" . $tree_id . ";";
            }
        }

        // *** Save events ***
        if ($event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $event['kind']['1'];
            for ($i = 1; $i <= $event_nr; $i++) {
                //if ($i==1){ $check_event_kind=$event['kind'][$i]; }
                $event_order++;
                if ($check_event_kind != $event['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $event['kind'][$i];
                }
                if ($add_tree == true or $reassign == true) {
                    if ($event['text'][$i]) $event['text'][$i] = $this->reassign_ged($event['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                event_tree_id='" . $tree_id . "',
                event_order='" . $event_order . "',
                event_connect_kind='" . $event['connect_kind'][$i] . "',
                event_connect_id='" . $event['connect_id'][$i] . "',
                event_kind='" . $this->text_process($event['kind'][$i]) . "',
                event_event='" . $this->text_process($event['event'][$i]) . "',
                event_event_extra='" . $this->text_process($event['event_extra'][$i]) . "',
                event_gedcom='" . $this->text_process($event['gedcom'][$i]) . "',
                event_date='" . $this->process_date($this->text_process($event['date'][$i])) . "',
                event_text='" . $this->text_process($event['text'][$i]) . "',
                event_place='" . $this->text_process($event['place'][$i]) . "'";
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$event=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save connections in seperate table ***
        if ($connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $connect['kind']['1'] . $connect['sub_kind']['1'] . $connect['connect_id']['1'];
            for ($i = 1; $i <= $connect_nr; $i++) {
                $connect_order++;
                if ($check_connect != $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                if ($connect['sub_kind'][$i] == 'family_address') {
                    if (isset($connect['connect_order'][$i])) $connect_order = $connect['connect_order'][$i];
                }

                if ($add_tree == true or $reassign == true) {
                    if ($connect['text'][$i]) $connect['text'][$i] = $this->reassign_ged($connect['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id='" . $tree_id . "',
                connect_order='" . $connect_order . "',
                connect_kind='" . $connect['kind'][$i] . "',
                connect_sub_kind='" . $connect['sub_kind'][$i] . "',
                connect_connect_id='" . $this->text_process($connect['connect_id'][$i]) . "',
                connect_source_id='" . $this->text_process($connect['source_id'][$i]) . "',
                connect_item_id='" . $this->text_process($connect['item_id'][$i]) . "',
                connect_text='" . $this->text_process($connect['text'][$i]) . "',
                connect_page='" . $this->text_process($connect['page'][$i]) . "',
                connect_role='" . $this->text_process($connect['role'][$i]) . "',
                connect_date='" . $this->process_date($this->text_process($connect['date'][$i])) . "',
                connect_place='" . $this->text_process($connect['place'][$i]) . "'
                ";
                //echo $check_connect.' !! '.$gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }
    }

    // ************************************************************************************************
    // *** Import GEDCOM texts ***
    // ************************************************************************************************
    function process_text($text_array)
    {
        global $dbh, $tree_id, $tree_prefix, $not_processed, $gen_program;
        global $largest_pers_ged, $largest_fam_ged, $largest_source_ged, $largest_text_ged, $largest_repo_ged, $largest_address_ged;
        global $add_tree, $reassign;
        global $connect_nr, $connect;
        $line = $text_array;
        $line2 = explode("\n", $line);
        $buffer = $line2[0];
        $text['text_text'] = '';
        $text["text_unprocessed_tags"] = "";
        $text["new_date"] = '';
        $text["new_time"] = '';
        $text["changed_date"] = '';
        $text["changed_time"] = '';
        // *** For source connect table ***
        $connect_nr = 0;


        // 0 @N954@ NOTE
        // *** Save as N954 ***
        // *** Strpos: we can search for the character, ignoring anything before the offset ***
        $second_char = strpos($buffer, '@', 3);
        //$text['text_gedcomnr']=substr($buffer, 2, $second_char-1);
        $text['text_gedcomnr'] = substr($buffer, 3, $second_char - 3);

        if ($add_tree == true or $reassign == true) {
            $text['text_gedcomnr'] = $this->reassign_ged($text['text_gedcomnr'], 'N');
        }

        // *** Check for text after "NOTE":
        if (strlen($buffer) > $second_char + 7) {
            $text['text_text'] = substr($buffer, $second_char + 7);
        }

        if (isset($_POST['show_gedcomnumbers'])) {
            print str_replace("@", "", $text['text_gedcomnr']) . " ";
        }

        // *** Save level0 ***
        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 4));  //rtrim for CHR_
                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            if ($level[1] == 'CONC') {
                if (substr($buffer, 2, 4) == 'CONC') {
                    $processed = 1;
                    $text['text_text'] .= substr($buffer, 7);
                }
            }
            if ($level[1] == 'CONT') {
                if (substr($buffer, 2, 4) == 'CONT') {
                    $processed = 1;
                    $text['text_text'] .= "\n" . substr($buffer, 7);
                }
            }

            // *** Reunion program: source by text ***
            /*	0 @N23@ NOTE
            1 CONT text
            2 SOUR @S16@
            2 SOUR @S16@
            2 SOUR @S20@
        */
            if ($level[2] == 'SOUR') {
                //echo $buffer.'<br>';
                $this->process_sources('ref_text', 'ref_text_source', $text['text_gedcomnr'], $buffer, '2');
            }

            // *** New date/ time ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $text["new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $text["new_time"] = substr($buffer, 7);
                }
            }

            // *** Changed date/ time ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $text["changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $text["changed_time"] = substr($buffer, 7);
                }
            }

            //*******************************************************************************************
            // *** Save non-processed items ***
            // ******************************************************************************************
            // *** Skip these lines ***
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }
            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($text["text_unprocessed_tags"]) {
                    $text["text_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $text["text_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $text["text_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $text["text_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $text["text_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $text["text_unprocessed_tags"] .= '|' . $buffer;
            }
        } //end explode

        // *** Aldfaer e-mail addressses have a double @. ***
        $text['text_text'] = str_replace('@@', '@', $text['text_text']);

        // *** Save text ***
        $sql = "INSERT IGNORE INTO humo_texts SET
        text_tree_id='" . $tree_id . "',
        text_gedcomnr='" . $this->text_process($text['text_gedcomnr']) . "',
        text_text='" . $this->text_process($text['text_text']) . "',
        text_new_date='" . $this->process_date($text['new_date']) . "',
        text_new_time='" . $text['new_time'] . "',
        text_changed_date='" . $this->process_date($text['changed_date']) . "',
        text_changed_time='" . $text['changed_time'] . "'
        ";
        //echo $sql.'<br>';
        $result = $dbh->query($sql);

        // *** Save connections in seperate table (source connected to text) ***
        if ($connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $connect['kind']['1'] . $connect['sub_kind']['1'] . $connect['connect_id']['1'];
            for ($i = 1; $i <= $connect_nr; $i++) {
                $connect_order++;
                if ($check_connect != $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i];
                }

                //if($add_tree==true OR $reassign==true) { $connect['text'][$i] = $this->reassign_ged($connect['text'][$i],'N'); }
                $connect['text'][$i] = $text['text_gedcomnr'];	// *** Allready re-assigned ***

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id='" . $tree_id . "',
                connect_order='" . $connect_order . "',
                connect_kind='" . $connect['kind'][$i] . "',
                connect_sub_kind='" . $connect['sub_kind'][$i] . "',
                connect_connect_id='" . $this->text_process($connect['connect_id'][$i]) . "',
                connect_source_id='" . $this->text_process($connect['source_id'][$i]) . "',
                connect_item_id='" . $this->text_process($connect['item_id'][$i]) . "',
                connect_text='" . $this->text_process($connect['text'][$i]) . "',
                connect_page='" . $this->text_process($connect['page'][$i]) . "',
                connect_role='" . $this->text_process($connect['role'][$i]) . "',
                connect_date='" . $this->process_date($this->text_process($connect['date'][$i])) . "',
                connect_place='" . $this->text_process($connect['place'][$i]) . "'
                ";
                //echo $check_connect.' !! '.$gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        $text_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($text["text_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_text_id='" . $text_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($text["text_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }
    }

    // ************************************************************************************************
    // *** Process sources ***
    // ************************************************************************************************
    function process_source($source_array)
    {
        global $dbh, $tree_id, $tree_prefix, $not_processed, $gen_program;
        global $largest_source_ged, $largest_text_ged, $largest_repo_ged, $add_tree, $reassign;
        global $processed;

        global $connect_nr, $connect;
        $connect_nr = 0;

        // *** Needed for picture function ***
        global $event, $event_nr, $calculated_event_id, $calculated_connect_id;
        $event_nr = 0;

        $line = $source_array;
        $line2 = explode("\n", $line);
        $buffer = $line2[0];

        unset($source);  //Reset array
        $source["source_status"] = "";
        $source["source_title"] = "";
        $source["source_abbr"] = "";
        $source["source_date"] = "";
        $source["source_publ"] = "";
        $source["source_place"] = "";
        $source["source_refn"] = "";
        $source["source_auth"] = "";
        $source["source_subj"] = "";
        $source["source_item"] = "";
        $source["source_kind"] = "";
        $source["source_text"] = "";
        $source["source_repo_name"] = "";
        $source["source_repo_caln"] = "";
        $source["source_repo_page"] = "";
        $source["source_repo_gedcomnr"] = "";
        $source["source_unprocessed_tags"] = "";
        $source["new_date"] = '';
        $source["new_time"] = '';
        $source["changed_date"] = '';
        $source["changed_time"] = '';
        //$source["source_shared"]="1";

        //0 @S1@ SOUR
        $source["id"] = substr($buffer, 3, -6);
        if ($add_tree == true or $reassign == true) {
            $source["id"] = $this->reassign_ged($source["id"], 'S');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            print $source["id"] . " ";
        }

        // *** Save level0 ***
        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Save level1 ***
            if ($buffer1 == '1') {
                //$level[1]=rtrim(substr($buffer,2,4));  //rtrim for CHR_
                $level[1] = rtrim(substr($buffer, 2, 5));  //rtrim voor CHR_
                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            // ****************************************************************************************
            // *** Sources 0 @S1@ SOUR ***
            // ****************************************************************************************

            // *** Source BK ***
            //0 @S3@ SOUR
            //1 AUTH Voornaam Achternaam
            //1 TITL Test bron
            //1 ABBR Bron afkorting
            //1 PUBL 2007
            //1 TEXT Tekst test bron
            //2 CONT 2nd line
            //2 CONT 3rd line
            //1 NOTE Informatie test bron
            //2 CONT 2nd line
            //2 CONT 3rd line
            //1 REPO @R8@
            //2 CALN Lds nr.
            //2 MEDI Other

            // BK archive data is saved in repository table:
            //0 @R8@ REPO
            //1 NAME Plaats
            //1 ADDR Straat 45
            //2 CONT Plaats
            //1 PHON telefoon
            //1 FAX fax
            //1 EMAIL mail
            //1 WWW website

            // *** Restricted source ***
            if (substr($buffer, 2, 12) == 'RESN privacy') {
                $processed = 1;
                $source["source_status"] = 'restricted';
            }

            if (substr($buffer, 2, 4) == 'DATE') {
                // *** New date/ time ***
                //1 _NEW
                //2 DATE 04 AUG 2004
                if ($level[1] == '_NEW') {
                    // processed later in file
                }

                // *** Changed date/ time ***
                //1 CHAN
                //2 DATE 04 AUG 2004
                elseif ($level[1] == 'CHAN') {
                    // processed later in file
                } else {
                    $processed = 1;
                    $source["source_date"] = substr($buffer, 7);
                }
            }

            if ($level[1] == 'TITL') {
                if (substr($buffer, 2, 4) == 'TITL') {
                    $processed = 1;
                    $source["source_title"] = substr($buffer, 7);
                }
                if (substr($buffer, 2, 4) == 'CONT') {
                    $processed = 1;
                    $source["source_title"] .= $this->cont(substr($buffer, 7));
                }
                if (substr($buffer, 2, 4) == 'CONC') {
                    $processed = 1;
                    $source["source_title"] .= $this->conc(substr($buffer, 7));
                }
            }

            if (substr($buffer, 2, 4) == 'PLAC') {
                $processed = 1;
                $source["source_place"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'REFN') {
                $processed = 1;
                $source["source_refn"] = substr($buffer, 7);
            }

            if (substr($buffer, 2, 4) == 'PUBL') {
                $processed = 1;
                $source["source_publ"] = substr($buffer, 7);
            }  // BK
            if ($level[1] == 'PUBL') {
                if ($level[2] == 'CONT') {
                    $processed = 1;
                    $source["source_publ"] .= $this->cont(substr($buffer, 7));
                }
                if ($level[2] == 'CONC') {
                    $processed = 1;
                    $source["source_publ"] .= $this->conc(substr($buffer, 7));
                }
            }

            if ($level[1] == 'TEXT') {
                if (substr($buffer, 2, 4) == 'TEXT') {
                    $processed = 1;
                    $source["source_text"] = $this->merge_texts($source["source_text"], ', ', substr($buffer, 7));
                }
                if (substr($buffer, 2, 4) == 'CONC') {
                    $processed = 1;
                    $source["source_text"] .= $this->conc(substr($buffer, 7));
                }
                if (substr($buffer, 2, 4) == 'CONT') {
                    $processed = 1;
                    $source["source_text"] .= $this->cont(substr($buffer, 7));
                }
            }

            if ($level[1] == 'NOTE') {
                $source["source_text"] = $this->process_texts($source["source_text"], $buffer, '1');
            }

            if (substr($buffer, 2, 4) == 'SUBJ') {
                $processed = 1;
                $source["source_subj"] = substr($buffer, 7);
            }
            if ($level[1] == 'AUTH') {
                if (substr($buffer, 2, 4) == 'AUTH') {
                    $processed = 1;
                    $source["source_auth"] = substr($buffer, 7);
                } // BK
                if (substr($buffer, 2, 4) == 'CONC') {
                    $processed = 1;
                    $source["source_auth"] .= $this->conc(substr($buffer, 7));
                }
                if (substr($buffer, 2, 4) == 'CONT') {
                    $processed = 1;
                    $source["source_auth"] .= $this->cont(substr($buffer, 7));
                }
            }
            if (substr($buffer, 2, 4) == 'ITEM') {
                $processed = 1;
                $source["source_item"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'KIND') {
                $processed = 1;
                $source["source_kind"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'ABBR') {
                $processed = 1;
                $source["source_abbr"] = substr($buffer, 7);
            } // BK

            // *** Haza-data pictures ***
            //1 PHOTO @#Aplaatjes\beert&id.jpg jpg@
            if ($level[1] == 'PHOTO') {
                if ($buffer7 == '1 PHOTO') {
                    $processed = 1;
                    $photo = substr($buffer, 11, -6);
                    $photo = $this->humo_basename($photo);

                    $event_nr++;
                    $calculated_event_id++;
                    $event['connect_kind'][$event_nr] = 'source';
                    $event['connect_id'][$event_nr] = $source["id"];
                    $event['kind'][$event_nr] = 'picture';
                    $event['event'][$event_nr] = $photo;
                    $event['event_extra'][$event_nr] = '';
                    $event['gedcom'][$event_nr] = 'PHOTO';
                    $event['date'][$event_nr] = '';
                    $event['text'][$event_nr] = '';
                    $event['place'][$event_nr] = '';
                }
                if ($buffer6 == '2 DSCR' or $buffer6 == '2 NAME') {
                    $processed = 1;
                    $event['text'][$event_nr] = substr($buffer, 7);
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event['date'][$event_nr] = substr($buffer, 7);
                }
            }

            // *** 1 OBJE ***
            if ($level[1] == 'OBJE') $this->process_picture('source', $source["id"], 'picture', $buffer);

            // x REPO name
            // x REPO @R1@
            if (substr($buffer, 2, 4) == 'REPO') {
                $processed = 1;
                if (substr($buffer, 2, 6) == 'REPO @') {
                    $source["source_repo_gedcomnr"] = substr($buffer, 8, -1);
                    if ($add_tree == true or $reassign == true) {
                        $source["source_repo_gedcomnr"] = $this->reassign_ged($source["source_repo_gedcomnr"], 'RP');
                    }
                } else {
                    $source["source_repo_name"] = substr($buffer, 7);
                }
            }

            if (substr($buffer, 2, 4) == 'CALN') {
                $processed = 1;
                $source["source_repo_caln"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'PAGE') {
                $processed = 1;
                $source["source_repo_page"] = substr($buffer, 7);
            }

            ////*** Picture text ********************************
            //if level1='PHOT' then begin //Foto omschrijving
            //  if copy(buf,1,6)='2 DSCR' then afbeeldtekst[nrafbeelding]:=copy(buf,8,length(buf));
            //  if copy(buf,1,6)='2 NAME' then afbeeldtekst[nrafbeelding]:=copy(buf,8,length(buf)); //VOOR ANDERE GEDCOM VERSIES!
            //end;

            //if (level1<>'PHOT') and (copy(buf,3,4)='NAME') then Naam:=copy(buf,8,length(buf));
            //}

            // *** New date/ time ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $source["new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $source["new_time"] = substr($buffer, 7);
                }
            }

            // *** Changed date/ time ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $source["changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $source["changed_time"] = substr($buffer, 7);
                }
            }

            //********************************************************************************************
            // *** Save non-processed items ***
            // *******************************************************************************************
            //Skip these lines
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }
            //if ($buffer=='1 REPO'){ $processed=1; }
            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($source["source_unprocessed_tags"]) {
                    $source["source_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $source["source_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $source["source_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $source["source_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $source["source_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $source["source_unprocessed_tags"] .= '|' . $buffer;
            }
        } //end explode

        // Don't use this anymore. Because HuMo-genealogy sources will be changed when using EXPORT and IMPORT of GEDCOM ***
        // *** Generate title if there is no title in GEDCOM file (BK etc.). ***
        //if ($source["source_title"]==''){
        //	if ($source["source_auth"]){ $source["source_title"]=$source["source_auth"]; }
        //	if ($source["source_subj"]){ $source["source_title"]=$source["source_subj"]; }
        //}
        // *** Aldfaer sources: no title, no subject ***
        //if ($source["source_title"]=='' AND $source["source_subj"]=='' AND $source["source_text"]){
        //	$words = explode(" ", $source["source_text"]);
        //	// Check for multiple words in text
        //	$source["source_title"].=' '.$words[0];
        //	if (isset($words[1])){ $source["source_title"].=' '.$words[1]; }
        //	if (count($words)>2){ $source["source_title"].=' '.$words[2]; }
        //	if (count($words)>3){ $source["source_title"].=' '.$words[3]; }
        //	if (count($words)>2){ $source["source_title"].='...'; }
        //}
        // *** If there is still no title, then use ... ***
        //if ($source["source_title"]==''){ $source["source_title"]="..."; }

        if ($add_tree == true or $reassign == true) {
            if ($source["source_text"]) $source["source_text"] = $this->reassign_ged($source["source_text"], 'N');
        }

        // *** Save events ***
        if ($event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $event['kind']['1'];
            for ($i = 1; $i <= $event_nr; $i++) {
                //if ($i==1){ $check_event_kind=$event['kind'][$i]; }
                $event_order++;
                if ($check_event_kind != $event['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $event['kind'][$i];
                }

                if ($add_tree == true or $reassign == true) {
                    if ($event['text'][$i]) $event['text'][$i] = $this->reassign_ged($event['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                event_tree_id='" . $tree_id . "',
                event_order='" . $event_order . "',
                event_connect_kind='" . $event['connect_kind'][$i] . "',
                event_connect_id='" . $event['connect_id'][$i] . "',
                event_kind='" . $this->text_process($event['kind'][$i]) . "',
                event_event='" . $this->text_process($event['event'][$i]) . "',
                event_event_extra='" . $this->text_process($event['event_extra'][$i]) . "',
                event_gedcom='" . $this->text_process($event['gedcom'][$i]) . "',
                event_date='" . $this->process_date($this->text_process($event['date'][$i])) . "',
                event_text='" . $this->text_process($event['text'][$i]) . "',
                event_place='" . $this->text_process($event['place'][$i]) . "'";
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$event=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        // *** NEW july 2021: Save connections in seperate table ***
        if ($connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $connect['kind']['1'] . $connect['sub_kind']['1'] . $connect['connect_id']['1'];
            for ($i = 1; $i <= $connect_nr; $i++) {
                $connect_order++;
                if ($check_connect != $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                //if ($connect['sub_kind'][$i]=='family_address'){
                //	if (isset($connect['connect_order'][$i])) $connect_order=$connect['connect_order'][$i];
                //}

                if ($add_tree == true or $reassign == true) {
                    if ($connect['text'][$i]) $connect['text'][$i] = $this->reassign_ged($connect['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id='" . $tree_id . "',
                connect_order='" . $connect_order . "',
                connect_kind='" . $connect['kind'][$i] . "',
                connect_sub_kind='" . $connect['sub_kind'][$i] . "',
                connect_connect_id='" . $this->text_process($connect['connect_id'][$i]) . "',
                connect_source_id='" . $this->text_process($connect['source_id'][$i]) . "',
                connect_item_id='" . $this->text_process($connect['item_id'][$i]) . "',
                connect_text='" . $this->text_process($connect['text'][$i]) . "',
                connect_page='" . $this->text_process($connect['page'][$i]) . "',
                connect_role='" . $this->text_process($connect['role'][$i]) . "',
                connect_date='" . $this->process_date($this->text_process($connect['date'][$i])) . "',
                connect_place='" . $this->text_process($connect['place'][$i]) . "'
                ";
                //echo $check_connect.' !! '.$gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save sources ***
        //source_shared='".$source["source_shared"]."',
        $sql = "INSERT IGNORE INTO humo_sources SET
    source_tree_id='" . $tree_id . "',
    source_gedcomnr='" . $this->text_process($source["id"]) . "',
    source_status='" . $source["source_status"] . "',
    source_title='" . $this->text_process($source["source_title"]) . "',
    source_abbr='" . $this->text_process($source["source_abbr"]) . "',
    source_date='" . $this->process_date($this->text_process($source["source_date"])) . "',
    source_publ='" . $this->text_process($source["source_publ"]) . "',
    source_place='" . $this->text_process($source["source_place"]) . "',
    source_refn='" . $this->text_process($source["source_refn"]) . "',
    source_auth='" . $this->text_process($source["source_auth"]) . "',
    source_subj='" . $this->text_process($source["source_subj"]) . "',
    source_item='" . $this->text_process($source["source_item"]) . "',
    source_kind='" . $this->text_process($source["source_kind"]) . "',
    source_text='" . $this->text_process($source["source_text"]) . "',
    source_repo_name='" . $this->text_process($source["source_repo_name"]) . "',
    source_repo_caln='" . $this->text_process($source["source_repo_caln"]) . "',
    source_repo_page='" . $this->text_process($source["source_repo_page"]) . "',
    source_repo_gedcomnr='" . $this->text_process($source["source_repo_gedcomnr"]) . "',
    source_new_date='" . $this->process_date($source['new_date']) . "',
    source_new_time='" . $source['new_time'] . "',
    source_changed_date='" . $this->process_date($source['changed_date']) . "',
    source_changed_time='" . $source['changed_time'] . "'
    ";
        $result = $dbh->query($sql);

        $source_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($source["source_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_source_id='" . $source_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($source["source_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }
    }

    // ************************************************************************************************
    // *** Process repository ***
    // ************************************************************************************************
    function process_repository($repo_array)
    {
        global $dbh, $tree_id, $tree_prefix, $not_processed, $gen_program;
        global $largest_text_ged, $largest_repo_ged, $add_tree, $reassign;
        global $processed;

        $line = $repo_array;
        $line2 = explode("\n", $line);
        $buffer = $line2[0];

        unset($repo);  //Reset array
        $repo["repo_gedcomnr"] = "";
        $repo["repo_name"] = "";
        $repo["repo_address"] = "";
        $repo["repo_zip"] = "";
        $repo["repo_place"] = "";
        $repo["repo_phone"] = "";
        $repo["repo_date"] = "";
        $repo["repo_text"] = "";
        $repo["repo_mail"] = "";
        $repo["repo_url"] = "";
        $repo["repo_unprocessed_tags"] = "";
        $repo["repo_new_date"] = "";
        $repo["repo_new_time"] = "";
        $repo["repo_changed_date"] = "";
        $repo["repo_changed_time"] = "";

        /*
    Example of repository record in GEDCOM file:
    0 @R2@ REPO
    1 NAME Lakewood Cemetery
    1 ADDR Lakewood Cemetery
    2 CONT 3600 Hennepin Ave
    2 CONT Minneapolis, MN 55408 USA
    2 _NAME Lakewood Cemetery
    2 ADR1 3600 Hennepin Ave
    2 CITY Minneapolis
    2 STAE MN
    2 POST 55408
    2 CTRY USA
    2 MAP
    3 LATI N44.9327769444444
    3 LONG W93.2994438888889
    2 NOTE They have birth records dating from July 1, 1907.  They hav
    3 CONC e death records from July 1, 1907.  They have marriage reco
    3 CONC rds from January 1, 1968.
    1 PHON (612) 822-0575 - fax
    1 _EMAIL fhl@ldschurch.org
    1 _URL http://www.familysearch.org
    */

        //0 @R1@ REPO
        $repo["repo_gedcomnr"] = substr($buffer, 3, -6);
        if ($add_tree == true or $reassign == true) {
            $repo["repo_gedcomnr"] = $this->reassign_ged($repo["repo_gedcomnr"], 'RP');
        }
        //if (isset($_POST['show_gedcomnumbers'])){ print substr($repo["repo_gedcomnr"],0,-1)." "; }
        if (isset($_POST['show_gedcomnumbers'])) {
            print $repo["repo_gedcomnr"] . " ";
        }

        // *** Save level0 ***
        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 4));  //rtrim for CHR_
                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            if (substr($buffer, 2, 4) == 'NAME') {
                $processed = 1;
                $repo["repo_name"] = substr($buffer, 7);
            }

            if ($level[1] == 'ADDR') {
                if (substr($buffer, 2, 4) == 'ADDR') {
                    $processed = 1;
                    $repo["repo_address"] = substr($buffer, 7);
                }
                if (substr($buffer, 2, 4) == 'CONC') {
                    $processed = 1;
                    $repo["repo_address"] .= $this->conc(substr($buffer, 7));
                }
                if (substr($buffer, 2, 4) == 'CONT') {
                    $processed = 1;
                    $repo["repo_address"] .= $this->cont(substr($buffer, 7));
                }
            }

            if (substr($buffer, 2, 4) == 'POST') {
                $processed = 1;
                $repo["repo_zip"] = substr($buffer, 7);
            }

            if (substr($buffer, 2, 4) == 'CITY') {
                $processed = 1;
                $repo["repo_place"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'STAE') {
                if ($repo["repo_place"]) {
                    $repo["repo_place"] .= ', ';
                }
                $processed = 1;
                $repo["repo_place"] .= substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'CTRY') {
                if ($repo["repo_place"]) {
                    $repo["repo_place"] .= ', ';
                }
                $processed = 1;
                $repo["repo_place"] .= substr($buffer, 7);
            }

            // 1 PHON +1-801-240-2331 (information)
            // 1 PHON +1-801-240-1278 (gifts & donations)
            // 1 PHON +1-801-240-2584 (support)
            if (substr($buffer, 2, 4) == 'PHON') {
                $processed = 1;
                $repo["repo_phone"] = substr($buffer, 7);
            }

            if (substr($buffer, 2, 4) == 'DATE') {
                $processed = 1;
                $repo["repo_date"] = substr($buffer, 7);
            }

            //SOURCE

            //2 NOTE They have birth records dating from July 1, 1907.  They hav
            //3 CONC e death records from July 1, 1907.  They have marriage reco
            //3 CONC rds from January 1, 1968.
            if ($level[2] == 'NOTE') {
                $repo["repo_text"] = $this->process_texts($repo["repo_text"], $buffer, '2');
            }
            if ($level[1] == 'NOTE') {
                $repo["repo_text"] = $this->process_texts($repo["repo_text"], $buffer, '1');
            }

            // *** Process photo bij repository ***
            //
            //

            // 1 _EMAIL fhl@ldschurch.org
            if (substr($buffer, 2, 6) == '_EMAIL') {
                $processed = 1;
                $repo["repo_email"] = substr($buffer, 7);
            }

            // 1 _URL http://www.familysearch.org
            if (substr($buffer, 2, 4) == '_URL') {
                $processed = 1;
                $repo["repo_email"] = substr($buffer, 7);
            }

            // *** New date/ time ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $repo["repo_new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $repo["repo_new_time"] = substr($buffer, 7);
                }
            }

            // *** Changed date/ time ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $repo["repo_changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $repo["repo_changed_time"] = substr($buffer, 7);
                }
            }

            //********************************************************************************************
            // *** Save non-processed items ***
            // *******************************************************************************************
            //Skip these lines
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }
            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($repo["repo_unprocessed_tags"]) {
                    $repo["repo_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $repo["repo_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $repo["repo_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $repo["repo_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $repo["repo_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $repo["repo_unprocessed_tags"] .= '|' . $buffer;
            }
        } //end explode

        if ($add_tree == true or $reassign == true) {
            if ($repo["repo_text"]) $repo["repo_text"] = $this->reassign_ged($repo["repo_text"], 'N');
        }

        // *** Save repository ***
        $sql = "INSERT IGNORE INTO humo_repositories SET
    repo_tree_id='" . $tree_id . "',
    repo_gedcomnr='" . $this->text_process($repo["repo_gedcomnr"]) . "',
    repo_name='" . $this->text_process($repo["repo_name"]) . "',
    repo_address='" . $this->text_process($repo["repo_address"]) . "',
    repo_zip='" . $this->text_process($repo["repo_zip"]) . "',
    repo_place='" . $this->text_process($repo["repo_place"]) . "',
    repo_phone='" . $this->text_process($repo["repo_phone"]) . "',
    repo_date='" . $this->process_date($this->text_process($repo["repo_date"])) . "',
    repo_text='" . $this->text_process($repo["repo_text"]) . "',
    repo_mail='" . $this->text_process($repo["repo_mail"]) . "',
    repo_url='" . $this->text_process($repo["repo_url"]) . "',
    repo_new_date='" . $this->process_date($repo['repo_new_date']) . "',
    repo_new_time='" . $repo['repo_new_time'] . "',
    repo_changed_date='" . $this->process_date($repo['repo_changed_date']) . "',
    repo_changed_time='" . $repo['repo_changed_time'] . "'
    ";
        $result = $dbh->query($sql);

        $repo_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($repo["repo_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_repo_id='" . $repo_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($repo["repo_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }
    }

    // ************************************************************************************************
    // *** Process (shared) addresses ***
    // ************************************************************************************************
    function process_address($line)
    {
        global $tree_id, $tree_prefix, $not_processed, $gen_program;
        global $largest_text_ged, $largest_address_ged, $add_tree, $reassign;
        global $processed, $dbh;

        global $connect_nr, $connect;
        $connect_nr = 0;

        $line2 = explode("\n", $line);
        $buffer = $line2[0];

        // *********************************************************************************************
        // *** Addressses Haza-Plus & HuMo-genealogy shared addresses ***
        // *********************************************************************************************
        //0 @R1@ RESI
        //1 ADDR Lange Houtstraat 100
        //1 ZIP 1234 AB
        //1 PLAC Amsterdam
        //1 NOTE Bla bla.
        //1 PHOTO @#Aplaatjes\w-brinke.jpg jpg@
        //2 NAME Brinke

        unset($address);  //Reset array
        $address["address_shared"] = "1";
        $address["address"] = "";
        $address["address_zip"] = "";
        $address["address_place"] = "";
        $address["address_phone"] = "";
        $address["address_text"] = "";
        $address["address_gedcomnr"] = substr($buffer, 3, -6);
        if ($add_tree == true or $reassign == true) {
            $address["address_gedcomnr"] = $this->reassign_ged($address["address_gedcomnr"], 'R');
        }
        $address["address_unprocessed_tags"] = "";
        $address["new_date"] = '';
        $address["new_time"] = '';
        $address["changed_date"] = '';
        $address["changed_time"] = '';

        //if (isset($_POST['show_gedcomnumbers'])){ print substr($address["id"],0,-1)." "; }
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $address["address_gedcomnr"] . " ";
        }

        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 4));  //rtrim for CHR_
                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            if (substr($buffer, 2, 4) == 'ADDR') {
                $processed = 1;
                $address["address"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'ZIP ') {
                $processed = 1;
                $address["address_zip"] = substr($buffer, 6);
            }  //Voor BK
            //if (substr($buffer,2,4)=='DATE '){ $processed=1; $address["datum"]=substr($buffer,7); }
            if (substr($buffer, 2, 4) == 'PLAC') {
                $processed = 1;
                $address["address_place"] = substr($buffer, 7);
            }
            if (substr($buffer, 2, 4) == 'PHON') {
                $processed = 1;
                $address["address_phone"] = substr($buffer, 7);
            }

            // *** Text by address ***
            $address["address_text"] = $this->process_texts($address["address_text"], $buffer, '1');

            // *** Source by shared address ***
            if ($level[2] == 'SOUR') {
                $this->process_sources('address', 'address_source', $address["address_gedcomnr"], $buffer, '2');
            }

            //1 PHOTO @#APLAATJES\AKTEMONS.GIF GIF@
            //if ($buffer7=='1 PHOTO'){
            //	$processed=1; $address["address_photo"]=$this->merge_texts($address["address_photo"], ';', substr($buffer,11,-5)); }

            // *** New date/ time ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $address["new_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $address["new_time"] = substr($buffer, 7);
                }
            }

            // *** Changed date/ time ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $address["changed_date"] = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $address["changed_time"] = substr($buffer, 7);
                }
            }

            //********************************************************************************************
            // *** Save non-processed items ***
            // *******************************************************************************************
            //Skip these lines
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }

            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($address["address_unprocessed_tags"]) {
                    $address["address_unprocessed_tags"] .= "<br>\n";
                }
                if ($level[1]) {
                    $address["address_unprocessed_tags"] .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $address["address_unprocessed_tags"] .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $address["address_unprocessed_tags"] .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $address["address_unprocessed_tags"] .= '|3 ' . $level[3];
                }
                $address["address_unprocessed_tags"] .= '|' . $buffer;
            }
        } //end explode

        // *** NEW april 2022: Save connections in seperate table ***
        if ($connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $connect['kind']['1'] . $connect['sub_kind']['1'] . $connect['connect_id']['1'];
            for ($i = 1; $i <= $connect_nr; $i++) {
                $connect_order++;
                if ($check_connect != $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $connect['kind'][$i] . $connect['sub_kind'][$i] . $connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                //if ($connect['sub_kind'][$i]=='family_address'){
                //	if (isset($connect['connect_order'][$i])) $connect_order=$connect['connect_order'][$i];
                //}

                if ($add_tree == true or $reassign == true) {
                    if ($connect['text'][$i]) $connect['text'][$i] = $this->reassign_ged($connect['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id='" . $tree_id . "',
                connect_order='" . $connect_order . "',
                connect_kind='" . $connect['kind'][$i] . "',
                connect_sub_kind='" . $connect['sub_kind'][$i] . "',
                connect_connect_id='" . $this->text_process($connect['connect_id'][$i]) . "',
                connect_source_id='" . $this->text_process($connect['source_id'][$i]) . "',
                connect_item_id='" . $this->text_process($connect['item_id'][$i]) . "',
                connect_text='" . $this->text_process($connect['text'][$i]) . "',
                connect_page='" . $this->text_process($connect['page'][$i]) . "',
                connect_role='" . $this->text_process($connect['role'][$i]) . "',
                connect_date='" . $this->process_date($this->text_process($connect['date'][$i])) . "',
                connect_place='" . $this->text_process($connect['place'][$i]) . "'
                ";
                //echo $check_connect.' !! '.$gebeurtsql.'<br>';
                $result = $dbh->query($gebeurtsql);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($event);
            //$connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save addressses ***
        $sql = "INSERT IGNORE INTO humo_addresses SET
    address_tree_id='" . $tree_id . "',
    address_gedcomnr='" . $this->text_process($address["address_gedcomnr"]) . "',
    address_shared='" . $this->text_process($address["address_shared"]) . "',
    address_address='" . $this->text_process($address["address"]) . "',
    address_zip='" . $this->text_process($address["address_zip"]) . "',
    address_place='" . $this->text_process($address["address_place"]) . "',
    address_phone='" . $this->text_process($address["address_phone"]) . "',
    address_text='" . $this->text_process($address["address_text"]) . "',
    address_new_date='" . $this->process_date($address['new_date']) . "',
    address_new_time='" . $address['new_time'] . "',
    address_changed_date='" . $this->process_date($address['changed_date']) . "',
    address_changed_time='" . $address['changed_time'] . "'";
        $result = $dbh->query($sql);

        $address_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($address["address_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_address_id='" . $address_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($address["address_unprocessed_tags"]) . "'";
            $result = $dbh->query($sql);
        }
    }


    // ************************************************************************************************
    // *** Process objects ***
    // ************************************************************************************************
    function process_object($object_array)
    {
        global $dbh, $tree_id, $tree_prefix, $not_processed, $gen_program, $largest_object_ged;
        global $add_tree, $reassign, $processed;

        $line = $object_array;
        $line2 = explode("\n", $line);
        $buffer = $line2[0];

        // *********************************************************************************************
        // *** Objects ***
        // *********************************************************************************************
        // 0 @O123@ OBJE
        // 1 FORM jpeg
        // 1 TITL Fred Smith
        // 1 FILE fred_smith.jpg
        // 1 CHAN
        // 2 DATE 4 JUN 2010
        // 3 TIME 09:19:50

        $event['gedcomnr'] = substr($buffer, 3, -6);
        if ($add_tree == true or $reassign == true) {
            $event['gedcomnr'] = $this->reassign_ged($event['gedcomnr'], 'O');
        }
        $event['event'] = '';
        $event['event_extra'] = '';
        $event['date'] = '';
        $event['place'] = '';
        $event['text'] = ''; // $event['source']='';
        $event_unprocessed_tags = "";
        $event_new_date = '';
        $event_new_time = '';
        $event_changed_date = '';
        $event_changed_time = '';

        if (isset($_POST['show_gedcomnumbers'])) {
            print $event['gedcomnr'] . " ";
        }

        $level[0] = substr($buffer, 2);
        $level[1] = "";
        $level[2] = "";
        $level[3] = "";
        $level[4] = "";

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $processed = 0;
            $buffer = $line2[$z];
            $buffer = rtrim($buffer, "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$buffer."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($gen_program == 'PRO-GEN') {
                $buffer = ltrim($buffer, " ");
            }

            $buffer1 = substr($buffer, 0, 1);
            $buffer5 = substr($buffer, 0, 5);
            $buffer6 = substr($buffer, 0, 6);
            $buffer7 = substr($buffer, 0, 7);
            $buffer8 = substr($buffer, 0, 8);

            // *** Save level1 ***
            if ($buffer1 == '1') {
                $level[1] = rtrim(substr($buffer, 2, 4));  //rtrim for CHR_
                $event_status = '';
                $event_start = '1';
                $level[2] = "";
                $level[3] = "";
                $level[4] = "";
            }
            // *** Save level2 ***
            elseif ($buffer1 == '2') {
                $level[2] = substr($buffer, 2, 4);
                $level[3] = '';
                $level[4] = '';
            }
            // *** Save level3 ***
            elseif ($buffer1 == '3') {
                $level[3] = substr($buffer, 2, 4);
                $level[4] = '';
            }
            // *** Save level4 ***
            elseif ($buffer1 == '4') {
                $level[4] = substr($buffer, 2, 4);
            }

            if ($buffer6 == '1 FILE') {
                $processed = 1;
                $photo = substr($buffer, 7);
                // *** Aldfaer sometimes uses: 2 FILE \bestand.jpg ***
                $photo = $this->humo_basename($photo);
                $event['event'] = $photo;
            }
            //if ($buffer6=='1 TITL'){
            if ($buffer6 == '1 TITL' or $buffer6 == '2 TITL') {
                $processed = 1;
                $event['text'] = substr($buffer, 7);
            }

            // *** New date/ time ***
            //1 _NEW
            //2 DATE 04 AUG 2004
            if ($level[1] == '_NEW') {
                if ($buffer6 == '1 _NEW') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event_new_date = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $event_new_time = substr($buffer, 7);
                }
            }

            // *** Changed date/ time ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($level[1] == 'CHAN') {
                if ($buffer6 == '1 CHAN') {
                    $processed = 1;
                }
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $event_changed_date = substr($buffer, 7);
                }
                if ($buffer6 == '3 TIME') {
                    $processed = 1;
                    $event_changed_time = substr($buffer, 7);
                }
            }

            //********************************************************************************************
            // *** Save non-processed items ***
            // *******************************************************************************************
            //Skip these lines
            if ($buffer == '0 TRLR') {
                $processed = 1;
            }

            if ($processed == 0) {
                if (isset($_POST['check_processed'])) {
                    $not_processed[] = '0 ' . $level[0] . '</td><td>1 ' . $level[1] . '<br></td><td>2 ' . $level[2] . '<br></td><td>3 ' . $level[3] . '<br></td><td>' . $buffer;
                    //$this->non_processed_items($buffer);
                }

                if ($event_unprocessed_tags) {
                    $event_unprocessed_tags .= "<br>\n";
                }
                if ($level[1]) {
                    $event_unprocessed_tags .= '0 ' . $level[0];
                }
                if ($level[2]) {
                    $event_unprocessed_tags .= '|1 ' . $level[1];
                }
                if ($level[3]) {
                    $event_unprocessed_tags .= '|2 ' . $level[2];
                }
                if ($level[4]) {
                    $event_unprocessed_tags .= '|3 ' . $level[3];
                }
                $event_unprocessed_tags .= '|' . $buffer;
            }
        } //end explode

        if ($add_tree == true or $reassign == true) {
            $event['text'] = $this->reassign_ged($event['text'], 'O');
        }
        // *** Save object ***
        $eventsql = "INSERT IGNORE INTO humo_events SET
        event_tree_id='" . $tree_id . "',
        event_gedcomnr='" . $event['gedcomnr'] . "',
        event_order='1',
        event_connect_kind='',
        event_connect_id='',
        event_kind='object',
        event_event='" . $this->text_process($event['event']) . "',
        event_event_extra='" . $this->text_process($event['event_extra']) . "',
        event_gedcom='OBJE',
        event_date='" . $this->process_date($this->text_process($event['date'])) . "',
        event_place='" . $this->text_process($event['place']) . "',
        event_text='" . $this->text_process($event['text']) . "',
        event_new_date='" . $this->process_date($event_new_date) . "',
        event_new_time='" . $event_new_time . "',
        event_changed_date='" . $this->process_date($event_changed_date) . "',
        event_changed_time='" . $event_changed_time . "'";
        //echo '<br>'.$eventsql.'<br>';
        $result = $dbh->query($eventsql);

        $event_id = $dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($event_unprocessed_tags) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_event_id='" . $event_id . "',
            tag_tree_id='" . $tree_id . "',
            tag_tag='" . $this->text_process($event_unprocessed_tags) . "'";
            $result = $dbh->query($sql);
        }
    }

    // *****************
    // *** Functions ***
    // *****************

    /*
function non_processed_items($buffer){
    global $not_processed, $level;

    // *** Not processed items for list by reading of GEDCOM ***
    $not_processed_tmp='0 '.$level[0].'</td><td>1 '.$level[1].'<br></td><td>';
    if ($level[2]){ $not_processed_tmp.="2 $level[2]"; }
    $not_processed_tmp.="<br></td><td>";
    if ($level[3]){ $not_processed_tmp.="3 $level[3]"; }
    $not_processed_tmp.="<br></td><td>$buffer";
    $not_processed[]=$not_processed_tmp;

    //if ($process){ $process.="<br>\n"; }
    //if ($level[1]){ $process.='0 '.$level[0]; }
    //if ($level[2]){ $process.='|1 '.$level[1]; }
    //if ($level[3]){ $process.='|2 '.$level[2]; }
    //if ($level[4]){ $process.='|3 '.$level[3]; }
    //$process.='|'.$buffer;
    //return $process;
}
*/

    function text_process($text, $long_text = false)
    {
        global $dbh;
        //if ($long_text==true){
        //	$text = str_replace("\r\n", "\n", $text);
        //}
        //$text=safe_text_db($text);
        //return $text;

        $return_text = '';
        if ($text) {
            $return_text = $dbh->quote($text);
        }
        // PDO "quote" escapes, BUT also encloses in single quotes. 
        // In all HuMo-genealogy scripts the single quotes are already coded ( "...some-parameter = '".$var."'")  so we take them off:
        $return_text = substr($return_text, 1, -1); // remove quotes from beginning and end
        return $return_text;
    }

    function process_date($date)
    {
        // *** Convert 2 DATE Bef 1909 to: 2 DATE BEF 1909 ***
        $date = strtoupper($date);

        // in case years under 1000 are given as 0945, make it 945
        $date = str_replace(" 0", " ", $date); //gets rid of "bet 2 may 0954 AND jun 0951" and "5 may 0985"
        //if(substr($date,-4,1)=="0") {
        //	 // if there is still a "0" this means we had the year by itself "0985" with nothing before it
        if (substr($date, -4, 1) == "0" and strlen($date) == 4) { // if there is still a "0" this means we had the year by itself "0985" with nothing before it
            // the strlen code was added to prevent that double dates with a 0 in position 4th from the end, will be erroneously changed  (1960/61 --> /61)
            $date = substr($date, -3, 3);
        }
        return $date;
    }

    // *** Merge function: text1, merge character, text2 ***
    function merge_texts($text1, $merge, $text2)
    {
        if ($text1) {
            $text1 = $text1 . $merge . $text2;
        } else {
            $text1 = $text2;
        }
        return $text1;
    }

    // CONT
    function cont($text1)
    {
        //$text="<br>\n".$text1;
        $text = "\n" . $text1;
        return $text;
    }

    // CONC, Some programs need an extra space after CONC!
    // PRO-GEN 3.0b-p10: add extra space.
    // PRO-GEN 3.22: no extra space.
    function conc($text1)
    {
        global $gen_program, $gen_program_version;
        $spacer = '';
        if ($gen_program == 'HuMo-gen' or $gen_program == 'HuMo-genealogy') {
            $spacer = ' ';
        } elseif ($gen_program == 'Haza-Data') {
            $spacer = ' ';
        } elseif ($gen_program == 'PRO-GEN' and substr($gen_program_version, 0, 3) == '3.0') {
            $spacer = ' ';
        } elseif ($gen_program == 'Family Tree Legends') {
            $spacer = ' ';
        }
        $text = $spacer . $text1;
        return $text;
    }

    // *** Process texts ***
    // 1 NOTE Information, text, etc.
    // 2 CONT 2nd line.
    // 2 CONT 3rd line
    // 2 CONC remaining text of 3rd line.
    function process_texts($text, $buffer, $number)
    {
        global $processed;
        $buffer6 = substr($buffer, 0, 6);
        if ($buffer6 == ($number) . ' NOTE') {
            // *** Seperator for multiple texts ***
            if ($text != '') {
                $text .= "|";
            }
            $processed = 1;
            $text .= substr($buffer, 7);
        } elseif ($buffer6 == ($number) . ' TITL') {
            $processed = 1;
            $text .= substr($buffer, 7);
        }	// 1 OBJE -> 2 TITL 
        elseif ($buffer6 == ($number + 1) . ' CONT') {
            $processed = 1;
            $text .= $this->cont(substr($buffer, 7));
        } elseif ($buffer6 == ($number + 1) . ' CONC') {
            $processed = 1;
            $text .= $this->conc(substr($buffer, 7));
        }
        return $text;
    }

    function humo_basename($photo)
    {
        global $humo_option;

        // *** Default: only read file name for example: picture.jpg ***
        if ($humo_option["gedcom_process_pict_path"] == 'file_name') {
            // *** Basename is locale aware! If basename is used, also set "setlocale" ***
            setlocale(LC_ALL, 'en_US.UTF-8');
            $photo = basename($photo);

            // *** Because basename isn't working by all providers, extra code to remove a path ***
            if (strpos(' ' . $photo, "\\") > 0) {
                $photo = substr(strrchr(' ' . $photo, "\\"), 1);
            }
        } else {
            // *** Read full picture path ***
            // *** It's probably better to replace \ by / for use at Linux webservers ***
            $photo = str_replace('\\', '/', $photo);
        }

        // **** SteveP modded 15 Jan 2022 :- Added 1 Line of Code to remove Drive designator if present ***
        // **** example Drive designator format x:/ where x can be A to Z or a to z ***
        if (strpos($photo, ":/") == 1) $photo = substr($photo, 3, strlen($photo) - 3);

        return $photo;
    }

    // *** Quality ***
    // 0 = Unreliable evidence or estimated data 
    // 1 = Questionable reliability of evidence (interviews, census, oral genealogies, or potential for bias for example, an autobiography) 
    // 2 = Secondary evidence, data officially recorded sometime after event 
    // 3 = Direct and primary evidence used, or by dominance of the evidence
    // Example:
    // 2 QUAY 0
    function process_quality($buffer)
    {
        global $gen_program;
        $text = substr($buffer, -1);
        // Ancestry uses 1 - 4 in stead of 0 - 3, adjust numbers:
        if ($gen_program == "Ancestry.com Family Trees") $text--;
        return $text;
    }

    function reassign_ged($gednr, $letter)
    {
        global $new_gednum, $reassign_array;

        // *** Notes ***
        // 1 NOTE @N8647@	reference to note
        // 0 @N8647@ NOTE	note

        // 1 NOTE Text		skip text without reference.

        // *** GEDCOM numbering must be numeric ***
        //if (is_numeric(substr($gednr,1)) AND $letter!='N' OR ($letter=='N' AND substr($gednr,0,2)=='@N')) {
        if (is_numeric(substr($gednr, 1))) {
            //echo $gednr.' '.$letter.'<br>';
            $newged = '';
            $tempged = '';
            if (!isset($reassign_array[$gednr])) {
                $reassign_array[$gednr] = $new_gednum[$letter];
                $new_gednum[$letter]++;
            }
            $tempged = $reassign_array[$gednr];
            if ($letter == "RP") {
                $letter = "R";
            } // after using repo array "RP" above (to differentiate from "R" for addresses) we change it back to "R"
            $newged = $letter . $tempged;
            //if(substr($gednr,0,1)=='@') { $newged = '@'.$newged.'@'; } // if the gedcomnumber was @N23@ it has to be returned as such with the adjusted number
            //if ($letter=='N') echo $gednr.'-'.$newged.'<br>';
            //if ($letter=='N') return '@'.$newged.'@';
            return $newged;
        } else {
            //	//if ($letter=='N') echo $gednr.'-'.$newged.'!<br>';
            //	//if ($letter=='N') return '@'.$newged.'@';
            return $gednr;
        }
    }

    // *** Process place ***
    function process_place($place)
    {
        // *** Solve bug in Haza-data GEDCOM export, replace: Adelaide ,Australië by: Adelaide, Australië *
        $place = str_replace(" ,", ", ", $place);
        return $place;
    }

    // *** Process places ***
    function process_places($map_place, $buffer)
    {
        global $geocode_nr, $geocode_plac, $geocode_lati, $geocode_long, $geocode_type, $processed, $level;

        // 2 PLAC Cleveland, Ohio, USA
        // 3 MAP
        // 4 LATI N41.500347
        // 4 LONG W81.66687
        if (substr($level[3], 0, 3) == 'MAP') {
            $buffer6 = substr($buffer, 0, 6);
            //if ($buffer6==$number.' PLAC'){ $processed=1; $place=substr($buffer, 7); }

            if (substr($buffer, 0, 5) == '3 MAP') {
                $processed = 1;
                $geocode_nr++;
                $geocode_plac[$geocode_nr] = $map_place;
                $geocode_type[$geocode_nr] = ""; // needed to enter location_status as "humo3_death" later
                if ($level[1] == 'BIRT') {
                    $geocode_type[$geocode_nr] = "birth";
                } elseif ($level[1] == 'BAPT') {
                    $geocode_type[$geocode_nr] = "bapt";
                } elseif ($level[1] == 'DEAT') {
                    $geocode_type[$geocode_nr] = "death";
                } elseif ($level[1] == 'BURI') {
                    $geocode_type[$geocode_nr] = "buried";
                }
            } elseif ($buffer6 == '4 LATI') {
                $processed = 1;
                $geocode = (substr($buffer, 7));
                if (substr($geocode, 0, 1) == 'S') {
                    $geocode = '-' . substr($geocode, 1);
                } else {
                    $geocode = substr($geocode, 1);
                }
                $geocode_lati[$geocode_nr] = $geocode;
            } elseif ($buffer6 == '4 LONG') {
                $processed = 1;
                $geocode = (substr($buffer, 7));
                if (substr($geocode, 0, 1) == 'W') {
                    $geocode = '-' . substr($geocode, 1);
                } else {
                    $geocode = substr($geocode, 1);
                }
                $geocode_long[$geocode_nr] = $geocode;
            }
        }
    }



    // *** Process addresses by person and relation ***
    function process_addresses($connect_kind, $connect_sub_kind, $connect_id, $buffer)
    {
        global $gen_program, $processed, $pers_place_index;
        global $level, $add_tree, $reassign;
        global $connect, $connect_nr, $calculated_connect_id;
        global $nraddress2, $address_order;
        global $address_array;
        //echo $connect_kind.' '.$connect_sub_kind.' '.$connect_id.' '.$buffer.'<br>';
        $buffer6 = substr($buffer, 0, 6);

        // *** Living place ***
        //Haza-Data 7.2
        //1 ADDR Ridderkerk
        //1 ADDR Slikkerveer
        //1 ADDR Alkmaar
        //1 ADDR Heerhugowaard
        if ($buffer6 == '1 ADDR') {
            $address_gedcomnr = 'R' . $_SESSION['new_address_gedcomnr'];
            if ($add_tree == true or $reassign == true) {
                $address_gedcomnr = $this->reassign_ged('R' . $_SESSION['new_address_gedcomnr'], 'R');
            }
            $_SESSION['address_gedcomnr'] = $address_gedcomnr;
            $nraddress2++;
            $address_array["gedcomnr"][$nraddress2] = $address_gedcomnr;
            $address_array["place"][$nraddress2] = "";
            $address_array["address"][$nraddress2] = "";
            $address_array["zip"][$nraddress2] = "";
            $address_array["phone"][$nraddress2] = "";
            $address_array["date"][$nraddress2] = "";
            $address_array["text"][$nraddress2] = "";
            $processed = 1;
            $address_array["place"][$nraddress2] = substr($buffer, 7);
            $pers_place_index = substr($buffer, 7);

            // *** Used for general numbering of connections ***
            $connect_nr++;
            $calculated_connect_id++;
            $address_order++;

            // *** Seperate numbering, because there can be sources by a address ***
            $address_connect_nr = $connect_nr;

            $connect['kind'][$connect_nr] = $connect_kind;
            $connect['sub_kind'][$connect_nr] = $connect_sub_kind;
            $connect['connect_id'][$connect_nr] = $connect_id;
            $connect['connect_order'][$connect_nr] = $address_order;

            $connect['source_id'][$connect_nr] = '';
            $connect['text'][$connect_nr] = '';
            $connect['item_id'][$connect_nr] = $address_gedcomnr;
            $connect['quality'][$connect_nr] = '';
            $connect['date'][$connect_nr] = '';
            $connect['place'][$connect_nr] = '';
            $connect['page'][$connect_nr] = '';
            $connect['role'][$connect_nr] = '';
            $connect['date'][$connect_nr] = '';

            // *** Next GEDCOM number ***
            $_SESSION['new_address_gedcomnr'] = $_SESSION['new_address_gedcomnr'] + 1;
        }

        // *** Address ***
        if ($level[1] == 'RESI') {
            // *** Living place Haza-data plus etc. ***
            //*** Haza-data plus link to address ***
            //1 ADDR de Rijp
            //1 RESI @R34@
            //2 DATE 1651
            //2 ROLE landbouwer op

            // *** Use connection table to store addresses ***
            // *** Check for address links (shared addresses), @R34@ links ***
            if (substr($level['1a'], 0, 8) == '1 RESI @') {
                if ($buffer6 == '1 RESI') {
                    $processed = 1;

                    // *** Used for general numbering of connections ***
                    $connect_nr++;
                    $calculated_connect_id++;
                    $address_order++;

                    // *** Seperate numbering, because there can be sources by a address ***
                    $address_connect_nr = $connect_nr;

                    $connect['kind'][$connect_nr] = $connect_kind;
                    $connect['sub_kind'][$connect_nr] = $connect_sub_kind;
                    $connect['connect_id'][$connect_nr] = $connect_id;
                    $connect['connect_order'][$connect_nr] = $address_order;

                    $connect['source_id'][$connect_nr] = '';
                    $connect['text'][$connect_nr] = '';
                    // *** Save place GEDCOM number in connect_item_id field ***
                    $connect['item_id'][$connect_nr] = substr($buffer, 8, -1);
                    if ($add_tree == true or $reassign == true) {
                        $connect['item_id'][$connect_nr] = $this->reassign_ged($connect['item_id'][$connect_nr], 'R');
                    }
                    $_SESSION['address_gedcomnr'] = $connect['item_id'][$connect_nr];

                    $connect['quality'][$connect_nr] = '';
                    $connect['date'][$connect_nr] = '';
                    $connect['place'][$connect_nr] = '';
                    $connect['page'][$connect_nr] = '';
                    $connect['role'][$connect_nr] = '';
                    $connect['date'][$connect_nr] = '';
                }

                // *** Address role ***
                if ($buffer6 == '2 ROLE') {
                    $processed = 1;
                    $connect['role'][$connect_nr] = substr($buffer, 7);
                }
                // *** Address date ***
                if ($buffer6 == '2 DATE') {
                    $processed = 1;
                    $connect['date'][$connect_nr] = substr($buffer, 7);
                }
                // *** Extra text by address in HuMo-genealogy ***
                // 2 DATA
                // 3 TEXT text ..... => Extra text by address...
                // 4 CONT ..........
                if ($buffer6 == '2 DATA') {
                    $processed = 1; //$connect['text'][$connect_nr]=substr($buffer, 7);
                }
                if ($buffer6 == '3 TEXT') {
                    if ($connect['text'][$connect_nr]) {
                        $connect['text'][$connect_nr] .= '<br>';
                    }
                    $processed = 1;
                    $connect['text'][$connect_nr] .= substr($buffer, 7);
                }
                if ($buffer6 == '4 CONT') {
                    $processed = 1;
                    $connect['text'][$connect_nr] .= $this->cont(substr($buffer, 7));
                }
                if ($buffer6 == '4 CONC') {
                    $processed = 1;
                    $connect['text'][$connect_nr] .= $this->conc(substr($buffer, 7));
                }

                if ($level[2] == 'SOUR') {
                    if ($connect_kind == 'person') {
                        $this->process_sources('person', 'pers_address_connect_source', $calculated_connect_id, $buffer, '2');
                    } else {
                        $this->process_sources('family', 'fam_address_connect_source', $calculated_connect_id, $buffer, '2');
                    }
                }
            }

            // BK
            //1 RESI
            //2 DATE 01 DEC 1931
            //2 PLAC AMSTERDAM-AMSTELDIJK 93/1
            //2 NOTE Tijdens verloving.
            //1 RESI
            //2 DATE 10 JUL 1934
            //2 PLAC AMSTERDAM-AMSTELDIJK 93/1
            //2 NOTE Tijdens ondertrouw.
            //if ($buffer6=='1 RESI' AND $gen_program!='Haza-Data' AND $gen_program!='HuMo-genealogy'){
            if ($buffer6 == '1 RESI' and substr($buffer, 7, 1) != '@') {
                $processed = 1;
                $nraddress2++;

                $address_gedcomnr = 'R' . $_SESSION['new_address_gedcomnr'];
                if ($add_tree == true or $reassign == true) {
                    $address_gedcomnr = $this->reassign_ged('R' . $_SESSION['new_address_gedcomnr'], 'R');
                }
                $_SESSION['address_gedcomnr'] = $address_gedcomnr;
                $address_array["gedcomnr"][$nraddress2] = $address_gedcomnr;
                $address_array["place"][$nraddress2] = "";
                $address_array["address"][$nraddress2] = "";
                $address_array["zip"][$nraddress2] = "";
                $address_array["phone"][$nraddress2] = "";
                $address_array["date"][$nraddress2] = "";
                $address_array["text"][$nraddress2] = "";
                //$address_source[$nraddress2]="";

                // FTM:
                // 1 RESI Owner of the house, 6 
                // 2 CONC Lane.
                if (substr($buffer, 7)) {
                    $address_array["address"][$nraddress2] = substr($buffer, 7);
                }

                // *** Used for general numbering of connections ***
                $connect_nr++;
                $calculated_connect_id++;
                $address_order++;

                // *** Seperate numbering, because there can be sources by an address ***
                $address_connect_nr = $connect_nr;

                $connect['kind'][$connect_nr] = $connect_kind;
                $connect['sub_kind'][$connect_nr] = $connect_sub_kind;
                $connect['connect_id'][$connect_nr] = $connect_id;
                $connect['connect_order'][$connect_nr] = $address_order;

                $connect['source_id'][$connect_nr] = '';
                $connect['text'][$connect_nr] = '';
                $connect['item_id'][$connect_nr] = $address_gedcomnr;
                $connect['quality'][$connect_nr] = '';
                $connect['date'][$connect_nr] = '';
                $connect['place'][$connect_nr] = '';
                $connect['page'][$connect_nr] = '';
                $connect['role'][$connect_nr] = '';
                $connect['date'][$connect_nr] = '';

                // *** Next GEDCOM number ***
                $_SESSION['new_address_gedcomnr'] = $_SESSION['new_address_gedcomnr'] + 1;
            }

            // FTM:
            // 1 RESI Owner of the house, 6 
            // 2 CONC Lane.
            if ($level[2] == 'CONC') {
                $processed = 1;
                $address_array["address"][$nraddress2] .= substr($buffer, 7);
            }
            if ($level[2] == 'CONT') {
                $processed = 1;
                $address_array["address"][$nraddress2] .= "\n" . substr($buffer, 7);
            }

            // *** Restore HuMo-genealogy address GEDCOM numbers ***
            // 1 RESI
            // 2 RIN 1 (GEDCOM number without R).
            if ($gen_program == 'HuMo-gen' or $gen_program == 'HuMo-genealogy') {
                if ($level[2] == 'RIN ') {
                    $processed = 1;
                    //echo substr($buffer,6).'<br>';
                    $address_gedcomnr = 'R' . substr($buffer, 6);

                    if ($add_tree == true or $reassign == true) {
                        $address_gedcomnr = $this->reassign_ged($address_gedcomnr, 'R');
                    }

                    $address_array["gedcomnr"][$nraddress2] = $address_gedcomnr;
                    $connect['item_id'][$connect_nr] = $address_gedcomnr;
                    $_SESSION['address_gedcomnr'] = $address_gedcomnr;

                    // *** Reset address GEDCOM number ***
                    $_SESSION['new_address_gedcomnr'] = $_SESSION['new_address_gedcomnr'] - 1;
                }
            }

            // *** Street Aldfaer/ Pro-gen ***
            //1 RESI
            //2 ADDR Citystreet 18
            //3 CITY Wellen
            if ($level[2] == 'ADDR') {
                if ($buffer6 == '2 ADDR') {
                    $address_array["address"][$nraddress2] .= substr($buffer, 7);
                    $processed = 1;
                }
                $address_array["address"][$nraddress2] = $this->process_texts($address_array["address"][$nraddress2], $buffer, '2');
            }

            // *** Living place for Aldfaer ***
            //1 RESI
            //2 ADDR Oosteind 44
            //3 CONT Zwaag
            //3 CITY Zwaag
            if ($buffer6 == '3 CITY') {
                $processed = 1;
                if ($address_array["place"][$nraddress2]) $address_array["place"][$nraddress2] .= ', ';
                $address_array["place"][$nraddress2] .= substr($buffer, 7);
                $pers_place_index = substr($buffer, 7);
            }

            // GRAMPS:
            // 1 RESI
            // 2 ADDR
            // 3 CITY Huixquilucan
            // 3 STAE Edo. de Mexico
            // 3 POST 55555
            // 3 CTRY Mexico
            // 2 PHON 52 (55) 1234-5xxx
            if ($buffer6 == '3 STAE') {
                $processed = 1;
                if ($address_array["place"][$nraddress2]) $address_array["place"][$nraddress2] .= ', ';
                $address_array["place"][$nraddress2] .= substr($buffer, 7);
            }
            if ($buffer6 == '3 CTRY') {
                $processed = 1;
                if ($address_array["place"][$nraddress2]) $address_array["place"][$nraddress2] .= ', ';
                $address_array["place"][$nraddress2] .= substr($buffer, 7);
            }

            if ($buffer6 == '3 POST') {
                $processed = 1;
                $address_array["zip"][$nraddress2] = substr($buffer, 7);
            }

            if ($buffer6 == '2 PHON') {
                $processed = 1;
                $address_array["phone"][$nraddress2] = substr($buffer, 7);
            }

            // *** Living place for BK ***
            if ($buffer6 == '2 PLAC') {
                $processed = 1;
                if ($address_array["place"][$nraddress2]) $address_array["place"][$nraddress2] .= ', ';
                $address_array["place"][$nraddress2] .= substr($buffer, 7);
                $pers_place_index = substr($buffer, 7);
            }

            // *** Texts by living places for BK, Aldfaer ***
            if ($level[2] == 'NOTE') {
                if ($address_array["text"][$nraddress2]) $address_array["text"][$nraddress2] .= '. ';
                $address_array["text"][$nraddress2] = $this->process_texts($address_array["text"][$nraddress2], $buffer, '2');
            }

            // *** Texts by living place for SukuJutut ***
            if ($gen_program == "SukuJutut") {
                if ($address_array["text"][$nraddress2]) $address_array["text"][$nraddress2] .= '. ';
                $address_array["text"][$nraddress2] = $this->process_texts($address_array["text"][$nraddress2], $buffer, '3');
            }

            // *** Date by living place for BK etc. ***
            //if ($buffer6=='2 DATE'){ $processed=1; $address_array["date"][$nraddress2]=substr($buffer,7); }
            if ($buffer6 == '2 DATE') {
                $processed = 1;
                $connect['date'][$connect_nr] = substr($buffer, 7);
            }

            // *** Source by address ***
            // *** Source also uses the connect table, so an extra SESSION is needed to store the address GEDCOM number ***
            if ($level[2] == 'SOUR') {
                //if ($connect_kind=='person'){
                //	$this->process_sources('person','pers_address_source',$_SESSION['address_gedcomnr'],$buffer,'2');
                //}
                //else{
                //	$this->process_sources('family','fam_address_source',$_SESSION['address_gedcomnr'],$buffer,'2');
                //}

                if ($connect_kind == 'person') {
                    $this->process_sources('person', 'pers_address_connect_source', $calculated_connect_id, $buffer, '2');
                } else {
                    $this->process_sources('family', 'fam_address_connect_source', $calculated_connect_id, $buffer, '2');
                }

                // *** Source by address ***
                //$this->process_sources('address','address_source',$calculated_connect_id,$buffer,'2');
            }
        }
    }



    // *** Process all kind of STANDARD sources ***
    function process_sources($connect_kind2, $connect_sub_kind2, $connect_connect_id2, $buffer, $number)
    {
        global $connect_nr, $connect, $calculated_connect_id;
        global $processed, $level;
        global $largest_source_ged, $add_tree, $reassign;
        global $nrsource, $source;

        // 2 SOUR Source text
        $buffer6 = substr($buffer, 0, 6);

        // *** Store source - connections ***
        if ($buffer6 == $number . ' SOUR') {
            $processed = 1;
            $connect_nr++;
            $calculated_connect_id++;
            $connect['kind'][$connect_nr] = $connect_kind2;
            $connect['sub_kind'][$connect_nr] = $connect_sub_kind2;
            $connect['connect_id'][$connect_nr] = $connect_connect_id2;
            $connect['text'][$connect_nr] = '';
            $connect['quality'][$connect_nr] = '';
            $connect['source_id'][$connect_nr] = '';
            $connect['item_id'][$connect_nr] = '';
            $connect['text'][$connect_nr] = '';
            $connect['page'][$connect_nr] = '';
            $connect['role'][$connect_nr] = '';
            $connect['date'][$connect_nr] = '';
            $connect['place'][$connect_nr] = '';

            // *** Check for @ characters (=link to shared source), or save text ***
            // 1 SOUR @S1@
            if (substr($buffer, 7, 1) == '@') {
                // *** Trim needed for MyHeritage (double spaces behind a source line) ***
                $buffer = trim($buffer);

                $connect['source_id'][$connect_nr] = substr($buffer, 8, -1);
                if ($add_tree == true or $reassign == true) {
                    $connect['source_id'][$connect_nr] = $this->reassign_ged(substr($buffer, 8, -1), 'S');
                }
            } else {
                // *** Jan. 2021: all sources are stored in the source table ***

                $new_source_gedcomnr = 'S' . $_SESSION['new_source_gedcomnr'];
                if ($add_tree == true or $reassign == true) {
                    $new_source_gedcomnr = $this->reassign_ged('S' . $_SESSION['new_source_gedcomnr'], 'S');
                }
                $connect['source_id'][$connect_nr] = $new_source_gedcomnr;

                $nrsource++;
                //unset ($source);  //Reset array
                $source["source_gedcomnr"][$nrsource] = $new_source_gedcomnr;
                $source["source_status"][$nrsource] = '';
                $source["source_title"][$nrsource] = '';
                $source["source_abbr"][$nrsource] = '';
                $source["source_date"][$nrsource] = '';
                $source["source_publ"][$nrsource] = '';
                $source["source_place"][$nrsource] = '';
                $source["source_refn"][$nrsource] = '';
                $source["source_auth"][$nrsource] = '';
                $source["source_subj"][$nrsource] = '';
                $source["source_item"][$nrsource] = '';
                $source["source_kind"][$nrsource] = '';
                $source["source_text"][$nrsource] = '';
                $source["source_repo_name"][$nrsource] = '';
                $source["source_repo_caln"][$nrsource] = '';
                $source["source_repo_page"][$nrsource] = '';
                $source["source_repo_gedcomnr"][$nrsource] = '';
                $source["source_unprocessed_tags"][$nrsource] = '';
                $source["new_date"][$nrsource] = '';
                $source["new_time"][$nrsource] = '';
                $source["changed_date"][$nrsource] = '';
                $source["changed_time"][$nrsource] = '';

                $source["source_text"][$nrsource] .= substr($buffer, 7);

                // *** Next GEDCOM number ***
                $_SESSION['new_source_gedcomnr'] = $_SESSION['new_source_gedcomnr'] + 1;
            }
        }

        // *** Source text ***
        if ($level[$number] == 'SOUR') {
            if ($buffer6 == ($number + 1) . ' CONT') {
                $processed = 1;
                $source["source_text"][$nrsource] .= $this->cont(substr($buffer, 7));
            }
            if ($buffer6 == ($number + 1) . ' CONC') {
                $processed = 1;
                $source["source_text"][$nrsource] .= $this->conc(substr($buffer, 7));
            }
        }

        // *** Sources in GEDCOM test file ***
        // 1 SOUR This source is embedded in the record instead of being a link to a
        // 2 CONC separate SOURCE record.
        // 2 CONT The source description can use any number of lines
        // 2 TEXT Text from a source. The preferred approach is to cite sources by
        // 3 CONC links to SOURCE records.
        // 3 CONT Here is a new line of text from the source.
        if ($number < 3) {
            if ($level[$number + 1] == 'TEXT') {
                if ($buffer6 == ($number + 1) . ' TEXT') {
                    $processed = 1;
                    if ($source["source_text"][$nrsource]) {
                        $source["source_text"][$nrsource] .= '<br>';
                    }
                    $source["source_text"][$nrsource] .= substr($buffer, 7);
                }
                if ($buffer6 == ($number + 2) . ' CONT') {
                    $processed = 1;
                    $source["source_text"][$nrsource] .= $this->cont(substr($buffer, 7));
                }
                if ($buffer6 == ($number + 2) . ' CONC') {
                    $processed = 1;
                    $source["source_text"][$nrsource] .= $this->conc(substr($buffer, 7));
                }
            }
        }

        // *** Source text BK and Legacy ***
        // 1 BIRT
        // 2 DATE 08 SEP 1909
        // 2 PLAC DJAKARTA(BATAVIA)-INDONESIE
        // 2 SOUR @S125@
        // 3 DATA
        // 4 TEXT text ..... => Extra text by source...
        // 5 CONT ..........
        $test_level = 'level' . ($number + 1);
        if (isset($level[$number + 1]) and $level[$number + 1] == 'DATA') {
            if ($buffer6 == ($number + 1) . ' DATA') {
                $processed = 1; //$connect['text'][$connect_nr]=substr($buffer, 7);
            }
            if ($buffer6 == ($number + 2) . ' TEXT') {
                if ($connect['text'][$connect_nr]) {
                    $connect['text'][$connect_nr] .= '<br>';
                }
                $processed = 1;
                $connect['text'][$connect_nr] .= substr($buffer, 7);
            }
            if ($buffer6 == ($number + 3) . ' CONT') {
                $processed = 1;
                $connect['text'][$connect_nr] .= $this->cont(substr($buffer, 7));
            }
            if ($buffer6 == ($number + 3) . ' CONC') {
                $processed = 1;
                $connect['text'][$connect_nr] .= $this->conc(substr($buffer, 7));
            }
        }

        // *** Picture by source
        // 3 OBJE
        // 4 TITL Multimedia link about this source
        // 4 FORM jpeg
        // 4 NOTE @N26@
        // 4 FILE ImgFile.JPG
        // *** ONLY CHECK: 3 OBJE ***
        //if ($level[$number+1]=='OBJE'){

        //if ($level[3]=='OBJE'){
        //SKIP @xx@, otherwise this will process errors: 3 OBJE @M1@
        if ($level[3] == 'OBJE' and substr($buffer, 7, 1) != '@') {
            $this->process_picture('connect', $calculated_connect_id, 'picture', $buffer);
        }

        // *** Source reference (own code) ***
        if ($buffer6 == ($number + 1) . ' REFN') {
            $processed = 1;
            $source["source_refn"][$nrsource] = substr($buffer, 7);
        }

        // *** Source page ***
        if ($number < 3) {
            if ($level[$number + 1] == 'PAGE') {
                if ($buffer6 == ($number + 1) . ' PAGE') {
                    $processed = 1;
                    $connect['page'][$connect_nr] = substr($buffer, 7);
                }
                if ($buffer6 == ($number + 2) . ' CONT') {
                    $processed = 1;
                    $connect['page'][$connect_nr] .= $this->cont(substr($buffer, 7));
                }
                if ($buffer6 == ($number + 2) . ' CONC') {
                    $processed = 1;
                    $connect['page'][$connect_nr] .= $this->conc(substr($buffer, 7));
                }
            }
        }

        // *** Source role ***
        if ($buffer6 == ($number + 1) . ' ROLE') {
            $processed = 1;
            $connect['role'][$connect_nr] = substr($buffer, 7);
        }

        // *** Source date ***
        if ($buffer6 == ($number + 1) . ' DATE') {
            $processed = 1;
            $connect['date'][$connect_nr] = substr($buffer, 7);
            //$processed=1; $source["source_date"][$nrsource]=substr($buffer, 7);
        }
        // *** Source place ***
        if ($buffer6 == ($number + 1) . ' PLAC') {
            $processed = 1;
            $connect['place'][$connect_nr] = substr($buffer, 7);
            //$processed=1; $source["source_place"][$nrsource]=substr($buffer, 7);
        }

        // *** Aldfaer time ***
        //2 _ALDFAER_TIME 08:00:00
        //if (substr($buffer,0,15)=='2 _ALDFAER_TIME'){
        //	if (nrbron>0) then BronDate[nrbron]:=copy(buf,8,length(buf));
        //}

        // *** Source quality, stored in connection table ***
        if ($buffer6 == ($number + 1) . ' QUAY') {
            $processed = 1;
            $connect['quality'][$connect_nr] = $this->process_quality($buffer);
        }
    }

    /* EXAMPLES
 * if ($level[2]=='OBJE') $this->process_picture('person',$pers_gedcomnumber,'picture_birth', $buffer);
 * if ($level[2]=='OBJE') $this->process_picture('person',$pers_gedcomnumber,'picture_event_'.$calculated_event_id, $buffer);
 * if ($level[2]=='OBJE') $this->process_picture('family',$gedcomnumber,'picture_fam_marr_notice', $buffer);
 * if ($level[3]=='OBJE' AND substr($buffer,7,1)!='@'){ $this->process_picture('connect',$calculated_connect_id,'picture', $buffer); }
 * if ($level[1]=='OBJE') $this->process_picture('source',$source["id"],'picture', $buffer);
 */
    function process_picture($connect_kind, $connect_id, $picture, $buffer)
    {
        global $level, $processed;
        global $event, $event_nr, $event2, $event2_nr;
        global $calculated_event_id, $calculated_connect_id;
        global $connect, $connect_nr, $add_tree, $reassign;

        $event_picture = false;
        $buffer6 = substr($buffer, 0, 6);

        // *** Just for sure: set default values ***
        $test_level = 'level2';
        $test_number1 = '1';
        $test_number2 = '2';
        // *** picture = person or family picture ***
        if ($picture == 'picture') {
            $test_level = 'level2';
            $test_number1 = '1';
            $test_number2 = '2';
        } elseif ($picture == 'picture_birth') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_bapt') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        }
        // *** picture_death = pictures or cards etc. by death ***
        elseif ($picture == 'picture_death') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_buried') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_church_notice') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_notice') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_church') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr') {
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        }
        // *** Picture by event ***
        elseif (substr($picture, 0, 13) == 'picture_event') {
            $event_picture = true;
            $test_level = 'level3';
            $test_number1 = '2';
            $test_number2 = '3';
        }

        // *** Picture by source ***
        // 3 OBJE
        // 4 TITL Multimedia link about this source
        // 4 FORM jpeg
        // 4 NOTE @N26@
        // 4 FILE ImgFile.JPG
        if ($connect_kind == 'connect' and $picture == 'picture') {
            $test_level = 'level4';
            $test_number1 = '3';
            $test_number2 = '4';
        }

        // *** External object/ image (could be multiple lines, they will be processed!) ***
        // 1 OBJE @O3@
        if ($buffer6 == $test_number1 . ' OBJE' and substr($buffer, 7, 1) == '@') {
            // *** Connection to seperate object (image) stored in connection table ***
            $processed = 1;
            $connect_nr++;
            $calculated_connect_id++;

            if ($connect_kind == 'person') {
                $connect['kind'][$connect_nr] = 'person';
                $connect['sub_kind'][$connect_nr] = 'pers_object';
            } elseif ($connect_kind == 'family') {
                $connect['kind'][$connect_nr] = 'family';
                $connect['sub_kind'][$connect_nr] = 'fam_object';
            } elseif ($connect_kind == 'source') {
                $connect['kind'][$connect_nr] = 'source';
                $connect['sub_kind'][$connect_nr] = 'source_object';
            }

            $connect['connect_id'][$connect_nr] = $connect_id;
            $connect['text'][$connect_nr] = '';
            // *** Check for @ characters (=link to shared source), or save text ***
            $connect['source_id'][$connect_nr] = '';
            $connect['item_id'][$connect_nr] = '';
            //$connect['text'][$connect_nr]='';
            if (substr($buffer, 7, 1) == '@') {
                // *** Saved as source_id in database, but connect_item_id is probably better... ***
                $connect['source_id'][$connect_nr] = substr($buffer, 8, -1);
                if ($add_tree == true or $reassign == true) {
                    $connect['source_id'][$connect_nr] = $this->reassign_ged(substr($buffer, 8, -1), 'O');
                }
            } else {
                $connect['text'][$connect_nr] .= substr($buffer, 7);
            }
            $connect['quality'][$connect_nr] = '';
            //PLACE NOT IN USE YET
            $connect['place'][$connect_nr] = '';
            $connect['page'][$connect_nr] = '';
            $connect['role'][$connect_nr] = '';
            $connect['date'][$connect_nr] = '';
        }

        // *** Objects without reference ***
        // 3 OBJE H:\haza21v3\plaatjes\IM000247.jpg
        // *** Skip link to object with reference: 1 OBJE @O3@ ***
        if ($buffer6 == $test_number1 . ' OBJE' and substr($buffer, 7, 1) != '@') {
            $processed = 1;
            if ($event_picture == true) {
                // *** Process picture by event ***
                $calculated_event_id++;
                $event2_nr++;
                $event2['connect_kind'][$event2_nr] = $connect_kind;
                $event2['connect_id'][$event2_nr] = $connect_id;
                $event2['kind'][$event2_nr] = $picture; // picture = person or family picture.
                $event2['event'][$event2_nr] = '';
                $event2['event_extra'][$event2_nr] = '';
                $event2['gedcom'][$event2_nr] = 'OBJE';
                $event2['date'][$event2_nr] = '';
                $event2['text'][$event2_nr] = '';
                $event2['place'][$event2_nr] = '';
            } else {
                $event_nr++;
                $calculated_event_id++;
                $event['connect_kind'][$event_nr] = $connect_kind;
                $event['connect_id'][$event_nr] = $connect_id;
                $event['kind'][$event_nr] = $picture; // picture = person or family picture.
                $event['event'][$event_nr] = '';
                $event['event_extra'][$event_nr] = '';
                $event['gedcom'][$event_nr] = 'OBJE';
                $event['date'][$event_nr] = '';
                $event['text'][$event_nr] = '';
                $event['place'][$event_nr] = '';
            }

            // *** Haza-data picture ***
            if (substr($buffer, 7)) {
                $processed = 1;
                $photo = substr($buffer, 7);
                $photo = $this->humo_basename($photo);
                if ($event_picture == true)
                    $event2['event'][$event2_nr] = $photo;
                else
                    $event['event'][$event_nr] = $photo;
            }
        }

        // *** Gramps ***
        // 1 OBJE
        // 2 FORM URL
        // 2 TITL GEDCOM 5.5 documentation web site
        // 2 FILE http://homepages.rootsweb.com/~pmcbride/gedcom/55gctoc.htm
        if (substr($buffer, 0, 10) == $test_number2 . ' FORM URL') {
            $processed = 1;
            if ($event_picture == true)
                $event2['kind'][$event2_nr] = 'URL';
            else
                $event['kind'][$event_nr] = 'URL';
        }

        if ($buffer6 == $test_number2 . ' FILE') {
            $processed = 1;
            $photo = substr($buffer, 7);
            // *** Aldfaer sometimes uses: 2 FILE \bestand.jpg ***
            $photo = $this->humo_basename($photo);
            if ($event_picture == true)
                $event2['event'][$event2_nr] = $photo;
            else
                $event['event'][$event_nr] = $photo;
        }

        // *** Aldfaer ***
        // 2 TITL text
        // 3 CONT text second line
        //if ($level[2]=='TITL'){
        if ($level[$test_number2] == 'TITL') {
            $processed = 1;
            if ($event_picture == true)
                $event2['text'][$event2_nr] = $this->process_texts($event2['text'][$event2_nr], $buffer, $test_number2);
            else
                $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, $test_number2);
        }

        // *** 2 FORM jpeg ***
        //if ($level[2]=='FORM'){
        if ($level[$test_number2] == 'FORM') {
            $processed = 1;
            if ($event_picture == true)
                $event2['event_extra'][$event2_nr] = substr($buffer, 7);
            else
                $event['event_extra'][$event_nr] = substr($buffer, 7);
        }

        // *** Text by photo Haza-21 ***
        if ($level[2] == 'NOTE') {
            if ($event_picture == true)
                $event2['text'][$event2_nr] = $this->process_texts($event2['text'][$event2_nr], $buffer, $test_number2);
            else
                $event['text'][$event_nr] = $this->process_texts($event['text'][$event_nr], $buffer, $test_number2);
        }

        if ($buffer6 == $test_number2 . ' DATE') {
            $processed = 1;
            if ($event_picture == true)
                $event2['date'][$event2_nr] = substr($buffer, 7);
            else
                $event['date'][$event_nr] = substr($buffer, 7);
        }

        // *** Source by pictures ***
        if ($event_picture == true) {
            // no source by picture by event at this moment...
        } else {
            //if ($level[2]=='SOUR'){
            // *** Don't process a source by a object by a source. The script will stop with error ***
            if ($level[2] == 'SOUR' and $level[3] != 'OBJE') {
                $this->process_sources('person', 'pers_event_source', $calculated_event_id, $buffer, $test_number2);
            }
        }
    }
} // end class
