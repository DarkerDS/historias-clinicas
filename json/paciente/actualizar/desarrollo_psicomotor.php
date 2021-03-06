<?php
/*
    Codigos:
    0 = Algún campo está vacío
    1 = Desarrollo psicomotor del paciente actualizado correctamente en la BD
    2 = El desarrollo psicomotor del paciente no se pudo actualizar en la BD
    3 = Error consultando en la base de datos
    4 = No posee permisos para realizar la operación
*/
session_start();
$msg['msg'] = 'No posee permisos para actualizar los datos del paciente';
$msg['flag'] = 4;

if(isset($_SESSION['super_administrador']) || isset($_SESSION['administrador']) || isset($_SESSION['general'])) {
    $flag = 1;
    
    foreach ($_POST as $valor)
        if(!isset($valor) || empty($valor)){
            $flag = 0;
            break;
        }

    if($flag){
        require_once('../../../config.php');
        $conexion = pg_connect('host='.$app['db']['host'].' port='.$app['db']['port'].' dbname='.$app['db']['name'].' user='.$app['db']['user'].' password='.$app['db']['pass']) OR die('Error de conexión con la base de datos');

        if(isset($_SESSION['super_administrador']))
            $id_usuario = $_SESSION['super_administrador'];
        else if(isset($_SESSION['administrador']))
            $id_usuario = $_SESSION['administrador'];
        else if(isset($_SESSION['general']))
            $id_usuario = $_SESSION['general'];
        
        $select = 'SELECT id_paciente FROM desarrollo_psicomotor WHERE id_paciente = '.$_POST['id_paciente'];
        
        if($query = pg_query($select)){
            date_default_timezone_set('Etc/GMT+4');
            $respuesta = pg_fetch_assoc($query);
            
            if(empty($respuesta['id_paciente'])){
                $columnas = 'INSERT INTO desarrollo_psicomotor (fecha_ua, usuario_ua, creador, ';
                $valores = 'VALUES (\''.date('Y-m-d').'\', '.$id_usuario.', '.$id_usuario.', ';
                $last_value = ');';
                
            } else{
                $columnas = 'UPDATE desarrollo_psicomotor SET (fecha_ua, usuario_ua, ';
                $valores = '= (\''.date('Y-m-d').'\', '.$id_usuario.', ';
                $last_value = ') WHERE id_paciente = '.$_POST['id_paciente'].';';
                unset($_POST['id_paciente']);
            }
            $cont = 0;
            $len = count($_POST);
            
            foreach ($_POST as $clave => $valor){
                if($cont === $len - 1) {
                    $columnas .= $clave.') ';
                    $valores .= '\''.$valor.'\''.$last_value;
                    
                } else {
                    $columnas .= $clave.',';
                    $valores .= '\''.$valor.'\',';
                }
                $cont++;
            }
            $query = $columnas . $valores;

            if(pg_query($query)) {
                $msg['msg'] = 'Actualización de datos exitosa';
                $msg['flag'] = 1;
            } else {
                $msg['msg'] = 'No se pudieron actualizar los datos del paciente';
                $msg['flag'] = 2;
            }
        } else {
            $msg['msg'] = 'Error de consulta en la base de datos';
            $msg['flag'] = 3;
        }
        pg_close($conexion);
        
    } else {
        $msg['msg'] = 'Debe llenar todos los campos';
        $msg['flag'] = 0;
    }
}
echo json_encode($msg);
?>