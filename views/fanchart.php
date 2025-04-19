<?php

/*****************************************************************************
 * fanchart.php                                                              *
 * Original fan plotting code from PhpGedView (GNU/GPL licence)              *
 *                                                                           *
 * Rewritten and adapted for HuMo-genealogy by Yossi Beck  -  October 2009   *
 *                                                                           *
 * This program is free software; you can redistribute it and/or modify      *
 * it under the terms of the GNU General Public License as published by      *
 * the Free Software Foundation; either version 2 of the License, or         *
 * (at your option) any later version.                                       *
 *                                                                           *
 * This program is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             *
 * GNU General Public License for more details.                              *
 ****************************************************************************/

// *** Tab menu: ancestors ***
echo $data['ancestor_header'];

$maxgens = 7;

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

/*
if (!isset($_POST['show_desc'])) {  // first entry into page - check cookie or session
    if (isset($_COOKIE["humogen_showdesc"])) {
        $showdesc = $_COOKIE["humogen_showdesc"];
    } elseif (isset($_SESSION['save_show_desc'])) {
        $showdesc = $_SESSION['save_show_desc'];
    }
}
// The $_POST['show_desc'] and cookie setting is handled in header script before the headers are sent
*/

$data["fanchart_item"] = array();
$maxperson = pow(2, $data["chosengen"]);
// initialize array
for ($i = 0; $i < $maxperson; $i++) {
    for ($n = 0; $n < 6; $n++) {
        $data["fanchart_item"][$i][$n] = "";
    }
}

function fillarray($nr, $famid)
{
    global $dbh, $db_functions, $maxperson, $data, $indexnr;
    if ($nr >= $maxperson) {
        return;
    }
    if ($famid) {
        $personmnDb = $db_functions->get_person($famid);

        $man_cls = new PersonCls($personmnDb);
        $man_privacy = $man_cls->privacy;

        $name = $man_cls->person_name($personmnDb);
        //$data["fanchart_item"][$nr][0]=$name["standard_name"];
        $data["fanchart_item"][$nr][0] = html_entity_decode($name["standard_name"]);

        // *** Privacy filter ***
        if (!$man_privacy) {
            $data["fanchart_item"][$nr][1] = $personmnDb->pers_birth_date ? $personmnDb->pers_birth_date : $personmnDb->pers_bapt_date;
            $data["fanchart_item"][$nr][4] = $personmnDb->pers_death_date ? $personmnDb->pers_death_date : $personmnDb->pers_buried_date;
        } else {
            $data["fanchart_item"][$nr][1] = '';
            $data["fanchart_item"][$nr][4] = '';
        }

        if ($nr == 1) {
            // *** If selected, show descendant chart at bottom of page ***
            $indexnr = '';
            if ($personmnDb->pers_famc) {
                $indexnr = $personmnDb->pers_famc;
            }
            if ($personmnDb->pers_fams) {
                $pers_fams = explode(';', $personmnDb->pers_fams);
                $indexnr = $pers_fams[0];
            }
        }

        $data["fanchart_item"][$nr][2] = $personmnDb->pers_fams;
        $data["fanchart_item"][$nr][3] = $famid;
        $data["fanchart_item"][$nr][5] = $personmnDb->pers_sexe;

        if ($personmnDb->pers_famc) {
            $record_family = $db_functions->get_family($personmnDb->pers_famc);
            if ($record_family->fam_man) {
                fillarray($nr * 2, $record_family->fam_man);
            }
            if ($record_family->fam_woman) {
                fillarray($nr * 2 + 1, $record_family->fam_woman);
            }
        }

        // *** famc ***
        $data["fanchart_item"][$nr][6] = $personmnDb->pers_famc;
    }
}

fillarray(1, $data["main_person"]);

/**
 * split and center text by lines
 * @param string $data input string
 * @param int $maxlen max length of each line
 * @return string $text output string
 */
function split_align_text($data, $maxlen, $rtlflag, $nameflag, $gennr)
{
    $lines = explode("\n", $data);
    // more than 1 line : recursive calls
    if (count($lines) > 1) {
        $text = "";
        foreach ($lines as $indexval => $line) $text .= split_align_text($line, $maxlen, $rtlflag, $nameflag, $gennr) . "\n";
        return $text;
    }

    // process current line word by word
    $split = explode(" ", $data);
    $text = "";
    $line = "";

    if ($rtlflag == 1 && $nameflag == 1) {    // rtl name has to be re-positioned
        if ($data["fan_style"] == 2 && ($gennr == 1 || $gennr == 2)) {
            $maxlen *= 1.5;
        } // half-circle has different position for 2nd 3rd generation
        else {
            $maxlen *= 2;
        }
    }

    $found = false;
    if ($found) $line = $data;
    else
        foreach ($split as $indexval => $word) {
            $len = strlen($line);
            $wlen = strlen($word);

            // line too long ?
            if (($len + $wlen) < $maxlen) {
                if (!empty($line)) $line .= " ";
                $line .= "$word";
            } else {
                $p = max(0, floor(($maxlen - $len) / 2));
                if (!empty($line)) {
                    if ($rtlflag == 1 and $nameflag == 1) {   // trl name
                        $line = "$line" . str_repeat(" ", $p); //
                    } elseif ($rtlflag == 1 && $nameflag == 0) {
                        $line = str_repeat(" ", $p * 1.5) . "$line"; // center alignment using spaces
                    } else {
                        $line = str_repeat(" ", $p) . "$line"; // center alignment using spaces
                    }
                    $text .= "$line\n";
                }
                $line = $word;
            }
        }
    // last line
    if (!empty($line)) {
        $len = strlen($line);
        //.. if (in_array(ord($line{0}),$RTLOrd)) $len/=2;
        $p = max(0, floor(($maxlen - $len) / 2));
        if ($rtlflag == 1 && $nameflag == 1) {
            $line = "$line" . str_repeat(" ", $p);
        } elseif ($rtlflag == 1 && $nameflag == 0) {
            $line = str_repeat(" ", $p * 1.5) . "$line"; // center alignment using spaces
        } else {
            $line = str_repeat(" ", $p) . "$line"; // center alignment using spaces
        }
        $text .= "$line";
    }
    // $text.=$wlen;
    return $text;
}

/**
 * echo ancestors on a fan chart
 * @param array ancestry pid
 * @param int $fanw fan width in px (default=840)
 * @param int $fandeg fan size in deg (default=270)
 */
function print_fan_chart($data, $fanw = 840, $fandeg = 270)
{
    global $dbh, $tree_id, $db_functions, $language, $selected_language, $china_message;

    // check for GD 2.x library
    /*
    if (!defined("IMG_ARC_PIE")) {
        echo "ERROR: NO GD LIBRARY [IMG_ARC_PIE]";
        return false;
    }
    if (!function_exists("ImageTtfBbox")) {
        echo "ERROR: NO GD LIBRARY [ImageTtfBbox]";
        return false;
    }
    */
    // Use this line (is there a difference using gd2?)
    // if(extension_loaded('gd'))

    if (intval($data["fontsize"]) < 2) {
        $data["fontsize"] = 7;
    }

    $treesize = count($data["fanchart_item"]);
    if ($treesize < 1) {
        return;
    }

    // generations count
    $gen = log($treesize) / log(2) - 1;
    $sosa = $treesize - 1;

    // fan size
    if ($fandeg == 0) {
        $fandeg = 360;
    }
    $fandeg = min($fandeg, 360);
    $fandeg = max($fandeg, 90);
    $cx = $fanw / 2 - 1; // center x
    $cy = $cx; // center y
    $rx = $fanw - 1;
    $rw = $fanw / ($gen + 1);
    $fanh = $fanw; // fan height
    if ($fandeg == 180) {
        $fanh = round($fanh * ($gen + 1) / ($gen * 2));
    }
    if ($fandeg == 270) {
        $fanh = round($fanh * .86);
    }
    $scale = $fanw / 840;

    // image init
    $image = ImageCreate($fanw, $fanh);
    $black = ImageColorAllocate($image, 0, 0, 0);
    $white = ImageColorAllocate($image, 0xFF, 0xFF, 0xFF);
    ImageFilledRectangle($image, 0, 0, $fanw, $fanh, $white);
    if ($data["printing"] == 1) {
        ImageColorTransparent($image, $white);
    }

    // *** Border colour ***
    $rgb = "";
    if (empty($rgb)) $rgb = "#6E6E6E";
    $grey = ImageColorAllocate($image, hexdec(substr($rgb, 1, 2)), hexdec(substr($rgb, 3, 2)), hexdec(substr($rgb, 5, 2)));

    // *** Text colour ***
    $rgb = "";
    if (empty($rgb)) $rgb = "#000000";
    $color = ImageColorAllocate($image, hexdec(substr($rgb, 1, 2)), hexdec(substr($rgb, 3, 2)), hexdec(substr($rgb, 5, 2)));

    // *** Background colour ***
    $rgb = "";
    if (empty($rgb)) $rgb = "#EEEEEE";
    $bgcolor = ImageColorAllocate($image, hexdec(substr($rgb, 1, 2)), hexdec(substr($rgb, 3, 2)), hexdec(substr($rgb, 5, 2)));

    // *** Man colour ***
    $rgb = "";
    if (empty($rgb)) $rgb = "#B2DFEE";
    $bgcolorM = ImageColorAllocate($image, hexdec(substr($rgb, 1, 2)), hexdec(substr($rgb, 3, 2)), hexdec(substr($rgb, 5, 2)));

    // *** wife colour ***
    $rgb = "";
    if (empty($rgb)) $rgb = "#FFE4C4";
    $bgcolorF = ImageColorAllocate($image, hexdec(substr($rgb, 1, 2)), hexdec(substr($rgb, 3, 2)), hexdec(substr($rgb, 5, 2)));

    // imagemap
    $imagemap = "<map id=\"fanmap\" name=\"fanmap\">";

    // loop to create fan cells
    while ($gen >= 0) {
        // clean current generation area
        $deg2 = 360 + ($fandeg - 180) / 2;
        $deg1 = $deg2 - $fandeg;
        ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bgcolor, IMG_ARC_PIE);
        ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bgcolor, IMG_ARC_EDGED | IMG_ARC_NOFILL);
        $rx -= 3;

        // calculate new angle
        $p2 = pow(2, $gen);
        $angle = $fandeg / $p2;
        $deg2 = 360 + ($fandeg - 180) / 2;
        $deg1 = $deg2 - $angle;
        // special case for rootid cell
        if ($gen == 0) {
            $deg1 = 90;
            $deg2 = 360 + $deg1;
        }

        // draw each cell
        while ($sosa >= $p2) {
            $pid = $data["fanchart_item"][$sosa][0];
            $birthyr = $data["fanchart_item"][$sosa][1];
            $deathyr = $data["fanchart_item"][$sosa][4];
            $fontpx = $data["fontsize"];
            if ($sosa >= 16 && $fandeg == 180) {
                $fontpx = $data["fontsize"] - 1;
            }
            if ($sosa >= 32 && $fandeg != 180) {
                $fontpx = $data["fontsize"] - 1;
            }
            if (!empty($pid)) {
                if ($sosa % 2) $bg = $bgcolorF;
                else $bg = $bgcolorM;
                if ($sosa == 1) {
                    if ($data["fanchart_item"][$sosa][5] == "F") {
                        $bg = $bgcolorF;
                    } elseif ($data["fanchart_item"][$sosa][5] == "M") {
                        $bg = $bgcolorM;
                    } else {
                        $bg = $bgcolor; // sex unknown
                    }
                }

                //ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bg, IMG_ARC_PIE);
                ImageFilledArc($image, round($cx), round($cy), round($rx), round($rx), round($deg1), round($deg2), round($bg), IMG_ARC_PIE);
                if ($gen != 0) {
                    //ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $grey, IMG_ARC_EDGED | IMG_ARC_NOFILL);
                    ImageFilledArc($image, round($cx), round($cy), round($rx), round($rx), round($deg1), round($deg2), round($grey), IMG_ARC_EDGED | IMG_ARC_NOFILL);
                } else {
                    //ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $grey, IMG_ARC_NOFILL);
                    ImageFilledArc($image, round($cx), round($cy), round($rx), round($rx), round($deg1), round($deg2), round($grey), IMG_ARC_NOFILL);
                }

                $name = $pid;

                // check if string is RTL language- if it is, it has to be reversed later on by persian_log2vis()
                $rtlstr = 0;
                //if(preg_match('/(*UTF8)[א-ת]/',$name)!==0 OR preg_match('/(*UTF8)[أ-ى]/',$name)!==0) {
                if (preg_match('/(*UTF8)[א-ת]/', $name) === 1 || preg_match('/(*UTF8)[أ-ى]/', $name) === 1) {
                    // this is either Hebrew, Arabic or Persian -> we have to reverse the text!
                    $rtlstr = 1;
                }
                $fontfile = "include/fanchart/dejavusans.ttf"; // this default font serves: Latin,Hebrew,Arabic,Persian,Russian

                //if(preg_match('/(*UTF8)\p{Han}/',$name)!==0) {	// String is Chinese so use a Chinese ttf font if present in the folder
                if (preg_match('/(*UTF8)\p{Han}/', $name) === 1) {    // String is Chinese so use a Chinese ttf font if present in the folder
                    if (is_dir("include/fanchart/chinese")) {
                        $dh = opendir("include/fanchart/chinese");
                        while (false !== ($filename = readdir($dh))) {
                            //if (strtolower(substr($filename, -3)) == "ttf"){
                            if (strtolower(substr($filename, -3)) === "otf" || strtolower(substr($filename, -3)) === "ttf") {
                                $fontfile = "include/fanchart/chinese/" . $filename;
                            }
                        }
                    }
                    if ($fontfile === "include/fanchart/dejavusans.ttf") { //no Chinese ttf file found
                        $china_message = 1;
                    }
                }

                $text = $name; // names
                $text2 = ""; // dates
                if ($data["date_display"] == 1) {  // don't show dates
                } else if ($data["date_display"] == 2) { //show years only
                    // years only chosen but we also do this if no place in outer circles
                    $text2 .= substr($birthyr, -4) . " - " . substr($deathyr, -4);
                } else if ($data["date_display"] == 3) {  //show full dates (but not in narrow outer circles!)
                    if ($gen > 5) {
                        $text2 .= substr($birthyr, -4) . " - " . substr($deathyr, -4);
                    } else if ($gen > 4 and $data["fan_style"] != 4) {
                        $text2 .= substr($birthyr, -4) . " - " . substr($deathyr, -4);
                    } else {  // full dates
                        if ($birthyr) {
                            $text2 .= __('b.') . language_date($birthyr) . "\n";
                        }
                        if ($deathyr) {
                            $text2 .= __('d.') . language_date($deathyr);
                        }
                    }
                }

                // split and center text by lines
                $wmax = floor($angle * 7 / $fontpx * $scale);
                $wmax = min($wmax, 35 * $scale);  //35
                //$wmax = floor((90*$wmax)/100);
                if ($gen == 0) {
                    $wmax = min($wmax, 17 * $scale);
                }
                //17
                $text = split_align_text($text, $wmax, $rtlstr, 1, $gen);
                $text2 = split_align_text($text2, $wmax, $rtlstr, 0, $gen);

                if ($rtlstr == 1) {
                    persian_log2vis($text); // converts persian, arab and hebrew text from logical to visual and reverses it
                }

                $text .= "\n" . $text2;

                // text angle
                $tangle = 270 - ($deg1 + $angle / 2);
                if ($gen == 0) {
                    $tangle = 0;
                }
                // calculate text position
                $fontfile = realpath($fontfile); // *** Huub 04-01-2019: Necessary for PHP 7.2 ***
                $bbox = ImageTtfBbox((float)$fontpx, 0, $fontfile, $text);
                $textwidth = $bbox[4]; //4

                $deg = $deg1 + .44;
                if ($deg2 - $deg1 > 40) {
                    $deg = $deg1 + ($deg2 - $deg1) / 11;
                }
                // 11
                if ($deg2 - $deg1 > 80) {
                    $deg = $deg1 + ($deg2 - $deg1) / 7;
                }
                //  7
                if ($deg2 - $deg1 > 140) {
                    $deg = $deg1 + ($deg2 - $deg1) / 4;
                }
                //  4
                if ($gen == 0) {
                    $deg = 180;
                }
                $rad = deg2rad($deg);
                $mr = ($rx - $rw / 4) / 2;
                if ($gen > 0 && $deg2 - $deg1 > 80) {
                    $mr = $rx / 2;
                }
                $tx = $cx + ($mr) * cos($rad);

                $ty = $cy - $mr * -sin($rad);
                if ($sosa == 1) {
                    $ty -= $mr / 2;
                }
                //ImageTtfText($image, (double)$fontpx, $tangle, $tx, $ty, $color, $fontfile, $text);
                ImageTtfText($image, (float)$fontpx, $tangle, round($tx), round($ty), round($color), $fontfile, $text);

                $imagemap .= "<area shape=\"poly\" coords=\"";
                // plot upper points
                $mr = $rx / 2;
                $deg = $deg1;
                while ($deg <= $deg2) {
                    $rad = deg2rad($deg);
                    $tx = round($cx + ($mr) * cos($rad));
                    $ty = round($cy - $mr * -sin($rad));
                    $imagemap .= "$tx, $ty, ";
                    $deg += ($deg2 - $deg1) / 6;
                }
                // plot lower points
                $mr = ($rx - $rw) / 2;
                $deg = $deg2;
                while ($deg >= $deg1) {
                    $rad = deg2rad($deg);
                    $tx = round($cx + ($mr) * cos($rad));
                    $ty = round($cy - $mr * -sin($rad));
                    $imagemap .= "$tx, $ty, ";
                    $deg -= ($deg2 - $deg1) / 6;
                }
                // join first point
                $mr = $rx / 2;
                $deg = $deg1;
                $rad = deg2rad($deg);
                $tx = round($cx + ($mr) * cos($rad));
                $ty = round($cy - $mr * -sin($rad));
                $imagemap .= "$tx, $ty";

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $person_cls = new PersonCls;
                $url = $person_cls->person_url2($tree_id, $data["fanchart_item"][$sosa][6], $data["fanchart_item"][$sosa][2], $data["fanchart_item"][$sosa][3]);
                $imagemap .= "\" href=\"" . $url . "\"";
                //}

                // *** Add first spouse to base person's tooltip ***
                $spousename = "";
                if ($gen == 0 && $data["fanchart_item"][1][2] != "") {
                    // base person and has spouse
                    $spouse = $data["fanchart_item"][1][5] == "F" ? "fam_man" : "fam_woman";
                    $spouse_result = $dbh->query("SELECT " . $spouse . " FROM humo_families
                        WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $data["fanchart_item"][1][2] . "'");
                    $spouseDb = $spouse_result->fetch(); // fetch() with no parameter deaults to array which is what we want here
                    $spouse2Db = $db_functions->get_person($spouseDb[$spouse]);

                    $spouse_cls = new PersonCls($spouse2Db);
                    $spname = $spouse_cls->person_name($spouse2Db);
                    $spouse_lan = $data["fanchart_item"][1][5] == "F" ? "SPOUSE_MALE" : "SPOUSE_FEMALE";
                    if ($spname != "") {
                        $spousename = "\n(" . __($spouse_lan) . ": " . $spname["standard_name"] . ")";
                    }
                }

                $imagemap .= " alt=\"" . $pid . "\" title=\"" . $pid . $spousename . "\">";
            }
            $deg1 -= $angle;
            $deg2 -= $angle;
            $sosa--;
        }
        $rx -= $rw;
        $gen--;
    }

    $imagemap .= "</map>";

    echo $imagemap;

    $name = str_replace('"', '', $name);
    $image_title = preg_replace('~<.*>~', '', $name) . ' - ' . __('Reload fanchart with \'View\' button');
    echo "<p align=\"center\" >";

    ob_start();
    ImagePng($image);
    $image_data = ob_get_contents();
    ob_end_clean();
    $image_data = serialize($image_data);
    unset($_SESSION['image_data']);
    $_SESSION['image_data'] = $image_data;

    echo "<img src=\"include/fanchart/fanimage.php\" width=\"$fanw\" height=\"$fanh\" border=\"0\" alt=\"$image_title\" title=\"$image_title\" usemap=\"#fanmap\">";

    echo "</p>\n";
    ImageDestroy($image);
}


// *** Huub test: TEXT in image using CSS... ***
//echo '
//<style>
//#rotate {
//	position: absolute; z-index:2;
//	top: 420px; left: 420px;
//	-ms-transform: rotate(-65deg); /* IE 9 */
//	-webkit-transform: rotate(-65deg); /* Chrome, Safari, Opera */
//	transform: rotate(-65deg);
//}
//</style>
//<div id="rotate">Rotate<br>漢字<br>טבלאות בסיס</div>
//';
?>

<?php
//YB  Code to automatically make chart bigger when 7 generations are chosen
//    and the boxes for generations in outer circle(s) become too small
//    Same for 6 generations in half circle chart

if ($data["fan_width"] == "auto" or $data["fan_width"] == "") {  // if someone cleared the field alltogether we'll handle it as "auto"
    $menu_fan = "auto"; // $menu_fan is what will be displayed in menu. If size is changed automatically still "auto" will be displayed
    if ($data["chosengen"] == 7) {
        if ($data["fan_style"] == 2) {
            $data["fan_width"] = 220;
        } elseif ($data["fan_style"] == 3) {
            $data["fan_width"] = 160;
        } elseif ($data["fan_style"] == 4) {
            $data["fan_width"] = 130;
        } else { //YB: you can never get here, but just for paranoia's sake...
            $data["fan_width"] = 100;
        }
    }
    // or 6 generations with half circle...
    else if ($data["chosengen"] == 6 and $data["fan_style"] == 2) {
        $data["fan_width"] = 130;
    } else {
        $data["fan_width"] = 100;
    }
} else if ($data["fan_width"] > 49 and $data["fan_width"] < 301) {  // valid entry by user
    $menu_fan = $data["fan_width"];
} else { // invalid entry! reset it.
    $data["fan_width"] = 100;
    $menu_fan = "auto";
}

//$path_tmp = $link_cls->get_link($uri_path, 'fanchart', $tree_id, true);
//$path_tmp .= 'id=' . $data["main_person"];
$vars['id'] = $data['main_person'];
$path_tmp = $link_cls->get_link($uri_path, 'fanchart', $tree_id, false, $vars);
?>

<!-- Menu -->
<form name="people" method="post" action="<?= $path_tmp; ?>" style="display:inline;">
    <div class="row genealogy_search mt-1 ms-1 d-print-none">
        <div class="col">
            <!-- Fan style -->
            <?= __('Fan style'); ?><br>
            <div>
                <input type="radio" name="fan_style" value="2" <?php if ($data["fan_style"] == 2) echo ' checked'; ?>><?= __('half'); ?><br>
                <input type="radio" name="fan_style" value="3" <?php if ($data["fan_style"] == 3) echo ' checked'; ?>> 3/4<br>
                <input type="radio" name="fan_style" value="4" <?php if ($data["fan_style"] == 4) echo ' checked'; ?>><?= __('full'); ?>
            </div>
        </div>
        <div class="col">
            <!-- Nr. of generations -->
            <?= __('Generations'); ?>:<br>
            <select name="chosengen">
                <?php for ($i = 2; $i <= min(9, $maxgens); $i++) {; ?>
                    <option value="<?= $i; ?>" <?php if ($i == $data["chosengen"]) echo ' selected'; ?>><?= $i; ?></option>
                <?php } ?>
            </select><br>
        </div>
        <div class="col">
            <!-- Fontsize -->
            <?= __('Font size'); ?>:<br>
            <select name="fontsize">
                <?php for ($i = 5; $i <= 12; $i++) {; ?>
                    <option value="<?= $i; ?>" <?php if ($i == $data["fontsize"]) echo ' selected'; ?>><?= $i; ?></option>
                <?php } ?>
            </select><br>
        </div>
        <div class="col">
            <!-- Date display -->
            <?= __('Date display'); ?>:<br>
            <div>
                <input type="radio" name="date_display" value="1" <?php if ($data["date_display"] == "1") echo ' checked'; ?>><?= __('No dates'); ?><br>
                <input type="radio" name="date_display" value="2" <?php if ($data["date_display"] == "2") echo ' checked'; ?>><?= __('Years only'); ?><br>
                <input type="radio" name="date_display" value="3" <?php if ($data["date_display"] == "3") echo ' checked'; ?>><?= __('Full dates'); ?>
            </div>
        </div>
        <div class="col">
            <!-- Fan width in percentages -->
            <?= __('Fan width:'); ?><br>
            <input type="text" size="3" name="fan_width" value="<?= $menu_fan; ?>"> <b>%</b>
            <div style="font-size:10px;"><?= __('"auto" for automatic resizing for best display, or value between 50-300'); ?></div>
        </div>
        <div class="col">
            <!-- Background (for printing with IE) -->
            <?= __('Background'); ?>:<br>
            <div>
                <input type="radio" name="printing" value="1" <?php if ($data["printing"] == 1) echo " checked"; ?>> <?= __('transparent'); ?><br>
                <input type="radio" name="printing" value="2" <?php if ($data["printing"] == 2) echo " checked"; ?>> <?= __('white'); ?>
            </div>
        </div>
        <?php
        /*
        <div class="col">
            <div>
                <input type="hidden" name="show_desc" value="0">
                <input type="checkbox" name="show_desc" value="1" <?php if ($showdesc == "1") echo ' checked'; ?>> <span style="font-size:10px;"><?= __('descendants'); ?><br>&nbsp;&nbsp;&nbsp;&nbsp;<?= __('under fanchart'); ?></span>
            </div>
        </div>
        */
        ?>
        <div class="col">
            <input type="submit" value="<?= __('View'); ?>" class="btn btn-sm btn-success"><br>
        </div>
    </div>
</form>

<?php
$china_message = 0;
// *** Container for fanchart ***
?>
<div style="top:60px; left:135px; width:<?= (840 * $data["fan_width"] / 100); ?>px">
    <div style="padding:5px">
        <?php print_fan_chart($data, 840 * $data["fan_width"] / 100, $data["fan_style"] * 90); ?>
    </div>
</div>

<?php
// TODO check download link. Use sourceforge?
if ($china_message == 1) {
?>
    <!-- <div style="border:2px solid red;background-color:white;padding:5px;position:relative;length:300px;margin-left:30%;margin-right:30%;top:90px;font-weight:bold;color:red;font-size:120%;text-align:center;"> -->
    <div style="border:2px solid red;background-color:white;padding:5px;position:relative;margin-left:30%;margin-right:30%;top:90px;font-weight:bold;color:red;font-size:120%;text-align:center;">
        <?= __('No Chinese ttf font file found'); ?><br>
        <?= __('Download link'); ?>: <a href="http://humogen.com/download.php?file=simplified-wts47.zip">Simplified 简体中文 </a>
        <?= __('or'); ?> <a href="http://humogen.com/download.php?file=traditional-wt011.zip">Traditional 繁體中文</a><br>
        <?= __('Unzip and place in "include/fanchart/chinese/" folder'); ?>
    </div>
<?php
}

// *** Show descendants ***
/*
if ($showdesc == "1") {
    $fan_w =  9.3 * $data["fan_width"];
    if ($data["fan_style"] == 2) $top_pos = $fan_w / 2 + 165;
    elseif ($data["fan_style"] == 3) $top_pos = 0.856 * $fan_w;
    elseif ($data["fan_style"] == 4) $top_pos = $fan_w;
    echo '<iframe src="descendant/' . $tree_prefix . '/' . $indexnr . '?main_person=' . $data["main_person"] . '&amp;menu=1" id="iframe1"  style="position:absolute;top:' . $top_pos . 'px;left:0px;width:100%;height:700px;" ;" ></iframe';
}
*/
?>

<br><br><br>