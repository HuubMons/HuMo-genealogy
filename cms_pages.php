<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

//include_once(CMS_ROOTPATH."include/language_date.php");
//include_once(CMS_ROOTPATH."include/date_place.php");

echo '<div id="mainmenu_centerbox">';

	echo '<div id="mainmenu_left">';
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='0' AND page_status!='' ORDER BY page_order");
		while($cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ)) {
			echo '<a href="'.CMS_ROOTPATH.'cms_pages.php?select_page='.$cms_pagesDb->page_id.'">'.$cms_pagesDb->page_title.'</a><br>';
		}

		$qry= $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
		while($cmsDb = $qry->fetch(PDO::FETCH_OBJ)) {
			echo '<p><b>'.$cmsDb->menu_name.'</b><br>';
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='".$cmsDb->menu_id."' AND page_status!='' ORDER BY page_order");
			while($cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ)) {
				echo '<a href="'.CMS_ROOTPATH.'cms_pages.php?select_page='.$cms_pagesDb->page_id.'">'.$cms_pagesDb->page_title.'</a><br>';
			}
		}

	echo '</div>';

	echo '<div id="mainmenu_center_alt" style="text-align:left;">';

		if (isset($_GET['select_page'])){
			$select_page=safe_text($_GET['select_page']);
		}
		else{
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order ASC LIMIT 0,1");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			$select_page=$cms_pagesDb->page_id;
		}
		
		//if (isset($_GET['select_page'])){
			// *** Show page ***
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='".$select_page."' AND page_status!=''");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			echo $cms_pagesDb->page_text;

			// *** Raise page counter ***
			// Only count page once every session
			$session_counter[]='';
			//@$itemteller=$cms_pagesDb->page_counter;
			$visited=0;
			if (isset($_SESSION["opslag_sessieteller"])){ $session_counter=$_SESSION["opslag_sessieteller"]; }
			for ($i=0; $i<=count($session_counter)-1; $i++) {
				if (@$cms_pagesDb->page_id==$session_counter[$i]){
				$visited=1;
				break;
				}
			}
			// *** Only raise counter at 1st visit of a session ***
			if ($visited==0){
				$session_counter[]=$cms_pagesDb->page_id;
				$_SESSION["opslag_sessieteller"]=$session_counter;
				$itemteller=$cms_pagesDb->page_counter+1;
				$sql="UPDATE humo_cms_pages SET page_counter='".$itemteller."' WHERE page_id=".$cms_pagesDb->page_id."";
				$dbh->query($sql);
			}

		//}

	echo '</div>';
		
echo '</div>';

include_once(CMS_ROOTPATH."footer.php");
?>