<?php
include('conexion.php'); // Asegúrate de tener una conexión correcta

// Consulta para obtener las boletas con información del cliente y tipo
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
        ORDER BY b.FECHA_ENTRADA DESC";

$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Caja - Parking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
     <link href="Estilos/caja.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
</head>

<body>

    <div class="container-fluid"> <!-- Cambiado de container a container-fluid para más ancho -->
        <div class="caja-container">
            <div class="header">
                <h1><i class="fas fa-cash-register me-3"></i> CAJA</h1>
            </div>

            <?php if (isset($_GET['pago_exitoso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-check-circle me-2"></i> ¡Pago procesado correctamente!</strong> Ya puede generar la boleta.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="grid-container">
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
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
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
                                                        <button class="btn-boleta disabled" disabled>
                                                            <i class="fas fa-receipt"></i> Boleta
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No se encontraron boletas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="entrada_parking.php" class="btn btn-primary"><i class="fas fa-car me-2"></i> Nueva Entrada</a>
                    <a href="buscar_boleta.php" class="btn btn-outline-primary"><i class="fas fa-search me-2"></i> Buscar</a>
                    <a href="adm.php" class="btn btn-outline-primary"><i class="fas fa-calculator me-2"></i> Adminstracion</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>