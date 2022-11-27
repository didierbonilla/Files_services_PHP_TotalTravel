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
}

?>