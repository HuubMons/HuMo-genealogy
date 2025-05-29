<?php
class TimelineController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getTimeline($id, $dirmark1): array
    {
        $TimelineModel = new TimelineModel($this->config);

        $personDb = $this->config['db_functions']->get_person($id);
        $person_data = $TimelineModel->getPersonData($personDb);
        $get_timeline_persons = $TimelineModel->getTimelinePersons($personDb, $dirmark1);

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
