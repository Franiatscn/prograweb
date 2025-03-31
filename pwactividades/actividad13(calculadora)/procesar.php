<?php
    if(isset($_POST['submit'])){
        $numbr1 = $_POST['numbr1'];
        $numbr2 = $_POST['numbr2'];
        $option = $_POST['option'];

        if(is_numeric($numbr1) && is_numeric($numbr2)){ 
        $result = 0;
            switch($option){
            case 'sumar':
                $result = $numbr1 + $numbr2;
            break;
            case 'restar':
                $result = $numbr1 - $numbr2;
            break;
            case 'multiplicar':
                $result = $numbr1 * $numbr2;
            break;
            case 'dividir':
                if($numbr2 != 0){
                    $result = $numbr1 / $numbr2;
                }else{
                    $result = 'No se puede dividir entre 0';
                }
            break;
        }
        echo "<h2><center>El resultado de la operación es: $result</center></h2>";
        }else{
            echo "<h2> Error: Favor de ingresar números</h2>";
        }
    }
?>  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <form action="index.html" method="get"  class="d-flex justify-content-center">
            <button class="btn btn-outline-success" type="submit" name="submit">Regresar a Calcular</button>
        </form