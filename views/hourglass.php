<?php

/**
 * HOURGLASS script by Yossi Beck august 2013
 * Made with extensive use of existing files:
 * - report_ancestor (included with minor changes in the file)
 * - report_descendant (included with minor changes in the file)
 * - family script (included with minor changes in the file)
 * Other additions and alterations:
 * - hourglass item added to popup menu in /include/person_cls.php
 * - icon added: /images/hourglass.gif
 * - class added to gedcom.css and silverline.css for graph lines
 * 
 * Updated to MVC model by Huub in november 2023.
 * 
 * TODO:
 * - save slider position
 * - think about names in size 8
 */

$genarray = $data["genarray"];
$hourglass = true;

// *** Ancestor part of report ***
$_GET['id'] = $_GET['main_person'];
include_once(__DIR__ . "/ancestor_chart.php");

// THE HORIZONTALLY ALIGN POSITION OF THE BASE PERSON IN DONE DESC CHART AND ANCESTOR CHART

// Height of base person in desc chart is dynamically generated in descendant chart functions
// Height of base person in ancestor chart is set here. Will be moved down if necessary
if ($data["size"] == 50) {
    $boxhight = 1.5 * 75;
} elseif ($data["size"] == 45) {
    $boxhight = 1.5 * 45;
} else {
    $boxhight = 1.5 * $data["size"];
}
$anc_top = (pow(2, $data["chosengenanc"] - 1) * $boxhight) / 2;

if ($genarray[0]["posy"] < $anc_top) { // if desc base pers higher on screen than base person of ancestor chart - has to be lowered to there.
    $offset = $anc_top - $genarray[0]["posy"];
    $counter = count($genarray);
    for ($a = 0; $a < $counter; $a++) {
        $genarray[$a]["posy"] += $offset;
        if (isset($genarray[$a]["fst"])) {
            $genarray[$a]["fst"] += $offset;
        }
        if (isset($genarray[$a]["lst"])) {
            $genarray[$a]["lst"] += $offset;
        }
    }
}
if ($genarray[0]["posy"] > $anc_top) { // if desc base person lower, we have to lower base person of anc chart.
    $anc_top = $genarray[0]["posy"];
}
//Set height of chart, both for screen and img-to-print
//Descendant chart bottom coordinates
$desc_hi = 0;
$counter = count($genarray);
for ($i = 0; $i < $counter; $i++) {
    if ($genarray[$i]["posy"] > $desc_hi) {
        $desc_hi = $genarray[$i]["posy"];
    }
}
$desc_hi += 150;  // lowest point of desc chart
//Ancestor chart bottom coordinates
if ($data["size"] == 50) {
    $v_distance = 1.5 * 75;
} else {
    $v_distance = 1.5 * $data["size"];
}
$anc_hi = $anc_top + ((pow(2, $data["chosengenanc"] - 1) * $v_distance) / 2) + 100; // lowest point of anc chart 550

// Find longest chart and set as bottom of div
$div_hi = $desc_hi > $anc_hi ? $desc_hi : $anc_hi;
?>

<h1 class="standard_header" style="margin:auto; text-align: center;">
    <b><?= __('Hourglass chart') . __(' of ') . $genarray[0]["nam"]; ?></b>
</h1>

<script src="include/html2canvas/html2canvas.min.js"></script>

<div style="text-align:center;">
    <span style="font-size:130%"><?= __('Ancestors'); ?>&#8678;&#8678;&#8678;&nbsp;&nbsp;&nbsp;</span>
    <input type="button" id="imgbutton" value="<?= __('Get image of chart for printing (allow pop-up!)'); ?>" onClick="showimg();">
    <span style="font-size:130%">&nbsp;&nbsp;&nbsp;&#8680;&#8680;&#8680;<?= __('Descendants'); ?></span>
</div>

<!-- HELP POPUP - displayed at upper left corner of screen -->
<div id="helppopup" class="<?= $rtlmarker; ?>sddm" style="position:absolute;left:10px;top:10px;display:inline;">
    <a href="#" style="display:inline" onmouseover="mopen(event,'help_menu',0,0)" onmouseout="mclosetime()"><b><?= __('Help'); ?></b></a>&nbsp;
    <div class="sddm_fixed" style="z-index:10; padding:4px; text-align:<?= $alignmarker; ?>;  direction:<?= $rtlmarker; ?>;" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
        <?= __('<b>USE:</b>
<p>The main person is displayed in the center of the chart.<br>
Ancestors are displayed to his/her left, descendants are displayed to the right
<p><b>Hover over square:</b> Display pop-up menu with details<br>
<b>Click on square:</b> Move this person to center of chart<br>
<b>Click on name in pop-up menu:</b> Go to person\'s family page<br><br>
<b>LEGEND:</b>'); ?><br><br>

        <span style="background-image: linear-gradient(to bottom, #ffffff 0%, #81bef7 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;<?= __('Male'); ?><br>
        <span style="background-image: linear-gradient(to bottom, #ffffff 0%, #f5bca9 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;<?= __('Female'); ?><br>
        <span style="color:blue">=====</span>&nbsp;<?= __('Additional marriage of same person'); ?><br><br>

        <?= __('<b>SETTINGS:</b>

<br><br><b>Nr. Generations:</b> choose between 2 - 12 generations for ancestors<br>
and 2 - 15 generation for descendants. 
(large number of generations will take longer to generate)<br>
<b>Box size:</b> Use the slider to choose display size (10 steps): <br>
step 1-4: small boxes with popup for details<br>
step 5-7: larger boxes with initials of name + pop-up for details<br>
step 8-9: boxes/rectangles with name inside + pop-up with further details<br>
step 10:    large rectangles with name, birth and death details + pop-up with further details'); ?>
    </div>
</div>

<?php
$vars['pers_family'] = $data["family_id"];
$path_tmp = $link_cls->get_link($uri_path, 'hourglass', $tree_id, true, $vars);
$path_tmp .= "main_person=" . $data["main_person"] . '&amp;screen_mode=HOUR';
?>

<!-- MENU BAR - no. of generations, zoom -->
<div id="menubox" class="search_bar" style="margin-top:5px; direction:ltr; z-index:20; width:700px; text-align:left;">
    &nbsp;<?= __('Nr. generations') . ': ' . __('Anc.'); ?>&nbsp;

    <select name="chosengenanc" onChange="window.location=this.value">
        <?php
        for ($i = 2; $i <= 12; $i++) {
            echo '<option value="' . $path_tmp . '&amp;direction=' . $data["direction"] . '&amp;chosensize=' .
                $data["size"] . '&amp;chosengen=' . $data["chosengen"] . '&amp;chosengenanc=' . $i . '"';
            if ($i == $data["chosengenanc"]) {
                echo "selected=\"selected\" ";
            }
            echo ">" . $i . "</option>";
        }
        ?>
    </select>

    &nbsp;&nbsp;<?= __('Desc.'); ?>&nbsp;
    <select name="chosengen" onChange="window.location=this.value">
        <?php
        for ($i = 2; $i <= 15; $i++) {
            echo '<option value="' . $path_tmp . '&amp;direction=' . $data["direction"] . '&amp;chosensize=' .
                $data["size"] . '&amp;chosengen=' . $i . '&amp;chosengenanc=' . $data["chosengenanc"] . '"';
            if ($i == $data["chosengen"]) {
                echo "selected=\"selected\" ";
            }
            echo ">" . $i . "</option>";
        }

        // oct. 2023 DISABLED because of fault messages.
        // *** Option "All" for all generations ***
        //echo '<option value="' . $path_tmp . '&amp;direction=' . $data["direction"] . '&amp;chosensize=' .
        //    $data["size"] . '&amp;chosengen=All&amp;chosengenanc=' . $data["chosengenanc"] . '"';
        //if ($data["chosengen"] == "All") echo "selected=\"selected\" ";
        //echo ">" . "All" . "</option>";
        ?>
    </select>

    <?php
    echo '&nbsp;&nbsp;';
    // min:0 (for extra first step - now 10 steps: 0-9), then twice value +1 so on display first step is shown as 1, not 0

    // *** Don't use &amp in link in javascript ***
    $path_tmp = str_replace('&amp;', '&', $path_tmp);

    echo '
    <script>
    $(function() {
        $( "#slider" ).slider({
            value: ' . (($data["size"] / 5) - 1) . ',
            min: 0,
            max: 9,
            step: 1,
            slide: function( event, ui ) {
                $( "#amount" ).val(ui.value+1);
            }
        });
        $( "#amount" ).val($( "#slider" ).slider( "value" )+1 );

        // *** Only reload page if value is changed ***
        startPos = $("#slider").slider("value");
        $("#slider").on("slidestop", function(event, ui) {
            endPos = ui.value;
            if (startPos != endPos) {
                window.location.href = "' . $path_tmp . '&chosensize="+((endPos+1)*5)+"&chosengen=' . $data["chosengen"] . '&chosengenanc=' . $data["chosengenanc"] . '&direction=' . $data["direction"] . '";
            }
            startPos = endPos;
        });
    });
    </script>
    ';
    ?>

    <label for="amount"><?= __('Zoom level:'); ?></label>
    <input type="text" id="amount" disabled="disabled" style="width:28px;border:0; color:#0000CC; font-weight:normal;font-size:115%;">
    <div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>
</div>

<?php
// Start DIV FOR IMAGE (to print image of chart with plotter) ^^^^^

//following div gets width and length in imaging java function showimg() (at bottom) otherwise double scrollbars won't work.
echo '<div id="png">';

// Start DIV FOR DOUBLESCROLL (horizontal scrollbars top and bottom ^^^^^^^^^^^^^^^^^^^^^^^
echo '
<style type="text/css">
#doublescroll { position:relative; width:auto; height:' . $div_hi . 'px; overflow: auto; overflow-y: hidden; }
#doublescroll p { margin: 0; padding: 1em; white-space: nowrap; }
</style>
';
echo '<div id="doublescroll">';

// PRINT THE ANCESTOR CHART ^^^^^^^^^^^^^
$left = 10;
//$vdist = 20;
$data["vdist"] = 20;
$blocks = pow(2, $data["chosengenanc"] - 1);
$height = 75;
$width = 170;
$line_drop = $height / 2;
$incr = 1.5 * $height;
$hi = 1.5 * $height;
$gap = 3 * $height;

if ($data["size"] == 45) {
    $height = 45;
    $width = 100;
    $line_drop = $height / 2;
    $incr = 1.5 * $height;
    $hi = 1.5 * $height;
    $gap = 3 * $height;
}
if ($data["size"] < 45) {
    $height = $data["size"];
    $width = $data["size"];
    $line_drop = $height / 2;
    $incr = 1.5 * $height;
    $hi = 1.5 * $height;
    $gap = 3 * $height;
}

$top = $anc_top - ((($blocks * $hi) - $incr) / 2);

for ($x = $data["chosengenanc"]; $x > 1; $x--) {
    $this_top = $top;
    for ($i = 0; $i < $blocks; $i++) {
        $sexe_colour = '';
        $backgr_col = "#FFFFFF";
        if (isset($sexe[$i + $blocks]) && $sexe[$i + $blocks] != "") {
            if ($sexe[$i + $blocks] == 'F') {
                $sexe_colour = ' ancestor_woman';
                $backgr_col = "#FBDEC0";
            }
            if ($sexe[$i + $blocks] == 'M') {
                $sexe_colour = ' ancestor_man';
                $backgr_col =  "#C0F9FC";
            }
        } else { // empty square - give it background so lines won't show through
            $sexe_colour = ' ancestor_none';
        }
        echo '<div class="ancestorName' . $sexe_colour . '" style="background-color:' . $backgr_col . '; top: ' . $this_top . 'px; left: ' . $left . 'px; height: ' . $height . 'px; width:' . $width . 'px;';
        echo '">';
        if (isset($sexe[$i + $blocks]) && $sexe[$i + $blocks] != "") {
            echo ancestor_chart_person($i + $blocks, 'hour' . $data["size"]);
        } else {
            echo "&nbsp;"; // otherwise background color doesn't work and lines show through
        }
        echo '</div>';
        $this_top += $incr;
    }

    // *** long vertical line ***
    $this_top = $top + $line_drop;
    for ($i = 0; $i < $blocks / 2; $i++) {
        echo '<div class="hour_ancestor_split" style="top: ' . $this_top . 'px; left: ' . ($left + $width + 3) . 'px; height: ' . $hi . 'px;"></div>';
        $this_top += $gap;
    }
    // *** little horizontal line ***
    $this_top = $top + $line_drop;
    if ($i > 1) {
        for ($i = 0; $i < $blocks / 4; $i++) {
            echo '<div class="ancestor_line" style="top: ' . ($this_top + $hi / 2) . 'px; left: ' . ($left + $width + 12) . 'px; height: ' . ($hi * 2) . 'px;"></div>';
            $this_top += $gap * 2;
        }
    } else {
        echo '<div class="ancestor_line" style="border-bottom:none;top: ' . ($this_top + $hi / 2) . 'px; left: ' . ($left + $width + 12) . 'px; height:1px;"></div>';
    }
    // prepare for next generation
    $top += $incr / 2;
    $hi *= 2;
    $gap *= 2;
    $incr *= 2;
    $blocks /= 2;
    if ($x > $data["chosengenanc"] - 1 || $data["size"] < 45) { // maybe just: if($x==$data["chosengenanc"])     ;-)
        $left += $width + 20;
    } else {
        $left += $width / 2 + 20;
    }
}

// SET CHART DIMENSIONS AND CAPTIONS ^^^^^
if ($data["size"] == 50 || $data["size"] == 45) {
    if ($data["chosengenanc"] > 2) {
        $anc_len = (2 * ($width + 20)) + (($data["chosengenanc"] - 3) * (($data["size"] / 2) + 40));
    } else {
        $anc_len = $width + 20;
    }
} else {
    $anc_len = ($data["chosengenanc"] - 1) * ($width + 20);
}

if ($data["size"] == 50) {
    $desc_len = $data["chosengen"] * ($width + 60);
} elseif ($data["size"] == 45) {
    $desc_len = $data["chosengen"] * ($width + 50);
} else {
    $desc_len = $data["chosengen"] * ($width + $data["size"]);
}

$divlen = 10 + $anc_len + $desc_len;

$data["genarray"] = $genarray;
// *** Show descendant chart ***
include_once(__DIR__ . "/descendant_chart.php");
