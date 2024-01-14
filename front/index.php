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
global $DB;
Session::checkRight("config", UPDATE);
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
$Plugin = $DB->fetchAssoc($ResPlugin);
$EmpresaPlugin = $Plugin['name'];
$CnpjPlugin = $Plugin['cnpj'];
$EnderecoPlugin = $Plugin['address'];
$TelefonePlugin = $Plugin['phone'];
$CidadePlugin = $Plugin['city'];
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
<tr><td>Site: </td><td><input type="text" size="35" maxlength="256" name="site_form" value="<?php echo $SitePlugin; ?>"></td></tr>
</table>
<tr><td colspan="2" style="text-align:center;"><input type="submit" class="submit" value="Salvar" name="enviar"></td></tr>
</pre>
</table> 
</form>
</center>
<center>
<tr><td><div align="center"><h3>PASSO 2 - Logotipo da OS</h3></div></td></tr>
<tr><td><div align="center"><h4>Faça UPLOAD do logotipo (300x100) que será utilizado na OS. (obs: pasta pics precisa ter permissão de escrita.)</h4></div></td></tr>
<table width="500" border="0" cellpadding="0" cellspacing="0">
<form method="post" enctype="multipart/form-data" action="insert_logo.php">
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