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
    // Consulta para obtener los ingresos en el rango de fechas
    $sql = "SELECT 
                b.ID_BOLETA as NUMERO,
                c.PLACA,
                t.NOMBRE AS TIPO,
                b.FECHA_ENTRADA,
                b.FECHA_SALIDA
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
    $pdf->AddPage('L'); // Horizontal para más espacio
    
    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'REPORTE DE INGRESOS', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Tabla
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(20, 10, 'Nº', 1, 0, 'C');
    $pdf->Cell(30, 10, 'PLACA', 1, 0, 'C');
    $pdf->Cell(55, 10, 'FECHA INGRESO', 1, 0, 'C');
    $pdf->Cell(55, 10, 'FECHA SALIDA', 1, 0, 'C');
    $pdf->Cell(40, 10, 'TIPO', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    
    $total_ingresos = 0;
    $contador = 0;
    
    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $contador++;
            $pdf->Cell(20, 10, $fila['NUMERO'], 1, 0, 'C');
            $pdf->Cell(30, 10, $fila['PLACA'], 1, 0, 'C');
            $pdf->Cell(55, 10, date('d/m/Y H:i:s', strtotime($fila['FECHA_ENTRADA'])), 1, 0, 'C');
            $pdf->Cell(55, 10, date('d/m/Y H:i:s', strtotime($fila['FECHA_SALIDA'])), 1, 0, 'C');
            $pdf->Cell(40, 10, $fila['TIPO'], 1, 1, 'C');
        }
    } else {
        $pdf->Cell(200, 10, 'No se encontraron datos para el periodo seleccionado', 1, 1, 'C');
    }
    
    // Información adicional
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, 'Fecha de generacion: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
    $pdf->Cell(0, 5, 'PARKING - Reporte oficial de ingresos', 0, 1, 'L');
    
    // Salida del PDF
    $pdf->Output('D', 'Reporte_Ingresos_' . $fecha_inicio . '_' . $fecha_fin . '.pdf');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos - Parking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="Estilos/reporte_ingresos.css" rel="stylesheet">
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <h1><i class="fas fa-money-bill-wave me-3"></i> Reporte de Ingresos</h1>
            <p class="mb-0">Visualización y exportación de ingresos del estacionamiento</p>
        </div>

       

        <?php
        // Consulta para mostrar en la página
        $sql = "SELECT 
                    b.ID_BOLETA as NUMERO,
                    c.PLACA,
                    t.NOMBRE AS TIPO,
                    b.FECHA_ENTRADA,
                    b.FECHA_SALIDA
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
        
        // Consulta para total de registros
        $sql_total = "SELECT COUNT(*) as TOTAL_REGISTROS
                      FROM tbboletas 
                      WHERE FECHA_SALIDA IS NOT NULL 
                      AND DATE(FECHA_SALIDA) BETWEEN ? AND ?";
        
        $stmt_total = mysqli_prepare($con, $sql_total);
        mysqli_stmt_bind_param($stmt_total, "ss", $fecha_inicio, $fecha_fin);
        mysqli_stmt_execute($stmt_total);
        $resultado_total = mysqli_stmt_get_result($stmt_total);
        $fila_total = mysqli_fetch_assoc($resultado_total);
        
        $total_registros = $fila_total['TOTAL_REGISTROS'] ?? 0;
        ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="summary-card">
                    <div class="summary-number"><?php echo $total_registros; ?></div>
                    <div class="summary-label">Total de Registros</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nº</th>
                        <th>PLACA</th>
                        <th>FECHA INGRESO</th>
                        <th>FECHA SALIDA</th>
                        <th>TIPO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo $fila['NUMERO']; ?></td>
                                <td><?php echo $fila['PLACA']; ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($fila['FECHA_ENTRADA'])); ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($fila['FECHA_SALIDA'])); ?></td>
                                <td><?php echo $fila['TIPO']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron datos para el periodo seleccionado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-primary">
                    <tr>
                        <th colspan="5">TOTAL: <?php echo $total_registros; ?> registros</th>
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