<?php
$mid = 'intaro.intarocrm';
$CRM_ORDER_HISTORY_DATE = 'order_history_date';
$CRM_PAYMENT_STATUSES = 'pay_statuses_arr';

COption::SetOptionString($mid, $CRM_ORDER_HISTORY_DATE, date('Y-m-d H:i:s'));

$optionsPayStatuses = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_STATUSES, 0)); // --statuses

if(isset($optionsPayStatuses['Y'])) {
	$optionsPayStatuses['YY'] = $optionsPayStatuses['Y'];
	unset($optionsPayStatuses['Y']);
}

COption::SetOptionString($mid, $CRM_PAYMENT_STATUSES, serialize($optionsPayStatuses));