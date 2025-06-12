// Funciones completas para gestión de vehículos
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
        const mensaje = `¿Está seguro de eliminar el vehículo con placa ${placa}?\n\n` +
                       `Esta acción marcará el vehículo como inactivo.\n` +
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
            confirmButtonText: "<i class=\"fas fa-times me-2\"></i>Cerrar",
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

console.log("✅ Funciones de vehículos cargadas completamente");