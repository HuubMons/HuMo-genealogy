<?php

/**
 * July 2023: refactor family.php to MVC
 * Will be improved over time...
 * 
 */

class Family
{
    private $Connection;

    public function __construct($Connection)
    {
        $this->Connection = $Connection;

        include_once(__DIR__ . '/../include/language_date.php');
        include_once(__DIR__ . '/../include/language_event.php');
        include_once(__DIR__ . '/../include/date_place.php');
        include_once(__DIR__ . '/../include/process_text.php');
        include_once(__DIR__ . '/../include/calculate_age_cls.php');
        include_once(__DIR__ . '/../include/person_cls.php');
        include_once(__DIR__ . '/../include/marriage_cls.php');
        include_once(__DIR__ . '/../include/show_sources.php');
        include_once(__DIR__ . '/../include/witness.php');
        include_once(__DIR__ . '/../include/show_addresses.php');
        include_once(__DIR__ . '/../include/show_picture.php');
        include_once(__DIR__ . '/../include/show_quality.php');
    }

    public function getFamilyId()
    {
        $family_id = 'F1'; // *** standard: show first family ***
        //if (isset($urlpart[1])){ $family_id=$urlpart[1]; }
        if (isset($_GET["id"])) {
            $family_id = $_GET["id"];
        }
        if (isset($_POST["id"])) {
            $family_id = $_POST["id"];
        }
        return $family_id;
    }

    public function getMainPerson()
    {
        $main_person = ''; // *** Mainperson of a family ***
        //if (isset($urlpart[2])){ $main_person=$urlpart[2]; }
        if (isset($_GET["main_person"])) {
            $main_person = $_GET["main_person"];
        }
        if (isset($_POST["main_person"])) {
            $main_person = $_POST["main_person"];
        }
        return $main_person;
    }

    // *** Compact or expanded view ***
    public function getFamilyExpanded()
    {
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
        //global $user;
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
}
