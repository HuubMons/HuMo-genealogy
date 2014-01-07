<?php
include_once("header.php");
?>

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
	<div data-role="page" data-theme="b">
		<div data-role="header" data-theme="b">
			<h1><?php print __('Login');?> </h1>
			<a href="./"   data-direction="reverse" class="ui-btn-left jqm-home"><?php print __('Home');?></a>
		</div>
		<div data-role="content" data-theme="b"> 

<?php
	// *** No valid user found ***
	if ($fault==true){
		echo '<div class="center"><font color="red"><b>'.__('No valid username or password.').'</b></font></div>';
	}

	print '<form method="post" action="mob_login.php" data-ajax="false">';
	print '<div data-role="fieldcontain">';
	print '<label>'.__('Username').'</label>';
	print '<input class="fonts" name="username" type="text" >';
	print '<label>'.__('Password').'</label>';
		print '<input class="fonts" name="password" type="password">';
	print '</div>';
	print ' <input type="submit" value="'.__('Login').'" id="submit" data-theme="b">';
	print "</form>";
?>
		</div>
		<div data-role="footer" data-theme="b">
			<h4>HuMo-gen GPL Licence</h4>
		</div>
	</div>
</body>
</html>