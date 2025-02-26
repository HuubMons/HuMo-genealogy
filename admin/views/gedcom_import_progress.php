<?php
session_start();
$percentComplete = isset($_SESSION['save_import_progress']) && is_numeric($_SESSION['save_import_progress']) ? $_SESSION['save_import_progress'] : 0;
echo json_encode(['percentComplete' => $percentComplete]);
