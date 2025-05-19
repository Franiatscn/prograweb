<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Incluir el archivo de conexión a la base de datos
require_once 'includes/conexion.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitity - Login</title>
    <!-- Bulma CSS Framework para estilos base -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personalizado global -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">
    <div class="container">
        <?php // Mostrar notificación de error si existe en la sesión ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification is-danger is-light" style="margin: 1rem auto; max-width: 400px; position: relative; z-index: 1000;">
                <button class="delete"></button>
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>

        <?php // Mostrar notificación de éxito si existe en la sesión ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="notification is-success is-light" style="margin: 1rem auto; max-width: 400px; position: relative; z-index: 1000;">
                <button class="delete"></button>
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="logo-container">
                <h1 class="title has-text-white">
                    Habitity
                    <img src="assets/imgs/logo.png" alt="logo" class="logo-img">
                </h1>
            </div>
            <h2 class="subtitle has-text-centered has-text-white mb-4">Iniciar Sesión</h2>
            
            <!-- Formulario de inicio de sesión -->
            <form id="loginForm" method="POST" action="includes/validar.php" >
                <!-- Campo: Email -->
                <div class="field">
                    <div class="control">
                        <input class="input" type="email" name="email" placeholder="Correo electrónico" required> 
                    </div>
                </div>

                <!-- Campo: Contraseña -->
                <div class="field">
                    <div class="control password-field">
                        <input class="input" type="password" name="password" id="password" placeholder="Contraseña" required>
                        <!-- Botón para mostrar/ocultar contraseña -->
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Botón de Inicio de Sesión -->
                <div class="buttons-container">
                    <button type="submit" class="button login-btn">Iniciar sesión</button>
                </div>

                <!-- Enlace para ir a la página de registro -->
                <div class="links-container">
                    <p class="link-text">
                        ¿No tienes una cuenta Habitity? 
                        <a href="signup.php" class="link">Regístrate</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript para la funcionalidad de mostrar/ocultar contraseña y notificaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Permite cerrar notificaciones haciendo clic en la X
            document.querySelectorAll('.notification .delete').forEach(deleteButton => {
                deleteButton.addEventListener('click', () => {
                    deleteButton.parentNode.remove();
                });
            });

            // Auto-elimina notificaciones después de 5 segundos
            document.querySelectorAll('.notification').forEach(notification => {
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            });
        });

        // Función para mostrar/ocultar la contraseña
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = passwordInput.nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
