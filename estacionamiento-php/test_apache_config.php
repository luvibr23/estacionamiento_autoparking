<?php
// ================================================
// TEST DE CONFIGURACIÓN APACHE
// Archivo: test_apache.php
// ================================================

echo "<h1>🔧 Test de Configuración Apache</h1>";

// Información del servidor
echo "<h2>📊 Información del Servidor</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";

$serverInfo = [
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    'HTTP_HOST' => $_SERVER['HTTP_HOST'],
    'SERVER_NAME' => $_SERVER['SERVER_NAME'],
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME']
];

foreach ($serverInfo as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";

// Verificar rutas del proyecto
echo "<h2>📁 Rutas del Proyecto</h2>";
$currentDir = __DIR__;
$projectPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $currentDir);
$projectPath = str_replace('\\', '/', $projectPath);

echo "<p><strong>Directorio actual:</strong> $currentDir</p>";
echo "<p><strong>Document Root:</strong> {$_SERVER['DOCUMENT_ROOT']}</p>";
echo "<p><strong>Ruta del proyecto:</strong> $projectPath</p>";

// Verificar archivos específicos
echo "<h2>🔍 Verificación de Archivos</h2>";
$filesToCheck = [
    'assets/css/login.css',
    'assets/js/login.js',
    'config/database.php',
    'controllers/AuthController.php',
    'api/csrf_token.php'
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Archivo</th><th>Existe</th><th>Ruta Completa</th><th>URL</th><th>Test</th></tr>";

foreach ($filesToCheck as $file) {
    $fullPath = $currentDir . '/' . $file;
    $exists = file_exists($fullPath);
    $url = "http://{$_SERVER['HTTP_HOST']}$projectPath/$file";
    
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>" . ($exists ? "✅ Sí" : "❌ No") . "</td>";
    echo "<td style='font-size: 11px;'>$fullPath</td>";
    echo "<td><a href='$url' target='_blank' style='font-size: 11px;'>$url</a></td>";
    echo "<td><button onclick='testUrl(\"$url\", this)'>Probar</button></td>";
    echo "</tr>";
}
echo "</table>";

// Información de PHP
echo "<h2>🐘 Información de PHP</h2>";
echo "<ul>";
echo "<li><strong>Versión:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Extensions cargadas:</strong> " . implode(', ', get_loaded_extensions()) . "</li>";
echo "<li><strong>Include Path:</strong> " . get_include_path() . "</li>";
echo "</ul>";

// Test de escritura
echo "<h2>✍️ Test de Escritura</h2>";
$testFile = $currentDir . '/test_write.txt';
if (file_put_contents($testFile, 'Test de escritura: ' . date('Y-m-d H:i:s'))) {
    echo "<p style='color: green;'>✅ Escritura exitosa en: $testFile</p>";
    unlink($testFile); // Limpiar archivo de prueba
} else {
    echo "<p style='color: red;'>❌ No se puede escribir en el directorio</p>";
}

// Mostrar contenido del directorio assets
echo "<h2>📂 Contenido del Directorio Assets</h2>";
$assetsDir = $currentDir . '/assets';
if (is_dir($assetsDir)) {
    function listDirectory($dir, $prefix = '') {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $dir . '/' . $item;
            if (is_dir($fullPath)) {
                echo "<p>$prefix📁 <strong>$item/</strong></p>";
                listDirectory($fullPath, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                $size = filesize($fullPath);
                echo "<p>$prefix📄 $item ($size bytes)</p>";
            }
        }
    }
    
    listDirectory($assetsDir);
} else {
    echo "<p style='color: red;'>❌ Directorio assets no existe</p>";
}
?>

<script>
function testUrl(url, button) {
    button.innerHTML = '🔄 Probando...';
    
    fetch(url)
        .then(response => {
            if (response.ok) {
                button.innerHTML = '✅ OK';
                button.style.background = '#28a745';
                button.style.color = 'white';
            } else {
                button.innerHTML = `❌ ${response.status}`;
                button.style.background = '#dc3545';
                button.style.color = 'white';
            }
        })
        .catch(error => {
            button.innerHTML = '❌ Error';
            button.style.background = '#dc3545';
            button.style.color = 'white';
        });
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    table { background: white; margin: 15px 0; }
    th { background: #e9ecef; }
    button { padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
</style>