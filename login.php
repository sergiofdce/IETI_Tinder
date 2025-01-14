<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = hash('sha512', $_POST["password"]);

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
            $query = $pdo->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
            $query->bindParam(':email', $email);
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
            $_SESSION["email"] = $row["email"];
            //redireccionar a discover
            header("Location: discover.php");

            //si el login no es correcto buscamos si existe el usuario para mostrar password incorrecto     
        } else {
            try {
                //preparar consulta y sanear los parámetros
                $query = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $query->bindParam(':email', $_POST["email"]);
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
                $message = "<div class='alert alert-danger'>Contraseña incorrecta</div>";
                $passwordErrorClass = "borderError";
            } else {
                $message = "<div class='alert alert-danger'>Usuario incorrecto</div>";
                $emailErrorClass = "borderError";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Rellene ambos campos</div>";
        $emailErrorClass = "borderError";
        $passwordErrorClass = "borderError";
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