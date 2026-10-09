<?php

namespace Genealogy\App\Model;

use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonLink;
use Genealogy\Include\ShowSources;
use Genealogy\App\Model\BaseModel;
use Genealogy\Include\DatePlace;
use Genealogy\Include\HtmlEscaper;

class AddressModel extends BaseModel
{
    public function getAddressAuthorised(): string
    {
        $authorised = '';
        if ($this->user['group_addresses'] != 'j') {
            $authorised = __('You are not authorised to see this page.');
        }
        return $authorised;
    }

    public function getById($id): object
    {
        $addressDb = $this->db_functions->get_address($id);
        return $addressDb;
    }

    public function getAddressSources($id)
    {
        // *** Show source by addresss ***
        $showSources = new ShowSources();
        $source_array = $showSources->show_sources2("address", "address_source", $id);
        if ($source_array) {
            return $source_array['text'];
        }
        return null;
    }

    public function getAddressConnectedPersons($id): string
    {
        $text = '';
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $personLink = new PersonLink();
        $datePlace = new DatePlace();
        $htmlEscaper = new HtmlEscaper();

        // *** Search address in connections table ***
        $event_qry = $this->db_functions->get_connections('person_address', $id);
        if (count($event_qry) > 0) {
            $text .= '<h5>'.__('Address by person') . '</h5>';
        }
        foreach ($event_qry as $eventDb) {
            // *** Person address ***
            if ($eventDb->connect_connect_id) {
                $personDb = $this->db_functions->get_person($eventDb->connect_connect_id);
                $privacy = $personPrivacy->get_privacy($personDb);
                $name = $personName->get_person_name($personDb, $privacy);

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $personLink->get_person_link($personDb);

                //$text .= __('Address by person') . ': <a href="' . $url . '">' . $name["standard_name"] . '</a>';

                $date = $htmlEscaper->escHtmlDecoded($datePlace->date_place($eventDb->connect_date, ''));

                $text .= '<div class="row">';
                $text .= '<div class="col-md-2">' . $date . '</div><div class="col-md-10"><a href="' . $htmlEscaper->escHtml((string) $url) . '">' . $htmlEscaper->escHtml((string) $name["standard_name"]) . '</a>';
                if ($eventDb->connect_role) {
                    $text .= '; ' . $htmlEscaper->escHtml((string) $eventDb->connect_role);
                }
                if ($eventDb->connect_text) {
                    $text .= '; ' . nl2br($htmlEscaper->escHtml((string) $eventDb->connect_text));
                }
                $text .= '</div></div>';
            }
        }
        unset($event_qry);


        // *** Search address in connections table for relations ***
        $relation_qry = $this->db_functions->get_connections('family_address', $id);
        if (count($relation_qry) > 0) {
            $text .= '<h5 class="mt-3">'.__('Address by relation') . '</h5>';
        }
        foreach ($relation_qry as $relationDb) {
            // *** Relation address ***
            if ($relationDb->connect_connect_id) {
                $relationData = $this->db_functions->get_family($relationDb->connect_connect_id);
                if ($relationData) {
                    $date = $htmlEscaper->escHtmlDecoded($datePlace->date_place($relationDb->connect_date, ''));
                    
                    $text .= '<div class="row">';
                    $text .= '<div class="col-md-2">' . $date . '</div><div class="col-md-10">';

                    // *** Show man (father) ***
                    if ($relationData->partner1_id) {
                        $manDb = $this->db_functions->get_person_with_id($relationData->partner1_id);
                        if ($manDb) {
                            $privacy = $personPrivacy->get_privacy($manDb);
                            $manName = $personName->get_person_name($manDb, $privacy);
                            $manUrl = $personLink->get_person_link($manDb);
                            $text .= '<a href="' . $htmlEscaper->escHtml((string) $manUrl) . '">' . $htmlEscaper->escHtml((string) $manName["standard_name"]) . '</a>';
                        }
                    }

                    // *** Show woman (mother) ***
                    if ($relationData->partner2_id) {
                        $womanDb = $this->db_functions->get_person_with_id($relationData->partner2_id);
                        if ($womanDb) {
                            $privacy = $personPrivacy->get_privacy($womanDb);
                            $womanName = $personName->get_person_name($womanDb, $privacy);
                            $womanUrl = $personLink->get_person_link($womanDb);
                            $text .= ' &amp; <a href="' . $htmlEscaper->escHtml((string) $womanUrl) . '">' . $htmlEscaper->escHtml((string) $womanName["standard_name"]) . '</a>';
                        }
                    }

                    if ($relationDb->connect_role) {
                        $text .= '; ' . $htmlEscaper->escHtml((string) $relationDb->connect_role);
                    }
                    if ($relationDb->connect_text) {
                        $text .= '; ' . nl2br($htmlEscaper->escHtml((string) $relationDb->connect_text));
                    }
                    $text .= '</div></div>';
                }
            }
        }
        unset($relation_qry);

        return $text;
    }
}
