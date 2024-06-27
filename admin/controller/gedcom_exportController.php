<?php
require_once __DIR__ . "/../models/gedcom_export.php";

class Gedcom_exportController
{
    public function detail($tree_id)
    {
        $gedcom_exportModel = new GedcomExportModel();

        $export['part_tree'] = $gedcom_exportModel->get_part_tree();

        // *** Name of GEDCOM file: 2023_02_10_12_55_tree_x.ged ***
        $export['file_name'] = date('Y_m_d_H_i') . '_tree_' . $tree_id . '.ged';

        $export['path'] = $gedcom_exportModel->get_path();

        return $export;
    }
}
