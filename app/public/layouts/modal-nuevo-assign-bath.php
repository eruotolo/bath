<div class="modal fade modal-md" id="nuevoAssign" tabindex="-1"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Asignar Nuevo Baño al Contrato/Obra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <form action="controller/contract-bath-new-assign.php" class="mt-4 pt-2"  method="post" enctype="multipart/form-data">

                    <input type="number" class="form-control" id="id_Contrato" name="id_Contrato" value="<?php echo $row['id_Contrato'] ?>" hidden>

                    <div class="row mb-4">
                        <label for="id_Bath" class="col-sm-4 col-form-label">Seleccionar el baño:</label>
                        <div class="col-sm-8">
                            <select name="id_Bath" id="id_Bath" class="form-select">
                                <?php
                                    $sql = "SELECT id_Bath, codigo_Bath FROM bathrooms WHERE asignado_Bath = 0";
                                    $result_task = mysqli_query($link, $sql);
                                    while ($row = mysqli_fetch_Array($result_task)) {
                                ?>
                                    <option value="<?php echo $row['id_Bath'] ?>"><?php echo $row['codigo_Bath'] ?></option>
                                <?php
                                    }
                                    ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <button class="btn btn-primary w-md btn-registrar" type="submit" name="update">Asignar</button>
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