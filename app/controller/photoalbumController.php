<?php
class PhotoalbumController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail($selected_language): array
    {
        $photoalbumModel = new PhotoalbumModel($this->config);

        $photoalbum['show_pictures'] = $photoalbumModel->get_show_pictures();
        $photoalbum['search_media'] = $photoalbumModel->get_search_media();

        $photoalbum['show_categories'] = false;

        $categories = $photoalbumModel->get_categories($selected_language);
        $photoalbum = array_merge($photoalbum, $categories);

        $photoalbum['chosen_tab'] = $photoalbumModel->get_chosen_tab($photoalbum['category']);

        $get_media_files = $photoalbumModel->get_media_files($photoalbum['chosen_tab'], $photoalbum['search_media'], $photoalbum['category']);
        $photoalbum = array_merge($photoalbum, $get_media_files);


        // TODO move to model script.
        // *** Show media/ photo's ***
        $user = $this->config['user'];
        $hide_photocat_array = explode(";", $user['group_hide_photocat']);
        // *** Check is photo category tree is hidden for user group ***
        if (isset($photoalbum['media_files']) && $photoalbum['show_categories'] && in_array($photoalbum['category_id'][$photoalbum['chosen_tab']], $hide_photocat_array)) {
            unset($photoalbum['media_files']);
            $photoalbum['media_files'][] = '';
            $photoalbum['chosen_tab'] = 'none';
        }


        $get_calculate_pages = $photoalbumModel->calculate_pages($photoalbum);
        $photoalbum = array_merge($photoalbum, $get_calculate_pages);

        return $photoalbum;
    }
}
