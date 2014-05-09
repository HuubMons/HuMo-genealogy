<?php
// ********************************************************
// *** Show sources at birth, baptise, marriage, etc.   ***
// ********************************************************
//error_reporting(E_ALL);

// *** Source function ***
// function show_sources2
// $connect_kind = person/ family/ address
// $connect_sub_kind = birth/ baptise/ etc.
// $connect_connect_id = id (gedcomnumber or direct table id)
function show_sources2($connect_kind,$connect_sub_kind,$connect_connect_id){
	global $user, $humo_option, $language, $db, $dbh, $tree_prefix_quoted, $family_id, $url_path, $uri_path;
	global $main_person, $descendant_report, $pdf_source;
	global $source_footnotes, $screen_mode, $pdf_footnotes, $pdf;
	global $source_footnote_connect_id;
	global $source_combiner;
	$text='';

	$source_presentation='title';
	if (isset($_SESSION['save_source_presentation'])){
		$source_presentation=$_SESSION['save_source_presentation'];
	}

	// *** Hide sources in mobile version ***
	if ($screen_mode=='mobile') $source_presentation='hide';
	
	//if ($user['group_sources']!='n' AND $source_presentation!='hide'){
	if ($user['group_sources']!='n' AND $source_presentation!='hide' AND $screen_mode!='STAR'){

		// *** Search for all connected sources ***
		$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
			WHERE connect_kind='".$connect_kind."'
			AND connect_sub_kind='".$connect_sub_kind."'
			AND connect_connect_id='".$connect_connect_id."'
			ORDER BY connect_order";
		$connect_sql=$dbh->query($connect_qry);

		while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
			// *** Get extended source, and check for restriction (in source and user group) ***
			$source_status='publish';
			if ($connectDb->connect_source_id){
				$source_sql=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."sources
					WHERE source_gedcomnr='".safe_text($connectDb->connect_source_id)."'");
				$sourceDb=$source_sql->fetch(PDO::FETCH_OBJ);
				if ($user['group_show_restricted_source']=='n' AND $sourceDb->source_status=='restricted'){
					$source_status='restricted';
				}
			}

			// *** PDF export, for now only EXTENDED sources are exported! ***
			if ($screen_mode=='PDF' AND $connectDb->connect_source_id AND $source_status=='publish'){

				// *** Show sources as footnotes ***
				if (!isset($source_footnotes)){
					$source_footnotes[]=$sourceDb->source_id;
					$pdf_footnotes[] = $pdf->AddLink();
					$pdf_source[safe_text($connectDb->connect_source_id)]=safe_text($connectDb->connect_source_id);
				}

				$source_check=false;
				for ($j=0; $j<=(count($source_footnotes)-1); $j++){
					if ($sourceDb->source_id==$source_footnotes[$j]){
						if($source_presentation=='footnote') {
							$text.=' ('.strtolower(__('Sources')).' '.($j+1).')';
							$text.='~'; // delimiter
						}
						else {
							$text.=', '.strtolower(__('Source')).': ';
							if ($sourceDb->source_title){ $text.= " ".trim($sourceDb->source_title); }
							if ($sourceDb->source_date or $sourceDb->source_place) {
								$text.=" ".date_place($sourceDb->source_date, $sourceDb->source_place); }
							// append num of source in list for use with fpdf_extend.php to add the right link:
							$text .= "!!".($j+1).'~'; // ~ is delimiter for multiple sources
						}
						$source_check=true;
					}
				}
				if ($source_check!=true){
					if($source_presentation=='footnote') {
						$text.=' ('.strtolower(__('Sources')).' '.($j+1).')';
						$text.='~'; // delimiter
					}
					else {
						$text.=', '.strtolower(__('Source')).': ';
						if ($sourceDb->source_title){ $text.= " ".trim($sourceDb->source_title); }
						if ($sourceDb->source_date or $sourceDb->source_place) { $text.=" ".date_place($sourceDb->source_date, $sourceDb->source_place); }
						// append num of source in list as !!3 for use with fpdf_extend.php to add the right link:
						$text .= "!!".($j+1).'~'; // ~ is delimiter for multiple sources
					}
					$pdf_source[safe_text($connectDb->connect_source_id)]=safe_text($connectDb->connect_source_id);
					$pdf_footnotes[] = $pdf->AddLink();
					$source_footnotes[]=$sourceDb->source_id;
				}
			}
			elseif ($source_presentation=='footnote' AND $source_status=='publish'){
				// *** Combine footnotes with the same source including the same source role and source page... ***	//$combiner_check=$connectDb->connect_source_id.'_'.$connectDb->connect_role.'_'.$connectDb->connect_page;
				$combiner_check=$connectDb->connect_source_id.'_'.$connectDb->connect_role.'_'.$connectDb->connect_page.'_'.$connectDb->connect_date.' '.$connectDb->connect_place.' '.$connectDb->connect_text;
				$check=false;
				// *** Check if the source (including role and page) is allready used ***
				for ($j=0; $j<=(count($source_combiner)-1); $j++){
					if ($source_combiner[$j]==$combiner_check){ $check=true; $j2=$j+1;}
				}
				// *** Source not found in array, add new source to array ***
				if (!$check){
					// *** Save new combined source-role-page for check ***
					$source_combiner[]=$combiner_check;
					// *** Save connect_id to show footnotes ***
					$source_footnote_connect_id[]=$connectDb->connect_id;
					// *** Number to show for footnote ***
					$j2=count($source_footnote_connect_id);
				}
				// *** Test line for footnotes ***
				//$text.=' '.$combiner_check.' ';

				$text.=' <a href="'.str_replace("&","&amp;",$_SERVER['REQUEST_URI']).'#source_ref'.$j2.'"><sup>'.$j2.')</sup></a>';
			}
			else{
				// *** Link to extended source ***
				if ($connectDb->connect_source_id AND $source_status=='publish'){

					// *** Always show title of source, show link only after permission check ***
					if ($user['group_sources']=='j'){
						$text.= ', <a href="'.$uri_path.'source.php?database='.$_SESSION['tree_prefix'].
						'&amp;id='.$sourceDb->source_gedcomnr.'">'.strtolower(__('Source')).': ';
						if ($sourceDb->source_title){ $text.= " ".trim($sourceDb->source_title); }
						if ($sourceDb->source_date or $sourceDb->source_place) { $text.=" ".date_place($sourceDb->source_date, $sourceDb->source_place); }
						$text.= '</a>';
					}
					else{
						$text.= ', '.__('Source').': ';
						if ($sourceDb->source_title){ $text.= ' '.trim($sourceDb->source_title); }
						if ($sourceDb->source_date or $sourceDb->source_place){ $text.=" ".date_place($sourceDb->source_date, $sourceDb->source_place); }
					}
				} // *** End of extended source ***

				//else{
				//	// *** No extended source, show source text ***
				//	if ($connectDb->connect_text){
				//		$text.=', '.strtolower(__('Source')).': '.nl2br($connectDb->connect_text);
				//	}
				//}

				// *** Show (extra) source text ***
				if ($connectDb->connect_text AND $source_status=='publish'){
					$text.=', '.__('source text').': '.nl2br($connectDb->connect_text);
				}
			}


		} // *** Loop multiple source ***

	} // *** End of show sources ***
	return $text;
}


// **************************************************
// *** Show source list if footnotes are selected ***
// **************************************************
function show_sources_footnotes(){
	global $source_footnotes, $language, $db, $dbh, $tree_prefix_quoted, $user;
	global $uri_path, $source_footnote_connect_id;

	if (count($source_footnote_connect_id)>0){
		echo '<h3>'.__('Sources').'</h3>';
	}

	for ($j=0; $j<=(count($source_footnote_connect_id)-1); $j++){
		$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
			WHERE connect_id='".$source_footnote_connect_id[$j]."'";
		$connect_sql=$dbh->query($connect_qry);
		$connectDb=$connect_sql->fetch(PDO::FETCH_OBJ);
		// *** Show extended source data ***
		if ($connectDb->connect_source_id){
			$source_sql=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."sources
				WHERE source_gedcomnr='".safe_text($connectDb->connect_source_id)."'");
			$sourceDb=$source_sql->fetch(PDO::FETCH_OBJ);

			// *** Always show title of source, show link only after permission check ***
			echo '<a name="source_ref'.($j+1).'"><b>'.($j+1).')</b></a>';
			if ($user['group_sources']=='j'){
				echo ' <a href="'.$uri_path.'source.php?database='.$_SESSION['tree_prefix'].
				'&amp;id='.$sourceDb->source_gedcomnr.'">'.strtolower(__('Source')).': ';
				if ($sourceDb->source_title){ echo " ".trim($sourceDb->source_title); }
				echo '</a>';
			}
			else{
				if ($sourceDb->source_title){ echo ' '.trim($sourceDb->source_title); }
			}
			if ($connectDb->connect_date or $connectDb->connect_place){
				//if ($connectDb->source_title){ echo ', '; }
				echo " ".date_place($connectDb->connect_date, $connectDb->connect_place);
			}

			// *** Show extra source text ***
			if ($connectDb->connect_text){
				echo ' '.nl2br($connectDb->connect_text);
			}
		}

		else{
			// *** No extended source connected ***
			echo '<a name="source_ref'.($j+1).'">'.($j+1).')</a>';

			// *** Source text ***
			echo ' '.nl2br($connectDb->connect_text);
		}

		// *** Show rest of source items ***

		// *** Source role ***
		if ($connectDb->connect_role){
			echo ', '.__('role').': '.$connectDb->connect_role;
		}

		// *** Source page ***
		if ($connectDb->connect_page){
			echo ', '.strtolower(__('Page')).': '.$connectDb->connect_page;
		}

		echo '<br>';

	} // *** End of loop source footnotes ***

}
?>