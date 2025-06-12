// Funciones de logout mejoradas y seguras
console.log("🔐 Funciones de logout cargadas");

// Función principal de logout
async function logout() {
    console.log("🔐 Iniciando proceso de logout...");
    
    // Confirmación usando SweetAlert2 si está disponible
    let confirmarLogout = false;
    
    if (typeof Swal !== "undefined") {
        try {
            const result = await Swal.fire({
                title: "¿Cerrar sesión?",
                text: "¿Está seguro de que desea cerrar su sesión?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, cerrar sesión",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });
            confirmarLogout = result.isConfirmed;
        } catch (error) {
            console.log("Error con SweetAlert2, usando confirm nativo");
            confirmarLogout = confirm("¿Está seguro de cerrar sesión?");
        }
    } else {
        confirmarLogout = confirm("¿Está seguro de cerrar sesión?");
    }
    
    if (!confirmarLogout) {
        console.log("Logout cancelado por el usuario");
        return;
    }
    
    try {
        // Mostrar indicador de carga
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: "Cerrando sesión...",
                text: "Por favor espere",
                icon: "info",
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Intentar logout via AJAX
        const basePath = getBasePath();
        console.log("🌐 Intentando logout AJAX...");
        
        const response = await fetch(basePath + "controllers/AuthController_logout.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: "logout",
                csrf_token: window.csrfToken || "fallback-token"
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                console.log("✅ Logout AJAX exitoso");
                
                // Mostrar mensaje de éxito
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: "¡Hasta luego!",
                        text: "Su sesión ha sido cerrada exitosamente",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = basePath + "login_final.php?message=logout_success";
                    });
                } else {
                    alert("Sesión cerrada exitosamente");
                    window.location.href = basePath + "login_final.php?message=logout_success";
                }
                return;
            } else {
                console.warn("Logout AJAX falló:", data.message);
            }
        } else {
            console.warn("Response no OK:", response.status);
        }
        
    } catch (error) {
        console.error("❌ Error en logout AJAX:", error);
    }
    
    // Fallback: redirigir directamente a logout.php
    console.log("🔄 Usando fallback: logout.php");
    const basePath = getBasePath();
    window.location.href = basePath + "logout.php";
}

// Función para detectar ruta base
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes("/views/")) {
        return "../";
    }
    return "./";
}

// Función de redirección al menú principal
function irMenuPrincipal() {
    console.log("🏠 Redirigiendo al menú principal...");
    const basePath = getBasePath();
    window.location.href = basePath + "index.php";
}

// Verificar sesión
async function verificarSesion() {
    try {
        const basePath = getBasePath();
        const response = await fetch(basePath + "api/check_session.php");
        
        if (response.ok) {
            const data = await response.json();
            return data.success;
        }
    } catch (error) {
        console.error("Error verificando sesión:", error);
    }
    return false;
}

console.log("✅ Funciones de logout y navegación cargadas correctamente");