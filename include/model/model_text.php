<?php

require __DIR__ . '/../database_function.php';

class model_text extends database_function
{
	/**
	 * Get a single text from database.
	 */
	public function get_text(string $text_gedcomnr): object
	{
		$sql = "SELECT * FROM humo_texts WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr";

		$stmt = $this->db->prepare($sql);

		if ($stmt->execute([
			':text_tree_id' => $this->tree_id,
			':text_gedcomnr' => $text_gedcomnr
		])) {
			return $stmt->fetch(PDO::FETCH_OBJ);
		}

		throw new Exception("No text found with gedcom number $text_gedcomnr", 1);
	}
}
