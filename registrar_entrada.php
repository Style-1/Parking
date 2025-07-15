<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexion.php');
require('fpdf.php');

if (isset($_POST['placa']) && isset($_POST['tipo'])) {
    $placa = $_POST['placa'];
    $tipo = $_POST['tipo'];
    date_default_timezone_set('America/Lima');

    // Obtener nombre del tipo
    // Obtener nombre del tipo
    $sql_tipo = "SELECT NOMBRE FROM tbtipos WHERE ID_TIPO = $tipo";
    $result_tipo = mysqli_query($con, $sql_tipo);

    if ($result_tipo && mysqli_num_rows($result_tipo) > 0) {
        $row = mysqli_fetch_assoc($result_tipo);
        $nombre_tipo = $row['NOMBRE'];

        // Insertar en TBCLIENTES
        $sql_cliente = "INSERT INTO tbclientes (PLACA, TIPO, FECHAYHORA)
                        VALUES ('$placa', '$tipo', NOW())";
        if (mysqli_query($con, $sql_cliente)) {
            $id_cliente = mysqli_insert_id($con); // Obtener ID del nuevo cliente
            $fecha_entrada = date("Y-m-d H:i:s");

            // Insertar en TBBOLETAS
            $sql_boleta = "INSERT INTO tbboletas (ID_CLIENTE, FECHA_ENTRADA) 
                           VALUES ('$id_cliente', '$fecha_entrada')";
            if (mysqli_query($con, $sql_boleta)) {

                // Crear PDF
                $pdf = new FPDF();
                $pdf->AddPage();

                $pdf->Image('LOGO.jpg', 10, 8, 43);
                $pdf->SetFillColor(240, 248, 255);
                $pdf->Rect(0, 0, 210, 50, 'F');

                $pdf->SetDrawColor(0, 0, 225);
                $pdf->SetLineWidth(0.5);
                $pdf->Line(10, 50, 200, 50);

                $pdf->SetY(15);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->SetFont('Arial', 'B', 28);
                $pdf->Cell(150, 10, 'PARKING', 0, 1, 'R');

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->Cell(150, 8, 'SISTEMA DE GESTION DE ESTACIONAMIENTO', 0, 1, 'R');

                $pdf->SetY(30);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(150, 10, '[] AV. 28 DE JULIO 156', 0, 1, 'R');

                $pdf->Ln(15);
                $pdf->SetFillColor(245, 245, 255);
                $pdf->Rect(10, 60, 190, 60, 'F');
                $pdf->SetLineWidth(0.2);
                $pdf->SetDrawColor(0, 0, 225);
                $pdf->Rect(10, 60, 190, 60, 'D');

                $pdf->SetXY(15, 65);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(180, 8, 'DATOS DEL REGISTRO', 0, 1, 'L');
                $pdf->Line(15, 73, 195, 73);
                $pdf->Ln(2);

                $y_pos = 80;

                $pdf->SetXY(15, $y_pos);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(16, 8, ' Placa:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(50, 9, $placa, 0, 1);
                $y_pos += 10;

                $pdf->SetXY(15, $y_pos);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(14, 8, ' Tipo:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(50, 9, $nombre_tipo, 0, 1);
                $y_pos += 10;

                $pdf->SetXY(15, $y_pos);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(17, 8, ' Fecha:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(50, 9, date('Y-m-d'), 0, 1);
                $y_pos += 10;

                $pdf->SetXY(15, $y_pos);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(14, 8, ' Hora:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(50, 9, date('H:i:s'), 0, 1);

                $pdf->SetY(140);
                $pdf->SetFillColor(240, 248, 255);
                $pdf->Rect(10, 140, 190, 20, 'F');
                $pdf->SetDrawColor(0, 0, 225);
                $pdf->Rect(10, 140, 190, 20, 'D');

                $pdf->SetXY(10, 145);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 225);
                $pdf->Cell(190, 10, 'Gracias por registrar su vehiculo.', 0, 1, 'C');

                $pdf->SetY(180);
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(128, 128, 128);
                $pdf->Cell(0, 5, 'Este documento es un comprobante de ingreso al estacionamiento.', 0, 1, 'C');
                $pdf->Cell(0, 5, 'Por favor, consérvelo hasta su salida.', 0, 1, 'C');

                $pdf->SetY(200);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(0, 5, 'TICKET #: ' . date('Ymd') . '-' . rand(1000, 9999), 0, 1, 'C');

                $pdfFilename = "registro_cliente_" . $placa . ".pdf";
                $pdf->Output('D', $pdfFilename);
                exit();

            } else {
                echo "Error al insertar boleta: " . mysqli_error($con);
            }
        } else {
            echo "Error al insertar cliente: " . mysqli_error($con);
        }
    } else {
        echo "Tipo de vehículo no encontrado.";
    }
} else {
    echo "Faltan datos: Placa o Tipo no están definidos.";
}
?>
