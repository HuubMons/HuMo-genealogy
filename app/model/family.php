<?php

/**
 * July 2023: refactor family script to MVC
 */

include_once(__DIR__ . '/../../include/language_date.php');
include_once(__DIR__ . '/../../include/language_event.php');
include_once(__DIR__ . '/../../include/date_place.php');
include_once(__DIR__ . '/../../include/process_text.php');
include_once(__DIR__ . '/../../include/calculate_age_cls.php');
include_once(__DIR__ . '/../../include/person_cls.php');
include_once(__DIR__ . '/../../include/marriage_cls.php');
include_once(__DIR__ . '/../../include/show_sources.php');
include_once(__DIR__ . '/../../include/witness.php');
include_once(__DIR__ . '/../../include/show_addresses.php');
include_once(__DIR__ . '/../../include/show_picture.php');
include_once(__DIR__ . '/../../include/show_quality.php');



//TEST to use multiple functions in multiple classes
//https://www.w3schools.com/php/php_oop_traits.asp
//https://wiki.php.net/rfc/traits
/*
trait Hello {
    public function sayHello() {
      echo 'Hello ';
    }
  }
  
  trait World {
    public function sayWorld() {
      echo ' World';
    }
  }
*/

class FamilyModel
{
    private $dbh;

    // TEST
    //use Hello, World;


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
        if (isset($_GET['family_expanded'])) {
            if ($_GET['family_expanded'] == '0') $_SESSION['save_family_expanded'] = '0';
            else $_SESSION['save_family_expanded'] = '1';
        }
        // *** Default setting is selected by administrator ***
        if ($user['group_family_presentation'] == 'expanded') {
            $family_expanded = true;
        } else {
            $family_expanded = false;
        }
        if (isset($_SESSION['save_family_expanded'])) $family_expanded = $_SESSION['save_family_expanded'];
        return $family_expanded;
    }

    // *** Source presentation selected by user, only valid values are: title/ footnote/ hide ***
    public function getSourcePresentation()
    {
        // TODO remove global
        global $user;
        $source_presentation_array = array('title', 'footnote', 'hide');
        if (isset($_GET['source_presentation']) and in_array($_GET['source_presentation'], $source_presentation_array)) {
            $_SESSION['save_source_presentation'] = $_GET["source_presentation"];
        }
        // *** Default setting is selected by administrator ***
        $source_presentation = $user['group_source_presentation'];
        if (isset($_SESSION['save_source_presentation']) and in_array($_SESSION['save_source_presentation'], $source_presentation_array)) {
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
        if (isset($_GET['picture_presentation']) and in_array($_GET['picture_presentation'], $picture_presentation_array)) {
            $_SESSION['save_picture_presentation'] = $_GET["picture_presentation"];
        }
        // *** Default setting is selected by administrator ***
        //$picture_presentation=$user['group_picture_presentation'];
        if (isset($_SESSION['save_picture_presentation']) and in_array($_SESSION['save_picture_presentation'], $picture_presentation_array)) {
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
        if (isset($_GET['text_presentation']) and in_array($_GET['text_presentation'], $text_presentation_array)) {
            $_SESSION['save_text_presentation'] = $_GET["text_presentation"];
        }
        // *** Default setting is selected by administrator ***
        $text_presentation = $user['group_text_presentation'];
        if (isset($_SESSION['save_text_presentation']) and in_array($_SESSION['save_text_presentation'], $text_presentation_array)) {
            $text_presentation = $_SESSION['save_text_presentation'];
        }
        return $text_presentation;
    }

    // *** Define numbers (max. 60 generations) ***
    //TODO this is also defined in ancestor script.
    public function getNumberRoman()
    {
        $number_roman = array(
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
            11 => 'XI', 12 => 'XII', 13 => 'XIII', 14 => 'XIV', 15 => 'XV', 16 => 'XVI', 17 => 'XVII', 18 => 'XVIII', 19 => 'XIX', 20 => 'XX',
            21 => 'XXI', 22 => 'XXII', 23 => 'XXIII', 24 => 'XXIV', 25 => 'XXV', 26 => 'XXVII', 27 => 'XXVII', 28 => 'XXVIII', 29 => 'XXIX', 30 => 'XXX',
            31 => 'XXXI', 32 => 'XXXII', 33 => 'XXXIII', 34 => 'XXXIV', 35 => 'XXXV', 36 => 'XXXVII', 37 => 'XXXVII', 38 => 'XXXVIII', 39 => 'XXXIX', 40 => 'XL',
            41 => 'XLI', 42 => 'XLII', 43 => 'XLIII', 44 => 'XLIV', 45 => 'XLV', 46 => 'XLVII', 47 => 'XLVII', 48 => 'XLVIII', 49 => 'XLIX', 50 => 'L',
            51 => 'LI',  52 => 'LII',  53 => 'LIII',  54 => 'LIV',  55 => 'LV',  56 => 'LVII',  57 => 'LVII',  58 => 'LVIII',  59 => 'LIX',  60 => 'LX',
        );
        return $number_roman;
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

        $data['active']=array();
        $data['link']=array();

        //$text = '<h1 class="standard_header">';
        $vars['pers_family'] = $family_id;
        $path_tmp = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        $path_tmp .= "main_person=" . $main_person . '&amp;descendant_report=1';
        $data['link'][] = $path_tmp;
        if ($name == 'Descendant report') {
            //$text .= __($name);
            $data['active'][]='active';
        } else {
            //$text .= '<span style="font-weight: normal; font-size:70%; color:blue;"><a href="' . $path_tmp . '">' . __('Descendant report') . '</a></span>';
            $data['active'][]='';
        }

        //$text .= ' | ';

        if ($name == 'Descendant chart') {
            //$text .= __($name);
            $data['active'][]='active';
        } else {
            if ($humo_option["url_rewrite"] == 'j') {
                //$text .= '<span style="font-weight: normal; font-size:70%; color:blue;"><a href="descendant_chart/' . $tree_id . '/' . $family_id . '?main_person=' . $main_person . '">' . __('Descendant chart') . '</a></span>';
            } else {
                //$text .= '<span style="font-weight: normal; font-size:70%; color:blue;"><a href="descendant.php?tree_id=' . $tree_id . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '">' . __('Descendant chart') . '</a></span>';
                //$text .= '<span style="font-weight: normal; font-size:70%; color:blue;"><a href="index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '">' . __('Descendant chart') . '</a></span>';
            }
            $data['active'][]='';
        }
        $data['link'][] = 'index.php?page=descendant_chart&amp;tree_id=' . $tree_id . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person;

        //$text .= ' | ';

        $path_tmp = $link_cls->get_link($uri_path, 'outline_report', $tree_id, true);
        $path_tmp .= 'id=' . $family_id . '&amp;main_person=' . $main_person;
        $data['link'][] = $path_tmp;
        if ($name == 'Outline report') {
            //$text .= __($name);
            $data['active'][]='active';
        } else {
            //$text .= '<span style="font-weight: normal; font-size:70%; color:blue;"><a href="' . $path_tmp . '">' . __('Outline report') . '</a></span>';
            $data['active'][]='';
        }

        //$text .= '</h1>';

        // *** Tab menu ***
        $text = '
        <h1>' . __('Descendants') . '</h1>
        <ul class="nav nav-tabs">   
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['active'][0] . '" href="' . $data['link'][0] . '">' . __('Descendant report') . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['active'][1] . '" href="' . $data['link'][1] . '">' . __('Descendant chart') . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['active'][2] . '" href="' . $data['link'][2] . '">' . __('Outline report') . '</a>
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
        if (isset($_GET['maps_presentation']) and in_array($_GET['maps_presentation'], $maps_presentation_array)) {
            $_SESSION['save_maps_presentation'] = $_GET["maps_presentation"];
            $maps_presentation = $_GET["maps_presentation"];
        }

        // *** If session is used, read variable ***
        if (isset($_SESSION['save_maps_presentation']) and in_array($_SESSION['save_maps_presentation'], $maps_presentation_array)) {
            $maps_presentation = $_SESSION['save_maps_presentation'];
        }

        // *** Only show selection if there is a Google maps database ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
        if (!$temp->rowCount()) {
            $maps_presentation = 'hide';
        }
        return $maps_presentation;
    }
}
