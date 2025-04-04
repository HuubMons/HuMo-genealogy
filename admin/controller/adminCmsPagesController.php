<?php
class AdminCmsPagesController
{
    public function detail($dbh)
    {
        $CMS_pagesModel = new AdminCmsPagesModel($dbh);

        $edit_cms_pages['menu_tab'] = $CMS_pagesModel->menu_tab();

        $CMS_pagesModel->add_change_page($dbh);

        $edit_cms_pages['page_menu_id'] = $CMS_pagesModel->get_page_menu_id();

        $edit_cms_pages['select_page'] = $CMS_pagesModel->get_select_page();

        $CMS_pagesModel->update_pages($dbh);

        if ($edit_cms_pages['menu_tab'] === 'pages') {
            $CMS_pagesModel->check_pages_in_category($dbh);

            $get_page = $CMS_pagesModel->get_page($dbh);
            $edit_cms_pages = array_merge($edit_cms_pages, $get_page);

            $get_categories = $CMS_pagesModel->get_categories($dbh);
            $edit_cms_pages = array_merge($edit_cms_pages, $get_categories);
        }

        return $edit_cms_pages;
    }
}
