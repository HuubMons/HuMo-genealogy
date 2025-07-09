<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\AddressModel;

class AddressController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail(): array
    {
        $addressModel = new AddressModel($this->config);

        $authorised = $addressModel->getAddressAuthorised();
        $address = $addressModel->getById($_GET["id"]);
        $address_sources = $addressModel->getAddressSources($_GET["id"]);
        $address_connected_persons = $addressModel->getAddressConnectedPersons($_GET["id"]);
        return array(
            "authorised" => $authorised,
            "address" => $address,
            "address_sources" => $address_sources,
            "address_connected_persons" => $address_connected_persons,
            "title" => __('Address')
        );
    }
}
