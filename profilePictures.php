<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

$user_id = $_SESSION["user_id"];
require_once 'config/db_connection.php';

$query = "SELECT id, name, surname, alias, birth_date, location, 
                (SELECT path FROM user_images WHERE user_id = ? LIMIT 1) AS photo 
                FROM users WHERE id = ?";

$params = [$user_id, $user_id];
$user = executeQuery($pdo, $query, $params);

if (empty($user)) {
    echo "Usuario no encontrado.";
    exit();
}

$user = $user[0];

// Configuración de la cantidad máxima de fotos visibles
$maxVisiblePhotos = 6;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>EasyDates - Perfil</title>
    <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
    <!-- API Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="assets/js/profilePictures.js"></script>
    <script src="assets/js/profile.js"></script>
</head>

<body>

    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>

    <main>
        <h2>Mis fotos</h2>
        <div id="divEditPictures">
            <ul id="photoList">
                <script>
                    const maxVisiblePhotos = <?php echo $maxVisiblePhotos; ?>; // Configuración de cantidad máxima
                </script>
            </ul>
        </div>

        <div class="notification-container" id="notificationContainer"></div>
    </main>

    <footer>
        <nav>
            <ul>
                <li>
                    <a href="discover.php">
                        Descubrir
                        <!-- <img class="footer-icons" src="assets/img/web/search.png" alt="Logout"> -->
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        Mensajes
                        <!-- <img class="footer-icons" src="assets/img/web/message.png" alt="Logout"> -->
                    </a>
                </li>
                <li id="navFocus">
                    <a href="profile.php">
                        Perfil
                        <!-- <img class="footer-icons" src="assets/img/web/user.png" alt="Logout"> -->
                    </a>
                </li>
            </ul>
        </nav>
    </footer>

</body>

</html>
