<?php
include('conexion.php');

// Verificar si la columna TARIFA ya existe
$result = mysqli_query($con, "SHOW COLUMNS FROM tbtipos LIKE 'TARIFA'");
$exists = mysqli_num_rows($result) > 0;

if (!$exists) {
    // La columna no existe, agregarla
    $sql = "ALTER TABLE tbtipos ADD COLUMN TARIFA DECIMAL(10,2) DEFAULT 0.20";
    if (mysqli_query($con, $sql)) {
        echo "Columna TARIFA agregada correctamente.<br>";
    } else {
        echo "Error al agregar columna TARIFA: " . mysqli_error($con) . "<br>";
    }
} else {
    echo "La columna TARIFA ya existe.<br>";
}

// Actualizar las tarifas según el tipo de vehículo
$updates = [
    "UPDATE tbtipos SET TARIFA = 0.10 WHERE ID_TIPO = 1",  // Moto
    "UPDATE tbtipos SET TARIFA = 0.20 WHERE ID_TIPO = 2",  // Auto
    "UPDATE tbtipos SET TARIFA = 0.30 WHERE ID_TIPO = 3"   // Camión
];

foreach ($updates as $sql) {
    if (mysqli_query($con, $sql)) {
        echo "Tarifa actualizada correctamente.<br>";
    } else {
        echo "Error al actualizar tarifa: " . mysqli_error($con) . "<br>";
    }
}

echo "<br>Proceso completado. <a href='caja.php'>Volver a la caja</a>";
?>