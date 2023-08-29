<?php
$checked_death_char = '';
if (isset($humo_option['death_char']) and $humo_option['death_char'] == "y") {
    $checked_death_char = " checked ";
}

$checked_admin_hebdate = '';
if (isset($humo_option['admin_hebdate']) and $humo_option['admin_hebdate'] == "y") {
    $checked_admin_hebdate = " checked ";
}

?>
<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_special">
    <table class="humo" border="1">
        <tr class="table_header">
            <th colspan="2"><?= __('Special settings'); ?></th>
        </tr>

        <tr>
            <td><?= __('Jewish settings'); ?></td>
            <td>
                <u><?= __('Display settings'); ?>:</u><br>
                <input type="checkbox" id="death_char" value="y" name="death_char" <?= $checked_death_char; ?>> <label for="death_char"><?= __('Change all &#134; characters into &infin; characters in all language files'); ?> (<?= __('unchecking and saving will revert to the cross sign'); ?>)</label><br>

                <input type="checkbox" id="admin_hebdate" value="y" name="admin_hebdate" <?= $checked_admin_hebdate; ?>> <label for="admin_hebdate"><?= __('Display Hebrew date after Gregorian date: 23 Dec 1980 (16 Tevet 5741)'); ?></label><br>
                <?php

                $checked = '';
                if (isset($humo_option['david_stars']) and $humo_option['david_stars'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="david_stars" value="y" name="david_stars" ' . $checked . '>  <label for="david_stars">' . __('Place yellow Stars of David before holocaust victims in lists and reports') . '</label><br>';

                $checked = '';
                if (isset($humo_option['death_shoa']) and $humo_option['death_shoa'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="death_shoa" value="y" name="death_shoa" ' . $checked . '>  <label for="death_shoa">' . __('Add: "cause of death: murdered" to holocaust victims') . '</label><br>';
                echo '<u>' . __('Editor settings') . ':</u><br>';

                $checked = '';
                if (isset($humo_option['admin_hebnight']) and $humo_option['admin_hebnight'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="admin_hebnight" value="y" name="admin_hebnight" ' . $checked . '>  <label for="admin_hebnight">' . __('Add "night" checkbox next to Gregorian dates to calculate Hebrew date correctly') . '</label><br>';

                $checked = '';
                if (isset($humo_option['admin_hebname']) and $humo_option['admin_hebname'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="admin_hebname" value="y" name="admin_hebname" ' . $checked . '>  <label for="admin_hebname">' . __('Add field for Hebrew name in name section of editor (instead of in "events" list)') . '</label><br>';

                $checked = '';
                if (isset($humo_option['admin_brit']) and $humo_option['admin_brit'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="admin_brit" value="y" name="admin_brit" ' . $checked . '>  <label for="admin_brit">' . __('Add field for Brit Mila under birth fields (instead of in "events" list)') . '</label><br>';

                $checked = '';
                if (isset($humo_option['admin_barm']) and $humo_option['admin_barm'] == "y") {
                    $checked = " checked ";
                }
                echo '<input type="checkbox" id="admin_barm" value="y" name="admin_barm" ' . $checked . '>  <label for="admin_barm">' . __('Add field for Bar/ Bat Mitsva before baptise fields (instead of in "events" list)') . '</label>';
                echo '<br><input type="Submit" style="margin:3px" name="save_option3" value="' . __('Change') . '">';
                ?>
            </td>
        </tr>

        <tr>
            <td><?= __('Sitemap'); ?></td>
            <td>
                <b><?= __('Sitemap'); ?></b> <br>
                <?= __('A sitemap can be used for quick indexing of the family screens by search engines. Add the sitemap link to a search engine (like Google), or add the link in a robots.txt file (in the root folder of your website). Example of robots.txt file, sitemap line:<br>
Sitemap: http://www.yourwebsite.com/humo-gen/sitemap.php'); ?>
                <br><a href="../sitemap.php"><?= __('Sitemap'); ?></a>
            </td>
        </tr>

    </table>
</form>