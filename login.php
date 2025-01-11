<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = hash('sha512', $_POST["password"]);

        try {
            $hostname = "localhost";
            $dbname = "tinder";
            $username = "admin";
            $pw = "admin123";
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        } catch (PDOException $e) {
            echo "Error connectant a la BD: " . $e->getMessage() . "<br>\n";
            exit;
        }
        try {
            //preparar consulta y sanear los parámetros
            $query = $pdo->prepare("SELECT * FROM users WHERE mail = :mail AND password = :password");
            $query->bindParam(':mail', $email);
            $query->bindParam(':password', $password);
            $query->execute();
        } catch (PDOException $e) {
            echo "Error de SQL<br>\n";
            //comprobacion de errores
            $e = $query->errorInfo();
            if ($e[0] != '00000') {
                echo "\nPDO::errorInfo():\n";
                die("Error accedint a dades: " . $e[2]);
            }
        }
        //si login correcto:
        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            //guardamos su ID en una sesión
            $_SESSION["user"] = $row["id"];
            //responder con success al ajax
            $message = 'success';

        //si el login no es correcto buscamos si existe el usuario para mostrar password incorrecto     
        } else {
            try {
                //preparar consulta y sanear los parámetros
                $query = $pdo->prepare("SELECT * FROM users WHERE mail = :mail");
                $query->bindParam(':mail', $_POST["email"]);
                $query->execute();
            } catch (PDOException $e) {
                echo "Error de SQL<br>\n";
                //comprobacion de errores
                $e = $query->errorInfo();
                if ($e[0] != '00000') {
                    echo "\nPDO::errorInfo():\n";
                    die("Error accedint a dades: " . $e[2]);
                }
            }
            if ($query->rowCount() > 0) {
                $message = "incorrect password";
            } else {
                $message = "incorrect user";
            }
        }
    } else {
        $message = "empty post";
    }
}
echo $message;
