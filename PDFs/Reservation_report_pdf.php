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
    //$pdf->AutoPageBreak();

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
    $pdf->Ln(7);

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
    $pdf->tablewidths = array(30,166);
    $fecha_1 = $_Functions->date_Es($reservacionDetalle["fecha_Entrada"]);
    $fecha_2 = $_Functions->date_Es($reservacionDetalle["fecha_Salida"]);

    $hotel_info_2[] = array(
        utf8_decode("DESCRIPCIÓN;B;9"),
        utf8_decode(
            "Programado para el dia $fecha_1 hasta el dia $fecha_2 para ".(
                $reservacionDetalle["numeroPersonas"].( intval($reservacionDetalle["numeroPersonas"]) <= 1 ? " persona" : " personas")
            )
        ),
    );
    $pdf->morepagestable($hotel_info_2,6);
    $pdf->Ln(15);


// HOTEL ROOMS INFO TABLE --------------------------------------------------------------------

    // HEADER 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"HABITACIONES RESERVADAS",1,0,"C",1);
    $pdf->Ln(8);

    // BODY
    if(count($dataFilter["habitaciones"]) > 0){

        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(8,20,68,20,40,20,20);
        
        $count = 1;
        foreach ($dataFilter["habitaciones"] as $item) {
            
            $hotel_room_info[] = array( 
                utf8_decode($count."."),
                utf8_decode('NOMBRE;B;9'),
                utf8_decode($item["details"]["habitacion"]),
                utf8_decode('PRECIO;B;9'),
                utf8_decode("L ".number_format($item["details"]["precio"],2)." /noche"),    
                utf8_decode('CANTIDAD;B;9'),
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
        $pdf->Ln(16);
    }
    

// HOTEL ACTIVITIES TABLE --------------------------------------------------------------------
    
    // HEADER 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"ACTIVIDADES PROGRAMADAS EN EL HOTEL",1,0,"C",1);
    $pdf->Ln(8);

    // BODY 
    if(count($dataFilter["actividadesHoteles"]) > 0){

        $pdf->table_headers = array(
            "NO.",
            "NOMBRE",
            "PRECIO",
            "DESCRIPCIÓN"
        );
        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(10,61,40,85);
        
        $count = 1;
        foreach ($dataFilter["actividadesHoteles"] as $item) {
            
            $hotel_activities_info[] = array( 
                utf8_decode($count."."),
                utf8_decode($item["details"]["actividad"]),
                utf8_decode("L ".number_format($item["details"]["precio"],2)." /persona"),   
                utf8_decode("Programado para el dia ".$_Functions->date_Es($item["reAH_FechaReservacion"])." para ".$item["reAH_Cantidad"]." personas") 
            );
            $count++;
        }

        $pdf->morepagestable($hotel_activities_info,6);
        $pdf->table_headers = array();
        $pdf->Ln(16);
    }else{
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->Cell(0,8,"NO SE ENCONTRARON ACTIVIDADES PROGRAMADAS EN EL HOTEL",1,0,"C",1);
        $pdf->Ln(16);
    }


// EXTRA ACTIVITIES TABLE --------------------------------------------------------------------
    
    // HEADER 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"ACTIVIDADES EXTRA PROGRAMADAS",1,0,"C",1);
    $pdf->Ln(8);

    // BODY 
    if(count($dataFilter["actividadesExtras"]) > 0){

        $pdf->table_headers = array(
            "NO.",
            "NOMBRE",
            "PRECIO",
            "DESCRIPCIÓN"
        );
        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(10,61,40,85);
        
        $count = 1;
        foreach ($dataFilter["actividadesExtras"] as $item) {
            
            $extra_activities_info[] = array( 
                utf8_decode($count."."),
                utf8_decode($item["details"]["actividad"]),
                utf8_decode("L ".number_format($item["details"]["precio"],2)." /persona"),    
                utf8_decode("Programado para el dia ".$_Functions->date_Es($item["reAE_FechaReservacion"])." para ".$item["reAE_Cantidad"]." personas")
            );
            $count++;
        }

        $pdf->morepagestable($extra_activities_info,6);
        $pdf->table_headers = array();
        $pdf->Ln(16);
    }else{
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->Cell(0,8,"NO SE ENCONTRARON ACTIVIDADES EXTRAS PROGRAMADAS",1,0,"C",1);
        $pdf->Ln(16);
    }

// ------------------------------ NEW PAGE --------------------------------------------------------------------

    $pdf->AddPage('Portrait', 'Legal');
// RESTAURANT TABLE TABLE --------------------------------------------------------------------

    // HEADER 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"RESERVACIONES DE RESTAURANTES",1,0,"C",1);
    $pdf->Ln(8);

    // BODY 
    if(count($dataFilter["restaurantes"]) > 0){

        $pdf->table_headers = array(
            "NO.",
            "NOMBRE",
            "DIRECCIÓN",
            "DESCRIPCIÓN"
        );
        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(10,36,70,80);
        
        $count = 1;
        foreach ($dataFilter["restaurantes"] as $item) {

            $direccion = "Ciudad {$item["details"]["ciudad"]}, Colonia {$item["details"]["colonia"]}, Calle {$item["details"]["calle"]}, Avenida {$item["details"]["avenida"]}";
            $restaurant_info[] = array( 
                utf8_decode($count."."), 
                utf8_decode($item["details"]["restaurante"]),   
                utf8_decode($direccion), 
                utf8_decode("Programado para el día ".$_Functions->date_Es($item["reRe_FechaReservacion"]))
            );
            $count++;
        }

        $pdf->morepagestable($restaurant_info,6);
        $pdf->table_headers = array();
        $pdf->Ln(16);
    }else{
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->Cell(0,8,"NO SE ENCONTRARON RESTAURANTES PROGRAMADOS",1,0,"C",1);
        $pdf->Ln(16);
    }

// TRANSPORT TABLE TABLE --------------------------------------------------------------------

    // HEADER 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(153, 0, 255);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0,8,"RESERVACIONES DE TRANSPORTES",1,0,"C",1);
    $pdf->Ln(8);

    // BODY 
    if(count($dataFilter["transportes"]) > 0){

        $pdf->table_headers = array(
            "NO.",
            "AGENCIA",
            "DESCRIPCIÓN",
            "ASIENTOS",
            "PRECIO"
        );
        $pdf->SetTextColor(0,0,0);
        $pdf->tablewidths = array(10,41,70,35,40);
        
        $count = 1;
        foreach ($dataFilter["transportes"] as $item) {

            $transport_info[] = array( 
                utf8_decode($count."."), 
                utf8_decode($item["details"]["parter"]),   
                utf8_decode("Programado para el día ".$_Functions->date_Es($item["details"]["fecha_Salida"])." a las ".$item["details"]["hora_Salida"]),
                utf8_decode($item["reTr_CantidadAsientos"]),  
                utf8_decode("L ".number_format($item["details"]["precio"],2)." /asiento")
            );
            $count++;
        }

        $pdf->morepagestable($transport_info,6);
        $pdf->table_headers = array();
        $pdf->Ln(16);
    }else{
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->Cell(0,8,"NO SE ENCONTRARON TRANSPORTES PROGRAMADOS",1,0,"C",1);
        $pdf->Ln(16);
    }

    $pdf->Output();

}
