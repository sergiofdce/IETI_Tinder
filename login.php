<?php
session_start();
include 'includes/functions.php';
require_once 'config/db_connection.php';

error_reporting(E_ERROR | E_PARSE);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = hash('sha512', $_POST["password"]);

        $query = "SELECT * FROM users WHERE email = :email AND password = :password";
        $params = [':email' => $email, ':password' => $password];
        $results = executeQuery($pdo, $query, $params);

        if ($results) {
            $_SESSION["user_id"] = $results[0]["id"];
            $_SESSION["email"] = $results[0]["email"];
            logEvent("login_success", "El usuario " . $results[0]["email"] . " ha iniciado sesion", $results[0]["email"]);
            header("Location: discover.php");
        } else {
            $message = "Datos incorrectos";
            $passwordErrorClass = "form__field--error";
            logEvent("login_failure", "El usuario " . $_POST["email"] . " ha fallado la contraseña", $_POST["email"]);
        }
    } else {
        $message = "Rellene ambos campos";
        logEvent("login_failure", "Se ha introducido uno o varios campos vacíos", "empty");
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- DOC -->
    <title>EasyDates</title>
    <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
</head>

<body>
    <header id="login">
    </header>
    <main id="login">
        <div id="login-container">
            <div id="login-header">
                <img src="assets/img/web/logo.png" alt="Login" id="login-image">
                <p id="login-eslogan" class="fuente-titulos">Love Made Simple</p>
            </div>

            <div id="login" class="roboto">
                <form action="login.php" method="post">
                    <div class='login-alert'><?php echo $message; ?></div>

                    <div class="form__group field">
                        <input type="email" name="email" class="<?php echo $emailErrorClass; ?> form__field" placeholder="Email">
                        <label for="email" class="form__label">Email</label>
                    </div>

                    <div class="form__group field">
                        <input type="password" name="password" class="<?php echo $passwordErrorClass; ?> form__field" placeholder="Contraseña">
                        <label for="password" class="form__label">Contraseña</label>
                    </div>


                    <input type="submit" name="submit" value="Iniciar Sesión" class="styled-submit">


                </form>

                <div id="login-links">
                    <p class="login-link"><a href="#">¿Has olvidado la contraseña?</a></p>
                    <p class="login-link"><a href="register.php">Crear nueva cuenta</a></p>
                </div>

            </div>
        </div>
    </main>

    <footer id="login">
    </footer>

</body>

</html>