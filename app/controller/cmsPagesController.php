<?php
class CmsPagesController
{
    private $dbh, $user;

    public function __construct($dbh, $user)
    {
        $this->dbh = $dbh;
        $this->user = $user;
    }

    public function list()
    {
        $CMS_pagesModel = new CmsPagesModel($this->dbh);

        $authorised = $CMS_pagesModel->getCMS_pagesAuthorised($this->user);
        $pages = $CMS_pagesModel->getPages($this->dbh);
        $menu = $CMS_pagesModel->getMenu($this->dbh);
        $pages_menu = $CMS_pagesModel->getPages_menu($this->dbh);
        $page = $CMS_pagesModel->getPage($this->dbh);
        return array(
            "authorised" => $authorised,
            "pages" => $pages,
            "menu" => $menu,
            "pages_menu" => $pages_menu,
            "page" => $page,
            "title" => __('Address')
        );
    }
}
