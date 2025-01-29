<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

$user_id = $_SESSION["user_id"];
require_once 'config/db_connection.php';
include 'includes/functions.php';
logEvent("page_view", "El usuario ha accedido a la página Profile", $_SESSION["email"]);

$query = "SELECT id, name, surname, alias, birth_date, location, genre, sexual_preference,
                (SELECT GROUP_CONCAT(path ORDER BY id ASC) FROM user_images WHERE user_id = ?) AS photos 
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
    $genre = $_POST['genre'];
    $sexual_preference = $_POST['sexual_preference'];

    $update_query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?, genre = ?, sexual_preference = ? WHERE id = ?";
    $update_params = [$name, $surname, $alias, $birth_date, $location, $genre, $sexual_preference, $user_id];

    try {
        executeQuery($pdo, $update_query, $update_params);
        echo json_encode(['status' => 'success', 'message' => '¡Cambio realizado con éxito!', 'name' => $name]);
        logEvent("profile_update", "El usuario ha actualizado sus datos", $_SESSION["email"]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => '¡Error! Algo salió mal', 'error' => $e->getMessage()]);
        error_log("Error al actualizar perfil: " . $e->getMessage());
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
        <div class="dropdown">
            <button class="dropbtn">...</button>
            <div class="dropdown-content">
                <a href="logout.php">Cerrar sesión</a>
                <a href="#">Modificar contraseña</a>
                <a href="#">Eliminar cuenta</a>
            </div>
        </div>
    </header>

    <main>
        <div id="profile-container">
            <div class="tabs">
                <button id="buttonFocus" onclick="showTab('mostrar',this)">Mostrar</button>
                <button onclick="showTab('editar',this)">Editar</button>
            </div>
            <div id="mostrar" class="tab-content">
                <div class="profile-container profile-container-porfile">
                    <div class="slider">
                        <?php
                        $photos = explode(',', $user['photos']);
                        foreach ($photos as $index => $photo) {
                            $display = $index === 0 ? 'block' : 'none';
                            echo "<img class='profile-showImage' src='" . htmlspecialchars($photo) . "' alt='Profile Image' style='display: $display;'>";
                        }
                        ?>
                    </div>
                    <div id="profile-showInfo">
                        <div class="paginator">
                            <?php
                            foreach ($photos as $index => $photo) {
                                $active = $index === 0 ? 'active' : '';
                                echo "<span class='dot $active'></span>";
                            }
                            ?>
                        </div>
                        <p id="user-name"><?php echo htmlspecialchars($user['name']); ?> <span id="user-age"><?php echo date_diff(date_create($user['birth_date']), date_create('today'))->y; ?></span></p>

                    </div>
                </div>
            </div>
            <div id="editar" class="tab-content" style="display:none;">


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
                        <label for="genre">Género:</label>
                        <select id="genre" name="genre">
                            <option value="home" <?php echo $user['genre'] == 'home' ? 'selected' : ''; ?>>Hombre</option>
                            <option value="dona" <?php echo $user['genre'] == 'dona' ? 'selected' : ''; ?>>Mujer</option>
                            <option value="no binari" <?php echo $user['genre'] == 'no binari' ? 'selected' : ''; ?>>No binario</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="sexual_preference">Preferencia Sexual:</label>
                        <select id="sexual_preference" name="sexual_preference">
                            <option value="heterosexual" <?php echo $user['sexual_preference'] == 'heterosexual' ? 'selected' : ''; ?>>Heterosexual</option>
                            <option value="homosexual" <?php echo $user['sexual_preference'] == 'homosexual' ? 'selected' : ''; ?>>Homosexual</option>
                            <option value="bisexual" <?php echo $user['sexual_preference'] == 'bisexual' ? 'selected' : ''; ?>>Bisexual</option>
                        </select>
                    </div>
                    <input type="submit" value="Modificar">
                </form>

                <div class="re-pie">
                    <p><a href="profilePictures.php">Modificar mis fotos</a></p>
                </div>
            </div>
        </div>
        <div class="notification-container" id="notificationContainer"></div>
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
                <li>
                    <a href="messages.php">
                        Mensajes
                        <!-- <img class="footer-icons" src="assets/img/web/message.png" alt="Logout"> -->
                    </a>
                </li>
                <li id="navFocus">
                    <a href="profile.php">
                        Perfil
                        <!-- <img class="footer-icons" src="assets/img/web/user.png" alt="Logout"> -->
                    </a>
                </li>
            </ul>
        </nav>
    </footer>

    <script src="assets/js/profile.js"></script>

</body>

</html>