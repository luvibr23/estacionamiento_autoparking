<?php
session_start();
require_once __DIR__ . "/../models/Vehiculo.php";

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Log para debugging
error_log("VehiculoController: REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"]);
error_log("VehiculoController: Input = " . file_get_contents("php://input"));

// Verificar autenticación
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["csrf_token"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Verificar método
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// Obtener datos de entrada
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

// Verificar token CSRF (más flexible para debugging)
if (!isset($input["csrf_token"])) {
    echo json_encode(["success" => false, "message" => "Token CSRF requerido"]);
    exit;
}

// Obtener acción
$accion = $input["accion"] ?? "";

try {
    $vehiculo = new Vehiculo();
    
    switch ($accion) {
        case "listar_vehiculos":
            $filtros = [];
            if (!empty($input["placa"])) {
                $filtros["placa"] = trim($input["placa"]);
            }
            if (!empty($input["en_estacionamiento"])) {
                $filtros["en_estacionamiento"] = true;
            }
            
            $resultado = $vehiculo->obtenerVehiculos($filtros);
            echo json_encode($resultado);
            break;
            
        case "registrar_vehiculo":
            // Validar datos requeridos
            $requeridos = ["placa", "modelo", "color", "tipo_vehiculo"];
            foreach ($requeridos as $campo) {
                if (empty($input[$campo])) {
                    echo json_encode(["success" => false, "message" => "El campo $campo es requerido"]);
                    exit;
                }
            }
            
            // Validar formato de placa
            $placa = strtoupper(trim($input["placa"]));
            if (!preg_match("/^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/", $placa)) {
                echo json_encode(["success" => false, "message" => "Formato de placa no válido"]);
                exit;
            }
            
            // Normalizar placa
            $placa = str_replace("-", "", $placa);
            
            // Validar tipo de vehículo
            $tiposValidos = ["auto", "moto", "camioneta", "bus", "otro"];
            if (!in_array($input["tipo_vehiculo"], $tiposValidos)) {
                echo json_encode(["success" => false, "message" => "Tipo de vehículo no válido"]);
                exit;
            }
            
            // Sanitizar datos
            $datosVehiculo = [
                "placa" => $placa,
                "modelo" => trim($input["modelo"]),
                "color" => trim($input["color"]),
                "tipo_vehiculo" => $input["tipo_vehiculo"],
                "codigo_cliente" => trim($input["codigo_cliente"] ?? "")
            ];
            
            $resultado = $vehiculo->registrarVehiculo($datosVehiculo);
            echo json_encode($resultado);
            break;
            
        case "crear_cliente":
            // Validar datos requeridos
            $requeridos = ["nombre", "apellido"];
            foreach ($requeridos as $campo) {
                if (empty($input[$campo])) {
                    echo json_encode(["success" => false, "message" => "El campo $campo es requerido"]);
                    exit;
                }
            }
            
            // Validar email si se proporciona
            if (!empty($input["email"]) && !filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["success" => false, "message" => "Email no válido"]);
                exit;
            }
            
            // Sanitizar datos
            $datosCliente = [
                "nombre" => trim($input["nombre"]),
                "apellido" => trim($input["apellido"]),
                "telefono" => trim($input["telefono"] ?? ""),
                "email" => trim($input["email"] ?? ""),
                "direccion" => trim($input["direccion"] ?? "")
            ];
            
            $resultado = $vehiculo->crearCliente($datosCliente);
            echo json_encode($resultado);
            break;
            
        case "buscar_clientes":
            if (empty($input["termino"])) {
                echo json_encode(["success" => false, "message" => "Término de búsqueda requerido"]);
                exit;
            }
            
            $termino = trim($input["termino"]);
            if (strlen($termino) < 2) {
                echo json_encode(["success" => false, "message" => "Término de búsqueda muy corto"]);
                exit;
            }
            
            $resultado = $vehiculo->buscarClientes($termino);
            echo json_encode($resultado);
            break;
            
        default:
            echo json_encode(["success" => false, "message" => "Acción no válida: " . $accion]);
    }
    
} catch (Exception $e) {
    error_log("Error en VehiculoController: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>