<?php
include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

echo '<h2>';
printf(__('%s cookie information'),'HuMo-genealogy');
echo '</h2>';

printf(__('%s uses a few cookies so that settings you made can be stored on your computer for your future use.<br> The cookies that are created by %s are not used for any other purpose, are not transferred to others and the information they contain is used by you alone. <p>%s cookies are used for these purposes:'),'HuMo-genealogy','HuMo-genealogy','HuMo-genealogy');
echo '<br>';

printf(__('<ul><li>You chose a theme (skin) that is different from the default. The theme you chose will be used next time you visit the %s site.</li><li>You used the star to mark a family as "favourite". This family will appear on your favourite list on future visits as well.</li><li>In the photo album you set the number of photos to be displayed at a different number than the default. This number will be used next time you visit.</li></ul></p><p>If you do not want %s to create these cookies, you can just refrain from changing the default values for the above features.</p>'),'HuMo-genealogy','HuMo-genealogy');

include_once(CMS_ROOTPATH."footer.php");
?>