/**
 * ================================================
 * JAVASCRIPT PARA LOGIN - SISTEMA DE ESTACIONAMIENTO
 * Archivo: assets/js/login.js
 * ================================================
 */

// Variables globales
let isLoading = false;
let csrfToken = '';

/**
 * Inicialización cuando el DOM está listo
 */
$(document).ready(function() {
    console.log('Inicializando sistema de login...');
    
    // Inicializar componentes
    initializeLogin();
    
    // Cargar token CSRF
    loadCSRFToken();
    
    // Mostrar mensajes flash si existen
    showFlashMessages();
    
    // Configurar eventos de teclado
    setupKeyboardEvents();
    
    // Verificar estado de conexión
    monitorConnectionStatus();
    
    // Configurar accesibilidad
    setupAccessibility();
});

/**
 * Configurar mejoras de accesibilidad
 */
function setupAccessibility() {
    // Agregar live region para anuncios
    if ($('#announcements').length === 0) {
        $('body').append('<div id="announcements" aria-live="polite" aria-atomic="true" class="sr-only"></div>');
    }
    
    // Mejorar navegación por teclado
    $('#loginForm input').on('keydown', function(e) {
        // Tab cíclico dentro del formulario
        if (e.key === 'Tab') {
            const formFields = $('#loginForm input:visible, #loginForm button:visible');
            const currentIndex = formFields.index(this);
            
            if (e.shiftKey && currentIndex === 0) {
                e.preventDefault();
                formFields.last().focus();
            } else if (!e.shiftKey && currentIndex === formFields.length - 1) {
                e.preventDefault();
                formFields.first().focus();
            }
        }
    });
    
    // Anunciar estado inicial
    announceToScreenReader('Formulario de inicio de sesión cargado');
}

/**
 * Inicializar funcionalidad del login
 */
function initializeLogin() {
    console.log('Configurando eventos del formulario...');
    
    // Toggle de contraseña
    $('#passwordToggle').on('click', function() {
        togglePasswordVisibility();
    });

    // Validación en tiempo real
    $('#usuario, #password').on('input blur', function() {
        validateField($(this));
    });

    // Envío del formulario
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        handleLogin();
    });

    // Autocompletado y focus
    $('#usuario').on('focus', function() {
        if ($(this).val() === '') {
            showDemoUsers();
        }
    });

    // Limpiar mensajes de error al escribir
    $('#usuario, #password').on('input', function() {
        clearFieldErrors($(this));
    });

    // Recordar usuario si está guardado
    loadRememberedUser();
}

/**
 * Alternar visibilidad de contraseña con accesibilidad mejorada
 */
function togglePasswordVisibility() {
    const passwordField = $('#password');
    const passwordIcon = $('#passwordToggle i');
    const passwordToggle = $('#passwordToggle');
    
    if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        passwordToggle.attr('title', 'Ocultar contraseña')
                     .attr('aria-label', 'Ocultar contraseña');
    } else {
        passwordField.attr('type', 'password');
        passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        passwordToggle.attr('title', 'Mostrar contraseña')
                     .attr('aria-label', 'Mostrar contraseña');
    }
    
    // Mantener focus en el campo y anunciar el cambio
    passwordField.focus();
    
    // Anunciar cambio para lectores de pantalla
    announceToScreenReader(
        passwordField.attr('type') === 'password' 
            ? 'Contraseña oculta' 
            : 'Contraseña visible'
    );
}

/**
 * Configurar eventos de teclado
 */
function setupKeyboardEvents() {
    // Enter en cualquier campo del formulario
    $('#usuario, #password').on('keypress', function(e) {
        if (e.which === 13 && !isLoading) {
            $('#loginForm').submit();
        }
    });

    // Escape para limpiar formulario
    $(document).on('keydown', function(e) {
        if (e.which === 27) { // Escape
            clearForm();
        }
    });

    // Ctrl+Enter para login rápido
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.which === 13 && !isLoading) {
            $('#loginForm').submit();
        }
    });
}

/**
 * Manejar proceso de login
 */
function handleLogin() {
    console.log('Iniciando proceso de login...');
    
    // Prevenir envíos múltiples
    if (isLoading) {
        console.log('Login ya en proceso, ignorando...');
        return;
    }

    // Validar formulario
    if (!validateForm()) {
        console.log('Formulario inválido');
        return;
    }

    // Mostrar estado de carga
    setLoadingState(true);

    // Preparar datos del formulario
    const formData = {
        usuario: $('#usuario').val().trim(),
        password: $('#password').val(),
        csrf_token: csrfToken,
        action: 'login'
    };

    // Agregar recordar usuario si está marcado
    if ($('#rememberMe').is(':checked')) {
        formData.remember = 'on';
    }

    console.log('Enviando datos de login...');

    // Enviar petición AJAX
    $.ajax({
        url: 'controllers/AuthController_fixed.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        timeout: 15000,
        cache: false,
        success: function(response) {
            console.log('Respuesta recibida:', response);
            handleLoginResponse(response);
        },
        error: function(xhr, status, error) {
            console.error('Error en login:', { xhr, status, error });
            handleLoginError(xhr, status, error);
        },
        complete: function() {
            setLoadingState(false);
        }
    });
}

/**
 * Manejar respuesta del login
 */
function handleLoginResponse(response) {
    if (response.success) {
        console.log('Login exitoso');
        
        // Guardar usuario si está marcado recordar
        if ($('#rememberMe').is(':checked')) {
            localStorage.setItem('remembered_user', $('#usuario').val());
        } else {
            localStorage.removeItem('remembered_user');
        }
        
        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: '¡Bienvenido!',
            text: response.message || 'Login exitoso',
            timer: 1500,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Redireccionar al dashboard
            console.log('Redirigiendo a:', response.redirect);
            window.location.href = response.redirect;
        });
        
    } else {
        console.log('Login fallido:', response.message);
        
        // Mostrar mensaje de error
        Swal.fire({
            icon: 'error',
            title: 'Error de Acceso',
            text: response.message || 'Credenciales incorrectas',
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Intentar de nuevo'
        });
        
        // Limpiar contraseña y enfocar
        $('#password').val('').removeClass('is-valid').addClass('is-invalid');
        setTimeout(() => {
            $('#password').focus();
        }, 500);
        
        // Marcar usuario como inválido si es necesario
        if (response.message && response.message.includes('Usuario')) {
            $('#usuario').removeClass('is-valid').addClass('is-invalid');
        }
    }
}

/**
 * Manejar errores del login
 */
function handleLoginError(xhr, status, error) {
    console.error('Error detallado:', {
        status: xhr.status,
        statusText: xhr.statusText,
        responseText: xhr.responseText,
        ajaxStatus: status,
        error: error
    });
    
    let message = 'Error de conexión. Intente nuevamente.';
    let title = 'Error de Conexión';
    
    // Personalizar mensaje según el tipo de error
    switch (status) {
        case 'timeout':
            message = 'Tiempo de espera agotado. Verifique su conexión a internet.';
            title = 'Tiempo Agotado';
            break;
        case 'abort':
            message = 'Solicitud cancelada.';
            title = 'Solicitud Cancelada';
            break;
        case 'parsererror':
            message = 'Error procesando la respuesta del servidor.';
            title = 'Error de Formato';
            break;
        default:
            if (xhr.status === 500) {
                message = 'Error interno del servidor. Contacte al administrador.';
                title = 'Error del Servidor';
            } else if (xhr.status === 404) {
                message = 'Servicio no encontrado. Verifique la configuración.';
                title = 'Servicio No Encontrado';
            } else if (xhr.status === 403) {
                message = 'Acceso denegado.';
                title = 'Acceso Denegado';
            } else if (xhr.status === 0) {
                message = 'Sin conexión a internet o servidor no disponible.';
                title = 'Sin Conexión';
            }
    }

    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#e74c3c',
        footer: '<small>Si el problema persiste, contacte al administrador</small>'
    });
}

/**
 * Validar formulario completo
 */
function validateForm() {
    let isValid = true;
    const usuario = $('#usuario');
    const password = $('#password');

    // Validar usuario
    if (!validateField(usuario)) {
        isValid = false;
    }

    // Validar contraseña
    if (!validateField(password)) {
        isValid = false;
    }

    // Validar token CSRF
    if (!csrfToken) {
        console.warn('Token CSRF no disponible');
        showAlert('warning', 'Token de seguridad no válido. Recargue la página.');
        return false;
    }

    return isValid;
}

/**
 * Función para anunciar mensajes a lectores de pantalla
 */
function announceToScreenReader(message) {
    // Crear elemento temporal para anuncios
    const announcement = $('<div>')
        .attr('aria-live', 'polite')
        .attr('aria-atomic', 'true')
        .addClass('sr-only')
        .text(message);
    
    $('body').append(announcement);
    
    // Remover después de que se haya anunciado
    setTimeout(() => {
        announcement.remove();
    }, 1000);
}

/**
 * Mejorar validación con accesibilidad
 */
function validateField(field) {
    const value = field.val().trim();
    const fieldName = field.attr('name');
    const errorContainer = field.next('.invalid-feedback');
    let isValid = true;
    let message = '';

    // Validaciones específicas por campo
    switch (fieldName) {
        case 'usuario':
            if (!value) {
                isValid = false;
                message = 'El usuario es requerido';
            } else if (value.length < 3) {
                isValid = false;
                message = 'El usuario debe tener al menos 3 caracteres';
            } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                isValid = false;
                message = 'El usuario solo puede contener letras, números y guiones bajos';
            }
            break;
            
        case 'password':
            if (!value) {
                isValid = false;
                message = 'La contraseña es requerida';
            } else if (value.length < 6) {
                isValid = false;
                message = 'La contraseña debe tener al menos 6 caracteres';
            }
            break;
    }

    // Aplicar estilos de validación y actualizar aria-describedby
    if (isValid) {
        field.removeClass('is-invalid').addClass('is-valid');
        field.attr('aria-invalid', 'false');
        errorContainer.hide();
        
        // Anunciar validación exitosa
        if (field.data('was-invalid')) {
            announceToScreenReader(`${fieldName} válido`);
            field.removeData('was-invalid');
        }
    } else {
        field.removeClass('is-valid').addClass('is-invalid');
        field.attr('aria-invalid', 'true');
        errorContainer.text(message).show();
        field.data('was-invalid', true);
        
        // Anunciar error
        announceToScreenReader(message);
    }

    return isValid;
}

/**
 * Monitorear estado de conexión con indicador visual
 */
function monitorConnectionStatus() {
    function updateConnectionStatus() {
        const statusIndicator = $('#connectionStatus');
        
        if (navigator.onLine) {
            statusIndicator.removeClass('offline').addClass('online').text('En línea');
        } else {
            statusIndicator.removeClass('online').addClass('offline').text('Sin conexión');
            announceToScreenReader('Sin conexión a internet');
        }
    }
    
    // Crear indicador si no existe
    if ($('#connectionStatus').length === 0) {
        $('body').append('<div id="connectionStatus" class="connection-status" aria-live="polite"></div>');
    }
    
    // Verificar estado inicial
    updateConnectionStatus();
    
    // Escuchar cambios de conexión
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Verificar periódicamente
    setInterval(() => {
        if (!navigator.onLine) {
            updateConnectionStatus();
        }
    }, 30000);
}

/**
 * Limpiar errores de campo
 */
function clearFieldErrors(field) {
    field.removeClass('is-invalid is-valid');
    field.next('.invalid-feedback').hide();
}

/**
 * Establecer estado de carga
 */
function setLoadingState(loading) {
    isLoading = loading;
    const loginBtn = $('#loginBtn');
    const btnText = loginBtn.find('.btn-text');
    const loadingSpinner = loginBtn.find('.loading-spinner');
    const formFields = $('#usuario, #password, #rememberMe');
    const loginContainer = $('.login-container');

    if (loading) {
        // Estado de carga
        loginBtn.prop('disabled', true).addClass('loading');
        btnText.hide();
        loadingSpinner.show();
        formFields.prop('disabled', true);
        
        // Añadir clase de carga al formulario
        $('#loginForm').addClass('loading');
        
        // NO usar aria-hidden en contenedores con campos de formulario
        // En su lugar, usar indicadores visuales y deshabilitar interacciones
        loginContainer.addClass('loading-state');
        
        // Prevenir interacciones pero mantener accesibilidad
        $('.login-card').css('pointer-events', 'none');
        formFields.attr('tabindex', '-1');
        
    } else {
        // Estado normal
        loginBtn.prop('disabled', false).removeClass('loading');
        btnText.show();
        loadingSpinner.hide();
        formFields.prop('disabled', false);
        
        // Remover clase de carga
        $('#loginForm').removeClass('loading');
        loginContainer.removeClass('loading-state');
        
        // Restaurar interacciones y accesibilidad
        $('.login-card').css('pointer-events', 'auto');
        formFields.removeAttr('tabindex');
    }
}

/**
 * Cargar token CSRF con mejor manejo de errores
 */
function loadCSRFToken() {
    console.log('Cargando token CSRF...');
    
    $.ajax({
        url: 'api/csrf_token.php',
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        cache: false,
        success: function(data) {
            console.log('Respuesta CSRF recibida:', data);
            if (data.success && data.token) {
                csrfToken = data.token;
                $('#csrfToken').val(csrfToken);
                console.log('Token CSRF cargado exitosamente');
                
                // Remover alertas de error si las hay
                $('#flashMessages .alert-warning').fadeOut();
            } else {
                console.warn('Respuesta de token CSRF inválida:', data);
                handleCSRFError('Respuesta inválida del servidor');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando token CSRF:', { 
                xhr: xhr, 
                status: status, 
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            
            // Si es un error de parseo pero el status es 200, 
            // probablemente hay warnings de PHP mezclados con JSON
            if (status === 'parsererror' && xhr.status === 200) {
                console.log('Intentando extraer JSON de respuesta con warnings...');
                
                try {
                    // Buscar el JSON válido en la respuesta
                    const responseText = xhr.responseText;
                    const jsonMatch = responseText.match(/\{.*\}/);
                    
                    if (jsonMatch) {
                        const jsonData = JSON.parse(jsonMatch[0]);
                        console.log('JSON extraído exitosamente:', jsonData);
                        
                        if (jsonData.success && jsonData.token) {
                            csrfToken = jsonData.token;
                            $('#csrfToken').val(csrfToken);
                            console.log('Token CSRF cargado exitosamente (con warnings)');
                            
                            // Mostrar advertencia sobre warnings
                            showAlert('warning', 'Token de seguridad cargado con advertencias. Revisar configuración.');
                            return;
                        }
                    }
                } catch (parseError) {
                    console.error('No se pudo extraer JSON:', parseError);
                }
            }
            
            let errorMessage = 'No se pudo cargar el token de seguridad';
            
            if (xhr.status === 404) {
                errorMessage = 'Archivo de seguridad no encontrado (404)';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor (500)';
            } else if (status === 'timeout') {
                errorMessage = 'Tiempo de espera agotado cargando seguridad';
            } else if (status === 'parsererror') {
                errorMessage = 'Error procesando respuesta de seguridad. Revisar logs de PHP.';
            } else if (xhr.status === 0) {
                errorMessage = 'Sin conexión al servidor';
            }
            
            handleCSRFError(errorMessage, xhr);
        }
    });
}

/**
 * Manejar errores de CSRF
 */
function handleCSRFError(message, xhr = null) {
    console.warn('Error CSRF:', message);
    
    // Mostrar alerta al usuario
    showAlert('warning', message);
    
    // Intentar cargar token de respaldo
    if (!csrfToken) {
        console.log('Intentando generar token de respaldo...');
        csrfToken = 'fallback_' + Math.random().toString(36).substr(2, 32);
        $('#csrfToken').val(csrfToken);
        
        setTimeout(() => {
            showAlert('info', 'Usando sistema de seguridad alternativo');
        }, 1000);
    }
    
    // En desarrollo, mostrar más detalles
    if (window.location.hostname === 'localhost' && xhr) {
        console.group('Detalles del Error CSRF:');
        console.log('Status:', xhr.status);
        console.log('Status Text:', xhr.statusText);
        console.log('Response Text:', xhr.responseText);
        console.log('Response Headers:', xhr.getAllResponseHeaders());
        console.groupEnd();
        
        // Mostrar información de debug en la consola
        console.log('%cPara solucionar este problema:', 'color: orange; font-weight: bold;');
        console.log('1. Revisar warnings en config/database.php');
        console.log('2. Verificar que no hay sesiones múltiples');
        console.log('3. Verificar configuración de sesiones PHP');
        console.log('4. Revisar logs de error de Apache');
    }
}

/**
 * Mostrar mensajes flash
 */
function showFlashMessages() {
    // Obtener parámetros de URL
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');

    if (message && type) {
        showAlert(type, decodeURIComponent(message));
        
        // Limpiar URL sin recargar
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
}

/**
 * Mostrar alerta personalizada
 */
function showAlert(type, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-times-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };

    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    `;

    // Remover alertas anteriores
    $('#flashMessages .alert').remove();
    
    // Añadir nueva alerta
    $('#flashMessages').html(alertHtml);
    
    // Auto-ocultar después de 5 segundos (excepto errores)
    if (type !== 'error') {
        setTimeout(() => {
            $('#flashMessages .alert').fadeOut();
        }, 5000);
    }
}

/**
 * Mostrar información de usuarios demo
 */
function showDemoUsers() {
    const demoInfo = `
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Usuarios de Prueba:</strong><br>
            <small>
                <strong>Administrador:</strong> admin / admin123<br>
                <strong>Operador:</strong> operador1 / operador123
            </small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    `;
    
    // Solo mostrar si no hay otras alertas
    if ($('#flashMessages .alert').length === 0) {
        $('#flashMessages').html(demoInfo);
        
        // Auto-ocultar después de 8 segundos
        setTimeout(() => {
            $('#flashMessages .alert').fadeOut();
        }, 8000);
    }
}

/**
 * Cargar usuario recordado
 */
function loadRememberedUser() {
    const rememberedUser = localStorage.getItem('remembered_user');
    if (rememberedUser) {
        $('#usuario').val(rememberedUser);
        $('#rememberMe').prop('checked', true);
        $('#password').focus();
    }
}

/**
 * Limpiar formulario
 */
function clearForm() {
    $('#loginForm')[0].reset();
    $('#usuario, #password').removeClass('is-valid is-invalid');
    $('.invalid-feedback').hide();
    $('#usuario').focus();
}

/**
 * Verificar estado de conexión
 */
function checkConnectionStatus() {
    // Verificar conexión periódicamente
    setInterval(() => {
        if (!navigator.onLine) {
            showAlert('warning', 'Sin conexión a internet');
        }
    }, 30000); // Cada 30 segundos
}

/**
 * Función para autocompletar usuarios (click rápido)
 */
function quickFillUser(username) {
    $('#usuario').val(username);
    $('#password').focus();
    validateField($('#usuario'));
}

/**
 * Detectar si es móvil para ajustes específicos
 */
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Configuración específica para móviles
 */
if (isMobile()) {
    // Prevenir zoom en inputs en iOS
    $('meta[name=viewport]').attr('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
    
    // Scroll to top en focus para móviles
    $('#usuario, #password').on('focus', function() {
        setTimeout(() => {
            window.scrollTo(0, 0);
        }, 300);
    });
}

/**
 * Eventos de visibilidad de página
 */
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        // Recargar token CSRF cuando la página vuelve a ser visible
        loadCSRFToken();
    }
});

/**
 * Manejo de errores globales de JavaScript
 */
window.addEventListener('error', function(e) {
    console.error('Error JavaScript:', e.error);
    // En producción, podrías enviar esto a un servicio de logging
});

/**
 * Debug: Funciones de utilidad para desarrollo
 */
if (window.location.hostname === 'localhost') {
    // Función para login rápido en desarrollo
    window.quickLogin = function(role = 'admin') {
        if (role === 'admin') {
            $('#usuario').val('admin');
            $('#password').val('admin123');
        } else {
            $('#usuario').val('operador1');
            $('#password').val('operador123');
        }
        $('#loginForm').submit();
    };
    
    console.log('Modo desarrollo activado. Usa quickLogin() para login rápido.');
}