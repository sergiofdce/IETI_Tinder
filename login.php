<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@600&family=Roboto:wght@300&display=swap" rel="stylesheet">
    <title>Iniciar Sesión</title>
</head>

<body id="login">
    <div class="container">
        <main>
            <div id="logo">
                <h1 class="funnel-display"><a href="index.php">LOGO PLACEHOLDER</a></h1>
                <p class="roboto">Descripción del sitio</p>
            </div>
            <div id="login" class="roboto">
                <form action="login.php" method="post">
                    <label for="email" class="funnel-display">Email</label>
                    <input type="email" name="email" placeholder="usuario@ieti.site">
                    <label for="password" class="funnel-display">Contraseña</label>
                    <input type="password" name="password" placeholder="******">
                    <input type="submit" value="Iniciar Sesión">
                </form>
            </div>
        </main>
    </div>
</body>

</html>