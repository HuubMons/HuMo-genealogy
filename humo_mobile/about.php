<?php include_once("header.php"); ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>HuMo-gen mobile</title>
	<link rel="stylesheet" href="themes/rene.min.css" />
	<?php
	echo '<link rel="stylesheet" href="jquery_mobile/jquery.mobile.structure-1.2.0.min.css" />';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script>';
	echo '<script type="text/javascript" src="jquery_mobile/jquery.mobile-1.2.0.min.js"></script>';
	?>
</head>

<body>
<div data-role="page" data-theme="b" id="about">       
	<div data-role="header" data-theme="b"> 
	<h1><?php print ucfirst(strtolower(__('INFO'))); ?></h1>  	
	<a href="./"   data-direction="reverse" class="ui-btn-left jqm-home"><?php print __('Home'); ?></a>	
	</div>

	<div data-role="content" data-theme="b">
		<p><?php print __('This is the mobile version of the free and open-source HuMo-gen genealogy program.<br> To learn more about the program use the following button:');?> </p>
		<div class="ui-block-a"><a data-role="button" rel="external" href="http://www.humogen.com">HuMo-gen international</a>
		<a data-role="button" rel="external" href="http://www.humo-gen.com">HuMo-gen english/ dutch</a></div></p>
	</div>
</div>
</body>