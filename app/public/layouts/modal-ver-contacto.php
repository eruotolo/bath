<div class="modal fade modal-md"
     id="verContacto"
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
                <form id="contactForm" class="mt-4 pt-2" action="#" method="post">

                    <div class="row mb-4">
                        <label for="nombre" class="col-sm-4 col-form-label">Nombre:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="nombre" name="nombre" readonly>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="apellido" class="col-sm-4 col-form-label">Apellido:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="apellido" name="apellido" readonly>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="rut" class="col-sm-4 col-form-label">RUT:</label>

                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="rut" name="rut" readonly>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="telefono" class="col-sm-4 col-form-label">Teléfono:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="telefono" name="telefono" readonly>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="direccion" class="col-sm-4 col-form-label">Dirección:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="direccion" name="direccion" readonly>
                        </div>
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