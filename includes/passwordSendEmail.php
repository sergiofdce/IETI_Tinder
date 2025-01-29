<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../assets/modules/phpmail/vendor/autoload.php';
//Create an instance; passing `true` enables exceptions


session_start();

require_once '../config/db_connection.php';
include 'functions.php';



date_default_timezone_set('Europe/Madrid');

//variables para las clases error
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['email'])) {
        $userEmail = $_POST['email'];
        logEvent("page_view", "Un usuario ha solicitado un email de recuperación de contraseña", $userEmail);

        // comprobar que el email exista
        $checkEmailQuery = "SELECT * FROM users WHERE email = :email AND status = 'verified'";
        $checkEmailParams = [':email' => $userEmail];
        $checkEmailResults = executeQuery($pdo, $checkEmailQuery, $checkEmailParams);

        if (!$checkEmailResults) {
            echo json_encode(['status' => 'error', 'message' => 'El email no es un usuario registrado']);
            exit();
        }
        $token = generateToken();

        //actualizar la base de datos
        $updateTokenQuery = "UPDATE users SET token = ? WHERE email = ? AND status = 'verified'";
        $updateTokenParams = [$token, $userEmail];
        executeQuery($pdo, $updateTokenQuery, $updateTokenParams);

        if (sendVerificationEmail($userEmail, $token, $pdo)) {
            logEvent("page_view", "Un usuario ha recibido un email de recuperación de contraseña", $userEmail);
            echo json_encode(['status' => 'success', 'message' => 'Se ha enviado un email de verificación']);
            exit();
        }else {
            logEvent("error", "Se ha fallado el envío del email de recuperación de contraseña", $userEmail);
            echo json_encode(['status' => 'error', 'message' => 'Error al enviar el email de verificación']);
            exit();
        }
       
    } else { //si alguno de los campos esta vacio dar el estilo del error
        // esta comprobación de errores sólo se realizaría en caso de no ejecutarse el javascript
        $message = "Campo vacío";
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }
}


function generateToken()
{
    return bin2hex(random_bytes(16));
}

function sendVerificationEmail($userEmail, $token, $pdo)
{
    $queryGetId = "SELECT id, token FROM users WHERE email = :email and token = :token AND status = 'verified'";
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
        $mail->Subject = 'EasyDates - Recuperación de contraseña';
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
                                        img {
                                            width: 100%;
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
                                    <p class="message">Para recuperar su contraseña, por favor haga clic en el botón de abajo.</p>
                                    <a href=" http://tinder4.ieti.site/register.php?verify=' . $userId[0]['id'] . '&token=' . $userId[0]['token'] . '" class="button">Verificar Cuenta</a>
                                    <p class="footer">Si no solicitaste este correo, puedes ignorarlo.</p>
                                </div>
                            </body>
                            </html>
                            ';        $mail->AltBody = "Para recuperar su contraseña, por favor haga clic en el siguiente enlace: http://tinder4.ieti.site/register.php?verify=" . $userId[0]['id'] . "&token=" . $userId[0]['token'];

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}