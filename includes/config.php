<?php
/**
 * Configuración de conexión a la base de datos.
 * Ajustá estos valores según tu servidor (XAMPP/WAMP/hosting).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'vetanimal');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Veterinaria VetAnimal');
define('BASE_URL', '/vetanimal'); // cambiar si se aloja en otra carpeta

session_start();
