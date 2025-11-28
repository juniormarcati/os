<?php
session_start();
include(GLPI_ROOT . '/inc/includes.php');
global $DB;


//Apenas pro header

global $EmpresaPlugin, $CnpjPlugin, $EnderecoPlugin, $TelefonePlugin, $CidadePlugin, $SitePlugin;
global $EntidadeName, $EntidadeCep, $EntidadeEndereco, $EntidadeEmail, $EntidadePhone;
global $OsNome, $OsDescricao, $DataOs, $OsData, $OsDataEntrega, $OsSolucao, $OsResponsavel, $OsDataAtendimento, $OsId;


$OsId = $_GET['id'] ?? 0;
if (!$OsId) die("ID do ticket não fornecido.");
$Plugin = $DB->request('glpi_plugin_os_config')->current();
$EmpresaPlugin   = $Plugin['name'] ?? "";
$CnpjPlugin      = $Plugin['cnpj'] ?? "";
$EnderecoPlugin  = $Plugin['address'] ?? "";
$TelefonePlugin  = $Plugin['phone'] ?? "";
$CidadePlugin    = $Plugin['city'] ?? "";
$SitePlugin      = $Plugin['site'] ?? "";

$Ticket = $DB->request('glpi_tickets', ['id' => $OsId])->current();
$OsNome      = $Ticket['name'] ?? "";
$OsDescricao = $Ticket['content'] ?? "";
$DataOs      = !empty($Ticket['date']) ? date('d/m/Y', strtotime($Ticket['date'])) : "";
$OsData      = !empty($Ticket['date']) ? date('d/m/Y H:i', strtotime($Ticket['date'])) : "";
$OsDataEntrega = !empty($Ticket['solvedate']) ? date('d/m/Y H:i', strtotime($Ticket['solvedate'])) : "";

$SolucaoTicket = $DB->request('glpi_itilsolutions', ['items_id' => $OsId, 'status' => [2,3]])->current();
$OsSolucao = $SolucaoTicket['content'] ?? 0;

$TicketUsers = $DB->request('glpi_tickets_users', ['tickets_id' => $OsId])->current();
$OsUserId = $TicketUsers['users_id'] ?? 0;

$Resps = $DB->request('glpi_tickets_users', ['tickets_id' => $OsId, 'type' => 2]);
$OsResponsavel = "";
foreach ($Resps as $resp) {
    $user = $DB->request('glpi_users', ['id' => $resp['users_id']])->current();
    $OsResponsavel .= ($user['firstname'] ?? "")." ".($user['realname'] ?? "").", ";
}
$OsResponsavel = rtrim($OsResponsavel, ", ");

$log = $DB->request('glpi_logs', ['itemtype' => 'Ticket','id_search_option'=>12,'new_value'=>15,'items_id'=>$OsId],"MAX(DATE_FORMAT(date_mod,'%d/%m/%Y %H:%i')) as date_mod")->current();
$OsDataAtendimento = $log['date_mod'] ?? "";

$EntidadeId = $Ticket['entities_id'] ?? 0;
$Empresa = $DB->request('glpi_entities', ['id' => $EntidadeId])->current();
$EntidadeName     = $Empresa['name'] ?? "";
$EntidadeCep      = $Empresa['postcode'] ?? "";
$EntidadeEndereco = $Empresa['address'] ?? "";
$EntidadeEmail    = $Empresa['email'] ?? "";
$EntidadePhone    = $Empresa['phonenumber'] ?? "";

$EntityRnQuery = $DB->request('glpi_plugin_os_rn', ['entities_id' => $EntidadeId])->current();
$EntityRn = $EntityRnQuery['rn'] ?? "";

$Email = $DB->request('glpi_useremails', ['users_id' => $OsUserId])->current();
$UserEmail = $Email['email'] ?? "";

$Custo = $DB->request('glpi_ticketcosts', ['tickets_id' => $OsId], "SUM(cost_material+cost_fixed+cost_time*actiontime/3600) AS SomaTudo")->current();
$CustoTotal = $Custo['SomaTudo'] ?? 0;
$CustoTotalFinal = number_format($CustoTotal, 2, ',', ' ');

$TempoTotal = $DB->request('glpi_ticketcosts', ['tickets_id' => $OsId], "SUM(actiontime) AS TempoTotal")->current();
$seconds = $TempoTotal['TempoTotal'] ?? 0;
$hours = floor($seconds / 3600);
$seconds -= $hours * 3600;
$minutes = floor($seconds / 60);
$seconds -= $minutes * 60;

$LocationsId = $Ticket['locations_id'] ?? 0;
$Loc = $DB->request('glpi_locations', ['id' => $LocationsId])->current();
$Locations = $Loc['name'] ?? "";

$Users = $DB->request('glpi_users', ['id' => $OsUserId])->current();
$UserName    = ($Users['firstname'] ?? "")." ".($Users['realname'] ?? "");
$UserCpf     = $Users['registration_number'] ?? "";
$UserTelefone= $Users['mobile'] ?? "";
$UserEndereco= $Users['comment'] ?? "";
$UserCep     = $Users['phone2'] ?? "";

$ItensQuery = $DB->request('glpi_items_tickets', ['tickets_id' => $OsId])->current();
$ItemType = $ItensQuery['itemtype'] ?? "";
$ItensId  = $ItensQuery['items_id'] ?? "";

$ComputersQuery = $DB->request('glpi_computers', ['id' => $ItensId])->current();
$ComputerName   = $ComputersQuery['name'] ?? "";
$ComputerSerial = $ComputersQuery['serial'] ?? "";

$MonitorsQuery = $DB->request('glpi_monitors', ['id' => $ItensId])->current();
$MonitorName   = $MonitorsQuery['name'] ?? "";
$MonitorSerial = $MonitorsQuery['serial'] ?? "";

$PrintersQuery = $DB->request('glpi_printers', ['id' => $ItensId])->current();
$PrinterName   = $PrintersQuery['name'] ?? "";
$PrinterSerial = $PrintersQuery['serial'] ?? "";
