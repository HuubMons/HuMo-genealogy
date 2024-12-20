<?php
class AncestorChartController
{
    private $dbh, $db_functions;

    public function __construct($dbh, $db_functions)
    {
        $this->dbh = $dbh;
        $this->db_functions = $db_functions;
    }

    public function list($tree_id)
    {
        $get_ancestorModel = new AncestorModel($this->dbh);

        $main_person = $get_ancestorModel->getMainPerson();
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor chart', $tree_id, $main_person);
        $get_ancestors = $get_ancestorModel->get_ancestors($this->db_functions, $main_person);

        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "title" => __('Ancestor sheet')
        );

        return array_merge($data, $get_ancestors);
    }
}
