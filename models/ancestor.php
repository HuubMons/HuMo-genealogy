<?php

/**
 * July 2023: refactor ancestor.php to MVC
 * Will be improved over time...
 * 
 */

class Ancestor
{
    private $Connection;

    public function __construct($Connection)
    {
        $this->Connection = $Connection;

        include_once(__DIR__ . '/../include/language_date.php');
        include_once(__DIR__ . '/../include/language_event.php');
        include_once(__DIR__ . '/../include/calculate_age_cls.php');
        include_once(__DIR__ . '/../include/person_cls.php');
        include_once(__DIR__ . '/../include/witness.php');
        // Needed for marriage:
        include_once(__DIR__ . '/../include/process_text.php');
        include_once(__DIR__ . '/../include/date_place.php');
        include_once(__DIR__ . '/../include/marriage_cls.php');
        include_once(__DIR__ . '/../include/show_sources.php');
        include_once(__DIR__ . '/../include/show_addresses.php');
        include_once(__DIR__ . '/../include/show_picture.php');
        include_once(__DIR__ . '/../include/show_quality.php');
    }

    // *** Define numbers (max. 60 generations) ***
    //TODO this is also defined in family.php.
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
}
