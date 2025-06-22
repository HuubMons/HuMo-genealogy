<?php

/**
 * Descendant chart. Used to be part of family script, seperated in july 2023.
 */

$screen_mode = 'STAR';

$person_privacy = new PersonPrivacy;
$person_name = new PersonName;
$person_popup = new PersonPopup;

if (!isset($hourglass)) {
    $hourglass = false;
}
if ($hourglass === false) {
    // for png image generating
    echo '<script src="assets/html2canvas/html2canvas.min.js"></script>';
}

$genarray = $data["genarray"];

// YB: -- check browser type & version. we need this further on to detect IE7 with it's widely reported z-index bug
$browser_user_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

if ($hourglass === false) {
    // find rightmost and bottommost positions to calculate size of the canvas needed for png image
    $divlen = 0;
    $divhi = 0;
    $counter = count($genarray);
    for ($i = 0; $i < $counter; $i++) {
        if ($genarray[$i]["posx"] > $divlen) {
            $divlen = $genarray[$i]["posx"];
        }
        if ($genarray[$i]["posy"] > $divhi) {
            $divhi = $genarray[$i]["posy"];
        }
    }
    $divlen += 200;
    $divhi += 300;

    if ($humo_option["url_rewrite"] == 'j') {
        $path = 'descendant_chart/' . $tree_id . '/' . $data["family_id"] . '?';
        $path2 = 'descendant_chart/' . $tree_id . '/' . $data["family_id"] . '?';
    } else {
        $path = 'index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;';
        // Don't use &amp; for javascript.
        $path2 = 'index.php?page=descendant_chart&tree_id=' . $tree_id . '&id=' . $data["family_id"] . '&';
    }

    $dna_params = "";
    if ($data["dna"] != "none") {
        //$dna_params = '
        //	bn: "'.$data["base_person_name"].'",
        //	bs: "'.$data["base_person_sexe"].'",
        //	bf: "'.$data["base_person_famc"].'",
        //	bg: "'.$data["base_person_gednr"].'",';
        $dna_params = '&bn=' . $data["base_person_name"] . '&bs=' . $data["base_person_sexe"] . '&bf=' . $data["base_person_famc"] . '&bg=' . $data["base_person_gednr"];
    }

    // *** 20-08-2022: renewed jQuery and jQueryUI scripts ***
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
                window.location.href = "' . $path2 . 'main_person=' . $data["main_person"] .
        '&chosensize="+((endPos+1)*5)+"&chosengen=' . $data["chosengen"] .
        '&direction=' . $data["direction"] . '&dnachart=' . $data["dna"] . $dna_params . '";
                }
            startPos = endPos;
        });
    });
    </script>';

    // the width and length of following div are set with $divlen en $divhi in java function "showimg" 
    // (at bottom of this file) used to print chart.
?>
    <div id="png">

        <?= $data["descendant_header"]; ?>

        <?php
        if ($data["direction"] == 0) {  //vertical
            $latter = count($genarray) - 1;
            $the_height = $genarray[$latter]["posy"] + 130;
        } else {
            $hgt = 0;
            for ($e = 0; $e < count($genarray); $e++) {
                if ($genarray[$e]["posy"] > $hgt) {
                    $hgt = $genarray[$e]["posy"];
                }
            }
            $the_height = $hgt + 130;
        }

        //echo '<style type="text/css">';
        //echo '#doublescroll { position:relative; width:auto; height:' . $the_height . 'px; overflow: auto; overflow-y: hidden;z-index:10; }';
        //echo '</style>';
        //echo '<div id="doublescroll" class="wrapper" style="direction:' . $rtlmarker . ';">';
        ?>

        <div class="p-2 me-sm-2 genealogy_search" id="menubox">
            <!-- <div class="p-2 me-sm-2 genealogy_search d-print-none"> -->

            <div class="row">
                <div class="col-md-auto">
                    <h4>
                        <?php
                        if ($data["dna"] == "none") {
                            echo __('Descendant chart') . __(' of ') . $genarray[0]["nam"];
                        } elseif ($data["dna"] == "ydna" || $data["dna"] == "ydnamark") {
                            echo __('Same Y-DNA as ') . $data["base_person_name"];
                        } elseif ($data["dna"] == "mtdna" || $data["dna"] == "mtdnamark") {
                            echo __('Same mtDNA as ') . $data["base_person_name"];
                        }
                        ?>
                    </h4>
                </div>
            </div>

            <div class="row">

                <div class="col-md-auto">
                    <form method="POST" name="desc_form" action="<?= $path . 'chosensize=' . $data["size"]; ?>" style="display : inline;">
                        <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                        <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                        <input type="hidden" name="chosengen" value="<?= $data["chosengen"]; ?>">
                        <?php if ($data["dna"] != "none") { ?>
                            <input type="hidden" name="dnachart" value="<?= $data["dna"]; ?>">
                            <input type="hidden" name="bf" value="<?= $data["base_person_famc"]; ?>">
                            <input type="hidden" name="bs" value="<?= $data["base_person_sexe"]; ?>">
                            <input type="hidden" name="bn" value="<?= $data["base_person_name"]; ?>">
                            <input type="hidden" name="bg" value="<?= $data["base_person_gednr"]; ?>">
                        <?php } ?>
                        <input id="dirval" type="hidden" name="direction" value=""> <!-- will be filled in next lines -->
                        <?php if ($data["direction"] == "1") { ?>
                            <input type="button" name="dummy" value="<?= __('vertical'); ?>" onClick='document.desc_form.direction.value="0";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                        <?php } else { ?>
                            <input type="button" name="dummy" value="<?= __('horizontal'); ?>" onClick='document.desc_form.direction.value="1";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                        <?php } ?>
                    </form>
                </div>

                <div class="col-md-auto">
                    <input type="button" id="imgbutton" value="<?= __('Print'); ?>" onClick="showimg();" class="btn btn-sm btn-secondary">
                </div>

                <?php if ($data["dna"] != "none") { ?>
                    <div class="col-md-auto">
                        <?= __('DNA:'); ?>
                    </div>

                    <div class="col-md-auto">
                        <select name="dnachart" onChange="window.location=this.value" class="form-select form-select-sm">
                            <?php if ($data["base_person_sexe"] == "M") { ?>
                                <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?>&amp;dnachart=ydna&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=<?= $data["chosengen"]; ?>" <?= $data["dna"] == "ydna" ? 'selected' : ''; ?>>
                                    <?= __('Y-DNA Carriers only'); ?>
                                </option>

                                <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?>&amp;dnachart=ydnamark&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=<?= $data["chosengen"]; ?>" <?= $data["dna"] == "ydnamark" ? 'selected' : ''; ?>>
                                    <?= __('Y-DNA Mark carriers'); ?>
                                </option>
                            <?php
                            }

                            // if base person is male, only show mtDNA if there are ancestors since he can't have mtDNA descendants...
                            if ($data["base_person_sexe"] == "F" or ($data["base_person_sexe"] == "M" and isset($data["base_person_famc"]) and $data["base_person_famc"] != "")) {
                            ?>
                                <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?>&amp;dnachart=mtdna&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=<?= $data["chosengen"]; ?>" <?= $data["dna"] == "mtdna" ? 'selected' : ''; ?>>
                                    <?= __('mtDNA Carriers only'); ?>
                                </option>

                                <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?>&amp;dnachart=mtdnamark&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=<?= $data["chosengen"]; ?>" <?= $data["dna"] == "mtdnamark" ? 'selected' : ''; ?>>
                                    <?= __('mtDNA Mark carriers'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="col-md-auto">
                    <?= __('Nr. generations'); ?>:
                </div>

                <div class="col-md-auto">
                    <select name="chosengen" onChange="window.location=this.value" class="form-select form-select-sm">
                        <?php for ($i = 2; $i <= 15; $i++) { ?>
                            <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?><?= $data["dna"] != 'none' ? '&amp;dnachart=' . $data["dna"] : ''; ?>&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=<?= $i; ?>" <?= $i == $data["chosengen"] ? 'selected' : ''; ?>>
                                <?= $i; ?>
                            </option>
                        <?php } ?>

                        <!-- Option "All" for all generations -->
                        <option value="<?= $path; ?>main_person=<?= $data["main_person"]; ?>&amp;direction=<?= $data["direction"]; ?><?= $data["dna"] != 'none' ? '&amp;dnachart=' . $data["dna"] : ''; ?>&amp;chosensize=<?= $data["size"]; ?>&amp;chosengen=All" <?= $data["chosengen"] == "All" ? 'selected' : ''; ?>>
                            <?= __('All'); ?>
                        </option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <label for="amount"><?= __('Zoom level:'); ?></label>
                    <input type="text" id="amount" disabled="disabled" style="width:25px;border:0; color:#0000CC; font-weight:normal;font-size:115%;">
                </div>

                <div class="col-md-auto">
                    <div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>
                </div>

            </div>
        </div>

        <!-- TODO check div. It's too wide (is set in doublescroll script). Doublescroll isn't in use anymore. -->
        <style type="text/css">
            #doublescroll {
                position: relative;
                width: auto;
                height: <?= $the_height; ?>px;
                z-index: 10;
            }
        </style>
    <?php
    echo '<div id="doublescroll" class="wrapper" style="direction:' . $rtlmarker . ';">';

    // Test bootstrap container
    //echo '<div class="container-xxl overflow-auto">';
    //echo '<div style="height:200px;"></div>';
    //echo '<div class="container-md overflow-auto" style="z-index:10; top:300px;">';
    //echo '<div class="container-xxl overflow-auto">';
}

// *** Also used for hourglass script.
for ($w = 0; $w < count($genarray); $w++) {
    $xvalue = $genarray[$w]["posx"];
    $yvalue = $genarray[$w]["posy"];

    $sexe_colour = '';
    $backgr_col = "#FFFFFF";
    if ($genarray[$w]["sex"] == "v") {
        $sexe_colour = ' ancestor_woman';
        $backgr_col = "#FBDEC0";     //"#f8bdf1";
    } else {
        $sexe_colour = ' ancestor_man';
        $backgr_col =  "#C0F9FC";      //"#bbf0ff";
    }

    // *** Start person class and calculate privacy ***
    if (isset($genarray[$w]["gednr"]) && $genarray[$w]["gednr"]) {
        $man = $db_functions->get_person($genarray[$w]["gednr"]);
        $man_privacy = $person_privacy->get_privacy($man);
    }

    //echo '<div style="position:absolute; background-color:'.$bkcolor.';height:'.$data["vsize"].'px; width:'.$data["hsize"].'px; border:1px brown solid; left:'.$xvalue.'px; top:'.$yvalue.'px">';

    $bkgr = "";
    if (($data["dna"] == "ydnamark" || $data["dna"] == "mtdnamark" || $data["dna"] == "ydna" || $data["dna"] == "mtdna") && $genarray[$w]["dna"] == 1) {
        $bkgr = "border:3px solid #999999;background-color:" . $backgr_col . ";";
        if (isset($genarray[$w]["gednr"]) && $genarray[$w]["gednr"] == $data["base_person_gednr"]) {  // base person
            $bkgr = "border:3px solid red;background-color:" . $backgr_col . ";";
        }
    } else {
        $bkgr = "border:1px solid #8C8C8C;background-color:" . $backgr_col . ";";
    }
    if ($genarray[$w]["gen"] == 0 && $hourglass === true) {
        $bkgr = "background-color:" . $backgr_col . ";";
    }
    ?>

        <div class="ancestorName<?= $sexe_colour; ?>" style="<?= $bkgr; ?>position:absolute; height:<?= $data["vsize"]; ?>px; width:<?= $data["hsize"]; ?>px; left:<?= $xvalue; ?>px; top:<?= $yvalue; ?>px;">
            <?php
            $replacement_text = '';
            if ($data["size"] >= 25) {
                if (strpos($browser_user_agent, "msie 7.0") === false) {
                    if ($data["size"] == 50) {

                        // *** Show picture ***
                        if (!$man_privacy && $user['group_pictures'] == 'j') {
                            //  *** Path can be changed per family tree ***
                            global $dataDb;
                            $tree_pict_path = $dataDb->tree_pict_path;
                            if (substr($tree_pict_path, 0, 1) === '|') {
                                $tree_pict_path = 'media/';
                            }
                            $picture_qry = $db_functions->get_events_connect('person', $man->pers_gedcomnumber, 'picture');
                            // *** Only show 1st picture ***
                            if (isset($picture_qry[0])) {
                                $pictureDb = $picture_qry[0];
                                $showMedia = new ShowMedia();
                                $replacement_text .= $showMedia->print_thumbnail($tree_pict_path, $pictureDb->event_event, 60, 65, 'float:left; margin:5px;');
                            }
                        }

                        //$replacement_text.= '<strong>'.$genarray[$w]["nam"].'</strong>';
                        //$replacement_text.= '<span class="anc_box_name">'.$genarray[$w]["nam"].'</span>';
                        $replacement_text .= '<span class="anc_box_name">' . $genarray[$w]["nam"] . '</span>';
                        if ($man_privacy) {
                            $replacement_text .= '<br>' . __(' PRIVACY FILTER') . '<br>';  //Tekst privacy weergeven
                        } else {
                            //if ($man->pers_birth_date OR $man->pers_birth_place){
                            if ($man->pers_birth_date) {
                                //$replacement_text.= '<br>'.__('*').$dirmark1.' '.date_place($man->pers_birth_date,$man->pers_birth_place);
                                $replacement_text .= '<br>' . __('*') . $dirmark1 . ' ' . date_place($man->pers_birth_date, '');
                            }
                            //elseif ($man->pers_bapt_date OR $man->pers_bapt_place){
                            elseif ($man->pers_bapt_date) {
                                //$replacement_text.= '<br>'.__('~').$dirmark1.' '.date_place($man->pers_bapt_date,$man->pers_bapt_place);
                                $replacement_text .= '<br>' . __('~') . $dirmark1 . ' ' . date_place($man->pers_bapt_date, '');
                            }

                            //if ($man->pers_death_date OR $man->pers_death_place){
                            if ($man->pers_death_date) {
                                //$replacement_text.= '<br>'.__('&#134;').$dirmark1.' '.date_place($man->pers_death_date,$man->pers_death_place);
                                $replacement_text .= '<br>' . __('&#134;') . $dirmark1 . ' ' . date_place($man->pers_death_date, '');
                            }
                            //elseif ($man->pers_buried_date OR $man->pers_buried_place){
                            elseif ($man->pers_buried_date) {
                                //$replacement_text.= '<br>'.__('[]').$dirmark1.' '.date_place($man->pers_buried_date,$man->pers_buried_place);
                                $replacement_text .= '<br>' . __('[]') . $dirmark1 . ' ' . date_place($man->pers_buried_date, '');
                            }

                            if ($genarray[$w]["non"] == 0) { // otherwise for an unmarried child it would give the parents' marriage!
                                $ownfam = $db_functions->get_family($genarray[$w]["fams"]);
                                //if ($ownfam->fam_marr_date OR $ownfam->fam_marr_place){
                                // *** Don't check for date. Otherwise living together persons are missing ***
                                //if ($ownfam->fam_marr_date){
                                //$replacement_text.= '<br>'.__('X').$dirmark1.' '.date_place($ownfam->fam_marr_date,$ownfam->fam_marr_place);

                                if ($ownfam->fam_marr_date || $ownfam->fam_marr_place) {
                                    $replacement_text .= '<br>' . __('X');
                                } else {
                                    // *** Relation ***
                                    $replacement_text .= '<br>' . __('&amp;');
                                }

                                if ($ownfam->fam_marr_date) {
                                    $replacement_text .= $dirmark1 . ' ' . date_place($ownfam->fam_marr_date, '') . ' ';
                                }

                                // *** Jan. 2022: Show spouse ***
                                if (isset($genarray[$w]["sps"]) && $genarray[$w]["sps"] != '') {
                                    if ($ownfam->fam_marr_date || $ownfam->fam_marr_place) {
                                        //$replacement_text.= "&nbsp;".__(' to: ')."<br>";
                                        $replacement_text .= __(' to: ') . '<br>';
                                    } else {
                                        // *** Don't show 'to: ' for relations.
                                        $replacement_text .= ' ';
                                    }
                                    $replacement_text .= '<i>' . $genarray[$w]["sps"] . '</i>';
                                }
                                //}
                            }
                        }
                    } elseif ($data["size"] == 45) {
                        $replacement_text .= $genarray[$w]["nam"];
                    } elseif ($data["size"] == 40) {
                        $replacement_text .= '<span class="wordwrap" style="font-size:75%">' . $genarray[$w]["short"] . '</span>';
                    } elseif ($data["size"] >= 25 && $data["size"] < 40) {
                        $replacement_text .= $genarray[$w]["init"];
                    }
                }
            } else {
                if (isset($genarray[$w]["fams"]) and isset($genarray[$w]["gednr"])) {
                    if (strpos($browser_user_agent, "chrome") !== false or strpos($browser_user_agent, "safari") !== false) {
                        $replacement_text .= "&nbsp;";
                    }
                    //  (Chrome and Safari need some character here - even &nbsp - or else popup won't work..!
                }
            }
            //$replacement_text.='</a>';

            // *** POP-UP box ***
            $extra_popup_text = '';

            if ($genarray[$w]["2nd"] == 1) {
                $extra_popup_text .= $genarray[$w]["huw"] . "<br>";
            }

            if ($genarray[$w]["non"] != 1) {
                if (isset($genarray[$w]["spgednr"]) && $genarray[$w]["spgednr"]) {
                    $woman = $db_functions->get_person($genarray[$w]["spgednr"]);
                    $woman_privacy = $person_privacy->get_privacy($woman);
                }

                // *** Marriage data ***
                $extra_popup_text .= '<br>' . $genarray[$w]["htx"] . "<br>";
                if (isset($woman)) {
                    $name = $person_name->get_person_name($woman, $woman_privacy);
                    if (isset($genarray[$w]["spfams"]) && isset($genarray[$w]["spgednr"]) && isset($genarray[$w]["sps"])) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $person_link = new PersonLink();
                        $url = $person_link->get_person_link($woman);

                        $extra_popup_text .= '<a href="' . $url . '">' . '<strong>' . $name["standard_name"] . '</strong></a>';
                    } else {
                        $extra_popup_text .= $name["standard_name"];
                    }

                    if ($woman_privacy) {
                        $extra_popup_text .= __(' PRIVACY FILTER') . '<br>';  //Tekst privacy weergeven
                    } else {
                        if ($woman->pers_birth_date || $woman->pers_birth_place) {
                            $extra_popup_text .= __('born') . $dirmark1 . ' ' . date_place($woman->pers_birth_date, $woman->pers_birth_place) . '<br>';
                        }

                        if ($woman->pers_death_date || $woman->pers_death_place) {
                            $extra_popup_text .= __('died ') . $dirmark1 . ' ' . date_place($woman->pers_death_date, $woman->pers_death_place) . '<br>';
                        }
                    }
                } else {
                    $extra_popup_text .= __('N.N.');
                }
            }

            if (isset($man)) {
                echo $person_popup->person_popup_menu($man, $man_privacy, true, $replacement_text, $extra_popup_text);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                //$person_link = new PersonLink();
                //$url = $person_link->get_person_link($man);
                //echo '<a href="'.$url.'"><span clas="nam" style="font-size:10px; color: #000000; text-decoration: none;">'.$replacement_text.'</span></a>';
            }
            ?>
        </div>

    <?php
    if ($data["direction"] == 0) { // if vertical
        // draw dotted line from first marriage to following marriages
        if (isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] == 1) {
            $startx = $genarray[$w - 1]["posx"] + $data["hsize"] + 2;
            $starty = $genarray[$w - 1]["posy"] + ($data["vsize"] / 2);
            $width = ($genarray[$w]["posx"]) - ($genarray[$w - 1]["posx"] + $data["hsize"]) - 2;
            echo  '<div style="position:absolute;border:1px blue dashed;height:2px;width:' . $width . 'px;left:' . $startx . 'px;top:' . $starty . 'px"></div>';
        }

        // draw line to children
        if ($genarray[$w]["nrc"] != 0) {
            $startx = $genarray[$w]["posx"] + ($data["hsize"] / 2);
            $starty = $genarray[$w]["posy"] + $data["vsize"] + 2;
            echo  '<div class="chart_line" style="position:absolute; height:' . (($data["vdist"] / 2) - 2) . 'px; width:1px; left:' . $startx . 'px; top:' . $starty . 'px"></div>';
        }

        // draw line to parent
        if ($genarray[$w]["gen"] != 0 && $genarray[$w]["2nd"] != 1) {
            $startx = $genarray[$w]["posx"] + ($data["hsize"] / 2);
            $starty = $genarray[$w]["posy"] - ($data["vdist"] / 2);
            echo '<div class="chart_line" style="position:absolute; height:' . ($data["vdist"] / 2) . 'px;width:1px;left:' . $startx . 'px;top:' . $starty . 'px"></div>';
        }

        // draw horizontal line from 1st child in fam to last child in fam
        if ($genarray[$w]["gen"] != 0) {
            $parent = $genarray[$w]["par"];
            if ($genarray[$w]["chd"] == $genarray[$parent]["nrc"]) { // last child in fam
                $z = $w;
                while ($genarray[$z]["2nd"] == 1) { //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
                    $z--;
                }
                $startx = $genarray[$parent]["fst"] + ($data["hsize"] / 2);
                $starty = $genarray[$z]["posy"] - ($data["vdist"] / 2);
                $width = $genarray[$z]["posx"] - $genarray[$parent]["fst"];
                echo '<div class="chart_line" style="position:absolute; height:1px; width:' . $width . 'px; left:' . $startx . 'px; top:' . $starty . 'px"></div>';
            }
        }
    } // end if vertical

    else { // if horizontal
        // draw dotted line from first marriage to following marriages
        if (isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] == 1) {
            $starty = $genarray[$w - 1]["posy"] + $data["vsize"] + 2;
            $startx = $genarray[$w - 1]["posx"] + ($data["hsize"] / 2);
            $height = ($genarray[$w]["posy"]) - ($genarray[$w - 1]["posy"] + $data["vsize"]) - 2;
            echo  '<div style="position:absolute;border:1px blue dashed;height:' . $height . 'px; width:3px; left:' . $startx . 'px;top:' . $starty . 'px"></div>';
        }

        // draw line to children
        if ($genarray[$w]["nrc"] != 0) {
            $starty = $genarray[$w]["posy"] + ($data["vsize"] / 2);
            $startx = $genarray[$w]["posx"] + $data["hsize"] + 3;
            echo '<div class="chart_line" style="position:absolute; height:1px; width:' . (($data["hdist"] / 2) - 2) . 'px; left:' . $startx . 'px; top:' . $starty . 'px"></div>';
        }

        // draw line to parent
        if ($genarray[$w]["gen"] != 0 && $genarray[$w]["2nd"] != 1) {
            $starty = $genarray[$w]["posy"] + ($data["vsize"] / 2);
            $startx = $genarray[$w]["posx"] - ($data["hdist"] / 2);
            echo '<div class="chart_line" style="position:absolute; width:' . ($data["hdist"] / 2) . 'px; height:1px; left:' . $startx . 'px; top:' . $starty . 'px"></div>';
        }

        // draw vertical line from 1st child in fam to last child in fam
        if ($genarray[$w]["gen"] != 0) {
            $parent = $genarray[$w]["par"];
            if ($genarray[$w]["chd"] == $genarray[$parent]["nrc"]) { // last child in fam
                $z = $w;
                while ($genarray[$z]["2nd"] == 1) { //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
                    $z--;
                }
                $starty = $genarray[$parent]["fst"] + ($data["vsize"] / 2);
                $startx = $genarray[$z]["posx"] - ($data["hdist"] / 2);
                $height = $genarray[$z]["posy"] - $genarray[$parent]["fst"];
                echo '<div class="chart_line" style="position:absolute; width:1px; height:' . $height . 'px; left:' . $startx . 'px; top:' . $starty . 'px"></div>';
            }
        }
    } // end if horizontal
}
    ?>

    </div> <!-- png -->
    <!-- </div> --> <!-- doublescroll -->

    <?php
    // YB:
    // before creating the image (for printing the chart) we want to hide unnecessary items such as the help link, the menu box etc
    // we also have to set the width and height of the "png" div (this can't be set before because then the double scrollbars won't work
    // after generating the image, all those items are returned to their previous state....
    // *** 19-08-2022: script updated by Huub ***
    if ($hourglass === false) {
        echo "<script>
        function showimg() {
            //document.getElementById('helppopup').style.visibility = 'hidden';
            document.getElementById('menubox').style.visibility = 'hidden';
            document.getElementById('imgbutton').style.visibility = 'hidden';
            document.getElementById('nav-tab').style.visibility = 'hidden';
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

                // *** Show image at the same page ***
                //document.body.appendChild(canvas);

                //document.getElementById('helppopup').style.visibility = 'visible';
                document.getElementById('menubox').style.visibility = 'visible';
                document.getElementById('imgbutton').style.visibility = 'visible';
                document.getElementById('png').style.width = 'auto';
                document.getElementById('png').style.height= 'auto';

                var newWin = window.open();
                newWin.document.open();
                newWin.document.write('<!DOCTYPE html><head></head><body>" . __('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ') . "<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>" . __('If you have a plotter you can use its software to print the image on one large sheet.') . "<br><br><img src=\"' + img + '\"></body></html>');
                newWin.document.close();
            });
        }
        </script>";
    }
    ?>

    <!--
    <script>
        function DoubleScroll(element) {
            var scrollbar = document.createElement('div');
            scrollbar.appendChild(document.createElement('div'));
            scrollbar.style.overflow = 'auto';
            scrollbar.style.overflowY = 'hidden';
            scrollbar.firstChild.style.width = element.scrollWidth + 'px';
            scrollbar.firstChild.style.paddingTop = '1px';
            scrollbar.firstChild.style.height = '20px';
            scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
            scrollbar.onscroll = function() {
                element.scrollLeft = scrollbar.scrollLeft;
            };
            element.onscroll = function() {
                scrollbar.scrollLeft = element.scrollLeft;
            };
            element.parentNode.insertBefore(scrollbar, element);
        }
        DoubleScroll(document.getElementById('doublescroll'));
    </script>
-->