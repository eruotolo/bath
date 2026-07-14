<div class="modal fade modal-md" id="editarCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../controller/customer-update.php" method="post" id="contactForm" class="mt-4 pt-2">
                    <input type="number" class="dt-input" id="idCliente" name="idCliente" hidden>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                        <div class="mb-4">
                            <label for="rutCliente" class="dt-label">RUT del cliente</label>
                            <input type="text" class="dt-input" id="rutCliente" name="rutCliente" data-rut-mask>
                        </div>
                        <div class="mb-4">
                            <label for="nombreCliente" class="dt-label">Nombre del cliente</label>
                            <input type="text" class="dt-input" id="nombreCliente" name="nombreCliente">
                        </div>
                        <div class="mb-4">
                            <label for="telefonoCliente" class="dt-label">Teléfono del cliente</label>
                            <input type="text" class="dt-input" id="telefonoCliente" name="telefonoCliente">
                        </div>
                        <div class="mb-4">
                            <label for="emailCliente" class="dt-label">Email del cliente</label>
                            <input type="email" class="dt-input" id="emailCliente" name="emailCliente">
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="direccionCliente" class="dt-label">Dirección del cliente</label>
                            <input type="text" class="dt-input" id="direccionCliente" name="direccionCliente">
                        </div>
                        <div class="mb-4">
                            <label for="comunaCliente" class="dt-label">Comuna</label>
                            <input type="text" class="dt-input" id="comunaCliente" name="comunaCliente">
                        </div>
                        <div class="mb-4">
                            <label for="ciudadCliente" class="dt-label">Ciudad</label>
                            <input type="text" class="dt-input" id="ciudadCliente" name="ciudadCliente">
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="regionCliente" class="dt-label">Región</label>
                            <input type="text" class="dt-input" id="regionCliente" name="regionCliente" readonly>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="dt-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="dt-btn-add" type="submit" name="update">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
