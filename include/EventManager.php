<?php

/**
 * Aug. 2025 Huub: created general EventManager.
 */

namespace Genealogy\Include;

use PDO;

class EventManager
{
    private $dbh, $db_functions, $userid;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
        $this->db_functions = new DbFunctions($dbh);

        $this->userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $this->userid = $_SESSION['user_id_admin'];
        }
    }

    // *** Add event ***
    public function update_event($data): void
    {
        $processPlaceId = new ProcessPlaceId($this->dbh);
        $parseGedcomDate = new ParseGedcomDate();



        // TODO: check if all data fields are empty if update is done, if empty: remove the event.


        // Prepare date fields
        $event_date = $data['event_date'] ?? '';
        $event_place_lat = $data['event_place_lat'] ?? NULL;
        $event_place_lon = $data['event_place_lon'] ?? NULL;

        $parsed = $event_date ? $parseGedcomDate->parse($event_date) : ['year' => null, 'month' => null, 'day' => null];

        $event_place_id = isset($data['event_place']) ? $processPlaceId->get_id($data['event_place'], $event_place_lat, $event_place_lon) : null;
        $event_end_date = isset($data['event_end_date']) ? $data['event_end_date'] : null;
        $event_authority = isset($data['event_authority']) ? $data['event_authority'] : null;
        $event_date_hebnight = isset($data['event_date_hebnight']) ? $data['event_date_hebnight'] : '';

        // *** Generate new order number ***
        if (!isset($data['event_id'])) {
            // *** Default value for new event ***
            //$event_order = 1;

            // Also check event_gedcom?
            $event_sql = "SELECT * FROM humo_events
                WHERE event_tree_id = :tree_id
                AND event_connect_kind = :event_connect_kind
                AND event_connect_id = :event_connect_id
                AND event_kind = :event_kind
                ORDER BY event_order DESC LIMIT 0,1";
            $event_qry = $this->dbh->prepare($event_sql);
            $event_qry->bindValue(':tree_id', $data['tree_id'], PDO::PARAM_STR);
            $event_qry->bindValue(':event_connect_kind', $data['event_connect_kind'], PDO::PARAM_STR);
            $event_qry->bindValue(':event_connect_id', $data['event_connect_id'], PDO::PARAM_STR);
            $event_qry->bindValue(':event_kind', $data['event_kind'], PDO::PARAM_STR);
            $event_qry->execute();
            $eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

            if (isset($eventDb->event_order)) {
                $event_order = $eventDb->event_order;
                $event_order++;
            } else {
                $event_order = 1;
            }
        }

        /*
        // TODO Other method to check all $data items
        // Build assignments and bind only present params
        $assignments = [];
        foreach ($columns as $idx => $column) {
            $param = $values[$idx];
            // Only add if param is set in $data or is a required field
            $param_name = ltrim($param, ':');
            if (isset($data[$param_name]) || in_array($param_name, [
                'tree_id','event_date','event_date_hebnight','event_date_year','event_date_month','event_date_day',
                'event_place_id','event_text','event_order','event_new_user_id'
            ])) {
                $assignments[] = "$column = $param";
            }
        }
        $sql = "UPDATE humo_events SET " . implode(', ', $assignments) . " WHERE event_id = :event_id";
        */


        // Build the SQL dynamically to include event_person_id or event_relation_id
        $columns = [
            'event_tree_id',
            'event_place_id',
            'event_new_user_id',
            'event_date',
            'event_end_date',
            'event_date_year',
            'event_date_month',
            'event_date_day',
            'event_date_hebnight',
            'event_authority'
        ];
        $values = [
            ':tree_id',
            ':event_place_id',
            ':event_new_user_id',
            ':event_date',
            ':event_end_date',
            ':event_date_year',
            ':event_date_month',
            ':event_date_day',
            ':event_date_hebnight',
            ':event_authority'
        ];

        // *** Data fields for new event ***
        if (!isset($data['event_id'])) {
            $columns2 = [
                'event_order',
                'event_gedcomnr',
                'event_connect_kind',
                'event_connect_id',
                'event_kind',
                'event_event_extra'
            ];
            $columns = array_merge($columns, $columns2);

            $values2 = [
                ':event_order',
                ':event_gedcomnr',
                ':event_connect_kind',
                ':event_connect_id',
                ':event_kind',
                ':event_event_extra'
            ];
            $values = array_merge($values, $values2);

            // If event_connect_kind = person, add event_person_id. If it's family, add event_relation_id
            if ($data['event_connect_kind'] === 'person') {
                $columns[] = 'event_person_id';
                $values[] = ':event_person_id';
            } elseif ($data['event_connect_kind'] === 'family') {
                $columns[] = 'event_relation_id';
                $values[] = ':event_relation_id';
            }
        }

        if (isset($data['event_event'])) {
            $columns[] = 'event_event';
            $values[] = ':event_event';
        }
        if (isset($data['event_gedcom'])) {
            $columns[] = 'event_gedcom';
            $values[] = ':event_gedcom';
        }

        if (isset($data['event_connect_kind2'])) {
            $columns[] = 'event_connect_kind2';
            $values[] = ':event_connect_kind2';
        }
        if (isset($data['event_connect_id2'])) {
            $columns[] = 'event_connect_id2';
            $values[] = ':event_connect_id2';
        }

        if (isset($data['event_time'])) {
            $columns[] = 'event_time';
            $values[] = ':event_time';
        }
        if (isset($data['event_stillborn'])) {
            $columns[] = 'event_stillborn';
            $values[] = ':event_stillborn';
        }
        if (isset($data['event_cause'])) {
            $columns[] = 'event_cause';
            $values[] = ':event_cause';
        }
        if (isset($data['event_pers_age'])) {
            $columns[] = 'event_pers_age';
            $values[] = ':event_pers_age';
        }
        if (isset($data['event_cremation'])) {
            $columns[] = 'event_cremation';
            $values[] = ':event_cremation';
        }
        if (isset($data['event_text'])) {
            $columns[] = 'event_text';
            $values[] = ':event_text';
        }


        // *** For update query, add changed user id ***
        if (isset($data['event_changed_user_id'])) {
            $columns[] = 'event_changed_user_id';
            $values[] = ':event_changed_user_id';
        }

        // *** Update event ***
        if (isset($data['event_id'])) {
            // Build assignments like column = :param for each column
            $assignments = [];
            foreach ($columns as $idx => $column) {
                $param = $values[$idx];
                $assignments[] = "$column = $param";
            }
            $sql = "UPDATE humo_events SET " . implode(', ', $assignments) . " WHERE event_id = :event_id";
        } else {
            // *** New event ***
            $sql = "INSERT INTO humo_events (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
        }

        $stmt = $this->dbh->prepare($sql);

        // *** New event: try to get event_person_id ***
        if (!isset($data['event_id'])) {
            if ($data['event_connect_kind'] === 'person') {
                if (isset($data['event_person_id'])) {
                    $stmt->bindValue(':event_person_id', $data['event_person_id'], PDO::PARAM_INT);
                } else {
                    // *** Get person_id ***
                    $this->db_functions->set_tree_id($data['tree_id']);

                    $person = $this->db_functions->get_person($data['event_connect_id']);
                    $stmt->bindValue(':event_person_id', isset($person->pers_id) ? $person->pers_id : null, PDO::PARAM_INT);
                }
            } elseif ($data['event_connect_kind'] === 'family') {
                if (isset($data['event_relation_id'])) {
                    $stmt->bindValue(':event_relation_id', $data['event_relation_id'], PDO::PARAM_INT);
                } else {
                    // *** Get relation_id ***
                    $this->db_functions->set_tree_id($data['tree_id']);

                    $family = $this->db_functions->get_family($data['event_connect_id']);
                    $stmt->bindValue(':event_relation_id', isset($family->fam_id) ? $family->fam_id : null, PDO::PARAM_INT);
                }
            }
        }

        // *** Option to change event type and event name of existing event ***
        if (isset($data['event_event'])) {
            $stmt->bindValue(':event_event', $data['event_event'], PDO::PARAM_STR);
        }
        if (isset($data['event_gedcom'])) {
            $stmt->bindValue(':event_gedcom', $data['event_gedcom'], PDO::PARAM_STR);
        }

        if (isset($data['event_connect_kind2'])) {
            $stmt->bindValue(':event_connect_kind2', $data['event_connect_kind2'], PDO::PARAM_STR);
        }
        if (isset($data['event_connect_id2'])) {
            $stmt->bindValue(':event_connect_id2', $data['event_connect_id2'], PDO::PARAM_INT);
        }

        //echo $sql.' '.$family->fam_id.' '.$data['event_connect_id'];

        if (isset($data['event_time'])) {
            $stmt->bindValue(':event_time', $data['event_time'], PDO::PARAM_STR);
        }
        if (isset($data['event_stillborn'])) {
            $stmt->bindValue(':event_stillborn', $data['event_stillborn'], PDO::PARAM_STR);
        }
        if (isset($data['event_cause'])) {
            $stmt->bindValue(':event_cause', $data['event_cause'], PDO::PARAM_STR);
        }
        if (isset($data['event_pers_age'])) {
            $stmt->bindValue(':event_pers_age', $data['event_pers_age'], PDO::PARAM_INT);
        }
        if (isset($data['event_cremation'])) {
            $stmt->bindValue(':event_cremation', $data['event_cremation'], PDO::PARAM_STR);
        }
        if (isset($data['event_text'])) {
            $stmt->bindValue(':event_text', $data['event_text'], PDO::PARAM_STR);
        }
        $stmt->bindValue(':tree_id', $data['tree_id'], PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $event_date, PDO::PARAM_STR);
        $stmt->bindValue(':event_end_date', $event_end_date, PDO::PARAM_STR);
        $stmt->bindValue(':event_date_hebnight', $event_date_hebnight, PDO::PARAM_STR);
        $stmt->bindValue(':event_authority', $event_authority, PDO::PARAM_STR);
        $stmt->bindValue(':event_date_year', $parsed['year'], PDO::PARAM_INT);
        $stmt->bindValue(':event_date_month', $parsed['month'], PDO::PARAM_INT);
        $stmt->bindValue(':event_date_day', $parsed['day'], PDO::PARAM_INT);
        $stmt->bindValue(':event_place_id', $event_place_id, PDO::PARAM_INT);
        $stmt->bindValue(':event_new_user_id', $this->userid, PDO::PARAM_STR);
        if (isset($data['event_changed_user_id'])) {
            $stmt->bindValue(':event_changed_user_id', $data['event_changed_user_id'], PDO::PARAM_INT);
        }

        if (isset($data['event_id'])) {
            $stmt->bindValue(':event_id', $data['event_id'], PDO::PARAM_INT);
        } else {
            if (!isset($data['event_gedcomnr'])) {
                $data['event_gedcomnr'] = NULL;
            }
            if (!isset($data['event_event_extra'])) {
                $data['event_event_extra'] = NULL;
            }

            $stmt->bindValue(':event_order', $event_order, PDO::PARAM_INT);
            $stmt->bindValue(':event_gedcomnr', $data['event_gedcomnr'], PDO::PARAM_STR);
            $stmt->bindValue(':event_connect_kind', $data['event_connect_kind'], PDO::PARAM_STR);
            $stmt->bindValue(':event_connect_id', $data['event_connect_id'], PDO::PARAM_STR);
            $stmt->bindValue(':event_kind', $data['event_kind'], PDO::PARAM_STR);
            //$stmt->bindValue(':event_event', $data['event_event'], PDO::PARAM_STR);
            $stmt->bindValue(':event_event_extra', $data['event_event_extra'], PDO::PARAM_STR);
            $stmt->bindValue(':event_gedcom', $data['event_gedcom'], PDO::PARAM_STR);
        }

        $stmt->execute();

        //return $this->dbh->lastInsertId();
    }
}
