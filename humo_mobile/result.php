<?php include_once("header.php"); ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>HuMo-gen mobile</title>
	<link rel="stylesheet" href="themes/rene.min.css" />
	<?php
if($language["dir"]=="rtl") { 
	echo '<link rel="stylesheet" href="jquery_mobile/rtl.jquery.mobile-1.2.0.min.css" />';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script>';
	echo '<script type="text/javascript" src="jquery_mobile/rtl.jquery.mobile-1.2.0.min.js"></script>';
}
else {  
	echo '<link rel="stylesheet" href="jquery_mobile/jquery.mobile.structure-1.2.0.min.css" />';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script>';
	echo '<script type="text/javascript" src="jquery_mobile/jquery.mobile-1.2.0.min.js"></script>';
}
	?>
	<style type="text/css"></style>
</head>
<body>
	<div data-role="page" data-theme="b">
		<div data-role="header" data-theme="b">
		<h1><?php print __('Search result');?> </h1>
		<a href="./"   data-direction="reverse" class="ui-btn-left jqm-home"><?php print __('Home');?></a>
		</div>
		<div data-role="content" data-theme="b">
			<ul data-role="listview" data-inset="true">

<?php
  
	$quicksearch='';
	if (isset($_POST['quicksearch'])){
		$quicksearch=$_POST['quicksearch'];
		$_SESSION["save_quicksearch"]=$quicksearch;
	}

	// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
	$quicksearch = str_replace(',','',$quicksearch); // in case someone entered "Mons, Huub"
	$quicksearch=str_replace(' ', '%', $quicksearch);

	// one can enter "Huub Mons", "Mons Huub", "Huub van Mons", "van Mons, Huub", "Mons, Huub van" and even "Mons van, Huub" ...
	$query= "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."'
		AND(
		CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$quicksearch%' 
		OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%$quicksearch%' 
		OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%$quicksearch%' 
		OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%$quicksearch%'
		)";
	$query.=" ORDER BY pers_lastname, pers_firstname ASC ";   
	$data = $dbh->query($query);
	$num_rows = $data->rowCount();
	
	if ($num_rows==NULL){
		print __('No results found.');
	}
	else{
		//while($info = mysql_fetch_array( $data )){
		while($info = $data->fetch()){
			$text=$info['pers_gedcomnumber'];
			print popup($text);
		}
		print '<li data-role="list-divider">  </li>';
	}
    print '</ul>';
 
include_once("footer.php");
?>