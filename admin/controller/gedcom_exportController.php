<?php
require_once __DIR__ . "/../models/gedcom_export.php";

class Gedcom_exportController
{
    public function detail($dbh, $tree_id, $humo_option, $db_functions)
    {
        $gedcom_exportModel = new GedcomExportModel($humo_option);

        $export['part_tree'] = $gedcom_exportModel->get_part_tree();

        // *** Name of GEDCOM file: 2023_02_10_12_55_tree_x.ged ***
        $export['file_name'] = date('Y_m_d_H_i') . '_tree_' . $tree_id . '.ged';

        $export['path'] = $gedcom_exportModel->get_path();

        $gedcom_exportModel->set_submit_name($dbh, $tree_id);

        $gedcom_exportModel->set_submitter($db_functions);

        $export['submit_name'] = $gedcom_exportModel->get_submit_name();
        $export['submit_address'] = $gedcom_exportModel->get_submit_address();
        $export['submit_country'] = $gedcom_exportModel->get_submit_country();
        $export['submit_mail'] = $gedcom_exportModel->get_submit_mail();

        return $export;
    }
}
