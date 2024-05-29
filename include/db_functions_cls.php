<?php

/**--------------------[database functions]----------------------------
 *
 * AUTHOR		: Huub Mons. Created: jan. 2015.
 * THANKS TO	: Michael.j.Falconer
 *
 * FUNCTIONS:
 *      check_visitor               Check for valid visitor.
 *      get_user                    Check if user exists.
 *      get_tree                    Get data from selected family tree.
 *      get_trees                   Get data from all family trees.
 *      check_person                Check if person is valid.
 *      get_person                  Get a single person from database.
 *      get_person_with_id          Get a single person from database using id number.
 *      count_persons               Count persons in family tree.
 *      check_family                Check if family is valid.
 *      get_family                  Get a single family from database.
 *      count_families              Count families in family tree.
 *      get_event                   Get a single event from database.
 *      get_events_kind             Get multiple events of one event_kind from database. Example:
 *      get_events_connect          Get multiple events of a connected person, family etc. selecting one event_kind from database.
 *      get_source                  Get a single source from database.
 *      get_address                 Get a single address from database.
 *      get_addressses              Get all addresses (places) by a person, family, etc.
 *      get_connections             Get multiple connections (used for sources and addresses).
 *      get_connections_connect_id  Get multiple connections of a person or family.
 *      get_repository              Get a single repository from database.
 *
 *		generate_gedcomnr
 *
 * SET family tree variabele:
 *	$db_functions->set_tree_id($tree_id);
 *
 * EXAMPLE get single item from database:
 *		$person_manDb = $db_functions->get_person($familyDb->fam_man);
 *		if ($person_manDb==false){ }
 *
 * EXAMPLE get multiple items from database:
 *		$colour = $db_functions->get_events_connect('person',$personDb->pers_gedcomnumber,'person_colour_mark');
 *		foreach($colour as $colourDb){
 *			echo $colourDb->event_event;
 *		}
 *		$num_rows=count($colour); // *** number of rows ***
 *		unset($colour); // *** If finished, remove data from memory ***
 *
 * Some remarks:
 * event_connect_id = reference to person, family or source gedcomnumber.
 *
 */

class db_functions
{
    //public int $tree_id = 0;
    public $tree_id = 0;
    //public string $tree_prefix = '';
    public $tree_prefix = '';
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Set family tree_id 
     */
    public function set_tree_id(int $tree_id): void
    {
        if (is_numeric($tree_id)) $this->tree_id = $tree_id;
        $sql = "SELECT tree_prefix FROM humo_trees WHERE tree_id=:tree_id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':tree_id' => $tree_id
        ]);
        $tree = $stmt->fetch(PDO::FETCH_OBJ);
        if (isset($tree->tree_prefix)) {
            $this->tree_prefix = $tree->tree_prefix;
        }
    }

    /**
     * FUNCTION	: Check visitor
     * QUERY	: SELECT * FROM humo_user_log
     *				WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11
     * RETURNS	: True/ false.
     */
    public function check_visitor($ip_address, $block = 'total')
    {
        $allowed = true;
        $check_fails = 0;
        // *** $block: can be used to totally or partially (no login page) block the website ***
        // *** Check last 20 logins of IP address ***
        if ($block == 'total') {
            try {
                $sql = "SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':log_ip_address' => $ip_address
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $data2Db) {
                    if (@$data2Db->log_status == 'failed') {
                        $check_fails++;
                    }
                }
            } catch (PDOException $e) {
                //echo $e->getMessage() . "<br/>";
            }
            if ($check_fails > 20) {
                $allowed = false;
            }
        }

        // *** Check IP Blacklist ***
        $check = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist'");
        while ($checkDb = $check->fetch(PDO::FETCH_OBJ)) {
            $list = explode("|", $checkDb->setting_value);
            //if ($ip_address==$list[0]) $allowed=false;
            if (strcmp($ip_address, $list[0]) == 0) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * FUNCTION	: Get user from database return false if it isn't.
     * QUERY	: SELECT * FROM humo_users
     *				(user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''
     * QUERY	: SELECT * FROM humo_users
     *				(user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password
     * RETURNS	: user data.
     */
    public function get_user($user_name, $user_password)
    {
        // *** First check password method using salt ***
        $sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':user_name' => $user_name
        ]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        $isPasswordCorrect = false;
        //if (isset($qryDb)){
        if (isset($user->user_password_salted)) {
            $isPasswordCorrect = password_verify($user_password, $user->user_password_salted);
        }

        if (!$isPasswordCorrect) {
            // *** Old method without salt, update to new method including salt ***
            $sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password";
            try {
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':user_name' => $user_name,
                    ':user_password' => MD5($user_password)
                ]);

                $user = $stmt->fetch(PDO::FETCH_OBJ);

                // *** Update to new method including salt ***
                if ($user) {
                    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE humo_users SET user_password_salted='" . $hashed_password . "', user_password='' WHERE user_id=" . $user->user_id;
                    $this->dbh->query($sql);
                }
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }
        }
        return $user;
    }

    /**
     * FUNCTION	: Get family tree data from database.
     * QUERY 1	: SELECT * FROM humo_trees
     *				WHERE tree_prefix=:tree_prefix
     * QUERY 2	: SELECT * FROM humo_trees
     *				WHERE tree_id=:tree_id
     * RETURNS	: family tree data.
     */
    public function get_tree($tree_prefix)
    {
        // *** Detection of tree_prefix/ tree_id ***
        if (substr($tree_prefix, 0, 4) === 'humo') {
            // *** Found tree_prefix humox_ ***
            try {
                $sql = "SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_prefix' => $tree_prefix
                ]);
                $tree = $stmt->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }
        } elseif (is_numeric($tree_prefix)) {
            // **** Found tree_id, numeric value ***
            try {
                $sql = "SELECT * FROM humo_trees WHERE tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_prefix
                ]);
                $tree = $stmt->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }
        }
        return $tree;
    }

    /**
     * FUNCTION	: Get all data from family trees.
     * QUERY	: SELECT * FROM humo_trees
     *				WHERE tree_prefix!='EMPTY' ORDER BY tree_order
     * RETURNS	: all data from family trees.
     */
    public function get_trees()
    {
        $trees = array();
        try {
            $sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
            $trees = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $trees;
    }

    /**
     * FUNCTION	: Check for valid person in database.
     * QUERY 1	: SELECT pers_id FROM humo_persons
     *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * RETURNS	: Check for valid person.
     */
    //public function check_person(string $pers_gedcomnumber)
    // *** If string is used, script could stop if value is NULL ***
    public function check_person($pers_gedcomnumber)
    {
        if ($pers_gedcomnumber != '') {
            try {
                $sql = "SELECT pers_id FROM humo_persons
                    WHERE pers_tree_id=:pers_tree_id 
                    AND pers_gedcomnumber=:pers_gedcomnumber";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':pers_tree_id' => $this->tree_id,
                    ':pers_gedcomnumber' => $pers_gedcomnumber
                ]);
                $person = $stmt->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }
            if (!isset($person->pers_id)) {
                echo '<b>' . __('Something went wrong, there is no valid person id.') . '</b>';
                exit();
            }
            return $person;
        }
    }

    /**
     * FUNCTION	: Get a single person from database.
     * QUERY 1	: SELECT * FROM humo_persons
     *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * QUERY 2	: SELECT pers_famc, pers_fams FROM humo_persons
     *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * RETURNS	: a single person.
     */
    //public function get_person(string $pers_gedcomnumber, string $item = '')
    // *** If string is used, script could stop if value is NULL ***
    public function get_person($pers_gedcomnumber, string $item = '')
    {
        try {
            if ($item === 'famc-fams') {
                $sql = "SELECT pers_famc, pers_fams FROM humo_persons
                    WHERE pers_tree_id=:pers_tree_id 
                    AND pers_gedcomnumber=:pers_gedcomnumber";
            } else {
                $sql = "SELECT * FROM humo_persons
                    WHERE pers_tree_id=:pers_tree_id 
                    AND pers_gedcomnumber=:pers_gedcomnumber";
            }
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':pers_tree_id' => $this->tree_id,
                ':pers_gedcomnumber' => $pers_gedcomnumber
            ]);
            $person = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $person;
    }

    /**
     * FUNCTION	: Get a single person from database.
     * QUERY	: SELECT * FROM humo_persons WHERE pers_id=:pers_tree_id
     * RETURNS	: a single person.
     */
    public function get_person_with_id(int $pers_id)
    {
        try {
            $sql = "SELECT * FROM humo_persons WHERE pers_id=:pers_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':pers_id' => $pers_id
            ]);
            $person = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $person;
    }

    /**
     * FUNCTION	: Count persons in selected family tree.
     * QUERY	: SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id
     * RETURNS	: Number of persons in family tree.
     */
    public function count_persons(int $tree_id)
    {
        $count = 0;
        try {
            $sql = "SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':pers_tree_id' => $tree_id
            ]);
            $count = $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $count;
    }

    /**
     * FUNCTION	: Check for valid family in database.
     * QUERY 1	: SELECT fam_id FROM humo_families
     *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber
     * RETURNS	: Check for valid family.
     */
    public function check_family($fam_gedcomnumber)
    {
        if ($fam_gedcomnumber != '') {
            try {
                $sql = "SELECT fam_id FROM humo_families
                    WHERE fam_tree_id=:fam_tree_id 
                    AND fam_gedcomnumber=:fam_gedcomnumber";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':fam_tree_id' => $this->tree_id,
                    ':fam_gedcomnumber' => $fam_gedcomnumber
                ]);
                $family = $stmt->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }
            if (!isset($family->fam_id)) {
                echo '<b>' . __('Something went wrong, there is no valid family id.') . '</b>';
                exit();
            }
            return $family;
        }
    }

    /**
     * FUNCTION	: Get a single family from database.
     * QUERY 1	: SELECT fam_man, fam_woman FROM humo_families
     *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
     * QUERY 2	: SELECT * FROM humo_families
     *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
     * USE		: get_family($fam_number,'man-woman') to get man and woman id.
     * RETURNS	: a single family.
     */
    public function get_family($fam_gedcomnumber, $item = '')
    {
        $qryDb = false;
        try {
            if ($item == 'man-woman') {
                $sql = "SELECT fam_man, fam_woman, fam_children FROM humo_families
                    WHERE fam_tree_id=:fam_tree_id 
                    AND fam_gedcomnumber=:fam_gedcomnumber";
            } else {
                $sql = "SELECT * FROM humo_families
                    WHERE fam_tree_id=:fam_tree_id 
                    AND fam_gedcomnumber=:fam_gedcomnumber";
            }
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':fam_tree_id' => $this->tree_id,
                ':fam_gedcomnumber' => $fam_gedcomnumber
            ]);
            $family = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $family;
    }

    /**
     * FUNCTION	: Count families in selected family tree.
     * QUERY	: SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id
     * RETURNS	: Number of families in family tree.
     */
    public function count_families(int $tree_id)
    {
        $count = 0;
        try {
            $sql = "SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':fam_tree_id' => $tree_id
            ]);
            $count = $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $count;
    }

    /**
     * FUNCTION	: Get a single text from database.
     * QUERY	: SELECT * FROM humo_texts
     * 				WHERE fam_tree_id=:fam_tree_id AND text_gedcomnr=:text_gedcomnr
     * RETURNS	: a single text.
     */
    public function get_text($text_gedcomnr)
    {
        try {
            $sql = "SELECT * FROM humo_texts WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':text_tree_id' => $this->tree_id,
                ':text_gedcomnr' => $text_gedcomnr
            ]);
            $text = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $text;
    }

    /**
     * FUNCTION	: Get a single event from database.
     * QUERY	: SELECT * FROM humo_events WHERE event_id=:event_id
     * RETURNS	: a single event.
     */
    public function get_event($event_id)
    {
        try {
            $sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':event_id' => $event_id
            ]);
            $event = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $event;
    }

    /**
     * FUNCTION	: Get all selected events from database.
     * QUERY	: SELECT * FROM humo_events WHERE event_tree_id=:event_tree_id
     *				 AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order
     * RETURNS	: multiple selected events.
     */
    public function get_events_kind($event_event, $event_kind)
    {
        try {
            $sql = "SELECT * FROM humo_events
                WHERE event_tree_id=:event_tree_id 
                AND event_event=:event_event 
                AND event_kind=:event_kind 
                ORDER BY event_order";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':event_tree_id' => $this->tree_id,
                ':event_event' => $event_event,
                ':event_kind' => $event_kind
            ]);
            $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $events;
    }

    /**
     * FUNCTION	: Get all selected events by a person, family etc. from database.
     * QUERY	: SELECT * FROM humo_events
     *				WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind
     *				AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order
     * RETURNS	: all selected events by a person.
     */
    public function get_events_connect($event_connect_kind, $event_connect_id, $event_kind)
    {
        try {
            $sql = "SELECT * FROM humo_events
                WHERE event_tree_id=:event_tree_id 
                AND event_connect_kind=:event_connect_kind 
                AND event_connect_id=:event_connect_id 
                AND event_kind=:event_kind 
                ORDER BY event_order";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':event_tree_id' => $this->tree_id,
                ':event_connect_kind' => $event_connect_kind,
                ':event_connect_id' => $event_connect_id,
                ':event_kind' => $event_kind
            ]);
            $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $events;
    }

    /**
     * FUNCTION	: Get a single source from database.
     * QUERY 1	: SELECT * FROM humo_sources
     *				WHERE source_tree_id=:source_tree_id
     *				AND source_gedcomnr=:source_gedcomnr
     * QUERY 2	: SELECT * FROM humo_sources
     *				WHERE source_tree_id=:source_tree_id
     *				AND source_gedcomnr=:source_gedcomnr
     *				AND source_status!='restricted'"
     * RETURNS	: a single source.
     */
    public function get_source(string $source_gedcomnr)
    {
        try {
            $sql = "SELECT * FROM humo_sources
                WHERE source_tree_id=:source_tree_id 
                AND source_gedcomnr=:source_gedcomnr";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':source_tree_id' => $this->tree_id,
                ':source_gedcomnr' => $source_gedcomnr
            ]);
            $source = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $source;
    }

    /**
     * FUNCTION	: Get a single address from database.
     * QUERY	: SELECT * FROM humo_addresses
     * 				WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr
     * RETURNS	: a single address.
     */
    public function get_address($address_gedcomnr)
    {
        try {
            $sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':address_tree_id' => $this->tree_id,
                ':address_gedcomnr' => $address_gedcomnr
            ]);
            $address = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $address;
    }

    /**
     * FUNCTION	: Get all places by a person, family etc. from database.
     *			SELECT * FROM humo_connections
     *				LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
     *				WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
     *				AND connect_kind=:connect_kind
     *				AND connect_sub_kind=:connect_sub_kind
     *				AND connect_connect_id=:connect_connect_connect_id
     *				ORDER BY connect_order
     * RETURNS	: all places by a person, family etc.
     */
    public function get_addresses($connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        try {
            $sql = "SELECT * FROM humo_connections
            LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
            WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
            AND connect_kind=:connect_kind
            AND connect_sub_kind=:connect_sub_kind
            AND connect_connect_id=:connect_connect_id
            ORDER BY connect_order";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':connect_tree_id' => $this->tree_id,
                ':connect_kind' => $connect_kind,
                ':connect_sub_kind' => $connect_sub_kind,
                ':connect_connect_id' => $connect_connect_id
            ]);
            $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $result_array;
    }

    /**
     * FUNCTION	: Get multiple connections (sources or addresses) from database.
     * QUERY	: SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
     *				AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id
     * RETURNS	: multiple connections.
     */
    public function get_connections($connect_sub_kind, $connect_item_id)
    {
        try {
            $sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':connect_tree_id' => $this->tree_id,
                ':connect_sub_kind' => $connect_sub_kind,
                ':connect_item_id' => $connect_item_id
            ]);
            $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $result_array;
    }

    /**
     * FUNCTION	: Get multiple connections from database (for a person or family).
     * QUERY	: SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
     *				AND connect_kind=:connect_kind AND connect_sub_kind=:connect_sub_kind
     *				AND connect_connect_id=:connect_connect_id ORDER BY connect_order
     * RETURNS	: multiple connections.
     * EXAMPLE	: $connect_sql = $db_functions->get_connections_connect_id('person','pers_object',$event_connect_id);
     */
    public function get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        try {
            $sql = "SELECT * FROM humo_connections 
                WHERE connect_tree_id=:connect_tree_id 
                AND connect_kind=:connect_kind 
                AND connect_sub_kind=:connect_sub_kind 
                AND connect_connect_id=:connect_connect_id 
                ORDER BY connect_order";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':connect_tree_id' => $this->tree_id,
                ':connect_kind' => $connect_kind,
                ':connect_sub_kind' => $connect_sub_kind,
                ':connect_connect_id' => $connect_connect_id
            ]);
            $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $result_array;
    }

    /**
     * FUNCTION	: Get a single repository from database.
     * QUERY	: SELECT * FROM humo_repositories
     *				WHERE repo_tree_id=:repo_tree_id
     *				AND repo_gedcomnr=:repo_gedcomnr
     * RETURNS	: a single repository.
     */
    public function get_repository($repo_gedcomnr)
    {
        try {
            $sql = "SELECT * FROM humo_repositories WHERE repo_tree_id=:repo_tree_id AND repo_gedcomnr=:repo_gedcomnr";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':repo_tree_id' => $this->tree_id,
                ':repo_gedcomnr' => $repo_gedcomnr
            ]);
            $repository = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $repository;
    }

    /**
     * FUNCTION	: Update humo_settings.
     * QUERY	: UPDATE humo_settings
     * 				SET setting_value=:setting_value WHERE setting_variable=:setting_variable
     * RETURNS	: result.
     */
    public function update_settings($setting_variable, $setting_value)
    {
        try {
            $sql = "UPDATE humo_settings SET setting_value=:setting_value WHERE setting_variable=:setting_variable";
            $stmt = $this->dbh->prepare($sql);
            $isUpdated = $stmt->execute([
                ':setting_variable' => $setting_variable,
                ':setting_value' => $setting_value
            ]);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $isUpdated;
    }

    /** 
     * Generate new GEDCOM number for item (person, family, source, repo, address, etc.) ***
     * Generates new GEDCOM number (only numerical, like: 1234).
     * $sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
     * $sql = "SELECT fam_gedcomnumber FROM humo_families WHERE pers_tree_id=:tree_id";
     */
    public function generate_gedcomnr($tree_id, $item)
    {
        $qryDb = false;
        $new_gedcomnumber = 0;
        try {
            // *** Command preg_replace \D removes all non-digit characters (including spaces etc.) ***
            // *** This will work for all kinds of GEDCOM numbers like I1234, 1234I, U1234, X1234. ***
            if ($item == 'person') {
                $sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->pers_gedcomnumber));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'family') {
                $sql = "SELECT fam_gedcomnumber FROM humo_families WHERE fam_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->fam_gedcomnumber));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'source') {
                $sql = "SELECT source_gedcomnr FROM humo_sources WHERE source_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->source_gedcomnr));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'address') {
                $sql = "SELECT address_gedcomnr FROM humo_addresses WHERE address_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->address_gedcomnr));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'repo') {
                $sql = "SELECT repo_gedcomnr FROM humo_repositories WHERE repo_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->repo_gedcomnr));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'text') {
                $sql = "SELECT text_gedcomnr FROM humo_texts WHERE text_tree_id=:tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->text_gedcomnr));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
            if ($item == 'event') {
                $sql = "SELECT event_gedcomnr FROM humo_events WHERE event_tree_id=:tree_id AND event_gedcomnr LIKE '_%'";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':tree_id' => $tree_id
                ]);
                $result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($result_array as $resultDb) {
                    $gednum = (int)(preg_replace('/\D/', '', $resultDb->event_gedcomnr));
                    if ($gednum > $new_gedcomnumber) {
                        $new_gedcomnumber = $gednum;
                    }
                }
            }
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }

        $new_gedcomnumber++;
        return $new_gedcomnumber;
    }
}
