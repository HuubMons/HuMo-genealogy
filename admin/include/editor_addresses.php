<?php
//global $language, $tree_prefix, $pers_gedcomnumber, $db, $page, $gedcom_date, $gedcom_time;
if(CMS_SPECIFIC=="Joomla") {
	$phpself='index.php?option=com_humo-gen&amp;task=admin&amp;page=editor';
	$joomlastring='option=com_humo-gen&amp;task=admin&amp';  // can be placed after existing index?
}
else {
	$phpself=$_SERVER['PHP_SELF'];
	$joomlastring='';
}
// *** Remove living place ***
if (isset($_GET['living_place_drop'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to delete this place? ');
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="person_place_address" value="person_place_address">';	
	echo '<input type="hidden" name="living_place_id" value="'.$_GET['living_place_drop'].'">';
	echo ' <input type="Submit" name="living_place_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['living_place_drop2'])){
	$living_place_order=safe_text($_POST['living_place_id']);
	$sql="DELETE FROM ".$tree_prefix."addresses WHERE address_person_id='".$pers_gedcomnumber."'
		AND address_order='".$living_place_order."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$address_sql="SELECT * FROM ".$tree_prefix."addresses
		WHERE address_person_id='".$pers_gedcomnumber."' AND address_order>'".$living_place_order."'
		ORDER BY address_order";
	//$event_qry=mysql_query($address_sql,$db);
	$event_qry=$dbh->query($address_sql);
	//while($eventDb=mysql_fetch_object($event_qry)){
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		$sql="UPDATE ".$tree_prefix."addresses SET
		address_order='".($eventDb->address_order-1)."',
		address_changed_date='".$gedcom_date."',
		address_changed_time='".$gedcom_time."'
		WHERE address_id='".$eventDb->address_id."'";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
}

if (isset($_POST['living_place_add'])){
	$sql="INSERT INTO ".$tree_prefix."addresses SET
		address_person_id='".$pers_gedcomnumber."',
		address_date='".$editor_cls->date_process("address_date")."',
		address_place='".$_POST["address_place"]."',
		address_order='".safe_text($_POST['address_order'])."',
		address_new_date='".$gedcom_date."',
		address_new_time='".$gedcom_time."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_POST['living_place_change'])){
	$sql="UPDATE ".$tree_prefix."addresses SET
	address_place='".safe_text($_POST["address_place"])."',
	address_date='".$editor_cls->date_process("address_date")."',
	address_changed_date='".$gedcom_date."',
	address_changed_time='".$gedcom_time."'";
	$sql.=" WHERE address_id='".safe_text($_POST["address_id"])."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_GET['living_place_down'])){
	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='99'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order='".safe_text($_GET["living_place_down"])."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='".(safe_text($_GET['living_place_down']))."'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order='".(safe_text($_GET["living_place_down"])+1)."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='".(safe_text($_GET['living_place_down'])+1)."'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order=99";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_GET['living_place_up'])){
	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='99'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order='".safe_text($_GET["living_place_up"])."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='".(safe_text($_GET['living_place_up']))."'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order='".(safe_text($_GET["living_place_up"])-1)."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."addresses SET
	address_order='".(safe_text($_GET['living_place_up'])-1)."'
	WHERE address_person_id='".$pers_gedcomnumber."' AND address_order=99";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

// ********************************************************
// *** Standard living places (one place by one person) ***
// ********************************************************
echo '<h2 align="center">'.__('Domiciles (you can also attach a master address):').'</h2>';
echo '<table class="humo standard" border="1">';
echo '<tr class="table_header">';
	echo '<th>'.ucfirst(__('place')).'</th>';
	echo '<th>'.__('Date').'</th>';
	echo '<th>'.ucfirst(__('place')).'</th>';
echo '</tr>';
//$address_qry=mysql_query("SELECT * FROM ".$tree_prefix."addresses
//	WHERE address_person_id='".$pers_gedcomnumber."' ORDER BY address_order",$db);
//$address_count=mysql_num_rows($address_qry);
$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
	WHERE address_person_id='".$pers_gedcomnumber."' ORDER BY address_order");
$address_count=$address_qry->rowCount();
$address_nr=0;
//while($addressDb=mysql_fetch_object($address_qry)){
while($addressDb=$address_qry->fetch(PDO::FETCH_OBJ)){
	$address_nr++;
	echo '<FORM METHOD="POST" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="person_place_address" value="person_place_address">';
	echo '<input type="hidden" name="address_id" value="'.$addressDb->address_id.'">';

	echo '<tr><td>';
		echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;person_place_address=1&amp;living_place_drop='.
		$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0"></a>';

		if ($address_nr < $address_count){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;person_place_address=1&amp;living_place_down='.$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		if ($address_nr > 1){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;person_place_address=1&amp;living_place_up='.$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	echo '</td><td>';

		echo $editor_cls->date_show($addressDb->address_date,'address_date');

	echo '</td><td>';

		echo ' <input type="text" name="address_place" value="'.htmlspecialchars($addressDb->address_place).'" size="50">';
		//echo $addressDb->address_text.' ';
		//echo $addressDb->address_source.' ';

		//echo ' <button type="submit" name="living_place_change" title="submit" class="button"><img src="'.CMS_ROOTPATH_ADMIN.'images/submit.gif" width="16"></button>';
		echo ' <input type="submit" name="living_place_change" title="submit" value="'.__('Save').'">';

	echo '</td></tr>';

	echo '</form>';

	// *** Save last place for index places ***
	if ($address_nr==$address_count){
		$sql="UPDATE ".$tree_prefix."person SET
		pers_place_index='".safe_text($addressDb->address_place)."'
		WHERE pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
}

echo '<form method="POST" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="person_place_address" value="person_place_address">';
	echo '<tr bgcolor="#CCFFFF"><td>';
		echo __('Add');
	echo '</td><td>';
		echo $editor_cls->date_show("",'address_date');
	echo '</td><td>';
		echo ' <input type="text" name="address_place" value="" size="50">';
		echo '<input type="hidden" name="address_order" value="'.($address_nr+1).'">';
		echo' <input type="Submit" name="living_place_add" value="'.__('Add').'">';
	echo '</td><tr>';
echo '</form>';

echo '</table>';


// *******************************
// *** Select extended address ***
// *******************************

$text='';
//$text='<p>';
$text.='<h2 align="center">'.__('Addresses').' '.__('(extended address by a person)').'</h2>';

$text.= '<table class="humo standard" border="1">';
$text.= '<tr class="table_header">';
	$text.= '<th>'.__('Address').'</th>';
	$text.= '<th>'.__('Addressrole').'</th>';
	$text.= '<th>'.__('Date').'</th>';
	$text.= '<th>'.__('Address').'</th>';
$text.= '</tr>';

// *** Search for all connected sources ***
$connect_qry="SELECT * FROM ".$tree_prefix."connections
	WHERE connect_kind='person'
	AND connect_sub_kind='person_address'
	AND connect_connect_id='".safe_text($pers_gedcomnumber)."'
	ORDER BY connect_order";
//$text.=$connect_qry;
//$connect_sql=mysql_query($connect_qry,$db);
//$count=mysql_num_rows($connect_sql);
$connect_sql=$dbh->query($connect_qry);
$count=$connect_sql->rowCount();

//while($connectDb=mysql_fetch_object($connect_sql)){
while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
	$source_name=$connectDb->connect_id;

// *** For now: only use 1 value ***
$key='1';

	$text.='<form method="POST" action="'.$phpself.'">';
	$text.='<input type="hidden" name="page" value="'.$page.'">';
	$text.='<input type="hidden" name="person_place_address" value="person_place_address">';

	$text.='<input type="hidden" name="connect_change['.$key.']" value="'.$connectDb->connect_id.'">';

	$text.='<input type="hidden" name="connect_connect_id['.$key.']" value="'.$connectDb->connect_connect_id.'">';

	if (isset($marriage)){
		$text.='<input type="hidden" name="marriage_nr" value="'.$marriage.'">';
	}

	$text.='<input type="hidden" name="connect_kind['.$key.']" value="person">';
	$text.='<input type="hidden" name="connect_sub_kind['.$key.']" value="person_address">';

	$text.= '<tr><td>';

		$text.=' <a href="index.php?'.$joomlastring.'page='.$page.
			'&amp;person_place_address=1&amp;connect_drop='.$connectDb->connect_id.
			'&amp;connect_kind='.$connectDb->connect_kind.
			'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
			'&amp;connect_connect_id='.$connectDb->connect_connect_id;
			if (isset($marriage)){
				$text.='&amp;marriage_nr='.$marriage;
			}
			$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

		if ($connectDb->connect_order<$count){
			$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
			'&amp;person_place_address=1&amp;connect_down='.$connectDb->connect_id.
			'&amp;connect_kind='.$connectDb->connect_kind.
			'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
			'&amp;connect_connect_id='.$connectDb->connect_connect_id.
			'&amp;connect_order='.$connectDb->connect_order;
			if (isset($marriage)){
				$text.='&amp;marriage_nr='.$marriage;
			}
			$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
		}
		else{
			$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		if ($connectDb->connect_order>1){
			$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
			'&amp;person_place_address=1&amp;connect_up='.$connectDb->connect_id.
			'&amp;connect_kind='.$connectDb->connect_kind.
			'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
			'&amp;connect_connect_id='.$connectDb->connect_connect_id.
			'&amp;connect_order='.$connectDb->connect_order;
			if (isset($marriage)){
				$text.='&amp;marriage_nr='.$marriage;
			}
			$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
		}
		else{
			$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

	$text.='</td><td>';

		$text.= ' <input type="text" name="connect_role['.$key.']" value="'.htmlspecialchars($connectDb->connect_role).'" size="6">';

	$text.='</td><td>';

		$text.= '<input type="hidden" name="connect_page['.$key.']" value="">';

		$text.= $editor_cls->date_show($connectDb->connect_date,'connect_date',"[$key]");

		$text.= '<input type="hidden" name="connect_place['.$key.']" value="">';

	$text.='</td><td>';

		// *** Source ***
		// NO SOURCE YET
		$text.= '<input type="hidden" name="connect_source_id['.$key.']" value="">';
		$text.= '<input type="hidden" name="connect_text['.$key.']" value="">';

		// *** Only show addresses if a gedcomnumber is used (= link to full adres) ***
		//$addressqry=mysql_query("SELECT * FROM ".$tree_prefix."addresses WHERE address_gedcomnr LIKE '_%'
		//	ORDER BY address_place, address_address",$db);
		$addressqry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses WHERE address_gedcomnr LIKE '_%'
			ORDER BY address_place, address_address");			
		$text.= '<select size="1" name="connect_item_id['.$key.']" style="width: 300px">';
		$text.= '<option value="">'.__('Select address').'</option>';
		//while ($addressDb=mysql_fetch_object($addressqry)){
		while ($addressDb=$addressqry->fetch(PDO::FETCH_OBJ)){
			$selected='';
			if ($connectDb->connect_item_id==$addressDb->address_gedcomnr){ $selected=' SELECTED'; }
			$text.= '<option value="'.$addressDb->address_gedcomnr.'"'.$selected.'>'.
				@$addressDb->address_place.', '.$addressDb->address_address.' ['.@$addressDb->address_gedcomnr.']</option>';
		}
		$text.='</select>';

		//$text.= ' <BUTTON TYPE="submit" name="submit" title="submit" class="button"><img src="'.CMS_ROOTPATH_ADMIN.'images/submit.gif" width="16"></BUTTON>';
		$text.= ' <input type="submit" name="submit" title="submit" value="'.__('Save').'">';


		//$field_text='style="height: 40px; width:300px"';
		//$text.= '<br><textarea rows="2" name="connect_text" '.$field_text.'>'.$editor_cls->text_show($connectDb->connect_text).'</textarea>';

	$text.='</td></tr>';
	$text.='</form>';
}

// *** Add new address connection ***
$text.='<tr bgcolor="#CCFFFF"><td>'.__('Add').'</td>';
$text.='<td></td>';
$text.='<td></td>';
$text.='<td>';
	$text.='<form method="POST" action="'.$phpself.'">';
	$text.='<input type="hidden" name="page" value="'.$page.'">';
	$text.='<input type="hidden" name="person_place_address" value="person_place_address">';

	$text.='<input type="hidden" name="connect_kind" value="person">';
	$text.='<input type="hidden" name="connect_sub_kind" value="person_address">';
	$text.='<input type="hidden" name="connect_connect_id" value="'.safe_text($pers_gedcomnumber).'">';

	$text.=' <input type="Submit" name="connect_add" value="'.__('Add address').'">';
	$text.='</form>';
$text.='</td>';
$text.='</tr>';
$text.='</table><br>';

echo $text;
?>