<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

@set_time_limit(300);
global $selected_language;

if(CMS_SPECIFIC=="Joomla") {
	$phpself = "index.php?option=com_humo-gen&amp;task=admin&amp;page=groups";
}
else {
	$phpself = $_SERVER['PHP_SELF'];
}
echo '<h1 align=center>'.__('HuMo-gen Update').'</h1>';

// *** Installation of beta version ***
if (isset($_GET['install_beta'])){
	$update['up_to_date']='no';
	$update['version_auto_download']=$update['beta_version_auto_download'];
}

// *** Re-install some or all files ***
if (isset($_GET['re_install'])){
	$update['up_to_date']='no';
}

if (isset($update['up_to_date']) AND $update['up_to_date']=='yes'){
	// *** Show HuMo-gen version number ***
	echo '<h2>HuMo-gen</h2>';
	echo __('Version:').' ';
	if (isset($humo_option["version"])){
		echo $humo_option["version"].'.';
	}
	else{
		echo __('no version number available...');
	}
	echo ' '.__('HuMo-gen is up-to-date!');
	echo ' <a href="'.$path_tmp.'page=install_update&re_install=1&auto=1&update_check=1">'.__('If needed: re-install all or some of the HuMo-gen files').'</a>';
	echo '<br><br>';

	// *** Check for HuMo-gen beta version ***
	echo '<h2>HuMo-gen beta version</h2>';
	echo __('Sometimes there is a beta version available.');
	if (strtotime ($update['beta_version_date'])-strtotime($humo_option["version_date"])>0){
		echo ' <a href="'.$path_tmp.'page=install_update&install_beta=1&auto=1&update_check=1">'.__('HuMo-gen beta version available').' ('.$update['beta_version'].')!</a>';
	}
	else{
		echo '  '.__('No beta version available.');
	}
	echo '<br><br>';

	// *** Check for HuMo-gen beta version SAME CODE AS CODE BELOW... ***
	$check=' checked'; if ($humo_option['update_last_check']=='DISABLED') $check='';
	echo '<h2>'.__('Enable/ disable HuMo-gen update check.').'</h2>';
	echo '<form method="post" action="index.php?page=install_update&update_check=1" style="display : inline">';
	echo '<input type="checkbox" name="enable_update_check"'.$check.' onChange="this.form.submit();"> '.__('Check regularly for HuMo-gen updates.').'<br>';
	//print '<input type="Submit" name="enable_update_check_change" value="'.__('Change').'">';
	echo '<input type="hidden" name="enable_update_check_change" value="1">';
	echo '</form>';

	//echo __('Sometimes there is a beta version available.');
	//if (strtotime ($update['beta_version_date'])-strtotime($humo_option["version_date"])>0){
	//	echo ' <a href="'.$path_tmp.'page=install_update&install_beta=1&auto=1&update_check=1">'.__('HuMo-gen beta version available').' ('.$update['beta_version'].')!</a>';
	//}
	//else{
	//	echo '  '.__('No beta version available.');
	//}

	echo '<br><br>';

	// *** Check for HuMo-gen extensions ***
	//echo '<h2>HuMo-gen extensions</h2>';
	//echo 'Under construction...';
}

elseif (isset($update['up_to_date']) AND $update['up_to_date']=='no'){

	if (isset($_GET['auto'])){
		echo '<h2>'.__('Automatic update').'</h2>';

		echo __('Every step can take some time, please be patient and wait till the step is completed!').'<br><br>';

		echo __('Step 1) Download and unzip new HuMo-gen version').'</a><br>';

		if (!isset($_GET['step'])){
			echo '<a href="'.$path_tmp.'page=install_update&auto=1&step=1&update_check=1';
			if (isset($_GET['install_beta'])){ echo '&install_beta=1'; }
			if (isset($_GET['re_install'])){ echo '&re_install=1'; }
			echo '">'.__('Download and unzip new HuMo-gen version').'</a><br>';

			echo '<h2>'.__('HuMo-gen version history').'</h2>';
			echo '<p><iframe height="300" width="80%" src="http://www.humo-gen.com/genforum/viewforum.php?f=19"></iframe>';
		}

		// *** STEP 1: Download humo-gen.zip and unzip to update folder ***
		if (isset($_GET['step']) AND $_GET['step']=='1'){
			$download=false;

			// *** Check update folder permissions ***
			if (!is_writable('update')) {
				echo '<b>ERROR: Folder admin/update is NOT WRITABLE. Please change permissions.</b><br>';
			}

			// *** Copy HuMo-gen update to server using curl ***
			if(function_exists('curl_exec')){
				$source=$update['version_auto_download'];
				$destination='update/humo-gen_update.zip';
				$resource = curl_init();
				curl_setopt($resource, CURLOPT_URL, $source);
				curl_setopt($resource, CURLOPT_HEADER, false);
				curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 30);
				$content = curl_exec($resource);
				curl_close($resource);
				if($content != ''){
					$fp = fopen($destination, 'w');
					$fw = fwrite($fp, $content);
					fclose($fp);
					if($fw != false){
						$download=true;
					}
				}
			}
			// *** copy HuMo-gen to server using copy ***
			else{
				if (copy($update['version_auto_download'], 'update/humo-gen_update.zip')) {
					$download=true;
				}
			}

			if ($download==false){
				echo 'ERROR: automatic download failed...<br>';
			}
			else{
				echo __('Automatic download successfull!').'<br>';
				// *** Unzip downloaded file ***
				$zip = new ZipArchive;
				if ($zip->open("update/humo-gen_update.zip") === TRUE) { 
					$zip->extractTo('update/humo-gen_update');
					$zip->close();
					echo __('File successfully unzipped!').'<br>';

					echo '<br>'.__('Step 2)').' <a href="'.$path_tmp.'page=install_update&auto=1&step=2&update_check=1';
					if (isset($_GET['install_beta'])){ echo '&install_beta=1'; }
					if (isset($_GET['re_install'])){ echo '&re_install=1'; }
					echo '">'.__('Check files').'</a><br>';
				}
				else {  
					echo 'ERROR: unzip failed!<br>';
				}
			}
		}

		// *** STEP 1: Download humo-gen.zip and do an unzip to humo-gen folder ***
		if (isset($_GET['step']) AND ($_GET['step']=='2' OR $_GET['step']=='3')){
			echo '<br>'.__('Step 2) Compare existing and update files (no installation yet)...').'<br>';

			if ($_GET['step']=='3'){
				echo '<br>'.__('Step 3) Installation of new files...').'<br>';
			}

			function listFolderFiles($dir,$exclude,$file_array){
				global $existing_dir_files, $existing_dir, $existing_files;
				global $update_dir_files, $update_dir, $update_files;
				$ffs = scandir($dir);
				foreach($ffs as $ff){
					if(is_array($exclude) and !in_array($ff,$exclude)){ 
						if($ff != '.' && $ff != '..'){
							if ($file_array=='existing_files'){
								$existing_dir[]=$dir;
								$existing_files[]=$ff;
								$existing_dir_files[]=substr($dir.'/'.$ff,3);
							}
							else{
								$update_dir[]=$dir;
								$update_files[]=$ff;
								$update_dir_files[]=substr($dir.'/'.$ff,25);
							}
							if(is_dir($dir.'/'.$ff)) listFolderFiles($dir.'/'.$ff,$exclude,$file_array); 
						}
					}
				}
			}

			// *** Find all existing HuMo-gen files, skip humo-gen_update.zip, humo-gen_update folder and ip_files folder. ***
			listFolderFiles('..',array('humo-gen_update.zip','humo-gen_update','ip_files'),'existing_files'); 

			// *** Find all update HuMo-gen files, a__ is just some random text (skip items)... ***
			listFolderFiles('./update/humo-gen_update',array('a__','a__'),'update_files');

			echo '<form method="POST" action="'.$path_tmp.'page=install_update&auto=1&step=3&update_check=1';
			if (isset($_GET['install_beta'])){ echo '&install_beta=1'; }
			if (isset($_GET['re_install'])){ echo '&re_install=1'; }
			echo '" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';

			echo '<div style="border:1px solid black;height:200px;width:700px;overflow:scroll">';

			// *** Compare new files with old files, show list of renewed files ***
			for ($i=0; $i<=count($update_files)-1; $i++){
				if (!is_dir($update_dir[$i].'/'.$update_files[$i])){
					$key = array_search($update_dir_files[$i], $existing_dir_files);

					$exist_sha1=sha1_file($existing_dir[$key].'/'.$existing_files[$key]);
					$update_sha1=sha1_file($update_dir[$i].'/'.$update_files[$i]);
					if ($exist_sha1!=$update_sha1){
						$create_file='../'.substr($update_dir[$i].'/'.$update_files[$i],25);

						// *** Optional installation of file ***
						$check=' checked';
						if ($_GET['step']=='3'){
							$check=''; if (isset($_POST['install_file'.$i])){ $check=' checked'; }
						}
						echo '<input type="Checkbox" name="install_file'.$i.'" value="yes"'.$check.'>';
						echo $create_file;

						// *** Copy update file to existing file ***
						if ($_GET['step']=='3'){
							if (isset($_POST['install_file'.$i])){
								if (!copy($update_dir[$i].'/'.$update_files[$i],$create_file)){
									echo ' <b>'.__('Installation of file failed').'</b>';
								}
								else{
									echo ' '.__('File installed!');
								}
							}
						}
						echo '<br>';
					}
				}
				else{
					// *** Compare directory ***
					$key = array_search($update_dir_files[$i], $existing_dir_files);
					if ($key){
						//echo $update_dir[$i].'/'.$update_files[$i];
						//echo 'OK';
					}
					else{
						$create_dir='../'.substr($update_dir[$i].'/'.$update_files[$i],25);
						echo $create_dir;
						if ($_GET['step']=='3'){
							if (mkdir($create_dir)){
								echo ' '.__('Directory created.');
							}
						}
						echo '<br>';
					}
				}

			}

			// *** Compare new files with old files, show list of old system files ***
			echo '<br>'.__('The following files are no part of the HuMo-gen system files (anymore). If you want, they can be removed.').'<br>';
			//for ($i=0; $i<=count($existing_files)-1; $i++){
			// *** Skip first file (.htaccess) because of comparison problems ***
			for ($i=1; $i<=count($existing_files)-1; $i++){
				if (!is_dir($existing_dir[$i].'/'.$existing_files[$i])){
					$key = array_search($existing_dir_files[$i], $update_dir_files);
					if (!$key){
						$create_file=$existing_dir[$i].'/'.$existing_files[$i];
						// *** Optional removal of file ***
						$check='';
						if ($_GET['step']=='3'){
							$check=''; if (isset($_POST['remove_file'.$i])){ $check=' checked'; }
						}
						echo '<input type="Checkbox" name="remove_file'.$i.'" value="yes"'.$check.'>';
						echo $create_file;

						// *** Copy update file to existing file ***
						if ($_GET['step']=='3'){
							if (isset($_POST['remove_file'.$i])){
								unlink ($create_file);
							}
						}
						echo '<br>';
					}
				}
			}

			echo '</div>';

			if ($_GET['step']=='3' AND DATABASE_HOST){
				echo '<br>'.__('Update new db_login.php file...').'<br>';
				
				$login_file=CMS_ROOTPATH."include/db_login.php";
				if (!is_writable($login_file)) {
					$result_message='<b> *** '.__('The configuration file is not writable! Please change the include/db_login.php file manually.').' ***</b>';
				}
				else{
					// *** Read file ***
					$handle = fopen($login_file, "r");
					while (!feof($handle)) {
						$buffer[] = fgets($handle, 4096);
					}

					// *** Write file ***
					$check_config=false;
					$bestand_config = fopen($login_file,"w");
					for ($i=0; $i<=(count($buffer)-1); $i++) {

						//define("DATABASE_HOST",     "localhost");
						//define("DATABASE_USERNAME", "root");
						//define("DATABASE_PASSWORD", "usbw");
						//define("DATABASE_NAME",     "humo-gen");

						if (substr($buffer[$i],0,21)=='define("DATABASE_HOST'){
							$buffer[$i]='define("DATABASE_HOST",     "'.DATABASE_HOST.'");'."\n";
							$check_config=true;
						}

						if (substr($buffer[$i],0,25)=='define("DATABASE_USERNAME'){
							$buffer[$i]='define("DATABASE_USERNAME", "'.DATABASE_USERNAME.'");'."\n";
							$check_config=true;
						}

						if (substr($buffer[$i],0,25)=='define("DATABASE_PASSWORD'){
							$buffer[$i]='define("DATABASE_PASSWORD", "'.DATABASE_PASSWORD.'");'."\n";
							$check_config=true;
						}

						if (substr($buffer[$i],0,21)=='define("DATABASE_NAME'){
							$buffer[$i]='define("DATABASE_NAME",     "'.DATABASE_NAME.'");'."\n";
							$check_config=true;
						}
				
						fwrite($bestand_config,$buffer[$i]);
					}
					fclose($bestand_config);
					if ($check_config==false){
						$result_message='<b> *** '.__('There is a problem in the db_config file, maybe an old db_config file is used.').' ***</b>';
					}
					else{
						$result_message=__('File is updated.');
					}
					echo $result_message;
				}
				
			}

			if ($_GET['step']=='2'){
				//echo '<br><br>'.__('Step 3)').' <a href="'.$path_tmp.'page=install_update&auto=1&step=3';
				//if (isset($_GET['install_beta'])){ echo '&install_beta=1'; }
				//echo '">'.__('Install files!').'</a><br>';
				echo '<br><input type="Submit" name="submit" value="'.__('Install files!').'">';
			}
			else{
				// *** Update settings ***
				$result = $dbh->query("UPDATE humo_settings
					SET setting_value='2012-01-01'
					WHERE setting_variable='update_last_check'");
				$humo_option['update_last_check']='2012-01-01';

				// *** Remove installation files ***
				unlink("update/humo-gen_update.zip");
				// *** Count down, because files must be removed first before removing directories ***
				for ($i=count($update_files)-1; $i>=0; $i--){
					if (!is_dir($update_dir[$i].'/'.$update_files[$i])){
						unlink ($update_dir[$i].'/'.$update_files[$i]);
					}
					else{
						rmdir ($update_dir[$i].'/'.$update_files[$i]);
					}
				}
				rmdir("update/humo-gen_update");

				echo '<br><br>'.__('Update completed and installation files removed!').'<br><br>';
			}
		}

	}
	else{

	if (isset($humo_option["version"])){
		echo __('Current HuMo-gen version:').' '.$humo_option["version"].'.<br>';
		echo __('Available HuMo-gen version:').' '.$update['version'].'.<br><br>';
	}

		echo __('There are 2 update methods: automatic and manually.');

		echo '<h2>'.__('1) Automatic update of HuMo-gen').'</h2>';
		echo __('a) [Optional, but highly recommended] Do a database backup first').': <a href="'.$path_tmp.'page=backup">'.__('backup page').'.</a><br>';
		echo __('b)').' <a href="'.$path_tmp.'page=install_update&auto=1&update_check=1">'.__('Start automatic update').'.</a><br>';

		echo '<h2>'.__('2) Manual update of HuMo-gen').'</h2>';
		echo __('a) [Optional, but highly recommended] Do a database backup first').': <a href="'.$path_tmp.'page=backup">'.__('backup page').'.</a><br>';

		echo __('b) Download the new version: ').' <a href="'.$update['version_download'].'" target="_blank">'.__('HuMo-gen version').' '.$update['version'].'</a><br>';

		echo __('c) Unzip the file and replace the old HuMo-gen files by the new HuMo-gen files.').'<br>';
		echo __('Full installation and update instructions can be found at:').' <a href="http://www.humo-gen.com/genwiki" target="_blank">HuMo-gen Wiki</a>';
	}

}

else{
	echo __('Online version check unavailable.');

	// *** Check for HuMo-gen beta version SAME CODE AS CODE ABOVE ***
	$check=' checked'; if ($humo_option['update_last_check']=='DISABLED') $check='';
	echo '<h2>'.__('Enable/ disable HuMo-gen update check.').'</h2>';
	echo '<form method="post" action="index.php?page=install_update&update_check=1" style="display : inline">';
	echo '<input type="checkbox" name="enable_update_check"'.$check.' onChange="this.form.submit();"> '.__('Check regularly for HuMo-gen updates.').'<br>';
	//print '<input type="Submit" name="enable_update_check_change" value="'.__('Change').'">';
	print '<input type="hidden" name="enable_update_check_change" value="1">';
	echo '</form>';

}
?>