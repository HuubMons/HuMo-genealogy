<?php
// First test scipt made by: Klaas de Winkel
// Graphical script made by: Theo Huitema
// Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
// Graphical part: improved lay-out by: Huub Mons.
// Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
// July 2011: translated all variables to english by: Huub Mons.
//error_reporting(E_ALL);
@set_time_limit(3000);

//==========================
global $humo_option, $user, $marr_date_array, $marr_place_array;
global $gedcomnumber, $language;
global $screen_mode, $dirmark1, $dirmark2, $pdf_footnotes;

// == define roman numbers (max. 60 generations)
$rom_nr = array( 1=>'I',    2=>'II',    3=>'III',    4=>'IV',    5=>'V',    6=>'VI',     7=>'VII',    8=>'VIII',    9=>'IX',   10=>'X',
11=>'XI',  12=>'XII',  13=>'XIII',  14=>'XIV',  15=>'XV',  16=>'XVI',   17=>'XVII',  18=>'XVIII',  19=>'XIX',  20=>'XX',
21=>'XXI', 22=>'XXII', 23=>'XXIII', 24=>'XXIV', 25=>'XXV', 26=>'XXVII', 27=>'XXVII', 28=>'XXVIII', 29=>'XXIX', 30=>'XXX',
31=>'XXXI',32=>'XXXII',33=>'XXXIII',34=>'XXXIV',35=>'XXXV',36=>'XXXVII',37=>'XXXVII',38=>'XXXVIII',39=>'XXXIX',40=>'XL',
41=>'XLI', 42=>'XLII', 43=>'XLIII', 44=>'XLIV', 45=>'XLV', 46=>'XLVII', 47=>'XLVII', 48=>'XLVIII', 49=>'XLIX', 50=>'L',
51=>'LI',  52=>'LII',  53=>'LIII',  54=>'LIV',  55=>'LV',  56=>'LVII',  57=>'LVII',  58=>'LVIII',  59=>'LIX',  60=>'LX',);

$screen_mode=''; 
if (isset($_POST["screen_mode"]) AND $_POST["screen_mode"]=='PDF'){ $screen_mode='PDF'; }
if (isset($_POST["screen_mode"]) AND $_POST["screen_mode"]=='RTF'){ $screen_mode='RTF'; }
if (isset($_POST["screen_mode"]) AND $_POST["screen_mode"]=='ASPDF'){ $screen_mode='ASPDF'; }
if (isset($_GET["screen_mode"]) AND $_GET["screen_mode"]=='ancestor_sheet'){ $screen_mode='ancestor_sheet'; }
if (isset($_GET["screen_mode"]) AND $_GET["screen_mode"]=='ancestor_chart'){ $screen_mode='ancestor_chart'; }
 
if(isset($hourglass) AND $hourglass===true) { $screen_mode='ancestor_chart'; }
else $hourglass=false;

$pdf_source= array();  // is set in show_sources.php with sourcenr as key to be used in source appendix
	// see end of this code 

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/language_event.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/witness.php");
// Needed for marriage:
include_once(CMS_ROOTPATH."include/process_text.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/marriage_cls.php");
include_once(CMS_ROOTPATH."include/show_sources.php");
include_once(CMS_ROOTPATH."include/show_picture.php");

if($screen_mode!='PDF' AND $screen_mode!='ASPDF') {  //we can't have a menu in pdf...
	include_once(CMS_ROOTPATH."menu.php");
} else {
	include_once(CMS_ROOTPATH."include/db_functions_cls.php");
	$db_functions = New db_functions;

	if (isset($_SESSION['tree_prefix'])){
		$dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."'
			WHERE tree_prefix='".$tree_prefix_quoted."'";
		@$datasql = $dbh->query($dataqry);
		@$dataDb = @$datasql->fetch(PDO::FETCH_OBJ);
	}

	$tree_prefix=$dataDb->tree_prefix;
	$tree_id=$dataDb->tree_id;
	$db_functions->set_tree_id($dataDb->tree_id);
}
if($hourglass===false) {
	// *** CHECK: $family_id is actually a person_id... ***
	$family_id='I1'; // *** Default value, normally not used... ***
	if (isset($_GET["id"])){ $family_id=$_GET["id"]; }
	if (isset($_POST["id"])){ $family_id=$_POST["id"]; }

	// *** Check if person gedcomnumber is valid ***
	$db_functions->check_person($family_id);
}
if ($screen_mode!='ancestor_chart' AND $screen_mode!='ancestor_sheet' AND $screen_mode!='ASPDF'){
	// *** Source presentation selected by user (title/ footnote or hide sources) ***
	// *** Default setting is selected by administrator ***
	if (isset($_GET['source_presentation'])){
		$_SESSION['save_source_presentation']=safe_text_db($_GET["source_presentation"]);
	}
	$source_presentation=$user['group_source_presentation'];
	if (isset($_SESSION['save_source_presentation'])){
		$source_presentation=$_SESSION['save_source_presentation'];
	}
	else{
		// *** Save setting in session (if no choice is made, this is admin default setting) ***
		$_SESSION['save_source_presentation']=safe_text_db($source_presentation);
	}
}

if($screen_mode!='PDF' AND $screen_mode!='RTF' AND $screen_mode!="ancestor_sheet" AND $screen_mode!='ASPDF' AND $hourglass===false) {

	echo '<div class="standard_header fonts">';
		if ($screen_mode=='ancestor_chart'){
			echo __('Ancestor chart');
		}
		else {
			echo __('Ancestor report');

			//if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl") {
			if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl" AND $language["name"]!="简体中文") {
				// Show pdf button
				echo ' <form method="POST" action="'.$uri_path.'report_ancestor.php?show_sources=1" style="display : inline;">';
				echo '<input type="hidden" name="id" value="'.$family_id.'">';
				echo '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
				echo '<input type="hidden" name="screen_mode" value="PDF">';
				echo '<input class="fonts" type="Submit" name="submit" value="'.__('PDF Report').'">';
				echo '</form>';
			}

			if($user["group_rtf_button"]=='y' AND $language["dir"]!="rtl") {
				// Show rtf button
				echo ' <form method="POST" action="'.$uri_path.'report_ancestor.php?show_sources=1" style="display : inline;">';
				echo '<input type="hidden" name="id" value="'.$family_id.'">';
				echo '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
				echo '<input type="hidden" name="screen_mode" value="RTF">';
				echo '<input class="fonts" type="Submit" name="submit" value="'.__('RTF Report').'">';
				echo '</form>';
			}
		}
	echo '</div>';
}

if($screen_mode=='PDF') {
	//initialize pdf generation
	$pdfdetails=array();
	$pdf_marriage=array();
	$pdf=new PDF();
	@$persDb = $db_functions->get_person($family_id);
	// *** Use person class ***
	$pers_cls = New person_cls;
	$pers_cls->construct($persDb);
	$name=$pers_cls->person_name($persDb);
	$title=pdf_convert(__('Ancestor report').__(' of ').$name["standard_name"]);

	$pdf->SetTitle($title);
	$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
	$pdf->AddPage();

	$pdf->SetFont('Arial','B',15);
	$pdf->Ln(4);
	$name=$pers_cls->person_name($persDb);
	$pdf->MultiCell(0,10,__('Ancestor report').__(' of ').$name["standard_name"],0,'C');
	$pdf->Ln(4);
	$pdf->SetFont('Arial','',12);
}
if($screen_mode=='RTF') {  // initialize rtf generation
	require_once 'include/phprtflite/lib/PHPRtfLite.php';

	// *** registers PHPRtfLite autoloader (spl) ***
	PHPRtfLite::registerAutoloader();
	// *** rtf document instance ***
	$rtf = new PHPRtfLite();

	// *** Add section ***
	$sect = $rtf->addSection();

	// *** RTF Settings ***
	$arial10 = new PHPRtfLite_Font(10, 'Arial');
	$arial12 = new PHPRtfLite_Font(12, 'Arial');
	$arial14 = new PHPRtfLite_Font(14, 'Arial', '#000066');
	//Fonts
	$fontHead = new PHPRtfLite_Font(12, 'Arial');
	$fontSmall = new PHPRtfLite_Font(3);
	$fontAnimated = new PHPRtfLite_Font(10);
	$fontLink = new PHPRtfLite_Font(10, 'Helvetica', '#0000cc');

	$parNames = new PHPRtfLite_ParFormat();
	$parNames->setBackgroundColor('#FFFFFF');
	$parNames->setIndentLeft(0);
	$parNames->setSpaceBefore(0);
	$parNames->setSpaceAfter(0);

	$parHead = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
	$parHead->setSpaceBefore(3);
	$parHead->setSpaceAfter(8);
	$parHead->setBackgroundColor('#baf4c1');

	$parGen = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
	$parGen->setSpaceBefore(0);
	$parGen->setSpaceAfter(8);
	$parGen->setBackgroundColor('#baf4c1');

	$parSimple = new PHPRtfLite_ParFormat();
	$parSimple->setIndentLeft(2.5);
	$parSimple->setIndentRight(0.5);

	// *** Generate title of RTF file ***
	@$persDb = $db_functions->get_person($family_id);
	// *** Use person class ***
	$pers_cls = New person_cls;
	$pers_cls->construct($persDb);
	$name=$pers_cls->person_name($persDb);
		$title=__('Ancestor report').__(' of ').$name["standard_name"];

	//$sect->writeText($title, $arial14, new PHPRtfLite_ParFormat());
	$sect->writeText($title, $arial14, $parHead);

	$file_name=date("Y_m_d_H_i_s").'.rtf';
	// *** FOR TESTING PURPOSES ONLY ***
	if (@file_exists("../gedcom-bestanden")) $file_name='../gedcom-bestanden/'.$file_name;
		else $file_name='tmp_files/'.$file_name;

	// *** Automatically remove old RTF files ***
	$dh  = opendir('tmp_files');
	while (false !== ($filename = readdir($dh))) {
		if (substr($filename, -3) == "rtf"){
			//echo 'tmp_files/'.$filename.'<br>';
			// *** Remove files older then today ***
			if (substr($filename,0,10)!=date("Y_m_d")) unlink('tmp_files/'.$filename);
		}
	}

	//echo $file_name;
}

if ($screen_mode!='ancestor_chart' AND $screen_mode!='ancestor_sheet' AND $screen_mode!='ASPDF'){
	$ancestor_array2[] = $family_id;
	$ancestor_number2[]=1;
	$marriage_gedcomnumber2[]=0;
	$generation = 1;

	$language["gen1"]='';
	if (__('PROBANT')!='PROBANT'){ $language["gen1"].=__('PROBANT'); }

	$language["gen2"]=__('Parents');
	$language["gen3"]=__('Grandparents');
	$language["gen4"]=__('Great-Grandparents');
	$language["gen5"]=__('Great Great-Grandparents');
	$language["gen6"]=__('3rd Great-Grandparents');
	$language["gen7"]=__('4th Great-Grandparents');
	$language["gen8"]=__('5th Great-Grandparents');
	$language["gen9"]=__('6th Great-Grandparents');
	$language["gen10"]=__('7th Great-Grandparents');
	$language["gen11"]=__('8th Great-Grandparents');
	$language["gen12"]=__('9th Great-Grandparents');
	$language["gen13"]=__('10th Great-Grandparents');
	$language["gen14"]=__('11th Great-Grandparents');
	$language["gen15"]=__('12th Great-Grandparents');
	$language["gen16"]=__('13th Great-Grandparents');
	$language["gen17"]=__('14th Great-Grandparents');
	$language["gen18"]=__('15th Great-Grandparents');
	$language["gen19"]=__('16th Great-Grandparents');
	$language["gen20"]=__('17th Great-Grandparents');
	$language["gen21"]=__('18th Great-Grandparents');
	$language["gen22"]=__('19th Great-Grandparents');
	$language["gen23"]=__('20th Great-Grandparents');
	$language["gen24"]=__('21th Great-Grandparents');
	$language["gen25"]=__('22th Great-Grandparents');
	$language["gen26"]=__('23th Great-Grandparents');
	$language["gen27"]=__('24th Great-Grandparents');
	$language["gen28"]=__('25th Great-Grandparents');
	$language["gen29"]=__('26th Great-Grandparents');
	$language["gen30"]=__('27th Great-Grandparents');
	$language["gen31"]=__('28th Great-Grandparents');
	$language["gen32"]=__('29th Great-Grandparents');
	$language["gen33"]=__('30th Great-Grandparents');
	$language["gen34"]=__('31th Great-Grandparents');
	$language["gen35"]=__('32th Great-Grandparents');
	$language["gen36"]=__('33th Great-Grandparents');
	$language["gen37"]=__('34th Great-Grandparents');
	$language["gen38"]=__('35th Great-Grandparents');
	$language["gen39"]=__('36th Great-Grandparents');
	$language["gen40"]=__('37th Great-Grandparents');
	$language["gen41"]=__('38th Great-Grandparents');
	$language["gen42"]=__('39th Great-Grandparents');
	$language["gen43"]=__('40th Great-Grandparents');
	$language["gen44"]=__('41th Great-Grandparents');
	$language["gen45"]=__('42th Great-Grandparents');
	$language["gen46"]=__('43th Great-Grandparents');
	$language["gen47"]=__('44th Great-Grandparents');
	$language["gen48"]=__('45th Great-Grandparents');
	$language["gen49"]=__('46th Great-Grandparents');
	$language["gen50"]=__('47th Great-Grandparents');

	if($screen_mode!='PDF' AND $screen_mode!='RTF') {
		echo '<table style="border-style:none" align="center"><tr><td></td></tr>';
	}

	$listed_array=array();

	// *** Loop for ancestor report ***
	while (isset($ancestor_array2[0])){
		unset($ancestor_array);
		$ancestor_array=$ancestor_array2;
		unset($ancestor_array2);

		unset($ancestor_number);
		$ancestor_number=$ancestor_number2;
		unset($ancestor_number2);

		unset($marriage_gedcomnumber);
		$marriage_gedcomnumber=$marriage_gedcomnumber2;
		unset($marriage_gedcomnumber2);

		if($screen_mode!='PDF' AND $screen_mode!='RTF') {
			echo '</table>';

				echo '<div class="standard_header fonts">'.__('generation ').$rom_nr[$generation];
				if (isset($language["gen".$generation]) AND $language["gen".$generation]){
					echo ' ('.$language["gen".$generation].')';
				}
				echo '</div><br>';

			echo '<table class="humo standard" align="center">';
		}
		elseif($screen_mode=="RTF") {
			$rtf_text=__('generation ').$rom_nr[$generation];
			$sect->writeText($rtf_text, $arial14, $parGen);
		}
		else {
			//echo 'pdf generation<br>';
			$pdf->Cell(0,2,"",0,1);
			$pdf->SetFont('Arial','BI',14);
			$pdf->SetFillColor(200,220,255);
			if($pdf->GetY() > 260) { $pdf->AddPage(); $pdf->SetY(20); }
			if (isset($language["gen".$generation]) AND $language["gen".$generation]){
				$pdf->Cell(0,8,pdf_convert(__('generation ').$rom_nr[$generation].' ('.$language["gen".$generation].')'),0,1,'C',true);
			}
			else {
				$pdf->Cell(0,8,pdf_convert(__('generation ').$rom_nr[$generation]),0,1,'C',true);
			}
			$pdf->SetFont('Arial','',12);
		}

		// *** Loop per generation ***
		for ($i=0; $i<count($ancestor_array); $i++) {

			$listednr='';
// Check this code, can be improved?
			foreach ($listed_array as $key => $value) {
				if($value==$ancestor_array[$i]) {$listednr=$key;}
				// if person was already listed, $listednr gets kwartier number for reference in report:
				// instead of person's details it will say: "already listed above under number 4234"
				// and no additional ancestors will be looked for, to prevent duplicated branches
			}
			if($listednr=='') {  //if not listed yet, add person to array
				$listed_array[$ancestor_number[$i]]=$ancestor_array[$i];  
			}

			if ($ancestor_array[$i]!='0'){
				@$person_manDb = $db_functions->get_person($ancestor_array[$i]);
				$man_cls = New person_cls;
				$man_cls->construct($person_manDb);
				$privacy_man=$man_cls->privacy;

				// for pdf function pdf_ancestor_name() further along
				//$sexe=$person_manDb->pers_sexe;

				if (strtolower($person_manDb->pers_sexe)=='m' AND $ancestor_number[$i]>1){
					@$familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

					// *** Use privacy filter of woman ***
					@$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
					$woman_cls = New person_cls;
					$woman_cls->construct($person_womanDb);
					$privacy_woman=$woman_cls->privacy;

					// *** Use class for marriage ***
					$marriage_cls = New marriage_cls;
					$marriage_cls->construct($familyDb, $privacy_man, $privacy_woman);
					$family_privacy=$marriage_cls->privacy;
				}
				if($screen_mode!='PDF' AND $screen_mode!='RTF') {
					echo '<tr><td valign="top" width="80" nowrap><b>'.$ancestor_number[$i].
						'</b> ('.floor($ancestor_number[$i]/2).')</td>';

					echo '<td>';
					//*** Show data man ***
					echo '<div class="parent1">';
						// ***  Use "child", to show a link for own family. ***
						echo $man_cls->name_extended("child");
						if ($listednr=='') {
							echo $man_cls->person_data("standard", $ancestor_array[$i]);
						}
						else { // person was already listed
							echo ' <strong> ('.__('Already listed above as number ').$listednr.') </strong>';
						}
					echo '</div>';
					echo '</td></tr>';
				}
				elseif($screen_mode == "RTF") {
					$sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
					$table = $sect->addTable();
					$table->addRow(1);
					$table->addColumnsList(array(2,0.5,14));

					$rtf_text = $ancestor_number[$i]."(".floor($ancestor_number[$i]/2).")";
					$cell = $table->getCell(1, 1);
					$cell->writeText($rtf_text, $arial10, $parNames);
					$rtf_text = strip_tags($man_cls->name_extended("child"),"<b><i>");
					$cell = $table->getCell(1, 2);
					if ($person_manDb->pers_sexe=="M")
						$cell->addImage('images/man.jpg', null);
					elseif ($person_manDb->pers_sexe=="F")
						$cell->addImage(CMS_ROOTPATH.'images/woman.jpg', null);
					else
						$cell->addImage(CMS_ROOTPATH.'images/unknown.jpg', null);
					$cell = $table->getCell(1, 3);
					$cell->writeText($rtf_text, $arial12, $parNames);
					if ($listednr=='') {
						$rtf_text=strip_tags($man_cls->person_data("standard", $ancestor_array[$i]),"<b><i>");
						$rtf_text = substr($rtf_text,0,-1); // take off newline
					}
					else { // person was already listed
						$rtf_text=strip_tags('('.__('Already listed above as number ').$listednr.') ',"<b><i>");
					}
					$cell->writeText($rtf_text, $arial12, $parNames);

					$result = show_media('person',$person_manDb->pers_gedcomnumber); 
					if(isset($result[1]) AND count($result[1])>0) { 
						$break=1; $textarr = Array(); $goodpics=FALSE;
						foreach($result[1] as $key => $value) {  
							if (strpos($key,"path")!==FALSE) {
								$type = substr($result[1][$key],-3); 
								if($type=="jpg" OR $type=="png") {
									if($goodpics==FALSE) { //found 1st pic - make table
										$table2 = $sect->addTable();
										$table2->addRow(0.1);
										$table2->addColumnsList(array(2.5,5,5));
										$goodpics=TRUE;
									}
									$break++;
									$cell = $table2->getCell(1, $break);
									$imageFile = $value;
									$image = $cell->addImage($imageFile);
									$txtkey = str_replace("pic_path","pic_text",$key); 
									if(isset($result[1][$txtkey])) {
										$textarr[]=$result[1][$txtkey];
									}
									else { $textarr[]="&nbsp;"; }
								}

							}
							if($break==3) break; // max 2 pics
						} 
						$break1=1;
						if(count($textarr)>0) {
							$table2->addRow(0.1); //add row only if there is photo text
							foreach($textarr as $value) {
								$break1++;
								$cell = $table2->getCell(2, $break1);
								$cell->writeText($value);
							}
						}  
					}

				}
				else {
					// pdf NUMBER + MAN NAME + DATA
					$pdf->pdf_ancestor_name($ancestor_number[$i],$person_manDb->pers_sexe,$man_cls->name_extended("child"));
					if($listednr=='') {
						$pdfdetails= $man_cls->person_data("standard",$ancestor_array[$i]);
						if($pdfdetails) {
							$pdf->pdfdisplay($pdfdetails,"ancestor");
						}
						elseif ($ancestor_number[$i]>9999) {
							$pdf->Ln(8); // (number) was placed under previous number
							//  and there's no data so we have to move 1 line down for next person 
						}
					}
					else { // person was already listed
						$thisx=$pdf->GetX();
						$pdf->SetX($thisx+28);
						$pdf->Write(8,__('Already listed above as number ').$listednr."\n");
						$pdf->SetX($thisx);
					}

					$temp=0;
					$temp=floor($ancestor_number[$i]%2);
					if($ancestor_number[$i]>1 AND $temp==1 AND $i+1<count($ancestor_array)) {
						// if we're not in first generation (one person)
						// and we are after writing the woman's details
						// and there is at least one person of another family to come in this generation
						// then place a devider line
						$pdf->Cell(0,1,"",'B',1);
						$pdf->Ln(1);
					}
				}

				// Show own marriage (new line, after man)
				if (strtolower($person_manDb->pers_sexe)=='m' AND $ancestor_number[$i]>1){
					if($screen_mode!='PDF' AND $screen_mode!='RTF') {
						echo '<tr><td>&nbsp;</td><td>';
						echo '<span class="marriage">';
					}
					// *** $family_privacy='1' betekent filteren ***
					if ($family_privacy){
						if($screen_mode!='PDF' AND $screen_mode!='RTF') {
							echo __(' to: ');
						}
						elseif($screen_mode=="RTF") {
							$rtf_text = __(' to: ');
							$sect->writeText($rtf_text, $arial12, $parSimple);
						}
						else {
							$pdf->SetX(37);
							$pdf->Write(6,__(' to: ')."\n");
						}

						// If privacy filter is activated, show divorce
						if ($familyDb->fam_div_date OR $familyDb->fam_div_place){
							if($screen_mode!='PDF' AND $screen_mode!='RTF') {
								echo ' <span class="divorse">('.trim(__('divorced ')).')</span>';
							}
							elseif($screen_mode=="RTF") {
								$rtf_text = trim(__('divorced '));
								$sect->writeText($rtf_text, $arial12, $parSimple);
							}
							else {
								$pdf->Write(6,' ('.trim(__('divorced ')).')');
							}
						}
						// Show end of relation here?
						//if ($familyDb->fam_relation_end_date){
						//  echo ' <span class="divorse">('.trim(__('divorced ')).')</span>';
						//}
					}
					else{
						if($screen_mode!='PDF' AND $screen_mode!='RTF' ) {
							echo $marriage_cls->marriage_data();
						}
						elseif($screen_mode=="RTF") {
							$rtf_text = strip_tags($marriage_cls->marriage_data(),"<b><i>");
							$sect->writeText($rtf_text, $arial12, $parSimple);
						}
						else {
							//show pdf MARRIAGE DATA
							$pdf_marriage=$marriage_cls->marriage_data();
							if($pdf_marriage) {
								$pdf->displayrel($pdf_marriage,"ancestor");
							}
						}
					}
					if($screen_mode!='PDF' AND $screen_mode!='RTF') {
						echo '</span>';
						echo '</td></tr>';
					}
				}

				// ==	Check for parents
				if ($person_manDb->pers_famc  AND $listednr==''){
					@$family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
					if ($family_parentsDb->fam_man){
						$ancestor_array2[] = $family_parentsDb->fam_man;
						$ancestor_number2[]=(2*$ancestor_number[$i]);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}

					if ($family_parentsDb->fam_woman){
						$ancestor_array2[]= $family_parentsDb->fam_woman;
						$ancestor_number2[]=(2*$ancestor_number[$i]+1);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}
					else{
						// *** N.N. name ***
						$ancestor_array2[]= '0';
						$ancestor_number2[]=(2*$ancestor_number[$i]+1);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}
				}

			} else{

				// *** Show N.N. person ***
				@$person_manDb = $db_functions->get_person($ancestor_array[$i]);
				$man_cls = New person_cls;
				$man_cls->construct($person_manDb);
				$privacy_man=$man_cls->privacy;

				if($screen_mode!='PDF' AND $screen_mode!='RTF') {  
					echo '<tr><td valign="top" width="80" nowrap><b>'.$ancestor_number[$i].
						'</b> ('.floor($ancestor_number[$i]/2).')</td>';

					echo '<td>';
					//*** Show person_data of man ***
					echo '<div class="parent1">';
						// ***  Use "child", to show a link to own family. ***
						echo $man_cls->name_extended("child");
						echo $man_cls->person_data("standard", $ancestor_array[$i]);
					echo '</div>';
					echo '</td></tr>';  
				}
				elseif($screen_mode == "RTF") {
					$sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
					$table = $sect->addTable();
					$table->addRow(1);
					$table->addColumnsList(array(2,0.5,14));

					$rtf_text = $ancestor_number[$i]."(".floor($ancestor_number[$i]/2).")";
					$cell = $table->getCell(1, 1);
					$cell->writeText($rtf_text, $arial10, $parNames);
					$cell = $table->getCell(1, 2);
					if ($person_manDb AND $person_manDb->pers_sexe=="M")
						$cell->addImage('images/man.jpg', null);
					elseif ($person_manDb AND $person_manDb->pers_sexe=="F")
						$cell->addImage(CMS_ROOTPATH.'images/woman.jpg', null);
					else
						$cell->addImage(CMS_ROOTPATH.'images/unknown.jpg', null);
					$rtf_text = strip_tags($man_cls->name_extended("child"),"<b><i>");
					$cell = $table->getCell(1, 3);
					$cell->writeText($rtf_text, $arial12, $parNames);
					$rtf_text=strip_tags($man_cls->person_data("standard", $ancestor_array[$i]),"<b><i>");
					$rtf_text = substr($rtf_text,0,-1); // take off newline
					$cell->writeText($rtf_text, $arial12, $parNames);
				}
				else {
					// pdf NUMBER + NAME + DATA  NN PERSON

					//$pdf->pdf_ancestor_name($ancestor_number[$i],$person_manDb->pers_sexe,$man_cls->name_extended("child"));
					$pdf->pdf_ancestor_name($ancestor_number[$i],'',__('N.N.'));
					$pdfdetails= $man_cls->person_data("standard",$ancestor_array[$i]);
					if($pdfdetails) {
						$pdf->pdfdisplay($pdfdetails,"ancestor");
					}
					elseif ($ancestor_number[$i]>9999) {
						$pdf->Ln(8); // (number) was placed under previous number
								//  and there's no data so we have to move 1 line down for next person 
					}
					$temp=0;
					$temp=floor($ancestor_number[$i]%2);
					if($ancestor_number[$i]>1 AND $temp==1 AND $i+1<count($ancestor_array)) {
						// if we're not in first generation (one person)
						// and we are after writing the woman's details
						// and there is at least one person of another family to come in this generation
						// then place a divider line between the families in this generation
						$pdf->Cell(0,1,"",'B',1);
						$pdf->Ln(1);
					}
				}   
			}
		}	// loop per generation
		$generation++;
	}	// loop ancestor report


} // *** End of ancestor report

else{  // = ancestor chart, OR ancestor sheet OR PDF of ancestor sheet

	// The following is used for ancestor chart, ancestor sheet and ancestor sheet PDF (ASPDF)

	// person 01
	$personDb = $db_functions->get_person($family_id);
	$gedcomnumber[1]= $personDb->pers_gedcomnumber;
	$pers_famc[1]=$personDb->pers_famc;
	$sexe[1]=$personDb->pers_sexe;
	$parent_array[2]=''; $parent_array[3]='';
	if ($pers_famc[1]){
		$parentDb = $db_functions->get_family($pers_famc[1]);
		$parent_array[2]=$parentDb->fam_man; $parent_array[3]=$parentDb->fam_woman ;
		$marr_date_array[2]=$parentDb->fam_marr_date ; $marr_place_array[2]=$parentDb->fam_marr_place ;
	}
	// end of person 1

	// Loop to find person data
	$count_max = 64;
	if($hourglass===true) { $count_max = pow(2,$chosengenanc); }

	for ($counter = 2; $counter < $count_max; $counter++){
		$gedcomnumber[$counter]= '';
		$pers_famc[$counter]= '';
		$sexe[$counter]= '';
		if ($parent_array[$counter]){
			$personDb = $db_functions->get_person($parent_array[$counter]);
			$gedcomnumber[$counter]= $personDb->pers_gedcomnumber;
			$pers_famc[$counter]=$personDb->pers_famc;
			$sexe[$counter]=$personDb->pers_sexe;
		}

		$Vcounter=$counter*2; $Mcounter=$Vcounter+1;
		$parent_array[$Vcounter]=''; $parent_array[$Mcounter]='';
		$marr_date_array[$Vcounter]=''; $marr_place_array[$Vcounter]='';
		if ($pers_famc[$counter]){  
			$parentDb = $db_functions->get_family($pers_famc[$counter]);
			$parent_array[$Vcounter]=$parentDb->fam_man; $parent_array[$Mcounter]=$parentDb->fam_woman ;
			$marr_date_array[$Vcounter]=$parentDb->fam_marr_date ; $marr_place_array[$Vcounter]=$parentDb->fam_marr_place ;
		}
	}

	// *** Function to show data ***
	// box_appearance (large, medium, small, and some other boxes...)
	function ancestor_chart_person($id, $box_appearance){
		global $dbh, $db_functions, $tree_prefix_quoted, $humo_option, $user;
		global $marr_date_array, $marr_place_array;
		global $gedcomnumber, $language, $screen_mode, $dirmark1, $dirmark2;

		$hour_value=''; // if called from hourglass.php size of chart is given in box_appearance as "hour45" etc.
		if(strpos($box_appearance,"hour")!==false) { $hour_value=substr($box_appearance,4); }

		$text=''; $popup='';

		if ($gedcomnumber[$id]){ 
			@$personDb = $db_functions->get_person($gedcomnumber[$id]);
			$person_cls = New person_cls;
			$person_cls->construct($personDb);
			$pers_privacy=$person_cls->privacy;
			$name=$person_cls->person_name($personDb);

			if ($screen_mode=="ancestor_sheet" OR $language["dir"]=="rtl") {
				$name2=$name["name"];
			}
			else {
				//$name2=$name["short_firstname"];
				$name2=$name["name"];
			}
			$name2=$dirmark2.$name2.$name["colour_mark"].$dirmark2;

			// *** Replace pop-up icon by a text box ***
			$replacement_text='';
			if ($screen_mode=="ancestor_sheet") {  // *** Ancestor sheet: name bold, id not ***
				//$replacement_text.=$id.' <b>'.$name2.'</b>';
				$replacement_text.='<b>'.$name2.'</b>';
			}
			else {
				//$replacement_text.='<b>'.$id.'</b>';  // *** Ancestor number: id bold, name not ***
				$replacement_text.='<span class="anc_box_name">'.$name2.'</span>';
			}

			// >>>>> link to show rest of ancestor chart
			//if ($box_appearance=='small' AND isset($personDb->pers_gedcomnumber) AND $screen_mode!="ancestor_sheet"){
			if ($box_appearance=='small' AND isset($personDb->pers_gedcomnumber) AND $personDb->pers_famc AND $screen_mode!="ancestor_sheet"){
				$replacement_text.=' &gt;&gt;&gt;'.$dirmark1;
			}

			if ($pers_privacy){
				if($box_appearance!='ancestor_sheet_marr') {
					$replacement_text.='<br>'.__(' PRIVACY FILTER');  //Tekst privacy weergeven
				}
				else { 
					$replacement_text = __(' PRIVACY FILTER');
				}
			}
			else{
				if ($box_appearance!='small'){
					//if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
					if ($personDb->pers_birth_date){
						//$replacement_text.='<br>'.__('*').$dirmark1.' '.date_place($personDb->pers_birth_date,$personDb->pers_birth_place); }
						$replacement_text.='<br>'.__('*').$dirmark1.' '.date_place($personDb->pers_birth_date,''); }
					//elseif ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
					elseif ($personDb->pers_bapt_date){
						//$replacement_text.='<br>'.__('~').$dirmark1.' '.date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place); }
						$replacement_text.='<br>'.__('~').$dirmark1.' '.date_place($personDb->pers_bapt_date,''); }

					//if ($personDb->pers_death_date OR $personDb->pers_death_place){
					if ($personDb->pers_death_date){
						//$replacement_text.='<br>'.__('&#134;').$dirmark1.' '.date_place($personDb->pers_death_date,$personDb->pers_death_place); }
						$replacement_text.='<br>'.__('&#134;').$dirmark1.' '.date_place($personDb->pers_death_date,''); }
					//elseif ($personDb->pers_buried_date OR $personDb->pers_buried_place){
					elseif ($personDb->pers_buried_date){
						//$replacement_text.='<br>'.__('[]').$dirmark1.' '.date_place($personDb->pers_buried_date,$personDb->pers_buried_place); }
						$replacement_text.='<br>'.__('[]').$dirmark1.' '.date_place($personDb->pers_buried_date,''); }

					if ($box_appearance!='medium'){
						$marr_date=''; if (isset($marr_date_array[$id]) AND ($marr_date_array[$id]!='')){
							$marr_date=$marr_date_array[$id]; }
						$marr_place=''; if (isset($marr_place_array[$id]) AND ($marr_place_array[$id]!='')){
						$marr_place=$marr_place_array[$id]; }
						//if ($marr_date OR $marr_place){
						if ($marr_date){
							//$replacement_text.='<br>'.__('X').$dirmark1.' '.date_place($marr_date,$marr_place); }
							$replacement_text.='<br>'.__('X').$dirmark1.' '.date_place($marr_date,''); }
					}
					if ($box_appearance=='ancestor_sheet_marr'){
						$replacement_text='';
						$marr_date=''; if (isset($marr_date_array[$id]) AND ($marr_date_array[$id]!='')){
							$marr_date=$marr_date_array[$id]; }
						$marr_place=''; if (isset($marr_place_array[$id]) AND ($marr_place_array[$id]!='')){
							$marr_place=$marr_place_array[$id]; }
						//if ($marr_date OR $marr_place){
						if ($marr_date){
							//$replacement_text=__('X').$dirmark1.' '.date_place($marr_date,$marr_place); }
							$replacement_text=__('X').$dirmark1.' '.date_place($marr_date,''); }
							else $replacement_text=__('X'); // if no details in the row we don't want the row to collapse         
					}
					if ($box_appearance=='ancestor_header'){
						$replacement_text='';
						$replacement_text.=strip_tags($name2);
						$replacement_text.= $dirmark2;
					}
				}
			}

			if($hour_value != '') { // called from hourglass
				if($hour_value == '45') { $replacement_text = $name['name']; }
				elseif($hour_value == '40') { $replacement_text = '<span class="wordwrap" style="font-size:75%">'.$name['short_firstname'].'</span>'; }
				elseif($hour_value >20 AND $hour_value <40) { $replacement_text = $name['initials'];}
				elseif($hour_value <25) { $replacement_text = "&nbsp;";}
				// if full scale (50) then the default of this function will be used: name with details
			}

			$extra_popup_text='';
			$marr_date=''; if (isset($marr_date_array[$id]) AND ($marr_date_array[$id]!='')){
				$marr_date=$marr_date_array[$id]; }
			$marr_place=''; if (isset($marr_place_array[$id]) AND ($marr_place_array[$id]!='')){
				$marr_place=$marr_place_array[$id]; }
			if ($marr_date OR $marr_place){
				$extra_popup_text.='<br>'.__('X').$dirmark1.' '.date_place($marr_date,$marr_place);
			}

			// *** Show picture by person ***
			if ($box_appearance!='small' AND $box_appearance!='medium' AND (strpos($box_appearance,"hour")===false OR $box_appearance=="hour50")){
				// *** Show picture ***
				if (!$pers_privacy AND $user['group_pictures']=='j'){
					//  *** Path can be changed per family tree ***
					global $dataDb;
					$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
					$picture_qry=$db_functions->get_events_connect('person',$personDb->pers_gedcomnumber,'picture');
					// *** Only show 1st picture ***
					if (isset($picture_qry[0])){
						$pictureDb=$picture_qry[0];
						$picture=show_picture($tree_pict_path,$pictureDb->event_event,80,70);
						//$text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'">';
						$text.='<img src="'.$picture['path'].$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'">';
					}
				}
			}

			if ($box_appearance=='ancestor_sheet_marr' OR $box_appearance=='ancestor_header' ){ // cause in that case there is no link
				$text.= $replacement_text;
			}
			else{
				$text.=$person_cls->person_popup_menu($personDb,true,$replacement_text,$extra_popup_text);
			}

		}

		return $text."\n";
	}
	// *** End of function ancestor_chart_person ***

	// Specific code for ancestor chart:

	if($screen_mode!="ancestor_sheet" AND $screen_mode!= "ASPDF" AND $hourglass===false) {
		echo '<script type="text/javascript" src="include/jqueryui/js/html2canvas.js"></script>';
		echo '<script type="text/javascript" src="include/jqueryui/js/jquery.plugin.html2canvas.js"></script>';
		echo '<div style="text-align:center;">';
		echo '<br><input type="button" id="imgbutton" value="'.__('Get image of chart for printing (allow popup!)').'" onClick="showimg();">';
		echo '</div>';

		$divlen = 1000; 
		// width of the chart. for 6 generations 1000px is right
		// if we ever make the anc chart have optionally more generations, the width and length will have to be generated
		// as in dreport_descendant.php

		//following div gets width and length in imaging java function showimg() (at bottom) otherwise double scrollbars won't work.
		echo '<div id="png">';

		echo '
<style type="text/css">
		#doublescroll { position:relative; width:auto; height:1100px; overflow: auto; overflow-y: hidden; }
		#doublescroll p { margin: 0; padding: 1em; white-space: nowrap; }
</style>
';

	echo '<div id="doublescroll">';

	// *** First column name ***
	$left=10;
	$sexe_colour=''; $backgr_col = "#FFFFFF";
	//if ($sexe[1] == 'F'){ $sexe_colour=' ancestor_woman'; }
	//if ($sexe[1] == 'M'){ $sexe_colour=' ancestor_man'; }
	if ($sexe[1] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0"; }
	if ($sexe[1] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
	//echo '<div class="ancestor_name'.$sexe_colour.'" style="top: 520px; left: '.$left.'px; height: 80px; width:180px;';
	//echo '<div class="ancestor_name'.$sexe_colour.'" align="left" style="top: 520px; left: '.$left.'px; height: 80px; width:200px;';
	echo '<div class="ancestor_name'.$sexe_colour.'" align="left" style="background-color:'.$backgr_col.'; top: 520px; left: '.$left.'px; height: 80px; width:200px;';
	echo '">';
	echo ancestor_chart_person('1', 'large');
	echo '</div>';

	$left=50;
	$top=320;
	// *** Second column split ***
	echo '<div class="ancestor_split" style="top: '.$top.'px; left: '.$left.'px; height: 199px"></div>';
	echo '<div class="ancestor_split" style="top: '.($top+281).'px; left: '.$left.'px; height: 199px"></div>';
	// *** Second column names ***
	for ($i=1; $i<3; $i++){
		$sexe_colour=''; $backgr_col = "#FFFFFF";
		//if ($sexe[$i+1] == 'F'){ $sexe_colour=' ancestor_woman';  }
		//if ($sexe[$i+1] == 'M'){ $sexe_colour=' ancestor_man'; }
		if ($sexe[$i+1] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0"; }
		if ($sexe[$i+1] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="background-color:'.$backgr_col.'; top: '.(($top-520)+($i*480)).'px; left: '.($left+8).'px; height: 80px; width:200px;';
		echo '">';
		echo ancestor_chart_person($i+1, 'large');
		echo '</div>';
	}

	$left=80;
	$top=199;
	// *** Third column split ***
	echo '<div class="ancestor_split" style="top: '.$top.'px; left: '.($left+32).'px; height: 80px;"></div>';
	echo '<div class="ancestor_split" style="top: '.($top+162).'px; left: '.($left+32).'px; height: 80px;"></div>';
	echo '<div class="ancestor_split" style="top: '.($top+480).'px; left: '.($left+32).'px; height: 80px;"></div>';
	echo '<div class="ancestor_split" style="top: '.($top+642).'px; left: '.($left+32).'px; height: 80px;"></div>';
	// *** Third column names ***
	for ($i=1; $i<5; $i++){
		$sexe_colour=''; $backgr_col = "#FFFFFF";
		//if ($sexe[$i+3] == 'F'){ $sexe_colour=' ancestor_woman'; }
		//if ($sexe[$i+3] == 'M'){ $sexe_colour=' ancestor_man'; }
		if ($sexe[$i+3] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0";}
		if ($sexe[$i+3] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="background-color:'.$backgr_col.'; top: '.(($top-279)+($i*240)).'px; left: '.($left+40).'px; height: 80px; width:200px;';
		echo '">';
		echo ancestor_chart_person($i+3, 'large');
		echo '</div>';
	}

	$left=300;
	$top=-290;
	// *** Fourth column line ***
	for ($i=1; $i<3; $i++){
		echo '<div class="ancestor_line" style="top: '.($top+($i*485)).'px; left: '.($left+24).'px; height: 240px;"></div>';
	}
	// *** Fourth column split ***
	for ($i=1; $i<5; $i++){
		echo '<div class="ancestor_split" style="top: '.(($top+185)+($i*240)).'px; left: '.($left+32).'px; height: 120px;"></div>';
	}
	// *** Fourth column names ***
	for ($i=1; $i<9; $i++){
		$sexe_colour=''; $backgr_col = "#FFFFFF";
		//if ($sexe[$i+7] == 'F'){ $sexe_colour=' ancestor_woman'; }
		//if ($sexe[$i+7] == 'M'){ $sexe_colour=' ancestor_man'; }
		if ($sexe[$i+7] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0";}
		if ($sexe[$i+7] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="background-color:'.$backgr_col.'; top: '.(($top+265)+($i*120)).'px; left: '.($left+40).'px; height: 80px; width:200px;';
		echo '">';
		echo ancestor_chart_person($i+7, 'large');
		echo '</div>';
	}

	$left=520;
	$top=-110;
	// *** Fifth column line ***
	for ($i=1; $i<5; $i++){
		echo '<div class="ancestor_line" style="top: '.($top+($i*240)).'px; left: '.($left+24).'px; height: 120px;"></div>';
	}
	// *** Fifth column split ***
	for ($i=1; $i<9; $i++){
		echo '<div class="ancestor_split" style="top: '.(($top+90)+($i*120)).'px; left: '.($left+32).'px; height: 60px;"></div>';
	}
	// *** Fifth column names ***
	for ($i=1; $i<17; $i++){
		$sexe_colour=''; $backgr_col = "#FFFFFF";
		//if ($sexe[$i+15] == 'F'){ $sexe_colour=' ancestor_woman'; }
		//if ($sexe[$i+15] == 'M'){ $sexe_colour=' ancestor_man'; }
		if ($sexe[$i+15] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0";}
		if ($sexe[$i+15] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="background-color:'.$backgr_col.'; top: '.(($top+125)+($i*60)).'px; left: '.($left+40).'px; height: 50px; width:200px;';
		echo '">';
		echo ancestor_chart_person($i+15, 'medium');
		echo '</div>';
	}

	$left=740;
	$top=-20;
	// *** Last column line ***
	for ($i=1; $i<9; $i++){
		echo '<div class="ancestor_line" style="top: '.($top+($i*120)).'px; left: '.($left+24).'px; height: 60px;"></div>';
	}
	// *** Last column split ***
	for ($i=1; $i<17; $i++){
		echo '<div class="ancestor_split" style="top: '.(($top+45)+($i*60)).'px; left: '.($left+32).'px; height: 30px;"></div>';
	}
	// *** Last column names ***
	for ($i=1; $i<33; $i++){
		$sexe_colour=''; $backgr_col = "#FFFFFF";
		//if ($sexe[$i+31] == 'F'){ $sexe_colour=' ancestor_woman'; }
		//if ($sexe[$i+31] == 'M'){ $sexe_colour=' ancestor_man'; }
		if ($sexe[$i+31] == 'F'){ $sexe_colour=' ancestor_woman'; $backgr_col = "#FBDEC0"; }
		if ($sexe[$i+31] == 'M'){ $sexe_colour=' ancestor_man'; $backgr_col =  "#C0F9FC";}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="background-color:'.$backgr_col.'; top: '.(($top+66)+($i*30)).'px; left: '.($left+40).'px; height:16px; width:200px;';
		echo '">';
		echo ancestor_chart_person($i+31, 'small');
		echo '</div>';
	}
	echo '</div>';
echo '<div>';

	// YB:
	// before creating the image we want to hide unnecessary items such as the help link, the menu box etc
	// we also have to set the width and height of the "png" div (this can't be set before because then the double scrollbars won't work
	// after generating the image, all those items are returned to their  previous state....
	echo '<script type="text/javascript">';
	echo "
	function showimg() { 
		/*   document.getElementById('helppopup').style.visibility = 'hidden';
		document.getElementById('menubox').style.visibility = 'hidden'; */
 		document.getElementById('imgbutton').style.visibility = 'hidden';
		document.getElementById('png').style.width = '".$divlen."px';
		document.getElementById('png').style.height= 'auto';
		html2canvas( [ document.getElementById('png') ], {  
			onrendered: function( canvas ) {
				var img = canvas.toDataURL();
				/*   document.getElementById('helppopup').style.visibility = 'visible';
				document.getElementById('menubox').style.visibility = 'visible'; */
				document.getElementById('imgbutton').style.visibility = 'visible';
				document.getElementById('png').style.width = 'auto';
				document.getElementById('png').style.height= 'auto';
				var newWin = window.open();
				newWin.document.open();
				newWin.document.write('<!DOCTYPE html><head></head><body>".__('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ')."<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>".__('If you have a plotter you can use its software to print the image on one large sheet.')."<br><br><img src=\"' + img + '\"></body></html>');
				newWin.document.close();
				}
		});
	}
	";
	echo "</script>";

?>
	<script type="text/javascript">
		function DoubleScroll(element) {
			var scrollbar= document.createElement('div');
			scrollbar.appendChild(document.createElement('div'));
			scrollbar.style.overflow= 'auto';
			scrollbar.style.overflowY= 'hidden';
			scrollbar.firstChild.style.width= element.scrollWidth+'px';
			scrollbar.firstChild.style.paddingTop= '1px';
			scrollbar.firstChild.style.height= '20px';
			scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
			scrollbar.onscroll= function() {
				element.scrollLeft= scrollbar.scrollLeft;
			};
			element.onscroll= function() {
				scrollbar.scrollLeft= element.scrollLeft;
			};
			element.parentNode.insertBefore(scrollbar, element);                                 
		}

		DoubleScroll(document.getElementById('doublescroll'));
	</script>
<?php

	}   // end of ancestor CHART code

	// Specific code for ancestor SHEET:

	if($screen_mode=="ancestor_sheet" AND $screen_mode != "ASPDF") {

		// print names and details for each row in the table
		function kwname($start,$end,$increment,$fontclass,$colspan,$type) {
			echo '<tr>';
			for($x=$start;$x<$end;$x+=$increment) {
				if($colspan>1) {
					//echo '<td class="'.$fontclass.'" colspan='.$colspan.'>';
					echo '<td colspan='.$colspan.'>';
				}
				else{
					//echo '<td class="'.$fontclass.'">';
					echo '<td>';
				}
				$kwpers=ancestor_chart_person($x,$type);
				if($kwpers!='') {
					echo $kwpers;
				}
				else {   // if we don't do this IE7 wil not print borders of cells
					echo '&nbsp;';
				}
				echo '</td>';
			}
			echo '</tr>';
		}

		// check if there is anyone in a generation so no empty and collapsed rows will be shown
		function check_gen($start,$end) {
			global $gedcomnumber;
			$is_gen=0;
			for($i=$start;$i<$end;$i++) {
				if(isset($gedcomnumber[$i]) AND $gedcomnumber[$i]!='') {
					$is_gen=1;
				}
			}
			return $is_gen;
		}

		echo '<table class="humo ancestor_sheet">';
		echo '<tr><th class="ancestor_head" colspan="8">';  // adjusted for IE7
		echo __('Ancestor sheet').__(' of ').ancestor_chart_person(1,"ancestor_header");  

			//if($language["dir"]!="rtl") {
			if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl" AND $language["name"]!="简体中文") {
				// Show pdf button
				echo '&nbsp;&nbsp; <form method="POST" action="'.$uri_path.'report_ancestor.php?show_sources=1" style="display : inline;">';
				echo '<input type="hidden" name="id" value="'.$family_id.'">';
				echo '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
				echo '<input type="hidden" name="screen_mode" value="ASPDF">';
				echo '<input class="fonts" type="Submit" name="submit" value="PDF Report">';
				echo '</form>';
			}
		
		echo '</th></tr>';

		$gen=0; $gen=check_gen(16,32); if($gen==1) {
			kwname(16,32,2,"kw-small",1,"medium");
			kwname(16,32,2,"kw-small",1,"ancestor_sheet_marr");
			kwname(17,33,2,"kw-small",1,"medium");
			echo '<tr><td colspan=8 class="ancestor_devider">&nbsp;</td></tr>';  // adjusted for IE7
		}
		$gen=0; $gen=check_gen(8,16); if($gen==1) {
			kwname(8,16,1,"kw-bigger",1,"medium");
			kwname(8,16,2,"kw-small",2,"ancestor_sheet_marr");
		}
		$gen=0; $gen=check_gen(4,8); if($gen==1) {
			kwname(4,8,1,"kw-medium",2,"medium");
			kwname(4,8,2,"kw-small",4,"ancestor_sheet_marr");
		}
		kwname(2,4,1,"kw-big",4,"medium");
		kwname(2,4,2,"kw-small",8,"ancestor_sheet_marr");
		kwname(1,2,1,"kw-big",8,"medium");
		echo '</table>';
		echo '<br><div class="ancestor_legend">';
			echo '<b>'.__('Legend').'</b><br>';
			echo __('*').'  '.__('born').', '.__('&#134;').'  '.__('died').', '.__('X').'  '.__('married');
			echo '<br>';
			$date=date("d M Y - H:i");
			printf(__('Generated with %s on %s'),'HuMo-genealogy',$date);
		echo '</div>';
	}  // end of ancestor SHEET code

	// Specific code for ancestor sheet PDF:

	if($screen_mode=="ASPDF") {  

		// this function parses the input string to see how many lines it would take in the ancestor sheet box
		// it forces linebreaks when max nr of chars is encountered
		// if given a value for $trunc (the max nr of lines) it will truncate the string when max lines are reached
		// $str = the input string
		// $width = width of box (in characters)

		function parse_line($str,$width,$trunc,$bold="") {  
			global $pdf;
			//$result_array = $array();
			$count=1; //counts lines;
			$pos=0; // checks position of blank
			if($bold=="B") $width -= 5;
			$w=$width; 
			$nl=0;
			for($x=0;$x<strlen($str);$x++) {   
				if($str[$x]==' ') { $pos = $x;}
				if(ceil($pdf->GetStringWidth(substr($str,$nl,($x-$nl)+1))) >=$w) {  
					$count++;
				 	if($trunc!=0 AND $count>$trunc) { 
						$result_array[0] = $trunc;
						$result_array[1] = substr($str,0,$x-1);
						return $result_array;
					}     
					$str[$pos]="\n"; 
					$x = $pos+1; 
					$nl = $pos+1;    
				}    
			}  
			$result_array[0] = $count;
			$result_array[1] = $str;
			return $result_array;
		}


		// the function data_array fills a multi dimensional array used later to display the PDF ancestor sheet
		// first dimension contains $id, second dimension members [0-5] contain: name, birth date and place, death date and place, nr of lines
		$data_array = array(); $base_sex="M";

		// if people changed the death sign from a cross to something else it will be shown as ~ (also shown in legend)
		// (since the PDF font does not support most other signs such as infinity...) 
		if(__('&#134;') == '&#134;' OR __('&#134;') == "†") { $dsign = "†"; }
		else $dsign = "~";

		function data_array($id,$width,$height) {     
			global $dbh, $db_functions, $tree_prefix_quoted, $data_array, $gedcomnumber, $dsign;   

			if (isset($gedcomnumber[$id]) AND $gedcomnumber[$id]!=""){
				@$personDb = $db_functions->get_person($gedcomnumber[$id]);
				$person_cls = New person_cls;
				$person_cls->construct($personDb);
				$pers_privacy=$person_cls->privacy;	
				// get length of original name, birth, death strings
				$names = $person_cls->person_name($personDb);
				$name = $names["name"];
				//if($name != '') {
				if(preg_match('/[A-Za-z]/',$name)) {
					$result = parse_line($name,$width,0,"B");
					$name_len = $result[0];
					$name = $result[1];
				}
				else {  
					$name = __('N.N.');
					$name_len = 1;
				}

				$birth=''; $space='';

				if($personDb->pers_birth_date != '' OR $personDb->pers_birth_place != '') {
					if($pers_privacy) { 
						$birth = __("PRIVACY FILTER"); 
						$birth_len = 1;
					}
					else{
						if($personDb->pers_birth_date != '') { $space=' '; }
						$birth = __('*').' '.$personDb->pers_birth_date.$space.$personDb->pers_birth_place;
						$result = parse_line($birth,$width,0);
						$birth_len = $result[0];
						$birth = $result[1];
					}
				}
				else {
					$birth_len = 0;
				}
				$death = ''; $space='';
				if($personDb->pers_death_date != '' OR $personDb->pers_death_place != '') {
					if($pers_privacy) { 
						if($birth != __("PRIVACY FILTER")) {
							$death = __("PRIVACY FILTER");
							$death_len = 1;
						}
						else {
							$death_len = 0;
						}
					}
					else{
						if($personDb->pers_death_date != '') { $space=' '; }
						$death = $dsign.' '.$personDb->pers_death_date.$space.$personDb->pers_death_place;
						$result = parse_line($death,$width,0);
						$death_len = $result[0];
						$death = $result[1];
					}
				}
				else {
					$death_len = 0;
				}
  
				// now start adjusting the strings to make sure no more than $height lines will be displayed in box
				// name gets priority if extra space is available (= if birth or death take up less than 2 lines)
				if($name_len < 3) {
					$data_array[$id][0]= $name;
					$data_array[$id][3]= $name_len;
				}
				else {
					$rest = min(2,$birth_len) + min(2,$death_len);
					if($name_len <= $height - $rest) {
						$data_array[$id][0]=$name;
						$data_array[$id][3]=$name_len;
					}
					else { // too long: try with initials
						$result = parse_line($names['short_firstname'],$width,0);
						$name_len = $result[0];
						if($name_len <= $height - $rest) {
							$data_array[$id][0]=$result[1];
							$data_array[$id][3]=$name_len;
						}
						else { // still too long: truncate
							$result = parse_line($names['short_firstname'],$width,$height - $rest);
							$name_len = $result[0];
							$data_array[$id][0]=$result[1];
							$data_array[$id][3]=$result[0];
						}
					}
				}  
				
				if($birth_len < 3) {
					$data_array[$id][1]= $birth;
					$data_array[$id][4]= $birth_len;
				}
				else {
					$rest = $name_len + min(2,$death_len);
					if($birth_len <= $height - $rest) {
						$data_array[$id][1]=$birth;
						$data_array[$id][4]=$birth_len;
					}
					else { // too long: truncate
						$result = parse_line(str_replace("\n"," ",$birth),$width,$height - $rest);
						$birth_len = $result[0];
						$data_array[$id][1]=$result[1];
						$data_array[$id][4]=$result[0];
					}
				}  

				if($death_len < 3) {
					$data_array[$id][2]= $death;
					$data_array[$id][5]= $death_len;
				}
				else {
					$rest = $name_len + $birth_len;
					if($death_len <= $height - $rest) {
						$data_array[$id][2]=$death;
						$data_array[$id][5]=$death_len;
					}
					else { // too long: truncate
						$result = parse_line(str_replace("\n"," ",$death),$width,$height - $rest);
						$data_array[$id][2]=$result[1];
						$data_array[$id][5]=$result[0];
					}
				}  

			}
			else {
				$data_array[$id][0] = __('N.N.');
				$data_array[$id][1] = '';
				$data_array[$id][2] = '';
				$data_array[$id][3] = 1;
				$data_array[$id][4] = 0;
				$data_array[$id][5] = 0;
			}     
		}

		function place_cells($type,$begin,$end,$increment,$maxchar,$numrows,$cellwidth) {
			global $dbh, $db_functions, $tree_prefix_quoted, $pdf, $data_array,$posy,$posx,$marr_date_array, $marr_place_array, $sexe, $gedcomnumber;

			$pdf->SetLeftMargin(16);
			$marg = 16;
			for($m=$begin;$m<=$end;$m+=$increment) {
				if($type=="pers") { // person's name & details
					data_array($m,$maxchar,$numrows);
					$pdf->SetFont('Arial','B',8);
					if($m%2==0 OR ($m==1 AND $sexe[$m]=="M")) { // male
						$pdf->SetFillColor(191,239,255);
					}
					else  { // female
						$pdf->SetFillColor(255,228,225);
					}
					$pdf->MultiCell($cellwidth,4,$data_array[$m][0],"LTR","C",true);
					$marg += $cellwidth; 
					$pdf->SetFont('Arial','',8);
					$nstring=''; $used = $data_array[$m][3]+$data_array[$m][4]+$data_array[$m][5];
				}
				else {  // marr date & place
					$space='';
					if($marr_date_array[$m]!='') { $space=' '; }
					if($gedcomnumber[$m]!='') {
						@$personDb = $db_functions->get_person($gedcomnumber[$m]);
						$person_cls = New person_cls;
						$person_cls->construct($personDb);
						$pers_privacy=$person_cls->privacy;
					}
					else { $pers_privacy = false; }
					if($gedcomnumber[$m+1]!='') {
						@$womanDb = $db_functions->get_person($gedcomnumber[$m+1]);
						$woman_cls = New person_cls;
						$woman_cls->construct($womanDb);
						$woman_privacy=$person_cls->privacy;
					}
					else { $woman_privacy = false; }

					if($pers_privacy OR $woman_privacy) {
						$marr = __('PRIVACY FILTER');
					}
					else {
						$marr = __('X').' '.$marr_date_array[$m].$space.$marr_place_array[$m];
					}
					$result = parse_line($marr,$maxchar,$numrows);
					$marg += $cellwidth; 
					$nstring=''; $used = $result[0];
				}
				for($x=1;$x<= ($numrows-$used); $x++) {
					$nstring .= "\n"." ";
				}	
				if($type=="pers") {
					$breakln=''; if($data_array[$m][1]!='' AND $data_array[$m][2]!='') { $breakln = "\n"; }
					if($data_array[$m][4]==0 AND $data_array[$m][5]==0) { $nstring=substr($nstring,0,strlen($nstring)-1); }
					$pdf->SetFont('Arial','',8);
					$pdf->MultiCell($cellwidth,4,$data_array[$m][1].$breakln.$data_array[$m][2].$nstring,"LRB","C",true);
				}
				else {
					$pdf->SetFont('Arial','I',8);
					$pdf->MultiCell($cellwidth,4,$result[1].$nstring,"LR","C",false);
				}
				if($m < $end) {
					$pdf->SetLeftMargin($marg);  
					$pdf->SetY($posy);
				}
			}
			$pdf->SetX($posx);
			$posy = $pdf->GetY();
		}

		//initialize pdf generation
		@$persDb = $db_functions->get_person($family_id);
		// *** Use person class ***
		$pers_cls = New person_cls;
		$pers_cls->construct($persDb);
		$name=$pers_cls->person_name($persDb);
		$title=pdf_convert(__('Ancestor sheet').__(' of ').$name["standard_name"]);

		$pdf=new PDF();
		$pdf->SetTitle($title);
		$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
		$pdf->SetTopMargin(4);
		$pdf->SetAutoPageBreak(false);
		//$pdf->SetLineWidth(3);
		//$pdf->AddPage();
		$pdf->AddPage("L");	
		$pdf->SetLeftMargin(16);
		$pdf->SetRightMargin(16);
		$pdf->SetFont('Arial','B',12);
		$pdf->Ln(2);
		$name=$pers_cls->person_name($persDb);
		$pdf->MultiCell(0,10,__('Ancestor sheet').__(' of ').$name["standard_name"],0,'C');
		$pdf->Ln(2);
		$pdf->SetFont('Arial','',8);
 
		// Output the cells:
		$posy = $pdf->GetY();
		$posx = $pdf->GetX();

		// for each generation check if there is anyone, otherwise don't display those rows

		$exist=false; for($x=16; $x<32; $x++) { if ($gedcomnumber[$x]!='') $exist=true; }
		if($exist==true) {
			place_cells("pers",16,30,2,32,8,33);
			place_cells("marr",16,30,2,32,3,33);
			place_cells("pers",17,31,2,32,8,33);
			$pdf->MultiCell(264,3," ",0,"C"); $pdf->SetLeftMargin(16); $pdf->SetX(16); $posy+=4; $pdf->SetY($posy);
			$place=33; for($x=1;$x<9;$x++) { $pdf->Image("images/arrowdown.jpg",$place,94.5,2); $place+=33;}
		}
		$exist1=false; for($x=8; $x<16; $x++) { if ($gedcomnumber[$x]!='') $exist1=true; }
		if($exist==true OR $exist1==true) {
			place_cells("pers",8,15,1,32,8,33);
			place_cells("marr",8,14,2,65,2,66);
		}
		$exist2=false; for($x=4; $x<8; $x++) { if ($gedcomnumber[$x]!='') $exist2=true; }
		if($exist==true OR $exist1==true OR $exist2==true) {
 			place_cells("pers",4,7,1,65,4,66);
 			place_cells("marr",4,6,2,131,2,132);
		}
		place_cells("pers",2,3,1,131,3,132);
		place_cells("marr",2,2,2,263,2,264);
		place_cells("pers",1,1,1,263,3,264);

		// Output the legend:
		$legend = __('Legend').':  '.__('*').' ('.__('born').'),  '.$dsign.' ('.__('died').'),  '.__('X').' ('.__('marriage').')';
		$pdf->MultiCell(80,5,$legend,0,"L",false);
		$pdf->Cell(13,3," ",0,0);
		//$pdf->SetFillColor(255,228,225); $pdf->Cell(20,3,__('female'),1,0,"C",true);
		$pdf->SetFillColor(255,228,225); $pdf->Cell(20,3,pdf_convert(__('female')),1,0,"C",true);
		$pdf->Cell(5,3," ",0,0);
		//$pdf->SetFillColor(191,239,255); $pdf->Cell(20,3,__('male'),1,0,"C",true);
		$pdf->SetFillColor(191,239,255); $pdf->Cell(20,3,pdf_convert(__('male')),1,0,"C",true);

	} // end of ancestor sheet PDF code
}



// Code for ancestor report PDF -- list appendix of sources
if($screen_mode=="PDF" AND !empty($pdf_source) AND ($source_presentation=='footnote' OR $user['group_sources']=='j') ) {
	include_once(CMS_ROOTPATH."source.php");
	$pdf->AddPage(); // appendix on new page
	$pdf->SetFont('Arial',"B",14);
	$pdf->Write(8,__('Sources')."\n\n");
	$pdf->SetFont('Arial','',10);
	// the $pdf_source array is set in show_sources.php with sourcenr as key and value if a linked source is given
	$count=0;

	foreach($pdf_source as $key => $value) {
		$count++;
		if(isset($pdf_source[$key])) {
			$pdf->SetLink($pdf_footnotes[$count-1],-1);
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(6,$count.". ");
			if($user['group_sources']=='j') {
				source_display($pdf_source[$key]);  // function source_display from source.php, called with source nr.
			}
			elseif ($user['group_sources']=='t') {
				$db_functions->get_source($pdf_source[$key]);
				if ($sourceDb->source_title){
					$pdf->SetFont('Arial','B',10);
					$pdf->Write(6,__('Title:')." ");
					$pdf->SetFont('Arial','',10);
					$txt = ' '.trim($sourceDb->source_title);
					if ($sourceDb->source_date or $sourceDb->source_place){ $txt.=" ".date_place($sourceDb->source_date, $sourceDb->source_place); }
					$pdf->Write(6,$txt."\n");
				}
			}
			$pdf->Write(2,"\n");
			$pdf->SetDrawColor(200);  // grey line
			$pdf->Cell(0,2," ",'B',1);
			$pdf->Write(4,"\n");
		}
	}
	unset($value);
}

// Finishing code for ancestor report
if($screen_mode=='') {
	echo '</table>';
	// *** If source footnotes are selected, show them here ***
	if (isset($_SESSION['save_source_presentation']) AND $_SESSION['save_source_presentation']=='footnote'){
		echo show_sources_footnotes();
	}
}

if($hourglass===false) { 
	// Finishing code for ancestor chart and ancestor report
	if($screen_mode != 'PDF' AND $screen_mode != "ASPDF" AND $screen_mode != 'RTF') {
		include_once(CMS_ROOTPATH."footer.php");
	}

	elseif($screen_mode=='RTF') { // initialize rtf generation
		// *** Save rtf document to file ***
		$rtf->save($file_name);

		echo '<br><br><a href="'.$file_name.'">'.__('Download RTF report.').'</a>';
		echo '<br><br>'.__('TIP: Don\'t use Wordpad to open this file (the lay-out will be wrong!). It\'s better to use a text processor like Word or OpenOffice Writer.');

		$text='<br><br><form method="POST" action="'.$uri_path.'report_ancestor.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$family_id.'" style="display : inline;">';

		echo '<input type="hidden" name="screen_mode" value="">';

		$text.='<input class="fonts" type="Submit" name="submit" value="'.__('Back').'">';
		$text.='</form> ';
		echo $text;
	}

	// Finishing code for ancestor report PDF and ancestor sheet PDF (ASPDF)
	else {
		$pdf->Output($title.".pdf","I");
	}
}
?>