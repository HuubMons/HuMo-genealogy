<?php

/**
 * Select media in editor.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$showMedia = new Genealogy\Include\ShowMedia();

$place_item = '';
$form = '';
$selected_form = '';
$event_id = '';
if (isset($_GET['form']) && is_string($_GET['form'])) {
    $check_array = array("1", "2", "3", "5", "6");
    if (in_array($_GET['form'], $check_array, true)) {
        $form = 'form' . $_GET['form'];
        $selected_form = $_GET['form'];
    }

    $place_item = 'text_event';

    // *** Multiple events: add event_id ***
    $event_id = '';
    if (isset($_GET['event_id']) && is_scalar($_GET['event_id']) && is_numeric($_GET['event_id'])) {
        $event_id = (string) (int) $_GET['event_id'];
        $place_item .= $event_id;
    }
}

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
?>

<h1 class="center"><?= __('Select media'); ?></h1>

<?php
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
        if (is_string($_POST['search_quicksearch'])) {
            $search_quicksearch = $safeTextDb->safe_text_db($_POST['search_quicksearch']);
        }
    }
?>
    <form method="POST" action="<?= $HtmlEscaper->escHtml('index.php?page=editor_media_select&form=' . rawurlencode($selected_form) . '&event_id=' . rawurlencode($event_id)); ?>">
        <div class="row mb-2">
            <div class="col-md-4">
                <input type="text" name="search_quicksearch" placeholder="<?= $HtmlEscaper->escHtml(__('Name')); ?>" value="<?= $HtmlEscaper->escHtml((string) $search_quicksearch); ?>" size="15" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <input type="submit" name="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
            </div>
    </form><br>

    <?php
    // *** List of media files ***
    $ignore = array('.', '..', 'cms', 'readme.txt', 'slideshow', 'thumbs');
    $dirname_start = strlen($prefx . $pict_path);

    foreach ($array_picture_folder as $selected_picture_folder) {
        ?>
        <br style="clear: both">
        <h3><?= $HtmlEscaper->escHtml((string) $selected_picture_folder); ?></h3>
        <div class="container-fluid mt-2">
            <div class="row g-3">
                <?php
                $dh = opendir($selected_picture_folder);
                while (false !== ($filename = readdir($dh))) {
                    if (is_dir($selected_picture_folder . $filename)) {
                        //
                    } elseif (
                        !in_array($filename, $ignore) &&
                        substr($filename, 0, 6) !== 'thumb_' &&
                        substr($filename, 0, 1) !== '.'
                    ) {
                        // skip hidden files (unix style)
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
                        //$picture = $showMedia->print_thumbnail($selected_picture_folder, $selected_filename);
                        $picture = $showMedia->print_thumbnail($selected_picture_folder, $selected_filename, 175, 120, 'BOOTSTRAP_CARD');

                ?>
                        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
                            <div class="card h-100 shadow-sm">
                                <?= $picture; ?><br>
                                <!-- Replace ' by &prime; otherwise a place including a ' character can't be selected -->
                                <a href="#" class="media-select-link" data-media-path="<?= $HtmlEscaper->escHtml((string) $sub_dir . $selected_filename); ?>"><?= $HtmlEscaper->escHtml((string) $sub_dir . $selected_filename); ?></a><br>
                            </div>
                        </div>
                <?php
                    }
                    unset($list_filename);
                    unset($list_filename_order);
                }
                ?>
            </div>
        </div>
<?php
    }
}
?>

<script>
    const targetFormName = <?= json_encode($form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const targetFieldName = <?= json_encode($place_item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function select_item(item) {
        if (!window.opener || !window.opener.document.forms[targetFormName]) {
            return false;
        }

        const targetField = window.opener.document.forms[targetFormName].elements.namedItem(targetFieldName);
        if (!targetField) {
            return false;
        }

        targetField.value = item;
        top.close();
        return false;
    }

    document.querySelectorAll('.media-select-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            select_item(link.dataset.mediaPath);
        });
    });
</script>