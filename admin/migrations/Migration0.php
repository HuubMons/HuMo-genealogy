<?php

/**
 * Database updates.
 */

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration0
{
    private $dbh;

    public function __construct($dbh)
    {
        // Initialize the class with the database connection.
        $this->dbh = $dbh;
    }

    public function update_v3_1(): void
    {
        $update_check = false;
        try {
            $update_check = $this->dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        $update_check2 = false;
        try {
            $update_check2 = $this->dbh->query("SELECT * FROM humo_stambomen_tekst LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check || $update_check2) {
            echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            $this->dbh->query("CREATE TABLE humo_tree_texts (
                treetext_id smallint(5) unsigned NOT NULL auto_increment,
                treetext_tree_id smallint(5),
                treetext_language varchar(100) CHARACTER SET utf8,
                treetext_name varchar(100) CHARACTER SET utf8,
                treetext_mainmenu_text text CHARACTER SET utf8,
                treetext_mainmenu_source text CHARACTER SET utf8,
                treetext_family_top text CHARACTER SET utf8,
                treetext_family_footer text CHARACTER SET utf8,
                PRIMARY KEY  (`treetext_id`)
                ) DEFAULT CHARSET=utf8");
            // *** Re-check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }

            $familyTrees = $this->dbh->query("SELECT * FROM humo_stambomen ORDER BY volgorde");
            if ($familyTrees) {
                while ($familyTree = $familyTrees->fetch(PDO::FETCH_OBJ)) {
                    $sql = "INSERT INTO humo_tree_texts SET
                        treetext_tree_id='" . $familyTree->id . "',
                        treetext_language='nl',
                        treetext_name='" . $familyTree->naam . "',
                        treetext_mainmenu_text='" . $familyTree->tekst . "',
                        treetext_mainmenu_source='" . $familyTree->bron . "',
                        treetext_family_top='Familypage'";
                    $this->dbh->query($sql);
                }
            }

            echo '</td></tr>';
        }
    }

    public function update_v4_6(): void
    {
        $update_check_sql = false;
        try {
            $update_check_sql = $this->dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stambomen_tekst RENAME humo_tree_texts';
            $this->dbh->query($sql);

            $sql = 'ALTER TABLE humo_tree_texts
                CHANGE tekst_id treetext_id smallint(5) unsigned NOT NULL auto_increment,
                CHANGE stamboom_id treetext_tree_id smallint(5),
                CHANGE taal treetext_language varchar(100) CHARACTER SET utf8,
                CHANGE stamboom_naam treetext_name varchar(100) CHARACTER SET utf8,
                CHANGE hoofdmenu_tekst treetext_mainmenu_text text CHARACTER SET utf8,
                CHANGE hoofdmenu_bron treetext_mainmenu_source text CHARACTER SET utf8,
                CHANGE gezin_kop treetext_family_top text CHARACTER SET utf8,
                CHANGE gezin_voet treetext_family_footer text CHARACTER SET utf8';
            $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_tree_texts");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }
    }

    public function update_v4_2(): void
    {
        // *** Change names of languages in table humo_tree_texts ***
        $sql = 'UPDATE humo_tree_texts SET treetext_language="nl" WHERE treetext_language="talen/taal-nederlands.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="de" WHERE treetext_language="talen/taal-deutsch.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="en" WHERE treetext_language="talen/taal-english.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="fr" WHERE treetext_language="talen/taal-francais.php"';
        $this->dbh->query($sql);
    }

    public function update_v3_2(): void
    {
        $update_check = false;
        try {
            $update_check = $this->dbh->query("SELECT * FROM humo_stat_date LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        $update_check2 = false;
        try {
            $update_check2 = $this->dbh->query("SELECT * FROM humo_stat_datum LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check || $update_check2) {
            echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            $this->dbh->query("CREATE TABLE humo_stat_date (
            stat_id int(10) NOT NULL auto_increment,
            stat_easy_id varchar(100) CHARACTER SET utf8,
            stat_ip_address varchar(20) CHARACTER SET utf8,
            stat_user_agent varchar(255) CHARACTER SET utf8,
            stat_tree_id varchar(5) CHARACTER SET utf8,
            stat_gedcom_fam varchar(20) CHARACTER SET utf8,
            stat_gedcom_man varchar(20) CHARACTER SET utf8,
            stat_gedcom_woman varchar(20) CHARACTER SET utf8,
            stat_date_stat datetime,
            stat_date_linux varchar(50) CHARACTER SET utf8,
            PRIMARY KEY (`stat_id`)
        ) DEFAULT CHARSET=utf8");
            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_stat_date");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }

        $update_check_sql = false;
        try {
            $update_check_sql = $this->dbh->query("SELECT * FROM humo_stat_date LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';

            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stat_datum RENAME humo_stat_date';
            $update_Db = $this->dbh->query($sql);

            $sql = 'ALTER TABLE humo_stat_date
            CHANGE id stat_id int(10) NOT NULL auto_increment,
            CHANGE samengesteld_id stat_easy_id varchar(100) CHARACTER SET utf8,
            CHANGE ip_adres stat_ip_address varchar(20) CHARACTER SET utf8,
            CHANGE stamboom_id stat_tree_id varchar(5) CHARACTER SET utf8,
            CHANGE gedcom_gezin stat_gedcom_fam varchar(20) CHARACTER SET utf8,
            CHANGE gedcom_man stat_gedcom_man varchar(20) CHARACTER SET utf8,
            CHANGE gedcom_vrouw stat_gedcom_woman varchar(20) CHARACTER SET utf8,
            CHANGE datum_stat stat_date_stat datetime,
            CHANGE datum_linux stat_date_linux varchar(50) CHARACTER SET utf8
            ';
            $update_Db = $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_stat_date");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }

        // *** Automatic installation or update ***
        if (isset($field)) {
            unset($field);
        }
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_stat_date');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }
        if (isset($field['stat_user_agent'])) {
            $sql = "ALTER TABLE humo_stat_date CHANGE stat_user_agent stat_user_agent varchar(255) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } elseif (!isset($field['stat_user_agent'])) {
            $sql = "ALTER TABLE humo_stat_date ADD stat_user_agent VARCHAR(255) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }
    }

    public function update_v4_6_update_2(): void
    {
        $update_check_sql = false;
        try {
            $update_check_sql = $this->dbh->query("SELECT * FROM humo_settings LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check_sql) {
            echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';

            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_instellingen RENAME humo_settings';
            $this->dbh->query($sql);

            $sql = 'ALTER TABLE humo_settings
            CHANGE id setting_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE variabele setting_variable varchar(50) CHARACTER SET utf8,
            CHANGE waarde setting_value text CHARACTER SET utf8';
            $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_settings");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }

        // *** Change default languages in table humo_settings ***
        $sql = 'UPDATE humo_settings SET setting_value="nl" WHERE setting_value="languages/nederlands.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="nl" WHERE setting_value="talen/taal-nederlands.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="en" WHERE setting_value="languages/english.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="en" WHERE setting_value="talen/taal-english.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="de" WHERE setting_value="talen/taal-deutsch.php"';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="fr" WHERE setting_value="talen/taal-francais.php"';
        $this->dbh->query($sql);

        $update_check_sql = false;
        try {
            $update_check_sql = $this->dbh->query("SELECT * FROM humo_trees LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stambomen RENAME humo_trees';
            $this->dbh->query($sql);

            $sql = 'ALTER TABLE humo_trees
            CHANGE id tree_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE volgorde tree_order smallint(5),
            CHANGE voorvoegsel tree_prefix varchar(10) CHARACTER SET utf8,
            CHANGE datum tree_date varchar(20) CHARACTER SET utf8,
            CHANGE personen tree_persons varchar(10) CHARACTER SET utf8,
            CHANGE gezinnen tree_families varchar(10) CHARACTER SET utf8';
            $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_trees");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }


        // *** Automatic installation or update ***
        if (isset($field)) {
            unset($field);
        }
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_trees');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }

        // *** Automatic installation or update ***
        if (isset($field['email'])) {
            $sql = "ALTER TABLE humo_trees CHANGE email tree_email varchar(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } elseif (!isset($field['tree_email'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_email VARCHAR(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['eigenaar'])) {
            $sql = "ALTER TABLE humo_trees CHANGE eigenaar tree_owner varchar(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } elseif (!isset($field['tree_owner'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_owner VARCHAR(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['afbpad'])) {
            $sql = "ALTER TABLE humo_trees CHANGE afbpad tree_pict_path varchar(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } elseif (!isset($field['tree_pict_path'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_pict_path VARCHAR(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['privacy_stamboom'])) {
            $sql = "ALTER TABLE humo_trees CHANGE privacy_stamboom tree_privacy varchar(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } elseif (!isset($field['tree_privacy'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_privacy VARCHAR(100) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }


        echo '<tr><td>Check table humo_user_log</td>';
        $update_check_sql = false;
        try {
            $update_check_sql = $this->dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check_sql) {
            echo '<td style="background-color:#00FF00">OK</td></tr>';
        } else {
            // *** Check if there is an old humo_logboek table ***
            $update_check2_sql = $this->dbh->query("SELECT * FROM humo_logboek LIMIT 0,1");
            if ($update_check2_sql) {
                echo '<td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE humo_logboek RENAME humo_user_log';
                $this->dbh->query($sql);

                $sql = 'ALTER TABLE humo_user_log CHANGE username log_username varchar(25) CHARACTER SET utf8, CHANGE datum log_date varchar(20) CHARACTER SET utf8';
                $this->dbh->query($sql);

                // *** New check ***
                $update_check2 = $this->dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
                if ($update_check2) {
                    echo __('UPDATE OK!');
                } else {
                    echo __('UPDATE FAILED!');
                }
            } else {
                // *** There is no user log table ***
                echo '<td style="background-color:#00FF00">' . __('There is no humo_user_log table');
            }
            echo '</td></tr>';
        }



        // *** Update users ***
        $update_check_sql = $this->dbh->query("SELECT * FROM humo_users LIMIT 0,1");
        $tabel_controleDb = $update_check_sql->fetch(PDO::FETCH_OBJ);
        if (!isset($tabel_controleDb->id)) {
            echo '<tr><td>Check table humo_users</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_users</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';

            $sql = 'ALTER TABLE humo_users
            CHANGE id user_id smallint(5) NOT NULL auto_increment,
            CHANGE username user_name varchar(25) CHARACTER SET utf8,
            CHANGE paswoord user_password varchar(50) CHARACTER SET utf8,
            CHANGE groeps_id user_group_id varchar(1) CHARACTER SET utf8';
            $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_users LIMIT 0,1");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }



        // *** Update groups ***
        $groupsql = "SELECT * FROM humo_groups LIMIT 0,1";
        $groupresult = $this->dbh->query($groupsql);
        $groupDb = $groupresult->fetch(PDO::FETCH_OBJ);

        if (!isset($groupDb->id)) {
            echo '<tr><td>Check table humo_groups</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_groups</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            $sql = 'ALTER TABLE humo_groups
            CHANGE id group_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE groepsnaam group_name varchar(25) CHARACTER SET utf8,
            CHANGE privacy group_privacy varchar(1) CHARACTER SET utf8,
            CHANGE plaatsen group_menu_places varchar(1) CHARACTER SET utf8,
            CHANGE beheer group_admin varchar(1) CHARACTER SET utf8,
            CHANGE bronnen group_sources varchar(1) CHARACTER SET utf8,
            CHANGE afbeeldingen group_pictures varchar(1) CHARACTER SET utf8,
            CHANGE gedcomnummer group_gedcomnr varchar(1) CHARACTER SET utf8,
            CHANGE woonplaats group_living_place varchar(1) CHARACTER SET utf8,
            CHANGE plaats group_places varchar(1) CHARACTER SET utf8,
            CHANGE religie group_religion varchar(1) CHARACTER SET utf8,
            CHANGE plaatsdatum group_place_date varchar(1) CHARACTER SET utf8,
            CHANGE soortindex group_kindindex varchar(1) CHARACTER SET utf8,
            CHANGE gebeurtenis group_event varchar(1) CHARACTER SET utf8,
            CHANGE adressen group_addresses varchar(1) CHARACTER SET utf8,
            CHANGE eigencode group_own_code varchar(1) CHARACTER SET utf8,
            CHANGE werktekst group_work_text varchar(1) CHARACTER SET utf8,
            CHANGE teksten group_texts varchar(1) CHARACTER SET utf8,
            CHANGE tekstpersoon group_text_pers varchar(1) CHARACTER SET utf8,
            CHANGE tekstpersgeg group_texts_pers varchar(1) CHARACTER SET utf8,
            CHANGE tekstgezgeg group_texts_fam varchar(1) CHARACTER SET utf8,
            CHANGE levend group_alive varchar(1) CHARACTER SET utf8,
            CHANGE levenddatum group_alive_date_act varchar(1) CHARACTER SET utf8,
            CHANGE levenddatum2 group_alive_date varchar(4) CHARACTER SET utf8,
            CHANGE filterovl group_filter_death varchar(1) CHARACTER SET utf8,
            CHANGE filtertotaal group_filter_total varchar(1) CHARACTER SET utf8,
            CHANGE filternaam group_filter_name varchar(1) CHARACTER SET utf8,
            CHANGE gezinfilter group_filter_fam varchar(1) CHARACTER SET utf8,
            CHANGE persoonfilter group_filter_pers_show_act varchar(1) CHARACTER SET utf8,
            CHANGE filterkarakter group_filter_pers_show varchar(50) CHARACTER SET utf8,
            CHANGE persoonfilter2 group_filter_pers_hide_act varchar(1) CHARACTER SET utf8,
            CHANGE filterkarakter2 group_filter_pers_hide varchar(50) CHARACTER SET utf8';
            $this->dbh->query($sql);

            // *** New check ***
            $update_check2 = $this->dbh->query("SELECT * FROM humo_groups LIMIT 0,1");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }


        // *** Automatic installation or update ***
        if (isset($field)) {
            unset($field);
        }
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_groups');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }

        // *** Automatic installation or update ***
        if (isset($field['group_statistics'])) {
            $sql = "ALTER TABLE humo_groups CHANGE group_statistics group_statistics varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_statistics'])) {
            $sql = "ALTER TABLE humo_groups ADD group_statistics VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['verjaardagen_rss'])) {
            $sql = "ALTER TABLE humo_groups CHANGE verjaardagen_rss group_birthday_rss varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_birthday_rss'])) {
            $sql = "ALTER TABLE humo_groups ADD group_birthday_rss VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['verjaardagen_lijst'])) {
            $sql = "ALTER TABLE humo_groups CHANGE verjaardagen_lijst group_birthday_list varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_birthday_list'])) {
            $sql = "ALTER TABLE humo_groups ADD group_birthday_list VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['filterdatum'])) {
            $sql = "ALTER TABLE humo_groups CHANGE filterdatum group_filter_date varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_filter_date'])) {
            $sql = "ALTER TABLE humo_groups ADD group_filter_date VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['gen_protection'])) {
            $sql = "ALTER TABLE humo_groups CHANGE gen_protection group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_gen_protection'])) {
            $sql = "ALTER TABLE humo_groups ADD group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['persoonfilter3'])) {
            $sql = "ALTER TABLE humo_groups CHANGE persoonfilter3 group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_pers_hide_totally_act'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['filterkarakter3'])) {
            $sql = "ALTER TABLE humo_groups CHANGE filterkarakter3 group_pers_hide_totally varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_pers_hide_totally'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pers_hide_totally VARCHAR(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['photobook'])) {
            $sql = "ALTER TABLE humo_groups CHANGE photobook group_photobook varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_photobook'])) {
            $sql = "ALTER TABLE humo_groups ADD group_photobook VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $this->dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['hide_trees'])) {
            $sql = "ALTER TABLE humo_groups CHANGE hide_trees group_hide_trees varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT ''";
            $this->dbh->query($sql);
        } elseif (!isset($field['group_hide_trees'])) {
            $sql = "ALTER TABLE humo_groups ADD group_hide_trees VARCHAR( 200 ) NOT NULL DEFAULT '';";
            $this->dbh->query($sql);
        }
    }

    public function update_v4_6_update_3(): void
    {
        // *** Check for update version 4.6 ***
        echo '<tr><td>HuMo-genealogy update V4.6</td><td style="background-color:#00FF00">';

        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees
        WHERE tree_prefix!='LEEG' AND tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';

            $translate_tables = false;

            // *** Rename old tables, rename fields, convert html to utf-8 ***
            $update_check_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "persoon LIMIT 0,1");
            // *** Translate table names, update tables ***
            if ($update_check_sql) {

                $translate_tables = true;

                // *** Convert tables into utf-8 ***
                $get_tables = $this->dbh->query("SHOW TABLES");
                while ($x = $get_tables->fetch()) {
                    if (substr($x[0], 0, strlen($updateDb->tree_prefix)) == $updateDb->tree_prefix) {
                        // *** Change table into UTF-8 ***
                        $update_char = 'ALTER TABLE ' . $x[0] . ' DEFAULT CHARACTER SET utf8';
                        $update_charDb = $this->dbh->query($update_char);
                    }
                }

                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'persoon RENAME ' . $updateDb->tree_prefix . 'person';
                $this->dbh->query($sql);

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'person
                CHANGE id pers_id	mediumint(6) unsigned NOT NULL auto_increment,
                CHANGE gedcomnummer pers_gedcomnumber	varchar(20) CHARACTER SET utf8,
                CHANGE famc pers_famc varchar(50) CHARACTER SET utf8,
                CHANGE fams pers_fams varchar(150) CHARACTER SET utf8,
                CHANGE indexnr pers_indexnr varchar(20) CHARACTER SET utf8,
                CHANGE voornaam pers_firstname varchar(50) CHARACTER SET utf8,
                CHANGE roepnaam pers_callname varchar(50) CHARACTER SET utf8,
                CHANGE voorzetsel pers_prefix varchar(20) CHARACTER SET utf8,
                CHANGE achternaam pers_lastname varchar(50) CHARACTER SET utf8,
                CHANGE patroniem pers_patronym varchar(50) CHARACTER SET utf8,
                CHANGE naamtekst pers_name_text text CHARACTER SET utf8,
                CHANGE naambron pers_name_source text CHARACTER SET utf8,
                CHANGE sexe pers_sexe varchar(1) CHARACTER SET utf8,
                CHANGE eigencode pers_own_code varchar(100) CHARACTER SET utf8,
                CHANGE geboorteplaats pers_birth_place varchar(50) CHARACTER SET utf8,
                CHANGE geboortedatum pers_birth_date varchar(35) CHARACTER SET utf8,
                CHANGE geboortetijd pers_birth_time varchar(25) CHARACTER SET utf8,
                CHANGE geboortetekst pers_birth_text text CHARACTER SET utf8,
                CHANGE geboortebron pers_birth_source text CHARACTER SET utf8,
                CHANGE doopplaats pers_bapt_place varchar(50) CHARACTER SET utf8,
                CHANGE doopdatum pers_bapt_date varchar(35) CHARACTER SET utf8,
                CHANGE dooptekst pers_bapt_text text CHARACTER SET utf8,
                CHANGE doopbron pers_bapt_source text CHARACTER SET utf8,
                CHANGE religie pers_religion varchar(50) CHARACTER SET utf8,
                CHANGE overlijdensplaats pers_death_place varchar(50) CHARACTER SET utf8,
                CHANGE overlijdensdatum pers_death_date varchar(35) CHARACTER SET utf8,
                CHANGE overlijdenstijd pers_death_time varchar(25) CHARACTER SET utf8,
                CHANGE overlijdenstekst pers_death_text text CHARACTER SET utf8,
                CHANGE overlijdensbron pers_death_source text CHARACTER SET utf8,
                CHANGE oorzaak pers_death_cause varchar(50) CHARACTER SET utf8,
                CHANGE begrafenisplaats pers_buried_place varchar(50) CHARACTER SET utf8,
                CHANGE begrafenisdatum pers_buried_date varchar(35) CHARACTER SET utf8,
                CHANGE begrafenistekst pers_buried_text text CHARACTER SET utf8,
                CHANGE begrafenisbron pers_buried_source text CHARACTER SET utf8,
                CHANGE crematie pers_cremation varchar(1) CHARACTER SET utf8,
                CHANGE plaatsindex pers_place_index text CHARACTER SET utf8,
                CHANGE tekst pers_text text CHARACTER SET utf8,
                CHANGE levend pers_alive varchar(20) CHARACTER SET utf8';
                $this->dbh->query($sql);
                //$update.=$sql.'<br>';


                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'gezin RENAME ' . $updateDb->tree_prefix . 'family';
                $this->dbh->query($sql);

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'family
                CHANGE id fam_id mediumint(6) unsigned NOT NULL auto_increment,
                CHANGE gedcomnummer fam_gedcomnumber varchar(20) CHARACTER SET utf8,
                CHANGE man fam_man varchar(20) CHARACTER SET utf8,
                CHANGE vrouw fam_woman varchar(20) CHARACTER SET utf8,
                CHANGE kinderen fam_children text CHARACTER SET utf8,
                CHANGE soort fam_kind varchar(50) CHARACTER SET utf8,
                CHANGE samendatum fam_relation_date varchar(35) CHARACTER SET utf8,
                CHANGE samenplaats fam_relation_place varchar(50) CHARACTER SET utf8,
                CHANGE samentekst fam_relation_text text CHARACTER SET utf8,
                CHANGE samenbron fam_relation_source text CHARACTER SET utf8,
                CHANGE einddatum fam_relation_end_date varchar(35) CHARACTER SET utf8,
                CHANGE ondertrdatum fam_marr_notice_date varchar(35) CHARACTER SET utf8,
                CHANGE ondertrplaats fam_marr_notice_place varchar(50) CHARACTER SET utf8,
                CHANGE ondertrtekst fam_marr_notice_text text CHARACTER SET utf8,
                CHANGE ondertrbron fam_marr_notice_source text CHARACTER SET utf8,
                CHANGE trdatum fam_marr_date varchar(35) CHARACTER SET utf8,
                CHANGE trplaats fam_marr_place varchar(50) CHARACTER SET utf8,
                CHANGE trtekst fam_marr_text text CHARACTER SET utf8,
                CHANGE trbron fam_marr_source text CHARACTER SET utf8,
                CHANGE kerkondertrdatum fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
                CHANGE kerkondertrplaats fam_marr_church_notice_place varchar(50) CHARACTER SET utf8,
                CHANGE kerkondertrtekst fam_marr_church_notice_text text CHARACTER SET utf8,
                CHANGE kerkondertrbron fam_marr_church_notice_source text CHARACTER SET utf8,
                CHANGE kerktrdatum fam_marr_church_date varchar(35) CHARACTER SET utf8,
                CHANGE kerktrplaats fam_marr_church_place varchar(50) CHARACTER SET utf8,
                CHANGE kerktrtekst fam_marr_church_text text CHARACTER SET utf8,
                CHANGE kerktrbron fam_marr_church_source text CHARACTER SET utf8,
                CHANGE religie fam_religion varchar(50) CHARACTER SET utf8,
                CHANGE scheidingsdatum fam_div_date varchar(35) CHARACTER SET utf8,
                CHANGE scheidingsplaats fam_div_place varchar(50) CHARACTER SET utf8,
                CHANGE scheidingstekst fam_div_text text CHARACTER SET utf8,
                CHANGE scheidingsbron fam_div_source text CHARACTER SET utf8,
                CHANGE huwtekst fam_text text CHARACTER SET utf8,
                CHANGE levend fam_alive int(1),
                CHANGE teller fam_counter mediumint(8)';
                $this->dbh->query($sql);
                //$update.=$sql.'<br>';

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'texts
                CHANGE text_gedcomnr text_gedcomnr varchar(20) CHARACTER SET utf8,
                CHANGE text_text text_text text CHARACTER SET utf8,
                CHANGE text_new_date text_new_date varchar(35) CHARACTER SET utf8,
                CHANGE text_new_time text_new_time varchar(25) CHARACTER SET utf8,
                CHANGE text_changed_date text_changed_date varchar(35) CHARACTER SET utf8,
                CHANGE text_changed_time text_changed_time varchar(25) CHARACTER SET utf8';
                $this->dbh->query($sql);
                //$update.=$sql.'<br>';

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'sources
                CHANGE source_gedcomnr source_gedcomnr varchar(20) CHARACTER SET utf8,
                CHANGE source_title source_title text CHARACTER SET utf8,
                CHANGE source_abbr source_abbr varchar(50) CHARACTER SET utf8,
                CHANGE source_date source_date varchar(35) CHARACTER SET utf8,
                CHANGE source_publ source_publ varchar(150) CHARACTER SET utf8,
                CHANGE source_place source_place varchar(50) CHARACTER SET utf8,
                CHANGE source_refn source_refn varchar(50) CHARACTER SET utf8,
                CHANGE source_auth source_auth varchar(50) CHARACTER SET utf8,
                CHANGE source_subj source_subj varchar(50) CHARACTER SET utf8,
                CHANGE source_item source_item varchar(30) CHARACTER SET utf8,
                CHANGE source_kind source_kind varchar(50) CHARACTER SET utf8,
                CHANGE source_text source_text text CHARACTER SET utf8,
                CHANGE source_photo source_photo text CHARACTER SET utf8,
                CHANGE source_repo_name source_repo_name varchar(50) CHARACTER SET utf8,
                CHANGE source_repo_caln source_repo_caln varchar(50) CHARACTER SET utf8,
                CHANGE source_repo_page source_repo_page varchar(50) CHARACTER SET utf8,
                CHANGE source_new_date source_new_date varchar(35) CHARACTER SET utf8,
                CHANGE source_new_time source_new_time varchar(25) CHARACTER SET utf8,
                CHANGE source_changed_date source_changed_date varchar(35) CHARACTER SET utf8,
                CHANGE source_changed_time source_changed_time varchar(25) CHARACTER SET utf8';
                $this->dbh->query($sql);
                //$update.=$sql.'<br>';

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'addresses
                CHANGE address_gedcomnr address_gedcomnr varchar(20) CHARACTER SET utf8,
                CHANGE address_person_id address_person_id varchar(20) CHARACTER SET utf8,
                CHANGE address_family_id address_family_id varchar(20) CHARACTER SET utf8,
                CHANGE address_address address_address text CHARACTER SET utf8,
                CHANGE address_zip address_zip varchar(20) CHARACTER SET utf8,
                CHANGE address_place address_place varchar(50) CHARACTER SET utf8,
                CHANGE address_phone address_phone varchar(20) CHARACTER SET utf8,
                CHANGE address_date address_date varchar(35) CHARACTER SET utf8,
                CHANGE address_source address_source text CHARACTER SET utf8,
                CHANGE address_text address_text text CHARACTER SET utf8,
                CHANGE address_photo address_photo text CHARACTER SET utf8,
                CHANGE address_new_date address_new_date varchar(35) CHARACTER SET utf8,
                CHANGE address_new_time address_new_time varchar(25) CHARACTER SET utf8,
                CHANGE address_changed_date address_changed_date varchar(35) CHARACTER SET utf8,
                CHANGE address_changed_time address_changed_time varchar(25) CHARACTER SET utf8';
                $this->dbh->query($sql);

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'events
                CHANGE event_person_id event_person_id varchar(20) CHARACTER SET utf8,
                CHANGE event_family_id event_family_id varchar(20) CHARACTER SET utf8,
                CHANGE event_kind event_kind varchar(20) CHARACTER SET utf8,
                CHANGE event_event event_event text CHARACTER SET utf8,
                CHANGE event_gedcom event_gedcom varchar(10) CHARACTER SET utf8,
                CHANGE event_date event_date varchar(35) CHARACTER SET utf8,
                CHANGE event_place event_place varchar(50) CHARACTER SET utf8,
                CHANGE event_source event_source text CHARACTER SET utf8,
                CHANGE event_text event_text text CHARACTER SET utf8,
                CHANGE event_new_date event_new_date varchar(35) CHARACTER SET utf8,
                CHANGE event_new_time event_new_time varchar(25) CHARACTER SET utf8,
                CHANGE event_changed_date event_changed_date varchar(35) CHARACTER SET utf8,
                CHANGE event_changed_time event_changed_time varchar(25) CHARACTER SET utf8';
                $this->dbh->query($sql);

                echo ' Tree updated!';
            }



            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $this->dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "person");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //echo '<span>'.$field[$field_value].'</span><br>';
            }

            // *** Automatic installation or update ***
            if (isset($field['voorvoegsel'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE voorvoegsel pers_tree_prefix	varchar(10) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_tree_prefix'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_tree_prefix varchar(10) CHARACTER SET utf8 AFTER pers_gedcomnumber";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_text_source pers_text_source text CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_text_source text CHARACTER SET utf8 AFTER pers_text";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_favorite'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_favorite pers_favorite varchar(1) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_favorite'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_favorite varchar(1) CHARACTER SET utf8 AFTER pers_alive";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_new_date pers_new_date varchar(35) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_new_date varchar(35) CHARACTER SET utf8 AFTER pers_favorite";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_new_time pers_new_time varchar(25) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_new_time varchar(25) CHARACTER SET utf8 AFTER pers_new_date";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_changed_date pers_changed_date varchar(35) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_changed_date varchar(35) CHARACTER SET utf8 AFTER pers_new_time";
                $this->dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_changed_time pers_changed_time varchar(25) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['pers_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_changed_time varchar(25) CHARACTER SET utf8 AFTER pers_changed_time";
                $this->dbh->query($sql);
            }



            // *** HuMo-genealogy 4.7 updates ***
            // *** UPDATE 1: Add pers_stillborn in ALL person tables ***
            if (!isset($field['pers_stillborn'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_stillborn VARCHAR(1) CHARACTER SET utf8  DEFAULT 'n' AFTER pers_birth_source;";
                $this->dbh->query($sql);
            }

            // *** UPDATE 2: remove pers_index_bapt in ALL person tables ***
            if (isset($field['indexdoop'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP indexdoop";
                $this->dbh->query($sql);
            }
            if (isset($field['pers_index_bapt'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP pers_index_bapt";
                $this->dbh->query($sql);
            }

            // *** UPDATE 3: remove pers_index_death in ALL person tables ***
            if (isset($field['indexovl'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP indexovl";
                $this->dbh->query($sql);
            }
            if (isset($field['pers_index_death'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP pers_index_death";
                $this->dbh->query($sql);
            }

            // *** Show number of fields in table ***
            echo ' fields: ' . count($field);

            // Add family items
            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $this->dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "family");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }

            if (isset($field['family_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_text_source fam_text_source text CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_text_source text CHARACTER SET utf8 AFTER fam_text";
                $this->dbh->query($sql);
            }

            if (isset($field['trinstantie'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE trinstantie fam_marr_authority text CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_marr_authority'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_marr_authority text CHARACTER SET utf8 AFTER fam_marr_source";
                $this->dbh->query($sql);
            }

            if (isset($field['scheidingsinstantie'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE scheidingsinstantie fam_div_authority text CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_div_authority'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_div_authority text CHARACTER SET utf8 AFTER fam_div_source";
                $this->dbh->query($sql);
            }

            if (isset($field['family_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_new_date fam_new_date varchar(35) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_new_date varchar(35) CHARACTER SET utf8 AFTER fam_counter";
                $this->dbh->query($sql);
            }

            if (isset($field['family_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_new_time fam_new_time varchar(25) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_new_time varchar(35) CHARACTER SET utf8 AFTER fam_new_date";
                $this->dbh->query($sql);
            }

            if (isset($field['family_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_changed_date fam_changed_date varchar(35) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_changed_date varchar(35) CHARACTER SET utf8 AFTER fam_new_time";
                $this->dbh->query($sql);
            }

            if (isset($field['family_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_changed_time fam_changed_time varchar(25) CHARACTER SET utf8";
                $this->dbh->query($sql);
            } elseif (!isset($field['fam_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_changed_time varchar(35) CHARACTER SET utf8 AFTER fam_changed_date";
                $this->dbh->query($sql);
            }


            // *** CHANGE OF TABLES HERE ***
            if ($translate_tables == true) {
                // *** Update person table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET
                    pers_firstname="' . $read_persDb->pers_firstname . '",
                    pers_callname="' . $read_persDb->pers_callname . '",
                    pers_prefix="' . $read_persDb->pers_prefix . '",
                    pers_lastname="' . $read_persDb->pers_lastname . '",
                    pers_patronym="' . $read_persDb->pers_patronym . '",
                    pers_name_text="' . $read_persDb->pers_name_text . '",
                    pers_name_source="' . $read_persDb->pers_name_source . '",
                    pers_own_code="' . $read_persDb->pers_own_code . '",
                    pers_birth_place="' . $read_persDb->pers_birth_place . '",
                    pers_birth_text="' . $read_persDb->pers_birth_text . '",
                    pers_birth_source="' . $read_persDb->pers_birth_source . '",
                    pers_bapt_place="' . $read_persDb->pers_bapt_place . '",
                    pers_bapt_text="' . $read_persDb->pers_bapt_text . '",
                    pers_bapt_source="' . $read_persDb->pers_bapt_source . '",
                    pers_religion="' . $read_persDb->pers_religion . '",
                    pers_death_place="' . $read_persDb->pers_death_place . '",
                    pers_death_text="' . $read_persDb->pers_death_text . '",
                    pers_death_source="' . $read_persDb->pers_death_source . '",
                    pers_death_cause="' . $read_persDb->pers_death_cause . '",
                    pers_buried_place="' . $read_persDb->pers_buried_place . '",
                    pers_buried_text="' . $read_persDb->pers_buried_text . '",
                    pers_buried_source="' . $read_persDb->pers_buried_source . '",
                    pers_place_index="' . $read_persDb->pers_place_index . '",
                    pers_text="' . $read_persDb->pers_text . '",
                    pers_text_source="' . $read_persDb->pers_text_source . '"
                    WHERE pers_id="' . $read_persDb->pers_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }

                // *** Update family table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'family SET
                    fam_id="' . $read_persDb->fam_id . '",
                    fam_relation_place="' . $read_persDb->fam_relation_place . '",
                    fam_relation_text="' . $read_persDb->fam_relation_text . '",
                    fam_relation_source="' . $read_persDb->fam_relation_source . '",
                    fam_marr_notice_place="' . $read_persDb->fam_marr_notice_place . '",
                    fam_marr_notice_text="' . $read_persDb->fam_marr_notice_text . '",
                    fam_marr_notice_source="' . $read_persDb->fam_marr_notice_source . '",
                    fam_marr_place="' . $read_persDb->fam_marr_place . '",
                    fam_marr_text="' . $read_persDb->fam_marr_text . '",
                    fam_marr_source="' . $read_persDb->fam_marr_source . '",
                    fam_marr_authority="' . $read_persDb->fam_marr_authority . '",
                    fam_marr_church_notice_place="' . $read_persDb->fam_marr_church_notice_place . '",
                    fam_marr_church_notice_text="' . $read_persDb->fam_marr_church_notice_text . '",
                    fam_marr_church_notice_source="' . $read_persDb->fam_marr_church_notice_source . '",
                    fam_marr_church_place="' . $read_persDb->fam_marr_church_place . '",
                    fam_marr_church_text="' . $read_persDb->fam_marr_church_text . '",
                    fam_marr_church_source="' . $read_persDb->fam_marr_church_source . '",
                    fam_religion="' . $read_persDb->fam_religion . '",
                    fam_div_place="' . $read_persDb->fam_div_place . '",
                    fam_div_text="' . $read_persDb->fam_div_text . '",
                    fam_div_source="' . $read_persDb->fam_div_source . '",
                    fam_div_authority="' . $read_persDb->fam_div_authority . '",
                    fam_text="' . $read_persDb->fam_text . '",
                    fam_text_source="' . $read_persDb->fam_text_source . '"
                    WHERE fam_id="' . $read_persDb->fam_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }

                // *** Update text table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "texts");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'texts SET text_text="' . $read_persDb->text_text . '"
                    WHERE text_id="' . $read_persDb->text_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }

                // *** Update source table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "sources");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'sources SET
                    source_title="' . $read_persDb->source_title . '",
                    source_abbr="' . $read_persDb->source_abbr . '",
                    source_publ="' . $read_persDb->source_publ . '",
                    source_place="' . $read_persDb->source_place . '",
                    source_refn="' . $read_persDb->source_refn . '",
                    source_auth="' . $read_persDb->source_auth . '",
                    source_subj="' . $read_persDb->source_subj . '",
                    source_item="' . $read_persDb->source_item . '",
                    source_kind="' . $read_persDb->source_kind . '",
                    source_text="' . $read_persDb->source_text . '",
                    source_repo_name="' . $read_persDb->source_repo_name . '",
                    source_repo_caln="' . $read_persDb->source_repo_caln . '",
                    source_repo_page="' . $read_persDb->source_repo_page . '"
                    WHERE source_id="' . $read_persDb->source_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }

                // *** Update address table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "addresses");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'addresses SET
                    address_address="' . $read_persDb->address_address . '",
                    address_zip="' . $read_persDb->address_zip . '",
                    address_place="' . $read_persDb->address_place . '",
                    address_phone="' . $read_persDb->address_phone . '",
                    address_date="' . $read_persDb->address_date . '",
                    address_source="' . $read_persDb->address_source . '",
                    address_text="' . $read_persDb->address_text . '",
                    address_photo="' . $read_persDb->address_photo . '"
                    WHERE address_id="' . $read_persDb->address_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }

                // *** Update event table (html to utf-8) ***
                $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'events SET
                    event_person_id="' . $read_persDb->event_person_id . '",
                    event_family_id="' . $read_persDb->event_family_id . '",
                    event_kind="' . $read_persDb->event_kind . '",
                    event_event="' . $read_persDb->event_event . '",
                    event_gedcom="' . $read_persDb->event_gedcom . '",
                    event_date="' . $read_persDb->event_date . '",
                    event_place="' . $read_persDb->event_place . '",
                    event_source="' . $read_persDb->event_source . '",
                    event_text="' . $read_persDb->event_text . '"
                    WHERE event_id="' . $read_persDb->event_id . '"';
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    $sql = str_replace("<br>", "", $sql);
                    $this->dbh->query($sql);
                }
            }
        }

        echo '<br>';

        echo '</td></tr>';
    }
}
