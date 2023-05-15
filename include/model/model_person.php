<?php

require __DIR__ . '/../database_function.php';

class model_person extends database_function
{
    /**
     * Check for valid person in database.
     * 
     * TODO: @Devs This one is useless
     */
    public function check_person(string $pers_gedcomnumber)
    {
        $sql = "SELECT pers_id FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pers_tree_id' => $this->tree_id,
            ':pers_gedcomnumber' => $pers_gedcomnumber
        ]);

        if ($stmt->fetch(PDO::FETCH_OBJ)) {
            return true;
        }

        throw new Exception('Something went wrong, there is no valid person id.', 1);
    }

    /**
     * Get a single person from database by tree_id and gedcomnumber
     */
    public function get_person(string $pers_gedcomnumber, ?string $item = ''): object
    {
        if ($item == 'famc-fams') { // TODO: @Devs This one is useless
            $sql = "SELECT pers_famc, pers_fams FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
        } else {
            $sql = "SELECT * FROM humo_persons WHERE pers_tree_id=:pers_tree_id AND pers_gedcomnumber=:pers_gedcomnumber";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pers_tree_id' => $this->tree_id,
            ':pers_gedcomnumber' => $pers_gedcomnumber
        ]);

        if ($person = $stmt->fetch(PDO::FETCH_OBJ)) {
            return $person;
        }

        throw new Exception("No person found with gedcomnumber $pers_gedcomnumber", 1);
    }

    /**
     * Get a single person from database by pers_id
     */
    public function get_person_with_id(int $pers_id): object
    {
        $sql = "SELECT * FROM humo_persons WHERE pers_id=:pers_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pers_id' => $pers_id
        ]);

        if ($person = $stmt->fetch(PDO::FETCH_OBJ)) {
            return $person;
        }

        throw new Exception("No person found with id $pers_id", 1);
    }

    /**
     * Count persons in selected family tree.
     */
    function count_persons(int $tree_id): int
    {
        $sql = "SELECT COUNT(pers_id) AS count FROM humo_persons WHERE pers_tree_id=:pers_tree_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pers_tree_id' => $tree_id
        ]);

        return $stmt->fetchAll()['count'];
    }
}
