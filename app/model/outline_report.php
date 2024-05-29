<?php

class OutlineReportModel extends FamilyModel
{
    public function getShowDetails()
    {
        $show_details = false;
        if (isset($_GET["show_details"]) && is_numeric(($_GET["show_details"]))) {
            $show_details = $_GET["show_details"];
        }
        if (isset($_POST["show_details"]) && is_numeric($_POST["show_details"])) {
            $show_details = $_POST["show_details"];
        }
        return $show_details;
    }

    public function getShowDate()
    {
        $show_date = true;
        if (isset($_GET["show_date"]) && is_numeric($_GET["show_date"])) {
            $show_date = $_GET["show_date"];
        }
        if (isset($_POST["show_date"]) && is_numeric($_POST["show_date"])) {
            $show_date = $_POST["show_date"];
        }
        return $show_date;
    }

    public function getDatesBehindNames()
    {
        $dates_behind_names = true;
        if (isset($_GET["dates_behind_names"]) && is_numeric($_GET["dates_behind_names"])) {
            $dates_behind_names = $_GET["dates_behind_names"];
        }
        if (isset($_POST["dates_behind_names"]) && is_numeric($_POST["dates_behind_names"])) {
            $dates_behind_names = $_POST["dates_behind_names"];
        }
        return $dates_behind_names;
    }

    public function getNrGenerations($humo_option)
    {
        $nr_generations = ($humo_option["descendant_generations"] - 1);
        if (isset($_GET["nr_generations"]) && is_numeric($_GET["nr_generations"])) {
            $nr_generations = $_GET["nr_generations"];
        }
        if (isset($_POST["nr_generations"]) && is_numeric($_POST["nr_generations"])) {
            $nr_generations = $_POST["nr_generations"];
        }
        return $nr_generations;
    }
}
