<?php
class StatsTreeModel
{
    public function get_data($dbh, $db_functions, $tree_id)
    {
        // *** Most children in family ***
        $statistics['nr_children'] = 0; // *** minimum of 0 children ***
        $res = $dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_children != ''");
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
            $record = $db_functions->get_person($man_gedcomnumber);
            $person_cls = new PersonCls($record);
            $name = $person_cls->person_name($record);
            $statistics['man'] = $name["standard_name"];

            $record = $db_functions->get_person($woman_gedcomnumber);
            $person_cls = new PersonCls($record);
            $name = $person_cls->person_name($record);
            $statistics['woman'] = $name["standard_name"];

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$statistics['url']=$person_cls->person_url2($fatherDb->pers_tree_id,$fatherDb->pers_famc,$fatherDb->pers_fams,$fatherDb->pers_gedcomnumber);
            $statistics['url'] = $person_cls->person_url2($tree_id, $fam_gedcomnumber, '', '');
        }
        return $statistics;
    }
}
