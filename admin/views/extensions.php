<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 class="center"><?= __('Extensions'); ?></h1>

<form method="post" action="index.php?page=extensions" class="ms-3">
    <div class="row mb-2">
        <div class="col-md-6">
            <h2><?= __('Show/ hide languages'); ?></h2>
            <?php
            // *** Language choice ***
            $counter = count($language_file);
            for ($i = 0; $i < $counter; $i++) {
                // *** Get language name ***
                include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');

                $checked = ' checked';
                if (in_array($language_file[$i], $extensions['hide_languages'])) {
                    $checked = '';
                }

                $disabled = '';
                if ($language_file[$i] == $humo_option["default_language"] || $language_file[$i] == $humo_option["default_language_admin"]) {
                    $disabled = ' disabled';
                    $checked = ' checked';
                }
            ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="<?= $language_file[$i]; ?>" <?= $checked . $disabled; ?>>
                    <label class="form-check-label">
                        <img src="../languages/<?= $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;"> <?= $language["name"]; ?>
                    </label>
                </div>
            <?php } ?>
            <input type="submit" name="save_option" class="btn btn-sm btn-success" value="<?= __('Change'); ?>">
        </div>

        <div class="col-md-auto">
            <!-- Select theme's -->
            <h2><?= __('Show/ hide theme\'s'); ?></h2>
            <?php
            $count_themes = count($extensions['theme_folders']);
            for ($i = 0; $i < $count_themes; $i++) {
                $theme = str_replace(".css", "", $extensions['theme_folders'][$i]);
            ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="<?= $theme; ?>" <?= !in_array($theme, $extensions['hide_themes']) ? 'checked' : ''; ?>>
                    <label class="form-check-label">
                        <?= $theme; ?>
                    </label>
                </div>
            <?php } ?>
            <input type="submit" name="save_option" class="btn btn-sm btn-success" value="<?= __('Change'); ?>">
        </div>

    </div>
</form>