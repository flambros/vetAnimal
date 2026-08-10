<?php
require_once __DIR__ . '/db.php';

function usuario_actual() {
    return $_SESSION['usuario'] ?? null;
}

function esta_logueado() {
    return isset($_SESSION['usuario']);
}

function es_veterinario() {
    return esta_logueado() && $_SESSION['usuario']['rol'] === 'veterinario';
}

function requerir_login() {
    if (!esta_logueado()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requerir_veterinario() {
    requerir_login();
    if (!es_veterinario()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function iniciar_sesion_usuario(array $usuario) {
    unset($usuario['password_hash']);
    $_SESSION['usuario'] = $usuario;
}

function cerrar_sesion() {
    $_SESSION = [];
    session_destroy();
}
