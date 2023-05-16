<?php

/**
 * Source to pdf 
 */
if (isset($_GET["id"])) {
	source_to_pdf($_GET["id"]);
}

/**
 * Show a single source on a pdf
 */
function source_to_pdf(int $sourcenum)
{
	global $db_functions, $user, $pdf, $screen_mode;
	
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

	$pdf->SetFont('DejaVu', 'B', 10);
	$pdf->Write(6, __('Title') . ": ");
	$pdf->SetFont('DejaVu', '', 10);
	$pdf->Write(6, $sourceDb->source_title . "\n");
	
	$pdf->SetFont('DejaVu', 'B', 10);
	$pdf->Write(6, __('Date') . ": ");
	$pdf->SetFont('DejaVu', '', 10);
	$pdf->Write(6, language_date(strtolower($sourceDb->source_date)) . "\n");

	if ($sourceDb->source_publ) {
		$source_publ = $sourceDb->source_publ;
		
		$pdf->SetFont('DejaVu', 'B', 10);
		$pdf->Write(6, __('Publication') . ": ");
		$pdf->SetFont('DejaVu', '', 10);
		//if($pdflink==1) {
		if (substr($source_publ, 0, 7) == 'http://' or substr($source_publ, 0, 8) == 'https://') {
			$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->SetTextColor(28, 28, 255);
			$pdf->Write(6, strip_tags($source_publ) . "\n", strip_tags($source_publ));
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->SetTextColor(0);
		} else {
			$pdf->Write(6, strip_tags($source_publ) . "\n");
		}
	}
	if ($sourceDb->source_place) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Place') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_place . "\n");
	}
	if ($sourceDb->source_refn) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Own code') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_refn . "\n");
	}
	if ($sourceDb->source_auth) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Author') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_auth . "\n");
	}
	if ($sourceDb->source_subj) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Subject') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_subj . "\n");
	}
	if ($sourceDb->source_item) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Nr.') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_item . "\n");
	}
	if ($sourceDb->source_kind) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Kind') . ": ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_kind . "\n");
	}
	if ($sourceDb->source_repo_caln) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Archive') . " ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_repo_caln . "\n");
	}
	if ($sourceDb->source_repo_page) {
		$pdf->SetFont('DejaVu', 'B', 10);
			$pdf->Write(6, __('Page') . " ");
			$pdf->SetFont('DejaVu', '', 10);
			$pdf->Write(6, $sourceDb->source_repo_page . "\n");
	}

	if ($sourceDb->source_text) {
		$source_text = $sourceDb->source_text;
			$source_text = str_replace('<br>', '', $source_text);
			//$pdf->Write(6,html_entity_decode($source_text)."\n");
			$pdf->Write(6, $source_text . "\n");
	}

	// *** Pictures by source ***
	if ($screen_mode == "PDF") {} 

	// *** Show repository ***
	// NO REPOSITORIES IN PDF YET... 
	// NO PERSON IN PDF YET...
} 
