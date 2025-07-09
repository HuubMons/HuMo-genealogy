<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use PDO;

class SourceModel extends BaseModel
{
    public function GetSource($id)
    {
        return $this->db_functions->get_source($id);
    }

    public function GetSourceConnections($source_gedcomnr)
    {
        // *** Sources in connect table ***
        $connect_qry = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
            AND connect_source_id='" . $source_gedcomnr . "'
            ORDER BY connect_kind, connect_sub_kind, connect_order";
        $connect_sql = $this->dbh->query($connect_qry);
        return $connect_sql->fetchAll(PDO::FETCH_OBJ);
    }
}
