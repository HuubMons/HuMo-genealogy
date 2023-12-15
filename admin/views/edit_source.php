<?php

/**
 * This is the editor file for HuMo-genealogy.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

//globals for joomla
global $tree_prefix, $gedcom_date, $gedcom_time, $pers_gedcomnumber;



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/editor.php";
$get_editor = new EditorModel($dbh);
$menu_admin = $get_editor->getMenuAdmin();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



$phpself = 'index.php';
//$sourcestring = '../source.php?';

$field_text_large = 'style="height: 100px; width:550px"';

include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

include(__DIR__ . '/../include/editor_event_cls.php');
$event_cls = new editor_event_cls;


// *****************************
// *** HuMo-genealogy Editor ***
// *****************************

$new_tree = false;

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) and $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

$userid = false;
if (is_numeric($_SESSION['user_id_admin'])) $userid = $_SESSION['user_id_admin'];
$username = $_SESSION['user_name_admin'];
$gedcom_date = strtoupper(date("d M Y"));
$gedcom_time = date("H:i:s");

if (isset($tree_id)) {
    // *** Process queries ***
    include_once(__DIR__ . "/../include/editor_inc.php");
}


// ********************
// *** Show sources ***
// ********************

if ($menu_admin == 'sources') {

    // ******* SOURCE_ADD AND SOURCE_CHANGED IS MOVED TO EDITOR_INC.PHP *************
    if (isset($_POST['source_remove'])) {
?>
        <div class="confirm">
            <?= __('Are you sure you want to remove this source and ALL source references?'); ?>
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="source_id" value="<?= $_POST['source_id']; ?>">
                <input type="hidden" name="source_gedcomnr" value="<?= $_POST['source_gedcomnr']; ?>">
                <input type="Submit" name="source_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                <input type="Submit" name="dummy5" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
            </form>
        </div>
    <?php
    }
    if (isset($_POST['source_remove2'])) {
        echo '<div class="confirm">';
        // *** Delete source ***
        $sql = "DELETE FROM humo_sources WHERE source_id='" . safe_text_db($_POST["source_id"]) . "'";
        $result = $dbh->query($sql);

        // *** Delete connections to source, and re-order remaining source connections ***
        $connect_sql = "SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_source_id='" . safe_text_db($_POST['source_gedcomnr']) . "'";
        $connect_qry = $dbh->query($connect_sql);
        while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Delete source connections ***
            $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
            $result = $dbh->query($sql);

            // *** Re-order remaining source connections ***
            $event_order = 1;
            $event_sql = "SELECT * FROM humo_connections
                WHERE connect_tree_id='" . $tree_id . "'
                AND connect_kind='" . $connectDb->connect_kind . "'
                AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                AND connect_connect_id='" . $connectDb->connect_connect_id . "'
                ORDER BY connect_order";
            $event_qry = $dbh->query($event_sql);
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_connections
                    SET connect_order='" . $event_order . "'
                    WHERE connect_id='" . $eventDb->connect_id . "'";
                $result = $dbh->query($sql);
                $event_order++;
            }
        }
        echo __('Source is removed!');
        echo '</div>';
    }


    echo '<h1 class="center">' . __('Sources') . '</h1>';
    echo __('These sources can be connected to multiple persons, families, events and other items.');

    // *** Show delete message ***
    if ($confirm) echo $confirm;

    $source_id = '';
    $check_source_id = '';
    if (isset($_POST['source_id'])) $check_source_id = $_POST['source_id'];
    // *** Link to add pictures, is using gedcomnr ***
    $check_source_gedcomnr = '';
    if (isset($_GET['source_id'])) $check_source_gedcomnr = $_GET['source_id'];

    ?>
    <table class="humo standard" style="text-align:center;">
        <tr class="table_header_large">
            <td>
                <?php
                // *** Select family tree ***
                echo __('Family tree') . ': ';
                $editor_cls->select_tree($page);

                ?>
                <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                    <?php
                    echo '<input type="hidden" name="page" value="' . $page . '">';

                    $source_qry = $dbh->query("SELECT * FROM humo_sources
                        WHERE source_tree_id='" . $tree_id . "' ORDER BY IF (source_title!='',source_title,source_text)");
                    echo __('Select source') . ': ';
                    echo '<select size="1" name="source_id" style="width: 300px" onChange="this.form.submit();">';
                    echo '<option value="">' . __('Select source') . '</option>'; // *** For new source in new database... ***
                    while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) {
                        $selected = '';
                        if ($check_source_id and $check_source_id == $sourceDb->source_id) {
                            $selected = ' selected';
                            $source_id = $sourceDb->source_id;
                        }

                        if ($check_source_gedcomnr and $check_source_gedcomnr == $sourceDb->source_gedcomnr) {
                            $selected = ' selected';
                            $source_id = $sourceDb->source_id;
                        }

                        if ($sourceDb->source_title) {
                            $show_text = $sourceDb->source_title;
                        } else {
                            $show_text = substr($sourceDb->source_text, 0, 40);
                            if (strlen($sourceDb->source_text) > 40) $show_text .= '...';
                        }
                        $restricted = '';
                        if (@$sourceDb->source_status == 'restricted') $restricted = ' *' . __('restricted') . '*';
                        echo '<option value="' . $sourceDb->source_id . '"' . $selected . '>' . $show_text .
                            ' [' . @$sourceDb->source_gedcomnr . $restricted . ']</option>' . "\n";
                    }
                    echo '</select>';

                    echo ' ' . __('or') . ': ';

                    echo '<input type="Submit" name="add_source" value="' . __('Add source') . '">';
                    ?>
                </form>
            </td>
        </tr>
    </table><br>
    <?php

    // *** Show selected source ***
    if ($source_id or isset($_POST['add_source'])) {
        //echo '<table class="humo standard" border="1">';
        //echo '<tr class="table_header"><th>'.__('Option').'</th><th colspan="3">'.__('Value').'</th></tr>';

        if (isset($_POST['add_source'])) {
            $source_gedcomnr = '';
            $source_status = '';
            $source_title = '';
            $source_date = '';
            $source_place = '';
            $source_publ = '';
            $source_refn = '';
            $source_auth = '';
            $source_auth = '';
            $source_subj = '';
            $source_item = '';
            $source_kind = '';
            $source_text = '';
            $source_repo_caln = '';
            $source_repo_page = '';
            $source_repo_gedcomnr = '';
        } else {
            @$source_qry = $dbh->query("SELECT * FROM humo_sources
                WHERE source_tree_id='" . $tree_id . "' AND source_id='" . safe_text_db($source_id) . "'");
            //$sourceDb=$db_functions->get_source ($sourcenum);

            $die_message = __('No valid source number.');
            try {
                @$sourceDb = $source_qry->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $die_message;
            }
            $source_gedcomnr = $sourceDb->source_gedcomnr;
            $source_status = $sourceDb->source_status;
            $source_title = $sourceDb->source_title;
            $source_date = $sourceDb->source_date;
            $source_place = $sourceDb->source_place;
            $source_publ = $sourceDb->source_publ;
            $source_refn = $sourceDb->source_refn;
            $source_auth = $sourceDb->source_auth;
            $source_auth = $sourceDb->source_auth;
            $source_subj = $sourceDb->source_subj;
            $source_item = $sourceDb->source_item;
            $source_kind = $sourceDb->source_kind;
            $source_text = $sourceDb->source_text;
            $source_repo_caln = $sourceDb->source_repo_caln;
            $source_repo_page = $sourceDb->source_repo_page;
            $source_repo_gedcomnr = $sourceDb->source_repo_gedcomnr;
        }

        $repo_qry = $dbh->query("SELECT * FROM humo_repositories
            WHERE repo_tree_id='" . $tree_id . "' 
            ORDER BY repo_name, repo_place");

    ?>
        <form method="POST" action="<?= $phpself; ?>" name="form3" id="form3">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="source_id" value="<?= $source_id; ?>">
            <input type="hidden" name="source_gedcomnr" value="<?= $source_gedcomnr; ?>">
            <table class="humo standard" border="1">
                <tr class="table_header">
                    <th><?= __('Option'); ?></th>
                    <th colspan="3"><?= __('Value'); ?></th>
                </tr>

                <tr>
                    <td><?= __('Status:'); ?></td>
                    <td colspan="3">
                        <select class="fonts" size="1" name="source_status">
                            <option value="publish" <?php if ($source_status == 'publish') echo ' selected'; ?>><?= __('publish'); ?></option>
                            <option value="restricted" <?php if ($source_status == 'restricted') echo ' selected'; ?>><?= __('restricted'); ?></option>
                        </select> <?= __('restricted = only visible for selected user groups'); ?>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Title'); ?></td>
                    <td colspan="3"><input type="text" name="source_title" value="<?= htmlspecialchars($source_title); ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= __('Subject'); ?></td>
                    <td colspan="3"><input type="text" name="source_subj" value="<?= htmlspecialchars($source_subj); ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= __('date') . ' - ' . __('place'); ?></td>
                    <td colspan="3"><?= $editor_cls->date_show($source_date, "source_date"); ?> <input type="text" name="source_place" value="<?= htmlspecialchars($source_place); ?>" placeholder=<?= ucfirst(__('place')); ?> size="50"></td>
                </tr>

                <tr>
                    <td><?= __('Repository'); ?></td>
                    <td colspan="3">
                        <select size="1" name="source_repo_gedcomnr">
                            <option value=""></option>'; <!-- For new repository in new database... -->
                            <?php
                            while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) {
                                $selected = '';
                                if ($repoDb->repo_gedcomnr == $source_repo_gedcomnr) {
                                    $selected = ' selected';
                                }
                                echo '<option value="' . $repoDb->repo_gedcomnr . '"' . $selected . '>' .
                                    @$repoDb->repo_gedcomnr . ', ' . $repoDb->repo_name . ' ' . $repoDb->repo_place . '</option>' . "\n";
                            }
                            ?>
                        </select>
                        &nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?page=edit_repositories"><?= __('Add repositories'); ?></a>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Publication'); ?></td>
                    <td colspan="3"><input type="text" name="source_publ" value="<?= htmlspecialchars($source_publ); ?>" size="60"> https://... <?= __('will be shown as a link.'); ?></td>
                </tr>
                <tr>
                    <td><?= __('Own code'); ?></td>
                    <td colspan="3"><input type="text" name="source_refn" value="<?= $source_refn; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('Author'); ?></td>
                    <td colspan="3"><input type="text" name="source_auth" value="<?= $source_auth; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('Nr.'); ?></td>
                    <td colspan="3"><input type="text" name="source_item" value="<?= $source_item; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('Kind'); ?></td>
                    <td colspan="3"><input type="text" name="source_kind" value="<?= $source_kind; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('Archive'); ?></td>
                    <td colspan="3"><input type="text" name="source_repo_caln" value="<?= $source_repo_caln; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('Page'); ?></td>
                    <td colspan="3"><input type="text" name="source_repo_page" value="<?= $source_repo_page; ?>" size="60"></td>
                </tr>
                <tr>
                    <td><?= __('text'); ?></td>
                    <td colspan="3"><textarea rows="6" cols="80" name="source_text" <?= $field_text_large; ?>><?= $editor_cls->text_show($source_text); ?></textarea></td>
                </tr>

                <?php
                // *** Picture by source ***
                if (!isset($_POST['add_source'])) {
                    echo $event_cls->show_event('source', $sourceDb->source_gedcomnr, 'source_picture');
                }

                if (isset($_POST['add_source'])) {
                    echo '<tr><td>' . __('Add') . '</td><td colspan="3"><input type="Submit" name="source_add" value="' . __('Add') . '"></td></tr>';
                } else {
                    echo '<tr><td>' . __('Save') . '</td><td colspan="3"><input type="Submit" name="source_change" value="' . __('Save') . '">';
                    echo ' ' . __('or') . ' <input type="Submit" name="source_remove" value="' . __('Delete') . '">';
                    echo '</td></tr>';
                }

                ?>
            </table>
        </form>
<?php

        // *** Source example in IFRAME ***
        if (!isset($_POST['add_source'])) {
            $vars['source_gedcomnr'] = $sourceDb->source_gedcomnr;
            $sourcestring = $link_cls->get_link('../', 'source', $tree_id, false, $vars);

            echo '<p>' . __('Preview') . '<br>';
            echo '<iframe src ="' . $sourcestring . '" class="iframe">';
            //TODO TRANSLATE
            echo '  <p>Your browser does not support iframes.</p>';
            echo '</iframe>';
        }
    }
}

// *** Needed to add pictures ***
function editor_label2($label, $style = '')
{
    //$text = '<span style="display: inline-block; width:220px; vertical-align: top;">';
    $text = '<span style="display: inline-block; width:150px; vertical-align: top;">';
    if ($style == 'bold') $text .= '<b>';
    $text .= ucfirst($label);
    if ($style == 'bold') $text .= '</b>';
    $text .= '</span>';
    return $text;
}
