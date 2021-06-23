<?php
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="humo_backup-20190524-101057.sql.zip"');
readfile('humo_backup.sql.zip');
?>