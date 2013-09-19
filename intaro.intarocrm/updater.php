<?php
$mid = 'intaro.intarocrm';
$CRM_ORDER_PROPS = 'order_props';

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/intarocrm_run.php')) {
    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/intarocrm_run.php');
}
$updater->CopyFiles("install/export/intarocrm_run.php", "php_interface/include/catalog_export/intarocrm_run.php");

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/intarocrm_setup.php')) {
    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/intarocrm_setup.php');
}
$updater->CopyFiles("install/export/intarocrm_setup.php", "php_interface/include/catalog_export/intarocrm_setup.php");

COption::SetOptionString('intaro.intarocrm', 'sites_ids', 0);

$dateAgent = new DateTime();
$intAgent = new DateInterval('PT60S'); // PT60S - 60 sec;
$dateAgent->add($intAgent);

CAgent::AddAgent(
        "ICrmOrderActions::orderHistoryAgent();", 'intaro.intarocrm', "N", 600, // interval - 10 mins
        $dateAgent->format('d.m.Y H:i:s'), // date of first check
        "Y", // агент активен
        $dateAgent->format('d.m.Y H:i:s'), // date of first start
        30
);

$defaultOrderProps = array(
    1 => array(
        'fio' => 'FIO',
        'index' => 'ZIP',
        'text' => 'ADDRESS',
        'phone' => 'PHONE',
        'email' => 'EMAIL'
    ),
    2 => array(
        'fio' => 'FIO',
        'index' => 'ZIP',
        'text' => 'ADDRESS',
        'phone' => 'PHONE',
        'email' => 'EMAIL'
    )
);

COption::SetOptionString($mid, $CRM_ORDER_PROPS, serialize($defaultOrderProps));
UnRegisterModuleDependences("sale", "OnOrderNewSendEmail", $mid, "ICrmOrderEvent", "onSendOrderMail");
UnRegisterModuleDependences("sale", "OnOrderUpdate", $mid, "ICrmOrderEvent", "onUpdateOrder");
UnRegisterModuleDependences("sale", "OnBeforeOrderAdd", $mid, "ICrmOrderEvent", "onBeforeOrderAdd");