<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// ***********************************************************************************************
// ** Main index class ***
// ***********************************************************************************************
include_once(CMS_ROOTPATH."include/mainindex_cls.php");
$mainindex = new mainindex_cls();

echo $mainindex->show_tree_index();

// *** This line can be found in: index.php and tree_index.php ***
echo '<br><div class="humo_version">';
printf(__('This database is made by %s, a freeware genealogical  program'), '<a href="http://www.humo-gen.com">HuMo-gen</a>');
echo " (".$humo_option["version"].")</div>";

include_once(CMS_ROOTPATH."footer.php");
?>