<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar que la petición sea POST (envío del formulario de login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Por favor, complete todos los campos";
        header('Location: /proyecto_integrador/login.php');
        exit();
    }

    try {
        // Buscar el usuario por email
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no existe el usuario, mostrar error
        if (!$usuario) {
            $_SESSION['error'] = "El correo electrónico no está registrado";
            header('Location: /proyecto_integrador/login.php');
            exit();
        }

        // Verificar la contraseña
        if (!password_verify($password, $usuario['password'])) {
            $_SESSION['error'] = "La contraseña es incorrecta";
            header('Location: /proyecto_integrador/login.php');
            exit();
        }

        // Si todo es correcto, guardar datos de sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombres'] = $usuario['nombres'];
        $_SESSION['apellido_p'] = $usuario['apellido_p'];
        $_SESSION['id_rol'] = $usuario['id_rol'];

        // Redirigir según el rol del usuario
        if ($usuario['id_rol'] == 1) {
            header('Location: /proyecto_integrador/home/usuarios/admin_dashboard.php');
        } else {
            header('Location: /proyecto_integrador/home/index.php');
        }
        exit();
    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        error_log("Error en login: " . $e->getMessage());
        $_SESSION['error'] = "Error al intentar iniciar sesión. Por favor, intente nuevamente.";
        header('Location: /proyecto_integrador/login.php');
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a este archivo, redirigir al login
    header('Location: /proyecto_integrador/login.php');
    exit();
}