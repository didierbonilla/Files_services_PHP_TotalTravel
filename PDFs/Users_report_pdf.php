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

    // GET COUNTS OF PARAMETERS
    $parametersCount=0;
    if($parameters != null){
        foreach ($parameters as $key => $value)
            $parametersCount++;
    }
    
    // FILTER DATA
    if($parametersCount > 0)
        $dataFilter = $_Functions->getDataFilter($data,$parameters); 
}

//-------------------------------------------- PDF DOCUMENT START HERE ------------------------------------
$pdf = new PDF();
$pdf->user_name = $user_name;

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA
$header = array(
    'No.',
    'DNI',
    'Nombre completo',
    'Genero',
    'E-mail',
    'Rol de usuario',
    "Dirección",
);

if(count($dataFilter) > 0){
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
        $direccion = "Ciudad {$key["ciudad"]}, Colonia {$key["colonia"]}, Ave. {$key["avenida"]}, Calle {$key["calle"]}";

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

    $pdf->morepagestable($header,$row,6);
}else{
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(254,38,25);
    $pdf->tablewidths = array(190);
    $row[] = array(
        utf8_decode("NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA")
    );
    $pdf->morepagestable($header,$row,13);
}

$pdf->Output();
