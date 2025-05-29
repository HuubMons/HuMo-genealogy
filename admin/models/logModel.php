<?php
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
                $datasql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
                while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                    if (is_numeric($_POST[$dataDb->setting_id . 'id'])) {
                        $setting_value = $_POST[$dataDb->setting_id . 'own_code'] . "|" . $_POST[$dataDb->setting_id . 'link_text'];
                        $sql = "UPDATE humo_settings SET setting_value='" . safe_text_db($setting_value) . "'
                    WHERE setting_id=" . $_POST[$dataDb->setting_id . 'id'];
                        $this->dbh->query($sql);
                    }
                }
            }

            // *** Remove IP address  ***
            $datasql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST[$dataDb->setting_id . 'remove_link'])) {
                    $sql = "DELETE FROM humo_settings WHERE setting_id='" . $dataDb->setting_id . "'";
                    $this->dbh->query($sql);
                }
            }

            // *** Add IP address ***
            if (isset($_POST['add_link']) && $_POST['own_code'] != '' && is_numeric($_POST['link_order'])) {
                $setting_value = $_POST['own_code'] . "|" . $_POST['link_text'];
                $sql = "INSERT INTO humo_settings SET setting_variable='ip_blacklist',
                setting_value='" . safe_text_db($setting_value) . "', setting_order='" . $_POST['link_order'] . "'";
                $this->dbh->query($sql);
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
