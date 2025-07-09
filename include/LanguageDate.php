<?php
//declare(strict_types=1);

namespace Genealogy\Include;

class LanguageDate
{
    public function language_date($date_text = ''): string
    {
        global $humo_option, $selected_language;

        if ($date_text == NULL || $date_text == '') {
            return '';
        }
        $date_text = strtoupper($date_text);

        if ($humo_option["date_display"] == "ch" && $selected_language != "hu") {
            $date_text = str_replace("JAN", "01", $date_text);
            $date_text = str_replace("FEB", "02", $date_text);
            $date_text = str_replace("MAR", "03", $date_text);
            $date_text = str_replace("APR", "04", $date_text);
            $date_text = str_replace("MAY", "05", $date_text);
            $date_text = str_replace("JUN", "06", $date_text);
            $date_text = str_replace("JUL", "07", $date_text);
            $date_text = str_replace("AUG", "08", $date_text);
            $date_text = str_replace("SEP", "09", $date_text);
            $date_text = str_replace("OCT", "10", $date_text);
            $date_text = str_replace("NOV", "11", $date_text);
            $date_text = str_replace("DEC", "12", $date_text);
        } elseif ($selected_language != "hu") {
            $date_text = str_replace("JAN", __('jan'), $date_text);
            $date_text = str_replace("FEB", __('feb'), $date_text);
            $date_text = str_replace("MAR", __('mar'), $date_text);
            $date_text = str_replace("APR", __('apr'), $date_text);
            $date_text = str_replace("MAY", __('may'), $date_text);
            $date_text = str_replace("JUN", __('jun'), $date_text);
            $date_text = str_replace("JUL", __('jul'), $date_text);
            $date_text = str_replace("AUG", __('aug'), $date_text);
            $date_text = str_replace("SEP", __('sep'), $date_text);
            $date_text = str_replace("OCT", __('oct'), $date_text);
            $date_text = str_replace("NOV", __('nov'), $date_text);
            $date_text = str_replace("DEC", __('dec'), $date_text);
        } else {
            $date_text = str_replace("JAN", __('January'), $date_text);
            $date_text = str_replace("FEB", __('February'), $date_text);
            $date_text = str_replace("MAR", __('March'), $date_text);
            $date_text = str_replace("APR", __('April'), $date_text);
            $date_text = str_replace("MAY", __('May'), $date_text);
            $date_text = str_replace("JUN", __('June'), $date_text);
            $date_text = str_replace("JUL", __('July'), $date_text);
            $date_text = str_replace("AUG", __('August'), $date_text);
            $date_text = str_replace("SEP", __('September'), $date_text);
            $date_text = str_replace("OCT", __('October'), $date_text);
            $date_text = str_replace("NOV", __('November'), $date_text);
            $date_text = str_replace("DEC", __('December'), $date_text);
        }

        if ($humo_option["date_display"] == "us" || $humo_option["date_display"] == "ch" || $selected_language == "hu") {
            $prfx = ''; // prefix
            if (strpos($date_text, "EST ABT") !== false) {
                $prfx = __('estimated &#177;');
                $date_text = str_replace("EST ABT ", "", $date_text);
            } elseif (strpos($date_text, "CAL ABT") !== false) {
                $prfx = __('calculated &#177;');
                $date_text = str_replace("CAL ABT ", "", $date_text);
            } elseif (strpos($date_text, "AFT") !== false) {
                $prfx = __('after');
                $date_text = str_replace("AFT ", "", $date_text);
            } elseif (strpos($date_text, "ABT") !== false) {
                $prfx = __('&#177;');
                $date_text = str_replace("ABT ", "", $date_text);
            } elseif (strpos($date_text, "BEF") !== false) {
                $prfx = __('before');
                $date_text = str_replace("BEF ", "", $date_text);
            } elseif (strpos($date_text, "EST") !== false) {
                $prfx = __('estimated');
                $date_text = str_replace("EST ", "", $date_text);
            } elseif (strpos($date_text, "CAL") !== false) {
                $prfx = __('calculated');
                $date_text = str_replace("CAL ", "", $date_text);
            }
            if (strpos($date_text, "BET") === false && strpos($date_text, "BETWEEN") === false) {
                if ($humo_option["date_display"] == "us" && $selected_language != "hu") {
                    $date_text = $this->american_date($date_text);
                    $date_text = $prfx . " " . $date_text;
                } elseif ($humo_option["date_display"] == "ch" && $selected_language != "hu") {
                    $date_text = $this->chinese_date($date_text);
                    $date_text = $prfx . " " . $date_text;
                } else { // Hungarian display
                    $date_text = $this->hungarian_date($date_text);
                    if ($prfx == __('before') || $prfx == __('after')) {
                        $date_text = $date_text . " " . $prfx;
                    } else {
                        if ($prfx == __('estimated &#177;')) {
                            $prfx = __('estimated');
                        }
                        $date_text = $prfx . " " . $date_text;
                    }
                }
            } else {
                $find = array("BET ", "BETWEEN ");
                $replace = array("", "");
                $date_text = str_replace($find, $replace, $date_text);
                $date_text = str_replace(" AND ", "!", $date_text);
                $date_arr = explode("!", $date_text);
                if ($humo_option["date_display"] == "us" && $selected_language != "hu") {
                    $date_arr[0] = $this->american_date($date_arr[0]);
                    $date_arr[1] = $this->american_date($date_arr[1]);
                    $date_text = __('between') . " " . $date_arr[0] . " " . __('and') . " " . $date_arr[1];
                } elseif ($humo_option["date_display"] == "ch" && $selected_language != "hu") {
                    $date_arr[0] = $this->chinese_date($date_arr[0]);
                    $date_arr[1] = $this->chinese_date($date_arr[1]);
                    $date_text = __('between') . " " . $date_arr[0] . " " . __('and') . " " . $date_arr[1];
                } else { // Hungarian display: "between" at the end
                    $date_arr[0] = $this->hungarian_date($date_arr[0]);
                    $date_arr[1] = $this->hungarian_date($date_arr[1]);
                    $date_text = $date_arr[0] . " " . __('and') . " " . $date_arr[1] . " " . __('between');
                }
            }
        } else {
            $date_text = str_replace("EST ABT", __('estimated &#177;'), $date_text);
            $date_text = str_replace("CAL ABT", __('calculated &#177;'), $date_text);

            $date_text = str_replace("AFT", __('after'), $date_text);
            $date_text = str_replace("ABT", __('&#177;'), $date_text);
            $date_text = str_replace("BEF", __('before'), $date_text);
            $date_text = str_replace("BETWEEN", "BET", $date_text);
            $date_text = str_replace("BET", __('between'), $date_text);
            $date_text = str_replace("EST", __('estimated'), $date_text);
            $date_text = str_replace("CAL", __('calculated'), $date_text);
            $date_text = str_replace("INT", __('interpreted'), $date_text);
            $date_text = str_replace("AND", __('and'), $date_text);

            // *** Aldfaer items ***
            $date_text = str_replace("FROM", __('from'), $date_text);
            $date_text = str_replace("TO", __('to'), $date_text);
        }
        return $date_text;
    }

    // *** Added feb. 2024. Function to show new_datetime and changed_datetime ***
    public function show_datetime($datetime): string
    {
        // *** Is used in new_datetime if date was missing: 1970-01-01 00:00:01 ***
        if ($datetime && $datetime != '1970-01-01 00:00:01') {
            $datetime = $this->language_date(date('d M Y - H:i:s', (strtotime($datetime))));
        } else {
            $datetime = '';
        }
        return $datetime;
    }

    private function chinese_date($date_text): string
    {
        $date_arr = explode(" ", $date_text);
        $date_text = '';
        for ($i = count($date_arr) - 1; $i >= 0; $i--) {
            if (mb_strlen($date_arr[$i]) === 1) {
                $date_arr[$i] = "0" . $date_arr[$i];
            }
            $date_text .= $date_arr[$i] . "-";
        }
        return substr($date_text, 0, -1);
    }

    private function american_date($date_text): string
    {
        $date_arr = explode(" ", $date_text);
        $date_text = '';
        if (count($date_arr) == 1) {
            // only year: 1998
            $date_text = $date_arr[0];
        } elseif (count($date_arr) == 2) {
            // month and year: Dec 1998
            $date_text = $date_arr[0] . " " . $date_arr[1];
        } else {
            // full date: Dec 12, 1998
            $date_text = $date_arr[1] . " " . $date_arr[0] . ", " . $date_arr[2];
        }
        return $date_text;
    }

    private function hungarian_date($date_text): string
    {
        $date_arr = explode(" ", $date_text);
        $date_text = '';
        if (count($date_arr) == 1) {
            // only year: 1998
            $date_text = $date_arr[0];
        } elseif (count($date_arr) == 2) {
            // month and year: 1998. április
            $date_text = $date_arr[1] . ". " . $date_arr[0];
        } else {
            // full date: 1998. április 12.
            if ($date_arr[0] < 10) {
                $date_arr[0] = "0" . $date_arr[0];
            }
            $date_text = $date_arr[2] . ". " . $date_arr[1] . " " . $date_arr[0] . ".";
        }
        return $date_text;
    }
}
