<?php

/**
 * Show source list if footnotes are selected
 */

namespace Genealogy\Include;

use Genealogy\Include\DatePlace;
use Genealogy\Include\ProcessText;
use PDO;

class ShowSourcesFootnotes
{
    function show_sources_footnotes(): string
    {
        global $dbh, $db_functions, $tree_id, $user;
        global $uri_path, $source_footnote_connect_id, $humo_option;

        $datePlace = new DatePlace();
        $processText = new ProcessText();
        $showMedia = new ShowMedia();

        $text = '';

        if ($source_footnote_connect_id && count($source_footnote_connect_id) > 0) {
            $text .= '<h3>' . __('Sources') . "</h3>\n";

            for ($j = 0; $j <= (count($source_footnote_connect_id) - 1); $j++) {
                $connect_qry = "SELECT * FROM humo_connections WHERE connect_id = :connect_id";
                $connect_stmt = $dbh->prepare($connect_qry);
                $connect_stmt->execute([':connect_id' => $source_footnote_connect_id[$j]]);
                $connectDb = $connect_stmt->fetch(PDO::FETCH_OBJ);
                // *** Show shared source data ***
                if ($connectDb->connect_source_id) {
                    $sourceDb = $db_functions->get_source($connectDb->connect_source_id);
                    // *** Always show title of source, show link only after permission check ***
                    $text .= '<a name="source_ref' . ($j + 1) . '"><b>' . ($j + 1) . ')</b></a>';
                    //if ($user['group_sources']=='j'){
                    //if ($user['group_sources']=='j' AND $sourceDb->source_shared=='1'){
                    if ($user['group_sources'] == 'j' && $sourceDb->source_title != '') {
                        if ($humo_option["url_rewrite"] == "j") {
                            $url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
                        } else {
                            $url = $uri_path . 'index.php?page=source&amp;tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
                        }
                        $text .= ' <a href="' . $url . '">' . __('source') . ': ';
                        if ($sourceDb->source_title) {
                            $text .= ' ' . trim($sourceDb->source_title);
                        } else {
                            // *** Standard source without title ***
                            $text .= ' ' . $sourceDb->source_text;
                        }

                        //if ($sourceDb->source_text)
                        // $text .= ' ' . $processText->process_text($sourceDb->source_text);
                        $text .= '</a>';
                    } else {
                        if ($sourceDb->source_title) {
                            $text .= ' ' . trim($sourceDb->source_title);
                        }
                        //else{
                        // // *** Standard source without title ***
                        // $text.=' '.$sourceDb->source_text;
                        //}

                        if ($user['group_sources'] != 't' && $sourceDb->source_text) {
                            $text .= ' ' . $processText->process_text($sourceDb->source_text);
                        }

                        // *** User group option to only show title of source ***
                        // *** Show source own code ***
                        if ($user['group_sources'] != 't' && $sourceDb->source_refn) {
                            $text .= ', <b>' . __('own code') . '</b>: ' . $sourceDb->source_refn;
                        }
                    }

                    // *** User group option to only show title of source ***
                    if ($user['group_sources'] != 't') {
                        if ($connectDb->connect_date || $connectDb->connect_place) {
                            //if ($connectDb->source_title){
                            // $text.=', ';
                            //}
                            $text .= " " . $datePlace->date_place($connectDb->connect_date, $connectDb->connect_place);
                        }

                        // *** Show extra source text ***
                        if ($connectDb->connect_text) {
                            $text .= ', <b>' . __('extra text') . ':</b> ' . nl2br($connectDb->connect_text);
                        }
                    }
                }

                //OLD CODE
                //else{
                // // *** No shared source connected ***
                // $text.='<a name="source_ref'.($j+1).'">'.($j+1).')</a>';
                // // *** Source extra text ***
                // $text.=' '.nl2br($connectDb->connect_text);
                //}

                // *** Show rest of source items ***

                // *** Source role ***
                if ($connectDb->connect_role) {
                    $text .= ', <b>' . __('role') . '</b>: ' . $connectDb->connect_role;
                }

                // *** Source page (connection table) ***
                if ($connectDb->connect_page) {
                    $text .= ', <b>' . __('page') . '</b>: ' . $connectDb->connect_page;
                }
                // *** Page by source ***
                if (isset($sourceDb->source_repo_page) && $sourceDb->source_repo_page) {
                    $text .= ', <b>' . __('page') . '</b>: ' . $sourceDb->source_repo_page;
                }

                // *** Show picture by source ***
                $result = $showMedia->show_media('connect', $connectDb->connect_id);
                $text .= $result[0];

                $text .= "<br>\n";
            } // *** End of loop source footnotes ***
        }
        return $text;
    }
}
