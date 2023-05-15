<?php

class database_function
{
    protected PDO $db;
    public ?int $tree_id = null;
    public string $tree_prefix = '';

    public function __construct($db_connection)
    {
        $this->db = $db_connection;
    }

    /** 
     * Set family tree_id 
     */
    public function set_tree_id(int $tree_id)
    {
        $this->tree_id = $tree_id;
        $stmt = $this->db->prepare("SELECT tree_prefix FROM humo_trees WHERE tree_id=:tree_id");
        $stmt->execute([
            ':tree_id' => $tree_id
        ]);
        $tree = $stmt->fetch(PDO::FETCH_OBJ);
        if (isset($tree->tree_prefix)) $this->tree_prefix = $tree->tree_prefix;
    }
  
}
