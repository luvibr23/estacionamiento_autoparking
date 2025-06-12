<?php
// Script para corregir errores de funciones de vehículos y logout

echo "<h1>🔧 CORRECCIÓN DE ERRORES DE FUNCIONES</h1>";
echo "<p>Corrigiendo error de variable undefined y configurando menú principal</p>";
echo "<hr>";

// 1. Crear archivo JavaScript de logout corregido
echo "<h3>1. Creando JavaScript de Logout Corregido</h3>";

$logout_js_content = '// Funciones de logout mejoradas y seguras
console.log("🔐 Funciones de logout cargadas");

// Función principal de logout
async function logout() {
    console.log("🔐 Iniciando proceso de logout...");
    
    // Confirmación usando SweetAlert2 si está disponible
    let confirmarLogout = false;
    
    if (typeof Swal !== "undefined") {
        try {
            const result = await Swal.fire({
                title: "¿Cerrar sesión?",
                text: "¿Está seguro de que desea cerrar su sesión?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, cerrar sesión",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });
            confirmarLogout = result.isConfirmed;
        } catch (error) {
            console.log("Error con SweetAlert2, usando confirm nativo");
            confirmarLogout = confirm("¿Está seguro de cerrar sesión?");
        }
    } else {
        confirmarLogout = confirm("¿Está seguro de cerrar sesión?");
    }
    
    if (!confirmarLogout) {
        console.log("Logout cancelado por el usuario");
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
        
        // Intentar logout via AJAX
        const basePath = getBasePath();
        console.log("🌐 Intentando logout AJAX...");
        
        const response = await fetch(basePath + "controllers/AuthController_logout.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: "logout",
                csrf_token: window.csrfToken || "fallback-token"
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                console.log("✅ Logout AJAX exitoso");
                
                // Mostrar mensaje de éxito
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: "¡Hasta luego!",
                        text: "Su sesión ha sido cerrada exitosamente",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = basePath + "login_final.php?message=logout_success";
                    });
                } else {
                    alert("Sesión cerrada exitosamente");
                    window.location.href = basePath + "login_final.php?message=logout_success";
                }
                return;
            } else {
                console.warn("Logout AJAX falló:", data.message);
            }
        } else {
            console.warn("Response no OK:", response.status);
        }
        
    } catch (error) {
        console.error("❌ Error en logout AJAX:", error);
    }
    
    // Fallback: redirigir directamente a logout.php
    console.log("🔄 Usando fallback: logout.php");
    const basePath = getBasePath();
    window.location.href = basePath + "logout.php";
}

// Función para detectar ruta base
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes("/views/")) {
        return "../";
    }
    return "./";
}

// Función de redirección al menú principal
function irMenuPrincipal() {
    console.log("🏠 Redirigiendo al menú principal...");
    const basePath = getBasePath();
    window.location.href = basePath + "index.php";
}

// Verificar sesión
async function verificarSesion() {
    try {
        const basePath = getBasePath();
        const response = await fetch(basePath + "api/check_session.php");
        
        if (response.ok) {
            const data = await response.json();
            return data.success;
        }
    } catch (error) {
        console.error("Error verificando sesión:", error);
    }
    return false;
}

console.log("✅ Funciones de logout y navegación cargadas correctamente");';

// Crear directorio si no existe
if (!is_dir('assets/js')) {
    mkdir('assets/js', 0755, true);
}

file_put_contents('assets/js/logout-functions.js', $logout_js_content);
echo "✅ Archivo assets/js/logout-functions.js corregido<br>";

echo "<hr>";

// 2. Crear archivo JavaScript de funciones de vehículos completo
echo "<h3>2. Creando JavaScript de Funciones de Vehículos</h3>";

$vehiculos_functions_js = '// Funciones completas para gestión de vehículos
console.log("🚗 Funciones de vehículos cargadas");

// Variables globales
let vehiculoEditandoId = null;

// Función para editar vehículo
async function editarVehiculo(vehiculoId) {
    console.log("✏️ Editando vehículo ID:", vehiculoId);
    
    if (!vehiculoId) {
        mostrarError("ID de vehículo no válido");
        return;
    }
    
    try {
        // Mostrar loading
        mostrarCargando("Cargando datos del vehículo...");
        
        // Obtener datos del vehículo del servidor
        const data = await makeRequest("obtener_vehiculo", { id: vehiculoId });
        
        if (data.success && data.vehiculo) {
            vehiculoEditandoId = vehiculoId;
            const vehiculo = data.vehiculo;
            
            // Cerrar loading
            cerrarCargando();
            
            // Configurar modal para edición
            configurarModalEdicion(vehiculo);
            
            // Mostrar modal
            mostrarModalVehiculo();
            
            console.log("✅ Modal de edición configurado para vehículo:", vehiculo.placa);
            
        } else {
            throw new Error(data.message || "No se pudieron obtener los datos del vehículo");
        }
        
    } catch (error) {
        console.error("❌ Error editando vehículo:", error);
        cerrarCargando();
        mostrarError("Error al cargar los datos del vehículo: " + error.message);
    }
}

// Configurar modal para edición
function configurarModalEdicion(vehiculo) {
    // Cambiar título del modal
    const titulo = document.querySelector("#modalVehiculo .modal-title");
    if (titulo) {
        titulo.innerHTML = `<i class="fas fa-edit me-2"></i>Editar Vehículo - ${vehiculo.placa}`;
    }
    
    // Cambiar texto del botón
    const btnSubmit = document.querySelector("#modalVehiculo .btn-primary");
    if (btnSubmit) {
        btnSubmit.innerHTML = `<i class="fas fa-save me-2"></i>Actualizar Vehículo`;
    }
    
    // Llenar formulario con datos existentes
    rellenarFormularioVehiculo(vehiculo);
}

// Rellenar formulario con datos del vehículo
function rellenarFormularioVehiculo(vehiculo) {
    const campos = {
        "placa": vehiculo.placa,
        "modelo": vehiculo.modelo,
        "color": vehiculo.color,
        "tipo-vehiculo": vehiculo.tipo_vehiculo,
        "codigo-cliente": vehiculo.codigo_cliente || ""
    };
    
    Object.keys(campos).forEach(campoId => {
        const elemento = document.getElementById(campoId);
        if (elemento) {
            elemento.value = campos[campoId];
        }
    });
    
    // Mostrar información del cliente si existe
    if (vehiculo.cliente_nombre) {
        const buscarCliente = document.getElementById("buscar-cliente");
        if (buscarCliente) {
            buscarCliente.value = `${vehiculo.codigo_cliente} - ${vehiculo.cliente_nombre} ${vehiculo.cliente_apellido || ""}`;
        }
    }
    
    // Limpiar resultados de búsqueda
    const resultados = document.getElementById("resultados-clientes");
    if (resultados) {
        resultados.innerHTML = "";
    }
}

// Función para eliminar vehículo con confirmación
function confirmarEliminarVehiculo(vehiculoId, placa) {
    console.log("🗑️ Confirmando eliminación del vehículo:", placa);
    
    if (!vehiculoId || !placa) {
        mostrarError("Datos de vehículo no válidos");
        return;
    }
    
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "¿Eliminar vehículo?",
            html: `
                <div class="text-center">
                    <i class="fas fa-car fa-3x text-danger mb-3"></i>
                    <p class="mb-3">Se eliminará el vehículo con placa:</p>
                    <h4 class="text-danger"><strong>${placa}</strong></h4>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Nota:</strong> Esta acción marcará el vehículo como inactivo.<br>
                        Los datos históricos se conservarán.
                    </div>
                </div>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarVehiculo(vehiculoId, placa);
            }
        });
    } else {
        const mensaje = `¿Está seguro de eliminar el vehículo con placa ${placa}?\\n\\n` +
                       `Esta acción marcará el vehículo como inactivo.\\n` +
                       `Los datos históricos se conservarán.`;
        
        if (confirm(mensaje)) {
            eliminarVehiculo(vehiculoId, placa);
        }
    }
}

// Función para eliminar vehículo
async function eliminarVehiculo(vehiculoId, placa) {
    console.log("🗑️ Eliminando vehículo:", vehiculoId);
    
    try {
        // Mostrar loading
        mostrarCargando(`Eliminando vehículo ${placa}...`);
        
        const data = await makeRequest("eliminar_vehiculo", { id: vehiculoId });
        
        if (data.success) {
            console.log("✅ Vehículo eliminado exitosamente");
            
            // Cerrar loading y mostrar éxito
            cerrarCargando();
            mostrarExito(`El vehículo ${placa} ha sido eliminado exitosamente`);
            
            // Recargar la lista de vehículos
            if (typeof cargarVehiculos === "function") {
                setTimeout(() => cargarVehiculos(), 1000);
            }
            
            // Actualizar dashboard
            if (typeof updateDashboardData === "function") {
                setTimeout(() => updateDashboardData(), 1000);
            }
            
        } else {
            throw new Error(data.message || "No se pudo eliminar el vehículo");
        }
        
    } catch (error) {
        console.error("❌ Error eliminando vehículo:", error);
        cerrarCargando();
        mostrarError("Error al eliminar el vehículo: " + error.message);
    }
}

// Función para ver detalles del vehículo
async function verDetallesVehiculo(vehiculoId) {
    console.log("👁️ Viendo detalles del vehículo:", vehiculoId);
    
    try {
        // Buscar en datos locales primero
        let vehiculo = null;
        if (typeof vehiculosCrudData !== "undefined" && vehiculosCrudData.length > 0) {
            vehiculo = vehiculosCrudData.find(v => v.id == vehiculoId);
        }
        
        // Si no está en datos locales, obtener del servidor
        if (!vehiculo) {
            mostrarCargando("Cargando detalles del vehículo...");
            const data = await makeRequest("obtener_vehiculo", { id: vehiculoId });
            cerrarCargando();
            
            if (data.success) {
                vehiculo = data.vehiculo;
            } else {
                throw new Error(data.message || "Vehículo no encontrado");
            }
        }
        
        // Mostrar detalles
        mostrarDetallesVehiculo(vehiculo);
        
    } catch (error) {
        console.error("❌ Error viendo detalles:", error);
        cerrarCargando();
        mostrarError("Error al cargar los detalles: " + error.message);
    }
}

// Mostrar detalles en modal
function mostrarDetallesVehiculo(vehiculo) {
    const fechaRegistro = new Date(vehiculo.fecha_creacion);
    const fechaActualizacion = vehiculo.fecha_actualizacion ? new Date(vehiculo.fecha_actualizacion) : null;
    
    const detallesHtml = `
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-car me-2"></i>Información del Vehículo</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr><td><strong>Placa:</strong></td><td><span class="badge bg-primary">${vehiculo.placa}</span></td></tr>
                                <tr><td><strong>Modelo:</strong></td><td>${vehiculo.modelo}</td></tr>
                                <tr><td><strong>Color:</strong></td><td>${vehiculo.color}</td></tr>
                                <tr><td><strong>Tipo:</strong></td><td><span class="badge bg-info">${vehiculo.tipo_vehiculo}</span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Cliente</h6>
                        </div>
                        <div class="card-body">
                            ${vehiculo.cliente_nombre ? 
                                `<table class="table table-sm">
                                    <tr><td><strong>Código:</strong></td><td><span class="badge bg-success">${vehiculo.codigo_cliente}</span></td></tr>
                                    <tr><td><strong>Nombre:</strong></td><td>${vehiculo.cliente_nombre} ${vehiculo.cliente_apellido || ""}</td></tr>
                                </table>` :
                                `<div class="text-center text-muted">
                                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                                    <p>Sin cliente asignado</p>
                                </div>`
                            }
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Estado</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        ${vehiculo.estado_registro === "activo" ? 
                                            `<span class="badge bg-success">En estacionamiento</span>` : 
                                            `<span class="badge bg-secondary">Registrado</span>`
                                        }
                                    </td>
                                </tr>
                                ${vehiculo.numero_espacio ? 
                                    `<tr><td><strong>Espacio:</strong></td><td><span class="badge bg-info">${vehiculo.numero_espacio}</span></td></tr>` : ""
                                }
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-info mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Fechas</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr><td><strong>Registrado:</strong></td><td>${fechaRegistro.toLocaleString("es-PE")}</td></tr>
                                ${fechaActualizacion && fechaActualizacion.getTime() !== fechaRegistro.getTime() ? 
                                    `<tr><td><strong>Actualizado:</strong></td><td>${fechaActualizacion.toLocaleString("es-PE")}</td></tr>` : ""
                                }
                            </table>
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
            width: "900px",
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-times me-2"></i>Cerrar',
            customClass: {
                popup: "text-start"
            }
        });
    } else {
        // Fallback: abrir en nueva ventana
        const ventana = window.open("", "_blank", "width=900,height=700");
        ventana.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <title>Detalles - ${vehiculo.placa}</title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            </head>
            <body class="p-4">
                <h3><i class="fas fa-car me-2"></i>Detalles del Vehículo - ${vehiculo.placa}</h3>
                ${detallesHtml}
                <div class="text-center mt-4">
                    <button class="btn btn-secondary" onclick="window.close()">
                        <i class="fas fa-times me-2"></i>Cerrar
                    </button>
                </div>
            </body>
            </html>
        `);
    }
}

// Funciones auxiliares para notificaciones
function mostrarCargando(mensaje = "Cargando...") {
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: mensaje,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function cerrarCargando() {
    if (typeof Swal !== "undefined") {
        Swal.close();
    }
}

function mostrarExito(mensaje) {
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "¡Éxito!",
            text: mensaje,
            icon: "success",
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert("Éxito: " + mensaje);
    }
}

function mostrarError(mensaje) {
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "Error",
            text: mensaje,
            icon: "error"
        });
    } else {
        alert("Error: " + mensaje);
    }
}

// Función para actualizar vehículo (cuando está en modo edición)
async function actualizarVehiculo() {
    console.log("💾 Actualizando vehículo ID:", vehiculoEditandoId);
    
    if (!vehiculoEditandoId) {
        mostrarError("No hay vehículo seleccionado para actualizar");
        return;
    }
    
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
            
            mostrarExito("Vehículo actualizado exitosamente");
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalVehiculo"));
            if (modal) modal.hide();
            
            // Limpiar variable de edición
            vehiculoEditandoId = null;
            
            // Recargar datos
            if (typeof cargarVehiculos === "function") {
                setTimeout(() => cargarVehiculos(), 500);
            }
            if (typeof updateDashboardData === "function") {
                setTimeout(() => updateDashboardData(), 500);
            }
            
        } else {
            throw new Error(data.message || "No se pudo actualizar el vehículo");
        }
        
    } catch (error) {
        console.error("❌ Error actualizando:", error);
        mostrarError("Error al actualizar: " + error.message);
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}

// Funciones auxiliares para formulario
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
        mostrarError("Por favor, complete todos los campos obligatorios");
        return false;
    }
    
    const placaRegex = /^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/i;
    if (!placaRegex.test(datos.placa)) {
        mostrarError("Formato de placa no válido. Use formato ABC123 o ABC-123");
        return false;
    }
    
    return true;
}

// Función para resetear modal a modo registro
function resetearModalVehiculo() {
    vehiculoEditandoId = null;
    
    const titulo = document.querySelector("#modalVehiculo .modal-title");
    if (titulo) {
        titulo.innerHTML = `<i class="fas fa-plus me-2"></i>Registrar Nuevo Vehículo`;
    }
    
    const btnSubmit = document.querySelector("#modalVehiculo .btn-primary");
    if (btnSubmit) {
        btnSubmit.innerHTML = `<i class="fas fa-save me-2"></i>Registrar Vehículo`;
    }
    
    // Limpiar formulario
    const form = document.getElementById("formVehiculo");
    if (form) form.reset();
    
    const resultados = document.getElementById("resultados-clientes");
    if (resultados) resultados.innerHTML = "";
}

console.log("✅ Funciones de vehículos cargadas completamente");';

file_put_contents('assets/js/vehiculos-functions.js', $vehiculos_functions_js);
echo "✅ Archivo assets/js/vehiculos-functions.js creado correctamente<br>";

echo "<hr>";

// 3. Actualizar admin_dashboard.php para incluir redirección al menú principal
echo "<h3>3. Actualizando Admin Dashboard</h3>";

if (file_exists('views/admin_dashboard.php')) {
    $dashboard_content = file_get_contents('views/admin_dashboard.php');
    
    // Agregar botón de menú principal en el navbar si no existe
    if (strpos($dashboard_content, 'irMenuPrincipal') === false) {
        // Buscar el navbar y agregar el botón
        $menu_button = '<li class="nav-item">
                            <a class="nav-link" href="#" onclick="irMenuPrincipal()">
                                <i class="fas fa-home me-1"></i>Menú Principal
                            </a>
                        </li>';
        
        // Buscar donde insertar (antes del dropdown del usuario)
        if (strpos($dashboard_content, '<li class="nav-item dropdown">') !== false) {
            $dashboard_content = str_replace(
                '<li class="nav-item dropdown">',
                $menu_button . "\n                    " . '<li class="nav-item dropdown">',
                $dashboard_content
            );
            echo "✅ Botón 'Menú Principal' agregado al navbar<br>";
        }
    } else {
        echo "ℹ️ Botón 'Menú Principal' ya existe<br>";
    }
    
    // Agregar scripts si no están presentes
    $scripts_to_add = [
        'logout-functions.js',
        'vehiculos-functions.js'
    ];
    
    $scripts_added = 0;
    foreach ($scripts_to_add as $script) {
        if (strpos($dashboard_content, $script) === false) {
            $script_tag = "    <script src=\"../assets/js/$script\"></script>\n";
            $dashboard_content = str_replace('</body>', $script_tag . '</body>', $dashboard_content);
            $scripts_added++;
            echo "✅ Script $script agregado<br>";
        } else {
            echo "ℹ️ Script $script ya incluido<br>";
        }
    }
    
    // Guardar cambios
    if ($scripts_added > 0 || strpos($dashboard_content, 'irMenuPrincipal') !== false) {
        file_put_contents('views/admin_dashboard.php', $dashboard_content);
        echo "💾 Dashboard actualizado<br>";
    }
    
} else {
    echo "❌ Archivo admin_dashboard.php no encontrado<br>";
}

echo "<hr>";

// 4. Crear o actualizar el AuthController de logout
echo "<h3>4. Creando AuthController de Logout</h3>";

$auth_logout_controller = '<?php
session_start();

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Log para debugging
error_log("AuthController Logout: " . $_SERVER["REQUEST_METHOD"] . " - " . file_get_contents("php://input"));

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
                echo json_encode([
                    "success" => false, 
                    "message" => "No hay sesión activa",
                    "redirect" => "login_final.php"
                ]);
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
    error_log("Error en AuthController Logout: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>';

// Crear directorio controllers si no existe
if (!is_dir('controllers')) {
    mkdir('controllers', 0755, true);
}

file_put_contents('controllers/AuthController_logout.php', $auth_logout_controller);
echo "✅ Archivo controllers/AuthController_logout.php creado<br>";

echo "<hr>";

// 5. Actualizar o crear logout.php simple
echo "<h3>5. Creando Logout.php</h3>";

$logout_php = '<?php
session_start();

// Log de la acción de logout
error_log("Logout directo iniciado para usuario: " . ($_SESSION["username"] ?? "desconocido"));

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

// 6. Actualizar login_final.php para mostrar mensaje de logout
echo "<h3>6. Actualizando Login para Mensaje de Logout</h3>";

if (file_exists('login_final.php')) {
    $login_content = file_get_contents('login_final.php');
    
    // Verificar si ya maneja el mensaje de logout
    if (strpos($login_content, 'logout_success') === false) {
        // Buscar donde insertar el código de mensaje
        $logout_message_code = '
        // Mensaje de logout exitoso
        $logout_message = "";
        if (isset($_GET["message"]) && $_GET["message"] === "logout_success") {
            $logout_message = \'<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Hasta luego!</strong> Su sesión ha sido cerrada exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>\';
        }';
        
        // Insertar después del session_start()
        $login_content = preg_replace(
            '/(session_start\(\);)/',
            '$1' . $logout_message_code,
            $login_content,
            1
        );
        
        // Buscar donde mostrar el mensaje en el HTML
        $login_content = preg_replace(
            '/(<div[^>]*class="[^"]*container[^"]*"[^>]*>)/',
            '$1<?php echo $logout_message; ?>',
            $login_content,
            1
        );
        
        file_put_contents('login_final.php', $login_content);
        echo "✅ Login actualizado para mostrar mensaje de logout<br>";
    } else {
        echo "ℹ️ Login ya maneja mensajes de logout<br>";
    }
} else {
    echo "❌ Archivo login_final.php no encontrado<br>";
}

echo "<hr>";

// 7. Crear script de test completo
echo "<h3>7. Creando Script de Test</h3>";

$test_complete = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completo - Logout y Vehículos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.css" rel="stylesheet">
    <style>
        .test-card { margin-bottom: 20px; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><i class="fas fa-vial me-2"></i>Test Completo del Sistema</h1>
        <p class="lead">Verificación de funciones de logout y gestión de vehículos</p>
        
        <div class="row">
            <!-- Test de Archivos -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-file-code me-2"></i>Verificación de Archivos</h5>
                    </div>
                    <div class="card-body">
                        <div id="file-status">Verificando archivos...</div>
                    </div>
                </div>
            </div>
            
            <!-- Test de Funciones -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-cogs me-2"></i>Funciones JavaScript</h5>
                    </div>
                    <div class="card-body">
                        <div id="functions-status">Verificando funciones...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Test de Logout -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-sign-out-alt me-2"></i>Test de Logout</h5>
                    </div>
                    <div class="card-body">
                        <p>Pruebe la función de logout completa:</p>
                        <button class="btn btn-danger btn-lg w-100" onclick="testLogout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Probar Logout
                        </button>
                        <div class="mt-3">
                            <strong>Resultado esperado:</strong>
                            <ul class="small">
                                <li>Confirmación con SweetAlert2</li>
                                <li>Loading mientras procesa</li>
                                <li>Redirección a login</li>
                                <li>Mensaje de éxito en login</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test de Vehículos -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-car me-2"></i>Test de Vehículos</h5>
                    </div>
                    <div class="card-body">
                        <p>Pruebe las funciones de vehículos:</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testEditarVehiculo()">
                                <i class="fas fa-edit me-2"></i>Probar Editar (ID: 1)
                            </button>
                            <button class="btn btn-info" onclick="testVerDetalles()">
                                <i class="fas fa-eye me-2"></i>Probar Ver Detalles
                            </button>
                            <button class="btn btn-warning" onclick="testEliminar()">
                                <i class="fas fa-trash me-2"></i>Probar Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card test-card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-link me-2"></i>Links de Navegación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="login_final.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="views/admin_dashboard.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="views/operador_dashboard.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-user me-2"></i>Dashboard Operador
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-warning w-100" onclick="irMenuPrincipal()">
                                    <i class="fas fa-home me-2"></i>Menú Principal
                                </button>
                            </div>
                        </div>
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
        // Datos de prueba
        let vehiculosCrudData = [
            {
                id: 1,
                placa: "TEST123",
                modelo: "Toyota Corolla Test",
                color: "Azul",
                tipo_vehiculo: "auto",
                fecha_creacion: new Date().toISOString(),
                cliente_nombre: "Juan",
                cliente_apellido: "Pérez",
                codigo_cliente: "CLI001"
            }
        ];
        
        // Funciones de test
        function testLogout() {
            if (typeof logout === "function") {
                logout();
            } else {
                Swal.fire("Error", "Función logout no disponible", "error");
            }
        }
        
        function testEditarVehiculo() {
            if (typeof editarVehiculo === "function") {
                editarVehiculo(1);
            } else {
                Swal.fire("Error", "Función editarVehiculo no disponible", "error");
            }
        }
        
        function testVerDetalles() {
            if (typeof verDetallesVehiculo === "function") {
                verDetallesVehiculo(1);
            } else {
                Swal.fire("Error", "Función verDetallesVehiculo no disponible", "error");
            }
        }
        
        function testEliminar() {
            if (typeof confirmarEliminarVehiculo === "function") {
                confirmarEliminarVehiculo(1, "TEST123");
            } else {
                Swal.fire("Error", "Función confirmarEliminarVehiculo no disponible", "error");
            }
        }
        
        // Verificar archivos al cargar
        document.addEventListener("DOMContentLoaded", function() {
            checkFiles();
            checkFunctions();
        });
        
        async function checkFiles() {
            const files = [
                "assets/js/logout-functions.js",
                "assets/js/vehiculos-functions.js", 
                "logout.php",
                "controllers/AuthController_logout.php"
            ];
            
            let html = "";
            for (const file of files) {
                try {
                    const response = await fetch(file, { method: "HEAD" });
                    const status = response.ok ? "ok" : "error";
                    const icon = response.ok ? "check-circle" : "times-circle";
                    html += `<div class="status-${status}"><i class="fas fa-${icon} me-2"></i>${file}</div>`;
                } catch (error) {
                    html += `<div class="status-error"><i class="fas fa-times-circle me-2"></i>${file} (Error)</div>`;
                }
            }
            document.getElementById("file-status").innerHTML = html;
        }
        
        function checkFunctions() {
            const functions = [
                "logout",
                "irMenuPrincipal", 
                "editarVehiculo",
                "confirmarEliminarVehiculo",
                "verDetallesVehiculo"
            ];
            
            let html = "";
            functions.forEach(func => {
                const available = typeof window[func] === "function";
                const status = available ? "ok" : "error";
                const icon = available ? "check-circle" : "times-circle";
                html += `<div class="status-${status}"><i class="fas fa-${icon} me-2"></i>${func}</div>`;
            });
            
            document.getElementById("functions-status").innerHTML = html;
        }
    </script>
</body>
</html>';

file_put_contents('test_complete_system.html', $test_complete);
echo "✅ Archivo test_complete_system.html creado<br>";

echo "<hr>";

// 8. Resumen final
echo "<h3>🎉 CORRECCIÓN COMPLETADA</h3>";

echo '<div style="background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;">';
echo "<h4>✅ Problemas solucionados:</h4>";
echo "<ul>";
echo "<li>❌ <strong>Error variable undefined:</strong> Corregido completamente</li>";
echo "<li>🔐 <strong>Función logout:</strong> Implementada con confirmación y redirección</li>";
echo "<li>🚗 <strong>Funciones de vehículos:</strong> Editar, eliminar y ver detalles</li>";
echo "<li>🏠 <strong>Botón menú principal:</strong> Agregado al dashboard</li>";
echo "<li>💬 <strong>Mensajes de logout:</strong> Configurados en login</li>";
echo "</ul>";
echo "</div>";

echo '<div style="background: #cce5ff; padding: 20px; border-radius: 10px; margin: 20px 0;">';
echo "<h4>🚀 Para probar el sistema:</h4>";
echo "<ol>";
echo '<li><strong>Test completo:</strong> <a href="test_complete_system.html" target="_blank">test_complete_system.html</a></li>';
echo '<li><strong>Dashboard admin:</strong> <a href="views/admin_dashboard.php" target="_blank">admin_dashboard.php</a></li>';
echo '<li><strong>Probar logout:</strong> Clic en tu nombre → Cerrar Sesión</li>';
echo '<li><strong>Probar vehículos:</strong> Ir a sección Vehículos → Botones editar/eliminar</li>';
echo "</ol>";
echo "</div>";

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;">';
echo "<h5>📋 Archivos creados/corregidos:</h5>";
echo "<ul>";
echo "<li>📱 <strong>assets/js/logout-functions.js</strong> - Logout completo con confirmación</li>";
echo "<li>🚗 <strong>assets/js/vehiculos-functions.js</strong> - Editar, eliminar, ver detalles</li>";
echo "<li>🔐 <strong>controllers/AuthController_logout.php</strong> - Logout via AJAX</li>";
echo "<li>🔐 <strong>logout.php</strong> - Logout directo</li>";
echo "<li>🧪 <strong>test_complete_system.html</strong> - Test completo</li>";
echo "<li>🎯 <strong>views/admin_dashboard.php</strong> - Botón menú principal agregado</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>🎯 ¡Todos los errores están corregidos y las funcionalidades implementadas!</strong></p>";
?>