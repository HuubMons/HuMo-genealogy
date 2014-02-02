<?php
@set_time_limit(300);

function source_display($sourcenum) {

global $db, $dbh, $user, $pdf, $screen_mode, $language;
global $dataDb;

if($screen_mode!="PDF") {
	include_once("header.php"); //returns CMS_ROOTPATH constant
	include_once(CMS_ROOTPATH."menu.php");
	include_once(CMS_ROOTPATH."include/date_place.php");
	include_once(CMS_ROOTPATH."include/process_text.php");
}

// *** Check user authority ***
if ($user['group_sources']!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}
if($screen_mode!="PDF") {
	include_once(CMS_ROOTPATH."include/language_date.php");
	include_once(CMS_ROOTPATH."include/person_cls.php");
	print '<table class="humo standard">';
	print "<tr><td><h2>".__('Sources')."</h2>";
}

	//@$qry="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."sources
	//	WHERE source_gedcomnr='".$sourcenum."'";
	@$qry="SELECT * FROM ".$tree_prefix_quoted."sources
		WHERE source_gedcomnr='".$sourcenum."'";		
	if ($user['group_show_restricted_source']=='n'){ $qry.=" AND source_status!='restricted'"; }
	//@$source=mysql_query($qry,$db);
	//$die_message=__('No valid source number.');
	//@$sourceDb=mysql_fetch_object($source) or die("$die_message");
	@$source=$dbh->query($qry);
	try {
		@$sourceDb=@$source->fetch(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		echo __('No valid source number.');
	}

	if ($sourceDb->source_title){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Title').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_title."\n");
		}
		else {
			print '<b>'.__('Title').":</b> $sourceDb->source_title<br>";
		}
	}
	if ($sourceDb->source_date){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Date').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,language_date(strtolower($sourceDb->source_date))."\n");
		}
		else {
			print '<b>'.__('Date').":</b> ".language_date(strtolower($sourceDb->source_date))."<br>";
		}
	}
	if ($sourceDb->source_publ){
		$source_publ=$sourceDb->source_publ;

		$pdflink=0;
		if (substr($source_publ,0,7)=='http://'){
			$link=$source_publ;
			$source_publ='<a href="'.$link.'">'.$link.'</a>';
			$pdflink=1;
		}
		if (substr($source_publ,0,8)=='https://'){
			$link=$source_publ;
			$source_publ='<a href="'.$link.'">'.$link.'</a>';
			$pdflink=1;
		}

		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Publication').": ");
			$pdf->SetFont('Arial','',10);
			if($pdflink==1) {
				$pdf->SetFont('Arial','U',10);  $pdf->SetTextColor(28,28,255);
				$pdf->Write(6,strip_tags($source_publ)."\n",strip_tags($source_publ));
				$pdf->SetFont('Arial','',10);  $pdf->SetTextColor(0);
			}
			else {
				$pdf->Write(6,strip_tags($source_publ)."\n");
			}
		}
		else {
			print '<b>'.__('Publication').":</b> $source_publ<br>";
		}
	}
	if ($sourceDb->source_place){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Place').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_place."\n");
		}
		else {
			print '<b>'.__('Place').":</b> $sourceDb->source_place<br>";
		}
	}
	if ($sourceDb->source_refn){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Own code').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_refn."\n");
		}
		else {
			print '<b>'.__('Own code').":</b> $sourceDb->source_refn<br>";
		}
	}
	if ($sourceDb->source_auth){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Author').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_auth."\n");
		}
		else {
			print '<b>'.__('Author').":</b> $sourceDb->source_auth<br>";
		}
	}
	if ($sourceDb->source_subj){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Subject').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_subj."\n");
		}
		else {
			print '<b>'.__('Subject').":</b> $sourceDb->source_subj<br>";
		}
	}
	if ($sourceDb->source_item){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Nr.').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_item."\n");
		}
		else {
			print '<b>'.__('Nr.').":</b> $sourceDb->source_item<br>";
		}
	}
	if ($sourceDb->source_kind){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Kind').": ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_kind."\n");
		}
		else {
			print '<b>'.__('Kind').":</b> $sourceDb->source_kind<br>";
		}
	}
	if ($sourceDb->source_repo_caln){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Archive')." ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_repo_caln."\n");
		}
		else {
			print '<b>'.__('Archive')."</b> $sourceDb->source_repo_caln<br>"; }
		}
	if ($sourceDb->source_repo_page){
		if($screen_mode=="PDF") {
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,__('Page')." ");
			$pdf->SetFont('Arial','',10);
			$pdf->Write(6,$sourceDb->source_repo_page."\n");
		}
		else {
			print '<b>'.__('Page')."</b> $sourceDb->source_repo_page<br>";
		}
	}

	if ($sourceDb->source_text){
		if($screen_mode=="PDF") {
			$source_text=$sourceDb->source_text;
			$source_text=str_replace('<br>', '', $source_text);
			//$pdf->Write(6,html_entity_decode($source_text)."\n");
			$pdf->Write(6,$source_text."\n");
		}
		else {
			print '</td></tr><tr><td>'.process_text($sourceDb->source_text);
		}
	}


	// *** Source pictures not in use yet... ***


	// *** Show repository ***
	//$repo_qry=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."repositories
	//	WHERE repo_gedcomnr='".$sourceDb->source_repo_gedcomnr."'",$db);
	$repo_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."repositories
		WHERE repo_gedcomnr='".$sourceDb->source_repo_gedcomnr."'");		
	//$repoDb=mysql_fetch_object($repo_qry);
	$repoDb=$repo_qry->fetch(PDO::FETCH_OBJ);
	if ($repoDb){
		if($screen_mode=="PDF") {
			// NO REPOSITORIES IN PDF YET...
		}
		else{
			echo '</td></tr><tr><td>';

			echo '<h3>'.__('Repository').'</h3>';

			echo '<b>'.__('Title').':</b> '.$repoDb->repo_name.'<br>';

			if ($user['group_addresses']=='j'){
				echo '<b>'.__('Zip code').':</b> '.$repoDb->repo_zip.'<br>';
				echo '<b>'.__('Address').':</b> '.$repoDb->repo_address.'<br>';
			}

			if ($repoDb->repo_date){ echo '<b>'.__('Date').':</b> '.$repoDb->repo_date.'<br>'; }
			if ($repoDb->repo_place){ echo '<b>'.__('Place').':</b> '.$repoDb->repo_place.'<br>'; }	
			echo nl2br($repoDb->repo_text);
		}
	}

if($screen_mode!="PDF") { // we do not want all persons in the database as given online so
					              // in the pdf file so we'll take just the above details
					              // and leave references to persons

print '</td></tr>';
print '<tr><td>';

	$person_cls = New person_cls;

	// *** Sources in address table ***
	$address_sql="SELECT * FROM ".$tree_prefix_quoted."addresses WHERE address_source NOT LIKE ''";
	//$address_qry=mysql_query($address_sql,$db);
	@$address_qry=$dbh->query($address_sql);
	//while (@$address_Db=mysql_fetch_object($address_qry)){
	while (@$address_Db=$address_qry->fetch(PDO::FETCH_OBJ)){
		$sourceid=explode(";",$address_Db->address_source);
		for ($i=0; $i<=substr_count($address_Db->address_source, ';'); $i++){
			if (substr($sourceid[$i],1,-1)==$sourceDb->source_gedcomnr){
				//$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
				//	WHERE pers_gedcomnumber='$address_Db->address_person_id'",$db);
				//$personDb=mysql_fetch_object($person);
				$person=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
					WHERE pers_gedcomnumber='$address_Db->address_person_id'");
				$personDb=$person->fetch(PDO::FETCH_OBJ);				
				echo __('Source for address:').' <a href="'.CMS_ROOTPATH.'family.php?id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
				$name=$person_cls->person_name($personDb);
				echo $name["standard_name"];
				echo '</a><br>';
			}
		}
	}

	// *** Find person data ***
	// *** This seperate function speeds up the sources page ***
	function person_data($familyDb){
		global $db, $dbh, $tree_prefix_quoted;
		if ($familyDb->fam_man){
			//$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
			//WHERE pers_gedcomnumber='$familyDb->fam_man'",$db);
			$person=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
			WHERE pers_gedcomnumber='$familyDb->fam_man'");			
		}
		else{
			//$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
			//WHERE pers_gedcomnumber='$familyDb->fam_woman'",$db);
			$person=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
			WHERE pers_gedcomnumber='$familyDb->fam_woman'");			
		}
		//@$personDb=mysql_fetch_object($person);
		@$personDb=$person->fetch(PDO::FETCH_OBJ);
		return $personDb;
	}


	// *** Sources in connect table ***
	$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
		WHERE connect_source_id='".$sourceDb->source_gedcomnr."'
		ORDER BY connect_kind, connect_sub_kind, connect_order";
	//$connect_sql=mysql_query($connect_qry,$db);
	$connect_sql=$dbh->query($connect_qry);
	//while($connectDb=mysql_fetch_object($connect_sql)){
	while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
		// *** Person source ***
		if ($connectDb->connect_kind=='person'){
			if ($connectDb->connect_sub_kind=='person_source'){
				echo __('Source for:');
			}
			if ($connectDb->connect_sub_kind=='pers_name_source'){
				echo __('Source for name:');
			}
			if ($connectDb->connect_sub_kind=='pers_birth_source'){
				echo __('Source for birth:');
			}
			if ($connectDb->connect_sub_kind=='pers_bapt_source'){
				echo __('Source for baptism:');
			}
			if ($connectDb->connect_sub_kind=='pers_death_source'){
				echo __('Source for death:');
			}
			if ($connectDb->connect_sub_kind=='pers_buried_source'){
				echo __('Source for burial:');
			}
			if ($connectDb->connect_sub_kind=='pers_text_source'){
				echo __('Source for text:');
			}
			if ($connectDb->connect_sub_kind=='pers_sexe_source'){
				echo __('Source for sexe:');
			}
			//else { echo 'TEST'; }

			if ($connectDb->connect_sub_kind=='event_source'){
				// *** Sources by event ***
				$event_sql="SELECT * FROM ".$tree_prefix_quoted."events
					WHERE event_id='".$connectDb->connect_connect_id."'";
				//$event_qry=mysql_query($event_sql,$db);
				//$event_Db=mysql_fetch_object($event_qry);
				$event_qry=$dbh->query($event_sql);
				$event_Db=$event_qry->fetch(PDO::FETCH_OBJ);				
				// *** Person source ***
				if ($event_Db->event_person_id){
					/*
					$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
						WHERE pers_gedcomnumber='$event_Db->event_person_id'",$db);
					$personDb=mysql_fetch_object($person);
					*/
					$person=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
						WHERE pers_gedcomnumber='$event_Db->event_person_id'");
					$personDb=$person->fetch(PDO::FETCH_OBJ);					
					print __('Source for:').' <a href="'.CMS_ROOTPATH.'family.php?id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
					$name=$person_cls->person_name($personDb);
					echo $name["standard_name"].'</a>';
					if ($event_Db->event_event){ echo ' '.$event_Db->event_event; }
				}
			}
			else{
				/*
				$person=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
					WHERE pers_gedcomnumber='$connectDb->connect_connect_id'",$db);
				$personDb=mysql_fetch_object($person);
				*/
				$person=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
					WHERE pers_gedcomnumber='$connectDb->connect_connect_id'");
				$personDb=$person->fetch(PDO::FETCH_OBJ);				
				echo ' <a href="'.CMS_ROOTPATH.'family.php?id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
				$name=$person_cls->person_name($personDb);
				echo $name["standard_name"].'</a>';
			}
		}

		// *** Family source ***
		if ($connectDb->connect_kind=='family'){
			if ($connectDb->connect_sub_kind=='family_source'){
				echo __('Source for family:');
			}
			if ($connectDb->connect_sub_kind=='fam_relation_source'){
				echo __('Source for cohabitation:');
			}
			if ($connectDb->connect_sub_kind=='fam_marr_notice_source'){
				echo __('Source for marriage notice:');
			}
			if ($connectDb->connect_sub_kind=='fam_marr_source'){
				echo __('Source for marriage:');
			}
			if ($connectDb->connect_sub_kind=='fam_marr_church_notice_source'){
				echo __('Source for marriage notice (church):');
			}
			if ($connectDb->connect_sub_kind=='fam_marr_church_source'){
				echo __('Source for marriage (church):');
			}
			if ($connectDb->connect_sub_kind=='fam_div_source'){
				echo __('Source for divorce:');
			}
			if ($connectDb->connect_sub_kind=='fam_text_source'){
				echo __('Source for family text:');
			}
			//else{
			//	echo 'TEST2';
			//}

			if ($connectDb->connect_sub_kind=='event'){

				// *** Sources by event ***
				/*
				$event_sql="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."events
					WHERE event_id='".$connectDb->connect_connect_id."'";
				$event_qry=mysql_query($event_sql,$db);
				$event_Db=mysql_fetch_object($event_qry);
				*/
				$event_sql="SELECT * FROM ".$tree_prefix_quoted."events
					WHERE event_id='".$connectDb->connect_connect_id."'";
				$event_qry=$dbh->query($event_sql);
				$event_Db=$event_qry->fetch(PDO::FETCH_OBJ);				
				// *** Family source ***
				if ($event_Db->event_family_id){					print __('Source for family:');
					/*
					$family=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."family
						WHERE fam_gedcomnumber='".$event_Db->event_family_id."'",$db);
					$familyDb=mysql_fetch_object($family);
					$personDb=person_data($familyDb);
					*/
					$family=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."family
						WHERE fam_gedcomnumber='".$event_Db->event_family_id."'");
					$familyDb=$family->fetch(PDO::FETCH_OBJ);
					$personDb=person_data($familyDb);					
					echo ' <a href="'.CMS_ROOTPATH.'family.php?id='.$event_Db->event_family_id.'">';
					$name=$person_cls->person_name($personDb);
					echo $name["standard_name"].'</a>';

					if ($event_Db->event_event){ echo ' '.$event_Db->event_event; }
				}

			}
			else{
				/*
				$family=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."family
					WHERE fam_gedcomnumber='".$connectDb->connect_connect_id."'",$db);
				$familyDb=mysql_fetch_object($family);
				$personDb=person_data($familyDb);
				*/
				$family=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."family
					WHERE fam_gedcomnumber='".$connectDb->connect_connect_id."'");
				$familyDb=$family->fetch(PDO::FETCH_OBJ);
				$personDb=person_data($familyDb);				
				echo ' <a href="'.CMS_ROOTPATH.'family.php?id='.$connectDb->connect_connect_id.'">';
				$name=$person_cls->person_name($personDb);
				echo $name["standard_name"].'</a>';
			}

		}

		// *** Extra source connect information by every source ***
		if ($connectDb->connect_date or $connectDb->connect_place){
			echo " ".date_place($connectDb->connect_date, $connectDb->connect_place);
		}
		// *** Source role ***
		if ($connectDb->connect_role){
			echo ', '.__('role').': '.$connectDb->connect_role;
		}
		// *** Source page ***
		if ($connectDb->connect_page){
			echo ', '.strtolower(__('Page')).': '.$connectDb->connect_page;
		}
		echo '<br>';
	}

print '</td></tr>';

print '</table>';

include_once(CMS_ROOTPATH."footer.php");

} // end if not PDF

} // end function source_display

if(isset($_GET["id"])) { // source.php is called from show_sources.php, sources.php
	$sourcenumber=$_GET["id"];
	source_display($sourcenumber);
}

?>