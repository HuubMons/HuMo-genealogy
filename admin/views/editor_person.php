<!-- Start of editor table -->
<form method="POST" action="<?= $phpself; ?>" style="display : inline;" enctype="multipart/form-data" name="form1" id="form1">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="person" value="<?= $pers_gedcomnumber; ?>">

    <!-- Date needed to check if birth or baptise date is changed -->
    <input type="hidden" name="pers_birth_date_previous" value="<?= $pers_birth_date; ?>">
    <input type="hidden" name="pers_bapt_date_previous" value="<?= $pers_bapt_date; ?>">

    <!-- <table class="humo" border="1" style="line-height: 180%;"> -->
    <!-- <table class="humo" border="1" style="line-height: 150%;"> -->
    <table class="humo" id="table_editor" border="1" style="line-height: 150%;">
        <?php
        // *** Show mother and father with a link ***
        if ($add_person == false) {
            // *** Update settings ***
            if (isset($_POST['admin_online_search'])) {
                if ($_POST['admin_online_search'] == 'y' or $_POST['admin_online_search'] == 'n') {
                    $result = $db_functions->update_settings('admin_online_search', $_POST["admin_online_search"]);
                    $humo_option["admin_online_search"] = $_POST['admin_online_search'];
                }
            }

        ?>
            <!-- Open Archives -->
            <tr>
                <th class="table_header_large" colspan="4"><?= __('Open Archives'); ?>
                    <!-- Ignore the Are You Sure script -->
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <select size="1" name="admin_online_search" onChange="this.form.submit();" class="ays-ignore">
                        <option value="y"><?= __('Online search enabled'); ?></option>
                        <option value="n" <?php if ($humo_option["admin_online_search"] != 'y') echo ' selected'; ?>><?= __('Online search disabled'); ?></option>
                    </select>
                    <?php

                    // *** Show archive list ***
                    // *** Change CSS links ***
                    /*
                    echo '
                        <style>
                        .ltrsddm div a {
                            display:inline;
                            padding: 0px;
                        }
                        </style>';
                    */
                    // *** Show navigation pop-up ***
                    echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                    echo '<a href="#" style="display:inline" ';
                    echo 'onmouseover="mopen(event,\'archive_menu\',0,0)"';
                    echo 'onmouseout="mclosetime()">';
                    echo '[' . __('Archives') . ']</a>';
                    //echo '<div class="sddm_fixed"
                    //	style="text-align:left; z-index:400; padding:4px;
                    //	direction:'.$rtlmarker.'"
                    //	id="browse_menu"
                    //	onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                    echo '<div class="sddm_fixed"
                        style="text-align:left; z-index:400; padding:4px; border: 1px solid rgb(153, 153, 153);
                        direction:' . $rtlmarker . ';
                        box-shadow: 2px 2px 2px #999;
                        border-radius: 3px;"
                        id="archive_menu"
                        onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

                    // *** Show box with list link to archives ***
                    if ($add_person == false) {
                        $OAfromyear = '';
                        if ($person->pers_birth_date) {
                            if (substr($person->pers_birth_date, -4)) $OAfromyear = substr($person->pers_birth_date, -4);
                        } elseif ($person->pers_bapt_date) {
                            if (substr($person->pers_bapt_date, -4)) $OAfromyear = substr($person->pers_bapt_date, -4);
                        }

                        // *** Show person ***
                        //echo '<b>'.__('Person').'</b><br>';
                        //echo '<span style="font-weight:bold; font-size:12px">'.show_person($person->pers_gedcomnumber).'</span><br>';
                        //echo show_person($person->pers_gedcomnumber).'<br>';
                        echo show_person($person->pers_gedcomnumber, false, false) . '<br><br>';

                        // *** GeneaNet ***
                        // https://nl.geneanet.org/fonds/individus/?size=10&amp;
                        //nom=Heijnen&prenom=Andreas&ampprenom_operateur=or&amp;place__0__=Wouw+Nederland&amp;go=1
                        $link = 'https://geneanet.org/fonds/individus/?size=10&amp;nom=' . urlencode($person->pers_lastname) . '&amp;prenom=' . urlencode($person->pers_firstname);
                        //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&birthdate_until='.$OAfromyear;
                        echo '<a href="' . $link . '&amp;go=1" target="_blank">Geneanet.org</a><br><br>';

                        // *** StamboomZoeker.nl ***
                        // UITLEG: https://www.stamboomzoeker.nl/page/16/zoekhulp
                        // sn: Familienaam
                        // fn: Voornaam
                        // bd: Twee geboortejaren met een streepje (-) er tussen
                        // bp: Geboorteplaats
                        // http://www.stamboomzoeker.nl/?a=search&fn=andreas&sn=heijnen&np=1&bd1=1655&bd2=1655&bp=wouw+nederland
                        $link = 'http://www.stamboomzoeker.nl/?a=search&amp;fn=' . urlencode($person->pers_firstname) . '&amp;sn=' . urlencode($person->pers_lastname);
                        if ($OAfromyear != '') $link .= '&amp;bd1=' . $OAfromyear . '&amp;bd2=' . $OAfromyear;
                        echo '<a href="' . $link . '" target="_blank">Familytreeseeker.com/ StamboomZoeker.nl</a><br><br>';

                        // *** GenealogieOnline ***
                        //https://www.genealogieonline.nl/zoeken/index.php?q=mons&vn=nikus&pn=harderwijk
                        $link = 'https://genealogieonline.nl/zoeken/index.php?q=' . urlencode($person->pers_lastname) . '&amp;vn=' . urlencode($person->pers_firstname);
                        //if ($OAfromyear!='') $link.='&amp;bd1='.$OAfromyear.'&amp;bd2='.$OAfromyear;
                        echo '<a href="' . $link . '" target="_blank">Genealogyonline.nl/ Genealogieonline.nl</a><br><br>';

                        // FamilySearch
                        //https://www.familysearch.org/search/record/results?q.givenName=Marie&q.surname=CORNEZ&count=20
                        $link = 'http://www.familysearch.org/search/record/results?count=20&q.givenName=' . urlencode($person->pers_firstname) . '&q.surname=' . urlencode($person->pers_lastname);
                        //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&amp;birthdate_until='.$OAfromyear;
                        echo '<a href="' . $link . '" target="_blank">FamilySearch</a><br><br>';

                        // *** GrafTombe ***
                        // http://www.graftombe.nl/names/search?forename=Andreas&surname=Heijnen&birthdate_from=1655
                        // &amp;birthdate_until=1655&amp;submit=Zoeken&amp;r=names-search
                        $link = 'http://www.graftombe.nl/names/search?forename=' . urlencode($person->pers_firstname) . '&amp;surname=' . urlencode($person->pers_lastname);
                        if ($OAfromyear != '') $link .= '&amp;birthdate_from=' . $OAfromyear . '&amp;birthdate_until=' . $OAfromyear;
                        echo '<a href="' . $link . '&amp;submit=Zoeken&amp;r=names-search" target="_blank">Graftombe.nl</a><br><br>';

                        // *** WieWasWie ***
                        // https://www.wiewaswie.nl/nl/zoeken/?q=Andreas+Adriaensen+Heijnen
                        $link = 'https://www.wiewaswie.nl/nl/zoeken/?q=' . urlencode($person->pers_firstname) .
                            '+' . urlencode($person->pers_lastname);
                        //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&amp;birthdate_until='.$OAfromyear;
                        echo '<a href="' . $link . '" target="_blank">WieWasWie</a><br><br>';

                        // *** StamboomOnderzoek ***
                        // https://www.stamboomonderzoek.com/default/search.php?
                        // myfirstname=Andreas&mylastname=Heijnen&lnqualify=startswith&mybool=AND&showdeath=1&tree=-x--all--x-
                    }

                    echo '</div>';
                    echo '</div>';
                    // *** End of archive list pop-up ***
                    ?>
                </th>
            </tr>
            <?php
            if ($humo_option["admin_online_search"] == 'y') {

                function openarchives_new($name, $year_or_period)
                {
                    if (function_exists('curl_exec')) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $OAapi = 'https://api.openarch.nl/1.0/records/search.json?name=';
                        $OAurl = $OAapi . urlencode($name . $year_or_period);   # via urlencode, zodat ook andere tekens dan spatie juist worden gecodeerd

                        curl_setopt($ch, CURLOPT_URL, $OAurl);
                        $result = curl_exec($ch);
                        curl_close($ch);

                        $jsonData = json_decode($result, TRUE);
                        echo '<tr class="humo_color"><td colspan="4">';
                        echo '<b>' . __('Search') . ': <a href="https://www.openarch.nl/search.php?name=' . urlencode($name . $year_or_period) .
                            '" target="_blank">https://www.openarch.nl/search.php?name=' . $name . $year_or_period . '</a></b><br>';
                        echo '</td></tr>';
                        if (isset($jsonData["response"]["docs"]) and count($jsonData["response"]["docs"]) > 0) {
                            foreach ($jsonData["response"]["docs"] as $OAresult) {   # het voordeel van JSON/json_dcode is dat je er eenvoudig mee kunt werken (geen Iterator nodig)
                                $OAday = '';
                                if (isset($OAresult["eventdate"]["day"])) $OAday = $OAresult["eventdate"]["day"];
                                //$OAmonthName=date('M', mktime(0, 0, 0, $OAresult["eventdate"]["archive"], 10));   # laat PHP zelf de maandnaam maken
                                $OAmonthName = '';
                                if (isset($OAresult["eventdate"]["month"]))
                                    $OAmonthName = date('M', mktime(0, 0, 0, $OAresult["eventdate"]["month"], 10));   # laat PHP zelf de maandnaam maken
                                $OAyear = '';
                                if (isset($OAresult["eventdate"]["year"])) $OAyear = $OAresult["eventdate"]["year"];
                                $OAeventdate = join(" ", array($OAday, $OAmonthName, $OAyear));

                                echo '<tr><td colspan="4">';
                                echo '<a href="' . $OAresult["url"] . '" target="openarch.nl">';   # geen aparte 'link' maar heeft de regel als link, door target steeds zelfde window
                                echo $OAresult["personname"] . ' (' . $OAresult["relationtype"] . ')';
                                echo ', ' . $OAresult["eventtype"] . ' ' . $OAeventdate . ' ' . $OAresult["eventplace"];
                                echo ', ' . $OAresult["archive"] . '/' . $OAresult["sourcetype"];
                                echo '</a></td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">' . __('No results found') . '</td></tr>';
                        }
                    }
                }

                # Bepaal te zoeken jaar of periode (waardoor er maar één zoekactie is benodigd)
                $OAfromyear = '';
                if ($person->pers_birth_date) {
                    if (substr($person->pers_birth_date, -4)) $OAfromyear = substr($person->pers_birth_date, -4);
                } elseif ($person->pers_bapt_date) {
                    if (substr($person->pers_bapt_date, -4)) $OAfromyear = substr($person->pers_bapt_date, -4);
                }

                $OAuntilyear = '';
                if ($person->pers_death_date) {
                    if (substr($person->pers_death_date, -4)) $OAuntilyear = substr($person->pers_death_date, -4);
                } elseif ($person->pers_buried_date) {
                    if (substr($person->pers_buried_date, -4)) $OAuntilyear = substr($person->pers_buried_date, -4);
                }

                $OAsearchname = $person->pers_firstname . ' ' . $person->pers_lastname;

                openarchives_new($OAsearchname, ' ' . $OAfromyear);

                if ($OAuntilyear) {
                    openarchives_new($OAsearchname, ' ' . $OAuntilyear);
                }

                if ($OAfromyear or $OAuntilyear) {
                    $OAyear_or_period = '';
                    if ($OAfromyear != '' && $OAuntilyear == '') {
                        $OAyear_or_period = ' ' . $OAfromyear . '-' . ($OAfromyear + 100);
                    }
                    if ($OAfromyear == '' && $OAuntilyear != '') {
                        $OAyear_or_period = ' ' . ($OAuntilyear - 100) . '-' . $OAuntilyear;
                    }
                    if ($OAfromyear != '' && $OAuntilyear != '') {
                        $OAyear_or_period = ' ' . $OAfromyear . '-' . $OAuntilyear;
                    }
                    if (isset($_POST['search_period'])) {
                        openarchives_new($OAsearchname, $OAyear_or_period);
                    } else {
                        echo '<tr class="humo_color"><td colspan="4"><input type="submit" name="search_period" value="' . __('Search using period') . '">';
                        echo ' <b>' . __('Search') . ': <a href="https://www.openarch.nl/search.php?name=' . urlencode($OAsearchname . $OAyear_or_period) .
                            '" target="_blank">https://www.openarch.nl/search.php?name=' . $OAsearchname . $OAyear_or_period . '</a></b><br>';
                        echo '</td></tr>';
                    }
                }
            }

            // *** Empty line in table ***
            //echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

            // *** April 2023, temporary text ***
            echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">';
            echo __('If links in this page are not good visible, refresh the page once using: [CTRL] - Refresh.');
            echo '&nbsp;</td></tr>';

            //echo '<tr><th class="table_header_large" colspan="4">'.ucfirst(__('parents')).'</tr>';

            echo '<tr><td><b>' . ucfirst(__('parents')) . '</b></td><td colspan="3">';
            $parent_text = '';

            if ($person->pers_famc) {
                // *** Search for parents ***
                $family_parentsDb = $db_functions->get_family($person->pers_famc, 'man-woman');

                //*** Father ***
                if ($family_parentsDb->fam_man) $parent_text .= show_person($family_parentsDb->fam_man);
                //	else $parent_text=__('N.N.');

                $parent_text .= ' ' . __('and') . ' ';

                //*** Mother ***
                if ($family_parentsDb->fam_woman) $parent_text .= show_person($family_parentsDb->fam_woman);
                //	else $parent_text.=__('N.N.');
            } else {
                $hideshow = 701;
            ?>
                <!-- Add existing or new parents -->
                <b><?= __('There are no parents.'); ?></b><a href="index.php?page=<?= $page; ?>&amp;add_parents=1">
                    <a href="#" onclick="hideShow('<?= $hideshow; ?>');"><?= __('Add parents'); ?></a>
                    <span class="humo row701" style="margin-left:0px; display:none;">
                        <table class="humo" style="margin-left:0px;">
                            <tr class="table_header">
                                <th></th>
                                <th><?= __('Father'); ?></th>
                                <th><?= __('Mother'); ?></th>
                            </tr>
                            <tr>
                                <td><b><?= __('firstname'); ?></b></td>
                                <td><input type="text" name="pers_firstname1" value="" size="35" placeholder="<?= ucfirst(__('firstname')); ?>"></td>
                                <td><input type="text" name="pers_firstname2" value="" size="35" placeholder="<?= ucfirst(__('firstname')); ?>"></td>
                            </tr>
                            <tr>
                                <td><?= __('prefix'); ?></td>
                                <!-- HELP POPUP for prefix -->
                                <td><input type="text" name="pers_prefix1" value="<?= $pers_prefix; ?>" size="10" placeholder="<?= ucfirst(__('prefix')); ?>">
                                    <div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                                        <a href="#" style="display:inline" onmouseover="mopen(event,'help_prefix',100,400)" onmouseout="mclosetime()">
                                            <img src="../images/help.png" height="16" width="16">
                                        </a>
                                        <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_prefix" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                            <b><?= __("For example: d\' or:  van_ (use _ for a space)"); ?></b><br>
                                        </div>
                                    </div>
                                </td>

                                <!-- HELP POPUP for prefix -->
                                <td><input type="text" name="pers_prefix2" value="" size="10" placeholder="<?= ucfirst(__('prefix')); ?>">
                                    <div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                                        <a href="#" style="display:inline" onmouseover="mopen(event,'help_prefix',100,400)" onmouseout="mclosetime()">
                                            <img src="../images/help.png" height="16" width="16">
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Lastname -->
                            <tr>
                                <td><b><?= __('lastname'); ?></b></td>
                                <td>
                                    <input type="text" name="pers_lastname1" value="<?= $pers_lastname; ?>" size="35" placeholder="<?= ucfirst(__('lastname')); ?>">
                                </td>
                                <td><input type="text" name="pers_lastname2" value="" size="35" placeholder="<?= ucfirst(__('lastname')); ?>"></td>
                            </tr>

                            <!--  Patronym -->
                            <tr>
                                <td><?= __('patronymic'); ?></td>
                                <td>
                                    <input type="text" name="pers_patronym1" value="<?= $pers_patronym; ?>" size="35" placeholder="<?= ucfirst(__('patronymic')); ?>">
                                </td>
                                <td><input type="text" name="pers_patronym2" value="" size="35" placeholder="<?= ucfirst(__('patronymic')); ?>"></td>
                            </tr>

                            <tr>
                                <td><br>
                                </td>
                                <td>
                                    <select size="1" name="event_gedcom_add1" style="width: 150px">
                                        <!-- Nickname, alias, adopted name, hebrew name, etc. -->
                                        <?php event_selection($data_listDb->event_gedcom); ?>
                                    </select><br>
                                    <input type="text" name="event_event_name1" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35">
                                </td>
                                <td>
                                    <select size="1" name="event_gedcom_add2" style="width: 150px">
                                        <!-- Nickname, alias, adopted name, hebrew name, etc. -->
                                        <?php event_selection($data_listDb->event_gedcom); ?>
                                    </select><br>
                                    <input type="text" name="event_event_name2" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35">
                                </td>
                            </tr>

                            <!-- Privacy filter -->
                            <tr>
                                <td><?= __('Privacy filter'); ?></td>
                                <td>
                                    <input type="radio" name="pers_alive1" value="alive"><?= __('alive'); ?>
                                    <input type="radio" name="pers_alive1" value="deceased"><?= __('deceased'); ?>
                                </td>
                                <td>
                                    <input type="radio" name="pers_alive2" value="alive"><?= __('alive'); ?>
                                    <input type="radio" name="pers_alive2" value="deceased"><?= __('deceased'); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><?= __('Sex'); ?></td>
                                <td>
                                    <input type="radio" name="pers_sexe1" value="M" checked><?= __('male'); ?>
                                    <input type="radio" name="pers_sexe1" value="F"><?= __('female'); ?>
                                    <input type="radio" name="pers_sexe1" value="">?
                                </td>
                                <td>
                                    <input type="radio" name="pers_sexe2" value="M"><?= __('male'); ?>
                                    <input type="radio" name="pers_sexe2" value="F" checked><?= __('female'); ?>
                                    <input type="radio" name="pers_sexe2" value=""> ?
                                </td>
                            </tr>

                            <!-- Profession -->
                            <tr>
                                <td><?= __('Profession'); ?></td>
                                <td>
                                    <input type="text" name="event_profession1" placeholder="<?= __('Profession'); ?>" value="" size="35">
                                </td>
                                <td>
                                    <input type="text" name="event_profession2" placeholder="<?= __('Profession'); ?>" value="" size="35">
                                </td>
                            </tr>

                            <tr class="humo_color">
                                <td colspan="3"><input type="submit" name="add_parents2" value="<?= __('Add parents'); ?>"></td>
                            </tr>
                        </table><br>

                        <?= __('Or select an existing family as parents:'); ?>
                        <input class="fonts" type="text" name="add_parents" placeholder="<?= __('GEDCOM number (ID)'); ?>" value="" size="20">
                        <a href="#" onClick='window.open("index.php?page=editor_relation_select","","<?= $field_popup; ?>")'><img src="../images/search.png" alt=<?= __('Search'); ?>></a>
                        <input type="submit" name="dummy2" value="<?= __('Select'); ?>">
                    </span> <!-- End of hide item -->
                <?php
            }

            echo $parent_text . '</td></tr>';

            // *** Show message if age < 0 or > 120 ***
            $error_color = '';
            $show_message = '&nbsp;';
            if (($person->pers_bapt_date or $person->pers_birth_date) and $person->pers_death_date) {
                include_once(__DIR__ . "/../../include/calculate_age_cls.php");
                $process_age = new calculate_year_cls;
                $age = $process_age->calculate_age($person->pers_bapt_date, $person->pers_birth_date, $person->pers_death_date, true);
                if ($age and ($age < 0 or $age > 120)) {
                    $error_color = 'background-color:#FFAA80;';
                    $show_message = '&nbsp;' . __('age') . ' ' . $age . ' ' . __('year');
                }
            }

                ?>
                <tr>
                    <!-- Show empty line or error message in table -->
                    <td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;<?= $error_color; ?>">
                        <?= $show_message; ?>
                    </td>
                </tr>
            <?php
        }
            ?>
            <tr class="table_header_large">
                <?php
                // *** Hide or show all hide-show items ***
                $hide_show_all = '<a href="#" onclick="hideShowAll();"><span id="hideshowlinkall">[+]</span> ' . __('All') . '</a> ';

                if ($add_person == false) {
                    echo '<td>' . $hide_show_all . ' <input type="submit" name="person_remove" value="' . __('Delete person') . '"></td>';
                    //echo '<td style="border-right: none"></td>';
                } else {
                    // *** New person: no delete example link ***
                    echo '<td>' . $hide_show_all . '</td>';
                    //echo '<td style="border-right: none"><br></td>';
                }

                //echo '<th style="border-left: none; text-align:left;">'.__('Person');
                echo '<th style="border-left: none; text-align:left; font-size: 1.5em;" colspan="2">';

                if ($add_person == false) {
                    //echo ': ['.$pers_gedcomnumber.'] '.show_person($person->pers_gedcomnumber,false,false);
                    echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);

                    // *** Add person to admin favourite list ***
                    $fav_qry = "SELECT * FROM humo_settings
                        WHERE setting_variable='admin_favourite'
                        AND setting_tree_id='" . safe_text_db($tree_id) . "'
                        AND setting_value='" . $pers_gedcomnumber . "'";
                    $fav_result = $dbh->query($fav_qry);
                    $rows = $fav_result->rowCount();
                    if ($rows > 0)
                        echo '<a href="' . $phpself . '?page=editor&amp;person=' . $pers_gedcomnumber . '&amp;pers_favorite=0"><img src="../images/favorite_blue.png" style="border: 0px"></a>';
                    else
                        echo '<a href="' . $phpself . '?page=editor&amp;person=' . $pers_gedcomnumber . '&amp;pers_favorite=1"><img src="../images/favorite.png" style="border: 0px"></a>';
                    echo '<br>';
                }
                echo '</th><td>';

                if ($add_person == false) {
                    echo '<input type="submit" name="person_change" value="' . __('Save') . '">';
                } else {
                    echo '<input type="submit" name="person_add" value="' . __('Add') . '">';
                }
                ?>
                </td>
            </tr>

            <tr>
                <!-- Name-->
                <?php
                $hideshow = '1';
                $display = ' display:none;';
                // *** New person: show all name fields ***
                if (!$pers_gedcomnumber) $display = '';
                ?>
                <td><a name="name"></a><b><?= __('Name'); ?></b></td>
                <td colspan="2">
                    <?php
                    // *** Use hideshow to show and hide the editor lines ***
                    if ($pers_gedcomnumber) {
                        echo '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');"><b>';
                        echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);
                        if ($pers_name_text) echo ' <img src="images/text.png" height="16">';
                        echo '</b></span><br>';
                    }
                    echo '<span class="humo row' . $hideshow . '" style="margin-left:0px;' . $display . '">';

                    // *** Firstname ***
                    //echo '<input type="text" name="pers_firstname" value="'.$pers_firstname.'"  size="35" placeholder="'.ucfirst(__('firstname')).'"><br>';
                    echo editor_label(__('firstname'), 'bold');
                    echo '<input type="text" name="pers_firstname" value="' . $pers_firstname . '"  size="35"><br>';

                    // *** Prefix ***
                    //echo '<input type="text" name="pers_prefix" value="'.$pers_prefix.'" size="10" placeholder="'.ucfirst(__('prefix')).'"> '.__("For example: d\' or:  van_ (use _ for a space)").'<br>';
                    echo editor_label(__('prefix') . '. ' . __("For example: d\' or:  van_ (use _ for a space)"));
                    echo '<input type="text" name="pers_prefix" value="' . $pers_prefix . '" size="10"><br>';

                    // *** Lastname ***
                    //echo '<input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="35" placeholder="'.ucfirst(__('lastname')).'"> ';
                    //echo __('patronymic').' <input type="text" name="pers_patronym" value="'.$pers_patronym.'" size="20" placeholder="'.ucfirst(__('patronymic')).'">';
                    echo editor_label(__('lastname'), 'bold');
                    echo '<input type="text" name="pers_lastname" value="' . $pers_lastname . '" size="35"><br>';

                    // *** Patronym *** 
                    echo editor_label(__('patronymic'));
                    echo '<input type="text" name="pers_patronym" value="' . $pers_patronym . '" size="20">';

                    if ($humo_option['admin_hebname'] == "y") {  // user requested hebrew name field to be displayed here, not under "events"
                        $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_HEBN' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_kind='name' AND event_connect_kind='person'";
                        $result = $dbh->query($sql);
                        if ($result->rowCount() > 0) {
                            $hebnameDb = $result->fetch(PDO::FETCH_OBJ);
                            $he_name =  $hebnameDb->event_event;
                        } else {
                            $he_name = "";
                        }

                        echo editor_label(__('Hebrew name') . '. ' . __('For example: Joseph ben Hirsch Zvi'));
                        echo '<input type="text" name="even_hebname" value="' . htmlspecialchars($he_name) . '" size="35"> ';
                    }

                    // *** Person text by name ***
                    echo editor_label(__('text'));
                    $text = $editor_cls->text_show($pers_name_text);
                    //$field_text_selected=$field_text; if ($text) $field_text_selected=$field_text_medium;
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                    echo '<textarea rows="1" name="pers_name_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                    //TEST
                    //echo '<br><br><input type="submit" name="person_change" value="' . __('Save') . '">';

                    //TEST Ajax script
                    /*
?>
<script>
    $(document).ready(function() {
        $("#submit_ajax").click(function() {
            var tree_id='<?= $tree_id;?>';
            var pers_gedcomnumber='<?= $pers_gedcomnumber;?>';
            var pers_firstname = $("#pers_firstname").val();
            var pers_lastname = $("#pers_lastname").val();
            //if (name == '' || email == '' || contact == '' || gender == '' || msg == '') {
            //	alert("Insertion Failed Some Fields are Blank....!!");
            //} else {
                // Returns successful data submission message when the entered information is stored in database.
                $.post("include/editor_ajax.php", {
                    tree_id1: tree_id,
                    pers_gedcomnumber1: pers_gedcomnumber,
                    pers_firstname1: pers_firstname,
                    pers_lastname1: pers_lastname,
                }, function(data) {
                    alert(data);
                    //$('#form_ajax')[0].reset(); // To reset form fields
                });
            //}

            // Show name in <div>
            document.getElementById("ajax_pers_firstname").innerHTML = pers_firstname;
            document.getElementById("ajax_pers_lastname").innerHTML = pers_lastname;

            // TEST for hideshow of item.
            hideShow(1);
        });
    });
</script>

<br><br>
<div id="ajax_pers_fullname"><?= $pers_firstname.' '.$pers_lastname; ?></div>
<div id="ajax_pers_firstname"><?= $pers_firstname; ?></div>
<div id="ajax_pers_lastname"><?= $pers_lastname; ?></div>

<label>Name:</label>
<input id="pers_firstname" value="<?= $pers_firstname; ?>" placeholder="Your Name" type="text">
<label>Name:</label>
<input id="pers_lastname" value="<?= $pers_lastname; ?>" placeholder="Your Name" type="text">
<input id="submit_ajax" type="button" value="Submit">
    <?php
// END TEST SCRIPT
*/


                    echo '</span>';
                    ?>
                </td>

                <td>
                    <?php
                    if (!isset($_GET['add_person'])) {
                        // *** Source by name ***
                        //source_link('individual',$pers_gedcomnumber,'pers_name_source');
                        echo source_link2('500', $pers_gedcomnumber, 'pers_name_source', 'name');
                    ?>



<?php /*
<!-- TEST Bootstrap modal -->
<!--
Modal works. In editor.php also enable line 69 <<<<<<<<<<<<<<<<<<<<<
TODO: add and remove sources inside a modal without reloading page
Try: https://www.w3schools.com/jsref/met_node_appendchild.asp

Don't close modal is search for existing source is done?
$('#mymodal').on('hidden.bs.modal', function() {
  return false;
});

Or:
const modalToggle = document.getElementById('toggleMyModal');
myModal.show(modalToggle)
-->
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <?= __('Source'); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php
        // TEST using function in editor_sources.php. Include file in editor.php.
        source_edit("person", "pers_name_source", $pers_gedcomnumber);
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Also test second modal -->
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pers_birth_sourceModal">
    <?= __('Source'); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="pers_birth_sourceModal" tabindex="-1" aria-labelledby="pers_birth_sourceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="pers_birth_sourceModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php
        // TEST using function in editor_sources.php. Include file in editor.php.
        source_edit("person", "pers_birth_source", $pers_gedcomnumber);
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
*/ ?>

                    <?php } ?>
                </td>
            </tr>

            <?php
            // *** Show source by name in iframe ***
            echo edit_sources('500', 'person', 'pers_name_source', $pers_gedcomnumber);

            if ($add_person == false) {
                // *** Event name (also show ADD line for prefix, suffix, title etc. ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'name');

                //TEST if editing is done in table, Ajax could be used.
                //echo '<tr><td></td><td colspan="3"><table class="humo">';
                //echo $event_cls->show_event('person', $pers_gedcomnumber, 'name');
                //echo '</table></td></tr>';

                // *** NPFX Name prefix like: Lt. Cmndr. ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'NPFX');

                // *** NSFX Name suffix like: jr. ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'NSFX');

                // *** Title of Nobility ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'nobility');

                // *** Title ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'title');

                // *** Lordship ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'lordship');
            }

            // *** Alive ***

            // *** Disable radio boxes if person is deceased ***
            $disabled = '';
            if ($pers_death_date or $pers_death_place or $pers_buried_date or $pers_buried_place) {
                $disabled = ' disabled';
            }

            if ($pers_alive == 'deceased') {
                $selected_alive = '';
                $selected_deceased = ' checked';
            } else {
                $selected_alive = ' checked';
                $selected_deceased = '';
            }
            ?>
            <tr class="humo_color">
                <td><?= __('Privacy filter'); ?></td>
                <td colspan="2">
                    <input type="radio" name="pers_alive" value="alive" <?= $selected_alive . $disabled; ?>> <?= __('alive'); ?>
                    <?php

                    echo ' <input type="radio" name="pers_alive" value="deceased"' . $selected_deceased . $disabled . '> ' . __('deceased');

                    // *** Estimated/ calculated (birth) date, can be used for privacy filter ***
                    if (!$pers_cal_date) $pers_cal_date = 'dd mmm yyyy';
                    echo '<span style="color:#6D7B8D;">';
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?page=cal_date">' . __('Calculated birth date') . ':</a> ' . language_date($pers_cal_date);
                    echo '</span>';
                    ?>
                </td>
                <td></td>
            </tr>
            <?php

            // *** Sexe ***
            $colour = '';
            // *** If sexe = unknown then show a red line (new person = other colour). ***
            if ($pers_sexe == '') $colour = ' bgcolor="#FFAA80"';
            if ($add_person == true and $pers_sexe == '') $colour = ' bgcolor="#FFAA80"';

            $selected_m = '';
            if ($pers_sexe == 'M') $selected_m = ' checked';
            $selected_f = '';
            if ($pers_sexe == 'F') $selected_f = ' checked';
            $selected_u = '';
            if ($pers_sexe == '') $selected_u = ' checked';
            ?>
            <tr>
                <td><a name="sex"></a><?= __('Sex'); ?></td>
                <td<?= $colour; ?> colspan="2">
                    <input type="radio" name="pers_sexe" value="M" <?= $selected_m; ?>> <?= __('male'); ?>
                    <input type="radio" name="pers_sexe" value="F" <?= $selected_f; ?>> <?= __('female'); ?>
                    <input type="radio" name="pers_sexe" value="" <?= $selected_u; ?>> ?
                    </td>
                    <td>
                        <?php
                        if (!isset($_GET['add_person'])) {
                            //source_link('individual',$pers_gedcomnumber,'pers_sexe_source');
                            echo source_link2('501', $pers_gedcomnumber, 'pers_sexe_source', 'sex');
                        }
                        ?>
                    </td>
            </tr>
            <?php
            // *** Show source by sexe in iframe ***
            echo edit_sources('501', 'person', 'pers_sexe_source', $pers_gedcomnumber);

            //TEST (also after other items in this script)
            // *** Empty line in table ***
            //$divider='<tr style="height:8px;"><td colspan="4" class="table_empty_line"></td></tr>';
            //echo $divider;


            // *** Born ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '2';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

            ?>
            <tr class="humo_color">
                <td><a name="born"></a>
                    <b><?= ucfirst(__('born')); ?></b>
                </td>
                <?php
                /*
                <td style="border-right:0px; vertical-align: top;">
                    <?= ucfirst(__('born')); ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>">
                <br><?= __('date'); ?>
                <?php

                // HELP POPUP
                echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                echo '<a href="#" style="display:inline" ';
                echo 'onmouseover="mopen(event,\'help_date\')"';
                echo 'onmouseout="mclosetime()">';
                echo '<img src="../images/help.png" height="16" width="16">';
                echo '</a>';
                echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_date" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                echo __('Examples of date entries:') . '<br>';
                echo '<b>' . __('13 october 1813, 13 oct 1813, 13-10-1813, 13/10/1813, 13.10.1813, 13,10,1813, between 1986 and 1987, 13 oct 1100 BC.') . '</b><br>';
                echo '</div>';
                echo '</div><br>';

                echo __('birth time') . '<br>';
                echo ucfirst(__('text'));
                echo '</span>';
                echo '</td>';
*/

                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($pers_birth_date, $pers_birth_place);
                if ($pers_birth_time) {
                    $hideshow_text .= ' ' . __('at') . ' ' . $pers_birth_time . ' ' . __('hour');
                }
                //TEST
                //if (!$hideshow_text) $hideshow_text=ucfirst(__('born'));
                echo hideshow_editor($hideshow, $hideshow_text, $pers_birth_text);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($pers_birth_date, 'pers_birth_date', '', '', $pers_birth_date_hebnight, 'pers_birth_date_hebnight') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="pers_birth_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_birth_place) . '" size="' . $field_place . '">';
                //echo '<input type="text" name="pers_birth_place" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($pers_birth_place).'" size="'.$field_place.'">';

                // *** Auto complete doesn't work properly yet... ***
                //echo __('place').' <input list="place_auto_complete" name="pers_birth_place" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($pers_birth_place).'" size="'.$field_place.'">';

                echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_birth_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                echo editor_label2(__('birth time'));
                echo '<input type="text" placeholder="' . __('birth time') . '" name="pers_birth_time" value="' . $pers_birth_time . '" size="' . $field_date . '">';
                //echo '<input type="text" name="pers_birth_time" value="'.$pers_birth_time.'" size="'.$field_date.'">';
                // *** Stillborn child ***
                $check = '';
                if (isset($pers_stillborn) and $pers_stillborn == 'y') {
                    $check = ' checked';
                }
                echo '<input type="checkbox" name="pers_stillborn" ' . $check . '> ' . __('stillborn child') . '<br>';

                // *** Check if there are multiple lines in text ***
                $field_text_selected = $field_text;
                if ($pers_birth_text and preg_match('/\R/', $pers_birth_text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_birth_text" ' . $field_text_selected . '>' .
                    $editor_cls->text_show($pers_birth_text) . '</textarea>';

                // *** End of hideshow_editor span ***
                echo '</span>';
                echo '</td>';

                // *** Source by birth ***
                echo '<td>';
                if (!isset($_GET['add_person'])) {
                    //source_link('individual',$pers_gedcomnumber,'pers_birth_source');
                    echo source_link2('502', $pers_gedcomnumber, 'pers_birth_source', 'born');
                }
                ?>
                </td>
            </tr>
            <?php
            // *** Show source by birth in iframe ***
            echo edit_sources('502', 'person', 'pers_birth_source', $pers_gedcomnumber);

            // *** Birth declaration ***
            if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'birth_declaration');

            // **** BRIT MILA ***
            if ($humo_option['admin_brit'] == "y" and $pers_sexe != "F") {

                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '20';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_BRTM' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
                $result = $dbh->query($sql);
                if ($result->rowCount() > 0) {
                    $britDb = $result->fetch(PDO::FETCH_OBJ);
                    $britdate = $britDb->event_date;
                    $britplace = $britDb->event_place;
                    $brittext = $britDb->event_text;
                } else {
                    $britdate = "";
                    $britplace = "";
                    $brittext = "";
                }
                $britDb = $result->fetch(PDO::FETCH_OBJ);

                echo '<tr>';
                //echo '<td><a href="#" onclick="hideShow(20);"><span id="hideshowlink20">[+]</span></a> ';
                echo '<td>' . ucfirst(__('Brit Mila')) . '</td>';

                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($britdate, $britplace);
                echo hideshow_editor($hideshow, $hideshow_text, $brittext);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($britdate, 'even_brit_date') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="even_brit_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($britplace) . '" size="' . $field_place . '"><br>';

                // *** Check if there are multiple lines in text ***
                $text = $editor_cls->text_show($brittext);
                $field_text_selected = $field_text;
                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="even_brit_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                echo '<br>' . __('To display this, the option "Show events" has to be checked in "Users -> Groups"');
                // echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=even_brit_place","","'.$field_popup.'")\'><img src="../images/search.png" alt="'.__('Search').'"></a>';
                // *** End of hideshow_editor span ***
                echo '</span>';
                echo '</td>';

                // *** Source by Brit Mila ***
                echo '<td>';
                // No source yet.
                echo '</td></tr>';
            }

            //*** BAR/BAT MITSVA ***
            if ($humo_option['admin_barm'] == "y") {
                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '21';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                $sql = "SELECT * FROM humo_events WHERE (event_gedcom = 'BARM' OR event_gedcom = 'BASM') AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
                $result = $dbh->query($sql);
                if ($result->rowCount() > 0) {
                    $barmDb = $result->fetch(PDO::FETCH_OBJ);
                    $bardate =  $barmDb->event_date;
                    $barplace =  $barmDb->event_place;
                    $bartext =  $barmDb->event_text;
                } else {
                    $bardate = "";
                    $barplace = "";
                    $bartext = "";
                }

                echo '<tr>';
                //echo '<td><a href="#" onclick="hideShow(21);"><span id="hideshowlink21">[+]</span></a> ';
                echo '<td>';
                if ($pers_sexe == "F") {
                    echo __('Bat Mitzvah');
                } else {
                    echo __('Bar Mitzvah');
                }
                echo '</td>';

                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($bardate, $barplace);
                echo hideshow_editor($hideshow, $hideshow_text, $bartext);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($bardate, 'even_barm_date') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="even_barm_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($barplace) . '" size="' . $field_place . '"><br>';
                //echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=even_barm_place","","'.$field_popup.'")\><img src="../images/search.png" alt="'.__('Search').'"></a>';

                // *** Check if there are multiple lines in text ***
                $text = $editor_cls->text_show($bartext);
                $field_text_selected = $field_text;
                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="even_barm_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                echo '<br>' . __('To display this, the option "Show events" has to be checked in "Users -> Groups"');
                echo '</span>';
                echo '</td>';

                // *** Source by Bar Mitsva ***
                echo '<td>';
                //if (!isset($_GET['add_person'])){
                //	// no source yet
                //}
                echo '</td></tr>';
            }


            // *** Empty line in table ***
            //echo $divider;


            // *** Baptise ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '3';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>
            <tr>
                <td><a name="baptised"></a><b><?= ucfirst(__('baptised')); ?></b></td>
                <?php
                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($pers_bapt_date, $pers_bapt_place);

                if ($pers_religion) $hideshow_text .= ' (' . __('religion') . ': ' . $pers_religion . ')';

                echo hideshow_editor($hideshow, $hideshow_text, $pers_bapt_text);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($pers_bapt_date, 'pers_bapt_date') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="pers_bapt_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_bapt_place) . '" size="' . $field_place . '">';
                echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_bapt_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                $text = htmlspecialchars($pers_religion);
                echo editor_label2(__('religion'));
                echo '<input type="text" name="pers_religion" placeholder="' . __('religion') . '" value="' . $text . '" size="20"><br>';

                $text = $editor_cls->text_show($pers_bapt_text);
                // *** Check if there are multiple lines in text ***
                $field_text_selected = $field_text;
                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_bapt_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                echo '</span>';
                echo '</td>';

                // *** Source by baptise ***
                echo '<td>';
                if (!isset($_GET['add_person'])) {
                    //source_link('individual',$pers_gedcomnumber,'pers_bapt_source');
                    echo source_link2('503', $pers_gedcomnumber, 'pers_bapt_source', 'baptised');
                }
                ?>
                </td>
            </tr>
            <?php
            // *** Show source by baptise in iframe ***
            echo edit_sources('503', 'person', 'pers_bapt_source', $pers_gedcomnumber);

            // *** Baptism Witness ***
            if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'baptism_witness');


            // *** Empty line in table ***
            //echo $divider;

            // *** Died ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '4';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

            ?>
            <tr class="humo_color">
                <td><a name="died"></a>
                    <b><?= ucfirst(__('died')); ?></b>
                </td>
                <?php

                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($pers_death_date, $pers_death_place);

                if ($pers_death_time)
                    $hideshow_text .= ' ' . __('at') . ' ' . $pers_death_time . ' ' . __('hour');

                if ($pers_death_cause) {
                    if ($hideshow_text) $hideshow_text .= ', ';
                    $pers_death_cause2 = '';
                    if ($pers_death_cause == 'murdered') {
                        $pers_death_cause2 = __('cause of death') . ': ' . __('murdered');
                    }
                    if ($pers_death_cause == 'drowned') {
                        $pers_death_cause2 = __('cause of death') . ': ' . __('drowned');
                    }
                    if ($pers_death_cause == 'perished') {
                        $pers_death_cause2 = __('cause of death') . ': ' . __('perished');
                    }
                    if ($pers_death_cause == 'killed in action') {
                        $pers_death_cause2 = __('killed in action');
                    }
                    if ($pers_death_cause == 'being missed') {
                        $pers_death_cause2 = __('being missed');
                    }
                    if ($pers_death_cause == 'committed suicide') {
                        $pers_death_cause2 = __('cause of death') . ': ' . __('committed suicide');
                    }
                    if ($pers_death_cause == 'executed') {
                        $pers_death_cause2 = __('cause of death') . ': ' . __('executed');
                    }
                    if ($pers_death_cause == 'died young') {
                        $pers_death_cause2 = __('died young');
                    }
                    if ($pers_death_cause == 'died unmarried') {
                        $pers_death_cause2 = __('died unmarried');
                    }
                    if ($pers_death_cause == 'registration') {
                        $pers_death_cause2 = __('registration');
                    } //2 TYPE registration?
                    if ($pers_death_cause == 'declared death') {
                        $pers_death_cause2 = __('declared death');
                    }
                    if ($pers_death_cause2) {
                        $hideshow_text .= $pers_death_cause2;
                    } else {
                        $hideshow_text .= __('cause of death') . ' ' . $pers_death_cause;
                    }
                }

                echo hideshow_editor($hideshow, $hideshow_text, $pers_death_text);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($pers_death_date, 'pers_death_date', '', '', $pers_death_date_hebnight, 'pers_death_date_hebnight') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="pers_death_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_death_place) . '" size="' . $field_place . '">';
                echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_death_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                // *** Age by death ***
                echo editor_label2(__('Age'));
                echo '<input type="text" name="pers_death_age" placeholder="' . __('Age') . '" value="' . $pers_death_age . '" size="3">';
                // *** HELP POPUP for age by death ***
                echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                echo '<a href="#" style="display:inline" ';
                echo 'onmouseover="mopen(event,\'help_menu2\',100,400)"';
                echo 'onmouseout="mclosetime()">';
                echo '<img src="../images/help.png" height="16" width="16">';
                echo '</a>';
                echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_menu2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                echo '<b>' . __('If death year and age are used, then birth year is calculated automatically (when empty).') . '</b><br>';
                echo '</div>';
                echo '</div><br>';

                echo editor_label2(__('death time'));
                echo '<input type="text" name="pers_death_time" placeholder="' . __('death time') . '" value="' . $pers_death_time . '" size="' . $field_date . '"><br>';

                echo editor_label2(__('cause'));
                //echo ' ' . __('cause') . ' ';
                $cause = false;
                echo '<select size="1" name="pers_death_cause">';
                echo '<option value=""></option>';

                $selected = '';
                if ($pers_death_cause == 'murdered') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="murdered"' . $selected . '>' . __('murdered') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'drowned') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="drowned"' . $selected . '>' . __('drowned') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'perished') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="perished"' . $selected . '>' . __('perished') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'killed in action') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="killed in action"' . $selected . '>' . __('killed in action') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'being missed') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="being missed"' . $selected . '>' . __('being missed') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'committed suicide') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="committed suicide"' . $selected . '>' . __('committed suicide') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'executed') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="executed"' . $selected . '>' . __('executed') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'died young') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="died young"' . $selected . '>' . __('died young') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'died unmarried') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="died unmarried"' . $selected . '>' . __('died unmarried') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'registration') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="registration"' . $selected . '>' . __('registration') . '</option>';

                $selected = '';
                if ($pers_death_cause == 'declared death') {
                    $cause = true;
                    $selected = ' selected';
                }
                echo '<option value="declared death"' . $selected . '>' . __('declared death') . '</option>';

                echo '</select>';

                echo ' <b>' . __('or') . ':</b> ';
                $pers_death_cause2 = '';
                if ($pers_death_cause and $cause == false) $pers_death_cause2 = $pers_death_cause;
                echo '<input type="text" name="pers_death_cause2" placeholder="' . __('cause') . '" value="' . $pers_death_cause2 . '" size="' . $field_date . '"><br>';

                $text = $editor_cls->text_show($pers_death_text);
                // *** Check if there are multiple lines in text ***
                $field_text_selected = $field_text;
                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_death_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                echo '</span>';

                // *** Source by death ***
                echo '</td><td>';
                if (!isset($_GET['add_person'])) {
                    //source_link('individual',$pers_gedcomnumber,'pers_death_source');
                    echo source_link2('504', $pers_gedcomnumber, 'pers_death_source', 'died');
                }
                echo '</td>';
                ?>
            </tr>
            <?php
            // *** Show source by death in iframe ***
            echo edit_sources('504', 'person', 'pers_death_source', $pers_gedcomnumber);

            // *** Death Declaration ***
            if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'death_declaration');

            // *** Empty line in table ***
            //echo $divider;

            // *** Buried ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '5';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

            ?>
            <tr>
                <?php
                echo '<td><a name="buried"></a>';
                //echo '<a href="#" onclick="hideShow(5);"><span id="hideshowlink5">[+]</span></a> ';
                echo '<b>' . __('Buried') . '</b></td>';

                echo '<td colspan="2">';
                $hideshow_text = hideshow_date_place($pers_buried_date, $pers_buried_place);
                echo hideshow_editor($hideshow, $hideshow_text, $pers_buried_text);

                echo editor_label2(__('Date'));
                echo $editor_cls->date_show($pers_buried_date, 'pers_buried_date', '', '', $pers_buried_date_hebnight, 'pers_buried_date_hebnight') . '<br>';

                echo editor_label2(__('place'));
                echo '<input type="text" name="pers_buried_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_buried_place) . '" size="' . $field_place . '">';
                echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_buried_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                echo editor_label2(__('method of burial'));
                echo '<select size="1" name="pers_cremation">';
                echo '<option value="">' . __('buried') . '</option>';

                $selected = '';
                if ($pers_cremation == '1') {
                    $selected = ' selected';
                }
                echo '<option value="1"' . $selected . '>' . __('cremation') . '</option>';

                $selected = '';
                if ($pers_cremation == 'R') {
                    $selected = ' selected';
                }
                echo '<option value="R"' . $selected . '>' . __('resomated') . '</option>';

                $selected = '';
                if ($pers_cremation == 'S') {
                    $selected = ' selected';
                }
                echo '<option value="S"' . $selected . '>' . __('sailor\'s grave') . '</option>';

                $selected = '';
                if ($pers_cremation == 'D') {
                    $selected = ' selected';
                }
                echo '<option value="D"' . $selected . '>' . __('donated to science') . '</option>';
                echo '</select><br>';

                $text = $editor_cls->text_show($pers_buried_text);
                // *** Check if there are multiple lines in text ***
                $field_text_selected = $field_text;
                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                echo editor_label2(__('text'));
                echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_buried_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                echo '</span>';
                echo '</td>';

                // *** Source by burial ***
                echo '<td>';
                if (!isset($_GET['add_person'])) {
                    //source_link('individual',$pers_gedcomnumber,'pers_buried_source');
                    echo source_link2('505', $pers_gedcomnumber, 'pers_buried_source', 'buried');
                }
                echo '</td>';

                ?>
            </tr>
            <?php
            // *** Show source by burial in iframe ***
            echo edit_sources('505', 'person', 'pers_buried_source', $pers_gedcomnumber);

            // *** Burial Witness ***
            if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'burial_witness');

            // *** Empty line in table ***
            //echo $divider;

            // *** General text by person ***
            ?>
            <tr class="humo_color">
                <td><a name="text_person"></a><?= __('Text for person'); ?></td>
                <td colspan="2"><textarea rows="1" placeholder="<?= __('Text for person'); ?>" name="person_text" <?= $field_text_large; ?>><?= $editor_cls->text_show($person_text); ?></textarea>
                </td>
                <td>
                    <?php
                    // *** Source by text ***
                    if (!isset($_GET['add_person'])) {
                        //source_link('individual',$pers_gedcomnumber,'pers_text_source');
                        echo source_link2('506', $pers_gedcomnumber, 'pers_text_source', 'text_person');
                    }
                    ?>
                </td>
            </tr>
            <?php
            // *** Show source by person tekst in iframe ***
            echo edit_sources('506', 'person', 'pers_text_source', $pers_gedcomnumber);

            if (!isset($_GET['add_person'])) {
                // *** Person sources in new person editor screen ***
                echo '<tr><td><a name="source_person"></a>' . __('Source for person') . '</td><td colspan="2"></td>';
                echo '<td>';
                //source_link('individual',$pers_gedcomnumber,'person_source');
                echo source_link2('507', $pers_gedcomnumber, 'person_source', 'source_person');
                echo '</td></tr>';
                // *** Show source by person in iframe ***
                echo edit_sources('507', 'person', 'person_source', $pers_gedcomnumber);
            }

            // *** Own code ***
            ?>
            <tr class="humo_color">
                <td><?= ucfirst(__('own code')); ?></td>
                <td colspan="2"><input type="text" name="pers_own_code" placeholder="<?= __('own code'); ?>" value="<?= htmlspecialchars($pers_own_code); ?>" style="width: 500px">
                    <!-- HELP POPUP for own code -->
                    &nbsp;&nbsp;<div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                        <a href="#" style="display:inline" onmouseover="mopen(event,'help_menu3',100,400)" onmouseout="mclosetime()">
                            <img src="../images/help.png" height="16" width="16">
                        </a>
                        <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_menu3" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <b><?= __('Use own code for your own remarks.<br>
It\'s possible to use own code for special privacy options, see Admin > Users > Groups.<br>
It\'s also possible to add your own icons by a person! Add the icon in the images folder e.g. \'person.gif\', and add \'person\' in the own code field.'); ?></b><br>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            <?php

            // *** Profession(s) ***
            echo $event_cls->show_event('person', $pers_gedcomnumber, 'profession');

            // *** Religion ***
            echo $event_cls->show_event('person', $pers_gedcomnumber, 'religion');

            if (!isset($_GET['add_person'])) {
                // *** Show and edit places by person ***
                edit_addresses('person', 'person_address', $pers_gedcomnumber);
            } // *** End of check for new person ***

            if (!isset($_GET['add_person'])) {
                // *** Person event editor ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'person');

                // *** Picture ***
                echo $event_cls->show_event('person', $pers_gedcomnumber, 'picture');

                // *** Quality ***
                // Disabled quality by person. Quality officially belongs to a source...
                /*
                echo '<tr><td>'.__('Quality of data').'</td>';
                echo '<td style="border-right:0px;"></td>';
                    echo '<td style="border-left:0px;"><select size="1" name="pers_quality" style="width: 400px">';
                    echo '<option value="">'.ucfirst(__('quality: default')).'</option>';
                    $selected=''; if ($pers_quality=='0'){ $selected=' selected'; }
                    echo '<option value="0"'.$selected.'>'.ucfirst(__('quality: unreliable evidence or estimated data')).'</option>';
                    $selected=''; if ($pers_quality=='1'){ $selected=' selected'; }
                    echo '<option value="1"'.$selected.'>'.ucfirst(__('quality: questionable reliability of evidence')).'</option>';
                    $selected=''; if ($pers_quality=='2'){ $selected=' selected'; }
                    echo '<option value="2"'.$selected.'>'.ucfirst(__('quality: data from secondary evidence')).'</option>';
                    $selected=''; if ($pers_quality=='3'){ $selected=' selected'; }
                    echo '<option value="3"'.$selected.'>'.ucfirst(__('quality: data from direct source')).'</option>';
                    echo '</select></td>';
                echo '<td></td>';
                echo '</tr>';
                */

                // *** Show unprocessed GEDCOM tags ***
                $tag_qry = "SELECT * FROM humo_unprocessed_tags
                    WHERE tag_tree_id='" . $tree_id . "'
                    AND tag_pers_id='" . $person->pers_id . "'";
                $tag_result = $dbh->query($tag_qry);
                //$num_rows = $tag_result->rowCount();
                $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
                if (isset($tagDb->tag_tag)) {
                    $tags_array = explode('<br>', $tagDb->tag_tag);
                    $num_rows = count($tags_array);
            ?>
                    <tr class="humo_tags_pers humo_color">
                        <td>
                            <a href="#humo_tags_pers" onclick="hideShow(61);"><span id="hideshowlink61">[+]</span></a>
                            <?= __('GEDCOM tags'); ?>
                        </td>
                        <td colspan="2">
                            <?php
                            if ($tagDb->tag_tag) {
                                printf(__('There are %d unprocessed GEDCOM tags.'), $num_rows);
                            } else {
                                printf(__('There are %d unprocessed GEDCOM tags.'), 0);
                            }
                            ?>
                        </td>
                        <td></td>
                    </tr>
                    <tr style="display:none;" class="row61">
                        <td></td>
                        <td colspan="2"><?= $tagDb->tag_tag; ?></td>
                        <td></td>
                    </tr>
            <?php
                }

                // *** Show editor notes ***
                show_editor_notes('person');

                // *** Show user added notes ***
                $note_qry = "SELECT * FROM humo_user_notes
                    WHERE note_tree_id='" . $tree_id . "'
                    AND note_kind='user' AND note_connect_kind='person' AND note_connect_id='" . $pers_gedcomnumber . "'";
                $note_result = $dbh->query($note_qry);
                $num_rows = $note_result->rowCount();

                echo '<tr class="table_header_large"><td>';
                if ($num_rows)
                    echo '<a href="#humo_user_notes" onclick="hideShow(62);"><span id="hideshowlink62">[+]</span></a> ';
                echo __('User notes') . '</td><td colspan="2">';
                if ($num_rows)
                    printf(__('There are %d user added notes.'), $num_rows);
                else
                    printf(__('There are %d user added notes.'), 0);
                echo '</td><td></td></tr>';
                while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
                    $user_qry = "SELECT * FROM humo_users
                                    WHERE user_id='" . $noteDb->note_new_user_id . "'";
                    $user_result = $dbh->query($user_qry);
                    $userDb = $user_result->fetch(PDO::FETCH_OBJ);

                    echo '<tr class="row62" style="display:none;"><td></td>';
                    echo '<td colspan="2">';

                    echo __('Added by') . ' <b>' . $userDb->user_name . '</b> (' . language_date($noteDb->note_new_date) . ' ' . $noteDb->note_new_time . ')<br>';

                    echo '<b>' . $noteDb->note_names . '</b><br>';

                    echo '<textarea readonly rows="1" placeholder="' . __('Text') . '" ' . $field_text_large . '>' . $editor_cls->text_show($noteDb->note_note) . '</textarea>';

                    echo '</td>';
                    echo '<td></td></tr>';
                }

                // *** Person added by user ***
                if ($person->pers_new_user) {
                    echo '<tr class="table_header_large"><td>' . __('Added by') . '</td>';
                    //echo '<td colspan="2">'.$person->pers_new_user.' ('.$person->pers_new_date.' '.$person->pers_new_time.')</td><td></td></tr>';
                    echo '<td colspan="2">' . $person->pers_new_user . ' (' . language_date($person->pers_new_date) . ' ' . $person->pers_new_time . ')</td><td></td></tr>';
                }
                // *** Person changed by user ***
                if ($person->pers_changed_user) {
                    echo '<tr class="table_header_large"><td>' . __('Changed by') . '</td>';
                    echo '<td colspan="2">' . $person->pers_changed_user . ' (' . language_date($person->pers_changed_date) . ' ' . $person->pers_changed_time . ')</td><td></td></tr>';
                }
            }

            // *** Extra "Save" line ***
            echo '<tr class="table_header_large">';
            echo '<td></td><td colspan="2"></td>';
            echo '<td style="border-left: none; text-align:left; font-size: 1.5em;">';
            if ($add_person == false) {
                echo '<input type="submit" name="person_change" value="' . __('Save') . '">';
            } else {
                echo '<input type="submit" name="person_add" value="' . __('Add') . '">';
            }
            echo '</td>';
            echo '</tr>';

            ?>
    </table><br>
    <!-- End of person form -->
</form>