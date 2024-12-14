<?php
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");
include_once(__DIR__ . "/../../include/show_picture.php");

class PhotoalbumController
{
    public function detail($dbh, $tree_id, $db_functions)
    {
        $photoalbumModel = new PhotoalbumModel($dbh);
        $photoalbum['show_pictures'] = $photoalbumModel->get_show_pictures();
        $photoalbum['search_media'] = $photoalbumModel->get_search_media();

        $photoalbum['show_categories'] = false;

        $categories = $photoalbumModel->get_categories($dbh);
        $photoalbum = array_merge($photoalbum, $categories);

        $photoalbum['chosen_tab'] = $photoalbumModel->get_chosen_tab($photoalbum['category']);

        $get_media_files = $photoalbumModel->get_media_files($dbh, $tree_id, $db_functions, $photoalbum['chosen_tab'], $photoalbum['search_media'], $photoalbum['category']);
        $photoalbum = array_merge($photoalbum, $get_media_files);

        return $photoalbum;
    }
}
