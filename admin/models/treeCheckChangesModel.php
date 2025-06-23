<?php
class TreeCheckChangesModel extends AdminBaseModel
{
    public function get_editor(): string
    {
        $editor = '';
        if (isset($_POST['editor']) && is_numeric($_POST['editor'])) {
            $editor = $_POST['editor'];
        }
        return $editor;
    }

    public function get_limit(): int
    {
        $limit = 50;
        if (isset($_POST['limit']) && is_numeric($_POST['limit'])) {
            $limit = safe_text_db($_POST['limit']);
        }
        return $limit;
    }

    public function get_show_persons(): bool
    {
        $show_persons = false;
        if (isset($_POST['show_persons']) && $_POST['show_persons'] == '1') {
            $show_persons = true;
        }
        return $show_persons;
    }

    public function get_show_families(): bool
    {
        $show_families = false;
        if (isset($_POST['show_families']) && $_POST['show_families'] == '1') {
            $show_families = true;
        }
        return $show_families;
    }

    public function get_changes($tree_check): array
    {
        // TODO improve variabele. Now needed to show proper links in person_link.
        global $uri_path;

        $person_link = new PersonLink();
        $language_date = new LanguageDate();

        $row = 0;

        if ($tree_check['show_persons']) {
            if ($tree_check['editor']) {
                // *** Show latest changes and additions: editor is selected ***
                // *** Remark: ordering is done in the array, but also needed here to get good results if $tree_check['limit'] is a low value ***
                $person_qry = "(SELECT *, pers_changed_datetime AS changed_datetime
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NOT NULL AND pers_changed_user_id='" . $tree_check['editor'] . "')
                    UNION (SELECT *, pers_new_datetime AS changed_datetime
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NULL AND pers_new_user_id='" . $tree_check['editor'] . "')
                    ORDER BY changed_datetime DESC LIMIT 0," . $tree_check['limit'];
            } else {
                // *** Show latest changes and additions ***
                // *** Remark: ordering is done in the array, but also needed here to get good results if $tree_check['limit'] is a low value ***
                $person_qry = "(SELECT *, pers_changed_datetime AS changed_datetime
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NOT NULL)
                    UNION (SELECT *, pers_new_datetime AS changed_datetime
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NULL)
                    ORDER BY changed_datetime DESC LIMIT 0," . $tree_check['limit'];
                //FROM humo_persons WHERE pers_tree_id='".$this->tree_id."')
            }

            $person_result = $this->dbh->query($person_qry);
            while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                $tree_check['changes'][$row][0] = __('Person');

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
                $url = $person_link->get_person_link($person);

                $text = '<a href="' . $url . '">' . $person->pers_firstname . ' ' . $person->pers_prefix . $person->pers_lastname . '</a>';
                $tree_check['changes'][$row][1] = $text;

                $text = '';
                if ($person->pers_changed_datetime) {
                    $user_name = $this->db_functions->get_user_name($person->pers_changed_user_id);
                    $text .= $language_date->show_datetime($person->pers_changed_datetime) . ' ' . $user_name;
                }
                $tree_check['changes'][$row][2] = $text;

                //$text = '<nobr>' . $language_date->language_date($person->pers_new_date) . ' ' . $person->pers_new_time . ' ' . $person->pers_new_user . '</nobr>';
                $text = '';
                // TODO check if this could be added in query.
                if ($person->pers_new_datetime != '1970-01-01 00:00:01') {
                    $user_name = $this->db_functions->get_user_name($person->pers_new_user_id);
                    $text .= $language_date->show_datetime($person->pers_new_datetime) . ' ' . $user_name;
                }
                $tree_check['changes'][$row][3] = $text;

                // *** Used for ordering by date - time ***
                $tree_check['changes'][$row][4] = $person->changed_datetime;
                $row++;
            }
        }

        if ($tree_check['show_families']) {
            if ($tree_check['editor']) {
                // *** Show latest changes and additions: editor is selected ***
                // *** Remark: ordering is done in the array, but also needed here to get good results if $tree_check['limit'] is a low value ***
                $person_qry = "(SELECT *, fam_changed_datetime AS changed_datetime
                    FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_changed_datetime IS NOT NULL AND fam_changed_user_id='" . $tree_check['editor'] . "')
                    UNION (SELECT *, fam_new_datetime AS changed_datetime
                    FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_changed_datetime IS NULL AND fam_new_user_id='" . $tree_check['editor'] . "')
                    ORDER BY changed_datetime DESC LIMIT 0," . $tree_check['limit'];
            } else {
                // *** Show latest changes and additions ***
                // *** Remark: ordering is done in the array, but also needed here to get good results if $tree_check['limit'] is a low value ***
                $person_qry = "(SELECT *, fam_changed_datetime AS changed_datetime
                    FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_changed_datetime IS NOT NULL)
                    UNION (SELECT *, fam_new_datetime AS changed_datetime
                    FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_changed_datetime IS NULL)
                    ORDER BY changed_datetime DESC LIMIT 0," . $tree_check['limit'];
            }

            $person_result = $this->dbh->query($person_qry);
            while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                // check if standard functions can be used.
                //$personDb=$this->db_functions->get_person($parent1);
                $person2_qry = "(SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $person->fam_man . "')";
                $person2_result = $this->dbh->query($person2_qry);
                $person2 = $person2_result->fetch(PDO::FETCH_OBJ);

                if (isset($person2->pers_tree_id)) {
                    $tree_check['changes'][$row][0] = __('Family');

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
                    $url = $person_link->get_person_link($person2);

                    $text = '<a href="' . $url . '">' . $person2->pers_firstname . ' ' . $person2->pers_prefix . $person2->pers_lastname . '</a>';
                    $tree_check['changes'][$row][1] = $text;

                    $text = '';
                    if ($person->fam_changed_datetime) {
                        $user_name = $this->db_functions->get_user_name($person->fam_changed_user_id);
                        $text .= show_datetime($person->fam_changed_datetime) . ' ' . $user_name;
                    }
                    $tree_check['changes'][$row][2] = $text;

                    $text = '';
                    if ($person->fam_new_datetime != '1970-01-01 00:00:01') {
                        $user_name = $this->db_functions->get_user_name($person->fam_new_user_id);
                        $text .= show_datetime($person->fam_new_datetime) . ' ' . $user_name;
                    }
                    $tree_check['changes'][$row][3] = $text;

                    // *** Used for ordering by date - time ***
                    $tree_check['changes'][$row][4] = $person->changed_datetime;
                    $row++;
                }
            }
        }

        // *** Order array ***
        function cmp($a, $b)
        {
            //return strcmp($a[4], $b[4]);	// ascending
            return strcmp($b[4], $a[4]);    // descending
        }
        usort($tree_check['changes'], "cmp");

        return $tree_check['changes'];
    }

    public function get_editors($tree_check)
    {
        // *** List of editors, depending of selected items (persons and/ or families) ***
        $select_editor_qry = "(SELECT pers_new_user_id AS user FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "')
            UNION (SELECT pers_changed_user_id AS user FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "')";
        if ($tree_check['show_families']) {
            $select_editor_qry .= " UNION (SELECT fam_new_user_id AS user FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "')";
            $select_editor_qry .= " UNION (SELECT fam_changed_user_id AS user FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "')";
        }
        $select_editor_qry .= " ORDER BY user DESC LIMIT 0,50";
        return $this->dbh->query($select_editor_qry);
    }
}
