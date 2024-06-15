<?php
require_once  __DIR__ . "/../model/mailform.php";

class MailformController
{
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }

    public function get_mail_data($humo_option, $dataDb, $selected_language)
    {
        $mailformModel = new MailformModel($this->db_functions);

        $mail_form = $mailformModel->getFormdata();
        $mail_check = $mailformModel->mail_check($humo_option);

        $mail_array = array_merge($mail_form, $mail_check);

        $mail_send = $mailformModel->send_mail($mail_array, $dataDb, $selected_language);

        if (isset($mail_send)) {
            $mail_array = array_merge($mail_form, $mail_send);
        }
        return $mail_array;
    }
}
