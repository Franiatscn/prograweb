<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar que la petición sea POST (envío del formulario de registro)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar que todos los campos requeridos estén presentes
        if (empty($_POST['nombres']) || empty($_POST['apellido_p']) || empty($_POST['apellido_m']) || 
            empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Validar que las contraseñas coincidan
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Las contraseñas no coinciden");
        }

        // Validar que el email no esté ya registrado
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            throw new Exception("Este correo electrónico ya está registrado");
        }

        // Hash de la contraseña para seguridad
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Insertar el nuevo usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuario (nombres, apellido_p, apellido_m, email, password, id_rol, id_estatus, fec_creacion) 
                               VALUES (?, ?, ?, ?, ?, 2, 1, CURRENT_TIMESTAMP)");
        
        $result = $stmt->execute([
            $_POST['nombres'],
            $_POST['apellido_p'],
            $_POST['apellido_m'],
            $_POST['email'],
            $password_hash
        ]);

        // Si el registro fue exitoso, redirigir al login con mensaje de éxito
        if ($result) {
            $_SESSION['success'] = "Registro exitoso. Por favor, inicia sesión.";
            header('Location: /proyecto_integrador/login.php');
            exit();
        } else {
            throw new Exception("Error al registrar el usuario");
        }
    } catch (Exception $e) {
        // Manejo de errores y redirección al registro
        $_SESSION['error'] = $e->getMessage();
        header('Location: /proyecto_integrador/signup.php');
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a este archivo, redirigir al registro
    header('Location: /proyecto_integrador/signup.php');
    exit();
} 