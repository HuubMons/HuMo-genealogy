<?php

require __DIR__ . '/../database_function.php';

class model_address extends database_function
{
	/**
	 * Get a single address from database.
	 */
	public function get_address(string $address_gedcomnr): object
	{
		$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':address_tree_id' => $this->tree_id,
			':address_gedcomnr' => $address_gedcomnr
		]);

		if ($address = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $address;
		}

		throw new Exception("No address found with gedcom numero $address_gedcomnr", 1);
	}

	/**
	 * Get all places by a person, family etc. from database.
	 */
	function get_addresses(string $connect_kind, string $connect_sub_kind, $connect_connect_id): array
	{
		$sql = "SELECT * FROM humo_connections
		 		LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
		 		WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
		 		AND connect_kind=:connect_kind
		 		AND connect_sub_kind=:connect_sub_kind
		 		AND connect_connect_id=:connect_connect_connect_id
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
