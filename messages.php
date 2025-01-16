<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

// Database connection
require_once 'config/db_connection.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enlace a Google Fonts para la fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>EasyDates - Mensajes</title>
    <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
</head>


<body>
    <header>
        <img src="assets/img/web/logo.png" alt="EasyDates" id="logo">
    </header>
    <main>
        <h1 class="title-messenger">MATCHES</h1>
        <div class="container-matches">
            <?php
            if (isset($_SESSION["user_id"])) {
                $id_usuario = $_SESSION["user_id"];
                $query = "SELECT u.name, u.surname, ui.path as 'foto'
                    FROM users u
                    JOIN user_images ui ON u.id = ui.user_id
                    JOIN matches r ON (r.sender_id = ? AND r.receiver_id = u.id) OR (r.receiver_id = ? AND r.sender_id = u.id)
                    LEFT JOIN messages m ON (m.sender_id = ? AND m.receiver_id = u.id) OR (m.receiver_id = ? AND m.sender_id = u.id)
                    WHERE r.status = 'accepted'
                    AND m.message_id IS NULL;";
                $params = [$id_usuario, $id_usuario, $id_usuario, $id_usuario];
                $resultat = executeQuery($pdo, $query, $params);

                if (!$resultat || count($resultat) === 0) {
                    // Si no hay resultados, muestra un mensaje centrado
                    echo "<p class='no-results-message'>Hay gente esperando para hablar contigo, devuelve el like y chatea.</p>";
                } else {
                    foreach ($resultat as $row) {
                        echo "<a href='#' class='match-item' style='text-decoration: none;'>";
                        echo "<div class='contenedor-foto-match'>";
                        $foto_path = $row['foto'];
                        $base_url = 'assets/img/seeder/'; //////////////////////////////////////////////////////
                        if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                            echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario' style='width: 100%; height: 100%; object-fit: cover;'>";
                        } else {
                            echo "<img src='default_photo.jpg' alt='Foto por defecto' style='width: 100%; height: 100%; object-fit: cover;'>";
                        }
                        echo "</div>";
                        echo "<div class='match-name'>";
                        echo $row['name'] . " " . $row['surname'];
                        echo "</div>";
                        echo "</a>";
                    }
                }
            }
            ?>
        </div>


        <!-- Sección de conversaciones -->
        <h1 class="title-messenger">MENSAJES</h1>
        <div class="container-conversaciones">
            <?php
            if (isset($_SESSION["user_id"])) {
                $id_usuario = $_SESSION["user_id"];
                $query = "SELECT u.name, u.surname, ui.path AS 'foto', m.message, m.sent_at AS 'fechaMensaje'
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
                    // Si no hay resultados, muestra un mensaje centrado
                    echo "<p class='no-results-message'>No tienes niguna conversación aun, descubre gente nueva y haz mach.</p>";
                } else {
                    foreach ($resultat as $row) {
                        echo "<a href='#' class='conversation-item' style='text-decoration: none;'>";
                        echo "<div class='conversation-item'>";

                        // Contenedor de la foto
                        echo "<div class='contenedor-foto-conversation'>";
                        $foto_path = $row['foto'];
                        $base_url = 'assets/img/seeder/'; //////////////////////////////////////////////////////
                        if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                            echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario' style='width: 100%; height: 100%; object-fit: cover;'>";
                        } else {
                            echo "<img src='default_photo.jpg' alt='Foto por defecto' style='width: 100%; height: 100%; object-fit: cover;'>";
                        }
                        echo "</div>";

                        // Contenedor del nombre y mensaje
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
            ?>
        </div>


        </div>
        </div>
    </main>
    <footer>
        <nav>
            <ul>
                <li>
                    <a href="discover.php">
                        <img class="footer-icons" src="assets/img/web/search.png" alt="Logout">
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <img class="footer-icons" src="assets/img/web/message.png" alt="Logout">
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <img class="footer-icons" src="assets/img/web/user.png" alt="Logout">
                    </a>
                </li>
            </ul>
        </nav>
    </footer>

</body>

</html>