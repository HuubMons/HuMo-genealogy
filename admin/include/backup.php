<?php
@set_time_limit(3000);
@ini_set('memory_limit','-1');
error_reporting(E_ALL);
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('HuMo-gen backup').'</h1>';

// CREATE BACKUP FILE 
//echo '<table style="width:800px; margin-left:auto; margin-right:auto;" class="humo" border="1">';
echo '<table class="humo standard" style="width:800px;" border="1">';

//echo '<tr bgcolor="green"><th><font color="white">'.__('Create backup file').'</font></th></tr>';
echo '<tr class="table_header"><th>'.__('Create backup file').'</th></tr>';

echo '<tr><td>';
if(isset($_POST['create_backup'])) {
	backup_tables();
}
else {
	echo __('The last backup file will be saved to the admin folder. You can restore from this file with "Option 1" below.<br>
You will also be offered a download button and we suggest downloading backup files frequently in case the data on your server (including the backup file) might get deleted or corrupted. You can restore from downloaded files with "Option 2" below.').'<br>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'?page=backup" method="post">';
	echo '&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Create backup file').'" name="create_backup">';
	echo '</form>';
}
echo '</td></tr>';
//echo '<tr bgcolor="green"><th><font color="white">'.__('Restore database from backup file').'</font></th></tr>';
echo '<tr class="table_header"><th>'.__('Restore database from backup file').'</th></tr>';
echo '<tr><td>';

echo __('Here you can restore your entire database from the last backup made with HuMo-gen Backup (if available) or from an .sql or .sql.zip backup file on your computer.').'<br><br>';

echo '<table style="width:750px;margin-left:auto;margin-right:auto"><tr><th style="text-align:left">'.__('Option 1: Restore from last backup created with HuMo-gen Backup').'</th></tr><tr><td style="height:40px">';
echo '<form name="uploadform" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?page=backup" method="post">';

// RESTORE FROM HUMOGEN BACKUP
if(isset($_POST['restore_server'])) {
	// restore from backup on server made by humogen backup
	echo '<span style="color:red">'.__('Starting to restore database. This may take some time. Please wait...').'</span><br>';
	if(is_file('humo_backup.sql.zip')) {
		restore_tables('humo_backup.sql.zip');
	}
	else {
		echo __('No humo_backup file found on server.').'<br>';
	}
}

elseif(is_file('humo_backup.sql.zip')) { 
	echo '<input type="submit" style="font-size:14px" name="restore_server" value="'.__('Restore database').'"> ';
	echo __(' from backup created on ').date("d-M-Y, H:i:s",filemtime('humo_backup.sql.zip'));
}
else { echo "<b>&nbsp;&nbsp;&nbsp;".__('No backup file found!').'</b>'; }
echo '</form>';
echo '</td></tr></table><br>';

// RESTORE FROM FILE ON COMPUTER
echo '<table style="width:750px;margin-left:auto;margin-right:auto"><tr><th style="text-align:left">'.__('Option 2: Restore from backup file on your computer').'</th></tr><tr><td>'; 
echo '<form name="uploadform2" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?page=backup" method="post">'; 

if(isset($_POST['restore']) AND isset($_POST['select_bkfile']) AND $_POST['select_bkfile'] != "none") {
	// restore from uploaded .sql.zip or .sql file
	echo '<span style="color:red">'.__('Starting to restore database from backup file:').' '.$_POST['select_bkfile'].'<br>';
	echo __('This may take some time. Please wait...').'</span><br>';
	if(is_file("./backup_tmp/".$_POST['select_bkfile'])) {
		restore_tables('./backup_tmp/'.$_POST['select_bkfile']);
		if(is_file("./backup_tmp/".$_POST['select_bkfile'])) {  
			// restore_tables should have deleted the file by now, but we want to make sure we clean up...
			unlink('./backup_tmp/'.$_POST['select_bkfile']); 
		}

	}
}

else {
	if(isset($_POST['upload_the_file'])) {
		if(substr($_FILES['upload_file']['name'],-4)==".sql" OR substr($_FILES['upload_file']['name'],-8)==".sql.zip") {
			if (move_uploaded_file($_FILES['upload_file']['tmp_name'], './backup_tmp/'.$_FILES['upload_file']['name'])) {
				//file was successfully uploaded...
			}
			else {
				echo '<span style="color:red;font-weight:bold">'.__('Upload has failed</span> (you may wish to try again or choose to place the file in the admin/backup_tmp folder yourself with an ftp program or the control panel of your webhost)').'<br>';
			}
		}
		else {
			echo '<span style="color:red;font-weight:bold">'.__('Invalid backup file: has to be file with extension ".sql" or ".sql.zip"').'</span><br>';
		}
	} 
	echo "1.&nbsp;<input type='file' name='upload_file'>";
	echo "<input type='submit' name='upload_the_file' value='".__('Upload')."'>&nbsp;&nbsp;(".__('File will be deleted after successful restore').")<br>";
	echo '2.&nbsp;<select size="1" name="select_bkfile">';
	$dh  = opendir('./backup_tmp');
	$foundbk = 0;
	while (false !== ($filename = readdir($dh))) {
		if (substr($filename, -4) == ".sql" OR substr($filename, -8) == ".sql.zip"){
			echo '<option value="'.$filename.'">'.$filename.'</option>';
			$foundbk = 1;
		}
	}
	if($foundbk==0) {
		echo '<option value="none">'.__('No backup files found in admin/backup_tmp...').'</option>';
	}
	echo '</select><br>';
	if($foundbk==0) {
		echo "3.&nbsp;<input type='button' value='".__('Restore database')."'><br><br>"; // Dummy (to show the process) the real button will if a backup file is found in admin/backup_tmp!
	}
	else {
		echo "3.&nbsp;<input type='submit' style='font-size:14px' name='restore' value='".__('Restore database')."' ><br>";
	}
	echo '<b><u>'.__('IMPORTANT').':</u></b><ul>';
	echo __('<li>Only use files with .sql.zip or .sql extension. (Files you downloaded with HuMo-gen Backup automatically have a .sql.zip extension).</li>
<li>If you want to restore from a .sql file you created with any other program, we suggest you zip it first and rename it with a .sql.zip extension since zipping reduces the file size drastically!</li>
<li>If upload fails, you can place the backup file yourself in the admin/backup_tmp folder by other means, such as an ftp program or your web host\'s control panel.</li></ul>');
}  

echo '</form>';
echo '</td></tr></table><br>';  

echo '</td></tr>';
echo '</table>';


// BACKUP FUNCTION 
function backup_tables()
{ 
	global $dbh;
	echo '<div id="red_text" style="color:red">'.__('Creating backup file. This may take some time. Please wait...').'</div>';
ob_start();
	$tables = array();
	$result = $dbh->query('SHOW TABLES');
	while($row = $result->fetch(PDO::FETCH_NUM)){
		$tables[] = $row[0];
	}

	//cycle through
	//$return = "";
	$name = 'humo_backup.sql';
	$handle = fopen($name,'w+');

	foreach($tables as $table)
	{
		$return = "";
		$result = $dbh->query('SELECT * FROM '.$table);
		$num_fields = $result->columnCount();

		$row_result = $dbh->query('SHOW CREATE TABLE '.$table);
		$row2 = $row_result->fetch(PDO::FETCH_NUM);
		$return.= "\n\n".$row2[1].";\n\n";
		fwrite($handle,$return);
		unset($return); 
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = $result->fetch(PDO::FETCH_NUM))
			{
				$return = "";
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = str_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
				fwrite($handle,$return);
				unset($return); 
			}
		}
		$return = "";
		$return.="\n\n\n";
		fwrite($handle,$return);
		unset($return);
	}

	//fwrite($handle,$return);
	fclose($handle);
	$zip = new ZipArchive;
	if ($zip->open($name.'.zip',ZIPARCHIVE::CREATE) === TRUE) {
		$zip->addFile($name);
		$zip->close();
		unlink($name);
		$name = $name.'.zip'; // last backup file is always stored in /admin as: humo_backup.sql.zip
	}
	echo '<div>'.__('A backup file was saved to the server. We strongly suggest you download a copy to your computer in case you might need it later.').'</div>';

	//create download button(forced download) 
	//the downloadable file will be given an extended name including date and time of creation 
	//so the user can afterwards easily chose the right file
	$bk_file = fopen("downloadbk.php","w+");
	$downloadname = "humo_backup-".date('Ymd-His').".sql.zip";
	fwrite($bk_file,"<?php\nheader('Content-type: application/octet-stream');\nheader('Content-Disposition: attachment; filename=\"".$downloadname."\"');\nreadfile('".$name."');\n?>");
	fclose($bk_file);
	echo '<div><form style="display:inline">';

echo '<script type="text/javascript">';
echo ' document.getElementById("red_text").innerHTML = ""; ';
echo '</script>';

	echo '<input type="button" value="'.__('Download backup file').'" onClick="window.location.href=\'downloadbk.php\'">&nbsp;&nbsp;('.$downloadname.')';
	echo '</form><div>';

ob_flush();
}

// RESTORE FUNCTION
function restore_tables($filename) {
	global $dbh;
	$original_name = $filename;
	// Temporary variable, used to store current query
	$templine = '';
	$zip_success=1;
	// unzip (if zipped)
	$tmp_path = "backup_tmp/";
	if(substr($filename,-8)== ".sql.zip") {
		$zip = new ZipArchive;
		if ($zip->open($filename) === TRUE) {
			$content = $zip->statIndex(0); // content of first (and only) entry in the zip file
			$filename = $tmp_path.$content['name']; // name of the unzipped file
				$zip->extractTo('./'.$tmp_path);
				$zip->close();
		}
		else {
			$zip_success=0;
		}
	}
 
	// Read in entire file
	if($zip_success==1 AND is_file($filename) AND substr($filename,-4)==".sql") { 
		// wipe contents of database (we don't do this until we know we've got a proper backup file to work with...
		$result = $dbh->query("show tables"); // run the query and assign the result to $result
		while($table = $result->fetch()) { // go through each row that was returned in $result
			$dbh->query("DROP TABLE ".$table[0]);
		}
		//$lines = file($filename);
		// Loop through each line

		// *** Show processed lines ***
		$line_nr=0;
		echo '<div id="information" style="display: inline;"></div> '.__('Processed lines...').' ';

		// *** Batch processing ***
		$commit_data=0; $dbh->beginTransaction();

		//foreach ($lines as $line) {
		$handle = fopen($filename, "r");
		while(!feof($handle)){
			$line = fgets($handle);

			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '') { continue; }
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';') {
				// Perform the query
				try {
					$dbh->query($templine);
				} catch (PDOException $e){
					print('Error performing query \'<strong>' . $templine .'\': ' . $e->getMessage() .'<br /><br />');
				}
				// Reset temp variable to empty
				$templine = '';
			}

			// *** Update processed lines ***
			echo '<script language="javascript">';
			$percent=$line_nr;
			echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
			$line_nr++;
			echo '</script>';
			// This is for the buffer achieve the minimum size in order to flush data
			//echo str_repeat(' ',1024*64);
			// Send output to browser immediately
			ob_flush(); 
			flush(); // IE

			// *** Commit data every x lines in database ***
			if ($commit_data>500){
				$dbh->commit(); $dbh->beginTransaction();
				$commit_data=0;
			}
			$commit_data++;
		}
		$dbh->commit();
		fclose($handle);

		if($original_name != 'humo_backup.sql.zip') { 
			// if a file was uploaded to backup_tmp in order to restore, delete it now. 
			// if however the restore was made from the last humogen backup (humo_backup.sql.zip) it should always stay in /admin, until replaced by next backup
			unlink($original_name); 
		}
		if($original_name != $filename) { 
			// the original was a zip file, so we also have to delete the unzipped file
			unlink($filename); 
		}
		echo '<span style="color:red;font-weight:bold">'.__('Database has been restored successfully!').'</span><br>';
	}
	else {
		if($zip_success == 0) { echo "file could not be unzipped<br>"; }
		if(!is_file($filename)) { echo "file ".$filename." does not exist"; }
		if(is_file($filename) AND substr($filename,-4)!=".sql" ) { echo "This is not a valid back up file (no .sql extension)"; }
	}
}
?>