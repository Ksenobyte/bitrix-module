<?php
$mid = 'intaro.intarocrm';
$CRM_ORDER_HISTORY_DATE = 'order_history_date';

COption::SetOptionString($mid, $CRM_ORDER_HISTORY_DATE, new \DateTime()->format('Y-m-d H:i:s'));