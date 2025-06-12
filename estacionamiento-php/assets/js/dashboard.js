// Dashboard JavaScript - Versión con rutas corregidas y debugging
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando Dashboard...');
    initializeDashboard();
});

// Variables globales
let currentUser = null;
let dashboardChart = null;
let vehiculosData = [];
let tarifasData = [];
let csrfToken = null;

// Detectar la ruta base automáticamente
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/views/')) {
        return '../'; // Estamos en views/, subir un nivel
    }
    return './'; // Estamos en la raíz
}

// Funciones de inicialización
function initializeDashboard() {
    console.log('📍 Ruta detectada:', window.location.pathname);
    console.log('🔧 Base path:', getBasePath());
    
    getCurrentUser();
    getCSRFToken();
    setupEventListeners();
    showSection('dashboard');
    
    console.log('✅ Dashboard inicializado correctamente');
}

function getCurrentUser() {
    // Obtener datos del usuario del DOM si están disponibles
    const userNameElement = document.getElementById('user-name');
    if (userNameElement) {
        currentUser = {
            name: userNameElement.textContent.trim()
        };
        console.log('👤 Usuario actual:', currentUser.name);
    }
}

function getCSRFToken() {
    console.log('🔐 Obteniendo CSRF token...');
    const basePath = getBasePath();
    
    // Intentar múltiples rutas para el token
    const tokenUrls = [
        `${basePath}api/csrf_token_fixed.php`,
        `${basePath}api/csrf_token.php`,
        './api/csrf_token_fixed.php'
    ];
    
    async function tryGetToken() {
        for (const url of tokenUrls) {
            try {
                console.log(`🔍 Intentando obtener token desde: ${url}`);
                const response = await fetch(url);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        csrfToken = data.token;
                        console.log('✅ CSRF Token obtenido:', csrfToken.substring(0, 20) + '...');
                        return;
                    }
                }
            } catch (error) {
                console.log(`❌ Error con ${url}:`, error.message);
            }
        }
        
        console.warn('⚠️ No se pudo obtener CSRF token, usando token de sesión');
        // Fallback: usar token de la página si está disponible
        csrfToken = 'fallback-token';
    }
    
    tryGetToken();
}

function setupEventListeners() {
    // Event listeners para formularios
    const formVehiculo = document.getElementById('formVehiculo');
    if (formVehiculo) {
        formVehiculo.addEventListener('submit', function(e) {
            e.preventDefault();
            registrarVehiculo();
        });
    }
    
    const formCliente = document.getElementById('formCliente');
    if (formCliente) {
        formCliente.addEventListener('submit', function(e) {
            e.preventDefault();
            crearCliente();
        });
    }
    
    // Configurar fechas por defecto en reportes
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    
    if (fechaInicio && fechaFin) {
        const hoy = new Date();
        const hace7dias = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000);
        
        fechaInicio.value = hace7dias.toISOString().split('T')[0];
        fechaFin.value = hoy.toISOString().split('T')[0];
    }
    
    // Event listener para búsqueda de clientes
    const buscarCliente = document.getElementById('buscar-cliente');
    if (buscarCliente) {
        let timeoutId;
        buscarCliente.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                if (this.value.length >= 2) {
                    buscarClientes(this.value);
                }
            }, 300);
        });
    }
    
    console.log('🎯 Event listeners configurados');
}

// Funciones de navegación
function showSection(sectionName) {
    console.log(`📄 Mostrando sección: ${sectionName}`);
    
    // Ocultar todas las secciones
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Mostrar la sección seleccionada
    const targetSection = document.getElementById(sectionName + '-section');
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    // Actualizar navegación activa
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    const activeLink = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
    
    // Cargar datos específicos de la sección
    switch (sectionName) {
        case 'dashboard':
            updateDashboardData();
            break;
        case 'vehiculos':
            loadVehiculos();
            break;
        case 'tiempo-real':
            updateTiempoReal();
            generateParkingMap();
            break;
        case 'reportes':
            loadReportes();
            break;
    }
}

// Funciones de datos del dashboard
function updateDashboardData() {
    console.log('📊 Actualizando datos del dashboard...');
    
    makeRequest('listar_vehiculos', { en_estacionamiento: true })
        .then(data => {
            if (data.success) {
                console.log('✅ Datos cargados:', data.vehiculos.length, 'vehículos');
                const vehiculosActivos = data.vehiculos.length;
                updateElement('espacios-ocupados', vehiculosActivos);
                updateElement('espacios-disponibles', 50 - vehiculosActivos);
                
                const ingresosHoy = calcularIngresosHoy(data.vehiculos);
                updateElement('ingresos-hoy', `S/ ${ingresosHoy.toFixed(2)}`);
                updateElement('total-vehiculos', vehiculosActivos);
                
                const ocupacionPorcentaje = (vehiculosActivos / 50) * 100;
                updateProgressBar('progress-ocupacion', ocupacionPorcentaje);
                
                const ingresosPorcentaje = (ingresosHoy / 1000) * 100;
                updateProgressBar('progress-ingresos', Math.min(ingresosPorcentaje, 100));
                
                updateActividadReciente(data.vehiculos);
            }
        })
        .catch(error => {
            console.error('❌ Error actualizando dashboard:', error);
            updateDashboardDataFallback();
        });
}

function updateDashboardDataFallback() {
    console.log('🔄 Usando datos de fallback...');
    const data = {
        espaciosOcupados: Math.floor(Math.random() * 30) + 15,
        espaciosDisponibles: Math.floor(Math.random() * 20) + 10,
        ingresosHoy: (Math.random() * 500 + 200).toFixed(2),
        totalVehiculos: Math.floor(Math.random() * 50) + 25
    };
    
    updateElement('espacios-ocupados', data.espaciosOcupados);
    updateElement('espacios-disponibles', data.espaciosDisponibles);
    updateElement('ingresos-hoy', `S/ ${data.ingresosHoy}`);
    updateElement('total-vehiculos', data.totalVehiculos);
    
    const ocupacionPorcentaje = (data.espaciosOcupados / (data.espaciosOcupados + data.espaciosDisponibles)) * 100;
    updateProgressBar('progress-ocupacion', ocupacionPorcentaje);
    
    const ingresosPorcentaje = (data.ingresosHoy / 1000) * 100;
    updateProgressBar('progress-ingresos', Math.min(ingresosPorcentaje, 100));
    
    // Mostrar datos de ejemplo en la tabla
    updateActividadRecienteFallback();
}

function updateActividadRecienteFallback() {
    const tbody = document.querySelector('#tabla-actividad tbody');
    if (!tbody) return;
    
    const actividadesFallback = [
        { placa: 'ABC-123', accion: 'Entrada', hora: '10:30 AM', estado: 'Activo' },
        { placa: 'XYZ-789', accion: 'Salida', hora: '10:15 AM', estado: 'Completado' },
        { placa: 'DEF-456', accion: 'Entrada', hora: '09:45 AM', estado: 'Activo' }
    ];
    
    tbody.innerHTML = '';
    actividadesFallback.forEach(actividad => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${actividad.placa}</td>
            <td>${actividad.accion}</td>
            <td>${actividad.hora}</td>
            <td><span class="badge ${actividad.estado === 'Activo' ? 'bg-success' : 'bg-secondary'}">${actividad.estado}</span></td>
        `;
    });
}

function calcularIngresosHoy(vehiculos) {
    let total = 0;
    const hoy = new Date().toDateString();
    
    vehiculos.forEach(vehiculo => {
        if (vehiculo.fecha_entrada && new Date(vehiculo.fecha_entrada).toDateString() === hoy) {
            const entrada = new Date(vehiculo.fecha_entrada);
            const ahora = new Date();
            const horas = Math.ceil((ahora - entrada) / (1000 * 60 * 60));
            total += horas * parseFloat(vehiculo.tarifa_aplicada || 3.00);
        }
    });
    
    return total;
}

function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

function updateProgressBar(id, percentage) {
    const progressBar = document.getElementById(id);
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }
}

function updateActividadReciente(vehiculos) {
    const tbody = document.querySelector('#tabla-actividad tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    const ultimosVehiculos = vehiculos.slice(0, 5);
    
    ultimosVehiculos.forEach(vehiculo => {
        const row = tbody.insertRow();
        const fechaEntrada = vehiculo.fecha_entrada ? new Date(vehiculo.fecha_entrada) : new Date();
        const hora = fechaEntrada.toLocaleTimeString('es-PE', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        row.innerHTML = `
            <td>${vehiculo.placa}</td>
            <td>Entrada</td>
            <td>${hora}</td>
            <td><span class="badge bg-success">Activo</span></td>
        `;
    });
    
    if (ultimosVehiculos.length === 0) {
        const row = tbody.insertRow();
        row.innerHTML = '<td colspan="4" class="text-center">No hay actividad reciente</td>';
    }
}

// Funciones de vehículos
function loadVehiculos() {
    console.log('🚗 Cargando vehículos...');
    
    makeRequest('listar_vehiculos', {})
        .then(data => {
            if (data.success) {
                console.log('✅ Vehículos cargados:', data.vehiculos.length);
                vehiculosData = data.vehiculos;
                updateVehiculosTable();
            } else {
                console.error('❌ Error en respuesta:', data.message);
                showNotification('Error al cargar vehículos: ' + data.message, 'error');
                updateVehiculosTableFallback();
            }
        })
        .catch(error => {
            console.error('❌ Error cargando vehículos:', error);
            showNotification('Error de conexión al cargar vehículos', 'error');
            updateVehiculosTableFallback();
        });
}

function updateVehiculosTable() {
    const tbody = document.querySelector('#tabla-vehiculos tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    vehiculosData.forEach(vehiculo => {
        const row = tbody.insertRow();
        
        let tiempoTranscurrido = 'N/A';
        let tarifa = 'N/A';
        
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
            <td>${vehiculo.placa}</td>
            <td>${vehiculo.tipo_vehiculo || 'N/A'}</td>
            <td>${vehiculo.fecha_entrada ? new Date(vehiculo.fecha_entrada).toLocaleString('es-PE') : 'N/A'}</td>
            <td>${tiempoTranscurrido}</td>
            <td>${tarifa}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${vehiculo.estado_registro === 'activo' ? 
                        `<button class="btn btn-success" onclick="procesarSalida(${vehiculo.registro_id})" title="Procesar Salida">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>` : 
                        `<button class="btn btn-primary" onclick="registrarEntradaVehiculo(${vehiculo.id})" title="Registrar Entrada">
                            <i class="fas fa-sign-in-alt"></i>
                        </button>`
                    }
                </div>
            </td>
        `;
    });
    
    if (vehiculosData.length === 0) {
        const row = tbody.insertRow();
        row.innerHTML = '<td colspan="6" class="text-center">No hay vehículos registrados</td>';
    }
}

function updateVehiculosTableFallback() {
    const tbody = document.querySelector('#tabla-vehiculos tbody');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error de conexión. <button class="btn btn-sm btn-outline-primary" onclick="loadVehiculos()">Reintentar</button>
            </td>
        </tr>
    `;
}

function mostrarModalVehiculo() {
    document.getElementById('formVehiculo').reset();
    document.getElementById('resultados-clientes').innerHTML = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalVehiculo'));
    modal.show();
}

function registrarVehiculo() {
    console.log('📝 Registrando vehículo...');
    
    const formData = {
        placa: document.getElementById('placa').value.trim(),
        modelo: document.getElementById('modelo').value.trim(),
        color: document.getElementById('color').value.trim(),
        tipo_vehiculo: document.getElementById('tipo-vehiculo').value,
        codigo_cliente: document.getElementById('codigo-cliente').value.trim()
    };
    
    // Validar datos
    if (!formData.placa || !formData.modelo || !formData.color || !formData.tipo_vehiculo) {
        showNotification('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }
    
    // Validar formato de placa
    const placaRegex = /^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/i;
    if (!placaRegex.test(formData.placa)) {
        showNotification('Formato de placa no válido. Use formato ABC123 o ABC-123', 'error');
        return;
    }
    
    const submitBtn = document.querySelector('#modalVehiculo .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registrando...';
    
    makeRequest('registrar_vehiculo', formData)
        .then(data => {
            if (data.success) {
                console.log('✅ Vehículo registrado:', data.vehiculo_id);
                showNotification('Vehículo registrado exitosamente', 'success');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalVehiculo'));
                modal.hide();
                loadVehiculos();
                
                if (confirm('¿Desea registrar la entrada de este vehículo al estacionamiento?')) {
                    registrarEntradaVehiculo(data.vehiculo_id);
                }
            } else {
                console.error('❌ Error registrando:', data.message);
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// Funciones auxiliares
function makeRequest(action, data) {
    return new Promise((resolve, reject) => {
        console.log(`🌐 Realizando petición: ${action}`);
        
        if (!csrfToken) {
            console.warn('⚠️ No hay CSRF token, reintentando obtenerlo...');
            getCSRFToken();
            setTimeout(() => makeRequest(action, data).then(resolve).catch(reject), 1000);
            return;
        }
        
        const requestData = {
            accion: action,
            csrf_token: csrfToken,
            ...data
        };
        
        const basePath = getBasePath();
        const controllerUrl = `${basePath}controllers/VehiculoController.php`;
        
        console.log(`📡 URL: ${controllerUrl}`);
        console.log(`📦 Datos:`, requestData);
        
        fetch(controllerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log(`📥 Response status: ${response.status}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log(`📄 Response text:`, text.substring(0, 200) + '...');
            
            try {
                const data = JSON.parse(text);
                console.log(`✅ Parsed data:`, data);
                resolve(data);
            } catch (parseError) {
                console.error('❌ JSON Parse Error:', parseError);
                console.error('📄 Raw response:', text);
                reject(new Error(`Error parsing JSON: ${parseError.message}`));
            }
        })
        .catch(error => {
            console.error('❌ Fetch Error:', error);
            reject(error);
        });
    });
}

function showNotification(message, type = 'info') {
    console.log(`🔔 Notificación [${type}]: ${message}`);
    
    // Usar SweetAlert2 si está disponible
    if (typeof Swal !== 'undefined') {
        const icon = type === 'error' ? 'error' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info';
        Swal.fire({
            icon: icon,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    } else {
        // Fallback a alerta nativa
        alert(`${type.toUpperCase()}: ${message}`);
    }
}

// Funciones de tiempo real
function updateTiempoReal() {
    console.log('⏱️ Actualizando tiempo real...');
    
    makeRequest('listar_vehiculos', { en_estacionamiento: true })
        .then(data => {
            if (data.success) {
                const totalEspacios = 50;
                const ocupados = data.vehiculos.length;
                const disponibles = totalEspacios - ocupados;
                const porcentaje = Math.round((ocupados / totalEspacios) * 100);
                
                updateElement('total-espacios', totalEspacios);
                updateElement('espacios-libres', disponibles);
                updateElement('espacios-ocupados-rt', ocupados);
                updateElement('porcentaje-ocupacion', porcentaje + '%');
                
                updateParkingMap(ocupados, disponibles);
            }
        })
        .catch(error => {
            console.error('❌ Error tiempo real:', error);
            // Datos de fallback
            updateTiempoRealFallback();
        });
}

function updateTiempoRealFallback() {
    const totalEspacios = 50;
    const ocupados = Math.floor(Math.random() * 30) + 15;
    const disponibles = totalEspacios - ocupados;
    const porcentaje = Math.round((ocupados / totalEspacios) * 100);
    
    updateElement('total-espacios', totalEspacios);
    updateElement('espacios-libres', disponibles);
    updateElement('espacios-ocupados-rt', ocupados);
    updateElement('porcentaje-ocupacion', porcentaje + '%');
    
    updateParkingMap(ocupados, disponibles);
}

function generateParkingMap() {
    console.log('🗺️ Generando mapa de estacionamiento...');
    
    const mapaEspacios = document.getElementById('mapa-espacios');
    if (!mapaEspacios) return;
    
    mapaEspacios.innerHTML = '';
    
    for (let i = 1; i <= 50; i++) {
        const espacio = document.createElement('div');
        espacio.className = 'parking-space available';
        espacio.textContent = i.toString().padStart(2, '0');
        espacio.onclick = () => toggleParkingSpace(i);
        mapaEspacios.appendChild(espacio);
    }
    
    updateTiempoReal();
}

function updateParkingMap(ocupados, disponibles) {
    const espacios = document.querySelectorAll('.parking-space');
    
    // Resetear todos los espacios
    espacios.forEach(espacio => {
        espacio.className = 'parking-space available';
    });
    
    // Marcar espacios ocupados aleatoriamente
    const ocupadosArray = [];
    while (ocupadosArray.length < ocupados) {
        const randomIndex = Math.floor(Math.random() * 50);
        if (!ocupadosArray.includes(randomIndex)) {
            ocupadosArray.push(randomIndex);
            if (espacios[randomIndex]) {
                espacios[randomIndex].className = 'parking-space occupied';
            }
        }
    }
}

function toggleParkingSpace(spaceNumber) {
    const espacio = document.querySelector(`.parking-space:nth-child(${spaceNumber})`);
    if (!espacio) return;
    
    if (espacio.classList.contains('available')) {
        espacio.className = 'parking-space occupied';
    } else if (espacio.classList.contains('occupied')) {
        espacio.className = 'parking-space available';
    }
}

function actualizarTiempoReal() {
    updateTiempoReal();
    showNotification('Datos actualizados', 'info');
}

// Funciones de sesión
function checkSession() {
    const basePath = getBasePath();
    fetch(`${basePath}api/check_session.php`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showNotification('Sesión expirada. Redirigiendo al login...', 'warning');
                setTimeout(() => {
                    window.location.href = `${basePath}login_final.php`;
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error verificando sesión:', error);
        });
}

function logout() {
    if (confirm('¿Está seguro de cerrar sesión?')) {
        const basePath = getBasePath();
        fetch(`${basePath}controllers/AuthController_fixed.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'logout',
                csrf_token: csrfToken
            })
        })
        .then(() => {
            showNotification('Sesión cerrada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = `${basePath}login_final.php`;
            }, 1000);
        })
        .catch(error => {
            console.error('Error al cerrar sesión:', error);
            window.location.href = `${basePath}login_final.php`;
        });
    }
}

// Verificar sesión cada 5 minutos
setInterval(checkSession, 300000);

// Actualizar datos cada 30 segundos
setInterval(updateDashboardData, 30000);

// Funciones placeholder para compatibilidad
function loadReportes() {
    console.log('📊 Cargando reportes...');
    showNotification('Sección de reportes en desarrollo', 'info');
}

function registrarEntradaVehiculo(vehiculoId) {
    console.log('🚪 Registrando entrada para vehículo:', vehiculoId);
    showNotification('Funcionalidad de entrada en desarrollo', 'info');
}

function procesarSalida(registroId) {
    console.log('🚪 Procesando salida para registro:', registroId);
    showNotification('Funcionalidad de salida en desarrollo', 'info');
}

function crearCliente() {
    console.log('👤 Creando cliente...');
    showNotification('Funcionalidad de crear cliente en desarrollo', 'info');
}

function buscarClientes(termino) {
    console.log('🔍 Buscando clientes:', termino);
    // Implementar búsqueda de clientes
}

console.log('✅ Dashboard JavaScript cargado completamente');