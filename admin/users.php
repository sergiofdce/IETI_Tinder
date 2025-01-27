<?php
session_start();
include '../includes/functions.php';
require_once '../config/db_connection.php';

logEvent("page_view", "El usuario ha accedido a la página Admin - Usuarios", $_SESSION["email"]);

if (!isset($_SESSION["user_id"])) {
      header("HTTP/1.0 403 Forbidden");
      die("Error 403: Forbidden");
}

$user_id = $_SESSION["user_id"];
$query = "SELECT privileges FROM users WHERE id = :user_id";
$params = [':user_id' => $user_id];
$user = executeQuery($pdo, $query, $params);

if ($user[0]['privileges'] != "admin") {
      logEvent("page_view", "Problema de permisos, sin acceso a Admin - Usuarios", $_SESSION["email"]);
      header("HTTP/1.0 403 Forbidden");
      die("Error 403: Forbidden");
}

// Verificar si se solicita la vista detallada de un usuario
if (isset($_GET['id'])) {
      $user_id_detail = $_GET['id'];
      $query = "SELECT * FROM users WHERE id = :user_id";
      $params = [':user_id' => $user_id_detail];
      $user_detail = executeQuery($pdo, $query, $params);

      if (empty($user_detail)) {
            die("Usuario no encontrado.");
      }

      $user_detail = $user_detail[0];

      // Obtener imágenes del usuario
      $query_images = "SELECT * FROM user_images WHERE user_id = :user_id";
      $params_images = [':user_id' => $user_id_detail];
      $user_images = executeQuery($pdo, $query_images, $params_images);

      // Mostrar vista detallada del usuario
?>
      <!DOCTYPE html>
      <html lang="en">

      <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EasyDates - Detalles del Usuario</title>
            <link rel="stylesheet" href="../assets/css/styles.css">

      </head>

      <body id="admin-panel">
            <a href="/admin/users.php" class="card-link">Volver a la lista de usuarios</a>

            <h1>Detalles del Usuario: <?= htmlspecialchars($user_detail['name'] . ' ' . $user_detail['surname']) ?></h1>
            <p><strong>Alias:</strong> <?= htmlspecialchars($user_detail['alias']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_detail['email']) ?></p>
            <p><strong>Fecha de Nacimiento:</strong> <?= htmlspecialchars($user_detail['birth_date']) ?></p>
            <p><strong>Género:</strong> <?= htmlspecialchars($user_detail['genre']) ?></p>
            <p><strong>Preferencia Sexual:</strong> <?= htmlspecialchars($user_detail['sexual_preference']) ?></p>
            <p><strong>Ubicación:</strong> <?= htmlspecialchars($user_detail['location']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($user_detail['status']) ?></p>
            <p><strong>Privilegios:</strong> <?= htmlspecialchars($user_detail['privileges']) ?></p>
            <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars($user_detail['created_at']) ?></p>

            <h2>Imágenes del Usuario</h2>
            <?php if (!empty($user_images)): ?>
                  <div>
                        <?php foreach ($user_images as $image): ?>
                              <img src="../<?= htmlspecialchars($image['path']) ?>" alt="Imagen de usuario" style="max-width: 200px; margin: 10px;">
                        <?php endforeach; ?>
                  </div>
            <?php else: ?>
                  <p>No hay imágenes para este usuario.</p>
            <?php endif; ?>



      </body>

      </html>
<?php
      exit();
}

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

// Obtener el total de usuarios
$query_total = "SELECT COUNT(*) as total FROM users";
$total_users = executeQuery($pdo, $query_total);
$total_users = $total_users[0]['total'];
$total_pages = ceil($total_users / $limit);

// Obtener usuarios para la página actual
$query_users = "SELECT * FROM users LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($query_users);
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>EasyDates - Admin Panel - Usuarios</title>
      <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body id="admin-panel">
      <h1>Lista de Usuarios</h1>

      <a href="/admin/index.php" class="card-link">Volver a Inicio</a>

      <table class="admin-table" border="1">
            <thead>
                  <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Alias</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                  </tr>
            </thead>
            <tbody>
                  <?php foreach ($users as $user): ?>
                        <tr>
                              <td><?= htmlspecialchars($user['id']) ?></td>
                              <td><?= htmlspecialchars($user['name']) ?></td>
                              <td><?= htmlspecialchars($user['surname']) ?></td>
                              <td><?= htmlspecialchars($user['alias']) ?></td>
                              <td><?= htmlspecialchars($user['email']) ?></td>
                              <td><?= htmlspecialchars($user['status']) ?></td>
                              <td><a href="/admin/users.php?id=<?= htmlspecialchars($user['id']) ?>">Ver Detalles</a></td>
                        </tr>
                  <?php endforeach; ?>
            </tbody>
      </table>

      <!-- Reemplazar el paginador actual con el nuevo -->
      <div class="pagination">
            <?php if ($page > 1): ?>
                  <a href="/admin/users.php?page=<?= $page - 1 ?>" class="btn">Anterior</a>
            <?php endif; ?>

            <?php
            // Mostrar máximo 5 páginas alrededor de la página actual
            $start = max(1, min($page - 2, $total_pages - 4));
            $end = min($total_pages, max($page + 2, 5));

            if ($start > 1): ?>
                  <a href="/admin/users.php?page=1">1</a>
                  <?php if ($start > 2): ?>
                        <span>...</span>
                  <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                  <a href="/admin/users.php?page=<?= $i ?>"
                        <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                  <?php if ($end < $total_pages - 1): ?>
                        <span>...</span>
                  <?php endif; ?>
                  <a href="/admin/users.php?page=<?= $total_pages ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                  <a href="/admin/users.php?page=<?= $page + 1 ?>" class="btn">Siguiente</a>
            <?php endif; ?>
      </div>

</body>

</html>