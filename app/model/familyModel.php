<?php

/**
 * July 2023: refactor family script to MVC
 */

include_once(__DIR__ . '/../../include/language_date.php');
include_once(__DIR__ . '/../../include/language_event.php');
include_once(__DIR__ . '/../../include/date_place.php');
include_once(__DIR__ . '/../../include/process_text.php');
include_once(__DIR__ . '/../../include/show_sources.php');
include_once(__DIR__ . '/../../include/witness.php');
include_once(__DIR__ . '/../../include/show_addresses.php');
include_once(__DIR__ . '/../../include/showMedia.php');
include_once(__DIR__ . '/../../include/show_quality.php');

class FamilyModel
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function getFamilyId()
    {
        $family_id = 'F1'; // *** standard: show first family ***
        if (isset($_GET["id"])) {
            $family_id = $_GET["id"];
        }
        if (isset($_POST["id"])) {
            $family_id = $_POST["id"];
        }

        // *** A favourite ID is used ***
        if (isset($_POST["humo_favorite_id"])) {
            $favorite_array_id = explode("|", $_POST["humo_favorite_id"]);
            $family_id = $favorite_array_id[0];
        }

        return $family_id;
    }

    public function getMainPerson()
    {
        $main_person = ''; // *** Mainperson of a family ***
        if (isset($_GET["main_person"])) {
            $main_person = $_GET["main_person"];
        }
        if (isset($_POST["main_person"])) {
            $main_person = $_POST["main_person"];
        }

        // *** A favourite ID is used ***
        if (isset($_POST["humo_favorite_id"])) {
            $favorite_array_id = explode("|", $_POST["humo_favorite_id"]);
            $main_person = $favorite_array_id[1];
        }

        return $main_person;
    }

    // *** Compact or expanded view ***
    public function getFamilyExpanded()
    {
        // TODO remove global
        global $user;

        $family_expanded = 'compact'; // *** Default value ***

        // *** Default setting is selected by administrator ***
        if ($user['group_family_presentation'] == 'compact') {
            $family_expanded = 'compact';
        } elseif ($user['group_family_presentation'] == 'expanded' || $user['group_family_presentation'] == 'expanded1') {
            // expanded = backwards compatible only.
            $family_expanded = 'expanded1';
        } elseif ($user['group_family_presentation'] == 'expanded2') {
            $family_expanded = 'expanded2';
        }

        $check_array = array("compact", "expanded1", "expanded2");
        if (isset($_GET['family_expanded']) and in_array($_GET['family_expanded'], $check_array)) {
            $family_expanded = $_GET['family_expanded'];
            $_SESSION['save_family_expanded'] = $_GET['family_expanded'];
        }

        if (isset($_SESSION['save_family_expanded'])) {
            $family_expanded = $_SESSION['save_family_expanded'];
        }

        return $family_expanded;
    }

    // *** Source presentation selected by user, only valid values are: title/ footnote/ hide ***
    public function getSourcePresentation()
    {
        // TODO remove global
        global $user;
        $source_presentation_array = array('title', 'footnote', 'hide');
        if (isset($_GET['source_presentation']) && in_array($_GET['source_presentation'], $source_presentation_array)) {
            $_SESSION['save_source_presentation'] = $_GET["source_presentation"];
        }
        // *** Default setting is selected by administrator ***
        $source_presentation = $user['group_source_presentation'];
        if (isset($_SESSION['save_source_presentation']) && in_array($_SESSION['save_source_presentation'], $source_presentation_array)) {
            $source_presentation = $_SESSION['save_source_presentation'];
        } else {
            // *** Extra saving of setting in session (if no choice is made, this is admin default setting, needed for show_sources.php!!!) ***
            $_SESSION['save_source_presentation'] = safe_text_db($source_presentation);
        }
        return $source_presentation;
    }

    // *** Show/ hide pictures ***
    public function getPicturePresentation()
    {
        $picture_presentation = 'show';
        $picture_presentation_array = array('show', 'hide');
        if (isset($_GET['picture_presentation']) && in_array($_GET['picture_presentation'], $picture_presentation_array)) {
            $_SESSION['save_picture_presentation'] = $_GET["picture_presentation"];
        }
        // *** Default setting is selected by administrator ***
        if (isset($_SESSION['save_picture_presentation']) && in_array($_SESSION['save_picture_presentation'], $picture_presentation_array)) {
            $picture_presentation = $_SESSION['save_picture_presentation'];
        }
        return $picture_presentation;
    }

    // *** Show/ hide texts ***
    public function getTextPresentation()
    {
        // TODO remove global
        global $user;
        $text_presentation_array = array('show', 'hide', 'popup');
        if (isset($_GET['text_presentation']) && in_array($_GET['text_presentation'], $text_presentation_array)) {
            $_SESSION['save_text_presentation'] = $_GET["text_presentation"];
        }
        // *** Default setting is selected by administrator ***
        $text_presentation = $user['group_text_presentation'];
        if (isset($_SESSION['save_text_presentation']) && in_array($_SESSION['save_text_presentation'], $text_presentation_array)) {
            $text_presentation = $_SESSION['save_text_presentation'];
        }
        return $text_presentation;
    }

    // *** Define numbers (max. 60 generations) ***
    //TODO this is also defined in ancestor script.
    public function getNumberRoman()
    {
        return array(
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
            11 => 'XI', 12 => 'XII', 13 => 'XIII', 14 => 'XIV', 15 => 'XV', 16 => 'XVI', 17 => 'XVII', 18 => 'XVIII', 19 => 'XIX', 20 => 'XX',
            21 => 'XXI', 22 => 'XXII', 23 => 'XXIII', 24 => 'XXIV', 25 => 'XXV', 26 => 'XXVII', 27 => 'XXVII', 28 => 'XXVIII', 29 => 'XXIX', 30 => 'XXX',
            31 => 'XXXI', 32 => 'XXXII', 33 => 'XXXIII', 34 => 'XXXIV', 35 => 'XXXV', 36 => 'XXXVII', 37 => 'XXXVII', 38 => 'XXXVIII', 39 => 'XXXIX', 40 => 'XL',
            41 => 'XLI', 42 => 'XLII', 43 => 'XLIII', 44 => 'XLIV', 45 => 'XLV', 46 => 'XLVII', 47 => 'XLVII', 48 => 'XLVIII', 49 => 'XLIX', 50 => 'L',
            51 => 'LI',  52 => 'LII',  53 => 'LIII',  54 => 'LIV',  55 => 'LV',  56 => 'LVII',  57 => 'LVII',  58 => 'LVIII',  59 => 'LIX',  60 => 'LX',
        );
    }

    // *** Generate array: a, b, c .. z, aa, ab .. zz
    public function getNumberGeneration()
    {
        // a-z
        $number_generation[] = ''; // (1st number_generation is not used)
        for ($i = 1; $i <= 26; $i++) {
            $number_generation[] = chr($i + 96); //chr(97)=a
        }
        // aa, ab, ac .. az, ba, bb, bc .. bz, zz
        //for ($i = 1; $i <= 676; $i++) {
        for ($i = 1; $i <= 26; $i++) {
            for ($j = 1; $j <= 26; $j++) {
                $number_generation[] = chr($i + 96) . chr($j + 96); //chr(97)=a
            }
        }
        return $number_generation;
    }

    public function getDescendantReport()
    {
        $descendant_report = false;
        if (isset($_GET['descendant_report'])) {
            $descendant_report = true;
        }
        if (isset($_POST['descendant_report'])) {
            $descendant_report = true;
        }
        return $descendant_report;
    }

    function getDescendantHeader($name, $tree_id, $family_id, $main_person)
    {
        // TODO remove global
        global $humo_option, $link_cls, $uri_path;

        $data['header_active'] = array();
        $data['header_link'] = array();
        $data['header_text'] = array();

        $vars['pers_family'] = $family_id;
        $path_tmp = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        $path_tmp .= "main_person=" . $main_person . '&amp;descendant_report=1';
        $data['header_link'][] = $path_tmp;
        $data['header_active'][] = $name == 'Descendant report' ? 'active' : '';
        $data['header_text'][] = __('Descendant report');

        if (isset($_GET['dnachart'])) $name='DNA charts';

        if ($humo_option["url_rewrite"] == 'j') {
            $link = 'descendant_chart/' . $tree_id . '/' . $family_id . '?main_person=' . $main_person;
        } else {
            $link = 'index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person;
        }
        $data['header_link'][] = $link;
        $data['header_active'][] = $name == 'Descendant chart' ? 'active' : '';
        $data['header_text'][] = __('Descendant chart');

        // *** Added in july 2024 ***
        if ($humo_option["url_rewrite"] == 'j') {
            $link = 'descendant_chart/' . $tree_id . '/' . $family_id . '?main_person=' . $main_person.'&amp;dnachart=mtdna';
        } else {
            $link = 'index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person.'&amp;dnachart=mtdna';
        }
        $data['header_link'][] = $link;
        $data['header_active'][] = $name == 'DNA charts' ? 'active' : '';
        $data['header_text'][] = __('DNA Charts');

        $path_tmp = $link_cls->get_link($uri_path, 'outline_report', $tree_id, true);
        $path_tmp .= 'id=' . $family_id . '&amp;main_person=' . $main_person;
        $data['header_link'][] = $path_tmp;
        $data['header_active'][] = $name == 'Outline report' ? 'active' : '';
        $data['header_text'][] = __('Outline report');

        // TODO Move to view? Is used in multiple views.
        // *** Tab menu ***
        $text = '
        <h1>' . __('Descendants') . '</h1>
        <ul class="nav nav-tabs d-print-none" id="nav-tab">
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][0] . '" href="' . $data['header_link'][0] . '">' . $data['header_text'][0] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][1] . '" href="' . $data['header_link'][1] . '">' . $data['header_text'][1] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][2] . '" href="' . $data['header_link'][2] . '">' . $data['header_text'][2] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][3] . '" href="' . $data['header_link'][3] . '">' . $data['header_text'][3] . '</a>
            </li>
        </ul>
        <!-- Align content to the left -->
        <!-- <div style="float: left; background-color:white; height:500px; padding:10px;"> -->
        <div style="float: left; background-color:white; padding:10px;">';

        return $text;
    }

    // *** Used in family script: show/ hide Google maps ***
    function getMapsPresentation()
    {
        // TODO remove global
        global $dbh, $user;

        // *** Default setting is selected by administrator ***
        $maps_presentation = $user['group_maps_presentation'];

        $maps_presentation_array = array('show', 'hide');
        if (isset($_GET['maps_presentation']) && in_array($_GET['maps_presentation'], $maps_presentation_array)) {
            $_SESSION['save_maps_presentation'] = $_GET["maps_presentation"];
            $maps_presentation = $_GET["maps_presentation"];
        }

        // *** If session is used, read variable ***
        if (isset($_SESSION['save_maps_presentation']) && in_array($_SESSION['save_maps_presentation'], $maps_presentation_array)) {
            $maps_presentation = $_SESSION['save_maps_presentation'];
        }

        // *** Only show selection if there is a Google maps database ***
        // TODO maybe count valid locations in table.
        //$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
        //if (!$temp->rowCount()) {
        //    $maps_presentation = 'hide';
        //}
        return $maps_presentation;
    }
}
