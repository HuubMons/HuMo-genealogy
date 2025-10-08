<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class AdminMapsModel extends AdminBaseModel
{
    public function get_use_world_map(): string
    {
        $use_world_map = 'Google';
        $use_world_query = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'use_world_map'");
        $use_worldDb = $use_world_query->fetch(PDO::FETCH_OBJ);
        if ($use_worldDb) {
            $use_world_map = $use_worldDb->setting_value;
            // *** Update value ***
            if (isset($_POST['use_world_map']) && ($_POST['use_world_map'] == 'OpenStreetMap' || $_POST['use_world_map'] == 'Google')) {
                $this->dbh->query("UPDATE humo_settings SET setting_value='" . $_POST['use_world_map'] . "' WHERE setting_variable='use_world_map'");
                $use_world_map = $_POST['use_world_map'];
            }
        } elseif (isset($_POST['use_world_map']) && $_POST['use_world_map'] == 'OpenStreetMap') {
            // *** No value in database, add new value ***
            $this->dbh->query("INSERT INTO humo_settings SET setting_variable='use_world_map', setting_value='OpenStreetMap'");
            $use_world_map = $_POST['use_world_map'];
        }
        return $use_world_map;
    }

    public function get_google_api1(): string
    {
        // *** Google key 1 ***
        $api_1 = '';
        // *** Admin requested to delete the existing key - show field to enter updated key ***
        $api_query = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'google_api_key'");
        $apiDb = $api_query->fetch(PDO::FETCH_OBJ);
        if ($apiDb) {
            $api_1 = $apiDb->setting_value;
            // *** Update value ***
            if (isset($_POST['api_1'])) {
                $stmt = $this->dbh->prepare("UPDATE humo_settings SET setting_value = :setting_value WHERE setting_variable = 'google_api_key'");
                $stmt->bindValue(':setting_value', $_POST['api_1'], PDO::PARAM_STR);
                $stmt->execute();

                $api_1 = $_POST['api_1'];
            }
        } elseif (isset($_POST['api_1'])) {
            // *** No value in database, add new value ***
            $stmt = $this->dbh->prepare("INSERT INTO humo_settings SET setting_value = :setting_value, setting_variable = 'google_api_key'");
            $stmt->bindValue(':setting_value', $_POST['api_1'], PDO::PARAM_STR);
            $stmt->execute();

            $api_1 = $_POST['api_1'];
        }
        return $api_1;
    }

    public function get_geokeo_api(): string
    {
        // *** OpenStreepMap key ***
        $api_geokeo = '';
        $api_query = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'geokeo_api_key'");
        $api_2Db = $api_query->fetch(PDO::FETCH_OBJ);
        if ($api_2Db) {
            $api_geokeo = $api_2Db->setting_value;
            // *** Update value ***
            if (isset($_POST['api_geokeo'])) {
                $stmt = $this->dbh->prepare("UPDATE humo_settings SET setting_value = :setting_value WHERE setting_variable = 'geokeo_api_key'");
                $stmt->bindValue(':setting_value', $_POST['api_geokeo'], PDO::PARAM_STR);
                $stmt->execute();
                $api_geokeo = $_POST['api_geokeo'];
            }
        } elseif (isset($_POST['api_geokeo']) && $_POST['api_geokeo'] != '') {
            // *** No value in database, add new value ***
            $stmt = $this->dbh->prepare("INSERT INTO humo_settings SET setting_variable = 'geokeo_api_key', setting_value = :setting_value");
            $stmt->bindValue(':setting_value', $_POST['api_geokeo'], PDO::PARAM_STR);
            $stmt->execute();
            $api_geokeo = $_POST['api_geokeo'];
        }
        return $api_geokeo;
    }

    /*
    public function get_default_zoom(): string
    {
        $query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_zoom' ";
        $result = $this->dbh->query($query);
        if (isset($_GET['map_zoom_default']) && is_numeric($_GET['map_zoom_default'])) {
            if ($result->rowCount() > 0) {
                $this->db_functions->update_settings('google_map_zoom', $_GET['map_zoom_default']);
                $mapzoom_def = $_GET['map_zoom_default'];
            } else {
                $sql = "INSERT INTO humo_settings SET setting_variable='google_map_zoom', setting_value='" . $_GET['map_zoom_default'] . "'";
                $this->dbh->query($sql);
                $mapzoom_def = $_GET['map_zoom_default'];
            }
        } else {
            if ($result->rowCount() > 0) {
                $mapzoom_default = $result->fetch();
                $mapzoom_def = $mapzoom_default['setting_value'];
            } else {
                $mapzoom_def = "11";
            }
        }
        return $mapzoom_def;
    }
    */

    /*
    public function get_map_type(): string
    {
        $query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_type' ";
        $result = $this->dbh->query($query);
        if (isset($_GET['maptype_default']) && ($_GET['maptype_default'] == 'ROADMAP' || $_GET['maptype_default'] == 'HYBRID')) {
            if ($result->rowCount() > 0) {
                $this->db_functions->update_settings('google_map_type', $_GET['maptype_default']);
                $maptype_def = $_GET['maptype_default'];
            } else {
                $sql = "INSERT INTO humo_settings SET setting_variable='google_map_type', setting_value='" . $_GET['maptype_default'] . "'";
                $this->dbh->query($sql);
                $maptype_def = $_GET['maptype_default'];
            }
        } else {
            if ($result->rowCount() > 0) {
                $maptype_default = $result->fetch();
                $maptype_def = $maptype_default['setting_value'];
            } else {
                $maptype_def = "ROADMAP";
            }
        }
        return $maptype_def;
    }
    */

    public function get_slider(): string
    {
        $query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_default_pos' ";
        $result = $this->dbh->query($query);
        if (isset($_GET['slider_default']) && ($_GET['slider_default'] == 'off' || $_GET['slider_default'] == 'all')) {
            if ($result->rowCount() > 0) {
                $result = $this->db_functions->update_settings('gslider_default_pos', $_GET['slider_default']);
                $sl_def = $_GET['slider_default'];
            } else {
                $sql = "INSERT INTO humo_settings SET setting_variable='gslider_default_pos', setting_value='" . $_GET['slider_default'] . "'";
                $this->dbh->query($sql);
                $sl_def = $_GET['slider_default'];
            }
        } else {
            if ($result->rowCount() > 0) {
                $sl_default_pos = $result->fetch();
                $sl_def = $sl_default_pos['setting_value'];
            } else {
                $sl_def = "all";
            }
        }
        return $sl_def;
    }

    public function get_geo_tree_id(): int
    {
        $check_tree_id = '';
        if (isset($_SESSION['geo_tree_id']) && is_numeric($_SESSION['geo_tree_id'])) {
            $check_tree_id = $_SESSION['geo_tree_id'];
        }
        if (isset($_POST['tree_id']) && is_numeric($_POST['tree_id'])) {
            $check_tree_id = $_POST['tree_id'];
        }

        $tree_id = 0;
        $_SESSION['geo_tree_id'] = '';
        // *** Double check if tree_id is a valid family tree ***
        if ($check_tree_id) {
            $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_id='" . $check_tree_id . "'";
            $tree_search_result = $this->dbh->query($tree_search_sql);
            $tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ);
            if ($tree_searchDb->tree_id == $check_tree_id) {
                $tree_id = $check_tree_id;
                $_SESSION['geo_tree_id'] = $tree_id;
            }
        }

        return $tree_id;
    }
}
