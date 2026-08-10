<?php
require_once __DIR__ . '/includes/auth.php';
cerrar_sesion();
header('Location: ' . BASE_URL . '/index.php');
exit;
