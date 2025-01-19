<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

include_once(__DIR__ . "/../include/media_inc.php");
include_once(__DIR__ . "/../../include/showMedia.php");
$showMedia = new showMedia();
?>

<h1 class="center"><?= __('Select media'); ?></h1>

<?php
$place_item = '';
$form = '';
if (isset($_GET['form'])) {
    $check_array = array("1", "2", "3", "5", "6");
    $selected_form = '';
    if (in_array($_GET['form'], $check_array)) {
        $form = 'form' . $_GET['form'];
        $selected_form = $_GET['form'];
    }

    $place_item = 'text_event';

    // *** Multiple events: add event_id ***
    $event_id = '';
    if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
        $event_id = $_GET['event_id'];
        $place_item .= $_GET['event_id'];
    }
}

echo '
    <script>
    function select_item(item){
        /* EXAMPLE: window.opener.document.form1.pers_birth_place.value=item; */
        window.opener.document.' . $form . '.' . $place_item . '.value=item;
        top.close();
        return false;
    }
    </script>
';

$prefx = '../'; // to get out of the admin map

// *** Get main path for selected family tree ***
$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $tree_id);
$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
$pict_path = $data2Db->tree_pict_path;
if (substr($pict_path, 0, 1) === '|') {
    $pict_path = 'media/';
}
$array_picture_folder[] = $prefx . $pict_path;
//$array_picture_sub_dir[]='';

// *** Extra safety check if folder exists ***
if (file_exists($array_picture_folder[0])) {
    // *** Get all subdirectories ***
    function get_dirs($prefx, $path)
    {
        global $array_picture_folder;
        $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
        $dh = opendir($prefx . $path);
        while (false !== ($filename = readdir($dh))) {
            // *** Only process directories here. So list of media files will be in directory order ***
            if (!in_array($filename, $ignore) && is_dir($prefx . $path . $filename)) {
                $array_picture_folder[] = $prefx . $path . $filename . '/';
                //sub-sub dir: alles in $array_picture_folder zonder $prefx en $path...
                //$array_picture_sub_dir[]=$filename.'/';
                get_dirs($prefx, $path . $filename . '/');
            }
        }
        closedir($dh);
    }
    // *** Get directories ***
    get_dirs($prefx, $pict_path);

    $search_quicksearch = '';
    if (isset($_POST['search_quicksearch'])) {
        $search_quicksearch = safe_text_db($_POST['search_quicksearch']);
    }
?>
    <form method="POST" action="index.php?page=editor_media_select&form=<?= $selected_form . '&event_id=' . $event_id; ?>">
        <input type="text" name="search_quicksearch" placeholder="<?= __('Name'); ?>" value="<?= $search_quicksearch; ?>" size="15">
        <input type="submit" name="submit" value="<?= __('Search'); ?>">
    </form><br>

    <?php
    // *** List of media files ***
    $ignore = array('.', '..', 'cms', 'readme.txt', 'slideshow', 'thumbs');
    $dirname_start = strlen($prefx . $pict_path);

    foreach ($array_picture_folder as $selected_picture_folder) {
        echo '<br style="clear: both">';
        echo '<h3>' . $selected_picture_folder . '</h3>';

        $dh = opendir($selected_picture_folder);
        while (false !== ($filename = readdir($dh))) {
            if (is_dir($selected_picture_folder . $filename)) {
                //
            } elseif (
                !in_array($filename, $ignore) &&
                substr($filename, 0, 6) !== 'thumb_' &&
                substr($filename, 0, 1) !== '.'
            ) {   // skip hidden files (unix style)
                // *** stripos = case-insensitive search ***
                if ($search_quicksearch == '' || $search_quicksearch != '' && stripos($filename, $search_quicksearch) !== false) {
                    $sub_dir = substr($selected_picture_folder, $dirname_start);
                    $list_filename[] = $filename;
                    $list_filename_order[] = strtolower($filename); // *** So ordering is case-insensitive ***
                    // *** Replace ' by &prime; otherwise a place including a ' character can't be selected ***
                    //echo '<a href="" onClick=\'return select_item("'.$sub_dir.str_replace("'","&prime;",$filename).'")\'>'.$sub_dir.$filename.'</a><br>';
                }
            }
        }

        // *** Order language array by name of language (case insensitive!) ***
        if (isset($list_filename)) {
            array_multisort($list_filename_order, $list_filename);
            foreach ($list_filename as $selected_filename) {
    ?>
                <div class="photobook">
                    <?= $showMedia->print_thumbnail($selected_picture_folder, $selected_filename); ?><br>
                    <!-- Replace ' by &prime; otherwise a place including a ' character can't be selected -->
                    <a href="" onClick='return select_item("<?= $sub_dir . str_replace("'", "&prime;", $selected_filename); ?>")'><?= $sub_dir . $selected_filename; ?></a><br>
                </div>
<?php
            }
            unset($list_filename);
            unset($list_filename_order);
        }
    }
}
