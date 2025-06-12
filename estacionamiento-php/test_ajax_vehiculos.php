<?php
// Test directo de la funcionalidad AJAX de vehículos
session_start();

// Simular que estamos logueados si no lo estamos
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "<div style='background: yellow; padding: 10px; margin: 10px;'>⚠️ Sesión simulada para pruebas</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Vehículos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Test AJAX Sistema de Vehículos</h1>
        
        <div class="test-section">
            <h3>1. Información de Sesión</h3>
            <p><strong>User ID:</strong> <?= $_SESSION['user_id'] ?></p>
            <p><strong>Username:</strong> <?= $_SESSION['username'] ?></p>
            <p><strong>CSRF Token:</strong> <?= substr($_SESSION['csrf_token'], 0, 20) ?>...</p>
        </div>

        <div class="test-section">
            <h3>2. Test de Conexión a Base de Datos</h3>
            <div id="db-test-result">Probando...</div>
        </div>

        <div class="test-section">
            <h3>3. Test de API CSRF Token</h3>
            <button class="btn btn-primary" onclick="testCSRFToken()">Probar Token CSRF</button>
            <div id="csrf-test-result" class="mt-2"></div>
        </div>

        <div class="test-section">
            <h3>4. Test de Listar Vehículos</h3>
            <button class="btn btn-success" onclick="testListarVehiculos()">Probar Listar Vehículos</button>
            <div id="vehiculos-test-result" class="mt-2"></div>
        </div>

        <div class="test-section">
            <h3>5. Test de Registro de Vehículo</h3>
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="test-placa" placeholder="Placa" value="TEST123">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="test-modelo" placeholder="Modelo" value="Toyota Test">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="test-color" placeholder="Color" value="Rojo">
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="test-tipo">
                        <option value="auto">Auto</option>
                        <option value="moto">Moto</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-warning mt-2" onclick="testRegistrarVehiculo()">Probar Registro</button>
            <div id="registro-test-result" class="mt-2"></div>
        </div>

        <div class="test-section">
            <h3>6. Verificación de Archivos</h3>
            <div id="files-check-result">Verificando...</div>
        </div>

        <div class="test-section">
            <h3>7. Test de Controlador Directo</h3>
            <button class="btn btn-info" onclick="testControllerDirect()">Test Directo</button>
            <div id="controller-test-result" class="mt-2"></div>
        </div>

        <div class="test-section">
            <h3>8. Debug Console</h3>
            <div id="debug-console" style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; height: 200px; overflow-y: auto;"></div>
        </div>
    </div>

    <script>
        let csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        
        function log(message, type = 'info') {
            const console = document.getElementById('debug-console');
            const timestamp = new Date().toLocaleTimeString();
            console.innerHTML += `[${timestamp}] ${type.toUpperCase()}: ${message}\n`;
            console.scrollTop = console.scrollHeight;
            console.log(message);
        }

        // Test 1: Base de datos
        async function testDatabase() {
            try {
                const response = await fetch('debug_vehiculos.php');
                const text = await response.text();
                document.getElementById('db-test-result').innerHTML = 
                    '<div class="info">Ver debug_vehiculos.php para detalles completos</div>';
                log('Test de BD iniciado - ver debug_vehiculos.php');
            } catch (error) {
                document.getElementById('db-test-result').innerHTML = 
                    `<div class="error">Error: ${error.message}</div>`;
                log('Error en test BD: ' + error.message, 'error');
            }
        }

        // Test 2: CSRF Token
        async function testCSRFToken() {
            try {
                log('Probando CSRF token...');
                const response = await fetch('api/csrf_token_fixed.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('csrf-test-result').innerHTML = 
                        '<div class="success">✅ CSRF Token obtenido correctamente</div>';
                    log('CSRF Token OK: ' + data.token.substring(0, 20) + '...');
                } else {
                    document.getElementById('csrf-test-result').innerHTML = 
                        '<div class="error">❌ Error obteniendo CSRF Token</div>';
                    log('Error CSRF: ' + data.message, 'error');
                }
            } catch (error) {
                document.getElementById('csrf-test-result').innerHTML = 
                    `<div class="error">❌ Error de conexión: ${error.message}</div>`;
                log('Error conexión CSRF: ' + error.message, 'error');
            }
        }

        // Test 3: Listar vehículos
        async function testListarVehiculos() {
            try {
                log('Probando listar vehículos...');
                const requestData = {
                    accion: 'listar_vehiculos',
                    csrf_token: csrfToken
                };

                const response = await fetch('controllers/VehiculoController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData)
                });

                log('Response status: ' + response.status);
                log('Response headers: ' + JSON.stringify([...response.headers.entries()]));

                const text = await response.text();
                log('Response text: ' + text.substring(0, 200) + '...');

                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        document.getElementById('vehiculos-test-result').innerHTML = 
                            `<div class="success">✅ Vehículos cargados: ${data.vehiculos.length}</div>`;
                        log(`Vehículos cargados: ${data.vehiculos.length}`);
                    } else {
                        document.getElementById('vehiculos-test-result').innerHTML = 
                            `<div class="error">❌ Error: ${data.message}</div>`;
                        log('Error en respuesta: ' + data.message, 'error');
                    }
                } catch (parseError) {
                    document.getElementById('vehiculos-test-result').innerHTML = 
                        `<div class="error">❌ Error parsing JSON: ${parseError.message}<br>Response: ${text.substring(0, 500)}</div>`;
                    log('Error parsing JSON: ' + parseError.message, 'error');
                    log('Raw response: ' + text, 'error');
                }
            } catch (error) {
                document.getElementById('vehiculos-test-result').innerHTML = 
                    `<div class="error">❌ Error de red: ${error.message}</div>`;
                log('Error de red: ' + error.message, 'error');
            }
        }

        // Test 4: Registrar vehículo
        async function testRegistrarVehiculo() {
            try {
                log('Probando registrar vehículo...');
                const requestData = {
                    accion: 'registrar_vehiculo',
                    csrf_token: csrfToken,
                    placa: document.getElementById('test-placa').value,
                    modelo: document.getElementById('test-modelo').value,
                    color: document.getElementById('test-color').value,
                    tipo_vehiculo: document.getElementById('test-tipo').value
                };

                const response = await fetch('controllers/VehiculoController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData)
                });

                const text = await response.text();
                log('Response: ' + text);

                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        document.getElementById('registro-test-result').innerHTML = 
                            `<div class="success">✅ Vehículo registrado: ID ${data.vehiculo_id}</div>`;
                        log(`Vehículo registrado con ID: ${data.vehiculo_id}`);
                    } else {
                        document.getElementById('registro-test-result').innerHTML = 
                            `<div class="error">❌ Error: ${data.message}</div>`;
                        log('Error registro: ' + data.message, 'error');
                    }
                } catch (parseError) {
                    document.getElementById('registro-test-result').innerHTML = 
                        `<div class="error">❌ Error parsing: ${parseError.message}</div>`;
                    log('Error parsing registro: ' + parseError.message, 'error');
                }
            } catch (error) {
                document.getElementById('registro-test-result').innerHTML = 
                    `<div class="error">❌ Error: ${error.message}</div>`;
                log('Error registro: ' + error.message, 'error');
            }
        }

        // Test 5: Verificar archivos
        async function checkFiles() {
            const files = [
                'models/Vehiculo.php',
                'controllers/VehiculoController.php',
                'api/csrf_token_fixed.php'
            ];
            
            let results = '';
            for (let file of files) {
                try {
                    const response = await fetch(file, { method: 'HEAD' });
                    if (response.ok) {
                        results += `✅ ${file} - OK<br>`;
                        log(`Archivo ${file} existe`);
                    } else {
                        results += `❌ ${file} - No encontrado (${response.status})<br>`;
                        log(`Archivo ${file} no encontrado: ${response.status}`, 'error');
                    }
                } catch (error) {
                    results += `❌ ${file} - Error: ${error.message}<br>`;
                    log(`Error verificando ${file}: ${error.message}`, 'error');
                }
            }
            document.getElementById('files-check-result').innerHTML = results;
        }

        // Test 6: Controlador directo
        async function testControllerDirect() {
            try {
                log('Probando acceso directo al controlador...');
                const response = await fetch('controllers/VehiculoController.php', {
                    method: 'GET'
                });
                
                const text = await response.text();
                log('Response directo: ' + text.substring(0, 200));
                
                if (response.status === 405) {
                    document.getElementById('controller-test-result').innerHTML = 
                        '<div class="success">✅ Controlador funciona (rechaza GET correctamente)</div>';
                    log('Controlador rechaza GET - comportamiento correcto');
                } else {
                    document.getElementById('controller-test-result').innerHTML = 
                        `<div class="info">Status: ${response.status}<br>Response: ${text.substring(0, 200)}</div>`;
                    log(`Response status: ${response.status}`);
                }
            } catch (error) {
                document.getElementById('controller-test-result').innerHTML = 
                    `<div class="error">Error: ${error.message}</div>`;
                log('Error test directo: ' + error.message, 'error');
            }
        }

        // Ejecutar tests automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            log('Iniciando tests automáticos...');
            testDatabase();
            checkFiles();
            
            // Auto-ejecutar test CSRF después de 1 segundo
            setTimeout(testCSRFToken, 1000);
        });
    </script>
</body>
</html>