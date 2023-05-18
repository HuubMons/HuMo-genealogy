<?php
 
include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ . '/menu.php';

// ***********************************************************************************************
// ** Main index class ***
// ***********************************************************************************************
include_once __DIR__ . '/include/mainindex_cls.php';
$mainindex = new mainindex_cls($dbh);

// *** Replace the main index by an own CMS page ***
$text='';
if (isset($humo_option["main_page_cms_id_".$selected_language]) AND $humo_option["main_page_cms_id_".$selected_language]) {
	// *** Show CMS page ***
	if (is_numeric($humo_option["main_page_cms_id_".$selected_language])){
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id_".$selected_language]."' AND page_status!=''");
		$cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ);
		$text=$cms_pagesDb->page_text;
	}
}
elseif (isset($humo_option["main_page_cms_id"]) AND $humo_option["main_page_cms_id"]){
	// *** Show CMS page ***
	if (is_numeric($humo_option["main_page_cms_id"])){
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id"]."' AND page_status!=''");
		$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
		$text=$cms_pagesDb->page_text;
	}
}

// *** Show slideshow ***
if (isset($humo_option["slideshow_show"]) AND $humo_option["slideshow_show"]=='y'){
	$mainindex->show_slideshow();
}

if ($text) { // *** Show cms page *** ?>
	<div id="mainmenu_centerbox">
		<?= $text; ; ?>
	</div>
<?php } else { // *** Show default HuMo-genealogy homepage ***
	
	$mainindex->show_tree_index();
}


include_once __DIR__ . '/footer.php';

