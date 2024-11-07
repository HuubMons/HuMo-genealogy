<h2><?= __('Jewish settings'); ?></h2>

<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_special">

    <div class="genealogy_search p-2">
        <h4><?= __('Display settings'); ?>:</h4>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="y" name="death_char" <?php if (isset($humo_option['death_char']) and $humo_option['death_char'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Change all &#134; characters into &infin; characters in all language files'); ?> (<?= __('unchecking and saving will revert to the cross sign'); ?>)</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="admin_hebdate" <?php if (isset($humo_option['admin_hebdate']) and $humo_option['admin_hebdate'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Display Hebrew date after Gregorian date: 23 Dec 1980 (16 Tevet 5741)'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="david_stars" <?php if (isset($humo_option['david_stars']) and $humo_option['david_stars'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Place yellow Stars of David before holocaust victims in lists and reports'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="death_shoa" <?php if (isset($humo_option['death_shoa']) and $humo_option['death_shoa'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Add: "cause of death: murdered" to holocaust victims'); ?></label>
        </div>

        <h4><?= __('Editor settings'); ?>:</h4>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="admin_hebnight" <?php if (isset($humo_option['admin_hebnight']) and $humo_option['admin_hebnight'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Add "night" checkbox next to Gregorian dates to calculate Hebrew date correctly'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="admin_hebname" <?php if (isset($humo_option['admin_hebname']) and $humo_option['admin_hebname'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Add field for Hebrew name in name section of editor (instead of in "events" list)'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="admin_brit" <?php if (isset($humo_option['admin_brit']) and $humo_option['admin_brit'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Add field for Brit Mila under birth fields (instead of in "events" list)'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="admin_barm" <?php if (isset($humo_option['admin_barm']) and $humo_option['admin_barm'] == "y") echo 'checked'; ?>>
            <label class="form-check-label"><?= __('Add field for Bar/ Bat Mitsva before baptise fields (instead of in "events" list)'); ?></label>
        </div>

        <input type="submit" style="margin:3px" name="save_option3" class="btn btn-sm btn-success" value="<?= __('Change'); ?>">
    </div>
</form>

<h2 class="mt-2"><?= __('Sitemap'); ?></h2>
<?= __('A sitemap can be used for quick indexing of the family screens by search engines. Add the sitemap link to a search engine (like Google), or add the link in a robots.txt file (in the root folder of your website). Example of robots.txt file, sitemap line:<br>
Sitemap: http://www.yourwebsite.com/humo-gen/sitemap.php'); ?>
<br><a href="../sitemap.php"><?= __('Sitemap'); ?></a>