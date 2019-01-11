<?php 
/*--------------------[database functions]----------------------------
 *
 * AUTHOR		: Huub Mons. jan. 2015.
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
 *		check_family				Check if family is valid.
 *		get_family					Get a single family from database.
 *		get_event					Get a single event from database.
 *		get_events_kind				Get multiple events of one event_kind from database. Example:
 *		get_events_connect			Get multiple events of a connected person, family etc. selecting one event_kind from database.
 *		get_source					Get a single source from database.
 *		get_address					Get a single address from database.
 *		get_addressses				Get a all adresses (places) by a person, family, etc.
 *		get_connections				Get multiple connections (used for sources and addresses).
 *		get_connections_connect_id	Get multiple connections of a person or family.
 *		get_repository				Get a single repository from database.
 *
 * SET family tree variabele:
 *	$db_functions->set_tree_prefix($tree_prefix_quoted);
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

class db_functions{

private $query = array();
var $tree_id='';
var $tree_prefix='';

function __construct($tree_prefix='') {
	global $dbh;

	// *** Prepared statements ***
	if ($dbh){
		$sql = "SELECT * FROM humo_user_log WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11";
		$this->query['check_visitor'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_users
			WHERE user_name=:user_name AND user_password=:user_password";
		$this->query['get_user'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_trees WHERE tree_prefix=:tree_prefix";
		$this->query['get_tree'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$this->query['get_trees'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_repositories
			WHERE repo_tree_id=:repo_tree_id AND repo_gedcomnr=:repo_gedcomnr";
		$this->query['get_repositories'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_sources
			WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr";
		$this->query['get_source'] = $dbh->prepare( $sql );
		// *** Hide restricted source ***
		//$sql = "SELECT * FROM humo_sources
		//	WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr
		//	AND source_status!='restricted'";
		//$this->query['get_source_restricted'] = $dbh->prepare( $sql );

		// *** Person queries ***
		$sql = "SELECT * FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
		$this->query['get_person'] = $dbh->prepare( $sql );

		$sql = "SELECT pers_id FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
		$this->query['check_person'] = $dbh->prepare( $sql );

		$sql = "SELECT pers_famc, pers_fams FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
		$this->query['get_person_fams'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_persons WHERE pers_id=:pers_id";
		$this->query['get_person_with_id'] = $dbh->prepare( $sql );

		// *** Family queries ***
		$sql = "SELECT fam_id FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
		$this->query['check_family'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
		$this->query['get_family'] = $dbh->prepare( $sql );

		$sql = "SELECT fam_man, fam_woman FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
		$this->query['get_family_man_woman'] = $dbh->prepare( $sql );

		// *** Event queries ***
		$sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
		$this->query['get_event'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_events
		WHERE event_tree_id=:event_tree_id AND event_event=:event_event AND event_kind=:event_kind ORDER BY event_order";
		$this->query['get_events_kind'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_events
		WHERE event_tree_id=:event_tree_id AND event_connect_kind=:event_connect_kind AND event_connect_id=:event_connect_id AND event_kind=:event_kind ORDER BY event_order";
		$this->query['get_events_connect'] = $dbh->prepare( $sql );

		// *** Address queries ***
		$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
		$this->query['get_address'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_addresses
			WHERE address_tree_id=:address_tree_id
			AND address_connect_kind=:address_connect_kind
			AND address_connect_id=:address_connect_id
			ORDER BY address_order";
		$this->query['get_addresses'] = $dbh->prepare( $sql );

		// *** Connection queries ***
		$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id";
		$this->query['get_connections'] = $dbh->prepare( $sql );
		$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_kind=:connect_kind AND connect_sub_kind=:connect_sub_kind AND connect_connect_id=:connect_connect_id ORDER BY connect_order";
		$this->query['get_connections_connect_id'] = $dbh->prepare( $sql );

	}
}

// *** Set family tree_id ***
function set_tree_id($tree_id){
	$this->tree_id=$tree_id;
}

// *** Set family tree_prefix ***
function set_tree_prefix($tree_prefix){
	$this->tree_prefix=$tree_prefix;
}

/*--------------------[check_visitor]------------------------------
 * FUNCTION	: Check visitor
 * QUERY	: SELECT * FROM humo_user_log
 *				WHERE log_ip_address=:log_ip_address ORDER BY log_date DESC LIMIT 0,11
 * RETURNS	: True/ false.
 *----------------------------------------------------------------
 */
// *** $block: can be used to totally or partially (no login page) block the website ***
function check_visitor($ip_address,$block='total'){
	global $dbh;
	$allowed=true;
	$check_fails=0;

	// *** Check last 10 logins of IP address ***
	if ($block=='total'){
		try {
			$this->query['check_visitor']->bindValue(':log_ip_address', $ip_address, PDO::PARAM_STR);
			$this->query['check_visitor']->execute();
			$this->query['check_visitor']->execute();
			$result_array=$this->query['check_visitor']->fetchAll(PDO::FETCH_OBJ);
			foreach($result_array as $dataDb){
				if (@$dataDb->log_status=='failed') $check_fails++;
			}
		}catch (PDOException $e) {
			//echo $e->getMessage() . "<br/>";
		}
		if ($check_fails > 10) $allowed=false;
	}

	// *** Check IP Blacklist ***
	$check = $dbh->query("SELECT * FROM humo_settings
		WHERE setting_variable='ip_blacklist'");
	while($checkDb = $check->fetch(PDO::FETCH_OBJ)){
		$list=explode("|",$checkDb->setting_value);
		if ($ip_address==$list[0]) $allowed=false;
	}

	return $allowed;
}

/*--------------------[get user]----------------------------------
 * FUNCTION	: Get user from database return false if it isn't.
 * QUERY	: SELECT * FROM humo_users
 *				WHERE user_name=:user_name AND user_password=:user_password
 * RETURNS	: user data.
 *----------------------------------------------------------------
 */
function get_user($user_name,$user_password){
	$qryDb=false;
	try {
		$this->query['get_user']->bindValue(':user_name', $user_name, PDO::PARAM_STR);
		$this->query['get_user']->bindValue(':user_password', MD5($user_password), PDO::PARAM_STR);
		$this->query['get_user']->execute();
		$qryDb=$this->query['get_user']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get tree]--------------------------------
 * FUNCTION	: Get family tree data from database.
 * QUERY	: SELECT * FROM humo_trees
 *				WHERE tree_prefix=:tree_prefix
 * RETURNS	: family tree data.
 *----------------------------------------------------------------
 */
function get_tree($tree_prefix){
	$qryDb=false;
	try {
		$this->query['get_tree']->bindValue(':tree_prefix', $tree_prefix, PDO::PARAM_STR);
		$this->query['get_tree']->execute();
		$qryDb=$this->query['get_tree']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
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
function get_trees(){
	$result_array = array();
	try {
		$this->query['get_trees']->execute();
		$result_array=$this->query['get_trees']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[count persons]-----------------------------
 * FUNCTION	: Count all persons in family tree.
 * RETURNS	: number of persons.
 *----------------------------------------------------------------
 */
/*
function count_persons($tree_prefix,$pers_gedcomnumber){
	global $dbh;
	$nr_persons=0;
	try {
		//$sql = "SELECT COUNT(*) FROM humo_person";
		//$qry = $dbh->prepare( $sql );

		//$qry->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
		//$qry->execute();
		//$qryDb=$qry->fetch(PDO::FETCH_OBJ);
		$total = $dbh->query( SQL_COUNT_PERSONS );
		$total = $total->fetch();
		$nr_persons=$total[0]; 
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $nr_persons;
}
*/

/*--------------------[check person]------------------------------
 * FUNCTION	: Check for valid person in database.
 * QUERY 1	: SELECT pers_id FROM humo_persons
 *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
 * RETURNS	: Check for valid person.
 *----------------------------------------------------------------
 */
function check_person($pers_gedcomnumber){
	if ($pers_gedcomnumber!=''){
		try {
			$this->query['check_person']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['check_person']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$this->query['check_person']->execute();
			$qryDb=$this->query['check_person']->fetch(PDO::FETCH_OBJ);
		}catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		if (!isset($qryDb->pers_id)){
			echo '<b>'.__('Something went wrong, there is no valid person id.').'</b>';
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
function get_person($pers_gedcomnumber,$item=''){
	$qryDb=false;
	try {
		if ($item=='famc-fams'){
			$this->query['get_person_fams']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_person_fams']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$this->query['get_person_fams']->execute();
			$qryDb=$this->query['get_person_fams']->fetch(PDO::FETCH_OBJ);
		}
		else{
			$this->query['get_person']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_person']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
			$this->query['get_person']->execute();
			$qryDb=$this->query['get_person']->fetch(PDO::FETCH_OBJ);
		}
	}catch (PDOException $e) {
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
function get_person_with_id($pers_id){
	$qryDb=false;
	try {
		$this->query['get_person_with_id']->bindValue(':pers_id', $pers_id, PDO::PARAM_INT);
		$this->query['get_person_with_id']->execute();
		$qryDb=$this->query['get_person_with_id']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[check family]------------------------------
 * FUNCTION	: Check for valid family in database.
 * QUERY 1	: SELECT fam_id FROM humo_families
 *				WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber
 * RETURNS	: Check for valid family.
 *----------------------------------------------------------------
 */
function check_family($fam_gedcomnumber){
	if ($fam_gedcomnumber!=''){
		try {
			$this->query['check_family']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['check_family']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$this->query['check_family']->execute();
			$qryDb=$this->query['check_family']->fetch(PDO::FETCH_OBJ);
		}catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}
		if (!isset($qryDb->fam_id)){
			echo '<b>'.__('Something went wrong, there is no valid family id.').'</b>';
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
function get_family($fam_gedcomnumber,$item=''){
	$qryDb=false;
	try {
		if ($item=='man-woman'){
			$this->query['get_family_man_woman']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_family_man_woman']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$this->query['get_family_man_woman']->execute();
			$qryDb=$this->query['get_family_man_woman']->fetch(PDO::FETCH_OBJ);
		}
		else{
			$this->query['get_family']->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_family']->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
			$this->query['get_family']->execute();
			$qryDb=$this->query['get_family']->fetch(PDO::FETCH_OBJ);
		}

	}catch (PDOException $e) {
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
function get_event($event_id){
	$qryDb=false;
	try {
		// *** Don't need tree_id, it's a direct event_id ***
		$this->query['get_event']->bindValue(':event_id', $event_id, PDO::PARAM_INT);
		$this->query['get_event']->execute();
		$qryDb=$this->query['get_event']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_events_kind($event_event,$event_kind){
	$result_array = array();
	try {
		$this->query['get_events_kind']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_events_kind']->bindValue(':event_event', $event_event, PDO::PARAM_INT);
		$this->query['get_events_kind']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$this->query['get_events_kind']->execute();
		$result_array=$this->query['get_events_kind']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_events_connect($event_connect_kind,$event_connect_id,$event_kind){
	$result_array = array();
	try {
		$this->query['get_events_connect']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_events_connect']->bindValue(':event_connect_kind', $event_connect_kind, PDO::PARAM_STR);
		$this->query['get_events_connect']->bindValue(':event_connect_id', $event_connect_id, PDO::PARAM_STR);
		$this->query['get_events_connect']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$this->query['get_events_connect']->execute();
		$result_array=$this->query['get_events_connect']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_source($source_gedcomnr){
	global $user;
	$qryDb=false;
	try {
		// *** REMARK: it's easier to check for restricted sources in the script that uses this function, so disabled this code ***
		//if ($user['group_show_restricted_source']=='n'){
			$this->query['get_source']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_source']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
			$this->query['get_source']->execute();
			$qryDb=$this->query['get_source']->fetch(PDO::FETCH_OBJ);
		//}
		//else{
		//	$this->query['get_source_restricted']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
		//	$this->query['get_source_restricted']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
		//	$this->query['get_source_restricted']->execute();
		//	$qryDb=$this->query['get_source_restricted']->fetch(PDO::FETCH_OBJ);
		//}
	}catch (PDOException $e) {
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
function get_address($address_gedcomnr){
	$qryDb=false;
	try {
		$this->query['get_address']->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_address']->bindValue(':address_gedcomnr', $address_gedcomnr, PDO::PARAM_STR);
		$this->query['get_address']->execute();
		$qryDb=$this->query['get_address']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get addresses (places) ]-------
 * FUNCTION	: Get all places by a person, family etc. from database.
 * QUERY	: SELECT * FROM humo_addresses
 *				WHERE address_tree_id=:address_tree_id
 *				AND address_connect_kind=:address_connect_kind
 *				AND address_connect_id=:address_connect_id ORDER BY address_order
 * RETURNS	: all places by a person, family etc.
 *----------------------------------------------------------------
 */
function get_addresses($address_connect_id,$address_connect_kind){
	$result_array = array();
	try {
		$this->query['get_addresses']->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_addresses']->bindValue(':address_connect_kind', $address_connect_kind, PDO::PARAM_STR);
		$this->query['get_addresses']->bindValue(':address_connect_id', $address_connect_id, PDO::PARAM_STR);
		$this->query['get_addresses']->execute();
		$result_array=$this->query['get_addresses']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_connections($connect_sub_kind, $connect_item_id){
	$result_array = array();
	try {
		$this->query['get_connections']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_connections']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$this->query['get_connections']->bindValue(':connect_item_id', $connect_item_id, PDO::PARAM_STR);
		$this->query['get_connections']->execute();
		$result_array=$this->query['get_connections']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id){
	$result_array = array();
	try {
		$this->query['get_connections_connect_id']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_connections_connect_id']->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
		$this->query['get_connections_connect_id']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$this->query['get_connections_connect_id']->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
		$this->query['get_connections_connect_id']->execute();
		$result_array=$this->query['get_connections_connect_id']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
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
function get_repository($repo_gedcomnr){
	$qryDb=false;
	try {
		$this->query['get_repositories']->bindValue(':repo_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_repositories']->bindValue(':repo_gedcomnr', $repo_gedcomnr, PDO::PARAM_STR);
		$this->query['get_repositories']->execute();
		$qryDb=$this->query['get_repositories']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

} // *** End of db_functions class ***
?>