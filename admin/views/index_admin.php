<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

//TODO convert to MVC files.
?>

<h1 align=center><?= __('Administration'); ?></h1>

<?php
$path_tmp = "index.php";

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
            $database_check = 1;
            $result_message .= __('Database connection: OK!') . '<br>';
        }
        $temp_dbh = null;
    } catch (PDOException $e) {
        unset($database_check);
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


// *******************************************************************************
// *** Show HuMo-genealogy status, use scroll bar to show lots of family trees ***
// *******************************************************************************

//echo '<div style="height:450px; width:850px; overflow-y: auto; margin-left:auto; margin-right:auto;">';
?>
<div style="width:850px; margin-left:auto; margin-right:auto;">
    <table class="humo" width="100%">
        <tr class="table_header">
            <th colspan="2">
                <?php printf(__('%s status'), 'HuMo-genealogy'); ?>
            </th>
        </tr>

        <?php
        // *** HuMo-genealogy version ***
        if (isset($humo_option["version"])) {
            echo '<tr><td class="line_item">';
            printf(__('%s version'), 'HuMo-genealogy');
            echo '</td><td class="line_ok">' . $humo_option["version"];
            echo '&nbsp;&nbsp;&nbsp;<a href="index.php?page=extensions">';
            printf(__('%s extensions'), 'HuMo-genealogy');
            echo '</a></td></tr>';
        }

        // *** PHP Version ***
        $version = explode('.', phpversion());
        if ($version[0] > 7) {
        ?>
            <tr>
                <td class="line_item"><?= __('PHP Version'); ?></td>
                <td class="line_ok"><?= phpversion(); ?></td>
            </tr>
        <?php
        } else {
        ?>
            <tr>
                <td class="line_item"><?= __('PHP Version'); ?></td>
                <td class="line_nok"><?= phpversion(); ?> <?= __('It is recommended to update PHP!'); ?></td>
            </tr>
            <?php
        }

        // *** MySQL Version ***
        if (isset($dbh)) {
            // in PDO and MySQLi you can't get MySQL version number until connection is made
            // so on very first screens before saving connection parameters we do without.
            // as of Jan 2014 mysql_get_server_info still works but once deprecated will give errors, so better so without.
            $mysqlversion = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
            $version = explode('.', $mysqlversion);
            if ($version[0] > 7) {
            ?>
                <tr>
                    <td class="line_item"><?= __('MySQL Version'); ?></td>
                    <td class="line_ok"><?= $mysqlversion; ?></td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td class="line_item"><?= __('MySQL Version'); ?></td>
                    <td class="line_nok"><?= $mysqlversion; ?> <?= __('It is recommended to update MySQL!'); ?></td>
                </tr>
        <?php
            }
        }

        // *** Check if database and tables are ok ***
        $install_status = true;

        // *** Check database, if needed install local database ***
        ?>
        <tr>
            <td class="line_item"><?= __('Database'); ?></td>
            <?php
            if (@$database_check) {
                echo '<td class="line_ok">' . __('OK');
                echo ' <font size=-1>(' . __('Database name') . ': ' . DATABASE_NAME . ')</font>';
            } else {
                echo '<td class="line_nok">';
                printf(__('<b>There is no database connection! To connect the MySQL database to %s, fill in these settings:</b>'), 'HuMo-genealogy');

                $install_status = false;

                $db_host = 'localhost';
                if (isset($_POST['db_host'])) {
                    $db_host = $_POST['db_host'];
                }

                $db_username = 'root';
                if (isset($_POST['db_username'])) {
                    $db_username = $_POST['db_username'];
                }

                $db_password = '';
                if (isset($_POST['db_password'])) {
                    $db_password = $_POST['db_password'];
                }

                $db_name = 'humo-gen';
                if (isset($_POST['db_name'])) {
                    $db_name = $_POST['db_name'];
                }

                $install_database = '';
                if (isset($_POST["install_database"])) {
                    $install_database = ' checked';
                }

                // *** Get database settings ***
            ?>
                <form method="post" action="<?= $path_tmp; ?>" style="display : inline;">
                    <table class="humo" border="1" cellspacing="0" bgcolor="#DDFD9B">
                        <tr>
                            <th><?= __('Database setting'); ?></th>
                            <th><?= __('Database value'); ?></th>
                            <th><?= __('Example website provider'); ?></th>
                            <th><?= __('Example for XAMPP'); ?></th>
                        </tr>

                        <tr>
                            <td><?= __('Database host'); ?></td>
                            <td><input type="text" name="db_host" value="<?= $db_host; ?>" class="form-control" size="15"></td>
                            <td>localhost</td>
                            <td>localhost</td>
                        </tr>

                        <tr>
                            <td><?= __('Database username'); ?></td>
                            <td><input type="text" name="db_username" value="<?= $db_username; ?>" class="form-control" size="15"></td>
                            <td>database_username</td>
                            <td>root</td>
                        </tr>

                        <tr>
                            <td><?= __('Database password'); ?></td>
                            <td><input type="text" name="db_password" value="<?= $db_password; ?>" class="form-control" size="15"></td>
                            <td>database_password</td>
                            <td><br></td>
                        </tr>

                        <tr>
                            <td><?= __('Database name'); ?></td>
                            <td>
                                <input type="text" name="db_name" value="<?= $db_name; ?>" class="form-control" size="15">
                            </td>
                            <td>database_name</td>
                            <td>humo-gen</td>
                        </tr>

                        <tr>
                            <td><?= __('At a local PC also install database'); ?></td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="install_database" <?= $install_database; ?>>
                                    <label class="form-check-label"><?= __('YES, also install database'); ?></label>
                                </div>
                            </td>
                            <td><?= __('NO'); ?></td>
                            <td><?= __('YES'); ?></td>
                        </tr>

                        <tr>
                            <td><?= __('Save settings and connect to database'); ?></td>
                            <td><input type="submit" name="save_settings_database" class="btn btn-success" value="<?= __('Save'); ?>"></td>
                            <td><br></td>
                            <td><br></td>
                        </tr>
                    </table>
                </form>
                <?= __('Sometimes it\'s needed to add these lines to a /php.ini and admin/php.ini files to activate the PDO driver:'); ?><br>
                extension=pdo.so<br>
                extension=pdo_sqlite.so<br>
                extension=pdo_mysql.so<br>
            <?php
            }

            if (isset($_POST['install_database'])) {
                if (isset($database_check) && @$database_check) {
                    //
                } else {
                    //if (!$database_check){
                    echo '<p><b>' . __('The database has NOT been created!') . '</b>';
                    $install_status = false;
                }
            }
            ?>
            </td>
        </tr>
        <?php

        // *** Show button to continue installation (otherwise the tables are not recognised) ***
        if (isset($_POST['save_settings_database'])) {
            $install_status = false;

            // *** Show result messages after installing settings of db_login.php ***
            echo '<tr><td><br></td><td>' . $result_message . '</td></tr>';
            echo '<tr><td><br></td><td><form method="post" action="' . $path_tmp . '" style="display : inline;">';
            echo '<input type="hidden" name="page" value="admin">';
            echo '<input type="submit" name="submit" value="' . __('Continue installation') . '" class="btn btn-success">';
            echo '</form></td></tr>';
        }

        // *** Only show table status if database is checked ***
        if ($install_status == true) {
            // *** Check database tables ***
            if (isset($check_tables) && $check_tables) {
        ?>
                <tr>
                    <td class="line_item"><?= __('Database tables'); ?></td>
                    <td class="line_ok"><?= __('OK'); ?></td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td class="line_item"><?= __('Database tables'); ?></td>
                    <td class="line_nok">
                        <?php printf(__('No %s tables found in database.'), 'HuMo-genealogy'); ?><br>

                        <form method="post" action="<?= $path_tmp; ?>" style="display : inline;">
                            <input type="hidden" name="page" value="install">
                            <input type="submit" name="submit" class="btn btn-success" value="<?php printf(__('Install %s database tables'), 'HuMo-genealogy'); ?>">
                        </form>
                    </td>
                </tr>

            <?php
                $install_status = false;
            }
        }

        // *** Only show table status if database AND tables are checked ***
        if ($install_status == true) {
            // *** Show size of statistics table ***
            $sizeqry = $dbh->query('SHOW TABLE STATUS LIKE "humo_stat_date"');
            $sizeDb = $sizeqry->fetch(PDO::FETCH_OBJ);
            $size = '0 kB';
            if ($sizeDb) {
                $size = $sizeDb->Data_length;
                $bytes = array(' kB', ' MB', ' GB', ' TB');
                $size /= 1024;
                foreach ($bytes as $val) {
                    if (1024 <= $size) {
                        $size /= 1024;
                        continue;
                    }
                    break;
                }
                $size = round($size, 1) . $val;
            }

            ?>
            <tr>
                <td class="line_item"><?= __('Size of statistics table'); ?></td>
                <td class="line_ok"><?= $size; ?>
                    <a href="index.php?page=statistics"><?= __('If needed remove old statistics.'); ?></a>
                </td>
            </tr>
            <?php

            // *** Show size of database and optimize option ***
            $size = 0;
            $sizeqry = $dbh->query('SHOW TABLE STATUS');
            while ($sizeDb = $sizeqry->fetch(PDO::FETCH_OBJ)) {
                if (is_numeric($sizeDb->Data_length)) {
                    $size += $sizeDb->Data_length;
                }
                if (is_numeric($sizeDb->Index_length)) {
                    $size += $sizeDb->Index_length;
                }
            }
            $decimals = 2;
            $mbytes = number_format($size / (1024 * 1024), $decimals);
            ?>
            <tr>
                <td class="line_item">
                    <?= __('Size of database'); ?></td>
                <td class="line_ok">
                    <?php
                    if (isset($_GET['optimize'])) {
                        echo '<b>' . __('This may take some time. Please wait...') . '</b><br>';
                        //ob_start();
                        echo __('Optimize table...') . ' humo_persons<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_persons";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_families<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_families";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_unprocessed_tags<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_unprocessed_tags";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_settings<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_settings";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_repositories<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_repositories";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_sources<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_sources";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_texts<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_texts";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_connections<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_connections";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_addresses<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_addresses";
                        @$result = $dbh->query($sql);

                        //ob_start();
                        echo __('Optimize table...') . ' humo_events<br>';
                        //ob_flush();
                        flush();
                        $sql = "OPTIMIZE TABLE humo_events";
                        @$result = $dbh->query($sql);
                    }
                    ?>
                    <?= $mbytes; ?> MB <a href="index.php?optimize=1"><?= __('Optimize database.'); ?></a>
                </td>
            </tr>
            <?php

            // *** Check last database backup ***
            // *** Get list of backup files ***
            if (is_dir('./backup_files')) {
                $dh  = opendir('./backup_files');
                while (false !== ($filename = readdir($dh))) {
                    if (substr($filename, -4) === ".sql" || substr($filename, -8) === ".sql.zip") {
                        $backup_files[] = $filename;
                    }
                }
                $backup_count = 0;
                if (isset($backup_files)) {
                    $backup_count = count($backup_files);
                    rsort($backup_files); // *** Most recent backup file will be shown first ***
                }
            }

            ?>
            <tr>
                <td class="line_item"><?= __('Status of database backup'); ?></td>
                <?php
                if (isset($backup_files[0])) {
                    // 2023_02_23_09_56_humo-genealogy_backup.sql.zip
                    $backup_status = __('Last database backup') . ': ' . substr($backup_files[0], 8, 2) . '-' . substr($backup_files[0], 5, 2) . '-' . substr($backup_files[0], 0, 4) . '.';
                ?>
                    <td class="line_ok"><?= $backup_status; ?>
                    <?php } else { ?>
                    <td class="line_nok"><?= __('No backup file found!'); ?>
                    <?php } ?>
                    <a href="index.php?page=backup"><?= __('Database backup'); ?></a>
                    </td>
            </tr>
            <?php

            ?>
            <tr class="table_header">
                <th colspan="2">
                    <?php printf(__('%s security items'), 'HuMo-genealogy'); ?>
                </th>
            </tr>
            <?php

            // *** Check for standard admin username and password ***
            $check_admin_user = false;
            $check_admin_pw = false;
            $sql = "SELECT * FROM humo_users WHERE user_group_id='1'";
            $check_login = $dbh->query($sql);
            while ($check_loginDb = $check_login->fetch(PDO::FETCH_OBJ)) {
                if ($check_loginDb->user_name == 'admin') {
                    $check_admin_user = true;
                }
                if ($check_loginDb->user_password == MD5('humogen')) {
                    $check_admin_pw = true;
                } // *** Check old password method ***
                $check_password = password_verify('humogen', $check_loginDb->user_password_salted);
                if ($check_password) {
                    $check_admin_pw = true;
                }
            }
            if ($check_admin_user && $check_admin_pw) {
                $check_login = '<td class="line_nok">' . __('Standard admin username and admin password is used.');
                $check_login .= '<br><a href="index.php?page=users">' . __('Change admin username and password.') . '</a>';
            } elseif ($check_admin_user) {
                $check_login = '<td class="line_nok">' . __('Standard admin username is used.');
                $check_login .= '<br><a href="index.php?page=users">' . __('Change admin username.') . '</a>';
            } elseif ($check_admin_pw) {
                $check_login = '<td class="line_nok">' . __('Standard admin password is used.');
                $check_login .= '<br><a href="index.php?page=users">' . __('Change admin password.') . '</a>';
            } else {
                $check_login = '<td class="line_ok">' . __('OK');
            }
            echo '<tr><td class="line_item">' . __('Check admin account') . '</td>' . $check_login;

            // *** Show failed logins ***
            //3600 = 1 uur
            //86400 = 1 dag
            //604800 = 1 week
            //2419200 = 1 maand
            //31536000 = jaar
            $sql = "SELECT count(log_id) as count_failed FROM humo_user_log
                WHERE log_status='failed'
                AND UNIX_TIMESTAMP(log_date) > (UNIX_TIMESTAMP(NOW()) - 2419200)";
            $check_login = $dbh->query($sql);
            $check_loginDb = $check_login->fetch(PDO::FETCH_OBJ);
            if ($check_loginDb) {
                $check_login = '<td class="line_ok">' . __('Number of failed logins attempts last month') . ': ' . $check_loginDb->count_failed;
                $check_login .= '<br><a href="index.php?page=log">' . __('Logfile users') . '</a>';
            }
            //else
            //	$check_login='<td class="line_ok">'.__('OK');
            echo '<tr><td class="line_item">' . __('Failed login attempts') . '</td>' . $check_login;


            // *** Check login ***
            $check_login = '<td class="line_nok"><b>' . __('The folder "admin" has NOT YET been secured.') . '</b>';
            if (isset($_SERVER["PHP_AUTH_USER"])) {
                $check_login = '<td class="line_ok">' . __('At the moment you are logged in through an .htacces file.');
            }
            if (isset($_SESSION["user_name_admin"])) {
                $check_login = '<td class="line_nok">' . __('At the moment you are logged in through PHP-MySQL.');
            }

            ?>
            <tr>
                <td class="line_item"><?= __('Login control'); ?></td><?= $check_login; ?>

                <form method="POST" action="<?= $path_tmp; ?>" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="submit" name="login_info" class="btn btn-sm btn-success" value="<?= __('INFO'); ?>">
                </form>

                <?php if (isset($_POST['login_info'])) { ?>
                    <div id="security_remark">

                        <?php printf(__('After installation of the tables (click on the left at Install) the admin folder will be secured with PHP-MySQL security.
<p>You can have better security with .htaccess (server security).<br>
If the administration panel of your webhost has an option to password-protect directories, use this option on the \"admin\" folder of %s. If you don\'t have such an option, you can make an .htaccess file yourself.<br>
Make a file .htaccess:'), 'HuMo-genealogy'); ?>

                        <p>AuthType Basic<br>
                            AuthName "<?= __('Secured website'); ?>"<br>
                            AuthUserFile <?= $_SERVER['DOCUMENT_ROOT']; ?>/humo-gen/admin/.htpasswd<br>
                            &lt;LIMIT GET POST&gt;<br>
                            require valid-user<br>
                            &lt;/LIMIT&gt;';

                        <p><?= __('Next, you need a file with user names and passwords.<br>
For example go to: http://www.htaccesstools.com/htpasswd-generator/<br>
The file .htpasswd will look something like this:<br>'); ?>

                        <p>Huub:mmb95Tozzk3a2</p>

                        <form method="POST" action="<?= $path_tmp; ?>" style="display : inline;">
                            <p><?= __('You can also try this password generator:'); ?><br>
                                <input type="hidden" name="page" value="<?= $page; ?>">
                                <input type="text" name="username" value="username" class="form-control" size="20"><br>
                                <input type="text" name="password" value="password" class="form-control" size="20"><br>
                                <input type="submit" name="login_info" class="btn btn-sm btn-success" value="<?= __('Generate new ht-password'); ?>">
                        </form>
                        <?php

                        if (isset($_POST['username'])) {
                            //$htpassword=crypt(trim($_POST['password']),base64_encode(CRYPT_STD_DES));
                            $htpassword2 = crypt($_POST['password'], base64_encode($_POST['password']));
                            //echo $_POST['username'].":".$htpassword.'<br>';
                            echo $_POST['username'] . ":" . $htpassword2;
                        }

                        ?>
                    </div>
                <?php } ?>
                </td>
            </tr>
            <?php

            // *** display_errors ***
            /*
            if (!ini_get('display_errors')) {
                ?>
                <tr><td class="line_item"><?= __('Option "display_errors"');?></td><td class="line_ok"><?= __('OK (option is OFF)');?></td></tr>
                <?php
            } else {
                ?>
                <tr><td class="line_item"><?= __('Option "display_errors"');?></td><td class="line_nok"><?= __('UNSAFE (option is ON)<br>change this option in .htaccess file.');?></td></tr>
                <?php
            }
            */

            // *** HuMo-genealogy debug options ***
            ?>
            <tr>
                <td class="line_item">
                    <?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?>
                </td>
                <?php if ($humo_option["debug_front_pages"] == 'n' && $humo_option["debug_admin_pages"] == 'n') { ?>
                    <td class="line_ok"><?= __('OK (option is OFF)'); ?>
                        <a href="index.php?page=settings">
                            <?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?>
                        </a>
                    </td>
                <?php } else { ?>
                    <td class="line_nok"><?= __('UNSAFE (option is ON).'); ?>
                        <a href="index.php?page=settings">
                            <?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?>
                        </a>
                    </td>
                <?php } ?>
            </tr>

            <?php
            // *** Family trees ***
            $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
            if ($datasql) {
                $tree_counter = 0;
            ?>
                <tr class="table_header">
                    <th colspan="2"><?= __('Family trees'); ?></th>
                </tr>

                <?php
                while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                    // *** Skip empty lines (didn't work in query...) ***
                    $tree_counter++;
                    $treetext = show_tree_text($dataDb->tree_id, $selected_language);
                ?>
                    <tr>
                        <td class="line_item"><?= __('Status tree'); ?> <?= $tree_counter; ?></td>
                        <?php
                        if ($dataDb->tree_persons) {
                            echo '<td class="line_ok">';
                        } else {
                            echo '<td class="line_nok">';
                        }
                        echo $dirmark1 . '<a href="index.php?page=tree">' . $treetext['name'] . '</a>';

                        if ($dataDb->tree_persons > 0) {
                            print $dirmark1 . ' <font size=-1>(' . $dataDb->tree_persons . ' ' . __('persons') . ', ' . $dataDb->tree_families . ' ' . __('families') . ')</font>';
                        } else {
                        ?>
                            <b><?= __('This tree does not yet contain any data or has not been imported properly!'); ?></b><br>

                            <!-- Read GEDCOM file -->
                            <form method="post" action="<?= $path_tmp; ?>" style="display : inline;">
                                <input type="hidden" name="page" value="tree">
                                <input type="hidden" name="tree_id" value="<?= $dataDb->tree_id; ?>">
                                <input type="submit" name="step1" class="btn btn-sm btn-success" value="<?= __('Import Gedcom file'); ?>">
                            </form>

                            <!-- Editor -->
                            <?= __('or'); ?> <form method="post" action="index.php?page=editor" style="display : inline;">
                                <input type="hidden" name="tree_id" value="<?= $dataDb->tree_id; ?>">
                                <input type="submit" name="submit" class="btn btn-sm btn-success" value="<?= __('Editor'); ?>">
                            </form>
                        <?php } ?>
                        </td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td><?= __('Trees table'); ?></td>
                    <td class="line_nok">ERROR</td>
                </tr>
        <?php
            }

            // *** End of check database and table status ***
        }
        ?>
    </table>
</div>