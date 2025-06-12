<?php
// Script para corregir el error de console.log

echo "<h2>🔧 CORRECCIÓN DEL ERROR CONSOLE.LOG</h2>";
echo "<hr>";

echo "<h3>📝 Problema Detectado:</h3>";
echo "<p>El error <code>console.log is not a function</code> indica que el JavaScript se está ejecutando en un contexto donde no existe el objeto console.</p>";

echo "<h3>🛠️ Soluciones:</h3>";

// 1. Crear versión más robusta del JavaScript
echo "<h4>1. Actualizando dashboard_fixed.js con protección console</h4>";

$js_content_fixed = '// Dashboard JavaScript - Versión con protección console
// Protección para console
if (typeof console === "undefined") {
    window.console = {
        log: function() {},
        error: function() {},
        warn: function() {},
        info: function() {}
    };
}

console.log("🚀 Dashboard Fixed JS cargado - Versión robusta");

// Variables globales
let csrfToken = null;
let vehiculosData = [];

// Detectar ruta base
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes("/views/")) {
        return "../";
    }
    return "./";
}

// Utilidad segura para logging
function safeLog(message, type = "log") {
    try {
        if (typeof console !== "undefined" && console[type]) {
            console[type](message);
        }
    } catch (e) {
        // Silencioso si console no está disponible
    }
}

// Inicialización
document.addEventListener("DOMContentLoaded", function() {
    safeLog("🎯 Inicializando dashboard...");
    initializeDashboard();
});

async function initializeDashboard() {
    await getCSRFToken();
    showSection("dashboard");
    safeLog("✅ Dashboard inicializado");
}

// Obtener CSRF Token
async function getCSRFToken() {
    try {
        const basePath = getBasePath();
        const response = await fetch(basePath + "api/csrf_token_fixed.php");
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                csrfToken = data.token;
                safeLog("✅ CSRF Token obtenido");
                return;
            }
        }
    } catch (error) {
        safeLog("⚠️ Error obteniendo CSRF token: " + error.message, "warn");
    }
    
    csrfToken = "fallback-token-" + Date.now();
    safeLog("⚠️ Usando token de fallback", "warn");
}

// Función principal de peticiones
async function makeRequest(action, data = {}) {
    safeLog(`🌐 Petición: ${action}`);
    
    if (!csrfToken) {
        await getCSRFToken();
    }
    
    const requestData = {
        accion: action,
        csrf_token: csrfToken,
        ...data
    };
    
    const basePath = getBasePath();
    const url = basePath + "controllers/VehiculoController.php";
    
    safeLog("📡 URL: " + url);
    
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(requestData)
        });
        
        safeLog("📥 Response status: " + response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const text = await response.text();
        safeLog("📄 Response recibido (" + text.length + " chars)");
        
        // Limpiar respuesta de posibles caracteres extraños
        const cleanText = text.trim();
        
        try {
            const result = JSON.parse(cleanText);
            safeLog("✅ JSON parseado correctamente");
            return result;
        } catch (parseError) {
            safeLog("❌ Error parsing JSON: " + parseError.message, "error");
            safeLog("📄 Raw response: " + cleanText.substring(0, 500), "error");
            throw new Error("Error parsing JSON: " + parseError.message);
        }
        
    } catch (error) {
        safeLog("❌ Error en petición: " + error.message, "error");
        throw error;
    }
}

// Mostrar notificaciones
function showNotification(message, type = "info") {
    safeLog(`🔔 ${type}: ${message}`);
    
    // Primero intentar SweetAlert2
    if (typeof Swal !== "undefined") {
        try {
            const icon = type === "error" ? "error" : type === "success" ? "success" : "info";
            Swal.fire({
                icon: icon,
                title: message,
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            return;
        } catch (e) {
            // Fallback si Swal falla
        }
    }
    
    // Fallback: crear notificación simple
    try {
        const notification = document.createElement("div");
        notification.className = `alert alert-${type === "error" ? "danger" : type === "success" ? "success" : "info"} alert-dismissible fade show`;
        notification.style.position = "fixed";
        notification.style.top = "20px";
        notification.style.right = "20px";
        notification.style.zIndex = "9999";
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
        
    } catch (e) {
        // Último fallback: alert nativo
        alert(`${type.toUpperCase()}: ${message}`);
    }
}

// Navegación entre secciones
function showSection(sectionName) {
    safeLog(`📄 Mostrando sección: ${sectionName}`);
    
    try {
        // Ocultar todas las secciones
        document.querySelectorAll(".content-section").forEach(section => {
            section.style.display = "none";
        });
        
        // Mostrar la sección seleccionada
        const targetSection = document.getElementById(sectionName + "-section");
        if (targetSection) {
            targetSection.style.display = "block";
        }
        
        // Actualizar navegación
        document.querySelectorAll(".sidebar .nav-link").forEach(link => {
            link.classList.remove("active");
        });
        
        const activeLink = document.querySelector(`[onclick="showSection(\'${sectionName}\')"]`);
        if (activeLink) {
            activeLink.classList.add("active");
        }
        
        // Cargar datos según sección
        switch (sectionName) {
            case "dashboard":
                updateDashboardData();
                break;
            case "vehiculos":
                loadVehiculos();
                break;
            case "tiempo-real":
                updateTiempoReal();
                break;
        }
    } catch (error) {
        safeLog("❌ Error en showSection: " + error.message, "error");
    }
}

// Actualizar dashboard
async function updateDashboardData() {
    safeLog("📊 Actualizando dashboard...");
    
    try {
        const data = await makeRequest("listar_vehiculos", { en_estacionamiento: true });
        if (data.success) {
            const ocupados = data.vehiculos.length;
            updateElement("espacios-ocupados", ocupados);
            updateElement("espacios-disponibles", 50 - ocupados);
            updateElement("total-vehiculos", ocupados);
            updateElement("ingresos-hoy", "S/ " + (ocupados * 15).toFixed(2));
            
            updateActividadReciente(data.vehiculos);
            safeLog("✅ Dashboard actualizado correctamente");
        } else {
            throw new Error(data.message || "Error desconocido");
        }
    } catch (error) {
        safeLog("❌ Error actualizando dashboard: " + error.message, "error");
        updateDashboardFallback();
    }
}

function updateDashboardFallback() {
    safeLog("🔄 Usando datos de fallback...", "warn");
    updateElement("espacios-ocupados", "15");
    updateElement("espacios-disponibles", "35");
    updateElement("total-vehiculos", "15");
    updateElement("ingresos-hoy", "S/ 225.00");
    
    // Actividad de ejemplo
    const tbody = document.querySelector("#tabla-actividad tbody");
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td><strong>ABC123</strong></td>
                <td><span class="badge bg-primary">Entrada</span></td>
                <td>10:30 AM</td>
                <td><span class="badge bg-success">Activo</span></td>
            </tr>
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    Datos de ejemplo - Error de conexión con servidor
                </td>
            </tr>
        `;
    }
}

// Cargar vehículos
async function loadVehiculos() {
    safeLog("🚗 Cargando vehículos...");
    
    try {
        const data = await makeRequest("listar_vehiculos");
        if (data.success) {
            vehiculosData = data.vehiculos;
            updateVehiculosTable();
            showNotification("Vehículos cargados correctamente", "success");
        } else {
            throw new Error(data.message || "Error al cargar vehículos");
        }
    } catch (error) {
        safeLog("❌ Error cargando vehículos: " + error.message, "error");
        showNotification("Error de conexión al cargar vehículos", "error");
        updateVehiculosTableError();
    }
}

function updateVehiculosTable() {
    const tbody = document.querySelector("#tabla-vehiculos tbody");
    if (!tbody) return;
    
    tbody.innerHTML = "";
    
    if (vehiculosData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay vehículos registrados
                    <br><small>Haga clic en "Registrar Vehículo" para agregar uno</small>
                </td>
            </tr>
        `;
        return;
    }
    
    vehiculosData.forEach(vehiculo => {
        const row = tbody.insertRow();
        
        let tiempoTranscurrido = "N/A";
        let estado = "Registrado";
        let tarifa = "S/ 0.00";
        
        if (vehiculo.fecha_entrada) {
            const entrada = new Date(vehiculo.fecha_entrada);
            const ahora = new Date();
            const diff = ahora - entrada;
            const horas = Math.floor(diff / (1000 * 60 * 60));
            const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            tiempoTranscurrido = `${horas}h ${minutos}m`;
            estado = "En estacionamiento";
            
            const horasTotal = Math.max(1, Math.ceil(diff / (1000 * 60 * 60)));
            const tarifaPorHora = parseFloat(vehiculo.tarifa_aplicada) || 3.00;
            tarifa = `S/ ${(horasTotal * tarifaPorHora).toFixed(2)}`;
        }
        
        row.innerHTML = `
            <td>
                <strong>${vehiculo.placa}</strong>
                <br><small class="text-muted">${vehiculo.modelo || "N/A"}</small>
            </td>
            <td>
                <span class="badge bg-info">${vehiculo.tipo_vehiculo || "auto"}</span>
                <br><small class="text-muted">${vehiculo.color || "N/A"}</small>
            </td>
            <td>${vehiculo.fecha_entrada ? new Date(vehiculo.fecha_entrada).toLocaleString("es-PE") : "N/A"}</td>
            <td>${tiempoTranscurrido}</td>
            <td>${tarifa}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${vehiculo.estado_registro === "activo" ? 
                        `<button class="btn btn-success" onclick="procesarSalida(${vehiculo.registro_id})" title="Procesar Salida">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>` : 
                        `<button class="btn btn-primary" onclick="registrarEntradaVehiculo(${vehiculo.id})" title="Registrar Entrada">
                            <i class="fas fa-sign-in-alt"></i>
                        </button>`
                    }
                    <button class="btn btn-outline-secondary" onclick="editarVehiculo(${vehiculo.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </td>
        `;
    });
}

function updateVehiculosTableError() {
    const tbody = document.querySelector("#tabla-vehiculos tbody");
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error de conexión con el servidor
                    <br>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadVehiculos()">
                        <i class="fas fa-sync-alt me-1"></i>Reintentar
                    </button>
                </td>
            </tr>
        `;
    }
}

function updateActividadReciente(vehiculos) {
    const tbody = document.querySelector("#tabla-actividad tbody");
    if (!tbody) return;
    
    tbody.innerHTML = "";
    
    if (vehiculos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay actividad reciente
                </td>
            </tr>
        `;
        return;
    }
    
    // Mostrar últimos 5 vehículos
    vehiculos.slice(0, 5).forEach(vehiculo => {
        const row = tbody.insertRow();
        const hora = vehiculo.fecha_entrada ? 
            new Date(vehiculo.fecha_entrada).toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" }) : 
            "N/A";
        
        row.innerHTML = `
            <td><strong>${vehiculo.placa}</strong></td>
            <td><span class="badge bg-primary">Entrada</span></td>
            <td>${hora}</td>
            <td><span class="badge bg-success">Activo</span></td>
        `;
    });
}

// Funciones auxiliares
function updateElement(id, value) {
    try {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    } catch (error) {
        safeLog("❌ Error actualizando elemento " + id + ": " + error.message, "error");
    }
}

// Tiempo real
async function updateTiempoReal() {
    safeLog("⏱️ Actualizando tiempo real...");
    
    try {
        const data = await makeRequest("listar_vehiculos", { en_estacionamiento: true });
        if (data.success) {
            const totalEspacios = 50;
            const ocupados = data.vehiculos.length;
            const disponibles = totalEspacios - ocupados;
            const porcentaje = Math.round((ocupados / totalEspacios) * 100);
            
            updateElement("total-espacios", totalEspacios);
            updateElement("espacios-libres", disponibles);
            updateElement("espacios-ocupados-rt", ocupados);
            updateElement("porcentaje-ocupacion", porcentaje + "%");
            
            generateParkingMap(ocupados);
            safeLog("✅ Tiempo real actualizado");
        }
    } catch (error) {
        safeLog("❌ Error tiempo real: " + error.message, "error");
        // Datos de fallback
        updateElement("total-espacios", "50");
        updateElement("espacios-libres", "35");
        updateElement("espacios-ocupados-rt", "15");
        updateElement("porcentaje-ocupacion", "30%");
        generateParkingMap(15);
    }
}

function generateParkingMap(ocupados = 15) {
    const mapaEspacios = document.getElementById("mapa-espacios");
    if (!mapaEspacios) return;
    
    try {
        mapaEspacios.innerHTML = "";
        
        for (let i = 1; i <= 50; i++) {
            const espacio = document.createElement("div");
            espacio.className = "parking-space available";
            espacio.textContent = i.toString().padStart(2, "0");
            espacio.onclick = () => toggleParkingSpace(i);
            mapaEspacios.appendChild(espacio);
        }
        
        // Marcar espacios ocupados aleatoriamente
        const espacios = mapaEspacios.children;
        const ocupadosIndexes = [];
        
        while (ocupadosIndexes.length < Math.min(ocupados, 50)) {
            const randomIndex = Math.floor(Math.random() * 50);
            if (!ocupadosIndexes.includes(randomIndex)) {
                ocupadosIndexes.push(randomIndex);
                espacios[randomIndex].className = "parking-space occupied";
            }
        }
        
        safeLog("🗺️ Mapa de estacionamiento generado");
    } catch (error) {
        safeLog("❌ Error generando mapa: " + error.message, "error");
    }
}

function toggleParkingSpace(spaceNumber) {
    const espacio = document.querySelector(`.parking-space:nth-child(${spaceNumber})`);
    if (!espacio) return;
    
    if (espacio.classList.contains("available")) {
        espacio.className = "parking-space occupied";
    } else if (espacio.classList.contains("occupied")) {
        espacio.className = "parking-space available";
    }
}

// Modales
function mostrarModalVehiculo() {
    try {
        const modal = document.getElementById("modalVehiculo");
        if (modal) {
            const form = document.getElementById("formVehiculo");
            if (form) form.reset();
            
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    } catch (error) {
        safeLog("❌ Error mostrando modal vehículo: " + error.message, "error");
        alert("Error al abrir el formulario de vehículo");
    }
}

function mostrarModalCliente() {
    try {
        const modal = document.getElementById("modalCliente");
        if (modal) {
            const form = document.getElementById("formCliente");
            if (form) form.reset();
            
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    } catch (error) {
        safeLog("❌ Error mostrando modal cliente: " + error.message, "error");
        alert("Error al abrir el formulario de cliente");
    }
}

// Registrar vehículo
async function registrarVehiculo() {
    safeLog("📝 Registrando vehículo...");
    
    try {
        const formData = {
            placa: document.getElementById("placa")?.value?.trim() || "",
            modelo: document.getElementById("modelo")?.value?.trim() || "",
            color: document.getElementById("color")?.value?.trim() || "",
            tipo_vehiculo: document.getElementById("tipo-vehiculo")?.value || "",
            codigo_cliente: document.getElementById("codigo-cliente")?.value?.trim() || ""
        };
        
        // Validaciones
        if (!formData.placa || !formData.modelo || !formData.color || !formData.tipo_vehiculo) {
            showNotification("Complete todos los campos obligatorios", "error");
            return;
        }
        
        // Validar formato de placa
        const placaRegex = /^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/i;
        if (!placaRegex.test(formData.placa)) {
            showNotification("Formato de placa no válido. Use formato ABC123 o ABC-123", "error");
            return;
        }
        
        const submitBtn = document.querySelector("#modalVehiculo .btn-primary");
        const originalText = submitBtn?.innerHTML || "Registrar";
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Registrando...`;
        }
        
        const data = await makeRequest("registrar_vehiculo", formData);
        
        if (data.success) {
            showNotification("Vehículo registrado exitosamente", "success");
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalVehiculo"));
            if (modal) modal.hide();
            
            // Recargar vehículos
            await loadVehiculos();
            await updateDashboardData();
            
        } else {
            showNotification("Error: " + data.message, "error");
        }
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
    } catch (error) {
        safeLog("❌ Error registrando vehículo: " + error.message, "error");
        showNotification("Error de conexión", "error");
        
        const submitBtn = document.querySelector("#modalVehiculo .btn-primary");
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = "Registrar";
        }
    }
}

// Funciones placeholder
function registrarEntradaVehiculo(vehiculoId) {
    safeLog("🚪 Entrada para vehículo: " + vehiculoId);
    showNotification("Funcionalidad de entrada en desarrollo", "info");
}

function procesarSalida(registroId) {
    safeLog("🚪 Salida para registro: " + registroId);
    if (confirm("¿Procesar salida de este vehículo?")) {
        showNotification("Funcionalidad de salida en desarrollo", "info");
    }
}

function editarVehiculo(vehiculoId) {
    safeLog("✏️ Editar vehículo: " + vehiculoId);
    showNotification("Funcionalidad de edición en desarrollo", "info");
}

function crearCliente() {
    safeLog("👤 Creando cliente...");
    showNotification("Funcionalidad de crear cliente en desarrollo", "info");
}

function actualizarTiempoReal() {
    updateTiempoReal();
    showNotification("Datos actualizados", "info");
}

function logout() {
    if (confirm("¿Cerrar sesión?")) {
        window.location.href = getBasePath() + "login_final.php";
    }
}

// Auto-actualización cada 60 segundos (menos frecuente para evitar errores)
setInterval(() => {
    try {
        updateDashboardData();
    } catch (error) {
        safeLog("❌ Error en auto-actualización: " + error.message, "error");
    }
}, 60000);

safeLog("✅ Dashboard Fixed JS completamente cargado - Versión robusta");

// Test inicial
setTimeout(() => {
    safeLog("🧪 Ejecutando test inicial...");
    updateDashboardData();
}, 1000);';

// Guardar el archivo JavaScript corregido
if (!is_dir('assets/js')) {
    mkdir('assets/js', 0755, true);
}

file_put_contents('assets/js/dashboard_fixed.js', $js_content_fixed);
echo "✅ dashboard_fixed.js actualizado con protección console<br>";

echo "<hr>";

// 2. Verificar que los dashboards usan la versión correcta
echo "<h4>2. Verificando referencias en dashboards</h4>";

$dashboards = ['views/admin_dashboard.php', 'views/operador_dashboard.php'];

foreach ($dashboards as $dashboard_file) {
    if (file_exists($dashboard_file)) {
        $content = file_get_contents($dashboard_file);
        
        if (strpos($content, 'dashboard_fixed.js') !== false) {
            echo "✅ $dashboard_file - Ya usa dashboard_fixed.js<br>";
        } else {
            echo "🔧 $dashboard_file - Actualizando referencia...<br>";
            
            // Reemplazar referencia
            $content = str_replace(
                'dashboard.js',
                'dashboard_fixed.js',
                $content
            );
            
            file_put_contents($dashboard_file, $content);
            echo "✅ $dashboard_file - Referencia actualizada<br>";
        }
    } else {
        echo "❌ $dashboard_file - No encontrado<br>";
    }
}

echo "<hr>";

// 3. Crear test más específico
echo "<h4>3. Test Específico Corregido</h4>";

$test_content = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard Corregido</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Test Dashboard Corregido</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Tests Automáticos</h3>
                <div id="test-results"></div>
                
                <h3 class="mt-4">Tests Manuales</h3>
                <button class="btn btn-primary me-2" onclick="testCSRF()">Test CSRF</button>
                <button class="btn btn-success me-2" onclick="testVehiculos()">Test Vehículos</button>
                <button class="btn btn-info me-2" onclick="testDashboard()">Test Dashboard</button>
            </div>
            
            <div class="col-md-6">
                <h3>Console Log</h3>
                <div id="console-output" style="background: #f8f9fa; padding: 15px; height: 300px; overflow-y: auto; font-family: monospace; border: 1px solid #ddd; border-radius: 5px;">
                    <div>🚀 Test iniciado...</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard_fixed.js"></script>
    
    <script>
        // Override console para capturar logs
        const originalConsole = window.console;
        const consoleOutput = document.getElementById("console-output");
        
        function addToConsole(message, type = "log") {
            const timestamp = new Date().toLocaleTimeString();
            const div = document.createElement("div");
            div.innerHTML = `[${timestamp}] ${type.toUpperCase()}: ${message}`;
            div.style.color = type === "error" ? "#dc3545" : type === "warn" ? "#ffc107" : "#000";
            consoleOutput.appendChild(div);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
            
            // También enviar al console real
            if (originalConsole && originalConsole[type]) {
                originalConsole[type](message);
            }
        }
        
        // Reemplazar console methods
        window.console = {
            log: (msg) => addToConsole(msg, "log"),
            error: (msg) => addToConsole(msg, "error"),
            warn: (msg) => addToConsole(msg, "warn"),
            info: (msg) => addToConsole(msg, "info")
        };
        
        function addResult(message, type) {
            const results = document.getElementById("test-results");
            const div = document.createElement("div");
            div.className = `test-result ${type}`;
            div.innerHTML = message;
            results.appendChild(div);
        }
        
        // Tests automáticos
        document.addEventListener("DOMContentLoaded", function() {
            console.log("🎯 Iniciando tests automáticos...");
            
            // Test 1: Console protection
            try {
                console.log("Test console.log");
                addResult("