<?php
$response = file_get_contents('http://localhost/estacionamiento-php/controllers/AuthController_fixed.php', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'action' => 'login',
            'usuario' => 'admin',
            'password' => 'admin123'
        ])
    ]
]));

var_dump($response);
