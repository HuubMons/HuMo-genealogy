<?php

class db_functions
{

	private $db;
	public $tree_id = '';
	public $tree_prefix = '';

	public function __construct($databaseConnection)
	{
		$this->db = $databaseConnection; // db_login actualy
	}

	// *** Set family tree_id ***
	public function set_tree_id($tree_id)
	{
		$get_tree_prefix = $this->db->prepare("SELECT tree_prefix FROM humo_trees WHERE tree_id=:tree_id");
		$get_tree_prefix->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
		$get_tree_prefix->execute();
		$get_tree_prefixDb = $get_tree_prefix->fetch(PDO::FETCH_OBJ);
		if (isset($get_tree_prefixDb->tree_prefix)) $this->tree_prefix = $get_tree_prefixDb->tree_prefix;
	}


	public function check_visitor($ip_address, $block = 'total')
	{
		$allowed = true;
		$check_fails = 0;

		// *** Check last 20 logins of IP address ***
		if ($block == 'total') {
			$sql = "SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':log_ip_address', $ip_address, PDO::PARAM_STR);
			$stmt->execute();
			$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
			foreach ($result_array as $data2Db) {
				if (@$data2Db->log_status == 'failed') $check_fails++;
			}

			if ($check_fails > 20) $allowed = false;
		}

		// *** Check IP Blacklist ***
		$check = $this->db->query("SELECT * FROM humo_settings
		WHERE setting_variable='ip_blacklist'");
		while ($checkDb = $check->fetch(PDO::FETCH_OBJ)) {
			$list = explode("|", $checkDb->setting_value);
			//if ($ip_address==$list[0]) $allowed=false;
			if (strcmp($ip_address, $list[0]) == 0) $allowed = false;
		}

		return $allowed;
	}

	/**
	 * @deprecated Use Authenticator insteed
	 */
	/* public function get_user($user_name, $user_password)
	{
		$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password_salted!=''";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		$isPasswordCorrect = false;

		if (isset($qryDb->user_password_salted)) {
			$isPasswordCorrect = password_verify($user_password, $qryDb->user_password_salted);
		}

		if (!$isPasswordCorrect) {
			// *** Old method without salt, update to new method including salt ***
			$sql = "SELECT * FROM humo_users WHERE (user_name=:user_name OR user_mail=:user_name) AND user_password=:user_password";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
			$stmt->bindValue(':user_password', MD5($user_password), PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

			// *** Update to new method including salt ***
			if ($qryDb) {
				$hashToStoreInDb = password_hash($user_password, PASSWORD_DEFAULT);
				$sql = "UPDATE humo_users SET user_password_salted='" . $hashToStoreInDb . "', user_password='' WHERE user_id=" . $qryDb->user_id;
				$result = $this->db->query($sql);
			}
		}
		return $qryDb;
	} */

	public function get_tree($tree_prefix)
	{
		if (substr($tree_prefix, 0, 4) == 'humo') {
			// *** Found tree_prefix humox_ ***
			$sql = "SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_prefix', $tree_prefix, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		} elseif (is_numeric($tree_prefix)) {
			// **** Found tree_id, numeric value ***
			$sql = "SELECT * FROM humo_trees WHERE tree_id=:tree_id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_prefix, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		}
		return $qryDb;
	}


	public function get_trees()
	{
		$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}


	public function check_person($pers_gedcomnumber)
	{
		if ($pers_gedcomnumber != '') {
			$sql = "SELECT pers_id FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

			if (!isset($qryDb->pers_id)) {
				echo '<b>' . __('Something went wrong, there is no valid person id.') . '</b>';
				exit();
			}
		}
	}

	public function get_person($pers_gedcomnumber, $item = '')
	{
		if ($item == 'famc-fams') {
			$sql = "SELECT pers_famc, pers_fams FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		} else {
			$sql = "SELECT * FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		}

		return $qryDb;
	}

	public function get_person_with_id($pers_id)
	{
		$sql = "SELECT * FROM humo_persons WHERE pers_id=:pers_id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':pers_id', $pers_id, PDO::PARAM_INT);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}

	public function count_persons($tree_id)
	{
		$sql = "SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id=:pers_tree_id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':pers_tree_id', $tree_id, PDO::PARAM_INT);
		$stmt->execute();
		$nr_persons = $stmt->fetchColumn();

		return $nr_persons;
	}


	public function check_family($fam_gedcomnumber)
	{
		if ($fam_gedcomnumber != '') {
			$sql = "SELECT fam_id FROM humo_families
				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

			if (!isset($qryDb->fam_id)) {
				echo '<b>' . __('Something went wrong, there is no valid family id.') . '</b>';
				exit();
			}
		}
	}

	public function get_family($fam_gedcomnumber, $item = '')
	{
		if ($item == 'man-woman') {
			$sql = "SELECT fam_man, fam_woman, fam_children FROM humo_families
				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		} else {
			$sql = "SELECT * FROM humo_families
				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$stmt->execute();
			$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
		}

		return $qryDb;
	}


	public function count_families($tree_id)
	{
		$sql = "SELECT COUNT(*) FROM humo_families WHERE fam_tree_id=:fam_tree_id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':fam_tree_id', $tree_id, PDO::PARAM_INT);
		$stmt->execute();
		$nr_families = $stmt->fetchColumn();

		return $nr_families;
	}


	public function get_text($text_gedcomnr)
	{
		$sql = "SELECT * FROM humo_texts
		WHERE text_tree_id=:text_tree_id AND text_gedcomnr=:text_gedcomnr";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':text_tree_id', $this->tree_id, PDO::PARAM_INT);
		$stmt->bindValue(':text_gedcomnr', $text_gedcomnr, PDO::PARAM_STR);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}


	public function get_event($event_id)
	{
		$sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':event_id', $event_id, PDO::PARAM_INT);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}

	public function get_events_kind($event_event, $event_kind)
	{
		$sql = "SELECT * FROM humo_events
			WHERE event_tree_id=:event_tree_id AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		//$stmt->bindValue(':event_event', $event_event, PDO::PARAM_INT); // Gaat fout in PHP 7.2. Controle op waarde: I39
		$stmt->bindValue(':event_event', $event_event, PDO::PARAM_STR);
		$stmt->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}


	public function get_events_connect($event_connect_kind, $event_connect_id, $event_kind)
	{
		$sql = "SELECT * FROM humo_events
			WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':event_connect_kind', $event_connect_kind, PDO::PARAM_STR);
		$stmt->bindValue(':event_connect_id', $event_connect_id, PDO::PARAM_STR);
		$stmt->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}

	public function get_source($source_gedcomnr)
	{
		$sql = "SELECT * FROM humo_sources
			WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
		$stmt->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}


	public function get_address($address_gedcomnr)
	{
		$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':address_gedcomnr', $address_gedcomnr, PDO::PARAM_STR);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}


	public function get_addresses($connect_kind, $connect_sub_kind, $connect_connect_id)
	{
		$sql = "SELECT * FROM humo_connections
			LEFT JOIN humo_addresses ON address_gedcomnr=connect_item_id
			WHERE connect_tree_id=:connect_tree_id AND address_tree_id=:connect_tree_id
			AND connect_kind=:connect_kind
			AND connect_sub_kind=:connect_sub_kind
			AND connect_connect_id=:connect_connect_id
			ORDER BY connect_order";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
		$stmt->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$stmt->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}

	public function get_connections($connect_sub_kind, $connect_item_id)
	{
		$sql = "SELECT * FROM humo_connections 
			WHERE connect_tree_id=:connect_tree_id 
			AND connect_sub_kind=:connect_sub_kind 
			AND connect_item_id=:connect_item_id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$stmt->bindValue(':connect_item_id', $connect_item_id, PDO::PARAM_STR);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}


	public function get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id)
	{

		$sql = "SELECT * FROM humo_connections 
			WHERE connect_tree_id=:connect_tree_id 
			AND connect_kind=:connect_kind 
			AND connect_sub_kind=:connect_sub_kind 
			AND connect_connect_id=:connect_connect_id 
			ORDER BY connect_order";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
		$stmt->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$stmt->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
		$stmt->execute();
		$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);

		return $result_array;
	}


	public function get_repository($repo_gedcomnr)
	{
		$sql = "SELECT * FROM humo_repositories
			WHERE repo_tree_id=:repo_tree_id 
			AND repo_gedcomnr=:repo_gedcomnr";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':repo_tree_id', $this->tree_id, PDO::PARAM_STR);
		$stmt->bindValue(':repo_gedcomnr', $repo_gedcomnr, PDO::PARAM_STR);
		$stmt->execute();
		$qryDb = $stmt->fetch(PDO::FETCH_OBJ);

		return $qryDb;
	}


	public function update_settings($setting_variable, $setting_value)
	{
		$sql = "UPDATE humo_settings SET setting_value=:setting_value WHERE setting_variable=:setting_variable";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':setting_variable', $setting_variable, PDO::PARAM_STR);
		$stmt->bindValue(':setting_value', $setting_value, PDO::PARAM_STR);
		$stmt->execute();
	}

	public function generate_gedcomnr($tree_id, $item)
	{
		$new_gedcomnumber = 0;
		// *** Command preg_replace \D removes all non-digit characters (including spaces etc.) ***
		// *** This will work for all kinds of GEDCOM numbers like I1234, 1234I, U1234, X1234. ***
		if ($item == 'person') {
			$sql = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id=:tree_id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
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
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
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
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
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
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
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
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
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
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
			$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
			foreach ($result_array as $resultDb) {
				$gednum = (int)(preg_replace('/\D/', '', $resultDb->text_gedcomnr));
				if ($gednum > $new_gedcomnumber) {
					$new_gedcomnumber = $gednum;
				}
			}
		}
		if ($item == 'event') {
			$sql = "SELECT event_gedcomnr FROM humo_events WHERE event_tree_id=:tree_id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$stmt->execute();
			$result_array = $stmt->fetchAll(PDO::FETCH_OBJ);
			foreach ($result_array as $resultDb) {
				$gednum = (int)(preg_replace('/\D/', '', $resultDb->event_gedcomnr));
				if ($gednum > $new_gedcomnumber) {
					$new_gedcomnumber = $gednum;
				}
			}
		}

		$new_gedcomnumber++;
		return $new_gedcomnumber;
	}

}
