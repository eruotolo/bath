<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to index page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}
// Include config file
require_once "layouts/config.php";

// Define variables and initialize with empty values
$useremail = $username = $password = $image = $name = $lastname = $category = "";
$username_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, useremail, username, password, image, name, lastname, category, state  FROM users WHERE username = ?";

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
                    mysqli_stmt_bind_result($stmt, $userid, $useremail, $username, $hashed_password, $image, $name, $lastname, $category, $state);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
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

                            // Redirect user to welcome page
                            header("location: index.php");
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    
    <title>Login | Chubby - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<div class="auth-page">
    <div class="p-0 container-fluid">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="p-4 auth-full-page-content d-flex p-sm-5">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            <div class="mb-4 text-center mb-md-5">
                                <a href="index.php" class="d-block auth-logo">
                                    <img src="assets/images/logo-sm.svg" alt="" height="28"> <span
                                            class="logo-txt">Chubby Backend</span>
                                </a>
                            </div>
                            <div class="my-auto auth-content">
                                <div class="text-center">
                                    <h5 class="mb-0">¡Bienvenidos!</h5>
                                    <p class="mt-2 text-muted">Inicia sesión para continuar.</p>
                                </div>
                                
                                <!-- INICIO DEL FORMULARIO -->
                                
                                <form class="mt-4 pt-2" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                      method="post">
                                    <div class="mb-3 <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                                        <label class="form-label" for="username">Usuario</label>
                                        <input type="text" class="form-control" id="username"
                                               placeholder="Enter username" name="username" value="eruotolo">
                                        <span class="text-danger"><?php echo $username_err; ?></span>
                                    </div>
                                    <div class="mb-3 <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <label class="form-label" for="password">Password</label>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="">
                                                    <a href="auth-recoverpw.php"
                                                       class="text-muted">¿Olvidaste tu contraseña?</a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="Enter password"
                                                   name="password" value="123456" aria-label="Password"
                                                   aria-describedby="password-addon">
                                            <span class="text-danger"><?php echo $password_err; ?></span>
                                            <button class="btn btn-light ms-0" type="button" id="password-addon"><i
                                                        class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">
                                            Ingresar
                                        </button>
                                    </div>
                                </form>
                                <!-- FIN DEL FORMULARIO -->
                                
                                <div class="mt-5 text-center">
                                    <p class="mb-0 text-muted">¿No tienes una cuenta? <a href="auth-register.php"
                                                                                         class="text-primary fw-semibold">Regístrate ahora </a>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 text-center mt-md-5">
                                <p class="mb-0">©
                                    <script>document.write(new Date().getFullYear())</script>
                                                Chubby Backend. <br>Elaborado con <i
                                            class="mdi mdi-heart text-danger"></i><a href="https://crowadvance.com"
                                                                                     target="_blank">Crow Advance</a>
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