<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../includes/conexion.php';
// Datos del administrador
$nombre = 'Admin';
$apellido_p = 'Principal';
$apellido_m = '';
$email = 'admin@habitity.com';
$password = 'admin123';
$id_rol = 1;
$id_estatus = 1;

// Cifrar la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar el administrador en la base de datos
$sql = "INSERT INTO usuario (nombres, apellido_p, apellido_m, email, password, id_rol, id_estatus) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$nombre, $apellido_p, $apellido_m, $email, $password_hash, $id_rol, $id_estatus]);
echo "Administrador creado con éxito.";