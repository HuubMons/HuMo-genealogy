<?php

require __DIR__ . '/../database_function.php';

class model_event extends database_function
{
    /**
     * Get a single event from database.
     */
    public function get_event(int $event_id): object
    {
        $sql = "SELECT * FROM humo_events WHERE event_id=:event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_id' => $event_id
        ]);

        if ($event = $stmt->fetch(PDO::FETCH_OBJ)) {
            return $event;
        }

        throw new Exception("No event found with id $event_id", 1);
    }

    /**
     * Get all selected events from database.
     */
    public function get_events_kind(string $event_event, string $event_kind): array
    {
        $sql = "SELECT * FROM humo_events 
                WHERE event_tree_id=:event_tree_id 
                AND event_event=:event_event 
                AND event_kind=:event_kind 
                ORDER BY event_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_tree_id' => $this->tree_id,
            ':event_event' => $event_event,
            ':event_kind' => $event_kind
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all selected events by a person, family etc. 
     */
    public function get_events_connect(string $event_connect_kind, string $event_connect_id, string $event_kind): array
    {
        $sql = "SELECT * FROM humo_events
        		WHERE event_tree_id=:event_tree_id 
                AND event_connect_kind=:event_connect_kind
        		AND event_connect_id=:event_connect_id 
                AND event_kind=:event_kind 
                ORDER BY event_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_tree_id' => $this->tree_id,
            ':event_connect_kind' => $event_connect_kind,
            ':event_connect_id' => $event_connect_id,
            ':event_kind' => $event_kind
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
