<?php

class db_tree_text
{
	private $db;

	public function __construct($databaseConnection)
	{
		$this->db = $databaseConnection; // db_login actualy
	}

	public function show_tree_text($tree_id, $lang = 'default')
	{
		// *** Check for tree texts in selected language ***
		$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND treetext_language='" . $lang . "' WHERE tree_id='" . $tree_id . "'";
		$result = $this->db->query($sql);
		$text = $result->fetch(PDO::FETCH_OBJ);
		if (isset($text->treetext_name)) {
			return $this->_textObjectToTextArray($text);
		}

		// *** Final try to show some texts ***
		$sql = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND treetext_name LIKE '_%'
		WHERE tree_id='" . safe_text_db($tree_id) . "'";
		$result = $this->db->query($sql);
		$text = $result->fetch(PDO::FETCH_OBJ);
		if (isset($text->treetext_name)) {
			return $this->_textObjectToTextArray($text);
		}

		return $this->_textObjectToTextArray(new stdClass());
	}

	private function _textObjectToTextArray(object $text): array
	{
		$text_array['id'] = $text->treetext_id ?? null;
		$text_array['name'] = $text->treetext_name ?? '';
		$text_array['mainmenu_text'] = $text->treetext_mainmenu_text ?? '';
		$text_array['mainmenu_source'] = $text->treetext_mainmenu_source ?? '';
		$text_array['family_top'] = $text->treetext_family_top ?? '';
		$text_array['family_footer'] = $text->treetext_family_footer ?? '';

		return $text_array;
	}
}
