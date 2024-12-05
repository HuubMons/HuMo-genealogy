<?php
// this function gives us the media path in two ways. 1. Old way the humogen gave files - as static paths. 2. Second way - giving adress that is parsed by another function to give us dynamic link for media. Purpose - media files privacy and security.
function give_media_path($media_dir, $media_filename)
{
    // untill full code we are simulating options manually changing this bool. If it's false u cant get media old way waith static link. Needs to be replaced by code checking options in db
    // config - change true or false to test. True is old way
    $media_privacy_mode_off = true;

    //in this part we are simulating code that should be executed once while changing options. Need to port this to options code when we implement. Final code should also validate if .htaccess was modified and only then change option
    //{
    // path to dir for .htaccess
    $directoryPath = realpath(__DIR__ . '/../media/');

    if (!$media_privacy_mode_off) {
        // .htaccess content with directive to not allow to get file by static link - file will be possible to get only by query url
        $htaccessContent = <<<HTACCESS
        <FilesMatch ".*">
          Require all denied
        </FilesMatch>
        HTACCESS;
    } else {
        $htaccessContent = '';
        /*
        $htaccessContent = <<<HTACCESS
        <FilesMatch ".*">
          #Require all denied
        </FilesMatch>
        HTACCESS;
        */
    }
    // full path with filename
    $filePath = $directoryPath . '/.htaccess';

    if ($htaccessContent && file_put_contents($filePath, $htaccessContent) !== false) {
        // echo "File modified in $directoryPath";
    } else {
        // echo "Check permissions. I couldn't modify .htaccess in $directoryPath";
    }
    //}


    // first option is for old media path
    // for testing purpouse there is middle slash which gives in effect two slashes in picture adress which works the same as one slash but allows to test if a file goes through this function allowing to identify all the places that are done with this function confirming it's working. Finally delete that middle slash
    if ($media_privacy_mode_off) {
        $final_media_path = $media_dir . '/' . $media_filename;
    } else {
        // this second option gives us dynamic media link based on query strings which are parsed throug function give_media_file() which is put at beggining of layout.php

        $final_media_path = "index.php?page=serve_file&media_dir=" . $media_dir . "&media_filename=" . $media_filename;
    }
    return $final_media_path;
}
