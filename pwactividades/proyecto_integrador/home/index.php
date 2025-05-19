<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Incluir la conexión a la base de datos
    require_once '../includes/conexion.php';

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 2) {
        header('Location: ../login.php');
        exit();
    }

    // Obtener el nombre completo del usuario desde la sesión
    $nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellido_p'];

    // Obtener todos los hábitos del usuario
    $query_habitos = "SELECT 
                        rh.id_registro,
                        h.nombre as nombre_habito,
                        rh.objetivo,
                        rh.progreso,
                        rh.completado,
                        f.periodo,
                        rh.fec_inicio,
                        rh.fec_fin,
                        c.nombre as categoria_nombre
                     FROM registro_habito rh
                     JOIN habito h ON rh.id_habito = h.id_habito
                     JOIN frecuencia f ON rh.id_frecuencia = f.id_frecuencia
                     JOIN categoria c ON h.id_categoria = c.id_categoria
                     WHERE rh.id_usuario = ? 
                     ORDER BY rh.fec_inicio DESC";
    
    try {
        $stmt_habitos = $conn->prepare($query_habitos);
        $stmt_habitos->execute([$_SESSION['id_usuario']]);
        $habitos = $stmt_habitos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching habits: " . $e->getMessage());
        $habitos = [];
    }

    // Obtener todas las metas del usuario
    $query_metas = "SELECT 
                        m.id_meta,
                        m.descripcion,
                        m.objetivo,
                        m.estado,
                        m.frec_meta,
                        m.periodo,
                        m.cumplida,
                        m.fec_inicio,
                        m.fec_fin,
                        h.nombre as nombre_habito,
                        c.nombre as categoria_nombre
                    FROM meta m
                    JOIN habito h ON m.id_habito = h.id_habito
                    JOIN categoria c ON h.id_categoria = c.id_categoria
                    WHERE m.id_usuario = ? 
                    ORDER BY m.fec_inicio DESC";

    try {
        $stmt_metas = $conn->prepare($query_metas);
        $stmt_metas->execute([$_SESSION['id_usuario']]);
        $metas = $stmt_metas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching goals: " . $e->getMessage());
        $metas = [];
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitity - Dashboard</title>
    <!-- Bulma CSS Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .progress-bar {
            background-color: #f5f5f5;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .status-active {
            background-color: #fffbeb;
            color: #b45309;
        }
        .status-completed {
            background-color: #f0fdf4;
            color: #166534;
        }
        .status-pending {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .habit-item, .goal-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .habit-item:last-child, .goal-item:last-child {
            border-bottom: none;
        }
        .completed-date {
            font-size: 0.8rem;
            color: #166534;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="has-text-white"><span><?php echo htmlspecialchars($nombre_completo); ?></span></h3>
            </div>

            <nav>
                <div class="nav-group">
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <a href="habitos/index.php" class="nav-item">
                        <i class="fas fa-tasks"></i>
                        Mis Hábitos
                    </a>
                    <a href="metas/index.php" class="nav-item">
                        <i class="fas fa-bullseye"></i>
                        Mis Metas
                    </a> 
                    <a href="../logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="title is-4 mb-5">Dashboard</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="notification is-danger is-light">
                    <button class="delete"></button>
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="notification is-success is-light">
                    <button class="delete"></button>
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Hábitos Section -->
            <div class="card mb-5">
                <div class="card-header">
                    <h2 class="title is-5">Mis Hábitos</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($habitos)): ?>
                        <div class="has-text-centered py-5">
                            <p class="has-text-grey">No tienes hábitos registrados aún.</p>
                            <p class="has-text-grey is-size-7">Agrega un nuevo hábito en la sección de hábitos.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($habitos as $habito): ?>
                            <?php
                                $porcentaje = ($habito['objetivo'] > 0) ? min(100, round(($habito['progreso'] / $habito['objetivo']) * 100)) : 0;
                                $estado = $habito['completado'] ? 'completed' : ($habito['progreso'] > 0 ? 'active' : 'pending');
                                $estado_texto = $habito['completado'] ? 'Completado' : ($habito['progreso'] > 0 ? 'En proceso' : 'Pendiente');
                            ?>
                            <div class="habit-item">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                    <h3 class="is-size-6"><?php echo htmlspecialchars($habito['nombre_habito']); ?></h3>
                                    <span class="status-badge status-<?php echo $estado; ?>"><?php echo $estado_texto; ?></span>
                                </div>
                                <div class="progress-bar mb-2">
                                    <div class="progress-bar-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                </div>
                                <p class="is-size-7 has-text-grey">
                                    <?php echo $habito['progreso']; ?>/<?php echo $habito['objetivo']; ?> 
                                    veces por <?php echo $habito['periodo']; ?>
                                </p>
                                <?php if ($habito['completado'] && $habito['fec_fin']): ?>
                                    <p class="completed-date">
                                        Completado el <?php echo date('d/m/Y', strtotime($habito['fec_fin'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="has-text-centered mt-4">
                            <a href="habitos/index.php" class="button is-small is-light">
                                Ver todos los hábitos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metas Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="title is-5">Mis Metas</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($metas)): ?>
                        <div class="has-text-centered py-5">
                            <p class="has-text-grey">No tienes metas registradas aún.</p>
                            <p class="has-text-grey is-size-7">Crea una nueva meta en la sección de metas.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($metas as $meta): ?>
                            <?php
                                $estado = $meta['cumplida'] ? 'completed' : ($meta['estado'] === 'en proceso' ? 'active' : 'pending');
                                $estado_texto = $meta['cumplida'] ? 'Completada' : ($meta['estado'] === 'en proceso' ? 'En proceso' : 'Pendiente');
                            ?>
                            <div class="goal-item">
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                    <h3 class="is-size-6"><?php echo htmlspecialchars($meta['descripcion']); ?></h3>
                                    <span class="status-badge status-<?php echo $estado; ?>"><?php echo $estado_texto; ?></span>
                                </div>
                                <div class="progress-bar mb-2">
                                    <div class="progress-bar-fill" style="width: <?php echo $meta['cumplida'] ? '100' : '0'; ?>%"></div>
                                </div>
                                <p class="is-size-7 has-text-grey">
                                    Objetivo: <?php echo htmlspecialchars($meta['objetivo']); ?> | 
                                    Frecuencia: <?php echo $meta['frec_meta']; ?> veces por <?php echo $meta['periodo']; ?>
                                </p>
                              
                                <?php if ($meta['cumplida'] && $meta['fec_fin']): ?>
                                    <p class="completed-date">
                                        Completada el <?php echo date('d/m/Y', strtotime($meta['fec_fin'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="has-text-centered mt-4">
                            <a href="metas/index.php" class="button is-small is-light">
                                Ver todas las metas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Eliminar notificaciones
            document.querySelectorAll('.notification .delete').forEach(deleteButton => {
                deleteButton.addEventListener('click', () => {
                    deleteButton.parentNode.remove();
                });
            });

            // Animar barras de progreso
            const progressBars = document.querySelectorAll('.progress-bar-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html> 