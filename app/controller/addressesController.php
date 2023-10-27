<?php
require_once  __DIR__ . "/../model/addresses.php";

class addressesController
{
    private $dbh, $user, $tree_id;

    public function __construct($dbh, $user, $tree_id)
    {
        $this->dbh = $dbh;
        $this->user = $user;
        $this->tree_id = $tree_id;
    }

    public function list()
    {
        $model = new Addresses($this->dbh);

        $authorised = $model->getAddressAuthorised($this->user);
        $addresses = $model->getAll($this->dbh, $this->tree_id);
        $address_image = $model->getAddressImage();
        $place_image = $model->getPlaceImage();
        $select_sort = $model->getSelectSort();
        $place_link = $model->getPlaceLink();
        $address_link = $model->getAddressLink();

        $data = array(
            "authorised" => $authorised,
            "addresses" => $addresses,
            "address_image" => $address_image,
            "place_link" => $place_link,
            "address_link" => $address_link,
            "place_image" => $place_image,
            "select_sort" => $select_sort,
            "title" => __('Address')
        );
        return $data;
    }
}
