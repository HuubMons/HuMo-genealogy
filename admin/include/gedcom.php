<?php
// *** Support for gedcom files for MAC computers ***
@ini_set('auto_detect_line_endings', TRUE);

/**
* This is the gedcom processing file for HuMo-gen.
*
* If you are reading this in your web browser, your server is probably
* not configured correctly to run PHP applications!
*
* See the manual for basic setup instructions
*
* http://www.huubmons.nl/software/
*
* ----------
*
* Copyright (C) 2008-2009 Huub Mons,
* Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
* René Janssen, Yossi Beck
* and others.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//echo '<h1 align=center>'.__('Import Gedcom file').'</h1>';

include_once(CMS_ROOTPATH_ADMIN."include/gedcom_asciihtml.php");
include_once(CMS_ROOTPATH_ADMIN."include/gedcom_anselhtml.php");
include_once(CMS_ROOTPATH_ADMIN."include/gedcom_ansihtml.php");

@set_time_limit(4000);

//*** TEST TIME LIMIT ***
//set_time_limit(10);
//set_time_limit(120);
//set_time_limit(30000000);

if(CMS_SPECIFIC=="Joomla") {
	$phpself = "index.php?option=com_humo-gen&amp;task=admin";
	global $gen_program, $not_processed;
}
else {
	$phpself = 'index.php';
}

// *** Step 1 ***
if (isset($_POST['step1'])){ $step1=$_POST['step1']; }
if (isset($_GET['step1'])){ $step1=$_GET['step1']; }
if (isset($step1)){

	// *** THIS CODE DOESN'T WORK WITH PROVIDER BHOSTED! GIVES A SERVER ERROR. ***
	// *** Only needed for Huub's test server ***
	// *** TO PREVENT GENERATING A HTACCES FILE IN THE WORKVERSION ***
	/*
	if (@!file_exists("../../gedcom-bestanden")){
		// *** Make sure gzip is turned off in .htaccess (for progress bar) ***
		if(file_exists(".htaccess")===true) { // file exists, now check for line
			$content = file_get_contents(".htaccess");
			if(strpos($content,"SetEnv no-gzip dont-vary")===false) { // the line isn't there yet, append it
				file_put_contents(".htaccess","\nSetEnv no-gzip dont-vary", FILE_APPEND);
			}
		}
		else {  // create .htaccess with the relevant line
			file_put_contents(".htaccess","\nSetEnv no-gzip dont-vary");
		}
	}
	*/

	// *** Set parameters ***
	if(CMS_SPECIFIC=="Joomla")
		$gedcom_directory=substr(CMS_ROOTPATH_ADMIN, 0, -1); // take away the / at the end
	else
		$gedcom_directory="gedcom_files";

	// *** Only needed for Huub's test server ***
	if (@file_exists("../../gedcom-bestanden")){
		$gedcom_directory="../../gedcom-bestanden";
	}
	// *** Only needed for Huub's test server (for component in Joomla) ***
	//if (@file_exists("../../../../../../gedcom-bestanden")){
	//	$gedcom_directory="../../../../../../gedcom-bestanden";
	//}

	echo '<b>'.__('STEP 1) Select Gedcom file:').'</b>';

	// *** Upload gedcom file ***
	echo '<p><table class="humo" style="width:100%;">';
	echo '<tr class="table_header"><th colspan="2">'.__('Add and remove gedcom files').'</th></tr>';

	echo '<tr><td>'.__('Add gedcom file').'</td><td>';

	echo __('Here you can upload a gedcom file (for example: gedcom_name.ged or gedcom_name.zip).').'<br>';

	if (isset($_POST['upload'])){
		// *** Only needed for Huub's test server ***
		if (file_exists("../gedcom-bestanden")){ $gedcom_directory="../gedcom-bestanden"; }
		elseif (file_exists("gedcom_files")){ $gedcom_directory="gedcom_files"; }
		else{
			if(CMS_SPECIFIC=="Joomla") {
				$gedcom_directory=substr(CMS_ROOTPATH_ADMIN, 0, -1); // take away the / at the end
			}
			else {
				$gedcom_directory=".";
			}
		}

		// *** Only upload .ged or .zip files ***
		if (strtolower(substr($_FILES['upload_file']['name'],-4))=='.zip' OR strtolower(substr($_FILES['upload_file']['name'],-4))=='.ged'){
			$new_upload = $gedcom_directory.'/' . basename($_FILES['upload_file']['name']);
			// *** Move and check for succesful upload ***
			echo '<p><b>'.$new_upload.'<br>';
			if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $new_upload))
				echo __('File successfully uploaded.').'</b>';
			else
				echo __('Upload has failed.').'</b>';

			// *** If file is zipped, unzip it ***
			//if (substr($new_upload,-4)=='.zip'){
			if (strtolower(substr($new_upload,-4))=='.zip'){
				$zip = new ZipArchive;
				$res = $zip->open($new_upload);
				if ($res === TRUE) {

					// *** Only unzip .ged files ***
					$check_gedcom=true;
					for ($i = 0; $i < $zip->numFiles; $i++) {
						$filename = $zip->getNameIndex($i);
						//if (substr($filename,-4)!='.ged') $check_gedcom=false;
						if (strtolower(substr($filename,-4))!='.ged') $check_gedcom=false;
					}
					if ($check_gedcom){
						//$zip->extractTo('/myzips/extract_path/');
						$zip->extractTo($gedcom_directory);
						$zip->close();
						echo '<br>Succesfully unzipped file!';
					}

				} else {
					echo '<br>Error in unzipping file!';
				}

			}
		}

	}
	else {
		// *** Upload form ***
		echo "<form name='uploadform' enctype='multipart/form-data' action='".$phpself."' method='post'>";
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="file" name="upload_file" >';
			echo '<input type="hidden" name="upload" value="Upload">';
			echo ' <input type="submit" name="step1" value="Upload">';
		echo "</form><br>";

		echo __('ATTENTION: the privileges of the file map may have to be adjusted!').'<br>';
		echo __('Another option is to upload gedcom files manually by using FTP to folder: /humo-gen/admin/gedcom_files/').'<br><br>';

		echo '</tr><td>'.__('Remove gedcom files').'</td><td>';

		// *** Form to remove gedcom files ***
		if (isset($_POST['remove_gedcom_files'])){
			echo __('Are you sure to remove gedcom files?');
			echo ' <form name="remove_gedcomfiles" action="'.$phpself.'" method="post">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="step1" value="step1">';
			echo '<input type="hidden" name="remove_gedcom_files2" value="'.$_POST['remove_gedcom_files'].'">';
			echo ' <input type="Submit" name="remove_confirm" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
			echo '</form>';
		}
		elseif (isset($_POST['remove_gedcom_files2']) AND isset($_POST['remove_confirm'])){
			// *** Remove old gedcom files ***
			$dh  = opendir($gedcom_directory);
			while (false !== ($filename = readdir($dh))) {
				if (strtolower(substr($filename, -3)) == "ged"){
					if ($_POST['remove_gedcom_files2']=='gedcom_files_all'){
						$filenames[]=$gedcom_directory.'/'.$filename;
					}
					elseif ($_POST['remove_gedcom_files2']=='gedcom_files_1_month'){
						if (time() - filemtime($gedcom_directory.'/'.$filename) >= 60 * 60 * 24 * 30) { // 30 days
							$filenames[]=$gedcom_directory.'/'.$filename;
						}
					}
					elseif ($_POST['remove_gedcom_files2']=='gedcom_files_1_year'){
						if (time() - filemtime($gedcom_directory.'/'.$filename) >= 60 * 60 * 24 * 365) { // 365 days
							$filenames[]=$gedcom_directory.'/'.$filename;
						}
					}
				}
			}
			// *** Order gedcom files by alfabet ***
			if (isset($filenames)) usort($filenames,'strnatcasecmp');
			echo '<br>';
			for ($i=0; $i<count($filenames); $i++){
				if (strpos($filenames[$i],'HuMo-gen test gedcomfile.ged')>1)
					echo '<b>'.$filenames[$i].'</b><br>';
				else{
					echo $filenames[$i].' '._('Gedcom file is REMOVED.').'<br>';
					unlink ($filenames[$i]);
				}
			}
		}
		else{
			echo __('If needed remove gedcom files (except test gedcom file):');
			echo ' <form name="remove_gedcomfiles" action="'.$phpself.'" method="post">';
				echo '<select size="1" name="remove_gedcom_files">';
					//	$selected = ''; if($gedfile == $filenames[$i]) $selected = " selected ";
					echo '<option value="gedcom_files_all">'.__('Remove all gedcom files').'</option>';
					echo '<option value="gedcom_files_1_month">';
						printf(__('Remove gedcom files older than %d month(s)'), 1);
					echo '</option>';
					echo '<option value="gedcom_files_1_year">';
						printf(__('Remove gedcom files older than %d year(s)'), 1);
					echo '</option>';
				echo '</select>';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo ' <input type="submit" name="step1" value="'.__('Remove').'">';
			echo '</form><br>';
		}

	}
	echo '</td></tr></table>';

	if (isset($_POST['tree_prefix'])){
		$tree_prefix=$_POST['tree_prefix'];
		$_SESSION['tree_prefix']=$tree_prefix;
	}
	if (isset($_GET['tree_prefix'])){
		$tree_prefix=$_GET['tree_prefix'];
		$_SESSION['tree_prefix']=$tree_prefix;
	}
	$_SESSION['debug_person']=1;

	echo '<form method="post" action="'.$phpself.'" style="display : inline">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';

	$dh  = opendir($gedcom_directory);
	while (false !== ($filename = readdir($dh))) {
		if (strtolower(substr($filename, -3)) == "ged") $filenames[]=$gedcom_directory."/".$filename;
	}
	// *** Order gedcom files by alfabet ***
	if (isset($filenames)) usort($filenames,'strnatcasecmp');

	echo '<p><table class="humo" style="width:100%;">';
	echo '<tr class="table_header"><th colspan="2">'.__('Select gedcom file and settings').'</th></tr>';

		echo '</tr><td><br>'.__('Select gedcom file').'<br><br></td><td>';

		echo '<br><select size="1" name="gedcom_file">';
			$result = $dbh->query("SELECT tree_gedcom FROM humo_trees WHERE tree_prefix='".$_SESSION['tree_prefix']."'");
			$treegedDb = $result->fetch();
			$gedfile = $treegedDb['tree_gedcom'];
			for ($i=0; $i<count($filenames); $i++){
				// *** if this was last gedcom file that was used for this tree - select it ***
				$selected = ''; if($gedfile == $filenames[$i]) $selected = " selected ";
				echo '<option value="'.$filenames[$i].'" '.$selected.'>'.$filenames[$i].'</option>';
			}
		echo '</select><br><br>';

	echo '</tr><td>'.__('Gedcom settings').'</td><td>';

		$check=''; if ($humo_option["gedcom_read_add_source"]=='y'){ $check=' checked'; }
		echo '<input type="checkbox" name="add_source"'.$check.'> '.__('Add a general source connected to all persons in this gedcom file.')."<br>\n";

		$check=''; if ($humo_option["gedcom_read_reassign_gedcomnumbers"]=='y'){ $check=' checked'; }
		echo '<input type="checkbox" name="reassign_gedcomnumbers"'.$check.'> '.__('Reassign new ID numbers for persons, fams etc. (don\'t use IDs from gedcom)')."<br>\n";

		$check=''; if ($humo_option["gedcom_read_order_by_date"]=='y'){ $check=' checked'; }
		echo '<input type="checkbox" name="order_by_date"'.$check.'> '.__('Order children by date (only needed if children are in wrong order)')."<br>\n";

		$check=''; if ($humo_option["gedcom_read_order_by_fams"]=='y'){ $check=' checked'; }
		echo '<input type="checkbox" name="order_by_fams"'.$check.'> '.__('Order families by date (only needed if families are in wrong order)')."<br>\n";

		// *** if a humo_location table exists, refresh the location_status column ***
		$res = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		if ($res->rowCount()) {
			$check=''; if ($humo_option["gedcom_read_process_geo_location"]=='y'){ $check=' checked'; }
			echo "<input type='checkbox' name='process_geo_location'".$check."> ".__('Add new locations to geo-location database (for Google Maps locations). This will slow down reading of gedcom file!')."<br>\n";
		}

		// *** Process full picture path of files ***
		echo '<input type="checkbox" name="check_gedcom_process_pict_path" checked disabled> <select class="fonts" size="1" name="gedcom_process_pict_path" style="width: 550px">';
			$selected=''; if ($humo_option["gedcom_process_pict_path"]=='file_name'){ $selected=' selected'; }
			echo '<option value="file_name"'.$selected.'>'.__('Only process picture file name. For example: picture.jpg [DEFAULT]').'</option>';
			$selected=''; if ($humo_option["gedcom_process_pict_path"]=='full_path'){ $selected=' selected'; }
			echo '<option value="full_path"'.$selected.'>'.__('Process full picture path. For example: picture_path&#92;picture.jpg').'</option>';
		echo '</select><br>';

		// *** Option to add gedcom file to family tree if this family tree isn't empty ***
//use this code?
//$nr_persons=$db_functions->count_persons($tree_id);
//if ($nr_persons>0){
		$result = $dbh->query("SELECT tree_persons FROM humo_trees WHERE tree_prefix ='".$_SESSION['tree_prefix']."' LIMIT 1");
		$resultDb=$result->fetch(PDO::FETCH_OBJ);
		if ($resultDb->tree_persons != 0) {  // don't show if there is nothing in the database yet: this can't be a second gedcom!
			//echo "<br><input type='checkbox' name='add_tree'> ".__('Add this gedcom file to the existing tree')."<br>\n";
			echo '<br><input type="checkbox" onchange="document.getElementById(\'step2\').disabled = !this.checked;" name="add_tree"> '.__('Add this gedcom file to the existing tree')."<br>\n";
		}

		//TEST
		//$nr_persons=$db_functions->count_persons($tree_id);
		//if ($nr_persons>0){
		//	$checked1 = ''; $checked2 = ' checked';
		//	echo '<br><input type="radio" value="no" name="add_tree" onchange="document.getElementById(\'step2\').disabled = this.checked;" '.$checked2.'> ';printf(__('Replace existing family tree with %s persons'), $nr_persons);
		//	echo '<br>';
		//	echo '<input type="radio" value="yes" name="add_tree" onchange="document.getElementById(\'step2\').disabled = !this.checked;" '.$checked1.'> '.__('Add this gedcom file to the existing tree').'<br>';
		//}

		echo '<br></tr><td>'.__('Gedcom process settings').'</td><td>';

		echo '<input type="checkbox" name="check_processed"> '.__('Show non-processed items when processing gedcom (can be a long list!')."<br>\n";
		echo '<input type="checkbox" name="show_gedcomnumbers"> '.__('Show all numbers when processing gedcom (useful when a time-out occurs!)')."<br>\n";
		echo '<input type="checkbox" name="debug_mode"> '.__('Debug mode')."<br>\n";

		echo '<input type="checkbox" name="commit_checkbox" checked disabled> '.__('Batch processing').': <select class="fonts" size="1" name="commit_records" style="width: 200px">';
			echo '<option value="1">'.__('1 record (slow processing, but needs less server-memory)').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='10'){ $selected=' selected'; }
			echo '<option value="10"'.$selected.'>10 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='100'){ $selected=' selected'; }
			echo '<option value="100"'.$selected.'>100 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='500'){ $selected=' selected'; }
			echo '<option value="500"'.$selected.'>500 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='1000'){ $selected=' selected'; }
			echo '<option value="1000"'.$selected.'>1000 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='5000'){ $selected=' selected'; }
			echo '<option value="5000"'.$selected.'>5000 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='10000'){ $selected=' selected'; }
			echo '<option value="10000"'.$selected.'>10000 '.__('records per batch').'</option>';
			$selected=''; if ($humo_option["gedcom_read_commit_records"]=='9000000'){ $selected=' selected'; }
			echo '<option value="9000000"'.$selected.'>'.__('ALL records (fast processing, but needs server-memory)').'</option>';
		echo '</select>';

		// *** Controlled time-out ***
		$time_out=0; if ($humo_option["gedcom_read_time_out"]) $time_out= $humo_option["gedcom_read_time_out"];
		echo '<p>';
		if (isset($_POST['timeout_restart'])){
			if (isset($_SESSION['save_process_time']) and $_SESSION['save_process_time']) $time_out=($_SESSION['save_process_time']-3);
			echo '<b>'.__('Time-out detected! Controlled time-out setting is adjusted. Retry reading of gedcom with new setting.').'</b><br>';
		}
		echo '&nbsp;<input type="text" name="time_out" value="'.$time_out.'" size="2"> ';
		$max_time = ini_get("max_execution_time");
		echo __('seconds. Controlled time-out, the gedcom script will restart and continue.<br>Use this if the server has a time-out setting (set less seconds then server time-out).<br>0 = disable controlled time-out.').' ';
		printf(__('Your server time-out setting is: %s seconds.'), $max_time);
		echo "<br>\n";

	echo '</td></tr></table>';

	// *** Show extra warning if there is an existing family tree ***
	$nr_persons=$db_functions->count_persons($tree_id);
	if ($nr_persons>0){
		echo '<br><input type="checkbox" onchange="document.getElementById(\'step2\').disabled = !this.checked;" />';

		$treetext=show_tree_text($tree_id, $selected_language);
		$treetext2=''; if ($treetext['name']) $treetext2= $treetext['name'];
		//printf(__('Yes, replace existing family tree "" with %s persons!'), $nr_persons);
		printf(__('Yes, replace existing family tree: <b>"%1$s"</b> with %2$s persons!'), $treetext2, $nr_persons);

		echo '<br><input type="Submit" name="step2" id="step2" disabled value="'.__('Step').' 2"><br>';
	}
	else{
		echo '<p><input type="Submit" name="step2" value="'.__('Step').' 2"><br>';
	}

	echo '</form><br>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
}

// *** Step 2 generate tables ***
if (isset($_POST['step2'])){

	if(!isset($_POST['add_tree'])) {
		$_SESSION['add_tree']=false; 
		$limit=2500;
		if(CMS_SPECIFIC=="Joomla") {
			$rootpathinclude = CMS_ROOTPATH_ADMIN."include/";
		}
		else {
			$rootpathinclude = '';
		}

		echo '<b>'.__('STEP 2) Remove old family tree:').'</b><br>';

		// *** Time out button ***
		echo '<br><form method="post" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			if (isset($_POST['check_processed']))
				echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
			if (isset($_POST['show_gedcomnumbers']))
				echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
			if (isset($_POST['debug_mode']))
				echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';
			if (isset($_POST['time_out']))
				echo '<input type="hidden" name="time_out" value="'.$_POST['time_out'].'">';
			if(isset($_POST['add_tree']))
				echo '<input type="hidden" name="add_tree" value="1">';
			echo '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';
			echo __('ONLY use in case of a time-out, to continue click:');
			echo ' <input type="Submit" name="step2" value="'.__('Step').' 2">';
		echo '</form><br>';

		/*
		// *** Batch processing ***
		$dbh->beginTransaction();
			// *** Remove unprocessed tags ***
			printf(__('Remove old family tree items from %s table...'), 'humo_persons');
			echo '<br>';
			$sql="DELETE FROM humo_persons WHERE pers_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);
			ob_flush();
			flush(); // IE
		// *** Commit data in database ***
		$dbh->commit();
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_persons');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_persons WHERE pers_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_persons WHERE pers_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_persons";
			@$result=$dbh->query($sql);
		}
		echo '<br>';

		/*
		// *** Batch processing ***
		$dbh->beginTransaction();
			// *** Remove unprocessed tags ***
			printf(__('Remove old family tree items from %s table...'), 'humo_families');
			echo '<br>';
			$sql="DELETE FROM humo_families WHERE fam_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);
			ob_flush();
			flush(); // IE
		// *** Commit data in database ***
		$dbh->commit();
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_families');
		echo ' ';
		$total = $dbh->query("SELECT COUNT(*) FROM humo_families WHERE fam_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_families WHERE fam_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_families WHERE fam_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...').' ';
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_families";
			@$result=$dbh->query($sql);
			}
		echo '<br>';

		// *** Batch processing ***
		/*
		//$dbh->beginTransaction();
			// *** Remove unprocessed tags ***
			printf(__('Remove old family tree items from %s table...'), 'humo_unprocessed_tags');
			echo ' ';
			ob_flush(); flush(); // IE
			$sql="DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_unprocessed_tags";
			@$result=$dbh->query($sql);
		// *** Commit data in database ***
		//$dbh->commit();
		echo '<br>';
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_unprocessed_tags');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_unprocessed_tags WHERE tag_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_unprocessed_tags";
			@$result=$dbh->query($sql);
		}
		echo '<br>';


		// *** Remove admin favourites ***
		printf(__('Remove old family tree items from %s table...'), 'humo_settings');
		ob_flush(); flush(); // IE
		$sql="DELETE FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='".safe_text_db($tree_id)."'";
		@$result=$dbh->query($sql);

		echo ' '.__('Optimize table...');
		ob_flush(); flush(); // IE
		$sql="OPTIMIZE TABLE humo_settings";
		@$result=$dbh->query($sql);
		echo '<br>';

		// *** Remove repositories ***
		printf(__('Remove old family tree items from %s table...'), 'humo_repositories');
		ob_flush(); flush(); // IE
		$sql="DELETE FROM humo_repositories WHERE repo_tree_id='".safe_text_db($tree_id)."'";
		@$result=$dbh->query($sql);

		echo ' '.__('Optimize table...');
		ob_flush(); flush(); // IE
		$sql="OPTIMIZE TABLE humo_repositories";
		@$result=$dbh->query($sql);
		echo '<br>';

		// *** Batch processing ***
		/*
		//$dbh->beginTransaction();
			// *** Remove sources ***
			printf(__('Remove old family tree items from %s table...'), 'humo_sources');
			ob_flush(); flush(); // IE
			$sql="DELETE FROM humo_sources WHERE source_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_sources";
			@$result=$dbh->query($sql);
		// *** Commit data in database ***
		//$dbh->commit();
		echo '<br>';
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_sources');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_sources WHERE source_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_sources WHERE source_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_sources WHERE source_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_sources";
			@$result=$dbh->query($sql);
		}
		echo '<br>';


		// *** Batch processing ***
		//$dbh->beginTransaction();
		/*
			// *** Remove texts ***
			printf(__('Remove old family tree items from %s table...'), 'humo_texts');
			echo '<br>';
			$sql="DELETE FROM humo_texts WHERE text_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);
			ob_flush();
			flush(); // IE
		*/
		// *** Commit data in database ***
		//$dbh->commit();
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_texts');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_texts WHERE text_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_texts WHERE text_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			$sql="OPTIMIZE TABLE humo_texts";
			@$result=$dbh->query($sql);
		}
		echo '<br>';


		// *** Batch processing ***
		//$dbh->beginTransaction();
		/*
			// *** Remove connections ***
			printf(__('Remove old family tree items from %s table...'), 'humo_connections');
			ob_flush(); flush(); // IE
			$sql="DELETE FROM humo_connections WHERE connect_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_connections";
			@$result=$dbh->query($sql);
		// *** Commit data in database ***
		//$dbh->commit();
		echo '<br>';
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_connections');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_connections WHERE connect_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_connections WHERE connect_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_connections WHERE connect_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_connections";
			@$result=$dbh->query($sql);
		}
		echo '<br>';


		// *** Batch processing ***
		//$dbh->beginTransaction();
			// *** Remove addresses ***
			/*
			printf(__('Remove old family tree items from %s table...'), 'humo_addresses');
			echo '<br>';
			$sql="DELETE FROM humo_addresses WHERE address_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);
			ob_flush();
			flush(); // IE
			*/
		// *** Commit data in database ***
		//$dbh->commit();
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_addresses');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_addresses WHERE address_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_addresses WHERE address_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush();
				flush(); // IE
			}
			$sql="DELETE FROM humo_addresses WHERE address_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_address";
			@$result=$dbh->query($sql);
		}
		echo '<br>';

		// *** Batch processing ***
		//$dbh->beginTransaction();
		/*
			// *** Remove events ***
			printf(__('Remove old family tree items from %s table...'), 'humo_events');
			ob_flush(); flush(); // IE
			$sql="DELETE FROM humo_events WHERE event_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_events";
			@$result=$dbh->query($sql);
		// *** Commit data in database ***
		//$dbh->commit();
		echo '<br>';
		*/
		// *** Remove records in chunks because of InnoDb database... ***
		printf(__('Remove old family tree items from %s table...'), 'humo_events');
		echo ' ';
		ob_flush(); flush(); // IE
		$total = $dbh->query("SELECT COUNT(*) FROM humo_events WHERE event_tree_id='".$tree_id."'"); 
		$total = $total->fetch();
		$nr_records=$total[0];
		if ($nr_records>0){
			$loop=$nr_records/$limit;
			for ($i=0; $i<=$loop; $i++){
				$sql="DELETE FROM humo_events WHERE event_tree_id='".safe_text_db($tree_id)."' LIMIT ".$limit;
				@$result=$dbh->query($sql);
				echo '*';
				ob_flush(); flush(); // IE
			}
			$sql="DELETE FROM humo_events WHERE event_tree_id='".safe_text_db($tree_id)."'";
			@$result=$dbh->query($sql);

			echo ' '.__('Optimize table...');
			ob_flush(); flush(); // IE
			$sql="OPTIMIZE TABLE humo_events";
			@$result=$dbh->query($sql);
		}
		echo '<br>';


		if (isset($show_gedcom_status)) echo '<b>'.__('No error messages above? In that case the tables have been created!').'</b><br>';
	}
	else {
		$_SESSION['add_tree']=true;
		echo __('The data in this gedcom will be appended to the existing data in this tree!').'<br>';
	}

	if(!isset($_POST['add_source'])) {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_add_source'");
	}
	else {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_add_source'");
	}

	if(!isset($_POST['reassign_gedcomnumbers'])) {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'");
	}
	else {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_reassign_gedcomnumbers'");
	}

	if(!isset($_POST['order_by_date'])) {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_order_by_date'");
	}
	else {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_order_by_date'");
	}

	if(!isset($_POST['order_by_fams'])) {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_order_by_fams'");
	}
	else {
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_order_by_fams'");
	}

	if (isset($_POST['process_geo_location'])){
		$result = $dbh->query("UPDATE humo_settings SET setting_value='y' WHERE setting_variable='gedcom_read_process_geo_location'");
	}
	else{
		$result = $dbh->query("UPDATE humo_settings SET setting_value='n' WHERE setting_variable='gedcom_read_process_geo_location'");
	}

	if (isset($_POST['gedcom_process_pict_path'])){
		$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST['gedcom_process_pict_path'])."' WHERE setting_variable='gedcom_process_pict_path'");
	}

	if (isset($_POST['commit_records'])){
		$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST['commit_records'])."' WHERE setting_variable='gedcom_read_commit_records'");
	}

	if (isset($_POST['time_out'])){
		$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST['time_out'])."' WHERE setting_variable='gedcom_read_time_out'");
	}

	//$progress_counter=0;
	$handle = fopen($_POST["gedcom_file"], "r");

	// *** Get character set from gedcom file ***
	$accent='';
	while (!feof($handle)) {
		$buffer = fgets($handle, 4096);
		//$buffer=rtrim($buffer,"\n\r");  // *** Strip newline ***
		//$buffer=ltrim($buffer," ");  // *** Strip starting spaces, for Pro-gen ***
		$buffer=trim($buffer); // *** Strip starting spaces for Pro-gen and ending spaces for Ancestry.
		// Save accent kind (ASCII, ANSI, ANSEL or UTF-8)
		//if (substr($buffer, 0, 6)=='1 CHAR'){ $accent=substr($buffer,7); }
		if (substr($buffer, 0, 6)=='1 CHAR'){
			$accent=substr($buffer,7);
			break;
		}
	}

	// *** PREPARE PROGRESS BAR ***
	$_SESSION['save_progress2']='0';
	$_SESSION['save_perc']='0';
	$_SESSION['save_total']='0';
	$_SESSION['save_starttime']='0';
	// *** END PREPARE PROGRESS BAR ***

	// Reset variables (needed to proceed after time out)
	$_SESSION['save_pointer']='0';

	// *** Reset gen_program ***
	$gen_program=''; $_SESSION['save_gen_program']=$gen_program;

	echo '<br><table><tr><td>';
		echo '<form method="post" action="'.$phpself.'">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="gedcom_accent" value="'.$accent.'">';

		if (isset($_POST['check_processed']))
			echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
		if (isset($_POST['show_gedcomnumbers']))
			echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
		if (isset($_POST['debug_mode']))
			echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';
		if (isset($_POST['time_out']))
			echo '<input type="hidden" name="time_out" value="'.$_POST['time_out'].'">';

		if(!isset($_POST['add_tree'])) {
			// *** Reset nr of persons and families ***
			$sql = $dbh->query("UPDATE humo_trees
				SET tree_persons='', tree_families=''
				WHERE tree_prefix='".$_SESSION['tree_prefix']."'");
		}
		if(isset($_POST['add_tree'])) {
			echo '<input type="hidden" name="add_tree" value="1">';
		}
		else {
			echo '<input type="hidden" name="add_tree" value="">';
		}

		echo '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';

		echo '<input type="Submit" name="step3" value="'.__('Step').' 3">';
		echo '</form>';
	echo '</td>';
	if(isset($_POST['add_tree'])) {
		echo '<td>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<form method="post" style="display:inline" action="'.$phpself.'">';
		echo '<input type="hidden" name="page" value="tree">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="Submit" name="back" value="'.__('Cancel').'">';
		echo '</form>';
		echo '</td>';
	}
	echo '</tr></table>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
}

// ************************************************************************************************
// *** STEP 3 READ Gedcom file ***
// ************************************************************************************************
if (isset($_POST['step3'])){

	// *** Processing time ***
	if($_SESSION['save_starttime']==0) { $_SESSION['save_starttime']=time(); }
	$_SESSION['save_start_timeout']=time(); // *** Start controlled time-out ***

	// begin step 3 merge additions
	$add_tree = false;
	if($_SESSION['add_tree']==true) {
		$add_tree = true;
		unset($_SESSION['add_tree']); // we don't want the session variable to persist - can cause problems!
	}

	$reassign = false;
	if ($humo_option["gedcom_read_reassign_gedcomnumbers"]=='y'){ $reassign = true; }

	if($add_tree==true) {
		// if we add a tree we have to change the gedcomnumbers of pers, fam, source, addresses and notes
		// so that they will be different from the existing ones.
		// therefore we check what is the largest gednr in each of them
		// and in gedcom_cls.php we add this number to the ones in the new gedcom
		// this way they will never be the same as the existing ones

		// I40
		$test_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_pers_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->pers_gedcomnumber));
			if ($gednum > $largest_pers_ged) { $largest_pers_ged = $gednum; }
		}
		// F40
		$test_qry = "SELECT fam_gedcomnumber FROM humo_families WHERE fam_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_fam_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->fam_gedcomnumber));
			if ($gednum > $largest_fam_ged) { $largest_fam_ged = $gednum; }
		}
		// S40
		$test_qry = "SELECT source_gedcomnr FROM humo_sources WHERE source_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_source_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->source_gedcomnr));
			if ($gednum > $largest_source_ged) { $largest_source_ged = $gednum; }
		}
		//  R40 (RESI)
		$test_qry = "SELECT address_gedcomnr FROM humo_addresses WHERE address_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_address_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->address_gedcomnr));
			if ($gednum > $largest_address_ged) { $largest_address_ged = $gednum; }
		}
		//  R40 (REPO)
		$test_qry = "SELECT repo_gedcomnr FROM humo_repositories WHERE repo_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_repo_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->repo_gedcomnr));
			if ($gednum > $largest_repo_ged) { $largest_repo_ged = $gednum; }
		}
		// N40 (texts)
		$test_qry = "SELECT text_gedcomnr FROM humo_texts WHERE text_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_text_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->text_gedcomnr)); // takes away @@'s and any other letters
			if ($gednum > $largest_text_ged) { $largest_text_ged = $gednum; }
		}

		// @O40@ object table
		$test_qry = "SELECT event_gedcomnr FROM humo_events WHERE event_tree_id='".$tree_id."'";
		$geds = $dbh->query($test_qry);
		$largest_object_ged = 0;
		while ($gedsDb = $geds->fetch(PDO::FETCH_OBJ)) {
			$gednum = (int)(preg_replace('/\D/','',$gedsDb->event_gedcomnr)); // takes away @@'s and any other letters
			if ($gednum > $largest_object_ged) { $largest_object_ged = $gednum; }
		}

	}

	// for merging when we read in a new tree we have to make sure that the relevant rel_merge row in the Db is removed.
	$qry = "DELETE FROM humo_settings WHERE setting_variable ='rel_merge_".$_SESSION['tree_prefix']."'"; // doesn't create error if not exists
	$result = $dbh->query($qry);
	// we have to make sure that the dupl_arr session is unset if it exists.
	if(isset($_SESSION['dupl_arr_'.$_SESSION['tree_prefix']])) {
		unset($_SESSION['dupl_arr_'.$_SESSION['tree_prefix']]);
	// we have to make sure the present_compare session is unset, if exists
	}
	if(isset($_SESSION['present_compare_'.$_SESSION['tree_prefix']])) {
		unset($_SESSION['present_compare_'.$_SESSION['tree_prefix']]);
	}
	// End step 3 merge additions 

	// *** Weblog Class ***

	// variables to reassign new gedcomnumbers (in gedcom_cls.php)
	if(isset($reassign_array)) { unset($reassign_array); }
	if($reassign==true) {  // reassign gedcomnumbers when importing tree
		$new_gednum["I"] = 1;
		$new_gednum["F"] = 1;
		$new_gednum["M"] = 1;
		$new_gednum["O"] = 1;
		$new_gednum["S"] = 1;
		$new_gednum["R"] = 1;
		$new_gednum["RP"] = 1;
		$new_gednum["N"] = 1;
	}
	if($add_tree==true) { // reassign gedcomnumbers when importing added tree in merging
		$new_gednum["I"] = $largest_pers_ged + 1;
		$new_gednum["F"] = $largest_fam_ged + 1;
		$new_gednum["M"] = $largest_fam_ged + 1;
		$new_gednum["O"] = $largest_fam_ged + 1;
		$new_gednum["S"] = $largest_source_ged + 1;
		$new_gednum["R"] = $largest_address_ged + 1;
		$new_gednum["RP"] = $largest_repo_ged + 1;
		$new_gednum["N"] = $largest_text_ged + 1;
	}

	include_once(CMS_ROOTPATH_ADMIN.'include/gedcom_cls.php');
	$gedcom_cls = New gedcom_cls;

	require (CMS_ROOTPATH_ADMIN."prefixes.php");
	$loop2=count($pers_prefix);
	for ($i=0; $i<$loop2; $i++) {
		//$prefix[$i]=addslashes($pers_prefix[$i]);
		$prefix[$i]=$pers_prefix[$i];
		$prefix[$i]=str_replace("_", " ", $prefix[$i]);
		$prefix_length[$i]=strlen($prefix[$i]);
	}

	echo __('<b>STEP 3) Processing Gedcom file:</b>
<p>The following lines have to be processed without error messages...<br>
<b>Processing may take a while!!</b>').'<br>';

	// *** some providers use a timeout of 30 seconden, continue button needed. ***
	/*
	echo '<form method="post" action="'.$phpself.'" style="display : inline">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';

		echo '<input type="hidden" name="gedcom_accent" value="'.$_POST['gedcom_accent'].'">';
		if (isset($_POST['check_processed'])){
			echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
		}
		if (isset($_POST['show_gedcomnumbers'])){
			echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
		}
		if (isset($_POST['debug_mode'])){
			echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';
		}

		echo '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';
		echo '<input type="hidden" name="timeout" value="1">';
		echo __('ONLY use in case of a time-out, to continue click:').' <input type="Submit" name="step3" value="'.__('Step').' 3">';
	echo '</form><br><br>';
	*/

	// *** some providers use a timeout of 30 seconden, continue button needed. ***
	if ($_POST['time_out']=='0'){
		echo '<form method="post" action="'.$phpself.'" style="display : inline">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';

			echo '<input type="hidden" name="gedcom_accent" value="'.$_POST['gedcom_accent'].'">';
			if (isset($_POST['check_processed']))
				echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
			if (isset($_POST['show_gedcomnumbers']))
				echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
			if (isset($_POST['debug_mode']))
				echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';

			echo '<input type="hidden" name="timeout_restart" value="1">';
			echo '<input type="hidden" name="step1" value="'.$_POST['gedcom_file'].'">';

			echo __('ONLY use in case of a time-out, to continue click:').' <input type="Submit" name="timeout" value="'.__('Restart').' ">';
			echo ' '.__('Restarts reading of gedcom using a controlled time-out.');
		echo '</form><br><br>';
	}

	$process_gedcom="";
	$buffer2="";

	// *** PREPARE PROGRESS BAR ***
	$progress2=$_SESSION['save_progress2'];

	if (!isset($_POST['show_gedcomnumbers'])){
		echo '<div id="progress" style="width:500px;border:1px solid #ccc;"></div>';
		echo '<!-- Progress information -->';
		echo '<div id="information" style="width"></div>';

		$i=$_SESSION['save_progress2']; // save number of lines processed
		$perc=$_SESSION['save_perc'];   // save percentage processed

		$total=0;
		if($_SESSION['save_total']=='0') { // only first time in session: count number of lines in gedcom
			$handle = fopen($_POST["gedcom_file"], "r");
			while(!feof($handle)){
				$line = fgets($handle);
				$total++;
			}
			$_SESSION['save_total']=$total;
			fclose($handle);
		}
		$total = $_SESSION['save_total'];

		// Javascript for initial display of the progress bar and information (or after timeout)
		$percent=$perc."%"; if($perc==0) { $percent="0.5%"; } // show at least some green 
		echo '<script language="javascript">';
		echo 'document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#00CC00;\">&nbsp;</div>";';
		echo 'document.getElementById("information").innerHTML="'.$i.' / '.$total.' '.__('lines processed').' ('.$perc.'%).";';
		echo '</script>';
		// This is for the buffer achieve the minimum size in order to flush data
		echo str_repeat(' ',1024*64);
		// Send output to browser immediately
		ob_flush(); 
		flush(); // IE

		$devider=50; // determines the steps in percentages - regular: 2%
		if($total >200000) { $devider=100; } // 1% for larger files with over 200,000 lines
		if($total > 1000000) { $devider = 200; } // 0.5% for very large files
		$step = round($total/$devider);
	}
	// *** END preparation of progress bar ***


	require_once(CMS_ROOTPATH_ADMIN."include/ansel2unicode/ansel2unicode.php");
	global $a2u;
	$a2u = new Ansel2Unicode();

	function encode($buffer, $gedcom_accent){
		global $a2u;

		if ($gedcom_accent=="ASCII") {
			// These methods don't work :-(
			//$buffer=iconv("ASCII","UTF-8//IGNORE//TRANSLIT",$buffer);
			//$buffer=utf8_encode($buffer);

			// It looks like this is the only method that alway works:
			// Step 1: convert ASCII to html entities.
			$buffer=asciihtml($buffer);
			// Step 2: convert entities to UTF-8.
			$buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
		}

		if ($gedcom_accent=="ANSEL") {
			$buffer=$a2u->convert($buffer);

			// *** Method below is a lot faster, but accent characters are a problem ***
			//$buffer=asciihtml($buffer);
			//$buffer=anselhtml($buffer);
			//$buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
		}

		if ($gedcom_accent=="ANSI") {
			//$buffer=htmlentities($buffer,ENT_QUOTES,'ISO-8859-1');
			//$buffer=ansihtml($buffer);
			$buffer=iconv("windows-1252","UTF-8",$buffer);
			//$buffer=iconv("windows-1252","UTF-8//IGNORE//TRANSLIT",$buffer);
		}

		if ($gedcom_accent=="UTF-8") {
			// *** No conversion needed ***
		}

//echo mb_detect_encoding($buffer); // *** Show character set: doesn't seem to work properly... ***
//echo '<br>';

		//$buffer=addslashes($buffer);
		return $buffer;
	}


	// TEST: lock tables. Unfortunately not much faster than usual... ONLY FOR MYISAM TABLES!
	/*
	mysql_query("LOCK TABLES
		humo_person
		humo_events
		humo_addresses
		humo_family
		humo_connections
		humo_humo_location
		humo_texts
		humo_sources
		humo_repositories
		WRITE;");
	*/
	// *** Batch processing for InnoDB tables ***
	$commit_counter=0;
	$commit_records=$humo_option["gedcom_read_commit_records"];
	if ($commit_records>1){ $dbh->beginTransaction(); }


	/* Insert a temporary line into database to get latest id.
	*  Must be done because table can be empty when reloading gedcom file...
	*  Even in an empty table, latest id can be a high number...
	*/
	$sql="INSERT INTO humo_events SET event_tree_id='".$tree_id."'";
	$result=$dbh->query($sql);
	$calculated_event_id = $dbh->lastInsertId();
	$sql="DELETE FROM humo_events WHERE event_id='".$calculated_event_id."'";
	$result=$dbh->query($sql);

	$sql="INSERT INTO humo_addresses SET address_tree_id='".$tree_id."'";
	$result=$dbh->query($sql);
	$calculated_address_id = $dbh->lastInsertId();
	$sql="DELETE FROM humo_addresses WHERE address_id='".$calculated_address_id."'";
	$result=$dbh->query($sql);

	$sql="INSERT INTO humo_connections SET connect_tree_id='".$tree_id."'";
	$result=$dbh->query($sql);
	$calculated_connect_id = $dbh->lastInsertId();
	$sql="DELETE FROM humo_connections WHERE connect_id='".$calculated_connect_id."'";
//echo $sql.' '.$calculated_connect_id.'<br>';
	$result=$dbh->query($sql);

	// *****************
	// *** Read file ***
	// *****************

	$handle = fopen($_POST["gedcom_file"], "r");

	// *** CONTINUE AFTER TIME_OUT ***
	// Set pointer if continued
	if ($_SESSION['save_pointer']>0) {
		fseek($handle, $_SESSION['save_pointer']);
	}

	if (isset($_SESSION['save_gen_program'])) {
		$gen_program=$_SESSION['save_gen_program'];
	}

	$level0='';
	$last_pointer=0;

	while (!feof($handle)) {
		//$buffer = fgets($handle, 4096);
		$buffer = fgets($handle);
		$buffer=rtrim($buffer,"\n\r");  // *** strip newline ***
		$buffer=ltrim($buffer," ");  // *** Strip starting spaces, for Pro-gen ***

//TEST, show line after controlled time-out:
//if ($last_pointer==0) echo 'New line: '.$buffer.'<br>';

		// *** Controlled timeout pointers, save last pointer before a "0 @" line ***
		$previous_pointer=$last_pointer;
		$last_pointer=ftell($handle);
		if (substr($buffer,0,3)=='0 @'){
			$save_pointer=$previous_pointer;
		}

// TEST: show memory usage
//if (!isset($memory)) $memory=memory_get_usage();
//$calc_memory=(memory_get_usage()-$memory);
//if ($calc_memory>100){ echo '<b>'; }
//	echo '<br>'.memory_get_usage().' '.$calc_memory.'@ ';
//	$memory=memory_get_usage();
//	echo ' '.$buffer;
//if ($calc_memory>100){ echo '!!</b>'; }

		// *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
		if ($commit_records>1){
			$commit_counter++;
			if ($commit_counter>$humo_option["gedcom_read_commit_records"]){
				$commit_counter=0;
				// *** Save data in database ***
				$dbh->commit();
				// *** Start next process batch ***
				$dbh->beginTransaction();
			}
		}

		// *** Strip all spaces for Ancestry gedcom ***
		if ( isset($gen_program) AND $gen_program=='Ancestry.com Family Trees'){
			$buffer=rtrim($buffer," ");
		}

		$start_gedcom='';
		// *** Remove BOM header from UTF-8 BOM file ***
		if ($start_gedcom==''){
			if(substr($buffer,0,3) == pack("CCC",0xef,0xbb,0xbf)){
				// *** Remove BOM UTF-8 characters from 1st line ***
				$buffer = substr($buffer,3);
			}
		}
		if ( substr($buffer, 0, 3)=='0 @' OR $buffer=="0 TRLR"){ $start_gedcom=1; }

		// *** Start reading gedcom parts ***
		if ($start_gedcom){
			if ($process_gedcom=="person"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_person($buffer2);
				$process_gedcom=""; $buffer2="";

// TEST: show memory usage
//if (!isset($memory)) $memory=memory_get_usage();
//$calc_memory=(memory_get_usage()-$memory);
//if ($calc_memory>100){ echo '<b>'; }
//	echo '<br>'.memory_get_usage().' '.$calc_memory.'@ ';
//	$memory=memory_get_usage();
//	echo ' '.$buffer;
//if ($calc_memory>100){ echo '!!</b>'; }

			}

			elseif ($process_gedcom=="family"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_family($buffer2,0,0);
				$process_gedcom=""; $buffer2="";
			}

			elseif ($process_gedcom=="text"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_text($buffer2);
				$process_gedcom=""; $buffer2="";
			}

			elseif ($process_gedcom=="source"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_source($buffer2);
				$process_gedcom=""; $buffer2="";
			}

			// *** Repository ***
			elseif ($process_gedcom=="repository"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_repository($buffer2);
				$process_gedcom=""; $buffer2="";
			}

			elseif ($process_gedcom=="address"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_address($buffer2);
				$process_gedcom=""; $buffer2="";
			}

			elseif ($process_gedcom=="object"){
				$buffer2=encode($buffer2, $_POST["gedcom_accent"]);
				$gedcom_cls -> process_object($buffer2);
				$process_gedcom=""; $buffer2="";
			}

		}

		// *** CHECK ***
		if (substr($buffer, -6, 6)=='@ INDI'){
			$process_gedcom="person"; $buffer2="";
		}
		elseif (substr($buffer, -5, 5)=='@ FAM'){
			$process_gedcom="family"; $buffer2="";
		}
		elseif (substr($buffer, 0, 3)=='0 @'){
			// *** Aldfaer text: 0 @N954@ NOTE ***
			if (strpos($buffer,'@ NOTE')>1){
				$process_gedcom="text"; $buffer2="";
			}

			if (substr($buffer, -6, 6)=='@ SOUR'){
				$process_gedcom="source"; $buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ REPO'){
				$process_gedcom="repository"; $buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ RESI'){
				$process_gedcom="address"; $buffer2="";
			}
			elseif (substr($buffer, -6, 6)=='@ OBJE'){
				$process_gedcom="object"; $buffer2="";
			}
		}

		$buffer2=$buffer2.$buffer."\n";

		// *** Save level0 ***
		if (substr($buffer,0,1)=='0'){ $level0=substr($buffer,2,6); }
		// *** 1 SOUR Haza-Data ***
		if ($level0=='HEAD' AND substr($buffer,2,4)=='SOUR'){
			$gen_program=substr($buffer,7);
			$_SESSION['save_gen_program']=$gen_program;
			echo '<br><br>'.__('Gedcom file').': <b>'.$gen_program.'</b>, ';

			printf(__('this is an <b>%s</b> file'), $_POST["gedcom_accent"]);
			echo '<br>';

			// Save tree <-> gedcom connection - write gedcom to "tree_gedcom" in relevant tree
			$dbh->query("UPDATE humo_trees SET tree_gedcom='".$_POST["gedcom_file"]."',
				tree_gedcom_program='".$gen_program."'
				WHERE tree_prefix='".$_SESSION['tree_prefix']."'");
		}

		// *** progress bar ***
		//if (!isset($_POST['show_gedcomnumbers']) AND $progress>($progress_counter/500)){
		if (!isset($_POST['show_gedcomnumbers'])) {
			$i++; $_SESSION['save_progress2']=$i;

			// Calculate the percentage
			if($i%$step==0) {
				if($devider==50)  {$perc+=2; }
				elseif($devider==100) { $perc+=1; }
				elseif($devider==200) { $perc+=0.5; }
				$_SESSION['save_perc']=$perc;
				$percent = $perc."%";
 
				// Javascript for updating the progress bar and information
				echo '<script language="javascript">';
				echo 'document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#00CC00;\">&nbsp;</div>";';
				echo 'document.getElementById("information").innerHTML="'.$i.' / '.$total.' '.__('lines processed').' ('.$percent.')";';
				echo '</script>';

				// This is for the buffer achieve the minimum size in order to flush data
				echo str_repeat(' ',1024*64);

				// Send output to browser immediately
				ob_flush();
				flush(); // for IE
			}
		}

		// *** Save process time every cycle (time-out) ***
		$process_time=time();
		$_SESSION['save_process_time']=$process_time-$_SESSION['save_starttime'];

		// *** Controlled time-out ***
		$time_out=0; if (is_numeric($_POST['time_out'])) $time_out=$_POST['time_out'];
		if ($time_out>0){
			if (($process_time-$_SESSION['save_start_timeout']) > $time_out){

				// *** Save data in database ***
				$dbh->commit();

				// *** Save pointer of gedcom file ***
				$_SESSION['save_pointer']=$save_pointer;

				// *** Set time for next cycle ***
				$_SESSION['save_start_timeout']=time();

				// *** Restart after controlled time-out. ***
				echo '<form method="post" action="'.$phpself.'" style="display : inline">';
					echo '<input type="hidden" name="page" value="'.$page.'">';
					echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
					echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';

					echo '<input type="hidden" name="gedcom_accent" value="'.$_POST['gedcom_accent'].'">';
					if (isset($_POST['check_processed'])){
						echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
					}
					if (isset($_POST['show_gedcomnumbers'])){
						echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
					}
					if (isset($_POST['debug_mode'])){
						echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';
					}

					echo '<input type="hidden" name="gedcom_file" value="'.$_POST['gedcom_file'].'">';
					echo '<input type="hidden" name="time_out" value="'.$time_out.'">';
					echo '<b>'.__('Controlled time-out to continue reading of gedcom file, click:').'</b> <input type="Submit" name="step3" value="'.__('Step').' 3"><br>';
					printf(' <b>'.__('Or wait %s seconds for automatic continuation. Some browsers will give a reload message...').'</b>', '5');

				echo '</form><br><br>';

				// *** Automatic reload after 5 seconds ***
				echo '<script type="text/javascript">setTimeout(function () { location.reload(true); }, 5000);</script>';
				exit();
			}
		}

	}
	fclose($handle);


	// *** Add a general source to all persons in this gedcom file ***
	if ($humo_option["gedcom_read_add_source"]=='y'){
		// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
		$new_nr_qry= "SELECT *, ABS(substring(source_gedcomnr, 2)) AS gednr
			FROM humo_sources WHERE source_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
		$new_nr_result = $dbh->query($new_nr_qry);
		$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);

		$new_gedcomnumber='S1';
		if (isset($new_nr->source_gedcomnr)) $new_gedcomnumber='S'.(substr($new_nr->source_gedcomnr,1)+1);

		$gedcom_date=strtoupper(date("d M Y"));
		$gedcom_time=date("H:i:s");
		//source_title='".__('Persons added by gedcom import.').' '.$gedcom_date.' '.$gedcom_time."',
		$sql="INSERT INTO humo_sources SET
			source_tree_id='".$tree_id."',
			source_gedcomnr='".$new_gedcomnumber."',
			source_status='',
			source_title='".__('Persons added by gedcom import.')."',
			source_date='".$gedcom_date."',
			source_place='',
			source_publ='',
			source_refn='',
			source_auth='',
			source_subj='',
			source_item='',
			source_kind='',
			source_repo_caln='',
			source_repo_page='',
			source_repo_gedcomnr='',
			source_text='".__('Persons added by gedcom import.')."',
			source_new_date='".$gedcom_date."',
			source_new_time='".$gedcom_time."'";
		$result=$dbh->query($sql);

		// *** Replace temporary source number by all persons by a final source number ***
		$gebeurtsql="UPDATE humo_connections SET
			connect_source_id='".$new_gedcomnumber."'
			WHERE connect_tree_id='".$tree_id."' AND connect_source_id='Stemporary'";
		$result=$dbh->query($gebeurtsql);
	}


	// *** End of MyISAM batch processing ***
	// mysql_query("UNLOCK TABLES;");
	// *** End of InnoDB batch processing ***
	if ($commit_records>1){ $dbh->commit(); }

	// *** Show endtime ***
	$end_time=time();
	printf('<br>'.__('Reading in the file took: %d seconds').'<br>', $end_time-$_SESSION['save_starttime']);

	//*** Show "non-processed gedcom items" ***
	if (isset($_POST['check_processed'])){
		echo '<div style="height:350px;width:900px; overflow-y: scroll; white-space:nowrap;">';
			echo '<table class="humo" border="1" cellspacing="0">';
			echo '<tr><th>nr.</th><th colspan=5>'.__('Non-processed items').'</th></tr>';
			echo '<tr><th><br></th><th>'.__('Level').' 0</th><th>'.__('Level').' 1</th><th>'.__('Level').' 2</th><th>'.__('Level').' 3</th><th>'.__('text').'</th></tr>';
			if (isset($not_processed)){
				for ($i=0; $i<count($not_processed);$i++){ echo '<tr><td>'.($i+1).'</td><td>'.$not_processed[$i].'</td></tr>'."\n"; }
			}
			else{
				echo '<tr><td>0</td><td colspan=4>'.__('All items have been processed!').'</td></tr>'."\n";
			}
			echo '</table><br>';
		echo '</div>';
	}
	if (!isset($_POST['show_gedcomnumbers'])) {
		echo '<script language="javascript">';
		echo 'document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#00CC00;\">&nbsp;</div>";';
		echo 'document.getElementById("information").innerHTML="'.$total.' / '.$total.' '.__('lines processed').' (100%).";';
		echo '</script>';
		ob_flush();
		flush(); // for IE
	}
	echo '<br><form method="post" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
	echo '<input type="hidden" name="gen_program" value="'.$gen_program.'">';
	echo '<input type="Submit" name="step4" value="'.__('Step').' 4">';
	echo '</form>';

	if(CMS_SPECIFIC=="Joomla") {
		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}

}

// *** Step 4 ***
if (isset($_POST['step4'])){
	$start_time=time();
	$gen_program=$_POST['gen_program'];

	echo '<b>'.__('STEP 4) Final database processing:').'</b><br>';

	// *** To proceed if a (30 seconds) timeout has occured ***
	echo '<form method="post" action="'.$phpself.'">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="gen_program" value="'.$_POST['gen_program'].'">';
		//if (isset($_POST['check_processed'])){
		//	echo '<input type="hidden" name="check_processed" value="'.$_POST['check_processed'].'">';
		//}
		//if (isset($_POST['show_gedcomnumbers'])){
		//	echo '<input type="hidden" name="show_gedcomnumbers" value="'.$_POST['show_gedcomnumbers'].'">';
		//}
		//if (isset($_POST['debug_mode'])){
		//	echo '<input type="hidden" name="debug_mode" value="'.$_POST['debug_mode'].'">';
		//}
		echo '<br>'.__('ONLY use in case of a time-out, to continue click:').' <input type="Submit" name="step4" value="'.__('Step').' 4">';
	echo '</form><br>';

	// *** Show progress ***
	//echo '<div id="progress" style="width:500px;border:1px solid #ccc;"></div>';
	echo '<!-- Progress information -->';
	echo '<div id="information" style="width"></div>';
	$total=1;

	//echo '&gt;&gt;&gt; '.__('Processing single persons...');

	// *** Quick check for seperate saved texts in database (used in Aldfaer program and Reunion) and store them as standard texts ***
	$search_text_qry=$dbh->query("SELECT * FROM humo_texts WHERE text_tree_id='".$tree_id."' LIMIT 0,1");
	$count_text=$search_text_qry->rowCount();
	//$count_text=0;		// *** UITSCHAKELEN VAN VERWERKEN HUMO_TEXTS TABEL ***
	if ($count_text>0){

		// *** Number of records in text table, used to show a status counter ***
		$total_text_qry = $dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='".$tree_id."'"); 
		$total_text_db = $total_text_qry->fetch();
		$total_texts=$total_text_db[0];
		$total_processed_texts=0;

		echo '<br>&gt;&gt;&gt; '.__('Processing of referenced texts into standard texts...');
		echo ' ['.$total_texts.' text records].';

		$db_functions->set_tree_id($tree_id);

		// *** Batch processing for InnoDB tables ***
		$commit_counter=0;
		$commit_records=$humo_option["gedcom_read_commit_records"];
		if ($commit_records>1){ $dbh->beginTransaction(); }

		// *** Process texts in person table ***
		//$person_qry=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
		//while ($personDb=$person_qry->fetch(PDO::FETCH_OBJ)){

		// *** First only read pers_id, otherwise too much memory use ***
		$person2_qry=$dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
		while ($person2Db=$person2_qry->fetch(PDO::FETCH_OBJ)){

			$person_qry=$dbh->query("SELECT * FROM humo_persons WHERE pers_id='".$person2Db->pers_id."'");
			$personDb=$person_qry->fetch(PDO::FETCH_OBJ);

			$pers_text=''; 
			if (substr($personDb->pers_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_text,1,-1));
				if ($search_textDb){
					$pers_text=$search_textDb->text_text;

					// *** Search for all connected sources ***
					$connect_order=0;
					$connect_qry="SELECT * FROM humo_connections WHERE connect_tree_id='".$tree_id."'
						AND connect_kind='ref_text' AND connect_sub_kind='ref_text_source'
						AND connect_connect_id='".$search_textDb->text_gedcomnr."'
						ORDER BY connect_order";
					$connect_sql=$dbh->query($connect_qry);
					while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
						// *** Add to connection table ***
						$connect_order++;
						$gebeurtsql="INSERT INTO humo_connections SET
							connect_tree_id='".$tree_id."',
							connect_order='".$connect_order."',
							connect_kind='person',
							connect_sub_kind='pers_text_source',
							connect_connect_id='".safe_text_db($personDb->pers_gedcomnumber)."',
							connect_source_id='".safe_text_db($connectDb->connect_source_id)."'
							";
						$result=$dbh->query($gebeurtsql);
					}
				}
			}

			$pers_name_text=''; 
			if (substr($personDb->pers_name_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_name_text,1,-1));
				if ($search_textDb) $pers_name_text=$search_textDb->text_text;
			}

			$pers_birth_text=''; 
			if (substr($personDb->pers_birth_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_birth_text,1,-1));
				if ($search_textDb) $pers_birth_text=$search_textDb->text_text;
			}

			$pers_bapt_text='';
			if (substr($personDb->pers_bapt_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_bapt_text,1,-1));
				if ($search_textDb) $pers_bapt_text=$search_textDb->text_text;
			}

			$pers_death_text=''; 
			if (substr($personDb->pers_death_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_death_text,1,-1));
				if ($search_textDb) $pers_death_text=$search_textDb->text_text;
			}

			$pers_buried_text='';
			if (substr($personDb->pers_buried_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($personDb->pers_buried_text,1,-1));
				if ($search_textDb) $pers_buried_text=$search_textDb->text_text;
			}

			// *** Save all standard person texts ***
			if ($pers_text OR $pers_name_text OR $pers_birth_text OR $pers_bapt_text OR $pers_death_text OR $pers_buried_text){
				$first_item=true;
				// *** Remark: no need to check for fam_tree_id because fam_id is used ***
				$sql="UPDATE humo_persons SET ";
					if ($pers_text){ $first_item=false; $sql.="pers_text='".safe_text_db($pers_text)."'"; }
					if ($pers_name_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="pers_name_text='".safe_text_db($pers_name_text)."'";
					}
					if ($pers_birth_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="pers_birth_text='".safe_text_db($pers_birth_text)."'";
					}
					if ($pers_bapt_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="pers_bapt_text='".safe_text_db($pers_bapt_text)."'";
					}
					if ($pers_death_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="pers_death_text='".safe_text_db($pers_death_text)."'";
					}
					if ($pers_buried_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="pers_buried_text='".safe_text_db($pers_buried_text)."'";
					}
				$sql.=" WHERE pers_id='".$personDb->pers_id."'";
//echo $sql.'<hr>';
				$dbh->query($sql);

				// *** Update progress ***
				$total++;
				echo '<script language="javascript">';
					//echo 'document.getElementById("information").innerHTML="'.$total.' '.__('lines processed').'";';
					$status=' ['.__('persons').' '.($total_texts-$total_processed_texts).']';
					echo 'document.getElementById("information").innerHTML="'.$total.' '.__('lines processed').$status.'";';
				echo '</script>';
				ob_flush();
				flush(); // for IE

				// *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
				if ($commit_records>1){
					$commit_counter++;
					if ($commit_counter>$humo_option["gedcom_read_commit_records"]){
						$commit_counter=0;
						// *** Save data in database ***
						$dbh->commit();
						// *** Start next process batch ***
						$dbh->beginTransaction();
					}
				}
			}


		}

		// *** End of InnoDB batch processing ***
		if ($commit_records>1){
			// *** Save data in database ***
			$dbh->commit();
			// *** Start next process batch ***
			$dbh->beginTransaction();
		}


		// *** Process texts in family table ***
		//$fam_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."'");
		//while ($famDb=$fam_qry->fetch(PDO::FETCH_OBJ)){

		// *** Memory improvement, only read 1 full record at a time ***
		$fam_qry2=$dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='".$tree_id."'");
		while ($famDb2=$fam_qry2->fetch(PDO::FETCH_OBJ)){

			$fam_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_id='".$famDb2->fam_id."'");
			$famDb=$fam_qry->fetch(PDO::FETCH_OBJ);

			$fam_text=''; 
			if (substr($famDb->fam_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_text,1,-1));
				if ($search_textDb) $fam_text=$search_textDb->text_text;
			}

			$fam_relation_text='';
			if (substr($famDb->fam_relation_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_relation_text,1,-1));
				if ($search_textDb) $fam_relation_text=$search_textDb->text_text;
			}

			$fam_marr_notice_text='';
			if (substr($famDb->fam_marr_notice_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_marr_notice_text,1,-1));
				if ($search_textDb) $fam_marr_notice_text=$search_textDb->text_text;
			}

			$fam_marr_text='';
			if (substr($famDb->fam_marr_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_marr_text,1,-1));
				if ($search_textDb){
					$fam_marr_text=$search_textDb->text_text;
					// *** Search for all connected sources ***
					$connect_order=0;
					$connect_qry="SELECT * FROM humo_connections WHERE connect_tree_id='".$tree_id."'
						AND connect_kind='ref_text' AND connect_sub_kind='ref_text_source'
						AND connect_connect_id='".$search_textDb->text_gedcomnr."'
						ORDER BY connect_order";
					$connect_sql=$dbh->query($connect_qry);
					while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
						// *** Add to connection table ***
						$connect_order++;
						$gebeurtsql="INSERT INTO humo_connections SET
							connect_tree_id='".$tree_id."',
							connect_order='".$connect_order."',
							connect_kind='family',
							connect_sub_kind='family_text',
							connect_connect_id='".safe_text_db($famDb->fam_gedcomnumber)."',
							connect_source_id='".safe_text_db($connectDb->connect_source_id)."'
							";
						$result=$dbh->query($gebeurtsql);
					}
				}
			}

			$fam_marr_church_notice_text='';
			if (substr($famDb->fam_marr_church_notice_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_marr_church_notice_text,1,-1));
				if ($search_textDb) $fam_marr_church_notice_text=$search_textDb->text_text;
			}

			$fam_marr_church_text='';
			if (substr($famDb->fam_marr_church_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_marr_church_text,1,-1));
				if ($search_textDb) $fam_marr_church_text=$search_textDb->text_text;
			}

			$fam_div_text='';
			if (substr($famDb->fam_div_text, 0, 1)=='@'){
				$total_processed_texts++;
				$search_textDb=$db_functions->get_text(substr($famDb->fam_div_text,1,-1));
				if ($search_textDb) $fam_div_text=$search_textDb->text_text;
			}

			// *** Save all standard family texts ***
			if ($fam_text OR $fam_relation_text OR $fam_marr_notice_text OR $fam_marr_text OR $fam_marr_church_notice_text
				OR $fam_marr_church_text OR $fam_div_text){
				$first_item=true;
				// *** Remark: no need to check for fam_tree_id because fam_id is used ***
				$sql="UPDATE humo_families SET ";
					if ($fam_text){ $first_item=false; $sql.="fam_text='".safe_text_db($fam_text).'"'; }

					if ($fam_relation_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_relation_text='".safe_text_db($fam_relation_text)."'";
					}

					if ($fam_marr_notice_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_marr_notice_text='".safe_text_db($fam_marr_notice_text)."'";
					}

					if ($fam_marr_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_marr_text='".safe_text_db($fam_marr_text)."'";
					}

					if ($fam_marr_church_notice_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_marr_church_notice_text='".safe_text_db($fam_marr_church_notice_text)."'";
					}

					if ($fam_marr_church_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_marr_church_text='".safe_text_db($fam_marr_church_text)."'";
					}

					if ($fam_div_text){
						if (!$first_item) $sql.=", ";
						$first_item=false; $sql.="fam_div_text='".safe_text_db($fam_div_text)."'";
					}

				$sql.=" WHERE fam_id='".$famDb->fam_id."'";
//echo $sql.'<hr>';
				$dbh->query($sql);

				// *** Update progress ***
				$total++;
				echo '<script language="javascript">';
					//echo 'document.getElementById("information").innerHTML="'.$total.' '.__('lines processed').'";';
					$status=' ['.__('families').' '.($total_texts-$total_processed_texts).']';
					echo 'document.getElementById("information").innerHTML="'.$total.' '.__('lines processed').$status.'";';
				echo '</script>';
				ob_flush();
				flush(); // for IE

				// *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
				if ($commit_records>1){
					$commit_counter++;
					if ($commit_counter>$humo_option["gedcom_read_commit_records"]){
						$commit_counter=0;
						// *** Save data in database ***
						$dbh->commit();
						// *** Start next process batch ***
						$dbh->beginTransaction();
					}
				}
			}

		}

		// *** End of InnoDB batch processing ***
		if ($commit_records>1){
			// *** Save data in database ***
			$dbh->commit();
		}

	}



	// *** Process text by name etc. ***
	echo '<br>&gt;&gt;&gt; '.__('Processing texts IN names...');
	//$person_qry=$dbh->query("SELECT pers_id, pers_name_text, pers_firstname, pers_lastname
	//	FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
	$person_qry=$dbh->query("SELECT pers_id, pers_name_text, pers_firstname, pers_lastname
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_name_text!=''");
//echo 'AANTAL '.$person_qry->rowCount();
	while ($personDb=$person_qry->fetch(PDO::FETCH_OBJ)){
		//*** Haza-data option: text IN name where "*" is. ***
		if ($personDb->pers_name_text){
			// *** Check * in firstname ***
			$position=strpos($personDb->pers_firstname, '*');
			if ($position!== false){
				// pers_name_text change into: process_text(pers_name_text)
				$pers_firstname=substr($personDb->pers_firstname,0,$position).
					$personDb->pers_name_text.substr($personDb->pers_firstname,$position+1);
				$sql="UPDATE humo_persons
					SET pers_firstname='".safe_text_db($pers_firstname)."', pers_name_text=''
					WHERE pers_id='".$personDb->pers_id."'";
				$dbh->query($sql);
			}

			// ***Check * in lastname ***
			$position=strpos($personDb->pers_lastname, '*');
			if ($position!== false){
				//pers_name_text change into: process_text(pers_name_text)
				$pers_lastname=substr( $personDb->pers_lastname,0,$position).
					$personDb->pers_name_text.substr( $personDb->pers_lastname,$position+1);
				$sql="UPDATE humo_persons SET pers_lastname='".$pers_lastname."', pers_name_text=''
					WHERE pers_id='".$personDb->pers_id."'";
				$dbh->query($sql);
			}
		}
	}

	// if a humo_location table exists, refresh the location_status column
	$res = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if ($humo_option["gedcom_read_process_geo_location"]=='y' AND $res->rowCount()) {

		// after import, and ONLY for people with a humo_location table for googlemaps, refresh the location_status fields
		// first, make sure the location_status column exists. If not create it
		echo '<br>&gt;&gt;&gt; '.__('Updating location database...');
		$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
		$exists = $result->rowCount();
		if(!$exists) {
			$dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
		}

		$all_loc = $dbh->query("SELECT location_location FROM humo_location");
		while($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {
			$loca_array[$all_locDb->location_location] = "";
		}
		$status_string = "";

		$tree_id_string = " AND ( ";
		$id_arr = explode(";",substr($humo_option['geo_trees'],0,-1)); // substr to remove trailing ;
		foreach($id_arr as $value) {
			$tree_id_string .= "tree_id='".substr($value,1)."' OR ";  // substr removes leading "@" in geo_trees setting string
		}
		$tree_id_string = substr($tree_id_string,0,-4).")"; // take off last " ON " and add ")"

		$tree_pref_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ".$tree_id_string." ORDER BY tree_order";
		$tree_pref_result = $dbh->query($tree_pref_sql);
		while ($tree_prefDb=$tree_pref_result->fetch(PDO::FETCH_OBJ)){

			$result=$dbh->query("SELECT pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place FROM humo_persons WHERE pers_tree_id='".$tree_id."'");

			while($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
				if (isset($loca_array[$resultDb->pers_birth_place]) AND strpos($loca_array[$resultDb->pers_birth_place],$tree_prefDb->tree_prefix."birth ")===false) {
					$loca_array[$resultDb->pers_birth_place] .= $tree_prefDb->tree_prefix."birth ";
				}
				if (isset($loca_array[$resultDb->pers_bapt_place]) AND strpos($loca_array[$resultDb->pers_bapt_place],$tree_prefDb->tree_prefix."bapt ")===false) {
					$loca_array[$resultDb->pers_bapt_place] .= $tree_prefDb->tree_prefix."bapt ";
				}
				if (isset($loca_array[$resultDb->pers_death_place]) AND strpos($loca_array[$resultDb->pers_death_place],$tree_prefDb->tree_prefix."death ")===false) {
					$loca_array[$resultDb->pers_death_place] .= $tree_prefDb->tree_prefix."death ";
				}
				if (isset($loca_array[$resultDb->pers_buried_place]) AND strpos($loca_array[$resultDb->pers_buried_place],$tree_prefDb->tree_prefix."buried ")===false) {
					$loca_array[$resultDb->pers_buried_place] .= $tree_prefDb->tree_prefix."buried ";
				}
			}
		}
		foreach($loca_array as $key => $value) {
			$dbh->query("UPDATE humo_location SET location_status = '".$value."' WHERE location_location = '".addslashes($key)."'");
		}
		if(strpos($humo_option['geo_trees'],"@".$tree_id.";")===false) {  
			$dbh->query("UPDATE humo_settings SET setting_value = CONCAT(setting_value,'@".$tree_id.";') WHERE setting_variable = 'geo_trees'");
			$humo_option['geo_trees'] .= "@".$tree_id.";";
		} 
	} // end refresh location_status column


	// *** Jeroen Beemster Jan 2006. Code rewritten in June 2013 by Huub. Order children and marriages ***
	// If there are children without dates, ordering doesn't work very good...
	if ($humo_option["gedcom_read_order_by_date"]=='y') {
		function date_string($text) {
			$text=str_replace("JAN", "01", $text);
			$text=str_replace("FEB", "02", $text);
			$text=str_replace("MAR", "03", $text);
			$text=str_replace("APR", "04", $text);
			$text=str_replace("MAY", "05", $text);
			$text=str_replace("JUN", "06", $text);
			$text=str_replace("JUL", "07", $text);
			$text=str_replace("AUG", "08", $text);
			$text=str_replace("SEP", "09", $text);
			$text=str_replace("OCT", "10", $text);
			$text=str_replace("NOV", "11", $text);
			$text=str_replace("DEC", "12", $text);
			$returnstring = substr($text,-4).substr(substr($text,-7),0,2).substr($text,0,2);
			return $returnstring;
			// Solve maybe later: date_string 2 mei is smaller then 10 may (2 birth in 1 month is rare...).
		}

		echo '<br>&gt;&gt;&gt; '.__('Order children...');

		//$fam_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_children!=''");
		$fam_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_children!='' AND (INSTR(fam_children,';')>0) ");
		while ($famDb=$fam_qry->fetch(PDO::FETCH_OBJ)){
			$child_array=explode(";",$famDb->fam_children);
//echo '<br>'.$famDb->fam_children.' ';
			$nr_children = count($child_array);
			//if ($nr_children > 1) {
				unset ($children_array);
				for ($i=0; $i<$nr_children; $i++){
					$child=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$child_array[$i]."'");
					@$childDb=$child->fetch(PDO::FETCH_OBJ);

					$child_array_nr=$child_array[$i];
					if ($childDb->pers_birth_date){
						$children_array[$child_array_nr]=date_string($childDb->pers_birth_date);
					}
					elseif ($childDb->pers_bapt_date){
						$children_array[$child_array_nr]=date_string($childDb->pers_bapt_date);
					}
					else{
						$children_array[$child_array_nr]='';
					}
//echo $children_array[$child_array_nr].' ';
				}

				asort ($children_array);

				$fam_children='';
				foreach ($children_array as $key => $val) {
					if ($fam_children!=''){ $fam_children.=';'; }
					$fam_children.=$key;
				}

				if ($famDb->fam_children!=$fam_children){
					$sql = "UPDATE humo_families SET fam_children='".$fam_children."' WHERE fam_id='".$famDb->fam_id."'";
//echo $sql.' ';
					$dbh->query($sql);
				}
			//}
		}
	}

	// *** Order families, added in november 2018 by Huub. ***
	// If there is a relation without dates, ordering doesn't work very good...
	if ($humo_option["gedcom_read_order_by_fams"]=='y') {
		function date_string2($text) {
			$text=str_replace("JAN", "01", $text);
			$text=str_replace("FEB", "02", $text);
			$text=str_replace("MAR", "03", $text);
			$text=str_replace("APR", "04", $text);
			$text=str_replace("MAY", "05", $text);
			$text=str_replace("JUN", "06", $text);
			$text=str_replace("JUL", "07", $text);
			$text=str_replace("AUG", "08", $text);
			$text=str_replace("SEP", "09", $text);
			$text=str_replace("OCT", "10", $text);
			$text=str_replace("NOV", "11", $text);
			$text=str_replace("DEC", "12", $text);
			$returnstring = substr($text,-4).substr(substr($text,-7),0,2).substr($text,0,2);
			return $returnstring;
			// Solve maybe later: date_string 2 mei is smaller then 10 may (2 marriages in 1 month is rare...).
		}

		echo '<br>&gt;&gt;&gt; '.__('Order families...');

		// *** Find only persons with multiple relations ***
		$person=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_fams!='' AND (INSTR(pers_fams,';')>0) ");
		while($personDb=$person->fetch(PDO::FETCH_OBJ)){
//echo '<br>'.$personDb->pers_fams.' - ';
			$fam_array=explode(";",$personDb->pers_fams);
			$nr_fams = count($fam_array);

			unset ($families_array);
			for ($i=0; $i<$nr_fams; $i++){
				$fam_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam_array[$i]."'");
				$famDb=$fam_qry->fetch(PDO::FETCH_OBJ);

				$fam_array_nr=$fam_array[$i];
				if ($famDb->fam_relation_date){
					$families_array[$fam_array_nr]=date_string2($famDb->fam_relation_date);
				}
				elseif ($famDb->fam_marr_notice_date){
					$families_array[$fam_array_nr]=date_string2($famDb->fam_marr_notice_date);
				}
				elseif ($famDb->fam_marr_date){
					$families_array[$fam_array_nr]=date_string2($famDb->fam_marr_date);
				}
				elseif ($famDb->fam_marr_church_notice_date){
					$families_array[$fam_array_nr]=date_string2($famDb->fam_marr_church_notice_date);
				}
				elseif ($famDb->fam_marr_church_date){
					$families_array[$fam_array_nr]=date_string2($famDb->fam_marr_church_date);
				}
				else{
					$families_array[$fam_array_nr]='';
				}

//echo $families_array[$fam_array_nr].' ';

			}

			asort ($families_array);

			$families='';
			foreach ($families_array as $key => $val) {
				if ($families!=''){ $families.=';'; }
				$families.=$key;
			}

			if ($personDb->pers_fams!=$families){
				$sql = "UPDATE humo_persons SET fams='".$families."' WHERE pers_id='".$personDb->pers_id."'";
//echo $sql.'<br>';
				$dbh->query($sql);
			}

		}

	}


	// *** Process Aldfaer adoption children: remove uneccessary added relations ***
	if ($gen_program=='ALDFAER') {
		function fams_remove($personnr, $familynr){
			global $dbh, $tree_id;
			$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$personnr."'";
			$person_result = $dbh->query($person_qry);
			$person_db=$person_result->fetch(PDO::FETCH_OBJ);
			if (@$person_db->pers_gedcomnumber){
				$fams=explode(";",$person_db->pers_fams);
				foreach ($fams as $key => $value) {
					if ($fams[$key] != $familynr){ $fams2[]=$fams[$key]; }
				}
				$pers_indexnr=''; if ($person_db->pers_famc){ $pers_indexnr=$person_db->pers_famc; }
				$fams3=''; if (isset($fams2[0])){ $fams3 = implode(";", $fams2); $pers_indexnr=$fams2[0]; }
				$sql="UPDATE humo_persons SET
					pers_fams='".$fams3."', pers_indexnr='".$pers_indexnr."'
					WHERE pers_id='".$person_db->pers_id."'";
				$result=$dbh->query($sql);
			}
		}

		$famc_adoptive_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_kind='adoption_by_person'");
		while($famc_adoptiveDb=$famc_adoptive_qry->fetch(PDO::FETCH_OBJ)){
			$fam=$famc_adoptiveDb->event_event;

			// *** Remove fams number from man and woman ***
			$new_nr_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam."'";
			$new_nr_result = $dbh->query($new_nr_qry);
			$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);

			if ($new_nr->fam_man){
				$sql="UPDATE humo_events SET
					event_event='".$new_nr->fam_man."' WHERE event_id='".$famc_adoptiveDb->event_id."'";
				$dbh->query($sql);
				fams_remove($new_nr->fam_man, $fam);
			}
			unset ($fams2);
			if ($new_nr->fam_woman){
				$sql="UPDATE humo_events SET
					event_event='".$new_nr->fam_woman."' WHERE event_id='".$famc_adoptiveDb->event_id."'";
				$dbh->query($sql);
				fams_remove($new_nr->fam_woman, $fam);
			}

			$sql="DELETE FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam."'";
			$result=$dbh->query($sql);
		}
	}


	// *** Count persons and families ***
	echo '<br>&gt;&gt;&gt; '.__('Counting persons and families and enter into database...').' ';
	// *** Calculate number of persons and families ***
	$person_qry=$dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
	$persons=$person_qry->rowCount();

	$family_qry=$dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='".$tree_id."'");
	$families=$family_qry->rowCount();

	$tree_date=date("Y-m-d H:i");
	$sql="UPDATE humo_trees SET
		tree_persons='".$persons."',
		tree_families='".$families."',
		tree_date='".$tree_date."'
		WHERE tree_prefix='".$_SESSION['tree_prefix']."'";
	$dbh->query($sql);


	// Show process time:
	$end_time=time();
	printf('<p>'.__('Processing took: %d seconds').'<br>', $end_time-$start_time);
	echo __('No error messages? In this case the database is ready for use!');

	if(CMS_SPECIFIC=="Joomla") {
		printf('<p><b>'.__('Ready! Now click %s to watch the family tree').'</b><br>', ' <a href="index.php?option=com_humo-gen&task=index">index.php</a> ');

		echo '<br><br><br><br><br><br><br><br><br>'; //make sure left menu won't run off bottom of screen if content is not long enough
	}
	else {
		printf('<p><b>'.__('Ready! Now click %s to watch the family tree').'</b><br>', ' <a href="'.CMS_ROOTPATH.'index.php">index.php</a> ');
		echo __('TIP: Use <a href="index.php?page=cal_date">"Calculated birth dates"</a> for a better privacy filter.');
	}

} // end of read gedcom (step 4)
?>