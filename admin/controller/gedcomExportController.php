<?php
class GedcomExportController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $gedcom_exportModel = new GedcomExportModel($this->admin_config);

        $export['part_tree'] = $gedcom_exportModel->get_part_tree();
        // *** Name of GEDCOM file: 2023_02_10_12_55_tree_x.ged ***
        $export['file_name'] = date('Y_m_d_H_i') . '_tree_' . $this->admin_config['tree_id'] . '.ged';
        $export['path'] = $gedcom_exportModel->get_path();
        $gedcom_exportModel->set_submit_name();
        $gedcom_exportModel->set_submitter();

        $export['submit_name'] = $gedcom_exportModel->get_submit_name();
        $export['submit_address'] = $gedcom_exportModel->get_submit_address();
        $export['submit_country'] = $gedcom_exportModel->get_submit_country();
        $export['submit_mail'] = $gedcom_exportModel->get_submit_mail();

        return $export;
    }
}
