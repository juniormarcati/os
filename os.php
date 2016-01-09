<!DOCTYPE html>
<head>
<title>DNK Informática - Ordem de Serviço</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/styles.termo.css" rel="stylesheet" type="text/css">
</head>

<?php
// inclue as configs necessarias do glpi
include ('../../inc/includes.php');
include ('../../config/config.php');
global $DB;
Session::checkLoginUser();

// select na tabela glpi_tickets onde tem as infos do ticket
$SEL_TICKET = "SELECT * FROM glpi_tickets WHERE id = '".$_GET['id']."'";
$RES_TICKET = $DB->query($SEL_TICKET);
$TICKET = $DB->fetch_assoc($RES_TICKET);
// coleta na tabela do ticket
$OS_ID = $_GET['id']; //id do ticket
$OS_NOME = $TICKET['name']; //titulo do ticket
$OS_DATA = $TICKET['date']; //data de abertura do ticket
$OS_DESCRICAO = $TICKET['content']; //pega a descricao do ticket
$OS_DATA_ENTREGA = $TICKET['due_date']; // data de entrega

//select no ticket_users para pegar as infos do cliente/empresa
$SEL_TICKET_USERS = "SELECT * FROM glpi_tickets_users WHERE tickets_id = '".$OS_ID."'";
$RES_TICKET_USERS = $DB->query($SEL_TICKET_USERS);
$TICKET_USERS = $DB->fetch_assoc($RES_TICKET_USERS);
// coletas na tabela glpi_ticket_users
$OS_USER_ID = $TICKET_USERS['users_id']; //pega a id do user para trazer as infos

//select na tabela glpi_users onde estarao as infos do cliente p/ OS
$SEL_USERS = "SELECT * FROM glpi_users WHERE id = '".$OS_USER_ID."'";
$RES_USERS = $DB->query($SEL_USERS);
$USERS = $DB->fetch_assoc($RES_USERS);
//colets da tabela glpi_users
$USER_NAME = $USERS['firstname']; // primeiro e segundo nome do usuario
$USER_CPF = $USERS['registration_number'];  // numero de registro dos usuarios (CPF)
$USER_PHONE = $USERS['mobile']; // celular do usuario
$USER_ENDERECO = $USERS['comment']; // endereco (comment)

// trazendo a id da entidade do ticket
$ENTIDADE_ID = $TICKET['entities_id'];
// select na tabela da entidade para trazer infos da empresa.
$SEL_EMPRESA = "SELECT * FROM glpi_entities WHERE id = '".$ENTIDADE_ID."'";
$RES_EMPRESA = $DB->query($SEL_EMPRESA);
$EMPRESA = $DB->fetch_assoc($RES_EMPRESA);
// coletando infos da empresa pela tabela de entidades
$ENTIDADE_NAME = $EMPRESA['name']; // nome da empresa
$ENTIDADE_CEP = $EMPRESA['postcode']; // cep da empresa
$ENTIDADE_ENDERECO = $EMPRESA['address']; // pega o endereco da empresa
$ENTIDADE_EMAIL = $EMPRESA['email']; // email da empresa
$ENTIDADE_PHONE = $EMPRESA['phonenumber']; // telefone da empresa
$ENTIDADE_CNPJ = $EMPRESA['comment']; // cnpj que fica no comment

// acessa tabela onde fica o email do cliente
$SEL_EMAIL = "SELECT * FROM glpi_useremails WHERE users_id = '".$OS_USER_ID."'";
$RES_EMAIL = $DB->query($SEL_EMAIL);
$EMAIL = $DB->fetch_assoc($RES_EMAIL);
// abaixo as informacoes que quero da tabela glpi_useremails
$USER_EMAIL = $EMAIL['email']; // armazena o email do usuario da info do cliente

// acesso na tabela de custos
$SEL_CUSTO = "SELECT * FROM glpi_ticketcosts WHERE tickets_id = '".$OS_ID."'";
$RES_CUSTO = $DB->query($SEL_CUSTO);
$CUSTO = $DB->fetch_assoc($RES_CUSTO);
// retirando informacoes de custo
$CUSTO_NOME = $CUSTO['name']; // nome do custo
$CUSTO_TOTAL = $CUSTO['cost_fixed']; // custo total do servico
// importante: busquei na tabela glpi_tickets para somar tempo mesmo sem custo
$CUSTO_TEMPO = $CUSTO['actiontime']; // tempo do chamado
// formula para retirar zeros desnecessaarios no valor
$CUSTO_TOTAL_FINAL = number_format($CUSTO_TOTAL, 2, ',', ' ');
// formula para transformar os segundos em HH:MM:SS
$seconds = $CUSTO_TEMPO;
$hours = floor($seconds / 3600);
$seconds -= $hours * 3600;
$minutes = floor($seconds / 60);
$seconds -= $minutes * 60;
?>
	<body>
	<table width="200" border="1" cellpadding="0" cellspacing="0">
	<tr>
	<td><table width="688" border="1">
	<tr>
<!-- tabela do topo -->
	<td width="200" colspan="2"><table width="682" border="0" cellpadding="0" cellspacing="0">
<!-- tabela do logotipo -->
	<tr><td width="131" height="119" align="center" valign="middle"><img src="./pics/logo_os.jpg" width="119" height="58" align="absmiddle"></td>
<!-- tabela do titulo -->
	<td align="center"><p><font size=4>DNK Informática</font></p>
	<p><font size=2>Rua Alfredo Pujol, 345 - Centro - (19) 3913.7909 - ITAPIRA/SP</font></p>
<!-- tabela do titulo segunda linha -->
	<p width="131" height="119" align="center"><font size=6>OS Nº<b>**<?php echo $OS_ID;?>**</font></b></p></tr>
<!-- fecha a tabela de titulo -->
	</table></td>
<!-- segunda tabela -->
	<tr><td colspan="2" bgcolor="#1f3f65"><center><b><font color="#FFFFFF">DADOS DO CLIENTE</font></b></center></td></tr>
	<tr><td colspan="2" height="25"><b>NOME: </b><?php echo ($ENTIDADE_NAME) ?></td> </tr>
	<tr><td colspan="2" height="25"><b>ENDEREÇO: </b><?php echo ($ENTIDADE_ENDERECO)?></td></tr>
	<tr><td height="25"><b>CNPJ/CPF: </b><?php echo ($ENTIDADE_CNPJ)?></td><td height="25"><b>CEP: </b><?php echo ($ENTIDADE_CEP)?></td></tr>
	<tr><td height="25"><b>E-MAIL: </b><?php echo ($ENTIDADE_EMAIL)?></td><td height="25"><b>TELEFONE: </b><?php echo ($ENTIDADE_PHONE)?></td></tr>
<!-- tabela OS -->
	<tr><td colspan="2" bgcolor="#1f3f65"><center><b><font color="#FFFFFF">DETALHES DA ORDEM DE SERVIÇO</font></b></center></td></tr>
	<tr><td colspan="2"><b>TITULO:</b> <?php echo $OS_NOME;?></td></tr>
	<tr><td><b>DATA ABERTURA: </b><?php echo ($OS_DATA);?></td><td><b>DATA DE ENTREGA: </b><?php echo ($OS_DATA_ENTREGA);?></td></tr>
	</table>
	</td>
	</tr>
	<tr>
	<td height="180" colspan="2" valign="top"><span class="mens_erro">DESCRIÇÃO:<br><?php echo ($OS_DESCRICAO);?></span></td>
	</tr>
        <tr><td colspan="2" bgcolor="#1f3f65"><center><b><font color="#FFFFFF">DETALHES DE CUSTO</font></b></center></td></tr>
        <tr><td height="25"><b>SERVIÇO: </b><?php echo ($CUSTO_NOME)?></td></tr>
	<tr><td height="25"><b>TOTAL: </b>R$ <?php echo ($CUSTO_TOTAL_FINAL)?></td></tr>
	<tr><td height="25"><b>TEMPO: </b><?php echo ("$hours h $minutes m $seconds s")?></td></tr>
	<td height="220" colspan="2" align="center"><table border="0" cellspacing="0" cellpadding="0" width="600">
	<table width="600" border="0" cellpadding="0" cellspacing="0">
	<p></p>
	<center><tr><td><p>___________________________________</p></td><td><p>______________________________</p></td></tr></center>
	<center><tr><td><p><?php echo ($ENTIDADE_NAME);?></p><td><center><p>DNK Informática</p></td></tr></center>
	</table>
	</body>

<!-- inicia a impressao -->
<script type="text/javascript">
window.onload = function() { window.print(); }
</script>
