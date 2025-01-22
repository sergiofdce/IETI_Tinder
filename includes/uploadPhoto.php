<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'No estás autenticado.']);
    exit();
}

include 'functions.php';
require_once '../config/db_connection.php';

$user_id = $_SESSION["user_id"];

// Validar si se recibió un archivo
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = $_FILES['photo']['name'];
    $fileSize = $_FILES['photo']['size'];
    $fileType = $_FILES['photo']['type'];

    // Validar tipo de archivo (por ejemplo, solo imágenes)
    $allowedMimeTypes = ['image/jpg','image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido.']);
        exit();
    }

    // Definir la carpeta destino
    $uploadFolder = 'assets/img/seeder/';

    // Generar un nombre único para el archivo
    $newFileName = uniqid().".webp";
    $destPath = $uploadFolder . $newFileName;

    // Mover el archivo al directorio destino
    if (move_uploaded_file($fileTmpPath, "../" . $destPath)) {
        // Guardar la ruta en la base de datos
        $sql = "INSERT INTO user_images (user_id, path) VALUES (:user_id, :path)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':path' => $destPath,
        ]);

        echo json_encode(['success' => true, 'message' => 'Foto subida correctamente.']);

        logEvent("profile_update", "El usuario ha subido la foto: " . $newFileName, $_SESSION["email"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo.']);
}
?>