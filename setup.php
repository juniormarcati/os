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
define('PLUGIN_OS_VERSION', '0.2.0b');

class PluginOsConfig extends CommonDBTM {

   static protected $notable = true;
   static function getMenuName() {
      return __('Os');
   }
   
   static function getMenuContent() {
    global $CFG_GLPI;
   
    $menu = array();

      $menu['title']   = __('Ordem de Serviço','os');
      $menu['page']    = "/plugins/os/front/index.php";
      return $menu;
   }	
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Ticket':
            return array(1 => __('Ordem de Serviço','os'));
         default:
            return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Ticket':
            $config = new self();
            $config->showFormDisplay();
            break;
      }
      return true;
   }

   function showFormDisplay() {
      global $CFG_GLPI, $DB;
      $ID = $_REQUEST['id'];
      echo "<head>";
      echo "<script type='text/javascript'>";
      echo "function setIframeSource() {";
      echo "var theSelect = document.getElementById('PageType');";
      echo "var theIframe = document.getElementById('OsIframe');";
      echo "var theUrl;";
      echo "theUrl = theSelect.options[theSelect.selectedIndex].value;";
      echo "theIframe.src = theUrl;";
      echo "}";
      echo "</script>";
      echo "</head>";
      echo "<body>";
      echo "<form id='form1' method='post'>";
      echo "<label>Selecione o Layout </label>";
      echo "<select id='PageType' onchange='setIframeSource()'>";
      echo "<option value='../plugins/os/front/os_pdf.php?id=$ID'>A4</option>";
      echo "<option value='../plugins/os/front/os_pdflabel.php?id=$ID'>Label</option>";
      echo "</select>";
      echo "</form>";
      echo "<iframe id='OsIframe' src='../plugins/os/front/os_pdf.php?id=$ID' frameborder='0' marginwidth='0' marginheight='0' width='90%' height='500'></iframe>";
      echo "</body>";
   }
}

function plugin_init_os() {
  global $PLUGIN_HOOKS, $LANG;
  
  $PLUGIN_HOOKS['csrf_compliant']['os'] = true;

   Plugin::registerClass('PluginOsConfig', [
      'addtabon' => ['Ticket']
   ]);   
  
  $PLUGIN_HOOKS["menu_toadd"]['os'] = array('plugins'  => 'PluginOsConfig');
  $PLUGIN_HOOKS['config_page']['os'] = 'front/index.php';
}


function plugin_version_os(){
  global $DB, $LANG;

  return array('name'     => __('OS','os'),
          'version'   => PLUGIN_OS_VERSION ,
          'author'         => '<a href="mailto:junior@marcati.com.br"> Júnior Marcati </b> </a>',
          'license'     => 'AGPLv3+',
          'homepage'      => 'http://glpi-os.sourceforge.net',
          'minGlpiVersion'  => '9.4'
          );
}

function plugin_os_check_prerequisites(){
        if (GLPI_VERSION>=9.4){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 9.4";
        }
}


function plugin_os_check_config($verbose=false){
  if ($verbose) {
    echo 'Installed / not configured';
  }
  return true;
}
?>
