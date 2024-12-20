<?php
class LanguageEditorModel
{
    public function getLanguage()
    {
        $language = 'en';
        if (
            isset($_GET['editor_language']) && file_exists(__DIR__ . '/../../languages/' . $_GET['editor_language'] . '/' . $_GET['editor_language'] . '.mo')
        ) {
            $language = $_GET['editor_language'];
        }
        if (
            isset($_POST['editor_language']) && file_exists(__DIR__ . '/../../languages/' . $_POST['editor_language'] . '/' . $_POST['editor_language'] . '.mo')
        ) {
            $language = $_POST['editor_language'];
        }
        return $language;
    }

    /*
    public function get_languages_array($humo_option)
    {
        if (!isset($humo_option["hide_languages"])) {
            $humo_option["hide_languages"] = '';
        }
        $language_editor['hide_languages_array'] = explode(";", $humo_option["hide_languages"]);
        return $language_editor['hide_languages_array'];
    }
    */

    public function saveFile($language_editor)
    {
        $message = '';
        if (isset($_POST['save_button'])) {
            $save_array = array();
            $counter = count($_SESSION['line_array']);
            for ($i = 1; $i < $counter; $i++) {
                if (isset($_POST['txt_name' . $i])) {
                    // displayed items
                    $content = str_replace("\\\\\\", "\\", $_POST['txt_name' . $i]);
                    $content = str_replace("\\\\", "\\", $content);
                    $_SESSION['line_array'][$i]['msgstr'] = $content;
                    // store posted lines - these will be written to the file with the msgstr_save function.
                    // the other ones will just get copied straight from the array
                    $save_array[$i] = $this->msgstr_save($content);
                } elseif (isset($_SESSION['line_array'][$i]['msgstr'])) {
                    // non displayed items - these will be written to the file with the msgstr_save2 function.
                    $save_array[$i] = $this->msgstr_save2($_SESSION['line_array'][$i]['msgstr']);
                }
            }

            $handle_write = @fopen('../languages/' . $language_editor['language'] . '/' . $language_editor['language'] . ".po", "w+");
            if ($handle_write) {
                $counter = count($_SESSION['line_array']);
                for ($i = 0; $i < $counter; $i++) {
                    // #~ remarks need \n at end, except for last one:
                    if (isset($_SESSION['line_array'][$i]["note"]) && $i != (count($_SESSION['line_array']) - 1) && substr($_SESSION['line_array'][$i]["note"], 0, 2) === "#~") {
                        $_SESSION['line_array'][$i]["note"] .= "\n";
                    }
                    // write all types of notes:
                    if (isset($_SESSION['line_array'][$i]["note"])) {
                        if (strpos($_SESSION['line_array'][$i]["note"], "fuzzy") !== false && isset($_POST['txt_name' . $i]) && !isset($_POST['fuz' . $i])) {
                            // we have to find: "#, fuzzy" as well as: "#, fuzzy, php-format" as well as: "#, php-format, fuzzy"
                            $_SESSION['line_array'][$i]["note"] = str_replace(array("#, fuzzy\n", "fuzzy, ", ", fuzzy"), array("", "", ""), $_SESSION['line_array'][$i]["note"]);
                        }
                        if (strpos($_SESSION['line_array'][$i]["note"], "fuzzy") === false && isset($_POST['txt_name' . $i]) && isset($_POST['fuz' . $i])) {
                            if (strpos($_SESSION['line_array'][$i]["note"], "#,") != false) { // there already is another #. entry --> add fuzzy
                                $_SESSION['line_array'][$i]["note"] = str_replace("#,", "#, fuzzy,", $_SESSION['line_array'][$i]["note"]);
                            } else {
                                $_SESSION['line_array'][$i]["note"] .= "#, fuzzy\n";
                            }
                        }
                        fwrite($handle_write, $_SESSION['line_array'][$i]["note"]);
                    }
                    // write msgid line:
                    if (isset($_SESSION['line_array'][$i]["msgid"])) {
                        fwrite($handle_write, "msgid " . $_SESSION['line_array'][$i]["msgid"]);
                    }
                    // write all msgstr lines:
                    if (isset($_SESSION['line_array'][$i]["msgstr"])) {
                        if ($i == 0) { // first msgstr is the description of the po file
                            fwrite($handle_write, "msgstr " . $_SESSION['line_array'][$i]["msgstr"] . "\n");
                        } elseif (isset($_SESSION['line_array'][$i]["msgid"])) { // regular msgstr lines
                            fwrite($handle_write, "msgstr " . $save_array[$i]);
                        } else {  // no msgstr such as after #~ remarks
                            fwrite($handle_write, "\n");
                        }
                    }
                }
                $message = __('Saved') . ' ' . __('Language') . ': ' . $language_editor['file'];
            } else {
                $message = 'Saving failed!';
            }
            fclose($handle_write);

            // *** Convert .po file into .mo file! ***
            require(__DIR__ . '/../../admin/include/po-mo_converter/php-mo.php');
            if (phpmo_convert($language_editor['file'])) {
                //echo 'The .mo file is succesfully saved!';
            } else {
                $message = '<br>ERROR: the .mo file IS NOT saved!<br>';
            }
        }
        return $message;
    }

    private function msgstr_save($string)
    {
        // formats the displayed msgstr text for saving in .po file (text that is displayed)
        $string = strip_tags($string);
        if ($string && $string !== "<br>") {
            $string = htmlspecialchars_decode($string);
            $string = str_replace('"', '\"', $string);  // we want the " with backslash since msgstr afterwards gets " around it!
            $find = array("\\n<br>", "\r\n", "&nbsp;", "&#32;", '\\\\"');
            $replace = array("\\n", "\"\r\"", " ", " ", '\\"');
            if (substr($string, -4) === "<br>") {
                $string = substr($string, 0, -4);
            }
            $string = "\"" . str_replace($find, $replace, $string) . "\"\n\n";
        } else {
            $string = "\"\"\n\n";
        }
        return $string;
    }

    private function msgstr_save2($string)
    {
        // formats the non displayed msgstr text for saving in .po file 
        if ($string && $string != "<br>") {
            $find = array("\\n<br>", "\r\n", "&nbsp;", "&#32;", '\\\\"');
            $replace = array("\\n", "\"\r\"", " ", " ", '\\"');
            $string = str_replace($find, $replace, $string) . "\n";
        } else {
            $string = "\"\"\n\n";
        }
        return $string;
    }
}
