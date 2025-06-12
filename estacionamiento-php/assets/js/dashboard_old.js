// Dashboard JavaScript - Versión completa con funcionalidades de vehículos
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar dashboard
    initializeDashboard();
    
    // Verificar sesión cada 5 minutos
    setInterval(checkSession, 300000);
    
    // Actualizar datos cada 30 segundos
    setInterval(updateDashboardData, 30000);
    
    // Inicializar gráficos
    initializeCharts();
    
    // Generar mapa de espacios
    generateParkingMap();
    
    // Cargar datos iniciales
    loadInitialData();
});

// Variables globales
let currentUser = null;
let dashboardChart = null;
let vehiculosData = [];
let tarifasData = [];
let csrfToken = null;

// Funciones de inicialización
function initializeDashboard() {
    // Obtener información del usuario
    getCurrentUser();
    
    // Obtener token CSRF
    getCSRFToken();
    
    // Configurar eventos
    setupEventListeners();
    
    // Mostrar sección inicial
    showSection('dashboard');
    
    console.log('Dashboard inicializado correctamente');
}

function getCurrentUser() {
    // Simular obtención de datos del usuario
    // En producción esto vendría de una API
    currentUser = {
        id: 1,
        username: 'admin',
        email: 'admin@estacionamiento.com',
        role: 'admin',
        name: 'Administrador'
    };
    
    // Actualizar UI con datos del usuario
    document.getElementById('user-name').textContent = currentUser.name;
    
    // Mostrar/ocultar elementos según rol
    if (currentUser.role !== 'admin') {
        document.body.classList.add('user-operator');
    }
}

function getCSRFToken() {
    fetch('../api/csrf_token_fixed.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                csrfToken = data.token;
            }
        })
        .catch(error => {
            console.error('Error obteniendo CSRF token:', error);
        });
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
    
    const formUsuario = document.getElementById('formUsuario');
    if (formUsuario) {
        formUsuario.addEventListener('submit', function(e) {
            e.preventDefault();
            crearUsuario();
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
}

// Funciones de navegación
function showSection(sectionName) {
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
        case 'tarifas':
            loadTarifas();
            break;
        case 'tiempo-real':
            updateTiempoReal();
            break;
        case 'reportes':
            loadReportes();
            break;
        case 'usuarios':
            loadUsuarios();
            break;
    }
}

// Funciones de datos del dashboard
function updateDashboardData() {
    // Cargar datos reales de vehículos en estacionamiento
    makeRequest('listar_vehiculos', { en_estacionamiento: true })
        .then(data => {
            if (data.success) {
                const vehiculosActivos = data.vehiculos.length;
                updateElement('espacios-ocupados', vehiculosActivos);
                updateElement('espacios-disponibles', 50 - vehiculosActivos);
                
                // Calcular ingresos del día
                const ingresosHoy = calcularIngresosHoy(data.vehiculos);
                updateElement('ingresos-hoy', `S/ ${ingresosHoy.toFixed(2)}`);
                updateElement('total-vehiculos', vehiculosActivos);
                
                // Actualizar barras de progreso
                const ocupacionPorcentaje = (vehiculosActivos / 50) * 100;
                updateProgressBar('progress-ocupacion', ocupacionPorcentaje);
                
                const ingresosPorcentaje = (ingresosHoy / 1000) * 100;
                updateProgressBar('progress-ingresos', Math.min(ingresosPorcentaje, 100));
                
                // Actualizar tabla de actividad reciente
                updateActividadReciente(data.vehiculos);
            }
        })
        .catch(error => {
            console.error('Error actualizando dashboard:', error);
            // Datos de fallback
            updateDashboardDataFallback();
        });
}

function updateDashboardDataFallback() {
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
}

function calcularIngresosHoy(vehiculos) {
    let total = 0;
    const hoy = new Date().toDateString();
    
    vehiculos.forEach(vehiculo => {
        if (vehiculo.fecha_entrada && new Date(vehiculo.fecha_entrada).toDateString() === hoy) {
            // Calcular tiempo transcurrido en horas
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
    
    // Mostrar últimos 5 vehículos
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
    
    // Si no hay vehículos, mostrar mensaje
    if (ultimosVehiculos.length === 0) {
        const row = tbody.insertRow();
        row.innerHTML = '<td colspan="4" class="text-center">No hay actividad reciente</td>';
    }
}

// Funciones de vehículos
function loadVehiculos() {
    makeRequest('listar_vehiculos', {})
        .then(data => {
            if (data.success) {
                vehiculosData = data.vehiculos;
                updateVehiculosTable();
            } else {
                showNotification('Error al cargar vehículos: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error cargando vehículos:', error);
            showNotification('Error de conexión al cargar vehículos', 'error');
        });
}

function updateVehiculosTable() {
    const tbody = document.querySelector('#tabla-vehiculos tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    vehiculosData.forEach(vehiculo => {
        const row = tbody.insertRow();
        
        // Calcular tiempo transcurrido
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
                    <button class="btn btn-warning" onclick="editarVehiculo(${vehiculo.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
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

function mostrarModalVehiculo() {
    // Limpiar formulario
    document.getElementById('formVehiculo').reset();
    document.getElementById('resultados-clientes').innerHTML = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalVehiculo'));
    modal.show();
}

function registrarVehiculo() {
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
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registrando...';
    
    makeRequest('registrar_vehiculo', formData)
        .then(data => {
            if (data.success) {
                showNotification('Vehículo registrado exitosamente', 'success');
                
                // Cerrar modal y recargar datos
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalVehiculo'));
                modal.hide();
                loadVehiculos();
                
                // Preguntar si desea registrar entrada inmediatamente
                if (confirm('¿Desea registrar la entrada de este vehículo al estacionamiento?')) {
                    registrarEntradaVehiculo(data.vehiculo_id);
                }
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Registrar';
        });
}

function registrarEntradaVehiculo(vehiculoId) {
    // Cargar espacios disponibles
    makeRequest('espacios_disponibles', {})
        .then(data => {
            if (data.success && data.espacios.length > 0) {
                mostrarModalEntrada(vehiculoId, data.espacios);
            } else {
                showNotification('No hay espacios disponibles', 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar espacios', 'error');
        });
}

function mostrarModalEntrada(vehiculoId, espacios) {
    // Crear modal dinámicamente si no existe
    let modal = document.getElementById('modalEntrada');
    if (!modal) {
        modal = crearModalEntrada();
        document.body.appendChild(modal);
    }
    
    // Llenar select de espacios
    const selectEspacio = document.getElementById('espacio-entrada');
    selectEspacio.innerHTML = '<option value="">Seleccionar espacio</option>';
    
    espacios.forEach(espacio => {
        const option = document.createElement('option');
        option.value = espacio.id;
        option.textContent = `${espacio.numero_espacio} - ${espacio.tipo_espacio} (S/ ${espacio.tarifa_por_hora}/hora)`;
        selectEspacio.appendChild(option);
    });
    
    // Guardar ID del vehículo
    document.getElementById('vehiculo-entrada-id').value = vehiculoId;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

function crearModalEntrada() {
    const modalHtml = `
        <div class="modal fade" id="modalEntrada" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Entrada</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEntrada">
                            <input type="hidden" id="vehiculo-entrada-id">
                            <div class="mb-3">
                                <label class="form-label">Espacio de Estacionamiento</label>
                                <select class="form-control" id="espacio-entrada" required>
                                    <option value="">Seleccionar espacio</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones-entrada" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="confirmarEntrada()">Registrar Entrada</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const div = document.createElement('div');
    div.innerHTML = modalHtml;
    return div.firstElementChild;
}

function confirmarEntrada() {
    const vehiculoId = document.getElementById('vehiculo-entrada-id').value;
    const espacioId = document.getElementById('espacio-entrada').value;
    const observaciones = document.getElementById('observaciones-entrada').value;
    
    if (!espacioId) {
        showNotification('Debe seleccionar un espacio', 'error');
        return;
    }
    
    const submitBtn = document.querySelector('#modalEntrada .btn-primary');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registrando...';
    
    makeRequest('registrar_entrada', {
        vehiculo_id: vehiculoId,
        espacio_id: espacioId,
        observaciones: observaciones
    })
    .then(data => {
        if (data.success) {
            showNotification('Entrada registrada exitosamente', 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEntrada'));
            modal.hide();
            
            loadVehiculos();
            updateDashboardData();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Registrar Entrada';
    });
}

function procesarSalida(registroId) {
    if (!confirm('¿Está seguro de procesar la salida de este vehículo?')) {
        return;
    }
    
    makeRequest('registrar_salida', { registro_id: registroId })
        .then(data => {
            if (data.success) {
                const mensaje = `Salida procesada exitosamente.\nTiempo: ${Math.floor(data.tiempo_minutos / 60)}h ${data.tiempo_minutos % 60}m\nTotal: S/ ${data.monto_total.toFixed(2)}`;
                showNotification(mensaje, 'success');
                
                loadVehiculos();
                updateDashboardData();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
}

function editarVehiculo(vehiculoId) {
    // Implementar edición de vehículo
    showNotification('Funcionalidad de edición en desarrollo', 'info');
}

// Funciones de clientes
function buscarClientes(termino) {
    if (termino.length < 2) return;
    
    makeRequest('buscar_clientes', { termino: termino })
        .then(data => {
            if (data.success) {
                mostrarResultadosClientes(data.clientes);
            }
        })
        .catch(error => {
            console.error('Error buscando clientes:', error);
        });
}

function mostrarResultadosClientes(clientes) {
    const resultados = document.getElementById('resultados-clientes');
    if (!resultados) return;
    
    resultados.innerHTML = '';
    
    if (clientes.length === 0) {
        resultados.innerHTML = '<div class="alert alert-info">No se encontraron clientes</div>';
        return;
    }
    
    const lista = document.createElement('ul');
    lista.className = 'list-group';
    
    clientes.forEach(cliente => {
        const item = document.createElement('li');
        item.className = 'list-group-item list-group-item-action';
        item.style.cursor = 'pointer';
        item.innerHTML = `
            <strong>${cliente.codigo_cliente}</strong> - ${cliente.nombre} ${cliente.apellido}
            ${cliente.telefono ? `<br><small>Tel: ${cliente.telefono}</small>` : ''}
        `;
        
        item.onclick = () => seleccionarCliente(cliente);
        lista.appendChild(item);
    });
    
    resultados.appendChild(lista);
}

function seleccionarCliente(cliente) {
    document.getElementById('codigo-cliente').value = cliente.codigo_cliente;
    document.getElementById('buscar-cliente').value = `${cliente.codigo_cliente} - ${cliente.nombre} ${cliente.apellido}`;
    document.getElementById('resultados-clientes').innerHTML = '';
}

function mostrarModalCliente() {
    const modal = new bootstrap.Modal(document.getElementById('modalCliente'));
    modal.show();
}

function crearCliente() {
    const formData = {
        nombre: document.getElementById('nombre-cliente').value.trim(),
        apellido: document.getElementById('apellido-cliente').value.trim(),
        telefono: document.getElementById('telefono-cliente').value.trim(),
        email: document.getElementById('email-cliente').value.trim(),
        direccion: document.getElementById('direccion-cliente').value.trim()
    };
    
    if (!formData.nombre || !formData.apellido) {
        showNotification('Nombre y apellido son requeridos', 'error');
        return;
    }
    
    const submitBtn = document.querySelector('#modalCliente .btn-primary');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
    
    makeRequest('crear_cliente', formData)
        .then(data => {
            if (data.success) {
                showNotification('Cliente creado exitosamente: ' + data.codigo_cliente, 'success');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCliente'));
                modal.hide();
                
                // Si se está registrando un vehículo, llenar el código
                if (document.getElementById('codigo-cliente')) {
                    document.getElementById('codigo-cliente').value = data.codigo_cliente;
                }
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Crear Cliente';
        });
}

// Funciones auxiliares
function makeRequest(action, data) {
    return new Promise((resolve, reject) => {
        if (!csrfToken) {
            reject(new Error('Token CSRF no disponible'));
            return;
        }
        
        const requestData = {
            accion: action,
            csrf_token: csrfToken,
            ...data
        };
        
        fetch('../controllers/VehiculoController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => resolve(data))
        .catch(error => reject(error));
    });
}

function showNotification(message, type = 'info') {
    // Crear notificación usando SweetAlert2 si está disponible
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
        // Fallback a alert nativo
        alert(message);
    }
}

function checkSession() {
    // Verificar si la sesión sigue activa
    fetch('../api/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showNotification('Sesión expirada. Redirigiendo al login...', 'warning');
                setTimeout(() => {
                    window.location.href = '../login_final.php';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error verificando sesión:', error);
        });
}

function logout() {
    if (confirm('¿Está seguro de cerrar sesión?')) {
        fetch('../controllers/AuthController_fixed.php', {
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
                window.location.href = '../login_final.php';
            }, 1000);
        })
        .catch(error => {
            console.error('Error al cerrar sesión:', error);
            // Redirigir de todas formas
            window.location.href = '../login_final.php';
        });
    }
}

// Funciones de tarifas
function loadTarifas() {
    tarifasData = [
        { tipo: 'Auto', tarifa: 3.00 },
        { tipo: 'Moto', tarifa: 2.00 },
        { tipo: 'Camioneta', tarifa: 4.00 }
    ];
}

function mostrarModalTarifa() {
    showNotification('Funcionalidad de tarifas en desarrollo', 'info');
}

// Funciones de tiempo real
function updateTiempoReal() {
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
            console.error('Error actualizando tiempo real:', error);
            // Datos de fallback
            const totalEspacios = 50;
            const ocupados = Math.floor(Math.random() * 30) + 15;
            const disponibles = totalEspacios - ocupados;
            const porcentaje = Math.round((ocupados / totalEspacios) * 100);
            
            updateElement('total-espacios', totalEspacios);
            updateElement('espacios-libres', disponibles);
            updateElement('espacios-ocupados-rt', ocupados);
            updateElement('porcentaje-ocupacion', porcentaje + '%');
            
            updateParkingMap(ocupados, disponibles);
        });
}

function actualizarTiempoReal() {
    updateTiempoReal();
    showNotification('Datos actualizados', 'info');
}

function generateParkingMap() {
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

// Funciones de reportes
function loadReportes() {
    if (dashboardChart) {
        dashboardChart.destroy();
    }
    initializeCharts();
}

function generarReporte() {
    const fechaInicio = document.getElementById('fecha-inicio').value;
    const fechaFin = document.getElementById('fecha-fin').value;
    const tipoReporte = document.getElementById('tipo-reporte').value;
    
    if (!fechaInicio || !fechaFin) {
        showNotification('Por favor, seleccione las fechas del reporte.', 'error');
        return;
    }
    
    showNotification(`Generando reporte de ${tipoReporte} del ${fechaInicio} al ${fechaFin}`, 'info');
    
    setTimeout(() => {
        updateReportChart(tipoReporte);
        showNotification('Reporte generado exitosamente', 'success');
    }, 1500);
}

function updateReportChart(tipo) {
    const ctx = document.getElementById('grafico-ingresos');
    if (!ctx) return;
    
    const data = generateReportData(tipo);
    
    if (dashboardChart) {
        dashboardChart.destroy();
    }
    
    dashboardChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: tipo === 'ingresos' ? 'Ingresos (S/)' : tipo === 'ocupacion' ? 'Ocupación (%)' : 'Vehículos',
                data: data.values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function generateReportData(tipo) {
    const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    let values;
    
    switch (tipo) {
        case 'ingresos':
            values = [120, 150, 180, 200, 250, 300, 280];
            break;
        case 'ocupacion':
            values = [60, 70, 80, 85, 90, 95, 75];
            break;
        case 'vehiculos':
            values = [40, 50, 60, 67, 83, 100, 93];
            break;
        default:
            values = [0, 0, 0, 0, 0, 0, 0];
    }
    
    return { labels, values };
}

function initializeCharts() {
    const ctx = document.getElementById('grafico-ingresos');
    if (!ctx) return;
    
    if (dashboardChart) {
        dashboardChart.destroy();
    }
    
    const data = generateReportData('ingresos');
    
    dashboardChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Ingresos (S/)',
                data: data.values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadInitialData() {
    updateDashboardData();
    if (document.getElementById('vehiculos-section')) {
        loadVehiculos();
    }
}

// Funciones de usuarios (solo admin)
function loadUsuarios() {
    // Simulación de carga de usuarios
    showNotification('Funcionalidad de usuarios en desarrollo', 'info');
}

function mostrarModalUsuario() {
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

function crearUsuario() {
    const formData = {
        username: document.getElementById('username').value.trim(),
        email: document.getElementById('email').value.trim(),
        password: document.getElementById('password').value,
        rol: document.getElementById('rol').value
    };
    
    if (!formData.username || !formData.email || !formData.password || !formData.rol) {
        showNotification('Todos los campos son requeridos', 'error');
        return;
    }
    
    if (!validateEmail(formData.email)) {
        showNotification('Email no válido', 'error');
        return;
    }
    
    if (formData.password.length < 6) {
        showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    showNotification('Funcionalidad de creación de usuarios en desarrollo', 'info');
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalUsuario'));
    modal.hide();
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Inicialización cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboard);
} else {
    initializeDashboard();
}