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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos requeridos
        $campos_requeridos = ['id_meta', 'descripcion', 'objetivo', 'frec_meta', 'periodo'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }

        // Verificar que la meta pertenece al usuario
        $stmt = $conn->prepare("SELECT id_meta FROM meta WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$_POST['id_meta'], $_SESSION['id_usuario']]);
        if (!$stmt->fetch()) {
            throw new Exception("No tienes permiso para editar esta meta");
        }

        // Actualizar meta
        $query = "UPDATE meta SET 
                 descripcion = ?,
                 objetivo = ?,
                 frec_meta = ?,
                 periodo = ?
                 WHERE id_meta = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $_POST['descripcion'],
            $_POST['objetivo'],
            $_POST['frec_meta'],
            $_POST['periodo'],
            $_POST['id_meta'],
            $_SESSION['id_usuario']
        ]);

        $_SESSION['success'] = "Meta actualizada exitosamente";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: index.php');
exit(); 