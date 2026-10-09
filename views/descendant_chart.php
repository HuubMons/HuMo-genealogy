<?php

/**
 * Descendant chart. Used to be part of family script, seperated in july 2023.
 */

$screen_mode = 'STAR';

$directionMarkers = new \Genealogy\Include\DirectionMarkers($language["dir"], $screen_mode);
$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$personPopup = new \Genealogy\Include\PersonPopup();
$datePlace = new \Genealogy\Include\DatePlace();
$htmlEscaper = new \Genealogy\Include\HtmlEscaper();
$cssNumber = fn($value): string => $htmlEscaper->escHtml((string) (float) $value);
// *** Show allowed HTML elements in the popup ***
$sanitizePopupHtml = static function ($html) use ($htmlEscaper): string {
    $previousErrorSetting = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML('<?xml encoding="UTF-8"><div>' . (string) $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previousErrorSetting);

    $container = $document->getElementsByTagName('div')->item(0);
    if (!$container) {
        return $htmlEscaper->escHtmlDecoded(strip_tags((string) $html));
    }

    $renderNode = static function ($node) use (&$renderNode, $htmlEscaper): string {
        if ($node instanceof DOMText) {
            return $htmlEscaper->escHtml($node->nodeValue);
        }
        if (!$node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style'], true)) {
            return '';
        }
        if ($tag === 'br') {
            return '<br>';
        }

        $content = '';
        foreach ($node->childNodes as $child) {
            $content .= $renderNode($child);
        }

        if (in_array($tag, ['b', 'strong', 'i', 'em'], true)) {
            return '<' . $tag . '>' . $content . '</' . $tag . '>';
        }
        if ($tag === 'span') {
            $allowedClasses = ['divorse', 'event', 'religion'];
            $class = $node->getAttribute('class');
            if (in_array($class, $allowedClasses, true)) {
                return '<span class="' . $htmlEscaper->escHtml($class) . '">' . $content . '</span>';
            }
            return $content;
        }
        if ($tag === 'a') {
            $href = $node->getAttribute('href');
            $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
            if ($href !== '' && ($scheme === '' || in_array($scheme, ['http', 'https'], true))) {
                return '<a href="' . $htmlEscaper->escHtml($href) . '" rel="nofollow noopener noreferrer">' . $content . '</a>';
            }
        }

        return $content;
    };

    $sanitizedHtml = '';
    foreach ($container->childNodes as $child) {
        $sanitizedHtml .= $renderNode($child);
    }
    return $sanitizedHtml;
};

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

    $processLinks = new \Genealogy\Include\ProcessLinks();
    $vars['id'] = $data['family_id'];
    $path = $processLinks->get_link($uri_path, 'descendant_chart', $tree_id, true, $vars);

    $dna_params = "";
    if ($data["dna"] != "none") {
        $dna_params = '&bn=' . $data["base_person_name"] . '&bs=' . $data["base_person_sexe"] . '&bf=' . $data["base_person_famc"] . '&bg=' . $data["base_person_gednr"];
    }
    ?>

    <div id="descendant-chart-print-config"
        data-width="<?= $htmlEscaper->escHtml((string) $divlen); ?>"
        data-height="<?= $htmlEscaper->escHtml((string) $divhi); ?>"
        data-instructions="<?= $htmlEscaper->escHtml(__('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ')); ?>"
        data-plotter-instructions="<?= $htmlEscaper->escHtml(__('If you have a plotter you can use its software to print the image on one large sheet.')); ?>"
        data-posterazor-label="PosteRazor"
        data-posterazor-url="http://posterazor.sourceforge.net/index.php?page=download&amp;lang=english">
    </div>
    <script src="assets/js/descendant_chart_print.js"></script>
    <div id="png">
        <?php include __DIR__ . '/partial/descendant_header.php'; ?>

        <div class="p-2 me-sm-2 genealogy_search" id="menubox">
            <!-- <div class="p-2 me-sm-2 genealogy_search d-print-none"> -->

            <div class="row">
                <div class="col-md-auto">
                    <h4>
                        <?php
                        if ($data["dna"] == "none") {
                            $rootPersonDb = $db_functions->get_person($genarray[0]["gednr"]);
                            $rootPrivacy = $personPrivacy->get_privacy($rootPersonDb);
                            $rootName = $personName->get_person_name($rootPersonDb, $rootPrivacy);
                            echo $htmlEscaper->escHtml(__('Descendant chart') . __(' of ')) . $htmlEscaper->escHtmlDecoded($rootName["standard_name"]) . $rootName["colour_mark"];
                        } elseif ($data["dna"] == "ydna" || $data["dna"] == "ydnamark") {
                            echo $htmlEscaper->escHtml(__('Same Y-DNA as ')) . $htmlEscaper->escHtmlDecoded($data["base_person_name"]);
                        } elseif ($data["dna"] == "mtdna" || $data["dna"] == "mtdnamark") {
                            echo $htmlEscaper->escHtml(__('Same mtDNA as ')) . $htmlEscaper->escHtmlDecoded($data["base_person_name"]);
                        }
                        ?>
                    </h4>
                </div>
            </div>

            <div class="row">

                <div class="col-md-auto">
                    <form method="POST" name="desc_form" action="<?= $htmlEscaper->escHtml($path . 'chosensize=' . $data["size"]); ?>" style="display : inline;">
                        <input type="hidden" name="tree_id" value="<?= $htmlEscaper->escHtml((string) $tree_id); ?>">
                        <input type="hidden" name="main_person" value="<?= $htmlEscaper->escHtml($data["main_person"]); ?>">
                        <input type="hidden" name="chosengen" value="<?= $htmlEscaper->escHtml((string) $data["chosengen"]); ?>">
                        <?php if ($data["dna"] != "none") { ?>
                            <input type="hidden" name="dnachart" value="<?= $htmlEscaper->escHtml($data["dna"]); ?>">
                            <input type="hidden" name="bf" value="<?= $htmlEscaper->escHtml($data["base_person_famc"]); ?>">
                            <input type="hidden" name="bs" value="<?= $htmlEscaper->escHtml($data["base_person_sexe"]); ?>">
                            <input type="hidden" name="bn" value="<?= $htmlEscaper->escHtml($data["base_person_name"]); ?>">
                            <input type="hidden" name="bg" value="<?= $htmlEscaper->escHtml($data["base_person_gednr"]); ?>">
                        <?php } ?>
                        <input id="dirval" type="hidden" name="direction" value=""> <!-- will be filled in next lines -->
                        <?php if ($data["direction"] == "1") { ?>
                            <input type="button" name="dummy" value="<?= $htmlEscaper->escHtml(__('vertical')); ?>" onClick='document.desc_form.direction.value="0";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                        <?php } else { ?>
                            <input type="button" name="dummy" value="<?= $htmlEscaper->escHtml(__('horizontal')); ?>" onClick='document.desc_form.direction.value="1";document.desc_form.submit();' class="btn btn-sm btn-secondary">
                        <?php } ?>
                    </form>
                </div>

                <div class="col-md-auto">
                    <input type="button" id="imgbutton" value="<?= $htmlEscaper->escHtml(__('Print')); ?>" onClick="showimg();" class="btn btn-sm btn-secondary">
                </div>

                <?php if ($data["dna"] != "none") { ?>
                    <div class="col-md-auto">
                        <?= __('DNA:'); ?>
                    </div>

                    <div class="col-md-auto">
                        <select name="dnachart" onChange="window.location=this.value" class="form-select form-select-sm">
                            <?php if ($data["base_person_sexe"] == "M") { ?>
                                <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . '&dnachart=ydna&chosensize=' . $data["size"] . '&chosengen=' . $data["chosengen"]); ?>" <?= $data["dna"] == "ydna" ? 'selected' : ''; ?>>
                                    <?= $htmlEscaper->escHtml(__('Y-DNA Carriers only')); ?>
                                </option>

                                <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . '&dnachart=ydnamark&chosensize=' . $data["size"] . '&chosengen=' . $data["chosengen"]); ?>" <?= $data["dna"] == "ydnamark" ? 'selected' : ''; ?>>
                                    <?= $htmlEscaper->escHtml(__('Y-DNA Mark carriers')); ?>
                                </option>
                            <?php
                            }

                            // if base person is male, only show mtDNA if there are ancestors since he can't have mtDNA descendants...
                            if ($data["base_person_sexe"] == "F" or ($data["base_person_sexe"] == "M" and isset($data["base_person_famc"]) and $data["base_person_famc"] != "")) {
                            ?>
                                <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . '&dnachart=mtdna&chosensize=' . $data["size"] . '&chosengen=' . $data["chosengen"]); ?>" <?= $data["dna"] == "mtdna" ? 'selected' : ''; ?>>
                                    <?= $htmlEscaper->escHtml(__('mtDNA Carriers only')); ?>
                                </option>

                                <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . '&dnachart=mtdnamark&chosensize=' . $data["size"] . '&chosengen=' . $data["chosengen"]); ?>" <?= $data["dna"] == "mtdnamark" ? 'selected' : ''; ?>>
                                    <?= $htmlEscaper->escHtml(__('mtDNA Mark carriers')); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="col-md-auto">
                    <?= $htmlEscaper->escHtml(__('Nr. generations')); ?>:
                </div>

                <div class="col-md-auto">
                    <select name="chosengen" onChange="window.location=this.value" class="form-select form-select-sm">
                        <?php for ($i = 2; $i <= 15; $i++) { ?>
                            <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . ($data["dna"] != 'none' ? '&dnachart=' . $data["dna"] : '') . '&chosensize=' . $data["size"] . '&chosengen=' . $i); ?>" <?= $i == $data["chosengen"] ? 'selected' : ''; ?>>
                                <?= $i; ?>
                            </option>
                        <?php } ?>

                        <!-- Option "All" for all generations -->
                        <option value="<?= $htmlEscaper->escHtml($path . 'main_person=' . $data["main_person"] . '&direction=' . $data["direction"] . ($data["dna"] != 'none' ? '&dnachart=' . $data["dna"] : '') . '&chosensize=' . $data["size"] . '&chosengen=All'); ?>" <?= $data["chosengen"] == "All" ? 'selected' : ''; ?>>
                            <?= $htmlEscaper->escHtml(__('All')); ?>
                        </option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <label for="amount"><?= $htmlEscaper->escHtml(__('Zoom level:')); ?></label>
                    <input type="text" id="amount" disabled="disabled" style="width:25px;border:0; color:#0000CC; font-weight:normal;font-size:115%;">
                </div>

                <div class="col-md-auto">
                    <div id="slider"
                        data-slider-value="<?= $htmlEscaper->escHtml((string) (($data['size'] / 5) - 1)); ?>"
                        data-url-base="<?= $htmlEscaper->escHtml($path); ?>"
                        data-main-person="<?= $htmlEscaper->escHtml($data['main_person']); ?>"
                        data-chosen-generations="<?= $htmlEscaper->escHtml($data['chosengen']); ?>"
                        data-direction="<?= $htmlEscaper->escHtml($data['direction']); ?>"
                        data-dna="<?= $htmlEscaper->escHtml($data['dna']); ?>"
                        data-base-person-name="<?= $htmlEscaper->escHtml($data['base_person_name']); ?>"
                        data-base-person-sexe="<?= $htmlEscaper->escHtml($data['base_person_sexe']); ?>"
                        data-base-person-famc="<?= $htmlEscaper->escHtml($data['base_person_famc']); ?>"
                        data-base-person-gednr="<?= $htmlEscaper->escHtml($data['base_person_gednr']); ?>"
                        style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>
                    <script src="assets/js/descendant_chart_slider.js"></script>
                </div>

            </div>
        </div>

        <?php
        if ($data["direction"] == 0) {
            $lastPerson = count($genarray) - 1;
            $the_height = $genarray[$lastPerson]["posy"] + 130;
        } else {
            $highestPosition = 0;
            foreach ($genarray as $person) {
                if ($person["posy"] > $highestPosition) {
                    $highestPosition = $person["posy"];
                }
            }
            $the_height = $highestPosition + 130;
        }
        ?>

        <!-- TODO check div. It's too wide (is set in doublescroll script). Doublescroll isn't in use anymore. -->
        <style type="text/css">
            #doublescroll {
                position: relative;
                width: auto;
                height: <?= $htmlEscaper->escHtml((string) (float) $the_height); ?>px;
                z-index: 10;
            }
        </style>
    <?php
    $direction = in_array($directionMarkers->rtlmarker, ['ltr', 'rtl'], true) ? $directionMarkers->rtlmarker : 'ltr';
    echo '<div id="doublescroll" class="wrapper" style="direction:' . $direction . ';">';

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
    if (isset($genarray[$w]["sex"]) && $genarray[$w]["sex"] == "v") {
        $sexe_colour = ' box_woman';
        //$backgr_col = "#FBDEC0";     //"#f8bdf1";
    } else {
        $sexe_colour = ' box_man';
        //$backgr_col =  "#C0F9FC";      //"#bbf0ff";
    }

    // *** Start person class and calculate privacy ***
    $man = null;
    $man_privacy = false;
    $manName = null;
    if (isset($genarray[$w]["gednr"]) && $genarray[$w]["gednr"]) {
        $man = $db_functions->get_person($genarray[$w]["gednr"]);
        $man_privacy = $personPrivacy->get_privacy($man);
        $manName = $personName->get_person_name($man, $man_privacy);
    }

    //echo '<div style="position:absolute; background-color:'.$bkcolor.';height:'.$data["vsize"].'px; width:'.$data["hsize"].'px; border:1px brown solid; left:'.$xvalue.'px; top:'.$yvalue.'px">';

    $bkgr = "";
    if (isset($data["dna"]) && ($data["dna"] == "ydnamark" || $data["dna"] == "mtdnamark" || $data["dna"] == "ydna" || $data["dna"] == "mtdna") && $genarray[$w]["dna"] == 1) {
        $bkgr = "border:3px solid #999999;background-color:" . $backgr_col . ";";
        if (isset($genarray[$w]["gednr"]) && $genarray[$w]["gednr"] == $data["base_person_gednr"]) {
            // base person
            $bkgr = "border:3px solid red;background-color:" . $backgr_col . ";";
        }
    } else {
        $bkgr = "border:1px solid #8C8C8C;background-color:" . $backgr_col . ";";
    }
    if (isset($genarray[$w]["gen"]) && $genarray[$w]["gen"] == 0 && $hourglass === true) {
        $bkgr = "background-color:" . $backgr_col . ";";
    }
    ?>

        <div class="ancestorName<?= $sexe_colour; ?>" style="<?= $htmlEscaper->escHtml($bkgr); ?>position:absolute; height:<?= $htmlEscaper->escHtml((string) (float) $data["vsize"]); ?>px; width:<?= $htmlEscaper->escHtml((string) (float) $data["hsize"]); ?>px; left:<?= $htmlEscaper->escHtml((string) (float) $xvalue); ?>px; top:<?= $htmlEscaper->escHtml((string) (float) $yvalue); ?>px;">
            <?php
            $replacement_text = '';
            if ($data["size"] >= 25) {
                if (strpos($browser_user_agent, "msie 7.0") === false) {
                    if ($data["size"] == 50) {

                        // *** Show picture ***
                        if (!$man_privacy && $user['group_pictures'] == 'j') {
                            //  *** Path can be changed per family tree ***
                            $tree_pict_path = $selectedFamilyTree->tree_pict_path;
                            if (substr($tree_pict_path, 0, 1) === '|') {
                                $tree_pict_path = 'media/';
                            }
                            $picture_qry = $db_functions->get_events_connect('person', $man->pers_gedcomnumber, 'picture');
                            // *** Only show 1st picture ***
                            if (isset($picture_qry[0])) {
                                $pictureDb = $picture_qry[0];
                                $showMedia = new \Genealogy\Include\ShowMedia();
                                $replacement_text .= $showMedia->print_thumbnail($tree_pict_path, $pictureDb->event_event, 60, 65, 'float:left; margin:5px;');
                            }
                        }

                        //$replacement_text.= '<strong>'.$genarray[$w]["nam"].'</strong>';
                        //$replacement_text.= '<span class="anc_box_name">'.$genarray[$w]["nam"].'</span>';
                        $replacement_text .= '<span class="anc_box_name">' . $htmlEscaper->escHtmlDecoded($manName["standard_name"]) . $manName["colour_mark"] . '</span>';
                        if ($man_privacy) {
                            $replacement_text .= '<br>' . __(' PRIVACY FILTER') . '<br>';  //Tekst privacy weergeven
                        } else {
                            //if ($man->pers_birth_date OR $man->pers_birth_place){
                            if ($man->pers_birth_date) {
                                //$replacement_text.= '<br>'.__('*').$directionMarkers->dirmark1.' '.$datePlace->date_place($man->pers_birth_date,$man->pers_birth_place);
                                $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('*')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($man->pers_birth_date, ''));
                            }
                            //elseif ($man->pers_bapt_date OR $man->pers_bapt_place){
                            elseif ($man->pers_bapt_date) {
                                //$replacement_text.= '<br>'.__('~').$directionMarkers->dirmark1.' '.$datePlace->date_place($man->pers_bapt_date,$man->pers_bapt_place);
                                $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('~')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($man->pers_bapt_date, ''));
                            }

                            //if ($man->pers_death_date OR $man->pers_death_place){
                            if ($man->pers_death_date) {
                                //$replacement_text.= '<br>'.__('&#134;').$directionMarkers->dirmark1.' '.$datePlace->date_place($man->pers_death_date,$man->pers_death_place);
                                $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('&#134;')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($man->pers_death_date, ''));
                            }
                            //elseif ($man->pers_buried_date OR $man->pers_buried_place){
                            elseif ($man->pers_buried_date) {
                                //$replacement_text.= '<br>'.__('[]').$directionMarkers->dirmark1.' '.$datePlace->date_place($man->pers_buried_date,$man->pers_buried_place);
                                $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('[]')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($man->pers_buried_date, ''));
                            }

                            if ($genarray[$w]["non"] == 0) {
                                // otherwise for an unmarried child it would give the parents' marriage!
                                $ownfam = $db_functions->get_family($genarray[$w]["fams"]);
                                //if ($ownfam->fam_marr_date OR $ownfam->fam_marr_place){
                                // *** Don't check for date. Otherwise living together persons are missing ***
                                //if ($ownfam->fam_marr_date){
                                //$replacement_text.= '<br>'.__('X').$directionMarkers->dirmark1.' '.$datePlace->date_place($ownfam->fam_marr_date,$ownfam->fam_marr_place);

                                if ($ownfam->fam_marr_date || $ownfam->fam_marr_place) {
                                    $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('X'));
                                } else {
                                    // *** Relation ***
                                    $replacement_text .= '<br>' . $htmlEscaper->escHtml(__('&amp;'));
                                }

                                if ($ownfam->fam_marr_date) {
                                    $replacement_text .= $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($ownfam->fam_marr_date, '')) . ' ';
                                }

                                // *** Jan. 2022: Show spouse ***
                                if (isset($genarray[$w]["sps"]) && $genarray[$w]["sps"] != '') {
                                    if ($ownfam->fam_marr_date || $ownfam->fam_marr_place) {
                                        //$replacement_text.= "&nbsp;".__(' to: ')."<br>";
                                        $replacement_text .= $htmlEscaper->escHtml(__(' to: ')) . '<br>';
                                    } else {
                                        // *** Don't show 'to: ' for relations.
                                        $replacement_text .= ' ';
                                    }
                                    $replacement_text .= '<i>' . $htmlEscaper->escHtmlDecoded($genarray[$w]["sps"]) . '</i>';
                                }
                                //}
                            }
                        }
                    } elseif ($data["size"] == 45) {
                        $replacement_text .= $htmlEscaper->escHtmlDecoded($manName["standard_name"]) . $manName["colour_mark"];
                    } elseif ($data["size"] == 40) {
                        $replacement_text .= '<span class="wordwrap" style="font-size:75%">' . $htmlEscaper->escHtmlDecoded($genarray[$w]["short"]) . '</span>';
                    } elseif (isset($genarray[$w]["init"]) && $data["size"] >= 25 && $data["size"] < 40) {
                        $replacement_text .= $htmlEscaper->escHtmlDecoded($genarray[$w]["init"]);
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

            if (isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] == 1) {
                $extra_popup_text .= $sanitizePopupHtml($genarray[$w]["huw"]) . "<br>";
            }

            if (isset($genarray[$w]["non"]) && $genarray[$w]["non"] != 1) {
                if (isset($genarray[$w]["spgednr"]) && $genarray[$w]["spgednr"]) {
                    $woman = $db_functions->get_person($genarray[$w]["spgednr"]);
                    $woman_privacy = $personPrivacy->get_privacy($woman);
                }

                // *** Marriage data ***
                $extra_popup_text .= '<br>' . $sanitizePopupHtml($genarray[$w]["htx"]) . "<br>";
                if (isset($woman)) {
                    $name = $personName->get_person_name($woman, $woman_privacy);
                    $safeWomanName = $htmlEscaper->escHtmlDecoded($name["standard_name"]);
                    if (isset($genarray[$w]["spfams"]) && isset($genarray[$w]["spgednr"]) && isset($genarray[$w]["sps"])) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $personLink = new \Genealogy\Include\PersonLink();
                        $url = $personLink->get_person_link($woman);

                        $extra_popup_text .= '<a href="' . $htmlEscaper->escHtml($url) . '">' . '<strong>' . $safeWomanName . '</strong></a>';
                    } else {
                        $extra_popup_text .= $safeWomanName;
                    }

                    if ($woman_privacy) {
                        $extra_popup_text .= __(' PRIVACY FILTER') . '<br>';  //Tekst privacy weergeven
                    } else {
                        if ($woman->pers_birth_date || $woman->pers_birth_place) {
                            $extra_popup_text .= ' ' . $htmlEscaper->escHtml(__('born')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($woman->pers_birth_date, $woman->pers_birth_place)) . '<br>';
                        }

                        if ($woman->pers_death_date || $woman->pers_death_place) {
                            $extra_popup_text .= ' ' . $htmlEscaper->escHtml(__('died')) . $directionMarkers->dirmark1 . ' ' . $htmlEscaper->escHtmlDecoded($datePlace->date_place($woman->pers_death_date, $woman->pers_death_place)) . '<br>';
                        }
                    }
                } else {
                    $extra_popup_text .= $htmlEscaper->escHtml(__('N.N.'));
                }
            }

            if (isset($man)) {
                echo $personPopup->person_popup_menu($man, $man_privacy, true, $replacement_text, $extra_popup_text);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                //$personLink = new PersonLink();
                //$url = $personLink->get_person_link($man);
                //echo '<a href="'.$url.'"><span clas="nam" style="font-size:10px; color: #000000; text-decoration: none;">'.$replacement_text.'</span></a>';
            }
            ?>
        </div>

    <?php
    // *** Vertical ***
    if ($data["direction"] == 0) {
        // if vertical draw dotted line from first marriage to following marriages
        if (isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] == 1) {
            $startx = $genarray[$w - 1]["posx"] + $data["hsize"] + 2;
            $starty = $genarray[$w - 1]["posy"] + ($data["vsize"] / 2);
            $width = ($genarray[$w]["posx"]) - ($genarray[$w - 1]["posx"] + $data["hsize"]) - 2;
            echo '<div style="position:absolute;border:1px blue dashed;height:2px;width:' . $cssNumber($width) . 'px;left:' . $cssNumber($startx) . 'px;top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw line to children
        if ($genarray[$w]["nrc"] != 0) {
            $startx = $genarray[$w]["posx"] + ($data["hsize"] / 2);
            $starty = $genarray[$w]["posy"] + $data["vsize"] + 2;
            echo '<div class="chart_line" style="position:absolute; height:' . $cssNumber(($data["vdist"] / 2) - 2) . 'px; width:1px; left:' . $cssNumber($startx) . 'px; top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw line to parent
        if (isset($genarray[$w]["gen"]) && $genarray[$w]["gen"] != 0 && isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] != 1) {
            $startx = $genarray[$w]["posx"] + ($data["hsize"] / 2);
            $starty = $genarray[$w]["posy"] - ($data["vdist"] / 2);
            echo '<div class="chart_line" style="position:absolute; height:' . $cssNumber($data["vdist"] / 2) . 'px;width:1px;left:' . $cssNumber($startx) . 'px;top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw horizontal line from 1st child in fam to last child in fam
        if (isset($genarray[$w]["gen"]) && $genarray[$w]["gen"] != 0) {
            $parent = $genarray[$w]["par"];
            if (isset($genarray[$w]["chd"]) && $genarray[$w]["chd"] == $genarray[$parent]["nrc"]) {
                // last child in fam
                $z = $w;
                while (isset($genarray[$z]["2nd"]) && $genarray[$z]["2nd"] == 1) {
                    //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
                    $z--;
                }
                $startx = $genarray[$parent]["fst"] + ($data["hsize"] / 2);
                $starty = $genarray[$z]["posy"] - ($data["vdist"] / 2);
                $width = $genarray[$z]["posx"] - $genarray[$parent]["fst"];
                echo '<div class="chart_line" style="position:absolute; height:1px; width:' . $cssNumber($width) . 'px; left:' . $cssNumber($startx) . 'px; top:' . $cssNumber($starty) . 'px"></div>';
            }
        }
    }
    else {
        // *** Horizontal ***
        // if horizontal draw dotted line from first marriage to following marriages
        if (isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] == 1) {
            $starty = $genarray[$w - 1]["posy"] + $data["vsize"] + 2;
            $startx = $genarray[$w - 1]["posx"] + ($data["hsize"] / 2);
            $height = ($genarray[$w]["posy"]) - ($genarray[$w - 1]["posy"] + $data["vsize"]) - 2;
            echo '<div style="position:absolute;border:1px blue dashed;height:' . $cssNumber($height) . 'px; width:3px; left:' . $cssNumber($startx) . 'px;top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw line to children
        if ($genarray[$w]["nrc"] != 0) {
            $starty = $genarray[$w]["posy"] + ($data["vsize"] / 2);
            $startx = $genarray[$w]["posx"] + $data["hsize"] + 3;
            echo '<div class="chart_line" style="position:absolute; height:1px; width:' . $cssNumber(($data["hdist"] / 2) - 2) . 'px; left:' . $cssNumber($startx) . 'px; top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw line to parent
        if (isset($genarray[$w]["gen"]) && $genarray[$w]["gen"] != 0 && isset($genarray[$w]["2nd"]) && $genarray[$w]["2nd"] != 1) {
            $starty = $genarray[$w]["posy"] + ($data["vsize"] / 2);
            $startx = $genarray[$w]["posx"] - ($data["hdist"] / 2);
            echo '<div class="chart_line" style="position:absolute; width:' . $cssNumber($data["hdist"] / 2) . 'px; height:1px; left:' . $cssNumber($startx) . 'px; top:' . $cssNumber($starty) . 'px"></div>';
        }

        // draw vertical line from 1st child in fam to last child in fam
        if (isset($genarray[$w]["gen"]) && $genarray[$w]["gen"] != 0) {
            $parent = $genarray[$w]["par"];
            if (isset($genarray[$w]["chd"]) && $genarray[$w]["chd"] == $genarray[$parent]["nrc"]) {
                // last child in fam
                $z = $w;
                while (isset($genarray[$z]["2nd"]) && $genarray[$z]["2nd"] == 1) {
                    //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
                    $z--;
                }
                $starty = $genarray[$parent]["fst"] + ($data["vsize"] / 2);
                $startx = $genarray[$z]["posx"] - ($data["hdist"] / 2);
                $height = $genarray[$z]["posy"] - $genarray[$parent]["fst"];
                echo '<div class="chart_line" style="position:absolute; width:1px; height:' . $cssNumber($height) . 'px; left:' . $cssNumber($startx) . 'px; top:' . $cssNumber($starty) . 'px"></div>';
            }
        }
    }
}
    ?>

    </div> <!-- png -->
