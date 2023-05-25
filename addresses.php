<?php
include_once("header.php"); // *** returns CMS_ROOTPATH constant ***
include_once(CMS_ROOTPATH . "menu.php");

// *** Check user authority ***
if ($user['group_addresses'] != 'j') {
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH . "include/language_date.php");

$desc_asc = " ASC ";
$sort_desc = 0;
if (isset($_SESSION['sort_desc'])) {
	if ($_SESSION['sort_desc'] == 1) {
		$desc_asc = " DESC ";
		$sort_desc = 1;
	} else {
		$desc_asc = " ASC ";
		$sort_desc = 0;
	}
}
if (isset($_GET['sort_desc'])) {
	if ($_GET['sort_desc'] == 1) {
		$desc_asc = " DESC ";
		$sort_desc = 1;
		$_SESSION['sort_desc'] = 1;
	} else {
		$desc_asc = " ASC ";
		$sort_desc = 0;
		$_SESSION['sort_desc'] = 0;
	}
}
$selectsort = '';
if (isset($_SESSION['sort']) and !isset($_GET['sort'])) {
	$selectsort = $_SESSION['sort'];
}
if (isset($_GET['sort'])) {
	//if($_GET['sort']=="sort_country") { $selectsort="sort_country"; $_SESSION['sort']=$selectsort; }
	//if($_GET['sort']=="sort_state") { $selectsort="sort_state"; $_SESSION['sort']=$selectsort; }
	if ($_GET['sort'] == "sort_place") {
		$selectsort = "sort_place";
		$_SESSION['sort'] = $selectsort;
	}
	if ($_GET['sort'] == "sort_address") {
		$selectsort = "sort_address";
		$_SESSION['sort'] = $selectsort;
	}
}
//$orderby = " address_country ".$desc_asc.", address_state".$desc_asc.", address_place".$desc_asc.", address_address".$desc_asc;
$orderby = " address_place" . $desc_asc . ", address_address" . $desc_asc;
if ($selectsort) {
	//if($selectsort=="sort_country") {
	//	$orderby = " address_country ".$desc_asc.", address_state".$desc_asc.", address_place".$desc_asc.", address_address".$desc_asc;
	//}
	//if($selectsort=="sort_state") {
	//	$orderby = " address_state ".$desc_asc.", address_country".$desc_asc.", address_place".$desc_asc.", address_address".$desc_asc;
	//}
	if ($selectsort == "sort_place") {
		//$orderby = " address_place ".$desc_asc.", address_country".$desc_asc.", address_state".$desc_asc.", address_address".$desc_asc;
		$orderby = " address_place " . $desc_asc . ", address_address" . $desc_asc;
	}
	if ($selectsort == "sort_address") {
		$orderby = " address_address " . $desc_asc;
	}
}

$where = '';
//$adr_country=''; $adr_state='';
$adr_place = '';
$adr_address = '';
//if(isset($_POST['adr_country']) AND $_POST['adr_country'] != '') { $adr_country = $_POST['adr_country']; }
//if(isset($_POST['adr_state']) AND $_POST['adr_state'] != '') { $adr_state = $_POST['adr_state']; }
if (isset($_POST['adr_place']) and $_POST['adr_place'] != '') {
	$adr_place = $_POST['adr_place'];
}
if (isset($_POST['adr_address']) and $_POST['adr_address'] != '') {
	$adr_address = $_POST['adr_address'];
}

//if(isset($_GET['adr_country']) AND $_GET['adr_country'] != '') { $adr_country = $_GET['adr_country']; }
//if(isset($_GET['adr_state']) AND $_GET['adr_state'] != '') { $adr_state = $_GET['adr_state']; }
if (isset($_GET['adr_place']) and $_GET['adr_place'] != '') {
	$adr_place = $_GET['adr_place'];
}
if (isset($_GET['adr_address']) and $_GET['adr_address'] != '') {
	$adr_address = $_GET['adr_address'];
}

//if($adr_country OR $adr_state OR $adr_place OR $adr_address) {
if ($adr_place or $adr_address) {
	//if($adr_country!='') { $where .= " AND address_country LIKE '%".safe_text_db($adr_country)."%' "; }
	//if($adr_state!='') { $where .= " AND address_state LIKE '%".safe_text_db($adr_state)."%' "; }
	if ($adr_place != '') {
		$where .= " AND address_place LIKE '%" . safe_text_db($adr_place) . "%' ";
	}
	if ($adr_address != '') {
		$where .= " AND address_address LIKE '%" . safe_text_db($adr_address) . "%' ";
	}
}

echo '<h1 style="text-align:center;">' . __('Addresses') . '</h1>';
echo '<div>';
// *** Search form ***
echo ' <form method="POST" action="addresses.php" style="display : inline;">';
echo '<table class="humo" style="margin-left:auto;margin-right:auto">';

echo '<tr class=table_headline>';
//echo '<td>'.__('Country').':&nbsp;<input type="text" name="adr_country" size=15></td>';
//echo '<td>'.__('State').':&nbsp;<input type="text" name="adr_state" size=15></td>';
echo '<td>' . __('City') . ':&nbsp;<input type="text" name="adr_place" size=15></td>';
echo '<td>' . __('Street') . ':&nbsp;<input type="text" name="adr_address" size=15></td>';
echo '<input type="hidden" name="database" value="' . $database . '">';
echo '<td><input type="submit" value="' . __('Search') . '" name="search_addresses"></td>';
echo '</tr></table><br>';
echo '</form>';

// *** Show results ***
echo '<table class="humo" style="margin-left:auto;margin-right:auto">';
echo '<tr class=table_headline>';
//echo '<th>'.__('Country').'</th>';

//$style=''; $sort_reverse=$sort_desc; $img='';
//if ($selectsort=="sort_country"){
//	$style=' style="background-color:#ffffa0"';
//	$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
//}
//echo '<th><a href="addresses.php?database='.$database.'&adr_country='.$adr_country.'&adr_state='.$adr_state.'&adr_place='.$adr_place.'&adr_address='.$adr_address.'&sort=sort_country&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Country').' <img src="images/button3'.$img.'.png"></a>';

//$style=''; $sort_reverse=$sort_desc; $img='';
//if ($selectsort=="sort_state"){
//	$style=' style="background-color:#ffffa0"';
//	$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
//}
//echo '<th><a href="addresses.php?database='.$database.'&adr_place='.$adr_place.'&adr_address='.$adr_address.'&sort=sort_state&sort_desc='.$sort_reverse.'"'.$style.'>'.__('State').' <img src="images/button3'.$img.'.png"></a>';

$style = '';
$sort_reverse = $sort_desc;
$img = '';
if ($selectsort == "sort_place") {
	$style = ' style="background-color:#ffffa0"';
	$sort_reverse = '1';
	if ($sort_desc == '1') {
		$sort_reverse = '0';
		$img = 'up';
	}
}
//echo '<th><a href="addresses.php?database='.$database.'&adr_country='.$adr_country.'&adr_state='.$adr_state.'&adr_place='.$adr_place.'&adr_address='.$adr_address.'&sort=sort_place&sort_desc='.$sort_reverse.'"'.$style.'>'.__('City').' <img src="images/button3'.$img.'.png"></a>';
echo '<th><a href="addresses.php?database=' . $database . '&adr_place=' . safe_text_show($adr_place) . '&adr_address=' . safe_text_show($adr_address) . '&sort=sort_place&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('City') . ' <img src="images/button3' . $img . '.png"></a>';

$style = '';
$sort_reverse = $sort_desc;
$img = '';
if ($selectsort == "sort_address") {
	$style = ' style="background-color:#ffffa0"';
	$sort_reverse = '1';
	if ($sort_desc == '1') {
		$sort_reverse = '0';
		$img = 'up';
	}
}
//echo '<th><a href="addresses.php?database='.$database.'&adr_country='.$adr_country.'&adr_state='.$adr_state.'&adr_place='.$adr_place.'&adr_address='.$adr_address.'&sort=sort_address&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Street').' <img src="images/button3'.$img.'.png"></a>';
echo '<th><a href="addresses.php?database=' . $database . '&adr_place=' . safe_text_show($adr_place) . '&adr_address=' . safe_text_show($adr_address) . '&sort=sort_address&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Street') . ' <img src="images/button3' . $img . '.png"></a>';

echo '<th>' . __('Text') . '</th>';
echo '</tr>';

//$sql="SELECT * FROM humo_addresses WHERE address_tree_id='".$tree_id."' 
//	AND address_gedcomnr LIKE '_%' AND address_address LIKE '_%'".
//	$where." ORDER BY ".$orderby;
$sql = "SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' 
		AND address_shared='1'" .
	$where . " ORDER BY " . $orderby;
$address = $dbh->query($sql);

while (@$addressDb = $address->fetch(PDO::FETCH_OBJ)) {
	echo '<tr>';
	//echo '<td style="padding-left:5px;padding-right:5px">';
	//if($addressDb->address_country!='') { echo $addressDb->address_country; }
	//echo '</td><td style="padding-left:5px;padding-right:5px">';
	//if($addressDb->address_state!='')  { echo $addressDb->address_state; }
	//echo '</td>';
	echo '<td style="padding-left:5px;padding-right:5px">';
	if ($addressDb->address_place != '') echo $addressDb->address_place;
	echo '</td><td style="padding-left:5px;padding-right:5px">';
	if ($addressDb->address_address != '') {
		//echo '<a href="' . CMS_ROOTPATH . 'address.php?gedcomnumber=' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';

		//TEST
		if ($humo_option["url_rewrite"] == "j") {
			echo '<a href="' . CMS_ROOTPATH . 'address/' . $tree_id . '/' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
		} else {
			echo '<a href="' . CMS_ROOTPATH . 'index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
		}
	}
	echo '</td>';

	echo '<td>' . substr($addressDb->address_text, 0, 40);
	if (strlen($addressDb->address_text) > 40) echo '...';
	echo '</td>';
	echo '</tr>';
}
echo '</table>';
echo '</div>';
include_once(CMS_ROOTPATH . "footer.php");
