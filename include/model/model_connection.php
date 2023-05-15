<?php

require __DIR__ . '/../database_function.php';

class model_connection extends database_function
{

	/**
	 * Get multiple connections (sources or addresses) from database.
	 */
	public function get_connections(string $connect_sub_kind, string $connect_item_id): array
	{
		$sql = "SELECT * FROM humo_connections 
				WHERE connect_tree_id=:connect_tree_id 
				AND connect_sub_kind=:connect_sub_kind 
				AND connect_item_id=:connect_item_id";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':connect_tree_id' => $this->tree_id,
			':connect_sub_kind' => $connect_sub_kind,
			':connect_item_id' => $connect_item_id
		]);

		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}

	/**
	 * Get multiple connections from database (for a person or family).
	 */
	public function get_connections_connect_id(string $connect_kind, string $connect_sub_kind, string $connect_connect_id): array
	{
		$sql = "SELECT * FROM humo_connections 
				WHERE connect_tree_id=:connect_tree_id 
				AND connect_kind=:connect_kind 
				AND connect_sub_kind=:connect_sub_kind
				AND connect_connect_id=:connect_connect_id 
				ORDER BY connect_order";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':connect_tree_id' => $this->tree_id,
			':connect_kind' => $connect_kind,
			':connect_sub_kind' => $connect_sub_kind,
			':connect_connect_id' => $connect_connect_id
		]);
		
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}
}
