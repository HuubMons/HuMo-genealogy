<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class TreesModel extends AdminBaseModel
{
    private $selected_tree_id;

    public function set_tree_id(): void
    {
        $this->selected_tree_id = $this->tree_id;

        if (isset($_POST['tree_id']) && is_numeric(($_POST['tree_id']))) {
            $this->selected_tree_id = $_POST['tree_id'];
        }
    }
    public function get_tree_id(): int
    {
        return $this->selected_tree_id;
    }

    public function get_language($selected_language): string
    {
        $language = $selected_language; // Default language
        if (isset($_GET['language_tree'])) {
            $language = $_GET['language_tree'];
        }
        if (isset($_POST['language_tree'])) {
            $language = $_POST['language_tree'];
        }
        return $language;
    }

    public function get_language2($language, $selected_language): string
    {
        $language2 = $language;
        if ($language == 'default') {
            $language2 = $selected_language;
        }
        return $language2;
    }

    public function get_menu_tab(): string
    {
        $menu_tab = 'tree_main';
        if (isset($_POST['menu_admin'])) {
            $menu_tab = $_POST['menu_admin'];
        }
        if (isset($_GET['menu_admin'])) {
            $menu_tab = $_GET['menu_admin'];
        }
        return $menu_tab;
    }

    public function get_tree_pict_path()
    {
        $data2sql = $this->dbh->query("SELECT tree_pict_path FROM humo_trees WHERE tree_id=" . $this->selected_tree_id);
        $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
        $tree_pict_path = $data2Db->tree_pict_path;
        //if (substr($data2Db->tree_pict_path, 0, 1) === '|') {
        //    $tree_pict_path = substr($trees['tree_pict_path'], 1);
        //}
        return $tree_pict_path;
    }

    public function update_tree(): void
    {
        // *** Add family tree ***
        if (isset($_POST['add_tree_data'])) {
            // *** Find latest tree_prefix ***
            $found = '1';
            $i = 1;
            while ($found == '1') {
                $new_tree_prefix = 'humo' . $i . '_';
                $familyTree = $this->dbh->query("SELECT tree_id FROM humo_trees WHERE tree_prefix='" . $new_tree_prefix . "'");
                $found = $familyTree->rowCount();
                $i++;
            }

            // *** Get highest order number ***
            $tree_order = 1;
            $familyTreeQry = $this->dbh->query("SELECT tree_order FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1");
            if ($familyTree) {
                $familyTree = $familyTreeQry->fetch(PDO::FETCH_OBJ);
                $tree_order = $familyTree->tree_order + 1;
            }

            $sql = "INSERT INTO humo_trees SET
                tree_order='" . $tree_order . "',
                tree_prefix='" . $new_tree_prefix . "',
                tree_persons='0',
                tree_families='0',
                tree_email='',
                tree_privacy='',
                tree_pict_path='|../pictures/'";
            $this->dbh->query($sql);

            $_SESSION['tree_prefix'] = $new_tree_prefix;

            $this->selected_tree_id = $this->dbh->lastInsertId();
            $_SESSION['admin_tree_id'] = $this->selected_tree_id;
        }

        if (isset($_POST['change_tree_data'])) {
            $tree_pict_path = $_POST['tree_pict_path'];
            if (substr($_POST['tree_pict_path'], 0, 1) === '|') {
                if (isset($_POST['default_path']) && $_POST['default_path'] == 'no') {
                    $tree_pict_path = substr($tree_pict_path, 1);
                }
            } elseif (isset($_POST['default_path']) && $_POST['default_path'] == 'yes') {
                $tree_pict_path = '|' . $tree_pict_path;
            }

            $sql = "UPDATE humo_trees SET
                tree_email = :tree_email,
                tree_owner = :tree_owner,
                tree_pict_path = :tree_pict_path,
                tree_privacy = :tree_privacy
                WHERE tree_id = :tree_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tree_email'    => $_POST['tree_email'],
                ':tree_owner'    => $_POST['tree_owner'],
                ':tree_pict_path' => $tree_pict_path,
                ':tree_privacy'  => $_POST['tree_privacy'],
                ':tree_id'       => $this->selected_tree_id
            ]);
        }

        if (isset($_POST['remove_tree2']) && is_numeric($_POST['tree_id'])) {
            $removeqry = 'SELECT * FROM humo_trees WHERE tree_id="' . $_POST['tree_id'] . '"';
            $removesql = $this->dbh->query($removeqry);
            $removeDb = $removesql->fetch(PDO::FETCH_OBJ);
            $remove = $removeDb->tree_prefix;

            // *** Re-order family trees ***
            $repair_order = $removeDb->tree_order;
            $item = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_trees SET tree_order='" . ($itemDb->tree_order - 1) . "' WHERE tree_id=" . $itemDb->tree_id;
                $this->dbh->query($sql);
            }

            $sql = "DELETE FROM humo_trees WHERE tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove events ***
            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove items from table family_tree_text ***
            $sql = "DELETE FROM humo_tree_texts WHERE treetext_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove persons ***
            $sql = "DELETE FROM humo_persons WHERE pers_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove families ***
            $sql = "DELETE FROM humo_families WHERE fam_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove sources ***
            $sql = "DELETE FROM humo_sources WHERE source_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove repositories ***
            $sql = "DELETE FROM humo_repositories WHERE repo_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove texts ***
            $sql = "DELETE FROM humo_texts WHERE text_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove connections ***
            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove addresses ***
            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove statistics ***
            $sql = "DELETE FROM humo_stat_date WHERE stat_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove unprocessed tags ***
            $sql = "DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove cache ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable LIKE 'cache%' AND setting_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove admin favourites ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='" . $_POST['tree_id'] . "'";
            $this->dbh->query($sql);

            // *** Remove adjusted glider settings ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable='gslider_" . $remove . "'";
            $this->dbh->query($sql);

            unset($_POST['tree_id']);

            // *** Next lines to reset session items for editor pages ***
            if (isset($_SESSION['admin_tree_prefix'])) {
                unset($_SESSION['admin_tree_prefix']);
            }
            if (isset($_SESSION['admin_tree_id'])) {
                unset($_SESSION['admin_tree_id']);
            }
            unset($_SESSION['admin_pers_gedcomnumber']);
            unset($_SESSION['admin_fam_gedcomnumber']);

            // *** Now select another family tree ***
            $check_tree_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order LIMIT 0,1");
            $check_treeDb = $check_tree_sql->fetch(PDO::FETCH_OBJ);
            $check_tree_id = $check_treeDb->tree_id;

            // *** Double check tree_id and save tree id in session ***
            $_SESSION['admin_tree_id'] = '';
            if ($check_tree_id && $check_tree_id != '') {
                $get_treeDb = $this->db_functions->get_tree($check_tree_id);

                $this->selected_tree_id = $get_treeDb->tree_id;
                $_SESSION['admin_tree_id'] = $this->selected_tree_id;
            }
        }

        if (isset($_POST['add_tree_text'])) {
            $sql = "INSERT INTO humo_tree_texts (
                treetext_tree_id,
                treetext_language,
                treetext_name,
                treetext_mainmenu_text,
                treetext_mainmenu_source,
                treetext_family_top,
                treetext_family_footer
            ) VALUES (
                :treetext_tree_id,
                :treetext_language,
                :treetext_name,
                :treetext_mainmenu_text,
                :treetext_mainmenu_source,
                :treetext_family_top,
                :treetext_family_footer
            )";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':treetext_tree_id'         => $this->selected_tree_id,
                ':treetext_language'        => $_POST['language_tree'],
                ':treetext_name'            => $_POST['treetext_name'],
                ':treetext_mainmenu_text'   => $_POST['treetext_mainmenu_text'],
                ':treetext_mainmenu_source' => $_POST['treetext_mainmenu_source'],
                ':treetext_family_top'      => $_POST['treetext_family_top'],
                ':treetext_family_footer'   => $_POST['treetext_family_footer']
            ]);
        }

        if (isset($_POST['change_tree_text'])) {
            $sql = "UPDATE humo_tree_texts SET
                treetext_tree_id = :treetext_tree_id,
                treetext_language = :treetext_language,
                treetext_name = :treetext_name,
                treetext_mainmenu_text = :treetext_mainmenu_text,
                treetext_mainmenu_source = :treetext_mainmenu_source,
                treetext_family_top = :treetext_family_top,
                treetext_family_footer = :treetext_family_footer
            WHERE treetext_id = :treetext_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':treetext_tree_id'         => $this->selected_tree_id,
                ':treetext_language'        => $_POST['language_tree'],
                ':treetext_name'            => $_POST['treetext_name'],
                ':treetext_mainmenu_text'   => $_POST['treetext_mainmenu_text'],
                ':treetext_mainmenu_source' => $_POST['treetext_mainmenu_source'],
                ':treetext_family_top'      => $_POST['treetext_family_top'],
                ':treetext_family_footer'   => $_POST['treetext_family_footer'],
                ':treetext_id'              => $_POST['treetext_id']
            ]);
        }

        // *** Add empty line ***
        if (isset($_POST['add_tree_data_empty'])) {
            // *** Get highest order number ***
            $tree_order = 1;
            $familyTreesQry = $this->dbh->query("SELECT * FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1");
            if ($familyTreesQry) {
                $familyTree = $familyTreesQry->fetch(PDO::FETCH_OBJ);
                $tree_order = $familyTree->tree_order + 1;
            }

            $sql = "INSERT INTO humo_trees SET
                tree_order='" . $tree_order . "',
                tree_prefix='EMPTY',
                tree_persons='EMPTY',
                tree_families='EMPTY',
                tree_email='EMPTY',
                tree_privacy='EMPTY',
                tree_pict_path='EMPTY'
                ";
            $this->dbh->query($sql);
        }

        // *** Change collation of tree ***
        if (isset($_POST['tree_collation'])) {
            $collation = $_POST['tree_collation'];

            $stmt1 = $this->dbh->prepare("ALTER TABLE humo_persons CHANGE `pers_lastname` `pers_lastname` VARCHAR(50) COLLATE $collation;");
            $stmt1->execute();

            $stmt2 = $this->dbh->prepare("ALTER TABLE humo_persons CHANGE `pers_firstname` `pers_firstname` VARCHAR(50) COLLATE $collation;");
            $stmt2->execute();

            $stmt3 = $this->dbh->prepare("ALTER TABLE humo_persons CHANGE `pers_prefix` `pers_prefix` VARCHAR(20) COLLATE $collation;");
            $stmt3->execute();

            $stmt4 = $this->dbh->prepare("ALTER TABLE humo_events CHANGE `event_event` `event_event` TEXT COLLATE $collation;");
            $stmt4->execute();
        }
    }
}
