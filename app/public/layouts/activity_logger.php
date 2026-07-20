<?php
// layouts/activity_logger.php
// Cross-cutting activity logger for logs_actividad (see .doc/plan-log-actividad.md §4).
// Never throws — a logging failure must never break the user request (plan §8).

function log_activity(mysqli $link, array $data): void
{
    // Never log secrets — strip sensitive keys before encoding.
    static $blacklist = ['password', 'token', 'hashed_password', 'confirm_password', 'newpassword'];

    $payload = null;
    if (!empty($data['datos']) && is_array($data['datos'])) {
        $clean = array_diff_key($data['datos'], array_flip($blacklist));
        $payload = json_encode($clean, JSON_UNESCAPED_UNICODE);
    }

    $sql = "INSERT INTO logs_actividad
                (id_Usuario, username_Log, accion_Log, entidad_Log, entidad_id_Log,
                 descripcion_Log, pantalla_Log, metodo_Log, datos_Log, resultado_Log,
                 ip_Log, user_agent_Log)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

    try {
        $stmt = $link->prepare($sql);
        $stmt->bind_param(
            'isssssssssss',
            $data['id_usuario'], $data['username'], $data['accion'], $data['entidad'],
            $data['entidad_id'], $data['descripcion'], $data['pantalla'], $data['metodo'],
            $payload, $data['resultado'], $data['ip'], $data['user_agent']
        );
        $stmt->execute();
        $stmt->close();
    } catch (\Throwable $e) {
        // Swallow: logging must never interrupt the app.
        // (Optional: error_log('activity_logger failed: ' . $e->getMessage());)
    }
}

// Build the default context from $_SESSION + $_SERVER, then log.
function log_activity_ctx(mysqli $link, string $accion, array $extra = []): void
{
    log_activity($link, array_merge([
        'id_usuario'  => $_SESSION['id']       ?? null,
        'username'    => $_SESSION['username'] ?? null,
        'accion'      => $accion,
        'entidad'     => null,
        'entidad_id'  => null,
        'descripcion' => null,
        'pantalla'    => basename($_SERVER['SCRIPT_NAME'] ?? ''),
        'metodo'      => $_SERVER['REQUEST_METHOD'] ?? null,
        'datos'       => null,
        'resultado'   => 'success',
        'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ], $extra));
}
