<?php
class CMS_pagesModel
{
    private $select_page;

    public function __construct()
    {
        $this->select_page = 0;
    }

    public function menu_tab()
    {
        // *** Show editor if page is choosen for first time ***
        $menu_tab = 'pages';

        // *** Show and edit menu's ***
        if (isset($_GET['cms_tab']) && $_GET['cms_tab'] == 'menu') {
            $menu_tab = 'menu';
        }
        if (isset($_POST['cms_tab']) && $_POST['cms_tab'] == 'menu') {
            $menu_tab = 'menu';
        }
        if (isset($_GET['select_menu'])) {
            $menu_tab = 'menu';
        }

        if (isset($_GET['cms_tab']) && $_GET['cms_tab'] == 'settings') {
            $menu_tab = 'settings';
        }
        if (isset($_POST['cms_settings'])) {
            $menu_tab = 'settings';
        }
        return $menu_tab;
    }

    public function add_change_page($dbh)
    {
        // *** Save or add page ***
        if (isset($_POST['add_page']) || isset($_POST['change_page'])) {
            $page_status = "";
            if (isset($_POST['page_status']) && !empty($_POST['page_status'])) {
                $page_status = '1';
            }
            $page_menu_id = $_POST['page_menu_id'];

            // *** Generate new order numer, needed for new page or moved page ***
            $page_order = '1';
            $ordersql = $dbh->query("SELECT page_order FROM humo_cms_pages ORDER BY page_order DESC LIMIT 0,1");
            //if ($ordersql) {
            $orderDb = $ordersql->fetch(PDO::FETCH_OBJ);
            if (isset($orderDb->page_order)) {
                $page_order = $orderDb->page_order + 1;
            }

            if (isset($_POST['add_page'])) {
                $sql = "INSERT INTO humo_cms_pages SET page_order='" . $page_order . "', ";
            } else {
                $sql = "UPDATE humo_cms_pages SET ";

                // *** If menu/ category is changed, use new page_order. Ordering for old category is restored later in script ***
                $page_menu_id = '0';
                if ($_POST['page_menu_id'] && is_numeric($_POST['page_menu_id'])) {
                    $page_menu_id = $_POST['page_menu_id'];
                }
                if ($page_menu_id != $_POST['page_menu_id_old']) {
                    // *** Page is moved to another category, use new page_order ***
                    $sql .= "page_order='" . $page_order . "',";
                }
            }

            $sql .= "page_status='" . $page_status . "',
                page_menu_id='" . safe_text_db($page_menu_id) . "',
                page_title='" . safe_text_db($_POST['page_title']) . "',
                page_text='" . safe_text_db($_POST['page_text']) . "'";

            if (isset($_POST['change_page']) && is_numeric($_POST['page_id'])) {
                $sql .= "WHERE page_id='" . $_POST['page_id'] . "'";
                $this->select_page = $_POST['page_id']; // *** Show changed page ***
            }

            $dbh->query($sql);

            if (isset($_POST['add_page'])) {
                $qry = $dbh->query("SELECT * FROM humo_cms_pages ORDER BY page_id DESC LIMIT 0,1");
                $cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ);
                $this->select_page = $cms_pagesDb->page_id; // *** Show newly added page ***
            }
        }
    }

    public function get_select_page()
    {
        if (isset($_GET["select_page"]) && is_numeric($_GET["select_page"])) {
            $this->select_page = $_GET["select_page"];
        }

        return $this->select_page;
    }

    public function update_pages($dbh)
    {
        // *** Move pages ***
        if (isset($_GET['page_up']) && is_numeric($_GET['page_up']) && is_numeric($_GET['select_page'])) {
            $sql = "UPDATE humo_cms_pages as table1, humo_cms_pages as table2
                SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
                WHERE table1.page_id='" . $_GET['page_up'] . "' AND table2.page_id='" . $_GET['select_page'] . "'";
            $dbh->query($sql);
        }
        // *** Page up ***
        if (isset($_GET['page_down']) && is_numeric($_GET['page_down']) && is_numeric($_GET['menu_id'])) {
            $sql = "UPDATE humo_cms_pages as table1, humo_cms_pages as table2
                SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
                WHERE table1.page_order='" . $_GET['page_down'] . "' AND table1.page_menu_id='" . $_GET['menu_id'] . "'
                AND table2.page_order='" . $_GET['page_down'] + 1 . "'  AND table2.page_menu_id='" . $_GET['menu_id'] . "'";
            $dbh->query($sql);
        }

        // *** Remove page ***
        if (isset($_POST['page_remove2']) && is_numeric($_POST['page_id'])) {
            $sql = "DELETE FROM humo_cms_pages WHERE page_id='" . $_POST['page_id'] . "'";
            $dbh->query($sql);
        }

        // *** Save or add menu ***
        if (isset($_POST['add_menu'])) {
            $menu_order = '1';
            $datasql = $dbh->query("SELECT menu_id FROM humo_cms_menu");
            if ($datasql) {
                // *** Count lines in query ***
                $menu_order = $datasql->rowCount() + 1;
            }

            $sql = "INSERT INTO humo_cms_menu SET menu_order='" . $menu_order . "', menu_name='" . safe_text_db($_POST['menu_name']) . "'";
            $dbh->query($sql);
        }

        if (isset($_POST['change_menu']) && is_numeric($_POST['menu_id'])) {
            $sql = "UPDATE humo_cms_menu SET menu_name='" . safe_text_db($_POST['menu_name']) . "' WHERE menu_id='" . $_POST['menu_id'] . "'";
            $dbh->query($sql);
        }

        if (isset($_GET['menu_up']) && is_numeric($_GET['menu_up'])) {
            $sql = "UPDATE humo_cms_menu as table1, humo_cms_menu as table2
                SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
                WHERE table1.menu_order='" . $_GET['menu_up'] . "' AND table2.menu_order='" . $_GET['menu_up'] - 1 . "'";
            $dbh->query($sql);
        }
        if (isset($_GET['menu_down']) && is_numeric($_GET['menu_down'])) {
            $sql = "UPDATE humo_cms_menu as table1, humo_cms_menu as table2
                SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
                WHERE table1.menu_order='" . $_GET['menu_down'] . "' AND table2.menu_order='" . $_GET['menu_down'] + 1 . "'";
            $dbh->query($sql);
        }

        if (isset($_POST['menu_remove2']) && is_numeric($_POST['menu_id'])) {
            $sql = "DELETE FROM humo_cms_menu WHERE menu_id='" . $_POST['menu_id'] . "'";
            @$dbh->query($sql);

            // *** Re-order menu's ***
            $repair_order = 1;
            $item = $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_cms_menu SET menu_order='" . $repair_order . "' WHERE menu_id=" . $itemDb->menu_id;
                $dbh->query($sql);
                $repair_order++;
            }
        }
    }

    public function get_pages_in_category($dbh)
    {
        // *** Count number of pages in categories (so correct down arrows can be shown) ***
        // *** Also restore order numbering (if page is moved to another category) ***
        $page_nr = 0;
        $page_menu_id = 0;
        $pages_in_category = [];
        $qry = $dbh->query("SELECT page_id,page_menu_id,page_order FROM humo_cms_pages ORDER BY page_menu_id, page_order");
        while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if (!isset($pages_in_category[$cms_pagesDb->page_menu_id])) {
                $pages_in_category[$cms_pagesDb->page_menu_id] = '1';
            } else {
                $pages_in_category[$cms_pagesDb->page_menu_id]++;
            }

            if ($cms_pagesDb->page_menu_id > 0 && $page_menu_id != $cms_pagesDb->page_menu_id) {
                $page_nr = 0;
                $page_menu_id = $cms_pagesDb->page_menu_id;
            }
            $page_nr++;

            // *** Restore order numbering (if page is moved to another category) ***
            if ($page_nr != $cms_pagesDb->page_order) {
                $sql = "UPDATE humo_cms_pages SET page_order='" . $page_nr . "' WHERE page_id='" . $cms_pagesDb->page_id . "'";
                $dbh->query($sql);
            }
        }
        return $pages_in_category;
    }
}
