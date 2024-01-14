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
include ('../../../inc/includes.php');
include ('configOs.php');
include ('../inc/pdf/fpdf.php');
include ('../inc/qrcode/vendor/autoload.php');
global $DB;
Session::checkLoginUser();
// font size definition
$titleSize = 14;
$titleSizeCn = 8;
$fontSize = 7;
// Print details
$pdf = new FPDF('P','mm',array(95,70));
$pdf->AddPage();
$pdf->Ln(3);
// Logo
$pdf->Image('../pics/logo_os.png',15,0,40);
$pdf->Ln();
// Title
$pdf->SetFont('Arial','B',$titleSize);
$pdf->Cell(50,6,utf8_decode("OS Nº $OsId"),0,0,'C');
$pdf->Ln();
// Cabecalho
$pdf->SetFont('Arial','B',$titleSizeCn);
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,2.5,"CNPJ: $CnpjPlugin",0,0,'L');
$pdf->Ln();
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("FONE: $TelefonePlugin"))),0,0,'L');
$pdf->Ln();
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$EnderecoPlugin - $CidadePlugin"))),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,2.5,utf8_decode("RESPONSÁVEL: $OsResponsavel"),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,1,"-------------------------------------------------------------",0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,2.5,utf8_decode("CLIENTE: $EntidadeName"),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,2.5,utf8_decode("REQUERENTE: $UserName"),0,0,'L');
$pdf->Ln();
$pdf->Cell(50,2.5,utf8_decode("DATA: $DataOs"),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,1,"-------------------------------------------------------------",0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','b',$fontSize);
$pdf->Cell(50,2.5,utf8_decode("ITENS"),0,0,'L');
$pdf->Ln();
// Items
if ( $ItensId == null ) {
} else {
	$pdf->SetFont('Arial','',$fontSize);
  if ( $ItemType == 'Computer' ) {
    $pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$ComputerName - $ComputerSerial"))),0,0,'L');
		$pdf->Ln();
	} else if ( $ItemType == 'Monitor' ) {
		$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$MonitorName - $MonitorSerial"))),0,0,'L');
		$pdf->Ln();
	} else if ( $ItemType == 'Printer' ) {
		$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$PrinterName - $PrinterSerial"))),0,0,'L');
		$pdf->Ln();
	}
}
$pdf->SetFont('Arial','',$fontSize);
$pdf->Cell(50,1,"-------------------------------------------------------------",0,0,'L');
$pdf->Ln();
$pdf->Cell(50,2.5,"$SitePlugin",0,0,'C');
$pdf->Ln(10);

// QR Code
$url = $CFG_GLPI['url_base'];
$url2 = "/front/ticket.form.php?id=".$_GET['id']."";
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
$options = new QROptions([
  'version' => 5,
  'eccLevel' => QRCode::ECC_L,
  'outputType' => QRCode::OUTPUT_IMAGE_PNG,
  'imageBase64' => false
]);
file_put_contents('../pics/qr.png',(new QRCode($options))->render("$url$url2"));

// Setting the image size.
$width = 30;
$height = 0;

// Saves the current vertical position before adding the image.
$currentPosition = $pdf->GetY();

$pdf->Image('../pics/qr.png', $x = 20, $currentPosition + 2, $width, $height);
// Generating pdf file
$fileName = ''. $EmpresaPlugin .' - OS#'. $OsId .'.pdf';
$pdf->Output('I',$fileName);