<?php

class Functions{
    
    function getDataFilter($data,$count,$keys,$values){

        $arrayFilter = array();
        for ($i = 0; $i < $count; $i++){
            
            if(count($arrayFilter) == 0){
        
                for ($j = 0; $j < count($data); $j++){
                
                    $element = $data[$j];
                    
                    if($element[$keys[$i]] == intval($values[$i])){
                        array_push($arrayFilter, $element);
                    }
                }
                
            }else{
        
                $temporalFilter = $arrayFilter;
                $arrayFilter = array();
                for ($j = 0; $j < count($temporalFilter); $j++){
                
                    $element = $temporalFilter[$j];
                    
                    if($element[$keys[$i]] == intval($values[$i])){
                        array_push($arrayFilter, $element);
                    }
                }
            }
            //$parametros[$tags[$i]] = $valores[$i];
        }

        return $arrayFilter;
    }
}

?>