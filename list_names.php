<?php
include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ . '/menu.php';

// DEFAULT ==============================================

$last_name = 'a'; // *** Default first_character ***
$maxcols = 2; // number of name & nr colums in table. For example 3 means 3x name col + nr col
$maxnames = 100;
$nr_persons = $maxnames;
$item = 0;
$start = 0;
$number_high = 0;
$table2_width = (CMS_SPECIFIC == "Joomla") ? "100%" : "90%";

// REQUEST ==============================================

if (isset($_GET['last_name']) and $_GET['last_name'] and is_string($_GET['last_name'])) {
	$last_name = safe_text_db($_GET['last_name']);
}

if (isset($_POST['maxcols'])) {
	$maxcols = $_POST['maxcols'];
}

if (isset($_POST['freqsurnames'])) {
	$maxnames = $_POST['freqsurnames'];
}

if (isset($_GET['item'])) {
	$item = $_GET['item'];
}
if (isset($_GET["start"])) {
	$start = $_GET["start"];
}
//*** Find first first_character of last name ***
$person_qry = "SELECT UPPER(substring(pers_lastname,1,1)) as first_character
		FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY first_character ORDER BY first_character";

// *** Search pers_prefix for names like: "van Mons" ***
if ($user['group_kindindex'] == "j") {
	$person_qry = "SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character
			FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY first_character ORDER BY first_character";
}

?>
<p class="fonts">
<div style="text-align:center">
	<?php @$person_result = $dbh->query($person_qry);
	while (@$personDb = $person_result->fetch(PDO::FETCH_OBJ)) {
		if (CMS_SPECIFIC == 'Joomla') {
			$path_tmp = 'index.php?option=com_humo-gen&amp;task=list_names&amp;tree_id=' . $tree_id . '&amp;last_name=' . $personDb->first_character;
		} elseif ($humo_option["url_rewrite"] == "j") {
			$path_tmp = $uri_path . 'list_names/' . $tree_id . '/' . $personDb->first_character . '/';
		} else {
			$path_tmp = CMS_ROOTPATH . 'list_names.php?tree_id=' . $tree_id . '&amp;last_name=' . $personDb->first_character;
		} ?>

		<a href="<?= $path_tmp; ?>"><?= $personDb->first_character; ?></a>
	<?php }

	if (CMS_SPECIFIC == 'Joomla') {
		$path_tmp = 'index.php?option=com_humo-gen&amp;task=list_names&amp;last_name=all';
	} else {
		$path_tmp = CMS_ROOTPATH . "list_names.php?last_name=all";
	} ?>
	
	<a href="<?= $path_tmp; ?>"><?= __('All names'); ?></a>
</div><br>

<?php

function tablerow($nr, $lastcol = false)
{
	// displays one set of name & nr column items in the row
	// $nr is the array number of the name set created in function last_names
	// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
	global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $tree_id;
	//if (CMS_SPECIFIC=='Joomla'){
	//	$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;tree_id='.$tree_id;
	//}
	//else{
	$path_tmp = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id;
	//}
?>
	<td class="namelst">
		<?php if (isset($freq_last_names[$nr])) {
			$top_pers_lastname = '';
			if ($freq_pers_prefix[$nr]) {
				$top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
			}

			$top_pers_lastname .= $freq_last_names[$nr];

			if ($user['group_kindindex'] == "j") {
				$path_tmp .= '&amp;pers_lastname=' . str_replace("_", " ", $freq_pers_prefix[$nr]) . str_replace('&', '|', $freq_last_names[$nr]) . '&amp;part_lastname=equals';
			} else {
				$path_tmp .= '&amp;pers_lastname=' . str_replace("&", "|", $freq_last_names[$nr]);

				if ($freq_pers_prefix[$nr]) {
					$path_tmp .= '&amp;pers_prefix=' . $freq_pers_prefix[$nr];
					$top_pers_lastname = ', ' . str_replace("_", " ", $freq_pers_prefix[$nr]);
				} else {
					$path_tmp .= '&amp;pers_prefix=EMPTY';
				}
				$path_tmp .= '&amp;part_lastname=equals';
		?>
			<?php } ?>

			<a href="<?= $path_tmp; ?>"><?= $top_pers_lastname; ?></a>

		<?php } else { ?>
			-
		<?php } ?>
	</td>

	<?php if ($lastcol == false) { ?>
		<td class="namenr" style="text-align:center;border-right-width:3px">
		<?php } else { ?>
		</td>
		<td class="namenr" style="text-align:center">
		<?php } ?>
		<?= isset($freq_last_names[$nr]) ? $freq_count_last_names[$nr] : '-'; ?>
		</td>
	<?php }

// Mons, van or: van Mons
if ($user['group_kindindex'] == "j") {
	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
		FROM humo_persons
		WHERE pers_tree_id='" . $tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $last_name . "%'
		GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$count_qry = "SELECT pers_lastname, pers_prefix
		FROM humo_persons
		WHERE pers_tree_id='" . $tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $last_name . "%'
		GROUP BY pers_prefix, pers_lastname";


	if ($last_name == 'all') {
		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
			FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
			GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$count_qry = "SELECT pers_prefix, pers_lastname
			FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
			GROUP BY pers_prefix, pers_lastname";
	}
} else {
	// *** Select alphabet first_character ***
	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
		FROM humo_persons
		WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname LIKE '" . $last_name . "%'
		GROUP BY pers_lastname, pers_prefix";

	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$count_qry = "SELECT pers_lastname, pers_prefix
		FROM humo_persons
		WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname LIKE '" . $last_name . "%'
		GROUP BY pers_lastname, pers_prefix";

	if ($last_name == 'all') {
		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
			FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
			GROUP BY pers_lastname, pers_prefix";

		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$count_qry = "SELECT pers_lastname, pers_prefix
			FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
			GROUP BY pers_lastname, pers_prefix";
	}
}

// *** Add limit to query (results per page) ***
if ($maxnames != 'ALL') $personqry .= " LIMIT " . $item . "," . $maxnames;

$person = $dbh->query($personqry);
while (@$personDb = $person->fetch(PDO::FETCH_OBJ)) {
	if ($personDb->pers_lastname == '') $personDb->pers_lastname = '...';
	$freq_last_names[] = $personDb->pers_lastname;
	$freq_pers_prefix[] = $personDb->pers_prefix;
	$freq_count_last_names[] = $personDb->count_last_names;
	if ($personDb->count_last_names > $number_high) {
		$number_high = $personDb->count_last_names;
	}
}
if (isset($freq_last_names)) $row = ceil(count($freq_last_names) / $maxcols);

// *** Total number of persons for multiple pages ***

$result = $dbh->query($count_qry);
$count_persons = $result->rowCount();
if ($nr_persons == 'ALL') $nr_persons = $count_persons;

// *** Show options line ***
echo '<div style="text-align:center">';

if ($humo_option["url_rewrite"] == "j") {
	// *** $uri_path made in header.php ***
	$url = $uri_path . 'list_names/' . $tree_id . '/' . $last_name;
} else {
	//$url=CMS_ROOTPATH.'list_names.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'&amp;last_name='.$last_name;
	$url = CMS_ROOTPATH . 'list_names.php?tree_id=' . $tree_id . '&amp;last_name=' . $last_name;
}
	?>

	<form method="POST" action="<?= $url; ?>" style="display:inline;" id="frqnames">
		<?= __('Number of displayed surnames'); ?>
		: <select size=1 name="freqsurnames" onChange="this.form.submit();" style="width: 50px; height:20px;">

			<option value="25" <?= ($maxnames == 25) ? " selected" : ''; ?>>25</option>
			<option value="51" <?= ($maxnames == 51) ? " selected" : ''; ?>>50</option>
			<option value="75" <?= ($maxnames == 75) ? " selected" : ''; ?>>75</option>
			<option value="100" <?= ($maxnames == 100) ? " selected" : ''; ?>>100</option>
			<option value="201" <?= ($maxnames == 201) ? " selected" : ''; ?>>200</option>
			<option value="300" <?= ($maxnames == 300) ? " selected" : ''; ?>>300</option>
			<option value="ALL" <?= ($maxnames == 'ALL') ? " selected" : ''; ?>><?= __('All'); ?></option>
		</select>

		<?= __('Number of columns'); ?>
		: <select size=1 name="maxcols" onChange="this.form.submit();" style="width: 50px; height:20px;">
			<?php for ($i = 1; $i < 7; $i++) { ?>
				<option value="<?= $i; ?>" <?= ($maxcols == $i) ? " selected" : ''; ?>><?= $i; ?></option>
			<?php } ?>
		</select>
	</form>

	<?php
	//*** Show number of persons and pages *********************

	// *** Check for search results ***
	if (@$person->rowCount() == 0) {
		$line_pages = '';
		//echo '<br><div class="center">'.__('No names found.').'</div>';
	} else {
		$show_line_pages = false;
		$line_pages = __('Page');

		if ($humo_option["url_rewrite"] == "j") {
			// *** $uri_path made in header.php ***
			$uri_path_string = $uri_path . 'list_names/' . $tree_id . '/' . $last_name . '?';
		} else {
			$uri_path_string = 'list_names.php?last_name=' . $last_name . '&amp;';
		}

		// "<="
		if ($start > 1) {
			$show_line_pages = true;
			$start2 = $start - 20;
			$calculated = ($start - 2) * $nr_persons;
			$line_pages .= ' <a href="' . $uri_path_string .
				"start=" . $start2 .
				"&amp;item=" . $calculated .
				'">&lt;= </a>';
		}
		if ($start <= 0) {
			$start = 1;
		}

		// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
		for ($i = $start; $i <= $start + 19; $i++) {
			$calculated = ($i - 1) * $nr_persons;
			if ($calculated < $count_persons) {
				if ($item == $calculated) {
					$line_pages .=  ' <b>' . $i . '</b>';
				} else {
					$show_line_pages = true;
					$line_pages .= ' <a href="' . $uri_path_string .
						"start=" . $start .
						"&amp;item=" . $calculated .
						'"> ' . $i . '</a>';
				}
			}
		}

		// "=>"
		$calculated = ($i - 1) * $nr_persons;
		if ($calculated < $count_persons) {
			$show_line_pages = true;
			$line_pages .= ' <a href="' . $uri_path_string .
				"start=" . $i .
				"&amp;item=" . $calculated .
				'"> =&gt;</a>';
		}
	}
	//if (isset($show_line_pages) AND $show_line_pages) echo '<br>';
	//if (isset($line_pages)) echo $line_pages;
	if (isset($show_line_pages) and $show_line_pages and isset($line_pages)) { ?>
		<br><?= $line_pages; ?>
	<?php } ?>

	</div>

	<br>
	<table width="<?= $table2_width; ?>" class="humo nametbl" align="center">
		<tr class="table_headline">
			<?php $col_width = ((round(100 / $maxcols)) - 6) . "%";
			for ($x = 1; $x < $maxcols; $x++) { ?>
				<th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
				<th style="text-align:center;font-size:90%;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
			<?php } ?>
			<th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
			<th style="text-align:center;font-size:90%;width:6%"><?= __('Total'); ?></th>
		</tr>
		<?php for ($i = 0; $i < $row; $i++) { ?>
			<tr>
				<?php for ($n = 0; $n < $maxcols; $n++) {
					if ($n == $maxcols - 1) {
						tablerow($i + ($row * $n), true); // last col
					} else {
						tablerow($i + ($row * $n)); // other cols
					}
				} ?>
			</tr>
		<?php } ?>
	</table>


	<script>
		// *** Show number of names with gray background bar ***
		// TODO: Devs: innerHTML is not safe!
		// TODO: Devs: $number_high can be null and cause warning in console js!
		var tbl = document.getElementsByClassName("nametbl")[0];
		var rws = tbl.rows;
		var baseperc = '<?= $number_high; ?>';
		for (var i = 0; i < rws.length; i++) {
			var tbs = rws[i].getElementsByClassName("namenr");
			var nms = rws[i].getElementsByClassName("namelst");
			for (var x = 0; x < tbs.length; x++) {
				var percentage = parseInt(tbs[x].innerHTML, 10);
				percentage = (percentage * 100) / baseperc;
				if (percentage > 0.1) {
					nms[x].style.backgroundImage = "url(styles/images/lightgray.png)";
					nms[x].style.backgroundSize = percentage + "%" + " 100%";
					nms[x].style.backgroundRepeat = "no-repeat";
					nms[x].style.color = "rgb(0, 140, 200)";
				}
			}
		}
	</script>
	<br>

	<?php include_once __DIR__ . '/footer.php';
