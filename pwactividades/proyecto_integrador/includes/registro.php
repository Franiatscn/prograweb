<?php
session_start();

    try {
    
        require_once 'conexion.php';
    
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            echo "<script>alert('Error: Las contraseñas no coinciden.'); window.location.href='../signup.php';</script>";
            exit;
        }

        // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
        if (
            empty($_POST['nombres']) ||
            empty($_POST['apellido_p']) ||
            empty($_POST['apellido_m']) ||
            empty($_POST['email']) ||
            empty($_POST['password'])
        ) {
            echo "<script>alert('Todos los campos obligatorios deben llenarse.'); window.location.href='../signup.php';</script>";
            exit;
        }

        // Asignamos los datos del formulario a variables PHP
        $nombres    = $_POST['nombres'];
        $apellido_p  = $_POST['apellido_p'];
        $apellido_m = $_POST['apellido_m'];
        $email     = $_POST['email'];

        // Ciframos la contraseña antes de guardarla (muy importante para seguridad)        
        $password   = password_hash($password, PASSWORD_DEFAULT);

        // Valores por defecto para rol y estatus (puedes explicar estos roles a los alumnos)        
        $id_rol = 2;
        $id_estatus = 1;    

        // Verificamos si el correo ya está registrado en la base de datos
        $verificar = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = :email");
        $verificar->execute([':email' => $email]);

        if ($verificar->rowCount() > 0) {
            echo "<script>alert('El correo ya está registrado.'); window.location.href='../signup.php';</script>";
            exit;
        }

        // Si el correo no existe, insertamos el nuevo usuario usando consulta preparada
        $sql = "INSERT INTO usuario (nombres, apellido_p, apellido_m, email, password, id_rol, id_estatus)
                VALUES (:nombres, :apellido_p, :apellido_m, :email, :password, :id_rol, :id_estatus)";

        $stmt = $conn->prepare($sql);

        // Ejecutamos la consulta pasando los datos de forma segura (evita inyección SQL)
        $stmt = $stmt->execute([
            ':nombres'    => $nombres,
            ':apellido_p'  => $apellido_p,
            ':apellido_m'  => $apellido_m,
            ':email'     => $email,
            ':password'   => $password,
            ':id_rol'     => $id_rol,
            ':id_estatus' => $id_estatus
        ]);

        echo "<script>alert('¡Registro exitoso! Ahora puedes iniciar sesión.'); window.location.href='../login.php';</script>";

    } catch (PDOException $e) {
        // Si ocurre un error con la base de datos, lo mostramos        
        echo "<script>alert('Lo sentimos, ha ocurrido un error al registrar tu cuenta. Por favor, intenta nuevamente.'); window.location.href='../signup.php';</script>";
    }
?>