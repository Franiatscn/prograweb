<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    // Si no es admin, redirigir al login
    header('Location: ../../login.php');
    exit();
}

// Obtener roles y estatus para los selectores del formulario
$roles = $conn->query("SELECT * FROM rol ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$estatus = $conn->query("SELECT * FROM estatus_usuario ORDER BY descripcion")->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de usuarios con su rol y estatus
$query = "SELECT u.*, r.nombre as rol_nombre, e.descripcion as estatus_descripcion 
          FROM usuario u 
          JOIN rol r ON u.id_rol = r.id_rol 
          JOIN estatus_usuario e ON u.id_estatus = e.id_estatus 
          ORDER BY u.fec_creacion DESC";
$usuarios = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitity - Gestión de Usuarios</title>
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

        <!-- Contenido principal de gestión de usuarios -->
        <main class="main-content">
            <h1 class="title is-4 mb-5">Gestión de Usuarios</h1>

            <?php // Mostrar notificaciones de error o éxito si existen en la sesión ?>
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

            <!-- Formulario para crear o editar usuarios -->
            <div class="card mb-5">
                <div class="card-header">
                    <h2 class="title is-5" id="formTitle">Crear Nuevo Usuario</h2>
                </div>
                <div class="card-content">
                    <form id="userForm" action="crear_usuario.php" method="POST">
                        <input type="hidden" name="id_usuario" id="id_usuario">

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Nombres</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombres" id="nombres" required>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Apellido Paterno</label>
                                    <div class="control">
                                        <input class="input" type="text" name="apellido_p" id="apellido_p" required>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Apellido Materno</label>
                                    <div class="control">
                                        <input class="input" type="text" name="apellido_m" id="apellido_m" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Email</label>
                                    <div class="control">
                                        <input class="input" type="email" name="email" id="email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Contraseña</label>
                                    <div class="control">
                                        <input class="input" type="password" name="password" id="password">
                                    </div>
                                    <p class="help" id="passwordHelp">Obligatoria para nuevos usuarios</p>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Rol</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="id_rol" id="id_rol" required>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?php echo $rol['id_rol']; ?>">
                                                        <?php echo htmlspecialchars($rol['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Estatus</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="id_estatus" id="id_estatus" required>
                                                <?php foreach ($estatus as $est): ?>
                                                    <option value="<?php echo $est['id_estatus']; ?>">
                                                        <?php echo htmlspecialchars($est['descripcion']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">Guardar</button>
                            </div>
                            <div class="control">
                                <button type="button" class="button is-light" id="cancelEdit">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="card">
                <div class="card-header">
                    <h2 class="title is-5">Lista de Usuarios</h2>
                </div>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estatus</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellido_p'] . ' ' . $usuario['apellido_m']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                                        <td>
                                            <span class="tag <?php echo $usuario['id_estatus'] == 1 ? 'is-success' : 'is-danger'; ?>">
                                                <?php echo htmlspecialchars($usuario['estatus_descripcion']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['fec_creacion'])); ?></td>
                                        <td>
                                            <div class="buttons are-small">
                                                <!-- Botón para editar usuario -->
                                                <button class="button is-info edit-user" 
                                                        data-id="<?php echo $usuario['id_usuario']; ?>"
                                                        data-nombres="<?php echo htmlspecialchars($usuario['nombres']); ?>"
                                                        data-apellido-p="<?php echo htmlspecialchars($usuario['apellido_p']); ?>"
                                                        data-apellido-m="<?php echo htmlspecialchars($usuario['apellido_m']); ?>"
                                                        data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                                        data-rol="<?php echo $usuario['id_rol']; ?>"
                                                        data-estatus="<?php echo $usuario['id_estatus']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                    <!-- Botón para eliminar usuario (no permite eliminarse a sí mismo) -->
                                                    <form action="eliminar_usuario.php" method="POST" class="is-inline" 
                                                          onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                                        <button type="submit" class="button is-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Permite cerrar notificaciones haciendo clic en la X
            document.querySelectorAll('.notification .delete').forEach(deleteButton => {
                deleteButton.addEventListener('click', () => {
                    deleteButton.parentNode.remove();
                });
            });

            // Funcionalidad de edición de usuario: llena el formulario con los datos del usuario seleccionado
            const form = document.getElementById('userForm');
            const formTitle = document.getElementById('formTitle');
            const passwordInput = document.getElementById('password');
            const passwordHelp = document.getElementById('passwordHelp');
            const cancelButton = document.getElementById('cancelEdit');

            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', () => {
                    // Cambiar título y acción del formulario
                    formTitle.textContent = 'Editar Usuario';
                    form.action = 'editar_usuario.php';
                    
                    // Llenar el formulario con los datos del usuario
                    document.getElementById('id_usuario').value = button.dataset.id;
                    document.getElementById('nombres').value = button.dataset.nombres;
                    document.getElementById('apellido_p').value = button.dataset.apellidoP;
                    document.getElementById('apellido_m').value = button.dataset.apellidoM;
                    document.getElementById('email').value = button.dataset.email;
                    document.getElementById('id_rol').value = button.dataset.rol;
                    document.getElementById('id_estatus').value = button.dataset.estatus;
                    
                    // Hacer la contraseña opcional
                    passwordInput.removeAttribute('required');
                    passwordHelp.textContent = 'Opcional - Dejar en blanco para mantener la actual';
                    
                    // Scroll al formulario
                    form.scrollIntoView({ behavior: 'smooth' });
                });
            });

            // Botón cancelar edición: limpia el formulario y lo deja en modo "crear"
            cancelButton.addEventListener('click', () => {
                form.reset();
                formTitle.textContent = 'Crear Nuevo Usuario';
                form.action = 'crear_usuario.php';
                document.getElementById('id_usuario').value = '';
                passwordInput.setAttribute('required', '');
                passwordHelp.textContent = 'Obligatoria para nuevos usuarios';
            });
        });
    </script>
</body>
</html> 