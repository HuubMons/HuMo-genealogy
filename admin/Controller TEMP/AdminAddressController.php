<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\AdminAddressModel;
use Genealogy\Include\Editor_cls;

class AdminAddressController
{
    protected $admin_config;
    private $editor_cls;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;

        $this->editor_cls = new Editor_cls;
    }

    public function detail(): array
    {
        $editAddressModel = new AdminAddressModel($this->admin_config);

        $editAddressModel->set_address_id();
        $editAddressModel->update_address($this->editor_cls);
        $editAddress['address_id'] = $editAddressModel->get_address_id();

        $editAddress['editor_cls'] = $this->editor_cls;

        $get_addresses = $editAddressModel->get_addresses();
        $editAddress = array_merge($editAddress, $get_addresses);

        return $editAddress;
    }
}
