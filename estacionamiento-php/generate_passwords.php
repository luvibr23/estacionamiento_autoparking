<?php
// ================================================
// SCRIPT PARA GENERAR CONTRASEÑAS HASHEADAS
// Archivo: generate_passwords.php (temporal)
// ================================================

echo "<h2>Generador de Contraseñas Hasheadas</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Contraseñas a hashear
$passwords = [
    'admin123' => 'Contraseña para admin',
    'operador123' => 'Contraseña para operadores'
];

echo "<h3>Contraseñas Hasheadas:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Contraseña Original</th><th>Hash Generado</th><th>Descripción</th></tr>";

foreach ($passwords as $password => $description) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td><strong>$password</strong></td>";
    echo "<td style='font-family: monospace; font-size: 12px;'>$hash</td>";
    echo "<td>$description</td>";
    echo "</tr>";
}
echo "</table>";

// Script SQL generado automáticamente
echo "<h3>Script SQL para Actualizar:</h3>";
echo "<textarea rows='15' cols='100' style='font-family: monospace;'>";
echo "-- Script generado automáticamente el " . date('Y-m-d H:i:s') . "\n";
echo "USE estacionamiento_db;\n\n";

// Hash para admin123
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
echo "-- Actualizar contraseña de admin (admin123)\n";
echo "UPDATE usuarios SET password = '$adminHash' WHERE usuario = 'admin';\n\n";

// Hash para operador123
$operadorHash = password_hash('operador123', PASSWORD_DEFAULT);
echo "-- Actualizar contraseña de operador1 (operador123)\n";
echo "UPDATE usuarios SET password = '$operadorHash' WHERE usuario = 'operador1';\n\n";

echo "-- Actualizar contraseña de operador2 (operador123)\n";
echo "UPDATE usuarios SET password = '$operadorHash' WHERE usuario = 'operador2';\n\n";

echo "-- Verificar actualización\n";
echo "SELECT usuario, nombre, rol, \n";
echo "       CASE WHEN LENGTH(password) > 50 THEN 'Hash válido' ELSE 'Hash inválido' END as estado_password\n";
echo "FROM usuarios;\n";

echo "</textarea>";

// Test de verificación
echo "<h3>Test de Verificación:</h3>";
echo "<p>Probando si los hashes generados funcionan:</p>";

$testAdmin = password_verify('admin123', $adminHash);
$testOperador = password_verify('operador123', $operadorHash);

echo "<ul>";
echo "<li><strong>admin123:</strong> " . ($testAdmin ? "✅ Válido" : "❌ Inválido") . "</li>";
echo "<li><strong>operador123:</strong> " . ($testOperador ? "✅ Válido" : "❌ Inválido") . "</li>";
echo "</ul>";

// Instrucciones
echo "<h3>Instrucciones:</h3>";
echo "<ol>";
echo "<li>Copia el script SQL de arriba</li>";
echo "<li>Pégalo en MySQL Workbench o phpMyAdmin</li>";
echo "<li>Ejecuta el script</li>";
echo "<li>Prueba el login con:</li>";
echo "<ul>";
echo "<li><strong>admin</strong> / <strong>admin123</strong></li>";
echo "<li><strong>operador1</strong> / <strong>operador123</strong></li>";
echo "</ul>";
echo "</ol>";

// Información adicional
echo "<h3>Información del Sistema:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Password Hash:</strong> " . PASSWORD_DEFAULT . "</li>";
echo "<li><strong>Algoritmo:</strong> " . password_get_info($adminHash)['algoName'] . "</li>";
echo "</ul>";

// Test de conexión a base de datos
echo "<h3>Test de Conexión a Base de Datos:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db", "root", "123456");
    echo "✅ Conexión a base de datos exitosa<br>";
    
    // Verificar usuarios existentes
    $stmt = $pdo->query("SELECT usuario, nombre, rol FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    echo "<strong>Usuarios encontrados:</strong><br>";
    foreach ($usuarios as $user) {
        echo "- {$user['usuario']} ({$user['nombre']}) - Rol: {$user['rol']}<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    echo "Verifica que XAMPP esté funcionando y las credenciales sean correctas.";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 20px 0; }
    th { background: #f0f0f0; }
    textarea { width: 100%; max-width: 800px; }
    .success { color: green; }
    .error { color: red; }
</style>