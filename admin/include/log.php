<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//echo '<h1 align=center>'.__('Log').'</h1>';
echo '<h1 align=center>'.__('Logfile users').'</h1>';

//echo __('Logfile users');

$logbooksql="SELECT * FROM humo_user_log ORDER BY log_date DESC";
$logbook=$dbh->query($logbooksql);

echo '<table class="humo" border="1" cellspacing="0" width="auto">';
	//echo '<tr class="table_header"><th colspan="4">'.__('Logfile users').'</th></tr>';

	echo '<tr class="table_header">';
	echo '<th>'.__('Date - time').'</th>';
	echo '<th>'.__('User').'</th>';
	echo '<th>'.__('User/ Admin').'</th>';
	echo '<th>'.__('IP address').'</th>';
	echo '</tr>';

	while ($logbookDb=$logbook->fetch(PDO::FETCH_OBJ)){
		echo '<tr>';
		echo '<td>'.$logbookDb->log_date.'</td>';
		echo '<td>'.$logbookDb->log_username.'</td>';
		echo '<td>'.$logbookDb->log_user_admin.'</td>';
		echo '<td>'.$logbookDb->log_ip_address.'</td>';
		echo '</tr>';
	}
echo '</table>';
?>