<?php
require_once  __DIR__ . "/../model/photoalbum.php";

include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");
include_once(__DIR__ . "/../../include/person_cls.php");
include_once(__DIR__ . "/../../include/show_picture.php");

class PhotoalbumController
{
    /*
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function detail($dbh)
    {
        $photoalbumModel = new PhotoalbumModel($dbh);
        $photoalbum['show_pictures'] = $photoalbumModel->get_show_pictures();
        $photoalbum['search_media'] = $photoalbumModel->get_search_media();

        return $photoalbum;
    }
}
