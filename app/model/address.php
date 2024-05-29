<?php
class AddressModel
{
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }

    public function getAddressAuthorised($user)
    {
        $authorised = '';
        if ($user['group_addresses'] != 'j') {
            $authorised = __('You are not authorised to see this page.');
        }
        return $authorised;
    }

    /*
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    */

    public function getById($id)
    {
        $addressDb = $this->db_functions->get_address($id);

        //$this->Connection = null;
        return $addressDb;
    }

    public function getAddressSources($id)
    {
        // *** Show source by addresss ***
        $source_array = show_sources2("address", "address_source", $id);
        //$this->Connection = null;
        if ($source_array) {
            return $source_array['text'];
        }
        return null;
    }

    public function getAddressConnectedPersons($id)
    {
        $text = '';
        $person_cls = new person_cls;
        // *** Search address in connections table ***
        //$event_qry = $db_functions->get_connections('person_address', $_GET['gedcomnumber']);
        $event_qry = $this->db_functions->get_connections('person_address', $_GET['id']);
        foreach ($event_qry as $eventDb) {
            // *** Person address ***
            if ($eventDb->connect_connect_id) {
                $personDb = $this->db_functions->get_person($eventDb->connect_connect_id);
                $name = $person_cls->person_name($personDb);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                $text .= __('Address by person') . ': <a href="' . $url . '">' . $name["standard_name"] . '</a>';

                if ($eventDb->connect_role) {
                    $text .= ' ' . $eventDb->connect_role;
                }
                $text .= '<br>';
            }
        }
        unset($event_qry);

        //$this->Connection = null;
        return $text;
    }
}
