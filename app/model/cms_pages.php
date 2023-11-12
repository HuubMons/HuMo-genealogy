<?php
class CMS_pagesModel
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function getCMS_pagesAuthorised($user)
    {
        $authorised = '';
        if ($user['group_menu_cms'] != 'y') {
            $authorised = __('You are not authorised to see this page.');
        }
        return $authorised;
    }

    // *** Get pages without menu ***
    public function getPages($dbh)
    {
        $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='0' AND page_status!='' ORDER BY page_order");
        $pages = $page_qry->fetchAll(PDO::FETCH_OBJ);
        return $pages;
    }

    // *** Get menu ***
    public function getMenu($dbh)
    {
        $qry = $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
        $menu = $qry->fetchAll(PDO::FETCH_OBJ);
        return $menu;
    }

    // *** Get all pages for menu ***
    public function getPages_menu($dbh)
    {
        $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id!='0' AND page_status!='' ORDER BY page_order");
        $pages = $page_qry->fetchAll(PDO::FETCH_OBJ);
        return $pages;
    }

    public function getPage($dbh)
    {
        if (isset($_GET['select_page']) and (is_numeric($_GET['select_page']))) {
            $select_page = $_GET['select_page'];
        } elseif (isset($_GET['id']) and (is_numeric($_GET['id']))) {
            // *** If url_rewrite is used ***
            $select_page = $_GET['id'];
        } else {
            // *** First page in a menu ***
            $page_qry = $dbh->query("SELECT * FROM humo_cms_menu, humo_cms_pages
                WHERE page_status!='' AND page_menu_id=menu_id
                ORDER BY menu_order, page_order ASC LIMIT 0,1");
            $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
            if (isset($cms_pagesDb->page_id)) $select_page = $cms_pagesDb->page_id;

            // *** First pages without a menu (if present) ***
            $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id=0 ORDER BY page_order ASC LIMIT 0,1");
            $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
            if (isset($cms_pagesDb->page_id)) $select_page = $cms_pagesDb->page_id;
        }
        $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='" . safe_text_db($select_page) . "' AND page_status!=''");
        $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);

        // *** Raise page counter ***
        // Only change counter of page once every session
        $session_counter[] = '';
        $visited = 0;
        if (isset($_SESSION["opslag_sessieteller"])) $session_counter = $_SESSION["opslag_sessieteller"];
        // TODO improve code. Check if value is in array.
        for ($i = 0; $i <= count($session_counter) - 1; $i++) {
            if (@$cms_pagesDb->page_id == $session_counter[$i]) {
                $visited = 1;
                break;
            }
        }
        // *** Only raise counter at 1st visit of a session ***
        if ($visited == 0) {
            $session_counter[] = $cms_pagesDb->page_id;
            $_SESSION["opslag_sessieteller"] = $session_counter;
            $itemteller = $cms_pagesDb->page_counter + 1;
            $sql = "UPDATE humo_cms_pages SET page_counter='" . $itemteller . "' WHERE page_id=" . $cms_pagesDb->page_id . "";
            $dbh->query($sql);
        }

        return $cms_pagesDb->page_text;
    }
}
