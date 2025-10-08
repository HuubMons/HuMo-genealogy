<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\PersonLink;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonPopup;
use Genealogy\Include\LanguageDate;
use PDO;

class LatestChangesModel extends BaseModel
{
    public function listChanges(): array
    {
        $personLink = new PersonLink;
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $personPopup = new PersonPopup();
        $languageDate = new LanguageDate;

        // *** EXAMPLE of a UNION querie ***
        //$qry = "(SELECT * FROM humo1_person ".$query.') ';
        //$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
        //$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
        //$qry.= " ORDER BY pers_lastname, pers_firstname";

        if (isset($_POST["search_name"]) && $_POST["search_name"]) {
            $person_qry = "
            SELECT humo_persons2.*, humo_persons1.pers_id
            FROM humo_persons as humo_persons2
            RIGHT JOIN 
            (
                (
                SELECT pers_id
                FROM humo_persons
                LEFT JOIN humo_events
                    ON pers_id=person_id AND event_kind='name'
                    WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE :searchTerm
                    OR event_event LIKE :searchTerm)
                    AND ((pers_changed_datetime IS NOT NULL) OR (pers_new_datetime IS NOT NULL))
                    AND pers_tree_id=:tree_id
                GROUP BY pers_id
                )
            ) as humo_persons1
            ON humo_persons1.pers_id = humo_persons2.pers_id
            ORDER BY
            IF (humo_persons2.pers_changed_datetime IS NOT NULL,
            humo_persons2.pers_changed_datetime, humo_persons2.pers_new_datetime) DESC LIMIT 0,100";

            $stmt = $this->dbh->prepare($person_qry);
            $stmt->bindValue(':searchTerm', '%' . $_POST["search_name"] . '%', PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->execute();
            $person_result = $stmt;
        } else {
            // *** Only show persons if they have a changed or new datetime ***
            $person_qry = "
                (SELECT *, pers_changed_datetime AS changed_date FROM humo_persons
                 WHERE pers_tree_id = :tree_id AND pers_changed_datetime IS NOT NULL)
                UNION
                (SELECT *, pers_new_datetime AS changed_date FROM humo_persons
                 WHERE pers_tree_id = :tree_id AND pers_changed_datetime IS NULL AND pers_new_datetime != '1970-01-01 00:00:01')
                ORDER BY changed_date DESC LIMIT 0,100
            ";
            $stmt = $this->dbh->prepare($person_qry);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->execute();
            $person_result = $stmt;
        }

        $i = 0;
        $changes = [];

        while ($person2 = $person_result->fetch(PDO::FETCH_OBJ)) {
            // *** Get person from database to have all fields (not only those selected in the query) ***
            $person = $this->db_functions->get_person_with_id($person2->pers_id);
            if ($person->pers_sexe == "M") {
                $pers_sexe = '<img src="images/man.gif" alt="man">';
            } elseif ($person->pers_sexe == "F") {
                $pers_sexe = '<img src="images/woman.gif" alt="woman">';
            } else {
                $pers_sexe = '<img src="images/unknown.gif" alt="unknown">';
            }

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $url = $personLink->get_person_link($person);

            $privacy = $personPrivacy->get_privacy($person);
            $name = $personName->get_person_name($person, $privacy);

            $changes['show_person'][$i] = $personPopup->person_popup_menu($person, $privacy) . $pers_sexe . '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
            $changes['changed_date'][$i] = $languageDate->show_datetime($person->pers_changed_datetime);
            $changes['new_date'][$i] = $languageDate->show_datetime($person->pers_new_datetime);

            $i++;
        }

        // *** In some cases there are no results (probably when GEDCOM is imported long time ago without editing) ***
        if (!isset($changes['show_person'])) {
            $changes['show_person'][] = '';
            $changes['changed_date'][] = '';
            $changes['new_date'][] = '';
        }

        return $changes;
    }
}
