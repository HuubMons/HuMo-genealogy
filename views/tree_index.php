<?php

/**
 * Show homepage for family trees.
 * Huub: rebuild page in august. 2023.
 */



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../app/model/tree_index.php";
$mainindex = new Mainindex_cls($dbh);
$item_array = $mainindex->show_tree_index();



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


$middle = "col-sm-6";
// TODO: if there is no left and right, then center column 100%?
if (!$left or !$right) $middle = "col-sm-9";
?>

<div class="row m-lg-1 py-3 genealogy_row">
    <!--  Left column -->
    <?php if ($left) { ?>
        <div class="col-sm-3">
            <div class="row">
                <?php for ($i = 0; $i < count($item_array); $i++) { ?>
                    <?php if ($item_array[$i]['position'] == 'left') { ?>
                        <div class="col-12">
                            <!-- <div class="mb-3 bg-light p-2 border"> -->
                            <div class="mb-3 p-2 border genealogy_box">
                                <?php if ($item_array[$i]['header']) echo '<h5 class="text-center m-2"><strong>' . $item_array[$i]['header'] . '</strong></h5>'; ?>
                                <?= $item_array[$i]['item']; ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- Center column -->
    <!-- Remark: text in this column is centered (class text-center) -->
    <div class="<?= $middle; ?>">
        <div class="row">
            <?php for ($i = 0; $i < count($item_array); $i++) { ?>
                <?php if ($item_array[$i]['position'] == 'center') { ?>
                    <div class="col-12">
                        <!-- <div class="mb-3 bg-light p-2 border  text-center"> -->
                        <div class="mb-3 p-2 border text-center genealogy_box">
                            <?php if ($item_array[$i]['header']) echo '<h5 class="text-center m-2"><strong>' . $item_array[$i]['header'] . '</strong></h5>'; ?>
                            <?= $item_array[$i]['item']; ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <!-- Right column -->
    <?php if ($right) { ?>
        <div class="col-sm-3">
            <div class="row">
                <?php for ($i = 0; $i < count($item_array); $i++) { ?>
                    <?php if ($item_array[$i]['position'] == 'right') { ?>
                        <div class="col-12">
                            <!-- <div class="mb-3 bg-light p-2 border"> -->
                            <div class="mb-3 p-2 border genealogy_box">
                                <?php if ($item_array[$i]['header']) echo '<h5 class="text-center m-2"><strong>' . $item_array[$i]['header'] . '</strong></h5>'; ?>
                                <?= $item_array[$i]['item']; ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>