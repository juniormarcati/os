<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2022 by Junior Marcati
   https://github.com/juniormarcati/glpi_os
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
   @copyright Copyright (c) 2016-2022 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/glpi_os
   @since     2016
   ------------------------------------------------------------------------
 */
include ('../../../inc/includes.php');
include ('configOs.php');
include ('../inc/pdf/fpdf.php');
include ('../inc/qrcode/vendor/autoload.php');
global $DB;
Session::checkLoginUser();
// Print details
$pdf = new FPDF('P','mm',array(95,70));
$pdf->AddPage();
$pdf->Ln(7);
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
$pdf->Image('../pics/qr.png',20,55,30);
// Logo
$pdf->Image('../pics/logo_os.png',15,3,40);
$pdf->Ln();
// Cabecalho
$pdf->SetFont('Arial','B',7);
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'C');
$pdf->Ln();
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$TelefonePlugin"))),0,0,'C');
$pdf->SetFont('Arial','',6);
$pdf->Ln();
$pdf->Cell(50,2.5,"CNPJ: $CnpjPlugin",0,0,'C');
$pdf->Ln();
$pdf->Cell(50,2.5,utf8_decode(strip_tags(htmlspecialchars_decode("$EnderecoPlugin - $CidadePlugin"))),0,0,'C');
$pdf->Ln();
$pdf->Cell(50,2.5,"$SitePlugin",0,0,'C');
$pdf->Ln(5);
// OS details
$pdf->SetFont('Arial','B',14);
$pdf->Cell(50,6,utf8_decode("OS Nº $OsId"),0,0,'C');
$pdf->Ln(7);
$pdf->SetFont('Arial','B',5);
$pdf->Cell(50,2,utf8_decode("Empresa: "),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',5);
$pdf->Cell(50,2,utf8_decode("$EntidadeName"),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','B',5);
$pdf->Cell(50,2,utf8_decode("Requerente: "),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',5);
$pdf->Cell(50,2,utf8_decode("$UserName"),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','B',5);
$pdf->Cell(50,2,utf8_decode("Responsável: "),0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','',5);
$pdf->Cell(50,2,utf8_decode("$OsResponsavel"),0,0,'L');
$pdf->Ln(15);
// Generating pdf file
$fileName = ''. $EmpresaPlugin .' - OS#'. $OsId .'.pdf';
$pdf->Output('I',$fileName);