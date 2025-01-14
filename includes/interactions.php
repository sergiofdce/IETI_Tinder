<?php

require_once '../config/db_connection.php';

try {
    // Obtener los datos enviados en la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Datos de solicitud no válidos.');
    }

    $senderId = $data['senderID'];
    $receiverId = $data['receiverID'];
    $action = $data['action'];

      // Función para manejar la lógica de Like y Nope
      function handleMatch($senderId, $receiverId, $action, $pdo)
      {
            // Verificar si ya existe un registro en la tabla matches
            $query = "SELECT * FROM matches WHERE (sender_id = :senderId AND receiver_id = :receiverId) 
              OR (sender_id = :receiverId AND receiver_id = :senderId)";
            $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
            $existingMatch = executeQuery($pdo, $query, $params);

            if ($action == 'like') {
                  if (empty($existingMatch)) {
                        // Si no existe, crear un nuevo registro con estado "pending"
                        $query = "INSERT INTO matches (sender_id, receiver_id, status, request_date) 
                      VALUES (:senderId, :receiverId, 'pending', NOW())";
                        $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
                        executeQuery($pdo, $query, $params);
                        return json_encode(['message' => 'Like enviado y registrado como pendiente.']);
                  } elseif ($existingMatch[0]['status'] == 'pending') {
                        // Si ya existe y está en "pending", verificar si la otra persona ya dio like
                        $query = "SELECT * FROM matches WHERE (sender_id = :receiverId AND receiver_id = :senderId) 
                      AND status = 'pending'";
                        $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
                        $otherLike = executeQuery($pdo, $query, $params);

                        if (!empty($otherLike)) {
                              // Si la otra persona también tiene el like pendiente, convertirlo en "match"
                              $query = "UPDATE matches SET status = 'accepted', like_date = NOW() 
                          WHERE (sender_id = :senderId AND receiver_id = :receiverId) 
                          OR (sender_id = :receiverId AND receiver_id = :senderId)";
                              $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
                              executeQuery($pdo, $query, $params);
                              return json_encode(['message' => 'Match!']);
                        }
                  } elseif ($existingMatch[0]['status'] == 'rejected') {
                        // Si ya existe y está en "rejected", mantenerlo igual
                        return json_encode(['message' => 'La interacción está rechazada, no se puede actualizar.']);
                  }
            } elseif ($action == 'nope') {
                  if (empty($existingMatch)) {
                        // Si no existe, crear un nuevo registro con estado "rejected"
                        $query = "INSERT INTO matches (sender_id, receiver_id, status, request_date) 
                      VALUES (:senderId, :receiverId, 'rejected', NOW())";
                        $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
                        executeQuery($pdo, $query, $params);
                        return json_encode(['message' => 'Nope enviado y registrado como rechazado.']);
                  } elseif ($existingMatch[0]['status'] == 'pending') {
                        // Si ya existe y está en "pending", actualizar a "rejected"
                        $query = "UPDATE matches SET status = 'rejected' WHERE sender_id = :senderId AND receiver_id = :receiverId";
                        $params = [':senderId' => $senderId, ':receiverId' => $receiverId];
                        executeQuery($pdo, $query, $params);
                        return json_encode(['message' => 'La solicitud fue rechazada.']);
                  } elseif ($existingMatch[0]['status'] == 'rejected') {
                        // Si ya existe y está en "rejected", mantenerlo igual
                        return json_encode(['message' => 'La interacción ya está rechazada, no se puede actualizar.']);
                  }
            }
            return json_encode(['message' => 'Acción desconocida.']);
      }



    // Ejecutar la función según la acción
    echo handleMatch($senderId, $receiverId, $action, $pdo);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>
