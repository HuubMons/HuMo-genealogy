<?php
class AdminCmsPagesController
{
    public function detail($dbh)
    {
        $CMS_pagesModel = new AdminCmsPagesModel($dbh);

        $edit_cms_pages['menu_tab'] = $CMS_pagesModel->menu_tab();

        $CMS_pagesModel->add_change_page($dbh);

        $edit_cms_pages['select_page'] = $CMS_pagesModel->get_select_page();

        $CMS_pagesModel->update_pages($dbh);

        if ($edit_cms_pages['menu_tab'] === 'pages') {
            $edit_cms_pages['pages_in_category'] = $CMS_pagesModel->get_pages_in_category($dbh);

            $get_page = $CMS_pagesModel->get_page($dbh);
            $edit_cms_pages = array_merge($edit_cms_pages, $get_page);
        }

        return $edit_cms_pages;
    }
}
