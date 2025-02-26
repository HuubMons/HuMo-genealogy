<?php
class ListNamesController
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list_names($last_name, $uri_path)
    {
        $dbh = $this->config['dbh'];
        //$db_functions = $this->config['db_functions'];
        $tree_id = $this->config['tree_id'];
        $user = $this->config['user'];
        $humo_option = $this->config['humo_option'];
       
        $list_namesModel = new listNamesModel();

        $list_names['alphabet_array'] = $list_namesModel->getAlphabetArray($dbh, $tree_id, $user);
        $list_names['max_cols'] = $list_namesModel->getMaxCols();
        $list_names['max_names'] = $list_namesModel->getMaxNames();
        $list_names['last_name'] = $list_namesModel->get_last_name($last_name);
        $list_names["item"] = $list_namesModel->get_item();
        $list_names['start'] = $list_namesModel->get_start();

        $get_names = $list_namesModel->get_names($dbh, $tree_id, $user, $list_names);
        $list_names = array_merge($list_names, $get_names);

        $get_pagination = $list_namesModel->get_pagination($tree_id, $humo_option, $uri_path, $list_names);
        $list_names = array_merge($list_names, $get_pagination);

        return $list_names;
    }
}
