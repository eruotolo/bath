<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "layouts/config.php";
require_once "layouts/activity_logger.php";

$useremail = $username = $password = $image = $name = $lastname = $category = "";
$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $login_error = "Por favor, ingrese su nombre de usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $login_error = "Por favor, ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($login_error)) {
        $sql = "SELECT id, useremail, username, password, image, name, lastname, category, state FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $userid, $useremail, $username, $hashed_password, $image, $name, $lastname, $category, $state);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id'] = $userid;
                            $_SESSION['useremail'] = $useremail;
                            $_SESSION['username'] = $username;
                            $_SESSION['hashed_password'] = $hashed_password;
                            $_SESSION['image'] = $image;
                            $_SESSION['name'] = $name;
                            $_SESSION['lastname'] = $lastname;
                            $_SESSION['category'] = $category;
                            $_SESSION['state'] = $state;

                            $_SESSION['nivel'] = 0;
                            if ($stmt_nivel = mysqli_prepare($link, "SELECT nivel_category FROM category WHERE id_category = ?")) {
                                mysqli_stmt_bind_param($stmt_nivel, "i", $category);
                                if (mysqli_stmt_execute($stmt_nivel)) {
                                    mysqli_stmt_bind_result($stmt_nivel, $nivel_category);
                                    if (mysqli_stmt_fetch($stmt_nivel)) {
                                        $_SESSION['nivel'] = (int) $nivel_category;
                                    }
                                }
                                mysqli_stmt_close($stmt_nivel);
                            }

                            log_activity_ctx($link, 'LOGIN');

                            header("location: index.php");
                        } else {
                            $login_error = "La contraseña ingresada no es válida.";
                            log_activity($link, [
                                'id_usuario'  => null,
                                'username'    => null,
                                'accion'      => 'ERROR',
                                'entidad'     => null,
                                'entidad_id'  => null,
                                'descripcion' => 'Login fallido para usuario: ' . mb_substr($username, 0, 100),
                                'pantalla'    => 'auth-login.php',
                                'metodo'      => 'POST',
                                'datos'       => null,
                                'resultado'   => 'error',
                                'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                            ]);
                        }
                    }
                } else {
                    $login_error = "No se encontró una cuenta con ese usuario.";
                    log_activity($link, [
                        'id_usuario'  => null,
                        'username'    => null,
                        'accion'      => 'ERROR',
                        'entidad'     => null,
                        'entidad_id'  => null,
                        'descripcion' => 'Login fallido para usuario: ' . mb_substr($username, 0, 100),
                        'pantalla'    => 'auth-login.php',
                        'metodo'      => 'POST',
                        'datos'       => null,
                        'resultado'   => 'error',
                        'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                    ]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Login | Blanco Servicios - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>
<div class="min-h-screen w-screen bg-slate-50 flex items-center justify-center p-4 sm:p-6 md:p-8 font-sans" id="login-container">
    <div class="w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 p-8 space-y-6 relative overflow-hidden transition-all duration-300">

        <!-- Decorative Top Accent -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-indigo-600"></div>

        <!-- Brand Header -->
        <div class="flex flex-col items-center text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/20 transform transition-transform hover:scale-105 duration-300">
                <i data-lucide="bath" class="!w-7 !h-7 text-white"></i>
            </div>
            <div>
                <span class="font-sans font-black text-2xl tracking-tight text-slate-900 block leading-none">Blanco</span>
                <span class="font-mono text-[10px] text-indigo-600 font-bold tracking-widest uppercase block mt-1.5">Servicios</span>
            </div>
            <p class="text-xs text-slate-400 font-sans max-w-xs pt-1">
                Plataforma de Control Operativo, Inventario de Baños Químicos y Gestión Financiera Chiloé.
            </p>
        </div>

        <!-- Error Notification -->
        <?php if (!empty($login_error)): ?>
            <div class="p-3.5 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-700 font-medium flex items-start space-x-2.5" id="login-error-alert">
                <i data-lucide="alert-circle" class="!w-4 !h-4 text-rose-500 shrink-0 mt-0.5"></i>
                <span class="leading-relaxed"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="post" class="space-y-4">
            <div class="space-y-1.5">
                <label for="username" class="text-xs font-bold text-slate-600 block">Nombre de Usuario</label>
                <div class="relative">
                    <i data-lucide="user" class="!w-4 !h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        placeholder="e.g. eruotolo"
                        value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password-input" class="text-xs font-bold text-slate-600 block">Contraseña</label>
                <div class="relative">
                    <i data-lucide="lock" class="!w-4 !h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        id="password-input"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                        aria-label="Password"
                        aria-describedby="password-addon"
                    >
                    <button
                        type="button"
                        id="password-addon"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 transition-colors"
                        tabindex="-1"
                    >
                        <i data-lucide="eye" class="!w-4 !h-4"></i>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                id="login-submit-btn"
                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold font-sans flex items-center justify-center space-x-2 shadow-lg shadow-indigo-600/15 transition-all active:scale-98"
            >
                <i data-lucide="log-in" class="!w-4 !h-4"></i>
                <span>Ingresar al Sistema</span>
            </button>
        </form>

        <div class="text-center">
            <span class="font-mono text-[9px] text-slate-300">
                Oficina de Control Sanitario &bull; Castro, Chiloé
            </span>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/pages/pass-addon.init.js"></script>

<script>
if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
}
</script>

</body>
</html>
