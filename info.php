<?php
include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH . "menu.php");
echo '<div style="direction:ltr;margin-left:10px">';
echo '<h2>' . __('Information') . '</h2>';

echo '<p>';
printf(__('%s is a free, open-source and multilingual server-side program that makes it very easy to publish your genealogical data on the internet as a dynamic and searchable family tree website.'), 'HuMo-genealogy');
echo '</p>';

echo '<p>';
printf(__('There are 2 official %s websites:'), 'HuMo-genealogy');
echo '<br>';
?>
1) <a href="https://humo-gen.com" target="_blank">English/Dutch HuMo-genealogy website by Huub Mons</a><br>
2) <a href="https://humogen.com" target="_blank">International HuMo-genealogy website by Yossi Beck</a></p>

<p><a href="https://www.sourceforge.net/projects/humo-gen" target="_blank">HuMo-genealogy download</a></p>

<p><a href="https://www.sourceforge.net/projects/humo-gen/files/HuMo-gen_Manual/" target="_blank">HuMo-genealogy download PDF manual</a></p>

<p><a href="https://humo-gen.com/genforum" target="_blank">HuMo-genealogy forum</a></p>
</div>
<?php
include_once(CMS_ROOTPATH . "footer.php");
?>