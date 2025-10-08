<?php

/**
 * Database normalisation.
 */

namespace Genealogy\Admin\Migrations;

use PDO;
//use Exception;

class Migration21
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up()
    {
        /*
        Remark: person1 (father) and person2 (mother) could have different order numbers in case of multiple families.
        Example:
        relation_id - person_id - relation_type - order
        1             1           person1         1
        1             2           person2         1
        1             3           child           1
        1             4           child           2
        2             3           person1         1
        2             2           person2         2
        2             5           child           1
        */
        // *** Create new relation_persons table ***
        // TODO: check types and lengths
        $this->dbh->exec("
            CREATE TABLE humo_relations_persons (
                id INT UNSIGNED AUTO_INCREMENT,
                relation_id INT UNSIGNED NOT NULL,
                person_id INT UNSIGNED NOT NULL,
                tree_id SMALLINT(5) NOT NULL,
                relation_type VARCHAR(20) DEFAULT NULL, -- e.g. 'child', 'parent', 'adopted', 'father', 'mother', 'stepfather', 'stepmother', etc.
                relation_order TINYINT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (id)
            );
        ");

        // *** Move pers_fams data to humo_relations_persons table ***
        $persStmt = $this->dbh->query("SELECT pers_id, pers_gedcomnumber, pers_tree_id, pers_fams FROM humo_persons WHERE pers_fams IS NOT NULL AND pers_fams != ''");
        $insertPersFam = $this->dbh->prepare("
            INSERT INTO humo_relations_persons (relation_id, person_id, tree_id, relation_type, relation_order)
            VALUES (:relation_id, :person_id, :tree_id, :relation_type, :relation_order)
        ");
        while ($pers = $persStmt->fetch(PDO::FETCH_ASSOC)) {
            $famIds = explode(';', $pers['pers_fams']);
            $order = 1;
            foreach ($famIds as $famGedcomNr) {
                // *** Find family ID by gedcomnumber and tree_id ***
                $famStmt = $this->dbh->prepare("SELECT fam_id, fam_man FROM humo_families WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :gedcomnumber");
                $famStmt->execute([':tree_id' => $pers['pers_tree_id'], ':gedcomnumber' => $famGedcomNr]);
                $fam = $famStmt->fetch(PDO::FETCH_ASSOC);
                if ($fam) {
                    if ($pers['pers_gedcomnumber'] == $fam['fam_man']) {
                        $relation_type = 'person1';
                    } else {
                        $relation_type = 'person2';
                    }

                    $insertPersFam->execute([
                        ':relation_id' => $fam['fam_id'],
                        ':person_id' => $pers['pers_id'],
                        ':tree_id' => $pers['pers_tree_id'],
                        ':relation_type' => $relation_type,
                        ':relation_order' => $order
                    ]);
                    $order++;
                }
            }
        }

        // *** Move fam_children data to humo_relations_children table ***
        $famStmt = $this->dbh->query("SELECT fam_id, fam_tree_id, fam_children FROM humo_families WHERE fam_children IS NOT NULL AND fam_children != ''");
        $insert = $this->dbh->prepare("
            INSERT INTO humo_relations_persons (tree_id, relation_id, person_id, relation_type, relation_order)
            VALUES (:tree_id, :relation_id, :person_id, :relation_type, :relation_order)
        ");
        $stmt = $this->dbh->prepare("SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :gedcomnumber");
        while ($fam = $famStmt->fetch(PDO::FETCH_ASSOC)) {
            $children = explode(';', $fam['fam_children']);
            $order = 1;
            foreach ($children as $child_gedcomnr) {
                // *** Find person ID by gedcomnumber and tree_id ***
                $stmt->execute([':tree_id' => $fam['fam_tree_id'], ':gedcomnumber' => trim($child_gedcomnr)]);
                $pers = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($pers) {
                    $insert->execute([
                        ':tree_id' => $fam['fam_tree_id'],
                        ':relation_id' => $fam['fam_id'],
                        ':person_id' => $pers['pers_id'],
                        ':relation_type' => 'child',
                        ':relation_order' => $order
                    ]);
                    $order++;
                }
            }
        }

        $this->dbh->exec("
            ALTER TABLE humo_relations_persons
            ADD CONSTRAINT fk_relation_family
            FOREIGN KEY (relation_id) REFERENCES humo_families(fam_id)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");

        $this->dbh->exec("
            ALTER TABLE humo_relations_persons
            ADD CONSTRAINT fk_relation_person
            FOREIGN KEY (person_id) REFERENCES humo_persons(pers_id)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");

        $this->dbh->exec("
            ALTER TABLE humo_relations_persons
                ADD INDEX idx_relation_id (relation_id),
                ADD INDEX idx_person_id (person_id),
                ADD INDEX idx_relation_type (relation_type);
        ");

        // *** Remove old fam_man, fam_wife and fam_children fields ***
        // Maybe some fiels are still needed to import GEDCOM files.
        //$this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_man");
        //$this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_wife");
        //$this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_children");
        //$this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_fams");
        //
    }
}
