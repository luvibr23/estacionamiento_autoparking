// JavaScript para LOGIN - SISTEMA DE ESTACIONAMIENTO
let isLoading = false;
let csrfToken = "";

$(document).ready(function() {
    console.log("Inicializando sistema de login...");
    initializeLogin();
    loadCSRFToken();
});

function initializeLogin() {
    // Toggle de contraseña
    $("#passwordToggle").click(function() {
        togglePasswordVisibility();
    });

    // Validación en tiempo real
    $("#usuario, #password").on("input blur", function() {
        validateField($(this));
    });

    // Envío del formulario
    $("#loginForm").on("submit", function(e) {
        e.preventDefault();
        handleLogin();
    });
}

function togglePasswordVisibility() {
    const passwordField = $("#password");
    const passwordIcon = $("#passwordToggle i");
    
    if (passwordField.attr("type") === "password") {
        passwordField.attr("type", "text");
        passwordIcon.removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
        passwordField.attr("type", "password");
        passwordIcon.removeClass("fa-eye-slash").addClass("fa-eye");
    }
    passwordField.focus();
}

function loadCSRFToken() {
    $.ajax({
        url: "api/csrf_token.php",
        type: "GET",
        dataType: "json",
        timeout: 10000,
        success: function(data) {
            if (data.success && data.token) {
                csrfToken = data.token;
                $("#csrfToken").val(csrfToken);
                console.log("Token CSRF cargado exitosamente");
            }
        },
        error: function() {
            console.warn("No se pudo cargar token CSRF");
            csrfToken = "fallback_" + Date.now();
            $("#csrfToken").val(csrfToken);
        }
    });
}

function handleLogin() {
    if (isLoading) return;
    
    if (!validateForm()) return;
    
    setLoadingState(true);
    
    const formData = {
        usuario: $("#usuario").val().trim(),
        password: $("#password").val(),
        csrf_token: csrfToken,
        action: "login"
    };
    
    $.ajax({
        url: "controllers/AuthController.php",
        type: "POST",
        data: formData,
        dataType: "json",
        timeout: 15000,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Bienvenido!",
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect;
                });
            } else {
                Swal.fire("Error", response.message, "error");
                $("#password").val("").focus();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en login:", xhr.responseText);
            Swal.fire("Error", "Error de conexión: " + error, "error");
        },
        complete: function() {
            setLoadingState(false);
        }
    });
}

function validateForm() {
    const usuario = $("#usuario").val().trim();
    const password = $("#password").val();
    
    if (!usuario || !password) {
        Swal.fire("Error", "Por favor complete todos los campos", "error");
        return false;
    }
    
    return true;
}

function validateField(field) {
    const value = field.val().trim();
    return value.length > 0;
}

function setLoadingState(loading) {
    isLoading = loading;
    const loginBtn = $("#loginBtn");
    const btnText = loginBtn.find(".btn-text");
    const loadingSpinner = loginBtn.find(".loading-spinner");

    if (loading) {
        loginBtn.prop("disabled", true);
        btnText.hide();
        loadingSpinner.show();
    } else {
        loginBtn.prop("disabled", false);
        btnText.show();
        loadingSpinner.hide();
    }
}