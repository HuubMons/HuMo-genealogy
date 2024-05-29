<?php
class SourceModel
{
    public function GetSource($db_functions, $id)
    {
        return $db_functions->get_source($id);
    }

    public function GetSourceConnections($dbh, $tree_id, $source_gedcomnr)
    {
        // *** Sources in connect table ***
        $connect_qry = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
            AND connect_source_id='" . $source_gedcomnr . "'
            ORDER BY connect_kind, connect_sub_kind, connect_order";
        $connect_sql = $dbh->query($connect_qry);
        return $connect_sql->fetchAll(PDO::FETCH_OBJ);
    }
}
