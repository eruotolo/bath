<div class="modal fade modal-md" id="nuevoContacto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar Nuevo Contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="mt-4 pt-2" action="controller/contact-new.php" method="post" enctype="multipart/form-data">
                    <input type="number" class="dt-input" id="id_Cliente" name="id_Cliente" value="<?php echo isset($customer->id) ? (int) $customer->id : ''; ?>" hidden>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                        <div class="mb-4">
                            <label for="nombre_Contacto" class="dt-label">Nombre</label>
                            <input type="text" class="dt-input" id="nombre_Contacto" name="nombre_Contacto" required>
                        </div>
                        <div class="mb-4">
                            <label for="apellido_Contacto" class="dt-label">Apellido</label>
                            <input type="text" class="dt-input" id="apellido_Contacto" name="apellido_Contacto" required>
                        </div>
                        <div class="mb-4">
                            <label for="rut_Contacto" class="dt-label">RUT</label>
                            <input type="text" class="dt-input" id="rut_Contacto" name="rut_Contacto" placeholder="12.345.678-9" required data-rut-mask>
                        </div>
                        <div class="mb-4">
                            <label for="telefono_Contacto" class="dt-label">Teléfono</label>
                            <input type="number" class="dt-input" id="telefono_Contacto" name="telefono_Contacto" required>
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="direccion_Contacto" class="dt-label">Dirección</label>
                            <input type="text" class="dt-input" id="direccion_Contacto" name="direccion_Contacto" required>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="dt-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="dt-btn-add" type="submit" name="crear">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
