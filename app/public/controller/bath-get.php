<?php

include ('../layouts/config.php');
global $link;

$id_Bath = $_POST['id_Bath'];

$sql = "SELECT * FROM bathrooms WHERE id_Bath = $id_Bath";
$result = mysqli_query($link, $sql);
if(mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_array($result);
    $bathData = array(
        'id_Bath' => $row['id_Bath'],
        'codigo_Bath' => $row['codigo_Bath'],
        'fechaCompra_Bath' => $row['fechaCompra_Bath'],
        'observacion_Bath' => $row['observacion_Bath'],
        'estado_Bath' => $row['estado_Bath']
    );

    echo json_encode($bathData);

}else{
    echo "No se encontró datos para este baño";
}