<?php
session_start();

require_once 'config/db_connection.php';
include 'includes/functions.php';


logEvent("page_view", "Un usuario ha accedido a la página forgot_password", "new_user");

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

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['verify']) && isset($_GET['token'])) {
                $user_id = $_GET['verify'];
                $user_token = $_GET['token'];

                $query = "SELECT * FROM users WHERE id = :user_id AND token = :user_token";
                $params = [':user_id' => $user_id, ':user_token' => $user_token];
                $results = executeQuery($pdo, $query, $params);

                if ($results) {
                    echo '
                    <div>
                        <div class="container-cabecera">
                            <h1 class="fuente-titulos">Crear nueva contraseña: </h1>
                        </div>
            
                        <form class="profile-form" method="POST" action="forgot_password.php" id="forgotPasswordForm">
                            <div class="input-group">
                                <label for="password" id="password-label">Contraseña:</label>
                                <input type="password" id="password" name="password" placeholder="Nueva contraseña">
                            </div>
                            <div class="input-group">
                                <label for="password2" id="password2-label">Repetir contraseña:</label>
                                <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña">
                            </div>
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            <input type="submit" value="Continuar">
            
                        </form>
                    </div>
            
                    </div>
                    <div class="notification-container" id="notificationContainer"></div>';
                    
                } else {
                    //mostrar un mensaje de error
                    echo "     
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showMessage('wrongEmail', 'El enlace de verificación no es válido.');
                        })
                    </script>";
                    echo '
                    <div>
                        <div class="container-cabecera">
                            <h1 class="fuente-titulos">Recuperar contraseña: </h1>
                        </div>
            
                        <form class="profile-form" method="POST" action="forgot_password.php" id="forgotEmailForm">
                            <div class="input-group">
                                <label for="email" id="email-label">Correo:</label>
                                <input type="text" id="email" name="email" placeholder="correo@ieti.site">
                            </div>
                            <input type="submit" value="Continuar">
            
                        </form>
                    </div>
            
                    </div>
                    <div class="notification-container" id="notificationContainer"></div>';
                }
            }else {
                //este echo no funciona y no sé por qué
                    echo '
                    <div>
                        <div class="container-cabecera">
                            <h1 class="fuente-titulos">Recuperar contraseña: </h1>
                        </div>
            
                        <form class="profile-form" method="POST" action="forgot_password.php" id="forgotEmailForm">
                            <div class="input-group">
                                <label for="email" id="email-label">Correo:</label>
                                <input type="text" id="email" name="email" placeholder="correo@ieti.site">
                            </div>
                            <input type="submit" value="Continuar">
            
                        </form>
                    </div>
            
                    </div>
                    <div class="notification-container" id="notificationContainer"></div>';
                }
        }
       
        ?>
    </main>

    <footer>

    </footer>


    <script src="assets/js/forgot_password.js"></script>


</body>

</html>