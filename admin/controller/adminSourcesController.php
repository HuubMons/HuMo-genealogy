<?php
class AdminSourcesController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $editSourcesModel = new AdminSourcesModel($this->admin_config);

        $editSources['pers_gedcomnumber'] = $editSourcesModel->get_pers_gedcomnumber();
        $editSources['fam_gedcomnumber'] = $editSourcesModel->get_fam_gedcomnumber();

        // *** Needed for event sources ***
        $editSources['connect_kind'] = $editSourcesModel->get_connect_kind();
        $editSources['connect_sub_kind'] = $editSourcesModel->get_connect_sub_kind();
        // *** Needed for event sources ***
        $editSources['connect_connect_id'] = $editSourcesModel->get_connect_connect_id();

        $header_connect_kind = $editSourcesModel->get_header_connect_kind($editSources['connect_sub_kind']);
        $editSources = array_merge($editSources, $header_connect_kind);

        return $editSources;
    }
}
