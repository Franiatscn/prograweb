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
        if (!isset($_POST['id_usuario'])) {
            throw new Exception("ID de usuario no proporcionado");
        }

        // No permitir eliminar el propio usuario
        if ($_POST['id_usuario'] == $_SESSION['id_usuario']) {
            throw new Exception("No puedes eliminar tu propia cuenta");
        }

        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$_POST['id_usuario']]);

        $_SESSION['success'] = "Usuario eliminado exitosamente";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: index.php');
exit(); 