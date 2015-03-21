<?php
@set_time_limit(3000);
@ini_set('memory_limit','-1');
error_reporting(E_ALL);
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

if (isset($_POST['tree'])){ $tree=safe_text($_POST["tree"]); }

echo '<h1 align=center>'.__('User notes').'</h1>';

echo 'User notes page is under construction and will be completed in new versions...<br>';

echo '<table class="humo standard" style="width:800px;" border="1">';

echo '<tr class="table_header"><th colspan="2">'.__('User notes').'</th></tr>';

	echo '<tr><td>'.__('Choose family').'</td>';
	echo '<td>';
		$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_result = $dbh->query($tree_sql);
		$onchange='';
		if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part') {
			// we have to refresh so that the persons to choose from will belong to this tree!
			echo '<input type="hidden" name="flag_newtree" value=\'0\'>';
			$onchange = ' onChange="this.form.flag_newtree.value=\'1\';this.form.submit();" ';
		}
		echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
		echo '<input type="hidden" name="page" value="user_notes">';
		echo '<select '.$onchange.' size="1" name="tree">';
			while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
				$treetext=show_tree_text($treeDb->tree_prefix, $selected_language);
				$selected='';
				if (isset($tree)){
					if ($treeDb->tree_prefix==$tree){
						$selected=' SELECTED';
						// *** Needed for submitter ***
						//$tree_owner=$treeDb->tree_owner;
						//$tree_id=$treeDb->tree_id;
						$tree_prefix=$treeDb->tree_prefix;
						$db_functions->set_tree_id($tree_id);
					}
				}

				$note_qry= "SELECT * FROM humo_user_notes WHERE note_tree_prefix='".$treeDb->tree_prefix."'";
				$note_result = $dbh->query($note_qry);
				$num_rows = $note_result->rowCount();

				echo '<option value="'.$treeDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].' ['.$num_rows.']</option>';
			}
		echo '</select>';

		echo ' <input type="Submit" name="submit_button" value="'.__('Select').'">';
		echo '</form>';

	echo '</td></tr>';


	// *** Show user added notes ***
	if (isset($tree_prefix)){
		$note_qry= "SELECT * FROM humo_user_notes WHERE note_tree_prefix='".$tree_prefix."'";
		$note_result = $dbh->query($note_qry);
		$num_rows = $note_result->rowCount();

		echo '<tr class="humo_user_notes"><td>';
			if ($num_rows)
				echo '<a href="#humo_user_notes"></a> ';
			echo __('User notes').'</td><td colspan="2">';
			if ($num_rows)
				printf(__('There are %d user added notes.'), $num_rows);
			else
				printf(__('There are %d user added notes.'), 0);
		echo '</td></tr>';

		while($noteDb=$note_result->fetch(PDO::FETCH_OBJ)){
			$user_qry = "SELECT * FROM humo_users
				WHERE user_id='".$noteDb->note_user_id."'";
			$user_result = $dbh->query($user_qry);
			$userDb=$user_result->fetch(PDO::FETCH_OBJ);

			echo '<tr class="humo_color"><td>';
				// *** Select status of message ***
				echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
				echo '<input type="hidden" name="page" value="user_notes">';
				echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
				echo '<select '.$onchange.' size="1" name="tree" disabled>';
					$selected='';
					echo '<option value="new"'.$selected.'>'.__('New').'</option>';
					echo '<option value="new"'.$selected.'>'.__('Approved').'</option>';
				echo '</select>';

				echo ' <input type="Submit" name="submit_button" value="'.__('Select').'">';
				echo '</form>';

				echo '</td><td>';
				echo '<b>'.$noteDb->note_date.' '.$noteDb->note_time.' '.$userDb->user_name.'</b><br>';
				echo '<b>'.$noteDb->note_names.'</b><br>';
				echo nl2br($noteDb->note_note);
				echo '</td>';
			echo '</tr>';
		}
	}

echo '</table>';
?>