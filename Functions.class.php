<?php

class Functions{
    
    function getDataFilter($data,$parameters){

        $arrayFilter = array();
        foreach ($parameters as $key => $value){

            if(count($arrayFilter) == 0){

                foreach ($data as $element){

                    if(isset($element[$key])){
                        if($element[$key] == $value || $element[$key] == intval($value)){
                            array_push($arrayFilter, $element);
                        }
                    }
                }
            }
            else{

                $temporalFilter = $arrayFilter;
                $arrayFilter = array();
            
                foreach ($temporalFilter as $element){

                    if(isset($element[$key])){
                        if($element[$key] == $value || $element[$key] == intval($value)){
                            array_push($arrayFilter, $element);
                        }
                    }
                }
            }
        }

        return $arrayFilter;
    }

    function GetYearsBetweenTwoDates($_date_S, $_date_F = null){

        $_date_F = $_date_F == null ? date("Y-m-d") : $_date_F;

        $date_S = new DateTime($_date_S);
        $date_F = new DateTime($_date_F);
        $diferencia = $date_F->diff($date_S);
        return $diferencia->format("%y");
    }

    function date_Es($fecha) {
        $fecha = substr($fecha, 0, 10);
        $numeroDia = date('d', strtotime($fecha));
        $dia = date('l', strtotime($fecha));
        $mes = date('F', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));
        $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
        $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        $nombredia = str_replace($dias_EN, $dias_ES, $dia);
        $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        $nombreMes = str_replace($meses_EN, $meses_ES, $mes);

        return $nombredia." ".$numeroDia." de ".$nombreMes." de ".$anio;
    }
}

?>