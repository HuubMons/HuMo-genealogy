<?php
/**
 * This is the main web entry point for HuMo-gen.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * http://www.huubmons.nl/software/
 *
 * ----------
 *
 * Copyright (C) 2008-2016 Huub Mons,
 * Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
 * Reni Janssen, Yossi Beck
 * and others.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// ***********************************************************************************************
// ** Main index class ***
// ***********************************************************************************************
include_once(CMS_ROOTPATH."include/mainindex_cls.php");
$mainindex = new mainindex_cls();

// *** Show slideshow ***
if (isset($humo_option["slideshow_show"]) AND $humo_option["slideshow_show"]=='y'){
	// *** Used inline CSS, so it will be possible to use other CSS style (can be used for future slideshow options) ***

//$test=false;
//if ($test)
	echo '<style>
	/* CSS3 slider for mainmenu */
	/* @import url(http://fonts.googleapis.com/css?family=Istok+Web); */
	@keyframes slidy {
		0% { left: 0%; }
		20% { left: 0%; }
		25% { left: -100%; }
		45% { left: -100%; }
		50% { left: -200%; }
		70% { left: -200%; }
		75% { left: -300%; }
		95% { left: -300%; }
		100% { left: -400%; }
	}
	/* body, figure { */
	figure {
		margin: 0;
		/*	font-family: Istok Web, sans-serif; */
		font-weight: 100;
		
		/* height:250px; */
	}
	div#captioned-gallery {
		width: 100%; overflow: hidden; 
		margin-top: -17px;
	}
	figure.slider { 
		position: relative; width: 500%; 
		font-size: 0; animation: 30s slidy infinite; 
	}
	figure.slider figure { 
		width: 20%; height: auto;
		display: inline-block;  position: inherit; 
	}
	figure.slider img { width: 100%; height: auto; }
	figure.slider figure figcaption {
		position: absolute; bottom: 10px;
		background: rgba(0,0,0,0.4);
		color: #fff; width: 100%;
		font-size: 1.2rem; padding: .6rem;
		text-shadow: 2px 2px 4px #000000; 
	}
	/* end of CSS3 slider */
	</style>';

	echo '<div id="captioned-gallery">';

		echo '<figure class="slider">';
			echo '<figure>';
				$slideshow_01=explode('|',$humo_option["slideshow_01"]);
				if ($slideshow_01[0] AND file_exists($slideshow_01[0])){
					echo '<img src="'.$slideshow_01[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_01[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 01</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_02=explode('|',$humo_option["slideshow_02"]);
				if ($slideshow_02[0] AND file_exists($slideshow_02[0])){
					echo '<img src="'.$slideshow_02[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_02[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 02</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_03=explode('|',$humo_option["slideshow_03"]);
				if ($slideshow_03[0] AND file_exists($slideshow_03[0])){
					echo '<img src="'.$slideshow_03[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_03[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 03</figcaption>';
				}
			echo '</figure>';

			echo '<figure>';
				$slideshow_04=explode('|',$humo_option["slideshow_04"]);
				if ($slideshow_04[0] AND file_exists($slideshow_04[0])){
					echo '<img src="'.$slideshow_04[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_04[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 04</figcaption>';
				}
			echo '</figure>';

			// *** 5th picture must be the same as 1st picture ***
			echo '<figure>';
				$slideshow_01=explode('|',$humo_option["slideshow_01"]);
				if ($slideshow_01[0] AND file_exists($slideshow_01[0])){
					echo '<img src="'.$slideshow_01[0].'" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">'.$slideshow_01[1].'</figcaption>';
				}else{
					echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
					echo '<figcaption class="mobile_hidden">Missing image 01</figcaption>';
				}
			echo '</figure>';

		echo '</figure>';
	echo '</div>';


// *** Fading slideshow: doesn't work properly yet... ***
$test=false;
if ($test)
	echo '<style>
	.slider2 {
		width:100%;
		/* max-width: 300px; */
		height: 180px;
		/* margin: 20px auto; */
		position: relative;
		margin-top: -17px;
	}
	.slide1,.slide2,.slide3,.slide4,.slide5 {
		position: absolute;
		width: 100%;
		/* height: 100%; */
	}

	.slide1 {
	  /* background: url(http://media.dunkedcdn.com/assets/prod/40946/580x0-9_cropped_1371566801_p17tbs0rrjqdt1u4dnk94fe4b63.jpg)no-repeat center; */
		  background-size: cover;
		animation:fade 8s infinite;
	-webkit-animation:fade 8s infinite;
	}
	.slide2 {
	  /* background: url(http://media.dunkedcdn.com/assets/prod/40946/580x0-9_cropped_1371565525_p17tbqpu0d69c21hetd77dh483.jpeg)no-repeat center; */
		  background-size: cover;
		animation:fade2 8s infinite;
	-webkit-animation:fade2 8s infinite;
	}
	.slide3 {
	 /*   background: url(http://media.dunkedcdn.com/assets/prod/40946/580x0-9_cropped_1371564896_p17tbq6n86jdo3ishhta3fv1i3.jpg)no-repeat center; */
		  background-size: cover;
		animation:fade3 8s infinite;
	-webkit-animation:fade3 8s infinite;
	}
	@keyframes fade{
		0%   {opacity:1}
		33.333% { opacity: 0}
		66.666% { opacity: 0}
		100% { opacity: 1}
	}
	@keyframes fade2{
		0%   {opacity:0}
		33.333% { opacity: 1}
		66.666% { opacity: 0 }
		100% { opacity: 0}
	}
	@keyframes fade3{
		0%   {opacity:0}
		33.333% { opacity: 0}
		66.666% { opacity: 1}
		100% { opacity: 0}
	}
	</style>';
/*
	echo '<div class="slider2">';
		echo '<div class="slide1">';
			$slideshow_01=explode('|',$humo_option["slideshow_01"]);
			if ($slideshow_01[0] AND file_exists($slideshow_01[0])){
				//echo '<img src="'.$slideshow_01[0].'" height="174" width="946" alt="">';
				echo '<img src="'.$slideshow_01[0].'" alt="" width="100%">';
				echo '<figcaption class="mobile_hidden">'.$slideshow_01[1].'</figcaption>';
			}else{
				echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
				echo '<figcaption class="mobile_hidden">Missing image 01</figcaption>';
			}
		echo '</div>';

		echo '<div class="slide2">';
			$slideshow_02=explode('|',$humo_option["slideshow_02"]);
			if ($slideshow_02[0] AND file_exists($slideshow_02[0])){
				//echo '<img src="'.$slideshow_02[0].'" height="174" width="946" alt="">';
				echo '<img src="'.$slideshow_02[0].'" alt="" width="100%">';
				echo '<figcaption class="mobile_hidden">'.$slideshow_02[1].'</figcaption>';
			}else{
				echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
				echo '<figcaption class="mobile_hidden">Missing image 02</figcaption>';
			}
		echo '</div>';

		echo '<div class="slide3">';
			$slideshow_03=explode('|',$humo_option["slideshow_03"]);
			if ($slideshow_03[0] AND file_exists($slideshow_03[0])){
				//echo '<img src="'.$slideshow_03[0].'" height="174" width="946" alt="">';
				echo '<img src="'.$slideshow_03[0].'"  alt="" width="100%">';
				echo '<figcaption class="mobile_hidden">'.$slideshow_03[1].'</figcaption>';
			}else{
				echo '<img src="images/missing-image_large.jpg" height="174" width="946" alt="">';
				echo '<figcaption class="mobile_hidden">Missing image 03</figcaption>';
			}
		echo '</div>';

	echo '</div>';
*/
}

// *** Replace the main index by an own CMS page ***
if (isset($humo_option["main_page_cms_id_".$selected_language])) {  
	if ($humo_option["main_page_cms_id_".$selected_language] == "") {
		include_once(CMS_ROOTPATH."include/mainindex_cls.php");
		$mainindex = new mainindex_cls();
		echo $mainindex->show_tree_index();
	}
	else {
		echo '<div id="mainmenu_centerbox">';
			// *** Show page ***
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
				WHERE page_id='".$humo_option["main_page_cms_id_".$selected_language]."' AND page_status!=''");
			$cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ);
			echo $cms_pagesDb->page_text;
		echo '</div>';
	}
}
elseif (isset($humo_option["main_page_cms_id"]) AND $humo_option["main_page_cms_id"]){
	echo '<div id="mainmenu_centerbox">';

		// *** Show page ***
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id"]."' AND page_status!=''");
		$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
		echo $cms_pagesDb->page_text;

	echo '</div>';
}
else{
	// *** Show default HuMo-gen homepage ***
	echo $mainindex->show_tree_index();
}

// *** Show HuMo-gen footer ***
echo $mainindex->show_footer();

include_once(CMS_ROOTPATH."footer.php");
?>