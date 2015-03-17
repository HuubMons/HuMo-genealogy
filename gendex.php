<?php
header ('Content-type: text/plain; charset=iso-8859-1');

// **************************************************
// *** Privacy person                             ***
// **************************************************

define("CMS_ROOTPATH", '');

include_once(CMS_ROOTPATH."include/db_login.php"); //Inloggen database.
include_once(CMS_ROOTPATH."include/safe.php"); //Variabelen

// *** Needed for privacy filter ***
include_once(CMS_ROOTPATH."include/settings_global.php"); //Variables
include_once(CMS_ROOTPATH."include/settings_user.php"); // USER variables
include_once(CMS_ROOTPATH."include/person_cls.php");

include_once(CMS_ROOTPATH."include/db_functions_cls.php");
$db_functions = New db_functions;

// *** Database ***
$datasql=$db_functions->get_trees();
//$num_rows=count($datasql);
foreach($datasql as $dataDb){
	// *** Check is family tree is shown or hidden for user group ***
	$hide_tree_array=explode(";",$user['group_hide_trees']);
	$hide_tree=false;
	for ($x=0; $x<=count($hide_tree_array)-1; $x++){
		if ($hide_tree_array[$x]==$dataDb->tree_id){ $hide_tree=true; }
	}
	if ($hide_tree==false){
		//$person_qry = $dbh->query("SELECT * FROM ".safe_text($dataDb->tree_prefix)."person ORDER BY pers_lastname");
		$person_qry = $dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$dataDb->tree_id."' ORDER BY pers_lastname");
		//GENDEX:
		//person-URL|FAMILYNAME|Firstname /FAMILYNAME/|
		//Birthdate|Birthplace|Deathdate|Deathplace|
		while (@$personDb=$person_qry->fetch(PDO::FETCH_OBJ)) {
			// *** Use class for privacy filter ***
			$person_cls = New person_cls;
			$person_cls->construct($personDb);
			$privacy=$person_cls->privacy;

			// *** Completely filter person ***
			if ($user["group_pers_hide_totally_act"]=='j'
				AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
				// *** Don't show person ***
			}
			else{

				$person_url=$personDb->pers_indexnr;
				if ($person_url==''){
					if($personDb->pers_famc){
						// *** Person without own family ***
						$person_url=$personDb->pers_famc;
					}
					else{
						// *** Person without parents or own family ***	
						$person_url='&main_person='.$personDb->pers_gedcomnumber;
					}
				}

				$text=$person_url.'&database='.$dataDb->tree_prefix.'|';

				//$pers_lastname=strtoupper(str_replace("_", " ", $personDb->pers_prefix));
				//$pers_lastname.=strtoupper($personDb->pers_lastname);
				$pers_lastname=mb_strtoupper(str_replace("_", " ", $personDb->pers_prefix), 'iso-8859-1');
				$pers_lastname.=mb_strtoupper($personDb->pers_lastname, 'iso-8859-1');

				$text.=$pers_lastname.'|';
				$text.=$personDb->pers_firstname.' /'.$pers_lastname.'/|';

				if($privacy!=1) { // Privacy restricted person
					$birth_bapt_date="";
					if ($personDb->pers_bapt_date){ $birth_bapt_date=$personDb->pers_bapt_date; }
					if ($personDb->pers_birth_date){ $birth_bapt_date=$personDb->pers_birth_date; }
					$text.=$birth_bapt_date.'|';

					$birth_bapt_place="";
					if ($personDb->pers_bapt_place){ $birth_bapt_place=$personDb->pers_bapt_place; }
					if ($personDb->pers_birth_place){ $birth_bapt_place=$personDb->pers_birth_place; }
					$text.=$birth_bapt_place.'|';

					$died_bur_date="";
					if ($personDb->pers_death_date){ $died_bur_date=$personDb->pers_death_date; }
					if ($personDb->pers_buried_date){ $died_bur_date=$personDb->pers_buried_date; }
					$text.=$died_bur_date.'|';

					$died_bur_place="";
					if ($personDb->pers_death_place){ $died_bur_place=$personDb->pers_death_place; }
					if ($personDb->pers_buried_place){ $died_bur_place=$personDb->pers_buried_place; }
					$text.=$died_bur_place.'|';
				}
				else{
					$text.='||||';
				}
				//echo html_entity_decode($text)."\r\n";

				echo $text."\r\n";
			}

		}

	} // *** End of hidden family tree ***

} // *** End of multiple family trees ***
?>