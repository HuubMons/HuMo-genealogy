<?php

/**
 * Jan. 2024: changed the language editor. Removed Javascript, improved layout.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TODO create seperate controller script.
require_once  __DIR__ . "/../models/language_editor.php";
$language_model = new LanguageEditorModel($dbh);
$language_editor['language'] = $language_model->getLanguage();
$language_editor['file'] = '../languages/' . $language_editor['language'] . '/' . $language_editor['language'] . '.po';
$language_editor['message'] = $language_model->saveFile($language_editor);



// TODO move code to model script (including functions at end of this script)
if (!isset($humo_option["hide_languages"])) $humo_option["hide_languages"] = '';
$hide_languages_array = explode(";", $humo_option["hide_languages"]);

// *** Get name of selected language, will return $language["name"] ***
include(__DIR__ . '/../../languages/' . $language_editor['language'] . '/language_data.php');


$line_array = array();
$handle = @fopen($language_editor['file'], "r");
if ($handle) {
    $count = 0;
    $msgid = 0;
    $msgstr = 0;
    $note = 0;
    $line_array = array();
    while (($buffer = fgets($handle, 4096)) !== false) {
        if (substr($buffer, 0, 5) == "msgid") {
            $msgid = 1;
            $msgstr = 0;
            $note = 0;
            $line_array[$count]["msgid"] = substr($buffer, 6);
            $line_array[$count]["msgid_empty"] = 0;
        } elseif (substr($buffer, 0, 6) == "msgstr") {
            $msgstr = 1;
            $msgid = 0;
            $note = 0;
            $line_array[$count]["msgstr"] = substr($buffer, 7);
            $line_array[$count]["msgstr_empty"] = 0;
        } elseif (substr($buffer, 0, 1) == "#") {
            if ($note == 0) {
                $note = 1;
                $msgstr = 0;
                $msgid = 0;
                $line_array[$count]["note"] = $buffer;
            } else {
                $line_array[$count]["note"] .= $buffer;
            }
            /*	if(strpos("fuzzy",$buffer)!==false) {
                    $line_array[$count]["fuzzy"] = 1;
                }
                else {
                    $line_array[$count]["fuzzy"] = 0;
                }
            */
        } elseif (substr($buffer, 0, 1) == '"') {
            if ($msgid == 1) {
                $line_array[$count]["msgid"] .= $buffer;
                $line_array[$count]["msgid_empty"] = 1;
            }
            if ($msgstr == 1) {
                $line_array[$count]["msgstr"] .= $buffer;
                $line_array[$count]["msgstr_empty"] = 1;
            }
        } else {
            $count++;
            $note = 0;
            $msgstr = 0;
            $msgid = 0;
        }
        $line_array[$count]["nr"] = $count;
    }
    $_SESSION['line_array'] = $line_array;
} else {
    $language_editor['message'] = "Can't open the language file!";
}

if (!feof($handle)) {
    $language_editor['message'] = "Error: unexpected fgets() fail\n";
}
fclose($handle);



// default
if (!isset($_SESSION['maxlines'])) {
    $_SESSION['maxlines'] = 10;
}
// user input
elseif (isset($_POST['maxlines'])) {
    $_SESSION['maxlines'] = $_POST['maxlines'];
}

// default is first page
if (!isset($_SESSION['present_page'])) {
    $_SESSION['present_page'] = 0;
}
// previous page button pressed
if (isset($_GET['to_prev_page']) and is_numeric($_GET['to_prev_page'])) {
    $_SESSION['present_page'] = $_GET['to_prev_page'];
}
// next page button pressed
if (isset($_GET['to_next_page']) and is_numeric($_GET['to_next_page'])) {
    $_SESSION['present_page'] = $_GET['to_next_page'];
}
// after search change start with first page
if (isset($_POST['langsearch'])) {
    $_SESSION['present_page'] = 0;
}

// maxlines changed
if (
    isset($_POST['maxlines']) and !isset($_POST['prevpage']) and !isset($_POST['nextpage'])
    and !isset($_POST['langsearch']) and (isset($_POST['save_button']) and $_POST['save_button'] != "pressed")
) {
    $_SESSION['present_page'] = 0;
}

if (isset($_POST['langsearchtext']) and isset($_POST['langsearch'])) {
    $_SESSION['langsearchtext'] = $_POST['langsearchtext'];
}

$search_lines = 0;
$firstkey = 0;
if (isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != "") {
    //$search_lines=0;
    foreach ($_SESSION['line_array'] as $key => $value) {
        if ($key == 0) {
            $firstkey = 1;
            continue;
        } // description of po file
        if ((isset($value["msgid"]) and stripos($value["msgid"], $_SESSION['langsearchtext']) !== FALSE) or
            (isset($value["msgstr"]) and stripos($value["msgstr"], $_SESSION['langsearchtext']) !== FALSE)
        ) {
            $search_lines++;
        }
    }
}

if (isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != "") {
    $count_lines = $search_lines;
} else {
    $count_lines = count($_SESSION['line_array']);
}
$next = '';
if (($_SESSION['present_page'] + 1) * $_SESSION['maxlines'] < $count_lines) { // only show next page button if not last page
    $next = '&amp;to_next_page=' . ($_SESSION['present_page'] + 1);
}

$previous = '';
if ($_SESSION['present_page'] > 0) { // only show prev page button if not first page
    $previous = '&amp;to_prev_page=' . ($_SESSION['present_page'] - 1);
}

?>

<script src="include/popup_merge.js"></script>

<form method="POST" action="" name="saveform" style="display : inline;">
    <input type="hidden" name="editor_language" value="<?= $language_editor['language']; ?>">
    <h1 class="center"><?= __('Language editor'); ?></h1>

    <div style="margin:10px;padding:3px">
        <?php printf(__('This is the language editor of %s. It\'s possible to change or edit language items in this editor. If you find language errors in a language, please contact the programmers. They will change this in a next version!'), 'HuMo-genealogy'); ?>
        <?= __('Translate into the right column. The untranslated items appear first.'); ?>
    </div>

    <?php if ($language_editor['message']) { ?>
        <div class="alert alert-success"><?= $language_editor['message']; ?></div>
    <?php } ?>

    <!-- <div class="row p-2 mb-3 mx-sm-1 genealogy_search"> -->
    <div class="row p-2 mb-3 mx-sm-1 align-items-center genealogy_search">

        <div class="col-auto">
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= '../languages/' . $language_editor['language']; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>"> <?= $language["name"]; ?>
                </button>
                <ul class="dropdown-menu">
                    <?php
                    for ($i = 0; $i < count($language_file); $i++) {
                        // *** Get language name ***
                        if ($language_file[$i] != $language_editor['language'] and !in_array($language_file[$i], $hide_languages_array)) {
                            include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
                    ?>
                            <li>
                                <a class="dropdown-item" href="../admin/index.php?page=language_editor&amp;editor_language=<?= $language_file[$i]; ?>">
                                    <img src="<?= '../languages/' . $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;">
                                    <?= $language["name"]; ?>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="col-1">
            <!-- Navigation links -->
            <nav aria-label="Page navigation example">
                <ul class="pagination mb-0">
                    <li class="page-item">
                        <a class="page-link" href="index.php?page=language_editor<?= $previous; ?>&amp;editor_language=<?= $language_editor['language']; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item disabled">
                        <a class="page-link" href="#"><?= $_SESSION['present_page'] + 1; ?></a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="index.php?page=language_editor<?= $next; ?>&amp;editor_language=<?= $language_editor['language']; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="col-auto">
            <div class="input-group">
                <!-- Max items per page choice -->
                <label for="maxlines" class="col-sm-auto col-form-label"><?= __('Max items per page: '); ?>&nbsp;</label>
                <select size="1" name="maxlines" id="maxlines" class="form-select form-select-sm" onChange="this.form.submit();">
                    <option value="10" <?= $_SESSION['maxlines'] == 10 ? ' selected' : ''; ?>>10</option>
                    <option value="20" <?= $_SESSION['maxlines'] == 20 ? ' selected' : ''; ?>>20</option>
                    <option value="30" <?= $_SESSION['maxlines'] == 30 ? ' selected' : ''; ?>>30</option>
                    <option value="50" <?= $_SESSION['maxlines'] == 50 ? ' selected' : ''; ?>>50</option>
                    <option value="100" <?= $_SESSION['maxlines'] == 100 ? ' selected' : ''; ?>>100</option>
                    <option value="200" <?= $_SESSION['maxlines'] == 200 ? ' selected' : ''; ?>>200</option>
                    <option value="300" <?= $_SESSION['maxlines'] == 300 ? ' selected' : ''; ?>>300</option>
                    <option value="400" <?= $_SESSION['maxlines'] == 400 ? ' selected' : ''; ?>>400</option>
                </select>
            </div>
        </div>

        <div class="col-auto">
            <div class="input-group">
                <!-- Search box -->
                <?php
                $langsearchtext = "";
                if (isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != "") {
                    $langsearchtext = $_SESSION['langsearchtext'];
                }
                ?>
                <input type="text" style="width:200px;" name="langsearchtext" class="form-control form-control-sm" value="<?= $langsearchtext; ?>">
                <input type="submit" name="langsearch" class="btn btn-sm btn-success" value="<?= __('Search'); ?>">
            </div>
        </div>

        <div class="col-2">
            <!-- Items found -->
            <?= __('Total items found: ') . $count_lines; ?>
        </div>

        <div class="col-2">
            <!-- Save  button -->
            <?php
            if (@is_writable($language_editor['file'])) {
                $num = count($_SESSION['line_array']);
                echo ' <input type="submit" name="save_button" class="btn btn-sm btn-primary" value="' . __('Save') . '">';
            } else {
                echo '<b>' . __('FILE IS NOT WRITABLE!') . '</b>';
            }
            ?>
        </div>

    </div>

    <?php include(__DIR__ . '/../../languages/' . $language_editor['language'] . '/language_data.php'); ?>

    <!-- Show translation line. New function jan. 2024 -->
    <?php
    function show_line($mytext, $value, $key, $color = false, $fuzz = false)
    {
        $rows = 1;
        $count_lines = substr_count($value["msgstr"], '<br>');
        if ($count_lines > 0) {
            $rows = ($count_lines * 3);
        }
        $checked = '';
        if ($fuzz) {
            $checked = ' checked';
        }
        $bgcolor = '';
        if ($color) {
            $bgcolor = ' bg-warning';
        }
    ?>
        <tr>
            <td style="width:2%">
                <a onmouseover="popup('<?= popclean($mytext); ?> ',300);" href="#"><img style="border:0px;background:none" src="../images/reports.gif" alt="references"></a>
            </td>
            <td style="padding:2px;"><?= msgid_display($value["msgid"]); ?></td>
            <td style="text-align:center;"><input type="checkbox" value="fuzzie" name="fuz<?= $value["nr"]; ?>" <?= $checked; ?>></td>
            <td style="vertical-align:top">
                <!-- <label for="txt_id<?= $key; ?>" class="form-label">Label</label> -->
                <textarea name="txt_name<?= $key; ?>" rows="<?= $rows; ?>" class="form-control<?= $bgcolor; ?>" id="txt_id<?= $key; ?>"><?= msgstr_display($value["msgstr"]) ?></textarea>
            </td>
        </tr>
    <?php
    }
    ?>

    <!-- Show translation table -->
    <table class="humo" border="1" cellspacing="0" width="98%" style="margin-left:auto;margin-right:auto">
        <tr class="table_header_large">
            <th></th>
            <th style="border-right:none;width:48.5%"><?= __('Template'); ?></th>
            <th style="font-size:85%;width:4%"><?= __('Fuzzy'); ?></th>
            <th style="border-left:none;width:47.5%">
                &nbsp;&nbsp;&nbsp;<?= __('Translation into') . ' ' . $language["name"]; ?>
            </th>
        </tr>

        <?php
        $count = 0;
        $loop_count = 0;
        $found = false;
        // non-translated items
        foreach ($_SESSION['line_array'] as $key => $value) {
            if ($key == 0) {
                continue;
            }
            if (isset($value["msgstr"]) and str_replace("\n", "", $value["msgstr"]) == '""') {
                if (
                    isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != ""
                    and stripos($value["msgid"], $_SESSION['langsearchtext']) === FALSE
                    and stripos($value["msgstr"], $_SESSION['langsearchtext']) === FALSE
                ) {
                    continue;
                }

                if ($count < $_SESSION['present_page'] * $_SESSION['maxlines']) {
                    $count++;
                    continue;
                }
                $loop_count++;
                if ($loop_count > $_SESSION['maxlines']) break;

                if (isset($value["note"])) {
                    $mytext = notes($value["note"]);
                } else $mytext = "";
                show_line($mytext, $value, $key, true);
                $found = true;
            }
        }
        // translated items, fuzzy
        foreach ($_SESSION['line_array'] as $key => $value) {
            // description of po file
            if ($key == 0) {
                continue;
            }
            if (isset($value["note"]) and strpos($value["note"], "fuzzy") !== false and isset($value["msgstr"]) and str_replace("\n", "", $value["msgstr"]) != '""' and isset($value["msgid"])) {
                if (
                    isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != ""
                    and stripos($value["msgid"], $_SESSION['langsearchtext']) === FALSE
                    and stripos($value["msgstr"], $_SESSION['langsearchtext']) === FALSE
                ) {
                    continue;
                }
                if ($count < $_SESSION['present_page'] * $_SESSION['maxlines']) {
                    $count++;
                    continue;
                }
                $loop_count++;
                if ($loop_count > $_SESSION['maxlines']) break;

                $mytext = notes($value["note"]);
                show_line($mytext, $value, $key, true, true);
                $found = true;
            }
        }
        // translated items
        foreach ($_SESSION['line_array'] as $key => $value) {
            // description of po file
            if ($key == 0) {
                continue;
            }
            if ((!isset($value["note"]) or strpos($value["note"], "fuzzy") === false) and isset($value["msgstr"]) and str_replace("\n", "", $value["msgstr"]) != '""' and isset($value["msgid"])) {
                if (
                    isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != ""
                    and stripos($value["msgid"], $_SESSION['langsearchtext']) === FALSE
                    and stripos($value["msgstr"], $_SESSION['langsearchtext']) === FALSE
                ) {
                    continue;
                }
                if ($count < $_SESSION['present_page'] * $_SESSION['maxlines']) {
                    $count++;
                    continue;
                }
                $loop_count++;
                if ($loop_count > $_SESSION['maxlines']) break;

                if (isset($value["note"])) {
                    $mytext = notes($value["note"]);
                } else {
                    $mytext = "";
                }
                show_line($mytext, $value, $key);
                $found = true;
            }
        }
        if ($found === false) {
            echo '<tr><td colspan="3"><span style="color:red">' . __('No results found') . '</span></td></tr>';
        }
        ?>
    </table>
    <br><br><br>
</form>

<?php
//**** SOME FORMAT FUNCTIONS ****
function notes($input)
{
    // formats the po notes for the reference/notes popup
    $output = "<u>References/Notes</u>:<br>" . str_replace(array("# ", "#: ", "#~ "), array("", "&#187; ", ""), $input);
    return $output;
}

function popclean($input)
{
    // formats the text for the reference/notes popup
    $output = str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", htmlentities(addslashes($input), ENT_QUOTES));
    return $output;
}

function msgid_display($string)
{
    // formats the msgid for display in the table
    $string = str_replace('\"', '^^', $string);
    $string = str_replace('"', '', $string);
    $string = str_replace('^^', '\"', $string);
    $string = htmlspecialchars($string);
    $string = str_replace('\n', '\n<br>', $string);
    return $string;
}

function msgstr_display($string)
{
    // formats the msgid and msgstr for display in the table
    $string = str_replace('\"', '^^', $string);
    $string = str_replace('"', '', $string);
    $string = str_replace('^^', '\"', $string);
    $string = str_replace("\'", "'", $string);
    $string = substr($string, 0, -1);
    $string = htmlspecialchars($string);
    if (substr($string, 0, 1) == " ") {
        $string = "&nbsp;" . ltrim($string, " ");
    }
    if (substr($string, -1) == " ") {
        $string = rtrim($string, " ") . "&nbsp;";
    }
    $string = str_replace('\n', '\n<br>', $string);
    return $string;
}

function msgstr_save($string)
{
    // formats the displayed msgstr text for saving in .po file (text that is displayed)
    $string = strip_tags($string);
    if ($string and $string != "<br>") {
        $string = htmlspecialchars_decode($string);
        $string = str_replace('"', '\"', $string);  // we want the " with backslash since msgstr afterwards gets " around it!
        $find = array("\\n<br>", "\r\n", "&nbsp;", "&#32;", '\\\\"');
        $replace = array("\\n", "\"\r\"", " ", " ", '\\"');
        if (substr($string, -4) == "<br>") $string = substr($string, 0, -4);
        $string = "\"" . str_replace($find, $replace, $string) . "\"\n\n";
    } else {
        $string = "\"\"\n\n";
    }
    return $string;
}

function msgstr_save2($string)
{
    // formats the non displayed msgstr text for saving in .po file 
    if ($string and $string != "<br>") {
        $find = array("\\n<br>", "\r\n", "&nbsp;", "&#32;", '\\\\"');
        $replace = array("\\n", "\"\r\"", " ", " ", '\\"');
        $string = str_replace($find, $replace, $string) . "\n";
    } else {
        $string = "\"\"\n\n";
    }
    return $string;
}
