<?php

class db_setting
{
	private $db;
	
	public function __construct($databaseConnection)
	{
		$this->db = $databaseConnection;
	}

	/**
	 * Find all settings
	 */
	public function findAll(): array
	{
		$stmt = $this->db->query("SELECT * FROM humo_settings");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Data to array key
	 * 
	 * Transform all setting rows to an array [setting_variable, setting_value]
	 */
	public function dataToArrayKey(): array
	{
		$arrayKey = [];
		foreach ($this->findAll() as $row) {
			$arrayKey[$row['setting_variable']] = $row['setting_value'];
		}
		return $arrayKey;
	}
}
