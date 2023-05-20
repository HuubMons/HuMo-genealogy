<?php
include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ . '/menu.php';
include_once __DIR__ . '/include/person_cls.php';

// *** Extra safety line ***
if (!is_numeric($tree_id)) exit; // TODO: @devs: NEVER die or exit!!! Need a redirection or throw error.

$search_name = isset($_POST["search_name"]) ?  $_POST["search_name"] : '';

/**
 * Get list of last persons data change (model responsability)
 */
function getLastestChanges(int $tree_id, string $search_name = '')
{
	global $dbh;
	// *** EXAMPLE of a UNION querie ***
	//$qry = "(SELECT * FROM humo1_person ".$query.') ';
	//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
	//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
	//$qry.= " ORDER BY pers_lastname, pers_firstname";

	$person_qry = "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
	FROM humo_persons
	WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='')";
	$person_qry .= " UNION (SELECT *, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
	FROM humo_persons
	WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL)";
	$person_qry .= " ORDER BY changed_date DESC, changed_time DESC LIMIT 0,100";

	if (!null == $search_name) 
	{
		// *** Renewed querie because of ONLY_FULL_GROUP_BY in MySQL 5.7 ***
		$person_qry = "
		SELECT humo_persons2.*, humo_persons1.pers_id
		FROM humo_persons as humo_persons2
		RIGHT JOIN 
		(
			(
			SELECT pers_id
			FROM humo_persons
			LEFT JOIN humo_events
				ON pers_gedcomnumber=event_connect_id AND pers_tree_id=event_tree_id AND event_kind='name'
			WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($search_name) . "%'
				OR event_event LIKE '%" . safe_text_db($search_name) . "%')
				AND ((pers_changed_date IS NOT NULL AND pers_changed_date!='') OR (pers_new_date IS NOT NULL AND pers_new_date!=''))
				AND pers_tree_id='" . $tree_id . "'
			GROUP BY pers_id
			)
		) as humo_persons1
		ON humo_persons1.pers_id = humo_persons2.pers_id
		";

		// *** Order by pers_changed_date or pers_new_date, also order by pers_changed_time or pers_new_time ***
		$person_qry .= " ORDER BY
		IF (humo_persons2.pers_changed_date IS NOT NULL AND humo_persons2.pers_changed_date!='',
			STR_TO_DATE(humo_persons2.pers_changed_date,'%d %b %Y'),
			STR_TO_DATE(humo_persons2.pers_new_date,'%d %b %Y')
			) DESC,
		IF (humo_persons2.pers_changed_date IS NOT NULL AND humo_persons2.pers_changed_date!='',
			humo_persons2.pers_changed_time, humo_persons2.pers_new_time
			) DESC LIMIT 0,100";
	}

	$latestChanges = $dbh->query($person_qry)->fetchAll(PDO::FETCH_OBJ);
	return $latestChanges;
}

/**
 * Transform persons data to view data (dto responsability)
 */
function sexeDataToIcone(string $sexe)
{
	if ($sexe == "M") {
		return '<img src="/theme/images/man.gif" alt="man">';
	} elseif ($sexe == "F") {
		return '<img src="/theme/images/woman.gif" alt="woman">';
	} else {
		return '<img src="/theme/images/unknown.gif" alt="unknown">';
	}
}

$person_cls = new person_cls;

$latestChanges = getLastestChanges($tree_id, $search_name);

?>


<h2 class="center"><?= __('Recently changed persons and new persons'); ?></h2>
<div style="text-align: center; margin-bottom: 16px">
	<form action="/latest_changes.php'; ?>" method="post">
		<input type="text" name="search_name" id="part_of_name" value="<?= safe_text_show($search_name); ?>">
		<input type="submit" value="<?= __('Search'); ?>">
	</form>
</div>
<table class="humo small">
	<tr class=table_headline>
		<th style="font-size: 90%; text-align: left"><?= __('Changed/ Added'); ?></th>
		<th style="font-size: 90%; text-align: left"><?= __('When changed'); ?></th>
		<th style="font-size: 90%; text-align: left"><?= __('When added'); ?></th>
	</tr>

	<?php foreach ($latestChanges as $person) { ?>
		<tr>
			<td style="font-size: 90%">
				<?php $person_cls->construct($person);
				echo $person_cls->person_popup_menu($person);
				echo sexeDataToIcone($person->pers_sexe);
				// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
				$url = $person_cls->person_url2($person->pers_tree_id, $person->pers_famc, $person->pers_fams, $person->pers_gedcomnumber);
				?>
				<a href="<?= $url; ?>">

					<?php $name = $person_cls->person_name($person);
					echo $name["standard_name"]; ?>
				</a>
			</td>
			<td style="font-size: 90%">
				<span style="white-space: nowrap"><?= strtolower($person->pers_changed_date) . ' - ' . $person->pers_changed_time; ?></span>
			</td>
			<td style="font-size: 90%">
				<span style="white-space: nowrap"><?= strtolower($person->pers_new_date) . ' - ' . $person->pers_new_time; ?></span>
			</td>
		</tr>
	<?php } ?>
</table>

<?php include_once __DIR__ . '/footer.php';
