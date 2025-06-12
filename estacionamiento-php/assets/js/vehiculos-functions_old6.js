// Funciones para manejar vehículos
class VehiculoManager {
    constructor() {
        this.apiUrl = 'controllers/VehiculoController.php';
        this.csrfToken = this.getCsrfToken();
    }

    // Obtener token CSRF
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Cargar lista de vehículos (GET)
    async cargarVehiculos(filtros = {}) {
        try {
            const params = new URLSearchParams({
                accion: 'listar_vehiculos',
                ...filtros
            });

            const response = await fetch(`${this.apiUrl}?${params}`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.mostrarVehiculos(data.data || data.vehiculos || []);
            } else {
                throw new Error(data.message || 'Error al cargar vehículos');
            }
            
        } catch (error) {
            console.error('Error cargando vehículos:', error);
            this.mostrarError('Error al cargar los datos del vehículo: ' + error.message);
        }
    }

    // Registrar nuevo vehículo (POST)
    async registrarVehiculo(datosVehiculo) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    accion: 'registrar_vehiculo',
                    csrf_token: this.csrfToken,
                    ...datosVehiculo
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.mostrarExito('Vehículo registrado correctamente');
                this.cargarVehiculos(); // Recargar lista
            } else {
                throw new Error(data.message || 'Error al registrar vehículo');
            }
            
        } catch (error) {
            console.error('Error registrando vehículo:', error);
            this.mostrarError('Error al registrar vehículo: ' + error.message);
        }
    }

    // Obtener vehículo por ID (GET)
    async obtenerVehiculo(id) {
        try {
            const params = new URLSearchParams({
                accion: 'obtener_vehiculo',
                id: id
            });

            const response = await fetch(`${this.apiUrl}?${params}`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return null;
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Error al obtener vehículo');
            }
            
        } catch (error) {
            console.error('Error obteniendo vehículo:', error);
            this.mostrarError('Error al obtener vehículo: ' + error.message);
            return null;
        }
    }

    // Editar vehículo (POST)
    async editarVehiculo(id, datosVehiculo) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    accion: 'editar_vehiculo',
                    csrf_token: this.csrfToken,
                    id: id,
                    ...datosVehiculo
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.mostrarExito('Vehículo actualizado correctamente');
                this.cargarVehiculos(); // Recargar lista
                this.cerrarModalEdicion();
            } else {
                throw new Error(data.message || 'Error al editar vehículo');
            }
            
        } catch (error) {
            console.error('Error editando vehículo:', error);
            this.mostrarError('Error al editar vehículo: ' + error.message);
        }
    }

    // Eliminar vehículo (POST)
    async eliminarVehiculo(id) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    accion: 'eliminar_vehiculo',
                    csrf_token: this.csrfToken,
                    id: id
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.mostrarExito('Vehículo eliminado correctamente');
                this.cargarVehiculos(); // Recargar lista
            } else {
                throw new Error(data.message || 'Error al eliminar vehículo');
            }
            
        } catch (error) {
            console.error('Error eliminando vehículo:', error);
            this.mostrarError('Error al eliminar vehículo: ' + error.message);
        }
    }

    // Mostrar modal de edición
    async mostrarModalEdicion(id) {
        const vehiculo = await this.obtenerVehiculo(id);
        if (!vehiculo) return;

        // Crear modal si no existe
        this.crearModalEdicion();
        
        // Llenar formulario con datos actuales
        document.getElementById('editVehiculoId').value = vehiculo.id;
        document.getElementById('editPlaca').value = vehiculo.placa || '';
        document.getElementById('editModelo').value = vehiculo.modelo || '';
        document.getElementById('editColor').value = vehiculo.color || '';
        document.getElementById('editTipoVehiculo').value = vehiculo.tipo_vehiculo || '';
        document.getElementById('editCodigoCliente').value = vehiculo.codigo_cliente || '';
        
        // Mostrar modal
        $('#modalEditarVehiculo').modal('show');
    }

    // Crear modal de edición dinámicamente
    crearModalEdicion() {
        if (document.getElementById('modalEditarVehiculo')) return;

        const modalHTML = `
            <div class="modal fade" id="modalEditarVehiculo" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Vehículo</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="formEditarVehiculo">
                                <input type="hidden" id="editVehiculoId" name="id">
                                
                                <div class="form-group">
                                    <label for="editPlaca">Placa</label>
                                    <input type="text" class="form-control" id="editPlaca" name="placa" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="editModelo">Modelo</label>
                                    <input type="text" class="form-control" id="editModelo" name="modelo" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="editColor">Color</label>
                                    <input type="text" class="form-control" id="editColor" name="color" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="editTipoVehiculo">Tipo de Vehículo</label>
                                    <select class="form-control" id="editTipoVehiculo" name="tipo_vehiculo" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="auto">Auto</option>
                                        <option value="moto">Moto</option>
                                        <option value="camioneta">Camioneta</option>
                                        <option value="bus">Bus</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="editCodigoCliente">Código Cliente</label>
                                    <input type="text" class="form-control" id="editCodigoCliente" name="codigo_cliente">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" onclick="guardarEdicionVehiculo()">Guardar Cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Cerrar modal de edición
    cerrarModalEdicion() {
        if (typeof $ !== 'undefined') {
            $('#modalEditarVehiculo').modal('hide');
        } else {
            const modal = document.getElementById('modalEditarVehiculo');
            if (modal) {
                modal.style.display = 'none';
            }
        }
    }

    // Confirmar eliminación
    async confirmarEliminacion(id) {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                this.eliminarVehiculo(id);
            }
        } else {
            if (confirm('¿Estás seguro de que quieres eliminar este vehículo?')) {
                this.eliminarVehiculo(id);
            }
        }
    }
    async buscarClientes(termino) {
        try {
            const params = new URLSearchParams({
                accion: 'buscar_clientes',
                termino: termino
            });

            const response = await fetch(`${this.apiUrl}?${params}`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            return data.success ? data.data : [];
            
        } catch (error) {
            console.error('Error buscando clientes:', error);
            return [];
        }
    }

    // Mostrar vehículos en la tabla
    mostrarVehiculos(vehiculos) {
        const tbody = document.querySelector('#tablaVehiculos tbody');
        if (!tbody) {
            console.error('No se encontró la tabla de vehículos');
            return;
        }

        tbody.innerHTML = '';

        if (vehiculos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay vehículos registrados</td></tr>';
            return;
        }

        vehiculos.forEach(vehiculo => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${vehiculo.placa || 'N/A'}</td>
                <td>${vehiculo.modelo || 'N/A'}</td>
                <td>${vehiculo.color || 'N/A'}</td>
                <td>${vehiculo.cliente_nombre || 'N/A'}</td>
                <td>${vehiculo.estado || 'N/A'}</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary" onclick="editarVehiculo(${vehiculo.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info" onclick="verDetalles(${vehiculo.id})" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarVehiculo(${vehiculo.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Mostrar mensajes de error
    mostrarError(mensaje) {
        // Usar SweetAlert2 si está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        } else {
            alert('Error: ' + mensaje);
        }
    }

    // Mostrar detalles del vehículo
    mostrarDetallesVehiculo(vehiculo) {
        const detallesHTML = `
            <div class="card">
                <div class="card-header">
                    <h5>Detalles del Vehículo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Placa:</strong> ${vehiculo.placa || 'N/A'}</p>
                            <p><strong>Modelo:</strong> ${vehiculo.modelo || 'N/A'}</p>
                            <p><strong>Color:</strong> ${vehiculo.color || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tipo:</strong> ${vehiculo.tipo_vehiculo || 'N/A'}</p>
                            <p><strong>Cliente:</strong> ${vehiculo.cliente_nombre ? vehiculo.cliente_nombre + ' ' + (vehiculo.cliente_apellido || '') : 'N/A'}</p>
                            <p><strong>Estado:</strong> ${vehiculo.estado || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Detalles del Vehículo',
                html: detallesHTML,
                width: 600,
                showCloseButton: true,
                showConfirmButton: false
            });
        } else {
            // Crear modal simple si no hay SweetAlert
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detalles del Vehículo</h5>
                            <button type="button" class="close" onclick="this.closest('.modal').remove()">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${detallesHTML}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            modal.style.display = 'block';
        }
    }
    mostrarExito(mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: mensaje
            });
        } else {
            alert(mensaje);
        }
    }
}

// Instancia global
const vehiculoManager = new VehiculoManager();

// Funciones para compatibilidad con código existente
function cargarVehiculos(filtros = {}) {
    vehiculoManager.cargarVehiculos(filtros);
}

function registrarVehiculo(datos) {
    vehiculoManager.registrarVehiculo(datos);
}

function buscarClientes(termino) {
    return vehiculoManager.buscarClientes(termino);
}

function editarVehiculo(id) {
    vehiculoManager.mostrarModalEdicion(id);
}

function verDetalles(id) {
    vehiculoManager.obtenerVehiculo(id).then(vehiculo => {
        if (vehiculo) {
            vehiculoManager.mostrarDetallesVehiculo(vehiculo);
        }
    });
}

function eliminarVehiculo(id) {
    vehiculoManager.confirmarEliminacion(id);
}

function guardarEdicionVehiculo() {
    const form = document.getElementById('formEditarVehiculo');
    const formData = new FormData(form);
    const datos = Object.fromEntries(formData.entries());
    const id = datos.id;
    delete datos.id; // Remover ID de los datos a enviar
    
    vehiculoManager.editarVehiculo(id, datos);
}

// Cargar vehículos al inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarVehiculos();
});

// Manejar formulario de registro
document.addEventListener('DOMContentLoaded', function() {
    const formRegistro = document.getElementById('formRegistroVehiculo');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const datos = Object.fromEntries(formData.entries());
            
            registrarVehiculo(datos);
        });
    }
});