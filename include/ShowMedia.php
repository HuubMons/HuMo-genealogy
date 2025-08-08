<?php

/**
 * Class to show media by person, marriage, etc.
 * 
 * Updated feb 2013, aug 2015, feb 2023.
 * Dec. 2024: rebuild to class
 */

namespace Genealogy\Include;

use Genealogy\Include\DatePlace;
use Genealogy\Include\MediaPath;
use Genealogy\Include\ProcessText;
use Genealogy\Include\ShowSources;
use Genealogy\Include\ResizePicture;
use PDO; // For database access

class ShowMedia
{
    public $pcat_dirs = array();

    public function __construct()
    {
        $this->set_pcat_dirs();
    }

    public function get_pcat_dirs(): array
    {
        return $this->pcat_dirs;
    }

    public function show_media($event_connect_kind, $event_connect_id): array
    {
        global $dbh, $db_functions, $tree_id, $user, $selectedFamilyTree, $data, $page;
        global $screen_mode; // *** RTF Export ***

        $datePlace = new DatePlace();
        $mediaPath = new MediaPath();
        $processText = new ProcessText();
        $showSources = new ShowSources();

        $templ_person = array(); // local version
        $process_text = '';
        $media_nr = 0;

        // *** Pictures/ media ***
        if ($user['group_pictures'] == 'j' && isset($data["picture_presentation"]) && $data["picture_presentation"] != 'hide') {
            if (isset($selectedFamilyTree->tree_pict_path)) {
                $tree_pict_path = $selectedFamilyTree->tree_pict_path;
            } else {
                $tree_pict_path = 'media/';
            }
            //$tree_pict_path = $selectedFamilyTree->tree_pict_path ?? 'media/';

            // *** Use default folder: media ***
            if (substr($tree_pict_path, 0, 1) === '|') {
                $tree_pict_path = 'media/';
            }

            //TODO check PDF code
            if ($screen_mode == 'PDF') {
                $tree_pict_path = __DIR__ . '/../' . $tree_pict_path;
            }

            // *** Standard connected media by person and family ***
            // TODO: show these items seperately: picture_birth, picture_death, picture_marriage, picture_burial etc.
            $sql = "SELECT * FROM humo_events WHERE event_tree_id = :tree_id
                AND event_connect_kind = :event_connect_kind
                AND event_connect_id = :event_connect_id
                AND LEFT(event_kind, 7) = 'picture'
                ORDER BY event_kind, event_order";
            $picture_qry = $dbh->prepare($sql);
            $picture_qry->execute([
                ':tree_id' => $tree_id,
                ':event_connect_kind' => $event_connect_kind,
                ':event_connect_id' => $event_connect_id
            ]);
            while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                $media_nr++;
                $media_event_id[$media_nr] = $pictureDb->event_id;
                $media_event_event[$media_nr] = $pictureDb->event_event;
                $media_event_date[$media_nr] = $pictureDb->event_date;
                $media_event_place[$media_nr] = $pictureDb->event_place;
                $media_event_text[$media_nr] = $pictureDb->event_text;
                // *** Remove last seperator ***
                if ($media_event_text[$media_nr] && substr(rtrim($media_event_text[$media_nr]), -1) === "|") {
                    $media_event_text[$media_nr] = substr($media_event_text[$media_nr], 0, -1);
                }
                //$media_event_source[$media_nr]=$pictureDb->event_source;
            }

            // *** Search for all external connected objects by a person, family or source ***
            if ($event_connect_kind == 'person') {
                $connect_sql = $db_functions->get_connections_connect_id('person', 'pers_object', $event_connect_id);
            } elseif ($event_connect_kind == 'family') {
                $connect_sql = $db_functions->get_connections_connect_id('family', 'fam_object', $event_connect_id);
            } elseif ($event_connect_kind == 'source') {
                $connect_sql = $db_functions->get_connections_connect_id('source', 'source_object', $event_connect_id);
            }

            if ($event_connect_kind == 'person' || $event_connect_kind == 'family' || $event_connect_kind == 'source') {
                foreach ($connect_sql as $connectDb) {
                    $sql = "SELECT * FROM humo_events WHERE event_tree_id = :tree_id
                        AND event_gedcomnr = :event_gedcomnr AND event_kind = 'object'
                        ORDER BY event_order";
                    $picture_qry = $dbh->prepare($sql);
                    $picture_qry->execute([
                        ':tree_id' => $tree_id,
                        ':event_gedcomnr' => $connectDb->connect_source_id
                    ]);
                    while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                        $media_nr++;
                        $media_event_id[$media_nr] = $pictureDb->event_id;
                        $media_event_event[$media_nr] = $pictureDb->event_event;
                        $media_event_date[$media_nr] = $pictureDb->event_date;
                        $media_event_place[$media_nr] = $pictureDb->event_place;
                        $media_event_text[$media_nr] = $pictureDb->event_text;
                        // *** Remove last seperator ***
                        if (substr(rtrim($media_event_text[$media_nr]), -1) === "|") {
                            $media_event_text[$media_nr] = substr($media_event_text[$media_nr], 0, -1);
                        }
                        //$media_event_source[$media_nr]=$pictureDb->event_source;
                    }
                }
            }

            // ******************
            // *** Show media ***
            // ******************
            if ($media_nr > 0) {
                if ($screen_mode == "RTF") {
                    $process_text .= "\n";
                } else {
                    $process_text .= '<br>';
                }
            }

            for ($i = 1; $i < ($media_nr + 1); $i++) {
                $dateplace = $datePlace->date_place($media_event_date[$i], $media_event_place[$i]);
                // *** If possible show a thumb ***

                // *** Don't use entities in a picture ***
                //$event_event = html_entity_decode($pictureDb->event_event, ENT_NOQUOTES, 'ISO-8859-15');
                $event_event = $media_event_event[$i];

                // in case subfolders are made for photobook categories and this was not already set in $picture_path, look there
                // (if the $picture_path is already set with subfolder this anyway gives false and so the $picture_path given will work)
                $temp_path = $tree_pict_path; // use temp path to modify

                // look in category subfolder if exists
                if (array_key_exists(substr($event_event, 0, 3), $this->pcat_dirs)) {
                    $temp_path .= substr($event_event, 0, 2) . '/';
                }

                // *** In some cases the picture name must be converted to lower case ***
                if (file_exists($temp_path . strtolower($event_event))) {
                    $event_event = strtolower($event_event);
                }
                // *** Show photo using the lightbox effect ***
                if (in_array(strtolower(pathinfo($event_event, PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'tif'))) {

                    $line_pos = 0;
                    if ($media_event_text[$i]) {
                        $line_pos = strpos($media_event_text[$i], "|");
                    }
                    $title_txt = $media_event_text[$i];
                    if ($line_pos > 0) {
                        $title_txt = substr($media_event_text[$i], 0, $line_pos);
                    }
                    $href_path = $mediaPath->give_media_path($temp_path, str_ireplace("%2F", "/", rawurlencode($event_event)));
                    // *** April 2021: using GLightbox ***
                    // *** lightbox can't handle brackets etc so encode it. ("urlencode" doesn't work since it changes spaces to +, so we use rawurlencode)
                    // *** But: reverse change of / character (if sub folders are used) ***
                    $picture = '<a href="' . $href_path . '" class="glightbox3" data-gallery="gallery' . $event_connect_id . '" data-glightbox="description: .custom-desc' . $media_event_id[$i] . '">';
                    // $picture = '<a href="' . $temp_path . str_ireplace("%2F", "/", rawurlencode($event_event)) . '" class="glightbox3" data-gallery="gallery' . $event_connect_id . '" data-glightbox="description: .custom-desc' . $media_event_id[$i] . '">';

                    // *** Need a class for multiple lines and HTML code in a text ***
                    $picture .= '<div class="glightbox-desc custom-desc' . $media_event_id[$i] . '">';
                    if ($dateplace) {
                        $picture .= $dateplace . '<br>';
                    }
                    $picture .= $title_txt . '</div>';
                    $picture .= $this->print_thumbnail($tree_pict_path, $event_event); // sing default hight 120px
                    $picture .= '</a>';

                    $thumb_url = $this->thumbnail_exists($temp_path, $event_event); // returns url of thumb or empty string
                    if (!empty($thumb_url)) {
                        $templ_person["pic_path" . $i] = $thumb_url; //for the time being pdf only with thumbs
                    } else {
                        $templ_person["pic_path" . $i] = $temp_path . $event_event; // use original picture instead
                    }
                    // *** Remove spaces ***
                    $templ_person["pic_path" . $i] = trim($templ_person["pic_path" . $i]);
                } else {
                    // other media formats not to be displayed with lightbox
                    $href_path = $mediaPath->give_media_path($temp_path, $event_event);
                    $picture = '<a href="' . $href_path . '" target="_blank">' . $this->print_thumbnail($temp_path, $event_event) . '</a>';
                }

                // *** Show picture date and place ***
                $picture_text = '';
                if ($media_event_date[$i] || $media_event_place[$i]) {
                    if ($screen_mode != 'RTF') {
                        $picture_text = $dateplace . ' ';
                    }
                    $templ_person["pic_text" . $i] = $dateplace;
                }

                // *** Show text by picture of little space ***
                if (isset($media_event_text[$i]) && $media_event_text[$i]) {
                    if ($screen_mode != 'RTF') {
                        //$picture_text.=' '.str_replace("&", "&amp;", $media_event_text[$i]);
                        $picture_text .= ' ' . str_replace("&", "&amp;", $processText->process_text($media_event_text[$i]));
                    }
                    if (isset($templ_person["pic_text" . $i])) {
                        $templ_person["pic_text" . $i] .= ' ' . $media_event_text[$i];
                    } else {
                        $templ_person["pic_text" . $i] = $media_event_text[$i];
                    }
                }

                if ($screen_mode != 'RTF') {
                    // Jan. 2024: Don't connect a source to a picture if source page is shown.
                    if ($page != 'source') {
                        // *** Show source by picture ***
                        $source_array = '';
                        if ($event_connect_kind == 'person') {
                            $source_array = $showSources->show_sources2("person", "pers_event_source", $media_event_id[$i]);
                        } else {
                            $source_array = $showSources->show_sources2("family", "fam_event_source", $media_event_id[$i]);
                        }
                        if ($source_array) {
                            $picture_text .= $source_array['text'];
                        }
                    }

                    $process_text .= '<div class="photo">';
                    $process_text .= $picture;

                    if (!file_exists($temp_path . $event_event) && !file_exists($temp_path . strtolower($event_event))) {
                        $picture_text .= '<br><b>' . __('Missing image') . ':<br>' . $temp_path . $event_event . '</b>';
                    }
                    // *** Show text by picture ***
                    if (isset($picture_text)) {
                        $process_text .= '<div class="phototext">' . $picture_text . '</div>';
                    }
                    $process_text .= '</div>' . "\n";
                }
            }

            if ($media_nr > 0) {
                $process_text .= '<br clear="All">';
                $templ_person["got_pics"] = 1;
            }
        }
        //return $process_text;
        $result[0] = $process_text;
        $result[1] = $templ_person; // local version with pic data
        return $result;
    }

    //search for a thumbnail or mime type placeholder and returns the image tag
    public function print_thumbnail($folder, $file, $maxw = 0, $maxh = 120, $css = '', $attrib = ''): string
    {
        global $humo_option;

        $mediaPath = new MediaPath();
        $resizePicture = new ResizePicture();

        // in current state this function is not displaying all formats of pictures that are allowed - for example it's not displaying webp
        // echo 'print thumbnail<br>';
        // echo 'folder:' . $folder;
        // echo '<br>file:' . $file;
        $img_style = ' style="';
        if ($maxw > 0 && $maxh > 0) {
            $img_style .= 'width:auto; height:auto; max-width:' . $maxw . 'px; max-height:' . $maxh . 'px; ' . $css . '" ' . $attrib;
        } elseif ($maxw > 0) {
            $img_style .= 'height:auto; max-width:' . $maxw . 'px; ' . $css . '" ' . $attrib;
        } elseif ($maxh > 0) {
            $img_style .= 'width:auto; max-height:' . $maxh . 'px; ' . $css . '" ' . $attrib;
        } else {
            $img_style .= 'width:auto; height:120px; ' . $css . '" ' . $attrib;
        }

        if (!$file || !$folder) {
            if (file_exists('images/thumb_missing-image.jpg')) {
                // Front pages:
                return '<img src="images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' missing path/filename">';
            } else {
                // Admin pages:
                return '<img src="../images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' missing path/filename">';
            }
        }

        $thumb_url =  $this->thumbnail_exists($folder, $file);
        // *** found thumbnail ***
        if (!empty($thumb_url)) {
            // there are problems with these relative paths - when called from lvl +1 (showMedia) its ok, when called from lvl +2 (editorEvent.php, thumbs.php) it gives bad directory argument for give_media_path so i quick fix this by deciding dir and prefix dependant on calling file
            $backtrace = debug_backtrace();
            if (isset($backtrace[0]['file']) && isset($backtrace[0]['line'])) {
                $calling_file = basename($backtrace[0]['file']);
                // echo "<br>Function was called by:" . $calling_file;
            }
            if ($calling_file === 'editorEvent.php' || $calling_file === 'thumbs.php' || $calling_file === 'editor_media_select.php') {
                $folder_for_give_media_path = substr($folder, 3);
                $prefix = '../';
            } else {
                $folder_for_give_media_path = $folder;
                $prefix = '';
            }

            // I modified thumbnail_exist function to serve also only file in swcond mode with its logic becouse i have not enough knowledge for new/old paths/files format - so i copy the logic to be consistent
            $mode = 'onlyfile';
            $fileName = $this->thumbnail_exists($folder, $file, $mode);

            $src_path = $mediaPath->give_media_path($folder_for_give_media_path, $fileName);
            return '<img src="' . $prefix . $src_path . '"' . $img_style . '>';
        }

        // no thumbnail found, create a new one, first check if/where org_file exist
        if (array_key_exists(substr($file, 0, 3), $this->pcat_dirs)) {
            $folder .= substr($file, 0, 2) . '/';
        } // photobook categories
        if (!file_exists($folder . $file)) {
            if (file_exists('images/thumb_missing-image.jpg')) {
                // Front pages:
                return '<img src="images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' not found">';
            } else {
                // Admin pages:
                return '<img src="../images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' not found">';
            }
        }
        // check for mime type and no_thumb file
        if (
            $resizePicture->check_media_type($folder, $file) &&
            !is_file($folder . '.' . $file . '.no_thumb')
        ) {
            // script will possibily die here and hidden no_thumb file becomes persistent
            // so this code might be skiped afterwords
            if ($humo_option["thumbnail_auto_create"] == 'y' && $resizePicture->create_thumbnail($folder, $file)) {
                $src_path = $mediaPath->give_media_path($folder, 'thumb_' . $file . '.jpg');
                return '<img src="' . $src_path . '"' . $img_style . '>';
            }
        }

        $extensions_check = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $src_path = $mediaPath->give_media_path($folder, $file);
        switch ($extensions_check) {
                /*
            case 'pdf':
                return '<img src="../images/pdf.jpg" alt="PDF">';
            case 'docx':
                return '<img src="../images/msdoc.gif" alt="DOCX">';
            case 'doc':
                return '<img src="../images/msdoc.gif" alt="DOC">';
            case 'wmv':
                return '<img src="../images/video-file.png" alt="WMV">';
            case 'avi':
                return '<img src="../images/video-file.png" alt="AVI">';
            case 'mp4':
                return '<img src="../images/video-file.png" alt="MP4">';
            case 'mpg':
                return '<img src="../images/video-file.png" alt="MPG">';
            case 'mov':
                return '<img src="../images/video-file.png" alt="MOV">';
            case 'wma':
                return '<img src="../images/video-file.png" alt="WMA">';
            case 'wav':
                return '<img src="../images/audio.gif" alt="WAV">';
            case 'mp3':
                return '<img src="../images/audio.gif" alt="MP3">';
            case 'mid':
                return '<img src="../images/audio.gif" alt="MID">';
            case 'ram':
                return '<img src="../images/audio.gif" alt="RAM">';
            case 'ra':
                return '<img src="../images/audio.gif" alt="RA">';
            case 'jpg':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'jpeg':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'png':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'gif':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'tif':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'tiff':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            case 'bmp':
                return '<img src="../' . $src_path . '"' . $img_style . '>';
            */

            case 'pdf':
                return '<img src="images/pdf.jpg" alt="PDF">';
            case 'docx':
                return '<img src="images/msdoc.gif" alt="DOCX">';
            case 'doc':
                return '<img src="images/msdoc.gif" alt="DOC">';
            case 'wmv':
                return '<img src="images/video-file.png" alt="WMV">';
            case 'avi':
                return '<img src="images/video-file.png" alt="AVI">';
            case 'mp4':
                return '<img src="images/video-file.png" alt="MP4">';
            case 'mpg':
                return '<img src="images/video-file.png" alt="MPG">';
            case 'mov':
                return '<img src="images/video-file.png" alt="MOV">';
            case 'wma':
                return '<img src="images/video-file.png" alt="WMA">';
            case 'wav':
                return '<img src="images/audio.gif" alt="WAV">';
            case 'mp3':
                return '<img src="images/audio.gif" alt="MP3">';
            case 'mid':
                return '<img src="images/audio.gif" alt="MID">';
            case 'ram':
                return '<img src="images/audio.gif" alt="RAM">';
            case 'ra':
                return '<img src="images/audio.gif" alt="RA">';
            case 'jpg':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'jpeg':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'png':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'gif':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'tif':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'tiff':
                return '<img src="' . $src_path . '"' . $img_style . '>';
            case 'bmp':
                return '<img src="' . $src_path . '"' . $img_style . '>';
        }
        //return '<img src="../images/thumb_missing-image.jpg"' . $img_style . '>';
        //return '<img src="../../images/thumb_missing-image.jpg"' . $img_style . '>';

        // No thumbnail found, return the original file.
        $src_path = $mediaPath->give_media_path($folder, $file);
        return '<img src="' . $src_path . '"' . $img_style . '>';
    }

    public function thumbnail_exists($folder, $file, $mode = 'both'): string
    {
        //added second mode to return only the filename part for function give_media_path (see line ~159)
        if ($mode === 'onlyfile') {
            $folder1 = '';
        } elseif ($mode === 'both') {
            $folder1 = $folder;
        }

        $pparts = pathinfo($file);

        if (!$file || !file_exists($folder . $file)) {
            return '';
        }
        if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
            return ($folder1 . 'thumb_' . $file . '.jpg');
        }
        if (file_exists($folder . 'thumb_' . $file)) {
            // old naming
            return ($folder1 . 'thumb_' . $file);
        }
        if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg')) {
            return ($folder1 . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg');
        }
        if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'])) {
            // old naming
            return ($folder1 . $pparts['dirname'] . '/thumb_' . $pparts['basename']);
        }

        if (array_key_exists(substr($file, 0, 3), $this->pcat_dirs)) {
            // check for cat folder
            $folder .= substr($file, 0, 2) . '/';
        }
        if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
            return ($folder1 . 'thumb_' . $file . '.jpg');
        }
        if (file_exists($folder . 'thumb_' . $file)) {
            // old naming
            return ($folder1 . 'thumb_' . $file);
        }
        return '';
    }

    // returns a.array with existing cat subfolders key=>dir val=>category name localized
    private function set_pcat_dirs(): void
    {
        global $dbh, $tree_id, $selected_language;

        $data2sql = $dbh->prepare("SELECT * FROM humo_trees WHERE tree_id = :tree_id");
        $data2sql->execute([':tree_id' => $tree_id]);
        $FamilyTree = $data2sql->fetch(PDO::FETCH_OBJ);
        $tree_pict_path = $FamilyTree->tree_pict_path;
        if (substr($tree_pict_path, 0, 1) === '|') {
            $tree_pict_path = 'media/';
        }
        // adjust path to media dir
        $tree_pict_path = __DIR__ . '/../../' . $tree_pict_path;
        $tmp_pcat_dirs = array();
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if ($temp->rowCount()) {
            // there is a category table
            $catg = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
            if ($catg->rowCount()) {
                while ($catDb = $catg->fetch(PDO::FETCH_OBJ)) {
                    $dirtest = $catDb->photocat_prefix;
                    if (is_dir($tree_pict_path . '/' . substr($dirtest, 0, 2))) {
                        // there is a subfolder of this prefix
                        $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = '" . $selected_language . "'");
                        if ($name->rowCount()) {
                            // there is a name for this language
                            $nameDb = $name->fetch(PDO::FETCH_OBJ);
                            $catname = $nameDb->photocat_name;
                        } else {
                            // maybe a default is set
                            $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = 'default'");
                            if ($name->rowCount()) {
                                // there is a default name for this category
                                $nameDb = $name->fetch(PDO::FETCH_OBJ);
                                $catname = $nameDb->photocat_name;
                            } else {
                                // no name found => show directory name
                                $catname = substr($dirtest, 0, 2);
                            }
                        }
                        $tmp_pcat_dirs[$dirtest] = $catname;
                    }
                }
            }
        }

        $this->pcat_dirs = $tmp_pcat_dirs;
    }
}
