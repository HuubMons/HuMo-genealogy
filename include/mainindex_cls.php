<?php
class mainindex_cls{

function show_tree_index(){
	global $dbh, $tree_prefix_quoted, $dataDb, $selected_language, $treetext_name, $dirmark2, $bot_visit, $humo_option, $db_functions;

	include_once(CMS_ROOTPATH."include/person_cls.php");

	echo '<script type="text/javascript">';
	echo 'checkCookie();';
	echo '</script>';

	// *** Option to only index CMS page for bots ***
	if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){
		$left_column='';

		$temp=$this->selected_family_tree();
		$center_column=$temp;

		$right_column='';
	}
	// *** Check visitor/ user permissions ***
	elseif ($tree_prefix_quoted=='' OR $tree_prefix_quoted=='EMPTY'){
		$left_column='';

		$temp=$this->selected_family_tree();
		$temp.='<h2><a href="'.CMS_ROOTPATH.'login.php">'.__('Select another family tree, or login for the selected family tree.').'</a></h2>';
		$center_column=$temp;

		$right_column='';
	}
	// *** One name study page ***
	elseif ($humo_option["one_name_study"]!='n') {
		$left_column='';

		// *** Show one name study homepage ***
		$temp='<br><br><br><br><span style="font-size:200%">'.__('One Name Study of the name').': </span><span style="font-weight:bold;font-size:250%">'.$humo_option["one_name_thename"].'</span>';;
		$center_column=$temp;

		// *** Right column: search module ***
		$temp='<div class="mainmenu_bar fonts">'.__('Search').'</div>';
		$temp.=$this->search_box();
		$right_column=$temp;
	}
	// *** Standard family tree template page ***
	else{
		$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
		while (@$data2Db=$datasql->fetch(PDO::FETCH_OBJ)){
			$item = explode("|",$data2Db->setting_value);
			if ($item[0]=='active'){
				//$module_status[] = $item[0];

				$module_column[] = $item[1];

				$module_item[] = $item[2];

				if (isset($item[3]))
					$module_option_1[]=$item[3];
				else
					$module_option_1[]='';

				if (isset($item[4]))
					$module_option_2[]=$item[4];
				else
					$module_option_2[]='';

				$module_order[] = $data2Db->setting_order;
			}
		}
		$left_column=''; $center_column=''; $right_column='';

		$nr_modules=0; if (isset($module_order)) $nr_modules=count($module_order);
		$nr_modules--;
		for($i=0; $i<=$nr_modules; $i++){
			$temp='';

			// *** Select family tree ***
			if ($module_item[$i]=='select_family_tree'){
//move these 2 rows at top of template script?
				$data2sql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
				$num_rows = $data2sql->rowCount();
				//if ($num_rows>1 AND $humo_option["one_name_study"]=='n'){
				if ($num_rows>1){
					$temp.='<div class="mainmenu_bar fonts">'.__('Select a family tree').'</div>';
					// *** List of family trees ***
					$temp.=$this->tree_list($data2sql);
				}
			}

			// *** Homepage favourites ***
			if ($module_item[$i]=='favourites'){ $temp.=$this->extra_links(); }

			// *** Just for sure, probably not necessary here: re-get selected family tree data ***
			@$dataDb=$db_functions->get_tree($tree_prefix_quoted);
			//*** Today in history ***
			if ($module_item[$i]=='history') $temp.=$this->today_in_history($module_option_1[$i]);

			// *** Alphabet line ***
			if ($module_item[$i]=='alphabet'){ $temp.=$this->alphabet().$dirmark2; }

			//*** Most frequent names ***
			if ($module_item[$i]=='names'){ $temp.=$this->last_names($module_option_1[$i], $module_option_2[$i]).$dirmark2; }

			// *** Show name of selected family tree ***
			if ($module_item[$i]=='selected_family_tree'){
				$temp.=$this->selected_family_tree();

// use seperate modules for these items?
				// *** Date and number of persons/ families ***
				$temp.=' <i>'.$this->tree_data().'</i><br>';
				if($this->tree_data()!="") { $temp.=$dirmark2; }

				// *** Owner genealogy ***
				$temp.=$this->owner();

				// *** Prepare mainmenu text and source ***
				$treetext=show_tree_text($dataDb->tree_id, $selected_language);
				// *** Show mainmenu text ***
				//$mainmenu_text=$treetext['mainmenu_text']; if ($mainmenu_text!='') $temp.='<p>'.nl2br($mainmenu_text).$dirmark2.'</p>';
				$mainmenu_text=$treetext['mainmenu_text']; if ($mainmenu_text!='') $temp.='<br><br>'.nl2br($mainmenu_text).$dirmark2;
				// *** Show mainmenu source ***
				//$mainmenu_source=$treetext['mainmenu_source']; if ($mainmenu_source!='') $temp.='<p>'.nl2br($mainmenu_source).$dirmark2.'</p>';
				$mainmenu_source=$treetext['mainmenu_source']; if ($mainmenu_source!='') $temp.='<br><br>'.nl2br($mainmenu_source).$dirmark2;
				//if ($mainmenu_text=='' AND $mainmenu_source=='') $temp.='<br>';
			}

			// *** Search ***
			if ($module_item[$i]=='search'){
				$temp.='<div class="mainmenu_bar fonts">'.__('Search').'</div>';
				if (!$bot_visit){ $temp.=$this->search_box(); }
			}

			// *** Random photo ***
			if ($module_item[$i]=='random_photo'){
				$temp.='<div class="mainmenu_bar fonts">'.__('Random photo').'</div>';
				if (!$bot_visit){ $temp.=$this->random_photo(); }
			}

			// *** Text ***
			if ($module_item[$i]=='text'){
				if ($module_option_1[$i]) $temp.='<div class="mainmenu_bar fonts">'.$module_option_1[$i].'</div>';
				$temp.=$module_option_2[$i];
			}

			// *** CMS page ***
			if ($module_item[$i]=='cms_page'){
				$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='".$module_option_1[$i]."' AND page_status!=''");
				$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
				if ($cms_pagesDb->page_title) $temp.='<div class="mainmenu_bar fonts">'.$cms_pagesDb->page_title.'</div>';
				$temp.=$cms_pagesDb->page_text;
			}

			// *** Own script ***
			if ($module_item[$i]=='own_script' AND strpos($module_option_2[$i], $_SERVER['HTTP_HOST'])){
				if ($module_option_1[$i]) $temp.='<div class="mainmenu_bar fonts">'.__($module_option_1[$i]).'</div>';
				$codefile=$module_option_2[$i];
				$temp.=file_get_contents($codefile.'?language='.$selected_language);
			}

			// *** Empty line ***
			if ($module_item[$i]=='empty_line'){ $temp.='<br>'; }

			if ($module_column[$i]=='left'){
				if ($left_column!=='') $left_column.='<br><br>';
				$left_column.=$temp;
			}
			if ($module_column[$i]=='center'){
				if ($center_column!=='') $center_column.='<br><br>';
				$center_column.=$temp;
			}
			if ($module_column[$i]=='right'){
				if ($right_column!=='') $right_column.='<br><br>';
				$right_column.=$temp;
			}
		}
	} // *** End of user permission check ***


// TOP?????
	// *** Show slideshow ***
	//if (isset($humo_option["slideshow_show"]) AND $humo_option["slideshow_show"]=='y'){
	//	$this->show_slideshow();
	//}

	// *** Can be used for extra box in lay-out ***
	echo '<div id="mainmenu_centerbox">';

		// *** Center column ***
		$center_id="mainmenu_center";
		//if ($num_rows<=1 OR $humo_option["one_name_study"]=='y') $center_id="mainmenu_center_alt";
		if ($left_column=='') $center_id="mainmenu_center_alt";

		// *** Left column ***
		if ($left_column){
			echo '<div id="mainmenu_left">'.$left_column.'</div>';
			// Send output to browser immediately for large family trees.
			//ob_flush();
			//flush(); // IE
		}

		// *** Center column ***
		echo '<div id="'.$center_id.'" class="style_tree_text fonts">';
			echo $center_column;
			// Send output to browser immediately for large family trees.
			//ob_flush();
			//flush(); // for IE
		echo '</div>';

		// *** Right column ***
		if ($right_column){
			echo '<div id="mainmenu_right" class="fonts">'.$right_column.'</div>';
			// Send output to browser immediately for large family trees.
			//ob_flush();
			//flush(); // IE
		}

	echo '</div>'; // end of center_box
}


// *** Show name of selected family tree ***
function selected_family_tree(){
	global $dbh, $num_rows, $selected_language;
	$text='<div class="mainmenu_bar fonts">';
		if ($num_rows>1){ $text.=__('Selected family tree').': '; }
		// *** Variable $treetext_name used from menu.php ***
		$treetext=show_tree_text($_SESSION['tree_id'], $selected_language);
		$text.=$treetext['name'];
	$text.='</div>';
	return $text;
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
			$treetext=show_tree_text($dataDb->tree_id, $selected_language);
			$treetext_name=$treetext['name'];

			// *** Name family tree ***
			if ($dataDb->tree_prefix=='EMPTY'){
				// *** Show empty line ***
				$tree_name='';
			}
			elseif (isset($_SESSION['tree_prefix']) AND $_SESSION['tree_prefix']==$dataDb->tree_prefix){
				$tree_name='<span class="tree_link fonts">'.$treetext_name.'</span>';
			}
			else{
				if (CMS_SPECIFIC=='Joomla'){
					//$path_tmp='index.php?option=com_humo-gen&amp;database='.$dataDb->tree_prefix;
					$path_tmp='index.php?option=com_humo-gen&amp;tree_id='.$dataDb->tree_id;
				}
				// *** url_rewrite ***
				elseif ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path is made in header.php ***
					$path_tmp=$uri_path.'tree_index/'.$dataDb->tree_id.'/';
					//$path_tmp=$uri_path.'index/'.$dataDb->tree_id.'/';
				}
				else{
					$path_tmp='tree_index.php?tree_id='.$dataDb->tree_id;
					//$path_tmp='index.php?tree_id='.$dataDb->tree_id;
				}
				$tree_name='<span class="tree_link fonts"><a href="'.$path_tmp.'">'.$treetext_name.'</a></span>';
			}
			if ($text!='') $text.='<br>';
			$text.=$tree_name;
		}	// end of family tree check

	}

	// *** Use scroll scrollbar for long list of family trees ***
	//$text='<div style="max-height:240px; overflow-x: auto;">'.$text.'</div>';

	return $text;
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

	$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4)." ".substr($tree_date,11,5);

	return __('Latest update:').' '.$tree_date.', '.$dataDb->tree_persons.' '.__('persons').", ".$dataDb->tree_families.' '.__('families');
}

// *** Owner family tree ***
function owner(){
	global $language, $dataDb;
	$tree_owner='';

	if (isset($dataDb->tree_owner) AND $dataDb->tree_owner){
		$tree_owner=__('Owner family tree:').' ';
		// *** Show owner e-mail address ***
		if ($dataDb->tree_email){
			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=mailform';
			}
			else{
				$path_tmp=CMS_ROOTPATH.'mailform.php';
			}
			$tree_owner.='<a href="'.$path_tmp.'">'.$dataDb->tree_owner."</a>\n";
		}
		else{
			$tree_owner.=$dataDb->tree_owner."\n";
		}
	}
	return $tree_owner;
}

//*** Most frequent names ***
function last_names($columns,$rows){
	global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path, $maxcols, $text;

	// MAIN SETTINGS
	$maxcols = 2; // number of name&nr colums in table. For example 3 means 3x name col + nr col
	if ($columns) $maxcols=$columns;

	$maxnames = 8;
	if ($rows) $maxnames=$rows*$maxcols;

	//$table2_width="500";
	$text='';

	if (!function_exists('tablerow')) {
		function tablerow($nr,$lastcol=false) {
			// displays one set of name & nr column items in the row
			// $nr is the array number of the name set created in function last_names
			// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
			global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $text;
			if (CMS_SPECIFIC=='Joomla'){
				//$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
				$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;tree_id='.tree_id;
			}
			else{
				//$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
				$path_tmp=CMS_ROOTPATH.'list.php?tree_id='.$_SESSION['tree_id'];
			}
			$text.='<td class="namelst">';
				if(isset($freq_last_names[$nr])) {
					$top_pers_lastname=''; 	if ($freq_pers_prefix[$nr]){
						$top_pers_lastname=str_replace("_", " ", $freq_pers_prefix[$nr]); }
					$top_pers_lastname.=$freq_last_names[$nr];
					if ($user['group_kindindex']=="j"){
						$text.='<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("_", " ", $freq_pers_prefix[$nr]).str_replace("&", "|", $freq_last_names[$nr]); 
					}
					else{
						$top_pers_lastname=$freq_last_names[$nr];
						if ($freq_pers_prefix[$nr]){ $top_pers_lastname.=', '.str_replace("_", " ", $freq_pers_prefix[$nr]); }
						$text.='<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("&", "|", $freq_last_names[$nr]);
						if ($freq_pers_prefix[$nr]){ $text.='&amp;pers_prefix='.$freq_pers_prefix[$nr]; }
						else{ $text.='&amp;pers_prefix=EMPTY'; }
					}
					$text.='&amp;part_lastname=equals">'.$top_pers_lastname."</a>";
				}
				else $text.='~';
			$text.='</td>';
			
			if($lastcol==false)  $text.='<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
			else $text.='</td><td class="namenr" style="text-align:center">'; // no thick border
			
			if(isset($freq_last_names[$nr])) $text.=$freq_count_last_names[$nr];
			else $text.='~';
			$text.='</td>';
		}
	}

	if (!function_exists('last_names')) {
		function last_names($max) {
			global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols, $text;

			// *** Read cache (only used in large family trees) ***
			$cache=''; $cache_count=0; $cache_check=false; // *** Use cache for large family trees ***
			$cacheqry = $dbh->query("SELECT * FROM humo_settings
				WHERE setting_variable='cache_surnames' AND setting_tree_id='".$tree_id."'");
			$cacheDb=$cacheqry->fetch(PDO::FETCH_OBJ);
			if ($cacheDb){
				$cache_array=explode("|",$cacheDb->setting_value);
				foreach ($cache_array as $cache_line) {
					$cacheDb = json_decode(unserialize($cache_line));

					$cache_check=true;
					$test_time=time()-7200; // *** 86400 = 1 day, 7200 = 2 hours ***
// TEST LINE
//$test_time=time()-20; // *** 86400 = 1 day, 7200 = 2 hours ***
					if($cacheDb->time < $test_time){
						$cache_check=false;
					}
					else{
						$freq_last_names[]=$cacheDb->pers_lastname;
						$freq_pers_prefix[]=$cacheDb->pers_prefix;
						$freq_count_last_names[]=$cacheDb->count_last_names;
					}
				}
			}

			if ($cache_check==false){
// TEST LINE
//echo 'NO CACHE';
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
					// *** Cache: only use cache if there are > 5.000 persons in database ***
					if (isset($dataDb->tree_persons) AND $dataDb->tree_persons>5000){
						$personDb->time=time(); // *** Add linux time to array ***
						if ($cache) $cache.='|';
						$cache.=serialize(json_encode($personDb));
						$cache_count++;
					}

					$freq_last_names[]=$personDb->pers_lastname;
					$freq_pers_prefix[]=$personDb->pers_prefix;
					$freq_count_last_names[]=$personDb->count_last_names;
				}

			} // *** End of cache ***

			// *** Add or renew cache in database (only if cache_count is valid) ***
			if ($cache AND ($cache_count==$max)){
				$sql = "DELETE FROM humo_settings
					WHERE setting_variable='cache_surnames' AND setting_tree_id='".safe_text_db($tree_id)."'";
				$result = $dbh->query($sql);
				$sql = "INSERT INTO humo_settings SET
					setting_variable='cache_surnames', setting_value='".safe_text_db($cache)."',
					setting_tree_id='".safe_text_db($tree_id)."'";
				$result = $dbh->query($sql);
			}


			$row=0;
			if ($freq_last_names) $row = round(count($freq_last_names)/$maxcols);

			for ($i=0; $i<$row; $i++){
				$text.='<tr>';
				for($n=0;$n<$maxcols;$n++) {
					if($n == $maxcols-1) {
						tablerow($i+($row*$n),true); // last col
					}
					else {
						tablerow($i+($row*$n)); // other cols
					}
				}
				$text.='</tr>';
			}
			if (isset($freq_count_last_names)) return $freq_count_last_names[0];
		}
	}

	//	$text.=__('Most frequent surnames:')."<br>";
	$text.='<div class="mainmenu_bar fonts">'.__('Names').'</div>';

	//$text.='<table width=500 class="humo nametbl" align="center">';
	$text.='<table width="90%" class="humo nametbl" align="center">';

	// *** Override td style ***
	$text.='
	<style>
	table.humo td, table.relmenu td {
		padding-top: 0px;
		padding-bottom: 0px;
	}
	</style>';

	$text.='<tr class="table_headline">';
	$col_width = ((round(100/$maxcols))-6)."%";
	for($x=1; $x<$maxcols;$x++) {
		$text.='<td width="'.$col_width.'"><b>'.__('Surname').'</b></td><td style="border-right-width:3px;width:6%"><b>'.__('Total').'</b></td>';  
	}
	$text.='<td width="'.$col_width.'"><b>'.__('Surname').'</b></td><td width:6%"><b>'.__('Total').'</b></td>';
	$text.='</tr>';

	$baseperc = last_names($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)

	//$text.='<tr class=table_headline>';
	//	$text.='<td colspan="2" style="border-right-width:3px;"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_surnames">'.__('More frequent surnames').'</a></td>';
	//	$text.='<td colspan="2"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_firstnames">'.__('Frequent first names').'</a></td>';
	//$text.='</tr>';

	$text.='<tr><td colspan="'.($maxcols*2).'" class=table_headline><a href="'.CMS_ROOTPATH.'statistics.php">'.__('More statistics').'</a></td></tr>';

	$text.='</table>';

	$text.='
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

	//ob_flush();
	//flush(); // IE
	return $text;
}

// *** Search field ***
function search_box(){
	global $language, $dbh, $humo_option;
	$text='';

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
	$pers_firstname=''; if (isset($_SESSION["save_firstname"])){ $pers_firstname=$_SESSION["save_firstname"]; }
	$part_firstname=''; if (isset($_SESSION["save_part_firstname"])){ $part_firstname=$_SESSION["save_part_firstname"]; }
	$pers_lastname=''; if (isset($_SESSION["save_lastname"])){ $pers_lastname=$_SESSION["save_lastname"]; }
	$part_lastname=''; if (isset($_SESSION["save_part_lastname"])){ $part_lastname=$_SESSION["save_part_lastname"]; }
	$search_database='tree_selected'; if (isset($_SESSION["save_search_database"])){ $search_database=$_SESSION["save_search_database"]; }

	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list';
	}
	else{
		$path_tmp=CMS_ROOTPATH.'list.php';
	}
	$text.='<form method="post" action="'.$path_tmp.'">';

	$text.='<p>';
		if($humo_option['one_name_study']=='n') { $text.=__('Enter name or part of name').'<br>'; }
		else { $text.=__('Enter private name').'<br>'; }
		//$text.='<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';
		
		$text.='<input type="hidden" name="index_list" value="quicksearch">';
		$quicksearch='';
		if (isset($_POST['quicksearch'])){
			//$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
			$quicksearch=safe_text_show($_POST['quicksearch']);
			$_SESSION["save_quicksearch"]=$quicksearch;
		}
		if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
	$text.='<input type="text" name="quicksearch" placeholder="'.__('Name').'" value="'.$quicksearch.'" size="30" pattern=".{3,}" title="'.__('Minimum: 3 characters.').'"></p>';

	// Check if there are multiple family trees.
	$datasql2 = $dbh->query("SELECT * FROM humo_trees");
	$num_rows2 = $datasql2->rowCount();
	if ($num_rows2>1 AND $humo_option['one_name_study']=='n'){
		$checked=''; if ($search_database=="tree_selected"){ $checked='checked'; }
		$text.='<input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.__('Selected family tree').'<br>';
		//$checked=''; if ($search_database=="all_databases"){ $checked='checked'; }
		$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
		$text.='<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees').'<br>';
		$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
		$text.='<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree').'<br>';
	}
	if ($num_rows2>1 AND $humo_option['one_name_study']=='y'){
		$text.='<input type="hidden" name="search_database" value="all_trees">';
	}
	$text.='<p><input type="submit" value="'.__('Search').'"></p>';
	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1&index_list=search';
	}
	else{
		$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1&index_list=search';
	}
	$text.='<p><a href="'.$path_tmp.'">'.__('Advanced search').'</a></p>';

	$text.="</form>\n";
	return $text;
}

// *** Random photo ***
function random_photo(){
	global $dataDb, $tree_id, $dbh, $db_functions;
	$text='';

	$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';

	// *** Loop through pictures and find first available picture without privacy filter ***
	$qry="SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_kind='picture' AND event_connect_kind='person' AND event_connect_id NOT LIKE ''
		ORDER BY RAND()";
	$picqry=$dbh->query($qry);
	while($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
		$picname = str_replace(" ","_",$picqryDb->event_event);
		$check_file=strtolower(substr($picname,-3,3));
		if (($check_file=='png' OR $check_file=='gif'  OR $check_file=='jpg')AND file_exists($tree_pict_path.$picname)){
			@$personmnDb = $db_functions->get_person($picqryDb->event_connect_id);
			$man_cls = New person_cls;
			$man_cls->construct($personmnDb);
			$man_privacy=$man_cls->privacy;
			if ($man_cls->privacy==''){
				$text.='<div style="text-align: center;">';

				//$text.='<img src="'.$tree_pict_path.$picname.'" width="200 px"
				//	style="border-radius: 15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"><br>';

				// *** Show picture using GLightbox ***
				$text.='<a href="'.$tree_pict_path.$picname.'" class="glightbox" data-glightbox="description: '.str_replace("&", "&amp;", $picqryDb->event_text).'"><img src="'.$tree_pict_path.$picname.'" width="200 px"
					style="border-radius: 15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"></a><br>';

				// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
				$url=$man_cls->person_url2($personmnDb->pers_tree_id,$personmnDb->pers_famc,$personmnDb->pers_fams,$personmnDb->pers_gedcomnumber);

				$text.='<a href="'.$url.'">'.$picqryDb->event_text.'</a></div><br>';

				// *** Show first available picture without privacy restrictions ***
				break;
			}

			// *** TEST privacy filter ***
			//else{
			//	$picname = str_replace(" ","_",$picqryDb->event_event);
			//	$text.='<img src="'.$tree_pict_path.$picname.'" width="200 px"
			//		style="border-radius: 15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">';
			//	$text.=$picqryDb->event_id.' tree_id:'.$picqryDb->event_tree_id.' ';
			//	$text.=$man_cls->privacy.'PRIVACY<br>';
			//}
		}
	}

	return $text;
}

// *** Favourites ***
function extra_links(){
	global $dbh, $tree_id, $humo_option, $uri_path;
	$text='';

	// *** Check if there are extra links ***
	$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
	@$num_rows = $datasql->rowCount();
	if ($num_rows>0){
		while (@$data2Db=$datasql->fetch(PDO::FETCH_OBJ)){
			$item = explode("|",$data2Db->setting_value);
			$pers_own_code[] = $item[0];
			$link_text[] = $item[1];
			$link_order[] = $data2Db->setting_order;
		}
		//include_once(CMS_ROOTPATH.'include/person_cls.php');
		$person=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_own_code NOT LIKE ''");
		while(@$personDb=$person->fetch(PDO::FETCH_OBJ)){
			if (in_array ($personDb->pers_own_code,$pers_own_code) ){
				$person_cls = New person_cls;

				// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
				$path_tmp=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);

				$name=$person_cls->person_name($personDb);
				$text_nr=array_search ($personDb->pers_own_code,$pers_own_code);

				$link_order2=$link_order[$text_nr];
				$link_text2[$link_order2]='<a href="'.$path_tmp.'">'.$name["standard_name"].'</a> '.$link_text[$text_nr];
			}
		}

		// *** Show links ***
		if (isset($link_text2)){
			$text.='<div class="mainmenu_bar fonts">'.__('Favourites').'</div>';
			//for($i=1; $i<=count($link_text2); $i++){
			for($i=1; $i<=$num_rows; $i++){
				if (isset($link_text2[$i])) $text.=$link_text2[$i]."<br>\n";
			}
		}

	}
	return $text;
}

// *** Alphabet line ***
function alphabet(){
	global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path;
	$text='';

	//*** Find first first_character of last name ***
	$text.=__('Surnames Index:')."<br>\n";

	// *** Read cache (only used in large family trees) ***
	$cache=''; $cache_count=0; $cache_check=false; // *** Use cache for large family trees ***
	$cacheqry = $dbh->query("SELECT * FROM humo_settings
		WHERE setting_variable='cache_alphabet' AND setting_tree_id='".$tree_id."'");
	$cacheDb=$cacheqry->fetch(PDO::FETCH_OBJ);
	if ($cacheDb){
		$cache_array=explode("|",$cacheDb->setting_value);
		foreach ($cache_array as $cache_line) {
			$cacheDb = json_decode(unserialize($cache_line));

			$cache_check=true;
			$test_time=time()-10800; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
// TEST LINE
//$test_time=time()-20; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
			if($cacheDb->time < $test_time){
				$cache_check=false;
			}
			else{
				$first_character[]=$cacheDb->first_character;
			}
		}
	}

	if ($cache_check==false){
		$personqry="SELECT UPPER(LEFT(pers_lastname,1)) as first_character FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
			GROUP BY first_character ORDER BY first_character";
		// *** If "van Mons" is selected, also check pers_prefix ***
		if ($user['group_kindindex']=="j"){
			$personqry="SELECT UPPER(LEFT(CONCAT(pers_prefix,pers_lastname),1)) as first_character FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
				GROUP BY first_character ORDER BY first_character";
		}

		@$person=$dbh->query($personqry);
		$count_first_character=$person->rowCount();
		while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
			// *** Cache: only use cache if there are > 5.000 persons in database ***
			if (isset($dataDb->tree_persons) AND $dataDb->tree_persons>5000){
				$personDb->time=time(); // *** Add linux time to array ***
				if ($cache) $cache.='|';
				$cache.=serialize(json_encode($personDb));
				$cache_count++;
			}

			$first_character[]=$personDb->first_character;
		}
	}

	// *** Add or renew cache in database (only if cache_count is valid) ***
	if ($cache AND ($cache_count==$count_first_character)){
		$sql = "DELETE FROM humo_settings
			WHERE setting_variable='cache_alphabet' AND setting_tree_id='".safe_text_db($tree_id)."'";
		$result = $dbh->query($sql);
		$sql = "INSERT INTO humo_settings SET
			setting_variable='cache_alphabet', setting_value='".safe_text_db($cache)."',
			setting_tree_id='".safe_text_db($tree_id)."'";
		$result = $dbh->query($sql);
	}

	// *** Show character line ***
	for ($i=0; $i<count($first_character); $i++){
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;tree_id='.$tree_id.
			'&amp;last_name='.$first_character[$i];
		}
		elseif ($humo_option["url_rewrite"]=="j"){
			// *** url_rewrite ***
			// *** $uri_path is gemaakt in header.php ***
			$path_tmp=$uri_path.'list_names/'.$tree_id.'/'.$first_character[$i].'/';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list_names.php?tree_id='.$tree_id.'&amp;last_name='.$first_character[$i];
		}
		$text.=' <a href="'.$path_tmp.'">'.$first_character[$i].'</a>';
	}

	//if (CMS_SPECIFIC=='Joomla'){
	//	$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;pers_lastname=...';
	//} else{
	//	$path_tmp=CMS_ROOTPATH.'list.php?pers_lastname=...';
	//}
	//$text.=' <a href="'.$path_tmp. '">'.__('Other')."</a>\n";

	$person="SELECT pers_patronym FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_patronym LIKE '_%' AND pers_lastname ='' LIMIT 0,1";
	@$personDb=$dbh->query($person);
	if ($personDb->rowCount()>0) {
		$text.=' <a href="'.CMS_ROOTPATH.'list.php?index_list=patronym">'.__('Patronyms').'</a>';
	}

	//ob_flush();
	//flush(); // IE

	return $text;
}

function today_in_history($view='with_table'){
	global $dbh, $dataDb;
	//include_once(CMS_ROOTPATH."include/person_cls.php");
	include_once(CMS_ROOTPATH."include/language_date.php");
	include_once(CMS_ROOTPATH."include/date_place.php");

	// *** Backwards compatible, value is empty ***
	if ($view=='') $view='with_table';

	$today = date('j').' '.strtoupper(date ("M"));
	$today2 = '0'.date('j').' '.strtoupper(date ("M"));
	$count_privacy=0;
	$text='';

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
		if (!$person_cls->privacy){
			if (trim(substr($record->pers_birth_date,0,6))==$today OR substr($record->pers_birth_date,0,6)==$today2){
				//$history['order'][]=substr($record->pers_birth_date,-4);
				// *** First order birth, using C ***
				$history['order'][]='C'.substr($record->pers_birth_date,-4);
				if ($view=='with_table'){
					$history['date'][]='<td>'.date_place($record->pers_birth_date,'').'</td><td>'.__('born').'</td>';
				}
				else{
					$history['item'][]=__('born');
					$history['date'][]=date_place($record->pers_birth_date,'');
				}
			}
			elseif (trim(substr($record->pers_bapt_date,0,6))==$today OR substr($record->pers_bapt_date,0,6)==$today2){
				// *** Second order baptise, using B ***
				$history['order'][]='B'.substr($record->pers_bapt_date,-4);
				if ($view=='with_table'){
					$history['date'][]='<td>'.date_place($record->pers_bapt_date,'').'</td><td>'.__('baptised').'</td>';
				}
				else{
					$history['item'][]=__('baptised');
					$history['date'][]=date_place($record->pers_bapt_date,'');
				}
			}
			else{
				// *** Third order death, using A ***
				$history['order'][]='A'.substr($record->pers_death_date,-4);
				if ($view=='with_table'){
					$history['date'][]='<td>'.date_place($record->pers_death_date,'').'</td><td>'.__('died').'</td>';
				}
				else{
					$history['item'][]=__('died');
					$history['date'][]=date_place($record->pers_death_date,'');
				}
			}

			// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
			$url=$person_cls->person_url2($record->pers_tree_id,$record->pers_famc,$record->pers_fams,$record->pers_gedcomnumber);

			$history['name'][]='<td><a href="'.$url.'">'.$name["standard_name"].'</a></td>';
		}
		else
			$count_privacy++;
	}

	$text.='<div class="mainmenu_bar fonts">'.__('Today in history').'</div>';

	// *** Use scrollbar for long list ***
	$text.='<div style="max-height:200px; overflow-x: auto;">';
		if ($view=='with_table'){
			$text.='<table width="90%" class="humo nametbl" align="center">';
				// *** Override td style ***
				$text.='
				<style>
				table.humo td, table.relmenu td {
					padding-top: 0px;
					padding-bottom: 0px;
				}
				</style>';

				//$text.='<tr class="table_headline">';
				//	$text.='<td colspan="3"><b>'.__('Today in history').'</b></td>';
				//$text.='</tr>';

				$text.='<tr class="table_headline">';
					$text.='<td><b>'.__('Date').'</b></td><td><b>'.__('Event').'</b></td><td><b>'.__('Name').'</b></td>';
				$text.='</tr>';

				if (isset($history['date'])){
					array_multisort($history['order'], SORT_DESC, $history['date'], $history['name']);

					for ($i=0; $i<=count($history['date'])-1; $i++){
						$text.='<tr>';
							$text.=$history['date'][$i];
							$text.=$history['name'][$i];
						$text.='</tr>';
					}
				}

				if ($count_privacy)
					$text.='<tr><td colspan="3">'.$count_privacy.__(' persons are not shown due to privacy settings').'</td></tr>';
			$text.='</table>';
		}
		else{
			// *** Show history list without table ***
			if (isset($history['date'])){
				array_multisort($history['order'], SORT_DESC, $history['date'], $history['name'], $history['item']);
				$item='';
				for ($i=0; $i<=count($history['date'])-1; $i++){
					if ($item==''){
						$item=$history['item'][$i];
						$text.='<b>'.ucfirst($history['item'][$i]).' '.substr($history['date'][$i],0,-4).'</b><br>';
					}
					if ($item!=$history['item'][$i]){
						$item=$history['item'][$i];
						$text.='<b>'.ucfirst($history['item'][$i]).' '.substr($history['date'][$i],0,-4).'</b><br>';
					}

					//$text.=$history['date'][$i].' ';
					$text.=substr($history['date'][$i],-4).' ';
					$text.=$history['name'][$i].'<br>';
				}
			}
			if ($count_privacy)
				$text.=$count_privacy.__(' persons are not shown due to privacy settings');
		}
	$text.='</div>';
	return $text;
}

function show_footer(){
	global $bot_visit,$humo_option,$uri_path;

	echo '<br><div class="humo_version">';
		// *** Show owner of family tree ***
		echo $this->owner();

		// *** Show HuMo-genealogy link ***
		printf(__('This database is made by %s, a freeware genealogical  program'), '<a href="https://humo-gen.com">HuMo-genealogy</a>');
		//echo ' ('.$humo_option["version"].').<br>';
		echo '.<br>';

		// *** Show European cookie information ***
		if ($humo_option["url_rewrite"]=="j"){
			// *** $uri_path made in header.php ***
			$url=$uri_path.'cookies';
		}
		else{
			$url='cookies.php';
		}
		if (!$bot_visit){ printf(__('European law: %s cookie information'),'<a href="'.$url.'">HuMo-genealogy'); }
		echo '</a>';
	echo '</div>';
}

// *** Show slideshow ***
function show_slideshow(){
	global $humo_option;

	// *** Used inline CSS, so it will be possible to use other CSS style (can be used for future slideshow options) ***

	echo '<style>
	/* CSS3 slider for mainmenu */
	/* @import url(http://fonts.googleapis.com/css?family=Istok+Web); */
	@keyframes slidy {
		0% { left: 0%; }
		20% { left: 0%; }
		25% { left: -100%; }
		45% { left: -100%; }
		50% { left: -200%; }
		70% { left: -200%; }
		75% { left: -300%; }
		95% { left: -300%; }
		100% { left: -400%; }
	}
	/* body, figure { */
	figure {
		margin: 0;
		/*	font-family: Istok Web, sans-serif; */
		font-weight: 100;
		
		/* height:250px; */
	}
	div#captioned-gallery {
		width: 100%; overflow: hidden; 
		margin-top: -17px;
	}
	figure.slider { 
		position: relative; width: 500%; 
		font-size: 0; animation: 30s slidy infinite; 
	}
	figure.slider figure { 
		width: 20%; height: auto;
		display: inline-block;  position: inherit; 
	}
	figure.slider img { width: 100%; height: auto; }
	figure.slider figure figcaption {
		position: absolute; bottom: 10px;
		background: rgba(0,0,0,0.4);
		color: #fff; width: 100%;
		font-size: 1.2rem; padding: .6rem;
		text-shadow: 2px 2px 4px #000000; 
	}
	/* end of CSS3 slider */
	</style>';

	echo '<div id="captioned-gallery">';

		echo '<figure class="slider">';
			echo '<figure>';
				$slideshow_01=explode('|',$humo_option["slideshow_01"]);
				if ($slideshow_01[0] AND file_exists($slideshow_01[0])){
					echo '<img src="'.$slideshow_01[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_01[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 01</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_02=explode('|',$humo_option["slideshow_02"]);
				if ($slideshow_02[0] AND file_exists($slideshow_02[0])){
					echo '<img src="'.$slideshow_02[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_02[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 02</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_03=explode('|',$humo_option["slideshow_03"]);
				if ($slideshow_03[0] AND file_exists($slideshow_03[0])){
					echo '<img src="'.$slideshow_03[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_03[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 03</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_04=explode('|',$humo_option["slideshow_04"]);
				if ($slideshow_04[0] AND file_exists($slideshow_04[0])){
					echo '<img src="'.$slideshow_04[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_04[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 04</figcaption>';
				}
			echo '</figure>';

			// *** 5th picture must be the same as 1st picture ***
			echo '<figure>';
				$slideshow_01=explode('|',$humo_option["slideshow_01"]);
				if ($slideshow_01[0] AND file_exists($slideshow_01[0])){
					echo '<img src="'.$slideshow_01[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_01[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 01</figcaption>';
				}
			echo '</figure>';

		echo '</figure>';
	echo '</div>';
}


}
?>