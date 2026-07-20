<?php
require_once "layouts/config.php";

$useremail = $username = $password = $confirm_password = "";
$useremail_err = $username_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter a useremail.";
    } elseif (!filter_var($_POST["useremail"], FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format";
    } else {
        $sql = "SELECT id FROM users WHERE useremail = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_useremail);
            $param_useremail = trim($_POST["useremail"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $useremail_err = "This useremail is already taken.";
                } else {
                    $useremail = trim($_POST["useremail"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please enter a confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (empty($useremail_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (useremail, username, password, token) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_useremail, $param_username, $param_password, $param_token);
            $param_useremail = $useremail;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_token = bin2hex(random_bytes(50));
            if (mysqli_stmt_execute($stmt)) {
                header("location: index.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Register | Blanco Servicios - Admin & Dashboard Template</title>
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
                                    <h5 class="font-sans text-xl font-bold text-slate-900 mb-0">Crear Cuenta</h5>
                                    <p class="text-sm text-slate-500 mt-2">Obtené tu cuenta gratuita de Blanco Servicios.</p>
                                </div>
                                <form class="needs-validation mt-6 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                                    <div class="mb-3 <?php echo !empty($useremail_err) ? 'has-error' : ''; ?>">
                                        <label for="useremail" class="dt-label">Email</label>
                                        <input type="email" class="dt-input" id="useremail" placeholder="Ingresa tu email" required name="useremail" value="<?php echo htmlspecialchars($useremail, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php if (!empty($useremail_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($useremail_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3 <?php echo !empty($username_err) ? 'has-error' : ''; ?>">
                                        <label for="username" class="dt-label">Usuario</label>
                                        <input type="text" class="dt-input" id="username" placeholder="Ingresa tu usuario" required name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php if (!empty($username_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($username_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3 <?php echo !empty($password_err) ? 'has-error' : ''; ?>">
                                        <label for="userpassword" class="dt-label">Password</label>
                                        <input type="password" class="dt-input" id="userpassword" placeholder="Ingresa tu password" required name="password" autocomplete="new-password">
                                        <?php if (!empty($password_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($password_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3 <?php echo !empty($confirm_password_err) ? 'has-error' : ''; ?>">
                                        <label class="dt-label" for="userpassword">Confirmar Password</label>
                                        <input type="password" class="dt-input" id="confirm_password" placeholder="Confirma tu password" name="confirm_password" autocomplete="new-password">
                                        <?php if (!empty($confirm_password_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($confirm_password_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-4">
                                        <p class="mb-0 text-xs text-slate-500">Al registrarte aceptás los <a href="#" class="text-primary-600 font-semibold">Términos de Uso</a> de Blanco Servicios</p>
                                    </div>
                                    <div class="mb-3">
                                        <button class="dt-btn-add w-full justify-center !w-full" type="submit">Registrar</button>
                                    </div>
                                </form>

                                <div class="mt-5 text-center">
                                    <p class="text-sm text-slate-500 mb-0">¿Ya tenés cuenta? <a href="auth-login.php" class="text-primary-600 font-semibold">Iniciar sesión</a></p>
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
                    <?php for ($i = 0; $i < 9; $i++): ?>
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
<script src="assets/js/pages/validation.init.js"></script>

</body>
</html>
