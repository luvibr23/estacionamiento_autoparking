<?php
// ================================================
// LOGIN SIMPLIFICADO PARA DEBUG
// Archivo: simple_login.php
// ================================================

session_start();

// Redireccionar si ya está autenticado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "<h2>Ya estás logueado!</h2>";
    echo "<p>Usuario: " . ($_SESSION['nombre'] ?? 'N/A') . "</p>";
    echo "<p>Rol: " . ($_SESSION['rol'] ?? 'N/A') . "</p>";
    echo "<a href='logout.php?force=1' style='background: #dc3545; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px;'>Cerrar Sesión</a>";
    exit();
}

// Procesar login si se envía
if ($_POST['usuario'] && $_POST['password']) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    echo "<h3>🔍 Procesando Login...</h3>";
    echo "<p>Usuario: <strong>$usuario</strong></p>";
    
    try {
        // Conectar a BD
        $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db", "root", "123456");
        echo "<p>✅ Conexión a BD exitosa</p>";
        
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p>✅ Usuario encontrado</p>";
            echo "<p>Hash en BD: <code>" . substr($user['password'], 0, 20) . "...</code></p>";
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✅ Contraseña correcta</p>";
                
                // Crear sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                echo "<p style='color: green;'>✅ Sesión creada exitosamente</p>";
                echo "<p>Redirigiendo en 3 segundos...</p>";
                
                $dashboardUrl = ($user['rol'] === 'Administrador') ? 'views/admin_dashboard.php' : 'views/operador_dashboard.php';
                
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '$dashboardUrl';
                    }, 3000);
                </script>";
                
            } else {
                echo "<p style='color: red;'>❌ Contraseña incorrecta</p>";
                
                // Test manual de hash
                $testHash = password_hash($password, PASSWORD_DEFAULT);
                echo "<p>Hash que debería ser: <code>" . substr($testHash, 0, 20) . "...</code></p>";
                
                // Verificar si es problema de hash
                $correctHashes = [
                    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
                    'operador123' => password_hash('operador123', PASSWORD_DEFAULT)
                ];
                
                echo "<h4>🔧 Opciones para corregir:</h4>";
                foreach ($correctHashes as $pass => $hash) {
                    if (password_verify($password, $hash)) {
                        echo "<p style='color: orange;'>⚠️ La contraseña '$password' debería funcionar con este hash:</p>";
                        echo "<code>UPDATE usuarios SET password = '$hash' WHERE usuario = '$usuario';</code>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Usuario no encontrado o inactivo</p>";
            
            // Mostrar usuarios disponibles
            $users = $pdo->query("SELECT usuario, nombre, rol, activo FROM usuarios")->fetchAll();
            echo "<h4>Usuarios disponibles:</h4>";
            echo "<ul>";
            foreach ($users as $u) {
                $status = $u['activo'] ? "Activo" : "Inactivo";
                echo "<li><strong>{$u['usuario']}</strong> ({$u['nombre']}) - {$u['rol']} - $status</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simplificado - Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .login-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>🔐 Login Simplificado (Debug)</h1>
        
        <div class="debug-info">
            <strong>🔧 Este es un login de debug</strong><br>
            Usar solo para diagnosticar problemas del login principal.
        </div>
        
        <form method="post">
            <label>Usuario:</label>
            <input type="text" name="usuario" value="admin" required>
            
            <label>Contraseña:</label>
            <input type="password" name="password" value="admin123" required>
            
            <button type="submit">🚀 Probar Login</button>
        </form>
        
        <div style="margin-top: 20px;">
            <h3>Usuarios de Prueba:</h3>
            <ul>
                <li><strong>admin</strong> / admin123 (Administrador)</li>
                <li><strong>operador1</strong> / operador123 (Operador)</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="debug_index.php" style="background: #6c757d; color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px;">🔍 Debug Completo</a>
            <a href="index.php" style="background: #28a745; color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px; margin-left: 10px;">🏠 Login Principal</a>
            <a href="unblock_user.php" style="background: #17a2b8; color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px; margin-left: 10px;">🔓 Gestionar Usuarios</a>
        </div>
    </div>
</body>
</html>