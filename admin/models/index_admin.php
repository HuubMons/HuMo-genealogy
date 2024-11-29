<?php
class IndexModel
{
    // *** TODO: improve function. Probably split into multiple functions ***
    public function database_settings($database_check)
    {
        $result_message = '';

        if (isset($_POST['save_settings_database'])) {
            $result_message = '<b>' . __('Database connection status:') . '</b><br>';

            // *** Check MySQL connection ***
            try {
                $conn = 'mysql:host=' . $_POST['db_host'];
                $db_check = new PDO($conn, $_POST['db_username'], $_POST['db_password']);

                $result_message .= __('MySQL connection: OK!') . '<br>';
                // *** If needed immediately install a new database ***
                if (isset($_POST['install_database'])) {
                    $install_qry = "CREATE DATABASE IF NOT EXISTS `" . $_POST['db_name'] . "`CHARACTER SET utf8 COLLATE utf8_general_ci";
                    $db_check->query($install_qry);
                }
            } catch (PDOException $e) {
                $result_message .= '<b>*** ' . __('There is no MySQL connection: please check host/ username and password.') . ' ***</b><br>';
            }

            // *** Check if database exists ***
            try {
                $conn = 'mysql:host=' . $_POST['db_host'] . ';dbname=' . $_POST['db_name'];
                //$temp_dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD);
                $temp_dbh = new PDO($conn, $_POST['db_username'], $_POST['db_password']);
                if ($temp_dbh !== false) {
                    $database_check = true;
                    $result_message .= __('Database connection: OK!') . '<br>';
                }
                $temp_dbh = null;
            } catch (PDOException $e) {
                $result_message .= '<b>*** ' . __('No database found! Check MySQL connection and database name') . ' ***</b><br>';
            }

            // *** Check if db_login.php is writable, and change database lines in db_login.php file ***
            $login_file = "../include/db_login.php";
            if (!is_writable($login_file)) {
                $result_message = '<b> *** ' . __('The configuration file is not writable! Please change the include/db_login.php file manually.') . ' ***</b>';
            } else {
                // *** Read file ***
                $handle = fopen($login_file, "r");
                while (!feof($handle)) {
                    $buffer[] = fgets($handle, 4096);
                }

                // *** Write file ***
                $check_config = false;
                $bestand_config = fopen($login_file, "w");
                for ($i = 0; $i <= (count($buffer) - 1); $i++) {

                    // *** Use ' character to prevent problems with $ character in password ***
                    //define("DATABASE_HOST",     'localhost');
                    //define("DATABASE_USERNAME", 'root');
                    //define("DATABASE_PASSWORD", 'usbw');
                    //define("DATABASE_NAME",     'humo-gen');

                    if (substr($buffer[$i], 0, 21) == 'define("DATABASE_HOST') {
                        $buffer[$i] = 'define("DATABASE_HOST",     ' . "'" . $_POST['db_host'] . "');\n";
                        $check_config = true;
                    }

                    if (substr($buffer[$i], 0, 25) == 'define("DATABASE_USERNAME') {
                        $buffer[$i] = 'define("DATABASE_USERNAME", ' . "'" . $_POST['db_username'] . "');\n";
                        $check_config = true;
                    }

                    if (substr($buffer[$i], 0, 25) == 'define("DATABASE_PASSWORD') {
                        $buffer[$i] = 'define("DATABASE_PASSWORD", ' . "'" . $_POST['db_password'] . "');\n";
                        $check_config = true;
                    }

                    if (substr($buffer[$i], 0, 21) == 'define("DATABASE_NAME') {
                        $buffer[$i] = 'define("DATABASE_NAME",     ' . "'" . $_POST['db_name'] . "');\n";
                        $check_config = true;
                    }

                    fwrite($bestand_config, $buffer[$i]);
                }
                fclose($bestand_config);
                if ($check_config == false) {
                    $result_message = '<b> *** ' . __('There is a problem in the db_login file, maybe an old db_login file is used.') . ' ***</b>';
                }
            }
        }

        $index['result_message'] = $result_message;
        $index['database_check'] = $database_check;

        return $index;
    }

    public function get_php_version()
    {
        $version = explode('.', phpversion());
        return $version[0];
    }

    public function get_mysql_version($dbh)
    {
        $index['mysql_version'] = '';
        if ($dbh) {
            // in PDO and MySQLi you can't get MySQL version number until connection is made
            // so on very first screens before saving connection parameters we do without.
            // as of Jan 2014 mysql_get_server_info still works but once deprecated will give errors, so better so without.
            // Example, version: 8.4.0
            $index['mysql_version_full'] = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
            $version = explode('.', $index['mysql_version_full']);
            // Example, version: 8
            $index['mysql_version'] = $version[0];
        }
        return $index;
    }
}
