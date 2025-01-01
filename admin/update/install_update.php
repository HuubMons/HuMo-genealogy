<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

@set_time_limit(300);

// TODO MVC use $install_update

// *** Check source of download file ***
if (isset($update['version_auto_download'])) {
    $install_update['source'] = $update['version_auto_download'];
}

if (isset($update['version_auto_download_github'])) {
    $install_update['source_github'] = $update['version_auto_download_github'];
    if (!$install_update['source_github']) {
        $install_update['source_github'] = 'https://github.com/HuubMons/HuMo-genealogy/archive/refs/heads/master.zip';
    }
}

// *** Source of beta download file ***
if (isset($_GET['install_beta'])) {
    $update['up_to_date'] = 'no';
    $update['version_auto_download'] = $update['beta_version_auto_download'];

    $install_update['source_github'] = $update['beta_version_auto_download_github'];
    if (!$install_update['source_github']) {
        $install_update['source_github'] = 'https://github.com/HuubMons/HuMo-genealogy/archive/refs/heads/beta_version.zip';
    }
}

$update['destination'] = 'update/humo-gen_update.zip';


// *** Re-install some or all files ***
if (isset($_GET['re_install'])) {
    $update['up_to_date'] = 'no';
}
?>

<h1 class="center">
    <?php printf(__('%s Update'), 'HuMo-genealogy'); ?>
</h1>

<?php if (isset($update['up_to_date']) && $update['up_to_date'] == 'yes') { ?>

    <div class="genealogy_search p-2">
        <!-- Show HuMo-genealogy version number -->
        <h2>HuMo-genealogy</h2>
        <?= __('Version:'); ?>
        <?= isset($humo_option["version"]) ? $humo_option["version"] . '.' : __('no version number available...'); ?>
        <?php printf(__('%s is up-to-date!'), 'HuMo-genealogy'); ?><br>

        <a href="index.php?page=install_update&re_install=1&auto=1&update_check=1">
            <?php printf(__('If needed: re-install all or some of the %s files'), 'HuMo-genealogy'); ?>
        </a>
    </div>

    <div class="genealogy_search mt-2 p-2">
        <!-- Check for HuMo-genealogy beta version -->
        <h2><?php printf(__('%s beta version'), 'HuMo-genealogy'); ?></h2>

        <?= __('Sometimes there is a beta version available.'); ?>

        <?php if (strtotime($update['beta_version_date']) - strtotime($humo_option["version_date"]) > 0) { ?>
            <a href="index.php?page=install_update&install_beta=1&auto=1&update_check=1">
                <?php printf(__('%s beta version available'), 'HuMo-genealogy'); ?> (<?= $update['beta_version']; ?>)!
            </a>
        <?php } else { ?>
            <?= __('No beta version available.'); ?>
        <?php } ?>
    </div>

    <div class="genealogy_search mt-2 p-2">
        <h2>
            <?= __('Settings'); ?>
        </h2>

        <!-- Check for HuMo-genealogy beta version SAME CODE AS CODE BELOW... -->
        <?php printf(__('Enable/ disable %s update check.'), 'HuMo-genealogy'); ?><br>

        <form method="post" action="index.php?page=install_update&update_check=1" class="mb-3">
            <input type="checkbox" name="enable_update_check" <?= $humo_option['update_last_check'] != 'DISABLED' ? 'checked' : ''; ?> onChange="this.form.submit();">
            <?php printf(__('Check regularly for %s updates.'), 'HuMo-genealogy'); ?><br>
            <input type="hidden" name="enable_update_check_change" value="1">
        </form>

        <!-- Debug update check -->
        <?php printf(__('Debug %s update'), 'HuMo-genealogy'); ?><br>
        <form method="post" action="index.php?page=install_update&update_check=1" style="display : inline">
            <input type="submit" name="debug_update" value="<?= __('Debug'); ?>" class="btn btn-secondary btn-sm">
        </form>
    </div>

    <?php
} elseif (isset($update['up_to_date']) && $update['up_to_date'] == 'no') {

    if (isset($_GET['auto'])) {
    ?>
        <h2><?= __('Automatic update'); ?></h2>

        <?= __('Every step can take some time, please be patient and wait till the step is completed!'); ?><br><br>

        <?php
        /*
        // Problem: HuMo-genealogy = 11 MB. Could be too large to upload for some providers in standard form without changing parameters.

        // *** Only upload .ged or .zip files ***
        if (isset($_POST['optional_upload'])) {
            if (strtolower(substr($_FILES['upload_file']['name'], -4)) == '.zip' or strtolower(substr($_FILES['upload_file']['name'], -4)) == '.ged') {
                $new_upload = 'update/humo-gen_update.zip';
                // *** Move and check for succesful upload ***
                echo '<p><b>' . $new_upload . '<br>';
                if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $new_upload))
                    echo __('File successfully uploaded.') . '</b>';
                else
                    echo __('Upload has failed.') . '</b>';
            }
        }

        $reinstall = '';
        if (isset($_GET['re_install'])) {
            $reinstall = '&amp;re_install=1';
        }

        <?= __('Optional: manually upload new version.'); ?>
        <?php printf(__('First download latest %s version from Github or Sourceforge.'), 'HuMo-genealogy'); ?>

        <form name='uploadform' enctype='multipart/form-data' action="index.php?page=install_update&auto=1&step=1&update_check=1<?= $reinstall; ?>" method="post">
            <input type="file" name="upload_file">
            <input type="submit" name="optional_upload" value="Upload">
        </form><br>
        */

        printf(__('Step 1) Download and unzip new %s version'), 'HuMo-genealogy');
        echo '<br>';

        if (!isset($_GET['step'])) {
        ?>
            <a href="index.php?page=install_update&auto=1&step=1&update_check=1<?= isset($_GET['install_beta']) ? '&install_beta=1' : ''; ?><?= isset($_GET['re_install']) ? '&re_install=1' : ''; ?>">
                <?php printf(__('Download and unzip new %s version'), 'HuMo-genealogy'); ?>
            </a><br>

            <h2><?php printf(__('%s version history'), 'HuMo-genealogy'); ?></h2>

            <p><iframe height=" 300" width="80%" src="https://humo-gen.com/genforum/viewforum.php?f=19"></iframe></p>
            <?php
        }

        // *** STEP 1: Download humo-genealogy.zip and unzip to update folder ***
        if (isset($_GET['step']) && $_GET['step'] == '1') {
            $download = false;

            // *** Check update folder permissions ***
            if (!is_writable('update')) {
                echo '<b>ERROR: Folder admin/update is NOT WRITABLE. Please change permissions.</b><br>';
            }

            // *** No download of software update, manually put in update folder! ***
            if (file_exists('update/humo-gen_update.zip')) {
                echo '<b>' . __('Found update file, skipped download of update!') . '</b><br>';
                $download = true;
            }

            // *** First try Github using file_get_contents ***
            if (!$download) {
                // Download the ZIP archive
                $result = file_put_contents($update['destination'], file_get_contents($install_update['source_github']));
                if (!$result) {
            ?>
                    <b>Download 1 from Github failed.</b><br>
            <?php
                } else {
                    $download = true;
                }
            }

            // *** Download using CURL ***
            if (!$download) {
                // *** Copy HuMo-genealogy update to server using curl ***
                if (function_exists('curl_exec')) {
                    // *** First try to download from GitHub ***
                    $resource = curl_init();
                    curl_setopt($resource, CURLOPT_URL, $install_update['source_github']);
                    curl_setopt($resource, CURLOPT_HEADER, false);
                    curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 30);

                    // *** Nov 2023: Don't use CURL cache ***
                    curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                    // *** Added for GitHub ***
                    curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($resource, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

                    $content = curl_exec($resource);
                    curl_close($resource);
                    if ($content != '') {
                        $fp = fopen($update['destination'], 'w');
                        $fw = fwrite($fp, $content); //returns false on failure
                        fclose($fp);
                        if ($fw != false) {
                            $download = true;
                        }
                    }

                    // *** Download failed from Github, now try humo-gen.com ***
                    if ($download == false) {
                        // TODO translate?
                        echo '<b>Download 2 from Github failed. Now trying to download from HuMo-genealogy website.</b><br>';

                        $resource = curl_init();
                        curl_setopt($resource, CURLOPT_URL, $update['source']);
                        curl_setopt($resource, CURLOPT_HEADER, false);
                        curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 30);
                        $content = curl_exec($resource);
                        curl_close($resource);
                        if ($content != '') {
                            $fp = fopen($update['destination'], 'w');
                            $fw = fwrite($fp, $content);
                            fclose($fp);
                            if ($fw != false) {
                                $download = true;
                            }
                        }
                    }
                }
                // *** copy HuMo-genealogy to server using copy ***
                else {
                    // TODO translate?
                    echo '<b>Option curl is disabled. Now trying to copy file from HuMo-genealogy website.</b><br>';

                    if (copy($update['version_auto_download'], 'update/humo-gen_update.zip')) {
                        $download = true;
                    }
                }
            }


            if (!$download) {
                echo 'ERROR: automatic download failed...<br>';
            } else {
                if (is_file('update/humo-gen_update.zip')) {
                    echo __('Automatic download successfull!') . '<br>';
                } else {
                    echo __('Automatic download of file failed.') . '<br>';
                    exit;
                }

                // *** Unzip downloaded file ***
                $zip = new ZipArchive;
                if ($zip->open("update/humo-gen_update.zip") === TRUE) {
                    $zip->extractTo('update/humo-gen_update');
                    $zip->close();
                    echo __('File successfully unzipped!') . '<br>';

                    // *** July 2022: Archive from GitHub contains master folder ***
                    // Change folder: update/humo-gen_update/HuMo-genealogy-master
                    // Into: update/humo-gen_update
                    if (is_dir('update/humo-gen_update/HuMo-genealogy-master')) {
                        rename('update/humo-gen_update', 'update/humo-gen_update_temp');
                        rename('update/humo-gen_update_temp/HuMo-genealogy-master', 'update/humo-gen_update');
                        rmdir('update/humo-gen_update_temp');
                    }

                    echo '<br>' . __('Step 2)') . ' <a href="index.php?page=install_update&auto=1&step=2&update_check=1';
                    if (isset($_GET['install_beta'])) {
                        echo '&install_beta=1';
                    }
                    if (isset($_GET['re_install'])) {
                        echo '&re_install=1';
                    }
                    echo '">' . __('Check files') . '</a><br>';
                } else {
                    echo 'ERROR: unzip failed!<br>';

                    unlink('update/humo-gen_update.zip');
                    echo 'Update failed, update file is removed.<br>';
                }
            }
        }

        // *** STEP 2: check files ***
        if (isset($_GET['step']) && ($_GET['step'] == '2' || $_GET['step'] == '3')) {
            ?>
            <br><?= __('Step 2) Compare existing and update files (no installation yet)...'); ?><br>

            <?php if ($_GET['step'] == '3') { ?>
                <br><?= __('Step 3) Installation of new files...'); ?><br>
            <?php
            }

            function listFolderFiles($dir, $exclude, $file_array)
            {
                global $existing_dir_files, $existing_dir, $existing_files;
                global $update_dir_files, $update_dir, $update_files;
                $ffs = scandir($dir);
                foreach ($ffs as $ff) {
                    if (is_array($exclude) and !in_array($ff, $exclude)) {
                        if ($ff != '.' && $ff != '..') {
                            // *** Skip all media files in folders ../media/, ../media/cms/ etc.
                            if (substr($dir, 0, 8) == '../media' and !is_dir($dir . '/' . $ff) and $ff != 'readme.txt') {
                                // skip media files
                            }
                            // *** Skip all backup files in folder backup_files/
                            // ../admin/backup_files/2023_02_11_09_56_humo-genealogy_backup.sql.zip
                            elseif (substr($dir, 0, 21) == '../admin/backup_files' and !is_dir($dir . '/' . $ff) and $ff != 'readme.txt') {
                                // skip backup files
                            } else {
                                if ($file_array == 'existing_files') {
                                    $existing_dir[] = $dir;
                                    $existing_files[] = $ff;
                                    $existing_dir_files[] = substr($dir . '/' . $ff, 3);
                                } else {
                                    $update_dir[] = $dir;
                                    $update_files[] = $ff;
                                    $update_dir_files[] = substr($dir . '/' . $ff, 25);
                                }
                                if (is_dir($dir . '/' . $ff)) listFolderFiles($dir . '/' . $ff, $exclude, $file_array);
                            }
                        }
                    }
                }
            }

            // *** Find all existing HuMo-genealogy files, skip humo-gen_update.zip, humo-gen_update folder and ip_files folders. ***
            listFolderFiles('..', array('humo-gen_update.zip', 'humo-gen_update', 'ip_files'), 'existing_files');

            // *** Find all update HuMo-genealogy files, a__ is just some random text (skip items)... ***
            listFolderFiles('./update/humo-gen_update', array('a__', 'a__'), 'update_files');
            ?>

            <form method="POST" action="index.php?page=install_update&auto=1&step=3&update_check=1';<?= isset($_GET['install_beta']) ? '&install_beta=1' : ''; ?><?= isset($_GET['re_install']) ? '&re_install=1' : ''; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <div style="border:1px solid black;height:200px;width:700px;overflow:scroll">
                    <?php
                    // *** Compare new files with old files, show list of renewed files ***
                    for ($i = 0; $i <= count($update_files) - 1; $i++) {
                        if (!is_dir($update_dir[$i] . '/' . $update_files[$i])) {
                            $key = array_search($update_dir_files[$i], $existing_dir_files);

                            $exist_sha1 = sha1_file($existing_dir[$key] . '/' . $existing_files[$key]);
                            $update_sha1 = sha1_file($update_dir[$i] . '/' . $update_files[$i]);
                            if ($exist_sha1 !== $update_sha1) {
                                $create_file = '../' . substr($update_dir[$i] . '/' . $update_files[$i], 25);

                                // *** Optional installation of file ***
                    ?>
                                <input type="Checkbox" name="install_file<?= $i; ?>" value="yes" <?= ($_GET['step'] == '3' && !isset($_POST['install_file' . $i])) ? '' : ' checked'; ?>> <?= $create_file; ?>
                    <?php

                                // *** Copy update file to existing file ***
                                if ($_GET['step'] == '3' && isset($_POST['install_file' . $i])) {
                                    if (!copy($update_dir[$i] . '/' . $update_files[$i], $create_file)) {
                                        echo ' <b>' . __('Installation of file failed') . '</b>';
                                    } else {
                                        echo ' ' . __('File installed!');
                                    }
                                }
                                echo '<br>';
                            }
                        } else {
                            // *** Compare directory ***
                            $key = array_search($update_dir_files[$i], $existing_dir_files);
                            if ($key) {
                                //echo $update_dir[$i].'/'.$update_files[$i];
                                //echo 'OK';
                            } else {
                                $create_dir = '../' . substr($update_dir[$i] . '/' . $update_files[$i], 25);
                                echo $create_dir;
                                if ($_GET['step'] == '3' && mkdir($create_dir)) {
                                    echo ' ' . __('Directory created.');
                                }
                                echo '<br>';
                            }
                        }
                    }
                    ?>
                </div><br>

                <?php
                printf(__('The following files are not part of the %s system files (anymore). If you want, they can be removed.'), 'HuMo-genealogy');

                echo '<br><a href="index.php?page=install_update&auto=1&step=2&update_check=1';
                if (isset($_GET['install_beta'])) {
                    echo '&install_beta=1';
                }
                if (isset($_GET['re_install'])) {
                    echo '&re_install=1';
                }
                echo '&amp;select_all=1">' . __('Select all files (recommended option, please check these files first!)') . '</a><br>';
                ?>
                <div style="border:1px solid black;height:200px;width:700px;overflow:scroll">
                    <?php
                    // *** Compare new files with old files, show list of old system files ***
                    //for ($i=0; $i<=count($existing_files)-1; $i++){
                    // *** Skip first file (.htaccess) because of comparison problems ***
                    for ($i = 1; $i <= count($existing_files) - 1; $i++) {
                        if (!is_dir($existing_dir[$i] . '/' . $existing_files[$i])) {
                            $key = array_search($existing_dir_files[$i], $update_dir_files);
                            if (!$key) {
                                $create_file = $existing_dir[$i] . '/' . $existing_files[$i];
                                // *** Optional removal of file ***
                                $check = '';
                                if (isset($_GET['select_all'])) {
                                    $check = ' checked';
                                }
                                if ($_GET['step'] == '3' && isset($_POST['remove_file' . $i])) {
                                    $check = ' checked';
                                }
                                echo '<input type="Checkbox" name="remove_file' . $i . '" value="yes"' . $check . '> ';
                                echo $create_file;

                                if ($_GET['step'] == '3' && isset($_POST['remove_file' . $i])) {
                                    unlink($create_file);
                                }
                                echo '<br>';
                            }
                        }
                    }
                    ?>
                </div>

                <?php if ($_GET['step'] == '3' && DATABASE_HOST) { ?>
                    <br><?= __('Update new db_login.php file...'); ?><br>

                    <?php
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
                            //define("DATABASE_PASSWORD", '');
                            //define("DATABASE_NAME",     'humo-gen');

                            if (substr($buffer[$i], 8, 13) == 'DATABASE_HOST') {
                                $buffer[$i] = 'define("DATABASE_HOST",     ' . "'" . DATABASE_HOST . "');\n";
                                $check_config = true;
                            }

                            if (substr($buffer[$i], 8, 17) == 'DATABASE_USERNAME') {
                                $buffer[$i] = 'define("DATABASE_USERNAME", ' . "'" . DATABASE_USERNAME . "');\n";
                                $check_config = true;
                            }

                            if (substr($buffer[$i], 8, 17) == 'DATABASE_PASSWORD') {
                                $buffer[$i] = 'define("DATABASE_PASSWORD", ' . "'" . DATABASE_PASSWORD . "');\n";
                                $check_config = true;
                            }

                            if (substr($buffer[$i], 8, 13) == 'DATABASE_NAME') {
                                $buffer[$i] = 'define("DATABASE_NAME",     ' . "'" . DATABASE_NAME . "');\n";
                                $check_config = true;
                            }

                            fwrite($bestand_config, $buffer[$i]);
                        }
                        fclose($bestand_config);
                        if ($check_config == false) {
                            $result_message = '<b> *** ' . __('There is a problem in the db_login file, maybe an old db_login file is used.') . ' ***</b>';
                        } else {
                            $result_message = __('File is updated.');
                        }
                        echo $result_message;
                    }
                }

                if ($_GET['step'] == '2') {
                    ?>
                    <br><input type="submit" name="submit" value="<?= __('Install files!'); ?>" class="btn btn-success btn-sm"><br><br>
                <?php
                } else {
                    // *** Update settings ***
                    $dbh->query("UPDATE humo_settings SET setting_value='2012-01-01' WHERE setting_variable='update_last_check'");
                    $humo_option['update_last_check'] = '2012-01-01';

                    // *** Remove installation files ***
                    unlink("update/humo-gen_update.zip");
                    // *** Count down, because files must be removed first before removing directories ***
                    for ($i = count($update_files) - 1; $i >= 0; $i--) {
                        if (!is_dir($update_dir[$i] . '/' . $update_files[$i])) {
                            unlink($update_dir[$i] . '/' . $update_files[$i]);
                        } else {
                            rmdir($update_dir[$i] . '/' . $update_files[$i]);
                        }
                    }
                    rmdir("update/humo-gen_update");
                ?>

                    <br><br><?= __('Update completed and installation files removed!'); ?><br><br>
                <?php
                }
                ?>
            </form>
        <?php
        }
    } else {
        if (isset($humo_option["version"])) {
            printf(__('Current %s version:'), 'HuMo-genealogy');
            echo ' ' . $humo_option["version"] . '.<br>';

            printf(__('Available %s version:'), 'HuMo-genealogy');
            echo ' ' . $update['version'] . '.<br><br>';
        }

        echo __('There are 2 update methods: automatic and manually.');
        ?>

        <h2><?php printf(__('1) Automatic update of %s'), 'HuMo-genealogy'); ?></h2>
        <?php
        echo __('a) [Optional, but highly recommended] Do a database backup first') . ': <a href="index.php?page=backup">' . __('backup page') . '.</a><br>';
        echo __('b)') . ' <a href="index.php?page=install_update&auto=1&update_check=1">' . __('Start automatic update') . '.</a><br>';
        ?>

        <h2><?php printf(__('1) Manual update of %s'), 'HuMo-genealogy'); ?></h2>
    <?php
        echo __('a) [Optional, but highly recommended] Do a database backup first') . ': <a href="index.php?page=backup">' . __('backup page') . '.</a><br>';

        echo __('b) Download the new version: ') . ' <a href="' . $update['version_download'] . '" target="_blank">';
        printf(__('%s version:'), 'HuMo-genealogy');
        echo ' ' . $update['version'] . '</a><br>';

        printf(__('c) Unzip the file and replace the old %s files by the new %s files.'), 'HuMo-genealogy', 'HuMo-genealogy');
        echo '<br>';
        echo __('Full installation and update instructions can be found at:') . ' <a href="https://sourceforge.net/projects/humo-gen/files/HuMo-gen_Manual/" target="_blank">';
        printf(__('%s manual'), 'HuMo-genealogy');
        echo '</a>';
    }
} else {
    // *** Semi-automatic update. REMARK: to test this, just disable two lines "$content_array" in index.php ***
    ?>
    <?= __('Online version check unavailable.'); ?>
    <h2><?= __('Semi-automatic update'); ?></h2>
    <?= __('In some cases the automatic update doesn\'t work. Then use this semi-automatic update method.'); ?><br>
    <?php printf(__('1. Download a new version of %s.'), 'HuMo-genealogy'); ?><br>
    <?= __('2. Rename the zip file into: humo-gen_update.zip'); ?><br>

    <?php
    // *** Upload file ***
    if (isset($_FILES['update_file']) && $_FILES['update_file']['name']) {
        $fault = "";
        if (!$fault) {
            $update_new = 'update/humo-gen_update.zip';
            if (!move_uploaded_file($_FILES['update_file']['tmp_name'], $update_new)) {
                echo __('Upload failed, check folder rights');
            }
        }
    }
    ?>
    <form method="POST" action="index.php?page=install_update" style="display : inline;" enctype="multipart/form-data" name="formx" id="formx">
        <?= __('3. Upload humo-gen_update.zip:'); ?> <input type="file" name="update_file">
        <input type="submit" name="submit" title="submit" value="<?= __('Upload'); ?>" class="btn btn-secondary btn-sm">
    </form><br>
    &nbsp;&nbsp;&nbsp;&nbsp;<?= __('OR: manually upload the file to folder: admin/update/'); ?><br>

    <!-- Start update -->
    <?= __('4. Then click:'); ?>
    <a href="index.php?page=install_update&auto=1&step=1&update_check=1<?= isset($_GET['re_install']) ? '&re_install=1' : ''; ?>&re_install=1">
        <?php printf(__('start update of %s.'), 'HuMo-genealogy'); ?>
    </a><br>

    <!-- Show version -->
    <h2><?php printf(__('%s version history'), 'HuMo-genealogy'); ?></h2>
    <?php printf(__('%s version'), 'HuMo-genealogy'); ?> <?= $humo_option["version"]; ?>

    <h2><?php printf(__('Enable/ disable %s update check.'), 'HuMo-genealogy'); ?></h2>

    <form method="post" action="index.php?page=install_update&update_check=1" style="display : inline">
        <input type="hidden" name="enable_update_check_change" value="1">
        <input type="checkbox" name="enable_update_check" <?= $humo_option['update_last_check'] != 'DISABLED' ? 'checked' : ''; ?> onChange="this.form.submit();">
        <?php printf(__('Check regularly for %s updates.'), 'HuMo-genealogy'); ?><br>
    </form>

    <p><iframe height=" 300" width="80%" src="https://humo-gen.com/genforum/viewforum.php?f=19"></iframe></p>
<?php
}
