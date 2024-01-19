<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

include('layouts/config.php');
global $link;

$id_Servicio = $_GET['id_Servicio'];

$query = "SELECT * FROM servicios SR
    JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
    JOIN tipo_servicio TS ON SR.nro_Servicio = TS.nro_Servicio
    JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
 WHERE id_Servicio = $id_Servicio";

$query_run = mysqli_query($link, $query);

if ($query_run) {
    while ($row = mysqli_fetch_array($query_run)) {
?>

    <head>

        <title>Item Servicio Agregar Baños | Chubby - Admin & Dashboard</title>

        <?php include 'layouts/head.php'; ?>
        <?php include 'layouts/head-style.php'; ?>

    </head>

    <?php include 'layouts/body.php'; ?>

         <div class="layout-wrapper">

             <?php include 'layouts/menu.php'; ?>

             <!-- Start right Content here -->
             <div class="main-content">
                 <div class="page-content">
                     <div class="container-fluid">

                         <div class="row">
                             <div class="col-12">
                                 <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                     <h4 class="mb-sm-0 font-size-18">Item Servicios</h4>

                                     <div class="page-title-right">
                                         <ol class="breadcrumb m-0">
                                             <li class="breadcrumb-item"><a href="dash-servicios.php">Servicios</a></li>
                                             <li class="breadcrumb-item active">Item Servicios</li>
                                         </ol>
                                     </div>

                                 </div>
                             </div>
                         </div>

                         <div class="row">
                             <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <h5 class="card-title">Información del Servicio</h5>
                                                <p class="card-title-desc">Datos relevantes al servicio</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">

                                        <div class="row mb-4">
                                            <label for="nro_Servicio" class="col-sm-4 col-form-label">Número de Servicio:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo $row['nro_Servicio'] ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="nombre_Cliente" class="col-sm-4 col-form-label">Nombre del Cliente:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo $row['nombre_Cliente'] ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="obra_Contrato" class="col-sm-4 col-form-label">Obra:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo $row['obra_Contrato'] ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="direccion_Contrato" class="col-sm-4 col-form-label">Dirección de la Obra:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo $row['direccion_Contrato'] ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="fechaInicio_Contrato" class="col-sm-4 col-form-label">Fecha de Inicio de la Obra:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo date("d/m/Y", strtotime($row['fechaInicio_Contrato'])); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="fechaFin_Contrato" class="col-sm-4 col-form-label">Fecha de Fin de la Obra:</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" value="<?php echo date("d/m/Y", strtotime($row['fechaFin_Contrato'])); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label class="col-sm-4 col-form-label">Servicio Realizado:</label>
                                            <div class="col-sm-7 pt-2 view-servicios">
                                                <ul>
                                                <?php
                                                    if($row['instalacion_Tipo'] == 1){ ?>
                                                    <li><p>Instalación</p></li>
                                                <?php } ?>

                                                <?php
                                                    if($row['reparacion_Tipo'] == 1){ ?>
                                                    <li><p>Reparación</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['limpieza_Tipo'] == 1){ ?>
                                                    <li><p>Limpieza</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['desinfeccion_Tipo'] == 1){ ?>
                                                    <li><p>Desinfección</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['sanitizacion_Tipo'] == 1){ ?>
                                                    <li><p>Sanitización</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['higienico_Tipo'] == 1){ ?>
                                                    <li><p>Entrega Papel Higiénico</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['jabon_Tipo'] == 1){ ?>
                                                    <li><p>Entrega de Jabón Liquido</p></li>
                                                <?php } ?>

                                                <?php
                                                if($row['otros_Tipo'] == 1){ ?>
                                                    <li><p>Otros Servicios</p></li>
                                                <?php } ?>
                                                </ul>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                             </div>

                             <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="card-title">Baños de la Obra</h5>
                                                <p class="card-title-desc">Baños relacionados a la Obra</p>
                                            </div>
                                            <div class="col-6">
                                                <a href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#assignBath"
                                                   data-bs-whatever="@fat"
                                                   class="btn btn-light float-end">
                                                    <i class="bx bx-plus me-1"></i> Agregar Nuevo Baño</a>
                                            </div>

                                            <!-- INICIO MODAL AGREGAR BATH-->

                                            <div class="modal fade modal-md" id="assignBath" tabindex="-1"
                                                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Asignar Baño Al Servicio</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">

                                                            <form action="controller/service-bath-add.php" class="mt-4 pt-2"  method="post" enctype="multipart/form-data">

                                                                <input type="number" class="form-control" id="id_Servicio" name="id_Servicio" value="<?php echo $row['id_Servicio'] ?>" hidden>

                                                                <div class="row mb-4">
                                                                    <label for="id_Bath" class="col-sm-4 col-form-label">Seleccionar el baño:</label>
                                                                    <div class="col-sm-8">
                                                                        <select name="id_Bath" id="id_Bath" class="form-select">
                                                                            <?php
                                                                            $sql = "SELECT * FROM bathrooms BT
                                                                                        JOIN contrato_bathroom CB ON BT.id_Bath = CB.id_Bath
                                                                                        JOIN contratos CT ON CB.id_Contrato = CT.id_Contrato
                                                                                        JOIN servicios SR ON CT.id_Contrato = SR.id_Contrato
                                                                                    WHERE id_Servicio = $id_Servicio";
                                                                            $result_task = mysqli_query($link, $sql);
                                                                            while ($row = mysqli_fetch_Array($result_task)) {
                                                                                ?>
                                                                                <option value="<?php echo $row['id_Bath'] ?>"><?php echo $row['codigo_Bath'] ?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row mb-4">
                                                                    <button class="btn btn-primary w-md btn-registrar" type="submit" name="update">Asignar</button>
                                                                </div>

                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cerrar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- FIN MODAL AGREGAR BATH-->

                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <table class="table table-nowrap align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Código Baño</th>
                                                        <th>Fecha de compra</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $sql = "SELECT * FROM servicios_bathrooms SB
                                                            JOIN bathrooms BT ON SB.id_Bath = BT.id_Bath
                                                            JOIN servicios SR ON SB.id_Servicio = SR.id_Servicio
                                                        WHERE SB.id_Servicio =$id_Servicio;";
                                                $result_task = mysqli_query($link, $sql);
                                                while ($row = mysqli_fetch_Array($result_task)) {
                                                    $id_Contrato = $row['id_Contrato'];
                                                    $id_Relacion = $row['id_Relacion'];
                                                    $id_Bath = $row['id_Bath']
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $row['codigo_Bath'] ?></td>
                                                        <td><?php echo $row['fechaCompra_Bath'] ?></td>
                                                        <td style="width: 70px; text-align: center">
                                                            <!-- Botón para eliminar relación -->

                                                            <a href="controller/service-bath-remove.php?id_Relacion=<?php echo $row['id_Relacion'] ?>&id_Servicio=<?php echo $row['id_Servicio'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                             </div>



                         </div>

                     </div>
                 </div>
             </div>

         </div>

        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>
        <script src="assets/js/app.js"></script>

    </body>

    </html>


<?php
    }
} else {
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>
