<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'assets/modules/phpmail/vendor/autoload.php';
//Create an instance; passing `true` enables exceptions
session_start();

require_once 'config/db_connection.php';
include 'includes/functions.php';


logEvent("page_view", "Un usuario ha accedido a la página Register", "new_user");

date_default_timezone_set('Europe/Madrid');

//variables para los errores
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !empty($_POST['name']) &&
        !empty($_POST['surname']) &&
        !empty($_POST['username']) &&
        !empty($_POST['birthdate']) &&
        !empty($_POST['location']) &&
        !empty($_POST['genre']) &&
        !empty($_POST['sexual_preference']) &&
        !empty($_POST['email']) &&
        !empty($_POST['password']) &&
        !empty($_POST['password2']) &&
        isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK &&
        isset($_FILES['media2']) && $_FILES['media2']['error'] === UPLOAD_ERR_OK
    ) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $alias = $_POST['username'];
        $birth_date = $_POST['birthdate'];
        $location = $_POST['location'];
        $genre = $_POST['genre'];
        $sexual_preference = $_POST['sexual_preference'];
        $userEmail = $_POST['email'];
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
        $checkEmailParams = [':email' => $userEmail];
        $checkEmailResults = executeQuery($pdo, $checkEmailQuery, $checkEmailParams);
        $token = generateToken();
        $previouslyDeleted = false;

        if ($checkEmailResults) {
            //comprobar si es un usuario previamente borrado
            if ($checkEmailResults[0]['status'] === 'deleted') {
                $previouslyDeleted = true;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'El email ya existe']);
                exit();
            }
        }

        $register_query = "INSERT INTO users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?, genre = ?, sexual_preference = ?, password = ?, email = ?, created_at = ?, token = ?, status = 'unverified'";
        $register_params = [$name, $surname, $alias, $birth_date, $location, $genre, $sexual_preference, $hashed_password, $userEmail, $timestamp, $token];


        try {
            executeQuery($pdo, $register_query, $register_params);

            echo json_encode(['status' => 'success', 'message' => '¡Registro realizado con éxito!', 'name' => $name]);
            logEvent("new_register", "El usuario " . $userEmail . " se ha registrado", $userEmail);

            if (!sendVerificationEmail($userEmail, $token, $pdo)) {
                // echo json_encode(['status' => 'error', 'message' => 'Error al enviar el email de verificación']);
                exit();
            }

            $getIdQuery = "SELECT id FROM users WHERE email = :email AND status = 'unverified'";
            $getIdParams = [':email' => $userEmail];
            $getIdResults = executeQuery($pdo, $getIdQuery, $getIdParams);
            $_SESSION["user_id"] = $getIdResults[0]['id'];
            $_SESSION["email"] = $userEmail;

            uploadPhotos($_SESSION["user_id"], $pdo);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => '¡Error en el try catch!' . $e->getMessage()]);
            exit();
        }
        exit();
    } else { //si alguno de los campos esta vacio dar el estilo del error
        // esta comprobación de errores sólo se realizaría en caso de no ejecutarse el javascript
        $message = "Uno o más campos están vacíos";
        // echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['verify']) && isset($_GET['token'])) {
        $user_id = $_GET['verify'];
        $user_token = $_GET['token'];
        $query = "SELECT * FROM users WHERE id = :user_id AND token = :user_token";
        $params = [':user_id' => $user_id, ':user_token' => $user_token];
        $results = executeQuery($pdo, $query, $params);

        if ($results) {
            $user = $results[0];
            //actualizar la base de datos
            $query = "UPDATE users SET status = 'verified' WHERE id = :user_id AND token = :user_token";
            $params = [':user_id' => $user_id, ':user_token' => $user_token];
            executeQuery($pdo, $query, $params);

            logEvent("verify_success", "El usuario " . $user['email'] . " ha verificado su cuenta", $user['email']);

            $_SESSION["verified"] = true;
            header("Location: login.php");
        } else {
            //mostrar un mensaje de error
            echo "     
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showMessage('wrongEmail', 'El enlace de verificación no es válido.');
                    })
                </script>";
        }
    }
}

function generateToken()
{
    return bin2hex(random_bytes(16));
}

function sendVerificationEmail($userEmail, $token, $pdo)
{
    $queryGetId = "SELECT id, token FROM users WHERE email = :email and token = :token";
    $paramsGetId = [':email' => $userEmail, ':token' => $token];
    $userId = executeQuery($pdo, $queryGetId, $paramsGetId);

    $mail = new PHPMailer(true);

    try {
        //Server settings

        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'jpachonguerra.cf@iesesteveterradas.cat';                     //SMTP username
        $mail->Password   = 'osabyrjjarqgigjm';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet = "UTF-8";

        //Recipients
        $mail->setFrom('jpachonguerra.cf@iesesteveterradas.cat', 'Mailer');
        $mail->addAddress($userEmail);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'EasyDates - Creación de cuenta';
        $mail->Body    = '<html>
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Verifica tu cuenta - EasyDates</title>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        background-color: #E5E1E6;
                                        margin: 0;
                                        padding: 0;
                                    }
                                    .container {
                                        max-width: 600px;
                                        margin: 20px auto;
                                        background: #b1a6db;
                                        padding: 20px;
                                        border-radius: 10px;
                                        text-align: center;
                                    }
                                    .logo {
                                        font-size: 24px;
                                        font-weight: bold;
                                        color: #fff;
                                    }
                                    .message {
                                        color: #fff;
                                        font-size: 16px;
                                        margin: 20px 0;
                                    }
                                    div.container a.button {
                                        display: inline-block;
                                        background: #fff;
                                        color: #6A5B92;
                                        padding: 12px 20px;
                                        text-decoration: none;
                                        font-weight: bold;
                                        border-radius: 5px;
                                        margin-top: 10px;
                                    }
                                    .footer {
                                        margin-top: 20px;
                                        font-size: 12px;
                                        color: #fff;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <div class="logo"><img src="https://tinder4.ieti.site/assets/img/web/logo.png" alt="EasyDates" id="logo"></div>
                                    <p class="message">Gracias por registrarte en EasyDates. Por favor, verifica tu cuenta haciendo clic en el botón de abajo.</p>
                                    <a href=" http://tinder4.ieti.site/register.php?verify=' . $userId[0]['id'] . '&token=' . $userId[0]['token'] . '" class="button">Verificar Cuenta</a>
                                    <p class="footer">Si no solicitaste este correo, puedes ignorarlo.</p>
                                </div>
                            </body>
                            </html>
                            ';
        $mail->AltBody =  "Para verificar su cuenta, por favor haga clic en el siguiente enlace: http://tinder4.ieti.site/register.php?verify=" . $userId[0]['id'] . "&token=" . $userId[0]['token'];

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


function uploadPhotos($user_id, $pdo)
{
    // Validar si se recibieron archivos
    if (!empty($_FILES)) {
        foreach ($_FILES as $key => $file) {
            // Verificar si el archivo fue subido correctamente
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $file['tmp_name'];
                $fileName = $file['name'];
                $fileSize = $file['size'];
                $fileType = $file['type'];

                // Validar tipo de archivo (por ejemplo, solo imágenes)
                $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($fileType, $allowedMimeTypes)) {
                    // echo json_encode(['success' => false, 'message' => "Tipo de archivo no permitido para el archivo: $fileName."]);
                    exit();
                }

                // Definir la carpeta destino
                $uploadFolder = 'assets/img/seeder/';

                // Generar un nombre único para el archivo
                $newFileName = uniqid() . ".webp";
                $destPath = $uploadFolder . $newFileName;

                // Mover el archivo al directorio destino
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Guardar la ruta en la base de datos

                    $sql = "INSERT INTO user_images (user_id, path) VALUES (:user_id, :path)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':user_id' => $user_id,
                        ':path' => $destPath,
                    ]);

                    // Registrar evento
                    logEvent("profile_photoUpload", "El usuario ha subido la foto: " . $newFileName, $_SESSION["email"]);
                } else {
                    // echo json_encode(['success' => false, 'message' => "Error al mover el archivo: $fileName."]);
                    exit();
                }
            } else {
                // echo json_encode(['success' => false, 'message' => "Error al subir ficheros."]);
                exit();
            }
        }
    } else {
        // echo json_encode(['success' => false, 'message' => 'No se recibieron archivos.']);
        exit();
    }
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

<body id="register">

    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>

    <main>
        <div>
            <div class="container-cabecera">
                <h1 class="fuente-titulos">Nuevo registro: </h1>
            </div>

            <form class="profile-form" method="POST" action="register.php" id="registerForm" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="name" id="name-label">Nombre:</label>
                    <input type="text" id="name" name="name" placeholder="Nombre">
                </div>
                <div class="input-group">
                    <label for="surname" id="surname-label">Apellidos:</label>
                    <input type="text" id="surname" name="surname" placeholder="Apellidos">
                </div>
                <div class="input-group">
                    <label for="username" id="username-label">Alias:</label>
                    <input type="text" id="username" name="username" placeholder="Alias">
                </div>
                <div class="input-group">
                    <label for="email" id="email-label">Correo:</label>
                    <input type="text" id="email" name="email" placeholder="correo@ieti.site">
                </div>
                <div class="input-group">
                    <label for="media" id="media-label">Imagen 1:</label>
                    <input type="file" class="file-input" name="media" id="media" accept="image/*" />
                </div>
                <div class="input-group">
                    <label for="media2" id="media2-label">Imagen 2:</label>
                    <input type="file" class="file-input" name="media2" id="media2" accept="image/*" />
                </div>
                <div class="input-group">
                    <label for="genre" id="genre-label">Genero:</label>
                    <select id="genre" name="genre">
                        <option value="" disabled selected>Elegir uno</option>
                        <option value="home">Masculino</option>
                        <option value="dona">Femenino</option>
                        <option value="no binari">No binario</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="sexual_preference" id="sexual_preference-label">Preferencia sexual:</label>
                    <select id="sexual_preference" name="sexual_preference">
                        <option value="" disabled selected>Elegir uno</option>
                        <option value="heterosexual">Heterosexual</option>
                        <option value="homosexual">Homosexual</option>
                        <option value="bisexual">Bisexual</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="birthdate" id="birthdate-label">Fecha de nacimiento:</label>
                    <input type="date" id="birthdate" name="birthdate">
                </div>
                <div class="input-group">
                    <label for="location" id="location-label">Ubicación:</label>
                    <input type="text" disabled id="location" name="location" placeholder="Clica en la chincheta">
                    <span id="location-icon">📍</span>
                </div>
                <div id="map-container" style="display: none; position: absolute; z-index: 1000;">
                    <div id="map" style="height: 500px;"></div>
                </div>
                <div class="input-group">
                    <label for="password" id="password-label">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña">
                </div>
                <div class="input-group">
                    <label for="password2" id="password2-label">Repetir contraseña:</label>
                    <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña">
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