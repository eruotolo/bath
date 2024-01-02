<div class="modal fade modal-md"
     id="editarContacto"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm" class="mt-4 pt-2" action="controller/contact-update.php" method="post">
                    <input type="text" class="form-control" id="idCC" name="idCC" readonly hidden>
                    <input type="text" class="form-control" id="idC" name="idC" readonly hidden>
                    <div class="row mb-4">
                        <label for="nombreC" class="col-sm-4 col-form-label">Nombre:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nombreC" name="nombreC" >
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="apellidoC" class="col-sm-4 col-form-label">Apellido:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="apellidoC" name="apellidoC" >
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="rutC" class="col-sm-4 col-form-label">RUT:</label>

                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="rutC" name="rutC" >
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="telefonoC" class="col-sm-4 col-form-label">Teléfono:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="telefonoC" name="telefonoC" >
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="direccionC" class="col-sm-4 col-form-label">Dirección:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="direccionC" name="direccionC">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <button class="btn btn-primary w-md btn-registrar" type="submit" name="update">Actualizar</button>
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