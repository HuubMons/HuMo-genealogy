<?php

/**
 * Added in in august 2023.
 * 
 * Example to get a link:
 * $link = $processLinks->get_link($uri_path, 'tree_index', $tree_id);
 *
 * For family page, add $vars.
 *  $vars['pers_family'] = $pers_family;
 *  $link = $processLinks->get_link('../', 'family', $tree_id, true, $vars);
 *  $link .= "main_person=" . $person->pers_gedcomnumber;
 */

namespace Genealogy\Include;

class ProcessLinks
{
    public $path = '';

    // *** Seperator can be used if rewrite is disabled ***
    public $link_array = [
        ['page' => 'addresses', 'file_rewrite' => 'addresses', 'file' => 'index.php?page=addresses', 'seperator' => '&'],
        ['page' => 'ancestor_report_rtf', 'file_rewrite' => 'ancestor_report_rtf', 'file' => 'index.php?page=ancestor_report_rtf', 'seperator' => '&'],
        ['page' => 'ancestor_report', 'file_rewrite' => 'ancestor_report', 'file' => 'index.php?page=ancestor_report', 'seperator' => '&'],
        ['page' => 'ancestor_sheet_pdf', 'file_rewrite' => 'ancestor_sheet_pdf', 'file' => 'index.php?page=ancestor_sheet_pdf', 'seperator' => '&'],
        ['page' => 'anniversary', 'file_rewrite' => 'anniversary', 'file' => 'index.php?page=anniversary', 'seperator' => '&'],
        ['page' => 'cms_pages', 'file_rewrite' => 'cms_pages', 'file' => 'index.php?page=cms_pages', 'seperator' => '&'],
        ['page' => 'cookies', 'file_rewrite' => 'cookies', 'file' => 'index.php?page=cookies', 'seperator' => '&'],
        ['page' => 'descendant_chart', 'file_rewrite' => 'descendant_chart', 'file' => 'index.php?page=descendant_chart', 'seperator' => '&'],
        ['page' => 'family', 'file_rewrite' => 'family', 'file' => 'index.php?page=family', 'seperator' => '&'],
        ['page' => 'fanchart', 'file_rewrite' => 'fanchart', 'file' => 'index.php?page=fanchart', 'seperator' => '&'],
        ['page' => 'help', 'file_rewrite' => 'help', 'file' => 'index.php?page=help', 'seperator' => '&'],
        ['page' => 'hourglass', 'file_rewrite' => 'hourglass', 'file' => 'index.php?page=hourglass', 'seperator' => '&'],
        ['page' => 'index', 'file_rewrite' => 'index', 'file' => 'index.php', 'seperator' => '?'],
        ['page' => 'language', 'file_rewrite' => 'index', 'file' => 'index.php', 'seperator' => '?'],
        ['page' => 'latest_changes', 'file_rewrite' => 'latest_changes', 'file' => 'index.php?page=latest_changes', 'seperator' => '&'],
        ['page' => 'list', 'file_rewrite' => 'list', 'file' => 'index.php?page=list', 'seperator' => '&'],
        ['page' => 'list_names', 'file_rewrite' => 'list_names', 'file' => 'index.php?page=list_names', 'seperator' => '&'],
        ['page' => 'list_places_families', 'file_rewrite' => 'list_places_families', 'file' => 'index.php?page=list_places_families', 'seperator' => '&'],
        ['page' => 'login', 'file_rewrite' => 'login', 'file' => 'index.php?page=login', 'seperator' => '&'],
        ['page' => 'logoff', 'file_rewrite' => 'index?log_off=1', 'file' => 'index.php?log_off=1', 'seperator' => '&'],
        ['page' => 'mailform', 'file_rewrite' => 'mailform', 'file' => 'index.php?page=mailform', 'seperator' => '&'],
        ['page' => 'maps', 'file_rewrite' => 'maps', 'file' => 'index.php?page=maps', 'seperator' => '&'],
        ['page' => 'photoalbum', 'file_rewrite' => 'photoalbum', 'file' => 'index.php?page=photoalbum', 'seperator' => '&'],
        ['page' => 'register', 'file_rewrite' => 'register', 'file' => 'index.php?page=register', 'seperator' => '&'],
        ['page' => 'relations', 'file_rewrite' => 'relations', 'file' => 'index.php?page=relations', 'seperator' => '&'],
        ['page' => 'reset_password', 'file_rewrite' => 'reset_password', 'file' => 'index.php?page=reset_password', 'seperator' => '&'],
        ['page' => 'outline_report', 'file_rewrite' => 'outline_report', 'file' => 'index.php?page=outline_report', 'seperator' => '&'],
        ['page' => 'sources', 'file_rewrite' => 'sources', 'file' => 'index.php?page=sources', 'seperator' => '&'],
        ['page' => 'source', 'file_rewrite' => 'source', 'file' => 'index.php?page=source', 'seperator' => '&'],
        ['page' => 'statistics', 'file_rewrite' => 'statistics', 'file' => 'index.php?page=statistics', 'seperator' => '&'],
        ['page' => 'timeline', 'file_rewrite' => 'timeline', 'file' => 'index.php?page=timeline', 'seperator' => '&'],
        ['page' => 'tree_index', 'file_rewrite' => 'tree_index', 'file' => 'index.php?page=tree_index', 'seperator' => '&'],
        ['page' => 'user_settings', 'file_rewrite' => 'user_settings', 'file' => 'index.php?page=user_settings', 'seperator' => '&'],
    ];

    public function __construct($path = '')
    {
        $this->path = $path;
    }

    public function get_link($change_path, $page, $tree_id = NULL, $add_seperator = false, $vars = ''): string
    {
        global $humo_option;

        $path = $this->path;
        if ($change_path != '') {
            $path = $change_path;
        }

        // *** Default link ***
        if ($humo_option["url_rewrite"] == "j") {
            $link = $path . 'index'; // *** Default value ***
        } else {
            $seperator = '?';
            $link = $path . 'index.php'; // *** Default value ***
        }

        foreach ($this->link_array as $links) {
            //$vars = [];

            //if (isset($route['vars'])) {
            //    $vars = explode(',', $route['vars']);
            //}

            if ($humo_option["url_rewrite"] == "j") {
                if ($page == $links['page']) {
                    $link = $path . $links['file_rewrite'];

                    if ($tree_id) {
                        $link .= '/' . $tree_id;
                    }

                    if ($page == 'ancestor_report' && $vars) {
                        $link .= '/' . $vars['id'];
                    }
                    if ($page == 'ancestor_report_rtf' && $vars) {
                        $link .= '/' . $vars['id'];
                    }
                    if ($page == 'ancestor_sheet_pdf' && $vars) {
                        $link .= '?main_person=' . $vars['id'];
                    }
                    if ($page == 'cms_pages' && $vars) {
                        $link .= '/' . $vars['select_page'];
                    }
                    if ($page == 'descendant_chart' && $vars) {
                        $link .= '/' . $vars['id'];
                    }

                    if ($page == 'family' && $vars) {
                        $link .= '/' . $vars['pers_family'];
                    }

                    if ($page == 'fanchart' && $vars) {
                        $link .= '/' . $vars['id'];
                    }

                    if ($page == 'hourglass' && $vars) {
                        $link .= '/' . $vars['pers_family'];
                    }

                    if ($page == 'list_names' && $vars) {
                        $link .= '/' . rawurlencode((string)$vars['last_name']);
                    }

                    if ($page == 'timeline' && $vars) {
                        $link .= '/' . $vars['pers_gedcomnumber'];
                    }

                    if ($page == 'source' && $vars) {
                        $link .= '/' . $vars['source_gedcomnr'];
                    }

                    if ($add_seperator) {
                        $link .= '?';
                    }
                    break;
                }
            } else {
                if ($page == $links['page']) {
                    $link = $path . $links['file'];
                    $seperator = $links['seperator'];

                    if ($tree_id) {
                        $link .= $seperator . 'tree_id=' . $tree_id;
                        $seperator = '&';
                    }

                    if ($page == 'ancestor_report' and $vars) {
                        $link .= '&id=' . $vars['id'];
                    }
                    if ($page == 'ancestor_report_rtf' and $vars) {
                        $link .= '&id=' . $vars['id'];
                    }
                    if ($page == 'ancestor_sheet_pdf' and $vars) {
                        $link .= '&main_person=' . $vars['id'];
                    }
                    if ($page == 'cms_pages' and $vars) {
                        $link .= '&select_page=' . $vars['select_page'];
                    }
                    if ($page == 'descendant_chart' and $vars) {
                        $link .= '&id=' . $vars['id'];
                    }

                    if ($page == 'family' and $vars) {
                        $link .= '&id=' . $vars['pers_family'];
                    }

                    if ($page == 'fanchart' and $vars) {
                        $link .= '&id=' . $vars['id'];
                    }

                    if ($page == 'hourglass' and $vars) {
                        $link .= '&id=' . $vars['pers_family'];
                    }

                    if ($page == 'list_names' and $vars) {
                        $link .= '&last_name=' . rawurlencode((string)$vars['last_name']);
                    }

                    if ($page == 'timeline' and $vars) {
                        $link .= '&id=' . $vars['pers_gedcomnumber'];
                    }

                    if ($page == 'source' and $vars) {
                        $link .= '&id=' . $vars['source_gedcomnr'];
                    }

                    if ($add_seperator) $link .= $seperator;
                    break;
                }
            }
        }

        return $link;
    }
}
