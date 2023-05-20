<?php 

require __DIR__ . '/Authenticator2fa.php';
require_once __DIR__ . '/../include/model/db_user.php';
require_once __DIR__ . '/../include/model/db_user_log.php';


class Authenticator
{
    private db_user $db_user;
    private db_user_log $db_user_log;
    private Authenticator2fa $auth_fa;

    public function __construct($dbh)
    {
        $this->db_user = new db_user($dbh);
        $this->db_user_log = new db_user_log($dbh);
        $this->auth_fa = new Authenticator2fa();
    }

    public function login(string $username, string $plaintext_password, string $fa_code = null)
    {
        $user = $this->db_user->findOneByCriteria(['user_name' => $username]);
        if ($user && password_verify($plaintext_password, $user->user_password_salted))
        {
            if ($user->user_2fa_enabled == true)
            {
                $check2Fa = $this->auth_fa->verifyCode($user->user_2fa_auth_secret, $fa_code, 2); // 2 = 2*30sec clock tolerance
				if (!$check2Fa) {
                    $this->_onLoginFail($username);
					return false;
				}
            }
            $this->_onLoginSuccess($username);
            return $user;
        }

        $this->_onLoginFail($username);
        return false;
    }

    public function logout()
    {
        session_destroy();
        header("Location: /");
    }

    /**
     * Insert auth success on user_logs table
     */
    private function _onLoginSuccess($username)
    {
        $this->db_user_log->insert(
            [
                'log_username' => $username,
                'log_date' => date("Y-m-d H:i"),
                'log_ip_address' => $_SERVER['REMOTE_ADDR'],
                'log_user_admin' => strpos($_SERVER['REQUEST_URI'], 'admin') ? 'admin' : 'front',
                'log_status' => 'success'
            ]
        );
    }

    /**
     * Insert auth fail on user logs_table
     */
    private function _onLoginFail($username)
    {
		$this->db_user_log->insert(
			[
				'log_username' => $username,
				'log_date' => date("Y-m-d H:i"),
				'log_ip_address' => $_SERVER['REMOTE_ADDR'],
				'log_user_admin' => strpos($_SERVER['REQUEST_URI'], 'admin') ? 'admin' : 'front',
				'log_status' => 'failed'
			]
		);
    }
}