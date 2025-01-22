<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'No estás autenticado.']);
    exit();
}

require_once 'config/db_connection.php';

$user_id = $_SESSION["user_id"];

try {
    $stmt = $pdo->prepare("SELECT path FROM user_images WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'photos' => $photos]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener las fotos: ' . $e->getMessage()]);
}
