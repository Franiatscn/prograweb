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
        if (empty($_POST['habito']) || empty($_POST['descripcion']) || empty($_POST['objetivo']) || 
            empty($_POST['frec_meta']) || empty($_POST['periodo'])) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Insertar la meta
        $stmt = $conn->prepare("INSERT INTO meta (id_usuario, id_habito, descripcion, objetivo, frec_meta, periodo, estado, fec_inicio) 
                               VALUES (?, ?, ?, ?, ?, ?, 'pendiente', CURRENT_TIMESTAMP)");
        
        $result = $stmt->execute([
            $_SESSION['id_usuario'],
            $_POST['habito'],
            $_POST['descripcion'],
            $_POST['objetivo'],
            $_POST['frec_meta'],
            $_POST['periodo']
        ]);

        if ($result) {
            $_SESSION['success'] = "Meta creada exitosamente";
        } else {
            throw new Exception("Error al crear la meta");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Redirigir de vuelta a la página de metas
header('Location: index.php');
exit(); 