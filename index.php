<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" src="assets/js/index.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@600&family=Roboto:wght@300&display=swap" rel="stylesheet">
    <title>Iniciar Sesión</title>
</head>

<body id="login">
    <div class="flex-container">
        <main>
            <div id="logo">
                <h1 class="funnel-display">LOGO PLACEHOLDER</h1>
                <p class="roboto">Descripción del sitio</p>
            </div>
            <div id="login" class="roboto">
                <form action="login.php" method="post">
                    <label for="email" class="funnel-display">Email</label>
                    <input type="email" name="email" id="email" placeholder="usuario@ieti.site">
                    <label for="password" class="funnel-display">Contraseña</label>
                    <input type="password" name="password" id="password" placeholder="******">
                    <div id="message"></div>
                    <input type="submit" name="submit" id="submit" value="Iniciar Sesión">
                </form>
                <p><a href="#">¿Has olvidado la contraseña?</a></p>
                <p><a href="register.php">Crear nueva cuenta</a></p>
            </div>
        </main>
    </div>
</body>

</html>