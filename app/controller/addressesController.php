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
        $addressesModel = new AddressesModel($this->dbh);

        $authorised = $addressesModel->getAddressAuthorised($this->user);
        $addresses = $addressesModel->getAll($this->dbh, $this->tree_id);
        $address_image = $addressesModel->getAddressImage();
        $place_image = $addressesModel->getPlaceImage();
        $select_sort = $addressesModel->getSelectSort();
        $place_link = $addressesModel->getPlaceLink();
        $address_link = $addressesModel->getAddressLink();

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
