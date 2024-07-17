<?php
class GedcomExportModel
{
    private $submit_name;
    private $submit_address;
    private $submit_country;
    private $submit_mail;

    public function __construct($humo_option)
    {
        $this->submit_name = $humo_option["gedcom_submit_name"];
        $this->submit_address = $humo_option["gedcom_submit_address"];
        $this->submit_country = $humo_option["gedcom_submit_country"];
        $this->submit_mail = $humo_option["gedcom_submit_mail"];
    }

    public function get_part_tree()
    {
        $part_tree = '';
        if (isset($_POST['part_tree']) and $_POST['part_tree']) {
            $part_tree = $_POST['part_tree'];
        }
        return $part_tree;
    }

    public function get_path()
    {
        $path = 'gedcom_files/';
        // *** FOR TESTING PURPOSES ONLY ***
        if (@file_exists("../../gedcom-bestanden")) {
            $path = '../../gedcom-bestanden/';
        }
        if (@file_exists("../../../gedcom-bestanden")) {
            $path = '../../../gedcom-bestanden/';
        }
        return $path;
    }

    public function set_submit_name($dbh, $tree_id)
    {
        if ($this->submit_name == '') {
            //$tree_sql = "SELECT * FROM humo_trees WHERE tree_id='" . $tree_id . "'";
            $tree_result = $dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $tree_id . "'");
            $treeDb = $tree_result->fetch(PDO::FETCH_OBJ);
            if ($treeDb->tree_owner) {
                $this->submit_name = $treeDb->tree_owner;
            }
        }
    }

    public function set_submitter($db_functions)
    {
        // *** Update submitter data ***
        if (isset($_POST['gedcom_submit_name'])) {
            $db_functions->update_settings('gedcom_submit_name', $_POST["gedcom_submit_name"]);
            $this->submit_name = $_POST["gedcom_submit_name"];

            $db_functions->update_settings('gedcom_submit_address', $_POST["gedcom_submit_address"]);
            $this->submit_address = $_POST["gedcom_submit_address"];

            $db_functions->update_settings('gedcom_submit_country', $_POST["gedcom_submit_country"]);
            $this->submit_country = $_POST["gedcom_submit_country"];

            $db_functions->update_settings('gedcom_submit_mail', $_POST["gedcom_submit_mail"]);
            $this->submit_mail = $_POST["gedcom_submit_mail"];
        }
    }

    public function get_submit_name()
    {
        return $this->submit_name;
    }
    public function get_submit_address()
    {
        return $this->submit_address;
    }
    public function get_submit_country()
    {
        return $this->submit_country;
    }
    public function get_submit_mail()
    {
        return $this->submit_mail;
    }
}
