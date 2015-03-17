<?php
/****************************************************************************
* fanchart.php                                                              *
* Original fan plotting code from PhpGedView (GNU/GPL licence)              *
*                                                                           *
* Rewritten and adapted for HuMo-gen by Yossi Beck  -  October 2009         *
*                                                                           *
* This program is free software; you can redistribute it and/or modify      *
* it under the terms of the GNU General Public License as published by      *
* the Free Software Foundation; either version 2 of the License, or         *
* (at your option) any later version.                                       *
*                                                                           *
* This program is distributed in the hope that it will be useful,           *
* but WITHOUT ANY WARRANTY; without even the implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             *
* GNU General Public License for more details.                              *
****************************************************************************/
//error_reporting(E_ALL);
@set_time_limit(3000);

global $maxperson, $treeid, $chosengen, $fontsize, $date_display, $family_id, $printing, $fan_style, $fanw, $fanh, $indexnr;

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/language_event.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."menu.php");
require_once(CMS_ROOTPATH."include/fanchart/persian_log2vis.php");

$family_id=1; // *** Show 1st family if file is called directly. ***
if (isset($_GET["id"])){ $family_id=$_GET["id"]; }
if (isset($_POST["id"])){ $family_id=$_POST["id"]; }

$chosengen=5;
if (isset($_GET["chosengen"])){ $chosengen=$_GET["chosengen"]; }
if (isset($_POST["chosengen"])){ $chosengen=$_POST["chosengen"]; }

$fontsize=8;
if (isset($_GET["fontsize"])){ $fontsize=$_GET["fontsize"]; }
if (isset($_POST["fontsize"])){ $fontsize=$_POST["fontsize"]; }

$date_display=2;
if (isset($_GET["date_display"])){ $date_display=$_GET["date_display"]; }
if (isset($_POST["date_display"])){ $date_display=$_POST["date_display"]; }

$printing=1;
if (isset($_GET["printing"])){ $printing=$_GET["printing"]; }
if (isset($_POST["printing"])){ $printing=$_POST["printing"]; }

//NEW
if(!isset($_POST['show_desc'])) {  // first entry into page - check cookie or session
	if(isset($_COOKIE["humogen_showdesc"])) { 
		$showdesc=$_COOKIE["humogen_showdesc"];
	}
	elseif (isset( $_SESSION['save_show_desc'])){  
		$showdesc=$_SESSION['save_show_desc'];
	}
}
// The $_POST['show_desc'] and cookie setting is handled in header.php before the headers are sent
 
$treeid = array();

$maxperson = pow(2,$chosengen);
// initialize array
for ($i=0 ; $i < $maxperson; $i++) {
	for ($n=0; $n<6; $n++) {
		$treeid[$i][$n]="";
	}
}

// some prepared statements so they will be initialized once
$person_prep = $dbh->prepare("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber=?");
$person_prep->bindParam(1,$pers_var);

$fam_prep = $dbh->prepare("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber =?");
$fam_prep->bindParam(1,$fam_var);

function fillarray ($nr, $famid) {
	global $dbh, $maxperson;
	global $treeid, $person_prep, $fam_prep, $pers_var, $fam_var, $indexnr;
	if ($nr >= $maxperson) { return; }
	if ($famid) {
		$pers_var = $famid;
		$person_prep->execute();
		@$personmnDb = $person_prep->fetch(PDO::FETCH_OBJ);

		$man_cls = New person_cls;
		$man_cls->construct($personmnDb);
		$man_privacy=$man_cls->privacy;

		$name=$man_cls->person_name($personmnDb);
		$treeid[$nr][0]=$name["standard_name"];

		// *** Privacy filter ***
		if ($man_privacy==''){
			if ($personmnDb->pers_birth_date) $treeid[$nr][1]=$personmnDb->pers_birth_date;
				else $treeid[$nr][1]=$personmnDb->pers_bapt_date;

			if ($personmnDb->pers_death_date) $treeid[$nr][4]=$personmnDb->pers_death_date;
				else $treeid[$nr][4]=$personmnDb->pers_buried_date;
		}
		else{
			$treeid[$nr][1]='';
			$treeid[$nr][4]='';
		}

		//NEW
		if($nr==1) $indexnr = $personmnDb->pers_indexnr; // need this for desc chart at bottom, if selected

		$pos=strpos($personmnDb->pers_fams,";");
		if($pos===false) { $treeid[$nr][2]=$personmnDb->pers_fams; }
		else {$treeid[$nr][2]=substr($personmnDb->pers_fams,0,$pos);}
		$treeid[$nr][3]=$famid;
		$treeid[$nr][5]=$personmnDb->pers_sexe;

		if ($personmnDb->pers_famc){
			$fam_var = $personmnDb->pers_famc;
			$fam_prep->execute();
			@$record_family = $fam_prep->fetch(PDO::FETCH_OBJ);
			if ($record_family->fam_man){
				fillarray ($nr*2, $record_family->fam_man);
			}
			if ($record_family->fam_woman){
				fillarray ($nr*2+1, $record_family->fam_woman);
			}
		}
	}
} //END FUNCTION FILLARRAY

fillarray(1, $family_id);

/* split and center text by lines
* @param string $data input string
* @param int $maxlen max length of each line
* @return string $text output string
*/
function split_align_text($data, $maxlen, $rtlflag, $nameflag, $gennr) {
	$lines = explode("\n", $data);
	// more than 1 line : recursive calls
	if (count($lines)>1) {
		$text = "";
		foreach ($lines as $indexval => $line) $text .= split_align_text($line, $maxlen, $rtlflag, $nameflag, $gennr )."\n";
		return $text;
	}

	// process current line word by word
	$split = explode(" ", $data);
	$text = "";
	$line = "";

	if($rtlflag==1 AND $nameflag==1) {	// rtl name has to be re-positioned
		global $fan_style;
		if($fan_style==2 AND ($gennr==1 OR $gennr==2)) { $maxlen *= 1.5; } // half-circle has different position for 2nd 3rd generation
		else { $maxlen *= 2; }
	}

	$found = false;
	if ($found) $line=$data;
	else
	foreach ($split as $indexval => $word) {
		$len = strlen($line);
		$wlen = strlen($word);

		// line too long ?
		if (($len+$wlen)<$maxlen) {
			if (!empty($line)) $line .= " ";
			$line .= "$word";
		}
		else {
			$p = max(0,floor(($maxlen-$len)/2));
			if (!empty($line)) {
				if($rtlflag==1 AND $nameflag==1) {   // trl name
					$line = "$line".str_repeat(" ", $p); //
				}
				elseif ($rtlflag==1 AND $nameflag==0) {
					$line = str_repeat(" ", $p*1.5) . "$line"; // center alignment using spaces
				}
				else {
					$line = str_repeat(" ", $p) . "$line"; // center alignment using spaces
				}
				$text .= "$line\n";
			}
			$line = $word;
		}
	}
	// last line
	if (!empty($line)) {
		$len = strlen($line);
		//.. if (in_array(ord($line{0}),$RTLOrd)) $len/=2;
		$p = max(0,floor(($maxlen-$len)/2));
		if($rtlflag==1 AND $nameflag==1) {
			$line = "$line".str_repeat(" ", $p);
		}
		elseif ($rtlflag==1 AND $nameflag==0) {
			$line = str_repeat(" ", $p*1.5) . "$line"; // center alignment using spaces
		}
		else {
			$line = str_repeat(" ", $p) . "$line"; // center alignment using spaces
		}
		$text .= "$line";
	}
				// $text.=$wlen;
	return $text;
}

/**
* print ancestors on a fan chart
* @param array $treeid ancestry pid
* @param int $fanw fan width in px (default=840)
* @param int $fandeg fan size in deg (default=270)
*/
function print_fan_chart($treeid, $fanw=840, $fandeg=270) {
	global $dbh, $tree_id, $fontsize, $date_display;
	global $fan_style, $family_id;
	global $printing, $language, $selected_language;
	global $person_prep, $pers_var, $tree_prefix_quoted;
	global $china_message;
	// check for GD 2.x library
	if (!defined("IMG_ARC_PIE")) {
		print "ERROR: NO GD LIBRARY";
		return false;
	}
	if (!function_exists("ImageTtfBbox")) {
		print "ERROR: NO GD LIBRARY";
		return false;
	}

	if (intval($fontsize)<2) $fontsize = 7;

	$treesize=count($treeid);
	if ($treesize<1) return;

	// generations count
	$gen=log($treesize)/log(2)-1;
	$sosa=$treesize-1;

	// fan size
	if ($fandeg==0) $fandeg=360;
	$fandeg=min($fandeg, 360);
	$fandeg=max($fandeg, 90);
	$cx=$fanw/2-1; // center x
	$cy=$cx; // center y
	$rx=$fanw-1;
	$rw=$fanw/($gen+1);
	$fanh=$fanw; // fan height
	if ($fandeg==180) $fanh=round($fanh*($gen+1)/($gen*2));
	if ($fandeg==270) $fanh=round($fanh*.86);
	$scale=$fanw/840;

	// image init
	$image = ImageCreate($fanw, $fanh);
	$black = ImageColorAllocate($image, 0, 0, 0);
	$white = ImageColorAllocate($image, 0xFF, 0xFF, 0xFF);
	ImageFilledRectangle ($image, 0, 0, $fanw, $fanh, $white);
	if($printing==1) {
		ImageColorTransparent($image, $white);
	}

	// *** Border colour ***
	$rgb=""; if (empty($rgb)) $rgb = "#6E6E6E";
	$grey = ImageColorAllocate($image, hexdec(substr($rgb,1,2)), hexdec(substr($rgb,3,2)), hexdec(substr($rgb,5,2)));

	// *** Text colour ***
	$rgb=""; if (empty($rgb)) $rgb = "#000000";
	$color = ImageColorAllocate($image, hexdec(substr($rgb,1,2)), hexdec(substr($rgb,3,2)), hexdec(substr($rgb,5,2)));

	// *** Background colour ***
	$rgb=""; if (empty($rgb)) $rgb = "#EEEEEE";
	$bgcolor = ImageColorAllocate($image, hexdec(substr($rgb,1,2)), hexdec(substr($rgb,3,2)), hexdec(substr($rgb,5,2)));

	// *** Man colour ***
	$rgb=""; if (empty($rgb)) $rgb = "#B2DFEE";
	$bgcolorM = ImageColorAllocate($image, hexdec(substr($rgb,1,2)), hexdec(substr($rgb,3,2)), hexdec(substr($rgb,5,2)));

	// *** wife colour ***
	$rgb=""; if (empty($rgb)) $rgb = "#FFE4C4";
	$bgcolorF = ImageColorAllocate($image, hexdec(substr($rgb,1,2)), hexdec(substr($rgb,3,2)), hexdec(substr($rgb,5,2)));

	// imagemap
	$imagemap="<map id=\"fanmap\" name=\"fanmap\">";

	// loop to create fan cells
	while ($gen>=0) {
		// clean current generation area
		$deg2=360+($fandeg-180)/2;
		$deg1=$deg2-$fandeg;
		ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bgcolor, IMG_ARC_PIE);
		ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bgcolor, IMG_ARC_EDGED | IMG_ARC_NOFILL);
		$rx-=3;

		// calculate new angle
		$p2=pow(2, $gen);
		$angle=$fandeg/$p2;
		$deg2=360+($fandeg-180)/2;
		$deg1=$deg2-$angle;
		// special case for rootid cell
		if ($gen==0) {
			$deg1=90;
			$deg2=360+$deg1;
		}

		// draw each cell
		while ($sosa >= $p2) {
			$pid=$treeid[$sosa][0];
			$birthyr=$treeid[$sosa][1];
			$deathyr=$treeid[$sosa][4];
			$fontpx=$fontsize;
			if($sosa>=16 AND $fandeg==180) { $fontpx=$fontsize-1; }
			if($sosa>=32 AND $fandeg!=180) { $fontpx=$fontsize-1; }
			if (!empty($pid)) {
				if ($sosa%2) $bg=$bgcolorF;
				else $bg=$bgcolorM;
				if ($sosa==1) {
					if($treeid[$sosa][5]=="F") {
						$bg=$bgcolorF;
							}
						else if ($treeid[$sosa][5]=="M") {
							$bg=$bgcolorM;
						}
					else {
						$bg=$bgcolor; // sex unknown
						}
				}

				ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $bg, IMG_ARC_PIE);
				if($gen!=0) {
					ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $grey, IMG_ARC_EDGED | IMG_ARC_NOFILL);
				}
				else {
					ImageFilledArc($image, $cx, $cy, $rx, $rx, $deg1, $deg2, $grey, IMG_ARC_NOFILL);
					}

				$name=$pid;

				// check if string is RTL language- if it is, it has to be reversed later on by persian_log2vis()
				$rtlstr=0;
				if(preg_match('/(*UTF8)[א-ת]/',$name)!==0 OR preg_match('/(*UTF8)[أ-ى]/',$name)!==0) {
					// this is either Hebrew, Arabic or Persian -> we have to reverse the text!
					$rtlstr=1; 
				}
				$fontfile=CMS_ROOTPATH."include/fanchart/dejavusans.ttf"; // this default font serves: Latin,Hebrew,Arabic,Persian,Russian
				
				if(preg_match('/(*UTF8)\p{Han}/',$name)!==0) {	// String is Chinese so use a Chinese ttf font if present in the folder
					if(is_dir(CMS_ROOTPATH."include/fanchart/chinese")) {
						$dh=opendir(CMS_ROOTPATH."include/fanchart/chinese"); 
						while (false !== ($filename = readdir($dh))) {
							//if (strtolower(substr($filename, -3)) == "ttf"){
							if (strtolower(substr($filename, -3)) == "otf" OR strtolower(substr($filename, -3)) == "ttf"){
								$fontfile = CMS_ROOTPATH."include/fanchart/chinese/".$filename;
							}
						}
					}
					if($fontfile==CMS_ROOTPATH."include/fanchart/dejavusans.ttf") { //no Chinese ttf file found
						$china_message=1;
					}
				}
				
				$text = $name; // names
				$text2=""; // dates
				if($date_display==1) {  // don't show dates
				}
				else if ($date_display==2) { //show years only
					// years only chosen but we also do this if no place in outer circles
					$text2 .= substr($birthyr,-4)." - ".substr($deathyr,-4);
				}
				else if ($date_display==3) {  //show full dates (but not in narrow outer circles!)
					if ($gen >5) {
						$text2 .= substr($birthyr,-4)." - ".substr($deathyr,-4);
					}
						else if ($gen >4 AND $fan_style != 4) {
						$text2 .= substr($birthyr,-4)." - ".substr($deathyr,-4);
					}
					else {  // full dates
						if($birthyr) { $text2 .= "b.".$birthyr."\n"; }
						if($deathyr) { $text2 .= "d.".$deathyr; }
					}
				}

				// split and center text by lines
				$wmax = floor($angle*7/$fontpx*$scale);
				$wmax = min($wmax,35*$scale);  //35
				//$wmax = floor((90*$wmax)/100);
				if ($gen==0) $wmax = min($wmax, 17*$scale);  //17
				$text = split_align_text($text, $wmax, $rtlstr, 1, $gen);
				$text2 = split_align_text($text2, $wmax, $rtlstr, 0, $gen);

				if($rtlstr==1) {  
					persian_log2vis($text); // converts persian, arab and hebrew text from logical to visual and reverses it
				}
			
				$text.="\n".$text2;

				// text angle
				$tangle = 270-($deg1+$angle/2);
				if ($gen==0) $tangle=0;

				// calculate text position
				$bbox=ImageTtfBbox((double)$fontpx, 0, $fontfile, $text);
				$textwidth = $bbox[4]; //4

				$deg = $deg1+.44;
				if ($deg2-$deg1>40) $deg = $deg1+($deg2-$deg1)/11;   // 11
				if ($deg2-$deg1>80) $deg = $deg1+($deg2-$deg1)/7;   //  7
				if ($deg2-$deg1>140) $deg = $deg1+($deg2-$deg1)/4;  //  4


				if ($gen==0) $deg=180;

				$rad=deg2rad($deg);
				$mr=($rx-$rw/4)/2;
				if ($gen>0 and $deg2-$deg1>80) $mr=$rx/2;
				$tx=$cx + ($mr) * cos($rad);

				$ty=$cy - $mr * -sin($rad);
				if ($sosa==1) $ty-=$mr/2;

				// print text
				ImageTtfText($image, (double)$fontpx, $tangle, $tx, $ty, $color, $fontfile, $text);

				$imagemap .= "<area shape=\"poly\" coords=\"";
				// plot upper points
				$mr=$rx/2;
				$deg=$deg1;
				while ($deg<=$deg2) {
					$rad=deg2rad($deg);
					$tx=round($cx + ($mr) * cos($rad));
					$ty=round($cy - $mr * -sin($rad));
					$imagemap .= "$tx, $ty, ";
					$deg+=($deg2-$deg1)/6;
				}
				// plot lower points
				$mr=($rx-$rw)/2;
				$deg=$deg2;
				while ($deg>=$deg1) {
					$rad=deg2rad($deg);
					$tx=round($cx + ($mr) * cos($rad));
					$ty=round($cy - $mr * -sin($rad));
					$imagemap .= "$tx, $ty, ";
					$deg-=($deg2-$deg1)/6;
				}
				// join first point
				$mr=$rx/2;
				$deg=$deg1;
				$rad=deg2rad($deg);
				$tx=round($cx + ($mr) * cos($rad));
				$ty=round($cy - $mr * -sin($rad));
				$imagemap .= "$tx, $ty";

				if (CMS_SPECIFIC == "Joomla") {
					$imagemap .= "\" href=\"index.php?option=com_humo-gen&amp;task=family&amp;id=".$treeid[$sosa][2]."&amp;main_person=".$treeid[$sosa][3]."\"";
				}
				else {
					$imagemap .= "\" href=\"family.php?id=".$treeid[$sosa][2]."&amp;main_person=".$treeid[$sosa][3]."\"";
				}

				//NEW - add first spouse to base person's tooltip
				$spousename=""; 
				if($gen==0 AND $treeid[1][2] != "") { // base person and has spouse
					if($treeid[1][5]=="F") { $spouse="fam_man";} else { $spouse="fam_woman"; }

					//2 reasons this is not a prepared pdo statement: 1. only used once  2. table names can't be parameters...
					//$spouse_result = $dbh->query("SELECT ".$spouse." FROM ".$tree_prefix_quoted."family WHERE fam_gedcomnumber='".$treeid[1][2]."'");
					$spouse_result = $dbh->query("SELECT ".$spouse." FROM humo_families
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$treeid[1][2]."'");
					@$spouseDb = $spouse_result->fetch(); // fetch() with no parameter deaults to array which is what we want here

 					$pers_var = $spouseDb[$spouse];
					$person_prep->execute();
					@$spouse2Db = $person_prep->fetch(PDO::FETCH_OBJ);

					$spouse_cls = New person_cls;
					$spouse_cls->construct($spouse2Db);
					$spname=$spouse_cls->person_name($spouse2Db);
					if($treeid[1][5]=="F") $spouse_lan="SPOUSE_MALE"; else $spouse_lan="SPOUSE_FEMALE";
					if($spname!="") { $spousename="\n(".__($spouse_lan).": ".$spname["standard_name"].")"; }
				}

				$imagemap .= " alt=\"".$pid."\" title=\"".$pid.$spousename."\">";
			}
			$deg1-=$angle;
			$deg2-=$angle;
			$sosa--;
		}
		$rx-=$rw;
		$gen--;
	}

	$imagemap .= "</map>";

	echo $imagemap;

	$image_title=preg_replace("~<.*>~", "", $name) ."   - ".__('RELOAD FANCHART WITH \'VIEW\' BUTTON ON THE LEFT');
	echo "<p align=\"center\" >";

	if(CMS_SPECIFIC == "Joomla") {
		ImagePng($image,CMS_ROOTPATH."include/fanchart/tmpimg.png");
		$ext="?".time(); // add random string to file to prevent loading from cache and then replacing which is not nice
		echo "<img src=\"index.php?option=com_humo-gen&task=fanimage&format=raw&nochache=".$ext."\" width=\"$fanw\" height=\"$fanh\" border=\"0\" alt=\"$image_title\" title=\"$image_title\" usemap=\"#fanmap\">";
	}
	else {
		ob_start();
		ImagePng($image);
		$image_data = ob_get_contents();
		ob_end_clean();
		$image_data = serialize($image_data);
		unset ($_SESSION['image_data']);
		$_SESSION['image_data']=$image_data;

		echo "<img src=\"include/fanchart/fanimage.php\" width=\"$fanw\" height=\"$fanh\" border=\"0\" alt=\"$image_title\" title=\"$image_title\" usemap=\"#fanmap\">";
	}

	echo "</p>\n";
	ImageDestroy($image);
}



//TEST in image using CSS
//echo '
//<STYLE>
//#rotate {
//	position: absolute; z-index:2;
//	top: 420px; left: 420px;
//	-ms-transform: rotate(-65deg); /* IE 9 */
//	-webkit-transform: rotate(-65deg); /* Chrome, Safari, Opera */
//	transform: rotate(-65deg);
//}
//</STYLE>
//<div id="rotate">Rotate<br>漢字<br>טבלאות בסיס</div>
//';



$fan_style=3;
$maxgens=7;
$fan_width="auto";

if (isset($_GET["fan_style"])){ $fan_style=$_GET["fan_style"]; }
if (isset($_POST["fan_style"])){ $fan_style=$_POST["fan_style"]; }
if (isset($_GET["fan_width"])){ $fan_width=$_GET["fan_width"]; }
if (isset($_POST["fan_width"])){ $fan_width=$_POST["fan_width"]; }

if ($fan_width >50 AND $fan_width <301){ $tmp_width=$fan_width; }
else { // "auto" or invalid entry - reset to 100%
	$tmp_width=100;
	if(CMS_SPECIFIC == "Joomla") { $tmp_width=78; }
}
$realwidth=(840*$tmp_width)/100; // realwidth needed for next line (top text)

// Text on Top: Name of base person and print-help link
$top_for_name=20;
if(CMS_SPECIFIC == "Joomla") {
	$top_for_name=45; // lower to get out of the way of possible scrollbar
}
echo '<div style="border:1px;z-index:80; position:absolute; top:'.$top_for_name.'px; left:135px; width:'.$realwidth.'px; height:30px; text-align:center; color:#000000">';

echo '<div style="padding:5px">';
print "<strong>".__('Fanchart')." - ".$treeid[1][0]."</strong>\n";

//======== HELP POPUP ========================
echo '<div class='.$rtlmarker.'sddm>';
echo '<a href="#"';
echo ' style="display:inline" ';
echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
echo 'onmouseout="mclosetime()">';
echo '<br><strong>'.__('How to print the chart').'</strong>';
echo '</a>&nbsp;';
echo '<div class="sddm_fixed" style="z-index:40; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

echo __('<u>Internet Explorer:</u><br>
1. Set background to "white" on the menu and press "View"<br>
2. Right-click on the chart<br>
3. Save to disk with "Save picture as"<br>
4. Print the saved picture');

echo __('<p><u>All other browsers:</u><br>
Just print the page .... ;-)<br>
Print the chart in "Landscape" outlay, use "Print Preview"<br>
and adjust printing size to fit the page<br>
(for regular charts 85%-90% of screen size)');

echo '</div>';
echo "</div>\n";

//=================================

echo '</div></div>';

//YB  Code to automatically make chart bigger when 7 generations are chosen
//    and the boxes for generations in outer circle(s) become too small
//    Same for 6 generations in half circle chart

if($fan_width=="auto" OR $fan_width=="") {  // if someone cleared the field alltogether we'll handle it as "auto"
	$menu_fan = "auto"; // $menu_fan is what will be displayed in menu. If size is changed automatically still "auto" will be displayed
	if($chosengen==7) {
		if($fan_style==2){
			$fan_width=220;
		}
		else if($fan_style==3) {
			$fan_width=160;
		}
		else if($fan_style==4) {
			$fan_width=130;
		}
		else { //YB: you can never get here, but just for paranoia's sake...
			$fan_width=100;
		}
	}
	// or 6 generations with half circle...
	else if($chosengen==6 AND $fan_style==2){
		$fan_width=130;
	}

	else {
		$fan_width=100;
		if (CMS_SPECIFIC == "Joomla") { $fan_width=78; }
	}
}
else if($fan_width >50 AND $fan_width <300) {  // valid entry by user
	$menu_fan = $fan_width;
}
else { // invalid entry! reset it.
	$fan_width=100;
	if (CMS_SPECIFIC == "Joomla") { $fan_width=78; }
	$menu_fan = "auto";
}

if(CMS_SPECIFIC == "Joomla") {
		if($fan_style==3) { $thisheight = ($fan_width*3.5)/4; }   // (default) 3/4 circle
		else if($fan_style==2) { $thisheight = $fan_width/1.6; }  // half circle
		else { $thisheight = $fan_width; }  // full circle

		$thisheight = 840*$thisheight/100 + 100;

		echo '<style type="text/css">';
		echo '#doublescroll { position:relative; width:100%; height:'.$thisheight.'px; overflow: auto; overflow-y: hidden; }';
		echo '</style>';
		echo '<div id="doublescroll">';
}

// Semi-transparant MENU BOX on the left
echo '<div class="fanmenu1">';
echo '<div class="fanmenu2">';
echo '<div class="fanmenu3"></div>';
echo '<div style="position:absolute; left:0; top:0; color: #000000">';

//echo '<div style="border: 2px solid #000077; padding:10px">';

if (CMS_SPECIFIC == "Joomla") {
	print "<form name=\"people\" method=\"post\" action=\"index.php?option=com_humo-gen&task=fanchart&id=".$family_id."\" style=\"display:inline;\">";
}
else {
	print "<form name=\"people\" method=\"post\" action=\"fanchart.php?id=".$family_id."\" style=\"display:inline;\">";
}
//print "<br>";
print "<input type=\"submit\" value=\"" .__('View'). "\">";

// Fan style
print '<br><hr style="width:110px">';
print __('Fan style')."<br>";
print '<div style="text-align:'.$alignmarker.';margin-left:15%;margin-right:15%">';
print "<input type=\"radio\" name=\"fan_style\" value=\"2\"";
if ($fan_style==2) print " checked=\"checked\"";

print ">".__('half');

print "<br><input type=\"radio\" name=\"fan_style\" value=\"3\"";
if ($fan_style==3) print " checked=\"checked\"";
print "> 3/4";
print "<br><input type=\"radio\" name=\"fan_style\" value=\"4\"";
if ($fan_style==4) print " checked=\"checked\"";
print ">".__('full');
print '</div>';

// Nr. of generations
print '<hr style="width:110px">';
print __('Generations').":<br>";
print "<select name=\"chosengen\">";
for ($i=2; $i<=min(9,$maxgens); $i++) {
	print "<option value=\"".$i."\"" ;
	if ($i == $chosengen) print "selected=\"selected\" ";
	print ">".$i."</option>";
}
print "</select>";

// Fontsize
print '<br><hr style="width:110px">';
print __('Font size').":<br>";
print "<select name=\"fontsize\">";
for ($i=5; $i<=12; $i++) {
	print "<option value=\"".$i."\"" ;
	if ($i == $fontsize) print "selected=\"selected\" ";
	print ">".$i."</option>";
}
print "</select>";

// Date display
print '<br><hr style="width:110px">';
print __('Date display').":<br>";

print '<div style="text-align:'.$alignmarker.';margin-left:5%;margin-right:5%">';
print "<input type=\"radio\" name=\"date_display\" value=\"1\"";
if ($date_display=="1") print " checked=\"checked\"";
print '>'.__('No dates');

print "<br><input type=\"radio\" name=\"date_display\" value=\"2\"";
if ($date_display=="2") print " checked=\"checked\"";
print ">".__('Years only');

print "<br><input type=\"radio\" name=\"date_display\" value=\"3\"";
if ($date_display=="3") print " checked=\"checked\"";
print ">".__('Full dates');
print '</div>';

// Fan width in percentages
print '<hr style="width:110px">';
print __('Fan width:')."<br>";
print "<input type=\"text\" size=\"3\" name=\"fan_width\" value=\"".$menu_fan."\"> <b>%</b> ";
print '<div style="font-size:10px;">'.__('"auto" for automatic resizing for best display, or value between 50-300').'</div>';

// Background (for printing with IE)
print '<hr style="width:110px">';
print __('Background').":<br>";

print '<div style="text-align:'.$alignmarker.';margin-left:5%;margin-right:5%">';
print "<input type=\"radio\" name=\"printing\" value=\"1\"";
if ($printing==1) print " checked=\"checked\"";
print "> <span style=\"font-size:10px;\">".__('transparent')."</span>";
print "<br><input type=\"radio\" name=\"printing\" value=\"2\"";
if ($printing==2) print " checked=\"checked\"";
print "> <span style=\"font-size:10px;\">".__('white')."</span>";
print '</div>';
 
//NEW
print '<hr style="width:110px">';
print '<div style="text-align:'.$alignmarker.';margin-left:5%;margin-right:5%">';
print '<input type="hidden" name="show_desc" value="0">';
print '<input type="checkbox" name="show_desc" value="1"';
if ($showdesc=="1") print ' checked="checked"';
print '> <span style="font-size:10px;">'.__('descendants').'<br>&nbsp;&nbsp;&nbsp;&nbsp;'.__('under fanchart').'</span>';
print '</div>';
//END NEW
 
print "</form>";
print "</div></div></div>";

//  Container for fanchart
echo '<div style="position:absolute; top:60px; left:135px; width:'.$fan_width.'">';
if(CMS_SPECIFIC != "Joomla") {
	echo '<div style="padding:5px">';
}
$china_message=0;
print_fan_chart($treeid, 840*$fan_width/100, $fan_style*90);

echo '</div></div>';
// end container for fanchart

if(CMS_SPECIFIC == "Joomla") {
	echo '</div>'; // end of horizontal scrollbar div
}
if($china_message==1) {
	echo '<div style="border:2px solid red;background-color:white;padding:5px;position:relative;
	length:300px;margin-left:30%;margin-right:30%;top:90px;font-weight:bold;color:red;
	font-size:120%;text-align:center;">'.
	__('No Chinese ttf font file found').
	"<br>".__('Download link').': <a href="http://humogen.com/download.php?file=simplified-wts47.zip">Simplified 简体中文 </a>'.__('or').
	' <a href="http://humogen.com/download.php?file=traditional-wt011.zip">Traditional 繁體中文</a><br>'.
	__('Unzip and place in "include/fanchart/chinese/" folder').'</div>';
}
//NEW

if($showdesc=="1" ) {
	$fan_w =  9.3*$fan_width;
	if($fan_style==2) $top_pos = $fan_w/2 + 165;
	elseif($fan_style==3) $top_pos = 0.856*$fan_w;
	elseif($fan_style==4) $top_pos = $fan_w;

	echo '<iframe src="family.php?database='.safe_text($_SESSION['tree_prefix']).'&amp;id='.$indexnr.'&amp;main_person='.$family_id.'&amp;screen_mode=STAR&amp;menu=1" id="iframe1"  style="position:absolute;top:'.$top_pos.'px;left:0px;width:100%;height:700px;" ;" >';
	echo '</iframe>';
}
//END NEW

echo '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
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

include_once(CMS_ROOTPATH."footer.php");
?>