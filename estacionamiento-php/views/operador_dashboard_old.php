<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['rol'] !== 'Operador') {
    header("Location: ../index_fixed.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Operador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?> (Operador)</h2>
        <a href="../logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
    <div class="card p-4">
        <h4>Panel de Operaciones</h4>
        <p>Aquí puedes realizar asignaciones y verificar estados de equipos.</p>
    </div>
</div>

</body>
</html>
