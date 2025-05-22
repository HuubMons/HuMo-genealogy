<?php
class CmsPagesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list(): array
    {
        $CMS_pagesModel = new CmsPagesModel($this->config);

        $authorised = $CMS_pagesModel->getCMS_pagesAuthorised();
        $pages = $CMS_pagesModel->getPages();
        $menu = $CMS_pagesModel->getMenu();
        $pages_menu = $CMS_pagesModel->getPages_menu();
        $page = $CMS_pagesModel->getPage();
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
