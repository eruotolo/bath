<div class="modal fade modal-md" id="editarContacto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm" class="mt-4 pt-2" action="controller/contact-update.php" method="post">
                    <input type="text" class="dt-input" id="idCC" name="idCC" readonly hidden>
                    <input type="text" class="dt-input" id="idC" name="idC" readonly hidden>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                        <div class="mb-4">
                            <label for="nombreC" class="dt-label">Nombre</label>
                            <input type="text" class="dt-input" id="nombreC" name="nombreC">
                        </div>
                        <div class="mb-4">
                            <label for="apellidoC" class="dt-label">Apellido</label>
                            <input type="text" class="dt-input" id="apellidoC" name="apellidoC">
                        </div>
                        <div class="mb-4">
                            <label for="rutC" class="dt-label">RUT</label>
                            <input type="text" class="dt-input" id="rutC" name="rutC" data-rut-mask>
                        </div>
                        <div class="mb-4">
                            <label for="telefonoC" class="dt-label">Teléfono</label>
                            <input type="number" class="dt-input" id="telefonoC" name="telefonoC">
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="direccionC" class="dt-label">Dirección</label>
                            <input type="text" class="dt-input" id="direccionC" name="direccionC">
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="observacionC" class="dt-label">Observaciones</label>
                            <textarea id="observacionC" name="observacionC" rows="4" class="dt-input"></textarea>
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
