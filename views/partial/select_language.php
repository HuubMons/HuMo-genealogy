<?php

/**
 * Apr. 2024: new function to show language selection using country flags.
 */

function show_country_flags($selected_language, $path, $variable, $language_path)
{
    global $humo_option, $language, $language_file;
    $HtmlEscaper = new \Genealogy\Include\HtmlEscaper();
    $selectedLanguageName = $HtmlEscaper->escHtmlDecoded($language["name"]);
?>
    <a class="nav-link dropdown-toggle" href="index.php?option=com_humo-gen" data-bs-toggle="dropdown"><img src="<?= $HtmlEscaper->escHtml((string) ($path . 'languages/' . $selected_language . '/flag.gif')); ?>" title="<?= $selectedLanguageName; ?>" alt="<?= $selectedLanguageName; ?>"></a>

    <ul class="dropdown-menu genealogy_menu">
        <?php
        // *** Needed to prevent new installation error ***
        if (isset($humo_option["hide_languages"])){
            $humo_option["hide_languages"] = $humo_option["hide_languages"];
        } else {
            $humo_option["hide_languages"] = "";
        }

        $hide_languages_array = explode(";", $humo_option["hide_languages"]);
        for ($i = 0; $i < count($language_file); $i++) {
            // *** Get language name ***
            if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
                $languageName = $HtmlEscaper->escHtmlDecoded($language["name"]);
                $languageCode = rawurlencode((string) $language_file[$i]);
                $flagPath = $HtmlEscaper->escHtml((string) ($path . 'languages/' . $language_file[$i] . '/flag.gif'));
                $languageUrl = $HtmlEscaper->escHtml((string) ($language_path . $variable . '=' . $languageCode));
        ?>
                <li>
                    <a class="dropdown-item" href="<?= $languageUrl; ?>">
                        <img src="<?= $flagPath; ?>" title="<?= $languageName; ?>" alt="<?= $languageName; ?>" style="border:none;">
                        <?= $languageName; ?>
                    </a>
                </li>
        <?php
            }
        }
        ?>
    </ul>
<?php } ?>