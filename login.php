<?php
session_start();
//iniciar sesión automáticamente si se detecta cookie de sesión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = hash('sha512', $_POST["password"]);

        if (isset($_COOKIE["login_cookie"])) {
            //auto login con cookie
        }

        try {
            $hostname = "localhost";
            $dbname = "tinder";
            $username = "admin";
            $pw = "admin123";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Error connectant a la BD: " . $e->getMessage() . "<br>\n";
            exit;
        }
        try {
            //preparar consulta y sanear los parámetros
            $query = $pdo->prepare("SELECT * FROM users WHERE mail = :mail AND password = :password");
            $query->bindParam(':mail', $email);
            $query->bindParam(':password', $password);
            $query->execute();
        } catch (PDOException $e) {
            echo "Error de SQL<br>\n";
            //comprobacion de errores
            $e = $query->errorInfo();
            if ($e[0] != '00000') {
                echo "\nPDO::errorInfo():\n";
                die("Error accedint a dades: " . $e[2]);
            }
        }
        //si login correcto, ir a vista discover
        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            $_SESSION["user"] = $row["id"];
            //guardar sesión en cookie
            setcookie("login_cookie", $row["id"], time() + 3600);
            //responder con success al ajax
            $message = 'success';

            //si el login no es correcto buscamos si existe el usuario para mostrar password incorrecto     
        } else {
            try {
                //preparar consulta y sanear los parámetros
                $query = $pdo->prepare("SELECT * FROM users WHERE mail = :mail");
                $query->bindParam(':mail', $_POST["email"]);
                $query->execute();
            } catch (PDOException $e) {
                echo "Error de SQL<br>\n";
                //comprobacion de errores
                $e = $query->errorInfo();
                if ($e[0] != '00000') {
                    echo "\nPDO::errorInfo():\n";
                    die("Error accedint a dades: " . $e[2]);
                }
            }
            if ($query->rowCount() > 0) {
                $message = "incorrect password";
            } else {
                $message = "incorrect user";
            }
        }
    } else {
        $message = "empty post";
    }
}
echo $message;
?>
<?php /*
<!--
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
    <div class="flex-container">
        <main>
            <div id="logo">
                <h1 class="funnel-display">LOGO PLACEHOLDER</h1>
                <p class="roboto">Descripción del sitio</p>
            </div>
            <div id="login" class="roboto">
                <form action="login.php" method="post">
                    <label for="email" class="funnel-display">Email</label>
                    <input type="email" name="email" placeholder="usuario@ieti.site">
                    <label for="password" class="funnel-display">Contraseña</label>
                    <input type="password" name="password" placeholder="******">
                    <?php echo $message; ?>
                    <input type="submit" name="submit" value="Iniciar Sesión">
                </form>
                <p><a href="#">¿Has olvidado la contraseña?</a></p>
                <p><a href="register.php">Crear nueva cuenta</a></p>
            </div>
        </main>
    </div>
</body>

</html> -->
*/ ?>