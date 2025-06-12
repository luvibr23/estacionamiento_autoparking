<?php
// Script para integrar logout y funciones de vehículos en dashboards

echo "<h2>🔧 INTEGRACIÓN DE LOGOUT Y FUNCIONES DE VEHÍCULOS</h2>";
echo "<hr>";

$dashboards_to_update = [
    "views/admin_dashboard.php",
    "views/operador_dashboard.php"
];

foreach ($dashboards_to_update as $dashboard_file) {
    echo "<h4>Actualizando: $dashboard_file</h4>";
    
    if (!file_exists($dashboard_file)) {
        echo "❌ Archivo no encontrado: $dashboard_file<br>";
        continue;
    }
    
    $content = file_get_contents($dashboard_file);
    
    // 1. Agregar scripts de logout y vehículos si no están presentes
    $scripts_to_add = [
        "logout-functions.js",
        "vehiculos-functions.js"
    ];
    
    $scripts_added = 0;
    foreach ($scripts_to_add as $script) {
        if (strpos($content, $script) === false) {
            // Buscar donde insertar el script (antes del cierre de body)
            if (strpos($content, "</body>") !== false) {
                $script_tag = "    <script src=\"../assets/js/$script\"></script>\n";
                $content = str_replace("</body>", $script_tag . "</body>", $content);
                $scripts_added++;
                echo "✅ Script $script agregado<br>";
            }
        } else {
            echo "ℹ️ Script $script ya está incluido<br>";
        }
    }
    
    // 2. Verificar que el botón de logout tenga la función correcta
    if (strpos($content, "onclick=\"logout()\"") !== false) {
        echo "✅ Botón de logout ya configurado<br>";
    } else {
        // Buscar el botón de logout y corregirlo
        $patterns = [
            "/onclick=\"[^\"]*logout[^\"]*\"/",
            "/href=\"[^\"]*logout[^\"]*\"/"
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "onclick=\"logout()\"", $content);
                echo "✅ Botón de logout corregido<br>";
                break;
            }
        }
    }
    
    // 3. Guardar cambios si se hicieron modificaciones
    if ($scripts_added > 0) {
        file_put_contents($dashboard_file, $content);
        echo "💾 Archivo actualizado con $scripts_added script(s)<br>";
    }
    
    echo "<br>";
}

echo "<hr>";
echo "<h3>✅ INTEGRACIÓN COMPLETADA</h3>";
?>