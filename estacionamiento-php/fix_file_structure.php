<?php
// ================================================
// CORRECTOR DE ESTRUCTURA DE ARCHIVOS
// Archivo: fix_structure.php
// ================================================

echo "<h1>🔧 Corrector de Estructura de Archivos</h1>";

$baseDir = __DIR__;
echo "<p><strong>Directorio base:</strong> $baseDir</p>";

// Crear estructura de directorios
$directories = [
    'assets',
    'assets/css',
    'assets/js', 
    'assets/img',
    'api',
    'config',
    'controllers',
    'models',
    'views',
    'uploads',
    'uploads/qr',
    'uploads/pdf'
];

echo "<h2>📁 Creando Directorios</h2>";
foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "<p style='color: green;'>✅ Creado: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creando: $dir</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Ya existe: $dir</p>";
    }
}

// Contenido del CSS
$cssContent = '/* ESTILOS PARA LOGIN - SISTEMA DE ESTACIONAMIENTO */
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --dark-color: #34495e;
    --light-color: #ecf0f1;
}

body {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    min-height: 100vh;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
}

.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    z-index: 2;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 400px;
    width: 100%;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 30px 20px;
    text-align: center;
}

.login-header i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.9;
}

.login-header h4 {
    margin: 0;
    font-weight: 300;
    letter-spacing: 1px;
}

.login-body {
    padding: 40px 30px;
}

.form-floating {
    margin-bottom: 20px;
    position: relative;
}

.form-floating .form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 15px;
    font-size: 16px;
    transition: all 0.3s ease;
    background-color: #fff;
    height: auto;
    min-height: 58px;
}

.form-floating .form-control:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    background-color: #fff;
}

.form-floating label {
    color: #6c757d;
    font-weight: 500;
    padding: 1rem 1rem;
    transition: all 0.3s ease;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    z-index: 10;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    color: var(--secondary-color);
    background-color: rgba(52, 152, 219, 0.1);
}

.btn-login {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    border: none;
    border-radius: 12px;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    color: white;
    text-transform: uppercase;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
    color: white;
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.loading-spinner {
    display: none;
}

.floating-shapes {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
    pointer-events: none;
}

.shape {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.shape:nth-child(1) {
    width: 80px;
    height: 80px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.shape:nth-child(2) {
    width: 60px;
    height: 60px;
    top: 60%;
    right: 15%;
    animation-delay: 2s;
}

.shape:nth-child(3) {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
    50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
}

.alert {
    border-radius: 12px;
    border: none;
    margin-bottom: 20px;
}

@media (max-width: 576px) {
    .login-card { margin: 10px; border-radius: 15px; }
    .login-body { padding: 30px 20px; }
    .login-header { padding: 20px 15px; }
    .login-header i { font-size: 2.5rem; }
}';

// Contenido del JavaScript
$jsContent = '// JavaScript para LOGIN - SISTEMA DE ESTACIONAMIENTO
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
}';

// Crear archivos CSS y JS
echo "<h2>📄 Creando Archivos</h2>";

// Crear CSS
$cssFile = $baseDir . '/assets/css/login.css';
if (file_put_contents($cssFile, $cssContent)) {
    echo "<p style='color: green;'>✅ Creado: assets/css/login.css</p>";
} else {
    echo "<p style='color: red;'>❌ Error creando CSS</p>";
}

// Crear JS
$jsFile = $baseDir . '/assets/js/login.js';
if (file_put_contents($jsFile, $jsContent)) {
    echo "<p style='color: green;'>✅ Creado: assets/js/login.js</p>";
} else {
    echo "<p style='color: red;'>❌ Error creando JS</p>";
}

// Verificar creación
echo "<h2>🔍 Verificación</h2>";
$filesToVerify = [
    'assets/css/login.css' => filesize($cssFile ?? '') . ' bytes',
    'assets/js/login.js' => filesize($jsFile ?? '') . ' bytes'
];

foreach ($filesToVerify as $file => $size) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p style='color: green;'>✅ $file ($size)</p>";
        echo "<p style='margin-left: 20px;'><a href='$file' target='_blank'>Probar acceso: $file</a></p>";
    } else {
        echo "<p style='color: red;'>❌ $file no existe</p>";
    }
}

echo "<h2>🎯 Próximos Pasos</h2>";
echo "<ol>";
echo "<li>Verificar que los archivos se crearon correctamente</li>";
echo "<li>Probar acceso a: <a href='assets/css/login.css' target='_blank'>CSS</a> y <a href='assets/js/login.js' target='_blank'>JS</a></li>";
echo "<li>Si funcionan, probar el login: <a href='index.php'>index.php</a></li>";
echo "<li>Si no funcionan, usar: <a href='index_fixed.php'>index_fixed.php</a></li>";
echo "</ol>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    h1, h2 { color: #495057; }
    p { margin: 5px 0; }
</style>