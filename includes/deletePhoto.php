<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'No estás autenticado.']);
    exit();
}

require_once 'functions.php';
require_once '../config/db_connection.php';

$user_id = $_SESSION["user_id"];

// Leer la ruta de la foto desde la solicitud
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['path'])) {
    echo json_encode(['success' => false, 'message' => 'Ruta de la foto no proporcionada.']);
    exit();
}

$photoPath = $data['path'];

try {
    // Verificar cuántas fotos tiene el usuario
    $stmt = $pdo->prepare("SELECT COUNT(*) AS photo_count FROM user_images WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $photoCount = $stmt->fetch(PDO::FETCH_ASSOC)['photo_count'];

    if ($photoCount <= 1) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu única foto.']);
        exit();
    }

    // Verificar que la foto pertenece al usuario
    $stmt = $pdo->prepare("SELECT id FROM user_images WHERE path = :path AND user_id = :user_id");
    $stmt->execute([':path' => $photoPath, ':user_id' => $user_id]);
    $photo = $stmt->fetch();

    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'No se encontró la foto o no pertenece al usuario.']);
        exit();
    }

    // Eliminar la foto de la base de datos
    $stmt = $pdo->prepare("DELETE FROM user_images WHERE path = :path AND user_id = :user_id");
    $stmt->execute([':path' => $photoPath, ':user_id' => $user_id]);

    // Eliminar el archivo físico del servidor
    if (file_exists("../" . $photoPath)) {
        unlink("../" . $photoPath);
    }

    echo json_encode(['success' => true, 'message' => 'Foto eliminada correctamente.']);
    logEvent("profile_photoDelete", "El usuario ha eliminado la foto: " . basename($photoPath), $_SESSION["email"]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la foto: ' . $e->getMessage()]);
}
?>
