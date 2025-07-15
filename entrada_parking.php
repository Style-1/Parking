<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Parking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .parking-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0d6efd;
            font-weight: bold;
        }
        .header-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .btn-register {
            background-color: #0d6efd;
            width: 100%;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
        }
        .form-label {
            font-weight: 600;
        }
        .price-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="parking-container">
            <div class="header">
                <i class="fas fa-parking header-icon"></i>
                <h1>Sistema de Parking</h1>
                <p class="text-muted">Registro de entrada de vehículos</p>
            </div>
            
            <form action="registrar_entrada.php" method="post">
                <div class="mb-4">
                    <label for="placa" class="form-label">Número de Placa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="placa" name="placa" placeholder="Ingrese la placa del vehículo" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="tipo" class="form-label">Tipo de Vehículo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car"></i></span>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="" selected disabled>Seleccione el tipo de vehículo</option>
                            <option value="3">Camión <span class="price-info">(0.30/min)</span></option>
                            <option value="2">Auto <span class="price-info">(0.20/min)</span></option>
                            <option value="1">Moto <span class="price-info">(0.10/min)</span></option>
                        </select>
                    </div>
                    <div class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Tarifas: Camión 0.30/min - Auto 0.20/min - Moto 0.10/min
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-register">
                        <i class="fas fa-check-circle me-2"></i> Registrar Entrada
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <a href="caja.php" class="text-decoration-none">Ver vehículos registrados</a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS (opcional, solo si necesitas componentes interactivos) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>