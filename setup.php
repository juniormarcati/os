<?php
// define glpi_os version
define('PLUGIN_OS_VERSION', '0.0.9');

class PluginOsConfig extends CommonDBTM {

   static protected $notable = true;
   
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Os');
   }
   
   static function getMenuContent() {
    global $CFG_GLPI;
   
    $menu = array();

      $menu['title']   = __('Os','os');
      $menu['page']    = "/plugins/os/front/os.php";
    return $menu;
   }
}

function plugin_init_os() {
  
   global $PLUGIN_HOOKS, $LANG ;
       
    $PLUGIN_HOOKS['csrf_compliant']['os'] = true;   
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
          'minGlpiVersion'  => '9.2'
          );
}

function plugin_os_check_prerequisites(){
        if (GLPI_VERSION>=9.2){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 9.2";
        }
}


function plugin_os_check_config($verbose=false){
  if ($verbose) {
    echo 'Installed / not configured';
  }
  return true;
}
?>