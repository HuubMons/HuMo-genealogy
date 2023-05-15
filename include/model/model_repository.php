<?php

require __DIR__ . '/../database_function.php';

class model_repository extends database_function
{
	/**
	 * Get a single repository from database.
	 */
	public function get_repository(string $repo_gedcomnr): object
	{
		$sql = "SELECT * FROM humo_repositories WHERE repo_tree_id=:repo_tree_id AND repo_gedcomnr=:repo_gedcomnr";

		$stmt = $this->db->prepare($sql);

		$stmt->execute([
			':repo_tree_id' => $this->tree_id,
			':repo_gedcomnr' => $repo_gedcomnr
		]);

		if ($repository = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $repository;
		}

		throw new Exception('Something went wrong, there is no valid person id.', 1);
	}
}
