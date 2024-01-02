<div class="modal fade modal-md"
     id="editarCliente"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="controller/customer-update.php" method="post" id="contactForm" class="mt-4 pt-2">

                    <input type="number" class="form-control" id="idCliente" name="idCliente" hidden>

                    <div class="row mb-4">
                        <label for="rutCliente" class="col-sm-4 col-form-label">RUT del cliente:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="rutCliente" name="rutCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="nombreCliente" class="col-sm-4 col-form-label">Nombre del cliente:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nombreCliente" name="nombreCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="telefonoCliente" class="col-sm-4 col-form-label">Teléfono del cliente:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="telefonoCliente" name="telefonoCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="emailCliente" class="col-sm-4 col-form-label">Email del cliente:</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" id="emailCliente" name="emailCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="direccionCliente" class="col-sm-4 col-form-label">Dirección del cliente:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="direccionCliente" name="direccionCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="comunaCliente" class="col-sm-4 col-form-label">Comuna:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="comunaCliente" name="comunaCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="ciudadCliente" class="col-sm-4 col-form-label">Ciudad</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="ciudadCliente" name="ciudadCliente" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="regionCliente" class="col-sm-4 col-form-label">Región:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="regionCliente" name="regionCliente" readonly>
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