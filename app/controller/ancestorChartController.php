<?php
class AncestorChartController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list($id): array
    {
        $get_ancestorModel = new AncestorModel($this->config);

        $main_person = $get_ancestorModel->getMainPerson2($id);
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor chart', $main_person);
        $get_ancestors = $get_ancestorModel->get_ancestors($main_person);

        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "title" => __('Ancestor sheet')
        );

        return array_merge($data, $get_ancestors);
    }
}
