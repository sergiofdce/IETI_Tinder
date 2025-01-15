<?php
session_start();
include 'includes/functions.php';
require_once 'config/db_connection.php';

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
            $query = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];
            $results = executeQuery($pdo, $query, $params);

            if ($results) {
                $message = "<div class='alert alert-danger'>Contraseña incorrecta</div>";
                $passwordErrorClass = "borderError";
                logEvent("login_failure", "El usuario " . $_POST["email"] . " ha fallado la contraseña", $_POST["email"]);
            } else {
                $message = "<div class='alert alert-danger'>Usuario incorrecto</div>";
                $emailErrorClass = "borderError";
                logEvent("login_failure", "El usuario " . $_POST["email"] . " no existe", $_POST["email"]);
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Rellene ambos campos</div>";
        $emailErrorClass = "borderError";
        $passwordErrorClass = "borderError";
        logEvent("login_failure", "Se ha introducido uno o varios campos vacíos", "empty");
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@600&family=Roboto:wght@300&display=swap" rel="stylesheet">
    <title>Iniciar Sesión</title>
</head>

<body id="login">
    <header>
    </header>
    <main>
        <div class="flex-container">
            <div id="logo">
                <h1 class="funnel-display">LOGO PLACEHOLDER</h1>
                <p class="roboto">Descripción del sitio</p>
            </div>
            <div id="login" class="roboto">
                <form action="login.php" method="post">
                    <?php echo $message; ?>
                    <label for="email" class="funnel-display">Email</label>
                    <input type="email" name="email" class="<?php echo $emailErrorClass; ?>" placeholder="usuario@ieti.site">
                    <label for="password" class="funnel-display">Contraseña</label>
                    <input type="password" name="password" class="<?php echo $passwordErrorClass; ?>" placeholder="******">
                    <input type="submit" name="submit" value="Iniciar Sesión">
                </form>
                <p><a href="#">¿Has olvidado la contraseña?</a></p>
                <p><a href="register.php">Crear nueva cuenta</a></p>
            </div>
        </div>
    </main>

    <footer>
    </footer>

</body>

</html>
