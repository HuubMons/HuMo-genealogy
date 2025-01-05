<?php

/**
 * July 2023: refactor editor to MVC
 */

include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");
include_once(__DIR__ . "/../../include/language_event.php");

class EditorModel
{
    private $dbh;
    private $tree_id;
    private $tree_prefix;
    private $db_functions;
    private $new_tree = false;
    private $pers_gedcomnumber;
    private $search_id;
    private $person;
    private $search_name;
    private $add_person;
    private $marriage;
    private $editor_cls;
    private $humo_option;
    private $userid;

    public function __construct($dbh, $tree_id, $tree_prefix, $db_functions, $editor_cls, $humo_option)
    {
        $this->dbh = $dbh;
        $this->tree_id = $tree_id;
        $this->tree_prefix = $tree_prefix;
        $this->db_functions = $db_functions;
        $this->editor_cls = $editor_cls;
        $this->humo_option = $humo_option;

        $this->userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $this->userid = $_SESSION['user_id_admin'];
        }
    }

    public function set_hebrew_night(): void
    {
        // for jewish settings only for humo_persons table:
        if ($this->humo_option['admin_hebnight'] == "y") {
            $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_persons');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['pers_birth_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_birth_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['pers_death_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_death_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['pers_buried_date_hebnight'])) {
                $sql = "ALTER TABLE humo_persons ADD pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_buried_date;";
                $this->dbh->query($sql);
            }

            $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_families');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['fam_marr_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_notice_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_notice_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_notice_date;";
                $this->dbh->query($sql);
            }
            if (!isset($field['fam_marr_church_date_hebnight'])) {
                $sql = "ALTER TABLE humo_families ADD fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_date;";
                $this->dbh->query($sql);
            }

            $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_events');
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
            }
            if (!isset($field['event_date_hebnight'])) {
                $sql = "ALTER TABLE humo_events ADD event_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER event_date;";
                $this->dbh->query($sql);
            }
        }
    }

    public function set_pers_gedcomnumber(): void
    {
        // *** Used for new selected family tree or search person etc. ***
        if (isset($_POST["tree_id"])) {
            $this->pers_gedcomnumber = '';
            unset($_SESSION['admin_pers_gedcomnumber']);
        }

        // *** Delete session variables for new person ***
        if (isset($_POST['person_add'])) {
            if (!isset($_POST['child_connect'])) {
                unset($_SESSION['admin_pers_gedcomnumber']);
            }
            unset($_SESSION['admin_fam_gedcomnumber']);
        }

        // *** Save person GEDCOM number ***
        $this->pers_gedcomnumber = '';

        if (isset($_SESSION['admin_pers_gedcomnumber'])) {
            $this->pers_gedcomnumber = $_SESSION['admin_pers_gedcomnumber'];
        }

        if (isset($_POST["person"]) && $_POST["person"]) {
            $this->pers_gedcomnumber = $_POST['person'];
        }

        if (isset($_GET["person"])) {
            $this->pers_gedcomnumber = $_GET['person'];

            $_SESSION['admin_search_name'] = '';
            $this->search_name = '';
        }

        $this->search_id = '';
        // *** Manually search for GEDCOM number, must be pattern like: Ixxxx ***
        if (isset($_POST["search_id"])) {
            $pattern = '/^^[a-z,A-Z][0-9]{1,}$/';
            if (preg_match($pattern, $_POST["search_id"])) {
                $this->pers_gedcomnumber = $_POST['search_id'];
                $this->search_id = $_POST['search_id'];

                $_SESSION['admin_search_name'] = '';
                $this->search_name = '';
            }
        }

        // *** New (selected) family tree: no default or selected pers_gedcomnumer, add new person ***
        if ($this->pers_gedcomnumber == '') {
            // *** Open editor screen first time after starting browser ***
            unset($_SESSION['admin_pers_gedcomnumber']);

            // *** Select first person to show from favorite list (also check if person still exists) ***
            $new_nr_qry = "SELECT * FROM humo_settings LEFT JOIN humo_persons ON setting_value=pers_gedcomnumber
                WHERE setting_variable='admin_favourite' AND setting_tree_id='" . safe_text_db($this->tree_id) . "'
                AND pers_tree_id='" . safe_text_db($this->tree_id) . "' LIMIT 0,1";
            $new_nr_result = $this->dbh->query($new_nr_qry);
            if ($new_nr_result && $new_nr_result->rowCount()) {
                $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
                $this->pers_gedcomnumber = $new_nr->setting_value;
            } else {
                $new_nr_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . safe_text_db($this->tree_id) . "' LIMIT 0,1";
                $new_nr_result = $this->dbh->query($new_nr_qry);
                $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
                if (isset($new_nr->pers_gedcomnumber)) {
                    $this->pers_gedcomnumber = $new_nr->pers_gedcomnumber;
                }
            }

            // *** New family tree ***
            if ($this->pers_gedcomnumber == '') {
                $this->add_person = true;
                //TODO check variable
                $_GET['add_person'] = '1';
                $this->new_tree = true;
            }
        }

        // *** Check GEDCOM number should be I123456 (although other letter is allowed) ***
        if ($this->pers_gedcomnumber) {
            $pattern = '/^^[a-z,A-Z][0-9]{1,}$/';
            if (preg_match($pattern, $this->pers_gedcomnumber)) {
                // *** Now check if GEDCOM number exists ***
                $this->person = $this->db_functions->get_person($this->pers_gedcomnumber);
                // *** Person don't exist (anymore)! ***
                if (!isset($this->person->pers_gedcomnumber)) {
                    $this->pers_gedcomnumber = '';
                }
            } else {
                // *** Non valid GEDCOM number, now reset GEDCOM number ***
                $this->pers_gedcomnumber = '';
            }
            $_SESSION['admin_pers_gedcomnumber'] = $this->pers_gedcomnumber;

            //TODO also save tree. If family tree is changed, don't use selected person.
            //$_SESSION['admin_person']['tree_id'] = $this->pers_gedcomnumber;
            //$_SESSION['admin_person']['gedcomnumber'] = $this->tree_id;
            // test lines, works.
            //echo $_SESSION['admin_person']['tree_id'].'!!!!';
            //unset ($_SESSION['admin_person']);
            //echo $_SESSION['admin_person']['tree_id'];
        }
    }

    public function get_pers_gedcomnumber()
    {
        return $this->pers_gedcomnumber;
    }

    public function get_search_id()
    {
        return $this->search_id;
    }

    public function set_search_name(): void
    {
        // *** Search person name ***
        $this->search_name = '';

        if (isset($_POST["search_quicksearch"])) {
            $this->search_name = safe_text_db($_POST['search_quicksearch']);
            $_SESSION['admin_search_name'] = $this->search_name;

            //$this->pers_gedcomnumber = '';
            //$_SESSION['admin_pers_gedcomnumber'] = '';
        }
        if (isset($_SESSION['admin_search_name'])) {
            $this->search_name = $_SESSION['admin_search_name'];
        }
    }

    public function get_search_name()
    {
        return $this->search_name;
    }

    public function set_add_person(): void
    {
        $this->add_person = false;
        if (isset($_GET['add_person'])) {
            $this->add_person = true;

            $_SESSION['admin_search_name'] = '';
        }
    }

    public function get_add_person()
    {
        return $this->add_person;
    }

    public function get_new_tree()
    {
        return $this->new_tree;
    }

    public function set_marriage(): void
    {
        $this->marriage = '';

        // *** Child is added, show marriage page ***
        if (isset($_POST['child_connect'])) {
            $this->marriage = $_POST['marriage_nr'];
        }

        if (isset($this->person->pers_fams) && $this->person->pers_fams) {
            if (isset($_SESSION['admin_fam_gedcomnumber'])) {
                $this->marriage = $_SESSION['admin_fam_gedcomnumber'];
            }

            // *** Get marriage number, also used for 2nd, 3rd etc. relation ***
            if (isset($_POST["marriage_nr"]) && $_POST["marriage_nr"]) {
                $this->marriage = $_POST['marriage_nr'];
            }

            if (isset($_GET["marriage_nr"])) {
                $this->marriage = $_GET['marriage_nr'];
            }

            // *** Just in case there is no marriage number found ***
            if (!$this->marriage) {
                $fams1 = explode(";", $this->person->pers_fams);
                $this->marriage = $fams1[0];
            }
        }

        // *** Check GEDCOM number should be F123456 (although other letter is allowed) ***
        if ($this->marriage) {
            $pattern = '/^^[a-z,A-Z][0-9]{1,}$/';
            if (preg_match($pattern, $this->marriage)) {
                // Maybe later check here if $marriage is in database. But normally it should be.
            } else {
                // *** Non valid GEDCOM number, now reset GEDCOM number ***
                $this->marriage = '';
            }
            $_SESSION['admin_fam_gedcomnumber'] = $this->marriage;
        }
    }

    public function get_marriage()
    {
        return $this->marriage;
    }

    public function set_favorite(): void
    {
        if (isset($_GET['pers_favorite'])) {
            if ($_GET['pers_favorite'] == "1") {
                $sql = "INSERT INTO humo_settings SET
                    setting_variable='admin_favourite',
                    setting_value='" . safe_text_db($this->pers_gedcomnumber) . "',
                    setting_tree_id='" . safe_text_db($this->tree_id) . "'";
                $this->dbh->query($sql);
            } else {
                $sql = "DELETE FROM humo_settings
                    WHERE setting_variable='admin_favourite'
                    AND setting_value='" . safe_text_db($this->pers_gedcomnumber) . "'
                    AND setting_tree_id='" . safe_text_db($this->tree_id) . "'";
                $this->dbh->query($sql);
            }
        }
    }

    public function get_favorites($dbh, $tree_id, $new_tree)
    {
        if ($new_tree == false) {
            // *** Favourites ***
            $fav_qry = "SELECT * FROM humo_settings LEFT JOIN humo_persons ON setting_value=pers_gedcomnumber
                WHERE setting_variable='admin_favourite' AND setting_tree_id='" . safe_text_db($tree_id) . "' AND pers_tree_id='" . safe_text_db($tree_id) . "'";
            $fav_result = $dbh->query($fav_qry);

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes();

            return $fav_result;
        }
        return false;
    }

    public function update_editor()
    {
        // *** Return deletion confim box in $confirm variabele ***
        $confirm = '';
        $confirm_relation = '';

        // TODO: also save userid in changed queries.
        if (isset($_POST['person_remove2'])) {
            $confirm .= '<div class="alert alert-success">';

            $personDb = $this->db_functions->get_person($this->pers_gedcomnumber);

            // *** If person is married: remove marriages from family ***
            if ($personDb->pers_fams) {
                $fams_array = explode(";", $personDb->pers_fams);
                foreach ($fams_array as $key => $value) {
                    $famDb = $this->db_functions->get_family($fams_array[$key]);

                    if ($famDb->fam_man == $this->pers_gedcomnumber) {
                        // *** Completely remove marriage if man and woman are removed *** 
                        if ($famDb->fam_woman == '' || $famDb->fam_woman == '0') {

                            // *** Remove parents by children ***
                            $fam_children = explode(";", $famDb->fam_children);
                            foreach ($fam_children as $key2 => $value) {
                                $sql = "UPDATE humo_persons SET pers_famc=''
                                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $fam_children[$key2] . "'";
                                $this->dbh->query($sql);
                            }

                            $sql = "DELETE FROM humo_families
                                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                            $this->dbh->query($sql);
                        } else {
                            $sql = "UPDATE humo_families SET fam_man='0'
                                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                            $this->dbh->query($sql);
                            $confirm .= __('Person disconnected from marriage(s).') . '<br>';
                        }
                    }

                    if ($famDb->fam_woman == $this->pers_gedcomnumber) {
                        // *** Completely remove marriage if man and woman are removed *** 
                        if ($famDb->fam_man == '' || $famDb->fam_man == '0') {

                            // *** Remove parents by children ***
                            $fam_children = explode(";", $famDb->fam_children);
                            foreach ($fam_children as $key2 => $value) {
                                $sql = "UPDATE humo_persons SET pers_famc=''
                                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $fam_children[$key2] . "'";
                                $this->dbh->query($sql);
                            }

                            $sql = "DELETE FROM humo_families
                                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                            $this->dbh->query($sql);
                        } else {
                            $sql = "UPDATE humo_families SET fam_woman='0'
                                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                            $this->dbh->query($sql);
                            $confirm .= __('Person disconnected from marriage(s).') . '<br>';
                        }
                    }
                }
            }

            // *** If person is a child: remove child number from parents family ***
            if ($personDb->pers_famc) {
                $famDb = $this->db_functions->get_family($personDb->pers_famc);

                $fam_children = explode(";", $famDb->fam_children);
                foreach ($fam_children as $key => $value) {
                    if ($fam_children[$key] != $this->pers_gedcomnumber) {
                        $fam_children2[] = $fam_children[$key];
                    }
                }
                $fam_children3 = '';
                if (isset($fam_children2[0])) {
                    $fam_children3 = implode(";", $fam_children2);
                }

                $sql = "UPDATE humo_families SET fam_children='" . $fam_children3 . "'
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $personDb->pers_famc . "'";
                $this->dbh->query($sql);

                $confirm .= __('Person disconnected from parents.') . '<br>';
            }

            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'
                AND address_connect_sub_kind='person'
                AND address_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            // *** Added in march 2023 ***
            $sql = "DELETE FROM humo_user_notes WHERE note_tree_id='" . $this->tree_id . "' AND note_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes(true);

            $confirm .= '<strong>' . __('Person is removed') . '</strong>';

            // *** Select new person ***
            //$new_nr_qry = "SELECT * FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='" . $this->tree_id . "' LIMIT 0,1";
            //$new_nr_result = $this->dbh->query($new_nr_qry);
            //if ($new_nr_result and $new_nr_result->rowCount()) {
            //    $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
            //    $this->pers_gedcomnumber = $new_nr->setting_value;
            //    $_SESSION['admin_pers_gedcomnumber'] = $this->pers_gedcomnumber;
            //} else {
            //    $new_nr_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' LIMIT 0,1";
            //    $new_nr_result = $this->dbh->query($new_nr_qry);
            //    $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
            //    if ($new_nr->pers_gedcomnumber) {
            //        $this->pers_gedcomnumber = $new_nr->pers_gedcomnumber;
            //        $_SESSION['admin_pers_gedcomnumber'] = $this->pers_gedcomnumber;
            //    }
            //}

            $this->family_tree_update();

            $confirm .= '</div>';
        }

        //if (isset($_POST['person_change'])){
        $save_person_data = false;
        if (isset($_POST['person_change'])) {
            $save_person_data = true;
        }
        // *** Also save person data if name is added ***
        if (isset($_POST['event_add_name'])) {
            $save_person_data = true;
        }
        // *** Also save person data if profession is added ***
        if (isset($_POST['event_add_profession'])) {
            $save_person_data = true;
        }
        // *** Also save person data if religion is added ***
        if (isset($_POST['event_add_religion'])) {
            $save_person_data = true;
        }
        // *** Also save person data if person event is added ***
        if (isset($_POST['person_event_add'])) {
            $save_person_data = true;
        }

        // *** Also save person data if witnesses are added ***
        if (isset($_POST['add_birth_declaration'])) {
            $save_person_data = true;
        }
        if (isset($_POST['add_baptism_witness'])) {
            $save_person_data = true;
        }
        if (isset($_POST['add_death_declaration'])) {
            $save_person_data = true;
        }
        if (isset($_POST['add_burial_witness'])) {
            $save_person_data = true;
        }

        // *** Also save person data if addresses are added ***
        if (isset($_POST['person_add_address'])) {
            $save_person_data = true;
        }

        // *** Also save person data if media is added ***
        if (isset($_POST['add_picture'])) {
            $save_person_data = true;
        }
        if (isset($_POST['person_add_media'])) {
            $save_person_data = true;
        }

        //DIT MOETEN KNOPPEN WORDEN. $_POST dus.
        //TODO sources, editor notes, wisselen van vrouw en man in relatie, toevoegen kind.
        // Bij bronnen een array meesturen met alle namen van de bron knoppen?
        /* Voorbeeld:
        <input type="text" name="add_source_button[]" value="'.$page_source.'"/>
        <input type="text" name="add_source_button[]" value="'.$page_source.'"/>
        <input type="text" name="add_source_button[]" value="'.$page_source.'"/>

        http://localhost/humo-genealogy/admin/index.php?
        page=editor&
        source_add3=1&
        connect_kind=person&
        >> connect_sub_kind=pers_birth_source&
        >> connect_connect_id=I1180
        #pers_birth_sourceI1180
        */

        if ($save_person_data) {
            // *** Manual alive setting ***
            $pers_alive = safe_text_db($_POST["pers_alive"]);
            // *** Only change alive setting if birth or bapise date is changed ***
            if ($_POST["pers_birth_date_previous"] != $_POST["pers_birth_date"] && is_numeric(substr($_POST["pers_birth_date"], -4))) {
                if (date("Y") - substr($_POST["pers_birth_date"], -4) > 120) {
                    $pers_alive = 'deceased';
                }
            }
            if ($_POST["pers_bapt_date_previous"] != $_POST["pers_bapt_date"] && is_numeric(substr($_POST["pers_bapt_date"], -4))) {
                if (date("Y") - substr($_POST["pers_bapt_date"], -4) > 120) {
                    $pers_alive = 'deceased';
                }
            }
            // *** If person is deceased, set alive setting ***
            if ($_POST["pers_death_date"] || $_POST["pers_death_place"] || $_POST["pers_buried_date"] || $_POST["pers_buried_place"]) {
                $pers_alive = 'deceased';
            }

            $pers_prefix = $this->editor_cls->text_process($_POST["pers_prefix"]);
            $pers_prefix = str_replace(' ', '_', $pers_prefix);

            //pers_callname='".$this->editor_cls->text_process($_POST["pers_callname"])."',
            $sql = "UPDATE humo_persons SET
                pers_firstname='" . $this->editor_cls->text_process($_POST["pers_firstname"]) . "',
                pers_prefix='" . $pers_prefix . "',
                pers_lastname='" . $this->editor_cls->text_process($_POST["pers_lastname"]) . "',
                pers_patronym='" . $this->editor_cls->text_process($_POST["pers_patronym"]) . "',
                pers_name_text='" . $this->editor_cls->text_process($_POST["pers_name_text"], true) . "',
                pers_alive='" . $pers_alive . "',
                pers_sexe='" . safe_text_db($_POST["pers_sexe"]) . "',
                pers_own_code='" . safe_text_db($_POST["pers_own_code"]) . "',
                pers_text='" . $this->editor_cls->text_process($_POST["person_text"], true) . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($this->pers_gedcomnumber) . "'";
            //echo $sql;
            $this->dbh->query($sql);

            $pers_stillborn = '';
            if (isset($_POST["pers_stillborn"])) {
                $pers_stillborn = 'y';
            }

            $pers_death_cause = $_POST["pers_death_cause"];
            if (isset($_POST["pers_death_cause2"]) && $_POST["pers_death_cause2"]) {
                $pers_death_cause = $_POST["pers_death_cause2"];
            }

            // *** Automatically calculate birth date if death date and death age is used ***
            if ($_POST["pers_death_age"] != '' && $_POST["pers_death_date"] != '' && $_POST["pers_birth_date"] == '' && $_POST["pers_bapt_date"] == '') {
                $_POST["pers_birth_date"] = 'ABT ' . (substr($_POST["pers_death_date"], -4) - $_POST["pers_death_age"]);
            }

            // *** Process estimates/ calculated date for privacy filter ***
            $pers_cal_date = '';
            if ($_POST["pers_birth_date"]) {
                $pers_cal_date = $_POST["pers_birth_date"];
            } elseif ($_POST["pers_bapt_date"]) {
                $pers_cal_date = $_POST["pers_bapt_date"];
            }
            $pers_cal_date = substr($pers_cal_date, -4);

            $sql = "UPDATE humo_persons SET
                pers_birth_date='" . $this->editor_cls->date_process("pers_birth_date") . "',
                pers_birth_place='" . $this->editor_cls->text_process($_POST["pers_birth_place"]) . "',
                pers_birth_time='" . $this->editor_cls->text_process($_POST["pers_birth_time"]) . "',
                pers_birth_text='" . $this->editor_cls->text_process($_POST["pers_birth_text"], true) . "',
                pers_stillborn='" . $pers_stillborn . "',
                pers_bapt_date='" . $this->editor_cls->date_process("pers_bapt_date") . "',
                pers_bapt_place='" . $this->editor_cls->text_process($_POST["pers_bapt_place"]) . "',
                pers_bapt_text='" . $this->editor_cls->text_process($_POST["pers_bapt_text"], true) . "',
                pers_religion='" . safe_text_db($_POST["pers_religion"]) . "',
                pers_death_date='" . $this->editor_cls->date_process("pers_death_date") . "',
                pers_death_place='" . $this->editor_cls->text_process($_POST["pers_death_place"]) . "',
                pers_death_time='" . $this->editor_cls->text_process($_POST["pers_death_time"]) . "',
                pers_death_text='" . $this->editor_cls->text_process($_POST["pers_death_text"], true) . "',
                pers_death_cause='" . safe_text_db($pers_death_cause) . "',
                pers_death_age='" . safe_text_db($_POST["pers_death_age"]) . "',
                pers_buried_date='" . $this->editor_cls->date_process("pers_buried_date") . "',
                pers_buried_place='" . $this->editor_cls->text_process($_POST["pers_buried_place"]) . "',
                pers_buried_text='" . $this->editor_cls->text_process($_POST["pers_buried_text"], true) . "',";
            if ($pers_cal_date) $sql .= "pers_cal_date='" . $pers_cal_date . "',";
            $sql .= "pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
                pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($this->pers_gedcomnumber) . "'";
            $this->dbh->query($sql);

            // *** Save birth declaration ***
            if ($_POST['birth_decl_id'] && is_numeric($_POST['birth_decl_id'])) {
                $sql = "UPDATE humo_events SET
                    event_date='" . $this->editor_cls->date_process("birth_decl_date") . "',
                    event_place='" . $this->editor_cls->text_process($_POST['birth_decl_place']) . "',
                    event_text='" . $this->editor_cls->text_process($_POST['birth_decl_text']) . "',
                    event_changed_user_id='" . $this->userid . "'
                    WHERE event_id='" . $_POST['birth_decl_id'] . "'";
                $this->dbh->query($sql);
            } elseif ($_POST['birth_decl_date'] || $_POST['birth_decl_place'] || $_POST['birth_decl_text']) {
                $sql = "INSERT INTO humo_events SET
                    event_tree_id='" . $this->tree_id . "',
                    event_gedcomnr='',
                    event_order='1',
                    event_connect_kind='person',
                    event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "',
                    event_kind='birth_declaration',
                    event_event='',
                    event_event_extra='',
                    event_gedcom='EVEN',
                    event_date='" . $this->editor_cls->date_process("birth_decl_date") . "',
                    event_place='" . $this->editor_cls->text_process($_POST['birth_decl_place']) . "',
                    event_text='" . $this->editor_cls->text_process($_POST['birth_decl_text']) . "',
                    event_quality='',
                    event_new_user_id='" . $this->userid . "'";
                $this->dbh->query($sql);
            }

            // *** Save death declaration ***
            if ($_POST['death_decl_id'] && is_numeric($_POST['death_decl_id'])) {
                $sql = "UPDATE humo_events SET
                    event_date='" . $this->editor_cls->date_process("death_decl_date") . "',
                    event_place='" . $this->editor_cls->text_process($_POST['death_decl_place']) . "',
                    event_text='" . $this->editor_cls->text_process($_POST['death_decl_text']) . "',
                    event_changed_user_id='" . $this->userid . "'
                    WHERE event_id='" . $_POST['death_decl_id'] . "'";
                $this->dbh->query($sql);
            } elseif ($_POST['death_decl_date'] || $_POST['death_decl_place'] || $_POST['death_decl_text']) {
                $sql = "INSERT INTO humo_events SET
                    event_tree_id='" . $this->tree_id . "',
                    event_gedcomnr='',
                    event_order='1',
                    event_connect_kind='person',
                    event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "',
                    event_kind='death_declaration',
                    event_event='',
                    event_event_extra='',
                    event_gedcom='EVEN',
                    event_date='" . $this->editor_cls->date_process("death_decl_date") . "',
                    event_place='" . $this->editor_cls->text_process($_POST['death_decl_place']) . "',
                    event_text='" . $this->editor_cls->text_process($_POST['death_decl_text']) . "',
                    event_quality='',
                    event_new_user_id='" . $this->userid . "'";
                $this->dbh->query($sql);
            }

            // *** Extra UPDATE queries if jewish dates is enabled ***
            if ($this->humo_option['admin_hebnight'] == "y") {
                $per_bir_heb = "";
                $per_bur_heb = "";
                $per_dea_heb = "";
                if (isset($_POST["pers_birth_date_hebnight"])) {
                    $per_bir_heb = $_POST["pers_birth_date_hebnight"];
                }
                if (isset($_POST["pers_buried_date_hebnight"])) {
                    $per_bur_heb = $_POST["pers_buried_date_hebnight"];
                }
                if (isset($_POST["pers_death_date_hebnight"])) {
                    $per_dea_heb = $_POST["pers_death_date_hebnight"];
                }
                $sql = "UPDATE humo_persons SET
                    pers_birth_date_hebnight='" . safe_text_db($per_bir_heb) . "',
                    pers_death_date_hebnight='" . safe_text_db($per_dea_heb) . "',
                    pers_buried_date_hebnight='" . safe_text_db($per_bur_heb) . "'
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($this->pers_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // Extra UPDATE queries if Hebrew name is displayed in main Name section (and not under name event)
            if ($this->humo_option['admin_hebname'] == "y") {
                $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom = '_HEBN' AND event_connect_id = '" . $this->pers_gedcomnumber . "' AND event_kind='name' AND event_connect_kind='person'";
                $result = $this->dbh->query($sql);
                if ($result->rowCount() != 0) {     // a Hebrew name entry already exists for this person: UPDATE 
                    if ($_POST["even_hebname"] == '') {  // empty entry: existing hebrew name was deleted so delete the event
                        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom='_HEBN'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "' AND event_kind='name' ";
                        $this->dbh->query($sql);
                    } else {  // update or retain the entered value
                        $sql = "UPDATE `humo_events` SET 
                            event_event='" . safe_text_db($_POST["even_hebname"]) . "',
                            event_changed_user_id='" . $this->userid . "'
                            WHERE event_tree_id='" . $this->tree_id . "' AND  event_gedcom='_HEBN' AND event_kind='name' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "'";
                        $this->dbh->query($sql);
                    }
                } elseif ($_POST["even_hebname"] != '') {  // new Hebrew name event: INSERT
                    // *** Add event. If event is new, use: $new_event=true. ***
                    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                    $this->add_event(false, 'person', $this->pers_gedcomnumber, 'name', $_POST["even_hebname"], '_HEBN', '', '', '');
                }
            }

            // Extra UPDATE queries if brit mila is displayed 
            if ($this->humo_option['admin_brit'] == "y") {
                $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom = '_BRTM' AND event_connect_id = '" . $this->pers_gedcomnumber . "' AND event_connect_kind='person'";
                $result = $this->dbh->query($sql);
                if ($result->rowCount() != 0) {     // a brit mila already exists for this person: UPDATE 
                    if ($_POST["even_brit_date"] == '' && $_POST["even_brit_place"] == '' && $_POST["even_brit_text"] == '') {
                        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom='_BRTM'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "' AND event_kind='event' ";
                        $this->dbh->query($sql);
                    } else {
                        $sql = "UPDATE humo_events SET
                            event_date='" . $this->editor_cls->date_process("even_brit_date") . "',
                            event_place='" . safe_text_db($_POST["even_brit_place"]) . "',
                            event_text='" . safe_text_db($_POST["even_brit_text"]) . "',
                            event_changed_user_id='" . $this->userid . "'
                            WHERE event_tree_id='" . $this->tree_id . "' AND  event_gedcom='_BRTM' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "'";
                        $this->dbh->query($sql);
                    }
                } elseif (
                    isset($_POST["even_brit_date"]) && $_POST["even_brit_date"] != '' || isset($_POST["even_brit_place"]) && $_POST["even_brit_place"] != '' || isset($_POST["even_brit_text"]) && $_POST["even_brit_text"] != ''
                ) {  // new brit mila event: INSERT

                    // *** Add event. If event is new, use: $new_event=true. ***
                    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                    $this->add_event(false, 'person', $this->pers_gedcomnumber, 'event', '', '_BRTM', 'even_brit_date', $_POST["even_brit_place"], $_POST["even_brit_text"]);
                }
            }

            // Extra UPDATE queries if Bar Mitsva is displayed 
            if ($this->humo_option['admin_barm'] == "y") {
                $barmbasm = $_POST["pers_sexe"] == "F" ? "BASM" : "BARM";
                $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom = '" . $barmbasm . "' AND event_connect_id = '" . $this->pers_gedcomnumber . "' AND event_connect_kind='person'";
                $result = $this->dbh->query($sql);
                if ($result->rowCount() != 0) {     // a bar/bat mitsvah already exists for this person: UPDATE 

                    if ($_POST["even_barm_date"] == '' && $_POST["even_barm_place"] == '' && $_POST["even_barm_text"] == '') {
                        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' AND event_gedcom='" . $barmbasm . "'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "' AND event_kind='event' ";
                        $this->dbh->query($sql);
                    } else {
                        $sql = "UPDATE humo_events SET 
                            event_date='" . $this->editor_cls->date_process("even_barm_date") . "',
                            event_place='" . safe_text_db($_POST["even_barm_place"]) . "',
                            event_text='" . safe_text_db($_POST["even_barm_text"]) . "',
                            event_changed_user_id='" . $this->userid . "'
                            WHERE event_tree_id='" . $this->tree_id . "' AND  event_gedcom='" . $barmbasm . "' AND event_connect_id='" . safe_text_db($this->pers_gedcomnumber) . "'";
                        $this->dbh->query($sql);
                    }
                } elseif ($_POST["even_barm_date"] != '' || $_POST["even_barm_place"] != '' || $_POST["even_barm_text"] != '') {  // new BAR/BAT MITSVA event: INSERT

                    // *** Add event. If event is new, use: $new_event=true. ***
                    // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                    $this->add_event(false, 'person', $this->pers_gedcomnumber, 'event', '', $barmbasm, 'even_barm_date', $_POST["even_barm_place"], $_POST["even_barm_text"]);
                }
            }

            $this->family_tree_update();

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes(true);
        }

        // TODO check this code.
        if (isset($_GET['add_person'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'I' . $this->db_functions->generate_gedcomnr($this->tree_id, 'person');
        }

        if (isset($_POST['person_add']) || isset($_POST['relation_add'])) {
            // *** Added new person in relation, store original person gedcomnumber ***
            if (isset($_POST['relation_add'])) {
                $relation_gedcomnumber = $this->pers_gedcomnumber;
            }

            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'I' . $this->db_functions->generate_gedcomnr($this->tree_id, 'person');

            // *** If person is deceased, set alive setting ***
            $pers_alive = safe_text_db($_POST["pers_alive"]);
            if ($_POST["pers_death_date"] || $_POST["pers_death_place"] || $_POST["pers_buried_date"] || $_POST["pers_buried_place"]) {
                $pers_alive = 'deceased';
            }

            $pers_stillborn = '';
            if (isset($_POST["pers_stillborn"])) {
                $pers_stillborn = 'y';
            }

            $pers_death_cause = $_POST["pers_death_cause"];
            if (isset($_POST["pers_death_cause2"]) && $_POST["pers_death_cause2"]) {
                $pers_death_cause = $_POST["pers_death_cause2"];
            }

            // *** Automatically calculate birth date if death date and death age is used ***
            if ($_POST["pers_death_age"] != '' && $_POST["pers_death_date"] != '' && $_POST["pers_birth_date"] == '' && $_POST["pers_bapt_date"] == '') {
                $_POST["pers_birth_date"] = 'ABT ' . (substr($_POST["pers_death_date"], -4) - $_POST["pers_death_age"]);
            }

            // *** Process estimates/ calculated date for privacy filter ***
            $pers_cal_date = '';
            if ($_POST["pers_birth_date"]) {
                $pers_cal_date = $_POST["pers_birth_date"];
            } elseif ($_POST["pers_bapt_date"]) {
                $pers_cal_date = $_POST["pers_bapt_date"];
            }
            $pers_cal_date = substr($pers_cal_date, -4);

            $pers_prefix = $this->editor_cls->text_process($_POST["pers_prefix"]);
            $pers_prefix = str_replace(' ', '_', $pers_prefix);

            //pers_callname='".$this->editor_cls->text_process($_POST["pers_callname"])."',
            $sql = "INSERT INTO humo_persons SET
                pers_tree_id='" . $this->tree_id . "',
                pers_tree_prefix='" . $this->tree_prefix . "',
                pers_famc='',
                pers_fams='',
                pers_gedcomnumber='" . $new_gedcomnumber . "',
                pers_firstname='" . $this->editor_cls->text_process($_POST["pers_firstname"]) . "',
                pers_prefix='" . $pers_prefix . "',
                pers_lastname='" . $this->editor_cls->text_process($_POST["pers_lastname"]) . "',
                pers_patronym='" . $this->editor_cls->text_process($_POST["pers_patronym"]) . "',
                pers_name_text='" . $this->editor_cls->text_process($_POST["pers_name_text"]) . "',
                pers_alive='" . $pers_alive . "',
                pers_sexe='" . safe_text_db($_POST["pers_sexe"]) . "',
                pers_own_code='" . safe_text_db($_POST["pers_own_code"]) . "',
                pers_place_index='',
                pers_text='" . $this->editor_cls->text_process($_POST["person_text"]) . "',

                pers_birth_date='" . $this->editor_cls->date_process("pers_birth_date") . "',
                pers_birth_place='" . $this->editor_cls->text_process($_POST["pers_birth_place"]) . "',
                pers_birth_time='" . $this->editor_cls->text_process($_POST["pers_birth_time"]) . "',
                pers_birth_text='" . $this->editor_cls->text_process($_POST["pers_birth_text"], true) . "',
                pers_stillborn='" . $pers_stillborn . "',
                pers_bapt_date='" . $this->editor_cls->date_process("pers_bapt_date") . "',
                pers_bapt_place='" . $this->editor_cls->text_process($_POST["pers_bapt_place"]) . "',
                pers_bapt_text='" . $this->editor_cls->text_process($_POST["pers_bapt_text"], true) . "',
                pers_religion='" . safe_text_db($_POST["pers_religion"]) . "',
                pers_death_date='" . $this->editor_cls->date_process("pers_death_date") . "',
                pers_death_place='" . $this->editor_cls->text_process($_POST["pers_death_place"]) . "',
                pers_death_time='" . $this->editor_cls->text_process($_POST["pers_death_time"]) . "',
                pers_death_text='" . $this->editor_cls->text_process($_POST["pers_death_text"], true) . "',
                pers_death_cause='" . safe_text_db($pers_death_cause) . "',
                pers_death_age='" . safe_text_db($_POST["pers_death_age"]) . "',
                pers_buried_date='" . $this->editor_cls->date_process("pers_buried_date") . "',
                pers_buried_place='" . $this->editor_cls->text_process($_POST["pers_buried_place"]) . "',
                pers_buried_text='" . $this->editor_cls->text_process($_POST["pers_buried_text"], true) . "',";
            if ($pers_cal_date) $sql .= "pers_cal_date='" . $pers_cal_date . "',";
            $sql .= "pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
                pers_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            // *** Save birth declaration (there is no birth declaration when new relation is added) ***
            if (isset($_POST['birth_decl_date']) && ($_POST['birth_decl_date'] || $_POST['birth_decl_place'] || $_POST['birth_decl_text'])) {
                $sql = "INSERT INTO humo_events SET
                    event_tree_id='" . $this->tree_id . "',
                    event_gedcomnr='',
                    event_order='1',
                    event_connect_kind='person',
                    event_connect_id='" . $new_gedcomnumber . "',
                    event_kind='birth_declaration',
                    event_event='',
                    event_event_extra='',
                    event_gedcom='EVEN',
                    event_date='" . safe_text_db($_POST['birth_decl_date']) . "',
                    event_place='" . safe_text_db($_POST['birth_decl_place']) . "',
                    event_text='" . safe_text_db($_POST['birth_decl_text']) . "',
                    event_quality='',
                    event_new_user_id='" . $this->userid . "'";
                $this->dbh->query($sql);
            }

            // *** Save death declaration (there is no death declaration when new relation is added) ***
            if (isset($_POST['death_decl_date']) && ($_POST['death_decl_date'] || $_POST['death_decl_place'] || $_POST['death_decl_text'])) {
                $sql = "INSERT INTO humo_events SET
                    event_tree_id='" . $this->tree_id . "',
                    event_gedcomnr='',
                    event_order='1',
                    event_connect_kind='person',
                    event_connect_id='" . $new_gedcomnumber . "',
                    event_kind='death_declaration',
                    event_event='',
                    event_event_extra='',
                    event_gedcom='EVEN',
                    event_date='" . safe_text_db($_POST['death_decl_date']) . "',
                    event_place='" . safe_text_db($_POST['death_decl_place']) . "',
                    event_text='" . safe_text_db($_POST['death_decl_text']) . "',
                    event_quality='',
                    event_new_user_id='" . $this->userid . "'";
                $this->dbh->query($sql);
            }

            // *** Only needed for jewish settings ***
            if ($this->humo_option['admin_hebnight'] == "y" && isset($_POST["pers_birth_date_hebnight"])) {
                $sql = "UPDATE humo_persons SET
                    pers_birth_date_hebnight='" . safe_text_db($_POST["pers_birth_date_hebnight"]) . "',
                    pers_death_date_hebnight='" . safe_text_db($_POST["pers_death_date_hebnight"]) . "',
                    pers_buried_date_hebnight='" . safe_text_db($_POST["pers_buried_date_hebnight"]) . "'
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($new_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // TODO: this code is used multiple times in this script.
            // *** New jul.2023: also use special names ***
            if (isset($_POST["event_event_name_new"]) && $_POST["event_event_name_new"] != "") {
                $event_kind = 'name';
                if ($_POST['event_gedcom_new'] == 'NPFX') {
                    $event_kind = 'NPFX';
                }
                if ($_POST['event_gedcom_new'] == 'NSFX') {
                    $event_kind = 'NSFX';
                }
                if ($_POST['event_gedcom_new'] == 'nobility') {
                    $event_kind = 'nobility';
                }
                if ($_POST['event_gedcom_new'] == 'title') {
                    $event_kind = 'title';
                }
                if ($_POST['event_gedcom_new'] == 'lordship') {
                    $event_kind = 'lordship';
                }

                $event_gedcom = $_POST['event_gedcom_new'];
                $event_event = $_POST['event_event_name_new'];
                $event_date = '';

                $event_place = "";
                //if (isset($_POST["event_place_name"])) $event_place = $_POST["event_place_name"];
                $event_text = "";
                //if (isset($_POST["event_text_name"]))  $event_text = $_POST["event_text_name"];

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $new_gedcomnumber, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text);
            }

            // *** New person: add profession ***
            if (isset($_POST["event_profession"]) && $_POST["event_profession"] != "" && $_POST["event_profession"] != "Profession") {
                //$event_date = '';
                $event_place = "";
                if (isset($_POST["event_place_profession"])) {
                    $event_place = $_POST["event_place_profession"];
                }
                $event_text = "";
                if (isset($_POST["event_text_profession"])) {
                    $event_text = $_POST["event_text_profession"];
                }

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                //$this->add_event(true, 'person', $new_gedcomnumber, 'profession', $_POST["event_profession"], '', $event_date, $event_place, $event_text);
                $this->add_event(true, 'person', $new_gedcomnumber, 'profession', $_POST["event_profession"], '', 'event_date_profession', $event_place, $event_text);
            }

            // *** New person: add religion ***
            if (isset($_POST["event_religion"]) && $_POST["event_religion"] != "" && $_POST["event_religion"] != "Religion") {
                $event_place = "";
                if (isset($_POST["event_place_religion"])) {
                    $event_place = $_POST["event_place_religion"];
                }
                $event_text = "";
                if (isset($_POST["event_text_religion"])) {
                    $event_text = $_POST["event_text_religion"];
                }

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $new_gedcomnumber, 'religion', $_POST["event_religion"], 'RELI', 'event_date_religion', $event_place, $event_text);
            }

            if (!isset($_POST['child_connect'])) {
                // *** Show new person ***
                $this->pers_gedcomnumber = $new_gedcomnumber;
                $_SESSION['admin_pers_gedcomnumber'] = $this->pers_gedcomnumber;
            }

            $this->family_tree_update();

            // *** Add child to family, add a new child (new gedcomnumber) ***
            if (isset($_POST['child_connect'])) {
                $_POST['child_connect2'] = $new_gedcomnumber;
            }

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes(true);
        }

        // *** Family move down ***
        if (isset($_GET['fam_down'])) {
            $child_array_org = explode(";", safe_text_db($_GET['fam_array']));
            $child_array = $child_array_org;
            $child_array_id = safe_text_db($_GET['fam_down']);
            $child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
            $child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
            $fams = '';
            $counter = count($child_array);
            for ($k = 0; $k < $counter; $k++) {
                if ($k > 0) {
                    $fams .= ';';
                }
                $fams .= $child_array[$k];
            }
            $sql = "UPDATE humo_persons SET
                pers_fams='" . $fams . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_id='" . safe_text_db($_GET["person_id"]) . "'";
            $this->dbh->query($sql);
        }

        // *** Family move up ***
        if (isset($_GET['fam_up'])) {
            $child_array_org = explode(";", safe_text_db($_GET['fam_array']));
            $child_array = $child_array_org;
            $child_array_id = safe_text_db($_GET['fam_up']) - 1;
            $child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
            $child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
            $fams = '';
            $counter = count($child_array);
            for ($k = 0; $k < $counter; $k++) {
                if ($k > 0) {
                    $fams .= ';';
                }
                $fams .= $child_array[$k];
            }
            $sql = "UPDATE humo_persons SET
                pers_fams='" . $fams . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_id='" . safe_text_db($_GET["person_id"]) . "'";
            $this->dbh->query($sql);
        }

        // *** Family disconnect ***
        if (isset($_POST['fam_remove2'])) {
            $fam_remove = safe_text_db($_POST['fam_remove3']);

            // *** Remove fams number from man and woman ***
            $new_nr = $this->db_functions->get_family($fam_remove);

            // *** Disconnect ALL children from marriage ***
            if ($new_nr->fam_children) {
                $child_gedcomnumber = explode(";", $new_nr->fam_children);
                foreach ($child_gedcomnumber as $i => $value) {
                    // *** Find child data ***
                    // TODO check line
                    $resultDb = $this->db_functions->get_person($child_gedcomnumber[$i]);

                    // *** Remove parents from child record ***
                    $sql = "UPDATE humo_persons SET
                        pers_famc='',
                        pers_changed_user_id='" . $this->userid . "'
                        WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($child_gedcomnumber[$i]) . "'";
                    $this->dbh->query($sql);
                }
            }

            if (isset($new_nr->fam_man)) {
                $this->fams_remove($new_nr->fam_man, $fam_remove);
            }

            unset($fams2);
            if (isset($new_nr->fam_woman)) {
                $this->fams_remove($new_nr->fam_woman, $fam_remove);
            }

            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                AND event_connect_kind='family' AND event_connect_id='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'
                AND address_connect_sub_kind='family' AND address_connect_id='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_connect_id='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $this->family_tree_update();

            $confirm_relation .= '<div class="alert alert-success">';
            $confirm_relation .= __('Marriage is removed!');
            $confirm_relation .= '</div>';

            // *** If this relation is removed, show 1st relation of person, or link to new relation ***
            $marriage = '';
            if (isset($this->person->pers_fams) && $this->person->pers_fams) {
                $fams1 = explode(";", $this->person->pers_fams);
                $marriage = $fams1[0];
            }
            $_POST["marriage_nr"] = $marriage;
            $_SESSION['admin_fam_gedcomnumber'] = $marriage;
        }

        // *** Add NEW parents to a child ***
        if (isset($_POST['add_parents2'])) {
            // *** Generate new GEDCOM number ***
            $fam_gedcomnumber = 'F' . $this->db_functions->generate_gedcomnr($this->tree_id, 'family');

            // *** Generate new GEDCOM number ***
            $temp_number = $this->db_functions->generate_gedcomnr($this->tree_id, 'person');
            $man_gedcomnumber = 'I' . $temp_number;
            $woman_gedcomnumber = 'I' . ($temp_number + 1);

            $sql = "INSERT INTO humo_families SET
                fam_gedcomnumber='" . $fam_gedcomnumber . "',
                fam_tree_id='" . $this->tree_id . "',
                fam_kind='',
                fam_man='" . safe_text_db($man_gedcomnumber) . "',
                fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
                fam_children='" . safe_text_db($this->pers_gedcomnumber) . "',
                fam_relation_date='', fam_relation_place='', fam_relation_text='',
                fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
                fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
                fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
                fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
                fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
                fam_text='',
                fam_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_families SET 
                    fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight='' 
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Add father ***
            //pers_callname='',
            $pers_alive1 = '';
            if (isset($_POST['pers_alive1'])) {
                $pers_alive1 = safe_text_db($_POST['pers_alive1']);
            }
            $pers_sexe1 = '';
            if (isset($_POST['pers_sexe1'])) {
                $pers_sexe1 = safe_text_db($_POST['pers_sexe1']);
            }
            $sql = "INSERT INTO humo_persons SET
                pers_gedcomnumber='" . $man_gedcomnumber . "',
                pers_tree_id='" . $this->tree_id . "',
                pers_tree_prefix='" . $this->tree_prefix . "',
                pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "',
                pers_firstname='" . safe_text_db($_POST['pers_firstname1']) . "',
                pers_prefix='" . safe_text_db($_POST['pers_prefix1']) . "',
                pers_lastname='" . safe_text_db($_POST['pers_lastname1']) . "',
                pers_patronym='" . safe_text_db($_POST['pers_patronym1']) . "',
                pers_name_text='',
                pers_alive='" . $pers_alive1 . "',
                pers_sexe='" . $pers_sexe1 . "',
                pers_own_code='', pers_place_index='', pers_text='',
                pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
                pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
                pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
                pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='', 
                pers_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            // *** Add special name ***
            if (isset($_POST["event_event_name1"]) && $_POST["event_event_name1"] != "") {
                $event_kind = 'name';
                if ($_POST['event_gedcom_add1'] == 'NPFX') {
                    $event_kind = 'NPFX';
                }
                if ($_POST['event_gedcom_add1'] == 'NSFX') {
                    $event_kind = 'NSFX';
                }
                if ($_POST['event_gedcom_add1'] == 'nobility') {
                    $event_kind = 'nobility';
                }
                if ($_POST['event_gedcom_add1'] == 'title') {
                    $event_kind = 'title';
                }
                if ($_POST['event_gedcom_add1'] == 'lordship') {
                    $event_kind = 'lordship';
                }

                $event_gedcom = $_POST['event_gedcom_add1'];
                $event_event = $_POST['event_event_name1'];
                $event_date = '';

                $event_place = "";
                //if (isset($_POST["event_place_name"])) $event_place = $_POST["event_place_name"];
                $event_text = "";
                //if (isset($_POST["event_text_name"]))  $event_text = $_POST["event_text_name"];

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $man_gedcomnumber, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text);
            }

            // *** Add profession ***
            if (isset($_POST["event_profession1"]) && $_POST["event_profession1"] != "" && $_POST["event_profession1"] != "Profession") {
                $event_place = "";
                if (isset($_POST["event_place_profession1"])) {
                    $event_place = $_POST["event_place_profession1"];
                }
                $event_text = "";
                if (isset($_POST["event_text_profession1"])) {
                    $event_text = $_POST["event_text_profession1"];
                }

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $man_gedcomnumber, 'profession', $_POST["event_profession1"], '', '', $event_place, $event_text);
            }

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_persons SET 
                    pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($man_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Add mother ***
            //pers_callname='',
            $pers_alive2 = '';
            if (isset($_POST['pers_alive2'])) {
                $pers_alive2 = safe_text_db($_POST['pers_alive2']);
            }
            $pers_sexe2 = '';
            if (isset($_POST['pers_sexe2'])) {
                $pers_sexe2 = safe_text_db($_POST['pers_sexe2']);
            }
            $sql = "INSERT INTO humo_persons SET
                pers_gedcomnumber='" . $woman_gedcomnumber . "',
                pers_tree_id='" . $this->tree_id . "',
                pers_tree_prefix='" . $this->tree_prefix . "',
                pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "',
                pers_firstname='" . safe_text_db($_POST['pers_firstname2']) . "',
                pers_prefix='" . safe_text_db($_POST['pers_prefix2']) . "',
                pers_lastname='" . safe_text_db($_POST['pers_lastname2']) . "',
                pers_patronym='" . safe_text_db($_POST['pers_patronym2']) . "',
                pers_name_text='',
                pers_alive='" . $pers_alive2 . "',
                pers_sexe='" . $pers_sexe2 . "',
                pers_own_code='', pers_place_index='', pers_text='',
                pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
                pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
                pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
                pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='', 
                pers_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            // *** Add special name ***
            if (isset($_POST["event_event_name2"]) && $_POST["event_event_name2"] != "") {
                $event_kind = 'name';
                if ($_POST['event_gedcom_add2'] == 'NPFX') {
                    $event_kind = 'NPFX';
                }
                if ($_POST['event_gedcom_add2'] == 'NSFX') {
                    $event_kind = 'NSFX';
                }
                if ($_POST['event_gedcom_add2'] == 'nobility') {
                    $event_kind = 'nobility';
                }
                if ($_POST['event_gedcom_add2'] == 'title') {
                    $event_kind = 'title';
                }
                if ($_POST['event_gedcom_add2'] == 'lordship') {
                    $event_kind = 'lordship';
                }

                $event_gedcom = $_POST['event_gedcom_add2'];
                $event_event = $_POST['event_event_name2'];
                $event_date = '';

                $event_place = "";
                //if (isset($_POST["event_place_name"])) $event_place = $_POST["event_place_name"];
                $event_text = "";
                //if (isset($_POST["event_text_name"]))  $event_text = $_POST["event_text_name"];

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $woman_gedcomnumber, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text);
            }

            // *** Add profession ***
            if (isset($_POST["event_profession2"]) && $_POST["event_profession2"] != "" && $_POST["event_profession2"] != "Profession") {
                $event_place = "";
                if (isset($_POST["event_place_profession2"])) {
                    $event_place = $_POST["event_place_profession2"];
                }
                $event_text = "";
                if (isset($_POST["event_text_profession2"])) {
                    $event_text = $_POST["event_text_profession2"];
                }

                // *** Add event. If event is new, use: $new_event=true. ***
                // *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
                $this->add_event(true, 'person', $woman_gedcomnumber, 'profession', $_POST["event_profession2"], '', '', $event_place, $event_text);
            }

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_persons SET 
                    pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($woman_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Add parents to child record ***
            $sql = "UPDATE humo_persons SET
                pers_famc='" . safe_text_db($fam_gedcomnumber) . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($this->pers_gedcomnumber) . "'";
            $this->dbh->query($sql);

            $this->family_tree_update();
        }

        // *** Add EXISTING parents to a child ***
        if (isset($_POST['add_parents']) && $_POST['add_parents'] != '') {
            $parentsDb = $this->db_functions->get_family(strtoupper($_POST['add_parents']));

            // *** Check if manual selected family is existing family in family tree ***
            if (isset($parentsDb->fam_gedcomnumber) && strtoupper($_POST['add_parents']) == $parentsDb->fam_gedcomnumber) {
                if ($parentsDb->fam_children) {
                    $fam_children = $parentsDb->fam_children . ';' . $this->pers_gedcomnumber;
                } else {
                    $fam_children = $this->pers_gedcomnumber;
                }

                $sql = "UPDATE humo_families SET
                    fam_children='" . $fam_children . "',
                    fam_changed_user_id='" . $this->userid . "'
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $parentsDb->fam_gedcomnumber . "'";
                $this->dbh->query($sql);

                // *** Add parents to child record ***
                $sql = "UPDATE humo_persons SET
                    pers_famc='" . $parentsDb->fam_gedcomnumber . "',
                    pers_changed_user_id='" . $this->userid . "'
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . $this->pers_gedcomnumber . "'";
                $this->dbh->query($sql);

                $this->family_tree_update();

                $confirm .= '<div class="alert alert-success">';
                $confirm .= __('Parents are selected!');
                $confirm .= '</div>';
            } else {
                $confirm .= '<div class="alert alert-danger">';
                $confirm .= __('Manual selected family isn\'t an existing family in the family tree!');
                $confirm .= '</div>';
            }
        }

        // *** Add child to family ***
        if (isset($_POST['child_connect2']) && $_POST['child_connect2'] && !isset($_POST['submit'])) {

            // *** Check valid gedcomnumber and check if child already has parents connected! ***
            $resultDb = $this->db_functions->get_person($_POST["child_connect2"]);

            // *** Check if input is a valid gedcomnumber ***
            if (isset($resultDb->pers_gedcomnumber)) {

                if ($resultDb->pers_famc && !isset($_POST['child_connecting'])) {
                    $confirm .= '<div class="alert alert-danger">';
                    $confirm .= __('Child already has parents connected! Are you sure you want to connect this child?');
                    $confirm .= ' <form method="post" action="index.php" style="display : inline;">';
                    $confirm .= '<input type="hidden" name="page" value="editor">';
                    $confirm .= '<input type="hidden" name="family_id" value="' . $_POST['family_id'] . '">';
                    $confirm .= '<input type="hidden" name="children" value="' . $_POST['children'] . '">';
                    $confirm .= '<input type="hidden" name="child_connect2" value="' . $_POST['child_connect2'] . '">';
                    $confirm .= ' <input type="submit" name="child_connecting" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
                    $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
                    $confirm .= '</form>';
                    $confirm .= '</div>';
                } else {

                    // *** First check if person already was connected to other parents: remove this person as child from this family. ***
                    if ($resultDb->pers_famc) {
                        $famDb = $this->db_functions->get_family($resultDb->pers_famc);
                        $fam_children = explode(";", $famDb->fam_children);
                        foreach ($fam_children as $key => $value) {
                            if ($fam_children[$key] != $_POST["child_connect2"]) {
                                $fam_children2[] = $fam_children[$key];
                            }
                        }
                        $fam_children3 = '';
                        if (isset($fam_children2[0])) {
                            $fam_children3 = implode(";", $fam_children2);
                        }

                        $sql = "UPDATE humo_families SET fam_children='" . $fam_children3 . "'
                            WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . $resultDb->pers_famc . "'";
                        $this->dbh->query($sql);
                    }

                    // *** Change i10 into I10 ***
                    $_POST["child_connect2"] = ucfirst($_POST["child_connect2"]);
                    // *** Change entry "48" into "I48" ***
                    if (substr($_POST["child_connect2"], 0, 1) !== "I") {
                        $_POST["child_connect2"] = "I" . $_POST["child_connect2"];
                    }

                    if (isset($_POST["children"]) && $_POST["children"]) {
                        $sql = "UPDATE humo_families SET
                            fam_children='" . safe_text_db($_POST["children"]) . ';' . safe_text_db($_POST["child_connect2"]) . "',
                            fam_changed_user_id='" . $this->userid . "'
                            WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['family_id']) . "'";
                    } else {
                        $sql = "UPDATE humo_families SET
                            fam_children='" . safe_text_db($_POST["child_connect2"]) . "',
                            fam_changed_user_id='" . $this->userid . "'
                            WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['family_id']) . "'";
                    }
                    //echo $sql;
                    $this->dbh->query($sql);

                    // *** Add parents to child record ***
                    $sql = "UPDATE humo_persons SET
                        pers_famc='" . safe_text_db($_POST['family_id']) . "',
                        pers_changed_user_id='" . $this->userid . "'
                        WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($_POST["child_connect2"]) . "'";
                    $this->dbh->query($sql);

                    $this->family_tree_update();
                }
            }
        }

        // *** Disconnect child ***
        if (isset($_POST['child_disconnecting'])) {
            $sql = "UPDATE humo_families SET
                fam_children='" . safe_text_db($_POST["child_disconnect2"]) . "',
                fam_changed_user_id='" . $this->userid . "'
                WHERE fam_id='" . safe_text_db($_POST["family_id"]) . "'";
            $this->dbh->query($sql);

            // *** Remove parents from child record ***
            $sql = "UPDATE humo_persons SET
                pers_famc='',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($_POST["child_disconnect_gedcom"]) . "'";
            $this->dbh->query($sql);
        }

        // *** Child move down ***
        if (isset($_GET['child_down'])) {
            $child_array_org = explode(";", safe_text_db($_GET['child_array']));
            $child_array = $child_array_org;
            $child_array_id = safe_text_db($_GET['child_down']);
            $child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
            $child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
            $fam_children = '';
            // use implode: $fam_children = implode(";", $child_array);
            $counter = count($child_array);
            // use implode: $fam_children = implode(";", $child_array);
            for ($k = 0; $k < $counter; $k++) {
                if ($k > 0) {
                    $fam_children .= ';';
                }
                $fam_children .= $child_array[$k];
            }
            $sql = "UPDATE humo_families SET
                fam_children='" . $fam_children . "',
                fam_changed_user_id'" . $this->userid . "'
                WHERE fam_id='" . safe_text_db($_GET["family_id"]) . "'";
            $this->dbh->query($sql);
        }

        // *** Child move up ***
        if (isset($_GET['child_up'])) {
            $child_array_org = explode(";", safe_text_db($_GET['child_array']));
            $child_array = $child_array_org;
            $child_array_id = safe_text_db($_GET['child_up']) - 1;
            $child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
            $child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
            $fam_children = '';
            // use implode: $fam_children = implode(";", $child_array);
            $counter = count($child_array);
            // use implode: $fam_children = implode(";", $child_array);
            for ($k = 0; $k < $counter; $k++) {
                if ($k > 0) {
                    $fam_children .= ';';
                }
                $fam_children .= $child_array[$k];
            }
            $sql = "UPDATE humo_families SET
                fam_children='" . $fam_children . "',
                fam_changed_user_id='" . $this->userid . "'
                WHERE fam_id='" . safe_text_db($_GET["family_id"]) . "'";
            $this->dbh->query($sql);
        }


        // ***************************
        // *** PROCESS DATA FAMILY ***
        // ***************************

        // *** Add new family with new partner ***
        if (isset($_POST['relation_add'])) {
            // *** Generate new GEDCOM number ***
            $fam_gedcomnumber = 'F' . $this->db_functions->generate_gedcomnr($this->tree_id, 'family');

            // *** Directly show new marriage on screen ***
            $_POST["marriage_nr"] = $fam_gedcomnumber;
            $marriage = $fam_gedcomnumber;
            $_SESSION['admin_fam_gedcomnumber'] = $marriage;

            // *** Generate new GEDCOM number ***
            //$partner_gedcomnumber='I'.$this->db_functions->generate_gedcomnr($this->tree_id,'person');
            $partner_gedcomnumber = $this->pers_gedcomnumber;
            //$pers_gedcomnumber = $relation_gedcomnumber;

            $person_db = $this->db_functions->get_person($relation_gedcomnumber);
            if ($person_db->pers_sexe == 'M') {
                $man_gedcomnumber = $relation_gedcomnumber;
                $woman_gedcomnumber = $partner_gedcomnumber;
            } elseif ($person_db->pers_sexe == 'F') {
                $man_gedcomnumber = $partner_gedcomnumber;
                $woman_gedcomnumber = $relation_gedcomnumber;
            } else {
                // Unknown sexe.
                $man_gedcomnumber = $relation_gedcomnumber;
                $woman_gedcomnumber = $partner_gedcomnumber;
            }

            $sql = "INSERT INTO humo_families SET
                fam_tree_id='" . $this->tree_id . "',
                fam_gedcomnumber='" . $fam_gedcomnumber . "', fam_kind='',
                fam_man='" . safe_text_db($man_gedcomnumber) . "', fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
                fam_children='',
                fam_relation_date='', fam_relation_place='', fam_relation_text='',
                fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
                fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
                fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
                fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
                fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
                fam_text='',
                fam_new_user_id='" . $this->userid . "'";
            //echo $sql;
            $this->dbh->query($sql);

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_families SET 
                    fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight=''  
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Update famc and fams for new added partner ***
            //$sql = "UPDATE humo_persons SET
            //    pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "'
            //    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($partner_gedcomnumber) . "'";
            //$this->dbh->query($sql);
            // *** Add marriage to person ***
            $this->fams_add($partner_gedcomnumber, $fam_gedcomnumber);

            // *** Add marriage to person ***
            $this->fams_add($relation_gedcomnumber, $fam_gedcomnumber);

            // extra UPDATE queries if jewish dates enabled
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_persons SET 
                    pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
                    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_gedcomnumber='" . safe_text_db($partner_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Update $pers_gedcomnumber. Should be original value. ***
            $this->pers_gedcomnumber = $relation_gedcomnumber;

            $this->family_tree_update();
        }

        // *** Add new family with selected partner ***
        if (isset($_POST['relation_add2']) && $_POST['relation_add2'] != '') {
            // *** Change i10 into I10 ***
            $_POST['relation_add2'] = ucfirst($_POST['relation_add2']);
            // *** Change entry "48" into "I48" ***
            if (substr($_POST['relation_add2'], 0, 1) !== "I") {
                $_POST['relation_add2'] = "I" . $_POST['relation_add2'];
            }

            // *** Generate new GEDCOM number ***
            $fam_gedcomnumber = 'F' . $this->db_functions->generate_gedcomnr($this->tree_id, 'family');

            // *** Directly show new marriage on screen ***
            $_POST["marriage_nr"] = $fam_gedcomnumber;
            $marriage = $fam_gedcomnumber;
            $_SESSION['admin_fam_gedcomnumber'] = $marriage;

            $person_db = $this->db_functions->get_person($this->pers_gedcomnumber);
            if ($person_db->pers_sexe == 'M') {
                $man_gedcomnumber = $this->pers_gedcomnumber;
                $woman_gedcomnumber = $_POST['relation_add2'];
                //$sexe = 'F';
            } else {
                $man_gedcomnumber = $_POST['relation_add2'];
                $woman_gedcomnumber = $this->pers_gedcomnumber;
                //$sexe = 'M';
            }

            $sql = "INSERT INTO humo_families SET
                fam_tree_id='" . $this->tree_id . "',
                fam_gedcomnumber='" . $fam_gedcomnumber . "', fam_kind='',
                fam_man='" . safe_text_db($man_gedcomnumber) . "', fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
                fam_children='',
                fam_relation_date='', fam_relation_place='', fam_relation_text='',
                fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
                fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
                fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
                fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
                fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
                fam_text='',
                fam_new_user_id='" . $this->userid . "'";
            //echo $sql.'<br>';
            $this->dbh->query($sql);

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $sql = "UPDATE humo_families SET 
                    fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight='' 
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
                $this->dbh->query($sql);
            }

            // *** Add marriage to person records MAN and WOMAN ***
            $this->fams_add($man_gedcomnumber, $fam_gedcomnumber);
            $this->fams_add($woman_gedcomnumber, $fam_gedcomnumber);

            $this->family_tree_update();
        }

        // *** Switch parents ***
        if (isset($_POST['parents_switch'])) {
            $sql = "UPDATE humo_families SET
                fam_man='" . safe_text_db($_POST["connect_woman"]) . "',
                fam_woman='" . safe_text_db($_POST["connect_man"]) . "'
                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
            $this->dbh->query($sql);

            // *** Empty search boxes if a switch is made ***
            $_POST['search_quicksearch_woman'] = '';
            $_POST['search_quicksearch_man'] = '';
        }

        // *** Also save relation data if witnesses are added ***
        $save_relation_data = false;
        if (isset($_POST['marriage_change'])) {
            $save_relation_data = true;
        }
        if (isset($_POST['add_marriage_witness'])) {
            $save_relation_data = true;
        }
        if (isset($_POST['add_marriage_witness_rel'])) {
            $save_relation_data = true;
        }

        // *** Also save relation data if addresses are added ***
        if (isset($_POST['relation_add_address'])) {
            $save_relation_data = true;
        }

        // *** Also save relation data if addresses are added ***
        if (isset($_POST['add_marriage_picture'])) {
            $save_relation_data = true;
        }
        if (isset($_POST['relation_add_media'])) {
            $save_relation_data = true;
        }

        // ** Change relation ***
        if ($save_relation_data == true) {
            // *** Change i10 into I10 ***
            $_POST["connect_man"] = ucfirst($_POST["connect_man"]);
            $_POST["connect_woman"] = ucfirst($_POST["connect_woman"]);

            // *** Man is changed in marriage ***
            if ($_POST["connect_man"] != $_POST["connect_man_old"]) {
                $this->fams_remove($_POST['connect_man_old'], $_POST['marriage']);
                $this->fams_add($_POST['connect_man'], $_POST['marriage']);
            }
            // *** Woman is changed in marriage ***
            if ($_POST["connect_woman"] != $_POST["connect_woman_old"]) {
                $this->fams_remove($_POST['connect_woman_old'], $_POST['marriage']);
                $this->fams_add($_POST['connect_woman'], $_POST['marriage']);
            }

            $fam_div_text = '';
            if (isset($_POST['fam_div_no_data'])) {
                $fam_div_text = 'DIVORCE';
            }
            if ($_POST["fam_div_text"]) {
                $fam_div_text = $_POST["fam_div_text"];
            }

            $sql = "UPDATE humo_families SET
                fam_kind='" . safe_text_db($_POST["fam_kind"]) . "',
                fam_man='" . safe_text_db($_POST["connect_man"]) . "',
                fam_woman='" . safe_text_db($_POST["connect_woman"]) . "',
                fam_relation_date='" . $this->editor_cls->date_process("fam_relation_date") . "',
                fam_relation_end_date='" . $this->editor_cls->date_process("fam_relation_end_date") . "',
                fam_relation_place='" . $this->editor_cls->text_process($_POST["fam_relation_place"]) . "',
                fam_relation_text='" . $this->editor_cls->text_process($_POST["fam_relation_text"], true) . "',
                fam_man_age='" . safe_text_db($_POST["fam_man_age"]) . "',
                fam_woman_age='" . safe_text_db($_POST["fam_woman_age"]) . "',
                fam_marr_notice_date='" . $this->editor_cls->date_process("fam_marr_notice_date") . "',
                fam_marr_notice_place='" . $this->editor_cls->text_process($_POST["fam_marr_notice_place"]) . "',
                fam_marr_notice_text='" . $this->editor_cls->text_process($_POST["fam_marr_notice_text"], true) . "',
                fam_marr_date='" . $this->editor_cls->date_process("fam_marr_date") . "',
                fam_marr_place='" . $this->editor_cls->text_process($_POST["fam_marr_place"]) . "',
                fam_marr_text='" . $this->editor_cls->text_process($_POST["fam_marr_text"], true) . "',
                fam_marr_authority='" . safe_text_db($_POST["fam_marr_authority"]) . "',
                fam_marr_church_date='" . $this->editor_cls->date_process("fam_marr_church_date") . "',
                fam_marr_church_place='" . $this->editor_cls->text_process($_POST["fam_marr_church_place"]) . "',
                fam_marr_church_text='" . $this->editor_cls->text_process($_POST["fam_marr_church_text"], true) . "',
                fam_marr_church_notice_date='" . $this->editor_cls->date_process("fam_marr_church_notice_date") . "',
                fam_marr_church_notice_place='" . $this->editor_cls->text_process($_POST["fam_marr_church_notice_place"]) . "',
                fam_marr_church_notice_text='" . $this->editor_cls->text_process($_POST["fam_marr_church_notice_text"], true) . "',
                fam_religion='" . safe_text_db($_POST["fam_religion"]) . "',
                fam_div_date='" . $this->editor_cls->date_process("fam_div_date") . "',
                fam_div_place='" . $this->editor_cls->text_process($_POST["fam_div_place"]) . "',
                fam_div_text='" . $this->editor_cls->text_process($fam_div_text, true) . "',
                fam_div_authority='" . safe_text_db($_POST["fam_div_authority"]) . "',
                fam_text='" . $this->editor_cls->text_process($_POST["fam_text"], true) . "',
                fam_changed_user_id='" . $this->userid . "'
                WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
            $this->dbh->query($sql);

            // only needed for jewish settings
            if ($this->humo_option['admin_hebnight'] == "y") {
                $f_m_n_d_h = "";
                $f_m_d_h = "";
                $f_m_c_d_h = "";
                $f_m_c_n_d_h = "";
                if (isset($_POST["fam_marr_notice_date_hebnight"])) {
                    $f_m_n_d_h = $_POST["fam_marr_notice_date_hebnight"];
                }
                if (isset($_POST["fam_marr_date_hebnight"])) {
                    $f_m_d_h = $_POST["fam_marr_date_hebnight"];
                }
                if (isset($_POST["fam_marr_church_date_hebnight"])) {
                    $f_m_c_d_h = $_POST["fam_marr_church_date_hebnight"];
                }
                if (isset($_POST["fam_marr_church_notice_date_hebnight"])) {
                    $f_m_c_n_d_h = $_POST["fam_marr_church_notice_date_hebnight"];
                }
                $sql = "UPDATE humo_families SET 
                    fam_marr_notice_date_hebnight='" . $this->editor_cls->text_process($f_m_n_d_h) . "', 
                    fam_marr_date_hebnight='" . $this->editor_cls->text_process($f_m_d_h) . "', 
                    fam_marr_church_date_hebnight='" . $this->editor_cls->text_process($f_m_c_d_h) . "', 
                    fam_marr_church_notice_date_hebnight='" . $this->editor_cls->text_process($f_m_c_n_d_h) . "' 
                    WHERE fam_tree_id='" . $this->tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
                $this->dbh->query($sql);
            }

            $this->family_tree_update();
        }
        return $confirm;
    }

    // *** Some functions to add and remove a fams number from a person (if marriage is changed) ***
    function fams_add($personnr, $familynr): void
    {
        // *** Add marriage to person records ***
        $person_db = $this->db_functions->get_person($personnr);
        if ($person_db->pers_gedcomnumber) {
            $fams = $person_db->pers_fams;
            if ($fams) {
                $fams .= ';' . $familynr;
            } else {
                $fams = $familynr;
            }
            $sql = "UPDATE humo_persons SET
                pers_fams='" . $fams . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_id='" . $person_db->pers_id . "'";
            $this->dbh->query($sql);
        }
    }

    function fams_remove($personnr, $familynr): void
    {
        $person_db = $this->db_functions->get_person($personnr);
        if ($person_db->pers_gedcomnumber) {
            $fams = explode(";", $person_db->pers_fams);
            foreach ($fams as $key => $value) {
                if ($fams[$key] != $familynr) {
                    $fams2[] = $fams[$key];
                }
            }
            $fams3 = '';
            if (isset($fams2[0])) {
                $fams3 = implode(";", $fams2);
            }

            $sql = "UPDATE humo_persons SET
                pers_fams='" . $fams3 . "',
                pers_changed_user_id='" . $this->userid . "'
                WHERE pers_id='" . $person_db->pers_id . "'";
            $this->dbh->query($sql);
        }
    }

    // *** Calculate and update nr. of persons and nr. of families ***
    private function family_tree_update(): void
    {
        $nr_persons = $this->db_functions->count_persons($this->tree_id);
        $nr_families = $this->db_functions->count_families($this->tree_id);

        $tree_date = date("Y-m-d H:i");
        $sql = "UPDATE humo_trees SET tree_persons='" . $nr_persons . "', tree_families='" . $nr_families . "', tree_date='" . $tree_date . "' WHERE tree_id='" . $this->tree_id . "'";
        $this->dbh->query($sql);
    }

    // *** Add event. $new_event=false/true ***
    // *** COPIED FROM editor_inc.php (double function at this moment) ***
    // $event_date='event_date'
    function add_event($new_event, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text, $multiple_rows = ''): void
    {
        // *** Generate new order number ***
        $event_order = 1;
        if (!$new_event) {
            $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                AND event_connect_kind='" . $event_connect_kind . "'
                AND event_connect_id='" . $event_connect_id . "'
                AND event_kind='" . $event_kind . "'
                ORDER BY event_order DESC LIMIT 0,1";
            $event_qry = $this->dbh->query($event_sql);
            $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
            $event_order = 0;
            if (isset($eventDb->event_order)) {
                $event_order = $eventDb->event_order;
            }
            $event_order++;
        }

        $sql = "INSERT INTO humo_events SET
            event_tree_id='" . $this->tree_id . "',
            event_connect_kind='" . $event_connect_kind . "',
            event_connect_id='" . safe_text_db($event_connect_id) . "',
            event_kind='" . $event_kind . "',
            event_event='" . safe_text_db($event_event) . "',
            event_gedcom='" . safe_text_db($event_gedcom) . "',";
        if ($event_date) {
            $sql .= " event_date='" . $this->editor_cls->date_process($event_date, $multiple_rows) . "',";
        }
        $sql .= " event_place='" . safe_text_db($event_place) . "',
        event_text='" . safe_text_db($event_text) . "',
        event_order='" . $event_order . "',
        event_new_user_id='" . $this->userid . "'";
        $this->dbh->query($sql);
    }

    public function update_note()
    {
        $confirm = '';

        // *** Add editor note ***
        if (isset($_GET['note_add']) && $_GET['note_add']) {
            // *** $note_connect_kind = person or family ***
            $note_connect_kind = 'person';
            if ($_GET['note_add'] == 'family') {
                $note_connect_kind = 'family';
            }

            // *** $note_connect_id = I123 or F123 ***
            $note_connect_id = $this->pers_gedcomnumber;
            if ($note_connect_kind === 'family') {
                $note_connect_id = $this->marriage;
            }

            // *** Name of selected person in family tree ***
            $persDb = $this->db_functions->get_person($this->pers_gedcomnumber);
            // *** Use class to process person ***
            $pers_cls = new PersonCls($persDb);
            $name = $pers_cls->person_name($persDb);
            $note_names = safe_text_db($name["standard_name"]);

            //note_connect_kind='person',
            $sql = "INSERT INTO humo_user_notes SET
                note_new_user_id='" . $this->userid . "',
                note_note='',
                note_kind='editor',
                note_status='Not started',
                note_priority='Normal',
                note_connect_kind='" . $note_connect_kind . "',
                note_connect_id='" . safe_text_db($note_connect_id) . "',
                note_names='" . safe_text_db($note_names) . "',
                note_tree_id='" . $this->tree_id . "'";
            $this->dbh->query($sql);
        }

        // *** Change editor note ***
        if (isset($_POST['note_id'])) {
            foreach ($_POST['note_id'] as $key => $value) {
                $note_id = $_POST["note_id"][$key];
                if (is_numeric($note_id)) {
                    // *** Read old values ***
                    $note_qry = "SELECT * FROM humo_user_notes WHERE note_id='" . $note_id . "'";
                    $note_result = $this->dbh->query($note_qry);
                    $noteDb = $note_result->fetch(PDO::FETCH_OBJ);
                    $note_changed = false;
                    if ($noteDb->note_status != $_POST["note_status"][$key]) {
                        $note_changed = true;
                    }
                    if ($noteDb->note_priority != $_POST["note_priority"][$key]) {
                        $note_changed = true;
                    }
                    if ($noteDb->note_note != $_POST["note_note"][$key]) {
                        $note_changed = true;
                    }

                    if ($note_changed) {
                        $sql = "UPDATE humo_user_notes SET
                            note_note='" . $this->editor_cls->text_process($_POST["note_note"][$key]) . "',
                            note_status='" . $this->editor_cls->text_process($_POST["note_status"][$key]) . "',
                            note_priority='" . $this->editor_cls->text_process($_POST["note_priority"][$key]) . "',
                            note_changed_user_id='" . $this->userid . "'
                            WHERE note_id='" . $note_id . "'";
                        $this->dbh->query($sql);
                    }
                }
            }
        }

        // *** Remove editor note ***
        if (isset($_GET['note_drop']) && is_numeric($_GET['note_drop'])) {
            $confirm .= '<div class="alert alert-danger">';
            $confirm .= __('Are you sure you want to remove this note?');
            $confirm .= ' <form method="post" action="index.php" style="display : inline;">';
            $confirm .= '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
            $confirm .= '<input type="hidden" name="note_drop" value="' . $_GET['note_drop'] . '">';
            $confirm .= ' <input type="submit" name="note_drop2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
            $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
            $confirm .= '</form>';
            $confirm .= '</div>';
        }
        if (isset($_POST['note_drop2']) && is_numeric($_POST['note_drop'])) {
            $sql = "DELETE FROM humo_user_notes WHERE note_id='" . safe_text_db($_POST['note_drop']) . "'";
            $this->dbh->query($sql);
        }

        return $confirm;
    }

    private function cache_latest_changes($force_update = false)
    {
        global $tree_id, $pers_id;
        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='cache_latest_changes' AND setting_tree_id='" . $tree_id . "'");
        $cacheDb = $cacheqry->fetch(PDO::FETCH_OBJ);
        if ($cacheDb) {
            $cache_exists = true;
            $cache_array = explode("|", $cacheDb->setting_value);
            foreach ($cache_array as $cache_line) {
                $cacheDb = json_decode(unserialize($cache_line));

                if (!$force_update) {
                    $pers_id[] = $cacheDb->pers_id;
                }

                $cache_check = true;
                $test_time = time() - 10800; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
                if ($cacheDb->time < $test_time) {
                    $cache_check = false;
                }
            }
        }

        if ($force_update) {
            $cache_check = false;
        }

        if ($cache_check == false) {
            // *** First get pers_id, will be quicker in very large family trees ***
            /*
            $person_qry = "(SELECT pers_id, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='')
                UNION (SELECT pers_id, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL)
                ORDER BY changed_date DESC, changed_time DESC LIMIT 0,15";
            */

            $person_qry = "(SELECT pers_id, pers_changed_datetime as changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NOT NULL)
                UNION (SELECT pers_id, pers_new_datetime AS changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NULL)
                ORDER BY changed_datetime DESC LIMIT 0,15";
            $person_result = $this->dbh->query($person_qry);
            $count_latest_changes = $person_result->rowCount();
            while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                // *** Cache: only use cache if there are > 5.000 persons in database ***
                //if (isset($dataDb->tree_persons) AND $dataDb->tree_persons>5000){
                $person->time = time(); // *** Add linux time to array ***
                if ($cache) $cache .= '|';
                $cache .= serialize(json_encode($person));
                $cache_count++;
                //}
                if (!$force_update) {
                    $pers_id[] = $person->pers_id;
                }
            }

            // *** Add or renew cache in database (only if cache_count is valid) ***
            if ($cache && $cache_count == $count_latest_changes) {
                if ($cache_exists) {
                    //$sql = "UPDATE humo_settings SET setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "' WHERE setting_tree_id='" . safe_text_db($tree_id) . "' AND setting_variable='cache_latest_changes'";

                    // Because of bug found in jan. 2024, remove value from database and insert again.
                    $sql = "DELETE FROM humo_settings WHERE setting_tree_id='" . safe_text_db($tree_id) . "' AND setting_variable='cache_latest_changes'";
                    $this->dbh->query($sql);

                    $sql = "INSERT INTO humo_settings SET
                        setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "', setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $this->dbh->query($sql);
                } else {
                    $sql = "INSERT INTO humo_settings SET
                    setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "', setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $this->dbh->query($sql);
                }
            }
        }
    }
}
