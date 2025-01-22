<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

$user_id = $_SESSION["user_id"];
require_once 'config/db_connection.php';
include 'includes/functions.php';
logEvent("page_view", "El usuario ha accedido a la página Profile", $_SESSION["email"]);

$query = "SELECT id, name, surname, alias, birth_date, location, 
                (SELECT path FROM user_images WHERE user_id = ? LIMIT 1) AS photo 
                FROM users WHERE id = ?";

$params = [$user_id, $user_id];
$user = executeQuery($pdo, $query, $params);

if (empty($user)) {
    echo "Usuario no encontrado.";
    exit();
}

$user = $user[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $alias = $_POST['username'];
    $birth_date = $_POST['birthdate'];
    $location = $_POST['location'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (!empty($password) && $password !== $password2) {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
        exit();
    }

    $update_query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?";
    $update_params = [$name, $surname, $alias, $birth_date, $location];

    if (!empty($password)) {
        $hashed_password = hash('sha512', $password);
        $update_query .= ", password = ?";
        $update_params[] = $hashed_password;
    }

    $update_query .= " WHERE id = ?";
    $update_params[] = $user_id;

    try {
        executeQuery($pdo, $update_query, $update_params);
        echo json_encode(['status' => 'success', 'message' => '¡Cambio realizado con éxito!', 'name' => $name]);
        logEvent("profile_update", "El usuario ha actualizado sus datos", $_SESSION["email"]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => '¡Error! Algo salió mal']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>EasyDates - Perfil</title>
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
                <h1 class="fuente-titulos"><?php echo htmlspecialchars($user['name']); ?></h1>
                <?php
                $base_url = 'assets/img/seeder/';
                $photo_path = !empty($user['photo']) ? $user['photo'] : 'default.png';
                $full_photo_path = $base_url . basename($photo_path);

                if (!file_exists($full_photo_path)) {
                    $full_photo_path = $base_url . 'default.png';
                }

                echo "<img src='$full_photo_path' alt='Foto de perfil' class='profile-image'>";
                ?>
            </div>

            <form class="profile-form" method="POST" action="profile.php" id="profileForm">
                <div class="input-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Nombre">
                </div>
                <div class="input-group">
                    <label for="surname">Apellidos:</label>
                    <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" placeholder="Apellidos">
                </div>
                <div class="input-group">
                    <label for="username">Alias:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['alias']); ?>" placeholder="Alias">
                </div>
                <div class="input-group">
                    <label for="birthdate">Fecha de nacimiento:</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo $user['birth_date']; ?>">
                </div>
                <div class="input-group">
                    <label for="location">Ubicación:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" placeholder="Ubicación">
                    <span id="location-icon">📍</span>
                </div>
                <div id="map-container" style="display: none; position: absolute; z-index: 1000;">
                    <div id="map" style="height: 500px;"></div>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña">
                </div>
                <div class="input-group">
                    <label for="password2">Repetir contraseña:</label>
                    <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña">
                </div>
                <input type="submit" value="Modificar">
            </form>


            <div class="re-pie">
                <p><a href="profilePictures.php">Editar fotos</a></p>
                <p><a href="logout.php">Cerrar sesión</a></p>
            </div>

        </div>
        <div class="notification-container" id="notificationContainer"></div>
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


    <script src="assets/js/profile.js"></script>


</body>

</html>