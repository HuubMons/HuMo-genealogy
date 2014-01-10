<?php
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="humo_backup-20140105-220239.sql.zip"');
readfile('humo_backup.sql.zip');
?>