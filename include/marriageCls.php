<?php

/**
 * Proces marriage data
 * Class for HuMo-genealogy program
 */

class MarriageCls
{
    public $cls_marriage_Db = null;  // Database relation record
    public $privacy = false;  // Relation privacy filter

    private $marriage_check = false;
    private $relation_kind = ''; // Living together, non mariatal, etc.
    private $relation_check = false; // true = relation/ not married.
    private $addition = ''; // Add (married) "to" in marriage text.

    public function __construct($familyDb = null, $privacy_man = null, $privacy_woman = null)
    {
        $this->cls_marriage_Db = $familyDb; // Database relation record
        $this->privacy = $this->set_privacy($privacy_man, $privacy_woman); // Set relation privacy
    }

    /**
     * Relation privacy filter
     * If man OR woman privacy filter is set
     */
    public function set_privacy($privacy_man, $privacy_woman)
    {
        global $user;
        $privacy_marriage = false;
        if ($user["group_privacy"] == 'n') {
            if ($privacy_man) {
                $privacy_marriage = true;
            }
            if ($privacy_woman) {
                $privacy_marriage = true;
            }
        }
        return $privacy_marriage;
    }

    private function check_relation_type($fam_kind)
    {
        if ($fam_kind == 'living together') {
            $this->relation_check = true;
            $this->relation_kind = __('Living together');
        }
        if ($fam_kind == 'living apart together') {
            $this->relation_kind = __('Living apart together');
            $this->relation_check = true;
        }
        if ($fam_kind == 'intentionally unmarried mother') {
            $this->relation_kind = __('Intentionally unmarried mother');
            $this->relation_check = true;
            $this->addition = '';
        }
        if ($fam_kind == 'homosexual') {
            $this->relation_check = true;
            $this->relation_kind = __('Homosexual');
        }
        if ($fam_kind == 'non-marital') {
            $this->relation_check = true;
            $this->relation_kind = __('Non marital');
            $this->addition = '';
        }
        if ($fam_kind == 'extramarital') {
            $this->relation_check = true;
            $this->relation_kind = __('Extramarital');
            $this->addition = '';
        }

        // Not tested
        if ($fam_kind == "PRO-GEN") {
            $this->relation_check = true;
            $this->relation_kind = __('Extramarital');
            $this->addition = '';
        }

        // *** Aldfaer relations ***
        if ($fam_kind == 'partners') {
            $this->relation_check = true;
            $this->relation_kind = __('Partner') . ' ';
        }
        if ($fam_kind == 'registered') {
            $this->relation_check = true;
            $this->relation_kind = __('Registered partnership') . ' ';
        }
        if ($fam_kind == 'unknown') {
            $this->relation_check = true;
            $this->relation_kind = __('Unknown relation') . ' ';
        }
    }

    private function get_living_together($marriageDb, $user, $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // *** Living together ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_relation_date || $marriageDb->fam_relation_place) {
            // TODO check these variables.
            $templ_relation["cohabit_date"] = date_place($marriageDb->fam_relation_date, $marriageDb->fam_relation_place);
            $temp = "cohabit_date";
            $temp_text .= $templ_relation["cohabit_date"];
        }
        if ($user["group_texts_fam"] == 'j' and process_text($marriageDb->fam_relation_text)) {
            if ($temp_text) {
                //$temp_text.= ', ';
                //if($temp) { $templ_relation[$temp].=", "; }
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= ' ';
                }
            }
            //$templ_relation["cohabit_text"]=strip_tags(process_text($marriageDb->fam_relation_text));
            $templ_relation["cohabit_text"] = process_text($marriageDb->fam_relation_text);
            $temp = "cohabit_text";
            $temp_text .= $templ_relation["cohabit_text"];
        }

        // *** Living together source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_relation_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["cohabit_source"] = $source_array['text'];
                    $temp = "cohabit_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                // TODO check this. Maybe templ_relation?
                $templ_relation["cohabit_add"] = '';
                $temp = "cohabit_add";
            }
        }

        if ($temp_text) {
            $relation_check = true;
            $this->addition = __(' to: ');
            if ($text !== '') {
                $text .= "<br>\n";
                $templ_relation["cohabit_exist"] = "\n";
            }
            // *** Text "living together" already shown in "kind" ***
            // *** Just in case made an extra text "living together" here ***
            //if (!$this->relation_kind) {
            //if ($marriageDb->fam_kind != 'living together') {
            $text .= '<b>' . __('Living together') . '</b>';
            if (isset($templ_relation["cohabit_exist"])) {
                $templ_relation["cohabit_exist"] .= __('Living together') . " ";
            } else {
                $templ_relation["cohabit_exist"] = __('Living together') . " ";
            }
            //}
            $text .= ' ' . $temp_text;
        }

        // *** End of living together. NO end place, end text or end source yet. ***
        $temp_text = '';
        $temp = '';
        $fam_relation_end_place = '';
        if ($marriageDb->fam_relation_end_date || $fam_relation_end_place) {
            $temp_text .= date_place($marriageDb->fam_relation_end_date, $fam_relation_end_place);
            $templ_relation["cohabit_end"] = '';
            if (isset($templ_relation["cohabit_exist"])) {
                $templ_relation["cohabit_end"] = '. ';
            }
            $templ_relation["cohabit_end"] .= __('End living together') . ' ' . date_place($marriageDb->fam_relation_end_date, $fam_relation_end_place);
            $temp = "cohabit_end";
        }
        //if ($user["group_texts_fam"]=='j' AND isset($marriageDb->fam_relation_end_text) AND process_text($marriageDb->fam_relation_end_text)){
        //	if ($temp_text){
        //		$temp_text.= ', ';
        //		if($temp) { $templ_relation[$temp].=", "; }
        //	}
        //	$temp_text.= process_text($marriageDb->fam_relation_end_text);
        //	//$templ_relation["cohabit_text"]=process_text($marriageDb->fam_relation_end_text);
        //	//$temp="cohabit_text";
        //}
        // *** Living together source ***
        // no source yet...
        if ($temp_text) {
            $this->marriage_check = true;
            if ($text !== '' || $this->relation_kind) {
                $text .= "<br>\n";
                //$templ_relation["cohabit_exist"]="\n";
            }
            $text .= '<b>' . __('End living together') . '</b>';
            //if(isset($templ_relation["cohabit_exist"])) {$templ_relation["cohabit_exist"].=__('End living together')." "; }
            //else {$templ_relation["cohabit_exist"]=__('End living together')." ";  }
            $text .= ' ' . $temp_text;
        }
    }

    private function get_married_notice($marriageDb, $humo_option, $user,  $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // *** Married Notice ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_marr_notice_date || $marriageDb->fam_marr_notice_place) {
            $nightfall = "";
            if ($humo_option['admin_hebnight'] == "y") {
                $nightfall = $marriageDb->fam_marr_notice_date_hebnight;
            }
            $temp_text .= date_place($marriageDb->fam_marr_notice_date, $marriageDb->fam_marr_notice_place, $nightfall);
            $templ_relation["prew_date"] = date_place($marriageDb->fam_marr_notice_date, $marriageDb->fam_marr_notice_place, $nightfall);
            $temp = "prew_date";
        }
        if ($user["group_texts_fam"] == 'j' and process_text($marriageDb->fam_marr_notice_text)) {
            if ($temp_text) {
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= " ";
                }
            }
            $temp_text .= process_text($marriageDb->fam_marr_notice_text);
            //$templ_relation["prew_text"]=strip_tags(process_text($marriageDb->fam_marr_notice_text));
            $templ_relation["prew_text"] = process_text($marriageDb->fam_marr_notice_text);
            $temp = "prew_text";
        }

        // *** Married notice source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_marr_notice_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["prew_source"] = $source_array['text'];
                    $temp = "prew_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["prew_source_add"] = '';
                $temp = "prew_source_add";
            }
        }

        if ($temp_text) {
            $this->marriage_check = true;
            $this->addition = __(' to: ');
            if ($text !== '') {
                $text .= "<br>\n";
                $templ_relation["prew_exist"] = "\n";
            }
            $text .= '<b>' . __('Marriage notice') . '</b> ' . $temp_text . '. ';
            if (isset($templ_relation["prew_exist"])) {
                $templ_relation["prew_exist"] .= __('Marriage notice') . ' ';
            } else {
                $templ_relation["prew_exist"] = __('Marriage notice') . ' ';
            }
        }
    }

    private function get_marriage($marriageDb, $humo_option, $parent1Db, $user, $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // *** Marriage ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_marr_date || $marriageDb->fam_marr_place) {
            $nightfall = "";
            if ($humo_option['admin_hebnight'] == "y") {
                $nightfall = $marriageDb->fam_marr_date_hebnight;
            }
            $templ_relation["wedd_date"] = date_place($marriageDb->fam_marr_date, $marriageDb->fam_marr_place, $nightfall);

            // *** Show age of parent1 when married. Only show age if dates are available. ***
            //if (isset($parent1Db->pers_bapt_date) OR isset($parent1Db->pers_birth_date)){
            if ($marriageDb->fam_marr_date && ($parent1Db->pers_bapt_date || $parent1Db->pers_birth_date)) {
                $process_age = new CalculateDates;
                $age = $process_age->calculate_age($parent1Db->pers_bapt_date, $parent1Db->pers_birth_date, $marriageDb->fam_marr_date);
                $templ_relation["wedd_date"] .= $age;
            }

            $temp = "wedd_date";
            $temp_text .= $templ_relation["wedd_date"];
        }

        if ($marriageDb->fam_marr_authority) {
            $templ_relation["wedd_authority"] = " [" . $marriageDb->fam_marr_authority . "]";
            $temp = "wedd_authority";
            $temp_text .= $templ_relation["wedd_authority"];
        }
        if ($user["group_texts_fam"] == 'j' and process_text($marriageDb->fam_marr_text)) {
            if ($temp_text) {
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= " ";
                }
            }
            //$templ_relation["wedd_text"]=strip_tags(process_text($marriageDb->fam_marr_text));
            $templ_relation["wedd_text"] = process_text($marriageDb->fam_marr_text);

            // *** Source by family text ***
            if ($presentation == 'standard') {
                $source_array = show_sources2("family", "family_text", $marriageDb->fam_gedcomnumber);
                if ($source_array) {
                    $templ_relation["wedd_text"] .= $source_array['text'];
                }
            }

            $temp = "wedd_text";
            $temp_text .= $templ_relation["wedd_text"];
        }
        // *** Aldfaer/ HuMo-genealogy: show witnesses ***
        if ($marriageDb->fam_gedcomnumber) {
            $text_array = witness($marriageDb->fam_gedcomnumber, 'ASSO', 'MARR');
            if ($text_array) {
                if ($temp) {
                    $templ_relation[$temp] .= ' ';
                }
                //$templ_relation["wedd_witn"] = '(' . __('marriage witness') . ': ' . $text_array['text'];
                $templ_relation["wedd_witn"] = $text_array['text'];
                $temp = "wedd_witn";
                $temp_text .= ' ' . $templ_relation["wedd_witn"];
                if (isset($text_array['source'])) {
                    $templ_relation["wedd_witn_source"] = $text_array['source'];
                    $temp = "wedd_witn_source";

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_relation["wedd_witn_add"] = '';
                    $temp = "wedd_witn_add";

                    $temp_text .= $text_array['source'];
                }
                //$templ_relation[$temp] .= ')';
                //$temp_text .= ')';
            }
        }

        // *** Marriage source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_marr_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["wedd_source"] = $source_array['text'];
                    $temp = "wedd_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["wedd_source_add"] = '';
                $temp = "wedd_source_add";
            }
        }

        if ($temp_text) {
            $this->marriage_check = true;
            $this->addition = __(' to: ');
            $templ_relation["wedd_exist"] = '';
            if ($text !== '') {
                $text .= "<br>\n";
                $templ_relation["wedd_exist"] = "\n";
            }

            //if ($this->relation_kind == '') $text .= '<b>' . __('Married') . '</b> ';
            //$text .= $temp_text;
            if (isset($templ_relation["wedd_exist"])) {
                if ($this->relation_kind != '') {
                    $templ_relation["wedd_exist"] .= $this->relation_kind . ' ';
                } else {
                    $templ_relation["wedd_exist"] .= __('Married') . ' ';
                }
            } else {
                if ($this->relation_kind != '') {
                    $templ_relation["wedd_exist"] = $this->relation_kind . ' ';
                } else {
                    $templ_relation["wedd_exist"] = __('Married') . ' ';
                }
            }
            $text .= '<b>' . $templ_relation["wedd_exist"] . '</b>' . $temp_text;
        } else {
            // *** Marriage without further data (date or place) ***
            if ($marriageDb->fam_kind == 'civil') {
                $this->marriage_check = true;
                $this->addition = __(' to: ');
                $text .= '<b>' . __('Married') . '</b>';
                $templ_relation["wedd_exist"] = __('Married');
                $templ_relation["wedd_dummy"] = "&nbsp;"; // we need this, otherwise tfpdfextend ignores the wedd_exist value !
            } elseif ($this->relation_kind != '') {
                $templ_relation["wedd_exist"] = $this->relation_kind;
                $templ_relation["wedd_dummy"] = "&nbsp;";
            }
        }
    }

    private function get_married_church_notice($marriageDb, $humo_option, $user, $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // *** Married church notice ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_marr_church_notice_date || $marriageDb->fam_marr_church_notice_place) {
            $nightfall = "";
            if ($humo_option['admin_hebnight'] == "y") {
                $nightfall = $marriageDb->fam_marr_church_notice_date_hebnight;
            }
            $templ_relation["prec_date"] = date_place($marriageDb->fam_marr_church_notice_date, $marriageDb->fam_marr_church_notice_place, $nightfall);
            $temp = "prec_date";
            $temp_text .= $templ_relation["prec_date"];
        }
        if ($user["group_texts_fam"] == 'j' and process_text($marriageDb->fam_marr_church_notice_text)) {
            if ($temp_text) {
                //$temp_text.= ', ';
                //if($temp) { $templ_relation[$temp].=", "; }
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= " ";
                }
            }
            //$templ_relation["prec_text"]= strip_tags(process_text($marriageDb->fam_marr_church_notice_text));
            $templ_relation["prec_text"] = process_text($marriageDb->fam_marr_church_notice_text);
            $temp = "prec_text";
            $temp_text .= $templ_relation["prec_text"];
        }

        // *** Married church notice source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_marr_church_notice_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["prec_source"] = $source_array['text'];
                    $temp = "prec_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["prec_source_add"] = '';
                $temp = "prec_source_add";
            }
        }

        if ($temp_text) {
            $this->marriage_check = true;
            $this->addition = __(' to: ');
            if ($text !== '') {
                $text .= "<br>\n";
                $templ_relation["prec_exist"] = "\n";
            }
            $text .= '<b>' . __('Married notice (religious)') . '</b> ' . $temp_text;
            if (isset($templ_relation["prec_exist"])) {
                $templ_relation["prec_exist"] .= __('Married notice (religious)') . ' ';
            } else {
                $templ_relation["prec_exist"] = __('Married notice (religious)') . ' ';
            }
        }
    }

    private function get_married_church($marriageDb, $humo_option, $user, $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // *** Married church ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_marr_church_date || $marriageDb->fam_marr_church_place) {
            $nightfall = "";
            if ($humo_option['admin_hebnight'] == "y") {
                $nightfall = $marriageDb->fam_marr_church_date_hebnight;
            }
            $templ_relation["chur_date"] = date_place($marriageDb->fam_marr_church_date, $marriageDb->fam_marr_church_place, $nightfall);
            $temp = "chur_date";
            $temp_text .= $templ_relation["chur_date"];
        }
        if ($user["group_texts_fam"] == 'j' and process_text($marriageDb->fam_marr_church_text)) {
            if ($temp_text) {
                //$temp_text.= ', ';
                //if($temp) { $templ_relation[$temp].=", "; }
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= " ";
                }
            }
            //$templ_relation["chur_text"]=strip_tags(process_text($marriageDb->fam_marr_church_text));
            $templ_relation["chur_text"] = process_text($marriageDb->fam_marr_church_text);
            $temp = "chur_text";
            $temp_text .= $templ_relation["chur_text"];
        }
        // *** Aldfaer/ HuMo-genealogy show witnesses ***
        if ($marriageDb->fam_gedcomnumber) {
            $text_array = witness($marriageDb->fam_gedcomnumber, 'ASSO', 'MARR_REL');
            if ($text_array) {
                if ($temp) {
                    $templ_relation[$temp] .= ' ';
                }
                //$templ_relation["chur_witn"] = '(' . __('marriage witness (religious)') . ': ' . $text_array['text'];
                $templ_relation["chur_witn"] = $text_array['text'];
                $temp = "chur_witn";
                $temp_text .= ' ' . $templ_relation["chur_witn"];
                if (isset($text_array['source'])) {
                    $templ_relation["chur_witn_source"] = $text_array['source'];
                    $temp = "chur_witn_source";

                    // *** Extra item, so it's possible to add a comma or space ***
                    $templ_relation["chur_witn_add"] = '';
                    $temp = "chur_witn_add";

                    $temp_text .= $text_array['source'];
                }
                //$templ_relation[$temp] .= ')';
                //$temp_text .= ')';
            }
        }

        // *** Married church source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_marr_church_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["chur_source"] = $source_array['text'];
                    $temp = "chur_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["chur_source_add"] = '';
                $temp = "chur_source_add";
            }
        }

        if ($temp_text) {
            $this->marriage_check = true;
            $this->addition = __(' to: ');
            if ($text != '') {
                $text .= "<br>\n";
                $templ_relation["chur_exist"] = "\n";
            }
            $text .= '<b>' . __('Married (religious)') . '</b> ' . $temp_text;
            if (isset($templ_relation["chur_exist"])) {
                $templ_relation["chur_exist"] .= __('Married (religious)') . ' ';
            } else {
                $templ_relation["chur_exist"] = __('Married (religious)') . ' ';
            }
        }

        // *** Religion ***
        if ($user['group_religion'] == 'j' && $marriageDb->fam_religion) {
            $templ_relation["reli_reli"] = ' (' . __('religion: ') . $marriageDb->fam_religion . ')';
            $text .= ' <span class="religion">(' . __('religion: ') . $marriageDb->fam_religion . ')</span>';
        }
    }

    private function get_divorce($marriageDb, $user, $presentation, $screen_mode)
    {
        // TODO check globals
        global $temp;
        global $templ_relation;
        global $text;

        // Temporary needed to check for divorce.
        global $temp_text;

        // *** Divorse ***
        $temp_text = '';
        $temp = '';
        if ($marriageDb->fam_div_date || $marriageDb->fam_div_place) {
            $templ_relation["devr_date"] = date_place($marriageDb->fam_div_date, $marriageDb->fam_div_place);
            $temp = "devr_date";
            $temp_text .= $templ_relation["devr_date"];
        }
        if ($marriageDb->fam_div_authority) {
            $templ_relation["devr_authority"] = " [" . $marriageDb->fam_div_authority . "]";
            $temp = "devr_authority";
            $temp_text .= $templ_relation["devr_authority"];
        }
        if ($user["group_texts_fam"] == 'j' and $marriageDb->fam_div_text != 'DIVORCE' and process_text($marriageDb->fam_div_text)) {
            if ($temp_text) {
                //$temp_text.= ', ';
                //if($temp) { $templ_relation[$temp].=", "; }
                $temp_text .= ' ';
                if ($temp) {
                    $templ_relation[$temp] .= " ";
                }
            }
            //$templ_relation["devr_text"]=strip_tags(process_text($marriageDb->fam_div_text));
            $templ_relation["devr_text"] = process_text($marriageDb->fam_div_text);
            $temp = "devr_text";
            $temp_text .= $templ_relation["devr_text"];
        }

        // *** Divorse source ***
        if ($presentation == 'standard') {
            $source_array = show_sources2("family", "fam_div_source", $marriageDb->fam_gedcomnumber);
            if ($source_array) {
                if ($screen_mode == 'PDF') {
                    $templ_relation["devr_source"] = $source_array['text'];
                    $temp = "devr_source";
                } else {
                    $temp_text .= $source_array['text'];
                }

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["devr_source_add"] = '';
                $temp = "devr_source_add";
            }
        }

        // *** div_text "DIVORCE" is used for divorce without further data! ***
        if ($temp_text || $marriageDb->fam_div_text == 'DIVORCE') {
            $this->marriage_check = true;
            $this->addition = ' ' . __('from:') . ' ';
            if ($text !== '') {
                $text .= "<br>\n";
                $templ_relation["devr_exist"] = "\n";
            }
            $text .= '<span class="divorse"><b>' . ucfirst(__('divorced')) . '</b> ' . $temp_text . '</span>';
            if (isset($templ_relation["devr_exist"])) {
                $templ_relation["devr_exist"] .= ucfirst(__('divorced')) . ' ';
            } else {
                $templ_relation["devr_exist"] = ucfirst(__('divorced')) . ' ';
            }
            if ($marriageDb->fam_div_text == 'DIVORCE') {
                $templ_relation["devr_dummy"] = "&nbsp;"; // if we don't create at least one "devr" element in the $templ_relation array besides ["devr_exist"], then tfpdfextend will not display the  ["devr_exist"]. 
            }
        }
    }



    // ***************************************************
    // *** Show marriage                               ***
    // ***************************************************
    public function marriage_data($marriageDb = '', $number = '0', $presentation = 'standard')
    {
        global $dbh, $db_functions, $tree_prefix_quoted, $dataDb, $uri_path, $humo_option;
        global $language, $user, $screen_mode;
        global $parent1Db, $parent2Db;
        global $relation_check; // Global still needed to show a proper marriage or relation text when age is calculated in personCls.php.

        // TODO check globals in new functions.
        global $temp;
        global $templ_relation;
        global $text;

        $templ_relation = array($marriageDb);  //reset array

        if ($marriageDb == '') {
            $marriageDb = $this->cls_marriage_Db;
        }

        // *** Open a person class for witnesses ***
        $person_cls = new PersonCls;

        $text = '';

        $this->addition = __(' to: '); // Default addition.
        $this->check_relation_type($marriageDb->fam_kind);

        // This variable is also used to show a proper marriage or relation text when age is calculated in personCls.php.
        // Variable is global now.
        $relation_check = $this->relation_check;

        $this->get_living_together($marriageDb, $user, $presentation, $screen_mode);

        $this->get_married_notice($marriageDb, $humo_option, $user,  $presentation, $screen_mode);

        $this->get_marriage($marriageDb, $humo_option, $parent1Db, $user, $presentation, $screen_mode);

        $this->get_married_church_notice($marriageDb, $humo_option, $user, $presentation, $screen_mode);

        $this->get_married_church($marriageDb, $humo_option, $user, $presentation, $screen_mode);

        // TODO improve code to check if divorce is used. Use: $templ_relation["devr_exist"]?
        global $temp_text;
        $this->get_divorce($marriageDb, $user, $presentation, $screen_mode);


        // *** No relation data (marriage without date), show standard text ***
        if ($relation_check == false && $this->marriage_check == false) {
            // *** Show standard marriage text ***
            $templ_relation["unkn_rel"] = __('Married/ Related') . ' ';
            $text .= '<b>' . __('Married/ Related') . '</b> ';
        } else {
            // *** Years of marriage ***
            //if (($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_date)
            //	AND $marriageDb->fam_div_text!='DIVORCE'
            //	AND !($temp_text AND $marriageDb->fam_div_date==''))
            if ((($marriageDb->fam_marr_church_date and $marriageDb->fam_marr_church_date != '')
                    or ($marriageDb->fam_marr_date and $marriageDb->fam_marr_date != ''))
                and $marriageDb->fam_div_text != 'DIVORCE'
                and !($temp_text and $marriageDb->fam_div_date == '')
            ) {
                $end_date = '';

                // *** Check death date of husband ***
                if (isset($parent1Db->pers_death_date) and $parent1Db->pers_death_date) $end_date = $parent1Db->pers_death_date;
                elseif (isset($parent1Db->pers_buried_date) and $parent1Db->pers_buried_date) {
                    // if no death date, try burial date
                    $end_date = $parent1Db->pers_buried_date;
                }

                // *** Check death date of wife ***
                if (isset($parent2Db->pers_death_date) and $parent2Db->pers_death_date) {
                    // *** Check if men died earlier then woman (AT THIS MOMENT ONLY CHECK YEAR) ***
                    if ($end_date and substr($end_date, -4) > substr($parent2Db->pers_death_date, -4)) {
                        $end_date = $parent2Db->pers_death_date;
                    }
                    // *** Man still living or no date available  ***
                    if ($end_date == '') $end_date = $parent2Db->pers_death_date;
                } elseif (isset($parent2Db->pers_buried_date) and $parent2Db->pers_buried_date) {
                    // if no death date, try burial date
                    // *** Check if men died earlier then woman (AT THIS MOMENT ONLY CHECK YEAR) ***
                    if ($end_date and substr($end_date, -4) > substr($parent2Db->pers_buried_date, -4)) {
                        $end_date = $parent2Db->pers_buried_date;
                    }
                    // *** Man still living or no date available  ***
                    if ($end_date == '') $end_date = $parent2Db->pers_buried_date;
                }

                // *** End of marriage by divorce ***
                if ($marriageDb->fam_div_date) {
                    $end_date = $marriageDb->fam_div_date;
                }

                // *** Only show marriage years if there is a marriage (don't show for other relations at this moment) ***
                if ($relation_check == false) {
                    $marr_years = new CalculateDates;
                    $age = $marr_years->calculate_marriage($marriageDb->fam_marr_church_date, $marriageDb->fam_marr_date, $end_date);
                    $text .= $age;  // Space and comma in $age
                    //*** PDF ***
                    $templ_relation["marr_years"] = $age;
                }
            }
        }


        // *** Show media/ pictures ***
        $showMedia = new ShowMedia;
        $result = $showMedia->show_media('family', $marriageDb->fam_gedcomnumber); // *** This function can be found in file: showMedia.php! ***
        $text .= $result[0];
        if (isset($templ_relation)) $templ_relation = array_merge((array)$templ_relation, (array)$result[1]);
        else $templ_relation = $result[1];
        //if (isset($templ_relation))
        //	$templ_relation = array_merge((array)$templ_relation,(array)$result[1]);
        //else
        //	$templ_relation=$result[1];

        // *** Show objecs ***

        // *** Added oct. 2024: Internet links (URL) ***
        $url_qry = $db_functions->get_events_connect('family', $marriageDb->fam_gedcomnumber, 'URL');
        if (count($url_qry) > 0) {
            $text .= "<br>\n";
        }
        foreach ($url_qry as $urlDb) {
            //URL/ Internet link
            $text .= '<b>' . __('URL/ Internet link') . '</b> <a href="' . $urlDb->event_event . '" target="_blank">' . $urlDb->event_event . '</a>';
            if ($urlDb->event_text) {
                $text .= ' ' . process_text($urlDb->event_text);
            }
            $text .= "<br>\n";
        }

        // *** Show events ***
        if ($user['group_event'] == 'j') {
            if ($marriageDb->fam_gedcomnumber) {
                $event_qry = $db_functions->get_events_connect('family', $marriageDb->fam_gedcomnumber, 'event');
                $num_rows = count($event_qry);
                if ($num_rows > 0) {
                    $text .= '<span class="event">';
                }
                $i = 0;
                foreach ($event_qry as $eventDb) {
                    $i++;
                    //echo '<br>'.__('Event (family)');
                    if ($text != '') {
                        $text .= "<br>\n";
                    }
                    if ($i > 1) {
                        $templ_relation["event" . $i . "_ged"] = "\n";
                    }

                    // *** Check if NCHI is 0 or higher ***
                    $event_gedcom = $eventDb->event_gedcom;
                    $event_text = $eventDb->event_text;
                    if ($event_gedcom == 'NCHI' and trim($eventDb->event_text) == '0') {
                        $event_gedcom = 'NCHI0';
                        $event_text = '';
                    }

                    $text .= '<b>' . language_event($event_gedcom) . '</b>';
                    if (isset($templ_relation["event" . $i . "_ged"])) {
                        $templ_relation["event" . $i . "_ged"] .= language_event($event_gedcom);
                    } else {
                        $templ_relation["event" . $i . "_ged"] = language_event($event_gedcom);
                    }

                    // *** Show event kind ***
                    if ($eventDb->event_event) {
                        $templ_relation["event" . $i . "_event"] = ' (' . $eventDb->event_event . ')';
                        $text .= $templ_relation["event" . $i . "_event"];
                    }
                    if ($eventDb->event_date or $eventDb->event_place) {
                        $templ_relation["event" . $i . "_date"] = ' ' . date_place($eventDb->event_date, $eventDb->event_place);
                        $text .= $templ_relation["event" . $i . "_date"];
                    }
                    if ($event_text) {
                        $templ_relation["event" . $i . "_text"] = ' ' . process_text($eventDb->event_text);
                        $text .= $templ_relation["event" . $i . "_text"];
                    }

                    // *** Sources by a family event ***
                    if ($presentation == 'standard') {
                        $source_array = show_sources2("family", "fam_event_source", $eventDb->event_id);
                        if ($source_array) {
                            if ($screen_mode == 'PDF') {
                                //$templ_relation["event_source"]=show_sources2("family","fam_event_source",$eventDb->event_id);
                                //$temp="fam_event_source";
                            } else
                                $text .= $source_array['text'];
                        }
                    }
                }
                if ($num_rows > 0) {
                    $text .= "</span><br>\n"; // if there are events, the word "with" should be on a new line to make the text clearer
                    $templ_relation["event_lastline"] = "\n";
                    $this->addition = ltrim($this->addition);
                }
            }
        }

        // **********************************
        // *** Concatenate marriage texts ***
        // **********************************

        // Process english 1st, 2nd, 3rd and 4th marriage.
        // TODO Check code. 1st, 2nd is only show by children. Script also in personCls.php.
        // Used for descendant report.
        $relation_number = '';
        if ($presentation == 'short' || $presentation == 'shorter') {
            if ($number == '1') {
                $relation_number = __('1st');
            }
            if ($number == '2') {
                $relation_number = __('2nd');
            }
            if ($number == '3') {
                $relation_number = __('3rd');
            }
            if ($number > '3') {
                $relation_number = $number . __('th');
            }

            if ($this->marriage_check == true) {
                if ($number) {
                    $relation_number .= ' ' . __('marriage');     // marriage
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                } else {
                    $relation_number .= __('Married ');       // Married
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                }
            }

            if ($relation_check == true) {
                if ($number) {
                    $relation_number .= ' ' . __('related');   // relation
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                } else {
                    $relation_number = ucfirst(__('related')) . ' ';      // Relation
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                }
            }

            if ($relation_check == false && $this->marriage_check == false) {
                if ($number) {
                    // *** Other text in 2nd marriage: 2nd marriage Hubertus [Huub] Mons ***
                    if ($presentation == 'shorter') {
                        $relation_number .= ' ' . __('marriage/ relation');   // relation
                    } else {
                        $relation_number .= ' ' . __('married/ related');   // relation
                    }
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                } else {
                    $relation_number .= __('Married/ Related');      // Relation
                    $this->relation_kind = '';
                    $this->addition = __(' to: ');
                }
            }
        }

        if ($presentation == 'short' || $presentation == 'shorter') {
            $text = '<b>' . $relation_number . $this->relation_kind . '</b>';
            $templ_relation = array();  //reset array - don't need it
            $templ_relation['relnr_rel'] = $relation_number . $this->relation_kind;
            // *** Show divorse if privacy filter is set ***
            if ($marriageDb->fam_div_date || $marriageDb->fam_div_place || $marriageDb->fam_div_text) {
                $text .= ' <span class="divorse">(' . __('divorced') . ')</span>';
                $templ_relation['relnr_rel'] .= " (" . __('divorced') . ")";
            }
            // Show end of relation here?

            // *** No addition in text: 2nd marriage Hubertus [Huub] Mons ***
            if ($presentation == 'shorter') {
                $this->addition = '';
            }
        } else {
            // TODO check this line. Generates a double text...
            // Disabled dec. 2024.
            // $text = '<b>' . $relation_number . $this->relation_kind . '</b> ' . $text;
        }

        if ($this->addition) {
            $text .= '<b>' . $this->addition . '</b>';
            $templ_relation["rel_add"] = $this->addition;
        }

        //if ($presentation == 'short' or $presentation == 'shorter') {
        //    $templ_relation["rel_add"] = " " . $this->addition;
        //} else {
        //    $templ_relation["rel_add"] = "\n" . $this->addition;
        //}

        if ($screen_mode != "PDF") {
            return $text;
        } elseif (isset($templ_relation)) {
            foreach ($templ_relation as $key => $val) {
                $templ_relation[$key] = strip_tags($val);
            }
            return $templ_relation;
        }
        return null;
    } // *** End of marriage ***

} // End of class
