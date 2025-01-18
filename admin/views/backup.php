<?php
// *** Original script made by Yossi ***
// *** feb. 2023: Rebuild this script by Huub. Multiple backups will be stored on server. ***
// *** Jan. 2025: added tab's ***

//@ini_set('memory_limit', '-1');
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Get list of backup files ***
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
?>

<h1 class="center"><?php printf(__('%s backup'), 'HuMo-genealogy'); ?></h1>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($backup['menu_tab'] == 'database_backup') echo 'active'; ?>" href="index.php?page=backup"><?= __('Database backup'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($backup['menu_tab'] == 'create_backup') echo 'active'; ?>" href="index.php?page=backup&amp;menu_tab=create_backup"><?= __('Create backup file'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($backup['menu_tab'] == 'restore_backup') echo 'active'; ?>" href="index.php?page=backup&amp;menu_tab=restore_backup"><?= __('Restore database'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php if ($backup['menu_tab'] == 'database_backup') { ?>
        <div class="p-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
            <?php printf(__('If you use %s to edit in the family tree, then create multiple backups. Recommended backups:<br>
<b>1) Best option: use PhpMyAdmin. Export all tables from the %s database (TIP: use the zip option for a compressed file).</b><br>
2) Just for sure: export a GEDCOM file. This is not a full family tree backup! But it will contain all basic genealogical data.<br>
3) Use the %s backup page.'), 'HuMo-genealogy', 'HuMo-genealogy', 'HuMo-genealogy'); ?>
        </div>
    <?php } ?>

    <?php if ($backup['menu_tab'] == 'create_backup') { ?>
        <h2><?= __('Create backup file'); ?></h2>
        <table class="table">
            <tr>
                <td>
                    <?php
                    if (isset($_POST['create_backup'])) {
                        backup_tables($dbh);

                        // TODO refactor, this part is used 2 times in this script.
                        // *** Get list of backup files ***
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
                    } else {
                    ?>
                        <form action="index.php?page=backup&amp;menu_tab=create_backup" method="post">
                            &nbsp;&nbsp;<input type="submit" value="<?= __('Create backup file'); ?>" name="create_backup" class="btn btn-sm btn-success">
                        </form>
                    <?php } ?>

                    <?php if ($backup_count > 0) { ?>
                        <div class="my-3 p-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
                            <!-- Download most recent backup file -->
                            <h3><?= __('Download backup file'); ?></h3>
                            <?= __('We recommend downloading the most recent backup file in case the data on your server (including the backup file) might get deleted or corrupted.'); ?><br>
                            <?php if (isset($backup_files[0])) { ?>
                                <a href="backup_files/<?= $backup_files[0]; ?>"><?= $backup_files[0]; ?></a><br>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        </table>
    <?php } ?>

    <?php if ($backup['menu_tab'] == 'restore_backup') { ?>
        <h2><?= __('Restore database from backup file'); ?></h2>

        <?php if ($backup['upload_status'] == 'upload failed') { ?>
            <div class="alert alert-danger" role="alert">
                <?= __('Upload has failed. You may wish to try again or choose to place the file in the admin/backup_files folder yourself with an ftp program or the control panel of your webhost'); ?>
            </div>
        <?php } ?>

        <?php if ($backup['upload_status'] == 'wrong extension') { ?>
            <div class="alert alert-danger" role="alert">
                <?= __('Invalid backup file: has to be file with extension ".sql" or ".sql.zip"'); ?>
            </div>
        <?php } ?>

        <table class="table">
            <tr>
                <td>
                    <?php
                    printf(__('Here you can restore your entire database from a backup made with %s (if available) or from a .sql or .sql.zip backup file on your computer.'), 'HuMo-genealogy');
                    echo '<br>';

                    // *** Upload backup file ***
                    if (!isset($_POST['restore_server'])) {
                    ?>
                        <div class="my-3 p-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
                            <h3><?= __('Optional: upload a database backup file'); ?></h3>

                            <form name="uploadform2" enctype="multipart/form-data" action="index.php?page=backup&amp;menu_tab=restore_backup" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="file" id="upload_file" name="upload_file" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="submit" style="margin-top:4px" name="upload_the_file" value="<?= __('Upload'); ?>" class="btn btn-sm btn-secondary"><br>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php
                    }

                    if ($backup_count > 0) {
                        if (isset($_POST['restore_server'])) {
                            $restore_file = 'backup_files/' . $_POST['select_file'];
                            if (is_file($restore_file)) {
                                // *** restore from backup on server made by HuMo-genealogy backup ***
                        ?>
                                <br><span style="color:red"><?= __('Starting to restore database. This may take some time. Please wait...'); ?></span><br>
                        <?php
                                restore_tables($restore_file, $dbh);
                            }
                        }

                        ?>
                        <h3><?= __('Restore database from backup file'); ?></h3>

                        <!-- List of backup files -->
                        <form name="uploadform" enctype="multipart/form-data" action="index.php?page=backup&amp;menu_tab=restore_backup" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <select size="1" style="margin-top:4px;" name="select_file" class="form-select form-select-sm">
                                        <?php for ($i = 0; $i < $backup_count; $i++) { ?>
                                            <option value="<?= $backup_files[$i]; ?>"><?= $backup_files[$i]; ?>
                                                <?= $i == 0 ? ' * ' . __('Most recent backup!') . ' *' : ''; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="submit" style="font-size:14px" name="restore_server" value="<?= __('Restore database'); ?>" class="btn btn-sm btn-secondary">
                                </div>
                            </div>
                        </form>
                    <?php } else { ?>
                        <b>&nbsp;&nbsp;&nbsp;<?= __('No backup file found!'); ?></b>
                    <?php } ?>
                    <br><br>
                </td>
            </tr>
        </table>
    <?php } ?>
</div>

<?php
// *** Backup function ***
function backup_tables($dbh)
{
    global $backup_files;

    ob_start();

    $tables = array();
    $result = $dbh->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    // *** Count rows in all tables ***
    $total_rows = 0;
    foreach ($tables as $table) {
        // *** Skip tables names that contains a space in it ***
        if (strpos($table, ' ')) {
            //
        } else {
            $result = $dbh->query('SELECT COUNT(*) as counter FROM ' . $table);
            $resultDb = $result->fetch(PDO::FETCH_OBJ);
            $count_text = $resultDb->counter;
            if (isset($count_text) and is_numeric($count_text)) {
                $total_rows += $count_text;
            }
        }
    }
    $devider = floor($total_rows / 100);
?>
    <div id="red_text" style="color:red"><?= __('Creating backup file. This may take some time. Please wait...'); ?></div>

    <div class="progress" style="height:20px">
        <div class="progress-bar"></div>
    </div>

    <?php
    // This is for the buffer achieve the minimum size in order to flush data
    echo str_repeat(' ', 1024 * 64);
    // Send output to browser immediately
    ob_flush();
    flush();

    // *** Cycle through ***
    // *** Name of backup file: 2023_02_10_12_55_humo-genealogy_backup.sql.zip ***
    $name = 'backup_files/' . date('Y_m_d_H_i') . '_humo-genealogy_backup.sql';
    $handle = fopen($name, 'w+');

    // *** 22-10-2022: Needed for PHP 8.0 ***
    $return = "\n\n" . 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";' . "\n\n";
    fwrite($handle, $return);

    $count_rows = 0;
    $perc = 0;
    foreach ($tables as $table) {
        // *** Skip tables names that contains a space in it ***
        if (strpos($table, ' ')) {
            // *** Show progress ***
            echo '&gt; <b>' . __('Skipped backup of table:') . '</b> ' . $table . '<br>';
        } else {
            // *** Show progress ***
            echo '&gt; ' . $table . '<br>';

            // *** The next line could cost a lot of memory. ***
            // Maybe change into:
            // - Get names of columns:
            //	$sql = "SHOW COLUMNS FROM your-table";
            //	$result = mysqli_query($conn,$sql);
            //	while($row = mysqli_fetch_array($result)){
            //		echo $row['Field']."<br>";
            //	}
            // - Only get first item, something like: $result = $dbh->query('SELECT [pers_id/fam_id etc] FROM '.$table);
            // - In loop get all items.
            $result = $dbh->query('SELECT * FROM ' . $table);

            $row_result = $dbh->query('SHOW CREATE TABLE ' . $table);
            $row2 = $row_result->fetch(PDO::FETCH_NUM);
            $return = "\n\n" . $row2[1] . ";\n\n";
            fwrite($handle, $return);
            //unset($return);
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $return = 'INSERT INTO ' . $table . ' VALUES(';
                $num_fields = count($row);
                for ($j = 0; $j < $num_fields; $j++) {
                    if ($row[$j]) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    }
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
                fwrite($handle, $return);
                //unset($return); 

                $count_rows++;
                if ($count_rows == $devider) {
                    $perc += 1;
                    $count_rows = 0;

                    // *** Apr. 2024 New bootstrap bar ***
                    echo '<script>
                        var bar = document.querySelector(".progress-bar");
                        bar.style.width = ' . $perc . ' + "%";
                        bar.innerText = ' . $perc . ' + "%";
                    </script>';

                    // This is for the buffer achieve the minimum size in order to flush data
                    echo str_repeat(' ', 1024 * 64);

                    // Send output to browser immediately
                    ob_flush();
                    flush();
                }
            }
            $return = "\n\n\n";
            fwrite($handle, $return);
            //unset($return);
        }
    }

    //fwrite($handle,$return);
    fclose($handle);

    // *** Zip backup file ***
    $zip = new ZipArchive;
    if ($zip->open($name . '.zip', ZIPARCHIVE::CREATE) === TRUE) {
        $zip->addFile($name);
        $zip->close();
        unlink($name);
        $name .= '.zip';
    }
    ?>
    <div><?= __('A backup file was saved to the server. We strongly suggest you download a copy to your computer in case you might need it later.'); ?></div>
    <?php
}

// *** Restore function ***
function restore_tables($filename, $dbh)
{
    $original_name = $filename;
    // Temporary variable, used to store current query
    $templine = '';
    $zip_success = 1;
    // unzip (if zipped)
    //$tmp_path = 'backup_files/';
    $tmp_path = '';
    if (substr($filename, -8) == ".sql.zip") {
        $zip = new ZipArchive;
        if ($zip->open($filename) === TRUE) {
            $content = $zip->statIndex(0); // content of first (and only) entry in the zip file
            $filename = $tmp_path . $content['name']; // name of the unzipped file
            $zip->extractTo('./' . $tmp_path);
            $zip->close();
        } else {
            $zip_success = 0;
        }
    }

    // Read entire file
    if ($zip_success == 1 && is_file($filename) && substr($filename, -4) === ".sql") {
        // wipe contents of database. We don't do this until we know we've got a proper backup file to work with.
        $result = $dbh->query("show tables");
        while ($table = $result->fetch()) {
            $dbh->query("DROP TABLE " . $table[0]);
        }

        // *** Show processed lines ***
        $line_nr = 0;
        echo '<div id="information" style="display: inline;"></div> ' . __('Processed lines...') . ' ';

        // *** Batch processing ***
        $commit_data = 0;
        $dbh->beginTransaction();

        $handle = fopen($filename, "r");
        while (!feof($handle)) {
            $line = fgets($handle);

            // Skip it if it's a comment
            if (substr($line, 0, 2) === '--' || $line == '') {
                continue;
            }
            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) === ';') {
                try {
                    $dbh->query($templine);
                } catch (PDOException $e) {
                    print('Error performing query \'<strong>' . $templine . '\': ' . $e->getMessage() . '<br><br>');
                }
                $templine = '';
            }

            // *** Update processed lines ***
            echo '<script>';
            $percent = $line_nr;
            echo 'document.getElementById("information").innerHTML="' . $line_nr . '";';
            $line_nr++;
            echo '</script>';

            // *** Commit data every x lines in database ***
            if ($commit_data > 500) {
                $commit_data = 0;
                if ($dbh->inTransaction()) {
                    $dbh->commit();
                }
                $dbh->beginTransaction();
            }
            $commit_data++;
        }
        if ($dbh->inTransaction() && $commit_data > 1) {
            $dbh->commit();
        }
        fclose($handle);

        // *** The original was a zip file, so we delete the unzipped file ***
        if ($original_name != $filename) {
            unlink($filename);
        }
    ?>
        <span style="color:red;font-weight:bold"><?= __('Database has been restored successfully!'); ?></span><br>
<?php
    } else {
        // TODO: translate texts.
        if ($zip_success == 0) {
            echo "file could not be unzipped<br>";
        }
        if (!is_file($filename)) {
            echo "file " . $filename . " does not exist";
        }
        if (is_file($filename) && substr($filename, -4) !== ".sql") {
            echo "This is not a valid back up file (no .sql extension)";
        }
    }
}
