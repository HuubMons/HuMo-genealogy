<?php

class Router
{
    // *** REMARK: be aware of the order! Path "tree_index" must be used before "index" ***
    public $routes_array = [
        // *** Must be before address ***
        ['path' => 'addresses', 'title' => 'Addresses', 'page' => 'addresses', 'vars' => 'select_tree_id'],
        ['path' => 'address', 'title' => 'Address', 'page' => 'address', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_report_rtf', 'title' => 'Ancestor report', 'page' => 'ancestor_report_rtf', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_report', 'title' => 'Ancestor report', 'page' => 'ancestor_report', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_chart', 'title' => 'Ancestor chart', 'page' => 'ancestor_chart'],
        ['path' => 'ancestor_sheet', 'title' => 'Ancestor sheet', 'page' => 'ancestor_sheet'],
        ['path' => 'anniversary', 'title' => 'Birthday calendar', 'page' => 'anniversary'],
        ['path' => 'cms_pages', 'title' => 'Information', 'page' => 'cms_pages', 'vars' => 'id'],
        ['path' => 'cookies', 'title' => 'Cookie information', 'page' => 'cookies'],
        ['path' => 'descendant_report', 'title' => 'Descendants', 'page' => 'family', 'vars' => 'select_tree_id,id'],
        ['path' => 'descendant_chart', 'title' => 'Descendants', 'page' => 'descendant_chart', 'vars' => 'select_tree_id,id'],
        // *** Must be before family ***
        ['path' => 'family_rtf', 'title' => 'Family Page', 'page' => 'family_rtf'],
        ['path' => 'family', 'title' => 'Family Page', 'page' => 'family', 'vars' => 'select_tree_id,id'],
        ['path' => 'fanchart', 'title' => 'Fanchart', 'page' => 'fanchart'],
        ['path' => 'help', 'title' => 'Help', 'page' => 'help'],
        ['path' => 'hourglass', 'title' => 'Hourglass', 'page' => 'hourglass', 'vars' => 'select_tree_id,id'],
        // *** Must be before index ***
        ['path' => 'tree_index', 'title' => 'Family tree index', 'page' => 'tree_index', 'vars' => 'select_tree_id'],
        ['path' => 'index', 'title' => 'Main index', 'page' => 'index'],
        ['path' => 'latest_changes', 'title' => 'Latest changes', 'page' => 'latest_changes'],

        // *** Must be before places and before list (because of list in link) ***
        ['path' => 'list_places_families', 'title' => 'Places', 'page' => 'list_places_families'],
        // *** Must be before list***
        // ['path' => 'places', 'title' => 'Places', 'page' => 'places'],
        // *** Must be before list ***

        ['path' => 'list_names', 'title' => 'Names', 'page' => 'list_names', 'vars' => 'select_tree_id,last_name'],
        ['path' => 'list', 'title' => 'Persons', 'page' => 'list'],
        ['path' => 'login', 'title' => 'Login', 'page' => 'login'],
        ['path' => 'mailform', 'title' => 'Mail form', 'page' => 'mailform'],
        ['path' => 'maps', 'title' => 'World map', 'page' => 'maps'],
        ['path' => 'outline_report', 'title' => 'Outline Report', 'page' => 'outline_report'],
        ['path' => 'photoalbum', 'title' => 'Photobook', 'page' => 'photoalbum', 'vars' => 'select_tree_id'],
        ['path' => 'register', 'title' => 'Register', 'page' => 'register'],
        ['path' => 'relations', 'title' => 'Relationship calculator', 'page' => 'relations'],
        ['path' => 'reset_password', 'title' => 'Reset password', 'page' => 'reset_password'],
        // *** Must be before source ***
        ['path' => 'show_media_file', 'title' => 'Show media file', 'page' => 'show_media_file'],
        ['path' => 'sources', 'title' => 'Sources', 'page' => 'sources', 'vars' => 'select_tree_id'],
        ['path' => 'source', 'title' => 'Source', 'page' => 'source', 'vars' => 'select_tree_id,id'],
        ['path' => 'statistics', 'title' => 'Statistics', 'page' => 'statistics'],
        ['path' => 'timeline', 'title' => 'Timelines', 'page' => 'timeline', 'vars' => 'select_tree_id,id'],
        ['path' => 'user_settings', 'title' => 'Settings', 'page' => 'user_settings'],

        // Backwards compatibility only:
        ['path' => 'gezin', 'title' => 'Family Page', 'page' => 'family'],
        ['path' => 'lijst_namen', 'title' => 'Names', 'page' => 'list_names'],
        ['path' => 'lijst', 'title' => 'Persons', 'page' => 'list'],
    ];
    /*
    Examples:
    ['path' => '/cookies', 'title' => 'cookie_list', 'file' => 'cookies.php'],
    ['path' => '/help', 'title' => 'help', 'file' => 'help.php'],

    ['path' => '/tree-([0-9]+)', 'title' => 'tree_home', 'file' => 'tree_index.php', 'vars' => 'tree_id'],
    ['path' => "/([a-z]+)", 'title' => "cms_page", 'file' => 'cms_pages.php', 'vars' => 'cms_page_name'],
    */

    public function get_route($request_uri)
    {
        //TODO remove global
        global $humo_option;
        $result_array = [];
        $result_array['page404'] = false;
        //$result_array['page301'] = false;

        // *** Option url_rewrite disabled ***
        // http://127.0.0.1/HuMo-genealogy/index.php?page=ancestor_sheet&tree_id=3&id=I1180
        // change into (but still process index.php, so this will work in NGinx with url_rewrite disabled):
        // http://127.0.0.1/HuMo-genealogy/ancestor_sheet&tree_id=3&id=I1180
        if (isset($_GET['page'])) {
            //http://127.0.0.1/HuMo-genealogy/index.php?page=list&tree_id=3&adv_search=1&index_list=search
            $request_uri = str_replace('index.php?page=', '', $request_uri);
            // *** Example: http://localhost/HuMo-genealogy/list&tree_id=3&adv_search=1&index_list=search ***
            $request_uri = strtok($request_uri, "&"); // Remove last part of url: ?start=1&item=11
        } else {
            // *** Example: http://localhost/HuMo-genealogy/photoalbum/2?start=1&item=11 ***
            $request_uri = strtok($request_uri, "?"); // Remove last part of url: ?start=1&item=11
        }

        // *** Get url_rewrite variables ***
        $url_array = explode('/', $request_uri);

        foreach ($this->routes_array as $route_array) {
            //$vars = [];

            if (strpos($request_uri, $route_array['path']) > 0) {
                $result_array['page'] = $route_array['page'];

                // TODO remove title from router script.
                $result_array['title'] = $humo_option["database_name"] . ' - ' . __($route_array['title']);

                $url_position = strpos($request_uri, $route_array['path']);
                $result_array['tmp_path'] = substr($request_uri, 0, $url_position);


                // *** Check if link to website is valid. Remove last part of url: /photoalbum/2 and check if folder exists. ***
                // *** To prevent wrong links like: /humo-gen/list_places_families/fanchart/relations/11?pers_id=52211 ***
                $position = strpos($request_uri, $route_array['path']);
                if ($position !== false) {
                    $check_route = substr($request_uri, 0, $position);
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $check_route)) {
                        $result_array['page404'] = true;
                    }
                }


                // *** Get url_rewrite variables ***
                if ($humo_option["url_rewrite"] == "j" && isset($route_array['vars'])) {
                    $vars = explode(',', $route_array['vars']);
                    $nr_vars = count($vars);

                    //$vars_processed = false;

                    // *** Only 1 variable in url_rewrite, $vars='select_tree_id' ***
                    // Example: http://127.0.0.1/humo-genealogy/index/3
                    if ($nr_vars == 1 && $vars[0] === 'select_tree_id') {
                        // *** Get last item of array ***
                        $result_array['select_tree_id']  = end($url_array);
                        //$vars_processed = true;
                    }

                    // Example, cms page: http://127.0.0.1/humo-genealogy/cms_pages/4
                    if ($nr_vars == 1 && $vars[0] === 'id') {
                        // *** Get last item of array ***
                        $result_array['id']  = end($url_array);
                        //$vars_processed = true;
                    }

                    // *** 2 variables, 1st variable = family tree ***
                    // Example: http://127.0.0.1/humo-genealogy/list_names/3/D
                    if ($nr_vars == 2 && $vars[0] === 'select_tree_id') {
                        // *** Get last item of array ***
                        $result_array[$vars[1]] = end($url_array);
                        // *** Get previous item of array ***
                        $result_array['select_tree_id']  = prev($url_array);
                        //$vars_processed = true;
                    }

                    // TEST
                    //if ($nr_vars > 0 && !$vars_processed) {
                    //    $result_array['page404'] = true;
                    //}

                }
                break;
            }
        }

        // *** No valid page found. Check if link is the homepage.  ***
        /*
        if (!isset($result_array['page'])) {
            // *** Check if the URI links to the correct server folder ***
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $request_uri)) {
                $result_array['page404'] = true;
            }
        }
        */

        // *** Reroute links like: humo-gen/%3Cb%3E37%3C/languages/cs/flag.gif ***
        // *** %3Cb%3E = <b> ***
        //if (strpos($_SERVER['REQUEST_URI'], '%3Cb%3E') > 0) {
        //  $result_array['page301'] = str_replace('%3Cb%3E', '', $_SERVER['REQUEST_URI']);
        //  $result_array['page301'] = str_replace('%3C', '', $result_array['page301']);
        //}

        return $result_array;
    }
}
