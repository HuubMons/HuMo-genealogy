<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<form method="post" action="index.php?page=settings&menu_admin=settings_homepage" class="p-2">
    <ul class="list-group">
        <li class="list-group-item">
            <?= __('Homepage template'); ?>
            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;template_homepage_reset=1">[<?= __('Default settings'); ?>]</a><br>
            <?= __("If the left column isn't used, the center column will be made large automatically."); ?>
        </li>
    </ul>

    <ul class="list-group">
        <li class="list-group-item">
            <div class="row bg-primary-subtle p-3 mt-2">
                <div class="col-md-1">
                    <b><?= __('Status'); ?></b>
                </div>
                <div class="col-md-1">
                    <b><?= __('Position'); ?></b>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-3">
                    <b><?= __('Item'); ?></b>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-1">
                    <input type="submit" name="change_module" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
                </div>
            </div>
        </li>
    </ul>

    <ul id="sortable_trees" class="sortable_trees list-group">
        <?php $position = 'left'; ?>
        <?php for ($i = 0; $i <= $settings['nr_modules']; $i++) { ?>
            <?php
            $enable_drag = true;
            if ($settings['module_position'][$i] == 'left' && $settings['modules_left'] == 1) {
                $enable_drag = false;
            } elseif ($settings['module_position'][$i] == 'center' && $settings['modules_center'] == 1) {
                $enable_drag = false;
            } elseif ($settings['module_position'][$i] == 'right' && $settings['modules_right'] == 1) {
                $enable_drag = false;
            }

            if ($settings['module_position'][$i] != $position) {
                // *** Add empty line between modules ***
                $position = $settings['module_position'][$i];
            ?>
                <li class="list-group-item"><br></li>
            <?php } ?>
            <li class="list-group-item">
                <div class="row">
                    <!-- Active/ inactive with background colour -->
                    <div class="col-md-1 <?= $settings['module_active'][$i] == 'inactive' ? 'bg-info' : ''; ?>">
                        <input type="hidden" name="<?= $settings['module_setting_id'][$i]; ?>id" value="<?= $settings['module_setting_id'][$i]; ?>">
                        <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_status" class="form-select form-select-sm">
                            <option value="active"><?= __('Active'); ?></option>
                            <option value="inactive" <?php if ($settings['module_active'][$i] == 'inactive') echo ' selected'; ?>><?= __('Inactive'); ?></option>
                        </select>
                    </div>

                    <!-- TODO use seperate blocks for editing left/center/right items -->
                    <div class="col-md-1">
                        <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_column" class="form-select form-select-sm">
                            <option value="left"><?= __('Left'); ?></option>
                            <option value="center" <?php if ($settings['module_position'][$i] == 'center') echo ' selected'; ?>><?= __('Center'); ?></option>
                            <option value="right" <?php if ($settings['module_position'][$i] == 'right') echo ' selected'; ?>><?= __('Right'); ?></option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <?php if ($enable_drag) { ?>
                            <span style="cursor:move;" id="<?= $settings['module_setting_id'][$i]; ?>" class="handle me-4">
                                <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                            </span>
                        <?php } ?>

                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module=<?= $settings['module_setting_id'][$i]; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove">
                        </a>
                    </div>

                    <div class="col-md-3">
                        <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_item" class="form-select form-select-sm">
                            <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                            <option value="selected_family_tree" <?php if ($settings['module_item'][$i] == 'selected_family_tree') echo ' selected'; ?>><?= __('Selected family tree'); ?></option>
                            <option value="search" <?php if ($settings['module_item'][$i] == 'search') echo ' selected'; ?>><?= __('Search'); ?></option>
                            <option value="names" <?php if ($settings['module_item'][$i] == 'names') echo ' selected'; ?>><?= __('Names'); ?></option>
                            <option value="history" <?php if ($settings['module_item'][$i] == 'history') echo ' selected'; ?>><?= __('Today in history'); ?></option>
                            <option value="favourites" <?php if ($settings['module_item'][$i] == 'favourites') echo ' selected'; ?>><?= __('Favourites'); ?></option>
                            <option value="alphabet" <?php if ($settings['module_item'][$i] == 'alphabet') echo ' selected'; ?>><?= __('Surnames Index'); ?></option>
                            <option value="random_photo" <?php if ($settings['module_item'][$i] == 'random_photo') echo ' selected'; ?>><?= __('Random photo'); ?></option>
                            <option value="text" <?php if ($settings['module_item'][$i] == 'text') echo ' selected'; ?>><?= __('Text'); ?></option>
                            <option value="own_script" <?php if ($settings['module_item'][$i] == 'own_script') echo ' selected'; ?>><?= __('Own script'); ?></option>
                            <option value="cms_page" <?php if ($settings['module_item'][$i] == 'cms_page') echo ' selected'; ?>><?= __('CMS Own pages'); ?></option>
                            <option value="empty_line" <?php if ($settings['module_item'][$i] == 'empty_line') echo ' selected'; ?>><?= __('EMPTY LINE'); ?></option>
                        </select>
                    </div>

                    <!-- Extra table column used for extra options -->
                    <div class="col-md-auto">
                        <?php if ($settings['module_item'][$i] === 'names') { ?>
                            <?= __('Columns'); ?>
                            <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_option_1">
                                <option value="1">1</option>
                                <option value="2" <?= $settings['module_option_1'][$i] === '2' ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?= $settings['module_option_1'][$i] === '3' ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?= $settings['module_option_1'][$i] === '4' ? 'selected' : ''; ?>>4</option>
                            </select>

                            <?= __('Rows'); ?>
                            <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_option_2">
                                <option value="1">1</option>
                                <option value="2" <?= $settings['module_option_2'][$i] === '2' ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?= $settings['module_option_2'][$i] === '3' ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?= $settings['module_option_2'][$i] === '4' ? 'selected' : ''; ?>>4</option>
                                <option value="5" <?= $settings['module_option_2'][$i] === '5' ? 'selected' : ''; ?>>5</option>
                                <option value="6" <?= $settings['module_option_2'][$i] === '6' ? 'selected' : ''; ?>>6</option>
                                <option value="7" <?= $settings['module_option_2'][$i] === '7' ? 'selected' : ''; ?>>7</option>
                                <option value="8" <?= $settings['module_option_2'][$i] === '8' ? 'selected' : ''; ?>>8</option>
                                <option value="9" <?= $settings['module_option_2'][$i] === '9' ? 'selected' : ''; ?>>9</option>
                                <option value="10" <?= $settings['module_option_2'][$i] === '10' ? 'selected' : ''; ?>>10</option>
                                <option value="11" <?= $settings['module_option_2'][$i] === '11' ? 'selected' : ''; ?>>11</option>
                                <option value="12" <?= $settings['module_option_2'][$i] === '12' ? 'selected' : ''; ?>>12</option>
                            </select>
                        <?php
                        }

                        if ($settings['module_item'][$i] === 'text') {
                        ?>
                            <!-- Header text -->
                            <input type="text" placeholder="<?= __('Header'); ?>" name="<?= $settings['module_setting_id'][$i]; ?>module_option_1" value="<?= $settings['module_option_1'][$i]; ?>" size="30"><br>
                            <textarea rows="4" cols="50" placeholder="<?= __('Text'); ?>" name="<?= $settings['module_setting_id'][$i]; ?>module_option_2"><?= $settings['module_option_2'][$i]; ?></textarea><br>
                            <?= __('Show text block, HTML codes can be used.'); ?>
                        <?php
                        }

                        if ($settings['module_item'][$i] === 'own_script') {
                        ?>
                            <!-- Header text -->
                            <input type="text" placeholder="<?= __('Header'); ?>" name="<?= $settings['module_setting_id'][$i]; ?>module_option_1" value="<?= $settings['module_option_1'][$i]; ?>" size="30"><br>
                            <input type="text" placeholder="<?= __('File name'); ?>" name="<?= $settings['module_setting_id'][$i]; ?>module_option_2" value="<?= $settings['module_option_2'][$i]; ?>" size="30"><br>
                            <?= __('File name (full path) of the file with own script.'); ?>
                        <?php
                        }

                        if ($settings['module_item'][$i] === 'cms_page') {
                            $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                        ?>
                            <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_option_1">
                                <?php while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) { ?>
                                    <option value="<?= $pageDb->page_id; ?>" <?= $settings['module_option_1'][$i] == $pageDb->page_id ? 'selected' : ''; ?>><?= $pageDb->page_title; ?></option>
                                <?php } ?>
                            </select>
                            <?= __('Show text from CMS system.'); ?>
                        <?php
                        }

                        if ($settings['module_item'][$i] === 'history') {
                        ?>
                            <?= __('View'); ?>
                            <select size="1" name="<?= $settings['module_setting_id'][$i]; ?>module_option_1">
                                <option value="with_table"><?= __('with table'); ?></option>
                                <option value="without_table" <?= $settings['module_option_1'][$i] === 'without_table' ? 'selected' : ''; ?>><?= __('without table'); ?></option>
                            </select>
                        <?php } ?>
                    </div>
                </div>
            </li>
        <?php } ?>

        <li class="list-group-item"><br></li>

        <!-- Add new module -->
        <li class="list-group-item">
            <div class="row">
                <!-- Active/ inactive with background colour -->
                <div class="col-md-1">
                    <input type="hidden" name="module_order" value="<?= $settings['nr_modules'] + 1; ?>">
                    <select size="1" name="module_status" class="form-select form-select-sm">
                        <option value="active"><?= __('Active'); ?></option>
                        <option value="inactive"><?= __('Inactive'); ?></option>
                    </select>
                </div>

                <div class="col-md-1">
                    <select size="1" name="module_column" class="form-select form-select-sm">
                        <option value="left"><?= __('Left'); ?></option>
                        <option value="center"><?= __('Center'); ?></option>
                        <option value="right"><?= __('Right'); ?></option>
                    </select>
                </div>

                <div class="col-md-1"></div>

                <div class="col-md-3">
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
                </div>

                <div class="col-md-1"></div>

                <div class="col-md-1">
                    <input type="submit" name="add_module" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary">
                </div>
            </div>
        </li>

    </ul>
</form>

<!-- Order items using drag and drop using jquery and jqueryui -->
<script>
    $('#sortable_trees').sortable({
        handle: '.handle'
    }).bind('sortupdate', function() {
        var orderstring = "";
        var order_arr = document.getElementsByClassName("handle");
        for (var z = 0; z < order_arr.length; z++) {
            orderstring = orderstring + order_arr[z].id + ";";
            //document.getElementById('ordernum' + order_arr[z].id).innerHTML = (z + 1);
        }

        orderstring = orderstring.substring(0, orderstring.length - 1);
        $.ajax({
            url: "include/drag.php?drag_kind=homepage_modules&order=" + orderstring,
            success: function(data) {},
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
</script>


<!-- Edit homepage favorites -->
<form method="post" action="index.php?page=settings&menu_admin=settings_homepage">

    <table class="table table-light mt-3">
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
        $LinkQry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' ORDER BY setting_order");
        // *** Number for new link ***
        $count_links = 0;
        if ($LinkQry->rowCount()) {
            $count_links = $LinkQry->rowCount();
        }
        $new_number = 1;
        if ($count_links) {
            $new_number = $count_links + 1;
        }
        if ($LinkQry) {
            $teller = 1;
            while ($link = $LinkQry->fetch(PDO::FETCH_OBJ)) {
                $lijst = explode("|", $link->setting_value);
        ?>
                <tr>
                    <td>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_link=<?= $link->setting_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove">
                        </a>

                        <input type="hidden" name="<?= $link->setting_id; ?>id" value="<?= $link->setting_id; ?>"><?= __('Link') . ' ' . $teller; ?>
                        <?php if ($link->setting_order != '1') { ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;up=1&amp;link_order=<?= $link->setting_order; ?>&amp;id=<?= $link->setting_id; ?>">
                                <img src="images/arrow_up.gif" border="0" alt="up">
                            </a>
                        <?php
                        }

                        if ($link->setting_order != $count_links) {
                        ?>
                            <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;down=1&amp;link_order=<?= $link->setting_order; ?>&amp;id=<?= $link->setting_id; ?>">
                                <img src="images/arrow_down.gif" border="0" alt="down">
                            </a>
                        <?php } ?>
                    </td>
                    <td><input type="text" name="<?= $link->setting_id; ?>own_code" value="<?= $lijst[0]; ?>" size="5" class="form-control form-control-sm"></td>
                    <td><input type="text" name="<?= $link->setting_id; ?>link_text" value="<?= $lijst[1]; ?>" size="20" class="form-control form-control-sm"></td>
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
<form method="post" action="index.php?page=settings&menu_admin=settings_homepage">
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