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
            $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            if ($this->placaExiste($datos["placa"])) {
                return ["success" => false, "message" => "La placa ya está registrada"];
            }

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
                  WHERE codigo_cliente LIKE 'CLI%'";
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
                          CONCAT(nombre, ' ', apellido) LIKE ?
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
}
?>