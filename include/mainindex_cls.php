<?php
class mainindex_cls{

function show_tree_index(){
	global $dbh, $tree_prefix_quoted, $dataDb, $selected_language, $treetext_name, $dirmark2, $bot_visit, $humo_option, $db_functions;

	echo '<script type="text/javascript">';
	echo 'checkCookie();';
	echo '</script>';

	// *** Can be used for extra box in lay-out ***
	echo '<div id="mainmenu_centerbox">';

	// *** Select family tree ***
	$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
	$num_rows = $datasql->rowCount();
	if ($num_rows>1 AND $humo_option["one_name_study"]=='n'){
		echo '<div id="mainmenu_left">';
			echo '<div class="mainmenu_bar fonts">'.__('Select a family tree').'</div>';
			// *** List of family trees ***
			echo $this->tree_list($datasql);
		echo '</div>';
	}

	$center_id="mainmenu_center";
	if ($num_rows<=1 OR $humo_option["one_name_study"]=='y') $center_id="mainmenu_center_alt";
	if($humo_option["one_name_study"]=='n') {
		echo '<div id="'.$center_id.'" class="style_tree_text fonts">';

		// *** Just for sure, probably not necessary here: re-get selected family tree data ***
		@$dataDb=$db_functions->get_tree($tree_prefix_quoted);

		// *** Show name of selected family tree ***
		echo '<div class="mainmenu_bar fonts">';
			if ($num_rows>1){ echo __('Selected family tree').': '; }
			// *** Variable $treetext_name used from menu.php ***
			$treetext=show_tree_text($_SESSION['tree_prefix'], $selected_language);
			echo $treetext['name'];
		echo '</div>';

		if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){
			//
		}
		else{
			if ($tree_prefix_quoted=='' OR $tree_prefix_quoted=='EMPTY'){
				echo '<h2><a href="'.CMS_ROOTPATH.'login.php">'.__('Select another family tree, or login for the selected family tree.').'</a></h2>';
			}
			else{
				// *** Date and number of persons/ families ***
				echo ' <i>'.$this->tree_data().'</i><br>';
				if($this->tree_data()!="") {echo $dirmark2;}

				// *** Owner genealogy ***
				echo $this->owner();

				// *** Prepare mainmenu text and source ***
				$treetext=show_tree_text($tree_prefix_quoted, $selected_language);
				// *** Show mainmenu text ***
				$mainmenu_text=$treetext['mainmenu_text'];
				if ($mainmenu_text!='') echo '<p>'.nl2br($mainmenu_text).$dirmark2.'</p>';
				// *** Show mainmenu source ***
				$mainmenu_source=$treetext['mainmenu_source'];
				if ($mainmenu_source!='') echo '<p>'.nl2br($mainmenu_source).$dirmark2.'</p>';
				if ($mainmenu_text=='' AND $mainmenu_source=='') echo '<br>';

				// Send output to browser immediately for large family trees.
				ob_flush();
				flush(); // for IE

				//*** Most frequent names ***
				echo '<br>'.$this->last_names().$dirmark2;

				// Send output to browser immediately for large family trees.
				ob_flush();
				flush(); // for IE

				// *** Alphabet line ***
				echo '<br>'.$this->alphabet().$dirmark2.'<br>';

				//*** Today in history ***
				if ($humo_option["today_in_history_show"]=='y')
					echo '<br>'.$this->today_in_history();

				// *** Homepage favourites ***
				echo '<br>'.$this->extra_links();
			}
		}
	echo '</div>';
	}

	else {
		echo '<div id="'.$center_id.'" class="style_tree_text fonts">';
		echo '<br><br><br><br><span style="font-size:200%">'.__('One Name Study of the name').': </span><span style="font-weight:bold;font-size:250%">'.$humo_option["one_name_thename"].'</span>';;
		echo '</div>';
	}

	echo '<div id="mainmenu_right" class="fonts">';
		echo '<div class="mainmenu_bar fonts">'.__('Search').'</div>';
		// *** search ***
		if (!$bot_visit){
			$this->search_box();
		}
	echo '</div>';

	echo '</div>'; // end of center_box	
}

// *** List family trees ***
function tree_list($datasql){
	global $dbh, $humo_option, $uri_path, $user, $language, $selected_language;
	$text='';
	while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){

		// *** Check is family tree is shown or hidden for user group ***
		$hide_tree_array=explode(";",$user['group_hide_trees']);
		$hide_tree=false; if (in_array($dataDb->tree_id, $hide_tree_array)) $hide_tree=true;
		if ($hide_tree==false){
			$treetext=show_tree_text($dataDb->tree_prefix, $selected_language);
			$treetext_name=$treetext['name'];

			// *** Name family tree ***
			if (isset($_SESSION['tree_prefix']) AND $_SESSION['tree_prefix']==$dataDb->tree_prefix){
				$tree_name='<span class="tree_link fonts">'.$treetext_name.'</span><br>';
			}
			else{
				if (CMS_SPECIFIC=='Joomla'){
					$path_tmp='index.php?option=com_humo-gen&amp;database='.$dataDb->tree_prefix;
				}
				// *** url_rewrite ***
				elseif ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path is made in header.php ***
					$path_tmp=$uri_path.'tree_index/'.$dataDb->tree_prefix.'/';
				}
				else{
					$path_tmp='index.php?database='.$dataDb->tree_prefix;
				}

				$tree_name='<span class="tree_link fonts">';
				$tree_name.='<a href="'.$path_tmp.'">'.$treetext_name.'</a>';
				$tree_name.='</span><br>';
			}
			// *** Show empty line ***
			if ($dataDb->tree_prefix=='EMPTY'){ $tree_name='<br>'; }
			$text.=$tree_name;

		}		// end of family tree check

	}

	// *** Use scroll scrollbar for long list of family trees ***
	//$text='<div style="max-height:240px; overflow-x: auto;">'.$text.'</div>';

	echo $text;
}

// *** Family tree data ***
function tree_data(){
	global $dataDb, $language;
	$tree_date=$dataDb->tree_date;

	$month=''; // *** empty date ***
	if (substr($tree_date,5,2)=='01'){ $month=' '.__('jan').' ';}
	if (substr($tree_date,5,2)=='02'){ $month=' '.__('feb').' ';}
	if (substr($tree_date,5,2)=='03'){ $month=' '.__('mar').' ';}
	if (substr($tree_date,5,2)=='04'){ $month=' '.__('apr').' ';}
	if (substr($tree_date,5,2)=='05'){ $month=' '.__('may').' ';}
	if (substr($tree_date,5,2)=='06'){ $month=' '.__('jun').' ';}
	if (substr($tree_date,5,2)=='07'){ $month=' '.__('jul').' ';}
	if (substr($tree_date,5,2)=='08'){ $month=' '.__('aug').' ';}
	if (substr($tree_date,5,2)=='09'){ $month=' '.__('sep').' ';}
	if (substr($tree_date,5,2)=='10'){ $month=' '.__('oct').' ';}
	if (substr($tree_date,5,2)=='11'){ $month=' '.__('nov').' ';}
	if (substr($tree_date,5,2)=='12'){ $month=' '.__('dec').' ';}

	//$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4);
	$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4)." ".substr($tree_date,11,5);
	//return __('Latest update:').' '.$tree_date.', '.$dataDb->tree_persons.' '.__('persons').", ".$dataDb->tree_families.' '.__('families').".";
	return __('Latest update:').' '.$tree_date.', '.$dataDb->tree_persons.' '.__('persons').", ".$dataDb->tree_families.' '.__('families').'. <a href="'.CMS_ROOTPATH.'statistics.php">'.__('More statistics').'.</a>';
}

// *** Owner family tree ***
function owner(){
	global $language, $dataDb;
	$tree_owner='';

	if ($dataDb->tree_owner){
		$tree_owner=__('Owner family tree:').' ';
		// *** Show owner e-mail address ***
		if ($dataDb->tree_email){
			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=mailform';
			}
			else{
				$path_tmp=CMS_ROOTPATH.'mailform.php';
			}
			$tree_owner.='<a href="'.$path_tmp.'">'.$dataDb->tree_owner."</a><br>\n";
		}
		else{
			$tree_owner.=$dataDb->tree_owner."<br>\n";
		}
	}
	return $tree_owner;
}

//*** Most frequent names ***
function last_names(){
	global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $maxcols;

	// MAIN SETTINGS
	$maxcols = 2; // number of name&nr colums in table. For example 3 means 3x name col + nr col
	$maxnames = 8;
	//$table2_width="500";

	function tablerow($nr,$lastcol=false) {    
		// displays one set of name & nr column items in the row
		// $nr is the array number of the name set created in function last_names
		// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
		global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names;
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
		}
		echo '<td class="namelst">';
		if(isset($freq_last_names[$nr])) { 
			$top_pers_lastname=''; 	if ($freq_pers_prefix[$nr]){
				$top_pers_lastname=str_replace("_", " ", $freq_pers_prefix[$nr]); }
			$top_pers_lastname.=$freq_last_names[$nr];
			if ($user['group_kindindex']=="j"){
				echo '<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("_", " ", $freq_pers_prefix[$nr]).str_replace("&", "|", $freq_last_names[$nr]); 
			}
			else{
				$top_pers_lastname=$freq_last_names[$nr];
				if ($freq_pers_prefix[$nr]){ $top_pers_lastname.=', '.str_replace("_", " ", $freq_pers_prefix[$nr]); }
				echo '<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("&", "|", $freq_last_names[$nr]);
				if ($freq_pers_prefix[$nr]){ echo '&amp;pers_prefix='.$freq_pers_prefix[$nr]; }
				else{ echo '&amp;pers_prefix=EMPTY'; }
			}
			echo '&amp;part_lastname=equals">'.$top_pers_lastname."</a>";
		}
		else echo '~';
		echo '</td>';
		
		if($lastcol==false)  echo '<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
		else echo '</td><td class="namenr" style="text-align:center">'; // no thick border
		
		if(isset($freq_last_names[$nr])) echo $freq_count_last_names[$nr];
		else echo '~';
		echo '</td>';
	}

	function last_names($max) {
		global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols;
		/*
		$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
			GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,".$max;
		*/

		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$personqry="SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
			GROUP BY pers_lastname, pers_prefix ORDER BY count_last_names DESC LIMIT 0,".$max;

		$person=$dbh->query($personqry);
		while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){ 
			$freq_last_names[]=$personDb->pers_lastname;  
			$freq_pers_prefix[]=$personDb->pers_prefix;
			$freq_count_last_names[]=$personDb->count_last_names;
		}
		$row = round(count($freq_last_names)/$maxcols);

		for ($i=0; $i<$row; $i++){
			echo '<tr>';
			for($n=0;$n<$maxcols;$n++) {
				if($n == $maxcols-1) {
					tablerow($i+($row*$n),true); // last col
				}
				else {
					tablerow($i+($row*$n)); // other cols
				}
			}
			echo '</tr>';
		}
		return $freq_count_last_names[0];
	}

	//	echo __('Most frequent surnames:')."<br>";
	echo '<div class="mainmenu_bar fonts">'.__('Names').'</div>';

	//echo '<table width=500 class="humo nametbl" align="center">';
	echo '<table width="90%" class="humo nametbl" align="center">';

	// *** Override td style ***
	echo '
	<style>
	table.humo td, table.relmenu td {
		padding-top: 0px;
		padding-bottom: 0px;
	}
	</style>';

	echo '<tr class="table_headline">';
	$col_width = ((round(100/$maxcols))-6)."%";
	for($x=1; $x<$maxcols;$x++) {
		echo '<td width="'.$col_width.'"><b>'.__('Surname').'</b></td><td style="border-right-width:3px;width:6%"><b>'.__('Total').'</b></td>';  
	}
	echo '<td width="'.$col_width.'"><b>'.__('Surname').'</b></td><td width:6%"><b>'.__('Total').'</b></td>';
	echo '</tr>';

	$baseperc = last_names($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)
	//echo '<tr><td colspan="2" style="border-right-width:3px;"><a href="javascript:;" onClick=window.open("frequent_surnames.php","","width=970,height=600,top=40,left=60,scrollbars=yes");>'.__('More frequent surnames').'</a></td>';
	echo '<tr class=table_headline><td colspan="2" style="border-right-width:3px;"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_surnames">'.__('More frequent surnames').'</a></td>';
	//echo '<td colspan="2" style="border-right-width:3px;"><a href="javascript:;" onClick=window.open("frequent_firstnames.php","","width=1050,height=600,top=50,left=60,scrollbars=yes");>'.
	//__('Frequent first names').'</a></td>';
	//echo '<td colspan="2" style="border-right-width:3px;"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_firstnames">'.__('Frequent first names').'</a></td>';
	echo '<td colspan="2"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_firstnames">'.__('Frequent first names').'</a></td>';
	//echo '<td colspan="2"><a href="'.CMS_ROOTPATH.'statistics.php">'.__('Statistics').'</a></td></tr>';
	echo '</tr>';
	echo '</table>';

	echo '
	<script>
	var tbl = document.getElementsByClassName("nametbl")[0];
	var rws = tbl.rows; var baseperc = '.$baseperc.';
	for(var i = 0; i < rws.length; i ++) {
		var tbs =  rws[i].getElementsByClassName("namenr");
		var nms = rws[i].getElementsByClassName("namelst");
	  for(var x = 0; x < tbs.length; x ++) {
		var percentage = parseInt(tbs[x].innerHTML, 10);
		percentage = (percentage * 100)/baseperc;  
		if(percentage > 0.1) {
		   nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
		   nms[x].style.backgroundSize = percentage + "%" + " 100%";
		   nms[x].style.backgroundRepeat = "no-repeat";
		   nms[x].style.color = "rgb(0, 140, 200)";
		}
	  }
	}
	</script>';

}

// *** Search field ***
function search_box(){
	global $language, $dbh, $humo_option;

	// *** Reset search field if a new genealogy is selected ***
	$reset_search=false;
	if (isset($_SESSION["save_search_tree_prefix"])){
		if ($_SESSION["save_search_tree_prefix"]!=$_SESSION['tree_prefix']){ $reset_search=true; }
	}
	if ($reset_search){
		unset($_SESSION["save_firstname"]);
		unset($_SESSION["save_lastname"]);
		unset ($_SESSION["save_part_firstname"]);
		unset($_SESSION["save_part_lastname"]);
		unset($_SESSION["save_search_database"]);
	}
	//*** Search screen ***
	$pers_firstname='';
	if (isset($_SESSION["save_firstname"])){ $pers_firstname=$_SESSION["save_firstname"]; }
	$part_firstname='';
	if (isset($_SESSION["save_part_firstname"])){
		$part_firstname=$_SESSION["save_part_firstname"]; }
	$pers_lastname='';

	if (isset($_SESSION["save_lastname"])){ $pers_lastname=$_SESSION["save_lastname"]; }
	$part_lastname='';
	if (isset($_SESSION["save_part_lastname"])){
		$part_lastname=$_SESSION["save_part_lastname"]; }
	$search_database='tree_selected';

	if (isset($_SESSION["save_search_database"])){ $search_database=$_SESSION["save_search_database"]; }

	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list';
	}
	else{
		$path_tmp=CMS_ROOTPATH.'list.php';
	}
	echo '<form method="post" action="'.$path_tmp.'">';

	if($humo_option['one_name_study']=='n') { echo __('Enter name or part of name').'<br>'; }
	else { echo __('Enter private name').'<br>'; }
	//echo '<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';
	
	echo '<input type="hidden" name="index_list" value="quicksearch">';
	$quicksearch='';
	if (isset($_POST['quicksearch'])){
		//$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
		$quicksearch=safe_text_show($_POST['quicksearch']);
		$_SESSION["save_quicksearch"]=$quicksearch;
	}
	if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
	echo '<p><input type="text" name="quicksearch" value="'.$quicksearch.'" size="30" pattern=".{3,}" title="'.__('Minimum: 3 characters.').'"></p>';

	// Check if there are multiple family trees.
	$datasql2 = $dbh->query("SELECT * FROM humo_trees");
	$num_rows2 = $datasql2->rowCount();
	if ($num_rows2>1 AND $humo_option['one_name_study']=='n'){
		$checked=''; if ($search_database=="tree_selected"){ $checked='checked'; }
		echo '<p><input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.__('Selected family tree').'<br>';
		//$checked=''; if ($search_database=="all_databases"){ $checked='checked'; }
		$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
		echo '<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees').'<br>';
		$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
		echo '<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree').'</p>';
	}
	if ($num_rows2>1 AND $humo_option['one_name_study']=='y'){
		echo '<input type="hidden" name="search_database" value="all_trees">';
	}
	echo '<p><input type="submit" value="'.__('Search').'"></p>';
	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1&index_list=search';
	}
	else{
		$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1&index_list=search';
	}
	echo '<p><a href="'.$path_tmp.'">'.__('Advanced search').'</a></p>';

	echo "</form>\n";
}

// *** Favourites ***
function extra_links(){
	global $dbh, $tree_id, $humo_option, $uri_path;

	// *** Check if there are extra links ***
	$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
	@$num_rows = $datasql->rowCount();
	if ($num_rows>0){
		while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			$item = explode("|",$dataDb->setting_value);
			$pers_own_code[] = $item[0];
			$link_text[] = $item[1];
			$link_order[] = $dataDb->setting_order;
		}
		include_once(CMS_ROOTPATH.'include/person_cls.php');
		$person=$dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_own_code NOT LIKE ''");
		while(@$personDb=$person->fetch(PDO::FETCH_OBJ)){
			if (in_array ($personDb->pers_own_code,$pers_own_code) ){
				if (CMS_SPECIFIC=='Joomla'){
					$path_tmp='index.php?option=com_humo-gen&amp;task=family&amp;database='.$_SESSION['tree_prefix'].
						'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
				}
				elseif ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path is generated in header.php ***
					$path_tmp=$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$personDb->pers_indexnr.
						'/'.$personDb->pers_gedcomnumber.'/';
				}
				else{
					$path_tmp= CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
						'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
				}
				$person_cls = New person_cls;
				$name=$person_cls->person_name($personDb);
				$text_nr=array_search ($personDb->pers_own_code,$pers_own_code);

				$link_order2=$link_order[$text_nr];
				$link_text2[$link_order2]='<a href="'.$path_tmp.'">'.$name["standard_name"].'</a> '.$link_text[$text_nr];
			}
		}

		// *** Show links ***
		if (isset($link_text2)){
			echo '<div class="mainmenu_bar fonts">'.__('Favourites').'</div>';
			//for($i=1; $i<=count($link_text2); $i++){
			for($i=1; $i<=$num_rows; $i++){
				if (isset($link_text2[$i])) echo $link_text2[$i]."<br>\n";
			}
		}

	}
}

// *** Alphabet line ***
function alphabet(){
	global $dbh, $tree_id, $language, $user, $humo_option, $uri_path;

	//*** Find first first_character of last name ***
	echo __('Surnames Index:')."<br>\n";
	$personqry="SELECT UPPER(LEFT(pers_lastname,1)) as first_character FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' GROUP BY first_character";

	// *** If "van Mons" is selected, also check pers_prefix ***
	if ($user['group_kindindex']=="j"){
		$personqry="SELECT UPPER(LEFT(CONCAT(pers_prefix,pers_lastname),1)) as first_character FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' GROUP BY first_character";
	}
	@$person=$dbh->query($personqry);
	while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;database='.
			$_SESSION['tree_prefix'].'&amp;last_name='.$personDb->first_character;
		}
		elseif ($humo_option["url_rewrite"]=="j"){
			// *** url_rewrite ***
			// *** $uri_path is gemaakt in header.php ***
			$path_tmp=$uri_path.'list_names/'.$_SESSION['tree_prefix'].'/'.$personDb->first_character.'/';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list_names.php?database='.$_SESSION['tree_prefix'].'&amp;last_name='.$personDb->first_character;
		}
		echo ' <a href="'.$path_tmp.'">'.$personDb->first_character.'</a>';
	}

	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;pers_lastname=...';
	} else{
		$path_tmp=CMS_ROOTPATH.'list.php?pers_lastname=...';
	}
	echo ' <a href="'.$path_tmp. '">'.__('Other')."</a>\n";

	$person="SELECT pers_patronym FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_patronym LIKE '_%' AND pers_lastname =''";
	@$personDb=$dbh->query($person);
	if ($personDb->rowCount()>0) {
		echo ' <a href="'.CMS_ROOTPATH.'list.php?index_list=patronym">'.__('Patronyms').'</a>';
	}
}

function today_in_history(){
	global $dbh, $dataDb;
	include_once(CMS_ROOTPATH."include/person_cls.php");
	include_once(CMS_ROOTPATH."include/language_date.php");
	include_once(CMS_ROOTPATH."include/date_place.php");

	$today = date('j').' '.strtoupper(date ("M"));
	$today2 = '0'.date('j').' '.strtoupper(date ("M"));
	$count_privacy=0;

	// *** Check user group is restricted sources can be shown ***
	// *** Calculate present date, month and year ***
	$sql = "SELECT * FROM humo_persons WHERE pers_tree_id = :tree_id
		AND (
			substring( pers_birth_date,1,6) = :today OR substring( pers_birth_date, 1,6 ) = :today2
			OR substring( pers_bapt_date,1,6) = :today OR substring( pers_bapt_date, 1,6 ) = :today2
			OR substring( pers_death_date,1,6) = :today OR substring( pers_death_date, 1,6 ) = :today2
		)
		ORDER BY substring(pers_birth_date,-4) DESC
		LIMIT 0,30
		";
	try {
		$birth_qry = $dbh->prepare( $sql );
		$birth_qry->bindValue(':tree_id', $dataDb->tree_id, PDO::PARAM_STR);
		$birth_qry->bindValue(':today', $today, PDO::PARAM_STR);
		$birth_qry->bindValue(':today2', $today2, PDO::PARAM_STR);
		$birth_qry->execute();
	}catch (PDOException $e) {
		//echo $e->getMessage() . "<br/>";
	}

	// *** Save results in an array, so it's possible to order the results by date ***
	while ($record=$birth_qry->fetch(PDO::FETCH_OBJ)){
		$person_cls = New person_cls;
		$name=$person_cls->person_name($record);
		$person_cls->construct($record);
		if ($person_cls->privacy==''){
			if (trim(substr($record->pers_birth_date,0,6))==$today OR substr($record->pers_birth_date,0,6)==$today2){
				$history['order'][]=substr($record->pers_birth_date,-4);
				$history['date'][]='<td>'.date_place($record->pers_birth_date,'').'</td><td>'.__('born').'</td>';
			}
			elseif (trim(substr($record->pers_bapt_date,0,6))==$today OR substr($record->pers_bapt_date,0,6)==$today2){
				$history['order'][]=substr($record->pers_bapt_date,-4);
				$history['date'][]='<td>'.date_place($record->pers_bapt_date,'').'</td><td>'.__('baptised').'</td>';
			}
			else{
				$history['order'][]=substr($record->pers_death_date,-4);
				$history['date'][]='<td>'.date_place($record->pers_death_date,'').'</td><td>'.__('died').'</td>';
			}
			$history['name'][]='<td><a href="'.CMS_ROOTPATH.'family.php?id='.$record->pers_indexnr.'&amp;main_person='.$record->pers_gedcomnumber.'">'.$name["standard_name"].'</a></td>';
		}
		else
			$count_privacy++;
	}

	echo '<div class="mainmenu_bar fonts">'.__('Today in history').'</div>';

	echo '<div style="max-height:200px; overflow-x: auto;">';
	echo '<table width="90%" class="humo nametbl" align="center">';
		// *** Override td style ***
		echo '
		<style>
		table.humo td, table.relmenu td {
			padding-top: 0px;
			padding-bottom: 0px;
		}
		</style>';

		//echo '<tr class="table_headline">';
		//	echo '<td colspan="3"><b>'.__('Today in history').'</b></td>';
		//echo '</tr>';

		echo '<tr class="table_headline">';
			echo '<td><b>'.__('Date').'</b></td><td><b>'.__('Event').'</b></td><td><b>'.__('Name').'</b></td>';
		echo '</tr>';

		if (isset($history['date'])){
			array_multisort($history['order'], SORT_DESC, $history['date'], $history['name']);

			for ($i=0; $i<=count($history['date'])-1; $i++){
				echo '<tr>';
					echo $history['date'][$i];
					echo $history['name'][$i];
				echo '</tr>';
			}
		}

		if ($count_privacy)
			echo '<tr><td colspan="3">'.$count_privacy.__(' persons are not shown due to privacy settings').'</td></tr>';
	echo '</table>';
	echo '<br></div>';
}

function show_footer(){
	global $bot_visit;
	echo '<br><div class="humo_version">';
		// *** Show owner of family tree ***
		echo $this->owner();

		// *** Show HuMo-gen link ***
		printf(__('This database is made by %s, a freeware genealogical  program'), '<a href="http://www.humo-gen.com">HuMo-gen</a>');
		//echo ' ('.$humo_option["version"].').<br>';
		echo '.<br>';

		// *** Show European cookie information ***
		if (!$bot_visit){ printf(__('European law: %s HuMo-gen cookie information'),'<a href="info_cookies.php">'); }
		echo '</a>';
	echo '</div>';
}

}
?>