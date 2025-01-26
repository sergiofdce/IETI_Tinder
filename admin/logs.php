<?php
session_start();
include '../includes/functions.php';
require_once '../config/db_connection.php';

logEvent("page_view", "El usuario ha accedido a la página Admin - Logs", $_SESSION["email"]);

// Verificar autenticación y privilegios
if (!isset($_SESSION["user_id"])) {
    header("HTTP/1.0 403 Forbidden");
    die("Error 403: Forbidden");
}

$user_id = $_SESSION["user_id"];
$query = "SELECT privileges FROM users WHERE id = :user_id";
$params = [':user_id' => $user_id];
$user = executeQuery($pdo, $query, $params);

if ($user[0]['privileges'] != "admin") {
    logEvent("page_view", "Problema de permisos, sin acceso a Admin - Logs", $_SESSION["email"]);
    header("HTTP/1.0 403 Forbidden");
    die("Error 403: Forbidden");
}

// Verificar si se solicita un archivo específico
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitizar nombre de archivo
    $filepath = "../logs/" . $filename;
    
    if (!file_exists($filepath)) {
        die("Archivo de log no encontrado.");
    }

    $logs = file_get_contents($filepath);
    $logs_array = array_filter(explode("\n", $logs));
    
    // Procesar cada línea de log para extraer los componentes
    $processed_logs = [];
    foreach ($logs_array as $log) {
        if (preg_match('/\[(.*?)\] (.*?): (.*?) - (.*?) \(IP: (.*?), User Agent: (.*?), URI: (.*?)\)/', $log, $matches)) {
            $processed_logs[] = [
                'datetime' => $matches[1],
                'event_type' => $matches[2],
                'email' => $matches[3],
                'description' => $matches[4],
                'ip' => $matches[5],
                'user_agent' => $matches[6],
                'uri' => rtrim($matches[7], ')')
            ];
        }
    }

    // Paginación para los logs
    $log_page = isset($_GET['log_page']) ? (int)$_GET['log_page'] : 1;
    $logs_per_page = 50;
    $total_logs = count($processed_logs);
    $total_log_pages = ceil($total_logs / $logs_per_page);
    $log_offset = ($log_page - 1) * $logs_per_page;

    // Obtener logs para la página actual
    $current_logs = array_slice($processed_logs, $log_offset, $logs_per_page);
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyDates - Detalles del Log</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1>Logs del día: <?= htmlspecialchars(substr($filename, 0, -4)) ?></h1>
        <div class="logs-container">
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Tipo</th>
                        <th>Usuario</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th>URI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['datetime']) ?></td>
                            <td class="event-type"><?= htmlspecialchars($log['event_type']) ?></td>
                            <td><?= htmlspecialchars($log['email']) ?></td>
                            <td><?= htmlspecialchars($log['description']) ?></td>
                            <td><?= htmlspecialchars($log['ip']) ?></td>
                            <td><?= htmlspecialchars($log['uri']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginador para los logs -->
            <div class="pagination">
                <?php if ($log_page > 1): ?>
                    <a href="/admin/logs.php?file=<?= urlencode($filename) ?>&log_page=<?= $log_page - 1 ?>" class="btn">Anterior</a>
                <?php endif; ?>

                <?php 
                $start = max(1, min($log_page - 2, $total_log_pages - 4));
                $end = min($total_log_pages, max($log_page + 2, 5));

                if ($start > 1): ?>
                    <a href="/admin/logs.php?file=<?= urlencode($filename) ?>&log_page=1">1</a>
                    <?php if ($start > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="/admin/logs.php?file=<?= urlencode($filename) ?>&log_page=<?= $i ?>" 
                       <?= $i === $log_page ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end < $total_log_pages): ?>
                    <?php if ($end < $total_log_pages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="/admin/logs.php?file=<?= urlencode($filename) ?>&log_page=<?= $total_log_pages ?>"><?= $total_log_pages ?></a>
                <?php endif; ?>

                <?php if ($log_page < $total_log_pages): ?>
                    <a href="/admin/logs.php?file=<?= urlencode($filename) ?>&log_page=<?= $log_page + 1 ?>" class="btn">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <a href="/admin/logs.php" class="back-link">Volver a la lista de logs</a>
    </body>
    </html>
<?php
    exit();
}

// Listar archivos de logs
$logs_dir = "../logs/";
$log_files = glob($logs_dir . "*.txt");
rsort($log_files); // Ordenar por fecha descendente

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$total_files = count($log_files);
$total_pages = ceil($total_files / $limit);
$offset = ($page - 1) * $limit;

// Obtener archivos para la página actual
$current_files = array_slice($log_files, $offset, $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyDates - Admin Panel - Logs</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Registros del Sistema</h1>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tamaño</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($current_files as $file): ?>
                <tr>
                    <td><?= htmlspecialchars(basename($file, '.txt')) ?></td>
                    <td><?= round(filesize($file) / 1024, 2) ?> KB</td>
                    <td>
                        <a href="/admin/logs.php?file=<?= htmlspecialchars(basename($file)) ?>">Ver Detalles</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Cambiar esta sección del paginador -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="/admin/logs.php?page=<?= $page - 1 ?>" class="btn">Anterior</a>
        <?php endif; ?>

        <?php 
        // Mostrar máximo 5 páginas alrededor de la página actual
        $start = max(1, min($page - 2, $total_pages - 4));
        $end = min($total_pages, max($page + 2, 5));

        if ($start > 1): ?>
            <a href="/admin/logs.php?page=1">1</a>
            <?php if ($start > 2): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="/admin/logs.php?page=<?= $i ?>" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($end < $total_pages): ?>
            <?php if ($end < $total_pages - 1): ?>
                <span>...</span>
            <?php endif; ?>
            <a href="/admin/logs.php?page=<?= $total_pages ?>"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="/admin/logs.php?page=<?= $page + 1 ?>" class="btn">Siguiente</a>
        <?php endif; ?>
    </div>
</body>
</html>
