<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

$screen_mode = 'ASPDF';
$pdf_source = array();  // is set in show_sources with sourcenr as key to be used in source appendix

// TODO create seperate controller script.
$get_ancestor = new \Genealogy\App\Model\AncestorModel($config);
//$data["main_person"] = $get_ancestor->getMainPerson2('');
$data["main_person"] = $get_ancestor->getMainPerson('');
$rom_nr = $get_ancestor->getNumberRoman();

$db_functions->set_tree_id($tree_id);

$get_ancestors = $get_ancestor->get_ancestors2($data["main_person"]);
$data = array_merge($data, $get_ancestors);



// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

// this function parses the input string to see how many lines it would take in the ancestor sheet box
// it forces linebreaks when max nr of chars is encountered
// if given a value for $trunc (the max nr of lines) it will truncate the string when max lines are reached
// $str = the input string
// $width = width of box (in characters)
function parse_line($str, $width, $trunc, $bold = ""): array
{
    global $pdf;
    //$result_array = $array();
    $count = 1; //counts lines;
    $pos = 0; // checks position of blank
    if ($bold == "B") {
        $width -= 5;
    }
    $w = $width;
    $nl = 0;
    for ($x = 0; $x < strlen($str); $x++) {
        if ($str[$x] == ' ') {
            $pos = $x;
        }
        if (ceil($pdf->GetStringWidth(substr($str, $nl, ($x - $nl) + 1))) >= $w) {
            $count++;
            if ($trunc != 0 && $count > $trunc) {
                $result_array[0] = $trunc;
                $result_array[1] = substr($str, 0, $x - 1);
                return $result_array;
            }
            $str[$pos] = "\n";
            $x = $pos + 1;
            $nl = $pos + 1;
        }
    }
    $result_array[0] = $count;
    $result_array[1] = $str;
    return $result_array;
}


// the function data_array fills a multi dimensional array used later to display the PDF ancestor sheet
// first dimension contains $id, second dimension members [0-5] contain: name, birth date and place, death date and place, nr of lines
$data_array = array();
$base_sex = "M";

// if people changed the death sign from a cross to something else it will be shown as ~ (also shown in legend)
// (since the PDF font does not support most other signs such as infinity...) 
if (__('&#134;') == '&#134;' or __('&#134;') == "†") {
    $dsign = "†";
} else $dsign = "~";

function data_array($id, $width, $height): void
{
    global $db_functions, $data_array, $data, $dsign;

    $personPrivacy = new \Genealogy\Include\PersonPrivacy();
    $personName = new \Genealogy\Include\PersonName();
    $languageDate = new \Genealogy\Include\LanguageDate();

    if (isset($data["gedcomnumber"][$id]) && $data["gedcomnumber"][$id] != "") {
        $personDb = $db_functions->get_person($data["gedcomnumber"][$id]);
        $pers_privacy = $personPrivacy->get_privacy($personDb);

        $names = $personName->get_person_name($personDb, $pers_privacy);
        $name = $names["name"];

        if (preg_match('/[A-Za-z]/', $name)) {
            $result = parse_line($name, $width, 0, "B");
            $name_len = $result[0];
            $name = $result[1];
        } else {
            $name = __('N.N.');
            $name_len = 1;
        }

        $birth = '';
        $space = '';

        if ($personDb->pers_birth_date != '' || $personDb->pers_birth_place != '') {
            if ($pers_privacy) {
                $birth = __("PRIVACY FILTER");
                $birth_len = 1;
            } else {
                if ($personDb->pers_birth_date != '') {
                    $space = ' ';
                }
                //$birth = __('*').' '.$personDb->pers_birth_date.$space.$personDb->pers_birth_place;
                $birth = __('*') . ' ' . $languageDate->language_date($personDb->pers_birth_date) . $space . $personDb->pers_birth_place;
                $result = parse_line($birth, $width, 0);
                $birth_len = $result[0];
                $birth = $result[1];
            }
        } else {
            $birth_len = 0;
        }
        $death = '';
        $space = '';
        if ($personDb->pers_death_date != '' || $personDb->pers_death_place != '') {
            if ($pers_privacy) {
                if ($birth != __("PRIVACY FILTER")) {
                    $death = __("PRIVACY FILTER");
                    $death_len = 1;
                } else {
                    $death_len = 0;
                }
            } else {
                if ($personDb->pers_death_date != '') {
                    $space = ' ';
                }
                $death = $dsign . ' ' . $languageDate->language_date($personDb->pers_death_date) . $space . $personDb->pers_death_place;
                $result = parse_line($death, $width, 0);
                $death_len = $result[0];
                $death = $result[1];
            }
        } else {
            $death_len = 0;
        }

        // now start adjusting the strings to make sure no more than $height lines will be displayed in box
        // name gets priority if extra space is available (= if birth or death take up less than 2 lines)
        if ($name_len < 3) {
            $data_array[$id][0] = $name;
            $data_array[$id][3] = $name_len;
        } else {
            $rest = min(2, $birth_len) + min(2, $death_len);
            if ($name_len <= $height - $rest) {
                $data_array[$id][0] = $name;
                $data_array[$id][3] = $name_len;
            } else {
                // too long: try with initials
                $result = parse_line($names['short_firstname'], $width, 0);
                $name_len = $result[0];
                if ($name_len <= $height - $rest) {
                    $data_array[$id][0] = $result[1];
                    $data_array[$id][3] = $name_len;
                } else {
                    // still too long: truncate
                    $result = parse_line($names['short_firstname'], $width, $height - $rest);
                    $name_len = $result[0];
                    $data_array[$id][0] = $result[1];
                    $data_array[$id][3] = $result[0];
                }
            }
        }

        if ($birth_len < 3) {
            $data_array[$id][1] = $birth;
            $data_array[$id][4] = $birth_len;
        } else {
            $rest = $name_len + min(2, $death_len);
            if ($birth_len <= $height - $rest) {
                $data_array[$id][1] = $birth;
                $data_array[$id][4] = $birth_len;
            } else {
                // too long: truncate
                $result = parse_line(str_replace("\n", " ", $birth), $width, $height - $rest);
                $birth_len = $result[0];
                $data_array[$id][1] = $result[1];
                $data_array[$id][4] = $result[0];
            }
        }

        if ($death_len < 3) {
            $data_array[$id][2] = $death;
            $data_array[$id][5] = $death_len;
        } else {
            $rest = $name_len + $birth_len;
            if ($death_len <= $height - $rest) {
                $data_array[$id][2] = $death;
                $data_array[$id][5] = $death_len;
            } else {
                // too long: truncate
                $result = parse_line(str_replace("\n", " ", $death), $width, $height - $rest);
                $data_array[$id][2] = $result[1];
                $data_array[$id][5] = $result[0];
            }
        }
    } else {
        $data_array[$id][0] = __('N.N.');
        $data_array[$id][1] = '';
        $data_array[$id][2] = '';
        $data_array[$id][3] = 1;
        $data_array[$id][4] = 0;
        $data_array[$id][5] = 0;
    }
}

function place_cells($type, $begin, $end, $increment, $maxchar, $numrows, $cellwidth): void
{
    global $dbh, $db_functions, $pdf, $data_array, $posy, $posx, $data;
    $personPrivacy = new \Genealogy\Include\PersonPrivacy();

    $pdf->SetLeftMargin(16);
    $marg = 16;
    for ($m = $begin; $m <= $end; $m += $increment) {
        if ($type == "pers") {
            // person's name & details
            data_array($m, $maxchar, $numrows);
            $pdf->SetFont($pdf->pdf_font, 'B', 8);
            if ($m % 2 == 0 or ($m == 1 and $data["sexe"][$m] == "M")) {
                // male
                $pdf->SetFillColor(191, 239, 255);
            } else {
                // female
                $pdf->SetFillColor(255, 228, 225);
            }
            $pdf->MultiCell($cellwidth, 4, $data_array[$m][0], "LTR", "C", true);
            $marg += $cellwidth;
            $pdf->SetFont($pdf->pdf_font, '', 8);
            $nstring = '';
            $used = $data_array[$m][3] + $data_array[$m][4] + $data_array[$m][5];
        } else {
            // marr date & place
            $space = '';
            if ($data["marr_date"][$m] != '') {
                $space = ' ';
            }
            if ($data["gedcomnumber"][$m] != '') {
                $personDb = $db_functions->get_person($data["gedcomnumber"][$m]);
                $pers_privacy = $personPrivacy->get_privacy($personDb);
            } else {
                $pers_privacy = false;
            }
            if ($data["gedcomnumber"][$m + 1] != '') {
                $womanDb = $db_functions->get_person($data["gedcomnumber"][$m + 1]);
                $woman_privacy = $personPrivacy->get_privacy($womanDb);
            } else {
                $woman_privacy = false;
            }

            if ($pers_privacy || $woman_privacy) {
                $marr = __('PRIVACY FILTER');
            } else {
                $marr = __('X') . ' ' . $data["marr_date"][$m] . $space . $data["marr_place"][$m];
            }
            $result = parse_line($marr, $maxchar, $numrows);
            $marg += $cellwidth;
            $nstring = '';
            $used = $result[0];
        }
        for ($x = 1; $x <= ($numrows - $used); $x++) {
            $nstring .= "\n" . " ";
        }
        if ($type == "pers") {
            $breakln = '';
            if ($data_array[$m][1] != '' && $data_array[$m][2] != '') {
                $breakln = "\n";
            }
            if ($data_array[$m][4] == 0 && $data_array[$m][5] == 0) {
                $nstring = substr($nstring, 0, strlen($nstring) - 1);
            }
            $pdf->SetFont($pdf->pdf_font, '', 8);
            $pdf->MultiCell($cellwidth, 4, $data_array[$m][1] . $breakln . $data_array[$m][2] . $nstring, "LRB", "C", true);
        } else {
            $pdf->SetFont($pdf->pdf_font, 'I', 8);
            $pdf->MultiCell($cellwidth, 4, $result[1] . $nstring, "LR", "C", false);
        }
        if ($m < $end) {
            $pdf->SetLeftMargin($marg);
            $pdf->SetY($posy);
        }
    }
    $pdf->SetX($posx);
    $posy = $pdf->GetY();
}

//initialize pdf generation
$persDb = $db_functions->get_person($data["main_person"]);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();

$privacy = $personPrivacy->get_privacy($persDb);
$name = $personName->get_person_name($persDb, $privacy);

// *** Loading without autoload ***
require_once __DIR__ . '/../include/tfpdf/tFPDFextend.php';
$pdf = new tFPDFextend();

$title = $pdf->pdf_convert(__('Ancestor sheet') . __(' of ') . $name["standard_name"]);
$pdf->SetTitle($title, true);
$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
$pdf->SetTopMargin(4);
$pdf->SetAutoPageBreak(false);
//$pdf->SetLineWidth(3);
//$pdf->AddPage();
$pdf->AddPage("L");

$pdf->AddFont($pdf->pdf_font, '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont($pdf->pdf_font, 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont($pdf->pdf_font, 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->AddFont($pdf->pdf_font, 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

$pdf->SetLeftMargin(16);
$pdf->SetRightMargin(16);
$pdf->SetFont($pdf->pdf_font, 'B', 12);
$pdf->Ln(2);

$pdf->MultiCell(0, 10, __('Ancestor sheet') . __(' of ') . str_replace("&quot;", '"', $name["standard_name"]), 0, 'C');
$pdf->Ln(2);
$pdf->SetFont($pdf->pdf_font, '', 8);

// Output the cells:
$posy = $pdf->GetY();
$posx = $pdf->GetX();

// for each generation check if there is anyone, otherwise don't display those rows

$exist = false;
for ($x = 16; $x < 32; $x++) {
    if ($data["gedcomnumber"][$x] != '') {
        $exist = true;
    }
}
if ($exist == true) {
    place_cells("pers", 16, 30, 2, 32, 8, 33);
    place_cells("marr", 16, 30, 2, 32, 3, 33);
    place_cells("pers", 17, 31, 2, 32, 8, 33);
    $pdf->MultiCell(264, 3, " ", 0, "C");
    $pdf->SetLeftMargin(16);
    $pdf->SetX(16);
    $posy += 4;
    $pdf->SetY($posy);
    $place = 33;
    for ($x = 1; $x < 9; $x++) {
        //$pdf->Image("images/arrowdown.jpg", $place, 94.5, 2);
        $pdf->Image(__DIR__ . "../../images/arrowdown.jpg", $place, 94.5, 2);
        $place += 33;
    }
}
$exist1 = false;
for ($x = 8; $x < 16; $x++) {
    if ($data["gedcomnumber"][$x] != '') {
        $exist1 = true;
    }
}
if ($exist == true || $exist1 == true) {
    place_cells("pers", 8, 15, 1, 32, 8, 33);
    place_cells("marr", 8, 14, 2, 65, 2, 66);
}
$exist2 = false;
for ($x = 4; $x < 8; $x++) {
    if ($data["gedcomnumber"][$x] != '') {
        $exist2 = true;
    }
}
if ($exist == true || $exist1 == true || $exist2 == true) {
    place_cells("pers", 4, 7, 1, 65, 4, 66);
    place_cells("marr", 4, 6, 2, 131, 2, 132);
}
place_cells("pers", 2, 3, 1, 131, 3, 132);
place_cells("marr", 2, 2, 2, 263, 2, 264);
place_cells("pers", 1, 1, 1, 263, 3, 264);

// Output the legend:
$legend = __('Legend') . ':  ' . __('*') . ' (' . __('born') . '),  ' . $dsign . ' (' . __('died') . '),  ' . __('X') . ' (' . __('marriage') . ')';
$pdf->MultiCell(80, 5, $legend, 0, "L", false);
$pdf->Cell(13, 3, " ", 0, 0);
//$pdf->SetFillColor(255,228,225); $pdf->Cell(20,3,__('female'),1,0,"C",true);
$pdf->SetFillColor(255, 228, 225);
$pdf->Cell(20, 3, $pdf->pdf_convert(__('female')), 1, 0, "C", true);
$pdf->Cell(5, 3, " ", 0, 0);
//$pdf->SetFillColor(191,239,255); $pdf->Cell(20,3,__('male'),1,0,"C",true);
$pdf->SetFillColor(191, 239, 255);
$pdf->Cell(20, 3, $pdf->pdf_convert(__('male')), 1, 0, "C", true);

$pdf->Output($title . ".pdf", "I");
