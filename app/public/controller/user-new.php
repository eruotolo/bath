<?php

global $link;
include "../layouts/config.php";

if (isset($_POST['crear'])){
    $useremail = $_POST['useremail'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = "";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $param_token = bin2hex(random_bytes(50));
    $firstname = $_POST['name'];
    $lastname = $_POST['lastname'];
    $category = 2;
    $state = 1;

    #file name with a random number so that similar dont get replaced
    $pname = rand(1000,10000)."-".$_FILES["file"]["name"];

    #temporary file name to store file
    $tname = $_FILES["file"]["tmp_name"];

    #upload directory path
    $uploads_dir = '../uploads/users/';

    #TO move the uploaded file to specific location
    move_uploaded_file($tname, $uploads_dir.'/'.$pname);

    $query = "INSERT INTO users (useremail, username, password, token, name, lastname, image, category, state) VALUES ('$useremail', '$username', '$hash', '$param_token', '$firstname', '$lastname', '$pname', '$category', '$state' )";

    //echo $query;
    //die();

    $result = mysqli_query($link, $query) or ($error = mysqli_error($link));

    //echo $error;
    //die();

    header('Location: ../dash-users-list.php?status=success&msg=' . urlencode('Usuario creado correctamente'));

}else{
    header('Location: ../index.php?status=error&msg=' . urlencode('No se pudo crear el usuario'));
}
// Cerrar la conexión
$link->close();