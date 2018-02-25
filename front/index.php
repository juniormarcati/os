<?php
include ('../../../inc/includes.php');
include ('../../../config/config.php');
global $DB;
Session::checkLoginUser();
Html::header('OS', "", "plugins", "os");
?>
<html>
<head>
<title>Configuração: Ordem de Serviço</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/styles.css" rel="stylesheet" type="text/css">
<?php
      echo Html::css($CFG_GLPI["root_doc"]."/css/styles.css");
      if (isset($_SESSION["glpipalette"])) {
         echo Html::css($CFG_GLPI["root_doc"]."/css/palettes/".$_SESSION["glpipalette"].".css");
      }
$SelPlugin = "SELECT * FROM glpi_plugin_os_config";
$ResPlugin = $DB->query($SelPlugin);
$Plugin = $DB->fetch_assoc($ResPlugin);
$EmpresaPlugin = $Plugin['name'];
$CnpjPlugin = $Plugin['cnpj'];
$EnderecoPlugin = $Plugin['address'];
$TelefonePlugin = $Plugin['phone'];
$CidadePlugin = $Plugin['city'];
$CorPlugin = $Plugin['color'];
$CorTextoPlugin = $Plugin['textcolor'];
$SitePlugin = $Plugin['site'];
?>
</head>
<body>
<div id="container" style="background:#fff; margin:auto; width:60%; border: 1px solid #ddd; padding-bottom:25px;">
<center>
<table width="600" border="0" cellpadding="10" cellspacing="10">
<tr><td><div align="center"><a href="http://glpi-os.sourceforge.net" target="GLPI_OS"><img src="../pics/logotipo.png" alt="GLPI_OS" border="none"/></a></div></td></tr>
<tr><td><div align="center"><h1>GLPI_OS: Configuração</h1></div></td></tr>
</table> 
</center>
<center>
<tr><td><div align="center"><h3>PASSO 1 - CABEÇALHO </h3></div></td></tr>
<tr><td><div align="center"><h4>Informações da Empresa.</h4></div></td></tr>
<table width="500" border="0" cellpadding="0" cellspacing="0">
<form action="config.php" method="get">
<pre>
<tr><td>Nome da sua empresa: </td><td><input type="text" size="35" maxlength="256" name="name_form" value="<?php print $EmpresaPlugin; ?>"></td></tr>
<tr><td>CNPJ: </td><td><input type="text" size="35" maxlength="256" name="cnpj_form" value="<?php print $CnpjPlugin; ?>"></td></tr>
<tr><td>Endereço de sua empresa: </td><td><input type="text" size="35" maxlength="256" name="address_form" value="<?php print $EnderecoPlugin; ?>"></td></tr>
<tr><td>Telefone: </td><td><input type="text" size="35" maxlength="256" name="phone_form" value="<?php print $TelefonePlugin; ?>"></td></tr>
<tr><td>Cidade/Estado: </td><td><input type="text" size="35" maxlength="256" name="city_form" value="<?php print $CidadePlugin; ?>"></td></tr>
<tr><td>Site: </td><td><input type="text" size="35" maxlength="256" name="site_form" value="<?php print $SitePlugin; ?>"></td></tr>
</table>
<tr><td><div align="center"><h4>Cores - Personalize com as cores da sua empresa.</h4></div></td></tr>
<table width="500" border="0" cellpadding="0" cellspacing="0">
<tr><td>Cor do texto: </td><td><input type="text" size="15" maxlength="256" name="textcolor_form" value="#FFFFFF"<?php print $CorTextoPlugin; ?>"></td></tr><td>
<tr><td>Cor padrão: </td><td><input type="text" size="15" maxlength="256" name="color_form" value="#000000" <?php print $CorPlugin; ?>"></td></tr>
<tr><td colspan="2" style="text-align:center;"><input type="submit" class="submit" value="Salvar" name="enviar"></td></tr>
<tr><td>
</pre>
</table>
</form>
</center>
<center>
<tr><td><div align="center"><h3>PASSO 2 - Logotipo da OS</h3></div></td></tr>
<tr><td><div align="center"><h4>Faça UPLOAD do logotipo (50x50) que será utilizado na OS. (obs: pasta pics precisa ter permissão de escrita.)</h4></div></td></tr>
<table width="500" border="0" cellpadding="0" cellspacing="0">
<form method="post" enctype="multipart/form-data" action="recebeLogo.php">
<br/>
Selecione uma imagem: <input name="arquivo" type="file" required="required" />
<br />
</td></tr>
<tr><td>
<tr><td colspan="2" style="text-align:center;"><input type="submit" class="submit" value="Enviar" name="enviar"></td></tr>
</form>
</td></tr>
</table>
<table width="500" border="0" cellpadding="0" cellspacing="0"></table>
<br><a href="#" class="vsubmit" onclick="history.back();"> Voltar </a>
</center>
</div>
</body>
</html>
<?php  
Html::footer();
?>