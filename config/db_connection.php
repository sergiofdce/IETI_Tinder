<?php
// Configuración de la base de datos
$host = '127.0.0.1';
$dbname = 'tinder';
$username = 'admin';
$password = '123';

try {
      // Crear conexión PDO
      $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
      // Configurar atributos para el manejo de errores
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
      // Manejar errores de conexión
      die('Error en la conexión: ' . $e->getMessage());
}

?>

<?php

// Subir .json a la BBDD
function cargarJson($pdo, $jsonFile) {
      // Eliminar registros existentes en las tablas
      try {
            $pdo->beginTransaction();
            $pdo->exec('DELETE FROM users');
            $pdo->exec('DELETE FROM user_images');
            $pdo->exec('DELETE FROM matches');
            $pdo->exec('DELETE FROM messages');
            $pdo->commit();
      } catch (PDOException $e) {
            $pdo->rollBack();
            die('Error al limpiar las tablas: ' . $e->getMessage());
      }

      // Leer el contenido del archivo JSON
      $json = file_get_contents($jsonFile);
      $data = json_decode($json, true);

      // Preparar la consulta para insertar en la tabla `users`
      $stmtUser = $pdo->prepare('
        INSERT INTO users (
            name, surname, alias, birth_date, location, 
            genre, sexual_preference, password, email, created_at
        ) VALUES (
            :name, :surname, :alias, :birth_date, :location, 
            :genre, :sexual_preference, :password, :email, :created_at
        )
    ');

      // Preparar la consulta para insertar en la tabla `fotos_usuarios`
      $stmtPhoto = $pdo->prepare('
        INSERT INTO user_images (
            user_id, path, upload_date
        ) VALUES (
            :user_id, :path, NOW()
        )
    ');

      // Iterar sobre cada entrada del JSON
      foreach ($data as $row) {
            try {
                  $pdo->beginTransaction(); // Iniciar transacción para cada usuario

                  // Crear el valor para el campo `location` como string (ej: "latitude, longitude")
                  $location = $row['location']['latitude'] . ', ' . $row['location']['longitude'];

                  // Vincular los valores del usuario
                  $stmtUser->bindValue(':name', $row['name']);
                  $stmtUser->bindValue(':surname', $row['surname']);
                  $stmtUser->bindValue(':alias', $row['alias']);
                  $stmtUser->bindValue(':birth_date', $row['birth_date']);
                  $stmtUser->bindValue(':location', $location);
                  $stmtUser->bindValue(':genre', $row['genre']);
                  $stmtUser->bindValue(':sexual_preference', $row['sexual_preference']);
                  $stmtUser->bindValue(':password', $row['password']);
                  $stmtUser->bindValue(':email', $row['email']);
                  $stmtUser->bindValue(':created_at', $row['created_at']);

                  // Ejecutar la inserción del usuario
                  $stmtUser->execute();

                  // Obtener el ID del usuario recién insertado
                  $userId = $pdo->lastInsertId();

                  // Insertar las imágenes asociadas
                  foreach ($row['profile_images'] as $imagePath) {
                        $stmtPhoto->bindValue(':user_id', $userId);
                        $stmtPhoto->bindValue(':path', $imagePath);
                        $stmtPhoto->execute();
                  }

                  $pdo->commit(); // Confirmar la transacción
            } catch (PDOException $e) {
                  $pdo->rollBack(); // Revertir si algo falla
                  die('Error al insertar datos: ' . $e->getMessage());
            }
      }

      echo "Datos cargados exitosamente.";
}

// Hacer consultas BBDD
function executeQuery($pdo, $query, $params = []) {
      try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params); // Pasar los parámetros a la ejecución de la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
            return json_encode(['error' => true, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
      }
}

// Subir .json a la BBDD
//cargarJson($pdo, 'assets/data/fake_profiles.json');


?>