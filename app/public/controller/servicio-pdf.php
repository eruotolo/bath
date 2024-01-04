<?php

global $link;
include ('../layouts/config.php');


if (isset($_GET['id_Servicio'])) {
    $id_Servicio = $_GET['id_Servicio'];

    // Consulta para obtener los datos del servicio desde la base de datos
    $query = "SELECT * FROM servicios SR
        JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
        JOIN bathrooms BT ON SR.id_Bath = BT.id_Bath
        JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente WHERE id_Servicio = $id_Servicio";

    $result = mysqli_query($link, $query) or die(mysqli_error($link));

    // Verificar si se encontraron resultados
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Crear un nuevo objeto TCPDF
        require_once('../assets/tcpdf/tcpdf.php');
        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // Contenido del PDF con los datos del servicio
        $content = '

            <div class="card">
                                    <div class="card-body">

                                        <div class="invoice-title">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="mb-4">
                                                        <img src="../assets/images/logo_zl.svg" alt="" height="160">
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="pt-5">
                                                        <h4 class="font-size-16">Servicio N.º: '.$row['id_Servicio'].'</h4>
                                                        <p class="mb-1 pt-2">Blanco 60 Local 1 - Castro - Chiloé</p>
                                                        <p class="mb-1">
                                                            <i class="mdi mdi-email align-middle me-1"></i> jsanchez@expanda.cl
                                                        </p>
                                                        <p>
                                                            <i class="mdi mdi-phone align-middle me-1"></i> +56 9 9293 7371
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- DATOS DEL CUERPO DEL SERVICIO -->

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h5 class="font-size-15 mb-3"><b>Cliente:</b></h5>
                                                <h5 class="font-size-14 mb-2">'.$row['nombre_Cliente'].'</h5>
                                                <p class="mb-1">'.$row['direccion_Cliente'].' </p>
                                                <p class="mb-1">'.$row['email_Cliente'].' </p>
                                                <p class="mb-1">'.$row['telefono_Cliente'].' </p>
                                                <p class="mb-1">'.$row['ciudad_Cliente'].'  | '.$row['region_Cliente'].'</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <h5 class="font-size-15 mb-3"><b>Información:</b></h5>
                                                    <p class="mb-1">
                                                        <b>Número de Contrato:</b>  '.$row['id_Contrato'].'
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Obra: </b> '.$row['obra_Contrato'].'
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Fecha del Servicio:</b> '.date("d/m/Y", strtotime($row['fecha_Servicio'])).'
                                                    </p>

                                                    <p class="mb-1">
                                                        <b>Tipo de Servicio:</b> '.$row['tipo_Servicio'].'
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Codigo Baño:</b>  '.$row['codigo_Bath'].'
                                                    </p>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="py-2 mt-3">
                                            <h5 class="font-size-15">Observaciones</h5>
                                        </div>

                                        <div class="p-4 border rounded">
                                            <p>'.$row['observaciones_Servicio'].'</p>
                                        </div>

                                       

                                    </div>
                                </div>
           
        ';

        // Escribir el contenido HTML en el PDF
        $pdf->writeHTML($content, true, false, true, false, '');

        // Guardar el PDF en una ubicación temporal
        $timestamp = time(); // Obtener la marca de tiempo actual
        $tempPath = __DIR__ . '/../uploads/archive/servicio_' . $row['id_Servicio'] . '_' . $timestamp . '.pdf';
        $pdf->Output($tempPath, 'F');


        // Dirección de correo del cliente
        $to = $row['email_Cliente'];

        // Asunto y mensaje del correo electrónico
        $subject = 'Tu factura/servicio adjunto';
        $message = 'Adjunto encontrarás tu factura/servicio.';

        // Encabezados del correo electrónico
        $headers = 'From: tu_email@dominio.com' . "\r\n" .
            'Reply-To: tu_email@dominio.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n";

        // Adjuntar el archivo PDF
        $file_attached = $tempPath;

        // Enviar el correo electrónico con el archivo adjunto
        mail($to, $subject, $message, $headers, $file_attached);

        // Devolver una respuesta al cliente indicando que el PDF se ha generado y enviado
        echo 'El PDF se ha generado y enviado al cliente.';

        header("Location: ../dash-services-item.php?id_Servicio=$id_Servicio");

    } else {
        // Si no se encontraron datos para el id_Servicio
        echo 'No se encontraron datos para el servicio.';
    }
} else {
    // Si no se proporcionó el parámetro id_Servicio
    echo 'No se proporcionó el parámetro id_Servicio.';
}