<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class GedcomExportModel extends AdminBaseModel
{
    private $submit_name;
    private $submit_address;
    private $submit_country;
    private $submit_mail;

    public function __construct($admin_config)
    {
        parent::__construct($admin_config);
        $this->submit_name = $this->humo_option["gedcom_submit_name"];
        $this->submit_address = $this->humo_option["gedcom_submit_address"];
        $this->submit_country = $this->humo_option["gedcom_submit_country"];
        $this->submit_mail = $this->humo_option["gedcom_submit_mail"];
    }

    public function get_part_tree(): string
    {
        $part_tree = '';
        if (isset($_POST['part_tree']) and $_POST['part_tree']) {
            $part_tree = $_POST['part_tree'];
        }
        return $part_tree;
    }

    public function get_path(): string
    {
        $path = 'gedcom_files/';
        // *** FOR TESTING PURPOSES ONLY ***
        if (file_exists("../../gedcom-bestanden")) {
            $path = '../../gedcom-bestanden/';
        }
        if (file_exists("../../../gedcom-bestanden")) {
            $path = '../../../gedcom-bestanden/';
        }
        return $path;
    }

    public function set_submit_name(): void
    {
        if ($this->submit_name == '') {
            $tree_result = $this->dbh->query("SELECT tree_owner FROM humo_trees WHERE tree_id='" . $this->tree_id . "'");
            $treeDb = $tree_result->fetch(PDO::FETCH_OBJ);
            if ($treeDb->tree_owner) {
                $this->submit_name = $treeDb->tree_owner;
            }
        }
    }

    public function set_submitter(): void
    {
        // *** Update submitter data ***
        if (isset($_POST['gedcom_submit_name'])) {
            $this->db_functions->update_settings('gedcom_submit_name', $_POST["gedcom_submit_name"]);
            $this->submit_name = $_POST["gedcom_submit_name"];

            $this->db_functions->update_settings('gedcom_submit_address', $_POST["gedcom_submit_address"]);
            $this->submit_address = $_POST["gedcom_submit_address"];

            $this->db_functions->update_settings('gedcom_submit_country', $_POST["gedcom_submit_country"]);
            $this->submit_country = $_POST["gedcom_submit_country"];

            $this->db_functions->update_settings('gedcom_submit_mail', $_POST["gedcom_submit_mail"]);
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
