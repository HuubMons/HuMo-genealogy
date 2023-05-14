<?php
// *** Original script made by Yossi ***
// *** feb. 2023: Rebuild this script by Huub. Multiple backups will be stored on server. ***

@set_time_limit(3000);
@ini_set('memory_limit', '-1');
error_reporting(E_ALL);
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
	exit;
}
const BACKUP_DIR = __DIR__ . '/../../__storage/backup_files/';
// *** Move and remove files from previous backup procedure ***
if (file_exists(BACKUP_DIR . 'humo_backup.sql.zip')) {
	$new_file_name = BACKUP_DIR . date("Y_m_d_H_i", filemtime('humo_backup.sql.zip')) . '_humo-genealogy_backup.sql';
	rename('humo_backup.sql.zip', $new_file_name);

	if (file_exists('downloadbk.php')) {
		unlink('downloadbk.php');
	}
}

echo '<h1 class="center">';
printf(__('%s backup'), 'HuMo-genealogy');
echo '</h1>';

// *** Upload backup file ***
if (isset($_POST['upload_the_file'])) {
	if (substr($_FILES['upload_file']['name'], -4) == ".sql" or substr($_FILES['upload_file']['name'], -8) == ".sql.zip") {
		if (move_uploaded_file($_FILES['upload_file']['tmp_name'], BACKUP_DIR . $_FILES['upload_file']['name'])) {
			// file was successfully uploaded...
		} else {
			echo '<span style="color:red;font-weight:bold">' . __('Upload has failed</span> (you may wish to try again or choose to place the file in the admin/backup_files folder yourself with an ftp program or the control panel of your webhost)') . '<br>';
		}
	} else {
		echo '<span style="color:red;font-weight:bold">' . __('Invalid backup file: has to be file with extension ".sql" or ".sql.zip"') . '</span><br>';
	}
}

// *** CREATE BACKUP FILE *** 
echo '<table class="humo standard" style="width:800px;" border="1">';

echo '<tr class="table_header"><th>' . __('Create backup file') . '</th></tr>';

echo '<tr><td>';
if (isset($_POST['create_backup'])) {
	backup_tables();
} else {
	printf(__('If you use %s to edit in the family tree, then create multiple backups. Recommended backups:<br>
<b>1) Best option: use PhpMyAdmin. Export all tables from the %s database (TIP: use the zip option for a compressed file).</b><br>
2) Just for sure: export a GEDCOM file. This is not a full family tree backup! But it will contain all basic genealogical data.<br>
3) Use the %s backup page.'), 'HuMo-genealogy', 'HuMo-genealogy', 'HuMo-genealogy');
	echo '<br>';

	echo '<h3>' . __('Create backup file') . '</h3>';

	echo '<form action="index.php?page=backup" method="post">';
	echo '&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="' . __('Create backup file') . '" name="create_backup">';
	echo '</form>';
}

// *** Get list of backup files ***
$dh  = opendir(BACKUP_DIR);
while (false !== ($filename = readdir($dh))) {
	if (substr($filename, -4) == ".sql" or substr($filename, -8) == ".sql.zip") {
		$backup_files[] = $filename;
	}
}
$backup_count = 0;
if (isset($backup_files)) {
	$backup_count = count($backup_files);
	rsort($backup_files); // *** Most recent backup file will be shown first ***
}

// *** Download most recent backup file ***
echo '<h3>' . __('Download backup file') . '</h3>';
echo __('We recommend downloading the most recent backup file in case the data on your server (including the backup file) might get deleted or corrupted.') . '<br>';
if (isset($backup_files[0])) {
	echo '<a href="'. $backup_dir . $backup_files[0] . '">' . $backup_files[0] . '</a><br>';
}

echo '</td></tr>';

// *** Empty line in table ***
echo '<tr><td class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

echo '<tr class="table_header"><th>' . __('Restore database from backup file') . '</th></tr>';
echo '<tr><td>';

printf(__('Here you can restore your entire database from a backup made with %s (if available) or from a .sql or .sql.zip backup file on your computer.'), 'HuMo-genealogy');
echo '<br>';

// *** Upload backup file ***
if (!isset($_POST['restore_server'])) {
	echo '<h3>' . __('Optional: upload a database backup file') . '</h3>';

	echo ' <form name="uploadform2" enctype="multipart/form-data" action="index.php?page=backup" method="post">';
	echo '<input type="file" id="upload_file" name="upload_file">';
	echo " <input type='submit' style='margin-top:4px' name='upload_the_file' value='" . __('Upload') . "'><br>";
	echo '</form>';
}

if ($backup_count > 0) {
	if (isset($_POST['restore_server'])) {
		$restore_file = BACKUP_DIR . $_POST['select_file'];
		if (is_file($restore_file)) {
			// *** restore from backup on server made by HuMo-genealogy backup ***
			echo '<br><span style="color:red">' . __('Starting to restore database. This may take some time. Please wait...') . '</span><br>';
			if (is_file($restore_file)) {
				restore_tables($restore_file);
			}
		}
	}

	echo '<h3>' . __('Restore database from backup file') . '</h3>';

	// *** List of backup files ***
	echo '<form name="uploadform" enctype="multipart/form-data" action="index.php?page=backup" method="post">';
	echo '<select size="1" style="margin-top:4px;"  name="select_file">';
	for ($i = 0; $i < $backup_count; $i++) {
		echo '<option value="' . $backup_files[$i] . '">' . $backup_files[$i];
		if ($i == 0) echo ' * ' . __('Most recent backup!') . ' *';
		echo '</option>';
	}
	echo '</select>';
	echo ' <input type="submit" style="font-size:14px" name="restore_server" value="' . __('Restore database') . '"> ';
	echo '<form>';
} else {
	echo "<b>&nbsp;&nbsp;&nbsp;" . __('No backup file found!') . '</b>';
}

echo '<br><br>';

echo '</td></tr>';
echo '</table>';


// *** BACKUP FUNCTION ***
function backup_tables()
{
	global $dbh, $backup_files;
	echo '<div id="red_text" style="color:red">' . __('Creating backup file. This may take some time. Please wait...') . '</div>';
	//ob_start();
	$tables = array();
	$result = $dbh->query('SHOW TABLES');
	while ($row = $result->fetch(PDO::FETCH_NUM)) {
		$tables[] = $row[0];
	}

	// *** Cycle through ***
	// *** Name of backup file: 2023_02_10_12_55_humo-genealogy_backup.sql.zip ***
	$name = BACKUP_DIR . date('Y_m_d_H_i') . '_humo-genealogy_backup.sql';
	$handle = fopen($name, 'w+');

	// *** 22-10-2022: Needed for PHP 8.0 ***
	$return = "\n\n" . 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";' . "\n\n";
	fwrite($handle, $return);

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
			//$result = $dbh->query('SELECT * FROM `'.$table.'`');
			//$num_fields = $result->columnCount();

			$row_result = $dbh->query('SHOW CREATE TABLE ' . $table);
			//$row_result = $dbh->query('SHOW CREATE TABLE `'.$table.'`');
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
		$name = $name . '.zip'; // last backup file is always stored in /admin as: humo_backup.sql.zip
	}
	echo '<div>' . __('A backup file was saved to the server. We strongly suggest you download a copy to your computer in case you might need it later.') . '</div>';

	//ob_flush();
}

// *** RESTORE FUNCTION ***
function restore_tables($filename)
{
	global $dbh;
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

	// Read in entire file
	if ($zip_success == 1 and is_file($filename) and substr($filename, -4) == ".sql") {
		// wipe contents of database (we don't do this until we know we've got a proper backup file to work with...
		$result = $dbh->query("show tables"); // run the query and assign the result to $result
		while ($table = $result->fetch()) { // go through each row that was returned in $result
			$dbh->query("DROP TABLE " . $table[0]);
		}
		//$lines = file($filename);
		// Loop through each line

		// *** Show processed lines ***
		$line_nr = 0;
		echo '<div id="information" style="display: inline;"></div> ' . __('Processed lines...') . ' ';

		// *** Batch processing ***
		$commit_data = 0;
		$dbh->beginTransaction();

		//foreach ($lines as $line) {
		$handle = fopen($filename, "r");

		while (!feof($handle)) {
			$line = fgets($handle);

			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '') {
				continue;
			}
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';') {
				// Perform the query
				try {
					$dbh->query($templine);
				} catch (PDOException $e) {
					print('Error performing query \'<strong>' . $templine . '\': ' . $e->getMessage() . '<br /><br />');
				}
				// Reset temp variable to empty
				$templine = '';
			}

			// *** Update processed lines ***
			echo '<script language="javascript">';
			$percent = $line_nr;
			echo 'document.getElementById("information").innerHTML="' . $line_nr . '";';
			$line_nr++;
			echo '</script>';
			// This is for the buffer achieve the minimum size in order to flush data
			//echo str_repeat(' ',1024*64);
			// Send output to browser immediately
			//ob_flush();
			//flush(); // IE

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
		if ($dbh->inTransaction() and $commit_data > 1) $dbh->commit();
		fclose($handle);

		//if($original_name != 'humo_backup.sql.zip') {
		//	// if a file was uploaded to backup_tmp in order to restore, delete it now.
		//	// if however the restore was made from the last humogen backup (humo_backup.sql.zip) it should always stay in /admin, until replaced by next backup
		//	unlink($original_name);
		//}

		// *** The original was a zip file, so we delete the unzipped file ***
		if ($original_name != $filename) {
			unlink($filename);
		}
		echo '<span style="color:red;font-weight:bold">' . __('Database has been restored successfully!') . '</span><br>';
	} else {
		if ($zip_success == 0) {
			echo "file could not be unzipped<br>";
		}
		if (!is_file($filename)) {
			echo "file " . $filename . " does not exist";
		}
		if (is_file($filename) and substr($filename, -4) != ".sql") {
			echo "This is not a valid back up file (no .sql extension)";
		}
	}
}
