<?php

// *** It's very difficult to combine pages index.php and tree_index.php because of url_rewrite and CMS combined... ***

include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ . '/menu.php';

// ***********************
// ** Main index class ***
// ***********************
include_once __DIR__ . '/include/mainindex_cls.php';
$mainindex = new mainindex_cls($dbh);

echo $mainindex->show_tree_index();

include_once __DIR__ . '/footer.php';
