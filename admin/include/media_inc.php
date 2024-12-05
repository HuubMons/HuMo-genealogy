<?php
// holds the prefixes for existent category subdirectories
global $pcat_dirs;
$pcat_dirs = get_pcat_dirs();

// lookup which library is available or none
function create_thumbnail($folder, $file)
{
    $theight = 120; // default
    if (extension_loaded('imagick')) {
        return (create_thumbnail_IM($folder, $file, $theight)); // true on success
    } elseif ((extension_loaded('gd'))) {
        return (create_thumbnail_GD($folder, $file, $theight)); // true on success
    } else {
        return (false); // no thumbnails
    }
}
// lookup which library is available or none
function resize_picture($folder, $file)
{
    $maxheight = 2160; // default : 1080;
    $maxwidth = 3840;  // default : 1920;
    if (extension_loaded('imagick')) {
        return (resize_picture_IM($folder, $file, $maxwidth, $maxheight)); // true on success
    } elseif ((extension_loaded('gd'))) {
        return (resize_picture_GD($folder, $file, $maxwidth, $maxheight)); // true on success
    } else {
        return (false); // no resizing
    }
}

// Imagick library - returns true if a thumbnail has been created 
function create_thumbnail_IM($folder, $file, $theight = 120)
{
    $is_ghostscript = false;   // ghostscript has to be installed for pdf handling
    $is_ffmpeg      = false;   // ffmpeg has to be installed for video handling
    $no_windows = (strtolower(substr(PHP_OS, 0, 3)) !== 'win');
    if ($no_windows) {
        if (trim(shell_exec('type -P gs'))) {
            $is_ghostscript = true;
        }
        if (trim(shell_exec('type -P ffmpeg'))) {
            $is_ffmpeg = true;
        }
    }
    $add_arrow = false;
    $success = false;
    $pict_path_original = $folder . $file;
    $pict_path_thumb = $folder . 'thumb_' . $file . '.jpg';
    $imtype = strtoupper(substr($file, -3));
    if (Imagick::queryformats($imtype . '*')) {
        $fhandle = fopen($folder . '.' . $file . '.no_thumb', "w"); // create no_thumb to mark corrupt files
        fclose($fhandle);
        if ($imtype == 'PDF' && $is_ghostscript) {
            $im = new \Imagick($pict_path_original . '[0]'); //first page of PDF (default: last page)
            $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE); // without you only get black frames
        } elseif (($imtype == 'MP4' ||
                $imtype == 'MPG' ||
                $imtype == 'FLV' ||
                $imtype == 'MOV' ||
                $imtype == 'AVI')
            && $is_ffmpeg
        ) {
            $im = new \Imagick($pict_path_original . "[15]"); // [] should select frame 15 of video, not working, allways takes the first frame
            $add_arrow = true;
        } else {
            $im = new \Imagick($pict_path_original);
        }
        $im->setbackgroundcolor('rgb(255, 255, 255)');
        $im->thumbnailImage(0, $theight);                     // automatic proportional scaling
        // add play_button to movie thumbnails
        if ($add_arrow && is_file(__DIR__ . '/../images/play_button.png')) {
            $im2 = new \Imagick(__DIR__ . '/../images/play_button.png');
            $xpos = floor($im->getImageWidth() / 2 - $im2->getImageWidth() / 2);
            $ypos = floor($im->getImageHeight() / 2 - $im2->getImageHeight() / 2);
            $im->compositeImage($im2, $im2->getImageCompose(), $xpos, $ypos);
            $im2->clear();
            $im2->destroy();
        }
        $success = ($im->writeImage($pict_path_thumb));
        unlink($folder . '.' . $file . '.no_thumb');  // delete no_thumb
        $im->clear();
        $im->destroy();
    }
    return ($success);
}

// Imagic library - returns true on success or if picture already fits 
function resize_picture_IM($folder, $file, $maxheight = 1080, $maxwidth = 1920)
{
    $success = true;
    $pict_path_original = $folder . $file;
    $pict_path_resize = $pict_path_original;
    $imtype = strtoupper(substr($file, -3));
    if (
        $imtype == 'JPG' ||
        $imtype == 'PNG' ||
        $imtype == 'BMP' ||
        $imtype == 'TIF' ||
        $imtype == 'GIF'
    ) {
        if (Imagick::queryformats($imtype . '*'))  // format supported by Imagick?
        {
            $im = new \Imagick($pict_path_original);
            if ($im->getImageHeight() > $maxheight || $im->getImageWidth() > $maxwidth) {
                $im->resizeImage($maxwidth, $maxheight, Imagick::FILTER_CATROM, 1, true);
                $success = $im->writeImage($pict_path_resize);
                $im->clear();
                $im->destroy();
            }
        }
    }
    return ($success);
}
//search for a thumbnail or mime type placeholder and returns the image tag
function print_thumbnail($folder, $file, $maxw = 0, $maxh = 120, $css = '', $attrib = '')
{
    global $pcat_dirs, $humo_option;
    // in current state this function is not displayiung all formats of pictures that are allowed - for example it's not displaying webp
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
        // echo 'thumb missing';
        return '<img src="../images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' missing path/filename">';
    }

    $thumb_url =  thumbnail_exists($folder, $file);
    // echo '<br>sprawdzenie thumb_url: ' . $thumb_url;
    if (!empty($thumb_url)) {
        // echo '<br>!empty(thumb_url)';
        // there are problems with these relative paths - when called from lvl +1 (show_picture.php) its ok, when called from lvl +2 (editor_event_cls.php, thumbs.php) it gives bad directory argument for give_media_path so i quick fix this by deciding dir and prefix dependant on calling file
        $backtrace = debug_backtrace();
        if (isset($backtrace[0]['file']) && isset($backtrace[0]['line'])) {
            $calling_file = basename($backtrace[0]['file']);
            // echo "<br>Function was called by:" . $calling_file;
        }
        include_once(__DIR__ . '/../../include/give_media_path.php');
        if ($calling_file === 'editor_event_cls.php' || $calling_file === 'thumbs.php' || $calling_file === 'editor_media_select.php') {
            $folder_for_give_media_path = substr($folder, 3);
            $prefix = '../';
        } else {
            $folder_for_give_media_path = $folder;
            $prefix = '';
        }
        // echo "baza<br>";
        // echo basename($thumb_url);
        // echo "baza<br>";

        // i modified thumbnail_exist function to serve also only file in swcond mode with its logic becouse i have not enough knowledge for new/old paths/files format - so i copy the logic to be consistent
        $mode = 'onlyfile';
        $fileName = thumbnail_exists($folder, $file, $mode);

        // echo '<br>onlyfilename:' . $fileName . '<br>';

        $src_path = give_media_path($folder_for_give_media_path, $fileName);
        // echo '<br>src_path:' . $src_path;
        // echo '<br>src path:' . $src_path;
        return '<img src="' . $prefix . $src_path . '"' . $img_style . '>';
    } // found thumbnail

    // no thumbnail found, create a new one
    // first check if/where org_file exist
    if (array_key_exists(substr($file, 0, 3), $pcat_dirs)) {
        $folder .= substr($file, 0, 2) . '/';
    } // photobook categories
    if (!file_exists($folder . $file)) {
        // echo '<br>nie istnieje plik1:' . $folder . $file;
        return '<img src="../images/thumb_missing-image.jpg" style="width:auto; height:120px;" title="' . $folder . $file . ' not found">';
    }
    // check for mime type and no_thumb file
    if (
        check_media_type($folder, $file) &&
        !is_file($folder . '.' . $file . '.no_thumb')
    ) {
        // script will possibily die here and hidden no_thumb file becomes persistent
        // so this code might be skiped afterwords
        if ($humo_option["thumbnail_auto_create"] == 'y' && create_thumbnail($folder, $file)) {
            include_once(__DIR__ . '/../../include/give_media_path.php');
            $src_path = give_media_path($folder, 'thumb_' . $file . '.jpg');
            return '<img src="' . $src_path . '"' . $img_style . '>';
        }
    }

    $extensions_check = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    include_once(__DIR__ . '/../../include/give_media_path.php');
    $src_path = give_media_path($folder, $file);
    switch ($extensions_check) {
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
    }
    return '<img src="../images/thumb_missing-image.jpg"' . $img_style . '>';
}
// returns false if mime type of file is not listed here
function check_media_type($folder, $file)
{
    $mtypes = [
        'image/pjpeg',
        'image/jpeg',
        'image/gif',
        'image/png',
        'image/bmp',
        'image/tiff',
        'audio/mpeg',
        'audio/mpeg3',
        'audio/x-mpeg',
        'audio/x-mpeg3',
        'audio/mpg',
        'audio/mp3',
        'audio/mid',
        'audio/midi',
        'audio/x-midi',
        'audio/x-ms-wma',
        'audio/wav',
        'audio/x-wav',
        'audio/x-pn-realaudio',
        'audio/x-realaudio',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/quicktime',
        'video/x-flv',
        'video/avi',
        'video/x-msvideo',
        'video/msvideo',
        'video/mpeg',
        'video/mp4'
    ];
    $mtype  = mime_content_type($folder . $file);

    if (in_array($mtype, $mtypes)) {
        return (true);
    }
    return (false);
}

function thumbnail_exists($folder, $file, $mode = 'both')
{
    global $pcat_dirs;
    // echo '<br>folder ze środka t_e: ' . $folder;

    //added second mode to return only the filename part for function give_media_path (see line ~159)
    if ($mode === 'onlyfile') {
        $folder1 = '';
    } elseif ($mode === 'both') {
        $folder1 = $folder;
    }

    $pparts = pathinfo($file);


    if (!$file || !file_exists($folder . $file)) {
        // echo '<br>thumbnail_exist wnętrz - nie istnieje';
        return '';
    }
    if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
        return ($folder1 . 'thumb_' . $file . '.jpg');
    }
    if (file_exists($folder . 'thumb_' . $file)) {
        return ($folder1 . 'thumb_' . $file);
    } // old naming
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg')) {
        return ($folder1 . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg');
    }
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'])) {
        return ($folder1 . $pparts['dirname'] . '/thumb_' . $pparts['basename']);
    } // old naming
    if (array_key_exists(substr($file, 0, 3), $pcat_dirs)) {
        $folder .= substr($file, 0, 2) . '/';
    } // check for cat folder
    if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
        return ($folder1 . 'thumb_' . $file . '.jpg');
    }
    if (file_exists($folder . 'thumb_' . $file)) {
        return ($folder1 . 'thumb_' . $file);
    }  // old naming
    return '';
}
// GD library - returns true if a thumbnail has been created
function create_thumbnail_GD($folder, $file, $theight = 120)
{
    $pict_path_original = $folder . $file;
    $pict_path_thumb = $folder . 'thumb_' . $file . '.jpg';
    $gd_info = gd_info();
    list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
    $gdmime = get_GDmime(); // a.array
    $imtype = $gdmime[check_media_type($folder, $file)];
    $success = false;
    list($width, $height) = getimagesize($pict_path_original);
    if ($height == 0) {
        return ($success);
    }
    $twidth = floor($width * ($theight / $height));

    if (($imtype == 'JPEG' || $imtype == 'JPG') && $is_gdjpg) {
        $fhandle = fopen($folder . '.' . $file . '.no_thumb', "w"); // create no_thumb to mark corrupt files
        fclose($fhandle);
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefromjpeg($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
        unlink($folder . '.' . $file . '.no_thumb');  // delete no_thumb   
    } elseif ($imtype == 'PNG' && $is_gdpng) {
        $fhandle = fopen($folder . '.' . $file . '.no_thumb', "w"); // create no_thumb to mark corrupt files
        fclose($fhandle);
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefrompng($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
        unlink($folder . '.' . $file . '.no_thumb');  // delete no_thumb   
    } elseif ($imtype == 'GIF' && $is_gdgif) {
        $fhandle = fopen($folder . '.' . $file . '.no_thumb', "w"); // create no_thumb to mark corrupt files
        fclose($fhandle);
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefromgif($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
        unlink($folder . '.' . $file . '.no_thumb');  // delete no_thumb   
    }
    return ($success);
}

// GD library - returns true on success or if no resizing has to be done
function resize_picture_GD($folder, $file, $maxheight = 1080, $maxwidth = 1920)
{
    $success = false;

    $pict_path_original = $folder . $file;
    $picture_original_tmp = $folder . '0_temp' . $file . '.jpg';
    $gd_info = gd_info();
    list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
    $gdmime = get_GDmime(); // a.array
    $imtype = $gdmime[check_media_type($folder, $file)];
    list($width, $height) = getimagesize($pict_path_original);
    if ($width <= $maxwidth && $height <= $maxheight) {
        return (true);
    }
    if ($height == 0) {
        return (false);
    }
    if ($maxheight <= $maxwidth) {
        $rheight = $maxheight;
        $rwidth = ($rheight / $height) * $width;
    } else {
        $rwidth = $maxwidth;
        $rheight = ($rwidth / $width) * $height;
    }
    echo ('Resize: ' . $rwidth . ' - ' . $rheight);
    if (($imtype == 'JPEG' || $imtype == 'JPG') && $is_gdjpg) {
        rename($pict_path_original, $picture_original_tmp);
        $create_resized = imagecreatetruecolor($rwidth, $rheight);
        $source = imagecreatefromjpeg($picture_original_tmp);
        imagecopyresized($create_resized, $source, 0, 0, 0, 0, $rwidth, $rheight, $width, $height);
        $success = imagejpeg($create_resized, $pict_path_original);
        imagedestroy($create_resized);
        imagedestroy($source);
        unlink($picture_original_tmp);
    } elseif ($imtype == 'PNG' && $is_gdpng) {
        rename($pict_path_original, $picture_original_tmp);
        $create_resized = imagecreatetruecolor($rwidth, $rheight);
        $source = imagecreatefrompng($picture_original_tmp);
        imagecopyresized($create_resized, $source, 0, 0, 0, 0, $rwidth, $rheight, $width, $height);
        $success = imagepng($create_resized, $pict_path_original);
        imagedestroy($create_resized);
        imagedestroy($source);
        unlink($picture_original_tmp);
    } elseif ($imtype == 'GIF' && $is_gdgif) {
        rename($pict_path_original, $picture_original_tmp);
        $create_resized = imagecreatetruecolor($rwidth, $rheight);
        $source = imagecreatefromgif($picture_original_tmp);
        imagecopyresized($create_resized, $source, 0, 0, 0, 0, $rwidth, $rheight, $width, $height);
        $success = imagegif($create_resized, $pict_path_original);
        imagedestroy($create_resized);
        imagedestroy($source);
        unlink($picture_original_tmp);
    }
    return ($success);
}
function get_pcat_dirs() // returns a.array with existing cat subfolders key=>dir val=>category name localized
{
    global $dbh, $tree_id, $selected_language;

    $data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $tree_id);
    $dataDb = $data2sql->fetch(PDO::FETCH_OBJ);
    $tree_pict_path = $dataDb->tree_pict_path;
    if (substr($tree_pict_path, 0, 1) === '|') {
        $tree_pict_path = 'media/';
    }
    // adjust path to media dir
    $tree_pict_path = __DIR__ . '/../../' . $tree_pict_path;
    $tmp_pcat_dirs = array();
    $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
    if ($temp->rowCount()) {   // there is a category table
        $catg = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
        if ($catg->rowCount()) {
            while ($catDb = $catg->fetch(PDO::FETCH_OBJ)) {
                $dirtest = $catDb->photocat_prefix;
                if (is_dir($tree_pict_path . '/' . substr($dirtest, 0, 2))) {  // there is a subfolder of this prefix
                    $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = '" . $selected_language . "'");
                    if ($name->rowCount()) {  // there is a name for this language
                        $nameDb = $name->fetch(PDO::FETCH_OBJ);
                        $catname = $nameDb->photocat_name;
                    } else {  // maybe a default is set
                        $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = 'default'");
                        if ($name->rowCount()) {  // there is a default name for this category
                            $nameDb = $name->fetch(PDO::FETCH_OBJ);
                            $catname = $nameDb->photocat_name;
                        } else {  // no name found => show directory name
                            $catname = substr($dirtest, 0, 2);
                        }
                    }
                    $tmp_pcat_dirs[$dirtest] = $catname;
                }
            }
        }
    }
    return $tmp_pcat_dirs;
}
function get_GDmime()
{
    return [
        'image/pjpeg'  => 'JPG',
        'image/jpeg'   => 'JPG',
        'image/gif'    => 'GIF',
        'image/png'    => 'PNG',
        'image/bmp'    => 'BMP',
        'image/tiff'   => 'TIF',
        'audio/mpeg'   => '-',
        'audio/mpeg3'  => '-',
        'audio/x-mpeg' => '-',
        'audio/x-mpeg3' => '-',
        'audio/mpg'    => '-',
        'audio/mp3'    => '-',
        'audio/mid'    => '-',
        'audio/midi'   => '-',
        'audio/x-midi' => '-',
        'audio/x-ms-wma' => '-',
        'audio/wav'      => '-',
        'audio/x-wav'    => '-',
        'audio/x-pn-realaudio' => '-',
        'audio/x-realaudio'   => '-',
        'application/pdf'     => '-',
        'application/msword'  => '-',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'  => '-',
        'video/quicktime' => '-',
        'video/x-flv'     => '-',
        'video/avi'       => '-',
        'video/x-msvideo' => '-',
        'video/msvideo'   => '-',
        'video/mpeg'      => '-',
        'video/mp4'       => '-'
    ];
}
