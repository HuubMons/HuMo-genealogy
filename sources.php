<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// *** Check user authority ***
if ($user['group_sources']!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");

@set_time_limit(300);

echo '<div class=index_list1>';

if (isset($_POST['order_sources']) OR isset($_GET['order_sources'])){
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display : inline">';
	print '<input type="Submit" name="geenorder_sources" value="'.__('Order by title').'">';
	print '</form>';

	// *** Check user group is restricted sources can be shown ***
	$querie="SELECT source_status, source_id, source_gedcomnr, source_title, source_date, source_place,
	right(source_date,4) as year,
	date_format( str_to_date( substring(source_date,4,3),'%b' ),'%m') as month,
	date_format( str_to_date( left(source_date,2),'%d' ),'%d') as day
	FROM ".$tree_prefix_quoted."sources";
	if ($user['group_show_restricted_source']=='n'){ $querie.=" WHERE source_status!='restricted' OR source_status IS NULL"; }
	$querie.=" ORDER BY year, month, day";
}
else{
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display : inline">';
	print '<input type="Submit" name="order_sources" value="'.__('Order by date').'">';
	print '</form>';

	// *** Check user group is restricted sources can be shown ***
	$querie="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."sources";
	if ($user['group_show_restricted_source']=='n'){ $querie.=" WHERE source_status!='restricted' OR source_status IS NULL"; }
	$querie.=" ORDER BY source_title";
}

// *** Pages ***
$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }
$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
$count_sources=$humo_option['show_persons'];
// *** All sources query ***
$all_sources=$dbh->query($querie);
$source=$dbh->query($querie." LIMIT ".safe_text($item).",".$count_sources);
$line_pages=__('Page');

// "<="
if ($start>1){
	$start2=$start-20;
	$calculated=($start-2)*$count_sources;
	$line_pages.= "<a href=\"".$_SERVER['PHP_SELF']."?start=$start2&amp;item=$calculated";
	if (isset($_GET['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_GET['order_sources']; }
	if (isset($_POST['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_POST['order_sources']; }
	$line_pages.=  "\">&lt;= </a>";
}
if ($start<=0){$start=1;}

// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
for ($i=$start; $i<=$start+19; $i++) {
	$calculated=($i-1)*$count_sources;
	if ($calculated<$all_sources->rowCount()){
		if ($item==$calculated){
			$line_pages.=  " <b>$i</B>";
		}
		else {
			$line_pages.=  "<a href=\"".$_SERVER['PHP_SELF']."?item=$calculated&amp;start=$start";
			if (isset($_GET['order_sources'])){ $line_pages.= "&amp;order_sources=".$_GET['order_sources']; }
			if (isset($_POST['order_sources'])){ $line_pages.= "&amp;order_sources=".$_POST['order_sources']; }
			$line_pages.=  "\"> $i</a>";
		}
	}
}

// "=>"
$calculated=($i-1)*$count_sources;
if ($calculated<$all_sources->rowCount()){
	$line_pages.=  "<a href=\"".$_SERVER['PHP_SELF']."?start=$i&amp;item=$calculated";
	if (isset($_GET['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_GET['order_sources']; }
	if (isset($_POST['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_POST['order_sources']; }
	$line_pages.=  "\"> =&gt;</a>";
}

echo ' '.$line_pages."<br>\n";

	echo '<div class=index_list2>';
		//while (@$sourceDb=mysql_fetch_object($source)){
		while (@$sourceDb=$source->fetch(PDO::FETCH_OBJ)){
			print '<a href="'.CMS_ROOTPATH.'source.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$sourceDb->source_gedcomnr.'">';
			// *** Aldfaer sources don't have a title! ***
			if ($sourceDb->source_title){ echo $sourceDb->source_title; } else { echo $sourceDb->source_text; }
			echo '</a> '.date_place($sourceDb->source_date, $sourceDb->source_place).'<br>';
		}
	echo '</div>';
	
echo '<br>'.$line_pages;

echo '</div>';

include_once(CMS_ROOTPATH."footer.php");
?>