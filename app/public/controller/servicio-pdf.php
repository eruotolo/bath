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
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 70%">
                            <img src="../assets/images/logo_zl.png" style="height: 100px">
                            <p style="font-size: 8px; line-height: 8px;"></p>
                        </th>
                        <th style="width: 30%">
                            <h4 style="font-size: 10px; font-weight: 600;">Servicio N.º: '.$row['id_Servicio'].'</h4>
                            <p style="font-size: 8px; line-height: 8px; ">Blanco 60 Local 1 - Castro - Chiloé</p>
                            <p style="font-size: 8px; line-height: 8px; ">jsanchez@expanda.cl</p>
                            <p style="font-size: 8px; line-height: 8px; ">+56 9 9293 7371</p>
                            <p style="font-size: 8px; line-height: 8px;"></p>
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 70%; border-top: 0,5px solid #dedfdf;">
                            <h5 style="font-size: 10px; font-weight: 600;"><b>Cliente:</b></h5>
                            <h5 style="font-size: 10px; font-weight: 600;">'.$row['nombre_Cliente'].'</h5>
                            <p style="font-size: 8px; line-height: 8px;" >'.$row['direccion_Cliente'].' </p>
                            <p style="font-size: 8px; line-height: 8px;" >'.$row['email_Cliente'].' </p>
                            <p style="font-size: 8px; line-height: 8px;" >'.$row['telefono_Cliente'].' </p>
                            <p style="font-size: 8px; line-height: 8px;" >'.$row['ciudad_Cliente'].'  | '.$row['region_Cliente'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"></p>
                        </th>
                        <th style="width: 30%; border-top: 0,5px solid #dedfdf;">
                            <h5 style="font-size: 10px; font-weight: 600;"><b>Información:</b></h5>
                            <p style="font-size: 8px; line-height: 8px;"><b>Número de Contrato:</b>  '.$row['id_Contrato'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"><b>Obra: </b> '.$row['obra_Contrato'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"><b>Fecha del Servicio:</b> '.date("d/m/Y", strtotime($row['fecha_Servicio'])).'</p>
                            <p style="font-size: 8px; line-height: 8px;"><b>Tipo de Servicio:</b> '.$row['tipo_Servicio'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"><b>Codigo Baño:</b>  '.$row['codigo_Bath'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"></p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 100%; border: 0,5px solid #dedfdf; border-radius: 10px">
                            <h5 style="font-size: 10px; font-weight: 600;">Observaciones:</h5>
                            <p style="font-size: 8px; line-height: 10px;">'.$row['observaciones_Servicio'].'</p>
                            <p style="font-size: 8px; line-height: 8px;"></p>
                        </td>
                    </tr>
                </tbody>
            </table>     
        ';
        // Línea horizontal debajo de cada fila
        //$content = str_replace('</tr>', '<hr style="border: 0.5px solid gray; margin: 10px; padding: 10px"> </tr>', $content);

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