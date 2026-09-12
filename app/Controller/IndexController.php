<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\IndexModel;
use Genealogy\Include\GetVisitorIP;
use Genealogy\Include\ActiveUsers;
use Genealogy\Include\SetTimezone;
use Genealogy\Include\DbFunctions;
use Genealogy\Languages\LanguageCls;

class IndexController
{
    public function detail($dbh, $humo_option, $user): array
    {
        $indexModel = new IndexModel();
        $getVisitorIP = new GetVisitorIP();
        $getActiveUsers = new ActiveUsers();
        $setTimezone = new SetTimezone();

        // TODO check if these variables can be used in multiple scripts. Only use in index page?
        $db_functions = new DbFunctions($dbh);
        $index['db_functions'] = $db_functions;

        $index['visitor_ip'] = $getVisitorIP->visitorIP();

        // *** Debug HuMo-genealogy front pages ***
        if ($humo_option["debug_front_pages"] == 'y') {
            if ($humo_option["debug_show_deprecated"] == 'n') {
                error_reporting(E_ALL & ~E_DEPRECATED);
            } else {
                error_reporting(E_ALL);
            }
            ini_set('display_errors', 1);
        }

        // *** Check if visitor is allowed access to website ***
        if (!$index['db_functions']->check_visitor($index['visitor_ip'], 'partial')) {
            echo 'Access to website is blocked.';
            exit;
        }

        // *** Sept. 2026: Check for number of active users too prevent too many visitors ***
        $getActiveUsers->removeInactiveUsers($dbh, $humo_option["max_visitors_seconds"]);
        // Add active user to humo_active_visitors table
        $getActiveUsers->addActiveUser($dbh, $index['visitor_ip']);
        // *** Get number of active users ***
        $active_users = $getActiveUsers->GetActiveUsers($dbh);

        // TEST:
        //$humo_option["max_visitors"] = 0;

        $sendVisitorLimitMail = function (string $subject) use ($humo_option, $active_users): void {
            //$from = $humo_option['email_sender'] ?? '';
            $from = '';

            if ($humo_option["email_sender"] && filter_var($humo_option["email_sender"], FILTER_VALIDATE_EMAIL)) {
                // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
                $from = $humo_option["email_sender"];
            } else {
                //NAZIEN
                //$from =  $_POST['mail_sender'];
                //$from = $humo_option["email_sender"];
            }

            $to = $humo_option['max_visitors_email_address'] ?? '';
            if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
                error_log('Visitor limit mail skipped: invalid sender or recipient address.');
                return;
            }

            try {
                include __DIR__ . '/../../include/mail.php';
                $mail->setFrom($from, 'HuMo-genealogy');

                // *** Added july 2024 ***
                //$mail->AddReplyTo($_POST['mail_sender'], $_POST['mail_name']);
                $mail->AddReplyTo($from, 'HuMo-genealogy');

                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->msgHTML(
                    '<p>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>' .
                        '<p>Number of active users: ' . $active_users . '</p>' .
                        '<p>Please check the website.</p>'
                );
                $mail->AltBody = "Number of active users: " . $active_users . "\nPlease check the website.";

                if (!$mail->send()) {
                    error_log('Visitor limit mail failed: ' . $mail->ErrorInfo);
                }
            } catch (\Throwable $exception) {
                error_log('Visitor limit mail exception: ' . $exception->getMessage());
            }
        };

        // *** Check for limited website status ***
        $index['website_limited'] = 'n';
        $time_limit = 10 * 60;

        if (strpos($humo_option["website_status"], 'temporarily_limited') !== false) {
            // check if time has passed. If so, set website_status to active.
            $time_limited = (int)str_replace('temporarily_limited|', '', $humo_option["website_status"]);
            if (time() - $time_limited < $time_limit) {
                $index['website_limited'] = 'y';
            } else {
                $db_functions->update_settings('website_status', 'active');
            }
        } elseif ($active_users >= (int)$humo_option["max_visitors"] && $humo_option["max_visitors_action"] == 'close_pages') {
            $index['website_limited'] = 'y';

            // *** Also save start time ***
            $db_functions->update_settings(
                'website_status',
                'temporarily_limited|' . time()
            );

            $sendVisitorLimitMail('Website temporarily limited due to too many active users');
        }

        // *** Check for temporarily closed website status ***
        $close_website = false;

        // Also check time. Close website for a certain time period.
        if (strpos($humo_option["website_status"], 'temporarily_closed') !== false) {
            // check if time has passed. If so, set website_status to active.
            $time_closed = (int)str_replace('temporarily_closed|', '', $humo_option["website_status"]);
            if (time() - $time_closed < $time_limit) {
                $close_website = true;
            } else {
                $db_functions->update_settings('website_status', 'active');
            }
        } elseif ($active_users >= (int)$humo_option["max_visitors"] && $humo_option["max_visitors_action"] == 'close_website') {
            $close_website = true;

            // *** Also save start time ***
            $db_functions->update_settings(
                'website_status',
                'temporarily_closed|' . time()
            );

            $sendVisitorLimitMail('Website temporarily closed due to too many active users');
        }

        if ($close_website) {
?>
            <html>

            <head>
                <title>Too many active users</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
            </head>

            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger mt-5" role="alert">
                                <h4 class="alert-heading">Too many active users</h4>
                                <p>At this moment there are too many active users at the website. Please try again later.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </body>

            </html>

<?php
            exit;
        }


        $setTimezone->timezone();
        // *** TIMEZONE TEST ***
        //echo date("Y-m-d H:i");

        // *** Language items ***
        $language_cls = new LanguageCls;
        $index['language_file'] = $language_cls->get_languages();
        $index['selected_language'] = $language_cls->get_selected_language($humo_option);
        $index['language'] = $language_cls->get_language_data($index['selected_language']);
        // *** .mo language text files ***
        include_once(__DIR__ . "/../../languages/gettext.php");
        // *** Load ***
        Load_default_textdomain();

        $login = $indexModel->login($dbh, $index['db_functions'], $index['visitor_ip']);
        $index = array_merge($index, $login);

        $route = $indexModel->get_model_route($humo_option);
        $index = array_merge($index, $route);

        // *** Get tree_id, tree_prefix ***
        $family_tree = $indexModel->get_family_tree($dbh, $index['db_functions'], $user);
        $index = array_merge($index, $family_tree);

        $index['page404'] = $indexModel->get_page404();
        $index['page301'] = $indexModel->get_page301();

        return $index;
    }
}
