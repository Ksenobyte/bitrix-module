<?php
$mid = 'intaro.intarocrm';

$CRM_API_HOST_OPTION = 'api_host';
$CRM_API_KEY_OPTION = 'api_key';
$CRM_ORDER_TYPES_ARR = 'order_types_arr';
$CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
$CRM_DELIVERY_SERVICES_ARR = 'deliv_services_arr';
$CRM_PAYMENT_TYPES = 'pay_types_arr';
$CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
$CRM_PAYMENT = 'payment_arr';
$CRM_ORDER_LAST_ID = 'order_last_id';
$CRM_ORDER_SITES = 'sites_ids';
$CRM_ORDER_PROPS = 'order_props';

if (!CModule::IncludeModule("catalog")) return;
if (!CModule::IncludeModule("intaro.intarocrm")) return;

$optionsOrderTypes = unserialize(COption::GetOptionString($mid, $CRM_ORDER_TYPES_ARR, 0));
$optionsDelivTypes = unserialize(COption::GetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, 0));
$optionsPayTypes = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_TYPES, 0));
$optionsPayStatuses = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_STATUSES, 0));
$optionsPayment = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT, 0));
$optionsSites = unserialize(COption::GetOptionString($mid, $CRM_ORDER_SITES, 0));
$optionsOrderProps = unserialize(COption::GetOptionString($mid, $CRM_ORDER_PROPS, 0));

COption::SetOptionString($mid, $CRM_ORDER_TYPES_ARR, serialize(ICrmOrderActions::clearArr($optionsOrderTypes)));
COption::SetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, serialize(ICrmOrderActions::clearArr($optionsDelivTypes)));
COption::SetOptionString($mid, $CRM_PAYMENT_TYPES, serialize(ICrmOrderActions::clearArr($optionsPayTypes)));
COption::SetOptionString($mid, $CRM_PAYMENT_STATUSES, serialize(ICrmOrderActions::clearArr($optionsPayStatuses)));
COption::SetOptionString($mid, $CRM_PAYMENT, serialize(ICrmOrderActions::clearArr($optionsPayment)));
COption::SetOptionString($mid, $CRM_ORDER_SITES, serialize(ICrmOrderActions::clearArr($optionsSites)));
COption::SetOptionString($mid, $CRM_ORDER_PROPS, serialize(ICrmOrderActions::clearArr($orderPropsArr)));