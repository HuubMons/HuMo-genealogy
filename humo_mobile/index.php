<?php
include_once("header.php");

// get some general settings to use later on (email, owner, etc) 
$details = $dbh->query("SELECT tree_email, tree_owner FROM humo_trees WHERE tree_prefix = '".$_SESSION['tree_prefix']."'");
$details_arr=$details->fetch();

$homepage= $dbh->query("SELECT setting_value FROM humo_settings WHERE setting_variable='homepage'");
$homepage_arr=$homepage->fetch();

$homepage_name= $dbh->query("SELECT setting_value FROM humo_settings WHERE setting_variable='homepage_description'");
$homepage_name_arr=$homepage_name->fetch();

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $homepage_name_arr['setting_value']; ?></title>
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
	<style type="text/css">
		.img {
			padding-left: 70px;
		}
	</style>
</head>
 
<body>
	<div data-role="page" data-theme="b" id="home">
		<!-- YB: for some reason using H1 here crops the title to "HuMo-gen Mo.." so used styling instead-->
		<div style="text-align:center;line-height:2em;" data-role="header" data-theme="b">
			<?php echo $homepage_name_arr['setting_value'];?> 
		</div>

		<div style="text-align:center;" data-role="content">
			<?php print '<span style="font-weight:bold;font-size:120%;">'.__('Search in family tree').'</span><br>';?>
			<?php print __('Choose tree:');?>
<?php
	if (!$bot_visit){
		$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_prefix_result2 = $dbh->query($sql);
		$num_rows = $tree_prefix_result2->rowCount();
		if ($num_rows>1){

			echo ' <form method="POST" action="index.php" style="display : inline;" id="top_tree_select">';	
			echo '<select size=1 name="database" onChange="this.form.submit();" >';
			$count=0;
			while ($tree_prefixDb=$tree_prefix_result2->fetch(PDO::FETCH_OBJ)){
				// *** Check if family tree is shown or hidden for user group ***
				$hide_tree_array2=explode(";",$user['group_hide_trees']);
				$hide_tree2=false;
				for ($x=0; $x<=count($hide_tree_array2)-1; $x++){
					if ($hide_tree_array2[$x]==$tree_prefixDb->tree_id){ $hide_tree2=true; }
				}
				if ($hide_tree2==false){
					$selected='';
					if (isset($_SESSION['tree_prefix'])){
						if ($tree_prefixDb->tree_prefix==$_SESSION['tree_prefix']){ $selected=' SELECTED'; }
					}
					else {
						if($count==0) { $_SESSION['tree_prefix'] = $tree_prefixDb->tree_prefix; $selected=' SELECTED'; }
					}
					$treetext=show_tree_text($tree_prefixDb->tree_prefix, $selected_language);
					echo '<option value="'.$tree_prefixDb->tree_prefix.'"'.$selected.'>'.@$treetext["name"].'</option>';
					$count++;
				}
			}
			echo '</select>';
			echo '</form>';
		}
	}

	print '<form method="post" action="result.php" data-ajax="false">';
	print '<div data-role="fieldcontain">';
	print '<label>'.__('Enter name or part of name').' </label>';
	print '<input type="hidden" name="index_list" value="quicksearch">';
	$quicksearch='';
	if (isset($_POST['quicksearch'])){
		$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
		$_SESSION["save_quicksearch"]=$quicksearch;
	}
	if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
	print '<input type="text" name="quicksearch" value="'.$quicksearch.'">';
	print '</div>';
	print ' <input type="submit" value="'.__('Search').'" id="submit" data-theme="b">';
	print "</form>";

	print '<div class="ui-block-a" style="width:59%;float:left;">';
	if (!$bot_visit){ 
		if(isset($_POST['language'])) $selected_language=$_POST['language'];
		echo '<form method="POST" action="index.php" style="display : inline;" id="flag_select">';	
		echo '<select size=1 name="language" onChange="this.form.submit();" >';
		for ($i=0; $i<count($language_file); $i++){
			// *** Get language name *** 
			$selected=""; if ($language_file[$i] == $selected_language) { $selected = " selected "; }
			include('../languages/'.$language_file[$i].'/language_data.php');
			echo '<option value="'.$language_file[$i].'"'.$selected.'>'.$language["name"].'</option>';
		}  
		echo '</select></form>';
		include('../languages/'.$selected_language.'/language_data.php');
	}	
	print '</div>';
	if(!$user["user_name"]) { 
		print '<div style="width:39%;float:right;" class="ui-block-a"><a data-role="button" href="mob_login.php">'.__('Login').'</a></div>';
	}
	else {
		print '<div style="width:39%;float:right;" class="ui-block-a"><a data-role="button" href="index.php?log_off=1">'.__('Logoff').'</a></div>';
	}
?>
		</div>

		<div style="height:40px"; data-role="footer" data-theme="b">

			<!-- E-mail button, disabled, because e-mail address is readable in the code! -->
				<!-- <div style="margin-left:17px;float:left"; class="ui-block-a"> -->
				<!-- <a data-role="button" rel="external" href="mailto: -->
				<?php //echo $details_arr['tree_email'];?>
				<!-- "> -->
				<?php //print __(//'E-mail'); ?>
				<!-- </a> -->
			<!-- </div> -->

			<?php 
			echo '<div style="width:110px;margin-left:4%;" class="ui-block-a"><a data-role="button" href="about.php">'.ucfirst(strtolower(__('INFO'))).'</a></div>';
			?>

			<div style="margin-right:17px;float:right"; class="ui-block-a"><a data-role="button" rel="external" href="<?php echo $homepage_arr['setting_value'];?>?mobile=1"><?php print __('Website'); ?></a></div>
<?php include_once("footer.php"); ?>