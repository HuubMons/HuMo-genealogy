<?php
// *** To make HuMo-gen work, fill these lines properly! ***
// *** Om HuMo-gen werkend te krijgen onderstaande regels GOED invullen! ***
define("DATABASE_HOST",     "localhost");
define("DATABASE_USERNAME", "root");
define("DATABASE_PASSWORD", "usbw");
define("DATABASE_NAME",     "humo-gen");

// *** DON'T CHANGE ANYTHING BELOW THIS LINE! ***
// *** HIERONDER NIETS WIJZIGEN! ***

// *** Check if PDO driver is available ***
if (!defined('PDO::ATTR_DRIVER_NAME')) {
	echo '<html>
	<header><title>ERROR</title><body>
	<h2>ERROR: The PDO driver is unavailable!</h2>
	Possible solutions:<br><br>
	1) Update PHP to 5.0 or even better: 5.1 (or newer)<br>
	2) Check if the PDO driver is enabled.<br>
	3) If the PDO driver is enabled but you receive this message from the Admin screen, try adding these three lines to the admin/php.ini file:<br>
	extension=pdo.so<br>
	extension=pdo_sqlite.so<br>
	extension=pdo_mysql.so<br>

	<p>If it\'s not possible to use PDO, you have to downgrade to HuMo-gen 4.9.4!<br>
	1) Download HuMo-gen 4.9.4 at: <a href="https://sourceforge.net/projects/humo-gen/files">Sourceforge</a><br>
	2) Follow <a href="http://www.humo-gen.com/genwiki/index.php?title=Installation">HuMo-gen installation</a> instructions.
	</body></html>
	';
	exit();
}

// *** Open database using PDO **
//$conn = 'mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME;
$conn = 'mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME.';charset=utf8';
try {
	//$dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD);
	@$database_check=1; 
} catch (PDOException $e) {
	unset($database_check);
	//echo $e->getMessage() . "<br/>";
	if(!isset($ADMIN)) {
		echo '<br><font color=red><b>
		Database is not yet installed! Possible problems:<br>
		- Login file not yet configured.<br>
		- Database not yet installed.<br>
		Go to the <a href="admin">administration area</a> to solve this problem.
		<p>De database is nog niet bereikbaar! Mogelijke oorzaken:<br>
		- Het login bestand is niet goed ingevuld.<br>
		- De database is nog niet gemaakt.<br>
		Ga naar het <a href="admin">administratie scherm</a> om dit probleem op te lossen.
		</b></font>';
		exit();
	}
}
?>