<?php
class AdminCmsPagesController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $CMS_pagesModel = new AdminCmsPagesModel($this->admin_config);

        $edit_cms_pages['menu_tab'] = $CMS_pagesModel->menu_tab();
        $CMS_pagesModel->add_change_page();
        $edit_cms_pages['page_menu_id'] = $CMS_pagesModel->get_page_menu_id();
        $edit_cms_pages['select_page'] = $CMS_pagesModel->get_select_page();
        $CMS_pagesModel->update_pages();

        if ($edit_cms_pages['menu_tab'] === 'pages') {
            $CMS_pagesModel->check_pages_in_category();

            $get_page = $CMS_pagesModel->get_page();
            $edit_cms_pages = array_merge($edit_cms_pages, $get_page);

            $get_categories = $CMS_pagesModel->get_categories();
            $edit_cms_pages = array_merge($edit_cms_pages, $get_categories);
        }

        return $edit_cms_pages;
    }
}
