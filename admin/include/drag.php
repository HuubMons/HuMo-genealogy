<?php
session_start();
//error_reporting(E_ALL);

if (isset($_SESSION['admin_tree_prefix'])){
	if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "../../");

	$ADMIN=TRUE; // *** Override "no database" message for admin ***
	include_once(CMS_ROOTPATH."include/db_login.php"); // *** Database login ***
	include_once(CMS_ROOTPATH."include/safe.php");

	$gedcom_date=strtoupper(date("d M Y"));
	$gedcom_time=date("H:i:s");
	$tree_prefix=safe_text($_SESSION['admin_tree_prefix']);
	$drag_kind=safe_text($_GET["drag_kind"]);
	
	if($drag_kind=="children") {
		$chldstring = safe_text($_GET['chldstring']);
		$sql="UPDATE humo_families SET
		fam_children='".$chldstring."',
		fam_changed_date='".$gedcom_date."',
		fam_changed_time='".$gedcom_time."'
		WHERE fam_id='".safe_text($_GET["family_id"])."'";
		$result=$dbh->query($sql);
	}
	if($drag_kind=="media") {
		$mediastring = safe_text($_GET['mediastring']);	
		$media_arr = explode(";",$mediastring);
		for($x = 0 ; $x<count($media_arr); $x++) {
			//$sql="UPDATE ".$tree_prefix."events SET 
			$sql="UPDATE humo_events SET 
			event_order='".($x+1)."', 
			event_changed_date='".$gedcom_date."', 
			event_changed_time='".$gedcom_time."' 
			WHERE event_id='".$media_arr[$x]."'";
			$result=$dbh->query($sql);
		}
	}

}
?>