<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@1.0.2/css/bulma.min.css"
>  
<?php
    //Validar si los datos se enviaron 
    if(isset($_POST['nombre'], $_POST['edad'], $_POST['email'])){
        $nombre = $_POST['nombre'];
        $edad = $_POST['edad'];
        $email = $_POST['email'];
    //Validar si las variables contienen algo
        if(!empty($nombre) && !empty($edad) && !empty($email)){
        // validar si la edad es numerica
            if(is_numeric($edad) && filter_var($email, FILTER_VALIDATE_EMAIL)){
            //mostrar en pantalla los datos 
                echo "<h2 class='is-size-4'>Nombre: $nombre </h2>"; // no sé si profané el php pero quería que se viera bonito, plis no me mate 
                echo "<h2 class='is-size-4'>Edad: $edad </h2>";
                echo "<h2 class='is-size-4'>Correo: $email </h2>";
            }else{
                echo "<h3 class='is-size-4'>¡Asegurese que su correo tenga el formato correcto y la edad sea numérica!</h3>";
            }            
        }else{
                echo "<h3 class='is-size-4'>¡No puede dejar campos vacíos!</h3>";
        }
    }
?> 
    
    <form action="index.html" method="get">
        <div class="field is-grouped is-flex is-justify-content-center mt-2">
            <div class="control">
            <button class="button is-link" type="submit">Regresar</button>
        </div>
    </form>