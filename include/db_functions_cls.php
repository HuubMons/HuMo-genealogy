<?php
/*--------------------[database functions]----------------------------
 *
 * AUTHOR		: Huub Mons. Created: jan. 2015.
 * THANKS TO	: Michael.j.Falconer
 *
 * FUNCTIONS:
 *		check_visitor				Check for valid visitor.
 *		get_user					Check if user exists.
 *		get_tree					Get data from selected family tree.
 *		get_trees					Get data from all family trees.
 *		check_person				Check if person is valid.
 *		get_person					Get a single person from database.
 *		get_person_with_id			Get a single person from database using id number.
 *		count_persons				Count persons in family tree.
 *		check_family				Check if family is valid.
 *		get_family					Get a single family from database.
 *		count_families				Count families in family tree.
 *		get_event					Get a single event from database.
 *		get_events_kind				Get multiple events of one event_kind from database. Example:
 *		get_events_connect			Get multiple events of a connected person, family etc. selecting one event_kind from database.
 *		get_source					Get a single source from database.
 *		get_address					Get a single address from database.
 *		get_addressses				Get all adresses (places) by a person, family, etc.
 *		get_connections				Get multiple connections (used for sources and addresses).
 *		get_connections_connect_id	Get multiple connections of a person or family.
 *		get_repository				Get a single repository from database.
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
 *----------------------------------------------------------------
 */

class db_functions
{

	private $query = array();
	public $tree_id = '';
	public $tree_prefix = '';

	function __construct()
	{
		global $dbh;

		// *** Prepared statements ***
		if ($dbh) {
			$sql = "SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11";
			$this->query['check_visitor'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''";
			$this->query['get_user'] = $dbh->prepare($sql);
			$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password";
			$this->query['get_user_no_salt'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix";
			$this->query['get_tree_prefix'] = $dbh->prepare($sql);
			$sql = "SELECT * FROM humo_trees WHERE tree_id=:tree_id";
			$this->query['get_tree_id'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
			$this->query['get_trees'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_repositories
			WHERE repo_tree_id=:repo_tree_id AND repo_gedcomnr=:repo_gedcomnr";
			$this->query['get_repositories'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_sources
			WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr";
			$this->query['get_source'] = $dbh->prepare($sql);
			// *** Hide restricted source ***
			//$sql = "SELECT * FROM humo_sources
			//	WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr
			//	AND source_status!='restricted'";
			//$this->query['get_source_restricted'] = $dbh->prepare( $sql );

			// *** Person queries ***
			$sql = "SELECT * FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$this->query['get_person'] = $dbh->prepare($sql);

			$sql = "SELECT pers_id FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$this->query['check_person'] = $dbh->prepare($sql);

			$sql = "SELECT pers_famc, pers_fams FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$this->query['get_person_fams'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_persons WHERE pers_id=:pers_id";
			$this->query['get_person_with_id'] = $dbh->prepare($sql);

			$sql = "SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id";
			$this->query['count_persons'] = $dbh->prepare($sql);

			// *** Family queries ***
			$sql = "SELECT fam_id FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$this->query['check_family'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$this->query['get_family'] = $dbh->prepare($sql);

			$sql = "SELECT fam_man, fam_woman, fam_children FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$this->query['get_family_man_woman'] = $dbh->prepare($sql);

			$sql = "SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id";
			$this->query['count_families'] = $dbh->prepare($sql);

			// *** Text queries ***
			$sql = "SELECT * FROM humo_texts
			WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr";
			$this->query['get_text'] = $dbh->prepare($sql);

			// *** Event queries ***
			$sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
			$this->query['get_event'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_events
			WHERE event_tree_id=:event_tree_id AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order";
			$this->query['get_events_kind'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_events
			WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order";
			$this->query['get_events_connect'] = $dbh->prepare($sql);

			// *** Address queries ***
			$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
			$this->query['get_address'] = $dbh->prepare($sql);

			$sql = "SELECT * FROM humo_connections
			LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
			WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
			AND connect_kind=:connect_kind
			AND connect_sub_kind=:connect_sub_kind
			AND connect_connect_id=:connect_connect_id
			ORDER BY connect_order";
			$this->query['get_addresses'] = $dbh->prepare($sql);

			// *** Connection queries ***
			$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id";
			$this->query['get_connections'] = $dbh->prepare($sql);
			$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_kind=:connect_kind AND connect_sub_kind=:connect_sub_kind AND connect_connect_id=:connect_connect_id ORDER BY connect_order";
			$this->query['get_connections_connect_id'] = $dbh->prepare($sql);

			// *** Update humo_settings ***
			$sql = "UPDATE humo_settings SET setting_value=:setting_value WHERE setting_variable=:setting_variable";
			$this->query['update_settings'] = $dbh->prepare($sql);

			// *** Generate new gedcomnumber PERSON ***
			$sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
			$this->query['generate_gedcomnr_person'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber FAMILY ***
			$sql = "SELECT fam_gedcomnumber FROM humo_families WHERE fam_tree_id=:tree_id";
			$this->query['generate_gedcomnr_family'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber SOURCE ***
			$sql = "SELECT source_gedcomnr FROM humo_sources WHERE source_tree_id=:tree_id";
			$this->query['generate_gedcomnr_source'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber ADDRESS ***
			$sql = "SELECT address_gedcomnr FROM humo_addresses WHERE address_tree_id=:tree_id";
			$this->query['generate_gedcomnr_address'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber REPO ***
			$sql = "SELECT repo_gedcomnr FROM humo_repositories WHERE repo_tree_id=:tree_id";
			$this->query['generate_gedcomnr_repo'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber TEXT ***
			$sql = "SELECT text_gedcomnr FROM humo_texts WHERE text_tree_id=:tree_id";
			$this->query['generate_gedcomnr_text'] = $dbh->prepare($sql);
			// *** Generate new gedcomnumber EVENT ***
			$sql = "SELECT event_gedcomnr FROM humo_events WHERE event_tree_id=:tree_id";
			$this->query['generate_gedcomnr_event'] = $dbh->prepare($sql);
		}
	}

	// *** Set family tree_id ***
	function set_tree_id($tree_id)
	{
		if (is_numeric($tree_id)) $this->tree_id = $tree_id;

		// *** Also set tree_prefix variable ***
		global $dbh;
		$get_tree_prefix = $dbh->prepare("SELECT tree_prefix FROM humo_trees WHERE tree_id=:tree_id");
		$get_tree_prefix->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
		$get_tree_prefix->execute();
		$get_tree_prefixDb = $get_tree_prefix->fetch(PDO::FETCH_OBJ);
		if (isset($get_tree_prefixDb->tree_prefix)) $this->tree_prefix = $get_tree_prefixDb->tree_prefix;
	}

	/*--------------------[check_visitor]------------------------------
 * FUNCTION	: Check visitor
 * QUERY	: SELECT * FROM humo_user_log
 *				WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11
 * RETURNS	: True/ false.
 *----------------------------------------------------------------
 */
	// *** $block: can be used to totally or partially (no login page) block the website ***
	function check_visitor($ip_address, $block = 'total')
	{
		global $dbh;
		$allowed = true;
		$check_fails = 0;

		// *** Check last 20 logins of IP address ***
		if ($block == 'total') {
			try {
				$this->query['check_visitor']->bindValue(':log_ip_address', $ip_address, PDO::PARAM_STR);
				$this->query['check_visitor']->execute();
				$this->query['check_visitor']->execute();
				$result_array = $this->query['check_visitor']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $data2Db) {
					if (@$data2Db->log_status == 'failed') $check_fails++;
				}
			} catch (PDOException $e) {
				//echo $e->getMessage() . "<br/>";
			}
			if ($check_fails > 20) $allowed = false;
		}

		// *** Check IP Blacklist ***
		$check = $dbh->query("SELECT * FROM humo_settings
		WHERE setting_variable='ip_blacklist'");
		while ($checkDb = $check->fetch(PDO::FETCH_OBJ)) {
			$list = explode("|", $checkDb->setting_value);
			//if ($ip_address==$list[0]) $allowed=false;
			if (strcmp($ip_address, $list[0]) == 0) $allowed = false;
		}

		return $allowed;
	}

	/*--------------------[get user]----------------------------------
 * FUNCTION	: Get user from database return false if it isn't.
 * QUERY	: SELECT * FROM humo_users
 *				(user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''

 * QUERY	: SELECT * FROM humo_users
 *				(user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password
 * RETURNS	: user data.
 *----------------------------------------------------------------
 */
	function get_user($user_name, $user_password)
	{
		global $dbh;
		$qryDb = false;

		// *** First check password method using salt ***
		$this->query['get_user']->bindValue(':user_name', $user_name, PDO::PARAM_STR);
		$this->query['get_user']->execute();
		$qryDb = $this->query['get_user']->fetch(PDO::FETCH_OBJ);
		$isPasswordCorrect = false;
		//if (isset($qryDb)){
		if (isset($qryDb->user_password_salted)) {
			$isPasswordCorrect = password_verify($user_password, $qryDb->user_password_salted);
		}

		if (!$isPasswordCorrect) {
			// *** Old method without salt, update to new method including salt ***
			$qryDb = false;
			try {
				$this->query['get_user_no_salt']->bindValue(':user_name', $user_name, PDO::PARAM_STR);
				$this->query['get_user_no_salt']->bindValue(':user_password', MD5($user_password), PDO::PARAM_STR);
				$this->query['get_user_no_salt']->execute();
				$qryDb = $this->query['get_user_no_salt']->fetch(PDO::FETCH_OBJ);

				// *** Update to new method including salt ***
				if ($qryDb) {
					$hashToStoreInDb = password_hash($user_password, PASSWORD_DEFAULT);
					$sql = "UPDATE humo_users SET user_password_salted='" . $hashToStoreInDb . "', user_password='' WHERE user_id=" . $qryDb->user_id;
					$result = $dbh->query($sql);
				}
			} catch (PDOException $e) {
				echo $e->getMessage() . "<br/>";
			}
		}
		return $qryDb;
	}

	/*--------------------[get tree]--------------------------------
 * FUNCTION	: Get family tree data from database.
 * QUERY 1	: SELECT * FROM humo_trees
 *				WHERE tree_prefix=:tree_prefix
 * QUERY 2	: SELECT * FROM humo_trees
 *				WHERE tree_id=:tree_id
 * RETURNS	: family tree data.
 *----------------------------------------------------------------
 */
	function get_tree($tree_prefix)
	{
		$qryDb = false;
		// *** Detection of tree_prefix/ tree_id ***
		if (substr($tree_prefix, 0, 4) == 'humo') {
			// *** Found tree_prefix humox_ ***
			try {
				$this->query['get_tree_prefix']->bindValue(':tree_prefix', $tree_prefix, PDO::PARAM_STR);
				$this->query['get_tree_prefix']->execute();
				$qryDb = $this->query['get_tree_prefix']->fetch(PDO::FETCH_OBJ);
			} catch (PDOException $e) {
				echo $e->getMessage() . "<br/>";
			}
		} elseif (is_numeric($tree_prefix)) {
			// **** Found tree_id, numeric value ***
			try {
				$this->query['get_tree_id']->bindValue(':tree_id', $tree_prefix, PDO::PARAM_STR);
				$this->query['get_tree_id']->execute();
				$qryDb = $this->query['get_tree_id']->fetch(PDO::FETCH_OBJ);
			} catch (PDOException $e) {
				echo $e->getMessage() . "<br/>";
			}
		}
		return $qryDb;
	}

	/*--------------------[get data from all trees ]------------------
 * FUNCTION	: Get all data from family trees.
 * QUERY	: SELECT * FROM humo_trees
 *				WHERE tree_prefix!='EMPTY' ORDER BY tree_order
 * RETURNS	: all data from family trees.
 *----------------------------------------------------------------
 */
	function get_trees()
	{
		$result_array = array();
		try {
			$this->query['get_trees']->execute();
			$result_array = $this->query['get_trees']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[check person]------------------------------
 * FUNCTION	: Check for valid person in database.
 * QUERY 1	: SELECT pers_id FROM humo_persons
 *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
 * RETURNS	: Check for valid person.
 *----------------------------------------------------------------
 */
	function check_person($pers_gedcomnumber)
	{
		if ($pers_gedcomnumber != '') {
			try {
				$this->query['check_person']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['check_person']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
				$this->query['check_person']->execute();
				$qryDb = $this->query['check_person']->fetch(PDO::FETCH_OBJ);
			} catch (PDOException $e) {
				echo $e->getMessage() . "<br/>";
			}
			if (!isset($qryDb->pers_id)) {
				echo '<b>' . __('Something went wrong, there is no valid person id.') . '</b>';
				exit();
			}
		}
	}

	/*--------------------[get person]--------------------------------
 * FUNCTION	: Get a single person from database.
 * QUERY 1	: SELECT * FROM humo_persons
 *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
 * QUERY 2	: SELECT pers_famc, pers_fams FROM humo_persons
 *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
 * RETURNS	: a single person.
 *----------------------------------------------------------------
 */
	function get_person($pers_gedcomnumber, $item = '')
	{
		$qryDb = false;
		try {
			if ($item == 'famc-fams') {
				$this->query['get_person_fams']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['get_person_fams']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
				$this->query['get_person_fams']->execute();
				$qryDb = $this->query['get_person_fams']->fetch(PDO::FETCH_OBJ);
			} else {
				$this->query['get_person']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['get_person']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
				$this->query['get_person']->execute();
				$qryDb = $this->query['get_person']->fetch(PDO::FETCH_OBJ);
			}
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[get person with id]-----------------------
 * FUNCTION	: Get a single person from database.
 * QUERY	: SELECT * FROM humo_persons WHERE pers_id=:pers_tree_id
 * RETURNS	: a single person.
 *----------------------------------------------------------------
 */
	function get_person_with_id($pers_id)
	{
		$qryDb = false;
		try {
			$this->query['get_person_with_id']->bindValue(':pers_id', $pers_id, PDO::PARAM_INT);
			$this->query['get_person_with_id']->execute();
			$qryDb = $this->query['get_person_with_id']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[count_persons]-------------------------------------------
 * FUNCTION	: Count persons in selected family tree.
 * QUERY	: SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id
 * RETURNS	: Number of persons in family tree.
 *------------------------------------------------------------------------------
 */
	function count_persons($tree_id)
	{
		$nr_persons = 0;
		try {
			$this->query['count_persons']->bindValue(':pers_tree_id', $tree_id, PDO::PARAM_INT);
			$this->query['count_persons']->execute();
			$nr_persons = $this->query['count_persons']->fetchColumn();
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $nr_persons;
	}

	/*--------------------[check family]------------------------------
 * FUNCTION	: Check for valid family in database.
 * QUERY 1	: SELECT fam_id FROM humo_families
 *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber
 * RETURNS	: Check for valid family.
 *----------------------------------------------------------------
 */
	function check_family($fam_gedcomnumber)
	{
		if ($fam_gedcomnumber != '') {
			try {
				$this->query['check_family']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['check_family']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
				$this->query['check_family']->execute();
				$qryDb = $this->query['check_family']->fetch(PDO::FETCH_OBJ);
			} catch (PDOException $e) {
				echo $e->getMessage() . "<br/>";
			}
			if (!isset($qryDb->fam_id)) {
				echo '<b>' . __('Something went wrong, there is no valid family id.') . '</b>';
				exit();
			}
		}
	}

	/*--------------------[get family]--------------------------------
 * FUNCTION	: Get a single family from database.
 * QUERY 1	: SELECT fam_man, fam_woman FROM humo_families
 *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
 * QUERY 2	: SELECT * FROM humo_families
 *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
 * USE		: get_family($fam_number,'man-woman') to get man and woman id.
 * RETURNS	: a single family.
 *----------------------------------------------------------------
 */
	function get_family($fam_gedcomnumber, $item = '')
	{
		$qryDb = false;
		try {
			if ($item == 'man-woman') {
				$this->query['get_family_man_woman']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['get_family_man_woman']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
				$this->query['get_family_man_woman']->execute();
				$qryDb = $this->query['get_family_man_woman']->fetch(PDO::FETCH_OBJ);
			} else {
				$this->query['get_family']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
				$this->query['get_family']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
				$this->query['get_family']->execute();
				$qryDb = $this->query['get_family']->fetch(PDO::FETCH_OBJ);
			}
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[count_families]-----------------------------------------
 * FUNCTION	: Count families in selected family tree.
 * QUERY	: SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id
 * RETURNS	: Number of families in family tree.
 *-----------------------------------------------------------------------------
 */
	function count_families($tree_id)
	{
		$nr_families = 0;
		try {
			$this->query['count_families']->bindValue(':fam_tree_id', $tree_id, PDO::PARAM_INT);
			$this->query['count_families']->execute();
			$nr_families = $this->query['count_families']->fetchColumn();
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $nr_families;
	}

	/*--------------------[get text]----------------------------------
 * FUNCTION	: Get a single text from database.
 * QUERY	: SELECT * FROM humo_texts
 * 				WHERE fam_tree_id=:fam_tree_id AND text_gedcomnr=:text_gedcomnr
 * RETURNS	: a single text.
 *----------------------------------------------------------------
 */
	function get_text($text_gedcomnr)
	{
		$qryDb = false;
		try {
			$this->query['get_text']->bindValue(':text_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_text']->bindValue(':text_gedcomnr', $text_gedcomnr, PDO::PARAM_STR);
			$this->query['get_text']->execute();
			$qryDb = $this->query['get_text']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[get event]---------------------------------
 * FUNCTION	: Get a single event from database.
 * QUERY	: SELECT * FROM humo_events WHERE event_id=:event_id
 * RETURNS	: a single event.
 *----------------------------------------------------------------
 */
	function get_event($event_id)
	{
		$qryDb = false;
		try {
			// *** Don't need tree_id, it's a direct event_id ***
			$this->query['get_event']->bindValue(':event_id', $event_id, PDO::PARAM_INT);
			$this->query['get_event']->execute();
			$qryDb = $this->query['get_event']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[get events]--------------------------------
 * FUNCTION	: Get all selected events from database.
 * QUERY	: SELECT * FROM humo_events WHERE event_tree_id=:event_tree_id
 *				 AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order
 * RETURNS	: multiple selected events.
 *----------------------------------------------------------------
 */
	function get_events_kind($event_event, $event_kind)
	{
		$result_array = array();
		try {
			$this->query['get_events_kind']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
			//$this->query['get_events_kind']->bindValue(':event_event', $event_event, PDO::PARAM_INT); // Gaat fout in PHP 7.2. Controle op waarde: I39
			$this->query['get_events_kind']->bindValue(':event_event', $event_event, PDO::PARAM_STR);
			$this->query['get_events_kind']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
			$this->query['get_events_kind']->execute();
			$result_array = $this->query['get_events_kind']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[get events from a connection: person or family etc. ]----------------
 * FUNCTION	: Get all selected events by a person, family etc. from database.
 * QUERY	: SELECT * FROM humo_events
 *				WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind
 *				AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order
 * RETURNS	: all selected events by a person.
 *------------------------------------------------------------------------------------------
 */
	function get_events_connect($event_connect_kind, $event_connect_id, $event_kind)
	{
		$result_array = array();
		try {
			$this->query['get_events_connect']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_events_connect']->bindValue(':event_connect_kind', $event_connect_kind, PDO::PARAM_STR);
			$this->query['get_events_connect']->bindValue(':event_connect_id', $event_connect_id, PDO::PARAM_STR);
			$this->query['get_events_connect']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
			$this->query['get_events_connect']->execute();
			$result_array = $this->query['get_events_connect']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[get source]--------------------------------
 * FUNCTION	: Get a single source from database.
 * QUERY 1	: SELECT * FROM humo_sources
 *				WHERE source_tree_id=:source_tree_id
 *				AND source_gedcomnr=:source_gedcomnr
 * QUERY 2	: SELECT * FROM humo_sources
 *				WHERE source_tree_id=:source_tree_id
 *				AND source_gedcomnr=:source_gedcomnr
 *				AND source_status!='restricted'"
 * RETURNS	: a single source.
 *----------------------------------------------------------------
 */
	function get_source($source_gedcomnr)
	{
		global $user;
		$qryDb = false;
		try {
			// *** REMARK: it's easier to check for restricted sources in the script that uses this function, so disabled this code ***
			//if ($user['group_show_restricted_source']=='n'){
			$this->query['get_source']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_source']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
			$this->query['get_source']->execute();
			$qryDb = $this->query['get_source']->fetch(PDO::FETCH_OBJ);
			//}
			//else{
			//	$this->query['get_source_restricted']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
			//	$this->query['get_source_restricted']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
			//	$this->query['get_source_restricted']->execute();
			//	$qryDb=$this->query['get_source_restricted']->fetch(PDO::FETCH_OBJ);
			//}
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[get address]---------------------------------
 * FUNCTION	: Get a single address from database.
 * QUERY	: SELECT * FROM humo_addresses
 * 				WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr
 * RETURNS	: a single address.
 *----------------------------------------------------------------
 */
	function get_address($address_gedcomnr)
	{
		$qryDb = false;
		try {
			$this->query['get_address']->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_address']->bindValue(':address_gedcomnr', $address_gedcomnr, PDO::PARAM_STR);
			$this->query['get_address']->execute();
			$qryDb = $this->query['get_address']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[get addresses (places) ]-------
 * FUNCTION	: Get all places by a person, family etc. from database.
  *			SELECT * FROM humo_connections
 *				LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
 *				WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
 *				AND connect_kind=:connect_kind
 *				AND connect_sub_kind=:connect_sub_kind
 *				AND connect_connect_id=:connect_connect_connect_id
 *				ORDER BY connect_order
  * RETURNS	: all places by a person, family etc.
 *----------------------------------------------------------------
 */
	function get_addresses($connect_kind, $connect_sub_kind, $connect_connect_id)
	{
		$result_array = array();
		try {
			$this->query['get_addresses']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_addresses']->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
			$this->query['get_addresses']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
			$this->query['get_addresses']->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
			$this->query['get_addresses']->execute();
			$result_array = $this->query['get_addresses']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[get connections]---------------------------
 * FUNCTION	: Get multiple connections (sources or addresses) from database.
 * QUERY	: SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
 *				AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id
 * RETURNS	: multiple connections.
 *----------------------------------------------------------------
 */
	function get_connections($connect_sub_kind, $connect_item_id)
	{
		$result_array = array();
		try {
			$this->query['get_connections']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_connections']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
			$this->query['get_connections']->bindValue(':connect_item_id', $connect_item_id, PDO::PARAM_STR);
			$this->query['get_connections']->execute();
			$result_array = $this->query['get_connections']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[get connections_connect_id]----------------
 * FUNCTION	: Get multiple connections from database (for a person or family).
 * QUERY	: SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
 *				AND connect_kind=:connect_kind AND connect_sub_kind=:connect_sub_kind
 *				AND connect_connect_id=:connect_connect_id ORDER BY connect_order
 * RETURNS	: multiple connections.
 * EXAMPLE	: $connect_sql = $db_functions->get_connections_connect_id('person','pers_object',$event_connect_id);
 *----------------------------------------------------------------
 */
	function get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id)
	{
		$result_array = array();
		try {
			$this->query['get_connections_connect_id']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_connections_connect_id']->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
			$this->query['get_connections_connect_id']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
			$this->query['get_connections_connect_id']->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
			$this->query['get_connections_connect_id']->execute();
			$result_array = $this->query['get_connections_connect_id']->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $result_array;
	}

	/*--------------------[get repository]----------------------------
 * FUNCTION	: Get a single repository from database.
 * QUERY	: SELECT * FROM humo_repositories
 *				WHERE repo_tree_id=:repo_tree_id
 *				AND repo_gedcomnr=:repo_gedcomnr
 * RETURNS	: a single repository.
 *----------------------------------------------------------------
 */
	function get_repository($repo_gedcomnr)
	{
		$qryDb = false;
		try {
			$this->query['get_repositories']->bindValue(':repo_tree_id', $this->tree_id, PDO::PARAM_STR);
			$this->query['get_repositories']->bindValue(':repo_gedcomnr', $repo_gedcomnr, PDO::PARAM_STR);
			$this->query['get_repositories']->execute();
			$qryDb = $this->query['get_repositories']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	/*--------------------[update settings]----------------------------
 * FUNCTION	: Update humo_settings.
 * QUERY	: UPDATE humo_settings
 * 				SET setting_value=:setting_value WHERE setting_variable=:setting_variable
 * RETURNS	: result.
 *----------------------------------------------------------------
 */
	function update_settings($setting_variable, $setting_value)
	{
		$qryDb = false;
		try {
			$this->query['update_settings']->bindValue(':setting_variable', $setting_variable, PDO::PARAM_STR);
			$this->query['update_settings']->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
			$this->query['update_settings']->execute();
			//DON'T USE FOR UPDATE QUERY: $qryDb=$this->query['update_settings']->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		return $qryDb;
	}

	// *** Generate new GEDCOM number for item (person, family, source, repo, address, etc.) ***
	//	Generates new GEDCOM number (only numerical, like: 1234).
	//	$sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
	//	$sql = "SELECT fam_gedcomnumber FROM humo_families WHERE pers_tree_id=:tree_id";
	function generate_gedcomnr($tree_id, $item)
	{
		$qryDb = false;
		$new_gedcomnumber = 0;
		try {
			// *** Command preg_replace \D removes all non-digit characters (including spaces etc.) ***
			// *** This will work for all kinds of GEDCOM numbers like I1234, 1234I, U1234, X1234. ***
			if ($item == 'person') {
				$this->query['generate_gedcomnr_person']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_person']->execute();
				$result_array = $this->query['generate_gedcomnr_person']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->pers_gedcomnumber));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'family') {
				$this->query['generate_gedcomnr_family']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_family']->execute();
				$result_array = $this->query['generate_gedcomnr_family']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->fam_gedcomnumber));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'source') {
				$this->query['generate_gedcomnr_source']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_source']->execute();
				$result_array = $this->query['generate_gedcomnr_source']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->source_gedcomnr));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'address') {
				$this->query['generate_gedcomnr_address']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_address']->execute();
				$result_array = $this->query['generate_gedcomnr_address']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->address_gedcomnr));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'repo') {
				$this->query['generate_gedcomnr_repo']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_repo']->execute();
				$result_array = $this->query['generate_gedcomnr_repo']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->repo_gedcomnr));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'text') {
				$this->query['generate_gedcomnr_text']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_text']->execute();
				$result_array = $this->query['generate_gedcomnr_text']->fetchAll(PDO::FETCH_OBJ);
				foreach ($result_array as $resultDb) {
					$gednum = (int)(preg_replace('/\D/', '', $resultDb->text_gedcomnr));
					if ($gednum > $new_gedcomnumber) {
						$new_gedcomnumber = $gednum;
					}
				}
			}
			if ($item == 'event') {
				$this->query['generate_gedcomnr_event']->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
				$this->query['generate_gedcomnr_event']->execute();
				$result_array = $this->query['generate_gedcomnr_event']->fetchAll(PDO::FETCH_OBJ);
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
} // *** End of db_functions class ***
