<?php
class AddressesController
{
    private $dbh, $user, $tree_id, $link_cls, $uri_path, $humo_option;

    public function __construct($dbh, $user, $tree_id, $link_cls, $uri_path, $humo_option)
    {
        $this->dbh = $dbh;
        $this->user = $user;
        $this->tree_id = $tree_id;
        $this->link_cls = $link_cls;
        $this->uri_path = $uri_path;
        $this->humo_option = $humo_option;
    }

    public function list()
    {
        $addressesModel = new AddressesModel($this->dbh);

        $authorised = $addressesModel->getAddressAuthorised($this->user);
        $addressesModel->process_variables();
        $addresses = $addressesModel->listAddresses($this->dbh, $this->tree_id, $this->humo_option);
        $line_pages = $addressesModel->line_pages($this->tree_id, $this->link_cls, $this->uri_path);
        $address_image = $addressesModel->getAddressImage();
        $place_image = $addressesModel->getPlaceImage();
        $select_sort = $addressesModel->getSelectSort();
        $place_link = $addressesModel->getPlaceLink();
        $address_link = $addressesModel->getAddressLink();
        $adr_place = $addressesModel->get_adr_place();
        $adr_address = $addressesModel->get_adr_address();
        $data = array(
            "authorised" => $authorised,
            "addresses" => $addresses,
            "address_image" => $address_image,
            "place_link" => $place_link,
            "address_link" => $address_link,
            "place_image" => $place_image,
            "select_sort" => $select_sort,
            "title" => __('Address'),
            "adr_place" => $adr_place,
            "adr_address" => $adr_address
        );

        return array_merge($data, $line_pages);
    }
}
