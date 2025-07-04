<?php
class RenamePlaceController
{
    private $editor_cls;
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
        $this->editor_cls = new Editor_cls;
    }

    public function detail(): array
    {
        $renamePlaceModel = new RenamePlaceModel($this->admin_config);

        $renamePlaceModel->update_place($this->editor_cls);
        $place['result'] = $renamePlaceModel->get_query();
        $place['select'] = $renamePlaceModel->get_place_select();

        return $place;
    }
}
