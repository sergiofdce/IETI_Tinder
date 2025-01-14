<?php
session_start();
//persistencia de sesiones, si no hay usuario logueado, se redirige a login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>

    <header>
        <h1>Easydates</h1>
    </header>

    <main>
        <div class="container">
            <h1>Perfil</h1>
            <img src="https://via.placeholder.com/150" alt="Foto de perfil">
            <form>
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>">
                <label for="surname">Apellidos:</label>
                <input type="text" id="surname" name="surname" value="<?php echo $surname; ?>">
                <label for="username">Alias:</label>
                <input type="username" id="username" name="username" value="<?php echo $username; ?>">
                <label for="birthday">Fecha de nacimiento:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo $birthdate; ?>">
                <label for="location">Ubicación:</label>
                <input type="text" id="location" name="location" value="<?php echo $location; ?>">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password">
                <label for="password">Repetir contraseña:</label>
                <input type="password2" id="password2" name="password2">
                <input type="submit" value="Modificar">
            </form>
            <p><a href="logout.php">Desconectar</a></p>
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