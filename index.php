<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Veterinaria VetAnimal — Cuidado Compasivo para Cada Compañero</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <div class="eyebrow">Cuidado Compasivo</div>
        <h1>Atención avanzada para tus compañeros más queridos.</h1>
        <p>Experimentá medicina veterinaria de vanguardia brindada con calidez genuina. Combinamos precisión clínica con tranquilidad emocional.</p>
        <div class="hero-actions">
            <a href="booking.php" class="btn btn-primary">Reservar Turno</a>
            <a href="#servicios" class="btn btn-outline">Nuestros Servicios</a>
        </div>
    </div>
    <div class="hero-image" style="background-image:url('https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?w=900&q=80');"></div>
</section>

<section class="section" id="servicios">
    <div class="container">
        <div class="section-head">
            <h2>Servicios Integrales</h2>
            <p>Todo lo que tu mascota necesita en un solo lugar, utilizando tecnología moderna y técnicas amables.</p>
        </div>

        <div class="services-grid">
            <div class="service-card big">
                <div class="service-icon">♡</div>
                <h3>Atención General y Bienestar</h3>
                <p>Chequeos de rutina, vacunas y cuidados preventivos adaptados a la etapa específica de la vida de tu mascota.</p>
            </div>
            <div class="services-side">
                <div class="service-card blue">
                    <div class="service-icon">🩹</div>
                    <h3>Cirugía Avanzada</h3>
                    <p>Salas quirúrgicas de última generación equipadas para procedimientos tanto de rutina como complejos.</p>
                </div>
                <div class="service-card light">
                    <div class="service-icon">🧪</div>
                    <h3>Diagnósticos</h3>
                    <p>Laboratorio propio e imágenes digitales para un diagnóstico rápido y preciso.</p>
                </div>
                <div class="service-card light" style="grid-column:1/-1; flex-direction:row; align-items:center; justify-content:space-between;">
                    <div>
                        <h3>Farmacia Interna</h3>
                        <p>Acceso conveniente a medicamentos, dietas especializadas y tratamientos continuos directamente desde nuestra clínica.</p>
                    </div>
                    <a href="tienda.php" class="btn btn-light btn-sm">Recargar Receta</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="why-us">
    <div class="container why-grid">
        <div class="why-text">
            <div class="eyebrow">¿Por qué elegirnos?</div>
            <h2>Un entorno tranquilizador para mascotas y dueños nerviosos.</h2>
            <p>Diseñamos nuestra clínica para minimizar el estrés. Desde técnicas de manejo sin miedo hasta nuestro entorno especializado y relajante, priorizamos el bienestar emocional junto con la excelencia clínica.</p>
            <ul class="check-list">
                <li>Instalación acreditada por la AAHA</li>
                <li>Más de 20 años de experiencia combinada</li>
                <li>Profesionales certificados en Fear-Free</li>
            </ul>
        </div>
        <img src="https://images.unsplash.com/photo-1591946614720-90a587da4a36?w=800&q=80" alt="Cachorro en consulta veterinaria">
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
