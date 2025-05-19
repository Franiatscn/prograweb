<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /proyecto_integrador/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_meta'])) {
    try {
        // Verificar que la meta pertenece al usuario
        $stmt = $conn->prepare("SELECT id_meta FROM meta WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$_POST['id_meta'], $_SESSION['id_usuario']]);
        if (!$stmt->fetch()) {
            throw new Exception("No tienes permiso para eliminar esta meta");
        }

        // Eliminar meta
        $stmt = $conn->prepare("DELETE FROM meta WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$_POST['id_meta'], $_SESSION['id_usuario']]);

        $_SESSION['success'] = "Meta eliminada exitosamente";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: index.php');
exit(); 