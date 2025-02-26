<?php

/**
 * Apr. 2024: new function to show language selection using country flags.
 */

function show_country_flags($selected_language, $path, $variable, $language_path)
{
    global $humo_option, $language, $language_file;
?>
    <a class="nav-link dropdown-toggle" href="index.php?option=com_humo-gen" data-bs-toggle="dropdown"><img src="<?= $path . 'languages/' . $selected_language; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>"></a>

    <ul class="dropdown-menu genealogy_menu">
        <?php
        $hide_languages_array = explode(";", $humo_option["hide_languages"]);
        for ($i = 0; $i < count($language_file); $i++) {
            // *** Get language name ***
            if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
        ?>
                <li>
                    <a class="dropdown-item" href="<?= $language_path . $variable . '=' . $language_file[$i]; ?>">
                        <img src="<?= $path . 'languages/' . $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;">
                        <?= $language["name"]; ?>
                    </a>
                </li>
        <?php
            }
        }
        ?>
    </ul>
<?php } ?>