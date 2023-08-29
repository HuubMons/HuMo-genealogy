<?php

/**
 * Added in in august 2023.
 * 
 * Example to get link: $link = $link_cls->get_link($uri_path, 'tree_index', $tree_id);
 */

class Link_cls
{
    public $path = '';

    public function __construct($path = '')
    {
        $this->path = $path;
    }

    public function get_link($change_path, $page, $tree_id = NULL, $add_seperator = false)
    {
        global $humo_option;

        $path = $this->path;
        if ($change_path != '') $path = $change_path;

        if ($humo_option["url_rewrite"] == "j") {
            $link = $path . 'index'; // *** Default value ***

            if ($page == 'cms_pages') {
                $link = $path . 'cms_pages';
            }

            if ($page == 'cookies') {
                $link = $path . 'cookies';
            }

            if ($page == 'help') {
                $link = $path . 'help';
            }

            if ($page == 'index') {
                $link = $path . 'index';
            }

            if ($page == 'login') {
                $link = $path . 'login';
            }

            if ($page == 'logoff') {
                $link = $path . 'index?log_off=1';
            }

            //if ($page == 'persons') {
            //    $link = $path . 'list.php';
            //}

            if ($page == 'photoalbum') {
                $link = $path . 'photoalbum';
            }

            if ($page == 'register') {
                $link = $path . 'register';
            }

            if ($page == 'tree_index') {
                $link = $path . 'tree_index';
            }

            if ($tree_id) $link .= '/' . $tree_id;
            if ($add_seperator) $link .= '?';
        } else {
            $seperator = '?';
            $link = $path . 'index.php'; // *** Default value ***

            if ($page == 'cms_pages') {
                $link = $path . 'index.php?page=cms_pages';
                $seperator = '&amp;';
            }

            if ($page == 'cookies') {
                $link = $path . 'index.php?page=cookies';
                $seperator = '&amp;';
            }

            if ($page == 'help') {
                $link = $path . 'index.php?page=help';
                $seperator = '&amp;';
            }

            if ($page == 'index') {
                $link = $path . 'index.php';
            }

            if ($page == 'login') {
                $link = $path . 'login.php';
            }

            if ($page == 'logoff') {
                $link = $path . 'index?log_off=1';
            }

            //if ($page == 'persons') {
            //    $link = $path . 'list.php';
            //}

            if ($page == 'photoalbum') {
                $link = $path . 'photoalbum.php';
                //$link = $path . 'index.php?page=photoalbum';
                //$seperator = '&amp;';
            }

            if ($page == 'register') {
                $link = $path . 'index.php?page=register';
                $seperator = '&amp;';
            }

            if ($page == 'tree_index') {
                $link = $path . 'index.php?page=tree_index';
                $seperator = '&amp;';
            }

            if ($tree_id) {
                $link .= $seperator . 'tree_id=' . $tree_id;
                $seperator = '&amp;';
            }
            if ($add_seperator) $link .= $seperator;
        }
        return $link;
    }
}
