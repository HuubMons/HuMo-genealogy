<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//echo '<h1 align=center>'.__('Logfile users').'</h1>';

// *** Tab menu ***
if(CMS_SPECIFIC=="Joomla") {
	$prefx = ''; // in joomla the base folder is the main joomla map - not the HuMo-gen admin map
	$joomlastring="option=com_humo-gen&amp;task=admin&amp;";
}
else {
	$prefx = '../'; // to get out of the admin map
	$joomlastring="";
}

$menu_admin='log_users';
if (isset($_POST['menu_admin'])){ $menu_admin=$_POST['menu_admin']; }
if (isset($_GET['menu_admin'])){ $menu_admin=$_GET['menu_admin']; }

if (isset($_SESSION['tree_prefix'])) $tree_prefix=$_SESSION['tree_prefix'];
if (isset($_POST['tree_prefix'])){
	$tree_prefix=safe_text($_POST["tree_prefix"]);
	$_SESSION['tree_prefix']=safe_text($_POST['tree_prefix']);
}

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
	// <div class="pageHeadingText">Configuratie gegevens</div>
	// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

	echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
		echo '<ul class="pageTabs">';
			//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

			// *** Logfile users ***
			$select_item=''; if ($menu_admin=='log_users'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'">'.__('Logfile users')."</a></div></li>";

			// *** IP blacklist ***
			$select_item=''; if ($menu_admin=='log_blacklist'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=log_blacklist'.'">'.__('IP Blacklist')."</a></div></li>";

		echo '</ul>';
	echo '</div>';
echo '</div>';
echo '</div>';

// *** Align content to the left ***
echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';


// *** User log ***
if (isset($menu_admin) AND $menu_admin=='log_users'){

	$logbooksql="SELECT * FROM humo_user_log ORDER BY log_date DESC";
	$logbook=$dbh->query($logbooksql);

	echo '<table class="humo" border="1" cellspacing="0" width="auto">';
		//echo '<tr class="table_header"><th colspan="4">'.__('Logfile users').'</th></tr>';

		echo '<tr class="table_header">';
		echo '<th>'.__('Date - time').'</th>';
		echo '<th>'.__('User').'</th>';
		echo '<th>'.__('User/ Admin').'</th>';
		echo '<th>'.__('IP address').'</th>';
		echo '<th>'.__('Status').'</th>';
		echo '</tr>';

		while ($logbookDb=$logbook->fetch(PDO::FETCH_OBJ)){
			echo '<tr>';
			echo '<td>'.$logbookDb->log_date.'</td>';
			echo '<td>'.$logbookDb->log_username.'</td>';
			echo '<td>'.$logbookDb->log_user_admin.'</td>';
			echo '<td>'.$logbookDb->log_ip_address.'</td>';
			echo '<td>'.$logbookDb->log_status.'</td>';
			echo '</tr>';
		}
	echo '</table>';
}


// *** IP blacklist ***
if (isset($menu_admin) AND $menu_admin=='log_blacklist'){

	// *** Change Link ***
	if (isset($_POST['change_link'])){
		$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
		while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			$setting_value=$_POST[$dataDb->setting_id.'own_code']."|".$_POST[$dataDb->setting_id.'link_text'];
			$sql="UPDATE humo_settings SET setting_value='".safe_text($setting_value)."'
				WHERE setting_id=".safe_text($_POST[$dataDb->setting_id.'id']);
			$result=$dbh->query($sql);
		}
	}

	// *** Remove link  ***
	$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
	while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
		if (isset($_POST[$dataDb->setting_id.'remove_link'])){
			$sql="DELETE FROM humo_settings WHERE setting_id='".$dataDb->setting_id."'";
			$result=$dbh->query($sql);
		}
	}

	// *** Add link ***
	if (isset($_POST['add_link']) AND is_numeric ($_POST['link_order'])){
		$setting_value=$_POST['own_code']."|".$_POST['link_text'];
		$sql="INSERT INTO humo_settings SET setting_variable='ip_blacklist',
			setting_value='".safe_text($setting_value)."', setting_order='".safe_text($_POST['link_order'])."'";
		$result=$dbh->query($sql);
	}

	if (isset($_GET['up'])){
		// *** Search previous link ***
		$sql="SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' AND setting_order=".(safe_text($_GET['link_order'])-1);
		$item=$dbh->query($sql);
		$itemDb=$item->fetch(PDO::FETCH_OBJ);

		// *** Raise previous link ***
		$sql="UPDATE humo_settings SET setting_order='".safe_text($_GET['link_order'])."' WHERE setting_id='".$itemDb->setting_id."'";
		$result=$dbh->query($sql);

		// *** Lower link order ***
		$sql="UPDATE humo_settings SET setting_order='".(safe_text($_GET['link_order'])-1)."' WHERE setting_id=".safe_text($_GET['id']);
		$result=$dbh->query($sql);
	}
	if (isset($_GET['down'])){
		// *** Search next link ***
		$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' AND setting_order=".(safe_text($_GET['link_order'])+1));
		$itemDb=$item->fetch(PDO::FETCH_OBJ);

		// *** Lower previous link ***
		$sql="UPDATE humo_settings SET setting_order='".safe_text($_GET['link_order'])."' WHERE setting_id='".$itemDb->setting_id."'";

		$result=$dbh->query($sql);
		// *** Raise link order ***
		$sql="UPDATE humo_settings SET setting_order='".(safe_text($_GET['link_order'])+1)."' WHERE setting_id=".safe_text($_GET['id']);

		$result=$dbh->query($sql);
	}

	echo __('IP Blacklist: access to HuMo-gen will be totally blocked for these IP addresses.').'<br>';

	// *** Show all links ***
	if(CMS_SPECIFIC == "Joomla") {
		//print "<form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=links'>";
		print "<form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=log&amp;menu_admin=log_blacklist'>";
	}
	else {
		//print "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
		print "<form method='post' action='index.php?page=log&amp;menu_admin=log_blacklist'>";
	}
	echo '<input type="hidden" name="page" value="'.$page.'">';

	echo '<table class="humo" border="1">';
		print '<tr class="table_header"><th>Nr.</th><th>'.__('IP address').'</th><th>'.__('Description').'</th><th>'.__('Change / Add').'</th><th>'.__('Remove').'</th></tr>';
		$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' ORDER BY setting_order");
		// *** Number for new link ***
		$count_links=0; if ($datasql->rowCount()) $count_links=$datasql->rowCount();
		$new_number=1; if ($count_links) $new_number=$count_links+1;
		if ($datasql){
			$teller=1;
			while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
				$lijst=explode("|",$dataDb->setting_value);
				echo '<tr>';
				echo '<td>';
					echo '<input type="hidden" name="'.$dataDb->setting_id.'id" value="'.$dataDb->setting_id.'">'.$teller;

					if ($dataDb->setting_order!='1'){
						echo ' <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;up=1&amp;link_order='.$dataDb->setting_order.
						'&amp;id='.$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>'; }
					if ($dataDb->setting_order!=$count_links){
						echo ' <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;down=1&amp;link_order='.$dataDb->setting_order.'&amp;id='.
						$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>'; }
				echo '</td>';
				echo '<td><input type="text" name="'.$dataDb->setting_id.'own_code" value="'.$lijst[0].'" size="5"></td>';
				echo '<td><input type="text" name="'.$dataDb->setting_id.'link_text" value="'.$lijst[1].'" size="20"></td>';
				echo '<td><input type="Submit" name="change_link" value="'.__('Change').'"></td>';
				echo '<td bgcolor="red"><input type="Submit" name="'.$dataDb->setting_id.'remove_link" value="'.__('Remove').'"></td>';
				echo "</tr>";
				$teller++;
			}

			// *** Add new link ***
			echo "<tr>";
				echo "<td><br></td>";
				echo '<input type="hidden" name="link_order" value="'.$new_number.'">';
				echo '<td><input type="text" name="own_code" value="'.__('IP Address').'" size="5"></td>';
				echo '<td><input type="text" name="link_text" value="'.__('Description').'" size="20"></td>';
				echo '<td><input type="Submit" name="add_link" value="'.__('Add').'"></td>';
				echo '<td><br></td>';
			echo "</tr>";
		}
		else{
			echo '<tr><td colspan="4">'.__('Database is not yet available.').'</td></tr>';
		}
		print "</table>";
	print "</form>";

}

echo '</div>';
?>