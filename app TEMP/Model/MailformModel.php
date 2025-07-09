<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\ShowTreeText;

class MailformModel extends BaseModel
{
    public function getFormdata(): array
    {
        $mail_data["name"] = '';
        if (isset($_POST['mail_name'])) {
            $mail_data["name"] = $_POST['mail_name'];
        }

        $mail_data["sender"] = '';
        if (isset($_POST['mail_sender'])) {
            $mail_data["sender"] = $_POST['mail_sender'];
        }

        $mail_data["subject"] = '';
        if (isset($_POST['mail_subject'])) {
            $mail_data["subject"] = $_POST['mail_subject'];
        }

        $mail_data["text"] = '';
        if (isset($_POST['mail_text'])) {
            $mail_data["text"] = $_POST['mail_text'];
        }

        return $mail_data;
    }

    public function mail_check(): array
    {
        // *** Check block_spam_answer ***
        $mail_data["send_mail"] = false;
        $mail_data["correct_spam_answer"] = true;
        if (isset($_POST['send_mail'])) {
            if (isset($_POST['mail_block_spam']) && strtolower($_POST['mail_block_spam']) == strtolower($this->humo_option["block_spam_answer"])) {
                $mail_data["send_mail"] = true;
                $mail_data["correct_spam_answer"] = true;
            } else {
                $mail_data["correct_spam_answer"] = false;
            }
        }

        // *** Only simple check at this moment. TODO use regex. ***
        $mail_data["check_mail_address"] = true;
        if (isset($_POST['mail_sender'])) {
            $position = strpos($_POST['mail_sender'], "@");
            if ($position < 1) {
                $mail_data["check_mail_address"] = false;
                $mail_data["send_mail"] = false;
            }
        }

        if ($this->humo_option["use_spam_question"] != 'y') {
            $mail_data["send_mail"] = true;
        }

        return $mail_data;
    }

    public function send_mail($mail_data, $dataDb, $selected_language)
    {
        if (isset($_POST['send_mail']) && $mail_data["send_mail"] == true) {
            $mail_address = $dataDb->tree_email;
            $showTreeText = new ShowTreeText();

            $treetext = $showTreeText ->show_tree_text($_SESSION['tree_id'], $selected_language);
            $mail_data["subject"] = sprintf(__('%s Mail form.'), 'HuMo-genealogy');
            $mail_data["subject"] .= " (" . $treetext['name'] . "): " . $_POST['mail_subject'] . "\n";

            // *** It's better to use plain text in the subject ***
            $mail_data["subject"] = strip_tags($mail_data["subject"], ENT_QUOTES);

            $mail_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
            $mail_message .= "<br>\n";

            $mail_message .= "<br>\n";
            $mail_message .= __('Name') . ':' . $_POST['mail_name'] . "<br>\n";
            $mail_message .= __('E-mail') . ": <a href='mailto:" . $_POST['mail_sender'] . "'>" . $_POST['mail_sender'] . "</a><br>\n";
            if (isset($_SESSION['save_last_visitid'])) {
                $mail_message .= __('Last visited family:') . " <a href='http://" . $_SESSION['save_last_visitid'] . "'>" . $_SESSION['save_last_visitid'] . "</a><br>\n";
            }
            if (isset($_POST['newsletter'])) {
                $mail_message .= __('Receive newsletter') . ': ' . $_POST['newsletter'] . "<br>\n";
            }
            $mail_message .= $_POST['mail_text'] . "<br>\n";

            //$headers  = "MIME-Version: 1.0\n";
            //$headers .= "Content-type: text/html; charset=utf-8\n";
            //$headers .= "X-Priority: 3\n";
            //$headers .= "X-MSMail-Priority: Normal\n";
            //$headers .= "X-Mailer: php\n";
            // *** Removed "From e-mail address"! Some providers don't accept other e-mail addresses because safety reasons! ***
            //$headers .= "From: \"".$_POST['mail_name']."\"\n";
            //$headers .= "Reply-To: \"".$_POST['mail_name']."\" <".$_POST['mail_sender'].">\n";

            // *** REMARK: because of security, the mail address and message entered by the visitor are not shown on screen anymore! ***
            //echo '<br>'.__('You have entered the following e-mail address: ').'<b> '.$_POST['mail_sender'].'</b><br>';
            //echo __('Message: ').'<br>'.$_POST['mail_text'];

            // *** Use PhpMailer to send mail ***
            $humo_option = $this->humo_option; // Used in mail.php
            include_once(__DIR__ . '/../../include/mail.php');

            // *** Changed july 2024: Set who the message is to be sent from ***
            if ($humo_option["email_sender"] && filter_var($humo_option["email_sender"], FILTER_VALIDATE_EMAIL)) {
                // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
                $mail->setFrom($humo_option["email_sender"], $humo_option["email_sender"]);
            } else {
                $mail->setFrom($_POST['mail_sender'], $_POST['mail_name']);
            }

            // *** Added july 2024 ***
            $mail->AddReplyTo($_POST['mail_sender'], $_POST['mail_name']);

            // *** Set who the message is to be sent to ***
            //$mail->addAddress($mail_address, $mail_address);
            $mult = explode(",", $mail_address);
            foreach ($mult as $val) {
                $val = trim($val); // this way it will work both with "someone@gmail.com,other@gmail.com" and also "someone@gmail.com , other@gmail.com"
                $mail->addAddress($val, $val);
            }

            // *** Set the subject line ***
            $mail->Subject = $mail_data["subject"];

            $mail->msgHTML($mail_message);
            // *** Replace the plain text body with one created manually ***
            //$mail->AltBody = 'This is a plain-text message body';

            $mail_data["mail_results"] = $mail;

            return $mail_data;
        }
    }
}
