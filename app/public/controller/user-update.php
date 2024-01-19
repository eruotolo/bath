<?php

global $link;
include "../layouts/config.php";

if(isset($_POST['update'])){
    $id = $_POST['id'];

    if(isset($_FILES['file']) && $_FILES["file"]["error"] == 0){
        #file name with a random number so that similar dont get replaced
        $pname = rand(1000, 10000) . "-" . $_FILES["file"]["name"];

        #temporary file name to store file
        $tname = $_FILES["file"]["tmp_name"];

        #upload directory path
        $uploads_dir = '../uploads/users/';

        #TO move the uploaded file to specific location
        move_uploaded_file($tname, $uploads_dir . '/' . $pname);

        $image_update = ",image='$pname'";
    }

    // Useremail
    if (isset($_POST['useremail']) && $_POST['useremail'] <> '') {
        $useremail_update = "useremail='{$_POST['useremail']}'";
    } else {
        $useremail_update = "useremail=NULL";
    }

    //Username
    if (isset($_POST['username']) && $_POST['username'] <> '') {
        $username_update = ",username='{$_POST['username']}'";
    } else {
        $username_update = ",username=NULL";
    }

    // Name
    if (isset($_POST['name']) && $_POST['name'] <> '') {
        $name_update = ",name='{$_POST['name']}'";
    } else {
        $name_update = ",name=NULL";
    }
    // Lastname
    if (isset($_POST['lastname']) && $_POST['lastname'] <> '') {
        $lastname_update = ",lastname='{$_POST['lastname']}'";
    } else {
        $lastname_update = ",lastname=NULL";
    }


    $query = "UPDATE users SET {$useremail_update} {$username_update} {$name_update} {$lastname_update} {$image_update} WHERE id = $id";

    //echo $query;
    //die();

    //$result = mysqli_query($link, $query) or ($error= mysqli_error($link));

    //echo $error;
    //die();

    if(mysqli_query($link, $query) or ($error= mysqli_error($link))){
        //echo "File Sucessfully uploaded";
        header('Location: ../dash-users-list.php');
    }else{
        echo '<script> alert ("No se pudo crear el usuario")</script>';
        header('Location: ../index.php');
    }
}