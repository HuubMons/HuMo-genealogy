<?php
require_once __DIR__ . "/../models/cms_pages.php";

class edit_cms_pagesController
{
    public function detail($dbh)
    {
        $CMS_pagesModel = new CMS_pagesModel($dbh);

        $cms_pages['menu_tab'] = $CMS_pagesModel->menu_tab();

        $CMS_pagesModel->add_change_page($dbh);

        $cms_pages['select_page'] = $CMS_pagesModel->get_select_page();

        $CMS_pagesModel->update_pages($dbh);

        return $cms_pages;
    }
}
