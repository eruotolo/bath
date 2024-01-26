<div class="modal fade modal-md" id="nuevoPassword" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar el Password/Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="controller/user-profile-newpassword.php" class="mt-4 pt-2" method="post" enctype="multipart/form-data">

                    <input type="number" class="form-control" id="id" name="id" value="<?php echo $_SESSION['id']; ?>" hidden>

                    <div class="row mb-4">
                        <label for="password" class="col-sm-4 col-form-label">Nuevo Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <button class="btn btn-primary w-md btn-registrar" type="submit" name="update">Actualizar</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


