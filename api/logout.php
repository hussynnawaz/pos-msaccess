<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$auth = new AuthController();
$result = $auth->logout();

echo json_encode($result);
