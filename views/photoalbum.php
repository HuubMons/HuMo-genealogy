<?php
// *** Check user privileges ***
if ($user['group_pictures'] != 'j' || $user['group_photobook'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

include_once(__DIR__ . "/../admin/include/media_inc.php");

$showMedia = new ShowMedia;

// *** Show categories ***
if ($photoalbum['show_categories']) {
    $photoalbum['category_enabled']['none'] = true; // *** Always show main category ***
?>
    <ul class="nav nav-tabs mt-1">
        <?php
        foreach ($photoalbum['category'] as $category) {
            if (isset($photoalbum['category_enabled'][$category]) && $photoalbum['category_enabled'][$category]) {

                $path = 'index.php?page=photoalbum?tree_id=' . $tree_id . '&amp;select_category=' . $category;
                if ($humo_option["url_rewrite"] == "j") {
                    $path = 'photoalbum/' . $tree_id . '?select_category=' . $category;
                }
        ?>
                <li class="nav-item me-1">
                    <a class="nav-link genealogy_nav-link <?php if ($category == $photoalbum['chosen_tab']) echo 'active'; ?>" href="<?= $path; ?>"><?= $photoalbum['category_name'][$category]; ?></a>
                </li>
        <?php
            }
        }
        ?>
    </ul>

<?php
}

$tree_pict_path = $dataDb->tree_pict_path;
if (substr($tree_pict_path, 0, 1) === '|') {
    $tree_pict_path = 'media/';
}

// *** Needed for pagination ***
$data["previous_link"] = $photoalbum["previous_link"];
$data["previous_status"] = $photoalbum["previous_status"];
$data["next_link"] = $photoalbum["next_link"];
$data["next_status"] = $photoalbum["next_status"];
$data["page_nr"] = $photoalbum["page_nr"];
$data["page_link"] = $photoalbum["page_link"];
$data["page_status"] = $photoalbum["page_status"];


// TODO: refactor. Also added in model script.
$item = 0;
if (isset($_GET['item'])) {
    $item = $_GET['item'];
}
$albumpath = $link_cls->get_link($uri_path, 'photoalbum', $tree_id, true);


//$menu_path_photoalbum = $link_cls->get_link($uri_path, 'photoalbum',$tree_id);
$path = 'index.php?page=photoalbum?tree_id=' . $tree_id;
if ($photoalbum['show_categories'] === true) {
    $path .= '&amp;select_category=' . $photoalbum['chosen_tab'];
}

if ($humo_option["url_rewrite"] == "j") {
    $path = 'photoalbum/' . $tree_id;
    if ($photoalbum['show_categories'] === true) {
        $path .= '?select_category=' . $photoalbum['chosen_tab'];
    }
}
?>

<div <?= $photoalbum['show_categories'] === true ? 'style="float: left; background-color:white; height:auto; width:98%;padding:5px;"' : ''; ?>>
    <form method="post" action="<?= $path; ?>" style="display:inline">
        <div class="row mb-2">
            <div class="col-3"></div>

            <div class="col-auto">
                <label for="show-pictures" class="col-form-label"><?= __('Photo\'s per page'); ?></label>
            </div>
            <div class="col-auto">
                <select name="show_pictures" id="show_pictures" onChange="window.location=this.value" class="form-select form-select-sm">
                    <?php for ($i = 4; $i <= 60; $i++) { ?>
                        <option value="<?= $albumpath; ?>show_pictures=<?= $i; ?>&amp;start=0&amp;item=0&amp;select_category=<?= $photoalbum['chosen_tab']; ?>" <?= $i == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>
                            <?= $i; ?>
                        </option>
                    <?php } ?>

                    <option value="<?= $albumpath; ?>show_pictures=100&amp;start=0&amp;item=0&amp;select_category=<?= $photoalbum['chosen_tab']; ?>" <?= 100 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>100</option>
                    <option value="<?= $albumpath; ?>show_pictures=200&amp;start=0&amp;item=0&amp;select_category=<?= $photoalbum['chosen_tab']; ?>" <?= 200 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>200</option>
                    <option value="<?= $albumpath; ?>show_pictures=400&amp;start=0&amp;item=0&amp;select_category=<?= $photoalbum['chosen_tab']; ?>" <?= 400 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>400</option>
                    <option value="<?= $albumpath; ?>show_pictures=800&amp;start=0&amp;item=0&amp;select_category=<?= $photoalbum['chosen_tab']; ?>" <?= 800 == $photoalbum['show_pictures'] ? ' selected' : ''; ?>>800</option>
                </select>
            </div>

            <!-- Search by photo name -->
            <div class="col-auto">
                <input type="text" name="search_media" value="<?= safe_text_show($photoalbum['search_media']); ?>" size="20" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </form>

    <div style="padding:5px" class="center">
        <?php
        if (isset($data["page_nr"])) {
            include __DIR__ . '/partial/pagination.php';
        }
        ?>
    </div>

    <?php
    // *** Show photos ***
    $found_privacy_items = false;
    for ($picture_nr = $item; $picture_nr < ($item + $photoalbum['show_pictures']); $picture_nr++) {
        if (isset($photoalbum['media_files'][$picture_nr]) && $photoalbum['media_files'][$picture_nr]) {
            $filename = $photoalbum['media_files'][$picture_nr];
            $picture_text = '';    // Text with link to person
            $picture_text2 = '';    // Text without link to person

            $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
                AND event_connect_kind='person' AND LEFT(event_kind,7)='picture' AND LOWER(event_event)='" . safe_text_db(strtolower($filename)) . "'";
            $afbqry = $dbh->query($sql);
            if (!$afbqry->rowCount()) {
                $picture_text = substr($filename, 0, -4);
            }
            while ($afbDb = $afbqry->fetch(PDO::FETCH_OBJ)) {
                $person_cls = new PersonCls;
                $personDb = $db_functions->get_person($afbDb->event_connect_id);
                $name = $person_cls->person_name($personDb);
                $privacy = $person_cls->set_privacy($personDb);
                if (!$privacy) {
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                    $picture_text .= '<a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                    $picture_text2 .= $name["standard_name"];
                } else {
                    $found_privacy_items = true;
                }

                $date_place = date_place($afbDb->event_date, $afbDb->event_place);
                if ($afbDb->event_text || $date_place) {
                    if ($date_place) {
                        $picture_text .= $date_place . ' ';
                    }
                    $picture_text .= $afbDb->event_text . '<br>';

                    $picture_text2 .= '<br>';
                    if ($date_place) {
                        $picture_text2 .= $date_place . ' ';
                    }
                    $picture_text2 .= $afbDb->event_text;
                }
            }

            // *** Show texts from connected objects (where object is saved in seperate table): Family Tree Maker GEDCOM file ***
            $picture_qry = $dbh->query("SELECT * FROM humo_events
                WHERE event_tree_id='" . $tree_id . "' AND event_kind='object' AND LOWER(event_event)='" . strtolower($filename) . "'");
            while ($pictureDb = $picture_qry->fetch(PDO::FETCH_OBJ)) {
                $connect_qry = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
                    AND connect_sub_kind='pers_object' AND connect_source_id='" . $pictureDb->event_gedcomnr . "'");
                while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                    $person_cls = new PersonCls;
                    @$personDb = $db_functions->get_person($connectDb->connect_connect_id);
                    $name = $person_cls->person_name($personDb);
                    $privacy = $person_cls->set_privacy($personDb);
                    if (!$privacy) {
                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                        if ($picture_text !== '' && $picture_text !== '0') {
                            $picture_text .= '<br>';
                        }
                        $picture_text .= '<a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                        $picture_text2 .= $name["standard_name"];
                    }

                    $date_place = date_place($pictureDb->event_date, $pictureDb->event_place);
                    if ($pictureDb->event_text || $date_place) {
                        if ($date_place) {
                            $picture_text .= $date_place . ' ';
                        }
                        $picture_text .= $pictureDb->event_text . '<br>';

                        $picture_text2 .= '<br>';
                        if ($date_place) {
                            $picture_text2 .= $date_place . ' ';
                        }
                        $picture_text2 .= $pictureDb->event_text;
                    }
                }
            }
            $tmp_dir = $tree_pict_path;
            $picture = $showMedia->print_thumbnail($tree_pict_path, $filename, 175, 120);
            if (array_key_exists(substr($filename, 0, 3), $showMedia->get_pcat_dirs())) {
                $tmp_dir .= substr($filename, 0, 2) . '/';
            }
            if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'tif'))) {
    ?>
                <div class="photobook">
                    <!-- Show photo using the lightbox: GLightbox effect -->
                    <?php $href_path = give_media_path($tmp_dir, $filename); ?>
                    <a href="<?= $href_path ?>" class="glightbox3" data-gallery="gallery1" data-glightbox="description: .custom-desc<?= $picture_nr; ?>">
                        <!-- Need a class for multiple lines and HTML code in a text -->
                        <div class="glightbox-desc custom-desc<?= $picture_nr; ?>"><?= $picture_text2; ?></div>
                        <?= $picture; ?>
                    </a>
                    <div class="photobooktext"><?= $picture_text; ?></div>
                </div>
            <?php } else { ?>
                <div class="photobook">
                    <a href="<?= $tmp_dir . $filename; ?>" target="_blank"><?= $picture; ?></a>
                    <div class="photobooktext"><?= $picture_text; ?></div>
                </div>
    <?php
            }
        }
    }
    ?>

    <?php if ($found_privacy_items) { ?>
        <div style="float: left; background-color:white; height:auto; width:98%;padding:5px;"><br>
            <?= __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***'); ?><br><br>
        </div>
    <?php } ?>


</div>
<br clear="all"><br>

<?php
if (isset($data["page_nr"])) {
    include __DIR__ . '/partial/pagination.php';
}
?>