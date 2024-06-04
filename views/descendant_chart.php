<?php

/**
 * Descendant chart. Used to be part of family script, seperated in july 2023.
 */

$screen_mode = 'STAR';

if (!isset($hourglass)) {
    $hourglass = false;
}
if ($hourglass === false) {
    // for png image generating
    echo '<script src="include/html2canvas/html2canvas.min.js"></script>';
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

    // the width and length of following div are set with $divlen en $divhi in java function "showimg" 
    // (at bottom of this file) otherwise double scrollbars won't work.
?>
    <div id="png">

        <!--  HELP POPUP -->
        <div id="helppopup" class="<?= $rtlmarker; ?>sddm" style="position:absolute;left:10px;top:10px;display:inline;">
            <?php
            echo '<a href="#" style="display:inline" ';
            echo 'onmouseover="mopen(event,\'help_menu\',0,0)" onmouseout="mclosetime()">';
            echo '<b>' . __('Help') . '</b></a>&nbsp;';

            //echo '<div style="z-index:40; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
            ?>
            <div class="sddm_fixed" style="z-index:10; padding:4px; text-align:<?= $alignmarker; ?>;  direction:<?= $rtlmarker; ?>;" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                <?php
                echo __('<b>USE:</b>
<p><b>Hover over square:</b> Display popup menu with details and report & chart options<br>
<b>Click on square:</b> Move this person to the center of the chart<br>
<b>Click on spouse\'s name in popup menu:</b> Go to spouse\'s family page<br><br>
<b>LEGEND:</b>');

                echo '<p><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #81bef7 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;' . __('Male') . '</br>';
                echo '<span style="background-image: linear-gradient(to bottom, #ffffff 0%, #f5bca9 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;' . __('Female') . '</br>';
                if ($data["dna"] == "ydna" || $data["dna"] == "ydnamark" || $data["dna"] == "mtdna" || $data["dna"] == "mtdnamark") {
                    echo '<p style="line-height:3px"><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #81bef7 100%); border:3px solid #999999;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;' . __('Male Y-DNA or mtDNA carrier (Base person has red border)') . '</p>';
                    echo '<p style="line-height:10px"><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #f5bca9 100%); border:3px solid #999999;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;' . __('Female MtDNA carrier (Base person has red border)') . '</p>';
                }
                echo '<p><span style="color:blue">=====</span>&nbsp;' . __('Additional marriage of same person') . '<br><br>';
                echo __('<b>SETTINGS:</b>
<p>Horizontal/Vertical button: toggle direction of the chart from top-down to left-right<br>
<b>Nr. Generations:</b> choose between 2 - 15 generations<br>
(large number of generations will take longer to generate)<br>
<b>Box size:</b> Use the slider to choose display size (9 steps): <br>
step 1-3: small boxes with popup for details<br>
step 4-7: larger boxes with initials of name + popup for details<br>
step 8:   rectangles with name inside + popup with further details<br>
step 9:   large rectangles with name, birth and death details + popup with further details');

                ?>
            </div>
        </div>

        <?php
        //=================================
        if ($data["dna"] == "none") {
            //echo '<h1 class="standard_header" style="align:center; text-align: center;"><b>' . __('Descendant chart') . __(' of ') . $genarray[0]["nam"] . '</b>';
            echo $data["descendant_header"];
        } elseif ($data["dna"] == "ydna" || $data["dna"] == "ydnamark") {
            echo '<h1 class="standard_header" style="align:center; text-align: center;"><b>' . __('Same Y-DNA as ') . $data["base_person_name"] . '</b></h1>';
        } elseif ($data["dna"] == "mtdna" || $data["dna"] == "mtdnamark") {
            echo '<h1 class="standard_header" style="align:center; text-align: center;"><b>' . __('Same mtDNA as ') . $data["base_person_name"] . '</b></h1>';
        }
        ?>
        <br><input type="button" id="imgbutton" value="<?= __('Get image of chart for printing (allow popup!)'); ?>" onClick="showimg();" class="btn btn-sm btn-secondary">

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

        // generation and size choice box:
        if ($data["dna"] == "none") {
            $boxwidth = "640";
        } // regular descendant chart
        else {
            $boxwidth = "850";
        } // DNA charts
        ?>
        <div id="menubox" class="search_bar" style="margin-top:5px; direction:ltr; z-index:20; width:<?= $boxwidth; ?>px; text-align:left;">
            <div style="display:inline;">
                <?php
                if ($humo_option["url_rewrite"] == 'j') {
                    $path = 'descendant_chart/' . $tree_id . '/' . $data["family_id"] . '?';
                    $path2 = 'descendant_chart/' . $tree_id . '/' . $data["family_id"] . '?';
                } else {
                    $path = 'index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;';
                    // Don't use &amp; for javascript.
                    $path2 = 'index.php?page=descendant_chart&tree_id=' . $tree_id . '&id=' . $data["family_id"] . '&';
                }
                ?>

                <form method="POST" name="desc_form" action="<?= $path . 'chosensize=' . $data["size"]; ?>" style="display : inline;">
                    <?php
                    echo '<input type="hidden" name="chosengen" value="' . $data["chosengen"] . '">';
                    echo '<input type="hidden" name="main_person" value="' . $data["main_person"] . '">';
                    echo '<input type="hidden" name="database" value="' . $database . '">';
                    if ($data["dna"] != "none") {
                        echo '<input type="hidden" name="dnachart" value="' . $data["dna"] . '">';
                        echo '<input type="hidden" name="bf" value="' . $data["base_person_famc"] . '">';
                        echo '<input type="hidden" name="bs" value="' . $data["base_person_sexe"] . '">';
                        echo '<input type="hidden" name="bn" value="' . $data["base_person_name"] . '">';
                        echo '<input type="hidden" name="bg" value="' . $data["base_person_gednr"] . '">';
                    }
                    ?>
                    <input id="dirval" type="hidden" name="direction" value=""> <!-- will be filled in next lines -->
                    <?php if ($data["direction"] == "1") { ?>
                        <input type="button" name="dummy" value="<?= __('vertical'); ?>" onClick='document.desc_form.direction.value="0";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                    <?php } else { ?>
                        <input type="button" name="dummy" value="<?= __('horizontal'); ?>" onClick='document.desc_form.direction.value="1";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                    <?php } ?>
                </form>

                <?php
                // TODO check code. This query isn't used?
                //$result = $dbh->query("SELECT pers_sexe FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber ='" . $data["main_person"] . "'");
                //$resultDb = $result->fetch(PDO::FETCH_OBJ);
                // TODO cleanup code
                if ($data["dna"] != "none") {
                    echo "&nbsp;&nbsp;" . __('DNA: ');
                ?>
                    <select name="dnachart" style="width:150px" onChange="window.location=this.value">
                        <?php
                        if ($data["base_person_sexe"] == "M") {        // only show Y-DNA option if base person is male
                            $selected = "selected";
                            if ($data["dna"] != "ydna") {
                                $selected = "";
                            }
                            echo '<option value="' . $path . 'main_person=' .
                                $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;dnachart=' . "ydna" . '&amp;chosensize=' .
                                $data["size"] . '&amp;chosengen=' . $data["chosengen"] . '" ' . $selected . '>' . __('Y-DNA Carriers only') . '</option>';

                            $selected = "";
                            if ($data["dna"] == "ydnamark") {
                                $selected = "selected";
                            }
                            echo '<option value="' . $path . 'main_person=' .
                                $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;dnachart=' . "ydnamark" . '&amp;chosensize=' .
                                $data["size"] . '&amp;chosengen=' . $data["chosengen"] . '" ' . $selected . '>' . __('Y-DNA Mark carriers') . '</option>';
                        }

                        if ($data["base_person_sexe"] == "F" or ($data["base_person_sexe"] == "M" and isset($data["base_person_famc"]) and $data["base_person_famc"] != "")) {
                            // if base person is male, only show mtDNA if there are ancestors since he can't have mtDNA descendants...
                            $selected = "";
                            if ($data["dna"] == "mtdna") {
                                $selected = "selected";
                            }
                            echo '<option value="' . $path . 'main_person=' .
                                $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;dnachart=' . "mtdna" . '&amp;chosensize=' .
                                $data["size"] . '&amp;chosengen=' . $data["chosengen"] . '" ' . $selected . '>' . __('mtDNA Carriers only') . '</option>';
                            if ($data["base_person_sexe"]  == "F") {
                                $selected = "selected";
                                if ($data["dna"] != "mtdnamark") {
                                    $selected = "";
                                }
                            } else {
                                $selected = "";
                                if ($data["dna"] == "mtdnamark") {
                                    $selected = "selected";
                                }
                            }
                            echo '<option value="' . $path . 'main_person=' .
                                $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;dnachart=' . "mtdnamark" . '&amp;chosensize=' .
                                $data["size"] . '&amp;chosengen=' . $data["chosengen"] . '" ' . $selected . '>' . __('mtDNA Mark carriers') . '</option>';
                        }
                        ?>
                    </select>
                <?php } ?>
            </div>

            &nbsp;&nbsp;&nbsp;<?= __('Nr. generations'); ?>:
            <select name="chosengen" onChange="window.location=this.value">
                <?php
                for ($i = 2; $i <= 15; $i++) {
                    echo '<option value="' . $path . 'main_person=' . $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;dnachart=' . $data["dna"] .
                        '&amp;chosensize=' . $data["size"] . '&amp;chosengen=' . $i . '" ';
                    if ($i == $data["chosengen"]) {
                        echo "selected=\"selected\" ";
                    }
                    echo ">" . $i . '</option>' . "\n";
                }

                // *** Option "All" for all generations ***
                echo '<option value="' . $path . 'main_person=' . $data["main_person"] . '&amp;direction=' . $data["direction"] . '&amp;database=' . $database .
                    '&amp;dnachart=' . $data["dna"] . '&amp;chosensize=' .  $data["size"] . '&amp;chosengen=All" ';
                if ($data["chosengen"] == "All") echo "selected=\"selected\" ";
                echo ">" . "All" . "</option>";
                ?>
            </select>

            &nbsp;&nbsp;
            <?php
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
            ?>
            <label for="amount"><?= __('Zoom level:'); ?></label>
            <input type="text" id="amount" disabled="disabled" style="width:25px;border:0; color:#0000CC; font-weight:normal;font-size:115%;">
            <div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>
        </div>
    <?php
} // end if not hourglass

if ($hourglass === false) {
    echo '<style type="text/css">';
    echo '#doublescroll { position:relative; width:auto; height:' . $the_height . 'px; overflow: auto; overflow-y: hidden;z-index:10; }';
    echo '</style>';
    echo '<div id="doublescroll" class="wrapper" style="direction:' . $rtlmarker . ';">';
}

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
        $man_cls = new person_cls($man);
        $man_privacy = $man_cls->privacy;
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
                                $picture = show_picture($tree_pict_path, $pictureDb->event_event, 60, 65);
                                //$replacement_text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" height="65px">';
                                //$replacement_text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'"';
                                $replacement_text .= '<img src="' . $picture['path'] . $picture['thumb'] . $picture['picture'] . '" style="float:left; margin:5px;" alt="' . $pictureDb->event_text . '" width="' . $picture['width'] . '"';
                                //if (isset($picture['height'])) $replacement_text.=' height="'.$picture['height'].'"';
                                $replacement_text .= '>';
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
                // *** Start person class and calculate privacy ***
                $woman_cls = ''; // prevent use of $woman_cls from previous wife if another wife is NN
                if (isset($genarray[$w]["spgednr"]) && $genarray[$w]["spgednr"]) {
                    @$woman = $db_functions->get_person($genarray[$w]["spgednr"]);
                    $woman_cls = new person_cls($woman);
                    $woman_privacy = $woman_cls->privacy;
                }

                // *** Marriage data ***
                $extra_popup_text .= '<br>' . $genarray[$w]["htx"] . "<br>";
                if ($woman_cls) {
                    $name = $woman_cls->person_name($woman);
                    if (isset($genarray[$w]["spfams"]) && isset($genarray[$w]["spgednr"]) && isset($genarray[$w]["sps"])) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $woman_cls->person_url2($woman->pers_tree_id, $woman->pers_famc, $woman->pers_fams, $woman->pers_gedcomnumber);

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
                echo $man_cls->person_popup_menu($man, true, $replacement_text, $extra_popup_text);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                //$url=$man_cls->person_url2($man->pers_tree_id,$man->pers_famc,$man->pers_fams,$man->pers_gedcomnumber);
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
    <br><br>
    <!-- </div> --> <!-- doublescroll -->

    <?php
    // YB:
    // before creating the image we want to hide unnecessary items such as the help link, the menu box etc
    // we also have to set the width and height of the "png" div (this can't be set before because then the double scrollbars won't work
    // after generating the image, all those items are returned to their previous state....
    // *** 19-08-2022: script updated by Huub ***
    echo '<script>';
    if ($hourglass === false) {
        echo "
        function showimg() {
            document.getElementById('helppopup').style.visibility = 'hidden';
            document.getElementById('menubox').style.visibility = 'hidden';
            document.getElementById('imgbutton').style.visibility = 'hidden';
            document.getElementById('png').style.width = '" . $divlen . "px';
            document.getElementById('png').style.height= '" . $divhi . "px';

            // *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
            const el = document.querySelectorAll('.ancestorName');
            el.forEach((elItem) => {
                //elItem.style.setProperty('border-radius', 'none', 'important');
                elItem.style.setProperty('box-shadow', 'none', 'important');
            });

            // *** Previous version of html2canvas ***
            //html2canvas( [ document.getElementById('png') ], {
            //	onrendered: function( canvas ) {

                html2canvas(document.querySelector('#png')).then(canvas => {
                    var img = canvas.toDataURL();

                    // *** Show image at the same page ***
                    //document.body.appendChild(canvas);

                    document.getElementById('helppopup').style.visibility = 'visible';
                    document.getElementById('menubox').style.visibility = 'visible';
                    document.getElementById('imgbutton').style.visibility = 'visible';
                    document.getElementById('png').style.width = 'auto';
                    document.getElementById('png').style.height= 'auto';

                    var newWin = window.open();
                    newWin.document.open();
                    newWin.document.write('<!DOCTYPE html><head></head><body>" . __('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ') . "<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>" . __('If you have a plotter you can use its software to print the image on one large sheet.') . "<br><br><img src=\"' + img + '\"></body></html>');
                    newWin.document.close();
                }

            //}
            );
        }
        ";
    } else {
        // *** Printscreen of hourglass page ***

        // TODO check code.
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

        echo "
        function showimg() {
            document.getElementById('png').style.width = '" . $divlen . "px';
            document.getElementById('png').style.height= '" . $divhi . "px';

            // *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
            const el = document.querySelectorAll('.ancestorName');
            el.forEach((elItem) => {
                //elItem.style.setProperty('border-radius', 'none', 'important');
                elItem.style.setProperty('box-shadow', 'none', 'important');
            });

            //html2canvas( [ document.getElementById('png') ], {
            //	onrendered: function( canvas ) {
            html2canvas(document.querySelector('#png')).then(canvas => {
                var img = canvas.toDataURL();
                document.getElementById('png').style.width = 'auto';
                document.getElementById('png').style.height= 'auto';

                var newWin = window.open();
                newWin.document.open();
                newWin.document.write('<!DOCTYPE html><head></head><body>" . __('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ') . "<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>" . __('If you have a plotter you can use its software to print the image on one large sheet.') . "<br><br><img src=\"' + img + '\"></body></html>');
                newWin.document.close();
                }
            //}
            );
        }
        ";
    }
    echo "</script>";
    ?>

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

    <?php
    // here place div at bottom so there is some space under last boxes
    $last = count($genarray) - 1;
    $putit = $genarray[$last]["posy"] + 130;
    ?>
    <div style="position:absolute;left:1px;top:<?= $putit; ?>px;">&nbsp;</div>