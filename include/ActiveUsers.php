<?php

namespace Genealogy\Include;

class ActiveUsers
{

    // *** Remove inactive users from humo_active_visitors table (last_activity older than specified seconds) ***
    public function removeInactiveUsers($dbh, $max_visitors_seconds)
    {
        $stmt = $dbh->prepare("DELETE FROM humo_active_visitors WHERE last_activity < UNIX_TIMESTAMP() - :max_visitors_seconds");
        $stmt->execute([':max_visitors_seconds' => $max_visitors_seconds]);
    }

    // *** Add active user to humo_active_visitors table ***
    public function addActiveUser($dbh, $visitor_ip)
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $user_agent = substr($user_agent, 0, 255);

        $stmt = $dbh->prepare("
            INSERT INTO humo_active_visitors
                (visitor_ip, last_activity, user_agent)
            VALUES
                (:visitor_ip, UNIX_TIMESTAMP(), :user_agent)
            ON DUPLICATE KEY UPDATE
                last_activity = UNIX_TIMESTAMP(),
                user_agent = :user_agent
            ");

        $stmt->execute([
            ':visitor_ip' => $visitor_ip,
            ':user_agent' => $user_agent
        ]);
    }

    public function GetActiveUsers($dbh)
    {
        $active_users = 0;
        $stmt = $dbh->prepare("SELECT COUNT(*) AS active_users FROM humo_active_visitors");
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $active_users = (int)$result['active_users'];
        }
        return $active_users;
    }
}
