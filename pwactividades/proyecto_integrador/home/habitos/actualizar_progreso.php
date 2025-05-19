<?php
session_start();
require_once '../../includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_registro = $_POST['id_registro'];
    $id_usuario = $_SESSION['id_usuario'];

    try {
        // Obtener el registro actual
        $query = "SELECT rh.*, f.periodo 
                  FROM registro_habito rh 
                  JOIN frecuencia f ON rh.id_frecuencia = f.id_frecuencia 
                  WHERE rh.id_registro = ? AND rh.id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_registro, $id_usuario]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            throw new Exception('Registro no encontrado');
        }

        // Verificar si ya está completado
        if ($registro['completado']) {
            throw new Exception('Este hábito ya fue completado');
        }

        // 1. Incrementar el progreso
        $query = "UPDATE registro_habito 
                  SET progreso = progreso + 1
                  WHERE id_registro = ? AND id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_registro, $id_usuario]);

        // 2. Verificar si se alcanzó el objetivo y marcar como completado
        $query = "UPDATE registro_habito
                  SET completado = TRUE,
                      fec_fin = NOW()
                  WHERE id_registro = ? AND id_usuario = ?
                    AND progreso >= objetivo";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_registro, $id_usuario]);

        // Obtener el progreso actualizado
        $query = "SELECT progreso, completado FROM registro_habito WHERE id_registro = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_registro]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'progreso' => $resultado['progreso'],
            'completado' => $resultado['completado']
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
}
