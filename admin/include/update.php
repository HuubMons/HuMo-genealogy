<?php
/*
HuMo-genealogy database update script by Huub Mons.

15-08-2011 Completely rewritten this script, and update for version 4.7.
26-05-2019 Improved this script. Used updateCls.php, improved use of flush and commit for version 5.2.5.
*/

@set_time_limit(4000);

echo '<h2>UPDATE PROCEDURE</h2>';

echo __('Multiple updates will be done. It is recommended to do a database backup!<br><b>NEVER INTERRUPT THE UPDATE PROCEDURE!</b><br><b>Please wait until the notice that the update has been completed!</b>');

if (!isset($_GET['proceed'])) {
    echo '<p><a href="index.php?page=update&proceed=1">START UPDATE PROCEDURE</a>';
} else {
    // *** Use class for multiple update scripts ***
    $update_cls = new UpdateCls;

    // *** UPDATE PROCEDURES ****************************************************************
    $humo_update = 0;
    if (isset($_SESSION['save_humo_update'])) {
        $humo_update = $_SESSION['save_humo_update'];
    }

?>
    <p>
    <table class="humo">
        <?php
        echo '<tr class="table_header"><th colspan="2">';
        printf(__('%s status'), 'HuMo-genealogy');
        echo '</th></tr>';

        // *** Very old databases: no database update status available ***
        if (!isset($humo_option["update_status"])) {
            // *** Generate table humo_tree_texts and copy texts from humo_stambomen to humo_tree_texts ***
            $update_cls->update_v3_1();

            // *** Update humo_stambomen_tekst to humo_tree_texts ***
            $update_cls->update_v4_6();

            // *** No visible results ***
            // *** Change talen/taal-nederlands.php into nl etc. ***
            $update_cls->update_v4_2();

            // *** Create or translate table humo_stat_date ***
            $update_cls->update_v3_2();

            // *** Update table humo_settings, language settings, table humo_trees, table humo_logbook -> humo_user_log ***
            // *** Update table humo_users, table humo_groups, ................... ***
            $update_cls->update_v4_6_update_2();

            $update_cls->update_v4_6_update_3();

            // *** Start to save database status in humo_settings table ***
            $humo_option["update_status"] = '0';
            $sql = "INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'";
            @$dbh->query($sql);

            // ***************************************************************
            // *** Update procedure version 4.7 and update_status set to 1 ***
            // ***************************************************************
            $update_cls->update_v4_7();
        }
        if ($humo_option["update_status"] > '0') {
            echo '<tr><td>HuMo-genealogy update V4.7</td><td style="background-color:#00FF00">OK</td></tr>';
        }

        // ************************************
        // *** Update procedure version 4.8 ***
        // ************************************
        if ($humo_option["update_status"] > '1') {
            echo '<tr><td>HuMo-genealogy update V4.8</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v4_8();
        }

        // **************************************
        // *** Update procedure version 4.8.2 ***
        // **************************************
        if ($humo_option["update_status"] > '2') {
            echo '<tr><td>HuMo-genealogy update V4.8.2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v4_8_2();
        }

        // **************************************
        // *** Update procedure version 4.8.8 ***
        // **************************************
        if ($humo_option["update_status"] > '3') {
            echo '<tr><td>HuMo-genealogy update V4.8.8</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v4_8_8();
        }

        // **************************************
        // *** Update procedure version 4.8.9 ***
        // **************************************
        if ($humo_option["update_status"] > '4') {
            echo '<tr><td>HuMo-genealogy update V4.8.9</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v4_8_9();
        }

        // **************************************
        // *** Update procedure version 4.9.1 ***
        // **************************************
        if ($humo_option["update_status"] > '5') {
            echo '<tr><td>HuMo-genealogy update V4.9.1</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v4_9_1();
        }

        // ************************************
        // *** Update procedure version 5.0 ***
        // ************************************
        if ($humo_option["update_status"] > '6') {
            echo '<tr><td>HuMo-genealogy update V5.0</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_0();
        }

        // ************************************
        // *** Update procedure version 5.1 ***
        // ************************************
        if ($humo_option["update_status"] > '7') {
            echo '<tr><td>HuMo-genealogy update V5.1</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_1();
        }

        // **************************************
        // *** Update procedure version 5.1.6 ***
        // **************************************
        if ($humo_option["update_status"] > '8') {
            echo '<tr><td>HuMo-genealogy update V5.1.6</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_1_6();
        }

        // **************************************
        // *** Update procedure version 5.1.9 ***
        // **************************************
        if ($humo_option["update_status"] > '9') {
            echo '<tr><td>HuMo-genealogy update V5.1.9</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_1_9();
        }

        // **************************************
        // *** Update procedure version 5.2.5 ***
        // **************************************
        if ($humo_option["update_status"] > '10') {
            echo '<tr><td>HuMo-genealogy update V5.2.5</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_2_5();
        }

        // **************************************
        // *** Update procedure version 5.6.1 ***
        // **************************************
        if ($humo_option["update_status"] > '11') {
            echo '<tr><td>HuMo-genealogy update V5.6.1</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_6_1();
        }

        // ************************************
        // *** Update procedure version 5.7 ***
        // ************************************
        if ($humo_option["update_status"] > '12') {
            echo '<tr><td>HuMo-genealogy update V5.7</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_7();
        }

        // ************************************
        // *** Update procedure version 5.9 ***
        // ************************************
        if ($humo_option["update_status"] > '13') {
            echo '<tr><td>HuMo-genealogy update V5.9</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v5_9();
        }

        // **************************************
        // *** Update procedure version 6.0.1 ***
        // **************************************
        if ($humo_option["update_status"] > '14') {
            echo '<tr><td>HuMo-genealogy update V6.0.1</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v6_0_1();
        }

        // **************************************
        // *** Update procedure version 6.4.1 ***
        // **************************************
        if ($humo_option["update_status"] > '15') {
            echo '<tr><td>HuMo-genealogy update V6.4.1</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v6_4_1();
        }

        // **************************************
        // *** Update procedure version 6.7.2 ***
        // **************************************
        if ($humo_option["update_status"] > '16') {
            echo '<tr><td>HuMo-genealogy update V6.7.2</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v6_7_2();
        }

        // **************************************
        // *** Update procedure version 6.7.9 ***
        // **************************************
        if ($humo_option["update_status"] > '17') {
            echo '<tr><td>HuMo-genealogy update V6.7.9</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v6_7_9($dbh);
        }

        // ***************************************
        // *** Update procedure version 6.7.9a ***
        // ***************************************
        if ($humo_option["update_status"] > '18') {
            echo '<tr><td>HuMo-genealogy update V6.7.9a</td><td style="background-color:#00FF00">OK</td></tr>';
        } else {
            $update_cls->update_v6_7_9a($dbh);
        }

        /*	END OF MAIN UPDATE SCRIPT
        *** VERY IMPORTANT REMARKS FOR PROGRAMMERS ***
        * 1) Change update_status in install.php
        * 2) Change version check in admin/index.php
        * 3) Don't forget to add new database fields in the tables of install.php!
        * Change database fields: check if database changes can be made in global parts of this update script.
        * Use LIMIT 0,1 to prevent update problems (in large family trees) if possible: SELECT * FROM humo_stat_date LIMIT 0,1
        */

        // *** END OF UPDATES ***
        ?>
    </table><br>
    <?= __('All updates completed, click at "Mainmenu"'); ?>.
    <a href="index.php"><?= __('Main menu'); ?></a>
<?php
    // *** END OF UPDATE PROCEDURES *******************************************************
}
