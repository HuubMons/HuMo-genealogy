<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class SettingsHomepageModel extends AdminBaseModel
{
    public function reset_modules(): void
    {
        // *** Reset all modules. TODO: add confirmation box? ***
        if (isset($_GET['template_homepage_reset']) && $_GET['template_homepage_reset'] == '1') {
            $this->dbh->query("DELETE FROM humo_settings WHERE setting_variable='template_homepage'");

            // *** Reload page to get new values ***
            echo '<script> window.location="index.php?page=settings&menu_admin=settings_homepage";</script>';
        }
    }

    public function save_settings_modules(): void
    {
        // *** Change Module ***
        if (isset($_POST['change_module'])) {
            $modulesQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage'");
            while ($module = $modulesQry->fetch(PDO::FETCH_OBJ)) {
                $setting_value = $_POST[$module->setting_id . 'module_status'] . '|' . $_POST[$module->setting_id . 'module_column'] . '|' . $_POST[$module->setting_id . 'module_item'];
                if (isset($_POST[$module->setting_id . 'module_option_1'])) {
                    $setting_value .= '|' . $_POST[$module->setting_id . 'module_option_1'];
                }
                if (isset($_POST[$module->setting_id . 'module_option_2'])) {
                    $setting_value .= '|' . $_POST[$module->setting_id . 'module_option_2'];
                }
                $sql = "UPDATE humo_settings SET setting_value = :setting_value WHERE setting_id = :setting_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
                $stmt->bindValue(':setting_id', $_POST[$module->setting_id . 'id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // *** Remove module  ***
        if (isset($_GET['remove_module']) && is_numeric($_GET['remove_module'])) {
            $modulesQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_id='" . $_GET['remove_module'] . "'");
            $module = $modulesQry->fetch(PDO::FETCH_OBJ);
            $sql = "DELETE FROM humo_settings WHERE setting_id='" . $module->setting_id . "'";
            $this->dbh->query($sql);

            // *** Re-order links ***
            $repair_order = $module->setting_order;
            $item = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
                $this->dbh->query($sql);
            }
        }

        // *** Add module ***
        if (isset($_POST['add_module']) && is_numeric($_POST['module_order'])) {
            $setting_value = $_POST['module_status'] . "|" . $_POST['module_column'] . "|" . $_POST['module_item'];
            $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_order) VALUES (:setting_variable, :setting_value, :setting_order)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':setting_variable', 'template_homepage', PDO::PARAM_STR);
            $stmt->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
            $stmt->bindValue(':setting_order', $_POST['module_order'], PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function order_modules(): void
    {
        // *** Automatic group all items: left, center and right items. So it's easier to move items ***
        $moduleQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
        $left = 0;
        $center = 0;
        $right = 0;
        if ($moduleQry) {
            $teller = 0;
            // *** Read all items ***
            while ($module = $moduleQry->fetch(PDO::FETCH_OBJ)) {
                $module->setting_value .= '|'; // In some cases the last | is missing. TODO: improve saving of settings.
                $lijst = explode("|", $module->setting_value);
                if ($lijst[1] === 'left') {
                    $left++;
                }
                if ($lijst[1] === 'center') {
                    $center++;
                }
                if ($lijst[1] === 'right') {
                    $right++;
                }
                $item_array[$teller]['id'] = $module->setting_id;
                $item_array[$teller]['column'] = $lijst[1];
                $item_array[$teller]['order'] = $module->setting_order;
                $teller++;
            }
        }

        $count_left = 0;
        $count_center = $left;
        $count_right = $left + $center;
        // *** Reorder all items (if new item is added) ***
        $counter = count($item_array);
        // *** Reorder all items (if new item is added) ***
        for ($i = 0; $i < $counter; $i++) {
            if ($item_array[$i]['column'] == 'left') {
                $count_left++;
                if ($item_array[$i]['order'] != $count_left) {
                    $sql = "UPDATE humo_settings SET setting_order='" . $count_left . "' WHERE setting_id='" . $item_array[$i]['id'] . "'";
                    $this->dbh->query($sql);
                }
            }

            if ($item_array[$i]['column'] == 'center') {
                $count_center++;
                if ($item_array[$i]['order'] != $count_center) {
                    $sql = "UPDATE humo_settings SET setting_order='" . $count_center . "' WHERE setting_id='" . $item_array[$i]['id'] . "'";
                    $this->dbh->query($sql);
                }
            }

            if ($item_array[$i]['column'] == 'right') {
                $count_right++;
                if ($item_array[$i]['order'] != $count_right) {
                    $sql = "UPDATE humo_settings SET setting_order='" . $count_right . "' WHERE setting_id='" . $item_array[$i]['id'] . "'";
                    $this->dbh->query($sql);
                }
            }
        }
    }

    public function get_modules(): array
    {
        $settings['modules_left'] = 0;
        $settings['modules_center'] = 0;
        $settings['modules_right'] = 0;

        $datasql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
        while ($data2Db = $datasql->fetch(PDO::FETCH_OBJ)) {
            $data2Db->setting_value .= '|'; // In some cases the last | is missing. TODO: improve saving of settings.
            $item = explode("|", $data2Db->setting_value);

            $settings['module_setting_id'][] = $data2Db->setting_id;
            $settings['module_setting_order'][] = $data2Db->setting_order;

            $settings['module_active'][] = $item[0];
            $settings['module_position'][] = $item[1];
            $settings['module_item'][] = $item[2];

            $settings['module_option_1'][] = isset($item[3]) ? $item[3] : '';
            $settings['module_option_2'][] = isset($item[4]) ? $item[4] : '';

            // *** Count modules left, center, right ***
            if ($item[1] == 'left') {
                $settings['modules_left']++;
            } elseif ($item[1] == 'center') {
                $settings['modules_center']++;
            } elseif ($item[1] == 'right') {
                $settings['modules_right']++;
            }
        }
        $settings['nr_modules'] = count($settings['module_active']) - 1;
        return $settings;
    }

    public function save_settings_favorites(): void
    {
        // *** Change link ***
        if (isset($_POST['change_link'])) {
            $linksQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
            while ($link = $linksQry->fetch(PDO::FETCH_OBJ)) {
                $setting_value = $_POST[$link->setting_id . 'own_code'] . "|" . $_POST[$link->setting_id . 'link_text'];
                $sql = "UPDATE humo_settings SET setting_value = :setting_value WHERE setting_id = :setting_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
                $stmt->bindValue(':setting_id', $_POST[$link->setting_id . 'id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // *** Remove link  ***
        if (isset($_GET['remove_link']) && is_numeric($_GET['remove_link'])) {
            $linkQry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_id='" . $_GET['remove_link'] . "'");
            $link = $linkQry->fetch(PDO::FETCH_OBJ);
            $sql = "DELETE FROM humo_settings WHERE setting_id='" . $link->setting_id . "'";
            $this->dbh->query($sql);

            // *** Re-order links ***
            $repair_order = $link->setting_order;
            $item = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
                $this->dbh->query($sql);
            }
        }

        // *** Add link ***
        if (isset($_POST['add_link']) && is_numeric($_POST['link_order'])) {
            $setting_value = $_POST['own_code'] . "|" . $_POST['link_text'];
            $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_order) VALUES (:setting_variable, :setting_value, :setting_order)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':setting_variable', 'link', PDO::PARAM_STR);
            $stmt->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
            $stmt->bindValue(':setting_order', $_POST['link_order'], PDO::PARAM_INT);
            $stmt->execute();
        }

        if (isset($_GET['up']) && is_numeric($_GET['link_order']) && is_numeric($_GET['id'])) {
            // *** Search previous link ***
            $item = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . ($_GET['link_order'] - 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Raise previous link ***
            $sql = "UPDATE humo_settings SET setting_order='" . $_GET['link_order'] . "' WHERE setting_id='" . $itemDb->setting_id . "'";

            $this->dbh->query($sql);
            // *** Lower link order ***
            $sql = "UPDATE humo_settings SET setting_order='" . ($_GET['link_order'] - 1) . "' WHERE setting_id=" . $_GET['id'];

            $this->dbh->query($sql);
        }

        if (isset($_GET['down']) && is_numeric($_GET['link_order']) && is_numeric($_GET['id'])) {
            // *** Search next link ***
            $item = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . ($_GET['link_order'] + 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Lower previous link ***
            $sql = "UPDATE humo_settings SET setting_order = :link_order WHERE setting_id = :setting_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':link_order', $_GET['link_order'], PDO::PARAM_INT);
            $stmt->bindValue(':setting_id', $itemDb->setting_id, PDO::PARAM_INT);
            $stmt->execute();

            $this->dbh->query($sql);
            // *** Raise link order ***
            $sql = "UPDATE humo_settings SET setting_order='" . ($_GET['link_order'] + 1) . "' WHERE setting_id=" . $_GET['id'];

            $this->dbh->query($sql);
        }
    }
}
