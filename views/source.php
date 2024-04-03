<?php

/**
 * Show a single source.
 */

// *** Check user authority ***
if ($user['group_sources'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

global $data; // Prevent VSCode error messages.
?>

<h1><?= __('Source'); ?></h1>

<?php
// *** Check if visitor tries to see restricted sources ***
if ($user['group_show_restricted_source'] == 'n' and $data["sourceDb"]->source_status == 'restricted') exit(__('No valid source number.'));

// *** If an unknown source ID is choosen, exit function ***
if (!isset($data["sourceDb"]->source_id)) exit(__('No valid source number.'));
?>

<table class="humo standard">
    <tr>
        <td>
            <?php
            if ($data["sourceDb"]->source_title) {
                echo '<b>' . __('Title') . ':</b> ' . $data["sourceDb"]->source_title . '<br>';
            }
            if ($data["sourceDb"]->source_date) {
                echo '<b>' . __('Date') . ":</b> " . language_date(strtolower($data["sourceDb"]->source_date)) . "<br>";
            }
            if ($data["sourceDb"]->source_publ) {
                // TODO use a general function to create clickable links.
                $source_publ = $data["sourceDb"]->source_publ;
                // *** Convert all url's in a text to clickable links ***
                $source_publ = preg_replace("#(^|[ \n\r\t])www.([a-z\-0-9]+).([a-z]{2,4})($|[ \n\r\t])#mi", "\\1<a href=\"http://www.\\2.\\3\" target=\"_blank\">www.\\2.\\3</a>\\4", $source_publ);
                //$source_publ = preg_replace("#(^|[ \n\r\t])(((ftp://)|(http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $source_publ);
                $source_publ = preg_replace("#(^|[ \n\r\t])(((http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $source_publ);

                echo '<b>' . __('Publication') . ':</b> ' . $source_publ . '<br>';
            }
            if ($data["sourceDb"]->source_place) {
                echo '<b>' . __('Place') . ':</b> ' . $data["sourceDb"]->source_place . '<br>';
            }
            if ($data["sourceDb"]->source_refn) {
                echo '<b>' . __('Own code') . ':</b> ' . $data["sourceDb"]->source_refn . '<br>';
            }
            if ($data["sourceDb"]->source_auth) {
                echo '<b>' . __('Author') . ':</b> ' . $data["sourceDb"]->source_auth . '<br>';
            }
            if ($data["sourceDb"]->source_subj) {
                echo '<b>' . __('Subject') . ':</b> ' . $data["sourceDb"]->source_subj . '<br>';
            }
            if ($data["sourceDb"]->source_item) {
                echo '<b>' . __('Nr.') . ':</b> ' . $data["sourceDb"]->source_item . '<br>';
            }
            if ($data["sourceDb"]->source_kind) {
                echo '<b>' . __('Kind') . ':</b> ' . $data["sourceDb"]->source_kind . '<br>';
            }
            if ($data["sourceDb"]->source_repo_caln) {
                echo '<b>' . __('Archive') . ':</b> ' . $data["sourceDb"]->source_repo_caln . '<br>';
            }
            if ($data["sourceDb"]->source_repo_page) {
                echo '<b>' . __('Page') . ':</b> ' . $data["sourceDb"]->source_repo_page . '<br>';
            }

            if ($data["sourceDb"]->source_text) {
                echo '</td></tr><tr><td>' . process_text($data["sourceDb"]->source_text);
            }

            // *** Pictures by source ***
            $data["picture_presentation"] = 'show'; // Show pictures in source page.
            $result = show_media('source', $data["sourceDb"]->source_gedcomnr); // *** This function can be found in file: show_picture.php! ***
            echo $result[0];

            // *** Show repository ***
            $repoDb = $db_functions->get_repository($data["sourceDb"]->source_repo_gedcomnr);
            if ($repoDb) {
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <h3><?= __('Repository'); ?></h3>
            <b><?= __('Title'); ?>:</b> <?= $repoDb->repo_name; ?><br>
            <b><?= __('Zip code'); ?>:</b> <?= $repoDb->repo_zip; ?><br>
            <b><?= __('Address'); ?>:</b> <?= $repoDb->repo_address; ?><br>

            <!-- TODO translate date -->
            <?php if ($repoDb->repo_date) { ?>
                <b><?= __('Date'); ?>:</b> <?= $repoDb->repo_date; ?><br>
            <?php } ?>

        <?php
                if ($repoDb->repo_place) {
                    echo '<b>' . __('Place') . ':</b> ' . $repoDb->repo_place . '<br>';
                }
                echo nl2br($repoDb->repo_text);
            }
        ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php
            $person_cls = new person_cls;

            // *** Find person data if source is connected to a family item ***
            // *** This seperate function speeds up the sources page ***
            function person_data($familyDb)
            {
                global $db_functions;
                if ($familyDb->fam_man)
                    $personDb = $db_functions->get_person($familyDb->fam_man);
                else
                    $personDb = $db_functions->get_person($familyDb->fam_woman);
                return $personDb;
            }

            // *** Sources in connect table ***
            foreach ($data["source_connections"] as $connectDb) {
                // *** Person source ***
                if ($connectDb->connect_kind == 'person') {
                    if ($connectDb->connect_sub_kind == 'person_source') {
                        echo __('Source for:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_name_source') {
                        echo __('Source for name:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_birth_source') {
                        echo __('Source for birth:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_bapt_source') {
                        echo __('Source for baptism:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_death_source') {
                        echo __('Source for death:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_buried_source') {
                        echo __('Source for burial:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_text_source') {
                        echo __('Source for text:');
                    }
                    if ($connectDb->connect_sub_kind == 'pers_sexe_source') {
                        echo __('Source for sex:');
                    }

                    if ($connectDb->connect_sub_kind == 'pers_event_source') {
                        // *** Sources by event ***
                        $event_Db = $db_functions->get_event($connectDb->connect_connect_id);
                        // *** Person source ***
                        if (isset($event_Db->event_connect_kind) and $event_Db->event_connect_kind == 'person' and $event_Db->event_connect_id) {
                            $personDb = $db_functions->get_person($event_Db->event_connect_id);
                            $name = $person_cls->person_name($personDb);

                            echo __('Source for:');

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                            $name = $person_cls->person_name($personDb);
                            echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';

                            if ($event_Db->event_event) {
                                echo ' [' . $event_Db->event_event . ']';
                            }
                        }
                    }
                    // *** Show person-address connection ***
                    elseif ($connectDb->connect_sub_kind == 'pers_address_connect_source') {
                        // *** connect_sub_kind=pers_address_source/connect_connect_id=Rxx/connect_source_id=Sxx.
                        // *** connect_sub_kind=person_address/connect_connect_id=Ixx/connect_item_id=Rxx
                        $address_qry = "SELECT * FROM humo_connections WHERE connect_id='" . $connectDb->connect_connect_id . "'";
                        $address_sql = $dbh->query($address_qry);
                        $addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
                        // Show person that has connected address.
                        $personDb = $db_functions->get_person($addressDb->connect_connect_id);
                        $name = $person_cls->person_name($personDb);
                        echo __('Source by address (person):');

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        $name = $person_cls->person_name($personDb);
                        echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    } else {
                        $personDb = $db_functions->get_person($connectDb->connect_connect_id);

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        $name = $person_cls->person_name($personDb);
                        echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }
                }

                // *** Family source ***
                if ($connectDb->connect_kind == 'family') {
                    if ($connectDb->connect_sub_kind == 'family_source') {
                        echo __('Source for family:');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_relation_source') {
                        echo __('Source for cohabitation:');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_marr_notice_source') {
                        echo __('Source for marriage notice:');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_marr_source') {
                        echo __('Source for marriage:');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_marr_church_notice_source') {
                        echo __('Source for marriage notice (church):');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_marr_church_source') {
                        echo __('Source for marriage (church):');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_div_source') {
                        echo __('Source for divorce:');
                    }
                    if ($connectDb->connect_sub_kind == 'fam_text_source') {
                        echo __('Source for family text:');
                    }

                    if ($connectDb->connect_sub_kind == 'fam_event_source') {
                        // *** Sources by event ***
                        $event_Db = $db_functions->get_event($connectDb->connect_connect_id);
                        // *** Family source ***
                        if (isset($event_Db->event_connect_kind) and $event_Db->event_connect_kind == 'family' and $event_Db->event_connect_id) {
                            echo __('Source for family:');
                            $familyDb = $db_functions->get_family($event_Db->event_connect_id);
                            $personDb = person_data($familyDb);

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                            $name = $person_cls->person_name($personDb);
                            echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';

                            if ($event_Db->event_event) {
                                echo ' [' . $event_Db->event_event . ']';
                            }
                        }
                    }
                    // *** Show person-address connection ***
                    elseif ($connectDb->connect_sub_kind == 'fam_address_connect_source') {
                        // *** connect_sub_kind=fam_address_source/connect_connect_id=Rxx/connect_source_id=Sxx.
                        // *** connect_sub_kind=family_address/connect_connect_id=Fxx/connect_item_id=Rxx
                        $address_qry = "SELECT * FROM humo_connections
                            WHERE connect_id='" . $connectDb->connect_connect_id . "'";
                        $address_sql = $dbh->query($address_qry);
                        $addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
                        // Show family that has connected address.
                        echo __('Source by address (family):');
                        $familyDb = $db_functions->get_family($addressDb->connect_connect_id);
                        $personDb = person_data($familyDb);

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        $name = $person_cls->person_name($personDb);
                        echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    } else {
                        $familyDb = $db_functions->get_family($connectDb->connect_connect_id);
                        $personDb = person_data($familyDb);

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        $name = $person_cls->person_name($personDb);
                        echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';


                        //TEST show name of man and woman.
                        /*
                        if ($familyDb->fam_woman) {
                            $personDb = $db_functions->get_person($familyDb->fam_woman);

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                            $name = $person_cls->person_name($personDb);
                            echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
                        }
                        */
                    }
                }

                // *** Source by (shared) address ***
                if ($connectDb->connect_kind == 'address' and $connectDb->connect_sub_kind == 'address_source') {
                    $sql = "SELECT * FROM humo_addresses
                        WHERE address_tree_id='" . $connectDb->connect_tree_id . "'
                        AND address_gedcomnr='" . $connectDb->connect_connect_id . "'";
                    $address_sql = $dbh->query($sql);
                    $addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
                    $text = '';
                    if ($addressDb->address_address) $text .= $addressDb->address_address;
                    if ($addressDb->address_place) $text .= ' ' . $addressDb->address_place;

                    echo __('Source for address:');

                    if ($humo_option["url_rewrite"] == "j") {
                        $url = 'address/' . $tree_id . '/' . $addressDb->address_gedcomnr;
                    } else {
                        $url = 'index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr;
                    }           
                    ?>
                    <a href="<?= $url;?>"><?= $text;?></a>
                    <?php
                }

                // *** Extra source connect information by every source ***
                if ($connectDb->connect_date or $connectDb->connect_place) {
                    echo " " . date_place($connectDb->connect_date, $connectDb->connect_place);
                }
                // *** Source role ***
                if ($connectDb->connect_role) {
                    echo ', <b>' . __('role') . '</b>: ' . $connectDb->connect_role;
                }
                // *** Source page ***
                if ($connectDb->connect_page) {
                    echo ', <b>' . __('page') . '</b>: ' . $connectDb->connect_page;
                }
                echo '<br>';
            }
            ?>
        </td>
    </tr>
</table><br><br>