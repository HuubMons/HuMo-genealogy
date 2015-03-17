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

$desc_asc=" ASC "; $sort_desc=0;
if(isset($_GET['sort_desc'])) {
	$desc_asc=" ASC "; $sort_desc=0;
	if($_GET['sort_desc'] == 1) { $desc_asc=" DESC "; $sort_desc=1; }
}

$order_sources='title';
if(isset($_GET['order_sources'])) {
	if ($_GET['order_sources']=='title') $order_sources='title';
	if ($_GET['order_sources']=='date') $order_sources='date';
	if ($_GET['order_sources']=='place') $order_sources='place';
}
if($order_sources=="title") {
	// *** Default querie: order by title ***
	//$querie="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."sources";
	$querie="SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'";
	// *** Check user group is restricted sources can be shown ***
	if ($user['group_show_restricted_source']=='n'){ $querie.=" AND (source_status!='restricted' OR source_status IS NULL)"; }
	$querie.=" ORDER BY source_title".$desc_asc;
}
if($order_sources=="date") {
	// *** Check user group is restricted sources can be shown ***
	/*
	$querie="SELECT source_status, source_id, source_gedcomnr, source_title, source_date, source_place,
	right(source_date,4) as year,
	date_format( str_to_date( substring(source_date,4,3),'%b' ),'%m') as month,
	date_format( str_to_date( left(source_date,2),'%d' ),'%d') as day
	FROM ".$tree_prefix_quoted."sources";
	if ($user['group_show_restricted_source']=='n'){ $querie.=" WHERE source_status!='restricted' OR source_status IS NULL"; }
	$querie.=" ORDER BY year, month, day";
	*/
	$querie="SELECT source_status, source_id, source_gedcomnr, source_title, source_date, source_place,
	CONCAT(right(source_date,4),
		date_format( str_to_date( substring(source_date,4,3),'%b' ),'%m'),
		date_format( str_to_date( left(source_date,2),'%d' ),'%d') )
		as year
	FROM humo_sources WHERE source_tree_id='".$tree_id."'";
	//FROM ".$tree_prefix_quoted."sources";
	if ($user['group_show_restricted_source']=='n'){ $querie.=" AND (source_status!='restricted' OR source_status IS NULL)"; }
	$querie.=" ORDER BY year".$desc_asc;
}
if($order_sources=="place") {
	//$querie="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."sources";
	$querie="SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'";
	// *** Check user group is restricted sources can be shown ***
	if ($user['group_show_restricted_source']=='n'){ $querie.=" AND (source_status!='restricted' OR source_status IS NULL)"; }
	$querie.=" ORDER BY source_place".$desc_asc;
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
	if (isset($_GET['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_GET['order_sources'].'&sort_desc='.$sort_desc; }
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
			if (isset($_GET['order_sources'])){ $line_pages.= "&amp;order_sources=".$_GET['order_sources'].'&sort_desc='.$sort_desc; }
			$line_pages.=  "\"> $i</a>";
		}
	}
}

// "=>"
$calculated=($i-1)*$count_sources;
if ($calculated<$all_sources->rowCount()){
	$line_pages.=  "<a href=\"".$_SERVER['PHP_SELF']."?start=$i&amp;item=$calculated";
	if (isset($_GET['order_sources'])){ $line_pages.=  "&amp;order_sources=".$_GET['order_sources'].'&sort_desc='.$sort_desc; }
	$line_pages.=  "\"> =&gt;</a>";
}

echo '<div class=index_list1>'.$line_pages.'</div><br>';

	echo '<table class="humo index_table" align="center">';
		echo '<tr class=table_headline>';
			echo '<th colspan="3">'.__('Source').'</th>';
		echo '</tr>';

		echo '<tr class=table_headline>';
			$style=''; $sort_reverse=$sort_desc; $img='';
			if ($order_sources=="title"){
				$style=' style="background-color:#ffffa0"';
				$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
			}
			echo '<th><a href="sources.php?database='.$_SESSION['tree_prefix'].'&start=1&item=0&order_sources=title&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Title').' <img src="images/button3'.$img.'.png"></a></th>';

			$style=''; $sort_reverse=$sort_desc; $img='';
			if ($order_sources=="date"){
				$style=' style="background-color:#ffffa0"';
				$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
			}
			echo '<th><a href="sources.php?database='.$_SESSION['tree_prefix'].'&start=1&item=0&order_sources=date&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Date').' <img src="images/button3'.$img.'.png"></a></th>';

			$style=''; $sort_reverse=$sort_desc; $img='';
			if ($order_sources=="place"){
				$style=' style="background-color:#ffffa0"';
				$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
			}
			echo '<th><a href="sources.php?database='.$_SESSION['tree_prefix'].'&start=1&item=0&order_sources=place&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Place').' <img src="images/button3'.$img.'.png"></a></th>';
		echo '</tr>';

		while (@$sourceDb=$source->fetch(PDO::FETCH_OBJ)){
			echo '<tr><td><a href="'.CMS_ROOTPATH.'source.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$sourceDb->source_gedcomnr.'">';
			// *** Aldfaer sources don't have a title! ***
			if ($sourceDb->source_title){ echo $sourceDb->source_title; } else { echo $sourceDb->source_text; }
			echo '</a></td>'; 

			echo '<td>'.date_place($sourceDb->source_date, '').'</td>';
			echo '<td>'.$sourceDb->source_place.'</td>';
			echo '</tr>';
		}

	echo '</table>';

echo '<br><div class=index_list1>'.$line_pages.'</div>';

//echo '</div>';

include_once(CMS_ROOTPATH."footer.php");
?>