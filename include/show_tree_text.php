<?php
function show_tree_text($tree_prefix,$selected_language){
	global $dbh, $dataDb;

	// *** Standard tree text values ***
	$treetext_array['name']=__('NO NAME');
	$treetext_array['mainmenu_text']='';
	$treetext_array['mainmenu_source']='';
	//$treetext_array['family_top']='Family page';
	$treetext_array['family_top']='';
	$treetext_array['family_footer']='';

	$found_text=false;

	// *** NEW: Default tree texts ***
	$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND treetext_language='default' WHERE tree_prefix='".safe_text($tree_prefix)."'";
	$datasql = $dbh->query($sql);
	@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
	if (isset($dataDb->treetext_name)){
		$treetext_array['id']=$dataDb->treetext_id;
		$treetext_array['name']=$dataDb->treetext_name;
		$treetext_array['mainmenu_text']=$dataDb->treetext_mainmenu_text;
		$treetext_array['mainmenu_source']=$dataDb->treetext_mainmenu_source;
		$treetext_array['family_top']=$dataDb->treetext_family_top;
		$treetext_array['family_footer']=$dataDb->treetext_family_footer;

		$found_text=true;
	}

	// *** Check for tree texts in selected language ***
	$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND treetext_language='".$selected_language."' WHERE tree_prefix='".safe_text($tree_prefix)."'";
	$datasql = $dbh->query($sql);
	@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
	if (isset($dataDb->treetext_name)){
		$treetext_array['id']=$dataDb->treetext_id;
		$treetext_array['name']=$dataDb->treetext_name;
		$treetext_array['mainmenu_text']=$dataDb->treetext_mainmenu_text;
		$treetext_array['mainmenu_source']=$dataDb->treetext_mainmenu_source;
		$treetext_array['family_top']=$dataDb->treetext_family_top;
		$treetext_array['family_footer']=$dataDb->treetext_family_footer;

		$found_text=true;
	}

	if ($found_text===false){
		// *** Final try to show some texts ***
		$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND treetext_name LIKE '_%'
			WHERE tree_prefix='".safe_text($tree_prefix)."'";
		@$datasql = $dbh->query($sql);
		@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
		if (isset($dataDb->treetext_name)){
			$treetext_array['id']=$dataDb->treetext_id;
			$treetext_array['name']=$dataDb->treetext_name;
			$treetext_array['mainmenu_text']=$dataDb->treetext_mainmenu_text;
			$treetext_array['mainmenu_source']=$dataDb->treetext_mainmenu_source;
			$treetext_array['family_top']=$dataDb->treetext_family_top;
			$treetext_array['family_footer']=$dataDb->treetext_family_footer;
		}
	}

	return $treetext_array;
}
?>