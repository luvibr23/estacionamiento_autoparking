<?php
// ================================================
// DEBUG DEL INDEX.PHP
// Archivo: debug_index.php
// ================================================

echo "<h1>🔍 Debug del Sistema de Login</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar sesión actual
session_start();
echo "<h2>📊 Estado de la Sesión</h2>";
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>⚠️ SESIÓN ACTIVA DETECTADA</strong><br>";
    echo "Usuario: " . ($_SESSION['usuario'] ?? 'N/A') . "<br>";
    echo "Nombre: " . ($_SESSION['nombre'] ?? 'N/A') . "<br>";
    echo "Rol: " . ($_SESSION['rol'] ?? 'N/A') . "<br>";
    echo "Login time: " . date('d/m/Y H:i:s', $_SESSION['login_time'] ?? time()) . "<br>";
    echo "<br><strong>Por eso no ves el formulario de login - ya estás logueado!</strong>";
    echo "</div>";
    
    echo "<a href='logout.php?force=1' style='background: #dc3545; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px;'>🚪 Cerrar Sesión para Ver Login</a>";
} else {
    echo "<p style='color: green;'>✅ No hay sesión activa - deberías ver el formulario de login</p>";
}

// Verificar archivos necesarios
echo "<h2>📁 Verificación de Archivos</h2>";
$files = [
    'index.php' => file_exists('index.php'),
    'assets/css/login.css' => file_exists('assets/css/login.css'),
    'assets/js/login.js' => file_exists('assets/js/login.js'),
    'config/database.php' => file_exists('config/database.php'),
    'controllers/AuthController.php' => file_exists('controllers/AuthController.php'),
    'api/csrf_token.php' => file_exists('api/csrf_token.php')
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Archivo</th><th>Estado</th></tr>";
foreach ($files as $file => $exists) {
    $status = $exists ? "✅ Existe" : "❌ No existe";
    $color = $exists ? "#d4edda" : "#f8d7da";
    echo "<tr style='background: $color;'><td>$file</td><td>$status</td></tr>";
}
echo "</table>";

// Test de URLs directas
echo "<h2>🔗 Test de URLs</h2>";
$urls = [
    'CSS' => 'assets/css/login.css',
    'JavaScript' => 'assets/js/login.js',
    'API CSRF' => 'api/csrf_token.php',
    'Controlador' => 'controllers/AuthController.php'
];

foreach ($urls as $name => $url) {
    echo "<p><strong>$name:</strong> <a href='$url' target='_blank'>$url</a></p>";
}

// Test del formulario
echo "<h2>🧪 Test del Formulario de Login</h2>";
echo "<form method='post' action='controllers/AuthController.php' style='border: 2px dashed #ccc; padding: 20px; margin: 10px 0;'>";
echo "<h3>Login Manual (para probar)</h3>";
echo "<p>Usuario: <input type='text' name='usuario' value='admin' style='padding: 5px;'></p>";
echo "<p>Contraseña: <input type='password' name='password' value='admin123' style='padding: 5px;'></p>";
echo "<input type='hidden' name='action' value='login'>";
echo "<input type='hidden' name='csrf_token' value='test_token'>";
echo "<button type='submit' style='background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;'>Probar Login Directo</button>";
echo "</form>";

// Verificar contenido del index.php
echo "<h2>📄 Contenido del index.php</h2>";
if (file_exists('index.php')) {
    $indexContent = file_get_contents('index.php');
    $lines = explode("\n", $indexContent);
    $preview = array_slice($lines, 0, 20);
    
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars(implode("\n", $preview));
    if (count($lines) > 20) {
        echo "\n... (+" . (count($lines) - 20) . " líneas más)";
    }
    echo "</pre>";
    
    // Verificar si tiene las rutas correctas
    $hasCss = strpos($indexContent, 'assets/css/login.css') !== false;
    $hasJs = strpos($indexContent, 'assets/js/login.js') !== false;
    
    echo "<p><strong>Rutas en index.php:</strong></p>";
    echo "<ul>";
    echo "<li>CSS incluido: " . ($hasCss ? "✅ Sí" : "❌ No") . "</li>";
    echo "<li>JS incluido: " . ($hasJs ? "✅ Sí" : "❌ No") . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ El archivo index.php no existe!</p>";
}

// JavaScript para test en vivo
echo "<h2>🔬 Test JavaScript en Vivo</h2>";
echo "<button onclick='testCSRF()' style='background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;'>Probar API CSRF</button>";
echo "<button onclick='testJQuery()' style='background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-left: 10px;'>Probar jQuery</button>";
echo "<div id='testResults' style='margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;'></div>";

?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function testCSRF() {
    document.getElementById('testResults').innerHTML = '🔄 Probando API CSRF...';
    
    fetch('api/csrf_token.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('testResults').innerHTML = 
                '✅ <strong>API CSRF funciona:</strong><br>' + 
                '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('testResults').innerHTML = 
                '❌ <strong>Error en API CSRF:</strong><br>' + error.message;
        });
}

function testJQuery() {
    if (typeof jQuery !== 'undefined') {
        $('#testResults').html('✅ <strong>jQuery funciona correctamente</strong><br>Versión: ' + jQuery.fn.jquery);
    } else {
        document.getElementById('testResults').innerHTML = '❌ <strong>jQuery no está cargado</strong>';
    }
}

// Test automático al cargar
$(document).ready(function() {
    console.log('Debug page loaded');
    console.log('jQuery version:', jQuery.fn.jquery);
    
    // Verificar si los archivos CSS y JS se pueden cargar
    $('<link>')
        .attr('rel', 'stylesheet')
        .attr('href', 'assets/css/login.css')
        .on('load', function() {
            console.log('✅ CSS se carga correctamente');
        })
        .on('error', function() {
            console.log('❌ Error cargando CSS');
        })
        .appendTo('head');
});
</script>

<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        background: #f8f9fa; 
    }
    h1, h2 { 
        color: #495057; 
    }
    pre { 
        font-size: 12px; 
        max-height: 300px; 
        overflow-y: auto; 
    }
    button:hover { 
        opacity: 0.9; 
    }
</style>