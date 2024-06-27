<?php
class GedcomExportModel
{
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
}
