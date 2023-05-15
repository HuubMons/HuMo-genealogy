<?php

require __DIR__ . '/../database_function.php';

class model_setting extends database_function
{
	/**
	 * Update one setting.
	 * 
	 * TODO: @Devs This function will be in singular
	 */
	public function update_settings(string $setting_variable, string $setting_value): bool
	{
		$sql = "UPDATE humo_settings SET setting_value=:setting_value WHERE setting_variable=:setting_variable";

		$stmt = $this->db->prepare($sql);

		if ($stmt->execute([
			':setting_variable' => $setting_variable,
			':setting_value' => $setting_value
		])) {
			return true;
		}

		throw new Exception("There is no setting $setting_variable.", 1);
	}
}
