<?php
session_start();
require_once '../config/db_connection.php';
include 'functions.php';

// Update the user's password
if (isset($_POST['password']) && isset($_POST['password2'])) {
    $newPassword = $_POST['password'];
    $newPassword2 = $_POST['password2'];
    $user_id = $_POST['user_id'];

    if ($newPassword === $newPassword2) {
        $hashedPassword = hash('sha512', $newPassword);

        $query = "UPDATE users SET password = :password WHERE id = :userId";
        $params = [':password' => $hashedPassword, ':userId' => $user_id];
        executeQuery($pdo, $query, $params);
        logEvent("page_view", "Un usuario ha cambiado su contraseña", $user_id);

        echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
    }
}