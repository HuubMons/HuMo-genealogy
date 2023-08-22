<?php

// *** This file is used if a family tree is selected in homepage ***
// *** It's difficult to combine pages index.php and tree_index.php because of url_rewrite and CMS combined... ***

// ***********************
// ** Main index class ***
// ***********************
include_once(CMS_ROOTPATH . "include/mainindex_cls.php");
$mainindex = new mainindex_cls();

echo $mainindex->show_tree_index();

// *** Show HuMo-genealogy footer ***
echo $mainindex->show_footer();
