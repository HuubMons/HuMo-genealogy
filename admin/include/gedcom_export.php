<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// Needed to process witnesses etc.
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}



if (isset($tree_id) && isset($_POST['submit_button'])) {
    if ($export["part_tree"] == 'part' && isset($_POST['kind_tree']) && $_POST['kind_tree'] == "descendant") {
        // map descendants
        $desc_fams = '';
        $desc_pers = $_POST['person'];
        $max_gens = $_POST['nr_generations'];

        $fam_search = $dbh->query("SELECT pers_fams, pers_famc
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber ='" . $desc_pers . "'");
        $fam_searchDb = $fam_search->fetch(PDO::FETCH_OBJ);
        if ($fam_searchDb->pers_fams != '') {
            $desc_fams = $fam_searchDb->pers_fams;
        } else {
            $desc_fams = $fam_searchDb->pers_famc;
        }
        $generation_number = 0;

        // *** Only use first marriage of selected person to avoid error. Other marriages will be processed in the function! ***
        $pers_fams = explode(";", $desc_fams);
        descendants($pers_fams[0], $desc_pers, $generation_number, $max_gens);
    }
    if ($export["part_tree"] == 'part' && isset($_POST['kind_tree']) && $_POST['kind_tree'] == "ancestor") {
        // map ancestors
        $anc_pers = $_POST['person'];
        $max_gens = $_POST['nr_generations'] + 2;
        ancestors($anc_pers, $max_gens);
    }

    $gedcom_version = '551';
    if (isset($_POST['gedcom_version'])) {
        $gedcom_version = $_POST['gedcom_version'];
    }

    $gedcom_char_set = '';
    if (isset($_POST['gedcom_char_set'])) {
        $gedcom_char_set = $_POST['gedcom_char_set'];
    }

    // PMB our minimal option
    $gedcom_minimal = '';
    if (isset($_POST['gedcom_minimal'])) {
        $gedcom_minimal = $_POST['gedcom_minimal'];
    }

    $gedcom_texts = '';
    if (isset($_POST['gedcom_texts'])) {
        $gedcom_texts = $_POST['gedcom_texts'];
    }

    $gedcom_sources = '';
    if (isset($_POST['gedcom_sources'])) {
        $gedcom_sources = $_POST['gedcom_sources'];
    }
    $fh = fopen($export['path'] . $export['file_name'], 'w') or die("<b>ERROR: no permission to open a new file! Please check permissions of admin/gedcom_files folder!</b>");

    // *** GEDCOM header ***
    $buffer = '';

    if ($gedcom_version == '551') {
        // *** GEDCOM 5.5.1 ***
        //if ($gedcom_char_set=='UTF-8') $buffer.= "\xEF\xBB\xBF"; // *** Add BOM header to UTF-8 file ***
        $buffer .= "0 HEAD\r\n";
        $buffer .= "1 SOUR HuMo-genealogy\r\n";
        $buffer .= "2 VERS " . $humo_option["version"] . "\r\n";
        $buffer .= "2 NAME HuMo-genealogy\r\n";
        $buffer .= "2 CORP HuMo-genealogy software\r\n";
        $buffer .= "3 ADDR https://humo-gen.com\r\n";
        $buffer .= "1 SUBM @S1@\r\n";
        $buffer .= "1 GEDC\r\n";
        $buffer .= "2 VERS 5.5.1\r\n";
        $buffer .= "2 FORM Lineage-Linked\r\n";

        if ($gedcom_char_set == 'UTF-8') {
            $buffer .= "1 CHAR UTF-8\r\n";
        } elseif ($gedcom_char_set == 'ANSI') {
            $buffer .= "1 CHAR ANSI\r\n";
        } else {
            $buffer .= "1 CHAR ASCII\r\n";
        }
    } else {
        // *** GEDCOM 7.0 ***
        /*
        0 HEAD
        1 GEDC
        2 VERS 7.0
        1 SCHMA
        2 TAG _SKYPEID http://xmlns.com/foaf/0.1/skypeID
        2 TAG _JABBERID http://xmlns.com/foaf/0.1/jabberID
        1 SOUR https://gedcom.io/
        2 VERS 0.3
        2 NAME GEDCOM Steering Committee
        2 CORP FamilySearch
        */
        $buffer .= "0 HEAD\r\n";
        $buffer .= "1 GEDC\r\n";
        $buffer .= "2 VERS 7.0\r\n";
        $buffer .= "1 SOUR https://humo-gen.com\r\n";
        $buffer .= "2 VERS " . $humo_option["version"] . "\r\n";
        $buffer .= "2 NAME HuMo-genealogy\r\n";
        $buffer .= "2 CORP HuMo-genealogy software\r\n";
    }

    // 0 @S1@ SUBM
    // 1 NAME Huub Mons
    // 1 ADDR address
    $buffer .= "0 @S1@ SUBM\r\n";
    if ($export['submit_name']) {
        $buffer .= "1 NAME " . $export['submit_name'] . "\r\n";
    } else {
        $buffer .= "1 NAME Unknown\r\n";
    }

    if ($export['submit_address'] != '') {
        $buffer .= "1 ADDR " . $export['submit_address'] . "\r\n";
        if ($export['submit_country'] != '') $buffer .= "2 CTRY " . $export['submit_country'] . "\r\n";
    }

    if ($export['submit_mail'] != '') {
        $buffer .= "1 EMAIL " . $export['submit_mail'] . "\r\n";
    }

    fwrite($fh, $buffer);
    //$buffer = str_replace("\n", "<br>", $buffer);
    //echo '<p>'.$buffer;

    /* EXAMPLE:
    0 @I1181@ INDI
    1 RIN 1181
    1 REFN Eigencode
    1 NAME Voornaam/Achternaam/
    1 SEX M
    1 BIRT
    2 DATE 21 FEB 1960
    2 PLAC 1e woonplaats
    1 RESI
    2 ADDR 2e woonplaats
    1 RESI
    2 ADDR 3e woonplaats
    1 RESI
    2 ADDR 4e woonplaats
    1 OCCU 1e beroep
    1 OCCU 2e beroep
    1 EVEN
    2 TYPE living
    1 _COLOR 0
    1 NOTE @N51@
    1 FAMS @F10@
    1 FAMC @F8@
    1 _NEW
    2 TYPE 2
    2 DATE 8 JAN 2005
    3 TIME 20:31:24
    */

    // *** Count records in all tables for Bootstrap progress bar ***
    $total = $dbh->query("SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'");
    $total = $total->fetch();
    $nr_records = $total[0];

    $count_fam = $dbh->query("SELECT COUNT(fam_id) FROM humo_families WHERE fam_tree_id='" . $tree_id . "'");
    $count_famDb = $count_fam->fetch();
    $nr_records += $count_famDb[0];

    $total = $dbh->query("SELECT COUNT(*) FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
    $total = $total->fetch();
    $nr_records += $total[0];

    if ($gedcom_texts == 'yes') {
        $total = $dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='" . $tree_id . "'");
        $total = $total->fetch();
        $nr_records += $total[0];
    }

    $total = $dbh->query("SELECT COUNT(*) FROM humo_addresses WHERE address_tree_id='" . $tree_id . "'");
    $total = $total->fetch();
    $nr_records += $total[0];

    $total = $dbh->query("SELECT COUNT(*) FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "'");
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

    function update_bootstrap_bar($record_nr, $step, $devider, $perc)
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

    //echo $nr_records . '!' . $step . '!' . $devider . '!' . $perc;
    //$record_nr++;
    //$perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);


    // *** To reduce use of memory, first read pers_id only ***
    $persons_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'";
    $persons_result = $dbh->query($persons_qry);
    while ($persons = $persons_result->fetch(PDO::FETCH_OBJ)) {

        // *** Now read all person items ***
        $person = $db_functions->get_person_with_id($persons->pers_id);

        if ($export["part_tree"] == 'part' && !in_array($person->pers_gedcomnumber, $persids)) {
            continue;
        }

        // 0 @I1181@ INDI *** Gedcomnumber ***
        $buffer = '0 @' . $person->pers_gedcomnumber . "@ INDI\r\n";

        if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') echo $person->pers_gedcomnumber . ' ';

        // 1 RIN 1181
        // Not really necessary, so disabled this line...
        //$buffer.='1 RIN '.substr($person->pers_gedcomnumber,1)."\r\n";

        // 1 REFN Code *** Own code ***
        if ($person->pers_own_code) $buffer .= '1 REFN ' . $person->pers_own_code . "\r\n";

        // *** Name, add a space after first name if first name is present ***
        // 1 NAME Firstname /Lastname/
        $buffer .= '1 NAME ' . $person->pers_firstname;
        if ($person->pers_firstname) {
            // add a space after first name if first name is present
            $buffer .= ' ';
        }
        $buffer .= '/' . str_replace("_", " ", $person->pers_prefix);
        $buffer .= $person->pers_lastname . "/\r\n";

        // *** december 2021: pers_callname no longer in use ***
        //if ($person->pers_callname) $buffer.='2 NICK '.$person->pers_callname."\r\n";

        // Prefix is exported by name!
        //if ($person->pers_prefix) $buffer.='2 SPFX '.$person->pers_prefix."\r\n";

        // PMB if 'minimal' option selected don't export this
        if ($_POST['export_type'] == 'normal') {

            // *** Text and source by name ***
            if ($gedcom_sources == 'yes') {
                sources_export('person', 'pers_name_source', $person->pers_gedcomnumber, 2);
            }

            if ($gedcom_texts == 'yes' && $person->pers_name_text) {
                $buffer .= '2 NOTE ' . process_text(3, $person->pers_name_text);
            }

            // *** Export all name items, like 2 _AKAN etc. ***
            $nameqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='" . $person->pers_gedcomnumber . "'
                AND event_kind='name' ORDER BY event_order");
            while ($nameDb = $nameqry->fetch(PDO::FETCH_OBJ)) {
                $eventgedcom = $nameDb->event_gedcom;
                // *** 2 _RUFNAME is only used in BK, HuMo-genealogy uses 2 _RUFN ***
                //if($nameDb->event_gedcom == "_RUFN") $eventgedcom = '_RUFNAME';
                $buffer .= '2 ' . $eventgedcom . ' ' . $nameDb->event_event . "\r\n";
                if ($nameDb->event_date) $buffer .= '3 DATE ' . process_date($gedcom_version, $nameDb->event_date) . "\r\n";
                if ($gedcom_sources == 'yes')
                    sources_export('person', 'pers_event_source', $nameDb->event_id, 3);
                if ($gedcom_texts == 'yes' && $nameDb->event_text) {
                    $buffer .= '3 NOTE ' . process_text(4, $nameDb->event_text);
                }
            }
        }

        if ($person->pers_patronym) {
            $buffer .= '1 _PATR ' . $person->pers_patronym . "\r\n";
        }

        // *** Sex ***
        $buffer .= '1 SEX ' . $person->pers_sexe . "\r\n";

        // *** Birth data ***
        // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
        if (
            $person->pers_birth_date || $person->pers_birth_place || $person->pers_birth_text
            || (isset($person->pers_stillborn) && $person->pers_stillborn == 'y')
        ) {
            $buffer .= "1 BIRT\r\n";
            if ($person->pers_birth_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $person->pers_birth_date) . "\r\n";
                if (isset($person->pers_birth_date_hebnight) && $person->pers_birth_date_hebnight == 'y') {
                    $buffer .= '2 _HNIT y' . "\r\n";
                }
            }
            if ($person->pers_birth_place) {
                $buffer .= process_place($person->pers_birth_place, 2);
            }
            if ($person->pers_birth_time) {
                $buffer .= '2 TIME ' . $person->pers_birth_time . "\r\n";
            }
            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {
                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_birth_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_birth_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $person->pers_birth_text);
                }

                if (isset($person->pers_stillborn) && $person->pers_stillborn == 'y') {
                    $buffer .= '2 TYPE stillborn' . "\r\n";
                }

                // *** New sept. 2023 ***
                // *** Remark: only exported if there is another birth item ***
                // *** Oct 2024: for GEDCOM 7 changed to seperate event ***
                //export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                if ($gedcom_version == '551') {
                    $buffer .= export_witnesses($gedcom_version, 'birth_declaration', $person->pers_gedcomnumber, 'ASSO');
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
        if ($gedcom_version != '551') {
            $birth_registrationqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='$person->pers_gedcomnumber' AND event_kind='birth_declaration'");
            $birth_declarationDb = $birth_registrationqry->fetch(PDO::FETCH_OBJ);
            $birth_decl_witnesses = export_witnesses($gedcom_version, 'birth_declaration', $person->pers_gedcomnumber, 'ASSO');

            if ($birth_declarationDb || $birth_decl_witnesses) {
                $buffer .= "1 EVEN\r\n";
                $buffer .= "2 TYPE birth registration\r\n";

                if ($birth_declarationDb->event_date) {
                    $buffer .= '2 DATE ' . $birth_declarationDb->event_date . "\r\n";
                }
                if ($birth_declarationDb->event_place) {
                    $buffer .= '2 PLAC ' . $birth_declarationDb->event_place . "\r\n";
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'birth_decl_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $birth_declarationDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $birth_declarationDb->event_text);
                }

                $buffer .= $birth_decl_witnesses;
            }
        }

        // *** Christened data ***
        // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
        if ($person->pers_bapt_date || $person->pers_bapt_place || $person->pers_bapt_text || $person->pers_religion) {
            $buffer .= "1 CHR\r\n";
            if ($person->pers_bapt_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $person->pers_bapt_date) . "\r\n";
            }
            if ($person->pers_bapt_place) {
                $buffer .= process_place($person->pers_bapt_place, 2);
            }
            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {

                // *** Person religion. This is 1 CHR -> 2 RELI! 1 RELI is exported as event (after profession) ***
                if ($person->pers_religion) $buffer .= '2 RELI ' . $person->pers_religion . "\r\n";

                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_bapt_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_bapt_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $person->pers_bapt_text);
                }

                // *** Remark: only exported if there is another baptism item ***
                // *** $event_tree_id, $event_connect_kind,$event_connect_id, $event_kind ***
                // export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                //$buffer .= export_witnesses($gedcom_version, 'person', $person->pers_gedcomnumber, 'baptism_witness');
                $buffer .= export_witnesses($gedcom_version, 'CHR', $person->pers_gedcomnumber, 'ASSO');
            }
        }

        // *** Death data ***
        // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
        if ($person->pers_death_date || $person->pers_death_place || $person->pers_death_text || $person->pers_death_cause) {
            $buffer .= "1 DEAT\r\n";
            if ($person->pers_death_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $person->pers_death_date) . "\r\n";
                if (isset($person->pers_death_date_hebnight) && $person->pers_death_date_hebnight == 'y') {
                    $buffer .= '2 _HNIT y' . "\r\n";
                }
            }
            if ($person->pers_death_place) $buffer .= process_place($person->pers_death_place, 2);
            if ($person->pers_death_time) $buffer .= '2 TIME ' . $person->pers_death_time . "\r\n";

            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {

                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_death_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_death_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $person->pers_death_text);
                }
                if ($person->pers_death_cause) {
                    $buffer .= '2 CAUS ' . $person->pers_death_cause . "\r\n";
                }
                if ($person->pers_death_age) {
                    $buffer .= '2 AGE ' . $person->pers_death_age . "\r\n";
                }

                // *** Remark: only exported if there is another baptism item ***
                // *** Oct 2024: for GEDCOM 7 changed to seperate event ***
                // export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                if ($gedcom_version == '551') {
                    $buffer .= export_witnesses($gedcom_version, 'death_declaration', $person->pers_gedcomnumber, 'ASSO');
                }
            }
        }

        //  *** NEW oct. 2024: seperate event for death registration ***
        if ($gedcom_version != '551') {
            $death_registrationqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='$person->pers_gedcomnumber' AND event_kind='death_declaration'");
            $death_declarationDb = $death_registrationqry->fetch(PDO::FETCH_OBJ);
            $death_decl_witnesses = export_witnesses($gedcom_version, 'death_declaration', $person->pers_gedcomnumber, 'ASSO');

            if ($death_declarationDb || $death_decl_witnesses) {
                $buffer .= "1 EVEN\r\n";
                $buffer .= "2 TYPE death registration\r\n";

                if ($death_declarationDb->event_date) {
                    $buffer .= '2 DATE ' . $death_declarationDb->event_date . "\r\n";
                }
                if ($death_declarationDb->event_place) {
                    $buffer .= '2 PLAC ' . $death_declarationDb->event_place . "\r\n";
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'death_decl_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $death_declarationDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $death_declarationDb->event_text);
                }

                $buffer .= $death_decl_witnesses;
            }
        }

        // *** Buried data ***
        // TODO: if there are only witnesses, the witnesses are missing in export. But normally there should be a date or place?
        if ($person->pers_buried_date || $person->pers_buried_place || $person->pers_buried_text || $person->pers_cremation) {
            $buffer .= "1 BURI\r\n";
            if ($person->pers_buried_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $person->pers_buried_date) . "\r\n";
                if (isset($person->pers_buried_date_hebnight) && $person->pers_buried_date_hebnight == 'y') {
                    $buffer .= '2 _HNIT y' . "\r\n";
                }
            }
            if ($person->pers_buried_place) $buffer .= process_place($person->pers_buried_place, 2);

            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {

                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_buried_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_buried_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $person->pers_buried_text);
                }
                if ($person->pers_cremation == '1') {
                    $buffer .= '2 TYPE cremation' . "\r\n";
                }
                if ($person->pers_cremation == 'R') {
                    $buffer .= '2 TYPE resomated' . "\r\n";
                }
                if ($person->pers_cremation == 'S') {
                    $buffer .= '2 TYPE sailor\'s grave' . "\r\n";
                }
                if ($person->pers_cremation == 'D') {
                    $buffer .= '2 TYPE donated to science' . "\r\n";
                }

                // *** Remark: only exported if there is another baptism item ***
                // *** $event_tree_id, $event_connect_kind,$event_connect_id, $event_kind ***
                // export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                //$buffer .= export_witnesses($gedcom_version, 'person', $person->pers_gedcomnumber, 'burial_witness');
                $buffer .= export_witnesses($gedcom_version, 'BURI', $person->pers_gedcomnumber, 'ASSO');
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
            addresses_export('person', 'person_address', $person->pers_gedcomnumber);

            // *** Occupation ***
            $professionqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='$person->pers_gedcomnumber'
                AND event_kind='profession' ORDER BY event_order");
            while ($professionDb = $professionqry->fetch(PDO::FETCH_OBJ)) {
                $buffer .= '1 OCCU ' . $professionDb->event_event . "\r\n";

                if ($professionDb->event_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $professionDb->event_date) . "\r\n";
                }
                if ($professionDb->event_place) {
                    $buffer .= '2 PLAC ' . $professionDb->event_place . "\r\n";
                }
                if ($gedcom_texts == 'yes' && $professionDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $professionDb->event_text);
                }

                // *** Source by occupation ***
                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_event_source', $professionDb->event_id, 2);
                }
            }

            // *** Religion. REMARK: this is religion event 1 RELI. Baptise religion is saved as 1 CHR -> 2 RELI. ***
            $professionqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='$person->pers_gedcomnumber'
                AND event_kind='religion' ORDER BY event_order");
            while ($professionDb = $professionqry->fetch(PDO::FETCH_OBJ)) {
                $buffer .= '1 RELI ' . $professionDb->event_event . "\r\n";

                if ($professionDb->event_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $professionDb->event_date) . "\r\n";
                }
                if ($professionDb->event_place) {
                    $buffer .= '2 PLAC ' . $professionDb->event_place . "\r\n";
                }
                if ($gedcom_texts == 'yes' && $professionDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $professionDb->event_text);
                }

                // *** Source by religion ***
                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_event_source', $professionDb->event_id, 2);
                }
            }

            // *** Person source ***
            if ($gedcom_sources == 'yes') {
                sources_export('person', 'person_source', $person->pers_gedcomnumber, 1);
            }

            // *** Person pictures ***
            $sourceqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='" . $person->pers_gedcomnumber . "'
                AND event_kind='picture' ORDER BY event_order");
            while ($sourceDb = $sourceqry->fetch(PDO::FETCH_OBJ)) {
                $buffer .= "1 OBJE\r\n";
                $buffer .= "2 FORM jpg\r\n";
                $buffer .= '2 FILE ' . $sourceDb->event_event . "\r\n";
                if ($sourceDb->event_date) $buffer .= '2 DATE ' . process_date($gedcom_version, $sourceDb->event_date) . "\r\n";

                if ($gedcom_texts == 'yes' && $sourceDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $sourceDb->event_text);
                }

                if ($gedcom_sources == 'yes') {
                    sources_export('person', 'pers_event_source', $sourceDb->event_id, 2);
                }
            }

            // *** Person Note ***
            if ($gedcom_texts == 'yes' && $person->pers_text) {
                $buffer .= '1 NOTE ' . process_text(2, $person->pers_text);
                sources_export('person', 'pers_text_source', $person->pers_gedcomnumber, 2);
            }

            // *** Person color marks ***
            $sourceqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='" . $person->pers_gedcomnumber . "'
                AND event_kind='person_colour_mark' ORDER BY event_order");
            while ($sourceDb = $sourceqry->fetch(PDO::FETCH_OBJ)) {
                $buffer .= '1 _COLOR ' . $sourceDb->event_event . "\r\n";
                //if ($gedcom_sources=='yes'){
                //	sources_export('person','pers_event_source',$sourceDb->event_id,2);
                //}
            }

            // *** Person events ***
            $event_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND event_connect_id='" . $person->pers_gedcomnumber . "'
                AND event_kind='event' ORDER BY event_order");
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $process_event = false;
                $process_event2 = false;
                if ($eventDb->event_gedcom == 'ADOP') {
                    $process_event2 = true;
                    $event_gedcom = '1 ADOP';
                }
                if ($eventDb->event_gedcom == '_ADPF') {
                    $process_event2 = true;
                    $event_gedcom = '1 _ADPF';
                }
                if ($eventDb->event_gedcom == '_ADPM') {
                    $process_event2 = true;
                    $event_gedcom = '1 _ADPM';
                }
                if ($eventDb->event_gedcom == 'AFN') {
                    $process_event2 = true;
                    $event_gedcom = '1 AFN';
                }
                if ($eventDb->event_gedcom == 'ARVL') {
                    $process_event2 = true;
                    $event_gedcom = '1 ARVL';
                }
                if ($eventDb->event_gedcom == 'BAPM') {
                    $process_event2 = true;
                    $event_gedcom = '1 BAPM';
                }
                if ($eventDb->event_gedcom == 'BAPL') {
                    $process_event2 = true;
                    $event_gedcom = '1 BAPL';
                }
                if ($eventDb->event_gedcom == 'BARM') {
                    $process_event2 = true;
                    $event_gedcom = '1 BARM';
                }
                if ($eventDb->event_gedcom == 'BASM') {
                    $process_event2 = true;
                    $event_gedcom = '1 BASM';
                }
                if ($eventDb->event_gedcom == 'BLES') {
                    $process_event2 = true;
                    $event_gedcom = '1 BLES';
                }
                if ($eventDb->event_gedcom == '_BRTM') {
                    $process_event2 = true;
                    $event_gedcom = '1 _BRTM';
                }
                if ($eventDb->event_gedcom == 'CAST') {
                    $process_event2 = true;
                    $event_gedcom = '1 CAST';
                }
                if ($eventDb->event_gedcom == 'CENS') {
                    $process_event2 = true;
                    $event_gedcom = '1 CENS';
                }
                if ($eventDb->event_gedcom == 'CHRA') {
                    $process_event2 = true;
                    $event_gedcom = '1 CHRA';
                }
                if ($eventDb->event_gedcom == 'CONF') {
                    $process_event2 = true;
                    $event_gedcom = '1 CONF';
                }
                if ($eventDb->event_gedcom == 'CONL') {
                    $process_event2 = true;
                    $event_gedcom = '1 CONL';
                }
                if ($eventDb->event_gedcom == 'DPRT') {
                    $process_event2 = true;
                    $event_gedcom = '1 DPRT';
                }
                if ($eventDb->event_gedcom == 'EDUC') {
                    $process_event2 = true;
                    $event_gedcom = '1 EDUC';
                }
                if ($eventDb->event_gedcom == 'EMIG') {
                    $process_event2 = true;
                    $event_gedcom = '1 EMIG';
                }
                if ($eventDb->event_gedcom == 'ENDL') {
                    $process_event2 = true;
                    $event_gedcom = '1 ENDL';
                }
                if ($eventDb->event_gedcom == 'EVEN') {
                    $process_event2 = true;
                    $event_gedcom = '1 EVEN';
                }
                if ($eventDb->event_gedcom == '_EYEC') {
                    $process_event2 = true;
                    $event_gedcom = '1 _EYEC';
                }
                if ($eventDb->event_gedcom == 'FCOM') {
                    $process_event2 = true;
                    $event_gedcom = '1 FCOM';
                }
                if ($eventDb->event_gedcom == '_FNRL') {
                    $process_event2 = true;
                    $event_gedcom = '1 _FNRL';
                }
                if ($eventDb->event_gedcom == 'GRAD') {
                    $process_event2 = true;
                    $event_gedcom = '1 GRAD';
                }
                if ($eventDb->event_gedcom == '_HAIR') {
                    $process_event2 = true;
                    $event_gedcom = '1 _HAIR';
                }
                if ($eventDb->event_gedcom == '_HEIG') {
                    $process_event2 = true;
                    $event_gedcom = '1 _HEIG';
                }
                if ($eventDb->event_gedcom == 'IDNO') {
                    $process_event2 = true;
                    $event_gedcom = '1 IDNO';
                }
                if ($eventDb->event_gedcom == 'IMMI') {
                    $process_event2 = true;
                    $event_gedcom = '1 IMMI';
                }
                if ($eventDb->event_gedcom == '_INTE') {
                    $process_event2 = true;
                    $event_gedcom = '1 _INTE';
                }
                if ($eventDb->event_gedcom == 'LEGI') {
                    $process_event2 = true;
                    $event_gedcom = '1 LEGI';
                }
                if ($eventDb->event_gedcom == '_MEDC') {
                    $process_event2 = true;
                    $event_gedcom = '1 _MEDC';
                }
                //if ($eventDb->event_gedcom=='MILI'){ $process_event=true; $event_gedcom='1 _MILT'; }
                if ($eventDb->event_gedcom == 'MILI') {
                    $process_event2 = true;
                    $event_gedcom = '1 _MILT';
                }
                if ($eventDb->event_gedcom == 'NATU') {
                    $process_event2 = true;
                    $event_gedcom = '1 NATU';
                }
                if ($eventDb->event_gedcom == 'NATI') {
                    $process_event2 = true;
                    $event_gedcom = '1 NATI';
                }
                if ($eventDb->event_gedcom == 'NCHI') {
                    $process_event2 = true;
                    $event_gedcom = '1 NCHI';
                }
                if ($eventDb->event_gedcom == '_NMAR') {
                    $process_event2 = true;
                    $event_gedcom = '1 _NMAR';
                }
                if ($eventDb->event_gedcom == 'ORDN') {
                    $process_event2 = true;
                    $event_gedcom = '1 ORDN';
                }
                if ($eventDb->event_gedcom == 'PROB') {
                    $process_event2 = true;
                    $event_gedcom = '1 PROB';
                }
                if ($eventDb->event_gedcom == 'PROP') {
                    $process_event2 = true;
                    $event_gedcom = '1 PROP';
                }
                if ($eventDb->event_gedcom == 'RETI') {
                    $process_event2 = true;
                    $event_gedcom = '1 RETI';
                }
                if ($eventDb->event_gedcom == 'SLGC') {
                    $process_event2 = true;
                    $event_gedcom = '1 SLGC';
                }
                if ($eventDb->event_gedcom == 'SLGL') {
                    $process_event2 = true;
                    $event_gedcom = '1 SLGL';
                }
                if ($eventDb->event_gedcom == 'SSN') {
                    $process_event2 = true;
                    $event_gedcom = '1 SSN';
                }
                if ($eventDb->event_gedcom == 'TXPY') {
                    $process_event2 = true;
                    $event_gedcom = '1 TXPY';
                }
                if ($eventDb->event_gedcom == '_WEIG') {
                    $process_event2 = true;
                    $event_gedcom = '1 _WEIG';
                }
                if ($eventDb->event_gedcom == 'WILL') {
                    $process_event2 = true;
                    $event_gedcom = '1 WILL';
                }
                if ($eventDb->event_gedcom == '_YART') {
                    $process_event2 = true;
                    $event_gedcom = '1 _YART';
                }

                /* No longer in use
                // *** Text is added in the first line: 1 _MILT military items. ***
                if ($process_event){
                    if ($eventDb->event_text) $buffer.=$event_gedcom.' '.process_text(2,$eventDb->event_text);
                    if ($eventDb->event_date) $buffer.='2 DATE '.process_date($gedcom_version,$eventDb->event_date)."\r\n";
                    if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
                }
                */

                // *** No text behind first line, add text at second NOTE line ***
                if ($process_event2) {
                    $buffer .= $event_gedcom;
                    // *** Add text behind GEDCOM tag ***
                    if ($eventDb->event_event) {
                        $buffer .= ' ' . $eventDb->event_event;
                    }
                    $buffer .= "\r\n";
                    if ($eventDb->event_text) {
                        $buffer .= '2 NOTE ' . process_text(3, $eventDb->event_text);
                    }
                    if ($eventDb->event_date) {
                        $buffer .= '2 DATE ' . process_date($gedcom_version, $eventDb->event_date) . "\r\n";
                    }
                    if ($eventDb->event_place) {
                        $buffer .= '2 PLAC ' . $eventDb->event_place . "\r\n";
                    }
                }

                // *** Source ***
                sources_export('person', 'pers_event_source', $eventDb->event_id, 2);
            }
        }


        // *** Quality ***
        // Disabled because normally quality belongs to a source.
        //if ($person->pers_quality=='0' || $person->pers_quality){
        //	$buffer.='2 QUAY '.$person->pers_quality."\r\n";
        //}

        // *** FAMS ***
        if ($person->pers_fams) {
            $pers_fams = explode(";", $person->pers_fams);
            foreach ($pers_fams as $i => $value) {
                if ($export["part_tree"] == 'part' && !in_array($pers_fams[$i], $famsids)) {
                    continue;
                }
                $buffer .= '1 FAMS @' . $pers_fams[$i] . "@\r\n";
            }
        }

        // *** FAMC ***
        if ($person->pers_famc) {
            if ($export["part_tree"] == 'part' && !in_array($person->pers_famc, $famsids)) {
            } // don't export FAMC
            else {
                $buffer .= '1 FAMC @' . $person->pers_famc . "@\r\n";
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
                $buffer .= "1 EVEN\r\n";
                $buffer .= "2 TYPE living\r\n";
            }
            // *** Privacy filter option for HuMo-genealogy ***
            if ($person->pers_alive == 'deceased') {
                if (
                    !$person->pers_death_date && !$person->pers_death_place && !$person->pers_death_text && !$person->pers_death_cause
                    && !$person->pers_buried_date && !$person->pers_buried_place && !$person->pers_buried_text && !$person->pers_cremation
                ) {
                    $buffer .= "1 EVEN\r\n";
                    $buffer .= "2 TYPE deceased\r\n";
                }
            }

            // *** Datetime new in database ***
            // 1_NEW
            // 2 DATE 04 AUG 2004
            // 3 TIME 13:39:58
            $buffer .= process_datetime($gedcom_version, 'new', $person->pers_new_datetime, $person->pers_new_user_id);
            // *** Datetime changed in database ***
            // 1_CHAN
            // 2 DATE 04 AUG 2004
            // 3 TIME 13:39:58
            $buffer .= process_datetime($gedcom_version, 'changed', $person->pers_changed_datetime, $person->pers_changed_user_id);
        }


        // *** Write person data ***
        $buffer = decode($buffer);
        fwrite($fh, $buffer);


        // *** Update processed lines ***
        $record_nr++;
        $perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);
        //flush();


        // *** Show person data on screen ***
        //$buffer = str_replace("\r\n", "<br>", $buffer);
        //echo $buffer;
    }

    /* EXAMPLE
    0 @F1@ FAM
    1 HUSB @I2@
    1 WIFE @I3@
    1 MARL
    2 DATE 25 AUG 1683
    2 PLAC Arnhem
    1 MARR
    2 TYPE civil
    2 DATE 30 NOV 1683
    2 PLAC Arnhem
    2 NOTE @N311@
    1 CHIL @I4@
    1 CHIL @I5@
    1 CHIL @I6@
    */

    // *** FAMILY DATA ***
    // *** To reduce use of memory, first read fam_id only ***
    $families_qry = $dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='" . $tree_id . "'");
    while ($families = $families_qry->fetch(PDO::FETCH_OBJ)) {

        // *** Now read all family items ***
        $family_qry = $dbh->query("SELECT * FROM humo_families WHERE fam_id='" . $families->fam_id . "'");
        $family = $family_qry->fetch(PDO::FETCH_OBJ);

        if ($export["part_tree"] == 'part'  && !in_array($family->fam_gedcomnumber, $famsids)) {
            continue;
        }

        // 0 @I1181@ INDI *** Gedcomnumber ***
        $buffer = '0 @' . $family->fam_gedcomnumber . "@ FAM\r\n";

        if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') echo $family->fam_gedcomnumber . ' ';

        if ($family->fam_man) {
            if ($export["part_tree"] == 'part' && !in_array($family->fam_man, $persids)) {
                // skip if not included (e.g. if spouse of base person in ancestor export or spouses of descendants in desc export are not checked for export)
            } else {
                $buffer .= '1 HUSB @' . $family->fam_man . "@\r\n";
            }
        }

        if ($family->fam_woman) {
            if ($export["part_tree"] == 'part' && !in_array($family->fam_woman, $persids)) {
                // skip if not included
            } else {
                $buffer .= '1 WIFE @' . $family->fam_woman . "@\r\n";
            }
        }

        // *** Pro-gen & HuMo-genealogy: Living together ***
        if ($family->fam_relation_date || $family->fam_relation_place || $family->fam_relation_text) {
            $buffer .= "1 _LIV\r\n";

            // *** Relation start date ***
            if ($family->fam_relation_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_relation_date) . "\r\n";
            }

            // *** Relation end date ***
            // How to export this date?

            if ($family->fam_relation_place) {
                $buffer .= process_place($family->fam_relation_place, 2);
            }
            if ($gedcom_sources == 'yes') {
                sources_export('family', 'fam_relation_source', $family->fam_gedcomnumber, 2);
            }
            if ($gedcom_texts == 'yes' && $family->fam_relation_text) {
                $buffer .= '2 NOTE ' . process_text(3, $family->fam_relation_text);
            }
        }

        // PMB if 'minimal' option selected don't export this
        if ($_POST['export_type'] == 'normal') {

            // *** Marriage notice ***
            if ($family->fam_marr_notice_date || $family->fam_marr_notice_place || $family->fam_marr_notice_text) {
                $buffer .= "1 MARB\r\n";
                $buffer .= "2 TYPE civil\r\n";
                if ($family->fam_marr_notice_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_marr_notice_date) . "\r\n";
                    if (isset($family->fam_marr_notice_date_hebnight) && $family->fam_marr_notice_date_hebnight == 'y') {
                        $buffer .= '2 _HNIT y' . "\r\n";
                    }
                }
                if ($family->fam_marr_notice_place) {
                    $buffer .= process_place($family->fam_marr_notice_place, 2);
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_marr_notice_source', $family->fam_gedcomnumber, 2);
                }

                if ($gedcom_texts == 'yes' && $family->fam_marr_notice_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $family->fam_marr_notice_text);
                }
            }

            // *** Marriage notice church ***
            if ($family->fam_marr_church_notice_date || $family->fam_marr_church_notice_place || $family->fam_marr_church_notice_text) {
                $buffer .= "1 MARB\r\n";
                $buffer .= "2 TYPE religious\r\n";
                if ($family->fam_marr_church_notice_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_marr_church_notice_date) . "\r\n";
                    if (isset($family->fam_marr_church_notice_date_hebnight) && $family->fam_marr_church_notice_date_hebnight == 'y') {
                        $buffer .= '2 _HNIT y' . "\r\n";
                    }
                }
                if ($family->fam_marr_church_notice_place) {
                    $buffer .= process_place($family->fam_marr_church_notice_place, 2);
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_marr_church_notice_source', $family->fam_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $family->fam_marr_church_notice_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $family->fam_marr_church_notice_text);
                }
            }
        }

        // *** Marriage ***
        if ($family->fam_marr_date || $family->fam_marr_place || $family->fam_marr_text) {
            $buffer .= "1 MARR\r\n";
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
                //$buffer .= "2 TYPE civil\r\n";
                $buffer .= '2 TYPE ' . $family->fam_kind . "\r\n";
            }

            if ($family->fam_marr_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_marr_date) . "\r\n";
                if (isset($family->fam_marr_date_hebnight) && $family->fam_marr_date_hebnight == 'y') {
                    $buffer .= '2 _HNIT y' . "\r\n";
                }
            }
            if ($family->fam_marr_place) {
                $buffer .= process_place($family->fam_marr_place, 2);
            }

            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {
                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_marr_source', $family->fam_gedcomnumber, 2);
                }
                if ($family->fam_man_age) {
                    $buffer .= "2 HUSB\r\n3 AGE " . $family->fam_man_age . "\r\n";
                }
                if ($family->fam_woman_age) {
                    $buffer .= "2 WIFE\r\n3 AGE " . $family->fam_woman_age . "\r\n";
                }
                if ($gedcom_texts == 'yes' && $family->fam_marr_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $family->fam_marr_text);
                }

                // *** Remark: only exported if there is another baptism item ***
                // *** $event_tree_id, $event_connect_kind,$event_connect_id, $event_kind ***
                // export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                //$buffer .= export_witnesses($gedcom_version, 'family', $family->fam_gedcomnumber, 'marriage_witness');
                $buffer .= export_witnesses($gedcom_version, 'MARR', $family->fam_gedcomnumber, 'ASSO');
            }
        }

        // *** Marriage religious ***
        if ($family->fam_marr_church_date || $family->fam_marr_church_place || $family->fam_marr_church_text) {
            $buffer .= "1 MARR\r\n";
            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {
                $buffer .= "2 TYPE religious\r\n";
            }

            if ($family->fam_marr_church_date) {
                $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_marr_church_date) . "\r\n";
                if (isset($family->fam_marr_church_date_hebnight) && $family->fam_marr_church_date_hebnight == 'y') {
                    $buffer .= '2 _HNIT y' . "\r\n";
                }
            }
            if ($family->fam_marr_church_place) {
                $buffer .= process_place($family->fam_marr_church_place, 2);
            }

            // PMB if 'minimal' option selected don't export this
            if ($_POST['export_type'] == 'normal') {

                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_marr_church_source', $family->fam_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $family->fam_marr_church_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $family->fam_marr_church_text);
                }

                // *** Remark: only exported if there is another baptism item ***
                // *** $event_tree_id, $event_connect_kind,$event_connect_id, $event_kind ***
                // export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
                //$buffer .= export_witnesses($gedcom_version, 'family', $family->fam_gedcomnumber, 'marriage_witness_rel');
                $buffer .= export_witnesses($gedcom_version, 'MARR_REL', $family->fam_gedcomnumber, 'ASSO');
            }
        }


        // PMB if 'minimal' option selected don't export this
        if ($_POST['export_type'] == 'normal') {

            // *** Divorced ***
            if ($family->fam_div_date || $family->fam_div_place || $family->fam_div_text) {
                $buffer .= "1 DIV\r\n";
                if ($family->fam_div_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $family->fam_div_date) . "\r\n";
                }
                if ($family->fam_div_place) {
                    $buffer .= process_place($family->fam_div_place, 2);
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_div_source', $family->fam_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $family->fam_div_text && $family->fam_div_text != 'DIVORCE') {
                    $buffer .= '2 NOTE ' . process_text(3, $family->fam_div_text);
                }
            }
        }

        if ($family->fam_children) {
            $child = explode(";", $family->fam_children);
            foreach ($child as $i => $value) {
                if ($export["part_tree"] == 'part' && !in_array($child[$i], $persids)) {
                    continue;
                }
                $buffer .= '1 CHIL @' . $child[$i] . "@\r\n";
            }
        }


        // PMB if 'minimal' option selected don't export this
        if ($_POST['export_type'] == 'normal') {

            // *** Family source ***
            if ($gedcom_sources == 'yes') {
                sources_export('family', 'family_source', $family->fam_gedcomnumber, 1);
            }

            // *** Addresses (shared addresses are no valid GEDCOM 5.5.1) ***
            addresses_export('family', 'family_address', $family->fam_gedcomnumber);

            // *** Family pictures ***
            $sourceqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='family' AND event_connect_id='" . $family->fam_gedcomnumber . "'
                AND event_kind='picture' ORDER BY event_order");
            while ($sourceDb = $sourceqry->fetch(PDO::FETCH_OBJ)) {
                $buffer .= "1 OBJE\r\n";
                $buffer .= "2 FORM jpg\r\n";
                $buffer .= '2 FILE ' . $sourceDb->event_event . "\r\n";
                if ($sourceDb->event_date) {
                    $buffer .= '2 DATE ' . process_date($gedcom_version, $sourceDb->event_date) . "\r\n";
                }

                if ($gedcom_texts == 'yes' && $sourceDb->event_text) {
                    $buffer .= '2 NOTE ' . process_text(3, $sourceDb->event_text);
                }

                if ($gedcom_sources == 'yes') {
                    sources_export('family', 'fam_event_source', $sourceDb->event_id, 2);
                }
            }

            // *** Family Note ***
            if ($gedcom_texts == 'yes' && $family->fam_text) {
                $buffer .= '1 NOTE ' . process_text(2, $family->fam_text);
                sources_export('family', 'fam_text_source', $family->fam_gedcomnumber, 2);
            }

            // *** Family events ***
            $event_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='family' AND event_connect_id='" . $family->fam_gedcomnumber . "'
                AND event_kind='event' ORDER BY event_order");
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $process_event = false;
                $process_event2 = false;
                if ($eventDb->event_gedcom == 'ANUL') {
                    $process_event2 = true;
                    $event_gedcom = '1 ANUL';
                }
                if ($eventDb->event_gedcom == 'CENS') {
                    $process_event2 = true;
                    $event_gedcom = '1 CENS';
                }
                if ($eventDb->event_gedcom == 'DIVF') {
                    $process_event2 = true;
                    $event_gedcom = '1 DIVF';
                }
                if ($eventDb->event_gedcom == 'ENGA') {
                    $process_event2 = true;
                    $event_gedcom = '1 ENGA';
                }
                if ($eventDb->event_gedcom == 'EVEN') {
                    $process_event2 = true;
                    $event_gedcom = '1 EVEN';
                }
                if ($eventDb->event_gedcom == 'MARC') {
                    $process_event2 = true;
                    $event_gedcom = '1 MARC';
                }
                if ($eventDb->event_gedcom == 'MARL') {
                    $process_event2 = true;
                    $event_gedcom = '1 MARL';
                }
                if ($eventDb->event_gedcom == 'MARS') {
                    $process_event2 = true;
                    $event_gedcom = '1 MARS';
                }
                if ($eventDb->event_gedcom == 'SLGS') {
                    $process_event2 = true;
                    $event_gedcom = '1 SLGS';
                }

                // *** Text is added in the first line: 1 _MILT military items. ***
                //if ($process_event){
                //	if ($eventDb->event_text) $buffer.=$event_gedcom.' '.process_text(2,$eventDb->event_text);
                //	if ($eventDb->event_date) $buffer.='2 DATE '.process_date($gedcom_version,$eventDb->event_date)."\r\n";
                //	if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
                //}

                // *** No text behind first line, add text at second NOTE line ***
                if ($process_event2) {
                    $buffer .= $event_gedcom;
                    if ($eventDb->event_event) $buffer .= ' ' . $eventDb->event_event;
                    $buffer .= "\r\n";
                    if ($eventDb->event_text) $buffer .= '2 NOTE ' . process_text(3, $eventDb->event_text);
                    if ($eventDb->event_date) $buffer .= '2 DATE ' . process_date($gedcom_version, $eventDb->event_date) . "\r\n";
                    if ($eventDb->event_place) $buffer .= '2 PLAC ' . $eventDb->event_place . "\r\n";
                }
            }

            // *** Datetime new in database ***
            // 1_NEW
            // 2 DATE 04 AUG 2004
            // 3 TIME 13:39:58
            $buffer .= process_datetime($gedcom_version, 'new', $family->fam_new_datetime, $family->fam_new_user_id);
            // *** Datetime changed in database ***
            // 1_CHAN
            // 2 DATE 04 AUG 2004
            // 3 TIME 13:39:58
            $buffer .= process_datetime($gedcom_version, 'changed', $family->fam_changed_datetime, $family->fam_changed_user_id);
        }


        // *** Write family data ***
        $buffer = decode($buffer);
        fwrite($fh, $buffer);

        // *** Update processed lines ***
        $record_nr++;
        $perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);
        //flush();

        // *** Show family data on screen ***
        //$buffer = str_replace("\r\n", "<br>", $buffer);
        //echo $buffer;
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

        if ($export["part_tree"] == 'part') {  // only include sources that are used by the people in this partial tree
            $source_array = array();
            // find all sources referred to by persons (I233) or families (F233)
            $qry = $dbh->query("SELECT connect_connect_id, connect_source_id FROM humo_connections
                WHERE connect_tree_id='" . $tree_id . "' AND connect_source_id != ''");
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                if (in_array($qryDb->connect_connect_id, $persids) || in_array($qryDb->connect_connect_id, $famsids)) {
                    $source_array[] = $qryDb->connect_source_id;
                }
            }
            // find all sources referred to by addresses (233)
            // shared addresses: we need a three-fold procedure....
            // First: in the connections table search for exported persons/families that have an RESI number connection (R34)
            $address_connect_qry = $dbh->query("SELECT connect_connect_id, connect_item_id
                FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_sub_kind LIKE '%_address'");
            $resi_array = array();
            while ($address_connect_qryDb = $address_connect_qry->fetch(PDO::FETCH_OBJ)) {
                if (in_array($address_connect_qryDb->connect_connect_id, $persids) || in_array($address_connect_qryDb->connect_connect_id, $famsids)) {
                    $resi_array[] = $address_connect_qryDb->connect_item_id;
                }
            }
            // Second: in the address table search for the previously found R numbers and get their id number (33)
            $address_address_qry = $dbh->query("SELECT address_gedcomnr, address_id FROM humo_addresses
                WHERE address_tree_id='" . $tree_id . "' AND address_gedcomnr !='' ");
            $resi_id_array = array();
            while ($address_address_qryDb = $address_address_qry->fetch(PDO::FETCH_OBJ)) {
                if (in_array($address_address_qryDb->address_gedcomnr, $resi_array)) {
                    $resi_id_array[] = $address_address_qryDb->address_id;
                }
            }
            // Third: back in the connections table, find the previously found address id numbers and get the associated source ged number ($23)
            $address_connect2_qry = $dbh->query("SELECT connect_connect_id, connect_source_id
                FROM humo_connections
                WHERE connect_tree_id='" . $tree_id . "' AND connect_sub_kind = 'address_source'");
            while ($address_connect2_qry_qryDb = $address_connect2_qry->fetch(PDO::FETCH_OBJ)) {
                if (in_array($address_connect2_qry_qryDb->connect_connect_id, $resi_id_array)) {
                    $source_array[] = $address_connect2_qry_qryDb->connect_source_id;
                }
            }
            // "direct" addresses
            $addressqry = $dbh->query("SELECT address_id, address_connect_sub_kind, address_connect_id
                FROM humo_addresses
                WHERE address_tree_id='" . $tree_id . "'");
            $source_address_array = array();
            while ($addressqryDb = $addressqry->fetch(PDO::FETCH_OBJ)) {
                if ($addressqryDb->address_connect_sub_kind == 'person' && in_array($addressqryDb->address_connect_id, $persids)) {
                    $source_address_array[] = $addressqryDb->address_id;
                }
                if ($addressqryDb->address_connect_sub_kind == 'family' && in_array($addressqryDb->address_connect_id, $famsids)) {
                    $source_address_array[] = $addressqryDb->address_id;
                }
            }
            $addresssourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id
                FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_sub_kind LIKE 'address_%'");
            while ($addresssourceqryDb = $addresssourceqry->fetch(PDO::FETCH_OBJ)) {
                if (in_array($addresssourceqryDb->connect_connect_id, $source_address_array)) {
                    $source_array[] = $addresssourceqryDb->connect_source_id;
                }
            }

            // find all sources referred to by events (233)
            $eventqry = $dbh->query("SELECT event_id, event_connect_kind, event_connect_id FROM humo_events");
            $source_event_array = array();
            while ($eventqryDb = $eventqry->fetch(PDO::FETCH_OBJ)) {
                if (
                    $eventqryDb->event_connect_kind == 'person'
                    && $eventqryDb->event_connect_id != '' && in_array($eventqryDb->event_connect_id, $persids)
                ) {
                    $source_event_array[] = $eventqryDb->event_id;
                }
                if (
                    $eventqryDb->event_connect_kind == 'family' and
                    $eventqryDb->event_connect_id != '' && in_array($eventqryDb->event_connect_id, $famsids)
                ) {
                    $source_event_array[] = $eventqryDb->event_id;
                }
            }
            $eventsourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id
                FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_sub_kind LIKE 'event_%'");
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

        if ($gedcom_sources == 'yes') {
            $family_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
            while ($family = $family_qry->fetch(PDO::FETCH_OBJ)) {
                if ($export["part_tree"] == 'part'  && !in_array($family->source_gedcomnr, $source_array)) {
                    continue;
                }

                // 0 @I1181@ INDI *** Gedcomnumber ***
                $buffer = '0 @' . $family->source_gedcomnr . "@ SOUR\r\n";

                if (isset($_POST['gedcom_status']) && $_POST['gedcom_status'] == 'yes') echo $family->source_gedcomnr . ' ';
                if ($family->source_title) {
                    $buffer .= '1 TITL ' . $family->source_title . "\r\n";
                }
                if ($family->source_abbr) {
                    $buffer .= '1 ABBR ' . $family->source_abbr . "\r\n";
                }
                if ($family->source_date) {
                    $buffer .= '1 DATE ' . process_date($gedcom_version, $family->source_date) . "\r\n";
                }
                if ($family->source_place) {
                    $buffer .= '1 PLAC ' . $family->source_place . "\r\n";
                }
                if ($family->source_publ) {
                    $buffer .= '1 PUBL ' . $family->source_publ . "\r\n";
                }
                if ($family->source_refn) {
                    $buffer .= '1 REFN ' . $family->source_refn . "\r\n";
                }
                if ($family->source_auth) {
                    $buffer .= '1 AUTH ' . $family->source_auth . "\r\n";
                }
                if ($family->source_subj) {
                    $buffer .= '1 SUBJ ' . $family->source_subj . "\r\n";
                }
                if ($family->source_item) {
                    $buffer .= '1 ITEM ' . $family->source_item . "\r\n";
                }
                if ($family->source_kind) {
                    $buffer .= '1 KIND ' . $family->source_kind . "\r\n";
                }
                if ($family->source_text) {
                    $buffer .= '1 NOTE ' . process_text(2, $family->source_text);
                }
                if (isset($family->source_status) && $family->source_status == 'restricted') {
                    $buffer .= '1 RESN privacy' . "\r\n";
                }

                // *** Source pictures ***
                $sourceqry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                    AND event_connect_kind='source' AND event_connect_id='" . $family->source_gedcomnr . "'
                    AND event_kind='picture' ORDER BY event_order");
                while ($sourceDb = $sourceqry->fetch(PDO::FETCH_OBJ)) {
                    $buffer .= "1 OBJE\r\n";
                    $buffer .= "2 FORM jpg\r\n";
                    $buffer .= '2 FILE ' . $sourceDb->event_event . "\r\n";
                    if ($sourceDb->event_date) {
                        $buffer .= '2 DATE ' . process_date($gedcom_version, $sourceDb->event_date) . "\r\n";
                    }

                    if ($gedcom_texts == 'yes' && $sourceDb->event_text) {
                        $buffer .= '2 NOTE ' . process_text(3, $sourceDb->event_text);
                    }

                    //if ($gedcom_sources=='yes'){
                    //	sources_export('source','source_event_source',$sourceDb->event_id,2);
                    //}
                }

                // source_repo_name, source_repo_caln, source_repo_page.

                // *** Datetime new in database ***
                // 1_NEW
                // 2 DATE 04 AUG 2004
                // 3 TIME 13:39:58
                $buffer .= process_datetime($gedcom_version, 'new', $family->source_new_datetime, $family->source_new_user_id);
                // *** Datetime changed in database ***
                // 1_CHAN
                // 2 DATE 04 AUG 2004
                // 3 TIME 13:39:58
                $buffer .= process_datetime($gedcom_version, 'changed', $family->source_changed_datetime, $family->source_changed_user_id);


                // *** Write source data ***
                $buffer = decode($buffer);
                fwrite($fh, $buffer);

                // *** Update processed lines ***
                $record_nr++;
                $perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);
                //flush();

                // *** Show source data on screen ***
                //$buffer = str_replace("\n", "<br>", $buffer);
                //echo $buffer;
            }

            // *** Repository data ***
            $repo_qry = $dbh->query("SELECT * FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "' ORDER BY repo_name, repo_place");
            while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) {
                $buffer = '0 @' . $repoDb->repo_gedcomnr . "@ REPO\r\n";
                if ($repoDb->repo_name) {
                    $buffer .= '1 NAME ' . $repoDb->repo_name . "\r\n";
                }
                if ($repoDb->repo_text) {
                    $buffer .= '1 NOTE ' . process_text(2, $repoDb->repo_text);
                }
                if ($repoDb->repo_address) {
                    $buffer .= '1 ADDR ' . process_text(2, $repoDb->repo_address);
                }
                if ($repoDb->repo_zip) {
                    $buffer .= '2 POST ' . $repoDb->repo_zip . "\r\n";
                }
                if ($repoDb->repo_phone) {
                    $buffer .= '1 PHON ' . $repoDb->repo_phone . "\r\n";
                }
                if ($repoDb->repo_mail) {
                    $buffer .= '1 EMAIL ' . $repoDb->repo_mail . "\r\n";
                }
                if ($repoDb->repo_url) {
                    $buffer .= '1 WWW ' . $repoDb->repo_url . "\r\n";
                }

                // *** Datetime new in database ***
                // 1_NEW
                // 2 DATE 04 AUG 2004
                // 3 TIME 13:39:58
                $buffer .= process_datetime($gedcom_version, 'new', $repoDb->repo_new_datetime, $repoDb->repo_new_user_id);
                // *** Datetime changed in database ***
                // 1_CHAN
                // 2 DATE 04 AUG 2004
                // 3 TIME 13:39:58
                $buffer .= process_datetime($gedcom_version, 'changed', $repoDb->repo_changed_datetime, $repoDb->repo_changed_user_id);

                // *** Write repoitory data ***
                $buffer = decode($buffer);
                fwrite($fh, $buffer);

                // *** Update processed lines ***
                $record_nr++;
                $perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);
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
        if (isset($_POST['gedcom_shared_addresses']) && $_POST['gedcom_shared_addresses'] == 'standard') $export_addresses = false;
        if ($export_addresses) {
            $family_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'");
            while ($family = $family_qry->fetch(PDO::FETCH_OBJ)) {
                // 0 @R1@ RESI *** Gedcomnumber ***
                $buffer = '0 @' . $family->address_gedcomnr . "@ RESI\r\n";

                if ($family->address_address) {
                    $buffer .= '1 ADDR ' . $family->address_address . "\r\n";
                }
                if ($family->address_zip) {
                    $buffer .= '1 ZIP ' . $family->address_zip . "\r\n";
                }
                if ($family->address_date) {
                    $buffer .= '1 DATE ' . process_date($gedcom_version, $family->address_date) . "\r\n";
                }
                if ($family->address_place) {
                    $buffer .= '1 PLAC ' . $family->address_place . "\r\n";
                }
                if ($family->address_phone) {
                    $buffer .= '1 PHON ' . $family->address_phone . "\r\n";
                }
                if ($gedcom_sources == 'yes') {
                    sources_export('address', 'address_source', $family->address_gedcomnr, 2);
                }
                if ($family->address_text) {
                    $buffer .= '1 NOTE ' . process_text(2, $family->address_text);
                }

                // photo

                // *** Write source data ***
                $buffer = decode($buffer);
                fwrite($fh, $buffer);
                // *** Show source data on screen ***
                //$buffer = str_replace("\r\n", "<br>", $buffer);
                //echo $buffer;
            }
        }

        // *** Notes ***
        // 0 @N1@ NOTE
        // 1 CONT Start of the note
        // 2 CONC add a bit more to the line
        // 2 CONT Another line of the note

        // This adds seperate note records for all the note refs in table texts captured in $noteids
        if ($gedcom_texts == 'yes') {
            $buffer = '';
            natsort($noteids);
            foreach ($noteids as $note_text) {
                //$text_query = "SELECT * FROM humo_texts WHERE text_tree_id='" . $tree_id . "' AND text_gedcomnr='" . substr($note_text, 1, -1) . "'";
                //$text_sql = $dbh->query($text_query);
                //while ($textDb = $text_sql->fetch(PDO::FETCH_OBJ)) {

                $stmt = $dbh->prepare("SELECT * FROM humo_texts WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr");
                $stmt->execute([
                    ':text_tree_id' => $tree_id,
                    ':text_gedcomnr' => substr($note_text, 1, -1)
                ]);
                while ($textDb = $stmt->fetch(PDO::FETCH_OBJ)) {
                    $buffer .= "0 " . $note_text . " NOTE\r\n";
                    $buffer .= '1 CONC ' . process_text(1, $textDb->text_text);

                    // *** Datetime new in database ***
                    // 1_NEW
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $buffer .= process_datetime($gedcom_version, 'new', $textDb->text_new_datetime, $textDb->text_new_user_id);
                    // *** Datetime changed in database ***
                    // 1_CHAN
                    // 2 DATE 04 AUG 2004
                    // 3 TIME 13:39:58
                    $buffer .= process_datetime($gedcom_version, 'changed', $textDb->text_changed_datetime, $textDb->text_changed_user_id);
                }
            }

            // *** Write note data ***
            $buffer = decode($buffer);
            fwrite($fh, $buffer);

            // *** Update processed lines ***
            $record_nr++;
            $perc = update_bootstrap_bar($record_nr, $step, $devider, $perc);
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

function decode($buffer)
{
    //$buffer = html_entity_decode($buffer, ENT_NOQUOTES, 'ISO-8859-15');
    //$buffer = html_entity_decode($buffer, ENT_QUOTES, 'ISO-8859-15');
    if (isset($_POST['gedcom_char_set']) && $_POST['gedcom_char_set'] == 'ANSI')
        $buffer = iconv("UTF-8", "windows-1252", $buffer);
    return $buffer;
}

function process_date($gedcom_version, $text)
{
    if ($gedcom_version == '551') {
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
function process_text($level, $text, $extractnoteids = true)
{
    global $noteids, $gedcom_version;

    $text = str_replace("<br>", "", $text);
    $text = str_replace("\r", "", $text);

    // *** Export referenced texts ***
    if ($extractnoteids && substr($text, 0, 1) == '@') {
        $noteids[] = $text;
    }

    $regel = explode("\n", $text);
    // *** If text is too long split it, GEDCOM 5.5.1 specs: max. 255 characters including tag. ***
    $text = '';
    $text_processed = '';
    for ($j = 0; $j <= (count($regel) - 1); $j++) {
        $text = $regel[$j] . "\r\n";

        // *** CONC isn't allowed in GEDCOM 7.0 ***
        if ($gedcom_version == '551') {
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

function process_place($place, $number)
{
    global $dbh;
    // 2 PLAC Cleveland, Ohio, USA
    // 3 MAP
    // 4 LATI N41.500347
    // 4 LONG W81.66687
    $text = $number . ' PLAC ' . $place . "\r\n";
    if (isset($_POST['gedcom_geocode']) && $_POST['gedcom_geocode'] == 'yes') {
        $geo_location_sql = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_location='" . addslashes($place) . "'";
        $geo_location_qry = $dbh->query($geo_location_sql);
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
function addresses_export($connect_kind, $connect_sub_kind, $connect_connect_id)
{
    global $dbh, $buffer, $tree_id, $db_functions, $gedcom_sources, $gedcom_version;

    // *** Addresses (shared addresses are no valid GEDCOM 5.5.1) ***
    // *** Living place ***
    // 1 RESI
    // 2 ADDR Ridderkerk
    // 1 RESI
    // 2 ADDR Slikkerveer
    $eventnr = 0;
    $connect_sql = $db_functions->get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id);
    foreach ($connect_sql as $connectDb) {
        $addressDb = $db_functions->get_address($connectDb->connect_item_id);
        // *** Next items are only exported if Address is shared ***

        $export_addresses = false;
        if ($addressDb->address_shared == '1') $export_addresses = true;
        if (isset($_POST['gedcom_shared_addresses']) && $_POST['gedcom_shared_addresses'] == 'standard') $export_addresses = false;
        if ($export_addresses) {
            // *** Shared address ***
            // 1 RESI @R210@
            // 2 DATE 1 JAN 2021
            // 2 ROLE ROL
            $buffer .= '1 RESI @' . $connectDb->connect_item_id . "@\r\n";
            if ($connectDb->connect_date) $buffer .= '2 DATE ' . process_date($gedcom_version, $connectDb->connect_date) . "\r\n";
            if ($connectDb->connect_role) {
                $buffer .= '2 ROLE ' . $connectDb->connect_role . "\r\n";
            }

            // *** Extra text by address ***
            if ($connectDb->connect_text) {
                // 2 DATA
                // 3 TEXT text .....
                // 4 CONT ..........
                $buffer .= "2 DATA\r\n";
                $buffer .= '3 TEXT ' . process_text(4, $connectDb->connect_text);
            }

            // *** Source by address ***
            if ($gedcom_sources == 'yes') {
                //if ($connect_kind=='person'){
                //	//$buffer.='2 SOUR '.process_text(3,$addressDb->address_source);
                //	sources_export('person','pers_address_source',$addressDb->address_id,2);
                //}
                //else{
                //	sources_export('family','fam_address_source',$addressDb->address_id,2);
                //}

                if ($connect_kind == 'person') {
                    sources_export('person', 'pers_address_connect_source', $connectDb->connect_id, 2);
                } else {
                    sources_export('family', 'fam_address_connect_source', $connectDb->connect_id, 2);
                }
            }
        } else {
            // *** Living place ***
            // 1 RESI
            // 2 ADDR Ridderkerk
            // 1 RESI
            // 2 ADDR Slikkerveer
            $buffer .= "1 RESI\r\n";

            // *** Export HuMo-genealogy address GEDCOM numbers ***
            $buffer .= '2 RIN ' . substr($connectDb->connect_item_id, 1) . "\r\n";

            $buffer .= '2 ADDR';
            if ($addressDb->address_address) {
                $buffer .= ' ' . $addressDb->address_address;
            }
            $buffer .= "\r\n";
            if ($addressDb->address_place) {
                $buffer .= '3 CITY ' . $addressDb->address_place . "\r\n";
            }
            if ($addressDb->address_zip) {
                $buffer .= '3 POST ' . $addressDb->address_zip . "\r\n";
            }
            if ($addressDb->address_phone) {
                $buffer .= '2 PHON ' . $addressDb->address_phone . "\r\n";
            }
            //if ($addressDb->address_date){ $buffer.='2 DATE '.process_date($gedcom_version,$addressDb->address_date)."\r\n"; }
            if ($connectDb->connect_date) $buffer .= '2 DATE ' . process_date($gedcom_version, $connectDb->connect_date) . "\r\n";
            if ($addressDb->address_text) {
                $buffer .= '2 NOTE ' . process_text(3, $addressDb->address_text);
            }

            // *** Source by address ***
            if ($gedcom_sources == 'yes') {
                //if ($connect_kind=='person'){
                //	//$buffer.='2 SOUR '.process_text(3,$addressDb->address_source);
                //	sources_export('person','pers_address_source',$addressDb->address_gedcomnr,2);
                //}
                //else{
                //	sources_export('family','fam_address_source',$addressDb->address_gedcomnr,2);
                //}

                if ($connect_kind == 'person') {
                    sources_export('person', 'pers_address_connect_source', $connectDb->connect_id, 2);
                } else {
                    sources_export('family', 'fam_address_connect_source', $connectDb->connect_id, 2);
                }

                sources_export('address', 'address_source', $addressDb->address_gedcomnr, 2);
            }
        }
    }
}

// *** Function to export all kind of sources including role, pages etc. ***
function sources_export($connect_kind, $connect_sub_kind, $connect_connect_id, $start_number)
{
    global $dbh, $buffer, $tree_id, $gedcom_version;
    // *** Search for all connected sources ***
    $connect_qry = "SELECT * FROM humo_connections LEFT JOIN humo_sources ON source_gedcomnr=connect_source_id
        WHERE connect_tree_id='" . $tree_id . "' AND source_tree_id='" . $tree_id . "'
        AND connect_kind='" . $connect_kind . "'
        AND connect_sub_kind='" . $connect_sub_kind . "'
        AND connect_connect_id='" . $connect_connect_id . "'
        ORDER BY connect_order";
    $connect_sql = $dbh->query($connect_qry);
    while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
        //$connect_sql = $db_functions->get_connections_connect_id('person','person_address',$person->pers_gedcomnumber);
        //foreach ($connect_sql as $connectDb){

        // *** Source contains title, can be connected to multiple items ***
        // 0 @S2@ SOUR
        // 1 ROLE ROL
        // 1 PAGE page
        $buffer .= $start_number . ' SOUR @' . $connectDb->connect_source_id . "@\r\n";
        if ($connectDb->connect_role) {
            $buffer .= ($start_number + 1) . ' ROLE ' . $connectDb->connect_role . "\r\n";
        }
        if ($connectDb->connect_page) {
            $buffer .= ($start_number + 1) . ' PAGE ' . $connectDb->connect_page . "\r\n";
        }
        if ($connectDb->connect_quality || $connectDb->connect_quality == '0') {
            $buffer .= ($start_number + 1) . ' QUAY ' . $connectDb->connect_quality . "\r\n";
        }

        // *** Source citation (extra text by source) ***
        // 3 DATA
        // 4 DATE ......
        // 4 PLAC ....... (not in GEDOM specifications).
        // 4 TEXT text .....
        // 5 CONT ..........
        if ($connectDb->connect_text || $connectDb->connect_date || $connectDb->connect_place) {
            $buffer .= ($start_number + 1) . " DATA\r\n";

            if ($connectDb->connect_date) {
                $buffer .= ($start_number + 2) . ' DATE ' . process_date($gedcom_version, $connectDb->connect_date) . "\r\n";
            }

            if ($connectDb->connect_place) {
                $buffer .= ($start_number + 2) . ' PLAC ' . $connectDb->connect_place . "\r\n";
            }

            if ($connectDb->connect_text) {
                $buffer .= ($start_number + 2) . ' TEXT ' . process_text($start_number + 3, $connectDb->connect_text);
            }
        }
    }
}

function descendants($family_id, $main_person, $generation_number, $max_generations)
{
    global $dbh, $tree_id, $db_functions;
    global $persids, $famsids;
    global $language;
    $family_nr = 1; //*** Process multiple families ***
    if ($max_generations < $generation_number) {
        return;
    }
    $generation_number++;
    // *** Count marriages of man ***
    // *** If needed show woman as main_person ***
    if ($family_id == '') { // single person
        $persids[] = $main_person;
        return;
    }

    $family = $dbh->query("SELECT fam_man, fam_woman FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $family_id . "'");
    try {
        $familyDb = $family->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        echo __('No valid family number.');
    }

    $parent1 = '';
    $parent2 = '';
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
        $personDb = $db_functions->get_person($parent1);

        $marriage_array = explode(";", $personDb->pers_fams);
        $nr_families = substr_count($personDb->pers_fams, ";");
    } else {
        $marriage_array[0] = $family_id;
        $nr_families = "0";
    }

    // *** Loop multiple marriages of main_person ***
    for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
        $id = $marriage_array[$parent1_marr];
        $familyDb = $db_functions->get_family($id);

        // *************************************************************
        // *** Parent1 (normally the father)                         ***
        // *************************************************************
        if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, vrouw zonder man
            if ($family_nr == 1) {
                // *** Show data of man ***

                if ($swap_parent1_parent2 == true) {
                    // store I and Fs
                    $persids[] = $familyDb->fam_woman;
                    $families = explode(';', $personDb->pers_fams);
                    foreach ($families as $value) {
                        $famsids[] = $value;
                    }
                } else {
                    // store I and Fs
                    $persids[] = $familyDb->fam_man;
                    $families = explode(';', $personDb->pers_fams);
                    foreach ($families as $value) {
                        $famsids[] = $value;
                    }
                }
            }
            $family_nr++;
        } // *** end check of PRO-GEN ***

        // *************************************************************
        // *** Parent2 (normally the mother)                         ***
        // *************************************************************
        if (isset($_POST['desc_spouses'])) {
            if ($swap_parent1_parent2 == true) {
                $persids[] = $familyDb->fam_man;
                $desc_sp = $familyDb->fam_man;
            } else {
                $persids[] = $familyDb->fam_woman;
                $desc_sp = $familyDb->fam_woman;
            }
        }
        if (isset($_POST['desc_sp_parents'])) { // if set, add parents of spouse
            $spqry = $dbh->query("SELECT pers_famc FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber = '" . $desc_sp . "'");
            $spqryDb = $spqry->fetch(PDO::FETCH_OBJ);
            if (isset($spqryDb->pers_famc) && $spqryDb->pers_famc) {
                $famqryDb = $db_functions->get_family($spqryDb->pers_famc);
                if ($famqryDb->fam_man) {
                    $persids[] = $famqryDb->fam_man;
                }
                if ($famqryDb->fam_woman) {
                    $persids[] = $famqryDb->fam_woman;
                }
                $famsids[] = $spqryDb->pers_famc;
            }
        }
        // *************************************************************
        // *** Children                                              ***
        // *************************************************************
        if ($familyDb->fam_children) {
            $child_array = explode(";", $familyDb->fam_children);
            foreach ($child_array as $i => $value) {
                $child = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $child_array[$i] . "'");
                $childDb = $child->fetch(PDO::FETCH_OBJ);
                //$childDb = $db_functions->get_person($child_array[$i]);
                if ($child->rowCount() > 0) {
                    // *** Build descendant_report ***
                    if ($childDb->pers_fams) {
                        // *** 1st family of child ***
                        $child_family = explode(";", $childDb->pers_fams);
                        $child1stfam = $child_family[0];
                        descendants($child1stfam, $childDb->pers_gedcomnumber, $generation_number, $max_generations);  // recursive
                    } else {  // Child without own family
                        if ($max_generations >= $generation_number) {
                            $childgn = $generation_number + 1;
                            $persids[] = $childDb->pers_gedcomnumber;
                        }
                    }
                }
            }
        }
    } // Show  multiple marriages
} // End of descendant function

function ancestors($person_id, $max_generations)
{
    global $tree_id, $dbh, $db_functions, $persids, $famsids;
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
            if ($listednr == '') {  //if not listed yet, add person to array
                $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
            }
            if ($ancestor_array[$i] != '0') {
                $person_manDb = $db_functions->get_person($ancestor_array[$i]);
                if (strtolower($person_manDb->pers_sexe) == 'm' && $ancestor_number[$i] > 1) {
                    $familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);
                    $person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                }
                if ($listednr == '') {
                    //take I and F
                    if ($person_manDb->pers_gedcomnumber == $person_id) { // for the base person we add spouse manually
                        $persids[] = $person_manDb->pers_gedcomnumber;
                        if ($person_manDb->pers_fams) {
                            $families = explode(';', $person_manDb->pers_fams);
                            if ($person_manDb->pers_sexe == 'M') {
                                $spouse = "fam_woman";
                            } else {
                                $spouse = "fam_man";
                            }
                            foreach ($families as $value) {
                                $sp_main = $dbh->query("SELECT " . $spouse . " FROM humo_families
                                    WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $value . "'");
                                $sp_mainDb = $sp_main->fetch(PDO::FETCH_OBJ);
                                if (isset($_POST['ances_spouses'])) { // we also include spouses of base person
                                    $persids[] = $sp_mainDb->$spouse;
                                }
                                $famsids[] = $value;
                            }
                        }
                    } else { // any other person
                        $persids[] = $person_manDb->pers_gedcomnumber;
                    }
                    if ($person_manDb->pers_famc && $generation + 1 < $max_generations) {  // if this is the last generation (max gen) we don't want the famc!
                        $famsids[] = $person_manDb->pers_famc;
                        if (isset($_POST['ances_sibbl'])) { // also get I numbers of sibblings
                            $sibbqryDb = $db_functions->get_family($person_manDb->pers_famc);
                            $sibs = explode(';', $sibbqryDb->fam_children);
                            foreach ($sibs as $value) {
                                if ($value != $person_manDb->pers_gedcomnumber) {
                                    $persids[] = $value;
                                }
                            }
                        }
                    }
                } else { // person was already listed
                    // do nothing
                }

                // == Check for parents
                if ($person_manDb->pers_famc  && $listednr == '') {
                    $family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
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
                $person_manDb = $db_functions->get_person($ancestor_array[$i]);
                // take I (and F?)
            }
        }    // loop per generation
        $generation++;
    }    // loop ancestors function
}

function export_witnesses($gedcom_version, $event_connect_kind, $event_connect_id, $event_kind)
{
    global $db_functions;
    $witnesses = '';
    $witness_qry = $db_functions->get_events_connect($event_connect_kind, $event_connect_id, $event_kind);
    foreach ($witness_qry as $witnessDb) {
        if ($gedcom_version == '551') {
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
function process_datetime($gedcom_version, $new_changed, $datetime, $user_id)
{
    $buffer = '';
    if ($datetime && $datetime != '1970-01-01 00:00:01') {
        if ($new_changed == 'new' && $gedcom_version == '551') {
            $buffer .= "1 _NEW\r\n";
        } elseif ($new_changed == 'new') {
            $buffer .= "1 CREA\r\n";
        } else {
            $buffer .= "1 CHAN\r\n";
        }

        $export_date = strtoupper(date('d M Y', (strtotime($datetime))));
        $buffer .= "2 DATE " . process_date($gedcom_version, $export_date) . "\r\n";

        $buffer .= "3 TIME " . date('H:i:s', (strtotime($datetime))) . "\r\n";

        if ($user_id) {
            $buffer .= "2 _USR " . $user_id . "\r\n";
        }
    }
    return $buffer;
}
