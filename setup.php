<?php

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

	return array('name'			=> __('Os','os'),
<<<<<<< HEAD
					'version' 			=> '0.0.7',
					'author'			   => '<a href="mailto:juniormarcati@gmail.com"> Júnior Marcati </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'http://glpi-os.sourceforge.net',
=======
					'version' 			=> '0.0.7.1a',
					'author'			   => '<a href="mailto:itaymbere@gmail.com"> Itaymbere </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://itaymbere.github.io/glpi-os',
>>>>>>> 0.0.8a
					'minGlpiVersion'	=> '0.85'
					);
}

function plugin_os_check_prerequisites(){
        if (GLPI_VERSION>=0.85){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 0.85";
        }
}


function plugin_os_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}
?>
