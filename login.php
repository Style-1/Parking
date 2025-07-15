<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Parking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            max-width: 450px;
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
            font-size: 3.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
            background-color: #e9f0ff;
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            display: inline-block;
        }
        .btn-login {
            background-color: #0d6efd;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-cancel {
            border: 1px solid #6c757d;
            color: #6c757d;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-cancel:hover {
            background-color: #6c757d;
            color: white;
        }
        .form-label {
            font-weight: 600;
            color: #0d6efd;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="header">
                <div class="header-icon">
                    <i class="fas fa-parking"></i>
                </div>
                <h1>Sistema de Parking</h1>
                <p class="text-muted">Acceso al panel de administración</p>
            </div>
            
            <form action="ingreso.php" method="post">
                <div class="mb-4">
                    <label for="usuario" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control form-control-lg" id="usuario" name="usuario" placeholder="Ingrese su nombre de usuario" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="clave" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control form-control-lg" id="clave" name="clave" placeholder="Ingrese su contraseña" required>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-5">
                    <button type="reset" class="btn btn-cancel">
                        <i class="fas fa-times-circle me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Ingresar
                    </button>
                </div>
                
               
            </form>
        </div>
        
     
    </div>
    
    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>