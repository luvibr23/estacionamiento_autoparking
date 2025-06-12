<?php
// ================================================
// DEBUG DE LOGIN - VERIFICAR CONTRASEÑAS
// Archivo: debug_login.php (temporal)
// ================================================

echo "<h2>Debug de Sistema de Login</h2>";

try {
    // Conectar a base de datos
    $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db", "root", "123456");
    echo "✅ Conexión a BD exitosa<br><br>";
    
    // Obtener usuarios
    $stmt = $pdo->query("SELECT * FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    echo "<h3>Usuarios en Base de Datos:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Usuario</th><th>Nombre</th><th>Hash Actual</th><th>Test admin123</th><th>Test operador123</th></tr>";
    
    foreach ($usuarios as $user) {
        $testAdmin = password_verify('admin123', $user['password']);
        $testOperador = password_verify('operador123', $user['password']);
        
        echo "<tr>";
        echo "<td><strong>{$user['usuario']}</strong></td>";
        echo "<td>{$user['nombre']}</td>";
        echo "<td style='font-size: 10px;'>" . substr($user['password'], 0, 30) . "...</td>";
        echo "<td>" . ($testAdmin ? "✅" : "❌") . "</td>";
        echo "<td>" . ($testOperador ? "✅" : "❌") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Generar hashes correctos
    echo "<h3>Hashes Correctos:</h3>";
    $correctAdminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $correctOperadorHash = password_hash('operador123', PASSWORD_DEFAULT);
    
    echo "<p><strong>Hash para 'admin123':</strong><br>";
    echo "<code>$correctAdminHash</code></p>";
    
    echo "<p><strong>Hash para 'operador123':</strong><br>";
    echo "<code>$correctOperadorHash</code></p>";
    
    // Script de actualización
    echo "<h3>Script SQL para Corregir:</h3>";
    echo "<textarea rows='8' cols='100'>";
    echo "USE estacionamiento_db;\n";
    echo "UPDATE usuarios SET password = '$correctAdminHash' WHERE usuario = 'admin';\n";
    echo "UPDATE usuarios SET password = '$correctOperadorHash' WHERE usuario = 'operador1';\n";
    echo "UPDATE usuarios SET password = '$correctOperadorHash' WHERE usuario = 'operador2';\n";
    echo "SELECT usuario, 'Password actualizado' as estado FROM usuarios;";
    echo "</textarea>";
    
    // Test manual de login
    echo "<h3>Test Manual de Login:</h3>";
    echo "<form method='post'>";
    echo "Usuario: <input type='text' name='test_user' value='admin'><br><br>";
    echo "Contraseña: <input type='password' name='test_pass' value='admin123'><br><br>";
    echo "<input type='submit' value='Probar Login'>";
    echo "</form>";
    
    if ($_POST['test_user'] && $_POST['test_pass']) {
        $testUser = $_POST['test_user'];
        $testPass = $_POST['test_pass'];
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
        $stmt->execute([$testUser]);
        $user = $stmt->fetch();
        
        echo "<h4>Resultado del Test:</h4>";
        if ($user) {
            echo "Usuario encontrado: ✅<br>";
            echo "Contraseña coincide: " . (password_verify($testPass, $user['password']) ? "✅" : "❌") . "<br>";
            echo "Hash en BD: <code>" . $user['password'] . "</code><br>";
            echo "Hash debería ser: <code>" . password_hash($testPass, PASSWORD_DEFAULT) . "</code><br>";
        } else {
            echo "Usuario no encontrado: ❌<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 20px 0; }
    th { background: #f0f0f0; }
    textarea { width: 100%; }
    code { background: #f5f5f5; padding: 2px 4px; }
</style>