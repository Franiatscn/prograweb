<?php
session_start();
require_once '../../includes/conexion.php';

try {
    if (!isset($_SESSION['id_usuario'])) {  // Verificamos si el usuario ha iniciado sesión
        header('Location: ../login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificamos si el formulario se envía con el método POST
        $id_usuario = $_SESSION['id_usuario'];
        $id_habito = $_POST['habito'];
        $id_frecuencia = $_POST['frecuencia'];
        $objetivo = $_POST['objetivo'];
        $fec_inicio = date('Y-m-d H:i:s'); // Obtenemos la fecha y hora actual

        // Validar que el objetivo sea un número positivo
        if (!is_numeric($objetivo) || $objetivo <= 0) {
            throw new Exception('El objetivo debe ser un número positivo');
        }

        // Insertamos los datos en la base de datos
        $sql = "INSERT INTO registro_habito (
                    id_habito, 
                    id_usuario, 
                    id_frecuencia,
                    objetivo,
                    progreso,
                    fec_inicio
                ) VALUES (
                    :id_habito, 
                    :id_usuario, 
                    :id_frecuencia,
                    :objetivo,
                    0,
                    :fec_inicio
                )";

        $stmt = $conn->prepare($sql);

        // Ejecutamos la consulta pasando los datos de forma segura (evita inyección SQL)
        $stmt->execute([
            ':id_habito' => $id_habito,
            ':id_usuario' => $id_usuario,
            ':id_frecuencia' => $id_frecuencia,
            ':objetivo' => $objetivo,
            ':fec_inicio' => $fec_inicio
        ]);

        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit();
}
?>
