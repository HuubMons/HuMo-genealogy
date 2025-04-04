<?php
/*
 * Show witness (birt, baptize, etc. )
 * 
 * Used for:
 * birth witness -> is changed into: birt declaration witness.
 * baptise witness
 * death declaration -> is changed into: death declaration witness.
 * burial witness
 * marriage witness
 * marriage-church witness
 *
 * $event_connect_kind = person/ family.
*/

// **********************************************************************
// * function witness (person gedcomnumber, $event item, database field);
// **********************************************************************
function witness($gedcomnr, $event_kind, $event_connect_kind = 'person')
{
    global $dbh, $db_functions;
    $counter = 0;
    $text = '';

    $text_array = [];

    if ($gedcomnr) {
        $witness_cls = new PersonCls;
        //get_events_connect($event_connect_kind, $event_connect_id, $event_kind)
        $witness_qry = $db_functions->get_events_connect($event_connect_kind, $gedcomnr, $event_kind);

        $last_text = ''; // *** Show texts only once ***
        foreach ($witness_qry as $witnessDb) {
            $counter++;
            if ($counter > 1) {
                $text .= ', ';
            }

            // All roles allowed:
            // CHIL, CLERGY, FATH, FRIEND, GODP, HUSB, MOTH, MULTIPLE, NGHBR, OFFICIATOR, PARENT, SPOU, WIFE, WITN, OTHER.
            if ($witnessDb->event_gedcom == 'WITN' && $last_text != 'witness') {
                $text .= __('witness') . ': ';
                $last_text = 'witness';
            } elseif ($witnessDb->event_gedcom == 'OFFICIATOR') {
                $text .= __('officiator') . ': ';
                $last_text = 'officiator';
            } elseif ($witnessDb->event_gedcom == 'OTHER') {
                $event_event_extra = $witnessDb->event_event_extra;
                // *** Translate texts from Aldfaer ***
                if ($witnessDb->event_event_extra == 'funeral leader') {
                    $event_event_extra = __('funeral leader');
                }
                if ($witnessDb->event_event_extra == 'informant') {
                    $event_event_extra = __('informant');
                }
                $text .= $event_event_extra . ': ';
                $last_text = 'other';
            } elseif ($witnessDb->event_gedcom == 'GODP') {
                $text .= __('godfather') . ': ';
                $last_text = 'godfather';
            } elseif ($witnessDb->event_gedcom == 'CLERGY') {
                $text .= __('clergy') . ': ';
                $last_text = 'clergy';
            } elseif ($last_text != 'witness') {
                // For now, just show witness text in other cases.
                $text .= __('witness') . ': ';
                $last_text = 'witness';
            }

            if ($witnessDb->event_connect_id2) {
                // *** Connected witness ***
                $witness_nameDb = $db_functions->get_person($witnessDb->event_connect_id2);
                $name = $witness_cls->person_name($witness_nameDb);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $witness_cls->person_url2($witness_nameDb->pers_tree_id, $witness_nameDb->pers_famc, $witness_nameDb->pers_fams, $witness_nameDb->pers_gedcomnumber);

                $text .= '<a href="' . $url . '">' . rtrim($name["standard_name"]) . '</a>';
            } else {
                // *** Witness as text ***
                $text .= $witnessDb->event_event;
            }

            /* OLD CODE. There is no need to add a seperate date/ place/ text or source to witnesses.
            // *** Birth declaration witness: no date/ place/ text/ source in use ***
            if ($event_connect_kind != 'birth_declaration' && $event_connect_kind != 'death_declaration') {
                if ($witnessDb->event_date || $witnessDb->event_place) {
                    $text .= ' ' . date_place($witnessDb->event_date, $witnessDb->event_place);
                }

                if ($witnessDb->event_text) {
                    $text .= ' ' . process_text($witnessDb->event_text);
                }
            }
            $text_array['text'] = $text;

            // *** Show sources by witnesses ***
            if ($event_connect_kind == 'person') {
                $source_array = show_sources2($event_connect_kind, "pers_event_source", $witnessDb->event_id);
            } else {
                $source_array = show_sources2($event_connect_kind, "fam_event_source", $witnessDb->event_id);
            }
            if ($source_array) {
                $text_array['source'] = $source_array['text'];
            }
            */

            // *** Nov. 2024: restored text. ***
            if ($witnessDb->event_text) {
                $text .= ' ' . process_text($witnessDb->event_text);
            }
        }
        if ($text) {
            //$text_array['text'] = $text;
            $text_array['text'] = ' (' . $text . ')';
        }
    }

    //if (isset($text_array)) {
    //    return $text_array;
    //} else {
    //    return '';
    //}
    return $text_array;
}

/*
 ****************************************************
 *** Person was witness at (birt, baptize, etc. ) ***
 * Used for:
 * birth witness -> is changed into: birth declaration witness.
 * baptise witness
 * death declaration -> is changed into: death declaration witness.
 * burial witness
 * marriage witness
 * marriage-church witness
*/

// ********************************************************************************
// * function witness_by_events (person gedcomnumber, $event item, database field);
// ********************************************************************************
function witness_by_events($gedcomnr)
{
    global $dbh, $db_functions, $tree_id, $screen_mode;
    global $link_cls, $uri_path;

    $counter = 0;
    $text = '';
    if ($gedcomnr) {
        $witness_cls = new PersonCls;

        /*
        $source_prep = $dbh->prepare("SELECT * FROM humo_events
            WHERE event_tree_id=:event_tree_id
            AND event_connect_id2=:event_connect_id2
            AND (event_kind='birth_decl_witness' OR event_kind='baptism_witness'
                OR event_kind='death_decl_witness' OR event_kind='burial_witness'
                OR event_kind='marriage_witness' OR event_kind='marriage_witness_rel')
            ORDER BY event_kind
        ");
        */

        $source_prep = $dbh->prepare("SELECT * FROM humo_events
            WHERE event_tree_id=:event_tree_id AND event_connect_id2=:event_connect_id2 AND event_kind='ASSO'
            ORDER BY event_kind
        ");
        $source_prep->bindParam(':event_tree_id', $tree_id);
        $source_prep->bindParam(':event_connect_id2', $gedcomnr);
        $source_prep->execute();

        $witness_line = '';
        while ($witnessDb = $source_prep->fetch(PDO::FETCH_OBJ)) {
            if ($counter == 0) {
                if ($screen_mode == "PDF") {
                    $text = "\n" . __('This person was witness at:') . "\n";
                } else {
                    // *** March 2023: added extra empty line before witnes lines ***
                    $text = "<br>\n<br>\n" . __('This person was witness at:') . "<br>\n";
                }
            }
            $counter++;
            //if ($witnessDb->event_kind == 'birth_decl_witness' && $witness_line !== 'birth declaration') {
            if ($witnessDb->event_connect_kind == 'birth_declaration' && $witness_line !== 'birth declaration') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('birth declaration') . ': ';
                $witness_line = 'birth declaration';
                //} elseif ($witnessDb->event_kind == 'baptism_witness' && $witness_line !== 'baptism witness') {
            } elseif ($witnessDb->event_connect_kind == 'CHR' && $witness_line !== 'baptism witness') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('baptism witness') . ': ';
                $witness_line = 'baptism witness';
                //} elseif ($witnessDb->event_kind == 'death_decl_witness' && $witness_line !== 'death declaration') {
            } elseif ($witnessDb->event_connect_kind == 'death_declaration' && $witness_line !== 'death declaration') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('death declaration') . ': ';
                $witness_line = 'death declaration';
                //} elseif ($witnessDb->event_kind == 'burial_witness' && $witness_line !== 'burial_witness') {
            } elseif ($witnessDb->event_connect_kind == 'BURI' && $witness_line !== 'burial_witness') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('burial witness') . ': ';
                $witness_line = 'burial_witness';
                //} elseif ($witnessDb->event_kind == 'marriage_witness' && $witness_line !== 'marriage_witness') {
            } elseif ($witnessDb->event_connect_kind == 'MARR' && $witness_line !== 'marriage_witness') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('marriage witness') . ': ';
                $witness_line = 'marriage_witness';
                //} elseif ($witnessDb->event_kind == 'marriage_witness_rel' && $witness_line !== 'marriage_witness_rel') {
            } elseif ($witnessDb->event_connect_kind == 'MARR_REL' && $witness_line !== 'marriage_witness_rel') {
                if ($witness_line !== '') {
                    $text .= ".<br>\n";
                }
                $text .= __('marriage witness (religious)') . ': ';
                $witness_line = 'marriage_witness_rel';
            } elseif ($counter > 1) {
                $text .= ', ';
            }

            //if ($witnessDb->event_kind == 'marriage_witness' || $witnessDb->event_kind == 'marriage_witness_rel') {
            if ($witnessDb->event_connect_kind == 'MARR' || $witnessDb->event_connect_kind == 'MARR_REL') {
                // *** Connected witness by a family ***
                $fam_db = $db_functions->get_family($witnessDb->event_connect_id, 'man_woman');

                $name_man = __('N.N.');
                if (isset($fam_db->fam_man)) {
                    $witness_nameDb = $db_functions->get_person($fam_db->fam_man);
                    $name_man = $witness_cls->person_name($witness_nameDb);
                }

                $name_woman = __('N.N.');
                if (isset($fam_db->fam_woman)) {
                    $witness_nameDb = $db_functions->get_person($fam_db->fam_woman);
                    $name_woman = $witness_cls->person_name($witness_nameDb);
                }

                $vars['pers_family'] = $witnessDb->event_connect_id;
                $link = $link_cls->get_link($uri_path, 'family', $tree_id, false, $vars);
                $text .= '<a href="' . $link . '">' . rtrim($name_man["standard_name"]) . ' &amp; ' . rtrim($name_woman["standard_name"]) . '</a>';
            } else {
                // *** Connected witness by a person ***
                $witness_nameDb = $db_functions->get_person($witnessDb->event_connect_id);
                $name = $witness_cls->person_name($witness_nameDb);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $witness_cls->person_url2($witness_nameDb->pers_tree_id, $witness_nameDb->pers_famc, $witness_nameDb->pers_fams, $witness_nameDb->pers_gedcomnumber);

                $text .= '<a href="' . $url . '">' . rtrim($name["standard_name"]) . '</a>';
            }

            if ($witnessDb->event_date) {
                $text .= ' ' . date_place($witnessDb->event_date, '');
            } // *** Use date_place function, there is no place here... ***

            //$source_array=show_sources2($event_connect_kind,"pers_event_source",$witnessDb->event_id);
            //if ($source) $text.=$source_array['text'];
        }
    }
    return $text;
}
