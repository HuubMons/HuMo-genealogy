<?php
class TreesModel
{
    private $tree_id;

    public function set_tree_id($tree_id): void
    {
        $this->tree_id = $tree_id;

        if (isset($_POST['tree_id']) && is_numeric(($_POST['tree_id']))) {
            $this->tree_id = $_POST['tree_id'];
        }
    }
    public function get_tree_id()
    {
        return $this->tree_id;
    }

    public function get_language($selected_language)
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

    public function get_language2($language, $selected_language)
    {
        $language2 = $language;
        if ($language == 'default') {
            $language2 = $selected_language;
        }
        return $language2;
    }

    public function get_menu_tab()
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

    public function get_tree_pict_path($dbh, $tree_id)
    {
        $data2sql = $dbh->query("SELECT tree_pict_path FROM humo_trees WHERE tree_id=" . $tree_id);
        $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
        $tree_pict_path = $data2Db->tree_pict_path;
        //if (substr($data2Db->tree_pict_path, 0, 1) === '|') {
        //    $tree_pict_path = substr($trees['tree_pict_path'], 1);
        //}
        return $tree_pict_path;
    }

    public function update_tree($dbh, $db_functions): void
    {
        // *** Add family tree ***
        if (isset($_POST['add_tree_data'])) {
            // *** Find latest tree_prefix ***
            $found = '1';
            $i = 1;
            while ($found == '1') {
                $new_tree_prefix = 'humo' . $i . '_';
                $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='" . $new_tree_prefix . "'");
                $found = $datasql->rowCount();
                $i++;
            }

            // *** Get highest order number ***
            $tree_order = 1;
            $datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1");
            if ($datasql) {
                $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
                $tree_order = $dataDb->tree_order + 1;
            }

            $sql = "INSERT INTO humo_trees SET
                tree_order='" . $tree_order . "',
                tree_prefix='" . $new_tree_prefix . "',
                tree_persons='0',
                tree_families='0',
                tree_email='',
                tree_privacy='',
                tree_pict_path='|../pictures/'";
            $dbh->query($sql);

            $_SESSION['tree_prefix'] = $new_tree_prefix;

            $this->tree_id = $dbh->lastInsertId();
            $_SESSION['admin_tree_id'] = $this->tree_id;
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
                tree_email='" . safe_text_db($_POST['tree_email']) . "',
                tree_owner='" . safe_text_db($_POST['tree_owner']) . "',
                tree_pict_path='" . safe_text_db($tree_pict_path) . "',
                tree_privacy='" . safe_text_db($_POST['tree_privacy']) . "'
                WHERE tree_id=" . $this->tree_id;
            $dbh->query($sql);
        }

        if (isset($_POST['remove_tree2']) && is_numeric($_POST['tree_id'])) {
            $removeqry = 'SELECT * FROM humo_trees WHERE tree_id="' . $_POST['tree_id'] . '"';
            $removesql = $dbh->query($removeqry);
            $removeDb = $removesql->fetch(PDO::FETCH_OBJ);
            $remove = $removeDb->tree_prefix;

            // *** Re-order family trees ***
            $repair_order = $removeDb->tree_order;
            $item = $dbh->query("SELECT * FROM humo_trees WHERE tree_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_trees SET tree_order='" . ($itemDb->tree_order - 1) . "' WHERE tree_id=" . $itemDb->tree_id;
                $dbh->query($sql);
            }

            $sql = "DELETE FROM humo_trees WHERE tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove items from table family_tree_text ***
            $sql = "DELETE FROM humo_tree_texts WHERE treetext_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove persons ***
            $sql = "DELETE FROM humo_persons WHERE pers_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove families ***
            $sql = "DELETE FROM humo_families WHERE fam_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove sources ***
            $sql = "DELETE FROM humo_sources WHERE source_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove texts ***
            $sql = "DELETE FROM humo_texts WHERE text_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove connections ***
            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove addresses ***
            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove events ***
            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove statistics ***
            $sql = "DELETE FROM humo_stat_date WHERE stat_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove unprocessed tags ***
            $sql = "DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove cache ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable LIKE 'cache%' AND setting_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove admin favourites ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='" . $_POST['tree_id'] . "'";
            $dbh->query($sql);

            // *** Remove adjusted glider settings ***
            $sql = "DELETE FROM humo_settings WHERE setting_variable='gslider_" . $remove . "'";
            $dbh->query($sql);

            // *** Remove geo_tree settings for this tree ***
            $sql = "UPDATE humo_settings SET setting_value = REPLACE(setting_value, CONCAT('@'," . $_POST['tree_id'] . ",';'), '')  WHERE setting_variable='geo_trees'";
            $dbh->query($sql);

            // *** Remove tree_prefix of this tree from location table (humo2_birth, humo2_death, humo2_bapt, humo2_buried)  ***
            $loc_qry = "SELECT * FROM humo_location";
            $loc_result = $dbh->query($loc_qry);
            while ($loc_resultDb = $loc_result->fetch(PDO::FETCH_OBJ)) {
                if ($loc_resultDb->location_status && strpos($loc_resultDb->location_status, $remove) !== false) {   // only do this if the prefix appears
                    $stat_qry = "UPDATE humo_location SET location_status = REPLACE(REPLACE(REPLACE(REPLACE(location_status, CONCAT('" . $remove . "','birth'),''),CONCAT('" . $remove . "','death'),''),CONCAT('" . $remove . "','bapt'),''),CONCAT('" . $remove . "','buried'),'')  WHERE location_id = '" . $loc_resultDb->location_id . "'";
                    $dbh->query($stat_qry);
                }
            }

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
            $check_tree_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order LIMIT 0,1");
            $check_treeDb = $check_tree_sql->fetch(PDO::FETCH_OBJ);
            $check_tree_id = $check_treeDb->tree_id;

            // *** Double check tree_id and save tree id in session ***
            $_SESSION['admin_tree_id'] = '';
            if ($check_tree_id && $check_tree_id != '') {
                $get_treeDb = $db_functions->get_tree($check_tree_id);

                $this->tree_id = $get_treeDb->tree_id;
                $_SESSION['admin_tree_id'] = $this->tree_id;
            }
        }

        if (isset($_POST['add_tree_text'])) {
            $sql = "INSERT INTO humo_tree_texts SET
            treetext_tree_id='" . $this->tree_id . "',
            treetext_language='" . safe_text_db($_POST['language_tree']) . "',
            treetext_name='" . safe_text_db($_POST['treetext_name']) . "',
            treetext_mainmenu_text='" . safe_text_db($_POST['treetext_mainmenu_text']) . "',
            treetext_mainmenu_source='" . safe_text_db($_POST['treetext_mainmenu_source']) . "',
            treetext_family_top='" . safe_text_db($_POST['treetext_family_top']) . "',
            treetext_family_footer='" . safe_text_db($_POST['treetext_family_footer']) . "'";
            $dbh->query($sql);
        }

        if (isset($_POST['change_tree_text'])) {
            $sql = "UPDATE humo_tree_texts SET
            treetext_tree_id='" . $this->tree_id . "',
            treetext_language='" . safe_text_db($_POST['language_tree']) . "',
            treetext_name='" . safe_text_db($_POST['treetext_name']) . "',
            treetext_mainmenu_text='" . safe_text_db($_POST['treetext_mainmenu_text']) . "',
            treetext_mainmenu_source='" . safe_text_db($_POST['treetext_mainmenu_source']) . "',
            treetext_family_top='" . safe_text_db($_POST['treetext_family_top']) . "',
            treetext_family_footer='" . safe_text_db($_POST['treetext_family_footer']) . "'
            WHERE treetext_id=" . safe_text_db($_POST['treetext_id']);
            $dbh->query($sql);
        }

        // *** Add empty line ***
        if (isset($_POST['add_tree_data_empty'])) {
            // *** Get highest order number ***
            $tree_order = 1;
            $datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1");
            if ($datasql) {
                $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
                $tree_order = $dataDb->tree_order + 1;
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
            $dbh->query($sql);
        }

        // *** Change collation of tree ***
        if (isset($_POST['tree_collation'])) {
            $tree_collation = safe_text_db($_POST['tree_collation']);
            $dbh->query("ALTER TABLE humo_persons CHANGE `pers_lastname` `pers_lastname` VARCHAR(50) COLLATE " . $tree_collation . ";");
            $dbh->query("ALTER TABLE humo_persons CHANGE `pers_firstname` `pers_firstname` VARCHAR(50) COLLATE " . $tree_collation . ";");
            $dbh->query("ALTER TABLE humo_persons CHANGE `pers_prefix` `pers_prefix` VARCHAR(20) COLLATE " . $tree_collation . ";");
            //$dbh->query("ALTER TABLE humo_persons CHANGE `pers_callname` `pers_callname` VARCHAR(20) COLLATE ".$tree_collation.";");
            $dbh->query("ALTER TABLE humo_events CHANGE `event_event` `event_event` TEXT COLLATE " . $tree_collation . ";");
        }
    }
}
