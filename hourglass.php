<?php

//*************************************************************************
//** HOURGLASS.PHP by Yossi Beck august 2013                            ***
//** Made with extensive use of existing files:                         ***
//** - report_ancestor.php (included with minor changes in the file)    *** 
//** - report_descendant.php (included with minor changes in the file)  *** 
//** - family.php (included with minor changes in the file)             ***
//** Other additions and alterations:                                   ***
//** - hourglass item added to popup menu in /include/person_cls.php    ***
//** - icon added: /images/hourglass.gif                                ***
//** - class added to gedcom.css and silverline.css for graph lines     ***
//*************************************************************************

/* =================
TODO:
- save slider position
- think about names in size 8
==================== */

include_once("family.php");
include_once("report_descendant.php");

// in the ancestor code below $family_id is used for pers_gedcomnumber
// until here it was used by the descendant code for the fam_gedcomnumber
$family_id = $_GET['main_person']; 
include_once("report_ancestor.php");

// GENERATE DATA FOR DESCENDANTS ^^^^^

//$size=45; 
$direction=1;
generate(); // generates the $genarray for the descendant chart (to be printed later, after the ancestor chart - with the printchart() function)

// HORIZONTALLY ALIGN POSITION OF BASE PERSON IN DESC CHART AND ANCESTOR CHART

// Height of base person in desc chart is dynamically generated in descendant chart functions
// Height of base person in ancestor chart is set here. Will be moved down if necessary
if($size==50) { $boxhight=1.5 * 75;}
elseif($size==45) { $boxhight = 1.5 * 45;}
else { $boxhight = 1.5 * $size ; }
$anc_top = (pow(2,$chosengenanc-1)*$boxhight)/2; 

if($genarray[0]["y"] < $anc_top) { // if desc base pers higher on screen than base person of ancestor chart - has to be lowered to there.
	$offset = $anc_top - $genarray[0]["y"];
	for($a=0; $a<count($genarray);$a++) {
		$genarray[$a]["y"] += $offset;
		if(isset($genarray[$a]["fst"])) $genarray[$a]["fst"] += $offset;
		if(isset($genarray[$a]["lst"])) $genarray[$a]["lst"] += $offset;
	}
}
if($genarray[0]["y"] > $anc_top)  { // if desc base person lower, we have to lower base person of anc chart.
	$anc_top = $genarray[0]["y"] ;
}
//Set height of chart, both for screen and img-to-print
//Descendant chart bottom coordinates
$desc_hi=0;
for($i=0; $i < count($genarray); $i++) {
	if($genarray[$i]["y"] > $desc_hi) {
		$desc_hi = $genarray[$i]["y"];
	}
}
$desc_hi +=150;  // lowest point of desc chart
//Ancestor chart bottom coordinates
if($size==50) { $v_distance = 1.5 * 75;}
else { $v_distance = 1.5 * $size; }
$anc_hi = $anc_top + ((pow(2,$chosengenanc-1) * $v_distance)/2) + 100; // lowest point of anc chart 550

// Find longest chart and set as bottom of div
$div_hi = $desc_hi > $anc_hi ? $desc_hi : $anc_hi;

echo '<div class="standard_header fonts" style="align:center; text-align: center;">';
echo '<b>'.__('Hourglass chart').__(' of ').$genarray[0]["nam"].'</b>';
echo '</div>';

echo '<script type="text/javascript" src="include/jqueryui/js/html2canvas.js"></script>';
echo '<script type="text/javascript" src="include/jqueryui/js/jquery.plugin.html2canvas.js"></script>';
echo '<div style="text-align:center;">';
echo '<span style="font-size:130%">'.__('Ancestors')."&#8678;&#8678;&#8678;"; for($q=0;$q<25;$q++) { echo "&nbsp;"; } echo '</span>';
echo '<input type="button" id="imgbutton" value="'.__('Get image of chart for printing (allow popup!)').'" onClick="showimg();">';
echo '<span style="font-size:130%">'; for($q=0;$q<25;$q++) { echo "&nbsp;"; }  echo "&#8680;&#8680;&#8680;".__('Descendants'); echo '</span>';
echo '</div>';

// START HELP POPUP - displayed at upper left corner of screen

//======== HELP POPUP ========================
echo '<div id="helppopup" class="'.$rtlmarker.'sddm" style="position:absolute;left:10px;top:10px;display:inline;">';
echo '<a href="#"';
echo ' style="display:inline" ';
echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
echo 'onmouseout="mclosetime()">';
echo '<b>'.__('Help').'</b>';
echo '</a>&nbsp;';

echo '<div class="sddm_fixed" style="z-index:10; padding:4px; text-align:'.$alignmarker.';  direction:'.$rtlmarker.';" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

echo __('<b>USE:</b>
<p>The main person is displayed in the center of the chart.<br>
Ancestors are displayed to his/her left, descendants are displayed to the right
<p><b>Hover over square:</b> Display popup menu with details<br>
<b>Click on square:</b> Move this person to center of chart<br>
<b>Click on name in popup menu:</b> Go to person\'s family page<br><br>
<b>LEGEND:</b>');

echo '<p><span style="background-color:cyan; border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Male').'<br>';
echo '<span style="background-color:pink; border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Female').'<br>';

echo '<span style="color:blue">=====</span>&nbsp;'.__('Additional marriage of same person').'<br><br>';

echo __('<b>SETTINGS:</b>

<br><br><b>Nr. Generations:</b> choose between 2 - 12 generations for ancestors<br>
and 2 - 15 generation for descendants. 
(large number of generations will take longer to generate)<br>
<b>Box size:</b> Use the slider to choose display size (10 steps): <br>
step 1-4: small boxes with popup for details<br>
step 5-7: larger boxes with initials of name + popup for details<br>
step 8-9: boxes/rectangles with name inside + popup with further details<br>
step 10:    large rectangles with name, birth and death details + popup with further details');

echo '</div>';
echo '</div>';

// MENU BAR - no. of generations, zoom

	echo '<div id="menubox" class="search_bar" style="margin-top:5px; direction:ltr; z-index:20; width:600px; text-align:left;">';

	print '&nbsp;'.__('Nr. generations').': '.__('Anc.').'&nbsp;';
	print '<select name="chosengenanc" onChange="window.location=this.value">';

	for ($i=2; $i<=12; $i++) {
		if(CMS_SPECIFIC=='Joomla') {  
			print '<option value="index.php?option=com_humo-gen&task=hourglass&id='.$keepfamily_id.'&amp;main_person='.
			$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;chosensize='.
			$size.'&amp;chosengen='.$chosengen.'&amp;chosengenanc='.$i.'&amp;screen_mode=HOUR" ';
		}
		else{
			print '<option value="'.$uri_path.'hourglass.php?id='.$keepfamily_id.'&amp;main_person='.
			$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;chosensize='.
			$size.'&amp;chosengen='.$chosengen.'&amp;chosengenanc='.$i.'&amp;screen_mode=HOUR" ';
		}
		if ($i == $chosengenanc) print "selected=\"selected\" ";
		print ">".$i."</option>";
	}

	print '</select>';

	print '&nbsp;&nbsp;'.__('Desc.').'&nbsp;';
	print '<select name="chosengen" onChange="window.location=this.value">';

	for ($i=2; $i<=15; $i++) {
		if(CMS_SPECIFIC=='Joomla') {  
			print '<option value="index.php?option=com_humo-gen&task=hourglass&id='.$keepfamily_id.'&amp;main_person='.
			$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;chosensize='.
			$size.'&amp;chosengen='.$i.'&amp;chosengenanc='.$chosengenanc.'&amp;screen_mode=HOUR" ';
		}
		else{
			print '<option value="'.$uri_path.'hourglass.php?id='.$keepfamily_id.'&amp;main_person='.
			$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;chosensize='.
			$size.'&amp;chosengen='.$i.'&amp;chosengenanc='.$chosengenanc.'&amp;screen_mode=HOUR" ';
		}
		if ($i == $chosengen) print "selected=\"selected\" ";
		print ">".$i."</option>";
	}

	//NEW - option "All" for all generations
	print '<option value="'.$uri_path.'hourglass.php?id='.$keepfamily_id.'&amp;main_person='.
	$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;chosensize='.
	$size.'&amp;chosengen=All&amp;chosengenanc='.$chosengenanc.'&amp;screen_mode=HOUR" ';
	if ($chosengen=="All") print "selected=\"selected\" ";
	print ">"."All"."</option>";


	print '</select>';


	print '&nbsp;&nbsp;';
	//NEW min:0 (for extra first step - now 10 steps: 0-9), then twice value +1 so on display first step is shown as 1, not 0
	echo ' 
		<script>
		$(function() {
			$( "#slider" ).slider({
				value: '.(($size/5)-1).',
				min: 0,
				max: 9,
				step: 1,
				database: "'.$database.'",
				main_person: "'.$keepmain_person.'",
				id: "'.$keepfamily_id.'",
				chosengen: "'.$chosengen.'",
				chosengenanc: "'.$chosengenanc.'",
				direction: "'.$direction.'",
				chart_type: "hour",
				slide: function( event, ui ) {
					$( "#amount" ).val(ui.value+1);
				}
			});
			$( "#amount" ).val($( "#slider" ).slider( "value" )+1 );
		});
		</script>
	';

	//echo '<label for="amount">Zoom in/out:</label>';
	echo '<label for="amount">'.__('Zoom level:').'</label> ';
	echo '<input type="text" id="amount" disabled="disabled" style="width:18px;border:0; color:#0000CC; font-weight:normal;font-size:115%;" />';
	echo '<div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>';
	echo '</div>';

// START DIV FOR IMAGE (to print image of chart with plotter) ^^^^^

//following div gets width and length in imaging java function showimg() (at bottom) otherwise double scrollbars won't work.
echo '<div id="png">';

// START DIV FOR DOUBLESCROLL (horizontal scrollbars top and bottom ^^^^^^^^^^^^^^^^^^^^^^^

echo '
<style type="text/css">
#doublescroll { position:relative; width:auto; height:'.$div_hi.'px; overflow: auto; overflow-y: hidden; }
#doublescroll p { margin: 0; padding: 1em; white-space: nowrap; }
</style>
';

echo '<div id="doublescroll">';

// PRINT THE ANCESTOR CHART ^^^^^^^^^^^^^

$left=10;  
$vdist=20;
$blocks = pow(2,$chosengenanc-1);
$height=75;
$width=170;
$line_drop = $height/2; 
$incr = 1.5 * $height; 
$hi = 1.5 * $height; 
$gap = 3 * $height;  

if($size==45) {
	$height =45; $width =100;
	$line_drop = $height/2; 
	$incr = 1.5 * $height; 
	$hi = 1.5 * $height; 
	$gap = 3 * $height; 
}
if($size<45) {
	$height=$size; 
	$width=$size; 
	$line_drop = $height/2; 
	$incr = 1.5 * $height; 
	$hi = 1.5 * $height; 
	$gap = 3 * $height;  
}

$top = $anc_top - ((($blocks*$hi) - $incr)/2);  

for($x=$chosengenanc;$x>1;$x--) {
	$this_top = $top;
	for ($i=0; $i<$blocks; $i++){
		$sexe_colour=''; 
		if(isset($sexe[$i+$blocks]) AND $sexe[$i+$blocks]!="") { 
			if ($sexe[$i+$blocks] == 'F'){ $sexe_colour=' ancestor_woman'; }
			if ($sexe[$i+$blocks] == 'M'){ $sexe_colour=' ancestor_man'; }
		}
		else { // empty square - give it background so lines won't show through
			$sexe_colour=' ancestor_none';
		}
		echo '<div class="ancestor_name'.$sexe_colour.'" style="top: '.$this_top.'px; left: '.$left.'px; height: '.$height.'px; width:'.$width.'px;';
		echo '">';
		if(isset($sexe[$i+$blocks]) AND $sexe[$i+$blocks]!="" ) { 
			echo ancestor_chart_person($i+$blocks,'hour'.$size);
		}
		else { 
			echo "&nbsp;"; // otherwise background color doesn't work and lines show through
		}
		echo '</div>';
		$this_top += $incr;
	}	
	
	// *** long vertical line ***
	$this_top = $top+$line_drop; 
	for ($i=0; $i <$blocks/2; $i++){
		echo '<div class="hour_ancestor_split" style="top: '.$this_top.'px; left: '.($left+$width+3).'px; height: '.$hi.'px;"></div>';
		$this_top += $gap;
	}	
	// *** little horizontal line ***
	$this_top = $top+$line_drop;
	if($i>1) {
		for ($i=0; $i<$blocks/4; $i++){ 
			echo '<div class="ancestor_line" style="top: '.($this_top + $hi/2).'px; left: '.($left+$width+12).'px; height: '.($hi*2).'px;"></div>';
			$this_top += $gap*2;
		}	
	}
	else {
		echo '<div class="ancestor_line" style="border-bottom:none;top: '.($this_top + $hi/2).'px; left: '.($left+$width+12).'px; height:1px;"></div>';
	}
	// prepare for next generation
	$top = $top + $incr/2;
	$hi *= 2;
	$gap *= 2;
	$incr *= 2;
	$blocks = $blocks/2;
	if($x> $chosengenanc-1 OR $size <45) { // maybe just: if($x==$chosengenanc)     ;-)
		$left += $width +20;
	}
	else {
		$left += $width/2 +20;
	}
}

// SET CHART DIMENSIONS AND CAPTIONS ^^^^^

if($size==50 OR $size==45) { 
	if($chosengenanc>2) { $anc_len = (2*($width + 20)) + (($chosengenanc-3)*(($size/2)+40)); }
	else { $anc_len = $width + 20; }
} 
else { $anc_len = ($chosengenanc-1) * ($width + 20); }

if($size==50) { $desc_len = $chosengen * ($width + 60); }
elseif ($size==45) { $desc_len = $chosengen * ($width + 50); }
else $desc_len = $chosengen * ($width + $size);

$divlen = 10 + $anc_len + $desc_len;
 
// PRINT THE DESCENDANT CHART ^^^^^^^^^^^^^

printchart();
 
include_once(CMS_ROOTPATH."footer.php");

?>