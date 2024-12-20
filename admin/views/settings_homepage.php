<form method="post" action="index.php" class="p-2">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">

    <table class="table table-light">
        <thead class="table-primary">
            <tr>
                <th colspan="6"><?= __('Homepage template'); ?>
                    <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;template_homepage_reset=1">[<?= __('Default settings'); ?>]</a><br>
                    <?= __("If the left column isn't used, the center column will be made large automatically."); ?><br>
                </th>
            </tr>
        </thead>

        <tr class="table-primary">
            <th><?= __('Status'); ?></th>
            <th><?= __('Position'); ?></th>
            <th></th>
            <th><?= __('Item'); ?></th>
            <th><br></th>
            <th><input type="submit" name="change_module" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <?php
        $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
        // *** Number for new module ***
        $count_links = 0;
        if ($datasql->rowCount()) {
            $count_links = $datasql->rowCount();
        }
        $new_number = 1;
        if ($count_links) {
            $new_number = $count_links + 1;
        }
        if ($datasql) {
            $teller = 1;
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $dataDb->setting_value .= '|'; // In some cases the last | is missing. TODO: improve saving of settings.
                $lijst = explode("|", $dataDb->setting_value);
                // *** Just to prevent error messages, set a default value ***
                if (!isset($lijst[3])) {
                    $lijst[3] = '';
                }
                if (!isset($lijst[4])) {
                    $lijst[3] = '';
                }
        ?>
                <tr>
                    <!-- Active/ inactive with background colour -->
                    <td <?= $lijst[0] == 'inactive' ? 'class="table-warning"' : ''; ?>>
                        <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>">
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_status" class="form-select form-select-sm">
                            <option value="active"><?= __('Active'); ?></option>
                            <option value="inactive" <?php if ($lijst[0] == 'inactive') echo ' selected'; ?>><?= __('Inactive'); ?></option>
                        </select>
                    </td>

                    <!-- TODO use seperate blocks for editing left/center/right items -->
                    <td>
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_column" class="form-select form-select-sm">
                            <option value="left"><?= __('Left'); ?></option>
                            <option value="center" <?php if ($lijst[1] == 'center') echo ' selected'; ?>><?= __('Center'); ?></option>
                            <option value="right" <?php if ($lijst[1] == 'right') echo ' selected'; ?>><?= __('Right'); ?></option>
                        </select>
                    </td>

                    <td>
                        <?php if ($dataDb->setting_order != '1') { ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_up=1&amp;module_order=<?= $dataDb->setting_order; ?>&amp;id=<?= $dataDb->setting_id; ?>">
                                <img src="images/arrow_up.gif" border="0" alt="up">
                            </a>
                        <?php } else { ?>
                            &nbsp;&nbsp;&nbsp;
                        <?php
                        }

                        if ($dataDb->setting_order != $count_links) { ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_down=1&amp;module_order=<?= $dataDb->setting_order; ?>&amp;id=<?= $dataDb->setting_id; ?>">
                                <img src="images/arrow_down.gif" border="0" alt="down">
                            </a>
                        <?php } ?>
                    </td>

                    <td>
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_item" class="form-select form-select-sm">
                            <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                            <option value="selected_family_tree" <?php if ($lijst[2] == 'selected_family_tree') echo ' selected'; ?>><?= __('Selected family tree'); ?></option>
                            <option value="search" <?php if ($lijst[2] == 'search') echo ' selected'; ?>><?= __('Search'); ?></option>
                            <option value="names" <?php if ($lijst[2] == 'names') echo ' selected'; ?>><?= __('Names'); ?></option>
                            <option value="history" <?php if ($lijst[2] == 'history') echo ' selected'; ?>><?= __('Today in history'); ?></option>
                            <option value="favourites" <?php if ($lijst[2] == 'favourites') echo ' selected'; ?>><?= __('Favourites'); ?></option>
                            <option value="alphabet" <?php if ($lijst[2] == 'alphabet') echo ' selected'; ?>><?= __('Surnames Index'); ?></option>
                            <option value="random_photo" <?php if ($lijst[2] == 'random_photo') echo ' selected'; ?>><?= __('Random photo'); ?></option>
                            <option value="text" <?php if ($lijst[2] == 'text') echo ' selected'; ?>><?= __('Text'); ?></option>
                            <option value="own_script" <?php if ($lijst[2] == 'own_script') echo ' selected'; ?>><?= __('Own script'); ?></option>
                            <option value="cms_page" <?php if ($lijst[2] == 'cms_page') echo ' selected'; ?>><?= __('CMS Own pages'); ?></option>
                            <option value="empty_line" <?php if ($lijst[2] == 'empty_line') echo ' selected'; ?>><?= __('EMPTY LINE'); ?></option>
                        </select>
                    </td>

                    <!-- Extra table column used for extra options -->
                    <td>
                        <?php if ($lijst[2] === 'names') { ?>
                            <?= __('Columns'); ?>
                            <select size="1" name="<?= $dataDb->setting_id; ?>module_option_1">
                                <option value="1">1</option>
                                <option value="2" <?= $lijst[3] === '2' ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?= $lijst[3] === '3' ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?= $lijst[3] === '4' ? 'selected' : ''; ?>>4</option>
                            </select>

                            <?= __('Rows'); ?>
                            <select size="1" name="<?= $dataDb->setting_id; ?>module_option_2">
                                <option value="1">1</option>
                                <option value="2" <?= $lijst[4] === '2' ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?= $lijst[4] === '3' ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?= $lijst[4] === '4' ? 'selected' : ''; ?>>4</option>
                                <option value="5" <?= $lijst[4] === '5' ? 'selected' : ''; ?>>5</option>
                                <option value="6" <?= $lijst[4] === '6' ? 'selected' : ''; ?>>6</option>
                                <option value="7" <?= $lijst[4] === '7' ? 'selected' : ''; ?>>7</option>
                                <option value="8" <?= $lijst[4] === '8' ? 'selected' : ''; ?>>8</option>
                                <option value="9" <?= $lijst[4] === '9' ? 'selected' : ''; ?>>9</option>
                                <option value="10" <?= $lijst[4] === '10' ? 'selected' : ''; ?>>10</option>
                                <option value="11" <?= $lijst[4] === '11' ? 'selected' : ''; ?>>11</option>
                                <option value="12" <?= $lijst[4] === '12' ? 'selected' : ''; ?>>12</option>
                            </select>
                        <?php
                        }

                        if ($lijst[2] === 'text') {
                        ?>
                            <!-- Header text -->
                            <input type="text" placeholder="<?= __('Header'); ?>" name="<?= $dataDb->setting_id; ?>module_option_1" value="<?= isset($lijst[3]) ? $lijst[3] : ''; ?>" size="30"><br>
                            <textarea rows="4" cols="50" placeholder="<?= __('Text'); ?>" name="<?= $dataDb->setting_id; ?>module_option_2"><?= isset($lijst[4]) ? $lijst[4] : ''; ?></textarea><br>
                            <?= __('Show text block, HTML codes can be used.'); ?>
                        <?php
                        }

                        if ($lijst[2] === 'own_script') {
                        ?>
                            <!-- Header text -->
                            <input type="text" placeholder="<?= __('Header'); ?>" name="<?= $dataDb->setting_id; ?>module_option_1" value="<?= isset($lijst[3]) ? $lijst[3] : ''; ?>" size="30"><br>
                            <input type="text" placeholder="<?= __('File name'); ?>" name="<?= $dataDb->setting_id; ?>module_option_2" value="<?= isset($lijst[4]) ? $lijst[4] : ''; ?>" size="30"><br>
                            <?= __('File name (full path) of the file with own script.'); ?>
                        <?php
                        }

                        if ($lijst[2] === 'cms_page') {
                            $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                        ?>
                            <select size="1" name="<?= $dataDb->setting_id; ?>module_option_1">
                                <?php while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) { ?>
                                    <option value="<?= $pageDb->page_id; ?>" <?= $lijst[3] == $pageDb->page_id ? 'selected' : ''; ?>><?= $pageDb->page_title; ?></option>
                                <?php } ?>
                            </select>
                            <?= __('Show text from CMS system.'); ?>
                        <?php
                        }

                        if ($lijst[2] === 'history') {
                        ?>
                            <?= __('View'); ?>
                            <select size="1" name="<?= $dataDb->setting_id; ?>module_option_1">
                                <option value="with_table"><?= __('with table'); ?></option>
                                <option value="without_table" <?= $lijst[3] === 'without_table' ? 'selected' : ''; ?>><?= __('without table'); ?></option>
                            </select>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module=<?= $dataDb->setting_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove">
                        </a>
                    </td>
                </tr>
            <?php
                $teller++;
            }
            ?>

            <!-- Add new module -->
            <tr class="table-secondary">
                <input type="hidden" name="module_order" value="<?= $new_number; ?>">
                <td>
                    <select size="1" name="module_status" class="form-select form-select-sm">
                        <option value="active"><?= __('Active'); ?></option>
                        <option value="inactive"><?= __('Inactive'); ?></option>
                    </select>
                </td>

                <td>
                    <select size="1" name="module_column" class="form-select form-select-sm">
                        <option value="left"><?= __('Left'); ?></option>
                        <option value="center"><?= __('Center'); ?></option>
                        <option value="right"><?= __('Right'); ?></option>
                    </select>
                </td>

                <td></td>

                <td>
                    <select size="1" name="module_item" class="form-select form-select-sm">
                        <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                        <option value="selected_family_tree"><?= __('Selected family tree'); ?></option>
                        <option value="search"><?= __('Search'); ?></option>
                        <option value="names"><?= __('Names'); ?></option>
                        <option value="history"><?= __('Today in history'); ?></option>
                        <option value="favourites"><?= __('Favourites'); ?></option>
                        <option value="alphabet"><?= __('Surnames Index'); ?></option>
                        <option value="random_photo"><?= __('Random photo'); ?></option>
                        <option value="text"><?= __('Text'); ?></option>
                        <option value="own_script"><?= __('Own script'); ?></option>
                        <option value="cms_page"><?= __('CMS Own pages'); ?></option>
                        <option value="empty_line"><?= __('EMPTY LINE'); ?></option>
                    </select>
                </td>

                <td><br></td>

                <td><input type="submit" name="add_module" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary"></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="4"><?= __('Database is not yet available.'); ?></td>
            </tr>
        <?php } ?>
    </table>
</form>

<!-- Edit homepage favorites -->
<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">

    <table class="table table-light">
        <thead class="table-primary">
            <tr>
                <th colspan="4"><?= __('Show list of favourites in homepage'); ?></th>
            </tr>

            <tr>
                <th>Nr.</th>
                <th><?= __('Own code'); ?></th>
                <th><?= __('Description'); ?></th>
                <th><input type="submit" name="change_link" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
            </tr>
        </thead>

        <?php
        $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' ORDER BY setting_order");
        // *** Number for new link ***
        $count_links = 0;
        if ($datasql->rowCount()) {
            $count_links = $datasql->rowCount();
        }
        $new_number = 1;
        if ($count_links) {
            $new_number = $count_links + 1;
        }
        if ($datasql) {
            $teller = 1;
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $lijst = explode("|", $dataDb->setting_value);
        ?>
                <tr>
                    <td>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_link=<?= $dataDb->setting_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove">
                        </a>

                        <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>"><?= __('Link') . ' ' . $teller; ?>
                        <?php if ($dataDb->setting_order != '1') { ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;up=1&amp;link_order=<?= $dataDb->setting_order; ?>&amp;id=<?= $dataDb->setting_id; ?>">
                                <img src="images/arrow_up.gif" border="0" alt="up">
                            </a>
                        <?php
                        }

                        if ($dataDb->setting_order != $count_links) {
                        ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;down=1&amp;link_order=<?= $dataDb->setting_order; ?>&amp;id=<?= $dataDb->setting_id; ?>">
                                <img src="images/arrow_down.gif" border="0" alt="down">
                            </a>
                        <?php } ?>
                    </td>
                    <td><input type="text" name="<?= $dataDb->setting_id; ?>own_code" value="<?= $lijst[0]; ?>" size="5" class="form-control form-control-sm"></td>
                    <td><input type="text" name="<?= $dataDb->setting_id; ?>link_text" value="<?= $lijst[1]; ?>" size="20" class="form-control form-control-sm"></td>
                    <td><br></td>
                </tr>
            <?php
                $teller++;
            }
            ?>

            <!-- Add new link -->
            <tr class="table-secondary">
                <td><br></td>
                <input type="hidden" name="link_order" value="<?= $new_number; ?>">
                <td><input type="text" name="own_code" value="Code" size="5" class="form-control form-control-sm"></td>
                <td><input type="text" name="link_text" value="<?= __('Owner of tree'); ?>" size="20" class="form-control form-control-sm"></td>
                <td><input type="submit" name="add_link" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary"></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="4"><?= __('Database is not yet available.'); ?></td>
            </tr>
        <?php } ?>
    </table>
</form>

<?= __('Own code is the code that has to be entered in your genealogy program under "own code or REFN"
<p>Do the following:<br>
1) In your genealogy program, put a code. For example, with the patriarch enter a code "patriarch".<br>
2) Enter the same code in this table (multiple codes are possible)<br>
3) After processing the GEDCOM file, an extra link will appear in the main menu, i.e. to the patriarch!<br>'); ?>

<?php
// *** Slideshow ***
$slideshow_01 = explode('|', $humo_option["slideshow_01"]);
$slideshow_02 = explode('|', $humo_option["slideshow_02"]);
$slideshow_03 = explode('|', $humo_option["slideshow_03"]);
$slideshow_04 = explode('|', $humo_option["slideshow_04"]);
?>
<br>
<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">

    <table class="table table-light">
        <thead class="table-primary">
            <tr>
                <th colspan="2"><?= __('Slideshow on the homepage'); ?> <input type="submit" name="save_option2" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
            </tr>
        </thead>

        <tr>
            <td colspan="2"><?= __('This option shows a slideshow at the homepage. Put the images in the media/slideshow/ folder at the website.<br>Example of image link:'); ?> <b>media/slideshow/slide01.jpg</b><br>
                <?= __('Images size should be about:'); ?> <b>950 x 170 pixels.</b>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Show slideshow on the homepage'); ?>?</td>
            <td>
                <select size="1" name="slideshow_show" class="form-select form-select-sm w-25">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["slideshow_show"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </td>
        </tr>

        <!-- Picture 1 -->
        <tr>
            <td><?= __('Link to image'); ?> 1<br><?= __('Link description'); ?> 1</td>
            <td><input type="text" name="slideshow_slide_01" value="<?= $slideshow_01[0]; ?>" size="40"> media/slideshow/slide01.jpg<br>
                <input type="text" name="slideshow_text_01" value="<?= $slideshow_01[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 2 -->
        <tr>
            <td><?= __('Link to image'); ?> 2<br><?= __('Link description'); ?> 2</td>
            <td><input type="text" name="slideshow_slide_02" value="<?= $slideshow_02[0]; ?>" size="40"> media/slideshow/slide02.jpg<br>
                <input type="text" name="slideshow_text_02" value="<?= $slideshow_02[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 3 -->
        <tr>
            <td><?= __('Link to image'); ?> 3<br><?= __('Link description'); ?> 3</td>
            <td><input type="text" name="slideshow_slide_03" value="<?= $slideshow_03[0]; ?>" size="40"> media/slideshow/slide03.jpg<br>
                <input type="text" name="slideshow_text_03" value="<?= $slideshow_03[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 4 -->
        <tr>
            <td><?= __('Link to image'); ?> 4<br><?= __('Link description'); ?> 4</td>
            <td><input type="text" name="slideshow_slide_04" value="<?= $slideshow_04[0]; ?>" size="40"> media/slideshow/slide04.jpg<br>
                <input type="text" name="slideshow_text_04" value="<?= $slideshow_04[1]; ?>" size="40">
            </td>
        </tr>

    </table>
</form><br><br>