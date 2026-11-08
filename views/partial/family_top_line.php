<?php
$treetext = $showTreeText->show_tree_text($selectedFamilyTree->tree_id, $selected_language);

// *** Generate unique ID suffix to avoid duplicate IDs when this file is included multiple times ***
$id_suffix = '_' . uniqid();
?>

<tr class="table-secondary">
    <td>
        <div class="fs-4 family_page_toptext"><?= $HtmlEscaper->escHtml($treetext['family_top']); ?><br></div>
    </td>

    <td width="250" style="text-align:right;">
        <!-- Hide selections for bots, and second family screen (descendant report etc.) -->
        <?php //if (!$botDetector->isBot() && (isset($descendant_loop) && $descendant_loop == 0) && count($relations) == 0) { 
        ?>
        <?php if (!$botDetector->isBot() && (isset($descendant_loop) && $descendant_loop == 0)) { ?>
            <?php
            $vars['pers_family'] = $data["family_id"];
            $settings_url = $processLinks->get_link($uri_path, 'family', $tree_id, true, $vars);
            $url_add = '';
            if ($data["main_person"]) {
                $settings_url .= "main_person=" . rawurlencode((string) $data["main_person"]);
                $url_add = '&amp;';
            }

            $desc_rep = '';
            if ($data["descendant_report"] == true) {
                $desc_rep = '&amp;descendant_report=1';
            }

            $encodeSettingsUrl = static function (string $url) use ($HtmlEscaper): string {
                return $HtmlEscaper->escHtml((string) json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
            };
            ?>

            <!-- Settings in pop-up screen -->
            <div class="dropdown dropend d-inline">
                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="images/settings.png" alt="<?= __('Settings'); ?>">
                </button>
                <ul class="dropdown-menu p-2" style="width:400px;">
                    <li>
                        <h4><?= __('Settings family screen'); ?></h4>
                    </li>

                    <li>
                        <!-- Compact / Expanded view buttons -->
                        <b><?= __('Family Page'); ?></b><br>
                        <div class="form-check">
                            <input type="radio" name="family_expanded<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="compact" class="form-check-input" id="fam_compact<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'family_expanded=compact' . $desc_rep); ?>" <?= $data["family_expanded"] == 'compact' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="fam_compact<?= $id_suffix; ?>"><?= __('Compact view'); ?></label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="family_expanded<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="expanded1" class="form-check-input" id="fam_expanded1<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'family_expanded=expanded1' . $desc_rep); ?>" <?= $data["family_expanded"] == 'expanded1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="fam_expanded1<?= $id_suffix; ?>"><?= __("Expanded view"); ?> 1</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="family_expanded<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="expanded2" class="form-check-input" id="fam_expanded2<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'family_expanded=expanded2' . $desc_rep); ?>" <?= $data["family_expanded"] == 'expanded2' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="fam_expanded2<?= $id_suffix; ?>"><?= __("Expanded view"); ?> 2</label>
                        </div>
                    </li>

                    <!-- Select source presentation (as title/ footnote or hide sources) -->
                    <?php if ($user['group_sources'] != 'n') { ?>
                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Sources'); ?></b><br>
                            <div class="form-check">
                                <input type="radio" name="source_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="title" class="form-check-input" id="src_title<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'source_presentation=title' . $desc_rep); ?>" <?= $data["source_presentation"] == 'title' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="src_title<?= $id_suffix; ?>"><?= __("Show source"); ?></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="source_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="footnote" class="form-check-input" id="src_footnote<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'source_presentation=footnote' . $desc_rep); ?>" <?= $data["source_presentation"] == 'footnote' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="src_footnote<?= $id_suffix; ?>"><?= __("Show source as footnote"); ?></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="source_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="hide" class="form-check-input" id="src_hide<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'source_presentation=hide' . $desc_rep); ?>" <?= $data["source_presentation"] == 'hide' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="src_hide<?= $id_suffix; ?>"><?= __("Hide sources"); ?></label>
                            </div>
                        </li>
                    <?php
                    }

                    // *** Show/ hide maps ***
                    if ($user["group_googlemaps"] == 'j' && $data["descendant_report"] == false) {
                    ?>
                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Family map'); ?></b><br>
                            <div class="form-check">
                                <input type="radio" name="maps_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="show" class="form-check-input" id="map_show<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'maps_presentation=show'); ?>" <?= $data["maps_presentation"] == 'show' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="map_show<?= $id_suffix; ?>"><?= __("Show family map"); ?></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="maps_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="hide" class="form-check-input" id="map_hide<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'maps_presentation=hide'); ?>" <?= $data["maps_presentation"] == 'hide' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="map_hide<?= $id_suffix; ?>"><?= __("Hide family map"); ?></label>
                            </div>
                        </li>
                    <?php } ?>

                    <?php if ($user['group_pictures'] == 'j') { ?>
                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Pictures'); ?></b><br>
                            <div class="form-check">
                                <input type="radio" name="picture_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="show" class="form-check-input" id="pic_show<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'picture_presentation=show' . $desc_rep); ?>" <?= $data["picture_presentation"] == 'show' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pic_show<?= $id_suffix; ?>"><?= __("Show pictures"); ?></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="picture_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="hide" class="form-check-input" id="pic_hide<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'picture_presentation=hide' . $desc_rep); ?>" <?= $data["picture_presentation"] == 'hide' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pic_hide<?= $id_suffix; ?>"><?= __("Hide pictures"); ?></label>
                            </div>
                        </li>
                    <?php } ?>

                    <li>&nbsp;</li>
                    <li>
                        <b><?= __('Texts'); ?></b><br>
                        <div class="form-check">
                            <input type="radio" name="text_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="show" class="form-check-input" id="txt_show<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'text_presentation=show' . $desc_rep); ?>" <?= $data["text_presentation"] == 'show' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="txt_show<?= $id_suffix; ?>"><?= __("Show texts"); ?></label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="text_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="popup" class="form-check-input" id="txt_popup<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'text_presentation=popup' . $desc_rep); ?>" <?= $data["text_presentation"] == 'popup' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="txt_popup<?= $id_suffix; ?>"><?= __("Show texts in popup screen"); ?></label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="text_presentation<?= $HtmlEscaper->escHtml($id_suffix); ?>" value="hide" class="form-check-input" id="txt_hide<?= $HtmlEscaper->escHtml($id_suffix); ?>" onclick="document.location.href=<?= $encodeSettingsUrl($settings_url . $url_add . 'text_presentation=hide' . $desc_rep); ?>" <?= $data["text_presentation"] == 'hide' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="txt_hide<?= $id_suffix; ?>"><?= __("Hide texts"); ?></label>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- PDF button -->
            <?php if ($user["group_pdf_button"] == 'y' && $language["dir"] != "rtl" && $language["name"] != "简体中文") { ?>
                <?php
                if ($humo_option["url_rewrite"] == "j") {
                    $link = $uri_path . 'family_pdf/' . $tree_id . '/' . $data["family_id"] . '?main_person=' . $data["main_person"];
                } else {
                    $link = $uri_path . 'index.php?page=family_pdf&amp;tree_id=' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;main_person=' . $data["main_person"];
                }
                if ($data["descendant_report"] == true) {
                    $link .= '&amp;descendant_report=1';
                }
                ?>
                &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $HtmlEscaper->escHtml($link); ?>" style="display:inline-block; vertical-align:middle;">
                    <input type="Submit" class="btn btn-sm btn-info" name="submit" value="<?= __('PDF'); ?>">
                </form>
            <?php
            }

            // *** RTF button ***
            if ($user["group_rtf_button"] == 'y' && $language["dir"] != "rtl") {
                // TODO add tree_id, id etc. in links?
                if ($humo_option["url_rewrite"] == "j") {
                    $link = $uri_path . 'family_rtf';
                } else {
                    $link = $uri_path . 'index.php?page=family_rtf';
                }
            ?>
                &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $HtmlEscaper->escHtml($link); ?>" style="display:inline-block; vertical-align:middle;">
                    <input type="hidden" name="tree_id" value="<?= $HtmlEscaper->escHtml((string) $tree_id); ?>">
                    <input type="hidden" name="id" value="<?= $HtmlEscaper->escHtml((string) $data["family_id"]); ?>">
                    <input type="hidden" name="main_person" value="<?= $HtmlEscaper->escHtml((string) $data["main_person"]); ?>">
                    <input type="hidden" name="screen_mode" value="RTF">
                    <?php if ($data["descendant_report"] == true) { ?>
                        <input type="hidden" name="descendant_report" value="<?= $data["descendant_report"]; ?>">
                    <?php } ?>
                    <input type="Submit" class="btn btn-sm btn-info" name="submit" value="<?= __('RTF'); ?>">
                </form>
            <?php
            }

            // *** Add family to favourite list ***
            // If there is a N.N. father, then use mother in favourite icon.
            if (!isset($parent1Db->pers_gedcomnumber)) {
                $privacy = $personPrivacy->get_privacy($parent2Db);
                $name = $personName->get_person_name($parent2Db, $privacy);
                $favorite_gedcomnumber = $parent2Db->pers_gedcomnumber;
            } else {
                $privacy = $personPrivacy->get_privacy($parent1Db);
                $name = $personName->get_person_name($parent1Db, $privacy);
                $favorite_gedcomnumber = $parent1Db->pers_gedcomnumber;
            }

            if ($name) {
                // *** New cookies only need 3 variables ***
                $favorite_value = $tree_id . '|' . $data["family_id"] . '|' . $favorite_gedcomnumber;
                $check = false;
                if (isset($_SESSION['save_favorites'])) {
                    foreach ($_SESSION['save_favorites'] as $key => $value) {
                        if ($value == $favorite_value) {
                            $check = true;
                        }
                    }
                }

                $vars['pers_family'] = $data["family_id"];
                $link = $processLinks->get_link($uri_path, 'family', $tree_id, true, $vars);
                $link .= "main_person=" . rawurlencode((string) $data["main_person"]);
            ?>
                &nbsp;&nbsp;&nbsp;
                <form method="POST" action="<?= $HtmlEscaper->escHtml($link); ?>" style="display:inline-block; vertical-align:middle;">
                    <?php
                    if ($data["descendant_report"] == true) {
                        echo '<input type="hidden" name="descendant_report" value="1">';
                    }
                    if ($check == false) {
                        echo '<input type="hidden" name="favorite" value="' . $HtmlEscaper->escHtml($favorite_value) . '">';
                        echo ' <input type="image" src="images/favorite.png" name="favorite_button" alt="' . __('Add to favourite list') . '">';
                    } else {
                        echo '<input type="hidden" name="favorite_remove" value="' . $HtmlEscaper->escHtml($favorite_value) . '">';
                        echo ' <input type="image" src="images/favorite_blue.png" name="favorite_button" alt="' . __('Remove from favourite list') . '">';
                    }
                    ?>
                </form>
        <?php
            }
        } // End of bot visit
        ?>
    </td>
</tr>
