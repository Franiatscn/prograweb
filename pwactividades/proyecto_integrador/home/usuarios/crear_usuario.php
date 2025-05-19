<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos requeridos
        $campos_requeridos = ['nombres', 'apellido_p', 'apellido_m', 'email', 'password', 'id_rol', 'id_estatus'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }

        // Validar email único
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            throw new Exception("El email ya está registrado");
        }

        // Crear usuario
        $query = "INSERT INTO usuario (nombres, apellido_p, apellido_m, email, password, id_rol, id_estatus) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $_POST['nombres'],
            $_POST['apellido_p'],
            $_POST['apellido_m'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id_rol'],
            $_POST['id_estatus']
        ]);

        $_SESSION['success'] = "Usuario creado exitosamente";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: index.php');
exit(); 