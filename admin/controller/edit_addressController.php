<?php
require_once __DIR__ . "/../models/edit_address.php";

include_once(__DIR__ . "/../include/editor_cls.php");
include_once(__DIR__ . "/../include/select_tree.php");

class AddressController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editAddressModel = new EditAddressModel($dbh);
        $editAddressModel->set_address_id();
        $editAddressModel->update_address($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editAddress['address_id'] = $editAddressModel->get_address_id();

        $editAddress['editor_cls'] = $this->editor_cls;

        return $editAddress;
    }
}
