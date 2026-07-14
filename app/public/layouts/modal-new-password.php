<div class="modal fade modal-md" id="nuevoPassword" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar el Password/Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-5">
                <form action="controller/user-profile-newpassword.php" class="mt-4 pt-2" method="post" enctype="multipart/form-data">
                    <input type="number" class="dt-input" id="id" name="id" value="<?php echo isset($_SESSION['id']) ? (int) $_SESSION['id'] : ''; ?>" hidden>

                    <div class="mb-4">
                        <label for="password" class="dt-label">Nuevo Password</label>
                        <input type="password" class="dt-input" id="password" name="password" required>
                    </div>

                    <div class="flex justify-end">
                        <button class="dt-btn-add" type="submit" name="update">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
