<?php

require('../fpdf/fpdf.php');

class PDF extends FPDF
{
    var $tablewidths;
    var $table_headers = array();
    var $footerset;
    var $user_name;
    var $report_tittle;

    public function header(){
        //$this->Image('../img/logo.jpg',165,5,50,25,'jpg');
        $this->Image('../img/logo.jpg',9,5,50,25,'jpg');
        $this->SetFont('Arial','B',17);
        $this->SetX(70);
        $this->Cell(80,10,utf8_decode('AGENCIA TOTAL TRAVEL'),0,0,'C');
        $this->Ln(10);
        $this->SetX(74);
        $this->SetFont('Arial','',12);
        $this->MultiCell(70,6,utf8_decode($this->report_tittle),0,'C');
        $this->Ln(15);

    }

    function Footer() {

        // Compruebe si pie de página de esta página ya existe ( lo mismo para Header ( ) )
        if(!isset($this->footerset[$this->page])) {
            $this->SetTextColor(0,0,0);
            $this->SetFont('Arial', '', 9);
            $this->SetXY(9,-20);
    
            // Numero de Pagina
            $this->Cell(97.5,10,utf8_decode("Usuario: ".$this->user_name),0,0,'L');
            $this->Cell(97.5,10,utf8_decode("Fecha de impresión: ".date("Y-m-d g:i a")),0,0,'R');
            $this->Ln(5);
            $this->SetX(9);
            $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().' de {nb}',0,0,'R');
            //$this->Cell(0,10,'GetPageHeight: '.$this->GetPageHeight().', GetY: '.$this->GetY(),0,0,'R');
    
            // Conjunto Footerset
            $this->footerset[$this->page] = true;
        }
    }

    function morepagestable($datas, $lineheight = 13, $breakPage = 50, $x = 0 ,$y = 0)
    {
        // Algunas cosas para establecer y ' recuerdan '
        $startWidth =  $this->lMargin;

        if($x != 0)
            $this->lMargin = $x;
        if($y != 0)
            $this->SetY($y);   

        $l = $this->lMargin;
        $startheight = $h = $this->GetY();
        $startpage = $currpage = $maxpage = $this->page;

        // Calcular todo el ancho
        $fullwidth = 0;
        foreach ($this->tablewidths as $width) {
            $fullwidth += $width;
        }

        // GET HEADERS
        if(count($this->table_headers) > 0){
            $this->SetFont('Arial', 'B', 10);
            for ($i=0; $i < count($this->table_headers); $i++) { 
                $this->Cell($this->tablewidths[$i], $lineheight + 3, utf8_decode($this->table_headers[$i]), 1, 0, 'C');
            }
            $this->SetFont('Arial', '', 9);
            $h += ($lineheight + 3);
        }

        // Ahora vamos a empezar a escribir la tabla
        foreach ($datas as $row => $data) {

            $this->page = $currpage;
            // Escribir los bordes horizontales
            $this->Line($l, $h, $fullwidth + $l, $h);

            if($this->GetY() > ($this->GetPageHeight() - $breakPage)){
                $this->AddPage('Portrait', 'Legal');
                $h = $this->GetY();
                $currpage = $this->page;
                $this->Line($l, $h, $fullwidth + $l, $h);
                
                // GET HEADERS
                if(count($this->table_headers) > 0){
                    $this->SetFont('Arial', 'B', 10);
                    for ($i=0; $i < count($this->table_headers); $i++) { 
                        $this->Cell($this->tablewidths[$i], $lineheight + 3, utf8_decode($this->table_headers[$i]), 1, 0, 'C');
                    }
                    $this->SetFont('Arial', '', 9);
                    $h += ($lineheight + 3);
                }
            }
 
            // Escribir el contenido y recordar la altura de la más alta columna
            foreach ($data as $col => $txt) {

                $details = explode(";",$txt);

                // GET STYLE LETTER
                if( count($details) > 1 )
                    $this->SetFont('Arial', $details[1], intval($details[2]));
                else
                    $this->SetFont('Arial','',9);

                $this->page = $currpage;
                $this->SetXY($l, $h);
                $this->MultiCell($this->tablewidths[$col], $lineheight, $details[0],0,"C");
                $l += $this->tablewidths[$col];

                if (!isset($tmpheight[$row . '-' . $this->page]))
                    $tmpheight[$row . '-' . $this->page] = 0;
                if ($tmpheight[$row . '-' . $this->page] < $this->GetY()) {
                    $tmpheight[$row . '-' . $this->page] = $this->GetY();
                }
                if ($this->page > $maxpage)
                    $maxpage = $this->page;
                
            }

            // VERTICAL LINE FOR EACH COLUMN
            $th_h = $this->lMargin;
            foreach ($data as $col => $txt) {
                $th_h += $this->tablewidths[$col];
                $this->Line($th_h , $h, $th_h, $tmpheight[$row . '-' . $maxpage]); 
            }
            // INITIAL VERTICAL LINE
            $this->Line($this->lMargin, $h, $this->lMargin, $tmpheight[$row . '-' . $maxpage]);
             // FINAL VERTICAL LINE 
            $this->Line($l, $h, $l, $tmpheight[$row . '-' . $maxpage]); 

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
        // Establecerlo en la última página , si no que va a causar algunos problemas
        $this->page = $maxpage;

        return array($l, $startheight, $fullwidth + $l, $h);
    }
}