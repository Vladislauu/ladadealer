<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
echo json_encode(['phone' => isset($_SESSION['user_phone'])]);
exit;