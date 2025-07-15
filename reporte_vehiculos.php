<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexion.php');
require('fpdf.php');
date_default_timezone_set('America/Lima');

// Si se solicita generar PDF
if (isset($_GET['pdf'])) {
    // Consulta para obtener los vehículos actualmente en el parking
    $sql = "SELECT 
                b.ID_BOLETA,
                c.PLACA,
                t.NOMBRE AS TIPO,
                b.FECHA_ENTRADA,
                TIMESTAMPDIFF(MINUTE, b.FECHA_ENTRADA, NOW()) AS TIEMPO_MIN,
                t.TARIFA,
                (TIMESTAMPDIFF(MINUTE, b.FECHA_ENTRADA, NOW()) * t.TARIFA) AS MONTO_ACTUAL
            FROM tbboletas b
            INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
            INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
            WHERE b.FECHA_SALIDA IS NULL
            ORDER BY b.FECHA_ENTRADA";
    
    $resultado = mysqli_query($con, $sql);
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage('L'); // Horizontal
    
    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'REPORTE DE VEHICULOS EN PARKING', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Placa', 1, 0, 'C');
    $pdf->Cell(35, 10, 'Tipo', 1, 0, 'C');
    $pdf->Cell(45, 10, 'Fecha Entrada', 1, 0, 'C');
    $pdf->Cell(35, 10, 'Tiempo (min)', 1, 0, 'C');
    $pdf->Cell(25, 10, 'Tarifa', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Monto Actual', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    
    $total_vehiculos = 0;
    $total_monto = 0;
    
    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $pdf->Cell(20, 10, $fila['ID_BOLETA'], 1, 0, 'C');
            $pdf->Cell(30, 10, $fila['PLACA'], 1, 0, 'C');
            $pdf->Cell(35, 10, $fila['TIPO'], 1, 0, 'C');
            $pdf->Cell(45, 10, date('d/m/Y H:i:s', strtotime($fila['FECHA_ENTRADA'])), 1, 0, 'C');
            $pdf->Cell(35, 10, $fila['TIEMPO_MIN'], 1, 0, 'C');
            $pdf->Cell(25, 10, 'S/ ' . number_format($fila['TARIFA'], 2), 1, 0, 'C');
            $pdf->Cell(40, 10, 'S/ ' . number_format($fila['MONTO_ACTUAL'], 2), 1, 1, 'R');
            
            $total_vehiculos++;
            $total_monto += $fila['MONTO_ACTUAL'];
        }
    } else {
        $pdf->Cell(230, 10, 'No hay vehículos actualmente en el parking', 1, 1, 'C');
    }
    
    // Fila de totales
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(190, 10, 'TOTAL (' . $total_vehiculos . ' vehiculos)', 1, 0, 'R', true);
    $pdf->Cell(40, 10, 'S/ ' . number_format($total_monto, 2), 1, 1, 'R', true);
    
    // Información adicional
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, 'Nota: Los montos mostrados son calculados al momento de la generacion del reporte.', 0, 1, 'L');
    $pdf->Cell(0, 5, 'PARKING - Reporte de vehiculos en parking', 0, 1, 'L');
    
    // Salida del PDF
    $pdf->Output('D', 'Reporte_Vehiculos_' . date('Y-m-d_H-i-s') . '.pdf');
    exit();
}

// Consulta para mostrar en la página
$sql = "SELECT 
            b.ID_BOLETA,
            c.PLACA,
            t.NOMBRE AS TIPO,
            b.FECHA_ENTRADA,
            TIMESTAMPDIFF(MINUTE, b.FECHA_ENTRADA, NOW()) AS TIEMPO_MIN,
            t.TARIFA,
            (TIMESTAMPDIFF(MINUTE, b.FECHA_ENTRADA, NOW()) * t.TARIFA) AS MONTO_ACTUAL
        FROM tbboletas b
        INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
        INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
        WHERE b.FECHA_SALIDA IS NULL
        ORDER BY b.FECHA_ENTRADA";

$resultado = mysqli_query($con, $sql);

// Consulta para agrupar por tipo
$sql_por_tipo = "SELECT 
                    t.NOMBRE AS TIPO,
                    COUNT(*) AS CANTIDAD
                FROM tbboletas b
                INNER JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
                INNER JOIN tbtipos t ON c.TIPO = t.ID_TIPO
                WHERE b.FECHA_SALIDA IS NULL
                GROUP BY t.NOMBRE";

$resultado_por_tipo = mysqli_query($con, $sql_por_tipo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Vehículos en Parking - Parking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="Estilos/reporte_vehiculos.css" rel="stylesheet">
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <h1><i class="fas fa-car me-3"></i> Reporte de Vehículos en Parking</h1>
            <p class="mb-0">Información en tiempo real de vehículos actualmente en el estacionamiento</p>
        </div>

        <div class="content-card">
            <div class="row mb-4">
                <?php
                $total_vehiculos = mysqli_num_rows($resultado);
                $total_monto = 0;
                if ($total_vehiculos > 0) {
                    // Reset pointer
                    mysqli_data_seek($resultado, 0);
                    while ($fila = mysqli_fetch_assoc($resultado)) {
                        $total_monto += $fila['MONTO_ACTUAL'];
                    }
                    // Reset pointer again for later use
                    mysqli_data_seek($resultado, 0);
                }
                ?>
                <div class="col-md-6">
                    <div class="summary-card">
                        <div class="summary-number"><?php echo $total_vehiculos; ?></div>
                        <div class="summary-label">Vehículos en Parking</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card">
                        <div class="summary-number">S/ <?php echo number_format($total_monto, 2); ?></div>
                        <div class="summary-label">Monto Actual Acumulado</div>
                    </div>
                </div>
            </div>

            <!-- Gráfico por tipo de vehículo -->
            <div class="chart-container mb-4">
                <h4 class="mb-4 text-center">Vehículos por Tipo</h4>
                <div class="tipo-chart">
                    <?php
                    $max_cantidad = 0;
                    $tipos = [];
                    
                    if (mysqli_num_rows($resultado_por_tipo) > 0) {
                        while ($fila = mysqli_fetch_assoc($resultado_por_tipo)) {
                            $tipos[] = $fila;
                            if ($fila['CANTIDAD'] > $max_cantidad) {
                                $max_cantidad = $fila['CANTIDAD'];
                            }
                        }
                        
                        foreach ($tipos as $tipo) {
                            $altura = ($tipo['CANTIDAD'] / $max_cantidad) * 250;
                            if ($altura < 20) $altura = 20; // Altura mínima para visualización
                            
                            echo '<div class="chart-bar" style="height: ' . $altura . 'px;">';
                            echo '<div class="chart-value">' . $tipo['CANTIDAD'] . '</div>';
                            echo '<div class="chart-label">' . $tipo['TIPO'] . '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay datos disponibles</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Tipo</th>
                            <th>Fecha Entrada</th>
                            <th>Tiempo (min)</th>
                            <th>Tarifa</th>
                            <th>Monto Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado) > 0): ?>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?php echo $fila['ID_BOLETA']; ?></td>
                                    <td><?php echo $fila['PLACA']; ?></td>
                                    <td><?php echo $fila['TIPO']; ?></td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($fila['FECHA_ENTRADA'])); ?></td>
                                    <td><?php echo $fila['TIEMPO_MIN']; ?></td>
                                    <td>S/ <?php                                    echo number_format($fila['TARIFA'], 2); ?></td>
                                    <td>S/ <?php echo number_format($fila['MONTO_ACTUAL'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay vehículos actualmente en el parking.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="footer-btns">
                <a href="?pdf=1" class="btn btn-success action-btn">
                    <i class="fas fa-file-pdf me-2"></i> Generar PDF
                </a>
                <a href="entrada_parking.php" class="btn btn-secondary action-btn">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Menú
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
