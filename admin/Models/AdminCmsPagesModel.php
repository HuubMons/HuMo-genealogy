<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class AdminCmsPagesModel extends AdminBaseModel
{
    private $select_page = 0;
    private $page_menu_id = 0;

    public function get_page_menu_id()
    {
        return $this->page_menu_id;
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

    public function add_change_page()
    {
        // *** Save or add page ***
        if (isset($_POST['add_page']) || isset($_POST['change_page'])) {
            $page_status = "";
            if (isset($_POST['page_status']) && !empty($_POST['page_status'])) {
                $page_status = '1';
            }

            if ($_POST['page_menu_id'] && is_numeric($_POST['page_menu_id'])) {
                $this->page_menu_id = $_POST['page_menu_id'];
            }

            // *** Generate new order numer, needed for new page or moved page ***
            $page_order = '1';
            $ordersql = $this->dbh->query("SELECT page_order FROM humo_cms_pages ORDER BY page_order DESC LIMIT 0,1");
            $orderDb = $ordersql->fetch(PDO::FETCH_OBJ);
            if (isset($orderDb->page_order)) {
                $page_order = $orderDb->page_order + 1;
            }

            if (isset($_POST['add_page'])) {
                $sql = "INSERT INTO humo_cms_pages (page_order, page_status, page_menu_id, page_title, page_text)
                    VALUES (:page_order, :page_status, :page_menu_id, :page_title, :page_text)";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':page_order' => $page_order,
                    ':page_status' => $page_status,
                    ':page_menu_id' => $this->page_menu_id,
                    ':page_title' => $_POST['page_title'],
                    ':page_text' => $_POST['page_text']
                ]);
            } else {
                $sql = "UPDATE humo_cms_pages SET ";
                $params = [];

                if ($this->page_menu_id != $_POST['page_menu_id_old']) {
                    // *** Page is moved to another category, use new page_order ***
                    $sql .= "page_order = :page_order, ";
                    $params[':page_order'] = $page_order;
                }

                $sql .= "page_status = :page_status,
                    page_menu_id = :page_menu_id,
                    page_title = :page_title,
                    page_text = :page_text ";

                $params[':page_status'] = $page_status;
                $params[':page_menu_id'] = $this->page_menu_id;
                $params[':page_title'] = $_POST['page_title'];
                $params[':page_text'] = $_POST['page_text'];

                if (isset($_POST['change_page']) && is_numeric($_POST['page_id'])) {
                    $sql .= "WHERE page_id = :page_id";
                    $params[':page_id'] = $_POST['page_id'];
                    $this->select_page = $_POST['page_id']; // *** Show changed page ***
                }

                $stmt = $this->dbh->prepare($sql);
                $stmt->execute($params);
            }

            if (isset($_POST['add_page'])) {
                $qry = $this->dbh->query("SELECT * FROM humo_cms_pages ORDER BY page_id DESC LIMIT 0,1");
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

    public function get_page()
    {
        if ($this->select_page != 0) {
            $qry = $this->dbh->query("SELECT * FROM humo_cms_pages WHERE page_id=" . $this->select_page);
            $cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ);
            $edit_cms_pages['page_id'] = $cms_pagesDb->page_id;
            $edit_cms_pages['page_text'] = $cms_pagesDb->page_text;
            $edit_cms_pages['page_status'] = $cms_pagesDb->page_status;
            $edit_cms_pages['page_title'] = $cms_pagesDb->page_title;
            $edit_cms_pages['page_menu_id'] = $cms_pagesDb->page_menu_id;
            $edit_cms_pages['page_counter'] = $cms_pagesDb->page_counter;
        } else {
            // *** Add new page ***
            $edit_cms_pages['page_id'] = '';
            $edit_cms_pages['page_text'] = '';
            $edit_cms_pages['page_status'] = '1';
            $edit_cms_pages['page_title'] = __('Page title');
            $edit_cms_pages['page_menu_id'] = '';
            $edit_cms_pages['page_counter'] = '';
        }
        return $edit_cms_pages;
    }

    public function update_pages()
    {
        // *** Move pages ***
        if (isset($_GET['page_up']) && is_numeric($_GET['page_up']) && is_numeric($_GET['select_page'])) {
            $sql = "UPDATE humo_cms_pages as table1, humo_cms_pages as table2
                SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
                WHERE table1.page_id=:page_up AND table2.page_id=:select_page";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':page_up' => $_GET['page_up'],
                ':select_page' => $_GET['select_page']
            ]);
        }

        // *** Page up ***
        if (isset($_GET['page_down']) && is_numeric($_GET['page_down']) && is_numeric($_GET['menu_id'])) {
            $sql = "UPDATE humo_cms_pages as table1, humo_cms_pages as table2
                SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
                WHERE table1.page_order=:page_down AND table1.page_menu_id=:menu_id
                AND table2.page_order=:page_down_next AND table2.page_menu_id=:menu_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':page_down' => $_GET['page_down'],
                ':page_down_next' => $_GET['page_down'] + 1,
                ':menu_id' => $_GET['menu_id']
            ]);
        }

        // *** Remove page ***
        if (isset($_POST['page_remove2']) && is_numeric($_POST['page_id'])) {
            $sql = "DELETE FROM humo_cms_pages WHERE page_id = :page_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([':page_id' => $_POST['page_id']]);
        }

        // *** Save or add menu ***
        if (isset($_POST['add_menu'])) {
            $menu_order = '1';
            $datasql = $this->dbh->query("SELECT menu_id FROM humo_cms_menu");
            if ($datasql) {
                // *** Count lines in query ***
                $menu_order = $datasql->rowCount() + 1;
            }

            $sql = "INSERT INTO humo_cms_menu (menu_order, menu_name) VALUES (:menu_order, :menu_name)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':menu_order' => $menu_order,
                ':menu_name' => $_POST['menu_name']
            ]);
        }

        if (isset($_POST['change_menu']) && is_numeric($_POST['menu_id'])) {
            $sql = "UPDATE humo_cms_menu SET menu_name = :menu_name WHERE menu_id = :menu_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':menu_name' => $_POST['menu_name'],
                ':menu_id' => $_POST['menu_id']
            ]);
        }

        if (isset($_GET['menu_up']) && is_numeric($_GET['menu_up'])) {
            $sql = "UPDATE humo_cms_menu as table1, humo_cms_menu as table2
                SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
                WHERE table1.menu_order='" . $_GET['menu_up'] . "' AND table2.menu_order='" . $_GET['menu_up'] - 1 . "'";
            $this->dbh->query($sql);
        }
        if (isset($_GET['menu_down']) && is_numeric($_GET['menu_down'])) {
            $sql = "UPDATE humo_cms_menu as table1, humo_cms_menu as table2
                SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
                WHERE table1.menu_order='" . $_GET['menu_down'] . "' AND table2.menu_order='" . $_GET['menu_down'] + 1 . "'";
            $this->dbh->query($sql);
        }

        if (isset($_POST['menu_remove2']) && is_numeric($_POST['menu_id'])) {
            $sql = "DELETE FROM humo_cms_menu WHERE menu_id='" . $_POST['menu_id'] . "'";
            $this->dbh->query($sql);

            // *** Re-order menu's ***
            $repair_order = 1;
            $item = $this->dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_cms_menu SET menu_order='" . $repair_order . "' WHERE menu_id=" . $itemDb->menu_id;
                $this->dbh->query($sql);
                $repair_order++;
            }
        }
    }

    // *** Restore order numbering (if page is moved to another category) ***
    public function check_pages_in_category()
    {
        $page_nr = 0;
        $page_menu_id = 0;
        $qry = $this->dbh->query("SELECT page_id,page_menu_id,page_order FROM humo_cms_pages ORDER BY page_menu_id, page_order");
        while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if ($cms_pagesDb->page_menu_id > 0 && $page_menu_id != $cms_pagesDb->page_menu_id) {
                $page_nr = 0;
                $page_menu_id = $cms_pagesDb->page_menu_id;
            }
            $page_nr++;

            // *** Restore order numbering (if page is moved to another category) ***
            if ($page_nr != $cms_pagesDb->page_order) {
                $sql = "UPDATE humo_cms_pages SET page_order='" . $page_nr . "' WHERE page_id='" . $cms_pagesDb->page_id . "'";
                $this->dbh->query($sql);
            }
        }
    }

    public function get_categories()
    {
        $edit_cms_pages = [];

        // *** Get menu names ***
        $qry_menu = $this->dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
        while ($menuItem = $qry_menu->fetch(PDO::FETCH_OBJ)) {
            // *** Get pages ***
            $qry = $this->dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id = " . $menuItem->menu_id . " ORDER BY page_order");
            $count_pages = $qry->rowCount();
            while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $edit_cms_pages['menu_page_id'][$menuItem->menu_id][] = $cms_pagesDb->page_id;

                if ($cms_pagesDb->page_title) {
                    $page_title = $cms_pagesDb->page_title;
                } else {
                    $page_title = '[' . __('No page title') . ']';
                }
                $edit_cms_pages['menu_page_title'][$menuItem->menu_id][$cms_pagesDb->page_id] = $page_title;
            }

            // *** Only add category in array if there are pages ***
            if ($count_pages > 0) {
                $edit_cms_pages['menu_id'][] = $menuItem->menu_id;
                $edit_cms_pages['menu_name'][$menuItem->menu_id] = $menuItem->menu_name;
                $edit_cms_pages['menu_nr_pages'][$menuItem->menu_id] = $count_pages;
            }
        }

        // *** Also get pages without menu ***
        $qry = $this->dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id = 0 ORDER BY page_order");
        $count_pages = $qry->rowCount();
        while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $edit_cms_pages['menu_page_id'][0][] = $cms_pagesDb->page_id;

            if ($cms_pagesDb->page_title) {
                $page_title = $cms_pagesDb->page_title;
            } else {
                $page_title = '[' . __('No page title') . ']';
            }
            $edit_cms_pages['menu_page_title'][0][$cms_pagesDb->page_id] = $page_title;
        }

        // *** Only add category in array if there are pages ***
        if ($count_pages > 0) {
            $edit_cms_pages['menu_id'][] = 0;
            $edit_cms_pages['menu_name'][0] = '* ' . __('No menu selected') . ' *';
            $edit_cms_pages['menu_nr_pages'][0] = $count_pages;
        }

        // *** Also get pages using 9999 menu ***
        $qry = $this->dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id = 9999 ORDER BY page_order");
        $count_pages = $qry->rowCount();
        while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $edit_cms_pages['menu_page_id'][9999][] = $cms_pagesDb->page_id;

            if ($cms_pagesDb->page_title) {
                $page_title = $cms_pagesDb->page_title;
            } else {
                $page_title = '[' . __('No page title') . ']';
            }
            $edit_cms_pages['menu_page_title'][9999][$cms_pagesDb->page_id] = $page_title;
        }

        // *** Only add category in array if there are pages ***
        if ($count_pages > 0) {
            $edit_cms_pages['menu_id'][] = 9999;
            $edit_cms_pages['menu_name'][9999] = '* ' . __('Hide page in menu') . ' *';
            $edit_cms_pages['menu_nr_pages'][9999] = $count_pages;
        }

        if (!isset($edit_cms_pages['menu_id'])) {
            $edit_cms_pages['menu_id'] = [];
        }

        return $edit_cms_pages;
    }
}
