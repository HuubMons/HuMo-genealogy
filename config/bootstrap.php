<?php 

// *** Check if HuMo-genealogy is in a CMS system ***
//	- Code for all CMS: if (CMS_SPECIFIC) {}
//	- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
//	- Code NOT for CMS: if (!CMS_SPECIFIC) {}
if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);

/**
 * Constants and Class for start engine
 */

require __DIR__ . '/../nextlib/Request.php';

$app_config = require __DIR__ . '/application.php';
$blacklistedIp = require __DIR__ . '/blacklist_ip.php';

$request = new Request();

if (isset($app_config['app_env']) && $app_config['app_env'] === 'DEV') {
    error_reporting(E_ALL);
}

date_default_timezone_set($app_config['timezone']);

if (!CMS_SPECIFIC) {
	// session_cache_limiter('private, must-revalidate'); //TODO: @DEVS: Nonsense here, "must-revalidate" is for "nocache", "private" need a "max-age" to be unvalid, see https://www.php.net/manual/en/function.session-cache-limiter.php
	session_cache_limiter('nocache, must-revalidate'); 
	session_start();
	// *** Regenerate session id regularly to prevent session hacking *** 
	// TODO: @DEVS: will be revalidate only in authentification, see https://www.php.net/session_regenerate_id and confirmed here: https://stackoverflow.com/questions/22965067/when-and-why-i-should-use-session-regenerate-id
	// session_regenerate_id();  
}

// *** logout ***
if (isset($_GET['log_off'])) {
	session_destroy();
}

// *** blacklisted ip ***
if (in_array($request->getClientIp(), $blacklistedIp)) {
    echo 'Access not allowed.';
	exit;
}

// *** Check if visitor is a bot or crawler ***
$bot_visit = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);