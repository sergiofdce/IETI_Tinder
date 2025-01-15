<?php
session_start();
$_SESSION["user_id"] = 1; // Reemplaza con el ID del usuario actual

// Verificar si el usuario está logueado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$conn = mysqli_connect('localhost', 'admin', 'admin', 'tinder');

if (!$conn) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener los datos del usuario
$query = "SELECT id, name, surname, alias, birth_date, location, (SELECT path FROM user_images WHERE user_id = ?) AS photo FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Usuario no encontrado.";
    exit();
}


// Lógica de actualización si es un request Ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $name = $_POST['name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $username = $_POST['username'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $location = $_POST['location'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Verificar que las contraseñas coincidan solo si ambas están llenas
    if ($password || $password2) { // Solo verifica si uno de los campos de contraseñas tiene valor
        if ($password !== $password2) {
            echo json_encode(['status' => 'warning', 'message' => 'Las contraseñas no coinciden']);
            exit();
        }

        // Hashear la nueva contraseña si las contraseñas coinciden
        $password = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $name, $surname, $username, $birthdate, $location, $password, $user_id);
    } else {
        // Si no hay contraseñas, no las modificamos
        $query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $name, $surname, $username, $birthdate, $location, $user_id);
    }

    if ($stmt->execute()) {
        // Obtener los datos actualizados del usuario para devolverlos en la respuesta
        $stmt->close();

        // Obtener la foto del usuario
        $query = "SELECT name, surname, alias, birth_date, location, (SELECT path FROM user_images WHERE user_id = ?) AS photo FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $photo_path = $user['photo'];
        $photo_filename = basename($photo_path);
        $user['photo'] = 'assets/img/seeder/' . $photo_filename;///////////////////////////////////////////////////////

        echo json_encode(['status' => 'success', 'message' => 'Perfil actualizado correctamente', 'data' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el perfil']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="/assets/js/script.js"></script> 
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>

    <style>
        :root {
            --darkred-color: #800F2F;
            --red-color: #CE2D4F;
            --background-color: #e2dbdb;
            --pink-color: #E7A7F1;
            --blue-color: #8EA4D2;
            --darkblue-color: #2F418F;
            --text-color: black;
        }

        .profile-page {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .container-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .profile-title {
            width: 45%;
            font-size: 25px;
            margin-top: 2px;
            margin-bottom: 8px;
        }

        .profile-image {
    width: 150px; /* Tamaño fijo de ancho */
    height: 150px; /* Tamaño fijo de alto */
    border-radius: 50%; /* Esto hará que la imagen sea circular */
    object-fit: cover; /* Esto asegura que la imagen se recorte adecuadamente si es necesario */
}

        .container-profile {
            text-align: left;
            flex-grow: 1;
        }

        .container-profile p {
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .profile-form {
    display: flex;
    flex-direction: column;
    margin-top: 20px;
}

.profile-form .form-group {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    align-items: center; /* Alinea verticalmente las etiquetas y los inputs */
}

.profile-form label {
    width: 50%; /* Ajusta el tamaño de las etiquetas */
    text-align: left;
    font-weight: bold;
}

.profile-form input {
    width: 45%; /* Ajusta el tamaño de los inputs */
    padding: 8px;
    font-size: 12px;
    color: white;
    background-image: linear-gradient(to left, var(--blue-color), var(--red-color));
    border: 0;
    border-radius: 5px;
    margin-bottom: 8px;
    box-sizing: border-box;
}

.profile-form input::placeholder {
    color: rgb(210, 210, 210);
}


        /* Estilo original del botón de submit */
        .profile-form input[type="submit"] {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            color: white;
            background-image: linear-gradient(to top left, var(--darkblue-color), var(--darkred-color));
            border: 0;
            border-radius: 5px;
            margin-bottom: 8px;
            box-sizing: border-box;
            margin-top: 20px;
            transition: background-color 0.3s ease, transform 0.2s ease;  /* Transiciones suaves */
        }

        /* Efecto hover para el botón de submit */
        .profile-form input[type="submit"]:hover {
            background-image: linear-gradient(to top left, var(--blue-color), var(--red-color));  /* Cambiar el fondo */
            transform: translateY(-2px);  /* Efecto de levantamiento */
        }

        /* Efecto active para el botón de submit */
        .profile-form input[type="submit"]:active {
            background-image: linear-gradient(to top left, var(--blue-color), var(--darkblue-color));  /* Cambiar el fondo */
            transform: translateY(0);  /* Vuelve a la posición original */
        }


        .edit-fotos a {
            display: inline-block;  /* Asegura que el enlace sea un bloque en línea, como un botón */
            padding: 8px; /* Espaciado interno para hacerlo más "botonizado" */
            background-image: linear-gradient(to left, var(--darkblue-color), var(--darkred-color));
            color: white;  /* Color de texto */
            font-size: 14px;  /* Tamaño de fuente adecuado */
            text-align: center;  /* Centrar el texto */
            text-decoration: none;  /* Eliminar el subrayado predeterminado */
            border-radius: 5px;  /* Bordes redondeados */
            border: none;  /* Sin borde visible */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);  /* Sombra sutil */
            transition: background-color 0.3s ease, transform 0.2s ease;  /* Transiciones suaves */
        }

        .edit-fotos a:hover {
            background-image: linear-gradient(to left, var(--blue-color), var(--red-color));
            transform: translateY(-2px);  /* Efecto de levantamiento */
        }

        .edit-fotos a:active {
            background-image: linear-gradient(to left, var(--blue-color), var(--darkblue-color));
            transform: translateY(0);  /* Vuelve a la posición original */
        }

        .re-pie {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            
        }

    </style>
</head>

<body class="profile-page">

    <header>
        <h1>Easydates</h1>
    </header>

    <main>
        <div class="container-profile">
            <div class="container-cabecera">
                <h1 class="profile-title"><?php echo htmlspecialchars($user['name']); ?></h1>
                <?php
                // Ruta base para las imágenes
                $base_url = 'assets/img/seeder/'; ///////////////////////////////////////////////////////
                // Suponiendo que las imágenes están en la carpeta 'assets/img/'

                // Verificar si el usuario tiene una foto de perfil
                $photo_path = !empty($user['photo']) ? $user['photo'] : 'default.png'; // Si no tiene foto, usamos la imagen por defecto

                // Verificar si la imagen existe en el servidor
                $full_photo_path = $base_url . basename($photo_path); // Obtenemos la ruta completa de la imagen

                // Si la imagen no existe, usamos la imagen predeterminada
                if (!file_exists($full_photo_path)) {
                    $full_photo_path = $base_url . 'default.png';
                }

                // Mostrar la imagen con una clase para controlar el tamaño
                echo "<img src='$full_photo_path' alt='Foto de perfil' class='profile-image'>";
                ?>

                
                
            </div>
            <form class="profile-form" method="POST" action="profile.php" id="profileForm">
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label for="surname">Apellidos:</label>
                    <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" placeholder="Apellidos">
                </div>
                <div class="form-group">
                    <label for="username">Alias:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['alias']); ?>" placeholder="Alias">
                </div>
                <div class="form-group">
                    <label for="birthdate">Fecha de nacimiento:</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo $user['birth_date']; ?>">
                </div>
                <div class="form-group">
                    <label for="location">Ubicación:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" placeholder="Ubicación" onblur="getCoordinates(this.value)">

                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña">
                </div>
                <div class="form-group">
                    <label for="password2">Repetir contraseña:</label>
                    <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña">
                </div>
               <input type="submit" value="Modificar">
            </form>
            <div class="re-pie">
                <p class="edit-fotos"><a href="">Editar fotos</a></p>
                <p class="logout"><a href="logaut.php">Cerrar sessión</a></p>
            </div>
            
        </div>
        <div class="notification-container" id="notificationContainer"></div>
    </main>

    <footer>
        <nav>
            <ul>
                <li><a href="discover.php">Descubrir</a></li>
                <li><a href="messages.php">Mensajes</a></li>
                <li><a href="profile.php">Perfil</a></li>
            </ul>
        </nav>
    </footer>
    

    <script>


//funcion para mensajes

function typeMessenger(type) {
    const container = document.getElementById("notificationContainer");

    // Crear un nuevo elemento de mensaje
    const notification = document.createElement("div");
    notification.classList.add("messenger");

    // Determinar el estilo y texto del mensaje(añadir los necesarios y poner stilos en el css)
    switch (type) {
       
        case 'error':
            notification.classList.add("divNotiError");
            notification.innerText = "¡Error! Algo salió mal";
            break;
        case 'like':
            notification.classList.add("divNotiLike");
            notification.innerText = "¡Te han dado un like!";
            break;
        case 'nope':
            notification.classList.add("divNotiNope");
            notification.innerText = "Lo siento, no es una coincidencia.";
            break;
        case 'success': // Corregido: Eliminado el 's' extra
            notification.classList.add("divNotiSuccess");
            notification.innerText = "¡Cambio realizado con éxito!";
            break;
        case 'warning':
            notification.classList.add("divNotiWarning");
            notification.innerText = "¡Advertencia! Algo podría no estar bien.";
            console.log(notification.innerText); // Mover el console.log aquí para que se ejecute
            break;
        default:
            notification.classList.add("divNotiOther");
            notification.innerText = "Notificación sin tipo específico.";
            break;
    }

    // Añadir el mensaje al contenedor
    container.appendChild(notification);

    // Eliminar el mensaje después de 6 segundos
    setTimeout(() => {
        notification.remove();
    }, 6000);

    // Si hay más de 3 mensajes, eliminar el más antiguo
    if (container.children.length > 3) {
        container.firstChild.remove();
    }
}


       
   // Manejo del formulario con Ajax
document.getElementById("profileForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevenir el comportamiento por defecto del formulario

    const password = document.getElementById("password").value;
    const password2 = document.getElementById("password2").value;

    // Verificar que las contraseñas coincidan solo si se modifican
    if ((password && password !== password2) || (password2 && password2 !== password)) {
        document.getElementById("password").classList.add("input-error");
        document.getElementById("password2").classList.add("input-error");
        typeMessenger('warning', 'Las contraseñas no coinciden');
        return;
    }

    const formData = new FormData(this);
    formData.append('ajax', true); // Asegúrate de que el formulario es enviado como una solicitud AJAX

    // Enviar datos por Ajax
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "profile.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                typeMessenger('success', response.message); // Mostrar mensaje de éxito

                // Actualizar los datos mostrados en la página
                document.querySelector('#name').value = response.data.name;
                document.querySelector('#surname').value = response.data.surname;
                document.querySelector('#username').value = response.data.alias;
                document.querySelector('#birthdate').value = response.data.birth_date;
                document.querySelector('#location').value = response.data.location;

                // Actualizar la imagen si ha cambiado
                if (response.data.photo) {
                    document.querySelector('img/seeder').src = response.data.photo;//////////////////////////////////////////
                }
                document.querySelector('.profile-title').innerText = response.data.name;
            } else {
                typeMessenger('error', response.message); // Mostrar mensaje de error si algo falla
            }
        } else {
            typeMessenger('error', 'Error al actualizar el perfil');
        }
    };
    xhr.send(formData);
});

// Obtener coordenadas de Google Maps usando Geocoding API
function getCoordinates(location) {
    const geocoder = new google.maps.Geocoder();

    geocoder.geocode({ 'address': location }, function(results, status) {
        if (status === 'OK') {
            const lat = results[0].geometry.location.lat();
            const lng = results[0].geometry.location.lng();
            
            // Llenamos el campo location con las coordenadas (lat, lng)
            document.getElementById("location").value = `POINT(${lat} ${lng})`;
        } else {
            alert("No se pudo geolocalizar el lugar: " + status);
        }
    });
}




    </script>




</body>

</html>
