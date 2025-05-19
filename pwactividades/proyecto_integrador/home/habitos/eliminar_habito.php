<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'No autorizado';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_registro'])) {
    try {
        // Verificar que el hábito pertenece al usuario
        $query = "SELECT id_registro FROM registro_habito 
                 WHERE id_registro = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$_POST['id_registro'], $_SESSION['id_usuario']]);
        
        if (!$stmt->fetch()) {
            throw new Exception('No tienes permiso para eliminar este hábito');
        }

        // Eliminar el hábito
        $query = "DELETE FROM registro_habito 
                 WHERE id_registro = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$_POST['id_registro'], $_SESSION['id_usuario']]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Hábito eliminado correctamente';
        } else {
            throw new Exception('No se pudo eliminar el hábito');
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Solicitud inválida';
}

header('Location: index.php');
exit(); 