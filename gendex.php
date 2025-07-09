<?php
header('Content-type: text/plain; charset=iso-8859-1');

// *** Autoload composer classes ***
require __DIR__ . '/vendor/autoload.php';

include_once(__DIR__ . "/include/db_login.php"); //Inloggen database.

// *** Needed for privacy filter ***
$generalSettings = new \Genealogy\Include\GeneralSettings();
$humo_option = $generalSettings->get_humo_option($dbh);

$userSettings = new \Genealogy\Include\UserSettings();
$user = $userSettings->get_user_settings($dbh);

$db_functions = new \Genealogy\Include\DbFunctions($dbh);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();

// *** Database ***
$datasql = $db_functions->get_trees();
foreach ($datasql as $dataDb) {
    // *** Check if family tree is shown or hidden for user group ***
    $hide_tree_array = explode(";", $user['group_hide_trees']);
    if (!in_array($dataDb->tree_id, $hide_tree_array)) {
        $person_qry = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $dataDb->tree_id . "' ORDER BY pers_lastname");
        //GENDEX:
        //person-URL|FAMILYNAME|Firstname /FAMILYNAME/|
        //Birthdate|Birthplace|Deathdate|Deathplace|
        while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
            $privacy = $personPrivacy->get_privacy($personDb);
            // *** Completely filter person ***
            if (
                $user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0
            ) {
                // *** Don't show person ***
            } else {
                $person_url = '';
                if ($personDb->pers_famc) {
                    $person_url = $personDb->pers_famc;
                }
                if ($personDb->pers_fams) {
                    $pers_fams = explode(';', $personDb->pers_fams);
                    $person_url = $pers_fams[0];
                }
                if ($person_url == '') {
                    // *** Person without parents or own family ***	
                    $person_url = '&main_person=' . $personDb->pers_gedcomnumber;
                }
                $text = $person_url . '&database=' . $dataDb->tree_prefix . '|';

                $pers_lastname = mb_strtoupper(str_replace("_", " ", $personDb->pers_prefix), 'iso-8859-1');
                $pers_lastname .= mb_strtoupper($personDb->pers_lastname, 'iso-8859-1');

                $text .= $pers_lastname . '|';
                $text .= $personDb->pers_firstname . ' /' . $pers_lastname . '/|';

                if (!$privacy) {
                    $birth_bapt_date = '';
                    if ($personDb->pers_bapt_date) {
                        $birth_bapt_date = $personDb->pers_bapt_date;
                    }
                    if ($personDb->pers_birth_date) {
                        $birth_bapt_date = $personDb->pers_birth_date;
                    }
                    $text .= $birth_bapt_date . '|';

                    $birth_bapt_place = '';
                    if ($personDb->pers_bapt_place) {
                        $birth_bapt_place = $personDb->pers_bapt_place;
                    }
                    if ($personDb->pers_birth_place) {
                        $birth_bapt_place = $personDb->pers_birth_place;
                    }
                    $text .= $birth_bapt_place . '|';

                    $died_bur_date = '';
                    if ($personDb->pers_death_date) {
                        $died_bur_date = $personDb->pers_death_date;
                    }
                    if ($personDb->pers_buried_date) {
                        $died_bur_date = $personDb->pers_buried_date;
                    }
                    $text .= $died_bur_date . '|';

                    $died_bur_place = '';
                    if ($personDb->pers_death_place) {
                        $died_bur_place = $personDb->pers_death_place;
                    }
                    if ($personDb->pers_buried_place) {
                        $died_bur_place = $personDb->pers_buried_place;
                    }
                    $text .= $died_bur_place . '|';
                } else {
                    $text .= '||||';
                }
                //echo html_entity_decode($text)."\r\n";

                echo $text . "\r\n";
            }
        }
    } // *** End of hidden family tree ***
} // *** End of multiple family trees ***
