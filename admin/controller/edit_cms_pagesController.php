<?php
require_once __DIR__ . "/../models/edit_cms_pages.php";

class edit_cms_pagesController
{
    public function detail($dbh)
    {
        $CMS_pagesModel = new CMS_pagesModel($dbh);

        $edit_cms_pages['menu_tab'] = $CMS_pagesModel->menu_tab();

        $CMS_pagesModel->add_change_page($dbh);

        $edit_cms_pages['select_page'] = $CMS_pagesModel->get_select_page();

        $CMS_pagesModel->update_pages($dbh);

        if ($edit_cms_pages['menu_tab'] === 'pages') {
            $edit_cms_pages['pages_in_category'] = $CMS_pagesModel->get_pages_in_category($dbh);
        }

        return $edit_cms_pages;
    }
}
