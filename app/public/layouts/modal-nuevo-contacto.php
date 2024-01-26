<div class="modal fade modal-md" id="nuevoContacto" tabindex="-1"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <form class="mt-4 pt-2" action="controller/contact-new.php" method="post" enctype="multipart/form-data">

                    <input type="number" class="form-control" id="id_Cliente" name="id_Cliente" value="<?php echo $row['id_Cliente'] ?>" hidden>

                    <div class="row mb-4">
                        <label for="nombre_Contacto" class="col-sm-4 col-form-label">Nombre</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nombre_Contacto" name="nombre_Contacto" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="apellido_Contacto" class="col-sm-4 col-form-label">Apellido</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="apellido_Contacto" name="apellido_Contacto" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="rut_Contacto" class="col-sm-4 col-form-label">RUT</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="rut_Contacto" name="rut_Contacto" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="telefono_Contacto" class="col-sm-4 col-form-label">Teléfono</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="telefono_Contacto" name="telefono_Contacto" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="direccion_Contacto" class="col-sm-4 col-form-label">Dirección</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="direccion_Contacto" name="direccion_Contacto" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <button class="btn btn-primary w-md btn-registrar" type="submit" name="crear">Registrar</button>
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