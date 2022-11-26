<?php
header('Content-Type: application/pdf');
require('../fpdf/fpdf.php');
require('../API.class.php');
require('../Functions.class.php');

if(isset($_POST["parameters"])){

    $data = json_decode($_POST["parameters"]);

    $parameters = isset($data->parameters) ? $data->parameters : null;
    $token = isset($data->token) ? $data->token : null;
    $user_name = isset($data->user_name) ? $data->user_name : "";

    $_API = new API($token);
    $_Functions = new Functions();

    //save data
    $hotelsList = json_decode($_API->HTTPRequest("/API/Hotels/List", "GET", null),true);
    $data = $hotelsList["data"];

    //count the total of parameters
    $parametersCount=0;
    if($parameters != null){
        foreach ($parameters as $key => $value)
            $parametersCount++;
    }
    
    // filter data
    if($parametersCount > 0){
        $dataFilter = $_Functions->getDataFilter($data,$parameters); 
    }else{
        $dataFilter =  $data;
    }

}else{
    $_API = new API(null);
    $hotelsList = json_decode($_API->HTTPRequest("/API/Hotels/List", "GET", null),true);
    $data = $hotelsList["data"];
    $dataFilter =  $data;

    $user_name = "No Data";
    if(isset($_GET["user"]))
        $user_name = $_GET["user"];

}

    class PDF extends FPDF
    {
        var $tablewidths;
        var $footerset;
        var $user_name;

        public function header(){
            //$this->Image('../img/logo.jpg',165,5,50,25,'jpg');
            $this->Image('../img/logo.jpg',3,5,50,25,'jpg');
            $this->SetFont('Arial','B',17);
            $this->Cell(195,10,utf8_decode('AGENCIA TOTAL TRAVEL'),0,0,'C');
            $this->Ln(7);
            $this->SetFont('Arial','',12);
            $this->Cell(195,10,utf8_decode('REPORTE DE HOTELES'),0,0,'C');
            $this->Ln(20);

        }

        function Footer() {

            // Compruebe si pie de página de esta página ya existe ( lo mismo para Header ( ) )
            if(!isset($this->footerset[$this->page])) {
                $this->SetY(-20);
        
                // Numero de Pagina
                $this->Cell(97.5,10,utf8_decode("Usuario: ".$this->user_name),0,0,'L');
                $this->Cell(97.5,10,utf8_decode("Fecha de impresión: ".date("Y-m-d g:i a")),0,0,'R');
                $this->Ln(5);
                $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'de {nb}',0,0,'R');
        
                // Conjunto Footerset
                $this->footerset[$this->page] = true;
            }
        }

        function morepagestable($datas, $lineheight = 13)
        {
            // Algunas cosas para establecer y ' recuerdan '
            $l = $this->lMargin;
            $startheight = $h = $this->GetY();
            $startpage = $currpage = $maxpage = $this->page;

            // Calcular todo el ancho
            $fullwidth = 0;
            foreach ($this->tablewidths as $width) {
                $fullwidth += $width;
            }

            // Ahora vamos a empezar a escribir la tabla
            foreach ($datas as $row => $data) {
                $this->page = $currpage;

                // Escribir los bordes horizontales
                $this->Line($l, $h, $fullwidth + $l, $h);

                // Escribir el contenido y recordar la altura de la más alta columna
                foreach ($data as $col => $txt) {
                    $this->page = $currpage;
                    $this->SetXY($l, $h);
                    $this->MultiCell($this->tablewidths[$col], $lineheight, $txt,0,"C");
                    $l += $this->tablewidths[$col];

                    if (!isset($tmpheight[$row . '-' . $this->page]))
                        $tmpheight[$row . '-' . $this->page] = 0;
                    if ($tmpheight[$row . '-' . $this->page] < $this->GetY()) {
                        $tmpheight[$row . '-' . $this->page] = $this->GetY();
                    }
                    if ($this->page > $maxpage)
                        $maxpage = $this->page;
                }

                // Obtener la altura estábamos en la última página utilizada
                $h = $tmpheight[$row . '-' . $maxpage];

                //Establecer el "puntero " al margen izquierdo
                $l = $this->lMargin;

                // Establecer el "$currpage en la ultima pagina
                $currpage = $maxpage;
            }

            // Dibujar las fronteras
            // Empezamos a añadir una línea horizontal en la última página
            $this->page = $maxpage;
            $this->Line($l, $h, $fullwidth + $l, $h);
            // Ahora empezamos en la parte superior del documento
            for ($i = $startpage; $i <= $maxpage; $i++) {
                $this->page = $i;
                $l = $this->lMargin;
                $t = ($i == $startpage) ? $startheight : $this->tMargin;
                $lh = ($i == $maxpage) ? $h : $this->h - $this->bMargin;
                $this->Line($l, $t, $l, $lh);
                for ($i= 0; $i < count($this->tablewidths); $i++) { 
                    $l += $this->tablewidths[$i];
                    $this->Line($l, $t, $l, $lh);
                }
            }
            // Establecerlo en la última página , si no que va a causar algunos problemas
            $this->page = $maxpage;
        }
    }

    $pdf = new PDF();
    $pdf->user_name = $user_name;

    $pdf->AddPage('Portrait', 'Legal');
    $pdf->AliasNbPages();

    // HEADER DE LA TABLA
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 9, 'No.', 1, 0, 'C');
    $pdf->Cell(25, 9, 'COD.', 1, 0, 'C');
    $pdf->Cell(40, 9, 'Hotel', 1, 0, 'C');
    $pdf->Cell(45, 9, 'Socio', 1, 0, 'C');
    $pdf->Cell(70, 9, utf8_decode('Dirección'), 1, 0, 'C');
    $pdf->Ln(9);


    if(count($dataFilter) > 0){
        $pdf->SetFont('Arial', '', 9);
        // ESTABLECE EL TAMAÑO DE CADA CELDA
        $pdf->tablewidths = array(10,25,40,45,70);

        $item = 0;

        for ($i=0; $i < count($dataFilter); $i++) { 
            $item = $item+1;
            $key = $dataFilter[$i];

            $id_hotel = $key["id"];
            $hotel = $key["hotel"];;
            $partners = $key["partners"];
            $ciudad = "Ciudad {$key["ciudad"]}, Colonia {$key["colonia"]}, Ave. {$key["avenida"]}, Calle {$key["calle"]}";

            // este array se rellena en orden de las columnas
            //ejemplo $item es el valor de la columna #1 y asi
            $row[] = array(
                utf8_decode($item."."),
                utf8_decode("COD-00".$id_hotel),
                utf8_decode($hotel),
                utf8_decode($partners),
                utf8_decode($ciudad)
            );
        }

        $pdf->morepagestable($row,5);
    }else{
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(254,38,25);
        $pdf->tablewidths = array(190);
        $row[] = array(
            utf8_decode("NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA")
        );
        $pdf->morepagestable($row,13);
    }

    $pdf->Output();
