<?php
// =============================================================================
// Redireccionador neutralizador — servicio-pdf.php (LEGACY, NO USAR).
//
// Este archivo fue reemplazado por app/public/controller/service-pdf.php
// (patrón DDD, prepared statements, htmlspecialchars en todos los outputs).
// El código legacy que vivía acá:
//   - Tenía SQL injection activa ($_GET['id_Servicio'] interpolado en WHERE).
//   - Usaba una columna inexistente SR.id_Bath (la relación real es vía la
//     tabla intermedia servicios_bathrooms).
//   - Generaba el PDF con mail() inline (sin auth), con outputs sin escapar.
// Además, grep -rn confirma que NINGÚN .php del proyecto lo referencia — está
// completamente huérfano. Lo dejamos como redirect por compatibilidad de
// bookmarks/links viejos, pero ya no ejecuta lógica propia.
// Ver: AGENTS.md §SQL — reglas críticas (excepción conocida).
// =============================================================================

if (isset($_GET['id_Servicio']) && ctype_digit((string) $_GET['id_Servicio'])) {
    $id = (int) $_GET['id_Servicio'];
    header('Location: service-pdf.php?id_Servicio=' . $id);
    exit;
}

http_response_code(400);
header('Content-Type: text/plain; charset=utf-8');
echo 'Parámetro id_Servicio inválido.';
exit;
