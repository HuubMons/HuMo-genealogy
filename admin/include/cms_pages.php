<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

global $selected_language;

if(CMS_SPECIFIC=="Joomla")
	$phpself = "index.php?option=com_humo-gen&amp;task=admin&amp;page=groups";
else
	$phpself = 'index.php';

echo '<h1 class="center">'.__('CMS Own pages').'</h1>';

printf(__('Here you can add your own pages to %s! It\'s possible to use categories in the menu (like "Family history", "Family stories").'),'HuMo-genealogy');

echo '<p><form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<table class="humo" style="width:95%;text-align:center;border:1px solid black;"><tr class="table_header_large">';
		echo '<td><input type="Submit" name="cms_pages" value="'.__('Pages').'"></td>';
		echo '<td><input type="Submit" name="cms_pages" value="'.__('Add page').'"></td>';
		echo '<td><input type="Submit" name="cms_menu" value="'.__('Menu').'"></td>';
		echo '<td><input type="Submit" name="cms_settings" value="'.__('CMS Settings').'"></td>';
	echo '<tr></table>';
echo '</form>';

// *** Save or add page ***
if (isset($_POST['add_page']) OR isset($_POST['change_page'])){
	$page_status=""; if (isset($_POST['page_status']) AND !empty($_POST['page_status'])){ $page_status='1'; }
	$page_menu_id=$_POST['page_menu_id'];

	// *** Generate new order numer, needed for new page or moved page ***
	$page_order='1';
	$ordersql = $dbh->query("SELECT page_order FROM humo_cms_pages ORDER BY page_order DESC LIMIT 0,1");
	if ($ordersql){
		$orderDb=$ordersql->fetch(PDO::FETCH_OBJ);
		$page_order=$orderDb->page_order+1;
	}

	if (isset($_POST['add_page'])){
		$sql="INSERT INTO humo_cms_pages SET page_order='".$page_order."', ";
	}
	else{
		$sql="UPDATE humo_cms_pages SET ";

		// *** If menu/ category is changed, use new page_order. Ordering for old category is restored later in script ***
		$page_menu_id='0'; if ($_POST['page_menu_id']) $page_menu_id=$_POST['page_menu_id'];
		if ($page_menu_id!=$_POST['page_menu_id_old']){
			// *** Page is moved to another category, use new page_order ***
			$sql.="page_order='".$page_order."',";
		}
	}

	$sql.="page_status='".$page_status."',
	page_menu_id='".safe_text_db($page_menu_id)."',
	page_title='".safe_text_db($_POST['page_title'])."',
	page_text='".safe_text_db($_POST['page_text'])."'
	";

	if (isset($_POST['change_page'])){
		$sql.="WHERE page_id='".safe_text_db($_POST['page_id'])."'";

		$_GET["select_page"]=safe_text_db($_POST['page_id']);
	}

	$result=$dbh->query($sql);

	if (isset($_POST['add_page'])){
		$sql="SELECT * FROM humo_cms_pages ORDER BY page_id DESC LIMIT 0,1";
		$qry=$dbh->query($sql);
		$cms_pagesDb=$qry->fetch(PDO::FETCH_OBJ);
		$_GET["select_page"]=$cms_pagesDb->page_id;
	}
}

// *** Move pages. Only numeric values alowed ***
if (isset($_GET['page_up']) AND is_numeric($_GET['page_up']) AND is_numeric($_GET['select_page'])){
	$sql="UPDATE humo_cms_pages as table1, humo_cms_pages as table2
		SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
		WHERE table1.page_id='".$_GET['page_up']."' AND table2.page_id='".$_GET['select_page']."'";
	//echo $sql;
	$result=$dbh->query($sql);
}
// *** Page up, only allow numeric values ***
if (isset($_GET['page_down']) AND is_numeric($_GET['page_down']) AND is_numeric($_GET['menu_id'])){
	$sql="UPDATE humo_cms_pages as table1, humo_cms_pages as table2
		SET table1.page_order=table2.page_order, table2.page_order=table1.page_order
		WHERE table1.page_order='".safe_text_db($_GET['page_down'])."' AND table1.page_menu_id='".$_GET['menu_id']."'
		AND table2.page_order='".safe_text_db($_GET['page_down']+1)."'  AND table2.page_menu_id='".$_GET['menu_id']."'";
	//echo $sql;
	$result=$dbh->query($sql);
}

// *** Remove page, only allow numeric values ***
if (isset($_GET['page_remove']) AND is_numeric($_GET['page_remove'])){
	echo '<div class="confirm">';
	if (isset($humo_option["main_page_cms_id"]) AND $humo_option["main_page_cms_id"]==$_GET['page_remove']){
		echo __('This page is selected as homepage!');
	}
	else{
		//echo '<b>'.__('Selected:').' '.$_GET['treetext_name'].'</b> ';
		echo __('Are you sure you want to remove this page?');
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="cms_pages" value="cms_page">';
		echo '<input type="hidden" name="page_id" value="'.$_GET['page_remove'].'">';
		echo ' <input type="Submit" name="page_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
		echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
		echo '</form>';
	}
	echo '</div>';
}
// *** Only allow numeric values ***
if (isset($_POST['page_remove2']) AND is_numeric($_POST['page_id'])){
	$sql="DELETE FROM humo_cms_pages WHERE page_id='".$_POST['page_id']."'";
	@$result=$dbh->query($sql);
}


// *** Save or add menu ***
if (isset($_POST['add_menu']) OR isset($_POST['change_menu'])){
	if (isset($_POST['add_menu'])){
		$menu_order='1';
		$datasql = $dbh->query("SELECT * FROM humo_cms_menu");
		if ($datasql){
			// *** Count lines in query ***
			$menu_order=$datasql->rowCount() +1;
		}

		$sql="INSERT INTO humo_cms_menu SET
		menu_order='".$menu_order."', ";
	}
	else{
		$sql="UPDATE humo_cms_menu SET ";
	}
	$sql.="menu_name='".safe_text_db($_POST['menu_name'])."'";

	if (isset($_POST['change_menu'])){
		$sql.="WHERE menu_id='".safe_text_db($_POST['menu_id'])."'";
	}

	//echo $sql;
	$result=$dbh->query($sql);
}

if (isset($_GET['menu_up'])){
	$sql="UPDATE humo_cms_menu as table1, humo_cms_menu as table2
		SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
		WHERE table1.menu_order='".safe_text_db($_GET['menu_up'])."' AND table2.menu_order='".safe_text_db($_GET['menu_up']-1)."'";
	//echo $sql;
	$result=$dbh->query($sql);
}
if (isset($_GET['menu_down'])){
	$sql="UPDATE humo_cms_menu as table1, humo_cms_menu as table2
		SET table1.menu_order=table2.menu_order, table2.menu_order=table1.menu_order
		WHERE table1.menu_order='".safe_text_db($_GET['menu_down'])."' AND table2.menu_order='".safe_text_db($_GET['menu_down']+1)."'";
	//echo $sql;
	$result=$dbh->query($sql);
}

if (isset($_GET['menu_remove'])){
	echo '<div class="confirm">';
	$qry=$dbh->query("SELECT * FROM humo_cms_pages
		WHERE page_menu_id='".safe_text_db($_GET['menu_remove'])."' ORDER BY page_order");
	$count=$qry->rowCount();
	if ($count>0){
		echo __('There are still pages connected to this menu!<br>
Please disconnect the pages from this menu first.');
	}
	else{
		//echo '<b>'.__('Selected:').' '.$_GET['treetext_name'].'</b> ';
		echo __('Are you sure you want to remove this menu?');
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="cms_menu" value="cms_menu">';
		echo '<input type="hidden" name="menu_id" value="'.$_GET['menu_remove'].'">';
		echo ' <input type="Submit" name="menu_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
		echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
		echo '</form>';
	}
	echo '</div>';
}
if (isset($_POST['menu_remove2'])){
	$sql="DELETE FROM humo_cms_menu WHERE menu_id='".safe_text_db($_POST['menu_id'])."'";
	@$result=$dbh->query($sql);
	
	// *** Re-order menu's ***
	$repair_order=1;
	$item=$dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
	while($itemDb=$item->fetch(PDO::FETCH_OBJ)){
		$sql="UPDATE humo_cms_menu SET menu_order='".$repair_order."' WHERE menu_id=".$itemDb->menu_id;
		$result=$dbh->query($sql);
		$repair_order++;
	}
}

echo '<p>';


$cms_item='pages';
// *** Show editor if page is choosen for first time ***
//if (!isset($_POST['cms_menu']) AND !isset($_POST['cms_settings'])) $cms_item='pages';

// *** Show and edit pages ***
if (isset($_POST['cms_pages']) OR isset($_GET["select_page"])) $cms_item='pages';

// *** Show and edit menu's ***
if (isset($_POST['cms_menu']) OR isset($_GET['select_menu'])) $cms_item='menu';

// *** CMS Settings ***
if (isset($_POST['cms_settings'])) $cms_item='settings';

// *** Show and edit pages ***
//if (isset($_POST['cms_pages']) OR isset($_GET["select_page"])){
if ($cms_item=='pages'){
	// *** Count number of pages in categories (so correct down arrows can be shown) ***
	// *** Also restore order numbering (if page is moved to another category) ***
	$page_nr=0;
	$page_menu_id=0;
	$qry=$dbh->query("SELECT page_id,page_menu_id,page_order FROM humo_cms_pages ORDER BY page_menu_id, page_order");
	while($cms_pagesDb=$qry->fetch(PDO::FETCH_OBJ)){
		if (!isset($pages_in_category[$cms_pagesDb->page_menu_id])){
			$pages_in_category[$cms_pagesDb->page_menu_id]='1';
		}
		else{
			$pages_in_category[$cms_pagesDb->page_menu_id]++;
		}

		if ($cms_pagesDb->page_menu_id>0 AND $page_menu_id!=$cms_pagesDb->page_menu_id){
			$page_nr=0;
			$page_menu_id=$cms_pagesDb->page_menu_id;
		}
		$page_nr++;

		// *** Restore order numbering (if page is moved to another category) ***
//		echo '!'.$page_nr.' '.$cms_pagesDb->page_order.'<br>';
		if ($page_nr!=$cms_pagesDb->page_order){
			$sql="UPDATE humo_cms_pages
				SET page_order='".$page_nr."'
				WHERE page_id='".$cms_pagesDb->page_id."'";
//		echo $sql.'<br>';
			$result=$dbh->query($sql);
		}
	}

	echo '<table style="border-top: solid 1px #999999;"><tr><td valign="top" style="border-right: solid 1px #999999;">';

		// *** List of pages ***
		echo __('Pages, click to edit:').'<br>';
		echo '<table>';
			$qry=$dbh->query("SELECT * FROM humo_cms_pages ORDER BY page_menu_id, page_order");
			$page_nr=0;
			$page_menu_id=0;
			while($cms_pagesDb=$qry->fetch(PDO::FETCH_OBJ)){
				// ** Show name of menu/ category ***
				if ($cms_pagesDb->page_menu_id=='9999'){
					echo '<tr><td colspan="2"><b>* '.__('Hide page in menu').' *</b></td></tr>';
					$page_nr=0;
				}
				elseif ($cms_pagesDb->page_menu_id>0 AND $page_menu_id!=$cms_pagesDb->page_menu_id){
					$qry_menu=$dbh->query("SELECT * FROM humo_cms_menu WHERE menu_id='".$cms_pagesDb->page_menu_id."'");
					$cmsDb=$qry_menu->fetch(PDO::FETCH_OBJ);
					echo '<tr><td colspan="2"><b>'.$cmsDb->menu_name.'</b></td></tr>';
					$page_nr=0;
					$page_menu_id=$cms_pagesDb->page_menu_id;
				}

				$page_nr++;
				echo '<tr><td style="width:60px;">';
					echo '<a href="index.php?page='.$page.'&amp;select_page='.$cms_pagesDb->page_id.'&amp;page_remove='.$cms_pagesDb->page_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" alt="'.__('Remove page').'" border="0"></a>';

					// *** Show ID numbers for test ***
					if ($page_nr!='1'){
						echo ' <a href="index.php?page='.$page.'&amp;page_up='.$previous_page.'&amp;select_page='.$cms_pagesDb->page_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>';
					}
					if ($page_nr!=$pages_in_category[$cms_pagesDb->page_menu_id]){
						echo ' <a href="index.php?page='.$page.'&amp;page_down='.$cms_pagesDb->page_order.'&amp;select_page='.$cms_pagesDb->page_id.'&amp;menu_id='.$cms_pagesDb->page_menu_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
					}
				echo '</td><td>';
					$page_title='['.__('No page title').']'; if ($cms_pagesDb->page_title) $page_title=$cms_pagesDb->page_title;
					echo ' <a href="index.php?page='.$page.'&amp;select_page='.$cms_pagesDb->page_id.'">'.$page_title.'</a><br>';
				echo '</td></tr>';
				$previous_page=$cms_pagesDb->page_id;
			}
		echo '</table>';

	echo '</td><td valign="top">';
		// *** Only numeric values alowed ***
		if (isset($_GET["select_page"]) AND is_numeric($_GET["select_page"])){
			$sql="SELECT * FROM humo_cms_pages WHERE page_id=".$_GET["select_page"];
			$qry=$dbh->query($sql);
			$cms_pagesDb=$qry->fetch(PDO::FETCH_OBJ);
			//if ($memosoort2Db->website_id==$memosoortDb->menu_website_id){
			//	echo '<a href="index.php?page='.$page.'&amp;select_page='.$cms_pagesDb->page_id.'">'.$cms_pagesDb->page_title.'</a><br>';
			$page_id=$cms_pagesDb->page_id;
			$page_text=$cms_pagesDb->page_text;
			$page_status=$cms_pagesDb->page_status;
			$page_title=$cms_pagesDb->page_title;
			$page_menu_id=$cms_pagesDb->page_menu_id;
			$page_counter=$cms_pagesDb->page_counter;
			$page_edit='change';
		}
		else{
			// *** Add new page ***
			$page_id='';
			$page_text='';
			$page_status='1';
			$page_title=__('Page title');
			$page_menu_id='';
			$page_counter='';
			$page_edit='add';
		}

		echo __('To edit the pages the CKEditor is used. For help about this editor, go to: <a href="http://ckeditor.com" target="_blank">ckeditor.com</a>.').'<br>';
		echo __('"Hide page in menu" is a special option. These pages can be accessed using a direct link.').'<br>';
		if ($page_id){
			// SERVER_NAME   127.0.0.1
			// REQUEST_URI: /url_test/index/1abcd2345/
			// REQUEST_URI: /url_test/index.php?variabele=1

			// Search for: /admin/ in $_SERVER['PHP_SELF']
			$position=strpos($_SERVER['PHP_SELF'],'/admin/');
			$path_tmp= 'http://'.$_SERVER['SERVER_NAME'].substr($_SERVER['REQUEST_URI'],0,$position);
			echo __('This page can be accessed using this link: ').'<b>'.$path_tmp.'/cms_pages.php?select_page=',$page_id.'</b><br>';
		}
		
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="cms_pages" value="cms_page">';

		echo '<input type="hidden" name="page_id" value="'.$page_id.'">';

		echo ' <input type="text" name="page_title" value="'.$page_title.'" size=25> ';

		echo '<input type="hidden" name="page_menu_id_old" value="'.$page_menu_id.'">';
		echo '<select size="1" name="page_menu_id">';
			echo "<option value=''>* ".__('No menu selected')." *</option>\n";
			$select=''; if ($page_menu_id=='9999'){ $select=' SELECTED'; }
			echo '<option value="9999"'.$select.'>* '.__('Hide page in menu')." *</option>\n";
			$qry=$dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
			while($menuDb=$qry->fetch(PDO::FETCH_OBJ)){
				$select=''; if ($menuDb->menu_id==$page_menu_id){ $select=' SELECTED'; }
				echo '<option value="'.$menuDb->menu_id.'"'.$select.'>'.$menuDb->menu_name.'</option>';
			}
		echo "</select>";

		$checked=''; if ($page_status){ $checked=' CHECKED'; }
		echo ' <INPUT TYPE="CHECKBOX" name="page_status"'.$checked.'>'.__('Published');

		if ($page_edit=='add'){
			echo ' <input type="Submit" name="add_page" value="'.__('Save').'">';
		}
		else{
			echo ' <input type="Submit" name="change_page" value="'.__('Save').'">';
		}

		echo ' '.__('Visitors counter').': '.$page_counter;

		echo '<br>';
		//echo '<textarea cols="50" rows="5" name="page_text">'.$page_text.'</textarea><br>';
		echo '<textarea class="ckeditor" name="page_text">'.$page_text.'</textarea>';

		echo '</form>';

	echo '</td></tr></table>';

	// *** Updated CKEditor ***
	//echo '<script src="include/ckeditor/ckeditor.js"></script>';

	// *** KCfinder settings ***
	//$_SESSION['KCFINDER'] = array(
	//	'disabled' => false,
	//	'uploadURL' => "/users/" . $user['username'] . "/upload",
	//	'uploadDir' = ""
	//);

	$_SESSION['KCFINDER'] = array();
	// *** Enable KCFinder ***
	$_SESSION['KCFINDER']['disabled'] = false;
	if (isset($humo_option["cms_images_path"])){
		// *** Set KCfinder pathes ***
		$_SESSION['KCFINDER']['uploadDir'] = "";

		$cms_images_path=$humo_option["cms_images_path"]; if (substr($cms_images_path,0,1)=='|') $cms_images_path='../../../media/cms';
		$_SESSION['KCFINDER']['uploadURL'] = $cms_images_path;;
	}
	else{
		//$_SESSION['KCuploadURL']='upload';
		//$_SESSION['KCFINDER']['uploadDir'] = "";
	}

	// *** Roxy File Manager: File browser for CKEditor ***
	/*
	if (isset($humo_option["cms_images_path"]))
		$_SESSION['fileman_files_root'] = $humo_option["cms_images_path"];
	else $_SESSION['fileman_files_root'] = '';
	echo "<script>
		var roxyFileman = 'include/fileman/index.html?integration=ckeditor';
		$(function(){
			CKEDITOR.replace( 'page_text',{filebrowserBrowseUrl:roxyFileman, 
				filebrowserImageBrowseUrl:roxyFileman+'&type=image',
				removeDialogTabs: 'link:upload;image:upload'});
		});
	</script>";
	*/

	// *** Updated CKEditor ***
	echo '<script src="include/ckeditor/ckeditor.js"></script>';
}

// *** Show and edit menu's ***
//if (isset($_POST['cms_menu']) OR isset($_GET['select_menu'])){
if ($cms_item=='menu'){
	// *** List of categories ***
	echo __('Add and edit menu/ category items:').'<br>';

	echo '<table class="humo standard" border="1">';

	echo '<tr class="table_header"><th>'.__('Order').'</th><th>'.__('Menu item/ category').'</th><th>Save</th></tr>';

	$qry=$dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
	$count_menu=$qry->rowCount();
	while($cms_pagesDb=$qry->fetch(PDO::FETCH_OBJ)){

		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="cms_menu" value="cms_menu">';
		echo '<input type="hidden" name="menu_id" value="'.$cms_pagesDb->menu_id.'">';

		echo '<tr>';

		echo '<td>';
		//if ($cms_pagesDb->menu_order<10){ echo '0'; }
		//echo $cms_pagesDb->menu_order;

		echo '<a href="index.php?page='.$page.'&amp;select_menu='.$cms_pagesDb->menu_id.'&amp;menu_remove='.$cms_pagesDb->menu_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" alt="'.__('Remove menu').'" border="0"></a>';

		if ($cms_pagesDb->menu_order!='1'){
			echo ' <a href="index.php?page='.$page.'&amp;select_menu='.$cms_pagesDb->menu_id.'&amp;menu_up='.$cms_pagesDb->menu_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>'; }
		if ($cms_pagesDb->menu_order!=$count_menu){
			echo ' <a href="index.php?page='.$page.'&amp;select_menu='.$cms_pagesDb->menu_id.'&amp;menu_down='.$cms_pagesDb->menu_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>'; }
		echo '</td>';

		//echo ' <a href="index.php?page='.$page.'&amp;select_page='.$cms_pagesDb->menu_id.'">'.$cms_pagesDb->menu_name.'</a><br>';
		echo '<td><input type="text" name="menu_name" value="'.$cms_pagesDb->menu_name.'" size=50></td>';

		echo '<td><input type="Submit" name="change_menu" value="'.__('Save').'"></td>';
		echo '</tr>';
		echo '</form>';
	}

	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="cms_menu" value="cms_menu">';

	echo '<tr bgcolor="green"><td><br></td>';
	echo '<td><input type="text" name="menu_name" value="" size=50></td>';
	echo '<td><input type="Submit" name="add_menu" value="'.__('Add').'"></td>';
	echo '</tr>';
	
	echo '</form>';
	
	echo '</table>';
}

//if (isset($_POST['cms_settings'])){
if ($cms_item=='settings'){
	// *** Automatic installation or update ***
	if (!isset($humo_option["cms_images_path"])){
		$sql="INSERT INTO humo_settings SET setting_variable='cms_images_path', setting_value='|'";
		@$result=$dbh->query($sql);
		$cms_images_path='|';
	}
	else{
		$cms_images_path=$humo_option["cms_images_path"];
	}

	// *** Automatic installation or update ***
	if (!isset($humo_option["main_page_cms_id"])){
		$sql="INSERT INTO humo_settings SET setting_variable='main_page_cms_id', setting_value=''";
		@$result=$dbh->query($sql);
		$main_page_cms_id='';
	}
	else{
		$main_page_cms_id=$humo_option["main_page_cms_id"];
	}
	
	if (isset($_POST['cms_images_path'])){
		$cms_images_path=$_POST['cms_images_path'];
		if (substr($_POST['cms_images_path'],0,1)=='|'){
			if (isset($_POST['default_path']) AND $_POST['default_path']=='no') $cms_images_path=substr($cms_images_path,1);
		}
		else{
			if (isset($_POST['default_path']) AND $_POST['default_path']=='yes') $cms_images_path='|'.$cms_images_path;
		}

		// *** Save settings***
		$result = $db_functions->update_settings('cms_images_path',$cms_images_path);

		//$humo_option["cms_images_path"]=$_POST["cms_images_path"];
		//$cms_images_path=$humo_option["cms_images_path"];
		$humo_option["cms_images_path"]=$cms_images_path;
		$cms_images_path=$humo_option["cms_images_path"];
	}

	if (isset($_POST['main_page_cms_id'])){
		// *** Save settings***
		$result = $db_functions->update_settings('main_page_cms_id',$_POST["main_page_cms_id"]);

		$humo_option["main_page_cms_id"]=$_POST["main_page_cms_id"];
		$main_page_cms_id=$humo_option["main_page_cms_id"];
	}

	if (isset($_POST['languages_choice']) AND $_POST['languages_choice']=="all"){ 
		// admin chose to use one page for all languages - delete any language_specific entries if set (format: main_page_cms_id_nl etc)
		// note that because of the last underline before the %, the default main_page_id will not be affected!
		$dbh->query("DELETE FROM humo_settings WHERE setting_variable LIKE 'main_page_cms_id_%'");
	}

	if ($_POST['cms_settings']!='1'){
		if (isset($_POST['languages_choice']) AND $_POST['languages_choice']=="specific"){  
			// admin chose to use different pages for specific languages
			for ($i=0; $i<count($language_file); $i++){
				if (!isset($humo_option["main_page_cms_id_".$language_file[$i]])) {
					$dbh->query("INSERT INTO humo_settings SET setting_variable='main_page_cms_id_".$language_file[$i]."', setting_value='".$_POST['main_page_cms_id_'.$language_file[$i]]."'");
				}
				else {
					// *** Save settings***
					$result = $db_functions->update_settings('main_page_cms_id_'.$language_file[$i],$_POST['main_page_cms_id_'.$language_file[$i]]);
				}
			}
		}
	}

	echo '<p><form method="post" name="cms_setting_form" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="cms_settings" value="1">'; // if Save button is not pressed but checkboxes changed!
	echo '<table class="humo" border="1" cellspacing="0" width="80%">';

	echo '<tr class="table_header"><th>'.__('CMS Settings').'</th><th><input type="Submit" name="cms_settings" value="'.__('Change').'"></th></tr>';

	echo '<tr><td>';

		echo __('Path for pictures in CMS pages').':<br>';
		//echo 'media/cms<br>';
		echo __('To point the main humo-gen folder, use ../../../foldername<br>
To point to a folder outside (and parallel to) the humo-gen folder, use ../../../../foldername');

	echo '</td><td>';

		// *** Picture path. A | character is used for a default path (the old path will remain in the field) ***
		if (substr($cms_images_path,0,1)=='|'){
			$checked1 = ' checked'; $checked2 = '';
		}
		else{
			$checked1 = ''; $checked2 = ' checked';
		}
		//$tree_pict_path=$data2Db->tree_pict_path;
		if (substr($cms_images_path,0,1)=='|') $cms_images_path=substr($cms_images_path,1);

		echo '<input type="radio" value="yes" name="default_path" '.$checked1.'> '.__('Use default picture path:').' <b>media/cms</b><br>';
		echo '<input type="radio" value="no" name="default_path" '.$checked2.'> ';

		echo '<input type="text" name="cms_images_path" value="'.$cms_images_path.'" size=25>';

	echo '</td></tr>';

	echo '<tr><td>';

		printf(__('Select main homepage (welcome page for visitors) for %s<br>
<b>The selected CMS page will replace the main index!</b>'),'HuMo-genealogy');

	echo '</td><td>';

	$lang_qry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable LIKE 'main_page_cms_id_%'"); // check if there are language-specific entries
	$num = $lang_qry->rowCount();
	$checked1 = ' checked'; $checked2 = '';
	if (isset($_POST['languages_choice'])){
		if (($num >=1 AND $_POST['languages_choice']!="all") OR ($num <1 AND $_POST['languages_choice']=="specific"))
		{	// there are language specific entries so don't check the radiobox "Use for all languages"
			$checked1 = ''; $checked2 = ' checked';
		}
	}
	//else  { 
	//	$checked1 = ' checked'; $checked2 = '';
	//}
	echo '<input type="radio" onChange="document.cms_setting_form.submit()" value="all" name="languages_choice" '.$checked1.'> '.__('Use for all languages');
	echo ' <select size="1" name="main_page_cms_id">';
	echo "<option value=''>* ".__('Standard main index')." *</option>\n";
	$qry=$dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
	while($pageDb=$qry->fetch(PDO::FETCH_OBJ)){
		$select=''; if ($pageDb->page_id==$main_page_cms_id){ $select=' SELECTED'; }
		echo '<option value="'.$pageDb->page_id.'"'.$select.'>'.$pageDb->page_title.'</option>';
	}
	echo "</select><br>";
	echo '<br><input type="radio" onChange="document.cms_setting_form.submit()" value="specific" name="languages_choice" '.$checked2.'> '.__('Set per language');

	if($checked1==''){
		echo '<br><table style="border:none">';
 		for ($i=0; $i<count($language_file); $i++){
			include(CMS_ROOTPATH.'languages/'.$language_file[$i].'/language_data.php');
			echo '<tr><td><img src="'.CMS_ROOTPATH.'languages/'.$language_file[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none;"> ';
			echo $language["name"];
			echo '</td><td>';
			$select_page='dummy';
			$qry=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'main_page_cms_id_".$language_file[$i]."'");
			while($lang_pageDb=$qry->fetch(PDO::FETCH_OBJ)){
				$select_page = $lang_pageDb->setting_value;
			}
			$sel='';
			if($select_page != 'dummy' AND $select_page != '') $sel = $select_page; // a specific page was set
			elseif($select_page == 'dummy') $sel = $main_page_cms_id;  // no entry was found - use default
			//else the value was '' which means language was set individually to "main index", so don't set "select" so "main index" will show
			echo '<select size="1" name="main_page_cms_id_'.$language_file[$i].'">';
			echo "<option value=''>* ".__('Standard main index')." *</option>\n";
			$qry=$dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
			while($pageDb=$qry->fetch(PDO::FETCH_OBJ)){
				$select=''; 
				if($pageDb->page_id==$sel) { $select=' SELECTED'; $special_found=1;}
				echo '<option value="'.$pageDb->page_id.'"'.$select.'>'.$pageDb->page_title.'</option>';
			}
			echo "</select>";
			echo '</td></tr>';
		}
		echo '</table>'; // end table with language flags and pages
	}

	echo '</td></tr></table>';

	echo '</form>';

	echo '<h2>In some cases the picture-path setting doesn\'t work...</h2>';
	echo '<b>First try renaming or removing file: admin\php.ini.</b><br><br>';
	echo '<b>If you need this setting, you can manual set this picture path in this file: admin/include/kcfinder/conf/config.php<br>';
	echo 'Change "upload" into your picture path: \'uploadURL\' => "upload",<br>';
	echo 'Change "true" into "false": \'disabled\' => true,</b>';

}
?>