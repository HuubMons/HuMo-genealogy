<?php
include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

echo '<h2>'.__('HuMo-gen cookie information').'</h2>';

	echo __('HuMo-gen uses a few cookies so that settings you made can be stored on your computer for your future use.<br> The cookies that are created by HuMo-gen are not used for any other purpose, are not transferred to others and the information they contain is used by you alone. <p>HuMo-gen cookies are used for these purposes:<br><ul><li>You chose a theme (skin) that is different from the default. The theme you chose will be used next time you visit the HuMo-gen site.</li><li>You used the star to mark a family as "favorite". This family will appear on your favorite list on future visits as well.</li><li>In the photo album you set the number of photos to be displayed at a different number than the default. This number will be used next time you visit.</li><li>You changed the font size with the A+A- buttons. HuMo-gen will be displayed with this font size next time you visit.</li></ul></p><p>If you do not want HuMo-gen to create these cookies, you can just refrain from changing the default values for the above features.</p>');

include_once(CMS_ROOTPATH."footer.php");
?>