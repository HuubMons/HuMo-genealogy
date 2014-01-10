<?php
class mainindex_cls{

	function show_tree_index(){
		global $db, $dbh, $selected_language, $treetext_name, $dirmark2, $bot_visit, $humo_option;

		echo '<script type="text/javascript">';
		echo 'checkCookie();';
		echo '</script>';

		// *** Select family tree ***
		/*
		$datasql = mysql_query("SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."' ORDER BY tree_order",$db);
		$num_rows = mysql_num_rows($datasql);
		*/
		$datasql = $dbh->query("SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."' ORDER BY tree_order");
		$num_rows = $datasql->rowCount();		

		// *** Can be used for extra box in lay-out ***
		echo '<div id="mainmenu_centerbox">';

		// *** Select family tree ***
		/*
		$datasql = mysql_query("SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."' ORDER BY tree_order",$db);
		$num_rows = mysql_num_rows($datasql);
		*/
		$datasql = $dbh->query("SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."' ORDER BY tree_order");
		$num_rows = $datasql->rowCount();		
		if ($num_rows>1){
			echo '<div id="mainmenu_left">';
				echo '<div class="mainmenu_bar fonts">'.__('Select a family tree').':</div>';
				// *** List of family trees ***
				echo $this->tree_list($datasql);
			echo '</div>';
		}

		$center_id="mainmenu_center";
		if ($num_rows<=1){
			$center_id="mainmenu_center_alt";
		}
		echo '<div id="'.$center_id.'" class="style_tree_text fonts">';
			// *** Get genealogical data ***
			$tree_prefix_selected=$_SESSION['tree_prefix'];
			if (isset($_GET['tree_prefix_gegevens'])){
				// *** Check if genealogy exists ***
				//$datasql = mysql_query("SELECT * FROM humo_trees
				//	WHERE tree_prefix='".safe_text($_GET['tree_prefix_gegevens'])."'",$db);
				$datasql = $dbh->query("SELECT * FROM humo_trees
					WHERE tree_prefix='".safe_text($_GET['tree_prefix_gegevens'])."'");					
				if ($datasql->rowCount()==1){
					$tree_prefix_selected=$_GET['tree_prefix_gegevens'];
				}
			}
			/*
			$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
				ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
				AND treetext_language='".$selected_language."' WHERE tree_prefix='".safe_text($tree_prefix_selected)."'  ORDER BY tree_order";
			$datasql = mysql_query($sql,$db);
			@$dataDb=mysql_fetch_object($datasql);
			*/
			$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
				ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
				AND treetext_language='".$selected_language."' WHERE tree_prefix='".safe_text($tree_prefix_selected)."'  ORDER BY tree_order";
			$datasql = $dbh->query($sql);
			@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);			
			// *** Show name of selected family tree ***
			echo '<div class="mainmenu_bar fonts">';
			if ($num_rows>1){ echo __('Selected family tree').': '; }
			// *** Variable $treetext_name used from menu.php ***
			echo $treetext_name;
			echo '</div>';
			if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){
				//
			}
			else{
				if ($tree_prefix_selected==''){
					echo '<h2><a href="'.CMS_ROOTPATH.'login.php">'.__('Please login first.').'</a></h2>';
				}
				else{
					// *** Date and number of persons/ families ***
					echo ' <i>'.$this->tree_data().'</i><br>';
					if($this->tree_data()!="") {echo $dirmark2;}

					// *** Owner genealogy ***
					echo $this->owner();

					// *** Show tekst en source genealogy ***
					if ($dataDb->treetext_mainmenu_text!=''){
						echo '<p>'.nl2br($dataDb->treetext_mainmenu_text); echo $dirmark2.'</p>';
					}
					if (isset($dataDb->treetext_mainmenu_source) AND $dataDb->treetext_mainmenu_source!=''){
						echo '<p><i>'.nl2br($dataDb->treetext_mainmenu_source).'</i>';  echo $dirmark2.'</p>';
					}

					//*** Most frequent names ***
					echo $this->last_names().$dirmark2.'<br><br>';

					echo $this->alphabet().$dirmark2.'<br><br>';

					echo $this->extra_links().$dirmark2.'<br><br>';
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
		global $db, $dbh, $humo_option, $uri_path, $user, $language;
		$text='';
		//while (@$dataDb=mysql_fetch_object($datasql)){
		while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){

			// *** Check is family tree is shown or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false;
			for ($x=0; $x<=count($hide_tree_array)-1; $x++){
				if ($hide_tree_array[$x]==$dataDb->tree_id){ $hide_tree=true; }
			}
			if ($hide_tree==false){	

				$treetext_name=$dataDb->treetext_name;
				if ($treetext_name==''){
					$treetext_name=__('NO NAME');
					$data2qry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
						ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
						AND treetext_name LIKE '_%'
						WHERE tree_prefix='".$dataDb->tree_prefix."'";
					//@$data2sql = mysql_query($data2qry,$db);
					//@$data2Db=mysql_fetch_object($data2sql);	
					@$data2sql = $dbh->query($data2qry);
					@$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
					if (isset($data2Db->treetext_name)){ $treetext_name=$data2Db->treetext_name; }
				}

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
		global $db, $dbh, $language, $user, $humo_option, $uri_path;
		/*
		$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
			FROM ".safe_text($_SESSION['tree_prefix'])."person
			WHERE pers_lastname NOT LIKE ''
			GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,5";
		$person=mysql_query($personqry, $db);
		*/
		$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
			FROM ".safe_text($_SESSION['tree_prefix'])."person
			WHERE pers_lastname NOT LIKE ''
			GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,5";
		$person=$dbh->query($personqry);		
		//while (@$personDb=mysql_fetch_object($person)){
		while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
			// *** No & character in $_GET, replace to: | !!!
			$last_names[]=$personDb->pers_lastname;
			$pers_prefix[]=$personDb->pers_prefix;
			$count_last_names[]=$personDb->count_last_names;
		}
		print __('Most frequent surnames:')."<br>";
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
					echo '&amp;pers_prefix='.addslashes($pers_prefix[$i]);
				}
				else{
					echo '&amp;pers_prefix=EMPTY';
				}
			}
			echo '&amp;part_lastname=equals">'.$top_pers_lastname."</a>";
			
			echo " (".$count_last_names[$i].")";

			if ($i<count($last_names)-1){ echo ' / '; }
		}

	}

	// *** Search field ***
	function search_box(){
		global $language, $db, $dbh;

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
		print '<form method="post" action="'.$path_tmp.'">';
		/*
		print __('First name').':<br>';
		print ' <select name="part_firstname" style="width: 90px">';
		echo '<option value="contains">'.__('Contains').'</option>';
		$select_item=''; if ($part_firstname=='equals'){ $select_item=' selected'; }
		echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
		$select_item=''; if ($part_firstname=='starts_with'){ $select_item=' selected'; }
		echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
		print '</select>';
		print ' <input type="text" name="pers_firstname" value="'.$pers_firstname.'" size="15"><br>';

		print '<p>'.__('Last name').':<br>';
		print ' <select name="part_lastname" style="width: 90px">';
		echo '<option value="contains">'.__('Contains').'</option>';
		$select_item=''; if ($part_lastname=='equals'){ $select_item=' selected'; }
		echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
		$select_item=''; if ($part_lastname=='starts_with'){ $select_item=' selected'; }
		echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
		print '</select>';
		print ' <input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="15"></p>';
		*/

		echo __('Enter name or part of name').'<br>';
		//echo '<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';
		
		print '<input type="hidden" name="index_list" value="quicksearch">';
		$quicksearch='';
		if (isset($_POST['quicksearch'])){
			$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
			$_SESSION["save_quicksearch"]=$quicksearch;
		}
		if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
		print '<p><input type="text" name="quicksearch" value="'.$quicksearch.'" size="30" pattern=".{3,}" title="'.__('Minimum: 3 characters.').'"></p>';

		// Check if there are multiple family trees.
		//$datasql2 = mysql_query("SELECT * FROM humo_trees",$db);
		//$num_rows2 = mysql_num_rows($datasql2);
		$datasql2 = $dbh->query("SELECT * FROM humo_trees");
		$num_rows2 = $datasql2->rowCount();		
		if ($num_rows2>1){
			$checked=''; if ($search_database=="tree_selected"){ $checked='checked'; }
			print '<p><input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.__('Selected family tree').'<br>';
			//$checked=''; if ($search_database=="all_databases"){ $checked='checked'; }
			$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
			print '<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees').'<br>';
			$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
			print '<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree').'</p>';
		}

		print '<p><input type="submit" value="'.__('Search').'"></p>';
		if (CMS_SPECIFIC=='Joomla'){
			//$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1';
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;adv_search=1&index_list=search';
		}
		else{
			//$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1';
			$path_tmp=CMS_ROOTPATH.'list.php?adv_search=1&index_list=search';
		}
		print '<p><a href="'.$path_tmp.'">'.__('Advanced search').'</a></p>';

		print "</form>\n";
	}

	// *** Extra links ***
	function extra_links(){
		global $db, $dbh, $humo_option, $uri_path;

		// *** Check if there are extra links ***
		//$datasql = mysql_query("SELECT * FROM humo_settings WHERE setting_variable='link'",$db);
		//@$num_rows = mysql_num_rows($datasql);
		$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
		@$num_rows = $datasql->rowCount();		
		if ($num_rows>0){
			include_once(CMS_ROOTPATH.'include/person_cls.php');
			//$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
			//	WHERE pers_own_code NOT LIKE ''",$db);
			$person=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
				WHERE pers_own_code NOT LIKE ''");				
			//while(@$personDb=mysql_fetch_object($person)){
			while(@$personDb=$person->fetch(PDO::FETCH_OBJ)){
				//$datasql = mysql_query("SELECT * FROM humo_settings WHERE setting_variable='link'",$db);
				$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
				//while (@$dataDb=mysql_fetch_object($datasql)){
				while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
					$item=explode("|",$dataDb->setting_value);
					if ($personDb->pers_own_code==$item[0]){
						$person_cls = New person_cls;
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
						$name=$person_cls->person_name($personDb);
						echo '<a href="'.$path_tmp.'">'.$name["standard_name"].'</a>';
						echo " $item[1]<br>\n";
					}
				}		
			}
		}
	}

	// *** Alphabet line ***
	function alphabet(){
		global $language, $user, $db, $dbh, $humo_option, $uri_path;

		//*** Find first first_character of last name ***
		print __('Surnames Index:')."<br>\n";
		//$personqry="SELECT UPPER(substring(pers_lastname,1,1)) as first_character FROM ".safe_text($_SESSION['tree_prefix'])."person GROUP BY first_character";
		$personqry="SELECT UPPER(substring(pers_lastname,1,1)) as first_character FROM ".$_SESSION['tree_prefix']."person GROUP BY first_character";
		// *** If "van Mons" is selected, also check pers_prefix ***
		if ($user['group_kindindex']=="j"){
			//$personqry="SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character FROM ".safe_text($_SESSION['tree_prefix'])."person GROUP BY first_character";
			$personqry="SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character FROM ".safe_text($_SESSION['tree_prefix'])."person GROUP BY first_character";
		}
		//@$person=mysql_query($personqry, $db);
		@$person=$dbh->query($personqry);
		//while (@$personDb=mysql_fetch_object($person)){
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
			print ' <a href="'.$path_tmp.'">'.$personDb->first_character.'</a>';
		}

		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;pers_lastname=...';
		} else{
			$path_tmp=CMS_ROOTPATH.'list.php?pers_lastname=...';
		}
		echo ' <a href="'.$path_tmp. '">'.__('Other')."</a>\n";
	
		//$person="SELECT pers_patronym FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_patronym LIKE '_%' AND pers_lastname =''";
		//@$personDb=mysql_query($person,$db);
		$person="SELECT pers_patronym FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_patronym LIKE '_%' AND pers_lastname =''";
		@$personDb=$dbh->query($person);		
		//if (@mysql_num_rows($personDb)>0) {
		if ($personDb->rowCount()>0) {
			print ' <a href="'.CMS_ROOTPATH.'list.php?index_list=patronym">'.__('Patronyms').'</a>';
		}

	}

}
?>