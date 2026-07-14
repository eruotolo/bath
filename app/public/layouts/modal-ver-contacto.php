<div class="modal fade modal-md" id="verContacto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Información del contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm" class="mt-4 pt-2" action="#" method="post">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                        <label for="nombre" class="dt-label sm:col-span-1 self-center">Nombre:</label>
                        <div class="sm:col-span-2 mb-4">
                            <input type="text" class="dt-input" id="nombre" name="nombre" readonly>
                        </div>
                        <label for="apellido" class="dt-label sm:col-span-1 self-center">Apellido:</label>
                        <div class="sm:col-span-2 mb-4">
                            <input type="text" class="dt-input" id="apellido" name="apellido" readonly>
                        </div>
                        <label for="rut" class="dt-label sm:col-span-1 self-center">RUT:</label>
                        <div class="sm:col-span-2 mb-4">
                            <input type="text" class="dt-input" id="rut" name="rut" readonly data-rut-mask>
                        </div>
                        <label for="telefono" class="dt-label sm:col-span-1 self-center">Teléfono:</label>
                        <div class="sm:col-span-2 mb-4">
                            <input type="number" class="dt-input" id="telefono" name="telefono" readonly>
                        </div>
                        <label for="direccion" class="dt-label sm:col-span-1 self-center">Dirección:</label>
                        <div class="sm:col-span-2 mb-4">
                            <input type="text" class="dt-input" id="direccion" name="direccion" readonly>
                        </div>
                        <label for="observacion" class="dt-label sm:col-span-1 self-center">Observaciones:</label>
                        <div class="sm:col-span-2 mb-4">
                            <textarea id="observacion" name="observacion" rows="6" class="dt-input" readonly></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" class="dt-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
