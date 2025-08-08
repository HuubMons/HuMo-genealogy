<?php

/**
 * Nov. 2023 Huub: rebuild timeline to MVC model.
 * Jul. 2025 Huub: improved timeline processing. Still under construction.
 */

$personDb = $db_functions->get_person($id);

$personPrivacy = new Genealogy\Include\PersonPrivacy();
$personName = new Genealogy\Include\PersonName();

$privacy = $personPrivacy->get_privacy($personDb);
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


// *** Open timeline directory for reading available files ***
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
        if (is_file("languages/" . $selected_language . "/timelines/" . $filename)) {
            $filenames[$counter][0] = "languages/" . $selected_language . "/timelines/" . $filename;
        } elseif (is_file("languages/default_timelines/" . $filename)) {
            $filenames[$counter][0] = "languages/default_timelines/" . $filename;
        } else {
            $filenames[$counter][0] = ''; // Should not be used normally...
        }
        $filenames[$counter][1] = substr($filename, 0, -4);
        $counter++;
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
    // humo_option is: nl!europa@de!Sweitz@en!british  etc.
    $str = explode("@", substr($humo_option['default_timeline'], 0, -1));
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
$path = $processLinks->get_link($uri_path, 'timeline', $personDb->pers_tree_id, false, $vars);
?>

<!-- Timeline menu -->
<form name="tmlstep" method="post" action="<?= $path; ?>">
    <div class="p-2 me-sm-2 genealogy_search">
        <div class="row">

            <div class="col-md-auto"><?= __('Steps:'); ?></div>
            <div class="col-md-2">
                <select size="1" name="step" aria-label="<?= __('Select step'); ?>" class="form-select form-select-sm">
                    <option value="1" <?php if ($step == 1) echo 'selected'; ?>>1 <?= __('year'); ?></option>
                    <option value="5" <?php if ($step == 5) echo 'selected'; ?>>5 <?= __('year'); ?></option>
                    <option value="10" <?php if ($step == 10) echo 'selected'; ?>>10 <?= __('year'); ?></option>
                </select>
            </div>

            <!-- Only show timelines menu if there are more than 1 timeline files -->
            <?php if (count($filenames) > 1) { ?>
                <div class="col-md-auto"><?= __('Choose timeline'); ?>:</div>
                <div class="col-md-3">
                    <select size="1" name="tml" aria-label="<?= __('Select timeline'); ?>" class="form-select form-select-sm">
                        <?php
                        $selected_language2 = 'default_timelines';
                        for ($i = 0; $i < count($filenames); $i++) {
                            // *** A timeline is selected ***
                            $selected = '';
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
        $filename = "languages/" . $selected_language . "/timelines/" . $tml . '.txt';
    } elseif (file_exists("languages/default_timelines/" . $tml . '.txt')) {
        $filename = "languages/default_timelines/" . $tml . '.txt';
    }
    // *** Read the file into an array of lines ***
    // TODO: already process lines and use multi array including dates?
    // TODO: also get first line, could be "rtl" line (see $eventdir).
    // Example of timeline file content:
    // 1020 Italian towns, including Rome, Florence and Venice, become city states
    // 1000-1038 Rule of Stephen, first of Arpad dynasty of Hungary; he accepts Christianity for his people
    // 1014 Brian Boru, High King of all Ireland, defeats Vikings at Battle of Clontarf, but is killed after victory
    // 1016-1035 Reign of Canute, Viking king of England, Denmark, Norway and Sweden
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// if only bapt date available use that
($data["isborn"] == 1 && $data["bornyear"] == '') ? $byear = $data["baptyear"] : $byear = $data["bornyear"];
// if beginyear = 1923 and step is 5 this makes it 1915
$beginyear = intval($byear) - ((intval($byear) % intval($step)) + intval($step));
// if only burial date available use that
($data["isdeath"] == 1 && $data["deathyear"] == '') ? $dyear = $data["burryear"] : $dyear = $data["deathyear"];

// if endyear = 1923 and step is 5 this makes it 1929 
$endyear = intval($dyear) + ((intval($step) - (intval($dyear) % intval($step)))) + intval($step);
if ($endyear > date("Y")) {
    $endyear = date("Y");
}

$flag = 0; // flags a first entry of timeline event in a specific year. is set to 1 when at least one entry has been made

$name = $personName->get_person_name($personDb, $privacy);

// *** Display ***
if ($data["privacy_filtered"] == true) {
?>
    <div class="alert alert-warning">
        <?= __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>'; ?>
    </div>
<?php } ?>

<?php
$step == 1 ? $yearwidth = 60 : $yearwidth = 120; // when step is 1 the column can be much shorter
$eventdir = "ltr"; // default direction of timeline file is ltr (set to rtl later in the script if necessary

// Process all event years into a lookup array.
$eventLookup = [];
// Main person events
if ($data["bornyear"] != '') {
    $eventLookup[$data["bornyear"]][] = $data["borntext"];
}
if ($data["baptyear"] != '') {
    $eventLookup[$data["baptyear"]][] = $data["bapttext"];
}
if ($data["deathyear"] != '') {
    $eventLookup[$data["deathyear"]][] = $data["deathtext"];
}
if ($data["burryear"] != '') {
    $eventLookup[$data["burryear"]][] = $data["burrtext"];
}
if (isset($data["marryear"])) {
    foreach ($data["marryear"] as $i => $year) {
        if ($year != '') {
            $eventLookup[$year][] = $data["marrtext"][$i];
        }
    }
}
if (isset($data["spousedeathyear"])) {
    foreach ($data["spousedeathyear"] as $i => $year) {
        if ($year != '') {
            $eventLookup[$year][] = $data["spousedeathtext"][$i];
        }
    }
}
// Children events
if (isset($data["chbornyear"])) {
    foreach ($data["chbornyear"] as $i => $years) {
        if (is_array($years)) {
            foreach ($years as $m => $year) {
                if ($year != '') {
                    $eventLookup[$year][] = "<span style='color:green;font-weight:normal'>" . $data["chborntext"][$i][$m] . "</span>";
                }
            }
        }
    }
}
if (isset($data["chdeathyear"])) {
    foreach ($data["chdeathyear"] as $i => $years) {
        if (is_array($years)) {
            foreach ($years as $m => $year) {
                if ($year != '') {
                    $eventLookup[$year][] = "<span style='color:green;font-weight:normal'>" . $data["chdeathtext"][$i][$m] . "</span>";
                }
            }
        }
    }
}
if (isset($data["chmarryear"])) {
    foreach ($data["chmarryear"] as $i => $years1) {
        if (is_array($years1)) {
            foreach ($years1 as $m => $years2) {
                if (is_array($years2)) {
                    foreach ($years2 as $p => $year) {
                        if ($year != '') {
                            $eventLookup[$year][] = "<span style='color:green;font-weight:normal'>" . $data["chmarrtext"][$i][$m][$p] . "</span>";
                        }
                    }
                }
            }
        }
    }
}
// Grandchildren events
if (isset($data["grchbornyear"])) {
    foreach ($data["grchbornyear"] as $i => $years1) {
        if (is_array($years1)) {
            foreach ($years1 as $m => $years2) {
                if (is_array($years2)) {
                    foreach ($years2 as $p => $years3) {
                        if (is_array($years3)) {
                            foreach ($years3 as $g => $year) {
                                if ($year != '') {
                                    $eventLookup[$year][] = "<span style='color:blue;font-weight:normal'>" . $data["grchborntext"][$i][$m][$p][$g] . "</span>";
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
    foreach ($data["grchdeathyear"] as $i => $years1) {
        if (is_array($years1)) {
            foreach ($years1 as $m => $years2) {
                if (is_array($years2)) {
                    foreach ($years2 as $p => $years3) {
                        if (is_array($years3)) {
                            foreach ($years3 as $g => $year) {
                                if ($year != '') {
                                    $eventLookup[$year][] = "<span style='color:blue;font-weight:normal'>" . $data["grchdeathtext"][$i][$m][$p][$g] . "</span>";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>

<table class="table">
    <thead class="table-primary">
        <tr>
            <th colspan='3'><?= $name["name"]; ?></th>
        </tr>
    </thead>

    <thead class="table-primary">
        <tr>
            <th><?= __('Life events'); ?></th>
            <th><?= __('Year'); ?></th>
            <th>
                <?= __('Historic events'); ?>

                <?php if (!file_exists($filenames[0][0])) { ?>
                    <br><?= __('There are no timeline files available for this language.'); ?>
                <?php } ?>
            </th>
        </tr>
    </thead>

    <?php for ($yr = $beginyear; $yr < $endyear; $yr += $step) { ?>
        <tr>
            <!-- Display life events for this year or period (1st column) -->
            <td style='width:250px;padding:4px;vertical-align:top;font-weight:bold;color:red'>
                <?php
                $br_flag = 0;
                for ($tempyr = $yr; $tempyr < $yr + $step; $tempyr++) {
                    if (isset($eventLookup[$tempyr])) {
                        foreach ($eventLookup[$tempyr] as $eventText) {
                            if ($br_flag == 1) {
                                echo "<br>";
                            }
                            echo $eventText;
                            $br_flag = 1;
                        }
                    }
                }
                ?>
            </td>

            <?php
            // Display year or period (2nd column)
            $period = '';
            if ($step != 1) {
                $tmp = ($yr + $step) + 1;
                $period = "-" . $tmp;
            }
            ?>
            <td style='width:<?= $yearwidth; ?>px;padding:4px;text-align:center;vertical-align:top;font-weight:bold;font-size:120%'>
                <?= $yr . $period; ?>
            </td>

            <!-- Display historic events for this year or period (3rd column) -->
            <td style='vertical-align:top'>
                <?php
                if (file_exists($filenames[0][0])) {
                    $flag_br = 0;
                    foreach ($lines as $buffer) {
                        $eventyear = '';
                        $eventdata = '';
                        $timeline_year = substr($buffer, 0, 4);

                        // Valid year
                        if ($timeline_year > 0 and $timeline_year < 2200) {
                            if ($timeline_year < $yr) {
                                // we didn't get to the lifespan yet - take next line
                                continue;
                            } else if ($timeline_year >= $yr + $step) {
                                // event year is beyond the year/period checked, flag existence of buffer and break out of while loop
                                break;
                            } else if ($timeline_year >= $yr and $timeline_year < $yr + $step) {
                                if ($flag_br == 0) {
                                    // first entry in this year/period. if a "rtl" was read before the first text entry make direction rtl
                                    echo '<div style="direction:' . $eventdir . '">';
                                }
                                $thisyear = '';
                                if ($step != 1) {
                                    $thisyear = $timeline_year . " ";
                                }
                                if (substr($buffer, 4, 1) == '-') {
                                    $timeline_year2 = substr($buffer, 5, 4);
                                    if ($timeline_year2 > 0 and $timeline_year2 < 2200) {
                                        $tillyear = $timeline_year2;
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
                        } else {
                            // line doesn't start with valid year - take next line or:
                            // the timeline file is a rtl file (the word rtl was on one of the first lines in the file)
                            if (substr($timeline_year, 0, 3) == "rtl") {
                                $eventdir = "rtl";
                            }
                            continue;
                        }
                    }
                    if ($flag_br != 0) {
                        echo '</div>';
                    }
                }
                ?>
            </td>

        </tr>
    <?php } ?>
</table>