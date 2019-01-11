<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Administration').'</h1>';

// variable $path_tmp for all urls in this file
if (CMS_SPECIFIC=='Joomla'){
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin';
}
else{
	$path_tmp="index.php";
}

$result_message='';
if (isset($_POST['save_settings_database'])){
	$result_message='<b>'.__('Database connection status:').'</b><br>';

	// *** Check MySQL connection ***
	try {
		//$conn = 'mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME.';charset=utf8';
		$conn = 'mysql:host='.$_POST['db_host'];
		//$db_check = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
		$db_check = new PDO($conn,$_POST['db_username'],$_POST['db_password']);

		$result_message.=__('MySQL connection: OK!').'<br>';
		// *** If needed immediately install a new database ***
		if (isset($_POST['install_database'])){
			$install_qry="CREATE DATABASE IF NOT EXISTS `".$_POST['db_name']."`";
			$db_check->query($install_qry);
		}
	} catch (PDOException $e) { 
		$result_message.='<b>*** '.__('There is no MySQL connection: please check host/ username and password.').' ***</b><br>';
	}

	// *** Check if database exists ***
	try {
		$conn = 'mysql:host='.$_POST['db_host'].';dbname='.$_POST['db_name'];
		//$temp_dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD);
		$temp_dbh = new PDO($conn,$_POST['db_username'],$_POST['db_password']);	
		if($temp_dbh!==false) { $database_check=1; $result_message.=__('Database connection: OK!').'<br>'; }
		$temp_dbh=null;
	} catch (PDOException $e) { 
		unset($database_check);
		$result_message.='<b>*** '.__('No database found! Check MySQL connection and database name').' ***</b><br>';
	}

	// *** Check if db_login.php is writable, and change database lines in db_login.php file ***
	$login_file=CMS_ROOTPATH."include/db_login.php";
	if (!is_writable($login_file)) {
		$result_message='<b> *** '.__('The configuration file is not writable! Please change the include/db_login.php file manually.').' ***</b>';
	}
	else{
		// *** Read file ***
		$handle = fopen($login_file, "r");
		while (!feof($handle)) {
			$buffer[] = fgets($handle, 4096);
		}

		// *** Write file ***
		$check_config=false;
		$bestand_config = fopen($login_file,"w");
		for ($i=0; $i<=(count($buffer)-1); $i++) {

			// *** Use ' character to prevent problems with $ character in password ***
			//define("DATABASE_HOST",     'localhost');
			//define("DATABASE_USERNAME", 'root');
			//define("DATABASE_PASSWORD", 'usbw');
			//define("DATABASE_NAME",     'humo-gen');

			if (substr($buffer[$i],0,21)=='define("DATABASE_HOST'){
				$buffer[$i]='define("DATABASE_HOST",     '."'".$_POST['db_host']."');\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,25)=='define("DATABASE_USERNAME'){
				$buffer[$i]='define("DATABASE_USERNAME", '."'".$_POST['db_username']."');\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,25)=='define("DATABASE_PASSWORD'){
				$buffer[$i]='define("DATABASE_PASSWORD", '."'".$_POST['db_password']."');\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,21)=='define("DATABASE_NAME'){
				$buffer[$i]='define("DATABASE_NAME",     '."'".$_POST['db_name']."');\n";
				$check_config=true;
			}
	
			fwrite($bestand_config,$buffer[$i]);
		}
		fclose($bestand_config);
		if ($check_config==false){
			$result_message='<b> *** '.__('There is a problem in the db_login file, maybe an old db_login file is used.').' ***</b>';
		}
	}
}


// *************************************************************************
// *** Show HuMo-gen status, use scroll bar to show lots of family trees ***
// *************************************************************************

echo '<div style="height:400px; width:850px; overflow-y: auto; margin-left:auto; margin-right:auto;">';
echo '<table class="humo" width="100%">';
	echo '<tr class="table_header"><th colspan="2">'.__('HuMo-gen status').'</th></tr>';

	// *** HuMo-gen version ***
	if (isset($humo_option["version"]))
		echo '<tr><td class="line_item">'.__('HuMo-gen Version').'</td><td class="line_ok">'.$humo_option["version"].'</td></tr>';

	// *** PHP Version ***
	$version = explode('.', phpversion() );
	if ($version[0] > 4){
		echo '<tr><td class="line_item">'.__('PHP Version').'</td><td class="line_ok">'.phpversion().'</td></tr>';
	}
	else{
		echo '<tr><td class="line_item">'.__('PHP Version').'</td><td class="line_nok">'.phpversion().' '.__('It is recommended to update PHP!').'</td></tr>';
	}

	// *** MySQL Version ***
	if(isset($dbh)) {  
		// in PDO and MySQLi you can't get MySQL version number until connection is made 
		// so on very first screens before saving connection parameters we do without. 
		// as of Jan 2014 mysql_get_server_info still works but once deprecated will give errors, so better so without.
		$mysqlversion = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);  
		$version = explode('.',$mysqlversion);
		if ($version[0] > 4){
			echo '<tr><td class="line_item">'.__('MySQL Version').'</td><td class="line_ok">'.$mysqlversion.'</td></tr>';
		}
		else{
			echo '<tr><td class="line_item">'.__('MySQL Version').'</td><td class="line_nok">'.$mysqlversion.' '.__('It is recommended to update MySQL!').'</td></tr>';
		}
	}

// *** Check if database and tables are ok ***
$install_status=true;

// *** Check database, if needed install local database ***
echo '<tr><td class="line_item">'.__('Database').'</td>';
if (@$database_check){
	echo '<td class="line_ok">'.__('OK');
}
else{
	echo '<td class="line_nok">';
	echo __('<b>There is no database connection! To connect the MySQL database to HuMo-gen, fill in these settings:</b>');

	$install_status=false;

	// *** Get database settings ***
	echo ' <form method="post" action="'.$path_tmp.'" style="display : inline;">';

	echo '<table class="humo" border="1" cellspacing="0" bgcolor="#DDFD9B">';

	echo '<tr><th>'.__('Database setting').'</th><th>'.__('Database value').'</th><th>'.__('Example website provider').'</th><th>'.__('Example for XAMPP').'</th></tr>';

	$db_host='localhost'; if (isset($_POST['db_host'])){ $db_host=$_POST['db_host']; }
	echo '<tr><td>'.__('Database host').'</td>';
	echo '<td><input type="text" name="db_host" value="'.$db_host.'" size="15"></td>';
	echo '<td>localhost</td><td>localhost</td>';
	echo '</tr>';

	$db_username='root'; if (isset($_POST['db_username'])){ $db_username=$_POST['db_username']; }
	echo '<tr><td>'.__('Database username').'</td>';
	echo '<td><input type="text" name="db_username" value="'.$db_username.'" size="15"></td>';
	echo '<td>database_username</td><td>root</td>';
	echo '</tr>';

	$db_password=''; if (isset($_POST['db_password'])){ $db_password=$_POST['db_password']; }
	echo '<tr><td>'.__('Database password').'</td>';
	echo '<td><input type="text" name="db_password" value="'.$db_password.'" size="15"></td>';
	echo '<td>database_password</td><td><br></td>';
	echo '</tr>';

	$db_name='humo-gen'; if (isset($_POST['db_name'])){ $db_name=$_POST['db_name']; }
	echo '<tr><td>'.__('Database name').'</td>';
	echo '<td><input type="text" name="db_name" value="'.$db_name.'" size="15"></td>';
	echo '<td>database_name</td><td>humo-gen</td>';
	echo '</tr>';

	$install_database=''; if (isset($_POST["install_database"])){ $install_database=' checked'; }
	echo '<tr><td>'.__('At a local PC also install database').'</td><td><input type="checkbox" name="install_database" '.$install_database.'> '.__('YES, also install database').'</td>';
	echo '<td>'.__('NO').'</td><td>'.__('YES').'</td>';
	echo '</tr>';

	echo '<tr><td>'.__('Save settings and connect to database').'</td>';
	echo '<td><input type="Submit" name="save_settings_database" value="'.__('SAVE').'"></td>';
	echo '<td><br></td><td><br></td>';
	echo '</tr>';

	echo '</table>';
	
	echo '</form>';

	echo __('Sometimes it\'s needed to add these lines to a /php.ini and admin/php.ini files to activate the PDO driver:').'<br>';
	echo 'extension=pdo.so<br>
	extension=pdo_sqlite.so<br>
	extension=pdo_mysql.so<br>';
}

if (isset($_POST['install_database'])){
	if (!$database_check){
		echo '<p><b>'.__('The database has NOT been created!').'</b>';
		$install_status=false;
	}
}
echo '</td></tr>';

// *** Show button to continue installation (otherwise the tables are not recognised) ***
if (isset($_POST['save_settings_database'])){

	// *** Show result messages after installing settings of db_login.php ***
	echo '<tr><td><br></td><td>'.$result_message.'</td></tr>';

	$install_status=false;
	echo '<tr><td><br></td><td><form method="post" action="'.$path_tmp.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="admin">';
		echo '<input type="Submit" name="submit" value="'.__('Continue installation').'">';
	echo '</form></td></tr>';
}

// *** Only show table status if database is checked ***
if ($install_status==true){
	// *** Check database tables ***
	if (isset($check_tables) AND $check_tables){
		echo '<tr><td class="line_item">'.__('Database tables').'</td><td class="line_ok">'.__('OK').'</td></tr>';
	}
	else{
		echo '<tr><td class="line_item">'.__('Database tables').'</td><td class="line_nok">'.__('No HuMo-gen tables found in database.').'<br>';

		echo ' <form method="post" action="'.$path_tmp.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="install">';
		echo '<input type="Submit" name="submit" value="'.__('Install HuMo-gen database tables').'">';
		echo '</form>';
		echo '</td></tr>';
		$install_status=false;
	}

}

// *** Only show table status if database AND tables are checked ***
if ($install_status==true){
	// *** Show size of statistics table ***
	//$size = $dbh->query('SHOW TABLE STATUS WHERE Name="humo_stat_date"');
	$sizeqry = $dbh->query('SHOW TABLE STATUS LIKE "humo_stat_date"');
	//$size='? kB';
	//if ($sizeqry){
		//$sizeDb=$sizeqry->fetch();
		$sizeDb=$sizeqry->fetch(PDO::FETCH_OBJ);
		$size='0 kB';
		if ($sizeDb){
			$size=$sizeDb->Data_length;
			$bytes = array( ' kB', ' MB', ' GB', ' TB' );
			$size = $size / 1024;
			foreach ($bytes as $val) {
				if (1024 <= $size) {
					$size = $size / 1024;
					continue;
				}
				break;
			}
			$size= round( $size, 1 ) . $val;
		}
	//}

	echo '<tr><td class="line_item">'.__('Size of statistics table').'</td><td class="line_ok">'.$size;
		echo ' <a href="index.php?page=statistics">'.__('If needed remove old statistics.').'</a>';
	echo '</td></tr>';

	echo '<tr class="table_header"><th colspan="2">'.__('HuMo-gen security items').'</th></tr>';

	// *** Check for standard admin username and password ***
	$sql="SELECT * FROM humo_users WHERE user_name='admin' OR (user_name='admin' AND user_password='".MD5('humogen')."')";
	$check_login = $dbh->query($sql);
	$check_loginDb=$check_login->fetch(PDO::FETCH_OBJ);
	if ($check_loginDb){
		$check_login='<td class="line_nok">'.__('Standard admin username or admin password is used.');
		$check_login.='<br><a href="index.php?page=users">'.__('Change admin username and password.').'</a>';
	}
	else
		$check_login='<td class="line_ok">'.__('OK');
	echo '<tr><td class="line_item">'.__('Check admin account').'</td>'.$check_login;


	// *** Show failed logins ***
	//3600 = 1 uur
	//86400 = 1 dag
	//604800 = 1 week
	//2419200 = 1 maand
	//31536000 = jaar
	$sql="SELECT count(log_id) as count_failed FROM humo_user_log
		WHERE log_status='failed'
		AND UNIX_TIMESTAMP(log_date) > (UNIX_TIMESTAMP(NOW()) - 2419200)";
	$check_login = $dbh->query($sql);
	$check_loginDb=$check_login->fetch(PDO::FETCH_OBJ);
	if ($check_loginDb){
		$check_login='<td class="line_ok">'.__('Number of failed logins attempts last month').': '.$check_loginDb->count_failed;
		$check_login.='<br><a href="index.php?page=log">'.__('Logfile users').'</a>';
	}
	//else
	//	$check_login='<td class="line_ok">'.__('OK');
	echo '<tr><td class="line_item">'.__('Failed login attempts').'</td>'.$check_login;


	// *** Check login ***
	$check_login='<td class="line_nok"><b>'.__('The folder "admin" has NOT YET been secured.').'</b>';
	if (isset($_SERVER["PHP_AUTH_USER"])){
		$check_login='<td class="line_ok">'.__('At the moment you are logged in through an .htacces file.');
	}
	//if (isset($_SESSION["user_name_admin"]) AND $_SESSION["user_name_admin"]=="beheer") {
	if (isset($_SESSION["user_name_admin"])) {
		$check_login='<td class="line_nok">'.__('At the moment you are logged in through PHP-MySQL.');
	}

	echo '<tr><td class="line_item">'.__('Login control').'</td>'.$check_login;

	print '<form method="POST" action="'.$path_tmp.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	print ' <input type="Submit" name="login_info" value="'.__('INFO').'">';
	print '</form>';

	if (isset($_POST['login_info'])){
		echo '<div id="security_remark">';

			echo __('After installation of the tables (click on the left at Install) the admin folder will be secured with PHP-MySQL security.
<p>You can have better security with .htaccess (server security).<br>
If the administration panel of your webhost has an option to password-protect directories, use this option on the \"admin\" folder of HuMo-gen. If you don\'t have such an option, you can make an .htaccess file yourself.<br>
Make a file .htaccess:');

			echo '<p>AuthType Basic<br>
				AuthName "'.__('Secured website').'"<br>';
				echo 'AuthUserFile '.$_SERVER['DOCUMENT_ROOT'].'/humo-gen/admin/.htpasswd<br>';
				echo '&lt;LIMIT GET POST&gt;<br>
				require valid-user<br>
				&lt;/LIMIT&gt;';

			echo '<p>'.__('Next, you need a file with user names and passwords.<br>
For example go to: http://www.htaccesstools.com/htpasswd-generator/<br>
The file .htpasswd will look something like this:<br>');

			echo '<p>Huub:mmb95Tozzk3a2';

			echo '<form method="POST" action="'.$path_tmp.'" style="display : inline;">';
			echo '<p>'.__('You can also try this password generator:').'<br>';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="text" name="username" value="username" size="20">';
			echo '<input type="text" name="password" value="password" size="20">';
			echo ' <input type="Submit" name="login_info" value="'.__('Generate new ht-password').'">';
			echo '</form>';

			if (isset($_POST['username'])){
				//$htpassword=crypt(trim($_POST['password']),base64_encode(CRYPT_STD_DES));
				$htpassword2 = crypt($_POST['password'], base64_encode($_POST['password']));
				//echo $_POST['username'].":".$htpassword.'<br>';
				echo $_POST['username'].":".$htpassword2;
			}

		echo '</div>';
	}
	echo '</td></tr>';

	// *** Register global. Not nessecary in PHP 6.0! ***
	if (!ini_get('register_globals')){
		echo '<tr><td class="line_item">register_globals</td><td class="line_ok">'.__('OK (option is OFF)').'</td></tr>';
	}
	else{
		echo '<tr><td class="line_item">register_globals</td><td class="line_nok">'.__('UNSAFE (option is ON)<br>change this option in .htaccess file or put: "register_globals = Off" in the php.ini file.').'</td></tr>';
	}

	// *** Magic_quotes_gpc. Deprecated in PHP 5.3.0! ***
	$version = explode('.', phpversion() );
	if ($version[0] < 6 AND $version[1] < 3){
		if (ini_get('magic_quotes_gpc')){
			echo '<tr><td class="line_item">magic_quotes_gpc</td><td class="line_ok">'.__('OK (option is ON)').'</td></tr>';
		}
		else{
			echo '<tr><td class="line_item">magic_quotes_gpc</td><td class="line_nok">'.__('UNSAFE (option is OFF)<br>change this option in .htaccess file or put: "magic_quotes_gpc = On" in the php.ini file.').'</td></tr>';
		}
	}

	// *** display_errors ***
	if (!ini_get('display_errors')){
		echo '<tr><td class="line_item">display_errors</td><td class="line_ok">'.__('OK (option is OFF)').'</td></tr>';
	}
	else{
		echo '<tr><td class="line_item">display_errors</td><td class="line_nok">'.__('UNSAFE (option is ON)<br>change this option in .htaccess file.').'</td></tr>';
	}

	// *** Family trees ***
	@$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
	if ($datasql){
		$tree_counter=0;
		echo '<tr class="table_header"><th colspan="2">'.__('Family trees').'</th></tr>';
		//echo '<tr class="table_header"><th colspan="2"><a href="index.php?page=tree">'.__('Family trees').'</a></th></tr>';

		while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			// *** Skip empty lines (didn't work in query...) ***
			if ($dataDb->tree_prefix!='EMPTY'){
				$tree_counter++;
				echo '<tr><td class="line_item">'.__('Status tree').' '.$tree_counter.'</td>';

				if ($dataDb->tree_persons){
					echo '<td class="line_ok">';
				}
				else{
					echo '<td class="line_nok">';
				}
				$treetext=show_tree_text($dataDb->tree_id, $selected_language);
				//echo $dirmark1.$treetext['name'];
				echo $dirmark1.'<a href="index.php?page=tree">'.$treetext['name'].'</a>';

				if ($dataDb->tree_persons>0){
					print $dirmark1.' <font size=-1>('.$dataDb->tree_persons.' '.__('persons').', '.$dataDb->tree_families.' '.__('families').')</font>';
				}
				else{
					echo ' <b>'.__('This tree does not yet contain any data or has not been imported properly!').'</b><br>';
						// *** Read gedcom file ***
						echo ' <form method="post" action="'.$path_tmp.'" style="display : inline;">';
						echo '<input type="hidden" name="page" value="tree">';
						echo '<input type="hidden" name="tree_prefix" value="'.$dataDb->tree_prefix.'">';
						echo '<input type="Submit" name="step1" value="'.__('Import Gedcom file').'">';
						echo '</form>';

						// *** Editor ***
						echo ' '.__('or').' <form method="post" action="index.php?page=editor" style="display : inline;">';
						echo '<input type="hidden" name="tree_prefix" value="'.$dataDb->tree_prefix.'">';
						echo '<input type="Submit" name="submit" value="'.__('Editor').'">';
						echo '</form>';
				}
				echo '</td></tr>';
			}
		}
	}
	else{
		echo '<tr><td>'.__('Trees table').'</td><td class="line_nok">ERROR</td></tr>';
	}

	// *** End of check database and table status ***
}

echo '</table>';

echo '</div>';
?>