<?php
class AdminSourceController
{
    protected $admin_config;
    private $editor_cls;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
        $this->editor_cls = new Editor_cls;
    }

    public function detail(): array
    {
        $editSourceModel = new AdminSourceModel($this->admin_config);

        $editSourceModel->set_source_id();
        $editSourceModel->update_source($this->editor_cls);
        $editSource['source_id'] = $editSourceModel->get_source_id();

        $editSource['editor_cls'] = $this->editor_cls;

        $sources = $editSourceModel->get_sources();
        $editSource = array_merge($editSource, $sources);

        return $editSource;
    }
}
