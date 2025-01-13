<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    
    <!-- Enlace a Google Fonts para la fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="assets/css/styles.css">

 

</head>


<body>
    <header>
        <h1>Easydates</h1>
    </header>
    <main>
        <div class="container-messenger">
            <h1 class="title-messenger">MIS MACHES</h1>
            <div class="container-matches">
            <?php

                //$_POST["id_usuario"] = 1; // Reemplaza con el ID del usuario actual
                if (isset($_POST["id_usuario"])) {
                    $id_usuario = $_POST["id_usuario"];
                    $conn = mysqli_connect('localhost', 'admin', 'admin', 'tinder');
                    $consulta = "SELECT u.nombre, u.apellidos, f.path as 'foto'
                                FROM users u
                                JOIN fotos_Usuarios f ON u.id_autoinc = f.id_usuario
                                JOIN solicitudes s ON (s.id_usuario1 = ? AND s.id_usuario2 = u.id_autoinc) OR (s.id_usuario2 = ? AND s.id_usuario1 = u.id_autoinc)
                                LEFT JOIN mensajes m ON (m.id_origen = ? AND m.id_destino = u.id_autoinc) OR (m.id_destino = ? AND m.id_origen = u.id_autoinc)
                                WHERE s.estado = 'aceptado'
                                AND m.id_Mensajes IS NULL;";
                    $stmt = $conn->prepare($consulta);
                    $stmt->bind_param("iiii", $id_usuario, $id_usuario, $id_usuario, $id_usuario);
                    $stmt->execute();
                    $resultat = $stmt->get_result();
                    if (!$resultat) {
                        die('Consulta inválida: ' . mysqli_error($conn));
                    }
                    while ($row = $resultat->fetch_assoc()) {
                        echo "<a href='#' class='match-item' style='text-decoration: none;'>";
                        echo "<div class='contenedor-foto-match'>";
                        $foto_path = $row['foto'];
                        $base_url = 'images/';
                        if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                            echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario' style='width: 100%; height: 100%; object-fit: cover;'>";
                        } else {
                            echo "<img src='default_photo.jpg' alt='Foto por defecto' style='width: 100%; height: 100%; object-fit: cover;'>";
                        }
                        echo "</div>";
                        echo "<div class='match-name'>";
                        echo $row['nombre'] . " " . $row['apellidos'];
                        echo "</div>";
                        echo "</a>";
                    }
                    mysqli_close($conn);
                }
                ?>

            </div>

            <!-- Sección de conversaciones -->
            <h1 class="title-messenger">MENSAJES</h1>
            <div class="container-conversaciones">
                <?php
                if (isset($_POST["id_usuario"])) {
                    $id_usuario = $_POST["id_usuario"];
                    $conn = mysqli_connect('localhost', 'admin', 'admin', 'tinder');
                    $consulta = "SELECT u.nombre, u.apellidos, f.path AS 'foto', m.mensaje, m.fechaMensaje
                                FROM users u
                                JOIN fotos_Usuarios f ON u.id_autoinc = f.id_usuario
                                JOIN mensajes m ON (m.id_origen = ? AND m.id_destino = u.id_autoinc) 
                                    OR (m.id_destino = ? AND m.id_origen = u.id_autoinc)
                                WHERE m.id_Mensajes IN (
                                    SELECT MAX(m.id_Mensajes)
                                    FROM mensajes m
                                    WHERE (m.id_origen = ? AND m.id_destino = u.id_autoinc) 
                                        OR (m.id_destino = ? AND m.id_origen = u.id_autoinc)
                                    GROUP BY IF(m.id_origen = ?, m.id_destino, m.id_origen)
                                )
                                ORDER BY m.fechaMensaje DESC;";
                    $stmt = $conn->prepare($consulta);
                    $stmt->bind_param("iiiii", $id_usuario, $id_usuario, $id_usuario, $id_usuario, $id_usuario);
                    $stmt->execute();
                    $resultat = $stmt->get_result();
                    if (!$resultat) {
                        die('Consulta inválida: ' . mysqli_error($conn));
                    }
                    while ($row = mysqli_fetch_assoc($resultat)) {
                        echo "<a href='#' class='conversation-item' style='text-decoration: none;'>";
                        echo "<div class='conversation-item'>";
                        
                        // Contenedor de la foto
                        echo "<div class='contenedor-foto-conversation'>";
                        $foto_path = $row['foto'];
                        $base_url = 'images/';
                        if (!empty($foto_path) && file_exists($base_url . basename($foto_path))) {
                            echo "<img src='" . $base_url . basename($foto_path) . "' alt='Foto del usuario' style='width: 100%; height: 100%; object-fit: cover;'>";
                        } else {
                            echo "<img src='default_photo.jpg' alt='Foto por defecto' style='width: 100%; height: 100%; object-fit: cover;'>";
                        }
                        echo "</div>"; 

                        // Contenedor del nombre y mensaje
                        
                        echo "<div class='conversation-details'>";
                        echo "<div class='conversation-name'>";
                        echo $row['nombre'] . " " . $row['apellidos'];
                        echo "</div>";
                        echo "<div class='last-message'>";
                        echo $row['mensaje'];
                        echo "</div>";
                        echo "</div>"; 
                        
                        echo "</div>"; 
                        echo "</a>";
                    }
                    mysqli_close($conn);
                }
                ?>


            </div>
        </div>
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
