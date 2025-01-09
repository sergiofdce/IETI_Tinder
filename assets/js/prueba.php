<!DOCTYPE html>
<html lang="en">
<head>
    <!--solo para probar los mensajes luego se borra todo el archibo-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes Acumulativos</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
        }

        button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        /* Botones de tipo */
        .btn-error { background-color: #ff4d4d; color: white; }
        .btn-like { background-color: #4caf50; color: white; }
        .btn-nope { background-color: #ff9800; color: white; }
        .btn-other { background-color: #2196f3; color: white; }


        /*codigo para notificaciones*/

        /* Contenedor de notificaciones */
        .notification-container {
            position: fixed;
            text-align: center;
            width: 20%;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column-reverse; /* Los mensajes nuevos se agregan abajo */
            gap: 10px;
            max-width: 300px;
        }

        /* Notificación */
        .messenger {
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            opacity: 1;
            animation: fadeOut 6s forwards;
        }

        /* Estilos de notificación */
        .divNotiError { background-color: #e53935; }
        .divNotiLike { background-color: #4caf50; }
        .divNotiNope { background-color: #fb8c00; }
        .divNotiOther { background-color: #1e88e5; }

        /* Animación */
        @keyframes fadeOut {
            0%, 83% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn-error" onclick="typeMessenger('error')">Mostrar Error</button>
        <button class="btn-like" onclick="typeMessenger('like')">Mostrar Like</button>
        <button class="btn-nope" onclick="typeMessenger('nope')">Mostrar Nope</button>
        <button class="btn-other" onclick="typeMessenger('other')">Mostrar Otro</button>
    </div>

    <!-- Contenedor de notificaciones -->
    <div id="notificationContainer" class="notification-container"></div>

    <script src="css_Messenger.js"></script>
</body>
</html>
