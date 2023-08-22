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
$get_editor = new Editor($dbh);
$menu_admin = $get_editor->getMenuAdmin();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



$phpself = 'index.php';
$joomlastring = '';
$sourcestring = '../source.php?';
$addresstring = '../address.php?';

$field_text_large = 'style="height: 100px; width:550px"';

$joomlapath = CMS_ROOTPATH_ADMIN . 'include/';

include_once($joomlapath . "editor_cls.php");
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
    include_once($joomlapath . "editor_inc.php");
}



// *******************************
// *** Show/ edit repositories ***
// *******************************


if ($menu_admin == 'repositories') {
    if (isset($_POST['repo_add'])) {
        // *** Generate new GEDCOM number ***
        $new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'repo');

        $sql = "INSERT INTO humo_repositories SET
            repo_tree_id='" . $tree_id . "',
            repo_gedcomnr='" . $new_gedcomnumber . "',
            repo_name='" . $editor_cls->text_process($_POST['repo_name']) . "',
            repo_address='" . $editor_cls->text_process($_POST['repo_address']) . "',
            repo_zip='" . safe_text_db($_POST['repo_zip']) . "',
            repo_place='" . $editor_cls->text_process($_POST['repo_place']) . "',
            repo_phone='" . safe_text_db($_POST['repo_phone']) . "',
            repo_date='" . $editor_cls->date_process('repo_date') . "',
            repo_text='" . $editor_cls->text_process($_POST['repo_text']) . "',
            repo_mail='" . safe_text_db($_POST['repo_mail']) . "',
            repo_url='" . safe_text_db($_POST['repo_url']) . "',
            repo_new_user='" . $username . "',
            repo_new_date='" . $gedcom_date . "',
            repo_new_time='" . $gedcom_time . "'";
        $result = $dbh->query($sql);

        $_POST['repo_id'] = $dbh->lastInsertId();
    }

    if (isset($_POST['repo_change'])) {
        $sql = "UPDATE humo_repositories SET
            repo_name='" . $editor_cls->text_process($_POST['repo_name']) . "',
            repo_address='" . $editor_cls->text_process($_POST['repo_address']) . "',
            repo_zip='" . safe_text_db($_POST['repo_zip']) . "',
            repo_place='" . $editor_cls->text_process($_POST['repo_place']) . "',
            repo_phone='" . safe_text_db($_POST['repo_phone']) . "',
            repo_date='" . $editor_cls->date_process('repo_date') . "',
            repo_text='" . $editor_cls->text_process($_POST['repo_text']) . "',
            repo_mail='" . safe_text_db($_POST['repo_mail']) . "',
            repo_url='" . safe_text_db($_POST['repo_url']) . "',
            repo_changed_user='" . $username . "',
            repo_changed_date='" . $gedcom_date . "',
            repo_changed_time='" . $gedcom_time . "'
            WHERE repo_id='" . safe_text_db($_POST["repo_id"]) . "'";
        $result = $dbh->query($sql);
    }

    if (isset($_POST['repo_remove'])) {
?>
        <div class="confirm">
            <?= __('Really remove repository with all repository links?'); ?>
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="repo_id" value="<?= $_POST['repo_id']; ?>">
                <input type="Submit" name="repo_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                <input type="Submit" name="dummy6" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
            </form>
        </div>
    <?php
    }
    if (isset($_POST['repo_remove2'])) {
        echo '<div class="confirm">';
        // *** Find gedcomnumber, needed for events query ***
        $repo_qry = $dbh->query("SELECT * FROM humo_repositories
            WHERE repo_id='" . safe_text_db($_POST["repo_id"]) . "'");
        $repoDb = $repo_qry->fetch(PDO::FETCH_OBJ);

        // *** Delete repository link ***
        $sql = "UPDATE humo_sources SET source_repo_gedcomnr=''
            WHERE source_tree_id='" . $tree_id . "' AND source_repo_gedcomnr='" . $repoDb->repo_gedcomnr . "'";
        $result = $dbh->query($sql);

        // *** Delete repository ***
        $sql = "DELETE FROM humo_repositories
            WHERE repo_id='" . safe_text_db($_POST["repo_id"]) . "'";

        $result = $dbh->query($sql);
        echo __('Repository is removed!');
        echo '</div>';

        // *** Empty $_POST ***
        unset($_POST['repo_id']);
    }

    ?>
    <h1 class="center"><?= __('Repositories'); ?></h1>
    <?= __('A repository can be connected to a source. Edit a source to connect a repository.'); ?>

    <table class="humo standard" style="text-align:center;">
        <tr class="table_header_large">
            <td>
                <?php
                // *** Select family tree ***
                echo __('Family tree') . ': ';
                $editor_cls->select_tree($page);

                echo ' <form method="POST" action="' . $phpself . '" style="display : inline;">';
                echo '<input type="hidden" name="page" value="' . $page . '">';
                $repo_qry = $dbh->query("SELECT * FROM humo_repositories
                    WHERE repo_tree_id='" . $tree_id . "'
                    ORDER BY repo_name, repo_place");

                echo __('Select repository') . ' ';
                echo '<select size="1" name="repo_id" onChange="this.form.submit();">';
                echo '<option value="">' . __('Select repository') . '</option>'; // *** For new repository in new database... ***
                while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) {
                    $selected = '';
                    if (isset($_POST['repo_id'])) {
                        if ($_POST['repo_id'] == $repoDb->repo_id) {
                            $selected = ' selected';
                        }
                    }
                    echo '<option value="' . $repoDb->repo_id . '"' . $selected . '>' .
                        @$repoDb->repo_gedcomnr . ', ' . $repoDb->repo_name . ' ' . $repoDb->repo_place . '</option>' . "\n";
                }
                echo '</select>';

                echo ' ' . __('or') . ': ';
                echo '<input type="Submit" name="add_repo" value="' . __('Add repository') . '">';
                echo '</form>';
                ?>
            </td>
        </tr>
    </table><br>
    <?php

    // *** Show selected repository ***
    if (isset($_POST['repo_id'])) {
        if (isset($_POST['add_repo'])) {
            $repo_name = '';
            $repo_address = '';
            $repo_zip = '';
            $repo_place = '';
            $repo_phone = '';
            $repo_date = '';
            $repo_text = ''; //$repo_source='';
            $repo_mail = '';
            $repo_url = '';
            $repo_new_user = '';
            $repo_new_date = '';
            $repo_new_time = '';
            $repo_changed_user = '';
            $repo_changed_date = '';
            $repo_changed_time = '';
        } else {
            @$repo_qry = $dbh->query("SELECT * FROM humo_repositories
                WHERE repo_id='" . safe_text_db($_POST["repo_id"]) . "'");
            $die_message = __('No valid repository number.');
            try {
                @$repoDb = $repo_qry->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $die_message;
            }
            $repo_name = $repoDb->repo_name;
            $repo_address = $repoDb->repo_address;
            $repo_zip = $repoDb->repo_zip;
            $repo_place = $repoDb->repo_place;
            $repo_phone = $repoDb->repo_phone;
            $repo_date = $repoDb->repo_date;
            //$repo_source=$repoDb->repo_source;
            $repo_text = $repoDb->repo_text;
            $repo_mail = $repoDb->repo_mail;
            $repo_url = $repoDb->repo_url;
            $repo_new_user = $repoDb->repo_new_user;
            $repo_new_date = $repoDb->repo_new_date;
            $repo_new_time = $repoDb->repo_new_time;
            $repo_changed_user = $repoDb->repo_changed_user;
            $repo_changed_date = $repoDb->repo_changed_date;
            $repo_changed_time = $repoDb->repo_changed_time;
        }

    ?>
        <form method="POST" action="<?= $phpself; ?>">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="repo_id" value="<?= $_POST['repo_id']; ?>">
            <table class="humo standard" border="1">
                <tr class="table_header">
                    <th><?= __('Option'); ?></th>
                    <th colspan="2"><?= __('Value'); ?></th>
                </tr>

                <tr>
                    <td><?= __('Title'); ?></td>
                    <td><input type="text" name="repo_name" value="<?= htmlspecialchars($repo_name); ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= __('Address'); ?></td>
                    <td><input type="text" name="repo_address" value="<?= htmlspecialchars($repo_address); ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= __('Zip code'); ?></td>
                    <td><input type="text" name="repo_zip" value="<?= $repo_zip; ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= ucfirst(__('date')) . ' - ' . __('place'); ?></td>
                    <td><?= $editor_cls->date_show($repo_date, "repo_date"); ?> <input type="text" name="repo_place" value="<?= htmlspecialchars($repo_place); ?>" placeholder="<?= ucfirst(__('place')); ?>" size="50"></td>
                </tr>

                <tr>
                    <td><?= __('Phone'); ?></td>
                    <td><input type="text" name="repo_phone" value="<?= $repo_phone; ?>" size="60"></td>
                </tr>

                <!-- SOURCE -->

                <tr>
                    <td><?= ucfirst(__('text')); ?></td>
                    <td><textarea rows="1" name="repo_text" <?= $field_text_large; ?>><?= $editor_cls->text_show($repo_text); ?></textarea></td>
                </tr>

                <tr>
                    <td><?= __('E-mail'); ?></td>
                    <td><input type="text" name="repo_mail" value="<?= $repo_mail; ?>" size="60"></td>
                </tr>

                <tr>
                    <td><?= __('URL/ Internet link'); ?></td>
                    <td><input type="text" name="repo_url" value="<?= $repo_url; ?>" size="60"></td>
                </tr>

                <?php
                if (isset($_POST['add_repo'])) {
                    echo '<tr><td>' . __('Add') . '</td><td><input type="Submit" name="repo_add" value="' . __('Add') . '"></td></tr>';
                } else {
                    echo '<tr><td>' . __('Save') . '</td><td><input type="Submit" name="repo_change" value="' . __('Save') . '">';

                    echo ' ' . __('or') . ' ';
                    echo '<input type="Submit" name="repo_remove" value="' . __('Delete') . '">';

                    echo '</td></tr>';
                }
                ?>
            </table>
        </form>
<?php

        // *** Repository example in IFRAME ***
        if (!isset($_POST['add_repo'])) {
            //TODO: show repo in example frame.
            //echo '<p>'.__('Preview').'<br>';
            //echo '<iframe src ="'.$sourcestring.'tree_id='.$tree_id.'&amp;id='.$repoDb->repo_gedcomnr.'" class="iframe">';
            //TRANSLATE
            //echo '  <p>Your browser does not support iframes.</p>';
            //echo '</iframe>';
        }
    }
}
