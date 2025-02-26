<?php
class AdminSourceController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new Editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editSourceModel = new AdminSourceModel($dbh);
        $editSourceModel->set_source_id($dbh, $tree_id);
        $editSourceModel->update_source($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editSource['source_id'] = $editSourceModel->get_source_id();

        $editSource['editor_cls'] = $this->editor_cls;

        $sources = $editSourceModel->get_sources($dbh, $tree_id);
        $editSource = array_merge($editSource, $sources);

        return $editSource;
    }
}
