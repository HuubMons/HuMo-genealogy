<?php
// ********************************************************
// *** Show sources at birth, baptise, marriage, etc.   ***
// ********************************************************

/* *** Source function ***
	function show_sources2
	$connect_kind = person/ family/ address
	$connect_sub_kind = birth/ baptise/ etc.
	$connect_connect_id = id (gedcomnumber or direct table id)
*/
function show_sources2($connect_kind, $connect_sub_kind, $connect_connect_id)
{
	global $dbh, $db_functions, $tree_id, $user, $humo_option, $language, $family_id, $url_path, $uri_path;
	global $main_person, $descendant_report, $pdf_source;
	global $source_footnotes, $screen_mode, $pdf_footnotes, $pdf;
	global $source_footnote_connect_id;
	global $source_combiner;
	global $temp, $templ_person, $templ_relation; // *** PDF export ***
	global $family_expanded;
	$source_array['text'] = '';

	$source_presentation = 'title';
	if (isset($_SESSION['save_source_presentation'])) {
		$source_presentation = $_SESSION['save_source_presentation'];
	}

	// *** Hide sources in mobile version ***
	if ($screen_mode == 'mobile') $source_presentation = 'hide';

	if ($user['group_sources'] != 'n' and $source_presentation != 'hide' and $screen_mode != 'STAR') {
		// *** Search for all connected sources ***
		$connect_sql = $db_functions->get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id);
		$nr_sources = count($connect_sql);
		foreach ($connect_sql as $connectDb) {
			// *** Get shared source, and check for restriction (in source and user group) ***
			$source_status = 'publish';
			if ($connectDb->connect_source_id) {
				$sourceDb = $db_functions->get_source($connectDb->connect_source_id);
				if ($user['group_show_restricted_source'] == 'n' and $sourceDb->source_status == 'restricted') {
					$source_status = 'restricted';
				}
			}

			// *** PDF export. Jan. 2021: all sources are exported (used to be: only shared sources) ***
			//if ($screen_mode=='PDF' AND $connectDb->connect_source_id AND $source_status=='publish'){
			if ($screen_mode == 'PDF' and $source_status == 'publish') {
				// *** Show sources as footnotes ***
				if (!isset($source_footnotes)) {
					$source_footnotes[] = $sourceDb->source_id;
					$pdf_footnotes[] = $pdf->AddLink();
					$pdf_source[safe_text_db($connectDb->connect_source_id)] = safe_text_db($connectDb->connect_source_id);
					//echo 'TEST'.$connectDb->connect_source_id;
				}

				// *** Show text "Source by person/Sources by person" ***
				if ($nr_sources > 1) {
					if ($connect_sub_kind == 'person_source') $templ_person["source_start"] = __('Sources for person') . ': ';
					elseif ($connect_sub_kind == 'family_source') $templ_relation["source_start"] = __('Sources for family') . ': ';
				} else {
					if ($connect_sub_kind == 'person_source') $templ_person["source_start"] = __('Source for person') . ': ';
					elseif ($connect_sub_kind == 'family_source') $templ_relation["source_start"] = __('Source for family') . ': ';
				}

				// *** Check if source is allready listed in the sourcelist ***
				if (!in_array($sourceDb->source_id, $source_footnotes)) {
					// *** Add source in sourcelist ***
					$pdf_source[safe_text_db($connectDb->connect_source_id)] = safe_text_db($connectDb->connect_source_id);
					$pdf_footnotes[] = $pdf->AddLink();
					$source_footnotes[] = $sourceDb->source_id;

					$j = array_key_last($source_footnotes);
					$j++;
				} else {
					// *** Link to existing source ***
					$j = array_search($sourceDb->source_id, $source_footnotes);
					$j++;
				}

				// *** New source (in footnotes) ***
				if ($source_presentation == 'footnote') {
					if ($source_array['text']) $source_array['text'] .= '~'; // delimiter
					$source_array['text'] .= $j;
				} else {
					// *** Texts for all sources, except person_source and family_source ***
					if ($connect_sub_kind != 'person_source' and $connect_sub_kind != 'family_source') {
						if ($nr_sources > 1) {
							if ($connectDb->connect_order == '1') $source_array['text'] .= ', ' . __('sources') . ': ';
						} else {
							$source_array['text'] .= ', ' . __('source') . ': ';
						}
					}

					if ($sourceDb->source_title)
						$source_array['text'] .= trim($sourceDb->source_title);
					// *** Standard source without title ***
					else $source_array['text'] .= $sourceDb->source_text;

					// *** User group option to only show title of source ***
					if ($user['group_sources'] != 't') {
						if ($sourceDb->source_date or $sourceDb->source_place) {
							$source_array['text'] .= " " . date_place($sourceDb->source_date, $sourceDb->source_place);
						}
					}

					// append num of source in list as !!3 for use with fpdf_extend.php to add the right link:
					$source_array['text'] .= "!!" . $j . '~'; // ~ is delimiter for multiple sources
				}
			} // END PDF

			elseif ($source_presentation == 'footnote' and $source_status == 'publish') {
				// *** Combine footnotes with the same source including the same source role and source page... ***
				$combiner_check = $connectDb->connect_source_id . '_' . $connectDb->connect_role . '_' . $connectDb->connect_page . '_' . $connectDb->connect_date . ' ' . $connectDb->connect_place . ' ' . $connectDb->connect_text;

				// *** Jan. 2021: No shared source. Footnotes can be combined! ***
				//if ($sourceDb->source_shared=='')
				//if ($sourceDb->source_title=='')
				if (isset($sourceDb->source_title) and $sourceDb->source_title == '')
					$combiner_check = $connectDb->connect_role . '_' . $connectDb->connect_page . '_' . $connectDb->connect_date . ' ' . $connectDb->connect_place . ' ' . $sourceDb->source_text;

				$check = false;
				// *** Check if the source (including role and page) is already used ***
				if ($source_combiner) {
					for ($j = 0; $j <= (count($source_combiner) - 1); $j++) {
						if ($source_combiner[$j] == $combiner_check) {
							$check = true;
							$j2 = $j + 1;
						}
					}
				}
				// *** Source not found in array, add new source to array ***
				if (!$check) {
					// *** Save new combined source-role-page for check ***
					$source_combiner[] = $combiner_check;
					// *** Save connect_id to show footnotes ***
					$source_footnote_connect_id[] = $connectDb->connect_id;
					// *** Number to show for footnote ***
					$j2 = count($source_footnote_connect_id);
				}
				// *** Test line for footnotes ***
				//$source_array['text'].=' '.$combiner_check.' ';

				// *** Add text "Source for person/ family". Otherwise it isn't clear which source it is ***
				$rtf_text = '';
				if ($connect_sub_kind == 'person_source') {
					if ($nr_sources > 1) {
						if ($connectDb->connect_order == '1') $source_array['text'] .= '. <b>' . __('Sources for person') . '</b> ';
					} else
						$source_array['text'] .= '. <b>' . __('Source for person') . '</b> ';
				} elseif ($connect_sub_kind == 'family_source') {
					if ($nr_sources > 1) {
						if ($connectDb->connect_order == '1') $source_array['text'] .= '. <b>' . __('Sources for family') . '</b> ';
					} else
						$source_array['text'] .= '. <b>' . __('Source for family') . '</b> ';
				} elseif ($screen_mode == 'RTF') $rtf_text = __('sources') . ' ';
				$source_array['text'] .= ' <a href="' . str_replace("&", "&amp;", $_SERVER['REQUEST_URI']) . '#source_ref' . $j2 . '"><sup>' . $rtf_text . $j2 . ')</sup></a>';
			} else {
				// *** Link to shared source ***
				if ($connectDb->connect_source_id and $source_status == 'publish') {

					// *** Always show title of source, show link only after permission check ***

					$source_link = '';
					// *** Only show link if there is a source_title. Source is only shared if there is a source_title ***
					//if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1'){
					if ($user['group_sources'] == 'j' and $sourceDb->source_title != '') {
						//$source_link='<a href="'.$uri_path.'source.php?tree_id='.$tree_id.'&amp;id='.$sourceDb->source_gedcomnr.'">';
						if ($humo_option["url_rewrite"] == "j") {
							// *** $uri_path made in header.php ***
							$url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
						} else {
							$url = $uri_path . 'source.php?tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
						}
						$source_link = '<a href="' . $url . '">';
					}

					if ($connect_sub_kind != 'person_source' and $connect_sub_kind != 'family_source') {
						//$source_array['text'].= ', '.__('source').': '.$source_link;
						$source_array['text'] .= ', ';
						if ($nr_sources > 1) {
							// *** Only show text once ***
							if ($connectDb->connect_order == '1') $source_array['text'] .= __('sources') . ': ';
						} else {
							$source_array['text'] .= __('source') . ': ';
						}
						$source_array['text'] .= $source_link;
					} elseif ($connect_sub_kind == 'person_source') {
						if ($connectDb->connect_order == '1') {
							if ($family_expanded == true) $source_array['text'] .= '<br>';
							else $source_array['text'] .= '. ';
						} else $source_array['text'] .= ', ';

						if ($nr_sources > 1) {
							// *** Only show text once ***
							if ($connectDb->connect_order == '1') $source_array['text'] .= '<b>' . __('Sources for person') . '</b>: ';
						} else {
							$source_array['text'] .= '<b>' . __('Source for person') . '</b>: ';
						}
						$source_array['text'] .= $source_link;
					} elseif ($connect_sub_kind == 'family_source') {
						if ($connectDb->connect_order == '1') {
							if ($family_expanded == true) $source_array['text'] .= '<br>';
							else $source_array['text'] .= '. ';
						} else $source_array['text'] .= ', ';

						//$source_array['text'].= '<b>'.__('Source for family').'</b>'.$source_link;
						if ($nr_sources > 1) {
							// *** Only show text once ***
							if ($connectDb->connect_order == '1') $source_array['text'] .= '<b>' . __('Sources for family') . '</b>: ';
						} else {
							$source_array['text'] .= '<b>' . __('Source for family') . '</b>: ';
						}
						$source_array['text'] .= $source_link;
					}

					// *** Quality (function show_quality can be found in script: family.php) ***
					if ($connectDb->connect_quality == '0' or $connectDb->connect_quality) {
						$quality_text = show_quality($connectDb->connect_quality);
						//$source_array['text'].= ' <i>('.$quality_text.')</i>';
						$source_array['text'] .= ' <i>(' . $quality_text . ')</i>: ';
					}

					//$source_array['text'].= ': ';
					if ($sourceDb->source_title) {
						$source_array['text'] .= ' ' . trim($sourceDb->source_title);
					} elseif ($sourceDb->source_text) {
						$source_array['text'] .= ' ' . process_text($sourceDb->source_text);
					}

					// *** User group option to only show title of source ***
					if ($user['group_sources'] != 't') {
						// *** Show own code if there are no footnotes ***
						if ($sourceDb->source_refn) $source_array['text'] .= ', ' . __('own code') . ': ' . $sourceDb->source_refn;

						if ($sourceDb->source_date or $sourceDb->source_place) {
							$source_array['text'] .= " " . date_place($sourceDb->source_date, $sourceDb->source_place);
						}
					}

					// *** Only show link if there is a shared source ***
					//if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1') $source_array['text'].= '</a>'; // *** End of link ***
					if ($user['group_sources'] == 'j' and $sourceDb->source_title != '') $source_array['text'] .= '</a>'; // *** End of link ***
				} // *** End of shared source ***

				// *** Show (extra) source text ***
				if ($connectDb->connect_text and $source_status == 'publish') {
					$source_array['text'] .= ', ' . __('source text') . ': ' . nl2br($connectDb->connect_text);
				}

				// *** Show picture by source ***
				$result = show_media('connect', $connectDb->connect_id); // *** This function can be found in file: show_picture.php! ***
				$source_array['text'] .= $result[0];
			}
		} // *** Loop multiple source ***

	} // *** End of show sources ***

	//return $source_array;
	if ($source_array['text']) return $source_array;
	else return '';
}


// **************************************************
// *** Show source list if footnotes are selected ***
// **************************************************
function show_sources_footnotes()
{
	global $dbh, $db_functions, $tree_id, $source_footnotes, $language, $user;
	global $uri_path, $source_footnote_connect_id, $humo_option;
	$text = '';

	if ($source_footnote_connect_id && count($source_footnote_connect_id) > 0) {
		$text .= '<h3>' . __('Sources') . "</h3>\n";

		for ($j = 0; $j <= (count($source_footnote_connect_id) - 1); $j++) {
			$connect_qry = "SELECT * FROM humo_connections
				WHERE connect_id='" . $source_footnote_connect_id[$j] . "'";
			$connect_sql = $dbh->query($connect_qry);
			$connectDb = $connect_sql->fetch(PDO::FETCH_OBJ);
			// *** Show shared source data ***
			if ($connectDb->connect_source_id) {
				$sourceDb = $db_functions->get_source($connectDb->connect_source_id);
				// *** Always show title of source, show link only after permission check ***
				$text .= '<a name="source_ref' . ($j + 1) . '"><b>' . ($j + 1) . ')</b></a>';
				//if ($user['group_sources']=='j'){
				//if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1'){
				if ($user['group_sources'] == 'j' and $sourceDb->source_title != '') {

					//$text.=' <a href="'.$uri_path.'source.php?tree_id='.$tree_id.
					//	'&amp;id='.$sourceDb->source_gedcomnr.'">'.__('source').': ';
					if ($humo_option["url_rewrite"] == "j") {
						// *** $uri_path made in header.php ***
						$url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
					} else {
						$url = $uri_path . 'source.php?tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
					}
					$text .= ' <a href="' . $url . '">' . __('source') . ': ';
					if ($sourceDb->source_title) {
						$text .= ' ' . trim($sourceDb->source_title);
					}
					// *** Standard source without title ***
					else $text .= ' ' . $sourceDb->source_text;

					//if ($sourceDb->source_text)
					//	$text.=' '.process_text($sourceDb->source_text);
					$text .= '</a>';
				} else {
					if ($sourceDb->source_title) {
						$text .= ' ' . trim($sourceDb->source_title);
					}
					// *** Standard source without title ***
					//else $text.=' '.$sourceDb->source_text;

					if ($user['group_sources'] != 't' and $sourceDb->source_text)
						$text .= ' ' . process_text($sourceDb->source_text);

					// *** User group option to only show title of source ***
					if ($user['group_sources'] != 't') {
						// *** Show source own code ***
						if ($sourceDb->source_refn) $text .= ', <b>' . __('own code') . '</b>: ' . $sourceDb->source_refn;
					}
				}

				// *** User group option to only show title of source ***
				if ($user['group_sources'] != 't') {
					if ($connectDb->connect_date or $connectDb->connect_place) {
						//if ($connectDb->source_title){ $text.=', '; }
						$text .= " " . date_place($connectDb->connect_date, $connectDb->connect_place);
					}

					// *** Show extra source text ***
					if ($connectDb->connect_text) {
						$text .= ', <b>' . __('extra text') . ':</b> ' . nl2br($connectDb->connect_text);
					}
				}
			}

			// *** Show rest of source items ***

			// *** Source role ***
			if ($connectDb->connect_role) {
				$text .= ', <b>' . __('role') . '</b>: ' . $connectDb->connect_role;
			}

			// *** Source page (connection table) ***
			if ($connectDb->connect_page) {
				$text .= ', <b>' . __('page') . '</b>: ' . $connectDb->connect_page;
			}
			// *** Page by source ***
			if (isset($sourceDb->source_repo_page) and $sourceDb->source_repo_page) {
				$text .= ', <b>' . __('page') . '</b>: ' . $sourceDb->source_repo_page;
			}

			// *** Show picture by source ***
			$result = show_media('connect', $connectDb->connect_id); // *** This function can be found in file: show_picture.php! ***
			$text .= $result[0];

			$text .= "<br>\n";
		} // *** End of loop source footnotes ***
	}
	return $text;
}
