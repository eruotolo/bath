<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\CreateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include('../layouts/config.php');
include('../layouts/helpers.php');
require_once '../layouts/session.php';
require_once __DIR__ . '/../layouts/permissions.php';
require_once __DIR__ . '/../layouts/activity_logger.php';
require_permission('create', 'Customer');

function customerCreateRedirect(string $query): void {
    header('Location: ../dash-customers.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_new_client'])) {
    customerCreateRedirect('action=new&err=' . urlencode('Petición inválida.'));
}

$rut        = trim((string) ($_POST['rut_Cliente'] ?? ''));
$nombre     = trim((string) ($_POST['nombre_Cliente'] ?? ''));
$telefono   = trim((string) ($_POST['telefono_Cliente'] ?? ''));
$email      = trim((string) ($_POST['email_Cliente'] ?? ''));
$direccion  = trim((string) ($_POST['direccion_Cliente'] ?? ''));
$region     = trim((string) ($_POST['region_Cliente'] ?? 'Región de Los Lagos'));
$ciudad     = trim((string) ($_POST['ciudad_Cliente'] ?? 'Castro'));
$comuna     = trim((string) ($_POST['comuna_Cliente'] ?? 'Castro'));

$errores = [];

if ($rut === '') {
    $errores[] = 'El RUT es obligatorio.';
} elseif (!validar_rut_modulo11($rut)) {
    $errores[] = 'El RUT no es válido (dígito verificador incorrecto).';
}

if ($nombre === '') {
    $errores[] = 'El nombre del cliente es obligatorio.';
}

if ($email === '') {
    $errores[] = 'El correo electrónico es obligatorio.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electrónico no tiene un formato válido.';
}

if ($errores) {
    customerCreateRedirect('action=new&err=' . urlencode(implode(' ', $errores)));
}

$rutNormalizado = normalizar_rut($rut);

$dbRuts = $link->query('SELECT id_Cliente, rut_Cliente FROM clientes');
$rutDuplicado = false;
while ($row = $dbRuts->fetch_assoc()) {
    if (normalizar_rut((string) $row['rut_Cliente']) === $rutNormalizado) {
        $rutDuplicado = true;
        break;
    }
}
$dbRuts->close();
if ($rutDuplicado) {
    customerCreateRedirect('action=new&err=' . urlencode('Ya existe un cliente registrado con ese RUT.'));
}

$stmt = $link->prepare('SELECT id_Cliente FROM clientes WHERE LOWER(email_Cliente) = LOWER(?) LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    customerCreateRedirect('action=new&err=' . urlencode('Ya existe un cliente registrado con ese correo.'));
}
$stmt->close();

$repo = new MysqliCustomerRepository($link);
try {
    $id = (new CreateCustomer($repo))->handle([
        'rut_Cliente'        => $rut,
        'nombre_Cliente'     => mb_strtoupper($nombre),
        'telefono_Cliente'   => $telefono,
        'email_Cliente'      => $email,
        'direccion_Cliente'  => $direccion,
        'region_Cliente'     => $region,
        'ciudad_Cliente'     => mb_strtoupper($ciudad),
        'comuna_Cliente'     => $comuna,
    ]);

    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Customer',
        'entidad_id' => $id,
        'descripcion' => "Creó cliente id $id (RUT $rut)",
        'datos' => $_POST,
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Customer',
        'descripcion' => "Error al crear cliente RUT $rut",
        'datos' => $_POST,
        'resultado' => 'error',
    ]);
    customerCreateRedirect('action=new&err=' . urlencode('No se pudo crear el cliente. Intente nuevamente.'));
}

$link->close();

customerCreateRedirect('flash=success&msg=' . urlencode('Cliente creado correctamente.'));
