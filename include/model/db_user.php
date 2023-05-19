<?php

/**
 * User Model
 * 
 * Modeler of database humo_users table
 */
class db_user
{
	private $db;
	public function __construct($databaseConnection)
	{
		$this->db = $databaseConnection;
	}

	public function findId(int $id): object
	{
		$sql = "SELECT * FROM humo_users WHERE user_id=:id";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':id' => $id
		]);
		if ($user = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $user;
		}

		throw new Exception("No user found.", 1);
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

		$sql = "SELECT * FROM humo_users WHERE $params";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($criteria);
		if ($user = $stmt->fetch(PDO::FETCH_OBJ)) {
			return $user;
		}

		throw new Exception("No user found with those criteria.", 1);
	}
}
