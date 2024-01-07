<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

//TODO 
// IE - line around pics
?>
<script src="include/popup_merge.js"></script>

<form method="POST" action="" name="saveform" style="display : inline;">
    <div style="position:fixed;top:100px;left:0px;">
        <h1 class="center"><?= __('Language editor'); ?></h1>

        <div style="margin:10px;padding:3px">
            <?php
            printf(__('This is the language editor of %s. It\'s possible to change or edit language items in this editor. If you find language errors in a language, please contact the programmers. They will change this in a next version!'), 'HuMo-genealogy');
            echo '&nbsp;';
            echo __('Translate into the right column. The untranslated items appear first.') . '</div>';
            ?>

            <table class="humo standard" style="text-align:center;">
                <tr class="table_header_large">
                    <td>
                        <?php
                        $language_editor = 'en';
                        if (
                            isset($_GET['language_editor']) and
                            (file_exists('../languages/' . $_GET['language_editor'] . '/' . $_GET['language_editor'] . '.mo'))
                        ) {
                            $language_editor = $_GET['language_editor'];
                        }
                        if (
                            isset($_POST['language_editor']) and
                            (file_exists('../languages/' . $_POST['language_editor'] . '/' . $_POST['language_editor'] . '.mo'))
                        ) {
                            $language_editor = $_POST['language_editor'];
                        }
                        echo ' <input type="hidden" name="language_editor" value="' . $language_editor . '">';
                        echo __('Language') . ': ';
                        // *** Language choice ***
                        for ($i = 0; $i < count($language_select); $i++) {
                            // *** Get language name ***
                            include(__DIR__ . '/../../languages/' . $language_select[$i] . '/language_data.php');
                            echo '<a href="../admin/index.php?page=language_editor&amp;language_editor=' . $language_select[$i] . '" style="border-right:none; background:none;">';
                            echo '<img src="../languages/' . $language_select[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '"';

                            if ($language_editor == $language_select[$i]) {
                                echo ' style="	border: solid 2px #999999;"';
                            } else {
                                echo ' style="border:none;"';
                            }
                            echo '></a> ';
                        }
                        ?>
                    </td>
                    <td style="width:50%;text-align:center;border-left:none;">
                        <?php
                        $file = '../languages/' . $language_editor . '/' . $language_editor . '.po';
                        $message = '';

                        // here php will place the "saved file xx" message en javascript will put the "saving..." message
                        echo '<div id="announce" style="color:red; font-weight:bold; display:inline;">';

                        if (isset($_POST['save_button']) and $_POST['save_button'] == "pressed") {
                            $save_array = array();
                            for ($i = 1; $i < count($_SESSION['line_array']); $i++) {
                                if (isset($_POST['txt_name' . $i])) {  // displayed items
                                    $content = str_replace("\\\\\\", "\\", $_POST['txt_name' . $i]);
                                    $content = str_replace("\\\\", "\\", $content);
                                    $_SESSION['line_array'][$i]['msgstr'] = $content;
                                    // store posted lines - these will be written to the file with the msgstr_save function.
                                    // the other ones will just get copied straight from the array
                                    $save_array[$i] = msgstr_save($content);
                                } else { // non displayed items - these will be written to the file with the msgstr_save2 function.
                                    if (isset($_SESSION['line_array'][$i]['msgstr'])) {
                                        $save_array[$i] = msgstr_save2($_SESSION['line_array'][$i]['msgstr']);
                                    }
                                }
                            }

                            $handle_write = @fopen('../languages/' . $language_editor . '/' . $language_editor . ".po", "w+");
                            if ($handle_write) {
                                for ($i = 0; $i < count($_SESSION['line_array']); $i++) {
                                    // #~ remarks need \n at end, except for last one:
                                    if (isset($_SESSION['line_array'][$i]["note"]) and $i != (count($_SESSION['line_array']) - 1) and substr($_SESSION['line_array'][$i]["note"], 0, 2) == "#~") {
                                        $_SESSION['line_array'][$i]["note"] .= "\n";
                                    }
                                    // write all types of notes:
                                    if (isset($_SESSION['line_array'][$i]["note"])) {
                                        if (strpos($_SESSION['line_array'][$i]["note"], "fuzzy") !== false and isset($_POST['txt_name' . $i]) and !isset($_POST['fuz' . $i])) {
                                            // we have to find: "#, fuzzy" as well as: "#, fuzzy, php-format" as well as: "#, php-format, fuzzy"
                                            $_SESSION['line_array'][$i]["note"] = str_replace(array("#, fuzzy\n", "fuzzy, ", ", fuzzy"), array("", "", ""), $_SESSION['line_array'][$i]["note"]);
                                        }
                                        if (strpos($_SESSION['line_array'][$i]["note"], "fuzzy") === false and isset($_POST['txt_name' . $i]) and isset($_POST['fuz' . $i])) {
                                            if (strpos($_SESSION['line_array'][$i]["note"], "#,") != false) { // there already is another #. entry --> add fuzzy
                                                $_SESSION['line_array'][$i]["note"] = str_replace("#,", "#, fuzzy,", $_SESSION['line_array'][$i]["note"]);
                                            } else {
                                                $_SESSION['line_array'][$i]["note"] .= "#, fuzzy\n";
                                            }
                                        }
                                        fwrite($handle_write, $_SESSION['line_array'][$i]["note"]);
                                    }
                                    // write msgid line:
                                    if (isset($_SESSION['line_array'][$i]["msgid"])) fwrite($handle_write, "msgid " . $_SESSION['line_array'][$i]["msgid"]);
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
                                $message = __('Saved') . ' ';
                                $message .= __('Language') . ': ' . $file;
                                echo $message;
                            } else echo "Saving failed!";
                            fclose($handle_write);

                            // *** Convert .po file into .mo file! ***
                            require(__DIR__ . '/../../admin/include/po-mo_converter/php-mo.php');
                            if (phpmo_convert($file)) {
                                //echo 'The .mo file is succesfully saved!';
                            } else {
                                echo '<br>ERROR: the .mo file IS NOT saved!<br>';
                            }
                        }

                        $line_array = array();

                        $handle = @fopen($file, "r");
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
                            echo "Can't open the language file!";
                        }

                        if (!feof($handle)) {
                            echo "Error: unexpected fgets() fail\n";
                        }
                        fclose($handle);

                        echo '</div>';
                        ?>
                    </td>
                </tr>
            </table><br>
            <?php

            if (!isset($_SESSION['maxlines'])) {
                $_SESSION['maxlines'] = 10;
            } // default
            elseif (isset($_POST['maxlines'])) {
                $_SESSION['maxlines'] = $_POST['maxlines'];
            } // user input

            if (!isset($_SESSION['present_page'])) {
                $_SESSION['present_page'] = 0;
            } // default is first page
            if (isset($_POST['prevpage'])) {
                $_SESSION['present_page'] = $_POST['to_prev_page'];
            } // previous page button pressed
            if (isset($_POST['nextpage'])) {
                $_SESSION['present_page'] = $_POST['to_next_page'];
            } // next page button pressed
            if (isset($_POST['langsearch'])) {
                $_SESSION['present_page'] = 0;
            } // after search change start with first page

            if (
                isset($_POST['maxlines']) and !isset($_POST['prevpage']) and !isset($_POST['nextpage'])
                and !isset($_POST['langsearch']) and (isset($_POST['save_button']) and $_POST['save_button'] != "pressed")
            ) {
                $_SESSION['present_page'] = 0;
            } // maxlines changed

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
            ?>

            <table class="humo" border="" cellspacing="0" width="98%" style="border-width:0px;margin-left:auto;margin-right:auto">
                <tr class="table_header_large">
                    <td style="text-align:center;color:red;font-weight:bold">
                        <!-- Page nr -->
                        <?= __('Page'); ?>: <?= ($_SESSION['present_page'] + 1); ?>
                    </td>
                    <td style="width:135px;text-align:center">
                        <!-- Next page button -->
                        <?php
                        if (isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != "") $count_lines = $search_lines;
                        else $count_lines = count($_SESSION['line_array']);
                        if (($_SESSION['present_page'] + 1) * $_SESSION['maxlines'] < $count_lines) { // only show next page button if not last page
                            echo ' <input style="font-size:100%" type="submit" name="nextpage" value="' . __('Next page') . '">';
                            echo '<input type="hidden" name="to_next_page" value="' . ($_SESSION['present_page'] + 1) . '">';
                        }
                        ?>
                    </td>
                    <td style="width:135px;text-align:center">
                        <!-- Previous page button -->
                        <?php
                        if ($_SESSION['present_page'] > 0) { // only show prev page button if not first page
                            echo ' <input style="font-size:100%" type="submit" name="prevpage" value="' . __('Previous page') . '">';
                            echo '<input type="hidden" name="to_prev_page" value="' . ($_SESSION['present_page'] - 1) . '">';
                        }
                        ?>
                    </td>
                    <td style="text-align:center">
                        <!-- Max items per page choice -->
                        <?= __('Max items per page: '); ?>
                        <select size="1" name="maxlines" style="width:50px" onChange="this.form.submit();">
                            <?php
                            $selected = "";
                            if ($_SESSION['maxlines'] == 10) $selected = " selected ";
                            echo '<option value="10" ' . $selected . '>' . '10' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 20) $selected = " selected ";
                            echo '<option value="20" ' . $selected . '>' . '20' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 30) $selected = " selected ";
                            echo '<option value="30" ' . $selected . '>' . '30' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 50) $selected = " selected ";
                            echo '<option value="50" ' . $selected . '>' . '50' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 100) $selected = " selected ";
                            echo '<option value="100" ' . $selected . '>' . '100' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 200) $selected = " selected ";
                            echo '<option value="200" ' . $selected . '>' . '200' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 300) $selected = " selected ";
                            echo '<option value="300" ' . $selected . '>' . '300' . '</option>';
                            $selected = "";
                            if ($_SESSION['maxlines'] == 400) $selected = " selected ";
                            echo '<option value="400" ' . $selected . '>' . '400' . '</option>';
                            ?>
                        </select>
                    </td>
                    <td style="text-align:center">
                        <!-- Items found -->
                        <?= __('Total items found: ') . $count_lines; ?>
                    </td>
                    <td style="text-align:center">
                        <!-- Search box -->
                        <?php
                        $langsearchtext = "";
                        if (isset($_SESSION['langsearchtext']) and $_SESSION['langsearchtext'] != "") {
                            $langsearchtext = $_SESSION['langsearchtext'];
                        }
                        echo '<input type="text" style="width:200px;background-color:#d8f0f8" name="langsearchtext" value="' . $langsearchtext . '">';
                        echo '<input style="font-size:100%" type="submit" name="langsearch" value="' . __('Search') . '">';
                        ?>
                    </td>
                    <td style="width:150px;text-align:center">
                        <!-- Save  button -->
                        <?php
                        if (@is_writable($file)) {
                            $num = count($_SESSION['line_array']);
                            echo ' <input style="font-weight:bold;font-size:130%" type="button" onClick="doit(' . $num . ');" name="save_language" value="' . __('Save') . '">';
                            echo ' <input type="hidden" name="save_button" value="">'; // will be set by javascript to flag save buton pressed
                        } else {
                            echo '<b>' . __('FILE IS NOT WRITABLE!') . '</b>';
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <?php include(__DIR__ . '/../../languages/' . $language_editor . '/language_data.php'); ?>

            <table class="humo" border="1" cellspacing="0" width="98%" style="margin-left:auto;margin-right:auto">
                <tr class="table_header_large">
                    <th style="border-right:none;width:48.5%"><?= __('Template'); ?></th>
                    <th style="font-size:85%;width:4%"><?= __('Fuzzy'); ?></th>
                    <th style="border-left:none;width:47.5%">
                        &nbsp;&nbsp;&nbsp;<?= __('Translation into') . ' ' . $language["name"]; ?>
                    </th>
                </tr>
            </table>
            <?php display_po_table(); ?>
        </div>
</form>
<?php

//******** FUNCTION display_po_table() DISPLAYS THE PO-LIKE TABLE: LEFT THE TEMPLATE VALUES AND RIGHT THE TRANSLATION *********
//******** (this is a table within the language editor table, so it can scroll under the header **********************

function display_po_table()
{
?>
    <div style="height:450px;overflow:auto">
        <table class="humo" border="1" cellspacing="0" width="98%" style="margin-left:auto;margin-right:auto">
            <?php
            $count = 0;
            $loop_count = 0;
            $found = false;
            foreach ($_SESSION['line_array'] as $key => $value) { // non-translated items
                if ($key == 0) {
                    continue;
                } // description of po file
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
                    echo '<tr><td style="width:2%">';
                    echo '<a onmouseover="popup(\'' . popclean($mytext) . '\',300);" href="#" style="border-right:none;background:none">';
                    echo '<img style="border:0px;background:none" src="../images/reports.gif" alt="references"></a>';
                    echo '</td>';
                    echo '<td style="vertical-align:top;padding:2px;width:47%">' . msgid_display($value["msgid"]) . '</td>';
                    echo '<td style="width:4%"></td>';
                    echo '<td style="width:47%;">';
                    echo '<textarea name="txt_name' . $key . '" style="display:none;visibility:none"></textarea>';
                    echo '<div contentEditable="true" id="text_msgstr' . $key . '" style="padding:2px;border:1px solid #999999;background-color:white;width:100%;height:100%;min-height:20px;font:12px Verdana, tahoma, arial, sans-serif;line-height:160%;">';
                    echo '</div></td></tr>';
                    $found = true;
                }
            }
            foreach ($_SESSION['line_array'] as $key => $value) { // translated items
                if ($key == 0) {
                    continue;
                } // description of po file
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
                    echo '<tr><td style="width:2%">';
                    echo '<a onmouseover="popup(\'' . popclean($mytext) . '\',300);" href="#">';
                    echo '<img style="border:0px;background:none" src="../images/reports.gif" alt="references"></a>';
                    echo '</td>';
                    echo '<td style="padding:2px;width:47%">' . msgid_display($value["msgid"]) . '</td>';
                    echo '<td style="text-align:center;width:4%"><input type="checkbox" value="fuzzie" name="fuz' . $value["nr"] . '" checked></td>';
                    echo '<td style="background-color:white;width:47%;">';
                    echo '<textarea name="txt_name' . $key . '" style="display:none;visibility:none"></textarea>';
                    if (strpos($mytext, "fuzzy") !== false) {
                        $bkcolor = "background-color:yellow;";
                    } else {
                        $bkcolor = "";
                    }
                    echo '<div contentEditable="true" id="text_msgstr' . $key . '" style="' . $bkcolor . 'padding:2px;border:1px solid #999999;width:100%;height:100%;font:12px Verdana, tahoma, arial, sans-serif;line-height:160%;">';
                    echo msgstr_display($value["msgstr"]);
                    echo '</div></td></tr>';
                    $found = true;
                }
            }
            foreach ($_SESSION['line_array'] as $key => $value) { // translated items
                if ($key == 0) {
                    continue;
                } // description of po file
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
                    } else $mytext = "";
                    echo '<tr><td style="width:2%">';
                    echo '<a onmouseover="popup(\'' . popclean($mytext) . '\',300);" href="#">';
                    echo '<img style="border:0px;background:none" src="../images/reports.gif" alt="references"></a>';
                    echo '</td>';
                    echo '<td style="padding:2px;width:47%">' . msgid_display($value["msgid"]) . '</td>';
                    echo '<td style="text-align:center;width:4%"><input type="checkbox" value="fuzzie" name="fuz' . $value["nr"] . '"></td>';
                    echo '<td style="background-color:white;width:47%;">';
                    echo '<textarea name="txt_name' . $key . '" style="display:none;visibility:none"></textarea>';
                    if (strpos($mytext, "fuzzy") !== false) {
                        $bkcolor = "background-color:yellow;";
                    } else {
                        $bkcolor = "";
                    }
                    echo '<div contentEditable="true" id="text_msgstr' . $key . '" style="' . $bkcolor . 'padding:2px;border:1px solid #999999;width:100%;height:100%;font:12px Verdana, tahoma, arial, sans-serif;line-height:160%;">';
                    echo msgstr_display($value["msgstr"]);
                    echo '</div></td></tr>';
                    $found = true;
                }
            }
            if ($found === false) {
                echo '<tr><td colspan="3"><span style="color:red">' . __('No results found') . '</span></td></tr>';
            }
            ?>
        </table>
        <br><br><br>
    </div>
<?php
}

//**** SOME FORMAT FUNCTIONS: ****

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

// NOTE (Y.B.)
// This is a javascript workaround.
// This javascript takes the content of the writable divs (msgstr entries) and places them 
// in the parallel hidden textareas so they can be collected by $_POST
// The reason we don't just use textareas is that in FF and IE they don't dynamically stretch to the height of the table cell.
// Their height has to be set explicitly and since we have dynamic text that doesn't work.
// (Chrome is the exception. It automatically expands the textareas. 10 points for Chrome). 
// If one day IE and FF join up with Chrome, we can re-write this script with plain textareas.
?>
<script>
    function notice() {
        document.getElementById("announce").innerHTML = "<?= __('Saving') . "...."; ?> ";
    }

    function saveLanguage(num) {
        for (var i = 1; i < num; i++) {
            var div_content = "";
            if (document.getElementById('text_msgstr' + i)) {
                div_content = document.getElementById('text_msgstr' + i).innerHTML;
            }
            var textareaname = "txt_name" + i;
            if (eval('document.forms["saveform"].' + textareaname)) {
                var longstring = eval('document.forms["saveform"].' + textareaname);
                longstring.value = div_content;
            }
        }
        document.forms["saveform"].save_button.value = "pressed";
        document.forms["saveform"].submit();
    }

    function doit(num) {
        notice();
        saveLanguage(num);
    }
</script>