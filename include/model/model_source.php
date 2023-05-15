<?php

require __DIR__ . '/../database_function.php';

class model_source extends database_function
{

	/**
	 * Get a single source from database.
	 * 
	 * QUERY 1	: SELECT * FROM humo_sources WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr
	 * QUERY 2	: SELECT * FROM humo_sources WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr AND source_status!='restricted'"
	 */
	public function get_source(string $source_gedcomnr): object
	{
		$sql = "SELECT * FROM humo_sources
			WHERE source_tree_id=:source_tree_id 
			AND source_gedcomnr=:source_gedcomnr";

		$stmt = $this->db->prepare($sql);

		if ($stmt->execute([
			':source_tree_id' => $this->tree_id,
			':source_gedcomnr' => $source_gedcomnr
		])) {
			return $stmt->fetch(PDO::FETCH_OBJ);
		}

		throw new Exception("No source found with gedcom number $source_gedcomnr", 1);
	}
}
