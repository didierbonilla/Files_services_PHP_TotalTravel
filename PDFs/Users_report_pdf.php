<?php

require('PDF.class.php');
require('../API.class.php');
require('../Functions.class.php');

// INSTANCE CLASS 
$_API = new API(null);
$_Functions = new Functions();

// get API TOKEN
$login_response = json_decode($_API->getToken(),true);
$token = $login_response["data"]["token"];
$_API->_API_TOKEN = $token;

// SAVE REPORT DATA 
$data = json_decode($_API->HTTPRequest("/API/Users/List", "GET", null),true);
$usersList = $data["data"];

// GET DEFAULT DATA
$dataFilter =  $usersList;
$user_name = "NO USER NAME";

// IF EXIST PARAMETERS: FILTER DATA
if(isset($_GET["query"])){

    $query = json_decode($_GET["query"]);

    // GETS PARAMETERS
    $parameters = isset($query->parameters) ? $query->parameters : null;
    $user_name = isset($query->user_name) ? $query->user_name : "";

    // FILTER DATA BY AGE
    if(isset($parameters->edad)){

        //echo "edad:<br>";
        $temp_data = array();
        foreach ($usersList as $element){
            $fecha_nac = $element["fecha_Nacimiento"];
            $edad = intval($_Functions->GetYearsBetweenTwoDates($fecha_nac));
            $rango_edad = explode("-", strval($parameters->edad));

            if(count($rango_edad) == 2){
               // echo "rango de 2:<br>";
                if($edad >= intval($rango_edad[0]) && $edad <= intval($rango_edad[1])){
                    //echo "$edad<br>";
                    array_push($temp_data, $element);
                }
            } 
            else if(count($rango_edad) == 1){
                //echo "rango de 1:<br>";
                if($edad == intval($rango_edad[0])){
                    //echo "$edad<br>";
                    array_push($temp_data, $element);
                }
            }
        }
        //echo "total:".count($temp_data)."<br>";
        $usersList = $temp_data;
        unset($parameters->edad);
    }

    // GET COUNTS OF PARAMETERS
    $parametersCount=0;
    if($parameters != null){
        foreach ($parameters as $key=> $value)
            $parametersCount++;
    }
    
    // FILTER DATA
    if($parametersCount > 0)
        $dataFilter = $_Functions->getDataFilter($usersList,$parameters); 
    else
        $dataFilter = $usersList;
    
}

//-------------------------------------------- PDF DOCUMENT START HERE ------------------------------------
$pdf = new PDF();
$pdf->user_name = $user_name;
$pdf->report_tittle = "REPORTE DE USUARIOS DE EL SISTEMA";

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA

if(count($dataFilter) > 0){
    $pdf->table_headers = array(
        'No.',
        'DNI',
        'Nombre completo',
        'Sexo',
        'E-mail',
        'Rol de usuario',
        "Dirección",
    );

    $pdf->SetFont('Arial', '', 9);
    // ESTABLECE EL TAMAÑO DE CADA CELDA
    $pdf->tablewidths = array(8,27,33,20,40,28,40);

    // POR CADA REGISTRO CREA NUEVA FILA EN LA TABLA
    $item = 0;
    for ($i=0; $i < count($dataFilter); $i++) { 

        $item = $item+1;
        $key = $dataFilter[$i];

        $DNI = $key["dni"];
        $Nombre_completo = $key["nombrecompleto"];;
        $Genero = $key["sexo"];
        $E_mail = $key["email"];
        $Rol = $key["rol"];
        $direccion = "Pais {$key["pais"]}, Ciudad {$key["ciudad"]}, Colonia {$key["colonia"]}, Ave. {$key["avenida"]}, Calle {$key["calle"]}";

        // este array se rellena en orden de las columnas
        //ejemplo $item es el valor de la columna #1 y asi
        $row[] = array(
            utf8_decode($item."."),
            utf8_decode($DNI),
            utf8_decode($Nombre_completo),
            utf8_decode($Genero),
            utf8_decode($E_mail),
            utf8_decode($Rol),
            utf8_decode($direccion)
        );
    }

    $pdf->morepagestable($row,6);
}else{
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(254,38,25);
    $pdf->Cell(0,0,"NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA",1,0,"C");
}

$pdf->Output();
