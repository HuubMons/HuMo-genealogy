<?php
// TODO: @Devs add a button link for PDF to /pdf_source

/**
 * source.php is called from show_sources.php, sources.php
 */
if (isset($_GET["id"])) {
	source_display($_GET["id"]);
}

/**
 * Show a single source.
 */
function source_display(int $sourcenum)
{
	global $dbh, $db_functions, $tree_id, $dataDb, $user, $pdf, $screen_mode, $language, $humo_option;
	
	// *** Check user authority ***
	if ($user['group_sources'] != 'j') {
		echo __('You are not authorised to see this page.');
		exit();
	}

	$sourceDb = $db_functions->get_source($sourcenum);

	// *** Check if visitor tries to see restricted sources ***
	if ($user['group_show_restricted_source'] == 'n' && $sourceDb->source_status == 'restricted') {
		exit(__('No valid source number.'));
	}

	// *** If an unknown source ID is choosen, exit function ***
	if (!isset($sourceDb->source_id)) {
		exit(__('No valid source number.'));
	}

	// *** Convert all url's in a text to clickable links ***  ____old code
	/* $source_publ = $sourceDb->source_publ;
	$source_publ = preg_replace("#(^|[ \n\r\t])www.([a-z\-0-9]+).([a-z]{2,4})($|[ \n\r\t])#mi", 
						"\\1<a href=\"http://www.\\2.\\3\" target=\"_blank\">www.\\2.\\3</a>\\4", 
						$source_publ); 
	$source_publ = preg_replace("#(^|[ \n\r\t])(((http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", 
						"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", 
						$source_publ); */
	
	$repository = $db_functions->get_repository($sourceDb->source_repo_gedcomnr);

	include_once __DIR__ . '/header.php';
	include_once __DIR__ . '/menu.php';
	include_once __DIR__ . '/include/date_place.php';
	include_once __DIR__ . '/include/process_text.php';
	include_once __DIR__ . '/include/show_picture.php';// Needed for pictures by a source
	include_once __DIR__ . '/include/show_sources.php';
	include_once __DIR__ . '/include/language_date.php';
	include_once __DIR__ . '/include/person_cls.php';
	
	?>
	<table class="humo standard">
		<tr>
			<td>
				<h2><?= __('Sources'); ?></h2>
				<b><?= __('Title'); ?>:</b> <?= $sourceDb->source_title; ?><br>
				<b><?= __('Date'); ?>:</b> <?= language_date(strtolower($sourceDb->source_date)); ?><br>
				<b><?= __('Publication'); ?>:</b> <a href="<?= $sourceDb->$source_publ; ?>" target="_blank"></a><br>
				<b><?= __('Place'); ?>:</b> <?= $sourceDb->source_place; ?><br>
				<b><?= __('Own code'); ?>:</b> <?= $sourceDb->source_refn; ?><br>
				<b><?= __('Author'); ?>:</b> <?= $sourceDb->source_auth; ?><br>
				<b><?= __('Subject'); ?>:</b> <?= $sourceDb->source_subj; ?><br>
				<b><?= __('Nr.'); ?>:</b> <?= $sourceDb->source_item; ?><br>
				<b><?= __('Kind'); ?>:</b> <?= $sourceDb->source_kind; ?><br>
				<b><?= __('Archive'); ?>:</b> <?= $sourceDb->source_repo_caln; ?><br>
				<b><?= __('Page'); ?>:</b> <?= $sourceDb->source_repo_page; ?><br>
			</td>
		</tr>
		<tr>
			<td>
				<?= process_text($sourceDb->source_text); ?>
				<?php
				// *** Pictures by source ***
				$result = show_media('source', $sourceDb->source_gedcomnr); // *** This function can be found in file: show_picture.php! ***
				echo $result[0];
				?>
			</td>
		</tr>
		<tr>
			<td>
				<h3><?= __('Repository'); ?></h3>
				<b><?= __('Title'); ?>:</b> <?= $repository->repo_name; ?><br>
				<b><?= __('Zip code'); ?>:</b> <?= $repository->repo_zip; ?><br>
				<b><?= __('Address'); ?>:</b> <?= $repository->repo_address; ?><br>
				<b><?= __('Date'); ?>:</b> <?= $repository->repo_date; ?><br>
				<b><?= __('Place'); ?>:</b> <?= $repository->repo_place; ?><br>
				<?= nl2br($repository->repo_text); ?>
			</td>
		</tr>
		<tr><td>
		<?php
		$person_cls = new person_cls;

		// *** Find person data if source is connected to a family item ***
		// *** This seperate function speeds up the sources page ***
		function person_data($familyDb)
		{
			global $dbh, $db_functions;
			if ($familyDb->fam_man)
				$personDb = $db_functions->get_person($familyDb->fam_man);
			else
				$personDb = $db_functions->get_person($familyDb->fam_woman);
			return $personDb;
		}


		// *** Sources in connect table ***
		$connect_qry = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
			AND connect_source_id='" . $sourceDb->source_gedcomnr . "'
			ORDER BY connect_kind, connect_sub_kind, connect_order";
		$connect_sql = $dbh->query($connect_qry);
		while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
			// *** Person source ***
			if ($connectDb->connect_kind == 'person') {
				if ($connectDb->connect_sub_kind == 'person_source') {
					echo __('Source for:');
				}
				if ($connectDb->connect_sub_kind == 'pers_name_source') {
					echo __('Source for name:');
				}
				if ($connectDb->connect_sub_kind == 'pers_birth_source') {
					echo __('Source for birth:');
				}
				if ($connectDb->connect_sub_kind == 'pers_bapt_source') {
					echo __('Source for baptism:');
				}
				if ($connectDb->connect_sub_kind == 'pers_death_source') {
					echo __('Source for death:');
				}
				if ($connectDb->connect_sub_kind == 'pers_buried_source') {
					echo __('Source for burial:');
				}
				if ($connectDb->connect_sub_kind == 'pers_text_source') {
					echo __('Source for text:');
				}
				if ($connectDb->connect_sub_kind == 'pers_sexe_source') {
					echo __('Source for sex:');
				}

				if ($connectDb->connect_sub_kind == 'pers_event_source') {
					// *** Sources by event ***
					$event_Db = $db_functions->get_event($connectDb->connect_connect_id);
					// *** Person source ***
					if (isset($event_Db->event_connect_kind) and $event_Db->event_connect_kind == 'person' and $event_Db->event_connect_id) {
						$personDb = $db_functions->get_person($event_Db->event_connect_id);
						$name = $person_cls->person_name($personDb);

						echo __('Source for:');

						// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
						$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
						$name = $person_cls->person_name($personDb);
						echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';

						if ($event_Db->event_event) {
							echo ' [' . $event_Db->event_event . ']';
						}
					}
				}
				// *** Show person-address connection ***
				elseif ($connectDb->connect_sub_kind == 'pers_address_connect_source') {
					// *** connect_sub_kind=pers_address_source/connect_connect_id=Rxx/connect_source_id=Sxx.
					// *** connect_sub_kind=person_address/connect_connect_id=Ixx/connect_item_id=Rxx
					$address_qry = "SELECT * FROM humo_connections
						WHERE connect_id='" . $connectDb->connect_connect_id . "'";
					$address_sql = $dbh->query($address_qry);
					$addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
					// Show person that has connected address.
					$personDb = $db_functions->get_person($addressDb->connect_connect_id);
					$name = $person_cls->person_name($personDb);
					echo __('Source by address (person):');

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
					$name = $person_cls->person_name($personDb);
					echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
				} else {
					$personDb = $db_functions->get_person($connectDb->connect_connect_id);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
					$name = $person_cls->person_name($personDb);
					echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
				}
			}

			// *** Family source ***
			if ($connectDb->connect_kind == 'family') {
				if ($connectDb->connect_sub_kind == 'family_source') {
					echo __('Source for family:');
				}
				if ($connectDb->connect_sub_kind == 'fam_relation_source') {
					echo __('Source for cohabitation:');
				}
				if ($connectDb->connect_sub_kind == 'fam_marr_notice_source') {
					echo __('Source for marriage notice:');
				}
				if ($connectDb->connect_sub_kind == 'fam_marr_source') {
					echo __('Source for marriage:');
				}
				if ($connectDb->connect_sub_kind == 'fam_marr_church_notice_source') {
					echo __('Source for marriage notice (church):');
				}
				if ($connectDb->connect_sub_kind == 'fam_marr_church_source') {
					echo __('Source for marriage (church):');
				}
				if ($connectDb->connect_sub_kind == 'fam_div_source') {
					echo __('Source for divorce:');
				}
				if ($connectDb->connect_sub_kind == 'fam_text_source') {
					echo __('Source for family text:');
				}
				//else{
				//	echo 'TEST2';
				//}

				//if ($connectDb->connect_sub_kind=='event'){
				if ($connectDb->connect_sub_kind == 'fam_event_source') {
					// *** Sources by event ***
					$event_Db = $db_functions->get_event($connectDb->connect_connect_id);
					// *** Family source ***
					if (isset($event_Db->event_connect_kind) and $event_Db->event_connect_kind == 'family' and $event_Db->event_connect_id) {
						echo __('Source for family:');
						$familyDb = $db_functions->get_family($event_Db->event_connect_id);
						$personDb = person_data($familyDb);

						// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
						$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
						$name = $person_cls->person_name($personDb);
						echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';

						if ($event_Db->event_event) {
							echo ' [' . $event_Db->event_event . ']';
						}
					}
				}
				// *** Show person-address connection ***
				elseif ($connectDb->connect_sub_kind == 'fam_address_connect_source') {
					// *** connect_sub_kind=fam_address_source/connect_connect_id=Rxx/connect_source_id=Sxx.
					// *** connect_sub_kind=family_address/connect_connect_id=Fxx/connect_item_id=Rxx
					$address_qry = "SELECT * FROM humo_connections
						WHERE connect_id='" . $connectDb->connect_connect_id . "'";
					$address_sql = $dbh->query($address_qry);
					$addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
					// Show family that has connected address.
					echo __('Source by adres (family):');
					$familyDb = $db_functions->get_family($addressDb->connect_connect_id);
					$personDb = person_data($familyDb);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
					$name = $person_cls->person_name($personDb);
					echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
				} else {
					$familyDb = $db_functions->get_family($connectDb->connect_connect_id);
					$personDb = person_data($familyDb);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
					$name = $person_cls->person_name($personDb);
					echo ' <a href="' . $url . '">' . $name["standard_name"] . '</a>';
				}
			}

			// *** Source by (shared) address ***
			if ($connectDb->connect_kind == 'address' and $connectDb->connect_sub_kind == 'address_source') {
				$sql = "SELECT * FROM humo_addresses
					WHERE address_tree_id='" . $connectDb->connect_tree_id . "' AND address_gedcomnr='" . $connectDb->connect_connect_id . "'";
				$address_sql = $dbh->query($sql);
				$addressDb = $address_sql->fetch(PDO::FETCH_OBJ);
				$text = '';
				if ($addressDb->address_address) $text .= $addressDb->address_address;
				if ($addressDb->address_place) $text .= ' ' . $addressDb->address_place;

				echo __('Source for address:');
				echo ' <a href="address.php?gedcomnumber=' . $addressDb->address_gedcomnr . '">' . $text . '</a>';
			}

			// *** Extra source connect information by every source ***
			if ($connectDb->connect_date or $connectDb->connect_place) {
				echo " " . date_place($connectDb->connect_date, $connectDb->connect_place);
			}
			// *** Source role ***
			if ($connectDb->connect_role) {
				echo ', <b>' . __('role') . '</b>: ' . $connectDb->connect_role;
			}
			// *** Source page ***
			if ($connectDb->connect_page) {
				echo ', <b>' . __('page') . '</b>: ' . $connectDb->connect_page;
			}
			echo '<br>';
		} ?>
			</td>
		</tr>
	</table>

	<?php include_once __DIR__ . '/footer.php';
	
}
