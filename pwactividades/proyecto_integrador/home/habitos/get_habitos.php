<?php
require_once '../../includes/conexion.php';

if (isset($_GET['categoria_id'])) {
    $categoria_id = intval($_GET['categoria_id']);

    $query = "SELECT id_habito, nombre FROM habito WHERE id_categoria = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$categoria_id]);
    $habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($habitos);
}
?>
