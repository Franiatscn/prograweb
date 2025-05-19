<?php
    // Conectar con la Base de Datos
    $host = 'localhost';
    $dbname = 'habitity_db';
    $user = 'root';
    $pass = 'root';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);   
         /*
         * Configuramos el modo de errores para que PDO lance excepciones (EXCEPTION MODE).
         * Esto nos ayuda a detectar y manejar errores de forma controlada con try-catch.
         */   
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Se muestra un mensaje de error en caso de que no se genere la conexión
        die("Error de conexión: " . $e->getMessage());
    }
?>
