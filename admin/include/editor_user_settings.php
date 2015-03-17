<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<html><head><title>'.__('Extra settings').'Select place</title></head><body>';

echo '<h1 align=center>'.__('Extra settings').'</h1>';

// *** Update tree settings ***
if (isset($_POST['user_change'])){
	$user_hide_trees=''; $user_edit_trees='';
	$data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
	while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
		// *** Show/ hide trees ***
		$check='show_tree_'.$data3Db->tree_id;
		if (!isset($_POST["$check"])){
			if ($user_hide_trees!=''){ $user_hide_trees.=';'; }
			$user_hide_trees.=$data3Db->tree_id;
		}

		// *** Edit trees (NOT USED FOR ADMINISTRATOR) ***
		$check='edit_tree_'.$data3Db->tree_id;
		if (isset($_POST["$check"])){
			if ($user_edit_trees!=''){ $user_edit_trees.=';'; }
			$user_edit_trees.=$data3Db->tree_id;
		}
	}
	$sql="UPDATE humo_users SET
		user_hide_trees='".$user_hide_trees."', 
		user_edit_trees='".$user_edit_trees."' 
		WHERE user_id=".$_POST["id"];
	$result=$dbh->query($sql);
}

echo '<h2 align="center">'.__('Hide or show family trees per user.').'</h2>';
//echo __('Editor').': '.__('If an .htpasswd file is used: add username in .htpasswd file.').'</td>';

echo __('These are settings PER USER, it\'s also possible to set these setting PER USER GROUP.');

if (isset($_GET['user'])) $user=$_GET['user'];
if (isset($_POST['id'])) $user=$_POST['id'];
$usersql="SELECT * FROM humo_users WHERE user_id='".$user."'";
$user=$dbh->query($usersql);
$userDb=$user->fetch(PDO::FETCH_OBJ);

//$user_hide_trees='';$user_edit_trees='';
$hide_tree_array=explode(";",$userDb->user_hide_trees);
$edit_tree_array=explode(";",$userDb->user_edit_trees);

echo '<form method="POST" action="index.php?page=editor_user_settings">';
echo '<input type="hidden" name="page" value="editor_user_settings">';
echo '<input type="hidden" name="id" value="'.$userDb->user_id.'">';

echo '<table class="humo standard" border="1">';
	echo '<tr style="background-color:green; color:white"><th>'.__('Family tree').'</th><th>'.__('Show tree?').'</th><th>'.__('Edit tree?').' <input type="Submit" name="user_change" value="'.__('Change').'"></th></tr>';

	$data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
	while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
		$treetext=show_tree_text($data3Db->tree_prefix, $selected_language); $treetext_name=$treetext['name'];
		echo '<tr><td>'.$treetext_name.'</td>';

		// *** Show/ hide tree for user ***
		$check=' checked'; if (in_array($data3Db->tree_id, $hide_tree_array)) $check='';
		echo '<td><input type="checkbox" name="show_tree_'.$data3Db->tree_id.'"'.$check.'></td>';

		// *** Editor rights per family tree (NOT USED FOR ADMINISTRATOR) ***
		echo '<td>';
			$check=''; if (in_array($data3Db->tree_id, $edit_tree_array)) $check=' checked';
			$disabled=''; if ($userDb->user_id=='1'){
				$check=' checked'; $disabled=' disabled';
				//echo '<input type="hidden" name="edit_tree_'.$data3Db->tree_id.'" value="1">';
			}
			echo '<input type="checkbox" name="edit_tree_'.$data3Db->tree_id.'"'.$check.$disabled.'>';
		echo '</td>';

		echo '</tr>';
	}
echo '</table>';

echo '</form>';
?>