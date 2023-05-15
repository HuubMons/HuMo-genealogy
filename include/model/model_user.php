<?php

require __DIR__ . '/../database_function.php';

class model_user extends database_function
{
	/**
	 * Get user from database return false if it isn't.
	 * 
	 * @Deprecated Will be removed in version 7.0 
	 */
	public function get_user(string $user_name, string $user_password): object
	{
		$user = null;
		// *** First check password method using salt ***
		$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':user_name' => $user_name
		]);
		if ($user = $stmt->fetch(PDO::FETCH_OBJ)) {
			$isPasswordCorrect = false;
			if (isset($user->user_password_salted)) {
				$isPasswordCorrect = password_verify($user_password, $user->user_password_salted);
			}

			if (!$isPasswordCorrect) {
				// *** Old method without salt, update to new method including salt ***
				$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password";
				$stmt = $this->db->prepare($sql);
				if ($stmt->execute([
					':user_name' => $user_name,
					':user_password' => MD5($user_password)
				])) {
					$user = $stmt->fetch(PDO::FETCH_OBJ);
				}
				// *** Update to new method including salt ***
				if ($user) {
					$hashToStoreInDb = password_hash($user_password, PASSWORD_DEFAULT);
					$sql = "UPDATE humo_users SET user_password_salted='" . $hashToStoreInDb . "', user_password='' WHERE user_id=" . $user->user_id;
					$stmt = $this->db->prepare($sql);
					$stmt->execute();
				}
			}
		};
		
		return $user;
	}
}
