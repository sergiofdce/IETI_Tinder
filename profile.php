<?php
session_start();
$_SESSION["user_id"] = 1; // Reemplaza con el ID del usuario actual
// Verificar si el usuario está logueado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit();
}

// Obtener el ID del usuario desde la sesión

$user_id = $_SESSION["user_id"];

// Conectar a la base de datos
$conn = mysqli_connect('localhost', 'admin', 'admin', 'tinder');

// Verificar si la conexión fue exitosa
if (!$conn) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Consulta para obtener los datos del perfil
$query = "
    SELECT u.name, u.surname, u.alias, u.birth_date, u.location, ui.path AS photo
    FROM users u
    LEFT JOIN user_images ui ON u.id = ui.user_id
    WHERE u.id = ?
";

// Preparar la consulta
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // Bind del ID del usuario
$stmt->execute();

// Obtener los resultados
$result = $stmt->get_result();

// Verificar si se obtuvo algún dato
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("Error: No se encontraron los datos del usuario.");
}

// Cerrar la conexión
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="/assets/js/script.js"></script> 
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

        .container-profile img {
            width: 45%;
            height: 45%;
            border-radius: 50%;
            padding: 5px;
            object-fit: cover;
        }

        .container-profile {
            text-align: left;
            flex-grow: 1;
        }

        .container-profile p {
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .profile-form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .profile-form input {
            width: 100%;
            padding: 8px;
            font-size: 10px;
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

        .profile-form input[type="submit"] {
            width: 100%;
            padding: 8px;
            font-size: 10px;
            color: white;
            background-image: linear-gradient(to top left, var(--darkblue-color), var(--darkred-color));
            border: 0;
            border-radius: 5px;
            margin-bottom: 8px;
            box-sizing: border-box;
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
                <h1 class="profile-title">Perfil</h1>
                <?php
                // Si hay foto de perfil, mostrarla con una ruta accesible desde la web
                $photo_path = !empty($user['photo']) ? $user['photo'] : '/images/default_photo.jpg';
                echo "<img src='$photo_path' alt='Foto de perfil'>";
                ?>
            </div>
            <form class="profile-form" method="POST" action="update_profile.php" id="profileForm">

                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Nombre">
                <label for="surname">Apellidos:</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" placeholder="Apellidos">
                <label for="username">Alias:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['alias']); ?>" placeholder="Alias">
                <label for="birthdate">Fecha de nacimiento:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo $user['birth_date']; ?>">
                <label for="location">Ubicación:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" placeholder="Ubicación">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Nueva contraseña">
                <label for="password2">Repetir contraseña:</label>
                <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña">
                <input type="submit" value="Modificar">
            </form>
            <p><a href="logout.php">Desconectar</a></p>
        </div>
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
    <div class="notification-container" id="notificationContainer"></div>

    <script>
       
    // Manejo del formulario con Ajax
document.getElementById("profileForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevenir el comportamiento por defecto del formulario

    const password = document.getElementById("password").value;
    const password2 = document.getElementById("password2").value;

    // Verificar que las contraseñas coincidan
    if (password && password !== password2) {
        document.getElementById("password").classList.add("input-error");
        document.getElementById("password2").classList.add("input-error");
        typeMessenger('error', 'Las contraseñas no coinciden');
        return;
    }

    const formData = new FormData(this);

    // Enviar datos por Ajax
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_profile.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                // Mostrar el mensaje de éxito en verde
                typeMessenger('success', response.message); // Aquí muestra 'Cambio realizado con éxito'

                // Aquí puedes actualizar dinámicamente los datos mostrados en la página
                document.querySelector('#name').value = response.data.name;
                document.querySelector('#surname').value = response.data.surname;
                document.querySelector('#username').value = response.data.alias;
                document.querySelector('#birthdate').value = response.data.birth_date;
                document.querySelector('#location').value = response.data.location;
                // Actualizar la imagen si ha cambiado
                if (response.data.photo) {
                    document.querySelector('img').src = response.data.photo;
                }

            } else {
                typeMessenger('error', response.message); // Muestra el mensaje de error si algo falla
            }
        } else {
            typeMessenger('error', 'Error al actualizar el perfil');
        }
    };
    xhr.send(formData);
});


    </script>




</body>

</html>
