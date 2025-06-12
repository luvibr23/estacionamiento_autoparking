<?php
// ================================================
// HERRAMIENTA PARA DESBLOQUEAR USUARIOS
// Archivo: unblock_user.php (temporal)
// ================================================

echo "<h2>🔓 Herramienta de Desbloqueo de Usuarios</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    // Conectar a base de datos
    $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db", "root", "123456");
    echo "✅ Conexión a BD exitosa<br><br>";
    
    // Procesar desbloqueo si se envió el formulario
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'unblock_user' && !empty($_POST['user_id'])) {
            $userId = $_POST['user_id'];
            
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "✅ Usuario desbloqueado exitosamente";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "❌ Error al desbloquear usuario";
                echo "</div>";
            }
        }
        
        if ($_POST['action'] === 'unblock_all') {
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL");
            $result = $stmt->execute();
            
            if ($result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "✅ Todos los usuarios desbloqueados exitosamente";
                echo "</div>";
            }
        }
        
        if ($_POST['action'] === 'reset_passwords') {
            // Resetear contraseñas a valores por defecto
            $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
            $operadorHash = password_hash('operador123', PASSWORD_DEFAULT);
            
            $pdo->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE usuario = 'admin'")->execute([$adminHash]);
            $pdo->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE usuario = 'operador1'")->execute([$operadorHash]);
            $pdo->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE usuario = 'operador2'")->execute([$operadorHash]);
            
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Contraseñas reseteadas y usuarios desbloqueados<br>";
            echo "<strong>admin:</strong> admin123<br>";
            echo "<strong>operador1:</strong> operador123<br>";
            echo "<strong>operador2:</strong> operador123";
            echo "</div>";
        }
    }
    
    // Obtener estado actual de usuarios
    $stmt = $pdo->query("SELECT 
        id,
        usuario,
        nombre,
        rol,
        activo,
        intentos_fallidos,
        bloqueado_hasta,
        ultimo_acceso,
        CASE 
            WHEN bloqueado_hasta > NOW() THEN 'BLOQUEADO'
            WHEN intentos_fallidos >= 3 THEN 'EN RIESGO'
            ELSE 'NORMAL'
        END as estado,
        CASE 
            WHEN bloqueado_hasta > NOW() THEN TIMESTAMPDIFF(MINUTE, NOW(), bloqueado_hasta)
            ELSE 0
        END as minutos_restantes
    FROM usuarios 
    ORDER BY id");
    
    $usuarios = $stmt->fetchAll();
    
    // Mostrar estado de usuarios
    echo "<h3>📊 Estado Actual de Usuarios</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Estado</th><th>Intentos Fallidos</th><th>Tiempo Restante</th><th>Último Acceso</th><th>Acciones</th>";
    echo "</tr>";
    
    $hasBlockedUsers = false;
    
    foreach ($usuarios as $user) {
        $rowColor = '';
        if ($user['estado'] === 'BLOQUEADO') {
            $rowColor = 'background: #f8d7da;';
            $hasBlockedUsers = true;
        } elseif ($user['estado'] === 'EN RIESGO') {
            $rowColor = 'background: #fff3cd;';
        }
        
        echo "<tr style='$rowColor'>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['usuario']}</strong></td>";
        echo "<td>{$user['nombre']}</td>";
        echo "<td>{$user['rol']}</td>";
        
        $estadoIcon = '';
        if ($user['estado'] === 'BLOQUEADO') $estadoIcon = '🔒';
        elseif ($user['estado'] === 'EN RIESGO') $estadoIcon = '⚠️';
        else $estadoIcon = '✅';
        
        echo "<td>$estadoIcon {$user['estado']}</td>";
        echo "<td>{$user['intentos_fallidos']}/5</td>";
        
        if ($user['minutos_restantes'] > 0) {
            echo "<td>{$user['minutos_restantes']} min</td>";
        } else {
            echo "<td>-</td>";
        }
        
        echo "<td>" . ($user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca') . "</td>";
        
        echo "<td>";
        if ($user['estado'] === 'BLOQUEADO' || $user['intentos_fallidos'] > 0) {
            echo "<form method='post' style='display: inline;'>";
            echo "<input type='hidden' name='action' value='unblock_user'>";
            echo "<input type='hidden' name='user_id' value='{$user['id']}'>";
            echo "<button type='submit' style='background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>Desbloquear</button>";
            echo "</form>";
        }
        echo "</td>";
        
        echo "</tr>";
    }
    echo "</table>";
    
    // Acciones rápidas
    echo "<h3>⚡ Acciones Rápidas</h3>";
    echo "<div style='margin: 20px 0;'>";
    
    if ($hasBlockedUsers) {
        echo "<form method='post' style='display: inline; margin-right: 10px;'>";
        echo "<input type='hidden' name='action' value='unblock_all'>";
        echo "<button type='submit' style='background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;' onclick='return confirm(\"¿Desbloquear todos los usuarios?\")'>🔓 Desbloquear Todos</button>";
        echo "</form>";
    }
    
    echo "<form method='post' style='display: inline; margin-right: 10px;'>";
    echo "<input type='hidden' name='action' value='reset_passwords'>";
    echo "<button type='submit' style='background: #ffc107; color: black; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;' onclick='return confirm(\"¿Resetear todas las contraseñas a valores por defecto?\")'>🔑 Resetear Contraseñas</button>";
    echo "</form>";
    
    echo "<a href='index.php' style='background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;'>🏠 Volver al Login</a>";
    echo "</div>";
    
    // Script SQL para ejecutar manualmente
    echo "<h3>🛠️ Script SQL Manual</h3>";
    echo "<p>También puedes ejecutar este script directamente en MySQL Workbench:</p>";
    echo "<textarea rows='8' cols='100' style='font-family: monospace; width: 100%;'>";
    echo "-- Desbloquear todos los usuarios\n";
    echo "USE estacionamiento_db;\n";
    echo "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL;\n\n";
    echo "-- Resetear contraseñas (opcional)\n";
    echo "UPDATE usuarios SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE usuario = 'admin';\n";
    echo "UPDATE usuarios SET password = '" . password_hash('operador123', PASSWORD_DEFAULT) . "' WHERE usuario IN ('operador1', 'operador2');\n\n";
    echo "-- Verificar cambios\n";
    echo "SELECT usuario, intentos_fallidos, bloqueado_hasta, \n";
    echo "       CASE WHEN bloqueado_hasta > NOW() THEN 'BLOQUEADO' ELSE 'LIBRE' END as estado\n";
    echo "FROM usuarios;";
    echo "</textarea>";
    
    // Información adicional
    echo "<h3>ℹ️ Información del Sistema de Bloqueo</h3>";
    echo "<ul>";
    echo "<li><strong>Intentos permitidos:</strong> 5 antes del bloqueo</li>";
    echo "<li><strong>Tiempo de bloqueo:</strong> 15 minutos</li>";
    echo "<li><strong>Reset automático:</strong> Después del tiempo de bloqueo</li>";
    echo "<li><strong>Condiciones de bloqueo:</strong> 5 intentos fallidos consecutivos</li>";
    echo "</ul>";
    
    // Test de conexión al login
    echo "<h3>🧪 Test Rápido de Login</h3>";
    echo "<form method='post' action='controllers/AuthController.php' target='_blank'>";
    echo "<table>";
    echo "<tr><td>Usuario:</td><td><input type='text' name='usuario' value='admin'></td></tr>";
    echo "<tr><td>Contraseña:</td><td><input type='password' name='password' value='admin123'></td></tr>";
    echo "<tr><td colspan='2'>";
    echo "<input type='hidden' name='action' value='login'>";
    echo "<input type='hidden' name='csrf_token' value='test_token'>";
    echo "<button type='submit' style='background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 5px;'>Probar Login</button>";
    echo "</td></tr>";
    echo "</table>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
    echo "❌ Error de conexión: " . $e->getMessage();
    echo "</div>";
    echo "<p>Verifica que XAMPP esté funcionando y las credenciales de BD sean correctas.</p>";
}
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        background: #f8f9fa;
    }
    h2 { 
        color: #495057; 
        border-bottom: 2px solid #dee2e6; 
        padding-bottom: 10px; 
    }
    h3 { 
        color: #6c757d; 
        margin-top: 30px; 
    }
    table { 
        margin: 20px 0; 
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    th { 
        background: #f8f9fa; 
        font-weight: bold;
    }
    textarea { 
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 10px;
        border-radius: 5px;
    }
    button:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
</style>