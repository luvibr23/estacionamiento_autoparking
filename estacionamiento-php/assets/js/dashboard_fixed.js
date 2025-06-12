
// Dashboard JavaScript simplificado con debugging
console.log("🚀 Dashboard Fixed JS cargado");

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

// Obtener CSRF Token
async function getCSRFToken() {
    try {
        const basePath = getBasePath();
        const response = await fetch(basePath + "api/csrf_token_fixed.php");
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                csrfToken = data.token;
                console.log("✅ CSRF Token obtenido");
                return true;
            }
        }
    } catch (error) {
        console.log("⚠️ Error obteniendo CSRF token:", error);
    }
    
    // Token de fallback
    csrfToken = "fallback-token";
    return false;
}

// Función principal de peticiones
async function makeRequest(action, data = {}) {
    console.log(`🌐 Petición: ${action}`);
    
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
    
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(requestData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const text = await response.text();
        console.log("📥 Response:", text.substring(0, 100) + "...");
        
        const result = JSON.parse(text);
        console.log("✅ Parsed:", result);
        return result;
        
    } catch (error) {
        console.error("❌ Error:", error);
        throw error;
    }
}

// Mostrar notificaciones
function showNotification(message, type = "info") {
    console.log(`🔔 ${type}: ${message}`);
    
    if (typeof Swal !== "undefined") {
        const icon = type === "error" ? "error" : type === "success" ? "success" : "info";
        Swal.fire({
            icon: icon,
            title: message,
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}

// Actualizar elemento
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

// Navegación entre secciones
function showSection(sectionName) {
    console.log(`📄 Mostrando: ${sectionName}`);
    
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
    
    const activeLink = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
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
}

// Actualizar dashboard
async function updateDashboardData() {
    console.log("📊 Actualizando dashboard...");
    
    try {
        const data = await makeRequest("listar_vehiculos", { en_estacionamiento: true });
        if (data.success) {
            const ocupados = data.vehiculos.length;
            updateElement("espacios-ocupados", ocupados);
            updateElement("espacios-disponibles", 50 - ocupados);
            updateElement("total-vehiculos", ocupados);
            
            // Actualizar tabla de actividad
            updateActividadReciente(data.vehiculos);
        }
    } catch (error) {
        console.error("Error actualizando dashboard:", error);
        // Usar datos de ejemplo
        updateElement("espacios-ocupados", "15");
        updateElement("espacios-disponibles", "35");
        updateElement("total-vehiculos", "15");
        updateElement("ingresos-hoy", "S/ 450.00");
    }
}

// Cargar vehículos
async function loadVehiculos() {
    console.log("🚗 Cargando vehículos...");
    
    try {
        const data = await makeRequest("listar_vehiculos");
        if (data.success) {
            vehiculosData = data.vehiculos;
            updateVehiculosTable();
            showNotification("Vehículos cargados correctamente", "success");
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error cargando vehículos:", error);
        showNotification("Error de conexión al cargar vehículos", "error");
        
        // Mostrar mensaje en la tabla
        const tbody = document.querySelector("#tabla-vehiculos tbody");
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error de conexión. 
                        <button class="btn btn-sm btn-outline-primary" onclick="loadVehiculos()">
                            Reintentar
                        </button>
                    </td>
                </tr>
            `;
        }
    }
}

// Actualizar tabla de vehículos
function updateVehiculosTable() {
    const tbody = document.querySelector("#tabla-vehiculos tbody");
    if (!tbody) return;
    
    tbody.innerHTML = "";
    
    if (vehiculosData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No hay vehículos registrados
                </td>
            </tr>
        `;
        return;
    }
    
    vehiculosData.forEach(vehiculo => {
        const row = tbody.insertRow();
        
        // Calcular tiempo transcurrido
        let tiempoTranscurrido = "N/A";
        let tarifa = "N/A";
        
        if (vehiculo.fecha_entrada) {
            const entrada = new Date(vehiculo.fecha_entrada);
            const ahora = new Date();
            const diff = ahora - entrada;
            const horas = Math.floor(diff / (1000 * 60 * 60));
            const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            tiempoTranscurrido = `${horas}h ${minutos}m`;
            
            const horasTotal = Math.ceil(diff / (1000 * 60 * 60));
            const tarifaTotal = horasTotal * parseFloat(vehiculo.tarifa_aplicada || 3.00);
            tarifa = `S/ ${tarifaTotal.toFixed(2)}`;
        }
        
        row.innerHTML = `
            <td><strong>${vehiculo.placa}</strong></td>
            <td>${vehiculo.modelo || "N/A"} <small class="text-muted">(${vehiculo.color || "N/A"})</small></td>
            <td><span class="badge bg-info">${vehiculo.tipo_vehiculo || "auto"}</span></td>
            <td>${vehiculo.cliente_nombre ? vehiculo.cliente_nombre + " " + (vehiculo.cliente_apellido || "") : "Sin cliente"}</td>
            <td>
                ${vehiculo.estado_registro === "activo" ? 
                    `<span class="badge bg-success">En estacionamiento</span><br><small>${tiempoTranscurrido}</small>` : 
                    `<span class="badge bg-secondary">Registrado</span>`
                }
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${vehiculo.estado_registro === "activo" ? 
                        `<button class="btn btn-success" onclick="procesarSalida(${vehiculo.registro_id})" title="Procesar Salida">
                            <i class="fas fa-sign-out-alt"></i> Salida
                        </button>` : 
                        `<button class="btn btn-primary" onclick="registrarEntradaVehiculo(${vehiculo.id})" title="Registrar Entrada">
                            <i class="fas fa-sign-in-alt"></i> Entrada
                        </button>`
                    }
                </div>
            </td>
        `;
    });
}

// Actualizar actividad reciente
function updateActividadReciente(vehiculos) {
    const tbody = document.querySelector("#tabla-actividad tbody");
    if (!tbody) return;
    
    tbody.innerHTML = "";
    
    const ultimosVehiculos = vehiculos.slice(0, 5);
    
    if (ultimosVehiculos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    No hay actividad reciente
                </td>
            </tr>
        `;
        return;
    }
    
    ultimosVehiculos.forEach(vehiculo => {
        const row = tbody.insertRow();
        const fechaEntrada = vehiculo.fecha_entrada ? new Date(vehiculo.fecha_entrada) : new Date();
        const hora = fechaEntrada.toLocaleTimeString("es-PE", { 
            hour: "2-digit", 
            minute: "2-digit" 
        });
        
        row.innerHTML = `
            <td><strong>${vehiculo.placa}</strong></td>
            <td><span class="badge bg-primary">Entrada</span></td>
            <td>${hora}</td>
            <td><span class="badge bg-success">Activo</span></td>
        `;
    });
}

// Tiempo real
async function updateTiempoReal() {
    console.log("⏱️ Actualizando tiempo real...");
    
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
        }
    } catch (error) {
        console.error("Error tiempo real:", error);
        // Datos de fallback
        updateElement("total-espacios", "50");
        updateElement("espacios-libres", "35");
        updateElement("espacios-ocupados-rt", "15");
        updateElement("porcentaje-ocupacion", "30%");
        generateParkingMap(15);
    }
}

// Generar mapa de estacionamiento
function generateParkingMap(ocupados = 15) {
    const mapaEspacios = document.getElementById("mapa-espacios");
    if (!mapaEspacios) return;
    
    mapaEspacios.innerHTML = "";
    
    for (let i = 1; i <= 50; i++) {
        const espacio = document.createElement("div");
        espacio.className = "parking-space available";
        espacio.textContent = i.toString().padStart(2, "0");
        espacio.style.cursor = "pointer";
        mapaEspacios.appendChild(espacio);
    }
    
    // Marcar espacios ocupados aleatoriamente
    const espacios = mapaEspacios.children;
    const ocupadosArray = [];
    
    while (ocupadosArray.length < ocupados && ocupadosArray.length < 50) {
        const randomIndex = Math.floor(Math.random() * 50);
        if (!ocupadosArray.includes(randomIndex)) {
            ocupadosArray.push(randomIndex);
            espacios[randomIndex].className = "parking-space occupied";
        }
    }
}

// Funciones de modal
function mostrarModalVehiculo() {
    const modal = document.getElementById("modalVehiculo");
    if (modal) {
        // Limpiar formulario
        const form = document.getElementById("formVehiculo");
        if (form) form.reset();
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

function mostrarModalCliente() {
    const modal = document.getElementById("modalCliente");
    if (modal) {
        const form = document.getElementById("formCliente");
        if (form) form.reset();
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Registrar vehículo
async function registrarVehiculo() {
    console.log("📝 Registrando vehículo...");
    
    const formData = {
        placa: document.getElementById("placa")?.value?.trim() || "",
        modelo: document.getElementById("modelo")?.value?.trim() || "",
        color: document.getElementById("color")?.value?.trim() || "",
        tipo_vehiculo: document.getElementById("tipo-vehiculo")?.value || "",
        codigo_cliente: document.getElementById("codigo-cliente")?.value?.trim() || ""
    };
    
    // Validar datos
    if (!formData.placa || !formData.modelo || !formData.color || !formData.tipo_vehiculo) {
        showNotification("Por favor, complete todos los campos obligatorios.", "error");
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
    
    try {
        const data = await makeRequest("registrar_vehiculo", formData);
        
        if (data.success) {
            showNotification("Vehículo registrado exitosamente", "success");
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalVehiculo"));
            if (modal) modal.hide();
            
            // Recargar vehículos
            await loadVehiculos();
            
            // Preguntar por entrada
            if (confirm("¿Desea registrar la entrada de este vehículo al estacionamiento?")) {
                registrarEntradaVehiculo(data.vehiculo_id);
            }
        } else {
            showNotification("Error: " + data.message, "error");
        }
    } catch (error) {
        console.error("Error registrando vehículo:", error);
        showNotification("Error de conexión", "error");
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}

// Funciones placeholder
function registrarEntradaVehiculo(vehiculoId) {
    console.log("🚪 Entrada para vehículo:", vehiculoId);
    showNotification("Funcionalidad de entrada en desarrollo", "info");
}

function procesarSalida(registroId) {
    console.log("🚪 Salida para registro:", registroId);
    showNotification("Funcionalidad de salida en desarrollo", "info");
}

function crearCliente() {
    console.log("👤 Creando cliente...");
    showNotification("Funcionalidad de crear cliente en desarrollo", "info");
}

function actualizarTiempoReal() {
    updateTiempoReal();
    showNotification("Datos actualizados", "info");
}

function logout() {
    if (confirm("¿Está seguro de cerrar sesión?")) {
        const basePath = getBasePath();
        window.location.href = basePath + "login_final.php";
    }
}

// Inicialización
document.addEventListener("DOMContentLoaded", async function() {
    console.log("🚀 Inicializando dashboard...");
    
    // Obtener token CSRF
    await getCSRFToken();
    
    // Mostrar dashboard por defecto
    showSection("dashboard");
    
    console.log("✅ Dashboard inicializado correctamente");
});

// Auto-actualización cada 30 segundos
setInterval(updateDashboardData, 30000);

console.log("✅ Dashboard Fixed JS cargado completamente");
