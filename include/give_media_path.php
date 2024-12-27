<?php
// This function gives us the media path in two ways.
// 1. Old way the humogen gave files - as static paths.
// 2. Second way - giving adress that is parsed by another function to give us dynamic link for media. Purpose - media files privacy and security.
function give_media_path($media_dir, $media_filename)
{
    global $humo_option;

    // Final code should validate if .htaccess was modified and only then change option
    // path to dir for .htaccess
    // TODO: also check other optional image paths?

    // first option is for old media path
    if ($humo_option["media_privacy_mode"] == 'n') {
        $final_media_path = $media_dir . $media_filename;
    } else {
        // this second option gives us dynamic media link based on query strings which are parsed throug function give_media_file().

        // TODO does this work if url_rewrite is enabled? Should be something like this if url_rewrite is enabled:
        // serve_file?media_dir=" . $media_dir . "&media_filename=" . $media_filename;
        // ill check it after i reinspect code for normal usecases (without url_rewrite)
        $final_media_path = "index.php?page=serve_file&media_dir=" . $media_dir . "&media_filename=" . $media_filename;
    }
    return $final_media_path;
}
