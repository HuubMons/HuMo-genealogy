<?php

/**
 * Class for importing GEDCOM files
 * 
 * TODO: check "private" and "public" for every function.
 */

namespace Genealogy\Include;

class GedcomImport
{
    private $dbh, $tree_id, $tree_prefix, $humo_option;
    private $gen_program, $gen_program_version, $add_tree, $reassign;
    private $level, $buffer, $processed, $not_processed;

    private $nrsource, $source;
    private $nraddress2, $address_order, $address_array;
    private $connect_nr, $connect;

    private $event_nr, $event, $event_status;
    private $event_nr1, $event_nr2; // $event_nr1 = for 1st event level, $event_nr2 = for 2nd event level.

    private $calculated_event_id, $calculated_connect_id;
    //private $calculated_address_id;

    // *** Google geolocation ***
    private $geocode_nr, $geocode_plac, $geocode_lati, $geocode_long, $geocode_type;

    public function __construct($dbh, $tree_id, $tree_prefix, $humo_option, $add_tree, $reassign)
    {
        $this->dbh = $dbh;
        $this->tree_id = $tree_id;
        $this->tree_prefix = $tree_prefix;
        $this->humo_option = $humo_option;
        $this->add_tree = $add_tree;
        $this->reassign = $reassign;

        $this->connect_nr = 0;
        $this->event_nr = 0;
        //$this->event2_nr = 0;

        $this->event_nr1 = 0;
        $this->event_nr2 = 0;

        /**
         * Insert a temporary line into database to get latest id.
         * This is done because table can be empty when reloading GEDCOM file...
         * Even in an empty table, latest id can be a high number...
         * And: LastInsertId() can't be used for 1 selected table (without insert).
         * May 2025: even AI suggested to do this.
         */
        $dbh->query("INSERT INTO humo_events SET event_tree_id='" . $tree_id . "'");
        $this->calculated_event_id = $dbh->lastInsertId();
        $dbh->query("DELETE FROM humo_events WHERE event_id='" . $this->calculated_event_id . "'");

        $dbh->query("INSERT INTO humo_connections SET connect_tree_id='" . $tree_id . "'");
        $this->calculated_connect_id = $dbh->lastInsertId();
        $dbh->query("DELETE FROM humo_connections WHERE connect_id='" . $this->calculated_connect_id . "'");

        //$dbh->query("INSERT INTO humo_addresses SET address_tree_id='" . $tree_id . "'");
        //$this->calculated_address_id = $dbh->lastInsertId();$this->nraddress2
        //$dbh->query("DELETE FROM humo_addresses WHERE address_id='" . $this->calculated_address_id . "'");
    }

    public function set_gen_program($gen_program)
    {
        $this->gen_program = $gen_program;
    }

    public function set_gen_program_version($gen_program_version)
    {
        $this->gen_program_version = $gen_program_version;
    }

    /**
     * Process persons
     */
    public function process_person($person_array): void
    {
        global $prefix, $prefix_length; // Prefix for lastname

        $line2 = explode("\n", $person_array);

        //TEST LINE
        //echo '<p>'; for ($z=1; $z<=count($line2)-2; $z++){ echo $z.' '.$line2[$z].'<br>'; }

        // Use array for new variables.
        unset($person);  //Reset array

        $person["pers_patronym"] = '';
        $person["pers_prefix"] = '';
        $person["pers_text"] = '';
        $person["pers_own_code"] = '';

        //$pers_callname='';
        $pers_firstname = '';
        $pers_lastname = '';
        $pers_name_text = '';
        $fams = '';
        $pers_famc = '';
        $pers_birth_date = '';
        $pers_birth_time = '';
        $pers_birth_place = '';
        $pers_birth_text = '';
        $pers_stillborn = '';
        $pers_bapt_date = '';
        $pers_bapt_place = '';
        $pers_bapt_text = '';
        $pers_religion = '';
        $pers_death_date = '';
        $pers_death_time = '';
        $pers_death_place = '';
        $pers_death_text = '';
        $pers_buried_date = '';
        $pers_buried_place = '';
        $pers_buried_text = '';
        $pers_cremation = '';
        $pers_death_cause = '';
        $person["pers_death_age"] = '';
        $person["pers_cal_date"] = '';
        $pers_sexe = '';
        $person["pers_quality"] = '';
        $person["pers_unprocessed_tags"] = '';
        $person["new_date"] = "1970-01-01";
        $person["new_time"] = "00:00:01";
        $person["new_user_id"] = '';
        $person["changed_date"] = '';
        $person["changed_time"] = '';
        $person["changed_user_id"] = '';
        $pers_birth_date_hebnight = '';
        $pers_death_date_hebnight = '';
        $pers_buried_date_hebnight = '';
        $pers_heb_flag = '';
        $pers_alive = '';
        if ($this->gen_program == 'Haza-Data') {
            $pers_alive = 'deceased';
        }
        //if ($this->gen_program=='HuMo-gen' OR $this->gen_program=='HuMo-genealogy'){
        //  $pers_alive='deceased';
        //}

        $this->event_status = false;

        $surname_processed = false;

        // *** For event table ***
        $this->event_nr = 0;
        //$this->event2_nr = 0;

        // *** Save addresses in a seperate table ***
        $this->nraddress2 = 0;
        $this->address_order = 0;

        // *** Save sources in a seperate table ***
        $this->nrsource = 0;

        // *** Location data for Google maps ***
        $this->geocode_nr = 0;

        // *** For source connect table ***
        $this->connect_nr = 0;

        /**
         * Person
         */

        // 0 @I1@ INDI
        // *** Process 1st line ***
        $this->buffer[0] = $line2[0];
        // Old code: it is alowed to read tags like: I_1;
        //$this->buffer[0] = str_replace("_", "", $this->buffer[0]); //Aldfaer numbers
        $pers_gedcomnumber = substr($this->buffer[0], 3, -6);
        if ($this->add_tree == true || $this->reassign == true) {
            $pers_gedcomnumber = $this->reassign_ged($pers_gedcomnumber, 'I');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $pers_gedcomnumber . ' ';
        }

        // *** FOR TEST ONLY: Show endtime (see also show time at the end of person function) ***
        //$person_time=microtime();

        // *** Save level0 ***
        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level['1a'] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        // *** Process other lines ***
        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline

            // TEST: show memory usage
            //if (!isset($memory)){ $memory=memory_get_usage(); }
            //$calc_memory=(memory_get_usage()-$memory);
            //echo '<br>&nbsp;&nbsp;&nbsp;'.memory_get_usage().' '.$calc_memory.'# '.$this->buffer[0];
            //$memory=memory_get_usage();

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 5));  // *** rtrim voor CHR_. Update: rtrim not neccesary anymore? ***
                if (substr($this->buffer[0], 0, 6) === '1 CHR ') {
                    // *** Update sep 2015: better processing of HZ-21 line: 1 CHR Ned. Herv ***
                    $this->level[1] = 'CHR';
                }

                $this->level['1a'] = rtrim($this->buffer[0]);  // *** Needed to test for 1 RESI @. Update: rtrim not really neccesary anymore? ***
                $this->event_status = false;
                $event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
                $famc = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';

                // *** Possible bug in Haza-21 program: 2 @S167@ SOUR. Rebuild to: 2 SOUR @S167@ ***
                if ($this->gen_program == 'Haza-21' && substr($this->buffer[0], -4) === 'SOUR') {
                    $this->buffer[0] = substr($this->buffer[0], 0, 2) . 'SOUR ' . substr($this->buffer[0], 2, -5);
                    $this->level[2] = substr($this->buffer[0], 2, 4);
                }
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            // *** Save date ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $person["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $person["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $person["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Save changed date ***
            //1 CHAN
            //2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $person["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $person["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $person["changed_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Parents ***
            if ($this->level[1] == 'FAMC') {
                // 1 FAMC @F1@
                if ($this->buffer[8] === '1 FAMC @') {
                    if ($pers_famc) {
                        // *** Second famc, used for adoptive parents ***
                        $this->processed = true;
                        $famc = substr($this->buffer[0], 8, -1); // Needed for Aldfaer adoptive parents
                        if ($this->gen_program != 'ALDFAER') {
                            $pers_famc2 = substr($this->buffer[0], 8, -1);
                            if ($this->add_tree == true || $this->reassign == true) {
                                $pers_famc2 = $this->reassign_ged($pers_famc2, 'F');
                            }
                            $this->event_nr++;
                            $this->event_nr1 = $this->event_nr;
                            $this->calculated_event_id++;

                            // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                            $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                            $this->event['level'][$this->event_nr1] = '1';
                            $this->event['connect_kind'][$this->event_nr1] = 'person';
                            $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                            $this->event['connect_kind2'][$this->event_nr1] = '';
                            $this->event['connect_id2'][$this->event_nr1] = '';
                            $this->event['kind'][$this->event_nr1] = 'adoption';
                            $this->event['event'][$this->event_nr1] = $pers_famc2;
                            $this->event['event_extra'][$this->event_nr1] = '';
                            $this->event['gedcom'][$this->event_nr1] = 'FAMC';
                            $this->event['date'][$this->event_nr1] = '';
                            $this->event['text'][$this->event_nr1] = '';
                            $this->event['place'][$this->event_nr1] = '';
                        }
                    } else {
                        // *** Normal parents ***
                        $this->processed = true;
                        $famc = substr($this->buffer[0], 8, -1); // Needed for Aldfaer adoptive parents
                        $pers_famc = substr($this->buffer[0], 8, -1);
                        if ($this->add_tree == true || $this->reassign == true) {
                            $pers_famc = $this->reassign_ged($pers_famc, 'F');
                        }
                    }
                }

                // *** Aldfaer adopted/ steph/ legal/ foster childs ***
                // 2 PEDI adopted
                // 2 PEDI steph
                // 2 PEDI legal
                // 2 PEDI foster
                // 2 PEDI birth     is in use in Aldfaer 8.
                if ($this->buffer[7] === '2 PEDI ' && $this->buffer[0] !== '2 PEDI birth') {
                    // *** Adoption by person ***
                    $this->processed = true;
                    $pers_famc2 = $famc;
                    if ($this->add_tree == true || $this->reassign == true) {
                        $pers_famc2 = $this->reassign_ged($famc, 'F');
                    }
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'adoption_by_person';
                    // *** BE AWARE: in gedcom_import.php step 4 further processing is done, famc is converted into a person number!!! ***
                    $this->event['event'][$this->event_nr1] = $pers_famc2;
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = substr($this->buffer[0], 7); // *** adopted, steph, legal or foster. ***
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';

                    if ($pers_famc == $famc) {
                        $pers_famc = '';
                    }
                }
            }

            // *** Own families ***
            // 1 FAMS @F5@
            // 1 FAMS @F11@
            if ($this->buffer[8] === '1 FAMS @') {
                $this->processed = true;
                $tempnr = substr($this->buffer[0], 8, -1);
                if ($this->add_tree == true || $this->reassign == true) {
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

            // *** Pro-gen titles by name ***
            // 1 _TITL2 = title between first and last name.
            if ($this->buffer[8] === '1 _TITL2') {
                $this->level[1] = 'NAME';
                //$this->level[2]='NPFX';
                $this->level[2] = 'SPFX';
                //$this->buffer[0]='2 NPFX'.substr($this->buffer[0], 8);
                $this->buffer[0] = '2 SPFX' . substr($this->buffer[0], 8);
                $this->buffer[6] = substr($this->buffer[0], 0, 6);
            }
            // 1 _TITL3 = title behind lastname.
            if ($this->buffer[8] === '1 _TITL3') {
                $this->level[1] = 'NAME';
                $this->level[2] = 'NSFX';
                $this->buffer[0] = '2 NSFX' . substr($this->buffer[0], 8);
                $this->buffer[6] = substr($this->buffer[0], 0, 6);
            }

            if ($this->level[1] == 'NAME') {
                if ($this->buffer[6] === '1 NAME') {
                    $this->processed = true;
                    $name = str_replace("_", " ", $this->buffer[0]);
                    $name = str_replace("~", " ", $name);

                    // *** Second line "1 NAME" is a callname ***
                    if ($pers_firstname !== '' && $pers_firstname !== '0') {
                        // *** Don't process second/ third etc. "NAME" for Rootsmagic ***
                        if ($this->gen_program == "RootsMagic") {
                            $this->processed = true;
                        } else {
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

                            $this->processed = true;
                            $pers_aka = substr($name, 7);
                            // *** Remove / if nickname starts with / ***
                            if (substr($pers_aka, 0, 1) === '/') {
                                $pers_aka = substr($pers_aka, 1);
                            }
                            $pers_aka = str_replace("/", " ", $pers_aka);
                            $pers_aka = str_replace("  ", " ", $pers_aka);
                            $pers_aka = rtrim($pers_aka);

                            $this->processed = true;
                            $this->event_nr++;
                            $this->event_nr1 = $this->event_nr;
                            $this->calculated_event_id++;

                            // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                            $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                            $this->event['level'][$this->event_nr1] = '1';
                            $this->event['connect_kind'][$this->event_nr1] = 'person';
                            $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                            $this->event['connect_kind2'][$this->event_nr1] = '';
                            $this->event['connect_id2'][$this->event_nr1] = '';
                            $this->event['kind'][$this->event_nr1] = 'name';
                            $this->event['event'][$this->event_nr1] = $pers_aka;
                            $this->event['event_extra'][$this->event_nr1] = '';
                            $this->event['gedcom'][$this->event_nr1] = 'NICK';
                            $this->event['date'][$this->event_nr1] = '';
                            $this->event['text'][$this->event_nr1] = '';
                            $this->event['place'][$this->event_nr1] = '';
                        }
                    } else {
                        $position = strpos($name, "/");
                        if ($position !== false) {
                            // there are slashes
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
                //
                // Rootsmagic could have multiple surnames (used as alternative surnames):
                // 1 NAME Rebecca /Langton/
                // 2 GIVN Rebecca
                // 2 SURN Langton
                // 1 NAME /Hanzl/
                // 2 SURN Hanzl
                // 1 NAME /Hanzly/
                // 2 SURN Hanzly
                if ($this->buffer[6] === '2 GIVN') {
                    $this->processed = true;
                    $pers_firstname = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 SURN') {
                    if (!$surname_processed) {
                        $this->processed = true;
                        $pers_lastname = substr($this->buffer[0], 7);
                        $surname_processed = true;
                    } else {
                        $this->processed = true;
                        $pers_lastname .= ', ' . substr($this->buffer[0], 7);
                    }

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
                if (strtoupper($this->buffer[0]) === '2 TYPE AKA') {
                    $this->processed = true;
                    //$pers_aka=str_replace("/", " ", $pers_aka);
                    //$pers_aka=str_replace("  ", " ", $pers_aka);
                    //$pers_aka=rtrim($pers_aka);

                    //$this->processed = true;
                    //$this->event_nr++;
                    //$this->event_nr1 = $this->event_nr;
                    //$this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    //$this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    //$this->event['level'][$this->event_nr1] = '1'; 
                    //$this->event['connect_kind'][$this->event_nr1]='person';
                    //$this->event['connect_id'][$this->event_nr1]=$pers_gedcomnumber;
                    //$this->event['connect_kind2'][$this->event_nr1] = '';
                    //$this->event['connect_id2'][$this->event_nr1] = '';   
                    //$this->event['kind'][$this->event_nr1]='name';
                    //$this->event['event'][$this->event_nr1]=$pers_aka;
                    //$this->event['event_extra'][$this->event_nr1]='';
                    $this->event['gedcom'][$this->event_nr1] = '_AKA';
                    //$this->event['date'][$this->event_nr1]='';
                    //$this->event['text'][$this->event_nr1]='';
                    //$this->event['place'][$this->event_nr1]='';

                    // *** Empty original pers_call_name ***
                    //$pers_callname=$pers_callname_org;
                }

                // *** GEDCOM 5.5 lastname prefix: 2 SPFX Le ***
                if ($this->buffer[6] === '2 SPFX') {
                    $this->processed = true;
                    $person["pers_prefix"] = substr($this->buffer[0], 7) . '_';
                    $person["pers_prefix"] = str_replace(" ", "_", $person["pers_prefix"]);
                }

                // *** Title in GEDCOM 5.5: 2 NPFX Prof. ***
                if ($this->buffer[6] === '2 NPFX') {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'NPFX';
                    $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = 'NPFX';
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }

                // *** GEDCOM 5.5 name addition: 2 NSFX Jr. ***
                if ($this->buffer[6] === '2 NSFX') {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'NSFX';
                    $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = $this->level[1];
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }


                if ($this->level[2] == 'NOTE') {
                    $pers_name_text = $this->process_texts($pers_name_text, '2');
                }

                if ($this->gen_program == "SukuJutut" && $this->level[3] == 'NOTE') {
                    $pers_name_text = $this->process_texts($pers_name_text, '3');
                }

                // *** Source by pers_name ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_name_source', $pers_gedcomnumber, '2');
                }

                $process_event = false;
                if ($this->buffer[7] === '2 _AKAN') {
                    $process_event = true;
                }
                // *** MyHeritage uses _AKA
                if ($this->buffer[6] === '2 _AKA') {
                    $process_event = true;
                    //  *** Replace optional / characters: 2 _AKA Sijpkje /Visser/ ***
                    $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                    $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                    $this->buffer[0] = rtrim($this->buffer[0]);
                }

                // *** HuMo-genealogy (roepnaam), BK (als bijnaam) and PG (als roepnaam): 2 NICK name ***
                // *** Users can change nickname for BK in language file! ***
                if ($this->buffer[6] === '2 NICK') {
                    $process_event = true;
                }

                // *** PG: 2 _ALIA ***
                if ($this->buffer[7] === '2 _ALIA') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _SHON') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _ADPN') {
                    $process_event = true;
                }
                //if ($this->buffer[7]=='1 _HEBN'){ $process_event=true; }
                if ($this->buffer[7] === '2 _HEBN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _CENN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _MARN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _GERN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _FARN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _BIRN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _INDN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _FKAN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _CURN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _SLDN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _FRKA') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _RELN') {
                    $process_event = true;
                }
                if ($this->buffer[7] === '2 _OTHN') {
                    $process_event = true;
                }

                // *** For German "2 _RUFN" entries. BK uses "2 _RUFNAME" ***
                // 2 _RUFNAME needs the isset($this->buffer[0][10] check a few lines below
                if ($this->buffer[7] === '2 _RUFN') {
                    $process_event = true;
                }

                if ($process_event) {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'name';

                    // *** $this->buffer[0][7] = check 8th character.***
                    // *** There maybe is a problem for texts like: "1 tekst" or "A text". ***
                    // *** 2 _AKA Also known as ***
                    if (isset($this->buffer[0][6]) && $this->buffer[0][6] === ' ') {
                        $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    }
                    // *** 2 _ALIA Alias ***
                    elseif (isset($this->buffer[0][7]) && $this->buffer[0][7] === ' ') {
                        $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 8);
                    }
                    // *** X _MARNM ??  MyHeritage ***
                    elseif (isset($this->buffer[0][8]) && $this->buffer[0][8] === ' ') {
                        $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 9);
                    }
                    //elseif (isset($this->buffer[0][10]) AND $this->buffer[0][10]==' ') {
                    //  $this->event['event'][$this->event_nr1]=substr($this->buffer[0],11);
                    //}

                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = trim(substr($this->buffer[0], 2, 5));
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }

                // Proces name-source and name-date (BK)
                //  3 DATE 21 FEB 2007
                //  3 SOUR @S3@
                //  3 NOTE text by Naam censusnaam
                //  4 CONT 2nd line
                //  4 CONT 3rd line
                if ($this->buffer[6] === '3 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr1] = substr($this->buffer[0], 7);
                }

                // *** Source by person event (name) ***
                if ($this->level[3] == 'SOUR') {
                    $event_id = $this->event['calculated_event_id'][$this->event_nr1];
                    $this->process_sources('person', 'pers_event_source', $event_id, '3');
                }

                if ($this->level[3] == 'NOTE') {
                    // *** GensDataPro uses 3 NOTE, there's no event (because 2 GIVN and 2 SURN are skipped)! ***
                    // 0 @I428@ INDI
                    // 1 NAME Wilhelmina/Brink/
                    // 2 GIVN Wilhelmina
                    // 2 SURN Brink
                    // 3 NOTE Naam kan ook zijn: Brink
                    if (isset($this->event['text'][$this->event_nr1])) {
                        $this->event['text'][$this->event_nr1] = $this->process_texts($this->event['text'][$this->event_nr1], '3');
                    } else {
                        $pers_name_text = $this->process_texts($pers_name_text, '3');
                    }
                }
            }

            // *** Quality ***
            // BELONGS TO A 1 xxxx ITEM????
            // Certain/ uncertain person (onzeker persoon) HZ
            if ($this->gen_program == 'Haza-Data' && $this->buffer[8] === '2 QUAY 0') {
                $this->processed = true;
                $pers_firstname = '(?) ' . $pers_firstname;
            }
            //if ($this->buffer[6]=='2 QUAY'){ $this->processed = true; $person["pers_quality"]=$this->process_quality(); }

            // *** Pro-gen: 1 _PATR Jans ***
            if ($this->buffer[7] === '1 _PATR') {
                $this->processed = true;
                $person["pers_patronym"] = substr($this->buffer[0], 8);
            }

            // *** Own code ***
            if ($this->buffer[6] === '1 REFN') {
                $this->processed = true;
                $person["pers_own_code"] = substr($this->buffer[0], 7);
            }

            // *** Finnish genealogy program SukuJutut (and some other genealogical programs) ***
            // 1 ALIA Frederik Hektor /McLean/
            if ($this->buffer[6] === '1 ALIA') {
                //$this->processed = true;
                //$this->buffer[0] = str_replace("/", "", $this->buffer[0]);  // *** Remove / from alias: 1 ALIA Frederik Hektor /McLean/ ***
                //if ($pers_callname){
                //	$pers_callname=$pers_callname.", ".substr($this->buffer[0], 7);
                //}
                //else {
                //	$pers_callname=substr($this->buffer[0],7);
                //}
                //$pers_callname=rtrim($pers_callname);

                $this->processed = true;
                $pers_aka = substr($this->buffer[0], 7);
                $pers_aka = str_replace("/", "", $pers_aka);  // *** Remove / from alias: 1 ALIA Frederik Hektor /McLean/ ***
                $pers_aka = rtrim($pers_aka);

                $this->processed = true;
                $this->event_nr++;
                $this->event_nr1 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr1] = '1';
                $this->event['connect_kind'][$this->event_nr1] = 'person';
                $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                $this->event['connect_kind2'][$this->event_nr1] = '';
                $this->event['connect_id2'][$this->event_nr1] = '';
                $this->event['kind'][$this->event_nr1] = 'name';
                $this->event['event'][$this->event_nr1] = $pers_aka;
                $this->event['event_extra'][$this->event_nr1] = '';
                $this->event['gedcom'][$this->event_nr1] = 'NICK';
                $this->event['date'][$this->event_nr1] = '';
                $this->event['text'][$this->event_nr1] = '';
                $this->event['place'][$this->event_nr1] = '';
            }

            // *** December 2021: now processed as event **
            // *** HuMo-genealogy (roepnaam), BK (als bijnaam) and PG (als roepnaam): 2 NICK name ***
            /*
            if ($this->buffer[6]=='2 NICK'){
                $this->processed = true;
                if ($pers_callname){
                    $pers_callname=$pers_callname.", ".substr($this->buffer[0],7);
                }
                else {
                    $pers_callname=substr($this->buffer[0],7);
                }
                $pers_callname=rtrim($pers_callname);
            }
            */

            // *** Text(s) by person ***
            if ($this->level[1] == 'NOTE') {
                $person["pers_text"] = $this->process_texts($person["pers_text"], '1');

                // BK: source by text
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_text_source', $pers_gedcomnumber, '2');
                }
            }

            // *** For BK (Interesse) ***
            // 1 ANCI
            // 2 NOTE De moeder trouwde met David Hoofien-de koetsier van haar
            // 3 CONT vader- en werd daarom onterft.Deze was van de fam.
            if ($this->level[1] == 'ANCI') {
                //if (substr($this->buffer[0], 0, 6)=='1 ANCI'){ $this->processed = true; $person["pers_text"].="<br>".substr($this->buffer[0],7); }
                if ($this->buffer[6] === '1 ANCI') {
                    $this->processed = true;
                    $person["pers_text"] .= substr($this->buffer[0], 7);
                }
                $person["pers_text"] = $this->process_texts($person["pers_text"], '2');
            }

            /**
             * Address(es)
             */
            if ($this->buffer[6] === '1 ADDR' || $this->level[1] == 'RESI') {
                $this->process_addresses('person', 'person_address', $pers_gedcomnumber);
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

            /**
             * Birth
             */
            if ($this->level[1] == 'BIRT') {
                if ($this->buffer[6] === '1 BIRT') {
                    $this->processed = true;
                }

                // *** Date ***
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    //FTM and dorotree programs can have multiple birth dates, only first one is saved:
                    if ($pers_birth_date === '' || $pers_birth_date === '0') {
                        $pers_birth_date = trim(substr($this->buffer[0], 7));
                    }
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $this->processed = true;
                    $pers_birth_date_hebnight = substr($this->buffer[0], 8);
                }
                // *** Aldfaer time ***
                // 2 _ALDFAER_TIME 08:00:00
                if (substr($this->buffer[0], 0, 15) === '2 _ALDFAER_TIME') {
                    $this->processed = true;
                    $pers_birth_time = substr($this->buffer[0], 16);
                }
                // *** Pro-gen time NOT TESTED ***
                // 2 TIME 22.40
                if ($this->buffer[6] === '2 TIME') {
                    $this->processed = true;
                    $pers_birth_time = substr($this->buffer[0], 7);
                }

                // *** Place ***
                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $pers_birth_place = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($pers_birth_place);
                }

                // *** Texts ***
                if ($this->level[2] == 'NOTE') {
                    $pers_birth_text = $this->process_texts($pers_birth_text, '2');
                }

                // *** Text for SukuJutut***
                if ($this->gen_program == "SukuJutut" && $this->level[3] == 'NOTE') {
                    $pers_birth_text = $this->process_texts($pers_birth_text, '3');
                }

                // *** Process sources Pro-gen and (older versions of) Aldfaer etc. ***
                // *** Source by person birth ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_birth_source', $pers_gedcomnumber, '2');
                }

                // *** Birth witness Pro-Gen. _WITN = GEDCOM 5.5.1 ***
                if ($this->buffer[7] === '2 _WITN') {
                    $this->buffer[0] = str_replace("2 _WITN", "2 WITN", $this->buffer[0]);
                    $this->level[2] = 'WITN';
                }
                // 2 WITN Witness//
                // *** Old GEDCOM files < GEDCOM 5.5.1 ***
                if ($this->level[2] == 'WITN') {
                    if (substr($this->buffer[0], 2, 4) === 'WITN') {
                        $this->processed = true;
                        $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                        $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                        $this->buffer[0] = trim($this->buffer[0]);
                        $this->event_nr++;
                        $this->event_nr1 = $this->event_nr;
                        $this->calculated_event_id++;

                        // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                        $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                        $this->event['level'][$this->event_nr1] = '1';
                        //$this->event['connect_kind'][$this->event_nr1] = 'person';
                        $this->event['connect_kind'][$this->event_nr1] = 'birth_declaration';
                        $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                        $this->event['connect_kind2'][$this->event_nr1] = '';
                        $this->event['connect_id2'][$this->event_nr1] = '';

                        //$this->event['kind'][$this->event_nr1] = 'birth_declaration';
                        $this->event['kind'][$this->event_nr1] = 'ASSO';
                        $this->event['event'][$this->event_nr1] = '';

                        if (substr($this->buffer[0], 7, 1) === '@') {
                            // 2 WITN @I1@
                            $this->event['connect_kind2'][$this->event_nr1] = 'person';
                            $this->event['connect_id2'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                        } else {
                            // 2 WITN Doopgetuige1//
                            $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                        }

                        $this->event['event_extra'][$this->event_nr1] = '';
                        $this->event['gedcom'][$this->event_nr1] = 'WITN';
                        $this->event['date'][$this->event_nr1] = '';
                        $this->event['text'][$this->event_nr1] = '';
                        $this->event['place'][$this->event_nr1] = '';
                    }
                    if ($this->buffer[6] === '3 CONT') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                    if ($this->buffer[6] === '3 CONC') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                }

                // *** Stillborn child ***
                // 1 BIRT
                // 2 TYPE stillborn
                if (substr($this->buffer[0], 0, 16) === '2 TYPE stillborn') {
                    $this->processed = true;
                    $pers_stillborn = 'y';
                }

                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_birth');
                }
            }

            /**
             * Baptise
             */

            // *** FTW ***
            //1 EVEN
            //2 TYPE Doopgetuigen
            //2 PLAC Petrus Verdeurmen, Judoca De Grauw

            // Geneanet/ Geneweb
            // 1 BAPM
            // 2 DATE 23 APR 1658
            // 2 PLAC Venlo
            if ($this->gen_program == 'GeneWeb' && $this->buffer[0] == '1 BAPM') {
                $this->level[1] = 'CHR';
                $this->buffer[0] = '1 CHR';
                $this->buffer[5] = '1 CHR';
                $this->buffer[6] = '1 CHR';
            }

            //$this->buffer[0] = str_replace("1 CHR ", "1 CHR", $this->buffer[0]);  // For Aldfaer etc.
            if ($this->level[1] == 'CHR') {
                if ($this->buffer[5] === '1 CHR') {
                    $this->processed = true;
                }

                // HZ-21
                // 1 CHR Ned. Herv.
                if ($this->buffer[5] === '1 CHR' && substr($this->buffer[0], 7)) {
                    $this->processed = true;
                    $pers_religion = substr($this->buffer[0], 7);
                }

                // *** Date ***
                //if ($this->buffer[6]=='2 DATE'){
                //  $this->processed = true;
                //  $pers_bapt_date=substr($this->buffer[0], 7);
                //}
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    //dorotree programm can have multiple bapt dates, only first one is saved:
                    if ($pers_bapt_date === '' || $pers_bapt_date === '0') {
                        $pers_bapt_date = trim(substr($this->buffer[0], 7));
                    }
                }

                // *** Place ***
                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $pers_bapt_place = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($pers_bapt_place);
                }

                // *** Texts ***
                if ($this->level[2] == 'NOTE') {
                    $pers_bapt_text = $this->process_texts($pers_bapt_text, '2');
                }

                if ($this->gen_program == "SukuJutut" && $this->level[3] == 'NOTE') {
                    $pers_bapt_text = $this->process_texts($pers_bapt_text, '3');
                }

                // *** Process sources for Pro-gen and Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_bapt_source', $pers_gedcomnumber, '2');
                }

                // *** Baptise witnesses GEDCOM 5.x. ***
                // Pro-gen: 2 _WITN Anna van Wely
                if ($this->buffer[7] === '2 _WITN') {
                    $this->buffer[0] = str_replace("2 _WITN", "2 WITN", $this->buffer[0]);
                    $this->level[2] = 'WITN';
                }
                if ($this->level[2] == 'WITN') {
                    if (substr($this->buffer[0], 2, 4) === 'WITN') {
                        $this->processed = true;
                        $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                        $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                        $this->buffer[0] = trim($this->buffer[0]);
                        $this->event_nr++;
                        $this->event_nr1 = $this->event_nr;
                        $this->calculated_event_id++;

                        // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                        $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                        $this->event['level'][$this->event_nr1] = '1';
                        //$this->event['connect_kind'][$this->event_nr1] = 'person';
                        $this->event['connect_kind'][$this->event_nr1] = 'CHR';
                        $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                        $this->event['connect_kind2'][$this->event_nr1] = '';
                        $this->event['connect_id2'][$this->event_nr1] = '';

                        //$this->event['kind'][$this->event_nr1] = 'baptism_witness';
                        $this->event['kind'][$this->event_nr1] = 'ASSO';
                        $this->event['event'][$this->event_nr1] = '';

                        if (substr($this->buffer[0], 7, 1) === '@') {
                            // 2 WITN @I1@
                            $this->event['connect_kind2'][$this->event_nr1] = 'person';
                            $this->event['connect_id2'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                        } else {
                            // 2 WITN Doopgetuige1//
                            $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                        }

                        $this->event['event_extra'][$this->event_nr1] = '';
                        $this->event['gedcom'][$this->event_nr1] = 'WITN';
                        $this->event['date'][$this->event_nr1] = '';
                        $this->event['text'][$this->event_nr1] = '';
                        $this->event['place'][$this->event_nr1] = '';
                    }

                    // Haza-data uses "i.p.v." (instead of).
                    // 2 WITN Doopgetuige1//
                    // 3 TYPE locum
                    // 2 WITN Doopgetuige2//
                    if (substr($this->buffer[0], 0, 12) === '3 TYPE locum') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= " i.p.v. ";
                    }

                    if ($this->buffer[6] === '3 CONT') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                    if ($this->buffer[6] === '3 CONC') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                }


                // GEDCOM 5.x. Doopheffer/ Godfather. Geneanet-Geneweb
                // 1 BAPM (or 1 CHR)
                // 2 ASSO @I2334@
                // 3 TYPE INDI
                // 3 RELA GODP

                // GEDCOM 7.x
                // 1 CHR
                // 2 ASSO @I7@
                // 3 ROLE GODP (only use GEDCOM 7.x. defined list: WITN, CLERGY, etc.)

                // *** Oct. 2024: New function to process ASSO ***
                if ($this->level[2] == 'ASSO') {
                    $this->process_association($pers_gedcomnumber, 'CHR');
                }

                // *** Religion (1 RELI = event) ***
                if ($this->buffer[6] === '2 RELI') {
                    $this->processed = true;
                    $pers_religion = substr($this->buffer[0], 7);
                }

                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_bapt');
                }
            }

            /**
             * Deceased
             */
            if ($this->level[1] == 'DEAT') {
                if ($this->buffer[6] === '1 DEAT') {
                    $this->processed = true;
                }

                // Aldfaer uses DEAT without further data!
                // if ($this->gen_program=='ALDFAER') { $pers_alive='deceased'; }
                // Legacy death without further date. "1 DEAT Y"
                $pers_alive = 'deceased';

                // *** Date ***
                //if ($this->buffer[6]=='2 DATE'){
                //  $this->processed = true;
                //  $pers_death_date=substr($this->buffer[0], 7);
                //}
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    //dorotree programm can have multiple death dates, only first one is saved:
                    if (!$pers_death_date) {
                        $pers_death_date = trim(substr($this->buffer[0], 7));
                    }
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $this->processed = true;
                    $pers_death_date_hebnight = substr($this->buffer[0], 8);
                }
                // *** Aldfaer time ***
                // 2 _ALDFAER_TIME 08:00:00
                if (substr($this->buffer[0], 0, 15) === '2 _ALDFAER_TIME') {
                    $this->processed = true;
                    $pers_death_time = substr($this->buffer[0], 16);
                }
                // *** Pro-gen time ***
                // 2 TIME 22.40
                if ($this->buffer[6] === '2 TIME') {
                    $this->processed = true;
                    $pers_death_time = substr($this->buffer[0], 7);
                }

                // *** Place ***
                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $pers_death_place = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($pers_death_place);
                }

                // *** Texts ***
                if ($this->level[2] == 'NOTE') {
                    $pers_death_text = $this->process_texts($pers_death_text, '2');
                }

                if ($this->gen_program == "Sukujutut" && $this->level == 'NOTE') {
                    // *** Texts ***
                    $pers_death_text = $this->process_texts($pers_death_text, '3');
                }

                // *** Process source for Pro-gen, Aldfaer, etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_death_source', $pers_gedcomnumber, '2');
                }

                // *** Death witness Pro-Gen ***
                // Pro-gen: 2 _WITN Anna van Wely
                if ($this->buffer[7] === '2 _WITN') {
                    $this->buffer[0] = str_replace("2 _WITN", "2 WITN", $this->buffer[0]);
                    $this->level[2] = 'WITN';
                }
                if ($this->level[2] == 'WITN') {
                    if (substr($this->buffer[0], 2, 4) === 'WITN') {

                        // TODO split into declaration and declaration_witness.

                        $this->processed = true;
                        $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                        $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                        $this->buffer[0] = trim($this->buffer[0]);
                        $this->event_nr++;
                        $this->event_nr1 = $this->event_nr;
                        $this->calculated_event_id++;

                        // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                        $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                        $this->event['level'][$this->event_nr1] = '1';
                        //$this->event['connect_kind'][$this->event_nr1] = 'person';
                        $this->event['connect_kind'][$this->event_nr1] = 'death_declaration';
                        $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                        $this->event['connect_kind2'][$this->event_nr1] = '';
                        $this->event['connect_id2'][$this->event_nr1] = '';

                        //$this->event['kind'][$this->event_nr1] = 'death_declaration';
                        $this->event['kind'][$this->event_nr1] = 'ASSO';
                        $this->event['event'][$this->event_nr1] = '';

                        if (substr($this->buffer[0], 7, 1) === '@') {
                            // 2 WITN @I1@
                            $this->event['connect_kind2'][$this->event_nr1] = 'person';
                            $this->event['connect_id2'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                        } else {
                            // 2 WITN Doopgetuige1//
                            $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                        }

                        $this->event['event_extra'][$this->event_nr1] = '';
                        $this->event['gedcom'][$this->event_nr1] = 'WITN';
                        $this->event['date'][$this->event_nr1] = '';
                        $this->event['text'][$this->event_nr1] = '';
                        $this->event['place'][$this->event_nr1] = '';
                    }
                    if ($this->buffer[6] === '3 CONT') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                    if ($this->buffer[6] === '3 CONC') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                }

                // *** Pers_death_cause ***
                if ($this->buffer[6] === '2 CAUS') {
                    $this->processed = true;
                    $pers_death_cause = rtrim(substr($this->buffer[0], 7));
                }

                // *** Pers_death_age ***
                if ($this->buffer[5] === '2 AGE') {
                    $this->processed = true;
                    $person["pers_death_age"] = substr($this->buffer[0], 6);
                }

                // *** Pers_death_cause Haza-data ***
                if ($this->buffer[6] === '2 TYPE') {
                    $this->processed = true;
                    $pers_death_cause = rtrim(substr($this->buffer[0], 7));
                }
                if ($pers_death_cause === 'died single') {
                    $pers_death_cause = 'died unmarried';
                }

                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_death');
                }
            }

            /**
             * Burial
             */

            //Pro-gen:
            //1 CREM
            //2 DATE 02 MAY 2003
            //2 PLAC Schagen

            if ($this->buffer[6] === '1 CREM') {
                $this->level[1] = 'BURI';
                $this->buffer[0] = '2 TYPE cremation';
            }
            if ($this->level[1] == 'BURI') {
                if ($this->buffer[6] === '1 BURI') {
                    $this->processed = true;
                }

                // *** Set pers_alive setting ***
                $pers_alive = 'deceased';

                // *** Date ***
                //if ($this->buffer[6]=='2 DATE'){
                //  $this->processed = true;
                //  $pers_buried_date=substr($this->buffer[0], 7);
                //}
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    //dorotree programm can have multiple burial dates, only first one is saved:
                    if (!$pers_buried_date) {
                        $pers_buried_date = trim(substr($this->buffer[0], 7));
                    }
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $pers_heb_flag = 1;
                    $this->processed = true;
                    $pers_buried_date_hebnight = substr($this->buffer[0], 8);
                }
                // *** Place ***
                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $pers_buried_place = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($pers_buried_place);
                }

                // *** Texts ***
                if ($this->level[2] == 'NOTE') {
                    $pers_buried_text = $this->process_texts($pers_buried_text, '2');
                }

                if ($this->gen_program == "Sukujutut" && $this->level[3] == 'NOTE') {
                    // *** Texts ***
                    $pers_buried_text = $this->process_texts($pers_buried_text, '3');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_buried_source', $pers_gedcomnumber, '2');
                }

                // *** Burial witness Pro-Gen ***
                // Pro-gen: 2 _WITN Anna van Wely
                if ($this->buffer[7] === '2 _WITN') {
                    $this->buffer[0] = str_replace("2 _WITN", "2 WITN", $this->buffer[0]);
                    $this->level[2] = 'WITN';
                }
                if ($this->level[2] == 'WITN') {
                    if (substr($this->buffer[0], 2, 4) === 'WITN') {
                        $this->processed = true;
                        $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                        $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                        $this->buffer[0] = trim($this->buffer[0]);
                        $this->event_nr++;
                        $this->event_nr1 = $this->event_nr;
                        $this->calculated_event_id++;

                        // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                        $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                        $this->event['level'][$this->event_nr1] = '1';
                        //$this->event['connect_kind'][$this->event_nr1] = 'person';
                        $this->event['connect_kind'][$this->event_nr1] = 'BURI';
                        $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                        $this->event['connect_kind2'][$this->event_nr1] = '';
                        $this->event['connect_id2'][$this->event_nr1] = '';

                        //$this->event['kind'][$this->event_nr1] = 'burial_witness';
                        $this->event['kind'][$this->event_nr1] = 'ASSO';
                        $this->event['event'][$this->event_nr1] = '';

                        if (substr($this->buffer[0], 7, 1) === '@') {
                            // 2 WITN @I1@
                            $this->event['connect_kind2'][$this->event_nr1] = 'person';
                            $this->event['connect_id2'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                        } else {
                            // 2 WITN Doopgetuige1//
                            $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                        }

                        $this->event['event_extra'][$this->event_nr1] = '';
                        $this->event['gedcom'][$this->event_nr1] = 'WITN';
                        $this->event['date'][$this->event_nr1] = '';
                        $this->event['text'][$this->event_nr1] = '';
                        $this->event['place'][$this->event_nr1] = '';
                    }
                    if ($this->buffer[6] === '3 CONT') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                    if ($this->buffer[6] === '3 CONC') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                        $this->buffer[0] = ''; // to prevent use of this text in other text!
                    }
                }

                // Oct. 2024: New function to process ASSO.
                if ($this->level[2] == 'ASSO') {
                    $this->process_association($pers_gedcomnumber, 'BURI');
                }

                // *** Method of burial ***
                if (substr($this->buffer[0], 0, 16) === '2 TYPE cremation') {
                    $this->processed = true;
                    $pers_cremation = '1';
                }
                if (substr($this->buffer[0], 0, 16) === '2 TYPE resomated') {
                    $this->processed = true;
                    $pers_cremation = 'R';
                }
                // 2 TYPE sailor's grave in GEDOM file
                if (substr($this->buffer[0], 0, 13) === '2 TYPE sailor') {
                    $this->processed = true;
                    $pers_cremation = 'S';
                }
                // 2 TYPE donated to science in GEDCOM file
                if (substr($this->buffer[0], 0, 14) === '2 TYPE donated') {
                    $this->processed = true;
                    $pers_cremation = 'D';
                }

                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_buried');
                }
            }

            // *** Aldfaer witnesses GEDCOM 5.5.1 ***
            // *** Oct. 2024: BE AWARE Aldfaer GEDCOM 5.x is probably wrong. ASSO should be by main person, not by witness. ***
            // *** This is changed in Aldfaer GEDCOM 7! ***
            // Aldfaer birth declaration (by person who did the declaration)
            //  1 _SORTCHILD
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA birth registration

            // Aldfaer baptise
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA baptize

            //  Aldfaer death registration (by person who did the registration)
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA death registration

            //  Aldfaer
            //  1 ASSO @I1281@
            //  2 TYPE INDI
            //  2 RELA burial

            // Aldfaer witnesses marriage, @F2612 is not officially allowed:
            //  1 ASSO @F2612@
            //  2 TYPE FAM
            //  2 RELA civil
            //  3 NOTE INDI I1281

            // Aldfaer witnesses religous marriage, @F2612 is not officially allowed:
            //  1 ASSO @F2612@
            //  2 TYPE FAM
            //  2 RELA religious
            //  3 NOTE INDI I1281

            // Geneanet/ geneweb, @F2612 is not officially allowed
            //  1 ASSO @F101@
            //  2 TYPE FAM
            //  2 RELA witness

            // GEDCOM 7.x
            // 1 CHR
            // 2 ASSO @I7@
            // 3 ROLE GODP (only use GEDCOM 7.x. defined list: WITN, CLERGY, etc.)

            // GEDCOM 7.x ASSO example:
            // 1 ASSO @VOID@
            // 2 PHRASE Mr Stockdale -> event_event
            // 2 ROLE OTHER
            // 3 PHRASE Teacher -> event_event_extra

            // THIS CODE IS USED FOR Aldfaer GEDCOM 5.x.
            if ($this->level[1] == 'ASSO') {
                if ($this->buffer[6] === '1 ASSO') {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';

                    // *** Changed in jan. 2023 ***
                    // Jan 2024: Database example
                    // event_connect_id = I1 (main person)
                    // event_connect_id2 = I2012 (witness)

                    // 1 ASSO only used in Aldfaer GEDCOM 5.x?

                    // *** Oct. 2024: BE AWARE Aldfaer GEDCOM 5.x is probably wrong. ASSO should be by main person, not by witness. ***
                    // *** This is changed in Aldfaer GEDCOM 7! ***
                    // TODO check for GEDCOM 5 or 7 AND check for Aldfaer GEDCOM.

                    // TODO if this code is used for GEDCOM 7: switch connect_id and connect_id2.
                    //$this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_kind'][$this->event_nr1] = 'BURI';
                    $this->event['connect_id'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                    $this->event['connect_kind2'][$this->event_nr1] = 'person';
                    $this->event['connect_id2'][$this->event_nr1] = $pers_gedcomnumber;
                    //}
                    // *** This code isn't needed? OR: use this code for HuMo-gen. ***
                    //else{
                    //$this->event['connect_kind'][$this->event_nr1] = 'person';
                    //$this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    //$this->event['connect_kind2'][$this->event_nr1] = 'person';
                    //$this->event['connect_id2'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                    //}

                    $this->event['kind'][$this->event_nr1] = 'ASSO';
                    $this->event['event'][$this->event_nr1] = '';
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = 'WITN';
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 TYPE INDI') {
                    $this->processed = true;
                    if ($this->add_tree == true || $this->reassign == true) {
                        $this->event['connect_id'][$this->event_nr1] = $this->reassign_ged($this->event['connect_id'][$this->event_nr1], 'I');

                        //TODO this line isn't tested yet.
                        $this->event['connect_id2'][$this->event_nr1] = $this->reassign_ged($this->event['connect_id2'][$this->event_nr1], 'I');
                    }
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 TYPE FAM') {
                    $this->processed = true;
                    //$this->event['connect_kind'][$this->event_nr1] = 'family';
                    $this->event['connect_kind'][$this->event_nr1] = 'MARR';
                    if ($this->add_tree == true || $this->reassign == true) {
                        $this->event['connect_id'][$this->event_nr1] = $this->reassign_ged($this->event['connect_id'][$this->event_nr1], 'F');

                        //TODO this line isn't tested yet.
                        $this->event['connect_id2'][$this->event_nr1] = $this->reassign_ged($this->event['connect_id2'][$this->event_nr1], 'F');
                    }
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA birth registration') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'birth_declaration';
                    $this->event['connect_kind'][$this->event_nr1] = 'birth_declaration';
                    $this->event['kind'][$this->event_nr1] = 'ASSO';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA baptize') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'baptism_witness';
                    $this->event['connect_kind'][$this->event_nr1] = 'CHR';
                    $this->event['kind'][$this->event_nr1] = 'ASSO';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA death registration') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'death_declaration';
                    $this->event['connect_kind'][$this->event_nr1] = 'death_declaration';
                    $this->event['kind'][$this->event_nr1] = 'ASSO';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA burial') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'burial_witness';
                    $this->event['connect_kind'][$this->event_nr1] = 'BURI';
                    $this->event['kind'][$this->event_nr1] = 'ASSO';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA civil') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'marriage_witness';
                    $this->event['connect_kind'][$this->event_nr1] = 'MARR';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA witness') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'marriage_witness';
                    $this->event['connect_kind'][$this->event_nr1] = 'MARR';
                }
                // GEDCOM 5.x
                if ($this->buffer[0] == '2 RELA religious') {
                    $this->processed = true;
                    //$this->event['kind'][$this->event_nr1] = 'marriage_witness_rel';
                    $this->event['connect_kind'][$this->event_nr1] = 'MARR_REL';
                }
            }

            /**
             * Occupation
             */
            if ($this->level[1] == 'OCCU') {
                if ($this->buffer[6] === '1 OCCU') {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'profession';
                    $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = 'OCCU';
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }

                // *** Occupation, Haza-21 uses empty OCCU events... Isn't strange? ***
                // 1 OCCU lerares
                if ($this->level[1] == 'OCCU') {
                    if ($this->buffer[6] === '1 OCCU' && substr($this->buffer[0], 7)) {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    }
                    // *** Long occupation ***
                    if ($this->buffer[6] === '2 CONT') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                    }
                    if ($this->buffer[6] === '2 CONC') {
                        $this->processed = true;
                        $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                    }
                }

                // *** Text by occupation ***
                if ($this->level[2] == 'NOTE') {
                    $this->event['text'][$this->event_nr1] = $this->process_texts($this->event['text'][$this->event_nr1], '2'); // BK
                }

                // *** Occupation in iFamily program ***
                // 1 OCCU
                // 2 TYPE baas van de herberg
                if ($this->buffer[6] === '2 TYPE') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr1] .= substr($this->buffer[0], 7);
                }

                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr1] = substr($this->buffer[0], 7);
                } // BK
                if ($this->buffer[6] === '2 PLAC') {
                    $this->processed = true;
                    $this->event['place'][$this->event_nr1] = substr($this->buffer[0], 7);
                }

                // *** Source by person occupation ***
                if ($this->level[2] == 'SOUR') {
                    $event_id = $this->event['calculated_event_id'][$this->event_nr1];
                    $this->process_sources('person', 'pers_event_source', $event_id, '2');
                }
            }

            // *** BK, FTM, HuMo-genealogy & Aldfaer: 1 RELI RK ***
            // *** Nov. 2022 religion now saved as event ***
            if ($this->level[1] == 'RELI') {
                if ($this->buffer[6] === '1 RELI') {
                    //$this->processed = true; $pers_religion=substr($this->buffer[0], 7);

                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'religion';
                    $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 7);
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = 'RELI';
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }

                if ($this->buffer[6] === '2 CONT') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                    $this->buffer[0] = ''; // to prevent use of this text in other text!
                }
                if ($this->buffer[6] === '2 CONC') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                    $this->buffer[0] = ''; // to prevent use of this text in other text!
                }

                // *** Text by occupation ***
                if ($this->level[2] == 'NOTE') {
                    $this->event['text'][$this->event_nr1] = $this->process_texts($this->event['text'][$this->event_nr1], '2');
                }

                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr1] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 PLAC') {
                    $this->processed = true;
                    $this->event['place'][$this->event_nr1] = substr($this->buffer[0], 7);
                }

                // *** Source by person occupation ***
                if ($this->level[2] == 'SOUR') {
                    $event_id = $this->event['calculated_event_id'][$this->event_nr1];
                    $this->process_sources('person', 'pers_event_source', $event_id, '2');
                }
            }

            // *** Pictures by person ***

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
            if ($this->level[1] == 'OBJE') {
                $this->process_picture('person', $pers_gedcomnumber, 'picture');
            }

            // *** Haza-data pictures ***
            //1 PHOTO @#Aplaatjes\beert&id.jpg jpg@
            if ($this->level[1] == 'PHOTO') {
                if ($this->buffer[7] === '1 PHOTO') {
                    $this->processed = true;
                    $photo = substr($this->buffer[0], 11, -6);
                    $photo = $this->humo_basename($photo);

                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'picture';
                    $this->event['event'][$this->event_nr1] = $photo;
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = 'PHOTO';
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';
                    $this->event['place'][$this->event_nr1] = '';
                }
                if ($this->buffer[6] === '2 DSCR' || $this->buffer[6] === '2 NAME') {
                    $this->processed = true;
                    $this->event['text'][$this->event_nr1] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr1] = substr($this->buffer[0], 7);
                }
            }

            // *** Sex: F or M ***
            if (substr($this->level[1], 0, 3) === 'SEX') {
                // *** 1 SEX F/ 1 SEX M ***
                if ($this->buffer[5] === '1 SEX') {
                    $this->processed = true;
                    $pers_sexe = substr($this->buffer[0], 6);
                }
                // *** Source by person sex ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('person', 'pers_sexe_source', $pers_gedcomnumber, '2');
                }
            }

            // *** Colour mark by a person ***
            // 1 _COLOR 1
            if ($this->buffer[8] === '1 _COLOR') {
                $this->processed = true;
                $this->event_nr++;
                $this->event_nr1 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr1] = '1';
                $this->event['connect_kind'][$this->event_nr1] = 'person';
                $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                $this->event['connect_kind2'][$this->event_nr1] = '';
                $this->event['connect_id2'][$this->event_nr1] = '';
                $this->event['kind'][$this->event_nr1] = 'person_colour_mark';
                $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 9);
                $this->event['event_extra'][$this->event_nr1] = '';
                $this->event['gedcom'][$this->event_nr1] = '_COLOR';
                $this->event['date'][$this->event_nr1] = '';
                $this->event['text'][$this->event_nr1] = '';
                $this->event['place'][$this->event_nr1] = '';
            }

            // *******************
            // *** Save events ***
            // *******************

            // *** Gramps ***
            // 1 FACT Aaron ben Halevy
            // 2 TYPE Hebrew Name
            if ($this->buffer[6] === '1 FACT') {
                $this->buffer[0] = '1 EVEN ' . substr($this->buffer[0], 7);
                $this->buffer[6] = '1 EVEN';
                $this->level[1] = 'EVEN';
                $fact = true;
            }

            // *** Aldfaer not married ***
            if ($this->buffer[0] == '1 _NOPARTNER') {
                $this->buffer[0] = '1 _NMAR';
                $this->buffer[7] = '1 _NMAR';
                $this->level[1] = '_NMAR';
            }

            // BK
            //1 _MILT militaire dienst  Location: Amsterdam
            //2 DATE 3 APR 1996
            //2 NOTE Goedgekeurd.
            //3 CONT 2nd line gebeurtenis bij persoon.
            //3 CONT 3rd line.
            if ($this->buffer[7] === '1 _MILT') {
                $this->buffer[0] = str_replace("_MILT", "MILI", $this->buffer[0]);
                $this->buffer[6] = '1 MILI';
                $this->level[1] = 'MILI';
            }

            // *** Check for event ***
            if ($this->buffer[6] === '1 ADOP') {
                // *** Adoption ***
                // 1 ADOP
                // 2 DATE 15 MAR 2025
                // 2 FAMC @F2@
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[7] === '1 _ADPF') {
                // Adopted by father
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[7] === '1 _ADPM') {
                // Adopted by mother
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 BAPL') {
                // LDS baptised
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 BARM') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 BASM') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 BLES') {
                // LDS blessed
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 CENS') {
                // Census
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 CHRA') {
                // LDS christened
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 CONF') {
                // LDS confirmed
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 CONL') {
                // LDS confirmation
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 EMIG') {
                // Emigration
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 ENDL') {
                // LDS endowment
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 FCOM') {
                // LDS first communion
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[7] === '1 _FNRL') {
                // LDS funeral
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 GRAD') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 IMMI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 NATU') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 ORDN') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 PROB') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 RETI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 SLGC') {
                // LDS sealing to spouse
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 WILL') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[7] === '1 _YART') {
                // Yahrzeit
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[7] === '1 _INTE') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[7] === '1 _BRTM') {
                // Brit Milah
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[7] === '1 _NLIV') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[7] === '1 _NMAR') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 NCHI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 MILI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 EDUC') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 NATI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 CAST') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 AFN') {
                $event_temp = $this->check_event(6);
            } elseif ($this->buffer[6] === '1 SSN') {
                $event_temp = $this->check_event(6);
            } elseif ($this->buffer[6] === '1 _PRMN') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 IDNO') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 _HEIG') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 _WEIG') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 _EYEC') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 _HAIR') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 DSCR') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 _MEDC') {
                $event_temp = $this->check_event(8);
            } elseif ($this->buffer[6] === '1 ANCI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 DESI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 PROP') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 ARVL') {
                // Arrived
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 BAPM') {
                // Baptised as child
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 DIVF') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 DPRT') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 LEGI') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 SLGL') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 TXPY') {
                $event_temp = $this->check_event(7);
            } elseif ($this->buffer[6] === '1 TITL') {
                $event_temp = $this->check_event(7);
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

            if ($this->level[1] == 'EVEN') {
                if ($this->buffer[6] === '1 EVEN') {
                    $this->processed = true;
                    // *** Process text after 1 EVEN ***
                    if (substr($this->buffer[0], 7)) {
                        $event_temp = substr($this->buffer[0], 7);
                        $this->processed = true;
                        $this->event_status = true;
                    }
                }

                // *** Haza-data ***
                // 1 EVEN
                // 2 TYPE living
                if (substr($this->buffer[0], 0, 13) === '2 TYPE living') {
                    $this->processed = true;
                    $pers_alive = 'alive';
                    $this->event_status = false;
                    $this->level[1] = '';
                }

                // *** Humo-genealogy ***
                // 1 EVEN
                // 2 TYPE deceased
                if (substr($this->buffer[0], 0, 15) === '2 TYPE deceased') {
                    $this->processed = true;
                    $pers_alive = 'deceased';
                    $this->event_status = false;
                    $this->level[1] = '';
                }

                // *** Rootsmagic ***
                // 1 EVEN Truck driver
                // 2 TYPE Militairy service
                // 2 DATE BET 15 FEB 1988 AND 15 AUG 1989
                // 2 PLAC Amsterdam
                // 2 ADDR Damrak 1
                if (substr($this->buffer[0], 0, 24) === '2 TYPE Militairy service') {
                    $this->event['gedcom'][$this->event_nr1] = 'MILI';
                }
            }

            if ($this->event_status) {
                if ($event_start) {
                    $event_start = '';
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr1] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr1] = '1';
                    $this->event['connect_kind'][$this->event_nr1] = 'person';
                    $this->event['connect_id'][$this->event_nr1] = $pers_gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr1] = '';
                    $this->event['connect_id2'][$this->event_nr1] = '';
                    $this->event['kind'][$this->event_nr1] = 'event';
                    $this->event['event'][$this->event_nr1] = '';
                    $this->event['event_extra'][$this->event_nr1] = '';
                    $this->event['gedcom'][$this->event_nr1] = $this->level[1];
                    $this->event['date'][$this->event_nr1] = '';
                    $this->event['text'][$this->event_nr1] = '';

                    // *** Aldfaer, title by name: 1 TITL Ir. ***
                    if ($this->level[1] == 'TITL') {
                        $this->event['kind'][$this->event_nr1] = 'title';
                    }

                    // Text by GEDCOM TAG of event:
                    // 1 _MILT militairy service
                    if (isset($event_temp)) {
                        //$this->event['text'][$this->event_nr1] = $this->merge_texts($this->event['text'][$this->event_nr1], ', ',$event_temp);
                        $this->event['event'][$this->event_nr1] = $this->merge_texts($this->event['text'][$this->event_nr1], ', ', $event_temp);
                        $event_temp = '';
                    }

                    $this->event['place'][$this->event_nr1] = '';
                }

                // *** Save event type ***
                if ($this->buffer[6] === '2 TYPE') {
                    // *** Gramps ***
                    // 1 FACT Aaron ben Halevy
                    // 2 TYPE Hebrew Name
                    if (isset($fact)) {
                        $this->processed = true;
                        $this->event['text'][$this->event_nr1] = substr($this->buffer[0], 7);
                    }

                    // *** For Aldfaer ***
                    // 1 EVEN
                    // 2 TYPE birth registration
                    // 2 DATE 21 FEB 1965
                    // 2 SOUR @S9@
                    if ($this->buffer[0] == '2 TYPE birth registration') {
                        $this->processed = true;
                        $this->event['kind'][$this->event_nr1] = 'birth_declaration';
                    }
                    if ($this->buffer[0] == '2 TYPE death registration') {
                        $this->processed = true;
                        $this->event['kind'][$this->event_nr1] = 'death_declaration';
                    }

                    // *** Aldfaer nobility (predikaat) by name ***
                    // 1 EVEN Jhr.
                    // 2 TYPE predikaat
                    if ($this->buffer[0] == '2 TYPE predikaat') {
                        $this->processed = true;
                        $this->event['kind'][$this->event_nr1] = 'nobility';
                    }

                    // *** Aldfaer, lordship (heerlijkheid) after a name: 1 PROP Heerlijkheid ***
                    if ($this->buffer[0] == '2 TYPE heerlijkheid') {
                        $this->processed = true;
                        $this->event['kind'][$this->event_nr1] = 'lordship';
                    }

                    // *** HZ-21, ash dispersion ***
                    if ($this->buffer[0] == '2 TYPE ash dispersion') {
                        $this->processed = true;
                        $this->event['kind'][$this->event_nr1] = 'ash dispersion';
                    }

                    // *** Legacy ***
                    if ($this->buffer[0] == '2 TYPE Property') {
                        $this->processed = true;
                        $this->event['gedcom'][$this->event_nr1] = 'PROP';
                    }

                    // *** Aldfaer if 1 EVEN has no text, but 2 TYPE has text, use that for title of event  ***
                    // The name of the event will be displayed as-is, in the language as entered in the Gedcom
                    // 1 EVEN
                    // 2 TYPE E-mail address
                    // 2 NOTE @N457@
                    // 1 EVEN
                    // 2 TYPE Telefoon
                    // 2 NOTE @N456@
                    if (substr($this->buffer[0], 7) && $this->level[1] == 'EVEN' && $this->event['kind'][$this->event_nr1] == 'event' && $this->event['event'][$this->event_nr1] == '') {
                        $this->processed = true;
                        $this->event['gedcom'][$this->event_nr1] = substr($this->buffer[0], 7) . ":";
                    }
                }

                // GEDCOM 7.x.
                // 1 EVEN
                // 2 TYPE birth registration (or death registration)
                // 2 DATE 2 JAN 1980
                // 2 ASSO @I4@

                // check for GEDCOM 7?
                if ($this->level[2] == 'ASSO') {
                    $this->process_association($pers_gedcomnumber);
                }

                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr1] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 PLAC') {
                    $this->processed = true;
                    $this->event['place'][$this->event_nr1] = substr($this->buffer[0], 7);
                }

                if ($this->level[2] == 'NOTE') {
                    $this->event['text'][$this->event_nr1] = $this->process_texts($this->event['text'][$this->event_nr1], '2');
                }

                // *** Ancestry FTM: Normally there is a 2 NOTE > 3 CONC/ 3 CONT structure... ***
                //1 IMMI
                //2 CONC John.
                if ($this->level[2] == 'CONT') {
                    $this->processed = true;
                    $this->event['text'][$this->event_nr1] .= $this->cont(substr($this->buffer[0], 7));
                }
                if ($this->level[2] == 'CONC') {
                    $this->processed = true;
                    $this->event['text'][$this->event_nr1] .= $this->conc(substr($this->buffer[0], 7));
                }

                // *** Aldfaer has source by event: 2 SOUR @S9@
                // *** Source by person event ***
                if ($this->level[2] == 'SOUR') {
                    if ($this->event['kind'][$this->event_nr1] == 'birth_declaration') {
                        $this->process_sources('person', 'birth_decl_source', $pers_gedcomnumber, '2');
                    } elseif ($this->event['kind'][$this->event_nr1] == 'death_declaration') {
                        $this->process_sources('person', 'death_decl_source', $pers_gedcomnumber, '2');
                    } else {
                        $event_id = $this->event['calculated_event_id'][$this->event_nr1];
                        $this->process_sources('person', 'pers_event_source', $event_id, '2');
                    }
                }

                // *** Picture by event ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE d:\Mijn documenten\Mijn Stamboom\Media\Afbeeldingen\henk.jpg
                // 3 _SCBK Y
                // 3 _PRIM Y
                // 3 _TYPE PHOTO
                // *** Picture is connected to event_id column (=$this->calculated_event_id) ***
                if ($this->level[2] == 'OBJE') {
                    $event_id = $this->event['calculated_event_id'][$this->event_nr1];
                    $this->process_picture('person', $pers_gedcomnumber, 'picture_event_' . $event_id);
                }

                // *** Added may 2025: Adoption ***
                // 1 ADOP
                // 2 DATE 15 MAR 2025
                // 2 FAMC @F2@
                if ($this->level[1] == 'ADOP' && $this->buffer[6] === '2 FAMC') {
                    $this->event['kind'][$this->event_nr1] = 'adoption';
                    $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 8, -1);
                }
            }

            // Process here because of: 2 TYPE living
            if ($this->buffer[0] == '1 EVEN') {
                $this->processed = true;
                $this->event_status = true;
            }

            //*** Person source ***
            //Haza-data
            //1 SOUR @S1@
            //2 ROLE Persoonskaart
            //2 DATE
            if ($this->level[1] == 'SOUR') {
                $this->process_sources('person', 'person_source', $pers_gedcomnumber, '1');
            }

            /**
             * Save non processed GEDCOM items
             */
            $this->buffer[0] = trim($this->buffer[0]);
            // Skip these lines
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }

            // Aldfaer picture info
            if (strtolower($this->buffer[0]) === '2 form jpg') {
                $this->processed = true;
            }

            if ($this->buffer[6] === '1 RIN ') {
                $this->processed = true;
            }
            if ($this->buffer[5] === '1 RFN') {
                $this->processed = true;
            }

            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }
                if ($person["pers_unprocessed_tags"]) {
                    $person["pers_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $person["pers_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $person["pers_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $person["pers_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $person["pers_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $person["pers_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }  //end explode

        if ($this->add_tree == true || $this->reassign == true) {
            if ($pers_name_text) {
                $pers_name_text = $this->reassign_ged($pers_name_text, 'N');
            }
            if ($person["pers_text"]) {
                $person["pers_text"] = $this->reassign_ged($person["pers_text"], 'N');
            }
            if ($pers_birth_text) {
                $pers_birth_text = $this->reassign_ged($pers_birth_text, 'N');
            }
            if ($pers_bapt_text) {
                $pers_bapt_text = $this->reassign_ged($pers_bapt_text, 'N');
            }
            if ($pers_death_text) {
                $pers_death_text = $this->reassign_ged($pers_death_text, 'N');
            }
            if ($pers_buried_text) {
                $pers_buried_text = $this->reassign_ged($pers_buried_text, 'N');
            }
        }

        // *** Process estimates/ calculated date for privacy filter ***
        if ($pers_birth_date) $person["pers_cal_date"] = $pers_birth_date;
        elseif ($pers_bapt_date) $person["pers_cal_date"] = $pers_bapt_date;

        // for Jewish dates after nightfall
        $heb_qry = '';
        if ($pers_heb_flag == 1) {
            // At least one nightfall date is imported. We have to make sure the required tables exist and if not create them
            $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_persons');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['pers_birth_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_birth_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['pers_death_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_death_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['pers_buried_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_buried_date;";
                $this->dbh->query($sql);
            }
            // we have to add these values to the query below
            $heb_qry .= "pers_birth_date_hebnight='" . $pers_birth_date_hebnight . "',
                pers_death_date_hebnight='" . $pers_death_date_hebnight . "', pers_buried_date_hebnight='" . $pers_buried_date_hebnight . "',";
        }

        // Lastname prefixes, THIS PART SLOWS DOWN READING A BIT!!!
        // Check for accent or space in pers_lastname...
        if (strpos($pers_lastname, "'") || strpos($pers_lastname, " ")) {
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
        $sql = "INSERT IGNORE INTO humo_persons SET
            pers_gedcomnumber = :pers_gedcomnumber,
            pers_tree_id = :pers_tree_id,
            pers_tree_prefix = :pers_tree_prefix,
            pers_fams = :pers_fams,
            pers_famc = :pers_famc,
            pers_firstname = :pers_firstname,
            pers_lastname = :pers_lastname,
            pers_name_text = :pers_name_text,
            pers_prefix = :pers_prefix,
            pers_patronym = :pers_patronym,
            pers_text = :pers_text,
            pers_birth_date = :pers_birth_date,
            pers_birth_time = :pers_birth_time,
            pers_birth_place = :pers_birth_place,
            pers_birth_text = :pers_birth_text,
            pers_stillborn = :pers_stillborn,
            pers_bapt_date = :pers_bapt_date,
            pers_bapt_place = :pers_bapt_place,
            pers_bapt_text = :pers_bapt_text,
            pers_religion = :pers_religion,
            pers_death_date = :pers_death_date,
            pers_death_time = :pers_death_time,
            pers_death_place = :pers_death_place,
            pers_death_text = :pers_death_text,
            pers_buried_date = :pers_buried_date,
            pers_buried_place = :pers_buried_place,
            pers_buried_text = :pers_buried_text,
            pers_cal_date = :pers_cal_date,
            pers_cremation = :pers_cremation,
            pers_death_cause = :pers_death_cause,
            pers_death_age = :pers_death_age,
            pers_sexe = :pers_sexe,
            pers_own_code = :pers_own_code,
            pers_quality = :pers_quality,
            pers_alive = :pers_alive,
            pers_new_user_id = :pers_new_user_id,
            pers_changed_user_id = :pers_changed_user_id,
            pers_new_datetime = :pers_new_datetime
            " . $this->changed_datetime('pers_changed_datetime', $person["changed_date"], $person["changed_time"]);

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':pers_gedcomnumber' => $pers_gedcomnumber,
            ':pers_tree_id' => $this->tree_id,
            ':pers_tree_prefix' => $this->tree_prefix,
            ':pers_fams' => $fams,
            ':pers_famc' => $pers_famc,
            ':pers_firstname' => $pers_firstname,
            ':pers_lastname' => $pers_lastname,
            ':pers_name_text' => $pers_name_text,
            ':pers_prefix' => $person["pers_prefix"],
            ':pers_patronym' => $person["pers_patronym"],
            ':pers_text' => $person["pers_text"],
            ':pers_birth_date' => $this->process_date($pers_birth_date),
            ':pers_birth_time' => $pers_birth_time,
            ':pers_birth_place' => $pers_birth_place,
            ':pers_birth_text' => $pers_birth_text,
            ':pers_stillborn' => $pers_stillborn,
            ':pers_bapt_date' => $this->process_date($pers_bapt_date),
            ':pers_bapt_place' => $pers_bapt_place,
            ':pers_bapt_text' => $pers_bapt_text,
            ':pers_religion' => $pers_religion,
            ':pers_death_date' => $this->process_date($pers_death_date),
            ':pers_death_time' => $pers_death_time,
            ':pers_death_place' => $pers_death_place,
            ':pers_death_text' => $pers_death_text,
            ':pers_buried_date' => $this->process_date($pers_buried_date),
            ':pers_buried_place' => $pers_buried_place,
            ':pers_buried_text' => $pers_buried_text,
            ':pers_cal_date' => $this->process_date($person["pers_cal_date"]),
            ':pers_cremation' => $pers_cremation,
            ':pers_death_cause' => $pers_death_cause,
            ':pers_death_age' => $person["pers_death_age"],
            ':pers_sexe' => $pers_sexe,
            ':pers_own_code' => $person["pers_own_code"],
            ':pers_quality' => $person["pers_quality"],
            ':pers_alive' => $pers_alive,
            ':pers_new_user_id' => $person["new_user_id"],
            ':pers_changed_user_id' => $person["changed_user_id"],
            ':pers_new_datetime' => date('Y-m-d H:i:s', strtotime($person["new_date"] . ' ' . $person["new_time"])),
        ]);

        // TODO debug isn't usefull anymore because prepared query is used
        if (isset($_POST['debug_mode']) && $_SESSION['debug_person'] < 2) {
            echo '<br>' . $sql . '<br>';
            $_SESSION['debug_person']++; // *** Only process debug line once ***
        }

        $pers_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($person["pers_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
            tag_pers_id = :tag_pers_id,
            tag_tree_id = :tag_tree_id,
            tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_pers_id' => $pers_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $person["pers_unprocessed_tags"]
            ]);
        }

        // *** Empty variable to free memory ***
        unset($person);


        // *** Save sources ***
        if ($this->nrsource > 0) {
            $sql = "INSERT IGNORE INTO humo_sources SET
                source_tree_id = :source_tree_id,
                source_gedcomnr = :source_gedcomnr,
                source_status = :source_status,
                source_title = :source_title,
                source_abbr = :source_abbr,
                source_date = :source_date,
                source_publ = :source_publ,
                source_place = :source_place,
                source_refn = :source_refn,
                source_auth = :source_auth,
                source_subj = :source_subj,
                source_item = :source_item,
                source_kind = :source_kind,
                source_text = :source_text,
                source_repo_name = :source_repo_name,
                source_repo_caln = :source_repo_caln,
                source_repo_page = :source_repo_page,
                source_repo_gedcomnr = :source_repo_gedcomnr,
                source_new_user_id = :source_new_user_id,
                source_changed_user_id = :source_changed_user_id,
                source_new_datetime = :source_new_datetime" . $this->changed_datetime('source_changed_datetime', $this->source["changed_date"][$i], $this->source["changed_time"][$i]);
            $stmt = $this->dbh->prepare($sql);

            for ($i = 1; $i <= $this->nrsource; $i++) {
                $stmt->execute([
                    ':source_tree_id' => $this->tree_id,
                    ':source_gedcomnr' => $this->source["source_gedcomnr"][$i],
                    ':source_status' => $this->source["source_status"][$i],
                    ':source_title' => $this->source["source_title"][$i],
                    ':source_abbr' => $this->source["source_abbr"][$i],
                    ':source_date' => $this->process_date($this->source["source_date"][$i]),
                    ':source_publ' => $this->source["source_publ"][$i],
                    ':source_place' => $this->source["source_place"][$i],
                    ':source_refn' => $this->source["source_refn"][$i],
                    ':source_auth' => $this->source["source_auth"][$i],
                    ':source_subj' => $this->source["source_subj"][$i],
                    ':source_item' => $this->source["source_item"][$i],
                    ':source_kind' => $this->source["source_kind"][$i],
                    ':source_text' => $this->source["source_text"][$i],
                    ':source_repo_name' => $this->source["source_repo_name"][$i],
                    ':source_repo_caln' => $this->source["source_repo_caln"][$i],
                    ':source_repo_page' => $this->source["source_repo_page"][$i],
                    ':source_repo_gedcomnr' => $this->source["source_repo_gedcomnr"][$i],
                    ':source_new_user_id' => $this->source["new_user_id"],
                    ':source_changed_user_id' => $this->source["changed_user_id"],
                    ':source_new_datetime' => date('Y-m-d H:i:s', strtotime($this->source["new_date"][$i] . ' ' . $this->source["new_time"][$i])),
                ]);
            }
            //$source_id=$this->dbh->lastInsertId();
            unset($this->source);
        }

        // *** Save unprocessed items ***
        /*
        if ($this->source["source_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_source_id = :tag_source_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
            ':tag_source_id' => $source_id,
            ':tag_tree_id' => $this->tree_id,
            ':tag_tag' => $this->source["source_unprocessed_tags"]
            ]);
        }
        */

        // *** Save addressses in separate table ***
        if ($this->nraddress2 > 0) {
            $gebeurtsql = "INSERT IGNORE INTO humo_addresses SET
                address_tree_id = :address_tree_id,
                address_gedcomnr = :address_gedcomnr,
                address_place = :address_place,
                address_address = :address_address,
                address_zip = :address_zip,
                address_phone = :address_phone,
                address_date = :address_date,
                address_text = :address_text";
            $stmt = $this->dbh->prepare($gebeurtsql);

            for ($i = 1; $i <= $this->nraddress2; $i++) {
                $stmt->execute([
                    ':address_tree_id' => $this->tree_id,
                    ':address_gedcomnr' => $this->address_array["gedcomnr"][$i],
                    ':address_place' => $this->address_array["place"][$i],
                    ':address_address' => $this->address_array["address"][$i],
                    ':address_zip' => $this->address_array["zip"][$i],
                    ':address_phone' => $this->address_array["phone"][$i],
                    ':address_date' => $this->address_array["date"][$i],
                    ':address_text' => $this->address_array["text"][$i],
                ]);
            }

            unset($this->address_array);
        }
        // unprocessed items?????


        // *** Store geolocations in humo_locations table ***
        if ($this->geocode_nr > 0) {
            for ($i = 1; $i <= $this->geocode_nr; $i++) {
                $stmt = $this->dbh->prepare("SELECT * FROM humo_location WHERE location_location = :location_location");
                $stmt->execute([':location_location' => $this->geocode_plac[$i]]);
                $loc_qry = $stmt;

                if (!$loc_qry->rowCount() && $this->geocode_type[$this->geocode_nr] != "") {
                    // doesn't appear in the table yet and the location belongs to birth, bapt, death or buried event) {  
                    $geosql = "INSERT IGNORE INTO humo_location SET
                        location_location = :location_location,
                        location_lat = :location_lat,
                        location_lng = :location_lng";
                    $stmt = $this->dbh->prepare($geosql);
                    $stmt->execute([
                        ':location_location' => $this->geocode_plac[$i],
                        ':location_lat' => $this->geocode_lati[$i],
                        ':location_lng' => $this->geocode_long[$i]
                    ]);
                }
            }
            // TODO: check if this is still needed.
            if (strpos($this->humo_option['geo_trees'], "@" . $this->tree_id . ";") === false) {
                $this->dbh->query("UPDATE humo_settings SET setting_value = CONCAT(setting_value,'@" . $this->tree_id . ";') WHERE setting_variable = 'geo_trees'");
                $this->humo_option['geo_trees'] .= "@" . $this->tree_id . ";";
            }
        }

        // *** Save events in seperate table ***
        if ($this->event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $this->event['kind']['1'];
            // Oct 2024
            $check_event_connect_kind = $this->event['connect_kind']['1'];
            for ($i = 1; $i <= $this->event_nr; $i++) {
                $event_order++;

                // *** Level 2 event (picture or association) ***
                if ($this->event['level'][$i] == '1') {
                    if ($check_event_kind != $this->event['kind'][$i]) {
                        $event_order = 1;
                        $check_event_kind = $this->event['kind'][$i];
                    }
                } else {
                    if ($check_event_kind != $this->event['kind'][$i] || $check_event_connect_kind != $this->event['connect_kind'][$i]) {
                        $event_order = 1;
                        $check_event_kind = $this->event['kind'][$i];
                        $check_event_connect_kind = $this->event['connect_kind'][$i];
                    }
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                    event_tree_id = :event_tree_id,
                    event_order = :event_order,
                    event_connect_kind = :event_connect_kind,
                    event_connect_id = :event_connect_id,";
                if (isset($this->event['connect_id2'][$i])) {
                    $gebeurtsql .= "
                    event_connect_kind2 = :event_connect_kind2,
                    event_connect_id2 = :event_connect_id2,";
                }
                $gebeurtsql .= "
                    event_kind = :event_kind,
                    event_event = :event_event,
                    event_event_extra = :event_event_extra,
                    event_gedcom = :event_gedcom,
                    event_date = :event_date,
                    event_text = :event_text,
                    event_place = :event_place";

                $params = [
                    ':event_tree_id' => $this->tree_id,
                    ':event_order' => $event_order,
                    ':event_connect_kind' => $this->event['connect_kind'][$i],
                    ':event_connect_id' => $this->event['connect_id'][$i],
                    ':event_kind' => $this->event['kind'][$i],
                    ':event_event' => $this->event['event'][$i],
                    ':event_event_extra' => $this->event['event_extra'][$i],
                    ':event_gedcom' => $this->event['gedcom'][$i],
                    ':event_date' => $this->process_date($this->event['date'][$i]),
                    ':event_text' => $this->event['text'][$i],
                    ':event_place' => $this->event['place'][$i],
                ];
                if (isset($this->event['connect_id2'][$i])) {
                    $params[':event_connect_kind2'] = $this->event['connect_kind2'][$i];
                    $params[':event_connect_id2'] = $this->event['connect_id2'][$i];
                }

                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute($params);

                //TEST LINES to check calculated_event_id.
                //echo $this->event['calculated_event_id'][$i] . ' ';
                //echo $this->dbh->lastInsertId() . ' ' . $this->event['kind'][$i] . '<br>';
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->event);
            //echo ' '.memory_get_usage().'@ ';
        }

        // *** Add a general source to all persons in this GEDCOM file (source_id is temporary number!) ***
        if ($this->humo_option["gedcom_read_add_source"] == 'y') {
            // *** Used for general numbering of connections ***
            $this->connect_nr++;
            $this->calculated_connect_id++;

            // *** Seperate numbering, because there can be sources by an address ***
            $address_connect_nr = $this->connect_nr;

            $this->connect['kind'][$this->connect_nr] = 'person';
            $this->connect['sub_kind'][$this->connect_nr] = 'person_source';
            $this->connect['connect_id'][$this->connect_nr] = $pers_gedcomnumber;
            $this->connect['source_id'][$this->connect_nr] = 'Stemporary';
            $this->connect['text'][$this->connect_nr] = '';
            $this->connect['item_id'][$this->connect_nr] = '';
            $this->connect['quality'][$this->connect_nr] = '';
            $this->connect['place'][$this->connect_nr] = '';
            $this->connect['page'][$this->connect_nr] = '';
            $this->connect['role'][$this->connect_nr] = '';
            $this->connect['date'][$this->connect_nr] = '';
        }

        // *** Save connections in seperate table ***
        if ($this->connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $this->connect['kind']['1'] . $this->connect['sub_kind']['1'] . $this->connect['connect_id']['1'];
            for ($i = 1; $i <= $this->connect_nr; $i++) {
                $connect_order++;
                if ($check_connect !== $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                if ($this->connect['sub_kind'][$i] == 'person_address' && isset($this->connect['connect_order'][$i])) {
                    $connect_order = $this->connect['connect_order'][$i];
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                    connect_tree_id = :connect_tree_id,
                    connect_order = :connect_order,
                    connect_kind = :connect_kind,
                    connect_sub_kind = :connect_sub_kind,
                    connect_connect_id = :connect_connect_id,
                    connect_source_id = :connect_source_id,
                    connect_quality = :connect_quality,
                    connect_item_id = :connect_item_id,
                    connect_text = :connect_text,
                    connect_page = :connect_page,
                    connect_role = :connect_role,
                    connect_date = :connect_date,
                    connect_place = :connect_place";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':connect_tree_id' => $this->tree_id,
                    ':connect_order' => $connect_order,
                    ':connect_kind' => $this->connect['kind'][$i],
                    ':connect_sub_kind' => $this->connect['sub_kind'][$i],
                    ':connect_connect_id' => $this->connect['connect_id'][$i],
                    ':connect_source_id' => $this->connect['source_id'][$i],
                    ':connect_quality' => $this->connect['quality'][$i],
                    ':connect_item_id' => $this->connect['item_id'][$i],
                    ':connect_text' => $this->connect['text'][$i],
                    ':connect_page' => $this->connect['page'][$i],
                    ':connect_role' => $this->connect['role'][$i],
                    ':connect_date' => $this->process_date($this->connect['date'][$i]),
                    ':connect_place' => $this->connect['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->connect);
            //$this->connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        // *** TEST ONLY: show processed time per person ***
        //echo ':'.(time()-$person_time).' '.(time()-$start_time).'<br>';

        //$process_time=time()-$person_time;
        //echo ':'.$process_time.'<br>';

    }


    /**
     * Process families
     */
    function process_family($family_array, $first_marr, $second_marr): void
    {
        //$line = $family_array;
        $line2 = explode("\n", $family_array);

        // 0 @F1@ FAM
        // 0 @F1389_1390@ FAM aldfaer

        unset($family);  //Reset de hele array

        // *** For source connect table ***
        $this->connect_nr = 0;

        $family["fam_religion"] = '';
        $family["fam_kind"] = '';
        $family["fam_text"] = '';
        $family["fam_marr_church_notice_date"] = '';
        $family["fam_marr_church_notice_place"] = '';
        $family["fam_marr_church_notice_text"] = '';
        $family["fam_marr_church_date"] = '';
        $family["fam_marr_church_place"] = '';
        $family["fam_marr_church_text"] = '';
        // *** Living together ***
        $family["fam_relation_date"] = '';
        $family["fam_relation_place"] = '';
        $family["fam_relation_text"] = '';
        $family["fam_relation_end_date"] = '';
        $family["fam_man_age"] = '';
        $family["fam_woman_age"] = '';
        $family["fam_marr_notice_date"] = '';
        $family["fam_marr_notice_place"] = '';
        $family["fam_marr_notice_text"] = '';
        $family["fam_marr_date"] = '';
        $family["fam_marr_place"] = '';
        $family["fam_marr_text"] = '';
        $family["fam_marr_authority"] = '';
        $family["fam_div"] = false;
        $family["fam_div_date"] = '';
        $family["fam_div_place"] = '';
        $family["fam_div_text"] = '';
        $family["fam_div_authority"] = '';
        $family["fam_cal_date"] = '';
        $family["fam_unprocessed_tags"] = '';

        $family["new_date"] = "1970-01-01";
        $family["new_time"] = "00:00:01";
        $family["new_user_id"] = '';

        $family["changed_date"] = '';
        $family["changed_time"] = '';
        $family["changed_user_id"] = '';

        $family["fam_marr_date_hebnight"] = '';
        $family["fam_marr_notice_date_hebnight"] = '';
        $family["fam_marr_church_date_hebnight"] = '';
        $family["fam_marr_church_notice_date_hebnight"] = '';
        $heb_flag = '';
        $fam_children = '';
        $fam_man = 0;
        $fam_woman = 0;

        $this->event_status = false;
        $this->event_nr = 0;

        // *** Save addresses in a seperate table ***
        $this->nraddress2 = 0;
        $this->address_order = 0;

        // *** Save sources in a seperate table ***
        $this->nrsource = 0;

        $this->geocode_nr = 0;

        // *** Process 1st line ***
        $this->buffer[0] = $line2[0];
        $gedcomnumber = substr($this->buffer[0], 3, -5);

        if ($second_marr > 0) {
            $gedcomnumber .= "U1";
        }  // create unique nr. if 1st is F23, then 2nd will be F23U1

        if ($this->add_tree == true || $this->reassign == true) {
            $gedcomnumber = $this->reassign_ged($gedcomnumber, 'F');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $gedcomnumber . ' ';
        }

        // *** Save Level0 ***
        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';
        $this->level['1a'] = '';

        $temp_kind = ''; // marriage kind

        // *** Process other lines ***
        $loop = count($line2) - 2;

        $marr_flag = 0;
        $count_civil_religion = 0;

        for ($z = 1; $z <= $loop; $z++) {

            if ($second_marr > 0 && $z >= $first_marr && $z < $second_marr) {
                continue;
            } // skip lines that belong to 1st marriage

            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  //newline strippen

            $this->set_buffer();

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 5));  //rtrim for DIV_/ CHR_
                $this->level['1a'] = rtrim($this->buffer[0]);  // *** Needed to test for 1 RESI @. Update: rtrim not really neccesary anymore? ***

                $this->event_status = false;
                $event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';

                // *** Same couple: second marriage in BK program (in 1 @FAM part) ***
                $search_marr = rtrim(substr($this->buffer[0], 2, 5));
                if ($this->gen_program == 'BROSKEEP' && ($search_marr === "MARR" || $search_marr === "MARB" || $search_marr === "MARL")) {
                    if ($marr_flag == 1 && $family["fam_div"] == true) {
                        // this is a second MARR in this @FAM after a divorce so second marriage of these people
                        $skipfrom = $z; // Added dec. 2024 to prevent error.
                        //function process_family($family_array, $first_marr, $second_marr): void
                        $this->process_family($family_array, $skipfrom, $z); // calls itself with parameters what to skip
                        break;
                    } elseif ($second_marr == 0) {
                        // this is regular first marriage of these people (usually the only one....:-)
                        if ($marr_flag == 0) {
                            // flag only position of first liason before DIV
                            $marr_flag = 1; // flag that a MARR/MARB/MARL has been encountered
                            $skipfrom = $z; // if 2nd MARR will be encountered after a DIV then from this line should be skipped on second run
                        }
                    }
                }
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            // *** Save date ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $family["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $family["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $family["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Save date ***
            // 1 CHAN
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $family["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $family["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $family["changed_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Witnesses ***
            // 1 WITN John/Doe/
            // 1 WITN Jane/Doe/
            if ($this->buffer[6] === '1 WITN') {
                $this->processed = true;
                $this->buffer[0] = str_replace("/", " ", $this->buffer[0]);
                $this->buffer[0] = str_replace("  ", " ", $this->buffer[0]);
                $this->buffer[0] = trim($this->buffer[0]);
                $this->event_nr++;
                $this->event_nr1 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr] = '1';
                //$this->event['connect_kind'][$this->event_nr] = 'family';
                $this->event['connect_kind'][$this->event_nr] = 'MARR';
                $this->event['connect_id'][$this->event_nr] = $gedcomnumber;
                $this->event['connect_kind2'][$this->event_nr] = '';
                $this->event['connect_id2'][$this->event_nr] = '';

                //$this->event['kind'][$this->event_nr] = 'marriage_witness';
                $this->event['kind'][$this->event_nr] = 'ASSO';
                $this->event['event'][$this->event_nr] = substr($this->buffer[0], 7);
                $this->event['event_extra'][$this->event_nr] = '';
                $this->event['gedcom'][$this->event_nr] = 'WITN';
                $this->event['date'][$this->event_nr] = '';
                $this->event['text'][$this->event_nr] = '';
                $this->event['place'][$this->event_nr] = '';
            }

            // *** Oct. 2024: New function to process ASSO. ***
            if ($this->level[2] == 'ASSO') {
                if ($temp_kind === 'religious') {
                    $this->process_association($gedcomnumber, 'MARR_REL');
                } else {
                    $this->process_association($gedcomnumber, 'MARR');
                }
            }

            // *** Type relation (LAT etc.) ***
            if ($this->buffer[6] === '1 TYPE') {
                $this->processed = true;
                $family["fam_kind"] = substr($this->buffer[0], 7);
            }

            // *** Gedcomnumber man: 1 HUSB @I14@ ***
            if ($this->buffer[8] === '1 HUSB @') {
                $this->processed = true;
                $fam_man = substr($this->buffer[0], 8, -1);
                if ($this->add_tree == true || $this->reassign == true) {
                    $fam_man = $this->reassign_ged($fam_man, 'I');
                }
                if ($second_marr > 0) {
                    $this->dbh->query("UPDATE humo_persons SET pers_fams = CONCAT(pers_fams,';','" . $gedcomnumber . "')
                        WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber = '" . $fam_man . "'");
                }
            }

            // *** Gedcomnumber woman: 1 WIFE @I14@ ***
            if ($this->buffer[8] === '1 WIFE @') {
                $this->processed = true;
                $fam_woman = substr($this->buffer[0], 8, -1);
                if ($this->add_tree == true || $this->reassign == true) {
                    $fam_woman = $this->reassign_ged($fam_woman, 'I');
                }
                if ($second_marr > 0) {
                    $this->dbh->query("UPDATE humo_persons SET pers_fams = CONCAT(pers_fams,';','" . $gedcomnumber . "')
                        WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber = '" . $fam_woman . "'");
                }
            }
            // *** Gedcomnumbers children ***
            // 1 CHIL @I13@
            // 1 CHIL @I14@
            if ($second_marr == 0) {
                // only show children in first marriage of same people
                if ($this->buffer[8] === '1 CHIL @') {
                    $this->processed = true;
                    $tempnum = substr($this->buffer[0], 8, -1);
                    if ($this->add_tree == true || $this->reassign == true) {
                        $tempnum = $this->reassign_ged($tempnum, 'I');
                    }
                    $fam_children = $this->merge_texts($fam_children, ';', $tempnum);
                }

                // *** Adoption by person, used in Legacy and RootsMagic ***
                // 2 _FREL Adopted ===>>> Adopted by father.
                // 2 _MREL Adopted ===>>> Adopted by mother.
                /*
                if ($this->buffer[7]=='2 _FREL' OR $this->buffer[7]=='2 _MREL'){
                    $this->processed = true;
                    $child_array=explode(";",$fam_children); $count_children=count($child_array);

                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr] = '1';
                    $this->event['connect_kind'][$this->event_nr]='person';
                    $this->event['connect_id'][$this->event_nr]=$child_array[$count_children-1];
                    $this->event['connect_kind2'][$this->event_nr] = '';
                    $this->event['connect_id2'][$this->event_nr] = '';
                    $this->event['kind'][$this->event_nr]='adoption_by_person';
                    $this->event['event'][$this->event_nr]=$gedcomnumber;
                    $this->event['event_extra'][$this->event_nr]='';
                    $this->event['gedcom'][$this->event_nr]=substr($this->buffer[0],8); // *** adopted, steph, legal or foster. ***
                    $this->event['date'][$this->event_nr]='';
                    //$this->event['source'][$this->event_nr]='';
                    $this->event['text'][$this->event_nr]='';
                    $this->event['place'][$this->event_nr]='';

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

            /**
             * Marriage license church
             */

            // *** Marriage license Aldfaer ***
            if ($this->level[1] == 'MARL' && $this->gen_program == 'ALDFAER') {
                $this->level[1] = "MARB";
                if ($this->buffer[6] === '1 MARL') {
                    $this->processed = true;
                }
            }

            if ($this->level[1] == 'MARB' && $temp_kind === 'religious') {
                if ($this->buffer[6] === '1 MARB') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_marr_church_notice_date"])   $family["fam_marr_church_notice_date"] = trim(substr($this->buffer[0], 7));
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $heb_flag = 1;
                    $this->processed = true;
                    $family["fam_marr_church_notice_date_hebnight"] = substr($this->buffer[0], 8);
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_marr_church_notice_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_marr_church_notice_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_marr_church_notice_text"] = $this->process_texts($family["fam_marr_church_notice_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_church_notice_source', $gedcomnumber, '2');
                }

                // *** Pictures by marriage church notice ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_church_notice');
                }
            }

            /**
             * Marriage license
             */
            if ($this->level[1] == 'MARB' && $temp_kind !== 'religious') {
                // *** Type marriage / relation (civil or religious) ***
                if ($this->buffer[6] === '2 TYPE') {
                    $this->processed = true;
                    $temp_kind = strtolower(substr($this->buffer[0], 7));
                }
                if ($this->buffer[6] === '1 MARB') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_marr_notice_date"]) {
                        $family["fam_marr_notice_date"] = trim(substr($this->buffer[0], 7));
                    }
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $heb_flag = 1;
                    $this->processed = true;
                    $family["fam_marr_notice_date_hebnight"] = substr($this->buffer[0], 8);
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_marr_notice_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_marr_notice_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_marr_notice_text"] = $this->process_texts($family["fam_marr_notice_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_notice_source', $gedcomnumber, '2');
                }

                // *** Pictures by marriage licence ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_notice');
                }

                // *** SAME CODE IS USED FOR AGE BY MARRIAGE_NOTICE AND MARRIAGE! ***
                // *** Man age ***
                // 2 HUSB
                // 3 AGE 42y
                if ($this->level[2] == 'HUSB') {
                    if ($this->buffer[6] === '2 HUSB') {
                        $this->processed = true;
                    }
                    if ($this->buffer[5] === '3 AGE') {
                        $this->processed = true;
                        $family["fam_man_age"] = substr($this->buffer[0], 6);
                    }
                }
                // *** Woman age ***
                // 2 WIFE
                // 3 AGE 42y 6m
                if ($this->level[2] == 'WIFE') {
                    if ($this->buffer[6] === '2 WIFE') {
                        $this->processed = true;
                    }
                    if ($this->buffer[5] === '3 AGE') {
                        $this->processed = true;
                        $family["fam_woman_age"] = substr($this->buffer[0], 6);
                    }
                }
            }

            /**
             * Marriage church
             */
            if ($this->level[1] == 'MARR') {
                // *** fam_religion ***
                // Haza-data
                // 1 MARR
                // 2 TYPE religious
                // 2 RELI Hervormd
                if ($this->buffer[6] === '2 RELI') {
                    $this->processed = true;
                    $family["fam_religion"] = substr($this->buffer[0], 7);
                }

                // *** Haza-data marriage authority ***
                // 1 MARR
                // 2 AGNC alkmaar gemeente wettelijk
                if ($this->buffer[6] === '2 AGNC') {
                    $this->processed = true;
                    $family["fam_marr_authority"] = substr($this->buffer[0], 7);
                }

                // *** Type marriage / relation (civil or religious) ***
                if ($this->buffer[6] === '2 TYPE') {
                    $this->processed = true;
                    $temp_kind = strtolower(substr($this->buffer[0], 7));

                    // Ahnenblatt uses "2 TYPE RELI". Other programs: "2 TYPE religious"
                    if ($temp_kind == 'reli') {
                        $temp_kind = 'religious';
                    }

                    // *** Save marriage type in database, to show proper text if there is no further data.
                    //     Otherwise it will be "relation". ***
                    if ($family["fam_kind"] === '') {
                        $family["fam_kind"] = $temp_kind;
                    }

                    // *** Aldfaer relation ***
                    // 0 @F3027@ FAM
                    // 1 HUSB @I784@
                    // 1 WIFE @I258@
                    // 1 MARR
                    // 2 TYPE partners
                    if ($temp_kind === 'partners') {
                        $family["fam_kind"] = 'partners';
                        //$this->buffer[0] = '1 _LIV';
                        //$this->level[1] = '_LIV';
                    } elseif ($temp_kind === 'registered') {
                        $family["fam_kind"] = 'registered';
                        //$this->buffer[0] = '1 _LIV';
                        //$this->level[1] = '_LIV';
                    } elseif ($temp_kind === 'unknown') {
                        $family["fam_kind"] = 'unknown';
                        //$this->buffer[0] = '1 _LIV';
                        //$this->level[1] = '_LIV';
                    }
                }
            }

            // Pro-gen and GensdataPro, licence marriage church:
            // 1 ORDI
            // 2 DATE 01 JUN 1749
            // 2 PLAC Huizen
            // if (substr($this->buffer[0],0,1)=='1'){
            //   $temp_kind="";
            // }
            if ($this->buffer[6] === '1 ORDI') {
                $this->buffer[0] = "1 MARR";
                $temp_kind = "religious";
                $this->level[1] = 'MARR';
            }

            // *** Witnesses Pro-gen ***
            // 1 MARR
            // 2 DATE 21 MAY 1874
            // 2 PLAC Winsum
            // 2 _WITN Aam Pieter Borgman, oud 48 jaar, herbergier, wonende te
            // 3 CONT bla bla
            // 3 CONT bla bla
            if ($this->buffer[7] === '2 _WITN') {
                $this->buffer[0] = str_replace("2 _WITN", "2 WITN", $this->buffer[0]);
                $this->level[2] = 'WITN';
            }
            if ($this->level[2] == 'WITN') {
                if ($this->buffer[6] === '2 WITN') {
                    $this->processed = true;
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr] = '1';
                    //$this->event['connect_kind'][$this->event_nr] = 'family';
                    $this->event['connect_kind'][$this->event_nr] = 'MARR';
                    $this->event['connect_id'][$this->event_nr] = $gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr] = '';
                    $this->event['connect_id2'][$this->event_nr] = '';

                    //$this->event['kind'][$this->event_nr] = 'marriage_witness';
                    $this->event['kind'][$this->event_nr] = 'ASSO';
                    if ($temp_kind === 'religious') {
                        //$this->event['kind'][$this->event_nr] = 'marriage_witness_rel';
                        $this->event['connect_kind'][$this->event_nr] = 'MARR_REL';
                    }

                    $this->event['event'][$this->event_nr] = '';

                    if (substr($this->buffer[0], 7, 1) === '@') {
                        // 2 WITN @I1@
                        //$this->event['connect_kind2'][$this->event_nr] = 'family';
                        $this->event['connect_kind2'][$this->event_nr] = 'person';
                        $this->event['connect_id2'][$this->event_nr] = substr($this->buffer[0], 8, -1);
                    } else {
                        // 2 WITN Doopgetuige1//
                        $this->event['event'][$this->event_nr] = substr($this->buffer[0], 7);
                    }

                    $this->event['event_extra'][$this->event_nr] = '';
                    $this->event['gedcom'][$this->event_nr] = 'WITN';
                    $this->event['date'][$this->event_nr] = '';
                    $this->event['text'][$this->event_nr] = '';
                    $this->event['place'][$this->event_nr] = '';
                }
                if ($this->buffer[6] === '3 CONT') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr] .= $this->cont(substr($this->buffer[0], 7));
                    $this->buffer[0] = ''; // to prevent use of this text in text marriage!
                }
                if ($this->buffer[6] === '3 CONC') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr] .= $this->conc(substr($this->buffer[0], 7));
                    $this->buffer[0] = ''; // to prevent use of this text in text marriage!
                }
            }

            // Quick & dirty method to solve 2 TYPE problem in Ahnenblatt GEDCOM.
            // 2 TYPE isn't used directly after 1 MARR. So just assume 2nd MARR = religious.
            if ($this->buffer[6] === '1 MARR' && $this->gen_program == 'AHN' && $count_civil_religion > 0) {
                $temp_kind = 'religious'; // Just assume second MARR is religious.
            }

            if ($this->level[1] == 'MARR' && $temp_kind === 'religious') {
                if ($this->buffer[6] === '1 MARR') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_marr_church_date"])   $family["fam_marr_church_date"] = trim(substr($this->buffer[0], 7));
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $heb_flag = 1;
                    $this->processed = true;
                    $family["fam_marr_church_date_hebnight"] = substr($this->buffer[0], 8);
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_marr_church_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_marr_church_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_marr_church_text"] = $this->process_texts($family["fam_marr_church_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_church_source', $gedcomnumber, '2');
                }

                // *** Pictures by marriage church ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('family', $gedcomnumber, 'picture_fam_marr_church');
                }
            }

            /**
             * Marriage
             */
            if ($this->level[1] == 'MARR' && $temp_kind !== 'religious' && $this->gen_program != 'SukuJutut') {

                if ($this->buffer[6] === '1 MARR') {
                    $this->processed = true;

                    $count_civil_religion++; // Needed for Ahnenblatt.
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_marr_date"])  $family["fam_marr_date"] = trim(substr($this->buffer[0], 7));
                }
                if ($this->buffer[7] === '2 _HNIT') {
                    $heb_flag = 1;
                    $this->processed = true;
                    $family["fam_marr_date_hebnight"] = substr($this->buffer[0], 8);
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_marr_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_marr_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_marr_text"] = $this->process_texts($family["fam_marr_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_marr_source', $gedcomnumber, '2');
                }

                // *** Pictures by marriage ***
                // 2 OBJE
                // 3 FORM jpg
                // 3 FILE C:\Documents and Settings\Mijn documenten\test.jpg
                // 3 TITL test
                if ($this->level[2] == 'OBJE') {
                    $this->process_picture('family', $gedcomnumber, 'picture_fam_marr');
                }

                // *** SAME CODE IS USED FOR AGE BY MARRIAGE_NOTICE AND MARRIAGE! ***
                // *** Man age ***
                // 2 HUSB
                // 3 AGE 42y
                if ($this->level[2] == 'HUSB') {
                    if ($this->buffer[6] === '2 HUSB') {
                        $this->processed = true;
                    }
                    if ($this->buffer[5] === '3 AGE') {
                        $this->processed = true;
                        $family["fam_man_age"] = substr($this->buffer[0], 6);
                    }
                }
                // *** Woman age ***
                // 2 WIFE
                // 3 AGE 42y 6m
                if ($this->level[2] == 'WIFE') {
                    if ($this->buffer[6] === '2 WIFE') {
                        $this->processed = true;
                    }
                    if ($this->buffer[5] === '3 AGE') {
                        $this->processed = true;
                        $family["fam_woman_age"] = substr($this->buffer[0], 6);
                    }
                }
            }

            // Finnish program SukuJutut uses its own code for type of relation
            if ($this->level[1] == 'MARR' && $this->gen_program == 'SukuJutut') {
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $finrelation = 'marr';
                    if (substr($this->buffer[0], 7, 9) === 'AVOLIITTO') {
                        $family["fam_kind"] = "living together";
                        $finrelation = 'relation';
                    } elseif (substr($this->buffer[0], 7, 8) === 'LIITTO 2') {
                        $family["fam_kind"] = "living together";
                        $finrelation = 'relation';
                    } elseif (substr($this->buffer[0], 7, 8) === 'LIITTO 3') {
                        $family["fam_kind"] = "engaged";
                        $finrelation = 'relation';
                    } elseif (substr($this->buffer[0], 7, 8) === 'LIITTO 4') {
                        $family["fam_kind"] = "homosexual";
                        $finrelation = 'relation';
                    } elseif (substr($this->buffer[0], 7, 8) === 'LIITTO 5') {
                        $family["fam_kind"] = "unknown";
                        $finrelation = 'relation';
                    } elseif (substr($this->buffer[0], 7, 8) === 'LIITTO 6') {
                        $family["fam_kind"] = "non-marital"; // originally: father/mother of child (no relation - just conception)
                        $finrelation = 'relation';
                    } else {
                        $family["fam_marr_date"] = substr($this->buffer[0], 7);
                    }
                }
                if ($finrelation === 'relation' && isset($family["fam_marr_date"])) {
                    $family["fam_relation_date"] = $family["fam_marr_date"];
                    $family["fam_marr_date"] = '';
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_" . $finrelation . "_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_" . $finrelation . "_place"]);
                }

                //TODO check these lines.
                if ($this->level[2] == 'NOTE') {
                    //$family["fam_" . $finrelation . "_text"] = $this->process_texts($person["pers_text"], '2');
                    $family["fam_relation_text"] = $this->process_texts($family["fam_relation_text"], '2');
                }
                if ($this->level[3] == 'NOTE') {
                    //$family["fam_" . $finrelation . "_text"] = $this->process_texts($person["pers_text"], '3');
                    $family["fam_relation_text"] = $this->process_texts($family["fam_relation_text"], '3');
                }

                // ***  Process sources ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_' . $finrelation . '_source', $gedcomnumber, '2');
                }
            }

            /**
             * Living together
             */
            // NOT TESTED *** BK living together ***
            if ($this->buffer[7] === '1 _COML') {
                $this->processed = true;
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
            if ($this->buffer[6] === '1 _NMR') {
                $this->processed = true;
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
            if (substr($this->buffer[0], 0, 18) === '1 TYPE non-marital') {
                $this->buffer[0] = '1 _LIV';
                $this->level[1] = '_LIV';
            }

            // OR (Haza-data):
            // 1 TYPE living together
            // 1 _STRT
            // 2 DATE SEP 2005
            // 1 _END
            // 2 DATE 2006
            if ($this->level[1] == '_STRT') {
                if ($this->buffer[7] === '1 _STRT') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_relation_date"])  $family["fam_relation_date"] = trim(substr($this->buffer[0], 7));
                }
            }
            if ($this->level[1] == '_END') {
                if ($this->buffer[6] === '1 _END') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_relation_end_date"])  $family["fam_relation_end_date"] = trim(substr($this->buffer[0], 7));
                }
            }

            // *** Pro-gen & HuMo-genealogy living together: 1 _LIV ***
            //if ($this->buffer[6]=='1 _LIV'){
            //  $this->processed = true;
            //  $family["fam_kind"]="living together";
            //}
            if ($this->level[1] == '_LIV') {
                if ($this->buffer[6] === '1 _LIV') {
                    $this->processed = true;
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_relation_date"])  $family["fam_relation_date"] = substr($this->buffer[0], 7);
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_relation_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_relation_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_relation_text"] = $this->process_texts($family["fam_relation_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_relation_source', $gedcomnumber, '2');
                }
            }

            /**
             * Divorce
             * 
             * Divorce BK
             * 1 _SEPR
             * 2 DATE 1933
             * 2 SOUR @S326@
             * 2 NOTE He left his family.
             */
            if ($this->buffer[7] === '1 _SEPR') {
                $this->buffer[0] = str_replace("1 _SEPR", "1 DIV", $this->buffer[0]);
                $this->level[1] = 'DIV';
            }

            //if ($this->level[1]=='DIV'){
            if (substr($this->level[1], 0, 3) === 'DIV') {
                if (substr($this->buffer[0], 0, 5) === '1 DIV') {
                    $this->processed = true;
                    $family["fam_div"] = true;
                }

                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    if (!$family["fam_div_date"])  $family["fam_div_date"] = trim(substr($this->buffer[0], 7));
                }

                if ($this->level[2] == 'PLAC') {
                    if ($this->buffer[6] === '2 PLAC') {
                        $this->processed = true;
                        $family["fam_div_place"] = $this->process_place(substr($this->buffer[0], 7));
                    }
                    $this->process_places($family["fam_div_place"]);
                }

                if ($this->level[2] == 'NOTE') {
                    $family["fam_div_text"] = $this->process_texts($family["fam_div_text"], '2');
                }

                // *** Process sources for Pro-gen, Aldfaer etc. ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_div_source', $gedcomnumber, '2');
                }

                // *** Haza-data div. authority ***
                // 1 DIV
                // 2 AGNC alkmaar scheiding
                if ($this->buffer[6] === '2 AGNC') {
                    $this->processed = true;
                    $family["fam_div_authority"] = substr($this->buffer[0], 7);
                }
            }

            /**
             * Text by family
             */
            if ($this->level[1] == 'NOTE') {
                $family["fam_text"] = $this->process_texts($family["fam_text"], '1');

                // *** BK: source by family text ***
                if ($this->level[2] == 'SOUR') {
                    $this->process_sources('family', 'fam_text_source', $gedcomnumber, '2');
                }
            }

            // *** Pictures by family ***
            // 1 OBJE
            // 2 FORM jpg
            // 2 FILE C:\Documents and Settings\Mijn documenten\test.jpg
            // 2 TITL test
            //if ($this->level[1]=='OBJE') $this->process_picture('','','picture', $this->buffer[0]);
            if ($this->level[1] == 'OBJE') {
                $this->process_picture('family', $gedcomnumber, 'picture');
            }

            /**
             * Events
             *
             * 1 MILI
             * 2 TYPE militaire dienst
             * 2 DATE 01 JAN 1999
             * 2 NOTE test
             * 
             * 1 EVEN
             * 2 TYPE gebeurtenis
             * 2 DATE 01 JAN 2001
             * 2 PLAC Alkmaar
             * 2 NOTE gebeurtenis
             */

            // TODO use function check_event.
            //$event_temp = check_event(7);

            if ($this->buffer[7] == '1 _MBON') {
                if (substr($this->buffer[0], 8)) {
                    $event_temp = substr($this->buffer[0], 8);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 MARC') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            // *** Aldfaer: MARL = marriage license! ***
            if ($this->buffer[6] == '1 MARL' and $this->gen_program != 'ALDFAER') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 MARS') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 DIVF') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 ANUL') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 ENGA') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 SLGS') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 CENS') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            // *** Code _NMR is twice in this file ***
            if ($this->buffer[6] == '1 _NMR') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[7] == '1 _COML') {
                if (substr($this->buffer[0], 8)) {
                    $event_temp = substr($this->buffer[0], 8);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 NCHI') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[5] == '1 RFN') {
                if (substr($this->buffer[0], 6)) {
                    $event_temp = substr($this->buffer[0], 6);
                }
                $this->processed = true;
                $this->event_status = true;
            }
            if ($this->buffer[6] == '1 REFN') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }

            // Other events (no BK?)
            //if ($this->buffer[0]=='1 EVEN'){
            //  $this->processed = true;
            //  $this->event_status = true;
            //}
            if ($this->buffer[6] == '1 EVEN') {
                if (substr($this->buffer[0], 7)) {
                    $event_temp = substr($this->buffer[0], 7);
                }
                $this->processed = true;
                $this->event_status = true;
            }

            if ($this->buffer[0] == '1 SLGL') {
                $this->processed = true;
                $this->event_status = true;
            }

            if ($this->event_status) {
                if ($event_start) {
                    $event_start = '';
                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr] = '1';
                    $this->event['connect_kind'][$this->event_nr] = 'family';
                    $this->event['connect_id'][$this->event_nr] = $gedcomnumber;
                    $this->event['connect_kind2'][$this->event_nr] = '';
                    $this->event['connect_id2'][$this->event_nr] = '';
                    $this->event['kind'][$this->event_nr] = 'event';
                    $this->event['event'][$this->event_nr] = '';
                    $this->event['event_extra'][$this->event_nr] = '';
                    $this->event['gedcom'][$this->event_nr] = $this->level[1];
                    $this->event['date'][$this->event_nr] = '';
                    $this->event['text'][$this->event_nr] = '';
                    $this->event['place'][$this->event_nr] = '';

                    if (isset($event_temp)) {
                        //$this->event['text'][$this->event_nr]=$this->merge_texts ($this->event['text'][$this->event_nr],', ',$event_temp);
                        $this->event['event'][$this->event_nr] = $this->merge_texts($this->event['text'][$this->event_nr], ', ', $event_temp);

                        $event_temp = '';
                    }
                }
                // *** Save type ***
                if ($this->buffer[6] === '2 TYPE') {
                    $this->processed = true;
                    $this->event['event'][$this->event_nr] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 PLAC') {
                    $this->processed = true;
                    $this->event['place'][$this->event_nr] = substr($this->buffer[0], 7);
                }
                //if (copy(buf,1,6)='2 TYPE') AND (gen_program='BROSKEEP') then gebeurttekst[nrgebeurtenis]:=gebeurttekst[nrgebeurtenis]+', '+copy(buf,8,length(buf)); //Voor BK!!!

                if ($this->level[2] == 'NOTE') {
                    $this->event['text'][$this->event_nr] = $this->process_texts($this->event['text'][$this->event_nr], '2');
                }

                // *** Source by family event ***
                if ($this->level[2] == 'SOUR') {
                    $event_id = $this->event['calculated_event_id'][$this->event_nr];
                    $this->process_sources('family', 'fam_event_source', $event_id, '2');
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
            if ($this->level[1] == 'SOUR') {
                $this->process_sources('family', 'family_source', $gedcomnumber, '1');
            }

            // *** Address/Living place for family BK & HuMo-genealogy ***
            //1 RESI
            //2 ADDR Naam gezin
            //3 CONT Address ridderkerk
            //2 PHON telefoon
            //2 FAX fax
            //2 EMAIL mail
            //2 WWW website
            if ($this->level[1] == 'RESI') {
                $this->process_addresses('family', 'family_address', $gedcomnumber);
            }

            /**
             * Save non-processed items
             */
            // Skip these lines
            if ($this->buffer[0] == '2 ADDR') {
                $this->processed = true;
            }
            //if ($this->buffer[0]=='1 RESI'){ $this->processed = true; }
            if ($this->buffer[0] == '1 REPO') {
                $this->processed = true;
            }
            if ($this->buffer[0] == '0 TRLR') {
                $this->processed = true;
            }

            if ($this->buffer[5] === '1 RFN') {
                $this->processed = true;
            }

            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($family["fam_unprocessed_tags"]) {
                    $family["fam_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $family["fam_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $family["fam_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $family["fam_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $family["fam_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $family["fam_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }

        // SAVE
        // Pro-gen: special treatment for woman without a man... :-)
        if ($this->gen_program == 'PRO-GEN' && !$fam_man) {
            $family["fam_kind"] = "PRO-GEN";
        }

        // Aldfaer: special treatment for end of relation.
        // *** Aldfaer uses DIV if an relation is ended! ***
        // 1 DIV
        // 2 DATE 2 JAN 2011
        // 2 PLAC Brunssum
        // 1 MARR
        // 2 TYPE partners
        // etc.
        if ($this->gen_program == 'ALDFAER' && $family["fam_kind"] === 'partners') {
            $family["fam_div"] = false;
            $family["fam_relation_end_date"] = $family["fam_div_date"];
            $family["fam_div_date"] = '';
            //$family["fam_relation_end_place"] = $family["fam_div_place"];
            $family["fam_div_place"] = '';
            //$family["fam_relation_text"] = $family["fam_div_text"];
            $family["fam_div_text"] = '';
            // Sources and events...
        }

        if ($this->add_tree == true || $this->reassign == true) {
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
        if ($family["fam_div"] && (!$family["fam_div_date"] && !$family["fam_div_place"] && !$family["fam_div_text"] && !$family["fam_div_authority"])) {
            $family["fam_div_text"] = 'DIVORCE';
        }

        // *** Process estimates/ calculated date for privacy filter ***
        if ($family["fam_marr_date"]) {
            $family["fam_cal_date"] = $family["fam_marr_date"];
        } elseif ($family["fam_marr_church_date"]) {
            $family["fam_cal_date"] = $family["fam_marr_church_date"];
        }

        // for Jewish dates after nightfall
        $heb_qry = '';
        if ($heb_flag == 1) {
            // At least one nightfall date is imported. We have to make sure the required tables exist and if not create them
            $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_families');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['fam_marr_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_notice_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_notice_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_date;";
                $this->dbh->query($sql);
            }
            // we have to add these values to the query below
            $heb_qry .= "fam_marr_notice_date_hebnight='" . $family["fam_marr_notice_date_hebnight"] . "',fam_marr_date_hebnight='" . $family["fam_marr_date_hebnight"] . "',fam_marr_church_notice_date_hebnight='" . $family["fam_marr_church_notice_date_hebnight"] . "',fam_marr_church_date_hebnight='" . $family["fam_marr_church_date_hebnight"] . "',";
        }

        $sql = "INSERT IGNORE INTO humo_families SET
            fam_tree_id = :fam_tree_id,
            fam_gedcomnumber = :fam_gedcomnumber,
            fam_man = :fam_man,
            fam_man_age = :fam_man_age,
            fam_woman = :fam_woman,
            fam_woman_age = :fam_woman_age,
            fam_children = :fam_children,
            fam_religion = :fam_religion,
            fam_kind = :fam_kind,
            fam_text = :fam_text,
            fam_marr_church_notice_date = :fam_marr_church_notice_date,
            fam_marr_church_notice_place = :fam_marr_church_notice_place,
            fam_marr_church_notice_text = :fam_marr_church_notice_text,
            fam_marr_church_date = :fam_marr_church_date,
            fam_marr_church_place = :fam_marr_church_place,
            fam_marr_church_text = :fam_marr_church_text,
            fam_relation_date = :fam_relation_date,
            fam_relation_place = :fam_relation_place,
            fam_relation_text = :fam_relation_text,
            fam_relation_end_date = :fam_relation_end_date,
            fam_marr_notice_date = :fam_marr_notice_date,
            fam_marr_notice_place = :fam_marr_notice_place,
            fam_marr_notice_text = :fam_marr_notice_text,
            fam_marr_date = :fam_marr_date,
            fam_marr_place = :fam_marr_place,
            fam_marr_text = :fam_marr_text,
            fam_marr_authority = :fam_marr_authority,
            fam_div_date = :fam_div_date,
            fam_div_place = :fam_div_place,
            fam_div_text = :fam_div_text,
            fam_div_authority = :fam_div_authority,
            fam_cal_date = :fam_cal_date,
            fam_new_user_id = :fam_new_user_id,
            fam_changed_user_id = :fam_changed_user_id,
            fam_new_datetime = :fam_new_datetime"
            . $this->changed_datetime('fam_changed_datetime', $family["changed_date"], $family["changed_time"]);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':fam_tree_id' => $this->tree_id,
            ':fam_gedcomnumber' => $gedcomnumber,
            ':fam_man' => $fam_man,
            ':fam_man_age' => $family["fam_man_age"],
            ':fam_woman' => $fam_woman,
            ':fam_woman_age' => $family["fam_woman_age"],
            ':fam_children' => $fam_children,
            ':fam_religion' => $family["fam_religion"],
            ':fam_kind' => $family["fam_kind"],
            ':fam_text' => $family["fam_text"],
            ':fam_marr_church_notice_date' => $this->process_date($family["fam_marr_church_notice_date"]),
            ':fam_marr_church_notice_place' => $family["fam_marr_church_notice_place"],
            ':fam_marr_church_notice_text' => $family["fam_marr_church_notice_text"],
            ':fam_marr_church_date' => $this->process_date($family["fam_marr_church_date"]),
            ':fam_marr_church_place' => $family["fam_marr_church_place"],
            ':fam_marr_church_text' => $family["fam_marr_church_text"],
            ':fam_relation_date' => $this->process_date($family["fam_relation_date"]),
            ':fam_relation_place' => $family["fam_relation_place"],
            ':fam_relation_text' => $family["fam_relation_text"],
            ':fam_relation_end_date' => $this->process_date($family["fam_relation_end_date"]),
            ':fam_marr_notice_date' => $this->process_date($family["fam_marr_notice_date"]),
            ':fam_marr_notice_place' => $family["fam_marr_notice_place"],
            ':fam_marr_notice_text' => $family["fam_marr_notice_text"],
            ':fam_marr_date' => $this->process_date($family["fam_marr_date"]),
            ':fam_marr_place' => $family["fam_marr_place"],
            ':fam_marr_text' => $family["fam_marr_text"],
            ':fam_marr_authority' => $family["fam_marr_authority"],
            ':fam_div_date' => $this->process_date($family["fam_div_date"]),
            ':fam_div_place' => $family["fam_div_place"],
            ':fam_div_text' => $family["fam_div_text"],
            ':fam_div_authority' => $family["fam_div_authority"],
            ':fam_cal_date' => $this->process_date($family["fam_cal_date"]),
            ':fam_new_user_id' => $family["new_user_id"],
            ':fam_changed_user_id' => $family["changed_user_id"],
            ':fam_new_datetime' => date('Y-m-d H:i:s', strtotime($family["new_date"] . ' ' . $family["new_time"])),
        ]);

        $fam_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($family["fam_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_rel_id = :tag_rel_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_rel_id' => $fam_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $family["fam_unprocessed_tags"]
            ]);
        }

        // *** Save sources ***
        if ($this->nrsource > 0) {
            for ($i = 1; $i <= $this->nrsource; $i++) {
                $sql = "INSERT IGNORE INTO humo_sources SET
                    source_tree_id = :source_tree_id,
                    source_gedcomnr = :source_gedcomnr,
                    source_status = :source_status,
                    source_title = :source_title,
                    source_abbr = :source_abbr,
                    source_date = :source_date,
                    source_publ = :source_publ,
                    source_place = :source_place,
                    source_refn = :source_refn,
                    source_auth = :source_auth,
                    source_subj = :source_subj,
                    source_item = :source_item,
                    source_kind = :source_kind,
                    source_text = :source_text,
                    source_repo_name = :source_repo_name,
                    source_repo_caln = :source_repo_caln,
                    source_repo_page = :source_repo_page,
                    source_repo_gedcomnr = :source_repo_gedcomnr,
                    source_new_user_id = :source_new_user_id,
                    source_changed_user_id = :source_changed_user_id,
                    source_new_datetime = :source_new_datetime"
                    . $this->changed_datetime('source_changed_datetime', $this->source['changed_date'][$i], $this->source['changed_time'][$i]);

                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':source_tree_id' => $this->tree_id,
                    ':source_gedcomnr' => $this->source["source_gedcomnr"][$i],
                    ':source_status' => $this->source["source_status"][$i],
                    ':source_title' => $this->source["source_title"][$i],
                    ':source_abbr' => $this->source["source_abbr"][$i],
                    ':source_date' => $this->process_date($this->source["source_date"][$i]),
                    ':source_publ' => $this->source["source_publ"][$i],
                    ':source_place' => $this->source["source_place"][$i],
                    ':source_refn' => $this->source["source_refn"][$i],
                    ':source_auth' => $this->source["source_auth"][$i],
                    ':source_subj' => $this->source["source_subj"][$i],
                    ':source_item' => $this->source["source_item"][$i],
                    ':source_kind' => $this->source["source_kind"][$i],
                    ':source_text' => $this->source["source_text"][$i],
                    ':source_repo_name' => $this->source["source_repo_name"][$i],
                    ':source_repo_caln' => $this->source["source_repo_caln"][$i],
                    ':source_repo_page' => $this->source["source_repo_page"][$i],
                    ':source_repo_gedcomnr' => $this->source["source_repo_gedcomnr"][$i],
                    ':source_new_user_id' => $this->source["new_user_id"],
                    ':source_changed_user_id' => $this->source["changed_user_id"],
                    ':source_new_datetime' => date('Y-m-d H:i:s', strtotime($this->source['new_date'][$i] . ' ' . $this->source['new_time'][$i])),
                ]);
            }
            //$source_id=$this->dbh->lastInsertId();
            unset($this->source);
        }
        // Unprocessed items???

        // *** Save addressses ***
        if ($this->nraddress2 > 0) {
            for ($i = 1; $i <= $this->nraddress2; $i++) {
                $gebeurtsql = "INSERT IGNORE INTO humo_addresses SET
                    address_tree_id = :address_tree_id,
                    address_gedcomnr = :address_gedcomnr,
                    address_place = :address_place,
                    address_address = :address_address,
                    address_zip = :address_zip,
                    address_phone = :address_phone,
                    address_date = :address_date,
                    address_text = :address_text";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':address_tree_id' => $this->tree_id,
                    ':address_gedcomnr' => $this->address_array["gedcomnr"][$i],
                    ':address_place' => $this->address_array["place"][$i],
                    ':address_address' => $this->address_array["address"][$i],
                    ':address_zip' => $this->address_array["zip"][$i],
                    ':address_phone' => $this->address_array["phone"][$i],
                    ':address_date' => $this->process_date($this->address_array["date"][$i]),
                    ':address_text' => $this->address_array["text"][$i],
                ]);
            }
            unset($this->address_array);
        }
        // Unprocessed items???

        // store geolocations in humo_locations table
        if ($this->geocode_nr > 0) {
            for ($i = 1; $i <= $this->geocode_nr; $i++) {
                $stmt = $this->dbh->prepare("SELECT * FROM humo_location WHERE location_location = :location_location");
                $stmt->execute([':location_location' => $this->geocode_plac[$i]]);
                $loc_qry = $stmt;

                if (!$loc_qry->rowCount() && $this->geocode_type[$this->geocode_nr] != "") {
                    // doesn't appear in the table yet and the location belongs to birth, bapt, death or buried event
                    $geosql = "INSERT IGNORE INTO humo_location SET
                        location_location = :location_location,
                        location_lat = :location_lat,
                        location_lng = :location_lng";
                    $stmt = $this->dbh->prepare($geosql);
                    $stmt->execute([
                        ':location_location' => $this->geocode_plac[$i],
                        ':location_lat' => $this->geocode_lati[$i],
                        ':location_lng' => $this->geocode_long[$i]
                    ]);
                }
            }
            if (strpos($this->humo_option['geo_trees'], "@" . $this->tree_id . ";") === false) {
                $this->dbh->query("UPDATE humo_settings SET setting_value = CONCAT(setting_value,'@" . $this->tree_id . ";') WHERE setting_variable = 'geo_trees'");
                $this->humo_option['geo_trees'] .= "@" . $this->tree_id . ";";
            }
        }

        // *** Save events ***
        if ($this->event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $this->event['kind']['1'];
            for ($i = 1; $i <= $this->event_nr; $i++) {
                //if ($i==1){ $check_event_kind=$this->event['kind'][$i]; }
                $event_order++;
                if ($check_event_kind != $this->event['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $this->event['kind'][$i];
                }
                if (($this->add_tree == true or $this->reassign == true) && $this->event['text'][$i]) {
                    $this->event['text'][$i] = $this->reassign_ged($this->event['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                    event_tree_id = :event_tree_id,
                    event_order = :event_order,
                    event_connect_kind = :event_connect_kind,
                    event_connect_id = :event_connect_id,";
                if (isset($this->event['connect_id2'][$i])) {
                    $gebeurtsql .= "
                    event_connect_kind2 = :event_connect_kind2,
                    event_connect_id2 = :event_connect_id2,";
                }
                $gebeurtsql .= "
                    event_kind = :event_kind,
                    event_event = :event_event,
                    event_event_extra = :event_event_extra,
                    event_gedcom = :event_gedcom,
                    event_date = :event_date,
                    event_text = :event_text,
                    event_place = :event_place";

                $params = [
                    ':event_tree_id' => $this->tree_id,
                    ':event_order' => $event_order,
                    ':event_connect_kind' => $this->event['connect_kind'][$i],
                    ':event_connect_id' => $this->event['connect_id'][$i],
                    ':event_kind' => $this->event['kind'][$i],
                    ':event_event' => $this->event['event'][$i],
                    ':event_event_extra' => $this->event['event_extra'][$i],
                    ':event_gedcom' => $this->event['gedcom'][$i],
                    ':event_date' => $this->process_date($this->event['date'][$i]),
                    ':event_text' => $this->event['text'][$i],
                    ':event_place' => $this->event['place'][$i],
                ];
                if (isset($this->event['connect_id2'][$i])) {
                    $params[':event_connect_kind2'] = $this->event['connect_kind2'][$i];
                    $params[':event_connect_id2'] = $this->event['connect_id2'][$i];
                }

                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute($params);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->event);
            //$event=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save connections in seperate table ***
        if ($this->connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $this->connect['kind']['1'] . $this->connect['sub_kind']['1'] . $this->connect['connect_id']['1'];
            $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                connect_tree_id = :connect_tree_id,
                connect_order = :connect_order,
                connect_kind = :connect_kind,
                connect_sub_kind = :connect_sub_kind,
                connect_connect_id = :connect_connect_id,
                connect_source_id = :connect_source_id,
                connect_item_id = :connect_item_id,
                connect_text = :connect_text,
                connect_page = :connect_page,
                connect_role = :connect_role,
                connect_date = :connect_date,
                connect_place = :connect_place";
            $stmt = $this->dbh->prepare($gebeurtsql);
            for ($i = 1; $i <= $this->connect_nr; $i++) {
                $connect_order++;
                if ($check_connect !== $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                if ($this->connect['sub_kind'][$i] == 'family_address' && isset($this->connect['connect_order'][$i])) {
                    $connect_order = $this->connect['connect_order'][$i];
                }

                if (($this->add_tree == true or $this->reassign == true) && $this->connect['text'][$i]) {
                    $this->connect['text'][$i] = $this->reassign_ged($this->connect['text'][$i], 'N');
                }

                $stmt->execute([
                    ':connect_tree_id' => $this->tree_id,
                    ':connect_order' => $connect_order,
                    ':connect_kind' => $this->connect['kind'][$i],
                    ':connect_sub_kind' => $this->connect['sub_kind'][$i],
                    ':connect_connect_id' => $this->connect['connect_id'][$i],
                    ':connect_source_id' => $this->connect['source_id'][$i],
                    ':connect_item_id' => $this->connect['item_id'][$i],
                    ':connect_text' => $this->connect['text'][$i],
                    ':connect_page' => $this->connect['page'][$i],
                    ':connect_role' => $this->connect['role'][$i],
                    ':connect_date' => $this->process_date($this->connect['date'][$i]),
                    ':connect_place' => $this->connect['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->connect);
            //$this->connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }
    }

    /**
     * Import GEDCOM texts
     */
    public function process_text($text_array): void
    {
        //$line = $text_array;
        $line2 = explode("\n", $text_array);
        $this->buffer[0] = $line2[0];
        $text['text_text'] = '';
        $text["text_unprocessed_tags"] = '';
        $text["new_date"] = '1970-01-01';
        $text["new_time"] = '00:00:01';
        $text["new_user_id"] = '';

        $text["changed_date"] = '';
        $text["changed_time"] = '';
        $text["changed_user_id"] = '';

        // *** For source connect table ***
        $this->connect_nr = 0;

        // 0 @N954@ NOTE
        // *** Save as N954 ***
        // *** Strpos: we can search for the character, ignoring anything before the offset ***
        $second_char = strpos($this->buffer[0], '@', 3);
        //$text['text_gedcomnr']=substr($this->buffer[0], 2, $second_char-1);
        $text['text_gedcomnr'] = substr($this->buffer[0], 3, $second_char - 3);

        if ($this->add_tree == true || $this->reassign == true) {
            $text['text_gedcomnr'] = $this->reassign_ged($text['text_gedcomnr'], 'N');
        }

        // *** Check for text after "NOTE":
        if (strlen($this->buffer[0]) > $second_char + 7) {
            $text['text_text'] = substr($this->buffer[0], $second_char + 7);
        }

        if (isset($_POST['show_gedcomnumbers'])) {
            echo str_replace("@", "", $text['text_gedcomnr']) . " ";
        }

        // *** Save level0 ***
        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 4));  //rtrim for CHR_
                //$this->event_status = false;
                //$event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            if ($this->level[1] == 'CONC' && substr($this->buffer[0], 2, 4) === 'CONC') {
                $this->processed = true;
                $text['text_text'] .= substr($this->buffer[0], 7);
            }
            if ($this->level[1] == 'CONT' && substr($this->buffer[0], 2, 4) === 'CONT') {
                $this->processed = true;
                $text['text_text'] .= "\n" . substr($this->buffer[0], 7);
            }

            // *** Reunion program: source by text ***
            /*	0 @N23@ NOTE
            1 CONT text
            2 SOUR @S16@
            2 SOUR @S16@
            2 SOUR @S20@
            */
            if ($this->level[2] === 'SOUR') {
                //echo $this->buffer[0].'<br>';
                $this->process_sources('ref_text', 'ref_text_source', $text['text_gedcomnr'], '2');
            }

            // *** New date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $text["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $text["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $text["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Changed date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $text["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $text["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $text["changed_user_id"] = $created_changed["user_id"];
                }
            }

            /**
             * Save non-processed items
             */
            // *** Skip these lines ***
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }
            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($text["text_unprocessed_tags"]) {
                    $text["text_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $text["text_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $text["text_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $text["text_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $text["text_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $text["text_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }

        // *** Aldfaer e-mail addressses have a double @. ***
        $text['text_text'] = str_replace('@@', '@', $text['text_text']);

        // *** Save text ***
        $sql = "INSERT IGNORE INTO humo_texts SET
            text_tree_id = :text_tree_id,
            text_gedcomnr = :text_gedcomnr,
            text_text = :text_text,
            text_new_user_id = :text_new_user_id,
            text_changed_user_id = :text_changed_user_id,
            text_new_datetime = :text_new_datetime" . $this->changed_datetime('text_changed_datetime', $text['changed_date'], $text['changed_time']);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':text_tree_id' => $this->tree_id,
            ':text_gedcomnr' => $text['text_gedcomnr'],
            ':text_text' => $text['text_text'],
            ':text_new_user_id' => $text["new_user_id"],
            ':text_changed_user_id' => $text["changed_user_id"],
            ':text_new_datetime' => date('Y-m-d H:i:s', strtotime($text['new_date'] . ' ' . $text['new_time']))
        ]);

        // *** Save connections in seperate table (source connected to text) ***
        if ($this->connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $this->connect['kind']['1'] . $this->connect['sub_kind']['1'] . $this->connect['connect_id']['1'];
            for ($i = 1; $i <= $this->connect_nr; $i++) {
                $connect_order++;
                if ($check_connect !== $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i];
                }

                //if($this->add_tree==true OR $this->reassign==true) { $this->connect['text'][$i] = $this->reassign_ged($this->connect['text'][$i],'N'); }
                $this->connect['text'][$i] = $text['text_gedcomnr'];    // *** Allready re-assigned ***

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                    connect_tree_id = :connect_tree_id,
                    connect_order = :connect_order,
                    connect_kind = :connect_kind,
                    connect_sub_kind = :connect_sub_kind,
                    connect_connect_id = :connect_connect_id,
                    connect_source_id = :connect_source_id,
                    connect_item_id = :connect_item_id,
                    connect_text = :connect_text,
                    connect_page = :connect_page,
                    connect_role = :connect_role,
                    connect_date = :connect_date,
                    connect_place = :connect_place";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':connect_tree_id' => $this->tree_id,
                    ':connect_order' => $connect_order,
                    ':connect_kind' => $this->connect['kind'][$i],
                    ':connect_sub_kind' => $this->connect['sub_kind'][$i],
                    ':connect_connect_id' => $this->connect['connect_id'][$i],
                    ':connect_source_id' => $this->connect['source_id'][$i],
                    ':connect_item_id' => $this->connect['item_id'][$i],
                    ':connect_text' => $this->connect['text'][$i],
                    ':connect_page' => $this->connect['page'][$i],
                    ':connect_role' => $this->connect['role'][$i],
                    ':connect_date' => $this->process_date($this->connect['date'][$i]),
                    ':connect_place' => $this->connect['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->connect);
            //$this->connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        $text_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($text["text_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_text_id = :tag_text_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_text_id' => $text_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $text["text_unprocessed_tags"]
            ]);
        }
    }

    /**
     * Process sources
     */
    public function process_source($source_array): void
    {
        $this->connect_nr = 0;
        $this->event_nr = 0;

        //$line = $source_array;
        $line2 = explode("\n", $source_array);
        $this->buffer[0] = $line2[0];

        unset($source);  //Reset array
        $source = [
            "source_status" => "",
            "source_title" => "",
            "source_abbr" => "",
            "source_date" => "",
            "source_publ" => "",
            "source_place" => "",
            "source_refn" => "",
            "source_auth" => "",
            "source_subj" => "",
            "source_item" => "",
            "source_kind" => "",
            "source_text" => "",
            "source_repo_name" => "",
            "source_repo_caln" => "",
            "source_repo_page" => "",
            "source_repo_gedcomnr" => "",
            "source_unprocessed_tags" => "",
            "new_date" => '1970-01-01',
            "new_time" => '00:00:01',
            "new_user_id" => "",
            "changed_date" => '',
            "changed_time" => '',
            "changed_user_id" => ""
        ];

        //0 @S1@ SOUR
        $source["id"] = substr($this->buffer[0], 3, -6);
        if ($this->add_tree == true || $this->reassign == true) {
            $source["id"] = $this->reassign_ged($source["id"], 'S');
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $source["id"] . " ";
        }

        // *** Save level0 ***
        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                //$this->level[1]=rtrim(substr($this->buffer[0],2,4));  //rtrim for CHR_
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 5));  //rtrim voor CHR_
                //$this->event_status = false;
                //$event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            /**
             * Sources 0 @S1@ SOUR
             */

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
            if (substr($this->buffer[0], 2, 12) === 'RESN privacy') {
                $this->processed = true;
                $source["source_status"] = 'restricted';
            }

            // Used by GEDCOM 7 files.
            if ($this->buffer[6] == '1 DATA') {
                $this->processed = true;
            }
            if ($this->buffer[6] == '2 EVEN') {
                $this->processed = true;
            }

            //if ($this->buffer[6] == '1 DATE') {
            if (substr($this->buffer[0], 2, 4) === 'DATE') {
                $this->processed = true;
                $source["source_date"] = substr($this->buffer[0], 7);
            }

            if ($this->level[1] === 'TITL') {
                if (substr($this->buffer[0], 2, 4) === 'TITL') {
                    $this->processed = true;
                    $source["source_title"] = substr($this->buffer[0], 7);
                }
                if (substr($this->buffer[0], 2, 4) === 'CONT') {
                    $this->processed = true;
                    $source["source_title"] .= $this->cont(substr($this->buffer[0], 7));
                }
                if (substr($this->buffer[0], 2, 4) === 'CONC') {
                    $this->processed = true;
                    $source["source_title"] .= $this->conc(substr($this->buffer[0], 7));
                }
            }

            if (substr($this->buffer[0], 2, 4) === 'PLAC') {
                $this->processed = true;
                $source["source_place"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'REFN') {
                $this->processed = true;
                $source["source_refn"] = substr($this->buffer[0], 7);
            }

            if (substr($this->buffer[0], 2, 4) === 'PUBL') {
                $this->processed = true;
                $source["source_publ"] = substr($this->buffer[0], 7);
            }  // BK
            if ($this->level[1] === 'PUBL') {
                if ($this->level[2] === 'CONT') {
                    $this->processed = true;
                    $source["source_publ"] .= $this->cont(substr($this->buffer[0], 7));
                }
                if ($this->level[2] === 'CONC') {
                    $this->processed = true;
                    $source["source_publ"] .= $this->conc(substr($this->buffer[0], 7));
                }
            }

            if ($this->level[1] === 'TEXT') {
                if (substr($this->buffer[0], 2, 4) === 'TEXT') {
                    $this->processed = true;
                    $source["source_text"] = $this->merge_texts($source["source_text"], ', ', substr($this->buffer[0], 7));
                }
                if (substr($this->buffer[0], 2, 4) === 'CONC') {
                    $this->processed = true;
                    $source["source_text"] .= $this->conc(substr($this->buffer[0], 7));
                }
                if (substr($this->buffer[0], 2, 4) === 'CONT') {
                    $this->processed = true;
                    $source["source_text"] .= $this->cont(substr($this->buffer[0], 7));
                }
            }

            if ($this->level[1] === 'NOTE') {
                $source["source_text"] = $this->process_texts($source["source_text"], '1');
            }

            if (substr($this->buffer[0], 2, 4) === 'SUBJ') {
                $this->processed = true;
                $source["source_subj"] = substr($this->buffer[0], 7);
            }
            if ($this->level[1] === 'AUTH') {
                if (substr($this->buffer[0], 2, 4) === 'AUTH') {
                    $this->processed = true;
                    $source["source_auth"] = substr($this->buffer[0], 7);
                } // BK
                if (substr($this->buffer[0], 2, 4) === 'CONC') {
                    $this->processed = true;
                    $source["source_auth"] .= $this->conc(substr($this->buffer[0], 7));
                }
                if (substr($this->buffer[0], 2, 4) === 'CONT') {
                    $this->processed = true;
                    $source["source_auth"] .= $this->cont(substr($this->buffer[0], 7));
                }
            }
            if (substr($this->buffer[0], 2, 4) === 'ITEM') {
                $this->processed = true;
                $source["source_item"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'KIND') {
                $this->processed = true;
                $source["source_kind"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'ABBR') {
                $this->processed = true;
                $source["source_abbr"] = substr($this->buffer[0], 7);
            } // BK

            // *** Haza-data pictures ***
            //1 PHOTO @#Aplaatjes\beert&id.jpg jpg@
            if ($this->level[1] === 'PHOTO') {
                if ($this->buffer[7] === '1 PHOTO') {
                    $this->processed = true;
                    $photo = substr($this->buffer[0], 11, -6);
                    $photo = $this->humo_basename($photo);

                    $this->event_nr++;
                    $this->event_nr1 = $this->event_nr;
                    $this->calculated_event_id++;

                    // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                    $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                    $this->event['level'][$this->event_nr] = '1';
                    $this->event['connect_kind'][$this->event_nr] = 'source';
                    $this->event['connect_id'][$this->event_nr] = $source["id"];
                    $this->event['kind'][$this->event_nr] = 'picture';
                    $this->event['event'][$this->event_nr] = $photo;
                    $this->event['event_extra'][$this->event_nr] = '';
                    $this->event['gedcom'][$this->event_nr] = 'PHOTO';
                    $this->event['date'][$this->event_nr] = '';
                    $this->event['text'][$this->event_nr] = '';
                    $this->event['place'][$this->event_nr] = '';
                }
                if ($this->buffer[6] === '2 DSCR' || $this->buffer[6] === '2 NAME') {
                    $this->processed = true;
                    $this->event['text'][$this->event_nr] = substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->event['date'][$this->event_nr] = substr($this->buffer[0], 7);
                }
            }

            // *** 1 OBJE ***
            if ($this->level[1] === 'OBJE') {
                $this->process_picture('source', $source["id"], 'picture');
            }

            // x REPO name
            // x REPO @R1@
            if (substr($this->buffer[0], 2, 4) === 'REPO') {
                $this->processed = true;
                if (substr($this->buffer[0], 2, 6) === 'REPO @') {
                    $source["source_repo_gedcomnr"] = substr($this->buffer[0], 8, -1);
                    if ($this->add_tree == true || $this->reassign == true) {
                        $source["source_repo_gedcomnr"] = $this->reassign_ged($source["source_repo_gedcomnr"], 'RP');
                    }
                } else {
                    $source["source_repo_name"] = substr($this->buffer[0], 7);
                }
            }

            if (substr($this->buffer[0], 2, 4) === 'CALN') {
                $this->processed = true;
                $source["source_repo_caln"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'PAGE') {
                $this->processed = true;
                $source["source_repo_page"] = substr($this->buffer[0], 7);
            }

            ////*** Picture text ***
            //if level1='PHOT' then begin //Foto omschrijving
            //  if copy(buf,1,6)='2 DSCR' then afbeeldtekst[nrafbeelding]:=copy(buf,8,length(buf));
            //  if copy(buf,1,6)='2 NAME' then afbeeldtekst[nrafbeelding]:=copy(buf,8,length(buf)); //VOOR ANDERE GEDCOM VERSIES!
            //end;

            //if (level1<>'PHOT') and (copy(buf,3,4)='NAME') then Naam:=copy(buf,8,length(buf));
            //}

            // *** Save date ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $source["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $source["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $source["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Save date ***
            // 1 CHAN
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $source["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $source["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $source["changed_user_id"] = $created_changed["user_id"];
                }
            }


            /**
             * Save non-processed items
             */
            //Skip these lines
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }
            //if ($this->buffer[0]=='1 REPO'){ $this->processed = true; }
            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($source["source_unprocessed_tags"]) {
                    $source["source_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $source["source_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $source["source_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $source["source_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $source["source_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $source["source_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }

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

        if (($this->add_tree == true or $this->reassign == true) && $source["source_text"]) {
            $source["source_text"] = $this->reassign_ged($source["source_text"], 'N');
        }

        // *** Save events ***
        if ($this->event_nr > 0) {
            $event_order = 0;
            $check_event_kind = $this->event['kind']['1'];
            for ($i = 1; $i <= $this->event_nr; $i++) {
                //if ($i==1){ $check_event_kind=$this->event['kind'][$i]; }
                $event_order++;
                if ($check_event_kind != $this->event['kind'][$i]) {
                    $event_order = 1;
                    $check_event_kind = $this->event['kind'][$i];
                }

                if (($this->add_tree == true or $this->reassign == true) && $this->event['text'][$i]) {
                    $this->event['text'][$i] = $this->reassign_ged($this->event['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_events SET
                    event_tree_id = :event_tree_id,
                    event_order = :event_order,
                    event_connect_kind = :event_connect_kind,
                    event_connect_id = :event_connect_id,
                    event_kind = :event_kind,
                    event_event = :event_event,
                    event_event_extra = :event_event_extra,
                    event_gedcom = :event_gedcom,
                    event_date = :event_date,
                    event_text = :event_text,
                    event_place = :event_place";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':event_tree_id' => $this->tree_id,
                    ':event_order' => $event_order,
                    ':event_connect_kind' => $this->event['connect_kind'][$i],
                    ':event_connect_id' => $this->event['connect_id'][$i],
                    ':event_kind' => $this->event['kind'][$i],
                    ':event_event' => $this->event['event'][$i],
                    ':event_event_extra' => $this->event['event_extra'][$i],
                    ':event_gedcom' => $this->event['gedcom'][$i],
                    ':event_date' => $this->process_date($this->event['date'][$i]),
                    ':event_text' => $this->event['text'][$i],
                    ':event_place' => $this->event['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->event);
            //$event=null;
            //echo ' '.memory_get_usage().'@ ';
        }

        // *** NEW july 2021: Save connections in seperate table ***
        if ($this->connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $this->connect['kind']['1'] . $this->connect['sub_kind']['1'] . $this->connect['connect_id']['1'];
            for ($i = 1; $i <= $this->connect_nr; $i++) {
                $connect_order++;
                if ($check_connect !== $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                //if ($this->connect['sub_kind'][$i]=='family_address'){
                //	if (isset($this->connect['connect_order'][$i])) $connect_order=$this->connect['connect_order'][$i];
                //}

                if (($this->add_tree == true or $this->reassign == true) && $this->connect['text'][$i]) {
                    $this->connect['text'][$i] = $this->reassign_ged($this->connect['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                    connect_tree_id = :connect_tree_id,
                    connect_order = :connect_order,
                    connect_kind = :connect_kind,
                    connect_sub_kind = :connect_sub_kind,
                    connect_connect_id = :connect_connect_id,
                    connect_source_id = :connect_source_id,
                    connect_item_id = :connect_item_id,
                    connect_text = :connect_text,
                    connect_page = :connect_page,
                    connect_role = :connect_role,
                    connect_date = :connect_date,
                    connect_place = :connect_place";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':connect_tree_id' => $this->tree_id,
                    ':connect_order' => $connect_order,
                    ':connect_kind' => $this->connect['kind'][$i],
                    ':connect_sub_kind' => $this->connect['sub_kind'][$i],
                    ':connect_connect_id' => $this->connect['connect_id'][$i],
                    ':connect_source_id' => $this->connect['source_id'][$i],
                    ':connect_item_id' => $this->connect['item_id'][$i],
                    ':connect_text' => $this->connect['text'][$i],
                    ':connect_page' => $this->connect['page'][$i],
                    ':connect_role' => $this->connect['role'][$i],
                    ':connect_date' => $this->process_date($this->connect['date'][$i]),
                    ':connect_place' => $this->connect['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->connect);
            //$this->connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save sources ***
        $sql = "INSERT IGNORE INTO humo_sources SET
            source_tree_id = :source_tree_id,
            source_gedcomnr = :source_gedcomnr,
            source_status = :source_status,
            source_title = :source_title,
            source_abbr = :source_abbr,
            source_date = :source_date,
            source_publ = :source_publ,
            source_place = :source_place,
            source_refn = :source_refn,
            source_auth = :source_auth,
            source_subj = :source_subj,
            source_item = :source_item,
            source_kind = :source_kind,
            source_text = :source_text,
            source_repo_name = :source_repo_name,
            source_repo_caln = :source_repo_caln,
            source_repo_page = :source_repo_page,
            source_repo_gedcomnr = :source_repo_gedcomnr,
            source_new_user_id = :source_new_user_id,
            source_changed_user_id = :source_changed_user_id,
            source_new_datetime = :source_new_datetime"
            . $this->changed_datetime('source_changed_datetime', $source['changed_date'], $source['changed_time']);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':source_tree_id' => $this->tree_id,
            ':source_gedcomnr' => $source["id"],
            ':source_status' => $source["source_status"],
            ':source_title' => $source["source_title"],
            ':source_abbr' => $source["source_abbr"],
            ':source_date' => $this->process_date($source["source_date"]),
            ':source_publ' => $source["source_publ"],
            ':source_place' => $source["source_place"],
            ':source_refn' => $source["source_refn"],
            ':source_auth' => $source["source_auth"],
            ':source_subj' => $source["source_subj"],
            ':source_item' => $source["source_item"],
            ':source_kind' => $source["source_kind"],
            ':source_text' => $source["source_text"],
            ':source_repo_name' => $source["source_repo_name"],
            ':source_repo_caln' => $source["source_repo_caln"],
            ':source_repo_page' => $source["source_repo_page"],
            ':source_repo_gedcomnr' => $source["source_repo_gedcomnr"],
            ':source_new_user_id' => $source["new_user_id"],
            ':source_changed_user_id' => $source["changed_user_id"],
            ':source_new_datetime' => date('Y-m-d H:i:s', strtotime($source['new_date'] . ' ' . $source['new_time']))
        ]);

        $source_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($source["source_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_source_id = :tag_source_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_source_id' => $source_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $source["source_unprocessed_tags"]
            ]);
        }
    }

    /**
     * Process repository
     */
    public function process_repository($repo_array): void
    {
        //$line = $repo_array;
        $line2 = explode("\n", $repo_array);
        $this->buffer[0] = $line2[0];

        unset($repo);  //Reset array
        // TODO: change array variable names repo_gedcomnr -> gedcomnr
        $repo = array(
            "repo_gedcomnr" => "",
            "repo_name" => "",
            "repo_address" => "",
            "repo_zip" => "",
            "repo_place" => "",
            "repo_phone" => "",
            "repo_date" => "",
            "repo_text" => "",
            "repo_mail" => "",
            "repo_url" => "",
            "repo_unprocessed_tags" => "",
            "repo_new_date" => '1970-01-01',
            "repo_new_time" => '00:00:01',
            "repo_new_user_id" => "",
            "repo_changed_date" => '',
            "repo_changed_time" => '',
            "repo_changed_user_id" => ""
        );

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
        $repo["repo_gedcomnr"] = substr($this->buffer[0], 3, -6);
        if ($this->add_tree == true || $this->reassign == true) {
            $repo["repo_gedcomnr"] = $this->reassign_ged($repo["repo_gedcomnr"], 'RP');
        }
        //if (isset($_POST['show_gedcomnumbers'])){
        //  echo substr($repo["repo_gedcomnr"],0,-1)." ";
        //}
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $repo["repo_gedcomnr"] . " ";
        }

        // *** Save level0 ***
        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 4));  //rtrim for CHR_
                //$this->event_status = false;
                //$event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            if ($this->level[1] === 'DATE') {
                $this->processed = true;
                $repo["repo_date"] = substr($this->buffer[0], 7);
            }

            if (substr($this->buffer[0], 2, 4) === 'NAME') {
                $this->processed = true;
                $repo["repo_name"] = substr($this->buffer[0], 7);
            }

            if ($this->level[1] === 'ADDR') {
                if (substr($this->buffer[0], 2, 4) === 'ADDR') {
                    $this->processed = true;
                    $repo["repo_address"] = substr($this->buffer[0], 7);
                }
                if (substr($this->buffer[0], 2, 4) === 'CONC') {
                    $this->processed = true;
                    $repo["repo_address"] .= $this->conc(substr($this->buffer[0], 7));
                }
                if (substr($this->buffer[0], 2, 4) === 'CONT') {
                    $this->processed = true;
                    $repo["repo_address"] .= $this->cont(substr($this->buffer[0], 7));
                }
            }

            if (substr($this->buffer[0], 2, 4) === 'POST') {
                $this->processed = true;
                $repo["repo_zip"] = substr($this->buffer[0], 7);
            }

            if (substr($this->buffer[0], 2, 4) === 'PLAC') {
                $this->processed = true;
                $repo["repo_place"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'CITY') {
                $this->processed = true;
                $repo["repo_place"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) == 'STAE') {
                if ($repo["repo_place"]) {
                    $repo["repo_place"] .= ', ';
                }
                $this->processed = true;
                $repo["repo_place"] .= substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) == 'CTRY') {
                if ($repo["repo_place"]) {
                    $repo["repo_place"] .= ', ';
                }
                $this->processed = true;
                $repo["repo_place"] .= substr($this->buffer[0], 7);
            }

            // 1 PHON +1-801-240-2331 (information)
            // 1 PHON +1-801-240-1278 (gifts & donations)
            // 1 PHON +1-801-240-2584 (support)
            if (substr($this->buffer[0], 2, 4) === 'PHON') {
                $this->processed = true;
                $repo["repo_phone"] = substr($this->buffer[0], 7);
            }

            //SOURCE

            //2 NOTE They have birth records dating from July 1, 1907.  They hav
            //3 CONC e death records from July 1, 1907.  They have marriage reco
            //3 CONC rds from January 1, 1968.
            if ($this->level[2] === 'NOTE') {
                $repo["repo_text"] = $this->process_texts($repo["repo_text"], '2');
            }
            if ($this->level[1] === 'NOTE') {
                $repo["repo_text"] = $this->process_texts($repo["repo_text"], '1');
            }

            // *** Process photo bij repository ***
            //
            //

            // 1 _EMAIL fhl@ldschurch.org
            if (substr($this->buffer[0], 2, 6) === '_EMAIL') {
                $this->processed = true;
                $repo["repo_mail"] = substr($this->buffer[0], 8);
            }
            // GEDCOM 7
            if (substr($this->buffer[0], 2, 5) === 'EMAIL') {
                $this->processed = true;
                $repo["repo_mail"] = substr($this->buffer[0], 8);
            }

            // 1 _URL http://www.familysearch.org
            if (substr($this->buffer[0], 2, 4) === '_URL') {
                $this->processed = true;
                $repo["repo_url"] = substr($this->buffer[0], 7);
            }
            // GEDCOM 7
            if (substr($this->buffer[0], 2, 3) === 'WWW') {
                $this->processed = true;
                $repo["repo_url"] = substr($this->buffer[0], 6);
            }

            // *** New date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $repo["repo_new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $repo["repo_new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $repo["repo_new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Changed date/ time ***
            // *** Save date ***
            // 1 CHAN
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $repo["repo_changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $repo["repo_changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $repo["repo_changed_user_id"] = $created_changed["user_id"];
                }
            }

            /**
             * Save non-processed items
             */
            //Skip these lines
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }
            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($repo["repo_unprocessed_tags"]) {
                    $repo["repo_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $repo["repo_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $repo["repo_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $repo["repo_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $repo["repo_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $repo["repo_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }

        if (($this->add_tree == true or $this->reassign == true) && $repo["repo_text"]) {
            $repo["repo_text"] = $this->reassign_ged($repo["repo_text"], 'N');
        }

        // *** Save repository ***
        $sql = "INSERT IGNORE INTO humo_repositories SET
            repo_tree_id = :repo_tree_id,
            repo_gedcomnr = :repo_gedcomnr,
            repo_name = :repo_name,
            repo_address = :repo_address,
            repo_zip = :repo_zip,
            repo_place = :repo_place,
            repo_phone = :repo_phone,
            repo_date = :repo_date,
            repo_text = :repo_text,
            repo_mail = :repo_mail,
            repo_url = :repo_url,
            repo_new_user_id = :repo_new_user_id,
            repo_changed_user_id = :repo_changed_user_id,
            repo_new_datetime = :repo_new_datetime"
            . $this->changed_datetime('repo_changed_datetime', $repo['repo_changed_date'], $repo['repo_changed_time']);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':repo_tree_id' => $this->tree_id,
            ':repo_gedcomnr' => $repo["repo_gedcomnr"],
            ':repo_name' => $repo["repo_name"],
            ':repo_address' => $repo["repo_address"],
            ':repo_zip' => $repo["repo_zip"],
            ':repo_place' => $repo["repo_place"],
            ':repo_phone' => $repo["repo_phone"],
            ':repo_date' => $this->process_date($repo["repo_date"]),
            ':repo_text' => $repo["repo_text"],
            ':repo_mail' => $repo["repo_mail"],
            ':repo_url' => $repo["repo_url"],
            ':repo_new_user_id' => $repo["repo_new_user_id"],
            ':repo_changed_user_id' => $repo["repo_changed_user_id"],
            ':repo_new_datetime' => date('Y-m-d H:i:s', strtotime($repo['repo_new_date'] . ' ' . $repo['repo_new_time']))
        ]);

        $repo_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($repo["repo_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_repo_id = :tag_repo_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_repo_id' => $repo_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $repo["repo_unprocessed_tags"]
            ]);
        }
    }

    /**
     * Process (shared) addresses
     */
    public function process_address($line): void
    {
        $this->connect_nr = 0;

        $line2 = explode("\n", $line);
        $this->buffer[0] = $line2[0];

        /**
         * Addressses Haza-Plus & HuMo-genealogy shared addresses
         */

        //0 @R1@ RESI
        //1 ADDR Lange Houtstraat 100
        //1 ZIP 1234 AB
        //1 PLAC Amsterdam
        //1 NOTE Bla bla.
        //1 PHOTO @#Aplaatjes\w-brinke.jpg jpg@
        //2 NAME Brinke

        unset($address);  //Reset array
        $address["address_shared"] = "1";
        $address["address"] = '';
        $address["address_zip"] = '';
        $address["address_place"] = '';
        $address["address_phone"] = '';
        $address["address_text"] = '';
        $address["address_gedcomnr"] = substr($this->buffer[0], 3, -6);
        if ($this->add_tree == true || $this->reassign == true) {
            $address["address_gedcomnr"] = $this->reassign_ged($address["address_gedcomnr"], 'R');
        }
        $address["address_unprocessed_tags"] = '';
        $address["new_date"] = '1970-01-01';
        $address["new_time"] = '00:00:01';
        $address["new_user_id"] = '';
        $address["changed_date"] = '';
        $address["changed_time"] = '';
        $address["changed_user_id"] = '';

        //if (isset($_POST['show_gedcomnumbers'])){
        //  echo substr($address["id"],0,-1)." ";
        //}
        if (isset($_POST['show_gedcomnumbers'])) {
            echo $address["address_gedcomnr"] . " ";
        }

        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 4));  //rtrim for CHR_
                //$this->event_status = false;
                //$event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            if (substr($this->buffer[0], 2, 4) === 'ADDR') {
                $this->processed = true;
                $address["address"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'ZIP ') {
                $this->processed = true;
                $address["address_zip"] = substr($this->buffer[0], 6);
            }  //Voor BK
            //if (substr($this->buffer[0],2,4)=='DATE '){
            //  $this->processed = true; $address["datum"]=substr($this->buffer[0],7);
            //}
            if (substr($this->buffer[0], 2, 4) === 'PLAC') {
                $this->processed = true;
                $address["address_place"] = substr($this->buffer[0], 7);
            }
            if (substr($this->buffer[0], 2, 4) === 'PHON') {
                $this->processed = true;
                $address["address_phone"] = substr($this->buffer[0], 7);
            }

            // *** Text by address ***
            $address["address_text"] = $this->process_texts($address["address_text"], '1');

            // *** Source by shared address ***
            if ($this->level[2] === 'SOUR') {
                $this->process_sources('address', 'address_source', $address["address_gedcomnr"], '2');
            }

            //1 PHOTO @#APLAATJES\AKTEMONS.GIF GIF@
            //if ($this->buffer[7]=='1 PHOTO'){
            //	$this->processed = true;
            //  $address["address_photo"]=$this->merge_texts($address["address_photo"], ';', substr($this->buffer[0],11,-5));
            //}

            // *** New date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $address["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $address["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $address["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Changed date/ time ***
            // 1 CHAN
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $address["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $address["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $address["changed_user_id"] = $created_changed["user_id"];
                }
            }

            /**
             * Save non-processed items
             */
            //Skip these lines
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }

            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($address["address_unprocessed_tags"]) {
                    $address["address_unprocessed_tags"] .= "<br>\n";
                }
                if ($this->level[1]) {
                    $address["address_unprocessed_tags"] .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $address["address_unprocessed_tags"] .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $address["address_unprocessed_tags"] .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $address["address_unprocessed_tags"] .= '|3 ' . $this->level[3];
                }
                $address["address_unprocessed_tags"] .= '|' . $this->buffer[0];
            }
        }

        // *** NEW april 2022: Save connections in seperate table ***
        if ($this->connect_nr > 0) {
            $connect_order = 0;
            $check_connect = $this->connect['kind']['1'] . $this->connect['sub_kind']['1'] . $this->connect['connect_id']['1'];
            for ($i = 1; $i <= $this->connect_nr; $i++) {
                $connect_order++;
                if ($check_connect !== $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i]) {
                    $connect_order = 1;
                    $check_connect = $this->connect['kind'][$i] . $this->connect['sub_kind'][$i] . $this->connect['connect_id'][$i];
                }

                // *** Process address order (because address and source by address) ***
                //if ($this->connect['sub_kind'][$i]=='family_address'){
                //	if (isset($this->connect['connect_order'][$i])) $connect_order=$this->connect['connect_order'][$i];
                //}

                if (($this->add_tree == true or $this->reassign == true) && $this->connect['text'][$i]) {
                    $this->connect['text'][$i] = $this->reassign_ged($this->connect['text'][$i], 'N');
                }

                $gebeurtsql = "INSERT IGNORE INTO humo_connections SET
                    connect_tree_id = :connect_tree_id,
                    connect_order = :connect_order,
                    connect_kind = :connect_kind,
                    connect_sub_kind = :connect_sub_kind,
                    connect_connect_id = :connect_connect_id,
                    connect_source_id = :connect_source_id,
                    connect_item_id = :connect_item_id,
                    connect_text = :connect_text,
                    connect_page = :connect_page,
                    connect_role = :connect_role,
                    connect_date = :connect_date,
                    connect_place = :connect_place";
                $stmt = $this->dbh->prepare($gebeurtsql);
                $stmt->execute([
                    ':connect_tree_id' => $this->tree_id,
                    ':connect_order' => $connect_order,
                    ':connect_kind' => $this->connect['kind'][$i],
                    ':connect_sub_kind' => $this->connect['sub_kind'][$i],
                    ':connect_connect_id' => $this->connect['connect_id'][$i],
                    ':connect_source_id' => $this->connect['source_id'][$i],
                    ':connect_item_id' => $this->connect['item_id'][$i],
                    ':connect_text' => $this->connect['text'][$i],
                    ':connect_page' => $this->connect['page'][$i],
                    ':connect_role' => $this->connect['role'][$i],
                    ':connect_date' => $this->process_date($this->connect['date'][$i]),
                    ':connect_place' => $this->connect['place'][$i],
                ]);
            }

            // *** Reset array to free memory ***
            //echo '<br>====>>>>'.memory_get_usage().' RESET ';
            unset($this->connect);
            //$this->connect=null;
            //echo ' '.memory_get_usage().'@ ';
        }


        // *** Save addressses ***
        $sql = "INSERT IGNORE INTO humo_addresses SET
            address_tree_id = :address_tree_id,
            address_gedcomnr = :address_gedcomnr,
            address_shared = :address_shared,
            address_address = :address_address,
            address_zip = :address_zip,
            address_place = :address_place,
            address_phone = :address_phone,
            address_text = :address_text,
            address_new_user_id = :address_new_user_id,
            address_changed_user_id = :address_changed_user_id,
            address_new_datetime = :address_new_datetime"
            . $this->changed_datetime('address_changed_datetime', $address['changed_date'], $address['changed_time']);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':address_tree_id' => $this->tree_id,
            ':address_gedcomnr' => $address["address_gedcomnr"],
            ':address_shared' => $address["address_shared"],
            ':address_address' => $address["address"],
            ':address_zip' => $address["address_zip"],
            ':address_place' => $address["address_place"],
            ':address_phone' => $address["address_phone"],
            ':address_text' => $address["address_text"],
            ':address_new_user_id' => $address["new_user_id"],
            ':address_changed_user_id' => $address["changed_user_id"],
            ':address_new_datetime' => date('Y-m-d H:i:s', strtotime($address['new_date'] . ' ' . $address['new_time']))
        ]);

        $address_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($address["address_unprocessed_tags"]) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_address_id = :tag_address_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_address_id' => $address_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $address["address_unprocessed_tags"]
            ]);
        }
    }


    /**
     * Process objects
     */
    public function process_object($object_array): void
    {
        //$line = $object_array;
        $line2 = explode("\n", $object_array);
        $this->buffer[0] = $line2[0];

        // 0 @O123@ OBJE
        // 1 FORM jpeg
        // 1 TITL Fred Smith
        // 1 FILE fred_smith.jpg
        // 1 CHAN
        // 2 DATE 4 JUN 2010
        // 3 TIME 09:19:50

        $this->event['gedcomnr'] = substr($this->buffer[0], 3, -6);
        if ($this->add_tree == true || $this->reassign == true) {
            $this->event['gedcomnr'] = $this->reassign_ged($this->event['gedcomnr'], 'O');
        }
        $this->event['event'] = '';
        $this->event['event_extra'] = '';
        $this->event['date'] = '';
        $this->event['place'] = '';
        $this->event['text'] = ''; // $this->event['source']='';
        $event_unprocessed_tags = '';
        $this->event["new_date"] = '1970-01-01';
        $this->event["new_time"] = '00:00:01';
        $this->event["new_user_id"] = '';
        $this->event["changed_date"] = '';
        $this->event["changed_time"] = '';
        $this->event["changed_user_id"] = '';

        if (isset($_POST['show_gedcomnumbers'])) {
            echo $this->event['gedcomnr'] . " ";
        }

        $this->level[0] = substr($this->buffer[0], 2);
        $this->level[1] = '';
        $this->level[2] = '';
        $this->level[3] = '';
        $this->level[4] = '';

        $loop = count($line2) - 2;
        for ($z = 1; $z <= $loop; $z++) {
            $this->processed = false;
            $this->buffer[0] = $line2[$z];
            $this->buffer[0] = rtrim($this->buffer[0], "\n\r");  // strip newline
            //echo "BUFFER: ".$z."-".$this->buffer[0]."!".count($line2)."<br>";

            // *** Strip starting spaces, for Pro-gen ***
            if ($this->gen_program == 'PRO-GEN') {
                $this->buffer[0] = ltrim($this->buffer[0], " ");
            }

            $this->set_buffer();

            // *** Save level1 ***
            if ($this->buffer[1] === '1') {
                $this->level[1] = rtrim(substr($this->buffer[0], 2, 4));  //rtrim for CHR_
                //$this->event_status = false;
                //$event_start = '1';
                $this->level[2] = '';
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level2 ***
            elseif ($this->buffer[1] === '2') {
                $this->level[2] = substr($this->buffer[0], 2, 4);
                $this->level[3] = '';
                $this->level[4] = '';
            }
            // *** Save level3 ***
            elseif ($this->buffer[1] === '3') {
                $this->level[3] = substr($this->buffer[0], 2, 4);
                $this->level[4] = '';
            }
            // *** Save level4 ***
            elseif ($this->buffer[1] === '4') {
                $this->level[4] = substr($this->buffer[0], 2, 4);
            }

            if ($this->buffer[6] === '1 FILE') {
                $this->processed = true;
                $photo = substr($this->buffer[0], 7);
                // *** Aldfaer sometimes uses: 2 FILE \bestand.jpg ***
                $photo = $this->humo_basename($photo);
                $this->event['event'] = $photo;
            }
            //if ($this->buffer[6]=='1 TITL'){
            if ($this->buffer[6] === '1 TITL' || $this->buffer[6] === '2 TITL') {
                $this->processed = true;
                $this->event['text'] = substr($this->buffer[0], 7);
            }

            // *** New date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == '_NEW' || $this->level[1] == 'CREA') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $this->event["new_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $this->event["new_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $this->event["new_user_id"] = $created_changed["user_id"];
                }
            }

            // *** Changed date/ time ***
            // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
            // 2 DATE 04 AUG 2004
            if ($this->level[1] == 'CHAN') {
                $created_changed = $this->get_created_changed();
                $this->processed = $created_changed["processed"];
                if ($created_changed["date"]) {
                    $this->event["changed_date"] = $created_changed["date"];
                }
                if ($created_changed["time"]) {
                    $this->event["changed_time"] = $created_changed["time"];
                }
                if ($created_changed["user_id"]) {
                    $this->event["changed_user_id"] = $created_changed["user_id"];
                }
            }

            /**
             * Save non-processed items
             */
            //Skip these lines
            if ($this->buffer[0] === '0 TRLR') {
                $this->processed = true;
            }

            if (!$this->processed) {
                if (isset($_POST['check_processed'])) {
                    $this->not_processed[] = '0 ' . $this->level[0] . '</td><td>1 ' . $this->level[1] . '<br></td><td>2 ' . $this->level[2] . '<br></td><td>3 ' . $this->level[3] . '<br></td><td>' . $this->buffer[0];
                    //$this->non_processed_items($this->buffer[0]);
                }

                if ($event_unprocessed_tags) {
                    $event_unprocessed_tags .= "<br>\n";
                }
                if ($this->level[1]) {
                    $event_unprocessed_tags .= '0 ' . $this->level[0];
                }
                if ($this->level[2]) {
                    $event_unprocessed_tags .= '|1 ' . $this->level[1];
                }
                if ($this->level[3]) {
                    $event_unprocessed_tags .= '|2 ' . $this->level[2];
                }
                if ($this->level[4]) {
                    $event_unprocessed_tags .= '|3 ' . $this->level[3];
                }
                $event_unprocessed_tags .= '|' . $this->buffer[0];
            }
        }

        if ($this->add_tree == true || $this->reassign == true) {
            $this->event['text'] = $this->reassign_ged($this->event['text'], 'O');
        }
        // *** Save object ***
        $eventsql = "INSERT IGNORE INTO humo_events SET
            event_tree_id = :event_tree_id,
            event_gedcomnr = :event_gedcomnr,
            event_order = :event_order,
            event_connect_kind = :event_connect_kind,
            event_connect_id = :event_connect_id,
            event_kind = :event_kind,
            event_event = :event_event,
            event_event_extra = :event_event_extra,
            event_gedcom = :event_gedcom,
            event_date = :event_date,
            event_place = :event_place,
            event_text = :event_text,
            event_new_user_id = :event_new_user_id,
            event_changed_user_id = :event_changed_user_id,
            event_new_datetime = :event_new_datetime"
            . $this->changed_datetime('event_changed_datetime', $this->event["changed_date"], $this->event["changed_time"]);
        $stmt = $this->dbh->prepare($eventsql);
        $stmt->execute([
            ':event_tree_id' => $this->tree_id,
            ':event_gedcomnr' => $this->event['gedcomnr'],
            ':event_order' => 1,
            ':event_connect_kind' => '',
            ':event_connect_id' => '',
            ':event_kind' => 'object',
            ':event_event' => $this->event['event'],
            ':event_event_extra' => $this->event['event_extra'],
            ':event_gedcom' => 'OBJE',
            ':event_date' => $this->process_date($this->event['date']),
            ':event_place' => $this->event['place'],
            ':event_text' => $this->event['text'],
            ':event_new_user_id' => $this->event["new_user_id"],
            ':event_changed_user_id' => $this->event["changed_user_id"],
            ':event_new_datetime' => date('Y-m-d H:i:s', strtotime($this->event["new_date"] . ' ' . $this->event["new_time"]))
        ]);

        $event_id = $this->dbh->lastInsertId();

        // *** Save unprocessed items ***
        if ($event_unprocessed_tags) {
            $sql = "INSERT IGNORE INTO humo_unprocessed_tags SET
                tag_event_id = :tag_event_id,
                tag_tree_id = :tag_tree_id,
                tag_tag = :tag_tag";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tag_event_id' => $event_id,
                ':tag_tree_id' => $this->tree_id,
                ':tag_tag' => $event_unprocessed_tags
            ]);
        }
    }

    /**
     * Functions
     */

    /*
    private function non_processed_items($this->buffer[0]){
        // *** Not processed items for list by reading of GEDCOM ***
        $this->not_processed_tmp='0 '.$this->level[0].'</td><td>1 '.$this->level[1].'<br></td><td>';
        if ($this->level[2]){ $this->not_processed_tmp.="2 $this->level[2]"; }
        $this->not_processed_tmp.="<br></td><td>";
        if ($this->level[3]){ $this->not_processed_tmp.="3 $this->level[3]"; }
        $this->not_processed_tmp.="<br></td><td>$this->buffer[0]";
        $this->not_processed[]=$this->not_processed_tmp;

        //if ($process){ $process.="<br>\n"; }
        //if ($this->level[1]){ $process.='0 '.$this->level[0]; }
        //if ($this->level[2]){ $process.='|1 '.$this->level[1]; }
        //if ($this->level[3]){ $process.='|2 '.$this->level[2]; }
        //if ($this->level[4]){ $process.='|3 '.$this->level[3]; }
        //$process.='|'.$this->buffer[0];
        //return $process;
    }
    */

    private function process_date($date)
    {
        // *** Convert 2 DATE Bef 1909 to uppercase: 2 DATE BEF 1909 ***
        $date = strtoupper($date);

        // in case years under 1000 are given as 0945, make it 945
        $date = str_replace(" 0", " ", $date); //gets rid of "bet 2 may 0954 AND jun 0951" and "5 may 0985"
        //if(substr($date,-4,1)=="0") {
        //	 // if there is still a "0" this means we had the year by itself "0985" with nothing before it
        if (substr($date, -4, 1) === "0" && strlen($date) == 4) {
            // if there is still a "0" this means we had the year by itself "0985" with nothing before it
            // the strlen code was added to prevent that double dates with a 0 in position 4th from the end, will be erroneously changed  (1960/61 --> /61)
            $date = substr($date, -3, 3);
        }
        return $date;
    }

    // *** Merge function: text1, merge character, text2 ***
    private function merge_texts($text1, $merge, $text2)
    {
        return $text1 ? $text1 . $merge . $text2 : $text2;
    }

    // CONT
    private function cont($text1)
    {
        //$text="<br>\n".$text1;
        $text = "\n" . $text1;
        return $text;
    }

    // CONC, Some programs need an extra space after CONC!
    // PRO-GEN 3.0b-p10: add extra space.
    // PRO-GEN 3.22: no extra space.
    private function conc($text1)
    {
        $spacer = '';
        if ($this->gen_program == 'HuMo-gen' || $this->gen_program == 'HuMo-genealogy') {
            $spacer = ' ';
        } elseif ($this->gen_program == 'Haza-Data') {
            $spacer = ' ';
        } elseif ($this->gen_program == 'PRO-GEN' && substr($this->gen_program_version, 0, 3) === '3.0') {
            $spacer = ' ';
        } elseif ($this->gen_program == 'Family Tree Legends') {
            $spacer = ' ';
        }
        return $spacer . $text1;
    }

    // *** Process texts ***
    // 1 NOTE Information, text, etc.
    // 2 CONT 2nd line.
    // 2 CONT 3rd line
    // 2 CONC remaining text of 3rd line.
    private function process_texts($text, $number)
    {
        $this->buffer[6] = substr($this->buffer[0], 0, 6);
        if ($this->buffer[6] === ($number) . ' NOTE') {
            // *** Seperator for multiple texts ***
            if ($text != '') {
                $text .= "|";
            }
            $this->processed = true;
            $text .= substr($this->buffer[0], 7);
        } elseif ($this->buffer[6] === ($number) . ' TITL') {
            $this->processed = true;
            $text .= substr($this->buffer[0], 7);
        }    // 1 OBJE -> 2 TITL 
        elseif ($this->buffer[6] === ($number + 1) . ' CONT') {
            $this->processed = true;
            $text .= $this->cont(substr($this->buffer[0], 7));
        } elseif ($this->buffer[6] === ($number + 1) . ' CONC') {
            $this->processed = true;
            $text .= $this->conc(substr($this->buffer[0], 7));
        }
        return $text;
    }

    public function humo_basename($photo)
    {
        // *** Default: only read file name for example: picture.jpg ***
        if ($this->humo_option["gedcom_process_pict_path"] == 'file_name') {
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
        if (strpos($photo, ":/") == 1) {
            $photo = substr($photo, 3, strlen($photo) - 3);
        }

        return $photo;
    }

    // *** Quality ***
    // 0 = Unreliable evidence or estimated data 
    // 1 = Questionable reliability of evidence (interviews, census, oral genealogies, or potential for bias for example, an autobiography) 
    // 2 = Secondary evidence, data officially recorded sometime after event 
    // 3 = Direct and primary evidence used, or by dominance of the evidence
    // Example:
    // 2 QUAY 0
    public function process_quality()
    {
        $text = substr($this->buffer[0], -1);
        // Ancestry uses 1 - 4 in stead of 0 - 3, adjust numbers:
        if ($this->gen_program == "Ancestry.com Family Trees") {
            $text--;
        }
        return $text;
    }

    private function reassign_ged($gednr, $letter)
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
    private function process_place($place)
    {
        // *** Solve bug in Haza-data GEDCOM export, replace: Adelaide ,Australië by: Adelaide, Australië *
        $place = str_replace(" ,", ", ", $place);
        return $place;
    }

    // *** Process places ***
    public function process_places($map_place): void
    {
        // 2 PLAC Cleveland, Ohio, USA
        // 3 MAP
        // 4 LATI N41.500347
        // 4 LONG W81.66687
        if (substr($this->level[3], 0, 3) === 'MAP') {
            //if ($this->buffer[6]==$number.' PLAC'){ $this->processed = true; $place=substr($this->buffer[0], 7); }

            if (substr($this->buffer[0], 0, 5) === '3 MAP') {
                $this->processed = true;
                $this->geocode_nr++;
                $this->geocode_plac[$this->geocode_nr] = $map_place;
                $this->geocode_type[$this->geocode_nr] = ''; // needed to enter location_status as "humo3_death" later
                if ($this->level[1] == 'BIRT') {
                    $this->geocode_type[$this->geocode_nr] = "birth";
                } elseif ($this->level[1] == 'BAPT') {
                    $this->geocode_type[$this->geocode_nr] = "bapt";
                } elseif ($this->level[1] == 'DEAT') {
                    $this->geocode_type[$this->geocode_nr] = "death";
                } elseif ($this->level[1] == 'BURI') {
                    $this->geocode_type[$this->geocode_nr] = "buried";
                }
            } elseif ($this->buffer[6] === '4 LATI') {
                $this->processed = true;
                $geocode = (substr($this->buffer[0], 7));
                $geocode = substr($geocode, 0, 1) === 'S' ? '-' . substr($geocode, 1) : substr($geocode, 1);
                $this->geocode_lati[$this->geocode_nr] = $geocode;
            } elseif ($this->buffer[6] === '4 LONG') {
                $this->processed = true;
                $geocode = (substr($this->buffer[0], 7));
                $geocode = substr($geocode, 0, 1) === 'W' ? '-' . substr($geocode, 1) : substr($geocode, 1);
                $this->geocode_long[$this->geocode_nr] = $geocode;
            }
        }
    }



    // *** Process addresses by person and relation ***
    public function process_addresses($connect_kind, $connect_sub_kind, $connect_id): void
    {
        // *** Living place ***
        //Haza-Data 7.2
        //1 ADDR Ridderkerk
        //1 ADDR Slikkerveer
        //1 ADDR Alkmaar
        //1 ADDR Heerhugowaard
        if ($this->buffer[6] === '1 ADDR') {
            $address_gedcomnr = 'R' . $_SESSION['new_address_gedcomnr'];
            if ($this->add_tree == true || $this->reassign == true) {
                $address_gedcomnr = $this->reassign_ged('R' . $_SESSION['new_address_gedcomnr'], 'R');
            }
            $_SESSION['address_gedcomnr'] = $address_gedcomnr;
            $this->nraddress2++;
            $this->address_array["gedcomnr"][$this->nraddress2] = $address_gedcomnr;
            $this->address_array["place"][$this->nraddress2] = '';
            $this->address_array["address"][$this->nraddress2] = '';
            $this->address_array["zip"][$this->nraddress2] = '';
            $this->address_array["phone"][$this->nraddress2] = '';
            $this->address_array["date"][$this->nraddress2] = '';
            $this->address_array["text"][$this->nraddress2] = '';
            $this->processed = true;
            $this->address_array["place"][$this->nraddress2] = substr($this->buffer[0], 7);

            // *** Used for general numbering of connections ***
            $this->connect_nr++;
            $this->calculated_connect_id++;
            $this->address_order++;

            // *** Seperate numbering, because there can be sources by a address ***
            $address_connect_nr = $this->connect_nr;

            $this->connect['kind'][$this->connect_nr] = $connect_kind;
            $this->connect['sub_kind'][$this->connect_nr] = $connect_sub_kind;
            $this->connect['connect_id'][$this->connect_nr] = $connect_id;
            $this->connect['connect_order'][$this->connect_nr] = $this->address_order;

            $this->connect['source_id'][$this->connect_nr] = '';
            $this->connect['text'][$this->connect_nr] = '';
            $this->connect['item_id'][$this->connect_nr] = $address_gedcomnr;
            $this->connect['quality'][$this->connect_nr] = '';
            $this->connect['date'][$this->connect_nr] = '';
            $this->connect['place'][$this->connect_nr] = '';
            $this->connect['page'][$this->connect_nr] = '';
            $this->connect['role'][$this->connect_nr] = '';
            $this->connect['date'][$this->connect_nr] = '';

            // *** Next GEDCOM number ***
            $_SESSION['new_address_gedcomnr'] += 1;
        }

        // *** Address ***
        if ($this->level[1] == 'RESI') {
            // *** Living place Haza-data plus etc. ***
            //*** Haza-data plus link to address ***
            //1 ADDR de Rijp
            //1 RESI @R34@
            //2 DATE 1651
            //2 ROLE landbouwer op

            // *** Use connection table to store addresses ***
            // *** Check for address links (shared addresses), @R34@ links ***
            if (substr($this->level['1a'], 0, 8) === '1 RESI @') {
                if ($this->buffer[6] === '1 RESI') {
                    $this->processed = true;

                    // *** Used for general numbering of connections ***
                    $this->connect_nr++;
                    $this->calculated_connect_id++;
                    $this->address_order++;

                    // *** Seperate numbering, because there can be sources by a address ***
                    $address_connect_nr = $this->connect_nr;

                    $this->connect['kind'][$this->connect_nr] = $connect_kind;
                    $this->connect['sub_kind'][$this->connect_nr] = $connect_sub_kind;
                    $this->connect['connect_id'][$this->connect_nr] = $connect_id;
                    $this->connect['connect_order'][$this->connect_nr] = $this->address_order;

                    $this->connect['source_id'][$this->connect_nr] = '';
                    $this->connect['text'][$this->connect_nr] = '';
                    // *** Save place GEDCOM number in connect_item_id field ***
                    $this->connect['item_id'][$this->connect_nr] = substr($this->buffer[0], 8, -1);
                    if ($this->add_tree == true || $this->reassign == true) {
                        $this->connect['item_id'][$this->connect_nr] = $this->reassign_ged($this->connect['item_id'][$this->connect_nr], 'R');
                    }
                    $_SESSION['address_gedcomnr'] = $this->connect['item_id'][$this->connect_nr];

                    $this->connect['quality'][$this->connect_nr] = '';
                    $this->connect['date'][$this->connect_nr] = '';
                    $this->connect['place'][$this->connect_nr] = '';
                    $this->connect['page'][$this->connect_nr] = '';
                    $this->connect['role'][$this->connect_nr] = '';
                    $this->connect['date'][$this->connect_nr] = '';
                }

                // *** Address role ***
                if ($this->buffer[6] === '2 ROLE') {
                    $this->processed = true;
                    $this->connect['role'][$this->connect_nr] = substr($this->buffer[0], 7);
                }
                // *** Address date ***
                if ($this->buffer[6] === '2 DATE') {
                    $this->processed = true;
                    $this->connect['date'][$this->connect_nr] = substr($this->buffer[0], 7);
                }
                // *** Extra text by address in HuMo-genealogy ***
                // 2 DATA
                // 3 TEXT text ..... => Extra text by address...
                // 4 CONT ..........
                if ($this->buffer[6] === '2 DATA') {
                    $this->processed = true; //$this->connect['text'][$this->connect_nr]=substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '3 TEXT') {
                    if ($this->connect['text'][$this->connect_nr]) {
                        $this->connect['text'][$this->connect_nr] .= '<br>';
                    }
                    $this->processed = true;
                    $this->connect['text'][$this->connect_nr] .= substr($this->buffer[0], 7);
                }
                if ($this->buffer[6] === '4 CONT') {
                    $this->processed = true;
                    $this->connect['text'][$this->connect_nr] .= $this->cont(substr($this->buffer[0], 7));
                }
                if ($this->buffer[6] === '4 CONC') {
                    $this->processed = true;
                    $this->connect['text'][$this->connect_nr] .= $this->conc(substr($this->buffer[0], 7));
                }

                if ($this->level[2] == 'SOUR') {
                    if ($connect_kind == 'person') {
                        $this->process_sources('person', 'pers_address_connect_source', $this->calculated_connect_id, '2');
                    } else {
                        $this->process_sources('family', 'fam_address_connect_source', $this->calculated_connect_id, '2');
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
            //if ($this->buffer[6]=='1 RESI' AND $this->gen_program!='Haza-Data' AND $this->gen_program!='HuMo-genealogy'){
            if ($this->buffer[6] === '1 RESI' && substr($this->buffer[0], 7, 1) !== '@') {
                $this->processed = true;
                $this->nraddress2++;

                $address_gedcomnr = 'R' . $_SESSION['new_address_gedcomnr'];
                if ($this->add_tree == true || $this->reassign == true) {
                    $address_gedcomnr = $this->reassign_ged('R' . $_SESSION['new_address_gedcomnr'], 'R');
                }
                $_SESSION['address_gedcomnr'] = $address_gedcomnr;
                $this->address_array["gedcomnr"][$this->nraddress2] = $address_gedcomnr;
                $this->address_array["place"][$this->nraddress2] = '';
                $this->address_array["address"][$this->nraddress2] = '';
                $this->address_array["zip"][$this->nraddress2] = '';
                $this->address_array["phone"][$this->nraddress2] = '';
                $this->address_array["date"][$this->nraddress2] = '';
                $this->address_array["text"][$this->nraddress2] = '';
                //$address_source[$this->nraddress2]="";

                // FTM:
                // 1 RESI Owner of the house, 6 
                // 2 CONC Lane.
                if (substr($this->buffer[0], 7)) {
                    $this->address_array["address"][$this->nraddress2] = substr($this->buffer[0], 7);
                }

                // *** Used for general numbering of connections ***
                $this->connect_nr++;
                $this->calculated_connect_id++;
                $this->address_order++;

                // *** Seperate numbering, because there can be sources by an address ***
                $address_connect_nr = $this->connect_nr;

                $this->connect['kind'][$this->connect_nr] = $connect_kind;
                $this->connect['sub_kind'][$this->connect_nr] = $connect_sub_kind;
                $this->connect['connect_id'][$this->connect_nr] = $connect_id;
                $this->connect['connect_order'][$this->connect_nr] = $this->address_order;

                $this->connect['source_id'][$this->connect_nr] = '';
                $this->connect['text'][$this->connect_nr] = '';
                $this->connect['item_id'][$this->connect_nr] = $address_gedcomnr;
                $this->connect['quality'][$this->connect_nr] = '';
                $this->connect['date'][$this->connect_nr] = '';
                $this->connect['place'][$this->connect_nr] = '';
                $this->connect['page'][$this->connect_nr] = '';
                $this->connect['role'][$this->connect_nr] = '';
                $this->connect['date'][$this->connect_nr] = '';

                // *** Next GEDCOM number ***
                $_SESSION['new_address_gedcomnr'] += 1;
            }

            // FTM:
            // 1 RESI Owner of the house, 6 
            // 2 CONC Lane.
            if ($this->level[2] == 'CONC') {
                $this->processed = true;
                $this->address_array["address"][$this->nraddress2] .= substr($this->buffer[0], 7);
            }
            if ($this->level[2] == 'CONT') {
                $this->processed = true;
                $this->address_array["address"][$this->nraddress2] .= "\n" . substr($this->buffer[0], 7);
            }

            // *** Restore HuMo-genealogy address GEDCOM numbers ***
            // 1 RESI
            // 2 RIN 1 (GEDCOM number without R).
            if (($this->gen_program == 'HuMo-gen' or $this->gen_program == 'HuMo-genealogy') && $this->level[2] == 'RIN ') {
                $this->processed = true;
                //echo substr($this->buffer[0],6).'<br>';
                $address_gedcomnr = 'R' . substr($this->buffer[0], 6);
                if ($this->add_tree == true || $this->reassign == true) {
                    $address_gedcomnr = $this->reassign_ged($address_gedcomnr, 'R');
                }
                $this->address_array["gedcomnr"][$this->nraddress2] = $address_gedcomnr;
                $this->connect['item_id'][$this->connect_nr] = $address_gedcomnr;
                $_SESSION['address_gedcomnr'] = $address_gedcomnr;
                // *** Reset address GEDCOM number ***
                $_SESSION['new_address_gedcomnr'] -= 1;
            }

            // *** Street Aldfaer/ Pro-gen ***
            //1 RESI
            //2 ADDR Citystreet 18
            //3 CITY Wellen
            if ($this->level[2] == 'ADDR') {
                if ($this->buffer[6] === '2 ADDR') {
                    $this->address_array["address"][$this->nraddress2] .= substr($this->buffer[0], 7);
                    $this->processed = true;
                }
                $this->address_array["address"][$this->nraddress2] = $this->process_texts($this->address_array["address"][$this->nraddress2], '2');
            }

            // *** Living place for Aldfaer ***
            //1 RESI
            //2 ADDR Oosteind 44
            //3 CONT Zwaag
            //3 CITY Zwaag
            if ($this->buffer[6] === '3 CITY') {
                $this->processed = true;
                if ($this->address_array["place"][$this->nraddress2]) {
                    $this->address_array["place"][$this->nraddress2] .= ', ';
                }
                $this->address_array["place"][$this->nraddress2] .= substr($this->buffer[0], 7);
            }

            // GRAMPS:
            // 1 RESI
            // 2 ADDR
            // 3 CITY Huixquilucan
            // 3 STAE Edo. de Mexico
            // 3 POST 55555
            // 3 CTRY Mexico
            // 2 PHON 52 (55) 1234-5xxx
            if ($this->buffer[6] === '3 STAE') {
                $this->processed = true;
                if ($this->address_array["place"][$this->nraddress2]) {
                    $this->address_array["place"][$this->nraddress2] .= ', ';
                }
                $this->address_array["place"][$this->nraddress2] .= substr($this->buffer[0], 7);
            }
            if ($this->buffer[6] === '3 CTRY') {
                $this->processed = true;
                if ($this->address_array["place"][$this->nraddress2]) {
                    $this->address_array["place"][$this->nraddress2] .= ', ';
                }
                $this->address_array["place"][$this->nraddress2] .= substr($this->buffer[0], 7);
            }

            if ($this->buffer[6] === '3 POST') {
                $this->processed = true;
                $this->address_array["zip"][$this->nraddress2] = substr($this->buffer[0], 7);
            }

            if ($this->buffer[6] === '2 PHON') {
                $this->processed = true;
                $this->address_array["phone"][$this->nraddress2] = substr($this->buffer[0], 7);
            }

            // *** Living place for BK ***
            if ($this->buffer[6] === '2 PLAC') {
                $this->processed = true;
                if ($this->address_array["place"][$this->nraddress2]) {
                    $this->address_array["place"][$this->nraddress2] .= ', ';
                }
                $this->address_array["place"][$this->nraddress2] .= substr($this->buffer[0], 7);
            }

            // *** Texts by living places for BK, Aldfaer ***
            if ($this->level[2] == 'NOTE') {
                // TODO check this code.
                //if ($this->address_array["text"][$this->nraddress2]) {
                //    $this->address_array["text"][$this->nraddress2] .= '. ';
                //}
                $this->address_array["text"][$this->nraddress2] = $this->process_texts($this->address_array["text"][$this->nraddress2], '2');
            }

            // *** Texts by living place for SukuJutut ***
            if ($this->gen_program == "SukuJutut") {
                //if ($this->address_array["text"][$this->nraddress2]) {
                //    $this->address_array["text"][$this->nraddress2] .= '. ';
                //}
                $this->address_array["text"][$this->nraddress2] = $this->process_texts($this->address_array["text"][$this->nraddress2], '3');
            }

            // *** Date by living place for BK etc. ***
            //if ($this->buffer[6]=='2 DATE'){ $this->processed = true; $this->address_array["date"][$this->nraddress2]=substr($this->buffer[0],7); }
            if ($this->buffer[6] === '2 DATE') {
                $this->processed = true;
                $this->connect['date'][$this->connect_nr] = substr($this->buffer[0], 7);
            }

            // *** Source by address ***
            // *** Source also uses the connect table, so an extra SESSION is needed to store the address GEDCOM number ***
            if ($this->level[2] == 'SOUR') {
                //if ($connect_kind=='person'){
                //	$this->process_sources('person','pers_address_source',$_SESSION['address_gedcomnr'], '2');
                //}
                //else{
                //	$this->process_sources('family','fam_address_source',$_SESSION['address_gedcomnr'], '2');
                //}

                if ($connect_kind == 'person') {
                    $this->process_sources('person', 'pers_address_connect_source', $this->calculated_connect_id, '2');
                } else {
                    $this->process_sources('family', 'fam_address_connect_source', $this->calculated_connect_id, '2');
                }

                // *** Source by address ***
                //$this->process_sources('address','address_source',$this->calculated_connect_id, '2');
            }
        }
    }



    // *** Process all kind of STANDARD sources ***
    public function process_sources($connect_kind2, $connect_sub_kind2, $connect_connect_id2, $number): void
    {
        // 2 SOUR Source text
        // *** Store source - connections ***
        if ($this->buffer[6] === $number . ' SOUR') {
            $this->processed = true;
            $this->connect_nr++;
            $this->calculated_connect_id++;
            $this->connect['kind'][$this->connect_nr] = $connect_kind2;
            $this->connect['sub_kind'][$this->connect_nr] = $connect_sub_kind2;
            $this->connect['connect_id'][$this->connect_nr] = $connect_connect_id2;
            $this->connect['text'][$this->connect_nr] = '';
            $this->connect['quality'][$this->connect_nr] = '';
            $this->connect['source_id'][$this->connect_nr] = '';
            $this->connect['item_id'][$this->connect_nr] = '';
            $this->connect['text'][$this->connect_nr] = '';
            $this->connect['page'][$this->connect_nr] = '';
            $this->connect['role'][$this->connect_nr] = '';
            $this->connect['date'][$this->connect_nr] = '';
            $this->connect['place'][$this->connect_nr] = '';

            // *** Check for @ characters (=link to shared source), or save text ***
            // 1 SOUR @S1@
            if (substr($this->buffer[0], 7, 1) === '@') {
                // *** Trim needed for MyHeritage (double spaces behind a source line) ***
                $this->buffer[0] = trim($this->buffer[0]);

                $this->connect['source_id'][$this->connect_nr] = substr($this->buffer[0], 8, -1);
                if ($this->add_tree == true || $this->reassign == true) {
                    $this->connect['source_id'][$this->connect_nr] = $this->reassign_ged(substr($this->buffer[0], 8, -1), 'S');
                }
            } else {
                // *** Jan. 2021: all sources are stored in the source table ***

                $new_source_gedcomnr = 'S' . $_SESSION['new_source_gedcomnr'];
                if ($this->add_tree == true || $this->reassign == true) {
                    $new_source_gedcomnr = $this->reassign_ged('S' . $_SESSION['new_source_gedcomnr'], 'S');
                }
                $this->connect['source_id'][$this->connect_nr] = $new_source_gedcomnr;

                $this->nrsource++;
                //unset ($this->source);  //Reset array
                $this->source["source_gedcomnr"][$this->nrsource] = $new_source_gedcomnr;
                $this->source["source_status"][$this->nrsource] = '';
                $this->source["source_title"][$this->nrsource] = '';
                $this->source["source_abbr"][$this->nrsource] = '';
                $this->source["source_date"][$this->nrsource] = '';
                $this->source["source_publ"][$this->nrsource] = '';
                $this->source["source_place"][$this->nrsource] = '';
                $this->source["source_refn"][$this->nrsource] = '';
                $this->source["source_auth"][$this->nrsource] = '';
                $this->source["source_subj"][$this->nrsource] = '';
                $this->source["source_item"][$this->nrsource] = '';
                $this->source["source_kind"][$this->nrsource] = '';
                $this->source["source_text"][$this->nrsource] = '';
                $this->source["source_repo_name"][$this->nrsource] = '';
                $this->source["source_repo_caln"][$this->nrsource] = '';
                $this->source["source_repo_page"][$this->nrsource] = '';
                $this->source["source_repo_gedcomnr"][$this->nrsource] = '';
                $this->source["source_unprocessed_tags"][$this->nrsource] = '';
                $this->source["new_date"][$this->nrsource] = '1970-01-01';
                $this->source["new_time"][$this->nrsource] = '00:00:01';
                $this->source["new_user_id"] = '';
                $this->source["changed_date"][$this->nrsource] = '';
                $this->source["changed_time"][$this->nrsource] = '';
                $this->source["changed_user_id"] = '';

                $this->source["source_text"][$this->nrsource] .= substr($this->buffer[0], 7);

                // *** Next GEDCOM number ***
                $_SESSION['new_source_gedcomnr'] += 1;
            }
        }

        // *** Source text ***
        if ($this->level[$number] == 'SOUR') {
            if ($this->buffer[6] === ($number + 1) . ' CONT') {
                $this->processed = true;
                $this->source["source_text"][$this->nrsource] .= $this->cont(substr($this->buffer[0], 7));
            }
            if ($this->buffer[6] === ($number + 1) . ' CONC') {
                $this->processed = true;
                $this->source["source_text"][$this->nrsource] .= $this->conc(substr($this->buffer[0], 7));
            }
        }

        // *** Sources in GEDCOM test file ***
        // 1 SOUR This source is embedded in the record instead of being a link to a
        // 2 CONC separate SOURCE record.
        // 2 CONT The source description can use any number of lines
        // 2 TEXT Text from a source. The preferred approach is to cite sources by
        // 3 CONC links to SOURCE records.
        // 3 CONT Here is a new line of text from the source.
        if ($number < 3 && $this->level[$number + 1] == 'TEXT') {
            if ($this->buffer[6] === ($number + 1) . ' TEXT') {
                $this->processed = true;
                if ($this->source["source_text"][$this->nrsource]) {
                    $this->source["source_text"][$this->nrsource] .= '<br>';
                }
                $this->source["source_text"][$this->nrsource] .= substr($this->buffer[0], 7);
            }
            if ($this->buffer[6] === ($number + 2) . ' CONT') {
                $this->processed = true;
                $this->source["source_text"][$this->nrsource] .= $this->cont(substr($this->buffer[0], 7));
            }
            if ($this->buffer[6] === ($number + 2) . ' CONC') {
                $this->processed = true;
                $this->source["source_text"][$this->nrsource] .= $this->conc(substr($this->buffer[0], 7));
            }
        }

        // *** Source citation ***
        if (isset($this->level[$number + 1]) && $this->level[$number + 1] == 'DATA') {
            if ($this->buffer[6] === ($number + 1) . ' DATA') {
                $this->processed = true; //$this->connect['text'][$this->connect_nr]=substr($this->buffer[0], 7);
            }

            // *** Source date ***
            if ($this->buffer[6] === ($number + 2) . ' DATE') {
                $this->processed = true;
                $this->connect['date'][$this->connect_nr] = substr($this->buffer[0], 7);
            }
            // *** Source place ***
            if ($this->buffer[6] === ($number + 2) . ' PLAC') {
                $this->processed = true;
                $this->connect['place'][$this->connect_nr] = substr($this->buffer[0], 7);
            }

            if ($this->buffer[6] === ($number + 2) . ' TEXT') {
                if ($this->connect['text'][$this->connect_nr]) {
                    $this->connect['text'][$this->connect_nr] .= '<br>';
                }
                $this->processed = true;
                $this->connect['text'][$this->connect_nr] .= substr($this->buffer[0], 7);
            }
            if ($this->buffer[6] === ($number + 3) . ' CONT') {
                $this->processed = true;
                $this->connect['text'][$this->connect_nr] .= $this->cont(substr($this->buffer[0], 7));
            }
            if ($this->buffer[6] === ($number + 3) . ' CONC') {
                $this->processed = true;
                $this->connect['text'][$this->connect_nr] .= $this->conc(substr($this->buffer[0], 7));
            }
        }

        // *** Picture by source
        // 3 OBJE
        // 4 TITL Multimedia link about this source
        // 4 FORM jpeg
        // 4 NOTE @N26@
        // 4 FILE ImgFile.JPG
        // *** ONLY CHECK: 3 OBJE ***
        //if ($this->level[$number+1]=='OBJE'){

        //if ($this->level[3]=='OBJE'){
        //SKIP @xx@, otherwise this will process errors: 3 OBJE @M1@
        if ($this->level[3] == 'OBJE' && substr($this->buffer[0], 7, 1) !== '@') {
            $this->process_picture('connect', $this->calculated_connect_id, 'picture');
        }

        // *** Source reference (own code) ***
        if ($this->buffer[6] === ($number + 1) . ' REFN') {
            $this->processed = true;
            $this->source["source_refn"][$this->nrsource] = substr($this->buffer[0], 7);
        }

        // *** Source page ***
        if ($number < 3 && $this->level[$number + 1] == 'PAGE') {
            if ($this->buffer[6] === ($number + 1) . ' PAGE') {
                $this->processed = true;
                $this->connect['page'][$this->connect_nr] = substr($this->buffer[0], 7);
            }
            if ($this->buffer[6] === ($number + 2) . ' CONT') {
                $this->processed = true;
                $this->connect['page'][$this->connect_nr] .= $this->cont(substr($this->buffer[0], 7));
            }
            if ($this->buffer[6] === ($number + 2) . ' CONC') {
                $this->processed = true;
                $this->connect['page'][$this->connect_nr] .= $this->conc(substr($this->buffer[0], 7));
            }
        }

        // *** Source role ***
        if ($this->buffer[6] === ($number + 1) . ' ROLE') {
            $this->processed = true;
            $this->connect['role'][$this->connect_nr] = substr($this->buffer[0], 7);
        }

        // *** Aldfaer time ***
        //2 _ALDFAER_TIME 08:00:00
        //if (substr($this->buffer[0],0,15)=='2 _ALDFAER_TIME'){
        //	if (nrbron>0) then BronDate[nrbron]:=copy(buf,8,length(buf));
        //}

        // *** Source quality, stored in connection table ***
        if ($this->buffer[6] === ($number + 1) . ' QUAY') {
            $this->processed = true;
            $this->connect['quality'][$this->connect_nr] = $this->process_quality();
        }

        // *** Added oct. 2024 NOT TESTED YET ***
        // *** Save date ***
        // 1 _NEW (GEDCOM 5.5.1) or: 1 CREA (GEDCOM 7.x)
        // 2 DATE 04 AUG 2004
        if ($this->buffer[6] === ($number + 1) . '_NEW' || $this->buffer[6] === ($number + 1) . 'CREA') {
            $created_changed = $this->get_created_changed();
            $this->processed = $created_changed["processed"];
            if ($created_changed["date"]) {
                $this->source["new_date"] = $created_changed["date"];
            }
            if ($created_changed["time"]) {
                $this->source["new_time"] = $created_changed["time"];
            }
            if ($created_changed["user_id"]) {
                $this->source["new_user_id"] = $created_changed["user_id"];
            }
        }

        // *** Added oct. 2024 NOT TESTED YET ***
        // *** Save date ***
        // 1 CHAN
        // 2 DATE 04 AUG 2004
        if ($this->buffer[6] === ($number + 1) . 'CHAN') {
            $created_changed = $this->get_created_changed();
            $this->processed = $created_changed["processed"];
            if ($created_changed["date"]) {
                $this->source["changed_date"] = $created_changed["date"];
            }
            if ($created_changed["time"]) {
                $this->source["changed_time"] = $created_changed["time"];
            }
            if ($created_changed["user_id"]) {
                $this->source["changed_user_id"] = $created_changed["user_id"];
            }
        }
    }

    /** 
     * EXAMPLES
     * if ($this->level[2]=='OBJE'){
     *  $this->process_picture('person',$pers_gedcomnumber,'picture_birth');
     * }
     * if ($this->level[2]=='OBJE'){
     *  $this->process_picture('person',$pers_gedcomnumber,'picture_event_'.$this->calculated_event_id);
     * }
     * if ($this->level[2]=='OBJE'){
     *  $this->process_picture('family',$gedcomnumber,'picture_fam_marr_notice');
     * }
     * if ($this->level[3]=='OBJE' AND substr($this->buffer[0],7,1)!='@'){
     *  $this->process_picture('connect',$this->calculated_connect_id,'picture');
     * }
     * if ($this->level[1]=='OBJE') {
     *  $this->process_picture('source',$source["id"],'picture');
     * }
     */
    public function process_picture($connect_kind, $connect_id, $picture): void
    {
        $event_picture = false;
        // *** Just for sure: set default values ***
        $test_number1 = '1';
        $test_number2 = '2';
        // *** picture = person or family picture ***
        if ($picture == 'picture') {
            $test_number1 = '1';
            $test_number2 = '2';
        } elseif ($picture == 'picture_birth') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_bapt') {
            $test_number1 = '2';
            $test_number2 = '3';
        }
        // *** picture_death = pictures or cards etc. by death ***
        elseif ($picture == 'picture_death') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_buried') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_church_notice') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_notice') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr_church') {
            $test_number1 = '2';
            $test_number2 = '3';
        } elseif ($picture == 'picture_fam_marr') {
            $test_number1 = '2';
            $test_number2 = '3';
        }
        // *** Picture by event ***
        elseif (substr($picture, 0, 13) === 'picture_event') {
            $event_picture = true;
            $test_number1 = '2';
            $test_number2 = '3';
        }

        // *** Picture by source ***
        // 3 OBJE
        // 4 TITL Multimedia link about this source
        // 4 FORM jpeg
        // 4 NOTE @N26@
        // 4 FILE ImgFile.JPG
        if ($connect_kind == 'connect' && $picture == 'picture') {
            $test_number1 = '3';
            $test_number2 = '4';
        }

        // *** External object/ image (could be multiple lines, they will be processed!) ***
        // 1 OBJE @O3@
        if ($this->buffer[6] === $test_number1 . ' OBJE' && substr($this->buffer[0], 7, 1) === '@') {
            // *** Connection to seperate object (image) stored in connection table ***
            $this->processed = true;
            $this->connect_nr++;
            $this->calculated_connect_id++;

            if ($connect_kind == 'person') {
                $this->connect['kind'][$this->connect_nr] = 'person';
                $this->connect['sub_kind'][$this->connect_nr] = 'pers_object';
            } elseif ($connect_kind == 'family') {
                $this->connect['kind'][$this->connect_nr] = 'family';
                $this->connect['sub_kind'][$this->connect_nr] = 'fam_object';
            } elseif ($connect_kind == 'source') {
                $this->connect['kind'][$this->connect_nr] = 'source';
                $this->connect['sub_kind'][$this->connect_nr] = 'source_object';
            }

            $this->connect['connect_id'][$this->connect_nr] = $connect_id;
            $this->connect['text'][$this->connect_nr] = '';
            // *** Check for @ characters (=link to shared source), or save text ***
            $this->connect['source_id'][$this->connect_nr] = '';
            $this->connect['item_id'][$this->connect_nr] = '';
            //$this->connect['text'][$this->connect_nr]='';
            if (substr($this->buffer[0], 7, 1) === '@') {
                // *** Saved as source_id in database, but connect_item_id is probably better... ***
                $this->connect['source_id'][$this->connect_nr] = substr($this->buffer[0], 8, -1);
                if ($this->add_tree == true || $this->reassign == true) {
                    $this->connect['source_id'][$this->connect_nr] = $this->reassign_ged(substr($this->buffer[0], 8, -1), 'O');
                }
            } else {
                $this->connect['text'][$this->connect_nr] .= substr($this->buffer[0], 7);
            }
            $this->connect['quality'][$this->connect_nr] = '';
            //PLACE NOT IN USE YET
            $this->connect['place'][$this->connect_nr] = '';
            $this->connect['page'][$this->connect_nr] = '';
            $this->connect['role'][$this->connect_nr] = '';
            $this->connect['date'][$this->connect_nr] = '';
        }

        // *** Objects without reference ***
        // 3 OBJE H:\haza21v3\plaatjes\IM000247.jpg
        // *** Skip link to object with reference: 1 OBJE @O3@ ***
        if ($this->buffer[6] === $test_number1 . ' OBJE' && substr($this->buffer[0], 7, 1) !== '@') {
            $this->processed = true;
            if ($event_picture == true) {
                // *** Process picture by event ***
                $this->event_nr++;
                $this->event_nr2 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr2] = $this->calculated_event_id;
                $this->event['level2'][$this->event_nr2] = '2';
                $this->event['connect_kind'][$this->event_nr2] = $connect_kind;
                $this->event['connect_id'][$this->event_nr2] = $connect_id;
                $this->event['kind'][$this->event_nr2] = $picture; // picture = person or family picture.
                $this->event['event'][$this->event_nr2] = '';
                $this->event['event_extra'][$this->event_nr2] = '';
                $this->event['gedcom'][$this->event_nr2] = 'OBJE';
                $this->event['date'][$this->event_nr2] = '';
                $this->event['text'][$this->event_nr2] = '';
                $this->event['place'][$this->event_nr2] = '';
            } else {
                $this->event_nr++;
                $this->event_nr1 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr] = '1';
                $this->event['connect_kind'][$this->event_nr] = $connect_kind;
                $this->event['connect_id'][$this->event_nr] = $connect_id;
                $this->event['kind'][$this->event_nr] = $picture; // picture = person or family picture.
                $this->event['event'][$this->event_nr] = '';
                $this->event['event_extra'][$this->event_nr] = '';
                $this->event['gedcom'][$this->event_nr] = 'OBJE';
                $this->event['date'][$this->event_nr] = '';
                $this->event['text'][$this->event_nr] = '';
                $this->event['place'][$this->event_nr] = '';
            }

            // *** Haza-data picture ***
            if (substr($this->buffer[0], 7)) {
                $this->processed = true;
                $photo = substr($this->buffer[0], 7);
                $photo = $this->humo_basename($photo);
                if ($event_picture == true) {
                    $this->event['event'][$this->event_nr2] = $photo;
                } else {
                    $this->event['event'][$this->event_nr] = $photo;
                }
            }
        }

        // *** Gramps ***
        // 1 OBJE
        // 2 FORM URL
        // 2 TITL GEDCOM 5.5 documentation web site
        // 2 FILE http://homepages.rootsweb.com/~pmcbride/gedcom/55gctoc.htm
        if (substr($this->buffer[0], 0, 10) === $test_number2 . ' FORM URL') {
            $this->processed = true;
            if ($event_picture == true) {
                $this->event['kind'][$this->event_nr2] = 'URL';
            } else {
                $this->event['kind'][$this->event_nr] = 'URL';
            }
        }

        if ($this->buffer[6] === $test_number2 . ' FILE') {
            $this->processed = true;
            $photo = substr($this->buffer[0], 7);
            // *** Aldfaer sometimes uses: 2 FILE \bestand.jpg ***
            $photo = $this->humo_basename($photo);
            if ($event_picture == true) {
                $this->event['event'][$this->event_nr2] = $photo;
            } else {
                $this->event['event'][$this->event_nr] = $photo;
            }
        }

        // *** Aldfaer ***
        // 2 TITL text
        // 3 CONT text second line
        //if ($this->level[2]=='TITL'){
        if ($this->level[$test_number2] == 'TITL') {
            $this->processed = true;
            if ($event_picture == true) {
                $this->event['text'][$this->event_nr2] = $this->process_texts($this->event['text'][$this->event_nr2], $test_number2);
            } else {
                $this->event['text'][$this->event_nr] = $this->process_texts($this->event['text'][$this->event_nr], $test_number2);
            }
        }

        // *** 2 FORM jpeg ***
        //if ($this->level[2]=='FORM'){
        if ($this->level[$test_number2] == 'FORM') {
            $this->processed = true;
            if ($event_picture == true) {
                $this->event['event_extra'][$this->event_nr2] = substr($this->buffer[0], 7);
            } else {
                $this->event['event_extra'][$this->event_nr] = substr($this->buffer[0], 7);
            }
        }

        // *** Text by photo Haza-21 ***
        if ($this->level[2] == 'NOTE') {
            if ($event_picture == true) {
                $this->event['text'][$this->event_nr2] = $this->process_texts($this->event['text'][$this->event_nr2], $test_number2);
            } else {
                $this->event['text'][$this->event_nr] = $this->process_texts($this->event['text'][$this->event_nr], $test_number2);
            }
        }

        if ($this->buffer[6] === $test_number2 . ' DATE') {
            $this->processed = true;
            if ($event_picture == true) {
                $this->event['date'][$this->event_nr2] = substr($this->buffer[0], 7);
            } else {
                $this->event['date'][$this->event_nr] = substr($this->buffer[0], 7);
            }
        }

        // *** Source by pictures ***
        if ($event_picture == true) {
            // no source by picture by event at this moment...
        } elseif ($this->level[2] == 'SOUR' && $this->level[3] != 'OBJE') {
            //if ($this->level[2]=='SOUR'){
            // *** Don't process a source by a object by a source. The script will stop with error ***
            $event_id = $this->event['calculated_event_id'][$this->event_nr];
            $this->process_sources('person', 'pers_event_source', $event_id, $test_number2);
        }
    }

    public function changed_datetime($item, $changed_date, $changed_time)
    {
        $changed_datetime = '';
        if ($changed_date) {
            $changed_datetime = ", " . $item . " = '" . date('Y-m-d H:i:s', strtotime($changed_date . ' ' . $changed_time)) .  "'";
        }
        return $changed_datetime;
    }

    function get_created_changed()
    {
        $created_changed = [];
        $created_changed["processed"] = false;
        $created_changed["date"] = '';
        $created_changed["time"] = '';
        $created_changed["user_id"] = '';

        if ($this->buffer[6] === '1 _NEW' || $this->buffer[6] === '1 CREA' || $this->buffer[6] === '1 CHAN') {
            $created_changed["processed"] = true;
        }
        if ($this->buffer[6] === '2 DATE') {
            $created_changed["processed"] = true;
            $created_changed["date"] = substr($this->buffer[0], 7);
        }
        if ($this->buffer[6] === '3 TIME') {
            $created_changed["processed"] = true;
            $created_changed["time"] = substr($this->buffer[0], 7);
        }

        // 1 CHAN also supports notes for GEDCOM 7. BUT: multiple dates by change notes are not supported.

        // *** Oct. 2024 process: 2 _USR 1 ***
        if ($this->buffer[6] === '2 _USR') {
            $user_id = substr($this->buffer[0], 7);
            if (is_numeric($user_id)) {
                $created_changed["processed"] = true;
                $created_changed["user_id"] = $user_id;
            }
        }

        return $created_changed;
    }

    function process_association($gedcomnumber, $connect_kind = 'person')
    {
        // *** Sept. 2024: Aldfaer GEDCOM 7 ***
        // CHECK FOR GEDCOM 7.
        // 1 EVEN ==>> EVENT level 1
        // 2 TYPE birth registration
        // 2 DATE 2 JAN 1980
        // 2 SOUR @S5@
        // 2 _OBJE
        // 3 FILE 0d0d3dfdf7eb5ec8d94609dc49079b2a.jpg
        // 2 ASSO @I4@ ==>> EVENT level 2
        // 3 ROLE OFFICIATOR
        // 2 ASSO @I3@
        // 3 ROLE WITN
        // 2 ASSO @I5@ ==>> EVENT level 2
        // 3 ROLE OTHER
        // 4 PHRASE informant

        // GEDCOM 7.x
        // 1 CHR
        // 2 ASSO @I7@
        // 3 ROLE GODP (only use GEDCOM 7.x. defined list: WITN, CLERGY, etc.)

        // GEDCOM 7 birth and death declaration:
        // 1 EVEN
        // 2 TYPE birth registration
        // 2 ASSO @VOID@
        // 3 PHRASE Mr Stockdale -> event_event
        // 3 ROLE OTHER
        // 4 PHRASE Teacher -> event_event_extra

        if ($this->buffer[6] === '2 ASSO') {
            $this->processed = true;

            // Database example
            // event_connect_id = I1 (main person)
            // event_connect_id2 = I2012 (witness)

            // *** Remark: date, place, text, source isn't needed for ASSO's (and not supported in GEDCOM 7). These items are stored in one "birth/ death declaration event ***
            if ($this->level[1] == 'EVEN') {
                $this->event_nr++;
                $this->event_nr2 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr2] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr2] = '2';
                $this->event['connect_kind'][$this->event_nr2] = '';
                if ($this->event['kind'][$this->event_nr1] == 'birth_declaration') {
                    $this->event['connect_kind'][$this->event_nr2] = 'birth_declaration';
                }
                if ($this->event['kind'][$this->event_nr1] == 'death_declaration') {
                    $this->event['connect_kind'][$this->event_nr2] = 'death_declaration';
                }
                $this->event['connect_id'][$this->event_nr2] = $gedcomnumber;
                $this->event['connect_kind2'][$this->event_nr2] = 'person';
                $this->event['connect_id2'][$this->event_nr2] = substr($this->buffer[0], 8, -1);
                $this->event['kind'][$this->event_nr2] = 'ASSO';
                $this->event['event'][$this->event_nr2] = '';
                $this->event['event_extra'][$this->event_nr2] = '';
                $this->event['gedcom'][$this->event_nr2] = '';
                $this->event['date'][$this->event_nr2] = '';
                $this->event['place'][$this->event_nr2] = '';
                $this->event['text'][$this->event_nr2] = '';
            } else {
                $this->event_nr++;
                $this->event_nr1 = $this->event_nr;
                $this->calculated_event_id++;

                // *** Also save calculated_event_id in array, so it can be used to check event_id ***
                $this->event['calculated_event_id'][$this->event_nr] = $this->calculated_event_id;
                $this->event['level'][$this->event_nr] = '1';
                $this->event['connect_kind'][$this->event_nr] = $connect_kind;
                $this->event['connect_id'][$this->event_nr] = $gedcomnumber;
                $this->event['connect_kind2'][$this->event_nr] = 'person';
                $this->event['connect_id2'][$this->event_nr] = substr($this->buffer[0], 8, -1);
                $this->event['kind'][$this->event_nr] = 'ASSO';
                $this->event['event'][$this->event_nr] = '';
                $this->event['event_extra'][$this->event_nr] = '';
                $this->event['gedcom'][$this->event_nr] = '';
                $this->event['date'][$this->event_nr] = '';
                $this->event['place'][$this->event_nr] = '';
                $this->event['text'][$this->event_nr] = '';
            }

            // 2 ASSO @VOID@
            // 3 PHRASE Mr Stockdale -> event_event
            if (substr($this->buffer[0], 7, 6) == '@VOID@') {
                $this->processed = true;

                if ($this->level[1] == 'EVEN') {
                    $this->event['connect_kind2'][$this->event_nr2] = '';
                    $this->event['connect_id2'][$this->event_nr2] = '';
                } else {
                    $this->event['connect_kind2'][$this->event_nr] = '';
                    $this->event['connect_id2'][$this->event_nr] = '';
                }
            }
        }

        // GEDCOM 7.x
        // 2 ASSO @VOID@
        // 3 PHRASE Mr Stockdale -> event_event
        if ($this->buffer[8] == '3 PHRASE') {
            $this->processed = true;

            if ($this->level[1] == 'EVEN') {
                $this->event['event'][$this->event_nr2] = substr($this->buffer[0], 9);
            } else {
                $this->event['event'][$this->event_nr1] = substr($this->buffer[0], 9);
            }
        }

        // GEDCOM 7.x
        if ($this->buffer[6] == '3 ROLE') {
            //TODO: only import allowed roles (see GEDCOM 7.x):
            //CHIL, CLERGY, FATH, FRIEND, GODP, HUSB, MOTH, MULTIPLE, NGHBR, OFFICIATOR, PARENT, SPOU, WIFE, WITN, OTHER.
            $this->processed = true;
            if ($this->level[1] == 'EVEN') {
                $this->event['gedcom'][$this->event_nr2] = substr($this->buffer[0], 7);
            } else {
                $this->event['gedcom'][$this->event_nr1] = substr($this->buffer[0], 7);
            }
        }

        // GEDCOM 7.x
        if ($this->buffer[8] == '4 PHRASE') {
            //if ($event_gedcom == 'OTHER'  && $this->buffer[8] == '4 PHRASE'){
            $this->processed = true;
            if ($this->level[1] == 'EVEN') {
                $this->event['event_extra'][$this->event_nr2] = substr($this->buffer[0], 9);
            } else {
                $this->event['event_extra'][$this->event_nr1] = substr($this->buffer[0], 9);
            }
        }
    }

    public function get_not_processed()
    {
        return $this->not_processed;
    }

    function check_event($length)
    {
        $this->processed = true;
        $this->event_status = true;
        $event_temp = '';
        if (substr($this->buffer[0], $length)) {
            $event_temp = substr($this->buffer[0], $length);
        }
        return $event_temp;
    }

    function set_buffer(): void
    {
        $this->buffer[1] = $this->buffer[0][0]; // *** first character ***
        $this->buffer[5] = substr($this->buffer[0], 0, 5);
        $this->buffer[6] = substr($this->buffer[0], 0, 6);
        $this->buffer[7] = substr($this->buffer[0], 0, 7);
        $this->buffer[8] = substr($this->buffer[0], 0, 8);
    }
}
