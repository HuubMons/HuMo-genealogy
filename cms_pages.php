<?php
include_once __DIR__ .'/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ .'/menu.php';

if ($user['group_menu_cms']!='y'){
	echo 'BEVEILIGDE BLADZIJDE/ SECURED PAGE';
	die();
}

echo '<div id="mainmenu_centerbox">';

	echo '<div id="mainmenu_left">';
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='0' AND page_status!='' ORDER BY page_order");
		while($cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ))
		{
			echo '<a href="'.CMS_ROOTPATH.'cms_pages.php?select_page='.$cms_pagesDb->page_id.'">'.$cms_pagesDb->page_title.'</a><br>';
		}

		$qry= $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
		while($cmsDb = $qry->fetch(PDO::FETCH_OBJ))
		{
			echo '<p><b>'.$cmsDb->menu_name.'</b><br>';
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='".$cmsDb->menu_id."' AND page_status!='' ORDER BY page_order");
			while($cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ))
			{
				if ($humo_option["url_rewrite"]=="j"){
					echo '<a href="'.CMS_ROOTPATH.'cms_pages/'.$cms_pagesDb->page_id.'/">'.$cms_pagesDb->page_title.'</a><br>';
				}
				else{
					echo '<a href="'.CMS_ROOTPATH.'cms_pages.php?select_page='.$cms_pagesDb->page_id.'">'.$cms_pagesDb->page_title.'</a><br>';
				}
			}
		}
	echo '</div>';

	echo '<div id="mainmenu_center_alt" style="text-align:left;">';
		if (isset($_GET['select_page']) AND (is_numeric($_GET['select_page'])) ){
			$select_page=$_GET['select_page'];
		}
		//elseif(isset($urlpart[0]) AND (is_numeric($urlpart[0])) ){
		//	$select_page=$urlpart[0];
		//}
		else{
			// *** First page in a menu ***
			$page_qry = $dbh->query("SELECT * FROM humo_cms_menu, humo_cms_pages
				WHERE page_status!='' AND page_menu_id=menu_id
				ORDER BY menu_order, page_order ASC LIMIT 0,1");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			if (isset($cms_pagesDb->page_id)) $select_page=$cms_pagesDb->page_id;

			// *** First pages without a menu (if present) ***
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id=0 ORDER BY page_order ASC LIMIT 0,1");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			if (isset($cms_pagesDb->page_id)) $select_page=$cms_pagesDb->page_id;
		}

		// *** Show page ***
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='".safe_text_db($select_page)."' AND page_status!=''");
		$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
		echo $cms_pagesDb->page_text;

		// *** Raise page counter ***
		// Only count page once every session
		$session_counter[]='';
		$visited=0;
		if (isset($_SESSION["opslag_sessieteller"])) $session_counter=$_SESSION["opslag_sessieteller"];
		for ($i=0; $i<=count($session_counter)-1; $i++)
		{
			if (@$cms_pagesDb->page_id==$session_counter[$i])
			{
				$visited=1;
				break;
			}
		}
		// *** Only raise counter at 1st visit of a session ***
		if ($visited==0)
		{
			$session_counter[]=$cms_pagesDb->page_id;
			$_SESSION["opslag_sessieteller"]=$session_counter;
			$itemteller=$cms_pagesDb->page_counter+1;
			$sql="UPDATE humo_cms_pages SET page_counter='".$itemteller."' WHERE page_id=".$cms_pagesDb->page_id."";
			$dbh->query($sql);
		}
	echo '</div>';

echo '</div>';

include_once __DIR__ .'/footer.php';
