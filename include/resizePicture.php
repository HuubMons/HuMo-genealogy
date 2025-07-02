<?php

/**
 * Class ResizePicture
 * 
 * Jun. 2025 Huub: changed into class.
 */

class ResizePicture
{
    // lookup which library is available or none
    public function create_thumbnail($folder, $file)
    {
        $theight = 120; // default
        if (extension_loaded('imagick')) {
            return ($this->create_thumbnail_IM($folder, $file, $theight)); // true on success
        } elseif ((extension_loaded('gd'))) {
            return ($this->create_thumbnail_GD($folder, $file, $theight)); // true on success
        } else {
            return (false); // no thumbnails
        }
    }

    // lookup which library is available or none
    public function resize_picture($folder, $file)
    {
        $maxheight = 2160; // default : 1080;
        $maxwidth = 3840;  // default : 1920;
        if (extension_loaded('imagick')) {
            return ($this->resize_picture_IM($folder, $file, $maxwidth, $maxheight)); // true on success
        } elseif ((extension_loaded('gd'))) {
            return ($this->resize_picture_GD($folder, $file, $maxwidth, $maxheight)); // true on success
        } else {
            return (false); // no resizing
        }
    }

    // Imagick library - returns true if a thumbnail has been created 
    private function create_thumbnail_IM($folder, $file, $theight = 120)
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
            if ($imtype == 'PDF') {
                if ($is_ghostscript) {
                    $im = new \Imagick($pict_path_original . '[0]'); //first page of PDF (default: last page)
                    $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE); // without you only get black frames
                } else {
                    return false; // no ghostscript installed
                }
            } elseif (($imtype == 'MP4' || $imtype == 'MPG' || $imtype == 'FLV' || $imtype == 'MOV' || $imtype == 'AVI') && $is_ffmpeg
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
    private function resize_picture_IM($folder, $file, $maxheight = 1080, $maxwidth = 1920)
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

    // returns false if mime type of file is not listed here
    public function check_media_type($folder, $file)
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
            //here was bug - giving true rather than $mtype
            return $mtype;
        }
        return (false);
    }

    // GD library - returns true if a thumbnail has been created
    private function create_thumbnail_GD($folder, $file, $theight = 120)
    {
        $pict_path_original = $folder . $file;
        $pict_path_thumb = $folder . 'thumb_' . $file . '.jpg';
        $gd_info = gd_info();
        list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
        $gdmime = $this->get_GDmime(); // a.array it gives (map mime->extensions)
        // next we try to take some data from array based on bool given by check_media_type - it wont give uppercase extension needed in further code - UPDATE: fixed at check_media_type()
        $imtype = $gdmime[$this->check_media_type($folder, $file)];
        //old line im leaving it for a while - for deletion when version will be stable
        // $imtype = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
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
    private function resize_picture_GD($folder, $file, $maxheight = 1080, $maxwidth = 1920)
    {
        $success = false;

        $pict_path_original = $folder . $file;
        $picture_original_tmp = $folder . '0_temp' . $file . '.jpg';
        $gd_info = gd_info();
        list($is_gdjpg, $is_gdgif, $is_gdpng) = array($gd_info['JPEG Support'], $gd_info['GIF Read Support'], $gd_info['PNG Support']);
        $gdmime = $this->get_GDmime(); // a.array
        $imtype = $gdmime[$this->check_media_type($folder, $file)];
        // old working code let it  stay until stable version
        // $imtype = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
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

    private function get_GDmime()
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
}
