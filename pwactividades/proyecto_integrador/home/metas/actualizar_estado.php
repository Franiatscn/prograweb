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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_meta']) && isset($_POST['estado'])) {
    try {
        // Verificar que la meta pertenece al usuario
        $stmt = $conn->prepare("SELECT id_meta, estado, cumplida FROM meta WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$_POST['id_meta'], $_SESSION['id_usuario']]);
        $meta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$meta) {
            throw new Exception("No tienes permiso para actualizar esta meta");
        }

        // Validar estado
        $estados_permitidos = ['pendiente', 'en proceso', 'completada'];
        if (!in_array($_POST['estado'], $estados_permitidos)) {
            throw new Exception("Estado no válido");
        }

        // Preparar la actualización según el estado
        if ($_POST['estado'] === 'completada') {
            $stmt = $conn->prepare("UPDATE meta SET 
                estado = ?, 
                cumplida = 1,
                fec_fin = CURRENT_TIMESTAMP 
                WHERE id_meta = ? AND id_usuario = ?");
        } else {
            // Para estados pendiente o en proceso, resetear los campos de completado
            $stmt = $conn->prepare("UPDATE meta SET 
                estado = ?,
                cumplida = 0,
                fec_fin = NULL 
                WHERE id_meta = ? AND id_usuario = ?");
        }
        
        $result = $stmt->execute([$_POST['estado'], $_POST['id_meta'], $_SESSION['id_usuario']]);

        if ($result) {
            $_SESSION['success'] = "Estado de la meta actualizado exitosamente";
        } else {
            throw new Exception("Error al actualizar el estado de la meta");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Redirigir de vuelta a la página de metas
header('Location: index.php');
exit(); 