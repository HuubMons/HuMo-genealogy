<?php
class UpdateCls
{

    public function update_v3_1(): void
    {
        // ********************************
        // *** HuMo-genealogy update 1 (V3.1) ***
        // ********************************
        global $dbh;
        $update_check = false;
        try {
            $update_check = $dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        $update_check2 = false;
        try {
            $update_check2 = $dbh->query("SELECT * FROM humo_stambomen_tekst LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check || $update_check2) {
            echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_tree_texts</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            $dbh->query("CREATE TABLE humo_tree_texts (
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
            $update_check2 = $dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }

            $datasql = $dbh->query("SELECT * FROM humo_stambomen ORDER BY volgorde");
            if ($datasql) {
                while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                    $sql = "INSERT INTO humo_tree_texts SET
                treetext_tree_id='" . $dataDb->id . "',
                treetext_language='nl',
                treetext_name='" . $dataDb->naam . "',
                treetext_mainmenu_text='" . $dataDb->tekst . "',
                treetext_mainmenu_source='" . $dataDb->bron . "',
                treetext_family_top='Familypage'
                ";
                    $dbh->query($sql);
                }
            }

            echo '</td></tr>';
        }
    }

    public function update_v4_6(): void
    {
        // ********************************
        // *** HuMo-genealogy update 4 (V4.6) ***
        // ********************************
        global $dbh;
        $update_check_sql = false;
        try {
            $update_check_sql = $dbh->query("SELECT * FROM humo_tree_texts LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_tree_texts 2</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stambomen_tekst RENAME humo_tree_texts';
            $dbh->query($sql);

            $sql = 'ALTER TABLE humo_tree_texts
            CHANGE tekst_id treetext_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE stamboom_id treetext_tree_id smallint(5),
            CHANGE taal treetext_language varchar(100) CHARACTER SET utf8,
            CHANGE stamboom_naam treetext_name varchar(100) CHARACTER SET utf8,
            CHANGE hoofdmenu_tekst treetext_mainmenu_text text CHARACTER SET utf8,
            CHANGE hoofdmenu_bron treetext_mainmenu_source text CHARACTER SET utf8,
            CHANGE gezin_kop treetext_family_top text CHARACTER SET utf8,
            CHANGE gezin_voet treetext_family_footer text CHARACTER SET utf8
            ';
            $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_tree_texts");
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
        // *********************************
        // *** HuMo-genealogy update 3 (V 4.2) ***
        // *********************************
        global $dbh;
        // *** Change names of languages in table humo_tree_texts ***
        $sql = 'UPDATE humo_tree_texts SET treetext_language="nl" WHERE treetext_language="talen/taal-nederlands.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="de" WHERE treetext_language="talen/taal-deutsch.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="en" WHERE treetext_language="talen/taal-english.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_tree_texts SET treetext_language="fr" WHERE treetext_language="talen/taal-francais.php"';
        $dbh->query($sql);
    }

    public function update_v3_2(): void
    {
        // ********************************
        // *** HuMo-genealogy update 2 (V3.2) ***
        // ********************************
        global $dbh;

        $update_check = false;
        try {
            $update_check = $dbh->query("SELECT * FROM humo_stat_date LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        $update_check2 = false;
        try {
            $update_check2 = $dbh->query("SELECT * FROM humo_stat_datum LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check || $update_check2) {
            echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_stat_date</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            $db_update = $dbh->query("CREATE TABLE humo_stat_date (
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
            $update_check2 = $dbh->query("SELECT * FROM humo_stat_date");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }

        $update_check_sql = false;
        try {
            $update_check_sql = $dbh->query("SELECT * FROM humo_stat_date LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_stat_date 2</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';

            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stat_datum RENAME humo_stat_date';
            $update_Db = $dbh->query($sql);

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
            $update_Db = $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_stat_date");
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
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_stat_date');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }
        if (isset($field['stat_user_agent'])) {
            $sql = "ALTER TABLE humo_stat_date CHANGE stat_user_agent stat_user_agent varchar(255) CHARACTER SET utf8";
            $dbh->query($sql);
        } elseif (!isset($field['stat_user_agent'])) {
            $sql = "ALTER TABLE humo_stat_date ADD stat_user_agent VARCHAR(255) CHARACTER SET utf8";
            $dbh->query($sql);
        }
    }

    public function update_v4_6_update_2(): void
    {
        // ********************************
        // *** HuMo-genealogy update 4 (V4.6) ***
        // ********************************
        global $dbh;
        $update_check_sql = false;
        try {
            $update_check_sql = $dbh->query("SELECT * FROM humo_settings LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check_sql) {
            echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_settings</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';

            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_instellingen RENAME humo_settings';
            $dbh->query($sql);

            $sql = 'ALTER TABLE humo_settings
            CHANGE id setting_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE variabele setting_variable varchar(50) CHARACTER SET utf8,
            CHANGE waarde setting_value text CHARACTER SET utf8';
            $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_settings");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }

        // *** Change default languages in table humo_settings ***
        $sql = 'UPDATE humo_settings SET setting_value="nl" WHERE setting_value="languages/nederlands.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="nl" WHERE setting_value="talen/taal-nederlands.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="en" WHERE setting_value="languages/english.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="en" WHERE setting_value="talen/taal-english.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="de" WHERE setting_value="talen/taal-deutsch.php"';
        $dbh->query($sql);

        $sql = 'UPDATE humo_settings SET setting_value="fr" WHERE setting_value="talen/taal-francais.php"';
        $dbh->query($sql);

        $update_check_sql = false;
        try {
            $update_check_sql = $dbh->query("SELECT * FROM humo_trees LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($update_check_sql) {
            echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            echo '<tr><td>Check table humo_trees</td><td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
            // *** Translate dutch table name into english ***
            $sql = 'ALTER TABLE humo_stambomen RENAME humo_trees';
            $dbh->query($sql);

            $sql = 'ALTER TABLE humo_trees
            CHANGE id tree_id smallint(5) unsigned NOT NULL auto_increment,
            CHANGE volgorde tree_order smallint(5),
            CHANGE voorvoegsel tree_prefix varchar(10) CHARACTER SET utf8,
            CHANGE datum tree_date varchar(20) CHARACTER SET utf8,
            CHANGE personen tree_persons varchar(10) CHARACTER SET utf8,
            CHANGE gezinnen tree_families varchar(10) CHARACTER SET utf8';
            $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_trees");
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
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_trees');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }

        // *** Automatic installation or update ***
        if (isset($field['email'])) {
            $sql = "ALTER TABLE humo_trees CHANGE email tree_email varchar(100) CHARACTER SET utf8";
            $dbh->query($sql);
        } elseif (!isset($field['tree_email'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_email VARCHAR(100) CHARACTER SET utf8";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['eigenaar'])) {
            $sql = "ALTER TABLE humo_trees CHANGE eigenaar tree_owner varchar(100) CHARACTER SET utf8";
            $dbh->query($sql);
        } elseif (!isset($field['tree_owner'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_owner VARCHAR(100) CHARACTER SET utf8";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['afbpad'])) {
            $sql = "ALTER TABLE humo_trees CHANGE afbpad tree_pict_path varchar(100) CHARACTER SET utf8";
            $dbh->query($sql);
        } elseif (!isset($field['tree_pict_path'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_pict_path VARCHAR(100) CHARACTER SET utf8";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['privacy_stamboom'])) {
            $sql = "ALTER TABLE humo_trees CHANGE privacy_stamboom tree_privacy varchar(100) CHARACTER SET utf8";
            $dbh->query($sql);
        } elseif (!isset($field['tree_privacy'])) {
            $sql = "ALTER TABLE humo_trees ADD tree_privacy VARCHAR(100) CHARACTER SET utf8";
            $dbh->query($sql);
        }


        echo '<tr><td>Check table humo_user_log</td>';
        $update_check_sql = false;
        try {
            $update_check_sql = $dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
        } catch (Exception $e) {
            //
        }

        if ($update_check_sql) {
            echo '<td style="background-color:#00FF00">OK</td></tr>';
        } else {
            // *** Check if there is an old humo_logboek table ***
            $update_check2_sql = $dbh->query("SELECT * FROM humo_logboek LIMIT 0,1");
            if ($update_check2_sql) {
                echo '<td style="background-color:#00FF00">' . __('AUTOMATIC UPDATE PROCESS STARTED!') . '<br>';
                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE humo_logboek RENAME humo_user_log';
                $dbh->query($sql);

                $sql = 'ALTER TABLE humo_user_log CHANGE username log_username varchar(25) CHARACTER SET utf8, CHANGE datum log_date varchar(20) CHARACTER SET utf8';
                $dbh->query($sql);

                // *** New check ***
                $update_check2 = $dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
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
        $update_check_sql = $dbh->query("SELECT * FROM humo_users LIMIT 0,1");
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
            $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_users LIMIT 0,1");
            if ($update_check2) {
                echo __('UPDATE OK!');
            } else {
                echo __('UPDATE FAILED!');
            }
            echo '</td></tr>';
        }



        // *** Update groups ***
        $groupsql = "SELECT * FROM humo_groups LIMIT 0,1";
        $groupresult = $dbh->query($groupsql);
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
            CHANGE filterkarakter2 group_filter_pers_hide varchar(50) CHARACTER SET utf8
        ';
            $update_Db = $dbh->query($sql);

            // *** New check ***
            $update_check2 = $dbh->query("SELECT * FROM humo_groups LIMIT 0,1");
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
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_groups');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
            // *** test line ***
            //print '<span>'.$field[$field_value].'</span><br>';
        }

        // *** Automatic installation or update ***
        if (isset($field['group_statistics'])) {
            $sql = "ALTER TABLE humo_groups CHANGE group_statistics group_statistics varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $dbh->query($sql);
        } elseif (!isset($field['group_statistics'])) {
            $sql = "ALTER TABLE humo_groups ADD group_statistics VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['verjaardagen_rss'])) {
            $sql = "ALTER TABLE humo_groups CHANGE verjaardagen_rss group_birthday_rss varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $dbh->query($sql);
        } elseif (!isset($field['group_birthday_rss'])) {
            $sql = "ALTER TABLE humo_groups ADD group_birthday_rss VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['verjaardagen_lijst'])) {
            $sql = "ALTER TABLE humo_groups CHANGE verjaardagen_lijst group_birthday_list varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j'";
            $dbh->query($sql);
        } elseif (!isset($field['group_birthday_list'])) {
            $sql = "ALTER TABLE humo_groups ADD group_birthday_list VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['filterdatum'])) {
            $sql = "ALTER TABLE humo_groups CHANGE filterdatum group_filter_date varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $dbh->query($sql);
        } elseif (!isset($field['group_filter_date'])) {
            $sql = "ALTER TABLE humo_groups ADD group_filter_date VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['gen_protection'])) {
            $sql = "ALTER TABLE humo_groups CHANGE gen_protection group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $dbh->query($sql);
        } elseif (!isset($field['group_gen_protection'])) {
            $sql = "ALTER TABLE humo_groups ADD group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['persoonfilter3'])) {
            $sql = "ALTER TABLE humo_groups CHANGE persoonfilter3 group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $dbh->query($sql);
        } elseif (!isset($field['group_pers_hide_totally_act'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['filterkarakter3'])) {
            $sql = "ALTER TABLE humo_groups CHANGE filterkarakter3 group_pers_hide_totally varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X'";
            $dbh->query($sql);
        } elseif (!isset($field['group_pers_hide_totally'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pers_hide_totally VARCHAR(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['photobook'])) {
            $sql = "ALTER TABLE humo_groups CHANGE photobook group_photobook varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n'";
            $dbh->query($sql);
        } elseif (!isset($field['group_photobook'])) {
            $sql = "ALTER TABLE humo_groups ADD group_photobook VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n';";
            $dbh->query($sql);
        }

        // *** Automatic installation or update ***
        if (isset($field['hide_trees'])) {
            $sql = "ALTER TABLE humo_groups CHANGE hide_trees group_hide_trees varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT ''";
            $dbh->query($sql);
        } elseif (!isset($field['group_hide_trees'])) {
            $sql = "ALTER TABLE humo_groups ADD group_hide_trees VARCHAR( 200 ) NOT NULL DEFAULT '';";
            $dbh->query($sql);
        }
    }

    public function update_v4_6_update_3(): void
    {
        global $dbh;
        // *** Check for update version 4.6 ***
        echo '<tr><td>HuMo-genealogy update V4.6</td><td style="background-color:#00FF00">';

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees
        WHERE tree_prefix!='LEEG' AND tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';

            $translate_tables = false;

            // *** Rename old tables, rename fields, convert html to utf-8 ***
            $update_check_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "persoon LIMIT 0,1");
            // *** Translate table names, update tables ***
            if ($update_check_sql) {

                $translate_tables = true;

                // *** Convert tables into utf-8 ***
                $get_tables = $dbh->query("SHOW TABLES");
                while ($x = $get_tables->fetch()) {
                    if (substr($x[0], 0, strlen($updateDb->tree_prefix)) == $updateDb->tree_prefix) {
                        // *** Change table into UTF-8 ***
                        $update_char = 'ALTER TABLE ' . $x[0] . ' DEFAULT CHARACTER SET utf8';
                        $update_charDb = $dbh->query($update_char);
                    }
                }

                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'persoon RENAME ' . $updateDb->tree_prefix . 'person';
                $dbh->query($sql);

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
                $dbh->query($sql);
                //$update.=$sql.'<br>';


                // *** Translate dutch table name into english ***
                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'gezin RENAME ' . $updateDb->tree_prefix . 'family';
                $dbh->query($sql);

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
                $dbh->query($sql);
                //$update.=$sql.'<br>';

                $sql = 'ALTER TABLE ' . $updateDb->tree_prefix . 'texts
                CHANGE text_gedcomnr text_gedcomnr varchar(20) CHARACTER SET utf8,
                CHANGE text_text text_text text CHARACTER SET utf8,
                CHANGE text_new_date text_new_date varchar(35) CHARACTER SET utf8,
                CHANGE text_new_time text_new_time varchar(25) CHARACTER SET utf8,
                CHANGE text_changed_date text_changed_date varchar(35) CHARACTER SET utf8,
                CHANGE text_changed_time text_changed_time varchar(25) CHARACTER SET utf8';
                $dbh->query($sql);
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
                $dbh->query($sql);
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
                $dbh->query($sql);

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
                $dbh->query($sql);

                echo ' Tree updated!';
            } // *** End of tabel check ***



            // ******************************
            // *** AUTOMATIC UPDATES HERE ***
            // ******************************
            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "person");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //echo '<span>'.$field[$field_value].'</span><br>';
            }

            // *** Automatic installation or update ***
            if (isset($field['voorvoegsel'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE voorvoegsel pers_tree_prefix	varchar(10) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_tree_prefix'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_tree_prefix varchar(10) CHARACTER SET utf8 AFTER pers_gedcomnumber";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_text_source pers_text_source text CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_text_source text CHARACTER SET utf8 AFTER pers_text";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_favorite'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_favorite pers_favorite varchar(1) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_favorite'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_favorite varchar(1) CHARACTER SET utf8 AFTER pers_alive";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_new_date pers_new_date varchar(35) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_new_date varchar(35) CHARACTER SET utf8 AFTER pers_favorite";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_new_time pers_new_time varchar(25) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_new_time varchar(25) CHARACTER SET utf8 AFTER pers_new_date";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_changed_date pers_changed_date varchar(35) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_changed_date varchar(35) CHARACTER SET utf8 AFTER pers_new_time";
                $dbh->query($sql);
            }

            // *** Automatic installation or update ***
            if (isset($field['person_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person CHANGE person_changed_time pers_changed_time varchar(25) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['pers_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_changed_time varchar(25) CHARACTER SET utf8 AFTER pers_changed_time";
                $dbh->query($sql);
            }



            // *** HuMo-genealogy 4.7 updates ***
            // *** UPDATE 1: Add pers_stillborn in ALL person tables ***
            if (!isset($field['pers_stillborn'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_stillborn VARCHAR(1) CHARACTER SET utf8  DEFAULT 'n' AFTER pers_birth_source;";
                $dbh->query($sql);
            }

            // *** UPDATE 2: remove pers_index_bapt in ALL person tables ***
            if (isset($field['indexdoop'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP indexdoop";
                $dbh->query($sql);
            }
            if (isset($field['pers_index_bapt'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP pers_index_bapt";
                $dbh->query($sql);
            }

            // *** UPDATE 3: remove pers_index_death in ALL person tables ***
            if (isset($field['indexovl'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP indexovl";
                $dbh->query($sql);
            }
            if (isset($field['pers_index_death'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person DROP pers_index_death";
                $dbh->query($sql);
            }

            // *** Show number of fields in table ***
            echo ' fields: ' . count($field);

            // Add family items
            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "family");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }

            if (isset($field['family_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_text_source fam_text_source text CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_text_source'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_text_source text CHARACTER SET utf8 AFTER fam_text";
                $dbh->query($sql);
            }

            if (isset($field['trinstantie'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE trinstantie fam_marr_authority text CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_marr_authority'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_marr_authority text CHARACTER SET utf8 AFTER fam_marr_source";
                $dbh->query($sql);
            }

            if (isset($field['scheidingsinstantie'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE scheidingsinstantie fam_div_authority text CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_div_authority'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_div_authority text CHARACTER SET utf8 AFTER fam_div_source";
                $dbh->query($sql);
            }

            if (isset($field['family_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_new_date fam_new_date varchar(35) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_new_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_new_date varchar(35) CHARACTER SET utf8 AFTER fam_counter";
                $dbh->query($sql);
            }

            if (isset($field['family_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_new_time fam_new_time varchar(25) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_new_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_new_time varchar(35) CHARACTER SET utf8 AFTER fam_new_date";
                $dbh->query($sql);
            }

            if (isset($field['family_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_changed_date fam_changed_date varchar(35) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_changed_date'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_changed_date varchar(35) CHARACTER SET utf8 AFTER fam_new_time";
                $dbh->query($sql);
            }

            if (isset($field['family_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family CHANGE family_changed_time fam_changed_time varchar(25) CHARACTER SET utf8";
                $dbh->query($sql);
            } elseif (!isset($field['fam_changed_time'])) {
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_changed_time varchar(35) CHARACTER SET utf8 AFTER fam_changed_date";
                $dbh->query($sql);
            }


            // *****************************
            // *** CHANGE OF TABLES HERE ***
            // *****************************
            if ($translate_tables == true) {

                // *** Update person table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET
                    pers_firstname="' . safe_text_db($read_persDb->pers_firstname) . '",
                    pers_callname="' . safe_text_db($read_persDb->pers_callname) . '",
                    pers_prefix="' . safe_text_db($read_persDb->pers_prefix) . '",
                    pers_lastname="' . safe_text_db($read_persDb->pers_lastname) . '",
                    pers_patronym="' . safe_text_db($read_persDb->pers_patronym) . '",
                    pers_name_text="' . safe_text_db($read_persDb->pers_name_text) . '",
                    pers_name_source="' . safe_text_db($read_persDb->pers_name_source) . '",
                    pers_own_code="' . safe_text_db($read_persDb->pers_own_code) . '",
                    pers_birth_place="' . safe_text_db($read_persDb->pers_birth_place) . '",
                    pers_birth_text="' . safe_text_db($read_persDb->pers_birth_text) . '",
                    pers_birth_source="' . safe_text_db($read_persDb->pers_birth_source) . '",
                    pers_bapt_place="' . safe_text_db($read_persDb->pers_bapt_place) . '",
                    pers_bapt_text="' . safe_text_db($read_persDb->pers_bapt_text) . '",
                    pers_bapt_source="' . safe_text_db($read_persDb->pers_bapt_source) . '",
                    pers_religion="' . safe_text_db($read_persDb->pers_religion) . '",
                    pers_death_place="' . safe_text_db($read_persDb->pers_death_place) . '",
                    pers_death_text="' . safe_text_db($read_persDb->pers_death_text) . '",
                    pers_death_source="' . safe_text_db($read_persDb->pers_death_source) . '",
                    pers_death_cause="' . safe_text_db($read_persDb->pers_death_cause) . '",
                    pers_buried_place="' . safe_text_db($read_persDb->pers_buried_place) . '",
                    pers_buried_text="' . safe_text_db($read_persDb->pers_buried_text) . '",
                    pers_buried_source="' . safe_text_db($read_persDb->pers_buried_source) . '",
                    pers_place_index="' . safe_text_db($read_persDb->pers_place_index) . '",
                    pers_text="' . safe_text_db($read_persDb->pers_text) . '",
                    pers_text_source="' . safe_text_db($read_persDb->pers_text_source) . '"
                    WHERE pers_id="' . $read_persDb->pers_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    $dbh->query($sql);
                }

                // *** Update family table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'family SET
                    fam_id="' . $read_persDb->fam_id . '",
                    fam_relation_place="' . safe_text_db($read_persDb->fam_relation_place) . '",
                    fam_relation_text="' . safe_text_db($read_persDb->fam_relation_text) . '",
                    fam_relation_source="' . safe_text_db($read_persDb->fam_relation_source) . '",
                    fam_marr_notice_place="' . safe_text_db($read_persDb->fam_marr_notice_place) . '",
                    fam_marr_notice_text="' . safe_text_db($read_persDb->fam_marr_notice_text) . '",
                    fam_marr_notice_source="' . safe_text_db($read_persDb->fam_marr_notice_source) . '",
                    fam_marr_place="' . safe_text_db($read_persDb->fam_marr_place) . '",
                    fam_marr_text="' . safe_text_db($read_persDb->fam_marr_text) . '",
                    fam_marr_source="' . safe_text_db($read_persDb->fam_marr_source) . '",
                    fam_marr_authority="' . safe_text_db($read_persDb->fam_marr_authority) . '",
                    fam_marr_church_notice_place="' . safe_text_db($read_persDb->fam_marr_church_notice_place) . '",
                    fam_marr_church_notice_text="' . safe_text_db($read_persDb->fam_marr_church_notice_text) . '",
                    fam_marr_church_notice_source="' . safe_text_db($read_persDb->fam_marr_church_notice_source) . '",
                    fam_marr_church_place="' . safe_text_db($read_persDb->fam_marr_church_place) . '",
                    fam_marr_church_text="' . safe_text_db($read_persDb->fam_marr_church_text) . '",
                    fam_marr_church_source="' . safe_text_db($read_persDb->fam_marr_church_source) . '",
                    fam_religion="' . safe_text_db($read_persDb->fam_religion) . '",
                    fam_div_place="' . safe_text_db($read_persDb->fam_div_place) . '",
                    fam_div_text="' . safe_text_db($read_persDb->fam_div_text) . '",
                    fam_div_source="' . safe_text_db($read_persDb->fam_div_source) . '",
                    fam_div_authority="' . safe_text_db($read_persDb->fam_div_authority) . '",
                    fam_text="' . safe_text_db($read_persDb->fam_text) . '",
                    fam_text_source="' . safe_text_db($read_persDb->fam_text_source) . '"
                    WHERE fam_id="' . $read_persDb->fam_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    //$update.=$sql.'<br>';
                    $dbh->query($sql);
                }

                // *** Update text table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "texts");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'texts SET text_text="' . safe_text_db($read_persDb->text_text) . '"
                    WHERE text_id="' . $read_persDb->text_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    //$update.=$sql.'<br>';
                    $dbh->query($sql);
                }

                // *** Update source table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "sources");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'sources SET
                    source_title="' . safe_text_db($read_persDb->source_title) . '",
                    source_abbr="' . safe_text_db($read_persDb->source_abbr) . '",
                    source_publ="' . safe_text_db($read_persDb->source_publ) . '",
                    source_place="' . safe_text_db($read_persDb->source_place) . '",
                    source_refn="' . safe_text_db($read_persDb->source_refn) . '",
                    source_auth="' . safe_text_db($read_persDb->source_auth) . '",
                    source_subj="' . safe_text_db($read_persDb->source_subj) . '",
                    source_item="' . safe_text_db($read_persDb->source_item) . '",
                    source_kind="' . safe_text_db($read_persDb->source_kind) . '",
                    source_text="' . safe_text_db($read_persDb->source_text) . '",
                    source_repo_name="' . safe_text_db($read_persDb->source_repo_name) . '",
                    source_repo_caln="' . safe_text_db($read_persDb->source_repo_caln) . '",
                    source_repo_page="' . safe_text_db($read_persDb->source_repo_page) . '"
                    WHERE source_id="' . $read_persDb->source_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    //$update.=$sql.'<br>';
                    $dbh->query($sql);
                }

                // *** Update address table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "addresses");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'addresses SET
                    address_address="' . safe_text_db($read_persDb->address_address) . '",
                    address_zip="' . safe_text_db($read_persDb->address_zip) . '",
                    address_place="' . safe_text_db($read_persDb->address_place) . '",
                    address_phone="' . safe_text_db($read_persDb->address_phone) . '",
                    address_date="' . safe_text_db($read_persDb->address_date) . '",
                    address_source="' . safe_text_db($read_persDb->address_source) . '",
                    address_text="' . safe_text_db($read_persDb->address_text) . '",
                    address_photo="' . safe_text_db($read_persDb->address_photo) . '"
                    WHERE address_id="' . $read_persDb->address_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    //$update.=$sql.'<br>';
                    $dbh->query($sql);
                }

                // *** Update event table (html to utf-8) ***
                $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
                while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'events SET
                    event_person_id="' . safe_text_db($read_persDb->event_person_id) . '",
                    event_family_id="' . safe_text_db($read_persDb->event_family_id) . '",
                    event_kind="' . safe_text_db($read_persDb->event_kind) . '",
                    event_event="' . safe_text_db($read_persDb->event_event) . '",
                    event_gedcom="' . safe_text_db($read_persDb->event_gedcom) . '",
                    event_date="' . safe_text_db($read_persDb->event_date) . '",
                    event_place="' . safe_text_db($read_persDb->event_place) . '",
                    event_source="' . safe_text_db($read_persDb->event_source) . '",
                    event_text="' . safe_text_db($read_persDb->event_text) . '"
                    WHERE event_id="' . $read_persDb->event_id . '"';
                    //$sql = html_entity_decode($sql, ENT_QUOTES, 'UTF-8');
                    $sql = html_entity_decode($sql, ENT_NOQUOTES, 'UTF-8');
                    //$sql = str_replace("<br>\n", "\n", $sql);
                    $sql = str_replace("<br>", "", $sql);
                    //$update.=$sql.'<br>';
                    $dbh->query($sql);
                }
            } // end translate of tables
        } // End of reading family trees ***

        echo '<br>';

        echo '</td></tr>';
        // *** End of update version 4.6 ***
    }

    public function update_v4_7(): void
    {
        // ************************************
        // *** Update procedure version 4.7 ***
        // ************************************

        global $dbh;
        $start_time = time();

        echo '<tr><td>HuMo-genealogy update V4.7</td><td style="background-color:#00FF00">';

        // *** Automatic installation or update ***
        $update_check_sql = $dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
        if ($update_check_sql) {
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $dbh->query("SHOW COLUMNS FROM humo_user_log");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }
            if (!isset($field['log_ip_address'])) {
                $sql = "ALTER TABLE humo_user_log ADD log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT ''";
                $dbh->query($sql);
            }
            if (!isset($field['log_user_admin'])) {
                $sql = "ALTER TABLE humo_user_log ADD log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''";
                $dbh->query($sql);
            }
        }


        // *** Update 'Empty' line if used in tree table ***
        $update_tree_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='LEEG'");
        while ($update_treeDb = $update_tree_sql->fetch(PDO::FETCH_OBJ)) {
            $update_tree_sql2 = "UPDATE humo_trees SET
            tree_prefix='EMPTY',
            tree_persons='EMPTY',
            tree_families='EMPTY',
            tree_email='EMPTY',
            tree_pict_path='EMPTY',
            tree_privacy='EMPTY'
            WHERE tree_id=" . $update_treeDb->tree_id;
            $dbh->query($update_tree_sql2);
        }

        // *** Read all family trees from tree table ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';

            // *** Update tree privacy ***
            $tree_privacy = '';
            if ($updateDb->tree_privacy == 'Standaard') {
                $tree_privacy = 'standard';
            }
            if ($updateDb->tree_privacy == 'personen_filteren') {
                $tree_privacy = 'filter_persons';
            }
            if ($updateDb->tree_privacy == 'personen_weergeven') {
                $tree_privacy = 'show_persons';
            }
            if ($tree_privacy) {
                $sql = "UPDATE humo_trees SET tree_privacy='" . $tree_privacy . "' WHERE tree_id='" . $updateDb->tree_id . "'";
                $dbh->query($sql);
            }

            // *** Update person table ***
            $privacy_sql = $dbh->query("SELECT pers_id, pers_alive FROM " . $updateDb->tree_prefix . "person WHERE pers_alive!=''");
            while ($privacyDb = $privacy_sql->fetch(PDO::FETCH_OBJ)) {
                $pers_alive = $privacyDb->pers_alive;
                if ($privacyDb->pers_alive == 'HZ_levend') {
                    $pers_alive = 'alive';
                }
                if ($privacyDb->pers_alive == 'HZ_ovl') {
                    $pers_alive = 'deceased';
                }
                if ($privacyDb->pers_alive == 'Aldfaer_ovl') {
                    $pers_alive = 'deceased';
                }
                $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET pers_alive="' . $pers_alive . '"
                WHERE pers_id="' . $privacyDb->pers_id . '"';
                $dbh->query($sql);
            }

            // *** Update person table ***
            $pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person WHERE pers_lastname='doodgeboren kind'");
            while ($persDb = $pers_sql->fetch(PDO::FETCH_OBJ)) {
                $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET pers_lastname="N.N", pers_stillborn="y" WHERE pers_id="' . $persDb->pers_id . '"';
                $dbh->query($sql);
            }

            // *** Update event table: translate all event_kind items to english ***
            $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
            while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                if ($read_persDb->event_kind == 'adres') {
                    $event_kind = 'address';
                }
                if ($read_persDb->event_kind == 'afbeelding') {
                    $event_kind = 'picture';
                }
                if ($read_persDb->event_kind == 'begrafenisgetuige') {
                    $event_kind = 'burial_witness';
                }
                if ($read_persDb->event_kind == 'beroep') {
                    $event_kind = 'profession';
                }
                if ($read_persDb->event_kind == 'bron') {
                    $event_kind = 'source';
                }
                if ($read_persDb->event_kind == 'doopgetuige') {
                    $event_kind = 'baptism_witness';
                }
                if ($read_persDb->event_kind == 'gebeurtenis') {
                    $event_kind = 'event';
                }
                if ($read_persDb->event_kind == 'geboorteaangifte') {
                    $event_kind = 'birth_declaration';
                }
                if ($read_persDb->event_kind == 'getuige') {
                    $event_kind = 'witness';
                }
                if ($read_persDb->event_kind == 'heerlijkheid') {
                    $event_kind = 'lordship';
                }
                if ($read_persDb->event_kind == 'kerktrgetuige') {
                    $event_kind = 'marriage_witness_rel';
                }
                if ($read_persDb->event_kind == 'naam') {
                    $event_kind = 'name';
                }
                if ($read_persDb->event_kind == 'predikaat') {
                    $event_kind = 'nobility';
                }
                if ($read_persDb->event_kind == 'overlijdensaangifte') {
                    $event_kind = 'death_declaration';
                }
                if ($read_persDb->event_kind == 'titel') {
                    $event_kind = 'title';
                }
                if ($read_persDb->event_kind == 'trgetuige') {
                    $event_kind = 'marriage_witness';
                }
                if (isset($event_kind)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'events SET event_kind="' . $event_kind . '" WHERE event_id="' . $read_persDb->event_id . '"';
                    $dbh->query($sql);
                }
            }

            echo ' Tree updated!<br>';

            // *** Show processing time ***
            $end_time = time();
            echo $end_time - $start_time . ' ' . __('seconds.') . '<br>';
        }

        // *** Update "update_status" to number 1 ***
        $dbh->query("UPDATE humo_settings SET setting_value='1' WHERE setting_variable='update_status'");
    }

    public function update_v4_8(): void
    {
        // ************************************
        // *** Update procedure version 4.8 ***
        // ************************************

        global $dbh, $updateDb;
        $start_time = time();

        echo '<tr><td>HuMo-genealogy update V4.8</td><td style="background-color:#00FF00">';

        // *** Update sources by persons and families ***
        function update_source($read_dB, $source_value, $connect_kind, $connect_sub_kind, $connect_connect_id)
        {
            global $dbh, $updateDb;
            unset($source_array);
            $source_array = explode(";", $source_value);
            for ($i = 0; $i <= (count($source_array) - 1); $i++) {
                $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET
                connect_order='" . ($i + 1) . "',
                connect_kind='" . $connect_kind . "',
                connect_sub_kind='" . $connect_sub_kind . "',
                connect_connect_id='" . $connect_connect_id . "',";
                // *** Check if old source was a link or a text ***
                if (substr($source_array[$i], 0, 1) === '@') {
                    $gebeurtsql .= " connect_source_id='" . substr($source_array[$i], 1, -1) . "'";
                } else {
                    $gebeurtsql .= " connect_text='" . $source_array[$i] . "'";
                }
                //echo $gebeurtsql.'<br>';
                $dbh->query($gebeurtsql);
            }
        }

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            //ob_start();
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';
            //ob_flush();
            flush();

            // *** Update source table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources
            ADD source_status VARCHAR(10) CHARACTER SET utf8 DEFAULT '' AFTER source_id,
            ADD source_repo_gedcomnr VARCHAR(20) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_page";
            $dbh->query($sql);
            // *** Repositories table ***
            try {
                $tbldb = $dbh->query("DROP TABLE " . $updateDb->tree_prefix . "repositories"); // Remove table.
            } catch (Exception $e) {
                //
            }
            // *** Generate new table ***
            print '<br>' . __('creating repositories...') . '<br>';
            $dbh->query("CREATE TABLE " . $updateDb->tree_prefix . "repositories (
            repo_id mediumint(6) unsigned NOT NULL auto_increment,
            repo_gedcomnr varchar(20) CHARACTER SET utf8,
            repo_name text CHARACTER SET utf8,
            repo_address text CHARACTER SET utf8,
            repo_zip varchar(20) CHARACTER SET utf8,
            repo_place varchar(75) CHARACTER SET utf8,
            repo_phone varchar(20) CHARACTER SET utf8,
            repo_date varchar(35) CHARACTER SET utf8,
            repo_source text CHARACTER SET utf8,
            repo_text text CHARACTER SET utf8,
            repo_photo text CHARACTER SET utf8,
            repo_mail varchar(100) CHARACTER SET utf8,
            repo_url varchar(150) CHARACTER SET utf8,
            repo_new_date varchar(35) CHARACTER SET utf8,
            repo_new_time varchar(25) CHARACTER SET utf8,
            repo_changed_date varchar(35) CHARACTER SET utf8,
            repo_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`repo_id`)) DEFAULT CHARSET=utf8");
            // *** Sources connections table ***
            try {
                $dbh->query("DROP TABLE " . $updateDb->tree_prefix . "connections"); // Remove table.
            } catch (Exception $e) {
                //
            }
            // *** Generate new table ***
            print ' ' . __('creating connections...');
            $dbh->query("CREATE TABLE " . $updateDb->tree_prefix . "connections (
            connect_id mediumint(6) unsigned NOT NULL auto_increment,
            connect_order mediumint(6),
            connect_kind varchar(25) CHARACTER SET utf8,
            connect_sub_kind varchar(30) CHARACTER SET utf8,
            connect_connect_id varchar(20) CHARACTER SET utf8,
            connect_date varchar(35) CHARACTER SET utf8,
            connect_place varchar(75) CHARACTER SET utf8,
            connect_time varchar(25) CHARACTER SET utf8,
            connect_page text CHARACTER SET utf8,
            connect_role varchar(75) CHARACTER SET utf8,
            connect_text text CHARACTER SET utf8,
            connect_source_id varchar(20) CHARACTER SET utf8,
            connect_item_id varchar(20) CHARACTER SET utf8,
            connect_status varchar(10) CHARACTER SET utf8,
            connect_new_date varchar(35) CHARACTER SET utf8,
            connect_new_time varchar(25) CHARACTER SET utf8,
            connect_changed_date varchar(35) CHARACTER SET utf8,
            connect_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`connect_id`),
            KEY (connect_connect_id)
            ) DEFAULT CHARSET=utf8");

            // *** Move shared addresses from event to connect table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $event_qry = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events WHERE event_kind='address'");
            $eventnr = 0;
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET
                    connect_order='" . $eventDb->event_order . "',
                    connect_kind='person',
                    connect_sub_kind='person_address',
                    connect_connect_id='" . $eventDb->event_person_id . "',
                    connect_source_id='',
                    connect_role='" . $eventDb->event_event . "',
                    connect_date='" . $eventDb->event_date . "',
                    connect_item_id='" . substr($eventDb->event_source, 1, -1) . "',
                    connect_text='',
                    connect_new_date='" . $eventDb->event_new_date . "',
                    connect_new_time='" . $eventDb->event_new_time . "',
                    connect_changed_date='" . $eventDb->event_changed_date . "',
                    connect_changed_time='" . $eventDb->event_changed_time . "'
                    ";
                $dbh->query($gebeurtsql);
            }
            // *** Commit data in database ***
            $dbh->commit();

            // *** Remove old addresses from connect table ***
            $sql = "DELETE FROM " . $updateDb->tree_prefix . "events WHERE event_kind='address'";
            $dbh->query($sql);

            // *** Copy shared sources to new connect table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $event_qry = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events WHERE event_source LIKE '_%'
            ORDER BY event_person_id,event_family_id,event_order");
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                unset($source_array);
                $source_array = explode(";", $eventDb->event_source);

                // *** Event by person ***
                if ($eventDb->event_person_id) {
                    $connect_kind = 'person';
                    $connect_sub_kind = 'person_source';
                    $connect_connect_id = $eventDb->event_person_id;
                }
                // *** Event by family ***
                if ($eventDb->event_family_id) {
                    $connect_kind = 'family';
                    $connect_sub_kind = 'family_source';
                    $connect_connect_id = $eventDb->event_family_id;
                }
                // *** Source by event ***
                if ($eventDb->event_kind != 'source') {
                    $connect_sub_kind = 'event_source';
                    $connect_connect_id = $eventDb->event_id;
                }

                for ($i = 0; $i <= (count($source_array) - 1); $i++) {
                    $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET";
                    if ($eventDb->event_kind == 'source') {
                        $gebeurtsql .= " connect_order='" . $eventDb->event_order . "',";
                    } else {
                        $gebeurtsql .= " connect_order='" . ($i + 1) . "',";
                    }

                    $gebeurtsql .= " connect_kind='" . $connect_kind . "',
                    connect_sub_kind='" . $connect_sub_kind . "',
                    connect_connect_id='" . $connect_connect_id . "',";

                    // *** Check if old source was a link or a text ***
                    if (substr($source_array[$i], 0, 1) === '@') {
                        $gebeurtsql .= " connect_source_id='" . substr($source_array[$i], 1, -1) . "'";
                    } else {
                        $gebeurtsql .= " connect_text='" . $source_array[$i] . "'";
                    }

                    if ($eventDb->event_kind == 'source') {
                        $gebeurtsql .= ",
                        connect_role='" . $eventDb->event_event . "',
                        connect_date='" . $eventDb->event_date . "',
                        connect_place='" . $eventDb->event_place . "'";
                    }
                    $dbh->query($gebeurtsql);
                }

                // *** Update old source fields, or remove old source records ***
                if ($eventDb->event_kind != 'source') {
                    $sql = "UPDATE " . $updateDb->tree_prefix . "events SET event_source='SOURCE'
                    WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                } else {
                    $sql = "DELETE FROM " . $updateDb->tree_prefix . "events WHERE event_id='" . $eventDb->event_id . "'";
                    $dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $dbh->commit();


            // *** Update sources in person table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $read_pers_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
            while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                $update = false;
                $sql = "UPDATE " . $updateDb->tree_prefix . "person SET";

                if ($read_persDb->pers_name_source) {
                    update_source($read_persDb, $read_persDb->pers_name_source, 'person', 'pers_name_source', $read_persDb->pers_gedcomnumber);
                    $sql .= " pers_name_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_birth_source) {
                    update_source($read_persDb, $read_persDb->pers_birth_source, 'person', 'pers_birth_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_birth_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_bapt_source) {
                    update_source($read_persDb, $read_persDb->pers_bapt_source, 'person', 'pers_bapt_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_bapt_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_death_source) {
                    update_source($read_persDb, $read_persDb->pers_death_source, 'person', 'pers_death_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_death_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_buried_source) {
                    update_source($read_persDb, $read_persDb->pers_buried_source, 'person', 'pers_buried_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_buried_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_text_source) {
                    update_source($read_persDb, $read_persDb->pers_text_source, 'person', 'pers_text_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_text_source='SOURCE'";
                    $update = true;
                }
                $sql .= " WHERE pers_id='" . $read_persDb->pers_id . "'";

                if ($update == true) {
                    $dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $dbh->commit();


            // *** Update sources in family table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $read_fam_sql = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
            while ($read_famDb = $read_fam_sql->fetch(PDO::FETCH_OBJ)) {
                $update = false;
                $sql = "UPDATE " . $updateDb->tree_prefix . "family SET";

                if ($read_famDb->fam_relation_source) {
                    update_source($read_famDb, $read_famDb->fam_relation_source, 'family', 'fam_relation_source', $read_famDb->fam_gedcomnumber);
                    $sql .= " fam_relation_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_notice_source) {
                    update_source($read_famDb, $read_famDb->fam_marr_notice_source, 'family', 'fam_marr_notice_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_notice_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_source) {
                    update_source($read_famDb, $read_famDb->fam_marr_source, 'family', 'fam_marr_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_church_notice_source) {
                    update_source($read_famDb, $read_famDb->fam_marr_church_notice_source, 'family', 'fam_marr_church_notice_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_church_notice_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_church_source) {
                    update_source($read_famDb, $read_famDb->fam_marr_church_source, 'family', 'fam_marr_church_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_church_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_div_source) {
                    update_source($read_famDb, $read_famDb->fam_div_source, 'family', 'fam_div_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_div_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_text_source) {
                    update_source($read_famDb, $read_famDb->fam_text_source, 'family', 'fam_text_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_text_source='SOURCE'";
                    $update = true;
                }

                $sql .= " WHERE fam_id='" . $read_famDb->fam_id . "'";

                if ($update == true) {
                    $dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $dbh->commit();

            echo '<br>';
        }

        // *** Update "update_status" to number 2 ***
        $dbh->query("UPDATE humo_settings SET setting_value='2' WHERE setting_variable='update_status'");

        // *** Processing time ***
        $end_time = time();
        echo $end_time - $start_time . ' ' . __('seconds.') . '<br>';

        echo '</td></tr>';
    }

    public function update_v4_8_2(): void
    {
        // **************************************
        // *** Update procedure version 4.8.2 ***
        // **************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V4.8.2</td><td style="background-color:#00FF00">';

        try {
            $dbh->query("DROP TABLE humo_cms_menu");
        } catch (Exception $e) {
            //
        }
        print __('creating humo_cms_menu...') . '<br>';
        $dbh->query("CREATE TABLE humo_cms_menu (
        menu_id int(10) NOT NULL AUTO_INCREMENT,
        menu_parent_id int(10) NOT NULL DEFAULT '0',
        menu_order int(5) NOT NULL DEFAULT '0',
        menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
        PRIMARY KEY (`menu_id`)
        ) DEFAULT CHARSET=utf8");
        try {
            $dbh->query("DROP TABLE humo_cms_pages");
        } catch (Exception $e) {
            //
        }
        print __('creating humo_cms_pages...') . '<br>';
        $dbh->query("CREATE TABLE humo_cms_pages (
        page_id int(10) NOT NULL AUTO_INCREMENT,
        page_status varchar(1) CHARACTER SET utf8 DEFAULT '',
        page_menu_id int(10) NOT NULL DEFAULT '0',
        page_order int(10) NOT NULL DEFAULT '0',
        page_counter int(10) NOT NULL DEFAULT '0',
        page_date datetime,
        page_edit_date datetime,
        page_title varchar(50) CHARACTER SET utf8 DEFAULT '',
        page_text longtext CHARACTER SET utf8 DEFAULT '',
        PRIMARY KEY (`page_id`)
        ) DEFAULT CHARSET=utf8");
        // *** Update "update_status" to number 3 ***
        $dbh->query("UPDATE humo_settings SET setting_value='3' WHERE setting_variable='update_status'");

        echo '</td></tr>';
    }

    public function update_v4_8_8(): void
    {
        // **************************************
        // *** Update procedure version 4.8.8 ***
        // **************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V4.8.8</td><td style="background-color:#00FF00">';

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b><br>';

            // *** Update events table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events ADD event_gedcomnr varchar(20) CHARACTER SET utf8 AFTER event_id";
            $dbh->query($sql);

            // *** Update person table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_sexe_source text CHARACTER SET utf8 AFTER pers_sexe";
            $dbh->query($sql);
        }

        // *** Update "update_status" to number 4 ***
        $dbh->query("UPDATE humo_settings SET setting_value='4' WHERE setting_variable='update_status'");

        echo '</td></tr>';
    }

    public function update_v4_8_9(): void
    {
        // ************************************
        // *** Update procedure version 4.8.9 ***
        // ************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V4.8.9</td><td style="background-color:#00FF00">';

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            //ob_start();
            echo '<b>Check ' . $updateDb->tree_prefix . '</b><br>';
            //ob_flush();
            flush();

            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "person");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }

            // *** Batch processing ***
            //$dbh->beginTransaction();

            if (!isset($field['pers_unprocessed_tags'])) {
                // *** Update person table ***
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_unprocessed_tags text CHARACTER SET utf8 AFTER pers_favorite";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_unprocessed_tags text CHARACTER SET utf8 AFTER fam_counter";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "texts ADD text_unprocessed_tags text CHARACTER SET utf8 AFTER text_text";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources ADD source_unprocessed_tags text CHARACTER SET utf8 AFTER source_repo_gedcomnr";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "addresses ADD address_unprocessed_tags text CHARACTER SET utf8 AFTER address_photo";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events ADD event_unprocessed_tags text CHARACTER SET utf8 AFTER event_text";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "connections ADD connect_unprocessed_tags text CHARACTER SET utf8 AFTER connect_status";
                $dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "repositories ADD repo_unprocessed_tags text CHARACTER SET utf8 AFTER repo_url";
                $dbh->query($sql);
            }

            // *** Update tree tables ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER pers_alive";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER fam_alive";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "texts ADD text_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER text_text";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources ADD source_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_gedcomnr";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "addresses ADD address_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER address_photo";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events
                ADD event_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER event_text,
                ADD event_event_extra text CHARACTER SET utf8 AFTER event_event";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "connections ADD connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER connect_status";
            $dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "repositories ADD repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER repo_url";
            $dbh->query($sql);

            // *** Commit data in database ***
            //$dbh->commit();
        }

        $sql = "ALTER TABLE humo_users
        ADD user_mail varchar(100) CHARACTER SET utf8 AFTER user_name,
        ADD user_trees text CHARACTER SET utf8 AFTER user_mail,
        ADD user_remark text CHARACTER SET utf8 AFTER user_trees,
        ADD user_status varchar(1) CHARACTER SET utf8 AFTER user_remark,
        ADD user_register_date varchar(20) CHARACTER SET utf8 AFTER user_group_id,
        ADD user_last_visit varchar(25) CHARACTER SET utf8 AFTER user_register_date";
        $dbh->query($sql);

        // *** Update "update_status" to number 5 ***
        $dbh->query("UPDATE humo_settings SET setting_value='5' WHERE setting_variable='update_status'");
        echo ' Tree updated!';

        echo '</td></tr>';
    }

    public function update_v4_9_1(): void
    {
        // **************************************
        // *** Update procedure version 4.9.1 ***
        // **************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V4.9.1</td><td style="background-color:#00FF00">';
        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            // *** Update family table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD INDEX (fam_man), ADD INDEX (fam_woman)";
            $dbh->query($sql);
        }

        // *** Update "update_status" to number 6 ***
        $dbh->query("UPDATE humo_settings SET setting_value='6' WHERE setting_variable='update_status'");
        echo ' Tree updated!';
        echo '</td></tr>';
    }

    public function update_v5_0(): void
    {
        // ************************************
        // *** Update procedure version 5.0 ***
        // ************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V5.0</td><td style="background-color:#00FF00">';

        // *** Save GEDCOM file name and GEDCOM program in database ***
        // *** Test for existing column, some users already tried a new script including a database update ***
        $result = $dbh->query("SHOW COLUMNS FROM `humo_trees` LIKE 'tree_gedcom'");
        if ($result->rowCount() == 0) {
            // create it
            $dbh->query("ALTER TABLE humo_trees ADD COLUMN tree_gedcom varchar (100)");
        }

        $dbh->query("ALTER TABLE humo_trees ADD COLUMN tree_gedcom_program varchar (100)");

        // *** Bug in table, change user_group_id ***
        $dbh->query("ALTER TABLE humo_users CHANGE user_group_id user_group_id smallint(5)");

        // *** Add new table, for user notes ***
        $dbh->query("CREATE TABLE humo_user_notes (
        note_id smallint(5) unsigned NOT NULL auto_increment,
        note_date varchar(20) CHARACTER SET utf8,
        note_time varchar(25) CHARACTER SET utf8,
        note_user_id smallint(5),
        note_note text CHARACTER SET utf8,
        note_status varchar(10) CHARACTER SET utf8,
        note_tree_prefix varchar(25) CHARACTER SET utf8,
        note_pers_gedcomnumber varchar(20) CHARACTER SET utf8,
        note_fam_gedcomnumber varchar(20) CHARACTER SET utf8,
        note_names text CHARACTER SET utf8,
        PRIMARY KEY  (`note_id`)
        ) DEFAULT CHARSET=utf8");

        // *** Update "update_status" to number 7 ***
        $dbh->query("UPDATE humo_settings SET setting_value='7' WHERE setting_variable='update_status'");

        echo ' Database updated!';
        echo '</td></tr>';
    }

    public function update_v5_1(): void
    {
        // ************************************
        // *** Update procedure version 5.1 ***
        // ************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V5.1</td><td style="background-color:#00FF00">';

        // *** Show update status ***
        //echo __('Update in progress...').' <div id="information" style="display: inline; font-weight:bold;"></div><br>';
        echo __('Update in progress...') . '<br>';

        $sql = "ALTER TABLE humo_settings ADD setting_tree_id smallint(5), ADD setting_order smallint(5)";
        $dbh->query($sql);

        // *** Add ordering numbers by extra links in settings table ***
        $setting_order = 1;
        $update_sql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            $sql = "UPDATE humo_settings SET setting_order='" . $setting_order . "' WHERE setting_id='" . $updateDb->setting_id . "'";
            $dbh->query($sql);
            $setting_order++;
        }

        // *** New table for persons ***
        $tbldbqry = "CREATE TABLE humo_persons (
        pers_id mediumint(7) unsigned NOT NULL auto_increment,
        pers_gedcomnumber varchar(20) CHARACTER SET utf8,
        pers_tree_id mediumint(7),
        pers_tree_prefix varchar(10) CHARACTER SET utf8,
        pers_famc varchar(50) CHARACTER SET utf8,
        pers_fams varchar(150) CHARACTER SET utf8,
        pers_indexnr varchar(20) CHARACTER SET utf8,
        pers_firstname varchar(60) CHARACTER SET utf8,
        pers_callname varchar(50) CHARACTER SET utf8,
        pers_prefix varchar(20) CHARACTER SET utf8,
        pers_lastname varchar(60) CHARACTER SET utf8,
        pers_patronym varchar(50) CHARACTER SET utf8,
        pers_name_text text CHARACTER SET utf8,
        pers_sexe varchar(1) CHARACTER SET utf8,
        pers_own_code varchar(100) CHARACTER SET utf8,
    pers_birth_place varchar(75) CHARACTER SET utf8,
    pers_birth_date varchar(35) CHARACTER SET utf8,
    pers_birth_time varchar(25) CHARACTER SET utf8,
    pers_birth_text text CHARACTER SET utf8,
    pers_stillborn varchar(1) CHARACTER SET utf8 DEFAULT 'n',
    pers_bapt_place varchar(75) CHARACTER SET utf8,
    pers_bapt_date varchar(35) CHARACTER SET utf8,
    pers_bapt_text text CHARACTER SET utf8,
    pers_religion varchar(50) CHARACTER SET utf8,
    pers_death_place varchar(75) CHARACTER SET utf8,
    pers_death_date varchar(35) CHARACTER SET utf8,
    pers_death_time varchar(25) CHARACTER SET utf8,
    pers_death_text text CHARACTER SET utf8,
    pers_death_cause varchar(255) CHARACTER SET utf8,
    pers_death_age varchar(15) CHARACTER SET utf8,
    pers_buried_place varchar(75) CHARACTER SET utf8,
    pers_buried_date varchar(35) CHARACTER SET utf8,
    pers_buried_text text CHARACTER SET utf8,
    pers_cremation varchar(1) CHARACTER SET utf8,
        pers_place_index text CHARACTER SET utf8,
        pers_text text CHARACTER SET utf8,
        pers_alive varchar(20) CHARACTER SET utf8,
        pers_cal_date varchar(35) CHARACTER SET utf8,
        pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
        pers_new_date varchar(35) CHARACTER SET utf8,
        pers_new_time varchar(25) CHARACTER SET utf8,
        pers_changed_date varchar(35) CHARACTER SET utf8,
        pers_changed_time varchar(25) CHARACTER SET utf8,
        PRIMARY KEY (`pers_id`),
        KEY (pers_prefix),
        KEY (pers_lastname),
        KEY (pers_gedcomnumber),
        KEY (pers_tree_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $dbh->query($tbldbqry);

        // *** New table for families ***
        $tbldbqry = "CREATE TABLE humo_families (
        fam_id mediumint(7) unsigned NOT NULL auto_increment,
        fam_tree_id mediumint(7),
        fam_gedcomnumber varchar(20) CHARACTER SET utf8,
        fam_man varchar(20) CHARACTER SET utf8,
        fam_man_age varchar(15) CHARACTER SET utf8,
        fam_woman varchar(20) CHARACTER SET utf8,
        fam_woman_age varchar(15) CHARACTER SET utf8,
        fam_children text CHARACTER SET utf8,
        fam_kind varchar(50) CHARACTER SET utf8,
    fam_relation_date varchar(35) CHARACTER SET utf8,
    fam_relation_place varchar(75) CHARACTER SET utf8,
    fam_relation_text text CHARACTER SET utf8,
    fam_relation_end_date varchar(35) CHARACTER SET utf8,
    fam_marr_notice_date varchar(35) CHARACTER SET utf8,
    fam_marr_notice_place varchar(75) CHARACTER SET utf8,
    fam_marr_notice_text text CHARACTER SET utf8,
    fam_marr_date varchar(35) CHARACTER SET utf8,
    fam_marr_place varchar(75) CHARACTER SET utf8,
    fam_marr_text text CHARACTER SET utf8,
    fam_marr_authority text CHARACTER SET utf8,
    fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
    fam_marr_church_notice_place varchar(75) CHARACTER SET utf8,
    fam_marr_church_notice_text text CHARACTER SET utf8,
    fam_marr_church_date varchar(35) CHARACTER SET utf8,
    fam_marr_church_place varchar(75) CHARACTER SET utf8,
    fam_marr_church_text text CHARACTER SET utf8,
    fam_religion varchar(50) CHARACTER SET utf8,
    fam_div_date varchar(35) CHARACTER SET utf8,
    fam_div_place varchar(75) CHARACTER SET utf8,
    fam_div_text text CHARACTER SET utf8,
    fam_div_authority text CHARACTER SET utf8,
        fam_text text CHARACTER SET utf8,
        fam_alive int(1),
        fam_cal_date varchar(35) CHARACTER SET utf8,
        fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
        fam_counter mediumint(7),
        fam_new_date varchar(35) CHARACTER SET utf8,
        fam_new_time varchar(25) CHARACTER SET utf8,
        fam_changed_date varchar(35) CHARACTER SET utf8,
        fam_changed_time varchar(25) CHARACTER SET utf8,
        PRIMARY KEY (`fam_id`),
        KEY (fam_tree_id),
        KEY (fam_gedcomnumber),
        KEY (fam_man),
        KEY (fam_woman)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $dbh->query($tbldbqry);


        // *** New table for unprocessed tags ***
        $sql = "CREATE TABLE humo_unprocessed_tags (
        tag_id mediumint(6) unsigned NOT NULL auto_increment,
        tag_pers_id mediumint(6),
        tag_rel_id mediumint(6),
        tag_event_id mediumint(6),
        tag_source_id mediumint(6),
        tag_connect_id mediumint(6),
        tag_repo_id mediumint(6),
        tag_place_id mediumint(6),
        tag_address_id mediumint(6),
        tag_text_id mediumint(6),
        tag_tree_id smallint(5),
        tag_tag text CHARACTER SET utf8,
        PRIMARY KEY (tag_id),
        KEY (tag_tree_id),
        KEY (tag_pers_id),
        KEY (tag_rel_id),
        KEY (tag_event_id),
        KEY (tag_source_id),
        KEY (tag_connect_id),
        KEY (tag_repo_id),
        KEY (tag_place_id),
        KEY (tag_address_id),
        KEY (tag_text_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $dbh->query($sql);

        // *** Get tree_id of tree_prefix humo_ ***
        $sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='humo_'");
        $resultDb = $sql->fetch(PDO::FETCH_OBJ);
        $humo_tree_id = $resultDb->tree_id;

        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_repositories'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_repositories (
            repo_id mediumint(6) unsigned NOT NULL auto_increment,
            repo_tree_id smallint(5),
            repo_gedcomnr varchar(20) CHARACTER SET utf8,
            repo_name text CHARACTER SET utf8,
            repo_address text CHARACTER SET utf8,
            repo_zip varchar(20) CHARACTER SET utf8,
            repo_place varchar(75) CHARACTER SET utf8,
            repo_phone varchar(20) CHARACTER SET utf8,
            repo_date varchar(35) CHARACTER SET utf8,
            repo_text text CHARACTER SET utf8,
            repo_photo text CHARACTER SET utf8,
            repo_mail varchar(100) CHARACTER SET utf8,
            repo_url varchar(150) CHARACTER SET utf8,
            repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            repo_unprocessed_tags text CHARACTER SET utf8,
            repo_new_date varchar(35) CHARACTER SET utf8,
            repo_new_time varchar(25) CHARACTER SET utf8,
            repo_changed_date varchar(35) CHARACTER SET utf8,
            repo_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`repo_id`),
            KEY (repo_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            // *** Remove source column from repository table ***
            $qry = "ALTER TABLE humo_repositories DROP repo_source,
            ADD repo_tree_id smallint(5) AFTER repo_id,
            ADD KEY(`repo_tree_id`);";
            $sql_get = $dbh->query($qry);

            // *** Add repo_tree_id value in table ***
            $dbh->query("UPDATE humo_repositories SET repo_tree_id='" . $humo_tree_id . "' WHERE repo_id!=''");
        }


        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_sources'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_sources (
            source_id mediumint(6) unsigned NOT NULL auto_increment,
            source_tree_id smallint(5),
            source_status varchar(10) CHARACTER SET utf8,
            source_gedcomnr varchar(20) CHARACTER SET utf8,
            source_order mediumint(6),
            source_title text CHARACTER SET utf8,
            source_abbr varchar(50) CHARACTER SET utf8,
            source_date varchar(35) CHARACTER SET utf8,
            source_publ varchar(150) CHARACTER SET utf8,
            source_place varchar(75) CHARACTER SET utf8,
            source_refn varchar(50) CHARACTER SET utf8,
            source_auth varchar(50) CHARACTER SET utf8,
            source_subj varchar(50) CHARACTER SET utf8,
            source_item varchar(30) CHARACTER SET utf8,
            source_kind varchar(50) CHARACTER SET utf8,
            source_text text CHARACTER SET utf8,
            source_photo text CHARACTER SET utf8,
            source_repo_name varchar(50) CHARACTER SET utf8,
            source_repo_caln varchar(50) CHARACTER SET utf8,
            source_repo_page varchar(50) CHARACTER SET utf8,
            source_repo_gedcomnr varchar(20) CHARACTER SET utf8,
            source_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            source_unprocessed_tags text CHARACTER SET utf8,
            source_new_date varchar(35) CHARACTER SET utf8,
            source_new_time varchar(25) CHARACTER SET utf8,
            source_changed_date varchar(35) CHARACTER SET utf8,
            source_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`source_id`),
            KEY (source_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            // *** Add primary key ***
            $sql = "ALTER TABLE humo_sources ADD PRIMARY KEY(`source_id`),
            ADD source_tree_id smallint(5) AFTER source_id,
            ADD KEY(`source_tree_id`);";
            $dbh->query($sql);

            // *** Add source_tree_id value in table ***
            $dbh->query("UPDATE humo_sources SET source_tree_id='" . $humo_tree_id . "' WHERE source_id!=''");

            // *** Drop old index ***
            $sql = "ALTER TABLE humo_sources DROP INDEX source_id;";
            $dbh->query($sql);
        }

        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_texts'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_texts (
            text_id mediumint(6) unsigned NOT NULL auto_increment,
            text_tree_id smallint(5),
            text_gedcomnr varchar(20) CHARACTER SET utf8,
            text_text text CHARACTER SET utf8,
            text_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            text_unprocessed_tags text CHARACTER SET utf8,
            text_new_date varchar(35) CHARACTER SET utf8,
            text_new_time varchar(25) CHARACTER SET utf8,
            text_changed_date varchar(35) CHARACTER SET utf8,
            text_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (text_id),
            KEY (text_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            // *** Add primary key ***
            $qry = "ALTER TABLE humo_texts ADD PRIMARY KEY(`text_id`),
            ADD text_tree_id smallint(5) AFTER text_id,
            ADD KEY(`text_tree_id`)";
            $sql_get = $dbh->query($qry);

            // *** Add repo_tree_id value in table ***
            $dbh->query("UPDATE humo_texts SET text_tree_id='" . $humo_tree_id . "' WHERE text_id!=''");

            // *** Drop old index ***
            $qry = "ALTER TABLE humo_texts DROP INDEX text_id;";
            $sql_get = $dbh->query($qry);
        }


        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_connections'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_connections (
            connect_id mediumint(6) unsigned NOT NULL auto_increment,
            connect_tree_id smallint(5),
            connect_order mediumint(6),
            connect_kind varchar(25) CHARACTER SET utf8,
            connect_sub_kind varchar(30) CHARACTER SET utf8,
            connect_connect_id varchar(20) CHARACTER SET utf8,
            connect_date varchar(35) CHARACTER SET utf8,
            connect_place varchar(75) CHARACTER SET utf8,
            connect_time varchar(25) CHARACTER SET utf8,
            connect_page text CHARACTER SET utf8,
            connect_role varchar(75) CHARACTER SET utf8,
            connect_text text CHARACTER SET utf8,
            connect_source_id varchar(20) CHARACTER SET utf8,
            connect_item_id varchar(20) CHARACTER SET utf8,
            connect_status varchar(10) CHARACTER SET utf8,
            connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            connect_unprocessed_tags text CHARACTER SET utf8,
            connect_new_date varchar(35) CHARACTER SET utf8,
            connect_new_time varchar(25) CHARACTER SET utf8,
            connect_changed_date varchar(35) CHARACTER SET utf8,
            connect_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`connect_id`),
            KEY (connect_connect_id),
            KEY (connect_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_connections ADD connect_tree_id smallint(5) AFTER connect_id, ADD KEY(`connect_tree_id`)";
            $dbh->query($sql);

            // *** Add repo_tree_id value in table ***
            $dbh->query("UPDATE humo_connections SET connect_tree_id='" . $humo_tree_id . "' WHERE connect_id!=''");
            $dbh->query("UPDATE humo_connections SET connect_sub_kind='pers_event_source' WHERE connect_kind='person' AND connect_sub_kind='event_source'");
            $dbh->query("UPDATE humo_connections SET connect_sub_kind='fam_event_source' WHERE connect_kind='family' AND connect_sub_kind='event_source'");
            $dbh->query("UPDATE humo_connections SET connect_sub_kind='pers_address_source' WHERE connect_kind='person' AND connect_sub_kind='address_source'");
            $dbh->query("UPDATE humo_connections SET connect_sub_kind='fam_address_source' WHERE connect_kind='family' AND connect_sub_kind='address_source'");
        }

        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_addresses'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_addresses(
            address_id mediumint(6) unsigned NOT NULL auto_increment,
            address_tree_id smallint(5),
            address_gedcomnr varchar(20) CHARACTER SET utf8,
            address_order mediumint(6),
            address_person_id varchar(20) CHARACTER SET utf8,
            address_family_id varchar(20) CHARACTER SET utf8,
            address_address text CHARACTER SET utf8,
            address_zip varchar(20) CHARACTER SET utf8,
            address_place varchar(75) CHARACTER SET utf8,
            address_phone varchar(20) CHARACTER SET utf8,
            address_date varchar(35) CHARACTER SET utf8,
            address_text text CHARACTER SET utf8,
            address_photo text CHARACTER SET utf8,
            address_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            address_unprocessed_tags text CHARACTER SET utf8,
            address_new_date varchar(35) CHARACTER SET utf8,
            address_new_time varchar(25) CHARACTER SET utf8,
            address_changed_date varchar(35) CHARACTER SET utf8,
            address_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`address_id`),
            KEY (address_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_addresses ADD address_tree_id smallint(5) AFTER address_id, ADD KEY(`address_tree_id`)";
            $dbh->query($sql);

            // *** Add key ***
            //$sql = "ALTER TABLE humo_addresses ADD KEY(`address_tree_id`);";
            //$result=$dbh->query($sql);

            // *** Add address_tree_id value in table ***
            $dbh->query("UPDATE humo_addresses SET address_tree_id='" . $humo_tree_id . "' WHERE address_id!=''");

            // *** Remove source columns from addresses table ***
            $qry = "ALTER TABLE humo_addresses DROP address_source;";
            $sql_get = $dbh->query($qry);
        }

        // *** Check if table exists already if not create it ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_events'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_events (
            event_id mediumint(6) unsigned NOT NULL auto_increment,
            event_tree_id smallint(5),
            event_gedcomnr varchar(20) CHARACTER SET utf8,
            event_order mediumint(6),
            event_person_id varchar(20) CHARACTER SET utf8,
            event_pers_age varchar(15) CHARACTER SET utf8,
            event_family_id varchar(20) CHARACTER SET utf8,
            event_kind varchar(20) CHARACTER SET utf8,
            event_event text CHARACTER SET utf8,
            event_event_extra text CHARACTER SET utf8,
            event_gedcom varchar(10) CHARACTER SET utf8,
            event_date varchar(35) CHARACTER SET utf8,
            event_place varchar(75) CHARACTER SET utf8,
            event_text text CHARACTER SET utf8,
            event_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            event_unprocessed_tags text CHARACTER SET utf8,
            event_new_date varchar(35) CHARACTER SET utf8,
            event_new_time varchar(25) CHARACTER SET utf8,
            event_changed_date varchar(35) CHARACTER SET utf8,
            event_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`event_id`),
            KEY (event_tree_id),
            KEY (event_person_id),
            KEY (event_family_id),
            KEY (event_kind)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_events ADD event_tree_id smallint(5) AFTER event_id,
            ADD event_pers_age varchar(15) CHARACTER SET utf8 AFTER event_person_id,
            ADD KEY(`event_tree_id`)";
            $dbh->query($sql);

            // *** Add event_tree_id value in table ***
            $dbh->query("UPDATE humo_events SET event_tree_id='" . $humo_tree_id . "' WHERE event_id!=''");

            // *** Remove source columns from event table ***
            $qry = "ALTER TABLE humo_events DROP event_source;";
            $sql_get = $dbh->query($qry);
        }

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {

            // *** Show status of database update ***
            //echo '<script>';
            //	echo 'document.getElementById("information").innerHTML="'.__('Update tree:').' '.$updateDb->tree_id.'";';
            //echo '</script>';
            //ob_start();
            echo __('Update tree:') . ' ' . $updateDb->tree_id . '<br>';
            //ob_flush();
            flush();

            // *** Copy items from humo[nr]_person to humo_persons table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
            while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                $sql_put = "INSERT INTO humo_persons SET
                pers_gedcomnumber='" . $getDb->pers_gedcomnumber . "',
                pers_tree_id='" . $updateDb->tree_id . "',
                pers_tree_prefix='" . $getDb->pers_tree_prefix . "',
                pers_famc='" . $getDb->pers_famc . "',
                pers_fams='" . $getDb->pers_fams . "',
                pers_indexnr='" . $getDb->pers_indexnr . "',
                pers_firstname='" . safe_text_db($getDb->pers_firstname) . "',
                pers_callname='" . safe_text_db($getDb->pers_callname) . "',
                pers_prefix='" . safe_text_db($getDb->pers_prefix) . "',
                pers_lastname='" . safe_text_db($getDb->pers_lastname) . "',
                pers_patronym='" . safe_text_db($getDb->pers_patronym) . "',
                pers_name_text='" . safe_text_db($getDb->pers_name_text) . "',
                pers_sexe='" . $getDb->pers_sexe . "',
                pers_own_code='" . safe_text_db($getDb->pers_own_code) . "',
            pers_birth_place='" . safe_text_db($getDb->pers_birth_place) . "',
            pers_birth_date='" . $getDb->pers_birth_date . "',
            pers_birth_time='" . $getDb->pers_birth_time . "',
            pers_birth_text='" . safe_text_db($getDb->pers_birth_text) . "',
            pers_stillborn='" . $getDb->pers_stillborn . "',
            pers_bapt_place='" . safe_text_db($getDb->pers_bapt_place) . "',
            pers_bapt_date='" . $getDb->pers_bapt_date . "',
            pers_bapt_text='" . safe_text_db($getDb->pers_bapt_text) . "',
            pers_religion='" . $getDb->pers_religion . "',
            pers_death_place='" . safe_text_db($getDb->pers_death_place) . "',
            pers_death_date='" . $getDb->pers_death_date . "',
            pers_death_time='" . $getDb->pers_death_time . "',
            pers_death_text='" . safe_text_db($getDb->pers_death_text) . "',
            pers_death_cause='" . safe_text_db($getDb->pers_death_cause) . "',
            pers_buried_place='" . safe_text_db($getDb->pers_buried_place) . "',
            pers_buried_date='" . $getDb->pers_buried_date . "',
            pers_buried_text='" . safe_text_db($getDb->pers_buried_text) . "',
            pers_cremation='" . $getDb->pers_cremation . "',
                pers_place_index='" . safe_text_db($getDb->pers_place_index) . "',
                pers_text='" . safe_text_db($getDb->pers_text) . "',
                pers_alive='" . $getDb->pers_alive . "',
                pers_quality='" . $getDb->pers_quality . "',
                pers_new_date='" . $getDb->pers_new_date . "',
                pers_new_time='" . $getDb->pers_new_time . "',
                pers_changed_date='" . $getDb->pers_changed_date . "',
                pers_changed_time='" . $getDb->pers_changed_time . "'";
                $dbh->query($sql_put);
                //echo $sql_put.'<br>';
                $pers_id = $dbh->lastInsertId();

                if ($getDb->pers_unprocessed_tags) {
                    $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                        tag_pers_id='" . $pers_id . "',
                        tag_tree_id='" . $updateDb->tree_id . "',
                        tag_tag='" . safe_text_db($getDb->pers_unprocessed_tags) . "'";
                    $dbh->query($gebeurtsql);
                }
            }

            // *** Commit data in database ***
            $dbh->commit();

            // *** Remove old humo[nr]_repositories table ***
            $qry = "DROP TABLE " . $updateDb->tree_prefix . "person;";
            $dbh->query($qry);


            // *** Copy items from humo[nr]_family to humo_families table ***
            // *** Batch processing ***
            $dbh->beginTransaction();
            $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
            while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                $sql_put = "INSERT INTO humo_families SET
                fam_gedcomnumber='" . $getDb->fam_gedcomnumber . "',
                fam_tree_id='" . $updateDb->tree_id . "',
                fam_man='" . $getDb->fam_man . "',
                fam_woman='" . $getDb->fam_woman . "',
                fam_children='" . $getDb->fam_children . "',
                fam_kind='" . $getDb->fam_kind . "',
            fam_relation_date='" . $getDb->fam_relation_date . "',
            fam_relation_place='" . safe_text_db($getDb->fam_relation_place) . "',
            fam_relation_text='" . safe_text_db($getDb->fam_relation_text) . "',
            fam_relation_end_date='" . $getDb->fam_relation_end_date . "',
            fam_marr_notice_date='" . $getDb->fam_marr_notice_date . "',
            fam_marr_notice_place='" . safe_text_db($getDb->fam_marr_notice_place) . "',
            fam_marr_notice_text='" . safe_text_db($getDb->fam_marr_notice_text) . "',
            fam_marr_date='" . $getDb->fam_marr_date . "',
            fam_marr_place='" . safe_text_db($getDb->fam_marr_place) . "',
            fam_marr_text='" . safe_text_db($getDb->fam_marr_text) . "',
            fam_marr_authority='" . safe_text_db($getDb->fam_marr_authority) . "',
            fam_marr_church_notice_date='" . $getDb->fam_marr_church_notice_date . "',
            fam_marr_church_notice_place='" . safe_text_db($getDb->fam_marr_church_notice_place) . "',
            fam_marr_church_notice_text='" . safe_text_db($getDb->fam_marr_church_notice_text) . "',
            fam_marr_church_date='" . $getDb->fam_marr_church_date . "',
            fam_marr_church_place='" . safe_text_db($getDb->fam_marr_church_place) . "',
            fam_marr_church_text='" . safe_text_db($getDb->fam_marr_church_text) . "',
            fam_religion='" . safe_text_db($getDb->fam_religion) . "',
            fam_div_date='" . $getDb->fam_div_date . "',
            fam_div_place='" . safe_text_db($getDb->fam_div_place) . "',
            fam_div_text='" . safe_text_db($getDb->fam_div_text) . "',
            fam_div_authority='" . safe_text_db($getDb->fam_div_authority) . "',
                fam_text='" . safe_text_db($getDb->fam_text) . "',
                fam_alive='" . $getDb->fam_alive . "',
                fam_quality='" . $getDb->fam_quality . "',
                fam_counter='" . $getDb->fam_counter . "',
                fam_new_date='" . $getDb->fam_new_date . "',
                fam_new_time='" . $getDb->fam_new_time . "',
                fam_changed_date='" . $getDb->fam_changed_date . "',
                fam_changed_time='" . $getDb->fam_changed_time . "'";
                $dbh->query($sql_put);
                //echo $sql_put.'<br>';

                $fam_id = $dbh->lastInsertId();

                if ($getDb->fam_unprocessed_tags) {
                    $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                        tag_rel_id='" . $fam_id . "',
                        tag_tree_id='" . $updateDb->tree_id . "',
                        tag_tag='" . safe_text_db($getDb->fam_unprocessed_tags) . "'";
                    $dbh->query($gebeurtsql);
                }
            }

            // *** Commit data in database ***
            $dbh->commit();

            // *** Remove old humo[nr]_repositories table ***
            $qry = "DROP TABLE " . $updateDb->tree_prefix . "family;";
            $dbh->query($qry);

            // *** Change @N1@ into N1 reference ***
            $qry = "UPDATE " . $updateDb->tree_prefix . "texts SET text_gedcomnr=REPLACE(text_gedcomnr, '@', '')";
            $sql_get = $dbh->query($qry);

            // *** Combine multiple humo[nr]_repositories tables into 1 humo_repositories table ***
            if ($updateDb->tree_prefix != 'humo_') {
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "repositories");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = "INSERT INTO humo_repositories SET
                repo_gedcomnr='" . $getDb->repo_gedcomnr . "',
                repo_tree_id='" . $updateDb->tree_id . "',
                repo_name='" . safe_text_db($getDb->repo_name) . "',
                repo_address='" . safe_text_db($getDb->repo_address) . "',
                repo_zip='" . $getDb->repo_zip . "',
                repo_place='" . safe_text_db($getDb->repo_place) . "',
                repo_phone='" . $getDb->repo_phone . "',
                repo_date='" . $getDb->repo_date . "',
                repo_text='" . safe_text_db($getDb->repo_text) . "',
                repo_photo='" . safe_text_db($getDb->repo_photo) . "',
                repo_mail='" . $getDb->repo_mail . "',
                repo_url='" . $getDb->repo_url . "',
                repo_quality='" . $getDb->repo_quality . "',
                repo_unprocessed_tags='" . safe_text_db($getDb->repo_unprocessed_tags) . "',
                repo_new_date='" . $getDb->repo_new_date . "',
                repo_new_time='" . $getDb->repo_new_time . "',
                repo_changed_date='" . $getDb->repo_changed_date . "',
                repo_changed_time='" . $getDb->repo_changed_time . "'";
                    $dbh->query($sql_put);
                }

                // *** Remove old humo[nr]_repositories table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "repositories;";
                $dbh->query($qry);

                // *** Batch processing ***
                $dbh->beginTransaction();
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "sources");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = "INSERT INTO humo_sources SET
                    source_tree_id='" . $updateDb->tree_id . "',
                    source_status='" . $getDb->source_status . "',
                    source_gedcomnr='" . $getDb->source_gedcomnr . "',
                    source_order='" . $getDb->source_order . "',
                    source_title='" . safe_text_db($getDb->source_title) . "',
                    source_abbr='" . safe_text_db($getDb->source_abbr) . "',
                    source_date='" . $getDb->source_date . "',
                    source_place='" . safe_text_db($getDb->source_place) . "',
                    source_publ='" . safe_text_db($getDb->source_publ) . "',
                    source_refn='" . safe_text_db($getDb->source_refn) . "',
                    source_auth='" . safe_text_db($getDb->source_auth) . "',
                    source_subj='" . safe_text_db($getDb->source_subj) . "',
                    source_item='" . safe_text_db($getDb->source_item) . "',
                    source_kind='" . safe_text_db($getDb->source_kind) . "',
                    source_text='" . safe_text_db($getDb->source_text) . "',
                    source_photo='" . safe_text_db($getDb->source_photo) . "',
                    source_repo_name='" . safe_text_db($getDb->source_repo_name) . "',
                    source_repo_caln='" . safe_text_db($getDb->source_repo_caln) . "',
                    source_repo_page='" . safe_text_db($getDb->source_repo_page) . "',
                    source_repo_gedcomnr='" . $getDb->source_repo_gedcomnr . "',
                    source_quality='" . $getDb->source_quality . "',
                    source_unprocessed_tags='" . safe_text_db($getDb->source_unprocessed_tags) . "',
                    source_new_date='" . $getDb->source_new_date . "',
                    source_new_time='" . $getDb->source_new_time . "',
                    source_changed_date='" . $getDb->source_changed_date . "',
                    source_changed_time='" . $getDb->source_changed_time . "'";
                    $dbh->query($sql_put);
                }
                // *** Commit data in database ***
                $dbh->commit();

                // *** Remove old humo[nr]_sources table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "sources;";
                $dbh->query($qry);

                // *** Batch processing ***
                $dbh->beginTransaction();
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "texts");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = "INSERT INTO humo_texts SET
                    text_tree_id='" . $updateDb->tree_id . "',
                    text_gedcomnr='" . $getDb->text_gedcomnr . "',
                    text_text='" . safe_text_db($getDb->text_text) . "',
                    text_quality='" . $getDb->text_quality . "',
                    text_unprocessed_tags='" . safe_text_db($getDb->text_unprocessed_tags) . "',
                    text_new_date='" . $getDb->text_new_date . "',
                    text_new_time='" . $getDb->text_new_time . "',
                    text_changed_date='" . $getDb->text_changed_date . "',
                    text_changed_time='" . $getDb->text_changed_time . "'";
                    $dbh->query($sql_put);
                }
                // *** Commit data in database ***
                $dbh->commit();

                // *** Remove old humo[nr]_texts table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "texts;";
                $dbh->query($qry);

                // *** Batch processing ***
                $dbh->beginTransaction();
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "connections");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {

                    // STORE (address and events) REFERRED ID's IN ARRAY. Connect_sub_kind:
                    //person | event_source
                    //family  | event_source
                    //person | address_source
                    //family | address_source
                    $connect_sub_kind = $getDb->connect_sub_kind;
                    if ($getDb->connect_kind == 'person' && $getDb->connect_sub_kind == 'event_source') {
                        $person_event_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'pers_event_source';
                    }
                    if ($getDb->connect_kind == 'family' && $getDb->connect_sub_kind == 'event_source') {
                        $family_event_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'fam_event_source';
                    }
                    if ($getDb->connect_kind == 'person' && $getDb->connect_sub_kind == 'address_source') {
                        $person_address_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'pers_address_source';
                    }
                    if ($getDb->connect_kind == 'family' && $getDb->connect_sub_kind == 'address_source') {
                        $family_address_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'fam_address_source';
                    }

                    $sql_put = "INSERT INTO humo_connections SET
                    connect_tree_id='" . $updateDb->tree_id . "',
                    connect_order='" . $getDb->connect_order . "',
                    connect_kind='" . $getDb->connect_kind . "',
                    connect_sub_kind='" . $connect_sub_kind . "',
                    connect_connect_id='" . $getDb->connect_connect_id . "',
                    connect_date='" . $getDb->connect_date . "',
                    connect_place='" . safe_text_db($getDb->connect_place) . "',
                    connect_time='" . safe_text_db($getDb->connect_time) . "',
                    connect_page='" . safe_text_db($getDb->connect_page) . "',
                    connect_role='" . safe_text_db($getDb->connect_role) . "',
                    connect_text='" . safe_text_db($getDb->connect_text) . "',
                    connect_source_id='" . $getDb->connect_source_id . "',
                    connect_item_id='" . $getDb->connect_item_id . "',
                    connect_status='" . $getDb->connect_status . "',
                    connect_quality='" . $getDb->connect_quality . "',
                    connect_unprocessed_tags='" . safe_text_db($getDb->connect_unprocessed_tags) . "',
                    connect_new_date='" . $getDb->connect_new_date . "',
                    connect_new_time='" . $getDb->connect_new_time . "',
                    connect_changed_date='" . $getDb->connect_changed_date . "',
                    connect_changed_time='" . $getDb->connect_changed_time . "'";
                    $dbh->query($sql_put);
                }
                // *** Commit data in database ***
                $dbh->commit();

                // *** Remove old humo[nr]_connections table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "connections;";
                $dbh->query($qry);


                // *** Batch processing ***
                $dbh->beginTransaction();
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "addresses");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = "INSERT INTO humo_addresses SET
                    address_tree_id='" . $updateDb->tree_id . "',
                    address_gedcomnr='" . $getDb->address_gedcomnr . "',
                    address_order='" . $getDb->address_order . "',
                    address_person_id='" . $getDb->address_person_id . "',
                    address_family_id='" . $getDb->address_family_id . "',
                    address_address='" . safe_text_db($getDb->address_address) . "',
                    address_zip='" . safe_text_db($getDb->address_zip) . "',
                    address_place='" . safe_text_db($getDb->address_place) . "',
                    address_phone='" . safe_text_db($getDb->address_phone) . "',
                    address_date='" . $getDb->address_date . "',
                    address_text='" . safe_text_db($getDb->address_text) . "',
                    address_photo='" . safe_text_db($getDb->address_photo) . "',
                    address_quality='" . $getDb->address_quality . "',
                    address_unprocessed_tags='" . safe_text_db($getDb->address_unprocessed_tags) . "',
                    address_new_date='" . $getDb->address_new_date . "',
                    address_new_time='" . $getDb->address_new_time . "',
                    address_changed_date='" . $getDb->address_changed_date . "',
                    address_changed_time='" . $getDb->address_changed_time . "'";
                    $dbh->query($sql_put);

                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($person_address_source[$updateDb->tree_id]) and in_array($getDb->address_id, $person_address_source[$updateDb->tree_id])) && $dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='pers_address_source' AND connect_connect_id='" . $getDb->address_id . "'";
                        $dbh->query($qry);
                    }
                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($family_address_source[$updateDb->tree_id]) and in_array($getDb->address_id, $family_address_source[$updateDb->tree_id])) && $dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='fam_address_source' AND connect_connect_id='" . $getDb->address_id . "'";
                        $dbh->query($qry);
                    }
                }
                // *** Commit data in database ***
                $dbh->commit();

                // *** Remove old humo[nr]_addresses table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "addresses;";
                $dbh->query($qry);


                // *** Batch processing ***
                $dbh->beginTransaction();
                $sql_get = $dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    //event_event='".$getDb->event_event."',
                    $sql_put = "INSERT INTO humo_events SET
                    event_tree_id='" . $updateDb->tree_id . "',
                    event_gedcomnr='" . $getDb->event_gedcomnr . "',
                    event_order='" . $getDb->event_order . "',
                    event_person_id='" . $getDb->event_person_id . "',
                    event_family_id='" . $getDb->event_family_id . "',
                    event_kind='" . $getDb->event_kind . "',
                    event_event='" . safe_text_db($getDb->event_event) . "',
                    event_event_extra='" . safe_text_db($getDb->event_event_extra) . "',
                    event_gedcom='" . $getDb->event_gedcom . "',
                    event_date='" . $getDb->event_date . "',
                    event_place='" . safe_text_db($getDb->event_place) . "',
                    event_text='" . safe_text_db($getDb->event_text) . "',
                    event_quality='" . $getDb->event_quality . "',
                    event_unprocessed_tags='" . safe_text_db($getDb->event_unprocessed_tags) . "',
                    event_new_date='" . $getDb->event_new_date . "',
                    event_new_time='" . $getDb->event_new_time . "',
                    event_changed_date='" . $getDb->event_changed_date . "',
                    event_changed_time='" . $getDb->event_changed_time . "'";
                    $dbh->query($sql_put);

                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($person_event_source[$updateDb->tree_id]) and in_array($getDb->event_id, $person_event_source[$updateDb->tree_id])) && $dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='pers_event_source' AND connect_connect_id='" . $getDb->event_id . "'";
                        $dbh->query($qry);
                    }
                    // UPDATE connection table
                    if ((isset($family_event_source[$updateDb->tree_id]) and in_array($getDb->event_id, $family_event_source[$updateDb->tree_id])) && $dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='fam_event_source' AND connect_connect_id='" . $getDb->event_id . "'";
                        $dbh->query($qry);
                    }
                }
                // *** Commit data in database ***
                $dbh->commit();

                // *** Remove old humo[nr]_events table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "events;";
                $dbh->query($qry);
            }
        }

        // *** Show status of database update ***
        //echo '<script>';
        //echo 'document.getElementById("information").innerHTML="'.__('Update table unprocessed_tags...').'";';
        //echo '</script>';
        //ob_start();
        echo __('Update table unprocessed_tags...') . '<br>';
        //ob_flush();
        flush();


        // *** Copy tags from sources to tag table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT source_id, source_tree_id, source_unprocessed_tags FROM humo_sources WHERE source_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                tag_tree_id='" . $qryDb->source_tree_id . "',
                tag_source_id='" . $qryDb->source_id . "',
                tag_tag='" . safe_text_db($qryDb->source_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();
        // *** Remove tags from source table ***
        $qry = "ALTER TABLE humo_sources DROP source_unprocessed_tags;";
        $dbh->query($qry);

        // *** Copy tags from repositories table to tag table ***
        $sql = "SELECT repo_id, repo_tree_id, repo_unprocessed_tags FROM humo_repositories WHERE repo_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
            tag_tree_id='" . $qryDb->repo_tree_id . "',
            tag_repo_id='" . $qryDb->repo_id . "',
            tag_tag='" . safe_text_db($qryDb->repo_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Remove tags from repositories table ***
        $qry = "ALTER TABLE humo_repositories DROP repo_unprocessed_tags;";
        $dbh->query($qry);

        // *** Copy tags from texts to tag table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT text_id, text_tree_id, text_unprocessed_tags FROM humo_texts WHERE text_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                tag_tree_id='" . $qryDb->text_tree_id . "',
                tag_text_id='" . $qryDb->text_id . "',
                tag_tag='" . safe_text_db($qryDb->text_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();
        // *** Remove tags from texts table ***
        $qry = "ALTER TABLE humo_texts DROP text_unprocessed_tags;";
        $dbh->query($qry);

        // *** Copy tags from connections to tag table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT connect_id, connect_tree_id, connect_unprocessed_tags FROM humo_connections WHERE connect_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                tag_tree_id='" . $qryDb->connect_tree_id . "',
                tag_connect_id='" . $qryDb->connect_id . "',
                tag_tag='" . safe_text_db($qryDb->connect_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();
        // *** Remove tags from connections table ***
        $qry = "ALTER TABLE humo_connections DROP connect_unprocessed_tags;";
        $dbh->query($qry);

        // *** Copy tags from addresses to tag table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT address_id, address_tree_id, address_unprocessed_tags FROM humo_addresses WHERE address_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                tag_tree_id='" . $qryDb->address_tree_id . "',
                tag_address_id='" . $qryDb->address_id . "',
                tag_tag='" . safe_text_db($qryDb->address_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();
        // *** Remove tags from addresses table ***
        $qry = "ALTER TABLE humo_addresses DROP address_unprocessed_tags;";
        $dbh->query($qry);

        // *** Copy tags from events to tag table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT event_id, event_tree_id, event_unprocessed_tags FROM humo_events WHERE event_unprocessed_tags LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "INSERT INTO humo_unprocessed_tags SET
                tag_tree_id='" . $qryDb->event_tree_id . "',
                tag_event_id='" . $qryDb->event_id . "',
                tag_tag='" . safe_text_db($qryDb->event_unprocessed_tags) . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();
        // *** Remove tags from events table ***
        $qry = "ALTER TABLE humo_events DROP event_unprocessed_tags;";
        $dbh->query($qry);

        // *** Update "update_status" to number 8 ***
        $dbh->query("UPDATE humo_settings SET setting_value='8' WHERE setting_variable='update_status'");

        echo ' Database updated!';
        echo '</td></tr>';
    }

    public function update_v5_1_6(): void
    {
        // **************************************
        // *** Update procedure version 5.1.6 ***
        // **************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V5.1.6</td><td style="background-color:#00FF00">';

        // *** Show update status ***
        //echo __('Update in progress...').' <div id="information" style="display: inline; font-weight:bold;"></div><br>';
        echo __('Update in progress...') . '<br>';

        // *** Update event table ***
        $sql = "ALTER TABLE humo_events ADD event_connect_kind varchar(25) DEFAULT NULL AFTER event_order";
        $dbh->query($sql);

        $sql = 'UPDATE humo_events SET event_connect_kind="person" WHERE event_person_id!=""';
        $dbh->query($sql);

        $sql = 'UPDATE humo_events SET event_connect_kind="family", event_person_id=event_family_id WHERE event_family_id!=""';
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_events CHANGE event_person_id event_connect_id VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci";
        $dbh->query($sql);

        // *** Move photo's from source to event table ***
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT source_id, source_gedcomnr, source_tree_id, source_photo FROM humo_sources WHERE source_photo LIKE '_%'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $source_photo = explode(";", $qryDb->source_photo);
            for ($i = 0; $i <= count($source_photo) - 1; $i++) {
                $gebeurtsql = 'INSERT INTO humo_events SET
                    event_tree_id="' . $qryDb->source_tree_id . '",
                    event_order="' . ($i + 1) . '",
                    event_connect_kind="source",
                    event_connect_id="' . $qryDb->source_gedcomnr . '",
                    event_kind="picture",
                    event_event="' . trim($source_photo[$i]) . '"';
                $dbh->query($gebeurtsql);
            }
        }
        // *** Commit data in database ***
        $dbh->commit();

        $qry = "ALTER TABLE humo_events DROP event_family_id;";
        $dbh->query($qry);

        $qry = "ALTER TABLE humo_sources DROP source_photo;";
        $dbh->query($qry);

        $qry = "ALTER TABLE humo_repositories DROP repo_photo;";
        $dbh->query($qry);

        $qry = "ALTER TABLE humo_addresses DROP address_photo;";
        $dbh->query($qry);

        // *** Update user table ***
        $sql = "ALTER TABLE humo_user_notes ADD note_guest_name varchar(25) DEFAULT NULL AFTER note_user_id,
        ADD note_guest_mail varchar(100) DEFAULT NULL AFTER note_guest_name";
        $dbh->query($sql);

        // *** Update "update_status" to number 9 ***
        $dbh->query("UPDATE humo_settings SET setting_value='9' WHERE setting_variable='update_status'");

        echo ' Database updated!';
        echo '</td></tr>';
    }

    public function update_v5_1_9(): void
    {
        // **************************************
        // *** Update procedure version 5.1.9 ***
        // **************************************

        global $dbh;

        echo '<tr><td>HuMo-genealogy update V5.1.9</td><td style="background-color:#00FF00">';

        // *** Show update status ***
        //echo __('Update in progress...').' <div id="information" style="display: inline; font-weight:bold;"></div><br>';
        echo __('Update in progress...') . '<br>';

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_groups');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['group_source_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_source_presentation VARCHAR(20) NOT NULL DEFAULT 'title' AFTER group_sources;";
            $dbh->query($sql);
        }
        if (!isset($field['group_text_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_text_presentation VARCHAR(20) NOT NULL DEFAULT 'show' AFTER group_source_presentation;";
            $dbh->query($sql);
        }
        if (!isset($field['group_show_restricted_source'])) {
            $sql = "ALTER TABLE humo_groups ADD group_show_restricted_source VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_sources;";
            $dbh->query($sql);
        }
        if (!isset($field['group_death_date_act'])) {
            $sql = "ALTER TABLE humo_groups ADD group_death_date_act VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_alive_date;";
            $dbh->query($sql);
        }
        if (!isset($field['group_death_date'])) {
            $sql = "ALTER TABLE humo_groups ADD group_death_date VARCHAR(4) NOT NULL DEFAULT '1980' AFTER group_death_date_act;";
            $dbh->query($sql);
        }
        if (!isset($field['group_menu_persons'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_persons VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
            $dbh->query($sql);
        }
        if (!isset($field['group_menu_names'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_names VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
            $dbh->query($sql);
        }
        if (!isset($field['group_menu_login'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_login VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_menu_names;";
            $dbh->query($sql);
        }
        if (!isset($field['group_showstatistics'])) {
            $sql = "ALTER TABLE humo_groups ADD group_showstatistics VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $dbh->query($sql);
        }
        if (!isset($field['group_relcalc'])) {
            $sql = "ALTER TABLE humo_groups ADD group_relcalc VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $dbh->query($sql);
        }
        if (!isset($field['group_googlemaps'])) {
            $sql = "ALTER TABLE humo_groups ADD group_googlemaps VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $dbh->query($sql);
        }
        if (!isset($field['group_contact'])) {
            $sql = "ALTER TABLE humo_groups ADD group_contact VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $dbh->query($sql);
        }
        if (!isset($field['group_latestchanges'])) {
            $sql = "ALTER TABLE humo_groups ADD group_latestchanges VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $dbh->query($sql);
        }
        if (!isset($field['group_pdf_button'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pdf_button VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_own_code;";
            $dbh->query($sql);
        }
        if (!isset($field['group_rtf_button'])) {
            $sql = "ALTER TABLE humo_groups ADD group_rtf_button VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_pdf_button;";
            $dbh->query($sql);

            // *** Show RTF button in usergroup "Admin" ***
            $sql = "UPDATE humo_groups SET group_rtf_button='y' WHERE group_id=1";
            $dbh->query($sql);
        }
        if (!isset($field['group_user_notes'])) {
            $sql = "ALTER TABLE humo_groups ADD group_user_notes VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_own_code;";
            $dbh->query($sql);
        }
        if (!isset($field['group_user_notes_show'])) {
            $sql = "ALTER TABLE humo_groups ADD group_user_notes_show VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_user_notes;";
            $dbh->query($sql);
        }

        if (!isset($field['group_family_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_family_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'compact' AFTER group_pdf_button;";
            $dbh->query($sql);
        }
        if (!isset($field['group_maps_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_maps_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'hide' AFTER group_family_presentation;";
            $dbh->query($sql);
        }

        if (!isset($field['group_edit trees'])) {
            $sql = "ALTER TABLE humo_groups ADD group_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER group_hide_trees;";
            $dbh->query($sql);
        }

        $sql = "ALTER TABLE humo_groups ADD group_hide_photocat varchar(200) NOT NULL DEFAULT ''";
        $dbh->query($sql);

        // *** Update user_log table ***
        $sql = "ALTER TABLE humo_user_log ADD log_id mediumint(6) unsigned NOT NULL auto_increment FIRST, ADD PRIMARY KEY (`log_id`),
        ADD log_status varchar(10) CHARACTER SET utf8 DEFAULT '' AFTER log_user_admin";
        $dbh->query($sql);

        $dbh->query("UPDATE humo_user_log SET log_status='success'");

        // *** Update address table ***
        $sql = "ALTER TABLE humo_addresses ADD address_connect_kind varchar(25) DEFAULT NULL AFTER address_order,
        ADD address_connect_sub_kind varchar(30) DEFAULT NULL AFTER address_connect_kind";
        $dbh->query($sql);

        $sql = 'UPDATE humo_addresses SET address_connect_kind="person", address_connect_sub_kind="person" WHERE address_person_id!=""';
        $dbh->query($sql);

        $sql = 'UPDATE humo_addresses SET address_connect_kind="family", address_connect_sub_kind="family", address_person_id=address_family_id WHERE address_family_id!=""';
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_addresses CHANGE address_person_id address_connect_id VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci, DROP address_family_id;";
        $dbh->query($sql);

        // *** Update "update_status" to number 10 ***
        $dbh->query("UPDATE humo_settings SET setting_value='10' WHERE setting_variable='update_status'");

        echo ' Database updated!';
        echo '</td></tr>';
    }


    public function update_v5_2_5(): void
    {
        // **************************************
        // *** Update procedure version 5.2.5 ***
        // **************************************

        global $dbh;

        // *** Show update status ***
        //ob_start();
        echo '<tr><td>HuMo-genealogy update V5.2.5</td>';
        echo '<td style="background-color:#00FF00">' . __('Update in progress...') . ' <div id="information v5_2_5" style="display: inline; font-weight:bold;"></div></td></tr>';
        //ob_flush();
        flush();

        // *** Batch processing ***
        //$dbh->beginTransaction();

        // *** Update for 2 TYPE including long text: "2 TYPE E-mail address" ***
        $sql = "ALTER TABLE humo_events CHANGE event_gedcom event_gedcom VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_gedcomnr event_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_connect_id event_connect_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_id event_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update events table";</script>';
        //ob_flush();
        flush();

        // *** Automatic installation or update ***
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_users');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['user_hide_trees'])) {
            $sql = "ALTER TABLE humo_users ADD user_hide_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_group_id;";
            $dbh->query($sql);
        }
        if (!isset($field['user_edit_trees'])) {
            $sql = "ALTER TABLE humo_users ADD user_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_hide_trees;";
            $dbh->query($sql);
        }
        unset($field);

        // *** Change MEDIUMINT(6) into INT(10) for large family trees ***
        $sql = "ALTER TABLE humo_persons CHANGE pers_id pers_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE pers_gedcomnumber pers_gedcomnumber VARCHAR(25) CHARACTER SET utf8,
            CHANGE pers_indexnr pers_indexnr VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update pers table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_families CHANGE fam_id fam_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE fam_gedcomnumber fam_gedcomnumber VARCHAR(25) CHARACTER SET utf8,
            CHANGE fam_man fam_man VARCHAR(25) CHARACTER SET utf8,
            CHANGE fam_woman fam_woman VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update fam table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_addresses CHANGE address_id address_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE address_gedcomnr address_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE address_connect_id address_connect_id VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update address table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_connections CHANGE connect_id connect_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE connect_connect_id connect_connect_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE connect_source_id connect_source_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE connect_item_id connect_item_id VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update connect table";</script>';
        //ob_flush();
        flush();

        try {
            $sql = "ALTER TABLE humo_location CHANGE location_id location_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
            $dbh->query($sql);
        } catch (Exception $e) {
            //
        }

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update location table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_sources CHANGE source_id source_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE source_gedcomnr source_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE source_repo_gedcomnr source_repo_gedcomnr VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update source table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_texts CHANGE text_id text_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE text_gedcomnr text_gedcomnr VARCHAR(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update texts table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_repositories CHANGE repo_id repo_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE repo_gedcomnr repo_gedcomnr varchar(25) CHARACTER SET utf8";
        $dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update repo table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_user_notes ADD note_tree_id mediumint(7) AFTER note_status,
            CHANGE note_pers_gedcomnumber note_pers_gedcomnumber varchar(25) CHARACTER SET utf8,
            CHANGE note_fam_gedcomnumber note_fam_gedcomnumber varchar(25) CHARACTER SET utf8;";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_unprocessed_tags
            CHANGE tag_pers_id tag_pers_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_rel_id tag_rel_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_address_id tag_address_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_connect_id tag_connect_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_event_id tag_event_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_place_id tag_place_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_source_id tag_source_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_text_id tag_text_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_repo_id tag_repo_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_id tag_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $dbh->query($sql);
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update unprocessed table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_stat_date
            CHANGE stat_gedcom_fam stat_gedcom_fam varchar(25) CHARACTER SET utf8,
            CHANGE stat_gedcom_man stat_gedcom_man varchar(25) CHARACTER SET utf8,
            CHANGE stat_gedcom_woman stat_gedcom_woman varchar(25) CHARACTER SET utf8";
        $dbh->query($sql);

        // *** Table humo_users_notes isn't always available ***
        try {
            $sql = "ALTER TABLE humo_users_notes
                CHANGE note_pers_gedcomnumber note_pers_gedcomnumber varchar(25) CHARACTER SET utf8,
                CHANGE note_fam_gedcomnumber note_fam_gedcomnumber varchar(25) CHARACTER SET utf8";
            $dbh->query($sql);
        } catch (Exception $e) {
            //
        }

        // *** Update "update_status" to number 11 ***
        $dbh->query("UPDATE humo_settings SET setting_value='11' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }


    public function update_v5_6_1(): void
    {
        // **************************************
        // *** Update procedure version 5.6.1 ***
        // **************************************

        global $dbh;

        // *** Show update status ***
        //ob_start();
        echo '<tr><td>HuMo-genealogy update V5.6.1</td>';
        echo '<td style="background-color:#00FF00">' . __('Update in progress...') . ' <div id="information v5_6_1" style="display: inline; font-weight:bold;"></div></td></tr>';
        //ob_flush();
        flush();

        // *** Batch processing ***
        //$dbh->beginTransaction();

        // *** Add user_password_salted field to humo_users table ***
        $result = $dbh->query("SHOW COLUMNS FROM `humo_users` LIKE 'user_password_salted'");
        $exists = $result->rowCount();
        if (!$exists) {
            $dbh->query("ALTER TABLE humo_users ADD user_password_salted VARCHAR(255) CHARACTER SET utf8 AFTER user_password;");
        }

        $sql = "ALTER TABLE humo_sources
            CHANGE source_subj source_subj varchar(248) CHARACTER SET utf8,
            CHANGE source_place source_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_user_log CHANGE log_ip_address log_ip_address varchar(45) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_repositories CHANGE repo_phone repo_phone varchar(25) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_users ADD user_ip_address VARCHAR(45) CHARACTER SET utf8 DEFAULT '' AFTER user_edit_trees;";
        $dbh->query($sql);

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_persons');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['pers_new_user'])) {
            $sql = "ALTER TABLE humo_persons ADD pers_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER pers_quality;";
            $dbh->query($sql);
        }
        if (!isset($field['pers_changed_user'])) {
            $sql = "ALTER TABLE humo_persons ADD pers_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER pers_new_user;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_families');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['fam_new_user'])) {
            $sql = "ALTER TABLE humo_families ADD fam_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER fam_counter;";
            $dbh->query($sql);
        }
        if (!isset($field['fam_changed_user'])) {
            $sql = "ALTER TABLE humo_families ADD fam_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER fam_new_user;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_sources');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['source_new_user'])) {
            $sql = "ALTER TABLE humo_sources ADD source_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER source_quality;";
            $dbh->query($sql);
        }
        if (!isset($field['source_changed_user'])) {
            $sql = "ALTER TABLE humo_sources ADD source_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER source_new_user;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_repositories');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['repo_new_user'])) {
            $sql = "ALTER TABLE humo_repositories ADD repo_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER repo_quality;";
            $dbh->query($sql);
        }
        if (!isset($field['repo_changed_user'])) {
            $sql = "ALTER TABLE humo_repositories ADD repo_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER repo_new_user;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_addresses');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['address_new_user'])) {
            $sql = "ALTER TABLE humo_addresses ADD address_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER address_quality;";
            $dbh->query($sql);
        }
        if (!isset($field['address_changed_user'])) {
            $sql = "ALTER TABLE humo_addresses ADD address_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER address_new_user;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['event_new_user'])) {
            $sql = "ALTER TABLE humo_events ADD event_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER event_quality;";
            $dbh->query($sql);
        }
        if (!isset($field['event_changed_user'])) {
            $sql = "ALTER TABLE humo_events ADD event_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER event_new_user;";
            $dbh->query($sql);
        }

        // *** Update "update_status" to number 12 ***
        $dbh->query("UPDATE humo_settings SET setting_value='12' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_6_1").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }


    public function update_v5_7(): void
    {
        // ************************************
        // *** Update procedure version 5.7 ***
        // ************************************

        global $dbh, $db_functions;

        // *** Show update status ***
        //ob_start();
        echo '<tr><td>HuMo-genealogy update V5.7</td>';
        echo '<td style="background-color:#00FF00">' . __('Update in progress...') . ' <div id="information v5_7" style="display: inline; font-weight:bold;"></div></td></tr>';
        //ob_flush();
        flush();

        // *** Remove unwanted file from HuMo-genealogy ***
        if (file_exists('gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged')) {
            unlink('gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged');
        }

        $db_update = "ALTER TABLE humo_sources ADD source_shared varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER source_gedcomnr";
        $dbh->query($db_update);

        $db_update = "ALTER TABLE humo_addresses ADD address_shared varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER address_gedcomnr";
        $dbh->query($db_update);

        // *** Update ALL lines in humo_source table for all family trees ***
        $sql = "UPDATE humo_sources SET source_shared='1'";
        $dbh->query($sql);

        // *** Update ALL lines in humo_address table for all family trees ***
        $sql = "UPDATE humo_addresses SET address_shared='1' WHERE address_gedcomnr LIKE '_%'";
        $dbh->query($sql);

        // *** Batch processing ***
        //$dbh->beginTransaction();

        // *** Read all family trees from database ***
        $update_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = $db_functions->generate_gedcomnr($updateDb->tree_id, 'address');

            $address_qry = $dbh->query("SELECT * FROM humo_addresses
            WHERE address_tree_id='" . $updateDb->tree_id . "'
            AND (address_connect_kind='person' OR address_connect_kind='family')");
            while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
                //connect_order='".$count."',
                //$address_connect_sub_kind='person_address';
                $sql = "INSERT INTO humo_connections SET
                connect_tree_id='" . $updateDb->tree_id . "',
                connect_item_id='R" . $new_gedcomnumber . "',
                connect_date='" . $addressDb->address_date . "',
                connect_order='" . $addressDb->address_order . "',";
                if ($addressDb->address_connect_kind == 'person') {
                    $sql .= "connect_kind='person', connect_sub_kind='person_address',";
                } else {
                    $sql .= "connect_kind='family', connect_sub_kind='family_address',";
                }
                $sql .= "connect_connect_id='" . $addressDb->address_connect_id . "'";
                //echo $sql.'<br>';
                $dbh->query($sql);

                $sql = "UPDATE humo_addresses SET
                address_gedcomnr='R" . $new_gedcomnumber . "',
                address_order='',
                address_date='',
                address_connect_kind='',
                address_connect_sub_kind='',
                address_connect_id=''
                WHERE address_id='" . $addressDb->address_id . "'";
                //echo $sql.'<br><br>';
                $dbh->query($sql);

                $new_gedcomnumber++;
            }

            // *** Change ID for address by source into address GEDCOM number ***
            $sql = "SELECT * FROM humo_connections LEFT JOIN humo_addresses ON address_id=connect_connect_id
            WHERE connect_tree_id='" . $updateDb->tree_id . "'
            AND (connect_sub_kind='pers_address_source' OR connect_sub_kind='fam_address_source' OR connect_sub_kind='address_source')";
            $qry = $dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_connections SET connect_connect_id='" . $qryDb->address_gedcomnr . "'
                WHERE connect_id='" . $qryDb->connect_id . "'";
                //echo $sql.'<br><br>';
                $dbh->query($sql);
            }

            // *** Update sources ***

            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = $db_functions->generate_gedcomnr($updateDb->tree_id, 'source');

            // *** Batch processing ***
            //$dbh->beginTransaction();
            $sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $updateDb->tree_id . "'
            AND substring(connect_sub_kind, -7)='_source' AND connect_source_id NOT LIKE '_%'";
            $qry = $dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "INSERT INTO humo_sources SET
                source_tree_id='" . $updateDb->tree_id . "',
                source_gedcomnr='S" . $new_gedcomnumber . "',
                source_status='',
                source_title='',
                source_date='" . $qryDb->connect_date . "',
                source_place='',
                source_publ='',
                source_refn='',
                source_auth='',
                source_subj='',
                source_item='',
                source_kind='',
                source_repo_caln='',
                source_repo_page='',
                source_repo_gedcomnr='',
                source_text='" . safe_text_db($qryDb->connect_text) . "'";
                $dbh->query($sql);

                $sql = "UPDATE humo_connections SET connect_text='', connect_source_id='S" . $new_gedcomnumber . "' WHERE connect_id='" . $qryDb->connect_id . "'";
                $dbh->query($sql);

                $new_gedcomnumber++;
            }
            // *** Commit data in database ***
            //$dbh->commit();

        }

        // *** Update "update_status" to number 13 ***
        $dbh->query("UPDATE humo_settings SET setting_value='13' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_7").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }


    public function update_v5_9(): void
    {
        // ************************************
        // *** Update procedure version 5.9 ***
        // ************************************

        global $dbh, $db_functions;

        // *** Show update status ***
        //ob_start();
        echo '<tr><td>HuMo-genealogy update V5.9</td>';
        echo '<td style="background-color:#00FF00">' . __('Update in progress...') . ' <div id="information v5_9" style="display: inline; font-weight:bold;"></div></td></tr>';
        //ob_flush();
        flush();

        // *** Add index to humo_addresses ***
        $index_check = $dbh->query("SHOW KEYS FROM humo_addresses WHERE Key_name='address_gedcomnr'");
        if ($index_check->rowCount() == 0) {
            $update_sql = "ALTER TABLE `humo_addresses` ADD INDEX(`address_gedcomnr`);";
            $dbh->query($update_sql);
        }

        // *** Update for IPv6 ***
        $check_qry = "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_name = 'humo_stat_date' AND COLUMN_NAME = 'stat_ip_address'";
        $check_result = $dbh->query($check_qry);
        $checkDb = $check_result->fetch(PDO::FETCH_OBJ);
        if ($checkDb->CHARACTER_MAXIMUM_LENGTH == 20) {
            $update_sql = "ALTER TABLE `humo_stat_date` CHANGE `stat_ip_address` `stat_ip_address` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
            $dbh->query($update_sql);
        }

        // *** Add connect_new_user and connect_changed_user ***
        $sql = "ALTER TABLE humo_connections ADD connect_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER connect_quality;";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_connections ADD connect_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER connect_new_time;";
        $dbh->query($sql);

        // *** Add text_new_user and text_changed_user ***
        $sql = "ALTER TABLE humo_texts ADD text_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER text_quality;";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_texts ADD text_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER text_new_time;";
        $dbh->query($sql);

        // *** Move callname to event table ***
        // *** Batch processing ***
        //$dbh->beginTransaction();
        $sql_get = $dbh->query("SELECT pers_gedcomnumber,pers_tree_id,pers_callname FROM humo_persons WHERE pers_callname LIKE '_%'");
        while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
            $sql_put = "INSERT INTO humo_events SET
            event_tree_id='" . $getDb->pers_tree_id . "',
            event_order='1',
            event_connect_kind='person',
            event_connect_id='" . $getDb->pers_gedcomnumber . "',
            event_kind='name',
            event_event='" . safe_text_db($getDb->pers_callname) . "',
            event_gedcom='NICK'";
            //echo $sql_put.'<br>';
            $dbh->query($sql_put);
            //$pers_id=$dbh->lastInsertId();
        }
        // *** Commit data in database ***
        //$dbh->commit();

        // *** Update "update_status" to number 14 ***
        $dbh->query("UPDATE humo_settings SET setting_value='14' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_9").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }


    public function update_v6_0_1(): void
    {
        // **************************************
        // *** Update procedure version 6.0.1 ***
        // **************************************

        global $dbh, $db_functions;

        // *** Show update status ***
        //ob_start();
        echo '<tr><td>HuMo-genealogy update V6.0.1</td>';
        echo '<td style="background-color:#00FF00">' . __('Update in progress...') . ' <div id="information v6_0_1" style="display: inline; font-weight:bold;"></div></td></tr>';
        //ob_flush();
        flush();

        // *** Change pers_address_source and fam_address_source into: address_source ***
        $sql_get = $dbh->query("SELECT * FROM humo_connections WHERE connect_sub_kind='pers_address_source' OR connect_sub_kind='fam_address_source'");
        while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
            $sql_put = "UPDATE humo_connections SET connect_kind='address', connect_sub_kind='address_source' WHERE connect_id=" . $getDb->connect_id;
            $dbh->query($sql_put);
        }
        // *** Update humo_settings (needed larger ID, because of bug in scripts) ***
        $sql_put = "ALTER TABLE humo_settings CHANGE setting_id setting_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $dbh->query($sql_put);

        // *** Add 2FA in user table ***
        $sql = "ALTER TABLE humo_users ADD user_2fa_enabled varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER user_password_salted;";
        $dbh->query($sql);
        $sql = "ALTER TABLE humo_users ADD user_2fa_auth_secret varchar(50) CHARACTER SET utf8 DEFAULT '' AFTER user_2fa_enabled;";
        $dbh->query($sql);

        // *** Change place fields into 120 characters ***
        $sql = "ALTER TABLE humo_persons
        CHANGE pers_birth_place pers_birth_place VARCHAR(120) CHARACTER SET utf8,
        CHANGE pers_bapt_place pers_bapt_place VARCHAR(120) CHARACTER SET utf8,
        CHANGE pers_death_place pers_death_place VARCHAR(120) CHARACTER SET utf8,
        CHANGE pers_buried_place pers_buried_place VARCHAR(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_families
        CHANGE fam_relation_place fam_relation_place varchar(120) CHARACTER SET utf8,
        CHANGE fam_marr_notice_place fam_marr_notice_place varchar(120) CHARACTER SET utf8,
        CHANGE fam_marr_place fam_marr_place varchar(120) CHARACTER SET utf8,
        CHANGE fam_marr_church_notice_place fam_marr_church_notice_place varchar(120) CHARACTER SET utf8,
        CHANGE fam_marr_church_place fam_marr_church_place varchar(120) CHARACTER SET utf8,
        CHANGE fam_div_place fam_div_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_repositories CHANGE repo_place repo_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_connections CHANGE connect_place connect_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_addresses CHANGE address_place address_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $sql = "ALTER TABLE humo_events CHANGE event_place event_place varchar(120) CHARACTER SET utf8";
        $dbh->query($sql);

        $tempqry = $dbh->query("SHOW TABLES LIKE 'humo_location'");
        if ($tempqry->rowCount()) {
            $sql = "ALTER TABLE humo_location CHANGE location_location location_location VARCHAR(120) CHARACTER SET utf8";
            $dbh->query($sql);

            $result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
            $exists = $result->rowCount();
            if (!$exists) {
                $dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
            }
        }
        $tempqry = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
        if ($tempqry->rowCount()) {
            $sql = "ALTER TABLE humo_no_location CHANGE no_location_location no_location_location VARCHAR(120) CHARACTER SET utf8";
            $dbh->query($sql);
        }

        // *** Update "update_status" ***
        $dbh->query("UPDATE humo_settings SET setting_value='15' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v6_0_1").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }


    public function update_v6_4_1(): void
    {
        // **************************************
        // *** Update procedure version 6.4.1 ***
        // **************************************

        global $dbh, $db_functions;

        // *** Show update status ***
        //ob_start();
?>
        <tr>
            <td>HuMo-genealogy update V6.4.1</td>
            <td style="background-color:#00FF00"><?= __('Update in progress...'); ?><div id="information v6_4_1" style="display: inline; font-weight:bold;"></div>
            </td>
        </tr>
        <?php
        //ob_flush();
        flush();

        // *** Check table user_notes ***
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_user_notes');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        // *** Automatic update ***
        if (!isset($field['note_order'])) {
            $sql = "ALTER TABLE humo_user_notes CHANGE note_date note_new_date varchar(20) CHARACTER SET utf8;";
            $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_time note_new_time varchar(25) CHARACTER SET utf8;";
            $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_user_id note_new_user_id smallint(5);";
            $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_changed_date varchar(20) CHARACTER SET utf8 AFTER note_new_user_id;";
            $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_time varchar(25) CHARACTER SET utf8 AFTER note_changed_date;";
            $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_user_id smallint(5) AFTER note_changed_time;";
            $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_priority varchar(15) CHARACTER SET utf8 AFTER note_status;";
            $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes CHANGE note_status note_status varchar(15) CHARACTER SET utf8;";
            $dbh->query($sql);

            // *** Add note_order ***
            $sql = "ALTER TABLE humo_user_notes ADD note_order smallint(5) AFTER note_id;";
            $dbh->query($sql);

            // *** Add note_connect_kind = person/ family/ source/ repository ***
            $sql = "ALTER TABLE humo_user_notes ADD note_connect_kind varchar(20) CHARACTER SET utf8 AFTER note_tree_id;";
            $dbh->query($sql);

            // *** Add note_kind = user/ editor ***
            $sql = "ALTER TABLE humo_user_notes ADD note_kind varchar(10) CHARACTER SET utf8 AFTER note_tree_id;";
            $dbh->query($sql);

            // *** Change all existing note_connect_kind items into 'person' ***
            $sql = "UPDATE humo_user_notes SET note_connect_kind='person';";
            $dbh->query($sql);

            // *** Change note_pers_gedcomnumber into: note_connect_id ***
            $sql = "ALTER TABLE humo_user_notes CHANGE note_pers_gedcomnumber note_connect_id VARCHAR(25) CHARACTER SET utf8;";
            $dbh->query($sql);

            // *** Update tree_id, could be missing in some cases ***
            $sql = "SELECT * FROM humo_user_notes LEFT JOIN humo_trees ON note_tree_prefix=tree_prefix ORDER BY note_id;";
            $qry = $dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql2 = "UPDATE humo_user_notes SET note_tree_id='" . $qryDb->tree_id . "', note_kind='user' WHERE note_id='" . $qryDb->note_id . "'";
                $dbh->query($sql2);
            }

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_fam_gedcomnumber;";
            $dbh->query($sql);

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_tree_prefix;";
            $dbh->query($sql);
        }

        // *** Remove "NOT NULL" from hebnight variables ***
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_persons');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['pers_birth_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_birth_date_hebnight pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }
        if (isset($field['pers_death_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_death_date_hebnight pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }
        if (isset($field['pers_buried_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_buried_date_hebnight pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_families');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['fam_marr_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_notice_date_hebnight fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }
        if (isset($field['fam_marr_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_date_hebnight fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }
        if (isset($field['fam_marr_church_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_notice_date_hebnight fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }
        if (isset($field['fam_marr_church_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_date_hebnight fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['event_date_hebnight'])) {
            $sql = "ALTER TABLE humo_events CHANGE event_date_hebnight event_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $dbh->query($sql);
        }

        // *** Create table humo_stat_country
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_stat_country'");
        if (!$temp->rowCount()) {
            $qry = "CREATE TABLE humo_stat_country (
                stat_country_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                stat_country_ip_address varchar(40) DEFAULT NULL,
                stat_country_code VARCHAR(10) CHARACTER SET utf8
            )";
            $dbh->query($qry);
        }

        $dbh->query("ALTER TABLE `humo_texts` ADD KEY `text_gedcomnr` (`text_gedcomnr`)");

        $sql = "ALTER TABLE humo_events
            ADD event_connect_kind2 varchar(25) CHARACTER SET utf8 AFTER event_connect_id,
            ADD event_connect_id2 varchar(25) DEFAULT NULL AFTER event_connect_kind2";
        $dbh->query($sql);

        $dbh->query("ALTER TABLE `humo_events` ADD KEY `event_connect_id2` (`event_connect_id2`)");

        // loop humo_events, copy @ numbers to new event_connect_id2.
        // *** Batch processing ***
        $dbh->beginTransaction();
        $sql = "SELECT event_id, event_event FROM humo_events WHERE LEFT(event_event,1)= '@'
            AND (event_kind='birth_declaration' OR event_kind='baptism_witness'
            OR event_kind='death_declaration' OR event_kind='burial_witness'
            OR event_kind='marriage_witness' OR event_kind='marriage_witness_rel')
        ";
        // Bug: forgot to change event_connect_kind2 into person. Solved in later update.
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "UPDATE humo_events SET
                event_connect_id2='" . substr($qryDb->event_event, 1, -1) . "',
                event_event=''
                WHERE event_id= '" . $qryDb->event_id . "'";
            $dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $dbh->commit();

        // *** Update "update_status" ***
        $dbh->query("UPDATE humo_settings SET setting_value='16' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v6_4_1").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }

    public function update_v6_7_2(): void
    {
        // **************************************
        // *** Update procedure version 6.7.2 ***
        // **************************************

        global $dbh;

        // *** Show update status ***
        //ob_start();
        ?>
        <tr>
            <td>HuMo-genealogy update V6.7.2</td>
            <td style="background-color:#00FF00"><?= __('Update in progress...'); ?><div id="information v6_7_2" style="display: inline; font-weight:bold;"></div>
            </td>
        </tr>
        <?php
        //ob_flush();
        flush();

        function update_datetime_username($dbh, $table, $field)
        {
            // *** Improve date, time and username data fields ***
            $sql = "ALTER TABLE " . $table . "
                ADD " . $field . "_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER " . $field . "_new_time,
                ADD " . $field . "_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL AFTER " . $field . "_changed_time";
            if ($field != 'note') {
                $sql .= ", ADD " . $field . "_new_user_id SMALLINT NULL DEFAULT NULL AFTER " . $field . "_new_user,
                    ADD " . $field . "_changed_user_id SMALLINT NULL DEFAULT NULL AFTER " . $field . "_changed_user";
            }
            $dbh->query($sql);

            if ($field != 'note') {
                $sql = "SELECT " . $field . "_id as id,
                    " . $field . "_new_user as new_user,
                    " . $field . "_new_date as new_date,
                    " . $field . "_new_time as new_time,
                    " . $field . "_changed_user as changed_user,
                    " . $field . "_changed_date as changed_date,
                    " . $field . "_changed_time as changed_time,
                    new_user.user_id AS new_user_id,
                    changed_user.user_id AS changed_user_id
                    FROM " . $table . "
                    LEFT JOIN humo_users AS new_user ON " . $field . "_new_user=new_user.user_name
                    LEFT JOIN humo_users AS changed_user ON " . $field . "_changed_user=changed_user.user_name";
                //WHERE ".$field."_new_user IS NOT NULL or ".$field."_changed_user IS NOT NULL";
            } else {
                $sql = "SELECT " . $field . "_id as id,
                    " . $field . "_new_date as new_date,
                    " . $field . "_new_time as new_time,
                    " . $field . "_changed_date as changed_date,
                    " . $field . "_changed_time as changed_time
                    FROM " . $table;
                //WHERE ".$field."_new_user IS NOT NULL or ".$field."_changed_user IS NOT NULL";
            }
            $qry = $dbh->query($sql);

            // *** Batch processing ***
            $dbh->beginTransaction();

            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                // *** Also update new datetime field ***
                //$new_datetime = NULL;
                $new_datetime = '1970-01-01 00:00:01';
                if ($qryDb->new_date) {
                    // Convert from 06 JAN 2024 to 2024-02-06 20:54:13
                    //$new_datetime = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $qryDb->new_date . ' ' . $qryDb->new_time)));
                    $new_datetime = date('Y-m-d H:i:s', strtotime($qryDb->new_date . ' ' . $qryDb->new_time));
                }

                $changed_datetime = NULL;
                if ($qryDb->changed_date) {
                    //if ($qryDb->changed_date and $qryDb->changed_date!='') {
                    //$changed_datetime = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $qryDb->changed_date . ' ' . $qryDb->changed_time)));
                    $changed_datetime = date('Y-m-d H:i:s', strtotime($qryDb->changed_date . ' ' . $qryDb->changed_time));
                }

                $sql = 'UPDATE ' . $table . ' SET ' . $field . '_new_datetime="' . $new_datetime . '"';
                if ($field != 'note' && $qryDb->new_user_id) {
                    $sql .= ', ' . $field . '_new_user_id="' . $qryDb->new_user_id . '"';
                }

                if ($changed_datetime) {
                    $sql .= ', ' . $field . '_changed_datetime="' . $changed_datetime . '"';
                } else {
                    // *** Otherwise MySQL will add current date, not needed for conversion ***
                    $sql .= ', ' . $field . '_changed_datetime=NULL';
                }

                if ($field != 'note' && $qryDb->changed_user_id) {
                    $sql .= ', ' . $field . '_changed_user_id="' . $qryDb->changed_user_id . '"';
                }
                $sql .= ' WHERE ' . $field . '_id="' . $qryDb->id . '"';
                //echo $sql . '<br>';
                $dbh->query($sql);
            }

            // *** Commit data in database ***
            $dbh->commit();

            if ($field != 'note') {
                $dbh->query("ALTER TABLE " . $table . "
                DROP " . $field . "_new_user, DROP " . $field . "_new_date, DROP " . $field . "_new_time,
                DROP " . $field . "_changed_user, DROP " . $field . "_changed_date, DROP " . $field . "_changed_time;");
            } else {
                $dbh->query("ALTER TABLE " . $table . "
                DROP " . $field . "_new_date, DROP " . $field . "_new_time,
                DROP " . $field . "_changed_date, DROP " . $field . "_changed_time;");
            }
        }

        update_datetime_username($dbh, 'humo_addresses', 'address');
        update_datetime_username($dbh, 'humo_connections', 'connect');
        update_datetime_username($dbh, 'humo_events', 'event');

        update_datetime_username($dbh, 'humo_families', 'fam');
        update_datetime_username($dbh, 'humo_persons', 'pers');

        update_datetime_username($dbh, 'humo_repositories', 'repo');
        update_datetime_username($dbh, 'humo_sources', 'source');
        update_datetime_username($dbh, 'humo_texts', 'text');

        // *** Allready using user_id ***
        update_datetime_username($dbh, 'humo_user_notes', 'note');

        // *** Update "update_status" ***
        $dbh->query("UPDATE humo_settings SET setting_value='17' WHERE setting_variable='update_status'");

        // *** Commit data in database ***
        //$dbh->commit();

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v6_7_2").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }

    public function update_v6_7_9($dbh): void
    {
        // **************************************
        // *** Update procedure version 6.7.9 ***
        // **************************************

        // *** Show update status ***
        //ob_start();
        ?>
        <tr>
            <td>HuMo-genealogy update V6.7.9</td>
            <td style="background-color:#00FF00"><?= __('Update in progress...'); ?><div id="information v6_7_9" style="display: inline; font-weight:bold;"></div>
            </td>
        </tr>
        <?php
        //ob_flush();
        flush();

        // *** Restore update problem generate in version 6.4.1 (accidently changed family into person) ***
        $eventsql = "UPDATE humo_events SET event_connect_kind='family' WHERE event_kind='marriage_witness' OR event_kind='marriage_witness_rel'";
        $dbh->query($eventsql);

        // *** Create humo_location if not exists ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
        if (!$temp->rowCount()) {
            // no database exists - so create it
            // It has 4 columns:
            //     1. id
            //     2. name of location
            //     3. latitude as received from a geocode call
            //     4. longitude as received from a geocode call
            //     5. status: what is this location used for: birth/bapt/death/buried, and by which tree(s)
            $locationtbl = "CREATE TABLE humo_location (
                location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                location_location VARCHAR(120) CHARACTER SET utf8,
                location_lat FLOAT(10,6),
                location_lng FLOAT(10,6),
                location_status TEXT
            )";
            $dbh->query($locationtbl);
        }
        $result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
        $exists = $result->rowCount();
        if (!$exists) {
            $dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
        }

        // Table humo_no_location no longer in use.
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
        if ($temp->rowCount()) {
            $dbh->query("DROP TABLE humo_no_location");
        }

        // *** Change witnesses because of multiple kind of ASSO/ witnesses in GEDCOM 7 ***
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='CHR', event_gedcom='WITN' WHERE event_kind='baptism_witness'");
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='BURI', event_gedcom='WITN' WHERE event_kind='burial_witness'");
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='MARR', event_gedcom='WITN' WHERE event_kind='marriage_witness'");
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='MARR_REL', event_gedcom='WITN' WHERE event_kind='marriage_witness_rel'");

        // *** Change birth_declaration into birth_decl_witness ***
        // *** ONLY convert to birth_decl_witness if event_connect_kind2 is a person ***
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='birth_declaration', event_gedcom='WITN' WHERE event_kind='birth_declaration' AND (event_connect_kind2='person' OR event_event LIKE '_%')");
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='death_declaration', event_gedcom='WITN' WHERE event_kind='death_declaration' AND (event_connect_kind2='person' OR event_event LIKE '_%')");

        // *** Add seperate general birth_declaration and death_declaration events ***
        // *** Only convert declaration events where witness is connected ***
        $qry = $dbh->query("SELECT * from humo_events WHERE (event_connect_kind='birth_declaration' OR event_connect_kind='death_declaration') AND event_order='1'");

        // *** Batch processing ***
        $dbh->beginTransaction();

        // *** Add seperate general birth_declaration and death_declaration events ***
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if ($qryDb->event_date || $qryDb->event_place || $qryDb->event_text) {
                $sql_put = "INSERT INTO humo_events SET
                    event_tree_id='" . $qryDb->event_tree_id . "',
                    event_gedcomnr='',
                    event_order='" . $qryDb->event_order . "',
                    event_connect_kind='person',
                    event_connect_id='" . $qryDb->event_connect_id . "',
                    event_kind='" . $qryDb->event_connect_kind . "',
                    event_event='',
                    event_event_extra='" . safe_text_db($qryDb->event_event_extra) . "',
                    event_gedcom='EVEN',
                    event_date='" . $qryDb->event_date . "',
                    event_place='" . safe_text_db($qryDb->event_place) . "',
                    event_text='" . safe_text_db($qryDb->event_text) . "',
                    event_quality='" . $qryDb->event_quality . "',
                    event_new_user_id='" . $qryDb->event_new_user_id . "',
                    event_new_datetime='" . $qryDb->event_new_datetime . "',
                    event_changed_user_id='" . $qryDb->event_changed_user_id . "',
                    event_changed_datetime='" . $qryDb->event_changed_datetime . "'";

                $dbh->query($sql_put);
                $last_insert = $dbh->lastInsertId();

                // *** Update sources connected to these events connections ***
                $dbh->query("UPDATE humo_connections SET connect_connect_id='" . $last_insert . "' WHERE connect_connect_id='" . $qryDb->event_id . "'");
            }
        }

        // *** Commit data in database ***
        $dbh->commit();

        // *** Update for godfather events ***
        $sql = "SELECT * from humo_events WHERE event_kind='godfather'";
        $qry = $dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $eventsql = "UPDATE humo_events SET
                event_connect_kind='CHR',
                event_connect_kind2='person',
                event_connect_id2='" . substr($qryDb->event_event, 1, -1) . "',
                event_kind='ASSO',
                event_event='',
                event_gedcom='GODP'
                WHERE event_id= '" . $qryDb->event_id . "'";
            $dbh->query($eventsql);
        }

        // Remove old rel_merge_ variables using tree_prefix. Now tree_id is used.
        $dbh->query("DELETE FROM humo_settings WHERE setting_variable LIKE 'rel_merge_humo%'");

        // *** Update "update_status" ***
        $dbh->query("UPDATE humo_settings SET setting_value='18' WHERE setting_variable='update_status'");

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v6_7_9").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }

    public function update_v6_7_9a($dbh): void
    {
        // ***************************************
        // *** Update procedure version 6.7.9a ***
        // ***************************************

        // *** Show update status ***
        //ob_start();
        ?>
        <tr>
            <td>HuMo-genealogy update V6.7.9a</td>
            <td style="background-color:#00FF00"><?= __('Update in progress...'); ?><div id="information v6_7_9a" style="display: inline; font-weight:bold;"></div>
            </td>
        </tr>
<?php
        //ob_flush();
        flush();

        // *** In some cases event_connect_kind is empty, because of bug in previous update ***
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='birth_declaration', event_gedcom='WITN' WHERE event_kind='birth_declaration' AND event_connect_id2 LIKE 'I%'");
        $dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='death_declaration', event_gedcom='WITN' WHERE event_kind='death_declaration' AND event_connect_id2 LIKE 'I%'");

        // *** Because of bug: rerun this query, also check for empty event_connect_kind2 ***
        // *** Add seperate general birth_declaration and death_declaration events ***
        // *** Only convert declaration events where witness is connected ***
        $qry = $dbh->query("SELECT * from humo_events WHERE (event_connect_kind='birth_declaration' OR event_connect_kind='death_declaration') AND event_connect_kind2 = '' AND event_connect_id2 LIKE 'I%' AND event_order='1'");

        // *** Batch processing ***
        $dbh->beginTransaction();

        // *** Add seperate general birth_declaration and death_declaration events ***
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if ($qryDb->event_date || $qryDb->event_place || $qryDb->event_text) {
                $sql_put = "INSERT INTO humo_events SET
                    event_tree_id='" . $qryDb->event_tree_id . "',
                    event_gedcomnr='',
                    event_order='" . $qryDb->event_order . "',
                    event_connect_kind='person',
                    event_connect_id='" . $qryDb->event_connect_id . "',
                    event_kind='" . $qryDb->event_connect_kind . "',
                    event_event='',
                    event_event_extra='" . safe_text_db($qryDb->event_event_extra) . "',
                    event_gedcom='EVEN',
                    event_date='" . $qryDb->event_date . "',
                    event_place='" . safe_text_db($qryDb->event_place) . "',
                    event_text='" . safe_text_db($qryDb->event_text) . "',
                    event_quality='" . $qryDb->event_quality . "',
                    event_new_user_id='" . $qryDb->event_new_user_id . "',
                    event_new_datetime='" . $qryDb->event_new_datetime . "',
                    event_changed_user_id='" . $qryDb->event_changed_user_id . "',
                    event_changed_datetime='" . $qryDb->event_changed_datetime . "'";

                $dbh->query($sql_put);
                $last_insert = $dbh->lastInsertId();

                // *** Update sources connected to these events connections ***
                $dbh->query("UPDATE humo_connections SET connect_connect_id='" . $last_insert . "' WHERE connect_connect_id='" . $qryDb->event_id . "'");
            }
        }

        // *** Commit data in database ***
        $dbh->commit();

        // Solve bug in a previous update.
        $dbh->query("UPDATE humo_events SET event_connect_kind2='person' WHERE event_kind='ASSO' AND event_connect_id2 LIKE 'I%'");

        // *** Update "update_status" ***
        $dbh->query("UPDATE humo_settings SET setting_value='19' WHERE setting_variable='update_status'");

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v6_7_9a").innerHTML="Database updated!";</script>';
        //ob_flush();
        flush();
    }



    // Future update(s): remove some old datafields.


    /*	*** UPDATE REMARKS ***

    Length of all PLAC items: 120 chars.
        IP address: 45 characters (IP v6)
        PHON:25 characters

    EXAMPLES:
    *** Combination of ALTER and ADD in one query ***
    ALTER TABLE humo_persons CHANGE pers_id pers_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD pers_new_user VARCHAR(200) CHARACTER SET utf8 NULL DEFAULT NULL AFTER pers_quality

    *** Change multiple lines in 1 query ***
    $sql='ALTER TABLE humo_settings
    CHANGE id setting_id smallint(5) unsigned NOT NULL auto_increment,
    CHANGE variabele setting_variable varchar(50) CHARACTER SET utf8,
    CHANGE waarde setting_value text CHARACTER SET utf8
    ';

    *** Add multiple lines in 1 query ***
    $sql="ALTER TABLE ".$updateDb->tree_prefix."events
        ADD event_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER event_text,
        ADD event_event_extra text CHARACTER SET utf8 AFTER event_event";

    * Use batch processing if multiple queries are used:
        // *** Batch processing ***
        $dbh->beginTransaction();
        [Queries]
        // *** Commit data in database ***
        $dbh->commit();

    * If needed show processing time in second column:
        $start_time=time();
        [update script]
        // *** Show processing time ***
        $end_time=time(); echo $end_time-$start_time.' '.__('seconds.').'<br>';

    * Show more status information:
        echo __('Update in progress...').' <div id="information" style="display: inline; font-weight:bold;"></div><br>';

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information").innerHTML="'.__('Update tree:').' '.$updateDb->tree_id.'";</script>';
        //ob_flush();
        flush();
    */
} // *** End of update_cls ***
