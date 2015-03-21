<?php
function popup($text,$show_data=true){
	global $dbh, $tree_id, $lang, $number, $c, $tree_prefix_quoted;
	$person=$text;
	$text='';

	include_once("../include/person_cls.php");
	include_once("../include/marriage_cls.php"); 

	//$person_man=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='".safe_text($person)."'");
	$person_man=$dbh->query("SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($person)."'");
	@$person_manDb=$person_man->fetch(PDO::FETCH_OBJ);

	//*** Use class to show person ***
	$man_cls = New person_cls;
	$man_cls->construct($person_manDb);  
	$man_privacy=$man_cls->privacy;
	$name = $man_cls->person_name($person_manDb);   

	if ($person_manDb->pers_sexe=="M"){
		$text.= '<li><img src="../images/man.gif" class="ui-li-icon">';
	}
	elseif ($person_manDb->pers_sexe=="F"){
		$text.= '<li><img src="../images/woman.gif" class="ui-li-icon">';
	}
	else{
		$text.= '<li><img src="../images/unknown.gif" class="ui-li-icon">';
	}

	$number_text=''; if ($number==1){ $number_text=($c+1).' '; }
	$text.= '<p>'.$number_text.'<a href="family.php?id='.$person_manDb->pers_gedcomnumber.'">'.$name["index_name"].'</a><br>';

	if ($show_data==true){
		if($man_privacy) {
			$text.= __(' PRIVACY FILTER'); 
		}
		else { // no privacy set
			if ($person_manDb->pers_birth_date OR $person_manDb->pers_birth_place){
				$text.=ucfirst(__('*')).' '.date_place($person_manDb->pers_birth_date,$person_manDb->pers_birth_place).'<br>';
			}
			elseif ($person_manDb->pers_bapt_date OR $person_manDb->pers_bapt_place){
				$text.=ucfirst(__('~')).' '.date_place($person_manDb->pers_bapt_date,$person_manDb->pers_bapt_place).'<br>';
			}

			if ($person_manDb->pers_death_date OR $person_manDb->pers_death_place){
				$text.=ucfirst(__('&#134;')).' '.date_place($person_manDb->pers_death_date,$person_manDb->pers_death_place).'<br>';
			}
			elseif ($person_manDb->pers_buried_date OR $person_manDb->pers_buried_place){
				if ($person_manDb->pers_cremation) $var.=ucfirst(__('crem.')).' ';
					else $var.=ucfirst(__('buried')).' ';
				$var.=date_place($person_manDb->pers_buried_date,$person_manDb->pers_buried_place).'<br>';
			}
			else {
				$text.= '</p></li>';
			}
		}
	}

	return $text; 
}
?>