<?php
// Iniciar la sesión
session_start();
// Limpiar variables de sesion
session_unset();
// Destruir la sesión
session_destroy();
// Redirigir al usuario a la página de login
header('Location: login.php');
exit();
?>

