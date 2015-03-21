<?php 
/*--------------------[database functions]----------------------------
 *
 * AUTHOR		: Huub Mons
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

function __construct() {
	global $tree_prefix_quoted;

	define ("SQL_GET_TREE", "SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix_quoted."'");

	define ("SQL_GET_TREES", "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");

	// ** Define database queries ***
	//define ("SQL_COUNT_PERSONS", "SELECT COUNT(*) FROM ".$tree_prefix."person");

	define ("SQL_GET_PERSON", "SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber=:pers_gedcomnumber");
	//const SQL_GET_PERSON = "SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber=:pers_gedcomnumber";

	define ("SQL_GET_FAMILY", "SELECT * FROM ".$tree_prefix_quoted."family WHERE fam_gedcomnumber=:fam_gedcomnumber");

	define ("SQL_GET_EVENT", "SELECT * FROM ".$tree_prefix_quoted."events WHERE event_id=:event_id");
	//define ("SQL_GET_EVENTS_KIND", "SELECT * FROM ".$tree_prefix_quoted."events
	//	WHERE event_id=:event_id AND event_kind=:event_kind ORDER BY event_order");
	define ("SQL_GET_EVENTS_PERSON", "SELECT * FROM ".$tree_prefix_quoted."events
		WHERE event_person_id=:event_person_id AND event_kind=:event_kind ORDER BY event_order");
	define ("SQL_GET_EVENTS_FAMILY", "SELECT * FROM ".$tree_prefix_quoted."events
		WHERE event_family_id=:event_family_id AND event_kind=:event_kind ORDER BY event_order");

	define ("SQL_GET_SOURCE", "SELECT * FROM ".$tree_prefix_quoted."sources WHERE source_gedcomnr=:source_gedcomnr");
	define ("SQL_SOURCE_RESTRICTED", " AND source_status!='restricted'");

	//define ("SQL_GET_SOURCES", "SELECT * FROM ".$tree_prefix_quoted."sources");
	//define ("SQL_SOURCES_RESTRICTED", " WHERE source_status!='restricted' OR source_status IS NULL");
	//define ("SQL_SOURCES_ORDER", " ORDER BY source_title");
	//define ("SQL_ASC", " ASC");
	//define ("SQL_DESC", " DESC");

	define ("SQL_GET_ADDRESS", "SELECT * FROM ".$tree_prefix_quoted."addresses WHERE address_gedcomnr=:address_gedcomnr");
	define ("SQL_GET_ADDRESSES_PERSON", "SELECT * FROM ".$tree_prefix_quoted."addresses WHERE address_person_id=:address_person_id ORDER BY address_order");

	define ("SQL_GET_CONNECTIONS", "SELECT * FROM ".$tree_prefix_quoted."connections WHERE connect_sub_kind=:connect_sub_kind AND connect_item_id=:connect_item_id");
	define ("SQL_GET_CONNECTIONS_PERSON", "SELECT * FROM ".$tree_prefix_quoted."connections WHERE connect_kind='person' AND connect_sub_kind=:connect_sub_kind AND connect_connect_id=:connect_connect_id ORDER BY connect_order");

	define ("SQL_GET_REPOSITORY", "SELECT * FROM ".$tree_prefix_quoted."repositories WHERE repo_gedcomnr=:repo_gedcomnr");
}


/*--------------------[get tree]--------------------------------
 * Get family tree data from database.
 * RETURNS: family tree data.
 *----------------------------------------------------------------
 */
function get_tree(){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_TREE );
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get data from all trees ]------------------
 * Get all data from family trees.
 * RETURNS: all data from family trees.
 *----------------------------------------------------------------
 */
function get_trees(){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_TREES );
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[count persons]-----------------------------
 * Count all persons in family tree.
 * RETURNS: number of persons.
 *----------------------------------------------------------------
 */
/*
function count_persons($pers_gedcomnumber){
	global $dbh; $nr_persons=0;
	try {
		//$qry = $dbh->prepare( SQL_COUNT_PERSONS );
		//$qry->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
		//$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
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
 * Get a single person from database.
 * RETURNS: a single person.
 *----------------------------------------------------------------
 */
function get_person($pers_gedcomnumber){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_PERSON );
		$qry->bindValue(':pers_gedcomnumber', $pers_gedcomnumber, PDO::PARAM_STR);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get family]--------------------------------
 * Get a single family from database.
 * RETURNS: a single family.
 *----------------------------------------------------------------
 */
function get_family($fam_gedcomnumber){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_FAMILY );
		$qry->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get event]---------------------------------
 * Get a single event from database.
 * RETURNS: a single event.
 *----------------------------------------------------------------
 */
function get_event($event_id){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_EVENT );
		$qry->bindValue(':event_id', $event_id, PDO::PARAM_INT);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get events]--------------------------------
 * Get all selected events from database.
 * RETURNS: multiple selected events.
 *----------------------------------------------------------------
 */
 /*
function get_events_kind($event_id,$event_kind){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_EVENTS_KIND );
		$qry->bindValue(':event_id', $event_id, PDO::PARAM_INT);
		$qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}
*/

/*--------------------[get events from person ]-------------------
 * Get all selected events by a person from database.
 * RETURNS: all selected events by a person.
 *----------------------------------------------------------------
 */
function get_events_person($event_person_id,$event_kind){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_EVENTS_PERSON );
		$qry->bindValue(':event_person_id', $event_person_id, PDO::PARAM_STR);
		$qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get events from family ]-------------------
 * Get all selected events by a family from database.
 * RETURNS: all selected events by a family.
 *----------------------------------------------------------------
 */
function get_events_family($event_family_id,$event_kind){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_EVENTS_FAMILY );
		$qry->bindValue(':event_family_id', $event_family_id, PDO::PARAM_STR);
		$qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get source]--------------------------------
 * Get a single source from database.
 * RETURNS: a single source.
 *----------------------------------------------------------------
 */
function get_source($source_gedcomnr){
	global $dbh, $user; $qryDb=false;

	$sql=SQL_GET_SOURCE;
	// *** Hide restricted source ***
	if ($user['group_show_restricted_source']=='n'){ $sql.=SQL_SOURCE_RESTRICTED; }

	try {
		$qry = $dbh->prepare( $sql );
		$qry->bindValue(':source_gedcomnr', $source_gedcomnr, PDO::PARAM_STR);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get address]---------------------------------
 * Get a single address from database.
 * RETURNS: a single address.
 *----------------------------------------------------------------
 */
function get_address($address_gedcomnr){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_ADDRESS );
		$qry->bindValue(':address_gedcomnr', $address_gedcomnr, PDO::PARAM_STR);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

/*--------------------[get addresses (places) from person ]-------
 * Get all places by a person from database.
 * RETURNS: all places by a person.
 *----------------------------------------------------------------
 */
function get_addresses_person($address_person_id){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_ADDRESSES_PERSON );
		$qry->bindValue(':address_person_id', $address_person_id, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get connections]---------------------------
 * Get a single connection (source or address) from database.
 * RETURNS: multiple connections.
 *----------------------------------------------------------------
 */
function get_connections($connect_sub_kind, $connect_item_id){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_CONNECTIONS );
		$qry->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$qry->bindValue(':connect_item_id', $connect_item_id, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get connections_persons]-------------------
 * Get a multiple connections from database.
 * RETURNS: multiple connections.
 *----------------------------------------------------------------
 */
function get_connections_person($connect_sub_kind, $connect_connect_id){
	global $dbh; $result_array = array();
	try {
		$qry = $dbh->prepare( SQL_GET_CONNECTIONS_PERSON );
		$qry->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
		$qry->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
		$qry->execute(); $result_array=$qry->fetchAll(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $result_array;
}

/*--------------------[get repository]----------------------------
 * Get a single repository from database.
 * RETURNS: a single repository.
 *----------------------------------------------------------------
 */
function get_repository($repo_gedcomnr){
	global $dbh; $qryDb=false;
	try {
		$qry = $dbh->prepare( SQL_GET_REPOSITORY );
		$qry->bindValue(':repo_gedcomnr', $repo_gedcomnr, PDO::PARAM_STR);
		$qry->execute(); $qryDb=$qry->fetch(PDO::FETCH_OBJ);
	}catch (PDOException $e) {
		echo $e->getMessage() . "<br/>";
	}
	return $qryDb;
}

} // *** End of db_functions class ***
?>