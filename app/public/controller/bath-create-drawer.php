<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\CreateBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

require_once __DIR__ . '/../layouts/helpers.php';
require_authenticated_session('../auth-login.php');
require_once __DIR__ . '/../layouts/permissions.php';
require_once __DIR__ . '/../layouts/activity_logger.php';

global $link;
include('../layouts/config.php');
require_permission('create', 'Bathroom');

function bath_create_redirect(string $query): void {
    header('Location: ../dash-bathrooms.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_new_bath'])) {
    bath_create_redirect('action=new&err=' . urlencode('Petición inválida.'));
}

$csrf_token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!verify_csrf_token($csrf_token)) {
    bath_create_redirect('action=new&err=' . urlencode('La sesión del formulario expiró. Intente nuevamente.'));
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
    bath_create_redirect('action=new&err=' . urlencode(implode(' ', $errores)));
}

$repo = new MysqliBathroomRepository($link);

try {
    $id = (new CreateBathroom($repo))->handle([
        'codigo_Bath'      => $codigo,
        'fechaCompra_Bath' => $fechaCompra,
        'observacion_Bath' => $observacion,
        'estado_Bath'      => $estado,
    ]);

    if ($id === null) {
        bath_create_redirect('action=new&err=' . urlencode("Ya existe un baño con el código '$codigo'. Ingresá un código distinto."));
    }

    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Bathroom',
        'entidad_id' => $id,
        'descripcion' => "Creó baño código $codigo",
        'datos' => $_POST,
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Bathroom',
        'descripcion' => "Error al crear baño código $codigo",
        'datos' => $_POST,
        'resultado' => 'error',
    ]);
    bath_create_redirect('action=new&err=' . urlencode('No se pudo crear el baño. Intente nuevamente.'));
}

$link->close();

bath_create_redirect('flash=success&msg=' . urlencode('Baño creado correctamente.'));
