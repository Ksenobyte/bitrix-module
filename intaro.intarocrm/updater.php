<?php
$mid = 'intaro.intarocrm';
$CRM_CATALOG_BASE_PRICE = 'catalog_base_price';

if (!CModule::IncludeModule("catalog")) return;

// clear old agents
CAgent::RemoveAgent("ICrmOrderActions::uploadOrdersAgent();", $mid);
CAgent::RemoveAgent("ICrmOrderActions::orderHistoryAgent();", $mid);

// set catalog base price
dbPriceType = CCatalogGroup::GetList(
    array("SORT" => "ASC"), array("BASE" => "Y"), array(), array(), array("ID", "NAME", "BASE")
);

while ($arPriceType = $dbPriceType->Fetch()) {
    COption::SetOptionString($mid, $CRM_CATALOG_BASE_PRICE, $arPriceType['ID']);
    break;
}

// set new event handlers
RegisterModuleDependences("sale", "OnSaleBeforeReserveOrder", $mid, "ICrmOrderEvent", "onSaleBeforeReserveOrder");
RegisterModuleDependences("sale", "OnSaleReserveOrder", $mid, "ICrmOrderEvent", "onSaleReserveOrder");

// set orderAgent
CAgent::AddAgent(
    "ICrmOrderActions::orderAgent();", $mid, "N", 600, // interval - 10 mins
    $dateAgent->format('d.m.Y H:i:s'), // date of first check
    "Y", // agent is active
    $dateAgent->format('d.m.Y H:i:s'), // date of first start
    30
);
