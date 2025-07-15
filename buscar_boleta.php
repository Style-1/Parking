<?php
include('conexion.php');

$mensaje = "";
$resultado = null;

if (isset($_POST['buscar'])) {
    $valor = $_POST['valor'];
    
    // Búsqueda siempre por placa
    $sql = "SELECT 
            b.ID_BOLETA,
            c.PLACA,
            t.NOMBRE AS TIPO,
            b.FECHA_ENTRADA,
            b.FECHA_SALIDA,
            b.MONTO
        FROM tbboletas b
        JOIN tbclientes c ON b.ID_CLIENTE = c.ID_CLIENTE
        JOIN tbtipos t ON c.TIPO = t.ID_TIPO
        WHERE c.PLACA LIKE ?
        ORDER BY b.FECHA_ENTRADA DESC";
    $valor = "%" . $valor . "%";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $valor);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) == 0) {
        $mensaje = "No se encontraron resultados para su búsqueda.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Boleta - Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .search-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background-color: #0d6efd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }
        
        .search-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        table {
            margin-bottom: 0 !important;
        }
        
        th {
            background-color: #0d6efd;
            color: white;
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .btn-pagar, .btn-boleta {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-pagar {
            background-color: #198754;
            color: white;
        }
        
        .btn-boleta {
            background-color: #0dcaf0;
            color: white;
        }
        
        .btn-pagar:hover, .btn-boleta:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="search-container">
            <div class="header">
                <h1><i class="fas fa-search me-3"></i>Buscar Boleta por Placa</h1>
            </div>
            
            <div class="search-form">
                <form method="post" action="">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-9">
                            <label for="valor" class="form-label">Número de Placa:</label>
                            <input type="text" name="valor" id="valor" class="form-control" required 
                                   placeholder="Ingrese la placa o parte de ella...">
                           
                            <!-- Campo oculto para mantener la lógica del backend -->
                            <input type="hidden" name="criterio" value="placa">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" name="buscar" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="message <?php echo (strpos($mensaje, 'No se encontraron') !== false) ? 'error' : ''; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID Boleta</th>
                                <th>Placa</th>
                                <th>Tipo Vehículo</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?= $row['ID_BOLETA'] ?></td>
                                    <td><?= $row['PLACA'] ?></td>
                                    <td><?= $row['TIPO'] ?></td>
                                    <td><?= $row['FECHA_ENTRADA'] ?></td>
                                    <td><?= $row['FECHA_SALIDA'] ?? '---' ?></td>
                                    <td><?= $row['MONTO'] ? 'S/ ' . number_format($row['MONTO'], 2) : '---' ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if (empty($row['FECHA_SALIDA'])): ?>
                                                <a href="procesar_pago.php?id_boleta=<?= $row['ID_BOLETA'] ?>" class="btn-pagar pulse">
                                                    <i class="fas fa-money-bill-wave"></i> Pagar
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-pagar" disabled style="opacity: 0.5; cursor: not-allowed;">
                                                    <i class="fas fa-check"></i> Pagado
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($row['FECHA_SALIDA'])): ?>
                                                <a href="generar_boleta.php?id_boleta=<?= $row['ID_BOLETA'] ?>" class="btn-boleta">
                                                    <i class="fas fa-receipt"></i> Boleta
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-boleta" disabled style="opacity: 0.5; cursor: not-allowed;">
                                                    <i class="fas fa-receipt"></i> Boleta
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="caja.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Caja
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>