<?php
class addressController
{
    //private $connector;
    private $Connection;

    public function __construct()
    {
        //require_once  __DIR__ . "/../core/connector.php";
        require_once  __DIR__ . "/../models/address.php";

        include_once(CMS_ROOTPATH . "include/person_cls.php");
        include_once(CMS_ROOTPATH . "include/show_sources.php");
        include_once(CMS_ROOTPATH . "include/show_picture.php");

        //$this->connector = new connector();
        //$this->Connection = $this->connector->Connection();
        // *** Use existing database connection ***
        global $dbh;
        $this->Connection = $dbh;
    }

    /**
     * Process action.
     */
    public function run($action)
    {
        switch ($action) {
            case "index":
                $this->index();
                break;
            case "detail":
                $this->detail();
                break;
            default:
                $this->index();
                break;
        }
    }

    /**
     * Loads the addresses page
     */
    public function index()
    {
        $get_addresses = new Address($this->Connection);
        $addresses = $get_addresses->getAll();
        $this->view("addresses", array(
            "addressess" => $addresses,
            "title" => __('Addresses')
        ));
    }

    /**
     * Get address
     */
    public function detail()
    {
        global $user;

        //We load the model
        $model = new Address($this->Connection);
        $address = $model->getById($_GET["id"]);

        $address_sources = $model->getAddressSources($_GET["id"]);

        $address_connected_persons = $model->getAddressConnectedPersons($_GET["id"]);

        // *** Check user ***
        $authorised = '';
        if ($user['group_addresses'] != 'j') {
            $authorised = __('You are not authorised to see this page.');
        }

        //We load the detail view and pass values to it
        $this->view("address", array(
            "authorised" => $authorised,
            "address" => $address,
            "address_sources" => $address_sources,
            "address_connected_persons" => $address_connected_persons,
            "title" => __('Address')
        ));
    }

    /**
     * Create the view that we pass to it with the indicated data.
     */
    public function view($view, $results)
    {
        $data = $results;
        require_once  __DIR__ . "/../views/" . $view . "View.php";
    }
}
