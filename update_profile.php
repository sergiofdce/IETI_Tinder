<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

$user_id = $_SESSION["user_id"];
$conn = mysqli_connect('localhost', 'admin', 'admin', 'tinder');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit();
}

// Obtener datos del formulario
$name = $_POST['name'] ?? '';
$surname = $_POST['surname'] ?? '';
$username = $_POST['username'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$location = $_POST['location'] ?? '';
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

// Verificar que las contraseñas coincidan si se ingresaron
if ($password && $password !== $password2) {
    echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
    exit();
}

// Si se proporcionó una nueva contraseña, hashearla
if ($password) {
    $password = password_hash($password, PASSWORD_BCRYPT);
    $query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $name, $surname, $username, $birthdate, $location, $password, $user_id);
} else {
    $query = "UPDATE users SET name = ?, surname = ?, alias = ?, birth_date = ?, location = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $name, $surname, $username, $birthdate, $location, $user_id);
}

if ($stmt->execute()) {
    // Obtener los datos actualizados del usuario para devolverlos en la respuesta
    $stmt->close();
    $query = "SELECT name, surname, alias, birth_date, location, (SELECT path FROM user_images WHERE user_id = ?) AS photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo json_encode(['status' => 'success', 'message' => 'Perfil actualizado correctamente', 'data' => $user]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el perfil']);
}

$stmt->close();
$conn->close();
?>
