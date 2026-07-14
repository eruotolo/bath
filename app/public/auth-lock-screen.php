<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<?php
$username = $_SESSION['username'];
$password = "";
$password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            header("location: index.php");
                        } else {
                            $password_err = "La contraseña introducida no es válida.";
                        }
                    }
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<head>
    <title>Lock Screen | Blanco Servicios - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>
<div class="bg-slate-50 min-h-screen font-sans">
    <div class="container-fluid p-0">
        <div class="row g-0 min-h-screen">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="p-4 sm:p-5 min-h-screen flex">
                    <div class="w-full">
                        <div class="flex flex-col h-full min-h-screen py-8">
                            <div class="mb-8 text-center">
                                <a href="index.php" class="inline-flex items-center gap-2 no-underline">
                                    <img src="assets/images/logo-sm.svg" alt="" height="28">
                                    <span class="font-bold text-lg text-primary-600">Blanco Servicios</span>
                                </a>
                            </div>
                            <div class="my-auto">
                                <div class="text-center">
                                    <h5 class="font-sans text-xl font-bold text-slate-900 mb-0">Pantalla Bloqueada</h5>
                                    <p class="text-sm text-slate-500 mt-2">¡Ingresá tu password para desbloquear la pantalla!</p>
                                </div>
                                <div class="text-center my-6 pt-2">
                                    <img src="uploads/users/<?php echo htmlspecialchars($_SESSION['image'], ENT_QUOTES, 'UTF-8'); ?>" class="inline-block !h-20 !w-20 rounded-full border-4 border-slate-100 object-cover" alt="thumbnail">
                                    <h5 class="font-sans mt-3 text-base font-bold text-slate-900"><?php echo htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['lastname'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                </div>

                                <form class="mt-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                                    <div class="mb-3 hidden">
                                        <input type="text" class="dt-input" id="username" placeholder="Enter username" name="username" value="<?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <div class="dt-input-group">
                                            <input type="password" class="dt-input flex-1 rounded-r-none" id="password-input" placeholder="Ingresá tu password" name="password" value="" aria-label="Password" aria-describedby="password-addon">
                                            <button class="dt-input-suffix rounded-r-xl" type="button" id="password-addon">
                                                <i data-lucide="eye" class="!h-4 !w-4"></i>
                                            </button>
                                        </div>
                                        <?php if (!empty($password_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($password_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3 mt-4">
                                        <button class="dt-btn-add w-full justify-center !w-full" type="submit">Ingresar</button>
                                    </div>
                                </form>

                                <div class="mt-5 text-center">
                                    <p class="text-sm text-slate-500 mb-0">¿No sos vos? <a href="logout.php" class="text-primary-600 font-semibold">Cerrar sesión</a></p>
                                </div>
                            </div>
                            <div class="mt-8 text-center text-xs text-slate-400">
                                <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> Blanco Servicios.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9 col-lg-8 col-md-7 hidden md:flex items-center justify-center p-4 pt-md-5 bg-primary-600 relative overflow-hidden">
                <ul class="absolute inset-0 list-none m-0 p-0">
                    <?php for ($i = 0; $i < 11; $i++): ?>
                        <li class="absolute rounded-full bg-white/10" style="width: <?php echo 20 + $i * 8; ?>px; height: <?php echo 20 + $i * 8; ?>px; bottom: -<?php echo 100 + $i * 80; ?>px; left: <?php echo ($i * 12) % 90; ?>%; animation: float <?php echo 4 + ($i % 4); ?>s ease-in-out infinite;"></li>
                    <?php endfor; ?>
                </ul>
                <img src="assets/images/logo-sm.svg" alt="Logo" class="relative z-10 w-1/2 max-w-md">
            </div>
        </div>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
</style>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/pages/pass-addon.init.js"></script>

</body>
</html>
