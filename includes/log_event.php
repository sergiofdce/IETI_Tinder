<?php
include 'functions.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['eventType'], $data['description'], $data['userEmail'])) {
    logEvent($data['eventType'], $data['description'], $data['userEmail']);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
?>
