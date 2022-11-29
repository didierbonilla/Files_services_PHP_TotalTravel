<?php
setlocale(LC_ALL,"es_ES");
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
$reservation_id = isset($_GET["id"]) ? $_GET["id"] : 0;
$reservationList = json_decode($_API->HTTPRequest("/API/Reservation/Details?Id=$reservation_id", "GET", null),true);
$data = $reservationList["data"];

// GET DEFAULT DATA
$dataFilter =  $data;
$user_name = "NO USER NAME";

if($dataFilter["reservacionDetalle"] != null){

    $reservacionDetalle = $dataFilter["reservacionDetalle"];
    $pdf = new PDF();
    $pdf->user_name = $user_name;
    $pdf->report_tittle = "REPORTE A DETALLE DE RESERVACIÓN - COD-0$reservation_id";

    $pdf->AddPage('Portrait', 'Legal');
    $pdf->AliasNbPages();

    // GENERAL INFO ------------------------------------------------------------------------

    // HEADER
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,utf8_decode("INFORMACIÓN GENERAL"),1,0,"C",1);
    $pdf->Ln(8);

    // ROW #1
    $pdf->SetTextColor(0,0,0);
    $pdf->tablewidths = array(20,25,20,40,10,35,20,26);
    $general_info_1[] = array(
        utf8_decode("CÓDIGO;B;9"),
        utf8_decode("COD-0$reservation_id"),
        utf8_decode("CLIENTE;B;9"),
        utf8_decode($reservacionDetalle["nombrecompleto"]),
        utf8_decode("DNI;B;9"),
        utf8_decode($reservacionDetalle["dni"]),
        utf8_decode("TEL.;B;9"),
        utf8_decode($reservacionDetalle["telefono"])
    );
    $pdf->morepagestable($general_info_1,7);

    // ROW #2
    $pdf->tablewidths = array(20,35,20,60,20,41);
    $general_info_2[] = array(
        utf8_decode("PRECIO;B;9"),
        utf8_decode( "L ".number_format($reservacionDetalle["precio"],2) ),
        utf8_decode("PAQUETE;B;9"),
        utf8_decode($reservacionDetalle["esPersonalizado"] == true ? "Personalizado" : $reservacionDetalle["nombrePaquete"]),
        utf8_decode("DURACIÓN;B;9"),
        utf8_decode(
            intval($reservacionDetalle["durecionPaquete"]) <= 1
            ? "1 dias, 1 noches"
            : $reservacionDetalle["durecionPaquete"]." dias, ".(intval($reservacionDetalle["durecionPaquete"]) - 1)." noches"
        ),
    );
    $pdf->morepagestable($general_info_2,7);
    $pdf->Ln(10);


    // HOTEL INFO TABLE --------------------------------------------------------------------
    $HotelData = json_decode($_API->HTTPRequest("/API/Hotels/Find?Id=".$reservacionDetalle["hotel_ID"], "GET", null),true);

    // HEADER
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,utf8_decode("INFORMACIÓN DE HOTEL"),1,0,"C",1);
    $pdf->Ln(8);

    // ROW #1
    $pdf->SetTextColor(0,0,0);
    $pdf->tablewidths = array(15,25,20,51,25,60);
    $hotel_direction = "País {$HotelData["data"]["pais"]}, Ciudad {$HotelData["data"]["ciudad"]}, Colonia {$HotelData["data"]["colonia"]}, Ave. {$HotelData["data"]["avenida"]}, Calle {$HotelData["data"]["calle"]}";;
    $hotel_info_1[] = array(
        utf8_decode("HOTEL;B;9"),
        utf8_decode($HotelData["data"]["hotel"]),
        utf8_decode("AGENCIA;B;9"),
        utf8_decode($HotelData["data"]["partners"]),
        utf8_decode("DIRECCIÓN;B;9"),
        utf8_decode($hotel_direction),
    );
    $pdf->morepagestable($hotel_info_1,6);

    // ROW #2
    $pdf->tablewidths = array(30,106,30,30);
    $fecha_1 = $_Functions->date_Es($reservacionDetalle["fecha_Entrada"]);
    $fecha_2 = $_Functions->date_Es($reservacionDetalle["fecha_Salida"]);

    $hotel_info_2[] = array(
        utf8_decode("FECHA;B;9"),
        utf8_decode("Programado para el dia $fecha_1 hasta el dia $fecha_2"),
        utf8_decode("PERSONAS;B;9"),
        utf8_decode(
            $reservacionDetalle["numeroPersonas"].( intval($reservacionDetalle["numeroPersonas"]) <= 1 ? " persona" : " personas")
        )
    );
    $pdf->morepagestable($hotel_info_2,6);
    $pdf->Ln(15);


    // HOTEL ROOMS INFO TABLE --------------------------------------------------------------------
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"HABITACIONES RESERVADAS",1,0,"C",1);
    $pdf->Ln(8);

    if(count($dataFilter["habitaciones"]) > 0){

        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(8,8,20,60,20,40,20,20);
        
        $count = 1;
        foreach ($dataFilter["habitaciones"] as $item) {
            
            $hotel_room_info[] = array(
                utf8_decode('No.;B;9'),  
                utf8_decode($count),
                utf8_decode('Nombre;B;9'),
                utf8_decode($item["details"]["habitacion"]),
                utf8_decode('Precio;B;9'),
                utf8_decode("L ".number_format($item["details"]["precio"],2)." /noche"),    
                utf8_decode('Cantidad;B;9'),
                utf8_decode($item["habi_Cantidad"]),  
            );
            $count++;
        }

        $pdf->morepagestable($hotel_room_info,7);
        $pdf->Ln(10);

    }else{
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->Cell(0,8,"NO SE ENCONTRARON HABITACIONES RESERVADAS",1,0,"C",1);
    }
    
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0,8,"ACTIVIDADES RESERVADAS",1,0,"C",1);



    $pdf->Output();

}
