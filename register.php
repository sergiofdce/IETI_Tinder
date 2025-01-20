<?php
session_start();

require_once 'config/db_connection.php';
include 'includes/functions.php';
logEvent("page_view", "Un usuario ha accedido a la página Register", "new_user");

date_default_timezone_set('Europe/Madrid');

//variables para las clases error
$message = "";
$errorName = "";
$errorSurname = "";
$errorAlias = "";
$errorBirthdate = "";
$errorLocation = "";
$errorGenre = "";
$errorSexualPreference = "";
$errorEmail = "";
$errorPassword = "";
$errorPassword2 = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['name']) && !empty($_POST['surname']) && !empty($_POST['username']) && !empty($_POST['birthdate']) && !empty($_POST['location']) && !empty($_POST['genre']) && !empty($_POST['sexual_preference']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password2'])) {

        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $alias = $_POST['username'];
        $birth_date = $_POST['birthdate'];
        $location = $_POST['location'];
        $genre = $_POST['genre'];
        $sexual_preference = $_POST['sexual_preference'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];
        $timestamp = date('Y-m-d H:i:s');

        if (!empty($password) && $password !== $password2) {
            echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
            exit();
        }

        if (!empty($password)) {
            $hashed_password = hash('sha512', $password);
        }
        // comprobar que el email no exista
        $checkEmailQuery = "SELECT * FROM users WHERE email = :email";
        $checkEmailParams = [':email' => $email];
        $checkEmailResults = executeQuery($pdo, $checkEmailQuery, $checkEmailParams);

        if ($checkEmailResults) {
            echo json_encode(['status' => 'error', 'message' => 'El email ya existe']);
            exit();
        }
        $token = generateToken();

        $register_query = "INSERT INTO unverified_users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?, genre = ?, sexual_preference = ?, password = ?, email = ?, created_at = ?, token = ?";
        $register_params = [$name, $surname, $alias, $birth_date, $location, $genre, $sexual_preference, $hashed_password, $email, $timestamp, $token];

        try {
            executeQuery($pdo, $register_query, $register_params);
            
            echo json_encode(['status' => 'success', 'message' => '¡Registro realizado con éxito!', 'name' => $name]);
            logEvent("new_register", "El usuario " . $email . " se ha registrado", $email);

            sendVerificationEmail($email, $token, $pdo);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => '¡Error! Algo salió mal'. $e->getMessage()]);
        }
        exit();
    } else { //si alguno de los campos esta vacio dar el estilo del error
        // esta comprobación de errores sólo se realizaría en caso de no ejecutarse el javascript
        $message = "Uno o más campos están vacíos";
        if (empty($_POST['name'])) {
            $errorName = "form__field--error";
        }
        if (empty($_POST['surname'])) {
            $errorSurname = "form__field--error";
        }
        if (empty($_POST['username'])) {
            $errorAlias = "form__field--error";
        }
        if (empty($_POST['birthdate'])) {
            $errorBirthdate = "form__field--error";
        }
        if (empty($_POST['location'])) {
            $errorLocation = "form__field--error";
        }
        if (empty($_POST['email'])) {
            $errorEmail = "form__field--error";
        }
        if (empty($_POST['password'])) {
            $errorPassword = "form__field--error";
        }
        if (empty($_POST['password2'])) {
            $errorPassword2 = "form__field--error";
        }

        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['verify']) && isset($_GET['token'])) {
        $user_id = $_GET['verify'];
        $user_token = $_GET['token'];
        $query = "SELECT * FROM unverified_users WHERE id = :user_id AND token = :user_token";
        $params = [':user_id' => $user_id, ':user_token' => $user_token];
        $results = executeQuery($pdo, $query, $params);

        if ($results) {
            $user = $results[0];
            $query = "INSERT INTO users (name, surname, alias, birth_date, location, genre, sexual_preference, password, email, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$user['name'], $user['surname'], $user['alias'], $user['birth_date'], $user['location'], $user['genre'], $user['sexual_preference'], $user['password'], $user['email'], $user['created_at']];
            executeQuery($pdo, $query, $params);

            $query = "DELETE FROM unverified_users WHERE id = :user_id";
            $params = [':user_id' => $user_id];
            executeQuery($pdo, $query, $params);

            logEvent("verify_success", "El usuario " . $user['email'] . " ha verificado su cuenta", $user['email']);

            $_SESSION["verified"] = true;
        }
        header("Location: login.php");
    }
    else {
        //mostrar un mensaje de error
        echo "     
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showMessage('wrongEmail', 'El enlace de verificación no es válido.');
                })
            </script>";
    }
}

function generateToken() {
    return bin2hex(random_bytes(16));
}

function sendVerificationEmail($email, $pdo) {
    $queryGetId = "SELECT id, token FROM unverified_users WHERE email = :email";
    $paramsGetId = [':email' => $email];
    $userId = executeQuery($pdo, $queryGetId, $paramsGetId);

    $mailHeader = 'From: verify@tinder4.ieti.site' . "\r\n" .
    'Reply-To: verify@tinder4.ieti.site' .  "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    $subject = "EasyDates - Verificación de cuenta";
    $message = "Para verificar su cuenta, por favor haga clic en el siguiente enlace: http://tinder4.ieti.site/register.php?verify=" . $userId[0]['id'] . "&token=" . $userId[0]['token'];
    mail($email, $subject, $message, $mailHeader);
}
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

<body>

    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>

    <main>
        <div>
            <div class="container-cabecera">
                <h1 class="fuente-titulos">Nuevo registro: </h1>
            </div>

            <form class="profile-form" method="POST" action="register.php" id="registerForm">
                <div class="login-alert"><?php echo $message; ?></div>
                <div class="input-group">
                    <label for="name" id="name-label">Nombre:</label>
                    <input type="text" id="name" name="name" placeholder="Nombre" class="<?php echo $errorName; ?>">
                </div>
                <div class="input-group">
                    <label for="surname" id="surname-label">Apellidos:</label>
                    <input type="text" id="surname" name="surname" placeholder="Apellidos" class="<?php echo $errorSurname; ?>">
                </div>
                <div class="input-group">
                    <label for="username" id="username-label">Alias:</label>
                    <input type="text" id="username" name="username" placeholder="Alias" class="<?php echo $errorAlias; ?>">
                </div>
                <div class="input-group">
                    <label for="email" id="email-label">Correo:</label>
                    <input type="text" id="email" name="email" placeholder="correo@ieti.site" class="<?php echo $errorEmail; ?>">
                </div>
                <div class="input-group">
                    <label for="genre" id="genre-label">Genero:</label>
                    <select id="genre" name="genre" class="<?php echo $errorGenre; ?>">
                        <option value="home">Masculino</option>
                        <option value="dona">Femenino</option>
                        <option value="no binari">No binario</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="sexual_preference" id="sexual_preference-label">Preferencia sexual:</label>
                    <select id="sexual_preference" name="sexual_preference" class="<?php echo $errorSexualPreference; ?>">
                        <option value="heterosexual">Heterosexual</option>
                        <option value="homosexual">Homosexual</option>
                        <option value="bisexual">Bisexual</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="birthdate" id="birthdate-label">Fecha de nacimiento:</label>
                    <input type="date" id="birthdate" name="birthdate" class="<?php echo $errorBirthdate; ?>">
                </div>
                <div class="input-group">
                    <label for="location" id="location-label">Ubicación:</label>
                    <input type="text" id="location" name="location" placeholder="Ubicación" class="<?php echo $errorLocation; ?>">
                    <span id="location-icon">📍</span>
                </div>
                <div id="map-container" style="display: none; position: absolute; z-index: 1000;">
                    <div id="map" style="height: 500px;"></div>
                </div>
                <div class="input-group">
                    <label for="password" id="password-label">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña" class="<?php echo $errorPassword; ?>">
                </div>
                <div class="input-group">
                    <label for="password2" id="password2-label">Repetir contraseña:</label>
                    <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña" class="<?php echo $errorPassword2; ?>">
                </div>
                <input type="submit" value="Registrar">

            </form>
        </div>

        </div>
        <div class="notification-container" id="notificationContainer"></div>
    </main>

    <footer>

    </footer>


    <script src="assets/js/register.js"></script>


</body>

</html>