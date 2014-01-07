<?php
function database_name($tree_prefix,$selected_language) {
	global $db, $dataDb, $language;

	$dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND humo_tree_texts.treetext_language='".$selected_language."'
		WHERE tree_prefix='".safe_text($tree_prefix)."'";
	@$datasql = mysql_query($dataqry,$db);
	@$dataDb=mysql_fetch_object($datasql);

	// *** If no name is given for this language, try to find another name **
	$treetext_name=__('NO NAME');
	if (isset($dataDb->treetext_name)){ 
		$treetext_name=$dataDb->treetext_name;
	}
	else{
		$data2qry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND treetext_name LIKE '_%'
			WHERE tree_prefix='".safe_text($tree_prefix)."'";
		@$data2sql = mysql_query($data2qry,$db);
		@$data2Db=mysql_fetch_object($data2sql);
		if (isset($data2Db->treetext_name)){ $treetext_name=$data2Db->treetext_name; }
	}
	return $treetext_name;
}
?>