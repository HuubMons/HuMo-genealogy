<?php

/**--------------------[database functions]----------------------------
 *
 * AUTHOR        : Huub Mons. Created: jan. 2015.
 * THANKS TO     : Michael.j.Falconer
 *
 * FUNCTIONS:
 *      check_visitor               Check for valid visitor.
 *      get_user                    Check if user exists.
 *      get_tree                    Get data from selected family tree.
 *      get_trees                   Get data from all family trees.
 *      check_person                Check if person is valid.
 *      get_person                  Get a single person from database.
 *      get_person_with_id          Get a single person from database using id number.
 *      get_persons                 Get all persons from selected family tree.
 *      get_quicksearch_results     Get quicksearch results from database.
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
 *      generate_gedcomnr
 *
 * SET family tree variabele:
 *      $db_functions->set_tree_id($tree_id);
 *
 * EXAMPLE get single item from database:
 *      $person_manDb = $db_functions->get_person($familyDb->fam_man);
 *      if ($person_manDb==false){ }
 *
 * EXAMPLE get multiple items from database:
 *      $colour = $db_functions->get_events_connect('person',$personDb->pers_gedcomnumber,'person_colour_mark');
 *      foreach($colour as $colourDb){
 *          echo $colourDb->event_event;
 *      }
 *      $num_rows=count($colour); // *** number of rows ***
 *      unset($colour); // *** If finished, remove data from memory ***
 *
 * Some remarks:
 * event_connect_id = reference to person, family or source gedcomnumber.
 *
 */

namespace Genealogy\Include;

use PDO;
use PDOException;

class DbFunctions
{
    public $tree_id = 0;
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
     * FUNCTION     : Check visitor
     * QUERY        : SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11
     * RETURNS      : True/ false.
     */
    public function check_visitor($ip_address, string $block = 'total'): bool
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
                    if ($data2Db->log_status == 'failed') {
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
            //if ($ip_address==$list[0]){
            //  $allowed=false;
            //}
            if (strcmp($ip_address, $list[0]) == 0) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * FUNCTION     : Get user from database return false if it isn't.
     * QUERY        : SELECT * FROM humo_users (user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''
     * QUERY        : SELECT * FROM humo_users (user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password
     * RETURNS      : user data.
     */
    public function get_user(string $user_name, string $user_password)
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
     * FUNCTION     : Get user name from database.
     * QUERY        : SELECT user_name FROM humo_users WHERE user_id=:user_id
     * RETURNS      : user name.
     */
    public function get_user_name(int|null $user_id): string
    {
        $user_name = '';
        if ($user_id && is_numeric($user_id)) {
            $user_qry = "SELECT user_name FROM humo_users WHERE user_id='" . $user_id . "'";
            $user_result = $this->dbh->query($user_qry);
            $userDb = $user_result->fetch(PDO::FETCH_OBJ);
            if ($userDb) {
                $user_name = $userDb->user_name;
            }
        }
        return $user_name;
    }

    /**
     * FUNCTION     : Get family tree data from database.
     * QUERY 1      : SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix
     * QUERY 2      : SELECT * FROM humo_trees WHERE tree_id=:tree_id
     * RETURNS      : family tree data.
     */
    public function get_tree($tree_prefix)
    {
        $tree = '';
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
     * FUNCTION     : Get all data from family trees.
     * QUERY        : SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order
     * RETURNS      : all data from family trees.
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
     * FUNCTION     : Check for valid person in database.
     * QUERY 1      : SELECT pers_id FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * RETURNS      : Check for valid person.
     */
    public function check_person(string|null $pers_gedcomnumber)
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


    private function get_person_base_qry()
    {
        // MAX is used to get the latest event information (in case there are multiple rows).
        /*
        return "SELECT
            p.*,
            -- Birth
            MAX(CASE WHEN e.event_kind = 'birth' THEN e.event_date END) AS pers_birth_date,
            MAX(CASE WHEN e.event_kind = 'birth' THEN pl.location_location END) AS pers_birth_place,
            MAX(CASE WHEN e.event_kind = 'birth' THEN e.event_text END) AS pers_birth_text,
            -- Baptism
            MAX(CASE WHEN e.event_kind = 'baptism' THEN e.event_date END) AS pers_bapt_date,
            MAX(CASE WHEN e.event_kind = 'baptism' THEN pl.location_location END) AS pers_bapt_place,
            MAX(CASE WHEN e.event_kind = 'baptism' THEN e.event_text END) AS pers_bapt_text,
            -- Death
            MAX(CASE WHEN e.event_kind = 'death' THEN e.event_date END) AS pers_death_date,
            MAX(CASE WHEN e.event_kind = 'death' THEN pl.location_location END) AS pers_death_place,
            MAX(CASE WHEN e.event_kind = 'death' THEN e.event_text END) AS pers_death_text,
            -- Burial
            MAX(CASE WHEN e.event_kind = 'burial' THEN e.event_date END) AS pers_buried_date,
            MAX(CASE WHEN e.event_kind = 'burial' THEN pl.location_location END) AS pers_buried_place,
            MAX(CASE WHEN e.event_kind = 'burial' THEN e.event_text END) AS pers_buried_text
        FROM
            humo_persons p
        LEFT JOIN humo_events e ON e.person_id = p.pers_id
        LEFT JOIN humo_location pl ON e.place_id = pl.location_id";
        */

        // Query without MAX (there is only a single event for each item):
        return "SELECT
            p.*,

            COALESCE(birth.event_date, '') AS pers_birth_date,
            birth.event_time AS pers_birth_time,
            birthpl.location_location AS pers_birth_place,
            birth.event_text   AS pers_birth_text,
            birth.event_id AS pers_birth_event_id,
            birth.stillborn AS pers_stillborn,
            birth.event_date_hebnight AS pers_birth_date_hebnight,

            COALESCE(bapt.event_date, '')    AS pers_bapt_date,
            baptpl.location_location AS pers_bapt_place,
            bapt.event_text    AS pers_bapt_text,
            bapt.event_id      AS pers_bapt_event_id,
            bapt.event_date_hebnight AS pers_bapt_date_hebnight,

            death.event_date   AS pers_death_date,
            death.event_time   AS pers_death_time,
            death.event_pers_age AS pers_death_age,
            deathpl.location_location AS pers_death_place,
            death.event_text   AS pers_death_text,
            death.event_id     AS pers_death_event_id,
            death.cause  AS pers_death_cause,
            death.event_date_hebnight AS pers_death_date_hebnight,

            burial.event_date  AS pers_buried_date,
            burialpl.location_location AS pers_buried_place,
            burial.event_text  AS pers_buried_text,
            burial.event_id    AS pers_buried_event_id,
            burial.cremation AS pers_cremation,
            burial.event_date_hebnight AS pers_buried_date_hebnight

        FROM humo_persons p
        LEFT JOIN humo_events birth ON birth.person_id = p.pers_id AND birth.event_kind = 'birth'
        LEFT JOIN humo_location birthpl ON birth.place_id = birthpl.location_id
        LEFT JOIN humo_events bapt ON bapt.person_id = p.pers_id AND bapt.event_kind = 'baptism'
        LEFT JOIN humo_location baptpl ON bapt.place_id = baptpl.location_id
        LEFT JOIN humo_events death ON death.person_id = p.pers_id AND death.event_kind = 'death'
        LEFT JOIN humo_location deathpl ON death.place_id = deathpl.location_id
        LEFT JOIN humo_events burial ON burial.person_id = p.pers_id AND burial.event_kind = 'burial'
        LEFT JOIN humo_location burialpl ON burial.place_id = burialpl.location_id";
    }


    /**
     * FUNCTION     : Get a single person from database.
     * QUERY 1      : SELECT * FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * QUERY 2      : SELECT pers_famc, pers_fams FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
     * RETURNS      : a single person.
     */
    public function get_person(string|null $pers_gedcomnumber, string $item = '')
    {
        try {
            if ($item === 'famc-fams') {
                $sql = "SELECT pers_famc, pers_fams FROM humo_persons
                    WHERE pers_tree_id=:pers_tree_id 
                    AND pers_gedcomnumber=:pers_gedcomnumber";
            } else {
                $sql = $this->get_person_base_qry();
                $sql .= " WHERE p.pers_tree_id=:pers_tree_id 
                    AND p.pers_gedcomnumber = :pers_gedcomnumber";
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
     * FUNCTION     : Get a single person from database.
     * QUERY        : SELECT * FROM humo_persons WHERE pers_id=:pers_tree_id
     * RETURNS      : a single person.
     */
    public function get_person_with_id(int $pers_id)
    {
        try {
            $sql = $this->get_person_base_qry();
            $sql .= " WHERE p.pers_id=:pers_id";

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
     * Sep. 2025 Huub: added new function to get all persons included birth_date, birth_place, etc. from selected family tree.
     * FUNCTION     : Get all persons from selected family tree.
     * OPTIONAL     : $tree_id to override class variable.
     * RETURNS      : all persons from family tree.
     */
    public function get_persons(int $tree_id)
    {
        $persons = array();

        if (isset($tree_id) && is_numeric($tree_id) && $tree_id > 0) {
            $tree_id = $tree_id;
        } else {
            $tree_id = $this->tree_id;
        }

        try {
            $sql = $this->get_person_base_qry();
            $sql .= " WHERE p.pers_tree_id=:pers_tree_id ORDER BY p.pers_lastname, p.pers_firstname";

            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':pers_tree_id' => $tree_id
            ]);
            $persons = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $persons;
    }

    /**
     * Get quicksearch results from database.
     */
    public function get_quicksearch_results(int $tree_id, string $quicksearch)
    {
        $person_result = '';
        if ($quicksearch != '') {
            // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
            $quicksearch = str_replace(' ', '%', $quicksearch);

            // *** In case someone entered "Mons, Huub" using a comma ***
            $quicksearch = str_replace(',', '', $quicksearch);

            $person_qry = "SELECT * FROM humo_persons
                WHERE pers_tree_id = :tree_id
                AND (
                    CONCAT(pers_firstname, REPLACE(pers_prefix, '_', ' '), pers_lastname) LIKE :quicksearch
                    OR CONCAT(pers_lastname, REPLACE(pers_prefix, '_', ' '), pers_firstname) LIKE :quicksearch
                    OR CONCAT(pers_lastname, pers_firstname, REPLACE(pers_prefix, '_', ' ')) LIKE :quicksearch
                    OR CONCAT(REPLACE(pers_prefix, '_', ' '), pers_lastname, pers_firstname) LIKE :quicksearch
                )
                ORDER BY pers_lastname, pers_firstname, CAST(SUBSTRING(pers_gedcomnumber, 2) AS UNSIGNED)";
            $stmt = $this->dbh->prepare($person_qry);
            $likeQuicksearch = '%' . $quicksearch . '%';
            $stmt->execute([
                ':tree_id' => $tree_id,
                ':quicksearch' => $likeQuicksearch
            ]);
            $person_result = $stmt;
        }
        return $person_result;
    }

    /**
     * FUNCTION     : Count persons in selected family tree.
     * QUERY        : SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id
     * RETURNS      : Number of persons in family tree.
     */
    public function count_persons(int $tree_id): int
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
     * FUNCTION     : Check for valid family in database.
     * QUERY 1      : SELECT fam_id FROM humo_families WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber
     * RETURNS      : Check for valid family.
     */
    public function check_family(string|null $fam_gedcomnumber)
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

    private function get_family_base_qry(): string
    {
        return "SELECT
        f.*,
        marriage.event_date AS fam_marr_date,
        marriage.event_date_hebnight AS fam_marr_date_hebnight,
        marriagepl.location_location AS fam_marr_place,
        marriage.event_text AS fam_marr_text,
        marriage.authority AS fam_marr_authority,
        marriage.event_id AS fam_marr_event_id,

        divorce.event_date AS fam_div_date,
        divorce.event_date_hebnight AS fam_div_date_hebnight,
        divorcepl.location_location AS fam_div_place,
        divorce.event_text AS fam_div_text,
        divorce.authority AS fam_div_authority,
        divorce.event_id AS fam_div_event_id,

        relation.event_date AS fam_relation_date,
        relation.event_end_date AS fam_relation_end_date,
        relation.event_date_hebnight AS fam_relation_date_hebnight,
        relationpl.location_location AS fam_relation_place,
        relation.event_text AS fam_relation_text,
        relation.authority AS fam_relation_authority,
        relation.event_id AS fam_relation_event_id,

        marrchurch.event_date AS fam_marr_church_date,
        marrchurch.event_date_hebnight AS fam_marr_church_date_hebnight,
        marrchurchpl.location_location AS fam_marr_church_place,
        marrchurch.event_text AS fam_marr_church_text,
        marrchurch.authority AS fam_marr_church_authority,
        marrchurch.event_id AS fam_marr_church_event_id,

        marrchurchnotice.event_date AS fam_marr_church_notice_date,
        marrchurchnotice.event_date_hebnight AS fam_marr_church_notice_date_hebnight,
        marrchurchnoticepl.location_location AS fam_marr_church_notice_place,
        marrchurchnotice.event_text AS fam_marr_church_notice_text,
        marrchurchnotice.authority AS fam_marr_church_notice_authority,
        marrchurchnotice.event_id AS fam_marr_church_notice_event_id,

        marrnotice.event_date AS fam_marr_notice_date,
        marrnotice.event_date_hebnight AS fam_marr_notice_date_hebnight,
        marrnoticepl.location_location AS fam_marr_notice_place,
        marrnotice.event_text AS fam_marr_notice_text,
        marrnotice.authority AS fam_marr_notice_authority,
        marrnotice.event_id AS fam_marr_notice_event_id

        FROM humo_families f

        LEFT JOIN humo_events marriage ON marriage.relation_id = f.fam_id AND marriage.event_kind = 'marriage'
        LEFT JOIN humo_location marriagepl ON marriage.place_id = marriagepl.location_id

        LEFT JOIN humo_events divorce ON divorce.relation_id = f.fam_id AND divorce.event_kind = 'divorce'
        LEFT JOIN humo_location divorcepl ON divorce.place_id = divorcepl.location_id

        LEFT JOIN humo_events relation ON relation.relation_id = f.fam_id AND relation.event_kind = 'relation'
        LEFT JOIN humo_location relationpl ON relation.place_id = relationpl.location_id
        
        LEFT JOIN humo_events marrchurch ON marrchurch.relation_id = f.fam_id AND marrchurch.event_kind = 'marr_church'
        LEFT JOIN humo_location marrchurchpl ON marrchurch.place_id = marrchurchpl.location_id

        LEFT JOIN humo_events marrchurchnotice ON marrchurchnotice.relation_id = f.fam_id AND marrchurchnotice.event_kind = 'marr_church_notice'
        LEFT JOIN humo_location marrchurchnoticepl ON marrchurchnotice.place_id = marrchurchnoticepl.location_id

        LEFT JOIN humo_events marrnotice ON marrnotice.relation_id = f.fam_id AND marrnotice.event_kind = 'marriage_notice'
        LEFT JOIN humo_location marrnoticepl ON marrnotice.place_id = marrnoticepl.location_id";
    }

    /**
     * FUNCTION     : Get a single family from database.
     * QUERY 1      : SELECT fam_man, fam_woman FROM humo_families WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
     * QUERY 2      : SELECT * FROM humo_families WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
     * USE          : get_family($fam_number,'man-woman') to get man and woman id.
     * RETURNS      : a single family.
     */
    public function get_family(string|null $fam_gedcomnumber, string $item = '')
    {
        try {
            if ($item == 'man-woman') {
                $sql = "SELECT fam_man, fam_woman, fam_children FROM humo_families
                    WHERE fam_tree_id=:fam_tree_id 
                    AND fam_gedcomnumber=:fam_gedcomnumber";
            } else {
                // TODO add event_date_hebnight
                $sql = $this->get_family_base_qry();
                $sql .= " WHERE f.fam_tree_id=:fam_tree_id
                    AND f.fam_gedcomnumber=:fam_gedcomnumber";
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
     * FUNCTION     : Get a family person from database.
     * QUERY        : SELECT * FROM humo_family WHERE fam_id=:fam_id
     * RETURNS      : a single family.
     */
    public function get_family_with_id(int $fam_id)
    {
        try {
            $sql = $this->get_family_base_qry();
            $sql .= " WHERE f.fam_id=:fam_id";

            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':fam_id' => $fam_id
            ]);
            $family = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $family;
    }

    /**
     * FUNCTION     : Count families in selected family tree.
     * QUERY        : SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id
     * RETURNS      : Number of families in family tree.
     */
    public function count_families(int $tree_id): int
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
     * FUNCTION     : Get a single text from database.
     * QUERY        : SELECT * FROM humo_texts WHERE fam_tree_id=:fam_tree_id AND text_gedcomnr=:text_gedcomnr
     * RETURNS      : a single text.
     */
    public function get_text(string|null $text_gedcomnr)
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
     * FUNCTION     : Get a single event from database.
     * QUERY        : SELECT * FROM humo_events WHERE event_id=:event_id
     * RETURNS      : a single event.
     */
    public function get_event(int|null $event_id)
    {
        try {
            $sql = "SELECT e.*, l.location_location AS event_place
                FROM humo_events e
                LEFT JOIN humo_location l ON e.place_id = l.location_id
                WHERE e.event_id=:event_id";

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
     * FUNCTION     : Get all selected events from database.
     * QUERY        : SELECT * FROM humo_events WHERE event_tree_id=:event_tree_id AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order
     * RETURNS      : multiple selected events.
     */
    public function get_events_kind(string $event_event, string $event_kind)
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
     * FUNCTION     : Get all selected events by a person, family etc. from database.
     * QUERY        : SELECT * FROM humo_events
     *                WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind
     *                AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order
     * RETURNS      : all selected events by a person.
     */
    public function get_events_connect(string $event_connect_kind, string $event_connect_id, string $event_kind)
    {
        try {
            if ($event_kind === 'all') {
                $sql = "SELECT e.*, l.location_location AS event_place
                    FROM humo_events e
                    LEFT JOIN humo_location l ON e.place_id = l.location_id
                    WHERE e.event_tree_id=:event_tree_id 
                    AND e.event_connect_kind=:event_connect_kind 
                    AND e.event_connect_id=:event_connect_id 
                    ORDER BY e.event_order";
                $params = [
                    ':event_tree_id' => $this->tree_id,
                    ':event_connect_kind' => $event_connect_kind,
                    ':event_connect_id' => $event_connect_id
                ];
            } else {
                $sql = "SELECT e.*, l.location_location AS event_place
                    FROM humo_events e
                    LEFT JOIN humo_location l ON e.place_id = l.location_id
                    WHERE e.event_tree_id=:event_tree_id 
                    AND e.event_connect_kind=:event_connect_kind 
                    AND e.event_connect_id=:event_connect_id 
                    AND e.event_kind=:event_kind 
                    ORDER BY e.event_order";
                $params = [
                    ':event_tree_id' => $this->tree_id,
                    ':event_connect_kind' => $event_connect_kind,
                    ':event_connect_id' => $event_connect_id,
                    ':event_kind' => $event_kind
                ];
            }

            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($params);
            $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br/>";
        }
        return $events;
    }

    /**
     * FUNCTION     : Get a single source from database.
     * QUERY 1      : SELECT * FROM humo_sources WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr
     * QUERY 2      : SELECT * FROM humo_sources WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr AND source_status!='restricted'"
     * RETURNS      : a single source.
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
     * FUNCTION     : Get a single address from database.
     * QUERY        : SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr
     * RETURNS    : a single address.
     */
    public function get_address(string|null $address_gedcomnr)
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
     * FUNCTION     : Get all places by a person, family etc. from database.
     *              SELECT * FROM humo_connections
     *                  LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
     *                  WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
     *                  AND connect_kind=:connect_kind
     *                  AND connect_sub_kind=:connect_sub_kind
     *                  AND connect_connect_id=:connect_connect_connect_id
     *                  ORDER BY connect_order
     * RETURNS      : all places by a person, family etc.
     */
    public function get_addresses(string $connect_kind, string $connect_sub_kind, string $connect_connect_id)
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
     * FUNCTION     : Get multiple connections (sources or addresses) from database.
     * QUERY        : SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id
     * RETURNS      : multiple connections.
     */
    public function get_connections(string $connect_sub_kind, string $connect_item_id)
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
     * FUNCTION     : Get multiple connections from database (for a person or family).
     * QUERY        : SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
     *                  AND connect_kind=:connect_kind AND connect_sub_kind=:connect_sub_kind
     *                  AND connect_connect_id=:connect_connect_id ORDER BY connect_order
     * RETURNS      : multiple connections.
     * EXAMPLE      : $connect_sql = $db_functions->get_connections_connect_id('person','pers_object',$event_connect_id);
     */
    public function get_connections_connect_id(string $connect_kind, string $connect_sub_kind, string $connect_connect_id)
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
     * FUNCTION     : Get a single repository from database.
     * QUERY        : SELECT * FROM humo_repositories WHERE repo_tree_id=:repo_tree_id AND repo_gedcomnr=:repo_gedcomnr
     * RETURNS      : a single repository.
     */
    public function get_repository(string|null $repo_gedcomnr)
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
     * FUNCTION     : Update humo_settings.
     * QUERY        : UPDATE humo_settings SET setting_value=:setting_value WHERE setting_variable=:setting_variable
     * RETURNS      : result.
     */
    public function update_settings(string $setting_variable, string $setting_value): bool
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
    public function generate_gedcomnr(int $tree_id, string $item): string
    {
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
                    if ($resultDb->address_gedcomnr) {
                        $gednum = (int)(preg_replace('/\D/', '', $resultDb->address_gedcomnr));
                        if ($gednum > $new_gedcomnumber) {
                            $new_gedcomnumber = $gednum;
                        }
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
