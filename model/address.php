<?php
class Address
{
    //private $table = "tbl_tickets";
    private $Connection;
    //private $id;

    public function __construct($Connection)
    {
        $this->Connection = $Connection;
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

    public function getAll()
    {
        //$consultation = $this->Connection->prepare("SELECT id,Name,Surname,email,phone FROM " . $this->table);
        //$consultation = $this->Connection->prepare("SELECT * FROM " . $this->table);

//TESTING...
//TO BE USED FOR ADDRESSES.PHP?


        $consultation = $this->Connection->prepare($sql);
        $consultation->execute();
        /* Fetch all of the remaining rows in the result set */
        $results = $consultation->fetchAll();

        $this->Connection = null; //connection closure
        return $results;
    }

    public function getById($id)
    {
        global $db_functions;

        $addressDb = $db_functions->get_address($id);
        $result = $addressDb;

        $this->Connection = null; //connection closure
        return $result;
    }

    public function getAddressSources($id)
    {
        // *** Show source by addresss ***
        $source_array = show_sources2("address", "address_source", $id);

        $this->Connection = null; //connection closure
        if ($source_array)
            return $source_array['text'];
    }

    public function getAddressConnectedPersons($id)
    {
        global $db_functions;

        $text = '';
        $person_cls = new person_cls;
        // *** Search address in connections table ***
        //$event_qry = $db_functions->get_connections('person_address', $_GET['gedcomnumber']);
        $event_qry = $db_functions->get_connections('person_address', $_GET['id']);
        foreach ($event_qry as $eventDb) {
            // *** Person address ***
            if ($eventDb->connect_connect_id) {
                $personDb = $db_functions->get_person($eventDb->connect_connect_id);
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
        unset($event_qry); // *** If finished, remove data from memory ***

        $this->Connection = null; //connection closure
        return $text;
    }
}
