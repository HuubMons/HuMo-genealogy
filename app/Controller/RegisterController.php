<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\RegisterModel;

class RegisterController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get_register_data(): array
    {
        $registerModel = new RegisterModel($this->config);

        $register_form = $registerModel->getFormdata();
        $register_allowed["register_allowed"] = $registerModel->register_allowed();
        $register_array = array_merge($register_form, $register_allowed);

        $register_user = $registerModel->register_user($register_array);

        $register_array = array_merge($register_array, $register_user);
        return $register_array;
    }
}
