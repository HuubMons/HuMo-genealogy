<?php

/**
 * GedcomExport class
 * 
 * Jun. 2025 Huub: rebuild to class GedcomExport.
 */

namespace Genealogy\Include;

use PDO;
use PDOException;

class GedcomExport
{
    private $dbh, $db_functions, $humo_option;
    private $tree_id, $buffer, $gedcom_version = '551', $gedcom_sources = '';
    private $persids = array(), $famsids = array(), $noteids = array();

    public function __construct($dbh, $db_functions, $humo_option, $tree_id)
    {
        $this->dbh = $dbh;
        $this->db_functions = $db_functions;
        $this->humo_option = $humo_option;
        $this->tree_id = $tree_id;

        if (isset($_POST['gedcom_version'])) {
            $this->gedcom_version = $_POST['gedcom_version'];
        }

        if (isset($_POST['gedcom_sources'])) {
            $this->gedcom_sources = $_POST['gedcom_sources'];
        }
    }

    public function exportGedcom($export)
    {
        // Needed to process witnesses etc.
        if (isset($this->tree_id) && $this->tree_id) {
            $this->db_functions->set_tree_id($this->tree_id);
        }

        if (isset($this->tree_id) && isset($_POST['submit_button'])) {
            if ($export["part_tree"] == 'part' && isset($_POST['kind_tree']) && $_POST['kind_tree'] == "descendant") {
                // map descendants
                $desc_fams = '';
                $desc_pers = $_POST['person'];
                $max_gens = $_POST['nr_generations'];

                $fam_search = $this->dbh->query("SELECT pers_fams, pers_famc
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber ='" . $desc_pers . "'");
                $fam_searchDb = $fam_search->fetch(PDO::FETCH_OBJ);
                if ($fam_searchDb->pers_fams != '') {
                    $desc_fams = $fam_searchDb->pers_fams;
                } else {
                    $desc_fams = $fam_searchDb->pers_famc;
                }

                $generation_number = 0;

                // *** Only use first marriage of selected person to avoid error. Other marriages will be processed in the function! ***
                $pers_fams = explode(";", $desc_fams);
                $this->descendants($pers_fams[0], $desc_pers, $generation_number, $max_gens);
            }
            if ($export["part_tree"] == 'part' && isset($_POST['kind_tree']) && $_POST['kind_tree'] == "ancestor") {
                // map ancestors
                $anc_pers = $_POST['person'];
                $max_gens = $_POST['nr_generations'] + 2;
                $this->ancestors($anc_pers, $max_gens);
            }

            $gedcom_char_set = '';
            if (isset($_POST['gedcom_char_set'])) {
                $gedcom_char_set = $_POST['gedcom_char_set'];
            }

            $gedcom_texts = '';
            if (isset($_POST['gedcom_texts'])) {
                $gedcom_texts = $_POST['gedcom_texts'];
            }

            // PMB our minimal option
            //$gedcom_minimal = '';
            //if (isset($_POST['gedcom_minimal'])) {
            //    $gedcom_minimal = $_POST['gedcom_minimal'];
            //}

            $fh = fopen($export['path'] . $export['file_name'], 'w') or die("<b>ERROR: no permission to open a new file! Please check permissions of admin/gedcom_files folder!</b>");

            // *** GEDCOM header ***
            $this->buffer = '';

            if ($this->gedcom_version == '551') {
                // *** GEDCOM 5.5.1 ***
                //if ($gedcom_char_set=='UTF-8'){
                //  // *** Add BOM header to UTF-8 file ***
                //  $this->buffer.= "\xEF\xBB\xBF";
                //}
                $this->buffer .= "0 HEAD\r\n";
                $this->buffer .= "1 SOUR HuMo-genealogy\r\n";
                $this->buffer .= "2 VERS " . $this->humo_option["version"] . "\r\n";
                $this->buffer .= "2 NAME HuMo-genealogy\r\n";
                $this->buffer .= "2 CORP HuMo-genealogy software\r\n";
                $this->buffer .= "3 ADDR https://humo-gen.com\r\n";
                $this->buffer .= "1 SUBM @S1@\r\n";
                $this->buffer .= "1 GEDC\r\n";
                $this->buffer .= "2 VERS 5.5.1\r\n";
                $this->buffer .= "2 FORM Lineage-Linked\r\n";

                if ($gedcom_char_set == 'UTF-8') {
                    $this->buffer .= "1 CHAR UTF-8\r\n";
                } elseif ($gedcom_char_set == 'ANSI') {
                    $this->buffer .= "1 CHAR ANSI\r\n";
                } else {
                    $this->buffer .= "1 CHAR ASCII\r\n";
                }
            } else {
                /**
                 * GEDCOM 7.0
                 * 
                 * 0 HEAD
                 * 1 GEDC
                 * 2 VERS 7.0
                 * 1 SCHMA
                 * 2 TAG _SKYPEID http://xmlns.com/foaf/0.1/skypeID
                 * 2 TAG _JABBERID http://xmlns.com/foaf/0.1/jabberID
                 * 1 SOUR https://gedcom.io/
                 * 2 VERS 0.3
                 * 2 NAME GEDCOM Steering Committee
                 * 2 CORP FamilySearch
                 */
                $this->buffer .= "0 HEAD\r\n";
                $this->buffer .= "1 GEDC\r\n";
                $this->buffer .= "2 VERS 7.0\r\n";
                $this->buffer .= "1 SOUR https://humo-gen.com\r\n";
                $this->buffer .= "2 VERS " . $this->humo_option["version"] . "\r\n";
                $this->buffer .= "2 NAME HuMo-genealogy\r\n";
                $this->buffer .= "2 CORP HuMo-genealogy software\r\n";
            }

            // 0 @S1@ SUBM
            // 1 NAME Huub Mons
            // 1 ADDR address
            $this->buffer .= "0 @S1@ SUBM\r\n";
            if ($export['submit_name']) {
                $this->buffer .= "1 NAME " . $export['submit_name'] . "\r\n";
            } else {
                $this->buffer .= "1 NAME Unknown\r\n";
            }

            if ($export['submit_address'] != '') {
                $this->buffer .= "1 ADDR " . $export['submit_address'] . "\r\n";
                if ($export['submit_country'] != '') {
                    $this->buffer .= "2 CTRY " . $export['submit_country'] . "\r\n";
                }
            }

            if ($export['submit_mail'] != '') {
                $this->buffer .= "1 EMAIL " . $export['submit_mail'] . "\r\n";
            }

            fwrite($fh, $this->buffer);
            //$this->buffer = str_replace("\n", "<br>", $this->buffer);
            //echo '<p>'.$this->buffer;

            /**
             * EXAMPLE:
             * 0 @I1181@ INDI
             * 1 RIN 1181
             * 1 REFN Eigencode
             * 1 NAME Voornaam/Achternaam/
             * 1 SEX M
             * 1 BIRT
             * 2 DATE 21 FEB 1960
             * 2 PLAC 1e woonplaats
             * 1 RESI
             * 2 ADDR 2e woonplaats
             * 1 RESI
             * 2 ADDR 3e woonplaats
             * 1 RESI
             * 2 ADDR 4e woonplaats
             * 1 OCCU 1e beroep
             * 1 OCCU 2e beroep
             * 1 EVEN
             * 2 TYPE living
             * 1 _COLOR 0
             * 1 NOTE @N51@
             * 1 FAMS @F10@
             * 1 FAMC @F8@
             * 1 _NEW
             * 2 TYPE 2
             * 2 DATE 8 JAN 2005
             * 3 TIME 20:31:24
             */

            // *** Count records in all tables for Bootstrap progress bar ***
            $total = $this->dbh->query("SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'");
            $total = $total->fetch();
            $nr_records = $total[0];

            $count_fam = $this->dbh->query("SELECT COUNT(fam_id) FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "'");
            $count_famDb = $count_fam->fetch();
            $nr_records += $count_famDb[0];

            $total = $this->dbh->query("SELECT COUNT(*) FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'");
            $total = $total->fetch();
            $nr_records += $total[0];

            if ($gedcom_texts == 'yes') {
                $total = $this->dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='" . $this->tree_id . "'");
                $total = $total->fetch();
                $nr_records += $total[0];
            }

            $total = $this->dbh->query("SELECT COUNT(*) FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'");
            $total = $total->fetch();
            $nr_records += $total[0];

            $total = $this->dbh->query("SELECT COUNT(*) FROM humo_repositories WHERE repo_tree_id='" . $this->tree_id . "'");
            $total = $total->fetch();
            $nr_records += $total[0];

            // determines the steps in percentages.
            // regular: 2%
            $devider = 50;
            // 1% for larger files with over 200,000 lines
            if ($nr_records > 200000) {
                $devider = 100;
            }
            // 0.5% for very large files
            if ($nr_records > 1000000) {
                $devider = 200;
            }
            $step = round($nr_records / $devider);
            if ($step < 1) {
                $step = 1;
            }
            $perc = 0;
            $record_nr = 0;

            //echo $nr_records . '!' . $step . '!' . $devider . '!' . $perc;
            //$record_nr++;
            //$perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);

            // *** To reduce use of memory, first read pers_id only ***
            $persons_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            $persons_result = $this->dbh->query($persons_qry);
            while ($persons = $persons_result->fetch(PDO::FETCH_OBJ)) {
                // *** Now read all person items ***
                $person = $this->db_functions->get_person_with_id($persons->pers_id);

                if ($export["part_tree"] == 'part' && !in_array($person->pers_gedcomnumber, $this->persids)) {
                    continue;
                }

                // 0 @I1181@ INDI *** Gedcomnumber ***
                $this->buffer = '0 @' . $person->pers_gedcomnumber . "@ INDI\r\n";

                if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') {
                    echo $person->pers_gedcomnumber . ' ';
                }

                // 1 RIN 1181
                // Not really necessary, so disabled this line...
                //$this->buffer.='1 RIN '.substr($person->pers_gedcomnumber,1)."\r\n";

                // 1 REFN Code *** Own code ***
                if ($person->pers_own_code) {
                    $this->buffer .= '1 REFN ' . $person->pers_own_code . "\r\n";
                }

                // *** Name, add a space after first name if first name is present ***
                // 1 NAME Firstname /Lastname/
                $this->buffer .= '1 NAME ' . $person->pers_firstname;
                if ($person->pers_firstname) {
                    // add a space after first name if first name is present
                    $this->buffer .= ' ';
                }
                $this->buffer .= '/' . str_replace("_", " ", $person->pers_prefix);
                $this->buffer .= $person->pers_lastname . "/\r\n";

                // *** december 2021: pers_callname no longer in use ***
                //if ($person->pers_callname){
                //  $this->buffer.='2 NICK '.$person->pers_callname."\r\n";
                //}

                // Prefix is exported by name!
                //if ($person->pers_prefix){
                //  $this->buffer.='2 SPFX '.$person->pers_prefix."\r\n";
                //}

                // *** Create general person_events array ***
                $person_events = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'all');

                // PMB if 'minimal' option selected don't export this
                if ($_POST['export_type'] == 'normal') {
                    // *** Text and source by name ***
                    if ($this->gedcom_sources == 'yes') {
                        $this->sources_export('person', 'pers_name_source', $person->pers_gedcomnumber, 2);
                    }

                    if ($gedcom_texts == 'yes' && $person->pers_name_text) {
                        $this->buffer .= '2 NOTE ' . $this->process_text(3, $person->pers_name_text);
                    }

                    foreach ($person_events as $person_event) {
                        // *** Export all name items, like 2 _AKAN etc. ***
                        if ($person_event->event_kind == 'name' or $person_event->event_kind == 'NPFX' or $person_event->event_kind == 'NSFX') {
                            $eventgedcom = $person_event->event_gedcom;
                            // *** 2 _RUFNAME is only used in BK, HuMo-genealogy uses 2 _RUFN ***
                            //if($nameDb->event_gedcom == "_RUFN"){
                            //  $eventgedcom = '_RUFNAME';
                            //}
                            $this->buffer .= '2 ' . $eventgedcom . ' ' . $person_event->event_event . "\r\n";
                            if ($person_event->event_date) {
                                $this->buffer .= '3 DATE ' . $this->process_date($person_event->event_date) . "\r\n";
                            }
                            if ($this->gedcom_sources == 'yes') {
                                $this->sources_export('person', 'pers_event_source', $person_event->event_id, 3);
                            }
                            if ($gedcom_texts == 'yes' && $person_event->event_text) {
                                $this->buffer .= '3 NOTE ' . $this->process_text(4, $person_event->event_text);
                            }
                        }

                        // *** Export of person titles ***
                        // 1 TITL Ir.
                        if ($person_event->event_kind == 'title') {
                            $eventgedcom = $person_event->event_gedcom;
                            $this->buffer .= '1 TITL ' . $person_event->event_event . "\r\n";
                            if ($person_event->event_date) {
                                $this->buffer .= '2 DATE ' . $this->process_date($person_event->event_date) . "\r\n";
                            }
                            if ($this->gedcom_sources == 'yes') {
                                $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                            }
                            if ($gedcom_texts == 'yes' && $person_event->event_text) {
                                $this->buffer .= '2 NOTE ' . $this->process_text(4, $person_event->event_text);
                            }
                        }
                    }
                }


                foreach ($person_events as $person_event) {
                    // TODO check event ADOP (to be removed?).
                    // *** Adoption ***
                    // 1 ADOP
                    // 2 DATE 15 MAR 2025
                    // 2 FAMC @F2@
                    if ($person_event->event_kind == 'adoption') {
                        $this->buffer .= '1 ADOP' . "\r\n";
                        if ($person_event->event_event) {
                            $this->buffer .= '2 FAMC @' . $person_event->event_event . '@' . "\r\n";
                        }
                        if ($person_event->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($person_event->event_date) . "\r\n";
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person_event->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(4, $person_event->event_text);
                        }
                    }

                    // *** Nobility (used by Aldfaer & HuMo-genealogy program) added may 2025. Use event tag, there is no tag nobility in GEDCOM 7. ***
                    // 1 EVEN Predikaat naam
                    // 2 TYPE predikaat
                    if ($person_event->event_kind == 'nobility') {
                        $this->buffer .= '1 EVEN ' . $person_event->event_event . "\r\n";
                        $this->buffer .= '2 TYPE predikaat' . "\r\n";
                        if ($person_event->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($person_event->event_date) . "\r\n";
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person_event->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(4, $person_event->event_text);
                        }
                    }
                }

                // TODO (see also GEDCOM 7 specification):
                // event_kind = adoption_by_person
                // *** Aldfaer adopted/ steph/ legal/ foster childs ***
                // 1 FAMC @F2@
                // 2 PEDI adopted
                // 2 PEDI steph
                // 2 PEDI legal
                // 2 PEDI foster
                // 2 PEDI birth     is in use in Aldfaer 8.
                // 3 PHRASE
                // 2 NOTE

                // TODO (GEDCOM 7 specification: there is 1 PROP):
                // event_kind = lordship
                // 1 PROP Heerlijkheid
                // 2 TYPE heerlijkheid

                // TODO (check GEDCOM 7 specification):
                // event_kind = ash dispersion.
                // 2 TYPE ash dispersion

                if ($person->pers_patronym) {
                    $this->buffer .= '1 _PATR ' . $person->pers_patronym . "\r\n";
                }

                // *** Sex ***
                $this->buffer .= '1 SEX ' . $person->pers_sexe . "\r\n";
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_sexe_source', $person->pers_gedcomnumber, 2);
                }

                // *** Birth data ***
                // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
                if (
                    $person->pers_birth_date || $person->pers_birth_place || $person->pers_birth_text
                    || (isset($person->pers_stillborn) && $person->pers_stillborn == 'y')
                ) {
                    $this->buffer .= "1 BIRT\r\n";
                    if ($person->pers_birth_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($person->pers_birth_date) . "\r\n";
                        if (isset($person->pers_birth_date_hebnight) && $person->pers_birth_date_hebnight == 'y') {
                            $this->buffer .= '2 _HNIT y' . "\r\n";
                        }
                    }
                    if ($person->pers_birth_place) {
                        $this->buffer .= $this->process_place($person->pers_birth_place, 2);
                    }
                    if ($person->pers_birth_time) {
                        $this->buffer .= '2 TIME ' . $person->pers_birth_time . "\r\n";
                    }
                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_birth_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person->pers_birth_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $person->pers_birth_text);
                        }

                        if (isset($person->pers_stillborn) && $person->pers_stillborn == 'y') {
                            $this->buffer .= '2 TYPE stillborn' . "\r\n";
                        }

                        // *** New sept. 2023 ***
                        // *** Remark: only exported if there is another birth item ***
                        // *** Oct 2024: for GEDCOM 7 changed to seperate event ***
                        if ($this->gedcom_version == '551') {
                            $this->buffer .= $this->export_witnesses('birth_declaration', $person->pers_gedcomnumber, 'ASSO');
                        }
                    }
                }

                // GEDCOM 5.5.1
                // 1 EVEN
                // 2 TYPE birth registration
                // 2 DATE 2 JAN 1980
                // 2 SOUR @S5@
                //
                // GEDCOM 7.0 (Aldfaer)
                // 1 EVEN
                // 2 TYPE birth registration
                // 2 DATE 2 JAN 1980
                // 2 SOUR @S5@
                // 2 _OBJE
                // 3 FILE 0d0d3dfdf7eb5ec8d94609dc49079b2a.jpg
                // 2 ASSO @I4@
                // 3 ROLE OFFICIATOR
                // 2 ASSO @I3@
                // 3 ROLE WITN
                // 2 ASSO @I5@
                // 3 ROLE OTHER
                // 4 PHRASE informant

                //  *** NEW oct. 2024: seperate event for Birth registration ***
                if ($this->gedcom_version != '551') {
                    $birth_declarationDb = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'birth_declaration');
                    $birth_decl_witnesses = $this->export_witnesses('birth_declaration', $person->pers_gedcomnumber, 'ASSO');

                    if ($birth_declarationDb || $birth_decl_witnesses) {
                        $this->buffer .= "1 EVEN\r\n";
                        $this->buffer .= "2 TYPE birth registration\r\n";

                        if ($birth_declarationDb->event_date) {
                            $this->buffer .= '2 DATE ' . $birth_declarationDb->event_date . "\r\n";
                        }
                        if ($birth_declarationDb->event_place) {
                            $this->buffer .= '2 PLAC ' . $birth_declarationDb->event_place . "\r\n";
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'birth_decl_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $birth_declarationDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $birth_declarationDb->event_text);
                        }

                        $this->buffer .= $birth_decl_witnesses;
                    }
                }

                // *** Christened data ***
                // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
                if ($person->pers_bapt_date || $person->pers_bapt_place || $person->pers_bapt_text || $person->pers_religion) {
                    $this->buffer .= "1 CHR\r\n";
                    if ($person->pers_bapt_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($person->pers_bapt_date) . "\r\n";
                    }
                    if ($person->pers_bapt_place) {
                        $this->buffer .= $this->process_place($person->pers_bapt_place, 2);
                    }
                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        // *** Person religion. This is 1 CHR -> 2 RELI! 1 RELI is exported as event (after profession) ***
                        if ($person->pers_religion) {
                            $this->buffer .= '2 RELI ' . $person->pers_religion . "\r\n";
                        }

                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_bapt_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person->pers_bapt_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $person->pers_bapt_text);
                        }

                        // *** Remark: only exported if there is another baptism item ***
                        $this->buffer .= $this->export_witnesses('CHR', $person->pers_gedcomnumber, 'ASSO');
                    }
                }

                // *** Death data ***
                // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
                if ($person->pers_death_date || $person->pers_death_place || $person->pers_death_text || $person->pers_death_cause) {
                    $this->buffer .= "1 DEAT\r\n";
                    if ($person->pers_death_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($person->pers_death_date) . "\r\n";
                        if (isset($person->pers_death_date_hebnight) && $person->pers_death_date_hebnight == 'y') {
                            $this->buffer .= '2 _HNIT y' . "\r\n";
                        }
                    }
                    if ($person->pers_death_place) {
                        $this->buffer .= $this->process_place($person->pers_death_place, 2);
                    }
                    if ($person->pers_death_time) {
                        $this->buffer .= '2 TIME ' . $person->pers_death_time . "\r\n";
                    }

                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_death_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person->pers_death_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $person->pers_death_text);
                        }
                        if ($person->pers_death_cause) {
                            $this->buffer .= '2 CAUS ' . $person->pers_death_cause . "\r\n";
                        }
                        if ($person->pers_death_age) {
                            $this->buffer .= '2 AGE ' . $person->pers_death_age . "\r\n";
                        }

                        // *** Remark: only exported if there is another baptism item ***
                        // *** Oct 2024: for GEDCOM 7 changed to seperate event ***
                        if ($this->gedcom_version == '551') {
                            $this->buffer .= $this->export_witnesses('death_declaration', $person->pers_gedcomnumber, 'ASSO');
                        }
                    }
                }

                //  *** NEW oct. 2024: seperate event for death registration ***
                if ($this->gedcom_version != '551') {
                    $death_declarationDb = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'death_declaration');
                    $death_decl_witnesses = $this->export_witnesses('death_declaration', $person->pers_gedcomnumber, 'ASSO');

                    if ($death_declarationDb || $death_decl_witnesses) {
                        $this->buffer .= "1 EVEN\r\n";
                        $this->buffer .= "2 TYPE death registration\r\n";

                        if ($death_declarationDb->event_date) {
                            $this->buffer .= '2 DATE ' . $death_declarationDb->event_date . "\r\n";
                        }
                        if ($death_declarationDb->event_place) {
                            $this->buffer .= '2 PLAC ' . $death_declarationDb->event_place . "\r\n";
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'death_decl_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $death_declarationDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $death_declarationDb->event_text);
                        }

                        $this->buffer .= $death_decl_witnesses;
                    }
                }

                // *** Buried data ***
                // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
                if ($person->pers_buried_date || $person->pers_buried_place || $person->pers_buried_text || $person->pers_cremation) {
                    $this->buffer .= "1 BURI\r\n";
                    if ($person->pers_buried_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($person->pers_buried_date) . "\r\n";
                        if (isset($person->pers_buried_date_hebnight) && $person->pers_buried_date_hebnight == 'y') {
                            $this->buffer .= '2 _HNIT y' . "\r\n";
                        }
                    }
                    if ($person->pers_buried_place) {
                        $this->buffer .= $this->process_place($person->pers_buried_place, 2);
                    }

                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_buried_source', $person->pers_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $person->pers_buried_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $person->pers_buried_text);
                        }
                        if ($person->pers_cremation == '1') {
                            $this->buffer .= '2 TYPE cremation' . "\r\n";
                        }
                        if ($person->pers_cremation == 'R') {
                            $this->buffer .= '2 TYPE resomated' . "\r\n";
                        }
                        if ($person->pers_cremation == 'S') {
                            $this->buffer .= '2 TYPE sailor\'s grave' . "\r\n";
                        }
                        if ($person->pers_cremation == 'D') {
                            $this->buffer .= '2 TYPE donated to science' . "\r\n";
                        }

                        // *** Remark: only exported if there is another baptism item ***
                        $this->buffer .= $this->export_witnesses('BURI', $person->pers_gedcomnumber, 'ASSO');
                    }
                }


                // PMB if 'minimal' option selected don't export this
                if ($_POST['export_type'] == 'normal') {
                    // *** Addresses (shared addresses are no valid GEDCOM 5.5.1 but is used in some genealogical programs) ***
                    // *** Living place ***
                    // 1 RESI
                    // 2 ADDR Ridderkerk
                    // 1 RESI
                    // 2 ADDR Slikkerveer
                    $this->addresses_export('person', 'person_address', $person->pers_gedcomnumber);

                    // *** Occupation ***
                    $professionqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'profession');

                    //while ($professionDb = $professionqry->fetch(PDO::FETCH_OBJ)) {
                    foreach ($professionqry as $professionDb) {
                        $this->buffer .= '1 OCCU ' . $professionDb->event_event . "\r\n";

                        if ($professionDb->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($professionDb->event_date) . "\r\n";
                        }
                        if ($professionDb->event_place) {
                            $this->buffer .= '2 PLAC ' . $professionDb->event_place . "\r\n";
                        }
                        if ($gedcom_texts == 'yes' && $professionDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $professionDb->event_text);
                        }

                        // *** Source by occupation ***
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_event_source', $professionDb->event_id, 2);
                        }
                    }

                    // *** Religion. REMARK: this is religion event 1 RELI. Baptise religion is saved as 1 CHR -> 2 RELI. ***
                    $professionqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'religion');
                    //while ($professionDb = $professionqry->fetch(PDO::FETCH_OBJ)) {
                    foreach ($professionqry as $professionDb) {
                        $this->buffer .= '1 RELI ' . $professionDb->event_event . "\r\n";

                        if ($professionDb->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($professionDb->event_date) . "\r\n";
                        }
                        if ($professionDb->event_place) {
                            $this->buffer .= '2 PLAC ' . $professionDb->event_place . "\r\n";
                        }
                        if ($gedcom_texts == 'yes' && $professionDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $professionDb->event_text);
                        }

                        // *** Source by religion ***
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_event_source', $professionDb->event_id, 2);
                        }
                    }

                    // *** Person source ***
                    if ($this->gedcom_sources == 'yes') {
                        $this->sources_export('person', 'person_source', $person->pers_gedcomnumber, 1);
                    }

                    // *** Person pictures ***
                    $sourceqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'picture');
                    foreach ($sourceqry as $sourceDb) {

                        $this->buffer .= "1 OBJE\r\n";
                        $this->buffer .= "2 FORM jpg\r\n";
                        $this->buffer .= '2 FILE ' . $sourceDb->event_event . "\r\n";
                        if ($sourceDb->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($sourceDb->event_date) . "\r\n";
                        }

                        if ($gedcom_texts == 'yes' && $sourceDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $sourceDb->event_text);
                        }

                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('person', 'pers_event_source', $sourceDb->event_id, 2);
                        }
                    }

                    // *** Person Note ***
                    if ($gedcom_texts == 'yes' && $person->pers_text) {
                        $this->buffer .= '1 NOTE ' . $this->process_text(2, $person->pers_text);
                        $this->sources_export('person', 'pers_text_source', $person->pers_gedcomnumber, 2);
                    }

                    // *** Person color marks ***
                    $sourceqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'person_colour_mark');
                    foreach ($sourceqry as $sourceDb) {
                        $this->buffer .= '1 _COLOR ' . $sourceDb->event_event . "\r\n";
                        //if ($this->gedcom_sources=='yes'){
                        //	$this->sources_export('person','pers_event_source',$sourceDb->event_id,2);
                        //}
                    }

                    // *** Person events ***
                    $event_qry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'event');
                    foreach ($event_qry as $eventDb) {
                        // TODO: Check: ADOP, no longer in use?
                        $eventMapping = [
                            'ADOP' => '1 ADOP',
                            '_ADPF' => '1 _ADPF',
                            '_ADPM' => '1 _ADPM',
                            'AFN' => '1 AFN',
                            'ARVL' => '1 ARVL',
                            'BAPM' => '1 BAPM',
                            'BAPL' => '1 BAPL',
                            'BARM' => '1 BARM',
                            'BASM' => '1 BASM',
                            'BLES' => '1 BLES',
                            '_BRTM' => '1 _BRTM',
                            'CAST' => '1 CAST',
                            'CENS' => '1 CENS',
                            'CHRA' => '1 CHRA',
                            'CONF' => '1 CONF',
                            'CONL' => '1 CONL',
                            'DPRT' => '1 DPRT',
                            'EDUC' => '1 EDUC',
                            'EMIG' => '1 EMIG',
                            'ENDL' => '1 ENDL',
                            'EVEN' => '1 EVEN',
                            '_EYEC' => '1 _EYEC',
                            'FCOM' => '1 FCOM',
                            '_FNRL' => '1 _FNRL',
                            'GRAD' => '1 GRAD',
                            '_HAIR' => '1 _HAIR',
                            '_HEIG' => '1 _HEIG',
                            'IDNO' => '1 IDNO',
                            'IMMI' => '1 IMMI',
                            '_INTE' => '1 _INTE',
                            'LEGI' => '1 LEGI',
                            '_MEDC' => '1 _MEDC',
                            'MILI' => '1 _MILT',
                            'NATU' => '1 NATU',
                            'NATI' => '1 NATI',
                            'NCHI' => '1 NCHI',
                            '_NMAR' => '1 _NMAR',
                            'ORDN' => '1 ORDN',
                            'PROB' => '1 PROB',
                            'PROP' => '1 PROP',
                            'RETI' => '1 RETI',
                            'SLGC' => '1 SLGC',
                            'SLGL' => '1 SLGL',
                            'SSN' => '1 SSN',
                            'TXPY' => '1 TXPY',
                            '_WEIG' => '1 _WEIG',
                            'WILL' => '1 WILL',
                            '_YART' => '1 _YART',
                        ];
                        $process_event = false;
                        if (array_key_exists($eventDb->event_gedcom, $eventMapping)) {
                            $process_event = true;
                            $event_gedcom = $eventMapping[$eventDb->event_gedcom];
                        }

                        // *** No text behind first line, add text at second NOTE line ***
                        if ($process_event) {
                            $this->buffer .= $event_gedcom;
                            // *** Add text behind GEDCOM tag ***
                            if ($eventDb->event_event) {
                                $this->buffer .= ' ' . $eventDb->event_event;
                            }
                            $this->buffer .= "\r\n";
                            if ($eventDb->event_text) {
                                $this->buffer .= '2 NOTE ' . $this->process_text(3, $eventDb->event_text);
                            }
                            if ($eventDb->event_date) {
                                $this->buffer .= '2 DATE ' . $this->process_date($eventDb->event_date) . "\r\n";
                            }
                            if ($eventDb->event_place) {
                                $this->buffer .= '2 PLAC ' . $eventDb->event_place . "\r\n";
                            }
                        }

                        // *** Source ***
                        $this->sources_export('person', 'pers_event_source', $eventDb->event_id, 2);
                    }
                }


                // *** Quality ***
                // Disabled because normally quality belongs to a source.
                //if ($person->pers_quality=='0' || $person->pers_quality){
                //	$this->buffer.='2 QUAY '.$person->pers_quality."\r\n";
                //}

                // *** FAMS ***
                if ($person->pers_fams) {
                    $pers_fams = explode(";", $person->pers_fams);
                    foreach ($pers_fams as $i => $value) {
                        if ($export["part_tree"] == 'part' && !in_array($pers_fams[$i], $this->famsids)) {
                            continue;
                        }
                        $this->buffer .= '1 FAMS @' . $pers_fams[$i] . "@\r\n";
                    }
                }

                // *** FAMC ***
                if ($person->pers_famc) {
                    if ($export["part_tree"] == 'part' && !in_array($person->pers_famc, $this->famsids)) {
                        // don't export FAMC
                    } else {
                        $this->buffer .= '1 FAMC @' . $person->pers_famc . "@\r\n";
                    }
                }


                // PMB if 'minimal' option selected don't export this
                // not sure if this is the right thing to do here...
                // for my purposes, it is, as anyone not marked as
                // dead will be 'living', so potentially hidden
                // _NEW also causes a problem with PAF import
                if ($_POST['export_type'] == 'normal') {
                    // *** Privacy filter, HuMo-genealogy, Haza-data ***
                    if ($person->pers_alive == 'alive') {
                        $this->buffer .= "1 EVEN\r\n";
                        $this->buffer .= "2 TYPE living\r\n";
                    }
                    // *** Privacy filter option for HuMo-genealogy ***
                    if ($person->pers_alive == 'deceased') {
                        if (
                            !$person->pers_death_date && !$person->pers_death_place && !$person->pers_death_text && !$person->pers_death_cause
                            && !$person->pers_buried_date && !$person->pers_buried_place && !$person->pers_buried_text && !$person->pers_cremation
                        ) {
                            $this->buffer .= "1 EVEN\r\n";
                            $this->buffer .= "2 TYPE deceased\r\n";
                        }
                    }

                    // *** Datetime new in database ***
                    // 1_NEW
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $this->buffer .= $this->process_datetime('new', $person->pers_new_datetime, $person->pers_new_user_id);
                    // *** Datetime changed in database ***
                    // 1_CHAN
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $this->buffer .= $this->process_datetime('changed', $person->pers_changed_datetime, $person->pers_changed_user_id);
                }

                // *** Write person data ***
                $this->decode();
                fwrite($fh, $this->buffer);

                // *** Update processed lines ***
                $record_nr++;
                $perc = $this->update_bootstrap_bar($record_nr, $step, $devider, $perc);
                //flush();

                // *** Show person data on screen ***
                //$this->buffer = str_replace("\r\n", "<br>", $this->buffer);
                //echo $this->buffer;
            }

            /**
             * EXAMPLE
             * 0 @F1@ FAM
             * 1 HUSB @I2@
             * 1 WIFE @I3@
             * 1 MARL
             * 2 DATE 25 AUG 1683
             * 2 PLAC Arnhem
             * 1 MARR
             * 2 TYPE civil
             * 2 DATE 30 NOV 1683
             * 2 PLAC Arnhem
             * 2 NOTE @N311@
             * 1 CHIL @I4@
             * 1 CHIL @I5@
             * 1 CHIL @I6@
             */

            // *** FAMILY DATA ***
            // *** To reduce use of memory, first read fam_id only ***
            $families_qry = $this->dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "'");
            while ($families = $families_qry->fetch(PDO::FETCH_OBJ)) {
                // *** Now read all family items ***
                //$family_qry = $this->dbh->query("SELECT * FROM humo_families WHERE fam_id='" . $families->fam_id . "'");
                //$family = $family_qry->fetch(PDO::FETCH_OBJ);
                $family = $this->db_functions->get_family_with_id($families->fam_id);

                if ($export["part_tree"] == 'part'  && !in_array($family->fam_gedcomnumber, $this->famsids)) {
                    continue;
                }

                // 0 @I1181@ INDI *** Gedcomnumber ***
                $this->buffer = '0 @' . $family->fam_gedcomnumber . "@ FAM\r\n";

                if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') {
                    echo $family->fam_gedcomnumber . ' ';
                }


                /* TODO (used twice for family events)
                // *** Create general family_events array ***
                //$event_qry = $this->dbh->query("SELECT * FROM humo_events
                //    WHERE relation_id='" . $family->fam_id . "'
                //    ORDER BY event_kind, event_order");
                $event_qry = $db_functions->get_events_connect('family', $family->fam_gedcomnumber, 'all');
                $family_events = $event_qry->fetchAll(PDO::FETCH_ASSOC);
                */
                /*
                // Test lines
                foreach ($family_events as $family_event) {
                    echo $family_event['event_id'] . ' ';
                    echo $family_event['event_connect_id'] . ' ';
                    echo $family_event['event_kind'] . ' ';
                    echo $family_event['event_order'] . ' ';
                    echo $family_event['event_event'] . ' ';
                    echo '<br>';
                }
                */


                if ($family->fam_man) {
                    if ($export["part_tree"] == 'part' && !in_array($family->fam_man, $this->persids)) {
                        // skip if not included (e.g. if spouse of base person in ancestor export or spouses of descendants in desc export are not checked for export)
                    } else {
                        $this->buffer .= '1 HUSB @' . $family->fam_man . "@\r\n";
                    }
                }

                if ($family->fam_woman) {
                    if ($export["part_tree"] == 'part' && !in_array($family->fam_woman, $this->persids)) {
                        // skip if not included
                    } else {
                        $this->buffer .= '1 WIFE @' . $family->fam_woman . "@\r\n";
                    }
                }

                // *** Pro-gen & HuMo-genealogy: Living together ***
                if ($family->fam_relation_date || $family->fam_relation_place || $family->fam_relation_text) {
                    $this->buffer .= "1 _LIV\r\n";

                    // *** Relation start date ***
                    if ($family->fam_relation_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($family->fam_relation_date) . "\r\n";
                    }

                    // *** Relation end date ***
                    // How to export this date?

                    if ($family->fam_relation_place) {
                        $this->buffer .= $this->process_place($family->fam_relation_place, 2);
                    }
                    if ($this->gedcom_sources == 'yes') {
                        $this->sources_export('family', 'fam_relation_source', $family->fam_gedcomnumber, 2);
                    }
                    if ($gedcom_texts == 'yes' && $family->fam_relation_text) {
                        $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_relation_text);
                    }
                }

                // PMB if 'minimal' option selected don't export this
                if ($_POST['export_type'] == 'normal') {
                    // *** Marriage notice ***
                    if ($family->fam_marr_notice_date || $family->fam_marr_notice_place || $family->fam_marr_notice_text) {
                        $this->buffer .= "1 MARB\r\n";
                        $this->buffer .= "2 TYPE civil\r\n";
                        if ($family->fam_marr_notice_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($family->fam_marr_notice_date) . "\r\n";
                            if (isset($family->fam_marr_notice_date_hebnight) && $family->fam_marr_notice_date_hebnight == 'y') {
                                $this->buffer .= '2 _HNIT y' . "\r\n";
                            }
                        }
                        if ($family->fam_marr_notice_place) {
                            $this->buffer .= $this->process_place($family->fam_marr_notice_place, 2);
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_marr_notice_source', $family->fam_gedcomnumber, 2);
                        }

                        if ($gedcom_texts == 'yes' && $family->fam_marr_notice_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_marr_notice_text);
                        }
                    }

                    // *** Marriage notice church ***
                    if ($family->fam_marr_church_notice_date || $family->fam_marr_church_notice_place || $family->fam_marr_church_notice_text) {
                        $this->buffer .= "1 MARB\r\n";
                        $this->buffer .= "2 TYPE religious\r\n";
                        if ($family->fam_marr_church_notice_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($family->fam_marr_church_notice_date) . "\r\n";
                            if (isset($family->fam_marr_church_notice_date_hebnight) && $family->fam_marr_church_notice_date_hebnight == 'y') {
                                $this->buffer .= '2 _HNIT y' . "\r\n";
                            }
                        }
                        if ($family->fam_marr_church_notice_place) {
                            $this->buffer .= $this->process_place($family->fam_marr_church_notice_place, 2);
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_marr_church_notice_source', $family->fam_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $family->fam_marr_church_notice_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_marr_church_notice_text);
                        }
                    }
                }

                // *** Marriage ***
                if ($family->fam_marr_date || $family->fam_marr_place || $family->fam_marr_text) {
                    $this->buffer .= "1 MARR\r\n";
                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        // 1 MARR
                        // 2 TYPE partners
                        /*
                        living together
                        living apart together
                        intentionally unmarried mother
                        homosexual
                        non-marital
                        extramarital
                        partners
                        registered
                        unknown
                        */
                        //$this->buffer .= "2 TYPE civil\r\n";
                        $this->buffer .= '2 TYPE ' . $family->fam_kind . "\r\n";
                    }

                    if ($family->fam_marr_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($family->fam_marr_date) . "\r\n";
                        if (isset($family->fam_marr_date_hebnight) && $family->fam_marr_date_hebnight == 'y') {
                            $this->buffer .= '2 _HNIT y' . "\r\n";
                        }
                    }
                    if ($family->fam_marr_place) {
                        $this->buffer .= $this->process_place($family->fam_marr_place, 2);
                    }

                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_marr_source', $family->fam_gedcomnumber, 2);
                        }
                        if ($family->fam_man_age) {
                            $this->buffer .= "2 HUSB\r\n3 AGE " . $family->fam_man_age . "\r\n";
                        }
                        if ($family->fam_woman_age) {
                            $this->buffer .= "2 WIFE\r\n3 AGE " . $family->fam_woman_age . "\r\n";
                        }
                        if ($gedcom_texts == 'yes' && $family->fam_marr_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_marr_text);
                        }

                        // *** Remark: only exported if there is another baptism item ***
                        $this->buffer .= $this->export_witnesses('MARR', $family->fam_gedcomnumber, 'ASSO');
                    }
                }

                // *** Marriage religious ***
                if ($family->fam_marr_church_date || $family->fam_marr_church_place || $family->fam_marr_church_text) {
                    $this->buffer .= "1 MARR\r\n";
                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        $this->buffer .= "2 TYPE religious\r\n";
                    }

                    if ($family->fam_marr_church_date) {
                        $this->buffer .= '2 DATE ' . $this->process_date($family->fam_marr_church_date) . "\r\n";
                        if (isset($family->fam_marr_church_date_hebnight) && $family->fam_marr_church_date_hebnight == 'y') {
                            $this->buffer .= '2 _HNIT y' . "\r\n";
                        }
                    }
                    if ($family->fam_marr_church_place) {
                        $this->buffer .= $this->process_place($family->fam_marr_church_place, 2);
                    }

                    // PMB if 'minimal' option selected don't export this
                    if ($_POST['export_type'] == 'normal') {
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_marr_church_source', $family->fam_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $family->fam_marr_church_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_marr_church_text);
                        }

                        // *** Remark: only exported if there is another baptism item ***
                        $this->buffer .= $this->export_witnesses('MARR_REL', $family->fam_gedcomnumber, 'ASSO');
                    }
                }


                // PMB if 'minimal' option selected don't export this
                if ($_POST['export_type'] == 'normal') {
                    // *** Divorced ***
                    if ($family->fam_div_date || $family->fam_div_place || $family->fam_div_text) {
                        $this->buffer .= "1 DIV\r\n";
                        if ($family->fam_div_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($family->fam_div_date) . "\r\n";
                        }
                        if ($family->fam_div_place) {
                            $this->buffer .= $this->process_place($family->fam_div_place, 2);
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_div_source', $family->fam_gedcomnumber, 2);
                        }
                        if ($gedcom_texts == 'yes' && $family->fam_div_text && $family->fam_div_text != 'DIVORCE') {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $family->fam_div_text);
                        }
                    }
                }

                if ($family->fam_children) {
                    $child = explode(";", $family->fam_children);
                    foreach ($child as $i => $value) {
                        if ($export["part_tree"] == 'part' && !in_array($child[$i], $this->persids)) {
                            continue;
                        }
                        $this->buffer .= '1 CHIL @' . $child[$i] . "@\r\n";
                    }
                }


                // PMB if 'minimal' option selected don't export this
                if ($_POST['export_type'] == 'normal') {
                    // *** Family source ***
                    if ($this->gedcom_sources == 'yes') {
                        $this->sources_export('family', 'family_source', $family->fam_gedcomnumber, 1);
                    }

                    // *** Addresses (shared addresses are no valid GEDCOM 5.5.1) ***
                    $this->addresses_export('family', 'family_address', $family->fam_gedcomnumber);

                    // *** Family pictures ***
                    $sourceqry = $this->db_functions->get_events_connect('family', $family->fam_gedcomnumber, 'picture');
                    foreach ($sourceqry as $sourceDb) {
                        $this->buffer .= "1 OBJE\r\n";
                        $this->buffer .= "2 FORM jpg\r\n";
                        $this->buffer .= '2 FILE ' . $sourceDb->event_event . "\r\n";
                        if ($sourceDb->event_date) {
                            $this->buffer .= '2 DATE ' . $this->process_date($sourceDb->event_date) . "\r\n";
                        }

                        if ($gedcom_texts == 'yes' && $sourceDb->event_text) {
                            $this->buffer .= '2 NOTE ' . $this->process_text(3, $sourceDb->event_text);
                        }

                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('family', 'fam_event_source', $sourceDb->event_id, 2);
                        }
                    }

                    // *** Family Note ***
                    if ($gedcom_texts == 'yes' && $family->fam_text) {
                        $this->buffer .= '1 NOTE ' . $this->process_text(2, $family->fam_text);
                        $this->sources_export('family', 'fam_text_source', $family->fam_gedcomnumber, 2);
                    }

                    // *** Family events ***
                    $event_qry = $this->db_functions->get_events_connect('family', $family->fam_gedcomnumber, 'event');
                    foreach ($event_qry as $eventDb) {
                        $process_event = false;
                        if ($eventDb->event_gedcom == 'ANUL') {
                            $process_event = true;
                            $event_gedcom = '1 ANUL';
                        }
                        if ($eventDb->event_gedcom == 'CENS') {
                            $process_event = true;
                            $event_gedcom = '1 CENS';
                        }
                        if ($eventDb->event_gedcom == 'DIVF') {
                            $process_event = true;
                            $event_gedcom = '1 DIVF';
                        }
                        if ($eventDb->event_gedcom == 'ENGA') {
                            $process_event = true;
                            $event_gedcom = '1 ENGA';
                        }
                        if ($eventDb->event_gedcom == 'EVEN') {
                            $process_event = true;
                            $event_gedcom = '1 EVEN';
                        }
                        if ($eventDb->event_gedcom == 'MARC') {
                            $process_event = true;
                            $event_gedcom = '1 MARC';
                        }
                        if ($eventDb->event_gedcom == 'MARL') {
                            $process_event = true;
                            $event_gedcom = '1 MARL';
                        }
                        if ($eventDb->event_gedcom == 'MARS') {
                            $process_event = true;
                            $event_gedcom = '1 MARS';
                        }
                        if ($eventDb->event_gedcom == 'SLGS') {
                            $process_event = true;
                            $event_gedcom = '1 SLGS';
                        }

                        // *** No text behind first line, add text at second NOTE line ***
                        if ($process_event) {
                            $this->buffer .= $event_gedcom;
                            if ($eventDb->event_event) {
                                $this->buffer .= ' ' . $eventDb->event_event;
                            }
                            $this->buffer .= "\r\n";
                            if ($eventDb->event_text) {
                                $this->buffer .= '2 NOTE ' . $this->process_text(3, $eventDb->event_text);
                            }
                            if ($eventDb->event_date) {
                                $this->buffer .= '2 DATE ' . $this->process_date($eventDb->event_date) . "\r\n";
                            }
                            if ($eventDb->event_place) {
                                $this->buffer .= '2 PLAC ' . $eventDb->event_place . "\r\n";
                            }
                        }
                    }

                    // *** Datetime new in database ***
                    // 1_NEW
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $this->buffer .= $this->process_datetime('new', $family->fam_new_datetime, $family->fam_new_user_id);
                    // *** Datetime changed in database ***
                    // 1_CHAN
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $this->buffer .= $this->process_datetime('changed', $family->fam_changed_datetime, $family->fam_changed_user_id);
                }


                // *** Write family data ***
                $this->decode();
                fwrite($fh, $this->buffer);

                // *** Update processed lines ***
                $record_nr++;
                $perc = $this->update_bootstrap_bar($record_nr, $step, $devider, $perc);
                //flush();

                // *** Show family data on screen ***
                //$this->buffer = str_replace("\r\n", "<br>", $this->buffer);
                //echo $this->buffer;
            }


            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {

                // *** Sources ***
                //0 @S1@ SOUR
                //1 TITL Persoonskaarten
                //1 DATE 24 JAN 2003
                //1 PLAC Heerhugowaard
                //1 REFN Pers-v
                //1 PHOTO @#APLAATJES\AKTEMONS.GIF GIF@
                //2 DSCR Afbeelding van Persoonskaarten
                //1 PHOTO @#APLAATJES\HUUB&LIN.JPG JPG@
                //2 DSCR Beschrijving
                //1 NOTE Persoonskaarten (van overleden personen) besteld bij CBVG te Den Haag.

                if ($export["part_tree"] == 'part') {
                    // only include sources that are used by the people in this partial tree
                    $source_array = array();
                    // find all sources referred to by persons (I233) or families (F233)
                    $qry = $this->dbh->query("SELECT connect_connect_id, connect_source_id FROM humo_connections
                        WHERE connect_tree_id='" . $this->tree_id . "' AND connect_source_id != ''");
                    while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($qryDb->connect_connect_id, $this->persids) || in_array($qryDb->connect_connect_id, $this->famsids)) {
                            $source_array[] = $qryDb->connect_source_id;
                        }
                    }
                    // find all sources referred to by addresses (233)
                    // shared addresses: we need a three-fold procedure....
                    // First: in the connections table search for exported persons/families that have an RESI number connection (R34)
                    $address_connect_qry = $this->dbh->query("SELECT connect_connect_id, connect_item_id
                        FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_sub_kind LIKE '%_address'");
                    $resi_array = array();
                    while ($address_connect_qryDb = $address_connect_qry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($address_connect_qryDb->connect_connect_id, $this->persids) || in_array($address_connect_qryDb->connect_connect_id, $this->famsids)) {
                            $resi_array[] = $address_connect_qryDb->connect_item_id;
                        }
                    }
                    // Second: in the address table search for the previously found R numbers and get their id number (33)
                    $address_address_qry = $this->dbh->query("SELECT address_gedcomnr, address_id FROM humo_addresses
                        WHERE address_tree_id='" . $this->tree_id . "' AND address_gedcomnr !='' ");
                    $resi_id_array = array();
                    while ($address_address_qryDb = $address_address_qry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($address_address_qryDb->address_gedcomnr, $resi_array)) {
                            $resi_id_array[] = $address_address_qryDb->address_id;
                        }
                    }
                    // Third: back in the connections table, find the previously found address id numbers and get the associated source ged number ($23)
                    $address_connect2_qry = $this->dbh->query("SELECT connect_connect_id, connect_source_id
                        FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_sub_kind = 'address_source'");
                    while ($address_connect2_qry_qryDb = $address_connect2_qry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($address_connect2_qry_qryDb->connect_connect_id, $resi_id_array)) {
                            $source_array[] = $address_connect2_qry_qryDb->connect_source_id;
                        }
                    }
                    // "direct" addresses
                    $addressqry = $this->dbh->query("SELECT address_id, address_connect_sub_kind, address_connect_id
                        FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'");
                    $source_address_array = array();
                    while ($addressqryDb = $addressqry->fetch(PDO::FETCH_OBJ)) {
                        if ($addressqryDb->address_connect_sub_kind == 'person' && in_array($addressqryDb->address_connect_id, $this->persids)) {
                            $source_address_array[] = $addressqryDb->address_id;
                        }
                        if ($addressqryDb->address_connect_sub_kind == 'family' && in_array($addressqryDb->address_connect_id, $this->famsids)) {
                            $source_address_array[] = $addressqryDb->address_id;
                        }
                    }
                    $addresssourceqry = $this->dbh->query("SELECT connect_source_id, connect_connect_id
                        FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_sub_kind LIKE 'address_%'");
                    while ($addresssourceqryDb = $addresssourceqry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($addresssourceqryDb->connect_connect_id, $source_address_array)) {
                            $source_array[] = $addresssourceqryDb->connect_source_id;
                        }
                    }

                    // find all sources referred to by events (233)
                    $eventqry = $this->dbh->query("SELECT event_id, event_connect_kind, event_connect_id FROM humo_events");
                    $source_event_array = array();
                    while ($eventqryDb = $eventqry->fetch(PDO::FETCH_OBJ)) {
                        if (
                            $eventqryDb->event_connect_kind == 'person'
                            && $eventqryDb->event_connect_id != '' && in_array($eventqryDb->event_connect_id, $this->persids)
                        ) {
                            $source_event_array[] = $eventqryDb->event_id;
                        }
                        if (
                            $eventqryDb->event_connect_kind == 'family' and
                            $eventqryDb->event_connect_id != '' && in_array($eventqryDb->event_connect_id, $this->famsids)
                        ) {
                            $source_event_array[] = $eventqryDb->event_id;
                        }
                    }
                    $eventsourceqry = $this->dbh->query("SELECT connect_source_id, connect_connect_id
                        FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_sub_kind LIKE 'event_%'");
                    while ($eventsourceqryDb = $eventsourceqry->fetch(PDO::FETCH_OBJ)) {
                        if (in_array($eventsourceqryDb->connect_connect_id, $source_event_array)) {
                            $source_array[] = $eventsourceqryDb->connect_source_id;
                        }
                    }

                    // eliminate duplicates
                    if (isset($source_array)) {
                        $source_array = array_unique($source_array);
                    }
                }

                if ($this->gedcom_sources == 'yes') {
                    $source_qry = $this->dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'");
                    while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) {
                        if ($export["part_tree"] == 'part'  && !in_array($sourceDb->source_gedcomnr, $source_array)) {
                            continue;
                        }

                        // 0 @I1181@ INDI *** Gedcomnumber ***
                        $this->buffer = '0 @' . $sourceDb->source_gedcomnr . "@ SOUR\r\n";

                        if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') echo $sourceDb->source_gedcomnr . ' ';
                        if ($sourceDb->source_title) {
                            $this->buffer .= '1 TITL ' . $sourceDb->source_title . "\r\n";
                        }
                        if ($sourceDb->source_abbr) {
                            $this->buffer .= '1 ABBR ' . $sourceDb->source_abbr . "\r\n";
                        }

                        // *** Source date and place. GEDCOM 7 uses DATA and EVEN tags ***
                        $buffer_temp = '';
                        $tag_number = 1;
                        if ($this->gedcom_version != '551') {
                            $tag_number = 3;
                        }
                        if ($sourceDb->source_date) {
                            $buffer_temp .= $tag_number . ' DATE ' . $this->process_date($sourceDb->source_date) . "\r\n";
                        }
                        if ($sourceDb->source_place) {
                            $buffer_temp .= $tag_number . ' PLAC ' . $sourceDb->source_place . "\r\n";
                        }

                        // GEDCOM 7 uses extra tags 1 DATA and 2 EVEN.
                        if ($buffer_temp && $this->gedcom_version != '551') {
                            $this->buffer .= '1 DATA' . "\r\n";
                            $this->buffer .= '2 EVEN' . "\r\n";
                        }
                        $this->buffer .= $buffer_temp;
                        // *** End of source date and place ***

                        if ($sourceDb->source_publ) {
                            $this->buffer .= '1 PUBL ' . $sourceDb->source_publ . "\r\n";
                        }
                        if ($sourceDb->source_refn) {
                            $this->buffer .= '1 REFN ' . $sourceDb->source_refn . "\r\n";
                        }
                        if ($sourceDb->source_auth) {
                            $this->buffer .= '1 AUTH ' . $sourceDb->source_auth . "\r\n";
                        }
                        if ($sourceDb->source_subj) {
                            $this->buffer .= '1 SUBJ ' . $sourceDb->source_subj . "\r\n";
                        }
                        if ($sourceDb->source_item) {
                            $this->buffer .= '1 ITEM ' . $sourceDb->source_item . "\r\n";
                        }
                        if ($sourceDb->source_kind) {
                            $this->buffer .= '1 KIND ' . $sourceDb->source_kind . "\r\n";
                        }
                        if ($sourceDb->source_text) {
                            $this->buffer .= '1 NOTE ' . $this->process_text(2, $sourceDb->source_text);
                        }
                        if (isset($sourceDb->source_status) && $sourceDb->source_status == 'restricted') {
                            $this->buffer .= '1 RESN privacy' . "\r\n";
                        }

                        // *** Source pictures ***
                        $source_pic_qry = $this->db_functions->get_events_connect('source', $sourceDb->source_gedcomnr, 'picture');
                        foreach ($source_pic_qry as $source_picDb) {
                            $this->buffer .= "1 OBJE\r\n";
                            $this->buffer .= "2 FORM jpg\r\n";
                            $this->buffer .= '2 FILE ' . $source_picDb->event_event . "\r\n";
                            if ($source_picDb->event_date) {
                                $this->buffer .= '2 DATE ' . $this->process_date($source_picDb->event_date) . "\r\n";
                            }

                            if ($gedcom_texts == 'yes' && $source_picDb->event_text) {
                                $this->buffer .= '2 NOTE ' . $this->process_text(3, $source_picDb->event_text);
                            }

                            //if ($this->gedcom_sources=='yes'){
                            //	$this->sources_export('source','source_event_source',$source_picDb->event_id,2);
                            //}
                        }

                        // source_repo_name, source_repo_caln, source_repo_page.

                        // *** Datetime new in database ***
                        // 1_NEW
                        // 2 DATE 04 AUG 2004
                        // 3 TIME 13:39:58
                        $this->buffer .= $this->process_datetime('new', $sourceDb->source_new_datetime, $sourceDb->source_new_user_id);
                        // *** Datetime changed in database ***
                        // 1_CHAN
                        // 2 DATE 04 AUG 2004
                        // 3 TIME 13:39:58
                        $this->buffer .= $this->process_datetime('changed', $sourceDb->source_changed_datetime, $sourceDb->source_changed_user_id);


                        // *** Write source data ***
                        $this->decode();
                        fwrite($fh, $this->buffer);

                        // *** Update processed lines ***
                        $record_nr++;
                        $perc = $this->update_bootstrap_bar($record_nr, $step, $devider, $perc);
                        //flush();

                        // *** Show source data on screen ***
                        //$this->buffer = str_replace("\n", "<br>", $this->buffer);
                        //echo $this->buffer;
                    }

                    // *** Repository data ***
                    $repo_qry = $this->dbh->query("SELECT * FROM humo_repositories WHERE repo_tree_id='" . $this->tree_id . "' ORDER BY repo_name, repo_place");
                    while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) {
                        $this->buffer = '0 @' . $repoDb->repo_gedcomnr . "@ REPO\r\n";

                        if ($repoDb->repo_date) {
                            $this->buffer .= '1 DATE ' . $this->process_date($repoDb->repo_date) . "\r\n";
                        }
                        if ($repoDb->repo_place) {
                            $this->buffer .= $this->process_place($repoDb->repo_place, 1);
                        }

                        if ($repoDb->repo_name) {
                            $this->buffer .= '1 NAME ' . $repoDb->repo_name . "\r\n";
                        }
                        if ($repoDb->repo_text) {
                            $this->buffer .= '1 NOTE ' . $this->process_text(2, $repoDb->repo_text);
                        }
                        if ($repoDb->repo_address) {
                            $this->buffer .= '1 ADDR ' . $this->process_text(2, $repoDb->repo_address);
                        }

                        if ($repoDb->repo_zip) {
                            $this->buffer .= '2 POST ' . $repoDb->repo_zip . "\r\n";
                        }
                        if ($repoDb->repo_phone) {
                            $this->buffer .= '1 PHON ' . $repoDb->repo_phone . "\r\n";
                        }
                        if ($repoDb->repo_mail) {
                            // *** GEDCOM 7 ***
                            $this->buffer .= '1 EMAIL ' . $repoDb->repo_mail . "\r\n";
                        }
                        if ($repoDb->repo_url) {
                            // *** GEDCOM 7 ***
                            $this->buffer .= '1 WWW ' . $repoDb->repo_url . "\r\n";
                        }

                        // *** Datetime new in database ***
                        // 1_NEW
                        // 2 DATE 04 AUG 2004
                        // 3 TIME 13:39:58
                        $this->buffer .= $this->process_datetime('new', $repoDb->repo_new_datetime, $repoDb->repo_new_user_id);
                        // *** Datetime changed in database ***
                        // 1_CHAN
                        // 2 DATE 04 AUG 2004
                        // 3 TIME 13:39:58
                        $this->buffer .= $this->process_datetime('changed', $repoDb->repo_changed_datetime, $repoDb->repo_changed_user_id);

                        // *** Write repository data ***
                        $this->decode();
                        fwrite($fh, $this->buffer);

                        // *** Update processed lines ***
                        $record_nr++;
                        $perc = $this->update_bootstrap_bar($record_nr, $step, $devider, $perc);
                        //flush();
                    }
                }

                // *** THIS PART ISN'T VALID GEDCOM 5.5.1!!!!!!!! ***
                // *** Only export shared addresses ***
                // *** Addresses ***
                // 0 @R155@ RESI
                // 1 ADDR Straat
                // 1 ZIP
                // 1 PLAC Plaats
                // 1 PHON
                $export_addresses = true;
                if (isset($_POST['gedcom_shared_addresses']) && $_POST['gedcom_shared_addresses'] == 'standard') {
                    $export_addresses = false;
                }
                if ($export_addresses) {
                    $address_qry = $this->dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "' AND address_shared='1'");
                    while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
                        // 0 @R1@ RESI *** Gedcomnumber ***
                        $this->buffer = '0 @' . $addressDb->address_gedcomnr . "@ RESI\r\n";

                        if ($addressDb->address_address) {
                            $this->buffer .= '1 ADDR ' . $addressDb->address_address . "\r\n";
                        }
                        if ($addressDb->address_zip) {
                            $this->buffer .= '1 ZIP ' . $addressDb->address_zip . "\r\n";
                        }
                        if ($addressDb->address_date) {
                            $this->buffer .= '1 DATE ' . $this->process_date($addressDb->address_date) . "\r\n";
                        }
                        if ($addressDb->address_place) {
                            $this->buffer .= '1 PLAC ' . $addressDb->address_place . "\r\n";
                        }
                        if ($addressDb->address_phone) {
                            $this->buffer .= '1 PHON ' . $addressDb->address_phone . "\r\n";
                        }
                        if ($this->gedcom_sources == 'yes') {
                            $this->sources_export('address', 'address_source', $addressDb->address_gedcomnr, 2);
                        }
                        if ($addressDb->address_text) {
                            $this->buffer .= '1 NOTE ' . $this->process_text(2, $addressDb->address_text);
                        }

                        // photo

                        // *** Write source data ***
                        $this->decode();
                        fwrite($fh, $this->buffer);
                        // *** Show source data on screen ***
                        //$this->buffer = str_replace("\r\n", "<br>", $this->buffer);
                        //echo $this->buffer;
                    }
                }

                // *** Notes ***
                // 0 @N1@ NOTE
                // 1 CONT Start of the note
                // 2 CONC add a bit more to the line
                // 2 CONT Another line of the note

                // This adds seperate note records for all the note refs in table texts captured in $this->noteids
                if ($gedcom_texts == 'yes') {
                    $this->buffer = '';
                    natsort($this->noteids);
                    foreach ($this->noteids as $note_text) {
                        $stmt = $this->dbh->prepare("SELECT * FROM humo_texts WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr");
                        $stmt->execute([
                            ':text_tree_id' => $this->tree_id,
                            ':text_gedcomnr' => substr($note_text, 1, -1)
                        ]);
                        while ($textDb = $stmt->fetch(PDO::FETCH_OBJ)) {
                            $this->buffer .= "0 " . $note_text . " NOTE\r\n";
                            $this->buffer .= '1 CONC ' . $this->process_text(1, $textDb->text_text);

                            // *** Datetime new in database ***
                            // 1_NEW
                            // 2 DATE 04 AUG 2004
                            // 3 TIME 13:39:58
                            $this->buffer .= $this->process_datetime('new', $textDb->text_new_datetime, $textDb->text_new_user_id);
                            // *** Datetime changed in database ***
                            // 1_CHAN
                            // 2 DATE 04 AUG 2004
                            // 3 TIME 13:39:58
                            $this->buffer .= $this->process_datetime('changed', $textDb->text_changed_datetime, $textDb->text_changed_user_id);
                        }
                    }

                    // *** Write note data ***
                    $this->decode();
                    fwrite($fh, $this->buffer);

                    // *** Update processed lines ***
                    $record_nr++;
                    $perc = $this->update_bootstrap_bar($record_nr, $step, $devider, $perc);
                    //flush();
                }
            }

            // *** Bootstrap bar ***
?>
            <script>
                var bar = document.querySelector(".progress-bar");
                bar.style.width = 100 + "%";
                bar.innerText = 100 + "%";
            </script>

        <?php
            fwrite($fh, '0 TRLR');
            fclose($fh);
        }
    }

    private function update_bootstrap_bar($record_nr, $step, $devider, $perc): int
    {
        // Calculate the percentage
        if ($record_nr % $step == 0) {
            if ($devider == 50) {
                $perc += 2;
            } elseif ($devider == 100) {
                $perc += 1;
            } elseif ($devider == 200) {
                $perc += 0.5;
            }

            // *** Bootstrap bar ***
        ?>
            <script>
                var bar = document.querySelector(".progress-bar");
                bar.style.width = <?= $perc; ?> + "%";
                bar.innerText = <?= $perc; ?> + "%";
            </script>
<?php

            // TODO These items don't work properly. Probably because of the for loops.
            // This is for the buffer achieve the minimum size in order to flush data
            //echo str_repeat(' ', 1024 * 64);
            //ob_flush();

            flush();
        }
        return $perc;
    }

    private function decode(): void
    {
        //$buffer = html_entity_decode($buffer, ENT_NOQUOTES, 'ISO-8859-15');
        //$buffer = html_entity_decode($buffer, ENT_QUOTES, 'ISO-8859-15');
        if (isset($_POST['gedcom_char_set']) && $_POST['gedcom_char_set'] == 'ANSI') {
            $this->buffer = iconv("UTF-8", "windows-1252", $this->buffer);
        }
    }

    private function process_date($text): string
    {
        if ($this->gedcom_version == '551') {
            //
        } else {
            if ($text) {
                // *** Remove extra 0 for GEDCOM 7 export ***
                $text = str_replace('01 ', '1 ', $text);
                $text = str_replace('02 ', '2 ', $text);
                $text = str_replace('03 ', '3 ', $text);
                $text = str_replace('04 ', '4 ', $text);
                $text = str_replace('05 ', '5 ', $text);
                $text = str_replace('06 ', '6 ', $text);
                $text = str_replace('07 ', '7 ', $text);
                $text = str_replace('08 ', '8 ', $text);
                $text = str_replace('09 ', '9 ', $text);
            }
        }
        return $text;
    }

    // Official GEDCOM 5.5.1: 255 characters total (including tags).
    // Character other programs: Aldfaer about 60 char., BK about 230.
    // ALDFAER:
    // 1 CONC Bla, bla text.
    // 1 CONT
    // 1 CONT Another text.
    // 1 CONC Bla bla text etc.
    // Don't process first part, add if processed (can be: 2 NOTE or 3 NOTE)
    private function process_text($level, $text, $extractnoteids = true): string
    {
        $text = str_replace("<br>", "", $text);
        $text = str_replace("\r", "", $text);

        // *** Export referenced texts ***
        if ($extractnoteids && substr($text, 0, 1) == '@') {
            $this->noteids[] = $text;
        }

        $regel = explode("\n", $text);
        // *** If text is too long split it, GEDCOM 5.5.1 specs: max. 255 characters including tag. ***
        $text = '';
        $text_processed = '';
        for ($j = 0; $j <= (count($regel) - 1); $j++) {
            $text = $regel[$j] . "\r\n";

            // *** CONC isn't allowed in GEDCOM 7.0 ***
            if ($this->gedcom_version == '551') {
                if (strlen($regel[$j]) > 150) {
                    $line_length = strlen($regel[$j]);
                    $words = explode(" ", $regel[$j]);
                    $new_line = '';
                    $new_line2 = '';
                    $characters = 0;
                    for ($x = 0; $x <= (count($words) - 1); $x++) {
                        if ($x > 0) {
                            $new_line .= ' ';
                            $new_line2 .= ' ';
                        }
                        $new_line .= $words[$x];
                        $new_line2 .= $words[$x];
                        $characters = (strlen($new_line2));
                        //if ($characters>145){
                        // *** Break line if there are >5 characters left AND there are >145 characters ***
                        if ($characters > 145 && $line_length - $characters > 5) {
                            $new_line .= "\r\n" . $level . " CONC";
                            $new_line2 = '';
                            $line_length = $line_length - $characters;
                        }
                    }
                    $text = $new_line . "\r\n";
                }
            }

            // *** First line is x NOTE, use CONT at other lines ***
            if ($j > 0) {
                if (rtrim($text) != '') {
                    $text = $level . ' CONT ' . $text;
                } else {
                    $text = "2 CONT\r\n";
                }
            }
            $text_processed .= $text;
        }
        return $text_processed;
    }

    private function process_place($place, $number): string
    {
        // 2 PLAC Cleveland, Ohio, USA
        // 3 MAP
        // 4 LATI N41.500347
        // 4 LONG W81.66687
        $text = $number . ' PLAC ' . $place . "\r\n";
        if (isset($_POST['gedcom_geocode']) && $_POST['gedcom_geocode'] == 'yes') {
            $geo_location_sql = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_location='" . addslashes($place) . "'";
            $geo_location_qry = $this->dbh->query($geo_location_sql);
            $geo_locationDb = $geo_location_qry->fetch(PDO::FETCH_OBJ);
            if ($geo_locationDb) {
                $text .= ($number + 1) . ' MAP' . "\r\n";

                $geocode = $geo_locationDb->location_lat;
                if (substr($geocode, 0, 1) == '-') {
                    $geocode = 'S' . substr($geocode, 1);
                } else {
                    $geocode = 'N' . $geocode;
                }
                $text .= ($number + 2) . ' LATI ' . $geocode . "\r\n";

                $geocode = $geo_locationDb->location_lng;
                if (substr($geocode, 0, 1) == '-') {
                    $geocode = 'W' . substr($geocode, 1);
                } else {
                    $geocode = 'E' . $geocode;
                }
                $text .= ($number + 2) . ' LONG ' . $geocode . "\r\n";
            }
        }
        return $text;
    }


    // *** jan. 2021 new function ***
    private function addresses_export($connect_kind, $connect_sub_kind, $connect_connect_id): void
    {
        // *** Addresses (shared addresses are no valid GEDCOM 5.5.1) ***
        // *** Living place ***
        // 1 RESI
        // 2 ADDR Ridderkerk
        // 1 RESI
        // 2 ADDR Slikkerveer

        $eventnr = 0;
        $connect_sql = $this->db_functions->get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id);
        foreach ($connect_sql as $connectDb) {
            $addressDb = $this->db_functions->get_address($connectDb->connect_item_id);
            // *** Next items are only exported if Address is shared ***

            $export_addresses = false;
            if ($addressDb->address_shared == '1') $export_addresses = true;
            if (isset($_POST['gedcom_shared_addresses']) && $_POST['gedcom_shared_addresses'] == 'standard') {
                $export_addresses = false;
            }
            if ($export_addresses) {
                // *** Shared address ***
                // 1 RESI @R210@
                // 2 DATE 1 JAN 2021
                // 2 ROLE ROL
                $this->buffer .= '1 RESI @' . $connectDb->connect_item_id . "@\r\n";
                if ($connectDb->connect_date) {
                    $this->buffer .= '2 DATE ' . $this->process_date($connectDb->connect_date) . "\r\n";
                }
                if ($connectDb->connect_role) {
                    $this->buffer .= '2 ROLE ' . $connectDb->connect_role . "\r\n";
                }

                // *** Extra text by address ***
                if ($connectDb->connect_text) {
                    // 2 DATA
                    // 3 TEXT text .....
                    // 4 CONT ..........
                    $this->buffer .= "2 DATA\r\n";
                    $this->buffer .= '3 TEXT ' . $this->process_text(4, $connectDb->connect_text);
                }

                // *** Source by address ***
                if ($this->gedcom_sources == 'yes') {
                    //if ($connect_kind=='person'){
                    //	//$this->buffer.='2 SOUR '.$this->process_text(3,$addressDb->address_source);
                    //	$this->sources_export('person','pers_address_source',$addressDb->address_id,2);
                    //}
                    //else{
                    //	$this->sources_export('family','fam_address_source',$addressDb->address_id,2);
                    //}

                    if ($connect_kind == 'person') {
                        $this->sources_export('person', 'pers_address_connect_source', $connectDb->connect_id, 2);
                    } else {
                        $this->sources_export('family', 'fam_address_connect_source', $connectDb->connect_id, 2);
                    }
                }
            } else {
                // *** Living place ***
                // 1 RESI
                // 2 ADDR Ridderkerk
                // 1 RESI
                // 2 ADDR Slikkerveer
                $this->buffer .= "1 RESI\r\n";

                // *** Export HuMo-genealogy address GEDCOM numbers ***
                $this->buffer .= '2 RIN ' . substr($connectDb->connect_item_id, 1) . "\r\n";

                $this->buffer .= '2 ADDR';
                if ($addressDb->address_address) {
                    $this->buffer .= ' ' . $addressDb->address_address;
                }
                $this->buffer .= "\r\n";
                if ($addressDb->address_place) {
                    $this->buffer .= '3 CITY ' . $addressDb->address_place . "\r\n";
                }
                if ($addressDb->address_zip) {
                    $this->buffer .= '3 POST ' . $addressDb->address_zip . "\r\n";
                }
                if ($addressDb->address_phone) {
                    $this->buffer .= '2 PHON ' . $addressDb->address_phone . "\r\n";
                }
                //if ($addressDb->address_date){
                //  $this->buffer.='2 DATE '.$this->process_date($addressDb->address_date)."\r\n";
                //}
                if ($connectDb->connect_date) {
                    $this->buffer .= '2 DATE ' . $this->process_date($connectDb->connect_date) . "\r\n";
                }
                if ($addressDb->address_text) {
                    $this->buffer .= '2 NOTE ' . $this->process_text(3, $addressDb->address_text);
                }

                // *** Source by address ***
                if ($this->gedcom_sources == 'yes') {
                    //if ($connect_kind=='person'){
                    //	//$this->buffer.='2 SOUR '.$this->process_text(3,$addressDb->address_source);
                    //	$this->sources_export('person','pers_address_source',$addressDb->address_gedcomnr,2);
                    //}
                    //else{
                    //	$this->sources_export('family','fam_address_source',$addressDb->address_gedcomnr,2);
                    //}

                    if ($connect_kind == 'person') {
                        $this->sources_export('person', 'pers_address_connect_source', $connectDb->connect_id, 2);
                    } else {
                        $this->sources_export('family', 'fam_address_connect_source', $connectDb->connect_id, 2);
                    }

                    $this->sources_export('address', 'address_source', $addressDb->address_gedcomnr, 2);
                }
            }
        }
    }

    // *** Function to export all kind of sources including role, pages etc. ***
    private function sources_export($connect_kind, $connect_sub_kind, $connect_connect_id, $start_number): void
    {
        // *** Search for all connected sources ***
        $connect_qry = "SELECT * FROM humo_connections LEFT JOIN humo_sources ON source_gedcomnr=connect_source_id
            WHERE connect_tree_id='" . $this->tree_id . "' AND source_tree_id='" . $this->tree_id . "'
            AND connect_kind='" . $connect_kind . "'
            AND connect_sub_kind='" . $connect_sub_kind . "'
            AND connect_connect_id='" . $connect_connect_id . "'
            ORDER BY connect_order";
        $connect_sql = $this->dbh->query($connect_qry);
        while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
            //$connect_sql = $this->db_functions->get_connections_connect_id('person','person_address',$person->pers_gedcomnumber);
            //foreach ($connect_sql as $connectDb){

            // *** Source contains title, can be connected to multiple items ***
            // 0 @S2@ SOUR
            // 1 ROLE ROL
            // 1 PAGE page
            $this->buffer .= $start_number . ' SOUR @' . $connectDb->connect_source_id . "@\r\n";
            if ($connectDb->connect_role) {
                $this->buffer .= ($start_number + 1) . ' ROLE ' . $connectDb->connect_role . "\r\n";
            }
            if ($connectDb->connect_page) {
                $this->buffer .= ($start_number + 1) . ' PAGE ' . $connectDb->connect_page . "\r\n";
            }
            if ($connectDb->connect_quality || $connectDb->connect_quality == '0') {
                $this->buffer .= ($start_number + 1) . ' QUAY ' . $connectDb->connect_quality . "\r\n";
            }

            // *** Source citation (extra text by source) ***
            // 3 DATA
            // 4 DATE ......
            // 4 PLAC ....... (not in GEDOM specifications).
            // 4 TEXT text .....
            // 5 CONT ..........
            if ($connectDb->connect_text || $connectDb->connect_date || $connectDb->connect_place) {
                $this->buffer .= ($start_number + 1) . " DATA\r\n";

                if ($connectDb->connect_date) {
                    $this->buffer .= ($start_number + 2) . ' DATE ' . $this->process_date($connectDb->connect_date) . "\r\n";
                }

                if ($connectDb->connect_place) {
                    $this->buffer .= ($start_number + 2) . ' PLAC ' . $connectDb->connect_place . "\r\n";
                }

                if ($connectDb->connect_text) {
                    $this->buffer .= ($start_number + 2) . ' TEXT ' . $this->process_text($start_number + 3, $connectDb->connect_text);
                }
            }
        }
    }

    private function descendants($family_id, $main_person, $generation_number, $max_generations): void
    {
        $family_nr = 1; //*** Process multiple families ***
        if ($max_generations < $generation_number) {
            return;
        }
        $generation_number++;
        // *** Count marriages of man ***
        // *** If needed show woman as main_person ***
        if ($family_id == '') {
            // single person
            $this->persids[] = $main_person;
            return;
        }

        $family = $this->dbh->query("SELECT fam_man, fam_woman FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $family_id . "'");
        try {
            $familyDb = $family->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo __('No valid family number.');
        }

        $parent1 = '';
        $swap_parent1_parent2 = false;

        // *** Standard main_person is the man ***
        if ($familyDb->fam_man) {
            $parent1 = $familyDb->fam_man;
        }
        // *** If woman is selected, woman will be main_person ***
        if ($familyDb->fam_woman == $main_person) {
            $parent1 = $familyDb->fam_woman;
            $swap_parent1_parent2 = true;
        }

        // *** Check family with parent1: N.N. ***
        if ($parent1) {
            // *** Save man's families in array ***
            //check query
            $personDb = $this->db_functions->get_person($parent1);

            $marriage_array = explode(";", $personDb->pers_fams);
            $nr_families = substr_count($personDb->pers_fams, ";");
        } else {
            $marriage_array[0] = $family_id;
            $nr_families = "0";
        }

        // *** Loop multiple marriages of main_person ***
        for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
            $id = $marriage_array[$parent1_marr];
            $familyDb = $this->db_functions->get_family($id);

            /**
             * Parent1 (normally the father)
             */
            if ($familyDb->fam_kind != 'PRO-GEN') {
                //onecht kind, vrouw zonder man
                if ($family_nr == 1) {
                    // *** Show data of man ***

                    if ($swap_parent1_parent2 == true) {
                        // store I and Fs
                        $this->persids[] = $familyDb->fam_woman;
                        $families = explode(';', $personDb->pers_fams);
                        foreach ($families as $value) {
                            $this->famsids[] = $value;
                        }
                    } else {
                        // store I and Fs
                        $this->persids[] = $familyDb->fam_man;
                        $families = explode(';', $personDb->pers_fams);
                        foreach ($families as $value) {
                            $this->famsids[] = $value;
                        }
                    }
                }
                $family_nr++;
            }

            /**
             * Parent2 (normally the mother)
             */
            if (isset($_POST['desc_spouses'])) {
                if ($swap_parent1_parent2 == true) {
                    $this->persids[] = $familyDb->fam_man;
                    $desc_sp = $familyDb->fam_man;
                } else {
                    $this->persids[] = $familyDb->fam_woman;
                    $desc_sp = $familyDb->fam_woman;
                }
            }
            if (isset($_POST['desc_sp_parents'])) {
                // if set, add parents of spouse
                $spqry = $this->dbh->query("SELECT pers_famc FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber = '" . $desc_sp . "'");
                $spqryDb = $spqry->fetch(PDO::FETCH_OBJ);
                if (isset($spqryDb->pers_famc) && $spqryDb->pers_famc) {
                    $famqryDb = $this->db_functions->get_family($spqryDb->pers_famc);
                    if ($famqryDb->fam_man) {
                        $this->persids[] = $famqryDb->fam_man;
                    }
                    if ($famqryDb->fam_woman) {
                        $this->persids[] = $famqryDb->fam_woman;
                    }
                    $this->famsids[] = $spqryDb->pers_famc;
                }
            }

            /**
             * Children
             */
            if ($familyDb->fam_children) {
                $child_array = explode(";", $familyDb->fam_children);
                foreach ($child_array as $i => $value) {
                    $child = $this->dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $child_array[$i] . "'");
                    $childDb = $child->fetch(PDO::FETCH_OBJ);
                    //$childDb = $this->db_functions->get_person($child_array[$i]);
                    if ($child->rowCount() > 0) {
                        // *** Build descendant_report ***
                        if ($childDb->pers_fams) {
                            // *** 1st family of child ***
                            $child_family = explode(";", $childDb->pers_fams);
                            $child1stfam = $child_family[0];
                            $this->descendants($child1stfam, $childDb->pers_gedcomnumber, $generation_number, $max_generations);  // recursive
                        } else {
                            // Child without own family
                            if ($max_generations >= $generation_number) {
                                $childgn = $generation_number + 1;
                                $this->persids[] = $childDb->pers_gedcomnumber;
                            }
                        }
                    }
                }
            }
        } // Show  multiple marriages
    }

    private function ancestors($person_id, $max_generations): void
    {
        $ancestor_array2[] = $person_id;
        $ancestor_number2[] = 1;
        $marriage_gedcomnumber2[] = 0;
        $generation = 1;
        $listed_array = array();

        // *** Loop for ancestor report ***
        while (isset($ancestor_array2[0])) {
            if ($max_generations <= $generation) {
                return;
            }

            unset($ancestor_array);
            $ancestor_array = $ancestor_array2;
            unset($ancestor_array2);

            unset($ancestor_number);
            $ancestor_number = $ancestor_number2;
            unset($ancestor_number2);

            unset($marriage_gedcomnumber);
            $marriage_gedcomnumber = $marriage_gedcomnumber2;
            unset($marriage_gedcomnumber2);

            // *** Loop per generation ***
            for ($i = 0; $i < count($ancestor_array); $i++) {
                //foreach ($ancestor_array as $i => $value){
                $listednr = '';
                foreach ($listed_array as $key => $value) {
                    if ($value == $ancestor_array[$i]) {
                        $listednr = $key;
                    }
                }
                if ($listednr == '') {
                    //if not listed yet, add person to array
                    $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
                }
                if ($ancestor_array[$i] != '0') {
                    $person_manDb = $this->db_functions->get_person($ancestor_array[$i]);
                    if (strtolower($person_manDb->pers_sexe) == 'm' && $ancestor_number[$i] > 1) {
                        $familyDb = $this->db_functions->get_family($marriage_gedcomnumber[$i]);
                        $person_womanDb = $this->db_functions->get_person($familyDb->fam_woman);
                    }
                    if ($listednr == '') {
                        //take I and F
                        if ($person_manDb->pers_gedcomnumber == $person_id) {
                            // for the base person we add spouse manually
                            $this->persids[] = $person_manDb->pers_gedcomnumber;
                            if ($person_manDb->pers_fams) {
                                $families = explode(';', $person_manDb->pers_fams);
                                if ($person_manDb->pers_sexe == 'M') {
                                    $spouse = "fam_woman";
                                } else {
                                    $spouse = "fam_man";
                                }
                                foreach ($families as $value) {
                                    $sp_main = $this->dbh->query("SELECT " . $spouse . " FROM humo_families
                                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber = '" . $value . "'");
                                    $sp_mainDb = $sp_main->fetch(PDO::FETCH_OBJ);
                                    if (isset($_POST['ances_spouses'])) {
                                        // we also include spouses of base person
                                        $this->persids[] = $sp_mainDb->$spouse;
                                    }
                                    $this->famsids[] = $value;
                                }
                            }
                        } else {
                            // any other person
                            $this->persids[] = $person_manDb->pers_gedcomnumber;
                        }
                        if ($person_manDb->pers_famc && $generation + 1 < $max_generations) {
                            // if this is the last generation (max gen) we don't want the famc!
                            $this->famsids[] = $person_manDb->pers_famc;
                            if (isset($_POST['ances_sibbl'])) {
                                // also get I numbers of sibblings
                                $sibbqryDb = $this->db_functions->get_family($person_manDb->pers_famc);
                                $sibs = explode(';', $sibbqryDb->fam_children);
                                foreach ($sibs as $value) {
                                    if ($value != $person_manDb->pers_gedcomnumber) {
                                        $this->persids[] = $value;
                                    }
                                }
                            }
                        }
                    } else {
                        // person was already listed
                        // do nothing
                    }

                    // == Check for parents
                    if ($person_manDb->pers_famc  && $listednr == '') {
                        $family_parentsDb = $this->db_functions->get_family($person_manDb->pers_famc);
                        if ($family_parentsDb->fam_man) {
                            $ancestor_array2[] = $family_parentsDb->fam_man;
                            $ancestor_number2[] = (2 * $ancestor_number[$i]);
                            $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                        }
                        if ($family_parentsDb->fam_woman) {
                            $ancestor_array2[] = $family_parentsDb->fam_woman;
                            $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                            $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                        } else {
                            // *** N.N. name ***
                            $ancestor_array2[] = '0';
                            $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                            $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                        }
                    }
                } else {
                    // *** Show N.N. person ***
                    $person_manDb = $this->db_functions->get_person($ancestor_array[$i]);
                    // take I (and F?)
                }
            }    // loop per generation
            $generation++;
        }    // loop ancestors function
    }

    private function export_witnesses($event_connect_kind, $event_connect_id, $event_kind): string
    {
        $witnesses = '';
        $witness_qry = $this->db_functions->get_events_connect($event_connect_kind, $event_connect_id, $event_kind);
        foreach ($witness_qry as $witnessDb) {
            if ($this->gedcom_version == '551') {
                // *** Baptise witness: 2 _WITN @I1@ or: 2 _WITN firstname lastname ***
                if ($witnessDb->event_connect_id2) {
                    $witnesses .= '2 WITN @' . $witnessDb->event_connect_id2 . "@\r\n";
                } else {
                    $witnesses .= '2 WITN ' . $witnessDb->event_event . "\r\n";
                }
            } else {
                // *** GEDCOM 7 ***
                // 1 BURI
                // 2 ASSO @I9@
                // 3 ROLE OTHER
                // 4 PHRASE funeral leader
                if ($witnessDb->event_connect_id2) {
                    // *** Connected person ***
                    $witnesses .= '2 ASSO @' . $witnessDb->event_connect_id2 . "@\r\n";
                } else {
                    // *** No person connected, text is used for name of person ***
                    // 2 ASSO @VOID@
                    // 3 PHRASE Mr Stockdale
                    // 3 ROLE OTHER
                    // 4 PHRASE Teacher -> event_event_extra?
                    $witnesses .= "2 ASSO @VOID@\r\n";
                    $witnesses .= '3 PHRASE ' . $witnessDb->event_event . "\r\n";
                }

                $witnesses .= '3 ROLE ' . $witnessDb->event_gedcom . "\r\n";

                // *** 4 PHRASE for role OTHER ***
                if ($witnessDb->event_gedcom == 'OTHER') {
                    $witnesses .= '4 PHRASE ' . $witnessDb->event_event_extra . "\r\n";
                }
            }
        }
        return $witnesses;
    }

    // *** GEDCOM 5.5.1: 1 _NEW. GEDCOM 7.x: 1 CREA ***
    private function process_datetime($new_changed, $datetime, $user_id): string
    {
        $buffer = '';
        if ($datetime && $datetime != '1970-01-01 00:00:01') {
            if ($new_changed == 'new' && $this->gedcom_version == '551') {
                $buffer .= "1 _NEW\r\n";
            } elseif ($new_changed == 'new') {
                $buffer .= "1 CREA\r\n";
            } else {
                $buffer .= "1 CHAN\r\n";
            }

            $export_date = strtoupper(date('d M Y', (strtotime($datetime))));
            $buffer .= "2 DATE " . $this->process_date($export_date) . "\r\n";

            $buffer .= "3 TIME " . date('H:i:s', (strtotime($datetime))) . "\r\n";

            if ($user_id) {
                $buffer .= "2 _USR " . $user_id . "\r\n";
            }
        }
        return $buffer;
    }
}
