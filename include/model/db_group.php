<?php

/**
 * Group Model
 * 
 * Modeler of database humo_groups table
 */
class db_group
{
	private $db;
	public function __construct($databaseConnection)
	{
		$this->db = $databaseConnection;
	}

	public function findId(int $id): object
	{
		$sql = "SELECT * FROM humo_groups WHERE group_id=:id";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':id' => $id
		]);
		if ($user = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $user;
		}

		// throw new Exception("No group found.", 1);
		return null;
	}

	public function findOneByCriteria(array $criteria): object
	{
		$i = 0;
		$params = '';
		foreach ($criteria as $key => $value) {
			if ($i == 0) {
				$params .= "$key=:$key";
			} else {
				$params .= ", $key=:$key";
			}
			$i++;
		}

		$sql = "SELECT * FROM humo_groups WHERE $params";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($criteria);
		if ($user = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $user;
		}

		// throw new Exception("No group found with those criteria.", 1);
		return null;
	}
}
