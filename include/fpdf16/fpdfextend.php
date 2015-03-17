<?php
class PDF extends FPDF{

//***********************************************************************************************
// EXTRA FUNCTIONS FOR HUMO-GEN BY YOSSI BECK : pdfdisplay() , displayrel() , writename(),  pdf_ancestor_name() and adjusted Header()
//************************************************************************************************

//**********************************************************************************************
// function pdfdisplay() to display details of person from array returned by person_cls.php
//
// it parses the values and places them as needed, including pictures and their text,  links to parents
// it also places  "Born:"  "Died:" etc before their text (even though in the array they come after the text)
//**********************************************************************************************

function pdfdisplay($templ_personing,$person_kind) {
	global $pdf, $language, $gen_lus;
	global $romnr, $romannr, $parentchild, $parlink;
	global $indent, $child_indent;
	global $pdf_footnotes, $pdf_count_notes, $user;
	$largest_height=0;
	$pic=array();

	$picarray=array();
	$numpics=0;

	$tallestpic=0; $tallesttext=0;

	if($person_kind !="child") {
		$font=12;  $type='';
	}
	else {
		$font=11; $type='';
	}

	$source_presentation='title';
	if (isset($_SESSION['save_source_presentation'])){
		$source_presentation=$_SESSION['save_source_presentation'];
	}

	// check if we have first occurance of birth, death etc. data, so we add "Born", "Died", etc.
	$first=0; $own_code=0; $born=0; $bapt=0; $dead=0; $buri=0; $prof=0; $address=0;

	foreach ($templ_personing as $key => $value) {
		$pdf->SetFont('Arial',$type,$font);

		if(strpos($key,"pic_path")!==false) {
			if(strpos($value,".jpeg")!==false OR strpos($value,".jpg")!==false OR strpos($value,".gif")!==false OR strpos($value,".png")!==false) {
				if(is_file($value)) {
					if($numpics > 2) {continue;}  // no more than 3 pics
					$presentpic=intval(substr($key,8));   //get the pic nr to compare later with txt nr
					$picarray[$numpics][0]=$value;
					$size=getimagesize($value);
					$height=$size[1];
					$width=$size[0];
					if($width > 180) {  //narrow and wide thumbs should not get height 120px - they will be far too long
						$height*= 180/$width;
						$width=180;
					}
					//if($height > $tallestpic) { $tallestpic=$height; }
					$picarray[$numpics][1]=$width/3.87;  // turn px into mm for pdf
					$picarray[$numpics][4]=$height/3.87; // turn px into mm for pdf
					if($picarray[$numpics][4] > $tallestpic) { $tallestpic = $picarray[$numpics][4]; }
					$numpics++;
				}
			}
			continue;
		}

		if(strpos($key,"pic_text")!==false) {
			if(isset($presentpic) AND $presentpic==intval(substr($key,8))) {
				$picarray[$numpics-1][2]=$value;
				if(isset($picarray[$numpics-1][2])) {
					$textlines=ceil(strlen($value)/30);
					$totalheight=($textlines*5) + ($picarray[$numpics-1][4]);
					if( $totalheight > $tallestpic) { $tallestpic=$totalheight; }
				}
			}
			continue;
		}

		// *** Skip showing of special flags ***
		if(strpos($key,"flag_buri")!==false) continue;
		if(strpos($key,"flag_prof")!==false) continue;
		if(strpos($key,"flag_address")!==false) continue;

		if(!$own_code) {
			if(strpos($key,"own_code")!==false) {
				$pdf->SetFont('Arial','B',$font);
				if(!$first) { $temp=__('Own code').': '; }
					else $temp=', '.lcfirst(__('Own code')).': '; $first=1;
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$own_code=1;
			}
		}

		if(!$born) {
			if(strpos($key,"born")!==false) {
				//$value=", ".__('born').' '.$value;
				$pdf->SetFont('Arial','B',$font);
				if(!$first) { $temp=ucfirst(__('born')).' '; }
					else $temp=', '.__('born').' '; $first=1;
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$born=1;
			}
		}
		if(!$bapt) {
			if(strpos($key,"bapt")!==false) {
				//$value=", ".__('baptised').' '.$value;
				$pdf->SetFont('Arial','B',$font);
				if(!$first) { $temp=ucfirst(__('baptised')).' '; }
					else $temp=', '.__('baptised').' '; $first=1;
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$bapt=1;
			}
		}
		if(!$dead) {
			if(strpos($key,"dead")!==false) {
				//$value=", ".__('died').' '.$value;
				$pdf->SetFont('Arial','B',$font);
				if(!$first) { $temp=ucfirst(__('died')).' '; }
					else $temp=', '.__('died').' '; $first=1;
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$dead=1;
			}
		}
		if(!$buri) {
			if(strpos($key,"buri")!==false) {
				if($templ_personing["flag_buri"]==1) {
					//$value=", ".__('crem.').' '.$value;
					$pdf->SetFont('Arial','B',$font);
					if(!$first) { $temp=ucfirst(__('cremation')).' '; }
						else $temp=', '.__('cremation').' '; $first=1;
					$pdf->Write(6,$temp);
					$pdf->SetFont('Arial','',$font);
				}
				else {
					//$value=", ".__('buried').' '.$value;
					$pdf->SetFont('Arial','B',$font);
					if(!$first) { $temp=ucfirst(__('buried')).' '; }
						else $temp=', '.__('buried').' '; $first=1;
					$pdf->Write(6,$temp);
					$pdf->SetFont('Arial','',$font);
				}
			$buri=1;
			}
		}
		//if(!$first AND $value!='') {  
		//	if (substr($value,0,2)==', '){ $value=ucfirst(substr($value,2)); }
		//	else {ucfirst($value); }
		//	$first=1;
		//}

		if(!$prof){
			if(strpos($key,"prof")!==false) {
				if ($templ_personing["flag_prof"]==1)
					$occupation=ucfirst(__('occupations'));
				else
					$occupation=ucfirst(__('occupation'));
				$pdf->SetFont('Arial','B',$font);
				$temp=$occupation.': ';
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$prof=1;
			}
		}

		if(!$address){
			if(strpos($key,"address")!==false) {
				if ($templ_personing["flag_address"]==1)
					$residence=ucfirst(__('residences'));
				else
					$residence=ucfirst(__('residence'));
				$pdf->SetFont('Arial','B',$font);
				$temp=$residence.': ';
				$pdf->Write(6,$temp);
				$pdf->SetFont('Arial','',$font);
				$address=1;
			}
		}

		$value=html_entity_decode($value);

		if(strpos($key,"text")!==false) {  $pdf->SetFont('Arial','I',$font-1); }

		if(strpos($key,"source")!==false OR strpos($key,"witn")!==false) {
			$pdf->SetFont('Times','',$font);
		}

		if(strpos($key,"parent")!==false) {
			if($person_kind=="parent1" AND $key=="parents" AND $gen_lus!=0) {
				$pdf->SetTextColor(28,28,255);
				$pdf->SetFont('Arial','U',$font);
				$temp=$parentchild[$romannr];
				$pdf->Write(6,$value,$parlink[$temp]);
				$pdf->SetTextColor(0);
			}
			elseif($key=="parents"){
				$pdf->SetFont('Arial','B',$font);
				$pdf->Write(6,$value);
			}
			else {
				$pdf->SetFont('Arial','',$font);
				$pdf->Write(6,$value);
			}
		}
		else {
			if($person_kind=="child") {

				// pic with child
				if(strpos($key,"got_pics")!==false) {
					$keepY=$pdf->GetY()+7;  if(($keepY + $tallestpic) > 280) {$pdf->AddPage(); $keepY=20; }
					$keepX=$pdf->GetX();
					if(isset($picarray[0][0])) {  // we got at least 1 pic
						$pic_indent=34; $pictext_indent=34; $maxw = 180/3.87;
						for($i=0; $i <3 ; $i++) {
							if(isset($picarray[$i][0])) {
								$pic_indent += (($maxw - $picarray[$i][1]) / 2);
								$pdf->Image($picarray[$i][0],$pic_indent,$keepY,$picarray[$i][1] );
								$pic_indent = $pictext_indent + $maxw + 5;
								if(isset($picarray[$i][2])) {
									$pdf->SetFont('Arial','',8);
									$pdf->SetXY($pictext_indent,$keepY+$picarray[$i][4]+1);
									$pdf->MultiCell($maxw,4,$picarray[$i][2],0,'C');
								}
								$pictext_indent += $maxw + 5;
							}
						}
						$pdf->SetXY($keepX,$keepY+$tallestpic-7);
					}
				}
				// source link with child
				elseif(strpos($key,"source")!==false AND $value != ''){   // make source link to end of document
					$pdf->SetLeftMargin($child_indent);
					$pdf->SetFont('Times','',$font);
					$pdf->SetTextColor(28,28,255);
					if($source_presentation=='footnote') {  // "source 1" as link to list at end of doc
						$multitext = explode('~',$value);
						for($i=0;$i<count($multitext);$i++) {
							$len = strlen(__('Source')) + 3;  // 'space','(','$lang[...]','space'
							$num = substr($multitext[$i],$len,-1); // -1: take ) off the end
							$ofs = $num - 1; // offset starts with 0
							if($ofs >= 0) {  // is footnote to source from global source list
								$pdf->SetTextColor(28,28,255);
								$pdf->subWrite(6,$multitext[$i],$pdf_footnotes[$ofs],9,4);
							}
							else { // "manual" source list as regular non-clickable text
								$pdf->SetTextColor(0);
								$pdf->Write(6,$multitext[$i]);
							}
						}
					}
					elseif($user['group_sources']!='n')  {  // source title as link to list at end of doc
						// with multiple sources pdf string looks like:  ,firstsource!!12~,secondsource!!4~,thirdsource!!6
						$multitext = explode('~',$value); // each key looks like: ,somesource!!34
						for($i=0;$i<count($multitext);$i++) {
							$pos = strpos($multitext[$i],'!!');
							//if($user['group_sources']=='j' AND $pos) {
							if($pos) { //source title as link to list at bottom
								if($user['group_sources']=='j') { $pdf->SetTextColor(28,28,255); }
								else { $pdf->SetTextColor(0); }
								$ofs = substr($multitext[$i],$pos+2)-1;
								$txt = substr($multitext[$i],0,$pos); // take off the !!2 source number at end
								$pdf->Write(6,$txt,$pdf_footnotes[$ofs]);
							}
							else {  // source title as plain text
								$pdf->SetTextColor(0);
								$pdf->Write(6,$multitext[$i]);
							}
						}
						if($pos) {$pdf->Write(6,'. ');	}
					}
					/*
					else {
						$pdf->SetTextColor(0);
						$pdf->Write(6,$value);
					}
					*/
					$pdf->SetTextColor(0);
					$pdf->SetLeftMargin($indent);
				}

				else {
					$pdf->SetLeftMargin($child_indent);
					$pdf->Write(6,$value);
					$pdf->SetLeftMargin($indent);
				}
			}

			elseif($person_kind=="ancestor")  {

				if(strpos($key,"got_pics")!==false) {
					$keepY=$pdf->GetY()+7;  if(($keepY + $tallestpic) > 280) {$pdf->AddPage(); $keepY=20; }
					$keepX=$pdf->GetX();
					if(isset($picarray[0][0])) {  // we got at least 1 pic
						$pic_indent=35; $pictext_indent=35; $maxw = 180/3.87;
						for($i=0; $i <3 ; $i++) {
							if(isset($picarray[$i][0])) {
								$pic_indent += (($maxw - $picarray[$i][1]) / 2);
								$pdf->Image($picarray[$i][0],$pic_indent,$keepY,$picarray[$i][1] );
								$pic_indent = $pictext_indent + $maxw + 5;
								if(isset($picarray[$i][2])) {
									$pdf->SetFont('Arial','',8);
									$pdf->SetXY($pictext_indent,$keepY+$picarray[$i][4]+1);
									$pdf->MultiCell($maxw,4,$picarray[$i][2],0,'C');
								}
								$pictext_indent += $maxw + 5;
							}
						}
						$pdf->SetXY($keepX,$keepY+$tallestpic-7);
					}
				}
				// source link with ancestor
				elseif(strpos($key,"source")!==false AND $value != ''){   // make source link to end of document
					$pdf->SetLeftMargin(38);
					$pdf->SetFont('Times','',$font);
					$pdf->SetTextColor(28,28,255);
					if($source_presentation=='footnote') {  // "source 1" as link to list at end of doc
						$multitext = explode('~',$value);
						for($i=0;$i<count($multitext);$i++) {
							$len = strlen(__('Source')) + 3;  // 'space','(','$lang[...]','space'
							$num = substr($multitext[$i],$len,-1); // -1: take ) off the end
							$ofs = $num - 1; // offset starts with 0
							if($ofs >= 0) {  // is footnote to source from global source list
								$pdf->SetTextColor(28,28,255);
								$pdf->subWrite(6,$multitext[$i],$pdf_footnotes[$ofs],9,4);
							}
							else { // "manual" source list as regular non-clickable text
								$pdf->SetTextColor(0);
								$pdf->Write(6,$multitext[$i]);
							}
						}
					}
					elseif($user['group_sources']!='n')  {  // source title as link to list at end of doc
						// with multiple sources pdf string looks like:  ,firstsource!!12~,secondsource!!4~,thirdsource!!6
						$multitext = explode('~',$value); // each key looks like: ,somesource!!34
						for($i=0;$i<count($multitext);$i++) {
							$pos = strpos($multitext[$i],'!!');
							//if($user['group_sources']=='j' AND $pos) {
							if($pos) { //source title as link to list at bottom
								if($user['group_sources']=='j') { $pdf->SetTextColor(28,28,255); }
								else { $pdf->SetTextColor(0); }
								$ofs = substr($multitext[$i],$pos+2)-1;
								$txt = substr($multitext[$i],0,$pos); // take off the !!2 source number at end
								$pdf->Write(6,$txt,$pdf_footnotes[$ofs]);
							}
							else {  // source title as plain text
								$pdf->SetTextColor(0);
								$pdf->Write(6,$multitext[$i]);
							}
						}
						if($pos) {$pdf->Write(6,'. ');	}
					}
					/*
					else {
						$pdf->SetTextColor(0);
						$pdf->Write(6,$value);
					}
					*/
					$pdf->SetTextColor(0);
					$pdf->SetLeftMargin(10);
				}
				else {
					$pdf->SetLeftMargin(38);
					$pdf->Write(6,$value);
					$pdf->SetLeftMargin(10);
				}
			}

			elseif(strpos($key,"got_pics")!==false) {
				$keepY=$pdf->GetY()+7;  if(($keepY + $tallestpic) > 280) {$pdf->AddPage(); $keepY=20; }
				$keepX=$pdf->GetX();
				if(isset($picarray[0][0])) {  // we got at least 1 pic
					$pic_indent=28; $pictext_indent=28; $maxw = 180/3.87;
					for($i=0; $i <3 ; $i++) {
						if(isset($picarray[$i][0])) {
							$pic_indent += (($maxw - $picarray[$i][1]) / 2);
							$pdf->Image($picarray[$i][0],$pic_indent,$keepY,$picarray[$i][1] );
							$pic_indent = $pictext_indent + $maxw + 5;
							if(isset($picarray[$i][2])) {
								$pdf->SetFont('Arial','',8);
								$pdf->SetXY($pictext_indent,$keepY+$picarray[$i][4]+1);
								$pdf->MultiCell($maxw,4,$picarray[$i][2],0,'C');
							}
							$pictext_indent += $maxw + 5;
						}
					}
					$pdf->SetXY($keepX,$keepY+$tallestpic-7);
				}
			}
			elseif(strpos($key,"source")!==false AND $value != ''){   // make source link to end of document
				$pdf->SetFont('Times','',$font);
				$pdf->SetTextColor(28,28,255);
				if($source_presentation=='footnote') {  // "source 1" as link to list at end of doc
					$multitext = explode('~',$value);
					for($i=0;$i<count($multitext);$i++) {
						$len = strlen(__('Source')) + 3;  // 'space','(','$lang[...]','space'
						$num = substr($multitext[$i],$len,-1); // -1: take ) off the end
						$ofs = $num - 1; // offset starts with 0
						if($ofs >= 0) {  // is footnote to source from global source list
							$pdf->SetTextColor(28,28,255);
							$pdf->subWrite(6,$multitext[$i],$pdf_footnotes[$ofs],9,4);
						}
						else { // "manual" source list as regular non-clickable text
							$pdf->SetTextColor(0);
							$pdf->Write(6,$multitext[$i]);
						}
					}
				}
				elseif($user['group_sources']!='n')  {  // source title as link to list at end of doc
					// with multiple sources pdf string looks like:  ,firstsource!!12~,secondsource!!4~,thirdsource!!6
					$multitext = explode('~',$value); // each key looks like: ,somesource!!34
					for($i=0;$i<count($multitext);$i++) {
						$pos = strpos($multitext[$i],'!!');
						//if($user['group_sources']=='j' AND $pos) {
						if($pos) { //source title as link to list at bottom
							if($user['group_sources']=='j') { $pdf->SetTextColor(28,28,255); }
							else { $pdf->SetTextColor(0); }
							$ofs = substr($multitext[$i],$pos+2)-1;
							$txt = substr($multitext[$i],0,$pos); // take off the !!2 source number at end
							$pdf->Write(6,$txt,$pdf_footnotes[$ofs]);
						}
						else {  // source title as plain text
							$pdf->SetTextColor(0);
							$pdf->Write(6,$multitext[$i]);
						}
					}
					if($pos) {$pdf->Write(6,'. '); }
				}
				/*
				else {  // manual source title as plain text
					$pdf->SetTextColor(0);
					$pdf->Write(6,$value.'RED');
				}
				*/
				$pdf->SetTextColor(0);
			}
			else {
				$pdf->Write(6,$value);
			}
		}
		$pdf->SetFont('Arial',$type,$font);
	}
	if($person_kind!="child") {
		$pdf->Write(8,"\n");
	}
	else {
		$pdf->Write(6,"\n");
	}
}  // end function display details

// ***********************************************************************************
//  function displayrel()  to display wedding/relation details from marriage_cls.php
// ***********************************************************************************
function displayrel ($templ_relation,$ancestor_report) {
	global $pdf, $language, $user, $pdf_footnotes, $pdf_count_notes;
	$font=12;
	$samw=0;  $prew=0; $wedd=0; $prec=0; $chur=0; $devr=0;

	$source_presentation='title';
	if (isset($_SESSION['save_source_presentation'])){
		$source_presentation=$_SESSION['save_source_presentation'];
	}

	foreach($templ_relation as $key => $value) {
		$value=html_entity_decode($value);
		$pdf->SetFont('Arial','',$font);

		if(strpos($key,"exist")!==false) { continue; }

		if($ancestor_report=="ancestor") { $pdf->SetLeftMargin(38); }

		if(strpos($key,"samw")!==false AND $samw==0) {
			$pdf->SetFont('Arial','B',$font);
			$pdf->Write(6,html_entity_decode($templ_relation["marriage_exist"])); $samw=1;
			$pdf->SetFont('Arial','',$font);
		}
		if(strpos($key,"prew")!==false AND $prew==0) {
			$pdf->SetFont('Arial','B',$font);
			$pdf->Write(6,html_entity_decode($templ_relation["prew_exist"])); $prew=1;
			$pdf->SetFont('Arial','',$font);
		}
		if(strpos($key,"wedd")!==false AND $wedd==0) {
			$pdf->SetFont('Arial','B',$font);
			$pdf->Write(6,html_entity_decode($templ_relation["wedd_exist"])); $wedd=1;
			$pdf->SetFont('Arial','',$font);
		}
		if(strpos($key,"prec")!==false AND $prec==0) {
			$pdf->SetFont('Arial','B',$font);
			$pdf->Write(6,html_entity_decode($templ_relation["prec_exist"])); $prec=1;
			$pdf->SetFont('Arial','',$font);
		}
		if(strpos($key,"chur")!==false AND $chur==0) {
			$pdf->SetFont('Arial','B',$font);
			$pdf->Write(6,html_entity_decode($templ_relation["chur_exist"])); $chur=1;
			$pdf->SetFont('Arial','',$font);
		}
		if (isset($templ_relation["devr_exist"])){
			if(strpos($key,"devr")!==false AND $devr==0) {
				$pdf->SetFont('Arial','B',$font);
				$pdf->Write(6,html_entity_decode($templ_relation["devr_exist"])); $devr=1;
				$pdf->SetFont('Arial','',$font);
			}
		}

		if(strpos($key,"text")!==false) {  $pdf->SetFont('Arial','I',$font-1); }
		if(strpos($key,"witn")!==false) {  $pdf->SetFont('Times','',$font); }
		if(strpos($key,"source")!==false){ $pdf->SetFont('Times','',$font); }

		if (strpos($key,"source")!==false) {
			$multitext = explode('~',$value);

			if($source_presentation=='footnote') {  // "source 1" as link to list at end of doc
				for($i=0;$i<count($multitext);$i++) {
					$len = strlen(__('Source')) + 3;  // 'space','(','$lang[...]','space'
					$num = substr($multitext[$i],$len,-1); // -1: take ) off the end
					$ofs = $num - 1; // offset starts with 0
					if($ofs >= 0) {  // is footnote to source from global source list
						$pdf->SetTextColor(28,28,255);
						$pdf->subWrite(6,$multitext[$i],$pdf_footnotes[$ofs],9,4);
					}
					else { // "manual" source list as regular non-clickable text
						$pdf->SetTextColor(0);
						$pdf->Write(6,$multitext[$i]);
					}
				}
			}

			elseif($user['group_sources']!='n')  {  // source title as link to list at end of doc
				// with multiple sources pdf string looks like:  ,firstsource!!12~,secondsource!!4~,thirdsource!!6
				for($i=0;$i<count($multitext);$i++) {
					$pos = strpos($multitext[$i],'!!');
					//if($user['group_sources']=='j' AND $pos) {
					if($pos) { //source title as link to list at bottom
						if($user['group_sources']=='j') { $pdf->SetTextColor(28,28,255); }
						else { $pdf->SetTextColor(0); }
						$ofs = substr($multitext[$i],$pos+2)-1;
						$txt = substr($multitext[$i],0,$pos); // take off the !!2 source number at end
						$pdf->Write(6,$txt,$pdf_footnotes[$ofs]);
					}
					else {  // source title as plain text
						$pdf->SetTextColor(0);
						$pdf->Write(6,$multitext[$i]);
					}
				}
				if($pos) {$pdf->Write(6,'. '); }
			}
			$pdf->SetTextColor(0);
		}
		else {
			$pdf->Write(6,$value);
		}
		if($ancestor_report=="ancestor") { $pdf->SetLeftMargin(10); }
	}
	//$pdf_count_notes=0;
	$pdf->Write(8,"\n");
}

//*******************************************************************
//   function writename() to place the name of a person
//*******************************************************************

function writename($sexe,$indentation,$name,$length) {
	global $pdf, $language, $indent;
	if($sexe=="M") $pic="images/man.gif";
		elseif ($sexe=='F') $pic="images/woman.gif";
		else $pic="images/unknown.gif";
	$pdf->Image($pic,$indentation-4,$pdf->GetY()+2,3.5,3.5);
	$pdf->SetX($indentation);
	if($length=="long") {
		$indent=$pdf->GetX();
	}
	$pdf->SetFont('Arial','B',12);
	//$pdf->Write(8,$name);
	$pdf->MultiCell(0,8,$name,0,"L");
	$pdf->SetFont('Arial','',12);
	//$pdf->Write(8,"\n");
}

//*******************************************************************
// function pdf_ancestor_name()   writes names in ancestor report
//*******************************************************************

function pdf_ancestor_name($ancestor_reportnr,$sexe, $name) {
	global $pdf, $language;

	$pdf->SetFont('Arial','B',12);
	if($ancestor_reportnr>9999) { // (num) will be placed under num
		if($pdf->GetY()+7 >270) { //(num) would drop off bottom of page
			$pdf->AddPage(); // move num already to new page so they stay together...
		}
	}

	$pdf->Write(8,$ancestor_reportnr);
	$pdf->SetFont('Arial','',12);
	if($ancestor_reportnr>9999) { // num(num) becomes too long. (num) is placed 1 line down
		$pdf->Ln(7);
		$pdf->Write(8,'('.floor($ancestor_reportnr/2).')  ');
		$pdf->SetY($pdf->GetY()-7); //get back to first line to place name
	}
	else {
		$pdf->Write(8,' ('.floor($ancestor_reportnr/2).')  ');
	}

	$pdf->SetFont('Arial','B',12);
	$pdf->SetX(35);

	if($sexe=="M") {
		$pdf->Image("images/man.gif",$pdf->GetX(),$pdf->GetY()+2,3.5,3.5);
	}
	elseif($sexe=="F") {

		$pdf->Image("images/woman.gif",$pdf->GetX(),$pdf->GetY()+2,3.5,3.5);
	}
	else{
		$pdf->Image("images/unknown.gif",$pdf->GetX(),$pdf->GetY()+2,3.5,3.5);
	}

	$pdf->SetX($pdf->GetX()+3);
	$pdf->MultiCell(0,8,$name,0,"L");
	$pdf->SetFont('Arial','',12);
}

//***************************
 
var $angle=0;

function Rotate($angle,$x=-1,$y=-1){
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0){
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*$this->k;
		$cy=($this->h-$y)*$this->k;
		$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}

function _endpage(){
	if($this->angle!=0){
       	$this->angle=0;
		$this->_out('Q');
	}
	parent::_endpage();
} 

function Header(){
	global $title, $humo_option;
	if($this->PageNo()!=1) {
		//Position at 1.0 cm from top;
		$this->SetY(10);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Text color in gray
		$this->SetTextColor(128);
		//Page description
		$this->Cell(0,5,$title,0,1,'C');
		$this->Ln(5);
	}

	//Put watermark
	$this->SetFont('Arial', 'B', 50);
	$this->SetTextColor($humo_option["watermark_color_r"], $humo_option["watermark_color_g"], $humo_option["watermark_color_b"]);  // original was 255 192 203, changed default to 229 229 229
	$this->RotatedText(30, 190, $humo_option["watermark_text"], 45);
}

function RotatedText($x, $y, $txt, $angle){
	//Text rotated around its origin
	$this->Rotate($angle, $x, $y);
	$this->Text($x, $y, $txt);
	$this->Rotate(0);
}
 

/*   // original fpdf version - if ever needed
function Header(){
	global $title;

	//Arial bold 15
	$this->SetFont('Arial','B',15);
	//Calculate width of title and position
	$w=$this->GetStringWidth($title)+6;
	$this->SetX((210-$w)/2);
	//Colors of frame, background and text
	$this->SetDrawColor(0,80,180);
	$this->SetFillColor(230,230,0);
	$this->SetTextColor(220,50,50);
	//Thickness of frame (1 mm)
	$this->SetLineWidth(1);
	//Title
	$this->Cell($w,9,$title,1,1,'C',true);
	//Line break
	$this->Ln(10);
}
*/

function Footer(){
	//Position at 1.5 cm from bottom
	$this->SetY(-15);
	//Arial italic 8
	$this->SetFont('Arial','I',8);
	//Text color in gray
	$this->SetTextColor(128);
	//Page number
	$this->Cell(0,10,'PDF Created with HuMo-gen (PHP)    Page '.$this->PageNo(),0,0,'C');
}

// function to make super- or subscript
function subWrite($h, $txt, $link='', $subFontSize=12, $subOffset=0){
	// resize font
	$subFontSizeold = $this->FontSizePt;
	$this->SetFontSize($subFontSize);

	// reposition y
	$subOffset = ((($subFontSize - $subFontSizeold) / $this->k) * 0.3) + ($subOffset / $this->k);
	$subX        = $this->x;
	$subY        = $this->y;
	$this->SetXY($subX, $subY - $subOffset);

	//Output text
	$this->Write($h, $txt, $link);

	// restore y position
	$subX        = $this->x;
	$subY        = $this->y;
	$this->SetXY($subX,   $subY + $subOffset);

	// restore font size
	$this->SetFontSize($subFontSizeold);
}

} // end class that extends the pdf class
?>