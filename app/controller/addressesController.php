<?php
class AddressesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list(): array
    {
        $addressesModel = new AddressesModel($this->config);

        $authorised = $addressesModel->getAddressAuthorised();
        $addressesModel->process_variables();
        $addresses = $addressesModel->listAddresses();
        $line_pages = $addressesModel->line_pages();
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
