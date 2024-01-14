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
class PluginOsProfile extends Profile {
   static $rightname = 'profile';
   function rawSearchOptions() {
      $tab = [];
      $tab[] = ['id'                 => 'common',
                'name'               => __('OS', 'os')];
      $tab[] = ['id'                 => '2',
                'table'              => $this->getTable(),
                'field'              => 'use',
                'linkfield'          => 'id',
                'datatype'           => 'bool'];
      return $tab;
   }
   function showForm($ID, $options=[]) {
      $profile      = new Profile();
      if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
         echo "<form action='".$profile->getFormURL()."' method='post'>";
      }
      $profile->getFromDB($ID);
      $real_right = ProfileRight::getProfileRights($ID, ['plugin_os']);
      $checked = 0;
      if (isset($real_right)
          && ($real_right['plugin_os'] == 1)) {
         $checked = 1;
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2' class='center b'>".__('OS', 'os');
      echo "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('OS', 'os')."</td><td>";
      Html::showCheckbox(['name'    => '_plugin_os',
                          'checked' => $checked]);
      echo "</td></tr></table>\n";
      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update',
                                                     'class' => 'btn btn-primary',
                                                    'icon'  => 'ti ti-device-floppy']);
         echo "</div>\n";
         Html::closeForm();
      }
   }
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType() == 'Profile') {
         return __('OS', 'os');
      }
      return '';
   }
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'Profile') {
         $prof =  new self();
         $ID = $item->getField('id');
         $prof->showForm($ID);
      }
      return true;
   }
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing=false) {
      $profileRight = new ProfileRight();
      $dbu          = new DbUtils();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
                                        ['profiles_id' => $profiles_id,
                                         'name'        => $right])
             && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id,
                                             'name'        => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                         ['profiles_id' => $profiles_id,
                                          'name'        => $right])) {
               $myright['profiles_id'] = $profiles_id;
               $myright['name']        = $right;
               $myright['rights']      = $value;
               $profileRight->add($myright);
               $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }
   static function createFirstAccess($ID) {
      self::addDefaultProfileInfos($ID, ['plugin_os' => 1], true);
   }
   static function getAllRights($all=false) {
      $rights = [['itemtype'  => 'PluginOs',
                  'label'     => __('OS', 'os'),
                  'field'     => 'plugin_os']];
      return $rights;
   }
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();
      foreach ($profile->getAllRights() as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights",
                                        ['name' => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }
      foreach ($DB->request('glpi_profilerights',
                            ['profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                             'name'        => ['LIKE', '%plugin_os%']]) as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }
   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_os_profiles';
      if (!$DB->tableExists($table)
          && !$DB->tableExists('glpi_plugin_os_preferences')) {
         self::initProfile();
         self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
      } else {
         if ($DB->tableExists($table)
             && $DB->fieldExists($table,'ID')) {
            $mig->changeField($table, 'ID', 'id', 'autoincrement');
         }
         $profileRight = new ProfileRight();
         if ($DB->tableExists($table)) {
            foreach ($DB->request($table, ['use' => 1]) as $data) {
               $right['profiles_id']   = $data['id'];
               $right['name']          = "plugin_os";
               $right['rights']        = $data['use'];
               $profileRight->add($right);
            }
            $mig->dropTable($table);
         }
      }
   }
}
