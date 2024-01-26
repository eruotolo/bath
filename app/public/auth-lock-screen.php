<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<?php

// Define variables and initialize with empty values
$username = $_SESSION['username'];
$password = "";
$password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirect user to welcome page
                            header("location: index.php");
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "La contraseña introducida no es válida.";
                            /*header("location: auth-login.php");*/
                        }
                    }
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>

<head>

    <title>Lock Screen | Chubby - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            <div class="mb-4 mb-md-5 text-center">
                                <a href="index.php" class="d-block auth-logo">
                                    <img src="assets/images/logo-sm.svg" alt="" height="28"> <span
                                            class="logo-txt">Chubby Backend</span>
                                </a>
                            </div>
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                    <h5 class="mb-0">Pantalla Bloqueada</h5>
                                    <p class="text-muted mt-2">¡Ingresa tu password para desbloquear la pantalla!</p>
                                </div>
                                <div class="user-thumb text-center mb-4 mt-4 pt-2">
                                    <img src="uploads/users/<?php echo $_SESSION['image']; ?>"
                                         class="rounded-circle img-thumbnail avatar-lg"
                                         alt="thumbnail">
                                    <h5 class="font-size-15 mt-3"><?php echo $_SESSION['name']; ?><?php echo $_SESSION['lastname']; ?></h5>
                                </div>

                                <form class="mt-4" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                      method="post">
                                    <div class="mb-3 d-none">
                                        <input type="text" class="form-control" id="username"
                                               placeholder="Enter username" name="username"
                                               value="<?php echo $_SESSION['username']; ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="Enter password"
                                                   name="password" value="123456" aria-label="Password"
                                                   aria-describedby="password-addon">

                                            <button class="btn btn-light ms-0" type="button" id="password-addon"><i
                                                        class="mdi mdi-eye-outline"></i></button>
                                            <br>

                                        </div>
                                        <span class="text-danger"><?php echo $password_err; ?></span>
                                    </div>
                                    <div class="mb-3 mt-4">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">
                                            Ingresar
                                        </button>
                                    </div>
                                </form>


                                <div class="mt-5 text-center">
                                    <p class="text-muted mb-0">No eres tu ? ingresa <a href="logout.php"
                                                                                       class="text-primary fw-semibold">Login </a>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 mt-md-5 text-center">
                                <p class="mb-0">©
                                    <script>document.write(new Date().getFullYear())</script>
                                                Chubby Backend. <br>Elaborado con <i
                                            class="mdi mdi-heart text-danger"></i>
                                                Crow Advance
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end auth full page content -->
            </div>
            <!-- end col -->
            <div class="col-xxl-9 col-lg-8 col-md-7">
                <div class="p-4 auth-bg pt-md-5 d-flex justify-content-center align-items-center">
                    <div class="bg-overlay bg-primary"></div>
                    <ul class="bg-bubbles">
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                    </ul>
                    <img src="assets/images/logo-sm.svg" alt="Logo" class="logo-lema">
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container fluid -->
</div>


<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>

<!-- password addon init -->
<script src="assets/js/pages/pass-addon.init.js"></script>

</body>

</html>