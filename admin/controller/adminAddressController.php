<?php
class AdminAddressController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new Editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editAddressModel = new AdminAddressModel($dbh);
        $editAddressModel->set_address_id();
        $editAddressModel->update_address($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editAddress['address_id'] = $editAddressModel->get_address_id();

        $editAddress['editor_cls'] = $this->editor_cls;

        $get_addresses = $editAddressModel->get_addresses($dbh, $tree_id);
        $editAddress = array_merge($editAddress, $get_addresses);

        return $editAddress;
    }
}
