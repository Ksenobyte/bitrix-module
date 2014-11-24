<?php
$mid = 'intaro.intarocrm';
$CRM_API_HOST_OPTION = 'api_host';

if (!CModule::IncludeModule("intaro.intarocrm")) return;
// copy new files
if(mkdir($_SERVER['DOCUMENT_ROOT'] . '/retailcrm/')) {
	CopyDirFiles(
		$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $mid . '/install/retailcrm/', $_SERVER['DOCUMENT_ROOT'] . '/retailcrm/', true, true
        );
}
// clear old agents
CAgent::RemoveAgent("ICrmOrderActions::ordersAgent();", $mid);
// set orderAgent
$dateAgent = new DateTime();
$intAgent = new DateInterval('PT60S'); // PT60S - 60 sec;
$dateAgent->add($intAgent);
CAgent::AddAgent(
    "ICrmOrderActions::forkedOrderAgent();", $mid, "N", 600, // interval - 10 mins
    $dateAgent->format('d.m.Y H:i:s'), // date of first check
    "Y", // agent is active
    $dateAgent->format('d.m.Y H:i:s'), // date of first start
    30
);

$api_host = COption::GetOptionString($mid, $CRM_API_HOST_OPTION, 0);

// form correct url
$api_host = parse_url($api_host);
if($api_host['scheme'] != 'https') $api_host['scheme'] = 'https';

$apiHostArr = explode('.', $api_host['host']);

if(isset($apiHostArr[1]) && $apiHostArr[1] == 'intarocrm') {
	$apiHostArr[1] = 'retailcrm';
	$api_host['host'] = implode('.', $api_host['host']);
	$api_host = $api_host['scheme'] . '://' . $api_host['host'];	

	COption::SetOptionString($mid, $CRM_API_HOST_OPTION, $api_host);
}
