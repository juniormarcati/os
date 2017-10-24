<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/styles.termo.css" rel="stylesheet" type="text/css">
<link href="css/styles.css" rel="stylesheet" type="text/css">
</head>
<?php 
include ('../../../inc/includes.php');
include ('../../../config/config.php');
global $DB;
Session::checkLoginUser();
Html::header('OS', "", "plugins", "os");
echo Html::css($CFG_GLPI["root_doc"]."/css/styles.css");
if (isset($_SESSION["glpipalette"])) {
	echo Html::css($CFG_GLPI["root_doc"]."/css/palettes/".$_SESSION["glpipalette"].".css");
}
$SelPlugin = "SELECT * FROM glpi_plugin_os_config";
$ResPlugin = $DB->query($SelPlugin);
$Plugin = $DB->fetch_assoc($ResPlugin);
$EmpresaPlugin = $Plugin['name'];
$EnderecoPlugin = $Plugin['address'];
$TelefonePlugin = $Plugin['phone'];
$CidadePlugin = $Plugin['city'];
$CorPlugin = $Plugin['color'];
$CorTextoPlugin = $Plugin['textcolor'];
<<<<<<< HEAD
$SelTicket = "SELECT * FROM glpi_tickets WHERE id = '".$_GET['id']."'";
=======
$SelTicket = "SELECT date,name,content,date_format(date, '%d/%m/%Y %H:%i') AS DataInicio,due_date,date_format(solvedate, '%d/%m/%Y %H:%i') AS DataFim,solution,entities_id FROM glpi_tickets WHERE id = '".$_GET['id']."'";
>>>>>>> 0.0.8a
$ResTicket = $DB->query($SelTicket);
$Ticket = $DB->fetch_assoc($ResTicket);
$OsId = $_GET['id'];
$OsNome = $Ticket['name'];
<<<<<<< HEAD
$SelDataInicial = "SELECT date,date_format(date, '%d/%m/%Y %H:%i') AS DataInicio FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$ResDataInicial = $DB->query($SelDataInicial);
$DataInicial = $DB->fetch_assoc($ResDataInicial);
$OsData = $DataInicial['DataInicio'];
$OsDescricao = $Ticket['content'];
$SelDataFinal = "SELECT due_date,date_format(solvedate, '%d/%m/%Y %H:%i') AS DataFim FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$ResDataFinal = $DB->query($SelDataFinal);
$DataFinal = $DB->fetch_assoc($ResDataFinal);
$OsDataEntrega = $DataFinal['DataFim'];
=======
$OsData = $Ticket['DataInicio'];
$OsDescricao = $Ticket['content'];
$SelItemsLista = "SELECT group_concat(glpi_computers.name SEPARATOR '<br>') AS computers_names FROM `glpi_items_tickets` INNER JOIN glpi_computers ON glpi_items_tickets.items_id = glpi_computers.id WHERE tickets_id = '".$_GET['id']."'";
$ResItemsLista = $DB->query($SelItemsLista);
$ItemsLista = $DB->fetch_assoc($ResItemsLista);
$Equipamentos = $ItemsLista['computers_names'];
$SelListaPrinters = "SELECT group_concat(name SEPARATOR '<br>') AS printers_names FROM `glpi_items_tickets` INNER JOIN glpi_printers ON glpi_items_tickets.items_id = glpi_printers.id WHERE tickets_id = '".$_GET['id']."'";
$ResListaPrinters = $DB->query($SelListaPrinters);
$ListaPrinters = $DB->fetch_assoc($ResListaPrinters);
$Impressoras = $ListaPrinters ['printers_names'];
$OsDataEntrega = $Ticket['DataFim'];
>>>>>>> 0.0.8a
$OsSolucao = $Ticket['solution'];
$SelTicketUsers = "SELECT * FROM glpi_tickets_users WHERE tickets_id = '".$OsId."'";
$ResTicketUsers = $DB->query($SelTicketUsers);
$TicketUsers = $DB->fetch_assoc($ResTicketUsers);
$OsUserId = $TicketUsers['users_id'];
$SelIdOsResponsavel = "SELECT users_id FROM glpi_tickets_users WHERE tickets_id = '".$OsId."' AND type = 2";
$ResIdOsResponsavel = $DB->query($SelIdOsResponsavel);
$IdOsResponsavel = $DB->fetch_assoc($ResIdOsResponsavel);
$SelOsResponsavelName = "SELECT * FROM glpi_users WHERE id = '".$IdOsResponsavel['users_id']."'";
$ResOsResponsavelName = $DB->query($SelOsResponsavelName);
$OsResponsavelFull = $DB->fetch_assoc($ResOsResponsavelName);
$OsResponsavel = $OsResponsavelFull['firstname']. " " .$OsResponsavelFull['realname'];
$EntidadeId = $Ticket['entities_id'];
$SelEmpresa = "SELECT * FROM glpi_entities WHERE id = '".$EntidadeId."'";
$ResEmpresa = $DB->query($SelEmpresa);
$Empresa = $DB->fetch_assoc($ResEmpresa);
$EntidadeName = $Empresa['name'];
$EntidadeCep = $Empresa['postcode'];
$EntidadeEndereco = $Empresa['address'];
$EntidadeEmail = $Empresa['email'];
$EntidadePhone = $Empresa['phonenumber'];
$EntidadeCnpj = $Empresa['comment'];
$SelEmail = "SELECT * FROM glpi_useremails WHERE users_id = '".$OsUserId."'";
$ResEmail = $DB->query($SelEmail);
$Email = $DB->fetch_assoc($ResEmail);
$UserEmail = $Email['email'];
$SelCustoLista = "SELECT actiontime, sec_to_time(actiontime) AS Hora,name,cost_time,cost_fixed,cost_material,FORMAT(cost_time,2,'de_DE') AS cost_time2, FORMAT(cost_fixed,2,'de_DE') AS cost_fixed2, FORMAT(cost_material,2,'de_DE') AS cost_material2, SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS CustoItem FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."' GROUP BY id";
$ResCustoLista = $DB->query($SelCustoLista);
$SelCusto = "SELECT SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS SomaTudo FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
$ResCusto = $DB->query($SelCusto);
$Custo = $DB->fetch_assoc($ResCusto);
$CustoTotal =  $Custo['SomaTudo'];
$CustoTotalFinal = number_format($CustoTotal, 2, ',', ' ');
$SelTempoTotal = "SELECT SUM(actiontime) AS TempoTotal FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
$ResTempoTotal = $DB->query($SelTempoTotal);
$TempoTotal = $DB->fetch_assoc($ResTempoTotal);
$seconds = $TempoTotal['TempoTotal'];
$hours = floor($seconds / 3600);
$seconds -= $hours * 3600;
$minutes = floor($seconds / 60);
$seconds -= $minutes * 60;
?>

<body>
<!-- inicio dos botoes -->
<div id="botoes" style="width:55%; background:#fff; margin:auto; padding-bottom:10px;"> 
	<!--<input type="button" class="botao" name="configurar" value="Configurar" onclick="window.location.href='./index.php'"> -->
	<p></p>
	<form action="os.php" method="get">	
	<input type="text" name="id" value="Digite a ID" onfocus="if (this.value=='Digite a ID') this.value='';" onblur="if (this.value=='') this.value='Digite a ID'" />
	<input class="submit" type="submit" value="Enviar">
	</form>
	<p></p>
	<a href="#" class="vsubmit" onclick="window.print();"> Imprimir </a>
	<a href='os_cli.php?id=<?php echo $OsId; ?>' class="vsubmit"> Cliente </a>
	<a href='os.php?id=<?php echo $OsId; ?>' class="vsubmit"> Empresa </a>
	<a href="index.php" class="vsubmit" style="float:right;"> Configurar </a>
	<p></p>
</div>
<!-- inicio das tabelas -->
<table style="width:55%; background:#fff; margin:auto;" border="1" cellpadding="0" cellspacing="0"> 
<tr>
<td style="padding: 0px !important;" >
<table style="width:100%; background:#fff;" border="1">
<tr>
<td width="400" colspan="2">
<table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
<!-- tabela do logotipo -->
<tr><td height="119" valign="middle" style="width:25%; text-align:center; margin:auto;"><img src="./img/logo_os.png" width="119" height="58" align="absmiddle"></td>
<!-- tabela do titulo -->
<td style="text-align:center;"><p><font size="4"><?php echo ($EmpresaPlugin);?></font></p>
<p><font size="2"><?php echo ("$EnderecoPlugin - $CidadePlugin - $TelefonePlugin"); ?></font></p>
<!-- tabela do titulo segunda linha -->
<p width="131" height="70"><font size="6"> OS Nº &nbsp;<b><?php echo $OsId;?> </font></b></p></tr>
<!-- fecha a tabela de titulo -->
</table></td>
<!-- segunda tabela -->
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">DADOS DO CLIENTE</font></b></center></td> </tr>
<tr><td width="50%"><b>Empresa: </b><?php echo ($EntidadeName) ?></td><td ><b>Telefone: </b><?php echo ($EntidadePhone)?></td></tr>
<tr><td width="50%"><b>Endereço: </b><?php echo ($EntidadeEndereco)?></td><td><b>E-Mail: </b><?php echo ($EntidadeEmail)?></td></tr>
<tr><td width="50%"><b>CNPJ: </b><?php echo ($EntidadeCnpj)?></td><td ><b>CEP: </b><?php echo ($EntidadeCep)?></td></tr>
<!-- tabela OS -->
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">DETALHES DA ORDEM DE SERVIÇO</font></b></center></td></tr>
<tr><td width="50%"><b>Título:</b> <?php echo $OsNome;?></td><td width="50%"><b>Responsável:</b> <?php echo $OsResponsavel;?></td></tr>
<tr><td width="50%"><b>Data/Hora de Início: </b><?php echo ($OsData);?></td><td><b>Data/Hora de Término: </b><?php echo ($OsDataEntrega);?>
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">DESCRIÇÃO</font></b></center></td></tr>
<tr><td height="150" colspan="2" valign="top" style="padding:10px;"><?php echo html_entity_decode($OsDescricao);?></td></tr>
<<<<<<< HEAD
=======
<tr><td colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><center><b><font color="<?php echo $CorTextoPlugin; ?>">EQUIPAMENTOS</font></b></center></td></tr>
<tr><td colspan="2" valign="top" style="padding:10px;"><?php echo html_entity_decode($Equipamentos."<br>".$Impressoras);?></td></tr>
>>>>>>> 0.0.8a
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
			echo '<table align=center width=800px border=0>';
			echo '<tr>';
			echo '<td><b>DESCRIÇÃO</b></td>';
			echo '<td><b>CUSTO FIXO</b></td>';
			echo '<td><b>CUSTO DE MATERIAL</b></td>';
			echo '<td><b>CUSTO POR TEMPO</b></td>';
			echo '<td><b>DURAÇÃO</b></td>';
			echo '<td><b>CUSTO</b></td>';
			echo '</tr>';
			while($Escrita = $DB->fetch_assoc($ResCustoLista)){
				echo '<tr>';
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
			echo '<table align=center width=800px height=0 border=0>';
			echo '<td><p style=margin-top:0px;margin-bottom:0px align=left><b>DURAÇÃO TOTAL:</b> '.$hours.'h '.$minutes.'min '.$seconds.'seg</p></td>';
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
<br />
<br />
<table width="688" border="0" align="center" cellspacing="0">
<tr align="center"><td style="text-align:center;">____________________________________</td><td style="text-align:center;">_____________________________________</td></tr>
<tr align="center"><td style="text-align:center;" ><?php echo ($EntidadeName);?></td><td style="text-align:center;" ><?php echo ($EmpresaPlugin);?></td></tr>
</table>
</table> 
<!-- estilo do botao para nao aparecer em impressao --> 
<style media="print">
</style>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 0.0.8a
