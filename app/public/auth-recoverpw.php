<?php
require_once "layouts/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';
$useremail_err = $msg = "";
$mail = new PHPMailer(true);
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/$uri_segments[1]";
if (isset($_POST['submit'])) {
    $useremail = mysqli_real_escape_string($link, $_POST['useremail']);
    $sql = "SELECT * FROM users WHERE useremail = '$useremail'";
    $query = mysqli_query($link, $sql);
    $emailcount = mysqli_num_rows($query);
    if ($emailcount) {
        $userdata = mysqli_fetch_array($query);
        $username = $userdata['username'];
        $token = $userdata['token'];
        $subject = "Password Reset";
        $body = "Hi, $username. Click here to reset your password " . $actual_link . "/auth-reset-password.php?token=$token ";
        $sender_email = "From: $gmailid";
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Username = $gmailid;
            $mail->Password = $gmailpassword;
            $mail->setFrom($gmailid, $gmailusername);
            $mail->addAddress($useremail, $username);
            $mail->addReplyTo($gmailid, $gmailusername);
            $mail->IsHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            $msg = "We have emailed your password reset link!";
        } catch (Exception $e) {
            $useremail_err = "Error in sending email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $useremail_err = "No Email Found";
    }
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Recover Password | Blanco Servicios - Admin & Dashboard Template</title>
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
                                    <h5 class="font-sans text-xl font-bold text-slate-900 mb-0">Restablecer Password</h5>
                                    <p class="text-sm text-slate-500 mt-2">Restablecé tu contraseña de Blanco Servicios.</p>
                                </div>
                                <?php if ($msg): ?>
                                    <div class="dt-alert dt-alert-success text-center my-4">
                                        <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <form class="mt-6" action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3 <?php echo !empty($useremail_err) ? 'has-error' : ''; ?>">
                                        <label class="dt-label">Email</label>
                                        <input type="text" class="dt-input" id="email" name="useremail" placeholder="Ingresá tu email">
                                        <?php if (!empty($useremail_err)): ?>
                                            <span class="font-sans text-xs text-rose-600 mt-1 block"><?php echo htmlspecialchars($useremail_err, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3 mt-4">
                                        <button class="dt-btn-add w-full justify-center !w-full" type="submit" name="submit" value="Submit">Restablecer</button>
                                    </div>
                                </form>

                                <div class="mt-5 text-center">
                                    <p class="text-sm text-slate-500 mb-0">¿Te acordás? <a href="auth-login.php" class="text-primary-600 font-semibold">Iniciar sesión</a></p>
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

</body>
</html>
