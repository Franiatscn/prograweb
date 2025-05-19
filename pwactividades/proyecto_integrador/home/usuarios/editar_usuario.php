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
        $campos_requeridos = ['id_usuario', 'nombres', 'apellido_p', 'apellido_m', 'email', 'id_rol', 'id_estatus'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }

        // Validar email único (excluyendo el usuario actual)
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ? AND id_usuario != ?");
        $stmt->execute([$_POST['email'], $_POST['id_usuario']]);
        if ($stmt->fetch()) {
            throw new Exception("El email ya está registrado");
        }

        // Actualizar usuario
        $query = "UPDATE usuario SET 
                 nombres = ?, 
                 apellido_p = ?, 
                 apellido_m = ?, 
                 email = ?, 
                 id_rol = ?, 
                 id_estatus = ?";
        
        $params = [
            $_POST['nombres'],
            $_POST['apellido_p'],
            $_POST['apellido_m'],
            $_POST['email'],
            $_POST['id_rol'],
            $_POST['id_estatus']
        ];

        // Si se proporcionó una nueva contraseña, actualizarla
        if (!empty($_POST['password'])) {
            $query .= ", password = ?";
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $query .= " WHERE id_usuario = ?";
        $params[] = $_POST['id_usuario'];

        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        $_SESSION['success'] = "Usuario actualizado exitosamente";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: index.php');
exit(); 