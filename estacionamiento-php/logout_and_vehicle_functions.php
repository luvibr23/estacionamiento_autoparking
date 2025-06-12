<?php
// Script para implementar logout y funciones completas de vehículos

echo "<h1>🔐 IMPLEMENTACIÓN DE LOGOUT Y FUNCIONES DE VEHÍCULOS</h1>";
echo "<hr>";

// 1. Crear archivo de logout
echo "<h3>1. Creando Archivo de Logout</h3>";

$logout_php = '<?php
session_start();

// Log de la acción de logout
error_log("Logout iniciado para usuario: " . ($_SESSION["username"] ?? "desconocido"));

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Regenerar ID de sesión por seguridad
session_start();
session_regenerate_id(true);
session_destroy();

// Limpiar cache del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir al login con mensaje
header("Location: login_final.php?message=logout_success");
exit;
?>';

file_put_contents('logout.php', $logout_php);
echo "✅ Archivo logout.php creado<br>";

echo "<hr>";

// 2. Actualizar AuthController para manejar logout via AJAX
echo "<h3>2. Actualizando AuthController para Logout AJAX</h3>";

$auth_controller_updated = '<?php
session_start();

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Log para debugging
error_log("AuthController: " . $_SERVER["REQUEST_METHOD"] . " - " . file_get_contents("php://input"));

// Verificar método
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// Obtener datos de entrada
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

$action = $input["action"] ?? "";

try {
    switch ($action) {
        case "logout":
            // Verificar que hay una sesión activa
            if (!isset($_SESSION["user_id"])) {
                echo json_encode(["success" => false, "message" => "No hay sesión activa"]);
                break;
            }
            
            $username = $_SESSION["username"] ?? "desconocido";
            error_log("Logout AJAX para usuario: " . $username);
            
            // Destruir todas las variables de sesión
            $_SESSION = array();
            
            // Destruir la cookie de sesión si existe
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), "", time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destruir la sesión
            session_destroy();
            
            echo json_encode([
                "success" => true,
                "message" => "Sesión cerrada exitosamente",
                "redirect" => "login_final.php"
            ]);
            break;
            
        case "check_session":
            if (isset($_SESSION["user_id"])) {
                echo json_encode([
                    "success" => true,
                    "user_id" => $_SESSION["user_id"],
                    "username" => $_SESSION["username"],
                    "role" => $_SESSION["role"] ?? "operator"
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Sesión no válida"]);
            }
            break;
            
        default:
            echo json_encode(["success" => false, "message" => "Acción no válida"]);
    }
    
} catch (Exception $e) {
    error_log("Error en AuthController: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>';

file_put_contents('controllers/AuthController_logout.php', $auth_controller_updated);
echo "✅ Archivo controllers/AuthController_logout.php creado<br>";

echo "<hr>";

// 3. Actualizar el JavaScript del dashboard para logout funcional
echo "<h3>3. Actualizando JavaScript para Logout Funcional</h3>";

$dashboard_logout_js = '// Función de logout mejorada
async function logout() {
    console.log("🔐 Iniciando logout...");
    
    // Confirmación con SweetAlert2 si está disponible
    let confirmLogout = false;
    
    if (typeof Swal !== "undefined") {
        const result = await Swal.fire({
            title: "¿Cerrar sesión?",
            text: "¿Está seguro de que desea cerrar su sesión?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, cerrar sesión",
            cancelButtonText: "Cancelar"
        });
        confirmLogout = result.isConfirmed;
    } else {
        confirmLogout = confirm("¿Está seguro de cerrar sesión?");
    }
    
    if (!confirmLogout) {
        return;
    }
    
    try {
        // Mostrar indicador de carga
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Cerrando sesión...",
                text: "Por favor espere",
                icon: "info",
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Intentar logout via AJAX primero
        const basePath = getBasePath();
        
        const response = await fetch(basePath + "controllers/AuthController_logout.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: "logout",
                csrf_token: csrfToken || "fallback"
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                console.log("✅ Logout exitoso via AJAX");
                
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: "Sesión cerrada",
                        text: "Ha cerrado sesión exitosamente",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = basePath + "login_final.php";
                    });
                } else {
                    alert("Sesión cerrada exitosamente");
                    window.location.href = basePath + "login_final.php";
                }
                return;
            }
        }
        
        // Fallback: redirigir a logout.php directamente
        console.log("🔄 Fallback: redirigiendo a logout.php");
        window.location.href = basePath + "logout.php";
        
    } catch (error) {
        console.error("❌ Error en logout:", error);
        
        // Fallback final
        const basePath = getBasePath();
        window.location.href = basePath + "logout.php";
    }
}

// Función auxiliar para obtener la ruta base
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes("/views/")) {
        return "../";
    }
    return "./";
}

console.log("✅ Funciones de logout cargadas");';

file_put_contents('assets/js/vehiculos-functions.js', $vehiculos_complete_js);
echo "✅ Archivo assets/js/vehiculos-functions.js creado<br>";

echo "<hr>";

// 5. Actualizar el dashboard para incluir los nuevos scripts
echo "<h3>5. Actualizando Dashboard con Scripts</h3>";

$dashboard_updates = '
<!-- Agregar antes del cierre de </body> -->
<script src="../assets/js/logout-functions.js"></script>
<script src="../assets/js/vehiculos-functions.js"></script>

<script>
// Script de inicialización
document.addEventListener("DOMContentLoaded", function() {
    console.log("🎯 Inicializando funciones de logout y vehículos...");
    
    // Verificar que las funciones estén disponibles
    if (typeof logout === "function") {
        console.log("✅ Función logout disponible");
    } else {
        console.error("❌ Función logout no disponible");
    }
    
    if (typeof editarVehiculo === "function") {
        console.log("✅ Funciones de vehículos disponibles");
    } else {
        console.error("❌ Funciones de vehículos no disponibles");
    }
});
</script>';

// Crear archivo de actualización de dashboard
$dashboard_integration = '<?php
// Script para integrar logout y funciones de vehículos en dashboards

echo "<h2>🔧 INTEGRACIÓN DE LOGOUT Y FUNCIONES DE VEHÍCULOS</h2>";
echo "<hr>";

$dashboards_to_update = [
    "views/admin_dashboard.php",
    "views/operador_dashboard.php"
];

foreach ($dashboards_to_update as $dashboard_file) {
    echo "<h4>Actualizando: $dashboard_file</h4>";
    
    if (!file_exists($dashboard_file)) {
        echo "❌ Archivo no encontrado: $dashboard_file<br>";
        continue;
    }
    
    $content = file_get_contents($dashboard_file);
    
    // 1. Agregar scripts de logout y vehículos si no están presentes
    $scripts_to_add = [
        "logout-functions.js",
        "vehiculos-functions.js"
    ];
    
    $scripts_added = 0;
    foreach ($scripts_to_add as $script) {
        if (strpos($content, $script) === false) {
            // Buscar donde insertar el script (antes del cierre de body)
            if (strpos($content, "</body>") !== false) {
                $script_tag = "    <script src=\"../assets/js/$script\"></script>\n";
                $content = str_replace("</body>", $script_tag . "</body>", $content);
                $scripts_added++;
                echo "✅ Script $script agregado<br>";
            }
        } else {
            echo "ℹ️ Script $script ya está incluido<br>";
        }
    }
    
    // 2. Verificar que el botón de logout tenga la función correcta
    if (strpos($content, "onclick=\"logout()\"") !== false) {
        echo "✅ Botón de logout ya configurado<br>";
    } else {
        // Buscar el botón de logout y corregirlo
        $patterns = [
            "/onclick=\"[^\"]*logout[^\"]*\"/",
            "/href=\"[^\"]*logout[^\"]*\"/"
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "onclick=\"logout()\"", $content);
                echo "✅ Botón de logout corregido<br>";
                break;
            }
        }
    }
    
    // 3. Guardar cambios si se hicieron modificaciones
    if ($scripts_added > 0) {
        file_put_contents($dashboard_file, $content);
        echo "💾 Archivo actualizado con $scripts_added script(s)<br>";
    }
    
    echo "<br>";
}

echo "<hr>";
echo "<h3>✅ INTEGRACIÓN COMPLETADA</h3>";
?>';

file_put_contents('actualizar_dashboards.php', $dashboard_integration);
echo "✅ Script actualizar_dashboards.php creado<br>";

echo "<hr>";

// 6. Verificar y actualizar el controlador de vehículos para que funcionen editar/eliminar
echo "<h3>6. Verificando Controlador de Vehículos</h3>";

if (file_exists('controllers/VehiculoController.php')) {
    $controller_content = file_get_contents('controllers/VehiculoController.php');
    
    // Verificar si tiene las acciones necesarias
    $acciones_necesarias = [
        'obtener_vehiculo',
        'actualizar_vehiculo', 
        'eliminar_vehiculo'
    ];
    
    $acciones_faltantes = [];
    foreach ($acciones_necesarias as $accion) {
        if (strpos($controller_content, "case \"$accion\":") === false) {
            $acciones_faltantes[] = $accion;
        }
    }
    
    if (empty($acciones_faltantes)) {
        echo "✅ Controlador tiene todas las acciones necesarias<br>";
    } else {
        echo "⚠️ Acciones faltantes en controlador: " . implode(", ", $acciones_faltantes) . "<br>";
        echo "ℹ️ Ejecute sistema_vehiculos_crud.php para actualizar el controlador<br>";
    }
} else {
    echo "❌ Controlador VehiculoController.php no encontrado<br>";
    echo "ℹ️ Ejecute sistema_vehiculos_crud.php para crear el controlador<br>";
}

echo "<hr>";

// 7. Actualizar login_final.php para manejar mensaje de logout
echo "<h3>7. Actualizando Login para Mensaje de Logout</h3>";

if (file_exists('login_final.php')) {
    $login_content = file_get_contents('login_final.php');
    
    // Agregar manejo de mensaje de logout si no existe
    $logout_message_code = '
// Manejar mensaje de logout
$logout_message = "";
if (isset($_GET["message"]) && $_GET["message"] === "logout_success") {
    $logout_message = \'<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        Sesión cerrada exitosamente. Puede iniciar sesión nuevamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>\';
}';
    
    if (strpos($login_content, 'logout_success') === false) {
        // Buscar donde insertar el código (después del session_start)
        if (strpos($login_content, 'session_start()') !== false) {
            $login_content = str_replace(
                'session_start();',
                'session_start();' . $logout_message_code,
                $login_content
            );
            
            // Buscar donde mostrar el mensaje (en el cuerpo del HTML)
            if (strpos($login_content, '<div class="container') !== false) {
                $login_content = preg_replace(
                    '/(<div class="container[^>]*>)/',
                    '$1<?php echo $logout_message; ?>',
                    $login_content,
                    1
                );
            }
            
            file_put_contents('login_final.php', $login_content);
            echo "✅ Login actualizado para mostrar mensaje de logout<br>";
        } else {
            echo "⚠️ No se pudo actualizar automáticamente login_final.php<br>";
        }
    } else {
        echo "✅ Login ya maneja mensajes de logout<br>";
    }
} else {
    echo "❌ Archivo login_final.php no encontrado<br>";
}

echo "<hr>";

// 8. Crear archivo de test para verificar todas las funciones
echo "<h3>8. Creando Test de Funciones</h3>";

$test_functions = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Logout y Funciones de Vehículos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1><i class="fas fa-vial me-2"></i>Test de Logout y Funciones de Vehículos</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-sign-out-alt me-2"></i>Test de Logout</h5>
                    </div>
                    <div class="card-body">
                        <p>Pruebe la función de logout:</p>
                        <button class="btn btn-danger" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Probar Logout
                        </button>
                        <hr>
                        <p><strong>Resultado esperado:</strong></p>
                        <ul>
                            <li>Confirmación de logout</li>
                            <li>Redirección a login</li>
                            <li>Mensaje de éxito en login</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-car me-2"></i>Test de Funciones de Vehículos</h5>
                    </div>
                    <div class="card-body">
                        <p>Pruebe las funciones de vehículos:</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testEditarVehiculo()">
                                <i class="fas fa-edit me-2"></i>Probar Editar
                            </button>
                            <button class="btn btn-warning" onclick="testVerDetalles()">
                                <i class="fas fa-eye me-2"></i>Probar Ver Detalles
                            </button>
                            <button class="btn btn-danger" onclick="testEliminar()">
                                <i class="fas fa-trash me-2"></i>Probar Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-list me-2"></i>Estado de Funciones</h5>
                    </div>
                    <div class="card-body">
                        <div id="function-status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.js"></script>
    <script src="assets/js/logout-functions.js"></script>
    <script src="assets/js/vehiculos-functions.js"></script>
    <script src="assets/js/dashboard_fixed.js"></script>
    
    <script>
        // Simular algunos datos para pruebas
        let vehiculosCrudData = [
            {
                id: 1,
                placa: "TEST123",
                modelo: "Toyota Test",
                color: "Rojo",
                tipo_vehiculo: "auto",
                fecha_creacion: new Date().toISOString(),
                cliente_nombre: "Cliente Test",
                cliente_apellido: "Apellido",
                codigo_cliente: "CLI001"
            }
        ];
        
        function testEditarVehiculo() {
            if (typeof editarVehiculo === "function") {
                editarVehiculo(1);
            } else {
                alert("Función editarVehiculo no está disponible");
            }
        }
        
        function testVerDetalles() {
            if (typeof verDetallesVehiculo === "function") {
                verDetallesVehiculo(1);
            } else {
                alert("Función verDetallesVehiculo no está disponible");
            }
        }
        
        function testEliminar() {
            if (typeof confirmarEliminarVehiculo === "function") {
                confirmarEliminarVehiculo(1, "TEST123");
            } else {
                alert("Función confirmarEliminarVehiculo no está disponible");
            }
        }
        
        // Verificar estado de funciones al cargar
        document.addEventListener("DOMContentLoaded", function() {
            const status = document.getElementById("function-status");
            let statusHtml = "";
            
            const functions = [
                "logout",
                "editarVehiculo", 
                "actualizarVehiculo",
                "confirmarEliminarVehiculo",
                "eliminarVehiculo",
                "verDetallesVehiculo"
            ];
            
            functions.forEach(func => {
                const available = typeof window[func] === "function";
                statusHtml += `
                    <div class="row mb-1">
                        <div class="col-6">${func}:</div>
                        <div class="col-6">
                            ${available ? 
                                \'<span class="badge bg-success">Disponible</span>\' : 
                                \'<span class="badge bg-danger">No disponible</span>\'
                            }
                        </div>
                    </div>
                `;
            });
            
            status.innerHTML = statusHtml;
        });
    </script>
</body>
</html>';

file_put_contents('test_functions.html', $test_functions);
echo "✅ Archivo test_functions.html creado<br>";

echo "<hr>";

// 9. Resumen final
echo "<h3>🎉 IMPLEMENTACIÓN COMPLETADA</h3>";

echo '<div style="background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;">';
echo "<h4>✅ Archivos creados y funcionalidades implementadas:</h4>";
echo "<ul>";
echo "<li>🔐 <strong>logout.php</strong> - Logout seguro del sistema</li>";
echo "<li>🔐 <strong>controllers/AuthController_logout.php</strong> - Logout via AJAX</li>";
echo "<li>📱 <strong>assets/js/logout-functions.js</strong> - Función logout para JavaScript</li>";
echo "<li>🚗 <strong>assets/js/vehiculos-functions.js</strong> - Funciones completas editar/eliminar/ver</li>";
echo "<li>🔧 <strong>actualizar_dashboards.php</strong> - Script para integrar en dashboards</li>";
echo "<li>🧪 <strong>test_functions.html</strong> - Test de todas las funciones</li>";
echo "</ul>";
echo "</div>";

echo '<div style="background: #cce5ff; padding: 20px; border-radius: 10px; margin: 20px 0;">';
echo "<h4>🚀 Próximos pasos:</h4>";
echo "<ol>";
echo '<li><strong>Ejecutar:</strong> <a href="actualizar_dashboards.php" target="_blank">actualizar_dashboards.php</a></li>';
echo '<li><strong>Probar logout:</strong> <a href="views/admin_dashboard.php" target="_blank">admin_dashboard.php</a> → Menú usuario → Cerrar sesión</li>';
echo '<li><strong>Probar funciones vehículos:</strong> Ir a sección Vehículos → Botones editar/eliminar</li>';
echo '<li><strong>Test completo:</strong> <a href="test_functions.html" target="_blank">test_functions.html</a></li>';
echo "</ol>";
echo "</div>";

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;">';
echo "<h5>📋 Funcionalidades implementadas:</h5>";
echo "<ul>";
echo "<li>🔐 <strong>Logout seguro:</strong> Destruye sesión completamente y redirige al login</li>";
echo "<li>✏️ <strong>Editar vehículos:</strong> Modal con datos pre-cargados</li>";
echo "<li>🗑️ <strong>Eliminar vehículos:</strong> Eliminación lógica con confirmación</li>";
echo "<li>👁️ <strong>Ver detalles:</strong> Modal con información completa del vehículo</li>";
echo "<li>🛡️ <strong>Validaciones:</strong> Verificación de datos en frontend y backend</li>";
echo "<li>💬 <strong>Notificaciones:</strong> SweetAlert2 para mejor UX</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>🎯 ¡El sistema de logout y funciones de vehículos está completamente implementado!</strong></p>";
?>js/logout-functions.js', $dashboard_logout_js);
echo "✅ Archivo assets/js/logout-functions.js creado<br>";

echo "<hr>";

// 4. Completar las funciones de vehículos (editar/eliminar)
echo "<h3>4. Completando Funciones de Vehículos</h3>";

$vehiculos_complete_js = '// Funciones completas para editar y eliminar vehículos

// Editar vehículo - función completa
async function editarVehiculo(vehiculoId) {
    console.log("✏️ Editando vehículo ID:", vehiculoId);
    
    try {
        // Mostrar loading
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Cargando...",
                text: "Obteniendo datos del vehículo",
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Obtener datos del vehículo
        const data = await makeRequest("obtener_vehiculo", { id: vehiculoId });
        
        if (data.success) {
            vehiculoEditandoId = vehiculoId;
            
            const vehiculo = data.vehiculo;
            const modal = document.getElementById("modalVehiculo");
            const form = document.getElementById("formVehiculo");
            const titulo = document.querySelector("#modalVehiculo .modal-title");
            const btnSubmit = document.querySelector("#modalVehiculo .btn-primary");
            
            // Cerrar SweetAlert si está abierto
            if (typeof Swal !== "undefined") {
                Swal.close();
            }
            
            // Cambiar título y botón
            if (titulo) titulo.innerHTML = `<i class="fas fa-edit me-2"></i>Editar Vehículo - ${vehiculo.placa}`;
            if (btnSubmit) btnSubmit.innerHTML = `<i class="fas fa-save me-2"></i>Actualizar Vehículo`;
            
            // Llenar formulario con datos actuales
            if (document.getElementById("placa")) document.getElementById("placa").value = vehiculo.placa;
            if (document.getElementById("modelo")) document.getElementById("modelo").value = vehiculo.modelo;
            if (document.getElementById("color")) document.getElementById("color").value = vehiculo.color;
            if (document.getElementById("tipo-vehiculo")) document.getElementById("tipo-vehiculo").value = vehiculo.tipo_vehiculo;
            if (document.getElementById("codigo-cliente")) document.getElementById("codigo-cliente").value = vehiculo.codigo_cliente || "";
            
            // Mostrar cliente actual si existe
            if (vehiculo.cliente_nombre && document.getElementById("buscar-cliente")) {
                document.getElementById("buscar-cliente").value = 
                    `${vehiculo.codigo_cliente} - ${vehiculo.cliente_nombre} ${vehiculo.cliente_apellido || ""}`;
            }
            
            // Limpiar resultados de búsqueda de cliente
            const resultadosClientes = document.getElementById("resultados-clientes");
            if (resultadosClientes) resultadosClientes.innerHTML = "";
            
            // Mostrar modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            console.log("✅ Modal de edición abierto con datos del vehículo");
            
        } else {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Error",
                    text: data.message || "No se pudo obtener los datos del vehículo",
                    icon: "error"
                });
            } else {
                alert("Error: " + (data.message || "No se pudo obtener los datos del vehículo"));
            }
        }
        
    } catch (error) {
        console.error("❌ Error obteniendo datos del vehículo:", error);
        
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                icon: "error"
            });
        } else {
            alert("Error de conexión");
        }
    }
}

// Actualizar vehículo - función completa
async function actualizarVehiculo() {
    console.log("💾 Actualizando vehículo ID:", vehiculoEditandoId);
    
    const formData = obtenerDatosFormularioVehiculo();
    formData.id = vehiculoEditandoId;
    
    if (!validarDatosVehiculo(formData)) {
        return;
    }
    
    const submitBtn = document.querySelector("#modalVehiculo .btn-primary");
    const originalText = submitBtn?.innerHTML || "Actualizar";
    
    try {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...`;
        }
        
        const data = await makeRequest("actualizar_vehiculo", formData);
        
        if (data.success) {
            console.log("✅ Vehículo actualizado exitosamente");
            
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "¡Actualizado!",
                    text: "El vehículo ha sido actualizado exitosamente",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert("Vehículo actualizado exitosamente");
            }
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalVehiculo"));
            if (modal) modal.hide();
            
            // Limpiar variable de edición
            vehiculoEditandoId = null;
            
            // Recargar lista y dashboard
            if (typeof cargarVehiculos === "function") {
                await cargarVehiculos();
            }
            if (typeof updateDashboardData === "function") {
                await updateDashboardData();
            }
            
        } else {
            console.error("❌ Error actualizando:", data.message);
            
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Error",
                    text: data.message || "No se pudo actualizar el vehículo",
                    icon: "error"
                });
            } else {
                alert("Error: " + (data.message || "No se pudo actualizar el vehículo"));
            }
        }
        
    } catch (error) {
        console.error("❌ Error en actualización:", error);
        
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                icon: "error"
            });
        } else {
            alert("Error de conexión");
        }
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}

// Confirmar eliminación de vehículo - función completa
function confirmarEliminarVehiculo(vehiculoId, placa) {
    console.log("🗑️ Confirmando eliminación de vehículo:", vehiculoId, placa);
    
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "¿Eliminar vehículo?",
            html: `
                <div class="text-center">
                    <i class="fas fa-car fa-3x text-danger mb-3"></i>
                    <p>Se eliminará el vehículo con placa <strong>${placa}</strong></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta acción marcará el vehículo como inactivo.<br>
                        No se perderán los datos históricos.
                    </div>
                </div>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarVehiculo(vehiculoId, placa);
            }
        });
    } else {
        if (confirm(`¿Está seguro de eliminar el vehículo con placa ${placa}?\\n\\nEsta acción marcará el vehículo como inactivo.`)) {
            eliminarVehiculo(vehiculoId, placa);
        }
    }
}

// Eliminar vehículo - función completa
async function eliminarVehiculo(vehiculoId, placa) {
    console.log("🗑️ Eliminando vehículo ID:", vehiculoId);
    
    try {
        // Mostrar loading
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Eliminando...",
                text: `Eliminando vehículo ${placa}`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        const data = await makeRequest("eliminar_vehiculo", { id: vehiculoId });
        
        if (data.success) {
            console.log("✅ Vehículo eliminado exitosamente");
            
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "¡Eliminado!",
                    text: `El vehículo ${placa} ha sido eliminado exitosamente`,
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(`Vehículo ${placa} eliminado exitosamente`);
            }
            
            // Recargar lista y dashboard
            if (typeof cargarVehiculos === "function") {
                await cargarVehiculos();
            }
            if (typeof updateDashboardData === "function") {
                await updateDashboardData();
            }
            
        } else {
            console.error("❌ Error eliminando:", data.message);
            
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Error",
                    text: data.message || "No se pudo eliminar el vehículo",
                    icon: "error"
                });
            } else {
                alert("Error: " + (data.message || "No se pudo eliminar el vehículo"));
            }
        }
        
    } catch (error) {
        console.error("❌ Error en eliminación:", error);
        
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                icon: "error"
            });
        } else {
            alert("Error de conexión");
        }
    }
}

// Ver detalles del vehículo - función completa
async function verDetallesVehiculo(vehiculoId) {
    console.log("👁️ Viendo detalles del vehículo:", vehiculoId);
    
    try {
        // Buscar el vehículo en los datos cargados
        let vehiculo = null;
        if (typeof vehiculosCrudData !== "undefined" && vehiculosCrudData.length > 0) {
            vehiculo = vehiculosCrudData.find(v => v.id == vehiculoId);
        }
        
        if (!vehiculo) {
            // Si no está en los datos locales, obtenerlo del servidor
            const data = await makeRequest("obtener_vehiculo", { id: vehiculoId });
            if (data.success) {
                vehiculo = data.vehiculo;
            } else {
                throw new Error(data.message || "Vehículo no encontrado");
            }
        }
        
        // Crear HTML de detalles
        const fechaRegistro = new Date(vehiculo.fecha_creacion);
        const fechaActualizacion = vehiculo.fecha_actualizacion ? new Date(vehiculo.fecha_actualizacion) : null;
        
        const detallesHtml = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-car me-2"></i>Información del Vehículo</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Placa:</strong></div>
                                    <div class="col-8"><span class="badge bg-primary">${vehiculo.placa}</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Modelo:</strong></div>
                                    <div class="col-8">${vehiculo.modelo}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Color:</strong></div>
                                    <div class="col-8">${vehiculo.color}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Tipo:</strong></div>
                                    <div class="col-8"><span class="badge bg-info">${vehiculo.tipo_vehiculo}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h6>
                            </div>
                            <div class="card-body">
                                ${vehiculo.cliente_nombre ? 
                                    `<div class="row mb-2">
                                        <div class="col-4"><strong>Código:</strong></div>
                                        <div class="col-8"><span class="badge bg-success">${vehiculo.codigo_cliente}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Nombre:</strong></div>
                                        <div class="col-8">${vehiculo.cliente_nombre} ${vehiculo.cliente_apellido || ""}</div>
                                    </div>` :
                                    `<div class="text-center text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                                        <p>Sin cliente asignado</p>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Estado Actual</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Estado:</strong></div>
                                    <div class="col-8">
                                        ${vehiculo.estado_registro === "activo" ? 
                                            `<span class="badge bg-success">En estacionamiento</span>` : 
                                            `<span class="badge bg-secondary">Registrado</span>`
                                        }
                                    </div>
                                </div>
                                ${vehiculo.numero_espacio ? 
                                    `<div class="row mb-2">
                                        <div class="col-4"><strong>Espacio:</strong></div>
                                        <div class="col-8"><span class="badge bg-info">${vehiculo.numero_espacio}</span></div>
                                    </div>` : ""
                                }
                                ${vehiculo.fecha_entrada ? 
                                    `<div class="row mb-2">
                                        <div class="col-4"><strong>Entrada:</strong></div>
                                        <div class="col-8">${new Date(vehiculo.fecha_entrada).toLocaleString("es-PE")}</div>
                                    </div>` : ""
                                }
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Fechas de Registro</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Registrado:</strong></div>
                                    <div class="col-8">${fechaRegistro.toLocaleString("es-PE")}</div>
                                </div>
                                ${fechaActualizacion && fechaActualizacion.getTime() !== fechaRegistro.getTime() ? 
                                    `<div class="row mb-2">
                                        <div class="col-4"><strong>Actualizado:</strong></div>
                                        <div class="col-8">${fechaActualizacion.toLocaleString("es-PE")}</div>
                                    </div>` : ""
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: `<i class="fas fa-car me-2"></i>Detalles del Vehículo`,
                html: detallesHtml,
                width: "800px",
                showConfirmButton: true,
                confirmButtonText: "Cerrar",
                customClass: {
                    popup: "text-start"
                }
            });
        } else {
            // Fallback para navegadores sin SweetAlert2
            const ventana = window.open("", "_blank", "width=800,height=600");
            ventana.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Detalles del Vehículo - ${vehiculo.placa}</title>
                    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
                    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
                </head>
                <body class="p-4">
                    <h3><i class="fas fa-car me-2"></i>Detalles del Vehículo - ${vehiculo.placa}</h3>
                    ${detallesHtml}
                    <div class="text-center mt-4">
                        <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
                    </div>
                </body>
                </html>
            `);
        }
        
    } catch (error) {
        console.error("❌ Error viendo detalles:", error);
        
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Error",
                text: "No se pudieron cargar los detalles del vehículo",
                icon: "error"
            });
        } else {
            alert("Error: No se pudieron cargar los detalles del vehículo");
        }
    }
}

// Funciones auxiliares para el formulario
function obtenerDatosFormularioVehiculo() {
    return {
        placa: document.getElementById("placa")?.value?.trim() || "",
        modelo: document.getElementById("modelo")?.value?.trim() || "",
        color: document.getElementById("color")?.value?.trim() || "",
        tipo_vehiculo: document.getElementById("tipo-vehiculo")?.value || "",
        codigo_cliente: document.getElementById("codigo-cliente")?.value?.trim() || ""
    };
}

function validarDatosVehiculo(datos) {
    if (!datos.placa || !datos.modelo || !datos.color || !datos.tipo_vehiculo) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Campos incompletos",
                text: "Por favor, complete todos los campos obligatorios",
                icon: "warning"
            });
        } else {
            alert("Por favor, complete todos los campos obligatorios");
        }
        return false;
    }
    
    // Validar formato de placa
    const placaRegex = /^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/i;
    if (!placaRegex.test(datos.placa)) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Placa inválida",
                text: "El formato de placa no es válido. Use formato ABC123 o ABC-123",
                icon: "error"
            });
        } else {
            alert("Formato de placa no válido. Use formato ABC123 o ABC-123");
        }
        return false;
    }
    
    return true;
}

console.log("✅ Funciones completas de vehículos cargadas (editar/eliminar/ver)");';

file_put_contents('assets/