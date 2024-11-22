<?php
// lookup which library is available or none
function create_thumbnail($folder, $file)
{
    if (extension_loaded('imagick')) {
        return (create_thumbnail_IM($folder, $file));
    } elseif ((extension_loaded('gd'))) {
        return (create_thumbnail_GD($folder, $file));
    } else {
        return (false); // no thumbnails
    }
}
// lookup which library is available or none
function resize_picture($folder, $file)
{
    if (extension_loaded('imagick')) {
        return (resize_picture_IM($folder, $file));
    } elseif ((extension_loaded('gd'))) {
        return (resize_picture_GD($folder, $file));
    } else {
        return (false); // no resizing
    }
}

// Imagick library - returns true if a thumbnail has been created (value not used yet)
function create_thumbnail_IM($folder, $file)
{
    $is_ghostscript = false;   // ghostscript has to be installed for pdf handling
    $is_ffmpeg      = false;   // ffmpeg has to be installed for video handling 
    if (trim(shell_exec('type -P gs'))) {
        $is_ghostscript = true;
    }
    if (trim(shell_exec('type -P ffmpeg'))) {
        $is_ffmpeg = true;
    }
    $add_arrow = false;
    $theight = 120; // set hight of thumbnail
    $success = true;
    $pict_path_original = $folder . $file;
    $pict_path_thumb = $folder . 'thumb_' . $file . '.jpg';
    $imtype = strtoupper(substr($file, -3));
    if (Imagick::queryformats($imtype . '*')) {
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
            $im = new \Imagick($pict_path_original . "[15]"); // [] should select frame 15 of video, not working
            $add_arrow = true;
        } else {
            $im = new \Imagick($pict_path_original);
        }
        $im->setbackgroundcolor('rgb(255, 255, 255)');
        $im->thumbnailImage(0, $theight);                     // autmatic proportional scaling
        // add play_button to movie thumbnails
        if ($add_arrow) {
            $im2 = new \Imagick(__DIR__ . '/../images/play_button.png');
            $xpos = floor($im->getImageWidth() / 2 - $im2->getImageWidth() / 2);
            $ypos = floor($im->getImageHeight() / 2 - $im2->getImageHeight() / 2);
            $im->compositeImage($im2, $im2->getImageCompose(), $xpos, $ypos);
            $im2->clear();
            $im2->destroy();
        }
        $success = ($im->writeImage($pict_path_thumb));
        $im->clear();
        $im->destroy();
    }
    return ($success);
}

// Imagic library - returns true on success or if no resizing has to be done  (value not used yet)
function resize_picture_IM($folder, $file)
{
    $maxheight = 1080;
    $maxwidth = 1920;
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
function print_thumbnail($folder, $file)
{
    $pparts = pathinfo($file);
    if (!$file || !file_exists($folder . $file)) {
        return '<img src="../images/thumb_missing-image.jpg" style="width:auto; height:120px;">';
    }
    if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
        return '<img src="' . $folder . 'thumb_' . $file . '.jpg" style="width:auto; height:120px;">';
    }
    if (file_exists($folder . 'thumb_' . $file)) {
        return '<img src="' . $folder . 'thumb_' . $file . '" style="width:auto; height:120px;">';
    }
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg')) {
        return '<img src="' . $folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg" style="width:auto; height:120px;">';
    }
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'])) {
        return '<img src="' . $folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '" style="width:auto; height:120px;">';
    }
    // no thumbnail found
    $extensions_check = strtolower(pathinfo($file, PATHINFO_EXTENSION));
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
            return '<img src="' . $folder . $file . '" style="width:auto; height:120px;" alt="JPG">';
        case 'png':
            return '<img src="' . $folder . $file . '" style="width:auto; height:120px;" alt="PNG">';
        case 'gif':
            return '<img src="' . $folder . $file . '" style="width:auto; height:120px;" alt="GIF">';
        case 'tif':
            return '<img src="' . $folder . $file . '" style="width:auto; height:120px;" alt="TIFF">';
    }
}

// returns false if mime type of file is not listed here 
function check_media_type($folder, $file)
{
    $mtypes = [
        'image/pjpeg',
        'image/jpeg',
        'image/gif',
        'image/png',
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

function thumbnail_exists($folder, $file)
{
    $pparts = pathinfo($file);
    if (!$file || !file_exists($folder . $file)) {
        return false;
    }
    if (file_exists($folder . 'thumb_' . $file . '.jpg')) {
        return true;
    }
    if (file_exists($folder . 'thumb_' . $file)) {
        return true;
    }
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'] . '.jpg')) {
        return true;
    }
    if (file_exists($folder . $pparts['dirname'] . '/thumb_' . $pparts['basename'])) {
        return true;
    }
    return false;
}
// GD library - returns true if a thumbnail has been created (value not used yet)
function create_thumbnail_GD($folder, $file)
{
    $theight = 120; // set hight of thumbnail
    $pict_path_original = $folder . $file;
    $pict_path_thumb = $folder . 'thumb_' . $file . '.jpg';
    $gd_info = gd_info();
    list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
    $imtype = strtoupper(substr($file, -3));
    $success = false;
    list($width, $height) = getimagesize($pict_path_original);
    if ($height == 0) {
        return ($success);
    }
    $twidth = ($theight / $height) * $width;
    $twidth = floor($twidth);

    if ($imtype == 'JPG' && $is_gdjpg) {
        //$create_thumb = imagecreatetruecolor($twidth, $theight);
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefromjpeg($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
    } elseif ($imtype == 'PNG' && $is_gdpng) {
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefrompng($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
    } elseif ($imtype == 'GIF' && $is_gdgif) {
        $create_thumb = imagecreatetruecolor($twidth, $theight);
        $source = imagecreatefromgif($pict_path_original);
        imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $twidth, $theight, $width, $height);
        $success = imagejpeg($create_thumb, $pict_path_thumb);
        imagedestroy($create_thumb);
        imagedestroy($source);
    }
    return ($success);
}

// GD library - returns true on success or if no resizing has to be done  (value not used yet)
function resize_picture_GD($folder, $file)
{
    $maxheight = 1080;
    $maxwidth = 1920;
    $success = false;

    $pict_path_original = $folder . $file;
    $picture_original_tmp = $folder . '0_temp' . $file . '.jpg';
    $gd_info = gd_info();
    list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
    $imtype = strtoupper(substr($file, -3));
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

    if ($imtype == 'JPG' && $is_gdjpg) {
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
