<?php

/**
 * Aug. 2025 Huub & AI: Helper class for managing places in the genealogy database.
 */

namespace Genealogy\Include;

use PDO;

class ProcessPlaceId
{
    private $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Get the location_id for a place name, or add it if it doesn't exist.
     * @param string $place_name
     * @return int location_id
     */
    public function get_id($place_name, $latitude = NULL, $longitude = NULL): ?int
    {
        if (empty($place_name)) {
            return null;
        }

        // Try to find the place
        $sql = "SELECT location_id, location_lat, location_lng FROM humo_location WHERE location_location = :place_name LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':place_name', $place_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['location_id'])) {
            // *** Update latitude and longitude if available ***
            if (
                $row['location_lat'] == NULL && $row['location_lng'] == NULL && $latitude !== NULL && $latitude != '' && $longitude !== NULL && $longitude != ''
            ) {
                //echo $row['location_location'].'!'.$latitude.'!'.$longitude.'!<br>';
                $sql = "UPDATE humo_location SET location_lat = :latitude, location_lng = :longitude WHERE location_id = :location_id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
                $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
                $stmt->bindValue(':location_id', $row['location_id'], PDO::PARAM_INT);
                $stmt->execute();
            }

            // *** Found location_id, return the ID ***
            return (int)$row['location_id'];
        }

        // Not found, insert new place
        if ($latitude !== NULL && $latitude != '' && $longitude !== NULL && $longitude != '') {
            $sql = "INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES (:place_name, :latitude, :longitude)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':place_name', $place_name, PDO::PARAM_STR);
            $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
            $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "INSERT INTO humo_location (location_location) VALUES (:place_name)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':place_name', $place_name, PDO::PARAM_STR);
            $stmt->execute();
        }

        return (int)$this->dbh->lastInsertId();
    }
}
