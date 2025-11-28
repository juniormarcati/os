<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2024 by Junior Marcati
   https://github.com/juniormarcati/os  
   ------------------------------------------------------------------------
   LICENSE
   This file is part of Plugin OS project.
   Plugin OS is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.
   Plugin OS is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with Plugin OS. If not, see <http://www.gnu.org/licenses/>.
   ------------------------------------------------------------------------
   @package   Plugin OS
   @author    Junior Marcati
   @co-author
   @copyright Copyright (c) 2016-2024 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/os  
   @since     2016
   ------------------------------------------------------------------------
 */
include (GLPI_ROOT . '/inc/includes.php');
include ('configOs.php');
include ('../inc/pdf/fpdf.php');
include ('../inc/qrcode/vendor/autoload.php');
global $DB;
Session::checkLoginUser();

if (!isset($CFG_GLPI)) {
    global $CFG_GLPI;
}
$url = $CFG_GLPI['url_base'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$url2 = "/front/ticket.form.php?id=" . $_GET['id'];

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$options = new QROptions([
    'version'      => 5,
    'eccLevel'     => QRCode::ECC_L,
    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
    'imageBase64'  => false,
]);

$qrData = (new QRCode($options))->render("$url$url2");

$tempDir = sys_get_temp_dir();
do {
    $tempQrFile = $tempDir . '/os_qr_' . bin2hex(random_bytes(8)) . '.png';
} while (file_exists($tempQrFile));

file_put_contents($tempQrFile, $qrData);

register_shutdown_function(function() use ($tempQrFile) {
    if (file_exists($tempQrFile)) {
        @unlink($tempQrFile);
    }
});

class PDF extends FPDF {
    public $qrFile = '';

    function Header() {
        include ('../inc/qrcode/vendor/autoload.php');
        global $EmpresaPlugin;
        global $CnpjPlugin;
        global $EnderecoPlugin;
        global $CidadePlugin;
        global $SitePlugin;
        global $TelefonePlugin;
        global $DataOs;
        global $OsId;
        global $EntidadeName, $EntidadeCep, $EntidadeEndereco, $EntidadeEmail, $EntidadePhone;

        // Logo
        $this->Cell(30);
        $this->Image('../pics/logo_os.png',8,15,45);
        // Title - Line 1: Company name & OS
        $this->Cell(20);
        $this->SetFont('Arial','B',11);
        $this->Cell(90,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'C');
        $this->Cell(53,5,"",0,0,'C');

        // Title - Line 2: Phone number & OS Number
        $this->Ln();
        $this->Cell(50);
        $this->SetFont('Arial','B',9);
        $this->Cell(90,3,utf8_decode(strip_tags(htmlspecialchars_decode("$TelefonePlugin"))),0,0,'C');
        $this->SetFont('Arial','B',9);
        $this->Cell(33,3,utf8_decode("OS Nº"),0,0,'C');
        $this->Cell(20,3,"",0,0,'C');

        // Title - Line 3: Company registration number & Os date
        $this->Ln();
        $this->SetFont('Arial','',9);
        $this->SetTextColor(0,0,0);
        $this->Cell(50);
        $this->Cell(90,3,"CNPJ: $CnpjPlugin",0,0,'C');
        $this->SetFont('Arial','B',11);
        $this->SetTextColor(250,0,0);
        $this->Cell(33,3,"$OsId",0,0,'C');
        $this->SetFont('Arial','',9);
        $this->SetTextColor(0,0,0);
        $this->Cell(20,3,"",0,0,'C');
        $this->Ln();
        // Title - Line 4: Company address
        $this->Cell(50);
        $this->SetFont('Arial','',9);
        $this->Cell(90,3,utf8_decode(strip_tags(htmlspecialchars_decode("$EnderecoPlugin - $CidadePlugin"))),0,0,'C');
        $this->Cell(33,3,"$DataOs",0,0,'C');
        $this->Cell(20,3,"",0,0,'C');
        // QR Code
        if (!empty($this->qrFile) && file_exists($this->qrFile)) {
            $this->Image($this->qrFile, 180, 10, 22);
        }
        // Title - Line 5: URL
        $this->Ln();
        $this->Cell(50);
        $this->SetFont('Arial','',8);
        $this->Cell(90,3,"$SitePlugin",0,0,'C');
        $this->Cell(33,3,"",0,0,'C');
        $this->Cell(20,3,"",0,0,'C');
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->qrFile = $tempQrFile;
$pdf->AliasNbPages();
$pdf->AddPage();

// Entity
$pdf->SetDrawColor(75,75,85);
$pdf->setFillColor(55,55,64);
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(190,5,utf8_decode("Dados do cliente"),1,0,'C',true);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(23,4,utf8_decode("Cliente"),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(167,4,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeName"))),1,0,'L');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Ln();
$pdf->Cell(23,4,utf8_decode(__("Requester")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(72,4,utf8_decode("$UserName"),1,0,'L');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(20,4,utf8_decode(__("Phone")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(75,4,"$EntidadePhone",1,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(23,4,utf8_decode(__("Address")),1,0,'L',true);
$pdf->SetFont('Arial','',6);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeEndereco"))),1,0,'L');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(20,4,"CEP",1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(75,4,"$EntidadeCep",1,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(23,4,utf8_decode(__("Email")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeEmail"))),1,0,'L');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(20,4,"CNPJ",1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(75,4,"$EntityRn",1,0,'L');
$pdf->Ln();

// Details
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(190,5,utf8_decode("Detalhes do chamado"),1,0,'C',true); 
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->Cell(23,4,utf8_decode(__("Title")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(167,4,utf8_decode(strip_tags(htmlspecialchars_decode("$OsNome"))),1,0);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(23,4,utf8_decode(__("Technician")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(167,4,utf8_decode(strip_tags(htmlspecialchars_decode("$OsResponsavel"))),1,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(23,4,utf8_decode(__("Start date")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(72,4,"$OsData",1,0,'L');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(20,4,utf8_decode(__("Closure")),1,0,'L',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(75,4,"$OsDataEntrega",1,0,'L');
$pdf->Ln();

// Items
if ( $ItensId != null ) {
    $pdf->SetFont('Arial','B',9);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(190,5,utf8_decode(__("Linked item")),1,0,'C',true);
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(23,4,utf8_decode(__("Name")),1,0,'L',true);
    $pdf->SetFont('Arial','',8);
    if ( $ItemType == 'Computer' ) {
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$ComputerName"))),1,0,'L');
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(23,4,utf8_decode(__("Serial Number")),1,0,'L',true);
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$ComputerSerial"))),1,0,'L');
        $pdf->Ln();
    } else if ( $ItemType == 'Monitor' ) {
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$MonitorName"))),1,0,'L');
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(20,4,utf8_decode(__("Serial Number")),1,0,'L',true);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(75,4,utf8_decode(strip_tags(htmlspecialchars_decode("$MonitorSerial"))),1,0,'L');
        $pdf->Ln();
    } else if ( $ItemType == 'Printer' ) {
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(72,4,utf8_decode(strip_tags(htmlspecialchars_decode("$PrinterName"))),1,0,'L');
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(20,4,utf8_decode(__("Serial Number")),1,0,'L',true);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(75,4,utf8_decode(strip_tags(htmlspecialchars_decode("$PrinterSerial"))),1,0,'L');
        $pdf->Ln();
    }
}

// Cost
if ($CustoTotal > 0) {
    $pdf->SetFont('Arial','B',9);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(190,5,utf8_decode(__("Cost")),1,0,'C',true);
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(23,4,utf8_decode(__("Total cost")),1,0,'L',true);
    $pdf->SetFont('Arial','',8);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(167,4,utf8_decode(strip_tags(htmlspecialchars_decode("$CustoTotalFinal"))),1,0,'L');
    $pdf->Ln();
}

// Description
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(190,5,utf8_decode(__("Description")),1,0,'C',true);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Multicell(190,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsDescricao"))),1,'J');

// Solution
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(190,5,utf8_decode(__("Solution")),1,0,'C',true);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);
if ( $OsSolucao == null ) {
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(190,10,utf8_decode("Descreva a solução:"),0,0);
    $pdf->Ln(0);
    $pdf->Cell(190,45,utf8_decode(""),1,0);
    $pdf->Ln();
} else {
    $pdf->MultiCell(190,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsSolucao"))),1,0);
}

// Signatures
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(190,5,utf8_decode("Assinaturas"),1,0,'C',true);
$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->Cell(190,40,"",'LRB',0,'L');
$pdf->SetY($pdf->GetY() +20);
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode("_______________________________________"),0,0,'C');
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode("_______________________________________"),0,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode(strip_tags(htmlspecialchars_decode("$OsResponsavel"))),0,0,'C');
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode(strip_tags(htmlspecialchars_decode("$UserName"))),0,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'C');
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,4,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeName"))),0,0,'C');

// Output PDF
$fileName = ''. $EmpresaPlugin .' - OS#'. $OsId .'.pdf';
$pdf->Output('I',$fileName);
?>