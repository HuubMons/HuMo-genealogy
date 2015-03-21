<?php
class mainindex_cls{

	function show_tree_index(){
		global $dbh, $tree_prefix_quoted, $dataDb, $selected_language, $treetext_name, $dirmark2, $bot_visit, $humo_option;

		echo '<script type="text/javascript">';
		echo 'checkCookie();';
		echo '</script>';

		// *** Can be used for extra box in lay-out ***
		echo '<div id="mainmenu_centerbox">';

		// *** Select family tree ***
		$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
		$num_rows = $datasql->rowCount();
		if ($num_rows>1){
			echo '<div id="mainmenu_left">';
				echo '<div class="mainmenu_bar fonts">'.__('Select a family tree').':</div>';
				// *** List of family trees ***
				echo $this->tree_list($datasql);
			echo '</div>';
		}

		$center_id="mainmenu_center";
		if ($num_rows<=1) $center_id="mainmenu_center_alt";
		echo '<div id="'.$center_id.'" class="style_tree_text fonts">';
			$sql = "SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix_quoted."' ORDER BY tree_order";
			$datasql = $dbh->query($sql);
			@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
			// *** Show name of selected family tree ***
			echo '<div class="mainmenu_bar fonts">';
				if ($num_rows>1){ echo __('Selected family tree').': '; }
				// *** Variable $treetext_name used from menu.php ***
				//echo $treetext_name;
				$treetext=show_tree_text($_SESSION['tree_prefix'], $selected_language);
				echo $treetext['name'];
			echo '</div>';

			if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){
				//
			}
			else{
				if ($tree_prefix_quoted=='' OR $tree_prefix_quoted=='EMPTY'){
					//echo '<h2><a href="'.CMS_ROOTPATH.'login.php">'.__('Please login first.').'</a></h2>';
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

					//*** Most frequent names ***
					echo '<br>'.$this->last_names().$dirmark2.'<br>';

					// *** Alphabet line ***
					echo '<br>'.$this->alphabet().$dirmark2.'<br>';

					// *** Extra added links to persons ***
					echo '<br>'.$this->extra_links().$dirmark2.'<br><br>';
				}
			}
		echo '</div>';

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
						//$path_tmp=$uri_path.'index/'.$dataDb->tree_prefix.'/';
						$path_tmp=$uri_path.'tree_index/'.$dataDb->tree_prefix.'/';
					}
					else{
						$path_tmp=$_SERVER['PHP_SELF'].'?database='.$dataDb->tree_prefix;
					}
					//$tree_name='<a href="'.$path_tmp.'">';
					//$tree_name.='<span class="tree_link fonts">'.$treetext_name.'</span></a><br>';

					$tree_name='<span class="tree_link fonts">';
					$tree_name.='<a href="'.$path_tmp.'">'.$treetext_name.'</a>';
					$tree_name.='</span><br>';

				}
				// *** Show empty line ***
				if ($dataDb->tree_prefix=='EMPTY'){ $tree_name='<br>'; }
				$text.=$tree_name;

			}		// end of family tree check

		}
		echo $text;
	}

	// *** Family tree data ***
	function tree_data(){
		global $dataDb,$language;
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

		$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4);
		return __('Latest update:').' '.$tree_date.', '.$dataDb->tree_persons.' '.__('persons').", ".$dataDb->tree_families.' '.__('families').".";
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
		global $dbh, $tree_id, $language, $user, $humo_option, $uri_path;
		$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
			GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,10";
		$person=$dbh->query($personqry);
		while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
			// *** No & character in $_GET, replace to: | !!!
			$last_names[]=$personDb->pers_lastname;
			$pers_prefix[]=$personDb->pers_prefix;
			$count_last_names[]=$personDb->count_last_names;
		}
		//echo __('Most frequent surnames:')."<br>";
		echo '<div style="width:500px; margin-left:auto; margin-right:auto;">'.__('Most frequent surnames:')."<br>";
		for ($i=0; $i<@count($last_names); $i++){
			$top_pers_lastname='';
			if ($pers_prefix[$i]){ $top_pers_lastname=str_replace("_", " ", $pers_prefix[$i]); }
			$top_pers_lastname.=$last_names[$i];

			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
			}
			else{
				$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
			}

			if ($user['group_kindindex']=="j"){
				echo '<a href="'.$path_tmp.'&amp;pers_lastname='.
				str_replace("_", " ", $pers_prefix[$i]).str_replace("&", "|", $last_names[$i]);
			}
			else{
				$top_pers_lastname=$last_names[$i];
				if ($pers_prefix[$i]){ $top_pers_lastname.=', '.str_replace("_", " ", $pers_prefix[$i]); }

				echo '<a href="'.$path_tmp.'&amp;pers_lastname='.
					str_replace("&", "|", $last_names[$i]);
				if ($pers_prefix[$i]){
					//echo '&amp;pers_prefix='.addslashes($pers_prefix[$i]);
					echo '&amp;pers_prefix='.$pers_prefix[$i];
				}
				else{
					echo '&amp;pers_prefix=EMPTY';
				}
			}
			echo '&amp;part_lastname=equals">'.$top_pers_lastname."</a>";

			echo " (".$count_last_names[$i].")";

			if ($i<count($last_names)-1){ echo ' / '; }
		}
		echo '</div>';
	}

	// *** Search field ***
	function search_box(){
		global $language, $dbh;

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
		/*
		echo __('First name').':<br>';
		echo ' <select name="part_firstname" style="width: 90px">';
		echo '<option value="contains">'.__('Contains').'</option>';
		$select_item=''; if ($part_firstname=='equals'){ $select_item=' selected'; }
		echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
		$select_item=''; if ($part_firstname=='starts_with'){ $select_item=' selected'; }
		echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
		echo '</select>';
		echo ' <input type="text" name="pers_firstname" value="'.$pers_firstname.'" size="15"><br>';

		echo '<p>'.__('Last name').':<br>';
		echo ' <select name="part_lastname" style="width: 90px">';
		echo '<option value="contains">'.__('Contains').'</option>';
		$select_item=''; if ($part_lastname=='equals'){ $select_item=' selected'; }
		echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
		$select_item=''; if ($part_lastname=='starts_with'){ $select_item=' selected'; }
		echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
		echo '</select>';
		echo ' <input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="15"></p>';
		*/

		echo __('Enter name or part of name').'<br>';
		//echo '<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';
		
		echo '<input type="hidden" name="index_list" value="quicksearch">';
		$quicksearch='';
		if (isset($_POST['quicksearch'])){
			$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
			$_SESSION["save_quicksearch"]=$quicksearch;
		}
		if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
		echo '<p><input type="text" name="quicksearch" value="'.$quicksearch.'" size="30" pattern=".{3,}" title="'.__('Minimum: 3 characters.').'"></p>';

		// Check if there are multiple family trees.
		$datasql2 = $dbh->query("SELECT * FROM humo_trees");
		$num_rows2 = $datasql2->rowCount();
		if ($num_rows2>1){
			$checked=''; if ($search_database=="tree_selected"){ $checked='checked'; }
			echo '<p><input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.__('Selected family tree').'<br>';
			//$checked=''; if ($search_database=="all_databases"){ $checked='checked'; }
			$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
			echo '<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees').'<br>';
			$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
			echo '<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree').'</p>';
		}

		echo '<p><input type="submit" value="'.__('Search').'"></p>';
		if (CMS_SPECIFIC=='Joomla'){
			//$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1';
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1&index_list=search';
		}
		else{
			//$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1';
			$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1&index_list=search';
		}
		echo '<p><a href="'.$path_tmp.'">'.__('Advanced search').'</a></p>';

		echo "</form>\n";
	}

	// *** Extra links ***
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
				for($i=1; $i<=count($link_text2); $i++){
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
		//$personqry="SELECT UPPER(LEFT(pers_lastname,1)) as first_character FROM ".$_SESSION['tree_prefix']."person GROUP BY first_character";
		$personqry="SELECT UPPER(LEFT(pers_lastname,1)) as first_character FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' GROUP BY first_character";

		// *** If "van Mons" is selected, also check pers_prefix ***
		if ($user['group_kindindex']=="j"){
			//$personqry="SELECT UPPER(LEFT(CONCAT(pers_prefix,pers_lastname),1)) as first_character FROM ".safe_text($_SESSION['tree_prefix'])."person GROUP BY first_character";
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

		//$person="SELECT pers_patronym FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_patronym LIKE '_%' AND pers_lastname =''";
		$person="SELECT pers_patronym FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_patronym LIKE '_%' AND pers_lastname =''";
		@$personDb=$dbh->query($person);
		if ($personDb->rowCount()>0) {
			echo ' <a href="'.CMS_ROOTPATH.'list.php?index_list=patronym">'.__('Patronyms').'</a>';
		}

	}

}
?>