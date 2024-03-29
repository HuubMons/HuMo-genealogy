<form method="post" action="<?= $phpself; ?>" style="display : inline;">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
    <input type="hidden" name="menu_admin" value="<?= $menu_admin; ?>">
    <input type="hidden" name="language_tree" value="<?= $language_tree; ?>">
    <?php
    if (isset($treetext_id)) {
        echo '<input type="hidden" name="treetext_id" value="' . $treetext_id . '">';
    }
    ?>

    <br>
    <table class="humo" cellspacing="0" width="100%">
        <tr class="table_header">
            <th colspan="2"><?= __('Family tree texts (per language)'); ?></th>
        </tr>

        <tr>
            <td colspan="2">
                <?= __('Here you can add some overall texts for EVERY family tree (and for  EVERY LANGUAGE!).<br>Select language, and change text'); ?><br>
                <?= __('Add "Default" (e.g. english) texts  for all languages, and/ or select a language to add texts for that specific language'); ?>:<br>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Language'); ?></td>
            <td>
                <a href="index.php?page=tree&amp;menu_admin=tree_text&amp;language_tree=default&amp;tree_id=<?= $tree_id; ?>"><?= __('Default'); ?></a>
                <?php
                // *** Language choice ***
                $language_tree2 = $language_tree;
                if ($language_tree == 'default') $language_tree2 = $selected_language;
                echo '&nbsp;&nbsp;&nbsp;<div class="ltrsddm" style="display : inline;">';
                echo '<a href="index.php?option=com_humo-gen"';
                include(__DIR__ . '/../../languages/' . $language_tree2 . '/language_data.php');
                echo ' onmouseover="mopen(event,\'adminx\',\'?\',\'?\')"';
                $select_top = '';
                echo ' onmouseout="mclosetime()"' . $select_top . '>' . '<img src="../languages/' . $language_tree2 . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none; height:14px"> ' . $language["name"] . ' <img src="../images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';
                ?>
                <div id="adminx" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()" style="width:250px;">
                    <ul class="humo_menu_item2">
                        <?php
                        for ($i = 0; $i < count($language_file); $i++) {
                            // *** Get language name ***
                            if ($language_file[$i] != $language_tree2) {
                                include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
                                echo '<li style="float:left; width:124px;">';
                                echo '<a href="index.php?page=tree&amp;menu_admin=tree_text&amp;language_tree=' . $language_file[$i] . '&amp;tree_id=' . $tree_id . '">';
                                echo '<img src="../languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
                                echo $language["name"];
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><b><?= __('Name of family tree'); ?></b></td>
            <td><input type="text" name="treetext_name" value="<?= $treetext_name; ?>" size="60"></td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Extra text in main menu'); ?></td>
            <td>
                <?= __('I.e. a website'); ?>: &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;<br>
                <textarea cols="60" rows="2" name="treetext_mainmenu_text"><?= $treetext_mainmenu_text; ?></textarea>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Extra source in main menu'); ?></td>
            <td>
                <?= __(' I.e. a website'); ?>: &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;<br>
                <textarea cols="60" rows="2" name="treetext_mainmenu_source"><?= $treetext_mainmenu_source; ?></textarea>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Upper text family page'); ?></td>
            <td><?= __('I.e. Familypage'); ?><br>
                <textarea cols="60" rows="1" name="treetext_family_top"><?= $treetext_family_top; ?></textarea>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Lower text family page'); ?></td>
            <td><?= __('I.e.: For more information: &lt;a href="mailform.php"&gt;contact&lt;/a&gt;'); ?><br>
                <textarea cols="60" rows="1" name="treetext_family_footer"><?= $treetext_family_footer; ?></textarea>
            </td>
        </tr>

        <tr>
            <td>
                <?= __('Change'); ?>
            </td>
            <td>
                <?php
                if (isset($treetext_id)) {
                    echo '<input type="submit" name="change_tree_text" value="' . __('Change') . '" class="btn btn-sm btn-success">';
                } else {
                    echo '<input type="submit" name="add_tree_text" value="' . __('Change') . '" class="btn btn-sm btn-success">';
                }
                ?>
            </td>
        </tr>
    </table>
</form>