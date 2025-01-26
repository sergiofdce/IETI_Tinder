<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

// Database connection
require_once 'config/db_connection.php';

include 'includes/functions.php';
logEvent("page_view", "El usuario ha accedido a la página Messages", $_SESSION["email"]);

// Endpoint para cargar mensajes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'load') {
    if (isset($_POST['senderId']) && isset($_POST['receiverId'])) {
        $senderId = $_POST['senderId'];
        $receiverId = $_POST['receiverId'];

        $query = "SELECT m.message, m.sent_at, 
                         CASE 
                             WHEN m.sender_id = ? THEN 'sender' 
                             ELSE 'receiver' 
                         END AS role,
                         (SELECT ui.path FROM user_images ui WHERE ui.user_id = m.sender_id LIMIT 1) AS 'foto'
                  FROM messages m
                  WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                  ORDER BY m.sent_at ASC";
        $params = [$senderId, $senderId, $receiverId, $receiverId, $senderId];
        $messages = executeQuery($pdo, $query, $params);

        echo json_encode($messages);
    } else {
        echo json_encode(['error' => true, 'message' => 'Invalid parameters']);
    }
    exit;
}


// Endpoint para enviar mensajes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    if (isset($_POST['senderId']) && isset($_POST['receiverId']) && isset($_POST['message'])) {
        $senderId = $_POST['senderId'];
        $receiverId = $_POST['receiverId'];
        $message = $_POST['message'];

        $query = "INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?)";
        $params = [$senderId, $receiverId, $message, date('Y-m-d H:i:s')];
        $result = executeQuery($pdo, $query, $params);

        if ($result) {
            echo json_encode(['error' => false, 'message' => 'Mensaje enviado']);
        } else {
            echo json_encode(['error' => true, 'message' => 'Error al enviar el mensaje']);
        }
    } else {
        echo json_encode(['error' => true, 'message' => 'Parámetros inválidos']);
    }
    exit;
}

// Endpoint para obtener información del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'getUserInfo') {
    if (isset($_POST['userId'])) {
        $userId = $_POST['userId'];

        $query = "SELECT u.name AS nombre, u.birth_date as fecha_nacimiento, ui.path AS foto
                  FROM users u
                  JOIN user_images ui ON u.id = ui.user_id
                  WHERE u.id = ?";
        $params = [$userId];
        $result = executeQuery($pdo, $query, $params);

        if ($result) {
            echo json_encode($result[0]);
        } else {
            echo json_encode(['error' => true, 'message' => 'Usuario no encontrado']);
        }
    } else {
        echo json_encode(['error' => true, 'message' => 'Parámetros inválidos']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>EasyDates - Mensajes</title>
    <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Mensajes JS-->
    <script src="assets/js/messages.js"></script>
</head>


<body>
    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>
    <main id="main-content">
        <div id="messages-matches">
            <h1 class="title-messenger fuente-titulos">Mis Matches</h1>

            <div id="matches-container">
                <?php
                if (isset($_SESSION["user_id"])) {
                    $id_usuario = $_SESSION["user_id"];
                    $query = "SELECT DISTINCT u.id, u.name, u.surname, ui.path as 'foto'
                            FROM users u
                            JOIN user_images ui ON u.id = ui.user_id
                            JOIN matches r ON (r.sender_id = ? AND r.receiver_id = u.id) OR (r.receiver_id = ? AND r.sender_id = u.id)
                            WHERE r.status = 'accepted';";
                    $params = [$id_usuario, $id_usuario];
                    $resultat = executeQuery($pdo, $query, $params);

                    if (!$resultat || count($resultat) === 0) {
                        echo "<p class='messages-no-content'>Hay gente esperando para hablar contigo, devuelve el like y chatea.</p>";
                    } else {
                        $displayedUsers = [];
                        foreach ($resultat as $row) {
                            if (!in_array($row['id'], $displayedUsers)) {
                                $displayedUsers[] = $row['id'];
                                echo "<a href='#' onclick='loadUserMessages(" . $id_usuario . ", " . $row['id'] . ")' class='match-item' style='text-decoration: none;'>";
                                echo "<div class='contenedor-foto-match'>";
                                $foto_path = $row['foto'];
                                $base_url = 'assets/img/seeder/'; //////////////////////////////////////////////////////
                                if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                                    echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario'>";
                                } else {
                                    echo "<img src='default_photo.jpg' alt='Foto por defecto'>";
                                }
                                echo "</div>";
                                echo "<div class='match-name'>";
                                echo "<div>" . $row['name'] . "</div>";
                                echo "<div>" . $row['surname'] . "</div>";
                                echo "</div>";
                                echo "</a>";
                            }
                        }
                    }
                }
                ?>
            </div>

        </div>


        <!-- Sección de conversaciones -->
        <div id="messages-conversaciones">
            <h1 class="title-messenger fuente-titulos">Mensajes</h1>
            <div id="conversaciones-container">
                <?php
                if (isset($_SESSION["user_id"])) {
                    $id_usuario = $_SESSION["user_id"];
                    $query = "SELECT u.id, u.name, u.surname, ui.path AS 'foto', m.message, m.sent_at AS 'fechaMensaje'
                        FROM users u
                        JOIN user_images ui ON u.id = ui.user_id
                        JOIN messages m ON (m.sender_id = ? AND m.receiver_id = u.id) 
                            OR (m.receiver_id = ? AND m.sender_id = u.id)
                        WHERE m.message_id IN (
                            SELECT MAX(m.message_id)
                            FROM messages m
                            WHERE (m.sender_id = ? AND m.receiver_id = u.id) 
                                OR (m.receiver_id = ? AND m.sender_id = u.id)
                            GROUP BY IF(m.sender_id = ?, m.receiver_id, m.sender_id)
                        )
                        ORDER BY m.sent_at DESC;";
                    $params = [$id_usuario, $id_usuario, $id_usuario, $id_usuario, $id_usuario];
                    $resultat = executeQuery($pdo, $query, $params);

                    if (!$resultat || count($resultat) === 0) {
                        echo "<p class='messages-no-content'>No tienes ninguna conversación aún, descubre gente nueva y haz match.</p>";
                    } else {
                        $displayedUsers = [];
                        foreach ($resultat as $row) {
                            if (!in_array($row['id'], $displayedUsers)) {
                                $displayedUsers[] = $row['id'];
                                echo "<a href='#' onclick='loadUserMessages(" . $id_usuario . ", " . $row['id'] . ")'>";
                                echo "<div class='conversation-item'>";
                                echo "<div class='contenedor-foto-conversation'>";
                                $foto_path = $row['foto'];
                                $base_url = 'assets/img/seeder/';
                                if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                                    echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario'>";
                                } else {
                                    echo "<img src='default_photo.jpg' alt='Foto por defecto'>";
                                }
                                echo "</div>";
                                echo "<div class='conversation-details'>";
                                echo "<div class='conversation-name'>";
                                echo $row['name'] . " " . $row['surname'];
                                echo "</div>";
                                echo "<div class='last-message'>";
                                echo $row['message'];
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</a>";
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
        </div>
        </div>
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
                <li id="navFocus">
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

</body>

</html>