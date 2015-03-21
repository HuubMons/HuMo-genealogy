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
	$conn = 'mysql:host='.$_POST['db_host'];
	try {
		$db_check = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
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
		$temp_dbh = new PDO($conn,DATABASE_USERNAME,DATABASE_PASSWORD);	
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

			//define("DATABASE_HOST",     "localhost");
			//define("DATABASE_USERNAME", "root");
			//define("DATABASE_PASSWORD", "usbw");
			//define("DATABASE_NAME",     "humo-gen");

			if (substr($buffer[$i],0,21)=='define("DATABASE_HOST'){
				$buffer[$i]='define("DATABASE_HOST",     "'.$_POST['db_host'].'");'."\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,25)=='define("DATABASE_USERNAME'){
				$buffer[$i]='define("DATABASE_USERNAME", "'.$_POST['db_username'].'");'."\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,25)=='define("DATABASE_PASSWORD'){
				$buffer[$i]='define("DATABASE_PASSWORD", "'.$_POST['db_password'].'");'."\n";
				$check_config=true;
			}

			if (substr($buffer[$i],0,21)=='define("DATABASE_NAME'){
				$buffer[$i]='define("DATABASE_NAME",     "'.$_POST['db_name'].'");'."\n";
				$check_config=true;
			}
	
			fwrite($bestand_config,$buffer[$i]);
		}
		fclose($bestand_config);
		if ($check_config==false){
			$result_message='<b> *** '.__('There is a problem in the db_config file, maybe an old db_config file is used.').' ***</b>';
		}
	}
}


// *************************************************************************
// *** Show HuMo-gen status, use scroll bar to show lots of family trees ***
// *************************************************************************


//echo '<div style="height:400px; width:750px; overflow-y: auto; margin-left:auto; margin-right:auto;">';
echo '<div style="height:450px; width:750px; overflow-y: auto; margin-left:auto; margin-right:auto;">';
echo '<table class="humo">';
	echo '<tr class="table_header"><th colspan="2">'.__('HuMo-gen status').'</th></tr>';

	// *** HuMo-gen version ***
	if (isset($humo_option["version"]))
		echo '<tr><td>'.__('HuMo-gen Version').'</td><td style="background-color:#00FF00">'.__('HuMo-gen Version').': '.$humo_option["version"].'</td></tr>';

	// *** PHP Version ***
	$version = explode('.', phpversion() );
	if ($version[0] > 4){
		echo '<tr><td>'.__('PHP Version').'</td><td style="background-color:#00FF00">'.__('PHP Version').': '.phpversion().'</td></tr>';
	}
	else{
		echo '<tr><td>'.__('PHP Version').'</td><td style="background-color:#FF6600">'.phpversion().' '.__('It is recommended to update PHP!').'</td></tr>';
	}

	// *** MySQL Version ***
	if(isset($dbh)) {  
		// in PDO and MySQLi you can't get MySQL version number until connection is made 
		// so on very first screens before saving connection parameters we do without. 
		// as of Jan 2014 mysql_get_server_info still works but once deprecated will give errors, so better so without.
		$mysqlversion = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);  
		$version = explode('.',$mysqlversion);
		if ($version[0] > 4){
			echo '<tr><td>'.__('MySQL Version').'</td><td style="background-color:#00FF00">'.__('MySQL Version').': '.$mysqlversion.'</td></tr>';
		}
		else{
			echo '<tr><td>'.__('MySQL Version').'</td><td style="background-color:#FF6600">'.$mysqlversion.' '.__('It is recommended to update MySQL!').'</td></tr>';
		}
	}

// *** Check if database and tables are ok ***
$install_status=true;

// *** Check database, if needed install local database ***
echo '<tr><td>';
if (@$database_check){
	echo __('Database').'</td>';
	echo '<td style="background-color:#00FF00">'.__('OK');
}
else{
	echo __('Database').'</td><td style="background-color:#FF0000">';

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
		echo '<tr><td>'.__('Database tables').'</td><td style="background-color:#00FF00">'.__('OK').'</td></tr>';
	}
	else{
		//echo '<tr><td>'.__('Database tables').'</td><td style="background-color:#FF0000">'.__('ERROR').'<br>';
		echo '<tr><td>'.__('Database tables').'</td><td style="background-color:#FF0000">'.__('No HuMo-gen tables found in database.').'<br>';

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

	// *** Check login ***
	$check_login='<td style="background-color:#FF0000"><b>'.__('The folder "admin" has NOT YET been secured.').'</b>';
	if (isset($_SERVER["PHP_AUTH_USER"])){
		$check_login='<td style="background-color:#00FF00">'.__('At the moment you are logged in through an .htacces file.');
	}
	//if (isset($_SESSION["user_name_admin"]) AND $_SESSION["user_name_admin"]=="beheer") {
	if (isset($_SESSION["user_name_admin"])) {
		$check_login='<td style="background-color:#FF6600">'.__('At the moment you are logged in through PHP-MySQL.');
	}
	echo '<tr><td>'.__('Login control').'</td>'.$check_login;

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
		echo '<tr><td>register_globals</td><td style="background-color:#00FF00">'.__('OK (option is OFF)').'</td></tr>';
	}
	else{
		echo '<tr><td>register_globals</td><td style="background-color:#FF6600">'.__('UNSAFE (option is ON)<br>change this option in .htaccess file or put: "register_globals = Off" in the php.ini file.').'</td></tr>';
	}

	// *** Magic_quotes_gpc. Deprecated in PHP 5.3.0! ***
	$version = explode('.', phpversion() );
	if ($version[0] < 6 AND $version[1] < 3){
		if (ini_get('magic_quotes_gpc')){
			echo '<tr><td>magic_quotes_gpc</td><td style="background-color:#00FF00">'.__('OK (option is ON)').'</td></tr>';
		}
		else{
			echo '<tr><td>magic_quotes_gpc</td><td style="background-color:#FF6600">'.__('UNSAFE (option is OFF)<br>change this option in .htaccess file or put: "magic_quotes_gpc = On" in the php.ini file.').'</td></tr>';
		}
	}

	// *** display_errors ***
	if (!ini_get('display_errors')){
		echo '<tr><td>display_errors</td><td style="background-color:#00FF00">'.__('OK (option is OFF)').'</td></tr>';
	}
	else{
		echo '<tr><td>display_errors</td><td style="background-color:#FF6600">'.__('UNSAFE (option is ON)<br>change this option in .htaccess file.').'</td></tr>';
	}

	// *** Family trees ***
	@$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
	if ($datasql){

		// *** Show size of statistics table ***
		$size = $dbh->query('SHOW TABLE STATUS WHERE Name="humo_stat_date"');
		$sizeDb=$size->fetch(PDO::FETCH_OBJ);
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

		echo '<tr><td>'.__('Size of statistics table').'</td><td style="background-color:#00FF00">'.$size;
			echo ' <a href="index.php?page=statistics">'.__('If needed remove old statistics.').'</a>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Trees table').'</td><td style="background-color:#00FF00">OK</td></tr>';

		$tree_counter=0;
		while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			// *** Skip empty lines (didn't work in query...) ***
			if ($dataDb->tree_prefix!='EMPTY'){
				$tree_counter++;
				echo '<tr><td><b>'.__('Status tree').' '.$tree_counter.'</b></td>';

				if ($dataDb->tree_persons){
					echo '<td style="background-color:#00FF00">';
				}
				else{
					echo '<td style="background-color:#FF0000">';
				}
				$treetext=show_tree_text($dataDb->tree_prefix, $selected_language);
				echo $dirmark1.$treetext['name'];
				if ($dataDb->tree_persons>0){
					print $dirmark1.' <font size=-1>('.$dataDb->tree_persons.' '.__('persons').', '.$dataDb->tree_families.' '.__('families').')</font>';
				}
				else{
					echo ' <b>'.__('This tree does not yet contain any data or has not been imported properly!').'</b><br>';
						echo ' <form method="post" action="'.$path_tmp.'" style="display : inline;">';
						//echo '<input type="hidden" name="page" value="gedcom">';
						echo '<input type="hidden" name="page" value="tree">';
						echo '<input type="hidden" name="tree_prefix" value="'.$dataDb->tree_prefix.'">';
						echo '<input type="Submit" name="step1" value="'.__('Import Gedcom file').'">';
						echo '</form>';
				}
				echo '</td></tr>';
			}
		}
	}
	else{
		echo '<tr><td>'.__('Trees table').'</td><td style="background-color:#FF0000">FOUT</td></tr>';
	}

	// *** End of check database and table status ***
}

echo '</table>';
echo '</div>';

// *** Show result messages after installing settings of db_login.php ***
//echo '<p>'.$result_message.'</p>';

// *** Only show if database AND tables are checked ***
if ($install_status==true){
	echo '<p>'.__('TIPS:<br>
- Administrate one or more trees: go to "Family Trees"<br>
- Import Gedcom file: in menu under "Family trees"
<p>Not everything can be changed here:<br>
- The layout of HuMo-gen (style sheet) can be changed in: gedcom.css<br>
- Prefixes for family names can be changed in: prefixes.php');
	echo '</p>';
}
?>