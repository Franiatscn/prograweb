<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/conexion.php';

// Verificar si el usuario ha iniciado sesión y es un usuario normal (id_rol = 2)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 2) {
    header('Location: ../../login.php');
    exit();
}

// Obtener el nombre completo del usuario desde la sesión
$nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellido_p'];

// Obtener categorías y hábitos
$query_categorias = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre";
$stmt_categorias = $conn->prepare($query_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener frecuencias
$query_frecuencias = "SELECT id_frecuencia, periodo FROM frecuencia ORDER BY periodo";
$stmt_frecuencias = $conn->prepare($query_frecuencias);
$stmt_frecuencias->execute();
$frecuencias = $stmt_frecuencias->fetchAll(PDO::FETCH_ASSOC);

// Obtener las metas del usuario
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
    <title>Habitity - Mis Metas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .select select {
            background-color: #fff !important;
            color: #363636 !important;
        }
        .input {
            background-color: #fff !important;
            color: #363636 !important;
        }
        .goal-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        .goal-item:hover {
            background-color: #f9f9f9;
        }
        .goal-item:last-child {
            border-bottom: none;
        }
        .goal-category {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .goal-status {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-tag i {
            font-size: 0.9rem;
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
                    <a href="../../home/index.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                    <a href="../habitos/index.php" class="nav-item"><i class="fas fa-tasks"></i> Mis Hábitos</a>
                    <a href="index.php" class="nav-item active"><i class="fas fa-bullseye"></i> Mis Metas</a>
                    <a href="../../login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="title is-4 mb-5">Mis Metas</h1>

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

            <!-- Formulario de Nueva Meta -->
            <div class="card mb-5">
                <div class="card-header">
                    <h2 class="title is-5">Crear Nueva Meta</h2>
                </div>
                <div class="card-content">
                    <form action="crear_meta.php" method="POST">
                        <div class="field">
                            <label class="label">Categoría</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="categoria" id="categoria" required>
                                        <option value="">Seleccione una categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Hábito</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="habito" id="habito" required disabled>
                                        <option value="">Primero seleccione una categoría</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Descripción</label>
                            <div class="control">
                                <input class="input" type="text" name="descripcion" required 
                                       placeholder="Describe tu meta">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Objetivo</label>
                            <div class="control">
                                <input class="input" type="text" name="objetivo" required 
                                       placeholder="¿Qué quieres lograr?">
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Frecuencia</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="frec_meta" required>
                                                <option value="">Seleccione una frecuencia</option>
                                                <?php for ($i = 1; $i <= 30; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Periodo</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="periodo" required>
                                                <option value="">Seleccione un periodo</option>
                                                <?php foreach ($frecuencias as $frecuencia): ?>
                                                    <option value="<?php echo $frecuencia['periodo']; ?>">
                                                        <?php echo ucfirst($frecuencia['periodo']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <button class="button is-primary" type="submit">Guardar Meta</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Metas -->
            <div class="card">
                <div class="card-header">
                    <h2 class="title is-5">Mis Metas Actuales</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($metas)): ?>
                        <div class="has-text-centered py-5">
                            <p class="has-text-grey">No tienes metas registradas aún.</p>
                            <p class="has-text-grey is-size-7">Crea una nueva meta usando el formulario de arriba.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($metas as $meta): ?>
                            <?php
                                $estado = $meta['cumplida'] ? 'completada' : ($meta['estado'] === 'en proceso' ? 'en proceso' : 'pendiente');
                                $estado_texto = $meta['cumplida'] ? 'Completada' : ($meta['estado'] === 'en proceso' ? 'En proceso' : 'Pendiente');
                                $estado_tag = $meta['cumplida'] ? 'is-success' : ($meta['estado'] === 'en proceso' ? 'is-warning' : 'is-light');
                                $estado_icon = $meta['cumplida'] ? 'fa-check-circle' : ($meta['estado'] === 'en proceso' ? 'fa-spinner' : 'fa-clock');
                            ?>
                            <div class="goal-item">
                                <div class="goal-category">
                                    <span class="tag is-light"><?php echo htmlspecialchars($meta['categoria_nombre']); ?></span>
                                </div>
                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                    <h3 class="is-size-6"><?php echo htmlspecialchars($meta['nombre_habito']); ?></h3>
                                    <div class="buttons">
                                        <div class="select is-small">
                                            <select class="update-goal-state" data-id="<?php echo $meta['id_meta']; ?>">
                                                <option value="pendiente" <?php echo $estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="en proceso" <?php echo $estado === 'en proceso' ? 'selected' : ''; ?>>En proceso</option>
                                                <option value="completada" <?php echo $estado === 'completada' ? 'selected' : ''; ?>>Completada</option>
                                            </select>
                                        </div>
                                        <button class="button is-small is-info edit-goal" 
                                                data-id="<?php echo $meta['id_meta']; ?>"
                                                data-descripcion="<?php echo htmlspecialchars($meta['descripcion']); ?>"
                                                data-objetivo="<?php echo htmlspecialchars($meta['objetivo']); ?>"
                                                data-frec-meta="<?php echo $meta['frec_meta']; ?>"
                                                data-periodo="<?php echo $meta['periodo']; ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <form action="eliminar_meta.php" method="POST" class="is-inline" 
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta meta?');">
                                            <input type="hidden" name="id_meta" value="<?php echo $meta['id_meta']; ?>">
                                            <button type="submit" class="button is-small is-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="is-size-7"><?php echo htmlspecialchars($meta['descripcion']); ?></p>
                                <p class="is-size-7">Objetivo: <?php echo htmlspecialchars($meta['objetivo']); ?></p>
                                <div class="goal-status">
                                    <span class="tag <?php echo $estado_tag; ?> status-tag">
                                        <i class="fas <?php echo $estado_icon; ?>"></i>
                                        <?php echo $estado_texto; ?>
                                    </span>
                                    <span class="is-size-7 has-text-grey ml-2">
                                        <?php echo $meta['frec_meta'] . ' veces por ' . $meta['periodo']; ?>
                                    </span>
                                </div>
                                <?php if ($meta['fec_fin']): ?>
                                    <p class="is-size-7 has-text-grey mt-2">
                                        Fecha límite: <?php echo date('d/m/Y', strtotime($meta['fec_fin'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Edición -->
    <div class="modal" id="editModal">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Editar Meta</p>
                <button class="delete" aria-label="close"></button>
            </header>
            <form action="editar_meta.php" method="POST">
                <section class="modal-card-body">
                    <input type="hidden" name="id_meta" id="edit_id_meta">
                    
                    <div class="field">
                        <label class="label">Descripción</label>
                        <div class="control">
                            <input class="input" type="text" name="descripcion" id="edit_descripcion" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Objetivo</label>
                        <div class="control">
                            <input class="input" type="text" name="objetivo" id="edit_objetivo" required>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label">Frecuencia</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="frec_meta" id="edit_frec_meta" required>
                                            <?php for ($i = 1; $i <= 30; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Periodo</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="periodo" id="edit_periodo" required>
                                            <?php foreach ($frecuencias as $frecuencia): ?>
                                                <option value="<?php echo $frecuencia['periodo']; ?>">
                                                    <?php echo ucfirst($frecuencia['periodo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button type="submit" class="button is-success">Guardar cambios</button>
                    <button type="button" class="button cancel-edit">Cancelar</button>
                </footer>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Eliminar notificaciones
            document.querySelectorAll('.notification .delete').forEach(deleteButton => {
                deleteButton.addEventListener('click', () => {
                    deleteButton.parentNode.remove();
                });
            });

            // Dropdown de hábitos dinámico
            const categoriaSelect = document.getElementById('categoria');
            const habitoSelect = document.getElementById('habito');

            categoriaSelect.addEventListener('change', function() {
                const categoriaId = this.value;
                
                if (categoriaId) {
                    habitoSelect.disabled = false;
                    
                    fetch(`../habitos/get_habitos.php?categoria_id=${categoriaId}`)
                        .then(response => response.json())
                        .then(habitos => {
                            habitoSelect.innerHTML = '<option value="">Seleccione un hábito</option>';
                            
                            habitos.forEach(habito => {
                                const option = document.createElement('option');
                                option.value = habito.id_habito;
                                option.textContent = habito.nombre;
                                habitoSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            habitoSelect.innerHTML = '<option value="">Error al cargar hábitos</option>';
                        });
                } else {
                    habitoSelect.disabled = true;
                    habitoSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
                }
            });

            // Funcionalidad del modal de edición
            const editModal = document.getElementById('editModal');
            const editButtons = document.querySelectorAll('.edit-goal');
            const closeButtons = editModal.querySelectorAll('.delete, .cancel-edit');

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    const descripcion = button.dataset.descripcion;
                    const objetivo = button.dataset.objetivo;
                    const frecMeta = button.dataset.frecMeta;
                    const periodo = button.dataset.periodo;

                    document.getElementById('edit_id_meta').value = id;
                    document.getElementById('edit_descripcion').value = descripcion;
                    document.getElementById('edit_objetivo').value = objetivo;
                    document.getElementById('edit_frec_meta').value = frecMeta;
                    document.getElementById('edit_periodo').value = periodo;

                    editModal.classList.add('is-active');
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    editModal.classList.remove('is-active');
                });
            });

            // Cerrar modal al hacer clic en el fondo
            editModal.querySelector('.modal-background').addEventListener('click', () => {
                editModal.classList.remove('is-active');
            });

            // Actualizar estado de meta
            document.querySelectorAll('.update-goal-state').forEach(select => {
                select.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const estado = this.value;
                    const goalItem = this.closest('.goal-item');
                    const statusTag = goalItem.querySelector('.status-tag');
                    
                    // Deshabilitar el select mientras se procesa
                    this.disabled = true;
                    
                    fetch('actualizar_estado.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_meta=${id}&estado=${estado}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.text();
                    })
                    .then(() => {
                        // Actualizar la interfaz
                        let iconClass, tagClass;
                        switch(estado) {
                            case 'completada':
                                iconClass = 'fa-check-circle';
                                tagClass = 'is-success';
                                break;
                            case 'en proceso':
                                iconClass = 'fa-spinner';
                                tagClass = 'is-warning';
                                break;
                            default:
                                iconClass = 'fa-clock';
                                tagClass = 'is-light';
                        }
                        
                        statusTag.innerHTML = `<i class="fas ${iconClass}"></i> ${estado.charAt(0).toUpperCase() + estado.slice(1)}`;
                        statusTag.classList.remove('is-success', 'is-warning', 'is-light');
                        statusTag.classList.add(tagClass);
                        
                        // Recargar la página después de un breve retraso
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al actualizar la meta');
                        // Rehabilitar el select en caso de error
                        this.disabled = false;
                    });
                });
            });
        });
    </script>
</body>
</html> 