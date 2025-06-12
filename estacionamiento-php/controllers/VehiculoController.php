<?php
session_start();
require_once __DIR__ . "/../models/Vehiculo.php";
require_once __DIR__ . "/../config/Database.php";
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Log para debugging
error_log("VehiculoController: REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"]);
error_log("VehiculoController: Session user_id = " . ($_SESSION["user_id"] ?? "no set"));

// Verificar autenticación básica (solo user_id es esencial)
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autorizado - Sesión inválida"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

try {
    $vehiculo = new Vehiculo();
    
    // Manejar solicitudes GET (lectura de datos)
    if ($method === "GET") {
        $accion = $_GET["accion"] ?? "";
        
        switch ($accion) {
            case "listar_vehiculos":
            case "listar": // Alias común
                $filtros = [];
                
                // Filtros opcionales desde GET parameters
                if (!empty($_GET["placa"])) {
                    $filtros["placa"] = trim($_GET["placa"]);
                }
                if (!empty($_GET["en_estacionamiento"])) {
                    $filtros["en_estacionamiento"] = true;
                }
                if (!empty($_GET["solo_registrados"])) {
                    $filtros["solo_registrados"] = true;
                }
                
                $resultado = $vehiculo->obtenerVehiculos($filtros);
                echo json_encode($resultado);
                break;
                
            case "obtener_vehiculo":
                if (empty($_GET["id"])) {
                    echo json_encode(["success" => false, "message" => "ID de vehículo requerido"]);
                    exit;
                }
                
                $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);
                if (!$id) {
                    echo json_encode(["success" => false, "message" => "ID de vehículo inválido"]);
                    exit;
                }
                
                error_log("Obteniendo vehículo con ID: " . $id);
                
                // Verificar que la clase y método existen
                if (!method_exists($vehiculo, 'obtenerVehiculoPorId')) {
                    echo json_encode(["success" => false, "message" => "Método obtenerVehiculoPorId no existe en la clase Vehiculo"]);
                    exit;
                }
                
                $resultado = $vehiculo->obtenerVehiculoPorId($id);
                error_log("Resultado obtenido: " . json_encode($resultado));
                echo json_encode($resultado);
                break;
                
            case "buscar_clientes":
                if (empty($_GET["termino"])) {
                    echo json_encode(["success" => false, "message" => "Término de búsqueda requerido"]);
                    exit;
                }
                
                $termino = trim($_GET["termino"]);
                if (strlen($termino) < 2) {
                    echo json_encode(["success" => false, "message" => "Término de búsqueda muy corto"]);
                    exit;
                }
                
                $resultado = $vehiculo->buscarClientes($termino);
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode(["success" => false, "message" => "Acción GET no válida: " . $accion]);
        }
        
    // Manejar solicitudes POST (escritura/modificación de datos)
    } elseif ($method === "POST") {
        
        // Obtener datos de entrada
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["success" => false, "message" => "Datos JSON inválidos"]);
            exit;
        }
        
        // Para operaciones POST, verificar CSRF token (más estricto)
        if (!isset($input["csrf_token"]) || empty($input["csrf_token"])) {
            echo json_encode(["success" => false, "message" => "Token CSRF requerido para operaciones de escritura"]);
            exit;
        }
        
        // Opcional: verificar que el CSRF token coincida
        if (isset($_SESSION["csrf_token"]) && $input["csrf_token"] !== $_SESSION["csrf_token"]) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido"]);
            exit;
        }
        
        // Obtener acción
        $accion = $input["accion"] ?? "";
        
        switch ($accion) {
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
                
            case "editar_vehiculo":
                if (empty($input["id"])) {
                    echo json_encode(["success" => false, "message" => "ID de vehículo requerido"]);
                    exit;
                }
                
                // Validar datos para edición
                $datosEdicion = [];
                
                if (!empty($input["placa"])) {
                    $placa = strtoupper(trim($input["placa"]));
                    if (!preg_match("/^[A-Z0-9]{6,8}$|^[A-Z]{3}-?[0-9]{3,4}$/", $placa)) {
                        echo json_encode(["success" => false, "message" => "Formato de placa no válido"]);
                        exit;
                    }
                    $datosEdicion["placa"] = str_replace("-", "", $placa);
                }
                
                if (!empty($input["modelo"])) {
                    $datosEdicion["modelo"] = trim($input["modelo"]);
                }
                
                if (!empty($input["color"])) {
                    $datosEdicion["color"] = trim($input["color"]);
                }
                
                if (!empty($input["tipo_vehiculo"])) {
                    $tiposValidos = ["auto", "moto", "camioneta", "bus", "otro"];
                    if (!in_array($input["tipo_vehiculo"], $tiposValidos)) {
                        echo json_encode(["success" => false, "message" => "Tipo de vehículo no válido"]);
                        exit;
                    }
                    $datosEdicion["tipo_vehiculo"] = $input["tipo_vehiculo"];
                }
                
                if (isset($input["codigo_cliente"])) {
                    $datosEdicion["codigo_cliente"] = trim($input["codigo_cliente"]);
                }
                
                $resultado = $vehiculo->editarVehiculo($input["id"], $datosEdicion);
                echo json_encode($resultado);
                break;
                
            case "eliminar_vehiculo":
                if (empty($input["id"])) {
                    echo json_encode(["success" => false, "message" => "ID de vehículo requerido"]);
                    exit;
                }
                
                $resultado = $vehiculo->eliminarVehiculo($input["id"]);
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode(["success" => false, "message" => "Acción POST no válida: " . $accion]);
        }
        
    } else {
        // Método no soportado
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método HTTP no permitido: " . $method]);
    }
    
} catch (Exception $e) {
    error_log("Error en VehiculoController: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>