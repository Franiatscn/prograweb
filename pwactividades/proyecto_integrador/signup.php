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
    <title>Habitity - Registro</title>
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

        <div class="form-container">
            <div class="logo-container">
                <h1 class="title has-text-white">
                    Habitity
                    <img src="assets/imgs/logo.png" alt="logo" class="logo-img">
                </h1>
            </div>
            <h2 class="subtitle has-text-centered has-text-white mb-4">Crear Cuenta</h2>
            
            <!-- Formulario de registro de usuario -->
            <form id="signupForm" method="POST" action="includes/registrar.php">
                <!-- Campo: Nombres -->
                <div class="field">
                    <div class="control">
                        <input class="input" type="text" name="nombres" placeholder="Nombres" required>
                    </div>
                </div>

                <!-- Campo: Apellido Paterno -->
                <div class="field">
                    <div class="control">
                        <input class="input" type="text" name="apellido_p" placeholder="Apellido Paterno" required>
                    </div>
                </div>

                <!-- Campo: Apellido Materno -->
                <div class="field">
                    <div class="control">
                        <input class="input" type="text" name="apellido_m" placeholder="Apellido Materno" required>
                    </div>
                </div>

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

                <!-- Campo: Confirmar Contraseña -->
                <div class="field">
                    <div class="control password-field">
                        <input class="input" type="password" name="confirm_password" id="confirm_password" placeholder="Confirmar contraseña" required>
                        <!-- Botón para mostrar/ocultar confirmación de contraseña -->
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Botón de Registro -->
                <div class="buttons-container">
                    <button type="submit" class="button login-btn">Registrarse</button>
                </div>

                <!-- Enlace para ir a la página de login -->
                <div class="links-container">
                    <p class="link-text">
                        ¿Ya tienes una cuenta? 
                        <a href="login.php" class="link">Iniciar sesión</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Permite cerrar notificaciones de error haciendo clic en la X
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

            // Validación en el cliente: las contraseñas deben coincidir antes de enviar el formulario
            const form = document.getElementById('signupForm');
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    e.preventDefault(); // Evita el envío del formulario
                    alert('Las contraseñas no coinciden');
                }
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