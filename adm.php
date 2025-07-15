<?php
include('conexion.php');
date_default_timezone_set('America/Lima');

// Consulta para contar vehículos actualmente en el estacionamiento (sin fecha de salida)
$sql_vehiculos_actuales = "SELECT COUNT(*) as total FROM tbboletas WHERE FECHA_SALIDA IS NULL";
$result_vehiculos = mysqli_query($con, $sql_vehiculos_actuales);
$row_vehiculos = mysqli_fetch_assoc($result_vehiculos);
$total_vehiculos = $row_vehiculos['total'];

// Consulta para obtener ingresos del día actual
$hoy = date('Y-m-d');
$sql_ingresos_hoy = "SELECT SUM(MONTO) as total FROM tbboletas WHERE DATE(FECHA_SALIDA) = '$hoy'";
$result_ingresos = mysqli_query($con, $sql_ingresos_hoy);
$row_ingresos = mysqli_fetch_assoc($result_ingresos);
$ingresos_hoy = $row_ingresos['total'] ? number_format($row_ingresos['total'], 2) : '0.00';

// Consulta para obtener cantidad de boletas emitidas hoy
$sql_boletas_hoy = "SELECT COUNT(*) as total FROM tbboletas WHERE DATE(FECHA_SALIDA) = '$hoy'";
$result_boletas = mysqli_query($con, $sql_boletas_hoy);
$row_boletas = mysqli_fetch_assoc($result_boletas);
$boletas_hoy = $row_boletas['total'];

// Consulta para agrupar vehículos actuales por tipo
$sql_por_tipo = "SELECT t.NOMBRE, COUNT(*) as cantidad 
                FROM tbboletas b
                JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
                JOIN tbtipos t ON c.TIPO = t.ID_TIPO
                WHERE b.FECHA_SALIDA IS NULL
                GROUP BY t.NOMBRE";
$result_por_tipo = mysqli_query($con, $sql_por_tipo);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Parking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="Estilos/adm.css" rel="stylesheet">
</head>

<body>
    <div class="admin-container">
        <div class="header">
            <h1><i class="fas fa-chart-line me-3"></i> Panel de Administración</h1>
            <p class="mb-0">Sistema de Gestión de Estacionamiento</p>
        </div>

        <div class="row">
            <!-- Reportes -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-file-alt me-2"></i> Reportes</h3>
                    <hr>
                    
                    <a href="reporte_boletas.php" class="btn btn-primary report-btn">
                        <i class="fas fa-money-bill-alt me-2"></i> Reporte de Ingresos
                    </a>
                    
                    <a href="reporte_ingresos.php" class="btn btn-success report-btn">
                        <i class="fas fa-receipt me-2"></i> Reporte de Boletas
                    </a>
                    
                    <a href="reporte_vehiculos.php" class="btn btn-info report-btn">
                        <i class="fas fa-car me-2"></i> Reporte de Vehículos en Parking
                    </a>
                    
                    <a href="reporte_estadisticas.php" class="btn btn-warning report-btn">
                        <i class="fas fa-chart-pie me-2"></i> Estadísticas por Día
                    </a>
                </div>
            </div>

            <!-- Vehículos actuales por tipo -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="fas fa-car me-2"></i> Vehículos Actuales en Espera</h3>
                    <hr>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo de Vehículo</th>
                                    <th class="text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                if (mysqli_num_rows($result_por_tipo) > 0): 
                                    while ($row = mysqli_fetch_assoc($result_por_tipo)): 
                                        $total += $row['cantidad'];
                                ?>
                                <tr>
                                    <td><?php echo $row['NOMBRE']; ?></td>
                                    <td class="text-center"><?php echo $row['cantidad']; ?></td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                <tr>
                                    <td colspan="2" class="text-center">No hay vehículos en el parking</td>
                                </tr>
                                <?php endif; ?>
                                
                                <tr class="table-primary">
                                    <th>Total</th>
                                    <th class="text-center"><?php echo $total; ?></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="caja.php" class="btn btn-outline-primary back-btn">
                <i class="fas fa-arrow-left me-2"></i> Regresar a Caja
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>