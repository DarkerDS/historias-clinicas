<?php
/*
    Codigos:
    0 = Algún campo está vacío
    1 = Paciente insertado correctamente en la BD
    2 = El paciente no se pudo insertar en la BD
*/
session_start();
$msg = NULL;

if(isset($_SESSION['super_administrador']) || isset($_SESSION['administrador']) || isset($_SESSION['general'])) {
    $flag = 1;
    
    foreach ($_POST as $valor)
        if(!isset($valor) || empty($valor)){
            $flag = 0;
            break;
        }
    
    if($flag){     
        require_once('../config.php');
        $conexion = pg_connect('host='.$app['db']['host'].' port='.$app['db']['port'].' dbname='.$app['db']['name'].' user='.$app['db']['user'].' password='.$app['db']['pass']) OR die('Error de conexión con la base de datos');

        if(isset($_SESSION['super_administrador'])){
            $id_usuario = $_SESSION['super_administrador'];
        }else if(isset($_SESSION['administrador'])){
            $id_usuario = $_SESSION['administrador'];
        }else if(isset($_SESSION['general'])){
            $id_usuario = $_SESSION['general'];
        }

        date_default_timezone_set('Etc/GMT+4');
        $columnas = 'INSERT INTO antecedentes_perinatales (fecha_ua, usuario_ua, creador, ';
        $valores = 'VALUES (\''.date('Y-m-d').'\', '.$id_usuario.', '.$id_usuario.', ';
        $len = count($_POST);
        $cont = 0;

        foreach ($_POST as $clave => $valor){
            if($cont == $len - 1){
                $columnas .= sprintf('%s) ', $clave);
                $valores .= sprintf('\'%s\');', $valor);
            }
            else {
                $columnas .= sprintf('%s,', $clave);
                $valores .= sprintf('\'%s\',', $valor);
            }
            $cont++;
        }

        $query = $columnas . $valores;
        
        if(pg_query($query)) {
            $msg['codigo'] = 1;
        } else {
            $msg['codigo'] = 2;
        }
        pg_close($conexion);
    } else{
        $msg['codigo'] = 0;
    }
}
echo json_encode($msg);
?>