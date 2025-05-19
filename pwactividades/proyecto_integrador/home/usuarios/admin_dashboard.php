<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    // Si no es admin, redirigir al login
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitity - Panel de Administración</title>
    <!-- Bulma CSS Framework para estilos base -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personalizado global -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar de navegación de administrador -->
        <?php include '../../includes/admin_sidebar.php'; ?>

        <!-- Contenido principal del dashboard -->
        <main class="main-content">
            <h1 class="title is-4 mb-5">Panel de Administración</h1>

            <!-- Estadísticas Generales (puedes conectar con la BD para datos reales) -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Usuarios</h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Hábitos Activos</h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Metas en Progreso</h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>
            </div>

            <!-- Últimas Actividades (puedes conectar con la BD para datos reales) -->
            <div class="card mt-5">
                <div class="card-header">
                    <h2 class="title is-5">Últimas Actividades</h2>
                </div>
                <div class="card-content">
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No hay actividades recientes</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 