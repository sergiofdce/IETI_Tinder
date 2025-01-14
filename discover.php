<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Document</title>
      <link rel="stylesheet" href="assets/css/styles.css">
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

      <header>
            <h1>Easydates</h1>
      </header>

      <main>

            <?php

            // Database connection
            require_once 'config/db_connection.php';

            // Datos usuario logueado
            // --> Mantener datos en estas variables porque son pasadas al JS
            // ID de usuario
            $user_id = 2; // --> Mantener datos en estas variables porque son pasadas al JS

            // Email de usuario
            $query = "SELECT email FROM users WHERE id = :session_id";
            $params = [':session_id' => $user_id];
            $results = executeQuery($pdo, $query, $params);
            $user_email = $results[0]['email'];

            // Algoritmo

            // Seleccionar todos los usuarios que no sean el usuario logueado
            // y que no haya interactuado antes
            // falta comprobar que sea misma preferencia sexual
            $query = "
                  SELECT DISTINCT u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email,
                  GROUP_CONCAT(ui.path ORDER BY ui.id ASC) AS images
                  FROM users u
                  LEFT JOIN user_images ui ON u.id = ui.user_id
                  WHERE u.id != :session_id
                  -- Exclude users that have an accepted match with session_id (check both directions)
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
                  AND (
                  -- Include users that haven't had any interaction with session_id
                  NOT EXISTS (
                        SELECT 1 
                        FROM matches m1
                        WHERE m1.sender_id = :session_id 
                        AND m1.receiver_id = u.id
                  )
                  -- OR include users that have given a pending like to session_id
                  OR EXISTS (
                        SELECT 1 
                        FROM matches m2
                        WHERE m2.sender_id = u.id 
                        AND m2.receiver_id = :session_id
                        AND m2.status = 'pending'
                  )
                  )
                  GROUP BY u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email
                  ";



            // Le pasamos el user_id de la cookie como parámetro

            $params = [':session_id' => $user_id];



            // Array de usuarios
            $results = executeQuery($pdo, $query, $params);

            // Convertimos el array PHP a formato JSON para pasar al JS
            $profiles_json = json_encode($results);

            ?>

            <div class="container">
                  <div id="discover-profiles">
                        <div class="profile-container">
                              <img id="profile-image" src="" alt="Profile Image">
                              <div id="profile-info">
                                    <p id="user-name">Username <span id="user-age">Age</span></p>
                              </div>
                        </div>
                  </div>
                  <div id="actions">
                        <button id="nope">Nope</button>
                        <button id="like">Like</button>
                  </div>
            </div>

            <div id="showMatch" style="
                                    display: none;
                                    position: fixed;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%);
                                    border: 1px solid black;
                                    padding: 10px;
                                    background-color: white;
                                    width: 300px;
                                    height: auto;
                                    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
                                    z-index: 1000;
                                    text-align: center;
                                    ">
                  <p style="margin-bottom: 20px; font-size: 50px;">It's a Match!</p>
                  <button id="closeMatch1" style="
                        margin-right: 10px;
                        padding: 10px 20px;
                        background-color: #007BFF;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                  ">Conversa</button>
                  <button id="closeMatch2" style="
                        padding: 10px 20px;
                        background-color: #6c757d;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                  ">Discover</button>
            </div>

            <script>
                  const profiles = <?php echo $profiles_json; ?>;
                  const userId = <?php echo $user_id; ?>;
                  const userEmail = <?php echo json_encode($user_email); ?>;
            </script>

            <script src="assets/js/discover_Slider.js"></script>

      </main>


      <footer>
            <nav>
                  <ul>
                        <li><a href="discover.php">Descobrir</a></li>
                        <li><a href="messages.php">Missatges</a></li>
                        <li><a href="profile.php">Perfil</a></li>
                  </ul>
            </nav>
      </footer>

</body>

</html>