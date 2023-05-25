<?php
class Address
{
    //private $table = "tbl_tickets";
    private $Connection;
    //private $id;
    //private $Name;
    //private $Surname;
    //private $email;
    //private $phone;

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

    public function getName()
    {
        return $this->Name;
    }
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    public function getSurname()
    {
        return $this->Surname;
    }
    public function setSurname($Surname)
    {
        $this->Surname = $Surname;
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getphone()
    {
        return $this->phone;
    }
    public function setphone($phone)
    {
        $this->phone = $phone;
    }
    */

    public function getAll()
    {
        //$consultation = $this->Connection->prepare("SELECT id,Name,Surname,email,phone FROM " . $this->table);
        //$consultation = $this->Connection->prepare("SELECT * FROM " . $this->table);

        //$consultation = $this->Connection->prepare("SELECT * FROM tbl_tickets
        //    LEFT JOIN tbl_ticket_category ON ticket_category=ticket_category_id
        //    LEFT JOIN tbl_ticket_status ON ticket_status=ticket_status_id");
        $sql = "SELECT * FROM tbl_tickets
        LEFT JOIN tbl_ticket_category ON ticket_category=ticket_category_id
        LEFT JOIN tbl_ticket_status ON ticket_status=ticket_status_id";

        $start = ' WHERE';
        if (isset($_POST['search_ticket']))
            $search_ticket = $_POST['search_ticket'];
        else
            $search_ticket = '';

        if ($search_ticket) {
            $sql .= " WHERE ticket_name LIKE '%" . $_POST['search_ticket'] . "%'";
            $sql .= " OR ticket_text LIKE '%" . $_POST['search_ticket'] . "%'";
            $start = ' AND';
        }

        // *** Voorbeeld voor ONLY_FULL_GROUP_BY instellingen ***
        //$sql.=" GROUP BY person_id ORDER BY ticket_name";
        //$sql.=" GROUP BY person_id, ticket_name ORDER BY ticket_name";

        if (isset($_GET['order']) and $_GET['order'] == 'status') {
            $sql .= " ORDER BY ticket_status";
        } elseif (isset($_GET['order']) and $_GET['order'] == 'status_desc') {
            $sql .= " ORDER BY ticket_status DESC";
        } else {
            $sql .= " ORDER BY ticket_priority, ticket_id";
        }

        $consultation = $this->Connection->prepare($sql);
        $consultation->execute();
        /* Fetch all of the remaining rows in the result set */
        $results = $consultation->fetchAll();

        $this->Connection = null; //connection closure
        return $results;
    }

    public function getById($id)
    {
        //$consultation = $this->Connection->prepare("SELECT id,Name,Surname,email,phone
        //    FROM " . $this->table . "  WHERE id = :id");
        //  $consultation = $this->Connection->prepare("
        //      SELECT * FROM " . $this->table . "  WHERE ticket_id = :ticket_id");

        global $tree_id;

        $sql = "SELECT * FROM humo_addresses
            WHERE address_tree_id=:address_tree_id AND address_gedcomnr=:address_gedcomnr";
        //$stmt = $this->db->prepare($sql);
        $stmt = $this->Connection->prepare($sql);
        //$stmt->bindValue(':address_tree_id', $this->tree_id, PDO::PARAM_STR);
        $stmt->bindValue(':address_tree_id', $tree_id, PDO::PARAM_STR);
        //$stmt->bindValue(':address_gedcomnr', $address_gedcomnr, PDO::PARAM_STR);
        $stmt->bindValue(':address_gedcomnr', $id, PDO::PARAM_STR);
        $stmt->execute();
        //$qryDb = $stmt->fetch(PDO::FETCH_OBJ);
        //		$result = $stmt->fetch(PDO::FETCH_OBJ);
        $result = $stmt->fetchObject();

        $this->Connection = null; //connection closure
        return $result;
    }
}
