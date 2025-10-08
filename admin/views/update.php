<?php

/**
 * HuMo-genealogy database update script by Huub Mons.
 * 
 * 15-08-2011 Completely rewritten this script, and update for version 4.7.
 * 26-05-2019 Improved this script. Used updateCls.php, improved use of flush and commit for version 5.2.5.
 */

@set_time_limit(4000);
?>

<h2><?= __('Update procedure'); ?></h2>

<?= __('Multiple updates will be done. It is recommended to do a database backup!'); ?><br>

<div class="alert alert-danger my-2" role="alert">
    <b><?= __('NEVER INTERRUPT THE UPDATE PROCEDURE!'); ?></b>
</div>

<b><?= __('Please wait until the notice that the update has been completed!'); ?></b>

<?php if (!isset($_GET['proceed'])) { ?>
    <div class="mt-2"><a href="index.php?page=update&proceed=1"><?= __('Start update procedure'); ?></a></div>
    <?php
} else {
    // *** UPDATE PROCEDURES ***
    $humo_update = 0;
    if (isset($_SESSION['save_humo_update'])) {
        $humo_update = $_SESSION['save_humo_update'];
    }

    function show_status($dbh, $humo_option, $version, $version_number)
    {
        // *** Example: this is version update 19. So check should be > 18 ***
        $check_number = $version_number - 1;
        if ((int)$humo_option["update_status"] > $check_number) {
            // *** Only show update status if update version > 14 (v5.9) ***
            if ($version_number > 14) {
        ?>
                <tr>
                    <td>HuMo-genealogy update <?= $version ?></td>
                    <td style="background-color:#00FF00">OK</td>
                </tr>
            <?php
            }
        } else { ?>
            <tr>
                <td>HuMo-genealogy update <?= $version ?></td>
                <td style="background-color:#00FF00">
                    <?= __('Update in progress...'); ?><div id="information <?= str_replace('.', '_', $version) ?>" style="display: inline; font-weight:bold;"></div>

                    <?php
                    $class = '\Genealogy\Admin\Migrations\Migration' . $version_number;
                    $migration = new $class($dbh);
                    $migration->up();

                    // *** Update "update_status" ***
                    $dbh->query("UPDATE humo_settings SET setting_value=" . $version_number . " WHERE setting_variable='update_status'");
                    ?>

                    <div class="mt-2"><a class="btn btn-warning" href="index.php?page=update&proceed=1"><?= __('Reload for next update') ?></a></div>

                </td>
            </tr>

            <script>
                document.getElementById("information <?= str_replace('.', '_', $version) ?>").innerHTML = "<?= __('Database updated!') ?>";
            </script>

            <?php
            // *** Stop so button can be pressed to reload ***
            exit;
            ?>
    <?php
        }
    }
    ?>

    <table class="table mt-2">
        <tr>
            <th colspan="2">
                <?php printf(__('%s status'), 'HuMo-genealogy'); ?>
            </th>
        </tr>

        <?php
        // *** Very old databases: no database update status available ***
        if (!isset($humo_option["update_status"])) {
            $migration = new \Genealogy\Admin\Migrations\Migration0($dbh);

            // *** Generate table humo_tree_texts and copy texts from humo_stambomen to humo_tree_texts ***
            $migration->update_v3_1();

            // *** Update humo_stambomen_tekst to humo_tree_texts ***
            $migration->update_v4_6();

            // *** No visible results ***
            // *** Change talen/taal-nederlands.php into nl etc. ***
            $migration->update_v4_2();

            // *** Create or translate table humo_stat_date ***
            $migration->update_v3_2();

            // *** Update table humo_settings, language settings, table humo_trees, table humo_logbook -> humo_user_log ***
            // *** Update table humo_users, table humo_groups, ................... ***
            $migration->update_v4_6_update_2();

            $migration->update_v4_6_update_3();

            // *** Start to save database status in humo_settings table ***
            $humo_option["update_status"] = '0';
            $sql = "INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'";
            $dbh->query($sql);
        }

        show_status($dbh, $humo_option, 'v4.7', 1);
        show_status($dbh, $humo_option, 'v4.8', 2);
        show_status($dbh, $humo_option, 'v4.8.2', 3);
        show_status($dbh, $humo_option, 'v4.8.8', 4);
        show_status($dbh, $humo_option, 'v4.8.9', 5);
        show_status($dbh, $humo_option, 'v4.9.1', 6);
        show_status($dbh, $humo_option, 'v5.0', 7);
        show_status($dbh, $humo_option, 'v5.1', 8);
        show_status($dbh, $humo_option, 'v5.1.6', 9);
        show_status($dbh, $humo_option, 'v5.1.9', 10);
        show_status($dbh, $humo_option, 'v5.2.5', 11);
        show_status($dbh, $humo_option, 'v5.6.1', 12);
        show_status($dbh, $humo_option, 'v5.7', 13);
        show_status($dbh, $humo_option, 'v5.9', 14);
        show_status($dbh, $humo_option, 'v6.0.1', 15);
        show_status($dbh, $humo_option, 'v6.4.1', 16);
        show_status($dbh, $humo_option, 'v6.7.2', 17);
        show_status($dbh, $humo_option, 'v6.7.9', 18);
        show_status($dbh, $humo_option, 'v6.7.9a', 19);
        show_status($dbh, $humo_option, 'v7.0', 20);
        //show_status($dbh, $humo_option, 'v7.0.1', 21); // *** Future update ***

        /**
         * Remarks for programmers:
         * 1) Change update_status in install.php
         * 2) Change version check in admin/index.php
         * 3) Don't forget to add new database fields in the tables of install.php!
         * Change database fields: check if database changes can be made in general parts of this update script.
         * Use LIMIT 0,1 to prevent update problems (in large family trees) if possible: SELECT * FROM humo_stat_date LIMIT 0,1
         *
         * TODO: use functions or migration tool.
         * private function tableExists(string $table): bool
         * {
         *     try {
         *         $result = $this->dbh->query("SHOW TABLES LIKE " . $this->dbh->quote($table));
         *         return $result && $result->rowCount() > 0;
         *     } catch (Exception $e) {
         *         // log error
         *         return false;
         *     }
         * }
         * columnExists($tableName, $columnName)
         * addColumn($tableName, $columnDef)
         * renameTable($old, $new)
         * changeColumn($table, $old, $newDef)
         *
         *	*** UPDATE REMARKS ***
         * Length of all PLAC items: 120 chars.
         *    IP address: 45 characters (IP v6)
         *    PHON:25 characters
         *
         * EXAMPLES:
         * *** Combination of ALTER and ADD in one query ***
         * ALTER TABLE humo_persons CHANGE pers_id pers_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
         * ADD pers_new_user VARCHAR(200) CHARACTER SET utf8 NULL DEFAULT NULL AFTER pers_quality
         *
         * *** Change multiple lines in 1 query ***
         * $sql='ALTER TABLE humo_settings
         * CHANGE id setting_id smallint(5) unsigned NOT NULL auto_increment,
         * CHANGE variabele setting_variable varchar(50) CHARACTER SET utf8,
         * CHANGE waarde setting_value text CHARACTER SET utf8';
         *
         * *** Add multiple lines in 1 query ***
         * $sql="ALTER TABLE ".$updateDb->tree_prefix."events
         *     ADD event_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER event_text,
         *     ADD event_event_extra text CHARACTER SET utf8 AFTER event_event";
         *
         * Use batch processing if multiple queries are used:
         *     // *** Batch processing ***
         *     $this->dbh->beginTransaction();
         *     [Queries]
         *     // *** Commit data in database ***
         *     $this->dbh->commit();
         *
         * If needed show processing time in second column:
         *     $start_time=time();
         *     [update script]
         *     // *** Show processing time ***
         *     $end_time=time(); echo $end_time-$start_time.' '.__('seconds.').'<br>';
         */
        ?>
    </table><br>
    <?= __('All updates completed, click at "Mainmenu"'); ?>.
    <a href="index.php"><?= __('Main menu'); ?></a>
<?php
}
