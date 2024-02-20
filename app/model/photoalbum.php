<?php
class PhotoalbumModel
{
    function get_show_pictures()
    {
        $show_pictures = 8; // *** Default value ***

        // Remark: setcookie is done in header.
        if (isset($_COOKIE["humogenphotos"]) and is_numeric($_COOKIE["humogenphotos"])) {
            $show_pictures = $_COOKIE["humogenphotos"];
        } elseif (isset($_SESSION['save_show_pictures']) and is_numeric($_SESSION['save_show_pictures'])) {
            $show_pictures = $_SESSION['save_show_pictures'];
        }
        if (isset($_POST['show_pictures']) and is_numeric($_POST['show_pictures'])) {
            $show_pictures = $_POST['show_pictures'];
            $_SESSION['save_show_pictures'] = $show_pictures;
        }
        if (isset($_GET['show_pictures']) and is_numeric($_GET['show_pictures'])) {
            $show_pictures = $_GET['show_pictures'];
            $_SESSION['save_show_pictures'] = $show_pictures;
        }
        return $show_pictures;
    }

    function get_search_media()
    {
        // *** Photo search ***
        $search_media = '';
        if (isset($_SESSION['save_search_media'])) {
            $search_media = $_SESSION['save_search_media'];
        }
        if (isset($_POST['search_media'])) {
            $search_media = safe_text_db($_POST['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        if (isset($_GET['search_media'])) {
            $search_media = safe_text_db($_GET['search_media']);
            $_SESSION['save_search_media'] = $search_media;
        }
        return $search_media;
    }
}
