<?php

/**
 * Show sources at birth, baptise, marriage, etc.
 * 
 * function show_sources2
 * $connect_kind = person/ family/ address
 * $connect_sub_kind = birth/ baptise/ etc.
 * $connect_connect_id = id (gedcomnumber or direct table id)
 */

namespace Genealogy\Include;

use Genealogy\Include\DatePlace;
use Genealogy\Include\ProcessText;
use Genealogy\Include\ShowQuality;
use Genealogy\Include\ValidateGedcomnumber;

class ShowSources
{
    private $family_expanded;

    function __construct($family_expanded = '')
    {
        $this->family_expanded = $family_expanded;
    }

    function show_sources2(string $connect_kind, string $connect_sub_kind, string $connect_connect_id)
    {
        global $db_functions, $tree_id, $user, $humo_option, $uri_path, $data;
        global $pdf_source, $source_footnotes, $screen_mode, $pdf_footnotes, $pdf;
        global $source_footnote_connect_id, $source_combiner;
        global $templ_person, $templ_relation; // *** PDF export ***

        $datePlace = new DatePlace();
        $processText = new ProcessText();
        $validateGedcomber = new ValidateGedcomnumber();

        $source_array['text'] = '';

        // *** Check if family_expanded is set, otherwise use general value ***
        if (isset($data["family_expanded"])) {
            $this->family_expanded = $data["family_expanded"];
        }

        $data["source_presentation"] = 'title';
        if (isset($_SESSION['save_source_presentation'])) {
            $data["source_presentation"] = $_SESSION['save_source_presentation'];
        }

        if ($user['group_sources'] != 'n' && $data["source_presentation"] != 'hide' && $screen_mode != 'STAR') {
            // *** Search for all connected sources ***
            $connect_sql = $db_functions->get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id);
            $nr_sources = count($connect_sql);
            foreach ($connect_sql as $connectDb) {
                // *** Get shared source, and check for restriction (in source and user group) ***
                $source_status = 'publish';
                if ($connectDb->connect_source_id) {
                    $sourceDb = $db_functions->get_source($connectDb->connect_source_id);
                    if ($user['group_show_restricted_source'] == 'n' && $sourceDb->source_status == 'restricted') {
                        $source_status = 'restricted';
                    }
                }

                // *** PDF export. Jan. 2021: all sources are exported (used to be: only shared sources) ***
                if ($screen_mode == 'PDF' && $source_status === 'publish') {
                    // *** Show sources as footnotes ***
                    if (!isset($source_footnotes) && $validateGedcomber->validate($connectDb->connect_source_id)) {
                        $source_footnotes[] = $sourceDb->source_id;
                        $pdf_footnotes[] = $pdf->AddLink();
                        $pdf_source[$connectDb->connect_source_id] = $connectDb->connect_source_id;
                    }

                    // *** Show text "Source by person/Sources by person" ***
                    if ($nr_sources > 1) {
                        if ($connect_sub_kind == 'person_source') {
                            $templ_person["source_start"] = __('Sources for person') . ': ';
                        } elseif ($connect_sub_kind == 'family_source') {
                            $templ_relation["source_start"] = __('Sources for family') . ': ';
                        }
                    } elseif ($connect_sub_kind == 'person_source') {
                        $templ_person["source_start"] = __('Source for person') . ': ';
                    } elseif ($connect_sub_kind == 'family_source') {
                        $templ_relation["source_start"] = __('Source for family') . ': ';
                    }

                    // *** Check if source is allready listed in the sourcelist ***
                    if (!in_array($sourceDb->source_id, $source_footnotes) && $validateGedcomber->validate($connectDb->connect_source_id)) {
                        // *** Add source in sourcelist ***
                        $pdf_source[$connectDb->connect_source_id] = $connectDb->connect_source_id;
                        $pdf_footnotes[] = $pdf->AddLink();
                        $source_footnotes[] = $sourceDb->source_id;

                        $j = array_key_last($source_footnotes);
                        $j++;
                    } else {
                        // *** Link to existing source ***
                        $j = array_search($sourceDb->source_id, $source_footnotes);
                        $j++;
                    }

                    // *** New source (in footnotes) ***
                    if ($data["source_presentation"] == 'footnote') {
                        if ($source_array['text']) {
                            $source_array['text'] .= '~';
                        }
                        $source_array['text'] .= $j;
                    } else {
                        // *** Texts for all sources, except person_source and family_source ***
                        if ($connect_sub_kind != 'person_source' && $connect_sub_kind != 'family_source') {
                            if ($nr_sources > 1) {
                                if ($connectDb->connect_order == '1') {
                                    $source_array['text'] .= ', ' . __('sources') . ': ';
                                }
                            } else {
                                $source_array['text'] .= ', ' . __('source') . ': ';
                            }
                        }

                        if ($sourceDb->source_title) {
                            $source_array['text'] .= trim($sourceDb->source_title);
                        } else {
                            // *** Standard source without title ***
                            $source_array['text'] .= $sourceDb->source_text;
                        }

                        // *** User group option to only show title of source ***
                        if ($user['group_sources'] != 't') {
                            if ($sourceDb->source_date or $sourceDb->source_place) {
                                $source_array['text'] .= " " . $datePlace->date_place($sourceDb->source_date, $sourceDb->source_place);
                            }
                        }

                        // append num of source in list as !!3 for use with fpdf_extend.php to add the right link:
                        $source_array['text'] .= "!!" . $j . '~'; // ~ is delimiter for multiple sources
                    }
                } elseif ($data["source_presentation"] == 'footnote' && $source_status === 'publish') {
                    // *** Combine footnotes with the same source including the same source role and source page... ***
                    $combiner_check = $connectDb->connect_source_id . '_' . $connectDb->connect_role . '_' . $connectDb->connect_page . '_' . $connectDb->connect_date . ' ' . $connectDb->connect_place . ' ' . $connectDb->connect_text;

                    // *** Jan. 2021: No shared source. Footnotes can be combined! ***
                    //if ($sourceDb->source_shared=='')
                    //if ($sourceDb->source_title=='')
                    if (isset($sourceDb->source_title) && $sourceDb->source_title == '') {
                        $combiner_check = $connectDb->connect_role . '_' . $connectDb->connect_page . '_' . $connectDb->connect_date . ' ' . $connectDb->connect_place . ' ' . $sourceDb->source_text;
                    }

                    $check = false;
                    // *** Check if the source (including role and page) is already used ***
                    if ($source_combiner) {
                        for ($j = 0; $j <= (count($source_combiner) - 1); $j++) {
                            if ($source_combiner[$j] == $combiner_check) {
                                $check = true;
                                $j2 = $j + 1;
                            }
                        }
                    }
                    // *** Source not found in array, add new source to array ***
                    if (!$check) {
                        // *** Save new combined source-role-page for check ***
                        $source_combiner[] = $combiner_check;
                        // *** Save connect_id to show footnotes ***
                        $source_footnote_connect_id[] = $connectDb->connect_id;
                        // *** Number to show for footnote ***
                        $j2 = count($source_footnote_connect_id);
                    }
                    // *** Test line for footnotes ***
                    //$source_array['text'].=' '.$combiner_check.' ';

                    // *** Add text "Source for person/ family". Otherwise it isn't clear which source it is ***
                    $rtf_text = '';
                    if ($connect_sub_kind == 'person_source') {
                        if ($nr_sources > 1) {
                            if ($connectDb->connect_order == '1') {

                                if ($this->family_expanded != 'compact') {
                                    $source_array['text'] .= '<br>';
                                } else {
                                    $source_array['text'] .= '. ';
                                }

                                $source_array['text'] .= '<b>' . __('Sources for person') . '</b> ';
                            }
                        } else {

                            if ($this->family_expanded != 'compact') {
                                $source_array['text'] .= '<br>';
                            } else {
                                $source_array['text'] .= '. ';
                            }

                            $source_array['text'] .= '<b>' . __('Source for person') . '</b> ';
                        }
                    } elseif ($connect_sub_kind == 'family_source') {
                        if ($nr_sources > 1) {

                            if ($connectDb->connect_order == '1') {

                                //if ($this->family_expanded != 'compact') {
                                $source_array['text'] .= '<br>';
                                //} else {
                                //    $source_array['text'] .= '. ';
                                //}

                                $source_array['text'] .= '<b>' . __('Sources for family') . '</b> ';
                            }
                        } else {

                            //if ($this->family_expanded != 'compact') {
                            $source_array['text'] .= '<br>';
                            //} else {
                            //    $source_array['text'] .= '. ';
                            //}

                            $source_array['text'] .= '<b>' . __('Source for family') . '</b> ';
                        }
                    } elseif ($screen_mode == 'RTF') {
                        $rtf_text = __('sources') . ' ';
                    }
                    $source_array['text'] .= ' <a href="' . str_replace("&", "&amp;", $_SERVER['REQUEST_URI']) . '#source_ref' . $j2 . '"><sup>' . $rtf_text . $j2 . ')</sup></a>';
                } else {
                    // *** Link to shared source ***
                    if ($connectDb->connect_source_id && $source_status === 'publish') {

                        // *** Always show title of source, show link only after permission check ***

                        $source_link = '';
                        // *** Only show link if there is a source_title. Source is only shared if there is a source_title ***
                        //if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1'){
                        if ($user['group_sources'] == 'j' && $sourceDb->source_title != '') {
                            // TODO use function
                            if ($humo_option["url_rewrite"] == "j") {
                                $url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
                            } else {
                                $url = $uri_path . 'source.php?tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
                            }
                            $source_link = '<a href="' . $url . '">';
                        }

                        if ($connect_sub_kind != 'person_source' && $connect_sub_kind != 'family_source') {
                            //$source_array['text'].= ', '.__('source').': '.$source_link;
                            $source_array['text'] .= ', ';
                            if ($nr_sources > 1) {
                                // *** Only show text once ***
                                if ($connectDb->connect_order == '1') {
                                    $source_array['text'] .= __('sources') . ': ';
                                }
                            } else {
                                $source_array['text'] .= __('source') . ': ';
                            }
                            $source_array['text'] .= $source_link;
                        } elseif ($connect_sub_kind == 'person_source') {
                            if ($connectDb->connect_order == '1') {
                                if ($this->family_expanded != 'compact') {
                                    $source_array['text'] .= '<br>';
                                } else {
                                    $source_array['text'] .= '. ';
                                }
                            } else {
                                $source_array['text'] .= ', ';
                            }

                            if ($nr_sources > 1) {
                                // *** Only show text once ***
                                if ($connectDb->connect_order == '1') {
                                    $source_array['text'] .= '<b>' . __('Sources for person') . '</b>: ';
                                }
                            } else {
                                $source_array['text'] .= '<b>' . __('Source for person') . '</b>: ';
                            }
                            $source_array['text'] .= $source_link;
                        } elseif ($connect_sub_kind == 'family_source') {
                            if ($connectDb->connect_order == '1') {
                                if ($this->family_expanded != 'compact') {
                                    $source_array['text'] .= '<br>';
                                } else {
                                    $source_array['text'] .= '. ';
                                }
                            } else {
                                $source_array['text'] .= ', ';
                            }

                            //$source_array['text'].= '<b>'.__('Source for family').'</b>'.$source_link;
                            if ($nr_sources > 1) {
                                // *** Only show text once ***
                                if ($connectDb->connect_order == '1') {
                                    $source_array['text'] .= '<b>' . __('Sources for family') . '</b>: ';
                                }
                            } else {
                                $source_array['text'] .= '<b>' . __('Source for family') . '</b>: ';
                            }
                            $source_array['text'] .= $source_link;
                        }

                        // *** Quality ***
                        if ($connectDb->connect_quality == '0' || $connectDb->connect_quality) {
                            $showQuality = new ShowQuality();
                            $quality_text = $showQuality->show_quality($connectDb->connect_quality);
                            $source_array['text'] .= ' <i>(' . $quality_text . ')</i>: ';
                        }

                        //$source_array['text'].= ': ';
                        if ($sourceDb->source_title) {
                            $source_array['text'] .= ' ' . trim($sourceDb->source_title);
                        } elseif ($sourceDb->source_text) {
                            $source_array['text'] .= ' ' . $processText->process_text($sourceDb->source_text);
                        }

                        // *** Added june 2023, also show page info ***
                        // *** Source page (connection table) ***
                        if ($connectDb->connect_page) {
                            $source_array['text'] .= ' (' . $connectDb->connect_page . ')';
                        }

                        // *** User group option to only show title of source ***
                        if ($user['group_sources'] != 't') {
                            // *** Show own code if there are no footnotes ***
                            if ($sourceDb->source_refn) {
                                $source_array['text'] .= ', ' . __('own code') . ': ' . $sourceDb->source_refn;
                            }

                            if ($sourceDb->source_date || $sourceDb->source_place) {
                                $source_array['text'] .= " " . $datePlace->date_place($sourceDb->source_date, $sourceDb->source_place);
                            }
                        }

                        // *** Only show link if there is a shared source ***
                        //if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1') $source_array['text'].= '</a>'; // *** End of link ***
                        if ($user['group_sources'] == 'j' && $sourceDb->source_title != '') {
                            $source_array['text'] .= '</a>';
                        } // *** End of link ***
                    } // *** End of shared source ***

                    // *** Show (extra) source text ***
                    if ($connectDb->connect_text && $source_status === 'publish') {
                        $source_array['text'] .= ', ' . __('source text') . ': ' . nl2br($connectDb->connect_text);
                    }

                    // *** Show picture by source ***
                    $showMedia = new ShowMedia;
                    $result = $showMedia->show_media('connect', $connectDb->connect_id);
                    $source_array['text'] .= $result[0];
                }
            } // *** Loop multiple source ***

        } // *** End of show sources ***

        //return $source_array;
        if ($source_array['text']) {
            return $source_array;
        } else {
            return '';
        }
    }
}
