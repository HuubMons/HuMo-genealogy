<?php
require_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/calculate_age_cls.php");

class TimelineController
{
    public function getTimeline($db_functions, $id, $user, $dirmark1)
    {
        $TimelineModel = new TimelineModel();

        $personDb = $db_functions->get_person($id);

        $person_data = $TimelineModel->getPersonData($personDb);

        $get_timeline_persons = $TimelineModel->getTimelinePersons($db_functions, $personDb, $user, $dirmark1);

        //$data = array(
        //    "title" => __('Timeline')
        //);

        // *** Add array $person_data:
        //$data = array_merge($data, $person_data);
        $data = $person_data;

        if ($get_timeline_persons) {
            $data = array_merge($data, $get_timeline_persons);
        }

        return $data;
    }
}
