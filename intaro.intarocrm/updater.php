<?php
$mid = 'intaro.intarocrm';
$CRM_API_HOST_OPTION = 'api_host';

if (!CModule::IncludeModule("intaro.intarocrm")) return;
// copy new files
$rsSites = CSite::GetList($by, $sort, array('DEF' => 'Y'));
$defaultSite = array();
while ($ar = $rsSites->Fetch()) {
	$defaultSite = $ar;
	break;
}

if(mkdir($defaultSite['ABS_DOC_ROOT'] . '/retailcrm/')) {
	CopyDirFiles(
		$defaultSite['ABS_DOC_ROOT'] . '/bitrix/modules/' . $mid . '/install/retailcrm/', $defaultSite['ABS_DOC_ROOT'] . '/retailcrm/', true, true
	);
}

$api_host = COption::GetOptionString($mid, $CRM_API_HOST_OPTION, 0);

// form correct url
$api_host = parse_url($api_host);
if($api_host['scheme'] != 'https') $api_host['scheme'] = 'https';

$apiHostArr = explode('.', $api_host['host']);

if(isset($apiHostArr[1]) && $apiHostArr[1] == 'intarocrm') {
	$apiHostArr[1] = 'retailcrm';
	$api_host['host'] = implode('.', $apiHostArr);
	$api_host = $api_host['scheme'] . '://' . $api_host['host'];	

	COption::SetOptionString($mid, $CRM_API_HOST_OPTION, $api_host);
}
