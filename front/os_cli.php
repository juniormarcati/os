<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/styles.termo.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
</head>
<?php 
include ('../../../inc/includes.php');
include ('../../../config/config.php');
include ('configOs.php');
global $DB;
Session::checkLoginUser();
Html::header('OS', "", "plugins", "os");
echo Html::css($CFG_GLPI["root_doc"]."/css/styles.css");
if (isset($_SESSION["glpipalette"])) {
	echo Html::css($CFG_GLPI["root_doc"]."/css/palettes/".$_SESSION["glpipalette"].".css");
}
?>
<body>
<div id="botoes" style="width:55%; background:#fff; margin:auto; padding-bottom:10px;"> 
	<a href="#" class="vsubmit" onclick="window.print();"> Imprimir </a>
	<a href="#" class="vsubmit" onclick="history.back();"> Voltar </a>
	<a href="index.php" class="vsubmit" style="float:right;"> Configurar </a>
</div>
<table style="width:55%; background:#fff; margin:auto;" border="0" cellpadding="0" cellspacing="0"> 
<tr>
<td style="padding: 0px !important;" >
<table style="width:100%; background:#fff;" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="100" colspan="3">
<table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
<tr><td height="60" valign="middle" style="width:20%; text-align:center; margin:auto;"><img src="../pics/logo_os.png" width="50" height="50" align="absmiddle"></td>
<td style="text-align:center;"><font size="3"><?php echo ($EmpresaPlugin);?></font><br />
<font size="1">
<?php
	if ( $CnpjPlugin == null ) {
		echo " ";
	} else {
		echo "CNPJ: $CnpjPlugin";
	}
?>
</font><br />
<font size="1"><?php echo ("$EnderecoPlugin - $CidadePlugin"); ?></font><br />
<font size="1"><?php echo ("$SitePlugin - $TelefonePlugin"); ?></font><br />
<td height="50" valign="middle" style="width:20%; text-align:center;"><font size="4"> OS Nº &nbsp;<b></font><font size="5" color=#FF0000><?php echo $OsId;?></b></font><br \><font size="1"><?php echo $DataOs;?></font></td></tr>
</table></td>
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important"><center><b><font color="<?php echo $CorTextoPlugin; ?>">DADOS DO CLIENTE</font></b></center></td> </tr>
<tr><td width="50%"><b>Nome: </b><?php echo ($UserName) ?></td><td ><b>Telefone: </b><?php echo ($UserTelefone)?></td></tr>
<tr><td width="50%"><b>Endereço: </b><?php echo ($UserEndereco)?></td><td><b>E-Mail: </b><?php echo ($UserEmail)?></td></tr>
<tr><td width="50%"><b>CPF: </b><?php echo ($UserCpf)?></td><td ><b>CEP: </b><?php echo ($UserCep)?></td></tr>
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">DETALHES DA ORDEM DE SERVIÇO</font></b></center></td></tr>
<tr><td width="50%"><b>Título:</b> <?php echo $OsNome;?></td><td width="50%"><b>Responsável:</b> <?php echo $OsResponsavel;?></td></tr>
<tr><td width="50%"><b>Data/Hora de Início: </b><?php echo ($OsData);?></td><td><b>Data/Hora de Término: </b><?php echo ($OsDataEntrega);?></td></tr>
<tr>
<td>
<?php
	if ( $Locations == null ) {
		echo "</tr></td>";
	} else {
		echo "<b>Localização: </b>$Locations";
		echo "</tr></td>";
	}
?>
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">DESCRIÇÃO</font></b></center></td></tr>
<tr><td height="90" colspan="2" valign="top" style="padding:10px;"><?php echo html_entity_decode($OsDescricao);?></td></tr>
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">SOLUÇÃO</font></b></center></td></tr>
<tr><td height="5" colspan="2" valign="top" style="padding:10px;">
<?php 
	if ( $OsSolucao == null ) {
		echo "<br><hr><br><hr><br><hr><br>";
	} else {
		echo html_entity_decode($OsSolucao);
	}
?>
</td></tr>
<?php 
	if ( $CustoTotalFinal == 0 ) {
		echo "</tr>";
		} else {
			echo "<tr><td colspan=2 style=background-color:$CorPlugin><center><b><font color=$CorTextoPlugin >DETALHES DE CUSTO</font></b></center></tr></td>";
			echo '<td height="80" colspan="2" valign="top" style="padding:10px;">';
			echo '<table align=center width=100% height=0 border=0 cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><b>DESCRIÇÃO</b></td>';
			echo '<td><b>CUSTO FIXO</b></td>';
			echo '<td><b>CUSTO DE MATERIAL</b></td>';
			echo '<td><b>CUSTO POR TEMPO</b></td>';
			echo '<td><b>DURAÇÃO</b></td>';
			echo '<td><b>CUSTO</b></td>';
			echo '</tr>';
			while($Escrita = $DB->fetch_assoc($ResCustoLista)){
				echo '<td>'.$Escrita['name'].'</td>';
				echo '<td>R$ '.$Escrita['cost_fixed2'].'</td>';
				echo '<td>R$ '.$Escrita['cost_material2'].'</td>';
				echo '<td>R$ '.$Escrita['cost_time2'].'</td>';
				echo '<td>'.$Escrita['Hora'].'</td>';
				echo '<td> R$ '; 
				echo number_format($Escrita['CustoItem'], 2, ',', '.');
				echo '</td>'; 
				echo '</tr>';
			}
			echo '<table align=center width=100% height=0 border=0 cellpadding="0" cellspacing="0">';
			echo '<td><p style=margin-top:0px;margin-bottom:0px align=left><b>DURAÇÃO TOTAL:</b> '.$hours.'h '.$minutes.'m '.$seconds.'s</p></td>';
			echo '<tr>';
			echo '<td><p style=margin-top:0px;margin-bottom:0px align=left><b>CUSTO TOTAL:</b> R$ '.$CustoTotalFinal.'</td></p>';
			echo '</table>';
			echo '</table>';
			echo '<table style=width:100% align=center border=0>';
			echo '</tr>';
		}
?>
<table style="width:100%; background:#fff;" border="0">
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">ASSINATURAS</font></b></center></tr></td>
</table>
<br />
<br />
<br />
<table width="688" border="0" align="center" cellspacing="0">
<tr align="center"><td style="text-align:center; width:50%;"> <hr></td><td style="text-align:center; width:50%;"><hr></td></tr>
<tr align="center"><td style="text-align:center;" ><?php echo ($UserName);?></td><td style="text-align:center;" ><?php echo ($EmpresaPlugin);?></td></tr>
</table>
</table> 
<style media="print">
</style>
</body>
</html>
<?php  
Html::footer();
?>