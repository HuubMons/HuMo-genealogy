<?php

namespace Genealogy\Include;

use Genealogy\Include\SafeTextDb;
use Genealogy\Include\ValidateDate;
use PDO;

class Editor_cls
{
    // *** Date functions ***
    // 13 OCT 1813 = 13 okt 1813
    // BEF 2000 = bef 2000
    // ABT 2000 = abt 2000
    // AFT 2000 = aft 2000
    // BET 1986 AND 1987 = bet 1986 and 1987

    // *** $multiple_rows = addition for editing in multiple rows. Example: name = "event_date[]" ***
    public function date_show($process_date, $process_name, $multiple_rows = '', $hebnight = 'n', $hebvar = ''): void
    {
        // *** Process BEF, ABT, AFT and BET in an easier pulldown menu ***
        global $field_date, $humo_option;
        $style = '';

        // *** Prevent error in PHP 8.1.1 ***
        if (!isset($process_date)) {
            $process_date = '';
        }

        // *** Show month in selected language ***
        $process_date = str_replace("JAN", __('jan'), $process_date);
        $process_date = str_replace("FEB", __('feb'), $process_date);
        $process_date = str_replace("MAR", __('mar'), $process_date);
        $process_date = str_replace("APR", __('apr'), $process_date);
        $process_date = str_replace("MAY", __('may'), $process_date);
        $process_date = str_replace("JUN", __('jun'), $process_date);
        $process_date = str_replace("JUL", __('jul'), $process_date);
        $process_date = str_replace("AUG", __('aug'), $process_date);
        $process_date = str_replace("SEP", __('sep'), $process_date);
        $process_date = str_replace("OCT", __('oct'), $process_date);
        $process_date = str_replace("NOV", __('nov'), $process_date);
        $process_date = str_replace("DEC", __('dec'), $process_date);
        $process_date = str_replace(" AND ", __(' and '), $process_date);

        // *** Strip tags BEF, ABT, AFT, etc. are allready shown in date_prefix. Variable $text_value must be case sensitive. ***
        $process_date2 = strtolower($process_date);
        $text_value = '';
        if (substr($process_date2, 0, 4) === 'bef ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'abt ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'aft ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'bet ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'int ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'est ') {
            $text_value = substr($process_date, 4);
        } elseif (substr($process_date2, 0, 4) === 'cal ') {
            $text_value = substr($process_date, 4);
        } else {
            $text_value = $process_date;
        }

        // *** Show BC with uppercase, check case-insensitive ***
        if (strtolower(substr($process_date, -3)) === ' bc') {
            $process_date = substr($process_date, 0, -3) . ' BC';
        }
        if (strtolower(substr($process_date, -5)) === ' b.c.') {
            $process_date = substr($process_date, 0, -5) . ' B.C.';
        }

        // *** '!' is added after an invalid date, change background color if date is invalid ***
        $style = '';
        if (substr($process_date, -1) === '!') {
            $process_date = substr($process_date, 0, -1);
            $style = '; background-color:red"';
        }
?>
        <div class="input-group">
            <select size="1" id="<?= $process_name . '_prefix' . $multiple_rows; ?>" name="<?= $process_name . '_prefix' . $multiple_rows; ?>" class="form-select form-select-sm">
                <option value="">=</option>
                <option value="BEF " <?= substr($process_date, 0, 4) === 'BEF ' ? 'selected' : ''; ?>><?= __('before'); ?></option>
                <option value="ABT " <?= substr($process_date, 0, 4) === 'ABT ' ? 'selected' : ''; ?>><?= __('&#177;'); ?></option>
                <option value="AFT " <?= substr($process_date, 0, 4) === 'AFT ' ? 'selected' : ''; ?>><?= __('after'); ?></option>
                <option value="BET " <?= substr($process_date, 0, 4) === 'BET ' ? 'selected' : ''; ?>><?= __('between'); ?></option>
                <!-- New added april 2020 -->
                <option value="INT " <?= substr($process_date, 0, 4) === 'INT ' ? 'selected' : ''; ?>><?= __('interpreted'); ?></option>
                <option value="EST " <?= substr($process_date, 0, 4) === 'EST ' ? 'selected' : ''; ?>><?= __('estimated'); ?></option>
                <option value="CAL " <?= substr($process_date, 0, 4) === 'CAL ' ? 'selected' : ''; ?>><?= __('calculated'); ?></option>
            </select>

            <input type="text" name="<?= $process_name . $multiple_rows; ?>" style="direction:ltr<?= $style; ?>" value="<?= $text_value; ?>" size="<?= $field_date; ?>" class="form-control form-control-sm">
        </div>

        <?php
        // user wants checkbox for jewish setting of events after nightfall for specific events AND it is to be placed with this event
        if ($humo_option['admin_hebnight'] == "y" && $hebnight != 'n') {
        ?>
            <span style="white-space: nowrap">
                <input type="checkbox" id="<?= $hebvar; ?>" value="y" name="<?= $hebvar; ?>" <?= $hebnight == 'y' ? 'checked' : ''; ?> class="form-check-input"> <label for="<?= $hebvar; ?>"><?= __('After nightfall'); ?></label>
            </span>
<?php
        }
    }

    /*
    public function hebrew_after_nightfall($hebnight = 'n', $hebvar = '')
    {
        global $humo_option;
        // user wants checkbox for jewish setting of events after nightfall for specific events AND it is to be placed with this event
        if ($humo_option['admin_hebnight'] == "y" and $hebnight != 'n') {
        ?>
            <span style="white-space: nowrap">
                <input type="checkbox" id="<?= $hebvar; ?>" value="y" name="<?= $hebvar; ?>" <?= $hebnight == 'y' ? 'checked' : ''; ?>> <label for="<?= $hebvar; ?>"><?= __('After nightfall'); ?></label>
            </span>
<?php
        }
    }
    */

    public function date_process($process_name, $multiple_rows = '')
    {
        $safeTextDb = new SafeTextDb();

        // *** Save "before", "about", "after" texts before a date ***
        $process_name_prefix = $process_name . '_prefix';

        // *** Just for sure: remove spaces at beginning and end of date ***
        if ($multiple_rows != '') {
            $post_date = trim($_POST[$process_name][$multiple_rows]);
            $pref = $_POST["$process_name_prefix"][$multiple_rows];
        } else {
            //echo $_POST[$process_name].'?';
            $post_date = trim($_POST[$process_name]);
            $pref = $_POST["$process_name_prefix"];
        }
        $this_date = "";

        $post_date = str_replace(__(' and '), ' AND ', $post_date); // *** Use selected language for text "and" ***
        $pos = strpos(strtoupper($post_date), "AND");
        if ($pos !== false) {
            if ($pref == "BET ") {
                // we've got "BET" and "AND"
                $date1 = $this->valid_date(substr($post_date, 0, $pos - 1));
                $date2 = $this->valid_date(substr($post_date, $pos + 4));
                if ($date1 != null && $date2 != null) {
                    $this_date = $date1 . " AND " . $date2;
                }
                //else $this_date = __('Invalid date'); // one or both dates are invalid
                else $this_date = '!'; // one or both dates are invalid
            }
            //else $this_date = __('Invalid date'); // "AND" appears but not with "BET"
            else $this_date = '!'; // "AND" appears but not with "BET"
        } elseif ($pref == "BET " and $pos === false) {
            //$this_date = __('Invalid date'); // "BET" appears but not with "AND"
            $this_date = '!'; // "BET" appears but not with "AND"
        } elseif ($post_date != "") {
            $date = $this->valid_date($post_date);
            if ($date != null) {
                $this_date = $date;
            }
            //else $this_date = __('Invalid date'); 
            else $this_date = '!';
        }

        if ($multiple_rows != '')
            //$process_date=$_POST["$process_name_prefix"][$multiple_rows].$_POST["$process_name"][$multiple_rows];
            $process_date = $pref . $this_date;
        else
            //$process_date=$_POST["$process_name_prefix"].$_POST["$process_name"];
            $process_date = $pref . $this_date;

        // *** Invalid date, add a ! character after the date. Don't remove original date... ***
        if (substr($post_date, -1) === '!') {
            $process_date = $post_date;
        } elseif ($this_date == '!') {
            $process_date = $post_date . '!';
        }

        $process_date = strtoupper($process_date);
        return $safeTextDb->safe_text_db($process_date);
    }

    public function valid_date($date)
    {
        $validateDate = new ValidateDate;

        // German date input: 01.02.2016 or Scandinavian input: 01,02,2016
        $date2 = str_replace(" B.C.", "", $date); // Don't check . in B.C.!
        if (strpos($date2, ".") !== false) {
            $date = str_replace(".", "-", $date);
        }
        if (strpos($date, ",") !== false) {
            $date = str_replace(",", "-", $date);
        }

        // Use your own language for input, FULL MONTH NAMES
        $search  = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
        $replace = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
        $date = str_replace($search, $replace, ucwords($date));

        // $date=strtolower($date); // Do NOT change case of $date because of German date using a first uppercase: Dez
        // 21-06-2022: Changed str_replace by str_ireplace.
        // Use your own language for input, SHORT MONTH NAMES
        $date = str_ireplace(__('jan'), "JAN", $date);
        $date = str_ireplace(__('feb'), "FEB", $date);
        $date = str_ireplace(__('mar'), "MAR", $date);
        $date = str_ireplace(__('apr'), "APR", $date);
        $date = str_ireplace(__('may'), "MAY", $date);
        $date = str_ireplace(__('jun'), "JUN", $date);
        $date = str_ireplace(__('jul'), "JUL", $date);
        $date = str_ireplace(__('aug'), "AUG", $date);
        $date = str_ireplace(__('sep'), "SEP", $date);
        $date = str_ireplace(__('oct'), "OCT", $date);
        $date = str_ireplace(__('nov'), "NOV", $date);
        $date = str_ireplace(__('dec'), "DEC", $date);

        // date entered as 01-04-2013 or 01/04/2013
        if ((strpos($date, "-") !== false || strpos($date, "/") !== false) && strpos($date, " ") === false) {
            // skips "2 mar 1741/42" and "mar 1741/42"
            $delimiter = strpos($date, "-") !== false ? "-" : "/";
            $date_dash = explode($delimiter, $date);
            if (count($date_dash) == 2) {
                // date was entered as month and year: 4-2011 or 4/2011 or we have case of "1741/42" (just year no day/month)
                if ($date_dash[0] > $date_dash[1]) {
                    $member = "none"; // "1741/42" so don't perform transformation
                    $this_date = $date;
                } else {
                    $member = 0; // first member of array is month
                }
            } else {
                $member = 1; // second member of array is month
            }
            if ($member != "none") {
                if ($date_dash[$member] === "1" || $date_dash[$member] === "01") {
                    $date_dash[$member] = "JAN";
                } elseif ($date_dash[$member] === "2" || $date_dash[$member] === "02") {
                    $date_dash[$member] = "FEB";
                } elseif ($date_dash[$member] === "3" || $date_dash[$member] === "03") {
                    $date_dash[$member] = "MAR";
                } elseif ($date_dash[$member] === "4" || $date_dash[$member] === "04") {
                    $date_dash[$member] = "APR";
                } elseif ($date_dash[$member] === "5" || $date_dash[$member] === "05") {
                    $date_dash[$member] = "MAY";
                } elseif ($date_dash[$member] === "6" || $date_dash[$member] === "06") {
                    $date_dash[$member] = "JUN";
                } elseif ($date_dash[$member] === "7" || $date_dash[$member] === "07") {
                    $date_dash[$member] = "JUL";
                } elseif ($date_dash[$member] === "8" || $date_dash[$member] === "08") {
                    $date_dash[$member] = "AUG";
                } elseif ($date_dash[$member] === "9" || $date_dash[$member] === "09") {
                    $date_dash[$member] = "SEP";
                } elseif ($date_dash[$member] === "10") {
                    $date_dash[$member] = "OCT";
                } elseif ($date_dash[$member] === "11") {
                    $date_dash[$member] = "NOV";
                } elseif ($date_dash[$member] === "12") {
                    $date_dash[$member] = "DEC";
                }

                $this_date = implode(" ", $date_dash);
            }
        } else {
            $this_date = $date;
        }
        $result = $validateDate->check_date(strtoupper($this_date));
        if ($result == null) {
            return null;
        } else {
            return $this_date;
        }
    }

    public function text_process($text, $long_text = false)
    {
        // TODO check if this is still needed.
        if ($long_text == true) {
            $text = str_replace("\r\n", "\n", $text);
        }
        return $text;
    }

    // *** Show texts without <br> and process Aldfaer and other @xx@ texts ***
    public function text_show($find_text)
    {
        global $dbh, $tree_id;
        if ($find_text != '') {
            $text = $find_text;
            if (substr($find_text, 0, 1) === '@') {
                $search_text = $dbh->query("SELECT * FROM humo_texts
                    WHERE text_tree_id='" . $tree_id . "' AND text_gedcomnr='" . substr($find_text, 1, -1) . "'");
                $search_textDb = $search_text->fetch(PDO::FETCH_OBJ);

                if (isset($search_textDb->text_text)) {
                    $text = $search_textDb->text_text;
                }
                $text = str_replace("<br>", "<br>\n", $text);
            }
            return str_replace("<br>", "", $text);
        }
        return null;
    }

    public function show_selected_person($person)
    {
        $languageDate = new LanguageDate;

        $text = __('N.N.');
        if ($person) {
            $prefix1 = '';
            $prefix2 = '';
            //if($user['group_kindindex']=="j") {
            //	$prefix1=strtolower(str_replace("_"," ",$person->pers_prefix));
            //}
            //else {
            $prefix2 = " " . strtolower(str_replace("_", " ", $person->pers_prefix));
            //}

            $text = '[' . $person->pers_gedcomnumber . '] ' . $prefix1 . $person->pers_lastname . ', ' . $person->pers_firstname . $prefix2 . ' ';

            if ($person->pers_birth_date) {
                $text .= __('*') . ' ' . $languageDate->language_date($person->pers_birth_date);
            }
            if (!$person->pers_birth_date && $person->pers_bapt_date) {
                $text .= __('~') . ' ' . $languageDate->language_date($person->pers_bapt_date);
            }
            if ($person->pers_death_date) {
                if ($text) {
                    $text .= ' ';
                }
                $text .= __('&#134;') . ' ' . $languageDate->language_date($person->pers_death_date);
            }
            if (!$person->pers_death_date and $person->pers_buried_date) {
                if ($text) {
                    $text .= ' ';
                }
                $text .= __('[]') . ' ' . $languageDate->language_date($person->pers_buried_date);
            }
        }
        return ($text);
    }
}
