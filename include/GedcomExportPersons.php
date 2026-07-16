<?php

/**
 * GedcomExportPersons class
 * 
 * Handles GEDCOM export of individual person records
 * Dec. 2025 Extracted from GedcomExport class
 */

namespace Genealogy\Include;

use PDO;

class GedcomExportPersons extends GedcomExportFunctions
{
    //private $db_functions;
    //private $tree_id;
    //private $gedcom_sources = '';
    private $persids = array();
    private $famsids = array();
    //private $noteids = array();

    public function __construct($dbh, $db_functions, $tree_id, $gedcom_version = '551', $gedcom_sources = '')
    {
        $this->dbh = $dbh;
        $this->db_functions = $db_functions;
        $this->tree_id = $tree_id;
        $this->gedcom_version = $gedcom_version;
        $this->gedcom_sources = $gedcom_sources;
    }

    /**
     * Set arrays for partial tree export
     */
    public function setExportArrays($persids, $famsids, $noteids): void
    {
        $this->persids = $persids;
        $this->famsids = $famsids;
        $this->noteids = $noteids;
    }

    /**
     * Get updated noteids array after export
     */
    public function getNoteids(): array
    {
        return $this->noteids;
    }

    /**
     * Export all persons to GEDCOM file
     * 
     * @param resource $fh File handle
     * @param array $export Export configuration
     * @param string $gedcom_texts Include texts yes/no
     */
    public function exportPersons($fh, $export, $gedcom_texts): void
    {
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

            // *** Name ***
            $this->exportPersonName($person, $gedcom_texts);

            // *** Create general person_events array ***
            $person_events = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'all');

            // *** Export early INDI events ***
            $this->exportPersonEarlyEvents($person_events, $gedcom_texts);

            // *** Patronym ***
            if ($person->pers_patronym) {
                $this->buffer .= '1 _PATR ' . $person->pers_patronym . "\r\n";
            }

            // *** Sex ***
            $this->writeLine(1, 'SEX', $person->pers_sexe);
            if ($this->gedcom_sources == 'yes') {
                $this->sources_export('person', 'pers_sexe_source', $person->pers_gedcomnumber, 2);
            }

            // *** Life events ***
            $this->exportPersonLifeEvents($person, $gedcom_texts);

            // *** Additional person data ***
            if ($_POST['export_type'] == 'normal') {
                $this->exportPersonAdditionalData($person, $gedcom_texts);
            }

            // *** FAMS and FAMC ***
            $this->exportPersonRelations($person, $export);

            // *** Privacy and timestamps ***
            if ($_POST['export_type'] == 'normal') {
                $this->exportPersonPrivacy($person);
            }

            // *** Write person data ***
            $this->decode();
            fwrite($fh, $this->buffer);

            // *** Update processed lines ***
            $this->record_nr++;
            $this->perc = $this->update_bootstrap_bar();
        }
    }

    private function exportPersonName($person, $gedcom_texts): void
    {
        // Build full GEDCOM name value: Firstname /Prefix Lastname/
        $nameSurname = '';
        $firstname = trim($person->pers_firstname ?? '');
        $prefix = $person->pers_prefix ? trim(str_replace('_', ' ', $person->pers_prefix)) : '';
        $surname = trim($person->pers_lastname ?? '');

        // *** Prefix ***
        if ($prefix) {
            $nameSurname .= $prefix;
            if ($person->pers_lastname) {
                $nameSurname .= ' ';
            }
        }

        // *** Surname ***
        $nameSurname .= $surname;

        // *** Firstname ***
        $fullName = $firstname;
        if ($fullName !== '') {
            $fullName .= ' ';
        }

        // *** Add slashes around surname part, even if empty, because GEDCOM standard requires this. If surname is empty, there will be two slashes after each other. ***
        $fullName .= '/' . $nameSurname . '/';

        $this->writeLine(1, 'NAME', $fullName);

        /*
        // Preparations for GIVN, SPFX and SURN.
        // Not enabled yet because Belgian doesn't use SPFX. The prefix is part of the surname. If needed, it's probably better to add a new GEDCOM Export option.

        if ($firstname !== '') {
            $this->writeLine(2, 'GIVN', $firstname);
        }
        if ($prefix !== '') {
            $this->writeLine(2, 'SPFX', $prefix);
        }
        if ($surname !== '') {
            $this->writeLine(2, 'SURN', $surname);
        }
        */

        // PMB if 'minimal' option selected don't export this
        if ($_POST['export_type'] == 'normal') {
            if ($this->gedcom_sources == 'yes') {
                $this->sources_export('person', 'pers_name_source', $person->pers_gedcomnumber, 2);
            }
            if ($gedcom_texts == 'yes' && $person->pers_name_text) {
                $this->writeNote(2, $person->pers_name_text);
            }
        }
    }

    private function exportPersonEarlyEvents($person_events, $gedcom_texts): void
    {
        if ($_POST['export_type'] != 'normal') {
            return;
        }

        foreach ($person_events as $person_event) {
            // *** Export all name items, like 2 _AKAN etc. ***
            if ($person_event->event_kind == 'name' || $person_event->event_kind == 'NPFX' || $person_event->event_kind == 'NSFX') {
                $this->buffer .= '2 ' . $person_event->event_gedcom . ' ' . $person_event->event_event . "\r\n";
                if ($person_event->event_date) {
                    $this->buffer .= '3 DATE ' . $this->process_date($person_event->event_date) . "\r\n";
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_event_source', $person_event->event_id, 3);
                }
                if ($gedcom_texts == 'yes' && $person_event->event_text) {
                    $this->writeNote(3, $person_event->event_text);
                }
            }

            // *** Export of person titles ***
            if ($person_event->event_kind == 'title') {
                $this->writeLine(1, 'TITL', $person_event->event_event);
                if ($person_event->event_date) {
                    $this->writeLine(2, 'DATE', $this->process_date($person_event->event_date));
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                }
                if ($gedcom_texts == 'yes' && $person_event->event_text) {
                    $this->writeNote(2, $person_event->event_text);
                }
            }

            // *** Adoption ***
            if ($person_event->event_kind == 'adoption') {
                $this->writeLine(1, 'ADOP');
                if ($person_event->event_event) {
                    $this->writeLine(2, 'FAMC', '@' . $person_event->event_event . '@');
                }
                if ($person_event->event_date) {
                    $this->writeLine(2, 'DATE', $this->process_date($person_event->event_date));
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                }
                if ($gedcom_texts == 'yes' && $person_event->event_text) {
                    $this->writeNote(2, $person_event->event_text);
                }
            }

            // *** Nobility (predikaat) ***
            if ($person_event->event_kind == 'nobility') {
                $this->writeLine(1, 'EVEN', $person_event->event_event);
                $this->writeLine(2, 'TYPE', 'predikaat');
                if ($person_event->event_date) {
                    $this->writeLine(2, 'DATE', $this->process_date($person_event->event_date));
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_event_source', $person_event->event_id, 2);
                }
                if ($gedcom_texts == 'yes' && $person_event->event_text) {
                    $this->writeNote(2, $person_event->event_text);
                }
            }
        }
    }

    private function exportPersonLifeEvents($person, $gedcom_texts): void
    {
        // *** Birth data ***
        if (
            $person->pers_birth_date || $person->pers_birth_place || $person->pers_birth_text
            || (isset($person->pers_stillborn) && $person->pers_stillborn == 'y')
        ) {
            $this->writeLine(1, 'BIRT');
            if ($person->pers_birth_date) {
                $this->writeLine(2, 'DATE', $this->process_date($person->pers_birth_date));
                if (isset($person->pers_birth_date_hebnight) && $person->pers_birth_date_hebnight == 'y') {
                    $this->writeLine(2, '_HNIT', 'y');
                }
            }
            if ($person->pers_birth_place) {
                $this->buffer .= $this->process_place($person->pers_birth_place, 2);
            }
            if ($person->pers_birth_time) {
                $this->writeLine(2, 'TIME', $person->pers_birth_time);
            }
            if ($_POST['export_type'] == 'normal') {
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_birth_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_birth_text) {
                    $this->writeNote(2, $person->pers_birth_text);
                }

                if (isset($person->pers_stillborn) && $person->pers_stillborn == 'y') {
                    $this->writeLine(2, 'TYPE', 'stillborn');
                }

                // *** New sept. 2023 ***
                // *** Remark: only exported if there is another birth item ***
                // *** Oct 2024: for GEDCOM 7 changed to seperate event ***
                if ($this->gedcom_version == '551') {
                    $this->buffer .= $this->export_witnesses('birth_declaration', $person->pers_gedcomnumber, 'ASSO');
                }
            }
        }

        /**
         * Birth registration
         * NEW oct. 2024: seperate event for Birth registration
         * 
         * GEDCOM 5.5.1
         * 
         * 1 EVEN
         * 2 TYPE birth registration
         * 2 DATE 2 JAN 1980
         * 2 SOUR @S5@
         * 
         * GEDCOM 7.0 (Aldfaer)
         * 
         * 1 EVEN
         * 2 TYPE birth registration
         * 2 DATE 2 JAN 1980
         * 2 SOUR @S5@
         * 2 _OBJE
         * 3 FILE 0d0d3dfdf7eb5ec8d94609dc49079b2a.jpg
         * 2 ASSO @I4@
         * 3 ROLE OFFICIATOR
         * 2 ASSO @I3@
         * 3 ROLE WITN
         * 2 ASSO @I5@
         * 3 ROLE OTHER
         * 4 PHRASE informant
         */
        if ($this->gedcom_version != '551') {
            $birth_declarationDb = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'birth_declaration');
            //$birth_declarationDb = !empty($birth_declarationDb) ? $birth_declarationDb[0] : null;
            $birth_decl_witnesses = $this->export_witnesses('birth_declaration', $person->pers_gedcomnumber, 'ASSO');

            if ($birth_declarationDb || $birth_decl_witnesses) {
                $this->writeLine(1, 'EVEN');
                $this->writeLine(2, 'TYPE', 'birth registration');

                if ($birth_declarationDb && $birth_declarationDb->event_date) {
                    $this->writeLine(2, 'DATE', $birth_declarationDb->event_date);
                }
                if ($birth_declarationDb && $birth_declarationDb->event_place) {
                    $this->writeLine(2, 'PLAC', $birth_declarationDb->event_place);
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'birth_decl_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $birth_declarationDb && $birth_declarationDb->event_text) {
                    $this->writeNote(2, $birth_declarationDb->event_text);
                }
                $this->buffer .= $birth_decl_witnesses;
            }
        }

        // *** Christening data ***
        if ($person->pers_bapt_date || $person->pers_bapt_place || $person->pers_bapt_text || $person->pers_religion) {
            $this->writeLine(1, 'CHR');
            if ($person->pers_bapt_date) {
                $this->writeLine(2, 'DATE', $this->process_date($person->pers_bapt_date));
            }
            if ($person->pers_bapt_place) {
                $this->buffer .= $this->process_place($person->pers_bapt_place, 2);
            }
            if ($_POST['export_type'] == 'normal') {
                // *** Person religion. This is 1 CHR -> 2 RELI! 1 RELI is exported as event (after profession) ***
                if ($person->pers_religion) {
                    $this->writeLine(2, 'RELI', $person->pers_religion);
                }

                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_bapt_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_bapt_text) {
                    $this->writeNote(2, $person->pers_bapt_text);
                }

                // *** Remark: only exported if there is another baptism item ***
                $this->buffer .= $this->export_witnesses('CHR', $person->pers_gedcomnumber, 'ASSO');
            }
        }

        // *** Death data ***
        if (
            $person->pers_death_date || $person->pers_death_place || $person->pers_death_text || $person->pers_death_cause
            || $person->pers_death_age || $person->pers_death_time
        ) {
            $this->writeLine(1, 'DEAT');
            if ($person->pers_death_date) {
                $this->writeLine(2, 'DATE', $this->process_date($person->pers_death_date));
                if (isset($person->pers_death_date_hebnight) && $person->pers_death_date_hebnight == 'y') {
                    $this->writeLine(2, '_HNIT', 'y');
                }
            }
            if ($person->pers_death_place) {
                $this->buffer .= $this->process_place($person->pers_death_place, 2);
            }
            if ($person->pers_death_time) {
                $this->writeLine(2, 'TIME', $person->pers_death_time);
            }

            if ($_POST['export_type'] == 'normal') {
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_death_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_death_text) {
                    $this->writeNote(2, $person->pers_death_text);
                }
                if ($person->pers_death_cause) {
                    $this->writeLine(2, 'CAUS', $person->pers_death_cause);
                }
                if ($person->pers_death_age) {
                    $this->writeLine(2, 'AGE', $person->pers_death_age);
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
                $this->writeLine(1, 'EVEN');
                $this->writeLine(2, 'TYPE', 'death registration');

                if ($death_declarationDb && $death_declarationDb->event_date) {
                    $this->writeLine(2, 'DATE', $death_declarationDb->event_date);
                }
                if ($death_declarationDb && $death_declarationDb->event_place) {
                    $this->writeLine(2, 'PLAC', $death_declarationDb->event_place);
                }
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'death_decl_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $death_declarationDb && $death_declarationDb->event_text) {
                    $this->writeNote(2, $death_declarationDb->event_text);
                }

                $this->buffer .= $death_decl_witnesses;
            }
        }

        // *** Burial data ***
        if ($person->pers_buried_date || $person->pers_buried_place || $person->pers_buried_text || $person->pers_cremation) {
            // TODO check code
            //if ($person->pers_cremation == 'y') {
            //    $this->writeLine(1, 'CREM');
            //} else {
            $this->writeLine(1, 'BURI');
            //}
            if ($person->pers_buried_date) {
                $this->writeLine(2, 'DATE', $this->process_date($person->pers_buried_date));
                if (isset($person->pers_buried_date_hebnight) && $person->pers_buried_date_hebnight == 'y') {
                    $this->writeLine(2, '_HNIT', 'y');
                }
            }
            if ($person->pers_buried_place) {
                $this->buffer .= $this->process_place($person->pers_buried_place, 2);
            }
            if ($_POST['export_type'] == 'normal') {
                if ($this->gedcom_sources == 'yes') {
                    $this->sources_export('person', 'pers_buried_source', $person->pers_gedcomnumber, 2);
                }
                if ($gedcom_texts == 'yes' && $person->pers_buried_text) {
                    $this->writeNote(2, $person->pers_buried_text);
                }
                if ($person->pers_cremation == '1') {
                    $this->writeLine(2, 'TYPE', 'cremation');
                }
                if ($person->pers_cremation == 'R') {
                    $this->writeLine(2, 'TYPE', 'resomated');
                }
                if ($person->pers_cremation == 'S') {
                    $this->writeLine(2, 'TYPE', "sailor's grave");
                }
                if ($person->pers_cremation == 'D') {
                    $this->writeLine(2, 'TYPE', 'donated to science');
                }

                // *** Remark: only exported if there is another baptism item ***
                $this->buffer .= $this->export_witnesses('BURI', $person->pers_gedcomnumber, 'ASSO');
            }
        }
    }

    private function exportPersonAdditionalData($person, $gedcom_texts): void
    {
        // *** Addresses ***
        $this->addresses_export('person', 'person_address', $person->pers_gedcomnumber);

        // *** Occupation ***
        $professionqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'profession');
        foreach ($professionqry as $professionDb) {
            $this->writeLine(1, 'OCCU', $professionDb->event_event);
            if ($professionDb->event_date) {
                $this->writeLine(2, 'DATE', $this->process_date($professionDb->event_date));
            }
            if ($professionDb->event_place) {
                $this->writeLine(2, 'PLAC', $professionDb->event_place);
            }
            if ($gedcom_texts == 'yes' && $professionDb->event_text) {
                $this->writeNote(2, $professionDb->event_text);
            }
            if ($this->gedcom_sources == 'yes') {
                $this->sources_export('person', 'pers_event_source', $professionDb->event_id, 2);
            }
        }

        // *** Religion events ***
        $religionqry = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'religion');
        foreach ($religionqry as $religionDb) {
            $this->writeLine(1, 'RELI', $religionDb->event_event);
            if ($religionDb->event_date) {
                $this->writeLine(2, 'DATE', $this->process_date($religionDb->event_date));
            }
            if ($religionDb->event_place) {
                $this->writeLine(2, 'PLAC', $religionDb->event_place);
            }
            if ($gedcom_texts == 'yes' && $religionDb->event_text) {
                $this->writeNote(2, $religionDb->event_text);
            }
            if ($this->gedcom_sources == 'yes') {
                $this->sources_export('person', 'pers_event_source', $religionDb->event_id, 2);
            }
        }

        // *** Person source ***
        if ($this->gedcom_sources == 'yes') {
            $this->sources_export('person', 'person_source', $person->pers_gedcomnumber, 1);
        }

        // *** Person pictures ***
        $pictures = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'picture');
        foreach ($pictures as $picture) {
            $this->writeLine(1, 'OBJE');
            $this->writeLine(2, 'FORM', 'jpg');
            $this->writeLine(2, 'FILE', $picture->event_event);
            if ($picture->event_date) {
                $this->writeLine(2, 'DATE', $this->process_date($picture->event_date));
            }
            if ($gedcom_texts == 'yes' && $picture->event_text) {
                $this->writeNote(2, $picture->event_text);
            }
            if ($this->gedcom_sources == 'yes') {
                $this->sources_export('person', 'pers_event_source', $picture->event_id, 2);
            }
        }

        // *** Person Note ***
        if ($gedcom_texts == 'yes' && $person->pers_text) {
            $this->writeNote(1, $person->pers_text);
            $this->sources_export('person', 'pers_text_source', $person->pers_gedcomnumber, 2);
        }

        // *** Person color marks ***
        $colors = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'person_colour_mark');
        foreach ($colors as $color) {
            $this->buffer .= '1 _COLOR ' . $color->event_event . "\r\n";
        }

        // *** Person events ***
        $events = $this->db_functions->get_events_connect('person', $person->pers_gedcomnumber, 'event');
        foreach ($events as $event) {
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

            if (array_key_exists($event->event_gedcom, $eventMapping)) {
                $this->buffer .= $eventMapping[$event->event_gedcom];
                if ($event->event_event) {
                    $this->buffer .= ' ' . $event->event_event;
                }
                $this->buffer .= "\r\n";
                if ($event->event_text) {
                    $this->writeNote(2, $event->event_text);
                }
                if ($event->event_date) {
                    $this->writeLine(2, 'DATE', $this->process_date($event->event_date));
                }
                if ($event->event_place) {
                    $this->writeLine(2, 'PLAC', $event->event_place);
                }
                $this->sources_export('person', 'pers_event_source', $event->event_id, 2);
            }
        }
    }

    private function exportPersonRelations($person, $export): void
    {
        // *** FAMS ***
        $relations = $this->db_functions->get_relations($person->pers_id);
        foreach ($relations as $relation) {
            if ($export["part_tree"] == 'part' && !in_array($relation->relation_gedcomnumber, $this->famsids)) {
                continue;
            }
            $this->writeLine(1, 'FAMS', '@' . $relation->relation_gedcomnumber . '@');
        }

        // *** FAMC ***
        if ($person->parent_relation_gedcomnumber) {
            if ($export["part_tree"] == 'part' && !in_array($person->parent_relation_gedcomnumber, $this->famsids)) {
                // don't export FAMC
            } else {
                $this->writeLine(1, 'FAMC', '@' . $person->parent_relation_gedcomnumber . '@');
            }
        }
    }

    private function exportPersonPrivacy($person): void
    {
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

        // *** Datetime new and changed ***
        $this->buffer .= $this->process_datetime('new', $person->pers_new_datetime, $person->pers_new_user_id);
        $this->buffer .= $this->process_datetime('changed', $person->pers_changed_datetime, $person->pers_changed_user_id);
    }
}
