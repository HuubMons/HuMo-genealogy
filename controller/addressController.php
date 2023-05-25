<?php
class addressController
{
    //private $connector;
    private $Connection;

    public function __construct()
    {
        //require_once  __DIR__ . "/../core/connector.php";
        require_once  __DIR__ . "/../model/address.php";

        //$this->connector = new connector();
        //$this->Connection = $this->connector->Connection();
        // *** Use existing database connection ***
        global $dbh;
        $this->Connection = $dbh;
    }

    /**
     * Process action.
     *
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
     *
     */
    public function index()
    {
        // We create the Address object
        $get_tickets = new Address($this->Connection);
        // We get all the employees
        $addresses = $modelo->getAll();

        // We load the index view and pass values to it
        $this->view("addresses", array(
            "tickets" => $addresses,
            "titulo" => "PHP MVC"
        ));
    }

    /**
     * Get address
     *
     */
    public function detail()
    {
        //We load the model
        $modelo = new Address($this->Connection);

        $address = $modelo->getById($_GET["id"]);

        //We load the detail view and pass values to it
        $this->view("address", array(
            "address" => $address,
            "titulo" => __('Address')
        ));
    }

    /**
     * Create the view that we pass to it with the indicated data.
     *
     */
    public function view($vista, $datos)
    {
        //$data = $datos;
        require_once  __DIR__ . "/../view/" . $vista . "View.php";
    }
}
