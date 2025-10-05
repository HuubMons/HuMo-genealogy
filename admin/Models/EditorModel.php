<?php

/**
 * July 2023: refactor editor to MVC
 */

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use Genealogy\Include\Ancestors;
use Genealogy\Include\Descendants;
use Genealogy\Include\EventManager;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\ResizePicture;
use Genealogy\Include\SafeTextDb;
use Genealogy\Include\ShowMedia;
use Genealogy\Include\ValidateGedcomnumber;
use PDO;

class EditorModel extends AdminBaseModel
{
    private $tree_prefix, $new_tree = false;
    private $pers_gedcomnumber, $person, $pers_alive = '', $add_person, $search_id, $search_name;
    private $marriage; // TODO check $marriage. Not in use for all $marriage variables yet.
    private $editor_cls, $userid, $safeTextDb;

    public function __construct($admin_config, $tree_prefix, $editor_cls)
    {
        parent::__construct($admin_config);

        $this->tree_prefix = $tree_prefix;
        $this->editor_cls = $editor_cls;

        $this->userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $this->userid = $_SESSION['user_id_admin'];
        }

        $this->safeTextDb = new SafeTextDb();
    }

    // TODO: not used yet (because of other lines to calculate pers_alive status)
    public function set_pers_alive()
    {
        $this->pers_alive = '';
        if (isset($_POST["pers_alive"])) {
            $this->pers_alive = $this->safeTextDb->safe_text_db($_POST["pers_alive"]);
        }
        //if ($_POST["pers_death_date"] || $_POST["pers_death_place"] || $_POST["pers_buried_date"] || $_POST["pers_buried_place"]) {
        //    $this->pers_alive = 'deceased';
        //}
    }

    public function set_pers_gedcomnumber(): void
    {
        $validateGedcomnumber = new ValidateGedcomnumber;
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

        // TODO Doesn't work? Replaced browse links into $_GET.
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
            if ($validateGedcomnumber->validate($_POST["search_id"])) {
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
                WHERE setting_variable='admin_favourite' AND setting_tree_id=:tree_id
                AND pers_tree_id=:tree_id2 LIMIT 0,1";
            $stmt = $this->dbh->prepare($new_nr_qry);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id2', $this->tree_id, PDO::PARAM_STR);
            $new_nr_result = $stmt->execute() ? $stmt : false;
            if ($new_nr_result && $new_nr_result->rowCount()) {
                $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
                $this->pers_gedcomnumber = $new_nr->setting_value;
            } else {
                $new_nr_qry = "SELECT * FROM humo_persons WHERE pers_tree_id = :tree_id LIMIT 0,1";
                $stmt = $this->dbh->prepare($new_nr_qry);
                $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $new_nr_result = $stmt->execute() ? $stmt : false;
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
            if ($validateGedcomnumber->validate($this->pers_gedcomnumber)) {
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
            $this->search_name = $this->safeTextDb->safe_text_db($_POST['search_quicksearch']);
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

        $validateGedcomnumber = new ValidateGedcomnumber;

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
            if ($validateGedcomnumber->validate($this->marriage)) {
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
                $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_tree_id)
                    VALUES (:setting_variable, :setting_value, :setting_tree_id)";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':setting_variable', 'admin_favourite', PDO::PARAM_STR);
                $stmt->bindValue(':setting_value', $this->pers_gedcomnumber, PDO::PARAM_STR);
                $stmt->bindValue(':setting_tree_id', $this->tree_id, PDO::PARAM_STR);
                $stmt->execute();
            } else {
                $sql = "DELETE FROM humo_settings
                    WHERE setting_variable = :setting_variable
                    AND setting_value = :setting_value
                    AND setting_tree_id = :setting_tree_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':setting_variable', 'admin_favourite', PDO::PARAM_STR);
                $stmt->bindValue(':setting_value', $this->pers_gedcomnumber, PDO::PARAM_STR);
                $stmt->bindValue(':setting_tree_id', $this->tree_id, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }

    public function get_favorites($new_tree)
    {
        if ($new_tree == false) {
            // *** Favourites ***
            $fav_qry = "SELECT * FROM humo_settings 
                LEFT JOIN humo_persons ON setting_value = pers_gedcomnumber
                WHERE setting_variable = :setting_variable 
                AND setting_tree_id = :tree_id 
                AND pers_tree_id = :tree_id2";
            $stmt = $this->dbh->prepare($fav_qry);
            $stmt->bindValue(':setting_variable', 'admin_favourite', PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id2', $this->tree_id, PDO::PARAM_STR);
            $stmt->execute();
            $fav_result = $stmt;

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes();

            return $fav_result;
        }
        return false;
    }

    public function update_editor()
    {
        $validateGedcomnumber = new ValidateGedcomnumber;
        $eventManager = new EventManager($this->dbh);

        // *** Return deletion confim box in $confirm variabele ***
        $confirm = '';

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

                $sql = "UPDATE humo_families SET fam_children='" . $fam_children3 . "' WHERE fam_id='" . $famDb->fam_id . "'";
                $this->dbh->query($sql);

                $confirm .= __('Person disconnected from parents.') . '<br>';
            }

            // *** Remove birth, bapt. events etc. ***
            $sql = "DELETE FROM humo_events WHERE person_id='" . $personDb->pers_id . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'
                AND address_connect_sub_kind='person'
                AND address_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_persons WHERE pers_id='" . $personDb->pers_id . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            // *** Added in march 2023 ***
            $sql = "DELETE FROM humo_user_notes WHERE note_tree_id='" . $this->tree_id . "' AND note_connect_id='" . $this->pers_gedcomnumber . "'";
            $this->dbh->query($sql);

            // *** Update cache for list of latest changes ***
            $this->cache_latest_changes(true);

            $confirm .= '<strong>' . __('Person is removed') . '</strong>';

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
        <input type="text" name="add_source_button[]" value="' . $page_source . '" class="form-control form-control-sm">
        <input type="text" name="add_source_button[]" value="' . $page_source . '" class="form-control form-control-sm">
        <input type="text" name="add_source_button[]" value="' . $page_source . '" class="form-control form-control-sm">

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
            $pers_alive = '';
            if (isset($_POST["pers_alive"])) {
                $pers_alive = $this->safeTextDb->safe_text_db($_POST["pers_alive"]);
            }
            // *** Only change alive setting if birth or baptise date is changed ***
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

            $sql = "UPDATE humo_persons SET
                pers_firstname = :pers_firstname,
                pers_prefix = :pers_prefix,
                pers_lastname = :pers_lastname,
                pers_patronym = :pers_patronym,
                pers_name_text = :pers_name_text,
                pers_alive = :pers_alive,
                pers_sexe = :pers_sexe,
                pers_own_code = :pers_own_code,
                pers_text = :pers_text,
                pers_religion = :pers_religion,
                pers_changed_user_id = :userid
                WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :pers_gedcomnumber";

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':pers_firstname', $_POST["pers_firstname"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_prefix', $pers_prefix, PDO::PARAM_STR);
            $stmt->bindValue(':pers_lastname', $_POST["pers_lastname"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_patronym', $_POST["pers_patronym"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_name_text', $this->editor_cls->text_process($_POST["pers_name_text"], true), PDO::PARAM_STR);
            $stmt->bindValue(':pers_alive', $pers_alive, PDO::PARAM_STR);
            $stmt->bindValue(':pers_sexe', $_POST["pers_sexe"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_own_code', $_POST["pers_own_code"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_text', $this->editor_cls->text_process($_POST["person_text"], true), PDO::PARAM_STR);
            $stmt->bindValue(':pers_religion', $_POST["pers_religion"], PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':pers_gedcomnumber', $this->pers_gedcomnumber, PDO::PARAM_STR);
            $stmt->execute();
            $pers_stillborn = '';
            if (isset($_POST["pers_stillborn"])) {
                $pers_stillborn = 'y';
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

            // TODO check this query.
            // TODO: process pers_religion.
            $sql = "UPDATE humo_persons SET
                pers_religion = :pers_religion,";
            if ($pers_cal_date) {
                $sql .= " pers_cal_date = :pers_cal_date,";
            }
            $sql .= "pers_changed_user_id = :userid
                WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :pers_gedcomnumber";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':pers_religion', $_POST["pers_religion"], PDO::PARAM_STR);
            if ($pers_cal_date) {
                $stmt->bindValue(':pers_cal_date', $pers_cal_date, PDO::PARAM_STR);
            }
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':pers_gedcomnumber', $this->pers_gedcomnumber, PDO::PARAM_STR);
            $stmt->execute();

            $update_birth = false;
            if (isset($_POST["pers_birth_event_id"])) {
                // *** Only update event if items are changed ***
                if ($_POST["pers_birth_date_previous"] != $_POST['pers_birth_date_prefix'] . $_POST["pers_birth_date"]) {
                    $update_birth = true;
                }
                if ($_POST["pers_birth_time_previous"] != $_POST["pers_birth_time"]) {
                    $update_birth = true;
                }
                if ($_POST["pers_birth_place_previous"] != $_POST["pers_birth_place"]) {
                    $update_birth = true;
                }
                if ($_POST["pers_birth_text_previous"] != $_POST["pers_birth_text"]) {
                    $update_birth = true;
                }

                // *** Stillborn checkbox ***
                if ($_POST["pers_stillborn_previous"] == 'n' && isset($_POST["pers_stillborn"])) {
                    $update_birth = true;
                }
                if ($_POST["pers_stillborn_previous"] == 'y' && !isset($_POST["pers_stillborn"])) {
                    $update_birth = true;
                }

                // *** Heb. night checkbox ***
                if ($_POST["pers_birth_date_hebnight_previous"] == 'n' && isset($_POST["pers_birth_date_hebnight"])) {
                    $update_birth = true;
                }
                if ($_POST["pers_birth_date_hebnight_previous"] == 'y' && !isset($_POST["pers_birth_date_hebnight"])) {
                    $update_birth = true;
                }
            } elseif (
                // *** Check if items are added, then new event will be added ***
                !empty($_POST["pers_birth_date"]) ||
                !empty($_POST["pers_birth_time"]) ||
                !empty($_POST["pers_birth_place"]) ||
                !empty($_POST["pers_birth_text"]) ||
                !empty($_POST["pers_stillborn"]) ||
                !empty($_POST['pers_birth_date_hebnight'])
            ) {
                $update_birth = true;
            }

            if ($update_birth) {
                //echo 'UPDATE birth!';
                $birth_event = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $this->person->pers_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'event_kind' => 'birth',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_birth_date"),
                    'event_time' => $_POST["pers_birth_time"],
                    'event_place' => $_POST["pers_birth_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_birth_text"], true),
                    'stillborn' => isset($_POST["pers_stillborn"]) ? 'y' : '',
                    'event_date_hebnight' => isset($_POST["pers_birth_date_hebnight"]) ? $_POST["pers_birth_date_hebnight"] : ''
                ];
                if (isset($_POST['pers_birth_event_id'])) {
                    $birth_event['event_id'] = $_POST['pers_birth_event_id'];
                }
                $eventManager->update_event($birth_event);
            }



            $update_baptise = false;
            if (isset($_POST['pers_bapt_event_id'])) {
                if ($_POST["pers_bapt_date_previous"] != $_POST['pers_bapt_date_prefix'] . $_POST["pers_bapt_date"]) {
                    $update_baptise = true;
                }
                if ($_POST["pers_bapt_place_previous"] != $_POST["pers_bapt_place"]) {
                    $update_baptise = true;
                }
                if ($_POST["pers_bapt_text_previous"] != $_POST["pers_bapt_text"]) {
                    $update_baptise = true;
                }
            } elseif (
                !empty($_POST["pers_bapt_date"]) ||
                !empty($_POST["pers_bapt_place"]) ||
                !empty($_POST["pers_bapt_text"])
            ) {
                $update_baptise = true;
            }

            if ($update_baptise) {
                //echo 'UPDATE baptise!';
                $baptise_event = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $this->person->pers_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'event_kind' => 'baptism',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_bapt_date"),
                    'event_place' => $_POST["pers_bapt_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_bapt_text"], true)
                ];
                if (isset($_POST['pers_bapt_event_id'])) {
                    $baptise_event['event_id'] = $_POST['pers_bapt_event_id'];
                }
                $eventManager->update_event($baptise_event);
            }



            // Only update death event if there is a death_date, death_place, death_text, or death_age
            $event_cause = isset($_POST["pers_death_cause2"]) && $_POST["pers_death_cause2"] ? $_POST["pers_death_cause2"] : $_POST["pers_death_cause"];

            $update_death = false;
            if (isset($_POST['pers_death_event_id'])) {
                if ($_POST["pers_death_date_previous"] != $_POST['pers_death_date_prefix'] . $_POST["pers_death_date"]) {
                    $update_death = true;
                }
                if ($_POST["pers_death_time_previous"] != $_POST["pers_death_time"]) {
                    $update_death = true;
                }
                if ($_POST["pers_death_place_previous"] != $_POST["pers_death_place"]) {
                    $update_death = true;
                }
                if ($_POST["pers_death_text_previous"] != $_POST["pers_death_text"]) {
                    $update_death = true;
                }
                if ($_POST["pers_death_cause_previous"] != $event_cause) {
                    $update_death = true;
                }
                if ($_POST["pers_death_age_previous"] != $_POST["pers_death_age"]) {
                    $update_death = true;
                }
                // Heb. night checkbox
                if ($_POST["pers_death_date_hebnight_previous"] == 'n' && isset($_POST["pers_death_date_hebnight"])) {
                    $update_death = true;
                }
                if ($_POST["pers_death_date_hebnight_previous"] == 'y' && !isset($_POST["pers_death_date_hebnight"])) {
                    $update_death = true;
                }
            } elseif (
                !empty($_POST["pers_death_date"]) ||
                !empty($_POST["pers_death_time"]) ||
                !empty($_POST["pers_death_place"]) ||
                !empty($_POST["pers_death_text"]) ||
                !empty($event_cause) ||
                !empty($_POST["pers_death_age"]) ||
                !empty($_POST['pers_death_date_hebnight'])
            ) {
                $update_death = true;
            }

            if ($update_death) {
                //echo 'UPDATE death!';
                $death_event = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $this->person->pers_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'event_kind' => 'death',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_death_date"),
                    'event_time' => $_POST["pers_death_time"],
                    'event_place' => $_POST["pers_death_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_death_text"], true),
                    'cause' => $event_cause,
                    'event_pers_age' => $_POST["pers_death_age"],
                    'event_date_hebnight' => isset($_POST["pers_death_date_hebnight"]) ? $_POST["pers_death_date_hebnight"] : ''
                ];
                if (isset($_POST['pers_death_event_id'])) {
                    $death_event['event_id'] = $_POST['pers_death_event_id'];
                }
                $eventManager->update_event($death_event);
            }



            $update_burial = false;
            if (isset($_POST['pers_buried_event_id'])) {
                if ($_POST["pers_buried_date_previous"] != $_POST['pers_buried_date_prefix'] . $_POST["pers_buried_date"]) {
                    $update_burial = true;
                }
                if ($_POST["pers_buried_place_previous"] != $_POST["pers_buried_place"]) {
                    $update_burial = true;
                }
                if ($_POST["pers_buried_text_previous"] != $_POST["pers_buried_text"]) {
                    $update_burial = true;
                }
                if ($_POST["pers_cremation_previous"] != $_POST["pers_cremation"]) {
                    $update_burial = true;
                }
                // Heb. night checkbox
                if ($_POST["pers_buried_date_hebnight_previous"] == 'n' && isset($_POST["pers_buried_date_hebnight"])) {
                    $update_burial = true;
                }
                if ($_POST["pers_buried_date_hebnight_previous"] == 'y' && !isset($_POST["pers_buried_date_hebnight"])) {
                    $update_burial = true;
                }
            } elseif (
                !empty($_POST["pers_buried_date"]) ||
                !empty($_POST["pers_buried_place"]) ||
                !empty($_POST["pers_buried_text"]) ||
                !empty($_POST["pers_cremation"]) ||
                !empty($_POST['pers_buried_date_hebnight'])
            ) {
                $update_burial = true;
            }

            if ($update_burial) {
                //echo 'UPDATE burial!';
                $burial_event = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $this->person->pers_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'event_kind' => 'burial',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_buried_date"),
                    'event_place' => $_POST["pers_buried_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_buried_text"], true),
                    'cremation' => $_POST["pers_cremation"],
                    'event_date_hebnight' => isset($_POST["pers_buried_date_hebnight"]) ? $_POST["pers_buried_date_hebnight"] : ''
                ];
                if (isset($_POST['pers_buried_event_id'])) {
                    $burial_event['event_id'] = $_POST['pers_buried_event_id'];
                }
                $eventManager->update_event($burial_event);
            }


            // TODO no check for changed data yet
            // *** Add or change birth declaration event ***
            if (
                $_POST['birth_decl_id'] && is_numeric($_POST['birth_decl_id']) ||
                ($_POST['birth_decl_date'] || $_POST['birth_decl_place'] || $_POST['birth_decl_text'])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'person_id' => $this->person->pers_id,
                    'event_kind' => 'birth_declaration',
                    'event_event' => '',
                    'event_gedcom' => 'EVEN',
                    'event_date' => $this->editor_cls->date_process("birth_decl_date"),
                    'event_place' => $_POST["birth_decl_place"],
                    'event_text' => $_POST["birth_decl_text"]
                ];
                if (isset($_POST['birth_decl_id']) && is_numeric($_POST['birth_decl_id'])) {
                    $data['event_id'] = $_POST['birth_decl_id'];
                }
                $eventManager->update_event($data);
            }


            // TODO no check for changed data yet
            // *** Add or change death declaration event ***
            if (
                $_POST['death_decl_id'] && is_numeric($_POST['death_decl_id']) ||
                ($_POST['death_decl_date'] || $_POST['death_decl_place'] || $_POST['death_decl_text'])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $this->pers_gedcomnumber,
                    'person_id' => $this->person->pers_id,
                    'event_kind' => 'death_declaration',
                    'event_event' => '',
                    'event_gedcom' => 'EVEN',
                    'event_date' => $this->editor_cls->date_process("death_decl_date"),
                    'event_place' => $_POST['death_decl_place'],
                    'event_text' => $_POST['death_decl_text']
                ];
                if (isset($_POST['death_decl_id']) && is_numeric($_POST['death_decl_id'])) {
                    $data['event_id'] = $_POST['death_decl_id'];
                }
                $eventManager->update_event($data);
            }

            // Extra UPDATE queries if Hebrew name is displayed in main Name section (and not under name event)
            if ($this->humo_option['admin_hebname'] == "y") {
                if (isset($_POST['even_hebname_id']) && is_numeric($_POST['even_hebname_id']) || $_POST["even_hebname"]) {
                    $data = [
                        'tree_id' => $this->tree_id,
                        'event_connect_kind' => 'person',
                        'event_connect_id' => $this->pers_gedcomnumber,
                        'person_id' => $this->person->pers_id,
                        'event_kind' => 'name',
                        'event_event' => $_POST["even_hebname"],
                        'event_gedcom' => '_HEBN',
                        'event_date' => '',
                        'event_place' => '',
                        'event_text' => ''
                    ];
                    if (isset($_POST['even_hebname_id']) && is_numeric($_POST['even_hebname_id'])) {
                        $data['event_id'] = $_POST['even_hebname_id'];
                    }
                    $eventManager->update_event($data);
                }
            }

            // Extra UPDATE queries if brit mila is displayed 
            // *** Only used for men. So check if $_POST exists.***
            if ($this->humo_option['admin_brit'] == "y" && isset($_POST["even_brit_place"])) {
                if (
                    $_POST['brit_id'] && is_numeric($_POST['brit_id']) ||
                    ($_POST["even_brit_date"] || $_POST["even_brit_place"] || $_POST["even_brit_text"])
                ) {
                    $data = [
                        'tree_id' => $this->tree_id,
                        'event_connect_kind' => 'person',
                        'event_connect_id' => $this->pers_gedcomnumber,
                        'person_id' => $this->person->pers_id,
                        'event_kind' => 'event',
                        'event_event' => '',
                        'event_gedcom' => '_BRTM',
                        'event_date' => $this->editor_cls->date_process("even_brit_date"),
                        'event_place' => $_POST["even_brit_place"],
                        'event_text' => $_POST["even_brit_text"]
                    ];
                    if (isset($_POST['brit_id']) && is_numeric($_POST['brit_id'])) {
                        $data['event_id'] = $_POST['brit_id'];
                    }
                    $eventManager->update_event($data);
                }
            }

            // Extra UPDATE queries if Bar Mitsva is displayed
            if ($this->humo_option['admin_barm'] == "y") {
                if (
                    $_POST['barm_id'] && is_numeric($_POST['barm_id']) ||
                    ($_POST["even_barm_date"] || $_POST["even_barm_place"] || $_POST["even_barm_text"])
                ) {
                    $barmbasm = $_POST["pers_sexe"] == "F" ? "BASM" : "BARM";
                    $data = [
                        'tree_id' => $this->tree_id,
                        'event_connect_kind' => 'person',
                        'event_connect_id' => $this->pers_gedcomnumber,
                        'person_id' => $this->person->pers_id,
                        'event_kind' => 'event',
                        'event_event' => '',
                        'event_gedcom' => $barmbasm,
                        'event_date' => $this->editor_cls->date_process("even_barm_date"),
                        'event_place' => $_POST["even_barm_place"],
                        'event_text' => $_POST["even_barm_text"]
                    ];
                    if (isset($_POST['barm_id']) && is_numeric($_POST['barm_id'])) {
                        $data['event_id'] = $_POST['barm_id'];
                    }
                    $eventManager->update_event($data);
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
            $pers_alive = '';
            if (isset($_POST["pers_alive"])) {
                $pers_alive = $this->safeTextDb->safe_text_db($_POST["pers_alive"]);
            }
            if ($_POST["pers_death_date"] || $_POST["pers_death_place"] || $_POST["pers_buried_date"] || $_POST["pers_buried_place"]) {
                $pers_alive = 'deceased';
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

            $sql = "INSERT INTO humo_persons SET
                pers_tree_id = :tree_id,
                pers_tree_prefix = :tree_prefix,
                pers_famc = '',
                pers_fams = '',
                pers_gedcomnumber = :new_gedcomnumber,
                pers_firstname = :pers_firstname,
                pers_prefix = :pers_prefix,
                pers_lastname = :pers_lastname,
                pers_patronym = :pers_patronym,
                pers_name_text = :pers_name_text,
                pers_alive = :pers_alive,
                pers_sexe = :pers_sexe,
                pers_own_code = :pers_own_code,
                pers_text = :pers_text,
                pers_religion = :pers_religion,";
            if ($pers_cal_date) {
                $sql .= "pers_cal_date = :pers_cal_date,";
            }
            $sql .= "pers_new_user_id = :userid";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':tree_prefix', $this->tree_prefix, PDO::PARAM_STR);
            $stmt->bindValue(':new_gedcomnumber', $new_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':pers_firstname', $_POST["pers_firstname"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_prefix', $pers_prefix, PDO::PARAM_STR);
            $stmt->bindValue(':pers_lastname', $_POST["pers_lastname"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_patronym', $_POST["pers_patronym"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_name_text', $_POST["pers_name_text"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_alive', $pers_alive, PDO::PARAM_STR);
            $stmt->bindValue(':pers_sexe', $_POST["pers_sexe"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_own_code', $_POST["pers_own_code"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_text', $_POST["person_text"], PDO::PARAM_STR);
            $stmt->bindValue(':pers_religion', $_POST["pers_religion"], PDO::PARAM_STR);
            if ($pers_cal_date) {
                $stmt->bindValue(':pers_cal_date', $pers_cal_date, PDO::PARAM_STR);
            }
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

            // Get person_id
            $person_id = $this->dbh->lastInsertId();

            // *** Birth event ***
            $pers_stillborn = '';
            if (isset($_POST["pers_stillborn"])) {
                $pers_stillborn = 'y';
            }
            // Only create birth event if there is a birth_date, birth_time, birth_place, birth_text or pers_stillborn
            if (
                !empty($_POST["pers_birth_date"]) ||
                !empty($_POST["pers_birth_time"]) ||
                !empty($_POST["pers_birth_place"]) ||
                !empty($_POST["pers_birth_text"]) ||
                !empty($pers_stillborn)
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $person_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'birth',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_birth_date"),
                    'event_time' => $_POST["pers_birth_time"],
                    'event_place' => $_POST["pers_birth_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_birth_text"], true),
                    'stillborn' => $pers_stillborn
                ];
                if (isset($_POST["pers_birth_date_hebnight"])) {
                    $data['event_date_hebnight'] = $_POST["pers_birth_date_hebnight"];
                }

                $eventManager->update_event($data);
            }

            // *** Baptise event ***
            if (
                !empty($_POST["pers_bapt_date"]) ||
                !empty($_POST["pers_bapt_place"]) ||
                !empty($_POST["pers_bapt_text"])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $person_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'baptism',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_bapt_date"),
                    'event_place' => $_POST["pers_bapt_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_bapt_text"], true)
                ];
                $eventManager->update_event($data);
            }

            // *** Death event ***
            // Only create death event if there is a death_date, death_place, death_text, death_time, death_cause or death_age
            if (
                !empty($_POST["pers_death_date"]) ||
                !empty($_POST["pers_death_place"]) ||
                !empty($_POST["pers_death_text"]) ||
                !empty($_POST["pers_death_time"]) ||
                !empty($pers_death_cause) ||
                !empty($_POST["pers_death_age"])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $person_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'death',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_death_date"),
                    'event_time' => $_POST["pers_death_time"],
                    'event_place' => $_POST["pers_death_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_death_text"], true),
                    'cause' => $pers_death_cause,
                    'event_pers_age' => $_POST["pers_death_age"]
                ];
                if (isset($_POST["pers_death_date_hebnight"])) {
                    $data['event_date_hebnight'] = $_POST["pers_death_date_hebnight"];
                }

                $eventManager->update_event($data);
            }

            // *** Buried event ***
            // Only create buried event if there is a buried_date, buried_place, buried_time or buried_text
            if (
                !empty($_POST["pers_buried_date"]) ||
                !empty($_POST["pers_buried_place"]) ||
                !empty($_POST["pers_buried_text"])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'person_id' => $person_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'burial',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("pers_buried_date"),
                    'event_place' => $_POST["pers_buried_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["pers_buried_text"], true)
                ];
                if (isset($_POST["pers_buried_date_hebnight"])) {
                    $data['event_date_hebnight'] = $_POST["pers_buried_date_hebnight"];
                }

                $eventManager->update_event($data);
            }

            // *** Save birth declaration (there is no birth declaration when new relation is added) ***
            if (
                isset($_POST['birth_decl_date']) && ($_POST['birth_decl_date'] ||
                    $_POST['birth_decl_place'] ||
                    $_POST['birth_decl_text'])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'person_id' => $person_id,
                    'event_kind' => 'birth_declaration',
                    'event_event' => '',
                    'event_gedcom' => 'EVEN',
                    'event_date' => $_POST['birth_decl_date'],
                    'event_place' => $_POST["birth_decl_place"],
                    'event_text' => $_POST["birth_decl_text"]
                ];
                $eventManager->update_event($data);
            }

            // *** Save death declaration (there is no death declaration when new relation is added) ***
            if (
                isset($_POST['death_decl_date']) &&
                ($_POST['death_decl_date'] || $_POST['death_decl_place'] || $_POST['death_decl_text'])
            ) {
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'person_id' => $person_id,
                    'event_kind' => 'death_declaration',
                    'event_event' => '',
                    'event_gedcom' => 'EVEN',
                    'event_date' => $_POST['death_decl_date'],
                    'event_place' => $_POST["death_decl_place"],
                    'event_text' => $_POST["death_decl_text"]
                ];
                $eventManager->update_event($data);
            }

            // Extra UPDATE queries if brit mila is displayed. Also check even_brit_date to prevent errors if relation is added.
            if ($this->humo_option['admin_brit'] == "y" && isset($_POST["even_brit_date"])) {
                if ($_POST["even_brit_date"] || $_POST["even_brit_place"] || $_POST["even_brit_text"]) {
                    $data = [
                        'tree_id' => $this->tree_id,
                        'event_connect_kind' => 'person',
                        'event_connect_id' => $new_gedcomnumber,
                        'person_id' => $person_id,
                        'event_kind' => 'event',
                        'event_event' => '',
                        'event_gedcom' => '_BRTM',
                        'event_date' => $this->editor_cls->date_process("even_brit_date"),
                        'event_place' => $_POST["even_brit_place"],
                        'event_text' => $_POST["even_brit_text"]
                    ];
                    $eventManager->update_event($data);
                }
            }

            // Extra UPDATE queries if Bar Mitsva is displayed. Also check even_barm_date to prevent errors if relation is added.
            if ($this->humo_option['admin_barm'] == "y" && isset($_POST["even_barm_date"])) {
                if ($_POST["even_barm_date"] || $_POST["even_barm_place"] || $_POST["even_barm_text"]) {
                    $barmbasm = $_POST["pers_sexe"] == "F" ? "BASM" : "BARM";
                    $data = [
                        'tree_id' => $this->tree_id,
                        'event_connect_kind' => 'person',
                        'event_connect_id' => $new_gedcomnumber,
                        'person_id' => $person_id,
                        'event_kind' => 'event',
                        'event_event' => '',
                        'event_gedcom' => $barmbasm,
                        'event_date' => $this->editor_cls->date_process("even_barm_date"),
                        'event_place' => $_POST["even_barm_place"],
                        'event_text' => $_POST["even_barm_text"]
                    ];
                    $eventManager->update_event($data);
                }
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

                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => $event_kind,
                    'event_event' => $_POST['event_event_name_new'],
                    'event_gedcom' => $_POST['event_gedcom_new'],
                    'event_date' => '',
                    'event_place' => '',
                    'event_text' => ''
                ];
                $eventManager->update_event($data);
            }

            // *** New person: add profession ***
            if (isset($_POST["event_profession"]) && $_POST["event_profession"] != "" && $_POST["event_profession"] != "Profession") {
                //$event_date = '';
                $event_place = '';
                if (isset($_POST["event_place_profession"])) {
                    $event_place = $_POST["event_place_profession"];
                }
                $event_text = '';
                if (isset($_POST["event_text_profession"])) {
                    $event_text = $_POST["event_text_profession"];
                }

                // *** Add event ***
                // Add $person_id ??
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'profession',
                    'event_event' => $_POST["event_profession"],
                    'event_gedcom' => '',
                    'event_date' => 'event_date_profession',
                    'event_place' => $event_place,
                    'event_text' => $event_text
                ];
                $eventManager->update_event($data);
            }

            // *** New person: add religion ***
            if (isset($_POST["event_religion"]) && $_POST["event_religion"] != "" && $_POST["event_religion"] != "Religion") {
                $event_place = '';
                if (isset($_POST["event_place_religion"])) {
                    $event_place = $_POST["event_place_religion"];
                }
                $event_text = '';
                if (isset($_POST["event_text_religion"])) {
                    $event_text = $_POST["event_text_religion"];
                }

                // *** Add event ***
                // Add $person_id ??
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $new_gedcomnumber,
                    'event_kind' => 'religion',
                    'event_event' => $_POST["event_religion"],
                    'event_gedcom' => 'RELI',
                    'event_date' => 'event_date_religion',
                    'event_place' => $event_place,
                    'event_text' => $event_text
                ];
                $eventManager->update_event($data);
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
        if (isset($_GET['fam_down']) && is_numeric($_GET['fam_down'])) {
            $child_array_org = explode(";", $this->safeTextDb->safe_text_db($_GET['fam_array']));
            $child_array = $child_array_org;
            $child_array_id = $_GET['fam_down'];
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
                pers_fams = :fams,
                pers_changed_user_id = :userid
                WHERE pers_id = :person_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fams', $fams, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':person_id', $_GET["person_id"], PDO::PARAM_STR);
            $stmt->execute();
        }

        // *** Family move up ***
        if (isset($_GET['fam_up']) && is_numeric($_GET['fam_up'])) {
            $child_array_org = explode(";", $this->safeTextDb->safe_text_db($_GET['fam_array']));
            $child_array = $child_array_org;
            $child_array_id = $_GET['fam_up'] - 1;
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
                pers_fams = :fams,
                pers_changed_user_id = :userid
                WHERE pers_id = :person_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fams', $fams, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':person_id', $_GET["person_id"], PDO::PARAM_STR);
            $stmt->execute();
        }

        // *** Family disconnect ***
        // *** $_POST['fam_remove3'] = gedcomnumber of family to remove ***
        if (isset($_POST['fam_remove2']) && $validateGedcomnumber->validate($_POST['fam_remove3'])) {
            $fam_remove = $_POST['fam_remove3'];

            // *** Remove fams number from man and woman ***
            $new_nr = $this->db_functions->get_family($fam_remove);

            // *** Disconnect ALL children from marriage ***
            if ($new_nr->fam_children) {
                $child_gedcomnumber = explode(";", $new_nr->fam_children);
                foreach ($child_gedcomnumber as $i => $value) {
                    // *** Find child data ***
                    // TODO check line. Could be used for pers_id to improve update query.
                    //$resultDb = $this->db_functions->get_person($child_gedcomnumber[$i]);

                    // *** Remove parents from child record ***
                    $stmt = $this->dbh->prepare(
                        "UPDATE humo_persons SET
                            pers_famc='',
                            pers_changed_user_id=:userid
                            WHERE pers_tree_id=:tree_id AND pers_gedcomnumber=:gedcomnumber"
                    );
                    $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                    $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->bindValue(':gedcomnumber', $child_gedcomnumber[$i], PDO::PARAM_STR);
                    $stmt->execute();
                }
            }

            if (isset($new_nr->fam_man)) {
                $this->fams_remove($new_nr->fam_man, $fam_remove);
            }

            //unset($fams2);
            if (isset($new_nr->fam_woman)) {
                $this->fams_remove($new_nr->fam_woman, $fam_remove);
            }

            $sql = "DELETE FROM humo_events WHERE relation_id='" . $new_nr->fam_id . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "'
                AND address_connect_sub_kind='family' AND address_connect_id='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_families WHERE fam_id='" . $new_nr->fam_id . "'";
            $this->dbh->query($sql);

            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_connect_id='" . $fam_remove . "'";
            $this->dbh->query($sql);

            $this->family_tree_update();

            $confirm .= '<div class="alert alert-success">';
            $confirm .= __('Marriage is removed!');
            $confirm .= '</div>';

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

            $sql = "INSERT INTO humo_families (
                fam_gedcomnumber, fam_tree_id, fam_kind, fam_man, fam_woman, fam_children,
                fam_religion,
                fam_text, fam_new_user_id
            ) VALUES (
                :fam_gedcomnumber, :fam_tree_id, '', :fam_man, :fam_woman, :fam_children,
                '',
                '', :fam_new_user_id
            )";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':fam_man', $man_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman', $woman_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_children', $this->pers_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_new_user_id', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

            // *** Add father ***
            //pers_callname='',
            $pers_alive1 = '';
            if (isset($_POST['pers_alive1'])) {
                $pers_alive1 = $this->safeTextDb->safe_text_db($_POST['pers_alive1']);
            }
            $pers_sexe1 = '';
            if (isset($_POST['pers_sexe1'])) {
                $pers_sexe1 = $this->safeTextDb->safe_text_db($_POST['pers_sexe1']);
            }
            $stmt = $this->dbh->prepare(
                "INSERT INTO humo_persons SET
                    pers_gedcomnumber = :man_gedcomnumber,
                    pers_tree_id = :tree_id,
                    pers_tree_prefix = :tree_prefix,
                    pers_famc = '',
                    pers_fams = :fam_gedcomnumber,
                    pers_firstname = :pers_firstname1,
                    pers_prefix = :pers_prefix1,
                    pers_lastname = :pers_lastname1,
                    pers_patronym = :pers_patronym1,
                    pers_name_text = '',
                    pers_alive = :pers_alive1,
                    pers_sexe = :pers_sexe1,
                    pers_own_code = '',
                    pers_text = '',
                    pers_religion = '',
                    pers_new_user_id = :userid"
            );
            $stmt->bindValue(':man_gedcomnumber', $man_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':tree_prefix', $this->tree_prefix, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':pers_firstname1', $_POST['pers_firstname1'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_prefix1', $_POST['pers_prefix1'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_lastname1', $_POST['pers_lastname1'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_patronym1', $_POST['pers_patronym1'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_alive1', $pers_alive1, PDO::PARAM_STR);
            $stmt->bindValue(':pers_sexe1', $pers_sexe1, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

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

                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $man_gedcomnumber,
                    'event_kind' => $event_kind,
                    'event_event' => $_POST['event_event_name1'],
                    'event_gedcom' => $_POST['event_gedcom_add1'],
                    'event_date' => '',
                    'event_place' => '',
                    'event_text' => ''
                ];
                $eventManager->update_event($data);
            }

            // *** Add profession ***
            if (isset($_POST["event_profession1"]) && $_POST["event_profession1"] != "" && $_POST["event_profession1"] != "Profession") {
                $event_place = '';
                if (isset($_POST["event_place_profession1"])) {
                    $event_place = $_POST["event_place_profession1"];
                }
                $event_text = '';
                if (isset($_POST["event_text_profession1"])) {
                    $event_text = $_POST["event_text_profession1"];
                }

                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $man_gedcomnumber,
                    'event_kind' => 'profession',
                    'event_event' => $_POST["event_profession1"],
                    'event_gedcom' => '',
                    'event_date' => '',
                    'event_place' => $event_place,
                    'event_text' => $event_text
                ];
                $eventManager->update_event($data);
            }

            // *** Add mother ***
            //pers_callname='',
            $pers_alive2 = '';
            if (isset($_POST['pers_alive2'])) {
                $pers_alive2 = $this->safeTextDb->safe_text_db($_POST['pers_alive2']);
            }
            $pers_sexe2 = '';
            if (isset($_POST['pers_sexe2'])) {
                $pers_sexe2 = $this->safeTextDb->safe_text_db($_POST['pers_sexe2']);
            }
            $stmt = $this->dbh->prepare(
                "INSERT INTO humo_persons SET
                    pers_gedcomnumber = :woman_gedcomnumber,
                    pers_tree_id = :tree_id,
                    pers_tree_prefix = :tree_prefix,
                    pers_famc = '',
                    pers_fams = :fam_gedcomnumber,
                    pers_firstname = :pers_firstname2,
                    pers_prefix = :pers_prefix2,
                    pers_lastname = :pers_lastname2,
                    pers_patronym = :pers_patronym2,
                    pers_name_text = '',
                    pers_alive = :pers_alive2,
                    pers_sexe = :pers_sexe2,
                    pers_own_code = '',
                    pers_text = '',
                    pers_religion = '',
                    pers_new_user_id = :userid"
            );
            $stmt->bindValue(':woman_gedcomnumber', $woman_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':tree_prefix', $this->tree_prefix, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':pers_firstname2', $_POST['pers_firstname2'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_prefix2', $_POST['pers_prefix2'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_lastname2', $_POST['pers_lastname2'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_patronym2', $_POST['pers_patronym2'], PDO::PARAM_STR);
            $stmt->bindValue(':pers_alive2', $pers_alive2, PDO::PARAM_STR);
            $stmt->bindValue(':pers_sexe2', $pers_sexe2, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

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

                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $woman_gedcomnumber,
                    'event_kind' => $event_kind,
                    'event_event' => $_POST['event_event_name2'],
                    'event_gedcom' => $_POST['event_gedcom_add2'],
                    'event_date' => '',
                    'event_place' => '',
                    'event_text' => ''
                ];
                $eventManager->update_event($data);
            }

            // *** Add profession ***
            if (isset($_POST["event_profession2"]) && $_POST["event_profession2"] != "" && $_POST["event_profession2"] != "Profession") {
                $event_place = '';
                if (isset($_POST["event_place_profession2"])) {
                    $event_place = $_POST["event_place_profession2"];
                }
                $event_text = '';
                if (isset($_POST["event_text_profession2"])) {
                    $event_text = $_POST["event_text_profession2"];
                }

                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'person',
                    'event_connect_id' => $woman_gedcomnumber,
                    'event_kind' => 'profession',
                    'event_event' => $_POST["event_profession2"],
                    'event_gedcom' => '',
                    'event_date' => '',
                    'event_place' => $event_place,
                    'event_text' => $event_text
                ];
                $eventManager->update_event($data);
            }

            // *** Add parents to child record ***
            $stmt = $this->dbh->prepare(
                "UPDATE humo_persons SET
                    pers_famc = :fam_gedcomnumber,
                    pers_changed_user_id = :userid
                    WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :pers_gedcomnumber"
            );
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':pers_gedcomnumber', $this->pers_gedcomnumber, PDO::PARAM_STR);
            $stmt->execute();

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
                    fam_children = :fam_children,
                    fam_changed_user_id = :userid
                    WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :fam_gedcomnumber";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':fam_children', $fam_children, PDO::PARAM_STR);
                $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $stmt->bindValue(':fam_gedcomnumber', $parentsDb->fam_gedcomnumber, PDO::PARAM_STR);
                $stmt->execute();

                // *** Add parents to child record ***
                $stmt = $this->dbh->prepare(
                    "UPDATE humo_persons SET
                        pers_famc = :fam_gedcomnumber,
                        pers_changed_user_id = :userid
                        WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :pers_gedcomnumber"
                );
                $stmt->bindValue(':fam_gedcomnumber', $parentsDb->fam_gedcomnumber, PDO::PARAM_STR);
                $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $stmt->bindValue(':pers_gedcomnumber', $this->pers_gedcomnumber, PDO::PARAM_STR);
                $stmt->execute();

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
                    $confirm .= ' <form method="post" action="index.php?page=editor&amp;menu_tab=marriage" style="display : inline;">';
                    $confirm .= '<input type="hidden" name="family_id" value="' . $_POST['family_id'] . '">';
                    $confirm .= '<input type="hidden" name="children" value="' . $_POST['children'] . '">';
                    $confirm .= '<input type="hidden" name="child_connect2" value="' . $_POST['child_connect2'] . '">';
                    $confirm .= ' <input type="submit" name="child_connecting" value="' . __('Yes') . '" class="btn btn-sm btn-danger">';
                    $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" class="btn btn-sm btn-success ms-3">';
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
                            fam_children = :fam_children,
                            fam_changed_user_id = :userid
                            WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :fam_gedcomnumber";
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->bindValue(':fam_children', $_POST["children"] . ';' . $_POST["child_connect2"], PDO::PARAM_STR);
                        $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                        $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                        $stmt->bindValue(':fam_gedcomnumber', $_POST['family_id'], PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        $sql = "UPDATE humo_families SET
                            fam_children = :fam_children,
                            fam_changed_user_id = :userid
                            WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :fam_gedcomnumber";
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->bindValue(':fam_children', $_POST["child_connect2"], PDO::PARAM_STR);
                        $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                        $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                        $stmt->bindValue(':fam_gedcomnumber', $_POST['family_id'], PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    // *** Add parents to child record ***
                    $stmt = $this->dbh->prepare(
                        "UPDATE humo_persons SET
                            pers_famc = :family_id,
                            pers_changed_user_id = :userid
                            WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :child_gedcomnumber"
                    );
                    $stmt->bindValue(':family_id', $_POST['family_id'], PDO::PARAM_STR);
                    $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                    $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->bindValue(':child_gedcomnumber', $_POST["child_connect2"], PDO::PARAM_STR);
                    $stmt->execute();

                    $this->family_tree_update();
                }
            }
        }

        // *** Disconnect child ***
        if (isset($_POST['child_disconnecting'])) {
            $sql = "UPDATE humo_families SET
                fam_children = :fam_children,
                fam_changed_user_id = :userid
                WHERE fam_id = :fam_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fam_children', $_POST["child_disconnect2"], PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':fam_id', $_POST["family_id"], PDO::PARAM_STR);
            $stmt->execute();

            // *** Remove parents from child record ***
            $sql = "UPDATE humo_persons SET
                pers_famc = :famc,
                pers_changed_user_id = :userid
                WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :gedcomnumber";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':famc', '', PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':gedcomnumber', $_POST["child_disconnect_gedcom"], PDO::PARAM_STR);
            $stmt->execute();
        }

        /**
         * PROCESS DATA FAMILY
         */

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

            $stmt = $this->dbh->prepare(
                "INSERT INTO humo_families SET
                    fam_tree_id = :tree_id,
                    fam_gedcomnumber = :fam_gedcomnumber,
                    fam_kind = '',
                    fam_man = :fam_man,
                    fam_woman = :fam_woman,
                    fam_children = '',
                    fam_religion = '',
                    fam_text = '',
                    fam_new_user_id = :userid"
            );
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_man', $man_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman', $woman_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

            // *** Update famc and fams for new added partner ***

            // *** Add marriage to person ***
            $this->fams_add($partner_gedcomnumber, $fam_gedcomnumber);

            // *** Add marriage to person ***
            $this->fams_add($relation_gedcomnumber, $fam_gedcomnumber);

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

            $stmt = $this->dbh->prepare(
                "INSERT INTO humo_families SET
                    fam_tree_id = :tree_id,
                    fam_gedcomnumber = :fam_gedcomnumber,
                    fam_kind = '',
                    fam_man = :fam_man,
                    fam_woman = :fam_woman,
                    fam_children = '',
                    fam_religion = '',
                    fam_text = '',
                    fam_new_user_id = :userid"
            );
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $fam_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_man', $man_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman', $woman_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->execute();

            // *** Add marriage to person records MAN and WOMAN ***
            $this->fams_add($man_gedcomnumber, $fam_gedcomnumber);
            $this->fams_add($woman_gedcomnumber, $fam_gedcomnumber);

            $this->family_tree_update();
        }

        // *** Switch parents ***
        if (isset($_POST['parents_switch'])) {
            $sql = "UPDATE humo_families SET
                fam_man = :fam_man,
                fam_woman = :fam_woman
                WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :fam_gedcomnumber";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fam_man', $_POST["connect_woman"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman', $_POST["connect_man"], PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $_POST['marriage'], PDO::PARAM_STR);
            $stmt->execute();

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
                fam_kind = :fam_kind,
                fam_man = :fam_man,
                fam_woman = :fam_woman,
                fam_man_age = :fam_man_age,
                fam_woman_age = :fam_woman_age,
                fam_religion = :fam_religion,
                fam_text = :fam_text,
                fam_changed_user_id = :fam_changed_user_id
                WHERE fam_tree_id = :fam_tree_id AND fam_gedcomnumber = :fam_gedcomnumber";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fam_kind', $_POST["fam_kind"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_man', $_POST["connect_man"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman', $_POST["connect_woman"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_man_age', $_POST["fam_man_age"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_woman_age', $_POST["fam_woman_age"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_religion', $_POST["fam_religion"], PDO::PARAM_STR);
            $stmt->bindValue(':fam_text', $this->editor_cls->text_process($_POST["fam_text"], true), PDO::PARAM_STR);
            $stmt->bindValue(':fam_changed_user_id', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':fam_tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':fam_gedcomnumber', $_POST['marriage'], PDO::PARAM_STR);
            $stmt->execute();

            $update_relation = false;
            if (isset($_POST['fam_relation_event_id'])) {
                if ($_POST["fam_relation_date_previous"] != $_POST['fam_relation_date_prefix'] . $_POST["fam_relation_date"]) {
                    $update_relation = true;
                }
                if ($_POST["fam_relation_end_date_previous"] != $_POST['fam_relation_end_date_prefix'] . $_POST["fam_relation_end_date"]) {
                    $update_relation = true;
                }
                if ($_POST["fam_relation_place_previous"] != $_POST["fam_relation_place"]) {
                    $update_relation = true;
                }
                if ($_POST["fam_relation_text_previous"] != $_POST["fam_relation_text"]) {
                    $update_relation = true;
                }
            } elseif (
                !empty($_POST["fam_relation_date"]) ||
                !empty($_POST["fam_relation_place"]) ||
                !empty($_POST["fam_relation_text"])
            ) {
                $update_relation = true;
            }

            if ($update_relation) {
                //echo "Updating relation event...<br>";
                $relation_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'relation',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_relation_date"),
                    'event_end_date' => $this->editor_cls->date_process("fam_relation_end_date"),
                    'event_place' => $_POST["fam_relation_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["fam_relation_text"], true)
                ];
                if (isset($_POST['fam_relation_event_id'])) {
                    $relation_event['event_id'] = $_POST['fam_relation_event_id'];
                }
                $eventManager->update_event($relation_event);
            }

            $update_fam_marr_notice = false;
            if (isset($_POST['fam_marr_notice_event_id'])) {
                if ($_POST["fam_marr_notice_date_previous"] != $_POST['fam_marr_notice_date_prefix'] . $_POST["fam_marr_notice_date"]) {
                    $update_fam_marr_notice = true;
                }
                if ($_POST["fam_marr_notice_date_hebnight_previous"] == 'n' && isset($_POST["fam_marr_notice_date_hebnight"])) {
                    $update_fam_marr_notice = true;
                }
                if ($_POST["fam_marr_notice_date_hebnight_previous"] == 'y' && !isset($_POST["fam_marr_notice_date_hebnight"])) {
                    $update_fam_marr_notice = true;
                }
                if ($_POST["fam_marr_notice_place_previous"] != $_POST["fam_marr_notice_place"]) {
                    $update_fam_marr_notice = true;
                }
                if ($_POST["fam_marr_notice_text_previous"] != $_POST["fam_marr_notice_text"]) {
                    $update_fam_marr_notice = true;
                }
            } elseif (
                !empty($_POST["fam_marr_notice_date"]) ||
                !empty($_POST["fam_marr_notice_place"]) ||
                !empty($_POST["fam_marr_notice_text"])
            ) {
                $update_fam_marr_notice = true;
            }

            if ($update_fam_marr_notice) {
                //echo "Updating marriage notice event...<br>";
                $marr_notice_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'marriage_notice',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_marr_notice_date"),
                    'event_place' => $_POST["fam_marr_notice_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["fam_marr_notice_text"], true)
                ];

                if (isset($_POST['fam_marr_notice_event_id'])) {
                    $marr_notice_event['event_id'] = $_POST['fam_marr_notice_event_id'];
                }
                if (isset($_POST["fam_marr_notice_date_hebnight"])) {
                    $marr_notice_event['event_date_hebnight'] = $_POST["fam_marr_notice_date_hebnight"];
                }
                $eventManager->update_event($marr_notice_event);
            }


            $update_marriage = false;
            if (isset($_POST['fam_marr_event_id'])) {
                if ($_POST["fam_marr_date_previous"] != $_POST['fam_marr_date_prefix'] . $_POST["fam_marr_date"]) {
                    $update_marriage = true;
                }
                if ($_POST["fam_marr_date_hebnight_previous"] == 'n' && isset($_POST["fam_marr_date_hebnight"])) {
                    $update_marriage = true;
                }
                if ($_POST["fam_marr_date_hebnight_previous"] == 'y' && !isset($_POST["fam_marr_date_hebnight"])) {
                    $update_marriage = true;
                }
                if ($_POST["fam_marr_place_previous"] != $_POST["fam_marr_place"]) {
                    $update_marriage = true;
                }
                if ($_POST["fam_marr_text_previous"] != $_POST["fam_marr_text"]) {
                    $update_marriage = true;
                }
            } elseif (
                !empty($_POST["fam_marr_date"]) ||
                !empty($_POST["fam_marr_place"]) ||
                !empty($_POST["fam_marr_text"]) ||
                !empty($_POST["fam_marr_authority"])
            ) {
                $update_marriage = true;
            }

            if ($update_marriage) {
                //echo "Updating marriage event...<br>";
                $marr_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'marriage',
                    'event_event' => '',
                    'authority' => $_POST["fam_marr_authority"],
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_marr_date"),
                    'event_place' => $_POST["fam_marr_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["fam_marr_text"], true)
                ];
                if (isset($_POST['fam_marr_event_id'])) {
                    $marr_event['event_id'] = $_POST['fam_marr_event_id'];
                }
                if (isset($_POST["fam_marr_date_hebnight"])) {
                    $marr_event['event_date_hebnight'] = $_POST["fam_marr_date_hebnight"];
                }
                $eventManager->update_event($marr_event);
            }


            $update_marr_church_notice = false;
            if (isset($_POST['fam_marr_church_notice_event_id'])) {
                if ($_POST["fam_marr_church_notice_date_previous"] != $_POST['fam_marr_church_notice_date_prefix'] . $_POST["fam_marr_church_notice_date"]) {
                    $update_marr_church_notice = true;
                }
                if ($_POST["fam_marr_church_notice_date_hebnight_previous"] == 'n' && isset($_POST["fam_marr_church_notice_date_hebnight"])) {
                    $update_marr_church_notice = true;
                }
                if ($_POST["fam_marr_church_notice_date_hebnight_previous"] == 'y' && !isset($_POST["fam_marr_church_notice_date_hebnight"])) {
                    $update_marr_church_notice = true;
                }
                if ($_POST["fam_marr_church_notice_place_previous"] != $_POST["fam_marr_church_notice_place"]) {
                    $update_marr_church_notice = true;
                }
                if ($_POST["fam_marr_church_notice_text_previous"] != $_POST["fam_marr_church_notice_text"]) {
                    $update_marr_church_notice = true;
                }
            } elseif (
                !empty($_POST["fam_marr_church_notice_date"]) ||
                !empty($_POST["fam_marr_church_notice_place"]) ||
                !empty($_POST["fam_marr_church_notice_text"])
            ) {
                $update_marr_church_notice = true;
            }

            if ($update_marr_church_notice) {
                echo "Updating marriage church notice event...<br>";
                $marr_church_notice_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'marr_church_notice',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_marr_church_notice_date"),
                    'event_place' => $_POST["fam_marr_church_notice_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["fam_marr_church_notice_text"], true)
                ];
                if (isset($_POST['fam_marr_church_notice_event_id'])) {
                    $marr_church_notice_event['event_id'] = $_POST['fam_marr_church_notice_event_id'];
                }
                if (isset($_POST["fam_marr_church_notice_date_hebnight"])) {
                    $marr_church_notice_event['event_date_hebnight'] = $_POST["fam_marr_church_notice_date_hebnight"];
                }
                $eventManager->update_event($marr_church_notice_event);
            }


            $update_marr_church = false;
            if (isset($_POST['fam_marr_church_event_id'])) {
                if ($_POST["fam_marr_church_date_previous"] != $_POST['fam_marr_church_date_prefix'] . $_POST["fam_marr_church_date"]) {
                    $update_marr_church = true;
                }
                if ($_POST["fam_marr_church_date_hebnight_previous"] == 'n' && isset($_POST["fam_marr_church_date_hebnight"])) {
                    $update_marr_church = true;
                }
                if ($_POST["fam_marr_church_date_hebnight_previous"] == 'y' && !isset($_POST["fam_marr_church_date_hebnight"])) {
                    $update_marr_church = true;
                }
                if ($_POST["fam_marr_church_place_previous"] != $_POST["fam_marr_church_place"]) {
                    $update_marr_church = true;
                }
                if ($_POST["fam_marr_church_text_previous"] != $_POST["fam_marr_church_text"]) {
                    $update_marr_church = true;
                }
            } elseif (
                !empty($_POST["fam_marr_church_date"]) ||
                !empty($_POST["fam_marr_church_place"]) ||
                !empty($_POST["fam_marr_church_text"])
            ) {
                $update_marr_church = true;
            }

            if ($update_marr_church) {
                //echo "Updating marriage church event...<br>";
                $marr_church_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'marr_church',
                    'event_event' => '',
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_marr_church_date"),
                    'event_place' => $_POST["fam_marr_church_place"],
                    'event_text' => $this->editor_cls->text_process($_POST["fam_marr_church_text"], true)
                ];
                if (isset($_POST['fam_marr_church_event_id'])) {
                    $marr_church_event['event_id'] = $_POST['fam_marr_church_event_id'];
                }
                if (isset($_POST["fam_marr_church_date_hebnight"])) {
                    $marr_church_event['event_date_hebnight'] = $_POST["fam_marr_church_date_hebnight"];
                }
                $eventManager->update_event($marr_church_event);
            }


            $update_divorce = false;
            if (isset($_POST['fam_div_event_id'])) {
                if ($_POST["fam_div_date_previous"] != $_POST['fam_div_date_prefix'] . $_POST["fam_div_date"]) {
                    $update_divorce = true;
                }
                if ($_POST["fam_div_place_previous"] != $_POST["fam_div_place"]) {
                    $update_divorce = true;
                }
                if ($_POST["fam_div_text_previous"] != $fam_div_text) {
                    $update_divorce = true;
                }
                if ($_POST["fam_div_authority_previous"] != $_POST["fam_div_authority"]) {
                    $update_divorce = true;
                }
            } elseif (
                !empty($_POST["fam_div_date"]) ||
                !empty($_POST["fam_div_place"]) ||
                !empty($fam_div_text) ||
                !empty($_POST["fam_div_authority"])
            ) {
                $update_divorce = true;
            }

            if ($update_divorce) {
                echo "Updating divorce event...<br>";
                $div_event = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => 'family',
                    'event_connect_id' => $_POST['marriage'],
                    'event_kind' => 'divorce',
                    'event_event' => '',
                    'authority' => $_POST["fam_div_authority"],
                    'event_gedcom' => '',
                    'event_date' => $this->editor_cls->date_process("fam_div_date"),
                    'event_place' => $_POST["fam_div_place"],
                    'event_text' => $this->editor_cls->text_process($fam_div_text, true)
                ];
                if (isset($_POST['fam_div_event_id'])) {
                    $div_event['event_id'] = $_POST['fam_div_event_id'];
                }
                $eventManager->update_event($div_event);
            }

            $this->family_tree_update();
        }
        return $confirm;
    }


    // TODO: rename to process_events?
    // This update_editor2 part is also used for sources (editor_sources.php) and some other pages.
    public function update_editor2()
    {
        global $page;

        $ancestors = new Ancestors();
        $descendants = new Descendants();
        $resizePicture = new ResizePicture();
        $eventManager = new EventManager($this->dbh);

        // TODO refactor $marriage.
        if (isset($_SESSION['admin_fam_gedcomnumber'])) {
            $marriage = $_SESSION['admin_fam_gedcomnumber'];
        }

        // *** Return deletion confim box in $confirm variabele ***
        $confirm = '';

        /**
         * PROCESS DATA EVENT
         */

        // *** Add new event ***
        $new_event = false;
        if (!isset($_GET['add_person'])) {
            if (isset($_GET['event_add'])) {
                $new_event = true;
                $event_add = $_GET['event_add'];
            }

            // *** Add Nickname ***
            if (isset($_POST['event_add_name'])) {
                $new_event = true;
                $event_add = 'add_name';
            }
            // *** If "Save" is clicked, also save event names ***
            if (isset($_POST['event_event_name']) && $_POST['event_event_name'] != '') {
                $new_event = true;
                $event_add = 'add_name';
            }

            // *** April 2023: using POST so it's possible to save person data if event is added ***
            if (isset($_POST['add_birth_declaration'])) {
                $new_event = true;
                $event_add = 'add_birth_declaration';
            }
            if (isset($_POST['add_baptism_witness'])) {
                $new_event = true;
                $event_add = 'add_baptism_witness';
            }
            if (isset($_POST['add_death_declaration'])) {
                $new_event = true;
                $event_add = 'add_death_declaration';
            }
            if (isset($_POST['add_burial_witness'])) {
                $new_event = true;
                $event_add = 'add_burial_witness';
            }
            if (isset($_POST['add_marriage_witness'])) {
                $new_event = true;
                $event_add = 'add_marriage_witness';
            }
            if (isset($_POST['add_marriage_witness_rel'])) {
                $new_event = true;
                $event_add = 'add_marriage_witness_rel';
            }

            // *** Add profession ***
            if (isset($_POST['event_add_profession'])) {
                $new_event = true;
                $event_add = 'add_profession';
            }
            // *** If "Save" is clicked, also save event names ***
            if (isset($_POST['event_event_profession']) && $_POST['event_event_profession'] != '') {
                $new_event = true;
                $event_add = 'add_profession';
            }

            // *** Add religion ***
            if (isset($_POST['event_add_religion'])) {
                $new_event = true;
                $event_add = 'add_religion';
            }
            // *** If "Save" is clicked, also save event names ***
            if (isset($_POST['event_event_religion']) && $_POST['event_event_religion'] != '') {
                $new_event = true;
                $event_add = 'add_religion';
            }

            // *** Add picture ***
            if (isset($_POST['add_picture'])) {
                $new_event = true;
                $event_add = 'add_picture';
            }
            if (isset($_POST['add_marriage_picture'])) {
                $new_event = true;
                $event_add = 'add_marriage_picture';
            }
            if (isset($_POST['add_source_picture'])) {
                $new_event = true;
                $event_add = 'add_source_picture';
            }
        }

        if ($new_event) {
            $event_person_id = '';
            $event_relation_id = '';
            if (isset($_POST['marriage'])) {
                $marriage = $_POST['marriage'];

                $fam_stmt = $this->dbh->prepare("SELECT fam_id FROM humo_families WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :marriage LIMIT 1");
                $fam_stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $fam_stmt->bindValue(':marriage', $marriage, PDO::PARAM_STR);
                $fam_stmt->execute();
                $fam_row = $fam_stmt->fetch(PDO::FETCH_OBJ);
                if ($fam_row) {
                    $event_relation_id = $fam_row->fam_id;
                }
            } // *** Needed to check $_POST for multiple relations ***

            if ($event_add == 'add_name') {
                $event_connect_kind = 'person';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'name';

                if ($_POST['event_gedcom_add'] == 'NPFX') {
                    $event_kind = 'NPFX';
                }
                if ($_POST['event_gedcom_add'] == 'NSFX') {
                    $event_kind = 'NSFX';
                }
                if ($_POST['event_gedcom_add'] == 'nobility') {
                    $event_kind = 'nobility';
                }
                if ($_POST['event_gedcom_add'] == 'title') {
                    $event_kind = 'title';
                }
                if ($_POST['event_gedcom_add'] == 'lordship') {
                    $event_kind = 'lordship';
                }

                $event_event = $_POST['event_event_name'];
                $event_gedcom = $_POST['event_gedcom_add'];
            }

            if ($event_add == 'add_birth_declaration') {
                $event_connect_kind = 'birth_declaration';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = '';
            }
            if ($event_add == 'add_baptism_witness') {
                $event_connect_kind = 'CHR';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = 'WITN';
            }
            if ($event_add == 'add_death_declaration') {
                $event_connect_kind = 'death_declaration';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = '';
            }
            if ($event_add == 'add_burial_witness') {
                $event_connect_kind = 'BURI';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = 'WITN';
            }
            if ($event_add == 'add_marriage_witness') {
                $event_connect_kind = 'MARR';
                $event_connect_id = $marriage;
                $event_relation_id = $event_relation_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = 'WITN';
            }
            if ($event_add == 'add_marriage_witness_rel') {
                $event_connect_kind = 'MARR_REL';
                $event_connect_id = $marriage;
                $event_relation_id = $event_relation_id;
                $event_kind = 'ASSO';
                $event_event = '';
                $event_gedcom = 'WITN';
            }

            if ($event_add == 'add_profession') {
                $event_connect_kind = 'person';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'profession';
                $event_gedcom = '';
                $event_event = $_POST['event_event_profession'];
            }

            if ($event_add == 'add_religion') {
                $event_connect_kind = 'person';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'religion';
                $event_gedcom = 'RELI';
                $event_event = $_POST['event_event_religion'];
            }

            // *** Picture by person ***
            if ($event_add == 'add_picture') {
                $event_connect_kind = 'person';
                $event_connect_id = $this->pers_gedcomnumber;
                $event_person_id = $this->person->pers_id;
                $event_kind = 'picture';
                $event_event = '';
                $event_gedcom = '';
            }
            // *** Picture by relation ***
            if ($event_add == 'add_marriage_picture') {
                $event_connect_kind = 'family';
                $event_connect_id = $marriage;
                $event_relation_id = $event_relation_id;
                $event_kind = 'picture';
                $event_event = '';
                $event_gedcom = '';
            }
            // *** Picture by source ***
            if ($event_add == 'add_source_picture') {
                $event_connect_kind = 'source';
                $event_connect_id = $_POST['source_gedcomnr'];
                $event_kind = 'picture';
                $event_event = '';
                $event_gedcom = '';
            }

            // *** Add event ***
            // Example: add picture to source in source editor.
            $data = [
                'tree_id' => $this->tree_id,
                'event_connect_kind' => $event_connect_kind,
                'event_connect_id' => $event_connect_id,
                'event_kind' => $event_kind,
                'event_event' => $event_event,
                'event_gedcom' => $event_gedcom,
                'event_date' => '',
                'event_place' => '',
                'event_text' => ''
            ];
            if ($event_person_id) {
                $data['person_id'] = $event_person_id;
            }
            if ($event_relation_id) {
                $data['relation_id'] = $event_relation_id;
            }
            $eventManager->update_event($data);
        }

        if (isset($_POST['person_event_add'])) {
            // *** Add person event ***
            $data = [
                'tree_id' => $this->tree_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $this->pers_gedcomnumber,
                'event_kind' => $_POST['event_kind'],
                'event_event' => '',
                'event_gedcom' => '',
                'event_date' => '',
                'event_place' => '',
                'event_text' => ''
            ];
            $eventManager->update_event($data);
        } elseif (isset($_POST['marriage_event_add'])) {
            // *** Add marriage event ***
            $data = [
                'tree_id' => $this->tree_id,
                'event_connect_kind' => 'family',
                'event_connect_id' => $_POST['marriage'],
                'event_kind' => $_POST["event_kind"],
                'event_event' => '',
                'event_gedcom' => '',
                'event_date' => '',
                'event_place' => '',
                'event_text' => ''
            ];
            $eventManager->update_event($data);
        }

        // *** Upload images ***
        if (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['name']) {
            $showMedia = new showMedia();

            // *** get path of pictures folder 
            $familytrees = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='" . $this->tree_prefix . "'");
            $familytree = $familytrees->fetch(PDO::FETCH_OBJ);
            $tree_pict_path = $familytree->tree_pict_path;
            if (substr($tree_pict_path, 0, 1) === '|') {
                $tree_pict_path = 'media/';
            }
            $dir = '../' . $tree_pict_path;

            $safepath = '';
            $selected_subdir = preg_replace("/[\/\\\\]/", '',  $_POST['select_media_folder']); // remove all / and \ 
            if (array_key_exists(substr($_FILES['photo_upload']['name'], 0, 3), $showMedia->pcat_dirs)) {
                // old suffix style categories
                $dir .= substr($_FILES['photo_upload']['name'], 0, 2) . '/';
            } elseif (
                // new user selected dirs/cats
                !empty($selected_subdir) && is_dir($dir . $selected_subdir)
            ) {
                $dir .= $selected_subdir . '/';
                $safepath = $selected_subdir . '/';
            }
            $picture_original = $dir . $_FILES['photo_upload']['name'];
            if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'], $picture_original)) {
                echo __('Photo upload failed, check folder rights');
            } elseif ($resizePicture->check_media_type($dir, $_FILES['photo_upload']['name'])) {
                $resizePicture->resize_picture($dir, $_FILES['photo_upload']['name']); // resize only big image files to H=1080px
                $resizePicture->create_thumbnail($dir, $_FILES['photo_upload']['name']);
                // *** Add picture to array ***
                $picture_array[] = $_FILES['photo_upload']['name'];

                // *** Re-order pictures by alphabet ***
                @sort($picture_array);
                $nr_pictures = count($picture_array);

                // *** Directly connect new media to person or relation ***
                if (isset($_POST['person_add_media'])) {
                    $event_connect_kind = 'person';
                    $event_connect_id = $this->pers_gedcomnumber;
                    $event_kind = 'picture';
                    $event_event = $safepath . $_FILES['photo_upload']['name'];
                    $event_gedcom = '';
                }
                if (isset($_POST['relation_add_media'])) {
                    $event_connect_kind = 'family';
                    $event_connect_id = $marriage;
                    $event_kind = 'picture';
                    $event_event = $safepath . $_FILES['photo_upload']['name'];
                    $event_gedcom = '';
                }
                if (isset($_POST['source_add_media'])) {
                    $event_connect_kind = 'source';
                    $event_connect_id = $_POST['source_gedcomnr'];
                    $event_kind = 'picture';
                    $event_event = $safepath . $_FILES['photo_upload']['name'];
                    $event_gedcom = '';
                }
                // *** Add event ***
                $data = [
                    'tree_id' => $this->tree_id,
                    'event_connect_kind' => $event_connect_kind,
                    'event_connect_id' => $event_connect_id,
                    'event_kind' => $event_kind,
                    'event_event' => $event_event,
                    'event_gedcom' => $event_gedcom,
                    'event_date' => '',
                    'event_place' => '',
                    'event_text' => ''
                ];
                $eventManager->update_event($data);
            } else {
                echo '<font color="red">' . __('No valid picture, media or document file') .  '</font>';
            }
        }

        // *** Change event ***
        // Also check is_numeric
        if (isset($_POST['event_id'])) {
            foreach ($_POST['event_id'] as $key => $value) {
                $event_event = '';
                if (isset($_POST["text_event"][$key])) {
                    $event_event = $this->editor_cls->text_process($_POST["text_event"][$key]);
                }

                // *** Replaced array function, because witness popup javascript doesn't work using an html-form-array ***
                //if (isset($_POST["text_event2" . $key]) and $_POST["text_event2" . $key] != '') {
                //    $event_event = '@' . $_POST["text_event2" . $key] . '@';
                //}
                $event_connect_kind2 = '';
                $event_connect_id2 = '';
                // *** Replaced array function, because witness popup javascript doesn't work using an html-form-array ***
                if (isset($_POST["event_connect_id2" . $key]) && $_POST["event_connect_id2" . $key] != '') {
                    $event_connect_kind2 = 'person';
                    $event_connect_id2 = $_POST["event_connect_id2" . $key];
                }

                // *** Media selection pop-up option *** 
                if (isset($_POST["text_event" . $key]) && $_POST["text_event" . $key] != '') {
                    $event_event = $this->editor_cls->text_process($_POST["text_event" . $key]);
                }

                // *** Only update if there are changed values! Otherwise all event_change variables will be changed... ***
                $event_id = $_POST["event_id"][$key];
                if (is_numeric($event_id)) {
                    // *** Read old values ***
                    $event_qry = "SELECT e.*, l.location_location AS event_place
                        FROM humo_events e
                        LEFT JOIN humo_location l ON e.place_id = l.location_id
                        WHERE e.event_id='" . $event_id . "'";
                    $event_result = $this->dbh->query($event_qry);
                    $eventDb = $event_result->fetch(PDO::FETCH_OBJ);
                    $event_changed = false;

                    if ($event_event != $eventDb->event_event) {
                        $event_changed = true;
                    }

                    if ($event_connect_id2 != $eventDb->event_connect_id2) {
                        $event_changed = true;
                    }

                    // *** Compare date case-insensitive (for PHP 8.1 check if variabele is used) ***
                    //if (isset($_POST["event_date_prefix"][$key]) OR isset($_POST["event_date"][$key])){
                    // Doesn't work properly, date isn't always saved:
                    //if ($eventDb->event_date AND ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key])){
                    // Doesn't work if date is removed:
                    //if ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key]){
                    //if (isset($eventDb->event_date)){
                    if (isset($_POST["event_date"][$key])) {
                        $event_date = '';
                        if (isset($eventDb->event_date)) {
                            $event_date = $eventDb->event_date;
                        }
                        if (strcasecmp($_POST["event_date_prefix"][$key] . $_POST["event_date"][$key], $event_date) != 0) {
                            $event_changed = true;
                        }
                    }
                    if (isset($_POST["event_place" . $key]) && $_POST["event_place" . $key] != $eventDb->event_place) {
                        $event_changed = true;
                    }
                    if (isset($_POST["event_event_extra"][$key]) && $_POST["event_event_extra"][$key] != $eventDb->event_event_extra) {
                        $event_changed = true;
                    }
                    if (isset($_POST["event_gedcom"][$key]) && $_POST["event_gedcom"][$key] != $eventDb->event_gedcom) {
                        $event_changed = true;
                    }
                    if (isset($_POST["event_text"][$key]) && $_POST["event_text"][$key] != $eventDb->event_text) {
                        $event_changed = true;
                    }

                    if ($event_changed) {
                        // 'event_connect_kind' => 'person',
                        // 'event_connect_id' => $eventDb->event_connect_id,
                        // 'event_kind' => 'person_colour_mark',
                        // 'event_gedcom' => $event_gedcom,
                        $data = [
                            'tree_id' => $this->tree_id,
                            'event_id' => $event_id,
                            'event_connect_kind2' => $event_connect_kind2,
                            'event_connect_id2' => $event_connect_id2,
                            'event_event' => $event_event,
                            'event_changed_user_id' => $this->userid
                        ];
                        if (isset($_POST["event_date"][$key])) {
                            $data['event_date'] = $this->editor_cls->date_process("event_date", $key);
                        }
                        if (isset($_POST["event_place" . $key])) {
                            $data['event_place'] = $_POST["event_place" . $key];
                        }
                        if (isset($_POST["event_event_extra"][$key])) {
                            $data['event_event_extra'] = $_POST["event_event_extra"][$key];

                            // *** If witness isn't a connected person (other role), then use OTHER ***
                            if (isset($_POST["check_event_kind"][$key]) && $_POST["check_event_kind"][$key] == 'ASSO' && $_POST["event_event_extra"][$key]) {
                                $_POST["event_gedcom"][$key] = 'OTHER';
                            }
                        }
                        if (isset($_POST["event_gedcom"][$key])) {
                            $data['event_gedcom'] = $_POST["event_gedcom"][$key];
                        }
                        if (isset($_POST["event_text"][$key])) {
                            $data['event_text'] = $_POST["event_text"][$key];
                        }

                        $eventManager->update_event($data);
                    }
                }

                // *** Also change person colors by descendants of selected person ***
                if (isset($_POST["pers_colour_desc"][$key])) {
                    // EXAMPLE: $descendants->get_descendants($family_id,$main_person,$nr_generations);
                    $descendant_array = $descendants->get_descendants($marriage, $this->pers_gedcomnumber, 20);
                    $descendant_id = $descendants->get_descendant_id();
                    // *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
                    // *** $descendant_array[0]= not in use ***
                    // *** $descendant_array[1]= main person ***
                    for ($i = 2; $i <= $descendant_id; $i++) {
                        // *** Check if descendant already has this colour ***
                        $event_sql = "SELECT * FROM humo_events
                            WHERE event_tree_id = :tree_id
                            AND event_connect_kind = 'person'
                            AND event_connect_id = :descendant_id
                            AND event_kind = 'person_colour_mark'
                            AND event_event = :event_event";
                        $event_qry = $this->dbh->prepare($event_sql);
                        $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                        $event_qry->bindValue(':descendant_id', $descendant_array[$i], PDO::PARAM_STR);
                        $event_qry->bindValue(':event_event', $_POST["event_event_old"][$key], PDO::PARAM_STR);
                        $event_qry->execute();
                        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

                        $event_gedcom = '';
                        if (isset($_POST["event_gedcom"][$key])) {
                            $event_gedcom = $_POST["event_gedcom"][$key];
                        }
                        $event_text = '';
                        if (isset($_POST["event_text"][$key])) {
                            $event_text = $_POST["event_text"][$key];
                        }

                        // *** Descendant already has this color, change it ***
                        if (isset($eventDb->event_event)) {
                            $data = [
                                'tree_id' => $this->tree_id,
                                'event_connect_kind' => 'person',
                                'event_connect_id' => $descendant_array[$i],
                                'event_kind' => 'person_colour_mark',
                                'event_event' => $event_event,
                                'event_gedcom' => $event_gedcom,
                                'event_date' => $this->editor_cls->date_process("event_date", $key),
                                'event_place' => $_POST["event_place" . $key],
                                'event_text' => $event_text,
                                'event_changed_user_id' => $this->userid,
                                'event_id' => $eventDb->event_id
                            ];
                            $eventManager->update_event($data);
                        } else {
                            // *** Add person event for descendants ***
                            $data = [
                                'tree_id' => $this->tree_id,
                                'event_connect_kind' => 'person',
                                'event_connect_id' => $descendant_array[$i],
                                'event_kind' => 'person_colour_mark',
                                'event_event' => $event_event,
                                'event_gedcom' => $event_gedcom,
                                'event_date' => $this->editor_cls->date_process("event_date", $key),
                                'event_place' => $_POST["event_place" . $key],
                                'event_text' => $event_text
                            ];
                            $eventManager->update_event($data);
                        }
                    }
                }

                // *** Also change person colors by ancestors of selected person ***
                if (isset($_POST["pers_colour_anc"][$key])) {
                    $ancestor_array = $ancestors->get_ancestors($this->db_functions, $this->pers_gedcomnumber);
                    foreach ($ancestor_array as $key2 => $value) {
                        //echo $key2.'-'.$value.', ';
                        $selected_ancestor = $value;

                        // *** Check if ancestor already has this colour ***
                        $event_sql = "SELECT * FROM humo_events
                            WHERE event_tree_id = :tree_id
                            AND event_connect_kind = 'person'
                            AND event_connect_id = :selected_ancestor
                            AND event_kind = 'person_colour_mark'
                            AND event_event = :event_event";
                        $event_qry = $this->dbh->prepare($event_sql);
                        $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                        $event_qry->bindValue(':selected_ancestor', $selected_ancestor, PDO::PARAM_STR);
                        $event_qry->bindValue(':event_event', $_POST["event_event_old"][$key], PDO::PARAM_STR);
                        $event_qry->execute();
                        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

                        $event_gedcom = '';
                        if (isset($_POST["event_gedcom"][$key])) {
                            $event_gedcom = $_POST["event_gedcom"][$key];
                        }
                        $event_text = '';
                        if (isset($_POST["event_text"][$key])) {
                            $event_text = $_POST["event_text"][$key];
                        }

                        // *** Ancestor already has this color, change it ***
                        if (isset($eventDb->event_event)) {
                            $data = [
                                'tree_id' => $this->tree_id,
                                'event_connect_kind' => 'person',
                                'event_connect_id' => $selected_ancestor,
                                'event_kind' => 'person_colour_mark',
                                'event_event' => $event_event,
                                'event_gedcom' => $event_gedcom,
                                'event_date' => $this->editor_cls->date_process("event_date", $key),
                                'event_place' => $_POST["event_place" . $key],
                                'event_text' => $event_text,
                                'event_id' => $eventDb->event_id,
                                'event_changed_user_id' => $this->userid
                            ];
                            $eventManager->update_event($data);
                        } else {
                            // *** Add person event for ancestors ***
                            $data = [
                                'tree_id' => $this->tree_id,
                                'event_connect_kind' => 'person',
                                'event_connect_id' => $selected_ancestor,
                                'event_kind' => 'person_colour_mark',
                                'event_event' => $event_event,
                                'event_gedcom' => $event_gedcom,
                                'event_date' => $this->editor_cls->date_process("event_date", $key),
                                'event_place' => $_POST["event_place" . $key],
                                'event_text' => $event_text
                            ];
                            $eventManager->update_event($data);
                        }
                    }
                }
            }
        }

        // *** Remove event ***
        if (isset($_GET['event_drop'])) {
            $confirm .= '<div class="alert alert-danger">';
            $confirm .= '<strong>' . __('Are you sure you want to remove this event?') . '</strong>';
            $confirm .= ' <form method="post" action="index.php?page=' . $_GET['page'];
            if (isset($_GET['source_id'])) {
                $confirm .= '&amp;source_id=' . $_GET['source_id'];
            }
            $confirm .= '" style="display : inline;">';
            $confirm .= '<input type="hidden" name="event_connect_kind" value="' . $_GET['event_connect_kind'] . '">';
            $confirm .= '<input type="hidden" name="event_kind" value="' . $_GET['event_kind'] . '">';
            $confirm .= '<input type="hidden" name="event_drop" value="' . $_GET['event_drop'] . '">';

            if (isset($_GET['event_kind']) && $_GET['event_kind'] == 'person_colour_mark') {
                $selected = '';
                //if ($selected_alive=='alive'){
                //  $selected=' checked';
                //}
                $confirm .= '<br>' . __('Also remove colour marks of');
                $confirm .= ' <input type="checkbox" name="event_descendants" value="alive" class="form-check-input" ' . $selected . '> ' . __('Descendants');
                $confirm .= ' <input type="checkbox" name="event_ancestors" value="alive" class="form-check-input" ' . $selected . '> ' . __('Ancestors') . '<br>';
            }

            $confirm .= ' <input type="submit" name="event_drop2" value="' . __('Yes') . '" class="btn btn-sm btn-danger">';
            $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" class="btn btn-sm btn-success ms-3">';
            $confirm .= '</form>';
            $confirm .= '</div>';
        }
        if (isset($_POST['event_drop2'])) {
            $event_kind = $this->safeTextDb->safe_text_db($_POST['event_kind']);
            $event_order_id = $this->safeTextDb->safe_text_db($_POST['event_drop']);

            if ($_POST['event_connect_kind'] == 'person') {
                // *** Remove NON SHARED source from event (connection in humo_connections table) ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE person_id='" . $this->person->pers_id . "'
                    AND event_kind='" . $event_kind . "'
                    AND event_order='" . $event_order_id . "'";
                $event_qry = $this->dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                $event_event = $eventDb->event_event;

                // *** Remove sources ***
                //remove_sources('pers_event_source', $eventDb->event_id);

                if (isset($_POST['event_descendants']) || isset($_POST['event_ancestors'])) {
                    // *** Get event_event from selected person, needed to remove colour from descendant and/ or ancestors ***
                    $event_sql = "SELECT event_event FROM humo_events
                        WHERE person_id = :pers_id
                        AND event_kind = 'person_colour_mark' AND event_order = :event_order_id";
                    $event_qry = $this->dbh->prepare($event_sql);
                    $event_qry->bindValue(':pers_id', $this->person->pers_id, PDO::PARAM_STR);
                    $event_qry->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                    $event_qry->execute();
                    $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                    $event_event = $eventDb->event_event;
                }

                $sql = "DELETE FROM humo_events
                    WHERE person_id = :pers_id
                    AND event_kind = :event_kind
                    AND event_order = :event_order_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':pers_id', $this->person->pers_id, PDO::PARAM_STR);
                $stmt->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                $stmt->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                $stmt->execute();

                // *** Change order of events ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE person_id = :pers_id
                    AND event_kind = :event_kind AND event_order > :event_order_id ORDER BY event_order";
                $event_qry = $this->dbh->prepare($event_sql);
                $event_qry->bindValue(':pers_id', $this->person->pers_id, PDO::PARAM_INT);
                $event_qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                $event_qry->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                $event_qry->execute();
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_events SET
                        event_order='" . ($eventDb->event_order - 1) . "',
                        event_changed_user_id='" . $this->userid . "'
                        WHERE event_id='" . $eventDb->event_id . "'";
                    $this->dbh->query($sql);
                }

                // *** Also remove colour mark from descendants and/ or ancestors ***
                if (isset($_POST['event_descendants'])) {
                    // EXAMPLE: $descendants->get_descendants($family_id,$main_person,$nr_generations);
                    $descendant_array = $descendants->get_descendants($marriage, $this->pers_gedcomnumber, 20);
                    $descendant_id = $descendants->get_descendant_id();
                    // *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
                    for ($i = 2; $i <= $descendant_id; $i++) {
                        // *** Get event_order from selected person ***
                        $event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                            AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                        $event_qry = $this->dbh->query($event_sql);
                        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                        $event_order = $eventDb->event_order;

                        // *** Remove colour from descendant ***
                        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                            AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                        $this->dbh->query($sql);

                        // *** Restore order of colour marks ***
                        $event_sql = "SELECT * FROM humo_events
                            WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
                            AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
                        $event_qry = $this->dbh->query($event_sql);
                        while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                            $sql = "UPDATE humo_events SET
                                event_order='" . ($eventDb->event_order - 1) . "',
                                event_changed_user_id='" . $this->userid . "'
                                WHERE event_id='" . $eventDb->event_id . "'";
                            $this->dbh->query($sql);
                        }
                    }
                }

                if (isset($_POST['event_ancestors'])) {
                    $ancestor_array = $ancestors->get_ancestors($this->db_functions, $this->pers_gedcomnumber);
                    foreach ($ancestor_array as $key2 => $value) {
                        //echo $key2.'-'.$value.', ';
                        $selected_ancestor = $value;

                        // *** Get event_order from selected person ***
                        $event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                            AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                        $event_qry = $this->dbh->query($event_sql);
                        $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                        $event_order = $eventDb->event_order;

                        // *** Check if ancestor already has this colour ***
                        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                            AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
                        $this->dbh->query($sql);

                        // *** Restore order of colour marks ***
                        $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                            AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
                            AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
                        $event_qry = $this->dbh->query($event_sql);
                        while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                            $sql = "UPDATE humo_events SET
                                event_order='" . ($eventDb->event_order - 1) . "',
                                event_changed_user_id='" . $this->userid . "'
                                WHERE event_id='" . $eventDb->event_id . "'";
                            $this->dbh->query($sql);
                        }
                    }
                }
            }

            //if (isset($_POST['event_family'])) {
            elseif ($_POST['event_connect_kind'] == 'family') {
                // *** Remove NON SHARED source from event (connection in humo_connections table) ***
                $event_sql = "SELECT * FROM humo_events
                    WHERE event_tree_id='" . $this->tree_id . "'
                    AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
                    AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
                $event_qry = $this->dbh->query($event_sql);
                $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
                $event_event = $eventDb->event_event;

                // *** Remove sources ***
                //remove_sources('fam_event_source', $eventDb->event_id);

                $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                    AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
                    AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
                $this->dbh->query($sql);

                $event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $this->tree_id . "'
                    AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
                    AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
                $event_qry = $this->dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_events SET
                        event_order='" . ($eventDb->event_order - 1) . "',
                        event_changed_user_id='" . $this->userid . "'
                        WHERE event_id='" . $eventDb->event_id . "'";
                    $this->dbh->query($sql);
                }
            }

            // *** Picture by source: pictures are stored in event table ***
            //if (isset($_POST['event_source'])) {
            elseif ($_POST['event_connect_kind'] == 'source') {
                $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id
                    AND event_connect_kind = 'source' AND event_connect_id = :source_id
                    AND event_kind = :event_kind AND event_order = :event_order_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $stmt->bindValue(':source_id', $_GET['source_id'], PDO::PARAM_STR);
                $stmt->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                $stmt->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                $stmt->execute();

                $event_sql = "SELECT * FROM humo_events WHERE event_tree_id = :tree_id
                    AND event_connect_kind = 'source' AND event_connect_id = :source_id
                    AND event_kind = :event_kind AND event_order > :event_order_id ORDER BY event_order";
                $event_qry = $this->dbh->prepare($event_sql);
                $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $event_qry->bindValue(':source_id', $_GET['source_id'], PDO::PARAM_STR);
                $event_qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                $event_qry->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                $event_qry->execute();
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_events SET
                        event_order = :event_order,
                        event_changed_user_id = :event_changed_user_id
                        WHERE event_id = :event_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':event_order', ($eventDb->event_order - 1), PDO::PARAM_INT);
                    $stmt->bindValue(':event_changed_user_id', $this->userid, PDO::PARAM_STR);
                    $stmt->bindValue(':event_id', $eventDb->event_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            } else {
                $event_connect_id = '';

                $check_connect_kind = array("birth_declaration", "CHR", "death_declaration", 'BURI');
                if (in_array($_POST['event_connect_kind'], $check_connect_kind)) {
                    $event_connect_id = $this->pers_gedcomnumber;
                }
                if ($_POST['event_connect_kind'] == 'MARR' || $_POST['event_connect_kind'] == 'MARR_REL') {
                    $event_connect_id = $marriage;
                }
                if ($event_connect_id) {
                    $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id
                        AND event_connect_kind = :event_connect_kind
                        AND event_connect_id = :event_connect_id
                        AND event_kind = :event_kind
                        AND event_order = :event_order_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->bindValue(':event_connect_kind', $_POST['event_connect_kind'], PDO::PARAM_STR);
                    $stmt->bindValue(':event_connect_id', $event_connect_id, PDO::PARAM_STR);
                    $stmt->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                    $stmt->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                    $stmt->execute();

                    $event_sql = "SELECT * FROM humo_events WHERE event_tree_id = :tree_id
                        AND event_connect_kind = :event_connect_kind
                        AND event_connect_id = :event_connect_id
                        AND event_kind = :event_kind
                        AND event_order > :event_order_id
                        ORDER BY event_order";
                    $event_qry = $this->dbh->prepare($event_sql);
                    $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $event_qry->bindValue(':event_connect_kind', $_POST['event_connect_kind'], PDO::PARAM_STR);
                    $event_qry->bindValue(':event_connect_id', $event_connect_id, PDO::PARAM_STR);
                    $event_qry->bindValue(':event_kind', $event_kind, PDO::PARAM_STR);
                    $event_qry->bindValue(':event_order_id', $event_order_id, PDO::PARAM_STR);
                    $event_qry->execute();
                    while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                        $sql = "UPDATE humo_events SET
                            event_order='" . ($eventDb->event_order - 1) . "',
                            event_changed_user_id='" . $this->userid . "'
                            WHERE event_id='" . $eventDb->event_id . "'";
                        $this->dbh->query($sql);
                    }
                }
            }
        }

        /**
         * Save connections
         */

        // *** Add new person-address connection ***
        //if (isset($_GET['person_place_address']) AND isset($_GET['address_add'])){
        if (isset($_POST['person_add_address'])) {
            $_POST['connect_add'] = 'add_address';
            $_POST['connect_kind'] = 'person';
            $_POST["connect_sub_kind"] = 'person_address';
            $_POST["connect_connect_id"] = $this->pers_gedcomnumber;
        }
        // *** Add new family-address connection ***
        //if (isset($_GET['family_place_address']) AND isset($_GET['address_add'])){
        if (isset($_POST['relation_add_address'])) {
            $_POST['connect_add'] = 'add_address';
            $_POST['connect_kind'] = 'family';
            $_POST["connect_sub_kind"] = 'family_address';
            $marriage = $_POST['marriage']; // *** Needed to check $_POST for multiple relations ***
            $_POST["connect_connect_id"] = $marriage;
        }

        // *** Added april 2023: Add new source ***
        if (isset($_GET['source_add3'])) {
            $_POST['connect_add'] = 'add_source';
            $_POST['connect_kind'] = $_GET['connect_kind'];
            $_POST["connect_sub_kind"] = $_GET["connect_sub_kind"];
            $_POST["connect_connect_id"] = $_GET["connect_connect_id"];
        }

        /*
        // *** Added may 2023: Add new source ***
        //http://localhost/humo-genealogy/admin/index.php?page=editor&
        //source_add3=1&
        //connect_kind=person&
        //connect_sub_kind=pers_name_source&
        //connect_connect_id=I9892#pers_name_sourceI9892
        if (isset($_POST['add_pers_name_source'])){
            $_POST['connect_add']='add_source';
            $_POST['connect_kind']='person';
            $_POST["connect_sub_kind"]='pers_name_source';
            $_POST["connect_connect_id"]=$this->pers_gedcomnumber;

        unset ($_POST['connect_change']);
        }
        */

        // *** Add new source/ address connection ***
        if (isset($_POST['connect_add'])) {
            // *** Generate new order number ***
            $event_sql = "SELECT * FROM humo_connections
                WHERE connect_tree_id = :tree_id
                AND connect_kind = :connect_kind
                AND connect_sub_kind = :connect_sub_kind
                AND connect_connect_id = :connect_connect_id";
            $event_qry = $this->dbh->prepare($event_sql);
            $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $event_qry->bindValue(':connect_kind', $_POST['connect_kind'], PDO::PARAM_STR);
            $event_qry->bindValue(':connect_sub_kind', $_POST["connect_sub_kind"], PDO::PARAM_STR);
            $event_qry->bindValue(':connect_connect_id', $_POST["connect_connect_id"], PDO::PARAM_STR);
            $event_qry->execute();
            $count = $event_qry->rowCount();
            $count++;

            $sql = "INSERT INTO humo_connections SET
                connect_tree_id = :tree_id,
                connect_order = :connect_order,
                connect_new_user_id = :userid,
                connect_kind = :connect_kind,
                connect_sub_kind = :connect_sub_kind,
                connect_connect_id = :connect_connect_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':connect_order', $count, PDO::PARAM_INT);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':connect_kind', $_POST['connect_kind'], PDO::PARAM_STR);
            $stmt->bindValue(':connect_sub_kind', $_POST["connect_sub_kind"], PDO::PARAM_STR);
            $stmt->bindValue(':connect_connect_id', $_POST["connect_connect_id"], PDO::PARAM_STR);
            $stmt->execute();
        }

        // *** Change source/ address connection ***
        if (isset($_POST['connect_change'])) {
            foreach ($_POST['connect_change'] as $key => $value) {
                // *** Only update if there are changed values! Otherwise all connect_change variables will be changed... ***
                $connect_changed = true;

                if (isset($_POST['connect_date_old'][$key])) {
                    $connect_changed = false;
                    // *** Compare date case-insensitive ***
                    if (strcasecmp($_POST["connect_date_prefix"][$key] . $_POST["connect_date"][$key], $_POST["connect_date_old"][$key]) != 0) {
                        $connect_changed = true;
                    }
                    if ($_POST["connect_role"][$key] != $_POST["connect_role_old"][$key]) {
                        $connect_changed = true;
                    }
                    if ($_POST['connect_text'][$key] != $_POST["connect_text_old"][$key]) {
                        $connect_changed = true;
                    }

                    // *** Save shared address (even if role or extra text isn't used) ***
                    if (isset($_POST['connect_item_id'][$key])) {
                        if (isset($_POST["connect_item_id_old"][$key])) {
                            if ($_POST['connect_item_id'][$key] !== $_POST["connect_item_id_old"][$key]) {
                                $connect_changed = true;
                            }
                        } else {
                            $connect_changed = true;
                        }
                    }
                }

                // *** Remark: connect_kind and connect_sub_kind is missing if someone clicks "Add address" twice. ***
                if ($connect_changed) {
                    $sql = "UPDATE humo_connections SET
                        connect_page = :connect_page,
                        connect_role = :connect_role,
                        connect_changed_user_id = :userid";

                    $params = [
                        ':connect_page' => $_POST["connect_page"][$key],
                        ':connect_role' => $_POST["connect_role"][$key],
                        ':userid' => $this->userid,
                    ];

                    // Optional fields
                    if (isset($_POST['connect_kind'][$key])) {
                        $sql .= ", connect_kind = :connect_kind";
                        $params[':connect_kind'] = $_POST['connect_kind'][$key];
                    }
                    if (isset($_POST['connect_sub_kind'][$key])) {
                        $sql .= ", connect_sub_kind = :connect_sub_kind";
                        $params[':connect_sub_kind'] = $_POST['connect_sub_kind'][$key];
                    }
                    if (isset($_POST['connect_source_id'][$key])) {
                        $sql .= ", connect_source_id = :connect_source_id";
                        $params[':connect_source_id'] = $_POST['connect_source_id'][$key];
                    }
                    if (isset($_POST['connect_date'][$key])) {
                        $sql .= ", connect_date = :connect_date";
                        $params[':connect_date'] = $this->editor_cls->date_process("connect_date", $key);
                    }
                    if (isset($_POST['connect_place'][$key])) {
                        $sql .= ", connect_place = :connect_place";
                        $params[':connect_place'] = $_POST["connect_place"][$key];
                    }
                    if (isset($_POST['connect_text'][$key])) {
                        $sql .= ", connect_text = :connect_text";
                        $params[':connect_text'] = $_POST['connect_text'][$key];
                    }
                    if (isset($_POST['connect_quality'][$key])) {
                        $sql .= ", connect_quality = :connect_quality";
                        $params[':connect_quality'] = $_POST['connect_quality'][$key];
                    }
                    if (isset($_POST['connect_item_id'][$key]) && $_POST['connect_item_id'][$key]) {
                        $sql .= ", connect_item_id = :connect_item_id";
                        $params[':connect_item_id'] = $_POST['connect_item_id'][$key];
                    }

                    $sql .= " WHERE connect_id = :connect_id";
                    $params[':connect_id'] = $_POST["connect_change"][$key];

                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute($params);
                }

                if (isset($_POST['source_title'][$key])) {
                    $sql = "UPDATE humo_sources SET
                        source_title = :source_title,
                        source_text = :source_text,
                        source_refn = :source_refn,
                        source_date = :source_date,
                        source_place = :source_place,
                        source_changed_user_id = :userid
                        WHERE source_tree_id = :tree_id AND source_id = :source_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':source_title', $_POST['source_title'][$key], PDO::PARAM_STR);
                    $stmt->bindValue(':source_text', $this->editor_cls->text_process($_POST['source_text'][$key], true), PDO::PARAM_STR);
                    $stmt->bindValue(':source_refn', $_POST['source_refn'][$key], PDO::PARAM_STR);
                    $stmt->bindValue(':source_date', $this->editor_cls->date_process("source_date", $key), PDO::PARAM_STR);
                    $stmt->bindValue(':source_place', $_POST['source_place'][$key], PDO::PARAM_STR);
                    $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                    $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->bindValue(':source_id', $_POST["source_id"][$key], PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }

        // *** Remove source/ address connection ***
        if (isset($_GET['connect_drop'])) {
            // *** Needed for event sources ***
            $connect_kind = '';
            if (isset($_GET['connect_kind'])) {
                $connect_kind = $_GET['connect_kind'];
            }

            $connect_sub_kind = '';
            if (isset($_GET['connect_sub_kind'])) {
                $connect_sub_kind = $_GET['connect_sub_kind'];
            }

            // *** Needed for event sources ***
            $connect_connect_id = '';
            if (isset($_GET['connect_connect_id']) && $_GET['connect_connect_id']) {
                $connect_connect_id = $_GET['connect_connect_id'];
            }

            $event_link = '';
            if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                $event_link = '&event_person=1';
            }
            if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                $event_link = '&event_family=1';
            }
?>
            <div class="alert alert-danger">
                <!-- TODO: check code, $_GET and $_POST are used -->
                <form method="post" action="index.php?page=<?= $page; ?>&connect_kind=<?= $connect_kind; ?>&connect_sub_kind=<?= $connect_sub_kind; ?>&connect_connect_id=<?= $connect_connect_id . $event_link; ?>" style="display : inline;">
                    <input type="hidden" name="connect_drop" value="<?= $_GET['connect_drop']; ?>">
                    <input type="hidden" name="connect_kind" value="<?= $connect_kind; ?>">
                    <input type="hidden" name="connect_sub_kind" value="<?= $connect_sub_kind; ?>">
                    <input type="hidden" name="connect_connect_id" value="<?= $connect_connect_id; ?>">

                    <?php
                    if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                        echo '<input type="hidden" name="event_person" value="1">';
                    }
                    if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                        echo '<input type="hidden" name="event_family" value="1">';
                    }

                    // *** Remove address event ***
                    if (isset($_GET['person_place_address'])) {
                        echo '<input type="hidden" name="person_place_address" value="person_place_address">';
                    }

                    if (isset($_GET['marriage_nr'])) {
                        echo '<input type="hidden" name="marriage_nr" value="' . $this->safeTextDb->safe_text_db($_GET['marriage_nr']) . '">';
                    }
                    ?>

                    <strong><?= __('Are you sure you want to remove this event?'); ?></strong>
                    <input type="submit" name="connect_drop2" value="<?= __('Yes'); ?>" class="btn btn-sm btn-danger">
                    <input type="submit" name="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-success ms-3">
                </form>
            </div>
<?php
        }
        // *** Delete source or address connection ***
        if (isset($_POST['connect_drop2'])) {
            $event_sql = "SELECT * FROM humo_connections WHERE connect_id = :connect_id";
            $event_qry = $this->dbh->prepare($event_sql);
            $event_qry->bindValue(':connect_id', $_POST['connect_drop'], PDO::PARAM_STR);
            $event_qry->execute();
            $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

            $connect_kind = $eventDb->connect_kind;
            $connect_sub_kind = $eventDb->connect_sub_kind;
            $connect_connect_id = $eventDb->connect_connect_id;
            //echo $connect_kind.' '.$connect_sub_kind.' '.$connect_connect_id.'!!';

            // *** Remove (NON-SHARED) source by all connections ***
            /*
            if ($eventDb->connect_source_id){
                // TODO DOESN'T WORK (update: recheck because tree_id was missing)
                //$sourceDb = $this->db_functions->get_source($eventDb->connect_source_id);
                $source_sql="SELECT * FROM humo_sources
                    WHERE source_gedcomnr='".$eventDb->connect_source_id."'
                    AND source_shared!='1'";
                //echo $source_sql.'<br>';
                $source_qry=$this->dbh->query($source_sql);
                $sourceDb=$source_qry->fetch(PDO::FETCH_OBJ);
                if ($sourceDb){
                    $sql="DELETE FROM humo_sources WHERE source_id='".$sourceDb->source_id."'";
                    $this->dbh->query($sql);
                }
            }
            */

            // *** Remove NON SHARED addresses ***
            if ($connect_sub_kind == 'person_address' || $connect_sub_kind == 'family_address') {
                $address_sql = "SELECT * FROM humo_addresses
                    WHERE address_gedcomnr = :address_gedcomnr
                    AND address_shared != '1'";
                $address_qry = $this->dbh->prepare($address_sql);
                $address_qry->bindValue(':address_gedcomnr', $eventDb->connect_item_id, PDO::PARAM_STR);
                $address_qry->execute();
                $addressDb = $address_qry->fetch(PDO::FETCH_OBJ);
                if ($addressDb) {
                    $sql = "DELETE FROM humo_addresses WHERE address_id = :address_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':address_id', $addressDb->address_id, PDO::PARAM_INT);
                    $stmt->execute();
                }

                // *** Remove sources ***
                if ($connect_sub_kind == 'person_address') {
                    //remove_sources('pers_address_source', $eventDb->connect_item_id);
                }
                if ($connect_sub_kind == 'family_address') {
                    //remove_sources('fam_address_source', $eventDb->connect_item_id);
                }
            }

            $sql = "DELETE FROM humo_connections WHERE connect_id = :connect_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':connect_id', $_POST['connect_drop'], PDO::PARAM_STR);
            $stmt->execute();

            // *** Re-order remaining source connections ***
            $event_order = 1;
            $event_sql = "SELECT * FROM humo_connections
                WHERE connect_tree_id = :tree_id
                AND connect_kind = :connect_kind
                AND connect_sub_kind = :connect_sub_kind
                AND connect_connect_id = :connect_connect_id
                ORDER BY connect_order";
            $event_qry = $this->dbh->prepare($event_sql);
            $event_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $event_qry->bindValue(':connect_kind', $connect_kind, PDO::PARAM_STR);
            $event_qry->bindValue(':connect_sub_kind', $connect_sub_kind, PDO::PARAM_STR);
            $event_qry->bindValue(':connect_connect_id', $connect_connect_id, PDO::PARAM_STR);
            $event_qry->execute();
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_connections SET connect_order = :connect_order WHERE connect_id = :connect_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':connect_order', $event_order, PDO::PARAM_INT);
                $stmt->bindValue(':connect_id', $eventDb->connect_id, PDO::PARAM_STR);
                $stmt->execute();
                $event_order++;
            }
        }

        // *******************
        // *** Save source ***
        // *******************

        // *** december 2020: new combined source and shared source system ***
        if (isset($_GET['source_add2'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'S' . $this->db_functions->generate_gedcomnr($this->tree_id, 'source');

            $sql = "INSERT INTO humo_sources SET
                source_tree_id='" . $this->tree_id . "',
                source_gedcomnr='" . $new_gedcomnumber . "',
                source_status='',
                source_title='',
                source_date='',
                source_place='',
                source_publ='',
                source_refn='',
                source_auth='',
                source_subj='',
                source_item='',
                source_kind='',
                source_repo_caln='',
                source_repo_page='',
                source_repo_gedcomnr='',
                source_text='',
                source_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            $sql = "UPDATE humo_connections SET
                connect_tree_id = :tree_id,
                connect_source_id = :connect_source_id,
                connect_changed_user_id = :userid
                WHERE connect_id = :connect_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':connect_source_id', $new_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':connect_id', $_GET["connect_id"], PDO::PARAM_STR);
            $stmt->execute();
        }

        $save_source_data = false;
        if (isset($_POST['source_change'])) {
            $save_source_data = true;
        }
        // *** Also save source data if media is added ***
        if (isset($_POST['source_add_media'])) {
            $save_source_data = true;
        }
        if ($save_source_data) {
            $sql = "UPDATE humo_sources SET
                source_status = :source_status,
                source_title = :source_title,
                source_date = :source_date,
                source_place = :source_place,
                source_publ = :source_publ,
                source_refn = :source_refn,
                source_auth = :source_auth,
                source_subj = :source_subj,
                source_item = :source_item,
                source_kind = :source_kind,
                source_repo_caln = :source_repo_caln,
                source_repo_page = :source_repo_page,
                source_repo_gedcomnr = :source_repo_gedcomnr,
                source_text = :source_text,
                source_changed_user_id = :userid
                WHERE source_tree_id = :tree_id AND source_id = :source_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':source_status', $_POST['source_status'], PDO::PARAM_STR);
            $stmt->bindValue(':source_title', $_POST['source_title'], PDO::PARAM_STR);
            $stmt->bindValue(':source_date', $this->editor_cls->date_process('source_date'), PDO::PARAM_STR);
            $stmt->bindValue(':source_place', $_POST['source_place'], PDO::PARAM_STR);
            $stmt->bindValue(':source_publ', $_POST['source_publ'], PDO::PARAM_STR);
            $stmt->bindValue(':source_refn', $_POST['source_refn'], PDO::PARAM_STR);
            $stmt->bindValue(':source_auth', $_POST['source_auth'], PDO::PARAM_STR);
            $stmt->bindValue(':source_subj', $_POST['source_subj'], PDO::PARAM_STR);
            $stmt->bindValue(':source_item', $_POST['source_item'], PDO::PARAM_STR);
            $stmt->bindValue(':source_kind', $_POST['source_kind'], PDO::PARAM_STR);
            $stmt->bindValue(':source_repo_caln', $_POST['source_repo_caln'], PDO::PARAM_STR);
            $stmt->bindValue(':source_repo_page', $_POST['source_repo_page'], PDO::PARAM_STR);
            $stmt->bindValue(':source_repo_gedcomnr', $_POST['source_repo_gedcomnr'], PDO::PARAM_STR);
            $stmt->bindValue(':source_text', $this->editor_cls->text_process($_POST['source_text'], true), PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':source_id', $_POST["source_id"], PDO::PARAM_STR);
            $stmt->execute();
        }


        /**
         * Save data places
         */

        // *** 25-12-2020: NEW combined addresses and shared addresses ***
        if (isset($_GET['address_add2'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'R' . $this->db_functions->generate_gedcomnr($this->tree_id, 'address');

            $sql = "INSERT INTO humo_addresses SET
                address_tree_id='" . $this->tree_id . "',
                address_gedcomnr='" . $new_gedcomnumber . "',
                address_address='',
                address_date='',
                address_zip='',
                address_place='',
                address_phone='',
                address_text='',
                address_new_user_id='" . $this->userid . "'";
            $this->dbh->query($sql);

            $stmt = $this->dbh->prepare(
                "UPDATE humo_connections SET
                    connect_tree_id = :tree_id,
                    connect_kind = :connect_kind,
                    connect_sub_kind = :connect_sub_kind,
                    connect_connect_id = :connect_connect_id,
                    connect_item_id = :connect_item_id,
                    connect_new_user_id = :userid
                 WHERE connect_id = :connect_id"
            );
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->bindValue(':connect_kind', $_GET['connect_kind'], PDO::PARAM_STR);
            $stmt->bindValue(':connect_sub_kind', $_GET["connect_sub_kind"], PDO::PARAM_STR);
            $stmt->bindValue(':connect_connect_id', $_GET["connect_connect_id"], PDO::PARAM_STR);
            $stmt->bindValue(':connect_item_id', $new_gedcomnumber, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':connect_id', $_GET["connect_id"], PDO::PARAM_STR);
            $stmt->execute();
        }

        // *** Change address ***
        if (isset($_POST['change_address_id'])) {
            foreach ($_POST['change_address_id'] as $key => $value) {

                // *** Date for address is processed in connection table ***
                //address_date='".$this->editor_cls->date_process("address_date",$key)."',
                $address_shared = '';
                if (isset($_POST["address_shared_" . $key])) {
                    $address_shared = '1';
                }

                // *** Only update if there are changed values! Otherwise all address_change variables will be changed... ***
                $address_changed = false;
                // Or: get old values out of the database. See editor notes (below in this script).
                if ($address_shared != $_POST["address_shared_old"][$key]) {
                    $address_changed = true;
                }
                if ($_POST["address_address_" . $key] != $_POST["address_address_old"][$key]) {
                    $address_changed = true;
                }
                if ($_POST["address_place_" . $key] != $_POST["address_place_old"][$key]) {
                    $address_changed = true;
                }
                if ($_POST["address_text_" . $key] != $_POST["address_text_old"][$key]) {
                    $address_changed = true;
                }
                if ($_POST["address_phone_" . $key] != $_POST["address_phone_old"][$key]) {
                    $address_changed = true;
                }
                if ($_POST["address_zip_" . $key] != $_POST["address_zip_old"][$key]) {
                    $address_changed = true;
                }

                if ($address_changed) {
                    $sql = "UPDATE humo_addresses SET
                        address_shared = :address_shared,
                        address_address = :address_address,
                        address_place = :address_place,
                        address_text = :address_text,
                        address_phone = :address_phone,
                        address_zip = :address_zip,
                        address_changed_user_id = :userid
                        WHERE address_id = :address_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':address_shared', $address_shared, PDO::PARAM_STR);
                    $stmt->bindValue(':address_address', $_POST["address_address_" . $key], PDO::PARAM_STR);
                    $stmt->bindValue(':address_place', $_POST["address_place_" . $key], PDO::PARAM_STR);
                    $stmt->bindValue(':address_text', $_POST["address_text_" . $key], PDO::PARAM_STR);
                    $stmt->bindValue(':address_phone', $_POST["address_phone_" . $key], PDO::PARAM_STR);
                    $stmt->bindValue(':address_zip', $_POST["address_zip_" . $key], PDO::PARAM_STR);
                    $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                    $stmt->bindValue(':address_id', $_POST["change_address_id"][$key], PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
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
                pers_fams = :fams,
                pers_changed_user_id = :userid
                WHERE pers_id = :person_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fams', $fams, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':person_id', $person_db->pers_id, PDO::PARAM_STR);
            $stmt->execute();
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
                pers_fams = :fams,
                pers_changed_user_id = :userid
                WHERE pers_id = :person_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':fams', $fams3, PDO::PARAM_STR);
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':person_id', $person_db->pers_id, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // *** Calculate and update nr. of persons and nr. of families ***
    private function family_tree_update(): void
    {
        $nr_persons = $this->db_functions->count_persons($this->tree_id);
        $nr_families = $this->db_functions->count_families($this->tree_id);

        $tree_date = date("Y-m-d H:i");
        $sql = "UPDATE humo_trees SET tree_persons = :nr_persons, tree_families = :nr_families, tree_date = :tree_date WHERE tree_id = :tree_id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':nr_persons', $nr_persons, PDO::PARAM_INT);
        $stmt->bindValue(':nr_families', $nr_families, PDO::PARAM_INT);
        $stmt->bindValue(':tree_date', $tree_date, PDO::PARAM_STR);
        $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function update_note()
    {
        $confirm = '';
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();

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
            $privacy = $personPrivacy->get_privacy($persDb);
            $name = $personName->get_person_name($persDb, $privacy);
            $note_names = $this->safeTextDb->safe_text_db($name["standard_name"]);

            //note_connect_kind='person',
            $stmt = $this->dbh->prepare(
                "INSERT INTO humo_user_notes SET
                    note_new_user_id = :userid,
                    note_note = '',
                    note_kind = 'editor',
                    note_status = 'Not started',
                    note_priority = 'Normal',
                    note_connect_kind = :note_connect_kind,
                    note_connect_id = :note_connect_id,
                    note_names = :note_names,
                    note_tree_id = :tree_id"
            );
            $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
            $stmt->bindValue(':note_connect_kind', $note_connect_kind, PDO::PARAM_STR);
            $stmt->bindValue(':note_connect_id', $note_connect_id, PDO::PARAM_STR);
            $stmt->bindValue(':note_names', $note_names, PDO::PARAM_STR);
            $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
            $stmt->execute();
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
                            note_note = :note_note,
                            note_status = :note_status,
                            note_priority = :note_priority,
                            note_changed_user_id = :userid
                            WHERE note_id = :note_id";
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->bindValue(':note_note', $_POST["note_note"][$key], PDO::PARAM_STR);
                        $stmt->bindValue(':note_status', $_POST["note_status"][$key], PDO::PARAM_STR);
                        $stmt->bindValue(':note_priority', $_POST["note_priority"][$key], PDO::PARAM_STR);
                        $stmt->bindValue(':userid', $this->userid, PDO::PARAM_STR);
                        $stmt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }
            }
        }

        // *** Remove editor note ***
        if (isset($_GET['note_drop']) && is_numeric($_GET['note_drop'])) {
            $confirm .= '<div class="alert alert-danger">';
            $confirm .= __('Are you sure you want to remove this note?');
            $confirm .= ' <form method="post" action="index.php?page=' . $_GET['page'] . '" style="display : inline;">';
            $confirm .= '<input type="hidden" name="note_drop" value="' . $_GET['note_drop'] . '">';
            $confirm .= ' <input type="submit" name="note_drop2" value="' . __('Yes') . '" class="btn btn-sm btn-danger">';
            $confirm .= ' <input type="submit" name="submit" value="' . __('No') . '" class="btn btn-sm btn-success ms-3">';
            $confirm .= '</form>';
            $confirm .= '</div>';
        }
        if (isset($_POST['note_drop2']) && is_numeric($_POST['note_drop'])) {
            $sql = "DELETE FROM humo_user_notes WHERE note_id = :note_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':note_id', $_POST['note_drop'], PDO::PARAM_INT);
            $stmt->execute();
        }

        return $confirm;
    }

    private function cache_latest_changes($force_update = false)
    {
        global $pers_id;

        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='cache_latest_changes' AND setting_tree_id='" . $this->tree_id . "'");
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
            $person_qry = "(SELECT pers_id, pers_changed_datetime as changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NOT NULL)
                UNION (SELECT pers_id, pers_new_datetime AS changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_changed_datetime IS NULL)
                ORDER BY changed_datetime DESC LIMIT 0,15";
            $person_result = $this->dbh->query($person_qry);
            $count_latest_changes = $person_result->rowCount();
            while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                $person->time = time(); // *** Add linux time to array ***
                if ($cache) $cache .= '|';
                $cache .= serialize(json_encode($person));
                $cache_count++;
                if (!$force_update) {
                    $pers_id[] = $person->pers_id;
                }
            }

            // *** Add or renew cache in database (only if cache_count is valid) ***
            if ($cache && $cache_count == $count_latest_changes) {
                if ($cache_exists) {
                    // Because of bug found in jan. 2024, remove value from database and insert again.
                    $sql = "DELETE FROM humo_settings WHERE setting_tree_id = :tree_id AND setting_variable = :setting_variable";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->bindValue(':setting_variable', 'cache_latest_changes', PDO::PARAM_STR);
                    $stmt->execute();

                    $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_tree_id) VALUES (:setting_variable, :setting_value, :setting_tree_id)";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':setting_variable', 'cache_latest_changes', PDO::PARAM_STR);
                    $stmt->bindValue(':setting_value', $cache, PDO::PARAM_STR);
                    $stmt->bindValue(':setting_tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->execute();
                } else {
                    $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_tree_id) VALUES (:setting_variable, :setting_value, :setting_tree_id)";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->bindValue(':setting_variable', 'cache_latest_changes', PDO::PARAM_STR);
                    $stmt->bindValue(':setting_value', $cache, PDO::PARAM_STR);
                    $stmt->bindValue(':setting_tree_id', $this->tree_id, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }
    }
}
