<?php
// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../../login.php');
    exit();
}
?>
<aside class="sidebar">
    <div class="user-profile">
        <div class="user-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <h3 class="has-text-white">
            <span><?php echo htmlspecialchars($_SESSION['nombres'] . ' ' . $_SESSION['apellido_p']); ?></span>
        </h3>
        <span class="tag is-info is-light mt-2">Administrador</span>
    </div>

    <nav>
        <div class="nav-group">
            <a href="../usuarios/index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Gestión de Usuarios
            </a>
            <a href="../../logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </nav>
</aside> 