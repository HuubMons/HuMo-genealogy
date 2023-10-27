<?php
require_once  __DIR__ . "/../model/cms_pages.php";

class cms_pagesController
{
    private $dbh, $user;

    public function __construct($dbh, $user)
    {
        $this->dbh = $dbh;
        $this->user = $user;
    }

    public function list()
    {
        $model = new CMS_pages($this->dbh);

        $authorised = $model->getCMS_pagesAuthorised($this->user);
        $pages = $model->getPages($this->dbh);
        $menu = $model->getMenu($this->dbh);
        $pages_menu = $model->getPages_menu($this->dbh);
        $page = $model->getPage($this->dbh);

        $data = array(
            "authorised" => $authorised,
            "pages" => $pages,
            "menu" => $menu,
            "pages_menu" => $pages_menu,
            "page" => $page,
            "title" => __('Address')
        );
        return $data;
    }
}
