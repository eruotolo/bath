<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Customer\FindCustomer;
use App\Application\Contact\ListContactsByCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;
use App\Infrastructure\Persistence\MysqliContactRepository;

include('layouts/config.php');

$id_Cliente = (int) $_GET['id_Cliente'];

$customer = (new FindCustomer(new MysqliCustomerRepository($link)))->handle($id_Cliente);

if ($customer !== null) {
    $contactos = (new ListContactsByCustomer(new MysqliContactRepository($link)))->handle($id_Cliente);
    ?>

        <head>

            <title>Perfil Cliente | Blanco Servicios - Admin & Dashboard</title>
            <?php include 'layouts/head.php'; ?>
            <?php include 'layouts/head-style.php'; ?>

        </head>

        <?php include 'layouts/body.php'; ?>

        <div id="layout-wrapper">

            <?php include 'layouts/menu.php'; ?>

            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <div class="dt-page-title">
                            <h1>Perfil Cliente</h1>
                            <ol class="dt-breadcrumb">
                                <li><a href="dash-customers.php">Clientes</a></li>
                                <li class="active">Perfil Cliente</li>
                            </ol>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                            <!-- Informacion del Cliente -->
                            <div class="dt-card">
                                <div class="dt-card-header flex items-start justify-between gap-4">
                                    <div>
                                        <h5 class="dt-card-title">Información del Cliente</h5>
                                        <p class="dt-card-desc">Datos relevantes del cliente</p>
                                    </div>
                                    <a href="javascript:void(0)"
                                       class="dt-btn-secondary"
                                       data-bs-toggle="modal"
                                       data-bs-target="#editarCliente"
                                       data-id="<?php echo (int) $customer->id; ?>"
                                       title="Editar">
                                        <i data-lucide="square-pen" class="!mr-1.5 !h-3.5 !w-3.5"></i> Editar Cliente
                                    </a>
                                </div>
                                <?php include 'layouts/modal-edit-customer.php'; ?>

                                <div class="dt-card-body py-6">
                                    <dl class="grid grid-cols-[120px_1fr] gap-y-2 text-[13px]">
                                        <dt class="font-bold text-slate-500">Nombre:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">RUT:</dt>
                                        <dd class="font-mono text-slate-700 m-0"><?php echo htmlspecialchars($customer->rut, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Email:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Teléfono:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Dirección:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->address, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Comuna:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->commune, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Ciudad:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->city, ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="font-bold text-slate-500">Región:</dt>
                                        <dd class="font-sans text-slate-900 m-0"><?php echo htmlspecialchars($customer->region, ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Contactos del Cliente -->
                            <div class="dt-card">
                                <div class="dt-card-header flex items-start justify-between gap-4">
                                    <div>
                                        <h5 class="dt-card-title">Contactos del Cliente</h5>
                                        <p class="dt-card-desc">Contactos relacionados al cliente</p>
                                    </div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#nuevoContacto"
                                       class="dt-btn-secondary">
                                        <i data-lucide="plus" class="!mr-1.5 !h-3.5 !w-3.5"></i> Agregar Nuevo Contacto
                                    </a>
                                </div>
                                <?php include 'layouts/modal-nuevo-contacto.php'; ?>

                                <div class="dt-card-body p-0">
                                    <?php if (empty($contactos)): ?>
                                        <p class="m-0 p-6 text-center text-sm text-slate-500">Sin contactos registrados.</p>
                                    <?php else: ?>
                                        <table class="w-full border-collapse text-left">
                                            <thead>
                                                <tr>
                                                    <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">RUT</th>
                                                    <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Nombre</th>
                                                    <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Teléfono</th>
                                                    <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contactos as $contacto): ?>
                                                    <tr>
                                                        <td class="border-b border-slate-100 px-6 py-4 font-mono text-xs text-slate-700"><?php echo htmlspecialchars($contacto->rut, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="border-b border-slate-100 px-6 py-4 font-sans text-sm font-bold text-slate-900"><?php echo htmlspecialchars($contacto->name, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($contacto->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="border-b border-slate-100 px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($contacto->phone, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="border-b border-slate-100 px-6 py-4" style="width: 100px">
                                                            <div class="flex items-center gap-1">
                                                                <a href="javascript:void(0)"
                                                                   class="dt-cell-action view"
                                                                   data-bs-toggle="modal"
                                                                   data-bs-target="#verContacto"
                                                                   data-id="<?php echo (int) $contacto->id; ?>"
                                                                   title="Ver">
                                                                    <i data-lucide="eye"></i>
                                                                </a>
                                                                <a href="javascript:void(0)"
                                                                   class="dt-cell-action editar"
                                                                   data-bs-toggle="modal"
                                                                   data-bs-target="#editarContacto"
                                                                   data-id="<?php echo (int) $contacto->id; ?>"
                                                                   title="Editar">
                                                                    <i data-lucide="square-pen"></i>
                                                                </a>
                                                                <a href="controller/contact-remove.php?id_Contacto=<?php echo (int) $contacto->id; ?>&id_Cliente=<?php echo (int) $contacto->customerId; ?>"
                                                                   class="dt-cell-action delete-contacto" title="Eliminar" aria-label="Eliminar contacto"
                                                                   data-confirm-delete data-confirm-title="¿Eliminar este contacto?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                    <i data-lucide="trash-2" aria-hidden="true"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/modal-ver-contacto.php'; ?>
        <?php include 'layouts/modal-editar-contacto.php'; ?>

        <?php include 'layouts/vendor-scripts.php'; ?>
        <script src="assets/js/app.js"></script>

        <script>
            ModalEditor.init({
                triggerSelector: '.editarCliente',
                endpoint: 'controller/customer-get.php',
                idParam: 'id_Cliente',
                fieldMap: {
                    idCliente: 'id_Cliente',
                    rutCliente: 'rut_Cliente',
                    nombreCliente: 'nombre_Cliente',
                    telefonoCliente: 'telefono_Cliente',
                    emailCliente: 'email_Cliente',
                    direccionCliente: 'direccion_Cliente',
                    comunaCliente: 'comuna_Cliente',
                    ciudadCliente: 'ciudad_Cliente',
                    regionCliente: 'region_Cliente',
                },
            });

            ModalEditor.init({
                triggerSelector: '.editar',
                endpoint: 'controller/contact-get.php',
                idParam: 'id_Contacto',
                fieldMap: {
                    idC: 'id_Contacto',
                    idCC: 'id_Cliente',
                    nombreC: 'nombre_Contacto',
                    apellidoC: 'apellido_Contacto',
                    rutC: 'rut_Contacto',
                    telefonoC: 'telefono_Contacto',
                    direccionC: 'direccion_Contacto',
                    observacionC: 'observacion_Contacto',
                },
            });

            ModalEditor.init({
                triggerSelector: '.view',
                endpoint: 'controller/contact-get.php',
                idParam: 'id_Contacto',
                fieldMap: {
                    id: 'id_Contacto',
                    nombre: 'nombre_Contacto',
                    apellido: 'apellido_Contacto',
                    rut: 'rut_Contacto',
                    telefono: 'telefono_Contacto',
                    direccion: 'direccion_Contacto',
                    observacion: 'observacion_Contacto',
                },
            });
        </script>

        </body>

        </html>
        <?php
} else {
    echo '<script>alert("Problema al cargar el cliente")</script>';
}
?>
