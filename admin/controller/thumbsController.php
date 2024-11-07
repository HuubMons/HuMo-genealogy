<?php
require_once __DIR__ . "/../models/thumbs.php";

class ThumbsController
{
    public function detail($dbh, $tree_id)
    {
        $thumbsModel = new ThumbsModel($dbh);

        $thumbs['menu_tab'] = $thumbsModel->get_menu_tab();

        if ($thumbs['menu_tab'] == 'picture_settings' || $thumbs['menu_tab'] == 'picture_thumbnails' || $thumbs['menu_tab'] == 'picture_show') {
            $thumbsModel->save_picture_path($dbh, $tree_id);

            // *** Process tree_pict_path, using a default or own path for pictures ***
            $tree_pict_path = $thumbsModel->get_tree_pict_path($dbh, $tree_id);
            $thumbs['default_path'] = $thumbsModel->get_default_path($tree_pict_path);

            $thumbs['tree_pict_path'] = $tree_pict_path;
            $thumbs['own_pict_path'] = $tree_pict_path;
            if ($thumbs['default_path']) {
                $thumbs['tree_pict_path'] = 'media/';
                $thumbs['own_pict_path'] = substr($tree_pict_path, 1);
            }
        }

        return $thumbs;
    }
}
