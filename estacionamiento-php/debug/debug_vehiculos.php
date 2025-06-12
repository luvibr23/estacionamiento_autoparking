<?php
// Script de diagnóstico para verificar el sistema de vehículos
session_start();

echo "<h2>🔍 DIAGNÓSTICO SISTEMA DE VEHÍCULOS</h2>";
echo "<hr>";

// 1. Verificar sesión
echo "<h3>1. Verificación de Sesión</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Sesión activa - Usuario ID: " . $_SESSION['user_id'] . "<br>";
    echo "✅ Username: " . ($_SESSION['username'] ?? 'No definido') . "<br>";
    echo "✅ Rol: " . ($_SESSION['role'] ?? 'No definido') . "<br>";
    echo "✅ CSRF Token: " . (isset($_SESSION['csrf_token']) ? 'Presente' : 'No presente') . "<br>";
} else {
    echo "❌ No hay sesión activa<br>";
    echo "<a href='login_final.php'>Ir al Login</a><br>";
}
echo "<hr>";

// 2. Verificar conexión a base de datos
echo "<h3>2. Verificación de Base de Datos</h3>";
try {
    require_once 'config/database.php';
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Conexión a base de datos exitosa<br>";
        
        // Verificar tablas
        $tablas = ['usuarios', 'clientes', 'vehiculos', 'espacios_estacionamiento', 'registros_estacionamiento', 'tarifas'];
        foreach ($tablas as $tabla) {
            $result = $conn->query("SHOW TABLES LIKE '$tabla'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Tabla '$tabla' existe<br>";
            } else {
                echo "❌ Tabla '$tabla' NO existe<br>";
            }
        }
    } else {
        echo "❌ Error en conexión a base de datos<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 3. Verificar archivos del modelo
echo "<h3>3. Verificación de Archivos</h3>";
$archivos = [
    'models/Vehiculo.php',
    'controllers/VehiculoController.php',
    'api/csrf_token_fixed.php',
    'api/check_session.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ Archivo '$archivo' existe<br>";
    } else {
        echo "❌ Archivo '$archivo' NO existe<br>";
    }
}
echo "<hr>";

// 4. Probar modelo Vehiculo
echo "<h3>4. Prueba del Modelo Vehículo</h3>";
try {
    if (file_exists('models/Vehiculo.php')) {
        require_once 'models/Vehiculo.php';
        $vehiculo = new Vehiculo();
        echo "✅ Clase Vehiculo instanciada correctamente<br>";
        
        // Probar obtener vehículos
        $resultado = $vehiculo->obtenerVehiculos();
        if ($resultado['success']) {
            echo "✅ Consulta de vehículos exitosa<br>";
            echo "📊 Vehículos encontrados: " . count($resultado['vehiculos']) . "<br>";
        } else {
            echo "❌ Error en consulta: " . $resultado['message'] . "<br>";
        }
    } else {
        echo "❌ Archivo del modelo no encontrado<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en modelo: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 5. Probar controlador
echo "<h3>5. Prueba del Controlador</h3>";
if (isset($_SESSION['user_id']) && isset($_SESSION['csrf_token'])) {
    echo "✅ Datos de sesión disponibles para prueba<br>";
    
    // Simular petición AJAX
    $testData = [
        'accion' => 'listar_vehiculos',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "📝 Datos de prueba preparados<br>";
    echo "🔗 URL del controlador: controllers/VehiculoController.php<br>";
} else {
    echo "❌ No se puede probar controlador sin sesión válida<br>";
}
echo "<hr>";

// 6. Verificar estructura de base de datos
echo "<h3>6. Estructura de Tablas</h3>";
try {
    if (isset($conn)) {
        // Verificar tabla vehículos
        $result = $conn->query("DESCRIBE vehiculos");
        if ($result) {
            echo "✅ Estructura tabla 'vehiculos':<br>";
            echo "<table border='1' style='margin: 10px;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Contar registros
        $result = $conn->query("SELECT COUNT(*) as total FROM vehiculos");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "📊 Total vehículos en BD: " . $row['total'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error verificando estructura: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 7. Información del servidor
echo "<h3>7. Información del Servidor</h3>";
echo "🌐 PHP Version: " . phpversion() . "<br>";
echo "🗄️ MySQL Extension: " . (extension_loaded('mysqli') ? 'Disponible' : 'No disponible') . "<br>";
echo "📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "🔗 Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "🕒 Fecha/Hora: " . date('Y-m-d H:i:s') . "<br>";

echo "<hr>";
echo "<h3>8. Soluciones Recomendadas</h3>";

if (!isset($_SESSION['user_id'])) {
    echo "🔧 <strong>Solución 1:</strong> <a href='login_final.php'>Iniciar sesión primero</a><br>";
}

if (!file_exists('models/Vehiculo.php')) {
    echo "🔧 <strong>Solución 2:</strong> Crear el archivo models/Vehiculo.php<br>";
}

if (!file_exists('controllers/VehiculoController.php')) {
    echo "🔧 <strong>Solución 3:</strong> Crear el archivo controllers/VehiculoController.php<br>";
}

echo "🔧 <strong>Solución 4:</strong> Verificar que todas las tablas estén creadas ejecutando estructura_vehiculos.sql<br>";
echo "🔧 <strong>Solución 5:</strong> Verificar la ruta de los archivos JavaScript en el dashboard<br>";

echo "<hr>";
echo "<p><a href='views/admin_dashboard.php'>Ir al Dashboard Admin</a> | ";
echo "<a href='views/operador_dashboard.php'>Ir al Dashboard Operador</a></p>";
?>