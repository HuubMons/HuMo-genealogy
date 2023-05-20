 <?php
// -------------------------------------------------------------------------
// |   REPORT_DESCENDANT.PHP                                               |
// |   for use with the $genarray generated in HuMo-genealogy              |
// |   Original starfield plotting code by Yossi Beck - Feb-March 2010     |
// |   Copyright GPL_GNU licence                                           |
// -------------------------------------------------------------------------
// meaning of $genarray members:
// "par" = array nr of parent
// "nrc" = nr of children (children with multiple marriages are counted as additional children for plotting's sake
// "gen" = nr of the generation
// "posx" = the x position of top left corner of a person's square
// "posy" = the y position of top left corner of a person's square
// "fst" = the x position of first (lefmost) child
// "lst" = the x position of last (rightmost) child, unless this is a second marriage of this child,
//         in which case the first marriage of the last child is entered into "lst"
// "chd" = the number of the child in the family (additional marriages have subsequent numbers)
// "2nd" = indicates this person is in fact a second or following instance of the previous person with additional marriage
// "htx" = wedding text ("married on 13 mar 1930 to:")
// "huw" = mentioning of additional marriage ("2nd marriage")
// "sex" = sex of the person
// "nam" = name of the person
// "sps" = name of spouse
// "fams"  = GEDCOM family number (F345)
// "gednr" = GEDCOM person number (I143)
// "non" = person with no own family (i.e. only child status)
// *********************************************************************************************

// for png image generating
echo '<script type="text/javascript" src="include/html2canvas/html2canvas.min.js"></script>';

global $hsize;  // horizontal length of box
global $vsize;  // vertical height of box
global $vbasesize; // vertical distance in between X value of parent and X value of child
global $vdist; // vertical distance in between boxes of two generations
global $hdist; // horizontal distance in between boxes of two generations

//*********************************************************************************
//********** 1st Part:  CODE TO GENERATE THE STARFIELD CHART FROM $GENARRAY *******
//*********************************************************************************
function generate() {
	global $genarray, $direction;
	$_SESSION['genarray']=$genarray;

	global $hsize;  // horizontal length of box
	global $vsize;  // vertical height of box
	global $chosengen; // number of generations to display (default set in family.php)
	global $chosengenanc; // number of ancestor generations chosen (in hourglass chart)
	global $size; // default size (default set in family.php)
	global $hourglass;

	if($direction==0) { // if vertical

		global $vbasesize; // vertical distance in between X value of parent and X value of child
		global $vdist; // vertical distance in between boxes of two generations

		if($size==50){   // full size box with name and details
			$hsize=150;
			$vsize=75;
			$vdist=80;
		}
		elseif($size==45) { // smaller box with name + popup
			$hsize=100;
			$vsize=45;
			$vdist=60;
		}
		else {             // re-sizable box with no name, only popup
			$hsize=$size;
			$vsize=$size;
			$vdist=$size*2;
		}

		$vbasesize=$vsize+$vdist;
		$inbetween=10;   // horizontal distance between two persons in a family. Between fams is double $inbetween

		$movepar=0;  // flags the need to move parent box. 1 means: call move() function

		for($i=0; $i < count($genarray);$i++) {
			if(!isset($genarray[$i])) { break; }

			$distance=0;

			$genarray[$i]["posy"]=($genarray[$i]["gen"]*($vbasesize))+40;
			$par=$genarray[$i]["par"];
			if($genarray[$i]["chd"]==1) {   // the first child in this fam
				if($genarray[$i]["gen"]==0) {  // this is base person - put in left most position
					$genarray[$i]["posx"]=0;
				}
				else { // first child in fam in 2nd or following generation
					$exponent=$genarray[$par]["nrc"]-1; // exponent is number of additional children
//if (isset($genarray[$i]["posx"]))
					$genarray[$i]["posx"] = $genarray[$par]["posx"] - (($exponent*($hsize+$inbetween))/2); // place in proper spot under parent
//else
//					$genarray[$i]["posx"] = (($exponent*($hsize+$inbetween))/2); // place in proper spot under parent

					if($genarray[$i]["gen"]==$genarray[$i-1]["gen"]) { // is first child in fam but not in generation

						if($genarray[$i]["posx"] < $genarray[$i-1]["posx"]+($hsize+$inbetween*2)) {
							$genarray[$i]["posx"]=$genarray[$i-1]["posx"]+($hsize+$inbetween*2);
							$movepar=1;
						}
					}
					else {  // is first child in generation. If it was set to minus 0, move it to 0 and call "move parents" function move()
//if (isset($genarray[$i]["posx"])){
						if($genarray[$i]["posx"]<0) {
							$genarray[$i]["posx"]=0;
							$movepar=1;
						}
//}
					}
//if (isset($genarray[$i]["posx"]))
					$genarray[$par]["fst"]=$genarray[$i]["posx"];    // x of first child in fam
				}

			}
			else {
//if (isset($genarray[$i]["posx"]))
				$genarray[$i]["posx"] = $genarray[$i-1]["posx"] + ($hsize+$inbetween);
			}

			$z=$i;
			if($genarray[$z]["gen"]!=0 AND $genarray[$z]["chd"]==$genarray[$par]["nrc"]) {

				while($genarray[$z]["2nd"]==1)  {
					$z--;
				}

				$genarray[$par]["lst"]=$genarray[$z]["posx"];
				if($genarray[$z]["gen"]>$genarray[$z-1]["gen"] AND $genarray[$par]["lst"]==$genarray[$par]["fst"]) { 
				// this person is first in generation and is only child - move directly under parent
					$genarray[$z]["posx"]=$genarray[$par]["posx"];
					while(isset($genarray[$z+1]) AND $genarray[$z+1]["2nd"]==1) {
						$genarray[$z+1]["posx"]=$genarray[$z]["posx"]+$hsize+$inbetween;
						$z++;
					}
					$genarray[$par]["fst"]=$genarray[$par]["posx"];
				}
				elseif($movepar==1) {
					$movepar=0;
					move($par);
				}
			}

		}	// end for loop

	} // end if vertical

	else {  // horizontal
		global $hbasesize; // horizontal distance in between X value of parent and X value of child
		global $hdist; // horizontal distance in between boxes of two generations

		if($size==50){   // full size box with name and details
			$hsize=150; if($hourglass===true) $hsize=170;
			$vsize=75;
			$hdist=60;  if($hourglass===true) $hdist=30;
 		}
		elseif($size==45) { // smaller box with name + popup
			$hsize=100;
			$vsize=45;
			$hdist=50;
		}
		else {             // re-sizable box with no name, first 4 with initials + popup, rest only popup
			$hsize=$size;
			$vsize=$size;
			$hdist=$size; if($size<15) $hdist=15; // shorter than this doesn't look good
		}

		$hbasesize=$hsize+$hdist;
		$vinbetween=10;   // vertical distance between two persons in a family. Between fams is double $inbetween

		$movepar=0;  // flags the need to move parent box. 1 means: call move() function

		for($i=0; $i < count($genarray);$i++) {
			if(!isset($genarray[$i])) { break; }

			$distance=0;

			$genarray[$i]["posx"]=($genarray[$i]["gen"]*$hbasesize)+1;

			if($hourglass===true) {
				// calculate left position for hourglass (depends on number of ancestor generations chosen)
				if($size==50) $thissize = 170;
				elseif($size==45) $thissize = 100;
				else $thissize = $size;

				$left = 30 + $thissize; // default when 2 generations only
				if($chosengenanc==3 AND $size==50 AND $genarray[1]["2nd"]==1) {
					// prevent parent overlap by 2nd marr of base person in 3 gen display
					$left = 10 + (2*(20 + $thissize)) + (($chosengenanc-3)*(($thissize/2)+20));
				}
				elseif($chosengenanc>2) { 
					if($size==50) {
						$left = 10 + (2*$thissize) + (($chosengenanc-3)*(($thissize/2)+20));
					}
					elseif($size==45) {
						$left = 10 + (2*(20 + $thissize)) + (($chosengenanc-3)*(($thissize/2)+20));
					}
					elseif($size<45 ) {
						$left = 10 + (($chosengenanc-1) * ($size +20));
					}
				}

				$genarray[$i]["posx"]=($genarray[$i]["gen"]*$hbasesize)+$left;
			}
			$par=$genarray[$i]["par"];
			if($genarray[$i]["chd"]==1) {
				if($genarray[$i]["gen"]==0) {
					$genarray[$i]["posy"]=40;
				}
				else {
					$exponent=$genarray[$par]["nrc"]-1;

					$genarray[$i]["posy"] = $genarray[$par]["posy"] -  (($exponent*($vsize+$vinbetween))/2);

					if($genarray[$i]["gen"]==$genarray[$i-1]["gen"]) {

						if($genarray[$i]["posy"] < $genarray[$i-1]["posy"]+($vsize+$vinbetween*2)) {
							$genarray[$i]["posy"]=$genarray[$i-1]["posy"]+($vsize+$vinbetween*2);
							$movepar=1;
						}
					}
					else {
						if($genarray[$i]["posy"]<40) {
							$genarray[$i]["posy"]=40;
							$movepar=1;
						}
					}
					$genarray[$par]["fst"]=$genarray[$i]["posy"];       // y of first child in fam
				}

			}
			else {
				$genarray[$i]["posy"] = $genarray[$i-1]["posy"] + ($vsize+$vinbetween);
			}

			$z=$i;
			if($genarray[$z]["gen"]!=0 AND $genarray[$z]["chd"]==$genarray[$par]["nrc"]) {

				while($genarray[$z]["2nd"]==1)  {
					$z--;
				}

				$genarray[$par]["lst"]=$genarray[$z]["posy"];
				//NEW
				if($genarray[$z]["gen"]>$genarray[$z-1]["gen"] AND $genarray[$par]["lst"]==$genarray[$par]["fst"]) {
				// this person is first in generation and is only child - move directly under parent
					$genarray[$z]["posy"]=$genarray[$par]["posy"];
					// make this into while loop
					while(isset($genarray[$z+1]) AND $genarray[$z+1]["2nd"]==1) {
						$genarray[$z+1]["posy"]=$genarray[$z]["posy"]+$vsize+$vinbetween;
						$z++;
					}
					$genarray[$par]["fst"]=$genarray[$par]["posy"];
				}
				elseif($movepar==1) {
					$movepar=0;
					move($par);
				}
			}

		}	// end for loop

	}  // end if horizontal

}  // end function generate()

// *********************************************************************************************
// **** 2nd Part: RECURSIVE FUNCTION TO MOVE PART OF THE CHART WHEN NEW ITEMS ARE ADDED ********
// *********************************************************************************************
function move($i) {
	global $genarray, $size, $direction;

	if($direction==0) { // if vertical
		$par=$genarray[$i]["par"];
		$tempx= $genarray[$i]["posx"];
//if (isset($genarray[$i]["lst"]))
		$genarray[$i]["posx"] = ($genarray[$i]["fst"] + $genarray[$i]["lst"])/2;

		if($genarray[$i]["gen"]!=0) {
			$q=$i;
			if($genarray[$q]["chd"] == 1) {
				$genarray[$par]["fst"]=$genarray[$q]["posx"];
			}
			if($genarray[$q]["chd"]==$genarray[$par]["nrc"]) {
				while($genarray[$q]["2nd"]==1) {
					$q--;
				}
			$genarray[$par]["lst"]=$genarray[$q]["posx"];
			}
		}
		$distance = $genarray[$i]["posx"] - $tempx;

		$n=$i+1;
		while($genarray[$n]["gen"] == $genarray[$n-1]["gen"]) {
//		while(isset($genarray[$n]["gen"]) AND $genarray[$n]["gen"] == $genarray[$n-1]["gen"]) {
			if(isset($genarray[$n]["fst"]) AND isset($genarray[$n]["lst"])) {
				$tempx= $genarray[$n]["posx"];
				$genarray[$n]["posx"] = ($genarray[$n]["fst"] + $genarray[$n]["lst"])/2;
				$distance = $genarray[$n]["posx"] - $tempx;
			}
			else {
//if (isset($genarray[$n]["posx"]))
				$genarray[$n]["posx"] += $distance;
//else
//				$genarray[$n]["posx"] = $distance;
			}
			if($genarray[$n]["gen"]!=0) {
				$c=$n;
				$par=$genarray[$c]["par"];
				if($genarray[$c]["chd"] == 1) {
					$genarray[$par]["fst"]=$genarray[$c]["posx"];
				}
				if($genarray[$c]["chd"]==$genarray[$par]["nrc"]) {

					while($genarray[$c]["2nd"]==1) {
						// $c++;
						$c--;
					}

					$genarray[$par]["lst"]=$genarray[$c]["posx"];
				}
			}
			$n++;
		}
		if($genarray[$i]["gen"]>0) {
			$par=$genarray[$i]["par"];
			move($par);
		}
	} // end if vertical

	else { // if horizontal
		$par=$genarray[$i]["par"];
		$tempx= $genarray[$i]["posy"];
		$genarray[$i]["posy"] = ($genarray[$i]["fst"] + $genarray[$i]["lst"])/2;

		if($genarray[$i]["gen"]!=0) {
			$q=$i;
			if($genarray[$q]["chd"] == 1) {
				$genarray[$par]["fst"]=$genarray[$q]["posy"];
			}
			if($genarray[$q]["chd"]==$genarray[$par]["nrc"]) {
				while($genarray[$q]["2nd"]==1) {
					$q--;
				}
			$genarray[$par]["lst"]=$genarray[$q]["posy"];
			}
		}
		$distance = $genarray[$i]["posy"] - $tempx;

		$n=$i+1;
		while($genarray[$n]["gen"] == $genarray[$n-1]["gen"]) {
			if(isset($genarray[$n]["fst"]) AND isset($genarray[$n]["lst"])) {
				$tempx= $genarray[$n]["posy"];
				$genarray[$n]["posy"] = ($genarray[$n]["fst"] + $genarray[$n]["lst"])/2;
				$distance = $genarray[$n]["posy"] - $tempx;
			}
			else {
				$genarray[$n]["posy"] += $distance;
			}
			if($genarray[$n]["gen"]!=0) {
				$c=$n;
				$par=$genarray[$c]["par"];
				if($genarray[$c]["chd"] == 1) {
					$genarray[$par]["fst"]=$genarray[$c]["posy"];
				}
				if($genarray[$c]["chd"]==$genarray[$par]["nrc"]) {

					while($genarray[$c]["2nd"]==1) {
						$c--;
					}

					$genarray[$par]["lst"]=$genarray[$c]["posy"];
				}
			}
			$n++;
		}
		if($genarray[$i]["gen"]>0) {
			$par=$genarray[$i]["par"];
			move($par);
		}

	}  // end if horizontal
}

//*********************************************************************
//********** 3rd Part:  CODE TO PRINT THE STARFIELD CHART         *****
//*********************************************************************
function printchart() {
	global $dbh, $tree_id, $db_functions, $genarray, $size, $tree_prefix_quoted, $language, $chosengen, $keepfamily_id, $keepmain_person, $uri_path, $database;
	global $vbasesize, $hsize, $vsize, $vdist, $hdist, $user, $direction, $dna;
	global $dirmark1, $dirmark2, $rtlmarker, $alignmarker, $base_person_gednr, $base_person_name, $base_person_sexe, $base_person_famc;

	// YB: -- check browser type & version. we need this further on to detect IE7 with it's widely reported z-index bug
	$browser_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';

	global $hourglass, $divlen, $divhi;
	if($hourglass===false) {

		// find rightmost and bottommost positions to calculate size of the canvas needed for png image
		$divlen=0; $divhi=0;
		for($i=0; $i < count($genarray); $i++) {
			if($genarray[$i]["posx"] > $divlen) {
				$divlen = $genarray[$i]["posx"];
			}
			if($genarray[$i]["posy"] > $divhi) {
				$divhi = $genarray[$i]["posy"];
			}
		}
		$divlen += 200; $divhi +=300;

		// the width and length of following div are set with $divlen en $divhi in java function "showimg" 
		// (at bottom of this file) otherwise double scrollbars won't work.
		echo '<div id="png">';

		//======== HELP POPUP ========================
		echo '<div id="helppopup" class="'.$rtlmarker.'sddm" style="position:absolute;left:10px;top:10px;display:inline;">';
		echo '<a href="#"';
		echo ' style="display:inline" ';
		echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
		echo 'onmouseout="mclosetime()">';
		echo '<b>'.__('Help').'</b>';
		echo '</a>&nbsp;';

		//echo '<div style="z-index:40; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
		echo '<div class="sddm_fixed" style="z-index:10; padding:4px; text-align:'.$alignmarker.';  direction:'.$rtlmarker.';" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

		echo __('<b>USE:</b>
<p><b>Hover over square:</b> Display popup menu with details and report & chart options<br>
<b>Click on square:</b> Move this person to the center of the chart<br>
<b>Click on spouse\'s name in popup menu:</b> Go to spouse\'s family page<br><br>
<b>LEGEND:</b>');

		echo '<p><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #81bef7 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Male').'</br>';
		echo '<span style="background-image: linear-gradient(to bottom, #ffffff 0%, #f5bca9 100%); border:1px brown solid;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Female').'</br>';
		if($dna=="ydna" OR $dna=="ydnamark" OR $dna=="mtdna" OR $dna=="mtdnamark") {
		echo '<p style="line-height:3px"><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #81bef7 100%); border:3px solid #999999;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Male Y-DNA or mtDNA carrier (Base person has red border)').'</p>';
		echo '<p style="line-height:10px"><span style="background-image: linear-gradient(to bottom, #ffffff 0%, #f5bca9 100%); border:3px solid #999999;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.__('Female MtDNA carrier (Base person has red border)').'</p>';
		}
		echo '<p><span style="color:blue">=====</span>&nbsp;'.__('Additional marriage of same person').'<br><br>';
		echo __('<b>SETTINGS:</b>
<p>Horizontal/Vertical button: toggle direction of the chart from top-down to left-right<br>
<b>Nr. Generations:</b> choose between 2 - 15 generations<br>
(large number of generations will take longer to generate)<br>
<b>Box size:</b> Use the slider to choose display size (9 steps): <br>
step 1-3: small boxes with popup for details<br>
step 4-7: larger boxes with initials of name + popup for details<br>
step 8:   rectangles with name inside + popup with further details<br>
step 9:   large rectangles with name, birth and death details + popup with further details');

		echo '</div>';
		echo '</div>';

		//=================================
		if($dna=="none") {
			echo '<div class="standard_header fonts" style="align:center; text-align: center;"><b>'.__('Descendant chart').__(' of ').$genarray[0]["nam"].'</b>';
		}
		elseif($dna=="ydna" OR $dna=="ydnamark") {
			echo '<div class="standard_header fonts" style="align:center; text-align: center;"><b>'.__('Same Y-DNA as ').$base_person_name.'</b>';
		}
		elseif($dna=="mtdna" OR $dna=="mtdnamark") {
			echo '<div class="standard_header fonts" style="align:center; text-align: center;"><b>'.__('Same mtDNA as ').$base_person_name.'</b>';
		}
			echo '<br><input type="button" id="imgbutton" value="'.__('Get image of chart for printing (allow popup!)').'" onClick="showimg();">';
		echo '</div>';

		if ($direction==0) {
			$latter=count($genarray)-1;
			$the_height=$genarray[$latter]["posy"]+130;
		}
		else {
			$hgt = 0;
			for ($e = 0; $e < count($genarray); $e++) {
				if($genarray[$e]["posy"] > $hgt) { $hgt = $genarray[$e]["posy"]; }
			}
			$the_height = $hgt + 130;
		}

		echo '<style type="text/css">';
		echo '#doublescroll { position:relative; width:auto; height:'.$the_height.'px; overflow: auto; overflow-y: hidden;z-index:10; }';
		echo '</style>';

		//echo '<div class="wrapper" style="position:relative; direction:'.$rtlmarker.';">';
		//echo '<div id="doublescroll" class="wrapper" style="direction:'.$rtlmarker.';"><br style="line-height:50%">';

		echo '<div id="doublescroll" class="wrapper" style="direction:'.$rtlmarker.';">';

		// generation and size choice box:
		if($dna=="none") { $boxwidth="640"; } // regular descendant chart
		else { $boxwidth="750"; } // DNA charts
		echo '<div id="menubox" class="search_bar" style="margin-top:5px; direction:ltr; z-index:20; width:'.$boxwidth.'px; text-align:left;">';

		echo '<div style="display:inline;">';
		echo '<form method="POST" name="desc_form" action="/family.php?chosensize='.$size.'&amp;screen_mode=STARSIZE" style="display : inline;">';
		
		echo '<input type="hidden" name="id" value="'.$keepfamily_id.'">';
		echo '<input type="hidden" name="chosengen" value="'.$chosengen.'">';
		echo '<input type="hidden" name="main_person" value="'.$keepmain_person.'">';
		echo '<input type="hidden" name="database" value="'.$database.'">';
		if($dna!="none") {
			echo '<input type="hidden" name="dnachart" value="'.$dna.'">';
			echo '<input type="hidden" name="bf" value="'.$base_person_famc.'">';
			echo '<input type="hidden" name="bs" value="'.$base_person_sexe.'">';
			echo '<input type="hidden" name="bn" value="'.$base_person_name.'">';
			echo '<input type="hidden" name="bg" value="'.$base_person_gednr.'">';
		}

		echo '<input id="dirval" type="hidden" name="direction" value="">';  // will be filled in next lines
		if ($direction=="1"){ // horizontal
			echo '<input type="button" name="dummy" value="'.__('vertical').'" onClick=\'document.desc_form.direction.value="0";document.desc_form.submit();\'>';
		}
		else{
			echo '<input type="button" name="dummy" value="'.__('horizontal').'" onClick=\'document.desc_form.direction.value="1";document.desc_form.submit();\'>';
		}
		echo '</form>';

		$result=$dbh->query("SELECT pers_sexe FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$keepmain_person."'");
		$resultDb=$result->fetch(PDO::FETCH_OBJ);
		if($dna!="none") {
			echo "&nbsp;&nbsp;".__('DNA: '); 
			echo '<select name="dnachart" style="width:150px" onChange="window.location=this.value">';
			//echo $selected="selected"; if($dna!="none") $selected="";
			//echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
			//		$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='."none".'&amp;chosensize='.
			//		$size.'&amp;chosengen='.$chosengen.'&amp;screen_mode=STAR" '.$selected.'>'.__('All').'</option>';
			if($base_person_sexe=="M") {		// only show Y-DNA option if base person is male
				//echo $selected=""; if($dna=="ydna") $selected="selected";
				echo $selected="selected"; if($dna!="ydna")  $selected=""; 
				echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
						$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='."ydna".'&amp;chosensize='.
						$size.'&amp;chosengen='.$chosengen.'&amp;screen_mode=STAR" '.$selected.'>'.__('Y-DNA Carriers only').'</option>';
				//echo $selected="selected"; if($dna!="ydnamark") $selected="";
				echo $selected=""; if($dna=="ydnamark") $selected="selected";
				echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
						$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='."ydnamark".'&amp;chosensize='.
						$size.'&amp;chosengen='.$chosengen.'&amp;screen_mode=STAR" '.$selected.'>'.__('Y-DNA Mark carriers').'</option>';
			}

			if($base_person_sexe=="F" OR ($base_person_sexe=="M" AND isset($base_person_famc) AND $base_person_famc!="")) {
				// if base person is male, only show mtDNA if there are ancestors since he can't have mtDNA descendants...
				echo $selected=""; if($dna=="mtdna") $selected="selected";
				echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
						$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='."mtdna".'&amp;chosensize='.
						$size.'&amp;chosengen='.$chosengen.'&amp;screen_mode=STAR" '.$selected.'>'.__('mtDNA Carriers only').'</option>';
				if($base_person_sexe=="F") { echo $selected="selected"; if($dna!="mtdnamark") $selected=""; }
				else { echo $selected=""; if($dna=="mtdnamark") $selected="selected";  }
				echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
						$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='."mtdnamark".'&amp;chosensize='.
						$size.'&amp;chosengen='.$chosengen.'&amp;screen_mode=STAR" '.$selected.'>'.__('mtDNA Mark carriers').'</option>';
			}
			echo '</select>';
		}
		echo '</div>';

		echo '&nbsp;&nbsp;';
		echo '&nbsp;'.__('Nr. generations').': ';
		echo '<select name="chosengen" onChange="window.location=this.value">';
			for ($i=2; $i<=15; $i++) {
				if(CMS_SPECIFIC=='Joomla') {
					echo '<option value="index.php?option=com_humo-gen&task=family&id='.$keepfamily_id.'&amp;main_person='.
					$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='.$dna.'&amp;chosensize='.
					$size.'&amp;chosengen='.$i.'&amp;screen_mode=STAR" ';
				}
				else{
					echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
					$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='.$dna.'&amp;chosensize='.
					$size.'&amp;chosengen='.$i.'&amp;screen_mode=STAR" ';
				}
				if ($i == $chosengen) echo "selected=\"selected\" ";
				echo ">".$i."</option>";
			}

			//NEW - option "All" for all generations
			echo '<option value="'.$uri_path.'family.php?id='.$keepfamily_id.'&amp;main_person='.
			$keepmain_person.'&amp;direction='.$direction.'&amp;database='.$database.'&amp;dnachart='.$dna.'&amp;chosensize='.
			$size.'&amp;chosengen=All&amp;screen_mode=STAR" ';
			if ($chosengen=="All") echo "selected=\"selected\" ";
			echo ">"."All"."</option>";
		echo '</select>';

		echo '&nbsp;&nbsp;';
		$dna_params="";
		if($dna!="none") {
			//$dna_params = '
			//	bn: "'.$base_person_name.'",
			//	bs: "'.$base_person_sexe.'",
			//	bf: "'.$base_person_famc.'",
			//	bg: "'.$base_person_gednr.'",';
			$dna_params = '&bn='.$base_person_name.'&bs='.$base_person_sexe.'&bf='.$base_person_famc.'&bg='.$base_person_gednr;
		}

		/*
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
					direction: "'.$direction.'",
					dna: "'.$dna.'",'.
					$dna_params.'
					slide: function( event, ui ) {
						$( "#amount" ).val(ui.value+1);
					}
				});
				$( "#amount" ).val($( "#slider" ).slider( "value" )+1 );
			});
			</script>
		';
		*/

		// *** 20-08-2022: renewed jQuery and jQueryUI scripts ***
		echo '
			<script>
			$(function() {
				$( "#slider" ).slider({
					value: '.(($size/5)-1).',
					min: 0,
					max: 9,
					step: 1,
					slide: function( event, ui ) {
						$( "#amount" ).val(ui.value+1);
					}
				});
				$( "#amount" ).val($( "#slider" ).slider( "value" )+1 );

				// *** Only reload page if value is changed ***
				startPos = $("#slider").slider("value");
				$("#slider").on("slidestop", function(event, ui) {
					endPos = ui.value;
					if (startPos != endPos) {
						window.location.href = "/family.php?tree_id='.$tree_id.'&id='.$keepfamily_id.'&main_person='.$keepmain_person.
							'&screen_mode=STAR&chosensize="+((endPos+1)*5)+"&chosengen='.$chosengen.
							'&direction='.$direction.'&dnachart='.$dna.'&screen_mode=STARSIZE'.$dna_params.'";
					}
					startPos = endPos;
				});

			});

			</script>
		';

		//echo '<label for="amount">Zoom in/out:</label>';
		echo '<label for="amount">'.__('Zoom level:').'</label> ';
		echo '<input type="text" id="amount" disabled="disabled" style="width:20px;border:0; color:#0000CC; font-weight:normal;font-size:115%;" />';
		echo '<div id="slider" style="float:right;width:135px;margin-top:7px;margin-right:15px;"></div>';
		echo '</div>';

	} // end if not hourglass

	for($w=0; $w < count($genarray); $w++) {
		$xvalue=$genarray[$w]["posx"];
		$yvalue=$genarray[$w]["posy"];

		$sexe_colour=''; $backgr_col = "#FFFFFF"; 
		if($genarray[$w]["sex"]=="v") {
			$sexe_colour=' ancestor_woman';
			$backgr_col = "#FBDEC0";     //"#f8bdf1";
		}
		else{
			$sexe_colour=' ancestor_man';
			$backgr_col =  "#C0F9FC";      //"#bbf0ff";
		}

		// *** Start person class and calculate privacy ***
		if (isset($genarray[$w]["gednr"]) AND $genarray[$w]["gednr"]){
			$man = $db_functions->get_person($genarray[$w]["gednr"]);
			$man_cls= New person_cls;
			$man_cls->construct($man);
			$man_privacy=$man_cls->privacy;
		}

		//echo '<div style="position:absolute; background-color:'.$bkcolor.';height:'.$vsize.'px; width:'.$hsize.'px; border:1px brown solid; left:'.$xvalue.'px; top:'.$yvalue.'px">';

		$bkgr="";  
		if(($dna=="ydnamark" OR $dna=="mtdnamark" OR $dna=="ydna" OR $dna=="mtdna") AND $genarray[$w]["dna"]==1) { 
			$bkgr = "border:3px solid #999999;background-color:".$backgr_col.";"; 
			if(isset($genarray[$w]["gednr"]) AND $genarray[$w]["gednr"]==$base_person_gednr) {  // base person
				$bkgr = "border:3px solid red;background-color:".$backgr_col.";"; 
			}
		}
		else {
			$bkgr = "border:1px solid #8C8C8C;background-color:".$backgr_col.";"; 
		}
		if($genarray[$w]["gen"]==0 AND $hourglass===true) { 
			$bkgr = "background-color:".$backgr_col.";"; 
		}
		echo '<div class="ancestorName'.$sexe_colour.'" style="'.$bkgr.'position:absolute; height:'.$vsize.'px; width:'.$hsize.'px; left:'.$xvalue.'px; top:'.$yvalue.'px;">';

		$replacement_text='';
		if($size>=25) {
			/*
			if(CMS_SPECIFIC=='Joomla') {
				$replacement_text.= '<a class="nam" href="index.php?option=com_humo-gen&task=family&id='.$genarray[$w]["fams"].'&amp;main_person='.$genarray[$w]["gednr"].'&amp;chosensize='.$size.'&amp;direction='.$direction.'&amp;screen_mode=STAR"';
			}
			else {
				$replacement_text.= '<a class="nam" href="'.CMS_ROOTPATH.'family.php?id='.$genarray[$w]["fams"].'&amp;main_person='.$genarray[$w]["gednr"].'&amp;chosensize='.$size.'&amp;direction='.$direction.'&amp;screen_mode=STAR"';
			}

			$replacement_text.= ' style="font-size:9px; text-align:center; display:block; width:100%; height:100%" ';
			$replacement_text.= 'onmouseover="mopen(event,\'m1'.$w.'\',0,0)"';
			$replacement_text.= 'onmouseout="mclosetime()">';
			*/
			if(strpos($browser_user_agent,"msie 7.0")===false) {
				if($size==50) {

					// *** Show picture ***
					if (!$man_privacy AND $user['group_pictures']=='j'){
						//  *** Path can be changed per family tree ***
						global $dataDb;
						$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
						$picture_qry=$db_functions->get_events_connect('person',$man->pers_gedcomnumber,'picture');
						// *** Only show 1st picture ***
						if (isset($picture_qry[0])){
							$pictureDb=$picture_qry[0];
							$picture=show_picture($tree_pict_path,$pictureDb->event_event,60,65);
							//$replacement_text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" height="65px">';
							//$replacement_text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'"';
							$replacement_text.='<img src="'.$picture['path'].$picture['thumb'].$picture['picture'].'" style="float:left; margin:5px;" alt="'.$pictureDb->event_text.'" width="'.$picture['width'].'"';
							//if (isset($picture['height'])) $replacement_text.=' height="'.$picture['height'].'"';
							$replacement_text.='>';
						}
					}

					//$replacement_text.= '<strong>'.$genarray[$w]["nam"].'</strong>';
					//$replacement_text.= '<span class="anc_box_name">'.$genarray[$w]["nam"].'</span>';
					$replacement_text.= '<span class="anc_box_name">'.$genarray[$w]["nam"].'</span>';
					if ($man_privacy){
						$replacement_text.= '<br>'.__(' PRIVACY FILTER').'<br>';  //Tekst privacy weergeven
					}
					else{
						//if ($man->pers_birth_date OR $man->pers_birth_place){
						if ($man->pers_birth_date){
							//$replacement_text.= '<br>'.__('*').$dirmark1.' '.date_place($man->pers_birth_date,$man->pers_birth_place);
							$replacement_text.= '<br>'.__('*').$dirmark1.' '.date_place($man->pers_birth_date,'');
						}
						//elseif ($man->pers_bapt_date OR $man->pers_bapt_place){
						elseif ($man->pers_bapt_date){
							//$replacement_text.= '<br>'.__('~').$dirmark1.' '.date_place($man->pers_bapt_date,$man->pers_bapt_place);
							$replacement_text.= '<br>'.__('~').$dirmark1.' '.date_place($man->pers_bapt_date,'');
						}

						//if ($man->pers_death_date OR $man->pers_death_place){
						if ($man->pers_death_date){
							//$replacement_text.= '<br>'.__('&#134;').$dirmark1.' '.date_place($man->pers_death_date,$man->pers_death_place);
							$replacement_text.= '<br>'.__('&#134;').$dirmark1.' '.date_place($man->pers_death_date,'');
						}
						//elseif ($man->pers_buried_date OR $man->pers_buried_place){
						elseif ($man->pers_buried_date){
							//$replacement_text.= '<br>'.__('[]').$dirmark1.' '.date_place($man->pers_buried_date,$man->pers_buried_place);
							$replacement_text.= '<br>'.__('[]').$dirmark1.' '.date_place($man->pers_buried_date,'');
						}

						if($genarray[$w]["non"]==0) { // otherwise for an unmarried child it would give the parents' marriage!
							$ownfam = $db_functions->get_family($genarray[$w]["fams"]);
							//if ($ownfam->fam_marr_date OR $ownfam->fam_marr_place){
							// *** Don't check for date. Otherwise living together persons are missing ***
							//if ($ownfam->fam_marr_date){
								//$replacement_text.= '<br>'.__('X').$dirmark1.' '.date_place($ownfam->fam_marr_date,$ownfam->fam_marr_place);

								if ($ownfam->fam_marr_date OR $ownfam->fam_marr_place){
									$replacement_text.= '<br>'.__('X');
								}
								else{
									// *** Relation ***
									$replacement_text.= '<br>'.__('&amp;');
								}

								if ($ownfam->fam_marr_date){
									$replacement_text.= $dirmark1.' '.date_place($ownfam->fam_marr_date,'').' ';
								}

								// *** Jan. 2022: Show spouse ***
								if(isset($genarray[$w]["sps"]) AND $genarray[$w]["sps"] != '') {
									if ($ownfam->fam_marr_date OR $ownfam->fam_marr_place){
									//$replacement_text.= "&nbsp;".__(' to: ')."<br>";
										$replacement_text.= __(' to: ').'<br>';
									}
									else{
										// *** Don't show 'to: ' for relations.
										$replacement_text.=' ';
									}
									$replacement_text.= '<i>'.$genarray[$w]["sps"].'</i>';
								}
							//}
						}
					}
				}
				elseif($size==45) {$replacement_text.= $genarray[$w]["nam"]; }
				elseif($size==40) {$replacement_text.= '<span class="wordwrap" style="font-size:75%">'.$genarray[$w]["short"].'</span>'; }
				elseif($size>=25 AND $size<40) {$replacement_text.= $genarray[$w]["init"]; }
			}
		}
		else {
			if(isset($genarray[$w]["fams"]) AND isset($genarray[$w]["gednr"])) {
				/*
				if(CMS_SPECIFIC=='Joomla') {
					$replacement_text.= '<a href="index.php?option=com_humo-gen&task=family&id='.$genarray[$w]["fams"].'&amp;main_person='.$genarray[$w]["gednr"].'&amp;chosensize='.$size.'&amp;direction='.$direction.'&amp;screen_mode=STAR"';
				}
				else {
					$replacement_text.= '<a href="'.CMS_ROOTPATH.'family.php?id='.$genarray[$w]["fams"].'&amp;main_person='.$genarray[$w]["gednr"].'&amp;chosensize='.$size.'&amp;direction='.$direction.'&amp;screen_mode=STAR"';
				}
				$replacement_text.= ' style="display:block; width:100%; height:100%" ';
				$replacement_text.= ' onmouseover="mopen(event,\'m1'.$w.'\',0,0)"';
				$replacement_text.= 'onmouseout="mclosetime()">';
				*/

				if(strpos($browser_user_agent,"chrome")!==false OR strpos($browser_user_agent,"safari")!==false  ) { $replacement_text.="&nbsp;"; }
				//  (Chrome and Safari need some character here - even &nbsp - or else popup won't work..!
			}
		}
		//$replacement_text.='</a>';

		// *** POP-UP box ***
		$extra_popup_text='';

		if($genarray[$w]["2nd"]==1) { $extra_popup_text.= $genarray[$w]["huw"]."<br>"; }

		if($genarray[$w]["non"]!=1) {
			// *** Start person class and calculate privacy ***
			$woman_cls=''; // prevent use of $woman_cls from previous wife if another wife is NN
			if (isset($genarray[$w]["spgednr"]) AND $genarray[$w]["spgednr"]){
				@$woman = $db_functions->get_person($genarray[$w]["spgednr"]);
				$woman_cls= New person_cls;
				$woman_cls->construct($woman);
				$woman_privacy=$woman_cls->privacy;
			}

			// *** Marriage data ***
			$extra_popup_text.= '<br>'.$genarray[$w]["htx"]."<br>";
			if($woman_cls) {
				$name=$woman_cls->person_name($woman);
				if(isset($genarray[$w]["spfams"]) AND isset($genarray[$w]["spgednr"]) AND isset($genarray[$w]["sps"])) {
					if(CMS_SPECIFIC=='Joomla') {
						$extra_popup_text.= '<a href="index.php?option=com_humo-gen&task=family&id='.$genarray[$w]["spfams"].'&amp;main_person='.$genarray[$w]["spgednr"].'">'.'<strong>'.$name["standard_name"].'</strong></a>';
					}
					else {
						// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
						$url=$woman_cls->person_url2($woman->pers_tree_id,$woman->pers_famc,$woman->pers_fams,$woman->pers_gedcomnumber);

						$extra_popup_text.= '<a href="'.$url.'">'.'<strong>'.$name["standard_name"].'</strong></a>';
					}
				}
				else {
					$extra_popup_text.= $name["standard_name"];
				}

				if ($woman_privacy){
					$extra_popup_text.= __(' PRIVACY FILTER').'<br>';  //Tekst privacy weergeven
				}
				else{
					if ($woman->pers_birth_date OR $woman->pers_birth_place){
						$extra_popup_text.= __('born').$dirmark1.' '.
						date_place($woman->pers_birth_date,$woman->pers_birth_place).'<br>'; }

					if ($woman->pers_death_date OR $woman->pers_death_place){
						$extra_popup_text.= __('died ').$dirmark1.' '.
						date_place($woman->pers_death_date,$woman->pers_death_place).'<br>'; }
				}
			}
			else {
				$extra_popup_text.= __('N.N.');
			}
		}

		if (isset($man))
			echo $man_cls->person_popup_menu($man,true,$replacement_text,$extra_popup_text);

		echo '</div>';  // div of square

		if($direction==0) { // if vertical
			// draw dotted line from first marriage to following marriages
			if(isset($genarray[$w]["2nd"]) AND $genarray[$w]["2nd"]==1) {
				$startx=$genarray[$w-1]["posx"]+$hsize+2;
					$starty=$genarray[$w-1]["posy"]+($vsize/2);
				$width=($genarray[$w]["posx"]) - ($genarray[$w-1]["posx"]+$hsize)-2;
				echo  '<div style="position:absolute;border:1px blue dashed;height:2px;width:'.$width.'px;left:'.$startx.'px;top:'.$starty.'px"></div>';
			}

			// draw line to children
			if($genarray[$w]["nrc"]!=0) {
				$startx=$genarray[$w]["posx"]+($hsize/2);
					$starty=$genarray[$w]["posy"]+$vsize+2;
				echo  '<div class="chart_line" style="position:absolute; height:'.(($vdist/2)-2).'px; width:1px; left:'.$startx.'px; top:'.$starty.'px"></div>';
			}

			// draw line to parent
			if($genarray[$w]["gen"]!=0 AND $genarray[$w]["2nd"]!=1) {
				$startx=$genarray[$w]["posx"]+($hsize/2);
				$starty=$genarray[$w]["posy"]-($vdist/2);
				echo '<div class="chart_line" style="position:absolute; height:'.($vdist/2).'px;width:1px;left:'.$startx.'px;top:'.$starty.'px"></div>';
			}

			// draw horizontal line from 1st child in fam to last child in fam
			if($genarray[$w]["gen"] != 0) {
				$parent=$genarray[$w]["par"];
				if($genarray[$w]["chd"] == $genarray[$parent]["nrc"]) { // last child in fam
					$z=$w;
					while($genarray[$z]["2nd"]==1) { //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
						$z--;
					}
						$startx=$genarray[$parent]["fst"]+($hsize/2);
						$starty=$genarray[$z]["posy"]-($vdist/2);
						$width=$genarray[$z]["posx"] - $genarray[$parent]["fst"];
						echo '<div class="chart_line" style="position:absolute; height:1px; width:'.$width.'px; left:'.$startx.'px; top:'.$starty.'px"></div>';
				}
			}
		} // end if vertical

		else { // if horizontal
			// draw dotted line from first marriage to following marriages
			if(isset($genarray[$w]["2nd"]) AND $genarray[$w]["2nd"]==1) {
				$starty=$genarray[$w-1]["posy"]+$vsize+2;
				$startx=$genarray[$w-1]["posx"]+($hsize/2);
				$height=($genarray[$w]["posy"]) - ($genarray[$w-1]["posy"]+$vsize)-2;
				echo  '<div style="position:absolute;border:1px blue dashed;height:'.$height.'px; width:3px; left:'.$startx.'px;top:'.$starty.'px"></div>';
			}

			// draw line to children
			if($genarray[$w]["nrc"]!=0) {
				$starty=$genarray[$w]["posy"]+($vsize/2);
				$startx=$genarray[$w]["posx"]+$hsize+3;
				echo '<div class="chart_line" style="position:absolute; height:1px; width:'.(($hdist/2)-2).'px; left:'.$startx.'px; top:'.$starty.'px"></div>';
			}

			// draw line to parent
			if($genarray[$w]["gen"]!=0 AND $genarray[$w]["2nd"]!=1) {
				$starty=$genarray[$w]["posy"]+($vsize/2);
				$startx=$genarray[$w]["posx"]-($hdist/2);
				echo '<div class="chart_line" style="position:absolute; width:'.($hdist/2).'px; height:1px; left:'.$startx.'px; top:'.$starty.'px"></div>';
			}

			// draw vertical line from 1st child in fam to last child in fam
			if($genarray[$w]["gen"] != 0) {
				$parent=$genarray[$w]["par"];
				if($genarray[$w]["chd"] == $genarray[$parent]["nrc"]) { // last child in fam
					$z=$w;
					while($genarray[$z]["2nd"]==1) { //if last is 2nd (3rd etc) marriage, the line has to stop at first marriage
						$z--;
					}
					$starty=$genarray[$parent]["fst"]+($vsize/2);
					$startx=$genarray[$z]["posx"]-($hdist/2);
					$height=$genarray[$z]["posy"] - $genarray[$parent]["fst"];
					echo '<div class="chart_line" style="position:absolute; width:1px; height:'.$height.'px; left:'.$startx.'px; top:'.$starty.'px"></div>';
				}
			}

		} // end if horizontal
	}

	echo '</div>'; // id=png
	echo "<br><br></div>"; // id=doublescroll

	// YB:
	// before creating the image we want to hide unnecessary items such as the help link, the menu box etc
	// we also have to set the width and height of the "png" div (this can't be set before because then the double scrollbars won't work
	// after generating the image, all those items are returned to their previous state....
	// *** 19-08-2022: script updated by Huub ***
	echo '<script type="text/javascript">';
	if($hourglass===false) {

		echo "
		function showimg() {
			document.getElementById('helppopup').style.visibility = 'hidden';
			document.getElementById('menubox').style.visibility = 'hidden';
			document.getElementById('imgbutton').style.visibility = 'hidden';
			document.getElementById('png').style.width = '".$divlen."px';
			document.getElementById('png').style.height= '".$divhi."px';

			// *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
			const el = document.querySelectorAll('.ancestorName');
			el.forEach((elItem) => {
				//elItem.style.setProperty('border-radius', 'none', 'important');
				elItem.style.setProperty('box-shadow', 'none', 'important');
			});

			// *** Previous version of html2canvas ***
			//html2canvas( [ document.getElementById('png') ], {
			//	onrendered: function( canvas ) {

				html2canvas(document.querySelector('#png')).then(canvas => {
					var img = canvas.toDataURL();

					// *** Show image at the same page ***
					//document.body.appendChild(canvas);

					document.getElementById('helppopup').style.visibility = 'visible';
					document.getElementById('menubox').style.visibility = 'visible';
					document.getElementById('imgbutton').style.visibility = 'visible';
					document.getElementById('png').style.width = 'auto';
					document.getElementById('png').style.height= 'auto';

					var newWin = window.open();
					newWin.document.open();
					newWin.document.write('<!DOCTYPE html><head></head><body>".__('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ')."<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>".__('If you have a plotter you can use its software to print the image on one large sheet.')."<br><br><img src=\"' + img + '\"></body></html>');
					newWin.document.close();
				}

			//}
			);
		}
		";
	}
	else {
		// *** Printscreen of hourglass page ***
		echo "
		function showimg() {
			document.getElementById('png').style.width = '".$divlen."px';
			document.getElementById('png').style.height= '".$divhi."px';

			// *** Change ancestorName class, DO NOT USE A _ CHARACTER IN CLASS NAME ***
			const el = document.querySelectorAll('.ancestorName');
			el.forEach((elItem) => {
				//elItem.style.setProperty('border-radius', 'none', 'important');
				elItem.style.setProperty('box-shadow', 'none', 'important');
			});

			//html2canvas( [ document.getElementById('png') ], {
			//	onrendered: function( canvas ) {
			html2canvas(document.querySelector('#png')).then(canvas => {
				var img = canvas.toDataURL();
				document.getElementById('png').style.width = 'auto';
				document.getElementById('png').style.height= 'auto';

				var newWin = window.open();
				newWin.document.open();
				newWin.document.write('<!DOCTYPE html><head></head><body>".__('Right click on the image below and save it as a .png file to your computer.<br>You can then print it over multiple pages with dedicated third-party programs, such as the free: ')."<a href=\"http://posterazor.sourceforge.net/index.php?page=download&lang=english\" target=\"_blank\">\"PosteRazor\"</a><br>".__('If you have a plotter you can use its software to print the image on one large sheet.')."<br><br><img src=\"' + img + '\"></body></html>');
				newWin.document.close();
				}
			//}
			);
		}
		";
	}
	echo "</script>"; 
?>
	<script type='text/javascript'>
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

	// here place div at bottom so there is some space under last boxes
	$last=count($genarray)-1;
	$putit=$genarray[$last]["posy"]+130;
	echo '<div style="position:absolute;left:1px;top:'.$putit.'px;">&nbsp; </div>';

}
