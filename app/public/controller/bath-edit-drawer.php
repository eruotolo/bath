<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\UpdateBathroom;
use App\Application\Bathroom\FindBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

require_once __DIR__ . '/../layouts/helpers.php';
require_authenticated_session('../auth-login.php');
require_once __DIR__ . '/../layouts/permissions.php';
require_once __DIR__ . '/../layouts/activity_logger.php';

global $link;
include('../layouts/config.php');

function bath_edit_redirect(string $query): void {
    header('Location: ../dash-bathrooms.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_edit_bath'])) {
    bath_edit_redirect('flash=error&msg=' . urlencode('Petición inválida.'));
}

$idBath = (int) ($_POST['id_Bath'] ?? 0);
require_permission('update', 'Bathroom', $idBath);
if ($idBath <= 0) {
    bath_edit_redirect('flash=error&msg=' . urlencode('ID de baño inválido.'));
}

$csrf_token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!verify_csrf_token($csrf_token)) {
    bath_edit_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('La sesión del formulario expiró. Intente nuevamente.'));
}

$repo = new MysqliBathroomRepository($link);

$actual = (new FindBathroom($repo))->handle($idBath);
if ($actual === null) {
    bath_edit_redirect('flash=error&msg=' . urlencode('Baño no encontrado.'));
}

$codigo      = trim((string) ($_POST['codigo_Bath'] ?? ''));
$fechaCompra = trim((string) ($_POST['fechaCompra_Bath'] ?? ''));
$observacion = trim((string) ($_POST['observacion_Bath'] ?? ''));
$estado      = (string) ($_POST['estado_Bath'] ?? '');

$errores = [];

if ($codigo === '') {
    $errores[] = 'El código del baño es obligatorio.';
}

$fecha = \DateTime::createFromFormat('Y-m-d', $fechaCompra);
if ($fechaCompra === '' || !$fecha || $fecha->format('Y-m-d') !== $fechaCompra) {
    $errores[] = 'La fecha de compra no es válida.';
}

if (!in_array($estado, ['0', '1', '2'], true)) {
    $errores[] = 'El estado no es válido.';
}

if ($errores) {
    bath_edit_redirect('action=edit&id=' . $idBath . '&err=' . urlencode(implode(' ', $errores)));
}

try {
    $ok = (new UpdateBathroom($repo))->handle($idBath, [
        'codigo_Bath'      => $codigo,
        'fechaCompra_Bath' => $fechaCompra,
        'observacion_Bath' => $observacion,
        'estado_Bath'      => $estado,
    ]);

    if (!$ok) {
        bath_edit_redirect('action=edit&id=' . $idBath . '&err=' . urlencode("Ya existe otro baño con el código '$codigo'."));
    }

    log_activity_ctx($link, 'UPDATE', [
        'entidad' => 'Bathroom',
        'entidad_id' => $idBath,
        'descripcion' => "Actualizó baño código $codigo",
        'datos' => $_POST,
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'UPDATE', [
        'entidad' => 'Bathroom',
        'entidad_id' => $idBath,
        'descripcion' => "Error al actualizar baño código $codigo",
        'datos' => $_POST,
        'resultado' => 'error',
    ]);
    bath_edit_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('No se pudo actualizar el baño. Intente nuevamente.'));
}

$link->close();

bath_edit_redirect('flash=success&msg=' . urlencode('Baño actualizado correctamente.'));
