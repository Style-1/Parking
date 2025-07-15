<?php
include('conexion.php');
date_default_timezone_set('America/Lima');

// Obtener fecha inicial y final para el filtro
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-6 days', strtotime($fecha_fin)));

// Consultar estadísticas diarias para el rango seleccionado
$sql_estadisticas = "SELECT 
                    DATE(FECHA_SALIDA) as fecha,
                    COUNT(*) as total_boletas,
                    SUM(MONTO) as ingreso_total,
                    AVG(TIMESTAMPDIFF(MINUTE, FECHA_ENTRADA, FECHA_SALIDA)) as promedio_minutos
                FROM tbboletas
                WHERE FECHA_SALIDA IS NOT NULL 
                AND DATE(FECHA_SALIDA) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                GROUP BY DATE(FECHA_SALIDA)
                ORDER BY fecha ASC";
$result_estadisticas = mysqli_query($con, $sql_estadisticas);

// Consultar distribución por tipo para el rango seleccionado
$sql_tipos = "SELECT 
                DATE(b.FECHA_SALIDA) as fecha,
                t.NOMBRE as tipo,
                COUNT(*) as cantidad,
                SUM(b.MONTO) as ingreso_tipo
            FROM tbboletas b
            JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
            JOIN tbtipos t ON c.TIPO = t.ID_TIPO
            WHERE b.FECHA_SALIDA IS NOT NULL 
            AND DATE(b.FECHA_SALIDA) BETWEEN '$fecha_inicio' AND '$fecha_fin'
            GROUP BY DATE(b.FECHA_SALIDA), t.NOMBRE
            ORDER BY fecha ASC, t.NOMBRE";
$result_tipos = mysqli_query($con, $sql_tipos);

// Preparar datos para gráficos
$fechas = [];
$ingresos = [];
$vehiculos = [];
$tiempos = [];

$datos_tipos = [];

if (mysqli_num_rows($result_estadisticas) > 0) {
    while ($row = mysqli_fetch_assoc($result_estadisticas)) {
        $fechas[] = $row['fecha'];
        $ingresos[] = floatval($row['ingreso_total']);
        $vehiculos[] = intval($row['total_boletas']);
        $tiempos[] = round(floatval($row['promedio_minutos']), 1);
    }
}

// Preparar datos de distribución por tipo
if (mysqli_num_rows($result_tipos) > 0) {
    while ($row = mysqli_fetch_assoc($result_tipos)) {
        if (!isset($datos_tipos[$row['fecha']])) {
            $datos_tipos[$row['fecha']] = [];
        }
        $datos_tipos[$row['fecha']][$row['tipo']] = [
            'cantidad' => intval($row['cantidad']),
            'ingreso' => floatval($row['ingreso_tipo'])
        ];
    }
}

// Calcular totales generales
$sql_totales = "SELECT 
                COUNT(*) as total_boletas,
                SUM(MONTO) as ingreso_total,
                AVG(TIMESTAMPDIFF(MINUTE, FECHA_ENTRADA, FECHA_SALIDA)) as promedio_minutos
            FROM tbboletas
            WHERE FECHA_SALIDA IS NOT NULL 
            AND DATE(FECHA_SALIDA) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
$result_totales = mysqli_query($con, $sql_totales);
$totales = mysqli_fetch_assoc($result_totales);

// Datos JSON para los gráficos
$json_ingresos = json_encode($ingresos);
$json_fechas = json_encode($fechas);
$json_vehiculos = json_encode($vehiculos);
$json_tiempos = json_encode($tiempos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas por Día - Parking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="Estilos/reporte_estadisticas.css" rel="stylesheet">
</head>

<body>
    <div class="stats-container">
        <div class="header">
            <h1><i class="fas fa-chart-pie me-3"></i> Estadísticas por Día</h1>
        </div>

        <!-- Filtro de fechas -->
        <div class="filter-card">
            

        <!-- Resumen -->
        <div class="stats-card">
            <h3><i class="fas fa-chart-line me-2"></i> Resumen del Período</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="value"><?php echo number_format($totales['total_boletas']); ?></div>
                        <div class="label">Total de Boletas</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="value">S/ <?php echo number_format($totales['ingreso_total'], 2); ?></div>
                        <div class="label">Ingresos Totales</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="value"><?php echo round($totales['promedio_minutos'], 0); ?> min</div>
                        <div class="label">Tiempo Promedio de Estancia</div>
                    </div>
                </div>
            </div>
        </div>

        

            <!-- Tabla detallada -->
            <div class="col-lg-6">
                <div class="stats-card">
                    <h3><i class="fas fa-table me-2"></i> Datos Detallados</h3>
                    <div class="table-responsive">
                        <table class="table table-hover table-stats">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Vehículos</th>
                                    <th>Ingresos</th>
                                    <th>Tiempo Prom</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                mysqli_data_seek($result_estadisticas, 0);
                                if (mysqli_num_rows($result_estadisticas) > 0):
                                    while ($row = mysqli_fetch_assoc($result_estadisticas)):
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo $row['total_boletas']; ?></td>
                                    <td>S/ <?php echo number_format($row['ingreso_total'], 2); ?></td>
                                    <td><?php echo round($row['promedio_minutos'], 0); ?> min</td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

       

        <div class="text-center">
            <a href="adm.php" class="btn btn-outline-primary back-btn">
                <i class="fas fa-arrow-left me-2"></i> Volver al Panel de Administración
            </a>
        </div>
    </div>

    <!-- Scripts para los gráficos -->
    <script>
        // Configuración común de colores
        const primaryColor = '#3498db';
        const secondaryColor = '#f1c40f';
        const tertiaryColor = '#2ecc71';

        // Datos de PHP
        const fechas = <?php echo $json_fechas; ?>;
        const fechasFormateadas = fechas.map(fecha => {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
        });
        
        const ingresos = <?php echo $json_ingresos; ?>;
        const vehiculos = <?php echo $json_vehiculos; ?>;
        const tiempos = <?php echo $json_tiempos; ?>;

        // Gráfico de Ingresos
        const ctxIngresos = document.getElementById('ingresos-chart').getContext('2d');
        new Chart(ctxIngresos, {
            type: 'bar',
            data: {
                labels: fechasFormateadas,
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: ingresos,
                    backgroundColor: primaryColor,
                    borderColor: primaryColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Vehículos
        const ctxVehiculos = document.getElementById('vehiculos-chart').getContext('2d');
        new Chart(ctxVehiculos, {
            type: 'line',
            data: {
                labels: fechasFormateadas,
                datasets: [{
                    label: 'Cantidad de Vehiculos',
                    data: vehiculos,
                    backgroundColor: 'rgba(241, 196, 15, 0.2)',
                    borderColor: secondaryColor,
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Gráfico de Tiempo Promedio
        const ctxTiempo = document.getElementById('tiempo-chart').getContext('2d');
        new Chart(ctxTiempo, {
            type: 'line',
            data: {
                labels: fechasFormateadas,
                datasets: [{
                    label: 'Tiempo Promedio (min)',
                    data: tiempos,
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: tertiaryColor,
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>