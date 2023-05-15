<?php

require __DIR__ . '/../database_function.php';

class model_family extends database_function
{
    /**
     * Check for valid family in database.
     * 
     * TODO: @Devs This one is useless
     */
    public function check_family(int $fam_gedcomnumber)
    {
        $sql = "SELECT fam_id FROM humo_families 
                WHERE fam_tree_id=:fam_tree_id 
                AND fam_gedcomnumber=:fam_gedcomnumber";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fam_tree_id' => $this->tree_id,
            ':fam_gedcomnumber' => $fam_gedcomnumber
        ]);

        if ($stmt->fetch(PDO::FETCH_OBJ)) {
            return true;
        }

        throw new Exception("No family found with gedcom id $fam_gedcomnumber", 1);
    }

    /**
     * Get a single family from database.
     * 
     * USE: get_family($fam_number,'man-woman') to get man and woman id. // TODO: useless
     */
    public function get_family(string $fam_gedcomnumber, $item = '')
    {
        if ($item == 'man-woman') {
            $sql = "SELECT fam_man, fam_woman FROM humo_families 
                        WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
        } else {
            $sql = "SELECT * FROM humo_families WHERE fam_tree_id=:fam_tree_id AND fam_gedcomnumber=:fam_gedcomnumber";
        }
        $stmt = $this->db->prepare($sql);
        if($stmt->execute([
            ':fam_tree_id' => $this->tree_id,
            ':fam_gedcomnumber' => $fam_gedcomnumber
        ])) {
            return $stmt->fetch(PDO::FETCH_OBJ);
        }

        throw new Exception("No family found with gedcom number $fam_gedcomnumber", 1);
    }

    /**
     * Count families in selected family tree.
     */
    public function count_families(int $tree_id): int
    {
        $sql = "SELECT COUNT(fam_id) AS count FROM humo_families WHERE fam_tree_id=:fam_tree_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fam_tree_id' => $this->tree_id
        ]);
        
        return $stmt->fetchAll()['count'];
    }
}
