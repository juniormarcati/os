<?php
define('PLUGIN_OS_VERSION', '0.2.0-beta2');

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
      $botao = Session::haveRight(Config::$rightname, UPDATE);
      echo "<form name='form' action='../plugins/os/front/os.php' method='get'>\n";
      echo Html::hidden('config_context', ['value' => 'os']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<div class='center' id='tabsbody'>\n";
      echo "<table class='tab_cadre_fixe' style='width:95%;'>\n";
      echo "<tr class='tab_bg_2'>\n";
      echo "<td colspan='4' class='center'>\n";
      echo "<input type='submit' name='update' class='submit' value=\"" . __('Gerar OS - Entidade', 'os') . "\">\n";
      echo "</td></tr>\n";
      echo "</table></div>";
      Html::closeForm();
      $botao_cli = Session::haveRight(Config::$rightname, UPDATE);
      echo "<form name='form' action='../plugins/os/front/os_cli.php' method='get'>\n";
      echo Html::hidden('config_context', ['value' => 'os']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<div class='center' id='tabsbody2'>\n";
      echo "<table class='tab_cadre_fixe' style='width:95%;'>\n";
      echo "<tr class='tab_bg_2'>\n";
      echo "<td colspan='4' class='center'>\n";
      echo "<input type='submit' name='update2' class='submit' value=\"" . __('Gerar OS - Cliente', 'os') . "\">\n";
      echo "</td></tr>\n";
      echo "</table></div>";
      Html::closeForm();

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

  return array('name'     => __('Os','os'),
          'version'   => PLUGIN_OS_VERSION ,
          'author'         => '<a href="mailto:junior@marcati.com.br"> Júnior Marcati </b> </a>',
          'license'     => 'GPLv2+',
          'homepage'      => 'http://glpi-os.sourceforge.net',
          'minGlpiVersion'  => '9.3'
          );
}

function plugin_os_check_prerequisites(){
        if (GLPI_VERSION>=9.3){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 9.3";
        }
}


function plugin_os_check_config($verbose=false){
  if ($verbose) {
    echo 'Installed / not configured';
  }
  return true;
}
?>
