<?php
class LatestChangesModel extends BaseModel
{
    public function listChanges(): array
    {
        // *** EXAMPLE of a UNION querie ***
        //$qry = "(SELECT * FROM humo1_person ".$query.') ';
        //$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
        //$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
        //$qry.= " ORDER BY pers_lastname, pers_firstname";

        // *** Only show persons if they have a changed or new datetime ***
        $person_qry = "(SELECT *, pers_changed_datetime AS changed_date FROM humo_persons
            WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NOT NULL)
            UNION (SELECT *, pers_new_datetime AS changed_date FROM humo_persons
            WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NULL AND pers_new_datetime!='1970-01-01 00:00:01')";
        $person_qry .= " ORDER BY changed_date DESC LIMIT 0,100";

        $search_name = '';
        if (isset($_POST["search_name"]) && $_POST["search_name"]) {
            $search_name = $_POST["search_name"];

            // *** Renewed querie because of ONLY_FULL_GROUP_BY in MySQL 5.7 ***
            $person_qry = "
            SELECT humo_persons2.*, humo_persons1.pers_id
            FROM humo_persons as humo_persons2
            RIGHT JOIN 
            (
                (
                SELECT pers_id
                FROM humo_persons
                LEFT JOIN humo_events
                    ON pers_gedcomnumber=event_connect_id AND pers_tree_id=event_tree_id AND event_kind='name'
                WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($search_name) . "%'
                    OR event_event LIKE '%" . safe_text_db($search_name) . "%')
                    AND ((pers_changed_datetime IS NOT NULL) OR (pers_new_datetime IS NOT NULL))
                    AND pers_tree_id='" . $this->tree_id . "'
                GROUP BY pers_id
                )
            ) as humo_persons1
            ON humo_persons1.pers_id = humo_persons2.pers_id";

            // *** Order by pers_changed_date or pers_new_date, also order by pers_changed_time or pers_new_time ***
            $person_qry .= " ORDER BY
            IF (humo_persons2.pers_changed_datetime IS NOT NULL,
                humo_persons2.pers_changed_datetime, humo_persons2.pers_new_datetime) DESC LIMIT 0,100";
        }

        $person_result = $this->dbh->query($person_qry);
        $i = 0;
        $changes = [];
        while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
            if ($person->pers_sexe == "M") {
                $pers_sexe = '<img src="images/man.gif" alt="man">';
            } elseif ($person->pers_sexe == "F") {
                $pers_sexe = '<img src="images/woman.gif" alt="woman">';
            } else {
                $pers_sexe = '<img src="images/unknown.gif" alt="unknown">';
            }

            $person_cls = new PersonCls($person);
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $url = $person_cls->person_url2($person->pers_tree_id, $person->pers_famc, $person->pers_fams, $person->pers_gedcomnumber);
            $name = $person_cls->person_name($person);

            $changes['show_person'][$i] = $person_cls->person_popup_menu($person) . $pers_sexe . '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
            $changes['changed_date'][$i] = show_datetime($person->pers_changed_datetime);
            $changes['new_date'][$i] = show_datetime($person->pers_new_datetime);

            $i++;
        }

        // *** In some cases there are no results (probably when GEDCOM is imported long time ago without editing) ***
        if (!isset($changes['show_person'])){
            $changes['show_person'][]='';
            $changes['changed_date'][]='';
            $changes['new_date'][]='';
        }

        return $changes;
    }
}
