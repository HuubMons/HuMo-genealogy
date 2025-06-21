<?php

/**
 * 25-12-2020: New combined module addresses and shared addresses -> Residences
 * Residences/addresses (was: extended addresses for HuMo-genealogy and dutch Haza-data program (Haza-data plus version)
 */
function show_addresses($connect_kind, $connect_sub_kind, $connect_connect_id): string
{
    global $dbh, $db_functions, $user, $uri_path;
    global $temp, $templ_person, $templ_relation; // *** PDF export ***
    global $tree_id, $humo_option, $data;

    $text = '';
    $address_nr = 0;

    // *** Search for all connected addresses ***
    $connect_sql = $db_functions->get_addresses($connect_kind, $connect_sub_kind, $connect_connect_id);
    $nr_addresses = count($connect_sql);
    foreach ($connect_sql as $connectDb) {
        $address_nr++;
        if ($address_nr == '1') {
            //if ($process_text){
            //	if ($data["family_expanded"]!='compact'){ $text.='<br>'; } else{ $text.='. '; }
            //}
            if ($nr_addresses == '1') {
                $residence = ucfirst(__('residence'));
                if ($connect_kind == 'person') {
                    $templ_person["address_start"] = ucfirst(__('residence')) . ': ';
                }
                if ($connect_kind == 'family') {
                    $templ_relation["address_start"] = __('Residence (family)') . ': ';
                    $residence = __('Residence (family)');
                }
            } else {
                $residence = ucfirst(__('residences'));
                if ($connect_kind == 'person') {
                    $templ_person["address_start"] = ucfirst(__('residences')) . ': ';
                }
                if ($connect_kind == 'family') {
                    $templ_relation["address_start"] = __('Residences (family)') . ': ';
                    $residence = __('Residence (family)');
                }
            }

            if ($temp && $connect_kind == 'person') {
                $templ_person[$temp] .= ". ";
            }
            //if ($temp && $connect_kind=='family'){
            //      $templ_relation[$temp].=". ";
            //}

            //$templ_person["address_exist"]=$residence.': ';
            //$temp="address_exist";
            $text .= '<b>' . $residence . ':</b> ';
        }
        if ($address_nr > 1) {
            $text .= ', ';
            if ($temp && $connect_kind == 'person') {
                $templ_person[$temp] .= ", ";
            }
            if ($temp && $connect_kind == 'family') {
                $templ_relation[$temp] .= ", ";
            }

            if ($data["family_expanded"] == 'expanded2') {
                $text .= '<br>';
            }
        }

        // *** Show link to shared address ***
        if ($connectDb->address_shared == '1') {
            if ($humo_option["url_rewrite"] == "j") {
                $text .= '<a href="' . $uri_path . 'address/' . $tree_id . '/' . $connectDb->connect_item_id . '">';
            } else {
                $text .= '<a href="' . $uri_path . 'index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $connectDb->connect_item_id . '">';
            }
        }

        // *** Address ***
        if ($user['group_living_place'] == 'j' && $connectDb->address_address) {
            $text .= ' ' . $connectDb->address_address . ' ';

            // *** PDF export ***
            if ($connect_kind == 'person') {
                $templ_person["address_address" . $address_nr] = $connectDb->address_address;
                if ($templ_person["address_address" . $address_nr] != '') {
                    $temp = "address_address" . $address_nr;
                }
            }
            if ($connect_kind == 'family') {
                $templ_relation["address_address" . $address_nr] = $connectDb->address_address;
                if ($templ_relation["address_address" . $address_nr] != '') {
                    $temp = "address_address" . $address_nr;
                }
            }
        }

        // *** Zip code ***
        if ($connectDb->address_zip) {
            $text .= ' ' . $connectDb->address_zip . ' ';

            // *** PDF export ***
            if ($connect_kind == 'person') {
                if (isset($templ_person["address_address" . $address_nr])) {
                    $templ_person["address_address" . $address_nr] .= ' ' . $connectDb->address_zip;
                } else {
                    $templ_person["address_address" . $address_nr] = $connectDb->address_zip;
                }
                if ($templ_person["address_address" . $address_nr] != '') {
                    $temp = "address_address" . $address_nr;
                }
            }
            if ($connect_kind == 'family') {
                if (isset($templ_relation["address_address" . $address_nr])) {
                    $templ_relation["address_address" . $address_nr] .= ' ' . $connectDb->address_zip;
                } else {
                    $templ_relation["address_address" . $address_nr] = $connectDb->address_zip;
                }
                if ($templ_relation["address_address" . $address_nr] != '') {
                    $temp = "address_address" . $address_nr;
                }
            }
        }

        // *** Place ***
        $text .= $connectDb->address_place;
        // *** PDF export ***
        if ($connect_kind == 'person') {
            if (isset($templ_person["address_address" . $address_nr])) {
                $templ_person["address_address" . $address_nr] .= ' ' . $connectDb->address_place;
            } else {
                $templ_person["address_address" . $address_nr] = $connectDb->address_place;
            }
            // *** Add date ***
            if ($connectDb->connect_date) {
                $templ_person["address_address" . $address_nr] .= ' (' . date_place($connectDb->connect_date, '') . ')';
            }
            if ($templ_person["address_address" . $address_nr] != '') {
                $temp = "address_address" . $address_nr;
            }
        }
        if ($connect_kind == 'family') {
            if (isset($templ_relation["address_address" . $address_nr])) {
                $templ_relation["address_address" . $address_nr] .= ' ' . $connectDb->address_place;
            } else {
                $templ_relation["address_address" . $address_nr] = $connectDb->address_place;
            }
            // *** Add date ***
            if ($connectDb->connect_date) {
                $templ_relation["address_address" . $address_nr] .= ' (' . date_place($connectDb->connect_date, '') . ')';
            }
            if ($templ_relation["address_address" . $address_nr] != '') {
                $temp = "address_address" . $address_nr;
            }
        }

        // *** END OF: Show link to address if street is used ***
        if ($connectDb->address_shared == '1') {
            $text .= "</a>";
        }

        // *** Phone number ***
        if ($connectDb->address_phone) {
            $text .= ', ' . __('phone') . ' ' . $connectDb->address_phone;

            // *** PDF export ***
            if (isset($templ_relation["address_phone" . $address_nr])) {
                $templ_person["address_phone" . $address_nr] .= ', ' . __('phone') . ' ' . $connectDb->address_phone;
            } else {
                $templ_person["address_phone" . $address_nr] = ', ' . __('phone') . ' ' . $connectDb->address_phone;
            }
        }

        // *** Don't use address_date. Using connect_date for all addresses ***
        //if ($connectDb->address_date){
        //	//$text.=date_place($connectDb->address_date,'').' ';
        //	$text.=' ('.date_place($connectDb->address_date,'').')';
        //	// default, without place, place is processed later.
        //	$templ_person["address_date".$address_nr]=' ('.date_place($connectDb->address_date,'').')';
        //	$temp="address_date".$address_nr;
        //}
        if ($connectDb->connect_date) {
            //$text.=date_place($connectDb->address_date,'').' ';
            $text .= ' (' . date_place($connectDb->connect_date, '') . ')';
            // default, without place, place is processed later.
            //$templ_person["address_date".$address_nr]=' ('.date_place($connectDb->connect_date,'').')';
            //$temp="address_date".$address_nr;
        }

        // *** Address text ***
        if ($connectDb->address_text) {
            $work_text = process_text($connectDb->address_text);
            if ($work_text) {
                // *** PDF export ***
                if ($connect_kind == 'person') {
                    $templ_person["address_text" . $address_nr] = ' ' . $connectDb->address_text;
                    if ($templ_person["address_text" . $address_nr] != '') {
                        $temp = "address_text" . $address_nr;
                    }
                }
                if ($connect_kind == 'family') {
                    $templ_relation["address_text" . $address_nr] = ' ' . $connectDb->address_text;
                    if ($templ_relation["address_text" . $address_nr] != '') {
                        $temp = "address_text" . $address_nr;
                    }
                }

                $text .= ' ' . $work_text;
            }
        }

        // *** Address extra text (connection table) ***
        if ($connectDb->connect_text) {
            $work_text = process_text($connectDb->connect_text);
            if ($work_text) {
                // *** PDF export ***
                if ($connect_kind == 'person') {
                    if (isset($templ_person["address_text" . $address_nr])) {
                        $templ_person["address_text" . $address_nr] .= ', ' . $connectDb->connect_text;
                    } else {
                        $templ_person["address_text" . $address_nr] = ' ' . $connectDb->connect_text;
                    }
                    if ($templ_person["address_text" . $address_nr] != '') {
                        $temp = "address_text" . $address_nr;
                    }
                }
                if ($connect_kind == 'family') {
                    if (isset($templ_relation["address_text" . $address_nr])) {
                        $templ_relation["address_text" . $address_nr] .= ', ' . $connectDb->connect_text;
                    } else {
                        $templ_relation["address_text" . $address_nr] = ' ' . $connectDb->connect_text;
                    }
                    if ($templ_relation["address_text" . $address_nr] != '') {
                        $temp = "address_text" . $address_nr;
                    }
                }

                $text .= ' ' . $work_text;
            }
        }

        // *** Show source by address ***
        $source_array = show_sources2("address", "address_source", $connectDb->address_gedcomnr);
        if ($source_array) {
            // *** PDF export ***
            if ($connect_kind == 'person') {
                $templ_person["address_source" . $address_nr] = $source_array['text'];
                // *** Extra item, so it's possible to add a comma or space ***
                if ($templ_person["address_source" . $address_nr] != '') {
                    $templ_person["address_add"] = '';
                    $temp = "address_add";
                }
            }
            if ($connect_kind == 'family') {
                $templ_relation["address_source" . $address_nr] = $source_array['text'];
                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["address_add"] = '';
                $temp = "address_add";
            }
            $text .= $source_array['text'];
        }

        // *** April 2022: Show source by person/family-address-connection ***
        if ($connect_kind == 'person') {
            $source_array = show_sources2("person", "pers_address_connect_source", $connectDb->connect_id);
        } else {
            $source_array = show_sources2("family", "fam_address_connect_source", $connectDb->connect_id);
        }
        if ($source_array) {
            // *** PDF export ***
            if ($connect_kind == 'person') {
                $templ_person["address_source" . $address_nr] = $source_array['text'];

                // *** Extra item, so it's possible to add a comma or space ***
                if ($templ_person["address_source" . $address_nr] != '') {
                    $templ_person["address_add"] = '';
                    $temp = "address_add";
                }
            }
            if ($connect_kind == 'family') {
                $templ_relation["address_source" . $address_nr] = $source_array['text'];

                // *** Extra item, so it's possible to add a comma or space ***
                $templ_relation["address_add"] = '';
                $temp = "address_add";
            }
            $text .= $source_array['text'];
        }
    }
    return $text;
}
