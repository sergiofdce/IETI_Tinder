<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

include 'functions.php';
require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = :session_id";
$params = [':session_id' => $user_id];
$results = executeQuery($pdo, $query, $params);

$user_email = $results[0]['email'];
$user_genre = $results[0]['genre'];
$user_preference = $results[0]['sexual_preference'];
$user_location = $results[0]['location'];

$user_lat = substr($user_location, 0, strpos($user_location, ','));
$user_long = substr($user_location, strpos($user_location, ',') + 1);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data && isset($data['minAge'], $data['maxAge'], $data['minLat'], $data['maxLat'], $data['minLon'], $data['maxLon'], $data['radius'])) {

$query = "
            SELECT DISTINCT u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email,
            GROUP_CONCAT(ui.path ORDER BY ui.id ASC) AS images,
            (
                6371 * acos(
                    cos(radians(:lat)) * cos(radians(SUBSTRING_INDEX(u.location, ',', 1))) *
                    cos(radians(SUBSTRING_INDEX(u.location, ',', -1)) - radians(:long)) +
                    sin(radians(:lat)) * sin(radians(SUBSTRING_INDEX(u.location, ',', 1)))
                )
            ) AS distance
            FROM users u
            LEFT JOIN user_images ui ON u.id = ui.user_id
            WHERE u.id != :session_id
            AND u.privileges != 'admin'
            AND (
                (:user_genre = 'home' AND :user_preference = 'heterosexual' AND u.genre = 'dona')
                OR (:user_genre = 'home' AND :user_preference = 'homosexual' AND u.genre = 'home')
                OR (:user_genre = 'dona' AND :user_preference = 'heterosexual' AND u.genre = 'home')
                OR (:user_genre = 'dona' AND :user_preference = 'homosexual' AND u.genre = 'dona')
                OR (:user_preference = 'bisexual')
                OR (:user_genre = 'no binari')
            )
            AND TIMESTAMPDIFF(YEAR, u.birth_date, CURDATE()) BETWEEN :minAge AND :maxAge
            AND (
                6371 * acos(
                    cos(radians(:lat)) * cos(radians(SUBSTRING_INDEX(u.location, ',', 1))) *
                    cos(radians(SUBSTRING_INDEX(u.location, ',', -1)) - radians(:long)) +
                    sin(radians(:lat)) * sin(radians(SUBSTRING_INDEX(u.location, ',', 1)))
                ) <= :radius
            )
            AND NOT EXISTS (
                SELECT 1 FROM matches m_accepted
                WHERE m_accepted.status = 'accepted'
                AND ((m_accepted.sender_id = :session_id AND m_accepted.receiver_id = u.id)
                    OR (m_accepted.sender_id = u.id AND m_accepted.receiver_id = :session_id))
            )
            AND NOT EXISTS (
                SELECT 1 FROM matches m_rejected
                WHERE m_rejected.status = 'rejected'
                AND ((m_rejected.sender_id = :session_id AND m_rejected.receiver_id = u.id)
                    OR (m_rejected.sender_id = u.id AND m_rejected.receiver_id = :session_id 
                        AND NOT EXISTS (
                            SELECT 1 FROM matches m_pending
                            WHERE m_pending.sender_id = :session_id
                            AND m_pending.receiver_id = u.id
                        )))
            )
            AND (
                NOT EXISTS (
                    SELECT 1 FROM matches m1
                    WHERE m1.sender_id = :session_id 
                    AND m1.receiver_id = u.id
                )
                OR EXISTS (
                    SELECT 1 FROM matches m2
                    WHERE m2.sender_id = u.id 
                    AND m2.receiver_id = :session_id
                    AND m2.status = 'pending'
                )
            )
            GROUP BY u.id, u.name, u.surname, u.alias, u.birth_date, u.location, u.genre, u.sexual_preference, u.email
            ORDER BY distance ASC";

    $params = [
            ':session_id' => $user_id,
            ':user_genre' => $user_genre,
            ':user_preference' => $user_preference,
            ':lat' => $user_lat,
            ':long' => $user_long,
            ':minAge' => (int)$data['minAge'],
            ':maxAge' => (int)$data['maxAge'],
            ':minLat' => (float)$data['minLat'],
            ':maxLat' => (float)$data['maxLat'],
            ':minLon' => (float)$data['minLon'],
            ':maxLon' => (float)$data['maxLon'],
            ':radius' => (float)$data['radius']
        ];

    $results = executeQuery($pdo, $query, $params);

    // Imprimir parámetros y resultados para depuración
    error_log(print_r($params, true));
    error_log(print_r($results, true));

    echo json_encode(['success' => true, 'users' => $results]);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos de filtro no válidos']);
}
?>
