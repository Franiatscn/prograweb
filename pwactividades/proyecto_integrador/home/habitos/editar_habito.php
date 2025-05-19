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
        // Validar datos
        if (empty($_POST['id_registro']) || empty($_POST['id_frecuencia']) || empty($_POST['objetivo'])) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Verificar que el registro pertenece al usuario
        $stmt = $conn->prepare("SELECT id_registro FROM registro_habito WHERE id_registro = ? AND id_usuario = ?");
        $stmt->execute([$_POST['id_registro'], $_SESSION['id_usuario']]);
        
        if (!$stmt->fetch()) {
            throw new Exception("No tienes permiso para editar este hábito");
        }

        // Actualizar el registro del hábito
        $stmt = $conn->prepare("UPDATE registro_habito SET id_frecuencia = ?, objetivo = ? WHERE id_registro = ? AND id_usuario = ?");
        $result = $stmt->execute([
            $_POST['id_frecuencia'],
            $_POST['objetivo'],
            $_POST['id_registro'],
            $_SESSION['id_usuario']
        ]);

        if ($result) {
            $_SESSION['success'] = "Hábito actualizado exitosamente";
        } else {
            throw new Exception("Error al actualizar el hábito");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Redirigir de vuelta a la página de hábitos
header('Location: index.php');
exit(); 