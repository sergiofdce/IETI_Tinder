<?php
session_start();
if (!isset($_SESSION["user_id"])) {
      header("Location: login.php");
      exit();
}

include 'includes/functions.php';

// Registrar evento de visualización de la página
logEvent("page_view", "El usuario ha accedido a la página Discover", $_SESSION["email"]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="assets/css/styles.css">
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <title>EasyDates - Discover</title>
      <link rel="icon" type="image/png" href="assets/img/web/icon.ico">
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
                        <div id="filtro">
                              <!-- <button id="filtroButton">Filtrar</button> -->
                              <!-- From Uiverse.io by andrew-demchenk0 -->
                              <button class="filtroButton" id="filtroButton">
                                    <span>Filtros</span>
                              </button>
                        </div>
                  </div>
            </div>
      </header>

      <main>

            <?php

            // Database connection
            require_once 'config/db_connection.php';

            // Datos usuario logueado
            // --> Mantener datos en estas variables porque son pasadas al JS
            // ID de usuario
            // $user_id = 1; // --> Mantener datos en estas variables porque son pasadas al JS
            $user_id = $_SESSION['user_id'];

            // Email de usuario
            $query = "SELECT * FROM users WHERE id = :session_id";
            $params = [':session_id' => $user_id];
            $results = executeQuery($pdo, $query, $params);
            $user_email = $results[0]['email'];
            $user_genre = $results[0]['genre'];
            $user_preference = $results[0]['sexual_preference'];
            $user_location = $results[0]['location'];
            $user_birth_date = $results[0]['birth_date'];

            //$user_location tiene formato 'lat, long' y hay que separarlo en dos variables
            $user_lat = substr($user_location, 0, strpos($user_location, ','));
            $user_long = substr($user_location, strpos($user_location, ',') + 1);

            // Configurar rango de edad (18-100 años)
            $min_age = 18;
            $max_age = 100;

            // Radio de búsqueda en kilómetros
            $radius = 50;

            // Convertir el radio en diferencias de latitud/longitud aproximadas
            // 1 grado de latitud = ~111km
            // 1 grado de longitud = ~111km * cos(latitud)
            $lat_range = $radius / 111;
            $long_range = $radius / (111 * cos(deg2rad($user_lat)));

            // Calcular los límites de latitud y longitud
            $min_lat = $user_lat - $lat_range;
            $max_lat = $user_lat + $lat_range;
            $min_long = $user_long - $long_range;
            $max_long = $user_long + $long_range;

            // Algoritmo

            // Seleccionar todos los usuarios que no sean el usuario logueado
            // y que no haya interactuado antes

            $query = "
                  SELECT DISTINCT u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email,
                  GROUP_CONCAT(ui.path ORDER BY ui.id ASC) AS images,
                  (
                  6371 * acos(
                        cos(radians(:lat)) * cos(radians(SUBSTRING_INDEX(u.location, ',', 1))) *
                        cos(radians(SUBSTRING_INDEX(u.location, ',', -1)) - radians(:long)) +
                        sin(radians(:lat)) * sin(radians(SUBSTRING_INDEX(u.location, ',', 1)))
                  )
                  ) AS distance,
                  (SELECT COUNT(*) 
                  FROM matches m 
                  WHERE m.sender_id = u.id AND m.status = 'pending' OR m.status = 'accepted') -
                  (SELECT COUNT(*) 
                  FROM matches m 
                  WHERE m.sender_id = u.id AND m.status = 'rejected') AS interaction_score
                  FROM users u
                  LEFT JOIN user_images ui ON u.id = ui.user_id
                  WHERE u.id != :session_id
                  AND u.privileges != 'admin'
                  AND u.status = 'verified'
                  AND (
                  (:user_genre = 'home' AND :user_preference = 'heterosexual' AND u.genre = 'dona')
                  OR (:user_genre = 'home' AND :user_preference = 'homosexual' AND u.genre = 'home')
                  OR (:user_genre = 'dona' AND :user_preference = 'heterosexual' AND u.genre = 'home')
                  OR (:user_genre = 'dona' AND :user_preference = 'homosexual' AND u.genre = 'dona')
                  OR (:user_preference = 'bisexual')
                  OR (:user_genre = 'no binari')
                  )
                  -- Filtro de edad
                  AND TIMESTAMPDIFF(YEAR, u.birth_date, CURDATE()) BETWEEN :minAge AND :maxAge
                  -- Filtro por radio de 50 km
                  AND (
                        6371 * acos(
                              cos(radians(:lat)) * cos(radians(SUBSTRING_INDEX(u.location, ',', 1))) *
                              cos(radians(SUBSTRING_INDEX(u.location, ',', -1)) - radians(:long)) +
                              sin(radians(:lat)) * sin(radians(SUBSTRING_INDEX(u.location, ',', 1)))
                        )
                  ) <= 50
                  AND NOT EXISTS (
                  SELECT 1 
                  FROM matches m_accepted
                  WHERE m_accepted.status = 'accepted'
                  AND (
                  (m_accepted.sender_id = :session_id AND m_accepted.receiver_id = u.id)
                  OR 
                  (m_accepted.sender_id = u.id AND m_accepted.receiver_id = :session_id)
                  )
                  )
                  AND NOT EXISTS (
                  SELECT 1 
                  FROM matches m_rejected
                  WHERE m_rejected.status = 'rejected'
                  AND (
                  (m_rejected.sender_id = :session_id AND m_rejected.receiver_id = u.id)
                  OR 
                  (m_rejected.sender_id = u.id AND m_rejected.receiver_id = :session_id AND NOT EXISTS (
                        SELECT 1 
                        FROM matches m_pending
                        WHERE m_pending.sender_id = :session_id
                        AND m_pending.receiver_id = u.id
                  ))
                  )
                  )
                  AND (
                  NOT EXISTS (
                  SELECT 1 
                  FROM matches m1
                  WHERE m1.sender_id = :session_id 
                  AND m1.receiver_id = u.id
                  )
                  OR EXISTS (
                  SELECT 1 
                  FROM matches m2
                  WHERE m2.sender_id = u.id 
                  AND m2.receiver_id = :session_id
                  AND m2.status = 'pending'
                  )
                  )
                  GROUP BY u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email
                  ORDER BY ((interaction_score * 0.7) + (1 - (distance / MAX(distance)) * 0.3)) DESC;
                  ";



            // Le pasamos el user_id de la cookie como parámetro
            $params =   [
                  'session_id' => $user_id,
                  'user_genre' => $user_genre,
                  'user_preference' => $user_preference,
                  'lat' => $user_lat,
                  'long' => $user_long,
                  'minAge' => $min_age,
                  'maxAge' => $max_age,
                  'minLat' => $min_lat,
                  'maxLat' => $max_lat,
                  'minLon' => $min_long,
                  'maxLon' => $max_long
            ];



            // Array de usuarios
            $results = executeQuery($pdo, $query, $params);

            // Convertimos el array PHP a formato JSON para pasar al JS
            $profiles_json = json_encode($results);

            ?>

            <div class="container">
                  <div id="discover-profiles">
                        <div class="profile-container">
                              <div id="profile-info">
                                    <p id="user-name">Username <span id="user-age">Age</span></p>
                              </div>
                        </div>
                  </div>
                  <div id="actions">
                        <button id="nope" class="discover-actionButton nope-button">
                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18"></path>
                                    <path d="m6 6 12 12"></path>
                              </svg>
                        </button>
                        <button id="like" class="discover-actionButton like-button">
                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                              </svg>
                        </button>
                  </div>
            </div>

            <div id="showMatch">
                  <p id="matchTitle">It's a Match!</p>
                  <button id="closeMatch1">Ir al Chat</button>
                  <button id="closeMatch2">Seguir Descubriendo</button>
            </div>

            <script>
                  let profiles = <?php echo $profiles_json; ?>;
                  const userId = <?php echo $user_id; ?>;
                  const userEmail = <?php echo json_encode($user_email); ?>;
                  const userLocation = <?php echo json_encode($user_location); ?>;
            </script>

            <script src="assets/js/discover_Slider.js"></script>

      </main>


      <footer>
            <nav>
                  <ul>
                        <li id="navFocus">
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