<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class LogModel extends AdminBaseModel
{
    public function get_menu_tab(): string
    {
        $menu_tab = 'log_users';
        if (isset($_POST['menu_admin'])) {
            $menu_tab = $_POST['menu_admin'];
        }
        if (isset($_GET['menu_admin'])) {
            $menu_tab = $_GET['menu_admin'];
        }
        return $menu_tab;
    }

    public function update_ip($menu_tab): void
    {
        // *** IP blacklist ***
        if ($menu_tab == 'log_blacklist') {

            // *** Change IP address ***
            if (isset($_POST['change_link'])) {
                $ipBlacklistQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
                while ($ipBlacklist = $ipBlacklistQry->fetch(PDO::FETCH_OBJ)) {
                    if (is_numeric($_POST[$ipBlacklist->setting_id . 'id'])) {
                        $setting_value = $_POST[$ipBlacklist->setting_id . 'own_code'] . "|" . $_POST[$ipBlacklist->setting_id . 'link_text'];
                        $sql = "UPDATE humo_settings SET setting_value = :setting_value WHERE setting_id = :setting_id";
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->execute([
                            ':setting_value' => $setting_value,
                            ':setting_id' => $_POST[$ipBlacklist->setting_id . 'id']
                        ]);
                    }
                }
            }

            // *** Remove IP address  ***
            $ipBlacklistQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
            while ($ipBlacklist = $ipBlacklistQry->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST[$ipBlacklist->setting_id . 'remove_link'])) {
                    $sql = "DELETE FROM humo_settings WHERE setting_id='" . $ipBlacklist->setting_id . "'";
                    $this->dbh->query($sql);
                }
            }

            // *** Add IP address ***
            if (isset($_POST['add_link']) && $_POST['own_code'] != '' && is_numeric($_POST['link_order'])) {
                $setting_value = $_POST['own_code'] . "|" . $_POST['link_text'];
                $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_order) VALUES (:setting_variable, :setting_value, :setting_order)";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':setting_variable' => 'ip_blacklist',
                    ':setting_value'    => $setting_value,
                    ':setting_order'    => $_POST['link_order']
                ]);
            }

            if (isset($_GET['up']) && is_numeric($_GET['link_order']) && is_numeric($_GET['id'])) {
                // *** Search previous link ***
                $sql = "SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' AND setting_order=" . $_GET['link_order'] - 1;
                $item = $this->dbh->query($sql);
                $itemDb = $item->fetch(PDO::FETCH_OBJ);

                // *** Raise previous link ***
                $sql = "UPDATE humo_settings SET setting_order='" . $_GET['link_order'] . "' WHERE setting_id='" . $itemDb->setting_id . "'";
                $this->dbh->query($sql);

                // *** Lower link order ***
                $sql = "UPDATE humo_settings SET setting_order='" . $_GET['link_order'] - 1 . "' WHERE setting_id=" . $_GET['id'];
                $this->dbh->query($sql);
            }
            if (isset($_GET['down']) && is_numeric($_GET['link_order']) && is_numeric($_GET['id'])) {
                // *** Search next link ***
                $item = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' AND setting_order=" . $_GET['link_order'] + 1);
                $itemDb = $item->fetch(PDO::FETCH_OBJ);

                // *** Lower previous link ***
                $sql = "UPDATE humo_settings SET setting_order='" . $_GET['link_order'] . "' WHERE setting_id='" . $itemDb->setting_id . "'";
                $this->dbh->query($sql);

                // *** Raise link order ***
                $sql = "UPDATE humo_settings SET setting_order='" . $_GET['link_order'] + 1 . "' WHERE setting_id=" . $_GET['id'];
                $this->dbh->query($sql);
            }
        }
    }
}
