<?php

/**
 * Process texts.
 * 
 * Process hidden text (worktext).
 * Process optional text popup.
 * Convert links into clickable links.
 */

namespace Genealogy\Include;

use PDO;

class ProcessText
{
    public function process_text($text_process, $text_sort = 'standard'): string
    {
        global $dbh, $tree_id, $user, $screen_mode, $data;

        if (!isset($data["text_presentation"])) {
            $data["text_presentation"] = '';
        }
        if (!isset($data["picture_presentation"])) {
            $data["picture_presentation"] = '';
        }

        if ($data["text_presentation"] != 'hide' && $text_process) {
            //1 NOTE Text by person#werktekst#
            //2 CONT 2e line text persoon#2e werktekst#
            //2 CONT 3e line #3e werktekst# tekst persoon

            // *** If multiple texts are read, a | seperator character is added ***
            // *** Split the text, and check for @Nxx@ texts ***
            $text_pieces = explode("|", $text_process);
            $text_result = '';
            for ($i = 0; $i <= (count($text_pieces) - 1); $i++) {
                // *** Search for Aldfaer texts ***
                if (substr($text_pieces[$i], 0, 1) === '@') {
                    $text_check = substr($text_pieces[$i], 1, -1);
                    $qry = "SELECT * FROM humo_texts WHERE text_tree_id = :tree_id AND text_gedcomnr = :gedcomnr";
                    $stmt = $dbh->prepare($qry);
                    $stmt->execute([
                        ':tree_id' => $tree_id,
                        ':gedcomnr' => $text_check
                    ]);
                    $search_text = $stmt;
                    $search_textDb = $search_text->fetch(PDO::FETCH_OBJ);
                    if ($text_result) {
                        $text_result .= '<br>';
                    }
                    if (isset($search_textDb->text_text)) {
                        $text_result .= $search_textDb->text_text;
                    }
                } else {
                    if ($text_result) {
                        $text_result .= '<br>';
                    }
                    $text_result .= $text_pieces[$i];
                }
            }
            if ($text_result) {
                $text_process = $text_result;
            }

            // *** If needed strip worktext (used in Haza-Data) ***
            if ($user['group_work_text'] == 'n') {
                // *** Added a '!' sign to prevent '0' detection. The routine will stop then! ***
                $text_process = "!" . $text_process;
                while (strpos($text_process, '#') > 0) {
                    $first = strpos($text_process, '#');
                    $text1 = substr($text_process, 0, $first);
                    $text_process = substr($text_process, $first + 1);

                    $second = strpos($text_process, '#');
                    $text2 = substr($text_process, $second + 1);

                    $text_process = $text1 . $text2;
                }
                // *** Strip added '!' sign ***
                $text_process = substr($text_process, 1);
            }

            // *** Convert all url's in a text to clickable links ***
            $text_process = preg_replace("#(^|[ \n\r\t])www.([a-z\-0-9]+).([a-z]{2,4})($|[ \n\r\t])#mi", "\\1<a href=\"http://www.\\2.\\3\" target=\"_blank\">www.\\2.\\3</a>\\4", $text_process);
            //$text_process = preg_replace("#(^|[ \n\r\t])(((ftp://)|(http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text_process);
            $text_process = preg_replace("#(^|[ \n\r\t])(((http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text_process);

            if ($text_process) {
                if (strpos($text_process, "</table>") !== false) {
                    // if the text contains an html table make sure it doesn't get <br>
                    $text_process = preg_replace('~\R~u', "\n", $text_process); // first make sure all type newline combinations (\r, \r\n etc) will be \n
                    $txt_arr = explode("\n", $text_process);
                    $flag_table = 0;
                    $new_text_process = '';
                    foreach ($txt_arr as $value) {
                        if (strpos($value, "<table") !== false) {
                            $flag_table = 1;
                        } elseif (strpos($value, "</table>") !== false) {
                            // we're leaving table -> table flag OFF
                            $flag_table = 0;
                        }
                        if ($flag_table == 1) {
                            $new_text_process .= $value;
                        } else {
                            // add a <br> to the non-table lines
                            $new_text_process .= $value . "<br>";
                        }
                    }
                    $text_process = $new_text_process;
                } else {
                    $text_process = nl2br($text_process);
                }
            }

            if ($text_process) {
                if ($screen_mode == 'RTF') {
                    $text_process = '<i>' . $text_process . '</i>';
                } else {
                    $text_process = '<span class="text">' . $text_process . '</span>';
                }
            }

            // *** Show tekst in popup screen (don't use popup text for media) ***
            if ($data["text_presentation"] == 'popup' && $screen_mode != 'PDF' && $screen_mode != 'RTF' && $text_process && $text_sort != 'media') {
                // *** Used for general person and general marriage text ***
                if ($text_sort == 'standard') {
                    $outline = 'outline-';
                } else {
                    $outline = '';
                }

                // Use larger textbox if long text is shown.
                $width = 400;
                if (strlen($text_process) > 1000) {
                    $width = 1000;
                }

                // *** Show a correct website link in text, code from old popup script ***
                //$text_process = str_ireplace('<a href', '<a style="display:inline" href', $text_process);

                $text2 = '<div class="dropdown dropend d-inline ms-2 overflow-auto">';
                $text2 .= '<button class="btn btn-sm btn-' . $outline . 'secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-line-height: 1;">' . __('Text') . '</button>';
                $text2 .= '<ul class="dropdown-menu p-2" style="width:' . $width . 'px;">';
                $text2 .= $text_process;
                $text2 .= '</ul>';
                $text2 .= '</div>';
                $text_process = $text2;
            }
        } else {
            $text_process = '';
        }

        return $text_process;
    }
}
