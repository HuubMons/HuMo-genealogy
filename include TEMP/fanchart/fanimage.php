<?php
session_start();

/****************************************************************************
 * fanimage.php                                                              *
 * Based on original code from PhpGedView (GNU/GPL licence)                  *
 *                                                                           *
 * Rewritten and adapted for HuMo-genealogy by Yossi Beck  -  October 2009   *
 *                                                                           *
 * This program is free software; you can redistribute it and/or modify      *
 * it under the terms of the GNU General Public License as published by      *
 * the Free Software Foundation; either version 2 of the License, or         *
 * (at your option) any later version.                                       *
 *                                                                           *
 * This program is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             *
 * GNU General Public License for more details.                              *
 ****************************************************************************/

if (!isset($_SESSION['image_data'])) {
    // TODO check this code. When is it used?
    defined('_JEXEC') or die('Restricted access');
    if (file_exists("include/fanchart/tmpimg.png")) {
        $im = imagecreatefrompng("include/fanchart/tmpimg.png");

        $document = &JFactory::getDocument();
        $document->setMimeEncoding('image/png');

        imagepng($im);
        imagedestroy($im);   // delete image resource
        unlink("include/fanchart/tmpimg.png");  // delete temporary image file
    }
} else {
    // read image_data from SESSION variable
    if (isset($_SESSION['image_data'])) {
        $image_data = $_SESSION['image_data'];
        $image_data = @unserialize($image_data);
        unset($_SESSION['image_data']);
    }

    if (!empty($image_data)) {
        // send data to browser
        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        header('Pragma: no-cache');
        echo $image_data;
    }
}
