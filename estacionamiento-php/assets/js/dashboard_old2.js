// Dashboard JavaScript
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

// Funciones de inicialización
function initializeDashboard() {
    // Obtener información del usuario
    getCurrentUser();
    
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
    
    // Configurar fechas por defecto en reportes
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    
    if (fechaInicio && fechaFin) {
        const hoy = new Date();
        const hace7dias = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000);
        
        fechaInicio.value = hace7dias.toISOString().split('T')[0];
        fechaFin.value = hoy.toISOString().split('T')[0];
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
    // Simular datos del dashboard
    const data = {
        espaciosOcupados: Math.floor(Math.random() * 30) + 15,
        espaciosDisponibles: Math.floor(Math.random() * 20) + 10,
        ingresosHoy: (Math.random() * 500 + 200).toFixed(2),
        totalVehiculos: Math.floor(Math.random() * 50) + 25
    };
    
    // Actualizar elementos del DOM
    updateElement('espacios-ocupados', data.espaciosOcupados);
    updateElement('espacios-disponibles', data.espaciosDisponibles);
    updateElement('ingresos-hoy', `S/ ${data.ingresosHoy}`);
    updateElement('total-vehiculos', data.totalVehiculos);
    
    // Actualizar barras de progreso
    const ocupacionPorcentaje = (data.espaciosOcupados / (data.espaciosOcupados + data.espaciosDisponibles)) * 100;
    updateProgressBar('progress-ocupacion', ocupacionPorcentaje);
    
    const ingresosPorcentaje = (data.ingresosHoy / 1000) * 100;
    updateProgressBar('progress-ingresos', Math.min(ingresosPorcentaje, 100));
    
    // Actualizar tabla de actividad reciente
    updateActividadReciente();
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

function updateActividadReciente() {
    const tbody = document.querySelector('#tabla-actividad tbody');
    if (!tbody) return;
    
    // Datos simulados de actividad reciente
    const actividades = [
        { placa: 'ABC-123', accion: 'Entrada', hora: '10:30 AM', estado: 'Activo' },
        { placa: 'XYZ-789', accion: 'Salida', hora: '10:15 AM', estado: 'Completado' },
        { placa: 'DEF-456', accion: 'Entrada', hora: '09:45 AM', estado: 'Activo' },
        { placa: 'GHI-012', accion: 'Salida', hora: '09:30 AM', estado: 'Completado' }
    ];
    
    tbody.innerHTML = '';
    actividades.forEach(actividad => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${actividad.placa}</td>
            <td>${actividad.accion}</td>
            <td>${actividad.hora}</td>
            <td><span class="badge ${actividad.estado === 'Activo' ? 'bg-success' : 'bg-secondary'}">${actividad.estado}</span></td>
        `;
    });
}

// Funciones de vehículos
function loadVehiculos() {
    // Simular carga de vehículos
    vehiculosData = [
        {
            id: 1,
            placa: 'ABC-123',
            tipo: 'Auto',
            horaEntrada: '08:30 AM',
            tiempoTranscurrido: '2h 15m',
            tarifa: 'S/ 6.75'
        },
        {
            id: 2,
            placa: 'XYZ-789',
            tipo: 'Moto',
            horaEntrada: '09:15 AM',
            tiempoTranscurrido: '1h 30m',
            tarifa: 'S/ 3.00'
        }
    ];
    
    updateVehiculosTable();
}

function updateVehiculosTable() {
    const tbody = document.querySelector('#tabla-vehiculos tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    vehiculosData.forEach(vehiculo => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${vehiculo.placa}</td>
            <td>${vehiculo.tipo}</td>
            <td>${vehiculo.horaEntrada}</td>
            <td>${vehiculo.tiempoTranscurrido}</td>
            <td>${vehiculo.tarifa}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editarVehiculo(${vehiculo.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-success" onclick="procesarSalida(${vehiculo.id})">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </td>
        `;
    });
}

function mostrarModalVehiculo() {
    const modal = new bootstrap.Modal(document.getElementById('modalVehiculo'));
    modal.show();
}

function registrarVehiculo() {
    const placa = document.getElementById('placa').value;
    const tipo = document.getElementById('tipo-vehiculo').value;
    const observaciones = document.getElementById('observaciones').value;
    
    if (!placa || !tipo) {
        alert('Por favor, complete todos los campos obligatorios.');
        return;
    }
    
    // Simular registro de vehículo
    const nuevoVehiculo = {
        id: vehiculosData.length + 1,
        placa: placa.toUpperCase(),
        tipo: tipo,
        horaEntrada: new Date().toLocaleTimeString('es-PE', { 
            hour: '2-digit', 
            minute: '2-digit' 
        }),
        tiempoTranscurrido: '0m',
        tarifa: 'S/ 0.00'
    };
    
    vehiculosData.push(nuevoVehiculo);
    updateVehiculosTable();
    
    // Cerrar modal y limpiar formulario
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalVehiculo'));
    modal.hide();
    document.getElementById('formVehiculo').reset();
    
    showNotification('Vehículo registrado exitosamente', 'success');
}

function procesarSalida(vehiculoId) {
    if (confirm('¿Está seguro de procesar la salida de este vehículo?')) {
        // Simular procesamiento de salida
        vehiculosData = vehiculosData.filter(v => v.id !== vehiculoId);
        updateVehiculosTable();
        showNotification('Salida procesada exitosamente', 'success');
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
    // Implementar modal de tarifas
    alert('Funcionalidad de tarifas en desarrollo');
}

// Funciones de tiempo real
function updateTiempoReal() {
    const totalEspacios = 50;
    const ocupados = Math.floor(Math.random() * 30) + 15;
    const disponibles = totalEspacios - ocupados;
    const porcentaje = Math.round((ocupados / totalEspacios) * 100);
    
    updateElement('total-espacios', totalEspacios);
    updateElement('espacios-libres', disponibles);
    updateElement('espacios-ocupados-rt', ocupados);
    updateElement('porcentaje-ocupacion', porcentaje + '%');
    
    // Actualizar mapa de espacios
    updateParkingMap(ocupados, disponibles);
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
    
    // Simular algunos espacios ocupados
    updateParkingMap(25, 25);
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
            espacios[randomIndex].className = 'parking-space occupied';
        }
    }
}

function toggleParkingSpace(spaceNumber) {
    const espacio = document.querySelector(`.parking-space:nth-child(${spaceNumber})`);
    if (espacio.classList.contains('available')) {
        espacio.className = 'parking-space occupied';
    } else if (espacio.classList.contains('occupied')) {
        espacio.className = 'parking-space available';
    }
}

// Funciones de reportes
function loadReportes() {
    // Cargar datos para reportes
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
        alert('Por favor, seleccione las fechas del reporte.');
        return;
    }
    
    showNotification(`Generando reporte de ${tipoReporte} del ${fechaInicio} al ${fechaFin}`, 'info');
    
    // Simular generación de reporte
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
            