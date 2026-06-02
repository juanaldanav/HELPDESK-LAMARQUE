<?php
// Plantilla de configuración. Copiar a config.local.php y poner credenciales reales.
// config.local.php NO se versiona y debe ir chmod 600.
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'glpidb',
        'user'    => 'glpiuser',
        'pass'    => 'CHANGEME',
        'charset' => 'utf8mb4',
    ],
    'base_url'    => '/soporte-v2',          // ruta bajo la que sirve Apache (vacío '' en local php -S)
    'uploads_dir' => __DIR__ . '/uploads',   // fotos subidas
    'app_name'    => 'Soporte Lamarque',
    'smtp' => [
        'enabled'   => false,
        'host'      => 'smtp.gmail.com',
        'port'      => 587,
        'user'      => 'noreply@lamarque.mx',
        'pass'      => '',
        'from'      => 'noreply@lamarque.mx',
        'from_name' => 'Soporte Lamarque',
    ],
];
