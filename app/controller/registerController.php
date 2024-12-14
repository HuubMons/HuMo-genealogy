<?php
class RegisterController
{
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }

    public function get_register_data($dbh, $dataDb, $humo_option)
    {
        $registerModel = new RegisterModel($this->db_functions);

        $register_form = $registerModel->getFormdata();
        $register_allowed["register_allowed"] = $registerModel->register_allowed($humo_option);
        $register_array = array_merge($register_form, $register_allowed);

        $register_user = $registerModel->register_user($dbh, $dataDb, $humo_option, $register_array);

        $register_array = array_merge($register_array, $register_user);
        return $register_array;
    }
}
