<?php
class MailformController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get_mail_data($dataDb, $selected_language): array
    {
        $mailformModel = new MailformModel($this->config);

        $mail_form = $mailformModel->getFormdata();
        $mail_check = $mailformModel->mail_check();

        $mail_array = array_merge($mail_form, $mail_check);

        $mail_send = $mailformModel->send_mail($mail_array, $dataDb, $selected_language);

        if (isset($mail_send)) {
            $mail_array = array_merge($mail_form, $mail_send);
        }
        return $mail_array;
    }
}
