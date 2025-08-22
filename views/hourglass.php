<?php

/**
 * Aug. 2023 Yossi Beck: Added HOURGLASS script.
 * Made with extensive use of existing files:
 * - report_ancestor (included with minor changes in the file)
 * - report_descendant (included with minor changes in the file)
 * - family script (included with minor changes in the file)
 * Other additions and alterations:
 * - hourglass item added to person popup menu.
 * - icon added: /images/hourglass.gif
 * - class added to gedcom.css and silverline.css for graph lines
 * 
 * Nov. 2023 Huub: updated to MVC model.
 * 
 * TODO:
 * - save slider position
 * - think about names in size 8
 */

$ancestorBox = new \Genealogy\Include\AncestorBox();

$genarray = $data["genarray"];
$hourglass = true;

// *** Ancestor part of report ***
$_GET['id'] = $_GET['main_person'];
$sexe = $data["sexe"];

// THE HORIZONTALLY ALIGN POSITION OF THE BASE PERSON IS DONE IN DESC CHART AND ANCESTOR CHART

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

if ($genarray[0]["posy"] < $anc_top) {
    // if desc base pers higher on screen than base person of ancestor chart - has to be lowered to there.
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
if ($genarray[0]["posy"] > $anc_top) {
    // if desc base person lower, we have to lower base person of anc chart.
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

$vars['pers_family'] = $data["family_id"];
$path_tmp = $processLinks->get_link($uri_path, 'hourglass', $tree_id, true, $vars);
$path_tmp .= "main_person=" . $data["main_person"] . '&amp;screen_mode=HOUR';
?>

<h1 class="standard_header" style="margin:auto; text-align: center;">
    <?= __('Hourglass chart'); ?>
</h1>

<script src="assets/html2canvas/html2canvas.min.js"></script>

<div class="p-2 me-sm-2 genealogy_search d-print-none">
    <div class="row">
        <div class="col-md-auto">
            <input type="button" id="imgbutton" value="<?= __('Print'); ?>" onClick="showimg();" class="btn btn-sm btn-secondary">
        </div>

        <div class="col-md-auto">
            <?php
            // min:0 (for extra first step - now 10 steps: 0-9), then twice value +1 so on display first step is shown as 1, not 0
            // *** Don't use &amp; in link in javascript ***
            $path_tmp = str_replace('&amp;', '&', $path_tmp);

            echo '<script>
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
            </script>';
            ?>

            <label for="amount"><?= __('Zoom level:'); ?></label>
            <input type="text" id="amount" disabled="disabled" style="width:28px;border:0; color:#0000CC; font-weight:normal;font-size:115%;">
            <div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>
        </div>

        <div class="col-md-auto">
            <?= __('Hourglass chart') . __(' of ') . '<b>' . $genarray[0]["nam"] . '</b>'; ?>
        </div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-md-auto">
            <span style="font-size:130%">&#8678;&#8678;&#8678; <?= __('Ancestors') . '. ' . __('Nr. generations') . ':'; ?></span>
        </div>
        <div class="col-md-auto">
            <select name="chosengenanc" onChange="window.location=this.value" class="form-select form-select-sm">
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
        </div>

        <div class="col-md-2"></div>

        <div class="col-md-auto">
            <span style="font-size:130%"><?= __('Descendants') . '. ' . __('Nr. generations'); ?>:</span>
        </div>
        <div class="col-md-auto">
            <select name="chosengen" onChange="window.location=this.value" class="form-select form-select-sm">
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
        </div>
        <div class="col-md-auto">
            <span style="font-size:130%"> &#8680;&#8680;&#8680;</span>
        </div>
    </div>
</div>


<?php
// Start DIV FOR IMAGE (to print image of chart with plotter) ^^^^^
// following div gets width and length in imaging java function showimg() (at bottom) otherwise double scrollbars won't work.
?>
<div id="png">

    <?php
    // Start DIV FOR DOUBLESCROLL (horizontal scrollbars top and bottom ^^^^^^^^^^^^^^^^^^^^^^^
    echo '<style type="text/css">
#doublescroll { position:relative; width:auto; height:' . $div_hi . 'px; overflow: auto; overflow-y: hidden; }
#doublescroll p { margin: 0; padding: 1em; white-space: nowrap; }
</style>';
    echo '<div id="doublescroll">';

    // *** Print the ancestor chart ***
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
                    $sexe_colour = ' box_woman';
                    //$backgr_col = "#FBDEC0";
                }
                if ($sexe[$i + $blocks] == 'M') {
                    $sexe_colour = ' box_man';
                    //$backgr_col =  "#C0F9FC";
                }
            } else {
                // empty square - give it background so lines won't show through
                $sexe_colour = ' ancestor_none';
            }
            echo '<div class="ancestorName' . $sexe_colour . '" style="background-color:' . $backgr_col . '; top: ' . $this_top . 'px; left: ' . $left . 'px; height: ' . $height . 'px; width:' . $width . 'px;';
            echo '">';
            if (isset($sexe[$i + $blocks]) && $sexe[$i + $blocks] != "") {
                echo $ancestorBox->ancestorBox($i + $blocks, 'hour' . $data["size"]);
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
        if ($x > $data["chosengenanc"] - 1 || $data["size"] < 45) {
            // maybe just: if($x==$data["chosengenanc"])     ;-)
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
    ?>
</div>

<?php
$divhi = 0;
for ($i = 0; $i < count($genarray); $i++) {
    if ($genarray[$i]["posx"] > $divlen) {
        //$divlen = $genarray[$i]["posx"];
    }
    if ($genarray[$i]["posy"] > $divhi) {
        $divhi = $genarray[$i]["posy"];
    }
}
//$divlen += 200;
$divhi += 300;

echo "<script>
    function showimg() {
        document.getElementById('png').style.width = '" . $divlen . "px';
        document.getElementById('png').style.height= '" . $divhi . "px';

        // *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
        const el = document.querySelectorAll('.ancestorName');
        el.forEach((elItem) => {
            //elItem.style.setProperty('border-radius', 'none', 'important');
            elItem.style.setProperty('box-shadow', 'none', 'important');
        });

        html2canvas(document.querySelector('#png')).then(canvas => {
            var img = canvas.toDataURL();
            document.getElementById('png').style.width = 'auto';
            document.getElementById('png').style.height= 'auto';

            var newWin = window.open();
            newWin.document.open();
            newWin.document.write('<!DOCTYPE html><head></head><body>" . __('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ') . "<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>" . __('If you have a plotter you can use its software to print the image on one large sheet.') . "<br><br><img src=\"' + img + '\"></body></html>');
            newWin.document.close();
            });
    }
    </script>";
