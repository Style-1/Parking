<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexion.php');
require('fpdf.php');
date_default_timezone_set('America/Lima');

// Obtener parámetros de fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Si se solicita generar PDF
if (isset($_GET['pdf'])) {
    // Consulta para obtener las boletas simplificadas en el rango de fechas
    $sql = "SELECT 
                b.ID_BOLETA,
                b.ID_BOLETA AS NUMERO_BOLETA,
                c.PLACA,
                t.NOMBRE AS TIPO,
                b.MONTO
            FROM tbboletas b
            INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
            INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
            WHERE b.FECHA_SALIDA IS NOT NULL 
            AND DATE(b.FECHA_SALIDA) BETWEEN ? AND ?
            ORDER BY b.ID_BOLETA";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $fecha_inicio, $fecha_fin);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'REPORTE DE BOLETAS', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Tabla
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(20, 10, 'Nº', 1, 0, 'C');
    $pdf->Cell(40, 10, 'BOLETA', 1, 0, 'C');
    $pdf->Cell(40, 10, 'PLACA', 1, 0, 'C');
    $pdf->Cell(40, 10, 'TIPO', 1, 0, 'C');
    $pdf->Cell(40, 10, 'MONTO (S/)', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    
    $contador = 1;
    $total_monto = 0;
    
    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            // Para el reporte solo mostramos los primeros 5 dígitos de la boleta
            $boleta_formato = 'B-' . str_pad(substr($fila['NUMERO_BOLETA'], 0, 5), 5, '0', STR_PAD_LEFT);
            
            $pdf->Cell(20, 10, $contador, 1, 0, 'C');
            $pdf->Cell(40, 10, $boleta_formato, 1, 0, 'C');
            $pdf->Cell(40, 10, $fila['PLACA'], 1, 0, 'C');
            $pdf->Cell(40, 10, $fila['TIPO'], 1, 0, 'C');
            $pdf->Cell(40, 10, 'S/ ' . number_format($fila['MONTO'], 2), 1, 1, 'R');
            
            $contador++;
            $total_monto += $fila['MONTO'];
        }
    } else {
        $pdf->Cell(180, 10, 'No se encontraron boletas para el periodo seleccionado', 1, 1, 'C');
    }
    
    // Fila de totales
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(140, 10, 'TOTAL (' . ($contador - 1) . ' boletas)', 1, 0, 'R', true);
    $pdf->Cell(40, 10, 'S/ ' . number_format($total_monto, 2), 1, 1, 'R', true);
    
    // Información adicional
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, 'Fecha de generacion: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
    $pdf->Cell(0, 5, 'PARKING - Reporte de boletas', 0, 1, 'L');
    
    // Salida del PDF
    $pdf->Output('D', 'Reporte_Simple_Boletas_' . $fecha_inicio . '_' . $fecha_fin . '.pdf');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Simple de Boletas - Parking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="Estilos/reporte_boletas.css" rel="stylesheet">
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <h1><i class="fas fa-receipt me-3"></i> Reportes Boletas</h1>
            <p class="mb-0">Visualización simplificada y exportación de boletas emitidas</p>
        </div>


        <?php
        // Consulta para mostrar en la página
        $sql = "SELECT 
                    b.ID_BOLETA,
                    b.ID_BOLETA AS NUMERO_BOLETA,
                    c.PLACA,
                    t.NOMBRE AS TIPO,
                    b.MONTO
                FROM tbboletas b
                INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
                INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
                WHERE b.FECHA_SALIDA IS NOT NULL 
                AND DATE(b.FECHA_SALIDA) BETWEEN ? AND ?
                ORDER BY b.ID_BOLETA";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $fecha_inicio, $fecha_fin);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        // Consulta para totales
        $sql_total = "SELECT COUNT(*) as TOTAL_BOLETAS, SUM(MONTO) as TOTAL_MONTO
                      FROM tbboletas 
                      WHERE FECHA_SALIDA IS NOT NULL 
                      AND DATE(FECHA_SALIDA) BETWEEN ? AND ?";
        
        $stmt_total = mysqli_prepare($con, $sql_total);
        mysqli_stmt_bind_param($stmt_total, "ss", $fecha_inicio, $fecha_fin);
        mysqli_stmt_execute($stmt_total);
        $resultado_total = mysqli_stmt_get_result($stmt_total);
        $fila_total = mysqli_fetch_assoc($resultado_total);
        
        $total_boletas = $fila_total['TOTAL_BOLETAS'] ?? 0;
        $total_monto = $fila_total['TOTAL_MONTO'] ?? 0;
        ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="summary-card">
                    <div class="summary-number"><?php echo $total_boletas; ?></div>
                    <div class="summary-label">Boletas Emitidas</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-card">
                    <div class="summary-number">S/ <?php echo number_format($total_monto, 2); ?></div>
                    <div class="summary-label">Monto Total</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nº</th>
                        <th>BOLETA</th>
                        <th>PLACA</th>
                        <th>TIPO</th>
                        <th class="text-end">MONTO (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): 
                        $contador = 1;
                    ?>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): 
                            // Para el reporte solo mostramos los primeros 5 dígitos de la boleta
                            $boleta_formato = 'B-' . str_pad(substr($fila['NUMERO_BOLETA'], 0, 5), 5, '0', STR_PAD_LEFT);
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $contador++; ?></td>
                                <td><?php echo $boleta_formato; ?></td>
                                <td><?php echo $fila['PLACA']; ?></td>
                                <td><?php echo $fila['TIPO']; ?></td>
                                <td class="text-end">S/ <?php echo number_format($fila['MONTO'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron boletas para el periodo seleccionado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-primary">
                    <tr>
                        <th colspan="4" class="text-end">TOTAL (<?php echo $total_boletas; ?> boletas)</th>
                        <th class="text-end">S/ <?php echo number_format($total_monto, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer-btns">
            <a href="adm.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
            <a href="<?php echo $_SERVER['PHP_SELF'] . '?fecha_inicio=' . $fecha_inicio . '&fecha_fin=' . $fecha_fin . '&pdf=1'; ?>" class="btn btn-danger action-btn">
                <i class="fas fa-file-pdf me-2"></i> Exportar PDF
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>