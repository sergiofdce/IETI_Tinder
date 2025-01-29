<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}
require_once 'config/db_connection.php';
include 'includes/functions.php';

logEvent("page_view", "Un usuario ha accedido a la página delete-account", $_SESSION["email"]);

date_default_timezone_set('Europe/Madrid');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>EasyDates - Nuevo registro</title>
    <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
    <!-- API Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

</head>

<body id="register">

    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>

    <main>
        <div>
            <div class="container-cabecera">
                <h1 class="fuente-titulos">Eliminar cuenta</h1>
            </div>

            <form class="profile-form" method="POST" action="delete-account.php" id="deleteAccountForm">
                <p class="confirmation-message">¡Atención! Esta operación es irreversible. Para continuar, escriba "Eliminar".</p>
                <div class="input-group">
                    <input type="text" id="delete" name="delete" placeholder="Escriba aquí...">
                </div>
                <input type="hidden" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>">
                <input type="submit" value="Continuar">

            </form>
        </div>

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
                <li>
                    <a href="profile.php">
                        Perfil
                        <!-- <img class="footer-icons" src="assets/img/web/user.png" alt="Logout"> -->
                    </a>
                </li>
            </ul>
        </nav>
    </footer>


    <script src="assets/js/delete-account.js"></script>


</body>

</html>