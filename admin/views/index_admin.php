<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 align=center><?= __('Administration'); ?></h1>

<div class="p-3 my-md-2 genealogy_search container-md">
    <div class="row mb-2">
        <div class="col-md-auto">
            <h2><?php printf(__('%s status'), 'HuMo-genealogy'); ?></h2>
        </div>
    </div>

    <?php if (isset($humo_option["version"])) { ?>
        <div class="row mb-2">
            <div class="col-md-4">
                <?php printf(__('%s version'), 'HuMo-genealogy'); ?>
            </div>

            <div class="col-md-auto">
                <?= $humo_option["version"]; ?>
                <a href="index.php?page=extensions"><?php printf(__('%s extensions'), 'HuMo-genealogy'); ?></a>
            </div>
        </div>
    <?php } ?>

    <!-- PHP Version -->
    <div class="row mb-2">
        <div class="col-md-4">
            <?= __('PHP Version'); ?>
        </div>

        <div class="col-md-8">
            <?php if ($index['php_version'] < 8) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= phpversion(); ?>
                    <?= __('It is recommended to update PHP!'); ?>
                </div>
            <?php } else { ?>
                <?= phpversion(); ?>
            <?php } ?>
        </div>
    </div>

    <!-- MySQL Version -->
    <?php if ($index['mysql_version']) { ?>
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('MySQL Version'); ?>
            </div>

            <div class="col-md-8">
                <?php if ($index['mysql_version'] < 8) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $index['mysql_version_full']; ?>
                        <?= __('It is recommended to update MySQL!'); ?>
                    </div>
                <?php } else { ?>
                    <?= $index['mysql_version_full']; ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- Check database -->
    <div class="row mb-2">
        <?php if ($index['database_check']) { ?>

            <div class="col-md-4">
                <?= __('Database'); ?>
            </div>
            <div class="col-md-8">
                <?= __('OK'); ?>
                <font size=-1>(<?= __('Database name'); ?>: <?= DATABASE_NAME; ?>)</font>
            </div>

        <?php } else { ?>

            <div class="col-md-12">
                <?php printf(__('<b>There is no database connection! To connect the MySQL database to %s, fill in these settings:</b>'), 'HuMo-genealogy'); ?><br><br>

                <!-- Get database settings -->
                <form method="post" action="index.php" style="display : inline;">
                    <table class="humo" border="1" cellspacing="0" bgcolor="#DDFD9B">
                        <tr>
                            <th><?= __('Database setting'); ?></th>
                            <th><?= __('Database value'); ?></th>
                            <th><?= __('Example website provider'); ?></th>
                            <th><?= __('Example for XAMPP'); ?></th>
                        </tr>

                        <tr>
                            <td><?= __('Database host'); ?></td>
                            <td><input type="text" name="db_host" value="<?= $index['db_host']; ?>" class="form-control" size="15"></td>
                            <td>localhost</td>
                            <td>localhost</td>
                        </tr>

                        <tr>
                            <td><?= __('Database username'); ?></td>
                            <td><input type="text" name="db_username" value="<?= $index['db_username']; ?>" class="form-control" size="15"></td>
                            <td>database_username</td>
                            <td>root</td>
                        </tr>

                        <tr>
                            <td><?= __('Database password'); ?></td>
                            <td><input type="text" name="db_password" value="<?= $index['db_password']; ?>" class="form-control" size="15"></td>
                            <td>database_password</td>
                            <td><br></td>
                        </tr>

                        <tr>
                            <td><?= __('Database name'); ?></td>
                            <td>
                                <input type="text" name="db_name" value="<?= $index['db_name']; ?>" class="form-control" size="15">
                            </td>
                            <td>database_name</td>
                            <td>humo-gen</td>
                        </tr>

                        <tr>
                            <td><?= __('At a local PC also install database'); ?></td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="install_database" <?= isset($_POST["install_database"]) ? 'checked' : ''; ?>>
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
                </form><br>

                <?= __('Sometimes it\'s needed to add these lines to a /php.ini and admin/php.ini files to activate the PDO driver:'); ?><br>
                extension=pdo.so<br>
                extension=pdo_sqlite.so<br>
                extension=pdo_mysql.so<br>

                <?php if (isset($_POST['install_database']) && !$index['database_check']) { ?>
                    <p><b><?= __('The database has NOT been created!'); ?></b>
                    <?php
                    $index['install_status'] = false;
                }
                    ?>
            </div>

        <?php } ?>
    </div>

    <?php
    // *** Show button to continue installation (otherwise the tables are not recognised) ***
    if (isset($_POST['save_settings_database'])) {
        $index['install_status'] = false;
    ?>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <!-- Show result messages after installing settings of db_login.php -->
                <?= $index['result_message']; ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="install">
                    <input type="submit" name="submit" value="<?= __('Continue installation'); ?>" class="btn btn-success">
                </form>
            </div>
        </div>
    <?php } ?>

    <?php
    $index['database_tables'] = false;
    if ($index['install_status'] && isset($check_tables) && $check_tables) {
        $index['database_tables'] = true;
    }
    ?>
    <?php if ($index['install_status'] && !$index['database_tables']) { ?>
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Database tables'); ?>
            </div>

            <div class="col-md-auto">
                <?php printf(__('No %s tables found in database.'), 'HuMo-genealogy'); ?><br>

                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="install">
                    <input type="submit" name="submit" class="btn btn-success" value="<?php printf(__('Install %s database tables'), 'HuMo-genealogy'); ?>">
                </form>

                <?php $index['install_status'] = false; ?>
            </div>
        </div>
    <?php } ?>

    <?php
    if ($index['install_status']) {
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
    }
    ?>

    <?php if ($index['install_status']) { ?>
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Size of statistics table'); ?>
            </div>

            <div class="col-md-auto">
                <?= $size; ?>
                <a href="index.php?page=statistics"><?= __('If needed remove old statistics.'); ?></a>
            </div>
        </div>
    <?php } ?>

    <?php if ($index['install_status']) { ?>
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
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Size of database'); ?>
            </div>

            <div class="col-md-auto">
                <?php
                if (isset($_GET['optimize'])) {
                    echo '<b>' . __('This may take some time. Please wait...') . '</b><br>';
                    //ob_start();
                    echo __('Optimize table...') . ' humo_persons<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_persons");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_families<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_families");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_unprocessed_tags<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_unprocessed_tags");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_settings<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_settings");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_repositories<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_repositories");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_sources<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_sources");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_texts<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_texts");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_connections<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_connections");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_addresses<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_addresses");

                    //ob_start();
                    echo __('Optimize table...') . ' humo_events<br>';
                    //ob_flush();
                    flush();
                    @$result = $dbh->query("OPTIMIZE TABLE humo_events");
                }
                ?>
                <?= $mbytes; ?> MB <a href="index.php?optimize=1"><?= __('Optimize database.'); ?></a>
            </div>
        </div>
    <?php } ?>

    <?php if ($index['install_status'] == true) { ?>
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

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Status of database backup'); ?>
            </div>

            <div class="col-md-8">
                <?php if (isset($backup_files[0])) { ?>
                    <!-- 2023_02_23_09_56_humo-genealogy_backup.sql.zip -->
                    <?= __('Last database backup') . ': ' . substr($backup_files[0], 8, 2) . '-' . substr($backup_files[0], 5, 2) . '-' . substr($backup_files[0], 0, 4) . '.'; ?>
                    <a href="index.php?page=backup"><?= __('Database backup'); ?></a>
                <?php } else { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= __('No backup file found!'); ?><br>
                        <a href="index.php?page=backup"><?= __('Database backup'); ?></a>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Thumbnail libraries and tools'); ?>
            </div>

            <div class="col-md-8">
                <?php
                $is_thumblib = false;
                $no_windows = (strtolower(substr(PHP_OS, 0, 3)) !== 'win');
                ?>

                <?= __('Imagick (images):'); ?>
                <?php if (extension_loaded('imagick')) { ?>
                    <?= strtolower(__('Yes')); ?><br>

                    <?php if ($no_windows) { ?>
                        - <?= __('Ghostscript (PDF support):'); ?>
                <?php
                        echo (trim(shell_exec('type -P gs'))) ? strtolower(__('Yes')) . '<br>' : strtolower(__('No')) . '<br>';
                        echo '- ' . __('ffmpeg (movie support):') . ' ';
                        echo (trim(shell_exec('type -P ffmpeg'))) ? strtolower(__('Yes')) . '<br>' : strtolower(__('No')) . '<br>';

                        $is_thumblib = true;
                    }
                } else {
                    echo ' ' . strtolower(__('No')) . '<br>';
                }

                echo __('GD (images):');
                if (extension_loaded('gd')) {
                    echo ' ' . strtolower(__('Yes')) . '<br>';
                    $is_thumblib = true;
                } else {
                    echo ' ' . strtolower(__('No')) . '<br>';
                }

                if (!$is_thumblib) {
                    echo __('No Thumbnail library available') . '<br>';
                }
                ?>

                <!-- Auto create thumbnails -->
                <?php if (isset($_POST["thumbnail_auto_create"]) && ($_POST["thumbnail_auto_create"] == 'y' || $_POST["thumbnail_auto_create"] == 'n')) {
                    $db_functions->update_settings('thumbnail_auto_create', $_POST["thumbnail_auto_create"]);
                    $humo_option["thumbnail_auto_create"] = $_POST["thumbnail_auto_create"];
                }
                ?>
                <form method="POST" action="index.php">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <div class="row mb-2">
                        <div class="col-md-auto">
                            <?= __('Automatically create thumbnails?'); ?>
                        </div>
                        <div class="col-md-auto">
                            <select size="1" name="thumbnail_auto_create" onChange="this.form.submit();" class="form-select form-select-sm">
                                <option value="n"><?= __('No'); ?></option>
                                <option value="y" <?= $humo_option["thumbnail_auto_create"] == 'y' ? 'selected' : ''; ?>><?= __('Yes'); ?></option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Media privacy mode -->
                <?php if (isset($_POST["media_privacy_mode"]) && ($_POST["media_privacy_mode"] == 'y' || $_POST["media_privacy_mode"] == 'n')) {
                    $db_functions->update_settings('media_privacy_mode', $_POST["media_privacy_mode"]);
                    $humo_option["media_privacy_mode"] = $_POST["media_privacy_mode"];
                }
                ?>
                <form method="POST" action="index.php">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <div class="row mb-2">
                        <div class="col-md-auto">
                            <?= __('Secure media folder for direct access?'); ?>
                        </div>
                        <div class="col-md-auto">
                            <select size="1" name="media_privacy_mode" onChange="this.form.submit();" class="form-select form-select-sm">
                                <option value="n"><?= __('No'); ?></option>
                                <option value="y" <?= $humo_option["media_privacy_mode"] == 'y' ? 'selected' : ''; ?> disabled><?= __('Yes'); ?></option>
                            </select>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    <?php } ?>



    <?php if ($index['install_status'] == true) { ?>
        <?php
        // *** Check for standard admin username and password ***
        $check_admin_user = false;
        $check_admin_pw = false;
        $check_login = $dbh->query("SELECT * FROM humo_users WHERE user_group_id='1'");
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
        $index['security_status'] = true;
        if ($check_admin_user && $check_admin_pw) {
            $check_login = __('Standard admin username and admin password is used.');
            $check_login .= '<br><a href="index.php?page=users">' . __('Change admin username and password.') . '</a>';
        } elseif ($check_admin_user) {
            $check_login = __('Standard admin username is used.');
            $check_login .= '<br><a href="index.php?page=users">' . __('Change admin username.') . '</a>';
        } elseif ($check_admin_pw) {
            $check_login = __('Standard admin password is used.');
            $check_login .= '<br><a href="index.php?page=users">' . __('Change admin password.') . '</a>';
        } else {
            $check_login = __('OK');
            $index['security_status'] = false;
        }

        // *** Show failed logins ***
        //3600 = 1 uur
        //86400 = 1 dag
        //604800 = 1 week
        //2419200 = 1 maand
        //31536000 = jaar
        $sql = "SELECT count(log_id) as count_failed FROM humo_user_log WHERE log_status='failed' AND UNIX_TIMESTAMP(log_date) > (UNIX_TIMESTAMP(NOW()) - 2419200)";
        $check_login_sql = $dbh->query($sql);
        $check_loginDb = $check_login_sql->fetch(PDO::FETCH_OBJ);
        if ($check_loginDb) {
            $check_login2 = __('Number of failed logins attempts last month') . ': ' . $check_loginDb->count_failed;
            $check_login2 .= '<br><a href="index.php?page=log">' . __('Logfile users') . '</a>';
        }
        ?>
        <div class="p-3 my-md-2 genealogy_search container-md">
            <div class="row mb-2">
                <div class="col-md-auto">
                    <h2><?php printf(__('%s security items'), 'HuMo-genealogy'); ?></h2>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <?= __('Check admin account'); ?>
                </div>

                <div class="col-md-8">
                    <?php if ($index['security_status']) { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $check_login; ?>
                        </div>
                    <?php } else { ?>
                        <?= $check_login; ?>
                    <?php } ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <?= __('Failed login attempts'); ?>
                </div>

                <div class="col-md-8">
                    <?= $check_login2; ?>
                </div>
            </div>


            <?php
            // TODO: check this. This situation doesn't occur?
            $index['wrong_username'] = true;
            // *** Check login ***
            if (isset($_SESSION["user_name_admin"])) {
                $index['wrong_username'] = false;
            } elseif (isset($_SERVER["PHP_AUTH_USER"])) {
                $index['wrong_username'] = false;
            }
            ?>
            <div class="row mb-2">
                <div class="col-md-4">
                    <?= __('Login control'); ?>
                </div>

                <div class="col-md-8 <?= $index['wrong_username'] ? 'bg-warning' : ''; ?> ">
                    <?php
                    // *** Check login ***
                    if (isset($_SESSION["user_name_admin"])) {
                        echo __('At the moment you are logged in through PHP-MySQL.');
                    } elseif (isset($_SERVER["PHP_AUTH_USER"])) {
                        echo __('At the moment you are logged in through an .htacces file.');
                    } else {
                        echo '<b>' . __('The folder "admin" has NOT YET been secured.') . '</b>';
                    }
                    ?>

                    <form method="POST" action="index.php" style="display : inline;">
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

                            <form method="POST" action="index.php" style="display : inline;">
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
                </div>
            </div>

            <?php
            if ($humo_option["debug_front_pages"] == 'n' && $humo_option["debug_admin_pages"] == 'n') {
                $index['debug_front_pages'] = false;
            } else {
                $index['debug_front_pages'] = true;
            }
            ?>

            <!-- HuMo-genealogy debug options -->
            <div class="row mb-2">
                <div class="col-md-4">
                    <?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?>
                </div>

                <div class="col-md-8">
                    <?php if (!$index['debug_front_pages']) { ?>
                        <?= __('OK (option is OFF)'); ?>
                        <a href="index.php?page=settings"><?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?></a>
                    <?php } else { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= __('UNSAFE (option is ON).'); ?><br>
                            <a href="index.php?page=settings"><?php printf(__('Debug %s pages'), 'HuMo-genealogy'); ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    <?php } ?>

    <?php if ($index['install_status'] == true) { ?>
        <div class="p-3 my-md-2 genealogy_search container-md">

            <div class="row mb-2">
                <div class="col-md-auto">
                    <h2><?= __('Family trees'); ?></h2>
                </div>
            </div>

            <?php
            // *** Family trees ***
            $tree_counter = 0;
            $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                // *** Skip empty lines (didn't work in query...) ***
                $tree_counter++;
                $treetext = show_tree_text($dataDb->tree_id, $selected_language);
            ?>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <?= __('Status tree'); ?> <?= $tree_counter; ?>
                    </div>

                    <div class="col-md-8">

                        <?php if ($dataDb->tree_persons > 0) { ?>
                            <?= $dirmark1; ?><a href="index.php?page=tree&amp;tree_id=<?= $dataDb->tree_id; ?>"><?= $treetext['name']; ?></a>
                            <?= $dirmark1; ?> <font size=-1>(<?= $dataDb->tree_persons; ?> <?= __('persons'); ?>, <?= $dataDb->tree_families; ?> <?= __('families'); ?>)</font>
                        <?php } else { ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $dirmark1; ?><a href="index.php?page=tree"><?= $treetext['name']; ?></a>
                                <b><?= __('This tree does not yet contain any data or has not been imported properly!'); ?></b><br>

                                <!-- Read GEDCOM file -->
                                <form method="post" action="index.php" style="display : inline;">
                                    <input type="hidden" name="page" value="tree">
                                    <input type="hidden" name="tree_id" value="<?= $dataDb->tree_id; ?>">
                                    <input type="submit" name="step1" class="btn btn-sm btn-success" value="<?= __('Import Gedcom file'); ?>">
                                </form>

                                <!-- Editor -->
                                <?= __('or'); ?>
                                <form method="post" action="index.php?page=editor" style="display : inline;">
                                    <input type="hidden" name="tree_id" value="<?= $dataDb->tree_id; ?>">
                                    <input type="submit" name="submit" class="btn btn-sm btn-success" value="<?= __('Editor'); ?>">
                                </form>
                            </div>
                        <?php } ?>

                    </div>
                </div>

            <?php } ?>

        </div>
    <?php } ?>
</div>