<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexion.php');
require('fpdf.php');
date_default_timezone_set('America/Lima');

if (isset($_GET['id_boleta'])) {
    $id_boleta = intval($_GET['id_boleta']);

    // Verificar si existe la columna TARIFA
    $check_column = mysqli_query($con, "SHOW COLUMNS FROM tbtipos LIKE 'TARIFA'");
    $tarifa_column_exists = mysqli_num_rows($check_column) > 0;
    
    // Obtener datos de la boleta, cliente y tipo de vehículo
    if ($tarifa_column_exists) {
        $sql = "SELECT b.ID_BOLETA, b.FECHA_ENTRADA, c.PLACA, t.NOMBRE AS TIPO, t.TARIFA
                FROM tbboletas b
                INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
                INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
                WHERE b.ID_BOLETA = ?";
    } else {
        $sql = "SELECT b.ID_BOLETA, b.FECHA_ENTRADA, c.PLACA, t.NOMBRE AS TIPO
                FROM tbboletas b
                INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
                INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
                WHERE b.ID_BOLETA = ?";
    }
            
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_boleta);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Datos del vehículo y tiempos
        $fecha_entrada = new DateTime($row['FECHA_ENTRADA']);
        $fecha_salida = new DateTime(); // Ahora
        $placa = $row['PLACA'];
        $tipo = $row['TIPO'];
        
        // Definir tarifas según el tipo de vehículo
        $tarifas = [
            'Moto' => 0.10,
            'Auto' => 0.20,
            'Camion' => 0.30
        ];
        
        // Usar tarifa de la base de datos si existe la columna, de lo contrario usar el arreglo predefinido
        if ($tarifa_column_exists && isset($row['TARIFA'])) {
            $tarifa = floatval($row['TARIFA']);
        } else {
            $tarifa = isset($tarifas[$tipo]) ? $tarifas[$tipo] : 0.20; // Tarifa por defecto si no se encuentra
        }

        // Calcular tiempo y monto
        $intervalo = $fecha_entrada->diff($fecha_salida);
        $minutos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;
        $subtotal = ceil($minutos) * $tarifa;

        // Calcular IGV (18%)
        $igv = $subtotal * 0.18;
        $total = $subtotal + $igv;

        $fecha_salida_str = $fecha_salida->format('Y-m-d H:i:s');

        // Actualizar boleta con fecha de salida y monto
        $sql_update = "UPDATE tbboletas 
                       SET FECHA_SALIDA = ?, MONTO = ? 
                       WHERE ID_BOLETA = ?";
        $stmt_update = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sdi", $fecha_salida_str, $total, $id_boleta);
        
        if (mysqli_stmt_execute($stmt_update)) {
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

            // Crear PDF
            $pdf = new FPDF();
            $pdf->AddPage();
            
            // Cabecera del boleto
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'BOLETO DE VENTA', 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'PARKING SYSTEM', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 5, 'AV. 28 DE JULIO 156', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Lima, Peru', 0, 1, 'C');
            $pdf->Ln(5);

            // Línea separadora
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);

            // Serie y número del boleto
            $numero_formateado = str_pad($numero, 8, "0", STR_PAD_LEFT);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Serie: ' . $serie . '-' . $numero_formateado, 0, 1, 'C');
            $pdf->Ln(5);

            // Detalles del servicio
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Placa:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, $placa, 0, 1);

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Fecha:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, $fecha_salida->format('Y-m-d'), 0, 1);

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Hora Entrada:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, $fecha_entrada->format('H:i:s'), 0, 1);

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Hora Salida:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, $fecha_salida->format('H:i:s'), 0, 1);

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Tiempo:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, $minutos . ' minutos', 0, 1);

            // Línea separadora
            $pdf->Line(10, $pdf->GetY() + 5, 200, $pdf->GetY() + 5);
            $pdf->Ln(10);

            // Montos
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'Subtotal:', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, 'S/ ' . number_format($subtotal, 2), 0, 1);

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, 8, 'IGV (18%):', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, 'S/ ' . number_format($igv, 2), 0, 1);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Total:', 0, 0);
            $pdf->Cell(0, 10, 'S/ ' . number_format($total, 2), 0, 1);

            // Pie de página
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 5, 'Gracias por su preferencia', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Vuelva pronto!', 0, 1, 'C');
            
            

            $pdfFilename = "boleta_venta_" . $placa . "_" . $id_boleta . ".pdf";
            $pdf->Output('D', $pdfFilename); // Descargar automáticamente
            exit();
        } else {
            echo "Error al actualizar la boleta: " . mysqli_error($con);
        }
    } else {
        echo "Boleta no encontrada o ya ha sido pagada.";
    }
} else {
    echo "ID de boleta no proporcionado.";
}
?>