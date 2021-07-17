<?php

/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2021 by Junior Marcati
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
   @copyright Copyright (c) 2016-2021 OS Plugin Development team
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
class PDF extends FPDF {
	// Page header
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
		// Logo
		$this->Cell(30);
		$this->Image('../pics/logo_os.png',10,15,45);
		// Title - Line 1: Company name & OS
		$this->Cell(20);
		$this->SetFont('Arial','B',12);
		$this->Cell(90,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'C');
		$this->Cell(20,5,"",0,0,'C');
		$this->Cell(33,5,"",0,0,'C');
		// Title - Line 2: Phone number & OS Number
		$this->Ln();
		$this->Cell(50);
		$this->Cell(90,5,utf8_decode(strip_tags(htmlspecialchars_decode("$TelefonePlugin"))),0,0,'C');
		$this->SetFont('Arial','B',12);
		$this->Cell(33,5,utf8_decode("OS Nº"),0,0,'C');
		$this->Cell(20,5,"",0,0,'C');
		// Title - Line 3: Company registration number & Os date
		$this->Ln();
		$this->SetFont('Arial','',10);
		$this->SetTextColor(1,0,0);
		$this->Cell(50);
		$this->Cell(90,5,"CNPJ: $CnpjPlugin",0,0,'C');
		$this->SetFont('Arial','B',14);
		$this->SetTextColor(250,0,0);
		$this->Cell(33,5,"$OsId",0,0,'C');
		$this->SetFont('Arial','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(20,5,"",0,0,'C');
		$this->Ln();
		// Title - Line 4: Company address
		$this->Cell(50);
		$this->Cell(90,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EnderecoPlugin - $CidadePlugin"))),0,0,'C');
		$this->Cell(33,5,"$DataOs",0,0,'C');
		$this->Cell(20,5,"",0,0,'C');
		$this->Image('../pics/qr.png',180,10,22);
		// Title - Line 5: URL
		$this->Ln();
		$this->Cell(50);
		$this->Cell(90,5,"$SitePlugin",0,0,'C');
		$this->Cell(33,5,"",0,0,'C');
		$this->Cell(20,5,"",0,0,'C');
		$this->Ln(7);
	}
// Page footer
function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Page number
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
// Entity data
$pdf->setFillColor(230,230,230); 
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("DADOS DA EMPRESA"),1,1,'C',1);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,utf8_decode("Empresa:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(167,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeName"))),1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,utf8_decode("Endereço:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(167,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeEndereco - $EntidadeCep"))),1,0,'L');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,"CNPJ:",1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(77,5,"$EntidadeCnpj",1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,5,utf8_decode("Telefone:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(70,5,"$EntidadePhone",1,0,'L');
$pdf->Ln();
// User data
$pdf->setFillColor(230,230,230); 
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("DADOS DO USUÁRIO"),1,1,'C',1);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,utf8_decode("Requerente:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(77,5,"$UserName",1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,5,utf8_decode("Telefone:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(70,5,"$UserTelefone $UserMobile",1,0,'L');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,utf8_decode("CPF:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(77,5,"$UserCpf",1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,5,utf8_decode("E-mail:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(70,5,"$UserEmail",1,0,'L');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(23,5,utf8_decode("Endereço:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(167,5,utf8_decode(strip_tags(htmlspecialchars_decode("$UserEndereco"))),1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Ln();

// SO details
$pdf->setFillColor(230,230,230);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("DETALHES DA ORDEM DE SERVIÇO"),1,1,'C',1);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(14,5,utf8_decode("Título:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(176,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsNome"))),1,0,'L');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,5,utf8_decode("Responsável:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(165,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsResponsavel"))),1,0,'L');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(14,5,utf8_decode("Início:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(90,5,"$OsData",1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(26,5,utf8_decode("Conclusão:"),1,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,5,"$OsDataEntrega",1,0,'L');
$pdf->Ln();
// Description
$pdf->setFillColor(230,230,230);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("DESCRIÇÃO"),1,1,'C',1);
$pdf->Cell(1);
$pdf->SetFont('Arial','',10);
$pdf->Multicell(190,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsDescricao"))),1,J);
// Solution
$pdf->setFillColor(230,230,230);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("SOLUÇÃO"),1,1,'C',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(1);
// Lines if solution is empty
if ( $OsSolucao == null ) {
	$pdf->Ln();
	$pdf->Cell(1);
	$pdf->Cell(190,55,utf8_decode("Descreva a solução:"),1,0,'L',0);
	$pdf->Ln();
} else {
	$pdf->Ln();
	$pdf->Cell(1);
	$pdf->MultiCell(190,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsSolucao"))),1,J);
}
// Signatures
$pdf->setFillColor(230,230,230);
$pdf->Cell(1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,7,utf8_decode("ASSINATURAS"),1,1,'C',1);
// Signatures Lines
$pdf->Cell(1);
$pdf->Cell(190,40,"",1,0,'L');
$pdf->SetY($pdf->GetY() +20);
$pdf->Cell(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(95,5,utf8_decode("_______________________________________"),0,0,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(95,5,utf8_decode("_______________________________________"),0,0,'C');
$pdf->Ln();
$pdf->Cell(1);
$pdf->SetFont('Arial','',7);
$pdf->Cell(95,5,utf8_decode(strip_tags(htmlspecialchars_decode("$OsResponsavel"))),0,0,'C');
$pdf->Cell(95,5,utf8_decode(strip_tags(htmlspecialchars_decode("$UserName"))),0,0,'C');
$pdf->Ln();
$pdf->Cell(1);
$pdf->Cell(95,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EmpresaPlugin"))),0,0,'C');
$pdf->Cell(95,5,utf8_decode(strip_tags(htmlspecialchars_decode("$EntidadeName"))),0,0,'C');


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
// Output PDF
$fileName = ''. $EmpresaPlugin .' - OS#'. $OsId .'.pdf';
$pdf->Output('I',$fileName);
?>