<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Incluir la conexión a la base de datos
    require_once '../../includes/conexion.php';

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 2) {
        header('Location: ../../login.php');
        exit();
    }

    // Obtener el nombre completo del usuario desde la sesión
    $nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellido_p'];

    // Obtener categorías mediante una consulta SQL preparada y ejecutada
    $query_categorias = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre";
    $stmt_categorias = $conn->prepare($query_categorias);
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // Obtener frecuencias mediante una consulta SQL preparada y ejecutada
    $query_frecuencias = "SELECT id_frecuencia, periodo FROM frecuencia ORDER BY periodo";
    $stmt_frecuencias = $conn->prepare($query_frecuencias);
    $stmt_frecuencias->execute();
    $frecuencias = $stmt_frecuencias->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los hábitos del usuario con su progreso
    $query_habitos = "SELECT 
                        rh.id_registro,
                        h.nombre as nombre_habito,
                        h.descripcion,
                        rh.objetivo,
                        f.periodo,
                        rh.id_frecuencia,
                        rh.progreso,
                        rh.completado,
                        c.nombre as categoria_nombre
                     FROM registro_habito rh
                     JOIN habito h ON rh.id_habito = h.id_habito
                     JOIN frecuencia f ON rh.id_frecuencia = f.id_frecuencia
                     JOIN categoria c ON h.id_categoria = c.id_categoria
                     WHERE rh.id_usuario = ? 
                     AND rh.fec_fin IS NULL
                     ORDER BY rh.fec_inicio DESC";
    
    try {
        $stmt_habitos = $conn->prepare($query_habitos);
        $stmt_habitos->execute([$_SESSION['id_usuario']]);
        $habitos_progreso = $stmt_habitos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Error fetching habits: " . $e->getMessage());
        $habitos_progreso = [];
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitity - Mis Hábitos</title>
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
        .progress {
            height: 8px;
            margin: 0.5rem 0;
        }
        .progress::-webkit-progress-value {
            transition: width 0.3s ease;
        }
        .habit-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .habit-item:last-child {
            border-bottom: none;
        }
        .habit-category {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
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
                <a href="../index.php" class="nav-item active"><i class="fas fa-tasks"></i> Mis Hábitos</a>
                <a href="../metas/index.php" class="nav-item"><i class="fas fa-bullseye"></i> Mis Metas</a>
                <a href="../../login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="title is-4 mb-5">Gestión de Hábitos</h1>

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

        <!-- Agregar Nuevo Hábito -->
        <div class="card mb-5">
            <div class="card-header">
                <h2 class="title is-5">Agregar Nuevo Hábito</h2>
            </div>
            <div class="card-content">
                <form action="procesar_habito.php" method="POST">
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
                        <label class="label">Frecuencia</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="frecuencia" id="frecuencia" required>
                                    <option value="">Seleccione una frecuencia</option>
                                    <?php foreach ($frecuencias as $frecuencia): ?>
                                        <option value="<?php echo $frecuencia['id_frecuencia']; ?>">
                                            <?php echo ucfirst($frecuencia['periodo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Objetivo</label>
                        <div class="control">
                            <input class="input" type="number" name="objetivo" min="1" required 
                                   placeholder="¿Cuántas veces quieres realizar este hábito?">
                        </div>
                        <p class="help">Ingresa el número de veces que deseas realizar este hábito</p>
                    </div>

                    <div class="field">
                        <button class="button is-primary" type="submit">Guardar Hábito</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Hábitos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title is-5">Mis Hábitos Actuales</h2>
            </div>
            <div class="card-content">
                <?php if (empty($habitos_progreso)): ?>
                    <div class="has-text-centered py-5">
                        <p class="has-text-grey">No tienes hábitos registrados aún.</p>
                        <p class="has-text-grey is-size-7">Agrega un nuevo hábito usando el formulario de arriba.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($habitos_progreso as $habito): ?>
                        <?php
                            $nombre = htmlspecialchars($habito['nombre_habito']);
                            $categoria = htmlspecialchars($habito['categoria_nombre']);
                            $objetivo = $habito['objetivo'];
                            $completado = $habito['progreso'];
                            $periodo = $habito['periodo'];
                            $porcentaje = ($objetivo > 0) ? min(100, round(($completado / $objetivo) * 100)) : 0;
                            $is_completed = $habito['completado'];
                        ?>
                        <div class="habit-item">
                            <div class="habit-category">
                                <span class="tag is-light"><?php echo $categoria; ?></span>
                            </div>
                            <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                <h3 class="is-size-6"><?php echo $nombre; ?></h3>
                                <div class="buttons">
                                    <button class="button is-small is-success update-progress" 
                                            data-id="<?php echo $habito['id_registro']; ?>"
                                            <?php echo $is_completed ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check"></i> Completado
                                    </button>
                                    <button class="button is-small is-info edit-habit" 
                                            data-id="<?php echo $habito['id_registro']; ?>"
                                            data-frecuencia="<?php echo $habito['id_frecuencia']; ?>"
                                            data-objetivo="<?php echo $habito['objetivo']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <form action="eliminar_habito.php" method="POST" class="is-inline" 
                                          onsubmit="return confirm('¿Estás seguro de que deseas eliminar este hábito?');">
                                        <input type="hidden" name="id_registro" value="<?php echo $habito['id_registro']; ?>">
                                        <button type="submit" class="button is-small is-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <progress class="progress is-warning" value="<?php echo $completado; ?>" max="<?php echo $objetivo; ?>"></progress>
                            <p class="is-size-7 has-text-grey"><?php echo "$completado/$objetivo veces por $periodo"; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de Hábitos -->
        <div class="card mt-5">
            <div class="card-header">
                <h2 class="title is-5">Historial</h2>
            </div>
            <div class="card-content">
                <?php
                // Obtener el periodo seleccionado (por defecto: todos)
                $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'todos';

                // Construir la consulta base
                $query_historial = "SELECT 
                                    rh.id_registro,
                                    h.nombre as nombre_habito,
                                    c.nombre as categoria_nombre,
                                    f.periodo,
                                    rh.progreso,
                                    rh.objetivo,
                                    rh.fec_inicio,
                                    rh.fec_fin
                                  FROM registro_habito rh
                                  JOIN habito h ON rh.id_habito = h.id_habito
                                  JOIN categoria c ON h.id_categoria = c.id_categoria
                                  JOIN frecuencia f ON rh.id_frecuencia = f.id_frecuencia
                                  WHERE rh.id_usuario = ? 
                                  AND rh.fec_fin IS NOT NULL";

                // Agregar filtro de periodo si no es 'todos'
                if ($periodo !== 'todos') {
                    $query_historial .= " AND f.periodo = ?";
                }

                $query_historial .= " ORDER BY rh.fec_fin DESC";

                try {
                    $stmt_historial = $conn->prepare($query_historial);
                    
                    if ($periodo !== 'todos') {
                        $stmt_historial->execute([$_SESSION['id_usuario'], $periodo]);
                    } else {
                        $stmt_historial->execute([$_SESSION['id_usuario']]);
                    }
                    
                    $habitos_completados = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    error_log("Error fetching completed habits: " . $e->getMessage());
                    $habitos_completados = [];
                }
                ?>

                <div class="history-filters">
                    <div class="buttons has-addons is-centered">
                        <a href="?periodo=todos" class="button <?php echo $periodo === 'todos' ? 'is-active' : 'is-light'; ?>">
                            Todos
                        </a>
                        <a href="?periodo=día" class="button <?php echo $periodo === 'día' ? 'is-active' : 'is-light'; ?>">
                            Diarios
                        </a>
                        <a href="?periodo=semana" class="button <?php echo $periodo === 'semana' ? 'is-active' : 'is-light'; ?>">
                            Semanales
                        </a>
                        <a href="?periodo=mes" class="button <?php echo $periodo === 'mes' ? 'is-active' : 'is-light'; ?>">
                            Mensuales
                        </a>
                    </div>
                </div>

                <?php if (empty($habitos_completados)): ?>
                    <div class="empty-history">
                        <i class="fas fa-history"></i>
                        <p class="title is-5">No hay hábitos completados</p>
                        <p class="subtitle">Aún no has completado ningún hábito en este periodo.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($habitos_completados as $habito): ?>
                        <div class="habit-history-item">
                            <div class="habit-history-header">
                                <div>
                                    <h3 class="habit-history-title"><?php echo htmlspecialchars($habito['nombre_habito']); ?></h3>
                                    <div class="habit-history-category">
                                        <span class="tag is-light"><?php echo htmlspecialchars($habito['categoria_nombre']); ?></span>
                                    </div>
                                </div>
                                <span class="tag is-success">Completado</span>
                            </div>
                            
                            <div class="habit-history-details">
                                <div class="habit-history-stats">
                                    <p>Frecuencia: <?php echo ucfirst($habito['periodo']); ?></p>
                                </div>
                                
                                <div class="habit-history-progress">
                                    <progress class="progress is-success" 
                                             value="<?php echo $habito['progreso']; ?>" 
                                             max="<?php echo $habito['objetivo']; ?>">
                                    </progress>
                                </div>
                                
                                <div class="habit-history-stats">
                                    <p><?php echo $habito['progreso']; ?>/<?php echo $habito['objetivo']; ?> veces</p>
                                </div>
                            </div>
                            
                            <div class="habit-history-date">
                                <p>Completado el: <?php echo $habito['fec_fin'] ? date('d/m/Y', strtotime($habito['fec_fin'])) : 'Pendiente'; ?></p>
                            </div>
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
            <p class="modal-card-title">Editar Hábito</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <form action="editar_habito.php" method="POST">
            <section class="modal-card-body">
                <input type="hidden" name="id_registro" id="edit_id_registro">
                
                <div class="field">
                    <label class="label">Frecuencia</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="id_frecuencia" id="edit_frecuencia" required>
                                <?php foreach ($frecuencias as $frecuencia): ?>
                                    <option value="<?php echo $frecuencia['id_frecuencia']; ?>">
                                        <?php echo ucfirst($frecuencia['periodo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Objetivo</label>
                    <div class="control">
                        <input class="input" type="number" name="objetivo" id="edit_objetivo" 
                               min="1" required placeholder="¿Cuántas veces quieres realizar este hábito?">
                    </div>
                    <p class="help">Ingresa el número de veces que deseas realizar este hábito</p>
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

        // Barra de progreso   
        const progressBars = document.querySelectorAll('.progress');
        progressBars.forEach(bar => {
            const width = bar.value;
            bar.value = 0;
            setTimeout(() => {
                bar.value = width;
            }, 100);
        });

        // Actualizar progreso
        document.querySelectorAll('.update-progress').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const habitItem = this.closest('.habit-item');
                const progressBar = habitItem.querySelector('.progress');
                const progressText = habitItem.querySelector('.is-size-7');
                
                fetch('actualizar_progreso.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_registro=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    if (data.success) {
                        progressBar.value = data.progreso;
                        const [current, max] = progressText.textContent.split('/');
                        progressText.textContent = `${data.progreso}/${max}`;
                        
                        if (data.completado) {
                            this.disabled = true;
                            this.classList.add('is-static');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el progreso');
                });
            });
        });

        // Dropdown de hábitos dinámico
        const categoriaSelect = document.getElementById('categoria');
        const habitoSelect = document.getElementById('habito');

        categoriaSelect.addEventListener('change', function() {
            const categoriaId = this.value;
            
            if (categoriaId) {
                // Habilita el dropdown de hábitos
                habitoSelect.disabled = false;
                
                // Obtiene los hábitos para la categoría seleccionada
                fetch(`get_habitos.php?categoria_id=${categoriaId}`)
                    .then(response => response.json())
                    .then(habitos => {
                        // Limpia las opciones actuales
                        habitoSelect.innerHTML = '<option value="">Seleccione un hábito</option>';
                        
                        // Agrega las nuevas opciones
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
                // Deshabilita y limpia el dropdown de hábitos
                habitoSelect.disabled = true;
                habitoSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
            }
        });

        // Funcionalidad del modal de edición
        const editModal = document.getElementById('editModal');
        const editButtons = document.querySelectorAll('.edit-habit');
        const closeButtons = editModal.querySelectorAll('.delete, .cancel-edit');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const frecuencia = button.dataset.frecuencia;
                const objetivo = button.dataset.objetivo;

                document.getElementById('edit_id_registro').value = id;
                document.getElementById('edit_frecuencia').value = frecuencia;
                document.getElementById('edit_objetivo').value = objetivo;

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
    });
</script>
</body>
</html>