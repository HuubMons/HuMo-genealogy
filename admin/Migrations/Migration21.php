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
        Remark: partner 1 (father) and partner 2 (mother) could have different relation_order numbers if these persons have multiple relations.
        In this schema, "relation_order" indicates the order of a child in a relationship, or the order of a relation connected to a person (partner).
        Field "partner_order" is used for first (man) and second (woman) person in a relation.

        relation_id - person_id - relation_type - relation_order - partner_order
        1             1           partner         1                1 (man)
        1             2           partner         1                2 (woman)
        1             3           child           1                1
        1             4           child           2                1
        2             3           partner         1                1 (man)
        2             2           partner         2                2 (woman)
        2             5           child           1                1
        */

        // *** Create new relation_persons table ***
        $this->dbh->exec("
            CREATE TABLE humo_relations_persons (
                id INT UNSIGNED AUTO_INCREMENT,
                relation_id INT UNSIGNED NOT NULL,
                relation_gedcomnumber VARCHAR(30) DEFAULT NULL,
                person_id INT UNSIGNED NOT NULL,
                person_gedcomnumber VARCHAR(30) DEFAULT NULL,
                person_age VARCHAR(15) CHARACTER SET utf8,
                tree_id SMALLINT NOT NULL,
                relation_type VARCHAR(20) DEFAULT NULL,
                relation_order TINYINT UNSIGNED DEFAULT NULL,
                partner_order TINYINT UNSIGNED DEFAULT 1,
                PRIMARY KEY (id)
            );
        ");

        // *** Move pers_fams data to humo_relations_persons table ***
        $persStmt = $this->dbh->query("SELECT pers_id, pers_gedcomnumber, pers_tree_id, pers_fams FROM humo_persons WHERE pers_fams IS NOT NULL AND pers_fams != ''");
        $insertPersFam = $this->dbh->prepare("
            INSERT INTO humo_relations_persons (relation_id, relation_gedcomnumber, person_id, person_gedcomnumber, person_age, tree_id, relation_type, relation_order, partner_order)
            VALUES (:relation_id, :relation_gedcomnumber, :person_id, :person_gedcomnumber, :person_age, :tree_id, :relation_type, :relation_order, :partner_order)
        ");
        $this->dbh->beginTransaction();
        while ($pers = $persStmt->fetch(PDO::FETCH_ASSOC)) {
            $famIds = explode(';', $pers['pers_fams']);
            $order = 1;
            foreach ($famIds as $famGedcomNr) {
                // *** Find family ID by gedcomnumber and tree_id ***
                $famStmt = $this->dbh->prepare("SELECT fam_id, fam_gedcomnumber, fam_man, fam_man_age, fam_woman_age FROM humo_families
                    WHERE fam_tree_id = :tree_id AND fam_gedcomnumber = :gedcomnumber");
                $famStmt->execute([':tree_id' => $pers['pers_tree_id'], ':gedcomnumber' => $famGedcomNr]);
                $fam = $famStmt->fetch(PDO::FETCH_ASSOC);
                if ($fam) {
                    if ($pers['pers_gedcomnumber'] == $fam['fam_man']) {
                        $relation_type = 'partner';
                        $partner_order = 1; // man
                        $person_age = $fam['fam_man_age'];
                    } else {
                        $relation_type = 'partner';
                        $partner_order = 2; // woman
                        $person_age = $fam['fam_woman_age'];
                    }

                    $insertPersFam->execute([
                        ':relation_id' => $fam['fam_id'],
                        ':relation_gedcomnumber' => $fam['fam_gedcomnumber'],
                        ':person_id' => $pers['pers_id'],
                        ':person_gedcomnumber' => $pers['pers_gedcomnumber'],
                        ':person_age' => $person_age,
                        ':tree_id' => $pers['pers_tree_id'],
                        ':relation_type' => $relation_type,
                        ':relation_order' => $order,
                        ':partner_order' => $partner_order
                    ]);
                    $order++;
                }
            }
        }
        // *** Commit data in database ***
        $this->dbh->commit();

        // *** Move fam_children data to humo_relations_children table ***
        $famStmt = $this->dbh->query("SELECT fam_id, fam_gedcomnumber, fam_tree_id, fam_children FROM humo_families WHERE fam_children IS NOT NULL AND fam_children != ''");
        $insert = $this->dbh->prepare("
            INSERT INTO humo_relations_persons (tree_id, relation_id, relation_gedcomnumber, person_id, person_gedcomnumber, relation_type, relation_order)
            VALUES (:tree_id, :relation_id, :relation_gedcomnumber, :person_id, :person_gedcomnumber, :relation_type, :relation_order)
        ");
        $stmt = $this->dbh->prepare("SELECT pers_id, pers_gedcomnumber FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :gedcomnumber");
        $this->dbh->beginTransaction();
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
                        ':relation_gedcomnumber' => $fam['fam_gedcomnumber'],
                        ':person_id' => $pers['pers_id'],
                        ':person_gedcomnumber' => $pers['pers_gedcomnumber'],
                        ':relation_type' => 'child',
                        ':relation_order' => $order
                    ]);
                    $order++;
                }
            }
        }
        // *** Commit data in database ***
        $this->dbh->commit();

        $this->dbh->exec("
            ALTER TABLE humo_relations_persons
                ADD INDEX idx_relation_id (relation_id),
                ADD INDEX idx_relation_gedcomnumber (relation_gedcomnumber),
                ADD INDEX idx_person_id (person_id),
                ADD INDEX idx_person_gedcomnumber (person_gedcomnumber),
                ADD INDEX idx_relation_type (relation_type);
        ");

        // Add foreign key constraint after data migration to avoid integrity errors during migration.
        // *** Just to be sure: clean up values before adding foreign key constraint ***
        $this->dbh->exec("
            UPDATE humo_relations_persons
            SET relation_id = NULL
            WHERE relation_id IS NOT NULL
            AND relation_id NOT IN (SELECT fam_id FROM humo_families)
            ");
        try {
            $this->dbh->exec("
            ALTER TABLE humo_relations_persons
            ADD CONSTRAINT fk_relation_person_family
            FOREIGN KEY (relation_id) REFERENCES humo_families(fam_id)
            ON DELETE CASCADE ON UPDATE CASCADE
            ");
        } catch (\Exception $e) {
            // Just ignore, probably some invalid values are still in the table.
            printf('<br><b>' . __('Minor problem: constraint %s failed.') . '</b>', 'fk_relation_person_family');
        }

        // *** Just to be sure: clean up values before adding foreign key constraint ***
        $this->dbh->exec("
            UPDATE humo_relations_persons
            SET person_id = NULL
            WHERE person_id IS NOT NULL
            AND person_id NOT IN (SELECT pers_id FROM humo_persons)
            ");
        try {
            $this->dbh->exec("
            ALTER TABLE humo_relations_persons
            ADD CONSTRAINT fk_relation_person
            FOREIGN KEY (person_id) REFERENCES humo_persons(pers_id)
            ON DELETE CASCADE ON UPDATE CASCADE
            ");
        } catch (\Exception $e) {
            // Just ignore, probably some invalid values are still in the table.
            printf('<br><b>' . __('Minor problem: constraint %s failed.') . '</b>', 'fk_relation_person');
        }

        // *** Remove old fam_man, fam_wife, fam_children, pers_fams and pers_famc fields ***
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_man");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_man_age");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_woman");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_woman_age");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_children");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_fams");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_famc");
    }
}
