<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonLink;
use PDO;

class StatsTreeModel extends BaseModel
{
    public function get_data(): array
    {
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $personLink = new PersonLink();

        // *** Most children in family ***
        $statistics['nr_children'] = 0; // *** minimum of 0 children ***
        $res = $this->dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' AND fam_children != ''");
        while ($record = $res->fetch(PDO::FETCH_OBJ)) {
            $count_children = substr_count($record->fam_children, ';');
            $count_children += 1;
            if ($count_children > $statistics['nr_children']) {
                $statistics['nr_children'] = $count_children;
                $man_gedcomnumber = $record->fam_man;
                $woman_gedcomnumber = $record->fam_woman;
                $fam_gedcomnumber = $record->fam_gedcomnumber;
            }
        }

        if ($statistics['nr_children'] != "0") {
            $record_man = $this->db_functions->get_person($man_gedcomnumber);
            $privacy = $personPrivacy->get_privacy($record_man);
            $name = $personName->get_person_name($record_man, $privacy);
            $statistics['man'] = $name["standard_name"];

            $record_woman = $this->db_functions->get_person($woman_gedcomnumber);
            $privacy = $personPrivacy->get_privacy($record_woman);
            $name = $personName->get_person_name($record_woman, $privacy);
            $statistics['woman'] = $name["standard_name"];

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $statistics['url'] = $personLink->get_person_link($record_man);
        }
        return $statistics;
    }
}
