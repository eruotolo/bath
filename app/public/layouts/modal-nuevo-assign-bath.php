<div class="modal fade modal-md" id="nuevoAssign" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Asignar Nuevo Baño al Contrato/Obra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="controller/contract-bath-new-assign.php" class="mt-4 pt-2" method="post" enctype="multipart/form-data">
                    <input type="number" class="dt-input" id="id_Contrato" name="id_Contrato" value="<?php echo isset($contrato['id_Contrato']) ? (int) $contrato['id_Contrato'] : ''; ?>" hidden>

                    <div class="mb-4">
                        <label for="id_Bath" class="dt-label">Seleccionar el baño:</label>
                        <select name="id_Bath" id="id_Bath" class="dt-select" data-enhanced-select data-search-placeholder="Buscar baño...">
                            <?php
                            $sql = "SELECT id_Bath, codigo_Bath FROM bathrooms WHERE asignado_Bath = 0";
                            $result_task = mysqli_query($link, $sql);
                            while ($row = mysqli_fetch_array($result_task)) {
                            ?>
                                <option value="<?php echo (int) $row['id_Bath']; ?>"><?php echo htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="dt-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="dt-btn-add" type="submit" name="update">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
