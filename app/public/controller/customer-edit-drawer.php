<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\UpdateCustomer;
use App\Application\Customer\FindCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include('../layouts/config.php');
include('../layouts/helpers.php');
require_once '../layouts/session.php';
require_once __DIR__ . '/../layouts/permissions.php';

function customerEditRedirect(string $query): void {
    header('Location: ../dash-customers.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_edit_client'])) {
    customerEditRedirect('err=' . urlencode('Petición inválida.'));
}

$idCliente = (int) ($_POST['id_Cliente'] ?? 0);
require_permission('update', 'Customer', $idCliente);
if ($idCliente <= 0) {
    customerEditRedirect('action=edit&id=0&err=' . urlencode('ID de cliente inválido.'));
}

$repo = new MysqliCustomerRepository($link);

$actual = (new FindCustomer($repo))->handle($idCliente);
if ($actual === null) {
    customerEditRedirect('err=' . urlencode('Cliente no encontrado.'));
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
    customerEditRedirect('action=edit&id=' . $idCliente . '&err=' . urlencode(implode(' ', $errores)));
}

$rutNormalizado = normalizar_rut($rut);
$rutActualNorm  = normalizar_rut((string) $actual->rut);

if ($rutNormalizado !== $rutActualNorm) {
    $dbRuts = $link->query('SELECT id_Cliente, rut_Cliente FROM clientes WHERE id_Cliente <> ' . $idCliente);
    $rutDuplicado = false;
    while ($row = $dbRuts->fetch_assoc()) {
        if (normalizar_rut((string) $row['rut_Cliente']) === $rutNormalizado) {
            $rutDuplicado = true;
            break;
        }
    }
    $dbRuts->close();
    if ($rutDuplicado) {
        customerEditRedirect('action=edit&id=' . $idCliente . '&err=' . urlencode('Ya existe otro cliente registrado con ese RUT.'));
    }
}

if (strcasecmp($email, (string) $actual->email) !== 0) {
    $stmt = $link->prepare('SELECT id_Cliente FROM clientes WHERE LOWER(email_Cliente) = LOWER(?) AND id_Cliente <> ? LIMIT 1');
    $stmt->bind_param('si', $email, $idCliente);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        customerEditRedirect('action=edit&id=' . $idCliente . '&err=' . urlencode('Ya existe otro cliente registrado con ese correo.'));
    }
    $stmt->close();
}

try {
    (new UpdateCustomer($repo))->handle($idCliente, [
        'rutCliente'        => $rut,
        'nombreCliente'     => mb_strtoupper($nombre),
        'telefonoCliente'   => $telefono,
        'emailCliente'      => $email,
        'direccionCliente'  => $direccion,
        'regionCliente'     => $region,
        'ciudadCliente'     => mb_strtoupper($ciudad),
        'comunaCliente'     => $comuna,
    ]);
} catch (\mysqli_sql_exception $e) {
    customerEditRedirect('action=edit&id=' . $idCliente . '&err=' . urlencode('No se pudo actualizar el cliente. Intente nuevamente.'));
}

$link->close();

customerEditRedirect('flash=success&msg=' . urlencode('Cliente actualizado correctamente.'));
