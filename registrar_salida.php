<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexion.php');
require('fpdf.php');
date_default_timezone_set('America/Lima');

if (isset($_GET['id_boleta'])) {
    $id_boleta = intval($_GET['id_boleta']);

    // Obtener datos
    $sql = "SELECT b.ID_BOLETA, b.FECHA_ENTRADA, c.PLACA, t.NOMBRE AS TIPO, t.TARIFA
            FROM tbboletas b
            INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
            INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
            WHERE b.ID_BOLETA = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_boleta);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $fecha_entrada = new DateTime($row['FECHA_ENTRADA']);
        $fecha_salida = new DateTime();
        $placa = $row['PLACA'];
        $tipo = $row['TIPO'];
        $tarifa = floatval($row['TARIFA']);

        $intervalo = $fecha_entrada->diff($fecha_salida);
        $minutos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;
        $monto = ceil($minutos) * $tarifa;

        // Calcular el IGV (18%)
        $igv = $monto * 0.18;
        $total = $monto + $igv;

        $fecha_salida_str = $fecha_salida->format('Y-m-d H:i:s');

        // Actualizar boleta con fecha de salida y monto
        $sql_update = "UPDATE tbboletas 
                       SET FECHA_SALIDA = ?, MONTO = ? 
                       WHERE ID_BOLETA = ?";
        $stmt_update = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sdi", $fecha_salida_str, $monto, $id_boleta);
        mysqli_stmt_execute($stmt_update);

        // Obtener serie y número de boleta
        $sql_serie = "SELECT SERIE, NUMERO FROM TBSERIE WHERE SERIE = 'B001'";
        $result_serie = mysqli_query($con, $sql_serie);
        $serie = 'B001'; // Serie predeterminada
        $numero = 0;

        if ($result_serie && mysqli_num_rows($result_serie) > 0) {
            $row_serie = mysqli_fetch_assoc($result_serie);
            $serie = $row_serie['SERIE'];
            $numero = $row_serie['NUMERO'] + 1; // Incrementar número de boleta
        }

        // Actualizar número de boleta en la serie
        $sql_update_serie = "UPDATE TBSERIE SET NUMERO = ? WHERE SERIE = 'B001'";
        $stmt_update_serie = mysqli_prepare($con, $sql_update_serie);
        mysqli_stmt_bind_param($stmt_update_serie, "i", $numero);
        mysqli_stmt_execute($stmt_update_serie);

        // Generar PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'BOLETA DE SALIDA - PARKING', 0, 1, 'C');
        $pdf->Ln(5);

        // Boleta info
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Placa:', 0, 0);
        $pdf->Cell(100, 10, $placa, 0, 1);

        $pdf->Cell(50, 10, 'Tipo de Vehiculo:', 0, 0);
        $pdf->Cell(100, 10, $tipo, 0, 1);

        $pdf->Cell(50, 10, 'Fecha de Entrada:', 0, 0);
        $pdf->Cell(100, 10, $fecha_entrada->format('Y-m-d H:i:s'), 0, 1);

        $pdf->Cell(50, 10, 'Fecha de Salida:', 0, 0);
        $pdf->Cell(100, 10, $fecha_salida->format('Y-m-d H:i:s'), 0, 1);

        $pdf->Cell(50, 10, 'Tiempo (min):', 0, 0);
        $pdf->Cell(100, 10, $minutos . ' min', 0, 1);

        // Mostrar el IGV y total
        $pdf->Cell(50, 10, 'IGV (18%):', 0, 0);
        $pdf->Cell(100, 10, 'S/ ' . number_format($igv, 2), 0, 1);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(50, 10, 'Total a Pagar:', 0, 0);
        $pdf->Cell(100, 10, 'S/ ' . number_format($total, 2), 0, 1);

        // Serie y Número de la boleta
        $pdf->Cell(50, 10, 'Serie - Número:', 0, 0);
        $pdf->Cell(100, 10, $serie . '-' . str_pad($numero, 4, "0", STR_PAD_LEFT), 0, 1);

        $pdfFilename = "boleta_salida_" . $placa . "_" . $id_boleta . ".pdf";
        $pdf->Output('D', $pdfFilename); // Descargar

    } else {
        echo "Boleta no encontrada.";
    }
} else {
    echo "ID de boleta no proporcionado.";
}
?>
