<?php

require __DIR__ . '/../database_function.php';

class model_user_log extends database_function
{
    /**
     * Check visitor
     * 
     * @Deprecated Will be removed in v 7.x
     * 
     * // TODO: @Devs Never store IP in db for security concern!
     * 
     * $block: can be used to totally or partially (no login page) block the website
     */
    public function check_visitor(mixed $ip_address, string $block = 'total'): bool
    {
        $allowed = true;
        $check_fails = 0;

        // *** Check last 20 logins of IP address ***
        if ($block == 'total') {
            $sql = "SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':log_ip_address' => $ip_address
            ]);
            $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($logs as $log) {
                if ($log->log_status == 'failed') $check_fails++;
            }

            if ($check_fails > 20) $allowed = false;
        }

        // *** Check IP Blacklist ***
        $stmt2 = $this->db->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
        if ($setting = $stmt2->fetch(PDO::FETCH_OBJ)) {
            $list = explode("|", $setting->setting_value);
            //if ($ip_address==$list[0]) $allowed=false;
            if (strcmp($ip_address, $list[0]) == 0) $allowed = false;
        }

        return $allowed;
    }
}
