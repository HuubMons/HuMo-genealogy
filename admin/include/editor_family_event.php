<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

$phpself.='#event_family_link';

if (isset($_POST['marriage_event_add'])){
	// *** Generate new order number ***
	$event_sql="SELECT * FROM ".$tree_prefix."events
		WHERE event_family_id='".$marriage."' AND event_kind='".$_POST["event_kind"]."'
		ORDER BY event_order DESC LIMIT 0,1";
	//$event_qry=mysql_query($event_sql,$db);
	//$eventDb=mysql_fetch_object($event_qry);
	$event_qry=$dbh->query($event_sql);
	$eventDb=$event_qry->fetch(PDO::FETCH_OBJ);	
	$event_order=0;
	if (isset($eventDb->event_order)){ $event_order=$eventDb->event_order; }
	$event_order++;

	$sql="INSERT INTO ".$tree_prefix."events SET
		event_family_id='".$marriage."',
		event_kind='".$_POST["event_kind"]."',
		event_order='".$event_order."',
		event_new_date='".$gedcom_date."',
		event_new_time='".$gedcom_time."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_POST['marriage_event_change'])){
	foreach($_POST['marriage_event_id'] as $key=>$value){  
		$event_event=$editor_cls->text_process($_POST["text_event"][$key]);
		if (isset($_POST["text_event2"][$key]) AND $_POST["text_event2"][$key]!=''){ $event_event=$editor_cls->text_process($_POST["text_event2"][$key]); }

		$sql="UPDATE ".$tree_prefix."events SET
		event_event='".$event_event."',";
		if (isset($_POST["event_gedcom"][$key])){ $sql.="event_gedcom='".safe_text($_POST["event_gedcom"][$key])."',"; }
		$sql.="event_date='".$editor_cls->date_process("event_date",$key)."',
		event_place='".$editor_cls->text_process($_POST["event_place"][$key])."',
		event_changed_date='".$gedcom_date."',
		event_changed_time='".$gedcom_time."'";
		if (isset($_POST["event_text"][$key])){ $sql.=", event_text='".$editor_cls->text_process($_POST["event_text"][$key])."'"; }
		$sql.=" WHERE event_id='".safe_text($_POST["marriage_event_id"][$key])."'";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	family_tree_update($tree_prefix);
}

// *** Remove event ***
if (isset($_GET['family_event_drop'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to remove this event?').' ';
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="event_family" value="event_family">';
	echo '<input type="hidden" name="event_kind" value="'.$_GET['event_kind'].'">';
	echo '<input type="hidden" name="family_event_drop" value="'.$_GET['family_event_drop'].'">';
	echo ' <input type="Submit" name="family_event_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo '<input type="hidden" name="marriage_nr" value="'.$_GET['marriage_nr'].'">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['family_event_drop2'])){
	$event_kind=safe_text($_POST['event_kind']);
	$event_order_id=safe_text($_POST['family_event_drop']);

	$sql="DELETE FROM ".$tree_prefix."events
		WHERE event_family_id='".$marriage."' AND event_kind='".$event_kind."'
		AND event_order='".$event_order_id."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$event_sql="SELECT * FROM ".$tree_prefix."events
		WHERE event_family_id='".$marriage."' AND event_kind='".$event_kind."'
		AND event_order>'".$event_order_id."' ORDER BY event_order";
	//$event_qry=mysql_query($event_sql,$db);
	$event_qry=$dbh->query($event_sql);
	//while($eventDb=mysql_fetch_object($event_qry)){
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		$sql="UPDATE ".$tree_prefix."events SET
		event_order='".($eventDb->event_order-1)."',
		event_changed_date='".$gedcom_date."',
		event_changed_time='".$gedcom_time."'
		WHERE event_id='".$eventDb->event_id."'";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
}

if (isset($_GET['family_event_down'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET["family_event_down"]);

	$sql="UPDATE ".$tree_prefix."events SET event_order='99'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order='".$event_order."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."events SET event_order='".$event_order."'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order='".($event_order+1)."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."events SET event_order='".($event_order+1)."'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order=99";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_GET['family_event_up'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET["family_event_up"]);

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='99'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order='".$event_order."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='".$event_order."'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order='".($event_order-1)."'";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='".($event_order-1)."'
	WHERE event_family_id='".$marriage."'
	AND event_kind='".$event_kind."'
	AND event_order=99";
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

//echo '<tr><th class="table_header_large" colspan="4">';

	echo '<h2 align="center">'.__('Events by marriage').'</h2>';
	echo '<table class="humo standard" border="1">';

	echo '<form method="POST" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="event_family" value="event_family">';
	echo '<input type="hidden" name="marriage_nr" value="'.$marriage.'">';

//echo '</th></tr>';

//$data_list_qry=mysql_query("SELECT * FROM ".$tree_prefix."events
//	WHERE event_family_id='$marriage' ORDER BY event_kind, event_order",$db);
$data_list_qry=$dbh->query("SELECT * FROM ".$tree_prefix."events
	WHERE event_family_id='$marriage' ORDER BY event_kind, event_order");	

echo '<tr class="table_header_large"><th><a name="event_family_link">'.__('Option').'</th><th colspan="2">'.__('Value').'</th>';
	echo '<td>';
	//if (mysql_num_rows($data_list_qry)>0){
	if ($data_list_qry->rowCount() >0){
		echo '<input type="submit" name="marriage_event_change" title="submit" value="'.__('Save').'">';
	}
	echo '</td>';
echo '</tr>';

$change_bg_colour='';
//while($data_listDb=mysql_fetch_object($data_list_qry)){
while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
	echo '<input type="hidden" name="marriage_event_id['.$data_listDb->event_id.']" value="'.$data_listDb->event_id.'">';

	echo '<tr'.$change_bg_colour.'><td>';

		echo '<a href="#event_family_link" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a> ';

		echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_family=1&amp;event_kind='.$data_listDb->event_kind.'&amp;family_event_drop='.
			$data_listDb->event_order.'&amp;marriage_nr='.$marriage.'#event_family_link"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

		// *** Count number of events ***
		//$count_event=mysql_query("SELECT * FROM ".$tree_prefix."events
		//	WHERE event_family_id='$marriage' AND event_kind='".$data_listDb->event_kind."'",$db);
		//$count=mysql_num_rows($count_event);
		$count_event=$dbh->query("SELECT * FROM ".$tree_prefix."events
			WHERE event_family_id='$marriage' AND event_kind='".$data_listDb->event_kind."'");
		$count=$count_event->rowCount();		

		if ($data_listDb->event_order<$count){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_family=1&amp;family_event_down='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'&amp;marriage_nr='.$marriage.'#event_family_link"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		if ($data_listDb->event_order>1){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_family=1&amp;family_event_up='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'&amp;marriage_nr='.$marriage.'#event_family_link"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

	echo '</td>';

	// *** Event text ***
	echo '<td style="border-right:0px;">';
		if ($data_listDb->event_kind=='event'){ echo __('Event'); }
		elseif ($data_listDb->event_kind=='marriage_witness'){ echo __('Marriage Witness'); }
		elseif ($data_listDb->event_kind=='marriage_witness_rel'){ echo __('Marriage Witness (church)'); }
		else {
			// *** Not in use yet ***
			echo ucfirst($data_listDb->event_kind);
		}
		/*
		if ($data_listDb->event_kind=='event'){
			echo '<select size="1" name="event_gedcom" style="width: 150px">';
				event_option($data_listDb->event_gedcom,'EVEN');
				event_option($data_listDb->event_gedcom,'_MBON');
				event_option($data_listDb->event_gedcom,'MARC');
				event_option($data_listDb->event_gedcom,'MARL');
				event_option($data_listDb->event_gedcom,'MARS');
				event_option($data_listDb->event_gedcom,'DIVF');
				event_option($data_listDb->event_gedcom,'ANUL');
				event_option($data_listDb->event_gedcom,'ENGA');
				event_option($data_listDb->event_gedcom,'SLGS');
			echo '</select>';
		}
		*/
	echo '</td>';

	// *** Witness and declaration persons ***
	if ($data_listDb->event_kind=='marriage_witness' OR $data_listDb->event_kind=='marriage_witness_rel'){
		echo '<td style="border-left:0px;">';
		witness_edit($data_listDb->event_event,'['.$data_listDb->event_id.']');
		echo '</td>';
	}
	else{
		echo '<td style="border-left:0px;"><input type="text" name="text_event['.$data_listDb->event_id.']" value="'.$data_listDb->event_event.'" size="60">';
		if ($data_listDb->event_kind=='event'){
			echo ' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';
				event_option($data_listDb->event_gedcom,'EVEN');
				event_option($data_listDb->event_gedcom,'_MBON');
				event_option($data_listDb->event_gedcom,'MARC');
				event_option($data_listDb->event_gedcom,'MARL');
				event_option($data_listDb->event_gedcom,'MARS');
				event_option($data_listDb->event_gedcom,'DIVF');
				event_option($data_listDb->event_gedcom,'ANUL');
				event_option($data_listDb->event_gedcom,'ENGA');
				event_option($data_listDb->event_gedcom,'SLGS');
			echo '</select>';
		}
		echo '</td>';
	}

	echo '<td>';
		// *** Calculate and show nr. of sources ***
		$connect_qry="SELECT *
			FROM ".$tree_prefix."connections
			WHERE connect_kind='family' AND connect_sub_kind='event_source'
			AND connect_connect_id='".$data_listDb->event_id."'";
		//$connect_sql=mysql_query($connect_qry,$db);
		$connect_sql=$dbh->query($connect_qry);
		//echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_kind=family&connect_sub_kind=fam_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
		echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&event_family=1&connect_kind=family&connect_sub_kind=fam_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
		//echo ' ['.mysql_num_rows($connect_sql).']</a>';
		echo ' ['.$connect_sql->rowCount().']</a>';
	echo '</td>';

	echo '</tr>';


	// *** Date and place ***
	echo '<tr'.$change_bg_colour.' style="display:none;" id="row'.$data_listDb->event_id.'00" name="row'.$data_listDb->event_id.'00"><td></td>';
		echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($data_listDb->event_date,"event_date","[$data_listDb->event_id]").' '.__('place').' <input type="text" name="event_place['.$data_listDb->event_id.']" value="'.$data_listDb->event_place.'" size="'.$field_date.'">';
		echo '</td><td>';
	echo '</td></tr>';

	// *** Text ***
	echo '<tr'.$change_bg_colour.' style="display:none;" id="row'.$data_listDb->event_id.'00" name="row'.$data_listDb->event_id.'00"><td></td><td style="border-right:0px;">'.__('text').'</td>';
	echo '<td style="border-left:0px;"><textarea rows="1" name="event_text['.$data_listDb->event_id.']" '.$field_text.'>'.$editor_cls->text_show($data_listDb->event_text).'</textarea></td><td></td></tr>';

	if ($change_bg_colour!=''){ $change_bg_colour=''; }
		else{ $change_bg_colour=' class="humo_color"'; }
}
echo '</form>';


echo '<form method="POST" action="'.$phpself.'">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<input type="hidden" name="event_family" value="event_family">';
echo '<input type="hidden" name="marriage_nr" value="'.$marriage.'">';
echo '<tr bgcolor="#CCFFFF"></td><td></td><td style="border-right:0px;">'.__('Add event').'</td><td style="border-left:0px;">';
	echo '<select size="1" name="event_kind">';
	//echo '<option value="address">Address</option>';
	//echo '<option value="picture">Picture</option>';
	//echo '<option value="source">'.__('source').'</option>';
	echo '<option value="event">'.__('Event').'</option>';
	echo '<option value="marriage_witness">'.__('Marriage Witness').'</option>';
	echo '<option value="marriage_witness_rel">'.__('Marriage Witness (church)').'</option>';
	echo '</select>';
echo '</td><td><input type="Submit" name="marriage_event_add" value="'.__('Add').'"></td><tr>';
echo '</form>';

echo '</table><br>'."\n";
echo '<br>'."\n";
?>