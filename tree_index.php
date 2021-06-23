<?php

// *** It's very difficult to combine pages index.php and tree_index.php because of url_rewrite and CMS combined... ***

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// ***********************
// ** Main index class ***
// ***********************
include_once(CMS_ROOTPATH."include/mainindex_cls.php");
$mainindex = new mainindex_cls();

echo $mainindex->show_tree_index();

// *** Show HuMo-genealogy footer ***
echo $mainindex->show_footer();

include_once(CMS_ROOTPATH."footer.php");
?>