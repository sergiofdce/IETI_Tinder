<?php 
session_start();
include '../includes/functions.php';
require_once '../config/db_connection.php';

if (!isset($_SESSION["user_id"])) {
    header('HTTP/1.0 403 Forbidden');
}

$user_id = $_SESSION["user_id"];
$query = "SELECT privileges FROM users WHERE id = :user_id";
$params = [':user_id' => $user_id];
$user = executeQuery($pdo, $query, $params);
if ($user[0]['privileges'] != "admin") {
    header('HTTP/1.0 403 Forbidden');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyDates - Admin Panel</title>
</head>
<body id="adminPanel">
    <h1>Admin Panel (En construcción)</h1>
</body>
</html>