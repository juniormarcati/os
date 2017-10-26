<?php
// define glpi_os version
define('PLUGIN_OS_VERSION', '0.0.8');

class PluginOsConfig extends CommonDBTM {
  static protected $notable = true;
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
  global $PLUGIN_HOOKS, $LANG;
  $PLUGIN_HOOKS['csrf_compliant']['os'] = true;
  $PLUGIN_HOOKS["menu_toadd"]['os'] = array('plugins'  => 'PluginOsConfig');
  $PLUGIN_HOOKS['config_page']['os'] = 'front/index.php';
}


function plugin_version_os() {
  global $DB, $LANG;
  return [
    'name'      => 'OS',
    'version' 	=> PLUGIN_OS_VERSION ,
    'author'		=> '<a href="mailto:juniormarcati@gmail.com"> Júnior Marcati </b> </a>',
    'license'		=> 'GPLv2+',
    'homepage'	=> 'http://glpi-os.sourceforge.net',
    'requirements'   => [
      'glpi'   => [
        'min' => '9.2'
      ],
      'php'    => [
        'min' => '7.0'
      ]
    ]
  ];
}

function plugin_os_check_config($verbose = false) {
if (true) { // Your configuration check
  return true;
}

if ($verbose) {
  echo "Installed, but not configured";
}
return false;
}