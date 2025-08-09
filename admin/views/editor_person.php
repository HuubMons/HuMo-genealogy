<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$languageDate = new \Genealogy\Include\LanguageDate;
$editorEventSelection = new \Genealogy\Include\EditorEventSelection;
?>

<!-- Start of editor table -->
<form method="POST" action="index.php" style="display : inline;" enctype="multipart/form-data" name="form1" id="form1">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="person" value="<?= $pers_gedcomnumber; ?>">

    <!-- Date needed to check if birth or baptise date is changed -->
    <input type="hidden" name="pers_birth_date_previous" value="<?= $pers_birth_date; ?>">
    <input type="hidden" name="pers_bapt_date_previous" value="<?= $pers_bapt_date; ?>">

    <?php
    if ($editor['add_person'] == false) {
        // *** Update settings ***
        if (isset($_POST['admin_online_search']) && ($_POST['admin_online_search'] == 'y' || $_POST['admin_online_search'] == 'n')) {
            $db_functions->update_settings('admin_online_search', $_POST["admin_online_search"]);
            $humo_option["admin_online_search"] = $_POST['admin_online_search'];
        }
    ?>

        <!-- Archives -->
        <div class="p-2 m-2 genealogy_search">
            <div class="row">

                <div class="col-md-2">
                    <label for="admin_online_search" class="col-form-label">
                        <b><?= __('Open Archives'); ?></b>
                    </label>
                </div>

                <div class="col-auto">
                    <!-- Ignore the Are You Sure script -->
                    <select size="1" id="admin_online_search" name="admin_online_search" onChange="this.form.submit();" class="ays-ignore form-select form-select-sm">
                        <option value="y"><?= __('Online search enabled'); ?></option>
                        <option value="n" <?php if ($humo_option["admin_online_search"] != 'y')  echo ' selected'; ?>><?= __('Online search disabled'); ?></option>
                    </select>
                </div>

                <!-- Show archive list -->
                <?php
                // TODO move to model script.
                if ($editor['add_person'] == false) {
                    $OAfromyear = '';
                    if ($person->pers_birth_date) {
                        if (substr($person->pers_birth_date, -4)) {
                            $OAfromyear = substr($person->pers_birth_date, -4);
                        }
                    } elseif ($person->pers_bapt_date) {
                        if (substr($person->pers_bapt_date, -4)) {
                            $OAfromyear = substr($person->pers_bapt_date, -4);
                        }
                    }

                    // *** GeneaNet ***
                    // https://nl.geneanet.org/fonds/individus/?size=10&amp;
                    //nom=Heijnen&prenom=Andreas&ampprenom_operateur=or&amp;place__0__=Wouw+Nederland&amp;go=1
                    $link_geneanet = 'https://geneanet.org/fonds/individus/?size=10&amp;nom=' . urlencode($person->pers_lastname) . '&amp;prenom=' . urlencode($person->pers_firstname);

                    // *** StamboomZoeker.nl ***
                    // UITLEG: https://www.stamboomzoeker.nl/page/16/zoekhulp
                    // sn: Familienaam
                    // fn: Voornaam
                    // bd: Twee geboortejaren met een streepje (-) er tussen
                    // bp: Geboorteplaats
                    // http://www.stamboomzoeker.nl/?a=search&fn=andreas&sn=heijnen&np=1&bd1=1655&bd2=1655&bp=wouw+nederland
                    $link_familyseeker = 'http://www.stamboomzoeker.nl/?a=search&amp;fn=' . urlencode($person->pers_firstname) . '&amp;sn=' . urlencode($person->pers_lastname);
                    if ($OAfromyear !== '') {
                        $link_familyseeker .= '&amp;bd1=' . $OAfromyear . '&amp;bd2=' . $OAfromyear;
                    }

                    // *** GenealogieOnline ***
                    //https://www.genealogieonline.nl/zoeken/index.php?q=mons&vn=nikus&pn=harderwijk
                    $link_genealogieonline = 'https://genealogieonline.nl/zoeken/index.php?q=' . urlencode($person->pers_lastname) . '&amp;vn=' . urlencode($person->pers_firstname);

                    // FamilySearch
                    //https://www.familysearch.org/search/record/results?q.givenName=Marie&q.surname=CORNEZ&count=20
                    $link_familysearch = 'http://www.familysearch.org/search/record/results?count=20&q.givenName=' . urlencode($person->pers_firstname) . '&q.surname=' . urlencode($person->pers_lastname);

                    // *** GrafTombe ***
                    // http://www.graftombe.nl/names/search?forename=Andreas&surname=Heijnen&birthdate_from=1655
                    // &amp;birthdate_until=1655&amp;submit=Zoeken&amp;r=names-search
                    $link_graftombe = 'http://www.graftombe.nl/names/search?forename=' . urlencode($person->pers_firstname) . '&amp;surname=' . urlencode($person->pers_lastname);
                    if ($OAfromyear !== '') {
                        $link_graftombe .= '&amp;birthdate_from=' . $OAfromyear . '&amp;birthdate_until=' . $OAfromyear;
                    }

                    // *** WieWasWie ***
                    // https://www.wiewaswie.nl/nl/zoeken/?q=Andreas+Adriaensen+Heijnen
                    $link_wiewaswie = 'https://www.wiewaswie.nl/nl/zoeken/?q=' . urlencode($person->pers_firstname) . '+' . urlencode($person->pers_lastname);

                    // *** StamboomOnderzoek ***
                    // https://www.stamboomonderzoek.com/default/search.php?
                    // myfirstname=Andreas&mylastname=Heijnen&lnqualify=startswith&mybool=AND&showdeath=1&tree=-x--all--x-
                }
                ?>
                <div class="col-auto">
                    <div class="dropdown dropend d-inline">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Archives'); ?></button>
                        <ul class="dropdown-menu p-2" style="width:450px;">
                            <?php if ($editor['add_person'] == false) { ?>
                                <li class="mb-2"><b><?= show_person($person->pers_gedcomnumber, false, false); ?></b></li>
                                <li class="mb-2"><a href="<?= $link_geneanet; ?>&amp;go=1" target="_blank">Geneanet.org</a></li>
                                <li class="mb-2"><a href="<?= $link_familyseeker; ?>" target="_blank">Familytreeseeker.com/ StamboomZoeker.nl</a></li>
                                <li class="mb-2"><a href="<?= $link_genealogieonline; ?>" target="_blank">Genealogyonline.nl/ Genealogieonline.nl</a></li>
                                <li class="mb-2"><a href="<?= $link_familysearch; ?>" target="_blank">FamilySearch</a></li>
                                <li class="mb-2"><a href="<?= $link_graftombe; ?>&amp;submit=Zoeken&amp;r=names-search" target="_blank">Graftombe.nl</a></li>
                                <li class="mb-2"><a href="<?= $link_wiewaswie; ?>" target="_blank">WieWasWie</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

            </div>

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
            ?>
                        <b><?= __('Search'); ?>: <a href="https://www.openarch.nl/search.php?name=<?= urlencode($name . $year_or_period); ?>" target="_blank">https://www.openarch.nl/search.php?name=<?= $name . $year_or_period; ?></a></b><br>
                        <?php
                        if (isset($jsonData["response"]["docs"]) && count($jsonData["response"]["docs"]) > 0) {
                            foreach ($jsonData["response"]["docs"] as $OAresult) {   # het voordeel van JSON/json_dcode is dat je er eenvoudig mee kunt werken (geen Iterator nodig)
                                $OAday = '';
                                if (isset($OAresult["eventdate"]["day"])) {
                                    $OAday = $OAresult["eventdate"]["day"];
                                }
                                //$OAmonthName=date('M', mktime(0, 0, 0, $OAresult["eventdate"]["archive"], 10));   # laat PHP zelf de maandnaam maken
                                $OAmonthName = '';
                                if (isset($OAresult["eventdate"]["month"])) {
                                    $OAmonthName = date('M', mktime(0, 0, 0, $OAresult["eventdate"]["month"], 10));
                                }   # laat PHP zelf de maandnaam maken
                                $OAyear = '';
                                if (isset($OAresult["eventdate"]["year"])) $OAyear = $OAresult["eventdate"]["year"];
                                $OAeventdate = join(" ", array($OAday, $OAmonthName, $OAyear));
                        ?>
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-auto">
                                        <!-- geen aparte 'link' maar heeft de regel als link, door target steeds zelfde window -->
                                        <a href="<?= $OAresult["url"]; ?>" target="openarch.nl">
                                            <?= $OAresult["personname"]; ?> (<?= $OAresult["relationtype"]; ?>),
                                            <?= $OAresult["eventtype"]; ?> <?= $OAeventdate; ?> <?= $OAresult["eventplace"][0]; ?>,
                                            <?= $OAresult["archive"]; ?>/<?= $OAresult["sourcetype"]; ?>
                                        </a><br>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            ?>
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-auto">
                                    <?= __('No results found'); ?>
                                </div>
                            </div>
                        <?php
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

                if ($OAfromyear || $OAuntilyear) {
                    $OAyear_or_period = '';
                    if ($OAfromyear !== '' && $OAuntilyear === '') {
                        $OAyear_or_period = ' ' . $OAfromyear . '-' . ($OAfromyear + 100);
                    }
                    if ($OAfromyear === '' && $OAuntilyear !== '') {
                        $OAyear_or_period = ' ' . ($OAuntilyear - 100) . '-' . $OAuntilyear;
                    }
                    if ($OAfromyear !== '' && $OAuntilyear !== '') {
                        $OAyear_or_period = ' ' . $OAfromyear . '-' . $OAuntilyear;
                    }
                    if (isset($_POST['search_period'])) {
                        openarchives_new($OAsearchname, $OAyear_or_period);
                    } else {
                        ?>
                        <b><?= __('Search'); ?>: <a href="https://www.openarch.nl/search.php?name=<?= urlencode($OAsearchname . $OAyear_or_period); ?>" target="_blank">https://www.openarch.nl/search.php?name=<?= $OAsearchname . $OAyear_or_period; ?></a></b><br>
                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col-md-auto">
                                <input type="submit" name="search_period" value="<?= __('Search using period'); ?>" class="btn btn-sm btn-success">
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
            <?php } ?>
        </div>

        <!-- Parents -->
        <div class="p-2 m-2 genealogy_search">

            <?php
            if ($person->pers_famc) {
                // *** Search for parents ***
                $family_parentsDb = $db_functions->get_family($person->pers_famc, 'man-woman');

                echo '<b>' . ucfirst(__('parents')) . '</b>';

                //*** Father ***
                if ($family_parentsDb->fam_man) {
                    echo ' ' . show_person($family_parentsDb->fam_man);
                }

                echo ' ' . __('and') . ' ';

                //*** Mother ***
                if ($family_parentsDb->fam_woman) {
                    echo show_person($family_parentsDb->fam_woman);
                }
            } else {
                $hideshow = 701;
            ?>
                <!-- Add existing or new parents -->
                <b><?= __('There are no parents.'); ?></b>
                <a href="#" onclick="hideShow('<?= $hideshow; ?>');"><?= __('Add parents'); ?></a>
                <span class="humo row701" style="margin-left:0px; display:none;"> <!-- Show/ hide parents form -->

                    <!-- Add father -->
                    <div class="row m-2">
                        <div class="col-md-3"></div>
                        <div class="col-md-7 bg-primary-subtle">
                            <h2><?= __('Father'); ?></h2>
                        </div>
                    </div>
                    <?php edit_firstname('pers_firstname1', ''); ?>
                    <?php edit_prefix('pers_prefix1', ''); ?>
                    <?php edit_lastname('pers_lastname1', ''); ?>
                    <?php edit_patronymic('pers_patronym1', ''); ?>
                    <?php edit_event_name('event_gedcom_add1', 'event_event_name1', ''); ?>
                    <?php edit_privacyfilter('pers_alive1', ''); ?>
                    <?php edit_sexe('pers_sexe1', 'M'); ?>
                    <?php edit_profession('event_profession1', ''); ?>

                    <!-- Add mother -->
                    <div class="row mb-2">
                        <div class="col-md-3"></div>
                        <div class="col-md-7 bg-primary-subtle">
                            <h2><?= __('Mother'); ?></h2>
                        </div>
                    </div>
                    <?php edit_firstname('pers_firstname2', ''); ?>
                    <?php edit_prefix('pers_prefix2', ''); ?>
                    <?php edit_lastname('pers_lastname2', ''); ?>
                    <?php edit_patronymic('pers_patronym2', ''); ?>
                    <?php edit_event_name('event_gedcom_add2', 'event_event_name2', ''); ?>
                    <?php edit_privacyfilter('pers_alive2', ''); ?>
                    <?php edit_sexe('pers_sexe2', 'F'); ?>
                    <?php edit_profession('event_profession2', ''); ?>

                    <div class="row mb-2">
                        <div class="col-md-3"></div>
                        <div class="col-md-7">
                            <input type="submit" name="add_parents2" value="<?= __('Add parents'); ?>" class="btn btn-sm btn-success">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3"></div>
                        <div class="col-md-7">
                            <?= __('Or select an existing family as parents:'); ?>
                            <div class="input-group">
                                <input type="text" name="add_parents" placeholder="<?= __('GEDCOM number (ID)'); ?>" value="" size="20" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_relation_select","","<?= $field_popup; ?>")'><img src="../images/search.png" alt=<?= __('Search'); ?>></a>
                                &nbsp;<input type="submit" name="dummy2" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
                            </div>
                        </div>
                    </div>

                </span> <!-- End of hide item -->
            <?php } ?>

        </div>
    <?php } ?>

    <table class="table table-light" id="table_editor">
        <?php if ($editor['add_person'] == false) { ?>
            <?php
            // *** Show message if age < 0 or > 120 ***
            $show_age_message = '';
            if (($person->pers_bapt_date || $person->pers_birth_date) && $person->pers_death_date) {
                $process_age = new \Genealogy\Include\CalculateDates;
                $age = $process_age->calculate_age($person->pers_bapt_date, $person->pers_birth_date, $person->pers_death_date, true);
                if ($age && ($age < 0 || $age > 120)) {
                    $show_age_message = $age;
                }
            }
            ?>

            <?php if ($show_age_message) { ?>
                <div class="alert alert-danger my-4" role="alert">
                    &nbsp;<?= ucfirst(__('age')); ?> <?= $age; ?> <?= __('year'); ?>
                </div>
            <?php } ?>
        <?php } ?>

        <thead class="table-primary">
            <tr>
                <td><a href="#" onclick="hideShowAll();"><span id="hideshowlinkall">[+]</span> <?= __('All'); ?></a></td>

                <th style="font-size: 1.5em;" colspan="2">
                    <?php
                    if ($editor['add_person'] == false) {
                    ?>
                        <input type="submit" name="person_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

                        <?php
                        echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);

                        // *** Add person to admin favourite list ***
                        $fav_qry = "SELECT * FROM humo_settings
                            WHERE setting_variable = :setting_variable
                            AND setting_tree_id = :setting_tree_id
                            AND setting_value = :setting_value";
                        $fav_stmt = $dbh->prepare($fav_qry);
                        $fav_stmt->execute([
                            ':setting_variable' => 'admin_favourite',
                            ':setting_tree_id' => $tree_id,
                            ':setting_value' => $pers_gedcomnumber
                        ]);
                        $fav_result = $fav_stmt;
                        $rows = $fav_result->rowCount();
                        if ($rows > 0) {
                        ?>
                            <a href="index.php?page=editor&amp;person=<?= $pers_gedcomnumber; ?>&amp;pers_favorite=0"><img src="../images/favorite_blue.png" style="border: 0px" alt="<?= __('Remove from favourite list'); ?>"></a>
                        <?php } else { ?>
                            <a href="index.php?page=editor&amp;person=<?= $pers_gedcomnumber; ?>&amp;pers_favorite=1"><img src="../images/favorite.png" style="border: 0px" alt="<?= __('Add to favourite list'); ?>"></a>
                        <?php
                        }
                    } else {
                        ?>
                        <input type=" submit" name="person_add" value="<?= __('Add'); ?>" class="btn btn-sm btn-success">
                    <?php } ?>
                </th>
            </tr>
        </thead>

        <tr>
            <!-- Name-->
            <?php
            $hideshow = '1';
            $display = ' display:none;';
            // *** New person: show all name fields ***
            if (!$pers_gedcomnumber) {
                $display = '';
            }
            $check_sources_text = '';
            if ($pers_gedcomnumber) {
                $check_sources_text = check_sources('person', 'pers_name_source', $pers_gedcomnumber);
            }
            ?>
            <td><a name="name"></a><b><?= __('Name'); ?></b></td>
            <td colspan="2">
                <?php if ($pers_gedcomnumber) { ?>
                    <span class="hideshowlink" onclick="hideShow(<?= $hideshow; ?>);">
                        <b>
                            <?php
                            echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);
                            if ($pers_name_text) {
                            ?>
                                <img src="images/text.png" height="16" alt="<?= __('Text'); ?>">
                            <?php
                            }
                            echo ' ' . $check_sources_text;
                            ?>
                        </b>
                    </span><br>
                <?php } ?>

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>">
                    <?php edit_firstname('pers_firstname', $pers_firstname); ?>
                    <?php edit_prefix('pers_prefix', $pers_prefix); ?>
                    <?php edit_lastname('pers_lastname', $pers_lastname); ?>
                    <?php edit_patronymic('pers_patronym', $pers_patronym); ?>

                    <?php
                    if ($humo_option['admin_hebname'] == "y") {
                        // user requested hebrew name field to be displayed here, not under "events"
                        $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_HEBN' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_kind='name' AND event_connect_kind='person'";
                        $result = $dbh->query($sql);
                        if ($result->rowCount() > 0) {
                            $hebnameDb = $result->fetch(PDO::FETCH_OBJ);
                            $he_name =  $hebnameDb->event_event;
                        } else {
                            $he_name = '';
                        }
                    ?>
                        <!-- Hebrew name -->
                        <div class="row mb-2">
                            <label for="hebrew_name" class="col-md-3 col-form-label"><?= ucfirst(__('Hebrew name')); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="even_hebname" value="<?= htmlspecialchars($he_name); ?>" size="35" class="form-control form-control-sm">
                                <span style="font-size: 13px;"><?= __("For example: Joseph ben Hirsch Zvi"); ?></span>
                            </div>
                        </div>
                    <?php
                    }

                    // *** Person text by name ***
                    $text = $editor_cls->text_show($pers_name_text);
                    // *** Check if there are multiple lines in text ***
                    // TODO check all these fields.
                    $field_text_selected = $field_text;
                    if ($text && preg_match('/\R/', $text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <!-- Text -->
                    <div class="row mb-2">
                        <label for="text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="pers_name_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                        </div>
                    </div>

                    <?php
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
                    <input id="submit_ajax" type="button" value="Submit" class="btn btn-sm btn-success">
                    <?php
                    // END TEST SCRIPT
                    */


                    // *** Source by name ***
                    // *** source_link3($connect_kind, $connect_sub_kind, $connect_connect_id) ***
                    if (!isset($_GET['add_person'])) {
                    ?>
                        <!-- Source -->
                        <div class="row mb-2">
                            <label for="source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'pers_name_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </span>
            </td>
        </tr>

        <?php
        if ($editor['add_person'] == false) {
            // *** Event name (also show ADD line for prefix, suffix, title etc.) ***
            // TODO SEE ALSO: function edit_event_name in editor.php.
            // *** Nickname, alias, adopted name, hebrew name, etc. ***
            // *** Remark: in editorModel.php a check is done for event_event_name, so this will also be saved if "Save" is clicked ***
        ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <div class="row">
                        <div class="col-md-4">
                            <select size="1" name="event_gedcom_add" id="event_gedcom_add" aria-label="<?= __('Name'); ?>" class="form-select form-select-sm">
                                <?php $editorEventSelection->event_selection(''); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="event_event_name" id="event_event_name" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="submit" name="event_add_name" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>
                    </div>
                </td>
            </tr>
        <?php
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'name');

            // *** NPFX Name prefix like: Lt. Cmndr. ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'NPFX');

            // *** NSFX Name suffix like: jr. ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'NSFX');

            // *** Title of Nobility ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'nobility');

            // *** Title ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'title');

            // *** Lordship ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'lordship');
        }

        // *** Alive ***
        // *** Disable radio boxes if person is deceased ***
        $disabled = '';
        if ($pers_death_date || $pers_death_place || $pers_buried_date || $pers_buried_place) {
            $disabled = ' disabled';
        }
        ?>
        <tr>
            <td><?= __('Privacy filter'); ?></td>
            <td colspan="2">
                <input type="radio" name="pers_alive" value="alive" <?= $pers_alive == 'alive' ? 'checked' : ''; ?> <?= $disabled; ?> class="form-check-input"> <?= __('alive'); ?>
                <input type="radio" name="pers_alive" value="deceased" <?= $pers_alive == 'deceased' ? 'checked' : ''; ?> <?= $disabled; ?> class="form-check-input"> <?= __('deceased'); ?>
                <?= $disabled ? '<input type="hidden" name="pers_alive" value="deceased">' : ''; ?>

                <!-- Estimated/ calculated (birth) date, can be used for privacy filter -->
                <?php if (!$pers_cal_date) {
                    $pers_cal_date = 'dd mmm yyyy';
                } ?>
                <span style="color:#6D7B8D;">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?page=cal_date"><?= __('Calculated birth date'); ?>:</a> <?= $languageDate->language_date($pers_cal_date); ?>
                </span>

                <?php
                /*
                <?= edit_privacyfilter('pers_alive', ''); ?>
                */
                ?>
            </td>
        </tr>

        <?php
        // *** Sexe ***
        $check_sources_text = '';
        if ($pers_gedcomnumber) {
            $check_sources_text = check_sources('person', 'pers_sexe_source', $pers_gedcomnumber);
        }
        ?>
        <tr>
            <td><a name="sex"></a><?= __('Sex'); ?></td>
            <td <?= $pers_sexe == '' ? 'class="table-danger"' : ''; ?> colspan="2">
                <input type="radio" name="pers_sexe" value="M" <?= $pers_sexe == 'M' ? 'checked' : '' ?> class="form-check-input"> <?= __('male'); ?>
                <input type="radio" name="pers_sexe" value="F" <?= $pers_sexe == 'F' ? 'checked' : ''; ?> class="form-check-input"> <?= __('female'); ?>
                <input type="radio" name="pers_sexe" value="" <?= $pers_sexe == '' ? 'checked' : ''; ?> class="form-check-input"> ?

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php
                if (!isset($_GET['add_person'])) {
                    source_link3('sex', 'pers_sexe_source', $pers_gedcomnumber);
                    echo $check_sources_text;
                }
                ?>

                <?php
                /*
                <?= edit_sexe('pers_sexe2', $pers_sexe); ?>
                */
                ?>
            </td>
        </tr>

        <?php
        // *** Born ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '2';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
        ?>
        <tr>
            <td><a name="born"></a>
                <b><?= ucfirst(__('born')); ?></b>
            </td>
            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($pers_birth_date, $pers_birth_place);
                if ($pers_birth_time) {
                    $hideshow_text .= ' ' . __('at') . ' ' . $pers_birth_time . ' ' . __('hour');
                }
                //TEST
                //if (!$hideshow_text) $hideshow_text=ucfirst(__('born'));

                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('born', 'pers_birth_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $pers_birth_text); ?>

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                    <div class="row mb-2">
                        <label for="pers_birth_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show($pers_birth_date, 'pers_birth_date', '', $pers_birth_date_hebnight, 'pers_birth_date_hebnight'); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_birth_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="pers_birth_place" value="<?= htmlspecialchars($pers_birth_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_birth_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_birth_time" class="col-md-3 col-form-label"><?= ucfirst(__('birth time')); ?></label>
                        <div class="col-md-2">
                            <input type="text" name="pers_birth_time" value="<?= $pers_birth_time; ?>" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <input type="checkbox" name="pers_stillborn" <?= (isset($pers_stillborn) && $pers_stillborn == 'y') ? 'checked' : ''; ?> class="form-check-input"> <?= __('stillborn child'); ?>
                        </div>
                    </div>

                    <?php
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($pers_birth_text && preg_match('/\R/', $pers_birth_text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="pers_birth_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($pers_birth_text); ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('born', 'pers_birth_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </span>
            </td>
        </tr>

        <?php
        // *** Sep. 2024: using seperate birth declaration lines ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '201';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

        $sql = "SELECT * FROM humo_events WHERE event_tree_id = '" . $tree_id . "' AND event_kind = 'birth_declaration' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
        $result = $dbh->query($sql);
        if ($result->rowCount() > 0) {
            $birth_declDb = $result->fetch(PDO::FETCH_OBJ);
            $birth_decl_id = $birth_declDb->event_id;
            $birth_decl_date = $birth_declDb->event_date;
            $birth_decl_place = $birth_declDb->event_place;
            $birth_decl_text = $birth_declDb->event_text;
        } else {
            $birth_decl_id = '';
            $birth_decl_date = '';
            $birth_decl_place = '';
            $birth_decl_text = '';
        }
        ?>
        <tr>
            <td><a name="birth_declaration"></a>
                <?= ucfirst(__('birth declaration')); ?>

                <input type="hidden" name="birth_decl_id" value="<?= $birth_decl_id; ?>">
            </td>

            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($birth_decl_date, $birth_decl_place);
                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('birth_decl', 'birth_decl_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $birth_decl_text); ?>

                <input type="submit" name="add_birth_declaration" value="<?= __('witness') . ' - ' . __('officiator'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                    <div class="row mb-2">
                        <label for="birth_decl_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <!-- TODO check this -->
                            <?php $editor_cls->date_show($birth_decl_date, 'birth_decl_date', '', '', ''); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="birth_decl_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="birth_decl_place" value="<?= htmlspecialchars($birth_decl_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=birth_decl_place","","<?= $field_popup; ?>")'>
                                    <img src="../images/search.png" alt="<?= __('Search'); ?>">
                                </a><br>
                            </div>
                        </div>
                    </div>

                    <?php
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($birth_decl_text && preg_match('/\R/', $birth_decl_text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="birth_decl_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="birth_decl_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($birth_decl_text); ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="birth_decl_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                // *** This sourve is connected to person, not to event. Because event id is only available when saved ***
                                source_link3('birth_decl', 'birth_decl_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </span>
            </td>
        </tr>
        <?php
        // *** Birth declaration ***
        if ($editor['add_person'] == false) {
            //show_event($event_connect_kind, $event_connect_id, $event_kind)
            echo $EditorEvent->show_event('birth_declaration', $pers_gedcomnumber, 'witness');
        }

        // **** BRIT MILA ***
        if ($humo_option['admin_brit'] == "y" && $pers_sexe != "F") {

            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '20';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

            $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_BRTM' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
            $result = $dbh->query($sql);
            if ($result->rowCount() > 0) {
                $britDb = $result->fetch(PDO::FETCH_OBJ);
                $britid = $britDb->event_id;
                $britdate = $britDb->event_date;
                $britplace = $britDb->event_place;
                $brittext = $britDb->event_text;
            } else {
                $britid = '';
                $britdate = '';
                $britplace = '';
                $brittext = '';
            }
            //$britDb = $result->fetch(PDO::FETCH_OBJ);
        ?>
            <tr>
                <td><?= ucfirst(__('Brit Mila')); ?></td>
                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($britdate, $britplace);
                    if ($pers_gedcomnumber and $britid) {
                        $check_sources_text = check_sources('person', 'pers_event_source', $britid);
                        $hideshow_text .= $check_sources_text;
                    }
                    ?>
                    <?= hideshow_editor($hideshow, $hideshow_text, $brittext); ?>

                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="even_brit_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($britdate, 'even_brit_date'); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="even_brit_place" value="<?= htmlspecialchars($britplace); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $text = $editor_cls->text_show($brittext);
                        $field_text_selected = $field_text;
                        if ($text && preg_match('/\R/', $text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="even_brit_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                            </div>
                        </div>

                        <?php if (!isset($_GET['add_person'])) { ?>
                            <div class="row mb-2">
                                <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('person', 'pers_event_source', $britid);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php
                        }

                        echo '<i>' . __('To display this, the option "Show events" has to be checked in "Users -> Groups"') . '</i>';
                        // echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=even_brit_place","","'.$field_popup.'")\'><img src="../images/search.png" alt="'.__('Search').'"></a>';
                        ?>
                    </span>
                </td>
            </tr>
        <?php
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
                $barid =  $barmDb->event_id;
                $bardate =  $barmDb->event_date;
                $barplace =  $barmDb->event_place;
                $bartext =  $barmDb->event_text;
            } else {
                $barid = '';
                $bardate = '';
                $barplace = '';
                $bartext = '';
            }
        ?>

            <tr>
                <td>
                    <?= $pers_sexe == "F" ? __('Bat Mitzvah') : __('Bar Mitzvah'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($bardate, $barplace);
                    if ($pers_gedcomnumber and $barid) {
                        $check_sources_text = check_sources('person', 'pers_event_source', $barid);
                        $hideshow_text .= $check_sources_text;
                    }
                    echo hideshow_editor($hideshow, $hideshow_text, $bartext);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                        <div class="row mb-2">
                            <label for="even_barm_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($bardate, 'even_barm_date'); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="even_barm_date" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="even_barm_place" value="<?= htmlspecialchars($barplace); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $text = $editor_cls->text_show($bartext);
                        $field_text_selected = $field_text;
                        if ($text && preg_match('/\R/', $text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="even_barm_date" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="even_barm_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                            </div>
                        </div>

                        <?php if (!isset($_GET['add_person'])) { ?>
                            <div class="row mb-2">
                                <label for="pers_event_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('person', 'pers_event_source', $barid);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                        <i><?= __('To display this, the option "Show events" has to be checked in "Users -> Groups"'); ?></i>
                    </span>
                </td>
            </tr>
        <?php
        }


        // *** Baptise/ Christened ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '3';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
        ?>
        <tr>
            <td><a name="baptised"></a><b><?= ucfirst(__('baptised')); ?></b></td>
            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($pers_bapt_date, $pers_bapt_place);
                if ($pers_religion) {
                    $hideshow_text .= ' (' . __('religion') . ': ' . $pers_religion . ')';
                }
                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('person', 'pers_bapt_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $pers_bapt_text); ?>

                <input type="submit" name="add_baptism_witness" value="<?= __('witness') . ' - ' . __('clergy') . ' - ' . __('godfather'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                    <div class="row mb-2">
                        <label for="pers_bapt_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show($pers_bapt_date, 'pers_bapt_date'); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_bapt_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="pers_bapt_place" value="<?= htmlspecialchars($pers_bapt_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_bapt_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_religion" class="col-md-3 col-form-label"><?= ucfirst(__('religion')); ?></label>
                        <div class="col-md-7">
                            <input type="text" name="pers_religion" value="<?= htmlspecialchars($pers_religion); ?>" size="20" class="form-control form-control-sm">
                        </div>
                    </div>

                    <?php
                    $text = $editor_cls->text_show($pers_bapt_text);
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($text && preg_match('/\R/', $text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="pers_bapt_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="pers_bapt_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'pers_bapt_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                </span>
            </td>
        </tr>

        <?php
        // *** Baptism Witness ***
        if ($editor['add_person'] == false) {
            echo $EditorEvent->show_event('CHR', $pers_gedcomnumber, 'ASSO');
        }


        // *** Died ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '4';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
        ?>
        <tr>
            <td><a name="died"></a>
                <b><?= ucfirst(__('died')); ?></b>
            </td>
            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($pers_death_date, $pers_death_place);

                if ($pers_death_time) {
                    $hideshow_text .= ' ' . __('at') . ' ' . $pers_death_time . ' ' . __('hour');
                }

                if ($pers_death_cause) {
                    if ($hideshow_text) {
                        $hideshow_text .= ', ';
                    }
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
                        $hideshow_text .= __('cause of death') . ': ' . $pers_death_cause;
                    }
                }

                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('person', 'pers_death_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $pers_death_text); ?>

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                    <div class="row mb-2">
                        <label for="pers_death_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show($pers_death_date, 'pers_death_date', '', $pers_death_date_hebnight, 'pers_death_date_hebnight'); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_death_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="pers_death_place" value="<?= htmlspecialchars($pers_death_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_death_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                            </div>
                        </div>
                    </div>

                    <!-- Age by death -->
                    <div class="row mb-2">
                        <label for="pers_death_age" class="col-md-3 col-form-label"><?= __('Age'); ?></label>
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="text" name="pers_death_age" value="<?= $pers_death_age; ?>" size="3" class="form-control form-control-sm">

                                <!-- Help popover for events -->
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                    data-bs-content="<?= __('If death year and age are used, then birth year is calculated automatically (when empty).'); ?>">
                                    ?
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_death_place" class="col-md-3 col-form-label"><?= ucfirst(__('death time')); ?></label>
                        <div class="col-md-2">
                            <input type="text" name="pers_death_time" value="<?= $pers_death_time; ?>" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <!-- Death cause -->
                    <?php
                    $check_cause = false;
                    $pers_death_cause2 = '';
                    $cause_array = array('murdered', 'drowned', 'perished', 'killed in action', 'being missed', 'committed suicide', 'executed', 'died young', 'died unmarried', 'registration', 'declared death');
                    if (!in_array($pers_death_cause, $cause_array)) {
                        $check_cause = true;
                        $pers_death_cause2 = $pers_death_cause;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="pers_death_cause" class="col-md-3 col-form-label"><?= ucfirst(__('cause')); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <select size="1" id="pers_death_cause" name="pers_death_cause" class="form-select form-select-sm">
                                    <option value=""></option>
                                    <option value="murdered" <?= $pers_death_cause == 'murdered' ? 'selected' : ''; ?>><?= __('murdered'); ?></option>
                                    <option value="drowned" <?= $pers_death_cause == 'drowned' ? 'selected' : ''; ?>><?= __('drowned'); ?></option>
                                    <option value="perished" <?= $pers_death_cause == 'perished' ? 'selected' : ''; ?>><?= __('perished'); ?></option>
                                    <option value="killed in action" <?= $pers_death_cause == 'killed in action' ? 'selected' : ''; ?>><?= __('killed in action'); ?></option>
                                    <option value="being missed" <?= $pers_death_cause == 'being missed' ? 'selected' : ''; ?>><?= __('being missed'); ?></option>
                                    <option value="committed suicide" <?= $pers_death_cause == 'committed suicide' ? 'selected' : ''; ?>><?= __('committed suicide'); ?></option>
                                    <option value="executed" <?= $pers_death_cause == 'executed' ? 'selected' : ''; ?>><?= __('executed'); ?></option>
                                    <option value="died young" <?= $pers_death_cause == 'died young' ? 'selected' : ''; ?>><?= __('died young'); ?></option>
                                    <option value="died unmarried" <?= $pers_death_cause == 'died unmarried' ? 'selected' : ''; ?>><?= __('died unmarried'); ?></option>
                                    <option value="registration" <?= $pers_death_cause == 'registration' ? 'selected' : ''; ?>><?= __('registration'); ?></option>
                                    <option value="declared death" <?= $pers_death_cause == 'declared death' ? 'selected' : ''; ?>><?= __('declared death'); ?></option>
                                </select>
                                &nbsp;<b><?= __('or'); ?>:</b>&nbsp;
                                <input type="text" name="pers_death_cause2" value="<?= $pers_death_cause2; ?>" size="<?= $field_date; ?>" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <?php
                    $text = $editor_cls->text_show($pers_death_text);
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($text && preg_match('/\R/', $text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="pers_death_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="pers_death_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'pers_death_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                </span>
            </td>
        </tr>

        <?php
        // *** Sep. 2024: using seperate death declaration lines ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '401';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

        $sql = "SELECT * FROM humo_events WHERE event_tree_id = '" . $tree_id . "' AND event_kind = 'death_declaration' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
        $result = $dbh->query($sql);
        if ($result->rowCount() > 0) {
            $death_declDb = $result->fetch(PDO::FETCH_OBJ);
            $death_decl_id = $death_declDb->event_id;
            $death_decl_date = $death_declDb->event_date;
            $death_decl_place = $death_declDb->event_place;
            $death_decl_text = $death_declDb->event_text;
        } else {
            $death_decl_id = '';
            $death_decl_date = '';
            $death_decl_place = '';
            $death_decl_text = '';
        }
        ?>
        <tr>
            <td><a name="death_declaration"></a>
                <?= ucfirst(__('death declaration')); ?>

                <input type="hidden" name="death_decl_id" value="<?= $death_decl_id; ?>">
            </td>

            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($death_decl_date, $death_decl_place);
                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('death_decl', 'death_decl_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $death_decl_text); ?>

                <input type="submit" name="add_death_declaration" value="<?= __('witness') . ' - ' . __('officiator'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                    <div class="row mb-2">
                        <label for="death_decl_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <!-- TODO check this -->
                            <?php $editor_cls->date_show($death_decl_date, 'death_decl_date', '', '', ''); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="death_decl_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="death_decl_place" value="<?= htmlspecialchars($death_decl_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=death_decl_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                            </div>
                        </div>
                    </div>

                    <?php
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($death_decl_text && preg_match('/\R/', $death_decl_text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="death_decl_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="death_decl_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($death_decl_text); ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="death_decl_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                // *** This source is connected to person, not to event. Because event id is only available when saved ***
                                source_link3('death_decl', 'death_decl_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </span>
            </td>
        </tr>
        <?php
        // *** Death declaration ***
        if ($editor['add_person'] == false) {
            echo $EditorEvent->show_event('death_declaration', $pers_gedcomnumber, 'ASSO');
        }


        // *** Buried ***
        // *** Use hideshow to show and hide the editor lines ***
        $hideshow = '5';
        // *** If items are missing show all editor fields ***
        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
        ?>

        <tr>
            <td><a name="buried"></a>
                <b><?= __('Buried'); ?></b>
            </td>
            <td colspan="2">
                <?php
                $hideshow_text = hideshow_date_place($pers_buried_date, $pers_buried_place);
                if ($pers_gedcomnumber) {
                    $check_sources_text = check_sources('person', 'pers_buried_source', $pers_gedcomnumber);
                    $hideshow_text .= $check_sources_text;
                }
                ?>
                <?= hideshow_editor($hideshow, $hideshow_text, $pers_buried_text); ?>

                <input type="submit" name="add_burial_witness" value="<?= __('witness') . ' - ' . __('clergy'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                    <div class="row mb-2">
                        <label for="pers_buried_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show($pers_buried_date, 'pers_buried_date', '', $pers_buried_date_hebnight, 'pers_buried_date_hebnight'); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="pers_buried_place" value="<?= htmlspecialchars($pers_buried_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_buried_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a><br>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="pers_cremation" class="col-md-3 col-form-label"><?= ucfirst(__('method of burial')); ?></label>
                        <div class="col-md-7">
                            <select size="1" id="pers_cremation" name="pers_cremation" class="form-select form-select-sm">
                                <option value=""><?= __('buried'); ?></option>
                                <option value="1" <?= $pers_cremation == '1' ? 'selected' : ''; ?>><?= __('cremation'); ?></option>
                                <option value="R" <?= $pers_cremation == 'R' ? 'selected' : ''; ?>><?= __('resomated'); ?></option>
                                <option value="S" <?= $pers_cremation == 'S' ? 'selected' : ''; ?>><?= __('sailor\'s grave'); ?></option>
                                <option value="D" <?= $pers_cremation == 'D' ? 'selected' : ''; ?>><?= __('donated to science'); ?></option>
                            </select>
                        </div>
                    </div>

                    <?php
                    $text = $editor_cls->text_show($pers_buried_text);
                    // *** Check if there are multiple lines in text ***
                    $field_text_selected = $field_text;
                    if ($text && preg_match('/\R/', $text)) {
                        $field_text_selected = $field_text_medium;
                    }
                    ?>
                    <div class="row mb-2">
                        <label for="pers_buried_date" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="pers_buried_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $text; ?></textarea>
                        </div>
                    </div>

                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <label for="pers_burial_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'pers_buried_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                </span>
            </td>
        </tr>

        <?php
        // *** Burial Witness ***
        if ($editor['add_person'] == false) {
            echo $EditorEvent->show_event('BURI', $pers_gedcomnumber, 'ASSO');
        }
        ?>

        <!-- General text by person -->
        <tr>
            <td><a name="text_person"></a><?= __('Text for person'); ?></td>
            <td colspan="2">
                <textarea rows="1" name="person_text" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($person_text); ?></textarea>

                <?php if (!isset($_GET['add_person'])) { ?>
                    <div class="row mb-2">
                        <!-- <label for="pers_text_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                        <div class="col-md-7">
                            <?php
                            source_link3('person', 'pers_text_source', $pers_gedcomnumber);

                            if ($pers_gedcomnumber) {
                                $check_sources_text = check_sources('person', 'pers_text_source', $pers_gedcomnumber);
                                echo $check_sources_text;
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </td>
        </tr>

        <?php
        if (!isset($_GET['add_person'])) {
            // *** Person sources in new person editor screen ***
        ?>
            <tr>
                <td><a name="source_person"></a><?= __('Source for person'); ?></td>
                <td>
                    <?php if (!isset($_GET['add_person'])) { ?>
                        <div class="row mb-2">
                            <!-- <label for="pers_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'person_source', $pers_gedcomnumber);
                                if ($pers_gedcomnumber) {
                                    $check_sources_text = check_sources('person', 'person_source', $pers_gedcomnumber);
                                    echo $check_sources_text;
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>

        <!-- Own code -->
        <tr>
            <td><?= ucfirst(__('own code')); ?></td>
            <td colspan="2">
                <div class="row mb-2">
                    <!-- <label for="pers_buried_place" class="col-md-3 col-form-label"><?= ucfirst(__('own code')); ?></label> -->
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" name="pers_own_code" value="<?= htmlspecialchars($pers_own_code); ?>" class="form-control form-control-sm">

                            <!-- Help popover for own code -->
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-toggle="popover" data-bs-placement="bottom" data-bs-custom-class="popover-wide" data-bs-html="true"
                                data-bs-title="<?= ucfirst(__('own code')); ?>"
                                data-bs-content="
                                <ul>
                                    <li>
                                        <?= __('Use own code for your own remarks.'); ?>
                                    </li>
                                    <li>
                                        <?= __('It\'s possible to use own code for special privacy options, see Admin > Users > Groups.'); ?>
                                    </li>
                                    <li>
                                        <?= __('You can add your own icons by a person! Add the icon in the images folder e.g. \'person.gif\', and add \'person\' in the own code field.'); ?>
                                        </li>
                                </ul>
                                ">
                                ?
                            </button>

                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <?php
        // TODO SEE ALSO: function edit_event_profession in editor.php. 
        ?>
        <!-- Profession(s) -->
        <tr id="profession">
            <td style="border-right:0px;">
                <b><?= __('Profession'); ?></b>
            </td>
            <td colspan="2">
                <?php
                // *** Skip for newly added person ***
                // *** Remark: in editorModel.php a check is done for event_event_profession, so this will also be saved if "Save" is clicked ***
                if (!isset($_GET['add_person'])) {
                ?>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="event_event_profession" value="" size="35" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <input type="submit" name="event_add_profession" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>
                    </div>
                <?php } ?>
            </td>
        </tr>

        <?php if (isset($_GET['add_person'])) { ?>
            <!-- Directly add a first profession for new person -->
            <tr>
                <td style="border-right:0px;"><?= __('Profession'); ?></td>
                <td colspan="2">
                    <div class="row mb-2">
                        <label for="event_profession" class="col-md-3 col-form-label"><?= __('Profession'); ?></label>
                        <div class="col-md-7">
                            <input type="text" name="event_profession" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_date_profession" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show("", "event_date_profession", ""); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_place_profession" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <input type="text" name="event_place_profession" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_text_profession" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="event_text_profession" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show(""); ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>

        <?= $EditorEvent->show_event('person', $pers_gedcomnumber, 'profession'); ?>

        <!-- Religion -->
        <tr id="religion">
            <td style="border-right:0px;"><?= __('Religion'); ?></td>
            <td colspan="2">
                <?php
                // *** Skip for newly added person ***
                if (!isset($_GET['add_person'])) {
                    // *** Remark: in editorModel.php a check is done for event_event_religion, so this will also be saved if "Save" is clicked ***
                ?>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="event_event_religion" value="" size="35" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <input type="submit" name="event_add_religion" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">
                        </div>
                    </div>
                <?php } ?>
            </td>
        </tr>

        <?php if (isset($_GET['add_person'])) { ?>
            <!-- Directly add a religion for new person -->
            <tr>
                <td style="border-right:0px;"><?= __('Religion'); ?></td>
                <td colspan="2">
                    <div class="row mb-2">
                        <label for="event_religion" class="col-md-3 col-form-label"><?= __('Religion'); ?></label>
                        <div class="col-md-7">
                            <input type="text" name="event_religion" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_date_religion" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                        <div class="col-md-7">
                            <?php $editor_cls->date_show("", "event_date_religion", ""); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_place_religion" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                        <div class="col-md-7">
                            <input type="text" name="event_place_religion" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label for="event_text_religion" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                        <div class="col-md-7">
                            <textarea rows="1" name="event_text_religion" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show(""); ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>

        <?php
        echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'religion');

        if (!isset($_GET['add_person'])) {
            // *** Show and edit places by person ***
            $connect_kind = 'person';
            $connect_sub_kind = 'person_address';
            $connect_connect_id = $pers_gedcomnumber;
            include_once __DIR__ . '/partial/editor_addresses.php';
        }

        if (!isset($_GET['add_person'])) {
        ?>
            <!-- Person events -->
            <tr id="event_person_link">
                <td><?= __('Events'); ?></td>
                <td colspan="2">
                    <div class="row">
                        <!-- Add person event -->
                        <div class="col-4">
                            <select size="1" name="event_kind" aria-label="<?= __('Events'); ?>" class="form-select form-select-sm">
                                <option value="event"><?= __('Event'); ?></option>
                                <option value="adoption"><?= __('Adoption'); ?></option>
                                <option value="URL"><?= __('URL/ Internet link'); ?></option>
                                <option value="person_colour_mark"><?= __('Colour mark by person'); ?></option>
                            </select>
                        </div>

                        <div class="col-3">
                            <input type="submit" name="person_event_add" value="<?= __('Add event'); ?>" class="btn btn-sm btn-outline-primary">

                            <!-- Help popover for events -->
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                data-bs-content="<?= __('For items like:') . ' ' . __('Event') . ', ' . __('baptized as child') . ', ' . __('depart') . ' ' . __('etc.'); ?>">
                                ?
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'person');

            // *** Picture ***
            echo $EditorEvent->show_event('person', $pers_gedcomnumber, 'picture');

            // *** Quality ***
            // Disabled quality by person. Quality officially belongs to a source...
            /*
            <tr><td><?= __('Quality of data');?></td>
                <td style="border-right:0px;"></td>
                <td style="border-left:0px;">
                    <select size="1" name="pers_quality" aria-label="<?= __('Quality of data'); ?>" style="width: 400px">
                        <option value=""><?= ucfirst(__('quality: default'));?></option>
                        $selected=''; if ($pers_quality=='0'){ $selected=' selected'; }
                        <option value="0"<?= $selected;?>><?= ucfirst(__('quality: unreliable evidence or estimated data'));?></option>
                        $selected=''; if ($pers_quality=='1'){ $selected=' selected'; }
                        <option value="1"<?= $selected;?>><?= ucfirst(__('quality: questionable reliability of evidence'));?></option>
                        $selected=''; if ($pers_quality=='2'){ $selected=' selected'; }
                        <option value="2"<?= $selected;?>><?= ucfirst(__('quality: data from secondary evidence'));?></option>
                        $selected=''; if ($pers_quality=='3'){ $selected=' selected'; }
                        <option value="3"<?= $selected;?>><?= ucfirst(__('quality: data from direct source'));?></option>
                    </select>
                </td>
                <td></td>
            </tr>
            */

            // *** Show unprocessed GEDCOM tags ***
            $tag_qry = "SELECT * FROM humo_unprocessed_tags WHERE tag_tree_id='" . $tree_id . "' AND tag_pers_id='" . $person->pers_id . "'";
            $tag_result = $dbh->query($tag_qry);
            $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
            if (isset($tagDb->tag_tag)) {
                $tags_array = explode('<br>', $tagDb->tag_tag);
                $num_rows = count($tags_array);
            ?>
                <tr class="humo_tags_pers">
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
            $note_connect_kind = 'person';
            include_once __DIR__ . '/partial/editor_notes.php';

            // *** Show user added notes ***
            $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $tree_id . "'
                AND note_kind='user' AND note_connect_kind='person' AND note_connect_id='" . $pers_gedcomnumber . "'";
            $note_result = $dbh->query($note_qry);
            $num_rows = $note_result->rowCount();
            ?>

            <tr>
                <td>
                    <?php if ($num_rows) { ?>
                        <a href="#humo_user_notes" onclick="hideShow(62);"><span id="hideshowlink62">[+]</span></a>
                    <?php } ?>
                    <?= __('User notes'); ?>
                </td>
                <td colspan="2">
                    <?php
                    if ($num_rows) {
                        printf(__('There are %d user added notes.'), $num_rows);
                    } else {
                        printf(__('There are %d user added notes.'), 0);
                    }
                    ?>
                </td>
            </tr>

            <?php
            while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
                $user_name = $db_functions->get_user_name($noteDb->note_new_user_id);
            ?>
                <tr class="row62" style="display:none;">
                    <td></td>
                    <td colspan="2">
                        <?= __('Added by'); ?> <b><?= $user_name; ?></b> (<?= $languageDate->show_datetime($noteDb->note_new_datetime); ?>)<br>
                        <b><?= $noteDb->note_names; ?></b><br>
                        <textarea readonly rows="1" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($noteDb->note_note); ?></textarea>
                    </td>
                </tr>
            <?php
            }

            // *** Person added by user ***
            if ($person->pers_new_user_id || $person->pers_new_datetime) {
            ?>
                <tr>
                    <td><?= __('Added by'); ?></td>
                    <td colspan="2">
                        <?= $languageDate->show_datetime($person->pers_new_datetime) . ' ' . $db_functions->get_user_name($person->pers_new_user_id); ?>
                    </td>
                </tr>
            <?php
            }

            // *** Person changed by user ***
            if ($person->pers_changed_user_id || $person->pers_changed_datetime) {
            ?>
                <tr>
                    <td><?= __('Changed by'); ?></td>
                    <td colspan="2">
                        <?= $languageDate->show_datetime($person->pers_changed_datetime) . ' ' . $db_functions->get_user_name($person->pers_changed_user_id); ?>
                    </td>
                </tr>
        <?php
            }
        }
        ?>

        <!-- Extra "Save" line -->
        <tr>
            <td></td>
            <td colspan="2">
                <?php if ($editor['add_person'] == false) { ?>
                    <input type="submit" name="person_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                    <?= __('or'); ?>
                    <input type="submit" name="person_remove" value="<?= __('Delete person'); ?>" class="btn btn-sm btn-secondary">
                <?php } else { ?>
                    <input type="submit" name="person_add" value="<?= __('Add'); ?>" class="btn btn-sm btn-success">
                <?php } ?>
            </td>
        </tr>

    </table><br>
</form>