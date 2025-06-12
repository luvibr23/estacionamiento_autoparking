<?php
// ================================================
// ARCHIVO DE PRUEBA PARA CSRF TOKEN
// Archivo: test_csrf.php (temporal para debug)
// ================================================

session_start();

echo "<h2>Test de Token CSRF</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar si la sesión funciona
echo "<h3>1. Test de Sesión:</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sesión activa<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Sesión no activa<br>";
}

// Test de token simple
echo "<h3>2. Test de Token Simple:</h3>";
try {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo "✅ Token generado: " . substr($_SESSION['csrf_token'], 0, 16) . "...<br>";
} catch (Exception $e) {
    echo "❌ Error generando token: " . $e->getMessage() . "<br>";
}

// Test de JSON
echo "<h3>3. Test de Respuesta JSON:</h3>";
$response = [
    'success' => true,
    'token' => $_SESSION['csrf_token'] ?? 'N/A',
    'timestamp' => time()
];
echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";

// Test de configuración PHP
echo "<h3>4. Configuración PHP:</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Cookie Params: <pre>" . print_r(session_get_cookie_params(), true) . "</pre>";

// Test de permisos
echo "<h3>5. Test de Permisos:</h3>";
$apiPath = __DIR__ . '/api/csrf_token.php';
echo "Ruta API: $apiPath<br>";
echo "Archivo existe: " . (file_exists($apiPath) ? "✅ Sí" : "❌ No") . "<br>";
echo "Es legible: " . (is_readable($apiPath) ? "✅ Sí" : "❌ No") . "<br>";

// Test directo de la API
echo "<h3>6. Test Directo de API:</h3>";
if (file_exists($apiPath)) {
    echo "<iframe src='api/csrf_token.php' width='100%' height='100'></iframe>";
} else {
    echo "❌ Archivo API no encontrado";
}

// JavaScript para test AJAX
echo "<h3>7. Test AJAX:</h3>";
echo "<button onclick='testAjax()'>Probar AJAX</button>";
echo "<div id='ajaxResult'></div>";

echo "<script src='https://code.jquery.com/jquery-3.7.0.min.js'></script>";
echo "<script>
function testAjax() {
    $('#ajaxResult').html('Cargando...');
    
    $.ajax({
        url: 'api/csrf_token.php',
        type: 'GET',
        dataType: 'json',
        timeout: 5000,
        success: function(data) {
            $('#ajaxResult').html('<strong>✅ Éxito:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>');
        },
        error: function(xhr, status, error) {
            $('#ajaxResult').html('<strong>❌ Error:</strong><br>' + 
                'Status: ' + status + '<br>' +
                'Error: ' + error + '<br>' +
                'Response: ' + xhr.responseText + '<br>' +
                'Status Code: ' + xhr.status);
        }
    });
}
</script>";
?>