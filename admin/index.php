<?php
session_start();
include '../includes/functions.php';
require_once '../config/db_connection.php';

logEvent("page_view", "El usuario ha accedido a la página Admin", $_SESSION["email"]);

if (!isset($_SESSION["user_id"])) {
    header("HTTP/1.0 403 Forbidden");
    die();
}
$user_id = $_SESSION["user_id"];
$query = "SELECT privileges FROM users WHERE id = :user_id";
$params = [':user_id' => $user_id];
$user = executeQuery($pdo, $query, $params);
if ($user[0]['privileges'] != "admin") {
    logEvent("page_view", "Problema de permisos, sin acceso a Admin", $_SESSION["email"]);
    header("HTTP/1.0 403 Forbidden");
    include ('../errors/error403.php');
    die();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyDates - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="icon" type="image/png" href="../assets/img/web/icon.ico">

</head>

<body id="admin-panel">
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Admin EasyDates</a>
        <button id="menuToggle" class="menu-toggle">☰</button>
    </nav>

    <div class="admin-container">
        <nav class="admin-nav" id="adminNav">
            <div class="nav-header">
                <h2>Panel de Administración</h2>
            </div>
            <ul class="nav-links">
                <li><a href="users.php" class="nav-link">
                        <span class="nav-icon">👥</span>
                        Gestión de usuarios
                    </a></li>
                <li><a href="logs.php" class="nav-link">
                        <span class="nav-icon">📋</span>
                        Gestión de logs
                    </a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <h1>Panel de Administración</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Usuarios</h3>
                    <p>Gestionar usuarios del sistema</p>
                    <a href="users.php" class="card-link">Ir a Usuarios</a>
                </div>
                <div class="card">
                    <h3>Logs</h3>
                    <p>Ver registros del sistema</p>
                    <a href="logs.php" class="card-link">Ir a Logs</a>
                </div>
            </div>
        </main>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const adminNav = document.getElementById('adminNav');

        menuToggle.addEventListener('click', () => {
            adminNav.classList.toggle('show');
        });
    </script>
</body>

</html>