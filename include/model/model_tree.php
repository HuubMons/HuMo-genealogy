<?php

require __DIR__ . '/../database_function.php';

class model_tree extends database_function
{
	/**
	 * Get family tree data from database.
	 */
	public function get_tree(string $tree_prefix): object
	{
		// *** Detection of tree_prefix/ tree_id ***
		if (substr($tree_prefix, 0, 4) == 'humo') {
			// *** Found tree_prefix humox_ ***
			$sql = "SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix";

			$stmt = $this->db->prepare($sql);

			if ($stmt->execute([
				':tree_prefix' => $tree_prefix
			])) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
		} elseif (is_numeric($tree_prefix)) {
			// **** Found tree_id, numeric value ***
			$sql = "SELECT * FROM humo_trees WHERE tree_id=:tree_id";
			$stmt = $this->db->prepare($sql);

			if ($stmt->execute([
				':tree_id' => $tree_prefix
			])) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
		}

		throw new Exception("No tree found with tree_prefix $tree_prefix", 1);
	}

	/**
	 * Get all data from family trees.
	 */
	function get_trees(): array
	{
		$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";

		$stmt = $this->db->prepare($sql);

        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);

	}
}
