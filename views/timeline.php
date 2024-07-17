<?php

/**
 * Nov. 2023 Huub: rebuild timeline to MVC model.
 */

$personDb = $db_functions->get_person($id);

// *** Check privacy filter ***
$person_cls = new person_cls($personDb);
$privacy = $person_cls->privacy;
if ($privacy) {
    echo '<br><br>' . __('PRIVACY FILTER');
    exit();
}

if ($data["isborn"] == 0 && $data["isdeath"] == 0 && $data["ismarr"] == 0 && $data["ischild"] == 0) {
?>
    <!-- No birth or death dates available -->
    <div class="alert alert-warning">
        <?= __('There are no dates available for this person. Timeline can not be calculated.'); ?>
    </div>
<?php
    exit();
}


// *** OPEN TIMELINE DIRECTORY FOR READING AVAILABLE FILES ***
if (is_dir("languages/" . $selected_language . "/timelines")) {
    // *** Open languages/xx/timelines folder ***
    $dh  = opendir("languages/" . $selected_language . "/timelines");
} else {
    // *** No timelines folder found inside selected language: use default timeline folder ***
    $dh  = opendir("languages/default_timelines");
}

$counter = 0;
while (false !== ($filename = readdir($dh))) {
    if (strtolower(substr($filename, -3)) === "txt") {
        $counter++;
        if (is_file("languages/" . $selected_language . "/timelines/" . $filename)) {
            $filenames[$counter - 1][0] = "languages/" . $selected_language . "/timelines/" . $filename;
        } elseif (is_file("languages/default_timelines/" . $filename)) {
            $filenames[$counter - 1][0] = "languages/default_timelines/" . $filename;
        } else {
            $filenames[$counter - 1][0] = ''; // Should not be used normally...
        }
        $filenames[$counter - 1][1] = substr($filename, 0, -4);
    }
}
sort($filenames);

// *** Selected step ***
$step = 5; // default step - user can choose 1 or 10 instead
if (isset($_POST['step'])) {
    $step = $_POST['step'];
}

// *** Selected timeline ***
$tml = $filenames[0][1]; // if default is not set the first file will be checked
if (isset($_POST['tml'])) {
    $tml = $_POST['tml'];
} elseif (isset($humo_option['default_timeline']) && $humo_option['default_timeline'] != "") {
    $str = explode("@", substr($humo_option['default_timeline'], 0, -1));  // humo_option is: nl!europa@de!Sweitz@en!british  etc.
    $val_arr = array();
    foreach ($str as $value) {
        $str2 = explode("!", $value);   //  $value = nl!europa
        $val_arr[$str2[0]] = $str2[1];   //  $val_arr[nl]='europa'
    }

    $selected_language2 = 'default_timelines'; // *** Timelines default folder ***

    // *** 1st Use timeline from language folder ***
    if (isset($val_arr[$selected_language]) && is_file("languages/" . $selected_language . "/timelines/" . $val_arr[$selected_language] . ".txt")) {
        $tml = $val_arr[$selected_language];
    }
    // *** 2nd Use timeline file from default folder ***
    elseif (isset($val_arr[$selected_language]) && is_file("languages/default_timelines/" . $val_arr[$selected_language] . ".txt")) {
        $tml = $val_arr[$selected_language];
    }
    // *** Use timeline file from default folder ***
    elseif (isset($val_arr[$selected_language2]) && is_file("languages/default_timelines/" . $val_arr[$selected_language2] . ".txt")) {
        $tml = $val_arr[$selected_language2];
    }
}

$vars['pers_gedcomnumber'] = $personDb->pers_gedcomnumber;
$path = $link_cls->get_link($uri_path, 'timeline', $personDb->pers_tree_id, false, $vars);
?>

<!-- SHOW MENU -->
<form name="tmlstep" method="post" action="<?= $path; ?>">
    <div class="p-2 me-sm-2 genealogy_search">
        <div class="row">

            <div class="col-md-auto">
                <!-- Steps of years in display: 1, 5 or 10 -->
                <?= __('Steps:'); ?>
            </div>
            <div class="col-md-2">
                <select size="1" name="step" class="form-select form-select-sm">
                    <option value="1" <?php if ($step == 1) echo 'selected'; ?>>1 <?= __('year'); ?></option>
                    <option value="5" <?php if ($step == 5) echo 'selected'; ?>>5 <?= __('year'); ?></option>
                    <option value="10" <?php if ($step == 10) echo 'selected'; ?>>10 <?= __('year'); ?></option>
                </select>
            </div>

            <!-- Only show timelines menu if there are more than 1 timeline files -->
            <?php if (count($filenames) > 1) { ?>
                <div class="col-md-auto">
                    <?= __('Choose timeline'); ?>:
                </div>
                <div class="col-md-3">
                    <select size="1" name="tml" class="form-select form-select-sm">
                        <?php
                        $selected_language2 = 'default_timelines';
                        for ($i = 0; $i < count($filenames); $i++) {
                            $selected = '';
                            // *** A timeline is selected ***
                            if (isset($_POST['tml']) && $_POST['tml'] == $filenames[$i][1]) {
                                $selected = "selected";
                            }

                            // *** If no selection is made, use default settings ***
                            if (!isset($_POST['tml'])) {
                                // *** humo_option is: nl!europa@de!Sweitz@en!british  etc. ***
                                if (isset($humo_option['default_timeline']) && strpos($humo_option['default_timeline'], $selected_language . "!" . $filenames[$i][1] . "@") !== false) {
                                    $selected = "selected";
                                }
                                // *** humo_option is: nl!europa@de!Sweitz@en!british  etc. ***
                                elseif (isset($humo_option['default_timeline']) && strpos($humo_option['default_timeline'], $selected_language2 . "!" . $filenames[$i][1] . "@") !== false) {
                                    $selected = "selected";
                                }
                                // *** There are no default settings, and no selection is made ***
                                elseif ($tml == $filenames[$i][1]) {
                                    $selected = "selected";
                                }
                            }
                        ?>
                            <option value="<?= $filenames[$i][1]; ?>" <?= $selected; ?>><?= ucfirst($filenames[$i][1]); ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="col-md-auto">
                <input type="submit" value="<?= __('Change Display'); ?>" class="btn btn-sm btn-success">
            </div>

            <div class="col-md-auto">
                <!-- Help popup. Remark: Bootstrap popover javascript in layout script. -->
                <style>
                    .popover {
                        max-width: 500px;
                    }
                    .popover-body {
                        height: 500px;
                        overflow-y: auto;
                    }
                </style>
                <?php $popup_text =  __('Explanation of the timeline chart:<br>
<ul><li>The middle column displays the years of the timeline. The starting point will be just before birth and the end year will be just after death.</li>
<li>The left column displays the events in the person\'s life.<br>
Events listed are: birth, death and marriage(s) of main person, death of spouse, birth, marriage and death of children and birth and death of grandchildren.<br>
Birth, death, marriages and death of spouse are listed in bold red. Birth, marriage and death of children in green. Birth and death of grandchildren in blue</li>
<li>The rightmost column displays historic events that took place in these years.</li></ul>
The timeline menu:<br>
<ul><li>On the top part of the menu you can choose how the chart will be displayed. There are three choices:<br>
1 - will display each year in a separate row.<br>
5 - will create periods of five years for a more concise display.<br>
10 - displays the chart in periods of one decade for even more concise display.</li>
<li>If the webmaster enabled more than one timeline, the bottom part of the menu will let you choose from amongst several possible timelines. For example "American History", "Dutch History" etc.</li>
<li><strong>After choosing the desired step and/or timeline, click the "Change Display" button on the bottom of the menu.</strong></li></ul>'); ?>
                <?php $popup_text = str_replace('"', "'", $popup_text); ?>

                <button type="button" class="btn btn-sm btn-secondary" data-bs-html="true" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?= $popup_text; ?>">
                    <?= __('Help'); ?>
                </button>
            </div>

        </div>
    </div>
</form><br>


<?php
if (file_exists($filenames[0][0])) {
    if (file_exists("languages/" . $selected_language . "/timelines/" . $tml . '.txt')) {
        $handle = fopen("languages/" . $selected_language . "/timelines/" . $tml . '.txt', "r");
    } elseif (file_exists("languages/default_timelines/" . $tml . '.txt')) {
        $handle = fopen("languages/default_timelines/" . $tml . '.txt', "r");
    }
}

// if only bapt date available use that
($data["isborn"] == 1 && $data["bornyear"] == '') ? $byear = $data["baptyear"] : $byear = $data["bornyear"];
// if beginyear=1923 and step is 5 this makes it 1915
$beginyear = intval($byear) - ((intval($byear) % intval($step)) + intval($step));
// if only burial date available use that
($data["isdeath"] == 1 && $data["deathyear"] == '') ? $dyear = $data["burryear"] : $dyear = $data["deathyear"];
// if endyear=1923 and step is 5 this makes it 1929 
$endyear = intval($dyear) + ((intval($step) - (intval($dyear) % intval($step)))) + intval($step);

if ($endyear > date("Y")) {
    $endyear = date("Y");
}
$flag = 0; // flags a first entry of timeline event in a specific year. is set to 1 when at least one entry has been made

$name = $person_cls->person_name($personDb);

// ****** DISPLAY
if ($data["privacy_filtered"] == true) {
?>
    <div class="alert alert-warning">
        <?= __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>'; ?>
    </div>
<?php } ?>

<table align="center" class="humo index_table">
    <tr class=table_headline>
        <th colspan='3'><?= $name["name"]; ?></th>
    </tr>

    <tr class=table_headline>
        <th><?= __('Life events'); ?></th>
        <th><?= __('Year'); ?></th>
        <th>
            <?= __('Historic events'); ?>

            <?php if (!file_exists($filenames[0][0])) { ?>
                <br><?= __('There are no timeline files available for this language.'); ?>
            <?php } ?>
        </th>
    </tr>

    <?php
    $step == 1 ? $yearwidth = 60 : $yearwidth = 120; // when step is 1 the column can be much shorter
    $flag_isbuffer = 0;
    $eventdir = "ltr"; // default direction of timeline file is ltr (set to rtl later in the script if necessary
    for ($yr = $beginyear; $yr < $endyear; $yr += $step) {  // range of years for lifespan
        // DISPLAY LIFE EVENTS FOR THIS YEAR/PERIOD (1st column)
    ?>
        <tr>
            <td style='width:250px;padding:4px;vertical-align:top;font-weight:bold;color:red'>
                <?php
                $br_flag = 0;
                for ($tempyr = $yr; $tempyr < $yr + $step; $tempyr++) {
                    if ($data["bornyear"] != '' && $data["bornyear"] == $tempyr) {
                        if ($br_flag == 1) {
                            echo "<br>";
                        }
                        echo $data["borntext"];
                        $br_flag = 1;
                    } elseif ($data["baptyear"] != '' && $data["baptyear"] == $tempyr) {
                        if ($br_flag == 1) {
                            echo "<br>";
                        }
                        echo $data["bapttext"];
                        $br_flag = 1;
                    }
                    if (isset($data["marryear"])) {
                        for ($i = 0; $i < count($data["marryear"]); $i++) {
                            if ($data["marryear"][$i] != '' and $data["marryear"][$i] == $tempyr) {
                                if ($br_flag == 1) {
                                    echo "<br>";
                                }
                                echo $data["marrtext"][$i];
                                $br_flag = 1;
                            }
                        }
                    }
                    if (isset($data["spousedeathyear"])) {
                        for ($i = 0; $i < count($data["spousedeathyear"]); $i++) {
                            if ($data["spousedeathyear"][$i] != '' and $data["spousedeathyear"][$i] == $tempyr) {
                                if ($br_flag == 1) {
                                    echo "<br>";
                                }
                                echo $data["spousedeathtext"][$i];
                                $br_flag = 1;
                            }
                        }
                    }
                    if (isset($data["chbornyear"])) {
                        for ($i = 0; $i < count($data["marriages"]); $i++) {
                            if (is_array($data["children"][$i])) {
                                for ($m = 0; $m < count($data["children"][$i]); $m++) {
                                    if (isset($data["chbornyear"][$i][$m]) and $data["chbornyear"][$i][$m] == $tempyr) {
                                        if ($br_flag == 1) {
                                            echo "<br>";
                                        }
                                        echo "<span style='color:green;font-weight:normal'>" . $data["chborntext"][$i][$m] . "</span>";
                                        $br_flag = 1;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($data["chdeathyear"])) {
                        for ($i = 0; $i < count($data["marriages"]); $i++) {
                            if (is_array($data["children"][$i])) {
                                for ($m = 0; $m < count($data["children"][$i]); $m++) {
                                    if (isset($data["chdeathyear"][$i][$m]) and $data["chdeathyear"][$i][$m] == $tempyr) {
                                        if ($br_flag == 1) {
                                            echo "<br>";
                                        }
                                        echo "<span style='color:green;font-weight:normal'>" . $data["chdeathtext"][$i][$m] . "</span>";
                                        $br_flag = 1;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($data["chmarryear"])) {
                        for ($i = 0; $i < count($data["marriages"]); $i++) {
                            if (is_array($data["children"][$i])) {
                                for ($m = 0; $m < count($data["children"][$i]); $m++) {
                                    if (is_array($data["chmarriages"][$i][$m])) {
                                        for ($p = 0; $p < count($data["chmarriages"][$i][$m]); $p++) {
                                            if (isset($data["chmarryear"][$i][$m][$p]) and $data["chmarryear"][$i][$m][$p] != '' and $data["chmarryear"][$i][$m][$p] == $tempyr) {
                                                if ($br_flag == 1) {
                                                    echo "<br>";
                                                }
                                                echo "<span style='color:green;font-weight:normal'>" . $data["chmarrtext"][$i][$m][$p] . "</span>";
                                                $br_flag = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (isset($data["grchbornyear"])) {
                        for ($i = 0; $i < count($data["marriages"]); $i++) {
                            if (is_array($data["children"][$i])) {
                                for ($m = 0; $m < count($data["children"][$i]); $m++) {
                                    if (is_array($data["chmarriages"][$i][$m])) {
                                        for ($p = 0; $p < count($data["chmarriages"][$i][$m]); $p++) {
                                            if (is_array($data["grchildren"][$i][$m][$p])) {
                                                for ($g = 0; $g < count($data["grchildren"][$i][$m][$p]); $g++) {
                                                    if (isset($data["grchbornyear"][$i][$m][$p][$g]) and $data["grchbornyear"][$i][$m][$p][$g] != '' and $data["grchbornyear"][$i][$m][$p][$g] == $tempyr) {
                                                        if ($br_flag == 1) {
                                                            echo "<br>";
                                                        }
                                                        echo "<span style='color:blue;font-weight:normal'>" . $data["grchborntext"][$i][$m][$p][$g] . "</span>";
                                                        $br_flag = 1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (isset($data["grchdeathyear"])) {
                        for ($i = 0; $i < count($data["marriages"]); $i++) {
                            if (is_array($data["children"][$i])) {
                                for ($m = 0; $m < count($data["children"][$i]); $m++) {
                                    if (is_array($data["chmarriages"][$i][$m])) {
                                        for ($p = 0; $p < count($data["chmarriages"][$i][$m]); $p++) {
                                            if (is_array($data["grchildren"][$i][$m][$p])) {
                                                for ($g = 0; $g < count($data["grchildren"][$i][$m][$p]); $g++) {
                                                    if (isset($data["grchdeathyear"][$i][$m][$p][$g]) and $data["grchdeathyear"][$i][$m][$p][$g] != '' and $data["grchdeathyear"][$i][$m][$p][$g] == $tempyr) {
                                                        if ($br_flag == 1) {
                                                            echo "<br>";
                                                        }
                                                        echo "<span style='color:blue;font-weight:normal'>" . $data["grchdeathtext"][$i][$m][$p][$g] . "</span>";
                                                        $br_flag = 1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($data["deathyear"] != '' && $data["deathyear"] == $tempyr) {
                        if ($br_flag == 1) {
                            echo "<br>";
                        }
                        echo $data["deathtext"];
                        $br_flag = 1;
                    } elseif ($data["burryear"] != '' && $data["burryear"] == $tempyr) {
                        if ($br_flag == 1) {
                            echo "<br>";
                        }
                        echo $data["burrtext"];
                        $br_flag = 1;
                    }
                } // end life events loop
                ?>
            </td>

            <?php
            // DISPLAY YEAR/PERIOD (2nd column)
            $period = '';
            if ($step != 1) {
                $tmp = ($yr + $step) + 1;
                $period = "-" . $tmp;
            }
            ?>
            <td style='width:<?= $yearwidth; ?>px;padding:4px;text-align:center;vertical-align:top;font-weight:bold;font-size:120%'>
                <?= $yr . $period; ?>
            </td>

            <!-- DISPLAY HISTORIC EVENTS FOR THIS YEAR/PERIOD (3rd column) -->
            <td style='vertical-align:top'>
                <?php
                if (file_exists($filenames[0][0])) {
                    $flag_br = 0;
                    while (!feof($handle) or (feof($handle) and $flag_isbuffer == 1)) {
                        $eventyear = '';
                        $eventdata = '';
                        if ($flag_isbuffer != 1) {
                            $buffer = fgets($handle, 4096);
                            $temp = substr($buffer, 0, 4);
                        } else {
                            $flag_isbuffer = 0;
                        }

                        if ($temp > 0 and $temp < 2200) { // valid year
                            if ($temp < $yr) { // we didn't get to the lifespan yet - take next line
                                continue;
                            } else if ($temp >= $yr + $step) { // event year is beyond the year/period checked, flag existence of buffer and break out of while loop
                                $flag_isbuffer = 1;
                                break;
                            } else if ($temp >= $yr and $temp < $yr + $step) {
                                if ($flag_br == 0) { // first entry in this year/period. if a "rtl" was read before the first text entry make direction rtl
                                    echo '<div style="direction:' . $eventdir . '">';
                                }
                                $thisyear = '';
                                if ($step != 1) {
                                    $thisyear = $temp . " ";
                                }
                                if (substr($buffer, 4, 1) == '-') {
                                    $temp2 = substr($buffer, 5, 4);
                                    if ($temp2 > 0 and $temp2 < 2200) {
                                        $tillyear = $temp2;
                                        $eventdata = "(" . __('till') . " " . $tillyear . ") " . substr($buffer, 10);
                                        if ($flag_br == 1) {
                                            echo "<br>";
                                        }
                                        echo $thisyear . $eventdata;
                                        $flag_br = 1;
                                    }
                                } else {
                                    $eventdata = substr($buffer, 5);
                                    if ($flag_br == 1) {
                                        echo "<br>";
                                    }
                                    echo $thisyear . $eventdata;
                                    $flag_br = 1;
                                }
                            }
                        } else { // line doesn't start with valid year - take next line
                            if (substr($temp, 0, 3) == "rtl") {  //the timeline file is a rtl file (the word rtl was on one of the first lines in the file)
                                $eventdir = "rtl";
                            }
                            continue;
                        }
                    } // end while loop
                    if ($flag_br != 0) {
                        echo '</div>';
                    }
                }
                ?>
            </td>
        </tr>
    <?php } ?>
</table>
<br><br>

<?php
if (file_exists($filenames[0][0])) {
    fclose($handle);
}
