<?php

/**
 * Show homepage for family trees.
 * Huub: rebuild page in august. 2023.
 */



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/tree_index.php";
$mainindex = new Mainindex_cls($dbh);
$item_array = $mainindex->show_tree_index();
//$family_id = $get_family->getFamilyId();
//$main_person = $get_ancestor->getMainPerson();
//$rom_nr = $get_ancestor->getNumberRoman();
//$number_generation = $get_family->getNumberGeneration();
//$ancestor_header = $get_ancestor->getAncestorHeader('Ancestor chart', $tree_id, $main_person);
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



// *** Show slideshow ***
if ($page != 'tree_index') {
    if (isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
        $mainindex->show_slideshow();
    }
}

// *** Check if there are items in left, center and right columns ***
$left = false;
$center = false;
$right = false;
for ($i = 0; $i < count($item_array); $i++) {
    if ($item_array[$i]['position'] == 'left') {
        $left = true;
    }
    if ($item_array[$i]['position'] == 'center') {
        $center = true;
    }
    if ($item_array[$i]['position'] == 'right') {
        $right = true;
    }
}

$center_id = "mainmenu_center";
if (!$left) $center_id = "mainmenu_center_alt";
// TODO: if there is no right colum also use center_alt?
// TODO: if there is no left and right, then center column 100%?
?>

<!-- Mainmenu Centerbox can be used for an extra box in lay-out -->
<div id="mainmenu_centerbox">
    <!--  Left column -->
    <?php if ($left) { ?>
        <div id="mainmenu_left">
            <?php for ($i = 0; $i < count($item_array); $i++) { ?>
                <?php if ($item_array[$i]['position'] == 'left') { ?>
                    <div class="homepage_box">
                        <?php if ($item_array[$i]['header']) echo '<h3>' . $item_array[$i]['header'] . '</h3>'; ?>
                        <?= $item_array[$i]['item']; ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>

    <!-- Center column -->
    <div id="<?= $center_id; ?>">
        <?php for ($i = 0; $i < count($item_array); $i++) { ?>
            <?php if ($item_array[$i]['position'] == 'center') { ?>
                <div class="homepage_box">
                    <?php if ($item_array[$i]['header']) echo '<h3>' . $item_array[$i]['header'] . '</h3>'; ?>
                    <?= $item_array[$i]['item']; ?>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <!-- Right column -->
    <?php if ($right) { ?>
        <div id="mainmenu_right">
            <?php for ($i = 0; $i < count($item_array); $i++) { ?>
                <?php if ($item_array[$i]['position'] == 'right') { ?>
                    <div class="homepage_box">
                        <?php if ($item_array[$i]['header']) echo '<h3>' . $item_array[$i]['header'] . '</h3>'; ?>
                        <?= $item_array[$i]['item']; ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
</div>