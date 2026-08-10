# Veterinaria VetAnimal — Sistema Web de Turnos

Sitio completo en **HTML + CSS + JavaScript + PHP** con base de datos **MySQL**, con panel de
cliente y panel de veterinario (admin), reserva de turnos paso a paso, historial clínico,
tienda y autenticación.

## 1. Requisitos

- Servidor con PHP 7.4+ (recomendado PHP 8.x) y extensión `pdo_mysql`.
- MySQL o MariaDB.
- Se recomienda **XAMPP**, **WAMP** o **Laragon** para probarlo en tu computadora.

## 2. Instalación local (XAMPP)

1. Copiá toda la carpeta `vetanimal` dentro de `htdocs` (en XAMPP suele ser
   `C:\xampp\htdocs\vetanimal` o `/Applications/XAMPP/htdocs/vetanimal`).
2. Iniciá los servicios **Apache** y **MySQL** desde el panel de XAMPP.
3. Abrí **phpMyAdmin** (`http://localhost/phpmyadmin`), creá una base llamada `vetanimal`
   (o dejá que el script la cree solo) e importá el archivo `sql/vetanimal.sql`
   (pestaña "Importar").
4. Revisá `includes/config.php` y ajustá `DB_USER` / `DB_PASS` si tu MySQL no usa el
   usuario `root` sin contraseña (el default de XAMPP).
5. Si la carpeta del proyecto **no** se llama `vetanimal`, actualizá la constante
   `BASE_URL` en `includes/config.php` (por ejemplo `define('BASE_URL', '/mi-carpeta');`).
6. Abrí `http://localhost/vetanimal/index.php` en el navegador.

## 3. Usuarios de prueba

| Rol         | Email                              | Contraseña |
|-------------|-------------------------------------|------------|
| Veterinario | santiago.mendez@vetanimal.com       | 123456     |
| Veterinario | martina.paz@vetanimal.com           | 123456     |
| Cliente     | agustina.gomez@example.com          | 123456     |

Los clientes nuevos pueden registrarse desde `register.php`.

## 4. Estructura del proyecto

```
vetanimal/
├── index.php              Página de inicio
├── login.php               Inicio de sesión (cliente y veterinario)
├── register.php             Registro de nuevos clientes
├── forgot-password.php       Recuperar contraseña
├── booking.php               Asistente de reserva de turnos (3 pasos)
├── mascota-nueva.php          Alta de mascota
├── historial.php               Historial clínico (portal cliente / vista veterinario)
├── registro-nuevo.php           Alta de registro clínico (solo veterinario)
├── tienda.php                    Tienda de productos
├── carrito.php                    Carrito de compras
├── perfil.php                      Mi cuenta / mis turnos
├── logout.php
├── admin/
│   ├── dashboard.php               Panel principal del veterinario
│   ├── turnos.php                   Gestión de todos los turnos
│   └── pacientes.php                 Listado de pacientes
├── includes/
│   ├── config.php    Configuración de conexión y constantes
│   ├── db.php         Conexión PDO
│   ├── auth.php         Funciones de sesión / roles
│   ├── header.php         Encabezado y navegación
│   ├── footer.php           Pie de página
│   └── admin-nav.php          Barra lateral del panel veterinario
├── css/style.css      Estilos generales
├── js/main.js           Interactividad general (tienda, confirmaciones)
├── js/booking.js          Interactividad del asistente de turnos
└── sql/vetanimal.sql        Script de base de datos con datos de ejemplo
```

## 5. Funcionalidad principal

- **Autenticación** con dos roles: `cliente` y `veterinario`, contraseñas hasheadas
  (`password_hash` / `password_verify`).
- **Reserva de turnos** en 3 pasos (Mascota y Servicio → Fecha y Hora → Revisión),
  con calendario dinámico, horarios ocupados bloqueados automáticamente y validación
  contra turnos duplicados a nivel de base de datos (`UNIQUE KEY`).
- **Historial clínico** por mascota: consultas, vacunación, estudios y alergias.
- **Panel del veterinario**: estadísticas del día, agenda, gestión de estados de
  turnos (pendiente / confirmado / completado / cancelado) y ficha de pacientes.
- **Tienda** con filtros por categoría, buscador y carrito de compras.

## 6. Notas

- Las imágenes de referencia se cargan desde Unsplash a modo ilustrativo; podés
  reemplazarlas por fotos propias en la carpeta `uploads/`.
- El botón "Llamada de Emergencia" abre el marcador telefónico (`tel:911`); cambiá
  el número por el de tu clínica.
- Este proyecto está pensado como base funcional/educativa: antes de llevarlo a
  producción sumá HTTPS, protección CSRF y validaciones adicionales del lado servidor.
