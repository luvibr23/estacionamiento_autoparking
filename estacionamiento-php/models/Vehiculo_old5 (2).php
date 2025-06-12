<?php
require_once __DIR__ . "/../config/database.php";

class Vehiculo {
    private $conn;
    private $table_vehiculos = "vehiculos";
    private $table_clientes = "clientes";
    private $table_registros = "registros_estacionamiento";
    private $table_espacios = "espacios_estacionamiento";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
/*

    public function obtenerVehiculos($filtros = []) {
        try {
            $query = "SELECT v.*, c.codigo_cliente, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                             r.id as registro_id, r.fecha_entrada, r.estado as estado_registro,
                             e.numero_espacio, r.tarifa_aplicada
                      FROM " . $this->table_vehiculos . " v
                      LEFT JOIN " . $this->table_clientes . " c ON v.cliente_id = c.id
                      LEFT JOIN " . $this->table_registros . " r ON v.id = r.vehiculo_id AND r.estado = \"activo\"
                      LEFT JOIN " . $this->table_espacios . " e ON r.espacio_id = e.id
                      WHERE v.activo = 1";
            
            $params = [];
            $types = "";
            
            if (!empty($filtros["placa"])) {
                $query .= " AND v.placa LIKE ?";
                $params[] = "%" . $filtros["placa"] . "%";
                $types .= "s";
            }
            
            if (!empty($filtros["en_estacionamiento"])) {
                $query .= " AND r.estado = \"activo\"";
            }
            
            $query .= " ORDER BY v.fecha_creacion DESC";
            
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculos = [];
            while ($row = $result->fetch_assoc()) {
                $vehiculos[] = $row;
            }
            
            return ["success" => true, "vehiculos" => $vehiculos];
            
        } catch (Exception $e) {
            error_log("Error en obtenerVehiculos: " . $e->getMessage());
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

*/
public function obtenerVehiculos($filtros = []) {
    try {
        $sql = "SELECT v.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido 
                FROM vehiculos v 
                LEFT JOIN clientes c ON v.codigo_cliente = c.codigo 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['placa'])) {
            $sql .= " AND v.placa LIKE ?";
            $params[] = '%' . $filtros['placa'] . '%';
        }
        
        if (!empty($filtros['en_estacionamiento'])) {
            $sql .= " AND v.en_estacionamiento = 1";
        }
        
        $sql .= " ORDER BY v.id DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
// ERROR: fetchAll reemplazado manualmente
        $vehiculos = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $vehiculos[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $vehiculos
        ];
        
    } catch (Exception $e) {
        error_log("Error en obtenerVehiculos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al obtener vehículos'
        ];
    }
}


    public function registrarVehiculo($datos) {
        try {
            // Verificar si la placa ya existe
            if ($this->placaExiste($datos["placa"])) {
                return ["success" => false, "message" => "La placa ya está registrada"];
            }

            // Verificar/crear cliente
            $clienteId = null;
            if (!empty($datos["codigo_cliente"])) {
                $clienteId = $this->obtenerClientePorCodigo($datos["codigo_cliente"]);
                if (!$clienteId) {
                    return ["success" => false, "message" => "Código de cliente no válido"];
                }
            }

            $query = "INSERT INTO " . $this->table_vehiculos . " 
                      (placa, modelo, color, tipo_vehiculo, cliente_id) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssi", 
                strtoupper($datos["placa"]),
                $datos["modelo"],
                $datos["color"],
                $datos["tipo_vehiculo"],
                $clienteId
            );
            
            if ($stmt->execute()) {
                return [
                    "success" => true,
                    "vehiculo_id" => $this->conn->insert_id,
                    "message" => "Vehículo registrado exitosamente"
                ];
            }
            
            return ["success" => false, "message" => "Error al registrar vehículo"];
            
        } catch (Exception $e) {
            error_log("Error en registrarVehiculo: " . $e->getMessage());
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    private function placaExiste($placa) {
        $query = "SELECT id FROM " . $this->table_vehiculos . " WHERE placa = ? AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", strtoupper($placa));
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    private function obtenerClientePorCodigo($codigo) {
        $query = "SELECT id FROM " . $this->table_clientes . " WHERE codigo_cliente = ? AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row["id"];
        }
        return null;
    }

    public function crearCliente($datos) {
        try {
            // Generar código de cliente único
            $codigoCliente = $this->generarCodigoCliente();
            
            $query = "INSERT INTO " . $this->table_clientes . " 
                      (codigo_cliente, nombre, apellido, telefono, email, direccion) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssss", 
                $codigoCliente,
                $datos["nombre"],
                $datos["apellido"],
                $datos["telefono"],
                $datos["email"],
                $datos["direccion"]
            );
            
            if ($stmt->execute()) {
                return [
                    "success" => true,
                    "cliente_id" => $this->conn->insert_id,
                    "codigo_cliente" => $codigoCliente,
                    "message" => "Cliente creado exitosamente"
                ];
            }
            
            return ["success" => false, "message" => "Error al crear cliente"];
            
        } catch (Exception $e) {
            error_log("Error en crearCliente: " . $e->getMessage());
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    private function generarCodigoCliente() {
        $query = "SELECT MAX(CAST(SUBSTRING(codigo_cliente, 4) AS UNSIGNED)) as max_num 
                  FROM " . $this->table_clientes . " 
                  WHERE codigo_cliente LIKE \"CLI%\"";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        $nextNum = ($row["max_num"] ?? 0) + 1;
        return "CLI" . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
    }

    public function buscarClientes($termino) {
        try {
            $query = "SELECT * FROM " . $this->table_clientes . " 
                      WHERE activo = 1 AND (
                          codigo_cliente LIKE ? OR 
                          nombre LIKE ? OR 
                          apellido LIKE ? OR
                          CONCAT(nombre, \" \", apellido) LIKE ?
                      )
                      ORDER BY nombre, apellido
                      LIMIT 20";
            
            $termino = "%" . $termino . "%";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $clientes = [];
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row;
            }
            
            return ["success" => true, "clientes" => $clientes];
            
        } catch (Exception $e) {
            error_log("Error en buscarClientes: " . $e->getMessage());
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }
public function obtenerVehiculoPorId($id) {
    try {
        $sql = "SELECT v.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido 
                FROM vehiculos v 
                LEFT JOIN clientes c ON v.codigo_cliente = c.codigo 
                WHERE v.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();
        if ($vehiculo) {
            return [
                'success' => true,
                'data' => $vehiculo
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Vehículo no encontrado'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en obtenerVehiculoPorId: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al obtener vehículo: ' . $e->getMessage()
        ];
    }
}

// Editar vehículo
public function editarVehiculo($id, $datos) {
    try {
        // Verificar que el vehículo existe
        $verificar = $this->obtenerVehiculoPorId($id);
        if (!$verificar['success']) {
            return $verificar;
        }
        
        // Construir query dinámicamente según los campos proporcionados
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['placa', 'modelo', 'color', 'tipo_vehiculo', 'codigo_cliente'];
        
        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = ?";
                $valores[] = $datos[$campo];
            }
        }
        
        if (empty($campos)) {
            return [
                'success' => false,
                'message' => 'No hay datos para actualizar'
            ];
        }
        
        // Verificar que la placa no esté duplicada (si se está cambiando)
        if (isset($datos['placa'])) {
            $sqlCheck = "SELECT id FROM vehiculos WHERE placa = ? AND id != ?";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->execute([$datos['placa'], $id]);
            
            if ($stmtCheck->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un vehículo con esa placa'
                ];
            }
        }
        
        // Agregar fecha de actualización
        $campos[] = "fecha_actualizacion = NOW()";
        
        // Construir y ejecutar query
        $sql = "UPDATE vehiculos SET " . implode(', ', $campos) . " WHERE id = ?";
        $valores[] = $id;
        
        $stmt = $this->conn->prepare($sql);
        $resultado = $stmt->execute($valores);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Vehículo actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar vehículo'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en editarVehiculo: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al editar vehículo'
        ];
    }
}

// Eliminar vehículo
public function eliminarVehiculo($id) {
    try {
        // Verificar que el vehículo existe
        $verificar = $this->obtenerVehiculoPorId($id);
        if (!$verificar['success']) {
            return $verificar;
        }
        
        // Verificar que el vehículo no esté actualmente en el estacionamiento
        $sql = "SELECT en_estacionamiento FROM vehiculos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();
        if ($vehiculo && $vehiculo['en_estacionamiento']) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar un vehículo que está actualmente en el estacionamiento'
            ];
        }
        
        // Eliminar el vehículo
        $sql = "DELETE FROM vehiculos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Vehículo eliminado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar vehículo'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en eliminarVehiculo: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al eliminar vehículo'
        ];
    }
}

// Método para obtener estadísticas de vehículos (bonus)
public function obtenerEstadisticas() {
    try {
        $sql = "SELECT 
                    COUNT(*) as total_vehiculos,
                    COUNT(CASE WHEN en_estacionamiento = 1 THEN 1 END) as en_estacionamiento,
                    COUNT(CASE WHEN tipo_vehiculo = 'auto' THEN 1 END) as autos,
                    COUNT(CASE WHEN tipo_vehiculo = 'moto' THEN 1 END) as motos,
                    COUNT(CASE WHEN tipo_vehiculo = 'camioneta' THEN 1 END) as camionetas,
                    COUNT(CASE WHEN tipo_vehiculo = 'bus' THEN 1 END) as buses
                FROM vehiculos";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();
        return [
            'success' => true,
            'data' => $estadisticas
        ];
        
    } catch (Exception $e) {
        error_log("Error en obtenerEstadisticas: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al obtener estadísticas'
        ];
    }
}

?>