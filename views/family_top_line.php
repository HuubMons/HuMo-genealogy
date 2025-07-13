<?php $treetext = $showTreeText->show_tree_text($dataDb->tree_id, $selected_language); ?>

<tr class="table_headline">
    <td class="table_header">
        <div class="family_page_toptext"><?= $treetext['family_top']; ?><br></div>
    </td>

    <td class="table_header" width="220" style="text-align:right;">
        <!-- Hide selections for bots, and second family screen (descendant report etc.) -->
        <?php if (!$bot_visit && (isset($descendant_loop) && $descendant_loop == 0) && $parent1_marr == 0) { ?>
            <?php
            $vars['pers_family'] = $data["family_id"];
            $settings_url = $processLinks->get_link($uri_path, 'family', $tree_id, true, $vars);
            $url_add = '';
            if ($data["main_person"]) {
                $settings_url .= "main_person=" . $data["main_person"];
                $url_add = '&amp;';
            }

            $desc_rep = '';
            if ($data["descendant_report"] == true) {
                $desc_rep = '&amp;descendant_report=1';
            }
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
                        <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=compact<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'compact' ? 'checked' : ''; ?>> <?= __('Compact view'); ?><br>
                        <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=expanded1<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'expanded1' ? 'checked' : ''; ?>> <?= __('Expanded view'); ?> 1<br>
                        <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=expanded2<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'expanded2' ? 'checked' : ''; ?>> <?= __('Expanded view'); ?> 2<br>
                    </li>

                    <!-- Select source presentation (as title/ footnote or hide sources) -->
                    <?php if ($user['group_sources'] != 'n') { ?>
                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Sources'); ?></b><br>
                            <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=title<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'title' ? 'checked' : ''; ?>> <?= __('Show source'); ?><br>
                            <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=footnote<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'footnote' ? 'checked' : ''; ?>> <?= __('Show source as footnote'); ?><br>
                            <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide sources'); ?><br>
                        </li>
                    <?php
                    }

                    // *** Show/ hide maps ***
                    if ($user["group_googlemaps"] == 'j' && $data["descendant_report"] == false) {
                    ?>
                        <li>&nbsp;</li>
                        <li>
                            <?php
                            // TODO: maybe count valid locations in table.
                            // *** Only show selection if there is a location database ***
                            //$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                            //if ($temp->rowCount()) {
                            ?>
                            <b><?= __('Family map'); ?></b><br>
                            <input type="radio" name="keuze2" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>maps_presentation=show&xx='+this.value" <?= $data["maps_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show family map'); ?><br>
                            <input type="radio" name="keuze2" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>maps_presentation=hide&xx='+this.value" <?= $data["maps_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide family map'); ?><br>
                            <?php
                            //}
                            ?>
                        </li>
                    <?php } ?>

                    <?php if ($user['group_pictures'] == 'j') { ?>
                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Pictures'); ?></b><br>
                            <input type="radio" name="keuze3" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>picture_presentation=show<?= $desc_rep; ?>&xx='+this.value" <?= $data["picture_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show pictures'); ?><br>
                            <input type="radio" name="keuze3" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>picture_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["picture_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide pictures'); ?><br>
                        </li>
                    <?php } ?>

                    <li>&nbsp;</li>
                    <li>
                        <b><?= __('Texts'); ?></b><br>
                        <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=show<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show texts'); ?><br>
                        <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=popup<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'popup' ? 'checked' : ''; ?>> <?= __('Show texts in popup screen'); ?><br>
                        <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide texts'); ?><br>
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
                &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;">
                    <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('PDF'); ?>">
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
                &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                    <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                    <input type="hidden" name="screen_mode" value="RTF">
                    <?php if ($data["descendant_report"] == true) { ?>
                        <input type="hidden" name="descendant_report" value="<?= $data["descendant_report"]; ?>">
                    <?php } ?>
                    <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('RTF'); ?>">
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
                $link .= "main_person=" . $data["main_person"];
            ?>
                &nbsp;&nbsp;&nbsp;
                <form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;">
                    <?php
                    if ($data["descendant_report"] == true) {
                        echo '<input type="hidden" name="descendant_report" value="1">';
                    }
                    if ($check == false) {
                        echo '<input type="hidden" name="favorite" value="' . $favorite_value . '">';
                        echo ' <input type="image" src="images/favorite.png" name="favorite_button" alt="' . __('Add to favourite list') . '">';
                    } else {
                        echo '<input type="hidden" name="favorite_remove" value="' . $favorite_value . '">';
                        echo ' <input type="image" src="images/favorite_blue.png" name="favorite_button" alt="' . __('Add to favourite list') . '">';
                    }
                    ?>
                </form>
        <?php
            }
        } // End of bot visit
        ?>
    </td>
</tr>