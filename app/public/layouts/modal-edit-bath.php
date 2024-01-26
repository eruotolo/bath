<div class="modal fade modal-md"
     id="editarBath"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del Baño Químico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="controller/bath-update.php" method="post" id="bathForm" class="mt-4 pt-2">
                    <input type="number" class="form-control" id="idBath" name="idBath" hidden>

                    <div class="row mb-4">
                        <label for="codigoBath" class="col-sm-4 col-form-label">Código del Baño:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="codigoBath" name="codigoBath" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="fechaCompraBath" class="col-sm-4 col-form-label">Fecha de compra:</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" id="fechaCompraBath" name="fechaCompraBath" >
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label for="observacionBath" class="col-sm-4 col-form-label">Observaciones:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="observacionBath" name="observacionBath" >
                        </div>
                    </div>

                    <div class="row mb-4" hidden>
                        <label for="estadoBath" class="col-sm-4 col-form-label">Estado:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="estadoBath" name="estadoBath" >
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
</div>>