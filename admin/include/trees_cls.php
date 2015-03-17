<?php
class tree_cls{

// *** List of trees ***
function tree_main(){
	global $language, $dbh, $page, $language_tree, $language_select, $menu_admin, $tree_id;
	global $phpself, $phpself2, $joomlastring;

	echo '<br>';

	echo __('Administration of the family tree(s), i.e. the name can be changed here, and trees can be added or removed.').'<br>';

	// *** Read settings here to be shure radio buttons show proper values. ***
	include_once(CMS_ROOTPATH."include/settings_global.php"); // *** Read settings ***

	echo '<table class="humo" border="1" cellspacing="0" width="100%">';
	echo '<tr class="table_header"><th>'.__('Order').'</th>';
	echo '<th>'.__('Name of family tree').'</th>';
	echo '<th>'.__('Family tree data').'</th>';
	//echo '<th>'.__('Collation').'</th>';
	echo '<th>'.__('Remove').'</th>';
	echo '</tr>';

	echo '<tr class="table_header">';
	echo '<td></td>';
	echo '<td>';

		echo '<a href="index.php?'.$joomlastring.'page=tree&amp;language_tree=default&amp;tree_id='.$tree_id.'">'.__('Default').'</a> ';

		// *** Language choice ***
		for ($i=0; $i<count($language_select); $i++){
			// *** Get language name ***
			include(CMS_ROOTPATH.'languages/'.$language_select[$i].'/language_data.php'); //tb NOT INCLUDE_ONCE, DOES NOT CHECK PATH!
			echo '<a href="index.php?'.$joomlastring.'page=tree&amp;language_tree='.$language_select[$i].'&amp;tree_id='.$tree_id.'" style="border-right:none; background:none;">';
			echo '<img src="'.CMS_ROOTPATH.'languages/'.$language_select[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'"';

			if ($language_tree!=$language_select[$i]){
				echo ' style="border:none;"';
			}
			else{
				//echo ' style=" border: solid 2px #999999;"';
				echo ' style="border: solid 2px #000000;"';
			}

			echo '></a>';
			echo ' ';
		}
	echo '</td>';
	echo '<td></td>';
	//echo '<td></td>';
	echo '<td></td>';
	echo '</tr>';

	// *** New family tree number ***
	$new_number='1';
	$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
	if ($datasql){
	// *** Count lines in query ***
	$count_trees=$datasql->rowCount();
	while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
		$style=''; if ($tree_id==$dataDb->tree_id){ $style=' bgcolor="#99CCFF"'; }
		echo '<tr'.$style.'>';
		echo '<td nowrap>';
		if ($dataDb->tree_order<10){ echo '0'; }
		echo $dataDb->tree_order;
		// *** Number for new family tree ***
		$new_number=$dataDb->tree_order+1;
		if ($dataDb->tree_order!='1'){
			echo ' <a href="'.$phpself2.'page='.$page.'&amp;up=1&amp;tree_order='.$dataDb->tree_order.
			'&amp;id='.$dataDb->tree_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>'; }
		if ($dataDb->tree_order!=$count_trees){
			echo ' <a href="'.$phpself2.'page='.$page.'&amp;down=1&amp;tree_order='.$dataDb->tree_order.'&amp;id='.
			$dataDb->tree_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>'; }
		echo '</td>';

		echo '<td>';

		$treetext=show_tree_text($dataDb->tree_prefix, $language_tree);
		if ($dataDb->tree_prefix=='EMPTY')
			echo '* '.__('EMPTY LINE').' *';
		else{
			// *** Change family tree name ***
			//echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
			//	echo '<input type="hidden" name="page" value="'.$page.'">';
			//	echo '<input type="hidden" name="menu_admin" value="tree_text">';
			//	echo '<input type="hidden" name="tree_id" value="'.$dataDb->tree_id.'">';
			//	echo '<input type="Submit" name="submit" value="'.__('Select').'">';
			//echo '</form>';

			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_text&amp;tree_id='.$dataDb->tree_id.'"><img src="images/edit.jpg" title="edit" alt="edit"></a> '.$treetext['name'];
		}

	echo '</td>';

	echo '<td>';

		if ($dataDb->tree_prefix!='EMPTY'){
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_gedcom&amp;tree_id='.$dataDb->tree_id.'&tree_prefix='.$dataDb->tree_prefix.'&step1=read_gedcom"><img src="images/import.jpg" title="gedcom import" alt="gedcom import"></a>';
		}

		if ($dataDb->tree_prefix=='EMPTY'){
			//
		}
		elseif ($dataDb->tree_persons>0){ echo ' <font color="#00FF00"><b>'.__('OK').'</b></font>'; }
		else{ echo ' <font color="#FF0000"><b>'.__('ERROR').'!</b></font>'; }

		if ($dataDb->tree_prefix=='EMPTY'){
			//
		}
		elseif ($dataDb->tree_persons>0){
			// *** Show tree data ***
			$tree_date=$dataDb->tree_date;
			$month=''; // for empty tree_dates
			if (substr($tree_date,5,2)=='01'){ $month=' '.strtolower(__('jan')).' ';}
			if (substr($tree_date,5,2)=='02'){ $month=' '.strtolower(__('feb')).' ';}
			if (substr($tree_date,5,2)=='03'){ $month=' '.strtolower(__('mar')).' ';}
			if (substr($tree_date,5,2)=='04'){ $month=' '.strtolower(__('apr')).' ';}
			if (substr($tree_date,5,2)=='05'){ $month=' '.strtolower(__('may')).' ';}
			if (substr($tree_date,5,2)=='06'){ $month=' '.strtolower(__('jun')).' ';}
			if (substr($tree_date,5,2)=='07'){ $month=' '.strtolower(__('jul')).' ';}
			if (substr($tree_date,5,2)=='08'){ $month=' '.strtolower(__('aug')).' ';}
			if (substr($tree_date,5,2)=='09'){ $month=' '.strtolower(__('sep')).' ';}
			if (substr($tree_date,5,2)=='10'){ $month=' '.strtolower(__('oct')).' ';}
			if (substr($tree_date,5,2)=='11'){ $month=' '.strtolower(__('nov')).' ';}
			if (substr($tree_date,5,2)=='12'){ $month=' '.strtolower(__('oct')).' ';}
			$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4);
			echo ' <font size=-1>'.$tree_date.': '.$dataDb->tree_persons.' '.
			__('persons').', '.$dataDb->tree_families.' '.__('families').'</font>';
		}
		else{
			echo ' <b>'.__('This tree does not yet contain any data or has not been imported properly!').'</b>';
		}

	echo '</td>';

	/*
	echo '<td>';
		// *** Only show collation if family tree is made ***
		if ($dataDb->tree_prefix!='EMPTY' AND $dataDb->tree_persons>0){
			// ** Change collation of family tree (needed for Swedish etc.) ***
			//$collation_sql = $dbh->query("SHOW FULL COLUMNS
			//	FROM ".$dataDb->tree_prefix."person
			//	WHERE Field = 'pers_firstname'");
			$collation_sql = $dbh->query("SHOW FULL COLUMNS
				FROM humo_persons
				WHERE Field = 'pers_firstname'");
			$collationDb=$collation_sql->fetch(PDO::FETCH_OBJ);
			$collation=$collationDb->Collation;

			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				//echo '<input type="hidden" name="collation_prefix" value="'.$dataDb->tree_prefix.'">';
 
				//echo '<select size="1" name="tree_collation" style="width:150px;">';
				echo '<select size="1" name="tree_collation" style="width:150px;" onChange="this.form.submit();">';
					// *** Default collation ***
					echo '<option value="utf8_general_ci">utf8_general_ci (default)</option>';

					// *** Swedish collation ***
					$select=''; if ($collation=='utf8_swedish_ci'){ $select='selected'; }
					echo '<option value="utf8_swedish_ci"'.$select.'>utf8_swedish_ci</option>';

					// *** Danish collation ***
					$select=''; if ($collation=='utf8_danish_ci'){ $select='selected'; }
					echo '<option value="utf8_danish_ci"'.$select.'>utf8_danish_ci</option>';
				echo '</select>';

				//echo ' <input type="Submit" name="change_collation" value="OK">';
			echo '</form>';
		}
	echo '</td>';
	*/

	echo '<td nowrap>';
		// *** If there is only one family tree, prevent it can be removed ***
		if ($count_trees>1){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;remove_tree='.$dataDb->tree_id.'&amp;treetext_name='.$treetext['name'].'">';
			echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" alt="'.__('Remove tree').'" border="0"></a>';
		}
	echo '</td>';

	}

	echo '</tr>';
	}

	// *** Add new family tree ***

	// *** Find latest tree_prefix ***
	$found='1'; $i=1;
	while ($found=='1'){
		$new_tree_prefix='humo'.$i.'_';
		$datasql = $dbh->query("SELECT * FROM humo_trees
			WHERE tree_prefix='$new_tree_prefix'");
		$found=$datasql->rowCount();
		$i++;
	}

	echo '<tr><td colspan="4"><br></td></tr>';

	echo '<tr><td>';
		if ($new_number<10){ echo '0'; }
		echo $new_number.'</td>';
		echo '<td colspan="3">';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_order" value="'.$new_number.'">';
			//echo ' <b>'.__('Table prefix').':</b> <input type="text" name="tree_prefix" value="'.$new_tree_prefix.'" size="10">';
			echo '<input type="hidden" name="tree_prefix" value="'.$new_tree_prefix.'">';
			echo ' <input type="Submit" name="add_tree_data" value="'.__('Add family tree').'">';
			echo '</form>';
	echo '</td></tr>';

	echo '<tr><td colspan="4"><br></td></tr>';

	echo '<tr><td>';
	if ($new_number<10){ echo '0'; }
	echo $new_number.'</td>';
	echo '<td colspan="3">';
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="tree_order" value="'.$new_number.'">';
	echo ' <input type="Submit" name="add_tree_data_empty" value="'.__('Add empty line').'"> ';
	echo __('Add empty line in list of family trees');
	echo '</form>';
	echo '</td></tr>';

	echo "</table>";

	// *** Only show collation if family tree is made ***
	//if ($dataDb->tree_prefix!='EMPTY' AND $dataDb->tree_persons>0){
		// ** Change collation of family tree (needed for Swedish etc.) ***
		$collation_sql = $dbh->query("SHOW FULL COLUMNS
			FROM humo_persons
			WHERE Field = 'pers_firstname'");
		$collationDb=$collation_sql->fetch(PDO::FETCH_OBJ);
		$collation=$collationDb->Collation;

		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			//echo '<input type="hidden" name="collation_prefix" value="'.$dataDb->tree_prefix.'">';
			echo '<br>'.__('Collation').' ';
			//echo '<select size="1" name="tree_collation" style="width:150px;">';
			echo '<select size="1" name="tree_collation" style="width:250px;">';
				// *** Default collation ***
				echo '<option value="utf8_general_ci">utf8_general_ci (default)</option>';

				// *** Swedish collation ***
				$select=''; if ($collation=='utf8_swedish_ci'){ $select='selected'; }
				echo '<option value="utf8_swedish_ci"'.$select.'>utf8_swedish_ci</option>';

				// *** Danish collation ***
				$select=''; if ($collation=='utf8_danish_ci'){ $select='selected'; }
				echo '<option value="utf8_danish_ci"'.$select.'>utf8_danish_ci</option>';
			echo '</select>';

			echo ' <input type="Submit" name="change_collation" value="OK">';
		echo '</form>';
	//}
}

function tree_data(){
	global $language, $data2Db, $page, $menu_admin;
	global $phpself, $phpself2, $joomlastring;

	echo '<form method="post" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="tree_id" value="'.$data2Db->tree_id.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';

	echo '<br><table class="humo" cellspacing="0" width="100%" style="background-color : #CCFFFF;">';
		echo '<tr class="table_header"><th colspan="2">'.__('Family tree data').'</th></tr>';

		//echo '<tr><td>'.__('Table prefix').'</td><td>'.$data2Db->tree_prefix.'</td></tr>';

		echo  '<tr><td>'.__('E-mail address').'<br>'.__('Owner of tree').'</td>';
		echo '<td>'.__('E-mail address will not be shown on the site: an e-mail form will be generated!').'<br><input type="text" name="tree_email" value="'.$data2Db->tree_email.'" size="40"><br>';
		echo '<input type="text" name="tree_owner" value="'.$data2Db->tree_owner.'" size="40"></td></tr>';
		echo '<tr><td>'.__('Path to the pictures').'</td>';
			$data2Db->tree_pict_path.'</textarea></td></tr>';
		echo '<td>'.__('example: ../pictures/').'<br><textarea rows="1" cols="20" name="tree_pict_path" style="height: 20px; width:500px">'.
			$data2Db->tree_pict_path.'</textarea></td></tr>';

		// *** Family tree privacy ***
		echo '<tr><td>'.__('Tree privacy').':</td>';
		echo '<td>'.__('This option is valid for ALL persons in this tree!').'<br><select size="1" name="tree_privacy">';
		echo '<option value="standard">'.__('Standard').'</option>';
		$select=''; if ($data2Db->tree_privacy=='filter_persons'){ $select='selected'; }
		echo '<option value="filter_persons"'.$select.'>'.__('FILTER ALL persons').'</option>';
		$select=''; if ($data2Db->tree_privacy=='show_persons'){ $select='selected'; }
		echo '<option value="show_persons"'.$select.'>'.__('DISPLAY ALL persons').'</option>';
		echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Change').'</td><td><input type="Submit" name="change_tree_data" value="'.__('Change').'">';

		echo '</td></tr>';
	echo '</table>';
	echo '</form>';
}

function tree_text(){
	global $language, $page, $tree_id, $language_tree, $treetext_name, $language_select, $data2Db;
	global $treetext_mainmenu_text, $treetext_mainmenu_source, $treetext_family_top, $treetext_family_footer, $treetext_id, $menu_admin;
	global $phpself, $phpself2, $joomlastring;

	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
	echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
	echo '<input type="hidden" name="language_tree" value="'.$language_tree.'">';
	if (isset($treetext_id)){ echo '<input type="hidden" name="treetext_id" value="'.$treetext_id.'">'; }

	echo '<br><table class="humo" cellspacing="0" width="100%" style="background-color : #CCFFFF;">';
	echo '<tr class="table_header"><th colspan="2">'.__('Family tree texts (per language)').'</th></tr>';

	echo '<tr><td colspan="2">';
	echo __('Here you can add some overall texts for EVERY family tree (and for  EVERY LANGUAGE!).<br>Select language, and change text').'.<br>';
	echo __('Add "Default" (e.g. english) texts  for all languages, and/ or select a language to add texts for that specific language').':<br>';

	echo '<a href="index.php?'.$joomlastring.'page=tree&amp;menu_admin=tree_text&amp;language_tree=default&amp;tree_id='.$tree_id.'">'.__('Default').'</a> ';

	// *** Language choice ***
	for ($i=0; $i<count($language_select); $i++){
		// *** Get language name ***
		include(CMS_ROOTPATH.'languages/'.$language_select[$i].'/language_data.php');
		echo '<a href="index.php?'.$joomlastring.'page=tree&amp;menu_admin=tree_text&amp;language_tree='.$language_select[$i].'&amp;tree_id='.$tree_id.'" style="border-right:none; background:none;">';
		echo '<img src="'.CMS_ROOTPATH.'languages/'.$language_select[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'"';
		if ($language_tree!=$language_select[$i]){
			echo ' style="border:none;"';
		}
		else{
			echo ' style="border: solid 2px #999999;"';
		}
		echo '></a> ';
	}
	echo '</td></tr>';

	echo '<tr><td style="white-space:nowrap;"><b>'.__('Name of family tree').'</b></td><td><input type="text" name="treetext_name" value="'.$treetext_name.'" size="60"></td></tr>';

	echo '<tr><td style="white-space:nowrap;">'.__('Extra text in main menu').'</td>';
	echo '<td>';
	echo __('I.e. a website').': &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;<br>';
	echo '<textarea cols="60" rows="2" name="treetext_mainmenu_text">'.$treetext_mainmenu_text.'</textarea>';
	echo '</td>';

	echo '<tr><td style="white-space:nowrap;">'.__('Extra source in main menu').'</td>';
	echo '<td>';
	echo __(' I.e. a website').': &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;<br>';
	echo '<textarea cols="60" rows="2" name="treetext_mainmenu_source">'.$treetext_mainmenu_source.'</textarea>';
	echo '</td></tr>';

	echo '<tr><td style="white-space:nowrap;">'.__('Upper text family page').'</td>';
	echo '<td>'.__('I.e. Familypage').'<br>';
	echo '<textarea cols="60" rows="1" name="treetext_family_top">'.$treetext_family_top.'</textarea>';
	echo '</td></tr>';

	echo '<tr><td style="white-space:nowrap;">'.__('Lower text family page').'</td>';
	echo '<td>'.__('I.e.: For more information: &lt;a href="mailform.php"&gt;contact&lt;/a&gt;').'<br>';
	echo '<textarea cols="60" rows="1" name="treetext_family_footer">'.$treetext_family_footer.'</textarea>';
	echo '</td></tr>';

	if (isset($treetext_id)){
		echo '<tr><td>'.__('Change').'</td><td><input type="Submit" name="change_tree_text" value="'.__('Change').'">';
	}
	else{
		echo '<tr><td>'.__('Change').'</td><td><input type="Submit" name="add_tree_text" value="'.__('Change').'">';
	}

	echo '</table>';
	echo '</form>';
}

//**************************************************************************************
//******  tree_merge is the function that navigates all merge screens and options  *****
//**************************************************************************************
function tree_merge() {
	global $dbh, $data2Db, $phpself;
	global $page, $language, $tree_id, $menu_admin, $relatives_merge, $merge_chars;

	// check for stored settings and if not present set them
	$relatives_merge='';
	$qry = "SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'";
	$relmerge = $dbh->query($qry);
	if($relmerge->rowCount() >0) {
		$relmergeDb = $relmerge->fetch(PDO::FETCH_OBJ);
		$relatives_merge= $relmergeDb->setting_value;
	}
	else { // the rel_merge row didn't exist yet - make it, with empty value
		$dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('rel_merge_".$data2Db->tree_prefix."', '')");
	}
	$result = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_chars'");
	// get it
	if($result->rowCount() >0) { $resultDb = $result->fetch(PDO::FETCH_OBJ); $merge_chars = $resultDb->setting_value; }
	// or set it to default
	else { $result = $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('merge_chars', '10')");}

	$result = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_lastname'");
	if($result->rowCount() >0) { $resultDb = $result->fetch(PDO::FETCH_OBJ); $merge_lastname = $resultDb->setting_value; }
	else { $result = $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('merge_lastname', 'YES')");}

	$result = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_firstname'");
	if($result->rowCount() >0) { $resultDb = $result->fetch(PDO::FETCH_OBJ); $merge_firstname = $resultDb->setting_value; }
	else { $result = $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('merge_firstname', 'YES')");}

	$result = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_dates'");
	if($result->rowCount() >0) { $resultDb = $result->fetch(PDO::FETCH_OBJ); $merge_dates = $resultDb->setting_value; }
	else { $result = $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('merge_dates', 'YES')");}

	$result = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_parentsdate'");
	if($result->rowCount() >0) { $resultDb = $result->fetch(PDO::FETCH_OBJ); $merge_parentsdate = $resultDb->setting_value; }
	else { $result = $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('merge_parentsdate', 'YES')");}

	// the following creates the pages that cycle through all duplicates that are stored in the dupl_arr array
	// the pages themselves are presented with the "show_pair function"
	if(isset($_POST['duplicate_compare'])) {
		if(!isset($_POST['no_increase'])) {  // no increase is used if "switch left and right" was chosen
			$nr = ++$_SESSION['present_compare_'.$data2Db->tree_prefix]; // present_compare is the pair that has to be shown next - saved to session
		}
		else {
			$nr = $_SESSION['present_compare_'.$data2Db->tree_prefix];
		}
		if(isset($_POST['choice_nr'])) {  // choice number is the number from the "skip to" pulldown - saved to a session
			$nr = $_POST['choice_nr'];
			$_SESSION['present_compare_'.$data2Db->tree_prefix] = $_POST['choice_nr'];
		}
			// make sure the persons in the array are still there (in case in the mean time someone was merged)
			// after all, one person may be compared to more than one other person!

		while($_SESSION['present_compare_'.$data2Db->tree_prefix] < count($_SESSION['dupl_arr_'.$data2Db->tree_prefix])) {
			$comp_set = explode(';',$_SESSION['dupl_arr_'.$data2Db->tree_prefix][$nr]);
			//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$comp_set[0]."'";
			$qry = "SELECT * FROM humo_persons WHERE pers_id ='".$comp_set[0]."'";
			$res = $dbh->query($qry);
			//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$comp_set[1]."'";
			$qry = "SELECT * FROM humo_persons WHERE pers_id ='".$comp_set[1]."'";
			$res2 = $dbh->query($qry);
			if(!$res OR !$res2) { // one or 2 persons are missing - continue with next pair
				$nr = ++$_SESSION['present_compare_'.$data2Db->tree_prefix];
				continue; // look for next pair in array
			}
			else { // we have got a valid pair
				echo '<br'.__('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left".<br>
If you don\'t want to merge, press "SKIP" to continue to the next pair of possible duplicates').'<br><br>';

				$left = $comp_set[0];
				$right = $comp_set[1];
				if(isset($_POST['left'])) { $left = $_POST['left']; }
				if(isset($_POST['right'])) { $right = $_POST['right']; }

				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
				echo '</form>';

				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<input type="hidden" name="no_increase" value="1">';
				echo '<input type="hidden" name="left" value="'.$right.'">';
				echo '<input type="hidden" name="right" value="'.$left.'">';
				echo '<input type="Submit" name="duplicate_compare" value="'.__('<- Switch left and right ->').'">';
				echo '</form>';

				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<input type="Submit" name="duplicate_compare" value="'.__('Skip to next').'">';
				echo '</form>';

				echo '&nbsp;&nbsp;&nbsp;&nbsp;'.__('Skip to nr: ');
				echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<select style="max-width:60px" name="choice_nr">';
				for($x=0; $x < count($_SESSION['dupl_arr_'.$data2Db->tree_prefix]);$x++) {
					$selected='';
					if($x == $_SESSION['present_compare_'.$data2Db->tree_prefix]) { $selected = "SELECTED"; }
					echo '<option value="'.$x.'" '.$selected.'>'.($x+1).'</option>';
				}
				echo '</select>';
				echo '<input type="Submit" name="duplicate_compare" value="'.__('Go!').'">';
				echo '</form>';

				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
				echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
				echo '<input type="hidden" name="dupl" value="1">';
				echo '<input type="Submit" name="merge" value="'.__('Merge right into left').'">';

				echo '<br><br>';
				$this->show_pair($left,$right,'duplicate');
				echo '<br>';

				echo '</form>';

				break; // get out of the while loop. next loop will be called by skip or merge buttons
			}
		}

		if($_SESSION['present_compare_'.$data2Db->tree_prefix] >= count($_SESSION['dupl_arr_'.$data2Db->tree_prefix])) {
			unset($_SESSION['present_compare_'.$data2Db->tree_prefix]);
			echo '<br><br>'.__('No more duplicates found').'<br><br>';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
			echo '</form>';
		}
	}

	// this creates the screen for manual merge. the pair itself is presented with the "show_pair" function
	elseif(isset($_POST['manual_compare'])) {

		// check if persons are of opposite sex - if so don't continue
		//$qry1= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$_POST['left']."'";  // left person
		$qry1= "SELECT * FROM humo_persons WHERE pers_id ='".$_POST['left']."'";  // left person
		$per1 = $dbh->query($qry1);
		$per1Db = $per1->fetch(PDO::FETCH_OBJ);
		//$qry2= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$_POST['right']."'";  // right person
		$qry2= "SELECT * FROM humo_persons WHERE pers_id ='".$_POST['right']."'";  // right person
		$per2 = $dbh->query($qry2);
		$per2Db = $per2->fetch(PDO::FETCH_OBJ);
		if($per1Db->pers_sexe != $per2Db->pers_sexe) { // trying to merge opposite sexes
			echo '<br>'.__('You cannot merge persons of opposite sex. Please try again').'.<br><br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="manual" value="'.__('Choose another pair').'">';
			echo '</form>';
		}
		elseif($per1Db->pers_gedcomnumber == $per2Db->pers_gedcomnumber) { // trying to merge same person!!
			echo '<br>'.__('This is one person already - you can\'t merge! Please try again').'<br><br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="manual" value="'.__('Choose another pair').'">';
			echo '</form>';
		}
		else {
			echo '<br>'.__('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left"').'.<br>';
			echo __('The checked items will be the ones entered into the database for the merged person. You can change the default settings').'<br>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
			echo '</form>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="left" value="'.$_POST['right'].'">';
			echo '<input type="hidden" name="right" value="'.$_POST['left'].'">';
			echo '<input type="Submit" name="manual_compare" value="'.__('<- Switch left and right ->').'">';
			echo '</form>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="manual" value="'.__('Choose another pair').'">';
			echo '</form>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="manu" value="1">';
			echo '<input type="hidden" name="left" value="'.$_POST['left'].'">';
			echo '<input type="hidden" name="right" value="'.$_POST['right'].'">';
			echo '<input type="Submit" name="merge" value="'.__('Merge right into left').'">';

			echo '<br><br>';
			$this->show_pair($_POST['left'],$_POST['right'],'manual');
			echo '<br>';

			echo '</form>';
		}
	}

	// this creates the pages that cycle through the surrounding relatives that have to be checked for merging
	// the "surrounding relatives" array is created in all merge modes (in the merge_them function) )and saved to the database
	elseif(isset($_POST['relatives'])) {

		// if skip - delete pair from database string
		if(isset($_POST['skip_rel'])) {
			// remove first entry (that the admin decided not to merge) from string
			$relcomp = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
			$relcompDb = $relcomp->fetch(PDO::FETCH_OBJ);		// database row: I23@I300;I54@I304;I34@I430;
			$firstsemi = strpos($relcompDb->setting_value,';') + 1;
			$string = substr($relcompDb->setting_value,$firstsemi);
			$dbh->query("UPDATE humo_settings SET setting_value ='".$string."' WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
			$relatives_merge = $string;
		}

		// merge
		if(isset($_POST['rela'])) {  // the merge button was used
			$left = $_POST['left'];
			$right = $_POST['right'];
			$this->merge_them($left,$right,"relatives");
		}

		$relcomp = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
		$relcompDb = $relcomp->fetch(PDO::FETCH_OBJ);		// database row: I23@I300;I54@I304;I34@I430;

		if($relcompDb->setting_value != '') {
			if(!isset($_POST['swap'])) {
				$allpairs = explode(';',$relcompDb->setting_value);  // $allpairs[0]:  I23@I300
				$pair = explode('@',$allpairs[0]); // $pair[0]:  I23;
				$lft = $pair[0];  // I23
				$rght = $pair[1]; // I300
				//$leftqry = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$lft."'");
				$leftqry = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$lft."'");
				$leftDb = $leftqry->fetch(PDO::FETCH_OBJ);
				$left = $leftDb->pers_id;
				//$rightqry = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$rght."'");
				$rightqry = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$rght."'");
				$rightDb = $rightqry->fetch(PDO::FETCH_OBJ);
				$right = $rightDb->pers_id;
			}
			else {  // "switch left-right" button used"
				$left = $_POST['left'];
				$right = $_POST['right'];
			}
			echo '<br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
			echo '</form>';

			// button skip
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="skip_rel" value="1">';
			echo '<input type="Submit" name="relatives" value="'.__('Skip to next').'">';
			echo '</form>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="swap" value="1">';
			echo '<input type="hidden" name="left" value="'.$right.'">';
			echo '<input type="hidden" name="right" value="'.$left.'">';
			echo '<input type="Submit" name="relatives" value="'.__('<- Switch left and right ->').'">';
			echo '</form>';

			// button merge
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="hidden" name="left" value="'.$left.'">';
			echo '<input type="hidden" name="right" value="'.$right.'">';
			echo '<input type="hidden" name="rela" value="1">';
			echo '<input type="Submit" name="relatives" value="'.__('Merge right into left').'">';
			echo '<br><br>';
			$this->show_pair($left,$right,'relatives');
			echo '<br>';
			echo '</form>';
		}
		else {
			echo '<br><br>'.__('No more surrounding relatives to check').'.<br><br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
			echo '</form>';
		}
	}

	// this is called up by the "Merge" button in manual and duplicate merge modes
	elseif(isset($_POST['merge'])) { // do merge and allow to continue with comparing duplicates

		if(isset($_POST['manu'])) {
			$left = $_POST['left'];
			$right = $_POST['right'];
			$this->merge_them($left,$right,"man_dupl"); // merge_them is called in manual/duplicate mode
		}
		elseif(isset($_POST['dupl'])) { // duplicate merging
			$nr = $_SESSION['present_compare_'.$data2Db->tree_prefix];
			$comp_set = explode(';',$_SESSION['dupl_arr_'.$data2Db->tree_prefix][$nr]);
			$left = $comp_set[0];
			$right = $comp_set[1];
			$this->merge_them($left,$right,"man_dupl"); // merge_them is called in manual/duplicate mode
		}
	}

	//This is called when you push the "Duplicate merge option on the main merge screen.
	// It gives an explanation and also offers to continue with previous dupl merge, if one was already done in this session
	elseif(isset($_POST['duplicate_choices'])) {

		echo '<br>';
		echo __('With "Duplicate merge" the program will look for all persons with a fixed set of criteria for identical data.
These are:
<ul><li>Same last name and same first name.<br>
By default, people with blank first or last names are included. You can disable that under "Settings" in the main menu.</li>
<li>Same birthdate or same deathdate.<br>
By default, when one or both persons have a missing birth/death date they will still be included when the name matches.
You can change that under "Settings" in the main menu.</li></ul>
The found duplicates will be presented to you, one pair after the other, with their details.<br>
You can then decide whether to accept the default merge, or change which details of the right person will be merged into the left.<br>
If you decide not to merge this pair, you can "skip" to the next pair.<br>
If after the merge there are surrounding relatives that might need merging too, you will be urged to move to "Relatives merge"<br>
If you have interrupted a duplicate merge in this session (for example to move to "relatives merge"),
this page will also show a "Continue duplicate merge" button so you can continue where you left off.<br>
<b>Please note that generating the duplicates may take some time, depending on the size of the tree.</b>');

		echo '<br><br>';
		if(isset($_SESSION['dupl_arr_'.$data2Db->tree_prefix])) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" style="min-width:150px" name="duplicate_compare" value="'.__('Continue duplicate merge').'">';
			echo '</form>';
		}

		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" style="min-width:150px" name="duplicate" value="'.__('Generate new duplicate merge').'">';
		echo '</form>';

		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
		echo '</form>';

	}

	// this is called when the "duplicate merge" button is used on the duplicate_choices page
	// it creates the dupl_arr array with all duplicates found
	elseif(isset($_POST['duplicate'])) {
		echo __('Please wait while duplicate list is generated').'<br>';
		//$qry = "SELECT pers_id,pers_firstname,pers_lastname, pers_birth_date, pers_death_date FROM ".$data2Db->tree_prefix."person ORDER BY pers_id";
		$qry = "SELECT pers_id,pers_firstname,pers_lastname, pers_birth_date, pers_death_date
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY pers_id";
		$pers = $dbh->query($qry);
		unset($dupl_arr); // just to make sure...
		while($persDb = $pers->fetch(PDO::FETCH_OBJ)) {
		// the exact phrasing of the query depends on the admin settings
			//$qry2 = "SELECT pers_id,pers_firstname,pers_lastname, pers_birth_date, pers_death_date	FROM ".$data2Db->tree_prefix."person
			//	WHERE pers_id > ".$persDb->pers_id;
			$qry2 = "SELECT pers_id,pers_firstname,pers_lastname, pers_birth_date, pers_death_date
				FROM humo_persons WHERE pers_id > ".$persDb->pers_id;
			if($merge_firstname == 'YES') {
				$qry2 .= " AND SUBSTR(pers_firstname,1,".$merge_chars.") = SUBSTR('".$persDb->pers_firstname."',1,".$merge_chars.")";
			}
			else {
				$qry2 .= " AND pers_firstname != '' AND SUBSTR(pers_firstname,1,".$merge_chars.") = SUBSTR('".$persDb->pers_firstname."',1,".$merge_chars.")";
			}
			if($merge_lastname == 'YES') {
				$qry2 .= " AND pers_lastname ='".$persDb->pers_lastname."' ";
			}
			else {
				$qry2 .= " AND pers_lastname != '' AND pers_lastname ='".$persDb->pers_lastname."' ";
			}

			if($merge_dates == "YES") {
				$qry2 .= " AND (pers_birth_date ='".$persDb->pers_birth_date."' OR pers_birth_date ='' OR '".$persDb->pers_birth_date."'='') ";
				$qry2 .= " AND (pers_death_date ='".$persDb->pers_death_date."' OR pers_death_date ='' OR '".$persDb->pers_death_date."'='') ";
			}
			else {
				$qry2 .= " AND (( pers_birth_date != '' AND pers_birth_date ='".$persDb->pers_birth_date."' AND !(pers_death_date != '".$persDb->pers_death_date."'))
					OR
					(  pers_death_date != '' AND pers_death_date ='".$persDb->pers_death_date."' AND !(pers_birth_date != '".$persDb->pers_birth_date."')) )";
			}

			//$pers2 = mysql_query($qry2,$db);
			$pers2 = $dbh->query($qry2);

			if($pers2) {
				while($pers2Db = $pers2->fetch(PDO::FETCH_OBJ)) {
						$dupl_arr[]= $persDb->pers_id.';'.$pers2Db->pers_id;
				}
			}
		}
		if(isset($dupl_arr)) {
			$_SESSION['dupl_arr_'.$data2Db->tree_prefix]= $dupl_arr;
			$_SESSION['present_compare_'.$data2Db->tree_prefix]= -1;
			echo '<br>'.__('Possible duplicates found: ').count($dupl_arr).'<br><br>'; // possible duplicates found
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="duplicate_compare" value="'.__('Start comparing duplicates').'">'; // start comparing duplicates
			echo '</form>';
		}
		else {
			echo '<br>'.__('No duplicates found. Duplicate merge and Automatic merge won\'t result in merges!').'<br>'; // no duplicates were found
			echo __('You can try one of the other merge options').'<br><br>'; // try other options

			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
			echo '</form>';
		}
	}

	// this is the page where one can choose two people from all persons in the tree for manual merging
	// the pairs will be presented by the show_pair function
	elseif(isset($_POST['manual']) OR isset($_POST["search1"]) OR isset($_POST["search2"]) OR isset($_POST["switch"])) {

		echo '<br>'.__('Pick the two persons you want to check for merging').'.';
		echo ' '.__('You can enter names (or part of names) or gedcom no. (INDI), or leave boxes empty').'<br>';
		echo __('<b>TIP: when you click "search" with all boxes left empty you will get a list with all persons in the database. (May take a few seconds)</b>').'<br><br>';

		// ===== BEGIN SEARCH BOX SYSTEM

		include_once(CMS_ROOTPATH."include/person_cls.php");
		$pers_cls = New person_cls;

		if(!isset($_POST["search1"]) AND !isset($_POST["search2"]) AND !isset($_POST["manual_compare"]) AND !isset($_POST["switch"])) {
			// no button pressed: this is a fresh entry from humogen's frontpage link: start clean search form
			$_SESSION["search1"]=''; $_SESSION["search2"]='';
			$_SESSION['rel_search_firstname']=''; $_SESSION['rel_search_lastname']='';
			$_SESSION['rel_search_firstname2']=''; $_SESSION['rel_search_lastname2']='';
			$_SESSION['search_indi']=''; $_SESSION['search_indi2']='';
		}

		$left=''; if (isset($_POST["left"])){	$left=$_POST['left']; }
		$right=''; if (isset($_POST["right"])){ $right=$_POST['right']; }
		if (isset($_POST["search1"])){ $_SESSION["search1"]=1; }
		if (isset($_POST["search2"])){ $_SESSION["search2"]=1; }

		if(isset($_POST["switch"])) {
			$temp=$_SESSION['rel_search_firstname']; $_SESSION['rel_search_firstname']=$_SESSION['rel_search_firstname2']; 	$_SESSION['rel_search_firstname2']=$temp;
			$temp=$_SESSION['rel_search_lastname'];  $_SESSION['rel_search_lastname']=$_SESSION['rel_search_lastname2'];   $_SESSION['rel_search_lastname2']=$temp;
			$temp=$_SESSION['search_indi'];  $_SESSION['search_indi']=$_SESSION['search_indi2'];   $_SESSION['search_indi2']=$temp;	
			$temp=$left; $left=$right; $right=$temp;
			$temp=$_SESSION["search1"]; $_SESSION["search1"]=$_SESSION["search2"]; $_SESSION["search2"]=$temp;
		}
		// if joomla component will be continued the following line has to be adjusted for joomla
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<table class="humo" style="text-align:center; width:100%; background-color:#CCFFFF;">';
		echo '<tr><td>';
		echo '&nbsp;';
		echo '</td><td>';
		echo __('First name').':';
		echo '</td><td>';
		echo __('Last name').':';
		echo '</td><td>';
		echo __('gedcom no. ("I43")').':';
		echo '</td><td>';
		echo __('Search');
		echo '</td><td colspan=2>'.__('Pick a name from search results').'</td><td>';
		echo __('Show details');

		echo '</td></tr><tr><td style="white-space:nowrap">';
		$language_person=__('Person').' ';
		if(CMS_SPECIFIC == "Joomla") { $language_person=''; }  // for joomla keep it short....
		echo $language_person.'1:';
		echo '</td><td>';

		$search_firstname='';
		if (isset($_POST["search_firstname"]) AND !isset($_POST["switch"])){
			$search_firstname=safe_text($_POST['search_firstname']);
			$_SESSION['rel_search_firstname']=$search_firstname;
		}
		if (isset($_SESSION['rel_search_firstname'])){ $search_firstname=$_SESSION['rel_search_firstname']; }

		$search_lastname='';
		if (isset($_POST["search_lastname"]) AND !isset($_POST["switch"])){
			$search_lastname=safe_text($_POST['search_lastname']);
			$_SESSION['rel_search_lastname']=$search_lastname;
		}
		if (isset($_SESSION['rel_search_lastname'])){ $search_lastname=$_SESSION['rel_search_lastname']; }

		$search_indi='';
		if (isset($_POST["search_indi"]) AND !isset($_POST["switch"])){
			$search_indi=safe_text($_POST['search_indi']);
			$_SESSION['search_indi']=$search_indi;
		}
		if (isset($_SESSION['search_indi'])){ $search_indi=$_SESSION['search_indi']; }

		echo ' <input type="text" class="fonts relboxes" name="search_firstname" value="'.$search_firstname.'" size="15"> ';
		echo '</td><td>';

		echo '&nbsp; <input class="fonts relboxes" type="text" name="search_lastname" value="'.$search_lastname.'" size="15">';
		echo '</td><td>';
		echo ' <input type="text" class="fonts relboxes" name="search_indi" value="'.$search_indi.'" size="10"> ';
		echo '</td><td>';
		echo '&nbsp; <input class="fonts" type="submit" name="search1" value="'.__('Search').'">';
		echo '</td><td>';

		$len=230;  // length of name pulldown box
		if(CMS_SPECIFIC == "Joomla") { $len = 180; } // for joomla keep it short....

		if(isset($_SESSION["search1"]) AND $_SESSION["search1"]==1) {
			$indi_string="";
			if(isset($_SESSION["search_indi"]) AND $_SESSION["search_indi"] != "") { 
				// make sure it works with "I436", "i436" and "436"
				$indi = (substr($search_indi,0,1)=="I" OR substr($search_indi,0,1)=="i") ? strtoupper($search_indi) : "I".$search_indi;
				$indi_string = " AND pers_gedcomnumber ='".$indi."' ";
			}  
			//$search_qry= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)";
			$search_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)";
			$search_qry.= " LIKE '%".$search_lastname."%' AND pers_firstname LIKE '%".$search_firstname."%' ".$indi_string." ORDER BY pers_lastname, pers_firstname";
			$search_result = $dbh->query($search_qry);
			if ($search_result){ 
				if($search_result->rowCount() >0) {
					echo '<select class="fonts" size="1" name="left"  style="width:'.$len.'px">';
						while ($searchDb=$search_result->fetch(PDO::FETCH_OBJ)){
							$name=$pers_cls->person_name($searchDb);
							if ($name["show_name"]){
								echo '<option';
								if(isset($left)) {
									if ($searchDb->pers_id==$left AND !(isset($_POST["search1"]) AND $search_lastname=='' AND $search_firstname=='')){
										echo ' SELECTED';
									}
								}
								echo ' value="'.$searchDb->pers_id.'">'.$name["index_name"].' ['.$searchDb->pers_gedcomnumber.']</option>';
							}
						}
						echo '</select>';
				}
				else {echo '<select size="1" name="notfound" value="1" style="width:'.$len.'px"><option>'.__('Person not found').'</option></select>'; }
			}
		}
		else {  echo '<select size="1" name="left" style="width:'.$len.'px"><option></option></select>'; }
		echo '</td><td rowspan=2>';
		echo '<input type="submit" alt="'.__('Switch persons').'" title="'.__('Switch persons').'" value=" " name="switch" style="background: #fff url(\''.CMS_ROOTPATH.'images/turn_around.gif\') top no-repeat;width:25px;height:25px">';
		echo '</td><td rowspan=2>';
		echo '<input type="submit" name="manual_compare" value="'.__('Show details').'" style="font-size:115%;">';
		echo '</td></tr><tr><td  style="white-space:nowrap">';

		// SECOND PERSON
		echo $language_person.'2:';
		echo '</td><td>';

		$search_firstname2='';
		if (isset($_POST["search_firstname2"]) AND !isset($_POST["switch"])){
			$search_firstname2=safe_text($_POST['search_firstname2']);
			$_SESSION['rel_search_firstname2']=$search_firstname2;
		}
		if (isset($_SESSION['rel_search_firstname2'])){ $search_firstname2=$_SESSION['rel_search_firstname2']; }

		$search_lastname2='';
		if (isset($_POST["search_lastname2"]) AND !isset($_POST["switch"])){
			$search_lastname2=safe_text($_POST['search_lastname2']);
			$_SESSION['rel_search_lastname2']=$search_lastname2;
		}
		if (isset($_SESSION['rel_search_lastname2'])){ $search_lastname2=$_SESSION['rel_search_lastname2']; }

		$search_indi2='';
		if (isset($_POST["search_indi2"]) AND !isset($_POST["switch"])){
			$search_indi2=safe_text($_POST['search_indi2']);
			$_SESSION['search_indi2']=$search_indi2;
		}
		if (isset($_SESSION['search_indi2'])){ $search_indi2=$_SESSION['search_indi2']; }

		echo ' <input type="text" class="fonts relboxes" name="search_firstname2" value="'.$search_firstname2.'" size="15"> ';
		echo '</td><td>';
		echo '&nbsp; <input class="fonts relboxes" type="text" name="search_lastname2" value="'.$search_lastname2.'" size="15">';
		echo '</td><td>';
		echo ' <input type="text" class="fonts relboxes" name="search_indi2" value="'.$search_indi2.'" size="10"> ';
		echo '</td><td>';
		echo '&nbsp; <input class="fonts" type="submit" name="search2" value="'.__('Search').'">';
		echo '</td><td>';

		if(isset($_SESSION["search2"]) AND $_SESSION["search2"]==1) {
			$indi_string2="";
			if(isset($_SESSION["search_indi2"]) AND $_SESSION["search_indi2"] != "") { 
				// make sure it works with "I436", "i436" and "436"
				$indi2 = (substr($search_indi2,0,1)=="I" OR substr($search_indi2,0,1)=="i") ? strtoupper($search_indi2) : "I".$search_indi2;
				$indi_string2 = " AND pers_gedcomnumber ='".$indi2."' ";
			}  
			//$search_qry= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)";
			$search_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)";
			$search_qry.= " LIKE '%".$search_lastname2."%' AND pers_firstname LIKE '%".$search_firstname2."%' ".$indi_string2." ORDER BY pers_lastname, pers_firstname";
			$search_result2 = $dbh->query($search_qry);
			if ($search_result2){
				if($search_result2->rowCount() >0) {
					echo '<select class="fonts" size="1" name="right" style="width:'.$len.'px">';
					while ($searchDb2=$search_result2->fetch(PDO::FETCH_OBJ)){
						$name=$pers_cls->person_name($searchDb2);
						if ($name["show_name"]){
							echo '<option';
							if(isset($right)) {
								if ($searchDb2->pers_id==$right AND !(isset($_POST["search2"]) AND $search_lastname2=='' AND $search_firstname2=='')){
									echo ' SELECTED';
								}
							}
							echo ' value="'.$searchDb2->pers_id.'">'.$name["index_name"].' ['.$searchDb2->pers_gedcomnumber.']</option>';
						}
					}
					echo '</select>';
				}
				else { echo '<select size="1" name="notfound" value="1" style="width:'.$len.'px"><option>'.__('Person not found').'</option></select>'; }
			}
		}
		else { echo '<select size="1" name="right" style="width:'.$len.'px"><option></option></select>'; }
		echo '</td></tr></table>';
		echo '</form>';

		// ===== END SEARCH BOX SYSTEM

	}

	// this is the screen that will show when you choose "automatic merge" from the main merge page
	elseif(isset($_POST['automatic'])) {

		echo '<br>';
		echo __('Automatic merge will go through the entire database and merge all persons who comply with ALL the following conditions:<br>
<ul><li>Both persons have a first name and a last name and they are identical</li>
<li>Both persons have parents with first and last names and those names are identical</li>
<li>Both persons\' parents have a marriage date and it is identical (This can be disabled under "Settings")</li>
<li>Both persons have a birth date and it is identical OR both have a death date and it is identical</li></ul>
<b>Please note that the automatic merge may take quite some time, depending on the size of the database and the number of merges.</b><br>
You will be notified of results as the action is completed');
		echo '<br><br>';


		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" name="auto_merge" value="'.__('Perform automatic merge').'">';
		echo '</form>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
		echo '</form>';
	}

	// this checks the persons that can be merged automatically and merges them with the "merge_them" function
	elseif(isset($_POST['auto_merge'])) {
			echo '<br>'.__('Please wait while the automatic merges are processed...').'<br>';
			$merges=0;
			/*
			$qry= "SELECT pers_id, pers_lastname, pers_firstname, pers_birth_date, pers_death_date, pers_famc   FROM ".$data2Db->tree_prefix."person WHERE
				pers_lastname !='' AND
				pers_firstname !='' AND
				(pers_birth_date !='' OR pers_death_date !='') AND
				pers_famc !=''
				ORDER BY pers_id";
			*/
			$qry= "SELECT pers_id, pers_lastname, pers_firstname, pers_birth_date, pers_death_date, pers_famc
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND
				pers_lastname !='' AND
				pers_firstname !='' AND
				(pers_birth_date !='' OR pers_death_date !='') AND
				pers_famc !=''
				ORDER BY pers_id";
			$pers = $dbh->query($qry);
			//(pers_firstname !='' AND SUBSTR(pers_firstname,1,".$merge_chars.") = SUBSTR(".$persDb->pers_firstname.",1,".$merge_chars.")) AND
			while($persDb = $pers->fetch(PDO::FETCH_OBJ)) {
				$qry2 = "SELECT pers_id, pers_lastname, pers_firstname, pers_birth_date, pers_death_date, pers_famc  FROM humo_persons WHERE
				pers_tree_id='".$tree_id."' AND
				pers_id > ".$persDb->pers_id." AND
				(pers_lastname !='' AND pers_lastname = '".$persDb->pers_lastname."') AND
				(pers_firstname !='' AND pers_firstname = '".$persDb->pers_firstname."') AND
				((pers_birth_date !='' AND pers_birth_date ='".$persDb->pers_birth_date."') OR
				(pers_death_date !='' AND pers_death_date ='".$persDb->pers_death_date."')) AND
				pers_famc !=''
				ORDER BY pers_id";

				$pers2 = $dbh->query($qry2);
				if($pers2) {
					while($pers2Db = $pers2->fetch(PDO::FETCH_OBJ)) {
						// get the two families
						//$qry = "SELECT fam_man, fam_woman, fam_marr_date FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber='".$persDb->pers_famc."'";
						$qry = "SELECT fam_man, fam_woman, fam_marr_date FROM humo_families
							WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$persDb->pers_famc."'";
						$fam1 = $dbh->query($qry);
						$fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
						//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber='".$pers2Db->pers_famc."'";
						$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$pers2Db->pers_famc."'";
						$fam2 = $dbh->query($qry);
						$fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
						if($fam1->rowCount() > 0 AND $fam2->rowCount() > 0) {
							$go = 1;
							if($merge_parentsdate=='YES') { // we want to check for wedding date of parents
								if($fam1Db->fam_marr_date!='' AND $fam1Db->fam_marr_date == $fam2Db->fam_marr_date) {
									$go=1;
								}
								else { $go = 0;  // no wedding date or no match --> no merge!
								}
							}

							if($go) {
								// no use doing all this if the marriage date doesn't match
								//$qry = "SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber='".$fam1Db->fam_man."'";
								$qry = "SELECT pers_lastname, pers_firstname FROM humo_persons
									WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam1Db->fam_man."'";
								$fath1 = $dbh->query($qry);
								$fath1Db = $fath1->fetch(PDO::FETCH_OBJ);
								//$qry = "SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber='".$fam1Db->fam_woman."'";
								$qry = "SELECT pers_lastname, pers_firstname FROM humo_persons
									WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam1Db->fam_woman."'";
								$moth1 = $dbh->query($qry);
								$moth1Db = $moth1->fetch(PDO::FETCH_OBJ);

								//$qry = "SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber='".$fam2Db->fam_man."'";
								$qry = "SELECT pers_lastname, pers_firstname FROM humo_persons
									WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam2Db->fam_man."'";
								$fath2 = $dbh->query($qry);
								$fath2Db = $fath2->fetch(PDO::FETCH_OBJ);
								//$qry = "SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber='".$fam2Db->fam_woman."'";
								$qry = "SELECT pers_lastname, pers_firstname FROM humo_persons
									WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam2Db->fam_woman."'";
								$moth2 = $dbh->query($qry);
								$moth2Db = $moth2->fetch(PDO::FETCH_OBJ);
								if($fath1->rowCount()>0 AND $moth1->rowCount()>0 AND $fath2->rowCount()>0 AND $moth2->rowCount()>0) {
									if($fath1Db->pers_lastname!='' AND $fath1Db->pers_lastname == $fath2Db->pers_lastname
										AND $moth1Db->pers_lastname!='' AND $moth1Db->pers_lastname == $moth2Db->pers_lastname
										AND $fath1Db->pers_firstname!='' AND $fath1Db->pers_firstname == $fath2Db->pers_firstname
										AND $moth1Db->pers_firstname!='' AND $moth1Db->pers_firstname == $moth2Db->pers_firstname	)
									{

										// MERGE THEM !!
										$this->merge_them($persDb->pers_id,$pers2Db->pers_id,'automatic');
										$mergedlist[]=$persDb->pers_id;
										$merges++;

									}
								}
							} 	// end "if($go)"
						}
					}	// end while
				} // end "if($pers2)

			}

		if($merges == 0) {
			echo '<br>'.__('No automatic merge options were found.').'<br><br>';
		}
		else {
			echo '<br>'.__('Automatic merge completed').' '.$merges.__(' merges were performed').'<br><br>';
		}
		if($relatives_merge != '') {
			echo __('It is recommended to continue with <b>"Relatives merge"</b> to consider merging persons affected by previous merges that were performed.').'<br><br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" style="font-weight:bold;font-size:120%" name="relatives" value="'.__('Relatives merge').'">';
			echo '</form>';
		}
		else {
 			echo __('You may wish to proceed with duplicate merge or manual merge.').'<br><br>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="duplicate_choices" value="'.__('Duplicate merge').'">';
			echo '</form>';
 			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" name="manual" value="'.__('Manual merge').'">';
			echo '</form>';
		}
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" value="'.__('Back to main merge menu').'">';
		echo '</form>';

		if(isset($mergedlist)) { // there is a list of merged persons
			echo '<br><br><b><u>'.__('These are the persons that were merged:').'</u></b><br>';
			for($i=0;$i<count($mergedlist);$i++) {
				//$result=$dbh->query("SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id='".$mergedlist[$i]."'");
				$result=$dbh->query("SELECT * FROM humo_persons WHERE pers_id='".$mergedlist[$i]."'");
				$resultDb=$result->fetch(PDO::FETCH_OBJ);
				echo $resultDb->pers_lastname.', '.$resultDb->pers_firstname.' '.strtolower(str_replace("_"," ",$resultDb->pers_prefix)).' (#'.$resultDb->pers_gedcomnumber.')<br>';
			}
		}
	}

	// The settings screen with "Save" and "Reset" buttons and explanations
	elseif(isset($_POST['settings']) OR isset($_POST['reset'])) {
		echo '<br>';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';

		if(isset($_POST['reset'])) { // reset to default
				$dbh->query("UPDATE humo_settings SET setting_value ='10' WHERE setting_variable='merge_chars'");
		}
		elseif(isset($_POST['merge_chars'])) { // the "Save" button was pressed
			$merge_chars = $_POST['merge_chars'];  // store into variable and write to database
			$dbh->query("UPDATE humo_settings SET setting_value ='".$merge_chars."' WHERE setting_variable = 'merge_chars'");
		}
		$chars = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_chars'");
		$charsDb = $chars->fetch(PDO::FETCH_OBJ);

		if(isset($_POST['reset'])) {
				$dbh->query("UPDATE humo_settings SET setting_value ='YES' WHERE setting_variable='merge_dates'");
		}
		elseif(isset($_POST['merge_dates'])) {
			$merge_dates = $_POST['merge_dates'];
			$dbh->query("UPDATE humo_settings SET setting_value ='".$merge_dates."' WHERE setting_variable = 'merge_dates'");
		}
		$dates = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_dates'");
		$datesDb = $dates->fetch(PDO::FETCH_OBJ);

		if(isset($_POST['reset'])) {
				$dbh->query("UPDATE humo_settings SET setting_value ='YES' WHERE setting_variable='merge_lastname'");
		}
		elseif(isset($_POST['merge_lastname'])) {
			$merge_lastname = $_POST['merge_lastname'];
			$dbh->query("UPDATE humo_settings SET setting_value ='".$merge_lastname."' WHERE setting_variable = 'merge_lastname'");
		}
		$lastn = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_lastname'");
		$lastnDb = $lastn->fetch(PDO::FETCH_OBJ);

		if(isset($_POST['reset'])) {
				$dbh->query("UPDATE humo_settings SET setting_value ='YES' WHERE setting_variable='merge_firstname'");
		}
		elseif(isset($_POST['merge_firstname'])) {
			$merge_firstname = $_POST['merge_firstname'];
			$dbh->query("UPDATE humo_settings SET setting_value ='".$merge_firstname."' WHERE setting_variable = 'merge_firstname'");
		}
		$firstn = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_firstname'");
		$firstnDb = $firstn->fetch(PDO::FETCH_OBJ);

		if(isset($_POST['reset'])) {
				$dbh->query("UPDATE humo_settings SET setting_value ='YES' WHERE setting_variable='merge_parentsdate'");
		}
		elseif(isset($_POST['merge_parentsdate'])) {
			$merge_parentsdate = $_POST['merge_parentsdate'];
			$dbh->query("UPDATE humo_settings SET setting_value ='".$merge_parentsdate."' WHERE setting_variable = 'merge_parentsdate'");
		}
		$pard = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'merge_parentsdate'");
		$pardDb = $pard->fetch(PDO::FETCH_OBJ);

		echo '<table class="humo" style="width:900px; background-color:#CCFFFF;">';
		echo '<tr class="table_header"><th colspan="3">'.__('Merge filter settings').'</th></tr>';
		echo '<tr><th style="width:300px" colspan="2">'.__('Settings').'</th><th style="width:600px">'.__('Explanation').'</th></tr>';
		echo '<tr><td style="font-weight:bold;text-align:left;vertical-align:top" colspan="3">';

		echo __('General').'</td></tr><tr><td>';
		echo __('Max characters to match firstname:');
		echo '</td><td>';
		echo '<input type="text" name="merge_chars" value="'.$charsDb->setting_value.'"size="1">';
		echo '</td><td>'; // explanation

		echo __('In different trees, first names may be listed differently: Thomas Julian Booth, Thomas J. Booth, Thomas Booth etc. By default a match of the first 10 characters of the first name will be considered a match. You can change this to another value. Try and find the right balance: if you set a low number of chars you will get many unwanted possible matches. If you set it too high, you may miss possible matches as in the example names above.');

		echo '</td></tr><tr><td style="font-weight:bold;text-align:left;vertical-align:top" colspan="3">';
		echo __('Duplicate merge');
		echo '</td></tr><tr><td>'.__('include blank lastnames');
		echo '</td><td>';
		echo '<select size="1" name="merge_lastname">';
		if ($lastnDb->setting_value=='YES'){
			echo '<option value="YES" SELECTED>'.__('Yes').'</option>';
			echo '<option value="NO">'.__('No').'</option>';
		}
		else{
			echo '<option value="NO" SELECTED>'.__('No').'</option>';
			echo '<option value="YES">'.__('Yes').'</option>';
		}
		echo "</select>";
		echo '</td><td>'; // explanation

		echo __('By default two persons with missing lastnames will be included as possible duplicates. Two persons called "John" without lastname will be considered a possible match. If you have many cases like this you could get a very long list of possible duplicates and you might want to disable this, so only persons with lastnames will be included.');

		echo '</td></tr><tr><td>'.__('include blank firstnames');
		echo '</td><td>';
		echo '<select size="1" name="merge_firstname">';
		if ($firstnDb->setting_value=='YES'){
			echo '<option value="YES" SELECTED>'.__('Yes').'</option>';
			echo '<option value="NO">'.__('No').'</option>';
		}
		else{
			echo '<option value="NO" SELECTED>'.__('No').'</option>';
			echo '<option value="YES">'.__('Yes').'</option>';
		}
		echo "</select>";
		echo '</td><td>'; // explanation

		echo __('Same as above, but for first names. When enabled (default), all persons called "Smith" without first name will be considered possible duplicates of each other. If you have many cases like this it could give you a long list and you might want to disable it.');

		echo '</td></tr><tr><td>'.__('include blank dates');
		echo '</td><td>';
		echo '<select size="1" name="merge_dates">';
		if ($datesDb->setting_value=='YES'){
			echo '<option value="YES" SELECTED>'.__('Yes').'</option>';
			echo '<option value="NO">'.__('No').'</option>';
		}
		else{
			echo '<option value="NO" SELECTED>'.__('No').'</option>';
			echo '<option value="YES">'.__('Yes').'</option>';
		}
		echo "</select>";
		echo '</td><td>'; // explanation

		echo __('By default, two persons with identical names, but with one or both missing birth/death dates are considered possible duplicates. In certain trees this can give a long list of possible duplicates. You can choose to disable this so only persons who both have a birth or death date and this date is identical, will be considered a possible match. This can drastically cut down the number of possible duplicates, but of course you may also miss out on pairs that actually are duplicates.');

		echo '</td></tr><tr><td style="font-weight:bold;text-align:left;vertical-align:top" colspan="3">';
		echo __('Automatic merge');
		echo '</td></tr><tr><td>'.__('include parents marriage date:');
		echo '</td><td>';
		echo '<select size="1" name="merge_parentsdate">';
		if ($pardDb->setting_value=='YES'){
			echo '<option value="YES" SELECTED>'.__('Yes').'</option>';
			echo '<option value="NO">'.__('No').'</option>';
		}
		else{
			echo '<option value="NO" SELECTED>'.__('No').'</option>';
			echo '<option value="YES">'.__('Yes').'</option>';
		}
		echo "</select>";
		echo '</td><td>'; // explanation

		echo __('Automatic merging is a dangerous business. Therefore many clauses are used to make sure the persons are indeed identical. Besides identical names, identical birth or death dates and identical names of parents, also the parents\' wedding date is included. If you consider this too much and rely on the above clauses, you can disable this.');

		echo '</td></tr>';

		echo '<tr><td colspan="2" style="text-align:center"><input type="Submit" name="settings" value="'.__('Save').'">';
		echo '&nbsp;&nbsp;&nbsp;<input type="Submit" name="reset" value="'.__('Reset').'"></td>';

		echo '</td><td>';
		echo '</tr></table><br><br><br>';
		echo '</form>';
	}

	// The default entry to the merge feature (the main screen) with the merge modes and settings
	else {
		echo '<br>';
		echo '<table class="humo" style="width:98%;background-color : #CCFFFF;">';
		echo '<tr class="table_header"><th colspan="2">'.__('Merge Options').'</th></tr>';
		echo '<tr><td colspan="2" style="padding:10px">';

		echo __('<b>NOTE:</b> None of these buttons will cause immediate merging. You will first be presented with information and can then decide to make a merge.<br><br>
<b>TIP:</b> Start with automatic merge to get rid of all obvious merges. (If no automatic merge options are found, try the duplicate merge option).<br>
These will likely cause surrounding relatives to be found, so continue with the "Relatives merge" option.<br>
Once you finish that, most needed merges will have been performed. You can then use "Duplicate merge" to see if there are duplicates left to consider for merging.<br>
As a last resort you can perform manual merges.');

		echo '</td></tr>';
		echo '<tr><td style="vertical-align:center;text-align:center;width:200px">';

		// automatic merge option button
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" style="min-width:150px" name="automatic" value="'.__('Automatic merge').'">';
		echo '</form>';
		echo '</td><td>';
		echo __('You will be shown the set of strict criteria used for automatic merging and then you can decide whether to continue.');

		// relatives merge option button (only shown as button if previous merges created a "surrounding relatives" array)
		echo '</td></tr><tr><td style="vertical-align:center;text-align:center;width:200px">';
		if($relatives_merge != '') {
			echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" style="min-width:150px" name="relatives" value="'.__('Relatives merge').'">';
			echo '</form>';
		}
		else {
			echo __('Relatives merge');
		}
		echo '</td><td>';

		echo __('This button will become available if you have made merges, and surrounding relatives (parents, children or spouses) have to be considered for merging too.<br>
By pressing this button, you can then continue to check the surrounding relatives, pair by pair, and merge them if necessary. If those merges will create additional surrounding relatives to consider, they will be automatically added to the list.<br>
Surrounding relatives are saved to the database and you can also return to it at a later stage.');

		echo '</td></tr><tr><td style="vertical-align:center;text-align:center;width:200px">';

		// duplicate merge option button
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" style="min-width:150px" name="duplicate_choices" value="'.__('Duplicate merge').'">';
		echo '</form>';
		echo '</td><td>';

		echo __('You will be presented, one after the other, with pairs of possible duplicates to consider for merging.<br>
After a merge you can switch to "relatives merge" and after that return to duplicate search where you left off.');

		echo '</td></tr><tr><td style="min-height:50px;vertical-align:center;text-align:center;width:200px">';

		// manual merge option button
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" style="min-width:150px" name="manual" value="'.__('Manual merge').'">';
		echo '</form>';
		echo '</td><td>';
		echo __('You can pick two persons out of the database to consider for merging.');
		echo '</td></tr><tr><td style="vertical-align:center;text-align:center;width:200px">';

		// settings option button
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
		echo '<input type="Submit" style="min-width:150px" name="settings" value="'.__('Settings').'">';
		echo '</form>';
		echo '</td><td>'.__('Here you can change the default filters for the different merge options.');
		echo '</td></tr></table>';

	}
}

//*********************************************************************************************
//******  "show_pair" is the function that presents the data of two persons to be merged  *****
//******  with the possibility to determine what information is passed from left to right *****
//*********************************************************************************************
function show_pair($left_id,$right_id,$mode) {
	global $dbh, $data2Db, $phpself;
	global $page, $tree_id, $menu_admin, $relatives_merge, $language;

	// get data for left person
	//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id = ".$left_id;
	$qry = "SELECT * FROM humo_persons WHERE pers_id = ".$left_id;
	$left = $dbh->query($qry);
	$leftDb = $left->fetch(PDO::FETCH_OBJ);

	$spouses1 = ''; $children1='';
	if($leftDb->pers_fams) {
		$fams = explode(';',$leftDb->pers_fams);
		foreach($fams as $value) {
			//$fam_qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$value."'";
			$fam_qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$value."'";
			$fam = $dbh->query($fam_qry);
			$famDb = $fam->fetch(PDO::FETCH_OBJ);
			if($famDb->fam_man == $leftDb->pers_gedcomnumber) { // spouse is the woman
				$spouse_ged = $famDb->fam_woman;
			}
			else {
				$spouse_ged = $famDb->fam_man;
			}
			//$spouse_qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$spouse_ged."'";
			$spouse_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$spouse_ged."'";
			$spouse = $dbh->query($spouse_qry);
			$spouseDb = $spouse->fetch(PDO::FETCH_OBJ);
			if($spouseDb) {
				$spouses1 .= $spouseDb->pers_lastname.' '.$spouseDb->pers_firstname.'<br>';
			}
			if($famDb->fam_children) {
				$child = explode(';',$famDb->fam_children);
				foreach($child as $ch_value) {
					//$ch_qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$ch_value."'";
					$ch_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$ch_value."'";
					$ch = $dbh->query($ch_qry);
					$chDb = $ch->fetch(PDO::FETCH_OBJ);
					if($chDb) {
						$children1 .= $chDb->pers_firstname.'<br>';
					}
				}
			}
		}
		$spouses1 = substr($spouses1,0,-4); // take off last <br>
		$children1 = substr($children1,0,-4); // take of last <br>
	}

	$father1=''; $mother1='';
	if($leftDb->pers_famc) {
		//$qry2 = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$leftDb->pers_famc."'";
		$qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$leftDb->pers_famc."'";
		$parents = $dbh->query($qry2);
		$parentsDb = $parents->fetch(PDO::FETCH_OBJ);

		//$fath_sql = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$parentsDb->fam_man."'";
		$fath_sql = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$parentsDb->fam_man."'";
		$fath = $dbh->query($fath_sql);
		$fathDb = $fath->fetch(PDO::FETCH_OBJ);
		if($fathDb) {
			$father1 = $fathDb->pers_lastname.', '.$fathDb->pers_firstname;
		}
		//$moth_sql = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$parentsDb->fam_woman."'";
		$moth_sql = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$parentsDb->fam_woman."'";
		$moth = $dbh->query($moth_sql);
		$mothDb = $moth->fetch(PDO::FETCH_OBJ);
		if($mothDb) {
			$mother1 = $mothDb->pers_lastname.', '.$mothDb->pers_firstname;
		}
	}

	// get data for right person
	//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id = ".$right_id;
	$qry = "SELECT * FROM humo_persons WHERE pers_id = ".$right_id;
	$right = $dbh->query($qry);
	$rightDb = $right->fetch(PDO::FETCH_OBJ);

	$spouses2 = ''; $children2 = '';
	if($rightDb->pers_fams) {
		$fams = explode(';',$rightDb->pers_fams);
		foreach($fams as $value) {
			//$fam_qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$value."'";
			$fam_qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$value."'";
			$fam = $dbh->query($fam_qry);
			$famDb = $fam->fetch(PDO::FETCH_OBJ);
			if($famDb->fam_man == $rightDb->pers_gedcomnumber) { // spouse is the woman
				$spouse_ged = $famDb->fam_woman;
			}
			else {
				$spouse_ged = $famDb->fam_man;
			}
			//$spouse_qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$spouse_ged."'";
			$spouse_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$spouse_ged."'";
			$spouse = $dbh->query($spouse_qry);
			$spouseDb = $spouse->fetch(PDO::FETCH_OBJ);
			if($spouseDb) {
				$spouses2 .= $spouseDb->pers_lastname.' '.$spouseDb->pers_firstname.'<br>';
			}
			if($famDb->fam_children) {
				$child = explode(';',$famDb->fam_children);
				foreach($child as $ch_value) {
					//$ch_qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$ch_value."'";
					$ch_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$ch_value."'";
					$ch = $dbh->query($ch_qry);
					$chDb = $ch->fetch(PDO::FETCH_OBJ);
					if($chDb) {
						$children2 .= $chDb->pers_firstname.'<br>';
					}
				}
			}
		}
		$spouses2 = substr($spouses2,0,-4); // take off last <br>
		$children2 = substr($children2,0,-4); // take of last <br>
	}

	$father2=''; $mother2='';
	if($rightDb->pers_famc) {
		//$qry2 = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$rightDb->pers_famc."'";
		$qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$rightDb->pers_famc."'";
		$parents = $dbh->query($qry2);
		$parentsDb = $parents->fetch(PDO::FETCH_OBJ);

		//$fath_sql = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$parentsDb->fam_man."'";
		$fath_sql = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$parentsDb->fam_man."'";
		$fath = $dbh->query($fath_sql);
		$fathDb = $fath->fetch(PDO::FETCH_OBJ);
		if($fathDb) {
			$father2 = $fathDb->pers_lastname.', '.$fathDb->pers_firstname;
		}
		//$moth_sql = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$parentsDb->fam_woman."'";
		$moth_sql = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$parentsDb->fam_woman."'";
		$moth = $dbh->query($moth_sql);
		$mothDb = $moth->fetch(PDO::FETCH_OBJ);
		if($mothDb) {
			$mother2 = $mothDb->pers_lastname.', '.$mothDb->pers_firstname;
		}
	}

	echo '<table style="width:900px;border:2px solid #d8d8d8">';
	echo '<tr class="table_header"><th style="vertical-align:top;font-size:130%" colspan=3>';
	if($mode=="duplicate") {
		echo __('Duplicate merge');
	}
	elseif($mode=="relatives") {
		echo __('Surrounding relatives check');
	}
	else {
		echo __('Manual merge');
	}
	echo '</th></tr>';
	if($mode=='duplicate') {
		$num = $_SESSION['present_compare_'.$data2Db->tree_prefix] + 1;
		echo '<tr><th style="width:150px;border-bottom:2px solid #a4a4a4;text-align:left">'.__('Nr. ').$num.__(' of ').count($_SESSION['dupl_arr_'.$data2Db->tree_prefix]).'</th>';
	}
	elseif($mode='relatives') {
		$rl = explode(';',$relatives_merge);
		$rls = count($rl) -1;
		echo '<tr><th style="width:150px;border-bottom:2px solid #a4a4a4;text-align:left">'.$rls.__(' relatives to check').'</th>';
	}
	else {
		echo '<tr><th style="width:150px;border-bottom:2px solid #a4a4a4"></th>';
	}
	echo '<th style="width:375px;border-bottom:2px solid #a4a4a4"> '.__('Person 1: ').' </th>';
	echo '<th style="width:375px;border-bottom:2px solid #a4a4a4"> '.__('Person 2: ').' </th></tr>';
	$color='#e6e6e6';
	echo '<tr style="background-color:#e6e6e6"><td style="font-weight:bold">'.__('Gedcom number:').'</td>';
	echo '<td>'.$leftDb->pers_gedcomnumber.'</td>';
	echo '<td>'.$rightDb->pers_gedcomnumber.'</td></tr>';

	$this->show_regular($leftDb->pers_lastname,$rightDb->pers_lastname,__('last name'),'l_name');
	$this->show_regular($leftDb->pers_firstname,$rightDb->pers_firstname,__('first name'),'f_name');
	$this->show_regular($leftDb->pers_callname,$rightDb->pers_callname,__('Nickname'),'c_name');
	$this->show_regular($leftDb->pers_patronym,$rightDb->pers_patronym,__('patronym'),'patr');
	$this->show_regular($leftDb->pers_birth_date,$rightDb->pers_birth_date,__('birth date'),'b_date');
	$this->show_regular($leftDb->pers_birth_place,$rightDb->pers_birth_place,__('birth place'),'b_place');
	$this->show_regular($leftDb->pers_birth_time,$rightDb->pers_birth_time,__('birth time'),'b_time');
	$this->show_regular($leftDb->pers_death_date,$rightDb->pers_death_date,__('death date'),'d_date');
	$this->show_regular($leftDb->pers_death_place,$rightDb->pers_death_place,__('death place'),'d_place');
	$this->show_regular($leftDb->pers_death_time,$rightDb->pers_death_time,__('death time'),'d_time');
	$this->show_regular($leftDb->pers_death_cause,$rightDb->pers_death_cause,__('death cause'),'d_cause');
	$this->show_regular($leftDb->pers_cremation,$rightDb->pers_cremation,__('cremation'),'crem');
	$this->show_regular($leftDb->pers_bapt_date,$rightDb->pers_bapt_date,__('baptism date'),'bp_date');
	$this->show_regular($leftDb->pers_bapt_place,$rightDb->pers_bapt_place,__('baptism place'),'bp_place');
	$this->show_regular($leftDb->pers_buried_date,$rightDb->pers_buried_date,__('burial date'),'br_date');
	$this->show_regular($leftDb->pers_buried_place,$rightDb->pers_buried_place,__('burial place'),'br_place');
	$this->show_regular($leftDb->pers_alive,$rightDb->pers_alive,__('alive'),'alive');
	$this->show_regular($leftDb->pers_religion,$rightDb->pers_religion,__('religion'),'reli');
	$this->show_regular($leftDb->pers_own_code,$rightDb->pers_own_code,__('own code'),'code');
	$this->show_regular($leftDb->pers_stillborn,$rightDb->pers_stillborn,__('stillborn'),'stborn');
	//$this->show_regular($leftDb->pers_favorite,$rightDb->pers_favorite,__('favourite'),'fav');
	$this->show_regular_text($leftDb->pers_text,$rightDb->pers_text,__('general text'),'text');
	$this->show_regular_text($leftDb->pers_name_text,$rightDb->pers_name_text,__('name text'),'n_text');
	$this->show_regular_text($leftDb->pers_birth_text,$rightDb->pers_birth_text,__('birth text'),'b_text');
	$this->show_regular_text($leftDb->pers_bapt_text,$rightDb->pers_bapt_text,__('baptism text'),'bp_text');
	$this->show_regular_text($leftDb->pers_death_text,$rightDb->pers_death_text,__('death text'),'d_text');
	$this->show_regular_text($leftDb->pers_buried_text,$rightDb->pers_buried_text,__('burial text'),'br_text');

	// functions that deal with showing the events, sources and addresses
	$this->show_events($leftDb->pers_gedcomnumber,$rightDb->pers_gedcomnumber);
	$this->show_sources($leftDb->pers_gedcomnumber,$rightDb->pers_gedcomnumber);
	$this->show_addresses($leftDb->pers_gedcomnumber,$rightDb->pers_gedcomnumber);

	echo '<tr><td colspan=3 style="border-top:2px solid #a4a4a4;border-bottom:2px solid #a4a4a4;font-weight:bold">'.__('Relatives').':</td></tr>';
	echo '<tr style="background-color:#f2f2f2"><td style="font-weight:bold">'.__('Spouse').':</td>';
	echo '<td>'.$spouses1.'</td>';
	echo '<td>'.$spouses2.'</td></tr>';
	echo '<tr style="background-color:#e6e6e6"><td style="font-weight:bold">'.__('Father').':</td>';
	echo '<td>'.$father1.'</td>';
	echo '<td>'.$father2.'</td></tr>';
	echo '<tr style="background-color:#f2f2f2"><td style="font-weight:bold">'.__('Mother').':</td>';
	echo '<td>'.$mother1.'</td>';
	echo '<td>'.$mother2.'</td></tr>';
	echo '<tr style="background-color:#e6e6e6"><td style="font-weight:bold">'.__('Children').':</td>';
	echo '<td>'.$children1.'</td>';
	echo '<td>'.$children2.'</td></tr>';
	echo '</table>';
}
//*************************************************************************************************************
//****** show_regular is a function that places the regular items from humoX_person in the comparison table **
//*************************************************************************************************************
function show_regular ($left_item,$right_item,$title,$name) {
	global $dbh, $language, $color;
	if($left_item OR $right_item) {
		if($color=='#e6e6e6') { $color='#f2f2f2'; } else { $color='#e6e6e6'; }
		echo '<tr style="background-color:'.$color.'"><td style="font-weight:bold">'.ucfirst($title).':</td>';
		$checked=''; if($left_item) {
			$checked=" CHECKED";
			if($name=='crem' AND $left_item=='1') { $left_item = 'Yes'; }
			if($name=='fav' AND $left_item=='1') { $left_item = 'Yes'; }
			if($name=='stborn' AND $left_item=='y') { $left_item = 'Yes'; }
		}
		echo '<td><input type="radio" name="'.$name.'" value="1"'.$checked.'>'.$left_item.'</td>';
		$checked=''; if(!$left_item) {
			$checked=" CHECKED";
			if($name=='crem' AND $right_item=='1') { $right_item = 'Yes'; }
			if($name=='fav' AND $right_item=='1') { $right_item = 'Yes'; }
			if($name=='stborn' AND $right_item=='y') { $right_item = 'Yes'; }
		}
		echo '<td><input type="radio" name="'.$name.'" value="2"'.$checked.'>'.$right_item.'</td></tr>';
	}
}
//***********************************************************************************************************************
//****** show_regular_text is a function that places the regular text items from humoX_person in the comparison table **
//***********************************************************************************************************************
function show_regular_text ($left_item,$right_item,$title,$name) {
	global $dbh, $tree_id, $language, $data2Db, $color;
	if($right_item) {
		if($color=='#e6e6e6') { $color='#f2f2f2'; } else { $color='#e6e6e6'; }
		echo '<tr style="background-color:'.$color.'"><td style="font-weight:bold">'.$title.':</td><td>';
		$checked=''; $showtext='';
		if($left_item) {
			$checked=" CHECKED"; $showtext="&nbsp;&nbsp;[ ".__('Read text')." ]";
			echo '<input type="checkbox" name="'.$name.'_l" '.$checked.'>';
			if(substr($left_item,0,2)=="@N") {  // not plain text but @N23@ -> look it up in humoX_texts
				//$notes = $dbh->query("SELECT text_text FROM ".$data2Db->tree_prefix."texts WHERE text_gedcomnr ='".substr($left_item,1,-1)."'");
				$notes = $dbh->query("SELECT text_text FROM humo_texts
					WHERE text_tree_id='".$tree_id."' AND text_gedcomnr ='".substr($left_item,1,-1)."'");
				$notesDb = $notes->fetch(PDO::FETCH_OBJ);
				$notetext = $notesDb->text_text;
			}
			else { $notetext = $left_item; }
			echo '<a onmouseover="popup(\''.$this->popclean($notetext).'\');" href="#">'.$showtext.'</a>';
		}
		else { echo __('(no data)'); }
		$checked=''; $showtext=''; if(!$left_item) { $checked=" CHECKED"; }
		$showtext="&nbsp;&nbsp;[ ".__('Read text')." ]";
		echo '</td><td><input type="checkbox" name="'.$name.'_r" '.$checked.'>';
		if(substr($right_item,0,2)=="@N") {  // not plain text but @N23@ -> look it up in humoX_texts
			//$notes = $dbh->query("SELECT text_text FROM ".$data2Db->tree_prefix."texts WHERE text_gedcomnr ='".substr($right_item,1,-1)."'");
			$notes = $dbh->query("SELECT text_text FROM humo_texts
				WHERE text_tree_id='".$tree_id."' AND text_gedcomnr ='".substr($right_item,1,-1)."'");
			$notesDb = $notes>fetch(PDO::FETCH_OBJ);
			$notetext = $notesDb->text_text;
		}
		else { $notetext = $right_item; }
		echo '<a onmouseover="popup(\''.$this->popclean($notetext).'\');" href="#">'.$showtext.'</a></td></tr>';
	}
}
//***********************************************************************************
//****** show_events is a function that places the events in the comparison table **
//***********************************************************************************
function show_events ($left_ged,$right_ged) {
	global $dbh, $tree_id, $language, $data2Db, $color;
	$l_address = $l_picture = $l_profession = $l_source = $l_event = $l_birth_declaration = $l_baptism_witness = $l_death_declaration = $l_burial_witness = $l_name = $l_nobility = $l_title = $l_lordship = $l_URL = $l_else = '';
	$r_address = $r_picture = $r_profession = $r_source = $r_event = $r_birth_declaration = $r_baptism_witness = $r_death_declaration = $r_burial_witness = $r_name = $r_nobility = $r_title = $r_lordship = $r_URL = $r_else = '';
	//$left_events = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$left_ged."' ORDER BY event_kind ");
	$left_events = $dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$left_ged."' ORDER BY event_kind ");
	//$right_events = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$right_ged."' ORDER BY event_kind ");	
	$right_events = $dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$right_ged."' ORDER BY event_kind ");

	if($right_events->rowCount() > 0) {  // no use doing this if right has no events at all...

		while($l_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
			if($l_eventsDb->event_kind=="address") { $l_address[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="picture") { $l_picture[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="profession") { $l_profession[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			//elseif($l_eventsDb->event_kind=="source") { $l_source[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="event") { $l_event[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="birth_declaration") { $l_birth_declaration[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="baptism_witness") { $l_baptism_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="death_declaration") { $l_death_declaration[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="burial_witness") { $l_burial_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="name") { $l_name[$l_eventsDb->event_id] = '('.$l_eventsDb->event_gedcom.') '.$l_eventsDb->event_event;	}
			elseif($l_eventsDb->event_kind=="nobility") { $l_nobility[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="title") { $l_title[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="lordship") { $l_lordship[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			elseif($l_eventsDb->event_kind=="URL") { $l_URL[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
			else { 	$l_else[$l_eventsDb->event_id] = $l_eventsDb->event_event; }
		}

		//while($r_eventsDb = mysql_fetch_object($right_events)) {
		while($r_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
			if($r_eventsDb->event_kind=="address") { $r_address[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="picture") { $r_picture[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="profession") { $r_profession[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			//elseif($r_eventsDb->event_kind=="source") { $r_source[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="event") { $r_event[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="birth_declaration") { $r_birth_declaration[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="baptism_witness") { $r_baptism_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="death_declaration") { $r_death_declaration[$r_eventsDb->event_id] = $r_eventsDb->event_event;	}
			elseif($r_eventsDb->event_kind=="burial_witness") { $r_burial_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="name") { $r_name[$r_eventsDb->event_id] = '('.$r_eventsDb->event_gedcom.') '.$r_eventsDb->event_event;	}
			elseif($r_eventsDb->event_kind=="nobility") { $r_nobility[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="title") { $r_title[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="lordship") { $r_lordship[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			elseif($r_eventsDb->event_kind=="URL") { $r_URL[$r_eventsDb->event_id] = $r_eventsDb->event_event; }
			else { 	$r_else[] = $l_eventsDb->event_event; }
		}
		// before calling put_event function check if right has a value otherwise there is no need to show
		if(isset($r_address)) { $this->put_event('address',__('Address'),$l_address,$r_address); }
		if(isset($r_picture)) { $this->put_event('picture',__('Picture'),$l_picture,$r_picture); }
		if(isset($r_profession)) { $this->put_event('profession',__('Profession'),$l_profession,$r_profession); }
		//if(isset($r_source)) { $this->put_event('source','Sources',$l_source,$r_source); }
		if(isset($r_event)) { $this->put_event('event',__('Event'),$l_event,$r_event); }
		if(isset($r_birth_declaration)) { $this->put_event('birth_declaration',__('birth declaration'),$l_birth_declaration,$r_birth_declaration); }
		if(isset($r_baptism_witness)) { $this->put_event('baptism_witness',__('baptism witness'),$l_baptism_witness,$r_baptism_witness); }
		if(isset($r_death_declaration)) { $this->put_event('death_declaration',__('death declaration'),$l_death_declaration,$r_death_declaration); }
		if(isset($r_burial_witness)) { $this->put_event('burial_witness',__('burial witness'),$l_burial_witness,$r_burial_witness); }
		if(isset($r_name)) { $this->put_event('name',__('Other names'),$l_name,$r_name); }
		if(isset($r_nobility)) { $this->put_event('nobility',__('Title of Nobility'),$l_nobility,$r_nobility); }
		if(isset($r_title)) { $this->put_event('title',__('Title'),$l_title,$r_title); }
		if(isset($r_lordship)) { $this->put_event('lordship',__('Title of Lordship'),$l_lordship,$r_lordship); }
		if(isset($r_URL)) { $this->put_event('URL',__('Internet link / URL'),$l_URL,$r_URL); }
	}
}

//*********************************************************************************************
//******  "put_event" is a function to create the checkboxes for the event items          *****
//*********************************************************************************************
function put_event($this_event,$name_event,$l_ev,$r_ev) {
	global $color, $dbh, $data2Db, $language;

	if($r_ev!='') { // if right has no event all stays as it is
		if($color=='#e6e6e6') { $color='#f2f2f2'; } else { $color='#e6e6e6'; }
		echo '<tr style="background-color:'.$color.'"><td style="font-weight:bold">'.$name_event.':</td>';
		echo '<td>';
		if($l_ev!='') {
			foreach($l_ev as $key => $value) {
				if(substr($value,0,2)=='@I') {  // this is a person gedcom number, not plain text -> show the name
					$value = str_replace('@','',$value);
					//$result = $dbh->query("SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber = '".$value."'");
					$result = $dbh->query("SELECT pers_lastname, pers_firstname
						FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$value."'");
					$resultDb = $result->fetch(PDO::FETCH_OBJ);
					$value = $resultDb->pers_firstname.' '.$resultDb->pers_lastname;
				}
				if($this_event=='picture') { // show link to pic
					$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$data2Db->tree_prefix."'");
					$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
					$tree_pict_path=$dataDb->tree_pict_path;
					$dir='../'.$tree_pict_path;
					$value = $value.'<a onmouseover="popup(\'<img width=&quot;150px&quot; src=&quot;'.$dir.$value.'&quot;>\',\'150px\');" href="#">'.' [ Show ]'.'</a>';
				}
				echo '<input type="checkbox" name="l_'.$this_event.'_'.$key.'" checked>'.$value.'<br>';
			}
		}
		else {
			echo __('(no data)');
		}
		echo '</td><td>';
		if($r_ev!='') {
			$checked=''; if($l_ev=='') { $checked=" CHECKED"; }
			foreach($r_ev as $key => $value) {
				if(substr($value,0,2)=='@I') {  // this is a person gedcom number, not plain text
					$value = str_replace('@','',$value);
					//$result = $dbh->query("SELECT pers_lastname, pers_firstname FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber = '".$value."'");
					$result = $dbh->query("SELECT pers_lastname, pers_firstname
						FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$value."'");
					$resultDb = $result->fetch(PDO::FETCH_OBJ);
					$value = $resultDb->pers_firstname.' '.$resultDb->pers_lastname;
				}
				if($this_event=='picture') {
					$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$data2Db->tree_prefix."'");
					$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
					$tree_pict_path=$dataDb->tree_pict_path;
					$dir='../'.$tree_pict_path;
					$value = $value.'<a onmouseover="popup(\'<img width=&quot;150px&quot; src=&quot;'.$dir.$value.'&quot;>\',\'150px\');" href="#">'.' [ Show ]'.'</a>';
				}
				echo '<input type="checkbox" name="r_'.$this_event.'_'.$key.'" '.$checked.'>'.$value.'<br>';
			}
		}
		else {
			echo __('(no data)');
		}
		echo '</td></tr>';
	}
}

//**********************************************************************************************************************
//******  "show_sources" is the function that places the sources in the comparison table (if right has a value)     *****
//**********************************************************************************************************************
function show_sources ($left_ged,$right_ged) {
	global $dbh, $tree_id, $language, $data2Db, $color;
	//$left_sources = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$left_ged."' AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
	$left_sources = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$left_ged."' AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
	//$right_sources = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$right_ged."' AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");	
	$right_sources = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$right_ged."' AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");	

	if($right_sources->rowCount() > 0) { // no use doing this if right has no sources
		if($color=='#e6e6e6') { $color='#f2f2f2'; } else { $color='#e6e6e6'; }
		echo '<tr style="background-color:'.$color.'"><td style="font-weight:bold">'.__('Sources').':</td>';
		echo '<td>';
		if($left_sources->rowCount() > 0) {
			while($left_sourcesDb = $left_sources->fetch(PDO::FETCH_OBJ))	{
				//$l_source= $dbh->query("SELECT source_title FROM ".$data2Db->tree_prefix."sources WHERE source_gedcomnr='".$left_sourcesDb->connect_source_id."'");
				$l_source= $dbh->query("SELECT source_title FROM humo_sources
					WHERE source_tree_id='".$tree_id."' AND source_gedcomnr='".$left_sourcesDb->connect_source_id."'");
				$result = $l_source->fetch(PDO::FETCH_OBJ);
				if(strlen($result->source_title) > 30) { $title = '<a onmouseover="popup(\''.$this->popclean($result->source_title).'\');" href="#">'.'&nbsp;[ Show source ]'.'</a>'; }
				else { $title = $result->source_title; }
				echo '<input type="checkbox" name="l_source_'.$left_sourcesDb->connect_id.'" '.'checked'.'>('.str_replace('_source',' ',$left_sourcesDb->connect_sub_kind).') '.$title.'<br>';
			}
		}
		else {
			echo __('(no data)');
		}
		echo '</td><td>';
		while($right_sourcesDb = $right_sources->fetch(PDO::FETCH_OBJ)) {
			$checked=''; if(!$left_sources->rowCount()) { $checked = " checked"; }
			//$r_source= $dbh->query("SELECT source_title FROM ".$data2Db->tree_prefix."sources WHERE source_gedcomnr='".$right_sourcesDb->connect_source_id."'");
			$r_source= $dbh->query("SELECT source_title FROM humo_sources
				WHERE source_tree_id='".$tree_id."' AND source_gedcomnr='".$right_sourcesDb->connect_source_id."'");
			$result = $r_source->fetch(PDO::FETCH_OBJ);
			if(strlen($result->source_title) > 30) { $title = '<a onmouseover="popup(\''.$this->popclean($result->source_title).'\');" href="#">'.'&nbsp;[ Show source ]'.'</a>'; }
			else { $title = $result->source_title; }
			echo '<input type="checkbox" name="r_source_'.$right_sourcesDb->connect_id.'" '.$checked.'>('.str_replace('_source',' ',$right_sourcesDb->connect_sub_kind).') '.$title.'<br>';
		}
		echo '</td></tr>';
	}
}

//**********************************************************************************************************************
//******  "show_addresses" is the function that places the addresses in the comparison table (if right has a value) *****
//**********************************************************************************************************************
function show_addresses ($left_ged,$right_ged) {
	global $dbh, $tree_id, $language, $data2Db, $color;
	//$left_addresses = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$left_ged."' AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
	$left_addresses = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$left_ged."' AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
	//$right_addresses = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$right_ged."' AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
	$right_addresses = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$right_ged."' AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");

	if($right_addresses->rowCount() > 0) {  // no use doing this if right has no sources
		if($color=='#e6e6e6') { $color='#f2f2f2'; } else { $color='#e6e6e6'; }
		echo '<tr style="background-color:'.$color.'"><td style="font-weight:bold">'.__('Addresses').':</td>';
		echo '<td>';
		if($left_addresses->rowCount() > 0) {
			while($left_addressesDb = $left_addresses->fetch(PDO::FETCH_OBJ))	{
				//$l_address= $dbh->query("SELECT address_address, address_place FROM ".$data2Db->tree_prefix."addresses WHERE address_gedcomnr='".$left_addressesDb->connect_item_id."'");
				$l_address= $dbh->query("SELECT address_address, address_place FROM humo_addresses
					WHERE address_tree_id='".$tree_id."' AND address_gedcomnr='".$left_addressesDb->connect_item_id."'");
				$result = $l_address->fetch(PDO::FETCH_OBJ);
				if(strlen($result->address_address.' '.$result->address_place) > 30) { $title = '<a onmouseover="popup(\''.$this->popclean($result->address_address.' '.$result->address_place).'\');" href="#">'.'&nbsp;[ Show address ]'.'</a>'; }
				else { $title = $result->address_address.' '.$result->address_place; }
				echo '<input type="checkbox" name="l_address_'.$left_addressesDb->connect_id.'" '.'checked'.'>('.str_replace('_address',' ',$left_addressesDb->connect_sub_kind).') '.$title.'<br>';
			}
		}
		else {
			echo __('(no data)');
		}
		echo '</td><td>';
		while($right_addressesDb = $right_addresses->fetch(PDO::FETCH_OBJ))	{
			$checked=''; if(!$left_addresses->rowCount()) { $checked = " checked"; }
			$r_address= $dbh->query("SELECT address_address, address_place FROM humo_addresses
				WHERE address_tree_id='".$tree_id."' AND address_gedcomnr='".$right_addressesDb->connect_item_id."'");
			//$r_address= $dbh->query("SELECT address_address, address_place FROM ".$data2Db->tree_prefix."addresses WHERE address_gedcomnr='".$right_addressesDb->connect_item_id."'");
			$r_address= $dbh->query("SELECT address_address, address_place FROM humo_addresses
				WHERE address_tree_id='".$tree_id."' AND address_gedcomnr='".$right_addressesDb->connect_item_id."'");
			$result = $r_address->fetch(PDO::FETCH_OBJ);
			if(strlen($result->address_address.' '.$result->address_place) > 30) { $title = '<a onmouseover="popup(\''.$this->popclean($result->address_address.' '.$result->address_place).'\');" href="#">'.'&nbsp;[ Show address ]'.'</a>'; }
			else { $title = $result->address_address.' '.$result->address_place; }
			echo '<input type="checkbox" name="r_address_'.$right_addressesDb->connect_id.'" '.$checked.'>('.str_replace('_address',' ',$right_addressesDb->connect_sub_kind).') '.$title.'<br>';
		}
		echo '</td></tr>';
	}
}

//**********************************************************************************************************************
//******  "merge_them" is the function that does the actual job of merging the data of two persons (left and right)*****
//**********************************************************************************************************************
function merge_them($left,$right,$mode) {
	global $dbh, $tree_id, $data2Db, $phpself, $language;
	global $page, $menu_admin;
	global $relatives_merge, $merge_chars;
	global $result1Db, $result2Db;
	// merge algorithm - merge right into left
	// 1. if right has pers_fams with different wife - this Fxx is added to left's pers_fams (in humo_person)
	//    and in humo_family the Ixx of right is replaced with the Ixx of left
	//    Right's Ixx is deleted
	// 2. if right has pers_fams with identical wife - children are added to left's Fxx (in humo_family)
	//    and with each child the famc is changed to left's fams
	//    Right's Fxx is deleted
	//    Right's Ixx is deleted
	// 3. In either case whether right has family or not, if right has famc then in
	//    humo_family in right's parents Fxx, the child's Ixx is changed from right's to left's

	//$qry1= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$left."'";  // left person
	$qry1= "SELECT * FROM humo_persons WHERE pers_id ='".$left."'";  // left person
	$result1 = $dbh->query($qry1);
	$result1Db = $result1->fetch(PDO::FETCH_OBJ);
	//$qry2= "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_id ='".$right."'";  // right person
	$qry2= "SELECT * FROM humo_persons WHERE pers_id ='".$right."'";  // right person
	$result2 = $dbh->query($qry2);
	$result2Db = $result2->fetch(PDO::FETCH_OBJ);

	$name1 = $result1Db->pers_firstname.' '.$result1Db->pers_lastname; // store for notification later
	$name2 = $result2Db->pers_firstname.' '.$result2Db->pers_lastname; // store for notification later

	if($result2Db->pers_fams) {
		$spouse1=''; $spouse2=''; $count_doubles=0;
		$same_spouse = false; // will be made true if identical spouses found in next "if"

		if($result1Db->pers_fams) {
			//$same_spouse=false;
			$fam1_arr = explode(";",$result1Db->pers_fams);
			$fam2_arr = explode(";",$result2Db->pers_fams);
			// start searching for spouses with same ged nr (were merged earlier) of both persons
			for($n=0;$n<count($fam1_arr);$n++) {
				//$famqry1 = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$fam1_arr[$n]."'";
				$famqry1 = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$fam1_arr[$n]."'";
				$famresult1 = $dbh->query($famqry1);
				$famresult1Db = $famresult1->fetch(PDO::FETCH_OBJ);
				$spouse1 = $famresult1Db->fam_man;
				if($result2Db->pers_sexe == "M") {
					$spouse1 = $famresult1Db->fam_woman;
				}
				for($m=0;$m<count($fam2_arr);$m++) {
					//$famqry2 = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$fam2_arr[$m]."'";
					$famqry2 = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$fam2_arr[$m]."'";
					$famresult2 = $dbh->query($famqry2);
					$famresult2Db = $famresult2->fetch(PDO::FETCH_OBJ);
					$spouse2 = $famresult2Db->fam_man;
					if($result2Db->pers_sexe == "M") {
						$spouse2 = $famresult2Db->fam_woman;
					}
					if($spouse1 == $spouse2) { // found identical spouse, these F's have to be merged
						$same_spouse = true;
						// make array of fam mysql objects with identical spouses
						//(there may be more than one if they were merged earlier!)
						$f1[] = $famresult1Db;
						$f2[] = $famresult2Db;
						$sp1[] = $spouse1;
						$sp2[] = $spouse2; // need this?????
					}
				}
			}
			if($same_spouse == true) {
				// left has one or more fams with same wife (spouse was already merged)
				// if right has children - add them to the left F

				// with all possible families of the right person that will move to the left, change right's I for left I
				$r_spouses = explode(';',$result2Db->pers_fams);
				for($i=0;$i<count($r_spouses);$i++) { // get all fams
					if($result2Db->pers_sexe == "M") { $per = "fam_man"; }
					else { $per = "fam_woman"; }
					//$qry = "UPDATE ".$data2Db->tree_prefix."family SET ".$per." = '".$result1Db->pers_gedcomnumber."' WHERE fam_gedcomnumber ='".$r_spouses[$i]."'";
					$qry = "UPDATE humo_families SET ".$per." = '".$result1Db->pers_gedcomnumber."'
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$r_spouses[$i]."'";
					$dbh->query($qry);
				}
				for($i=0; $i < count($f1); $i++) { // with all identical spouses
					if($f2[$i]->fam_children) {
						if($f1[$i]->fam_children) {
							// add right's children to left if not same gedcomnumber (=if not merged already)
							$rightchld = $f2[$i]->fam_children;
							$l_chld = explode(';',$f1[$i]->fam_children);
							$r_chld = explode(';',$f2[$i]->fam_children);
							for($q=0;$q<count($l_chld);$q++) {
								for($w=0;$w<count($r_chld);$w++) {
									if($l_chld[$q] == $r_chld[$w]) { // same gedcomnumber
										$rightchld = str_replace($r_chld[$w].';', '' , $rightchld.';' );
										if(substr($rightchld,-1,1) == ';') { $rightchld = substr($rightchld,0,-1); }
									}
								}
							}
							if($rightchld != '') {
								$childr = $f1[$i]->fam_children.';'.$rightchld;
							}
							else {
								$childr = $f1[$i]->fam_children;
							}

							// if children were moved to left, create warning about possible duplicate children that will be created
							if($rightchld != '') {
								$allch1 = explode(';',$f1[$i]->fam_children);
								$allch2 = explode(';',$rightchld);
								for($z=0; $z < count($allch1); $z++) {
									//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$allch1[$z]."'";
									$qry = "SELECT * FROM humo_persons
										WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$allch1[$z]."'";
									$chl1 = $dbh->query($qry);
									$chl1Db = $chl1->fetch(PDO::FETCH_OBJ);
									for($y=0; $y < count($allch2); $y++) {
										//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$allch2[$y]."'";
										$qry = "SELECT * FROM humo_persons
											WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$allch2[$y]."'";
										$chl2 = $dbh->query($qry);
										$chl2Db = $chl2->fetch(PDO::FETCH_OBJ);
										if($chl1Db->pers_lastname == $chl2Db->pers_lastname AND
											substr($chl1Db->pers_firstname,0,$merge_chars) == substr($chl2Db->pers_firstname,0,$merge_chars)) {
											$string1 = $allch1[$z].'@'.$allch2[$y].';';
											$string2 = $allch2[$y].'@'.$allch1[$z].';';
											// make sure this pair doesn't exist already in the string
											if(strstr($relatives_merge,$string1)===false AND strstr($relatives_merge,$string2)===false) {
												$relatives_merge .= $string1;
											}
											$dbh->query("UPDATE humo_settings SET setting_value ='".$relatives_merge."'
												WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
										}
									}
								}
							}
						}
						else { // only right has children
							$childr = $f2[$i]->fam_children;
						}
						//$qry = "UPDATE ".$data2Db->tree_prefix."family SET fam_children ='".$childr."' WHERE fam_gedcomnumber='".$f1[$i]->fam_gedcomnumber."'";
						$qry = "UPDATE humo_families SET fam_children ='".$childr."'
							WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$f1[$i]->fam_gedcomnumber."'";
						$dbh->query($qry);

						// change those childrens' famc to left F
						$allchld = explode(";",$f2[$i]->fam_children);
						foreach($allchld as $value) {
							//$qry = "UPDATE ".$data2Db->tree_prefix."person SET pers_famc='".$f1[$i]->fam_gedcomnumber."' WHERE pers_gedcomnumber='".$value."'";
							$qry = "UPDATE humo_persons SET pers_famc='".$f1[$i]->fam_gedcomnumber."'
								WHERE fam_tree_id='".$tree_id."' AND pers_gedcomnumber='".$value."'";
							$dbh->query($qry);
						}
					}
				}

				// Add the right fams to left fams, without the F's that belonged to the duplicate right spouse(s)
				$famstring = $result2Db->pers_fams.';';
				for($i=0; $i < count($f1); $i++) { // can use f1 or f2 they are the same size
					for($i=0; $i < count($f2); $i++) {
						$famstring = str_replace($f2[$i]->fam_gedcomnumber.';' ,'',$famstring);
					}
				}
				if(substr($famstring,-1,1)==';') { $famstring = substr($famstring,0,-1); } // take off last ;
				if($famstring!='') {$newstring = $result1Db->pers_fams.';'.$famstring; }
				else { $newstring = $result1Db->pers_fams; }
				//$qry = "UPDATE ".$data2Db->tree_prefix."person SET pers_fams = '".$newstring."' WHERE pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
				$qry = "UPDATE humo_persons SET pers_fams = '".$newstring."'
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
				$dbh->query($qry);

				// remove the F that belonged to the duplicate right spouse from that spouse as well - he/she is one and the same
				for($i=0; $i<count($f1); $i++) { // for each of the identical spouses
					//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$sp1[$i]."'";
					$qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$sp1[$i]."'";
					$sp_data = $dbh->query($qry) or die(mysql_error()."-7");
					$sp_dataDb = $sp_data->fetch(PDO::FETCH_OBJ);
					$sp_string = $sp_dataDb->pers_fams.';';
					$sp_string = str_replace($f2[$i]->fam_gedcomnumber.';' ,'',$sp_string);
					if(substr($sp_string,-1,1)==';') { $sp_string = substr($sp_string,0,-1); } // take off last ; again
					//$qry = "UPDATE ".$data2Db->tree_prefix."person SET pers_fams = '".$sp_string."' WHERE pers_id ='".$sp_dataDb->pers_id."'";
					$qry = "UPDATE humo_persons SET pers_fams = '".$sp_string."' WHERE pers_id ='".$sp_dataDb->pers_id."'";
					$dbh->query($qry);
				}

				// before we delete the F's of duplicate wifes from the database, we first check if they have items
				// that are not known in the "receiving" F's. If so, we copy it to the corresponding left families
				// to make one Db query only, we first put the necessary fields and values in an array
				for($i=0; $i<count($f1); $i++) {
					if($f1[$i]->fam_kind=='' AND $f2[$i]->fam_kind!='') { $fam_items[$i]["fam_kind"] = $f2[$i]->fam_kind; }
					if($f1[$i]->fam_relation_date=='' AND $f2[$i]->fam_relation_date!='') { $fam_items[$i]["fam_relation_date"] = $f2[$i]->fam_relation_date; }
					if($f1[$i]->fam_relation_place=='' AND $f2[$i]->fam_relation_place!='') { $fam_items[$i]["fam_relation_place"] = $f2[$i]->fam_relation_place; }
					if($f1[$i]->fam_relation_text=='' AND $f2[$i]->fam_relation_text!='') { $fam_items[$i]["fam_relation_text"] = $f2[$i]->fam_relation_text; }
					if($f1[$i]->fam_relation_source=='' AND $f2[$i]->fam_relation_source!='') { $fam_items[$i]["fam_relation_source"] = $f2[$i]->fam_relation_source; }
					if($f1[$i]->fam_relation_end_date=='' AND $f2[$i]->fam_relation_end_date!='') { $fam_items[$i]["fam_relation_end_date"] = $f2[$i]->fam_relation_end_date; }
					if($f1[$i]->fam_marr_notice_date=='' AND $f2[$i]->fam_marr_notice_date!='') { $fam_items[$i]["fam_marr_notice_date"] = $f2[$i]->fam_marr_notice_date; }
					if($f1[$i]->fam_marr_notice_place=='' AND $f2[$i]->fam_marr_notice_place!='') { $fam_items[$i]["fam_marr_notice_place"] = $f2[$i]->fam_marr_notice_place; }
					if($f1[$i]->fam_marr_notice_text=='' AND $f2[$i]->fam_marr_notice_text!='') { $fam_items[$i]["fam_marr_notice_text"] = $f2[$i]->fam_marr_notice_text; }
					if($f1[$i]->fam_marr_notice_source=='' AND $f2[$i]->fam_marr_notice_source!='') { $fam_items[$i]["fam_marr_notice_source"] = $f2[$i]->fam_marr_notice_source; }
					if($f1[$i]->fam_marr_date=='' AND $f2[$i]->fam_marr_date!='') { $fam_items[$i]["fam_marr_date"] = $f2[$i]->fam_marr_date; }
					if($f1[$i]->fam_marr_place=='' AND $f2[$i]->fam_marr_place!='') { $fam_items[$i]["fam_marr_place"] = $f2[$i]->fam_marr_place; }
					if($f1[$i]->fam_marr_text=='' AND $f2[$i]->fam_marr_text!='') { $fam_items[$i]["fam_marr_text"] = $f2[$i]->fam_marr_text; }
					if($f1[$i]->fam_marr_source=='' AND $f2[$i]->fam_marr_source!='') { $fam_items[$i]["fam_marr_source"] = $f2[$i]->fam_marr_source; }
					if($f1[$i]->fam_marr_authority=='' AND $f2[$i]->fam_marr_authority!='') { $fam_items[$i]["fam_marr_authority"] = $f2[$i]->fam_marr_authority; }
					if($f1[$i]->fam_marr_church_notice_date=='' AND $f2[$i]->fam_marr_church_notice_date!='') { $fam_items[$i]["fam_marr_church_notice_date"] = $f2[$i]->fam_marr_church_notice_date; }
					if($f1[$i]->fam_marr_church_notice_place=='' AND $f2[$i]->fam_marr_church_notice_place!='') { $fam_items[$i]["fam_marr_church_notice_place"] = $f2[$i]->fam_marr_church_notice_place; }
					if($f1[$i]->fam_marr_church_notice_text=='' AND $f2[$i]->fam_marr_church_notice_text!='') { $fam_items[$i]["fam_marr_church_notice_text"] = $f2[$i]->fam_marr_church_notice_text; }
					if($f1[$i]->fam_marr_church_notice_source=='' AND $f2[$i]->fam_marr_church_notice_source!='') { $fam_items[$i]["fam_marr_church_notice_source"] = $f2[$i]->fam_marr_church_notice_source; }
					if($f1[$i]->fam_marr_church_date=='' AND $f2[$i]->fam_marr_church_date!='') { $fam_items[$i]["fam_marr_church_date"] = $f2[$i]->fam_marr_church_date; }
					if($f1[$i]->fam_marr_church_place=='' AND $f2[$i]->fam_marr_church_place!='') { $fam_items[$i]["fam_marr_church_place"] = $f2[$i]->fam_marr_church_place; }
					if($f1[$i]->fam_marr_church_text=='' AND $f2[$i]->fam_marr_church_text!='') { $fam_items[$i]["fam_marr_church_text"] = $f2[$i]->fam_marr_church_text; }
					if($f1[$i]->fam_marr_church_source=='' AND $f2[$i]->fam_marr_church_source!='') { $fam_items[$i]["fam_marr_church_source"] = $f2[$i]->fam_marr_church_source; }
					if($f1[$i]->fam_religion=='' AND $f2[$i]->fam_religion!='') { $fam_items[$i]["fam_religion"] = $f2[$i]->fam_religion; }
					if($f1[$i]->fam_div_date=='' AND $f2[$i]->fam_div_date!='') { $fam_items[$i]["fam_div_date"] = $f2[$i]->fam_div_date; }
					if($f1[$i]->fam_div_place=='' AND $f2[$i]->fam_div_place!='') { $fam_items[$i]["fam_div_place"] = $f2[$i]->fam_div_place; }
					if($f1[$i]->fam_div_text=='' AND $f2[$i]->fam_div_text!='') { $fam_items[$i]["fam_div_text"] = $f2[$i]->fam_div_text; }
					if($f1[$i]->fam_div_source=='' AND $f2[$i]->fam_div_source!='') { $fam_items[$i]["fam_div_source"] = $f2[$i]->fam_div_source; }
					if($f1[$i]->fam_div_authority=='' AND $f2[$i]->fam_div_authority!='') { $fam_items[$i]["fam_div_authority"] = $f2[$i]->fam_div_authority; }
					if($f1[$i]->fam_text=='' AND $f2[$i]->fam_text!='') { $fam_items[$i]["fam_text"] = $f2[$i]->fam_text; }
					if($f1[$i]->fam_text_source=='' AND $f2[$i]->fam_text_source!='') { $fam_items[$i]["fam_text_source"] = $f2[$i]->fam_text_source; }
				}
				for($i=0; $i<count($f1); $i++) {
					if(isset($fam_items[$i])) {
						$item_string='';
						foreach($fam_items[$i] as $key => $value) {
							$item_string .= $key."='".$value."',";
						}
						$item_string = substr($item_string,0,-1); // take off last comma

						//$qry = "UPDATE ".$data2Db->tree_prefix."family SET ".$item_string." WHERE fam_gedcomnumber ='".$f1[$i]->fam_gedcomnumber."'";
						$qry = "UPDATE humo_families SET ".$item_string."
							WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$f1[$i]->fam_gedcomnumber."'";
						$dbh->query($qry);
					}
				}

				// delete F's that belonged to identical right spouse(s)
				for($i=0; $i<count($f1); $i++) { // for each of the identical spouses
					//$qry = "DELETE FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$f2[$i]->fam_gedcomnumber."'";
					$qry = "DELETE FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$f2[$i]->fam_gedcomnumber."'";
					$dbh->query($qry);
					// CLEANUP: also delete this F from other tables where it may appear
					//$qry = "DELETE FROM ".$data2Db->tree_prefix."addresses WHERE address_family_id ='".$f2[$i]->fam_gedcomnumber."'";
					$qry = "DELETE FROM humo_addresses
						WHERE address_tree_id='".$tree_id."' AND address_family_id ='".$f2[$i]->fam_gedcomnumber."'";
					$dbh->query($qry);
					//$qry = "DELETE FROM ".$data2Db->tree_prefix."events WHERE event_family_id ='".$f2[$i]->fam_gedcomnumber."'";
					$qry = "DELETE FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_family_id ='".$f2[$i]->fam_gedcomnumber."'";
					$dbh->query($qry);
					//$qry = "DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$f2[$i]->fam_gedcomnumber."'";
					$qry = "DELETE FROM humo_connections
						WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$f2[$i]->fam_gedcomnumber."'";
					$dbh->query($qry);
				}
				// check for other spouses that may have to be added to relative merge string
				if(count($r_spouses) > count($f1)) { // right had more than the identical spouse(s). maybe they need merging
					$leftfam = explode(';',$result1Db->pers_fams);
					$rightfam = explode(';',$famstring);
					for($e=0; $e < count($leftfam); $e++) {
						//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$leftfam[$e]."'";
						$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$leftfam[$e]."'";
						$fam1 = $dbh->query($qry);
						$fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
						$sp_ged = $fam1Db->fam_woman;
						if($result1Db->pers_sexe == "F") { $sp_ged = $fam1Db->fam_man;   }
						//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$sp_ged."'";
						$qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$sp_ged."'";
						$spo1 = $dbh->query($qry);
						$spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
						if($spo1->rowCount() > 0) {
							for($f=0; $f < count($rightfam); $f++ ) {
								//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$rightfam[$f]."'";
								$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$rightfam[$f]."'";
								$fam2 = $dbh->query($qry);
								$fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
								$sp_ged = $fam2Db->fam_woman;
								if($result1Db->pers_sexe == "F") { $sp_ged = $fam2Db->fam_man;   }
								//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$sp_ged."'";
								$qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$sp_ged."'";
								$spo2 = $dbh->query($qry);
								$spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
								if($spo2->rowCount() > 0) {
									if($spo1Db->pers_lastname == $spo2Db->pers_lastname AND substr($spo1Db->pers_firstname,0,$merge_chars) == substr($spo2Db->pers_firstname,0,$merge_chars)) {
										$string1 = $spo1Db->pers_gedcomnumber.'@'.$spo2Db->pers_gedcomnumber.';';
										$string2 = $spo2Db->pers_gedcomnumber.'@'.$spo1Db->pers_gedcomnumber.';';
										// make sure this pair doesn't appear already in the string
										if(strstr($relatives_merge,$string1) === false AND strstr($relatives_merge,$string2) === false) {
											$relatives_merge .= $string1;
										}
										$dbh->query("UPDATE humo_settings SET setting_value ='".$relatives_merge."'
											WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
									}
								}
							}
						}
					}
				}
			}
		}

		if(!$result1Db->pers_fams OR $same_spouse==false) {
			// left has no fams or fams with different spouses than right -> add fams to left

			// add right's F to left's fams
			if($result1Db->pers_fams) {
				$fam = $result1Db->pers_fams.";".$result2Db->pers_fams;
			}
			else {
				$fam = $result2Db->pers_fams;
			}
			//$qry = "UPDATE ".$data2Db->tree_prefix."person SET pers_fams='".$fam."' WHERE pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
			$qry = "UPDATE humo_persons SET pers_fams='".$fam."'
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
			$dbh->query($qry);

			// in humo_family, under right's F, change fam_man/woman to left's I
			$self = "man";
			if($result1Db->pers_sexe == "F") { $self = "woman"; }

			//in all right's families (that are now moved to left!) change right's I to left's I
			$r_fams = explode(';',$result2Db->pers_fams);
			for($i=0;$i<count($r_fams);$i++) {
				//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$r_fams[$i]."'";
				$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$r_fams[$i]."'";
				$r_fm = $dbh->query($qry);
				$r_fmDb = $r_fm->fetch(PDO::FETCH_OBJ);
				//$qry = "UPDATE ".$data2Db->tree_prefix."family SET fam_".$self."='".$result1Db->pers_gedcomnumber."' WHERE fam_gedcomnumber='".$r_fams[$i]."'";
				$qry = "UPDATE humo_families SET fam_".$self."='".$result1Db->pers_gedcomnumber."'
					WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$r_fams[$i]."'";
				$dbh->query($qry);
			}

			// check for spouses to be added to relative merge string:
			if($result1Db->pers_fams AND $same_spouse==false) {
				$leftfam = explode(';',$result1Db->pers_fams);
				$rightfam = explode(';',$result2Db->pers_fams);
				for($e=0; $e < count($leftfam); $e++) {
					//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$leftfam[$e]."'";
					$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$leftfam[$e]."'";
					$fam1 = $dbh->query($qry);
					$fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
					$sp_ged = $fam1Db->fam_woman;
					if($result1Db->pers_sexe == "F") { $sp_ged = $fam1Db->fam_man;   }
					//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$sp_ged."'";
					$qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$sp_ged."'";
					$spo1 = $dbh->query($qry);
					$spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
					if($spo1->rowCount() > 0) {
						for($f=0; $f < count($rightfam); $f++ ) {
							//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$rightfam[$f]."'";
							$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$rightfam[$f]."'";
							$fam2 = $dbh->query($qry);
							$fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
							$sp_ged = $fam2Db->fam_woman;
							if($result1Db->pers_sexe == "F") { $sp_ged = $fam2Db->fam_man;   }
							//$qry = "SELECT * FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$sp_ged."'";
							$qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$sp_ged."'";
							$spo2 = $dbh->query($qry);
							$spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
							if($spo2->rowCount() > 0) {
								if($spo1Db->pers_lastname == $spo2Db->pers_lastname AND substr($spo1Db->pers_firstname,0,$merge_chars) == substr($spo2Db->pers_firstname,0,$merge_chars)) {
									$string1 = 	$spo1Db->pers_gedcomnumber.'@'.$spo2Db->pers_gedcomnumber.';';
									$string2 = 	$spo2Db->pers_gedcomnumber.'@'.$spo1Db->pers_gedcomnumber.';';
									// make sure this pair doesn't already exist in the string
									if(strstr($relatives_merge,$string1) === false AND strstr($relatives_merge,$string2) === false)	{
										$relatives_merge .= $string1;
									}
									$dbh->query("UPDATE humo_settings SET setting_value ='".$relatives_merge."'
										WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
								}
							}
						}
					}
				}
			}
		}
	}
	if($result2Db->pers_famc) {
		// if the two merged persons had a different parent set (e.i. parents aren't merged yet)
		// then in humo_family under right's parents' F, in fam_children, change right's I to left's I
		// (because right I will be deleted and as long as the double parents aren't merged we don't want errors
		// when accessing the children!

		//$parqry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$result2Db->pers_famc."'";
		$parqry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$result2Db->pers_famc."'";
		$parfam = $dbh->query($parqry);
		$parfamDb = $parfam->fetch(PDO::FETCH_OBJ);

		$children = $parfamDb->fam_children.";";
		// add ; at end for following manipulation
		// we have to search for "I45;" if we searched for I34 without semi colon then also I346 would give true!
		// since the last entry doesn't have a ; we have to temporarily add it for the search.

		if(!$result1Db->pers_famc OR ($result1Db->pers_famc AND $result1Db->pers_famc != $result2Db->pers_famc)) {
			// left has no parents or a different parent set (at least one parent not merged yet)
			// --> change right I for left I in right's parents' F
			$children = str_replace($result2Db->pers_gedcomnumber.";" , $result1Db->pers_gedcomnumber.";",$children);
			// check if to add to relatives merge string
			if($result1Db->pers_famc AND $result1Db->pers_famc != $result2Db->pers_famc) {
				// there is a double set of parents - these have to be merged by the user! Save in variables
				//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$result1Db->pers_famc."'";
				$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$result1Db->pers_famc."'";
				$par1 = $dbh->query($qry);
				$par1Db = $par1->fetch(PDO::FETCH_OBJ);
				//$qry = "SELECT * FROM ".$data2Db->tree_prefix."family WHERE fam_gedcomnumber ='".$result2Db->pers_famc."'";
				$qry = "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$result2Db->pers_famc."'";
				$par2 = $dbh->query($qry);
				$par2Db = $par2->fetch(PDO::FETCH_OBJ);
				// add the parents to string of surrounding relatives to be merged
				// to help later with exploding, sets are separated by ";" and left and right are separated by "@"
				if(isset($par1Db->fam_man) AND $par1Db->fam_man!='0' AND isset($par2Db->fam_man) AND $par2Db->fam_man!='0'
					AND $par1Db->fam_man != $par2Db->fam_man) { 
					// make sure none of the two fathers is N.N. and that this father is not merged already!
					$string1= $par1Db->fam_man.'@'.$par2Db->fam_man.";";
					$string2= $par2Db->fam_man.'@'.$par1Db->fam_man.";";
					// make sure this pair doesn't appear already in the string
					if(strstr($relatives_merge,$string1)===false AND strstr($relatives_merge,$string2)===false) {
						$relatives_merge .= $string1;
					}
				}
				elseif((!isset($par1Db->fam_man) OR $par1Db->fam_man=='0')  AND isset($par2Db->fam_man) AND $par2Db->fam_man!='0') {
					// left father is N.N. so move right father to left F
					//$dbh->query("UPDATE ".$data2Db->tree_prefix."family SET fam_man = '".$par2Db->fam_man."' WHERE fam_gedcomnumber ='".$result1Db->pers_famc."'");
					$dbh->query("UPDATE humo_families SET fam_man = '".$par2Db->fam_man."'
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$result1Db->pers_famc."'");
				}
				if(isset($par1Db->fam_woman) AND $par1Db->fam_woman!='0' AND isset($par2Db->fam_woman) AND $par2Db->fam_woman!='0'
					AND $par1Db->fam_woman != $par2Db->fam_woman) {
					// make sure none of the two mothers is N.N. and that this mother is not merged already!
					$string1= $par1Db->fam_woman.'@'.$par2Db->fam_woman.";";
					$string2= $par2Db->fam_woman.'@'.$par1Db->fam_woman.";";
					if(strstr($relatives_merge,$string1)===false AND strstr($relatives_merge,$string2)===false) {
						// make sure this pair doesn't appear already in the string
						$relatives_merge .= $string1;
					}
				}
				elseif((!isset($par1Db->fam_woman) OR $par1Db->fam_woman=='0')  AND isset($par2Db->fam_woman) AND $par2Db->fam_woman!='0') {
					// left mother is N.N. so move right mother to left F
					//$dbh->query("UPDATE ".$data2Db->tree_prefix."family SET fam_woman = '".$par2Db->fam_woman."' WHERE fam_gedcomnumber ='".$result1Db->pers_famc."'");
					$dbh->query("UPDATE humo_families SET fam_woman = '".$par2Db->fam_woman."'
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$result1Db->pers_famc."'");
				}
				$dbh->query("UPDATE humo_settings SET setting_value ='".$relatives_merge."'
					WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
			}
			if(!$result1Db->pers_famc) {
				// give left the famc of right
				//$qry = "UPDATE ".$data2Db->tree_prefix."person SET pers_famc ='".$result2Db->pers_famc."' WHERE pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
				$qry = "UPDATE humo_persons SET pers_famc ='".$result2Db->pers_famc."'
					WHERE fam_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$result1Db->pers_gedcomnumber."'";
				$dbh->query($qry);
			}
		}
		elseif ($result1Db->pers_famc AND $result1Db->pers_famc == $result2Db->pers_famc) {
			// same parent set (double children in one family) just remove right's I from F
			// we can use right's F since this is also left's F....
			$children = str_replace($result2Db->pers_gedcomnumber.";" , "",$children);
		}
		if(substr($children,-1)==";") { // if the added ';' is still there, remove it
			$children = substr($children,0,-1); // take off last ;
		}
		//
		//$qry = "UPDATE ".$data2Db->tree_prefix."family SET fam_children='".$children."' WHERE fam_gedcomnumber = '".$result2Db->pers_famc."'";
		$qry = "UPDATE humo_families SET fam_children='".$children."'
			WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$result2Db->pers_famc."'";
		$dbh->query($qry);
	}

	// PERSONAL DATA
	// default:
	// 1. if there is data for left only, or for left and right --> the left data is retained.
	// 2. if right has data and left hasn't --> right's data is transfered to left
	// in manual, duplicate and relatives merge this can be over-ruled by the admin with the radio buttons

	// for automatic merge see if data has to be transferred from right to left
	// (for manual, duplicate and relative merge this is done in the form with radio buttons by the user)
	$l_name='1'; $f_name='1'; $b_date='1'; $b_place='1'; $d_date='1'; $d_place='1'; $b_time='1'; $b_text='1'; $d_time='1'; $d_text='1';
	$d_cause='1'; $br_date='1'; $br_place='1'; $br_text='1'; $bp_date='1'; $bp_place='1'; $bp_text='1'; $crem='1';
	$reli='1'; $code='1'; $stborn='1'; $alive='1'; $c_name='1'; $patr='1'; $fav='1'; $n_text='1'; $text='1';

	if($mode=='automatic') {
		// the regular items for automatic mode
		// 2 = move text to left person.  3 = append right text to left text
		if($result1Db->pers_birth_date=='' AND $result2Db->pers_birth_date!='') { $b_date='2'; }
		if($result1Db->pers_birth_place=='' AND $result2Db->pers_birth_place!='') { $b_place='2'; }
		if($result1Db->pers_death_date=='' AND $result2Db->pers_death_date!='') { $d_date='2'; }
		if($result1Db->pers_death_place=='' AND $result2Db->pers_death_place!='') { $d_place='2'; }
		if($result1Db->pers_birth_time=='' AND $result2Db->pers_birth_time!='') { $b_time='2'; }
		if($result1Db->pers_birth_text=='' AND $result2Db->pers_birth_text!='') { $b_text='2'; }
		if($result1Db->pers_death_time=='' AND $result2Db->pers_death_time!='') { $d_time='2'; }
		if($result1Db->pers_death_text=='' AND $result2Db->pers_death_text!='') { $d_text='2'; }
		if($result1Db->pers_death_cause=='' AND $result2Db->pers_death_cause!='') { $d_cause='2'; }
		if($result1Db->pers_buried_date=='' AND $result2Db->pers_buried_date!='') { $br_date='2'; }
		if($result1Db->pers_buried_place=='' AND $result2Db->pers_buried_place!='') { $br_place='2'; }
		if($result1Db->pers_buried_text=='' AND $result2Db->pers_buried_text!='') { $br_text='2'; }
		if($result1Db->pers_bapt_date=='' AND $result2Db->pers_bapt_date!='') { $bp_date='2'; }
		if($result1Db->pers_bapt_place=='' AND $result2Db->pers_bapt_place!='') { $bp_place='2'; }
		if($result1Db->pers_bapt_text=='' AND $result2Db->pers_bapt_text!='') { $bp_text='2'; }
		if($result1Db->pers_religion=='' AND $result2Db->pers_religion!='') { $reli='2'; }
		if($result1Db->pers_own_code=='' AND $result2Db->pers_own_code!='') { $code='2'; }
		if($result1Db->pers_stillborn=='' AND $result2Db->pers_stillborn!='') { $stborn='2'; }
		if($result1Db->pers_alive=='' AND $result2Db->pers_alive!='') { $alive='2'; }
		if($result1Db->pers_callname=='' AND $result2Db->pers_callname!='') { $c_name='2'; }
		if($result1Db->pers_patronym=='' AND $result2Db->pers_patronym!='') { $patr='2'; }
		//if($result1Db->pers_favorite=='' AND $result2Db->pers_favorite!='') { $fav='2'; }
		if($result1Db->pers_name_text=='' AND $result2Db->pers_name_text!='') { $n_text='2'; }
		if($result1Db->pers_text=='' AND $result2Db->pers_text!='') { $text='2'; }
		if($result1Db->pers_cremation=='' AND $result2Db->pers_cremation!='') { $crem='2'; }
	}
	$this->check_regular('l_name',$l_name,'pers_lastname');
	$this->check_regular('f_name',$f_name,'pers_firstname');
	$this->check_regular('b_date',$b_date,'pers_birth_date');
	$this->check_regular('b_place',$b_place,'pers_birth_place');
	$this->check_regular('d_date',$d_date,'pers_death_date');
	$this->check_regular('d_place',$d_place,'pers_death_place');
	$this->check_regular('b_time',$b_time,'pers_birth_time');
	$this->check_regular_text('b_text',$b_text,'pers_birth_text');
	$this->check_regular('d_time',$d_time,'pers_death_time');
	$this->check_regular_text('d_text',$d_text,'pers_death_text');
	$this->check_regular('d_cause',$d_cause,'pers_death_cause');
	$this->check_regular('br_date',$br_date,'pers_buried_date');
	$this->check_regular('br_place',$br_place,'pers_buried_place');
	$this->check_regular_text('br_text',$br_text,'pers_buried_text');
	$this->check_regular('bp_date',$bp_date,'pers_bapt_date');
	$this->check_regular('bp_place',$bp_place,'pers_bapt_place');
	$this->check_regular_text('bp_text',$bp_text,'pers_bapt_text');
	$this->check_regular('reli',$reli,'pers_religion');
	$this->check_regular('code',$code,'pers_own_code');
	$this->check_regular('stborn',$stborn,'pers_stillborn');
	$this->check_regular('alive',$alive,'pers_alive');
	$this->check_regular('c_name',$c_name,'pers_callname');
	$this->check_regular('patr',$patr,'pers_patronym');
	$this->check_regular_text('n_text',$n_text,'pers_name_text');
	$this->check_regular_text('text',$text,'pers_text');
	//$this->check_regular('fav',$fav,'pers_favorite');
	$this->check_regular('crem',$crem,'pers_cremation');

	// check for posted event, address and source items (separate functions below process input from comparison form)
	if($mode != 'automatic') {
		$this->check_events($result1Db->pers_gedcomnumber,$result2Db->pers_gedcomnumber);
		$this->check_addresses($result1Db->pers_gedcomnumber,$result2Db->pers_gedcomnumber);
		$this->check_sources($result1Db->pers_gedcomnumber,$result2Db->pers_gedcomnumber);
	}
	else { // for automatic mode check for situation where right has event/source/address data and left not. In that case use right's.

		//$right_result=$dbh->query("SELECT * FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$result2Db->pers_gedcomnumber."'");
		$right_result=$dbh->query("SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$result2Db->pers_gedcomnumber."'");
		while($right_resultDb=$right_result->fetch(PDO::FETCH_OBJ)) {
			//$left_result=$dbh->query("SELECT * FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$result1Db->pers_gedcomnumber."'");
			$left_result=$dbh->query("SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$result1Db->pers_gedcomnumber."'");
			$foundleft=false;
			while($left_resultDb=$left_result->fetch(PDO::FETCH_OBJ)) {
				if($left_resultDb->event_kind == $right_resultDb->event_kind AND $left_resultDb->event_gedcom == $right_resultDb->event_gedcom) {
					// NOTE: if "event" or "name" we also check for sub-type (_AKAN, _HEBN, BARM etc) so as not to match different subtypes
					// this event from right wil not be copied to left - left already has this type event
					// so clear the database
					//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."events WHERE event_id ='".$right_resultDb->event_id."'");
					$dbh->query("DELETE FROM humo_events WHERE event_id ='".$right_resultDb->event_id."'");
					$foundleft = true;
				}
			}
			if($foundleft==false) { // left has no such type of event, so change right's I for left I at this event
				//$dbh->query("UPDATE ".$data2Db->tree_prefix."events SET event_person_id ='".$result1Db->pers_gedcomnumber."' WHERE event_id ='".$right_resultDb->event_id."'");
				$dbh->query("UPDATE humo_events
					SET event_person_id ='".$result1Db->pers_gedcomnumber."' WHERE event_id ='".$right_resultDb->event_id."'");
			}
		}

		// do same for sources and address (from connections table). no need here to differentiate between sources and addresses, all will be handled
		//$right_result=$dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$result2Db->pers_gedcomnumber."'");
		$right_result=$dbh->query("SELECT * FROM humo_connections
			WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$result2Db->pers_gedcomnumber."'");
		while($right_resultDb=$right_result->fetch(PDO::FETCH_OBJ)) {
			//$left_result=$dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$result1Db->pers_gedcomnumber."'");
			$left_result=$dbh->query("SELECT * FROM humo_connections
				WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$result1Db->pers_gedcomnumber."'");
			$foundleft=false;
			while($left_resultDb=$left_result->fetch(PDO::FETCH_OBJ)) {
				if($left_resultDb->connect_sub_kind == $right_resultDb->connect_sub_kind) {
					// NOTE: We check for sub-kind so as not to match different sub_kinds
					// this source/address sub_kind from right wil not be copied to left - left already has a source/address for this sub_kind
					// so clear right's data from the database
					//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_id ='".$right_resultDb->connect_id."'");
					$dbh->query("DELETE FROM humo_connections WHERE connect_id ='".$right_resultDb->connect_id."'");
					$foundleft = true;
				}
			}
			if($foundleft==false) { // left has no such sub_kind of source/address, so change right's I for left I at this sub_kind
				//$dbh->query("UPDATE ".$data2Db->tree_prefix."connections SET connect_connect_id ='".$result1Db->pers_gedcomnumber."' WHERE connect_id ='".$right_resultDb->connect_id."'");
				$dbh->query("UPDATE humo_connections SET connect_connect_id ='".$result1Db->pers_gedcomnumber."' WHERE connect_id ='".$right_resultDb->connect_id."'");
			}
		}
	}
	// now delete right I from humoX_person table
	//$qry = "DELETE FROM ".$data2Db->tree_prefix."person WHERE pers_gedcomnumber ='".$result2Db->pers_gedcomnumber."'";
	$qry = "DELETE FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$result2Db->pers_gedcomnumber."'";
	$dbh->query($qry);
	// CLEANUP: delete this person's I from any other tables that refer to this person (otherwise we will receive errors in humogen)
	//$qry = "DELETE FROM ".$data2Db->tree_prefix."addresses WHERE address_person_id ='".$result2Db->pers_gedcomnumber."'";
	$qry = "DELETE FROM humo_addresses WHERE address_tree_id='".$tree_id."' AND address_person_id ='".$result2Db->pers_gedcomnumber."'";
	$dbh->query($qry);
	//$qry = "DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_connect_id ='".$result2Db->pers_gedcomnumber."'";
	$qry = "DELETE FROM humo_connections WHERE connect_tree_id='".$tree_id."' AND connect_connect_id ='".$result2Db->pers_gedcomnumber."'";
	$dbh->query($qry);
	//$qry = "DELETE FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$result2Db->pers_gedcomnumber."'";
	$qry = "DELETE FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$result2Db->pers_gedcomnumber."'";
	$dbh->query($qry);
	// CLEANUP: This person's I may still exist in the humoX_events table under "event_event",
	// in case of birth/death declaration or bapt/burial witness. If so, change the gedcom to the left person's I:
	//$qry = "UPDATE ".$data2Db->tree_prefix."events SET event_event = '@".$result1Db->pers_gedcomnumber."@' WHERE event_event ='@".$result2Db->pers_gedcomnumber."@'";
	$qry = "UPDATE humo_events
		SET event_event = '@".$result1Db->pers_gedcomnumber."@'
		WHERE event_tree_id='".$tree_id."' AND event_event ='@".$result2Db->pers_gedcomnumber."@'";
	$dbh->query($qry);

	// remove from the relatives-to-merge pairs in the database any pairs that contain the deleted right person
	if(isset($relatives_merge)) {
		$temp_rel_arr = explode(";",$relatives_merge);
		$new_rel_string = "";
		for($x=0 ; $x < count($temp_rel_arr); $x++) {
			// one array piece is I354@I54. We DONT want to match "I35" or "I5" 
			// so to make sure we find the complete number we look for I354@ or for I345;
			if(strstr($temp_rel_arr[$x],$result2Db->pers_gedcomnumber."@")=== false AND 
				strstr($temp_rel_arr[$x].";",$result2Db->pers_gedcomnumber.";")=== false) {
					$new_rel_string .= $temp_rel_arr[$x].";";
			}
		}
		$relatives_merge = substr($new_rel_string,0,-1); // take off last ;
		/*
		$found1 = $result1Db->pers_gedcomnumber.'@'.$result2Db->pers_gedcomnumber.';';
		$found2 = $result2Db->pers_gedcomnumber.'@'.$result1Db->pers_gedcomnumber.';';
		if(strstr($relatives_merge,$found1) !== false) {
			$relatives_merge = str_replace($found1,'',$relatives_merge);
		}
		elseif(strstr($relatives_merge ,$found2) !== false) {
			$relatives_merge = str_replace($found2,'',$relatives_merge);
		}
		*/
		$dbh->query("UPDATE humo_settings SET setting_value ='".$relatives_merge."' WHERE setting_variable = 'rel_merge_".$data2Db->tree_prefix."'");
	}

	if(isset($_SESSION['dupl_arr_'.$data2Db->tree_prefix])) { //remove this pair from the dupl_arr array
		$found1 = $result1Db->pers_id.';'.$result2Db->pers_id;
		$found2 = $result2Db->pers_id.';'.$result1Db->pers_id;
		for($z=0;$z<count($_SESSION['dupl_arr_'.$data2Db->tree_prefix]);$z++) {
			if($_SESSION['dupl_arr_'.$data2Db->tree_prefix][$z] == $found1 OR $_SESSION['dupl_arr_'.$data2Db->tree_prefix][$z]==$found2) {
				//unset($_SESSION['dupl_arr'][$z]) ;
				array_splice($_SESSION['dupl_arr_'.$data2Db->tree_prefix],$z,1);
			}
		}
	}

	if($mode != 'automatic' AND $mode != 'relatives') {

		echo '<br>'.$name2.__(' was successfully merged into ').$name1.'<br><br>';  // john was successfully merged into jack
		$rela = explode(';',$relatives_merge);
		$rela = count($rela) - 1;
		if($rela >0) {
			printf(__('After this merge there are %d surrounding relatives to be checked for merging!'), $rela);

			echo '<br><br>';
			
			echo __('<b>You are strongly advised to move to "Relatives merge" mode to check all surrounding persons who may have to be checked for merging.</b><br>
While in "Relatives merge" mode, any persons who might need merging as a result of consequent merges will be added automatically.<br>
This is the easiest way to make sure you don\'t forget anyone.');
			echo '<br><br>';

			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			echo '<input type="Submit" style="font-weight:bold;font-size:120%" name="relatives" value="'.__('Relatives merge').'">';
			echo '</form>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			if(isset($_POST['left'])) { // manual merge
				echo '<input type="Submit" name="manual" value="'.__('Continue manual merge').'">';
			}
			else { // duplicate merge
				echo '<input type="Submit" name="duplicate_compare" value="'.__('Continue duplicate merge').'">';
			}
			echo '</form>';
		}
		else {
			echo '<br><form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="hidden" name="menu_admin" value="'.$menu_admin.'">';
			if(isset($_POST['left'])) { // manual merge
				echo '<input type="Submit" name="manual" value="'.__('Choose another pair').'">';
			}
			else { // duplicate merge
				echo '<input type="Submit" name="duplicate_compare" value="'.__('Continue with next pair').'">';
			}
			echo '</form>';
		}
	}	// end if not automatic}
}
//*********************************************************************************************************************************
//*********  function check_regular checks if data from the humoX_person table was marked (checked) in the comparison table  *****
//*********************************************************************************************************************************
function check_regular ($post_var,$auto_var,$mysql_var) {
	global $dbh, $language, $data2Db, $result1Db, $result2Db;
	if((isset($_POST[$post_var]) AND $_POST[$post_var]=='2') OR $auto_var=='2') {
		//$qry = "UPDATE ".$data2Db->tree_prefix."person SET ".$mysql_var." = '".$result2Db->$mysql_var."' WHERE pers_id ='".$result1Db->pers_id."'";
		$qry = "UPDATE humo_persons SET ".$mysql_var." = '".$result2Db->$mysql_var."'
			WHERE pers_id ='".$result1Db->pers_id."'";
		$dbh->query($qry);
	}
}
//*********************************************************************************************************************************
//*********  function check_regular_text checks if text data from the humoX_person table was marked (checked) in the comparison table  *****
//*********************************************************************************************************************************
function check_regular_text ($post_var,$auto_var,$mysql_var) {
	global $dbh, $tree_id, $language, $data2Db, $result1Db, $result2Db;
	if(isset($_POST[$post_var.'_r']) OR $auto_var=='2') {
		if(isset($_POST[$post_var.'_l'])) { // when not in automatic mode, this means we have to join the notes of left and right
			// If left or right has a @N34@ text entry we join the text as regular text.
			// We can't change the notes in humoX_texts because they could be used for other persons!
			if(substr($result1Db->$mysql_var,0,2)=='@N') {
				//$noteqry = $dbh->query("SELECT text_text FROM ".$data2Db->tree_prefix."texts WHERE text_gedcomnr = '".substr($result1Db->$mysql_var,1,-1)."'");
				$noteqry = $dbh->query("SELECT text_text FROM humo_texts
					WHERE text_tree_id='".$tree_id."' AND text_gedcomnr = '".substr($result1Db->$mysql_var,1,-1)."'");
				$noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
				$leftnote = $noteqryDb->text_text;
			}
			else {
				$leftnote = $result1Db->$mysql_var;
			}
			if(substr($result2Db->$mysql_var,0,2)=='@N') {
				//$noteqry = $dbh->query("SELECT text_text FROM ".$data2Db->tree_prefix."texts WHERE text_gedcomnr = '".substr($result2Db->$mysql_var,1,-1)."'");
				$noteqry = $dbh->query("SELECT text_text FROM humo_texts
					WHERE text_tree_id='".$tree_id."' AND text_gedcomnr = '".substr($result2Db->$mysql_var,1,-1)."'");
				$noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
				$rightnote = $noteqryDb->text_text;
			}
			else {
				$rightnote = $result2Db->$mysql_var;
			}
			//$qry = "UPDATE ".$data2Db->tree_prefix."person SET ".$mysql_var." = CONCAT('".$leftnote."',\"\n\",'".$rightnote."') WHERE pers_id ='".$result1Db->pers_id."'";
			$qry = "UPDATE humo_persons SET ".$mysql_var." = CONCAT('".$leftnote."',\"\n\",'".$rightnote."')
				WHERE pers_id ='".$result1Db->pers_id."'";
		}
		else {
			//$qry = "UPDATE ".$data2Db->tree_prefix."person SET ".$mysql_var." = '".$result2Db->$mysql_var."' WHERE pers_id ='".$result1Db->pers_id."'";
			$qry = "UPDATE humo_persons SET ".$mysql_var." = '".$result2Db->$mysql_var."'
				WHERE pers_id ='".$result1Db->pers_id."'";
		}
		//mysql_query($qry,$db);
		$dbh->query($qry);
	}
}
//****************************************************************************************************
//*********  function check_event checks if event were marked (checked) in the comparison table  *****
//****************************************************************************************************
function check_events($left_ged,$right_ged) {
	global $dbh, $language, $data2Db;
	$right_event_array='';
	//$left_events = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."events WHERE event_person_id ='".$left_ged."' ORDER BY event_kind ");
	$left_events = $dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$left_ged."' ORDER BY event_kind ");
	$right_events = $dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id ='".$right_ged."' ORDER BY event_kind ");
	if($right_events->rowCount() > 0) { //if right has no events it did not appear in the comparison table, so the whole thing is unnecessary
		while($right_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
			$right_event_array[$right_eventsDb->event_kind]="1"; // we need this to know whether to handle left
			if(isset($_POST['r_'.$right_eventsDb->event_kind.'_'.$right_eventsDb->event_id])) { // change right's I to left's I
				$dbh->query("UPDATE humo_events SET event_person_id ='".$left_ged."'
					WHERE event_id ='".$right_eventsDb->event_id."'");
			}
			else { // clean up database -> remove this entry altogether (IF IT EXISTS...)
				$dbh->query("DELETE FROM humo_events WHERE event_id ='".$right_eventsDb->event_id."' AND event_kind='".$right_eventsDb->event_kind."'");
			}
		}
		while($left_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
			if(isset($right_event_array[$left_eventsDb->event_kind]) AND $right_event_array[$left_eventsDb->event_kind]=="1"  AND !isset($_POST['l_'.$left_eventsDb->event_kind.'_'.$left_eventsDb->event_id])) {
				$dbh->query("DELETE FROM humo_events WHERE event_id ='".$left_eventsDb->event_id."' AND event_kind='".$left_eventsDb->event_kind."'");
			}
		}
	}
}
//****************************************************************************************************
//** function check_addresses checks if addresses were marked (checked) in the comparison table  *****
//****************************************************************************************************
function check_addresses($left_ged,$right_ged) {
	global $dbh, $tree_id, $language, $data2Db;
	//$left_address = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='".$left_ged."'");
	$left_address = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='".$left_ged."'");
	//$right_address = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='".$right_ged."'");
	$right_address = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='".$right_ged."'");
	if($right_address->rowCount() > 0) { //if right has no addresses it did not appear in the comparison table, so the whole thing is unnecessary
		while($left_addressDb = $left_address->fetch(PDO::FETCH_OBJ)) {
			if(!isset($_POST['l_address_'.$left_addressDb->connect_id])) {
				//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_id ='".$left_addressDb->connect_id."'");
				$dbh->query("DELETE FROM humo_connections
					WHERE connect_tree_id='".$tree_id."' AND connect_id ='".$left_addressDb->connect_id."'");
			}
		}
		while($right_addressDb = $right_address->fetch(PDO::FETCH_OBJ)) {
			if(isset($_POST['r_address_'.$right_addressDb->connect_id])) { // change right's I to left's I
				//$dbh->query("UPDATE ".$data2Db->tree_prefix."connections SET connect_connect_id ='".$left_ged."' WHERE connect_id ='".$right_addressDb->connect_id."'");
				$dbh->query("UPDATE humo_connections SET connect_connect_id ='".$left_ged."' WHERE connect_id ='".$right_addressDb->connect_id."'");
			}
			else { // clean up database -> remove this entry altogether (IF IT EXISTS...)
				//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_id ='".$right_addressDb->connect_id."'");
				$dbh->query("DELETE FROM humo_connections WHERE connect_id ='".$right_addressDb->connect_id."'");
			}
		}
	}
}
//****************************************************************************************************
//*********  function check_sources checks if sources were marked (checked) in the comparison table  *****
//****************************************************************************************************

function check_sources($left_ged,$right_ged) {
	global $dbh, $language, $data2Db;
	//$left_source = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='".$left_ged."'");
	$left_source = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='".$left_ged."'");
	//$right_source = $dbh->query("SELECT * FROM ".$data2Db->tree_prefix."connections WHERE LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='".$right_ged."'");
	$right_source = $dbh->query("SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='".$right_ged."'");
	if($right_source->rowCount() > 0) { //if right has no sources it did not appear in the comparison table, so the whole thing is unnecessary
		while($left_sourceDb = $left_source->fetch(PDO::FETCH_OBJ)) {
			if(!isset($_POST['l_source_'.$left_sourceDb->connect_id])) {
				//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_id ='".$left_sourceDb->connect_id."'");
				$dbh->query("DELETE FROM humo_connections WHERE connect_id ='".$left_sourceDb->connect_id."'");
			}
		}
		while($right_sourceDb = $right_source->fetch(PDO::FETCH_OBJ)) {
			if(isset($_POST['r_source_'.$right_sourceDb->connect_id])) { // change right's I to left's I
				//$dbh->query("UPDATE ".$data2Db->tree_prefix."connections SET connect_connect_id ='".$left_ged."' WHERE connect_id ='".$right_sourceDb->connect_id."'");
				$dbh->query("UPDATE humo_connections SET connect_connect_id ='".$left_ged."' WHERE connect_id ='".$right_sourceDb->connect_id."'");
			}
			else { // clean up database -> remove this entry altogether (IF IT EXISTS...)
				//$dbh->query("DELETE FROM ".$data2Db->tree_prefix."connections WHERE connect_id ='".$right_sourceDb->connect_id."'");
				$dbh->query("DELETE FROM humo_connections WHERE connect_id ='".$right_sourceDb->connect_id."'");
			}
		}
	}
}
//********************************************************************************************************
//*********  function popclean prepares a mysql output string for presentation with popup_merge.js *****
//********************************************************************************************************
function popclean($input) {
	$output = str_replace(array("\r\n","\n\r","\r","\n"),"<br>",htmlentities(addslashes($input),ENT_QUOTES));
	return $output;
}

} // *** End of class ***
?>