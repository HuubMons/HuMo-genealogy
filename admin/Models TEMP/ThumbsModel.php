<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class ThumbsModel extends AdminBaseModel
{
    public function get_menu_tab(): string
    {
        $menu_tab = 'picture_settings';
        if (isset($_POST['menu_tab'])) {
            $menu_tab = $_POST['menu_tab'];
        }
        if (isset($_GET['menu_tab'])) {
            $menu_tab = $_GET['menu_tab'];
        }

        return $menu_tab;
    }

    // *** Save new/ changed picture path ***
    public function save_picture_path(): void
    {
        if (isset($_POST['change_tree_data'])) {
            $tree_pict_path = $_POST['tree_pict_path'];
            if (substr($_POST['tree_pict_path'], 0, 1) === '|') {
                if (isset($_POST['default_path']) && $_POST['default_path'] == 'no') {
                    $tree_pict_path = substr($tree_pict_path, 1);
                }
            } elseif (isset($_POST['default_path']) && $_POST['default_path'] == 'yes') {
                $tree_pict_path = '|' . $tree_pict_path;
            }
            $sql = "UPDATE humo_trees SET tree_pict_path = :tree_pict_path WHERE tree_id = :tree_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':tree_pict_path' => $tree_pict_path,
                ':tree_id' => $this->tree_id
            ]);
        }
    }

    public function get_tree_pict_path(): string
    {
        $data2sql = $this->dbh->query("SELECT tree_pict_path FROM humo_trees WHERE tree_id=" . $this->tree_id);
        $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
        return $data2Db->tree_pict_path;
    }

    public function get_default_path($tree_pict_path): bool
    {
        // *** Picture path. A | character is used for a default path (the old path will remain in the field) ***
        if (substr($tree_pict_path, 0, 1) === '|') {
            return true;
        } else {
            return false;
        }
    }
}
