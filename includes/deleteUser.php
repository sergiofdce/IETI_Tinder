<?php
session_start();
require_once '../config/db_connection.php';
include 'functions.php';

if ($_POST['delete'] == 'Eliminar') {
    $user_id = $_POST['user_id'];

    $query = "UPDATE users SET status = 'deleted' WHERE id = :user_id";
    $params = [':user_id' => $user_id];
    executeQuery($pdo, $query, $params);
    logEvent("page_view", "Un usuario ha eliminado su cuenta", $user_id);

    echo json_encode(['status' => 'success', 'message' => 'Cuenta eliminada correctamente']);
    session_destroy();
}else{
    echo json_encode(['status' => 'error', 'message' => 'La palabra '.$_POST['delete'].' no es correcta']);
}