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
        $gedcomExportModel = new GedcomExportModel($this->admin_config);

        $export['part_tree'] = $gedcomExportModel->get_part_tree();
        // *** Name of GEDCOM file: 2023_02_10_12_55_tree_x.ged ***
        $export['file_name'] = date('Y_m_d_H_i') . '_tree_' . $this->admin_config['tree_id'] . '.ged';
        $export['path'] = $gedcomExportModel->get_path();
        $gedcomExportModel->set_submit_name();
        $gedcomExportModel->set_submitter();

        $export['submit_name'] = $gedcomExportModel->get_submit_name();
        $export['submit_address'] = $gedcomExportModel->get_submit_address();
        $export['submit_country'] = $gedcomExportModel->get_submit_country();
        $export['submit_mail'] = $gedcomExportModel->get_submit_mail();

        return $export;
    }
}
