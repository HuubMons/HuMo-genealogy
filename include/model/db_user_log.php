<?php

/**
 * User Log Model
 * 
 * Modeler of database humo_user_log table
 */
class db_user_log
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
		$stmt = $this->db->query("SELECT * FROM humo_user_log");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function insert(array $log)
    {
        $sql = "INSERT INTO humo_user_log SET log_username=:log_username, 
                log_date=:log_date, log_ip_address=:log_ip_address,
				log_user_admin=:log_user_admin, log_status=:log_status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':log_username' => $log['log_username'],
            ':log_date' => $log['log_date'],
            ':log_ip_address' => $log['log_ip_address'],
            ':log_user_admin' => $log['log_user_admin'],
            ':log_status' => $log['log_status'],
        ]);
    }
}