<?php
// ================================================
// TEST DIRECTO DEL LOGIN
// Archivo: test_login_direct.php
// ================================================

echo "<h1>🧪 Test Directo del Sistema de Login</h1>";

// Test 1: Verificar que podemos conectar a la BD
echo "<h2>1. Test de Conexión a Base de Datos</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db;charset=utf8mb4", "root", "123456");
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar usuarios
    $stmt = $pdo->query("SELECT usuario, nombre, rol, intentos_fallidos, bloqueado_hasta FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Intentos</th><th>Bloqueado</th></tr>";
    foreach ($usuarios as $user) {
        $bloqueado = $user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time() ? "SÍ" : "NO";
        echo "<tr>";
        echo "<td>{$user['usuario']}</td>";
        echo "<td>{$user['nombre']}</td>";
        echo "<td>{$user['rol']}</td>";
        echo "<td>{$user['intentos_fallidos']}</td>";
        echo "<td>$bloqueado</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 2: Test de Token CSRF
echo "<h2>2. Test de Token CSRF</h2>";
echo "<button onclick='testCSRF()'>Probar API CSRF</button>";
echo "<div id='csrfResult'></div>";

// Test 3: Test de Login Manual
echo "<h2>3. Test de Login Manual</h2>";
echo "<form id='testLoginForm'>";
echo "<table>";
echo "<tr><td>Usuario:</td><td><input type='text' id='testUsuario' value='admin'></td></tr>";
echo "<tr><td>Contraseña:</td><td><input type='password' id='testPassword' value='admin123'></td></tr>";
echo "<tr><td colspan='2'><button type='button' onclick='testLogin()'>Probar Login</button></td></tr>";
echo "</table>";
echo "</form>";
echo "<div id='loginResult'></div>";

// Test 4: Test de Archivos
echo "<h2>4. Test de Archivos del Sistema</h2>";
$archivos = [
    'controllers/AuthController.php' => 'Controlador original',
    'controllers/AuthController_fixed.php' => 'Controlador corregido',
    'api/csrf_token.php' => 'API CSRF original', 
    'api/csrf_token_fixed.php' => 'API CSRF corregida'
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Archivo</th><th>Existe</th><th>Descripción</th><th>Probar</th></tr>";
foreach ($archivos as $archivo => $descripcion) {
    $existe = file_exists($archivo) ? "✅" : "❌";
    echo "<tr>";
    echo "<td>$archivo</td>";
    echo "<td>$existe</td>";
    echo "<td>$descripcion</td>";
    echo "<td><button onclick='testFile(\"$archivo\")'>Test</button></td>";
    echo "</tr>";
}
echo "</table>";

echo "<div id='fileTestResult'></div>";

// Instrucciones
echo "<h2>🔧 Instrucciones de Reparación</h2>";
echo "<ol>";
echo "<li><strong>Crear archivos corregidos:</strong> AuthController_fixed.php y csrf_token_fixed.php</li>";
echo "<li><strong>Probar con los botones de arriba</strong> que funcionan correctamente</li>";
echo "<li><strong>Si funcionan:</strong> Renombrar los archivos _fixed a los nombres originales</li>";
echo "<li><strong>Probar login:</strong> Ir a index.php y hacer login normal</li>";
echo "</ol>";

echo "<h2>🚀 Acciones Rápidas</h2>";
echo "<button onclick='createFixedFiles()' style='background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Crear Archivos Corregidos</button>";
echo " ";
echo "<button onclick='testAllSystems()' style='background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Probar Todo el Sistema</button>";

?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function testCSRF() {
    $('#csrfResult').html('🔄 Probando...');
    
    // Probar API original
    $.get('api/csrf_token.php')
        .done(function(data) {
            $('#csrfResult').html('✅ <strong>API Original funciona:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>');
        })
        .fail(function(xhr) {
            $('#csrfResult').html('❌ <strong>API Original falla.</strong> Probando API corregida...');
            
            // Probar API corregida
            $.get('api/csrf_token_fixed.php')
                .done(function(data) {
                    $('#csrfResult').append('<br>✅ <strong>API Corregida funciona:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>');
                })
                .fail(function(xhr2) {
                    $('#csrfResult').append('<br>❌ <strong>Ambas APIs fallan.</strong><br>Error: ' + xhr2.responseText);
                });
        });
}

function testLogin() {
    $('#loginResult').html('🔄 Probando login...');
    
    const usuario = $('#testUsuario').val();
    const password = $('#testPassword').val();
    
    // Probar controlador original
    $.post('controllers/AuthController.php', {
        action: 'login',
        usuario: usuario,
        password: password,
        csrf_token: 'test_token'
    })
    .done(function(data) {
        $('#loginResult').html('✅ <strong>Controlador Original funciona:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>');
    })
    .fail(function(xhr) {
        $('#loginResult').html('❌ <strong>Controlador Original falla.</strong> Error: ' + xhr.status + '<br>Respuesta: ' + xhr.responseText.substring(0, 500) + '...');
        
        // Probar controlador corregido
        $('#loginResult').append('<br><br>🔄 Probando controlador corregido...');
        
        $.post('controllers/AuthController_fixed.php', {
            action: 'login',
            usuario: usuario,
            password: password,
            csrf_token: 'test_token'
        })
        .done(function(data) {
            $('#loginResult').append('<br>✅ <strong>Controlador Corregido funciona:</strong><pre>' + JSON.stringify(data, null, 2) + '</pre>');
        })
        .fail(function(xhr2) {
            $('#loginResult').append('<br>❌ <strong>Ambos controladores fallan.</strong><br>Error: ' + xhr2.responseText.substring(0, 500));
        });
    });
}

function testFile(archivo) {
    $('#fileTestResult').html('🔄 Probando ' + archivo + '...');
    
    $.get(archivo)
        .done(function(data) {
            $('#fileTestResult').html('✅ <strong>' + archivo + ' accesible</strong> (' + data.length + ' chars)');
        })
        .fail(function(xhr) {
            $('#fileTestResult').html('❌ <strong>' + archivo + ' no accesible</strong> - ' + xhr.status + ': ' + xhr.statusText);
        });
}

function createFixedFiles() {
    alert('Para crear los archivos corregidos:\n\n1. Copia el contenido de AuthController_fixed.php\n2. Guárdalo como controllers/AuthController_fixed.php\n3. Copia el contenido de csrf_token_fixed.php\n4. Guárdalo como api/csrf_token_fixed.php\n\nLuego prueba con los botones de test.');
}

function testAllSystems() {
    testCSRF();
    setTimeout(testLogin, 2000);
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    h1, h2 { color: #495057; }
    table { background: white; margin: 15px 0; }
    th { background: #e9ecef; }
    button { padding: 8px 12px; border: none; border-radius: 3px; cursor: pointer; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto; }
</style>