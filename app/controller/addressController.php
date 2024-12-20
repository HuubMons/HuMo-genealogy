<?php
class AddressController
{
    private $db_functions, $user;

    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }

    public function detail()
    {
        $addressModel = new AddressModel($this->db_functions);

        $authorised = $addressModel->getAddressAuthorised($this->user);
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
