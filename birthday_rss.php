<?php
// author: Louis Ywema
// date: 31-07-2007
// purpose: rssfeed present birthday's
// RSS link in html-page:
// <link rel="alternate" type="application/rss+xml" href="birthday_rss.php" title="RSS feed birthdays" >

// Update by: Huub Mons.
// Added multiple languages.

session_start();

define("CMS_ROOTPATH", "");

include_once(CMS_ROOTPATH."include/db_login.php");
include_once(CMS_ROOTPATH."include/settings_global.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/safe.php");
include_once(CMS_ROOTPATH."include/settings_user.php");

include_once(CMS_ROOTPATH."include/db_functions_cls.php");
$db_functions = New db_functions;

// *** Set timezone ***
include_once(CMS_ROOTPATH."include/timezone.php");
timezone();

$today = date("d M Y");
$month_name = date("F");
$today_day = date("d");
$month_number = date ("M");
$year = date("Y");
$newline ="\n";

/*
header ("Content-Type: application/rss+xml; charset=UTF-8");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<rss version=\"2.0\"
	xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
	xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"
	xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\">\n";
*/

$language_rss="en";
if (isset($_GET['lang']) AND file_exists(CMS_ROOTPATH.'languages/'.$_GET['lang'].'/'.$_GET['lang'].'.mo')){ $language_rss=$_GET['lang']; }
// *** Extra check if file exists ***
if (file_exists(CMS_ROOTPATH.'languages/'.$language_rss.'/'.$language_rss.'.mo')){
	// *** .mo language text files ***
	include_once(CMS_ROOTPATH."languages/gettext.php");
	// *** Load ***
	$_SESSION["language_selected"]=$language_rss;
	Load_default_textdomain();
}

header("Content-Type: application/xml; charset=iso-8859-1");
echo '<?xml version="1.0" encoding="iso-8859-1"?>';
echo '<rss version="2.0">';

//  channel info
echo "<channel>\n";
echo "<title>".__('Birthday calendar')."</title>".$newline;
echo '<link>'.$humo_option["rss_link"].'</link>'.$newline;
echo "<description>".__('Whose birthday is it today?')."</description>".$newline;
echo "<language>".__('En-en')."</language>".$newline;

$counter=0;
$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
	// *** Check is family tree is shown or hidden for user group ***
	$hide_tree_array=explode(";",$user['group_hide_trees']);
	$hide_tree=false;
	for ($x=0; $x<=count($hide_tree_array)-1; $x++){
		if ($hide_tree_array[$x]==$dataDb->tree_id){ $hide_tree=true; }
	}
	if ($hide_tree==false){
		$sql="SELECT *,
			substring(pers_birth_date,1,2) as birth_day,
			substring(pers_birth_date,8,4) as birth_year,
			substring(pers_death_date,8,4) as death_year
			FROM humo_persons
			WHERE pers_tree_id='".$dataDb->tree_id."'
			AND (pers_birth_date!=''
			AND (substring(pers_birth_date,3,3) = '$month_number' AND CONCAT('0',substring(pers_birth_date,1,1)) = '$today_day') 
			OR (substring(pers_birth_date,4,3) = '$month_number' AND substring(pers_birth_date,1,2)='$today_day')
			) order by pers_lastname";
		$query = $dbh->query($sql);
		while($record = $query->fetch(PDO::FETCH_OBJ)) {
			$person_cls1 = New person_cls;
			$person_cls1->construct($record);
			$privacy=$person_cls1->privacy;
			if($privacy!=1) {
				$death_date = $record->pers_death_date;
				$calculated_age='';
				if ($death_date !=''){
					$death_date =' (&#8224; '.$death_date.')';
					if ($record->death_year-$record->birth_year < 120){
						$calculated_age = ' ('.($record->death_year-$record->birth_year).')';
					}
				}
				else{
					$death_date = '';
					if ($year - $record->birth_year<120){
						$calculated_age = ' ('.($year - $record->birth_year).')';
					}
				}

				$person_cls = New person_cls;
				$name=$person_cls->person_name($record);
				$title = $name["standard_name"];

				$title = str_replace('&', '&amp;', $title);  // Los & teken niet toegestaan in RSS
				$title.=$calculated_age.$death_date;

				$url = CMS_ROOTPATH.'family.php?database='.$dataDb->tree_prefix.'&amp;id='.$record->pers_indexnr;

				// show content
				echo "<item>".$newline;
				echo "<title>".$title."</title>".$newline;
				echo "<link>".$humo_option["rss_link"]."/".$url."</link>".$newline;
				echo "</item>".$newline;
				$counter++;
			}
		}  // close channel and rss

	} // End check if tree is hidden

}  // End of multiple family trees

// *** No results found ***
if ($counter==0){
	echo "<item>".$newline;
	echo "<title>".__('No results found.').$today_day.'-'.$month_number.'-'.$year."</title>".$newline;
	echo "</item>".$newline;
}

echo "</channel>".$newline;
echo "</rss>";
?>