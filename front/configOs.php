<?php
/**
 * ------------------------------------------------------------------------
 * Plugin OS – Community Edition
 * Copyright (C) 2016-2026 Marcati
 * https://github.com/juniormarcati
 * ------------------------------------------------------------------------
 * This file is part of Plugin OS.
 *
 * Plugin OS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Plugin OS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Plugin OS. If not, see <https://www.gnu.org/licenses/>.
 * ------------------------------------------------------------------------
 *
 * @package   PluginOS
 * @author    Marcati
 * @copyright 2016-2026 Marcati
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/juniormarcati/os
 * @since     2016
 * ------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    throw new RuntimeException(__('Invalid ID provided', 'osfree'));
}

$OsId = (int)$_GET['id'];

if (!function_exists('sanitizeOutput')) {
    function sanitizeOutput($value) {
        return Html::cleanInputText($value ?? ''); 
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($value) {
        return Html::formatNumber((float)$value, 2); 
    }
}

if (!function_exists('formatTime')) {
    function formatTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%02d:%02d", $hours, $minutes);
    }
}

try {
    
    $ticket = new Ticket();
    if (!$ticket->getFromDB($OsId)) {
        throw new RuntimeException(__('Ticket not found', 'osfree'));
    }
    
    if (!$ticket->canViewItem()) {
        throw new RuntimeException(__('No permission to access this ticket', 'osfree'));
    }

    if (!Session::haveAccessToEntity($ticket->fields['entities_id'])) {
        throw new RuntimeException(__('No permission to access this entity', 'osfree'));
    }
    
    $ticketData = $ticket->fields;
    
    $OsNome = sanitizeOutput($ticketData['name']);
    $DataOs = Html::convDate($ticketData['date']);
    $OsData = Html::convDateTime($ticketData['date']);
    $OsDescricao = $ticketData['content'];
    $OsDataEntrega = !empty($ticketData['closedate']) ? Html::convDateTime($ticketData['closedate']) : __('Not finished', 'osfree');
    
    global $DB;
    try {
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_os_config',
            'LIMIT' => 1
        ]);
        
        if (count($iterator) > 0) {
            $pluginConfig = $iterator->current();
        } else {
            $pluginConfig = [
                'name' => '',
                'cnpj' => '',
                'address' => '',
                'phone' => '',
                'city' => '',
                'site' => ''
            ];
        }
    } catch (Exception $e) {
        
        $pluginConfig = [
            'name' => '',
            'cnpj' => '',
            'address' => '',
            'phone' => '',
            'city' => '',
            'site' => ''
        ];
    }
    
    $EmpresaPlugin = sanitizeOutput($pluginConfig['name']);
    $CnpjPlugin = sanitizeOutput($pluginConfig['cnpj']);
    $EnderecoPlugin = sanitizeOutput($pluginConfig['address']);
    $TelefonePlugin = sanitizeOutput($pluginConfig['phone']);
    $CidadePlugin = sanitizeOutput($pluginConfig['city']);
    $SitePlugin = sanitizeOutput($pluginConfig['site']);
    
    $OsSolucao = '';
    try {
        $solution = new ITILSolution();
        $solutions = $solution->find([
            'items_id' => $OsId,
            'itemtype' => 'Ticket',
            'status' => [CommonITILValidation::ACCEPTED, CommonITILValidation::WAITING]
        ], ['date_creation DESC'], 1);
        
        if (!empty($solutions)) {
            $solutionData = reset($solutions);
            $OsSolucao = $solutionData['content'];
        }
    } catch (Exception $e) {
        
        $OsSolucao = '';
    }
    
    $ticket_user = new Ticket_User();
    $technicians = $ticket_user->find([
        'tickets_id' => $OsId,
        'type' => CommonITILActor::ASSIGN
    ]);
    
    $techNames = [];
    foreach ($technicians as $techData) {
        $user = new User();
        if ($user->getFromDB($techData['users_id'])) {
            $techNames[] = $user->getName();
        }
    }
    $OsResponsavel = implode(', ', $techNames) ?: __('Not assigned', 'osfree');

    $entity = new Entity();
    $entityData = [
        'name' => '',
        'postcode' => '',
        'address' => '',
        'email' => '',
        'phonenumber' => '',
        'registration_number' => ''
    ];
    
    if ($entity->getFromDB($ticketData['entities_id'])) {
        $entityData = $entity->fields;
    }
    
    $EntidadeName = sanitizeOutput($entityData['name']);
    $EntidadeCep = sanitizeOutput($entityData['postcode']);
    $EntidadeEndereco = sanitizeOutput($entityData['address']);
    $EntidadeEmail = sanitizeOutput($entityData['email']);
    $EntidadePhone = sanitizeOutput($entityData['phonenumber']);
    $EntityRn = sanitizeOutput($entityData['registration_number']);
    
    if (empty($EntityRn) && class_exists('PluginOsfreeRn')) {
        try {
            $rnConfig = new PluginOsfreeRn();
            $rnData = $rnConfig->find(['entities_id' => $ticketData['entities_id']]);
            if (!empty($rnData)) {
                $rnInfo = reset($rnData);
                $EntityRn = sanitizeOutput($rnInfo['rn']);
            }
        } catch (Exception $e) {
        }
    }
    
    $requesters = $ticket_user->find([
        'tickets_id' => $OsId,
        'type' => CommonITILActor::REQUESTER
    ]);
    
    $userData = [
        'firstname' => '',
        'realname' => '',
        'registration_number' => '',
        'mobile' => '',
        'comment' => '',
        'phone2' => ''
    ];
    $UserEmail = '';
    
    if (!empty($requesters)) {
        $requesterData = reset($requesters);
        $user = new User();
        if ($user->getFromDB($requesterData['users_id'])) {
            $userData = $user->fields;
            
            try {
                $userEmailObj = new UserEmail();
                $emails = $userEmailObj->find([
                    'users_id' => $requesterData['users_id'],
                    'is_default' => 1
                ], [], 1);
                
                if (!empty($emails)) {
                    $emailData = reset($emails);
                    $UserEmail = $emailData['email'];
                } else {
                    $emails = $userEmailObj->find([
                        'users_id' => $requesterData['users_id']
                    ], [], 1);
                    
                    if (!empty($emails)) {
                        $emailData = reset($emails);
                        $UserEmail = $emailData['email'];
                    }
                }
            } catch (Exception $e) {
                $UserEmail = $userData['email'] ?? '';
            }
        }
    }
    
    $UserName = sanitizeOutput(trim($userData['firstname'] . ' ' . $userData['realname']));
    $UserCpf = sanitizeOutput($userData['registration_number']);
    $UserTelefone = sanitizeOutput($userData['mobile']);
    $UserEndereco = sanitizeOutput($userData['comment']);
    $UserCep = sanitizeOutput($userData['phone2']);
    $UserEmail = sanitizeOutput($UserEmail);
    
    $ticketCosts = [];
    $CustoTotal = 0;
    $CustoTotalFinal = formatCurrency(0);
    $totalTimeFormatted = formatTime(0);
    
    $Locations = '';
    if (!empty($ticketData['locations_id'])) {
        $location = new Location();
        if ($location->getFromDB($ticketData['locations_id'])) {
            $Locations = sanitizeOutput($location->fields['name']);
        }
    }
    
    $allItems = [];
    $ItemType = $ItensId = '';
    $ComputerName = $ComputerSerial = '';
    $MonitorName = $MonitorSerial = '';
    $PrinterName = $PrinterSerial = '';

} catch (Exception $e) {

    $EmpresaPlugin = __('Error loading configuration', 'osfree');
    $CnpjPlugin = $EnderecoPlugin = $TelefonePlugin = $CidadePlugin = $SitePlugin = '';
    $OsId = $OsId ?? 0;
    $OsNome = __('Error loading ticket', 'osfree');
    $DataOs = $OsData = $OsDataEntrega = Html::convDate(date('Y-m-d'));
    $OsDescricao = $OsSolucao = __('Error loading data', 'osfree');
    $OsResponsavel = $UserName = __('Not available', 'osfree');
    $EntidadeName = $EntidadeCep = $EntidadeEndereco = $EntidadeEmail = $EntidadePhone = '';
    $EntityRn = $UserCpf = $UserTelefone = $UserEndereco = $UserCep = $UserEmail = '';
    $ticketCosts = [];
    $CustoTotal = 0;
    $CustoTotalFinal = formatCurrency(0);
    $totalTimeFormatted = '00:00';
    $Locations = '';
    $allItems = [];
    $ItemType = $ItensId = '';
    $ComputerName = $ComputerSerial = $MonitorName = $MonitorSerial = $PrinterName = $PrinterSerial = '';
    
    if (isset($_SESSION['glpi_use_mode']) && $_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
        
    }
}

