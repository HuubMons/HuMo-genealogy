<?php



function database_check($dbh)
{
    $check_tables = false;
	try {
		$check_tables = $dbh->query("SELECT * FROM humo_settings");
	} catch (Exception $e) {
		//
	}

	if ($check_tables) {
		// *** Added may 2020, needed for some user settings in admin section ***
		// *** At this moment there is no separation for front user and admin user... ***
		

		// *** Added in mar. 2023. To prevent double results in search results ***
		//SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
		$result = $dbh->query("SET SESSION sql_mode=(SELECT
			REPLACE(
				REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')
			,'NO_ZERO_IN_DATE',''));");
	}
}

function remove_the_folders($remove_folders)
{
    global $update_dir, $update_files;
    foreach ($remove_folders as $rf) {
        if (is_dir($rf)) {
            // *** Remove these old HuMo-genealogy files, a__ is just some random text (skip items)... ***
            listFolderFiles2($rf, array('a__', 'a__'), 'update_files');
            //echo $update_dir[0].' '.$update_files[0];
            // *** Count down, because files must be removed first before removing directories ***
            if (is_array($update_files)) {
                for ($i = count($update_files) - 1; $i >= 0; $i--) {
                    if (!is_dir($update_dir[$i] . '/' . $update_files[$i])) {
                        unlink($update_dir[$i] . '/' . $update_files[$i]);
                    } else {
                        rmdir($update_dir[$i] . '/' . $update_files[$i]);
                    }
                    //echo $update_dir[$i].'/'.$update_files[$i].'<br>';
                }
            }
            rmdir($rf);
            unset($update_dir, $update_files);
        }
    }
}

function listFolderFiles2($dir, $exclude, $file_array)
{
    global $update_dir, $update_files;
    $ffs = scandir($dir);
    foreach ($ffs as $ff) {
        if (is_array($exclude) and !in_array($ff, $exclude)) {
            if ($ff != '.' && $ff != '..') {
                // *** Skip media files in ../media/, ../media/cms/ etc.
                //if (substr($dir,0,8)=='../media' AND !is_dir($dir.'/'.$ff) AND $ff != 'readme.txt'){
                //	// skip media files
                //}
                //else{
                $update_dir[] = $dir;
                $update_files[] = $ff;
                if (is_dir($dir . '/' . $ff)) listFolderFiles2($dir . '/' . $ff, $exclude, $file_array);
                //}
            }
        }
    }
}
