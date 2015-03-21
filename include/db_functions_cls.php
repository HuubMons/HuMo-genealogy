<?php 
/*--------------------[database functions]----------------------------
 *
 * AUTHOR		: Huub Mons. jan. 2015.
 * THANKS TO	: Michael.j.Falconer
 *
 * FUNCTIONS:
 *		get_tree				Get data from selected family tree.
 *		get_trees				Get data from all family trees.
 *		get_person				Get a single person from database.
 *		get_family				Get a single family from database.
 *		get_event				Get a single event from database.
 *		get_events_kind			Get multiple events of one event_kind from database. Example:
 *		get_events_person		Get multiple events of a person selecting one event_kind from database.
 *		get_events_family		Get multiple events of a family selecting one event_kind from database.
 *		get_source				Get a single source from database.
 *		get_address				Get a single address from database.
 *		get_addressses_person	Get a all adresses (places) by a person.
 *		get_connections			Get multiple connections (used for sources and addresses).
 *		get_connections_person	Get multiple connections of a person.
 *		get_repository			Get a single repository from database.
 *
 * SET family tree variabele:
 *	$db_functions->set_tree_prefix($tree_prefix_quoted);
 *
 * EXAMPLE get single item from database:
 *		$person_manDb = $db_functions->get_person($familyDb->fam_man);
 *		if ($person_manDb==false){ }
 *
 * EXAMPLE get multiple items from database:
 *		$colour = $db_functions->get_events_person($personDb->pers_gedcomnumber,'person_colour_mark');
 *		foreach($colour as $colourDb){
 *			echo $colourDb->event_event;
 *		}
 *		$num_rows=count($colour); // *** number of rows ***
 *		unset($colour); // *** If finished, remove data from memory ***
 *
 * Some remarks:
 * event_person_id = reference to person gedcomnumber.
 *
 *----------------------------------------------------------------
 */

class db_functions{

private $query = array();
var $tree_id='';
var $tree_prefix='';

/* Because of "$tree_prefix_quoted" it's not possible to use all queries as a real prepared query...
 * Some scripts need to search in multiple family trees...
 * If database normalisation is done, it's possible to use fully prepared queries!
 */
function __construct($tree_prefix='') {
	global $dbh;

	// *** Prepared statements ***
	if ($dbh){
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
		$sql = "SELECT * FROM humo_sources
			WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr
			AND source_status!='restricted'";
		$this->query['get_source_restricted'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_persons
			WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
		$this->query['get_person'] = $dbh->prepare( $sql );

		$sql = "SELECT fam_man, fam_woman FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
		$this->query['get_family_man_woman'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_families
			WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
		$this->query['get_family'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
		$this->query['get_event'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_events
		WHERE event_tree_id=:event_tree_id AND event_person_id=:event_person_id AND event_kind=:event_kind ORDER BY event_order";
		$this->query['get_events'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_events
		WHERE event_tree_id=:event_tree_id AND event_family_id=:event_family_id AND event_kind=:event_kind ORDER BY event_order";
		$this->query['get_events_family'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
		$this->query['get_address'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:address_tree_id AND address_person_id=:address_person_id ORDER BY address_order";
		$this->query['get_addresses_person'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id";
		$this->query['get_connections'] = $dbh->prepare( $sql );

		$sql = "SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id AND connect_kind='person' AND connect_sub_kind=:connect_sub_kind AND connect_connect_id=:connect_connect_id ORDER BY connect_order";
		$this->query['get_connections_person'] = $dbh->prepare( $sql );

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
 *				 WHERE tree_prefix!='EMPTY' ORDER BY tree_order
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
		//$sql = "SELECT COUNT(*) FROM ".$tree_prefix."person";
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

/*--------------------[get person]--------------------------------
 * FUNCTION	: Get a single person from database.
 * QUERY	: SELECT * FROM humo_persons
 *				WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber
 * RETURNS	: a single person.
 *----------------------------------------------------------------
 */
function get_person($pers_gedcomnumber){
	$qryDb=false;
	try {
		$this->query['get_person']->bindValue(':pers_tree_id', $this->tree_id, PDO::PARAM_INT);
		$this->query['get_person']->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
		$this->query['get_person']->execute();
		$qryDb=$this->query['get_person']->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
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
 * RETURNS	: multiple selected events.
 *----------------------------------------------------------------
 */
 /*
function get_events_kind($event_id,$event_kind){
	global $dbh;
	$result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_EVENTS_KIND );

		$qry->bindValue(':event_id', $event_id, PDO::PARAM_INT);
		$qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$qry->execute();
		$result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}
*/

/*--------------------[get events from person ]-------------------
 * FUNCTION	: Get all selected events by a person from database.
 * QUERY	: SELECT * FROM humo_events
 *				WHERE event_tree_id=:event_tree_id
 *				AND event_person_id=:event_person_id AND event_kind=:event_kind ORDER BY event_order
 * RETURNS	: all selected events by a person.
 *----------------------------------------------------------------
 */
function get_events_person($event_person_id,$event_kind){
	$result_array = array();
	try {
		$this->query['get_events']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_events']->bindValue(':event_person_id', $event_person_id, PDO::PARAM_STR);
		$this->query['get_events']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$this->query['get_events']->execute();
		$result_array=$this->query['get_events']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get events from family ]-------------------
 * FUNCTION	: Get all selected events by a family from database.
 * QUERY	: SELECT * FROM humo_events
 *				WHERE event_tree_id=:event_tree_id
 *				AND event_family_id=:event_family_id AND event_kind=:event_kind ORDER BY event_order
 * RETURNS	: all selected events by a family.
 *----------------------------------------------------------------
 */
function get_events_family($event_family_id,$event_kind){
	$result_array = array();
	try {
		$this->query['get_events_family']->bindValue(':event_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_events_family']->bindValue(':event_family_id', $event_family_id, PDO::PARAM_STR);
		$this->query['get_events_family']->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$this->query['get_events_family']->execute();
		$result_array=$this->query['get_events_family']->fetchAll(PDO::FETCH_OBJ);
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
		if ($user['group_show_restricted_source']=='n'){
			$this->query['get_source']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_source']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
			$this->query['get_source']->execute();
			$qryDb=$this->query['get_source']->fetch(PDO::FETCH_OBJ);
		}
		else{
			$this->query['get_source_restricted']->bindValue(':source_tree_id', $this->tree_id, PDO::PARAM_INT);
			$this->query['get_source_restricted']->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
			$this->query['get_source_restricted']->execute();
			$qryDb=$this->query['get_source_restricted']->fetch(PDO::FETCH_OBJ);
		}
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

/*--------------------[get addresses (places) from person ]-------
 * FUNCTION	: Get all places by a person from database.
 * QUERY	: SELECT * FROM humo_addresses
 *				WHERE address_tree_id=:address_tree_id
 *				AND address_person_id=:address_person_id ORDER BY address_order
 * RETURNS	: all places by a person.
 *----------------------------------------------------------------
 */
function get_addresses_person($address_person_id){
	$result_array = array();
	try {
		$this->query['get_addresses_person']->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_addresses_person']->bindValue(':address_person_id', $address_person_id, PDO::PARAM_STR);
		$this->query['get_addresses_person']->execute();
		$result_array=$this->query['get_addresses_person']->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get connections]---------------------------
 * FUNCTION	: Get a single connection (source or address) from database.
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

/*--------------------[get connections_persons]-------------------
 * FUNCTION	: Get a multiple connections from database.
 * QUERY	: SELECT * FROM humo_connections WHERE connect_tree_id=:connect_tree_id
 *				AND connect_kind='person' AND connect_sub_kind=:connect_sub_kind
 *				AND connect_connect_id=:connect_connect_id ORDER BY connect_order
 * RETURNS	: multiple connections.
 *----------------------------------------------------------------
 */
function get_connections_person($connect_sub_kind, $connect_connect_id){
	$result_array = array();
	try {
		$this->query['get_connections_person']->bindValue(':connect_tree_id', $this->tree_id, PDO::PARAM_STR);
		$this->query['get_connections_person']->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$this->query['get_connections_person']->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
		$this->query['get_connections_person']->execute();
		$result_array=$this->query['get_connections_person']->fetchAll(PDO::FETCH_OBJ);
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